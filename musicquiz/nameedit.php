<?php
	if ( !isset( $session ) ) {
		die();
	}
	
if ( $session->logged_in && $session->user->is_admin() ) {
	require_once 'db.class.php';
	require_once '../credentials.php';
	$db = new db( $__db_connstr_music__, $__db_username_music__, $__db_password_music__ );
	
	if ( isset( $_GET['id'] ) ) {
		$id = (int)$_GET['id'];
		if ( $id <= 0 ) {
			die();
		}
	} else {
		die();
	}

	// edit/add
	if ( isset ( $_POST['name'] ) && ( $_POST['name'] = trim($_POST['name']) ) != '' && isset ( $_POST['priority'] ) ) {
		if ( $_POST['priority'] == -1 ) {
			//add
			if ( $_GET['type'] == 'game' ) {
				$db->add_game( $id, $_POST['name'] );
			} else if ( $_GET['type'] == 'songname' ) {
				$db->add_songname( $id, $_POST['name'] );
			}
		} else {
			//edit
			if ( $_GET['type'] == 'game' ) {
				$db->edit_game( $id, $_POST['name'], $_POST['priority'] );
			} else if ( $_GET['type'] == 'songname' ) {
				$db->edit_songname( $id, $_POST['name'], $_POST['priority'] );
			}
		}
	}
	if ( isset ( $_POST['series_existing'] ) && isset ( $_POST['seriesname'] ) && isset ( $_POST['seriesid'] ) ) {
		if ( $_POST['series_existing'] == 0 ) {
			//if "create new" selected -> insert new into DB && set game's 
			$newseriesid = $db->create_new_series( $_POST['seriesname'] );
			$db->set_series_of_game( $id, $newseriesid );
		} else if ( $_POST['series_existing'] == $_POST['seriesid'] ) {
			//if equal -> series hasn't changed; update name in DB
			$db->edit_series_name( $_POST['series_existing'], $_POST['seriesname'] );
		} else {
			//otherwise set seriesID to new
			$db->set_series_of_game( $id, $_POST['series_existing'] );
		}
	}
	
	if ( isset( $_GET['type'] ) ) {
		if ( $_GET['type'] == 'game' ) {
			$names = $db->get_gamenames_with_priority( $id );

			// series
			$all_series = $db->get_all_series();
			$series = $db->get_series_from_gameid( $id );
			
			echo 'Series: <form action="index.php?section=nameedit&type=game&id='.$id.'" method="post">';
			echo '<select name="series_existing"><option value="0"> --- create new --- </option>';
			foreach ( $all_series as $aseriesid => $aseriesname ) {
				echo '<option value="'.$aseriesid.'"'.( $series[0] == $aseriesid ? ' selected' : '' ).'>'.$aseriesname.'</option>';
			}
			echo '</select> ';
			
			if ( $series ) {
				echo '<input size="50" type="text" name="seriesname" value="'.$series[1].'" />';
				echo '<input type="hidden" name="seriesid" value="'.$series[0].'" />';
				echo '<input type="submit" value="Edit" />';
			} else {
				echo '<input size="50" type="text" name="seriesname" />';
				echo '<input type="hidden" name="seriesid" value="0" />';
				echo '<input type="submit" value="Add" />';
			}
			echo '</form>';
			
		} else if ( $_GET['type'] == 'songname' ) {
			$names = $db->get_songnames_with_priority( $id );
		} else {
			die();
		}
	} else {
		die();
	}
	
	if ( $names == null ) {
		die();
	}
	echo '<table>';
		foreach ( $names as $prio => $name ) {
			echo '<form action="index.php?section=nameedit&type='.$_GET['type'].'&id='.$id.'" method="post"><tr>';
				echo '<td><input size="100" type="text" name="name" value="'.$name.'" /></td>';
				echo '<td><input type="hidden" name="priority" value="'.$prio.'" /></td>';
				echo '<td><input type="submit" value="Edit" /></td>';
			echo '</tr></form>';
		}
		echo '<form action="index.php?section=nameedit&type='.$_GET['type'].'&id='.$id.'" method="post"><tr>';
			echo '<td><input size="100" type="text" name="name" /></td>';
			echo '<td><input type="hidden" name="priority" value="-1" /></td>';
			echo '<td><input type="submit" value="Add" /></td>';
		echo '</tr></form>';
	echo '</table>';

	echo '<a href="http://192.168.0.19/musicquiz/index.php?section=nameedit&type=game&id='.($id+1).'">Next</a>';
	
} else {
	include 'main.php';
}
?>