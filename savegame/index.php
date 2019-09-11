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


$database = new PDO( $__db_connstr_stuff__, $__db_username_stuff__, $__db_password_stuff__, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'") );
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$database->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$database->beginTransaction();

echo '<html>';
echo '<head>';
echo '<title>Savegame Archive</title>';
echo '</head>';
echo '<body>';

$statement = $database->prepare( 'SELECT id, date, filename, `desc` FROM marioparty ORDER BY date DESC' );
$statement->execute();

while ( $data = $statement->fetch() ) {
	$desc = $data['desc'] == null ? $data['filename'] : $data['desc'];
	echo '<a href="'.$data['filename'].'">'.$desc.'</a>, '.time_since(time()-strtotime($data['date'])).' ago<br>';
}

echo '</body>';

$database->rollBack();

?>