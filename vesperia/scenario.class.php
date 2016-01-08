<?php
class scenario {
	var $episodeId;
	var $type;
	var $jpName;
	var $jpText;
	var $enName;
	var $enText;
	var $changeStatus;
	
	function __construct( $episodeId, $type, $jpName, $jpText, $enName, $enText, $changeStatus ) {
		$this->episodeId = $episodeId;
		$this->type = $type;
		$this->jpName = $jpName;
		$this->jpText = $jpText;
		$this->enName = $enName;
		$this->enText = $enText;
		$this->changeStatus = $changeStatus;
	}
	
	function Render( $markVersionDifferences ) {
	?>
<div class="storyLine">
	<div class="storyBlock">
		<?php if ( $this->jpText !== '' ) { ?>
		<div class="storyText<?php if ( $this->type > 0 ) { echo $this->type; } ?>">
		<div class="storyTextSub<?php if ( $this->type > 0 ) { echo $this->type; } ?>">
			<div class="charaContainer<?php if ( $this->type > 0 ) { echo $this->type; } ?>">
				<div class="charaSubContainer<?php if ( $this->type > 0 ) { echo $this->type; } ?>">
					<div class="charaSubSubContainer<?php if ( $this->type > 0 ) { echo $this->type; } ?>"><?php echo $this->jpName ?></div>
				</div>
			</div>
			<div class="textJP textContainerSub<?php
				if ( $this->type > 0 ) { echo $this->type; }
				if ( $markVersionDifferences ) { echo ' changeStatus'.$this->changeStatus; }
			?>"><?php
				echo $this->jpText;
			?></div>
		</div>
		</div>
		<?php } ?>
	</div>
	<div class="storyBlock">
		<?php if ( $this->enText !== '' ) { ?>
		<div class="storyText<?php if ( $this->type > 0 ) { echo $this->type; } ?>">
		<div class="storyTextSub<?php if ( $this->type > 0 ) { echo $this->type; } ?>">
			<div class="charaContainer<?php if ( $this->type > 0 ) { echo $this->type; } ?>">
				<div class="charaSubContainer<?php if ( $this->type > 0 ) { echo $this->type; } ?>">
					<div class="charaSubSubContainer<?php if ( $this->type > 0 ) { echo $this->type; } ?>"><?php echo $this->enName ?></div>
				</div>
			</div>
			<div class="textEN textContainerSub<?php
				if ( $this->type > 0 ) { echo $this->type; }
				if ( $markVersionDifferences ) { echo ' changeStatus'.$this->changeStatus; }
			?>"><?php
				echo $this->enText;
			?></div>
		</div>
		</div>
		<?php } ?>
	</div>
</div>
	<?php
	}
}

class scenarioMeta {
	var $id;
	var $type;
	var $sceneGroup;
	var $parentId;
	var $episodeId;
	var $description;
	var $changeStatus;
	
	function __construct( $id, $type, $sceneGroup, $parentId, $episodeId, $description, $changeStatus ) {
		$this->id             = $id;
		$this->type           = $type;
		$this->sceneGroup     = $sceneGroup;
		$this->parentId       = $parentId;
		$this->episodeId      = $episodeId;
		$this->description    = $description;
		$this->changeStatus   = $changeStatus;
	}
	
	public static function RenderIndex( $version, $scenarioMetadata, $markVersionDifferences, $currentEpisodeId = null ) {
		$categoryId = null;
		$sceneId = null;
		$currDepth = 0;
		$prevDepth = 0;
		
		echo '<div class="scenario-index'.( $currentEpisodeId !== null ? ' scenario-index-sub' : '' ).'">';
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
				echo '</ul><ul><li>';
			}
			
			if ( $currentEpisodeId !== null && $currentEpisodeId === $scene->episodeId ) {
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
		}
		
		while ( $prevDepth > 1 ) {
			echo '</li></ul>';
			$prevDepth--;
		}
		
		echo '</div>';
	}
	
	public static function RenderPreviousNext( $version, $scenarioMetadata, $currentEpisodeId, $top, $allowVersionSelect ) {
		$categoryId = null;
		$sceneId = null;
		$currDepth = 0;
		$currentIndex = null;
		$previousIndex = null;
		
		$previousNextText = '<div class="scenario-previous-next">';
		foreach ( $scenarioMetadata as $index => $scene ) {
			if ( $currentEpisodeId === $scene->episodeId ) {
				// print previous scene if possible
				if ( $previousIndex !== null ) {
					$s = $scenarioMetadata[$previousIndex];
					$previousNextText .= '<span class="scenario-previous"><a href="?version='.$version.'&section=scenario&name='.$s->episodeId.'">'.$s->description.'</a></span>';
					$previousNextText .= ' - ';
				}
				
				// print current
				$previousNextText .= '<span class="scenario-selected">'.$scene->description.'</span>';
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
					$previousNextText .= ' - ';
					$previousNextText .= '<span class="scenario-next"><a href="?version='.$version.'&section=scenario&name='.$scene->episodeId.'">'.$scene->description.'</a></span>';
					break;
				}
			} else if ( $scene->parentId === $sceneId ) {
				// skit
				$currDepth = 3;
			}
		}
		$previousNextText .= '</div>';

		$versionSelect = '<div>';
		if ( $allowVersionSelect ) {
			$versionSelect .= '<a href="?version=360&section=scenario&name='.$currentEpisodeId.'&diff=true">360</a>';
			$versionSelect .= ' ';
			$versionSelect .= '<a href="?version=ps3&section=scenario&name='.$currentEpisodeId.'&diff=true">PS3</a>';
		}
		$versionSelect .= '</div>';

		if ( $allowVersionSelect && $top ) {
			echo $versionSelect;
		}

		echo $previousNextText;

		if ( $allowVersionSelect && !$top ) {
			echo $versionSelect;
		}
	}
}
?>