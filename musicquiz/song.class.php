<?php
class song {
	var $songid;
	var $url;
	var $difficulty;
	var $games;
	var $names;
	var $game_guessed;
	var $name_guessed;
	var $game_correct;
	var $name_correct;
	var $gameid;
	var $guessamount;
	var $seriesid;
	var $seriesname;
	var $seriesamount;
	var $available;
	var $halfguessed;
	var $hidden;
	
	var $artist;
	var $lossy;
	var $lossless;
	var $date;
	
	var $username;
	var $userid;

	function __construct($songid, $url = false, $difficulty = false, $games = false, $names = false) {
		$this->songid = (int)$songid;
		$this->url = $url;
		$this->difficulty = (int)$difficulty;
		$this->games = $games;
		$this->names = $names;
	}
	
	function strip_string( $str ) {
		//remove non-letter characters
		$str = str_replace( array('\\', '-', '_', ',', '&', ';', '.', ':', '+', '~', '/', '"', '!', '?', '#', '<', '>', '|', '(', ')','*') , ' ', $str);
		
		//remove "'s", so that Mario's Theme == Mario Theme
		//$str = str_replace( array('\'s', '´s', '`s') , '', $str );
		
		//remove "and", "the", "a", "an"
		$str = preg_replace('/\band\b/i', '', $str);
		$str = preg_replace('/\bthe\b/i', '', $str);
		$str = preg_replace('/\ba\b/i', '', $str);
		$str = preg_replace('/\ban\b/i', '', $str);
		
		//replace accented characters and similar with normal ones
		$str = str_replace( array('\'', '`', '´', 'é', 'è', 'ê', 'á', 'à', 'â', 'ú', 'ù', 'û', 'ó', 'ò', 'ô', 'í', 'ì', 'î', 'ä', 'ö', 'ü') ,
							array('',   '',  '',  'e', 'e', 'e', 'a', 'a', 'a', 'u', 'u', 'u', 'o', 'o', 'o', 'i', 'i', 'i', 'a', 'o', 'u'), $str);
		
		//remove multiple spaces
		$str = preg_replace('/\s\s+/', ' ', trim($str));
		return $str;
	}
	
	function check_guess( $gameguess, $songguess ) {
		$this->game_correct = false;
		$this->name_correct = false;
		$gameguess = $this->strip_string($gameguess);
		$songguess = $this->strip_string($songguess);
		
		//check game guess
		foreach ( $this->games as $game ) {
			if ( strcasecmp( $this->strip_string($game), $gameguess ) == 0 ) {
				$this->game_correct = true;
				break;
			}
		}
		
		//check songname guess
		foreach ( $this->names as $songname ) {
			if ( strcasecmp( $this->strip_string($songname), $songguess ) == 0 ) {
				$this->name_correct = true;
				break;
			}
		}
		
		//if both wrong: see if game/songname was reversed
		if ( $this->name_correct == false && $this->game_correct == false ) {
			foreach ( $this->games as $game ) {
				if ( strcasecmp( $this->strip_string($game), $songguess ) == 0 ) {
					foreach ( $this->names as $songname ) {
						if ( strcasecmp( $this->strip_string($songname), $gameguess ) == 0 ) {
							$this->game_correct = true;
							$this->name_correct = true;
							break;
						}
					}
					break;
				}
			}
		}
	}
	
}
?>