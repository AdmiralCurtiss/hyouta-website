<?php
require_once 'user.class.php';
require_once 'song.class.php';
require_once 'url_container.class.php';

class db {

	var $database;

	function __construct($database) {
		$this->database = $database;
	}
	
	function gethash($password) {
		return md5('nGWNrTdpmBBpNCce4#hWNvyYNum4f'.$password.'# umN3q3rbcP# HFpmzR6ExQMn7sZ');
	}
	function register($username, $password) {
		$pwdhash = $this->gethash($password);
		$query = 'INSERT INTO music_users (username, password) VALUES ( \''.mysql_real_escape_string($username).'\', \''.$pwdhash.'\')';

		return mysql_query($query, $this->database);
	}
	function editpassword( $userid, $password ) {
		$userid = (int)$userid;
		$pwdhash = $this->gethash($password);
		$query = 'UPDATE music_users SET password = \''.$pwdhash.'\' WHERE userid = '.$userid;

		return mysql_query($query, $this->database);
	}
	function get_userid($username) {
		$query = 'SELECT userid FROM music_users WHERE username = \''.mysql_real_escape_string($username).'\'';
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			return $data['userid'];
		}
		
		return false;
	}
	function get_user($userid) {
		$userid = (int)$userid;
		$query = 'SELECT userid, username, role, halfguess, guessorder, autoplay FROM music_users WHERE userid = '.$userid;
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			$user = new user($data['userid'], $data['username'], $data['role'], $data['halfguess'], $data['guessorder'], $data['autoplay']);
			return $user;
		}
		
		return false;
	}
	function get_users_with_rank($role) {
		$role = (int)$role;
		$query = 'SELECT userid, username, role, halfguess, guessorder, autoplay FROM music_users WHERE role >= '.$role;
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$users = array();
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$users[(int)$data['userid']] = new user((int)$data['userid'], $data['username'], $data['role'], $data['halfguess'], $data['guessorder'], $data['autoplay']);
			}
			return $users;
		}
		
		return false;
	}
	function get_all_users() {
		$query = 'SELECT userid, username, role, halfguess, guessorder, autoplay FROM music_users ORDER BY username ASC';
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$users = array();
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$users[(int)$data['userid']] = new user((int)$data['userid'], $data['username'], $data['role'], $data['halfguess'], $data['guessorder'], $data['autoplay']);
			}
			return $users;
		}
		
		return false;
	}
	function get_user_and_confirm_password($userid, $password) {
		$userid = (int)$userid;
		$pwdhash = $this->gethash($password);
		$query = 'SELECT userid, username, role, halfguess, guessorder FROM music_users WHERE userid = '.$userid.' AND password = \''.$pwdhash.'\'';
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			if ( $data == false ) return false;
			$user = new user($data['userid'], $data['username'], $data['role'], $data['halfguess'], $data['guessorder']);
			return $user;
		}
		
		return false;
	}
	function update_session($userid, $session, $ip = '0.0.0.0') {
		$userid = (int)$userid;
		$ip = mysql_real_escape_string($ip);
		$query = 'UPDATE music_users SET session = \''.mysql_real_escape_string($session).'\', ip = INET_ATON(\''.$ip.'\') WHERE userid = '.$userid;

		return mysql_query($query, $this->database);
	}
	function confirm_user($userid, $sessiontoken) {
		$userid = (int)$userid;
		$query = 'SELECT session FROM music_users WHERE userid = '.$userid;
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			if ( $data['session'] == $sessiontoken ) {
				return true;
			}
		}
		
		return false;
	}
	
	function get_gamenames( $songid ) {
		$songid = (int)$songid;
		$query = 'SELECT gamename FROM music_songs JOIN music_games ON music_songs.gameid = music_games.gameid WHERE songid = '.$songid.' ORDER BY priority ASC';
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$games = array();
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$games[] = $data['gamename'];
			}
			return $games;
		}
	
		return false;
	}

	function get_gamenames_with_priority( $gameid ) {
		$gameid = (int)$gameid;
		$query = 'SELECT gamename, priority FROM music_games WHERE gameid = '.$gameid.' ORDER BY priority ASC';
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$games = array();
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$games[(int)$data['priority']] = $data['gamename'];
			}
			return $games;
		}
	
		return false;
	}
	function get_songnames_with_priority( $songid ) {
		$songid = (int)$songid;
		$query = 'SELECT songname, priority FROM music_songnames WHERE songid = '.$songid.' ORDER BY priority ASC';
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$songnames = array();
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$songnames[(int)$data['priority']] = $data['songname'];
			}
			return $songnames;
		}
		
		return false;	
	}
	function edit_game( $gameid, $gamename, $priority ) {
		$gameid = (int)$gameid;
		$priority = (int)$priority;
		
		$query = 'UPDATE music_games SET gamename = \''.mysql_real_escape_string(stripslashes($gamename))
				.'\' WHERE gameid = '.$gameid.' AND priority = '.$priority;

		return mysql_query($query, $this->database);
	}
	function edit_songname( $songid, $songname, $priority ) {
		$songid = (int)$songid;
		$priority = (int)$priority;
		
		$query = 'UPDATE music_songnames SET songname = \''.mysql_real_escape_string(stripslashes($songname))
				.'\' WHERE songid = '.$songid.' AND priority = '.$priority;

		return mysql_query($query, $this->database);
	}
	function check_if_song_is_public( $songid ) {
		$songid = (int)$songid;
		
		$query = 'SELECT COUNT(1) AS songexist FROM music_songs WHERE available = 1 AND songid = '.$songid;
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			if ( $data['songexist'] == 1 ) return true;
			return false;
		} else {
			return false;
		}
	}

	function get_gamelist() {
		$query = 'SELECT gamename, gameid FROM music_games ORDER BY gameid ASC, priority ASC';
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$games = array();
			$previd = 0;
			$i = -1;
			while ( $data = mysql_fetch_assoc($resultset) ) {
				if ( $previd != $data['gameid'] ) {
					$previd = $data['gameid'];
					$i++;
					$games[$i] = array();
					$games[$i][] = $data['gameid'];
				}
				$games[$i][] = $data['gamename'];
			}
			return $games;
		}
	
		return false;
	}
	function get_songnames( $songid ) {
		$songid = (int)$songid;
		$query = 'SELECT songname FROM music_songnames WHERE songid = '.$songid.' ORDER BY priority ASC';
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$songnames = array();
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$songnames[] = $data['songname'];
			}
			return $songnames;
		}
		
		return false;	
	}
	function get_song( $songid, $get_names = true ) {
		$songid = (int)$songid;
		if ( $get_names ) {
			$query = 'SELECT songid, url, gameid, difficulty, available FROM music_songs WHERE songid = '.$songid;
		} else {
			$query = 'SELECT songid, url, difficulty FROM music_songs WHERE available = 1 AND songid = '.$songid;
		}
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			if ( $data['songid'] == null ) return false;
			if ( $get_names ) {
				$song = new song( $data['songid'], $data['url'], $data['difficulty'],
								  $this->get_gamenames($songid), $this->get_songnames($songid) );
				$song->gameid = $data['gameid'];
				$song->available = ( $data['available'] == 1 );
			} else {
				$song = new song( $data['songid'], $data['url'], $data['difficulty'] );
			}
			return $song;
		}
		
		return false;
	}
	function get_all_songs( $unavailable_only = false ) {
		$query = 'SELECT music_songs.songid, url, difficulty, gamename, songname, available FROM music_songs '
				.'LEFT JOIN music_games ON music_songs.gameid = music_games.gameid '
				.'LEFT JOIN music_songnames ON music_songs.songid = music_songnames.songid '
				.'WHERE ( music_games.priority = 1 or ISNULL(music_games.priority) ) AND ( music_songnames.priority = 1 or ISNULL(music_songnames.priority) ) '
				.( $unavailable_only ? 'AND available = 0 ' : '' )
				.'ORDER BY music_songs.songid ASC';
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$songs = array();
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$i = (int)$data['songid'];
				$songs[$i] = new song( $data['songid'], $data['url'], $data['difficulty'], $data['gamename'], $data['songname'] );
				$songs[$i]->available = (bool)$data['available'];
			}
			return $songs;
		}
		
		return false;
	}
	function get_all_songs_percentage_only_from_cache( $start_with = 0, $amount = 200, $order = 'songid ASC' ) {
		$query = 'SELECT songid, gameguessed, gamecorrect, gamenoguess, songguessed, songcorrect, songnoguess, skipped FROM music_songs WHERE available = 1 ORDER BY '.$order.' LIMIT '.$start_with.', '.$amount;
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$songs = array();
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$i = (int)$data['songid'];
				$songs[$i] = new song( $data['songid'] );
				
				$guessamount['gameguessed'] = (int)$data['gameguessed'];
				$guessamount['gamecorrect'] = (int)$data['gamecorrect'];
				$guessamount['gamenoguess'] = (int)$data['gamenoguess'];
				$guessamount['songguessed'] = (int)$data['songguessed'];
				$guessamount['songcorrect'] = (int)$data['songcorrect'];
				$guessamount['songnoguess'] = (int)$data['songnoguess'];
				$guessamount['skipped'] = (int)$data['skipped'];
				$songs[$i]->guessamount = $guessamount;
			}
			return $songs;
		}
		
		return false;
	}
	function get_amount_available_songs() {
		$query = 'SELECT COUNT(1) AS songamount FROM music_songs WHERE available = 1';
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			if ( $data = mysql_fetch_assoc($resultset) ) {
				return (int)$data['songamount'];
			}
		}
		
		return false;
	}
	function get_current_highest_songid() {
		$query = 'SELECT MAX(songid) AS maxid FROM music_songs';
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			if ( $data = mysql_fetch_assoc($resultset) ) {
				return (int)$data['maxid'];
			}
		}
		
		return false;
	}
	function get_all_songs_percentage_for_recalc() {
		$query = 'SELECT music_songs.songid, music_series_games.seriesid FROM music_songs '
				.'JOIN music_series_games ON music_songs.gameid = music_series_games.gameid';
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$songs = array();
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$i = (int)$data['songid'];
				$songs[$i] = new song($i);
				$songs[$i]->guessamount = $this->get_amount_people_guessed( $i );
				$songs[$i]->seriesid = (int)$data['seriesid'];
			}
			return $songs;
		}
		
		return false;
	}
	function get_next_song( $userid ) {
		$userid = (int)$userid;
		$query = 'SELECT songid, url, difficulty FROM music_songs '
				.'WHERE songid NOT IN ( SELECT songid FROM music_guesses WHERE userid = '.$userid.' ) '
				.'AND songid NOT IN ( SELECT songid FROM music_skipped WHERE userid = '.$userid.' ) AND available = 1 ORDER BY difficulty ASC LIMIT 1';

		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			if ( $data['songid'] == null ) return false;
			$song = new song($data['songid'], $data['url'], $data['difficulty']);
			return $song;
		}
		
		return false;
	}
	function get_random_unguessed_song( $userid ) {
		$userid = (int)$userid;
		$query = 'SELECT songid, url, difficulty FROM music_songs '
				.'WHERE songid NOT IN ( SELECT songid FROM music_guesses WHERE userid = '.$userid.' ) '
				.'AND songid NOT IN ( SELECT songid FROM music_skipped WHERE userid = '.$userid.' ) AND available = 1 ORDER BY rand() LIMIT 1';

		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			if ( $data['songid'] == null ) return false;
			$song = new song($data['songid'], $data['url'], $data['difficulty']);
			return $song;
		}
		
		return false;
	}
	function get_calcdiff_unguessed_song( $userid ) {
		$userid = (int)$userid;
		$query = 'SELECT songid, url, difficulty FROM music_songs '
				.'WHERE songid NOT IN ( SELECT songid FROM music_guesses WHERE userid = '.$userid.' ) '
				.'AND songid NOT IN ( SELECT songid FROM music_skipped WHERE userid = '.$userid.' ) AND available = 1 ORDER BY calcdiff DESC LIMIT 1';

		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			if ( $data['songid'] == null ) return false;
			$song = new song($data['songid'], $data['url'], $data['difficulty']);
			return $song;
		}
		
		return false;
	}
	function get_series_name( $seriesid ) {
		$seriesid = (int)$seriesid;
		$query = 'SELECT seriesname FROM music_series WHERE seriesid = '.$seriesid;

		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			if ( $data['seriesname'] == null ) return false;
			return $data['seriesname'];
		}
		
		return false;
	}
	function get_all_series() {
		$query = 'SELECT seriesid, seriesname FROM music_series ORDER BY seriesname ASC';

		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$series = array();
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$series[(int)$data['seriesid']] = $data['seriesname'];
			}
			return $series;
		}
		
		return false;
	}
	function get_series_from_gameid( $gameid ) { // $return[0] = id, $return[1] = name
		$gameid  = (int)$gameid ;
		$query = 'SELECT music_series.seriesid, music_series.seriesname FROM music_songs'
				.' JOIN music_series_games ON music_series_games.gameid = music_songs.gameid'
				.' JOIN music_series ON music_series_games.seriesid = music_series.seriesid'
				.' WHERE music_series_games.gameid  = '.$gameid;

		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			if ( $data['seriesid'] == null ) return false;
			return array( $data['seriesid'] , $data['seriesname'] );
		}
		
		return false;
	}

	function delete_skip( $userid, $songid ) {
		$userid = (int)$userid;
		$songid = (int)$songid;

		//delete skip if exists
		$query = 'DELETE FROM music_skipped WHERE userid = '.$userid.' AND songid = '.$songid;
		
		if ( mysql_query($query, $this->database) ) {
			if ( mysql_affected_rows() != 0 ) {
				$query = 'UPDATE music_songs SET skipped = skipped-1 WHERE songid = '.$songid;
				mysql_query($query, $this->database);
			}
			return true;
		}
		
		return false;
	}
	function guess_song( $userid, $songid, $gameguess, $gamecorrect, $songguess, $songcorrect, $halfguess ) {
		$userid = (int)$userid;
		$songid = (int)$songid;
		
		if ( $songid <= 0 ) return false;
		if ( !$this->check_if_song_is_public( $songid ) ) return false;
		
		$this->delete_skip( $userid, $songid );
		
		//set guessamounts
		$query = 'UPDATE music_songs SET ';
		if ( $gamecorrect ) {
			$query .= 'gameguessed = gameguessed+1, gamecorrect = gamecorrect+1, ';
		} else if ( $gameguess ) {
			$query .= 'gameguessed = gameguessed+1, ';
		} else {
			$query .= 'gamenoguess = gamenoguess+1, ';
		}
		if ( $songcorrect ) {
			$query .= 'songguessed = songguessed+1, songcorrect = songcorrect+1';
		} else if ( $songguess ) {
			$query .= 'songguessed = songguessed+1';
		} else {
			$query .= 'songnoguess = songnoguess+1';
		}
		$query .= ' WHERE songid = '.$songid;
		mysql_query($query, $this->database);
		
		$points = 0;
		if ( $gamecorrect ) $points++;
		if ( $songcorrect ) $points += 2;
		if ( $halfguess && ( !$gameguess || !$songguess ) ) $points += 4;
				
		// auto-hide if all guessed is correct
		if ( $gamecorrect && $songcorrect ) { $hidefromall = 1; }
		else if ( $gamecorrect && !$songguess ) { $hidefromall = 1; }
		else if ( $songcorrect && !$gameguess ) { $hidefromall = 1; }
		else { $hidefromall = 0; }
				
		$query = 'INSERT INTO music_guesses ( userid, songid, gameguess, songguess, points, hidefromall ) '
				.'VALUES ('.$userid.', '.$songid.', '.( $gameguess ? '\''.mysql_real_escape_string(stripslashes($gameguess)).'\'' : 'NULL' ).', '.( $songguess ? '\''.mysql_real_escape_string(stripslashes($songguess)).'\'' : 'NULL' ).', '.$points.', '.$hidefromall.')';
		
		return mysql_query($query, $this->database);
	}
	function skip_song( $userid, $songid ) {
		$userid = (int)$userid;
		$songid = (int)$songid;
		
		if ( $songid <= 0 ) return false;
		if ( !$this->check_if_song_is_public( $songid ) ) return false;
		
		//check if song already guessed
		$query = 'SELECT COUNT(1) AS alreadyguessed FROM music_guesses WHERE userid = '.$userid.' AND songid = '.$songid;
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			if ( $data['alreadyguessed'] == 1 ) return false;
		} else {
			return false;
		}

		$query = 'INSERT INTO music_skipped ( userid, songid ) VALUES ('.$userid.', '.$songid.')';
		if ( mysql_query($query, $this->database) ) {
			$query = 'UPDATE music_songs SET skipped = skipped+1 WHERE songid = '.$songid;
			mysql_query($query, $this->database);
			return true;
		}
		return false;
	}
	function give_up( $userid, $songid ) {
		$userid = (int)$userid;
		$songid = (int)$songid;
		
		if ( $songid <= 0 ) return false;
		if ( !$this->check_if_song_is_public( $songid ) ) return false;

		//check if song already guessed
		$query = 'SELECT COUNT(1) AS alreadyguessed FROM music_guesses WHERE userid = '.$userid.' AND songid = '.$songid;
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			if ( $data['alreadyguessed'] == 1 ) return false;
		} else {
			return false;
		}
		
		$this->delete_skip( $userid, $songid );

		$query = 'INSERT INTO music_guesses ( userid, songid, gameguess, songguess, points, hidefromall ) '
				.'VALUES ('.$userid.', '.$songid.', NULL, NULL, 0, 1)';
		if ( mysql_query($query, $this->database) ) {
			$query = 'UPDATE music_songs SET gamenoguess = gamenoguess+1, songnoguess = songnoguess+1 WHERE songid = '.$songid;
			mysql_query($query, $this->database);
			return true;
		}
		return false;
	}
	function get_amount_skipped_songs( $userid ) {
		$userid = (int)$userid;
		$query = 'SELECT COUNT(songid) AS songcount FROM music_skipped WHERE userid = '.$userid;
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			return $data['songcount'];
		}
		
		return false;
	}
	function get_skipped_song_ids( $userid ) {
		$userid = (int)$userid;
		$query = 'SELECT songid FROM music_skipped WHERE userid = '.$userid.' ORDER BY songid ASC';
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$songids = array();
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$songids[] = $data['songid'];
			}
			return $songids;
		}
		
		return false;
	}
	function clear_skipped_songs( $userid ) {
		$userid = (int)$userid;
		
		$skipped_songs = $this->get_skipped_song_ids( $userid );
		foreach ( $skipped_songs as $song ) {
			$query = 'UPDATE music_songs SET skipped=skipped-1 WHERE songid = '.$song;
			mysql_query($query, $this->database);
		}
		
		$query = 'DELETE FROM music_skipped WHERE userid = '.$userid;
		return mysql_query($query, $this->database);
	}
	function song_already_guessed( $userid, $songid ) {
		//returns true if song already guessed, false if not
		
		$userid = (int)$userid;
		$songid = (int)$songid;
		$query = 'SELECT COUNT(1) AS already_guessed FROM music_guesses WHERE userid = '.$userid.' AND songid = '.$songid;
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			return ( $data['already_guessed'] == 1 );
		}
		
		return false;
	}
	function song_halfguessed( $userid, $songid ) {
		//returns 0 = nope, 1 = game guessed / song not, 2 = game not / song guessed
		
		$userid = (int)$userid;
		$songid = (int)$songid;
		$query = 'SELECT gameguess, songguess, points FROM music_guesses WHERE userid = '.$userid.' AND songid = '.$songid;
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			if ( $data['points'] < 4 ) {
				return 0;
			}
			if ( $data['gameguess'] ) {
				return 1;
			}
			if ( $data['songguess'] ) {
				return 2;
			}
		}
		
		return false;
	}
	function update_guess( $userid, $songid, $gameguess, $add_gamecorrect, $songguess, $add_songcorrect, $remove_halfguess ) {
		$songid = (int)$songid;
		$userid = (int)$userid;
		
		$points = 0;
		if ( $add_gamecorrect )  $points++;
		if ( $add_songcorrect )  $points += 2;
		if ( $remove_halfguess ) $points -= 4;
		
		$skip_gameguess = ( $gameguess === false );
		$skip_songguess = ( $songguess === false );
		
		if ( !$skip_gameguess && !$add_gamecorrect ) {
			$unhide_from_all = true;
		} else if ( !$skip_songguess && !$add_songcorrect ) {
			$unhide_from_all = true;
		} else {
			$unhide_from_all = false;
		}
	
		$query = 'UPDATE music_guesses SET '
				.( $skip_gameguess ? '' : 'gameguess = \''.mysql_real_escape_string(stripslashes($gameguess)).'\', ' )
				.( $skip_songguess ? '' : 'songguess = \''.mysql_real_escape_string(stripslashes($songguess)).'\', ' )
				.( $unhide_from_all ? 'hidefromall = 0, ' : '' )
				.'points = ( points + '.$points.' ) WHERE songid = '.$songid.' AND userid = '.$userid;
		
		return mysql_query($query, $this->database);
	}
	
	function add_song( $url, $difficulty, $gameid, $available ) {
		$difficulty = (int)$difficulty;
		$gameid = (int)$gameid;
		
		$query = 'INSERT INTO music_songs ( url, gameid, difficulty, available ) VALUES '
				.'( \''.mysql_real_escape_string($url).'\', '.$gameid.', '.$difficulty.', '.( $available ? 1 : 0 ).' )';
		if ( mysql_query($query, $this->database) ) {
			return mysql_insert_id();
		}
		
		return false;
	}
	function get_free_gameid() {
		$query = 'SELECT MAX(gameid)+1 AS nextgameid FROM music_games';
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			return $data['nextgameid'];
		}
		
		return false;
	}
	function get_free_priority( $gameid, $from ) {
		$gameid = (int)$gameid;
		if ( $from == 'g' ) {
			$query = 'SELECT MAX(priority)+1 AS nextprio FROM music_games WHERE gameid = '.$gameid;
		} else if ( $from == 's' ) {
			$query = 'SELECT MAX(priority)+1 AS nextprio FROM music_songnames WHERE songid = '.$gameid;
		} else {
			return false;
		}
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			return $data['nextprio'];
		}
		
		return false;
	}
	function add_game( $gameid, $gamename, $priority = false ) {
		$gameid = (int)$gameid;
		if ( $priority ) {
			$priority = (int)$priority;
		} else {
			$priority = $this->get_free_priority( $gameid, 'g' );
			if ( $priority == false ) $priority = 1;
		}
		
		$query = 'INSERT INTO music_games ( gameid, gamename, priority ) VALUES '
				.'( '.$gameid.', \''.mysql_real_escape_string(stripslashes($gamename)).'\', '.$priority.' )';
		
		return mysql_query($query, $this->database);
	}
	function add_songname( $songid, $songname, $priority = false ) {
		$songid = (int)$songid;
		if ( $priority ) {
			$priority = (int)$priority;
		} else {
			$priority = $this->get_free_priority( $songid, 's' );
			if ( $priority == false ) $priority = 1;
		}
		
		$query = 'INSERT INTO music_songnames ( songid, songname, priority ) VALUES '
				.'( '.$songid.', \''.mysql_real_escape_string(stripslashes($songname)).'\', '.$priority.' )';
		
		return mysql_query($query, $this->database);
	}
	function edit_song( $songid, $url, $difficulty, $gameid, $available ) {
		$songid = (int)$songid;
		$difficulty = (int)$difficulty;
		$gameid = (int)$gameid;
		$query = 'UPDATE music_songs SET url = \''.mysql_real_escape_string($url).'\', '
				.'difficulty = '.$difficulty.', gameid = '.$gameid.', available = '.( $available ? 1 : 0 ).' WHERE songid = '.$songid;

		return mysql_query($query, $this->database);
	}
	function create_new_series( $seriesname ) {
		$query = 'INSERT INTO music_series ( seriesname ) VALUES ( \''.mysql_real_escape_string(stripslashes($seriesname)).'\' )';
		if ( mysql_query($query, $this->database) ) {
			return mysql_insert_id();
		}
		
		return false;
	}
	function edit_series_name( $seriesid, $seriesname ) {
		$seriesid = (int)$seriesid;
		
		$query = 'UPDATE music_series SET seriesname = \''.mysql_real_escape_string(stripslashes($seriesname)).'\' WHERE seriesid = '.$seriesid;
		
		return mysql_query($query, $this->database);
	}
	function set_series_of_game( $gameid, $seriesid ) {
		$gameid = (int)$gameid;
		$seriesid = (int)$seriesid;
		
		if ( $this->get_assigned_seriesid_from_gameid($gameid) ) {
			$query = 'UPDATE music_series_games SET seriesid = '.$seriesid.' WHERE gameid = '.$gameid;
			return mysql_query($query, $this->database);
		} else {
			$query = 'INSERT INTO music_series_games (seriesid, gameid) VALUES ('.$seriesid.', '.$gameid.')';
			return mysql_query($query, $this->database);
		}
		return false;
	}
	function get_assigned_seriesid_from_gameid($gameid) {
		$gameid = (int)$gameid;
		$query = 'SELECT seriesid FROM music_series_games WHERE gameid = '.$gameid;
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			if ( $data = mysql_fetch_assoc($resultset) ) {
				return (int)$data['seriesid'];
			}
			return false;
		}
		return false;
	}
	function change_song_availability( $songid, $available = true ) {
		$songid = (int)$songid;
		$query = 'UPDATE music_songs SET available = '.( $available ? 1 : 0 ).' WHERE songid = '.$songid;

		return mysql_query($query, $this->database);
	}
	
	function get_guessed_song_ids( $userid, $start_with = 0, $amount = 50 ) {
		$userid = (int)$userid;
		$start_with = (int)$start_with;
		$amount = (int)$amount;
		$query = 'SELECT songid FROM music_guesses WHERE userid = '.$userid.' ORDER BY time DESC LIMIT '.$start_with.', '.$amount;
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$songids = array();
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$songids[] = $data['songid'];
			}
			return $songids;
		}
		
		return false;
	}
	
	function get_vgmusicoftheday_types($linktype = -1) {
		$linktype = (int)$linktype;
		$query = 'SELECT id, name, icon FROM vgmusicoftheday_types'
				.( $linktype == -1 ? '' : ' WHERE linktype = '.$linktype )
				.' ORDER BY id';
	
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$types = array();
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$typeid = (int)$data['id'];
				$types[$typeid] = new vgmusicoftheday_type($typeid, $data['name'], $data['icon']);
			}
			return $types;
		}
	}
	
	function get_vgmusicoftheday_songs_count($search_string = false) {
		if ( $search_string !== false ) {
			$search_string = mysql_real_escape_string(stripslashes($search_string));
			$query = 'SELECT COUNT(1) as c FROM vgmusicoftheday'
				.' LEFT JOIN music_users ON uploaderid = userid'
				.' WHERE UPPER(artist) LIKE UPPER("%'.$search_string.'%") OR UPPER(game) LIKE UPPER("%'.$search_string.'%") OR UPPER(song) LIKE UPPER("%'.$search_string.'%") OR UPPER(username) LIKE UPPER("'.$search_string.'")';
		} else {
			$query = 'SELECT COUNT(1) as c FROM vgmusicoftheday';
		}
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			return $data['c'];
		}
	}
	
	function add_vgmusicoftheday_url( $vgm_id, $url_type, $url ) {
		$vgm_id = (int)$vgm_id;
		$url_type = (int)$url_type;
		$url = mysql_real_escape_string(stripslashes($url));
		
		$query = 'INSERT INTO vgmusicoftheday_urls ( vgm_id, url, url_type ) VALUES ( '.$vgm_id.', "'.$url.'", '.$url_type.' )';
		
		if ( mysql_query($query, $this->database) ) {
			return mysql_insert_id();
		}
		
		return false;
	}
	
	function edit_vgmusicoftheday_url( $id, $vgm_id, $url_type, $url ) {
		$id = (int)$id;
		$vgm_id = (int)$vgm_id;
		$url_type = (int)$url_type;
		$url = mysql_real_escape_string(stripslashes($url));
		
		$query = 'UPDATE vgmusicoftheday_urls SET vgm_id = '.$vgm_id.', url = "'.$url.'", url_type = '.$url_type.' WHERE id = '.$id;
		
		return mysql_query($query, $this->database);
	}
	
	function delete_vgmusicoftheday_url( $id ) {
		$id = (int)$id;
		
		$query = 'DELETE FROM vgmusicoftheday_urls WHERE id = '.$id;
		
		return mysql_query($query, $this->database);
	}

	function edit_vgmusicoftheday_song( $id, $day, $artist, $game, $song, $quiz_id, $uploaderid ) {
		$id = (int)$id;
		$day = mysql_real_escape_string(stripslashes($day));
		$artist = mysql_real_escape_string(stripslashes($artist));
		$game = mysql_real_escape_string(stripslashes($game));
		$song = mysql_real_escape_string(stripslashes($song));
		$quiz_id = (int)$quiz_id;
		$uploaderid = (int)$uploaderid;
		
		$query = 'UPDATE vgmusicoftheday'
				.' SET day = STR_TO_DATE("'.$day.'","%Y-%m-%d"), artist = "'.$artist.'", game = "'.$game.'", song = "'.$song.'",'
				.' quiz_id = '.( $quiz_id == 0 ? 'NULL' : $quiz_id ).', uploaderid = '.( $uploaderid == 0 ? 'NULL' : $uploaderid )
				.' WHERE id = '.$id;
		
		return mysql_query($query, $this->database);
	}

	function add_vgmusicoftheday_song( $day, $artist, $game, $song, $quiz_id, $uploaderid ) {
		$day = mysql_real_escape_string(stripslashes($day));
		$artist = mysql_real_escape_string(stripslashes($artist));
		$game = mysql_real_escape_string(stripslashes($game));
		$song = mysql_real_escape_string(stripslashes($song));
		$quiz_id = (int)$quiz_id;
		$uploaderid = (int)$uploaderid;
		
		$query = 'INSERT INTO vgmusicoftheday ( day, artist, game, song, quiz_id, uploaderid ) VALUES'
				.' ( STR_TO_DATE("'.$day.'","%Y-%m-%d"), "'.$artist.'", "'.$game.'", "'.$song.'",'
				.' '.( $quiz_id == 0 ? 'NULL' : $quiz_id ).', '.( $uploaderid == 0 ? 'NULL' : $uploaderid ).' )';
		
		if ( mysql_query($query, $this->database) ) {
			return mysql_insert_id();
		}
		
		return false;
	}
	
	function get_vgmusicoftheday_song( $id ) {
		
		$query = 'SELECT id, day, artist, game, song, quiz_id, userid, username FROM vgmusicoftheday'
				.' LEFT JOIN music_users ON uploaderid = userid'
				.' WHERE id = '.$id;
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$i = 0;
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$songid = (int)$data['id'];
				$songs = new song($songid, null, null, $data['game'], $data['song']);
				$songs->artist = $data['artist'];
				$songs->date = $data['day'];
				$songs->gameid = $data['quiz_id'];
				$songs->userid = $data['userid'];
				$songs->username = $data['username'];
				
				// grab urls
				$query_urls = 'SELECT id, url, url_type FROM vgmusicoftheday_urls WHERE vgm_id = '.$songid.' ORDER BY url_type ASC';
				$resultset_urls = mysql_query($query_urls, $this->database);
				if ( $resultset_urls ) {
					$urls = array();
					while ( $data_urls = mysql_fetch_assoc($resultset_urls) ) {
						$urls[] = new url_container($data_urls['id'], $data_urls['url'], $data_urls['url_type']);
					}
					$songs->url = $urls;
				}
				// end grab urls

				$i++;
			}
			return $songs;
		}
		
		return false;
	}
	function get_vgmusicoftheday_songs( $start_with = 0, $amount = 50, $order = 'day ASC', $search_string = false ) {
		$start_with = (int)$start_with;
		$amount = (int)$amount;
		if ( $search_string !== false ) {
			$search_string = mysql_real_escape_string(stripslashes($search_string));
		}
		
		$query = 'SELECT id, day, artist, game, song, quiz_id, userid, username FROM vgmusicoftheday'
				.' LEFT JOIN music_users ON uploaderid = userid'
				.( $search_string === false ? '' :
					' WHERE UPPER(artist) LIKE UPPER("%'.$search_string.'%") OR UPPER(game) LIKE UPPER("%'.$search_string.'%") OR UPPER(song) LIKE UPPER("%'.$search_string.'%") OR UPPER(username) LIKE UPPER("'.$search_string.'")' )
				.' ORDER BY '.$order
				.' LIMIT '.$start_with.', '.$amount;
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$songs = array();
			$i = 0;
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$songid = (int)$data['id'];
				$songs[$i] = new song($songid, null, null, $data['game'], $data['song']);
				$songs[$i]->artist = $data['artist'];
				$songs[$i]->date = $data['day'];
				$songs[$i]->gameid = $data['quiz_id'];
				$songs[$i]->userid = $data['userid'];
				$songs[$i]->username = $data['username'];
				
				// grab urls
				$query_urls = 'SELECT id, url, url_type FROM vgmusicoftheday_urls WHERE vgm_id = '.$songid.' ORDER BY url_type ASC';
				$resultset_urls = mysql_query($query_urls, $this->database);
				if ( $resultset_urls ) {
					$urls = array();
					while ( $data_urls = mysql_fetch_assoc($resultset_urls) ) {
						$urls[] = new url_container($data_urls['id'], $data_urls['url'], $data_urls['url_type']);
					}
					$songs[$i]->url = $urls;
				}
				// end grab urls

				$i++;
			}
			return $songs;
		}
		
		return false;
	}
	
	function get_guessed_songs( $userid, $songid_only, $start_with = 0, $amount = 50, $order = 'time DESC', $include_series = false, $halfguess_check = false ) {
		$userid = (int)$userid;
		$start_with = (int)$start_with;
		$amount = (int)$amount;
		
		if ( $userid > 0 ) {
			$query = 'SELECT music_guesses.songid, gameguess, songguess, points, gamename, songname, gameguessed, gamecorrect, gamenoguess, songguessed, songcorrect, songnoguess, skipped, hidefromall'
					.( $include_series ? ', music_series.seriesid, seriesname, seriesgameguessed, seriesgamecorrect, seriesgamenoguess, seriessongguessed, seriessongcorrect, seriessongnoguess, seriesskipped, music_series.seriesid IS NULL AS isnull' : '' )
					.' FROM music_guesses'
					.' JOIN music_songs ON music_guesses.songid = music_songs.songid'
					.' JOIN music_games ON music_games.gameid = music_songs.gameid'
					.' JOIN music_songnames ON music_songnames.songid = music_songs.songid'
					.( $include_series ? ' LEFT JOIN music_series_games ON music_games.gameid = music_series_games.gameid'
										.' LEFT JOIN music_series ON music_series.seriesid = music_series_games.seriesid' : '' )
					.' WHERE music_games.priority = 1 AND music_songnames.priority = 1'
					.' AND music_guesses.userid = '.$userid
					.( $halfguess_check ? ' AND points > 3' : ' AND points <= 3 ORDER BY '.$order )
					.' LIMIT '.$start_with.', '.$amount;
		} else {
			$query = 'SELECT music_guesses.userid, music_users.username, music_guesses.songid, gameguess, songguess, points, gamename, songname, gameguessed, gamecorrect, gamenoguess, songguessed, songcorrect, songnoguess, skipped, hidefromall'
					.( $include_series ? ', music_series.seriesid, seriesname, seriesgameguessed, seriesgamecorrect, seriesgamenoguess, seriessongguessed, seriessongcorrect, seriessongnoguess, seriesskipped, music_series.seriesid IS NULL AS isnull' : '' )
					.' FROM music_guesses'
					.' JOIN music_songs ON music_guesses.songid = music_songs.songid'
					.' JOIN music_games ON music_games.gameid = music_songs.gameid'
					.' JOIN music_songnames ON music_songnames.songid = music_songs.songid'
					.' JOIN music_users ON music_guesses.userid = music_users.userid'
					.( $include_series ? ' LEFT JOIN music_series_games ON music_games.gameid = music_series_games.gameid'
										.' LEFT JOIN music_series ON music_series.seriesid = music_series_games.seriesid' : '' )
					.' WHERE music_games.priority = 1 AND music_songnames.priority = 1'
					.( $userid == 0 ? ' AND hidefromall = 0' : '' )
					.( $songid_only != 0 ? ' AND music_guesses.songid = '.$songid_only : '' )
					.' ORDER BY '.$order.' LIMIT '.$start_with.', '.$amount;
		}
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$songs = array();
			$i = 0;
			while ( $data = mysql_fetch_assoc($resultset) ) {
				$songid = (int)$data['songid'];
				$songs[$i] = new song($songid, null, null, $data['gamename'], $data['songname']);
				$songs[$i]->game_guessed = str_replace( array('<', '>', '\\\'') , array('&lt;', '&gt;', '\'') , $data['gameguess'] );
				$songs[$i]->name_guessed = str_replace( array('<', '>', '\\\'') , array('&lt;', '&gt;', '\'') , $data['songguess'] );
				$data['points'] = (int)$data['points'];
				if ( $data['points'] >= 4 ) {
					$songs[$i]->halfguess = true;
					$data['points'] -= 4;
				} else {
					$songs[$i]->halfguess = false;
				}
				$songs[$i]->game_correct = ( $data['points'] == 1 || $data['points'] == 3 );
				$songs[$i]->name_correct = ( $data['points'] >= 2 );

				$songs[$i]->guessamount['gameguessed'] = (int)$data['gameguessed'];
				$songs[$i]->guessamount['gamecorrect'] = (int)$data['gamecorrect'];
				$songs[$i]->guessamount['gamenoguess'] = (int)$data['gamenoguess'];
				$songs[$i]->guessamount['songguessed'] = (int)$data['songguessed'];
				$songs[$i]->guessamount['songcorrect'] = (int)$data['songcorrect'];
				$songs[$i]->guessamount['songnoguess'] = (int)$data['songnoguess'];
				$songs[$i]->guessamount['skipped'] = (int)$data['skipped'];

				if ( $userid <= 0 ) {
					$songs[$i]->userid = $data['userid'];
					$songs[$i]->username = $data['username'];
				}
				if ( $include_series ) {
					if ( !$songs[$i]->halfguess ) {
						$songs[$i]->seriesid = $data['seriesid'];
						$songs[$i]->seriesname = $data['seriesname'];
						$songs[$i]->seriesamount['gameguessed'] = (int)$data['seriesgameguessed'];
						$songs[$i]->seriesamount['gamecorrect'] = (int)$data['seriesgamecorrect'];
						$songs[$i]->seriesamount['gamenoguess'] = (int)$data['seriesgamenoguess'];
						$songs[$i]->seriesamount['songguessed'] = (int)$data['seriessongguessed'];
						$songs[$i]->seriesamount['songcorrect'] = (int)$data['seriessongcorrect'];
						$songs[$i]->seriesamount['songnoguess'] = (int)$data['seriessongnoguess'];
						$songs[$i]->seriesamount['skipped'] = (int)$data['seriesskipped'];
					} else {
						$songs[$i]->seriesid = 0;
					}
				}
				
				$songs[$i]->hidden = (int)$data['hidefromall'];
				
				$i++;
			}
			return $songs;
		}
		
		return false;
	}
	function get_guessed_song( $userid, $songid ) {
		$song = $this->get_song( $songid );
		$userid = (int)$userid;
		$songid = (int)$songid;
		$query = 'SELECT gameguess, songguess, points FROM music_guesses WHERE userid = '.$userid.' AND songid = '.$songid;
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			$song->game_guessed = str_replace( array('<', '>', '\\\'') , array('&lt;', '&gt;', '\'') , $data['gameguess'] );
			$song->name_guessed = str_replace( array('<', '>', '\\\'') , array('&lt;', '&gt;', '\'') , $data['songguess'] );
			$data['points'] = (int)$data['points'];
			$song->game_correct = ( $data['points'] == 1 || $data['points'] == 3 );
			$song->name_correct = ( $data['points'] >= 2 );
			return $song;
		}
		
		return false;

	}
	function edit_result( $userid, $songid, $pointchange ) {
		$userid = (int)$userid;
		$songid = (int)$songid;
		$pointchange = (int)$pointchange;
		if ( $pointchange < -2 || $pointchange > 2 || $pointchange == 0 ) return false;
		
		$query = 'UPDATE music_guesses SET points = ( points + '.$pointchange.' ) WHERE songid = '.$songid.' AND userid = '.$userid;
		if ( mysql_query($query, $this->database) ) {
			switch ( $pointchange ) {
				case 1:
					$query = 'UPDATE music_songs SET gamecorrect = gamecorrect+1 WHERE songid = '.$songid;
					break;
				case 2:
					$query = 'UPDATE music_songs SET songcorrect = songcorrect+1 WHERE songid = '.$songid;
					break;
				case -1:
					$query = 'UPDATE music_songs SET gamecorrect = gamecorrect-1 WHERE songid = '.$songid;
					break;
				case -2:
					$query = 'UPDATE music_songs SET songcorrect = songcorrect-1 WHERE songid = '.$songid;
					break;
			}
			mysql_query($query, $this->database);
			return true;
		}
		return false;
	}
	
	function get_amount_guessed_songs( $userid, $songid, $points = false ) {
		$userid = (int)$userid;
		$songid = (int)$songid;
		$query  = 'SELECT COUNT(1) AS songcount FROM music_guesses';
		if ( $userid > 0 ) {
			$query .= ' WHERE userid = '.$userid;
			if ( $points ) {
				$query .= ' AND points = '.$points;
			}
		} else {
			if ( $userid == 0 ) {
				$query .= ' WHERE hidefromall = 0';
				if ( $songid > 0 ) {
					$query .= ' AND songid = '.$songid;
				}
			} else if ( $songid > 0 ) {
				$query .= ' WHERE songid = '.$songid;
			}
		}
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			return $data['songcount'];
		}
		
		return false;
	}
	function get_amount_halfguessed_songs( $userid ) {
		$userid = (int)$userid;
		$query  = 'SELECT COUNT(1) AS songcount FROM music_guesses WHERE userid = '.$userid.' AND points > 3';
		
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			return $data['songcount'];
		}
		
		return false;
	}
	function get_amount_people_guessed( $songid ) {
		$songid = (int)$songid;
		
		$query = 'SELECT ( SELECT COUNT(1) AS songcount FROM music_guesses WHERE songid = '.$songid.' AND gameguess IS NOT NULL ) AS gameguessed, '
				.' ( SELECT COUNT(1) AS songcount FROM music_guesses WHERE songid = '.$songid.' AND ( points = 1 OR points = 3 OR points = 5 ) ) AS gamecorrect, '
				.' ( SELECT COUNT(1) AS songcount FROM music_guesses WHERE songid = '.$songid.' AND gameguess IS NULL ) AS gamenoguess, '
				.' ( SELECT COUNT(1) AS songcount FROM music_guesses WHERE songid = '.$songid.' AND songguess IS NOT NULL ) AS songguessed, '
				.' ( SELECT COUNT(1) AS songcount FROM music_guesses WHERE songid = '.$songid.' AND ( points = 2 OR points = 3 OR points = 6 ) ) AS songcorrect, '
				.' ( SELECT COUNT(1) AS songcount FROM music_guesses WHERE songid = '.$songid.' AND songguess IS NULL ) AS songnoguess, '
				.' ( SELECT COUNT(1) AS songcount FROM music_skipped WHERE songid = '.$songid.' ) AS skipped '
				;

		/*	Query that ignores "not guessed" entries
				$query = 'SELECT ( SELECT COUNT(1) AS songcount FROM music_guesses WHERE songid = '.$songid.' AND gameguess IS NOT NULL ) AS gametotal, '
				.' ( SELECT COUNT(1) AS songcount FROM music_guesses WHERE songid = '.$songid.' AND gameguess IS NOT NULL AND ( points = 1 OR points = 3 ) ) AS gamecorrect, '
				.' ( SELECT COUNT(1) AS songcount FROM music_guesses WHERE songid = '.$songid.' AND songguess IS NOT NULL ) AS songtotal, '
				.' ( SELECT COUNT(1) AS songcount FROM music_guesses WHERE songid = '.$songid.' AND songguess IS NOT NULL AND ( points = 2 OR points = 3 ) ) AS songcorrect, '
				.' ( SELECT COUNT(1) AS songcount FROM music_skipped WHERE songid = '.$songid.' ) AS skipped '
				;
		*/
				
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			$guessamount['gameguessed'] = (int)$data['gameguessed'];
			$guessamount['gamecorrect'] = (int)$data['gamecorrect'];
			$guessamount['gamenoguess'] = (int)$data['gamenoguess'];
			$guessamount['songguessed'] = (int)$data['songguessed'];
			$guessamount['songcorrect'] = (int)$data['songcorrect'];
			$guessamount['songnoguess'] = (int)$data['songnoguess'];
			$guessamount['skipped'] = (int)$data['skipped'];
		} else {
			return false;
		}

		return $guessamount;
	}
	function get_amount_people_guessed_from_cache( $songid ) {
		$songid = (int)$songid;
		
		$query = 'SELECT gameguessed, gamecorrect, gamenoguess, songguessed, songcorrect, songnoguess, skipped FROM music_songs WHERE songid = '.$songid;
		$resultset = mysql_query($query, $this->database);
		if ( $resultset ) {
			$data = mysql_fetch_assoc($resultset);
			$guessamount['gameguessed'] = (int)$data['gameguessed'];
			$guessamount['gamecorrect'] = (int)$data['gamecorrect'];
			$guessamount['gamenoguess'] = (int)$data['gamenoguess'];
			$guessamount['songguessed'] = (int)$data['songguessed'];
			$guessamount['songcorrect'] = (int)$data['songcorrect'];
			$guessamount['songnoguess'] = (int)$data['songnoguess'];
			$guessamount['skipped'] = (int)$data['skipped'];
		} else {
			return false;
		}

		return $guessamount;
	}
	
	function set_calculated_difficulty( $songid, $calcdiff ) {
		$songid = (int)$songid;
		$calcdiff = (int)$calcdiff;
				
		$query = 'UPDATE music_songs SET calcdiff = '.$calcdiff.' WHERE songid = '.$songid;
		
		return mysql_query($query, $this->database);
	}
	function set_guessamount_cache( $songid, $gameguessed, $gamecorrect, $gamenoguess, $songguessed, $songcorrect, $songnoguess, $skipped ) {
		$songid = (int)$songid;
		$gameguessed = (int)$gameguessed;
		$gamecorrect = (int)$gamecorrect;
		$gamenoguess = (int)$gamenoguess;
		$songguessed = (int)$songguessed;
		$songcorrect = (int)$songcorrect;
		$songnoguess = (int)$songnoguess;
		$skipped = (int)$skipped;
				
		$query = 'UPDATE music_songs SET gameguessed = '.$gameguessed.', gamecorrect = '.$gamecorrect.', gamenoguess = '.$gamenoguess.', songguessed = '.$songguessed.', songcorrect = '.$songcorrect.', songnoguess = '.$songnoguess.', skipped = '.$skipped.' WHERE songid = '.$songid;
		
		return mysql_query($query, $this->database);
	}
	function set_seriesamount_cache( $seriesid, $gameguessed, $gamecorrect, $gamenoguess, $songguessed, $songcorrect, $songnoguess, $skipped ) {
		$seriesid = (int)$seriesid;
		$gameguessed = (int)$gameguessed;
		$gamecorrect = (int)$gamecorrect;
		$gamenoguess = (int)$gamenoguess;
		$songguessed = (int)$songguessed;
		$songcorrect = (int)$songcorrect;
		$songnoguess = (int)$songnoguess;
		$skipped = (int)$skipped;
				
		$query = 'UPDATE music_series SET seriesgameguessed = '.$gameguessed.', seriesgamecorrect = '.$gamecorrect.', seriesgamenoguess = '.$gamenoguess.', seriessongguessed = '.$songguessed.', seriessongcorrect = '.$songcorrect.', seriessongnoguess = '.$songnoguess.', seriesskipped = '.$skipped.' WHERE seriesid = '.$seriesid;
		
		return mysql_query($query, $this->database);
	}

	function set_guessorder( $userid, $guessorder ) {
		$userid = (int)$userid;
		$guessorder = (int)$guessorder;
				
		$query = 'UPDATE music_users SET guessorder = '.$guessorder.' WHERE userid = '.$userid;
		
		return mysql_query($query, $this->database);
	}
	function set_autoplay( $userid, $autoplay ) {
		$userid = (int)$userid;
		$autoplay = (int)$autoplay;
				
		$query = 'UPDATE music_users SET autoplay = '.$autoplay.' WHERE userid = '.$userid;
		
		return mysql_query($query, $this->database);
	}
	function set_halfguess( $userid, $halfguess ) {
		$userid = (int)$userid;
		$halfguess = (int)$halfguess;
				
		$query = 'UPDATE music_users SET halfguess = '.$halfguess.' WHERE userid = '.$userid;
		
		return mysql_query($query, $this->database);
	}
	function hide_guess_from_all( $userid, $songid ) {
		$userid = (int)$userid;
		$songid = (int)$songid;
		
		$query = 'UPDATE music_guesses SET hidefromall = 1 WHERE userid = '.$userid.( $songid != -1 ? ' AND songid = '.$songid : '' );
		
		return mysql_query($query, $this->database);
	}
}
?>