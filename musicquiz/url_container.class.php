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
	
	static function get_types() {
		$types = $session->db->get_vgmusicoftheday_types;
	}
	
	function has_icon() {
		if ( $types == null ) get_types();
		if ( $types[$url_type]->icon != null ) return true;
		return false;
	}
	
	function get_icon() {
		if ( $types == null ) get_types();
		return $types[$url_type]->icon;
	}
	
	function get_typename() {
		if ( $types == null ) get_types();
		return $types[$url_type]->name;
	}
}
?>