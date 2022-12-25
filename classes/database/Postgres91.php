<?php

/*
 * PostgreSQL 9.1 support
 */

include_once('./classes/database/Postgres.php');

class Postgres91 extends Postgres9211 {

	var $major_version = 9.1;

	/**
	 * Constructor
	 * @param $conn The database connection
	 */
	function __construct($conn) {
		parent::__construct($conn);
	}

}
?>
