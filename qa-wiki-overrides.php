<?php
		
	function qa_get_permit_options() {
		$permits = qa_get_permit_options_base();
		$permits[] = 'wiki_edit_allow';
		$permits[] = 'wiki_send_allow';
		return $permits;
	}
						
/*							  
		Omit PHP closing tag to help avoid accidental output
*/							  
						  

