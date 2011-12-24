<?php

/*
   for use with plugins/contrib/autolinking.php
   * creates a cache entry
*/

define("EWIKI_AUTOLINKING_CACHE", "system/tmp/autolinking");

if (!$_POST["admin_do"]) {

   echo<<<EOT
<h2 class="page title">$id</h2>
<form action="$_SERVER[REQUEST_URI]" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="id" value="$action/$id">
  <input type="submit" name="admin_do" value="do">
</form>
EOT;

}
else {

   $pages = array();

   #-- find AllPages
   $result = ewiki_database("GETALL", array("id", "flags"));
   while ($row = $result->get()) {

      if (EWIKI_DB_F_TEXT != ($row["flags"] & EWIKI_DB_F_TYPE)) {
         continue;
      }
      $id = $row["id"];

      #-- only care about pagenames, which are words but no WikiWords
      if (!strpos($id, " ") && preg_match('/^\w+$/', $id)
      && !preg_match('/^(['.EWIKI_CHARS_U.']+['.EWIKI_CHARS_L.']+){2,}[\w\d]*$/', $id))
      {
         $pages[] = $id;
      }

   }

   #-- save found pages in cache entry
   $save = array(
      "id" => EWIKI_AUTOLINKING_CACHE,
      "version" => 1,
      "flags" => EWIKI_DB_F_SYSTEM,
      "created" => time(),
      "lastmodified" => time(),
      "author" => ewiki_author("PrepareAutolinking"),
      "content" => "",
      "meta" => "",
      "refs" => "\n\n" . implode("\n", $pages) . "\n\n",
   );
   $ok = ewiki_database("OVERWRITE", $save);

   #-- output results
   if ($ok) {
      echo "Written informations about <b>" . count($pages) . "</b> pages into the database cache entry <tt>" . EWIKI_AUTOLINKING_CACHE . "</tt>. These pages will then get autolinked by the according plugin.";
   }
   else {
      echo "Error writing the database cache entry <tt>" . EWIKI_AUTOLINKING_CACHE . "</tt>. Autolinking pages won't work, please retry";
   }

}

?>