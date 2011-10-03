<?php
class user {
	var $userid;
	var $username;
	var $role;
	var $halfguess;
	var $guessorder;
	var $autoplay;

	function __construct($userid, $username, $role, $halfguess, $guessorder, $autoplay = true) {
		$this->userid = (int)$userid;
		$this->username = $username;
		$this->role = (int)$role;
		$this->halfguess = (int)$halfguess;
		$this->guessorder = (int)$guessorder;
		$this->autoplay = (int)$autoplay;
	}
	
	function is_admin() {
		if ( $this->role == 9 ) {
			return true;
		}
		return false;
	}
}
?>