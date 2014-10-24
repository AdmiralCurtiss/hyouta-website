<?php
function print_header( $section = false ) {
?>
	<head>
		<link rel="stylesheet" href="style.css" />
		<title><?php if ( $section !== false ) { echo $section.' - '; } ?>Tales of Vesperia</title>
	</head>
<?php
}
?>