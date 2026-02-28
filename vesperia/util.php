<?php
function WantsJp( $compare ) {
	return $compare !== '2';
}
function WantsEn( $compare ) {
	return $compare !== '1';
}

class GameVersionLocale {
	var $version;
	var $locale;
	var $defaultCompare;
	var $validCompares;
	var $shortName;
	var $longName;
	var $lang1short;
	var $lang2short;
	var $lang1long;
	var $lang2long;

	function __construct( $version, $locale, $defaultCompare, $validCompares, $shortName, $longName, $lang1short, $lang2short, $lang1long, $lang2long ) {
		$this->version = $version;
		$this->locale = $locale;
		$this->defaultCompare = $defaultCompare;
		$this->validCompares = $validCompares;
		$this->shortName = $shortName;
		$this->longName = $longName;
		$this->lang1short = $lang1short;
		$this->lang2short = $lang2short;
		$this->lang1long = $lang1long;
		$this->lang2long = $lang2long;
	}

	public static function HasSearchPoints( $version ) {
		return $version === 'ps3v' || $version === 'ps3p' || $version === 'pc';
	}

	public static function HasTrophies( $version ) {
		return $version === 'ps3v' || $version === 'ps3p';
	}

	public static function HasNecropolis( $version ) {
		return $version === 'ps3v' || $version === 'ps3p' || $version === 'pc';
	}

	public static function HasPatty( $version ) {
		return $version === 'ps3v' || $version === 'ps3p' || $version === 'pc';
	}

	public static function AllowScenario( $version ) {
		return $version === 'ps3p';
	}

	public static function GetVersions() {
		$l = array();
		//                            version  locale  default compare  valid compares                 short version select id  user friendly long name
		$l[] = new GameVersionLocale( '360u',  'us',   '2',             array(      '2'             ), '360',                   'Xbox 360 North American Version'    , 'JP', 'US', 'Japanese', 'English' );
		$l[] = new GameVersionLocale( '360e',  'uk',   '2',             array(      '2'             ), '360',                   'Xbox 360 European Version - English', 'JP', 'UK', 'Japanese', 'English' );
		$l[] = new GameVersionLocale( '360e',  'fr',   '2',             array(      '2'             ), '360',                   'Xbox 360 European Version - French' , 'JP', 'FR', 'Japanese', 'French' );
		$l[] = new GameVersionLocale( '360e',  'de',   '2',             array(      '2'             ), '360',                   'Xbox 360 European Version - German' , 'JP', 'DE', 'Japanese', 'German' );
		$l[] = new GameVersionLocale( 'ps3v',  'jp',   '1',             array( '1'                  ), 'PS3',                   'PlayStation 3 Japanese Version'     , 'JP', 'EN', 'Japanese', 'English' );
		$l[] = new GameVersionLocale( 'ps3p',  'jp',   'c2',            array(      '2', 'c1', 'c2' ), 'PS3',                   'PlayStation 3 Fan-Translation'      , 'JP', 'Fan EN', 'Japanese', 'English' );
		$l[] = new GameVersionLocale( 'pc',   'eng',   '2',             array(      '2'             ), 'PC',                    'PC Version - English'               , 'JP', 'EN', 'Japanese', 'English' );
		return $l;
	}

	public static function ParseVersion( &$version, &$locale, &$compare, &$args ) {
		$v = isset( $args['version'] ) && is_string( $args['version'] ) ? $args['version'] : NULL;
		$l = isset( $args['locale']  ) && is_string( $args['locale']  ) ? $args['locale']  : NULL;
		$c = isset( $args['compare'] ) && is_string( $args['compare'] ) ? $args['compare'] : NULL;

		// old site used 'ps3' for the patched ps3 version and '360' for the european 360 version, so update that if necessary
		if ( $v === 'ps3' ) { $v = 'ps3p'; } else if ( $v === '360' ) { $v = '360e'; }

		$validversions = GameVersionLocale::GetVersions();

		// see if valid version was selected
		$foundversion = false;
		foreach ( $validversions as $g ) {
			if ( $g->version === $v ) {
				$version = $g->version;
				$foundversion = true;
				break;
			}
		}
		if ( !$foundversion ) {
			// no valid version selected, fall back to sensible default
			$version = 'pc';
		}

		// see if valid locale was selected
		$foundlocale = false;
		$selectedversion = null;
		foreach ( $validversions as $g ) {
			if ( $g->version === $version && $g->locale === $l ) {
				$locale = $g->locale;
				$foundlocale = true;
				$selectedversion = $g;
				break;
			}
		}
		if ( !$foundlocale ) {
			// no valid locale selected, fall back to first found locale of version
			foreach ( $validversions as $g ) {
				if ( $g->version === $version ) {
					$locale = $g->locale;
					$foundlocale = true;
					$selectedversion = $g;
					break;
				}
			}
		}
		if ( !$foundlocale || is_null( $selectedversion ) ) {
			// this shouldn't happen
			$version = null;
			$locale = null;
			$compare = null;
			return false;
		}

		// see if valid compare was selected
		$foundcompare = false;
		foreach ( $selectedversion->validCompares as $cmp ) {
			if ( $cmp === $c ) {
				$compare = $cmp;
				$foundcompare = true;
			}
		}
		if ( !$foundcompare ) {
			// no, fall back to default
			$compare = $selectedversion->defaultCompare;
			$foundcompare = true;
		}

		return true;
	}

	public static function GetUserFriendlyLongNameFromCompare( $compare, $gameversion ) {
		if ( $compare === '1'  ) { return $gameversion->lang1long; }
		if ( $compare === '2'  ) { return $gameversion->lang2long; }
		if ( $compare === 'c1' ) { return 'Compare with '.$gameversion->lang1long.' links'; }
		if ( $compare === 'c2' ) { return 'Compare with '.$gameversion->lang2long.' links'; }
		return $compare;
	}
	public static function GetUserFriendlyShortNameFromCompare( $compare, $gameversion ) {
		if ( $compare === '1'  ) { return $gameversion->lang1short; }
		if ( $compare === '2'  ) { return $gameversion->lang2short; }
		if ( $compare === 'c1' ) { return 'Compare'; }
		if ( $compare === 'c2' ) { return 'Compare'; }
		return $compare;
	}

	public static function PrintVersionSelectLong( $urlHelper ) {
		$versions = GameVersionLocale::GetVersions();
		foreach ( $versions as $g ) {
			$links = array();
			foreach ( $g->validCompares as $c ) {
				$selected = $urlHelper->version === $g->version && $urlHelper->locale === $g->locale && $urlHelper->compare === $c;
				$links[] = [
					'selected' => $selected,
					'compare' => $c,
					'link' => $urlHelper->WithVersion($g->version)->WithLocale($g->locale)->WithCompare($c)->GetUrl()
				];
			}

			if ( count($links) === 1 ) {
				foreach ( $links as $link ) {
					if ( $link['selected'] ) {
						echo '<b>';
					} else {
						echo '<a href="'.$link['link'].'">';
					}
					echo $g->longName;
					if ( $link['selected'] ) {
						echo '</b>';
					} else {
						echo '</a>';
					}
				}
			} else {
				echo $g->longName;
				echo ':';
				foreach ( $links as $link ) {
					echo ' ';
					if ( $link['selected'] ) {
						echo '<b>';
					} else {
						echo '<a href="'.$link['link'].'">';
					}
					echo GameVersionLocale::GetUserFriendlyLongNameFromCompare( $link['compare'], $g );
					if ( $link['selected'] ) {
						echo '</b>';
					} else {
						echo '</a>';
					}
				}
			}
			echo '<br />';
		}
	}

	public static function PrintVersionSelectShort( $urlHelper ) {
		$versions = GameVersionLocale::GetVersions();
		$groups = array();
		foreach ( $versions as $g ) {
			$groups[$g->shortName][] = $g;
		}

		$first = true;
		foreach ( $groups as $shortName => $group ) {
			if ( $first ) {
				$first = false;
			} else {
				echo ' - ';
			}

			echo $shortName;
			foreach ( $group as $g ) {
				$links = array();
				foreach ( $g->validCompares as $c ) {
					if ( $c === 'c1' ) { continue; } // hack so we don't have two compares in the short version select...
					$selected = $urlHelper->version === $g->version && $urlHelper->locale === $g->locale && $urlHelper->compare === $c;
					$links[] = [
						'selected' => $selected,
						'compare' => $c,
						'link' => $urlHelper->WithVersion($g->version)->WithLocale($g->locale)->WithCompare($c)->GetUrl()
					];
				}

				if ( count($links) === 1 ) {
					foreach ( $links as $link ) {
						echo ' ';
						if ( $link['selected'] ) {
							echo '<b>';
						} else {
							echo '<a href="'.$link['link'].'">';
						}
						echo GameVersionLocale::GetUserFriendlyShortNameFromCompare( $link['compare'], $g );
						if ( $link['selected'] ) {
							echo '</b>';
						} else {
							echo '</a>';
						}
					}
				} else {
					foreach ( $links as $link ) {
						echo ' ';
						if ( $link['selected'] ) {
							echo '<b>';
						} else {
							echo '<a href="'.$link['link'].'">';
						}
						echo GameVersionLocale::GetUserFriendlyShortNameFromCompare( $link['compare'], $g );
						if ( $link['selected'] ) {
							echo '</b>';
						} else {
							echo '</a>';
						}
					}
				}
			}
		}
	}
}

class VesperiaUrlHelper {
	// all of these are set to boolean 'false' if unset unless otherwise noted
	var $version;    // string; game version
	var $locale;     // string; game locale
	var $compare;    // string; 1 language only / both / which links
	var $section;    // string; type of content to show
	var $category;   // integer; category of items or enemy
	var $icon;       // integer; icon of items
	var $character;  // integer; single player character for artes/skills
	var $id;         // integer; individual item/enemy/recipe/whatever
	var $name;       // string; scenario or skit name
	var $mapletter;  // integer; range 0-5; necropolis stratum
	var $mapfloor;   // integer; range 1-10; necropolis floor in stratum
	var $enemies;    // boolean; true to show necropolis maps with enemies instead of general data
	var $query;      // string; search query
	var $page;       // integer; page index
	var $perpage;    // integer; amount of items per page
	var $diff;       // boolean; true to mark diffs to 360 version on ps3 content (where implemented)
	var $jump;       // boolean; true to show the scenario jumper next to scenario text; true by default!
	var $filtered;   // boolean; true to filter out things like unused items and generic artes; true by default!

	function __construct( $version, $locale, $compare, $section, $category, $icon, $character, $id, $name, $mapletter, $mapfloor, $enemies, $query, $page, $perpage, $diff, $jump, $filtered ) {
		$this->version = $version;
		$this->locale = $locale;
		$this->compare = $compare;
		$this->section = $section;
		$this->category = $category;
		$this->icon = $icon;
		$this->character = $character;
		$this->id = $id;
		$this->name = $name;
		$this->mapletter = $mapletter;
		$this->mapfloor = $mapfloor;
		$this->enemies = $enemies;
		$this->query = $query;
		$this->page = $page;
		$this->perpage = $perpage;
		$this->diff = $diff;
		$this->jump = $jump;
		$this->filtered = $filtered;
	}

	public static function FromGetParams( $args ) {
		$version = 'ps3p';
		$locale = 'jp';
		$compare = 'c2';
		if ( !GameVersionLocale::ParseVersion( $version, $locale, $compare, $args ) ) {
			die();
		}

		$section = false;
		if ( isset($args['section']) ) {
			$section = $args['section'];
		}
		$id = false;
		if ( isset($args['id']) ) {
			$id = (int)$args['id'];
		}
		$category = false;
		if ( isset($args['category']) ) {
			$category = (int)$args['category'];
		}
		$icon = false;
		if ( isset($args['icon']) ) {
			$icon = (int)$args['icon'];
		}
		$character = false;
		if ( isset($args['character']) ) {
			$character = (int)$args['character'];
		}
		$name = false;
		if ( isset($args['name']) ) {
			$name = $args['name'];
		}
		$query = false;
		if ( isset($args['query']) ) {
			$query = $args['query'];
		}
		$page = false;
		if ( isset($args['page']) ) {
			$page = (int)$args['page'];
			if ( $page < 1 ) {
				$page = 1;
			}
		}
		$perPage = false;
		if ( isset($args['perpage']) ) {
			$perPage = (int)$args['perpage'];
		}
		$markVersionDifferences = false;
		if ( isset($args['diff']) && $args['diff'] === 'true' ) {
			$markVersionDifferences = true;
		}
		$showScenarioJumper = true;
		if ( isset($args['jump']) && $args['jump'] === 'false' ) {
			$showScenarioJumper = false;
		}

		$map_letter_digit = false;
		$map_number = false;
		if ( isset($args['map']) ) {
			$map = $args['map'];
			$map_letter = substr($map, 0, 1);
			$map_letter_digit = ord($map_letter) - ord('A');
			if ( $map_letter_digit < 0 ) {
				$map_letter_digit = 0;
			}
			if ( $map_letter_digit > 5 ) {
				$map_letter_digit = 5;
			}
			$map_number = (int)substr($map, 1);
			if ( $map_number < 1 ) {
				$map_number = 1;
			}
			if ( $map_number > 10 ) {
				$map_number = 10;
			}
		}
		$enemies = false;
		if ( isset($args['enemies']) ) {
			$enemies = $args['enemies'] === 'true';
		}
		$filtered = true;
		if ( isset($args['filtered']) && $args['filtered'] === 'false' ) {
			$filtered = false;
		}
		
		return new VesperiaUrlHelper( $version, $locale, $compare, $section, $category, $icon, $character, $id, $name, $map_letter_digit, $map_number, $enemies, $query, $page, $perPage, $markVersionDifferences, $showScenarioJumper, $filtered );
	}

	function WithVersion   ( $d ) { return new VesperiaUrlHelper( $d,             $this->locale, $this->compare, $this->section, $this->category, $this->icon, $this->character, $this->id, $this->name, $this->mapletter, $this->mapfloor, $this->enemies, $this->query, $this->page, $this->perpage, $this->diff, $this->jump, $this->filtered ); }
	function WithLocale    ( $d ) { return new VesperiaUrlHelper( $this->version, $d           , $this->compare, $this->section, $this->category, $this->icon, $this->character, $this->id, $this->name, $this->mapletter, $this->mapfloor, $this->enemies, $this->query, $this->page, $this->perpage, $this->diff, $this->jump, $this->filtered ); }
	function WithCompare   ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $d            , $this->section, $this->category, $this->icon, $this->character, $this->id, $this->name, $this->mapletter, $this->mapfloor, $this->enemies, $this->query, $this->page, $this->perpage, $this->diff, $this->jump, $this->filtered ); }
	function WithSection   ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $d            , false,           false,       false,            false,     false,       false,            false,           false,          $this->query, false,       false,          $this->diff, $this->jump, $this->filtered ); } // this clears section-specific params automatically too
	function WithCategory  ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $d             , $this->icon, $this->character, $this->id, $this->name, $this->mapletter, $this->mapfloor, $this->enemies, $this->query, $this->page, $this->perpage, $this->diff, $this->jump, $this->filtered ); }
	function WithIcon      ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $this->category, $d         , $this->character, $this->id, $this->name, $this->mapletter, $this->mapfloor, $this->enemies, $this->query, $this->page, $this->perpage, $this->diff, $this->jump, $this->filtered ); }
	function WithCharacter ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $this->category, $this->icon, $d              , $this->id, $this->name, $this->mapletter, $this->mapfloor, $this->enemies, $this->query, $this->page, $this->perpage, $this->diff, $this->jump, $this->filtered ); }
	function WithId        ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $this->category, $this->icon, $this->character, $d       , $this->name, $this->mapletter, $this->mapfloor, $this->enemies, $this->query, $this->page, $this->perpage, $this->diff, $this->jump, $this->filtered ); }
	function WithName      ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $this->category, $this->icon, $this->character, $this->id, $d         , $this->mapletter, $this->mapfloor, $this->enemies, $this->query, $this->page, $this->perpage, $this->diff, $this->jump, $this->filtered ); }
	function WithMapLetter ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $this->category, $this->icon, $this->character, $this->id, $this->name, $d              , $this->mapfloor, $this->enemies, $this->query, $this->page, $this->perpage, $this->diff, $this->jump, $this->filtered ); }
	function WithMapFloor  ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $this->category, $this->icon, $this->character, $this->id, $this->name, $this->mapletter, $d             , $this->enemies, $this->query, $this->page, $this->perpage, $this->diff, $this->jump, $this->filtered ); }
	function WithEnemies   ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $this->category, $this->icon, $this->character, $this->id, $this->name, $this->mapletter, $this->mapfloor, $d            , $this->query, $this->page, $this->perpage, $this->diff, $this->jump, $this->filtered ); }
	function WithQuery     ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $this->category, $this->icon, $this->character, $this->id, $this->name, $this->mapletter, $this->mapfloor, $this->enemies, $d          , $this->page, $this->perpage, $this->diff, $this->jump, $this->filtered ); }
	function WithPage      ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $this->category, $this->icon, $this->character, $this->id, $this->name, $this->mapletter, $this->mapfloor, $this->enemies, $this->query, $d         , $this->perpage, $this->diff, $this->jump, $this->filtered ); }
	function WithPerPage   ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $this->category, $this->icon, $this->character, $this->id, $this->name, $this->mapletter, $this->mapfloor, $this->enemies, $this->query, $this->page, $d            , $this->diff, $this->jump, $this->filtered ); }
	function WithDiff      ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $this->category, $this->icon, $this->character, $this->id, $this->name, $this->mapletter, $this->mapfloor, $this->enemies, $this->query, $this->page, $this->perpage, $d         , $this->jump, $this->filtered ); }
	function WithJump      ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $this->category, $this->icon, $this->character, $this->id, $this->name, $this->mapletter, $this->mapfloor, $this->enemies, $this->query, $this->page, $this->perpage, $this->diff, $d         , $this->filtered ); }
	function WithFiltered  ( $d ) { return new VesperiaUrlHelper( $this->version, $this->locale, $this->compare, $this->section, $this->category, $this->icon, $this->character, $this->id, $this->name, $this->mapletter, $this->mapfloor, $this->enemies, $this->query, $this->page, $this->perpage, $this->diff, $this->jump, $d              ); }

	function MapLetterAsLetter() {
		if ($this->mapletter !== false && $this->mapletter >= 0 && $this->mapletter < 6) {
			return chr(65 + $this->mapletter);
		} else {
			return 'A';
		}
	}

	function GetUrl() {
		$link = '?version='.$this->version;
		$link .= '&locale='.$this->locale;
		$link .= '&compare='.$this->compare;
		if ( $this->section !== false ) {
			$link .= '&section='.$this->section;
		}
		if ( $this->category !== false ) {
		$link .= '&category='.$this->category;
		}
		if ( $this->icon !== false ) {
			$link .= '&icon='.$this->icon;
		}
		if ( $this->character !== false ) {
			$link .= '&character='.$this->character;
		}
		if ( $this->id !== false ) {
			$link .= '&id='.$this->id;
		}
		if ( $this->name !== false ) {
			$link .= '&name='.$this->name;
		}
		if ( $this->mapletter !== false && $this->mapfloor !== false ) {
			$letter = $this->MapLetterAsLetter();
			$link .= '&map='.$letter.$this->mapfloor;
		}
		if ( $this->enemies === true ) {
			$link .= '&enemies=true';
		}
		if ( $this->query !== false ) {
			$link .= '&query='.urlencode($this->query);
		}
		if ( $this->page !== false ) {
			$link .= '&page='.$this->page;
		}
		if ( $this->perpage !== false ) {
			$link .= '&perpage='.$this->perpage;
		}
		if ( $this->diff === true ) {
			$link .= '&diff=true';
		}
		if ( $this->jump === false ) {
			$link .= '&jump=false';
		}
		if ( $this->filtered === false ) {
			$link .= '&filtered=false';
		}
		return $link;
	}
}

?>