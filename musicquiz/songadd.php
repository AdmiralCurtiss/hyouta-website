<?php
if ( !isset( $session ) ) {
	die();
}

function songadd($db) {
	if ( $_POST['gamename_existing'] == 0 ) {
		if ( $_POST['gamename'] != '' ) {
			$newgameid = $db->get_free_gameid();
			$using_new_game = true;
		} else {
			$newgameid = 0;
			$using_new_game = false;
		}
	} else {
		$newgameid = (int)$_POST['gamename_existing'];
		$using_new_game = false;
	}
	
	//insert new song
	$newsongid = $db->add_song( $_POST['url'], $_POST['diff'], $newgameid, isset($_POST['available']) );
	
	//possibly insert new games
	if ( $using_new_game ) {
		$newgamenames = explode(',', $_POST['gamename']);
		$i = 0;
		foreach ( $newgamenames as $newgame ) {
			$i++;
			$db->add_game( $newgameid, trim($newgame), $i );
		}
	}
	
	//insert new songnames
	if ( $_POST['songname'] != '' ) {
		$newsongnames = explode(',', $_POST['songname']);
		$i = 0;
		foreach ( $newsongnames as $newsongname ) {
			$i++;
			$db->add_songname( $newsongid, trim($newsongname), $i );
		}
	}
	
	return $newsongid;
}

if ( $session->logged_in && $session->user->is_admin() ) {
	include '../credentials.php';
	$db = new db( $__db_connstr_music__, $__db_username_music__, $__db_password_music__ );
	
	if ( isset($_POST['url']) ) {
		$unmodified_url = $_POST['url'];
	}
	
	if ( isset($_POST['formaction']) && $_POST['formaction'] == 'addsong' ) {
		$songinsert_error_message = false;
		
		if ( isset($_POST['url']) && isset($_POST['diff']) && isset($_POST['gamename']) && isset($_POST['gamename_existing']) && isset($_POST['songname']) ) {
			//convert url to youtube-v=-value
			//search for the start of the video identifyer
			$urlposition = strpos( $_POST['url'], '?v=' );
			if ( $urlposition === false ) {
				$urlposition = strpos( $_POST['url'], '&v=' );
				if ( $urlposition === false ) {
					$songinsert_error_message = 'Could not find Youtube video identifyer in the given URL!';
				}
			}
			
			if ( $songinsert_error_message === false ) {
				//remove till start, then search for the end of video identifyer
				$_POST['url'] = substr( $_POST['url'], $urlposition+3 );
				$urlposition = strpos( $_POST['url'], '&' );
				if ( $urlposition !== false ) {
					$_POST['url'] = substr( $_POST['url'], 0, $urlposition );
				}
				
				$newsongid = songadd($db);
				if ( $newsongid ) {
					$addedsong = $db->get_song( $newsongid );
					echo '<p>Added <span class="'.( $addedsong->available ? 'songavailable' : 'songnotavailable' ).'">'
						.( $addedsong->games ? $addedsong->games[0] : '[NULL]' ).' - '.( $addedsong->names ? $addedsong->names[0] : '[NULL]' )
						.'</span>! The Song ID is <a href="index.php?section=guess&songid='.$newsongid.'">'.$newsongid.'</a> <a href="index.php?section=songedit&songid='.$newsongid.'">e</a>.</p>';
					$_POST = null;
					unset($unmodified_url);
				} else {
					echo '<p>Failed adding new song. It\'s possible that only parts of the song information have been added to the database, please confirm that this is not the case before trying again.</p>';
				}
			} else {
				echo '<p>'.$songinsert_error_message.'</p>';
			}
			
		} else {
			echo '<p>Not all expected values were transfered to the server, please try again.</p>';
		}
	}
	
	echo 'Current highest Song ID: '.$db->get_current_highest_songid();
	
	if ( !isset($_POST['diff']) ) $_POST['diff'] = 0;
	if ( !isset($_POST['gamename_existing']) ) $_POST['gamename_existing'] = 0;
?><form action="index.php?section=songadd" method="post">
    <fieldset>
        <legend>Add a new Song</legend>
		<table>
			<tr>
				<td>Song URL (Youtube):</td>
				<td><input type="text" name="url"<?php if ( isset($unmodified_url) ) echo ' value="'.$unmodified_url.'"'; ?> /></td>
				<td>Please only use self-uploaded songs that have no information that would help identify the song (this includes the filename of the uploaded file!) and are marked as "Unlisted". Preferably use ~30sec clips of the song only. Also disable comments.<br>The expected URL-type is "http://www.youtube.com/watch?v=XXX". Extra parameters will be removed automatically.</td>
			</tr>
			<tr>
				<td>Difficulty:</td>
				<td align="right"><select name="diff"><?php for ($i = 1; $i <= 100; $i++) echo '<option value="'.$i.'"'.( $_POST['diff'] == $i ? ' selected' : '' ).'>'.$i.'</option>'; ?></select></td>
				<td>Songs with higher difficulty will appear later during sequential guessing.</td>
			</tr>
			<tr>
				<td>Game(s):</td>
				<td><input type="text" name="gamename"<?php if ( isset($_POST['gamename']) ) echo ' value="'.$_POST['gamename'].'"'; ?> /></td>
				<td><select name="gamename_existing"><option value="0"> --- or select an existing group of games --- </option><?php
					require_once 'sgname.class.php';
					$gamelist = sgname::games_to_list( $db->get_gamelist() );
					foreach ( $gamelist as $id => $game ) {
						echo '<option value="'.$id.'"'.( $_POST['gamename_existing'] == $id ? ' selected' : '' ).'>'.$game.'</option>';
					}
					
				?></select></td>
				</td>
			<tr>
				<td>Song Name(s):</td>
				<td><input type="text" name="songname"<?php if ( isset($_POST['songname']) ) echo ' value="'.$_POST['songname'].'"'; ?> /></td>
				<td>If entering multiple Games or Song Names, seperate them using commas.</td>
			</tr>
			<tr>
				<td><input type="hidden" name="formaction" value="addsong" /></td>
				<td align="right"><input type="submit" value="Add new Song" /></td>
				<td><input type="checkbox" name="available" /> Song is available for guessing.</td>
			</tr>
		</table>
    </fieldset>
</form>
<?php 
} else {
	include 'main.php';
}
?>