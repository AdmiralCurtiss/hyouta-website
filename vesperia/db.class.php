<?php
require_once 'arte.class.php';

class db {
	var $conn;
	
	function __construct() {
		$username = 'vesperia';
		$password = 'x4mrsSRfVQwsAPWKd35HjOzCqetCFYVzH7UkOmKJqhfoRLEV7RHT0XwzAwEP';
		$this->conn = new PDO('mysql:host=localhost;dbname=tov-360', $username, $password);
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	
	function GetArtes() {
		$stmt = $this->conn->prepare( 'SELECT Artes.id, gameId, refString, strDicName, strDicDesc, `type`, `character`, tpUsage, fatalStrikeType, usableInMenu, fire, earth, wind, water, light, dark, sname.japanese AS jpName, sname.english AS enName, dname.japanese AS jpDesc, dname.english AS enDesc '
			.'FROM Artes '
			.'LEFT JOIN StringDic sname ON Artes.strDicName = sname.id '
			.'LEFT JOIN StringDic dname ON Artes.strDicDesc = dname.id '
			.'ORDER BY id ASC' );
		$stmt->execute();
		
		$artes = array();
		while( $r = $stmt->fetch() ) {
			$a = new Arte();
			$a->id = $r['id'];
			$a->gameId = $r['gameId'];
			$a->refString = $r['refString'];
			$a->strDicName = $r['strDicName'];
			$a->strDicDesc = $r['strDicDesc'];
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
			$artes[] = $a;
		}
		return $artes;
	}
}
?>