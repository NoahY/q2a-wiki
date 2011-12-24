<?php
  include("t_config.php");
?>
<html>
<head>
<title>delete ewiki pages</title>
</head>
<body BGCOLOR="#778899">
<h3>delete ewiki pages</h3>
<?php


  if (empty($_REQUEST["remove"])) {

     echo "
	Note that only <b>unreferenced pages</b> will be listed here. And
	because the ewiki engine itself does only limited testing if a page is
	referenced it may miss some of them here.<br>
        If you however empty a page first, it will get listed here too.
        Various other database diagnostics are made as well.<br><br>\n";


     $result = ewiki_database("GETALL", array("version"));

     $selected = array();

     if (@$_REQUEST["listall"]) {
        while ($row = $result->get()) {
           $selected[$row["id"]] = "listall <br>";
        }
     }

    while ($page = $result->get()) {


        $id = $page["id"];
        $page = ewiki_database("GET", array("id"=>$id));
        $flags = $page["flags"];

        if (!strlen(trim(($page["content"])))) {
           @$selected[$id] .= "EMPTY <br>";
        }

        $result = ewiki_database("SEARCH", array("content" => $id));
        if ($result && $result->count()) {
           $check2 = 1;
           while ($row = $result->get()) {
              $check = ewiki_database("GET", array("id"=>$row["id"]));
              $check = strtolower($check["content"]);
              $check2 &= (strpos($check, strtolower($id)) !== false);
#echo "rc({$row['id']})==>($id): $check2 <br>";
           }
           $check = $check2;
        }
        if (empty($check)) {
           @$selected[$id] .= "UNREFerenced <br>";
        }

        if ($flags & EWIKI_DB_F_DISABLED) {
           @$selected[$id] .= "disabled_page <br>";
        }

        if (($flags & 3) == 3) {
           @$selected[$id] .= "errFLAGS(bin<b>+</b>txt) <br>";
        }

        if (!($flags & 3)) {
           @$selected[$id] .= "errFLAGS(notype) <br>";
        }

        if ($flags & EWIKI_DB_F_HTML) {
           @$selected[$id] .= "warning(HTML) <br>";
        }

        if (($flags & EWIKI_DB_F_READONLY) && !($flags & EWIKI_DB_F_BINARY)) {
           @$selected[$id] .= "readonly <br>";
        }

        if (($flags & EWIKI_DB_F_READONLY) && ($flags & EWIKI_DB_F_WRITEABLE)) {
           @$selected[$id] .= "errFLAGS(readonly<b>+</b>writable) <br>";
        }

        if (strlen($page["content"]) >= 65536) {
           @$selected[$id] .= "size &gt;= 64K <br>";
        }

        if (strpos($page["refs"], "\nDeleteMe\n")!==false) {
           @$selected[$id] .= "<tt>DeleteMe</tt> <br>";
        }

     }
     


     echo '<FORM ACTION="t_remove.php" METHOD="POST" ENCTYPE="multipart/form-data">';
     echo '<INPUT type="submit" NAME="listall" VALUE="listall">';
     echo '<TABLE BORDER="0" CELLSPACING="3" CELLPADDING="2" WIDTH="500">' . "\n";
     echo "<TR><TH>page name</TH><TH>error / reason</TH></TR>\n";

     foreach ($selected as $id => $reason) {
        
        echo '<TR><TD BGCOLOR="#EEEEEE">';

        #-- checkbox
        echo '<INPUT TYPE="checkbox" VALUE="1" NAME="remove[' . rawurlencode($id) . ']">&nbsp;&nbsp;';

        #-- link & id
        if (strpos($id, EWIKI_IDF_INTERNAL) === false) {
           echo '<A HREF="' . ewiki_script("", $id) . '">';
        }
        else {
           echo '<A HREF="' . ewiki_script_binary("", $id) . '">';
        }
        echo htmlentities($id) . '</A></TD>';

        #-- print reason
        echo '<TD BGCOLOR="#EEEEEE">' . $reason . "</TD>";

        echo "</TR>\n";

     }

     echo '</TABLE><BR><INPUT type="submit" VALUE="&nbsp; delete selected pages &nbsp;"></FORM>';

  }
  else {

     echo "<UL>\n";
     foreach ($_REQUEST["remove"] as $id => $uu) {

        $id = rawurldecode($id);

        echo "<li>purging »".htmlentities($id)."«...</li>";

        $data = ewiki_database("GET", array("id"=>$id));
        for ($version=1; $version<=$data["version"]; $version++) {

           ewiki_database("DELETE", array("id"=>$id, "version"=>$version));

        }
        
     }
     echo "</UL>\n";

  }

?>