<?php
if ( !isset( $session ) ) {
	die();
}

	
	if ( $session->logged_in ) {
		echo '<p>Logged in as <b>'.$session->user->username.'</b>';
		/*
		if ( isset($userdata['foto']) ) {
			require_once('load_picture.php');
			$userpic_url = 'http://inflamp.technikum-wien.at/~s09int/Userbilder/'.$userdata['foto'];
			$userpic_path = '/home/s09int/public_html/Userbilder/'.$userdata['foto'];
			if( !$userpic = load_pic($userpic_path) ) {
				echo 'Failed opening picture, go figure?';
			}
			$userpic_width = imagesx($userpic);
			$userpic_height = imagesy($userpic);
			if ( $userpic_width > 150 || $userpic_height > 150 ) {
				$userpic_url = 'userpic.php?pic='.$userdata['foto'];
			}
			echo '<td colspan="2"><img src="'.$userpic_url.'" /></td>';
		}
		*/
		echo '<br><a href="index.php?section=edituser">Change Password</a><form action="index.php" method="post"><input type="submit" name="formaction" value="logout" /></form></p>';
	} else {
		include 'login.php';
	}
?>