<?php

	class qa_html_theme_layer extends qa_html_theme_base {

		function head_custom() {
			if(strpos($this->request,'wiki') === 0) {
					$this->output('<style>',str_replace('^',QA_HTML_THEME_LAYER_URLTOROOT,qa_opt('wiki_page_css')),'</style>');
			}
			if($this->template == 'question' && qa_opt('wiki_send_enable') && !qa_permit_value_error(qa_opt('wiki_send_allow'), qa_get_logged_in_userid(), qa_get_logged_in_level(), qa_get_logged_in_flags())  && isset($this->content['a_list']['as']) && count($this->content['a_list']['as'])) {

				$this->output('<script>',"function wikifyName(name){var newname = prompt('Enter wiki post name:',name); if(!newname) return false; jQuery('.qa_wikify_title').val(newname); return true; }",'</script>');

				$wikified = $this->wiki_meta();

				foreach($this->content['a_list']['as'] as $idx => $answer) {
					if(qa_clicked('a_to_wiki_'.$idx)) {
						if(!in_array($answer['raw']['postid'],$wikified)) {
							$handle = $this->id_to_handle($this->content['q_view']['raw']['userid']);
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

