<?php
if ( !isset( $session ) ) {
	die();
}

function songedit($db) {
	if ( $_POST['gamename_existing'] == 0 ) {
		$newgameid = (int)$db->get_free_gameid();
		$using_new_game = true;
	} else {
		$newgameid = (int)$_POST['gamename_existing'];
		$using_new_game = false;
	}
	
	//convert url to youtube-v=-value
	//search for the start of the video identifyer
	$song_url_plain_link = false;
	$urlposition = strpos( $_POST['url'], '?v=' );
	if ( $urlposition === false ) {
		$urlposition = strpos( $_POST['url'], '&v=' );
		if ( $urlposition === false ) {
			$song_url_plain_link = true;
		}
	}
	if ( $song_url_plain_link === false ) {
		//remove till start, then search for the end of video identifyer
		$_POST['url'] = substr( $_POST['url'], $urlposition+3 );
		$urlposition = strpos( $_POST['url'], '&' );
		if ( $urlposition !== false ) {
			$_POST['url'] = substr( $_POST['url'], 0, $urlposition );
		}
	}
	
	//edit song
	$db->edit_song( $_GET['songid'], $_POST['url'], $_POST['diff'], $newgameid, isset($_POST['available']) );
	
	
	//insert new games
	$_POST['gamename'] = trim($_POST['gamename']);
	if ( $_POST['gamename'] != '' ) {
		$newgamenames = explode(',', $_POST['gamename']);
		foreach ( $newgamenames as $newgame ) {
			$db->add_game( $newgameid, trim($newgame) );
		}
	}
	
	//insert new songnames
	$_POST['songname'] = trim($_POST['songname']);
	if ( $_POST['songname'] != '' ) {
		$newsongnames = explode(',', $_POST['songname']);
		foreach ( $newsongnames as $newsongname ) {
			$db->add_songname( $_GET['songid'], trim($newsongname) );
		}
	}
	
	return true;
}

if ( $session->logged_in && $session->user->is_admin() ) {
	include '../credentials.php';
	$db = new db( $__db_connstr_music__, $__db_username_music__, $__db_password_music__ );
	
	if ( isset($_POST['formaction']) && $_POST['formaction'] == 'editsong' ) {
		if ( songedit($db) ) {
			echo '<p>Edited Song.</p>';
			$_POST['songname'] = '';
			$_POST['gamename'] = '';
		} else {
			echo '<p>Failed editing song...</p>';
		}
	}

	if ( isset( $_GET['songid'] ) ) {
		$_GET['songid'] = (int)$_GET['songid'];
		if ( $_GET['songid'] > 0 ) {
			$editsong = $db->get_song( $_GET['songid'] );
		} else if ( $_GET['songid'] <= -2 ) {
			$_GET['songid'] = 0;
		}
	} else {
		$_GET['songid'] = 0;
	}
	
	echo '<form action="index.php" method="get"><input type="hidden" name="section" value="songedit" />';
	echo '<select name="songid"><option value="-1"> --- Show unavailable songs --- </option>';
	echo '<option value="0"> --- Select a song --- </option>';
	$all_songs = $db->get_all_songs();
	$songlist = array();
	$songavail = array();
	foreach ( $all_songs as $song ) {
		$songlist[$song->songid] = $song->games.' - '.$song->names.' --- { '.$song->difficulty.' }';
		$songavail[$song->songid] = $song->available;
	}
	asort($songlist);
	foreach ( $songlist as $id => $song ) {
		echo '<option value="'.$id.'"'.( $_GET['songid'] == $id ? ' selected' : '' ).'>'.( $songavail[$id] ? '[A] ' : '[U] ' ).$song.'</option>';
	}
	echo '</select> <input type="submit" value="Select for Editing" /></form>';
	
	//list & publish unavailable songs
	if ( $_GET['songid'] == -1 ) {
		if ( isset ( $_POST['publishsongs'] ) ) {
			foreach ( $_POST['publishsongs'] as $publishid ) {
				if ( !$db->change_song_availability( $publishid, true ) ) {
					echo 'Error while making song '.$publishid.' available!';
				}
			}
		}
		
		$unavailable_songs = $db->get_all_songs(true);
		echo '<form action="index.php?section=songedit&songid=-1" method="post">';
	
		$unavailable_songlist = array();
		foreach ( $unavailable_songs as $usong ) {
			$unavailable_songlist[$usong->songid] = $usong->games.' - '.$usong->names.' { '.$usong->difficulty.' }';
		}
		asort($unavailable_songlist);
		foreach ( $unavailable_songlist as $id => $song ) {
			echo '<input type="checkbox" name="publishsongs[]" value="'.$id.'" /> '
				.'<a href="index.php?section=songedit&songid='.$id.'">'.$id.': '.$song.'</a><br />';
		}
		echo '<input type="submit" value="Make these songs available!" /></form>';
	}
	
	if ( isset( $editsong ) && $editsong != false ) {
		require_once 'sgname.class.php';
?><p><object width="640" height="240"><param name="movie" value="http://www.youtube.com/v/<?php echo $editsong->url; ?>&version=3&showinfo=0&modestbranding=1&rel=0&autohide=0"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/<?php echo $editsong->url; ?>&version=3&showinfo=0&modestbranding=1&rel=0&autohide=0" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="640" height="240"></embed></object></p>
<form action="index.php?section=songedit&songid=<?php echo $editsong->songid; ?>" method="post">
    <fieldset>
        <legend>Edit Song</legend>
		<table>
			<tr>
				<td width="100">Song URL (Youtube):</td>
				<td><input type="text" name="url" value="<?php echo $editsong->url; ?>" /></td>
				<td>Please only use self-uploaded songs that have no information that would help identify the song (this includes the filename of the uploaded file!) and are marked as "Unlisted". Preferably use ~30sec clips of the song only. Also disable comments.<br>The expected URL-type is "http://www.youtube.com/watch?v=XXX". Extra parameters will be removed automatically. You may also simply use the XXX parameter.</td>
			</tr>
			<tr>
				<td>Difficulty:</td>
				<td align="right"><select name="diff"><?php for ($i = 1; $i <= 100; $i++) echo '<option value="'.$i.'"'.( $editsong->difficulty == $i ? ' selected' : '' ).'>'.$i.'</option>'; ?></select></td>
				<td>Songs with higher difficulty will appear later during sequential guessing.</td>
			</tr>
			<tr>
				<td>Current Game Group<?php
					echo ' (<a href="index.php?section=nameedit&type=game&id='.$editsong->gameid.'">Edit</a>)'
				?>:</td>
				<td colspan="2"><select name="gamename_existing"><option value="0"> --- create a new game group --- </option><?php
					$gamelist = sgname::games_to_list( $db->get_gamelist() );
					foreach ( $gamelist as $id => $game ) {
						echo '<option value="'.$id.'"'.( $editsong->gameid == $id ? ' selected' : '' ).'>'.$game.'</option>';
					}
				?></select></td>
				</td>
			</tr>
			<tr>
				<td>Add Game(s) to Group:</td>
				<td><input type="text" name="gamename"<?php if ( isset($_POST['gamename']) ) echo ' value="'.$_POST['gamename'].'"'; ?> /></td>
				<td>If entering multiple, seperate using commas. Leave blank to not change.<br>Note that adding a game to an existing game group will affect ALL songs using that game group, so be careful!</td>
			<tr>
				<td>Current Song Name<?php
					$array_length = count($editsong->names);
					if ( $array_length > 1 ) echo 's';
					
					echo ' (<a href="index.php?section=nameedit&type=songname&id='.$editsong->songid.'">Edit</a>)'
				?>:</td>
				<td colspan="2"><?php 
				
				if ( $array_length != 0 ) {
					$songname_string = '';
					for ( $i = 0; $i < $array_length-1; $i++ ) {
						$songname_string .= $editsong->names[$i].', ';
					}
					$songname_string .= $editsong->names[$array_length-1];
					echo $songname_string;
				} else {
					echo '<i>No name set!</i>';
				}
				?></td>
			</tr>
			<tr>
				<td>Add Song Name(s):</td>
				<td><input type="text" name="songname"<?php if ( isset($_POST['songname']) ) echo ' value="'.$_POST['songname'].'"'; ?> /></td>
				<td>If entering multiple, seperate using commas. Leave blank to not change.</td>
			</tr>
			<tr>
				<td><input type="hidden" name="formaction" value="editsong" /></td>
				<td align="right"><input type="submit" value="Edit Song" /></td>
				<td><input type="checkbox" name="available" <?php if ( $editsong->available ) echo 'checked'; ?> /> Song is available for guessing.</td>
			</tr>
		</table>
    </fieldset>
</form><?php
		
		// next and previous song
		echo '<table width="100%"><tr>'
			.'<td width="50%"><a href="index.php?section=songedit&songid='.($editsong->songid-1).'">&lt;-- Previous Song</a></td>'
			.'<td width="50%" align="right"><a href="index.php?section=songedit&songid='.($editsong->songid+1).'">Next Song --&gt;</a></td>'
			.'</tr></table>';

	} else {
		if ( $_GET['songid'] == 0 ) {
			foreach ( $songlist as $id => $song ) {
				echo '<span class="'.( $songavail[$id] ? 'songavailable' : 'songnotavailable' ).'">'
					.'<a href="index.php?section=songedit&songid='.$id.'">'.$song.'</a></span><br />';
			}
		}
	}
} else {
	include 'main.php';
}
?>