<?php
if ( !isset( $session ) ) {
	die();
}

	
	if ( $session->logged_in ) {
		echo '<p>Logged in as <b>'.$session->user->username.'</b>';
		echo '<br><a href="index.php?section=edituser">Change Password</a><form action="index.php" method="post"><input type="submit" name="formaction" value="logout" /></form></p>';
	} else {
		include 'login.php';
	}
?>