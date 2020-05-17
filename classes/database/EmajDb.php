<?php

/**
 * A class that implements the database access for Emaj_web.
 * It currently covers E-Maj versions starting from 1.3.x
 */

class EmajDb {

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
	private $dblink_schema = null;
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
				WHERE nspname='emaj'";
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
		if ($data->isSuperUser($server_info['username'])) {
			$this->emaj_adm = true;
		} else {
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
		if ($this->emaj_adm) {
		// emaj_adm role is also considered as E-maj viewer
			$this->emaj_viewer = true;
		} else {
		// otherwise, is the current role member of emaj_viewer role ?
			$sql = "SELECT CASE WHEN pg_catalog.pg_has_role('emaj_viewer','USAGE') THEN 1 ELSE 0 END AS is_emaj_viewer";
			$this->emaj_viewer = $data->selectField($sql,'is_emaj_viewer');
		}
		return $this->emaj_viewer;
	}

	/**
	 * Determines whether or not the E-Maj extension has been installed in the instance.
	 */
	function isExtensionAvailable() {
		global $data;

		$sql = "SELECT 1 FROM pg_catalog.pg_available_extensions WHERE name = 'emaj'";
		$rs = $data->selectSet($sql);
		if ($rs->recordCount() == 1) {
			return 1;
		}
		return 0;
	}

	/**
	 * Determines whether or not E-Maj has been created as an extension.
	 */
	function isExtension() {
		global $data;

		$sql = "SELECT 1 FROM pg_catalog.pg_extension WHERE extname = 'emaj'";
		$rs = $data->selectSet($sql);
		if ($rs->recordCount() == 1) {
			return 1;
		}
		return 0;
	}

	/**
	 * Returns the E-Maj extension versions available in the instance for a CREATE EXTENSION.
	 */
	function getAvailableExtensionVersions() {
		global $data;

		$sql = "SELECT version FROM pg_catalog.pg_available_extension_versions
				  WHERE name = 'emaj' AND NOT installed
				  ORDER BY version DESC";

		return $data->selectSet($sql);
	}

	/**
	 * Returns a boolean indicating whether one or several versions of the E-Maj extension are available for an ALTER EXTENSION UPDATE.
	 */
	function areThereVersionsToUpdate() {
		global $data;

		$sql = "SELECT CASE WHEN EXISTS (
				  SELECT target FROM pg_catalog.pg_extension_update_paths('emaj')
					WHERE source = '{$this->emaj_version}' AND path IS NOT NULL
				) THEN 1 ELSE 0 END AS versions_exist";

		return $data->selectField($sql,'versions_exist');
	}

	/**
	 * Returns the E-Maj extension versions available as target for an ALTER EXTENSION UPDATE.
	 */
	function getAvailableExtensionVersionsForUpdate() {
		global $data;

		$sql = "SELECT target FROM pg_catalog.pg_extension_update_paths('emaj')
				  WHERE source = '{$this->emaj_version}' AND path IS NOT NULL
				  ORDER BY 1 DESC";

		return $data->selectSet($sql);
	}

	/**
	 * Creates the emaj extension
	 */
	function createEmajExtension($version) {
		global $data, $misc;

		$data->clean($version);

		if ($version !== null && $version <> '')
			$version = "VERSION '{$version}'";
		else
			$version = '';

		$server_info = $misc->getServerInfo();
		if ($server_info["pgVersion"] < 9.6) {
			$sql = "CREATE EXTENSION IF NOT EXISTS dblink;
					CREATE EXTENSION IF NOT EXISTS btree_gist;
					CREATE EXTENSION emaj {$version};";
		} else {
			$sql = "CREATE EXTENSION emaj {$version};";
		}

		$status = $data->execute($sql);
		if ($status == 0) {
			// the extension has been created, so reset all emajdb cached variables
			$this->emaj_version = '?';
			$this->emaj_version_num = 0;
			$this->enabled = null;
			$this->accessible = null;
			$this->emaj_adm = null;
			$this->emaj_viewer = null;
			$this->dblink_usable = null;
			$this->dblink_schema = null;
			$this->asyncRlbkUsable = null;
		}
		return $status;
	}

	/**
	 * Updates the emaj extension
	 */
	function updateEmajExtension($version) {
		global $data, $misc;

		$data->clean($version);

		if ($version !== null && $version <> '')
			$version = "TO '{$version}'";
		else
			$version = '';

		$sql = "ALTER EXTENSION emaj UPDATE {$version};";

		$status = $data->execute($sql);
		if ($status == 0) {
			// the extension version has changed, so reset all emajdb cached variables
			$this->emaj_version = '?';
			$this->emaj_version_num = 0;
			$this->enabled = null;
			$this->accessible = null;
			$this->emaj_adm = null;
			$this->emaj_viewer = null;
			$this->dblink_usable = null;
			$this->dblink_schema = null;
			$this->asyncRlbkUsable = null;
		}
		return $status;
	}

	/**
	 * Drops the emaj extension
	 * The emaj_adm and emaj_viewer roles are not dropped as they can bu used in other databases
	 */
	function dropEmajExtension() {
		global $data;

		$sql = "DO LANGUAGE plpgsql $$
				BEGIN
					PERFORM emaj.emaj_disable_protection_by_event_triggers();
					DROP EXTENSION IF EXISTS emaj CASCADE;
					DROP SCHEMA IF EXISTS emaj CASCADE;
					DROP FUNCTION IF EXISTS public._emaj_protection_event_trigger_fnct() CASCADE;
					RETURN;
				END;$$;";
		$status = $data->execute($sql);
		if ($status == 0) {
			// the extension has been dropped, so reset all emajdb cached variables
			$this->emaj_version = '?';
			$this->emaj_version_num = 0;
			$this->enabled = null;
			$this->accessible = null;
			$this->emaj_adm = null;
			$this->emaj_viewer = null;
			$this->dblink_usable = null;
			$this->dblink_schema = null;
			$this->asyncRlbkUsable = null;
		}
		return $status;
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
	 * Determines whether or not the asynchronous rollback can be used for the current user.
	 * Parameter: $useCache = boolean to be explicitely set to false to force the check
	 * It checks that:
	 * - dblink is effectively usable
	 * - the psql_path and temp_dir parameters from the configuration file are set and usable
	 * If they are set, one tries to use them.
	 */
	function isAsyncRlbkUsable($useCache = true) {

		// Return from the cache if possible
		if ($useCache && $this->asyncRlbkUsable !== null) return $this->asyncRlbkUsable;

		global $misc, $data, $conf;

		$this->asyncRlbkUsable = 0;

		// check if dblink is usable
		if ($this->isDblinkUsable()) {
			// if the _dblink_open_cnx() function is available for the user, 
			//   open a test dblink connection, analyse the result and close it if effetively opened
			$test_cnx_ok = 0;
			$sql = "SELECT CASE
						WHEN pg_catalog.has_function_privilege('emaj._dblink_open_cnx(text)', 'EXECUTE')
							THEN 1 ELSE 0 END as grant_open_ok";
			if ($data->selectField($sql,'grant_open_ok')) {
				if ($this->getNumEmajVersion() >= 30100){	// version >= 3.1.0
					$sql = "SELECT CASE WHEN v_status >= 0 THEN 1 ELSE 0 END as cnx_ok, v_schema
							FROM emaj._dblink_open_cnx('test')";
					$rs = $data->selectSet($sql);
					if ($rs->fields['cnx_ok']) {
						$this->dblink_schema = $rs->fields['v_schema'];
						$sql = "SELECT emaj._dblink_close_cnx('test', '{$this->dblink_schema}')";
						$data->execute($sql);
						$test_cnx_ok = 1;
					}
				} else {
					$sql = "SELECT CASE WHEN emaj._dblink_open_cnx('test') >= 0 THEN 1 ELSE 0 END as cnx_ok";
					if ($data->selectField($sql,'cnx_ok')) {
						$sql = "SELECT emaj._dblink_close_cnx('test')";
						$data->execute($sql);
						$test_cnx_ok = 1;
					}
				}
			}
			if ($test_cnx_ok) {
				// check if the emaj_web parameters are set
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
						WHERE relnamespace = pg_namespace.oid AND relname = 'emaj_visible_param' AND nspname = 'emaj')
				THEN 'emaj_visible_param' ELSE 'emaj_param' END AS param_table";
		$rs = $data->selectSet($sql);
		if ($rs->recordCount() == 1){
			$param_table = $rs->fields['param_table'];

			// search the 'emaj_version' parameter into the proper view or table
			$sql = "SELECT param_value_text AS version
					FROM emaj.{$param_table}
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
				if ($this->emaj_version == '<NEXT_VERSION>' || $this->emaj_version == '<devel>'){
					$this->emaj_version = htmlspecialchars($this->emaj_version);
					$this->emaj_version_num = 999999;
				}
			}
		}
		return;
	}

	/**
	 * Gets the E-Maj size on disk 
	 * = size of all relations in emaj primary and secondary schemas + size of linked toast tables
	 */
	function getEmajSize() {
		global $data;

		if ($this->emaj_adm){
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
								AND nspname = rel_log_schema 
								AND c1.relname = rel_log_table
							) as t2
						WHERE pg_settings.name = 'block_size'
					) as t";
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

		$sql = "SELECT
					CASE WHEN emaj_verify_all ILIKE 'No error%' THEN 4
						 WHEN emaj_verify_all LIKE 'Warning%' THEN 3
						 ELSE 1
					END AS rpt_severity,
					emaj_verify_all AS rpt_message
				  FROM emaj.emaj_verify_all()
				  ORDER BY rpt_severity DESC";
		return $data->selectSet($sql);
	}

	/**
	 * Get the parameters stored into the emaj_param table
	 */
	function getExtensionParams() {
		global $data;

		if (!$this->isEmaj_Adm()) {
			$table = 'emaj_visible_param';
		} else {
			$table = 'emaj_param';
		}

		$sql = "SELECT
					param_key,
					CASE
						WHEN param_key IN ('dblink_user_password', 'alter_log_table') THEN coalesce(param_value_text, '')
						WHEN param_key = 'history_retention' THEN coalesce(param_value_interval::text, '1 YEAR')
						WHEN param_key = 'avg_row_rollback_duration' THEN coalesce(to_char(param_value_interval,'US'), '100')
						WHEN param_key = 'avg_row_delete_log_duration' THEN coalesce(to_char(param_value_interval,'US'), '10')
						WHEN param_key = 'avg_fkey_check_duration' THEN coalesce(to_char(param_value_interval,'US'), '20')
						WHEN param_key = 'fixed_step_rollback_duration' THEN coalesce(to_char(param_value_interval,'US'), '2500')
						WHEN param_key = 'fixed_table_rollback_duration' THEN coalesce(to_char(param_value_interval,'US'), '1000')
						WHEN param_key = 'fixed_dblink_rollback_duration' THEN coalesce(to_char(param_value_interval,'US'), '4000')
					END AS param_value
					FROM emaj.{$table}
					WHERE param_key <> 'emaj_version'";

		return $data->selectSet($sql);
	}

	/**
	 * Export the parameters configuration
	 */
	function exportParamConfig() {
		global $data;

		$sql = "SELECT emaj.emaj_export_parameters_configuration() AS parameter_configuration";
		return $data->selectField($sql,'parameter_configuration');
	}

	/**
	 * Check a parameters configuration to import
	 */
	function checkJsonParamConf($json) {
		global $data, $lang;

		$data->clean($json);

		$sql = "SELECT
				rpt_severity,
				CASE rpt_msg_type
					WHEN 101 THEN '" . $data->clean($lang['emajcheckjsonparamconf101']) . "'
					WHEN 102 THEN format('" . $data->clean($lang['emajcheckjsonparamconf102']) . "', rpt_int_var_1)
					WHEN 103 THEN format('" . $data->clean($lang['emajcheckjsonparamconf103']) . "', rpt_text_var_1, rpt_text_var_2)
					WHEN 104 THEN format('" . $data->clean($lang['emajcheckjsonparamconf104']) . "', rpt_text_var_1)
					WHEN 105 THEN format('" . $data->clean($lang['emajcheckjsonparamconf105']) . "', rpt_text_var_1)
                    ELSE 'Message not decoded (' || rpt_msg_type || ')'
				END as rpt_message
			FROM emaj._check_json_param_conf(E'{$json}'::json)
			ORDER BY rpt_msg_type, rpt_text_var_1, rpt_text_var_2, rpt_int_var_1";

		return $data->selectSet($sql);
	}

	/**
	 * Import the parameters configuration
	 */
	function importParamConfig($paramConfig, $replaceCurrent) {
		global $data;

		$data->clean($paramConfig);

		if ($replaceCurrent) { $bool = 'true'; } else { $bool = 'false'; }
		$sql = "SELECT emaj.emaj_import_parameters_configuration(E'" . $paramConfig . "'::json, " . $bool . ") AS nb_parameters";
		return $data->selectField($sql,'nb_parameters');
	}

	// GROUPS

	/**
	 * Gets all groups referenced in emaj_group table for this database
	 * The function is called to feed the browser tree or to list the groups to export (emaj 3.3+)
	 */
	function getGroups() {
		global $data;

		$sql = "SELECT group_name, group_comment";
		if ($this->getNumEmajVersion() >= 30300){	// version >= 3.3.0
			$sql .= ", group_nb_table, group_nb_sequence, 
					  CASE WHEN group_is_logging THEN 'LOGGING' ELSE 'IDLE' END 
						as group_state, 
					  CASE WHEN group_is_rollbackable THEN 'ROLLBACKABLE' ELSE 'AUDIT_ONLY' END 
						as group_type";
		}
		$sql .=	" FROM emaj.emaj_group ORDER BY group_name";

		return $data->selectSet($sql);
	}

	/**
	 * Gets number of created groups
	 */
	function getNbGroups() {
		global $data;

		$sql = "SELECT count(*) as nb_groups FROM emaj.emaj_group";

		return $data->selectField($sql,'nb_groups');
	}

	/**
	 * Gets all idle groups referenced in emaj_group table for this database
	 */
	function getIdleGroups() {
		global $data;

		if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence, group_comment,
					  CASE WHEN group_is_rollbackable THEN 'ROLLBACKABLE' ELSE 'AUDIT_ONLY' END 
						as group_type, 
					  to_char(time_tx_timestamp,'DD/MM/YYYY HH24:MI:SS') as creation_datetime,";
			if ($this->getNumEmajVersion() >= 30000){	// version >= 3.0.0
				$sql .=	"CASE WHEN group_has_waiting_changes THEN 1 ELSE 0 END as has_waiting_changes,";
			} else {
				$sql .=	"1 as has_waiting_changes,";
			}
			$sql .= " (SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM emaj.emaj_group, emaj.emaj_time_stamp
					WHERE NOT group_is_logging
					  AND time_id = group_creation_time_id
					ORDER BY group_name";
		}else{
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence, group_comment,
					  CASE WHEN group_is_rollbackable THEN 'ROLLBACKABLE' ELSE 'AUDIT_ONLY' END 
						as group_type, 
					  to_char(group_creation_datetime,'DD/MM/YYYY HH24:MI:SS') as creation_datetime,
					  1 as has_waiting_changes,
					  (SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM emaj.emaj_group
					WHERE NOT group_is_logging 
					ORDER BY group_name";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Gets all Logging groups referenced in emaj_group table for this database
	 */
	function getLoggingGroups() {
		global $data;

		if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence, group_comment,
					  CASE WHEN NOT group_is_rollbackable THEN 'AUDIT_ONLY'
						   WHEN group_is_rollbackable AND NOT group_is_rlbk_protected THEN 'ROLLBACKABLE'
						   ELSE 'ROLLBACKABLE-PROTECTED' END as group_type,
					  to_char(time_tx_timestamp,'DD/MM/YYYY HH24:MI:SS') as creation_datetime,";
			if ($this->getNumEmajVersion() >= 30000){	// version >= 3.0.0
				$sql .=	"CASE WHEN group_has_waiting_changes THEN 1 ELSE 0 END as has_waiting_changes,";
			} else {
				$sql .=	"1 as has_waiting_changes,";
			}
			$sql .= " (SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM emaj.emaj_group, emaj.emaj_time_stamp
					WHERE group_is_logging
					  AND time_id = group_creation_time_id
					ORDER BY group_name";
		}else{
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence, group_comment,
						CASE WHEN NOT group_is_rollbackable THEN 'AUDIT_ONLY'
							 WHEN group_is_rollbackable AND NOT group_is_rlbk_protected THEN 'ROLLBACKABLE'
							 ELSE 'ROLLBACKABLE-PROTECTED' END as group_type, 
						to_char(group_creation_datetime,'DD/MM/YYYY HH24:MI:SS') as creation_datetime,
						(SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM emaj.emaj_group
					WHERE group_is_logging 
					ORDER BY group_name";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Gets all configured but not yet created groups.
	 * They are referenced in emaj_group_def but not in emaj_group tables.
	 * Also return counters: number of tables, sequences.
	 */
	function getConfiguredGroups() {
		global $data;

		if ($this->getNumEmajVersion() >= 30000){	// version >= 3.0.0

			$sql = "WITH
					grp AS (
						SELECT DISTINCT grpdef_group AS group_name FROM emaj.emaj_group_def
					EXCEPT
						SELECT group_name FROM emaj.emaj_group
					),
					rel AS (
					SELECT grpdef_group, relkind
					FROM emaj.emaj_group_def
						JOIN grp ON grpdef_group = group_name
						LEFT OUTER JOIN pg_catalog.pg_namespace ON grpdef_schema = nspname
						LEFT OUTER JOIN pg_catalog.pg_class ON pg_namespace.oid = relnamespace AND grpdef_tblseq = relname
					)
					SELECT grpdef_group,
						count(CASE WHEN relkind = 'r' THEN 1 END) AS group_nb_table,
						count(CASE WHEN relkind = 'S' THEN 1 END) AS group_nb_sequence
					FROM rel
					GROUP BY 1
					ORDER BY 1
					";

		} else {

			if ($data->hasWithOids()) {
				$badTypeConditions = "c.relpersistence <> 'p' OR c.relhasoids";
			} else {
				$badTypeConditions = "c.relpersistence <> 'p'";
			}

			$sql = "WITH
					grp AS (
						SELECT DISTINCT grpdef_group AS group_name FROM emaj.emaj_group_def
					EXCEPT
						SELECT group_name FROM emaj.emaj_group
					),
					rel AS (
					SELECT d.grpdef_group, d.grpdef_schema, d.grpdef_tblseq, relkind,
						CASE WHEN nspname IS NULL THEN true ELSE false END as no_schema,                -- the schema doesn't exist
						CASE WHEN d.grpdef_schema like 'emaj%' THEN true ELSE false END as bad_schema,  -- the schema is an emaj_schema
						CASE WHEN relname IS NULL THEN true ELSE false END as no_relation,              -- the relation doesn't exist
						CASE WHEN relkind = 'r' AND ( ${badTypeConditions} ) 
								THEN true ELSE false END as bad_type,                                   -- the table has not the right type
						CASE WHEN rel_group IS NOT NULL THEN true ELSE false END as duplicate           -- the relation is already assigned to another created group
					FROM emaj.emaj_group_def d
						JOIN grp ON d.grpdef_group = group_name
						LEFT OUTER JOIN pg_catalog.pg_namespace n ON d.grpdef_schema = nspname
						LEFT OUTER JOIN pg_catalog.pg_class c ON n.oid = c.relnamespace AND d.grpdef_tblseq = c.relname
						LEFT OUTER JOIN emaj.emaj_relation ON d.grpdef_schema = rel_schema AND d.grpdef_tblseq = rel_tblseq ";
			if ($this->getNumEmajVersion() >= 20200){	// version >= 2.2.0
				$sql .=	"AND upper_inf(rel_time_range) ";
			}
			$sql .= ")
					SELECT grpdef_group,
						count(CASE WHEN relkind = 'r' and not (no_schema OR no_relation OR bad_type OR bad_schema OR duplicate) THEN 1 END) AS group_nb_table,
						count(CASE WHEN relkind = 'S' and not (no_schema OR no_relation OR bad_type OR bad_schema OR duplicate) THEN 1 END) AS group_nb_sequence,
						ARRAY[count(CASE WHEN no_schema THEN 1 END)
								,count(CASE WHEN bad_schema THEN 1 END)
								,count(CASE WHEN no_relation THEN 1 END)
								,count(CASE WHEN bad_type THEN 1 END)
								,count(CASE WHEN duplicate THEN 1 END)
								] as group_diagnostic
					FROM rel
					GROUP BY 1
					ORDER BY 1
					";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Gets some details about existing groups among a set of configured groups to import
	 */
	function getGroupsToImport($configuredGroupsValues) {
		global $data;

		$sql = "SELECT grp_name, group_comment, group_nb_table, group_nb_sequence, 
					   CASE WHEN group_is_logging THEN 'LOGGING'
						    WHEN NOT group_is_logging THEN 'IDLE'
						    ELSE NULL END as group_state,
					   CASE WHEN NOT group_is_rollbackable THEN 'AUDIT_ONLY'
						    WHEN group_is_rollbackable AND NOT group_is_rlbk_protected THEN 'ROLLBACKABLE'
						    WHEN group_is_rollbackable AND group_is_rlbk_protected THEN 'ROLLBACKABLE-PROTECTED'
						    ELSE NULL END as group_type,
					   CASE WHEN length(group_comment) > 100 THEN substr(group_comment,1,97) || '...' ELSE group_comment END 
					  	 as abbr_comment
					FROM (VALUES $configuredGroupsValues) AS t(grp_name)
						LEFT OUTER JOIN emaj.emaj_group ON (group_name = grp_name)
					ORDER BY group_name";

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
					CASE WHEN group_is_rollbackable THEN 'ROLLBACKABLE' ELSE 'AUDIT_ONLY' END as group_type, 
					group_comment, 
					pg_size_pretty((SELECT sum(pg_total_relation_size('\"' || rel_log_schema || '\".\"' || rel_log_table || '\"'))
						FROM emaj.emaj_relation 
						WHERE rel_group = group_name AND rel_kind = 'r')::bigint) as log_size,";
			if ($this->getNumEmajVersion() >= 30000){	// version >= 3.0.0
				$sql .=	"CASE WHEN group_has_waiting_changes THEN 1 ELSE 0 END as has_waiting_changes,";
			} else {
				$sql .=	"1 as has_waiting_changes,";
			}
			$sql .= " (SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM emaj.emaj_group, emaj.emaj_time_stamp
					WHERE group_name = '{$group}'
					  AND time_id = group_creation_time_id";
		} else {
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence, group_creation_datetime, 
						CASE WHEN group_is_logging THEN 'LOGGING' ELSE 'IDLE' END as group_state, 
						CASE WHEN NOT group_is_rollbackable THEN 'AUDIT_ONLY'
							 WHEN group_is_rollbackable AND NOT group_is_rlbk_protected THEN 'ROLLBACKABLE' 
							 ELSE 'ROLLBACKABLE-PROTECTED' END as group_type, 
						group_comment, 
						pg_size_pretty((SELECT sum(pg_total_relation_size(
										'\"' || rel_log_schema || '\".\"' || rel_schema || '_' || rel_tblseq || '_log\"')) 
										FROM emaj.emaj_relation WHERE rel_group = group_name AND rel_kind = 'r')::bigint) as log_size,
						(SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
							FROM emaj.emaj_group
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
				FROM emaj.emaj_group
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
				FROM emaj.emaj_group
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
				FROM emaj.emaj_group
				WHERE group_name = '{$group}'";

		return $data->selectField($sql,'is_protected');
	}

	/**
	 * Export a tables groups configuration
	 */
	function exportGroupsConfig($groups) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		$sql = "SELECT emaj.emaj_export_groups_configuration({$groupsArray}) AS groups_configuration";
		return $data->selectField($sql,'groups_configuration');
	}

	/**
	 * Prepare the a tables groups configuration import
	 */
	function importGroupsConfPrepare($groupsConfig, $groups) {
		global $data, $lang;

		$data->clean($groupsConfig);
		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		// The tables groups configuration import prepare step is inserted into a transaction,
		//   so that it can be properly canceled if errors are detected
		// The transaction will be commited just later in the importGroupsConfig() function call
		$status = $data->beginTransaction();

		$sql = "SELECT rpt_severity,
				CASE rpt_msg_type
					WHEN  1 THEN format('" . $data->clean($lang['emajcheckconfgroups01']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN  2 THEN format('" . $data->clean($lang['emajcheckconfgroups02']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN  3 THEN format('" . $data->clean($lang['emajcheckconfgroups03']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN  4 THEN format('" . $data->clean($lang['emajcheckconfgroups04']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN  5 THEN format('" . $data->clean($lang['emajcheckconfgroups05']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 10 THEN format('" . $data->clean($lang['emajcheckconfgroups10']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 11 THEN format('" . $data->clean($lang['emajcheckconfgroups11']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 12 THEN format('" . $data->clean($lang['emajcheckconfgroups12']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 13 THEN format('" . $data->clean($lang['emajcheckconfgroups13']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 20 THEN format('" . $data->clean($lang['emajcheckconfgroups20']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 21 THEN format('" . $data->clean($lang['emajcheckconfgroups21']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 22 THEN format('" . $data->clean($lang['emajcheckconfgroups22']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 30 THEN format('" . $data->clean($lang['emajcheckconfgroups30']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 31 THEN format('" . $data->clean($lang['emajcheckconfgroups31']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 32 THEN format('" . $data->clean($lang['emajcheckconfgroups32']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 33 THEN format('" . $data->clean($lang['emajcheckconfgroups33']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 250 THEN format('" . $data->clean($lang['emajgroupsconfimport250']) . "', rpt_text_var_1)
					WHEN 251 THEN format('" . $data->clean($lang['emajgroupsconfimport251']) . "', rpt_text_var_1)
					WHEN 252 THEN format('" . $data->clean($lang['emajgroupsconfimport252']) . "', rpt_text_var_1)
					WHEN 260 THEN format('" . $data->clean($lang['emajgroupsconfimport260']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 261 THEN format('" . $data->clean($lang['emajgroupsconfimport261']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
                    ELSE 'Message not decoded (' || rpt_msg_type || ')'
				END as rpt_message
			FROM emaj._import_groups_conf_prepare (E'{$groupsConfig}'::json, {$groupsArray}, true, NULL)";

		$errors = $data->selectSet($sql);

		if ($errors->recordCount() != 0) {
			$data->rollbackTransaction();
		}

		return $errors;
	}

	/**
	 * Import a tables groups configuration
	 */
	function importGroupsConfig($groupsConfig, $groups) {
		global $data;

		$data->clean($groupsConfig);
		$data->clean($groups);

		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		$sql = "SELECT emaj._import_groups_conf_exec(E'{$groupsConfig}'::json, {$groupsArray}) AS nb_groups";
		$nbGroups = $data->selectField($sql,'nb_groups');

		// Commit the transaction started in the importGroupsConfPrepare() function call
		$data->endTransaction();

		return $nbGroups;
	}

	/**
	 * Gets all marks related to a group
	 */
	function getMarks($group) {
		global $data;

		$data->clean($group);

		if ($this->getNumEmajVersion() >= 20000) {	// version >= 2.0.0
			$sql = "WITH mark_1 as (
						SELECT mark_time_id, mark_group, mark_name, time_tx_timestamp as mark_datetime, mark_comment,
							CASE WHEN mark_is_deleted THEN 'DELETED'
								 WHEN NOT mark_is_deleted AND mark_is_rlbk_protected THEN 'ACTIVE-PROTECTED'
								 ELSE 'ACTIVE' END as mark_state, 
							coalesce(mark_log_rows_before_next,
							(SELECT SUM(stat_rows) 
								FROM emaj.emaj_log_stat_group(emaj_mark.mark_group,emaj_mark.mark_name,NULL)),0)
							 AS mark_logrows
						FROM emaj.emaj_mark, emaj.emaj_time_stamp 
						WHERE mark_group = '{$group}'
						  AND time_id = mark_time_id
						)
					SELECT mark_group, mark_name, mark_datetime, mark_comment, mark_state, mark_logrows, 
						   sum(mark_logrows) OVER (ORDER BY mark_time_id DESC) AS mark_cumlogrows
					FROM mark_1
					ORDER BY mark_time_id DESC";
		} else {
			$sql = "WITH mark_1 as (
						SELECT mark_id, mark_group, mark_name, mark_datetime, mark_comment,
							CASE WHEN mark_is_deleted THEN 'DELETED'
								 WHEN NOT mark_is_deleted AND mark_is_rlbk_protected THEN 'ACTIVE-PROTECTED'
								 ELSE 'ACTIVE' END as mark_state, 
							coalesce(mark_log_rows_before_next,
							(SELECT SUM(stat_rows) 
								FROM emaj.emaj_log_stat_group(emaj_mark.mark_group,emaj_mark.mark_name,NULL)),0)
							 AS mark_logrows
						FROM emaj.emaj_mark
						WHERE mark_group = '{$group}'
						)
					SELECT mark_group, mark_name, mark_datetime, mark_comment, mark_state, mark_logrows, 
						   sum(mark_logrows) OVER (ORDER BY mark_id DESC) AS mark_cumlogrows
					FROM mark_1
					ORDER BY mark_id DESC";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Gets number of marks for a group
	 */
	function getNbMarks($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT count(*) as nb_marks FROM emaj.emaj_mark WHERE mark_group = '{$group}'";

		return $data->selectField($sql,'nb_marks');
	}

	/**
	 * Gets the content of one emaj_group 
	 */
	function getContentGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT rel_schema, rel_tblseq, rel_kind || '+' AS relkind, rel_priority,
                    rel_log_dat_tsp, rel_log_idx_tsp,
					rel_log_schema || '.' || rel_log_table as full_log_table,
					CASE WHEN rel_kind = 'r' THEN 
						pg_total_relation_size(quote_ident(rel_log_schema) || '.' || quote_ident(rel_log_table))
					END AS byte_log_size,
					CASE WHEN rel_kind = 'r' THEN 
						pg_size_pretty(pg_total_relation_size(quote_ident(rel_log_schema) || '.' || quote_ident(rel_log_table)))
					END AS pretty_log_size 
				FROM emaj.emaj_relation
				WHERE rel_group = '{$group}'";
		if ($this->getNumEmajVersion() >= 22000){	// version >= 2.2.0
			$sql .= " AND upper_inf(rel_time_range)";
		}
		$sql .= "		ORDER BY rel_schema, rel_tblseq";

		return $data->selectSet($sql);
	}

	/**
	 * Return the list of all existing emaj schemas recorded in the emaj_schema table
	 */
	function getEmajSchemasList() {
		global $data;

		$sql = "SELECT string_agg(sch_name, ',' ORDER BY sch_name) AS schemas_list FROM emaj.emaj_schema";

		return $data->selectField($sql,'schemas_list');
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
					  nspname != 'information_schema' AND nspname != 'emaj' ";
		if ($this->getNumEmajVersion() >= 22000){			// version >= 2.2.0
			$sql .=
				"AND nspname NOT IN (SELECT sch_name FROM emaj.emaj_schema) ";
		} else {
			$sql .=
				"AND nspname NOT IN (SELECT DISTINCT rel_log_schema FROM emaj.emaj_relation WHERE rel_log_schema IS NOT NULL) ";
		}
		$sql .= "UNION
				SELECT DISTINCT 2, grpdef_schema AS nspname, '!' AS nspowner, NULL AS nspcomment
				FROM emaj.emaj_group_def
				WHERE grpdef_schema NOT IN ( SELECT nspname FROM pg_catalog.pg_namespace )
				ORDER BY 1, nspname";

		return $data->selectSet($sql);
	}

	/**
	 * Return all existing non system schemas in the databse
	 */
	function getAllSchemas() {
		global $data;

		$sql = "WITH emaj_schemas AS (";
		if ($this->getNumEmajVersion() >= 22000){			// version >= 2.2.0
			$sql .= "SELECT sch_name FROM emaj.emaj_schema) ";
		} else {
			$sql .= "SELECT DISTINCT rel_log_schema AS sch_name FROM emaj.emaj_relation WHERE rel_log_schema IS NOT NULL
					 UNION SELECT 'emaj') ";
		}
		$sql .= "SELECT pn.nspname, pu.rolname AS nspowner,
						pg_catalog.obj_description(pn.oid, 'pg_namespace') AS nspcomment,
						CASE WHEN EXISTS (SELECT 0 FROM emaj_schemas WHERE sch_name = nspname) THEN 'E' ELSE '' END AS nsptype
				 FROM pg_catalog.pg_namespace pn
					 LEFT JOIN pg_catalog.pg_roles pu ON (pn.nspowner = pu.oid)
				 WHERE nspname NOT LIKE 'pg@_%' ESCAPE '@' AND nspname != 'information_schema'
				 ORDER BY nspname";

		return $data->selectSet($sql);
	}

	/**
	 * Return all tables of a schema, with their current E-Maj characteristics
	 */
	function getTables($schema) {
		global $data;

		$data->clean($schema);

		if ($data->hasWithOids()) {
			$goodTypeConditions = "c.relpersistence = 'p' and not c.relhasoids";
		} else {
			$goodTypeConditions = "c.relpersistence = 'p'";
		}

		$sql = "SELECT nspname, c.relname,
					c.relkind || case when (relkind = 'r' and ${goodTypeConditions}) then '+' else '-' end as relkind,
					pg_catalog.pg_get_userbyid(c.relowner) AS relowner,
					pg_catalog.obj_description(c.oid, 'pg_class') AS relcomment, spcname AS tablespace,
					coalesce(rel_group, '') AS rel_group, coalesce(rel_priority::text, '') AS rel_priority,
					coalesce(rel_log_dat_tsp, '') AS rel_log_dat_tsp, coalesce(rel_log_idx_tsp, '') AS rel_log_idx_tsp
					FROM pg_catalog.pg_class c
						LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
						LEFT JOIN emaj.emaj_relation ON rel_schema = nspname AND rel_tblseq = c.relname ";
		if ($this->getNumEmajVersion() >= 20200){	// version >= 2.2.0
			$sql .= "AND upper_inf(rel_time_range)";
		}
			$sql .= "LEFT JOIN pg_catalog.pg_tablespace pt ON pt.oid = c.reltablespace
					WHERE c.relkind IN ('r','p') AND nspname='{$schema}'
				ORDER BY relname";

		return $data->selectSet($sql);
	}

	/**
	 * Return all sequences of a schema, with their current E-Maj characteristics
	 */
	function getSequences($schema) {
		global $data;

		$data->clean($schema);

		$sql = "SELECT nspname, c.relname AS seqname, c.relkind,
					pg_catalog.pg_get_userbyid(c.relowner) AS seqowner,
					pg_catalog.obj_description(c.oid, 'pg_class') AS relcomment,
					coalesce(rel_group, '') AS rel_group
					FROM pg_catalog.pg_class c
						LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
						LEFT JOIN emaj.emaj_relation ON rel_schema = nspname AND rel_tblseq = c.relname ";
		if ($this->getNumEmajVersion() >= 20200){	// version >= 2.2.0
			$sql .= "AND upper_inf(rel_time_range)";
		}
			$sql .= "LEFT JOIN pg_catalog.pg_tablespace pt ON pt.oid = c.reltablespace
					WHERE c.relkind = 'S' AND nspname='{$schema}'
				ORDER BY relname";

		return $data->selectSet($sql);
	}

	/**
	 * Returns properties of a single sequence
	 */
	function getSequenceProperties($schema, $sequence) {
		global $data, $misc;

		$data->clean($schema);
		$data->clean($sequence);

		$server_info = $misc->getServerInfo();

		if ($server_info["pgVersion"] < 10) {				// Postgres version < 10
			$sql = "SELECT last_value, is_called, start_value, min_value, max_value, increment_by, is_cycled AS cycle,
							cache_value AS cache_size, log_cnt,
							pg_catalog.obj_description(s.tableoid, 'pg_class') AS seqcomment
					FROM \"{$sequence}\" AS s, pg_catalog.pg_class c, pg_catalog.pg_namespace n
					WHERE c.relnamespace = n.oid
						AND c.relname = '{$sequence}' AND n.nspname='{$schema}' AND c.relkind = 'S'";
		} else {
			$sql = "SELECT s.last_value, is_called, start_value, min_value, max_value, increment_by, cycle, cache_size, log_cnt,
							pg_catalog.obj_description(s.tableoid, 'pg_class') AS seqcomment
					FROM \"{$sequence}\" AS s, pg_catalog.pg_sequences, pg_catalog.pg_class c, pg_catalog.pg_namespace n
					WHERE c.relnamespace = n.oid
						AND schemaname = '{$schema}' AND sequencename = '{$sequence}'
						AND c.relname = '{$sequence}' AND n.nspname='{$schema}' AND c.relkind = 'S'";
		}

		return $data->selectSet( $sql );
	}

	/**
	 * Return all tables and sequences of a schema, 
	 * plus all non existent tables but listed in emaj_group_def with this schema
	 */
	function getTablesSequences($schema) {
		global $data;

		$data->clean($schema);

		if ($data->hasWithOids()) {
			$goodTypeConditions = "c.relpersistence = 'p' and not c.relhasoids";
		} else {
			$goodTypeConditions = "c.relpersistence = 'p'";
		}

		if ($this->getNumEmajVersion() >= 30100){			// version >= 3.1.0
			$sql = "SELECT 1, nspname, c.relname,
						c.relkind || case when relkind = 'S' or (relkind = 'r' and ${goodTypeConditions}) then '+' else '-' end as relkind,
						pg_catalog.pg_get_userbyid(c.relowner) AS relowner,
						pg_catalog.obj_description(c.oid, 'pg_class') AS relcomment, spcname AS tablespace,
						grpdef_group, grpdef_priority, grpdef_log_dat_tsp, grpdef_log_idx_tsp
						FROM pg_catalog.pg_class c
							LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
							LEFT JOIN emaj.emaj_group_def ON grpdef_schema = nspname AND grpdef_tblseq = c.relname
							LEFT JOIN pg_catalog.pg_tablespace pt ON pt.oid = c.reltablespace
						WHERE c.relkind IN ('r','S','p') AND nspname='{$schema}'
					UNION
					SELECT 2, grpdef_schema AS nspname, grpdef_tblseq AS relname, '!' AS relkind, NULL, NULL, NULL, 
						grpdef_group , grpdef_priority, grpdef_log_dat_tsp, grpdef_log_idx_tsp 
						FROM emaj.emaj_group_def
						WHERE grpdef_schema = '{$schema}' AND grpdef_tblseq NOT IN 
							(SELECT relname FROM pg_catalog.pg_class, pg_catalog.pg_namespace
								WHERE relnamespace = pg_namespace.oid AND nspname = '{$schema}' AND relkind IN ('r','S'))
					ORDER BY 1, relname";
		} else {
			$sql = "SELECT 1, nspname, c.relname,
						c.relkind || case when relkind = 'S' or (relkind = 'r' and ${goodTypeConditions}) then '+' else '-' end as relkind,
						pg_catalog.pg_get_userbyid(c.relowner) AS relowner,
						pg_catalog.obj_description(c.oid, 'pg_class') AS relcomment, spcname AS tablespace,
						grpdef_group, grpdef_priority, grpdef_log_schema_suffix, grpdef_emaj_names_prefix, grpdef_log_dat_tsp, grpdef_log_idx_tsp
						FROM pg_catalog.pg_class c
							LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
							LEFT JOIN emaj.emaj_group_def ON grpdef_schema = nspname AND grpdef_tblseq = c.relname
							LEFT JOIN pg_catalog.pg_tablespace pt ON pt.oid = c.reltablespace
						WHERE c.relkind IN ('r','S','p') AND nspname='{$schema}'
					UNION
					SELECT 2, grpdef_schema AS nspname, grpdef_tblseq AS relname, '!' AS relkind, NULL, NULL, NULL, 
						grpdef_group , grpdef_priority, grpdef_log_schema_suffix, grpdef_emaj_names_prefix, grpdef_log_dat_tsp, grpdef_log_idx_tsp 
						FROM emaj.emaj_group_def
						WHERE grpdef_schema = '{$schema}' AND grpdef_tblseq NOT IN 
							(SELECT relname FROM pg_catalog.pg_class, pg_catalog.pg_namespace
								WHERE relnamespace = pg_namespace.oid AND nspname = '{$schema}' AND relkind IN ('r','S'))
					ORDER BY 1, relname";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Gets group names already known in the emaj_group and emaj_group_def tables
	 */
	function getKnownGroups() {
		global $data;

		$sql = "SELECT group_name
				  FROM emaj.emaj_group
				UNION
				SELECT DISTINCT grpdef_group
				  FROM emaj.emaj_group_def
				ORDER BY 1";
		return $data->selectSet($sql);
	}

	/**
	 * Gets group names already known in the emaj_group table
	 */
	function getCreatedgroups() {
		global $data;

		$sql = "SELECT group_name
				  FROM emaj.emaj_group
				ORDER BY 1";
		return $data->selectSet($sql);
	}

	/**
	 * Gets log schema suffix already known in the emaj_group_def table
	 */
	function getKnownSuffix() {
		global $data;

		$sql = "SELECT DISTINCT grpdef_log_schema_suffix AS known_suffix 
				FROM emaj.emaj_group_def
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
		$sql = "INSERT INTO emaj.emaj_group_def (grpdef_schema, grpdef_tblseq, grpdef_group, grpdef_priority,";
		if ($this->getNumEmajVersion() < 30100){			// version < 3.1.0
			$sql .= "grpdef_log_schema_suffix, grpdef_emaj_names_prefix,";
		}
		$sql .= "grpdef_log_dat_tsp, grpdef_log_idx_tsp) 
					VALUES ('{$schema}', '{$tblseq}', '{$group}' ";
		if ($priority == '')
			$sql .= ", NULL";
		else
			$sql .= ", {$priority}";
		if ($this->getNumEmajVersion() < 30100){			// version < 3.1.0
			if ($logSchemaSuffix == '' || $relkind == 'S')
				$sql .= ", NULL";
			else
				$sql .= ", '{$logSchemaSuffix}'";
			if ($emajNamesPrefix == '' || $relkind == 'S')
				$sql .= ", NULL";
			else
				$sql .= ", '{$emajNamesPrefix}'";
		}
		if ($logDatTsp == '' || $relkind == 'S')
			$sql .= ", NULL";
		else
			$sql .= ", '{$logDatTsp}'";
		if ($logIdxTsp == '' || $relkind == 'S')
			$sql .= ", NULL";
		else
			$sql .= ", '{$logIdxTsp}'";
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
		if ($this->getNumEmajVersion() < 30100){			// version < 3.1.0
			if ($logSchemaSuffix == '' || $relkind == 'S')
				$sql .= ", grpdef_log_schema_suffix = NULL";
			else
				$sql .= ", grpdef_log_schema_suffix = '{$logSchemaSuffix}'";
			if ($emajNamesPrefix == '' || $relkind == 'S')
				$sql .= ", grpdef_emaj_names_prefix = NULL";
			else
				$sql .= ", grpdef_emaj_names_prefix = '{$emajNamesPrefix}'";
		}
		if ($logDatTsp == '' || $relkind == 'S')
			$sql .= ", grpdef_log_dat_tsp = NULL";
		else
			$sql .= ", grpdef_log_dat_tsp = '{$logDatTsp}'";
		if ($logIdxTsp == '' || $relkind == 'S')
			$sql .= ", grpdef_log_idx_tsp = NULL";
		else
			$sql .= ", grpdef_log_idx_tsp = '{$logIdxTsp}'";
		$sql .= " WHERE grpdef_schema = '{$schema}' AND grpdef_tblseq = '{$tblseq}' AND grpdef_group = '{$groupOld}'";

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
	 * Dynamically assign tables to a tables group
	 */
	function assignTables($schema,$tables,$group,$priority,$logDatTsp,$logIdxTsp,$mark) {
		global $data;

		$data->clean($schema);
		$data->clean($tables);
		$data->clean($group);
		$data->clean($priority);
		$data->clean($logDatTsp);
		$data->clean($logIdxTsp);
		$data->clean($mark);

		// Build the tables array
		$tablesArray = "ARRAY['" . str_replace(", ", "','", $tables) . "']";

		// Build the JSON structure
		if ($priority == '') $priority = 'null';
		if ($logDatTsp == '') $logDatTsp = 'null'; else $logDatTsp = "\"{$logDatTsp}\"";
		if ($logIdxTsp == '') $logIdxTsp = 'null'; else $logIdxTsp = "\"{$logIdxTsp}\"";
		$properties = "{\"priority\": {$priority}, \"log_data_tablespace\": {$logDatTsp}, \"log_index_tablespace\": {$logIdxTsp}}";

		$sql = "SELECT emaj.emaj_assign_tables('{$schema}',{$tablesArray},'{$group}','{$properties}'::jsonb,'{$mark}') AS nb_tables";

		return $data->selectField($sql,'nb_tables');
	}

	/**
	 * Dynamically move tables into another tables groups
	 */
	function moveTables($schema,$tables,$group,$mark) {
		global $data;

		$data->clean($schema);
		$data->clean($tables);
		$data->clean($group);
		$data->clean($mark);

		// Build the tables array
		$tablesArray = "ARRAY['" . str_replace(", ", "','", $tables) . "']";

		$sql = "SELECT emaj.emaj_move_tables('{$schema}',{$tablesArray},'{$group}','{$mark}') AS nb_tables";

		return $data->selectField($sql,'nb_tables');
	}

	/**
	 * Dynamically modify tables in their tables groups
	 */
	function modifyTables($schema,$tables,$priority,$logDatTsp,$logIdxTsp,$mark) {
		global $data;

		$data->clean($schema);
		$data->clean($tables);
		$data->clean($priority);
		$data->clean($logDatTsp);
		$data->clean($logIdxTsp);
		$data->clean($mark);

		// Build the tables array
		$tablesArray = "ARRAY['" . str_replace(", ", "','", $tables) . "']";

		// Build the JSON structure
		if ($priority == '') $priority = 'null';
		if ($logDatTsp == '') $logDatTsp = 'null'; else $logDatTsp = "\"{$logDatTsp}\"";
		if ($logIdxTsp == '') $logIdxTsp = 'null'; else $logIdxTsp = "\"{$logIdxTsp}\"";
		$properties = "{\"priority\": {$priority}, \"log_data_tablespace\": {$logDatTsp}, \"log_index_tablespace\": {$logIdxTsp}}";

		$sql = "SELECT emaj.emaj_modify_tables('{$schema}',{$tablesArray},'{$properties}'::jsonb,'{$mark}') AS nb_tables";

		return $data->selectField($sql,'nb_tables');
	}

	/**
	 * Dynamically remove tables from their tables groups
	 */
	function removeTables($schema,$tables,$mark) {
		global $data;

		$data->clean($schema);
		$data->clean($tables);
		$data->clean($mark);

		// Build the tables array
		$tablesArray = "ARRAY['" . str_replace(", ", "','", $tables) . "']";

		$sql = "SELECT emaj.emaj_remove_tables('{$schema}',{$tablesArray},'{$mark}') AS nb_tables";

		return $data->selectField($sql,'nb_tables');
	}

	/**
	 * Dynamically assign sequences to a tables group
	 */
	function assignSequences($schema,$sequences,$group,$mark) {
		global $data;

		$data->clean($schema);
		$data->clean($sequences);
		$data->clean($group);
		$data->clean($mark);

		// Build the sequences array
		$sequencesArray = "ARRAY['" . str_replace(", ", "','", $sequences) . "']";

		$sql = "SELECT emaj.emaj_assign_sequences('{$schema}',{$sequencesArray},'{$group}','{$mark}') AS nb_sequences";

		return $data->selectField($sql,'nb_sequences');
	}

	/**
	 * Dynamically move sequences into another tables groups
	 */
	function moveSequences($schema,$sequences,$group,$mark) {
		global $data;

		$data->clean($schema);
		$data->clean($sequences);
		$data->clean($group);
		$data->clean($mark);

		// Build the sequences array
		$sequencesArray = "ARRAY['" . str_replace(", ", "','", $sequences) . "']";

		$sql = "SELECT emaj.emaj_move_sequences('{$schema}',{$sequencesArray},'{$group}','{$mark}') AS nb_sequences";

		return $data->selectField($sql,'nb_sequences');
	}

	/**
	 * Dynamically remove sequences from their tables groups
	 */
	function removeSequences($schema,$sequences,$mark) {
		global $data;

		$data->clean($schema);
		$data->clean($sequences);
		$data->clean($mark);

		// Build the sequences array
		$sequencesArray = "ARRAY['" . str_replace(", ", "','", $sequences) . "']";

		$sql = "SELECT emaj.emaj_remove_sequences('{$schema}',{$sequencesArray},'{$mark}') AS nb_sequences";

		return $data->selectField($sql,'nb_sequences');
	}

	/**
	 * Determines whether or not a group name is valid as a new empty group
	 * Returns 1 if the group name is not already known, 0 otherwise.
	 */
	function isNewEmptyGroupValid($group) {

		global $data;

		$data->clean($group);

		$sql = "SELECT CASE WHEN 
				(SELECT COUNT(*) FROM emaj.emaj_group WHERE group_name = '{$group}') +
				(SELECT COUNT(*) FROM emaj.emaj_group_def WHERE grpdef_group = '{$group}')
				= 0 THEN 1 ELSE 0 END AS result";

		return $data->selectField($sql,'result');
	}

	/**
	 * Check in the emaj_group_def table the configuration of a new group to create
	 * The function is not called anymore since E-Maj version 3.2+
	 */
	function checkConfNewGroup($group) {
		global $data, $lang;

		$data->clean($group);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$group)."']";

		$sql = "SELECT chk_severity,
				CASE chk_msg_type
					WHEN  1 THEN format('" . $data->clean($lang['emajcheckconfgroups01']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN  2 THEN format('" . $data->clean($lang['emajcheckconfgroups02']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN  3 THEN format('" . $data->clean($lang['emajcheckconfgroups03']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN  4 THEN format('" . $data->clean($lang['emajcheckconfgroups04']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN  5 THEN format('" . $data->clean($lang['emajcheckconfgroups05']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 10 THEN format('" . $data->clean($lang['emajcheckconfgroups10']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 11 THEN format('" . $data->clean($lang['emajcheckconfgroups11']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 12 THEN format('" . $data->clean($lang['emajcheckconfgroups12']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 13 THEN format('" . $data->clean($lang['emajcheckconfgroups13']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 20 THEN format('" . $data->clean($lang['emajcheckconfgroups20']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 21 THEN format('" . $data->clean($lang['emajcheckconfgroups21']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 22 THEN format('" . $data->clean($lang['emajcheckconfgroups22']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 30 THEN format('" . $data->clean($lang['emajcheckconfgroups30']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 31 THEN format('" . $data->clean($lang['emajcheckconfgroups31']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 32 THEN format('" . $data->clean($lang['emajcheckconfgroups32']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 33 THEN format('" . $data->clean($lang['emajcheckconfgroups33']) . "', chk_group, chk_schema, chk_tblseq)
				END as chk_message
			FROM emaj._check_conf_groups ($groupsArray)";

		return $data->selectSet($sql);
	}

	/**
	 * Check in the emaj_group_def table the configuration of one or serveral existing groups to alter
	 * The function is not called anymore since E-Maj version 3.2+
	 */
	function checkConfExistingGroups($groups) {
		global $data, $lang;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		$sql = "SELECT chk_severity,
				CASE chk_msg_type
					WHEN  1 THEN format('" . $data->clean($lang['emajcheckconfgroups01']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN  2 THEN format('" . $data->clean($lang['emajcheckconfgroups02']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN  3 THEN format('" . $data->clean($lang['emajcheckconfgroups03']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN  4 THEN format('" . $data->clean($lang['emajcheckconfgroups04']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN  5 THEN format('" . $data->clean($lang['emajcheckconfgroups05']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 10 THEN format('" . $data->clean($lang['emajcheckconfgroups10']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 11 THEN format('" . $data->clean($lang['emajcheckconfgroups11']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 12 THEN format('" . $data->clean($lang['emajcheckconfgroups12']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 13 THEN format('" . $data->clean($lang['emajcheckconfgroups13']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 20 THEN format('" . $data->clean($lang['emajcheckconfgroups20']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 21 THEN format('" . $data->clean($lang['emajcheckconfgroups21']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 22 THEN format('" . $data->clean($lang['emajcheckconfgroups22']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 30 THEN format('" . $data->clean($lang['emajcheckconfgroups30']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 31 THEN format('" . $data->clean($lang['emajcheckconfgroups31']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 32 THEN format('" . $data->clean($lang['emajcheckconfgroups32']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 33 THEN format('" . $data->clean($lang['emajcheckconfgroups33']) . "', chk_group, chk_schema, chk_tblseq)
				END as chk_message
			FROM emaj._check_conf_groups ($groupsArray), emaj.emaj_group
	        WHERE 	chk_group = group_name
				AND ((group_is_rollbackable AND chk_severity <= 2)
				  OR (NOT group_is_rollbackable AND chk_severity <= 1))";

		return $data->selectSet($sql);
	}

	/**
	 * Check that a JSON structure representing tables groups configuration is valid, before importing it
	 */
	function checkJsonGroupsConf($json) {
		global $data, $lang;

		$data->clean($json);

		$sql = "SELECT
				rpt_severity,
				CASE rpt_msg_type
					WHEN 201 THEN '" . $data->clean($lang['emajcheckjsongroupsconf201']) . "'
					WHEN 202 THEN format('" . $data->clean($lang['emajcheckjsongroupsconf202']) . "', rpt_text_var_1)
					WHEN 210 THEN format('" . $data->clean($lang['emajcheckjsongroupsconf210']) . "', rpt_int_var_1)
					WHEN 211 THEN format('" . $data->clean($lang['emajcheckjsongroupsconf211']) . "', rpt_text_var_1, rpt_int_var_1)
					WHEN 212 THEN format('" . $data->clean($lang['emajcheckjsongroupsconf212']) . "', rpt_text_var_1)
					WHEN 220 THEN format('" . $data->clean($lang['emajcheckjsongroupsconf220']) . "', rpt_text_var_1, rpt_int_var_1)
					WHEN 221 THEN format('" . $data->clean($lang['emajcheckjsongroupsconf221']) . "', rpt_text_var_1, rpt_int_var_1)
					WHEN 222 THEN format('" . $data->clean($lang['emajcheckjsongroupsconf222']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 223 THEN format('" . $data->clean($lang['emajcheckjsongroupsconf223']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 224 THEN format('" . $data->clean($lang['emajcheckjsongroupsconf224']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_int_var_1)
					WHEN 225 THEN format('" . $data->clean($lang['emajcheckjsongroupsconf225']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 230 THEN format('" . $data->clean($lang['emajcheckjsongroupsconf230']) . "', rpt_text_var_1, rpt_int_var_1)
					WHEN 231 THEN format('" . $data->clean($lang['emajcheckjsongroupsconf231']) . "', rpt_text_var_1, rpt_int_var_1)
					WHEN 232 THEN format('" . $data->clean($lang['emajcheckjsongroupsconf232']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
                    ELSE 'Message not decoded (' || rpt_msg_type || ')'
				END as rpt_message
			FROM emaj._check_json_groups_conf(E'{$json}'::json)
			ORDER BY rpt_msg_type, rpt_text_var_1, rpt_text_var_2, rpt_text_var_3";

		return $data->selectSet($sql);
	}

	/**
	 * Creates a group, and comment if requested
	 */
	function createGroup($group,$isRollbackable,$isEmpty,$comment) {
		global $data;

		$data->clean($group);
		$data->clean($comment);

		if ($isEmpty) {
			if ($isRollbackable){
				$sql = "SELECT emaj.emaj_create_group('{$group}',true,true) AS nbtblseq";
			}else{
				$sql = "SELECT emaj.emaj_create_group('{$group}',false,true) AS nbtblseq";
			}
		} else {
			if ($isRollbackable){
				$sql = "SELECT emaj.emaj_create_group('{$group}',true) AS nbtblseq";
			}else{
				$sql = "SELECT emaj.emaj_create_group('{$group}',false) AS nbtblseq";
			}
		}
		$rt = $data->execute($sql);

		if ($comment <> '') {
			$sql = "SELECT emaj.emaj_comment_group('{$group}','{$comment}')";
			$data->execute($sql);
		}

		return $rt;
	}

	/**
	 * Drops a group
	 */
	function dropGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT emaj.emaj_drop_group('{$group}') AS nbtblseq";

		return $data->execute($sql);
	}

	/**
	 * Drops several groups at once
	 */
	function dropGroups($groups) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		$sql = "SELECT emaj.emaj_drop_group(group_name) FROM emaj.emaj_group
				  WHERE group_name = ANY ({$groupsArray})";

		return $data->execute($sql);
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
				FROM emaj.emaj_group
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
						SELECT rel_schema, rel_tblseq FROM emaj.emaj_relation WHERE rel_group = '{$group}'
							EXCEPT
						SELECT grpdef_schema, grpdef_tblseq FROM emaj.emaj_group_def WHERE grpdef_group = '{$group}'
					) as t";
			if ($data->selectField($sql,'nb_errors') > 0 ) { return 0; }
		}

		// if the emaj version is prior 2.3.0, check no table or sequence would be added to the group
		if ($this->getNumEmajVersion() < 20300){	// version < 2.3.0
			$sql = "SELECT count(*) as nb_errors FROM (
						SELECT grpdef_schema, grpdef_tblseq FROM emaj.emaj_group_def WHERE grpdef_group = '{$group}'
							EXCEPT
						SELECT rel_schema, rel_tblseq FROM emaj.emaj_relation WHERE rel_group = '{$group}'";
			if ($this->getNumEmajVersion() >= 20200){	// version >= 2.2.0
				$sql .= " AND upper_inf(rel_time_range) ";
			}
			$sql .= ") as t";
			if ($data->selectField($sql,'nb_errors') > 0 ) { return 0; }
		}

		// check that no table or sequence would be repaired for the group
		$sql = "SELECT count(*) as nb_errors FROM emaj._verify_groups(ARRAY['{$group}'], false)";
		if ($data->selectField($sql,'nb_errors') > 0 ) { return 0; }

		// all checks are ok
		return 1;
	}

	/**
	 * Alters a group
	 */
	function alterGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		if ($mark == '') {
			$sql = "SELECT emaj.emaj_alter_group('{$group}') AS nbtblseq";
		} else {
			$sql = "SELECT emaj.emaj_alter_group('{$group}', '{$mark}') AS nbtblseq";
		}
		return $data->execute($sql);
	}

	/**
	 * Alters several groups
	 */
	function alterGroups($groups,$mark) {
		global $data;

		$data->clean($groups);
		$data->clean($mark);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		if ($mark == '') {
			$sql = "SELECT emaj.emaj_alter_groups({$groupsArray}) AS nbtblseq";
		} else {
			$sql = "SELECT emaj.emaj_alter_groups({$groupsArray}, '{$mark}') AS nbtblseq";
		}

		return $data->execute($sql);
	}

	/**
	 * Sets a comment for a group
	 */
	function setCommentGroup($group,$comment) {
		global $data;

		$data->clean($group);
		$data->clean($comment);

		$sql = "SELECT emaj.emaj_comment_group('{$group}','{$comment}')";

		return $data->execute($sql);
	}

	/**
	 * Determines whether or not a mark name is valid as a new mark to set for a group or a groups array
     * It also resolves the % meta character in the mark name
	 * Returns NULL if the mark is not valid, or the final mark name (with % characters replaced).
	 */
	function isNewMarkValidGroups($groups,$mark) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);

		if ($mark == '' or $mark == 'EMAJ_LAST_MARK') {
			return NULL;
		}

		# replace the % characters by the time of day, in format 'HH24.MI.SS.MS'
		$finalMark = str_replace('%', strftime('%H.%M.%S.') . substr(microtime(),2,3), $mark);

		$sql = "SELECT CASE WHEN 
				(SELECT COUNT(*) FROM emaj.emaj_mark 
				   WHERE mark_group = ANY ({$groupsArray}) AND mark_name = '{$finalMark}')
				= 0 THEN 1 ELSE 0 END AS result";

		if ($data->selectField($sql,'result') == 0) {
			return NULL;
		} else {
			return $finalMark;
		}
	}

	/**
	 * Computes the number of active mark in a group.
	 */
	function nbActiveMarkGroup($group) {

		global $data;

		$data->clean($group);

		$sql = "SELECT COUNT(*) as result FROM emaj.emaj_mark WHERE mark_group = '{$group}'";

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

		if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
			$sql = "SELECT CASE WHEN mark_time_id = 
							(SELECT MIN (mark_time_id) FROM emaj.emaj_mark WHERE mark_group = '{$group}')
							THEN 1 ELSE 0 END AS result
					FROM emaj.emaj_mark
					WHERE mark_group = '{$group}' AND mark_name = '{$mark}'";
		} else {
			$sql = "SELECT CASE WHEN mark_id = 
							(SELECT MIN (mark_id) FROM emaj.emaj_mark WHERE mark_group = '{$group}')
							THEN 1 ELSE 0 END AS result
					FROM emaj.emaj_mark
					WHERE mark_group = '{$group}' AND mark_name = '{$mark}'";
		}

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
			$sql = "SELECT emaj.emaj_start_group('{$group}','{$mark}') AS nbtblseq";
		}else{
			$sql = "SELECT emaj.emaj_start_group('{$group}','{$mark}',false) AS nbtblseq";
		}

		return $data->execute($sql);
	}

	/**
	 * Starts several groups
	 */
	function startGroups($groups,$mark,$resetLog) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);

		if ($resetLog){
			$sql = "SELECT emaj.emaj_start_groups({$groupsArray},'{$mark}') AS nbtblseq";
		}else{
			$sql = "SELECT emaj.emaj_start_groups({$groupsArray},'{$mark}',false) AS nbtblseq";
		}

		return $data->execute($sql);
	}

	/**
	 * Stops a group
	 */
	function stopGroup($group,$mark,$forceStop) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		if ($forceStop){
			$sql = "SELECT emaj.emaj_force_stop_group('{$group}') AS nbtblseq";
		}else{
			if ($mark == ""){
				$sql = "SELECT emaj.emaj_stop_group('{$group}') AS nbtblseq";
			}else{
				$sql = "SELECT emaj.emaj_stop_group('{$group}','{$mark}') AS nbtblseq";
			}
		}

		return $data->execute($sql);
	}

	/**
	 * Stops several groups at once
	 */
	function stopGroups($groups,$mark) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);
			if ($mark == ""){
			$sql = "SELECT emaj.emaj_stop_groups({$groupsArray}) AS nbtblseq";
		}else{
			$sql = "SELECT emaj.emaj_stop_groups({$groupsArray},'{$mark}') AS nbtblseq";
		}

		return $data->execute($sql);
	}

	/**
	 * Resets a group
	 */
	function resetGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT emaj.emaj_reset_group('{$group}') AS nbtblseq";

		return $data->execute($sql);
	}

	/**
	 * Resets several groups at once
	 */
	function resetGroups($groups) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		$sql = "SELECT emaj.emaj_reset_group(group_name) FROM emaj.emaj_group
				  WHERE group_name = ANY ({$groupsArray})";

		return $data->execute($sql);
	}

	/**
	 * Protects a group
	 */
	function protectGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT emaj.emaj_protect_group('{$group}') AS status";

		return $data->execute($sql);
	}

	/**
	 * Unprotects a group
	 */
	function unprotectGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT emaj.emaj_unprotect_group('{$group}') AS status";

		return $data->execute($sql);
	}

	/**
	 * Sets a mark for a group and comments if requested
	 */
	function setMarkGroup($group,$mark,$comment) {
		global $data;

		$data->clean($group);
		$data->clean($mark);
		$data->clean($comment);

		$sql = "SELECT emaj.emaj_set_mark_group('{$group}','{$mark}') AS nbtblseq";
		$rt = $data->execute($sql);

		if ($comment <> '') {
			$sql = "SELECT emaj.emaj_comment_mark_group('{$group}','{$mark}','{$comment}')";
			$data->execute($sql);
		}

		return $rt;
	}

	/**
	 * Sets a mark for several groups and comments if requested
	 */
	function setMarkGroups($groups,$mark,$comment) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);
		$data->clean($comment);

		$sql = "SELECT emaj.emaj_set_mark_groups({$groupsArray},'{$mark}') AS nbtblseq";
		$rt = $data->execute($sql);

		if ($comment <> '') {
		// Set a comment for each group of the groups list
			$groupsA = explode(', ',$groups);
			foreach($groupsA as $g) {
				$sql = "SELECT emaj.emaj_comment_mark_group('{$g}','{$mark}','{$comment}')";
				$data->execute($sql);
			}
		}

		return $rt;
	}

	/**
	 * Gets properties of one mark 
	 */
	function getMark($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT mark_name, mark_group, mark_comment 
				FROM emaj.emaj_mark
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

		$sql = "SELECT emaj.emaj_comment_mark_group('{$group}','{$mark}','{$comment}')";

		return $data->execute($sql);
	}

	/**
	 * Protects a mark for a group
	 */
	function protectMarkGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT emaj.emaj_protect_mark_group('{$group}','{$mark}') AS status";

		return $data->execute($sql);
	}

	/**
	 * Unprotects a mark for a group
	 */
	function unprotectMarkGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT emaj.emaj_unprotect_mark_group('{$group}','{$mark}') AS status";

		return $data->execute($sql);
	}

	/**
	 * Deletes a mark for a group
	 */
	function deleteMarkGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT emaj.emaj_delete_mark_group('{$group}','{$mark}')";

		return $data->execute($sql);
	}

	/**
	 * Deletes all marks before a mark for a group
	 */
	function deleteBeforeMarkGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT emaj.emaj_delete_before_mark_group('{$group}','{$mark}') as nbmark";

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

		$sql = "SELECT emaj.emaj_rename_mark_group('{$group}','{$mark}','{$newMark}')";

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
					FROM emaj.emaj_mark, emaj.emaj_time_stamp 
					WHERE mark_group = '{$group}'
					  AND NOT mark_is_deleted
					  AND time_id = mark_time_id
					ORDER BY mark_time_id DESC";
		}else{
			$sql = "SELECT mark_name, mark_datetime, mark_is_rlbk_protected 
					  FROM emaj.emaj_mark 
					  WHERE NOT mark_is_deleted
						AND mark_group = '$group'
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
				 (SELECT 0 FROM emaj.emaj_mark 
                   WHERE mark_group = '{$group}' AND mark_name = '{$mark}' AND NOT mark_is_deleted
				  ) THEN 1 ELSE 0 END AS is_active";

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
			if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
				$sql = "SELECT CASE WHEN 
						(SELECT count(*) FROM emaj.emaj_mark 
						WHERE mark_group = '{$group}' AND mark_time_id > 
							(SELECT mark_time_id FROM emaj.emaj_mark 
							WHERE mark_group = '{$group}' AND mark_name = '{$mark}'
							) AND mark_is_rlbk_protected
						) = 0 THEN 1 ELSE 0 END AS result";
			} else {
				$sql = "SELECT CASE WHEN 
						(SELECT count(*) FROM emaj.emaj_mark 
						WHERE mark_group = '{$group}' AND mark_id > 
							(SELECT mark_id FROM emaj.emaj_mark 
							WHERE mark_group = '{$group}' AND mark_name = '{$mark}'
							) AND mark_is_rlbk_protected
						) = 0 THEN 1 ELSE 0 END AS result";
			}
			$result = $data->selectField($sql,'result');
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

		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
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
				  WHEN altr_step = 'CHANGE_REL_PRIORITY' THEN
					format('{$emajlang['emajalteredchangerelpriority']}', quote_ident(altr_schema), quote_ident(altr_tblseq))
				  WHEN altr_step = 'ADD_TBL' THEN
					format('{$emajlang['emajalteredaddtbl']}', quote_ident(altr_schema), quote_ident(altr_tblseq), quote_literal(altr_group))
				  WHEN altr_step = 'ADD_SEQ' THEN
					format('{$emajlang['emajalteredaddseq']}', quote_ident(altr_schema), quote_ident(altr_tblseq), quote_literal(altr_group))
                  END AS altr_action, 
				CASE WHEN altr_step IN ('ADD_TBL', 'ADD_SEQ', 'REMOVE_TBL', 'REMOVE_SEQ', 'REPAIR_TBL', 'REPAIR_SEQ',
										'CHANGE_TBL_LOG_SCHEMA', 'CHANGE_TBL_NAMES_PREFIX', 'CHANGE_TBL_LOG_DATA_TSP',
										'CHANGE_TBL_LOG_INDEX_TSP', 'CHANGE_REL_PRIORITY')
					THEN false ELSE true END AS altr_auto_rolled_back
				  FROM emaj.emaj_alter_plan, emaj.emaj_time_stamp
				  WHERE time_id = altr_time_id
					AND altr_group = ANY ({$groupsArray})
					AND altr_time_id >
						(SELECT mark_time_id FROM emaj.emaj_mark WHERE mark_group = '{$firstGroup}' AND mark_name = '{$mark}')
					AND altr_rlbk_id IS NULL
					AND altr_step IN ('ADD_TBL', 'ADD_SEQ', 'REMOVE_TBL', 'REMOVE_SEQ', 'REPAIR_TBL', 'REPAIR_SEQ',
									  'CHANGE_TBL_LOG_SCHEMA', 'CHANGE_TBL_NAMES_PREFIX', 'CHANGE_TBL_LOG_DATA_TSP',
									  'CHANGE_TBL_LOG_INDEX_TSP', 'CHANGE_REL_PRIORITY')
				  ORDER BY time_tx_timestamp, altr_schema, altr_tblseq, altr_step";
		return $data->selectSet($sql);
	}

	/**
	 * Rollbacks a group to a mark (the old version, to be used with emaj prior 2.1)
	 * It returns the number of tables and sequences processed.
	 */
	function oldRollbackGroup($group,$mark,$isLogged) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		if ($isLogged){
			$sql = "SELECTemaj.emaj_logged_rollback_group('{$group}','{$mark}') AS nbtblseq";
		} else {
			$sql = "SELECT emaj.emaj_rollback_group('{$group}','{$mark}') AS nbtblseq";
		}

		return $data->execute($sql);
	}

	/**
	 * Rollbacks a group to a mark
	 * It returns a set of messages
	 */
	function rollbackGroup($group,$mark,$isLogged) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		if ($isLogged){
			$sql = "SELECT * FROM emaj.emaj_logged_rollback_group('{$group}','{$mark}',true)";
		} else {
			$sql = "SELECT * FROM emaj.emaj_rollback_group('{$group}','{$mark}',true)";
		}

		return $data->selectSet($sql);
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

		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		// Initialize the rollback operation and get its rollback id
		$isL = $isLogged ? 'true' : 'false';
		$isM = $isMulti ? 'true' : 'false';
		if ($this->getNumEmajVersion() >= 20100){	// version >= 2.1.0
			$sql1 = "SELECT emaj._rlbk_init({$groupsArray}, '{$mark}', {$isL}, 1, {$isM}, true) as rlbk_id";
		} else {
			$sql1 = "SELECT emaj._rlbk_init({$groupsArray}, '{$mark}', {$isL}, 1, {$isM}) as rlbk_id";
		}
		$rlbkId = $data->selectField($sql1,'rlbk_id');

		// Build the psql report file name, the SQL command and submit the rollback execution asynchronously
		$sql2 = "SELECT emaj._rlbk_async({$rlbkId},{$isM})";
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
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

// Attention, this statement needs postgres 8.4+, because of array_agg() function use
		if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
			$sql = "SELECT t.mark_name, t.mark_datetime, t.mark_is_rlbk_protected 
					FROM (SELECT mark_name, time_tx_timestamp as mark_datetime, mark_is_rlbk_protected,
								 array_agg (mark_group) AS groups 
						  FROM emaj.emaj_mark,emaj.emaj_group,
							   emaj.emaj_time_stamp
						  WHERE mark_group = group_name AND time_id = mark_time_id 
							AND NOT mark_is_deleted AND group_is_rollbackable GROUP BY 1,2,3) AS t 
					WHERE t.groups @> $groupsArray
					ORDER BY t.mark_datetime DESC";
		}else{
			$sql = "SELECT t.mark_name, t.mark_datetime, t.mark_is_rlbk_protected 
					FROM (SELECT mark_name, mark_datetime, mark_is_rlbk_protected, array_agg (mark_group) AS groups 
							FROM emaj.emaj_mark,emaj.emaj_group 
							WHERE mark_group = group_name AND NOT mark_is_deleted
							  AND group_is_rollbackable GROUP BY 1,2,3) AS t 
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
					  FROM emaj.emaj_mark , emaj.emaj_time_stamp
					  WHERE time_id = mark_time_id
						AND mark_group IN ($groups) AND mark_is_rlbk_protected";
		}else{
			$sql = "SELECT max(mark_datetime) AS youngest_mark_datetime 
					  FROM emaj.emaj_mark 
					  WHERE mark_group IN ($groups) AND mark_is_rlbk_protected";
		}

		return $data->selectField($sql,'youngest_mark_datetime');
	}

	/**
	 * Get the list of protected groups from a groups list.
	 */
	function getProtectedGroups($groups) {

		global $data;

		$data->clean($groups);
		$groups="'".str_replace(', ',"','",$groups)."'";

// Attention, this statement needs postgres 8.4+, because of array_agg() function use
		$sql = "SELECT string_agg(group_name, ', ') AS groups 
				  FROM emaj.emaj_group 
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
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);

		$nbGroups = substr_count($groupsArray,',') + 1;

		// check the mark is active (i.e. not deleted)
		$sql = "SELECT CASE WHEN 
				(SELECT COUNT(*) FROM emaj.emaj_mark, emaj.emaj_group 
					WHERE mark_group = group_name 
						AND mark_group = ANY ({$groupsArray}) AND group_is_rollbackable AND mark_name = '{$mark}' 
						AND NOT mark_is_deleted
				) = {$nbGroups} THEN 1 ELSE 0 END AS result";

		$result = $data->selectField($sql,'result');

		if ($result == 1) {
			// the mark is active, so now check there is no intermediate protected mark
			if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
				$sql = "SELECT CASE WHEN 
						(SELECT count(*) FROM emaj.emaj_mark 
						  WHERE mark_group = ANY ({$groupsArray}) AND mark_time_id > 
							(SELECT mark_time_id FROM emaj.emaj_mark 
							 WHERE mark_group = ANY({$groupsArray}) AND mark_name = '{$mark}' LIMIT 1
						    ) AND mark_is_rlbk_protected
						) = 0 THEN 1 ELSE 0 END AS result";
			} else{
				$sql = "SELECT CASE WHEN 
						(SELECT count(*) FROM emaj.emaj_mark 
						  WHERE mark_group = ANY ({$groupsArray}) AND mark_id > 
							(SELECT mark_id FROM emaj.emaj_mark 
							 WHERE mark_group = ANY({$groupsArray}) AND mark_name = '{$mark}' LIMIT 1
						    ) AND mark_is_rlbk_protected
						) = 0 THEN 1 ELSE 0 END AS result";
			}
			$result = $data->selectField($sql,'result');
		}

		return $result;
	}

	/**
	 * Rollbacks a groups array to a mark (the old version, to be used with emaj prior 2.1)
	 * It returns the number of tables and sequences processed.
	 */
	function oldRollbackGroups($groups,$mark,$isLogged) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);

		if ($isLogged){
			$sql = "SELECT emaj.emaj_logged_rollback_groups({$groupsArray},'{$mark}') AS nbtblseq";
		}else{
			$sql = "SELECTemaj.emaj_rollback_groups({$groupsArray},'{$mark}') AS nbtblseq";
		}

		return $data->execute($sql);
	}

	/**
	 * Rollbacks a groups array to a mark
	 * It returns a set of messages
	 */
	function rollbackGroups($groups,$mark,$isLogged) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);

		if ($isLogged){
			$sql = "SELECT * FROM emaj.emaj_logged_rollback_groups({$groupsArray},'{$mark}',true)";
		}else{
			$sql = "SELECT * FROM emaj.emaj_rollback_groups({$groupsArray},'{$mark}',true)";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Gets the global rollback statistics for a group and a mark (i.e. total number of log rows to rollback)
	 */
	function getGlobalRlbkStatGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT coalesce(sum(stat_rows),0) as sumrows, count(*) as nbtables 
					FROM emaj.emaj_log_stat_group('{$group}','{$mark}',NULL)
					WHERE stat_rows > 0";

		return $data->selectSet($sql);
	}

	/**
	 * Estimates the rollback duration for one or several groups and a mark
	 */
	function estimateRollbackGroups($groups, $mark, $rollbackType) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);
		$bool = ($rollbackType == 'logged') ? 'TRUE' : 'FALSE';

		$sql = "SELECT to_char(emaj.emaj_estimate_rollback_groups({$groupsArray}, '{$mark}', {$bool})
								+ '1 second'::interval,'YYYY/MM/DD HH24:MI:SS') as duration";

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
						  FROM emaj.emaj_rlbk, emaj.emaj_time_stamp tr, emaj.emaj_time_stamp tm 
						  WHERE tr.time_id = rlbk_time_id AND tm.time_id = rlbk_mark_time_id 
							AND rlbk_status IN ('COMPLETED','COMMITTED','ABORTED')";
		} else {
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

		if ($this->getNumEmajVersion() >= 30000){	// version >= 3.0.0
			$sql = "SELECT cons_group, cons_target_rlbk_mark_name, tt.time_tx_timestamp AS cons_target_rlbk_mark_datetime, 
						cons_end_rlbk_mark_name, rt.time_tx_timestamp AS cons_end_rlbk_mark_datetime, cons_rows, cons_marks
					FROM emaj.emaj_get_consolidable_rollbacks(),
						emaj.emaj_time_stamp tt, emaj.emaj_time_stamp rt
					WHERE tt.time_id = cons_target_rlbk_mark_time_id
					  AND rt.time_id = cons_end_rlbk_mark_time_id
					  AND (cons_rows > 0 OR cons_marks > 0)
					ORDER BY cons_end_rlbk_mark_time_id, cons_group";
		} else {
			$sql = "SELECT cons_group, cons_target_rlbk_mark_name, tt.time_tx_timestamp AS cons_target_rlbk_mark_datetime, 
						cons_end_rlbk_mark_name, rt.time_tx_timestamp AS cons_end_rlbk_mark_datetime, cons_rows, cons_marks
					FROM emaj.emaj_get_consolidable_rollbacks(),
						emaj.emaj_mark tm, emaj.emaj_time_stamp tt, 
						emaj.emaj_mark rm, emaj.emaj_time_stamp rt
					WHERE tt.time_id = tm.mark_time_id AND tm.mark_id = cons_target_rlbk_mark_id
					  AND rt.time_id = rm.mark_time_id AND rm.mark_id = cons_end_rlbk_mark_id
					  AND (cons_rows > 0 OR cons_marks > 0)
					ORDER BY cons_end_rlbk_mark_id";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Consolidates a rollback operation
	 */
	function consolidateRollback($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT emaj.emaj_consolidate_rollback_group('{$group}','{$mark}') AS nbtblseq";

		return $data->execute($sql);
	}

	/**
	 * Gets the properties of a single rollback operation. It returns 1 row.
	 */
	function getOneRlbk($rlbkId) {
		global $data;

		$data->clean($rlbkId);

// first cleanup recently completed rollback operation status
		$sql = "SELECT emaj.emaj_cleanup_rollback_state()";
		$data->execute($sql);

// get the emaj_rlbk data
		$sql = "SELECT rlbk_id, array_to_string(rlbk_groups,',') as rlbk_groups_list, rlbk_status,
					rlbk_start_datetime, rlbk_end_datetime,
					to_char(rlbk_end_datetime - rlbk_start_datetime,'HH24:MI:SS.MSFM') as rlbk_duration, 
					rlbk_mark, rlbk_mark_datetime, rlbk_is_logged, rlbk_nb_session, rlbk_eff_nb_table,
					rlbk_nb_sequence ";
		if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
			$sql .= "FROM (SELECT *, tr.time_tx_timestamp as rlbk_start_datetime, tm.time_tx_timestamp as rlbk_mark_datetime
							FROM emaj.emaj_rlbk, emaj.emaj_time_stamp tr, emaj.emaj_time_stamp tm 
							WHERE tr.time_id = rlbk_time_id AND tm.time_id = rlbk_mark_time_id 
							  AND rlbk_id = {$rlbkId}
						  ) AS t";
		} else {
			$sql .= "FROM (SELECT *
							FROM emaj.emaj_rlbk 
							WHERE rlbk_id = {$rlbkId}
						  ) AS t";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Gets a single in progress rollback operation. It returns 0 or 1 row.
	 */
	function getOneInProgressRlbk($rlbkId) {
		global $data;

		$data->clean($rlbkId);

		$sql = "SELECT rlbk_status, rlbk_start_datetime,
					   to_char(rlbk_elapse,'HH24:MI:SS.MSFM') AS rlbk_current_elapse, rlbk_remaining,
					   rlbk_completion_pct
				FROM emaj.emaj_rollback_activity()
				WHERE rlbk_id = {$rlbkId}";

		return $data->selectSet($sql);
	}

	/**
	 * Gets the report messages for a single completed rollback operation.
	 */
	function getRlbkReportMsg($rlbkId) {
		global $data;

		$data->clean($rlbkId);

		$sql = "SELECT substring(msg from '(.*?):') AS rlbk_severity,
					   substring(msg from ': (.*)') AS rlbk_message
				FROM (SELECT unnest(rlbk_messages) AS msg
						FROM emaj.emaj_rlbk
						WHERE rlbk_id = {$rlbkId}
					 ) AS t";

		return $data->selectSet($sql);
	}

	/**
	 * Gets sessions data for a rollback operation.
	 */
	function getRlbkSessions($rlbkId) {
		global $data;

		$data->clean($rlbkId);

		$sql = "SELECT rlbs_session, rlbs_txid,
				rlbs_start_datetime::TIME,
				rlbs_end_datetime::TIME,
				to_char(rlbs_end_datetime - rlbs_start_datetime,'HH24:MI:SS.MSFM') AS rlbs_duration
				FROM emaj.emaj_rlbk_session
				WHERE rlbs_rlbk_id = {$rlbkId}
				ORDER BY rlbs_session";

		return $data->selectSet($sql);
	}

	/**
	 * Gets planning data for a rollback operation.
	 */
	function getRlbkSteps($rlbkId) {
		global $data, $lang;

		$data->clean($rlbkId);

		$sql = "SELECT row_number() over () AS rlbp_rank,
					   rlbp_schema || '.' || rlbp_table AS rlbk_schema_table,
					   CASE 
						 WHEN rlbp_step = 'DIS_APP_TRG' THEN
							format('{$lang['emajrlbkdisapptrg']}', quote_ident(rlbp_object))
						 WHEN rlbp_step = 'DIS_LOG_TRG' THEN
							'{$lang['emajrlbkdislogtrg']}'
						 WHEN rlbp_step = 'DROP_FK' THEN
							format('{$lang['emajrlbkdropfk']}',	quote_ident(rlbp_object))
						 WHEN rlbp_step = 'SET_FK_DEF' THEN
							format('{$lang['emajrlbksetfkdef']}', quote_ident(rlbp_object))
						 WHEN rlbp_step = 'RLBK_TABLE' THEN
							'{$lang['emajrlbkrlbktable']}'
						 WHEN rlbp_step = 'DELETE_LOG' THEN
							'{$lang['emajrlbkdeletelog']}'
						 WHEN rlbp_step = 'SET_FK_IMM' THEN
							format('{$lang['emajrlbksetfkimm']}', quote_ident(rlbp_object))
						 WHEN rlbp_step = 'ADD_FK' THEN
							format('{$lang['emajrlbkaddfk']}', quote_ident(rlbp_object))
						 WHEN rlbp_step = 'ENA_APP_TRG' THEN
							format('{$lang['emajrlbkenaapptrg']}', quote_ident(rlbp_object))
						 WHEN rlbp_step = 'ENA_LOG_TRG' THEN
							'{$lang['emajrlbkenalogtrg']}'
						 ELSE '?'
					   END AS rlbp_action,
					   rlbp_batch_number, rlbp_session,
					   rlbp_estimated_quantity, rlbp_estimated_duration,
					   CASE WHEN rlbp_estimate_method = 1 THEN 'STAT+'
							WHEN rlbp_estimate_method = 2 THEN 'STAT'
							WHEN rlbp_estimate_method = 3 THEN 'PARAM'
					   END AS rlbp_estimate_method,
					   rlbp_start_datetime::TIME, rlbp_quantity, rlbp_duration
				FROM emaj.emaj_rlbk_plan,
					(VALUES ('DIS_APP_TRG',1),('DIS_LOG_TRG',2),('DROP_FK',3),('SET_FK_DEF',4),
							('RLBK_TABLE',5),('DELETE_LOG',6),('SET_FK_IMM',7),('ADD_FK',8),
							('ENA_APP_TRG',9),('ENA_LOG_TRG',10)) AS step(step_name, step_order)
				WHERE rlbp_step::TEXT = step.step_name
				  AND rlbp_rlbk_id = {$rlbkId}
				  AND rlbp_step NOT IN ('LOCK_TABLE','CTRL-DBLINK','CTRL+DBLINK')
				ORDER BY rlbp_start_datetime, rlbp_batch_number, step_order, rlbp_table, rlbp_object";

		return $data->selectSet($sql);
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

		if ($this->getNumEmajVersion() >= 20300){	// version >= 2.3.0
			$sql = "CREATE TEMP TABLE tmp_stat AS
					SELECT stat_group, stat_schema, stat_table,
						   stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime,
						   stat_rows, 
						   'select * from ' || quote_ident(stat_log_schema) || '.' || quote_ident(stat_log_table) ||
						   ' where emaj_gid > ' || stat_first_mark_gid::text ||
						   coalesce (' and emaj_gid <= ' || stat_last_mark_gid::text, '') ||
						   ' order by emaj_gid' as sql_text
						FROM emaj._log_stat_groups('{\"{$group}\"}',false,'{$firstMark}','{$lastMark}')
						WHERE stat_rows > 0";
		} else {									// oldest emaj versions
			if ($lastMark == ''){
				$sql = "CREATE TEMP TABLE tmp_stat AS
						SELECT stat_group, stat_schema, stat_table, ";
				if ($this->getNumEmajVersion() >= 20203){	// version >= 2.2.3
					$sql .= "stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime, ";
				}
				$sql .= "stat_rows, 
						'select * from ' || quote_ident(rel_log_schema)";
				if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
					$sql .= " || '.' || quote_ident(rel_log_table) || 
								' where emaj_gid > ' || strttime.time_last_emaj_gid ||
								' order by emaj_gid' as sql_text
							FROM emaj.emaj_log_stat_group('{$group}','{$firstMark}',NULL), 
								emaj.emaj_mark strtmark, emaj.emaj_time_stamp strttime,
								emaj.emaj_relation
							WHERE stat_rows > 0 
								AND strtmark.mark_group = '{$group}' 
								AND strtmark.mark_name = '{$firstMark}' 
								AND rel_schema = stat_schema AND rel_tblseq = stat_table
								AND strtmark.mark_time_id = strttime.time_id";
				}else{
					$sql .= " || '.' || quote_ident(stat_schema || '_' || stat_table || '_log') || 
								' where emaj_gid > ' || strtmark.mark_global_seq ||
								' order by emaj_gid' as sql_text
							FROM emaj.emaj_log_stat_group('{$group}','{$firstMark}',NULL), 
								emaj.emaj_mark strtmark, 
								emaj.emaj_relation
							WHERE stat_rows > 0 
								AND strtmark.mark_group = '{$group}' 
								AND strtmark.mark_name = '{$firstMark}' 
								AND rel_schema = stat_schema AND rel_tblseq = stat_table";
				}
			}else{
				$sql = "CREATE TEMP TABLE tmp_stat AS
						SELECT stat_group, stat_schema, stat_table, ";
				if ($this->getNumEmajVersion() >= 20203){	// version >= 2.2.3
					$sql .= "stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime, ";
				}
				$sql .= "stat_rows, 
						'select * from ' || quote_ident(rel_log_schema)";
				if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
					$sql .= " || '.' || quote_ident(stat_schema || '_' || stat_table || '_log') ||
								' where emaj_gid > ' || strttime.time_last_emaj_gid ||
								' and emaj_gid <= ' || stoptime.time_last_emaj_gid ||
								' order by emaj_gid' as sql_text
							FROM emaj.emaj_log_stat_group('{$group}','{$firstMark}','{$lastMark}'), 
								emaj.emaj_mark strtmark, emaj.emaj_time_stamp strttime, 
								emaj.emaj_mark stopmark, emaj.emaj_time_stamp stoptime, 
								emaj.emaj_relation
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
							FROM emaj.emaj_log_stat_group('{$group}','{$firstMark}','{$lastMark}'), 
								emaj.emaj_mark strtmark , emaj.emaj_mark stopmark, 
								emaj.emaj_relation
							WHERE stat_rows > 0 
								AND strtmark.mark_group = '{$group}' 
								AND strtmark.mark_name = '{$firstMark}' 
								AND stopmark.mark_group = '{$group}' 
								AND stopmark.mark_name = '{$lastMark}' 
								AND rel_schema = stat_schema AND rel_tblseq = stat_table";
				}
			}
		}

		$data->execute($sql);

		$sql = "SELECT stat_group, stat_schema, stat_table, ";
		if ($this->getNumEmajVersion() >= 20300){	// version >= 2.3.0
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

		$sql = "SELECT coalesce(sum(stat_rows),0) AS sum_rows, count(distinct stat_schema || '.' || stat_table) AS nb_tables 
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

		if ($this->getNumEmajVersion() >= 20300){	// version >= 2.3.0
			$sql = "CREATE TEMP TABLE tmp_stat AS
					SELECT stat_group, stat_schema, stat_table,
						   stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime,
						   stat_role, stat_verb, stat_rows,
						   'select * from ' || quote_ident(stat_log_schema) || '.' || quote_ident(stat_log_table) ||
						   ' where emaj_gid > ' || stat_first_mark_gid::text ||
						   coalesce (' and emaj_gid <= ' || stat_last_mark_gid::text, '') ||
						   ' and emaj_verb = ' || quote_literal(substring(stat_verb from 1 for 3)) ||
						   ' order by emaj_gid' as sql_text
						FROM emaj._detailed_log_stat_groups('{\"{$group}\"}',false,'{$firstMark}','{$lastMark}')
						WHERE stat_rows > 0";
		} else {									// oldest emaj versions
			if ($lastMark==''){
				$sql = "CREATE TEMP TABLE tmp_stat AS
						SELECT stat_group, stat_schema, stat_table, ";
				if ($this->getNumEmajVersion() >= 20203){	// version >= 2.2.3
					$sql .= "stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime, ";
				}
				$sql .= "stat_role, stat_verb, stat_rows, 
						'select * from ' || quote_ident(rel_log_schema)";
				if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
					$sql .=    " || '.' || quote_ident(stat_schema || '_' || stat_table || '_log') || 
								' where emaj_gid > ' || strttime.time_last_emaj_gid ||
								' and emaj_verb = ' || quote_literal(substring(stat_verb from 1 for 3)) || 
								' order by emaj_gid' as sql_text
							FROM emaj.emaj_detailed_log_stat_group('{$group}','{$firstMark}',NULL), 
								emaj.emaj_mark strtmark, emaj.emaj_time_stamp strttime, 
								emaj.emaj_relation
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
							FROM emaj.emaj_detailed_log_stat_group('{$group}','{$firstMark}',NULL), 
								emaj.emaj_mark strtmark, emaj.emaj_relation
							WHERE stat_rows > 0 
								AND strtmark.mark_group = '{$group}' 
								AND strtmark.mark_name = '{$firstMark}' 
								AND rel_schema = stat_schema AND rel_tblseq = stat_table";
				}
			}else{
				$sql = "CREATE TEMP TABLE tmp_stat AS
						SELECT stat_group, stat_schema, stat_table, ";
				if ($this->getNumEmajVersion() >= 20203){	// version >= 2.2.3
					$sql .= "stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime, ";
				}
				$sql .= "stat_role, stat_verb, stat_rows, 
						'select * from ' || quote_ident(rel_log_schema)";
				if ($this->getNumEmajVersion() >= 20000){	// version >= 2.0.0
					$sql .=    " || '.' || quote_ident(stat_schema || '_' || stat_table || '_log') || 
								' where emaj_gid > ' || strttime.time_last_emaj_gid ||
								' and emaj_gid <= ' || stoptime.time_last_emaj_gid ||
								' and emaj_verb = ' || quote_literal(substring(stat_verb from 1 for 3)) || 
								' order by emaj_gid' as sql_text
							FROM emaj.emaj_detailed_log_stat_group('{$group}','{$firstMark}','{$lastMark}'), 
								emaj.emaj_mark strtmark, emaj.emaj_time_stamp strttime, 
								emaj.emaj_mark stopmark, emaj.emaj_time_stamp stoptime, 
								emaj.emaj_relation
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
							FROM emaj.emaj_detailed_log_stat_group('{$group}','{$firstMark}','{$lastMark}'), 
								emaj.emaj_mark strtmark , emaj.emaj_mark stopmark, 
								emaj.emaj_relation
							WHERE stat_rows > 0 
								AND strtmark.mark_group = '{$group}' 
								AND strtmark.mark_name = '{$firstMark}' 
								AND stopmark.mark_group = '{$group}' 
								AND stopmark.mark_name = '{$lastMark}' 
								AND rel_schema = stat_schema AND rel_tblseq = stat_table";
				}
			}
		}
		$data->execute($sql);

		$sql = "SELECT stat_group, stat_schema, stat_table, ";
		if ($this->getNumEmajVersion() >= 20300){	// version >= 2.3.0
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

	/**
	 * Gets the 'E-Maj type' of a table or sequence
	 * returns 
     *   'L' when the table or sequence is a Log object, or 
     *   'E' if it is an internal E-maj object, or 
     *   '' in other cases
	 */
	function getEmajTypeTblSeq($schema, $tblseq) {
		global $data;

		$data->clean($schema);
		$data->clean($tblseq);

		$sql = "SELECT CASE WHEN EXISTS (
									SELECT 1 FROM emaj.emaj_relation
										WHERE rel_log_schema = '{$schema}' AND (rel_log_table = '{$tblseq}' OR rel_log_sequence = '{$tblseq}')
									) THEN 'L'
							WHEN '{$schema}' = 'emaj' THEN 'E'
							ELSE '' END AS emaj_type";

		return $data->selectField($sql,'emaj_type');
	}

	/**
	 * Gets the application triggers on all application tables known in the database.
	 */
	function getAppTriggers() {
		global $data;

		$sql = "SELECT rn.nspname, relname,
					substring(pg_catalog.pg_get_triggerdef(t.oid) FROM 'ON (.*?) FOR ') as tgtable,
					t.tgname,
					substring(pg_catalog.pg_get_triggerdef(t.oid) FROM 'FOR EACH (\S+) EXECUTE') as tglevel,
					substring(pg_catalog.pg_get_triggerdef(t.oid) FROM 'CREATE TRIGGER \S+ (.*?) ON ') as tgevent,
                       quote_ident(pn.nspname) || '.' || quote_ident(proname) as tgfnct,
					CASE WHEN t.tgenabled = 'D' THEN 'Disabled' 
						 WHEN t.tgenabled = 'O' THEN 'Enabled' 
						 WHEN t.tgenabled = 'R' THEN 'Enabled on Replica'
						 WHEN t.tgenabled = 'A' THEN 'Enabled Always'
							END AS tgstate,
				";
		if ($this->isEnabled() && $this->isAccessible()) {
			if ($this->getNumEmajVersion() >= 30100) {
				$sql .= "NOT EXISTS (
							SELECT 1 FROM emaj.emaj_ignored_app_trigger
								WHERE trg_schema = rn.nspname AND trg_table = relname AND trg_name = tgname)
						AS tgisautodisable,";
			}
		}
		$sql .= "	CASE WHEN pg_catalog.pg_get_triggerdef(t.oid) ~ ' BEFORE .* EACH STATEMENT ' THEN 1
						WHEN pg_catalog.pg_get_triggerdef(t.oid) ~ ' BEFORE .* EACH ROW ' THEN 2
						WHEN pg_catalog.pg_get_triggerdef(t.oid) ~ ' AFTER .* EACH ROW ' THEN 3
						WHEN pg_catalog.pg_get_triggerdef(t.oid) ~ ' AFTER .* EACH STATEMENT ' THEN 4 END as tgorder
				FROM pg_catalog.pg_trigger t, pg_catalog.pg_class, pg_catalog.pg_namespace rn,
					 pg_catalog.pg_proc, pg_catalog.pg_namespace pn
				WHERE tgrelid = pg_class.oid AND relnamespace = rn.oid
				  AND tgfoid = pg_proc.oid AND pronamespace = pn.oid
				  AND (tgconstraint = 0 OR NOT EXISTS
						(SELECT 1 FROM pg_catalog.pg_depend d
							JOIN pg_catalog.pg_constraint c	ON (d.refclassid = c.tableoid AND d.refobjid = c.oid)
						WHERE d.classid = t.tableoid AND d.objid = t.oid AND d.deptype = 'i' AND c.contype = 'f'))
				  AND rn.nspname <> 'emaj'
				  AND tgname NOT IN ('emaj_trunc_trg', 'emaj_log_trg')
				ORDER BY rn.nspname, relname, tgorder, tgname";

		return $data->selectSet($sql);
	}

	/**
	 * Gets the orphan application triggers in the emaj_ignored_app_trigger table, i.e. not currently created.
	 */
	function getOrphanAppTriggers() {
		global $data;

		$sql = "	SELECT trg_schema, trg_table, trg_name
						FROM emaj.emaj_ignored_app_trigger
				EXCEPT
					SELECT nspname, relname, tgname
						FROM pg_catalog.pg_trigger t, pg_catalog.pg_class, pg_catalog.pg_namespace
						WHERE relnamespace = pg_namespace.oid AND tgrelid = pg_class.oid
				ORDER BY 1,2,3";

		return $data->selectSet($sql);
	}

	/**
	 * Gets the tables groups that owned or currently owns a given table or sequence.
	 * The function is only called when emaj version >= 2.2.0
	 */
	function getTableGroupsTblSeq($schema, $tblseq) {
		global $data;

		$data->clean($schema);
		$data->clean($tblseq);

		if ($this->getNumEmajVersion() >= 30100) {
			// The rel_group is suffixed with a ###LINK### when a link to the group definition has to be added at page display
			$sql = "	SELECT rel_group || CASE WHEN upper_inf(rel_time_range) THEN '###LINK###' ELSE '' END AS rel_group,
							   date_trunc('SECOND', start.time_tx_timestamp)::TEXT AS start_datetime,
							   coalesce(date_trunc('SECOND',stop.time_tx_timestamp)::TEXT,'') AS stop_datetime
						FROM emaj.emaj_relation
							LEFT OUTER JOIN emaj.emaj_time_stamp start ON (lower(rel_time_range) = start.time_id)
							LEFT OUTER JOIN emaj.emaj_time_stamp stop ON (upper(rel_time_range) = stop.time_id)
						WHERE rel_schema = '{$schema}' AND rel_tblseq = '{$tblseq}'
					UNION ALL
						SELECT relh_group,
							   date_trunc('SECOND', start.time_tx_timestamp)::TEXT AS start_datetime,
							   coalesce(date_trunc('SECOND',stop.time_tx_timestamp)::TEXT,'') AS stop_datetime
						FROM emaj.emaj_rel_hist
							LEFT OUTER JOIN emaj.emaj_time_stamp start ON (lower(relh_time_range) = start.time_id)
							LEFT OUTER JOIN emaj.emaj_time_stamp stop ON (upper(relh_time_range) = stop.time_id)
						WHERE relh_schema = '{$schema}' AND relh_tblseq = '{$tblseq}'
					ORDER BY start_datetime DESC";
		} else {
			// The rel_group is suffixed with a ###LINK### when a link to the group definition has to be added at page display
			$sql = "SELECT rel_group || CASE WHEN upper_inf(rel_time_range) THEN '###LINK###' ELSE '' END AS rel_group,
							date_trunc('SECOND', start.time_tx_timestamp)::TEXT AS start_datetime,
							coalesce(date_trunc('SECOND',stop.time_tx_timestamp)::TEXT,'') AS stop_datetime
					FROM emaj.emaj_relation
						LEFT OUTER JOIN emaj.emaj_time_stamp start ON (lower(rel_time_range) = start.time_id)
						LEFT OUTER JOIN emaj.emaj_time_stamp stop ON (upper(rel_time_range) = stop.time_id)
					WHERE rel_schema = '{$schema}' AND rel_tblseq = '{$tblseq}'
					ORDER BY rel_time_range DESC";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Gets the list of existing triggers on a table
	 */
	function getTriggersTable($schema, $table) {
		global $data;

		$data->clean($schema);
		$data->clean($table);

		$sql = "SELECT row_number(*) OVER (ORDER BY tgorder, tgname) as tgrank, * FROM (
					SELECT
						substring(pg_catalog.pg_get_triggerdef(t.oid) FROM 'ON (.*?) FOR ') as tgtable,
						t.tgname,
						substring(pg_catalog.pg_get_triggerdef(t.oid) FROM 'FOR EACH (\S+) EXECUTE') as tglevel,
						substring(pg_catalog.pg_get_triggerdef(t.oid) FROM 'CREATE TRIGGER \S+ (.*?) ON ') as tgevent,
                        quote_ident(pn.nspname) || '.' || quote_ident(proname) as tgfnct,
						CASE WHEN t.tgenabled = 'D' THEN 'Disabled' 
							 WHEN t.tgenabled = 'O' THEN 'Enabled' 
							 WHEN t.tgenabled = 'R' THEN 'Enabled on Replica'
							 WHEN t.tgenabled = 'A' THEN 'Enabled Always'
								END AS tgstate,
						CASE WHEN tgname IN ('emaj_trunc_trg', 'emaj_log_trg') THEN true ELSE false END as tgisemaj,
				";

		if ($this->isEnabled() && $this->isAccessible()) {
			if ($this->getNumEmajVersion() >= 30100) {
				$sql .= " 	CASE WHEN tgname IN ('emaj_trunc_trg', 'emaj_log_trg') THEN NULL 
								ELSE NOT EXISTS (
									SELECT 1 FROM emaj.emaj_ignored_app_trigger
										WHERE trg_schema = '{$schema}' AND trg_table = '{$table}' AND trg_name = tgname)
								END as tgisautodisable,";
			}
		}

		$sql .= "		CASE WHEN pg_catalog.pg_get_triggerdef(t.oid) ~ ' BEFORE .* EACH STATEMENT ' THEN 1
							WHEN pg_catalog.pg_get_triggerdef(t.oid) ~ ' BEFORE .* EACH ROW ' THEN 2
							WHEN pg_catalog.pg_get_triggerdef(t.oid) ~ ' AFTER .* EACH ROW ' THEN 3
							WHEN pg_catalog.pg_get_triggerdef(t.oid) ~ ' AFTER .* EACH STATEMENT ' THEN 4 END as tgorder
					FROM pg_catalog.pg_trigger t, pg_catalog.pg_class, pg_catalog.pg_namespace rn,
						 pg_catalog.pg_proc, pg_catalog.pg_namespace pn
					WHERE tgrelid = pg_class.oid AND relnamespace = rn.oid
					  AND tgfoid = pg_proc.oid AND pronamespace = pn.oid
					  AND relname='{$table}' AND rn.nspname='{$schema}'
					  AND (tgconstraint = 0 OR NOT EXISTS
							(SELECT 1 FROM pg_catalog.pg_depend d
								JOIN pg_catalog.pg_constraint c	ON (d.refclassid = c.tableoid AND d.refobjid = c.oid)
							WHERE d.classid = t.tableoid AND d.objid = t.oid AND d.deptype = 'i' AND c.contype = 'f'))
					) as t
				ORDER BY tgorder, tgname";

		return $data->selectSet($sql);
	}

	/**
	 * Handle the list of triggers that must not be automatically disabled at rollback time: add or remove one
	 * It usually returns 1 (unless the list has been just modified by someone else)
	 */
	function ignoreAppTrigger($action, $schema, $table, $trigger) {
		global $data;

		$data->clean($action);
		$data->clean($schema);
		$data->clean($table);
		$data->clean($trigger);

		$sql = "SELECT emaj.emaj_ignore_app_trigger('{$action}', '{$schema}','{$table}','{$trigger}') AS nbtriggers";

		return $data->selectField($sql,'nbtriggers');
	}

}
?>
