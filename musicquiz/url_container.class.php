<?php
class vgmusicoftheday_type {
	var $typeid;
	var $name;
	var $icon;

	function __construct($typeid, $name, $icon) {
		$this->typeid = (int)$typeid;
		$this->name = $name;
		$this->icon = $icon;
	}
}

class url_container {
	var $urlid;
	var $url;
	var $url_type;
	var $icon;
	
	static $types = null;

	function __construct($urlid, $url, $url_type) {
		$this->urlid = (int)$urlid;
		$this->url = $url;
		$this->url_type = (int)$url_type;
	}
	
	static function get_types($linktype = -1) {
		if ( $linktype == -1 ) {
			if ( self::$types == null ) {
				global $session;
				self::$types = $session->db->get_vgmusicoftheday_types();
			}
			return self::$types;
		} else {
			global $session;
			return $session->db->get_vgmusicoftheday_types($linktype);
		}
	}
	
	function has_icon() {
		if ( self::$types == null ) self::get_types();
		if ( self::$types[$this->url_type]->icon != null ) return true;
		return false;
	}
	
	function get_icon() {
		if ( self::$types == null ) self::get_types();
		return self::$types[$this->url_type]->icon;
	}
	
	function get_typename() {
		if ( self::$types == null ) self::get_types();
		return self::$types[$this->url_type]->name;
	}
}
?>