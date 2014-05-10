<?php
if ( !isset( $session ) ) {
	die();
}

//if ( $session->logged_in && $session->user->is_vgmusicoftheday() ) {
	require_once 'db.class.php';
	require_once 'song.class.php';
	require_once 'url_container.class.php';

	error_reporting(E_ALL);
	
	if ( $session->logged_in && $session->user->is_vgmusicoftheday() ) {
		$vgmotduser = true;
	} else {
		$vgmotduser = false;
	}
	
	$db = new db($database);

	( isset($_GET['start']) && ($_GET['start'] >= 0) ) ? $_GET['start'] = (int)$_GET['start'] : $_GET['start'] =  0;
	( isset($_GET['show'])  && ($_GET['show']  >  0) ) ? $_GET['show'] =  (int)$_GET['show']  : $_GET['show']  = 50;
	if ( isset($_GET['search']) ) {
		$search_string = trim($_GET['search']);
		if ( $search_string != '' ) {
			$searching = true;
		} else {
			$searching = false;
		}
	}
	if ( isset($_GET['past_only']) && ($_GET['past_only'] === 'false' ) ) {
		$past_only = false;
	} else {
		$past_only = true;
	}

	$sorting_criteria = 'day DESC';

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
			case 'uploader':
				$sorting_criteria = 'username ASC, day ASC';
				break;
			case 'uploaderD':
				$sorting_criteria = 'username DESC, day DESC';
				break;
			default:
				break;
		}	
	}
	
	if ( $searching ) {
		if ( $vgmotduser ) {
			$songs = $db->get_vgmusicoftheday_songs( $_GET['start'], $_GET['show'], $sorting_criteria, $search_string, $past_only );
			$amount_guessed_pagecalc = $db->get_vgmusicoftheday_songs_count($search_string, $past_only);
		} else {
			$songs = $db->get_vgmusicoftheday_songs_youtubeonly( $_GET['start'], $_GET['show'], $sorting_criteria, $search_string );
			$amount_guessed_pagecalc = $db->get_vgmusicoftheday_songs_youtubeonly_count($search_string);
		}
	} else {
		if ( $vgmotduser ) {
			$songs = $db->get_vgmusicoftheday_songs( $_GET['start'], $_GET['show'], $sorting_criteria, false, $past_only );
			$amount_guessed_pagecalc = $db->get_vgmusicoftheday_songs_count(false, $past_only);
		} else {
			$songs = $db->get_vgmusicoftheday_songs_youtubeonly( $_GET['start'], $_GET['show'], $sorting_criteria );
			$amount_guessed_pagecalc = $db->get_vgmusicoftheday_songs_youtubeonly_count();
		}
	}
	
	echo '<div align="center"><br><a href="http://www.youtube.com/user/VGMusicOfTheDay">VGMusic of the Day Youtube Channel</a></div><br>';
	
	if ( $vgmotduser ) {
		echo '<div align="center"><a href="index.php?section=vgmotd-add-edit">Add a new song</a></div><br>';
		
		if ( $past_only ) {
			echo '<div align="center">Future and undated entries are currently hidden. <a href="index.php?section=vgmoftheday&past_only=false&start='.$_GET['start'].'&show='.$_GET['show'].( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' ).( $searching ? '&search='.$search_string : '' ).'">Display them.</a></div><br>';
		} else {
			echo '<div align="center">Future and undated entries are currently shown. <a href="index.php?section=vgmoftheday&past_only=true&start='.$_GET['start'].'&show='.$_GET['show'].( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' ).( $searching ? '&search='.$search_string : '' ).'">Hide them.</a></div><br>';
		}
	}
	
	echo '<div align="center"><form action="index.php" method="get"><input type="hidden" name="section" value="vgmoftheday"/><input type="hidden" name="past_only" value="false"/><input type="text" name="search" value="'.( $searching ? $search_string : '' ).'" size="65"/><input type="submit" value="Search"/></form></div>';
	
	if ( !$songs ) {
		echo 'Nothing found!';
		return;
	}
	
	$pagestable = '<table width="100%"><tr><td width="20%">';
	if ( $_GET['start'] > 0 ) { //previous page
		$pagestable .= '<a href="index.php?section=vgmoftheday&past_only='.( $past_only ? 'true' : 'false' ).'&start='.($_GET['start']-$_GET['show']).'&show='.$_GET['show']
			.( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' )
			.( $searching ? '&search='.$search_string : '' )
			.'">&lt;-- Previous Page</a>';
	}
	$pagestable .= '</td><td align="center" width="60%">';
	
	//pagelist
	$pageamount = ceil($amount_guessed_pagecalc / $_GET['show']);
	if ( $pageamount != 1 ) {
		$pagelinkend = ( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' )
			.( $searching ? '&search='.$search_string : '' );
		for ( $i = 1 ; $i <= $pageamount ; $i++ ) {
			$pageshow = $_GET['show']*($i-1);
			if ( $pageshow == $_GET['start'] ) {
				$pagestable .= '<u>'.$i.'</u> ';
			} else {
				$pagestable .= '<a href="index.php?section=vgmoftheday&past_only='.( $past_only ? 'true' : 'false' ).'&start='.$pageshow.'&show='.$_GET['show'].$pagelinkend.'">'.$i.'</a> ';
			}
		}
	}
	
	$pagestable .= '</td><td align="right" width="20%">';
	if ( ($_GET['start']+$_GET['show']) < $amount_guessed_pagecalc ) { //next page
		$pagestable .= '<a href="index.php?section=vgmoftheday&past_only='.( $past_only ? 'true' : 'false' ).'&start='.($_GET['start']+$_GET['show']).'&show='.$_GET['show']
			.( isset( $_GET['order'] ) ? '&order='.$_GET['order'] : '' )
			.( $searching ? '&search='.$search_string : '' )
			.'">Next Page --&gt;</a>';
	}
	$pagestable .= '</td></tr></table>';
	
	echo $pagestable;

	$sorturl = 'index.php?section=vgmoftheday&past_only='.( $past_only ? 'true' : 'false' ).'&start='.$_GET['start'].'&show='.$_GET['show']
			.( $searching ? '&search='.$search_string : '' )
			.'&order=';
	
	if ( isset( $_GET['order'] ) ) {
		$currentorder = $_GET['order'];
	} else {
		$currentorder = 'dayD';
	}

	echo '<table border="1" width="100%" class="results" id="resulttable"><tr>';
	if ( $vgmotduser ) {
		echo '<th></th>';
	}
	echo '<th><a href="'.$sorturl.'day'.( $currentorder == 'day' ? 'D' : '' ).'">Day</a></th>'
		.'<th><a href="'.$sorturl.'day'.( $currentorder == 'day' ? 'D' : '' ).'">Date</a></th>'
		.'<th><a href="'.$sorturl.'artist'.( $currentorder == 'artist' ? 'D' : '' ).'">Artist</a></th>'
		.'<th><a href="'.$sorturl.'game'.( $currentorder == 'game' ? 'D' : '' ).'">Game</a></th>'
		.'<th><a href="'.$sorturl.'song'.( $currentorder == 'song' ? 'D' : '' ).'">Song</a></th>'
		.( $vgmotduser ? '<th colspan="6">URLs</th>' : '<th>Youtube</th>' )
		.'<th><a href="'.$sorturl.'uploader'.( $currentorder == 'uploader' ? 'D' : '' ).'">Uploader</a></th>'
		.'</tr>';

	$current_vgm_day = $db->get_vgmusicoftheday_current_day();
	foreach( $songs as $song ) {
		$class = '';
		if ( $song->comment ) { $class .= ' rowwithcomment'; }
		if ( $song->daynumber < $current_vgm_day ) { $class .= ' past_day'; }
		else if ( $song->daynumber == $current_vgm_day ) { $class .= ' current_day'; }
		else { $class .= ' future_day'; }
		echo '<tr class="normal'.$class.'" onMouseOver="this.className=\'highlight'.$class.'\'" onMouseOut="this.className=\'normal'.$class.'\'" id="vgmotd_'.$song->id.'">';
		if ( $vgmotduser ) {
			echo '<td align="center"><a href="index.php?section=vgmotd-add-edit&id='.$song->songid.'"><img src="pic/edit.png" title="Edit entry" border="0" /></a></td>';
		}
		echo '<td align="right">';
		if ( $song->comment ) {
			echo '<u>'.$song->daynumber.'</u>';
			echo '<span class="vgmotdcomment">'.$song->comment.'</span>';
		} else {
			echo $song->daynumber;
		}
		echo '</td>';
		echo '<td align="right">'.( $song->date != null ? $song->get_day_of_week_from_vgmotddaynum().'&nbsp;'.$song->date : '' ).'</td>'
			.'<td>'.$song->artist.'</td>'
			.'<td>'.$song->games.'</td>'
			.'<td>'.$song->names.'</td>';
		if ( $song->url != null ) {
			if ( $vgmotduser ) {
				$typelist = array( array(1), array(2), array(3), array(4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35), array(255));
				// this here is not really efficient (better would be throwing each "group" into a separate array and then looping over those, I assume), but whatever, it works well enough
				foreach ( $typelist as $t ) {
					echo '<td align="middle">';
					$f = true;
					foreach ( $song->url as $url ) {
						if ( in_array($url->url_type, $t) ) {
							if ( $f == true ) {
								$f = false;
							} else {
								echo '<br>';
							}
							
							if ( $url->has_icon() ) {
								echo '<a href="'.$url->url.'"><img src="'.$url->get_icon().'" title="'.$url->get_typename().'" border="0" /></a>';
							} else {
								echo '<a href="'.$url->url.'">['.$url->get_typename().']</a>&nbsp;';
							}
						}
					}
					if ( $f == true ) echo '&nbsp;';
					echo '</td>';
				}
			} else {
				foreach ( $song->url as $url ) {
					echo '<td align="middle">';
					if ( $url->has_icon() ) {
						echo '<a href="'.$url->url.'"><img src="'.$url->get_icon().'" title="'.$url->get_typename().'" border="0" /></a>&nbsp;';
					} else {
						echo '<a href="'.$url->url.'">['.$url->get_typename().']</a>&nbsp;';
					}
					echo '</td>';
				}
			}
		} else {
			// empty cells if there's no URL
			for ( $i = 0; $i < 5; ++$i ) {
				echo '<td>&nbsp;</td>';
			}
		}
		
		if ( $vgmotduser ) {
			echo '<td align="middle"><a href="index.php?section=vgmotd-urladd&id='.$song->songid.'">'
				.'<img src="images/plus.gif" title="Add new URL" border="0" /></a></td>';
		}
		echo '<td align="middle">'.( $song->username == null ? '&nbsp;' : $song->username ).'</td>'
			.'</tr>';
	}
	echo '</table>';
	
	echo $pagestable;
	
	echo '<br><br><br><br><br>';
	
//} else {
//	include 'main.php';
//}
?>