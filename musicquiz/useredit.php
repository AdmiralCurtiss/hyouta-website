<?php
if ( !isset( $session ) ) {
	die();
}

	if ( $session->logged_in ) {
		if (    isset($_POST['formaction']) && $_POST['formaction'] == 'edituser'
		     && isset($_POST['pass1'])      && $_POST['pass1'] != ''
		     && isset($_POST['pass2'])											 ) {
			if ( $_POST['pass1'] == $_POST['pass2'] ) {
				require_once('db.class.php');
				require_once '../credentials.php';
				$db = new db( $__db_connstr_music__, $__db_username_music__, $__db_password_music__ );
				if ( $db->editpassword( $session->userid, $_POST['pass1'] ) ) {
					$errormsg = 'Successfully changed password.';
				} else {
					$errormsg = 'An error occurred.';
				}
			} else {
				$errormsg = 'The two passwords do not match!';
			}
		}
	}
	
	if ( isset($errormsg) )
		echo '<p>'.$errormsg.'</p>';
?>
<form action="index.php?section=edituser" method="post">
<table><tr><td>Password:</td><td><input type="password" name="pass1" /></td></tr>
<tr><td>Confirm Password:</td><td><input type="password" name="pass2" /></td></tr>
<tr><td><input type="hidden" name="formaction" value="edituser" /></td>
<td><input type="submit" value="Change Password" /></td></tr></table></form>