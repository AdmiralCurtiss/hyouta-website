<form action="index.php" method="post">
	<fieldset>
		<legend>Login</legend>
		<table>
			<tr>
				<td>User:</td>
				<td><input type="text" name="user" size="10" /></td>
			</tr>
			<tr>
				<td>Pass:</td>
				<td><input type="password" name="pass" size="10" /></td>
			</tr>
			<tr>
				<td colspan=2><input type="checkbox" name="stayloggedin" value="yes" checked />&nbsp;Stay logged in.</td>
			</tr>
			<tr>
				<td><input type="hidden" name="formaction" value="login" /></td>
				<td><input type="submit" value="Login" /></td>
			</tr>
		</table>
	</fieldset>
	<?php //<a href="index.php?section=forget">Forgot your password?</a> ?>
</form>