<?php
require_once 'scenario.class.php';
require_once 'skitLine.class.php';

class db {
	var $conn;
	
	function __construct( $connstr, $username, $password ) {
		$this->conn = new PDO( $connstr, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'") );
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->conn->beginTransaction();
	}
	
	function __destruct() {
		$this->conn->rollBack();
	}
	
	function GetScenarioIndex( $type ) {
		$args = array();
		$s = 'SELECT id, sceneGroup, parent, episodeId, description FROM ScenarioMeta ';
		$s .= 'WHERE type = :type ';
		$args['type'] = $type;
		$s .= 'ORDER BY id ASC'; // should be "sceneGroup ASC, id ASC" but due to how this table is generated gives the same result
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$sce = array();
		while( $r = $stmt->fetch() ) {
			$sce[] = new scenarioMeta( $r['id'], $type, $r['sceneGroup'], $r['parent'], $r['episodeId'], $r['description'] );
		}
		return $sce;
	}
	
	function GetScenarioMetaFromEpisodeId( $episodeId ) {
		$args = array();
		$s = 'SELECT id, type, sceneGroup, parent, description FROM ScenarioMeta ';
		$s .= 'WHERE episodeId = :episodeId';
		$args['episodeId'] = $episodeId;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		if( $r = $stmt->fetch() ) {
			return new scenarioMeta( $r['id'], $r['type'], $r['sceneGroup'], $r['parent'], $episodeId, $r['description'] );
		}
		return null;
	}
	
	function GetScenarioMetaGroupRange( $type, $groupBegin, $groupEnd ) {
		$args = array();
		$s = 'SELECT id, type, sceneGroup, parent, episodeId, description FROM ScenarioMeta ';
		$s .= 'WHERE type = :type AND sceneGroup >= :groupBegin AND sceneGroup <= :groupEnd ';
		$args['type'] = $type;
		$args['groupBegin'] = $groupBegin;
		$args['groupEnd'] = $groupEnd;
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$sce = array();
		while( $r = $stmt->fetch() ) {
			$sce[] = new scenarioMeta( $r['id'], $r['type'], $r['sceneGroup'], $r['parent'], $r['episodeId'], $r['description'] );
		}
		return $sce;
	}
	
	function GetScenario( $episodeId ) {
		$args = array();
		$s = 'SELECT type, jpName, jpText, enName, enText FROM ScenarioDat ';
		$s .= 'WHERE episodeId = :searchId ';
		$args['searchId'] = $episodeId;
		$s .= 'ORDER BY displayOrder ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$sce = array();
		while( $r = $stmt->fetch() ) {
			$sce[] = new scenario( (int)$r['type'], $r['jpName'], $r['jpText'], $r['enName'], $r['enText'] );
		}
		return $sce;
	}
	
	function GetSkit( $skitId ) {
		$args = array();
		$s = 'SELECT jpChar, enChar, jpText, enText FROM SkitText ';
		$s .= 'WHERE skitId = :searchId ';
		$args['searchId'] = $skitId;
		$s .= 'ORDER BY displayOrder ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$lines = array();
		while( $r = $stmt->fetch() ) {
			$lines[] = new skitLine( $r['jpChar'], $r['jpText'], $r['enChar'], $r['enText'] );
		}
		return $lines;
	}
	
	function GetArtesHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM Artes ';
		if ( $id === false ) {
			$s .= 'WHERE ( ( Artes.type > 0 AND Artes.type <= 11 ) OR Artes.type = 13 ) AND ( Artes.character > 0 AND Artes.character <= 9 ) ';
		} else {
			$s .= 'WHERE Artes.gameId = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$artes = array();
		while( $r = $stmt->fetch() ) {
			$a = $r['html'];
			$artes[] = $a;
		}
		return $artes;
	}
	
	function GetSkillsHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM Skills ';
		if ( $id === false ) {
			$s .= 'WHERE id > 0 AND equipCost > 0 ';
		} else {
			$s .= 'WHERE gameId = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetRecipesHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM Recipes ';
		if ( $id === false ) {
			$s .= 'WHERE id > 0 ';
		} else {
			$s .= 'WHERE id = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetShopsHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM Shops ';
		if ( $id === false ) {
			$s .= 'WHERE gameId > 1 ';
		} else {
			$s .= 'WHERE gameId = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetTitlesHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM Titles ';
		if ( $id === false ) {
			$s .= 'WHERE gameId > 0 AND points > 0 ';
		} else {
			$s .= 'WHERE gameId = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetSynopsisHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM Synopsis ';
		if ( $id === false ) {
			$s .= 'WHERE storyMax > 0 ';
		} else {
			$s .= 'WHERE id = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetBattleBookHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM BattleBook ';
		if ( $id === false ) {
			$s .= 'WHERE id > 1 ';
		} else {
			$s .= 'WHERE id = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetEnemiesHtml( $id = false, $category = false ) {
		$args = array();
		$s = 'SELECT html FROM Enemies ';
		if ( $id !== false ) {
			$s .= 'WHERE gameId = :searchId ';
			$args['searchId'] = $id;
		} elseif ( $category !== false ) {
			$s .= 'WHERE category = :searchId AND id > 0 ';
			$args['searchId'] = $category;
		} else {
			$s .= 'WHERE id > 0 ';
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetItemsHtml( $id = false, $category = false, $icon = false ) {
		$args = array();
		$s = 'SELECT html FROM Items ';
		if ( $id !== false ) {
			$s .= 'WHERE gameId = :searchId ';
			$args['searchId'] = $id;
		} elseif ( $icon !== false ) {
			$s .= 'WHERE icon = :searchId ';
			$args['searchId'] = $icon;
		} elseif ( $category !== false ) {
			$s .= 'WHERE category = :searchId ';
			$args['searchId'] = $category;
		} else {
			//$s .= 'WHERE id > 0 ';
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetLocationsHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM Locations ';
		if ( $id === false ) {
			$s .= 'WHERE category > 0 ';
		} else {
			$s .= 'WHERE gameId = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetRecordsHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM Records ';
		if ( $id === false ) {
			//$s .= 'WHERE id > 0 ';
		} else {
			$s .= 'WHERE id = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetSettingsHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM Settings ';
		if ( $id === false ) {
			//$s .= 'WHERE id > 0 ';
		} else {
			$s .= 'WHERE id = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetGradeShopHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM GradeShop ';
		if ( $id === false ) {
			$s .= 'WHERE cost > 0 ';
		} else {
			$s .= 'WHERE gameId = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetNecropolisHtml( $enemies = false, $map = false ) {
		$args = array();
		$s = 'SELECT html';
		if ( $enemies === true ) { 
			$s .= 'Enemies AS html';
		}
		$s .= ' FROM NecropolisFloors ';
		if ( $map !== false ) {
			$s .= 'WHERE floorName = :searchMap ';
			$args['searchMap'] = $map;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetStrategySetHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM StrategySet ';
		if ( $id === false ) {
			//$s .= 'WHERE id > 0 ';
		} else {
			$s .= 'WHERE id = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetStrategyOptionsHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM StrategyOptions ';
		if ( $id === false ) {
			//$s .= 'WHERE id > 0 ';
		} else {
			$s .= 'WHERE gameId = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
}
?>