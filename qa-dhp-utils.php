<?php
if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
}

if (!function_exists('ami_dhp_delete_hidden_posts_process')) {		
		function ami_dhp_delete_hidden_posts_process()
		{
			
			// load all required files if not loaded 
			require_once QA_INCLUDE_DIR.'qa-app-admin.php';
			require_once QA_INCLUDE_DIR.'qa-db-admin.php';
			require_once QA_INCLUDE_DIR.'qa-db-selects.php';
			require_once QA_INCLUDE_DIR.'qa-app-format.php';
			
			//	Check admin privileges 
			if (qa_user_maximum_permit_error('permit_hide_show') && qa_user_maximum_permit_error('permit_delete_hidden')) {
				return false;
			}

			$userid=qa_get_logged_in_userid();
			
			//	Find recently hidden questions, answers, comments
			list($hiddenquestions, $hiddenanswers, $hiddencomments)=qa_db_select_with_pending(
				qa_db_qs_selectspec($userid, 'created', 0, null, null, 'Q_HIDDEN', true),
				qa_db_recent_a_qs_selectspec($userid, 0, null, null, 'A_HIDDEN', true),
				qa_db_recent_c_qs_selectspec($userid, 0, null, null, 'C_HIDDEN', true)
			);

			global $ami_dhp_posts_deleted ; 

			// first delete all hidden posts 
			if (count($hiddencomments)) {
				foreach ($hiddencomments as $hiddencomment) {
						ami_dhp_post_delete_recursive($hiddencomment['opostid']);
				}
			}
			
			// delete all the hidden answers 
			if (count($hiddenanswers)) {
				foreach ($hiddenanswers as $hiddenanswer) {
						ami_dhp_post_delete_recursive($hiddenanswer['opostid']);
				}
			}
			// delete all the hidden questions 
			if (count($hiddenquestions)) {
				foreach ($hiddenquestions as $hiddenquestion) {
						ami_dhp_post_delete_recursive($hiddenquestion['postid']);
				}
			}
		}
	}

if (!function_exists('ami_dhp_post_delete_recursive')) {
		function ami_dhp_post_delete_recursive($postid)
		{
				require_once QA_INCLUDE_DIR.'qa-app-admin.php';
				require_once QA_INCLUDE_DIR.'qa-db-admin.php';
				require_once QA_INCLUDE_DIR.'qa-db-selects.php';
				require_once QA_INCLUDE_DIR.'qa-app-format.php';
				require_once QA_INCLUDE_DIR.'qa-app-posts.php';

				global $ami_dhp_posts_deleted ; 

				if (is_null($ami_dhp_posts_deleted)) {
					$ami_dhp_posts_deleted = array() ;
				}

				if (in_array($postid, $ami_dhp_posts_deleted)){
					return;
				}

				$oldpost=qa_post_get_full($postid, 'QAC');
				
				if (!$oldpost['hidden']) {
					qa_post_set_hidden($postid, true, null);
					$oldpost=qa_post_get_full($postid, 'QAC');
				}
				
				switch ($oldpost['basetype']) {
					case 'Q':
						$answers=qa_post_get_question_answers($postid);
						$commentsfollows=qa_post_get_question_commentsfollows($postid);
						$closepost=qa_post_get_question_closepost($postid);
						
						if (count($answers) ){
							foreach ($answers as $answer) {
								ami_dhp_post_delete_recursive($answer['postid']);
							}
						}

						if (count($commentsfollows)){
							foreach ($commentsfollows as $commentsfollow) {
								ami_dhp_post_delete_recursive($commentsfollow['postid']);
							}
						}
						if (!in_array($oldpost['postid'], $ami_dhp_posts_deleted)){
							qa_question_delete($oldpost, null, null, null, $closepost);
							$ami_dhp_posts_deleted[] = $oldpost['postid'] ;
						}
						break;
						
					case 'A':
						$question=qa_post_get_full($oldpost['parentid'], 'Q');
						$commentsfollows=qa_post_get_answer_commentsfollows($postid);

						if (count($commentsfollows)){
							foreach ($commentsfollows as $commentsfollow) {
								ami_dhp_post_delete_recursive($commentsfollow['postid']);
							}
						}
						if (!in_array($oldpost['postid'], $ami_dhp_posts_deleted)){
							qa_answer_delete($oldpost, $question, null, null, null);
							$ami_dhp_posts_deleted[] = $oldpost['postid'] ;
						}
						break;
						
					case 'C':
						$parent=qa_post_get_full($oldpost['parentid'], 'QA');
						$question=qa_post_parent_to_question($parent);
						if (!in_array($oldpost['postid'], $ami_dhp_posts_deleted)){
							qa_comment_delete($oldpost, $question, $parent, null, null, null);
							$ami_dhp_posts_deleted[] = $oldpost['postid'] ;
						}
						break;
				}
				
		}
	}

if (!function_exists('ami_dhp_add_q_delete_button')) {
	function ami_dhp_add_q_delete_button( &$buttons , $post )
	{
		if (!qa_opt(qa_dhp_admin::PLUGIN_ENABLED) || !(ami_dhp_is_user_eligible_to_delete(qa_get_logged_in_userid() , @$post['userid'])) || isset($buttons['delete']) ) {
			// if the feature is not enabled from the admin panel , then return a falsy value , do not process anything 
			return false;
		}
		
		$prefix='q'.$post['postid'].'_';
		if (qa_clicked($prefix.qa_dhp_admin::DELETE_Q_BTN)) {
				ami_dhp_post_delete_recursive($post['postid']);
				header('Location: ../../');
				exit ;

		}else {
				// add the anonymous buton
				$buttons[qa_dhp_admin::DELETE_Q_BTN]=array(
						'tags' => 'name="'.$prefix.qa_dhp_admin::DELETE_Q_BTN.'"',
						'label' => qa_lang('ami_dhp/delete_q'),
						'popup' => qa_lang('question/delete_q_popup'),
					);
		}
	}
}

if (!function_exists('ami_dhp_add_a_delete_button')) {
	function ami_dhp_add_a_delete_button( &$buttons , $post )
	{

		if (!qa_opt(qa_dhp_admin::PLUGIN_ENABLED) || !(ami_dhp_is_user_eligible_to_delete(qa_get_logged_in_userid() , @$post['userid'])) || isset($buttons['delete']) ) {
			// if the feature is not enabled from the admin panel , then return a falsy value , do not process anything 
			return false;
		}

		$prefix='a'.$post['postid'].'_'; 

		if (qa_clicked($prefix.qa_dhp_admin::DELETE_A_BTN)) {
				ami_dhp_post_delete_recursive($post['postid']);
				qa_redirect(qa_self_html());
				exit ;
		}else {
				// add the anonymous buton
				$buttons[qa_dhp_admin::DELETE_A_BTN]=array(
						'tags' => 'name="'.$prefix.qa_dhp_admin::DELETE_A_BTN.'"',
						'label' => qa_lang('ami_dhp/delete_a'),
						'popup' => qa_lang('question/delete_a_popup'),
					);
		}
	}
}

if (!function_exists('ami_dhp_add_c_delete_button')) {
	function ami_dhp_add_c_delete_button( &$buttons , $post )
	{

		if (!qa_opt(qa_dhp_admin::PLUGIN_ENABLED) || !(ami_dhp_is_user_eligible_to_delete(qa_get_logged_in_userid() , @$post['userid'])) || isset($buttons['delete']) ) {
			// if the feature is not enabled from the admin panel , then return a falsy value , do not process anything 
			return false;
		}

		$prefix='c'.$post['postid'].'_'; 

		if (qa_clicked($prefix.qa_dhp_admin::DELETE_C_BTN)) {
				ami_dhp_post_delete_recursive($post['postid']);
				qa_redirect(qa_self_html());
				exit ;
		} else {
				// add the anonymous buton
				$buttons[qa_dhp_admin::DELETE_C_BTN]=array(
						'tags' => 'name="'.$prefix.qa_dhp_admin::DELETE_C_BTN.'"',
						'label' => qa_lang('ami_dhp/delete_c'),
						'popup' => qa_lang('question/delete_c_popup'),
					);
		}
	}
}


if (!function_exists('ami_dhp_is_user_eligible_to_delete')) {
	function ami_dhp_is_user_eligible_to_delete($userid = null , $post_userid = null )		
	{
		// if the plugin is not enabled first reuturn false 
		if (!qa_opt(qa_dhp_admin::PLUGIN_ENABLED)) {
			return false;
		}

		if (is_null($userid) || !isset($userid)) {
			// if the userid is not set then get the logged in userid 
			$userid = qa_get_logged_in_userid() ;
		}

		if (is_null($userid) && !qa_is_logged_in()) {
			// if still it is null then ret false 
			return false;
		}
		
		// return true for all special users that is allowed from admin panel 
		if (qa_get_logged_in_level() >= qa_opt(qa_dhp_admin::MIN_LEVEL_TO_DELETE_Q)) {
			return true;
		}

		if (qa_opt(qa_dhp_admin::SAME_USER_CAN_DELETE_QA) && !is_null($post_userid) && ((int)$userid == (int)$post_userid)) {
			return true;
		}
		
		return false ;
	}
}

/*
	Omit PHP closing tag to help avoid accidental output
*/