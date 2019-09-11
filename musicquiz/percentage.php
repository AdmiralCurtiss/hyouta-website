<?php
if ( !isset( $session ) ) {
	die();
}

	if ( $session->logged_in ) {
	require_once 'db.class.php';
	require_once 'song.class.php';

	require_once '../credentials.php';
    $db = new db( $__db_connstr_music__, $__db_username_music__, $__db_password_music__ );

	( isset($_GET['start']) 	&& ($_GET['start'] >= 0)	) ? $_GET['start'] = (int)$_GET['start'] : $_GET['start'] =   0;
	( isset($_GET['show'])  	&& ($_GET['show']  >  0)	) ? $_GET['show']  = (int)$_GET['show']  : $_GET['show']  = 150;
	( isset($_GET['tablecols']) && ($_GET['tablecols'] > 0) ) ? $tablecols     = (int)$_GET['tablecols'] : $tablecols =   6;
	
	$amount_guessed = $db->get_amount_available_songs();
	
	if ( isset($_GET['order']) ) {
		switch ( $_GET['order'] ) {
			case 'songid':
				$sortstring = 'songid ASC';
				break;
			case 'songidD':
				$sortstring = 'songid DESC';
				break;
			case 'gamepercent':
				$sortstring = 'gamecorrect/(gameguessed+gamenoguess+skipped) DESC, songid ASC';
				break;
			case 'gamepercentA':
				$sortstring = 'gamecorrect/(gameguessed+gamenoguess+skipped) ASC, songid ASC';
				break;
			case 'songpercent':
				$sortstring = 'songcorrect/(songguessed+songnoguess+skipped) DESC, songid ASC';
				break;
			case 'songpercentA':
				$sortstring = 'songcorrect/(songguessed+songnoguess+skipped) ASC, songid ASC';
				break;
			default:
				$sortstring = 'songid ASC';
				break;
		}
	} else {
		$sortstring = 'songid ASC';
	}
	
	$songs = $db->get_all_songs_percentage_only_from_cache($_GET['start'], $_GET['show'], $sortstring);
	
	if ( !$songs ) {
		return;
	}

	$pagestable = '<table width="100%"><tr><td width="20%">';
	if ( $_GET['start'] > 0 ) { //previous page
		$pagestable .= '<a href="index.php?section=songlist&start='.($_GET['start']-$_GET['show']).'&show='.$_GET['show']
			.'&tablecols='.$tablecols.( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' )
			.'">&lt;-- Previous Page</a>';
	}
	$pagestable .= '</td><td align="center" width="60%">';
	
	//pagelist
	$pageamount = ceil($amount_guessed / $_GET['show']);
	if ( $pageamount != 1 ) {
		$pagelinkend = ( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' );
		for ( $i = 1 ; $i <= $pageamount ; $i++ ) {
			$pageshow = $_GET['show']*($i-1);
			if ( $pageshow == $_GET['start'] ) {
				$pagestable .= '<u>'.$i.'</u> ';
			} else {
				$pagestable .= '<a href="index.php?section=songlist&start='.$pageshow.'&show='.$_GET['show'].'&tablecols='.$tablecols.$pagelinkend.'">'.$i.'</a> ';
			}
		}
	}
	
	$pagestable .= '</td><td align="right" width="20%">';
	if ( ($_GET['start']+$_GET['show']) < $amount_guessed ) { //next page
		$pagestable .= '<a href="index.php?section=songlist&start='.($_GET['start']+$_GET['show']).'&show='.$_GET['show']
			.'&tablecols='.$tablecols.( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' )
			.'">Next Page --&gt;</a>';
	}
	$pagestable .= '</td></tr></table>';
	
	echo $pagestable;

	$sorturl = 'index.php?section=songlist&start='.$_GET['start'].'&show='.$_GET['show'].'&order=';
	
	if ( isset( $_GET['order'] ) ) {
		$currentorder = $_GET['order'];
	} else {
		$currentorder = null;
	}
	
	echo '<table border="1" width="100%" class="results"><tr>';
	for ( $i = 0; $i < $tablecols; $i++ ) {
		echo '<th class="'.( $i%2 == 0 ? 'songlistA' : 'songlistB' ).'"><a href="'.$sorturl.'songid'.( $currentorder == 'songid' ? 'D' : '' ).'">Song&nbsp;#</a></th>'
			.'<th class="'.( $i%2 == 0 ? 'songlistA' : 'songlistB' ).'"><a href="'.$sorturl.'gamepercent'.( $currentorder == 'gamepercent' ? 'A' : '' ).'">Game&nbsp;%</a></th>'
			.'<th class="'.( $i%2 == 0 ? 'songlistA' : 'songlistB' ).'"><a href="'.$sorturl.'songpercent'.( $currentorder == 'songpercent' ? 'A' : '' ).'">Song&nbsp;%</a></th>';
	}
	echo '</tr>';

	$songsonrow = $tablecols;
	foreach( $songs as $song ) {
		$gamepercent = ( $song->guessamount['gameguessed'] != 0 ? round(($song->guessamount['gamecorrect']/($song->guessamount['gameguessed']+$song->guessamount['gamenoguess']+$song->guessamount['skipped']))*100) : 0 );
		$songpercent = ( $song->guessamount['songguessed'] != 0 ? round(($song->guessamount['songcorrect']/($song->guessamount['songguessed']+$song->guessamount['songnoguess']+$song->guessamount['skipped']))*100) : 0 );
		
		$songsonrow++;
		if ( $songsonrow > $tablecols ) {
			echo '<tr onMouseOver="this.className=\'highlight\'" onMouseOut="this.className=\'normal\'">';
			$songsonrow = 1;
		}
		echo '<td align="right" class="'.( $songsonrow%2 == 1 ? 'songlistA' : 'songlistB' ).'"><a href="index.php?section=guess&songid='.$song->songid.'">'.$song->songid.'</a>'
			.'<td align="right" class="percentage'.( $songsonrow%2 == 1 ? '' : 'songlistB' ).'">'.$gamepercent.'%'
			.'<span class="gamestat">'.$song->guessamount['gameguessed'].' guesses.<br>'.$song->guessamount['skipped'].' skips.<br>'.$song->guessamount['gamenoguess'].' unguessed.<br><b>'.$song->guessamount['gamecorrect'].' correct.</b></span></td>'
			.'<td align="right" class="percentage'.( $songsonrow%2 == 1 ? '' : 'songlistB' ).'">'.$songpercent.'%'
			.'<span class="'.( $songsonrow != $tablecols ? 'gamestat' : 'songstat' ).'">'.$song->guessamount['songguessed'].' guesses.<br>'.$song->guessamount['skipped'].' skips.<br>'.$song->guessamount['songnoguess'].' unguessed.<br><b>'.$song->guessamount['songcorrect'].' correct.</b></span></td>';
		if ( $songsonrow > $tablecols ) {
			echo '</tr>';
		}
	}
	echo '</table>';
	
	echo $pagestable;
	
	echo '<br><br><br><br><br>';
	
	} else {
		include 'main.php';
	}
?>