<?php

	class qa_html_theme_layer extends qa_html_theme_base {

		function head_custom() {
			if(strpos($this->request,'wiki') === 0) {
					$this->output('<style>',str_replace('^',QA_HTML_THEME_LAYER_URLTOROOT,qa_opt('wiki_page_css')),'</style>');
			}
			
			if($this->template == 'question' && qa_opt('wiki_send_enable') && qa_opt('wiki_send_allow') <= qa_get_logged_in_level()) {
				foreach($this->content['a_list']['as'] as $idx => $answer) {
					if(qa_clicked('a_to_wiki_'.$idx)) {
						qa_redirect('wiki',array('id'=>qa_html('edit/'.$this->content['q_view']['raw']['title']),'qa_wiki_content'=>qa_html($answer['raw']['content']),'qa_wiki_link'=>qa_html(qa_q_path($this->content['q_view']['raw']['postid'],$this->content['q_view']['raw']['title'],true,'A',$answer['raw']['postid']))));
					}
					$this->content['a_list']['as'][$idx]['form']['buttons']['wiki'] = array(
						'tags' => 'NAME="a_to_wiki_'.$idx.'"',
						'label' => qa_lang_html('wiki_page/a_to_wiki_button'),
						'popup' => qa_lang_html('wiki_page/a_to_wiki_popup'),
					);
				}
			}
			qa_html_theme_base::head_custom();
		}

	}

