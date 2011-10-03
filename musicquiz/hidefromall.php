<?php
	if ( !isset($session) ) {
		die();
	}

	if ( $session->logged_in && $session->user->is_admin() && $session->userid == 1 ) {
		require_once 'db.class.php';
		$db = new db($database);
		if ( $db->hide_guess_from_all( $_GET['userid'], $_GET['songid'] ) ) {
			echo '<p>Success!</p>';
		} else {
			echo '<p>Fail...</p>';
		}
		echo '<p><a href="'.$_SERVER['HTTP_REFERER'].'">Return</a>.</p>';
	} else {
		include 'main.php';
	}
?>