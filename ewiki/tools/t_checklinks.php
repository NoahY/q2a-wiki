<?php
  include("t_config.php");
?>
<html>
<head>
<title>check http:// links of a wiki page</title>
</head>
<body BGCOLOR="#778899">
<h3>link check</h3>
<?php


  if (empty($_REQUEST["page"])) {

     echo '
This tool checks all http:// links for availability, afterwards resaves the
wiki page with the dead links marked for easier editing.
<br><br>
<form action="checklinks.php" method="POST">
<input name="page" size="30"><br><br>
<input type="submit" value="check http:// links">
</form>
';

  }
  else {

     $id = $_REQUEST["page"];

     $get = ewiki_database("GET", array("id" => $id));
     $content = $get["content"];
     
     preg_match_all('_(http://[^\s"\'<>#,;]+[^\s"\'<>#,;.])_', $content, $links);
     $badlinks = array();
     foreach ($links[1] as $href) {

        $d = @implode("", @file($href));
        
        if (empty($d) || !strlen(trim($d)) || stristr("not found", $d) || stristr("error 404", $d)) {
           echo "[DEAD] $href<br>";
           $badlinks[] = $href;
        }
        else {
           echo "[OK] $href<br>";
        }
     }

     #-- replace dead links
     foreach ($badlinks as $href) {
        $content = preg_replace("\377^(.*$href)\377m", ' µµ__~[OFFLINE]__µµ   $1', $content);
     }

     #-- compare against db content
     if ($content != $get["content"]) {

        $get["content"] = $content;
        $get["version"]++;
        $get["author"] = ewiki_author("ewiki_checklinks");
        $get["lastmodified"] = time();

        ewiki_database("WRITE", $get);
     }
  
  }


?>