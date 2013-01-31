<?php
	header('Content-type: text/html; charset=iso-8859-1');
?><html>
<head>
	<!-- <title>Mystery Site</title> -->
	<title>Video Game Music Quiz</title>
	<link rel="stylesheet" type="text/css" href="layout.css" />
	<link rel="stylesheet" type="text/css" href="styles.css" media="all">
	<link rel="stylesheet" type="text/css" href="screen.css" media="screen">
	<link rel="stylesheet" type="text/css" href="print.css" media="print">
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" /><?php
	if ( $session->logged_in && $session->user->is_admin() ) {
		echo '<script type="text/javascript" src="result.js"></script>';
	}
	if ( isset($_GET['section']) && $_GET['section'] == 'guess' ) {
		echo '<script type="text/javascript" src="autofocus.js"></script>';
	}
?></head>
<body<?php 	if ( isset($_GET['section']) && $_GET['section'] == 'guess' ) echo ' onload="setFocus()"'; ?>>
	<div id="logo">
		<img src="images/logo.png" style="float: left; margin: 4px;" /> ~Video Game Music Quiz~
		<div class="smallheader">Shoot for the moon! If you miss you will still be among the stars.</div>
	</div>
	
