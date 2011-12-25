<?php

	class qa_html_theme_layer extends qa_html_theme_base {

		function head_custom() {
			if(strpos($this->request,'wiki') === 0) {
					$this->output('<style>',str_replace('^',QA_HTML_THEME_LAYER_URLTOROOT,qa_opt('wiki_page_css')),'</style>');
			}
			
			if($this->template == 'question' && qa_opt('wiki_send_enable') && qa_opt('wiki_send_allow') <= qa_get_logged_in_level() && isset($this->content['a_list']['as']) && count($this->content['a_list']['as'])) {
				$wikified = $this->wiki_meta();
				foreach($this->content['a_list']['as'] as $idx => $answer) {
					if(qa_clicked('a_to_wiki_'.$idx)) {
						if(!in_array($answer['raw']['postid'],$wikified)) {
							$this->wiki_meta($answer['raw']['postid']);
							qa_redirect('wiki',array('id'=>qa_html('edit/'.$this->content['q_view']['raw']['title']),'qa_wiki_content'=>qa_html($answer['raw']['content']),'qa_wiki_link'=>qa_html(qa_q_path($this->content['q_view']['raw']['postid'],$this->content['q_view']['raw']['title'],true,'A',$answer['raw']['postid']))));
						}
						else
							qa_redirect('wiki',array('id'=>qa_html($this->content['q_view']['raw']['title'])));
							
					}
					if(!in_array($answer['raw']['postid'],$wikified))
						$this->content['a_list']['as'][$idx]['form']['buttons']['wiki'] = array(
							'tags' => 'NAME="a_to_wiki_'.$idx.'"',
							'label' => qa_lang_html('wiki_page/a_to_wiki_button'),
							'popup' => qa_lang_html('wiki_page/a_to_wiki_popup'),
						);
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
		
		function wiki_meta($oid=null) {
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
			error_log($oid);
			if($oid) {
				qa_db_query_sub(
					'INSERT INTO ^postmeta (post_id,meta_key,meta_value) VALUES (#,$,$) ON DUPLICATE KEY UPDATE meta_value=$',
					$oid,'is_wikified','true','true'
				);
			}
			else {
				$array = qa_db_read_all_values(
					qa_db_query_sub(
						'SELECT post_id FROM ^postmeta WHERE meta_key=$',
						'is_wikified'
					)
				);		
				return $array;
			}
		}

	}

