<?php
class skitLine {
	var $skitId;
	var $jpChar;
	var $enChar;
	var $jpText;
	var $enText;
	
	function __construct( $skitId, $jpChar, $jpText, $enChar, $enText ) {
		$this->skitId = $skitId;
		$this->jpChar = $jpChar;
		$this->enChar = $enChar;
		$this->jpText = $jpText;
		$this->enText = $enText;
	}
	
	function GetEnName() {
		switch ( $this->enChar ) {
			case 'ALL':   return 'Everyone';
			case 'BAU':   return 'Ba\'ul';
			case 'EST':   return 'Estellise';
			case 'EST_P': return 'Estelle';
			case 'FRE':   return 'Flynn';
			case 'JUD':   return 'Judith';
			case 'KAR':   return 'Karol';
			case 'PAT':   return 'Patty';
			case 'RAP':   return 'Repede';
			case 'RAV':   return 'Raven';
			case 'RIT':   return 'Rita';
			case 'YUR':   return 'Yuri';
		}
	}
	
	function GetJpName() {
		switch ( $this->jpChar ) {
			case 'ALL':   return 'みんな';
			case 'BAU':   return 'バウル';
			case 'EST':   return 'エステリーゼ';
			case 'EST_P': return 'エステル';
			case 'FRE':   return 'フレン';
			case 'JUD':   return 'ジュディス';
			case 'KAR':   return 'カロル';
			case 'PAT':   return 'パティ';
			case 'RAP':   return 'ラピード';
			case 'RAV':   return 'レイヴン';
			case 'RIT':   return 'リタ';
			case 'YUR':   return 'ユーリ';
		}
	}
	
	function Render() {
	?>
<div class="storyLine">
	<div class="skitIconAndText">
		<div class="skitIcon"><img src="chara-icons/<?php echo substr( $this->jpChar, 0, 3 ); ?>.png" /></div>
		<div class="skitBlock">
			<div class="skitText">
				<div class="charaContainerSkit">
					<div class="charaSubContainerSkit">
						<div class="charaSubSubContainerSkit"><?php echo $this->GetJpName() ?></div>
					</div>
				</div>
				<div class="textJP textContainerSubSkit"><?php echo $this->jpText ?></div>
			</div>
		</div>
	</div>
	<div class="skitIconAndText">
		<div class="skitIcon"><img src="chara-icons/<?php echo substr( $this->enChar, 0, 3 ); ?>.png" /></div>
		<div class="skitBlock">
			<div class="skitText">
				<div class="charaContainerSkit">
					<div class="charaSubContainerSkit">
						<div class="charaSubSubContainerSkit"><?php echo $this->GetEnName() ?></div>
					</div>
				</div>
				<div class="textEN textContainerSubSkit"><?php echo $this->enText ?></div>
			</div>
		</div>
	</div>
</div>
	<?php
	}
}

class skitMeta {
	var $category;
	var $characterBitmask;
	var $jpName;
	var $enName;
	var $jpCond;
	var $enCond;
	
	function __construct( $category, $characterBitmask, $jpName, $enName, $jpCond, $enCond ) {
		$this->category = $category;
		$this->characterBitmask = $characterBitmask;
		$this->jpName = $jpName;
		$this->enName = $enName;
		$this->jpCond = $jpCond;
		$this->enCond = $enCond;
	}
}
?>