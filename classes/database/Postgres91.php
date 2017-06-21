<?php

/*
 * PostgreSQL 9.1 support
 */

include_once('./classes/database/Postgres.php');

class Postgres91 extends Postgres {

	var $major_version = 9.1;

	/**
	 * Constructor
	 * @param $conn The database connection
	 */
	function Postgres91($conn) {
		$this->Postgres($conn);
	}

}
?>
