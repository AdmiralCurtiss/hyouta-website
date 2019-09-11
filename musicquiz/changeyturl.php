<?php
	if ( !isset($session) ) {
		die();
	}

	if ( $session->logged_in && $session->user->is_admin() && $session->userid == 1 ) {
		require_once 'db.class.php';
		require_once '../credentials.php';
		$db = new db( $__db_connstr_music__, $__db_username_music__, $__db_password_music__ );
		
		if ( (int)$_GET['songid'] > 0 ) {
			if ( $db->edit_song( $_GET['songid'], $_GET['url'], $_GET['diff'], -1, 0 ) ) {
				echo '<p>Success!</p>';
			} else {
				echo '<p>Fail...</p>';
			}
		} else {
			echo '<p>Fail...</p>';
		}
	} else {
		include 'main.php';
	}
?>