<?php
	function NULLify($string) {
		if ( $string == '' || $string == 'NULL' ) return 'NULL';
		else return '"'.$string.'"';
	}
?>