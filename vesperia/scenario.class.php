<?php
class scenario {
	var $type;
	var $jpName;
	var $jpText;
	var $enName;
	var $enText;
	
	function __construct( $type, $jpName, $jpText, $enName, $enText ) {
		$this->type = $type;
		$this->jpName = $jpName;
		$this->jpText = $jpText;
		$this->enName = $enName;
		$this->enText = $enText;
	}
}

class scenarioMeta {
	var $id;
	var $type;
	var $sceneGroup;
	var $parentId;
	var $episodeId;
	var $description;
	
	function __construct( $id, $type, $sceneGroup, $parentId, $episodeId, $description ) {
		$this->id          = $id;
		$this->type        = $type;
		$this->sceneGroup  = $sceneGroup;
		$this->parentId    = $parentId;
		$this->episodeId   = $episodeId;
		$this->description = $description;
	}
	
	// TODO: THIS IS STILL BROKEN IN SOME WAY CAUSE THE FIRST UL AND THE LATTER ONES DON'T MATCH SOMEHOW
	public static function RenderIndex( $version, $scenarioMetadata, $currentEpisodeId = null ) {
		$categoryId = null;
		$sceneId = null;
		$currDepth = 0;
		$prevDepth = 0;
		$shiftdown = false; // this is dumb but this nested HTML generation is far more complicated than it should be so fuck it, this works
		
		echo '<div class="scenario-index">';
		foreach ( $scenarioMetadata as $scene ) {
			if ( $scene->parentId === null ) {
				// category header
				$categoryId = $scene->id;
				$currDepth = 1;
			} else if ( $scene->parentId === $categoryId ) {
				// scene
				$sceneId = $scene->id;
				$currDepth = 2;
			} else if ( $scene->parentId === $sceneId ) {
				// skit
				$currDepth = 3;
			}
			
			if ( $prevDepth === $currDepth ) {
				echo '</li><li>';
			}
			while ( $prevDepth < $currDepth ) {
				echo '<ul><li>';
				$prevDepth++;
			}
			if ( $prevDepth > $currDepth ) {
				while ( $prevDepth > $currDepth ) {
					echo '</li></ul>';
					$prevDepth--;
				}
				echo '<li>';
				$shiftdown = true;
			}
			
			if ( $currentEpisodeId === $scene->episodeId ) {
				echo '<span class="scenario-selected">'.$scene->description.'</span>';
			} else {
				if ( $currDepth === 2 ) {
					echo '<a href="?version='.$version.'&section=scenario&name='.$scene->episodeId.'">';
				} else if ( $currDepth === 3 ) {
					echo '<a href="?version='.$version.'&section=skit&name='.$scene->episodeId.'">';
				}
				
				echo $scene->description;
				
				if ( $currDepth >= 2 ) {
					echo '</a>';
				}
			}
			
			if ( $shiftdown ) {
				echo '</li>';
				$shiftdown = false;
			}
		}
		
		while ( $prevDepth > 1 ) {
			echo '</li></ul>';
			$prevDepth--;
		}
		
		echo '</div>';
	}
	
	public static function RenderPreviousNext( $version, $scenarioMetadata, $currentEpisodeId ) {
		$categoryId = null;
		$sceneId = null;
		$currDepth = 0;
		$currentIndex = null;
		$previousIndex = null;
		
		echo '<div class="scenario-previous-next">';
		foreach ( $scenarioMetadata as $index => $scene ) {
			if ( $currentEpisodeId === $scene->episodeId ) {
				// print previous scene if possible
				if ( $previousIndex !== null ) {
					$s = $scenarioMetadata[$previousIndex];
					echo '<span class="scenario-previous"><a href="?version='.$version.'&section=scenario&name='.$s->episodeId.'">'.$s->description.'</a></span>';
					echo ' - ';
				}
				
				// print current
				echo '<span class="scenario-selected">'.$scene->description.'</span>';
				$currentIndex = $index;
				
				$currentDepth = 2;
				continue;
			}
			
			if ( $scene->parentId === null ) {
				// category header
				$categoryId = $scene->id;
				$currDepth = 1;
			} else if ( $scene->parentId === $categoryId ) {
				// scene
				$sceneId = $scene->id;
				$currDepth = 2;
				
				if ( $currentIndex === null ) {
					$previousIndex = $index;
				} else {
					// print next
					echo ' - ';
					echo '<span class="scenario-next"><a href="?version='.$version.'&section=scenario&name='.$scene->episodeId.'">'.$scene->description.'</a></span>';
					break;
				}
			} else if ( $scene->parentId === $sceneId ) {
				// skit
				$currDepth = 3;
			}
		}
		echo '</div>';
	}
}
?>