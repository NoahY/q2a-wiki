<?php

/*

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

	class qa_wiki_page {
		
		var $directory;
		var $urltoroot;
		

		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
			$this->urltoroot=$urltoroot;
		}

		
		function suggest_requests() // for display in admin interface
		{	
			return array(
				array(
					'title' => 'Wiki',
					'request' => 'wiki',
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				),
			);
		}

		
		function match_request($request)
		{
			if (strpos($request,'wiki') === 0)
				return true;

			return false;
		}

		
		function process_request($request)
		{


			$qa_content=qa_content_prepare();

			$qa_content['title']='<a href="'.qa_path_html('wiki').'">Wiki</a>';
		
			$tools = array(
				'wiki/tools' => 'index.html',
				'wiki/tools/flags' => 't_flags.php',
				'wiki/tools/backup' => 't_backup.php',
				'wiki/tools/restore' => 't_restore.php',
				'wiki/tools/remove' => 't_remove.php',
				'wiki/tools/holes' => 't_holes.php',
				'wiki/tools/convertdb' => 't_convertdb.php',
				'wiki/tools/checklinks' => 't_checklinks.php'
       		);
       		
			global $ewiki_request;
			$ewiki_request = array_merge($_GET?$_GET:array(),$_POST?$_POST:array());
			global $ewiki_links, $ewiki_plugins, $ewiki_ring, $ewiki_t, $ewiki_errmsg, $ewiki_data, $ewiki_title, $ewiki_id, $ewiki_action, $ewiki_config;
			ob_start();
			if(isset($tools[$request]) && qa_get_logged_in_level()>=QA_USER_LEVEL_ADMIN)
				include('ewiki/tools/'.$tools[$request]);
			else
				include("ewiki/index.php");
			$contents = ob_get_contents();
			ob_end_clean();
		   
			$qa_content['custom']=$contents;

			return $qa_content;
		}
		
	}
	

/*
	Omit PHP closing tag to help avoid accidental output
*/