<?php

/*
    This plugin will create a sitemap rooted at the given location
    Written By: Jeffrey Engleman
*/

define("EWIKI_PAGE_SITEMAP", "SiteMap");
define("EWIKI_SITEMAP_DEPTH", 1);
$ewiki_t["en"]["INVALIDROOT"] = "You are not authorized to access the current root page so no sitemap can be created.";
$ewiki_t["en"]["SITEMAPFOR"] = "Site map for ";
$ewiki_t["en"]["VIEWSMFOR"] = "View site map for ";
$ewiki_plugins["page"][EWIKI_PAGE_SITEMAP]="ewiki_page_sitemap";
$ewiki_plugins["action"]['sitemap']="ewiki_page_sitemap";

if(!isset($ewiki_config["SiteMap"]["RootList"])){
  $ewiki_config["SiteMap"]["RootList"]=array(EWIKI_PAGE_INDEX);
}

/* 
  populates an array with all sites the current user is allowed to access
  calls the sitemap creation function.
  returns the sitemap to be displayed.
*/
function ewiki_page_sitemap($id=0, $data=0, $action=0){
  global $ewiki_config;

  //**code hijacked from page_pageindex.php**
  //creates a list of all of the valid wiki pages in the site
  $str_null=NULL;

  //$time=getmicrotime();
  $result = ewiki_database("GETALL", array("flags", "refs"));
  while ($row = $result->get()) {
    if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && !ewiki_auth($row["id"], $str_null, "view")) {
      continue;
    }   
    if (($row["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_TEXT) {
      $a_validpages[$row["id"]]=array("refs" => explode("\n",$row["refs"]), "touched" => FALSE);
    }
  }
  //**end of hijacked code**
  //$time_end=getmicrotime();

  //creates the title bar on top of page 
  if($id == EWIKI_PAGE_SITEMAP){
    $o = ewiki_make_title($id, $id, 2);  

    foreach($ewiki_config["SiteMap"]["RootList"] as $root){
      if(isset($a_validpages[$root])){
        $valid_root=TRUE;
        $str_rootid=$root;
        break;
      }
    }
    
  }else{
    $o = ewiki_make_title($id, ewiki_t("SITEMAPFOR").$id, 2);    
    if(isset($a_validpages[$id])){
      $valid_root=TRUE;
      $str_rootid=$id;
    }    
  }

  $o .= "<p>".ewiki_t("VIEWSMFOR");

  foreach($ewiki_config["SiteMap"]["RootList"] as $root){
    if(isset($a_validpages[$root])){
      $o.='<a href="'.ewiki_script('sitemap/',$root).'">'.$root.'</a> ';
    }
  }
  
  $o.="</p>";

  //checks to see if the user is allowed to view the root page
  if(!isset($a_validpages[$str_rootid])){
    $o .= ewiki_t("INVALIDROOT");
    return $o;
  }
  
  //$timesitemap=getmicrotime();
  $o.=ewiki_sitemap_create($str_rootid, $a_validpages);
  //$timesitemap_end=getmicrotime();
  
  //$o.="GetAll: ".($time_end-$time)."\n";
  //$o.="SiteMap: ".($timesitemap_end-$timesitemap)."\n";
  //$o.="Total: ".($timesitemap_end-$time);
  
  
  return($o);
    
}

/*
  Adds each of the pages in the sitemap to an HTML list.  Each site is a clickable link.
*/
function format_sitemap($a_sitemap, $str_rootpage, &$str_formatted, &$prevlevel, &$timer){

  //get all children of the root format them and store in $str_formatted array
  if($a_sitemap[$str_rootpage]["child"]){
    while($str_child = current($a_sitemap[$str_rootpage]["child"])){
      $str_mark="";
      if($a_sitemap[$str_rootpage]["level"]>$prevlevel){
        $str_mark="<ul>\n";
      } 
      elseif ($a_sitemap[$str_rootpage]["level"]<$prevlevel){
        //markup length is 6 characters
        $str_mark=str_pad("", ($prevlevel-$a_sitemap[$str_rootpage]["level"])*6, "</ul>\n");
      }
      $prevlevel=$a_sitemap[$str_rootpage]["level"];
      $str_formatted.=($str_mark."<li><a href=\"?page=".$str_child."\">".$str_child."</a></li>\n");
      array_shift($a_sitemap[$str_rootpage]["child"]);
      format_sitemap($a_sitemap, $str_child, $str_formatted, $prevlevel, $timer);
    }
    return ($prevlevel+1);
  }
}


/*
  gets all children of the given root and stores them in the $a_children array
*/
function ewiki_page_listallchildren($str_root, &$a_children, &$a_sitemap, &$a_validpages, $i_level){
  if($i_level<EWIKI_SITEMAP_DEPTH){ //controls depth the sitemap will recurse into
    foreach($a_validpages[$str_root]["refs"] as $str_refs){
      if($str_refs){ //make sure $str_refs contains a value before doing anything
        if(isset($a_validpages[$str_refs])){ //test page validity
          if(!$a_validpages[$str_refs]["touched"]){ //check to see if page already exists
            $a_validpages[$str_refs]["touched"]=TRUE; //mark page as displayed
            $a_children[$str_refs]="";
            $a_currchildren[]=$str_refs;
          }
        }
      }
    }
    if($a_currchildren){
      $a_sitemap[$str_root]=array("level" => $i_level, "child" => $a_currchildren);
    } else {
      $a_sitemap[$str_root]=array("level" => $i_level);
    }
  }
}   


/*
  Creates the sitemap. And sends the data to the format_sitemap function.
  Returns the HTML formatted sitemap.
*/
function ewiki_sitemap_create($str_rootid, $a_validpages){
  //map starts out with a depth of 0
  $i_depth=0;
  $forcelevel=FALSE;

  //create entry for root in the sitemap array
  $a_sitemap[$str_rootid]=array("parent" => "", "level" => $i_depth, "child" => $str_rootid);
  //mark the root page as touched
  $a_validpages[$str_rootid]["touched"]=TRUE;
  //list all of the children of the root
  ewiki_page_listallchildren($str_rootid, $a_children, $a_sitemap, $a_validpages, $i_depth);
  $i_depth++;    
  
  if($a_children){
    end($a_children);
    $str_nextlevel=key($a_children);
    reset($a_children);
    
    while($str_child = key($a_children)){
      //list all children of the current child
      ewiki_page_listallchildren($str_child, $a_children, $a_sitemap, $a_validpages, $i_depth);
      
      //if the child is the next level marker...
      if($str_child==$str_nextlevel){
        //increment the level counter
        $i_depth++;
        //determine which child marks the end of this level
        end($a_children);
        $str_nextlevel=key($a_children);
        //reset the array counter to the beginning of the array
        reset($a_children);
        //we are done with this child...get rid of it 
      }
      array_shift($a_children);
    }
  }
  
  $level=-1;
  $timer=array();
  $str_formatted="<ul>\n<li><a href=\"?page=".$str_rootid."\">".$str_rootid."</a></li>";
  $fin_level=format_sitemap($a_sitemap, $str_rootid, $str_formatted, $level, $timer);
  $str_formatted.="</ul>".str_pad("", $fin_level*6, "</ul>\n");
  $r=$str_formatted;

  return $r;
}
?>