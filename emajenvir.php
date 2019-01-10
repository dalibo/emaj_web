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
