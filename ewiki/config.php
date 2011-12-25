<?php

 /*
     This include script just opens the database connection;
     it is however an __examplary__ "configuration file" for
     ewiki.php (real config constants can be found in there!)
     If you have read and understood the README file, you may
     probably want to remove this example file!
 */

#-- change dir to where this config script is located
chdir(dirname(__FILE__));


#-- OPEN DATABASE for ewiki
#


 #-- only loaded if it exists
 @include("local/config.php");


 #-- predefine some of the configuration constants
 define("EWIKI_LIST_LIMIT", 25);
// define("EWIKI_SCRIPT", "?id=");
// define("EWIKI_SCRIPT_URL", "http://www.example.com/wiki/?id=");
 define("EWIKI_HTML_CHARS", 1);
// ...
 define("EWIKI_PRINT_TITLE", 1);
   #
   # Note: constants in PHP can be defined() just once, so defining them
   # here makes sense, the settings won't get overridden by the defaults
   # in "ewiki.php" - you should likewise copy other settings from there
   # to here, if you wish to change some of them


 #-- fix broken PHP setup
 if (!function_exists("get_magic_quotes_gpc") || get_magic_quotes_gpc()) {
    include("fragments/strip_wonderful_slashes.php");
 }
 if (ini_get("register_globals")) {
    include("fragments/strike_register_globals.php");
 }


 #-- load plugins
// include("plugins/init.php");     # you can disable this later
# include("plugins/pluginloader.php");
# include("plugins/email_protect.php");
 include("plugins/page/powersearch.php");
 include("plugins/page/pageindex.php");
# include("plugins/page/wordindex.php");
# include("plugins/page/aboutplugins.php");
# include("plugins/page/imagegallery.php");
# include("plugins/page/orphanedpages.php");
# include("plugins/spages.php");
# ewiki_spages_init("tools/");
# include("plugins/filter/search_highlight.php");
# include("plugins/appearance/fancy_list_dict.php");
# include("plugins/patchsaving.php");
# include("plugins/action/diff.php");
# include("plugins/action/like_pages.php");
# include("plugins/jump.php");
 include("plugins/notify.php");
 include("plugins/feature/imgresize_gd.php");
# include("plugins/feature/imgresize_magick.php");
# include("plugins/module/calendar.php");
# include("plugins/appearance/title_calendar.php");
# include("plugins/module/downloads.php");
# include("plugins/aview/downloads.php");
 include("plugins/markup/css.php");
# include("plugins/markup/smilies.php");
# include("plugins/markup/paragraphs.php");
# include("plugins/markup/footnotes.php");
# include("plugins/markup/rescuehtml.php");
# include("plugins/interwiki/intermap.php");
# include("plugins/linking/link_css.php");
# include("plugins/linking/link_icons.php");
# include("plugins/linking/link_target_blank.php");
# include("plugins/mpi/mpi.php");
# include("plugins/aview/linktree.php");
# include("plugins/aview/backlinks.php");
# include("plugins/filter/fun_wella.php");
# include("plugins/filter/fun_upsidedown.php");
# include("plugins/filter/fun_chef.php");
# include("plugins/page/textupload.php");
# include("plugins/auth/auth_perm_ring.php");
# include("plugins/userdb_registry.php");
# include("plugins/auth/auth_method_http.php");
# include("plugins/db/binary_store.php");
# ...


 #-- load library
 include("ewiki.php");



 #-- post-init plugins
# include("plugins/aview/posts.php");



?>