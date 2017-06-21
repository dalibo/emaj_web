<?php

/*
 * PostgreSQL 8.3 support
 */

include_once('./classes/database/Postgres84.php');

class Postgres83 extends Postgres84 {

	var $major_version = 8.3;

	/**
	 * Constructor
	 * @param $conn The database connection
	 */
	function Postgres83($conn) {
		$this->Postgres($conn);
	}

	// Database functions

	/**
	 * Return all database available on the server
	 * @param $currentdatabase database name that should be on top of the resultset
	 * 
	 * @return A list of databases, sorted alphabetically
	 */
	function getDatabases($currentdatabase = NULL) {
		global $conf, $misc;

		$server_info = $misc->getServerInfo();

		if (isset($conf['owned_only']) && $conf['owned_only'] && !$this->isSuperUser()) {
			$username = $server_info['username'];
			$this->clean($username);
			$clause = " AND pr.rolname='{$username}'";
		}
		else $clause = '';

		if ($currentdatabase != NULL) {
			$this->clean($currentdatabase);
			$orderby = "ORDER BY pdb.datname = '{$currentdatabase}' DESC, pdb.datname";
		}
		else
			$orderby = "ORDER BY pdb.datname";

		if (!$conf['show_system'])
			$where = ' AND NOT pdb.datistemplate';
		else
			$where = ' AND pdb.datallowconn';

		$sql = "
			SELECT pdb.datname AS datname, pr.rolname AS datowner, pg_encoding_to_char(encoding) AS datencoding,
				(SELECT description FROM pg_catalog.pg_shdescription pd WHERE pdb.oid=pd.objoid AND pd.classoid='pg_database'::regclass) AS datcomment,
				(SELECT spcname FROM pg_catalog.pg_tablespace pt WHERE pt.oid=pdb.dattablespace) AS tablespace,
				pg_catalog.pg_database_size(pdb.oid) as dbsize
			FROM pg_catalog.pg_database pdb LEFT JOIN pg_catalog.pg_roles pr ON (pdb.datdba = pr.oid)
			WHERE true
				{$where}
				{$clause}
			{$orderby}";

		return $this->selectSet($sql);
	}

	function hasDatabaseCollation() { return false; }
	function hasAlterSequenceStart() { return false; }
}

?>
