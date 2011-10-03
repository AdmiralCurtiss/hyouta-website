<?php
if ( !isset( $session ) ) {
	die();
}

if ( !$session->logged_in ) {
	if ( isset($_POST['formaction']) && $_POST['formaction'] == 'register' ) {
		$register_return = include 'register.php';
		echo '<p>'.$register_return.'</p>';

		if ( isset($register_success) && $register_success == true ) {
			$_SESSION['section'] = 'main';
			return;
		}
	}
?>

<p><h2>! Spoiler Notice !</h2>
Due to the very nature of this site, it's possible that you will get spoiled on certain songs, or maybe even find out plot or other details of some games through the song names. By registering, you acknowledge this possibility.</p>

<form action="index.php?section=register" method="post">
    <fieldset>
        <legend>Register</legend>
		<table>
			<tr>
				<td>Username:</td>
				<td><input type="text" name="user"<?php if ( isset($_POST['user']) ) echo ' value='.$_POST['user']; ?> /></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input type="password" name="pass1" /></td>
			</tr>
			<tr>
				<td>Retype Pass:</td>
				<td><input type="password" name="pass2" /></td>
			</tr>
			<tr>
				<td><input type="hidden" name="formaction" value="register" /></td>
				<td><input type="submit" value="Register" /></td>
			</tr>
		</table>
    </fieldset>
</form>
<?php 
} else {
	include 'main.php';
}
?>