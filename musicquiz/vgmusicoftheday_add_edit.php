<?php
if ( !isset( $session ) ) {
	die();
}

if ( $session->logged_in && $session->user->is_vgmusicoftheday() ) {
	$songid = (int)$_GET['id'];
	if ( $songid <= 0 ) {
		$editing = false;
	} else {
		$editing = true;
	}

	require_once 'db.class.php';
	//require_once 'song.class.php';
	require_once 'url_container.class.php';

	$db = new db($database);
	
	$uploaders = $db->get_users_with_rank(3);

	if ( isset($_POST['formaction']) && ( $_POST['formaction'] == 'Add' || $_POST['formaction'] == 'Edit' ) ) {
		$date_day = (int)$_POST['date_day'];
		$date_month = (int)$_POST['date_month'];
		$date_year = (int)$_POST['date_year'];
		$uploaderid = (int)$_POST['uploaderid'];
	
		if ( $date_day > 0 && $date_month > 0 && $date_year > 0 && $uploaderid > 0
			&& isset($_POST['artist']) && isset($_POST['game']) && isset($_POST['song']) ) {
			$artist = trim($_POST['artist']);
			$game = trim($_POST['game']);
			$song = trim($_POST['song']);
			
			if ( $artist != '' && $game != '' && $song != '' ) {
				
				if ( $editing ) {
					$edit_success = $db->edit_vgmusicoftheday_song( $songid, $date_year.'-'.$date_month.'-'.$date_day, $artist, $game, $song, 0, $uploaderid );
					if ( !$edit_success ) echo 'Editing failed!';
				} else {
					$songid = $db->add_vgmusicoftheday_song( $date_year.'-'.$date_month.'-'.$date_day, $artist, $game, $song, 0, $uploaderid );
					if ( $songid <= 0 ) {
						echo 'Adding failed!';
					} else {
						$editing = true;
					}
				}
				
			} else {
				echo 'Not all neccessary data provided.';
			}
		} else {
			echo 'Not all neccessary data provided.';
		}
	}
	
	if ( $editing ) {
		$current_song = $db->get_vgmusicoftheday_song($songid);
		if ( !$current_song ) die();
		
		echo '<div align="center"><a href="index.php?section=vgmotd-add-edit">Add a new song</a></div><br>';
		
		echo '<div>'
			.'Editing <b>'.$current_song->games.' - '.$current_song->names.' by '.$current_song->artist.'</b>, uploaded by '.$current_song->username.'.<br><br></div>';
		echo '<div><a href="index.php?section=vgmotd-urladd&id='.$songid.'">Edit this song\'s URLs</a></div><br>';
		
		$expldate = explode('-', $current_song->date);
		
		$today_day   = (int)$expldate[2];
		$today_month = (int)$expldate[1];
		$today_year  = (int)$expldate[0];
	} else {
		$today_day   = (int)date('d');
		$today_month = (int)date('m');
		$today_year  = (int)date('Y');
	}
	
?><div>
<form action="index.php?section=vgmotd-add-edit<?php if ( $editing ) echo '&id='.$songid; ?>" method="post" enctype="multipart/form-data">
<table>
	<tr>
		<td>Date:</td>
		<td><select name="date_day"><?php
for ( $i = 1; $i <= 31; $i++ ) {
	echo '<option value="'.$i.'"'.( $today_day == $i ? ' selected' : '' ).'>'.$i.'</option>';
}
 ?></select>
<select name="date_month">
<option value="1"<?php if ( $today_month == '1' ) echo ' selected'; ?>>January</option>
<option value="2"<?php if ( $today_month == '2' ) echo ' selected'; ?>>February</option>
<option value="3"<?php if ( $today_month == '3' ) echo ' selected'; ?>>March</option>
<option value="4"<?php if ( $today_month == '4' ) echo ' selected'; ?>>April</option>
<option value="5"<?php if ( $today_month == '5' ) echo ' selected'; ?>>May</option>
<option value="6"<?php if ( $today_month == '6' ) echo ' selected'; ?>>June</option>
<option value="7"<?php if ( $today_month == '7' ) echo ' selected'; ?>>July</option>
<option value="8"<?php if ( $today_month == '8' ) echo ' selected'; ?>>August</option>
<option value="9"<?php if ( $today_month == '9' ) echo ' selected'; ?>>September</option>
<option value="10"<?php if ( $today_month == '10' ) echo ' selected'; ?>>October</option>
<option value="11"<?php if ( $today_month == '11' ) echo ' selected'; ?>>November</option>
<option value="12"<?php if ( $today_month == '12' ) echo ' selected'; ?>>December</option>
</select>
<select name="date_year"><?php
for ( $i = 2010; $i <= ($today_year+1); $i++ ) {
	echo '<option value="'.$i.'"'.( $today_year == $i ? ' selected' : '' ).'>'.$i.'</option>';
}
 ?></select>
</td>
	</tr>
	<tr>
		<td>Artist:</td>
		<td><input type="text" name="artist" size="90"<?php if ( $editing ) echo ' value="'.$current_song->artist.'"'; ?>/></td>
	</tr>
	<tr>
		<td>Game:</td>
		<td><input type="text" name="game" size="90"<?php if ( $editing ) echo ' value="'.$current_song->games.'"'; ?>/></td>
	</tr>
	<tr>
		<td>Song:</td>
		<td><input type="text" name="song" size="90"<?php if ( $editing ) echo ' value="'.$current_song->names.'"'; ?>/></td>
	</tr>
	<tr>
		<td>Uploader:</td>
		<td><select name="uploaderid"><?php
					foreach ( $uploaders as $uploader ) {
						echo '<option value="'.$uploader->userid.'"'
						.( $editing
						   ? ( $current_song->userid == $uploader->userid ? ' selected' : '' )
						   : ( $session->userid == $uploader->userid ? ' selected' : '' )
						 )
						.'>'.$uploader->username.'</option>';
					}
				?></select></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" name="formaction" value="<?php if ( $editing ) echo 'Edit'; else echo 'Add'; ?>"/></td>
	</tr>
</table>
</form>
</div>
<?php

	if ( $editing ) {
		$current_song = $db->get_vgmusicoftheday_song($songid);
		if ( !$current_song ) die();
		
		echo '<div>Copy for Youtube:</div><br>';

		echo '<div>VGMusic of the Day '.$current_song->daynumber.': '.$current_song->games.' - '.$current_song->names.'</div><br>';
		
		echo '<div>Game: '.$current_song->games.'<br>Title: '.$current_song->names.'<br>Composer: '.$current_song->artist.'<br>Uploaded by '.$current_song->username.'</div>';
	}

} else {
	include 'main.php';
}
?>