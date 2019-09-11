<?php
	require_once 'db.class.php';
	echo 'Recalculating...'."\n";
	
	require_once '../credentials.php';
	$db = new db( $__db_connstr_music__, $__db_username_music__, $__db_password_music__ );
	$songs = $db->get_all_songs_percentage_for_recalc();

	$series_amounts = array();
	
	foreach ( $songs as $song ) {
		//var_dump ( $song );
	
		$gamepercent = ( $song->guessamount['gameguessed'] != 0 ? round(($song->guessamount['gamecorrect']/($song->guessamount['gameguessed']+$song->guessamount['gamenoguess']+$song->guessamount['skipped']))*160) : 0 );
		$songpercent = ( $song->guessamount['songguessed'] != 0 ? round(($song->guessamount['songcorrect']/($song->guessamount['songguessed']+$song->guessamount['songnoguess']+$song->guessamount['skipped']))*92) : 0 );
		
		if (   $db->set_calculated_difficulty( $song->songid, $gamepercent+$songpercent )
			&& $db->set_guessamount_cache( $song->songid, $song->guessamount['gameguessed'], $song->guessamount['gamecorrect'], $song->guessamount['gamenoguess'],
														  $song->guessamount['songguessed'], $song->guessamount['songcorrect'], $song->guessamount['songnoguess'],
														  $song->guessamount['skipped'] 																 ) ) {
			echo 'ok [';
		} else {
			echo 'FAILED [';
		}
		
		echo $song->songid.': '.$gamepercent.'/'.$songpercent.']'."\n";
		
		$series_amounts[$song->seriesid]['gameguessed'] += $song->guessamount['gameguessed'];
		$series_amounts[$song->seriesid]['gamecorrect'] += $song->guessamount['gamecorrect'];
		$series_amounts[$song->seriesid]['gamenoguess'] += $song->guessamount['gamenoguess'];
		$series_amounts[$song->seriesid]['songguessed'] += $song->guessamount['songguessed'];
		$series_amounts[$song->seriesid]['songcorrect'] += $song->guessamount['songcorrect'];
		$series_amounts[$song->seriesid]['songnoguess'] += $song->guessamount['songnoguess'];
		$series_amounts[$song->seriesid]['skipped'] += $song->guessamount['skipped'];
	}
	
	foreach( $series_amounts as $series_id => $series_amount ) {
		if ( $db->set_seriesamount_cache( $series_id, $series_amount['gameguessed'], $series_amount['gamecorrect'], $series_amount['gamenoguess'],
														$series_amount['songguessed'], $series_amount['songcorrect'], $series_amount['songnoguess'],
														$series_amount['skipped'] 																 ) ) {
			echo 'ok [s'.$series_id.']';
		} else {
			echo 'FAILED [s'.$series_id.']';
		}

		echo "\n";
	}
	
	echo 'Done!';
?>