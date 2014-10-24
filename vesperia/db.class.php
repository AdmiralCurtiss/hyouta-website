<?php
class db {
	var $conn;
	
	function __construct( $connstr, $username, $password ) {
		$this->conn = new PDO( $connstr, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'") );
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->conn->beginTransaction();
	}
	
	function __destruct() {
		$this->conn->rollBack();
	}
	
	function GetArtesHtml( $id = false ) {
		$args = array();
		$s = 'SELECT html FROM Artes ';
		if ( $id === false ) {
			$s .= 'WHERE ( Artes.type > 0 AND Artes.type <= 11 ) OR Artes.type = 13 ';
		} else {
			$s .= 'WHERE Artes.id = :searchId ';
			$args['searchId'] = $id;
		}
		$s .= 'ORDER BY id ASC';
		
		$stmt = $this->conn->prepare( $s );
		$stmt->execute( $args );
		
		$artes = array();
		while( $r = $stmt->fetch() ) {
			$a = $r['html'];
			$artes[] = $a;
		}
		return $artes;
	}
}
?>