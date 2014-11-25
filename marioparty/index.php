<?php
function time_since($since) {
    $chunks = array(
        array(31536000, 'year'),
        array(2592000, 'month'),
        array(604800, 'week'),
        array(86400, 'day'),
        array(3600, 'hour'),
        array(60, 'minute'),
        array(1, 'second')
    );

    for ($i = 0, $j = count($chunks); $i < $j; $i++) {
        $seconds = $chunks[$i][0];
        $name = $chunks[$i][1];
        if (($count = floor($since / $seconds)) != 0) {
            break;
        }
    }

    $print = ($count == 1) ? '1 '.$name : "$count {$name}s";
    return $print;
}

	include '../credentials.php';
	$database = @mysql_connect($__db_hostname_stuff__, $__db_username_stuff__, $__db_password_stuff__) OR die(mysql_error());
	@mysql_select_db($__db_database_stuff__) OR die(mysql_error());
?>

<html>
<head>
<title>Mario Party 2</title>
</head>
<body>
<?php 

	$resultset = mysql_query('SELECT id, date, filename, `desc` FROM marioparty ORDER BY date DESC');
	if ( $resultset ) {
		while ( $data = mysql_fetch_assoc($resultset) ) {
			$desc = $data['desc'] == null ? $data['filename'] : $data['desc'];
			echo '<a href="'.$data['filename'].'">'.$desc.'</a>, '.time_since(time()-strtotime($data['date'])).' ago<br>';
			// ('.$data['date'].')
		}
	} else {
		echo 'Database query failed!';
	}

?>
</body>