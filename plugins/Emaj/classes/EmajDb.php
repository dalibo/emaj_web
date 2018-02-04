<?php

/**
 * A class that implements the database access for the E-Maj ppa plugin.
 * It currently covers E-Maj versions starting from 0.11.x
 */

class EmajDb {

	/**
	 * Constant
	 */
	private $emaj_schema = "emaj";

	/**
	 * Cache of static data
	 */
	private $emaj_version = '?';
	private $emaj_version_num = 0;
	private $enabled = null;
	private $accessible = null;
	private $emaj_adm = null;
	private $emaj_viewer = null;
	private $dblink_usable = null;
	private $asyncRlbkUsable = null;

	/**
	 * Constructor
	 */
	function __construct() {
	}

	/**
	 * Determines whether or not Emaj is installed in the current
	 * database.
	 * @post Will populate version and schema fields, etc.
	 * @return True if Emaj is installed, false otherwise.
	 */
	function isEnabled() {
		// Access cache
		if ($this->enabled !== null) return $this->enabled;

		global $data;

		$this->enabled = false;
		// Check for the emaj schema in the namespace relation.
		$sql = "SELECT nspname AS schema
				FROM pg_catalog.pg_namespace
				WHERE nspname='{$this->emaj_schema}'";
		$rs = $data->selectSet($sql);
		if ($rs->recordCount() == 1) {
			$schema = $rs->fields['schema'];
			$this->emaj_schema = $schema;
			$this->enabled = true;
		}
		return $this->enabled;
	}

	/**
	 * Determines whether or not the current user is granted to access emaj schema.
	 * @return True if enabled Emaj is accessible by the current user, false otherwise.
	 */
	function isAccessible() {
		// Access cache
		if ($this->accessible !== null) return $this->accessible;

		// otherwise compute
		$this->accessible = $this->enabled&&($this->isEmaj_Adm()||$this->isEmaj_Viewer());
		return $this->accessible;
	}

	/**
	 * Determines whether or not the current user is granted the 'emaj_adm' role.
	 * @return True if Emaj is accessible by the current user as E-maj administrator, false otherwise.
	 */
	function isEmaj_Adm() {
		// Access cache
		if ($this->emaj_adm !== null) return $this->emaj_adm;

		global $data, $misc;

		$this->emaj_adm = false;
		$server_info = $misc->getServerInfo();
		// if the current role is superuser, he is considered as E-maj administration
		if ($data->isSuperUser($server_info['username'])){
			$this->emaj_adm = true;
		}else{
		// otherwise, is the current role member of emaj_adm role ?
			$sql = "SELECT CASE WHEN pg_catalog.pg_has_role('emaj_adm','USAGE') THEN 1 ELSE 0 END AS is_emaj_adm";
			$this->emaj_adm = $data->selectField($sql,'is_emaj_adm');
		}
		return $this->emaj_adm;
	}

	/**
	 * Determines whether or not the current user is granted the 'emaj_viewer' role.
	 * @return True if Emaj is accessible by the current user as E-maj viewer, false otherwise.
	 * Note that an 'emaj_adm' role is also considered as 'emaj_viewer'
	 */
	function isEmaj_Viewer() {
		// Access cache
		if ($this->emaj_viewer !== null) return $this->emaj_viewer;

		global $data, $misc;

		$this->emaj_viewer = false;
		if ($this->emaj_adm){
		// emaj_adm role is also considered as E-maj viewer
			$this->emaj_viewer = true;
		}else{
		// otherwise, is the current role member of emaj_viewer role ?
			$sql = "SELECT CASE WHEN pg_catalog.pg_has_role('emaj_viewer','USAGE') THEN 1 ELSE 0 END AS is_emaj_viewer";
			$this->emaj_viewer = $data->selectField($sql,'is_emaj_viewer');
		}
		return $this->emaj_viewer;
	}

	/**
	 * Determines whether or not a dblink connection can be used for rollbacks (not necessarily for this user).
	 */
	function isDblinkUsable() {
		// Access cache
		if ($this->dblink_usable !== null) return $this->dblink_usable;

		global $data;

		// It checks that 
		// - dblink is installed into the database by testing the existence of the dblink_connect_u function
		// - the dblink_user_password E-Maj parameter has been configured
		$sql = "SELECT CASE WHEN 
                       EXISTS(SELECT 1 FROM pg_catalog.pg_proc WHERE proname = 'dblink_connect_u')
                   AND EXISTS(SELECT 1 FROM emaj.emaj_visible_param WHERE param_key = 'dblink_user_password')
                                 THEN 1 ELSE 0 END as cnx_ok";
		$this->dblink_usable = $data->selectField($sql,'cnx_ok');

		return $this->dblink_usable;
	}

	/**
	 * Determines whether or not the asynchronous rollback can be used by the plugin for the current user.
	 * It checks that:
	 * - dblink is effectively usable
	 * - the psql_path and temp_dir parameters from the plugin configuration file are set and usable
	 * If they are set, one tries to use them.
	 */
	function isAsyncRlbkUsable($conf) {
		// Access cache
		if ($this->asyncRlbkUsable !== null) return $this->asyncRlbkUsable;

		global $misc, $data;

		$this->asyncRlbkUsable = 0;

		// check dblink is usable
		if ($this->isDblinkUsable()) {
			// if the _dblink_open_cnx() function is available for the user, 
			//   open a dblink connection and analyse the result
			$sql = "SELECT CASE 
						WHEN pg_catalog.has_function_privilege('\"{$this->emaj_schema}\"._dblink_open_cnx(text)', 'EXECUTE')
							AND \"{$this->emaj_schema}\"._dblink_open_cnx('test') >= 0 THEN 1 
						ELSE 0 END as cnx_ok";
			if ($data->selectField($sql,'cnx_ok')) {
				// one can effectively use a dblink connection

				// close the test connection
				$sql = "SELECT \"{$this->emaj_schema}\"._dblink_close_cnx('test')";
				$data->execute($sql);

				// check if the plugin parameters are set
				if (isset($conf['psql_path']) && isset($conf['temp_dir'])) {
		
					// check the psql exe path supplied in the config file, 
					// by executing a simple "psql --version" command
					$psqlExe = $misc->escapeShellCmd($conf['psql_path']);
					$version = array();
					preg_match("/(\d+(?:\.\d+)?)(?:\.\d+)?.*$/", exec($psqlExe . " --version"), $version);
					if (!empty($version)) {

						// ok, check a file can be written into the temp directory supplied in the config file 
						$sep = (substr(php_uname(), 0, 3) == "Win") ? '\\' : '/';
						$testFileName = $conf['temp_dir'] . $sep . 'rlbk_report_test';
						$f = fopen($testFileName,'w');
						if ($f) {

							fclose($f);
							unlink($testFileName);
		
							// it's OK
							$this->asyncRlbkUsable = 1;
						}
					}
				}
			}
		}
		return $this->asyncRlbkUsable;
	}

	/**
	 * Gets emaj version from either from cache or from a getVersion() call
	 */
	function getEmajVersion() {
		// Access cache
		if ($this->emaj_version !== '?') return $this->emaj_version;
		// otherwise read from the emaj_param table
		$this->getVersion();
		return $this->emaj_version;
	}

	/**
	 * Gets emaj version in numeric format from either from cache or from a getVersion() call
	 */
	function getNumEmajVersion() {
		// Access cache
		if ($this->emaj_version_num !== 0) return $this->emaj_version_num;
		// otherwise read from the emaj_param table
		$this->getVersion();
		return $this->emaj_version_num;
	}

	/**
	 * Gets emaj version from the emaj_param table or the emaj_visible_param if it exists
	 */
	function getVersion() {
		global $data;

		// init version values
		$this->emaj_version = '?';
		$this->emaj_version_num = 0;

		// look at the postgres catalog to see if the emaj_visible_param view exists or not. If not (i.e. old emaj version), use the emaj_param table instead.
		$sql = "SELECT CASE WHEN EXISTS 
					(SELECT relname FROM pg_catalog.pg_class, pg_catalog.pg_namespace
						WHERE relnamespace = pg_namespace.oid AND relname = 'emaj_visible_param' AND nspname = '{$this->emaj_schema}')
				THEN 'emaj_visible_param' ELSE 'emaj_param' END AS param_table";
		$rs = $data->selectSet($sql);
		if ($rs->recordCount() == 1){
			$param_table = $rs->fields['param_table'];

			// search the 'emaj_version' parameter into the proper view or table
			$sql = "SELECT param_value_text AS version
					FROM \"{$this->emaj_schema}\".{$param_table}
					WHERE param_key = 'emaj_version'";
			$rs = $data->selectSet($sql);
			if ($rs->recordCount() == 1){
				$this->emaj_version = $rs->fields['version'];
				if (substr_count($this->emaj_version, '.')==2){
					list($v1,$v2,$v3) = explode(".",$this->emaj_version);
					$this->emaj_version_num = 10000 * $v1 + 100 * $v2 + $v3;
				}
				if (substr_count($this->emaj_version, '.')==1){
					list($v1,$v2) = explode(".",$this->emaj_version);
					$this->emaj_version_num = 10000 * $v1 + 100 * $v2;
				}
				if ($this->emaj_version == '<NEXT_VERSION>'){
					$this->emaj_version = htmlspecialchars($this->emaj_version);
					$this->emaj_version_num = 999999;
				}
			}
		}
		return;
	}

	/**
	 * Gets tspemaj current size
	 */
	function getEmajSize() {
		global $data;

		if ($this->emaj_adm){
			if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
// The E-Maj size = size of all relations in emaj primary and secondary schemas + size of linked toast tables
				$sql = "SELECT coalesce(pg_size_pretty(t.emajtotalsize) || 
								to_char(t.emajtotalsize * 100 / pg_database_size(current_database())::float,' = FM990D0%'), '0 B = 0%') as emajsize 
						FROM 
						(SELECT ((t1.totalpages + t2.totalpages) * setting::integer)::bigint as emajtotalsize
							FROM pg_catalog.pg_settings, 
								(
								SELECT sum(relpages) as totalpages
								  FROM pg_catalog.pg_class, pg_catalog.pg_namespace 
								  WHERE relnamespace = pg_namespace.oid 
									AND nspname IN ";
				if ($this->getNumEmajVersion() >= 20200){	// version >= 2.2.0
					$sql .= "			(SELECT sch_name FROM emaj.emaj_schema)";
				} else {
					$sql .= "			(SELECT DISTINCT rel_log_schema FROM emaj.emaj_relation)";
				}
				$sql .= "		) as t1,
								(
								SELECT sum(c2.relpages) as totalpages
								  FROM pg_catalog.pg_class c1, pg_catalog.pg_namespace, emaj.emaj_relation,
									   pg_catalog.pg_class c2 
								  WHERE c1.relnamespace = pg_namespace.oid 
									AND c2.oid = c1.reltoastrelid
									AND nspname = rel_log_schema ";
				if ($this->getNumEmajVersion() >= 10200){	// version >= 1.2.0
					$sql .= "		AND c1.relname = rel_log_table";
				} else {
					$sql .= "		AND c1.relname = rel_schema || '_' || rel_tblseq || '_log' AND rel_kind = 'r'";
				}
				$sql .= "		) as t2
							WHERE pg_settings.name = 'block_size'
						) as t";
			}else{
				$sql = "SELECT pg_size_pretty(
							(SELECT sum(pg_relation_size(pg_class.oid)) FROM pg_catalog.pg_class, pg_catalog.pg_namespace 
								WHERE relnamespace=pg_namespace.oid AND nspname = '{$this->emaj_schema}')::bigint
							) || to_char(
							(SELECT sum(pg_relation_size(pg_class.oid)) FROM pg_catalog.pg_class, pg_catalog.pg_namespace 
								WHERE relnamespace=pg_namespace.oid AND nspname = '{$this->emaj_schema}')
							*100 / pg_database_size(current_database())::float,' = FM990D0%') as emajsize";
			}
			return $data->selectField($sql,'emajsize');
		}else{
			return '?';
		}
	}

	/**
	 * Checks E-Maj consistency
	 */
	function checkEmaj() {
		global $data;

		$sql = "SELECT * FROM emaj.emaj_verify_all()";
		return $data->selectSet($sql);
	}


	// GROUPS

	/**
	 * Gets all groups referenced in emaj_group table for this database
	 */
	function getGroups() {
		global $data;

		$sql = "SELECT group_name, group_comment FROM \"{$this->emaj_schema}\".emaj_group ORDER BY group_name";

		return $data->selectSet($sql);
	}

	/**
	 * Gets all idle groups referenced in emaj_group table for this database
	 */
	function getIdleGroups() {
		global $data;

		if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence,
					  CASE WHEN group_is_rollbackable THEN 'ROLLBACKABLE' ELSE 'AUDIT_ONLY' END 
						as group_type, 
					  CASE WHEN length(group_comment) > 100 THEN substr(group_comment,1,97) || '...' ELSE group_comment END 
						as abbr_comment, 
					  to_char(time_tx_timestamp,'DD/MM/YYYY HH24:MI:SS') as creation_datetime,
					  (SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM \"{$this->emaj_schema}\".emaj_group, \"{$this->emaj_schema}\".emaj_time_stamp
					WHERE NOT group_is_logging
					  AND time_id = group_creation_time_id
					ORDER BY group_name";
		}else{
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence,
					  CASE WHEN group_is_rollbackable THEN 'ROLLBACKABLE' ELSE 'AUDIT_ONLY' END 
						as group_type, 
					  CASE WHEN length(group_comment) > 100 THEN substr(group_comment,1,97) || '...' ELSE group_comment END 
						as abbr_comment, 
					  to_char(group_creation_datetime,'DD/MM/YYYY HH24:MI:SS') as creation_datetime,
					  (SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM \"{$this->emaj_schema}\".emaj_group
					WHERE ";
			if ($this->getNumEmajVersion() >= 10100){	// version >= 1.1.0
				$sql .=	"NOT group_is_logging ";
			}else{
				$sql .=	"group_state = 'IDLE' ";
			}
			$sql .=	"ORDER BY group_name";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Gets all Logging groups referenced in emaj_group table for this database
	 */
	function getLoggingGroups() {
		global $data;

		if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence,
					  CASE WHEN NOT group_is_rollbackable THEN 'AUDIT_ONLY'
						   WHEN group_is_rollbackable AND NOT group_is_rlbk_protected THEN 'ROLLBACKABLE'
						   ELSE 'ROLLBACKABLE-PROTECTED' END as group_type,
					  CASE WHEN length(group_comment) > 100 THEN substr(group_comment,1,97) || '...' ELSE group_comment END
						as abbr_comment,
					  to_char(time_tx_timestamp,'DD/MM/YYYY HH24:MI:SS') as creation_datetime,
					  (SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM \"{$this->emaj_schema}\".emaj_group, \"{$this->emaj_schema}\".emaj_time_stamp
					WHERE group_is_logging
					  AND time_id = group_creation_time_id
					ORDER BY group_name";
		}else{
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence, ";
			if ($this->getNumEmajVersion() >= 10300){	// version >= 1.3.0
				$sql .=	"
						CASE WHEN NOT group_is_rollbackable THEN 'AUDIT_ONLY'
							 WHEN group_is_rollbackable AND NOT group_is_rlbk_protected THEN 'ROLLBACKABLE'
							 ELSE 'ROLLBACKABLE-PROTECTED' END as group_type, ";
			}else{
				$sql .=	"
						CASE WHEN group_is_rollbackable THEN 'ROLLBACKABLE' ELSE 'AUDIT_ONLY' END as group_type, ";
			}
			$sql .=	"
					 CASE WHEN length(group_comment) > 100 THEN substr(group_comment,1,97) || '...' ELSE group_comment END
						as abbr_comment, 
					 to_char(group_creation_datetime,'DD/MM/YYYY HH24:MI:SS') as creation_datetime,
					 (SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM \"{$this->emaj_schema}\".emaj_group
					WHERE ";
			if ($this->getNumEmajVersion() >= 10100){	// version >= 1.1.0
				$sql .=	"group_is_logging ";
			}else{
				$sql .=	"group_state = 'LOGGING' ";
			}
			$sql .=	"ORDER BY group_name";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Gets all groups referenced in emaj_group_def but not in emaj_group table
	 */
	function getNewGroups() {
		global $data;

		$sql = "SELECT DISTINCT grpdef_group AS group_name FROM \"{$this->emaj_schema}\".emaj_group_def
				EXCEPT
				SELECT group_name FROM \"{$this->emaj_schema}\".emaj_group
				ORDER BY 1";
		return $data->selectSet($sql);
	}

	/**
	 * Gets properties of one emaj_group 
	 */
	function getGroup($group) {
		global $data;

		$data->clean($group);

		if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence, time_tx_timestamp as group_creation_datetime,
					CASE WHEN group_is_logging THEN 'LOGGING' ELSE 'IDLE' END as group_state,
					CASE WHEN NOT group_is_rollbackable THEN 'AUDIT_ONLY'
						 WHEN group_is_rollbackable AND NOT group_is_rlbk_protected THEN 'ROLLBACKABLE' 
						 ELSE 'ROLLBACKABLE-PROTECTED' END as group_type,
					group_comment, 
					pg_size_pretty((SELECT sum(pg_total_relation_size('\"' || rel_log_schema || '\".\"' || rel_log_table || '\"'))
						FROM \"{$this->emaj_schema}\".emaj_relation 
						WHERE rel_group = group_name AND rel_kind = 'r')::bigint) as log_size,
					(SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM \"{$this->emaj_schema}\".emaj_group, \"{$this->emaj_schema}\".emaj_time_stamp
					WHERE group_name = '{$group}'
					  AND time_id = group_creation_time_id";
		}else{
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence, group_creation_datetime, ";
			if ($this->getNumEmajVersion() >= 10100){	// version >= 1.1.0
				$sql .=	"CASE WHEN group_is_logging THEN 'LOGGING' ELSE 'IDLE' END as group_state, ";
			}else{
				$sql .=	"group_state, ";
			}
			if ($this->getNumEmajVersion() >= 10300){	// version >= 1.3.0
				$sql .=	"
						CASE WHEN NOT group_is_rollbackable THEN 'AUDIT_ONLY'
							 WHEN group_is_rollbackable AND NOT group_is_rlbk_protected THEN 'ROLLBACKABLE' 
							 ELSE 'ROLLBACKABLE-PROTECTED' END as group_type, ";
			}else{
				$sql .=	"
						CASE WHEN group_is_rollbackable THEN 'ROLLBACKABLE' ELSE 'AUDIT_ONLY' END as group_type, ";
			}
			$sql .=	"
					group_comment, 
					pg_size_pretty((SELECT sum(pg_total_relation_size('";
			if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
				$sql .=	"\"' || rel_log_schema || '\"";
			}else{
				$sql .=	"emaj";
			}
			$sql .=
					".\"' || rel_schema || '_' || rel_tblseq || '_log\"')) 
						FROM \"{$this->emaj_schema}\".emaj_relation WHERE rel_group = group_name AND rel_kind = 'r')::bigint) as log_size,
					(SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM \"{$this->emaj_schema}\".emaj_group
					WHERE group_name = '{$group}'";
		}
		return $data->selectSet($sql);
	}

	/**
	 * Gets the isLogging property of one emaj_group (1 if in logging state, 0 if idle)
	 */
	function isGroupLogging($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT CASE WHEN group_is_logging THEN 1 ELSE 0 END AS is_logging
				FROM \"{$this->emaj_schema}\".emaj_group
				WHERE group_name = '{$group}'";

		return $data->selectField($sql,'is_logging');
	}

	/**
	 * Gets the isRollbackable property of one emaj_group (1 if rollbackable, 0 if audit_only)
	 */
	function isGroupRollbackable($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT CASE WHEN group_is_rollbackable THEN 1 ELSE 0 END AS is_rollbackable
				FROM \"{$this->emaj_schema}\".emaj_group
				WHERE group_name = '{$group}'";

		return $data->selectField($sql,'is_rollbackable');
	}

	/**
	 * Gets the isProtected property of one emaj_group (1 if protected, 0 if unprotected)
	 */
	function isGroupProtected($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT CASE WHEN group_is_rlbk_protected THEN 1 ELSE 0 END AS is_protected
				FROM \"{$this->emaj_schema}\".emaj_group
				WHERE group_name = '{$group}'";

		return $data->selectField($sql,'is_protected');
	}

	/**
	 * Gets all marks related to a group
	 */
	function getMarks($group) {
		global $data;

		$data->clean($group);

		if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
			// mark_cumlogrows is computed later, at results display
			$sql = "SELECT mark_group, mark_name, time_tx_timestamp as mark_datetime, mark_comment,
						CASE WHEN mark_is_deleted THEN 'DELETED'
							 WHEN NOT mark_is_deleted AND mark_is_rlbk_protected THEN 'ACTIVE-PROTECTED'
							 ELSE 'ACTIVE' END as mark_state, 
						coalesce(mark_log_rows_before_next,
						(SELECT SUM(stat_rows) 
							FROM \"{$this->emaj_schema}\".emaj_log_stat_group(emaj_mark.mark_group,emaj_mark.mark_name,NULL)))
						 AS mark_logrows, 
						 0 AS mark_cumlogrows
					FROM \"{$this->emaj_schema}\".emaj_mark, \"{$this->emaj_schema}\".emaj_time_stamp 
					WHERE mark_group = '{$group}'
					  AND time_id = mark_time_id
					ORDER BY mark_id DESC";
		}else{
			$sql = "SELECT mark_group, mark_name, mark_datetime, mark_comment, ";
			if ($this->getNumEmajVersion() >= 10300){	// version >= 1.3.0
				$sql .= "CASE WHEN mark_is_deleted THEN 'DELETED' 
							  WHEN NOT mark_is_deleted AND mark_is_rlbk_protected THEN 'ACTIVE-PROTECTED'
							  ELSE 'ACTIVE' END as mark_state, ";
			}elseif ($this->getNumEmajVersion() >= 10100){	// version >= 1.1.0
				$sql .= "CASE WHEN mark_is_deleted THEN 'DELETED' ELSE 'ACTIVE' END as mark_state, ";
			}else{
				$sql .= "mark_state, ";
			}
			$sql .=							// mark_cumlogrows is computed later, at results display
					"coalesce(mark_log_rows_before_next,
						(SELECT SUM(stat_rows) 
							FROM \"{$this->emaj_schema}\".emaj_log_stat_group(emaj_mark.mark_group,emaj_mark.mark_name,NULL)))
					 AS mark_logrows, 0 AS mark_cumlogrows
					FROM \"{$this->emaj_schema}\".emaj_mark
					WHERE mark_group = '{$group}' 
					ORDER BY mark_id DESC";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Gets the content of one emaj_group 
	 */
	function getContentGroup($group) {
		global $data;

		$data->clean($group);

		if ($this->getNumEmajVersion() >= 10200){	// version >= 1.2.0
			$sql = "SELECT rel_schema, rel_tblseq, rel_kind || '+' AS relkind, rel_priority,
						rel_log_schema, rel_log_dat_tsp, rel_log_idx_tsp,
						substring(rel_log_function FROM '(.*)\_log\_fnct') AS emaj_names_prefix,
						CASE WHEN rel_kind = 'r' THEN 
							pg_total_relation_size(quote_ident(rel_log_schema) || '.' || quote_ident(rel_log_table))
						END AS byte_log_size,
						CASE WHEN rel_kind = 'r' THEN 
							pg_size_pretty(pg_total_relation_size(quote_ident(rel_log_schema) || '.' || quote_ident(rel_log_table)))
						END AS pretty_log_size 
					FROM \"{$this->emaj_schema}\".emaj_relation
					WHERE rel_group = '{$group}'";
			if ($this->getNumEmajVersion() >= 22000){	// version >= 2.2.0
				$sql .= " AND upper_inf(rel_time_range)";
			}
			$sql .= "		ORDER BY rel_schema, rel_tblseq";
		} else {
			$sql = "SELECT rel_schema, rel_tblseq, rel_kind || '+' AS relkind, rel_priority";
			if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
				$sql .=
					", rel_log_schema, rel_log_dat_tsp, rel_log_idx_tsp";
			}
			$sql .= ", CASE WHEN rel_kind = 'r' THEN 
						pg_total_relation_size(";
			if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
				$sql .= "quote_ident(rel_log_schema)";
			}else{
				$sql .= "'{$this->emaj_schema}'";
			}
			$sql .= " || '.\"' || rel_schema || '_' || rel_tblseq || '_log\"')
						END as byte_log_size
					, CASE WHEN rel_kind = 'r' THEN 
						pg_size_pretty(pg_total_relation_size(";
			if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
				$sql .= "quote_ident(rel_log_schema)";
			}else{
				$sql .= "'{$this->emaj_schema}'";
			}
			$sql .= " || '.\"' || rel_schema || '_' || rel_tblseq || '_log\"'))
						END as pretty_log_size 
					FROM \"{$this->emaj_schema}\".emaj_relation
					WHERE rel_group = '{$group}'
					ORDER BY rel_schema, rel_tblseq";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Return all non system schemas but emaj from the current database
	 * plus all nonexistent schemas but listed in emaj_group_def
	 */
	function getSchemas() {
		global $data;

		$sql = "SELECT 1, pn.nspname, pu.rolname AS nspowner,
					   pg_catalog.obj_description(pn.oid, 'pg_namespace') AS nspcomment
				FROM pg_catalog.pg_namespace pn
					 LEFT JOIN pg_catalog.pg_roles pu ON (pn.nspowner = pu.oid)
				WHERE nspname NOT LIKE 'pg@_%' ESCAPE '@' AND 
					  nspname != 'information_schema' AND nspname != '{$this->emaj_schema}' ";
		if ($this->getNumEmajVersion() >= 10000){			// version >= 1.0.0
			if ($this->getNumEmajVersion() >= 22000){			// version >= 2.2.0
				$sql .=
					"AND nspname NOT IN (SELECT sch_name FROM emaj.emaj_schema) ";
			} else {
				$sql .=
					"AND nspname NOT IN (SELECT DISTINCT rel_log_schema FROM emaj.emaj_relation WHERE rel_log_schema IS NOT NULL) ";
			}
		}
		$sql .= "UNION
				SELECT DISTINCT 2, grpdef_schema AS nspname, '!' AS nspowner, NULL AS nspcomment
				FROM emaj.emaj_group_def
				WHERE grpdef_schema NOT IN ( SELECT nspname FROM pg_catalog.pg_namespace )
				ORDER BY 1, nspname";

		return $data->selectSet($sql);
	}

	/**
	 * Return all tables and sequences of a schema, 
	 * plus all non existent tables but listed in emaj_group_def with this schema
	 */
	function getTablesSequences($schema) {
		global $data;

		$data->clean($schema);

		$sql = "SELECT 1, nspname, c.relname,
					c.relkind || case when relkind = 'S' or (relkind = 'r' and c.relpersistence = 'p' and not c.relhasoids) then '+' else '-' end as relkind,
					pg_catalog.pg_get_userbyid(c.relowner) AS relowner,
					pg_catalog.obj_description(c.oid, 'pg_class') AS relcomment, spcname AS tablespace,
					grpdef_group, grpdef_priority ";
		if ($this->getNumEmajVersion() >= 10000){			// version >= 1.0.0
			$sql .=
				", grpdef_log_schema_suffix ";
		} else {
			$sql .=
				", NULL AS grpdef_log_schema_suffix ";
		}
		if ($this->getNumEmajVersion() >= 10200){			// version >= 1.2.0
			$sql .=
				", grpdef_emaj_names_prefix ";
		} else {
			$sql .=
				", NULL AS grpdef_emaj_names_prefix ";
		}
		if ($this->getNumEmajVersion() >= 10000){			// version >= 1.0.0
			$sql .=
				", grpdef_log_dat_tsp, grpdef_log_idx_tsp ";
		} else {
			$sql .=
				", NULL AS grpdef_log_dat_tsp, NULL AS grpdef_log_idx_tsp ";
		}
		$sql .=
			   "FROM pg_catalog.pg_class c
					LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
					LEFT JOIN emaj.emaj_group_def ON grpdef_schema = nspname AND grpdef_tblseq = c.relname
					LEFT JOIN pg_catalog.pg_tablespace pt ON pt.oid = c.reltablespace
				WHERE c.relkind IN ('r','S','p') AND nspname='{$schema}'
				UNION
				SELECT 2, grpdef_schema AS nspname, grpdef_tblseq AS relname, '!' AS relkind, NULL, NULL, NULL, 
					grpdef_group , grpdef_priority ";
		if ($this->getNumEmajVersion() >= 10000){			// version >= 1.0.0
			$sql .=
				", grpdef_log_schema_suffix ";
		} else {
			$sql .=
				", NULL AS grpdef_log_schema_suffix ";
		}
		if ($this->getNumEmajVersion() >= 10200){			// version >= 1.2.0
			$sql .=
				", grpdef_emaj_names_prefix ";
		} else {
			$sql .=
				", NULL AS grpdef_emaj_names_prefix ";
		}
		if ($this->getNumEmajVersion() >= 10000){			// version >= 1.0.0
			$sql .=
				", grpdef_log_dat_tsp, grpdef_log_idx_tsp ";
		} else {
			$sql .=
				", NULL AS grpdef_log_dat_tsp, NULL AS grpdef_log_idx_tsp ";
		}
		$sql .=
			   "FROM emaj.emaj_group_def
				WHERE grpdef_schema = '{$schema}' AND grpdef_tblseq NOT IN 
					( SELECT relname FROM pg_catalog.pg_class, pg_catalog.pg_namespace
						WHERE relnamespace = pg_namespace.oid AND nspname = '{$schema}' AND relkind IN ('r','S') )
				ORDER BY 1, relname";

		return $data->selectSet($sql);
	}

	/**
	 * Gets group names already known in the emaj_group and emaj_group_def tables
	 */
	function getKnownGroups() {
		global $data;

		$data->fieldClean($schema);
		$sql = "SELECT group_name
				  FROM \"{$this->emaj_schema}\".emaj_group
				UNION
				SELECT DISTINCT grpdef_group
				  FROM \"{$this->emaj_schema}\".emaj_group_def
				ORDER BY 1";
		return $data->selectSet($sql);
	}

	/**
	 * Gets log schema suffix already known in the emaj_group_def table
	 */
	function getKnownSuffix() {
		global $data;

		$data->fieldClean($schema);
		$sql = "SELECT DISTINCT grpdef_log_schema_suffix AS known_suffix 
				FROM \"{$this->emaj_schema}\".emaj_group_def
				WHERE grpdef_log_schema_suffix <> '' AND grpdef_log_schema_suffix IS NOT NULL
				ORDER BY 1";
		return $data->selectSet($sql);
	}

	/**
	 * Gets existing tablespaces
	 */
	function getKnownTsp() {
		global $data;

		$sql = "SELECT spcname  
				FROM pg_catalog.pg_tablespace
				WHERE spcname NOT LIKE 'pg\_%'
				ORDER BY 1";
		return $data->selectSet($sql);
	}

	/**
	 * Insert a table or sequence into the emaj_group_def table
	 */
	function assignTblSeq($schema,$tblseq,$group,$priority,$logSchemaSuffix,$emajNamesPrefix,$logDatTsp,$logIdxTsp) {
		global $data;

		$data->clean($schema);
		$data->clean($tblseq);
		$data->clean($group);
		$data->clean($priority);
		$data->clean($logSchemaSuffix);
		$data->clean($emajNamesPrefix);
		$data->clean($logDatTsp);
		$data->clean($logIdxTsp);

		// get the relkind of the tblseq to process
		$sql = "SELECT relkind 
				FROM pg_catalog.pg_class, pg_catalog.pg_namespace 
				WHERE pg_namespace.oid = relnamespace AND relname = '{$tblseq}' AND nspname = '{$schema}'";
		$rs = $data->selectSet($sql);
		if ($rs->recordCount() == 1){
			$relkind = $rs->fields['relkind'];
		}else{
			$relkind = "?";
		}

		// Insert the new row into the emaj_group_def table
		$sql = "INSERT INTO emaj.emaj_group_def (grpdef_schema, grpdef_tblseq, grpdef_group, grpdef_priority ";
		if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
			$sql .=
				", grpdef_log_schema_suffix ";
		}
		if ($this->getNumEmajVersion() >= 10200){	// version >= 1.2.0
			$sql .=
				", grpdef_emaj_names_prefix ";
		}
		if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
			$sql .=
				", grpdef_log_dat_tsp, grpdef_log_idx_tsp ";
		}
		$sql .=
				") VALUES ('{$schema}', '{$tblseq}', '{$group}' ";
		if ($priority == '')
			$sql .= ", NULL";
		else
			$sql .= ", {$priority}";
		if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
			if ($logSchemaSuffix == '' || $relkind == 'S')
				$sql .= ", NULL";
			else
				$sql .= ", '{$logSchemaSuffix}'";
		}
		if ($this->getNumEmajVersion() >= 10200){	// version >= 1.2.0
			if ($emajNamesPrefix == '' || $relkind == 'S')
				$sql .= ", NULL";
			else
				$sql .= ", '{$emajNamesPrefix}'";
		}
		if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
			if ($logDatTsp == '' || $relkind == 'S')
				$sql .= ", NULL";
			else
				$sql .= ", '{$logDatTsp}'";
			if ($logIdxTsp == '' || $relkind == 'S')
				$sql .= ", NULL";
			else
				$sql .= ", '{$logIdxTsp}'";
		}
		$sql .= ")";

		return $data->execute($sql);
	}

	/**
	 * Update a table or sequence into the emaj_group_def table
	 */
	function updateTblSeq($schema,$tblseq,$groupOld,$groupNew,$priority,$logSchemaSuffix,$emajNamesPrefix,$logDatTsp,$logIdxTsp) {
		global $data;

		$data->clean($schema);
		$data->clean($tblseq);
		$data->clean($groupOld);
		$data->clean($groupNew);
		$data->clean($priority);
		$data->clean($logSchemaSuffix);
		$data->clean($emajNamesPrefix);
		$data->clean($logDatTsp);
		$data->clean($logIdxTsp);

		// get the relkind of the tblseq to process
		$sql = "SELECT relkind 
				FROM pg_catalog.pg_class, pg_catalog.pg_namespace 
				WHERE pg_namespace.oid = relnamespace AND relname = '{$tblseq}' AND nspname = '{$schema}'";
		$rs = $data->selectSet($sql);
		if ($rs->recordCount() == 1){
			$relkind = $rs->fields['relkind'];
		}else{
			$relkind = "?";
		}

		// Update the row in the emaj_group_def table
		$sql = "UPDATE emaj.emaj_group_def SET 
					grpdef_group = '{$groupNew}'";
		if ($priority == '')
			$sql .= ", grpdef_priority = NULL";
		else
			$sql .= ", grpdef_priority = {$priority}";
		if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
			if ($logSchemaSuffix == '' || $relkind == 'S')
				$sql .= ", grpdef_log_schema_suffix = NULL";
			else
				$sql .= ", grpdef_log_schema_suffix = '{$logSchemaSuffix}'";
		}
		if ($this->getNumEmajVersion() >= 10200){	// version >= 1.2.0
			if ($emajNamesPrefix == '' || $relkind == 'S')
				$sql .= ", grpdef_emaj_names_prefix = NULL";
			else
				$sql .= ", grpdef_emaj_names_prefix = '{$emajNamesPrefix}'";
		}
		if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
			if ($logDatTsp == '' || $relkind == 'S')
				$sql .= ", grpdef_log_dat_tsp = NULL";
			else
				$sql .= ", grpdef_log_dat_tsp = '{$logDatTsp}'";
			if ($logIdxTsp == '' || $relkind == 'S')
				$sql .= ", grpdef_log_idx_tsp = NULL";
			else
				$sql .= ", grpdef_log_idx_tsp = '{$logIdxTsp}'";
		}
		$sql .=
               " WHERE grpdef_schema = '{$schema}' AND grpdef_tblseq = '{$tblseq}' AND grpdef_group = '{$groupOld}'";

		return $data->execute($sql);
	}

	/**
	 * Delete a table or sequence from emaj_group_def table
	 */
	function removeTblSeq($schema,$tblseq,$group) {
		global $data;

		$data->clean($schema);
		$data->clean($tblseq);
		$data->clean($group);

		// Begin transaction.  We do this so that we can ensure only one row is deleted
		$status = $data->beginTransaction();
		if ($status != 0) {
			$data->rollbackTransaction();
			return -1;
		}

		$sql = "DELETE FROM emaj.emaj_group_def 
				WHERE grpdef_schema = '{$schema}' AND grpdef_tblseq = '{$tblseq}' AND grpdef_group = '{$group}'";
		// Delete row
		$status = $data->execute($sql);

		if ($status != 0 || $data->conn->Affected_Rows() != 1) {
			$data->rollbackTransaction();
			return -2;
		}
		// End transaction
		return $data->endTransaction();
	}

	/**
	 * Creates a group
	 */
	function createGroup($group,$isRollbackable) {
		global $data;

		if ($isRollbackable){
			$sql = "SELECT \"{$this->emaj_schema}\".emaj_create_group('{$group}',true) AS nbtblseq";
		}else{
			$sql = "SELECT \"{$this->emaj_schema}\".emaj_create_group('{$group}',false) AS nbtblseq";
		}			

		return $data->selectField($sql,'nbtblseq');
	}

	/**
	 * Drops a group
	 */
	function dropGroup($group) {
		global $data;

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_drop_group('{$group}') AS nbtblseq";

		return $data->selectField($sql,'nbtblseq');
	}

	/**
	 * Check that the group can be altered
	 * If the group is not IDLE, it performs checks on operations that will be performed
	 * Returns 1 if OK, else 0
	 */
	function checkAlterGroup($group) {
		global $data;

		$data->clean($group);

		// get the group's state
		$sql = "SELECT CASE WHEN group_is_logging THEN 1 ELSE 0 END AS is_logging
				FROM \"{$this->emaj_schema}\".emaj_group
				WHERE group_name = '{$group}'";
		$isLogging = $data->selectField($sql,'is_logging');
		// the group is idle, so return immediately
		if (! $isLogging) { return 1; }
		// the group is logging 
		// if the emaj version is prior 2.1.0, exit immediately
		if ($this->getNumEmajVersion() < 20100){ return 0; }

		// if the emaj version is prior 2.2.0, check no table or sequence would be removed from the group
		if ($this->getNumEmajVersion() < 20200){	// version < 2.2.0
			$sql = "SELECT count(*) as nb_errors FROM (
						SELECT rel_schema, rel_tblseq FROM \"{$this->emaj_schema}\".emaj_relation WHERE rel_group = '{$group}'
							EXCEPT
						SELECT grpdef_schema, grpdef_tblseq FROM \"{$this->emaj_schema}\".emaj_group_def WHERE grpdef_group = '{$group}'
					) as t";
			if ($data->selectField($sql,'nb_errors') > 0 ) { return 0; }
		}

		// check no table or sequence would be added to the group
		$sql = "SELECT count(*) as nb_errors FROM (
					SELECT grpdef_schema, grpdef_tblseq FROM \"{$this->emaj_schema}\".emaj_group_def WHERE grpdef_group = '{$group}'
						EXCEPT
					SELECT rel_schema, rel_tblseq FROM \"{$this->emaj_schema}\".emaj_relation WHERE rel_group = '{$group}'";
		if ($this->getNumEmajVersion() >= 20200){	// version >= 2.2.0
			$sql .= " AND upper_inf(rel_time_range) ";
		}
		$sql .= ") as t";
		if ($data->selectField($sql,'nb_errors') > 0 ) { return 0; }

		// check that no table or sequence would be repaired for the group
		$sql = "SELECT count(*) as nb_errors FROM \"{$this->emaj_schema}\"._verify_groups(ARRAY['{$group}'], false)";
		if ($data->selectField($sql,'nb_errors') > 0 ) { return 0; }

		// all checks are ok
		return 1;
	}

	/**
	 * Alters a group
	 */
	function alterGroup($group,$mark) {
		global $data;

		if ($mark == '') {
			$sql = "SELECT \"{$this->emaj_schema}\".emaj_alter_group('{$group}') AS nbtblseq";
		} else {
			$sql = "SELECT \"{$this->emaj_schema}\".emaj_alter_group('{$group}', '{$mark}') AS nbtblseq";
		}
		return $data->selectField($sql,'nbtblseq');
	}

	/**
	 * Alters several groups
	 */
	function alterGroups($groups,$mark) {
		global $data;

		$data->clean($groups);
		$groupsArray="ARRAY['".str_replace(', ',"','",$groups)."']";

		if ($mark == '') {
			$sql = "SELECT \"{$this->emaj_schema}\".emaj_alter_groups({$groupsArray}) AS nbtblseq";
		} else {
			$sql = "SELECT \"{$this->emaj_schema}\".emaj_alter_groups({$groupsArray}, '{$mark}') AS nbtblseq";
		}

		return $data->selectField($sql,'nbtblseq');
	}

	/**
	 * Sets a comment for a group
	 */
	function setCommentGroup($group,$comment) {
		global $data;

		$data->clean($group);
		$data->clean($comment);

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_comment_group('{$group}','{$comment}')";

		return $data->execute($sql);
	}

	/**
	 * Determines whether or not a mark name is valid as a new mark to set for a group
	 * Returns 1 if the mark name is not already known, 0 otherwise.
	 */
	function isNewMarkValidGroup($group,$mark) {

		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT CASE WHEN 
				(SELECT COUNT(*) FROM \"{$this->emaj_schema}\".emaj_mark WHERE mark_group = '{$group}' AND mark_name = '{$mark}')
				= 0 THEN 1 ELSE 0 END AS result";

		return $data->selectField($sql,'result');
	}

	/**
	 * Determines whether or not a mark name is valid as a new mark to set for a groups array
	 * Returns 1 if the mark name is not already known, 0 otherwise.
	 */
	function isNewMarkValidGroups($groups,$mark) {

		global $data;

		$data->clean($groups);
		$groupsArray="ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);

		$sql = "SELECT CASE WHEN 
				(SELECT COUNT(*) FROM \"{$this->emaj_schema}\".emaj_mark 
				   WHERE mark_group = ANY ({$groupsArray}) AND mark_name = '{$mark}')
				= 0 THEN 1 ELSE 0 END AS result";

		return $data->selectField($sql,'result');
	}

	/**
	 * Computes the number of active mark in a group.
	 */
	function nbActiveMarkGroup($group) {

		global $data;

		$data->clean($group);

		$sql = "SELECT COUNT(*) as result FROM \"{$this->emaj_schema}\".emaj_mark WHERE mark_group = '{$group}'";

		return $data->selectField($sql,'result');
	}

	/**
	 * Determines whether or not a mark name is the first mark of its group
	 * Returns 1 if the mark is the oldest of its group, 0 otherwise.
	 */
	function isFirstMarkGroup($group,$mark) {

		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT CASE WHEN mark_id = 
						(SELECT MIN (mark_id) FROM \"{$this->emaj_schema}\".emaj_mark WHERE mark_group = '{$group}')
						THEN 1 ELSE 0 END AS result
				FROM \"{$this->emaj_schema}\".emaj_mark
				WHERE mark_group = '{$group}' AND mark_name = '{$mark}'";

		return $data->selectField($sql,'result');
	}

	/**
	 * Starts a group
	 */
	function startGroup($group,$mark,$resetLog) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		if ($resetLog){
			$sql = "SELECT \"{$this->emaj_schema}\".emaj_start_group('{$group}','{$mark}') AS nbtblseq";
		}else{
			$sql = "SELECT \"{$this->emaj_schema}\".emaj_start_group('{$group}','{$mark}',false) AS nbtblseq";
		}

		return $data->selectField($sql,'nbtblseq');
	}

	/**
	 * Starts several groups
	 */
	function startGroups($groups,$mark,$resetLog) {
		global $data;

		$data->clean($groups);
		$groupsArray="ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);

		if ($resetLog){
			$sql = "SELECT \"{$this->emaj_schema}\".emaj_start_groups({$groupsArray},'{$mark}') AS nbtblseq";
		}else{
			$sql = "SELECT \"{$this->emaj_schema}\".emaj_start_groups({$groupsArray},'{$mark}',false) AS nbtblseq";
		}

		return $data->selectField($sql,'nbtblseq');
	}

	/**
	 * Stops a group
	 */
	function stopGroup($group,$mark,$forceStop) {
		global $data;

		$data->clean($group);
		$data->clean($mark);
		
		if ($forceStop){
			$sql = "SELECT \"{$this->emaj_schema}\".emaj_force_stop_group('{$group}') AS nbtblseq";
		}else{
			if ($mark == ""){
				$sql = "SELECT \"{$this->emaj_schema}\".emaj_stop_group('{$group}') AS nbtblseq";
			}else{
				$sql = "SELECT \"{$this->emaj_schema}\".emaj_stop_group('{$group}','{$mark}') AS nbtblseq";
			}
		}

		return $data->selectField($sql,'nbtblseq');
	}

	/**
	 * Stops several groups at once
	 */
	function stopGroups($groups) {
		global $data;

		$data->clean($groups);
		$groupsArray="ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);
		
		if ($mark == ""){
			$sql = "SELECT \"{$this->emaj_schema}\".emaj_stop_groups({$groupsArray}) AS nbtblseq";
		}else{
			$sql = "SELECT \"{$this->emaj_schema}\".emaj_stop_groups({$groupsArray},'{$mark}') AS nbtblseq";
		}

		return $data->selectField($sql,'nbtblseq');
	}

	/**
	 * Resets a group
	 */
	function resetGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_reset_group('{$group}') AS nbtblseq";

		return $data->selectField($sql,'nbtblseq');
	}

	/**
	 * Protects a group
	 */
	function protectGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_protect_group('{$group}') AS status";

		return $data->selectField($sql,'status');
	}

	/**
	 * Unprotects a group
	 */
	function unprotectGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_unprotect_group('{$group}') AS status";

		return $data->selectField($sql,'status');
	}

	/**
	 * Sets a mark for a group
	 */
	function setMarkGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_set_mark_group('{$group}','{$mark}') AS nbtblseq";

		return $data->execute($sql);
	}

	/**
	 * Sets a mark for several groups
	 */
	function setMarkGroups($groups,$mark) {
		global $data;

		$data->clean($groups);
		$groupsArray="ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_set_mark_groups({$groupsArray},'{$mark}') AS nbtblseq";

		return $data->execute($sql);
	}

	/**
	 * Gets properties of one mark 
	 */
	function getMark($group,$mark) {
		global $data;

		$data->fieldClean($schema);
		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT mark_name, mark_group, mark_comment 
				FROM \"{$this->emaj_schema}\".emaj_mark
				WHERE mark_group = '{$group}' AND mark_name = '{$mark}'";
		return $data->selectSet($sql);
	}

	/**
	 * Sets a comment for a mark of a group
	 */
	function setCommentMarkGroup($group,$mark,$comment) {
		global $data;

		$data->clean($group);
		$data->clean($mark);
		$data->clean($comment);

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_comment_mark_group('{$group}','{$mark}','{$comment}')";

		return $data->execute($sql);
	}

	/**
	 * Protects a mark for a group
	 */
	function protectMarkGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_protect_mark_group('{$group}','{$mark}') AS status";

		return $data->selectField($sql,'status');
	}

	/**
	 * Unprotects a mark for a group
	 */
	function unprotectMarkGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_unprotect_mark_group('{$group}','{$mark}') AS status";

		return $data->selectField($sql,'status');
	}

	/**
	 * Deletes a mark for a group
	 */
	function deleteMarkGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_delete_mark_group('{$group}','{$mark}')";

		return $data->execute($sql);
	}

	/**
	 * Deletes all marks before a mark for a group
	 */
	function deleteBeforeMarkGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_delete_before_mark_group('{$group}','{$mark}') as nbmark";

		return $data->selectField($sql,'nbmark');
	}

	/**
	 * Renames a mark for a group
	 */
	function renameMarkGroup($group,$mark,$newMark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);
		$data->clean($newMark);

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_rename_mark_group('{$group}','{$mark}','{$newMark}')";

		return $data->execute($sql);
	}

	/**
	 * Returns the list of marks usable to rollback a group.
	 */
	function getRollbackMarkGroup($group) {

		global $data;

		$data->clean($group);

		if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
			$sql = "SELECT mark_name, time_tx_timestamp as mark_datetime, mark_is_rlbk_protected
					FROM \"{$this->emaj_schema}\".emaj_mark, \"{$this->emaj_schema}\".emaj_time_stamp 
					WHERE mark_group = '{$group}'
					  AND NOT mark_is_deleted
					  AND time_id = mark_time_id
					ORDER BY mark_id DESC";
		}else{
			$sql = "SELECT mark_name, mark_datetime, ";
			if ($this->getNumEmajVersion() >= 10300){	// version >= 1.3.0
				$sql .= "mark_is_rlbk_protected";
			}else{
				$sql .= "'f' AS mark_is_rlbk_protected";
			}
			$sql .= " FROM \"{$this->emaj_schema}\".emaj_mark 
					WHERE ";
			if ($this->getNumEmajVersion() >= 10100){	// version >= 1.1.0
				$sql .= "NOT mark_is_deleted";
			}else{
				$sql .= "mark_state = 'ACTIVE'";
			}
			$sql .= " AND mark_group = '$group'
					ORDER BY mark_id DESC";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Determines whether or not a mark name for a group is ACTIVE (ie. not deleted)
	 * Returns 1 if the mark name is known and is not deleted
	 * Retuns 0 otherwise.
	 */
	function isMarkActiveGroup($group,$mark) {

		global $data;

		$data->clean($group);
		$data->clean($mark);

		// check the mark is active
		$sql = "SELECT CASE WHEN EXISTS
				 (SELECT 0 FROM \"{$this->emaj_schema}\".emaj_mark 
                   WHERE mark_group = '{$group}' AND mark_name = '{$mark}' AND ";
		if ($this->getNumEmajVersion() >= 10100){	// version >= 1.1.0
			$sql .= "NOT mark_is_deleted";
		}else{
			$sql .= "mark_state = 'ACTIVE'";
		}
		$sql .= ") THEN 1 ELSE 0 END AS is_active";

		return $data->selectField($sql,'is_active');
	}

	/**
	 * Determines whether or not a mark name is valid as a mark to rollback to for a group
	 * Returns 1 if:
	 *   - the mark name is known and in ACTIVE state and 
	 *   - no intermediate protected mark would be covered by the rollback, 
	 * Retuns 0 otherwise.
	 */
	function isRollbackMarkValidGroup($group,$mark) {

		global $data;

		$data->clean($group);
		$data->clean($mark);

		// check the mark is active (i.e. not deleted)
		$result = $this->isMarkActiveGroup($group,$mark);

		if ($result == 1) {
			// the mark is active, so now check there is no intermediate protected mark
			if ($this->getNumEmajVersion() >= 10300){	// version >= 1.3.0
				$sql = "SELECT CASE WHEN 
						(SELECT count(*) FROM \"{$this->emaj_schema}\".emaj_mark 
						  WHERE mark_group = '{$group}' AND mark_id > 
							(SELECT mark_id FROM \"{$this->emaj_schema}\".emaj_mark 
							 WHERE mark_group = '{$group}' AND mark_name = '{$mark}'
						    ) AND mark_is_rlbk_protected
						) = 0 THEN 1 ELSE 0 END AS result";
				$result = $data->selectField($sql,'result');
			}
		}

		return $result;
	}

	/**
	 * Returns information about all alter_group operations that have been executed after a mark set for one or several groups
	 * The function is called when emaj version >= 2.1
	 */
	function getAlterAfterMarkGroups($groups,$mark,$emajlang) {

		global $data;

		$data->clean($groups);
		$data->clean($mark);

		$groupsArray="ARRAY['".str_replace(', ',"','",$groups)."']";
		$firstGroup = substr($groups, 0, strpos($groups.',', ','));

		// look at the alter group operations executed after the mark
		$sql = "SELECT time_tx_timestamp, altr_step, CASE
				  WHEN altr_step = 'REMOVE_TBL' THEN
					format('{$emajlang['emajalteredremovetbl']}', quote_ident(altr_schema), quote_ident(altr_tblseq), quote_literal(altr_group))
				  WHEN altr_step = 'REMOVE_SEQ' THEN
					format('{$emajlang['emajalteredremoveseq']}', quote_ident(altr_schema), quote_ident(altr_tblseq), quote_literal(altr_group))
				  WHEN altr_step = 'REPAIR_TBL' THEN
					format('{$emajlang['emajalteredrepairtbl']}', quote_ident(altr_schema), quote_ident(altr_tblseq))
				  WHEN altr_step = 'REPAIR_SEQ' THEN
					format('{$emajlang['emajalteredrepairseq']}', quote_ident(altr_schema), quote_ident(altr_tblseq))
				  WHEN altr_step = 'CHANGE_TBL_LOG_SCHEMA' THEN
					format('{$emajlang['emajalteredchangetbllogschema']}', quote_ident(altr_schema), quote_ident(altr_tblseq))
				  WHEN altr_step = 'CHANGE_TBL_NAMES_PREFIX' THEN
					format('{$emajlang['emajalteredchangetblnamesprefix']}', quote_ident(altr_schema), quote_ident(altr_tblseq))
				  WHEN altr_step = 'CHANGE_TBL_LOG_DATA_TSP' THEN
					format('{$emajlang['emajalteredchangetbllogdatatsp']}', quote_ident(altr_schema), quote_ident(altr_tblseq))
				  WHEN altr_step = 'CHANGE_TBL_LOG_INDEX_TSP' THEN
					format('{$emajlang['emajalteredchangetbllogindextsp']}', quote_ident(altr_schema), quote_ident(altr_tblseq))
				  WHEN altr_step = 'ASSIGN_REL' THEN
					format('{$emajlang['emajalteredremovetbl']}', quote_ident(altr_schema), quote_ident(altr_tblseq), quote_literal(altr_group), quote_literal(altr_new_group))
				  WHEN altr_step = 'CHANGE_REL_PRIORITY' THEN
					format('{$emajlang['emajalteredchangerelpriority']}', quote_ident(altr_schema), quote_ident(altr_tblseq))
				  WHEN altr_step = 'ADD_TBL' THEN
					format('{$emajlang['emajalteredaddtbl']}', quote_ident(altr_schema), quote_ident(altr_tblseq), quote_literal(altr_group))
				  WHEN altr_step = 'ADD_SEQ' THEN
					format('{$emajlang['emajalteredaddseq']}', quote_ident(altr_schema), quote_ident(altr_tblseq), quote_literal(altr_group))
                  END AS altr_action, 
				CASE WHEN altr_step IN ('REMOVE_TBL', 'REMOVE_SEQ', 'REPAIR_TBL',
										'CHANGE_TBL_LOG_SCHEMA', 'CHANGE_TBL_NAMES_PREFIX', 'CHANGE_TBL_LOG_DATA_TSP', 'CHANGE_TBL_LOG_INDEX_TSP', 'CHANGE_REL_PRIORITY')
					THEN false ELSE true END AS altr_auto_rolled_back
				  FROM \"{$this->emaj_schema}\".emaj_alter_plan, \"{$this->emaj_schema}\".emaj_time_stamp
				  WHERE time_id = altr_time_id
					AND altr_group = ANY ({$groupsArray})
					AND altr_time_id >
						(SELECT mark_time_id FROM \"{$this->emaj_schema}\".emaj_mark WHERE mark_group = '{$firstGroup}' AND mark_name = '{$mark}')
					AND altr_rlbk_id IS NULL
					AND altr_step IN ('REMOVE_TBL', 'REMOVE_SEQ', 'REPAIR_TBL', 'REPAIR_SEQ', 'CHANGE_TBL_LOG_SCHEMA', 'CHANGE_TBL_NAMES_PREFIX',
									  'CHANGE_TBL_LOG_DATA_TSP', 'CHANGE_TBL_LOG_INDEX_TSP', 'ASSIGN_REL', 'CHANGE_REL_PRIORITY', 'ADD_TBL', 'ADD_SEQ')
				  ORDER BY time_tx_timestamp, altr_schema, altr_tblseq, altr_step";
		return $data->selectSet($sql);
	}

	/**
	 * Rollbacks a group to a mark
	 */
	function rollbackGroup($group,$mark,$isLogged) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		if ($this->getNumEmajVersion() >= 20100){	// version >= 2.1.0
			if ($isLogged){
				$sql = "SELECT sum(substring(rlbk_message from '^\d+')::integer) AS nbtblseq
						FROM \"{$this->emaj_schema}\".emaj_logged_rollback_group('{$group}','{$mark}',true)
						WHERE rlbk_severity = 'Notice'";
			}else{
				$sql = "SELECT sum(substring(rlbk_message from '^\d+')::integer) AS nbtblseq
						FROM \"{$this->emaj_schema}\".emaj_rollback_group('{$group}','{$mark}',true)
						WHERE rlbk_severity = 'Notice'";
			}
		}else{
			if ($isLogged){
				$sql = "SELECT\"{$this->emaj_schema}\".emaj_logged_rollback_group('{$group}','{$mark}') AS nbtblseq";
			}else{
				$sql = "SELECT \"{$this->emaj_schema}\".emaj_rollback_group('{$group}','{$mark}') AS nbtblseq";
			}
		}

		return $data->selectField($sql,'nbtblseq');
	}

	/**
	 * rollbacks asynchronously one or several groups to a mark, using a single session
	 */
	function asyncRollbackGroups($groups,$mark,$isLogged,$psqlExe,$tempDir,$isMulti) {
		global $data, $misc;

		$data->clean($groups);
		$data->clean($mark);
		$data->clean($psqlExe);
		$data->clean($tempDir);

		$groupsArray="ARRAY['".str_replace(', ',"','",$groups)."']";

		// Initialize the rollback operation and get its rollback id
		$isL = $isLogged ? 'true' : 'false';
		$isM = $isMulti ? 'true' : 'false';
		if ($this->getNumEmajVersion() >= 20100){	// version >= 2.1.0
			$sql1 = "SELECT \"{$this->emaj_schema}\"._rlbk_init({$groupsArray}, '{$mark}', {$isL}, 1, $isM, true) as rlbk_id";
		} else {
			$sql1 = "SELECT \"{$this->emaj_schema}\"._rlbk_init({$groupsArray}, '{$mark}', {$isL}, 1, $isM) as rlbk_id";
		}
		$rlbkId = $data->selectField($sql1,'rlbk_id');

		// Build the psql report file name, the SQL command and submit the rollback execution asynchronously
		$sql2 = "SELECT \"{$this->emaj_schema}\"._rlbk_async({$rlbkId},{$isM})";
		$psqlReport = "rlbk_{$rlbkId}_report";
		$this->execPsqlInBackground($psqlExe,$sql2,$tempDir,$psqlReport);

		return $rlbkId;
	}

	/**
	 * Execute an external psql command in background
	 */
	function execPsqlInBackground($psqlExe,$stmt,$tempDir,$psqlReport) {
		global $misc;

		// Set environment variables that psql needs to connect
		$server_info = $misc->getServerInfo();
		putenv('PGPASSWORD=' . $server_info['password']);
		putenv('PGUSER=' . $server_info['username']);
		$hostname = $server_info['host'];
		if ($hostname !== null && $hostname != '') {
			putenv('PGHOST=' . $hostname);
		}
		$port = $server_info['port'];
		if ($port !== null && $port != '') {
			putenv('PGPORT=' . $port);
		}

		// Build and submit the psql command
		if (substr(php_uname(), 0, 3) == "Win"){
			$psqlCmd = "{$psqlExe} -d {$_REQUEST['database']} -c \"{$stmt}\" -o {$tempDir}\\{$psqlReport} 2>&1";
			pclose(popen("start /b \"\" ". $psqlCmd, "r"));
		} else {
			$psqlCmd = "{$psqlExe} -d {$_REQUEST['database']} -c \"{$stmt}\" -o {$tempDir}/{$psqlReport} 2>&1";
			exec($psqlCmd . " > /dev/null &");
		}
	}

	/**
	 * Returns the list of marks usable to rollback a groups array.
	 */
	function getRollbackMarkGroups($groups) {

		global $data;

		$data->clean($groups);
		$groupsArray="ARRAY['".str_replace(', ',"','",$groups)."']";

// Attention, this statement needs postgres 8.4+, because of array_agg() function use
		if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
			$sql = "SELECT t.mark_name, t.mark_datetime, t.mark_is_rlbk_protected 
					FROM (SELECT mark_name, time_tx_timestamp as mark_datetime, mark_is_rlbk_protected,
								 array_agg (mark_group) AS groups 
						  FROM \"{$this->emaj_schema}\".emaj_mark,\"{$this->emaj_schema}\".emaj_group,
							   \"{$this->emaj_schema}\".emaj_time_stamp
						  WHERE mark_group = group_name AND time_id = mark_time_id 
							AND NOT mark_is_deleted AND group_is_rollbackable GROUP BY 1,2,3) AS t 
					WHERE t.groups @> $groupsArray
					ORDER BY t.mark_datetime DESC";
		}else{
			$sql = "SELECT t.mark_name, t.mark_datetime, t.mark_is_rlbk_protected 
					FROM (SELECT mark_name, mark_datetime, ";
			if ($this->getNumEmajVersion() >= 10300){	// version >= 1.3.0
				$sql .= "        mark_is_rlbk_protected, ";
			}else{
				$sql .= "        'f' AS mark_is_rlbk_protected, ";
			}
			$sql .= "            array_agg (mark_group) AS groups 
						FROM \"{$this->emaj_schema}\".emaj_mark,\"{$this->emaj_schema}\".emaj_group 
						WHERE mark_group = group_name AND ";
			if ($this->getNumEmajVersion() >= 10100){	// version >= 1.1.0
				$sql .= "NOT mark_is_deleted";
			}else{
				$sql .= "mark_state = 'ACTIVE'";
			}
			$sql .= " AND group_is_rollbackable GROUP BY 1,2,3) AS t 
					WHERE t.groups @> $groupsArray
					ORDER BY t.mark_datetime DESC";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Get timestamp of the youngest protected mark of a groups list.
	 * The function is only called when E-Maj version >= 1.3.0
	 */
	function getYoungestProtectedMarkTimestamp($groups) {

		global $data;

		$data->clean($groups);
		$groups="'".str_replace(', ',"','",$groups)."'";

		if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
			$sql = "SELECT max(time_tx_timestamp) AS youngest_mark_datetime 
					  FROM \"{$this->emaj_schema}\".emaj_mark , \"{$this->emaj_schema}\".emaj_time_stamp
					  WHERE time_id = mark_time_id
						AND mark_group IN ($groups) AND mark_is_rlbk_protected";
		}else{
			$sql = "SELECT max(mark_datetime) AS youngest_mark_datetime 
					  FROM \"{$this->emaj_schema}\".emaj_mark 
					  WHERE mark_group IN ($groups) AND mark_is_rlbk_protected";
		}

		return $data->selectField($sql,'youngest_mark_datetime');
	}

	/**
	 * Get the list of protected groups from a groups list.
	 */
	function getProtectedGroups($groups) {

		global $data;

		if ($this->getNumEmajVersion() < 10300){	// version < 1.3.0
			return '';
		}

		$data->clean($groups);
		$groups="'".str_replace(', ',"','",$groups)."'";

// Attention, this statement needs postgres 8.4+, because of array_agg() function use
		$sql = "SELECT string_agg(group_name, ', ') AS groups 
				  FROM \"{$this->emaj_schema}\".emaj_group 
				  WHERE group_name IN ($groups) AND group_is_rlbk_protected";

		return $data->selectField($sql,'groups');
	}

	/**
	 * Determines whether or not a mark name is valid as a mark to rollback to for a groups array
	 * Returns 1 if:
	 *   - the mark name is known and in ACTIVE state,
	 *   - no intermediate protected mark for any group would be covered by the rollback, 
	 * Retuns 0 otherwise.
	 */
	function isRollbackMarkValidGroups($groups,$mark) {

		global $data;

		$data->clean($groups);
		$groupsArray="ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);
		$nbGroups = substr_count($groupsArray,',') + 1;

		// check the mark is active (i.e. not deleted)
		$sql = "SELECT CASE WHEN 
				(SELECT COUNT(*) FROM \"{$this->emaj_schema}\".emaj_mark, \"{$this->emaj_schema}\".emaj_group 
					WHERE mark_group = group_name 
						AND mark_group = ANY ({$groupsArray}) AND group_is_rollbackable AND mark_name = '{$mark}' 
						AND ";
		if ($this->getNumEmajVersion() >= 10100){	// version >= 1.1.0
			$sql .= "NOT mark_is_deleted";
		}else{
			$sql .= "mark_state = 'ACTIVE'";
		}
		$sql .= ") = {$nbGroups} THEN 1 ELSE 0 END AS result";

		$result = $data->selectField($sql,'result');

		if ($result == 1) {
			// the mark is active, so now check there is no intermediate protected mark
			if ($this->getNumEmajVersion() >= 10300){	// version >= 1.3.0
				$sql = "SELECT CASE WHEN 
						(SELECT count(*) FROM \"{$this->emaj_schema}\".emaj_mark 
						  WHERE mark_group = ANY ({$groupsArray}) AND mark_id > 
							(SELECT mark_id FROM \"{$this->emaj_schema}\".emaj_mark 
							 WHERE mark_group = ANY({$groupsArray}) AND mark_name = '{$mark}' LIMIT 1
						    ) AND mark_is_rlbk_protected
						) = 0 THEN 1 ELSE 0 END AS result";
				$result = $data->selectField($sql,'result');
			}
		}

		return $result;
	}

	/**
	 * Rollbacks a groups array to a mark
	 */
	function rollbackGroups($groups,$mark,$isLogged) {
		global $data;

		$data->clean($groups);
		$groupsArray="ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);

		if ($this->getNumEmajVersion() >= 20100){	// version >= 2.1.0
			if ($isLogged){
				$sql = "SELECT sum(substring(rlbk_message from '^\d+')::integer) AS nbtblseq
						FROM \"{$this->emaj_schema}\".emaj_logged_rollback_groups({$groupsArray},'{$mark}',true)
						WHERE rlbk_severity = 'Notice'";
			}else{
				$sql = "SELECT sum(substring(rlbk_message from '^\d+')::integer) AS nbtblseq
						FROM \"{$this->emaj_schema}\".emaj_rollback_groups({$groupsArray},'{$mark}',true)
						WHERE rlbk_severity = 'Notice'";
			}
		}else{
			if ($isLogged){
				$sql = "SELECT \"{$this->emaj_schema}\".emaj_logged_rollback_groups({$groupsArray},'{$mark}') AS nbtblseq";
			}else{
				$sql = "SELECT\"{$this->emaj_schema}\".emaj_rollback_groups({$groupsArray},'{$mark}') AS nbtblseq";
			}
		}

		return $data->selectField($sql,'nbtblseq');
	}

	/**
	 * Gets the global rollback statistics for a group and a mark (i.e. total number of log rows to rollback)
	 */
	function getGlobalRlbkStatGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT coalesce(sum(stat_rows),0) as sumrows, count(*) as nbtables 
					FROM \"{$this->emaj_schema}\".emaj_log_stat_group('{$group}','{$mark}',NULL)
					WHERE stat_rows > 0";

		return $data->selectSet($sql);
	}

	/**
	 * Estimates the rollback duration for a group and a mark
	 */
	function estimateRollbackGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT to_char(\"{$this->emaj_schema}\".";
		if ($this->getNumEmajVersion() >= 10100){	// version >= 1.1.0
			$sql .= "emaj_estimate_rollback_group('{$group}','{$mark}',false)";
		}else{
			$sql .= "emaj_estimate_rollback_duration('{$group}','{$mark}')";
		}
		$sql .= "+'1 second'::interval,'HH24:MI:SS') as duration";

		return $data->selectField($sql,'duration');
	}

	/**
	 * Gets the list of lastest completed rollback operations
	 */
	function getCompletedRlbk($nb,$retention) {
		global $data;

		$data->clean($nb);
		$data->clean($retention);

// first cleanup recently completed rollback operation status
		$sql = "SELECT emaj.emaj_cleanup_rollback_state()";
		$data->execute($sql);

// get the latest rollback operations
		$sql = "SELECT rlbk_id, array_to_string(rlbk_groups,',') as rlbk_groups_list, rlbk_status,
					rlbk_start_datetime, rlbk_end_datetime,
					to_char(rlbk_end_datetime - rlbk_start_datetime,'HH24:MI:SS') as rlbk_duration, 
					rlbk_mark, rlbk_mark_datetime, rlbk_is_logged, rlbk_nb_session, rlbk_eff_nb_table,
					rlbk_nb_sequence ";
		if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
			$sql .= "FROM (SELECT *, tr.time_tx_timestamp as rlbk_start_datetime, tm.time_tx_timestamp as rlbk_mark_datetime
						  FROM emaj.emaj_rlbk, \"{$this->emaj_schema}\".emaj_time_stamp tr, \"{$this->emaj_schema}\".emaj_time_stamp tm 
						  WHERE tr.time_id = rlbk_time_id AND tm.time_id = rlbk_mark_time_id 
							AND rlbk_status IN ('COMPLETED','COMMITTED','ABORTED')";
		}else{
			$sql .= "FROM (SELECT * FROM emaj.emaj_rlbk 
					WHERE rlbk_status IN ('COMPLETED','COMMITTED','ABORTED')";
		}
		if ($retention > 0)
			$sql .= " AND rlbk_end_datetime > current_timestamp - '{$retention} hours'::interval "; 
		$sql .= " ORDER BY rlbk_id DESC ";
		if ($nb > 0)
			$sql .= "LIMIT {$nb}";
		$sql .= ") AS t";

		return $data->selectSet($sql);
	}

	/**
	 * Gets the list of in progress rollback operations
	 */
	function getInProgressRlbk() {
		global $data;

		$sql = "SELECT rlbk_id, array_to_string(rlbk_groups,',') as rlbk_groups_list, rlbk_mark,
					rlbk_mark_datetime, rlbk_is_logged,	rlbk_nb_session, rlbk_nb_table, rlbk_nb_sequence,
					rlbk_eff_nb_table, rlbk_status, rlbk_start_datetime,
					to_char(rlbk_elapse,'HH24:MI:SS') as rlbk_current_elapse, rlbk_remaining,
					rlbk_completion_pct 
				FROM emaj.emaj_rollback_activity() 
				ORDER BY rlbk_id DESC";

		return $data->selectSet($sql);
	}

	/**
	 * Gets the list of consolidable rollbacks (masking already consolidated rollbacks,i.e. with no intermediate mark and log)
	 */
	function getConsolidableRlbk() {
		global $data;

		$sql = "SELECT cons_group, cons_target_rlbk_mark_name, tt.time_tx_timestamp AS cons_target_rlbk_mark_datetime, 
					cons_end_rlbk_mark_name, rt.time_tx_timestamp AS cons_end_rlbk_mark_datetime, cons_rows, cons_marks
				FROM emaj.emaj_get_consolidable_rollbacks(),
					 emaj.emaj_mark tm, emaj.emaj_time_stamp tt, 
					 emaj.emaj_mark rm, emaj.emaj_time_stamp rt
				WHERE tt.time_id = tm.mark_time_id AND tm.mark_id = cons_target_rlbk_mark_id
				  AND rt.time_id = rm.mark_time_id AND rm.mark_id = cons_end_rlbk_mark_id
				  AND (cons_rows > 0 OR cons_marks > 0)
				ORDER BY cons_end_rlbk_mark_id";

		return $data->selectSet($sql);
	}

	/**
	 * Consolidates a rollback operation
	 */
	function consolidateRollback($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT \"{$this->emaj_schema}\".emaj_consolidate_rollback_group('{$group}','{$mark}') AS nbtblseq";

		return $data->selectField($sql,'nbtblseq');
	}

	/**
	 * Gets the global log statistics for a group between 2 marks
	 * It also delivers the sql queries to look at the corresponding log rows
	 * It creates a temp table to easily compute aggregates in other functions called in the same conversation
	 */
	function getLogStatGroup($group,$firstMark,$lastMark) {
		global $data;

		$data->clean($group);
		$data->clean($firstMark);
		$data->clean($lastMark);

		if ($lastMark==''){
			$sql = "CREATE TEMP TABLE tmp_stat AS
					SELECT stat_group, stat_schema, stat_table, ";
			if ($this->getNumEmajVersion() >= 203000){	// version >= 2.3.0
				$sql .= "stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime, ";
			}
            $sql .= "stat_rows, 
					'select * from ' || ";
			if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
				$sql .= "quote_ident(rel_log_schema)";
			}else{
				$sql .= "'{$this->emaj_schema}'";
			}
			if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
				$sql .= " || '.' || quote_ident(rel_log_table) || 
							' where emaj_gid > ' || strttime.time_last_emaj_gid ||
							' order by emaj_gid' as sql_text
						FROM \"{$this->emaj_schema}\".emaj_log_stat_group('{$group}','{$firstMark}',NULL), 
							\"{$this->emaj_schema}\".emaj_mark strtmark, \"{$this->emaj_schema}\".emaj_time_stamp strttime,
							\"{$this->emaj_schema}\".emaj_relation
						WHERE stat_rows > 0 
							AND strtmark.mark_group = '{$group}' 
							AND strtmark.mark_name = '{$firstMark}' 
							AND rel_schema = stat_schema AND rel_tblseq = stat_table
							AND strtmark.mark_time_id = strttime.time_id";
			}else{
				$sql .= " || '.' || quote_ident(stat_schema || '_' || stat_table || '_log') || 
							' where emaj_gid > ' || strtmark.mark_global_seq ||
							' order by emaj_gid' as sql_text
						FROM \"{$this->emaj_schema}\".emaj_log_stat_group('{$group}','{$firstMark}',NULL), 
							\"{$this->emaj_schema}\".emaj_mark strtmark, 
							\"{$this->emaj_schema}\".emaj_relation
						WHERE stat_rows > 0 
							AND strtmark.mark_group = '{$group}' 
							AND strtmark.mark_name = '{$firstMark}' 
							AND rel_schema = stat_schema AND rel_tblseq = stat_table";
			}
		}else{
			$sql = "CREATE TEMP TABLE tmp_stat AS
					SELECT stat_group, stat_schema, stat_table, ";
			if ($this->getNumEmajVersion() >= 203000){	// version >= 2.3.0
				$sql .= "stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime, ";
			}
            $sql .= "stat_rows, 
					'select * from ' || ";
			if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
				$sql .= "quote_ident(rel_log_schema)";
			}else{
				$sql .= "'{$this->emaj_schema}'";
			}
			if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
				$sql .= " || '.' || quote_ident(stat_schema || '_' || stat_table || '_log') ||
							' where emaj_gid > ' || strttime.time_last_emaj_gid ||
							' and emaj_gid <= ' || stoptime.time_last_emaj_gid ||
							' order by emaj_gid' as sql_text
						FROM \"{$this->emaj_schema}\".emaj_log_stat_group('{$group}','{$firstMark}','{$lastMark}'), 
							\"{$this->emaj_schema}\".emaj_mark strtmark, \"{$this->emaj_schema}\".emaj_time_stamp strttime, 
							\"{$this->emaj_schema}\".emaj_mark stopmark, \"{$this->emaj_schema}\".emaj_time_stamp stoptime, 
							\"{$this->emaj_schema}\".emaj_relation
						WHERE stat_rows > 0 
							AND strtmark.mark_group = '{$group}' 
							AND strtmark.mark_name = '{$firstMark}' 
							AND stopmark.mark_group = '{$group}' 
							AND stopmark.mark_name = '{$lastMark}' 
							AND rel_schema = stat_schema AND rel_tblseq = stat_table
							AND strtmark.mark_time_id = strttime.time_id
							AND stopmark.mark_time_id = stoptime.time_id";
			}else{
				$sql .= " || '.' || quote_ident(stat_schema || '_' || stat_table || '_log') ||
							' where emaj_gid > ' || strtmark.mark_global_seq ||
							' and emaj_gid <= ' || stopmark.mark_global_seq ||
							' order by emaj_gid' as sql_text
						FROM \"{$this->emaj_schema}\".emaj_log_stat_group('{$group}','{$firstMark}','{$lastMark}'), 
							\"{$this->emaj_schema}\".emaj_mark strtmark , \"{$this->emaj_schema}\".emaj_mark stopmark, 
							\"{$this->emaj_schema}\".emaj_relation
						WHERE stat_rows > 0 
							AND strtmark.mark_group = '{$group}' 
							AND strtmark.mark_name = '{$firstMark}' 
							AND stopmark.mark_group = '{$group}' 
							AND stopmark.mark_name = '{$lastMark}' 
							AND rel_schema = stat_schema AND rel_tblseq = stat_table";
			}
		}

		$data->execute($sql);

		$sql = "SELECT stat_group, stat_schema, stat_table, ";
		if ($this->getNumEmajVersion() >= 203000){	// version >= 2.3.0
			$sql .= "stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime, ";
		}
        $sql .= "stat_rows, sql_text FROM tmp_stat
				ORDER BY stat_group, stat_schema, stat_table";

		return $data->selectSet($sql);
	}

	/**
	 * Gets some aggregates from the temporary log_stat table created by the just previously called getLogStatGroup() function
	 */
	function getLogStatSummary() {
		global $data;

		$sql = "SELECT coalesce(sum(stat_rows),0) AS sum_rows, count(distinct stat_table) AS nb_tables 
				FROM tmp_stat";

		return $data->selectSet($sql);
	}

	/**
	 * Gets the detailed log statistics for a group between 2 marks
	 * It also delivers the sql queries to look at the corresponding log rows
	 * It creates a temp table to easily compute aggregates for the same conversation
	 */
	function getDetailedLogStatGroup($group,$firstMark,$lastMark) {
		global $data;

		$data->clean($group);
		$data->clean($firstMark);
		$data->clean($lastMark);

		if ($lastMark==''){
			$sql = "CREATE TEMP TABLE tmp_stat AS
					SELECT stat_group, stat_schema, stat_table, ";
			if ($this->getNumEmajVersion() >= 203000){	// version >= 2.3.0
				$sql .= "stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime, ";
			}
            $sql .= "stat_role, stat_verb, stat_rows, 
					'select * from ' || ";
			if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
				$sql .= "quote_ident(rel_log_schema)";
			}else{
				$sql .= "'{$this->emaj_schema}'";
			}
			if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
				$sql .=    " || '.' || quote_ident(stat_schema || '_' || stat_table || '_log') || 
							' where emaj_gid > ' || strttime.time_last_emaj_gid ||
							' and emaj_verb = ' || quote_literal(substring(stat_verb from 1 for 3)) || 
							' order by emaj_gid' as sql_text
						FROM \"{$this->emaj_schema}\".emaj_detailed_log_stat_group('{$group}','{$firstMark}',NULL), 
							\"{$this->emaj_schema}\".emaj_mark strtmark, \"{$this->emaj_schema}\".emaj_time_stamp strttime, 
							\"{$this->emaj_schema}\".emaj_relation
						WHERE stat_rows > 0 
							AND strtmark.mark_group = '{$group}' 
							AND strtmark.mark_name = '{$firstMark}' 
							AND rel_schema = stat_schema AND rel_tblseq = stat_table
							AND strtmark.mark_time_id = strttime.time_id";
			}else{
				$sql .=    " || '.' || quote_ident(stat_schema || '_' || stat_table || '_log') || 
							' where emaj_gid > ' || strtmark.mark_global_seq ||
							' and emaj_verb = ' || quote_literal(substring(stat_verb from 1 for 3)) || 
							' order by emaj_gid' as sql_text
						FROM \"{$this->emaj_schema}\".emaj_detailed_log_stat_group('{$group}','{$firstMark}',NULL), 
							\"{$this->emaj_schema}\".emaj_mark strtmark, \"{$this->emaj_schema}\".emaj_relation
						WHERE stat_rows > 0 
							AND strtmark.mark_group = '{$group}' 
							AND strtmark.mark_name = '{$firstMark}' 
							AND rel_schema = stat_schema AND rel_tblseq = stat_table";
			}
		}else{
			$sql = "CREATE TEMP TABLE tmp_stat AS
					SELECT stat_group, stat_schema, stat_table, ";
			if ($this->getNumEmajVersion() >= 203000){	// version >= 2.3.0
				$sql .= "stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime, ";
			}
            $sql .= "stat_role, stat_verb, stat_rows, 
					'select * from ' || ";
			if ($this->getNumEmajVersion() >= 10000){	// version >= 1.0.0
				$sql .= "quote_ident(rel_log_schema)";
			}else{
				$sql .= "'{$this->emaj_schema}'";
			}
			if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
				$sql .=    " || '.' || quote_ident(stat_schema || '_' || stat_table || '_log') || 
							' where emaj_gid > ' || strttime.time_last_emaj_gid ||
							' and emaj_gid <= ' || stoptime.time_last_emaj_gid ||
							' and emaj_verb = ' || quote_literal(substring(stat_verb from 1 for 3)) || 
							' order by emaj_gid' as sql_text
						FROM \"{$this->emaj_schema}\".emaj_detailed_log_stat_group('{$group}','{$firstMark}','{$lastMark}'), 
							\"{$this->emaj_schema}\".emaj_mark strtmark, \"{$this->emaj_schema}\".emaj_time_stamp strttime, 
							\"{$this->emaj_schema}\".emaj_mark stopmark, \"{$this->emaj_schema}\".emaj_time_stamp stoptime, 
							\"{$this->emaj_schema}\".emaj_relation
						WHERE stat_rows > 0 
							AND strtmark.mark_group = '{$group}' 
							AND strtmark.mark_name = '{$firstMark}' 
							AND stopmark.mark_group = '{$group}' 
							AND stopmark.mark_name = '{$lastMark}' 
							AND rel_schema = stat_schema AND rel_tblseq = stat_table
							AND strtmark.mark_time_id = strttime.time_id
							AND stopmark.mark_time_id = stoptime.time_id";
			}else{
				$sql .=    " || '.' || quote_ident(stat_schema || '_' || stat_table || '_log') || 
							' where emaj_gid > ' || strtmark.mark_global_seq ||
							' and emaj_gid <= ' || stopmark.mark_global_seq ||
							' and emaj_verb = ' || quote_literal(substring(stat_verb from 1 for 3)) || 
							' order by emaj_gid' as sql_text
						FROM \"{$this->emaj_schema}\".emaj_detailed_log_stat_group('{$group}','{$firstMark}','{$lastMark}'), 
							\"{$this->emaj_schema}\".emaj_mark strtmark , \"{$this->emaj_schema}\".emaj_mark stopmark, 
							\"{$this->emaj_schema}\".emaj_relation
						WHERE stat_rows > 0 
							AND strtmark.mark_group = '{$group}' 
							AND strtmark.mark_name = '{$firstMark}' 
							AND stopmark.mark_group = '{$group}' 
							AND stopmark.mark_name = '{$lastMark}' 
							AND rel_schema = stat_schema AND rel_tblseq = stat_table";
			}
		}
		$data->execute($sql);

		$sql = "SELECT stat_group, stat_schema, stat_table, ";
		if ($this->getNumEmajVersion() >= 203000){	// version >= 2.3.0
			$sql .= "stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime, ";
		}
        $sql .= "stat_role, stat_verb, stat_rows, sql_text FROM tmp_stat
				ORDER BY stat_group, stat_schema, stat_table, stat_role, stat_verb";

		return $data->selectSet($sql);
	}

	/**
	 * Gets some aggregates from the temporary log_stat table created by the just previously called getDetailedLogStatGroup() function
	 */
	function getDetailedLogStatSummary() {
		global $data;

		$sql = "SELECT coalesce(sum(stat_rows),0) AS sum_rows, count(distinct stat_table) AS nb_tables,
				coalesce((SELECT sum(stat_rows) FROM tmp_stat WHERE stat_verb = 'INSERT'),0) as nb_ins,
				coalesce((SELECT sum(stat_rows) FROM tmp_stat WHERE stat_verb = 'UPDATE'),0) as nb_upd,
				coalesce((SELECT sum(stat_rows) FROM tmp_stat WHERE stat_verb = 'DELETE'),0) as nb_del,
				coalesce((SELECT sum(stat_rows) FROM tmp_stat WHERE stat_verb = 'TRUNCATE'),0) as nb_tru,
				coalesce((SELECT count(distinct stat_role) FROM tmp_stat),0) as nb_roles
				FROM tmp_stat";

		return $data->selectSet($sql);
	}

	/**
	 * Gets distinct roles from the temporary log_stat table created by the just previously called getDetailedLogStatGroup() function
TODO : when 8.3 will not be supported any more, an aggregate function would be to be included into getDetailedLogStatSummary()
array_to_string(array_agg(stat_role), ',') puis (string_agg(stat_role), ',') en 9.0+
	 */
	function getDetailedLogStatRoles() {
		global $data;

		$sql = "SELECT distinct stat_role FROM tmp_stat ORDER BY 1";

		return $data->selectSet($sql);
	}

}
?>
