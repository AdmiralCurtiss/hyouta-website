<?php
class stringDicEntry {
	var $gameId;
	var $jpText;
	var $enText;
	
	function __construct( $gameId, $jpText, $enText ) {
		$this->gameId = $gameId;
		$this->jpText = $jpText;
		$this->enText = $enText;
	}
	
	function Render() {
	?>
<div class="storyLine">
	<div class="storyBlock">
		<div class="storyText">
		<div class="storyTextSub">
			<div class="textJP textContainerSub"><?php echo $this->jpText ?></div>
		</div>
		</div>
	</div>
	<div class="storyBlock">
		<div class="storyText">
		<div class="storyTextSub">
			<div class="textEN textContainerSub"><?php echo $this->enText ?></div>
		</div>
		</div>
	</div>
</div>
	<?php
	}
}
?>