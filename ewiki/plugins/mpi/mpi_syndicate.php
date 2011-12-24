<?php

/*
   Allows to embed a RSS feed into a page. It retrieves and decodes the
   external URL and caches results in the database for later reuse.

   <?plugin Syndicate url="http://example.com/rss.php" ?>
*/

define("EWIKI_MPI_SYNDICATE_INVALIDATE", 70000);



$ewiki_plugins["mpi"]["syndicate"] = "ewiki_mpi_syndicate";

function ewiki_mpi_syndicate($action, &$args, &$iii, &$s) {

   global $ewiki_id;

   $o = "";

   #-- fetch URL
   if (($url = $args["url"]) || ($url = $args["href"]) || ($url = $args["src"])) {
      $data = ewiki_database("GET", array("id"=>$url));
      if (!$data || ($data["lastmodified"]+EWIKI_MPI_SYNDICATE_INVALIDATE<time())) {
         $r = array();

         #-- load
         ini_set("user_agent", $ua="ewiki/".EWIKI_VERSION . " (mpi_syndicate)");
         if (function_exists("stream_context_create")) {
            $context = stream_context_create(array(
               "http" => array("method"=>GET,
                "header" => "User-Agent: $ua\r\n"
                  ."Accept: text/x.rss+xml; version=2.0, text/x.rss+xml; version=0.92\r\n"
            )));
            $f = fopen($url, "r", false, $context);
         }
         else {
            $f = fopen($url, "r");
         }
         if ($f) {
            $xml = fread($f, 1<<20);
            fclose($f);
         }

         #-- analyze
         if ($xml) {
            if (strpos($xml, '<?xml version="1.0"') !== false) {
               $i = 0;
               while ($l = strpos($xml, "<item")) {
                  $current = substr($xml, 0, $l);
                  $xml = substr($xml, $l);
                  if (preg_match('#<title.*?>([^<>]+)</title>#s', $chunk, $uu)) {
                     $r[$i][0] = $uu[1];
                  }
                  if (preg_match('#<link.*?>([\w+]://[^<>]+)</link>#s', $chunk, $uu) || preg_match('#<guid.*?>([\w]+://[^<>]+)</guid>#s', $chunk, $uu)) {
                     $r[$i][1] = $uu[1];
                  }
                  if (preg_match('#<description.*?>([^<>]+)</description>#s', $chunk, $uu)) {
                     preg_match_all("/([-_\w]+)/", $uu[1], $uu);
                     $r[$i][2] = implode(" ", $uu[1]);
                  }
               }
            }
         }
         $data = array(
            "id" => $url,
            "version" => ($data["version"] ? $data["version"] : 0) + 1,
            "flags" => EWIKI_DB_F_BINARY,
            "auther" => ewiki_author("SyndicateMpi"),
            "created" => $data["created"] ? $data["created"] : time(),
            "lastmodified" => time(),
            "refs" => "\n\n$ewiki_id\n\n",
            "meta" => "",
            "content" => serialize($r),
         );
         ewiki_database("WRITE", $data);
      }
   }

   #-- insert as html into current page
   if (($r = $data["content"]) && ($r = unserialize($r))) {
      $o = "<b><a href=\"$r[0][1]\" title=\"$r[0][2]\">$r[0][0]</a></b><br>\n";
      unset($r[0]);
      foreach ($r as $line) {
         $o .= "<a href=\"$line[1]\">$line[0]</a> $r[0][2]<br>\n";
      }
      return($o);
   }
}


?>