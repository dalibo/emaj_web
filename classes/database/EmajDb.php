<?php

/**
 * A class that implements the database access for Emaj_web.
 * The covered E-Maj versions range is described into libraries/versions.inc.php.
 */

class EmajDb {

	/**
	 * Cache of static data.
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
	 * Constants.
	 */
	// Output format for timestamptz values. This format will be interpreted by the GUI layer for smart displays.
	private $tsFormat = 'YYYY/MM/DD HH24:MI:SS.US OF';
	// Output format for interval values. This format will be interpreted by the GUI layer for smart displays.
	private $intervalFormat = 'DD HH24:MI:SS.US';

	/**
	 * Constructor.
	 */
	function __construct() {
	}

	/**
	 * Determine whether Emaj is installed in the current database, by looking for a schema named 'emaj'.
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
				WHERE nspname = 'emaj'";
		$rs = $data->selectSet($sql);
		if ($rs->recordCount() == 1) {
			$schema = $rs->fields['schema'];
			$this->emaj_schema = $schema;
			$this->enabled = true;
		}
		return $this->enabled;
	}

	/**
	 * Determine whether the current user is granted to access emaj schema.
	 * @return True if enabled Emaj is accessible by the current user, false otherwise.
	 */
	function isAccessible() {
		// Access cache
		if ($this->accessible !== null) return $this->accessible;

		// Otherwise compute
		$this->accessible = $this->enabled&&($this->isEmaj_Adm()||$this->isEmaj_Viewer());
		return $this->accessible;
	}

	/**
	 * Determine whether the current user is granted the 'emaj_adm' role.
	 * @return True if Emaj is accessible by the current user as E-maj administrator, false otherwise.
	 */
	function isEmaj_Adm() {
		// Access cache
		if ($this->emaj_adm !== null) return $this->emaj_adm;

		global $data, $misc;

		$this->emaj_adm = false;
		$server_info = $misc->getServerInfo();
		// If the current role is superuser, he is considered as E-maj administration.
		if ($data->isSuperUser($server_info['username'])) {
			$this->emaj_adm = true;
		} else {
		// Otherwise, is the current role member of emaj_adm role ?
			$sql = "SELECT CASE WHEN pg_catalog.pg_has_role('emaj_adm','USAGE') THEN 1 ELSE 0 END AS is_emaj_adm";
			$this->emaj_adm = $data->selectField($sql,'is_emaj_adm');
		}
		return $this->emaj_adm;
	}

	/**
	 * Determine whether the current user is granted the 'emaj_viewer' role.
	 * @return True if Emaj is accessible by the current user as E-maj viewer, false otherwise.
	 * Note that an 'emaj_adm' role is also considered as 'emaj_viewer'.
	 */
	function isEmaj_Viewer() {
		// Access cache.
		if ($this->emaj_viewer !== null) return $this->emaj_viewer;

		global $data, $misc;

		$this->emaj_viewer = false;
		if ($this->emaj_adm) {
		// emaj_adm role is also considered as E-maj viewer.
			$this->emaj_viewer = true;
		} else {
		// Otherwise, is the current role member of emaj_viewer role ?
			$sql = "SELECT CASE WHEN pg_catalog.pg_has_role('emaj_viewer','USAGE') THEN 1 ELSE 0 END AS is_emaj_viewer";
			$this->emaj_viewer = $data->selectField($sql,'is_emaj_viewer');
		}
		return $this->emaj_viewer;
	}

	/**
	 * Determine whether the emaj extension has been installed in the instance.
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
	 * Determine whether E-Maj has been created as an extension.
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
	 * Return the emaj extension versions available in the instance for a CREATE EXTENSION and compatible with this Emaj_web version.
	 */
	function getAvailableExtensionVersions() {
		global $data, $oldest_supported_emaj_version;

		$sql = "SELECT version FROM pg_catalog.pg_available_extension_versions
				  WHERE name = 'emaj' AND NOT installed
					AND (version = 'devel' OR version >= '$oldest_supported_emaj_version')
				  ORDER BY version DESC";

		return $data->selectSet($sql);
	}

	/**
	 * Return a boolean indicating whether one or several versions of the emaj extension are available for an ALTER EXTENSION UPDATE.
	 */
	function areThereVersionsToUpdate() {
		global $data, $oldest_supported_emaj_version;

		$sql = "SELECT CASE WHEN EXISTS (
				  SELECT target FROM pg_catalog.pg_extension_update_paths('emaj')
					WHERE source = '{$this->emaj_version}' AND path IS NOT NULL
					  AND (target = 'devel' OR target >= '$oldest_supported_emaj_version')
				) THEN 1 ELSE 0 END AS versions_exist";

		return $data->selectField($sql,'versions_exist');
	}

	/**
	 * Return the number of E-Maj event triggers.
	 */
	function getNumberEventTriggers() {
		global $data;

		$sql = "SELECT count(*) nb_event_trigger FROM pg_catalog.pg_event_trigger
				  WHERE evtname IN ('emaj_protection_trg','emaj_sql_drop_trg','emaj_table_rewrite_trg')";

		return $data->selectField($sql,'nb_event_trigger');
	}

	/**
	 * Return the emaj extension versions available as target for an ALTER EXTENSION UPDATE and compatible with this Emaj_web version.
	 */
	function getAvailableExtensionVersionsForUpdate() {
		global $data, $oldest_supported_emaj_version;

		$sql = "SELECT target FROM pg_catalog.pg_extension_update_paths('emaj')
				  WHERE source = '{$this->emaj_version}' AND path IS NOT NULL
					AND (target = 'devel' OR target >= '$oldest_supported_emaj_version')
				  ORDER BY 1 DESC";

		return $data->selectSet($sql);
	}

	/**
	 * Create the emaj extension.
	 */
	function createEmajExtension($version) {
		global $data, $misc;

		$data->clean($version);

		if ($version !== null && $version <> '')
			$version = "VERSION '$version'";
		else
			$version = '';

		$server_info = $misc->getServerInfo();
		if (version_compare($server_info['pgVersion'], '9.6', '<')) {
			$sql = "CREATE EXTENSION IF NOT EXISTS dblink;
					CREATE EXTENSION IF NOT EXISTS btree_gist;
					CREATE EXTENSION emaj $version;";
		} else {
			$sql = "CREATE EXTENSION emaj $version CASCADE;";
		}

		$status = $data->execute($sql);

		if ($status == 0) {
			// The extension has been created, so reset all emajdb cached variables.
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
	 * Update the emaj extension.
	 */
	function updateEmajExtension($version) {
		global $data, $misc;

		$data->clean($version);

		if ($version !== null && $version <> '')
			$version = "TO '$version'";
		else
			$version = '';

		$sql = "ALTER EXTENSION emaj UPDATE $version;";

		$status = $data->execute($sql);

		if ($status == 0) {
			// The extension version has changed, so reset all emajdb cached variables.
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
	 * Drop the emaj extension.
	 * The emaj_adm and emaj_viewer roles are not dropped as they can bu used in other databases.
	 */
	function dropEmajExtension() {
		global $data;

		// Ask the catalog whether the emaj_drop_extension() function exists (only introduced in 4.5.0).
		$sql = "SELECT 1 AS exists
				FROM pg_catalog.pg_proc JOIN pg_catalog.pg_namespace n ON (pronamespace = n.oid)
				WHERE nspname = 'emaj' AND proname = 'emaj_drop_extension'";
		if ($data->selectField($sql, 'exists') == 1) {
			// The function exists, so call it.
			$sql = "SELECT emaj.emaj_drop_extension()";
		} else {
			// The function doesn't exist, so drop the extension 'manually'.
			$sql = "DO LANGUAGE plpgsql $$
					BEGIN
						PERFORM emaj.emaj_disable_protection_by_event_triggers();
						PERFORM emaj.emaj_force_drop_group(group_name) FROM emaj.emaj_group;
						DROP EXTENSION IF EXISTS emaj CASCADE;
						DROP SCHEMA IF EXISTS emaj CASCADE;
						DROP FUNCTION IF EXISTS public._emaj_protection_event_trigger_fnct() CASCADE;
						RETURN;
					END;$$;";
		}

		$status = $data->execute($sql);

		if ($status == 0) {
			// The extension has been dropped, so reset all emajdb cached variables.
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
	 * Determine whether a dblink connection can be used for rollbacks (not necessarily for this user).
	 */
	function isDblinkUsable() {
		// Access cache
		if ($this->dblink_usable !== null) return $this->dblink_usable;

		global $data;

		// It checks that
		// - dblink is installed into the database by testing the existence of the dblink_connect_u function,
		// - the dblink_user_password E-Maj parameter has been configured.
		$sql = "SELECT CASE WHEN
                       EXISTS(SELECT 1 FROM pg_catalog.pg_proc WHERE proname = 'dblink_connect_u')
                   AND EXISTS(SELECT 1 FROM emaj.emaj_visible_param WHERE param_key = 'dblink_user_password')
                                 THEN 1 ELSE 0 END as cnx_ok";
		$this->dblink_usable = $data->selectField($sql,'cnx_ok');

		return $this->dblink_usable;
	}

	/**
	 * Determine whether the asynchronous rollback can be used for the current user.
	 * Parameter: $useCache = boolean to be explicitely set to false to force the check
	 * It checks that:
	 * - dblink is effectively usable
	 * - the psql_path and temp_dir parameters from the configuration file are set and usable
	 * If they are set, one tries to use them.
	 */
	function isAsyncRlbkUsable($useCache = true) {

		// Return from the cache if possible.
		if ($useCache && $this->asyncRlbkUsable !== null) return $this->asyncRlbkUsable;

		global $misc, $data, $conf;

		$this->asyncRlbkUsable = 0;

		// Check if dblink is usable.
		if ($this->isDblinkUsable()) {
			// If the _dblink_open_cnx() function is available for the user,
			//   open a test dblink connection, analyse the result and close it if effectively opened.
			$test_cnx_ok = 0;
			if ($this->getNumEmajVersion() >= 40600){	// version >= 4.6.0
				$sql = "SELECT CASE
							WHEN pg_catalog.has_function_privilege('emaj._dblink_open_cnx(text, text)', 'EXECUTE')
								THEN 1 ELSE 0 END as grant_open_ok";
			} else {
				$sql = "SELECT CASE
							WHEN pg_catalog.has_function_privilege('emaj._dblink_open_cnx(text)', 'EXECUTE')
								THEN 1 ELSE 0 END as grant_open_ok";
			}
			if ($data->selectField($sql, 'grant_open_ok')) {
				if ($this->getNumEmajVersion() >= 40600){	// version >= 4.6.0
					$sql = "SELECT CASE WHEN p_status >= 0 THEN 1 ELSE 0 END as cnx_ok, p_schema
							FROM emaj._dblink_open_cnx('test', current_role)";
					$rs = $data->selectSet($sql);
					if ($rs->fields['cnx_ok']) {
						$this->dblink_schema = $rs->fields['p_schema'];
						$sql = "SELECT emaj._dblink_close_cnx('test', '{$this->dblink_schema}')";
						$data->execute($sql);
						$test_cnx_ok = 1;
					}
				} elseif ($this->getNumEmajVersion() >= 40000){	// version >= 4.0.0
					$sql = "SELECT CASE WHEN p_status >= 0 THEN 1 ELSE 0 END as cnx_ok, p_schema
							FROM emaj._dblink_open_cnx('test')";
					$rs = $data->selectSet($sql);
					if ($rs->fields['cnx_ok']) {
						$this->dblink_schema = $rs->fields['p_schema'];
						$sql = "SELECT emaj._dblink_close_cnx('test', '{$this->dblink_schema}')";
						$data->execute($sql);
						$test_cnx_ok = 1;
					}
				} elseif ($this->getNumEmajVersion() >= 30100){	// version >= 3.1.0
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
				// Check if the emaj_web parameters are set.
				if (isset($conf['psql_path']) && isset($conf['temp_dir'])) {

					// Check the psql exe path supplied in the config file,
					// by executing a simple "psql --version" command
					$psqlExe = $misc->escapeShellCmd($conf['psql_path']);
					$version = array();
					preg_match("/(\d+(?:\.\d+)?)(?:\.\d+)?.*$/", exec($psqlExe . " --version"), $version);
					if (!empty($version)) {

						// OK, check a file can be written into the temp directory supplied in the config file.
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
	 * Get the emaj version from either the cache or a getVersion() call.
	 */
	function getEmajVersion() {
		// Access cache
		if ($this->emaj_version !== '?') return $this->emaj_version;
		// otherwise read from the emaj_param table
		$this->getVersion();
		return $this->emaj_version;
	}

	/**
	 * Get the emaj version in numeric format from either the cache or a getVersion() call.
	 */
	function getNumEmajVersion() {
		// Access cache
		if ($this->emaj_version_num !== 0) return $this->emaj_version_num;
		// otherwise read from the emaj_param table
		$this->getVersion();
		return $this->emaj_version_num;
	}

	/**
	 * Get emaj version from the emaj_visible_param.
	 */
	function getVersion() {
		global $data;

		// Initialize version values.
		$this->emaj_version = '?';
		$this->emaj_version_num = 0;

		// Ask the catalog whether the emaj_get_version() function exists (only introduced in 4.4.0).
		$sql = "SELECT 1 AS exists
				FROM pg_catalog.pg_proc JOIN pg_catalog.pg_namespace n ON (pronamespace = n.oid)
				WHERE nspname = 'emaj' AND proname = 'emaj_get_version'";
		if ($data->selectField($sql, 'exists') == 1) {
			// The function exists, so call it.
			$sql = "SELECT emaj.emaj_get_version() AS version";
		} else {
			// The function doesn't exist, so read the emaj_param table through the emaj_visible_param view.
			$sql = "SELECT param_value_text AS version
					FROM emaj.emaj_visible_param
					WHERE param_key = 'emaj_version'";
		}
		$rs = $data->selectSet($sql);
		if ($rs->recordCount() == 1){
			// Set the cached values.
			$this->emaj_version = $rs->fields['version'];
			if (substr_count($this->emaj_version, '.') == 2) {
				list($v1,$v2,$v3) = explode(".",$this->emaj_version);
				$this->emaj_version_num = 10000 * $v1 + 100 * $v2 + $v3;
			} elseif (substr_count($this->emaj_version, '.') == 1) {
				list($v1,$v2) = explode(".",$this->emaj_version);
				$this->emaj_version_num = 10000 * $v1 + 100 * $v2;
			} else {
				$this->emaj_version = htmlspecialchars($this->emaj_version);
				$this->emaj_version_num = 999999;
			}
		}
		return;
	}

	/**
	 * Get the E-Maj size on disk.
	 * = size of all relations in emaj primary and secondary schemas + size of linked toast tables.
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
								AND nspname IN (SELECT sch_name FROM emaj.emaj_schema)
							) AS t1,
							(
							SELECT sum(c2.relpages) as totalpages
							  FROM pg_catalog.pg_class c1, pg_catalog.pg_namespace, emaj.emaj_relation,
								   pg_catalog.pg_class c2
							  WHERE c1.relnamespace = pg_namespace.oid
								AND c2.oid = c1.reltoastrelid
								AND nspname = rel_log_schema
								AND c1.relname = rel_log_table
							) AS t2
						WHERE pg_settings.name = 'block_size'
					) as t";
			return $data->selectField($sql,'emajsize');
		}else{
			return '?';
		}
	}

	/**
	 * Check E-Maj consistency.
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
	 * Get the parameters stored into the emaj_param table.
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
					FROM emaj.$table
					WHERE param_key <> 'emaj_version'";			# The 'emaj_version' key no longer exists since emaj 4.4.0

		return $data->selectSet($sql);
	}

	/**
	 * Export the parameters configuration.
	 */
	function exportParamConfig() {
		global $data;

		$sql = "SELECT emaj.emaj_export_parameters_configuration() AS parameter_configuration";
		return $data->selectField($sql,'parameter_configuration');
	}

	/**
	 * Check a parameters configuration to import.
	 */
	function checkJsonParamConf($json) {
		global $data, $lang;

		$data->clean($json);

		$sql = "SELECT
				rpt_severity,
				CASE rpt_msg_type
					WHEN 101 THEN '" . $data->clean($lang['strcheckjsonparamconf101']) . "'
					WHEN 102 THEN format('" . $data->clean($lang['strcheckjsonparamconf102']) . "', rpt_int_var_1)
					WHEN 103 THEN format('" . $data->clean($lang['strcheckjsonparamconf103']) . "', rpt_text_var_1, rpt_text_var_2)
					WHEN 104 THEN format('" . $data->clean($lang['strcheckjsonparamconf104']) . "', rpt_text_var_1)
					WHEN 105 THEN format('" . $data->clean($lang['strcheckjsonparamconf105']) . "', rpt_text_var_1)
                    ELSE 'Message not decoded (' || rpt_msg_type || ')'
				END as rpt_message
			FROM emaj._check_json_param_conf(E'$json'::json)
			ORDER BY rpt_msg_type, rpt_text_var_1, rpt_text_var_2, rpt_int_var_1";

		return $data->selectSet($sql);
	}

	/**
	 * Import the parameters configuration.
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
	 * Get all groups referenced in emaj_group table for this database.
	 * The function is called to feed the browser tree or to list the groups to export (emaj 3.3+).
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
	 * Get number of created groups.
	 */
	function getNbGroups() {
		global $data;

		$sql = "SELECT count(*) as nb_groups FROM emaj.emaj_group";

		return $data->selectField($sql,'nb_groups');
	}

	/**
	 * Get all idle groups referenced in emaj_group table for this database.
	 */
	function getIdleGroups() {
		global $data;

		if ($this->getNumEmajVersion() >= 40400) {		// version 4.4+
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence, group_comment,
						CASE WHEN group_is_rollbackable THEN 'ROLLBACKABLE' ELSE 'AUDIT_ONLY' END as group_type,
						to_char(time_tx_timestamp,'{$this->tsFormat}') as creation_datetime,
						1 as has_waiting_changes,
						(SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM emaj.emaj_group
						LEFT OUTER JOIN emaj.emaj_group_hist ON (grph_group = group_name AND upper_inf(grph_time_range))
						LEFT OUTER JOIN emaj.emaj_time_stamp ON (time_id = lower(grph_time_range))
					WHERE NOT group_is_logging
					ORDER BY group_name";
		} else {
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence, group_comment,
					CASE WHEN group_is_rollbackable THEN 'ROLLBACKABLE' ELSE 'AUDIT_ONLY' END
						as group_type,
					to_char(time_tx_timestamp,'{$this->tsFormat}') as creation_datetime, ";
			if ($this->getNumEmajVersion() < 40000){	// version 3.x
				$sql .=	"CASE WHEN group_has_waiting_changes THEN 1 ELSE 0 END as has_waiting_changes, ";
			} else {
				$sql .=	"1 as has_waiting_changes, ";
			}
			$sql .= " (SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM emaj.emaj_group, emaj.emaj_time_stamp
					WHERE NOT group_is_logging
					AND time_id = group_creation_time_id
					ORDER BY group_name";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Get all Logging groups referenced in emaj_group table for this database.
	 */
	function getLoggingGroups() {
		global $data;

		if ($this->getNumEmajVersion() >= 40400) {		// version 4.4+
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence, group_comment,
						CASE WHEN NOT group_is_rollbackable THEN 'AUDIT_ONLY'
							WHEN group_is_rollbackable AND NOT group_is_rlbk_protected THEN 'ROLLBACKABLE'
							ELSE 'ROLLBACKABLE-PROTECTED' END as group_type,
						to_char(time_tx_timestamp,'{$this->tsFormat}') as creation_datetime,
						1 as has_waiting_changes,
						(SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM emaj.emaj_group
						LEFT OUTER JOIN emaj.emaj_group_hist ON (grph_group = group_name AND upper_inf(grph_time_range))
						LEFT OUTER JOIN emaj.emaj_time_stamp ON (time_id = lower(grph_time_range))
					WHERE group_is_logging
					ORDER BY group_name";
		} else {
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence, group_comment,
					CASE WHEN NOT group_is_rollbackable THEN 'AUDIT_ONLY'
						WHEN group_is_rollbackable AND NOT group_is_rlbk_protected THEN 'ROLLBACKABLE'
						ELSE 'ROLLBACKABLE-PROTECTED' END as group_type,
					to_char(time_tx_timestamp,'{$this->tsFormat}') as creation_datetime, ";
			if ($this->getNumEmajVersion() < 40000){	// version 3.x
				$sql .=	"CASE WHEN group_has_waiting_changes THEN 1 ELSE 0 END as has_waiting_changes, ";
			} else {
				$sql .=	"1 as has_waiting_changes, ";
			}
			$sql .= " (SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM emaj.emaj_group, emaj.emaj_time_stamp
					WHERE group_is_logging
					AND time_id = group_creation_time_id
					ORDER BY group_name";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Get all inexistent tables groups that are known as having existed in the past and having been dropped.
	 */
	function getDroppedGroups() {
		global $data;

		$sql = "WITH selected_group AS (
					SELECT grph_group, max(upper(grph_time_range)) AS lastest_drop_time_id
					FROM emaj.emaj_group_hist
					WHERE NOT EXISTS (SELECT 0 FROM emaj.emaj_group WHERE group_name = grph_group)
					GROUP BY 1
				)
				SELECT h.grph_group, to_char(time_tx_timestamp,'{$this->tsFormat}') AS latest_drop_datetime,
					   CASE WHEN h.grph_is_rollbackable THEN 'ROLLBACKABLE' ELSE 'AUDIT_ONLY' END AS latest_is_rollbackable
					FROM selected_group s JOIN emaj.emaj_group_hist h ON (h.grph_group = s.grph_group AND upper(grph_time_range) = lastest_drop_time_id)
					JOIN emaj.emaj_time_stamp ON (time_id = upper(grph_time_range) - 1)
					ORDER BY 1";

		return $data->selectSet($sql);
	}

	/**
	 * Get all configured but not yet created groups.
	 * They are referenced in emaj_group_def but not in emaj_group tables.
	 * Also return counters: number of tables, sequences.
	 */
	function getConfiguredGroups() {
		global $data;

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

		return $data->selectSet($sql);
	}

	/**
	 * Get some details about existing groups among a set of configured groups to import.
	 */
	function getGroupsToImport($configuredGroupsArray) {
		global $data;

		$data->arrayClean($configuredGroupsArray);

		$values = '';
		foreach($configuredGroupsArray as $group){
			$values .= "('$group'), ";
		}
		$values = substr($values, 0, strlen($values) - 2);

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
					FROM (VALUES $values) AS t(grp_name)
						LEFT OUTER JOIN emaj.emaj_group ON (group_name = grp_name)
					ORDER BY group_name";

		return $data->selectSet($sql);
	}

	/**
	 * Verify whether a schema currently exists.
	 */
	function existsSchema($schema) {
		global $data;

		$data->clean($schema);

		$sql = "SELECT CASE WHEN EXISTS
					(SELECT 0
						FROM pg_catalog.pg_namespace
						WHERE nspname = '$schema'
					) THEN 1 ELSE 0 END AS schema_exists";

		return $data->selectField($sql,'schema_exists');
	}

	/**
	 * Detect tables or sequences that no longer exist for a schema.
	 */
	function missingTblSeqs($schema, $tblSeqsList, $relKind) {
		global $data;

		$data->clean($schema);
		$data->clean($tblSeqsList);
		$data->clean($relKind);
		$relsArray = "ARRAY['".str_replace(', ',"','",$tblSeqsList)."']";
		if ($relKind == 'table')
			$kind = 'r';
		else
			$kind = 'S';

		$sql = "SELECT count(*) AS nb_tblseqs, string_agg(name, ', ') AS tblseqs_list
					FROM unnest($relsArray) AS name
					WHERE NOT EXISTS(
						SELECT 0
							FROM pg_catalog.pg_class
								 JOIN pg_catalog.pg_namespace ON (pg_namespace.oid = relnamespace)
							WHERE nspname = '$schema'
							  AND relname = name
							  AND relkind = '$kind')";

		return $data->selectSet($sql);
	}

	/**
	 * Verify whether a group currently exists.
	 */
	function existsGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT CASE WHEN EXISTS
					(SELECT 0
						FROM emaj.emaj_group
						WHERE group_name = '$group'
					) THEN 1 ELSE 0 END AS group_exists";

		return $data->selectField($sql,'group_exists');
	}

	/**
	 * Detect groups that no longer exist.
	 */
	function missingGroups($groupsList) {
		global $data;

		$data->clean($groupsList);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groupsList)."']";

		$sql = "SELECT count(*) AS nb_groups, string_agg(name, ', ') AS groups_list
					FROM unnest($groupsArray) AS name
					WHERE NOT EXISTS(SELECT 0 FROM emaj.emaj_group WHERE group_name = name)";

		return $data->selectSet($sql);
	}

	/**
	 * Detect groups in logging state.
	 */
	function loggingGroups($groupsList) {
		global $data;

		$data->clean($groupsList);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groupsList)."']";

		$sql = "SELECT count(*) AS nb_groups, string_agg(group_name, ', ') AS groups_list
					FROM emaj.emaj_group
					WHERE group_name = ANY($groupsArray)
					  AND group_is_logging";

		return $data->selectSet($sql);
	}

	/**
	 * Detect groups in idle state.
	 */
	function idleGroups($groupsList) {
		global $data;

		$data->clean($groupsList);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groupsList)."']";

		$sql = "SELECT count(*) AS nb_groups, string_agg(group_name, ', ') AS groups_list
					FROM emaj.emaj_group
					WHERE group_name = ANY($groupsArray)
					  AND NOT group_is_logging";

		return $data->selectSet($sql);
	}

	/**
	 * Detect groups that already have a given new mark.
	 */
	function knownMarkGroups($groupsList,$mark) {
		global $data;

		$data->clean($groupsList);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groupsList)."']";
		$data->clean($mark);

		$sql = "SELECT count(*) AS nb_groups, string_agg(mark_group, ', ') AS groups_list
					FROM emaj.emaj_mark
					WHERE mark_name = '$mark'
					  AND mark_group = ANY ($groupsArray)";

		return $data->selectSet($sql);
	}

	/**
	 * Detect marks that no longer exist for a group.
	 */
	function missingMarksGroup($group, $marksList) {
		global $data;

		$data->clean($group);
		$data->clean($marksList);
		$marksArray = "ARRAY['".str_replace(', ',"','",$marksList)."']";

		$sql = "SELECT count(*) AS nb_marks, string_agg(name, ', ') AS marks_list
					FROM unnest($marksArray) AS name
					WHERE NOT EXISTS(SELECT 0 FROM emaj.emaj_mark WHERE mark_group = '$group' AND mark_name = name)";

		return $data->selectSet($sql);
	}

	/**
	 * Detect groups that no longer have a mark.
	 */
	function missingMarkGroups($groupsList, $mark) {
		global $data;

		$data->clean($groupList);
		$data->clean($mark);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groupsList)."']";

		$sql = "SELECT count(*) AS nb_groups, string_agg(name, ', ') AS groups_list
					FROM unnest($groupsArray) AS name
					WHERE NOT EXISTS(SELECT 0 FROM emaj.emaj_mark WHERE mark_group = name AND mark_name = '$mark')";

		return $data->selectSet($sql);
	}

	/**
	 * Get properties of a single tables group.
	 */
	function getGroup($group) {
		global $data;

		$data->clean($group);

		if ($this->getNumEmajVersion() >= 40400) {		// version 4.4+
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence,
						to_char(c.time_tx_timestamp,'{$this->tsFormat}') as group_creation_datetime,
						to_char(s.time_tx_timestamp,'{$this->tsFormat}') as group_start_datetime,
						CASE WHEN group_is_logging THEN 'LOGGING' ELSE 'IDLE' END as group_state,
						CASE WHEN NOT group_is_rollbackable THEN 'AUDIT_ONLY'
							WHEN group_is_rollbackable AND NOT group_is_rlbk_protected THEN 'ROLLBACKABLE'
							ELSE 'ROLLBACKABLE-PROTECTED' END as group_type,
						coalesce(group_comment, '') as group_comment,
						pg_size_pretty((SELECT sum(pg_total_relation_size(quote_ident(rel_log_schema) || '.' || quote_ident(rel_log_table)))
							FROM emaj.emaj_relation
							WHERE rel_group = group_name AND rel_kind = 'r')::bigint) as log_size,
						1 as has_waiting_changes,
						(SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM emaj.emaj_group
						LEFT OUTER JOIN emaj.emaj_group_hist ON (grph_group = group_name AND upper_inf(grph_time_range))
						LEFT OUTER JOIN emaj.emaj_time_stamp c ON (c.time_id = lower(grph_time_range))
						LEFT OUTER JOIN emaj.emaj_log_session ON (lses_group = group_name AND upper_inf(lses_time_range))
						LEFT OUTER JOIN emaj.emaj_time_stamp s ON (s.time_id = lower(lses_time_range))
					WHERE group_name = '$group'";
		} else {
			$sql = "SELECT group_name, group_nb_table, group_nb_sequence,
					to_char(time_tx_timestamp,'{$this->tsFormat}') as group_creation_datetime,
					CASE WHEN group_is_logging THEN 'LOGGING' ELSE 'IDLE' END as group_state,
					CASE WHEN NOT group_is_rollbackable THEN 'AUDIT_ONLY'
						WHEN group_is_rollbackable AND NOT group_is_rlbk_protected THEN 'ROLLBACKABLE'
						ELSE 'ROLLBACKABLE-PROTECTED' END as group_type,
					coalesce(group_comment, '') as group_comment,
					pg_size_pretty((SELECT sum(pg_total_relation_size(quote_ident(rel_log_schema) || '.' || quote_ident(rel_log_table)))
						FROM emaj.emaj_relation
						WHERE rel_group = group_name AND rel_kind = 'r')::bigint) as log_size, ";
			if ($this->getNumEmajVersion() < 40000){	// version 3.x
				$sql .=	"CASE WHEN group_has_waiting_changes THEN 1 ELSE 0 END as has_waiting_changes, ";
			} else {
				$sql .=	"1 as has_waiting_changes,";
			}
			$sql .= " (SELECT count(*) FROM emaj.emaj_mark WHERE mark_group = emaj_group.group_name) as nb_mark
					FROM emaj.emaj_group, emaj.emaj_time_stamp
					WHERE group_name = '$group'
					AND time_id = group_creation_time_id";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Get the isLogging property of one emaj_group (1 if in logging state, 0 if idle).
	 */
	function isGroupLogging($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT CASE WHEN group_is_logging THEN 1 ELSE 0 END AS is_logging
				FROM emaj.emaj_group
				WHERE group_name = '$group'";

		return $data->selectField($sql,'is_logging');
	}

	/**
	 * Get the isRollbackable property of one emaj_group (1 if rollbackable, 0 if audit_only).
	 */
	function isGroupRollbackable($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT CASE WHEN group_is_rollbackable THEN 1 ELSE 0 END AS is_rollbackable
				FROM emaj.emaj_group
				WHERE group_name = '$group'";

		return $data->selectField($sql,'is_rollbackable');
	}

	/**
	 * Get the isProtected property of one emaj_group (1 if protected, 0 if unprotected).
	 */
	function isGroupProtected($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT CASE WHEN group_is_rlbk_protected THEN 1 ELSE 0 END AS is_protected
				FROM emaj.emaj_group
				WHERE group_name = '$group'";

		return $data->selectField($sql,'is_protected');
	}

	/**
	 * Export a tables groups configuration.
	 */
	function exportGroupsConfig($groups) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		$sql = "SELECT emaj.emaj_export_groups_configuration($groupsArray) AS groups_configuration";
		return $data->selectField($sql,'groups_configuration');
	}

	/**
	 * Prepare the a tables groups configuration import.
	 */
	function importGroupsConfPrepare($groupsConfig, $groups) {
		global $data, $lang;

		$data->clean($groupsConfig);
		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		// The tables groups configuration import prepare step is inserted into a transaction,
		//   so that it can be properly canceled if errors are detected.
		// The transaction will be commited just later in the importGroupsConfig() function call.
		$status = $data->beginTransaction();

		// rpt_msg_type 260 and 261 disappear in emaj 4.0+
		$sql = "SELECT rpt_severity,
				CASE rpt_msg_type
					WHEN  1 THEN format('" . $data->clean($lang['strcheckconfgroups01']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN  2 THEN format('" . $data->clean($lang['strcheckconfgroups02']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN  3 THEN format('" . $data->clean($lang['strcheckconfgroups03']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN  4 THEN format('" . $data->clean($lang['strcheckconfgroups04']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN  5 THEN format('" . $data->clean($lang['strcheckconfgroups05']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 10 THEN format('" . $data->clean($lang['strcheckconfgroups10']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 11 THEN format('" . $data->clean($lang['strcheckconfgroups11']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 12 THEN format('" . $data->clean($lang['strcheckconfgroups12']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 13 THEN format('" . $data->clean($lang['strcheckconfgroups13']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 15 THEN format('" . $data->clean($lang['strcheckconfgroups15']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 16 THEN format('" . $data->clean($lang['strcheckconfgroups16']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 20 THEN format('" . $data->clean($lang['strcheckconfgroups20']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 21 THEN format('" . $data->clean($lang['strcheckconfgroups21']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 22 THEN format('" . $data->clean($lang['strcheckconfgroups22']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 30 THEN format('" . $data->clean($lang['strcheckconfgroups30']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 31 THEN format('" . $data->clean($lang['strcheckconfgroups31']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 32 THEN format('" . $data->clean($lang['strcheckconfgroups32']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 33 THEN format('" . $data->clean($lang['strcheckconfgroups33']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 250 THEN format('" . $data->clean($lang['strgroupsconfimport250']) . "', rpt_text_var_1)
					WHEN 251 THEN format('" . $data->clean($lang['strgroupsconfimport251']) . "', rpt_text_var_1)
					WHEN 252 THEN format('" . $data->clean($lang['strgroupsconfimport252']) . "', rpt_text_var_1)
					WHEN 260 THEN format('" . $data->clean($lang['strgroupsconfimport260']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 261 THEN format('" . $data->clean($lang['strgroupsconfimport261']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
                    ELSE 'Message not decoded (' || rpt_msg_type || ')'
				END as rpt_message
			FROM emaj._import_groups_conf_prepare('$groupsConfig'::json, $groupsArray, true, NULL)";

		$errors = $data->selectSet($sql);

		if ($errors->recordCount() != 0) {
			$data->rollbackTransaction();
		}

		return $errors;
	}

	/**
	 * Import a tables groups configuration.
	 */
	function importGroupsConfig($groupsConfig, $groups, $mark) {
		global $data;

		$data->clean($groupsConfig);
		$data->clean($groups);
		$data->clean($mark);

		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		if ($this->getNumEmajVersion() >= 40000){	// version 4.0+
			$sql = "SELECT emaj._import_groups_conf_exec('$groupsConfig'::json, $groupsArray, '$mark') AS nb_groups";
		} else {
			$sql = "SELECT emaj._import_groups_conf_exec('$groupsConfig'::json, $groupsArray, ) AS nb_groups";
		}
		$nbGroups = $data->selectField($sql,'nb_groups');

		// Commit the transaction started in the importGroupsConfPrepare() function call
		$data->endTransaction();

		return $nbGroups;
	}

	/**
	 * Get all marks related to a group.
	 */
	function getMarks($group) {
		global $data;

		$data->clean($group);

		if ($this->getNumEmajVersion() >= 40400){	// version 4.4+
//			The mark_log_session column is the concatetion of 3 fields separeted with #: the position of the mark in its log session,
//				and the log session start and stop times.
			$sql = "WITH mark_1 AS (
						SELECT mark_time_id, mark_group, mark_name, mark_comment,
							to_char(m.time_tx_timestamp,'{$this->tsFormat}') AS mark_datetime,
							CASE WHEN mark_time_id = lower(lses_time_range) THEN 'Begin'
								 WHEN first_value(mark_name) OVER (PARTITION BY lses_group, lses_time_range ORDER BY mark_time_id
										RANGE BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) = mark_name THEN 'First'
								 ELSE '' END
							||
							CASE WHEN mark_time_id = upper(lses_time_range) - 1 THEN 'End'
								 WHEN last_value(mark_name) OVER (PARTITION BY lses_group, lses_time_range order by mark_time_id
										RANGE BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING) = mark_name
									AND NOT upper_inf(lses_time_range) THEN 'Last'
								 ELSE '' END
							|| '#' || coalesce(l.time_clock_timestamp::TEXT, '') || '#' || coalesce(u.time_clock_timestamp::TEXT, '')
								AS mark_log_session,
							CASE WHEN mark_is_rlbk_protected THEN 'PROTECTED' ELSE '' END AS mark_state,
							coalesce(mark_log_rows_before_next,
								(SELECT SUM(stat_rows)
									FROM emaj.emaj_log_stat_group(emaj_mark.mark_group,emaj_mark.mark_name,NULL)),0)
									AS mark_logrows,
							(upper(lses_time_range) IS NOT NULL OR
								count(*) FILTER (where mark_is_rlbk_protected)
										OVER (ORDER BY mark_time_id DESC
											ROWS BETWEEN UNBOUNDED PRECEDING AND 0 FOLLOWING EXCLUDE CURRENT ROW) > 0) AS no_rollback_action,
							(upper(lses_time_range) IS NOT NULL OR mark_is_rlbk_protected) AS no_protect_action,
							NOT mark_is_rlbk_protected AS no_unprotect_action,
							first_value(mark_time_id) OVER (ORDER BY mark_time_id) = mark_time_id AS no_first_mark_action
						FROM emaj.emaj_mark
							JOIN emaj.emaj_time_stamp m ON (time_id = mark_time_id)
							LEFT OUTER JOIN emaj.emaj_log_session ON (lses_group = mark_group AND lses_time_range @> mark_time_id)
							LEFT OUTER JOIN emaj.emaj_time_stamp l ON (l.time_id = lower(lses_time_range))
							LEFT OUTER JOIN emaj.emaj_time_stamp u ON (u.time_id = upper(lses_time_range) - 1)
						WHERE mark_group = '$group'
						GROUP BY 1,2,3,4,5, lses_time_range, lses_group, l.time_clock_timestamp, u.time_clock_timestamp
						)
					SELECT mark_group, mark_name, mark_datetime, mark_comment, mark_state, mark_logrows, mark_log_session,
						sum(mark_logrows) OVER (ORDER BY mark_time_id DESC) AS mark_cumlogrows,
						no_rollback_action, no_protect_action, no_unprotect_action, no_first_mark_action
					FROM mark_1
					ORDER BY mark_time_id DESC";
		} else {
			$sql = "WITH mark_1 AS (
						SELECT mark_time_id, mark_group, mark_name,
							to_char(time_tx_timestamp,'{$this->tsFormat}') as mark_datetime, mark_comment,
							CASE WHEN mark_is_deleted THEN 'DELETED'
								WHEN NOT mark_is_deleted AND mark_is_rlbk_protected THEN 'ACTIVE-PROTECTED'
								ELSE 'ACTIVE' END as mark_state,
							coalesce(mark_log_rows_before_next,
								(SELECT SUM(stat_rows)
									FROM emaj.emaj_log_stat_group(emaj_mark.mark_group,emaj_mark.mark_name,NULL)),0)
								AS mark_logrows,
							(mark_is_deleted OR
							count(*) FILTER (where mark_is_rlbk_protected)
										OVER (ORDER BY mark_time_id DESC
											ROWS BETWEEN UNBOUNDED PRECEDING AND 0 FOLLOWING EXCLUDE CURRENT ROW) > 0) AS no_rollback_action,
							(mark_is_deleted OR mark_is_rlbk_protected) AS no_protect_action,
							NOT mark_is_rlbk_protected AS no_unprotect_action,
							first_value(mark_time_id) OVER (ORDER BY mark_time_id) = mark_time_id AS no_first_mark_action
						FROM emaj.emaj_mark, emaj.emaj_time_stamp
						WHERE mark_group = '$group'
						AND time_id = mark_time_id
						GROUP BY 1,2,3,4,5
						)
					SELECT mark_group, mark_name, mark_datetime, mark_comment, mark_state, mark_logrows,
						sum(mark_logrows) OVER (ORDER BY mark_time_id DESC) AS mark_cumlogrows,
						no_rollback_action, no_protect_action, no_unprotect_action, no_first_mark_action
					FROM mark_1
					ORDER BY mark_time_id DESC";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Get number of marks for a group.
	 */
	function getNbMarks($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT count(*) as nb_marks FROM emaj.emaj_mark WHERE mark_group = '$group'";

		return $data->selectField($sql,'nb_marks');
	}

	/**
	 * Get the content of a single tables group.
	 */
	function getContentGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT rel_kind || '+' AS relkind, rel_schema, rel_tblseq,
					to_char(time_tx_timestamp,'{$this->tsFormat}') as start_time,
					rel_priority, rel_log_dat_tsp, rel_log_idx_tsp,
					rel_log_schema || '.' || rel_log_table as full_log_table,
					CASE WHEN rel_kind = 'r' THEN
						pg_size_pretty(pg_total_relation_size(quote_ident(rel_log_schema) || '.' || quote_ident(rel_log_table)))
						|| '|' ||
						pg_total_relation_size(quote_ident(rel_log_schema) || '.' || quote_ident(rel_log_table))::TEXT
					END AS log_size,
					CASE WHEN rel_kind = 'r' THEN 'table' ELSE 'sequence' END as rel_type
				FROM emaj.emaj_relation, emaj.emaj_time_stamp
				WHERE lower(rel_time_range) = time_id
				  AND rel_group = '$group'
				  AND upper_inf(rel_time_range)
				ORDER BY rel_schema, rel_tblseq";

		return $data->selectSet($sql);
	}

	/**
	 * Return the history of create-drop and log sessions for a tables group.
	 */
	function getHistoryGroup($group) {

		global $data;

		$data->clean($group);

		$sql = "SELECT lower(lses_time_range), 'LS' AS event, 'strlogsession#GreySimple' AS graphic,
					   NULL AS create_drop_time,NULL AS grph_log_sessions,
					   to_char(b.time_tx_timestamp,'{$this->tsFormat}') AS start_time,
					   to_char(e.time_tx_timestamp,'{$this->tsFormat}') AS stop_time,
					   lses_marks, lses_log_rows
					FROM emaj.emaj_log_session
					     LEFT OUTER JOIN emaj.emaj_time_stamp b ON (b.time_id = lower(lses_time_range))
					     LEFT OUTER JOIN emaj.emaj_time_stamp e ON (e.time_id = upper(lses_time_range) - 1)
					WHERE lses_group = '$group'
			UNION ALL
				SELECT lower(grph_time_range), 'C', 'strgroupcreate#GreyBegin',
					   to_char(time_tx_timestamp,'{$this->tsFormat}'), grph_log_sessions,
					   NULL, NULL, NULL, NULL
					FROM emaj.emaj_group_hist
					     LEFT OUTER JOIN emaj.emaj_time_stamp ON (time_id = lower(grph_time_range))
					WHERE grph_group = '$group'
						AND NOT lower_inf(grph_time_range)
			UNION ALL
				SELECT upper(grph_time_range) - 1, 'D', 'strgroupdrop#GreyEnd',
					   to_char(time_tx_timestamp,'{$this->tsFormat}'), grph_log_sessions,
					   NULL, NULL, NULL, NULL
					FROM emaj.emaj_group_hist
					     LEFT OUTER JOIN emaj.emaj_time_stamp ON (time_id = upper(grph_time_range) -1)
					WHERE grph_group = '$group'
						AND NOT upper_inf(grph_time_range)
			UNION ALL
				SELECT lower(grph_time_range), 'DLS', 'strdeletedlogsessions#GreyDotted',
					   NULL, NULL, NULL, NULL, NULL, NULL
					FROM emaj.emaj_group_hist
					WHERE grph_group = '$group'
						AND grph_log_sessions > (SELECT count(*) FROM emaj.emaj_log_session
													WHERE lses_group = '$group' AND lses_time_range <@ grph_time_range)
			ORDER BY 1 DESC, 2 DESC";

		return $data->selectSet($sql);
	}

	/**
	 * Return the list of all existing emaj schemas recorded in the emaj_schema table.
	 */
	function getEmajSchemasList() {
		global $data;

		$sql = "SELECT string_agg(sch_name, ',' ORDER BY sch_name) AS schemas_list FROM emaj.emaj_schema";

		return $data->selectField($sql,'schemas_list');
	}

	/**
	 * Return all non system schemas but emaj from the current database
	 * plus all nonexistent schemas but listed in emaj_group_def.
	 */
	function getSchemas() {
		global $data;

		$sql = "SELECT 1, pn.nspname, pu.rolname AS nspowner,
					   pg_catalog.obj_description(pn.oid, 'pg_namespace') AS nspcomment
					FROM pg_catalog.pg_namespace pn
						LEFT JOIN pg_catalog.pg_roles pu ON (pn.nspowner = pu.oid)
					WHERE nspname NOT LIKE 'pg@_%' ESCAPE '@'
					  AND nspname != 'information_schema' AND nspname != 'emaj'
					  AND nspname NOT IN (SELECT sch_name FROM emaj.emaj_schema)
				UNION
				SELECT DISTINCT 2, grpdef_schema AS nspname, '!' AS nspowner, NULL AS nspcomment
					FROM emaj.emaj_group_def
					WHERE grpdef_schema NOT IN (SELECT nspname FROM pg_catalog.pg_namespace)
				ORDER BY 1, nspname";

		return $data->selectSet($sql);
	}

	/**
	 * Return all existing non system schemas in the databse.
	 */
	function getAllSchemas() {
		global $data;

		$sql = "SELECT pn.nspname, pu.rolname AS nspowner,
					   pg_catalog.obj_description(pn.oid, 'pg_namespace') AS nspcomment,
					   EXISTS (SELECT 0 FROM emaj.emaj_schema WHERE sch_name = nspname) AS nspisemaj
				FROM pg_catalog.pg_namespace pn
					LEFT JOIN pg_catalog.pg_roles pu ON (pn.nspowner = pu.oid)
				WHERE nspname NOT LIKE 'pg@_%' ESCAPE '@' AND nspname != 'information_schema'
				ORDER BY nspname";

		return $data->selectSet($sql);
	}

	/**
	 * Return all tables of a schema, with their current E-Maj characteristics.
	 * Filter regular tables and partitioned tables.
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
					c.relkind::TEXT || case when (relkind = 'r' and $goodTypeConditions) then '+' else '-' end as relkind,
					pg_catalog.pg_get_userbyid(c.relowner) AS relowner,
					pg_catalog.obj_description(c.oid, 'pg_class') AS relcomment, spcname AS tablespace,
					coalesce(rel_group, '') AS rel_group, coalesce(rel_priority::text, '') AS rel_priority,
					coalesce(rel_log_dat_tsp, '') AS rel_log_dat_tsp, coalesce(rel_log_idx_tsp, '') AS rel_log_idx_tsp
					FROM pg_catalog.pg_class c
						LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
						LEFT JOIN emaj.emaj_relation ON rel_schema = nspname AND rel_tblseq = c.relname AND upper_inf(rel_time_range)
						LEFT JOIN pg_catalog.pg_tablespace pt ON pt.oid = c.reltablespace
					WHERE c.relkind IN ('r','p') AND nspname = '$schema'
				ORDER BY relname";

		return $data->selectSet($sql);
	}

	/**
	 * Return all sequences of a schema, with their current E-Maj characteristics.
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
						LEFT JOIN emaj.emaj_relation ON rel_schema = nspname AND rel_tblseq = c.relname AND upper_inf(rel_time_range)
						LEFT JOIN pg_catalog.pg_tablespace pt ON pt.oid = c.reltablespace
					WHERE c.relkind = 'S' AND nspname = '$schema'
				ORDER BY relname";

		return $data->selectSet($sql);
	}

	/**
	 * Return the current Emaj properties for a single table or sequence.
	 */
	function getRelationEmajProperties($schema, $tblseq) {
		global $data;

		$data->clean($schema);
		$data->clean($tblseq);

		$sql = "SELECT coalesce(rel_group, '') AS rel_group, to_char(time_tx_timestamp,'{$this->tsFormat}') AS assign_ts,
					   coalesce(rel_priority::text, '') AS rel_priority,
					   coalesce(rel_log_dat_tsp, '') AS rel_log_dat_tsp, coalesce(rel_log_idx_tsp, '') AS rel_log_idx_tsp
					FROM emaj.emaj_relation
						LEFT OUTER JOIN emaj.emaj_time_stamp ON time_id = lower(rel_time_range)
					WHERE rel_schema = '$schema' AND rel_tblseq = '$tblseq' AND upper_inf(rel_time_range)";

		return $data->selectSet($sql);
	}

	/**
	 * Return a boolean indicating whether the user has SELECT privilege on a table.
	 */
	function hasSelectPrivilegeOnTable($schema, $table) {
		global $data;

		$data->clean($schema);
		$data->clean($table);

		// Check first the user has the USAGE privileges on the schema
		$sql = "SELECT has_schema_privilege('$schema', 'usage') has_usage_privilege";

		if ($data->selectField($sql,'has_usage_privilege') != 't') {
			return 0;
		}

		// Check now that the user has the SELECT privileges on the table itself
		$fullTableName = '"' . str_replace('"', '""', $schema) . '"."' . str_replace('"', '""', $table) . '"';
		$sql = "SELECT CASE WHEN has_table_privilege('$fullTableName', 'select') THEN 1
						    ELSE 0 END AS has_select_privilege";

		return $data->selectField($sql,'has_select_privilege');
	}

	/**
	 * Return a boolean indicating whether the table has a PRIMARY KEY.
	 */
	function hasTablePK($schema, $table) {
		global $data;

		$data->clean($schema);
		$data->clean($table);

		$sql = "SELECT CASE WHEN EXISTS(
					SELECT 0
					FROM pg_catalog.pg_constraint
						JOIN pg_catalog.pg_class c ON (conrelid = c.oid)
						JOIN pg_catalog.pg_namespace n ON (relnamespace = n.oid)
					WHERE nspname = '$schema'
					  AND relname = '$table'
					  AND contype = 'p'
					) THEN 1 ELSE 0 END AS has_pk";

		return $data->selectField($sql,'has_pk');
	}

	/**
	 * Return a boolean indicating whether the user has SELECT privilege on a sequence.
	 */
	function hasSelectPrivilegeOnSequence($schema, $sequence) {
		global $data;

		$data->clean($schema);
		$data->clean($sequence);

		// Check first the user has the USAGE privileges on the schema
		$sql = "SELECT has_schema_privilege('$schema', 'usage') has_usage_privilege";

		if ($data->selectField($sql,'has_usage_privilege') != 't') {
			return 0;
		}

		// Check now that the user has the SELECT privileges on the sequence itself
		$fullSequenceName = '"' . str_replace('"', '""', $schema) . '"."' . str_replace('"', '""', $sequence) . '"';
		$sql = "SELECT CASE WHEN has_sequence_privilege('$fullSequenceName', 'select') THEN 1
						    ELSE 0 END AS has_select_privilege";

		return $data->selectField($sql,'has_select_privilege');
	}

	/**
	 * Return properties of a single sequence.
	 */
	function getSequenceProperties($schema, $sequence) {
		global $data, $misc;

		$iSchema = $schema;
		$iSequence = $sequence;

		$data->clean($schema);
		$data->clean($sequence);
		$data->fieldClean($iSchema);
		$data->fieldClean($iSequence);

		$server_info = $misc->getServerInfo();

		if (version_compare($server_info['pgVersion'], '10', '<')) {				// Postgres version < 10
			$sql = "SELECT last_value, is_called, start_value, min_value, max_value, increment_by, is_cycled AS cycle,
							cache_value AS cache_size, log_cnt,
							pg_catalog.obj_description(s.tableoid, 'pg_class') AS seqcomment
					FROM \"$iSchema\".\"$iSequence\" AS s, pg_catalog.pg_class c, pg_catalog.pg_namespace n
					WHERE c.relnamespace = n.oid
						AND c.relname = '$sequence' AND n.nspname='$schema' AND c.relkind = 'S'";
		} else {
			$sql = "SELECT s.last_value, is_called, start_value, min_value, max_value, increment_by, cycle, cache_size, log_cnt,
							pg_catalog.obj_description(s.tableoid, 'pg_class') AS seqcomment
					FROM \"$iSchema\".\"$iSequence\" AS s, pg_catalog.pg_sequences, pg_catalog.pg_class c, pg_catalog.pg_namespace n
					WHERE c.relnamespace = n.oid
						AND schemaname = '$schema' AND sequencename = '$sequence'
						AND c.relname = '$sequence' AND n.nspname='$schema' AND c.relkind = 'S'";
		}

		return $data->selectSet( $sql );
	}

	/**
	 * Return all tables and sequences of a schema,
	 * plus all non existent tables but listed in emaj_group_def with this schema.
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
						c.relkind::TEXT || case when relkind = 'S' or (relkind = 'r' and ${goodTypeConditions}) then '+' else '-' end as relkind,
						pg_catalog.pg_get_userbyid(c.relowner) AS relowner,
						pg_catalog.obj_description(c.oid, 'pg_class') AS relcomment, spcname AS tablespace,
						grpdef_group, grpdef_priority, grpdef_log_dat_tsp, grpdef_log_idx_tsp
						FROM pg_catalog.pg_class c
							LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
							LEFT JOIN emaj.emaj_group_def ON grpdef_schema = nspname AND grpdef_tblseq = c.relname
							LEFT JOIN pg_catalog.pg_tablespace pt ON pt.oid = c.reltablespace
						WHERE c.relkind IN ('r','S','p') AND nspname='$schema'
					UNION
					SELECT 2, grpdef_schema AS nspname, grpdef_tblseq AS relname, '!' AS relkind, NULL, NULL, NULL,
						grpdef_group , grpdef_priority, grpdef_log_dat_tsp, grpdef_log_idx_tsp
						FROM emaj.emaj_group_def
						WHERE grpdef_schema = '$schema' AND grpdef_tblseq NOT IN
							(SELECT relname FROM pg_catalog.pg_class, pg_catalog.pg_namespace
								WHERE relnamespace = pg_namespace.oid AND nspname = '$schema' AND relkind IN ('r','S'))
					ORDER BY 1, relname";
		} else {
			$sql = "SELECT 1, nspname, c.relname,
						c.relkind::TEXT || case when relkind = 'S' or (relkind = 'r' and ${goodTypeConditions}) then '+' else '-' end as relkind,
						pg_catalog.pg_get_userbyid(c.relowner) AS relowner,
						pg_catalog.obj_description(c.oid, 'pg_class') AS relcomment, spcname AS tablespace,
						grpdef_group, grpdef_priority, grpdef_log_schema_suffix, grpdef_emaj_names_prefix, grpdef_log_dat_tsp, grpdef_log_idx_tsp
						FROM pg_catalog.pg_class c
							LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
							LEFT JOIN emaj.emaj_group_def ON grpdef_schema = nspname AND grpdef_tblseq = c.relname
							LEFT JOIN pg_catalog.pg_tablespace pt ON pt.oid = c.reltablespace
						WHERE c.relkind IN ('r','S','p') AND nspname='$schema'
					UNION
					SELECT 2, grpdef_schema AS nspname, grpdef_tblseq AS relname, '!' AS relkind, NULL, NULL, NULL,
						grpdef_group , grpdef_priority, grpdef_log_schema_suffix, grpdef_emaj_names_prefix, grpdef_log_dat_tsp, grpdef_log_idx_tsp
						FROM emaj.emaj_group_def
						WHERE grpdef_schema = '$schema' AND grpdef_tblseq NOT IN
							(SELECT relname FROM pg_catalog.pg_class, pg_catalog.pg_namespace
								WHERE relnamespace = pg_namespace.oid AND nspname = '$schema' AND relkind IN ('r','S'))
					ORDER BY 1, relname";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Get group names already known in the emaj_group and emaj_group_def tables.
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
	 * Get group names already known in the emaj_group table.
	 */
	function getCreatedgroups() {
		global $data;

		$sql = "SELECT group_name
				  FROM emaj.emaj_group
				ORDER BY 1";
		return $data->selectSet($sql);
	}

	/**
	 * Get log schema suffix already known in the emaj_group_def table.
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
	 * Get existing tablespaces.
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
	 * Get existing tablespaces.
	 */
	function getTablesProperties($schema,$tables) {
		global $data;

		$data->clean($schema);
		$data->clean($tables);
		$tablesList = "'" . str_replace(", ", "','", $tables) . "'";

		$sql = "SELECT count(DISTINCT priority) AS nb_priority,
					   count(DISTINCT log_dat_tsp) AS nb_log_dat_tsp,
					   count(DISTINCT log_idx_tsp) AS nb_log_idx_tsp,
					   min(priority) AS min_priority,
					   min(log_dat_tsp) AS min_log_dat_tsp,
					   min(log_idx_tsp) AS min_log_idx_tsp
				FROM (
					SELECT coalesce(rel_priority::TEXT, '') AS priority,
						   coalesce(rel_log_dat_tsp, '') AS log_dat_tsp,
						   coalesce(rel_log_idx_tsp, '') AS log_idx_tsp
					fROM emaj.emaj_relation
					WHERE rel_schema = '$schema'
					  AND rel_tblseq IN ($tablesList)
					  AND rel_kind = 'r'
					  AND upper_inf(rel_time_range)
				) AS rel";

		return $data->selectSet($sql);
	}

	/**
	 * Insert a table or sequence into the emaj_group_def table.
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

		// Get the relkind of the tblseq to process.
		$sql = "SELECT relkind
				FROM pg_catalog.pg_class, pg_catalog.pg_namespace
				WHERE pg_namespace.oid = relnamespace AND relname = '$tblseq' AND nspname = '$schema'";
		$rs = $data->selectSet($sql);
		if ($rs->recordCount() == 1){
			$relkind = $rs->fields['relkind'];
		}else{
			$relkind = "?";
		}

		// Insert the new row into the emaj_group_def table.
		$sql = "INSERT INTO emaj.emaj_group_def (grpdef_schema, grpdef_tblseq, grpdef_group, grpdef_priority,";
		if ($this->getNumEmajVersion() < 30100){			// version < 3.1.0
			$sql .= "grpdef_log_schema_suffix, grpdef_emaj_names_prefix,";
		}
		$sql .= "grpdef_log_dat_tsp, grpdef_log_idx_tsp)
					VALUES ('$schema', '$tblseq', '$group' ";
		if ($priority == '')
			$sql .= ", NULL";
		else
			$sql .= ", $priority";
		if ($this->getNumEmajVersion() < 30100){			// version < 3.1.0
			if ($logSchemaSuffix == '' || $relkind == 'S')
				$sql .= ", NULL";
			else
				$sql .= ", '$logSchemaSuffix'";
			if ($emajNamesPrefix == '' || $relkind == 'S')
				$sql .= ", NULL";
			else
				$sql .= ", '$emajNamesPrefix'";
		}
		if ($logDatTsp == '' || $relkind == 'S')
			$sql .= ", NULL";
		else
			$sql .= ", '$logDatTsp'";
		if ($logIdxTsp == '' || $relkind == 'S')
			$sql .= ", NULL";
		else
			$sql .= ", '$logIdxTsp'";
		$sql .= ")";

		return $data->execute($sql);
	}

	/**
	 * Update a table or sequence into the emaj_group_def table.
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

		// Get the relkind of the tblseq to process.
		$sql = "SELECT relkind
				FROM pg_catalog.pg_class, pg_catalog.pg_namespace
				WHERE pg_namespace.oid = relnamespace AND relname = '$tblseq' AND nspname = '$schema'";
		$rs = $data->selectSet($sql);
		if ($rs->recordCount() == 1){
			$relkind = $rs->fields['relkind'];
		}else{
			$relkind = "?";
		}

		// Update the row in the emaj_group_def table.
		$sql = "UPDATE emaj.emaj_group_def SET
					grpdef_group = '$groupNew'";
		if ($priority == '')
			$sql .= ", grpdef_priority = NULL";
		else
			$sql .= ", grpdef_priority = $priority";
		if ($this->getNumEmajVersion() < 30100){			// version < 3.1.0
			if ($logSchemaSuffix == '' || $relkind == 'S')
				$sql .= ", grpdef_log_schema_suffix = NULL";
			else
				$sql .= ", grpdef_log_schema_suffix = '$logSchemaSuffix'";
			if ($emajNamesPrefix == '' || $relkind == 'S')
				$sql .= ", grpdef_emaj_names_prefix = NULL";
			else
				$sql .= ", grpdef_emaj_names_prefix = '$emajNamesPrefix'";
		}
		if ($logDatTsp == '' || $relkind == 'S')
			$sql .= ", grpdef_log_dat_tsp = NULL";
		else
			$sql .= ", grpdef_log_dat_tsp = '$logDatTsp'";
		if ($logIdxTsp == '' || $relkind == 'S')
			$sql .= ", grpdef_log_idx_tsp = NULL";
		else
			$sql .= ", grpdef_log_idx_tsp = '$logIdxTsp'";
		$sql .= " WHERE grpdef_schema = '$schema' AND grpdef_tblseq = '$tblseq' AND grpdef_group = '$groupOld'";

		return $data->execute($sql);
	}

	/**
	 * Delete a table or sequence from emaj_group_def table.
	 */
	function removeTblSeq($schema,$tblseq,$group) {
		global $data;

		$data->clean($schema);
		$data->clean($tblseq);
		$data->clean($group);

		// Begin transaction.  We do this so that we can ensure only one row is deleted.
		$status = $data->beginTransaction();
		if ($status != 0) {
			$data->rollbackTransaction();
			return -1;
		}

		$sql = "DELETE FROM emaj.emaj_group_def
				WHERE grpdef_schema = '$schema' AND grpdef_tblseq = '$tblseq' AND grpdef_group = '$group'";
		// Delete row.
		$status = $data->execute($sql);

		if ($status != 0 || $data->conn->Affected_Rows() != 1) {
			$data->rollbackTransaction();
			return -2;
		}
		// End transaction.
		return $data->endTransaction();
	}

	/**
	 * Dynamically assign tables to a tables group.
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

		// Build the tables array.
		$tablesArray = "ARRAY['" . str_replace(", ", "','", $tables) . "']";

		// Build the JSON structure.
		if ($priority == '') $priority = 'null';
		if ($logDatTsp == '') $logDatTsp = 'null'; else $logDatTsp = "\"$logDatTsp\"";
		if ($logIdxTsp == '') $logIdxTsp = 'null'; else $logIdxTsp = "\"$logIdxTsp\"";
		$properties = "{\"priority\": $priority, \"log_data_tablespace\": $logDatTsp, \"log_index_tablespace\": $logIdxTsp}";

		$sql = "SELECT emaj.emaj_assign_tables('$schema',$tablesArray,'$group','$properties'::jsonb,'$mark') AS nb_tables";

		return $data->selectField($sql,'nb_tables');
	}

	/**
	 * Dynamically move tables into another tables groups.
	 */
	function moveTables($schema,$tables,$group,$mark) {
		global $data;

		$data->clean($schema);
		$data->clean($tables);
		$data->clean($group);
		$data->clean($mark);

		// Build the tables array.
		$tablesArray = "ARRAY['" . str_replace(", ", "','", $tables) . "']";

		$sql = "SELECT emaj.emaj_move_tables('$schema',$tablesArray,'$group','$mark') AS nb_tables";

		return $data->selectField($sql,'nb_tables');
	}

	/**
	 * Dynamically modify tables in their tables groups.
	 */
	function modifyTables($schema,$tables,$priority,$logDatTsp,$logIdxTsp,$mark) {
		global $data;

		$data->clean($schema);
		$data->clean($tables);
		$data->clean($mark);

		// Build the tables array.
		$tablesArray = "ARRAY['" . str_replace(", ", "','", $tables) . "']";

		// Build the JSON structure.
		$properties = "{";
		if (isset($priority)) {
			$data->clean($priority);
			if ($priority == '') $priority = 'null';
			$properties .= "\"priority\": $priority,";
		}
		if (isset($logDatTsp)) {
			$data->clean($logDatTsp);
			if ($logDatTsp == '') $logDatTsp = 'null'; else $logDatTsp = "\"$logDatTsp\"";
			$properties .= "\"log_data_tablespace\": $logDatTsp,";
		}
		if (isset($logIdxTsp)) {
			$data->clean($logIdxTsp);
			if ($logIdxTsp == '') $logIdxTsp = 'null'; else $logIdxTsp = "\"$logIdxTsp\"";
			$properties .= "\"log_index_tablespace\": $logIdxTsp,";
		}
		$properties .= "}";
		$properties = str_replace(',}', '}', $properties);

		$sql = "SELECT emaj.emaj_modify_tables('$schema',$tablesArray,'$properties'::jsonb,'$mark') AS nb_tables";

		return $data->selectField($sql,'nb_tables');
	}

	/**
	 * Dynamically remove tables from their tables groups.
	 */
	function removeTables($schema,$tables,$mark) {
		global $data;

		$data->clean($schema);
		$data->clean($tables);
		$data->clean($mark);

		// Build the tables array.
		$tablesArray = "ARRAY['" . str_replace(", ", "','", $tables) . "']";

		$sql = "SELECT emaj.emaj_remove_tables('$schema',$tablesArray,'$mark') AS nb_tables";

		return $data->selectField($sql,'nb_tables');
	}

	/**
	 * Dynamically assign sequences to a tables group.
	 */
	function assignSequences($schema,$sequences,$group,$mark) {
		global $data;

		$data->clean($schema);
		$data->clean($sequences);
		$data->clean($group);
		$data->clean($mark);

		// Build the sequences array.
		$sequencesArray = "ARRAY['" . str_replace(", ", "','", $sequences) . "']";

		$sql = "SELECT emaj.emaj_assign_sequences('$schema',$sequencesArray,'$group','$mark') AS nb_sequences";

		return $data->selectField($sql,'nb_sequences');
	}

	/**
	 * Dynamically move sequences into another tables groups.
	 */
	function moveSequences($schema,$sequences,$group,$mark) {
		global $data;

		$data->clean($schema);
		$data->clean($sequences);
		$data->clean($group);
		$data->clean($mark);

		// Build the sequences array.
		$sequencesArray = "ARRAY['" . str_replace(", ", "','", $sequences) . "']";

		$sql = "SELECT emaj.emaj_move_sequences('$schema',$sequencesArray,'$group','$mark') AS nb_sequences";

		return $data->selectField($sql,'nb_sequences');
	}

	/**
	 * Dynamically remove sequences from their tables groups.
	 */
	function removeSequences($schema,$sequences,$mark) {
		global $data;

		$data->clean($schema);
		$data->clean($sequences);
		$data->clean($mark);

		// Build the sequences array
		$sequencesArray = "ARRAY['" . str_replace(", ", "','", $sequences) . "']";

		$sql = "SELECT emaj.emaj_remove_sequences('$schema',$sequencesArray,'$mark') AS nb_sequences";

		return $data->selectField($sql,'nb_sequences');
	}

	/**
	 * Determine whether or not a group name is valid as a new empty group.
	 * Returns 1 if the group name is not already known, 0 otherwise.
	 */
	function isNewEmptyGroupValid($group) {

		global $data;

		$data->clean($group);

		if ($this->getNumEmajVersion() >= 40000){	// version >= 4.0.0
			$sql = "SELECT CASE WHEN EXISTS(SELECT 1 FROM emaj.emaj_group WHERE group_name = '$group') THEN 0 ELSE 1 END AS result";
		} else {
			$sql = "SELECT CASE WHEN
					(SELECT COUNT(*) FROM emaj.emaj_group WHERE group_name = '$group') +
					(SELECT COUNT(*) FROM emaj.emaj_group_def WHERE grpdef_group = '$group')
					= 0 THEN 1 ELSE 0 END AS result";
		}

		return $data->selectField($sql,'result');
	}

	/**
	 * Check in the emaj_group_def table the configuration of a new group to create.
	 * The function is not called anymore since E-Maj version 3.2+.
	 */
	function checkConfNewGroup($group) {
		global $data, $lang;

		$data->clean($group);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$group)."']";

		$sql = "SELECT chk_severity,
				CASE chk_msg_type
					WHEN  1 THEN format('" . $data->clean($lang['strcheckconfgroups01']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN  2 THEN format('" . $data->clean($lang['strcheckconfgroups02']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN  3 THEN format('" . $data->clean($lang['strcheckconfgroups03']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN  4 THEN format('" . $data->clean($lang['strcheckconfgroups04']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN  5 THEN format('" . $data->clean($lang['strcheckconfgroups05']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 10 THEN format('" . $data->clean($lang['strcheckconfgroups10']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 11 THEN format('" . $data->clean($lang['strcheckconfgroups11']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 12 THEN format('" . $data->clean($lang['strcheckconfgroups12']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 13 THEN format('" . $data->clean($lang['strcheckconfgroups13']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 20 THEN format('" . $data->clean($lang['strcheckconfgroups20']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 21 THEN format('" . $data->clean($lang['strcheckconfgroups21']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 22 THEN format('" . $data->clean($lang['strcheckconfgroups22']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 30 THEN format('" . $data->clean($lang['strcheckconfgroups30']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 31 THEN format('" . $data->clean($lang['strcheckconfgroups31']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 32 THEN format('" . $data->clean($lang['strcheckconfgroups32']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 33 THEN format('" . $data->clean($lang['strcheckconfgroups33']) . "', chk_group, chk_schema, chk_tblseq)
				END as chk_message
			FROM emaj._check_conf_groups ($groupsArray)";

		return $data->selectSet($sql);
	}

	/**
	 * Check in the emaj_group_def table the configuration of one or serveral existing groups to alter.
	 * The function is not called anymore since E-Maj version 3.2+.
	 */
	function checkConfExistingGroups($groups) {
		global $data, $lang;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		$sql = "SELECT chk_severity,
				CASE chk_msg_type
					WHEN  1 THEN format('" . $data->clean($lang['strcheckconfgroups01']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN  2 THEN format('" . $data->clean($lang['strcheckconfgroups02']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN  3 THEN format('" . $data->clean($lang['strcheckconfgroups03']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN  4 THEN format('" . $data->clean($lang['strcheckconfgroups04']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN  5 THEN format('" . $data->clean($lang['strcheckconfgroups05']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 10 THEN format('" . $data->clean($lang['strcheckconfgroups10']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 11 THEN format('" . $data->clean($lang['strcheckconfgroups11']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 12 THEN format('" . $data->clean($lang['strcheckconfgroups12']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 13 THEN format('" . $data->clean($lang['strcheckconfgroups13']) . "', chk_group, chk_schema, chk_tblseq, chk_extra_data)
					WHEN 20 THEN format('" . $data->clean($lang['strcheckconfgroups20']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 21 THEN format('" . $data->clean($lang['strcheckconfgroups21']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 22 THEN format('" . $data->clean($lang['strcheckconfgroups22']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 30 THEN format('" . $data->clean($lang['strcheckconfgroups30']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 31 THEN format('" . $data->clean($lang['strcheckconfgroups31']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 32 THEN format('" . $data->clean($lang['strcheckconfgroups32']) . "', chk_group, chk_schema, chk_tblseq)
					WHEN 33 THEN format('" . $data->clean($lang['strcheckconfgroups33']) . "', chk_group, chk_schema, chk_tblseq)
				END as chk_message
			FROM emaj._check_conf_groups ($groupsArray), emaj.emaj_group
	        WHERE 	chk_group = group_name
				AND ((group_is_rollbackable AND chk_severity <= 2)
				  OR (NOT group_is_rollbackable AND chk_severity <= 1))";

		return $data->selectSet($sql);
	}

	/**
	 * Check that a JSON structure representing tables groups configuration is valid, before importing it.
	 */
	function checkJsonGroupsConf($json) {
		global $data, $lang;

		$data->clean($json);

		# error messages 224 and 225 disappeared in 4.0+
		$sql = "SELECT
				rpt_severity,
				CASE rpt_msg_type
					WHEN 201 THEN '" . $data->clean($lang['strcheckjsongroupsconf201']) . "'
					WHEN 202 THEN format('" . $data->clean($lang['strcheckjsongroupsconf202']) . "', rpt_text_var_1)
					WHEN 210 THEN format('" . $data->clean($lang['strcheckjsongroupsconf210']) . "', rpt_int_var_1)
					WHEN 211 THEN format('" . $data->clean($lang['strcheckjsongroupsconf211']) . "', rpt_text_var_1, rpt_int_var_1)
					WHEN 212 THEN format('" . $data->clean($lang['strcheckjsongroupsconf212']) . "', rpt_text_var_1)
					WHEN 220 THEN format('" . $data->clean($lang['strcheckjsongroupsconf220']) . "', rpt_text_var_1, rpt_int_var_1)
					WHEN 221 THEN format('" . $data->clean($lang['strcheckjsongroupsconf221']) . "', rpt_text_var_1, rpt_int_var_1)
					WHEN 222 THEN format('" . $data->clean($lang['strcheckjsongroupsconf222']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 223 THEN format('" . $data->clean($lang['strcheckjsongroupsconf223']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 224 THEN format('" . $data->clean($lang['strcheckjsongroupsconf224']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_int_var_1)
					WHEN 225 THEN format('" . $data->clean($lang['strcheckjsongroupsconf225']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
					WHEN 226 THEN format('" . $data->clean($lang['strcheckjsongroupsconf226']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_int_var_1)
					WHEN 227 THEN format('" . $data->clean($lang['strcheckjsongroupsconf227']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3)
					WHEN 230 THEN format('" . $data->clean($lang['strcheckjsongroupsconf230']) . "', rpt_text_var_1, rpt_int_var_1)
					WHEN 231 THEN format('" . $data->clean($lang['strcheckjsongroupsconf231']) . "', rpt_text_var_1, rpt_int_var_1)
					WHEN 232 THEN format('" . $data->clean($lang['strcheckjsongroupsconf232']) . "', rpt_text_var_1, rpt_text_var_2, rpt_text_var_3, rpt_text_var_4)
                    ELSE 'Message not decoded (' || rpt_msg_type || ')'
				END as rpt_message
			FROM emaj._check_json_groups_conf('$json'::json)
			ORDER BY rpt_msg_type, rpt_text_var_1, rpt_text_var_2, rpt_text_var_3";

		return $data->selectSet($sql);
	}

	/**
	 * Create a group, and comment if requested.
	 */
	function createGroup($group,$isRollbackable,$isEmpty,$comment) {
		global $data;

		$data->clean($group);
		$data->clean($comment);

		$boolIsRollbackable = ($isRollbackable) ? 'true' : 'false';

		if ($isEmpty && $this->getNumEmajVersion() < 40000) {
			$sql = "SELECT emaj.emaj_create_group('$group',$boolIsRollbackable,true) AS nbtblseq";
		} elseif ($this->getNumEmajVersion() < 40600) {
			$sql = "SELECT emaj.emaj_create_group('$group',$boolIsRollbackable) AS nbtblseq";
		} else {
			$sql = "SELECT emaj.emaj_create_group('$group',$boolIsRollbackable,'$comment') AS nbtblseq";
		}
		$rt = $data->execute($sql);

		if ($this->getNumEmajVersion() < 40600 && $comment <> '') {
			$sql = "SELECT emaj.emaj_comment_group('$group','$comment')";
			$data->execute($sql);
		}

		return $rt;
	}

	/**
	 * Drop a group.
	 */
	function dropGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT emaj.emaj_drop_group('$group') AS nbtblseq";

		return $data->execute($sql);
	}

	/**
	 * Drop several groups at once.
	 */
	function dropGroups($groups) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		$sql = "SELECT emaj.emaj_drop_group(group_name) FROM emaj.emaj_group
				  WHERE group_name = ANY ($groupsArray)";

		return $data->execute($sql);
	}

	/**
	 * Erase all traces of a dropped group from the tables groups histories.
	 */
	function forgetGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT emaj.emaj_forget_group('$group') AS deleted_traces";

		return $data->execute($sql);
	}

	/**
	 * Check that the group can be altered.
	 * If the group is not IDLE, it performs checks on operations that will be performed.
	 * Returns 1 if OK, else 0.
	 */
	function checkAlterGroup($group) {
		global $data;

		$data->clean($group);

		// Get the group's state.
		$sql = "SELECT CASE WHEN group_is_logging THEN 1 ELSE 0 END AS is_logging
				FROM emaj.emaj_group
				WHERE group_name = '$group'";
		$isLogging = $data->selectField($sql,'is_logging');
		// The group is idle, so return immediately.
		if (! $isLogging) { return 1; }

		// The group is logging.
		// Check that no table or sequence would be repaired for the group.
		$sql = "SELECT count(*) as nb_errors FROM emaj._verify_groups(ARRAY['$group'], false)";
		if ($data->selectField($sql,'nb_errors') > 0 ) { return 0; }

		// all checks are ok
		return 1;
	}

	/**
	 * Alter a group.
	 */
	function alterGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		if ($mark == '') {
			$sql = "SELECT emaj.emaj_alter_group('$group') AS nbtblseq";
		} else {
			$sql = "SELECT emaj.emaj_alter_group('$group', '$mark') AS nbtblseq";
		}
		return $data->execute($sql);
	}

	/**
	 * Alter several groups.
	 */
	function alterGroups($groups,$mark) {
		global $data;

		$data->clean($groups);
		$data->clean($mark);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		if ($mark == '') {
			$sql = "SELECT emaj.emaj_alter_groups($groupsArray) AS nbtblseq";
		} else {
			$sql = "SELECT emaj.emaj_alter_groups($groupsArray, '$mark') AS nbtblseq";
		}

		return $data->execute($sql);
	}

	/**
	 * Set a comment for a group.
	 */
	function setCommentGroup($group,$comment) {
		global $data;

		$data->clean($group);
		$data->clean($comment);

		if ($comment <> '') {
			$sql = "SELECT emaj.emaj_comment_group('$group','$comment')";
		} else {
			$sql = "SELECT emaj.emaj_comment_group('$group',NULL)";
		}

		return $data->execute($sql);
	}

	/**
	 * Compute the number of active mark in a group.
	 */
	function nbActiveMarkGroup($group) {

		global $data;

		$data->clean($group);

		$sql = "SELECT COUNT(*) as result FROM emaj.emaj_mark WHERE mark_group = '$group'";

		return $data->selectField($sql,'result');
	}

	/**
	 * Start a group.
	 */
	function startGroup($group,$mark,$resetLog) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		if ($resetLog){
			$sql = "SELECT emaj.emaj_start_group('$group','$mark') AS nbtblseq";
		}else{
			$sql = "SELECT emaj.emaj_start_group('$group','$mark',false) AS nbtblseq";
		}

		return $data->execute($sql);
	}

	/**
	 * Start several groups.
	 */
	function startGroups($groups,$mark,$resetLog) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);

		if ($resetLog){
			$sql = "SELECT emaj.emaj_start_groups($groupsArray,'$mark') AS nbtblseq";
		}else{
			$sql = "SELECT emaj.emaj_start_groups($groupsArray,'$mark',false) AS nbtblseq";
		}

		return $data->execute($sql);
	}

	/**
	 * Stop a group.
	 */
	function stopGroup($group,$mark,$forceStop) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		if ($forceStop){
			$sql = "SELECT emaj.emaj_force_stop_group('$group') AS nbtblseq";
		}else{
			if ($mark == ""){
				$sql = "SELECT emaj.emaj_stop_group('$group') AS nbtblseq";
			}else{
				$sql = "SELECT emaj.emaj_stop_group('$group','$mark') AS nbtblseq";
			}
		}

		return $data->execute($sql);
	}

	/**
	 * Stop several groups at once.
	 */
	function stopGroups($groups,$mark) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);
			if ($mark == ""){
			$sql = "SELECT emaj.emaj_stop_groups($groupsArray) AS nbtblseq";
		}else{
			$sql = "SELECT emaj.emaj_stop_groups($groupsArray,'$mark') AS nbtblseq";
		}

		return $data->execute($sql);
	}

	/**
	 * Reset a group.
	 */
	function resetGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT emaj.emaj_reset_group('$group') AS nbtblseq";

		return $data->execute($sql);
	}

	/**
	 * Reset several groups at once.
	 */
	function resetGroups($groups) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		$sql = "SELECT emaj.emaj_reset_group(group_name) FROM emaj.emaj_group
				  WHERE group_name = ANY ($groupsArray)";

		return $data->execute($sql);
	}

	/**
	 * Protect a group.
	 */
	function protectGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT emaj.emaj_protect_group('$group') AS status";

		return $data->execute($sql);
	}

	/**
	 * Protect several groups at once.
	 */
	function protectGroups($groups) {
		global $data;

		$data->clean($groups);
		$groupsList = str_replace(", ", "','", $groups);

		$sql = "SELECT sum(emaj.emaj_protect_group(group_name))  AS nb_group FROM emaj.emaj_group WHERE group_name IN ('$groupsList')";

		return $data->selectField($sql, 'nb_group');
	}

	/**
	 * Unprotect a group.
	 */
	function unprotectGroup($group) {
		global $data;

		$data->clean($group);

		$sql = "SELECT emaj.emaj_unprotect_group('$group') AS status";

		return $data->execute($sql);
	}

	/**
	 * Unprotect several groups at once.
	 */
	function unprotectGroups($groups) {
		global $data;

		$data->clean($groups);
		$groupsList = str_replace(", ", "','", $groups);

		$sql = "SELECT sum(emaj.emaj_unprotect_group(group_name))  AS nb_group FROM emaj.emaj_group WHERE group_name IN ('$groupsList')";

		return $data->selectField($sql, 'nb_group');
	}

	/**
	 * Set a mark for a group and comments if requested.
	 */
	function setMarkGroup($group,$mark,$comment) {
		global $data;

		$data->clean($group);
		$data->clean($mark);
		$data->clean($comment);

		if ($this->getNumEmajVersion() < 40600) {
			$sql = "SELECT emaj.emaj_set_mark_group('$group','$mark') AS nbtblseq";
		} else {
			$sql = "SELECT emaj.emaj_set_mark_group('$group','$mark','$comment') AS nbtblseq";
		}
		$rt = $data->execute($sql);

		if ($this->getNumEmajVersion() < 40600 && $comment <> '') {
			$sql = "SELECT emaj.emaj_comment_mark_group('$group','$mark','$comment')";
			$data->execute($sql);
		}

		return $rt;
	}

	/**
	 * Set a mark for several groups and comments if requested.
	 */
	function setMarkGroups($groups,$mark,$comment) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ', "','", $groups)."']";
		$data->clean($mark);
		$data->clean($comment);

		$sql = "SELECT emaj.emaj_set_mark_groups($groupsArray,'$mark') AS nbtblseq";
		$rt = $data->execute($sql);

		if ($comment <> '') {
		// Set a comment for each group of the groups list
			$groupsA = explode(', ', $groups);
			foreach($groupsA as $group) {
				$sql = "SELECT emaj.emaj_comment_mark_group('$group','$mark','$comment')";
				$data->execute($sql);
			}
		}

		return $rt;
	}

	/**
	 * Get properties of one mark.
	 */
	function getMark($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT mark_name, mark_group, coalesce(mark_comment, '') as mark_comment
				FROM emaj.emaj_mark
				WHERE mark_group = '$group' AND mark_name = '$mark'";
		return $data->selectSet($sql);
	}

	/**
	 * Set a comment for a mark of a group.
	 */
	function setCommentMarkGroup($group,$mark,$comment) {
		global $data;

		$data->clean($group);
		$data->clean($mark);
		$data->clean($comment);

		if ($comment <> '') {
			$sql = "SELECT emaj.emaj_comment_mark_group('$group','$mark','$comment')";
		} else {
			$sql = "SELECT emaj.emaj_comment_mark_group('$group','$mark',NULL)";
		}

		return $data->execute($sql);
	}

	/**
	 * Protect a mark for a group.
	 */
	function protectMarkGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT emaj.emaj_protect_mark_group('$group','$mark') AS status";

		return $data->execute($sql);
	}

	/**
	 * Unprotect a mark for a group.
	 */
	function unprotectMarkGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT emaj.emaj_unprotect_mark_group('$group','$mark') AS status";

		return $data->execute($sql);
	}

	/**
	 * Delete a mark for a group.
	 */
	function deleteMarkGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT emaj.emaj_delete_mark_group('$group','$mark')";

		return $data->execute($sql);
	}

	/**
	 * Delete all marks before a mark for a group.
	 */
	function deleteBeforeMarkGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT emaj.emaj_delete_before_mark_group('$group','$mark') as nbmark";

		return $data->selectField($sql,'nbmark');
	}

	/**
	 * Rename a mark for a group.
	 */
	function renameMarkGroup($group,$mark,$newMark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);
		$data->clean($newMark);

		$sql = "SELECT emaj.emaj_rename_mark_group('$group','$mark','$newMark')";

		return $data->execute($sql);
	}

	/**
	 * Return the list of marks usable to rollback a group.
	 */
	function getRollbackMarkGroup($group) {

		global $data;

		$data->clean($group);

		if ($this->getNumEmajVersion() >= 40400){	// version 4.4+
			$sql = "SELECT mark_name, to_char(time_tx_timestamp,'{$this->tsFormat}') as mark_datetime, mark_is_rlbk_protected
					FROM emaj.emaj_mark
						JOIN emaj.emaj_time_stamp ON (time_id = mark_time_id)
						JOIN emaj.emaj_log_session ON (lses_group = mark_group AND lses_time_range @> mark_time_id)
					WHERE mark_group = '$group'
					AND upper_inf(lses_time_range)
					ORDER BY mark_time_id DESC";
		} else {
			$sql = "SELECT mark_name, to_char(time_tx_timestamp,'{$this->tsFormat}') as mark_datetime, mark_is_rlbk_protected
					FROM emaj.emaj_mark, emaj.emaj_time_stamp
					WHERE mark_group = '$group'
					AND NOT mark_is_deleted
					AND time_id = mark_time_id
					ORDER BY mark_time_id DESC";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Determine whether or not a mark name for a group is ACTIVE.
	 * Returns 1 if the mark name is known and belongs to a group currently in logging state,
	 * Returns 0 otherwise.
	 */
	function isMarkActiveGroup($group,$mark) {

		global $data;

		$data->clean($group);
		$data->clean($mark);

		// Check the mark is active.
		if ($this->getNumEmajVersion() >= 40400){	// version 4.4+
			$sql = "SELECT CASE WHEN EXISTS
					(SELECT 0 FROM emaj.emaj_mark
								   JOIN emaj.emaj_log_session ON (lses_group = mark_group AND lses_time_range @> mark_time_id)
					WHERE mark_group = '$group'
					  AND mark_name = '$mark'
					  AND upper_inf(lses_time_range)
					) THEN 1 ELSE 0 END AS is_active";
		} else {
			$sql = "SELECT CASE WHEN EXISTS
					(SELECT 0 FROM emaj.emaj_mark
					WHERE mark_group = '$group' AND mark_name = '$mark' AND NOT mark_is_deleted
					) THEN 1 ELSE 0 END AS is_active";
		}

		return $data->selectField($sql,'is_active');
	}

	/**
	 * Determine whether or not a mark name is valid as a mark to rollback to for a group.
	 * Returns 1 if:
	 *   - the mark name is known and in ACTIVE state and
	 *   - no intermediate protected mark would be covered by the rollback,
	 * Retuns 0 otherwise.
	 */
	function isRollbackMarkValidGroup($group,$mark) {

		global $data;

		$data->clean($group);
		$data->clean($mark);

		// Check the mark is active (i.e. not deleted).
		$result = $this->isMarkActiveGroup($group,$mark);

		if ($result == 1) {
			// the mark is active, so now check there is no intermediate protected mark
			$sql = "SELECT CASE WHEN
					(SELECT count(*) FROM emaj.emaj_mark
					WHERE mark_group = '$group' AND mark_time_id >
						(SELECT mark_time_id FROM emaj.emaj_mark
						WHERE mark_group = '$group' AND mark_name = '$mark'
						) AND mark_is_rlbk_protected
					) = 0 THEN 1 ELSE 0 END AS result";
			$result = $data->selectField($sql,'result');
		}

		return $result;
	}

	/**
	 * Return information about all alter_group operations that have been executed after a mark set for one or several groups.
	 * The function is called when emaj version >= 2.1.
	 */
	function getAlterAfterMarkGroups($groups,$mark,$emajlang) {

		global $data;

		$data->clean($groups);
		$data->clean($mark);

		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$firstGroup = substr($groups, 0, strpos($groups.',', ','));

		// Look at the alter group operations executed after the mark.
		if ($this->getNumEmajVersion() >= 40000){	// version >= 4.0.0
			$sql = "SELECT to_char(time_tx_timestamp,'{$this->tsFormat}') as time_tx_timestamp,
					CASE
					WHEN rlchg_change_kind = 'REMOVE_TABLE' THEN
						format('{$emajlang['stralteredremovetbl']}', rlchg_schema, rlchg_tblseq, rlchg_group)
					WHEN rlchg_change_kind = 'REMOVE_SEQUENCE' THEN
						format('{$emajlang['stralteredremoveseq']}', rlchg_schema, rlchg_tblseq, rlchg_group)
					WHEN rlchg_change_kind = 'REPAIR_TABLE' THEN
						format('{$emajlang['stralteredrepairtbl']}', rlchg_schema, rlchg_tblseq)
					WHEN rlchg_change_kind = 'CHANGE_LOG_DATA_TABLESPACE' THEN
						format('{$emajlang['stralteredchangetbllogdatatsp']}', rlchg_schema, rlchg_tblseq)
					WHEN rlchg_change_kind = 'CHANGE_LOG_INDEX_TABLESPACE' THEN
						format('{$emajlang['stralteredchangetbllogindextsp']}', rlchg_schema, rlchg_tblseq)
					WHEN rlchg_change_kind = 'CHANGE_PRIORITY' THEN
						format('{$emajlang['stralteredchangerelpriority']}', rlchg_schema, rlchg_tblseq)
					WHEN rlchg_change_kind = 'CHANGE_IGNORED_TRIGGERS' THEN
						format('{$emajlang['stralteredchangeignoredtriggers']}', rlchg_schema, rlchg_tblseq)
					WHEN rlchg_change_kind = 'MOVE_TABLE' THEN
						format('{$emajlang['stralteredmovetbl']}', rlchg_schema, rlchg_tblseq, rlchg_group, rlchg_new_group)
					WHEN rlchg_change_kind = 'MOVE_SEQUENCE' THEN
						format('{$emajlang['stralteredmoveseq']}', rlchg_schema, rlchg_tblseq, rlchg_group, rlchg_new_group)
					WHEN rlchg_change_kind = 'ADD_TABLE' THEN
						format('{$emajlang['stralteredaddtbl']}', rlchg_schema, rlchg_tblseq, rlchg_group)
					WHEN rlchg_change_kind = 'ADD_SEQUENCE' THEN
						format('{$emajlang['stralteredaddseq']}', rlchg_schema, rlchg_tblseq, rlchg_group)
					END AS altr_action,
					CASE WHEN rlchg_change_kind IN ('ADD_TABLE', 'ADD_SEQUENCE', 'REMOVE_TABLE', 'REMOVE_SEQUENCE',
													'REPAIR_TABLE', 'MOVE_TABLE', 'MOVE_SEQUENCE', 'CHANGE_LOG_DATA_TABLESPACE',
													'CHANGE_LOG_INDEX_TABLESPACE', 'CHANGE_PRIORITY', 'CHANGE_IGNORED_TRIGGERS')
						THEN false ELSE true END AS altr_auto_rolled_back
					FROM emaj.emaj_relation_change, emaj.emaj_time_stamp
					WHERE time_id = rlchg_time_id
						AND (rlchg_group = ANY ($groupsArray) OR rlchg_new_group = ANY ($groupsArray))
						AND rlchg_time_id >
							(SELECT mark_time_id FROM emaj.emaj_mark WHERE mark_group = '$firstGroup' AND mark_name = '$mark')
						AND rlchg_change_kind IN   ('ADD_TABLE', 'ADD_SEQUENCE', 'REMOVE_TABLE', 'REMOVE_SEQUENCE',
													'REPAIR_TABLE', 'MOVE_TABLE', 'MOVE_SEQUENCE', 'CHANGE_LOG_DATA_TABLESPACE',
													'CHANGE_LOG_INDEX_TABLESPACE', 'CHANGE_PRIORITY', 'CHANGE_IGNORED_TRIGGERS')
					ORDER BY time_tx_timestamp, rlchg_schema, rlchg_tblseq, rlchg_change_kind";
		} else {
			$sql = "SELECT to_char(time_tx_timestamp,'{$this->tsFormat}') as time_tx_timestamp, altr_step,
					CASE
					WHEN altr_step = 'REMOVE_TBL' THEN
						format('{$emajlang['stralteredremovetbl']}', altr_schema, altr_tblseq, altr_group)
					WHEN altr_step = 'REMOVE_SEQ' THEN
						format('{$emajlang['stralteredremoveseq']}', altr_schema, altr_tblseq, altr_group)
					WHEN altr_step = 'REPAIR_TBL' THEN
						format('{$emajlang['stralteredrepairtbl']}', altr_schema, altr_tblseq)
					WHEN altr_step = 'REPAIR_SEQ' THEN
						format('{$emajlang['stralteredrepairseq']}', altr_schema, altr_tblseq)
					WHEN altr_step = 'CHANGE_TBL_LOG_SCHEMA' THEN
						format('{$emajlang['stralteredchangetbllogschema']}', altr_schema, altr_tblseq)
					WHEN altr_step = 'CHANGE_TBL_NAMES_PREFIX' THEN
						format('{$emajlang['stralteredchangetblnamesprefix']}', altr_schema, altr_tblseq)
					WHEN altr_step = 'CHANGE_TBL_LOG_DATA_TSP' THEN
						format('{$emajlang['stralteredchangetbllogdatatsp']}', altr_schema, altr_tblseq)
					WHEN altr_step = 'CHANGE_TBL_LOG_INDEX_TSP' THEN
						format('{$emajlang['stralteredchangetbllogindextsp']}', altr_schema, altr_tblseq)
					WHEN altr_step = 'CHANGE_REL_PRIORITY' THEN
						format('{$emajlang['stralteredchangerelpriority']}', altr_schema, altr_tblseq)
					WHEN altr_step = 'MOVE_TBL' THEN
						format('{$emajlang['stralteredmovetbl']}', altr_schema, altr_tblseq, altr_group, altr_new_group)
					WHEN altr_step = 'MOVE_SEQ' THEN
						format('{$emajlang['stralteredmoveseq']}', altr_schema, altr_tblseq, altr_group, altr_new_group)
					WHEN altr_step = 'ADD_TBL' THEN
						format('{$emajlang['stralteredaddtbl']}', altr_schema, altr_tblseq, altr_group)
					WHEN altr_step = 'ADD_SEQ' THEN
						format('{$emajlang['stralteredaddseq']}', altr_schema, altr_tblseq, altr_group)
					END AS altr_action,
					CASE WHEN altr_step IN ('ADD_TBL', 'ADD_SEQ', 'REMOVE_TBL', 'REMOVE_SEQ', 'REPAIR_TBL', 'REPAIR_SEQ', 'MOVE_TBL', 'MOVE_SEQ',
											'CHANGE_TBL_LOG_SCHEMA', 'CHANGE_TBL_NAMES_PREFIX', 'CHANGE_TBL_LOG_DATA_TSP',
											'CHANGE_TBL_LOG_INDEX_TSP', 'CHANGE_REL_PRIORITY')
						THEN false ELSE true END AS altr_auto_rolled_back
					FROM emaj.emaj_alter_plan, emaj.emaj_time_stamp
					WHERE time_id = altr_time_id
						AND altr_group = ANY ($groupsArray)
						AND altr_time_id >
							(SELECT mark_time_id FROM emaj.emaj_mark WHERE mark_group = '$firstGroup' AND mark_name = '$mark')
						AND altr_rlbk_id IS NULL
						AND altr_step IN ('ADD_TBL', 'ADD_SEQ', 'REMOVE_TBL', 'REMOVE_SEQ', 'REPAIR_TBL', 'REPAIR_SEQ', 'MOVE_TBL', 'MOVE_SEQ',
											'CHANGE_TBL_LOG_SCHEMA', 'CHANGE_TBL_NAMES_PREFIX', 'CHANGE_TBL_LOG_DATA_TSP',
											'CHANGE_TBL_LOG_INDEX_TSP', 'CHANGE_REL_PRIORITY')
					ORDER BY time_tx_timestamp, altr_schema, altr_tblseq, altr_step";
		}
		return $data->selectSet($sql);
	}

	/**
	 * Rollback a group to a mark.
	 * It returns a set of messages.
	 */
	function rollbackGroup($group,$mark,$isLogged,$comment) {
		global $data;

		$data->clean($group);
		$data->clean($mark);
		$data->clean($comment);

		if ($comment <> '') {
			if ($isLogged){
				$sql = "SELECT * FROM emaj.emaj_logged_rollback_group('$group','$mark',true,'$comment')";
			} else {
				$sql = "SELECT * FROM emaj.emaj_rollback_group('$group','$mark',true,'$comment')";
			}
		} else {
			if ($isLogged){
				$sql = "SELECT * FROM emaj.emaj_logged_rollback_group('$group','$mark',true)";
			} else {
				$sql = "SELECT * FROM emaj.emaj_rollback_group('$group','$mark',true)";
			}
		}

		return $data->selectSet($sql);
	}

	/**
	 * Rollback asynchronously one or several groups to a mark, using a single session.
	 */
	function asyncRollbackGroups($groups,$mark,$isLogged,$psqlExe,$tempDir,$isMulti,$comment) {
		global $data, $misc;

		$data->clean($groups);
		$data->clean($mark);
		$data->clean($psqlExe);
		$data->clean($tempDir);
		$data->clean($comment);

		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		// Initialize the rollback operation and get its rollback id
		$isL = $isLogged ? 'true' : 'false';
		$isM = $isMulti ? 'true' : 'false';
		if ($comment <> '') {
			$sql1 = "SELECT emaj._rlbk_init($groupsArray, '$mark', $isL, 1, $isM, true, '$comment') as rlbk_id";
		} else {
			$sql1 = "SELECT emaj._rlbk_init($groupsArray, '$mark', $isL, 1, $isM, true) as rlbk_id";
		}
		$rlbkId = $data->selectField($sql1,'rlbk_id');

		// Build the psql report file name, the SQL command and submit the rollback execution asynchronously
		$sql2 = "SELECT emaj._rlbk_async($rlbkId, $isM}";
		$psqlReport = "rlbk_{$rlbkId}_report";
		$this->execPsqlInBackground($psqlExe, $sql2, $tempDir, $psqlReport);

		return $rlbkId;
	}

	/**
	 * Execute an external psql command in background.
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
	 * Return the list of marks usable to rollback a groups array.
	 */
	function getRollbackMarkGroups($groups) {

		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";

		if ($this->getNumEmajVersion() >= 40400){	// version 4.4+
			$sql = "SELECT t.mark_name, t.mark_datetime, t.mark_is_rlbk_protected
					FROM (SELECT mark_name, to_char(time_tx_timestamp,'{$this->tsFormat}') as mark_datetime, mark_is_rlbk_protected,
								 array_agg (mark_group) AS groups
						FROM emaj.emaj_mark
							 JOIN emaj.emaj_group ON (mark_group = group_name)
							 JOIN emaj.emaj_time_stamp ON (time_id = mark_time_id)
							 JOIN emaj.emaj_log_session ON (lses_group = mark_group AND lses_time_range @> mark_time_id)
						WHERE group_is_rollbackable
						  AND upper_inf(lses_time_range)
						GROUP BY 1,2,3) AS t
					WHERE t.groups @> $groupsArray
					ORDER BY t.mark_datetime DESC";
		} else {
			$sql = "SELECT t.mark_name, t.mark_datetime, t.mark_is_rlbk_protected
					FROM (SELECT mark_name, to_char(time_tx_timestamp,'{$this->tsFormat}') as mark_datetime, mark_is_rlbk_protected,
								array_agg (mark_group) AS groups
						FROM emaj.emaj_mark,emaj.emaj_group,
							emaj.emaj_time_stamp
						WHERE mark_group = group_name AND time_id = mark_time_id
							AND NOT mark_is_deleted AND group_is_rollbackable GROUP BY 1,2,3) AS t
					WHERE t.groups @> $groupsArray
					ORDER BY t.mark_datetime DESC";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Get timestamp of the youngest protected mark of a groups list.
	 */
	function getYoungestProtectedMarkTimestamp($groups) {

		global $data;

		$data->clean($groups);
		$groups="'".str_replace(', ',"','",$groups)."'";

		$sql = "SELECT max(time_tx_timestamp) AS youngest_mark_datetime
				  FROM emaj.emaj_mark , emaj.emaj_time_stamp
				  WHERE time_id = mark_time_id
					AND mark_group IN ($groups) AND mark_is_rlbk_protected";

		return $data->selectField($sql,'youngest_mark_datetime');
	}

	/**
	 * Get the list of protected groups from a groups list.
	 */
	function getProtectedGroups($groups) {

		global $data;

		$data->clean($groups);
		$groups="'".str_replace(', ',"','",$groups)."'";

		$sql = "SELECT count(*) AS nb_groups, string_agg(group_name, ', ') AS groups_list
					FROM emaj.emaj_group
					WHERE group_name IN ($groups)
						AND group_is_rlbk_protected";

		return $data->selectSet($sql);
	}

	/**
	 * Determine whether or not a mark name is valid as a mark to rollback to for a groups array.
	 * Returns 1 if:
	 *   - the mark name is known and belongs to groups in logging state,
	 *   - no intermediate protected mark for any group would be covered by the rollback,
	 * Retuns 0 otherwise.
	 */
	function isRollbackMarkValidGroups($groups,$mark) {

		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);

		$nbGroups = substr_count($groupsArray,',') + 1;

		// Check the mark is active (i.e. belongs to logging groups).
		if ($this->getNumEmajVersion() >= 40400){	// version 4.4+
			$sql = "SELECT CASE WHEN
					(SELECT COUNT(*)
						FROM emaj.emaj_mark
							 JOIN emaj.emaj_group ON (mark_group = group_name)
							 JOIN emaj.emaj_log_session ON (lses_group = mark_group AND lses_time_range @> mark_time_id)
						WHERE mark_group = ANY ($groupsArray)
						  AND group_is_rollbackable
						  AND mark_name = '$mark'
						  AND upper_inf(lses_time_range)
					) = $nbGroups THEN 1 ELSE 0 END AS result";
		} else {
			$sql = "SELECT CASE WHEN
					(SELECT COUNT(*) FROM emaj.emaj_mark, emaj.emaj_group
						WHERE mark_group = group_name
							AND mark_group = ANY ($groupsArray) AND group_is_rollbackable AND mark_name = '$mark'
							AND NOT mark_is_deleted
					) = $nbGroups THEN 1 ELSE 0 END AS result";
		}

		$result = $data->selectField($sql,'result');

		if ($result == 1) {
			// The mark is active, so now check there is no intermediate protected mark.
			$sql = "SELECT CASE WHEN
					(SELECT count(*) FROM emaj.emaj_mark
					  WHERE mark_group = ANY ($groupsArray) AND mark_time_id >
						(SELECT mark_time_id FROM emaj.emaj_mark
						 WHERE mark_group = ANY($groupsArray) AND mark_name = '$mark' LIMIT 1
					    ) AND mark_is_rlbk_protected
					) = 0 THEN 1 ELSE 0 END AS result";
			$result = $data->selectField($sql,'result');
		}

		return $result;
	}

	/**
	 * Rollback a groups array to a mark.
	 * It returns a set of messages.
	 */
	function rollbackGroups($groups,$mark,$isLogged,$comment) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);
		$data->clean($comment);

		if ($comment <> '') {
			if ($isLogged){
				$sql = "SELECT * FROM emaj.emaj_logged_rollback_groups($groupsArray,'$mark',true,'$comment')";
			}else{
				$sql = "SELECT * FROM emaj.emaj_rollback_groups($groupsArray,'$mark',true,'$comment')";
			}
		} else {
			if ($isLogged){
				$sql = "SELECT * FROM emaj.emaj_logged_rollback_groups($groupsArray,'$mark',true)";
			}else{
				$sql = "SELECT * FROM emaj.emaj_rollback_groups($groupsArray,'$mark',true)";
			}
		}

		return $data->selectSet($sql);
	}

	/**
	 * Get the global rollback statistics for a group and a mark (i.e. total number of log rows to rollback).
	 */
	function getGlobalRlbkStatGroup($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT coalesce(sum(stat_rows),0) as sumrows, count(*) as nbtables
					FROM emaj.emaj_log_stat_group('$group','$mark',NULL)
					WHERE stat_rows > 0";

		return $data->selectSet($sql);
	}

	/**
	 * Estimate the rollback duration for one or several groups and a mark.
	 */
	function estimateRollbackGroups($groups, $mark, $rollbackType) {
		global $data;

		$data->clean($groups);
		$groupsArray = "ARRAY['".str_replace(', ',"','",$groups)."']";
		$data->clean($mark);
		$isLogged = ($rollbackType == 'logged') ? 'TRUE' : 'FALSE';

		$sql = "SELECT to_char(emaj.emaj_estimate_rollback_groups($groupsArray, '$mark', $isLogged)
								+ '1 second'::interval,'YYYY/MM/DD HH24:MI:SS') as duration";

		return $data->selectField($sql,'duration');
	}

	/**
	 * Get the list of completed rollback operations (the latest 1000, if so many).
	 */
	function getCompletedRlbk() {
		global $data;
		$nb = 1000;

		// First cleanup recently completed rollback operation status.
		$sql = "SELECT emaj.emaj_cleanup_rollback_state()";
		$data->execute($sql);

		// Get the latest rollback operations.
		if ($this->getNumEmajVersion() >= 40300){	// version >= 4.3.0
			$sql = "SELECT rlbk_id, array_to_string(rlbk_groups,', ') as rlbk_groups_list, rlbk_status,
						to_char(rlbk_start_datetime,'{$this->tsFormat}') AS rlbk_start_datetime,
						to_char(rlbk_end_datetime,'{$this->tsFormat}') AS rlbk_end_datetime,
						to_char(rlbk_end_datetime - rlbk_start_datetime, '{$this->intervalFormat}') as rlbk_duration,
						rlbk_mark, rlbk_is_logged, rlbk_nb_session, rlbk_comment
					FROM emaj.emaj_rlbk
					WHERE rlbk_status IN ('COMPLETED','COMMITTED','ABORTED')
					ORDER BY rlbk_id DESC
					LIMIT $nb";
		} else {
			$sql = "SELECT rlbk_id, rlbk_groups, array_to_string(rlbk_groups,', ') as rlbk_groups_list, rlbk_status,
						to_char(tr.time_tx_timestamp,'{$this->tsFormat}') AS rlbk_start_datetime,
						to_char(rlbk_end_datetime,'{$this->tsFormat}') AS rlbk_end_datetime,
						to_char(rlbk_end_datetime - tr.time_tx_timestamp, '{$this->intervalFormat}') as rlbk_duration,
						rlbk_mark, rlbk_is_logged, rlbk_nb_session
					FROM emaj.emaj_rlbk
						 JOIN emaj.emaj_time_stamp tr ON (tr.time_id = rlbk_time_id)
					WHERE rlbk_status IN ('COMPLETED','COMMITTED','ABORTED')
					ORDER BY rlbk_id DESC
					LIMIT $nb";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Get the list of in progress rollback operations.
	 */
	function getInProgressRlbk() {
		global $data;

		$sql = "SELECT rlbk_id, array_to_string(rlbk_groups,', ') AS rlbk_groups_list, rlbk_mark,
					to_char(rlbk_mark_datetime,'{$this->tsFormat}') AS rlbk_mark_datetime,
					rlbk_is_logged,	rlbk_nb_session, rlbk_nb_table, rlbk_nb_sequence, rlbk_eff_nb_table, rlbk_status,
					to_char(rlbk_start_datetime,'{$this->tsFormat}') AS rlbk_start_datetime,
					to_char(rlbk_elapse,'{$this->intervalFormat}') AS rlbk_current_elapse,
					to_char(rlbk_remaining, '{$this->intervalFormat}') AS rlbk_remaining,
					rlbk_completion_pct";
		if ($this->getNumEmajVersion() >= 40300){	// version >= 4.3.0
			$sql .= ", rlbk_comment";
		}
		$sql .= " FROM emaj.emaj_rollback_activity()
				ORDER BY rlbk_id DESC";

		return $data->selectSet($sql);
	}

	/**
	 * Get the list of consolidable rollbacks (masking already consolidated rollbacks,i.e. with no intermediate mark and log).
	 */
	function getConsolidableRlbk() {
		global $data;

		$sql = "SELECT cons_group,
					cons_target_rlbk_mark_name, to_char(tt.time_tx_timestamp,'{$this->tsFormat}') AS cons_target_rlbk_mark_datetime,
					cons_end_rlbk_mark_name, to_char(rt.time_tx_timestamp,'{$this->tsFormat}') AS cons_end_rlbk_mark_datetime,
					cons_rows, cons_marks
				FROM emaj.emaj_get_consolidable_rollbacks(),
					emaj.emaj_time_stamp tt, emaj.emaj_time_stamp rt
				WHERE tt.time_id = cons_target_rlbk_mark_time_id
				  AND rt.time_id = cons_end_rlbk_mark_time_id
				  AND (cons_rows > 0 OR cons_marks > 0)
				ORDER BY cons_end_rlbk_mark_time_id, cons_group";

		return $data->selectSet($sql);
	}

	/**
	 * Set a comment for a rollback.
	 */
	function setCommentRollback($rlbkId,$comment) {
		global $data;

		$data->clean($comment);

		if ($comment <> '') {
			$sql = "SELECT emaj.emaj_comment_rollback($rlbkId, '$comment')";
		} else {
			$sql = "SELECT emaj.emaj_comment_rollback($rlbkId, NULL)";
		}

		return $data->execute($sql);
	}

	/**
	 * Consolidate a rollback operation.
	 */
	function consolidateRollback($group,$mark) {
		global $data;

		$data->clean($group);
		$data->clean($mark);

		$sql = "SELECT emaj.emaj_consolidate_rollback_group('$group','$mark') AS nbtblseq";

		return $data->execute($sql);
	}

	/**
	 * Get the properties of a single rollback operation. It returns 1 row.
	 */
	function getOneRlbk($rlbkId) {
		global $data;

		$data->clean($rlbkId);

		// First cleanup recently completed rollback operation status.
		$sql = "SELECT emaj.emaj_cleanup_rollback_state()";
		$data->execute($sql);

		// Get the emaj_rlbk data.
		if ($this->getNumEmajVersion() >= 40300){	// version >= 4.3.0
			$sql = "WITH rlbks AS (
					SELECT rlbk_id AS id,
						lag(rlbk_id) OVER (ORDER BY rlbk_id) AS rlbk_prior,
						lead(rlbk_id) OVER (ORDER BY rlbk_id) AS rlbk_next
						FROM emaj.emaj_rlbk
					)
					SELECT rlbk_id, array_to_string(rlbk_groups,', ') AS rlbk_groups_list, rlbk_status, coalesce(rlbk_comment,'') AS rlbk_comment,
						to_char(rlbk_start_datetime,'{$this->tsFormat}') AS rlbk_start_datetime,
						to_char(rlbk_end_datetime,'{$this->tsFormat}') AS rlbk_end_datetime,
						to_char(rlbk_end_datetime - rlbk_start_datetime,'{$this->intervalFormat}') AS rlbk_global_duration,
						to_char(rlbk_end_planning_datetime - rlbk_start_datetime,'{$this->intervalFormat}') AS rlbk_planning_duration,
						to_char(rlbk_end_locking_datetime - rlbk_end_planning_datetime,'{$this->intervalFormat}') AS rlbk_locking_duration,
						rlbk_mark, to_char(tm.time_tx_timestamp,'{$this->tsFormat}') as rlbk_mark_datetime, rlbk_is_logged, rlbk_nb_session,
						format('%s / %s', rlbk_eff_nb_table, rlbk_nb_table) AS rlbk_tbl,
						format('%s / %s', coalesce(rlbk_eff_nb_sequence::TEXT, '?'), rlbk_nb_sequence) AS rlbk_seq,
						rlbk_prior, rlbk_next
					FROM emaj.emaj_rlbk
						 JOIN emaj.emaj_time_stamp tm ON (tm.time_id = rlbk_mark_time_id)
						 JOIN rlbks ON (id = rlbk_id)
					WHERE rlbk_id = $rlbkId";
		} else {
			$sql = "WITH rlbks AS (
					SELECT rlbk_id AS id,
						lag(rlbk_id) OVER (ORDER BY rlbk_id) AS rlbk_prior,
						lead(rlbk_id) OVER (ORDER BY rlbk_id) AS rlbk_next
						FROM emaj.emaj_rlbk
					)
					SELECT rlbk_id, array_to_string(rlbk_groups,', ') AS rlbk_groups_list, rlbk_status,
						to_char(tr.time_tx_timestamp,'{$this->tsFormat}') AS rlbk_start_datetime,
						to_char(rlbk_end_datetime,'{$this->tsFormat}') AS rlbk_end_datetime,
						to_char(rlbk_end_datetime - tr.time_tx_timestamp,'{$this->intervalFormat}') AS rlbk_global_duration,
						'' AS rlbk_planning_duration,
						'' AS rlbk_locking_duration,
						rlbk_mark, to_char(tm.time_tx_timestamp,'{$this->tsFormat}') as rlbk_mark_datetime, rlbk_is_logged, rlbk_nb_session,
						format('%s / %s', rlbk_eff_nb_table, rlbk_nb_table) AS rlbk_tbl,";
			if ($this->getNumEmajVersion() >= 40200){	// version >= 4.2.0
				$sql = $sql . "
						format('%s / %s', coalesce(rlbk_eff_nb_sequence::TEXT, '?'), rlbk_nb_sequence) AS rlbk_seq,";
			} else {
				$sql = $sql . "
						format('%s / %s', rlbk_nb_sequence, rlbk_nb_sequence) AS rlbk_seq,";
			}
			$sql = $sql . "
						rlbk_prior, rlbk_next
					FROM emaj.emaj_rlbk
						 JOIN emaj.emaj_time_stamp tm ON (tm.time_id = rlbk_mark_time_id)
						 JOIN emaj.emaj_time_stamp tr ON (tr.time_id = rlbk_time_id)
						 JOIN rlbks ON (id = rlbk_id)
					WHERE rlbk_id = $rlbkId";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Get a single in progress rollback operation. It returns 0 or 1 row.
	 */
	function getOneInProgressRlbk($rlbkId) {
		global $data;

		$data->clean($rlbkId);

		$sql = "SELECT rlbk_status,
					to_char(rlbk_start_datetime,'{$this->tsFormat}') AS rlbk_start_datetime,
					to_char(rlbk_elapse,'{$this->intervalFormat}') AS rlbk_current_elapse,";
		if ($this->getNumEmajVersion() >= 40300){	// version >= 4.3.0
			$sql = $sql . "
					to_char(rlbk_planning_duration,'{$this->intervalFormat}') AS rlbk_planning_duration,
					to_char(rlbk_locking_duration,'{$this->intervalFormat}') AS rlbk_locking_duration,";
		} else {
			$sql = $sql . "
					'' AS rlbk_planning_duration,
					'' AS rlbk_locking_duration,";
		}
		$sql = $sql . "
					to_char(rlbk_remaining, '{$this->intervalFormat}') AS rlbk_remaining,
					rlbk_completion_pct
				FROM emaj.emaj_rollback_activity()
				WHERE rlbk_id = $rlbkId";
		return $data->selectSet($sql);
	}

	/**
	 * Get the report messages for a single completed rollback operation.
	 */
	function getRlbkReportMsg($rlbkId) {
		global $data;

		$data->clean($rlbkId);

		$sql = "SELECT substring(msg from '(.*?):') AS rlbk_severity,
					   substring(msg from ': (.*)') AS rlbk_message
				FROM (SELECT unnest(rlbk_messages) AS msg
						FROM emaj.emaj_rlbk
						WHERE rlbk_id = $rlbkId
					 ) AS t";

		return $data->selectSet($sql);
	}

	/**
	 * Get sessions data for a rollback operation.
	 */
	function getRlbkSessions($rlbkId) {
		global $data;

		$data->clean($rlbkId);

		$sql = "SELECT rlbs_session, rlbs_txid,
				to_char(rlbs_start_datetime,'{$this->tsFormat}') AS rlbs_start_datetime,
				to_char(rlbs_end_datetime,'{$this->tsFormat}') AS rlbs_end_datetime,
				to_char(rlbs_end_datetime - rlbs_start_datetime,'{$this->intervalFormat}') AS rlbs_duration
				FROM emaj.emaj_rlbk_session
				WHERE rlbs_rlbk_id = $rlbkId
				ORDER BY rlbs_session";

		return $data->selectSet($sql);
	}

	/**
	 * Get planning data for a rollback operation.
	 */
	function getRlbkSteps($rlbkId) {
		global $data, $lang;

		$data->clean($rlbkId);

		$sql = "SELECT row_number() over (ORDER BY rlbp_start_datetime, rlbp_batch_number, rlbp_step, rlbp_table, rlbp_object)
						 AS rlbp_rank,
					   coalesce(rlbp_schema, '') AS rlbp_schema,
					   coalesce(rlbp_table, '') AS rlbp_table,
					   CASE rlbp_step::TEXT
						 WHEN 'RLBK_SEQUENCES' THEN
							'{$lang['strrlbksequences']}'
						 WHEN 'DIS_APP_TRG' THEN
							format('{$lang['strrlbkdisapptrg']}', quote_ident(rlbp_object))
						 WHEN 'DIS_LOG_TRG' THEN
							'{$lang['strrlbkdislogtrg']}'
						 WHEN 'SET_ALWAYS_APP_TRG' THEN
							format('{$lang['strrlbksetalwaysapptrg']}', quote_ident(rlbp_object))
						 WHEN 'DROP_FK' THEN
							format('{$lang['strrlbkdropfk']}',	quote_ident(rlbp_object))
						 WHEN 'SET_FK_DEF' THEN
							format('{$lang['strrlbksetfkdef']}', quote_ident(rlbp_object))
						 WHEN 'RLBK_TABLE' THEN
							'{$lang['strrlbkrlbktable']}'
						 WHEN 'DELETE_LOG' THEN
							'{$lang['strrlbkdeletelog']}'
						 WHEN 'SET_FK_IMM' THEN
							format('{$lang['strrlbksetfkimm']}', quote_ident(rlbp_object))
						 WHEN 'ADD_FK' THEN
							format('{$lang['strrlbkaddfk']}', quote_ident(rlbp_object))
						 WHEN 'ENA_APP_TRG' THEN
							format('{$lang['strrlbkenaapptrg']}', quote_ident(rlbp_object))
						 WHEN 'SET_LOCAL_APP_TRG' THEN
							format('{$lang['strrlbksetlocalapptrg']}', quote_ident(rlbp_object))
						 WHEN 'ENA_LOG_TRG' THEN
							'{$lang['strrlbkenalogtrg']}'
						 ELSE rlbp_step || ' (?)'
					   END AS rlbp_action,
					   rlbp_batch_number, rlbp_session, rlbp_estimated_quantity,
					   to_char(rlbp_estimated_duration,'{$this->intervalFormat}') AS rlbp_estimated_duration,
--						The estimate quality is composed of a quality indicator (a letter between A and D) and the effective duration in 100th seconds
					   CASE WHEN rlbp_duration IS NULL OR rlbp_duration <= '10 milliseconds' OR rlbp_estimated_duration IS NULL THEN 'A'
							ELSE
								CASE WHEN rlbp_duration < rlbp_estimated_duration * 2 AND rlbp_estimated_duration < rlbp_duration * 2 THEN 'B:'
									 WHEN rlbp_duration < rlbp_estimated_duration * 5 AND rlbp_estimated_duration < rlbp_duration * 5 THEN 'C:'
									 ELSE 'D:'
								END
								|| ((extract(epoch from (rlbp_start_datetime + rlbp_duration)) - extract(epoch from rlbp_start_datetime)) * 100)::TEXT
					   END AS rlbp_estimate_quality,
					   CASE WHEN rlbp_estimate_method = 1 THEN 'STAT+'
							WHEN rlbp_estimate_method = 2 THEN 'STAT'
							WHEN rlbp_estimate_method = 3 THEN 'PARAM'
					   END AS rlbp_estimate_method,
					   to_char(rlbp_start_datetime,'{$this->tsFormat}') AS rlbp_start_datetime,
					   rlbp_quantity, to_char(rlbp_duration,'{$this->intervalFormat}') AS rlbp_duration
				FROM emaj.emaj_rlbk_plan
				WHERE rlbp_rlbk_id = $rlbkId
				  AND rlbp_step NOT IN ('LOCK_TABLE','CTRL-DBLINK','CTRL+DBLINK')
				ORDER BY rlbp_start_datetime, rlbp_batch_number, rlbp_step, rlbp_table, rlbp_object";

		return $data->selectSet($sql);
	}

	/**
	 * Get the number of tables and sequences that a tables group owned during a marks interval.
	 */
	function getNbObjectsGroupInPeriod($group,$firstMark,$lastMark) {
		global $data;

		$data->clean($group);
		$data->clean($firstMark);
		$data->clean($lastMark);

		if ($lastMark == 'currentsituation') {
			$sql = "SELECT count(DISTINCT(rel_schema, rel_tblseq)) FILTER (WHERE rel_kind = 'r') AS nb_tbl_in_group,
						   count(DISTINCT(rel_schema, rel_tblseq)) FILTER (WHERE rel_kind = 'S') AS nb_seq_in_group
					FROM emaj.emaj_relation
						 JOIN emaj.emaj_mark strtmark ON (strtmark.mark_group = rel_group AND strtmark.mark_name = '$firstMark')
					WHERE rel_group = '$group'
					  AND rel_time_range @> strtmark.mark_time_id";
		} else {
			$sql = "SELECT count(DISTINCT(rel_schema, rel_tblseq)) FILTER (WHERE rel_kind = 'r') AS nb_tbl_in_group,
						   count(DISTINCT(rel_schema, rel_tblseq)) FILTER (WHERE rel_kind = 'S') AS nb_seq_in_group
					FROM emaj.emaj_relation
						 JOIN emaj.emaj_mark strtmark ON (strtmark.mark_group = rel_group AND strtmark.mark_name = '$firstMark')
						 JOIN emaj.emaj_mark endmark ON (endmark.mark_group = rel_group AND endmark.mark_name = '$lastMark')
					WHERE rel_group = '$group'
					  AND rel_time_range && int8range(strtmark.mark_time_id, endmark.mark_time_id,'[)')";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Get the number of log sessions covering a marks interval for a tables group.
	 */
	function getNbLogSessionInPeriod($group,$firstMark,$lastMark) {
		global $data;

		$data->clean($group);
		$data->clean($firstMark);
		$data->clean($lastMark);

		if ($lastMark == 'currentsituation') {
			$sql = "SELECT count(*) AS nb_log_session FROM (
						SELECT 0
							FROM emaj.emaj_log_session
								 JOIN emaj.emaj_mark fm ON (fm.mark_group = lses_group AND fm.mark_name = '$firstMark')
							WHERE lses_group = '$group'
							  AND (fm.mark_time_id < upper(lses_time_range) OR upper_inf(lses_time_range))
						) AS ls";
		} else {
			$sql = "SELECT count(*) AS nb_log_session FROM (
						SELECT 0
							FROM emaj.emaj_log_session
								 JOIN emaj.emaj_mark fm ON (fm.mark_group = lses_group AND fm.mark_name = '$firstMark')
								 JOIN emaj.emaj_mark lm ON (lm.mark_group = lses_group AND lm.mark_name = '$lastMark')
							WHERE lses_group = '$group'
							  AND lm.mark_time_id >= lower(lses_time_range)
							  AND (fm.mark_time_id < upper(lses_time_range) OR upper_inf(lses_time_range))
						) AS ls";
		}

		return $data->selectField($sql,'nb_log_session');
	}

	/**
	 * Get the global log statistics for a group between 2 marks.
	 * It also delivers the sql queries to look at the corresponding log rows.
	 * It creates a temp table to easily compute aggregates in other functions called in the same conversation.
	 */
	function getLogStatGroup($group,$firstMark,$lastMark) {
		global $data;

		$data->clean($group);
		$data->clean($firstMark);
		$data->clean($lastMark);

		if ($this->getNumEmajVersion() >= 40300) {			// version >= 4.3.0
			$sql = "CREATE TEMP TABLE tmp_stat AS
					SELECT stat_group, stat_schema, stat_table,
						   stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime,
						   stat_rows
						FROM emaj.emaj_log_stat_group('$group','$firstMark','$lastMark')
						WHERE stat_rows > 0";
		} else {
			$sql = "CREATE TEMP TABLE tmp_stat AS
					SELECT stat_group, stat_schema, stat_table,
						   stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime,
						   stat_rows,
						   'select * from ' || quote_ident(stat_log_schema) || '.' || quote_ident(stat_log_table) ||
						   ' where emaj_gid > ' || stat_first_mark_gid::text ||
						   coalesce (' and emaj_gid <= ' || stat_last_mark_gid::text, '') ||
						   ' order by emaj_gid' as sql_text
						FROM emaj._log_stat_groups('{\"{$group}\"}',false,'$firstMark','$lastMark')
						WHERE stat_rows > 0";
		}

		$data->execute($sql);

		if ($this->getNumEmajVersion() >= 40300) {				// version >= 4.3.0
			$sql = "SELECT stat_group, stat_schema, stat_table,	stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime, stat_rows
						FROM tmp_stat
						ORDER BY stat_group, stat_schema, stat_table";
		} else {
			$sql = "SELECT stat_group, stat_schema, stat_table,	stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime, stat_rows, sql_text
						FROM tmp_stat
						ORDER BY stat_group, stat_schema, stat_table";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Get some aggregates from the temporary log_stat table created by the just previously called getLogStatGroup() function.
	 */
	function getLogStatSummary() {
		global $data;

		$sql = "SELECT coalesce(sum(stat_rows),0) AS sum_rows, count(distinct stat_schema || '.' || stat_table) AS nb_tables
				FROM tmp_stat";

		return $data->selectSet($sql);
	}

	/**
	 * Get the sequences statistics for a group between 2 marks.
	 * It creates a temp table to easily compute aggregates in other functions called in the same conversation.
	 */
	function getSeqStatGroup($group,$firstMark,$lastMark) {
		global $data;

		$data->clean($group);
		$data->clean($firstMark);
		$data->clean($lastMark);

		$sql = "CREATE TEMP TABLE tmp_stat AS
				SELECT stat_group, stat_schema, stat_sequence,
					   stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime,
					   stat_increments, stat_has_structure_changed
					FROM emaj.emaj_sequence_stat_group('$group','$firstMark','$lastMark')
					WHERE stat_increments <> 0 OR stat_has_structure_changed";

		$data->execute($sql);

		$sql = "SELECT stat_group, stat_schema, stat_sequence,
					   stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime,
					   stat_increments, stat_has_structure_changed
					FROM tmp_stat
					ORDER BY stat_group, stat_schema, stat_sequence, stat_first_mark_datetime";

		return $data->selectSet($sql);
	}

	/**
	 * Get some aggregates from the temporary tmp_stat table created by the just previously called getSeqStatGroup() function.
	 */
	function getSeqStatSummary() {
		global $data;

		$sql = "SELECT count(DISTINCT stat_schema || '.' || stat_sequence) AS nb_sequences
				FROM tmp_stat";

		return $data->selectSet($sql);
	}

	/**
	 * Get the detailed log statistics for a group between 2 marks.
	 * It also delivers the sql queries to look at the corresponding log rows.
	 * It creates a temp table to easily compute aggregates for the same conversation.
	 */
	function getDetailedLogStatGroup($group,$firstMark,$lastMark) {
		global $data;

		$data->clean($group);
		$data->clean($firstMark);
		$data->clean($lastMark);

		if ($this->getNumEmajVersion() >= 40300) {	// version >= 4.3.0
			$sql = "CREATE TEMP TABLE tmp_stat AS
					SELECT stat_group, stat_schema, stat_table,
						   stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime,
						   stat_role, stat_verb, stat_rows
						FROM emaj.emaj_detailed_log_stat_group('$group','$firstMark','$lastMark')
						WHERE stat_rows > 0";
		} else {									// oldest emaj versions
			$sql = "CREATE TEMP TABLE tmp_stat AS
					SELECT stat_group, stat_schema, stat_table,
						   stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime,
						   stat_role, stat_verb, stat_rows,
						   'select * from ' || quote_ident(stat_log_schema) || '.' || quote_ident(stat_log_table) ||
						   ' where emaj_gid > ' || stat_first_mark_gid::text ||
						   coalesce (' and emaj_gid <= ' || stat_last_mark_gid::text, '') ||
						   ' and emaj_verb = ' || quote_literal(substring(stat_verb from 1 for 3)) ||
						   ' order by emaj_gid' as sql_text
						FROM emaj._detailed_log_stat_groups('{\"{$group}\"}',false,'$firstMark','$lastMark')
						WHERE stat_rows > 0";
		}
		$data->execute($sql);

		if ($this->getNumEmajVersion() >= 40300) {				// version >= 4.3.0
			$sql = "SELECT stat_group, stat_schema, stat_table,	stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime,
						   stat_role, stat_verb, stat_rows
						FROM tmp_stat
						ORDER BY stat_group, stat_schema, stat_table, stat_role, stat_verb";
		} else {
			$sql = "SELECT stat_group, stat_schema, stat_table,	stat_first_mark, stat_first_mark_datetime, stat_last_mark, stat_last_mark_datetime,
						   stat_role, stat_verb, stat_rows, sql_text
						FROM tmp_stat
						ORDER BY stat_group, stat_schema, stat_table, stat_role, stat_verb";
		}

		return $data->selectSet($sql);
	}

	/**
	 * Get some aggregates from the temporary log_stat table created by the just previously called getDetailedLogStatGroup() function.
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
	 * Get distinct roles from the temporary log_stat table created by the just previously called getDetailedLogStatGroup() function.
	 */
	function getDetailedLogStatRoles() {
		global $data;

		$sql = "SELECT array_agg(distinct stat_role ORDER BY stat_role) AS roles FROM tmp_stat";

		return $data->phpArray($data->selectField($sql,'roles'));
	}

	/**
	 * Get the E-Maj technical columns names for a log table linked to an application table at a given mark.
	 */
	function getEmajColumns($group, $schema, $table, $mark, $markTs) {
		global $data;

		$data->clean($group);
		$data->clean($schema);
		$data->clean($table);
		$data->clean($mark);
		$data->clean($markTs);

		// Get the start mark time_id
		if ($mark == '[deleted mark]') {
			// The mark name is unknown. So get the time id from the start mark timestamp.
			$sql = "SELECT time_id
						FROM emaj.emaj_relation
							JOIN emaj.emaj_time_stamp ON (time_id = lower(rel_time_range))
						WHERE rel_schema = '$schema' AND rel_tblseq = '$table'
						  AND time_clock_timestamp = '$markTs'";
		} else {
			// The mark name is known. So get the time id from the emaj_mark table.
			$sql = "SELECT mark_time_id AS time_id
						FROM emaj.emaj_mark
						WHERE mark_group = '$group' AND mark_name = '$mark'";
		}
		$markTimeId = $data->selectField($sql,'time_id');

		// Get the requested column names
		$sql = "SELECT attname
					FROM emaj.emaj_relation
						 JOIN pg_catalog.pg_class ON (relname = rel_log_table)
						 JOIN pg_catalog.pg_namespace ON (pg_namespace.oid = relnamespace AND nspname = rel_log_schema)
						 JOIN pg_catalog.pg_attribute ON (attrelid = pg_class.oid)
					WHERE rel_schema = '$schema' AND rel_tblseq = '$table'
					  AND rel_time_range @> $markTimeId::BIGINT
					  AND attnum >= rel_emaj_verb_attnum AND NOT attisdropped";

		return $data->selectSet($sql);
	}

	/**
	 * Generate the SQL statement that dumps changes for a table of a group on a mark range.
	 * Various options are provided.
	 */
	function genSqlDumpChanges($group, $schema, $table, $startMark, $startTs, $endMark, $endTs, $consolidation, $emajColumnsList, $colsOrder, $orderBy) {
		global $data;

		$data->clean($group);
		$data->clean($schema);
		$data->clean($table);
		$data->clean($startMark);
		$data->clean($startTs);
		$data->clean($endMark);
		$data->clean($endTs);
		$data->clean($consolidation);
		$data->clean($emajColumnsList);
		$data->clean($colsOrder);
		$data->clean($orderBy);

		// Compute the lower bound last_emaj_gid.
		// Use the start mark name.
		// If it is unknown (in the case when the mark at the table assign time has been deleted), use the start timestamp.
		if ($startMark == '[deleted mark]') {
			$sql = "SELECT time_id, time_last_emaj_gid
						FROM emaj.emaj_relation
							 JOIN emaj.emaj_time_stamp ON (time_id = lower(rel_time_range))
						WHERE rel_schema = '$schema' AND rel_tblseq = '$table'
						  AND time_clock_timestamp = '$startTs'";
		} else {
			$sql = "SELECT time_id, time_last_emaj_gid
						FROM emaj.emaj_mark
							 JOIN emaj.emaj_time_stamp ON (time_id = mark_time_id)
						WHERE mark_group = '$group' AND mark_name = '$startMark'";
		}
		$res = $data->selectSet($sql);
		$lowerTimeId = $res->fields['time_id'];
		$lowerLastEmajGid = $res->fields['time_last_emaj_gid'];

		// Compute the upper bound last_emaj_gid.
		// If the there is no upper mark, use the highest bigint value.
		// Otherwise, use the end mark name.
		// If it is unknown (in the case when the mark at the table removal time has been deleted), use the end timestamp.
		if ($endMark == '') {
			$upperLastEmajGid = '+9223372036854775807';
		} else {
			if ($endMark == '[deleted mark]') {
				$sql = "SELECT time_id, time_last_emaj_gid
							FROM emaj.emaj_relation
								 JOIN emaj.emaj_time_stamp ON (time_id = upper(rel_time_range))
							WHERE rel_schema = '$schema' AND rel_tblseq = '$table'
							  AND time_clock_timestamp = '$endTs'";
			} else {
				$sql = "SELECT time_id, time_last_emaj_gid
							FROM emaj.emaj_mark
								JOIN emaj.emaj_time_stamp ON (time_id = mark_time_id)
							WHERE mark_group = '$group' AND mark_name = '$endMark'";
			}
			$res = $data->selectSet($sql);
			$upperTimeId = $res->fields['time_id'];
			$upperLastEmajGid = $res->fields['time_last_emaj_gid'];
		}

		// Build the SQL statement
		if ($endMark == '') {
			// No end mark
			$sql = "SELECT emaj._gen_sql_dump_changes_tbl(rel_log_schema, rel_log_table, rel_emaj_verb_attnum, rel_pk_cols,
														$lowerLastEmajGid, $upperLastEmajGid, '$consolidation',
														'$emajColumnsList', '$colsOrder', '$orderBy') AS sql_text
						FROM emaj.emaj_relation
						WHERE rel_schema = '$schema' AND rel_tblseq = '$table' AND upper_inf(rel_time_range)";
		} else {
			// The end mark is known
			$sql = "SELECT emaj._gen_sql_dump_changes_tbl(rel_log_schema, rel_log_table, rel_emaj_verb_attnum, rel_pk_cols,
														$lowerLastEmajGid, $upperLastEmajGid, '$consolidation',
														'$emajColumnsList', '$colsOrder', '$orderBy') AS sql_text
						FROM emaj.emaj_relation
						WHERE rel_schema = '$schema' AND rel_tblseq = '$table' AND rel_time_range && int8range($lowerTimeId, $upperTimeId,'[)')";
		}

		// Generate the statement
		$sqlText = $data->selectField($sql,'sql_text') . ";";

		// For performance reason, add the GUC adjusment when the consolidation level is FULL
		if ($consolidation == 'FULL') {
			$sqlText = "--SET enable_nestloop = FALSE;\n" . $sqlText; // . "\nRESET enable_nestloop;";
		}

		return $sqlText;
	}

	/**
	 * Get the 'E-Maj type' of a table or sequence.
	 * It returns:
     *   - 'L' when the table or sequence is a Log object,
     *   - 'E' if it is an internal E-maj object,
	 *   - 'U' if a table is not eligible to be assigned to a tables group (partitionned, temporary, unlogged or wih OIDS table)
     *   - '' in other cases
	 */
	function getEmajTypeTblSeq($schema, $tblseq) {
		global $data;

		$data->clean($schema);
		$data->clean($tblseq);

		if ($data->hasWithOids()) {
			$withOidsCondition = "OR relhasoids";
		} else {
			$withOidsCondition = "";
		}

		$sql = "SELECT CASE WHEN EXISTS (
									SELECT 1 FROM emaj.emaj_relation
										WHERE rel_log_schema = '$schema' AND (rel_log_table = '$tblseq' OR rel_log_sequence = '$tblseq')
									) THEN 'L'
							WHEN '$schema' = 'emaj' THEN 'E'
							WHEN EXISTS (
									SELECT 1 FROM pg_catalog.pg_class
												LEFT JOIN pg_catalog.pg_namespace ON pg_namespace.oid = relnamespace
										WHERE nspname = '$schema' AND relname = '$tblseq'
										AND (relkind = 'p' OR (relkind = 'r' AND (relpersistence <> 'p' $withOidsCondition)))
									) THEN 'U'
							ELSE '' END AS emaj_type";

		return $data->selectField($sql,'emaj_type');
	}

	/**
	 * Get the application triggers on all application tables known in the database.
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
			if ($this->getNumEmajVersion() >= 40000) {				# emaj version 4.0+
				$sql .= "CASE
							WHEN NOT EXISTS (
								SELECT 1 FROM emaj.emaj_relation
									WHERE rel_schema = rn.nspname AND rel_tblseq = relname AND upper_inf(rel_time_range))
								THEN NULL
							ELSE NOT EXISTS (
								SELECT 1 FROM emaj.emaj_relation
									WHERE rel_schema = rn.nspname AND rel_tblseq = relname AND upper_inf(rel_time_range)
										AND tgname = ANY(rel_ignored_triggers))
							END	AS tgisautodisable,";
			} elseif ($this->getNumEmajVersion() >= 30100) {		# emaj version [3.1 - 4.0[
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
	 * Get the orphan application triggers in the emaj_relation table, i.e. not currently created.
	 */
	function getOrphanAppTriggers() {
		global $data;

		if ($this->getNumEmajVersion() >= 40000) {				# emaj version 4.0+
			$sql = "	SELECT rel_schema, rel_tblseq, unnest(rel_ignored_triggers)
							FROM emaj.emaj_relation";
		} else {
			$sql = "	SELECT trg_schema, trg_table, trg_name
							FROM emaj.emaj_ignored_app_trigger";
		}
		$sql .= " EXCEPT
					SELECT nspname, relname, tgname
						FROM pg_catalog.pg_trigger t, pg_catalog.pg_class, pg_catalog.pg_namespace
						WHERE relnamespace = pg_namespace.oid AND tgrelid = pg_class.oid
				ORDER BY 1,2,3";

		return $data->selectSet($sql);
	}

	/**
	 * Count the number of application triggers held by a tables set of a given schema.
	 */
	function getNbAppTriggers($schema, $tables) {
		global $data;

		$data->clean($schema);
		$data->clean($tables);
		$tablesList = "'" . str_replace(", ", "','", $tables) . "'";

		$sql = "SELECT count(*) AS nbtriggers
				FROM pg_catalog.pg_trigger t
					JOIN pg_catalog.pg_class ON (pg_class.oid = tgrelid)
					JOIN pg_catalog.pg_namespace ON (pg_namespace.oid = relnamespace)
				WHERE nspname = '$schema'
				  AND relname IN ($tablesList)
					-- Discard E-Maj triggers
				  AND tgname NOT IN ('emaj_trunc_trg', 'emaj_log_trg')
					-- Discard internal triggers for foreign key constraints
				  AND (tgconstraint = 0 OR NOT EXISTS
						(SELECT 1
							FROM pg_catalog.pg_depend d
								JOIN pg_catalog.pg_constraint c	ON (d.refclassid = c.tableoid AND d.refobjid = c.oid)
							WHERE d.classid = t.tableoid AND d.objid = t.oid AND d.deptype = 'i' AND c.contype = 'f'))";

		return $data->selectField($sql,'nbtriggers');
	}

	/**
	 * Get the history of E-Maj events for a given table or sequence.
	 * The function is only called when emaj version >= 4.4.0
	 */
	function getTblSeqEmajHist($schema, $tblseq) {
		global $data, $lang;

		$data->clean($schema);
		$data->clean($tblseq);

		$sql = "WITH group_hist AS (
					SELECT DISTINCT grph_group, grph_time_range
						FROM emaj.emaj_group_hist
							JOIN emaj.emaj_relation_change ON (grph_group = rlchg_group AND grph_time_range @> rlchg_time_id)
						WHERE rlchg_schema = '$schema' AND rlchg_tblseq = '$tblseq'
				), event AS (
					SELECT
						CASE		-- Icon color (every event prior a relation REMOVE or MOVE is grey)
							WHEN rlchg_change_kind::TEXT LIKE 'REMOVE_%' OR
								EXISTS (SELECT 1 FROM emaj.emaj_relation_change c2
											WHERE c2.rlchg_schema = '$schema' AND c2.rlchg_tblseq = '$tblseq'
											  AND c2.rlchg_change_kind::TEXT LIKE '%MOVE_%'
											  AND c2.rlchg_time_id > c1.rlchg_time_id)
								THEN 'Grey'
							ELSE 'Green'
						END
						||
						CASE		-- Icon shape
							WHEN rlchg_change_kind::TEXT LIKE 'ADD_%' THEN 'Begin'
							WHEN rlchg_change_kind::TEXT LIKE 'MOVE_%' THEN 'Cross'
							WHEN rlchg_change_kind::TEXT LIKE 'REMOVE_%' THEN 'End'
							WHEN rlchg_change_kind::TEXT LIKE 'CHANGE_%' OR rlchg_change_kind = 'REPAIR_TABLE' THEN 'Simple'
							ELSE ''
						END 
						AS ev_graphic,
						rlchg_time_id, 2 AS rank, rlchg_change_kind::TEXT AS change_kind, rlchg_group, rlchg_new_group,
						rlchg_priority, rlchg_new_priority, rlchg_log_data_tsp, rlchg_new_log_data_tsp,
						rlchg_log_index_tsp, rlchg_new_log_index_tsp, rlchg_ignored_triggers,  rlchg_new_ignored_triggers
						FROM emaj.emaj_relation_change c1
						WHERE rlchg_schema = '$schema' AND rlchg_tblseq = '$tblseq'
					UNION ALL
					SELECT '', lower(grph_time_range), 1, 'CREATE_GROUP', grph_group, NULL,
						NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL
						FROM group_hist
					UNION ALL
					SELECT '', upper(grph_time_range) - 1, 3, 'DROP_GROUP', grph_group, NULL,
						NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL
						FROM group_hist
						WHERE NOT upper_inf(grph_time_range)
				)
				SELECT ev_graphic, time_tx_timestamp AS ev_ts,
					CASE change_kind
						WHEN 'CREATE_GROUP' THEN format('" . $data->clean($lang['streventcreategroup']) . "', rlchg_group)
						WHEN 'DROP_GROUP' THEN format('" . $data->clean($lang['streventdropgroup']) . "', rlchg_group)
						WHEN 'ADD_TABLE' THEN format('" . $data->clean($lang['streventassigntable']) . "', rlchg_group)
						WHEN 'ADD_SEQUENCE' THEN format('" . $data->clean($lang['streventassignsequence']) . "', rlchg_group)
						WHEN 'MOVE_TABLE' THEN format('" . $data->clean($lang['streventmovetable']) . "', rlchg_group, rlchg_new_group)
						WHEN 'MOVE_SEQUENCE' THEN format('" . $data->clean($lang['streventmovesequence']) . "', rlchg_group, rlchg_new_group)
						WHEN 'REMOVE_TABLE' THEN format('" . $data->clean($lang['streventremovetable']) . "', rlchg_group)
						WHEN 'REMOVE_SEQUENCE' THEN format('" . $data->clean($lang['streventremovesequence']) . "', rlchg_group)
						WHEN 'CHANGE_PRIORITY' THEN format('" . $data->clean($lang['streventchangepriority']) . "',
															coalesce(rlchg_priority::TEXT, 'NULL'),
															coalesce(rlchg_new_priority::TEXT, 'NULL'))
						WHEN 'CHANGE_LOG_DATA_TABLESPACE' THEN format('" . $data->clean($lang['streventchangedatatsp']) . "',
																	coalesce(rlchg_log_data_tsp, 'NULL'),
																	coalesce(rlchg_new_log_data_tsp, 'NULL'))
						WHEN 'CHANGE_LOG_INDEX_TABLESPACE' THEN format('" . $data->clean($lang['streventchangeidxtsp']) . "',
																	coalesce(rlchg_log_index_tsp, 'NULL'),
																	coalesce(rlchg_new_log_index_tsp, 'NULL'))
						WHEN 'CHANGE_IGNORED_TRIGGERS' THEN format('" . $data->clean($lang['streventchangeignoredtriggers']) . "',
																coalesce(array_to_string(rlchg_ignored_triggers, ','), 'NULL'),
																coalesce(array_to_string(rlchg_new_ignored_triggers, ','), 'NULL'))
						WHEN 'REPAIR_TABLE' THEN '" . $data->clean($lang['streventrepairtable']) . "'
						ELSE 'Unknown event (' || change_kind || ') !!!'
					END AS ev_text
					FROM event
						JOIN emaj.emaj_time_stamp ON (time_id = rlchg_time_id)
				ORDER BY time_tx_timestamp DESC, rank DESC";

		return $data->selectSet($sql);
	}

	/**
	 * Get the list of existing triggers on a table.
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
						tgname IN ('emaj_trunc_trg', 'emaj_log_trg') as tgisemaj,
				";

		if ($this->isEnabled() && $this->isAccessible()) {
			if ($this->getNumEmajVersion() >= 40000) {
				$sql .= " 	CASE WHEN tgname IN ('emaj_trunc_trg', 'emaj_log_trg') THEN NULL
								 WHEN NOT EXISTS (
									SELECT 1 FROM emaj.emaj_relation
										WHERE rel_schema = '$schema' AND rel_tblseq = '$table' AND upper_inf(rel_time_range))
									THEN NULL
								 ELSE NOT EXISTS (
									SELECT 1 FROM emaj.emaj_relation
										WHERE rel_schema = '$schema' AND rel_tblseq = '$table' AND upper_inf(rel_time_range)
											  AND tgname = ANY(rel_ignored_triggers))
							END as tgisautodisable,";
			} elseif ($this->getNumEmajVersion() >= 30100) {
				$sql .= " 	CASE WHEN tgname IN ('emaj_trunc_trg', 'emaj_log_trg') THEN NULL
								ELSE NOT EXISTS (
									SELECT 1 FROM emaj.emaj_ignored_app_trigger
										WHERE trg_schema = '$schema' AND trg_table = '$table' AND trg_name = tgname)
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
					  AND relname='$table' AND rn.nspname='$schema'
					  AND (tgconstraint = 0 OR NOT EXISTS
							(SELECT 1 FROM pg_catalog.pg_depend d
								JOIN pg_catalog.pg_constraint c	ON (d.refclassid = c.tableoid AND d.refobjid = c.oid)
							WHERE d.classid = t.tableoid AND d.objid = t.oid AND d.deptype = 'i' AND c.contype = 'f'))
					) as t
				ORDER BY tgorder, tgname";

		return $data->selectSet($sql);
	}

	/**
	 * Handle the list of triggers that must not be automatically disabled at rollback time: add or remove one.
	 */
	function ignoreAppTrigger($action, $schema, $table, $trigger) {
		global $data;

		$data->clean($action);
		$data->clean($schema);
		$data->clean($table);
		$data->clean($trigger);

		if ($this->getNumEmajVersion() >= 40000) {				# emaj version 4.0+
			# Build the new list of "triggers to ignore at rollback time", by adding or removing the given trigger to or from the existing triggers array
			if ($action == 'ADD') {
				$arrayFunction = 'array_append';
			} else {
				$arrayFunction = 'array_remove';
			}
			$sql = "SELECT array_to_json($arrayFunction(rel_ignored_triggers, '$trigger')) AS json_triggers_list
					  FROM emaj.emaj_relation WHERE rel_schema = '$schema' AND rel_tblseq = '$table' AND upper_inf(rel_time_range)";
			$jsonTriggersList = $data->selectField($sql,'json_triggers_list');

			# Register the modified triggers list
			$sql = "SELECT emaj.emaj_modify_table('$schema', '$table', '{\"ignored_triggers\": $jsonTriggersList}'::JSONB)";
			$data->execute($sql);
			return 1;

		} else {
			$sql = "SELECT emaj.emaj_ignore_app_trigger('$action', '$schema', '$table', '$trigger') AS nbtriggers";
			return $data->selectField($sql,'nbtriggers');
		}
	}

	/**
	 * Get the current timestamp and sequences last_values for the E-Maj Activity reporting.
	 */
	function emajStatGetSeqLastVal($groupsIncludeFilter, $groupsExcludeFilter,
							$tablesIncludeFilter, $tablesExcludeFilter,
							$sequencesIncludeFilter, $sequencesExcludeFilter) {
		global $data;

		$data->clean($groupsIncludeFilter);
		$data->clean($groupsExcludeFilter);
		$data->clean($tablesIncludeFilter);
		$data->clean($tablesExcludeFilter);
		$data->clean($sequencesIncludeFilter);
		$data->clean($sequencesExcludeFilter);

		$sql = "SELECT p_key, p_value
					FROM emaj._get_sequences_last_value(
						'$groupsIncludeFilter', '$groupsExcludeFilter',
						'$tablesIncludeFilter', '$tablesExcludeFilter',
						'$sequencesIncludeFilter', '$sequencesExcludeFilter')";

		return $data->selectSet($sql);
	}

	/**
	 * Get the current timestamp and sequences last_values for the E-Maj Activity reporting.
	 */
	function emajStatGetGlobalCounters() {
		global $data;

		$sql = "SELECT count(*) AS nb_groups,
					count(*) FILTER (WHERE group_is_logging) AS nb_logging_groups,
					coalesce(sum(group_nb_table), 0) AS nb_tables,
					coalesce(sum(group_nb_table) FILTER (WHERE group_is_logging), 0) AS nb_logged_tables,
					coalesce(sum(group_nb_sequence), 0) AS nb_sequences,
					coalesce(sum(group_nb_sequence) FILTER (WHERE group_is_logging), 0) AS nb_logged_sequences
				FROM emaj.emaj_group";

		return $data->selectSet($sql);
	}

	/**
	 * Get the tables groups in logging state for the E-Maj Activity reporting.
	 */
	function emajStatGetGroups($groupsIncludeFilter, $groupsExcludeFilter) {
		global $data;

		$data->clean($groupsIncludeFilter);
		$data->clean($groupsExcludeFilter);

		$sql = "SELECT mark_group AS group,
					mark_name AS latest_mark,
					to_char(time_tx_timestamp, 'YYYY/MM/DD HH24:MI:SS') AS latest_mark_ts,
					extract(EPOCH FROM time_tx_timestamp) AS latest_mark_epoch,
					NULL AS changes_since_previous, NULL AS cps_since_previous,
					NULL AS changes_since_mark, NULL AS cps_since_mark
				FROM emaj.emaj_mark
					JOIN emaj.emaj_time_stamp ON (time_id = mark_time_id)
				WHERE mark_log_rows_before_next IS NULL				-- to filter the latest mark of each logging group
				  AND mark_group ~ '$groupsIncludeFilter'
				  AND ('$groupsExcludeFilter' = '' OR mark_group !~ '$groupsExcludeFilter')";

		return $data->selectSet($sql);
	}

	/**
	 * Get the logged tables for the E-Maj Activity reporting.
	 */
	function emajStatGetTables($groupsIncludeFilter, $groupsExcludeFilter, $tablesIncludeFilter, $tablesExcludeFilter) {
		global $data;

		$data->clean($groupsIncludeFilter);
		$data->clean($groupsExcludeFilter);
		$data->clean($tablesIncludeFilter);
		$data->clean($tablesExcludeFilter);

		$sql = "WITH filtered_group AS (
					SELECT mark_group, mark_time_id
						FROM emaj.emaj_mark
							JOIN emaj.emaj_time_stamp ON (time_id = mark_time_id)
						WHERE mark_log_rows_before_next IS NULL     -- to filter the latest mark of each logging group
						  AND mark_group ~ '$groupsIncludeFilter'
						  AND ('$groupsExcludeFilter' = '' OR mark_group !~ '$groupsExcludeFilter')
				)
					SELECT rel_schema, rel_tblseq, rel_group, tbl_log_seq_last_val AS seq_at_mark,
						NULL AS seq_current, NULL AS seq_previous,
						NULL AS changes_since_previous, NULL AS cps_since_previous,
						NULL AS changes_since_mark, NULL AS cps_since_mark
						FROM emaj.emaj_relation
							JOIN filtered_group ON (mark_group = rel_group)
							JOIN emaj.emaj_table ON (tbl_schema = rel_schema AND tbl_name = rel_tblseq AND tbl_time_id = mark_time_id)
						WHERE upper_inf(rel_time_range)
						  AND rel_kind = 'r'
						  AND (rel_schema || '.' || rel_tblseq) ~ '$tablesIncludeFilter'
						  AND ('$tablesExcludeFilter' = '' OR (rel_schema || '.' || rel_tblseq) !~ '$tablesExcludeFilter')";

		return $data->selectSet($sql);
	}

	/**
	 * Get the logged sequences for the E-Maj Activity reporting.
	 */
	function emajStatGetsequences($groupsIncludeFilter, $groupsExcludeFilter, $sequencesIncludeFilter, $sequencesExcludeFilter) {
		global $data;

		$data->clean($groupsIncludeFilter);
		$data->clean($groupsExcludeFilter);
		$data->clean($sequencesIncludeFilter);
		$data->clean($sequencesExcludeFilter);

		$sql = "WITH filtered_group AS (
					SELECT mark_group, mark_time_id
						FROM emaj.emaj_mark
							JOIN emaj.emaj_time_stamp ON (time_id = mark_time_id)
						WHERE mark_log_rows_before_next IS NULL     -- to filter the latest mark of each logging group
						  AND mark_group ~ '$groupsIncludeFilter'
						  AND ('$groupsExcludeFilter' = '' OR mark_group !~ '$groupsExcludeFilter')
				)
					SELECT rel_schema, rel_tblseq, rel_group, sequ_last_val AS seq_at_mark, sequ_increment,
						NULL AS seq_current, NULL AS seq_previous,
						NULL AS changes_since_previous, NULL AS cps_since_previous,
						NULL AS changes_since_mark, NULL AS cps_since_mark
						FROM emaj.emaj_relation
							JOIN filtered_group ON (mark_group = rel_group)
							JOIN emaj.emaj_sequence ON (sequ_schema = rel_schema AND sequ_name = rel_tblseq AND sequ_time_id = mark_time_id)
						WHERE upper_inf(rel_time_range)
						  AND rel_kind = 'S'
						  AND (rel_schema || '.' || rel_tblseq) ~ '$sequencesIncludeFilter'
						  AND ('$sequencesExcludeFilter' = '' OR (rel_schema || '.' || rel_tblseq) !~ '$sequencesExcludeFilter')";

		return $data->selectSet($sql);
	}

}
?>
