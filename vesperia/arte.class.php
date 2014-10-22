<?php

class ArteLearnReqs {
	var $type;
	var $value;
	var $useCount;
}

class ArteAlteredReqs {
	var $type;
	var $value;
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
}

?>