<?php

/*
 * PostgreSQL 9.5 to 9.6 support
 */

include_once('./classes/database/Postgres1011.php');

class Postgres9596 extends Postgres1011 {

	var $major_version = 9.5;

	/**
	 * Constructor
	 * @param $conn The database connection
	 */
	function __construct($conn) {
		parent::__construct($conn);
	}

	function hasIdentityColumn() { return false; }

}
?>
