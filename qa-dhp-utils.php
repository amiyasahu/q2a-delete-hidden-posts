<?php
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

			global $deleted ; 

			// first delete all hidden posts 
			foreach ($hiddencomments as $hiddencomment) {
					ami_dhp_post_delete_recursive($hiddencomment['opostid']);
			}
			// delete all the hidden answers 
			foreach ($hiddenanswers as $hiddenanswer) {
					ami_dhp_post_delete_recursive($hiddenanswer['opostid']);
			}
			// delete all the hidden questions 
			foreach ($hiddenquestions as $hiddenquestion) {
					ami_dhp_post_delete_recursive($hiddenquestion['postid']);
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

				global $deleted ; 

				if (is_null($deleted)) {
					$deleted = array() ;
				}

				if (in_array($postid, $deleted)){
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
						if (!in_array($oldpost['postid'], $deleted)){
							qa_question_delete($oldpost, null, null, null, $closepost);
							$deleted[] = $oldpost['postid'] ;
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
						if (!in_array($oldpost['postid'], $deleted)){
							qa_answer_delete($oldpost, $question, null, null, null);
							$deleted[] = $oldpost['postid'] ;
						}
						break;
						
					case 'C':
						$parent=qa_post_get_full($oldpost['parentid'], 'QA');
						$question=qa_post_parent_to_question($parent);
						if (!in_array($oldpost['postid'], $deleted)){
							qa_comment_delete($oldpost, $question, $parent, null, null, null);
							$deleted[] = $oldpost['postid'] ;
						}
						break;
				}
				
		}
	}

if (!function_exists('ami_dhp_add_q_delete_button')) {
	function ami_dhp_add_q_delete_button( &$buttons , $post )
	{
		if (!qa_opt(qa_dhp_admin::PLUGIN_ENABLED)) {
			// if the feature is not enabled from the admin panel , then return a falsy value , do not process anything 
			return false;
		}
		
		if (qa_clicked(qa_dhp_admin::DELETE_Q_BTN)) {
			if (ami_dhp_is_user_eligible_to_delete(qa_get_logged_in_userid() , @$post['userid'])) { // already checked 'permit_post_q'
				ami_dhp_post_delete_recursive($post['postid']);
				header('Location: ../../');
        		exit;
			} 
		}

		if (ami_dhp_is_user_eligible_to_delete(qa_get_logged_in_userid() , @$post['userid'])) {
				// add the anonymous buton
				$buttons[qa_dhp_admin::DELETE_Q_BTN]=array(
						'tags' => 'name="'.qa_dhp_admin::DELETE_Q_BTN.'"',
						'label' => qa_lang('ami_dhp/delete_q'),
						'popup' => qa_lang('ami_dhp/delete_q'),
					);
			}
	}
}

if (!function_exists('ami_dhp_add_a_delete_button')) {
	function ami_dhp_add_a_delete_button( &$buttons , $post )
	{

		if (!qa_opt(qa_dhp_admin::PLUGIN_ENABLED)) {
			// if the feature is not enabled from the admin panel , then return a falsy value , do not process anything 
			return false;
		}
		
		if (qa_clicked(qa_dhp_admin::DELETE_A_BTN)) {
			if (ami_dhp_is_user_eligible_to_delete(qa_get_logged_in_userid() , @$post['userid'])) { // already checked 'permit_post_q'
				ami_dhp_post_delete_recursive($post['postid']);
				qa_redirect(qa_self_html());
				exit ;
			} 
		}

		if (ami_dhp_is_user_eligible_to_delete(qa_get_logged_in_userid() , @$post['userid'])) {
				// add the anonymous buton
				$buttons[qa_dhp_admin::DELETE_A_BTN]=array(
						'tags' => 'name="'.qa_dhp_admin::DELETE_A_BTN.'"',
						'label' => qa_lang('ami_dhp/delete_a'),
						'popup' => qa_lang('ami_dhp/delete_a'),
					);
			}
	}
}


if (!function_exists('ami_dhp_is_user_eligible_to_delete')) {
	function ami_dhp_is_user_eligible_to_delete($userid = null , $q_userid = null )		
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

		if (qa_opt(qa_dhp_admin::SAME_USER_CAN_DELETE_QA) && !is_null($q_userid) && ((int)$userid == (int)$q_userid)) {
			return true;
		}
		
		return false ;
	}
}

/*
	Omit PHP closing tag to help avoid accidental output
*/