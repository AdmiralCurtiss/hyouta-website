<?php
	//page generation time code
		$time = explode(' ', microtime());
		$pagegen_start_time = $time[1] + $time[0];
	//page generation time code end

header('Content-Type: text/html; charset=UTF-8');

require_once 'util.php';
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

$urlHelper = VesperiaUrlHelper::FromGetParams( $_GET );

$version = $urlHelper->version;
$locale = $urlHelper->locale;
$compare = $urlHelper->compare;
$section = $urlHelper->section === false ? 'index' : $urlHelper->section;
$id = $urlHelper->id;
$category = $urlHelper->category;
$icon = $urlHelper->icon;
$character = $urlHelper->character;
$name = $urlHelper->name === false ? '' : $urlHelper->name;
$query = $urlHelper->query === false ? '' : $urlHelper->query;
$page = $urlHelper->page === false ? 1 : $urlHelper->page;
$markVersionDifferences = $urlHelper->diff;
$showScenarioJumper = $urlHelper->jump;
$perPage = $urlHelper->perpage === false ? 0 : $urlHelper->perpage;

include '../credentials.php';
$db = new db( __db_connstr_vesperia_from_version_and_locale__( $version, $locale ), $__db_username_vesperia__, $__db_password_vesperia__ );

echo '<html>';

function shouldSearch( $begin, $end, $offset, $count ) {
	// need to check if range [begin, end[ includes any of [offset, (offset+count)[, so, uh...
	return max( $begin, $offset ) - ( min( $end, ($offset+$count) ) - 1 ) <= 0;
}

function paginate( $pageNum, $itemsPerPage, $itemsTotal, $urlHelper ) {
	$totalPages = (int)ceil($itemsTotal / $itemsPerPage);

	if ( $totalPages > 1 ) {
		$pageString = '';
		if ( $pageNum > 1 ) {
			$pageString .= '<a href="'.$urlHelper->WithPage($pageNum - 1)->WithPerPage($itemsPerPage)->GetUrl().'">Previous Page</a> - ';
		}
		$pageString .= 'Page '.$pageNum.' of '.$totalPages;
		if ( $pageNum < $totalPages ) {
			$pageString .= ' - <a href="'.$urlHelper->WithPage($pageNum + 1)->WithPerPage($itemsPerPage)->GetUrl().'">Next Page</a>';
		}
		echo '<p>'.$pageString.'</p>';
	}
}

if ( $section === 'search' ) {
	print_top( $version, $locale, $compare, 'Search', $query );
	if ( $perPage <=   0 ) { $perPage =  50; }
	if ( $perPage >  500 ) { $perPage = 500; }
	$globalOffset = ( $page - 1 ) * $perPage;
	$entriesToGo = $perPage;

	if ( strlen( $query ) >= 2 ) {
		echo '<div class="scenario-content">';
		echo '<div class="storyBox">';

		$totalOffsetBegin = $globalOffset;
		$totalOffsetEnd = $totalOffsetBegin + $perPage;

		$totalSkitNameCount  = GameVersionLocale::AllowScenario( $version ) ? $db->SearchSkitNamesCount( $compare, $query ) : 0;
		$totalItemCount      = $db->SearchItemsCount( $compare, $query );
		$totalEnemyCount     = $db->SearchEnemiesCount( $compare, $query );
		$totalArteCount      = $db->SearchArtesCount( $compare, $query );
		$totalSkillCount     = $db->SearchSkillsCount( $compare, $query );
		$totalRecipeCount    = $db->SearchRecipesCount( $compare, $query );
		$totalTitleCount     = $db->SearchTitlesCount( $compare, $query );
		$totalSynopsisCount  = $db->SearchSynopsisCount( $compare, $query );
		$totalBtlBookCount   = $db->SearchBattleBookCount( $compare, $query );
		$totalScenarioCount  = GameVersionLocale::AllowScenario( $version ) ? $db->SearchScenarioCount( $compare, $query ) : 0;
		$totalSkitCount      = GameVersionLocale::AllowScenario( $version ) ? $db->SearchSkitCount( $compare, $query ) : 0;
		$totalStringDicCount = GameVersionLocale::AllowScenario( $version ) ? $db->SearchStringDicCount( $compare, $query ) : 0;

		$indexOffsetSkitName  = 0;
		$indexOffsetItem      = $indexOffsetSkitName  + $totalSkitNameCount;
		$indexOffsetEnemy     = $indexOffsetItem      + $totalItemCount;
		$indexOffsetArte      = $indexOffsetEnemy     + $totalEnemyCount;
		$indexOffsetSkill     = $indexOffsetArte      + $totalArteCount;
		$indexOffsetRecipe    = $indexOffsetSkill     + $totalSkillCount;
		$indexOffsetTitle     = $indexOffsetRecipe    + $totalRecipeCount;
		$indexOffsetSynopsis  = $indexOffsetTitle     + $totalTitleCount;
		$indexOffsetBtlBook   = $indexOffsetSynopsis  + $totalSynopsisCount;
		$indexOffsetScenario  = $indexOffsetBtlBook   + $totalBtlBookCount;
		$indexOffsetSkit      = $indexOffsetScenario  + $totalScenarioCount;
		$indexOffsetStringDic = $indexOffsetSkit      + $totalSkitCount;
		$totalFoundEntries    = $indexOffsetStringDic + $totalStringDicCount;

		paginate( $page, $perPage, $totalFoundEntries, $urlHelper->WithSection('search')->WithQuery($query) );

		echo '<div>Found '.$totalFoundEntries.' entries.</div>';

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetSkitName, $totalSkitNameCount ) ) {
			$skits = $db->SearchSkitNamesHtml( $compare, $query, max( 0, $totalOffsetBegin - $indexOffsetSkitName ), $entriesToGo );
			echo '<hr>';
			echo '<div class="scenario-previous-next">Skits</div>';
			echo '<table>';
			foreach ( $skits as $s ) {
				$s->RenderTableRow( $version, $locale, $compare, $markVersionDifferences );
				--$entriesToGo;
			}
			echo '</table>';
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetItem, $totalItemCount ) ) {
			$items = $db->SearchItems( $compare, $query, max( 0, $totalOffsetBegin - $indexOffsetItem ), $entriesToGo );
			echo '<hr>';
			echo '<table>';
			$first = true;
			foreach ( $items as $item ) {
				if ( $first === true ) { $first = false; } else {
					echo '<tr><td colspan="5"><hr></td></tr>';
				}
				echo $item;
				--$entriesToGo;
			}
			echo '</table>';
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetEnemy, $totalEnemyCount ) ) {
			$items = $db->SearchEnemies( $compare, $query, max( 0, $totalOffsetBegin - $indexOffsetEnemy ), $entriesToGo );
			echo '<hr>';
			echo '<table>';
			$first = true;
			foreach ( $items as $item ) {
				if ( $first === true ) { $first = false; } else {
					echo '<tr><td colspan="7"><hr></td></tr>';
				}
				echo $item;
				--$entriesToGo;
			}
			echo '</table>';
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetArte, $totalArteCount ) ) {
			$artes = $db->SearchArtes( $compare, $query, max( 0, $totalOffsetBegin - $indexOffsetArte ), $entriesToGo );
			echo '<hr>';
			echo '<table>';
			$first = true;
			foreach ( $artes as $a ) {
				if ( $first === true ) { $first = false; } else {
					echo '<tr><td colspan="5"><hr></td></tr>';
				}
				echo $a;
				--$entriesToGo;
			}
			echo '</table>';
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetSkill, $totalSkillCount ) ) {
			$items = $db->SearchSkills( $compare, $query, max( 0, $totalOffsetBegin - $indexOffsetSkill ), $entriesToGo );
			echo '<hr>';
			echo '<table>';
			$first = true;
			foreach ( $items as $a ) {
				if ( $first === true ) { $first = false; } else {
					echo '<tr><td colspan="4"><hr></td></tr>';
				}
				echo $a;
				--$entriesToGo;
			}
			echo '</table>';
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetRecipe, $totalRecipeCount ) ) {
			$items = $db->SearchRecipes( $compare, $query, max( 0, $totalOffsetBegin - $indexOffsetRecipe ), $entriesToGo );
			echo '<hr>';
			echo '<table>';
			$first = true;
			foreach ( $items as $item ) {
				if ( $first === true ) { $first = false; } else {
					echo '<tr><td colspan="4"><hr></td></tr>';
				}
				echo '<tr>';
				echo $item;
				echo '</tr>';
				--$entriesToGo;
			}
			echo '</table>';
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetTitle, $totalTitleCount ) ) {
			$items = $db->SearchTitles( $compare, $query, max( 0, $totalOffsetBegin - $indexOffsetTitle ), $entriesToGo );
			echo '<hr>';
			echo '<table>';
			$first = true;
			foreach ( $items as $item ) {
				if ( $first === true ) { $first = false; } else {
					echo '<tr><td colspan="4"><hr></td></tr>';
				}
				echo $item;
				--$entriesToGo;
			}
			echo '</table>';
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetSynopsis, $totalSynopsisCount ) ) {
			$items = $db->SearchSynopsis( $compare, $query, max( 0, $totalOffsetBegin - $indexOffsetSynopsis ), $entriesToGo );
			echo '<hr>';
			$first = true;
			foreach ( $items as $item ) {
				if ( $first === true ) { $first = false; } else {
					echo '<hr>';
				}
				echo $item;
				--$entriesToGo;
			}
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetBtlBook, $totalBtlBookCount ) ) {
			$items = $db->SearchBattleBook( $compare, $query, max( 0, $totalOffsetBegin - $indexOffsetBtlBook ), $entriesToGo );
			echo '<hr>';
			echo '<table>';
			$first = true;
			foreach ( $items as $item ) {
				if ( $first === true ) { $first = false; } else {
					echo '<tr><td colspan="2"><hr></td></tr>';
				}
				echo '<tr>';
				echo $item;
				echo '</tr>';
				--$entriesToGo;
			}
			echo '</table>';
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetScenario, $totalScenarioCount ) ) {
			echo '<hr>';
			$sce = $db->SearchScenario( $compare, $query, max( 0, $totalOffsetBegin - $indexOffsetScenario ), $entriesToGo );
			$previousId = '';
			foreach ( $sce as $s ) {
				if ( $previousId !== $s->episodeId ) {
					echo '<div class="scenario-previous-next"><a href="'.$urlHelper->WithSection('scenario')->WithName($s->episodeId)->GetUrl().'">'.$s->episodeId.'</a></div>';
					$previousId = $s->episodeId;
				}
				$s->Render( $version, $locale, $compare, $markVersionDifferences );
				--$entriesToGo;
			}
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetSkit, $totalSkitCount ) ) {
			echo '<hr>';
			$skit = $db->SearchSkit( $compare, $query, max( 0, $totalOffsetBegin - $indexOffsetSkit ), $entriesToGo );
			$previousId = '';
			foreach ( $skit as $s ) {
				if ( $previousId !== $s->skitId ) {
					echo '<div class="scenario-previous-next"><a href="'.$urlHelper->WithSection('skit')->WithName($s->skitId)->GetUrl().'">'.$s->skitId.'</a></div>';
					$previousId = $s->skitId;
				}
				$s->Render( $version, $locale, $compare, $markVersionDifferences );
				--$entriesToGo;
			}
		}

		if ( shouldSearch( $totalOffsetBegin, $totalOffsetEnd, $indexOffsetStringDic, $totalStringDicCount ) ) {
			echo '<hr>';
			$entries = $db->SearchStringDic( $compare, $query, max( 0, $totalOffsetBegin - $indexOffsetStringDic ), $entriesToGo );
			if ( !empty($entries) ) {
				echo '<div class="scenario-previous-next">Strings</div>';
				foreach ( $entries as $e ) {
					$e->Render( $version, $locale, $compare, $markVersionDifferences );
					--$entriesToGo;
				}
			}
		}

		paginate( $page, $perPage, $totalFoundEntries, $urlHelper->WithSection('search')->WithQuery($query) );

		echo '</div>';
		echo '</div>';
	}
} elseif ( GameVersionLocale::AllowScenario( $version ) && $section === 'scenario' ) {
	print_top( $version, $locale, $compare, 'Scenario' );
	
	$scenarioMetadata = null;
	if ( $showScenarioJumper ) {
		$thisScenarioMeta = $db->GetScenarioMetaFromEpisodeId( $name );
		if ( $thisScenarioMeta !== null ) {
			$scenarioMetadata = $db->GetScenarioMetaGroupRange( $thisScenarioMeta->type, $thisScenarioMeta->sceneGroup - 1, $thisScenarioMeta->sceneGroup + 1 );
			ScenarioMeta::RenderIndex( $urlHelper, $scenarioMetadata, $markVersionDifferences, $name );
		}
	}
	
	echo '<div class="scenario-content">';
	
	if ( $scenarioMetadata !== null ) {
		ScenarioMeta::RenderPreviousNext( $urlHelper, $scenarioMetadata, $name, true, $markVersionDifferences );
	}
	
	$sce = $db->GetScenario( $name );

	echo '<div class="storyBox">';
	foreach ( $sce as $s ) {
		$s->Render( $version, $locale, $compare, $markVersionDifferences );
	}
	echo '</div>';
	
	if ( $scenarioMetadata !== null ) {
		ScenarioMeta::RenderPreviousNext( $urlHelper, $scenarioMetadata, $name, false, $markVersionDifferences );
	}
	
	echo '</div>';
	
} elseif ( GameVersionLocale::AllowScenario( $version ) && $section === 'skit' ) {
	print_top( $version, $locale, $compare, 'Skit' );
	
	if ( $showScenarioJumper ) {
		$thisScenarioMeta = $db->GetScenarioMetaFromEpisodeId( $name );
		if ( $thisScenarioMeta !== null ) {
			$scenarioMetadata = $db->GetScenarioMetaGroupRange( $thisScenarioMeta->type, $thisScenarioMeta->sceneGroup - 1, $thisScenarioMeta->sceneGroup + 1 );
			ScenarioMeta::RenderIndex( $urlHelper, $scenarioMetadata, $markVersionDifferences, $name );
		}
	}
	
	$lines = $db->GetSkit( $name );
	$meta = $db->GetSkitMeta( $name );
	
	echo '<div class="scenario-content">';
	
	echo '<div';
	if ( $markVersionDifferences ) {
		echo ' class="changeStatusIndex'.$meta->changeStatus.'"';
	}
	echo '>';
	if ( $meta !== null ) {
		if ( WantsJp($compare) ) {
			echo '<div class="skit-name">';
			echo $meta->jpName;
			echo '</div>';
		}
		if ( WantsEn($compare) ) {
			echo '<div class="skit-name">';
			echo $meta->enName;
			echo '</div>';
		}
	}
	echo '</div>';
	
	echo '<div class="storyBox">';
	foreach ( $lines as $s ) {
		$s->Render( $version, $locale, $compare, $markVersionDifferences );
	}
	echo '</div>';
	
	echo '</div>';
	
} elseif ( GameVersionLocale::AllowScenario( $version ) && $section === 'scenario-index' ) {
	print_top( $version, $locale, $compare, 'Story Index' );
	$scenarioMetadata = $db->GetScenarioIndex( 1 );
	ScenarioMeta::RenderIndex( $urlHelper, $scenarioMetadata, $markVersionDifferences );
} elseif ( GameVersionLocale::AllowScenario( $version ) && $section === 'sidequest-index' ) {
	print_top( $version, $locale, $compare, 'Sidequest Index' );
	$scenarioMetadata = $db->GetScenarioIndex( 2 );
	ScenarioMeta::RenderIndex( $urlHelper, $scenarioMetadata, $markVersionDifferences );
} elseif ( GameVersionLocale::AllowScenario( $version ) && $section === 'skit-index' ) {
	print_top( $version, $locale, $compare, 'Skit Index' );
	echo '<table>';
	
	$skits = $db->GetSkitIndex();
	foreach ( $skits as $s ) {
		$s->RenderTableRow( $version, $locale, $compare, $markVersionDifferences );
	}
	
	echo '</table>';
} elseif ( $section === 'artes' ) {
	print_top( $version, $locale, $compare, 'Artes' );
	print_character_select( $version, $locale, $compare, $section );
	echo '<table>';
	
	if ( $character === false ) {
		$artes = $db->GetArtesHtml( $compare, $id );
	} else {
		$artes = $db->GetArtesByCharacterHtml( $compare, $character );
	}
	$first = true;
	foreach ( $artes as $a ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="4"><hr></td></tr>';
		}
		echo $a;
	}
	
	echo '</table>';
} elseif ( $section === 'skills' ) {
	print_top( $version, $locale, $compare, 'Skills' );
	print_character_select( $version, $locale, $compare, $section );
	echo '<table>';
	
	if ( $character === false ) {
		$skills = $db->GetSkillsHtml( $compare, $id );
	} else {
		$skills = $db->GetSkillsByCharacterHtml( $compare, $character );
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
	print_top( $version, $locale, $compare, 'Recipes' );
	echo '<table>';
	
	$items = $db->GetRecipesHtml( $compare, $id );
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
	print_top( $version, $locale, $compare, 'Shops' );
	echo '<table>';
	
	$items = $db->GetShopsHtml( $compare, $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="6"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'titles' ) {
	print_top( $version, $locale, $compare, 'Titles' );
	print_character_select( $version, $locale, $compare, $section );
	echo '<table>';
	
	if ( $character === false ) {
		$items = $db->GetTitlesHtml( $compare, $id );
	} else {
		$items = $db->GetTitlesByCharacterHtml( $compare, $character );
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
	print_top( $version, $locale, $compare, 'Synopsis' );
	
	$items = $db->GetSynopsisHtml( $compare, $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<hr>';
		}
		echo $item;
	}
	
} elseif ( $section === 'battlebook' ) {
	print_top( $version, $locale, $compare, 'Battle Book' );
	echo '<table>';
	
	$items = $db->GetBattleBookHtml( $compare, $id );
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
	print_top( $version, $locale, $compare, 'Enemies' );
	echo '<table>';
	
	$items = $db->GetEnemiesHtml( $compare, $id, $category );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="7"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'items' ) {
	print_top( $version, $locale, $compare, 'Items' );

	if ( $perPage <=   0 ) { $perPage = 120; }
	if ( $perPage >  500 ) { $perPage = 500; }

	$itemcount = $db->GetItemsCount( $id, $category, $icon );
	$offset = ($page - 1) * $perPage;
	$items = $db->GetItemsHtml( $compare, $id, $category, $icon, $offset, $perPage );

	paginate( $page, $perPage, $itemcount, $urlHelper );

	echo '<table>';
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="5"><hr></td></tr>';
		}
		echo $item;
	}
	echo '</table>';

	paginate( $page, $perPage, $itemcount, $urlHelper );
} elseif ( $section === 'locations' ) {
	print_top( $version, $locale, $compare, 'Locations' );
	echo '<table>';
	
	$items = $db->GetLocationsHtml( $compare, $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="3"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( GameVersionLocale::HasSearchPoints( $version ) && $section === 'searchpoint' ) {
	print_top( $version, $locale, $compare, 'Search Points' );
	echo '<img src="etc/'.$version.'/SearchPoint.jpg">';
	echo '<hr>';
	echo '<table>';
	
	$items = $db->GetSearchPointsHtml( $compare, $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="5"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'records' ) {
	print_top( $version, $locale, $compare, 'Records' );
	echo '<table>';
	
	$items = $db->GetRecordsHtml( $compare, $id );
	foreach ( $items as $item ) {
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'settings' ) {
	print_top( $version, $locale, $compare, 'Settings' );
	echo '<table>';
	
	$items = $db->GetSettingsHtml( $compare, $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="5"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'gradeshop' ) {
	print_top( $version, $locale, $compare, 'Grade Shop' );
	echo '<table>';
	
	$items = $db->GetGradeShopHtml( $compare, $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="3"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( GameVersionLocale::HasTrophies( $version ) && $section === 'trophies' ) {
	print_top( $version, $locale, $compare, 'Trophies' );
	echo '<table>';
	
	$items = $db->GetTrophiesHtml( $compare, $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="3"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'strategy' ) {
	print_top( $version, $locale, $compare, 'Strategy' );
	echo '<table>';
	
	$items = $db->GetStrategySetHtml( $compare, $id );
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
	
	$items = $db->GetStrategyOptionsHtml( $compare, $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="4"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( GameVersionLocale::HasNecropolis( $version ) && $section === 'necropolis' ) {
	print_top( $version, $locale, $compare, 'Necropolis of Nostalgia' );

	$stratumNames = array(
		0 => 'Firmament',
		1 => 'Existence',
		2 => 'Hegemony',
		3 => 'Fauna',
		4 => 'Fatality',
		5 => 'Abysm'
	);

	$map = false;
	if ( $urlHelper->mapletter !== false && $urlHelper->mapfloor !== false ) {
		$map_letter = $urlHelper->mapletter;
		$map_number = $urlHelper->mapfloor;
		$map = 'BTL_XTM_AREA_'.( str_pad($map_letter * 10 + $map_number, 2, '0', STR_PAD_LEFT) );
	}
	$enemies = $urlHelper->enemies;

	if ( $map === false ) {
		// output map list
		echo '<div class="necropolis-select">';
		echo '<table>';
		for ( $letter = 0; $letter < 6; ++$letter ) {
			echo '<tr>';
			echo '<td>';
			echo $stratumNames[$letter];
			echo '</td>';
			for ( $number = 1; $number <= 10; ++$number ) {
				echo '<td>';
				echo '<a href="'.$urlHelper->WithSection('necropolis')->WithMapLetter($letter)->WithMapFloor($number)->GetUrl().'">'.$number.'</a>';
				echo '</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';
	} else {
		$items = $db->GetNecropolisHtml( $compare, $enemies, $map );
		$first = true;
		foreach ( $items as $item ) {
			if ( $first === true ) { $first = false; } else {
				echo '<hr>';
			}
			
			echo '<div id="'.$urlHelper->MapLetterAsLetter().$map_number.'">';
			echo '<table class="necropolisfloor"><tr><th colspan="6"><div class="itemname" style="text-align: center;">'.$stratumNames[$map_letter].' '.$map_number.'F ('.$urlHelper->MapLetterAsLetter().'-'.$map_number.')</div></th></tr>';
			echo '<tr><th colspan="6">';
			if ( $enemies === true ) {
				echo '<a href="'.$urlHelper->WithEnemies(false)->GetUrl().'">General Info</a>';
			} else {
				echo 'General Info';
			}
			echo ' - ';
			if ( $enemies !== true ) {
				echo '<a href="'.$urlHelper->WithEnemies(true)->GetUrl().'">Enemies</a>';
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
	print_top( $version, $locale, $compare, false );
	echo '<h1>Tales of Vesperia</h1>';
	echo '<h2>Reference Guide</h2>';
	
	echo '<p>';
	echo '<div class="mainVersionSelect">';
	GameVersionLocale::PrintVersionSelectLong( $version, $locale, $compare );
	echo '</div>';
	echo '</p>';

	echo '<p>';
	//echo '<div>Part of the <a href="http://talesofvesperia.net/">PS3 fan-translation</a>.</div>'
	echo '<div>Inspired by and some layout provided by <a href="http://apps.lushu.org/vesperia/">apps.lushu.org</a>.</div>';
	echo '</p>';
	
	echo '<p><div>This page uses <a href="http://en.wikipedia.org/wiki/Ruby_character#HTML_markup">Ruby HTML markup</a> to display Furigana. If it doesn\'t display properly, please update your browser and/or install a browser extension.</div></p>';
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