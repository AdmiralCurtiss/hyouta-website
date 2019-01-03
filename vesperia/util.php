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
	var $longName;

	function __construct( $version, $locale, $defaultCompare, $validCompares, $longName ) {
		$this->version = $version;
		$this->locale = $locale;
		$this->defaultCompare = $defaultCompare;
		$this->validCompares = $validCompares;
		$this->longName = $longName;
	}

	public static function HasSearchPoints( $version ) {
		return $version === 'ps3v' || $version === 'ps3p';
	}

	public static function HasTrophies( $version ) {
		return $version === 'ps3v' || $version === 'ps3p';
	}

	public static function HasNecropolis( $version ) {
		return $version === 'ps3v' || $version === 'ps3p';
	}

	public static function HasPatty( $version ) {
		return $version === 'ps3v' || $version === 'ps3p';
	}

	public static function GetVersions() {
		$l = array();
		//                            version  locale  default compare  valid compares                 user friendly long name
		$l[] = new GameVersionLocale( '360u',  'us',   '2',             array( '1', '2', 'c1', 'c2' ), 'Xbox 360 North American Version'     );
		$l[] = new GameVersionLocale( '360e',  'uk',   '2',             array( '1', '2', 'c1', 'c2' ), 'Xbox 360 European Version - English' );
		$l[] = new GameVersionLocale( '360e',  'fr',   '2',             array( '1', '2', 'c1', 'c2' ), 'Xbox 360 European Version - French'  );
		$l[] = new GameVersionLocale( '360e',  'de',   '2',             array( '1', '2', 'c1', 'c2' ), 'Xbox 360 European Version - German'  );
		$l[] = new GameVersionLocale( 'ps3v',  'jp',   '1',             array( '1', '2', 'c1', 'c2' ), 'PlayStation 3 Japanese Version'      );
		$l[] = new GameVersionLocale( 'ps3p',  'jp',   'c2',            array( '1', '2', 'c1', 'c2' ), 'PlayStation 3 Fan-Translation'       );
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
			$version = 'ps3p';
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

	public static function GetUserFriendlyLongNameFromCompare( $compare ) {
		if ( $compare === '1'  ) { return '1st Language Only'; }
		if ( $compare === '2'  ) { return '2st Language Only'; }
		if ( $compare === 'c1' ) { return 'Both Languages (Links of 1st)'; }
		if ( $compare === 'c2' ) { return 'Both Languages (Links of 2nd)'; }
		return $compare;
	}

	public static function PrintVersionSelect( $version, $locale, $compare ) {
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
					if ( !$link['selected'] ) { echo '<a href="'.$link['link'].'">'; }
					echo $g->longName;
					if ( !$link['selected'] ) { echo '</a>'; }
				}
			} else {
				echo $g->longName;
				echo ':';
				foreach ( $links as $link ) {
					echo ' ';
					if ( !$link['selected'] ) { echo '<a href="'.$link['link'].'">'; }
					echo GameVersionLocale::GetUserFriendlyLongNameFromCompare( $link['compare'] );
					if ( !$link['selected'] ) { echo '</a>'; }
				}
			}
			echo '<br />';
		}
	}
}

?>