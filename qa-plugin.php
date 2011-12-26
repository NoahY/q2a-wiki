<?php

/*
	Question2Answer (c) Gideon Greenspan

	http://www.question2answer.org/

	
	File: qa-plugin/example-page/qa-plugin.php
	Version: See define()s at top of qa-include/qa-base.php
	Description: Initiates example page plugin


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

/*
	Plugin Name: Wiki Page
	Plugin URI: 
	Plugin Description: Wiki plugin page
	Plugin Version: 0.1
	Plugin Date: 2011-12-23
	Plugin Author: NoahY
	Plugin Author URI: http://www.question2answer.org/
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.5
	Plugin Update Check URI: 
*/


	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}


	qa_register_plugin_module('page', 'qa-wiki-page.php', 'qa_wiki_page', 'Wiki Page');
	qa_register_plugin_module('module', 'qa-wiki-admin.php', 'qa_wiki_admin', 'Wiki Admin');
	qa_register_plugin_layer('qa-wiki-layer.php', 'Wiki Layer');

	qa_register_plugin_phrases('qa-wiki-lang-*.php', 'wiki_page');
	
	function qa_wiki_plugin_meta($oid) {

		qa_db_query_sub(
			'CREATE TABLE IF NOT EXISTS ^postmeta (
			meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			post_id bigint(20) unsigned NOT NULL,
			meta_key varchar(255) DEFAULT \'\',
			meta_value longtext,
			PRIMARY KEY (meta_id),
			KEY post_id (post_id),
			KEY meta_key (meta_key)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8'
		);
		qa_db_query_sub(
			'CREATE TABLE IF NOT EXISTS ^usermeta (
			meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			PRIMARY KEY (meta_id),
			UNIQUE (user_id,meta_key)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8'
		);
		
		qa_db_query_sub(
			'INSERT INTO ^postmeta (post_id,meta_key,meta_value) VALUES (#,$,$) ON DUPLICATE KEY UPDATE meta_value=$',
			$oid,'is_wikified','true','true'
		);
		qa_db_query_sub(
			'INSERT INTO ^usermeta (user_id,meta_key,meta_value) VALUES (#,#,$) ON DUPLICATE KEY UPDATE meta_value=meta_value+1',
			qa_get_logged_in_userid(),'wikified',1
		);
		$var = qa_db_read_one_value(
			qa_db_query_sub(
				'SELECT meta_value FROM ^usermeta WHERE meta_key=$ AND user_id=#',
				'wikified',qa_get_logged_in_userid()
			),true
		);
		if(function_exists('qa_badge_award_check') && qa_opt('badge_active') && qa_opt('badge_custom_badges'))
			qa_badge_award_check(array('wikifier','wacky_wikifier','wicked_wikifier'), $var, qa_get_logged_in_userid(), NULL, 2); 
	}

/*
	Omit PHP closing tag to help avoid accidental output
*/