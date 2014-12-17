<?php
class skitLine {
	var $character;
	var $jpText;
	var $enText;
	
	function __construct( $character, $jpText, $enText ) {
		$this->character = $character;
		$this->jpText = $jpText;
		$this->enText = $enText;
	}
	
	function GetEnName() {
		switch ( $this->character ) {
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
		switch ( $this->character ) {
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
?>