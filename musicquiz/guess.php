<?php
if ( !isset( $session ) ) {
	die();
}

	require_once('db.class.php');
	include '../credentials.php';
    $db = new db( $__db_connstr_music__, $__db_username_music__, $__db_password_music__ );
	
	$halfguess = ( $session->user->halfguess != 0 );
	
	if ( isset( $_POST['formaction'] ) && $_POST['formaction'] == 'guess' ) {
		if ( isset($_POST['game']) ) {
			$_POST['game'] = trim($_POST['game']);
		} else {
			$_POST['game'] = null;
		}
		if ( isset($_POST['song']) ) {
			$_POST['song'] = trim($_POST['song']);
		} else {
			$_POST['song'] = null;
		}
		if ( $_POST['game'] != null && $_POST['game'] != '' ) {
			$gameguess = stripslashes($_POST['game']);
		} else {
			$gameguess = false;
		}
		if ( $_POST['song'] != null && $_POST['song'] != '' ) {
			$songguess = stripslashes($_POST['song']);
		} else {
			$songguess = false;
		}
		
		if ( $gameguess || $songguess ) {
			$_POST['songid'] = (int)$_POST['songid'];
			
			$song_already_guessed = $db->song_already_guessed( $session->userid, $_POST['songid'] );
			if ( $song_already_guessed ) {
				//returns 0 = nope, 1 = game guessed / song not, 2 = game not / song guessed
				$halfguessvalue = $db->song_halfguessed( $session->userid, $_POST['songid'] );
			} else {
				$halfguessvalue = 0;
			}
			
			$song = $db->get_song( $_POST['songid'] );
			$song->check_guess( $gameguess, $songguess );
			$gamecorrect = $song->game_correct;
			$songcorrect = $song->name_correct;
			
			if ( $halfguessvalue !== 0 ) {
				$guess_song_return = $db->update_guess( $session->userid, $_POST['songid'], $gameguess, $gamecorrect, $songguess, $songcorrect, true );
			} else {
				$guess_song_return = $db->guess_song( $session->userid, $_POST['songid'], $gameguess, $gamecorrect, $songguess, $songcorrect, $halfguess );
			}
			
			if ( $guess_song_return ) {
				if ( $gameguess ) {
					if ( $gamecorrect ) {
						echo '<p>Correctly guessed the game! ('.$song->games[0].')</p>';
					} else {
						echo '<p>Incorrect game guess. The expected game was "'.$song->games[0].'".</p>';
					}
				} else {
					if ( $halfguessvalue === 0 ) {
						if ( $halfguess ) {
							echo '<p>Not guessed the game.</p>';
						} else {
							echo '<p>Not guessed the game. The expected game was "'.$song->games[0].'".</p>';
						}
					}
				}
				
				if ( $songguess ) {
					if ( $songcorrect ) {
						echo '<p>Correctly guessed the songname! ('.$song->names[0].')</p>';
					} else {
						echo '<p>Incorrect song name guess. The expected name was "'.$song->names[0].'".</p>';
					}
				} else {
					if ( $halfguessvalue === 0 ) {
						if ( $halfguess ) {
							echo '<p>Not guessed the song name.</p>';
						} else {
							echo '<p>Not guessed the song name. The expected name was "'.$song->names[0].'".</p>';
						}
					}
				}
			}
		}
	}
	
	if ( isset($_GET['skipsong']) ) {
		if ( $db->skip_song( $session->userid, $_GET['skipsong'] ) ) {
			echo '<p>Skipped song.</p>';
		} else {
			echo '<p>Skipping song failed.</p>';
		}
	} else if ( isset($_GET['giveup']) ) {
		if ( $db->give_up( $session->userid, $_GET['giveup'] ) ) {
			$giveupsong = $db->get_song( $_GET['giveup'] );
			echo '<p>Gave up on song. The song was '.$giveupsong->games[0].' - '.$giveupsong->names[0].'.</p>';
		} else {
			echo '<p>Giving up failed.</p>';
		}
	} else if ( isset($_GET['clear_skipped']) ) {
		if ( $_GET['clear_skipped'] == 'ask' ) {
			echo '<p><b>Are you really sure you want to clear your skipped songs? <a href="index.php?section=guess&clear_skipped=yes">Click here to confirm.</a></b></p>';
		} else if ( $_GET['clear_skipped'] == 'yes' ) {
			if ( $db->clear_skipped_songs($session->userid) ) {
				echo '<p>Cleared skipped songs.</p>';
			}
		}
	}
	
	if ( !isset($_GET['order']) ) {
		if ( $session->user->guessorder == 1 ) {
			$_GET['order'] = 'random';
		} else if ( $session->user->guessorder == 2 ) {
			$_GET['order'] = 'percent';
		} else {
			$_GET['order'] = 'default';
		}
	} else {
		if ( $session->user->guessorder != 0 && $_GET['order'] == 'default' ) {
			$db->set_guessorder( $session->userid, 0 );
		} else if ( $session->user->guessorder != 1 && $_GET['order'] == 'random' ) {
			$db->set_guessorder( $session->userid, 1 );
		} else if ( $session->user->guessorder != 2 && $_GET['order'] == 'percent' ) {
			$db->set_guessorder( $session->userid, 2 );
		}
	}
	
	if ( isset($_GET['halfguess']) ) {
		if ( $_GET['halfguess'] == 'on' ) {
			$db->set_halfguess( $session->userid, 1 );
			$halfguess = true;
		} else if ( $_GET['halfguess'] == 'off' ) {
			$db->set_halfguess( $session->userid, 0 );
			$halfguess = false;
		}
	}
	
	if ( $halfguess ) {
		echo '<p>Halfguessing is enabled. <a href="index.php?section=guess&halfguess=off">Click here to turn it off.</a></p>';
	} else {
		echo '<p>Halfguessing is disabled. <a href="index.php?section=guess&halfguess=on">Click here to turn it on. (experimental feature, read FAQ!)</a></p>';
	}
	
	if ( !isset($_GET['autoplay']) ) {
		$autoplay = ( $session->user->autoplay != 0 );
	} else {
		if ( $session->user->autoplay != 0 && $_GET['autoplay'] == 'off' ) {
			$db->set_autoplay( $session->userid, 0 );
			$autoplay = false;
		} else if ( $session->user->autoplay != 1 && $_GET['autoplay'] == 'on' ) {
			$db->set_autoplay( $session->userid, 1 );
			$autoplay = true;
		} else {
			$autoplay = ( $session->user->autoplay != 0 );
		}
	}
	
	if ( $autoplay ) {
		echo '<p>Autoplay is enabled. <a href="index.php?section=guess&autoplay=off">Click here to turn it off.</a></p>';
	} else {
		echo '<p>Autoplay is disabled. <a href="index.php?section=guess&autoplay=on">Click here to turn it on.</a></p>';
	}
	
	$skipped = $db->get_amount_skipped_songs($session->userid);
	if ( isset( $_GET['songid'] ) ) {
		$song = $db->get_song($_GET['songid'], false);
	} else if ( $_GET['order'] == 'random' ) {
		$song = $db->get_random_unguessed_song( $session->userid );
		echo '<p>Currently playing with random song order. Change to: <a href="index.php?section=guess&order=default">Default</a>, <a href="index.php?section=guess&order=percent">Percentage-based</a></p>';
	} else if ( $_GET['order'] == 'percent' ) {
		$song = $db->get_calcdiff_unguessed_song( $session->userid );
		echo '<p>Currently playing with percentage-based song order. Change to: <a href="index.php?section=guess&order=default">Default</a>, <a href="index.php?section=guess&order=random">Random</a></p>';
	} else {
		$song = $db->get_next_song( $session->userid );
		echo '<p>Currently playing with default song order. Change to: <a href="index.php?section=guess&order=random">Random</a>, <a href="index.php?section=guess&order=percent">Percentage-based</a></p>';
	}
	
	if ( $skipped && $skipped > 0 ) {
		echo '<p>'.$skipped.' songs have been skipped. <a href="index.php?section=guess&clear_skipped=ask">Clear</a></p>';
	}
	
	if ( !$song ) {
		if ( isset($_GET['songid']) ) {
			echo '<p>Selected song does not exist!</p>';
		} else {
			echo '<p>No unguessed songs currently available!</p>';
		}
		return;
	}



	$song_already_guessed = $db->song_already_guessed( $session->userid, $song->songid );

	if ( !$song_already_guessed ) {

?><form action="index.php?section=guess" method="post" enctype="multipart/form-data" name="guessform"><table>
<tr><td>Game:</td><td><input name="game" type="text" onfocus="formInUse = true;" /></td></tr>
<tr><td>Song:</td><td><input name="song" type="text" onfocus="formInUse = true;" /></td></tr>
<tr><td><input type="hidden" name="formaction" value="guess" /><input type="hidden" name="songid" value="<?php echo $song->songid; ?>" /></td>
<td><input type="submit" value="Guess!" /> <a href="index.php?section=guess&skipsong=<?php echo $song->songid; ?>">Skip this song</a></td></table>
</form><a href="index.php?section=guess&giveup=<?php echo $song->songid; ?>">Give up on this song (show game and songname)</a><?php

	} else {
		//returns 0 = nope, 1 = game guessed / song not, 2 = game not / song guessed
		$halfguessvalue = $db->song_halfguessed( $session->userid, $song->songid );
		
		if ( $halfguessvalue != 0 ) {
?><form action="index.php?section=guess" method="post" enctype="multipart/form-data" name="guessform"><table><?php
if ( $halfguessvalue == 2 ) echo '<tr><td>Game:</td><td><input name="game" type="text" onfocus="formInUse = true;" /></td></tr>';
if ( $halfguessvalue == 1 ) echo '<tr><td>Song:</td><td><input name="song" type="text" onfocus="formInUse = true;" /></td></tr>';
?><tr><td><input type="hidden" name="formaction" value="guess" /><input type="hidden" name="songid" value="<?php echo $song->songid; ?>" /></td>
<td><input type="submit" value="Guess!" /></td></table>
</form><a href="index.php?section=guess&giveup=<?php echo $song->songid; ?>">Give up on this song (show game and songname)</a><?php
		} else {
			echo '<p>Already guessed this song.</p>';
		}
	}
?><p><object width="640" height="240"><param name="movie" value="http://www.youtube.com/v/<?php echo $song->url; if ( $autoplay ) echo '&autoplay=1'; ?>&version=3&showinfo=0&modestbranding=1&rel=0&autohide=0"></param>
<param name="allowFullScreen" value="false"></param>
<param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/<?php echo $song->url; if ( $autoplay ) echo '&autoplay=1'; ?>&version=3&showinfo=0&modestbranding=1&rel=0&autohide=0" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="640" height="240"></embed></object></p><?php ?>