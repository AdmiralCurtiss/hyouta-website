<?php
	error_reporting(E_ALL);
	//page generation time code
		$time = explode(' ', microtime());
		$pagegen_start_time = $time[1] + $time[0];
	//page generation time code end
	
	$database = @mysql_connect('localhost', 'music', 'k3pK4vwUyTUT74pfXRJdhhXB') OR die(mysql_error());
	@mysql_select_db('musicquiz') OR die(mysql_error());
	include 'session.php';
	$session = new Session($database);
	
	$section = array();
	$section['main']     = 'main.php';
	$section['news']     = 'news.php';
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
	
	if ( $session->logged_in && $session->user->is_vgmusicoftheday() ) {
		$section['vgmoftheday'] = 'vgmusicoftheday.php';
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
	}

	if ( isset($_GET['section'], $section[$_GET['section']]) ) {
		$section_now = $_GET['section'];
	} else {
		$section_now = 'news';
	}
	
	if ( !isset($section[$section_now]) ) {
		$section_now = 'main';
	}
	
	include 'constants.php';
	include 'header.php';
	echo '<div id="menu">';
	include 'menu.php';
	echo '</div><div id="main">';	
	include $section[$section_now];
	echo
'</div><!--[if IE 8]>
<div class="login-ie"><b><i>NOTE: Layout doesn\'t display properly in Internet Explorer 8</i></b>
<![endif]-->
<!--[if !IE]>-->
<div class="login">
<!--<![endif]-->';
	include 'userarea.php';
	echo '</div>';
	mysql_close($database);
	
	//page generation time code
		$time = explode(' ', microtime());
		$totaltime = (($time[1] + $time[0]) - $pagegen_start_time);
		echo '<div id="footer_time">Page generated in '.round($totaltime, 3).' seconds.</div>';
	//page generation time code end
?>
</body>
</html>