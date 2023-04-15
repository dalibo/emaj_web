<?php

/**
 * A Class that implements the DB Interface for Postgres
 * Note: This Class uses ADODB and returns RecordSets.
 */

include_once('./classes/database/ADODB_base.php');

class Postgres extends ADODB_base {

	var $major_version = 9.2;
	// Max object name length
	var $_maxNameLen = 63;
	// Store the current schema
	var $_schema;
	// Name of id column
	var $id = 'oid';
	// Supported join operations for use with view wizard
	// Select operators
	var $selectOps = array('=' => 'i', '!=' => 'i', '<' => 'i', '>' => 'i', '<=' => 'i', '>=' => 'i',
		'<<' => 'i', '>>' => 'i', '<<=' => 'i', '>>=' => 'i',
		'LIKE' => 'i', 'NOT LIKE' => 'i', 'ILIKE' => 'i', 'NOT ILIKE' => 'i', 'SIMILAR TO' => 'i',
		'NOT SIMILAR TO' => 'i', '~' => 'i', '!~' => 'i', '~*' => 'i', '!~*' => 'i',
		'IS NULL' => 'p', 'IS NOT NULL' => 'p', 'IN' => 'x', 'NOT IN' => 'x',
		'@@' => 'i', '@@@' => 'i', '@>' => 'i', '<@' => 'i',
		'@@ to_tsquery' => 't', '@@@ to_tsquery' => 't', '@> to_tsquery' => 't', '<@ to_tsquery' => 't',
		'@@ plainto_tsquery' => 't', '@@@ plainto_tsquery' => 't', '@> plainto_tsquery' => 't', '<@ plainto_tsquery' => 't');

	/**
	 * Constructor
	 * @param $conn The database connection
	 */
	function __construct($conn) {
		$this->ADODB_base($conn);
	}

	// Formatting functions

	/**
	 * Cleans (escapes) a string
	 * @param $str The string to clean, by reference
	 * @return The cleaned string
	 */
	function clean(&$str) {
		if ($str === null) return null;
		$str = str_replace("\r\n","\n",$str);
		$str = pg_escape_string($this->conn->_connectionID, $str);
		return $str;
	}

	/**
	 * Cleans (escapes) an object name (eg. table, field)
	 * @param $str The string to clean, by reference
	 * @return The cleaned string
	 */
	function fieldClean(&$str) {
		if ($str === null) return null;
		$str = str_replace('"', '""', $str);
		return $str;
	}

	/**
	 * Cleans (escapes) an array of field names
	 * @param $arr The array to clean, by reference
	 * @return The cleaned array
	 */
	function fieldArrayClean(&$arr) {
		foreach ($arr as $k => $v) {
			if ($v === null) continue;
			$arr[$k] = str_replace('"', '""', $v);
		}
		return $arr;
	}

	/**
	 * Cleans (escapes) an array
	 * @param $arr The array to clean, by reference
	 * @return The cleaned array
	 */
	function arrayClean(&$arr) {
		foreach ($arr as $k => $v) {
			if ($v === null) continue;
			$arr[$k] = pg_escape_string($this->conn->_connectionID, $v);
		}
		return $arr;
	}

	/**
	 * Escapes bytea data for display on the screen
	 * @param $data The bytea data
	 * @return Data formatted for on-screen display
	 */
	function escapeBytea($data) {
		return $data;
	}

	/**
	 * Outputs the HTML code for a particular field
	 * @param $name The name to give the field
	 * @param $value The value of the field.  Note this could be 'numeric(7,2)' sort of thing...
	 * @param $type The database type of the field
	 * @param $extras An array of attributes name as key and attributes' values as value
	 */
	function printField($name, $value, $type, $extras = array()) {
		global $lang;

		// Determine actions string
		$extra_str = '';
		foreach ($extras as $k => $v) {
			$extra_str .= " {$k}=\"" . htmlspecialchars($v) . "\"";
		}

		switch (substr($type,0,9)) {
			case 'bool':
			case 'boolean':
				if ($value !== null && $value == '') $value = null;
				elseif ($value == 'true') $value = 't';
				elseif ($value == 'false') $value = 'f';

				// If value is null, 't' or 'f'...
				if ($value === null || $value == 't' || $value == 'f') {
					echo "<select name=\"", htmlspecialchars($name), "\"{$extra_str}>\n";
					echo "<option value=\"\"", ($value === null) ? ' selected="selected"' : '', "></option>\n";
					echo "<option value=\"t\"", ($value == 't') ? ' selected="selected"' : '', ">{$lang['strtrue']}</option>\n";
					echo "<option value=\"f\"", ($value == 'f') ? ' selected="selected"' : '', ">{$lang['strfalse']}</option>\n";
					echo "</select>\n";
				}
				else {
					echo "<input name=\"", htmlspecialchars($name), "\" value=\"", htmlspecialchars($value), "\" size=\"35\"{$extra_str} />\n";
				}
				break;
			case 'bytea':
			case 'bytea[]':
                if (!is_null($value)) {
				    $value = $this->escapeBytea($value);
                }
			case 'text':
			case 'text[]':
			case 'xml':
			case 'xml[]':
				$n = substr_count($value, "\n");
				$n = $n < 5 ? 5 : $n;
				$n = $n > 20 ? 20 : $n;
				echo "<textarea name=\"", htmlspecialchars($name), "\" rows=\"{$n}\" cols=\"75\"{$extra_str}>\n";
				echo htmlspecialchars($value);
				echo "</textarea>\n";
				break;
			case 'character':
			case 'character[]':
				$n = substr_count($value, "\n");
				$n = $n < 5 ? 5 : $n;
				$n = $n > 20 ? 20 : $n;
				echo "<textarea name=\"", htmlspecialchars($name), "\" rows=\"{$n}\" cols=\"35\"{$extra_str}>\n";
				echo htmlspecialchars($value);
				echo "</textarea>\n";
				break;
			default:
				echo "<input name=\"", htmlspecialchars($name), "\" value=\"", htmlspecialchars($value), "\" size=\"35\"{$extra_str} />\n";
				break;
		}
	}

	/**
	 * Formats a value or expression for sql purposes
	 * @param $type The type of the field
	 * @param $format VALUE or EXPRESSION
	 * @param $value The actual value entered in the field.  Can be NULL
	 * @return The suitably quoted and escaped value.
	 */
	function formatValue($type, $format, $value) {
		switch ($type) {
			case 'bool':
			case 'boolean':
				if ($value == 't')
					return 'TRUE';
				elseif ($value == 'f')
					return 'FALSE';
				elseif ($value == '')
					return 'NULL';
				else
					return $value;
				break;
			default:
				// Checking variable fields is difficult as there might be a size
				// attribute...
				if (strpos($type, 'time') === 0) {
					// Assume it's one of the time types...
					if ($value == '') return "''";
					elseif (strcasecmp($value, 'CURRENT_TIMESTAMP') == 0
							|| strcasecmp($value, 'CURRENT_TIME') == 0
							|| strcasecmp($value, 'CURRENT_DATE') == 0
							|| strcasecmp($value, 'LOCALTIME') == 0
							|| strcasecmp($value, 'LOCALTIMESTAMP') == 0) {
						return $value;
					}
					elseif ($format == 'EXPRESSION')
						return $value;
					else {
						$this->clean($value);
						return "'{$value}'";
					}
				}
				else {
					if ($format == 'VALUE') {
						$this->clean($value);
						return "'{$value}'";
					}
					return $value;
				}
		}
	}

	/**
	 * Formats a type correctly for display.  Postgres 7.0 had no 'format_type'
	 * built-in function, and hence we need to do it manually.
	 * @param $typname The name of the type
	 * @param $typmod The contents of the typmod field
	 */
	function formatType($typname, $typmod) {
		// This is a specific constant in the 7.0 source
		$varhdrsz = 4;

		// If the first character is an underscore, it's an array type
		$is_array = false;
		if (substr($typname, 0, 1) == '_') {
			$is_array = true;
			$typname = substr($typname, 1);
		}

		// Show lengths on bpchar and varchar
		if ($typname == 'bpchar') {
			$len = $typmod - $varhdrsz;
			$temp = 'character';
			if ($len > 1)
				$temp .= "({$len})";
		}
		elseif ($typname == 'varchar') {
			$temp = 'character varying';
			if ($typmod != -1)
				$temp .= "(" . ($typmod - $varhdrsz) . ")";
		}
		elseif ($typname == 'numeric') {
			$temp = 'numeric';
			if ($typmod != -1) {
				$tmp_typmod = $typmod - $varhdrsz;
				$precision = ($tmp_typmod >> 16) & 0xffff;
				$scale = $tmp_typmod & 0xffff;
				$temp .= "({$precision}, {$scale})";
			}
		}
		else $temp = $typname;

		// Add array qualifier if it's an array
		if ($is_array) $temp .= '[]';

		return $temp;
	}

	// Database functions

	/**
	 * Return all information about a particular database
	 * @param $database The name of the database to retrieve
	 * @return The database info
	 */
	function getDatabase($database) {
		$this->clean($database);
		$sql = "SELECT * FROM pg_database WHERE datname='{$database}'";
		return $this->selectSet($sql);
	}

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
				CASE WHEN pg_catalog.has_database_privilege(current_user, pdb.oid, 'CONNECT') 
					THEN pg_catalog.pg_database_size(pdb.oid) 
					ELSE -1 -- set this magic value, which we will convert to no access later  
				END as dbsize, pdb.datcollate, pdb.datctype
			FROM pg_catalog.pg_database pdb
				LEFT JOIN pg_catalog.pg_roles pr ON (pdb.datdba = pr.oid)
			WHERE true
				{$where}
				{$clause}
			{$orderby}";

		return $this->selectSet($sql);
	}

	/**
	 * Return the database comment of a db from the shared description table
	 * @param string $database the name of the database to get the comment for
	 * @return recordset of the db comment info
	 */
	function getDatabaseComment($database) {
		$this->clean($database);
		$sql = "SELECT description FROM pg_catalog.pg_database JOIN pg_catalog.pg_shdescription ON (oid=objoid AND classoid='pg_database'::regclass) WHERE pg_database.datname = '{$database}' ";
		return $this->selectSet($sql);
	}

	/**
	 * Return the database owner of a db
	 * @param string $database the name of the database to get the owner for
	 * @return recordset of the db owner info
	 */
	function getDatabaseOwner($database) {
		$this->clean($database);
		$sql = "SELECT usename FROM pg_user, pg_database WHERE pg_user.usesysid = pg_database.datdba AND pg_database.datname = '{$database}' ";
		return $this->selectSet($sql);
	}

	/**
	 * Returns the current database encoding
	 * @return The encoding.  eg. SQL_ASCII, UTF-8, etc.
	 */
	function getDatabaseEncoding() {
		return pg_parameter_status($this->conn->_connectionID, 'server_encoding');
	}

	// Schema functions

	/**
	 * Return all schemas in the current database.
	 * @return All schemas, sorted alphabetically
	 */
	function getSchemas() {
		global $conf;

		if (!$conf['show_system']) {
			$where = "WHERE nspname NOT LIKE 'pg@_%' ESCAPE '@' AND nspname != 'information_schema'";

		}
		else $where = "WHERE nspname !~ '^pg_t(emp_[0-9]+|oast)$'";
		$sql = "
			SELECT pn.nspname, pu.rolname AS nspowner,
				pg_catalog.obj_description(pn.oid, 'pg_namespace') AS nspcomment
			FROM pg_catalog.pg_namespace pn
				LEFT JOIN pg_catalog.pg_roles pu ON (pn.nspowner = pu.oid)
			{$where}
			ORDER BY nspname";

		return $this->selectSet($sql);
	}

	/**
	 * Sets the current working schema.  Will also set Class variable.
	 * @param $schema The the name of the schema to work in
	 * @return 0 success
	 */
	function setSchema($schema) {
		// Get the current schema search path, including 'pg_catalog'.
		$search_path = $this->getSearchPath();
		// Prepend $schema to search path
		array_unshift($search_path, $schema);
		$status = $this->setSearchPath($search_path);
		if ($status == 0) {
			$this->_schema = $schema;
			return 0;
		}
		else return $status;
	}

	/**
	 * Sets the current schema search path
	 * @param $paths An array of schemas in required search order
	 * @return 0 success
	 * @return -1 Array not passed
	 * @return -2 Array must contain at least one item
	 */
	function setSearchPath($paths) {
		if (!is_array($paths)) return -1;
		elseif (sizeof($paths) == 0) return -2;
		elseif (sizeof($paths) == 1 && $paths[0] == '') {
			// Need to handle empty paths in some cases
			$paths[0] = 'pg_catalog';
		}

		// Loop over all the paths to check that none are empty
		$temp = array();
		foreach ($paths as $schema) {
			if ($schema != '') $temp[] = $schema;
		}
		$this->fieldArrayClean($temp);

		$sql = 'SET SEARCH_PATH TO "' . implode('","', $temp) . '"';

		return $this->execute($sql);
 		}

	/**
	 * Return the current schema search path
	 * @return Array of schema names
	 */
	function getSearchPath() {
		$sql = 'SELECT current_schemas(false) AS search_path';

		return $this->phpArray($this->selectField($sql, 'search_path'));
		}

	// Table functions

    /**
	 * Checks to see whether or not a table has a unique id column
	 * @param $table The table name
	 * @return True if it has a unique id, false otherwise
	 * @return null error
	 **/
	function hasObjectID($table) {

	// Since Postgres 12, tables with OIDS do not exist anymore
		return false;
	}

	/**
	 * Returns table information
	 * @param $table The name of the table
	 * @return A recordset
	 */
	function getTable($table) {
		$c_schema = $this->_schema;
		$this->clean($c_schema);
		$this->clean($table);

		$sql = "
			SELECT
			  c.relname, n.nspname, c.relkind, u.usename AS relowner,
			  pg_catalog.obj_description(c.oid, 'pg_class') AS relcomment,
			  (SELECT spcname FROM pg_catalog.pg_tablespace pt WHERE pt.oid=c.reltablespace) AS tablespace
			FROM pg_catalog.pg_class c
			     LEFT JOIN pg_catalog.pg_user u ON u.usesysid = c.relowner
			     LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
			WHERE c.relkind in ('r', 'p')
			      AND n.nspname = '{$c_schema}'
			      AND c.relname = '{$table}'";

		return $this->selectSet($sql);
	}

	/**
	 * Return all tables in current database (and schema).
	 * Filter regular tables and partitioned tables.
	 * @param $all True to fetch all tables, false for just in current schema
	 * @return All tables, sorted alphabetically
	 */
	function getTables($all = false) {
		$c_schema = $this->_schema;
		$this->clean($c_schema);
		if ($all) {
			// Exclude pg_catalog and information_schema tables
			$sql = "SELECT schemaname AS nspname, tablename AS relname, tableowner AS relowner
					FROM pg_catalog.pg_tables
					WHERE schemaname NOT IN ('pg_catalog', 'information_schema', 'pg_toast')
					ORDER BY schemaname, tablename";
		} else {
			$sql = "SELECT c.relname, pg_catalog.pg_get_userbyid(c.relowner) AS relowner,
						pg_catalog.obj_description(c.oid, 'pg_class') AS relcomment,
						reltuples::bigint,
						(SELECT spcname FROM pg_catalog.pg_tablespace pt WHERE pt.oid=c.reltablespace) AS tablespace
					FROM pg_catalog.pg_class c
					LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
					WHERE c.relkind in ('r', 'p')
					AND nspname='{$c_schema}'
					ORDER BY c.relname";
		}

		return $this->selectSet($sql);
	}

	/**
	 * Retrieve the attribute definition of a table
	 * @param $table The name of the table
	 * @param $field (optional) The name of a field to return
	 * @return All attributes in order
	 */
	function getTableAttributes($table, $field = '') {
		$c_schema = $this->_schema;
		$this->clean($c_schema);
		$this->clean($table);
		$this->clean($field);

		if ($field == '') {
			// This query is made much more complex by the addition of the 'attisserial' field.
			// The subquery to get that field checks to see if there is an internally dependent
			// sequence on the field.
			$sql = "
				SELECT
					a.attname, a.attnum,
					pg_catalog.format_type(a.atttypid, a.atttypmod) as type,
					a.atttypmod,
					a.attnotnull, a.atthasdef, pg_catalog.pg_get_expr(adef.adbin, adef.adrelid, true) as adsrc,
					a.attstattarget, a.attstorage, t.typstorage,
					(
						SELECT 1 FROM pg_catalog.pg_depend pd, pg_catalog.pg_class pc
						WHERE pd.objid=pc.oid
						AND pd.classid=pc.tableoid
						AND pd.refclassid=pc.tableoid
						AND pd.refobjid=a.attrelid
						AND pd.refobjsubid=a.attnum
						AND pd.deptype='i'
						AND pc.relkind='S'
					) IS NOT NULL AS attisserial,
					pg_catalog.col_description(a.attrelid, a.attnum) AS comment
				FROM
					pg_catalog.pg_attribute a LEFT JOIN pg_catalog.pg_attrdef adef
					ON a.attrelid=adef.adrelid
					AND a.attnum=adef.adnum
					LEFT JOIN pg_catalog.pg_type t ON a.atttypid=t.oid
				WHERE
					a.attrelid = (SELECT oid FROM pg_catalog.pg_class WHERE relname='{$table}'
						AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace WHERE
						nspname = '{$c_schema}'))
					AND a.attnum > 0 AND NOT a.attisdropped
				ORDER BY a.attnum";
		}
		else {
			$sql = "
				SELECT
					a.attname, a.attnum,
					pg_catalog.format_type(a.atttypid, a.atttypmod) as type,
					pg_catalog.format_type(a.atttypid, NULL) as base_type,
					a.atttypmod,
					a.attnotnull, a.atthasdef, pg_catalog.pg_get_expr(adef.adbin, adef.adrelid, true) as adsrc,
					a.attstattarget, a.attstorage, t.typstorage,
					pg_catalog.col_description(a.attrelid, a.attnum) AS comment
				FROM
					pg_catalog.pg_attribute a LEFT JOIN pg_catalog.pg_attrdef adef
					ON a.attrelid=adef.adrelid
					AND a.attnum=adef.adnum
					LEFT JOIN pg_catalog.pg_type t ON a.atttypid=t.oid
				WHERE
					a.attrelid = (SELECT oid FROM pg_catalog.pg_class WHERE relname='{$table}'
						AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace WHERE
						nspname = '{$c_schema}'))
					AND a.attname = '{$field}'";
		}

		return $this->selectSet($sql);
	}

	/**
	 * Given an array of attnums and a relation, returns an array mapping
	 * attribute number to attribute name.
	 * @param $table The table to get attributes for
	 * @param $atts An array of attribute numbers
	 * @return An array mapping attnum to attname
	 * @return -1 $atts must be an array
	 * @return -2 wrong number of attributes found
	 */
	function getAttributeNames($table, $atts) {
		$c_schema = $this->_schema;
		$this->clean($c_schema);
		$this->clean($table);
		$this->arrayClean($atts);

		if (!is_array($atts)) return -1;

		if (sizeof($atts) == 0) return array();

		$sql = "SELECT attnum, attname FROM pg_catalog.pg_attribute WHERE
			attrelid=(SELECT oid FROM pg_catalog.pg_class WHERE relname='{$table}' AND
			relnamespace=(SELECT oid FROM pg_catalog.pg_namespace WHERE nspname='{$c_schema}'))
			AND attnum IN ('" . join("','", $atts) . "')";

		$rs = $this->selectSet($sql);
		if ($rs->recordCount() != sizeof($atts)) {
				return -2;
			}
		else {
			$temp = array();
			while (!$rs->EOF) {
				$temp[$rs->fields['attnum']] = $rs->fields['attname'];
				$rs->moveNext();
			}
			return $temp;
		}
	}

	// Row functions

	/**
	 * Get the fields for uniquely identifying a row in a table
	 * @param $table The table for which to retrieve the identifier
	 * @return An array mapping attribute number to attribute name, empty for no identifiers
	 * @return -1 error
	 */
	function getRowIdentifier($table) {
		$oldtable = $table;
		$c_schema = $this->_schema;
		$this->clean($c_schema);
		$this->clean($table);

		$status = $this->beginTransaction();
		if ($status != 0) return -1;

		// Get the first primary or unique index (sorting primary keys first) that
		// is NOT a partial index.
		$sql = "
			SELECT indrelid, indkey
			FROM pg_catalog.pg_index
			WHERE indisunique AND indrelid=(
				SELECT oid FROM pg_catalog.pg_class
				WHERE relname='{$table}' AND relnamespace=(
					SELECT oid FROM pg_catalog.pg_namespace
					WHERE nspname='{$c_schema}'
				)
			) AND indpred IS NULL AND indexprs IS NULL
			ORDER BY indisprimary DESC LIMIT 1";
		$rs = $this->selectSet($sql);

		// If none, check for an OID column.  Even though OIDs can be duplicated, the edit and delete row
		// functions check that they're only modiying a single row.  Otherwise, return empty array.
		if ($rs->recordCount() == 0) {
			// Check for OID column
			$temp = array();
			if ($this->hasObjectID($table)) {
				$temp = array('oid');
			}
			$this->endTransaction();
			return $temp;
		}
		// Otherwise find the names of the keys
		else {
			$attnames = $this->getAttributeNames($oldtable, explode(' ', $rs->fields['indkey']));
			if (!is_array($attnames)) {
				$this->rollbackTransaction();
				return -1;
			}
			else {
				$this->endTransaction();
				return $attnames;
			}
		}
	}

	// Sequence functions

	/**
	 * Returns properties of a single sequence
	 * @param $sequence Sequence name
	 * @return A recordset
	 */
	function getSequence($sequence) {
		$c_schema = $this->_schema;
		$this->clean($c_schema);
		$c_sequence = $sequence;
		$this->fieldClean($sequence);
		$this->clean($c_sequence);

		$sql = "
			SELECT c.relname AS seqname, s.*,
				pg_catalog.obj_description(s.tableoid, 'pg_class') AS seqcomment,
				u.usename AS seqowner, n.nspname
			FROM \"{$sequence}\" AS s, pg_catalog.pg_class c, pg_catalog.pg_user u, pg_catalog.pg_namespace n
			WHERE c.relowner=u.usesysid AND c.relnamespace=n.oid
				AND c.relname = '{$c_sequence}' AND c.relkind = 'S' AND n.nspname='{$c_schema}'
				AND n.oid = c.relnamespace";

		return $this->selectSet( $sql );
	}

	/**
	 * Returns all sequences in the current database
	 * @return A recordset
	 */
	function getSequences($all = false) {
		if ($all) {
			// Exclude pg_catalog and information_schema tables
			$sql = "SELECT n.nspname, c.relname AS seqname, u.usename AS seqowner
				FROM pg_catalog.pg_class c, pg_catalog.pg_user u, pg_catalog.pg_namespace n
				WHERE c.relowner=u.usesysid AND c.relnamespace=n.oid
				AND c.relkind = 'S'
				AND n.nspname NOT IN ('pg_catalog', 'information_schema', 'pg_toast')
				ORDER BY nspname, seqname";
		} else {
			$c_schema = $this->_schema;
			$this->clean($c_schema);
			$sql = "SELECT c.relname AS seqname, u.usename AS seqowner, pg_catalog.obj_description(c.oid, 'pg_class') AS seqcomment,
				(SELECT spcname FROM pg_catalog.pg_tablespace pt WHERE pt.oid=c.reltablespace) AS tablespace
				FROM pg_catalog.pg_class c, pg_catalog.pg_user u, pg_catalog.pg_namespace n
				WHERE c.relowner=u.usesysid AND c.relnamespace=n.oid
				AND c.relkind = 'S' AND n.nspname='{$c_schema}' ORDER BY seqname";
		}

		return $this->selectSet( $sql );
	}

	// Constraint functions

	/**
	 * Returns a list of all constraints on a table,
	 * including constraint name, definition, related col and referenced namespace,
	 * table and col if needed
	 * @param $table the table where we are looking for fk
	 * @return a recordset
	 */
	function getConstraintsWithFields($table) {

		$c_schema = $this->_schema;
		$this->clean($c_schema);
		$this->clean($table);

		// get the max number of col used in a constraint for the table
		$sql = "SELECT DISTINCT
			max(SUBSTRING(array_dims(c.conkey) FROM  \$patern\$^\\[.*:(.*)\\]$\$patern\$)) as nb
		FROM pg_catalog.pg_constraint AS c
			JOIN pg_catalog.pg_class AS r ON (c.conrelid=r.oid)
			JOIN pg_catalog.pg_namespace AS ns ON (r.relnamespace=ns.oid)
		WHERE
			r.relname = '{$table}' AND ns.nspname='{$c_schema}'";

		$rs = $this->selectSet($sql);

		if ($rs->EOF) $max_col = 0;
		else $max_col = $rs->fields['nb'];

		$sql = '
			SELECT
				c.oid AS conid, c.contype, c.conname, pg_catalog.pg_get_constraintdef(c.oid, true) AS consrc,
				ns1.nspname as p_schema, r1.relname as p_table, ns2.nspname as f_schema,
				r2.relname as f_table, f1.attname as p_field, f1.attnum AS p_attnum, f2.attname as f_field,
				f2.attnum AS f_attnum, pg_catalog.obj_description(c.oid, \'pg_constraint\') AS constcomment,
				c.conrelid, c.confrelid
			FROM
				pg_catalog.pg_constraint AS c
				JOIN pg_catalog.pg_class AS r1 ON (c.conrelid=r1.oid)
				JOIN pg_catalog.pg_attribute AS f1 ON (f1.attrelid=r1.oid AND (f1.attnum=c.conkey[1]';
		for ($i = 2; $i <= $rs->fields['nb']; $i++) {
			$sql.= " OR f1.attnum=c.conkey[$i]";
		}
		$sql.= '))
				JOIN pg_catalog.pg_namespace AS ns1 ON r1.relnamespace=ns1.oid
				LEFT JOIN (
					pg_catalog.pg_class AS r2 JOIN pg_catalog.pg_namespace AS ns2 ON (r2.relnamespace=ns2.oid)
				) ON (c.confrelid=r2.oid)
				LEFT JOIN pg_catalog.pg_attribute AS f2 ON
					(f2.attrelid=r2.oid AND ((c.confkey[1]=f2.attnum AND c.conkey[1]=f1.attnum)';
		for ($i = 2; $i <= $rs->fields['nb']; $i++)
			$sql.= " OR (c.confkey[$i]=f2.attnum AND c.conkey[$i]=f1.attnum)";

		$sql .= sprintf("))
			WHERE
				r1.relname = '%s' AND ns1.nspname='%s'
			ORDER BY 1", $table, $c_schema);

		return $this->selectSet($sql);
	}

	/**
	 * Determines whether or not a user is a super user
	 * @param $username The username of the user
	 * @return True if is a super user, false otherwise
	 */
	function isSuperUser($username = '') {
		$this->clean($username);

		if (empty($usename)) {
			$val = pg_parameter_status($this->conn->_connectionID, 'is_superuser');
			if ($val !== false) return $val == 'on';
		}

		$sql = "SELECT usesuper FROM pg_user WHERE usename='{$username}'";

		$usesuper = $this->selectField($sql, 'usesuper');
		if ($usesuper == -1) return false;
		else return $usesuper == 't';
	}

	// Misc functions

	/**
	 * Executes an SQL script as a series of SQL statements.  Returns
	 * the result of the final step.  This is a very complicated lexer
	 * based on the REL7_4_STABLE src/bin/psql/mainloop.c lexer in
	 * the PostgreSQL source code.
	 * XXX: It does not handle multibyte languages properly.
	 * @param $name Entry in $_FILES to use
	 * @param $callback (optional) Callback function to call with each query,
	                               its result and line number.
	 * @return True for general success, false on any failure.
	 */
	function executeScript($name, $callback = null) {
		global $data;

		// This whole function isn't very encapsulated, but hey...
		$conn = $data->conn->_connectionID;
		if (!is_uploaded_file($_FILES[$name]['tmp_name'])) return false;

		$fd = fopen($_FILES[$name]['tmp_name'], 'r');
		if (!$fd) return false;

		// Build up each SQL statement, they can be multiline
		$query_buf = null;
		$query_start = 0;
		$in_quote = 0;
		$in_xcomment = 0;
		$bslash_count = 0;
		$dol_quote = null;
		$paren_level = 0;
		$len = 0;
		$i = 0;
		$prevlen = 0;
		$thislen = 0;
		$lineno = 0;

		// Loop over each line in the file
		while (!feof($fd)) {
			$line = fgets($fd);
			$lineno++;

			// Nothing left on line? Then ignore...
			if (trim($line) == '') continue;

		    $len = strlen($line);
		    $query_start = 0;

    		/*
    		 * Parse line, looking for command separators.
    		 *
    		 * The current character is at line[i], the prior character at line[i
    		 * - prevlen], the next character at line[i + thislen].
    		 */
    		$prevlen = 0;
    		$thislen = ($len > 0) ? 1 : 0;

    		for ($i = 0; $i < $len; $this->advance_1($i, $prevlen, $thislen)) {

    			/* was the previous character a backslash? */
    			if ($i > 0 && substr($line, $i - $prevlen, 1) == '\\')
    				$bslash_count++;
    			else
    				$bslash_count = 0;

    			/*
    			 * It is important to place the in_* test routines before the
    			 * in_* detection routines. i.e. we have to test if we are in
    			 * a quote before testing for comments.
    			 */

    			/* in quote? */
    			if ($in_quote !== 0)
    			{
    				/*
    				 * end of quote if matching non-backslashed character.
    				 * backslashes don't count for double quotes, though.
    				 */
    				if (substr($line, $i, 1) == $in_quote &&
    					($bslash_count % 2 == 0 || $in_quote == '"'))
    					$in_quote = 0;
    			}

				/* in or end of $foo$ type quote? */
				else if ($dol_quote) {
					if (strncmp(substr($line, $i), $dol_quote, strlen($dol_quote)) == 0) {
						$this->advance_1($i, $prevlen, $thislen);
						while(substr($line, $i, 1) != '$')
							$this->advance_1($i, $prevlen, $thislen);
						$dol_quote = null;
					}
				}

    			/* start of extended comment? */
    			else if (substr($line, $i, 2) == '/*')
    			{
    				$in_xcomment++;
    				if ($in_xcomment == 1)
    					$this->advance_1($i, $prevlen, $thislen);
    			}

    			/* in or end of extended comment? */
    			else if ($in_xcomment)
    			{
    				if (substr($line, $i, 2) == '*/' && !--$in_xcomment)
    					$this->advance_1($i, $prevlen, $thislen);
    			}

    			/* start of quote? */
    			else if (substr($line, $i, 1) == '\'' || substr($line, $i, 1) == '"') {
    				$in_quote = substr($line, $i, 1);
    		    }

				/*
				 * start of $foo$ type quote?
				 */
				else if (!$dol_quote && $this->valid_dolquote(substr($line, $i))) {
					$dol_end = strpos(substr($line, $i + 1), '$');
					$dol_quote = substr($line, $i, $dol_end + 1);
					$this->advance_1($i, $prevlen, $thislen);
					while (substr($line, $i, 1) != '$') {
						$this->advance_1($i, $prevlen, $thislen);
					}

				}

    			/* single-line comment? truncate line */
    			else if (substr($line, $i, 2) == '--')
    			{
    			    $line = substr($line, 0, $i); /* remove comment */
    				break;
    			}

    			/* count nested parentheses */
				else if (substr($line, $i, 1) == '(') {
    				$paren_level++;
				}

    			else if (substr($line, $i, 1) == ')' && $paren_level > 0) {
    				$paren_level--;
    			}

    			/* semicolon? then send query */
    			else if (substr($line, $i, 1) == ';' && !$bslash_count && !$paren_level)
    			{
    			    $subline = substr(substr($line, 0, $i), $query_start);
    				/* is there anything else on the line? */
    				if (strspn($subline, " \t\n\r") != strlen($subline))
    				{
    					/*
    					 * insert a cosmetic newline, if this is not the first
    					 * line in the buffer
						 */
    					if (strlen($query_buf) > 0)
    					    $query_buf .= "\n";
    					/* append the line to the query buffer */
    					$query_buf .= $subline;
    					$query_buf .= ';';

						// Execute the query. PHP cannot execute
            			// empty queries, unlike libpq
						$res = @pg_query($conn, $query_buf);

						// Call the callback function for display
						if ($callback !== null) $callback($query_buf, $res, $lineno);
            			// Check for COPY request
            			if (pg_result_status($res) == 4) { // 4 == PGSQL_COPY_FROM
            				while (!feof($fd)) {
            					$copy = fgets($fd, 32768);
            					$lineno++;
            					pg_put_line($conn, $copy);
            					if ($copy == "\\.\n" || $copy == "\\.\r\n") {
            						pg_end_copy($conn);
            						break;
            					}
            				}
            			}
            		}

					$query_buf = null;
					$query_start = $i + $thislen;
    			}

				/*
				 * keyword or identifier?
				 * We grab the whole string so that we don't
				 * mistakenly see $foo$ inside an identifier as the start
				 * of a dollar quote.
				 */
				// XXX: multibyte here
				else if (preg_match('/^[_[:alpha:]]$/', substr($line, $i, 1))) {
					$sub = substr($line, $i, $thislen);
					while (preg_match('/^[\$_A-Za-z0-9]$/', $sub)) {
						/* keep going while we still have identifier chars */
						$this->advance_1($i, $prevlen, $thislen);
						$sub = substr($line, $i, $thislen);
					}
					// Since we're now over the next character to be examined, it is necessary
					// to move back one space.
					$i-=$prevlen;
				}
    	    } // end for

    		/* Put the rest of the line in the query buffer. */
    		$subline = substr($line, $query_start);
    		if ($in_quote || $dol_quote || strspn($subline, " \t\n\r") != strlen($subline))
    		{
    			if (strlen($query_buf) > 0)
    			    $query_buf .= "\n";
    			$query_buf .= $subline;
    		}

    		$line = null;

    	} // end while

    	/*
    	 * Process query at the end of file without a semicolon, so long as
    	 * it's non-empty.
		 */
    	if (strlen($query_buf) > 0 && strspn($query_buf, " \t\n\r") != strlen($query_buf))
    	{
			// Execute the query
			$res = @pg_query($conn, $query_buf);

			// Call the callback function for display
			if ($callback !== null) $callback($query_buf, $res, $lineno);
			// Check for COPY request
			if (pg_result_status($res) == 4) { // 4 == PGSQL_COPY_FROM
				while (!feof($fd)) {
					$copy = fgets($fd, 32768);
					$lineno++;
					pg_put_line($conn, $copy);
					if ($copy == "\\.\n" || $copy == "\\.\r\n") {
						pg_end_copy($conn);
						break;
					}
				}
			}
		}

		fclose($fd);

		return true;
	}

	/**
	 * Generates the SQL for the 'select' function
	 * @param $table The table from which to select
	 * @param $show An array of columns to show.  Empty array means all columns.
	 * @param $values An array mapping columns to values
	 * @param $ops An array of the operators to use
	 * @param $orderby (optional) An array of column numbers or names (one based)
	 *        mapped to sort direction (asc or desc or '' or null) to order by
	 * @return The SQL query
	 */
	function getSelectSQL($table, $show, $values, $ops, $orderby = array()) {
		$this->fieldArrayClean($show);

		// If an empty array is passed in, then show all columns
		if (sizeof($show) == 0) {
			if ($this->hasObjectID($table))
				$sql = "SELECT \"{$this->id}\", * FROM ";
			else
				$sql = "SELECT * FROM ";
		}
		else {
			// Add oid column automatically to results for editing purposes
			if (!in_array($this->id, $show) && $this->hasObjectID($table))
				$sql = "SELECT \"{$this->id}\", \"";
			else
				$sql = "SELECT \"";

			$sql .= join('","', $show) . "\" FROM ";
		}

		$this->fieldClean($table);

		if (isset($_REQUEST['schema'])) {
			$f_schema = $_REQUEST['schema'];
			$this->fieldClean($f_schema);
			$sql .= "\"{$f_schema}\".";
		}
		$sql .= "\"{$table}\"";

		// If we have values specified, add them to the WHERE clause
		$first = true;
		if (is_array($values) && sizeof($values) > 0) {
			foreach ($values as $k => $v) {
				if ($v != '' || $this->selectOps[$ops[$k]] == 'p') {
					$this->fieldClean($k);
					if ($first) {
						$sql .= " WHERE ";
						$first = false;
					} else {
						$sql .= " AND ";
					}
					// Different query format depending on operator type
					switch ($this->selectOps[$ops[$k]]) {
						case 'i':
							// Only clean the field for the inline case
							// this is because (x), subqueries need to
							// to allow 'a','b' as input.
							$this->clean($v);
							$sql .= "\"{$k}\" {$ops[$k]} '{$v}'";
							break;
						case 'p':
							$sql .= "\"{$k}\" {$ops[$k]}";
							break;
						case 'x':
							$sql .= "\"{$k}\" {$ops[$k]} ({$v})";
							break;
						case 't':
							$sql .= "\"{$k}\" {$ops[$k]}('{$v}')";
							break;
						default:
							// Shouldn't happen
					}
				}
			}
		}

		// ORDER BY
		if (is_array($orderby) && sizeof($orderby) > 0) {
			$sql .= " ORDER BY ";
			$first = true;
			foreach ($orderby as $k => $v) {
				if ($first) $first = false;
				else $sql .= ', ';
				if (preg_match('/^[0-9]+$/', $k)) {
					$sql .= $k;
				}
				else {
					$this->fieldClean($k);
					$sql .= '"' . $k . '"';
				}
				if (strtoupper($v) == 'DESC') $sql .= " DESC";
			}
		}

		return $sql;
	}

	/**
	 * Returns a recordset of all columns in a query.  Supports paging.
	 * @param $type Either 'QUERY' if it is an SQL query, or 'TABLE' if it is a table identifier,
	 *              or 'SELECT" if it's a select query
	 * @param $table The base table of the query.  NULL for no table.
	 * @param $query The query that is being executed.  NULL for no query.
	 * @param $sortkey The column number to sort by, or '' or null for no sorting
	 * @param $sortdir The direction in which to sort the specified column ('asc' or 'desc')
	 * @param $page The page of the relation to retrieve
	 * @param $page_size The number of rows per page
	 * @param &$max_pages (return-by-ref) The max number of pages in the relation
	 * @return A recordset on success
	 * @return -1 transaction error
	 * @return -2 counting error
	 * @return -3 page or page_size invalid
	 * @return -4 unknown type
	 * @return -5 failed setting transaction read only
	 */
	function browseQuery($type, $table, $query, $sortkey, $sortdir, $page, $page_size, &$max_pages) {
		// Check that we're not going to divide by zero
		if (!is_numeric($page_size) || $page_size != (int)$page_size || $page_size <= 0) return -3;

		// If $type is TABLE, then generate the query
		switch ($type) {
			case 'TABLE':
				if (preg_match('/^[0-9]+$/', $sortkey) && $sortkey > 0) $orderby = array($sortkey => $sortdir);
				else $orderby = array();
				$query = $this->getSelectSQL($table, array(), array(), array(), $orderby);
				break;
			case 'QUERY':
			case 'SELECT':
				// Trim query
				$query = trim($query);
				// Trim off trailing semi-colon if there is one
				if (substr($query, strlen($query) - 1, 1) == ';')
					$query = substr($query, 0, strlen($query) - 1);
				break;
			default:
				return -4;
		}

		// Generate count query
		$count = "SELECT COUNT(*) AS total FROM ($query) AS sub";

		// Open a transaction
		$status = $this->beginTransaction();
		if ($status != 0) return -1;

		// If backend supports read only queries, then specify read only mode
		// to avoid side effects from repeating queries that do writes.
		if ($this->hasReadOnlyQueries()) {
			$status = $this->execute("SET TRANSACTION READ ONLY");
			if ($status != 0) {
				$this->rollbackTransaction();
				return -5;
					}
				}


		// Count the number of rows
		$total = $this->browseQueryCount($query, $count);
		if ($total < 0) {
			$this->rollbackTransaction();
			return -2;
    			}

		// Calculate max pages
		$max_pages = ceil($total / $page_size);

		// Check that page is less than or equal to max pages
		if (!is_numeric($page) || $page != (int)$page || $page > $max_pages || $page < 1) {
			$this->rollbackTransaction();
			return -3;
					}

		// Set fetch mode to NUM so that duplicate field names are properly returned
		// for non-table queries.  Since the SELECT feature only allows selecting one
		// table, duplicate fields shouldn't appear.
		if ($type == 'QUERY') $this->conn->setFetchMode(ADODB_FETCH_NUM);

		// Figure out ORDER BY.  Sort key is always the column number (based from one)
		// of the column to order by.  Only need to do this for non-TABLE queries
		if ($type != 'TABLE' && isset($sortKey) && preg_match('/^[0-9]+$/', $sortkey) && $sortkey > 0) {
			$orderby = " ORDER BY {$sortkey}";
			// Add sort order
			if ($sortdir == 'desc')
				$orderby .= ' DESC';
			else
				$orderby .= ' ASC';
				}
		else $orderby = '';

		// Actually retrieve the rows, with offset and limit
		$rs = $this->selectSet("SELECT * FROM ({$query}) AS sub {$orderby} LIMIT {$page_size} OFFSET " . ($page - 1) * $page_size);
		$status = $this->endTransaction();
		if ($status != 0) {
			$this->rollbackTransaction();
			return -1;
    			}

		return $rs;
    			}

	/**
	 * Finds the number of rows that would be returned by a
	 * query.
	 * @param $query The SQL query
	 * @param $count The count query
	 * @return The count of rows
	 * @return -1 error
	 */
	function browseQueryCount($query, $count) {
		return $this->selectField($count, 'total');
    }

	/**
	 * Returns a recordset of all columns in a table
	 * @param $table The name of a table
	 * @param $key The associative array holding the key to retrieve
	 * @return A recordset
    					 */
	function browseRow($table, $key) {
		$f_schema = $this->_schema;
		$this->fieldClean($f_schema);
		$this->fieldClean($table);

		$sql = "SELECT * FROM \"{$f_schema}\".\"{$table}\"";
		if (is_array($key) && sizeof($key) > 0) {
			$sql .= " WHERE true";
			foreach ($key as $k => $v) {
				$this->fieldClean($k);
				$this->clean($v);
				$sql .= " AND \"{$k}\"='{$v}'";
           	}
   		}

		return $this->selectSet($sql);
   	}

	// Type conversion routines

	/**
	 * Change a parameter from 't' or 'f' to a boolean, (others evaluate to false)
	 * @param $parameter the parameter
	 */
	function phpBool($parameter) {
		$parameter = ($parameter == 't');
		return $parameter;
	}

	// Capabilities

	function hasAlterSequenceStart() { return true; }
	function hasReadOnlyQueries() { return true; }
	function hasServerAdminFuncs() { return true; }
	function hasTablespaces() { return true; }
	function hasDatabaseCollation() { return true; }
	function hasByteaHexDefault() { return true; } 
	function hasWithOids() { return false; }
	
}
?>
