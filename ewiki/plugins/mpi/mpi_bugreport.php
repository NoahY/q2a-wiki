<?php

/*
   Allows users to add a bug report, automatically creates a new bug
   number and writes that onto another page (BugNumberList). This also
   provides <form> elements which query users for version and system
   information.
*/


$ewiki_plugins["mpi"]["bugreport"] = "ewiki_mpi_bugreport";



// view <form>
function ewiki_mpi_bugreport($action, $args, &$iii, &$s) 
{
    global $ewiki_id;
    $PREP = "BUG";
    $o = "";

    if ($_REQUEST["br_save"]) {

       #-- check parameters
       $bugno = &$_REQUEST["bugno"];
       $content = &$_REQUEST["content"];
       $title = &$_REQUEST["title"];
       $author = &$_REQUEST["author"];
       if (strlen($content) < 50) {
          return("<p><b>Insufficient information for a useful BugReport.</b></p>");
       }
       if (trim($title) == "???") {
          $title = substr($content, 0, 50);
       }
       if (!strpos($bugno, "#") || (strlen($bugno) > 12)) {
          $bugno = $PREP . "#" . rand(1000,9999);
       }

       #-- add bugno to special page
       ewiki_mk_bugno($bugno, $title);

       #-- generate appended text
       $add = "\n\n"
            . "----\n\n"
            . "!! $bugno $title\n\n";
       foreach ($_REQUEST["i"] as $i=>$v) {
          if ($v != "unknown") {
             $add .= "| $i | $v |\n";
          }
       }
       $add .= "\n$author: $content\n\n";

       #-- store bugreport
       $data = ewiki_database("GET", array("id"=>$ewiki_id));
       $data["content"] .= $add;
       ewiki_data_update($data);
       $data["version"]++;
       ewiki_database("WRITE", $data);

       #-- append to page output
       $iii[] = array(
          $add,
          0xFFFF,
          "core"
       );


    }
    else {

       $url = ewiki_script("", $ewiki_id);
       $rand = rand(0,8999)+1000;
       $ver = EWIKI_VERSION;
       $o .=<<<EOT
<form style="border:2px #333370 solid; background:#7770B0; padding:5px;"class="BugReport" action="$url" method="POST" enctype="multipart/form-data">
bug title: <input type="text" name="title" value="???" size="50">
<br>
<br>
YourName: <input type="text" name="author" value="anonymous" size="30">
<br>
<br>
your ewiki version: <select name="i[ver]"><option>unknown<option>other<option>$ver<option>CVS today<option>CVS yesterday<option>CVS last week<option>two weeks old<option>latest -dev<option>R1.02a<option>R1.01e<option>R1.01d<option>R1.01c<option>R1.01b<option>R1.01a</select>
plattform: <select name="i[os]"><option>unknown<option>other<option>Win4<option>NT (2K,XP)<option>Unix<option>Linux<option>OS X</select>
<br>
database: <select name="i[db]"><option>unknown<option>mysql<option>db_flat_files<option>db_fast_files<option>anydb: other<option>anydb: pgsql<option>dzf2<option>dba/dbm<option>phpwiki13<option>zip<option>other<option>own</select>
php version: <select name="i[php]"><option>unknown<option>4.0.x<option>4.1.x<option>4.2.x<option>4.3.x<option>4.4/4.5 (CVS)<option>5.0b1<option>5.0b2 and later</select>
<br>
<br>
long error description:<br> 
<textarea name="content" cols="75" rows="8">
</textarea>
<br><br>
<input type="submit" name="br_save" value="send">
&nbsp; &nbsp; bug wish number: <input type="text" name="bugno" value="$PREP#$rand" size="10">
<br>
</form>
EOT;
    }

    return($o);
}



function ewiki_mk_bugno(&$bugid, $title, $id="BugNumberList") {

   $data = ewiki_database("GET", array("id"=>$id));
   if (!$data["version"]) {
      $data = ewiki_new_data($id);
   }
   else {
      $data["version"]++;
   }

   #-- mk unique bug id
   list($prep,$subno) = explode("#", $bugid);
   while (strpos($data["content"], "#$subno")) {
      $subno = rand(0,8999)+1000;
      $bugid = "$prep#$subno";
   }

   #-- check if to add new bug id
   if (!strpos($data["content"], "#$subno")) {
      $data["content"] =
         rtrim($data["content"])
         . "\n* [search:$bugid# \"$bugid\"]"
         . " $title\n";
      ewiki_data_update($data);
      ewiki_database("WRITE", $data);
   }

}


?>