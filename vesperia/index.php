<?php
header('Content-Type: text/html; charset=UTF-8');

require_once 'db.class.php';
require_once 'header.php';
include 'credentials.php';
$db = new db( $__db_connstr_360__, $__db_username__, $__db_password__ );

$section = 'index';
if ( isset($_GET['section']) ) {
	$section = $_GET['section'];
}
$id = false;
if ( isset($_GET['id']) ) { $id = (int)$_GET['id']; }

if ( $section === 'artes' ) {
	print_header( $section );
	echo '<body>';
	echo '<table>';
	$artes = $db->GetArtesHtml( $id );
	$first = true;
	foreach ( $artes as $a ) {
		if ( $first === true ) { $first = false; } else {
			echo '<tr><td colspan="5"><hr></td></tr>';
		}
		echo $a;
	}
	echo '</table>';
	echo '</body>';
	echo '</html>';
} else {
	echo 'Undefined.';
}



?>