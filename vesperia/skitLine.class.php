<?php
class skitLine {
	var $skitId;
	var $jpChar;
	var $enChar;
	var $jpText;
	var $enText;
	var $changeStatus;
	
	function __construct( $skitId, $jpChar, $jpText, $enChar, $enText, $changeStatus ) {
		$this->skitId = $skitId;
		$this->jpChar = $jpChar;
		$this->enChar = $enChar;
		$this->jpText = $jpText;
		$this->enText = $enText;
		$this->changeStatus = $changeStatus;
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
	
	function RenderIcon( $char ) {
		$c = substr( $char, 0, 3 );
		if ( !( $c === 'ALL' || $c === 'BAU' ) ) {
			echo '<img src="chara-icons/';
			echo $c;
			echo '.png" />';
		}
	}
	
	function Render( $markVersionDifferences ) {
	?>
<div class="storyLine">
	<div class="skitIconAndText">
		<div class="skitIcon"><?php $this->RenderIcon( $this->jpChar ); ?></div>
		<div class="skitBlock">
			<div class="skitText">
				<div class="charaContainerSkit">
					<div class="charaSubContainerSkit">
						<div class="charaSubSubContainerSkit"><?php echo $this->GetJpName() ?></div>
					</div>
				</div>
				<div class="textJP textContainerSubSkit<?php
					if ( $markVersionDifferences ) { echo ' changeStatusSkit'.$this->changeStatus; }
				?>"><?php
					echo $this->jpText;
				?></div>
			</div>
		</div>
	</div>
	<div class="skitIconAndText">
		<div class="skitIcon"><?php $this->RenderIcon( $this->enChar ); ?></div>
		<div class="skitBlock">
			<div class="skitText">
				<div class="charaContainerSkit">
					<div class="charaSubContainerSkit">
						<div class="charaSubSubContainerSkit"><?php echo $this->GetEnName() ?></div>
					</div>
				</div>
				<div class="textEN textContainerSubSkit<?php
					if ( $markVersionDifferences ) { echo ' changeStatusSkit'.$this->changeStatus; }
				?>"><?php
					echo $this->enText;
				?></div>
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

class skitMetaForIndex {
	var $skitId;
	var $category;
	var $jpName;
	var $enName;
	var $charHtml;
	var $changeStatus;

	function __construct( $skitId, $category, $jpName, $enName, $charHtml, $changeStatus ) {
		$this->skitId = $skitId;
		$this->category = $category;
		$this->jpName = $jpName;
		$this->enName = $enName;
		$this->charHtml = $charHtml;
		$this->changeStatus = $changeStatus;
	}
}

?>