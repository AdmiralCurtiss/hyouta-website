<?php
class passbcrypt_holder {
	var $salt;
	var $hash;

	function __construct($salt, $hash) {
		$this->salt = $salt;
		$this->hash = $hash;
	}
}
?>