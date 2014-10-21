<?php
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
$Artes = $db->GetArtes();

echo $twig->render('artes.html', array('Artes' => 'test'));

?>