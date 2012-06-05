<?php

	class qa_html_theme_layer extends qa_html_theme_base {

		function doctype() {
			qa_html_theme_base::doctype();
			if($this->request == 'admin/permissions' && qa_get_logged_in_level()>=QA_USER_LEVEL_ADMIN) {

				$permits[] = 'wiki_edit_allow';
				$permits[] = 'wiki_send_allow';			
				foreach($permits as $optionname) {
					$value = qa_opt($optionname);
					$optionfield=array(
						'id' => $optionname,
						'label' => qa_lang_html('wiki_page/'.$optionname).':',
						'tags' => 'NAME="option_'.$optionname.'" ID="option_'.$optionname.'"',
						'value' => $value,
						'error' => qa_html(@$errors[$optionname]),
					);					
					$widest=QA_PERMIT_ALL;
					$narrowest=QA_PERMIT_ADMINS;
					
					$permitoptions=qa_admin_permit_options($widest, $narrowest, (!QA_FINAL_EXTERNAL_USERS) && qa_opt('confirm_user_emails'));
					
					if (count($permitoptions)>1)
						qa_optionfield_make_select($optionfield, $permitoptions, $value,
							($value==QA_PERMIT_CONFIRMED) ? QA_PERMIT_USERS : min(array_keys($permitoptions)));
					$this->content['form']['fields'][$optionname]=$optionfield;

					$this->content['form']['fields'][$optionname.'_points']= array(
						'id' => $optionname.'_points',
						'tags' => 'NAME="option_'.$optionname.'_points" ID="option_'.$optionname.'_points"',
						'type'=>'number',
						'value'=>qa_opt($optionname.'_points'),
						'prefix'=>qa_lang_html('admin/users_must_have').'&nbsp;',
						'note'=>qa_lang_html('admin/points')
					);
					$checkboxtodisplay[$optionname.'_points']='(option_'.$optionname.'=='.qa_js(QA_PERMIT_POINTS).') ||(option_'.$optionname.'=='.qa_js(QA_PERMIT_POINTS_CONFIRMED).')';
				}
				qa_set_display_rules($this->content, $checkboxtodisplay);
			}
			if($this->template == 'question' && qa_opt('wiki_send_enable') && qa_permit_check('wiki_send_allow')  && isset($this->content['a_list']['as']) && count($this->content['a_list']['as'])) {

				$wikified = $this->wiki_meta();

				foreach($this->content['a_list']['as'] as $idx => $answer) {
					if(qa_clicked('a_to_wiki_'.$idx)) {
						if(!in_array($answer['raw']['postid'],$wikified)) {
							$handle = $this->id_to_handle($answer['raw']['userid']);
							qa_redirect('wiki',array('id'=>qa_html('edit/'.qa_post_text('qa_wikify_title')),'qa_wiki_oid'=>$answer['raw']['postid'],'qa_wiki_handle'=>$handle,'qa_wiki_link'=>qa_html(qa_q_path($this->content['q_view']['raw']['postid'],$this->content['q_view']['raw']['title'],true,'A',$answer['raw']['postid']))));
						}
						else
							qa_redirect('wiki',array('id'=>qa_html($this->content['q_view']['raw']['title'])));
							
					}
					if(!in_array($answer['raw']['postid'],$wikified)) {
					
						$this->content['a_list']['as'][$idx]['form']['buttons']['wiki'] = array(
							'tags' => 'onclick="return wikifyName(\''.qa_html($this->content['q_view']['raw']['title']).'\')" NAME="a_to_wiki_'.$idx.'"',
							'label' => qa_lang_html('wiki_page/a_to_wiki_button'),
							'popup' => qa_lang_html('wiki_page/a_to_wiki_popup'),
						);
						$this->content['a_list']['as'][$idx]['form']['hidden']['qa_wikify_title" class="qa_wikify_title'] = qa_html($this->content['q_view']['raw']['title']);
					}
					else
						$this->content['a_list']['as'][$idx]['form']['buttons']['wiki'] = array(
							'tags' => 'ID="wikified" NAME="a_to_wiki_'.$idx.'"',
							'label' => qa_lang_html('wiki_page/a_to_wikified_button'),
							'popup' => qa_lang_html('wiki_page/a_to_wikified_popup'),
						);
				}
			}
		}
		function head_custom() {
			if(strpos($this->request,'wiki') === 0) {
				$this->output('<style>',str_replace('^',QA_HTML_THEME_LAYER_URLTOROOT,qa_opt('wiki_page_css')),'</style>');
			}
			if($this->template == 'question' && qa_opt('wiki_send_enable') && qa_permit_check('wiki_send_allow')  && isset($this->content['a_list']['as']) && count($this->content['a_list']['as'])) {

				$this->output('<script type="text/javascript">',"function wikifyName(name){var newname = prompt('Enter wiki post name:',name); if(!newname) return false; jQuery('.qa_wikify_title').val(newname); return true; }",'</script>');
			}
			qa_html_theme_base::head_custom();
		}
		
		// worker
		
		function wiki_meta() {
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
			$array = qa_db_read_all_values(
				qa_db_query_sub(
					'SELECT post_id FROM ^postmeta WHERE meta_key=$',
					'is_wikified'
				)
			);		
			return $array;
		}

		function id_to_handle($uid) {
			require_once QA_INCLUDE_DIR.'qa-app-users.php';
			
			if (QA_FINAL_EXTERNAL_USERS) {
				$publictouserid=qa_get_public_from_userids(array($uid));
				$handle=@$publictouserid[$uid];
				
			} 
			else {
				$handle = qa_db_read_one_value(
					qa_db_query_sub(
						'SELECT handle FROM ^users WHERE userid = #',
						$uid
					),
					true
				);
			}
			if (!isset($handle)) return;
			return $handle;
		}
	}

