<?php
class scenario {
	var $type;
	var $jpName;
	var $jpText;
	var $enName;
	var $enText;
	
	function __construct( $type, $jpName, $jpText, $enName, $enText ) {
		$this->type = $type;
		$this->jpName = $jpName;
		$this->jpText = $jpText;
		$this->enName = $enName;
		$this->enText = $enText;
	}
}
?>