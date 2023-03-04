<?php
if ( !isset( $session ) ) {
	die();
}

if ( $session->logged_in && $session->user->is_admin() ) {
	$songid = (int)$_GET['id'];
	if ( $songid <= 0 ) {
		$editing = false;
	} else {
		$editing = true;
	}

	require_once 'db.class.php';
	//require_once 'song.class.php';
	require_once 'url_container.class.php';

	require_once '../credentials.php';
	$db = new db( $__db_connstr_music__, $__db_username_music__, $__db_password_music__ );
	
	$uploaders = $db->get_users_with_rank(3);

	if ( isset($_POST['formaction']) && ( $_POST['formaction'] == 'Add' || $_POST['formaction'] == 'Edit' ) ) {
		$date_day = (int)$_POST['date_day'];
		$date_month = (int)$_POST['date_month'];
		$date_year = (int)$_POST['date_year'];
		$uploaderid = (int)$_POST['uploaderid'];
		$nodate = ( isset($_POST['nodate']) && $_POST['nodate'] == "yes" );
	
		if ( ( ( $date_day > 0 && $date_month > 0 && $date_year > 0 ) || $nodate ) && $uploaderid > 0
			&& isset($_POST['artist']) && isset($_POST['game']) && isset($_POST['song']) ) {
			$artist = trim($_POST['artist']);
			$game = trim($_POST['game']);
			$song = trim($_POST['song']);
			$comment = trim($_POST['comment']);
			$comment = $comment == '' ? false : $comment;
			
			$date_for_function = $nodate ? false : $date_year.'-'.$date_month.'-'.$date_day;
			
			if ( $artist != '' && $game != '' && $song != '' ) {
				if ( $editing ) {
					$edit_success = $db->edit_vgmusicoftheday_song( $songid, $date_for_function, $artist, $game, $song, 0, $uploaderid, $comment );
					if ( !$edit_success ) echo 'Editing failed!';
				} else {
					$songid = $db->add_vgmusicoftheday_song( $date_for_function, $artist, $game, $song, 0, $uploaderid, $comment );
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
	
	$gettoday = true;
	if ( $editing ) {
		$current_song = $db->get_vgmusicoftheday_song($songid);
		if ( !$current_song ) die();
		
		echo '<div align="center"><a href="index.php?section=vgmotd-add-edit">Add a new song</a></div><br>';
		
		echo '<div>'
			.'Editing <b>'.$current_song->games.' - '.$current_song->names.' by '.$current_song->artist.'</b>, uploaded by '.$current_song->username.'.<br><br></div>';
		echo '<div><a href="index.php?section=vgmotd-urladd&id='.$songid.'">Edit this song&#39;s URLs</a></div><br>';
		
		if ( $current_song->date != null ) {
			$gettoday = false;
			$expldate = explode('-', $current_song->date);
			$today_day   = (int)$expldate[2];
			$today_month = (int)$expldate[1];
			$today_year  = (int)$expldate[0];
		}
	}
	
	if ( $gettoday ) {
		if ( isset($date_day) && $date_day > 0 ) {
			$today_day   = $date_day;
		} else {
			$today_day   = (int)date('d');
		}
		if ( isset($date_month) && $date_month > 0 ) {
			$today_month = $date_month;
		} else {
			$today_month = (int)date('m');
		}
		if ( isset($date_year) && $date_year > 0 ) {
			$today_year  = $date_year;
		} else {
			$today_year  = (int)date('Y');
		}
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
$year_until_list = date('Y') + 1;
for ( $i = 2010; $i <= $year_until_list; $i++ ) {
	echo '<option value="'.$i.'"'.( $today_year == $i ? ' selected' : '' ).'>'.$i.'</option>';
}
 ?></select>
<input type="checkbox" name="nodate" value="yes"<?php if ( $editing ) if ( $current_song->date == null ) echo ' checked'; ?> /> Do not assign date (store song for later).
</td>
	</tr>
	<tr>
		<td>Artist:</td>
		<td><input type="text" name="artist" size="90"<?php if ( $editing ) echo ' value="'.$current_song->artist.'"'; else if ( isset($_POST['artist']) ) echo ' value="'.$_POST['artist'].'"'; ?>/></td>
	</tr>
	<tr>
		<td>Game:</td>
		<td><input type="text" name="game" size="90"<?php if ( $editing ) echo ' value="'.$current_song->games.'"'; else if ( isset($_POST['game']) ) echo ' value="'.$_POST['game'].'"'; ?>/></td>
	</tr>
	<tr>
		<td>Song:</td>
		<td><input type="text" name="song" size="90"<?php if ( $editing ) echo ' value="'.$current_song->names.'"'; else if ( isset($_POST['song']) ) echo ' value="'.$_POST['song'].'"'; ?>/></td>
	</tr>
	<tr>
		<td>Uploader:</td>
		<td><select name="uploaderid"><?php
					foreach ( $uploaders as $uploader ) {
						echo '<option value="'.$uploader->userid.'"';
						if ( $editing ) {
							if ( $current_song->userid == $uploader->userid ) {
								echo ' selected';
							}
						} else {
							if ( isset($uploaderid) ) {
								// when creating a new entry, and form has already been submitted & rejected
								if ( $uploaderid == $uploader->userid ) echo ' selected';
							} else {
								// when creating a new entry on first page load
								if ( $session->userid == $uploader->userid ) echo ' selected';
							}
						}
						echo '>'.$uploader->username.'</option>';
						//echo '---$current_song->userid = '.$current_song->userid;
						//echo '---$uploader->userid = '.$uploader->userid;
						//echo '---$editing = '.$editing;
					}
				?></select></td>
	</tr>
	<tr>
		<td>Comment:</td>
		<td><textarea name="comment" rows="7" cols="72"><?php if ( $editing ) echo $current_song->comment; else if ( isset($_POST['comment']) ) echo $_POST['comment']; ?></textarea></td>
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
		
		echo '<div>See Also:';
		$seealso = $db->get_vgmusicoftheday_songs_youtubeonly( 0, 1000000, 'day ASC', $current_song->games );
		foreach ($seealso as $s) {
			if ( $s->songid === $current_song->songid ) { continue; }
			if ( strtolower($s->games) !== strtolower($current_song->games) ) { continue; }
			echo '<br>';
			echo '<a href="index.php?section=vgmotd-add-edit&id='.$s->songid.'">Day '.$s->daynumber.'</a>: <a href="'.$s->url[0]->url.'">'.$s->url[0]->url.'</a>';
		}
		echo '<br></div>';
		
		echo '<div>Tag Suggestions: ';
		$tags = $current_song->suggest_tags();
		foreach ($tags as &$tag) {
			echo $tag.', ';
		}
		echo '</div><br><br><br><br>';
	}

} else {
	include 'main.php';
}
?>