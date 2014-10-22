<?php
require_once 'arte.class.php';

class db {
	var $conn;
	// technically this could be used to print out a page for other languages as well, but I haven't actually done that
	var $lang0 = 0; // japanese
	var $lang1 = 1; // english
	
	function __construct() {
		$username = 'vesperia';
		$password = 'x4mrsSRfVQwsAPWKd35HjOzCqetCFYVzH7UkOmKJqhfoRLEV7RHT0XwzAwEP';
		$this->conn = new PDO('mysql:host=localhost;dbname=tov-360', $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	
	function GetArteLearnReqs( $arteId ) {
		$stmt = $this->conn->prepare( 'SELECT `type`, value, useCount FROM Artes_LearnReqs WHERE arteId = :arteId ORDER BY id ASC' );
		$stmt->execute( array('arteId' => $arteId) );
		
		$reqs = array();
		while( $r = $stmt->fetch() ) {
			$l = new ArteLearnReqs();
			$l->type = $r['type'];
			$l->value = $r['value'];
			$l->useCount = $r['useCount'];
			$reqs[] = $l;
		}
		return $reqs;
	}
	
	function GetArteAlteredReqs( $arteId ) {
		$stmt = $this->conn->prepare( 'SELECT `type`, value FROM Artes_AlteredReqs WHERE arteId = :arteId ORDER BY id ASC' );
		$stmt->execute( array('arteId' => $arteId) );
		
		$reqs = array();
		while( $r = $stmt->fetch() ) {
			$l = new ArteAlteredReqs();
			$l->type = $r['type'];
			$l->value = $r['value'];
			$reqs[] = $l;
		}
		return $reqs;
	}
	
	function GetArtes() {
		$stmt = $this->conn->prepare( 'SELECT Artes.id, Artes.gameId, Artes.refString, `type`, `character`, tpUsage, fatalStrikeType, usableInMenu, fire, earth, wind, water, light, dark, sjname.entry AS jpName, sename.entry AS enName, djname.entry AS jpDesc, dename.entry AS enDesc, Enemies.icon, ename.entry AS enemyName '
			.'FROM Artes '
			.'LEFT JOIN StringDic sjname ON Artes.strDicName = sjname.gameId AND sjname.language = :lang0 '
			.'LEFT JOIN StringDic djname ON Artes.strDicDesc = djname.gameId AND djname.language = :lang0 '
			.'LEFT JOIN StringDic sename ON Artes.strDicName = sename.gameId AND sename.language = :lang1 '
			.'LEFT JOIN StringDic dename ON Artes.strDicDesc = dename.gameId AND dename.language = :lang1 '
			.'LEFT JOIN Enemies ON Artes.character = Enemies.gameId '
			.'LEFT JOIN StringDic ename ON Enemies.strDicName = ename.gameId AND ename.language = :lang1 '
			.'ORDER BY id ASC' );
		$stmt->execute( array('lang0' => $this->lang0, 'lang1' => $this->lang1) );
		
		$artes = array();
		while( $r = $stmt->fetch() ) {
			$a = new Arte();
			$a->id = $r['id'];
			$a->gameId = $r['gameId'];
			$a->refString = $r['refString'];
			$a->type = $r['type'];
			$a->character = $r['character'];
			$a->tpUsage = $r['tpUsage'];
			$a->fatalStrikeType = $r['fatalStrikeType'];
			$a->usableInMenu = $r['usableInMenu'];
			$a->fire = $r['fire'];
			$a->earth = $r['earth'];
			$a->wind = $r['wind'];
			$a->water = $r['water'];
			$a->light = $r['light'];
			$a->dark = $r['dark'];
			$a->name = array( $r['jpName'], $r['enName'] );
			$a->desc = array( $r['jpDesc'], $r['enDesc'] );
			$a->enemyIcon = (int)$r['icon'];
			$a->enemyName = $r['enemyName'];
			
			$a->learnReqs = $this->GetArteLearnReqs( $a->id );
			$a->alteredReqs = $this->GetArteAlteredReqs( $a->id );
			
			$artes[] = $a;
		}
		return $artes;
	}
}
?>