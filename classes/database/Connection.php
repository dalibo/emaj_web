<?php

/**
 * Class to represent a database connection
 */

include_once('./classes/database/ADODB_base.php');

class Connection {

	var $conn;
	
	// The backend platform.  Set to UNKNOWN by default.
	var $platform = 'UNKNOWN';
	
	/**
	 * Creates a new connection.  Will actually make a database connection.
	 * @param $fetchMode Defaults to associative.  Override for different behaviour
	 */
	function __construct($host, $port, $sslmode, $user, $password, $database, $fetchMode = ADODB_FETCH_ASSOC) {
		$this->conn = ADONewConnection('postgres7');
		$this->conn->setFetchMode($fetchMode);

		// Ignore host if null
		if ($host === null || $host == '')
			if ($port !== null && $port != '')
				$pghost = ':'.$port;
			else
				$pghost = '';
		else
			$pghost = "{$host}:{$port}";

		// Add sslmode to $pghost as needed
                if (($sslmode == 'disable') || ($sslmode == 'allow') || ($sslmode == 'prefer') || ($sslmode == 'require')) {
                        $pghost .= ':'.$sslmode;
                } elseif ($sslmode == 'legacy') {
                        $pghost .= ' requiressl=1';
                }

		$this->conn->connect($pghost, $user, $password, $database);
	}

	/**
	 * Gets the name of the correct database driver to use.  As a side effect,
	 * sets the platform.
	 * @param (return-by-ref) $description A description of the database and version
	 * @return The class name of the driver eg. Postgres84
	 * @return null if version is < 9.1
	 * @return -3 Database-specific failure
	 */
	function getDriver(&$description) {

		$v = pg_version($this->conn->_connectionID);
		if (isset($v['server'])) $version = $v['server'];

		// If we didn't manage to get the version without a query, query...
		if (!isset($version)) {
			$adodb = new ADODB_base($this->conn);

			$sql = "SELECT version() AS version";
			$field = $adodb->selectField($sql, 'version');

			$params = explode(' ', $field);
			if (!isset($params[1])) return -3;

			$version = $params[1]; // eg. 15.2
		}

		$description = "PostgreSQL {$version}";

		// Detect version and choose appropriate database driver

		switch (substr($version,0,2)) {
			case '18':
			case '17':
			case '16':
			case '15':
			case '14':
			case '13':
			case '12': return 'Postgres'; break;
			case '11':
			case '10': return 'Postgres1011'; break;
		}
		switch (substr($version,0,3)) {
			case '9.6':
			case '9.5': return 'Postgres9596'; break;
			case '9.4':
			case '9.3':
			case '9.2':
			case '9.1':
			case '9.0': return null; break;
		}

		/* All 9.0- versions are not supported */
		// if major version is 9 or less and wasn't cought in the
		// switch/case block, we have an unsupported version.

		if (substr($version, 1, 1) == '.' && (int)substr($version, 0, 1) <= 9)
			return null;

		// If unknown version, then default to latest driver
		return 'Postgres';

	}

	/** 
	 * Get the last error in the connection
	 * @return Error string
	 */
	function getLastError() {		
		return pg_last_error($this->conn->_connectionID);
	}
}

?>
