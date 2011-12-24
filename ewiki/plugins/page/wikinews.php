<?php

/*
   prints out a short summary of changed wiki pages
   (an "updated-articles-list")

   It respects following $ewiki_config[] entries:
    ["wikinews_num"] - how many new articles to be shown
    ["wikinews_len"] - string length of the excerpts
    ["wikinews_regex"] - use only pages that match this /pregex/
*/


$ewiki_plugins["page"]["WikiNews"] = "ewiki_page_wikinews";


function ewiki_page_wikinews($id, $data, $action) {

   global $ewiki_plugins, $ewiki_config;

   #-- conf
   ($n_num = $ewiki_config["wikinews_num"]) || ($n_num = 10);
   ($n_len = $ewiki_config["wikinews_len"]) || ($n_len = 512);
   ($c_regex = $ewiki_config["wikinews_regex"]) || ($c_regex = false);

   #-- fetch all page entries from DB, for sorting on creation time
   $result = ewiki_database("GETALL", array("created"));
   $sorted = array();
   while ($row = $result->get()) {

      if (EWIKI_DB_F_TEXT == ($row["flags"] & EWIKI_DB_F_TYPE)) {

         if ($c_regex && !preg_match($c_regex, $row["id"])) {
            continue;
         }

         $sorted[$row["id"]] = $row["created"];
      }
   }

   #-- sort 
   arsort($sorted);
      
   $displayed  = 0;//$displayed will count pages successfully displayed

   #-- gen output
   $o = "";
   foreach ($sorted as $id=>$uu) {

      $row = ewiki_database("GET", array("id"=>$id));

      #-- require auth
      if (EWIKI_PROTECTED_MODE && !ewiki_auth($id, $row, "view", $ring=false, $force=0)) {
         if (EWIKI_PROTECTED_MODE_HIDING) {
            continue;
         } else {
            $row["content"] = ewiki_t("FORBIDDEN");
         }
      }
      
      $text = substr($row["content"], 0, $n_len);
      $text = trim(strtr($text, "\r\n\t", "   "));
      $text = str_replace("[internal://", "[  internal://", $text);
      $text .= " [...[read more | $id]]\n";
      
      $o .= "\n" .
          "!!! [$id] \n";
      $o .= " $text\n";
      $o .= "----\n";

      if (!($n_num--)) {
         break;
      }
   }

   #-- pass thru renderer
   $o = ewiki_format($o);

   return($o);
}



?>