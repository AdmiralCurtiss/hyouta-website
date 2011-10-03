<?php
require_once 'db.class.php';

class Session
{
	var $userid;
	var $sessiontoken;
	var $user;
	var $db;
	
	function __construct($database) {
		$this->db = new db($database);
		$this->startSession();
	}

	function startSession() {
		session_start();
		if ( isset($_POST['formaction']) && $_POST['formaction'] == 'login' ) {
			$this->logged_in = $this->login($_POST['user'], $_POST['pass'], ( isset($_POST['stayloggedin']) ? true : false ) );
		} else if ( isset($_POST['formaction']) && $_POST['formaction'] == 'logout' ) {
			$this->logout();
		} else {
			$this->logged_in = $this->checkLogin();
		}
	}

	function checkLogin() {
		if ( isset($_COOKIE['userid']) && isset($_COOKIE['session']) ) {
			$this->userid	    = $_SESSION['userid']  = $_COOKIE['userid'];
			$this->sessiontoken = $_SESSION['session'] = $_COOKIE['session'];
		}

		if ( isset($_SESSION['userid']) && isset($_SESSION['session']) ) {
				if ( $this->db->confirm_user($_SESSION['userid'], $_SESSION['session']) ) {
					$this->user   = $this->db->get_user($_SESSION['userid']);
					$this->userid = $this->user->userid;
					return true;
				} else {
					unset($_SESSION['userid']);
					unset($_SESSION['session']);
					return false;
				}
		} else {
			return false;
		}
	}

	function login($username, $password, $rememberme) {
		$this->userid = $this->db->get_userid($username);
		if ( $this->userid == false ) return false;
		$this->user   = $this->db->get_user_and_confirm_password($this->userid, $password);
		if ( $this->user == false ) return false;

		//username/password is correct
		$_SESSION['userid']  = $this->user->userid;
		$_SESSION['session'] = $this->generateRandStr(32);
		$this->db->update_session($_SESSION['userid'], $_SESSION['session'], $_SERVER['REMOTE_ADDR']);

		if($rememberme){
			setcookie('userid',  $_SESSION['userid'],  time()+60*60*24*31);
			setcookie('session', $_SESSION['session'], time()+60*60*24*31);
		}
		
		return true;
   }

	function logout() {
		if( isset($_COOKIE['userid']) ) {
			setcookie('userid',  '',  time()-60*60*24*31);
		}
		
		if( isset($_COOKIE['session']) ) {
			setcookie('session',  '',  time()-60*60*24*31);
		}
		
		unset($_SESSION['userid']);
		unset($_SESSION['session']);
		
		$this->logged_in = false;
		$this->user = false;
   }

   function generateRandStr($length) {
      $randstr = "";
      for($i=0; $i<$length; $i++){
         $randnum = mt_rand(0,61);
         if($randnum < 10){
            $randstr .= chr($randnum+48);
         }else if($randnum < 36){
            $randstr .= chr($randnum+55);
         }else{
            $randstr .= chr($randnum+61);
         }
      }
      return $randstr;
   }
};

?>
