<?php
		
	function qa_get_permit_options() {
		$permits = qa_get_permit_options_base();
		$permits[] = 'wiki_edit_allow';
		$permits[] = 'wiki_send_allow';
		return $permits;
	}

	function qa_get_request_content() {
		$qa_content = qa_get_request_content_base();
		
		// permissions
		
		if(isset($qa_content['form_profile']['fields']['permits'])) {			
			
				$ov = $qa_content['form_profile']['fields']['permits']['value'];
				$ov = str_replace('[profile/wiki_send_allow]',qa_lang('wiki_page/wiki_send_allow'),$ov);
				$ov = str_replace('[profile/wiki_edit_allow]',qa_lang('wiki_page/wiki_edit_allow'),$ov);
				$qa_content['form_profile']['fields']['permits']['value'] = $ov;
		}
		return $qa_content;
	}
						
/*							  
		Omit PHP closing tag to help avoid accidental output
*/							  
						  

