<?php
class skitLine {
	var $jpChar;
	var $enChar;
	var $jpText;
	var $enText;
	
	function __construct( $jpChar, $jpText, $enChar, $enText ) {
		$this->jpChar = $jpChar;
		$this->enChar = $enChar;
		$this->jpText = $jpText;
		$this->enText = $enText;
	}
	
	function GetEnName() {
		switch ( $this->enChar ) {
			case 'ALL':   return 'Everyone';
			case 'BAU':   return 'Ba\'ul';
			case 'EST':   return 'Estellise';
			case 'EST_P': return 'Estelle';
			case 'FRE':   return 'Flynn';
			case 'JUD':   return 'Judith';
			case 'KAR':   return 'Karol';
			case 'PAT':   return 'Patty';
			case 'RAP':   return 'Repede';
			case 'RAV':   return 'Raven';
			case 'RIT':   return 'Rita';
			case 'YUR':   return 'Yuri';
		}
	}
	
	function GetJpName() {
		switch ( $this->jpChar ) {
			case 'ALL':   return 'みんな';
			case 'BAU':   return 'バウル';
			case 'EST':   return 'エステリーゼ';
			case 'EST_P': return 'エステル';
			case 'FRE':   return 'フレン';
			case 'JUD':   return 'ジュディス';
			case 'KAR':   return 'カロル';
			case 'PAT':   return 'パティ';
			case 'RAP':   return 'ラピード';
			case 'RAV':   return 'レイヴン';
			case 'RIT':   return 'リタ';
			case 'YUR':   return 'ユーリ';
		}
	}
}

class skitMeta {
	var $category;
	var $characterBitmask;
	var $jpName;
	var $enName;
	var $jpCond;
	var $enCond;
	
	function __construct( $category, $characterBitmask, $jpName, $enName, $jpCond, $enCond ) {
		$this->category = $category;
		$this->characterBitmask = $characterBitmask;
		$this->jpName = $jpName;
		$this->enName = $enName;
		$this->jpCond = $jpCond;
		$this->enCond = $enCond;
	}
}
?>