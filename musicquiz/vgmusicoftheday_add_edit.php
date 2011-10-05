<?php
if ( !isset( $session ) ) {
	die();
}

if ( $session->logged_in && $session->user->is_vgmusicoftheday() ) {
	$_GET['id'] = (int)$_GET['id'];
	if ( $_GET['id'] <= 0 ) die();

	require_once 'db.class.php';
	//require_once 'song.class.php';
	require_once 'url_container.class.php';

	$db = new db($database);
	
	if ( isset($_POST['formaction']) ) {
		$_POST['type'] = (int)$_POST['type'];
		if ( $_POST['type'] <= 0 ) die();
	
		if ( $_POST['formaction'] == 'Add' ) {
			
		} else {
			echo '???';
		}
	}
	
?><div>
<form action="index.php?section=vgmotd-urladd&id=<?php echo $_GET['id']; ?>" method="post" enctype="multipart/form-data">
<table>
	<tr>
		<td>Date:</td>
		<td><select name="type"><option value="1">Jan</option></select></td>
	</tr>
	<tr>
		<td>Artist:</td>
		<td><input type="text" name="artist"/></td>
	</tr>
	<tr>
		<td>Game:</td>
		<td><input type="text" name="game"/></td>
	</tr>
	<tr>
		<td>Song:</td>
		<td><input type="text" name="song"/></td>
	</tr>
	<tr>
		<td>Uploader:</td>
		<td><select name="uploaderid"><?php
					foreach ( $types as $type ) {
						echo '<option value="'.$type->typeid.'"><img src="'.$type->icon.'" /> '.$type->name.'</option>';
					}
				?></select></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" name="formaction" value="Add"/></td>
	</tr>
</table>
</form>
</div>
<?php

} else {
	include 'main.php';
}
?>