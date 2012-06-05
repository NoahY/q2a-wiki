<?php
    class qa_wiki_admin {

	function option_default($option) {
		
	    switch($option) {
		case 'wiki_edit_allow':
		    return 150;
		case 'wiki_send_allow':
		    return 100;
		case 'wiki_edit_allow_points':
		    return 0;
		case 'wiki_send_allow_points':
		    return 0;

		case 'wiki_page_css':
		    return '
#wiki_container .rbr {
    background-color: #EEEEEE;
    border-color: #777;
    border-radius: 12px 0 0 12px;
    border-style: solid none solid solid;
    border-width: 2px 0 2px 2px;
    clear: right;
    color: #221003;
    float: left;
    margin-top: 8px;
    padding: 6px;
}
#wiki_container .rbr a {
  text-decoration:none;
}
#wiki_container .rbr ul {
  list-style: none outside none;
  padding:0;
}
#wiki_container .rbr li {
  float:left;
  padding:0 8px;
}
#wiki_container #wiki-main {
}

#wiki_container #wiki-main .action-links {
}
#wiki_container #wiki-main .action-links a {
    background-color: #EEEEEE;
    border: 1px solid silver;
    border-radius: 4px 4px 4px 4px;
    color: black;
    padding: 3px 8px;
    text-decoration: none;
}
#wiki-main .action-links a:hover {
  background-color: #EFEFEF !important;
}
#wiki-main .control-links {
  text-align:left;
}
#wiki-main .action-links-buttons {
  float:right;
}
.qa-wiki-error {
    font-weight:bold;
    color:Maroon;
}
';
		default:
		    return null;				
	    }
		
	}

		function custom_badges() {
			return array(
				'wikifier' => array('var'=>1, 'type'=>0),
				'wacky_wikifier' => array('var'=>5, 'type'=>1),
				'wicked_wikifier' => array('var'=>20, 'type'=>2),
			);
		}
		
		
		function custom_badges_rebuild() {
			$awarded = 0;
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
			$posts = qa_db_query_sub(
				'SELECT user_id AS userid, meta_value FROM ^usermeta WHERE meta_key=$',
				'wikified'
			);
			while ( ($post=qa_db_read_one_assoc($posts,true)) !== null ) {
				$badges = array('wikifier','wacky_wikifier','wicked_wikifier');
				$awarded += count(qa_badge_award_check($badges,(int)$post['meta_value'],$post['userid'],null,2));
			}
			return $awarded;
		}
        
        function allow_template($template)
        {
            return ($template!='admin');
        }       
            
        function admin_form(&$qa_content)
        {                       
                            
        // Process form input
            
            $ok = null;
            
            if (qa_clicked('wiki_plugin_save')) {
			
                qa_opt('wiki_send_enable',(bool)qa_post_text('wiki_send_enable'));
                qa_opt('wiki_page_css',qa_post_text('wiki_page_css'));
		
		$ok = qa_lang('admin/options_saved');
            }
            else if (qa_clicked('wiki_plugin_reset')) {
		foreach($_POST as $i => $v) {
		    $def = $this->option_default($i);
		    if($def !== null) qa_opt($i,$def);
		}
		$ok = qa_lang('admin/options_reset');
	    }
	    
	    if(qa_opt('wiki_send_allow')!=106) {
		qa_set_display_rules($qa_content, array(
		    'wiki_send_allow_points' => 'wiki_send_allow_points_hidden',
		));
	    }
	    if(qa_opt('wiki_edit_allow')!=106) {
		qa_set_display_rules($qa_content, array(
		    'wiki_edit_allow_points' => 'wiki_send_allow_points_hidden',
		));
	    }
  
        // Create the form for display
            
            $fields = array();


            $fields[] = array(
                'label' => 'Allow sending answers to wiki',
		'note' => '',
                'tags' => 'NAME="wiki_send_enable"',
                'type' => 'checkbox',
                'value' => qa_opt('wiki_send_enable'),
            );

	    $fields[] = array(
		'type' => 'blank',
	    );

            $fields[] = array(
                'label' => 'Wiki page stylesheet',
                'tags' => 'NAME="wiki_page_css"',
                'value' => qa_opt('wiki_page_css'),
		'rows' => 20,
		'type' => 'textarea',
		'note' => '^ will be replaced by location of this plugin directory',
            );
            return array(           
                'ok' => ($ok && !isset($error)) ? $ok : null,
                    
                'fields' => $fields,
		
		'hidden' => array(
		    array(
			'id' => 'wiki_send_allow_points_hidden',
			'tags' => 'NAME="wiki_send_allow_points_hidden"',
			'value' => 'false',
		    )
		),
             
                'buttons' => array(
                    array(
                        'label' => qa_lang_html('main/save_button'),
                        'tags' => 'NAME="wiki_plugin_save"',
                    ),
                    array(
                        'label' => qa_lang_html('admin/reset_options_button'),
                        'tags' => 'NAME="wiki_plugin_reset"',
                    ),
                ),
            );
        }
    }

