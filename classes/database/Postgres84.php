<?php

/*
 * PostgreSQL 8.4 support
 */

include_once('./classes/database/Postgres90.php');

class Postgres84 extends Postgres90 {

	var $major_version = 8.4;

	/**
	 * Constructor
	 * @param $conn The database connection
	 */
	function Postgres84($conn) {
		$this->Postgres($conn);
	}

	// Capabilities

	function hasByteaHexDefault() { return false; } 

}

?>
