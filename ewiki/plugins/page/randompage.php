<?php

 # this emits a rondomly selected page
 # if "RandomPage" is requested


define("EWIKI_PAGE_RANDOMPAGE", "RandomPage");
$ewiki_plugins["page"][EWIKI_PAGE_RANDOMPAGE] = "ewiki_page_random";


srand(time()-microtime()*1000);




function ewiki_page_random($id=0, $data=0) {

   global $ewiki_plugins;

   $result = array_keys(ewiki_database("GETALL", "flags"));
   while ($row = $result->get()) {
        if (EWIKI_PROTECTED_MODE && EWIKI_PROTECTED_MODE_HIDING && !ewiki_auth($row["id"], $uu, "view")) {
            continue;
        }   
        if (($row["flags"] & EWIKI_DB_F_TYPE) == EWIKI_DB_F_TEXT) {
            $pages[] = $row["id"];
        }
   }

   $pages = array_merge($pages, $ewiki_plugins["page"]);

   $n = rand(0, count($pages));
   $id = $pages[$n];

   return(ewiki_page($id));
}


?>