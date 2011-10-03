<?php
if ( !isset( $session ) ) {
	die();
}
	require_once 'db.class.php';
	$db = new db($database);

	if ('POST' == $_SERVER['REQUEST_METHOD']) {
		if (!isset($_POST['user'], $_POST['pass1'], $_POST['formaction'])) {
			return INVALID_FORM;
		}
		if ( $_POST['pass1'] != $_POST['pass2'] ) {
			return 'Your passwords don\'t match, please reypte your password.';
		}
		if (	($user = trim($_POST['user'])) == '' OR
				($pass = trim($_POST['pass1'])) == ''	) {
			return EMPTY_FORM;
		}
		
		$register_success = $db->register( $user, $pass );
		
		if ( $register_success === true ) {
			return 'Successfully registered, you may log in now.';
		} else {
			return 'An error occurred. Most likely, the username you\'re trying to register already exists.';
		}
	}
?>
