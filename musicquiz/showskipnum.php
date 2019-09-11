<?php
if ( !isset( $session ) ) {
	die();
}

	if ( $session->logged_in ) {
	require_once 'db.class.php';

	require_once '../credentials.php';
    $db = new db( $__db_connstr_music__, $__db_username_music__, $__db_password_music__ );
	
	if ( $session->user->is_admin() ) {
		if ( isset( $_GET['userid'] ) ) {
			$userid = (int)$_GET['userid'];
		} else {
			$userid = $session->userid;
		}
	} else {
		$userid = $session->userid;
	}

	
	$skipped_songs = $db->get_skipped_song_ids( $userid );
	
	if ( !empty($skipped_songs) ) {
		echo 'The following songs have been skipped:';
		foreach ( $skipped_songs as $song ) {
			echo ' <a href="index.php?section=guess&songid='.$song.'">'.$song.'</a>';
			if ( $session->user->is_admin() ) {
				echo '<a href="index.php?section=songedit&songid='.$song.'">e</a>';
			}
		}
	} else {
		echo 'You have not skipped any songs.';
	}

	} else {
		include 'main.php';
	}
?>