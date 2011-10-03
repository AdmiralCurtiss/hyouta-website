<?php
	if ( !isset($session) ) {
		die();
	}

	if ( $session->logged_in && $session->user->is_admin() && $session->userid == 1 ) {
		require_once 'db.class.php';
		$db = new db($database);
		if ( $db->edit_result( $_GET['userid'], $_GET['songid'], $_GET['pts'] ) ) {
			echo '<p>Success!</p>';
		} else {
			echo '<p>Fail...</p>';
		}
		echo '<p><a href="'.$_SERVER['HTTP_REFERER'].'">Return</a>.</p>';
	} else {
		include 'main.php';
	}
?>