<?php
require_once 'util.php';

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
	
	function Render( $version, $locale, $compare, $markVersionDifferences ) {
	?>
<div class="storyLine">
<?php if ( WantsJp($compare) ) { ?>
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
<?php } ?>
<?php if ( WantsEn($compare) ) { ?>
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
<?php } ?>
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
	var $descriptionJ;
	var $descriptionE;
	var $changeStatus;
	
	function __construct( $id, $type, $sceneGroup, $parentId, $episodeId, $descriptionJ, $descriptionE, $changeStatus ) {
		$this->id             = $id;
		$this->type           = $type;
		$this->sceneGroup     = $sceneGroup;
		$this->parentId       = $parentId;
		$this->episodeId      = $episodeId;
		$this->descriptionJ   = $descriptionJ;
		$this->descriptionE   = $descriptionE;
		$this->changeStatus   = $changeStatus;
	}
	
	function GetDescriptionShort( $compare ) {
		if ( $compare === '1' ) { return $this->descriptionJ; }
		if ( $compare === '2' ) { return $this->descriptionE; }
		if ( $compare === 'c1' ) { return $this->descriptionJ; }
		if ( $compare === 'c2' ) { return $this->descriptionE; }
		die();
	}
	function GetDescriptionLong( $compare ) {
		if ( $compare === '1' ) { return $this->descriptionJ; }
		if ( $compare === '2' ) { return $this->descriptionE; }
		if ( $this->descriptionJ === $this->descriptionE ) { return $this->descriptionJ; }
		if ( $compare === 'c1' ) { return $this->descriptionJ.' ('.$this->descriptionE.')'; }
		if ( $compare === 'c2' ) { return $this->descriptionE.' ('.$this->descriptionJ.')'; }
		die();
	}
	
	public static function RenderIndex( $version, $locale, $compare, $scenarioMetadata, $markVersionDifferences, $currentEpisodeId = null ) {
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
				echo '<span class="scenario-selected';
				if ( $markVersionDifferences ) {
					echo ' changeStatusIndex'.$scene->changeStatus;
				}
				echo '">'.$scene->GetDescriptionLong($compare).'</span>';
			} else {
				if ( $currDepth === 2 ) {
					echo '<a href="?version='.$version.'&locale='.$locale.'&compare='.$compare.'&section=scenario&name='.$scene->episodeId;
				} else if ( $currDepth === 3 ) {
					echo '<a href="?version='.$version.'&locale='.$locale.'&compare='.$compare.'&section=skit&name='.$scene->episodeId;
				}
				if ( $currDepth === 2 || $currDepth === 3 ) {
					if ( $markVersionDifferences ) {
						echo '&diff=true';
					}
					echo '"';
					if ( $markVersionDifferences ) {
						echo ' class="changeStatusIndex'.$scene->changeStatus.'"';
					}
					echo '>';
				}
				
				echo $scene->GetDescriptionLong($compare);
				
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
	
	public static function RenderPreviousNext( $version, $locale, $compare, $scenarioMetadata, $currentEpisodeId, $top, $markVersionDifferences ) {
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
					$previousNextText .= '<span class="scenario-previous';
					if ( $markVersionDifferences ) {
						$previousNextText .= ' changeStatusIndex'.$s->changeStatus;
					}
					$previousNextText .= '"><a href="?version='.$version.'&locale='.$locale.'&compare='.$compare.'&section=scenario&name='.$s->episodeId.( $markVersionDifferences ? '&diff=true' : '' ).'">'.$s->GetDescriptionShort($compare).'</a></span>';
					$previousNextText .= ' - ';
				}
				
				// print current
				$previousNextText .= '<span class="scenario-selected';
				if ( $markVersionDifferences ) {
					$previousNextText .= ' changeStatusIndex'.$scene->changeStatus;
				}
				$previousNextText .= '">'.$scene->GetDescriptionShort($compare).'</span>';
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
					$previousNextText .= '<span class="scenario-next';
					if ( $markVersionDifferences ) {
						$previousNextText .= ' changeStatusIndex'.$scene->changeStatus;
					}
					$previousNextText .= '"><a href="?version='.$version.'&locale='.$locale.'&compare='.$compare.'&section=scenario&name='.$scene->episodeId.( $markVersionDifferences ? '&diff=true' : '' ).'">'.$scene->GetDescriptionShort($compare).'</a></span>';
					break;
				}
			} else if ( $scene->parentId === $sceneId ) {
				// skit
				$currDepth = 3;
			}
		}
		$previousNextText .= '</div>';

		echo $previousNextText;
	}
}
?>