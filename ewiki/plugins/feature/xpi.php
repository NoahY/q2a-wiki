<?php

/*
   If you load this plugin, you'll get WikiFeatures:AutomaticFeatureInstall
   with ewiki unique ".xpi" plugins, that can simply be uploaded through a
   web interface. The name xpi was borought from the Mozilla project, the
   actual files are very different.

   To install plugins, one must provide a correct password. Most types of
   plugins can come in .xpi form. The .xpi must be generated using the
   tools/mkxpi utility.

   In conjunction with phpjs and the WikiApi plugin, this also allows for
   installation of .jpi plugins, which contain JavaScript code, that is
   compiled into sandboxed PHP script - such plugins could therefore be
   uploaded and installed by anybody, because they're guaranteed to be
   harmless to server security.
*/


define("EWIKI_DB_F_EXEC", 1<<17);
define("XPI_DB", "system/xpi/registry");
define("XPI_EVERYBODY_JPI", 1);

$ewiki_config["xpi_pw"] = array(     // plain password or md5 hash
   "*",
#  "password",
#  "3389dae361af79b04c9c8e7057f60cc6"
);
$ewiki_config["xpi_dirs"] = array(
   "http://erfurtwiki.sourceforge.net/downloads/contrib-add-ons/xpi/",
   "http://erfurtwiki.sourceforge.net/?XpiPlugins",
#  "http://erfurtwiki.sf.net/xpi/",
#  "http://erfurtwiki.sourceforge.net/?JpiPlugins",
);

$ewiki_plugins["page"]["PlugInstall"] = "xpi_install";
$ewiki_plugins["handler"][] = "xpi_exec";
$ewiki_plugins["init"][] = "xpi_init_plugins";


#-- executes pages with the _EXEC flag set
function xpi_exec($id, $data, $action) {

   global $ewiki_id, $ewiki_title, $ewiki_action, $ewiki_data,
      $ewiki_config, $ewiki_t, $ewiki_plugins, $_EWIKI;

   if ($data["flags"] & EWIKI_DB_F_EXEC) {
      eval($data["content"]);
      return($o);
   }
}


#-- runs plugins at init time
function xpi_init_plugins() {

   global $ewiki_id, $ewiki_title, $ewiki_action, $ewiki_data,
      $ewiki_config, $ewiki_t, $ewiki_plugins, $_EWIKI;

   #-- load xpi registry
   $conf = ewiki_database("GET", array("id"=>XPI_DB));
   if ($conf && ($conf["flags"] & EWIKI_DB_F_SYSTEM)
   && ($conf = unserialize($conf["content"]))) {

      $eval_this = "";

      #-- collect xpi code, execute it
      foreach ($conf as $xpi) {
         if ($xpi["state"] && ($xpi["type"] != "page")) {
            $d = ewiki_database("GET", array("id"=>$xpi["id"]));
            if ($d && ($d["flags"] & EWIKI_DB_F_EXEC)) {
               $eval_this .= $d["content"];
            }
         }
      }
      eval($eval_this);
   }
}


#-- provides the upload <form> and installation procedures for .xpi
function xpi_install($id, $data, $action) {

   global $ewiki_config, $ewiki_plugins;

   $o .= ewiki_make_title($id, $id, 2);
   $o .= '<form action="'.$_SERVER["REQUEST_URI"].'" method="POST" enctype="multipart/form-data">'
      . '<input type="hidden" name="id" value="'.htmlentities($id).'">';

   #-- check pw
   $o .= '<div class="xpi-login">';
   $pw = $_REQUEST["xpi_pw"];
   $access = !empty($pw) && ($pw != "*") && (
      in_array($pw, $ewiki_config["xpi_pw"]) ||
      in_array(md5($pw), $ewiki_config["xpi_pw"])
   );
   if (isset($_POST["xpi_pw"]) && ($_COOKIE["xpi_pw"] != $pw) || ($_REQUEST["xpi_logout"])) {
      $pw=$_POST["xpi_pw"];
      if ($access || !$pw) {
         setcookie("xpi_pw", $pw);
         $access = $access && $pw;
      }
   }
   $jpi_access = XPI_EVERYBODY_JPI || 1
      && function_exists("js_compile")
      && function_exists("jsa_generate");

   #-- login form
   if (!$access) {
      if ($jpi_access) {
         $o .= "On this Wiki everybody is allowed to install safe .jpi (phpjs) plugins. ";
      }
      $o .= "To install click-and-run .xpi plugins you must be administrator and provide the correct password. ";
      $o .= <<<EOT
<p><b>access password</b><br>
<input type="password" name="xpi_pw" size="16">
<br><input type="submit" value="log in">
</p>
EOT;
   }
   else {
      $o .= '<input type="submit" name="xpi_logout" value="log out">';
   }
   $o .= "</div>\n";


   #-- load .xpi plugin registry -----------------------------------------
   $conf = ewiki_database("GET", array("id"=>XPI_DB));
   if (!$conf || !($conf["flags"] & EWIKI_DB_F_SYSTEM)) {
      $conf = ewiki_new_data(XPI_DB, EWIKI_DB_F_SYSTEM);
      $conf["version"] = 0;
      $c = array();
   }
   else {
      $c = unserialize($conf["content"]);
   }


   #-- upload & install ----------------------------------------------------
   if ($access || $jpi_access) {

      #-- upload (.xpi filename received)
      $o .= '<div class="xpi-upload"><h4>.xpi plugin upload</h4>';
      if ($_REQUEST["install_xpi"]) {
         $xpi_install_fn = $fn = $_FILES["xpi_file"]["tmp_name"];
      }
      elseif ($uu = $_REQUEST["install_remote_xpi"]) {
         $xpi_install_fn = $uu;
      }

      #-- show a remote .xpi install directory
      if ($url = $_REQUEST["xpidir"]) {
         $r = array();
         if ($urls = ewiki_util_getlinks($url, '[^\"\'\>\s]+?\.[jx]pi')) {
            foreach ($urls as $fn) {
               if ($xpi = xpi_read($fn)) {
                  $xpi["XPI"] = $xpi["code"] = NULL;
                  if (!$access && $xpi["JPI"]) { 
                     continue;
                  }
                  $r[$fn] = $xpi;
               }
            }
         }
         $o .= "install .xpi plugins from remote directory<br><a href=\"$url\">$url</a>:<br>";
         $o .= '<table border="0" cellspacing="1" cellpadding="2">';
         foreach ($r as $fn=>$xpi) {
            $o .= "\n".'<tr><td colspan="3">'
               . '<input type="submit" name="install_remote_xpi" value="'
                 .htmlentities($fn).'" title="'.$xpi["id"].'">'
               . '</td></tr><tr>'
               . "<td class=\"xs-id\">[{$xpi[type]}] {$xpi[id]} {$xpi[version]}</td>"
               . "<td>{$xpi[description]}<br></td>"
               . "<td>{$xpi[author]}, {$xpi[license]}</td>"
               . '</tr>';
         }
         $o .= '</table><br>';
      }

      #-- install
      if ($xpi_install_fn) {
         if ($xpi = xpi_read($xpi_install_fn, "rb")) {

            #-- evaluate plugin
            if (strlen($xpi["id"]) < 3) {
               $o .= "missing .xpi header";
            }
            elseif (!$access && (!$jsi_access || !($xpi["type"]=="jpi"))) {
               $o .= "<b>ERROR</b>: You don't have permission to install this type of plugin.<br><br>";
            }
            else {
               $xpi["state"] = 1;

               #-- create new database entry
               $new = ewiki_new_data($xpi["id"], EWIKI_DB_F_SYSTEM|EWIKI_DB_F_EXEC);
               $new["content"] = $xpi["code"];
               unset($xpi["code"]);

               #-- compile .jpi plugins
               if ($xpi["type"]=="jpi") {
                  $xpi["type"] = "page";
                  js_compile($xpi["code"]);
                  $xpi["code"] = NULL;
                  $xpi["code"] = jsa_generate();
                  if ($GLOBALS["js_err"]) {
                     ewiki_log("failed compiling .jpi plugin '$xpi[id]'", 0);
                     return("$o <b>ERROR</b>: broken .jpi plugin! </div></form>");
                  }
               }

               #-- check for old version
               if ($access) {
                  $old = ewiki_database("GET", array("id"=>$new["id"]));
                  if ($old["version"]) {
                     $new["version"] = $old["version"]+1;
                     $o .= "(overwriting plugin [version {$old[version]}])<br>";
                  }
               }

               #-- store plugin into database
               if (ewiki_database("WRITE", $new)) {
                  ewiki_log("successfully installed .xpi plugin '$xpi[id]'", 0);
                  $o .= ($xpi["type"] == "page") ? ewiki_link($xpi[id]) : "<b>{$xpi[id]}</b>";
                  $o .= " plugin stored. ";

                  #-- update .xpi registry
                  $c[$xpi["id"]] = $xpi;
                  $conf["content"] = serialize($c);
                  ewiki_data_update($conf);
                  $conf["version"]++;
                  ewiki_database("WRITE", $conf);
               }
               else {
                  $o .= "<b>error</b> saving";
                  ewiki_log("error installing .xpi/.jpi plugin '$xpi[id]'", 0);
               }

            }
         }
         else {
            $o .= "not a valid .xpi plugin";
   #         $o .= "wrong/inacceptable xpi plugin type/version";
         }
      }
      else {
         #-- plugin upload <form>
         $o .= <<<EOT
<b>Warning</b>: before uploading an extension plugin, you should check its
source, because you'll otherwise may open big security leaks in your
installation.
<br><br>
<input type="file" name="xpi_file">
<br>
<input type="submit" name="install_xpi" value="install">
<br><br>
EOT;
         $o .= 'Or install a plugin from one of the registered plugin directories:<br>';
         foreach ($ewiki_config["xpi_dirs"] as $s) {
            $o .= '<input type="submit" name="xpidir" value="'.htmlentities($s).'"><br>';
         }
      }
      $o .= '</div><br>';
   }


   #-- plugin control ---------------------------------------------------
   $o .= '<div class="xpi-settings"><h4>plugin control</h4>';

   #-- delete plugins
   if ($access && ($uu = $_REQUEST["xpi_rm"])) {
      foreach ($uu as $id=>$del) {
         if ($del) {
            $id = rawurldecode($id);
            $dat = ewiki_database("GET", array("id"=>$id));
            $vZ = $dat["version"];
            for ($v=1; $v<=$vZ; $v++) {
               ewiki_database("DELETE", array("id"=>$id, "version"=>$v));
            }
            unset($c[$id]);
            $vZ += 0;
            $o .= "<b>i</b>: Purged $vZ versions of '$id' and removed xpi registry entry.<br><br>";
            ewiki_log("uninstalled .xpi/.jpi plugin '$id'", 0);
         }
      }
      $_REQUEST["setup_xpi"]=1;
   }

   #-- update config settings
   if ($_REQUEST["setup_xpi"]) {

      if ($access) {
         foreach ($c as $id=>$uu) {
            $c[$id]["state"] = $_REQUEST["xpi_set"][rawurlencode($id)] ?1:0;
         }

         $conf["content"] = serialize($c);
         ewiki_data_update($conf);
         $conf["version"]++;
         ewiki_database("WRITE", $conf);
      }
      else {
         $o .= "You have no privileges to change the status of installed .xpi plugins.<br>\n";
      }
   }

   #-- enable/disable checkboxes
   $o .= '<table border="0" cellspacing="1" cellpadding="2">';
   foreach ($c as $dat) {
      $enabled = ($dat["state"]==1);
      $hard = ($dat["type"]=="page");
      $title = $hard ? ewiki_link($dat["id"]) : $dat["id"];
      $o .= '<tr>'
         . '<td><tt>' . $dat["type"] . '</tt></td>'
         . '<td class="xs-check"><input type="checkbox" name="xpi_set['.rawurlencode($dat["id"])
         . ']" value="1"' . ($enabled?" checked":"")
         . ($hard?" disabled":"") . '></td>'
         . '<td class="xs-id">' . $title . '</td>'
         . '<td><small>' . htmlentities($dat["description"]) . '</small></td>'
         . '<td>' . $dat["author"] . ", " . $dat["license"] . '</td>'
         . '<td class="xs-check"><input type="submit" name="xpi_rm['.rawurlencode($dat["id"]).']" value="rm" title="uninstall plugin"'.($access?"":" disabled").'></td>'
         . '</tr>';
   }
   $o .= '</table>';
   $o .= '<br><input type="submit" name="setup_xpi" value="configure"'.($access?"":" disabled").'>';
   $o .= '</form></div>';

   
   return($o);
}


function xpi_read($fn) {
   if ($f = gzopen($fn, "rb")) {
      $xpi = unserialize(gzread($f, 1<<17));
      gzclose($f);
      if (($xpi["XPI"]=="0.1")
      && (($xpi["engine"]=="ewiki") || ($xpi["type"]=="jpi"))
      && $xpi["id"] && $xpi["type"] && $xpi["code"]) {
         return($xpi);
      }
   }
}


function ewiki_util_getlinks($url, $regex='.+?') {
   $r = array();

   if ($html = @file($url)) {
      $html = implode("", $html);

      $url_b = substr("$url", 0, strrpos($url, "/"));
      $url_s = substr("$url", 0, strpos($url, "/", 10));

      preg_match_all('#<a[^>]+href=["\']?('.$regex.')["\'\s>]#i', $html, $uu);
      foreach ($uu[1] as $fn) {
         if ($fn[0] == "/") {
            $fn = $url_s . $fn;
         }
         elseif (strpos($fn, "://")) {
         }
         else {
            $fn = $url_b . "/" . $fn;
         }
         $r[] = $fn;
      }
   }
   return($r);
}


?>