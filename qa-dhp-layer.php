<?php
	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}

	require_once QA_INCLUDE_DIR.'qa-theme-base.php';
	require_once QA_INCLUDE_DIR.'qa-app-blobs.php';
	require_once QA_INCLUDE_DIR.'qa-app-users.php';

	class qa_html_theme_layer extends qa_html_theme_base {

		private $extradata;
		private $pluginurl;
		
		function head_css() {
			qa_html_theme_base::head_css();
			$this->output('<link rel="stylesheet" href="'.qa_opt('site_url').'qa-plugin/'.DHP_DIR_NAME.'/styles.css">');
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
		
	}
	/*
		Omit PHP closing tag to help avoid accidental output
	*/