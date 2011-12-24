<?php

/*
   Allows to retrieve pages in raw Wiki source format using an URL
   like 'http://example.com/wiki/?id=raw/ThatPage'
*/

$ewiki_config["action_links"]["view"]["raw"] = "raw";
$ewiki_config["action_links"]["info"]["raw"] = "raw";

$ewiki_plugins["action"]["raw"] = "ewiki_action_raw";
function ewiki_action_raw($id, &$data, $action) {

   #-- MIME type
   header('Content-Type: text/wiki; variant="ErfurtWiki"; charset="ISO-8859-1"');

   #-- wiki magic number, http://www.emacswiki.org/cgi-bin/community/WikiMime
// echo "#!wiki ErfurtWiki http://erfurtwiki.sourceforge.net/?WikiMarkup\n";

   #-- output raw page text / source
   echo $data["content"];
   die();
}

?>