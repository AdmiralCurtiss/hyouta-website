<?php
if ( !isset( $session ) ) {
	die();
}

	if ( $session->logged_in ) {
	require_once 'db.class.php';
	require_once 'song.class.php';

	$db = new db($database);

	if ( $session->user->is_admin() ) {
		if ( isset( $_GET['userid'] ) ) {
			$userid = (int)$_GET['userid'];
		} else {
			$userid = 0;
		}
		
		if ( isset( $_GET['songid'] ) ) {
			$songid = (int)$_GET['songid'];
		} else {
			$songid = 0;
		}
	} else {
		$userid = $session->userid;
		$songid = 0;
	}
	
	( isset($_GET['start']) && ($_GET['start'] >= 0) ) ? $_GET['start'] = (int)$_GET['start'] : $_GET['start'] =  0;
	( isset($_GET['show'])  && ($_GET['show']  >  0) ) ? $_GET['show'] =  (int)$_GET['show']  : $_GET['show']  = 50;
	

	$sorting_criteria = 'time DESC';
	$include_series_function = false;
	$halfguess_check = false;

	if ( isset($_GET['order']) ) {
		switch ( $_GET['order'] ) {
			case 'halfguess':
				$sorting_criteria = 'time DESC';
				$halfguess_check = true;
				break;
			case 'songid':
				$sorting_criteria = 'songid ASC';
				break;
			case 'songidD':
				$sorting_criteria = 'songid DESC';
				break;
			case 'series':
				$sorting_criteria = 'isnull ASC, seriesname ASC, gamename ASC, songname ASC';
				$include_series_function = true;
				$include_series = true;
				break;
			case 'seriesD':
				$sorting_criteria = 'isnull ASC, seriesname DESC, gamename DESC, songname DESC';
				$include_series_function = true;
				$include_series = true;
				break;
			case 'game':
				$sorting_criteria = 'gamename ASC, songname ASC';
				break;
			case 'gameD':
				$sorting_criteria = 'gamename DESC, songname DESC';
				break;
			case 'song':
				$sorting_criteria = 'songname ASC, gamename ASC';
				break;
			case 'songD':
				$sorting_criteria = 'songname DESC, gamename DESC';
				break;
			case 'ggame':
				$sorting_criteria = 'gameguess ASC, songguess ASC';
				break;
			case 'ggameD':
				$sorting_criteria = 'gameguess DESC, songguess DESC';
				break;
			case 'gsong':
				$sorting_criteria = 'songguess ASC, gameguess ASC';
				break;
			case 'gsongD':
				$sorting_criteria = 'songguess DESC, gameguess DESC';
				break;
			case 'gamepercent':
				$sorting_criteria = 'gamecorrect/(gameguessed+gamenoguess+skipped) DESC, songid ASC';
				break;
			case 'gamepercentA':
				$sorting_criteria = 'gamecorrect/(gameguessed+gamenoguess+skipped) ASC, songid ASC';
				break;
			case 'songpercent':
				$sorting_criteria = 'songcorrect/(songguessed+songnoguess+skipped) DESC, songid ASC';
				break;
			case 'songpercentA':
				$sorting_criteria = 'songcorrect/(songguessed+songnoguess+skipped) ASC, songid ASC';
				break;
			//case 'correct':
			//	$sorting_criteria = 'points ASC';
			//	break;
			//case 'correctD':
			//	$sorting_criteria = 'points DESC';
			//	break;
			default:
				break;
		}	
	}
	
	$songs = $db->get_guessed_songs( $userid, $songid, $_GET['start'], $_GET['show'], $sorting_criteria, $include_series_function, $halfguess_check );
	
	if ( $session->user->is_admin() && $session->userid == 1 ) { //display user dropdown for myself
		echo '<form action="index.php" method="get"><input type="hidden" name="section" value="results" /><select name="userid">'
			.'<option value="-1"'.( $userid == -1 ? ' selected' : '').'>--- Complete ---</option>'
			.'<option value="0"'.( $userid == 0 ? ' selected' : '').'>--- All ---</option>';
		
		$users = $db->get_all_users();
		foreach ( $users as $auser ) {
			echo '<option value="'.$auser->userid.'"'.( $auser->userid == $userid ? ' selected' : '').'>'.$auser->username.'</option>';
		}
		
		echo '</select><input type="submit" value="Show"></form>';
	}

	if ( !$songs ) {
		return;
	}

	$amount_guessed = $db->get_amount_guessed_songs( $userid, $songid );
	$amount_halfguessed = $db->get_amount_halfguessed_songs( $userid );
	
	if ( $userid > 0 ) {
		$amount_skipped = $db->get_amount_skipped_songs( $userid );
		$amount_gamecorrect = $db->get_amount_guessed_songs( $userid, $songid, 1 );
		$amount_songcorrect = $db->get_amount_guessed_songs( $userid, $songid, 2 );
		$amount_fullcorrect = $db->get_amount_guessed_songs( $userid, $songid, 3 );
		$amount_gamecorrect += $amount_fullcorrect;
		$amount_songcorrect += $amount_fullcorrect;
		$amount_gamecorrect += $db->get_amount_guessed_songs( $userid, $songid, 5 );
		$amount_songcorrect += $db->get_amount_guessed_songs( $userid, $songid, 6 );

		echo '<p>You guessed a total of '.$amount_guessed.' songs, skipped <a href="index.php?section=skipped'.( $session->user->is_admin() ? '&userid='.$userid : '' ).'">'.$amount_skipped.' songs</a>, correctly guessed the game '.$amount_gamecorrect.' times ('.round(($amount_gamecorrect/$amount_guessed)*100).'%) and the song name '.$amount_songcorrect.' times ('.round(($amount_songcorrect/$amount_guessed)*100).'%).</p>';
		
		if ( $amount_halfguessed > 0 ) {
			echo '<p><a href="index.php?section=results&order=halfguess'.( $session->user->is_admin() ? '&userid='.$userid : '' ).'">Show my '.$amount_halfguessed.' halfguessed songs.</a></p>';
		}
	} else {
		if ( $userid == 0 ) {
			echo '<p>A total of '.$amount_guessed.' songs are visible in full view.</p>';
		} else {
			if ( $songid > 0 ) {
				echo '<p>This song has been guessed '.$amount_guessed.' times.</p>';
			} else {
				echo '<p>A total of '.$amount_guessed.' songs have been guessed on this page.</p>';
			}
		}
	}
	
	if ( !isset($_GET['order']) || $_GET['order'] != 'halfguess' ) {
		$amount_guessed_pagecalc = $amount_guessed - $amount_halfguessed;
	} else {
		$amount_guessed_pagecalc = $amount_halfguessed;
	}
	
	$pagestable = '<table width="100%"><tr><td width="20%">';
	if ( $_GET['start'] > 0 ) { //previous page
		$pagestable .= '<a href="index.php?section=results&start='.($_GET['start']-$_GET['show']).'&show='.$_GET['show']
			.( ( $session->user->is_admin() && isset( $_GET['userid'] ) ) ? '&userid='.$userid : '' )
			.( ( $session->user->is_admin() && isset( $_GET['songid'] ) ) ? '&songid='.$songid : '' )
			.( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' )
			.'">&lt;-- Previous Page</a>';
	}
	$pagestable .= '</td><td align="center" width="60%">';
	
	//pagelist
	$pageamount = ceil($amount_guessed_pagecalc / $_GET['show']);
	if ( $pageamount != 1 ) {
		$pagelinkend = ( ( $session->user->is_admin() && isset( $_GET['userid'] ) ) ? '&userid='.$userid : '' )
					  .( ( $session->user->is_admin() && isset( $_GET['songid'] ) ) ? '&songid='.$songid : '' )
					  .( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' );
		for ( $i = 1 ; $i <= $pageamount ; $i++ ) {
			$pageshow = $_GET['show']*($i-1);
			if ( $pageshow == $_GET['start'] ) {
				$pagestable .= '<u>'.$i.'</u> ';
			} else {
				$pagestable .= '<a href="index.php?section=results&start='.$pageshow.'&show='.$_GET['show'].$pagelinkend.'">'.$i.'</a> ';
			}
		}
	}
	
	$pagestable .= '</td><td align="right" width="20%">';
	if ( ($_GET['start']+$_GET['show']) < $amount_guessed_pagecalc ) { //next page
		$pagestable .= '<a href="index.php?section=results&start='.($_GET['start']+$_GET['show']).'&show='.$_GET['show']
			.( ( $session->user->is_admin() && isset( $_GET['userid'] ) ) ? '&userid='.$userid : '' )
		    .( ( $session->user->is_admin() && isset( $_GET['songid'] ) ) ? '&songid='.$songid : '' )
			.( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' )
			.'">Next Page --&gt;</a>';
	}
	$pagestable .= '</td></tr></table>';
	
	echo $pagestable;

	$sorturl = 'index.php?section=results&start='.$_GET['start'].'&show='.$_GET['show']
			.( ( $session->user->is_admin() && isset( $_GET['userid'] ) ) ? '&userid='.$userid : '' )
		    .( ( $session->user->is_admin() && isset( $_GET['songid'] ) ) ? '&songid='.$songid : '' )
			.'&order=';
	
	if ( isset( $_GET['order'] ) ) {
		$currentorder = $_GET['order'];
	} else {
		$currentorder = null;
	}

	echo '<table border="1" width="100%" class="results" id="resulttable"><tr><th><a href="'.$sorturl.'songid'.( $currentorder == 'songid' ? 'D' : '' ).'">Song&nbsp;#</a></th>';
	if ( $userid <= 0 ) {
		echo '<th>Username</th>';
	}
	echo '<th><a href="'.$sorturl.'game'.( $currentorder == 'game' ? 'D' : '' ).'">Game</a> / <a href="'.$sorturl.'series'.( $currentorder == 'series' ? 'D' : '' ).'">Series</a></th>'
		.'<th><a href="'.$sorturl.'ggame'.( $currentorder == 'ggame' ? 'D' : '' ).'">Guessed Game</a></th>'
		.'<th colspan="2"><a href="'.$sorturl.'gamepercent'.( $currentorder == 'gamepercent' ? 'A' : '' ).'">Correct?</a></th>'
		.'<th><a href="'.$sorturl.'song'.( $currentorder == 'song' ? 'D' : '' ).'">Song Name</a></th>'
		.'<th><a href="'.$sorturl.'gsong'.( $currentorder == 'gsong' ? 'D' : '' ).'">Guessed Name</a></th>'
		.'<th colspan="2"><a href="'.$sorturl.'songpercent'.( $currentorder == 'songpercent' ? 'A' : '' ).'">Correct?</a></th></tr>';

	$image_correct = 'images/NoteBlockN.gif" title="Correct';
	$image_incorrect = 'images/Goomba.gif" title="Incorrect';
	$image_notguessed = 'images/QuestionMarkBlockN.gif" title="Not guessed';
	$lastseriesid = 0;
	foreach( $songs as $song ) {
		$gamepercent = ( $song->guessamount['gameguessed'] != 0 ? round(($song->guessamount['gamecorrect']/($song->guessamount['gameguessed']+$song->guessamount['gamenoguess']+$song->guessamount['skipped']))*100) : 0 );
		$songpercent = ( $song->guessamount['songguessed'] != 0 ? round(($song->guessamount['songcorrect']/($song->guessamount['songguessed']+$song->guessamount['songnoguess']+$song->guessamount['skipped']))*100) : 0 );
		
		if ( $session->user->is_admin() && $userid > 0 ) {
			$song->userid = $userid;
		}
		
		if ( isset($include_series) ) {
			if ( $lastseriesid !== $song->seriesid ) {
				if ( $song->seriesid ) {
					echo '<tr><td colspan="4"><b>'.$song->seriesname.'</b></td><td align="right" class="percentage">'
						.( $song->seriesamount['gameguessed'] != 0 ? round(($song->seriesamount['gamecorrect']/($song->seriesamount['gameguessed']+$song->seriesamount['gamenoguess']+$song->seriesamount['skipped']))*100) : 0 )
						.'%<span class="gamestat">'.$song->seriesamount['gameguessed'].' guesses.<br>'.$song->seriesamount['skipped'].' skips.<br>'.$song->seriesamount['gamenoguess'].' unguessed.<br><b>'.$song->seriesamount['gamecorrect'].' correct.</b></span></td><td colspan="3">&nbsp;</td><td align="right" class="percentage">'
						.( $song->seriesamount['songguessed'] != 0 ? round(($song->seriesamount['songcorrect']/($song->seriesamount['songguessed']+$song->seriesamount['songnoguess']+$song->seriesamount['skipped']))*100) : 0 )
						.'%<span class="songstat">'.$song->seriesamount['songguessed'].' guesses.<br>'.$song->seriesamount['skipped'].' skips.<br>'.$song->seriesamount['songnoguess'].' unguessed.<br><b>'.$song->seriesamount['songcorrect'].' correct.</b></span></td></tr>';
					$lastseriesid = $song->seriesid;
				} else {
					echo '<tr><td colspan="9"><b>[No Series]</b></td></tr>';
					unset( $include_series );
				}
			}
		}
		echo '<tr onMouseOver="this.className=\'highlight\'" onMouseOut="this.className=\'normal\'" id="'.$song->userid.'_'.$song->songid.'">'
			.'<td align="right">'
			.( $song->halfguess ? '<b><a href="index.php?section=guess&songid='.$song->songid.'">'.$song->songid.'</a></b>' : '<a href="index.php?section=guess&songid='.$song->songid.'">'.$song->songid.'</a>' )
			.( $session->user->is_admin() ? '&nbsp;<a href="index.php?section=songedit&songid='.$song->songid.'">e</a>&nbsp;<a href="index.php?section=results&userid=-1&songid='.$song->songid.'">s</a>' : '' ).'</td>'
			.( $userid <= 0 ? '<td>'
			.( !$song->hidden ? '<span onclick=\'hide('.$song->userid.','.$song->songid.')\'>[h]</span>&nbsp;' : '' )
			.'<a href="index.php?section=results&userid='.$song->userid.'">'.str_replace(' ', '&nbsp;', $song->username).'</a></td>' : '' )
			.'<td>'.( ($song->halfguess && !$song->game_guessed) ? '<i>N/A</i>' : $song->games ).'</td><td>'.( $song->game_guessed ? $song->game_guessed : '<i>N/A</i>' ).'</td>'
			.'<td align="center" id="'.$song->userid.'g'.$song->songid.'"><img src="'.( $song->game_correct ? $image_correct : ( $song->game_guessed ? $image_incorrect : $image_notguessed ) ).'" />'
			.( $session->user->is_admin() && $session->userid == 1 ? '<span onclick=\'changepts('.$song->userid.','.$song->songid.','.( $song->game_correct ? '-1' : '1' ).')\'>c</span>' : '' )
			.'</td><td align="right" class="percentage">'.$gamepercent.'%'
			.'<span class="gamestat">'.$song->guessamount['gameguessed'].' guesses.<br>'.$song->guessamount['skipped'].' skips.<br>'.$song->guessamount['gamenoguess'].' unguessed.<br><b>'.$song->guessamount['gamecorrect'].' correct.</b></span></td>'
			.'<td>'.( ($song->halfguess && !$song->name_guessed) ? '<i>N/A</i>' : $song->names ).'</td><td>'.( $song->name_guessed ? $song->name_guessed : '<i>N/A</i>' ).'</td>'
			.'<td align="center" id="'.$song->userid.'s'.$song->songid.'"><img src="'.( $song->name_correct ? $image_correct : ( $song->name_guessed ? $image_incorrect : $image_notguessed ) ).'" />'
			.( $session->user->is_admin() && $session->userid == 1 ? '<span onclick=\'changepts('.$song->userid.','.$song->songid.','.( $song->name_correct ? '-2' : '2' ).')\'>c</span>' : '' )
			.'</td><td align="right" class="percentage">'.$songpercent.'%'
			.'<span class="songstat">'.$song->guessamount['songguessed'].' guesses.<br>'.$song->guessamount['skipped'].' skips.<br>'.$song->guessamount['songnoguess'].' unguessed.<br><b>'.$song->guessamount['songcorrect'].' correct.</b></span></td></tr>';
	}
	
	echo '</table>';
	
	echo $pagestable;
	
	echo '<br><br><br><br><br>';
	
	} else {
		include 'main.php';
	}
?>