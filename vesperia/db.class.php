<?php
require_once 'scenario.class.php';
require_once 'skitLine.class.php';
require_once 'stringDic.class.php';
require_once 'util.php';

class db {
	var $conn;
	
	function __construct( $connstr, $username, $password ) {
		$this->conn = new PDO( $connstr, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'") );
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$this->conn->beginTransaction();
	}
	
	function __destruct() {
		$this->conn->rollBack();
	}
	
	function ExecuteAndReturnFirstValueAsInteger( $s, $args ) {
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		if ( $r = $stmt->fetch() ) {
			return (int)$r[0];
		}
		return 0;
	}

	function FoundRows() {
		$s = 'SELECT FOUND_ROWS()';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute();
		
		return $stmt->fetchColumn();
	}
	
	function GetScenarioIndex( $type ) {
		$args = array();
		$s = 'SELECT id, sceneGroup, parent, episodeId, descriptionJ, descriptionE, changeStatus FROM ScenarioMeta ';
		$s .= 'WHERE type = :type ';
		$args['type'] = $type;
		$s .= 'ORDER BY id ASC'; // should be "sceneGroup ASC, id ASC" but due to how this table is generated gives the same result
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$sce = array();
		while( $r = $stmt->fetch() ) {
			$sce[] = new scenarioMeta( $r['id'], $type, $r['sceneGroup'], $r['parent'], $r['episodeId'], $r['descriptionJ'], $r['descriptionE'], (int)$r['changeStatus'] );
		}
		return $sce;
	}
	
	function GetScenarioMetaFromEpisodeId( $episodeId ) {
		$args = array();
		$s = 'SELECT id, type, sceneGroup, parent, descriptionJ, descriptionE, changeStatus FROM ScenarioMeta ';
		$s .= 'WHERE episodeId = :episodeId';
		$args['episodeId'] = $episodeId;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		if( $r = $stmt->fetch() ) {
			return new scenarioMeta( $r['id'], $r['type'], $r['sceneGroup'], $r['parent'], $episodeId, $r['descriptionJ'], $r['descriptionE'], (int)$r['changeStatus'] );
		}
		return null;
	}
	
	function GetScenarioMetaGroupRange( $type, $groupBegin, $groupEnd ) {
		$args = array();
		$s = 'SELECT id, type, sceneGroup, parent, episodeId, descriptionJ, descriptionE, changeStatus FROM ScenarioMeta ';
		$s .= 'WHERE type = :type AND sceneGroup >= :groupBegin AND sceneGroup <= :groupEnd ';
		$args['type'] = $type;
		$args['groupBegin'] = $groupBegin;
		$args['groupEnd'] = $groupEnd;
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$sce = array();
		while( $r = $stmt->fetch() ) {
			$sce[] = new scenarioMeta( $r['id'], $r['type'], $r['sceneGroup'], $r['parent'], $r['episodeId'], $r['descriptionJ'], $r['descriptionE'], (int)$r['changeStatus'] );
		}
		return $sce;
	}
	
	function GetScenario( $episodeId ) {
		$args = array();
		$s = 'SELECT type, jpName, jpText, enName, enText, changeStatus FROM ScenarioDat ';
		$s .= 'WHERE episodeId = :searchId ';
		$args['searchId'] = $episodeId;
		$s .= 'ORDER BY displayOrder ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$sce = array();
		while( $r = $stmt->fetch() ) {
			$sce[] = new scenario( $episodeId, (int)$r['type'], $r['jpName'], $r['jpText'], $r['enName'], $r['enText'], (int)$r['changeStatus'] );
		}
		return $sce;
	}
	
	function AppendSearchArgs( $compare, &$s, &$args, $query ) {
		// this would be proper but doesn't work well with japanese, unfortunately...
		//$s .= 'WHERE MATCH(jpSearchKanji, jpSearchFuri, enSearch) AGAINST (:search) ';
		//$args['search'] = $query;
		if ( WantsJp($compare) ) {
			$s .= 'WHERE jpSearchKanji LIKE :searchK ';
			$s .= 'OR jpSearchFuri LIKE :searchF ';
			$args['searchK'] = '%'.$query.'%';
			$args['searchF'] = '%'.$query.'%';
		}
		if ( WantsEn($compare) ) {
			if ( WantsJp($compare) ) {
				$s .= 'OR';
			} else {
				$s .= 'WHERE';
			}
			$s .= ' enSearch LIKE :searchE ';
			$args['searchE'] = '%'.$query.'%';
		}
	}
	
	function SearchScenario( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT episodeId, type, jpName, jpText, enName, enText, changeStatus FROM ScenarioDat ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		$s .= 'ORDER BY episodeId ASC, displayOrder ASC ';
		$s .= 'LIMIT :offset, :rowcnt';
		$args['offset'] = $offset;
		$args['rowcnt'] = $rowcount;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$sce = array();
		while( $r = $stmt->fetch() ) {
			$sce[] = new scenario( $r['episodeId'], (int)$r['type'], $r['jpName'], $r['jpText'], $r['enName'], $r['enText'], (int)$r['changeStatus'] );
		}
		return $sce;
	}

	function SearchScenarioCount( $compare, $query ) {
		$args = array();
		$s = 'SELECT COUNT(0) FROM ScenarioDat ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		return $this->ExecuteAndReturnFirstValueAsInteger( $s, $args );
	}

	function GetSkit( $skitId ) {
		$args = array();
		$s = 'SELECT jpChar, enChar, jpText, enText, changeStatus FROM SkitText ';
		$s .= 'WHERE skitId = :searchId ';
		$args['searchId'] = $skitId;
		$s .= 'ORDER BY displayOrder ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$lines = array();
		while( $r = $stmt->fetch() ) {
			$lines[] = new skitLine( $skitId, $r['jpChar'], $r['jpText'], $r['enChar'], $r['enText'], (int)$r['changeStatus'] );
		}
		return $lines;
	}
	
	function SearchSkit( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT skitId, jpChar, enChar, jpText, enText, changeStatus FROM SkitText ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		$s .= 'ORDER BY skitId ASC, displayOrder ASC ';
		$s .= 'LIMIT :offset, :rowcnt';
		$args['offset'] = $offset;
		$args['rowcnt'] = $rowcount;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$lines = array();
		while( $r = $stmt->fetch() ) {
			$lines[] = new skitLine( $r['skitId'], $r['jpChar'], $r['jpText'], $r['enChar'], $r['enText'], (int)$r['changeStatus'] );
		}
		return $lines;
	}
	
	function SearchSkitCount( $compare, $query ) {
		$args = array();
		$s = 'SELECT COUNT(0) FROM SkitText ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		return $this->ExecuteAndReturnFirstValueAsInteger( $s, $args );
	}
	
	function GetSkitIndex() {
		$args = array();
		$s = 'SELECT skitId, categoryStr, jpName, enName, charHtml, changeStatus FROM SkitMeta ';
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$skit = array();
		while( $r = $stmt->fetch() ) {
			$skit[] = new skitMetaForIndex( $r['skitId'], $r['categoryStr'], $r['jpName'], $r['enName'], $r['charHtml'], (int)$r['changeStatus'] );
		}
		return $skit;
	}
	
	function GetSkitMeta( $skitId ) {
		$args = array();
		$s = 'SELECT category, characterBitmask, jpName, enName, jpCond, enCond, changeStatus FROM SkitMeta ';
		$s .= 'WHERE skitId = :searchId ';
		$args['searchId'] = $skitId;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		if ( $r = $stmt->fetch() ) {
			$skit = new skitMeta( $r['category'], $r['characterBitmask'], $r['jpName'], $r['enName'], $r['jpCond'], $r['enCond'], (int)$r['changeStatus'] );
			return $skit;
		}
		return null;
	}
	
	function AppendSearchArgsNoKanjiFuriName( $compare, &$s, &$args, $query ) {
		if ( WantsJp($compare) ) {
			$s .= 'WHERE jpName LIKE :searchJ ';
			$args['searchJ'] = '%'.$query.'%';
		}
		if ( WantsEn($compare) ) {
			if ( WantsJp($compare) ) {
				$s .= 'OR';
			} else {
				$s .= 'WHERE';
			}
			$s .= ' enName LIKE :searchE ';
			$args['searchE'] = '%'.$query.'%';
		}
	}

	function SearchSkitNamesHtml( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT skitId, categoryStr, jpName, enName, charHtml, changeStatus FROM SkitMeta ';
		$this->AppendSearchArgsNoKanjiFuriName( $compare, $s, $args, $query );
		$s .= 'ORDER BY id ASC ';
		$s .= 'LIMIT :offset, :rowcnt';
		$args['offset'] = $offset;
		$args['rowcnt'] = $rowcount;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$skit = array();
		while( $r = $stmt->fetch() ) {
			$skit[] = new skitMetaForIndex( $r['skitId'], $r['categoryStr'], $r['jpName'], $r['enName'], $r['charHtml'], (int)$r['changeStatus'] );
		}
		return $skit;
	}

	function SearchSkitNamesCount( $compare, $query ) {
		$args = array();
		$s = 'SELECT COUNT(0) FROM SkitMeta ';
		$this->AppendSearchArgsNoKanjiFuriName( $compare, $s, $args, $query );
		return $this->ExecuteAndReturnFirstValueAsInteger( $s, $args );
	}
	
	function SearchStringDic( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT gameId, jpText, enText FROM StringDic ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		$s .= 'ORDER BY id ASC ';
		$s .= 'LIMIT :offset, :rowcnt';
		$args['offset'] = $offset;
		$args['rowcnt'] = $rowcount;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$entries = array();
		while( $r = $stmt->fetch() ) {
			$entries[] = new stringDicEntry( $r['gameId'], $r['jpText'], $r['enText'] );
		}
		return $entries;
	}
	
	function SearchStringDicCount( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT COUNT(0) FROM StringDic ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		return $this->ExecuteAndReturnFirstValueAsInteger( $s, $args );
	}
	
	function GetHtmlColumnPostfix( $compare ) {
		if ( $compare === '1' ) { return 'J'; }
		if ( $compare === '2' ) { return 'E'; }
		if ( $compare === 'c1' ) { return 'CJ'; }
		if ( $compare === 'c2' ) { return 'CE'; }
		die();
	}
	
	function GetArtesHtml( $compare, $id, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Artes ';
		if ( $id === false ) {
			if ( $filtered ) {
				$s .= 'WHERE ( ( Artes.type > 0 AND Artes.type <= 11 ) OR Artes.type = 13 ) AND ( Artes.character > 0 AND Artes.character <= 9 ) ';
			}
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

	function SearchArtes( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Artes ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		$s .= 'ORDER BY id ASC ';
		$s .= 'LIMIT :offset, :rowcnt';
		$args['offset'] = $offset;
		$args['rowcnt'] = $rowcount;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$artes = array();
		while( $r = $stmt->fetch() ) {
			$a = $r['html'];
			$artes[] = $a;
		}
		return $artes;
	}

	function SearchArtesCount( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT COUNT(0) FROM Artes ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		return $this->ExecuteAndReturnFirstValueAsInteger( $s, $args );
	}

	function GetArtesByCharacterHtml( $compare, $character, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Artes ';
		$s .= 'WHERE ';
		if ( $filtered ) {
			$s .= '( ( Artes.type > 0 AND Artes.type <= 11 ) OR Artes.type = 13 ) AND ';
		}
		$s .= '( Artes.character = :searchChar ) ';
		$args['searchChar'] = $character;
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetSkillsHtml( $compare, $id, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Skills ';
		if ( $id === false ) {
			if ( $filtered ) {
				$s .= 'WHERE learnableBy > 0 ';
			}
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

	function SearchSkills( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Skills ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		$s .= 'ORDER BY id ASC ';
		$s .= 'LIMIT :offset, :rowcnt';
		$args['offset'] = $offset;
		$args['rowcnt'] = $rowcount;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}

	function SearchSkillsCount( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT COUNT(0) FROM Skills ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		return $this->ExecuteAndReturnFirstValueAsInteger( $s, $args );
	}

	function GetSkillsByCharacterHtml( $compare, $character, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Skills ';
		if ( $filtered ) {
			$s .= 'WHERE ( learnableBy & ( 1 << ( :searchChar - 1 ) ) ) > 0 ';
		}
		$args['searchChar'] = $character;
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetRecipesHtml( $compare, $id, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Recipes ';
		if ( $id === false ) {
			if ( $filtered ) {
				$s .= 'WHERE id > 0 ';
			}
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

	function SearchRecipes( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Recipes ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		$s .= 'ORDER BY id ASC ';
		$s .= 'LIMIT :offset, :rowcnt';
		$args['offset'] = $offset;
		$args['rowcnt'] = $rowcount;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}

	function SearchRecipesCount( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT COUNT(0) FROM Recipes ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		return $this->ExecuteAndReturnFirstValueAsInteger( $s, $args );
	}

	function GetShopsHtml( $compare, $id, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Shops ';
		if ( $id === false ) {
			if ( $filtered ) {
				$s .= 'WHERE gameId > 1 ';
			}
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
	
	function GetTitlesHtml( $compare, $id, $filtered ) {
		// gameId = 67 is an Estelle Title with 0 points that still shows up in-game in PS3
		// consider checks for this a hack
		
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Titles ';
		if ( $id === false ) {
			if ( $filtered ) {
				$s .= 'WHERE gameId > 0 AND ( points > 0 OR gameId = 67 )';
			}
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

	function SearchTitles( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Titles ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		$s .= 'ORDER BY id ASC ';
		$s .= 'LIMIT :offset, :rowcnt';
		$args['offset'] = $offset;
		$args['rowcnt'] = $rowcount;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}

	function SearchTitlesCount( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT COUNT(0) FROM Titles ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		return $this->ExecuteAndReturnFirstValueAsInteger( $s, $args );
	}

	function GetTitlesByCharacterHtml( $compare, $character, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Titles ';
		$s .= 'WHERE Titles.character = :searchChar ';
		$args['searchChar'] = $character;
		if ( $filtered ) {
			$s .= 'AND ( points > 0 OR gameId = 67 ) ';
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
	
	function GetSynopsisHtml( $compare, $id, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Synopsis ';
		if ( $id === false ) {
			if ( $filtered ) {
				$s .= 'WHERE storyMax > 0 ';
			}
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

	function SearchSynopsis( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Synopsis ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		$s .= 'ORDER BY id ASC ';
		$s .= 'LIMIT :offset, :rowcnt';
		$args['offset'] = $offset;
		$args['rowcnt'] = $rowcount;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}

	function SearchSynopsisCount( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT COUNT(0) FROM Synopsis ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		return $this->ExecuteAndReturnFirstValueAsInteger( $s, $args );
	}

	function GetBattleBookHtml( $compare, $id, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM BattleBook ';
		if ( $id === false ) {
			if ( $filtered ) {
				$s .= 'WHERE id > 1 ';
			}
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

	function SearchBattleBook( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM BattleBook ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		$s .= 'ORDER BY id ASC ';
		$s .= 'LIMIT :offset, :rowcnt';
		$args['offset'] = $offset;
		$args['rowcnt'] = $rowcount;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}

	function SearchBattleBookCount( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT COUNT(0) FROM BattleBook ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		return $this->ExecuteAndReturnFirstValueAsInteger( $s, $args );
	}

	function GetEnemiesHtml( $compare, $id, $category, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Enemies ';
		if ( $id !== false ) {
			$s .= 'WHERE gameId = :searchId ';
			$args['searchId'] = $id;
		} elseif ( $category !== false ) {
			$s .= 'WHERE category = :searchId ';
			$args['searchId'] = $category;
			if ( $filtered ) {
				$s .= 'AND id > 0 ';
			}
		} else {
			if ( $filtered ) {
				$s .= 'WHERE id > 0 ';
			}
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

	function SearchEnemies( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Enemies ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		$s .= 'ORDER BY id ASC ';
		$s .= 'LIMIT :offset, :rowcnt';
		$args['offset'] = $offset;
		$args['rowcnt'] = $rowcount;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}

	function SearchEnemiesCount( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT COUNT(0) FROM Enemies ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		return $this->ExecuteAndReturnFirstValueAsInteger( $s, $args );
	}

	function GetItemsHtml( $compare, $id, $category, $icon, $rowOffset, $rowCount, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Items ';
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
			if ( $filtered ) {
				$s .= 'WHERE inCollectorsBook != 0 ';
			}
		}
		$s .= 'ORDER BY id ASC LIMIT '.$rowOffset.','.$rowCount;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}

	function GetItemsCount( $id, $category, $icon, $filtered ) {
		$args = array();
		$s = 'SELECT COUNT(1) AS cnt FROM Items ';
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
			if ( $filtered ) {
				$s .= 'WHERE inCollectorsBook != 0 ';
			}
		}
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		if( $r = $stmt->fetch() ) {
			return (int)$r['cnt'];
		}
		return -1;
	}

	function SearchItems( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Items ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		$s .= 'ORDER BY id ASC ';
		$s .= 'LIMIT :offset, :rowcnt';
		$args['offset'] = $offset;
		$args['rowcnt'] = $rowcount;
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}

	function SearchItemsCount( $compare, $query, $offset = 0, $rowcount = PHP_INT_MAX ) {
		$args = array();
		$s = 'SELECT COUNT(0) FROM Items ';
		$this->AppendSearchArgs( $compare, $s, $args, $query );
		return $this->ExecuteAndReturnFirstValueAsInteger( $s, $args );
	}

	function GetLocationsHtml( $compare, $id, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Locations ';
		if ( $id === false ) {
			if ( $filtered ) {
				$s .= 'WHERE category > 0 ';
			}
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
	
	function GetSearchPointsHtml( $compare, $id, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM SearchPoints ';
		if ( $id === false ) {
			if ( $filtered ) {
				$s .= 'WHERE displayId >= 0 ';
			}
		} else {
			$s .= 'WHERE gameId = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY displayId ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$items = array();
		while( $r = $stmt->fetch() ) {
			$items[] = $r['html'];
		}
		return $items;
	}
	
	function GetRecordsHtml( $compare, $id, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Records ';
		if ( $id === false ) {
			if ( $filtered ) {
				//$s .= 'WHERE id > 0 ';
			}
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
	
	function GetSettingsHtml( $compare, $id, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Settings ';
		if ( $id === false ) {
			if ( $filtered ) {
				//$s .= 'WHERE id > 0 ';
			}
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
	
	function GetGradeShopHtml( $compare, $id, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM GradeShop ';
		if ( $id === false ) {
			if ( $filtered ) {
				$s .= 'WHERE cost > 0 ';
			}
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
	
	function GetTrophiesHtml( $compare, $id = false ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM Trophies ';
		if ( $id !== false ) {
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
	
	function GetNecropolisHtml( $compare, $enemies = false, $map = false ) {
		$args = array();
		$s = 'SELECT ';
		if ( $enemies === true ) { 
			$s .= 'htmlEnemy'.$this->GetHtmlColumnPostfix($compare).' as html';
		} else {
			$s .= 'html'.$this->GetHtmlColumnPostfix($compare).' as html';
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
	
	function GetStrategySetHtml( $compare, $id, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM StrategySet ';
		if ( $id === false ) {
			if ( $filtered ) {
				//$s .= 'WHERE id > 0 ';
			}
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
	
	function GetStrategyOptionsHtml( $compare, $id, $filtered ) {
		$args = array();
		$s = 'SELECT html'.$this->GetHtmlColumnPostfix($compare).' as html FROM StrategyOptions ';
		if ( $id === false ) {
			if ( $filtered ) {
				//$s .= 'WHERE id > 0 ';
			}
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