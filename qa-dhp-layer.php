<?php
	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}

	require_once QA_INCLUDE_DIR.'qa-theme-base.php';
	require_once QA_INCLUDE_DIR.'qa-app-blobs.php';
	require_once QA_INCLUDE_DIR.'qa-app-users.php';

	class qa_html_theme_layer extends qa_html_theme_base {
		
		function head_css() {
			qa_html_theme_base::head_css();
			$this->output('<link rel="stylesheet" href="'.qa_opt('site_url').'qa-plugin/'.AMI_DHP_DIR_NAME.'/styles.css">');
		}

		function q_view_buttons($q_view) {
			if (isset($q_view['form']['buttons']) && count($q_view['form']['buttons'])) {
				ami_dhp_add_q_delete_button($q_view['form']['buttons'] , $q_view['raw']) ; 
			}
			
			qa_html_theme_base::q_view_buttons($q_view);
		}

		function a_item_buttons($a_item){
			if (isset($a_item['form']['buttons']) && count($a_item['form']['buttons'])) {
				ami_dhp_add_a_delete_button($a_item['form']['buttons'] , $a_item['raw']) ; 
			}
			
			qa_html_theme_base::a_item_buttons($a_item);
		}

		function c_item_buttons($c_item){
			if (isset($c_item['form']['buttons']) && count($c_item['form']['buttons'])) {
				ami_dhp_add_c_delete_button($c_item['form']['buttons'] , $c_item['raw']) ; 
			}
			
			qa_html_theme_base::c_item_buttons($c_item);
		}

		
	}
	/*
		Omit PHP closing tag to help avoid accidental output
	*/