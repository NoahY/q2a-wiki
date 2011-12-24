<?php

/*
   This plugin encapsulates the database layer to fragment your database
   into separate pieces. All pages from then will be created in its own
   namespace. The global '$subwiki' variable makes the internal pagename
   prefix and must be set by yoursite.php (the layout wrapper).

   The initial pages are created as usual, but then exist multiple times
   in the database ("WikiOne:EditThisPage" and "WikiTwo:EditThisPage").
   You should preferrably use the colon as separator, as it perfectly
   matches the InterWiki syntax; define the interwiki monikers and
   wrappers at different URLs correctly to get a closed system.
*/


#<off># $ewiki_plugins["init"][] = "ewiki_database_subwiki_init";
        ewiki_database_subwiki_init();
// initialization timing is difficult here, eventually breaks binary
// upload support then


function ewiki_database_subwiki_init()
{
   global $ewiki_plugins, $subwiki;

   #-- only engage if subwiki filtering requested
   if ($subwiki && !strpos($ewiki_plugins["database"][0], "_subwiki")) {

      ($pf = $ewiki_plugins["database"][0])
      or
      ($pf = "ewiki_database_mysql");

      $ewiki_plugins["database_real"][0] = $pf;
      $ewiki_plugins["database"][0] = "ewiki_database_subwiki";
   }
}

function ewiki_database_subwiki($func, &$args, $f1=0, $f2=0) {
   global $ewiki_plugins, $subwiki;

   $dbf = &$ewiki_plugins["database_real"][0];
   $n = strlen($subwiki);
   $dot = ":";

   switch ($func) {

      case "GET":
      case "HIT":
      case "OVERWRITE":
      case "WRITE":
      case "DELETE":
         $args["id"] = $subwiki . $dot . $args["id"];
      case "INIT":
         $r = $dbf($func, $args, $f1, $f2);
   // again "GET":
         if ($func=="GET") {
            $r["id"] = substr($r["id"], $n+1);
         }
         break;

      case "FIND":
         foreach ($args as $i=>$s) {
            $args[$i] = $subwiki . $dot . $s;
         }
         $e = $dbf($func, $args, $f1, $f2);
         $r = array();
         foreach ($e as $s=>$x) {
            $r[substr($s, $n+1)] = $x;
         }
         break;

      case "SEARCH":
      case "GETALL":
         $r = $dbf($func, $args, $f1, $f2);
         foreach ($r->entries as $i=>$d) {
            if (is_array($d) && (0==strncmp($d["id"], $subwiki, $n))) {
               $r->entries[$i]["id"] = substr($d["id"], $n+1);
            }
            elseif (is_string($d) && (0==strncmp($d, $subwiki, $n))) {
               $r->entries[$i] = substr($d, $n+1);
            }
            else {
               unset($i);
            }
         }
         break;

      default:
         die("\nERROR: unsupported database subfunc '$func'\n");
   }

   return($r);
}


?>