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
	
	$uploaders = $db->get_users_with_rank(3);
	
	if ( isset($_POST['formaction']) && $_POST['formaction'] == 'Add' ) {
		$date_day = (int)$_POST['date_day'];
		$date_month = (int)$_POST['date_month'];
		$date_year = (int)$_POST['date_year'];
		$uploaderid = (int)$_POST['uploaderid'];
	
		if ( $date_day > 0 && $date_month > 0 && $date_year > 0 ) {
			
		} else {
			echo '???';
		}
	}
	
?><div>
<form action="index.php?section=vgmotd-urladd&id=<?php echo $_GET['id']; ?>" method="post" enctype="multipart/form-data">
<table>
	<tr>
		<td>Date:</td>
		<td>
<select name="date_day"><?php
for ( $i = 1; $i <= 31; $i++ ) {
	echo '<option value="'.$i.'">'.$i.'</option>';
}
 ?></select>
<select name="date_month">
<option value="1">January</option>
<option value="2">February</option>
<option value="3">March</option>
<option value="4">April</option>
<option value="5">May</option>
<option value="6">June</option>
<option value="7">July</option>
<option value="8">August</option>
<option value="9">September</option>
<option value="10">October</option>
<option value="11">November</option>
<option value="12">December</option>
</select>
<select name="date_year"><?php
for ( $i = 2010; $i <= 2015; $i++ ) {
	echo '<option value="'.$i.'">'.$i.'</option>';
}
 ?></select>
</td>
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
					foreach ( $uploaders as $uploader ) {
						echo '<option value="'.$uploader->userid.'">'.$uploader->username.'</option>';
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