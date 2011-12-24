<?php

/*
   This mpi allows you to insert another wikipage into the current
   one using <?plugin Insert ThisWikiPage ?>. You can also temporarily
   change some rendering parameters, by supplying them as optional
   parameters:
     <?plugins insert PageName split_title=0 control_line=0 ?>

   The table=0 parameter would disable the optional table+border
   around the inserted page:
     <?plugins insert PageName table=0 ?>

   Please note, that the inserted page will be requested through
   an "sub-request" with ewiki_page(), thus usually incorporating
   all settings from the main page.
*/

  # you can disable the <table> generation, if you style pages via CSS
define("EWIKI_MPI_INSERT_TBL", 1);


$ewiki_plugins["mpi"]["insert"] = "ewiki_mpi_insert";


function ewiki_mpi_insert($action="html", $args, &$iii, &$s) {

   global $ewiki_config;

   switch ($action) {
      case "doc": return("The <b>insert</b> plugin allows you to insert the contents of another WikiPage into the current one.");
      case "desc": return("insert another WikiPage");

      default:
         #-- save environment
         $prevG = $GLOBALS;

         #-- use any params as _config settings
         foreach ($args as $set=>$val) {
            if ($set != "_") { 
               $ewiki_config[$set] = $val;
            }
         }

         #-- render requested page, through sub-request
         $o = ewiki_page($args["id"]);

         #-- reset env
         $GLOBALS = $prevG;

         #-- mk table around output
         if (!isset($args["table"]) && EWIKI_MPI_INSERT_TBL || $args["table"]) {
            $o = '<table border="1" cellpadding="5" cellspacing="5"><tr><td>' . $o . '</td></tr></table>';
         }
         $o = '<div class="mpi-insert">' . $o . '</div>';
         unset($prevG);
   }

   return($o);
}

?>