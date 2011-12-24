<?php

/*
   Adds an input box on the edit/ page, that allows users to set an
   author name individually. Would allow to override the username
   string, if one was already set by ProtectedMode plugins (but of
   course this is nor permission problem).
*/

define("EWIKI_UP_AUTHOR_NAME", "author_name");

#-- <input> 
$ewiki_plugins["edit_form_append"][] = "ewiki_aedit_authorname";
function ewiki_aedit_authorname($id, &$data, $action) {

   $var = EWIKI_UP_AUTHOR_NAME;
   return(ewiki_t(<<< EOT
<br>
 _{set the AuthorName to} <input size="20" name="$var" value="$GLOBALS[ewiki_author]">
<br>
EOT
   ));
}


#-- store as cookie
if ($_REQUEST[EWIKI_UP_AUTHOR_NAME]) {
   $ewiki_author = $_REQUEST[EWIKI_UP_AUTHOR_NAME];
   if ($_COOKIE[EWIKI_UP_AUTHOR_NAME] != $ewiki_author) {
     setcookie(EWIKI_UP_AUTHOR_NAME, $ewiki_author, time()+2700);
   }
}

?>