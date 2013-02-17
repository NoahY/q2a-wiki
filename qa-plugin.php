<?php

/*
	Plugin Name: Wiki Page
	Plugin URI: https://github.com/NoahY/q2a-wiki
	Plugin Description: Wiki plugin page
	Plugin Version: 0.5
	Plugin Date: 2011-12-23
	Plugin Author: NoahY
	Plugin Author URI: http://www.question2answer.org/
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.5
	Plugin Update Check URI: https://raw.github.com/NoahY/q2a-wiki/master/qa-plugin.php
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
	qa_register_plugin_overrides('qa-wiki-overrides.php');

	if(!function_exists('qa_permit_check')) {
		function qa_permit_check($opt) {
			if(qa_opt($opt) == QA_PERMIT_POINTS)
				return qa_get_logged_in_points() >= qa_opt($opt.'_points');
			return !qa_permit_value_error(qa_opt($opt), qa_get_logged_in_userid(), qa_get_logged_in_level(), qa_get_logged_in_flags());
		}
	}

/*
	Omit PHP closing tag to help avoid accidental output
*/
