<?php

class sgname {
	static function games_to_list( $games ) {
		$gamelist = array();
		foreach ( $games as $game ) {
			$game_string = '';
			$array_length = count($game);
			if ( $array_length > 6 ) {
				$game[6] = '...';
				$array_length = 7;
			}
			for ( $i = 1; $i < $array_length-1; $i++ ) {
				$game_string .= $game[$i].', ';
			}
			$game_string .= $game[$array_length-1];
			$gamelist[$game[0]] = $game_string;
		}
		asort($gamelist);
		return $gamelist;
	}
}

?>