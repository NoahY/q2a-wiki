<?php
  include("t_config.php");
?>
<html>
<head>
<title>database conversion (wiki engine transition)</title>
</head>
<body BGCOLOR="#778899">
<h3>database conversion</h3>
<?php

 if (empty($_REQUEST["convert"])) {

?>
  This tool can be used to convert an existing wiki database with
  all its pages to the ewiki database and page format. It is currently
  implemented for PhpWiki 1.3.x versions only.
  <br>
  It assumes the phpwiki tables are in the same database, as you configured
  this tool (and the core ewiki.php) to access per default.

  <br>
  <br>

  <FORM ACTION="t_convertdb.php" METHOD="POST">
    convert from
    <SELECT NAME="from_type">
     <OPTION VALUE="phpwiki13">PhpWiki 1.3.x</OPTION>
    </SELECT>
    to
    <SELECT NAME="to_type">
     <OPTION VALUE="ewiki">ErfurtWiki R1.00</OPTION>
    </SELECT>
    <br>
    <br>
    <INPUT TYPE="checkbox" NAME="convert_markup" VALUE="1"> try to convert wiki markup
    <br>
    <br>
    <INPUT TYPE="checkbox" NAME="overwrite" VALUE="1"> renew pages that already exist in ewiki database
    <br>
    <br>
    <INPUT TYPE="submit" name="convert" value="start conversion">
  </FORM>
 
<?php

 }
 elseif ($_REQUEST["to_type"] == "ewiki") {

    #-- creating ewiki database
    echo "<u>creating</u> ErfurtWiki database... ";
    ewiki_database("INIT", -1);
    echo "<br><br>\n";

    #-- including db_phpwiki
    switch ($sw=$_REQUEST["from_type"]) {

       case "phpwiki13":
            include("plugins/db_phpwiki13.php");
            if ($_REQUEST["convert_markup"]) {
               include("plugins/markup_phpwiki.php");
            }
            break;
          
       default:
            echo "unknown wiki database type '$sw' to convert from!<br>";
            die();
            break;
    }

    #-- restore/save db interface
    $from_db = $ewiki_plugins["database"][0];
    unset($ewiki_plugins["database"][0]);

    #-- go thru files
    echo "<u>copying</u> pages:<br>\n";

    $result = ewiki_database("GETALL", array(), 0, 0, $from_db);
    while ($row = $result->get()) {

       $id = $row["id"];
       echo "'$id',\n";

       #-- read
       $new = ewiki_database("GET", array("id"=>$id), 0, 0, $from_db);

       #-- prev
       $old = ewiki_database("GET", array("id"=>$id));

       #-- overwrite?
       if ($_REQUEST["overwrite"]) {
          $new["version"] = 1 + $old["version"];
       }
       else {
          $new["version"] = 1;
          if ($old["version"] >= 1) {
             continue;
          }
       }

       #-- markup conversion
       if ($pf_a = $ewiki_plugins["format_source"]) {
          foreach ($pf_a as $pf) {
             $pf($new["content"]);
          }
       }

       #-- insert
       ewiki_database("WRITE", $new);
       
    }

    #-- fin
    echo "<br><br>done\n";

 }
 else {

    echo "Cannot do that, sorry!";

 }

?>