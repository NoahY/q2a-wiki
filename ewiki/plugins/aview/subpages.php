<?php

//General subpages display plugin, lists all pages of the form
// current_pagename.*

//Original code by AndyFundinger

$ewiki_plugins["view_append"][] = "ewiki_view_append_subpages";
$ewiki_t["en"]["SUBPAGES"]= "Subpages";


function ewiki_view_append_subpages($id, $data, $action, $title="SUBPAGES", $class="subpages") {

   $pages=ewiki_subpage_list($id);

   if(0==count($pages)){return("");}

   $o = '<div class="'.$class.'"><small>'.ewiki_t($title).":</small><br>";
   $o .= ewiki_list_pages($pages)."</div>\n";
   return($o);
}

function ewiki_subpage_list($id,$postfix=""){

        $_hiding = EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING;

	$result = ewiki_database("SEARCH", array("id" => $id.$postfix));
	while ($row = $result->get()) {

            #-- retrieve and check rights if running in protected mode

            if ($_hiding){
                if(!ewiki_auth($row["id"], $uu,'view', $ring=false, $force=0)) {
                    continue;
                }
            }   
            $pages[$row["id"]] = "";
	}
	return($pages);
}


?>