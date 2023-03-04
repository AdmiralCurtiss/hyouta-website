<?php
if ( !isset( $session ) ) {
	die();
}

$register_attempt = false;
$register_success = false;
if (!$session->logged_in) {
	if (isset($_POST['formaction']) && $_POST['formaction'] === 'register') {
		$register_attempt = true;
		$register_return = 'Unknown error. Please try again. If this persists, try a different browser.';

		if ('POST' === $_SERVER['REQUEST_METHOD']) {
			if ($register_attempt === true && !isset($_POST['user'], $_POST['pass1'], $_POST['formaction'])) {
				$register_attempt = false;
				$register_return = 'Please only use the forms provided.';
			}
			if ($register_attempt === true && $_POST['pass1'] !== $_POST['pass2']) {
				$register_attempt = false;
				$register_return = 'Your passwords don&#39;t match, please re-type your password.';
			}
			if ($register_attempt === true) {
				$user = trim($_POST['user']);
				$pass = trim($_POST['pass1']);
				if ($user == '' || $pass == '') {
					$register_attempt = false;
					$register_return = 'Please fill out all fields in the form.';
				}
			}

			if ($register_attempt === true) {
				$register_success = $database->register( $user, $pass );
			}

			if ($register_attempt === true) {
				if ($register_success === true) {
					$register_return = 'Successfully registered, you may log in now.';
				} else {
					$register_return = 'An error occurred. Most likely, the username you&#39;re trying to register already exists, so please pick a different username.';
				}
			}
		}

		echo '<p>'.$register_return.'</p>';
	}

	if ($register_success === true) {
		// no need to re-print the register form
	} else {
		echo '<p><h2>! Spoiler Notice !</h2>';
		echo 'Due to the very nature of this site, it&#39;s possible that you will get spoiled on certain songs, or maybe even find out plot or other details of some games through the song names. By registering, you acknowledge this possibility.';
		echo '</p>';

		echo '<form action="index.php?section=register" method="post">';
			echo '<fieldset>';
				echo '<legend>Register</legend>';
				echo '<table>';
					echo '<tr>';
					echo '<td>Username:</td>';
					echo '<td><input type="text" name="user"';
					if (isset($_POST['user'])) { echo ' value="'.$_POST['user'].'"'; }
					echo ' /></td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td>Password:</td>';
					echo '<td><input type="password" name="pass1" /></td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td>Repeat Password:</td>';
					echo '<td><input type="password" name="pass2" /></td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td><input type="hidden" name="formaction" value="register" /></td>';
					echo '<td><input type="submit" value="Register" /></td>';
					echo '</tr>';
				echo '</table>';
			echo '</fieldset>';
		echo '</form>';
	}
} else {
	include 'main.php';
}
?>