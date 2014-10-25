<?php
	//page generation time code
		$time = explode(' ', microtime());
		$pagegen_start_time = $time[1] + $time[0];
	//page generation time code end


header('Content-Type: text/html; charset=UTF-8');
//$version = '360';
$version = 'ps3';

require_once 'db.class.php';
require_once 'header.php';
include 'credentials.php';
$db = new db( $__db_connstr__[$version], $__db_username__, $__db_password__ );

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

if ( $section === 'artes' ) {
	print_top( $version, 'Artes' );
	echo '<table>';
	
	$artes = $db->GetArtesHtml( $id );
	$first = true;
	foreach ( $artes as $a ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="5"><hr></td></tr>';
		}
		echo $a;
	}
	
	echo '</table>';
} elseif ( $section === 'skills' ) {
	print_top( $version, 'Skills' );
	echo '<table>';
	
	$skills = $db->GetSkillsHtml( $id );
	$first = true;
	foreach ( $skills as $a ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="4"><hr></td></tr>';
		}
		echo $a;
	}
	
	echo '</table>';
} elseif ( $section === 'recipes' ) {
	print_top( $version, 'Recipes' );
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
	print_top( $version, 'Shops' );
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
	print_top( $version, 'Titles' );
	echo '<table>';
	
	$items = $db->GetTitlesHtml( $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="4"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'synopsis' ) {
	print_top( $version, 'Synopsis' );
	
	$items = $db->GetSynopsisHtml( $id );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<hr>';
		}
		echo $item;
	}
	
} elseif ( $section === 'battlebook' ) {
	print_top( $version, 'Battle Book' );
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
	print_top( $version, 'Enemies' );
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
	print_top( $version, 'Items' );
	echo '<table>';
	
	$items = $db->GetItemsHtml( $id, $category, $icon );
	$first = true;
	foreach ( $items as $item ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="5"><hr></td></tr>';
		}
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'locations' ) {
	print_top( $version, 'Locations' );
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
} elseif ( $section === 'records' ) {
	print_top( $version, 'Records' );
	echo '<table>';
	
	$items = $db->GetRecordsHtml( $id );
	foreach ( $items as $item ) {
		echo $item;
	}
	
	echo '</table>';
} elseif ( $section === 'settings' ) {
	print_top( $version, 'Settings' );
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
	print_top( $version, 'Grade Shop' );
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
} elseif ( $section === 'strategy' ) {
	print_top( $version, 'Strategy' );
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
	print_top( $version, 'Necropolis of Nostalgia' );
	
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
			//echo $letter;
			echo '<tr>';
			for ( $number = 1; $number <= 10; ++$number ) {
				echo '<td>';
				echo '<a href="?section=necropolis&map='.$letter.$number.'">'.$letter.'-'.$number.'</a>';
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
			echo '<table class="necropolisfloor"><tr><th colspan="6"><div class="itemname" style="text-align: center;">'.$map_letter.'-'.$map_number.'</div></th></tr>';
			echo '<tr><th colspan="6">';
			if ( $enemies === true ) {
				echo '<a href="?section=necropolis&map='.$map_letter.$map_number.'">General Info</a>';
			} else {
				echo 'General Info';
			}
			echo ' - ';
			if ( $enemies !== true ) {
				echo '<a href="?section=necropolis&map='.$map_letter.$map_number.'&enemies=true">Enemies</a>';
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
	print_top( $version, false );
	echo 'Undefined.';
}

	//page generation time code
		$time = explode(' ', microtime());
		$totaltime = (($time[1] + $time[0]) - $pagegen_start_time);
		echo '<div id="footer_time">Page generated in '.round($totaltime, 3).' seconds.</div>';
	//page generation time code end

echo '</body>';
echo '</html>';
?>