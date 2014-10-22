<?php
header('Content-Type: text/html; charset=UTF-8');

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once '../twig/vendor/autoload.php';
require_once 'db.class.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
	'debug' => true,
	'cache' => '../twig/compilation_cache',
));

$db = new db();

$artes = $db->GetArtes();
$arteIconIds = array( 1 => "04", 2 => "05", 3 => "06", 4 => "00", 5 => "01", 6 => "02", 7 => "02", 8 => "12", 9 => "12", 10 => "02", 11 => "02", 13 => "02" );
$charIcons = array(
				0 => "<img src=\"chara-icons/YUR.png\" height=\"32\" width=\"24\" title=\"Yuri\">",
				1 => "<img src=\"chara-icons/EST.png\" height=\"32\" width=\"24\" title=\"Estelle\">",
				2 => "<img src=\"chara-icons/KAR.png\" height=\"32\" width=\"24\" title=\"Karol\">",
				3 => "<img src=\"chara-icons/RIT.png\" height=\"32\" width=\"24\" title=\"Rita\">",
				4 => "<img src=\"chara-icons/RAV.png\" height=\"32\" width=\"24\" title=\"Raven\">",
				5 => "<img src=\"chara-icons/JUD.png\" height=\"32\" width=\"24\" title=\"Judith\">",
				6 => "<img src=\"chara-icons/RAP.png\" height=\"32\" width=\"24\" title=\"Repede\">",
				7 => "<img src=\"chara-icons/FRE.png\" height=\"32\" width=\"24\" title=\"Flynn\">",
				8 => "<img src=\"chara-icons/PAT.png\" height=\"32\" width=\"24\" title=\"Patty\">"
			);


echo $twig->render( 'artes.html', array( 'Artes' => $artes, 'ArteIconIds' => $arteIconIds, 'CharIcons' => $charIcons ) );

?>