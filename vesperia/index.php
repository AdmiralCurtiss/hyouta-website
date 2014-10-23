<?php
header('Content-Type: text/html; charset=UTF-8');

require_once '../twig/vendor/autoload.php';
require_once 'db.class.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
	'debug' => true,
	'cache' => '../twig/compilation_cache',
));

include 'credentials.php';
include 'icons.php';
$db = new db( $__db_connstr_360__, $__db_username__, $__db_password__ );

$section = 'index';
if ( isset($_GET['section']) ) {
	$section = $_GET['section'];
}

if ( $section === 'artes' ) {
	$id = false;
	if ( isset($_GET['id']) ) { $id = (int)$_GET['id']; }
	
	$artes = $db->GetArtes( $id );
	echo $twig->render( 'artes.html', array( 'Artes' => $artes, 'ArteIconIds' => $ArteIconIds, 'CharIcons' => $CharIcons ) );
} else {
	echo 'Undefined.';
}



?>