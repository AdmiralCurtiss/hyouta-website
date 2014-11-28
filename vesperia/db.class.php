<?php
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
	
	function GetArtesHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM Artes ';
		if ( $id === false ) {
			$s .= 'WHERE ( ( Artes.type > 0 AND Artes.type <= 11 ) OR Artes.type = 13 ) AND Artes.character <= 9 ';
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