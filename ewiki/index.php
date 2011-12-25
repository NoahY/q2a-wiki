<?php

 #-- open database connection,
 #   and load ewiki.php 'library'
 include("config.php");
 
 // check for table
 
$db_exists = qa_db_read_one_value(qa_db_query_sub("SHOW TABLES LIKE '^ewiki'"),true);

if(!$db_exists) {
    if(qa_get_logged_in_level()<QA_USER_LEVEL_ADMIN)
        die('Wiki is not set up yet... ask your admin to set it up');
     ewiki_database("INIT", array());
     if ($dh = @opendir($path=EWIKI_INIT_PAGES)) {
        while ($filename = readdir($dh)) {
           if (preg_match('/^(['.EWIKI_CHARS_U.']+['.EWIKI_CHARS_L.']+\w*)+/', $filename)) {
              $found = ewiki_database("FIND", array($filename));
              if (! $found[$filename]) {
                 $content = implode("", file("$path/$filename"));
                 ewiki_scan_wikiwords($content, $ewiki_links, "_STRIP_EMAIL=1");
                 $refs = "\n\n" . implode("\n", array_keys($ewiki_links)) . "\n\n";
                 $save = array(
                    "id" => "$filename",
                    "version" => "1",
                    "flags" => "1",
                    "content" => $content,
                    "author" => ewiki_author("ewiki_initialize"),
                    "refs" => $refs,
                    "lastmodified" => filemtime("$path/$filename"),
                    "created" => filectime("$path/$filename")   // (not exact)
                 );
                 ewiki_database("WRITE", $save);
              }
           }
        }
        closedir($dh);
        ewiki_log("initializing database", 0);
     }
     else {
        die("<b>ewiki error</b>: could not read from directory ". realpath($path) ."<br>\n");
     }
}

#-- this is the actual call to generate the output for the current wiki
 #   page, but we buffer it now and print its output later
$ewiki = ewiki_page();

?><?php

 #-- color scheme
 list($color1, $color2) = //array("992211", "ffcc88");
                          //array("ffaa55", "ffcc88");
                          //array("994411", "ffcc00");
                          array("994411", "ffbb44");
       

?>

<div id="wiki_container">

    <!--HR STYLE="display:none; color:#dd5522; height:1px; margin:0px; padding:0px;" WIDTH="100%" NOSHADE COLOR="#dd5522"-->

    <div id="wiki-main">
        <?php

         #-- output previously generated page
         echo($ewiki);

        ?>
    </div>
       <!--
       <?php
          include("fragments/blocks/mainmenu.php");
       ?>
       -->
    <?php

     if(qa_get_logged_in_level()>=QA_USER_LEVEL_ADMIN && false) { // disabled

    ?>
       <div class="rbr">
       <b>database <A HREF="./wiki/tools">tools</A></b><br>
       » <A HREF="./wiki/tools/flags">set page flags</A><BR>
       » <A HREF="./wiki/tools/backup">backup util</A><BR>
       » <A HREF="./wiki/tools/restore">restore util</A><BR>
       » <A HREF="./wiki/tools/remove">page deletion</A><BR>
       » <A HREF="./wiki/tools/holes">make holes</A><BR>
       » <A HREF="./wiki/tools/convertdb">convert db</A><BR>
       » <A HREF="./wiki/tools/checklinks">check links</A><BR>
       </div>
    <?php

    }

    ?>

       <div class="rbr">
       <b>internal pages</b><ul>
       <?php
         foreach ($ewiki_plugins["page"] as $id=>$pf) {
            echo '<li><A HREF="' . ewiki_script("", $id) . '">' . $id . '</A></li>' . "\n";
         }
       ?>
       </div>

       <?php
         if (function_exists("calendar_exists") && calendar_exists()) {
            echo '<div class="rbr">';
            echo  calendar();
            echo '</div>';
         }
       ?>

    <?php
        if (file_exists($sf = "local/sftail.php")) {
         $sfsummary = "full";
         include($sf);
      }
    ?>


</div>

