<?php

  # this is the configuration for the ewiki page and database tools
  #     (which may need to be distinct from your main ewiki config)
  #


  #-- tools/ are run standalone?
  if (!function_exists("ewiki_database")) {


     #-- simplest authentication:
     include("../fragments/funcs/auth.php");


     #-- normalize cwd (stupid approach)
     if (!file_exists($LIB="ewiki.php")) {
        chdir("..");
        define("EWIKI_SCRIPT", "../?");
        define("EWIKI_SCRIPT_BINARY", "../?binary=");
     }


     #-- open db connection, load 'lib'
     include("./config.php");


  }


  #-- we now seem to run from inside ewiki (via the StaticPages plugin e.g.)
  else {

     #-- this terminates ewiki from within the spages plugin
     if (!EWIKI_PROTECTED_MODE || !ewiki_auth($id, $data, $action, 0, 2) || ($ewiki_ring>0) || !isset($ewiki_ring)) {
        die("Only the administrator can use this function.");
     }

  }



?>