<?php
if ( !isset( $session ) ) {
	die();
}

if ( $session->logged_in && $session->user->is_vgmusicoftheday() ) {
	require_once 'db.class.php';
	require_once 'song.class.php';
	require_once 'url_container.class.php';

	error_reporting(E_ALL);
	
	$db = new db($database);

	( isset($_GET['start']) && ($_GET['start'] >= 0) ) ? $_GET['start'] = (int)$_GET['start'] : $_GET['start'] =  0;
	( isset($_GET['show'])  && ($_GET['show']  >  0) ) ? $_GET['show'] =  (int)$_GET['show']  : $_GET['show']  = 50;

	$sorting_criteria = 'day ASC';

	if ( isset($_GET['order']) ) {
		switch ( $_GET['order'] ) {
			case 'day':
				$sorting_criteria = 'day ASC';
				break;
			case 'dayD':
				$sorting_criteria = 'day DESC';
				break;
			case 'artist':
				$sorting_criteria = 'artist ASC';
				break;
			case 'artistD':
				$sorting_criteria = 'artist DESC';
				break;
			case 'game':
				$sorting_criteria = 'game ASC';
				break;
			case 'gameD':
				$sorting_criteria = 'game DESC';
				break;
			case 'song':
				$sorting_criteria = 'song ASC';
				break;
			case 'songD':
				$sorting_criteria = 'song DESC';
				break;
			default:
				break;
		}	
	}
	
	$songs = $db->get_vgmusicoftheday_songs( $_GET['start'], $_GET['show'], $sorting_criteria );

	if ( !$songs ) {
		echo 'Did not retrieve anything from the database!';
		return;
	}

	$amount_guessed_pagecalc = $db->get_vgmusicoftheday_songs_count();
	
	$pagestable = '<table width="100%"><tr><td width="20%">';
	if ( $_GET['start'] > 0 ) { //previous page
		$pagestable .= '<a href="index.php?section=vgmoftheday&start='.($_GET['start']-$_GET['show']).'&show='.$_GET['show']
			.( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' )
			.'">&lt;-- Previous Page</a>';
	}
	$pagestable .= '</td><td align="center" width="60%">';
	
	//pagelist
	$pageamount = ceil($amount_guessed_pagecalc / $_GET['show']);
	if ( $pageamount != 1 ) {
		$pagelinkend = ( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' );
		for ( $i = 1 ; $i <= $pageamount ; $i++ ) {
			$pageshow = $_GET['show']*($i-1);
			if ( $pageshow == $_GET['start'] ) {
				$pagestable .= '<u>'.$i.'</u> ';
			} else {
				$pagestable .= '<a href="index.php?section=vgmoftheday&start='.$pageshow.'&show='.$_GET['show'].$pagelinkend.'">'.$i.'</a> ';
			}
		}
	}
	
	$pagestable .= '</td><td align="right" width="20%">';
	if ( ($_GET['start']+$_GET['show']) < $amount_guessed_pagecalc ) { //next page
		$pagestable .= '<a href="index.php?section=vgmoftheday&start='.($_GET['start']+$_GET['show']).'&show='.$_GET['show']
			.( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' )
			.'">Next Page --&gt;</a>';
	}
	$pagestable .= '</td></tr></table>';
	
	echo $pagestable;

	$sorturl = 'index.php?section=vgmoftheday&start='.$_GET['start'].'&show='.$_GET['show']
			.'&order=';
	
	if ( isset( $_GET['order'] ) ) {
		$currentorder = $_GET['order'];
	} else {
		$currentorder = 'day';
	}

	echo '<table border="1" width="100%" class="results" id="resulttable"><tr><th><a href="'.$sorturl.'day'.( $currentorder == 'day' ? 'D' : '' ).'">Date</a></th>'
		.'<th><a href="'.$sorturl.'artist'.( $currentorder == 'artist' ? 'D' : '' ).'">Artist</a></th>'
		.'<th><a href="'.$sorturl.'game'.( $currentorder == 'game' ? 'D' : '' ).'">Game</a></th>'
		.'<th><a href="'.$sorturl.'song'.( $currentorder == 'song' ? 'D' : '' ).'">Song</a></th>'
		.'<th>URLs</th>'
		.'</tr>';

	foreach( $songs as $song ) {
		echo '<tr onMouseOver="this.className=\'highlight\'" onMouseOut="this.className=\'normal\'" id="vgmotd_'.$song->id.'">'
			.'<td align="right">'.$song->date.'</td>'
			.'<td>'.$song->artist.'</td>'
			.'<td>'.$song->games.'</td>'
			.'<td>'.$song->names.'</td>'
			.'<td align="middle">';
			if ( $song->url != null ) {
				foreach ( $song->url as $url ) {
					if ( $url->has_icon() ) {
						echo '<a href="'.$url->url.'"><img src="'.$url->get_icon().'" border="0" /></a>';
					} else {
						echo '<a href="'.$url->url.'">['.$url->get_typename().']</a>';
					}
				}
			}
		echo '</td>'
			.'</tr>';
	}
	echo '</table>';
	
	echo $pagestable;
	
	echo '<br><br><br><br><br>';
	
} else {
	include 'main.php';
}
?>