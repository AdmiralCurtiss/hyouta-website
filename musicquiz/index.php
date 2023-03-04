<?php
	error_reporting(E_ALL);
	//page generation time code
		$time = explode(' ', microtime());
		$pagegen_start_time = $time[1] + $time[0];
	//page generation time code end

	require_once '../credentials.php';
	include 'session.php';
	$database = new db( $__db_connstr_music__, $__db_username_music__, $__db_password_music__ );
	$session = new Session( $database );

	$section = array();
	$section['main']     = 'main.php';
	$section['error']    = 'error.php';
	$section['faq']      = 'faq.php';
	if ( $session->logged_in ) {
		$section['guess']    = 'guess.php';
		$section['results']  = 'results.php';
		$section['edituser'] = 'useredit.php';
		$section['skipped']  = 'showskipnum.php';
		$section['songlist'] = 'percentage.php';
	} else {
		$section['register'] = 'register_form.php';
	}

	$section['vgmoftheday'] = 'vgmusicoftheday.php';

	if ( $session->logged_in && $session->user->is_vgmusicoftheday() ) {
		$section['vgmotd-urladd'] = 'vgmusicoftheday_addurl.php';
		$section['vgmotd-add-edit'] = 'vgmusicoftheday_add_edit.php';
	}

	if ( $session->logged_in && $session->user->is_admin() ) {
		$section['songadd']    = 'songadd.php';
		$section['songedit']   = 'songedit.php';
		$section['nameedit']   = 'nameedit.php';
		$section['resultedit'] = 'changepts.php';
		$section['resulthide'] = 'hidefromall.php';
		$section['edit']       = 'newsedit.php';
		$section['newsadd']    = 'newsadd.php';
		$section['changeyt']   = 'changeyturl.php';
	}

	if ( isset($_GET['section'], $section[$_GET['section']]) ) {
		$section_now = $_GET['section'];
	} else {
		$section_now = 'main';
	}

	if ( !isset($section[$section_now]) ) {
		$section_now = 'error';
	}

	include 'constants.php';
	include 'header.php';
	echo '<div id="menu">';
	include 'menu.php';
	echo '</div><div id="main">';
	include $section[$section_now];
	echo '</div><!--[if IE 8]>';
	echo '<div class="login-ie"><b><i>NOTE: Layout doesn&#39;t display properly in Internet Explorer 8</i></b>';
	echo '<![endif]-->';
	echo '<!--[if !IE]>-->';
	echo '<div class="login">';
	echo '<!--<![endif]-->';
	include 'userarea.php';
	echo '</div>';

	//page generation time code
		$time = explode(' ', microtime());
		$totaltime = (($time[1] + $time[0]) - $pagegen_start_time);
		echo '<div id="footer_time">Page generated in '.round($totaltime, 3).' seconds.</div>';
	//page generation time code end
?>
</body>
</html>