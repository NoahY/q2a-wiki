<?php
    class qa_wiki_admin {

	function option_default($option) {
		
	    switch($option) {
		case 'wiki_send_allow':
		    return 100;
		case 'wiki_page_css':
		    return '
#wiki_container .rbr {
  font-family:"skaterdudes","steelfish","coolvetica","Arial","Helvetica","Lucida";
  background-color:#ffbb44;
  color:#221003;
  padding:3px;
  border-radius:12px 0px 0px 12px;
  -moz-border-radius:12px 0px 0px 12px;
  border:2px solid #000000; border-right:0px;
  margin-top:8px;
  float:right; clear:right;
}
#wiki_container .rbr a {
  text-decoration:none;
}
#wiki_container #wiki-main {
}

#wiki_container #wiki-main .action-links {
}
#wiki_container #wiki-main .action-links a {
   padding:4px;
   background-color: #EEE;
  color:black;
  text-decoration:none;
}
#wiki-main .action-links a:hover {
  background-color: #EFEFEF !important;
}
';
		default:
		    return null;				
	    }
		
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
                qa_opt('wiki_send_allow',(int)qa_post_text('wiki_send_allow'));
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
  
        // Create the form for display
            
            $fields = array();

            $fields[] = array(
                'label' => 'Allow sending answers to wiki',
		'note' => '',
                'tags' => 'NAME="wiki_send_enable"',
                'type' => 'checkbox',
                'value' => qa_opt('wiki_send_enable'),
            );
            	    
	    $permitoptions=qa_admin_permit_options(QA_PERMIT_EXPERTS, QA_PERMIT_ADMINS, (!QA_FINAL_EXTERNAL_USERS) && qa_opt('confirm_user_emails'));

	    $fields[] = array(
		'id' => 'wiki_send_allow',
		'label' => 'Roles allowed to send answers to wiki',
		'tags' => 'NAME="wiki_send_allow" ID="wiki_send_allow"',
		'type' => 'select',
		'options' => $permitoptions,
		'value' => $permitoptions[qa_opt('wiki_send_allow')],
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

