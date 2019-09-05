<?php

/*
 * PostgreSQL 9.2 to 11 support
 */

include_once('./classes/database/Postgres.php');

class Postgres9211 extends Postgres {

	var $major_version = 9.2;

	/**
	 * Constructor
	 * @param $conn The database connection
	 */
	function Postgres9211($conn) {
		$this->Postgres($conn);
	}

    /**
	 * Checks to see whether or not a table has a unique id column
	 * @param $table The table name
	 * @return True if it has a unique id, false otherwise
	 * @return null error
	 **/
	function hasObjectID($table) {
		$c_schema = $this->_schema;
		$this->clean($c_schema);
		$this->clean($table);

		$sql = "SELECT relhasoids FROM pg_catalog.pg_class WHERE relname='{$table}'
			AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace WHERE nspname='{$c_schema}')";

		$rs = $this->selectSet($sql);
		if ($rs->recordCount() != 1) return null;
		else {
			$rs->fields['relhasoids'] = $this->phpBool($rs->fields['relhasoids']);
			return $rs->fields['relhasoids'];
		}
	}

	function hasWithOids() { return true; }

}
?>
