<?php
  include("t_config.php");
?>
<html>
<head>
<title>edit ewiki page flags</title>
</head>
<body BGCOLOR="#778899">
<h3>ewiki page flags</h3>
<?php


  $FD = array(
     EWIKI_DB_F_TEXT => "TXT",
     EWIKI_DB_F_BINARY => "BIN",
     EWIKI_DB_F_DISABLED => "OFF",
     EWIKI_DB_F_HTML => "HTM",
     EWIKI_DB_F_READONLY => "RO",
     EWIKI_DB_F_WRITEABLE => "WR",
  );

  if (empty($_REQUEST["set"])) {

     $result = ewiki_database("GETALL", array("version", "flags"));

     echo '<FORM ACTION="t_flags.php" METHOD="POST" ENCTYPE="multipart/form-data">';
     echo '<TABLE BORDER="0" CELLSPACING="3" CELLPADDING="2" WIDTH="96%">' . "\n";

     while ($row = $result->get()) {
        $id = $row["id"];

        $data = ewiki_database("GET", $row);

        echo '<TR><TD BGCOLOR="#EEEEEE" WIDTH="40%">';
        if ($data["flags"] & EWIKI_DB_F_TEXT) {
           echo '<A HREF="' . ewiki_script("", $id) . '">';
        }
        else {
           echo '<A HREF="' . ewiki_script_binary("", $id) . '">';
        }
        echo htmlentities($id) . '</A><small>' .  (".".$row["version"]) . '</small></TD>';

        echo '<TD BGCOLOR="#EEEEEE"><small>';
        foreach ($FD as $n=>$str) {
           echo '<INPUT TYPE="checkbox" NAME="set['. rawurlencode($id)
                . '][' . $n . ']" VALUE="1" '
                . (($data["flags"] & $n) ? "CHECKED" : "")
                . '>'.$str. ' ';
        }
        echo "</small></TD>";

        echo "</TR>\n";

     }

     echo '</TABLE><INPUT type="submit" VALUE="&nbsp;    change settings    &nbsp;"></FORM>';

  }
  else {

     foreach($_REQUEST["set"] as $page=>$fa) {

        $page = rawurldecode($page);

        $flags = 0;
        $fstr = "";
        foreach($fa as $num=>$isset) {
           if ($isset) {
              $flags += $num;
              $fstr .= ($fstr?",":""). $FD[$num];
           }
        }

        echo "· ".htmlentities($page)." ({$flags}=<small>[{$fstr}]</small>)";

        $data = ewiki_database("GET", array("id" => $page));

        if ($data["flags"] != $flags) {
           $data["flags"] = $flags;
           $data["author"] = "ewiki-tools, " . ewiki_author();
           $data["version"]++;
           ewiki_database("WRITE", $data);
           echo " <b>[set]</b>";
        }
        else {
           echo " [not changed]";
        }

        echo "<br>\n";

     }

  }


  function strong_htmlentities($str) {
     return preg_replace('/([^-., \w\d])/e', '"&#".ord("\\1").";"', $str);
  }

?>