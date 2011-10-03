<?php
if ( !isset( $session ) ) {
	die();
}
?><ul id="menulist">
<?php
	$menu=array();
	$menu['news']     = 'News';
	$menu['faq']      = 'FAQ';
	
	if ( $session->logged_in ) {
		$menu['guess']    = 'Guess Songs';
		$menu['results']  = 'Already guessed';
		$menu['songlist'] = '%';
	} else {
		$menu['register'] = 'Register';
	}
	
	if ( $session->logged_in && $session->user->is_vgmusicoftheday() ) {
		$menu['vgmoftheday'] = 'VGMusic of the Day';
	}
	
	if ( $session->logged_in && $session->user->is_admin() ) {
		$menu['songadd']  = 'Add new Song';
		$menu['songedit'] = 'Change existing Song';
		$menu['newsadd']  = 'Add news';
	}

	foreach ($menu as $sectname => $option) {
		echo '<li><a href="index.php?section='.$sectname.'"';
		if ($sectname == $section_now) {
			echo ' class="current"';
		}
		echo '>'.$option.'</a></li>';
	}
?>
</ul>
