<?php
	//page generation time code
		$time = explode(' ', microtime());
		$pagegen_start_time = $time[1] + $time[0];
	//page generation time code end

header('Content-Type: text/html; charset=UTF-8');

require_once 'db.class.php';
require_once 'scenario.class.php';
require_once 'skitLine.class.php';
require_once 'stringDic.class.php';
require_once 'header.php';

$maintenance_mode = false;
if ( $maintenance_mode ) {
	echo '<html>';
	print_header();
	echo '<body>';
	echo '<h1>Site is being updated, please check back in a minute!</h1>';
	echo '</body>';
	echo '</html>';
	die();
}

$version = 'ps3';
$allowVersionSelect = false;
if ( $allowVersionSelect && isset($_GET['version']) ) {
	if ( $_GET['version'] == 'ps3' || $_GET['version'] == '360' ) {
		$version = $_GET['version'];
	}
}

include '../credentials.php';
$db = new db( $__db_connstr_vesperia__[$version], $__db_username_vesperia__, $__db_password_vesperia__ );

$section = 'index';
if ( isset($_GET['section']) ) {
	$section = $_GET['section'];
}
$id = false;
if ( isset($_GET['id']) ) { $id = (int)$_GET['id']; }
$category = false;
if ( isset($_GET['category']) ) { $category = (int)$_GET['category']; }
$icon = false;
if ( isset($_GET['icon']) ) { $icon = (int)$_GET['icon']; }
$character = false;
if ( isset($_GET['character']) ) { $character = (int)$_GET['character']; }
$name = '';
if ( isset($_GET['name']) ) { $name = $_GET['name']; }
$query = '';
if ( isset($_GET['query']) ) { $query = $_GET['query']; }
$page = 1;
if ( isset($_GET['page']) ) { $page = (int)$_GET['page']; if ( $page < 1 ) { $page = 1; } }
$markVersionDifferences = false;
if ( isset($_GET['diff']) && $_GET['diff'] === 'true' ) { $markVersionDifferences = true; }
$showScenarioJumper = true;
if ( isset($_GET['jump']) && $_GET['jump'] === 'false' ) { $showScenarioJumper = false; }
$perPage = 0;
if ( isset($_GET['perpage']) ) { $perPage = (int)$_GET['perpage']; }

echo '<html>';

function shouldSearch( $begin, $end, $offset, $count ) {
	// need to check if range [begin, end[ includes any of [offset, (offset+count)[, so, uh...
	return max( $begin, $offset ) - ( min( $end, ($offset+$count) ) - 1 ) <= 0;
}

function paginate( $pageNum, $itemsPerPage, $itemsTotal, $baseLink ) {
	$totalPages = (int)ceil($itemsTotal / $itemsPerPage);

	if ( $totalPages > 1 ) {
		$pageString = '';
		if ( $pageNum > 1 ) {
			$pageString .= '<a href="'.$baseLink.'&page='.( $pageNum - 1 ).'&perpage='.$itemsPerPage.'">Previous Page</a> - ';
		}
		$pageString .= 'Page '.$pageNum.' of '.$totalPages;
		if ( $pageNum < $totalPages ) {
			$pageString .= ' - <a href="'.$baseLink.'&page='.( $pageNum + 1 ).'&perpage='.$itemsPerPage.'">Next Page</a>';
		}
		echo '<p>'.$pageString.'</p>';
	}
}

if ( $section === 'search' ) {
	print_top( $version, $allowVersionSelect, 'Search', $query );
	if ( $perPage <=   0 ) { $perPage =  50; }
	if ( $perPage >  500 ) { $perPage = 500; }
	$globalOffset = ( $page - 1 ) * $perPage;
	$entriesToGo = $perPage;

	if ( strlen( $query ) >= 2 ) {
		echo '<div class="scenario-content">';
		echo '<div class="storyBox">';

		$totalOffsetBegin = $globalOffset;
		$totalOffsetEnd = $totalOffsetBegin + $perPage;

		$totalSkitNameCount  = $db->SearchSkitNamesCount( $query );
		$totalScenarioCount  = $db->SearchScenarioCount( $query );
		$totalSkitCount      = $db->SearchSkitCount( $query );
		$totalStringDicCount = $db->SearchStringDicCount( $query );

		$indexOffsetSkitName  = 0;
		$indexOffsetScenario  = $indexOffsetSkitName  + $totalSkitNameCount;
		$indexOffsetSkit      = $indexOffsetScenario  + $totalScenarioCount;
		$indexOffsetStringDic = $indexOffsetSkit      + $totalSkitCount;
		$totalFoundEntries    = $indexOffsetStringDic + $totalStringDicCount;

		paginate( $page, $perPage, $totalFoundEntries, '?version='.$version.'&section=search&query='.urlencode($query) );

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetSkitName, $totalSkitNameCount ) ) {
			$skits = $db->SearchSkitNamesHtml( $query, max( 0, $totalOffsetBegin - $indexOffsetSkitName ), $entriesToGo );
			$skitCount = $db->FoundRows();
			echo '<div class="scenario-previous-next">Skits</div>';
			echo '<table>';
			foreach ( $skits as $s ) {
				$s->RenderTableRow( $version, $markVersionDifferences );
				--$entriesToGo;
			}
			echo '</table>';
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetScenario, $totalScenarioCount ) ) {
			$sce = $db->SearchScenario( $query, max( 0, $totalOffsetBegin - $indexOffsetScenario ), $entriesToGo );
			$sceRows = $db->FoundRows();
			$previousId = '';
			foreach ( $sce as $s ) {
				if ( $previousId != $s->episodeId ) {
					echo '<div class="scenario-previous-next"><a href="?version='.$version.'&section=scenario&name='.$s->episodeId.'">'.$s->episodeId.'</a></div>';
					$previousId = $s->episodeId;
				}
				$s->Render( $markVersionDifferences );
				--$entriesToGo;
			}
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetSkit, $totalSkitCount ) ) {
			$skit = $db->SearchSkit( $query, max( 0, $totalOffsetBegin - $indexOffsetSkit ), $entriesToGo );
			$skitRows = $db->FoundRows();
			$previousId = '';
			foreach ( $skit as $s ) {
				if ( $previousId != $s->skitId ) {
					echo '<div class="scenario-previous-next"><a href="?version='.$version.'&section=skit&name='.$s->skitId.'">'.$s->skitId.'</a></div>';
					$previousId = $s->skitId;
				}
				$s->Render( $markVersionDifferences );
				--$entriesToGo;
			}
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetStringDic, $totalStringDicCount ) ) {
			$entries = $db->SearchStringDic( $query, max( 0, $totalOffsetBegin - $indexOffsetStringDic ), $entriesToGo );
			$stringRows = $db->FoundRows();
			if ( !empty($entries) ) {
				echo '<div class="scenario-previous-next">Strings</div>';
				foreach ( $entries as $e ) {
					$e->Render( $markVersionDifferences );
					--$entriesToGo;
				}
			}
		}

		paginate( $page, $perPage, $totalFoundEntries, '?version='.$version.'&section=search&query='.urlencode($query) );

		echo '</div>';
		echo '</div>';
	}
} elseif ( $section === 'scenario' ) {
	print_top( $version, $allowVersionSelect, 'Scenario' );
	
	$scenarioMetadata = null;
	if ( $showScenarioJumper ) {
		$thisScenarioMeta = $db->GetScenarioMetaFromEpisodeId( $name );
		if ( $thisScenarioMeta !== null ) {
			$scenarioMetadata = $db->GetScenarioMetaGroupRange( $thisScenarioMeta->type, $thisScenarioMeta->sceneGroup - 1, $thisScenarioMeta->sceneGroup + 1 );
			ScenarioMeta::RenderIndex( $version, $scenarioMetadata, $markVersionDifferences, $name );
		}
	}
	
	echo '<div class="scenario-content">';
	
	if ( $scenarioMetadata !== null ) {
		ScenarioMeta::RenderPreviousNext( $version, $scenarioMetadata, $name, true, $allowVersionSelect, $markVersionDifferences );
	}
	
	$sce = $db->GetScenario( $name );

	echo '<div class="storyBox">';
	foreach ( $sce as $s ) {
		$s->Render( $markVersionDifferences );
	}
	echo '</div>';
	
	if ( $scenarioMetadata !== null ) {
		ScenarioMeta::RenderPreviousNext( $version, $scenarioMetadata, $name, false, $allowVersionSelect, $markVersionDifferences );
	}
	
	echo '</div>';
	
} elseif ( $section === 'skit' ) {
	print_top( $version, $allowVersionSelect, 'Skit' );
	
	if ( $showScenarioJumper ) {
		$thisScenarioMeta = $db->GetScenarioMetaFromEpisodeId( $name );
		if ( $thisScenarioMeta !== null ) {
			$scenarioMetadata = $db->GetScenarioMetaGroupRange( $thisScenarioMeta->type, $thisScenarioMeta->sceneGroup - 1, $thisScenarioMeta->sceneGroup + 1 );
			ScenarioMeta::RenderIndex( $version, $scenarioMetadata, $markVersionDifferences, $name );
		}
	}
	
	$lines = $db->GetSkit( $name );
	$meta = $db->GetSkitMeta( $name );
	
	echo '<div class="scenario-content">';
	
	if ( $allowVersionSelect ) {
		echo '<div>';
		echo '<a href="?version=360&section=skit&name='.$name.'&diff=true">360</a>';
		echo ' ';
		echo '<a href="?version=ps3&section=skit&name='.$name.'&diff=true">PS3</a>';
		echo '</div>';
	}
	
	echo '<div';
	if ( $markVersionDifferences ) {
		echo ' class="changeStatusIndex'.$meta->changeStatus.'"';
	}
	echo '>';
	if ( $meta !== null ) {
		echo '<div class="skit-name">';
		echo $meta->jpName;
		echo '</div>';
		echo '<div class="skit-name">';
		echo $meta->enName;
		echo '</div>';
	}
	echo '</div>';
	
	echo '<div class="storyBox">';
	foreach ( $lines as $s ) {
		$s->Render( $markVersionDifferences );
	}
	echo '</div>';
	
	echo '</div>';
	
} elseif ( $section === 'scenario-index' ) {
	print_top( $version, $allowVersionSelect, 'Story Index' );
	$scenarioMetadata = $db->GetScenarioIndex( 1 );
	ScenarioMeta::RenderIndex( $version, $scenarioMetadata, $markVersionDifferences );
} elseif ( $section === 'sidequest-index' ) {
	print_top( $version, $allowVersionSelect, 'Sidequest Index' );
	$scenarioMetadata = $db->GetScenarioIndex( 2 );
	ScenarioMeta::RenderIndex( $version, $scenarioMetadata, $markVersionDifferences );
} elseif ( $section === 'skit-index' ) {
	print_top( $version, $allowVersionSelect, 'Skit Index' );
	echo '<table>';
	
	$skits = $db->GetSkitIndex();
	foreach ( $skits as $s ) {
		$s->RenderTableRow( $version, $markVersionDifferences );
	}
	
	echo '</table>';
} elseif ( $section === 'artes' ) {
	print_top( $version, $allowVersionSelect, 'Artes' );
	print_character_select( $version, $section );
	echo '<table>';
	
	if ( $character === false ) {
		$artes = $db->GetArtesHtml( $id );
	} else {
		$artes = $db->GetArtesByCharacterHtml( $character );
	}
	$first = true;
	foreach ( $artes as $a ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="5"><hr></td></tr>';
		}
		echo $a;
	}
	
	echo '</table>';
} elseif ( $section === 'skills' ) {
	print_top( $version, $allowVersionSelect, 'Skills' );
	print_character_select( $version, $section );
	echo '<table>';
	
	if ( $character === false ) {
		$skills = $db->GetSkillsHtml( $id );
	} else {
		$skills = $db->GetSkillsByCharacterHtml( $character );
	}
	$first = true;
	foreach ( $skills as $a ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="4"><hr></td></tr>';
		}
		echo $a;
	}
	
	echo '</table>';
} elseif ( $section === 'recipes' ) {
	print_top( $version, $allowVersionSelect, 'Recipes' );
	echo '<table>';
	
	$items = $db->GetRecipesHtml( $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="4"><hr></td></tr>';
		}
		echo '<tr>';
		echo $item;
		echo '</tr>';
	}
	
	echo '</table>';
} elseif ( $section === 'shops' ) {
	print_top( $version, $allowVersionSelect, 'Shops' );
	echo '<table>';
	
	$items = $db->GetShopsHtml( $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="6"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'titles' ) {
	print_top( $version, $allowVersionSelect, 'Titles' );
	print_character_select( $version, $section );
	echo '<table>';
	
	if ( $character === false ) {
		$items = $db->GetTitlesHtml( $id );
	} else {
		$items = $db->GetTitlesByCharacterHtml( $character );
	}
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="4"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'synopsis' ) {
	print_top( $version, $allowVersionSelect, 'Synopsis' );
	
	$items = $db->GetSynopsisHtml( $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<hr>';
		}
		echo $item;
	}
	
} elseif ( $section === 'battlebook' ) {
	print_top( $version, $allowVersionSelect, 'Battle Book' );
	echo '<table>';
	
	$items = $db->GetBattleBookHtml( $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="2"><hr></td></tr>';
		}
		echo '<tr>';
		echo $item;
		echo '</tr>';
	}
	
	echo '</table>';
} elseif ( $section === 'enemies' ) {
	print_top( $version, $allowVersionSelect, 'Enemies' );
	echo '<table>';
	
	$items = $db->GetEnemiesHtml( $id, $category );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="7"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'items' ) {
	print_top( $version, $allowVersionSelect, 'Items' );

	if ( $perPage <=   0 ) { $perPage = 120; }
	if ( $perPage >  500 ) { $perPage = 500; }

	$itemcount = $db->GetItemsCount( $id, $category, $icon );
	$offset = ($page - 1) * $perPage;
	$items = $db->GetItemsHtml( $id, $category, $icon, $offset, $perPage );

	$baselink = '?version='.$version.'&section=items';
	if ( $category !== false ) { $baselink .= '&category='.$category; }
	if ( $icon !== false ) { $baselink .= '&icon='.$icon; }

	paginate( $page, $perPage, $itemcount, $baselink );

	echo '<table>';
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="5"><hr></td></tr>';
		}
		echo $item;
	}
	echo '</table>';

	paginate( $page, $perPage, $itemcount, $baselink );
} elseif ( $section === 'locations' ) {
	print_top( $version, $allowVersionSelect, 'Locations' );
	echo '<table>';
	
	$items = $db->GetLocationsHtml( $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="3"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'searchpoint' ) {
	print_top( $version, $allowVersionSelect, 'Search Points' );
	echo '<img src="etc/'.$version.'/SearchPoint.jpg">';
	echo '<hr>';
	echo '<table>';
	
	$items = $db->GetSearchPointsHtml( $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="5"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'records' ) {
	print_top( $version, $allowVersionSelect, 'Records' );
	echo '<table>';
	
	$items = $db->GetRecordsHtml( $id );
	foreach ( $items as $item ) {
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'settings' ) {
	print_top( $version, $allowVersionSelect, 'Settings' );
	echo '<table>';
	
	$items = $db->GetSettingsHtml( $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="5"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'gradeshop' ) {
	print_top( $version, $allowVersionSelect, 'Grade Shop' );
	echo '<table>';
	
	$items = $db->GetGradeShopHtml( $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="3"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'trophies' ) {
	print_top( $version, $allowVersionSelect, 'Trophies' );
	echo '<table>';
	
	$items = $db->GetTrophiesHtml( $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="3"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'strategy' ) {
	print_top( $version, $allowVersionSelect, 'Strategy' );
	echo '<table>';
	
	$items = $db->GetStrategySetHtml( $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="10"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
	echo '<hr>';
	echo '<table>';
	
	$items = $db->GetStrategyOptionsHtml( $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="4"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'necropolis' ) {
	print_top( $version, $allowVersionSelect, 'Necropolis of Nostalgia' );
	
	$stratumNames = array(
		'A' => 'Firmament',
		'B' => 'Existence',
		'C' => 'Hegemony',
		'D' => 'Fauna',
		'E' => 'Fatality',
		'F' => 'Abysm'
	);
	
	$map = false;
	if ( isset($_GET['map']) ) {
		$map = $_GET['map'];
		$map_letter = substr($map, 0, 1);
		$map_letter_digit = ord($map_letter) - ord('A');
		$map_number = (int)substr($map, 1);
		$map = 'BTL_XTM_AREA_'.( str_pad($map_letter_digit * 10 + $map_number, 2, '0', STR_PAD_LEFT) );
	}
	$enemies = false;
	if ( isset($_GET['enemies']) ) {
		$enemies = $_GET['enemies'] === 'true';
	}
	
	if ( $map === false ) {
		// output map list
		echo '<div class="necropolis-select">';
		echo '<table>';
		for ( $letter = 'A'; $letter <= 'F'; ++$letter ) {
			echo '<tr>';
			echo '<td>';
			echo $stratumNames[$letter];
			echo '</td>';
			for ( $number = 1; $number <= 10; ++$number ) {
				echo '<td>';
				echo '<a href="?version='.$version.'&section=necropolis&map='.$letter.$number.'">'.$number.'</a>';
				echo '</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';
	} else {
		$items = $db->GetNecropolisHtml( $enemies, $map );
		$first = true;
		foreach ( $items as $item ) {
			if ( $first === true ) { $first = false; } else {
				echo '<hr>';
			}
			
			echo '<div id="'.$map_letter.$map_number.'">';
			echo '<table class="necropolisfloor"><tr><th colspan="6"><div class="itemname" style="text-align: center;">'.$stratumNames[$map_letter].' '.$map_number.'F ('.$map_letter.'-'.$map_number.')</div></th></tr>';
			echo '<tr><th colspan="6">';
			if ( $enemies === true ) {
				echo '<a href="?version='.$version.'&section=necropolis&map='.$map_letter.$map_number.'">General Info</a>';
			} else {
				echo 'General Info';
			}
			echo ' - ';
			if ( $enemies !== true ) {
				echo '<a href="?version='.$version.'&section=necropolis&map='.$map_letter.$map_number.'&enemies=true">Enemies</a>';
			} else {
				echo 'Enemies';
			}
			echo '</th></tr>';
			echo $item;
			echo '</table>';
			echo '</div>';
		}
	}
	
} else {
	print_top( $version, $allowVersionSelect, false );?>
	<h1>Tales of Vesperia</h1>
	<h2>Data &amp; Translation Guide</h2>
	
	<p>
	<div>Part of the <a href="http://talesofvesperia.net/">PS3 fan-translation</a>.</div>
	<div>Inspired by and some layout provided by <a href="http://apps.lushu.org/vesperia/">apps.lushu.org</a>.</div>
	</p>
	
	<p><div>This page uses <a href="http://en.wikipedia.org/wiki/Ruby_character#HTML_markup">Ruby HTML markup</a> to display Furigana. If it doesn't display properly, please update your browser and/or install a browser extension.</div></p>
<?php
}
print_bottom();

	//page generation time code
		$time = explode(' ', microtime());
		$totaltime = (($time[1] + $time[0]) - $pagegen_start_time);
		echo '<div id="footer_time">Page generated in '.round($totaltime, 3).' seconds.</div>';
	//page generation time code end

echo '</body>';
echo '</html>';


?>