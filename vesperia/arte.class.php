<?php

class ArteLearnReqs {
	var $type;
	var $value;
	var $useCount;
	
	function __construct( $r ) {
		$this->type = $r['type'];
		$this->value = $r['value'];
		$this->useCount = $r['useCount'];
	}
}

class ArteAlteredReqs {
	var $type;
	var $value;
	
	function __construct( $r ) {
		$this->type = $r['type'];
		$this->value = $r['value'];
	}
}

class Arte {
	var $id;
	var $gameId;
	var $refString;
	var $type;
	var $character;
	var $tpUsage;
	var $fatalStrikeType;
	var $usableInMenu;
	var $fire;
	var $earth;
	var $wind;
	var $water;
	var $light;
	var $dark;
	
	var $name;
	var $desc;
	
	var $enemyIcon;
	var $enemyName;
	
	var $learnReqs;
	var $alteredReqs;

	function __construct( $r ) {
		$this->id = $r['id'];
		$this->gameId = $r['gameId'];
		$this->refString = $r['refString'];
		$this->type = $r['type'];
		$this->character = $r['character'];
		$this->tpUsage = $r['tpUsage'];
		$this->fatalStrikeType = $r['fatalStrikeType'];
		$this->usableInMenu = $r['usableInMenu'];
		$this->fire = $r['fire'];
		$this->earth = $r['earth'];
		$this->wind = $r['wind'];
		$this->water = $r['water'];
		$this->light = $r['light'];
		$this->dark = $r['dark'];
		$this->name = array( $r['jpName'], $r['enName'] );
		$this->desc = array( $r['jpDesc'], $r['enDesc'] );
		$this->enemyIcon = (int)$r['icon'];
		$this->enemyName = $r['enemyName'];
	}
}

?>