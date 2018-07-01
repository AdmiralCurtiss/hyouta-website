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

echo '<html>';

if ( $section === 'search' ) {
	print_top( $version, $allowVersionSelect, 'Search', $query );
	$perPage = 15;
	$globalOffset = ( $page - 1 ) * $perPage;
	$entriesToGo = $perPage;
	$totalEntriesPrinted = 0;
	
	if ( strlen( $query ) >= 3 ) {
		echo '<div class="scenario-content">';
		echo '<div class="storyBox">';
		
		$localOffset = $globalOffset;
		
		$skits = $db->SearchSkitNamesHtml( $query, $localOffset, $entriesToGo );
		$skitCount = $db->FoundRows();
		if ( !empty($skits) ) {
			echo '<div class="scenario-previous-next">Skits</div>';
			echo '<table>';
			foreach ( $skits as $s ) {
				$s->RenderTableRow( $version, $markVersionDifferences );
				--$entriesToGo;
				++$totalEntriesPrinted;
			}
			echo '</table>';
		}
		
		if ( $entriesToGo > 0 ) {
			$localOffset -= $skitCount; if ( $localOffset < 0 ) { $localOffset = 0; }
			$sce = $db->SearchScenario( $query, $localOffset, $entriesToGo );
			$sceRows = $db->FoundRows();
			$previousId = '';
			foreach ( $sce as $s ) {
				if ( $previousId != $s->episodeId ) {
					echo '<div class="scenario-previous-next"><a href="?version='.$version.'&section=scenario&name='.$s->episodeId.'">'.$s->episodeId.'</a></div>';
					$previousId = $s->episodeId;
				}
				$s->Render( $markVersionDifferences );
				--$entriesToGo;
				++$totalEntriesPrinted;
			}
			
			if ( $entriesToGo > 0 ) {
				$localOffset -= $sceRows; if ( $localOffset < 0 ) { $localOffset = 0; }
				
				$previousId = '';
				$skit = $db->SearchSkit( $query, $localOffset, $entriesToGo );
				$skitRows = $db->FoundRows();
				foreach ( $skit as $s ) {
					if ( $previousId != $s->skitId ) {
						echo '<div class="scenario-previous-next"><a href="?version='.$version.'&section=skit&name='.$s->skitId.'">'.$s->skitId.'</a></div>';
						$previousId = $s->skitId;
					}
					$s->Render( $markVersionDifferences );
					--$entriesToGo;
					++$totalEntriesPrinted;
				}
				
				if ( $entriesToGo > 0 ) {
					$localOffset -= $skitRows; if ( $localOffset < 0 ) { $localOffset = 0; }
					
					$entries = $db->SearchStringDic( $query, $localOffset, $entriesToGo );
					$stringRows = $db->FoundRows();
					if ( !empty($entries) ) {
						echo '<div class="scenario-previous-next">Strings</div>';
						foreach ( $entries as $e ) {
							$e->Render( $markVersionDifferences );
							--$entriesToGo;
							++$totalEntriesPrinted;
						}
					}
					
					$localOffset -= $stringRows;
				}
			}
		}
		
		// rather bad page detection, maybe fix later
		// though this does less db queries since we don't check if skits/strings have stuff if we just ended a category with the last scenario/skit entry
		if ( $totalEntriesPrinted === $perPage ) {
			echo '<div class="scenario-previous-next"><a href="?version='.$version.'&section=search&query='.urlencode($query).'&page='.( $page + 1 ).'">Next Page</a></div>';
		}
		
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
	
	$itemcount = $db->GetItemsCount( $id, $category, $icon );
	$itemsPerPage = 250;
	$offset = ($page - 1) * $itemsPerPage;
	$items = $db->GetItemsHtml( $id, $category, $icon, $offset, $itemsPerPage );
	
	$totalPages = $itemcount % $itemsPerPage == 0 ? $itemcount / $itemsPerPage : (int)($itemcount / $itemsPerPage) + 1;
	$pageString = 'Page '.$page.' of '.$totalPages;
	if ( $itemcount > $offset + $itemsPerPage ) {
		$pageString .= ' - <a href="?version='.$version.'&section=items';
		if ( $category !== false ) { $pageString .= '&category='.$category; }
		if ( $icon !== false ) { $pageString .= '&icon='.$icon; }
		$pageString .= '&page='.( $page + 1 ).'">Next Page</a>';
	}
	$isMultipage = $itemcount > $itemsPerPage;
	
	if ( $isMultipage ) { echo '<p>'.$pageString.'</p>'; }
	
	echo '<table>';
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="5"><hr></td></tr>';
		}
		echo $item;
	}
	echo '</table>';
	
	if ( $isMultipage ) { echo '<p>'.$pageString.'</p>'; }
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