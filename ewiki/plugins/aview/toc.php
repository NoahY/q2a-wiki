<?php

/*
   Adds a CSS container with links to all listed headlines of the
   current page (but threshold for its activation is 3).

    .wiki .page-toc {
       width: 160px;
       float: right;
       border: 2px #333333 solid;
       background: #777777;
    }
*/



$ewiki_plugins["format_source"][] = "ewiki_toc_format_source";
$ewiki_plugins["format_final"][] = "ewiki_toc_view_prepend";


function ewiki_toc_format_source(&$src) {

   $toc = array();

   $src = explode("\n", $src);
   foreach ($src as $i=>$line) {

      if ($line[0] == "!") {
         $n = strspn($line, "!");
         if (($n <= 3) and ($line[$n]==" ")) {

            $text = substr($line, $n);
            $toc[$i] = '<a href="#line'.$i.'">'
                     . str_repeat("&nbsp;", $n-1) . "·"
                     . trim($text) . "</a>";

            $src[$i] = str_repeat("!", $n) . $text . " [#line$i]";

         }
      }
   }
   $src = implode("\n", $src);

   $GLOBALS["ewiki_page_toc"] = &$toc;
}


function ewiki_toc_view_prepend(&$html) {

   global $ewiki_page_toc;

   if (count($ewiki_page_toc) >= 3) {

      $html = '<div class="page-toc">'
         . implode("<br>\n", $ewiki_page_toc) . "</div>"
         . $html;
   }

   $ewiki_page_toc = NULL;
}


?>