<?php

/*
  This script provides an RSS feed for your Wiki, if invoked from outside.
  It depends upon a "config.php" being available, and allows following
  QueryString parameters:
  
  * num=15, to limit feed items 
  * days=3 or seconds=250000, to limit feed items
  * updates=0 is the default, returns list of newly added pages
  * updates=1, to return updated pages
  * search=???

  You could therefore call this script with an URL like:
  http://example.net/wiki/rss.php?num=30&updates=1

  Please define EWIKI_SCRIPT_URL correctly, before using this!
*/

#-- setup
#error_reporting(0);
(@include("config.php")) || (include("../config.php"));


#-- description vars
$TITLE = EWIKI_PAGE_INDEX;
$URL = "http://".$_SERVER["SERVER_NAME"]."/";
$DESC = "A Wiki about some sort of things.";

#-- operational vars
$select = ($_REQUEST["updates"] ? "lastmodified" : "created");
($limit = $_REQUEST["num"])
  or ($limit = 15);
($frame = $_REQUEST["days"] * 24*60*60)
  or ($frame = $_REQUEST["seconds"])
  or ($frame = 250000);
$time = time();


#-- fetch from database
$r = array();
$result = ewiki_database("GETALL", array($select));
while ($row = $result->get(0, 0x0020)) {
   $r[$row["id"]] = $row[$select];
}

#-- sort, extract wanted entries
arsort($r);
$r = array_splice($r, 0, $limit);


#-- generate feed
$ITEMS = "";
foreach ($r as $id=>$t) {

   #-- stop when out of requested time frame
   if ($t+$frame<$time) {
      break;
   }

   #-- output
   $row = ewiki_database("GET", array("id"=>$id));
   preg_match_all("/([-_\w]+)/", $row["content"], $uu);
   $text = substr(implode(" ", $uu[1]), 0, 150);
   $link = ewiki_script("", $row['id'], 0, 0, "_XML=1", ewiki_script_url());
   $ITEMS .= "\n  <item>\n";
   $ITEMS .= "   <title>" . htmlentities($row['id']) . "</title>\n";
   $ITEMS .= "   <link>" . $link . "</link>\n";
   if ($text) {
      $ITEMS .= "   <description>" . htmlentities($text) . "</description>\n";
   }
   $ITEMS .= "   <pubDate>" . strftime($ewiki_t["C"]["DATE"], $row["created"]) . "</pubDate>\n";
   $ITEMS .= "  </item>\n";
}


#-- real output
echo <<<EOT
<?xml version="1.0" charset="ISO-8859-1"?>
<rss version="2.0">
<channel>
 <title>$TITLE</title>
 <link>$URL</link>
 <description>$DESC</description>
 <language>en</language>
$ITEMS
</channel>
</rss>
EOT;

//old: <!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN" "http://my.netscape.com/publish/formats/rss-0.91.dtd">

?>