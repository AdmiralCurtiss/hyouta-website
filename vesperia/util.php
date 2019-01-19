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

	public static function PrintVersionSelectLong( $version, $locale, $compare ) {
		$versions = GameVersionLocale::GetVersions();
		foreach ( $versions as $g ) {
			$links = array();
			foreach ( $g->validCompares as $c ) {
				$selected = $version === $g->version && $locale === $g->locale && $compare === $c;
				$links[] = [
					'selected' => $selected,
					'compare' => $c,
					'link' => '?version='.$g->version.'&locale='.$g->locale.'&compare='.$c
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

	public static function PrintVersionSelectShort( $version, $locale, $compare ) {
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
					$selected = $version === $g->version && $locale === $g->locale && $compare === $c;
					$links[] = [
						'selected' => $selected,
						'compare' => $c,
						'link' => '?version='.$g->version.'&locale='.$g->locale.'&compare='.$c
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

?>