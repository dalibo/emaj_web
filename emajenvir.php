<?php

	/*
	 * Display the E-Maj environment characteristics
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	// Callback function to dynamicaly add an icon to each diagnostic message
	function renderDiagnostic($val) {
		global $misc;
		if (preg_match("/[Nn]o error /",$val)) {
			$icon = 'CheckConstraint';
		} else {
			$icon = 'CorruptedDatabase';
		}
		return "<img src=\"".$misc->icon($icon)."\" style=\"vertical-align:bottom;\" />" . $val;
	}

	/**
	 * Show the E-Maj environment characteristics of the database
	 */
	function doDefault($msg = '') {
		global $misc, $lang, $emajdb;
		global $oldest_supported_emaj_version_num, $oldest_supported_emaj_version, $last_known_emaj_version_num;
		global $paramValue, $defValParam;

		$misc->printHeader('database', 'database', 'emajenvir');

		$emajOK = 1;

		// check if E-Maj is installed in the current database
		if (! $emajdb->isEnabled()) {
			// emaj is not installed, check if the extension is available
			if (! $emajdb->isExtensionAvailable()) {
				echo "<p>{$lang['emajextnotavailable']}</p>\n";
			} else {
				echo "<p>{$lang['emajextnotcreated']}</p>\n";
			}
			$emajOK= 0;
		} else {
			// emaj is installed, check that the user has enought rights to continue
			if (! $emajdb->isAccessible()) {
				echo "<p>{$lang['emajnogrant']}</p>\n";
				$emajOK= 0;
			}
		}

// Version section
		$misc->printTitle($lang['emajversions']);

		// display the postgres version
		$server_info = $misc->getServerInfo();
		preg_match('/PostgreSQL (.*)/',$server_info['platform'], $pgVersion);
		echo "<p>{$lang['emajpgversion']}{$pgVersion[1]}</p>\n";

		if ($emajOK) {
			if ($emajdb->isExtension()) {
				$installationMode = $lang['emajasextension'];
			} else {
				$installationMode = $lang['emajasscript'];
			}
			echo "<p>{$lang['emajversion']}{$emajdb->getEmajVersion()} ({$installationMode})</p>\n";
			if ($emajdb->getNumEmajVersion() < $oldest_supported_emaj_version_num) {
				echo "<p>" . sprintf($lang['emajtooold'],$emajdb->getEmajVersion(),$oldest_supported_emaj_version) . "</p>\n";
				$emajOK= 0;
			} else {
				if ($emajdb->getNumEmajVersion() <> 999999) {
					if ($emajdb->getNumEmajVersion() < $last_known_emaj_version_num) {
						echo "<p>{$lang['emajversionmorerecent']}</p>\n";
					}
					if ($emajdb->getNumEmajVersion() > $last_known_emaj_version_num) {
						echo "<p>{$lang['emajwebversionmorerecent']}</p>\n";
					}
				}
			}
		}

		if ($emajOK) {

// General characteristics of the E-Maj environment
			echo "<hr/>\n";
			$misc->printTitle($lang['emajcharacteristics']);
			if ($emajdb->isEmaj_Adm()) {
				echo "<p>".sprintf($lang['emajdiskspace'],$emajdb->getEmajSize())."</p>\n";
			}

// E-Maj environment checking
			echo "<hr/>\n";
			$misc->printTitle($lang['emajchecking']);

			$messages = $emajdb->checkEmaj();

			$columns = array(
				'message' => array(
					'title' => $lang['emajdiagnostics'],
					'field' => field('emaj_verify_all'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderDiagnostic'),
				),
			);

			$actions = array ();

			$misc->printTable($messages, $columns, $actions, 'checks');

// E-Maj parameters managed with the emaj_param table

	function displayOneParameter($param, $label, $info) {
	// This function displays the value of 1 parameter.
		global $lang, $misc, $emajdb, $paramValue, $defValParam;

		echo "\t<div class=\"form-param-label\">{$label}</div>\n";

		echo "\t<div class=\"form-param-info\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$info}\"/></div>";

		if (isset($paramValue[$param])) {
			$value = $paramValue[$param];
			// Specific adjustments for some parameters
			if ($param == 'dblink_user_password' && $value == "<masked data>") {
//TODO: better handle the case: for emaj_viewer, hide the entire row ?
//								for emaj_adm, add an eye to show the string value ?
				// Hide the dblink connection string for emaj_viewer only roles
				$value = htmlspecialchars($value);
			}
			if ($param == 'alter_log_table') {
				// Add a line feed between ADD COLUMN directives
				$value = preg_replace('/,(\s*ADD\s+COLUMN)/i', ',<br>$1', $value);
			}
			if (preg_match("/fixed_|avg_/",$param)) {
				// Drop unsignificant zeros on the left and add a unit to cost parameters
				$value = preg_replace('/^0+/','', $value) . " µs";
			}
		} else {
			$value = $defValParam[$param] . " (def)";
		}
		echo "<div class=\"form-param-value\">{$value}</div>\n";

		if ($emajdb->isEmaj_Adm()) {
//TODO: Modify button to insert
			echo "<div class=\"form-button-param\">"
//				. $lang['strupdate']
				. "</div>\n";
		} else {
			echo "<div class=\"form-button-param\"></div>\n";
		}
	}

			echo "<hr/>\n";
			$misc->printTitle($lang['emajextparams']);

			// Set the default values for all existing parameters
			if ($emajdb->getNumEmajVersion() >= 30000) {
				$defValParam['alter_log_table'] = '';
			}
			$defValParam['avg_fkey_check_duration'] = '20 µs';
			$defValParam['avg_row_delete_log_duration'] = '10 µs';
			$defValParam['avg_row_rollback_duration'] = '100 µs';
			$defValParam['dblink_user_password'] = '';
			$defValParam['fixed_dblink_rollback_duration'] = '4000 µs';
			$defValParam['fixed_step_rollback_duration'] = '2500 µs';
			$defValParam['fixed_table_rollback_duration'] = '1000 µs';
			$defValParam['history_retention'] = '1 YEAR';

			// Read and process the data from emaj_param
			$params = $emajdb->getExtensionParams();

			while (!$params->EOF) {
				$paramValue[$params->fields['param_key']] = $params->fields['param_value'];
				$params->moveNext();
			}

			// Display the parameter values
			echo "<div>\n";
			echo "<h4>{$lang['emajpargeneral']}</h4>\n";

			echo "<div class=\"form-container-param\">\n";
			displayOneParameter('history_retention', $lang['emajparhistret'], $lang['emajparhistretinfo']);
			displayOneParameter('dblink_user_password', $lang['emajpardblinkcon'], $lang['emajpardblinkconinfo']);

			if ($emajdb->getNumEmajVersion() >= 30000) {
				# add line feed if several columns added to the log tables
				displayOneParameter('alter_log_table', $lang['emajparalterlog'], $lang['emajparalterloginfo']);
			}
			echo "</div>\n";

			echo "<div>\n";
			echo "<h4>{$lang['emajparcostmodel']}</h4>\n";
			echo "</div>\n";

			echo "<div class=\"form-container-param\">\n";
			displayOneParameter('fixed_step_rollback_duration', $lang['emajparfixedstep'], $lang['emajparfixedstepinfo']);
			displayOneParameter('fixed_dblink_rollback_duration', $lang['emajparfixeddblink'], $lang['emajparfixeddblinkinfo']);
			displayOneParameter('fixed_table_rollback_duration', $lang['emajparfixedrlbktbl'], $lang['emajparfixedrlbktblinfo']);
			displayOneParameter('avg_row_rollback_duration', $lang['emajparavgrowrlbk'], $lang['emajparavgrowrlbkinfo']);
			displayOneParameter('avg_row_delete_log_duration', $lang['emajparavgrowdel'], $lang['emajparavgrowdelinfo']);
			displayOneParameter('avg_fkey_check_duration', $lang['emajparavgfkcheck'], $lang['emajparavgfkcheckinfo']);

			echo "</div>\n";
		}
	}

	$misc->printHtmlHeader($lang['emajenvironment']);
	$misc->printBody();

	switch ($action) {
		default:
			doDefault();
			break;
	}

	$misc->printFooter();

?>
