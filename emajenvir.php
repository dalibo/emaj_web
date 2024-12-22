<?php

	/*
	 * Display the E-Maj environment characteristics
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	// Callback function to dynamicaly transform a message severity level into an icon
	function renderMsgSeverity($val) {
		global $misc;
		if ($val == '1' || $val == '2') {
			$icon = 'RedX';
			$alt = 'Error';
		} elseif ($val == '3') {
			$icon = 'Warning';
			$alt = 'Warning';
		} elseif ($val == '4') {
			$icon = 'Checkmark';
			$alt = 'OK';
		} else {
			return '?';
		}
		return "<img src=\"{$misc->icon($icon)}\" alt=\"$alt\" class=\"cellicon\"/>";
	}

	function displayOneParameter($param, $label, $info) {
	// This function displays the value of a sinigle parameter in the parameter section of the main page.
		global $lang, $misc, $emajdb, $paramValue, $defValParam;

		echo "\t<div class=\"form-param-label\">{$label}</div>\n";
		echo "\t<div class=\"form-param-info\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$info}\"/></div>";

		if (isset($paramValue[$param])) {
			$value = $paramValue[$param];
			// Specific adjustments for some parameters
			if ($param == 'dblink_user_password') {
				if ($emajdb->isEmaj_Adm()) {
					// For emaj_adm roles, display the parameter in tooltip
					$value = "<div class=\"tooltip right-aligned-tooltip\">#############...<span>" . htmlspecialchars($value) . "</span></div>";
				} else {
					// For emaj_viewer roles, just a masked value
					$value = "################";
				}
			}
			if ($param == 'alter_log_table') {
				// Add a line feed between ADD COLUMN directives
				$value = preg_replace('/,(\s*ADD\s+COLUMN)/i', ',<br>$1', $value);
			}
			if (preg_match("/fixed_|avg_/",$param)) {
				// Drop unsignificant zeros on the left and add a unit to cost parameters
				$value = preg_replace('/^0+/','', $value) . " µs";
			}
			echo "<div class=\"form-param-value\">{$value}</div>\n";
		} else {
			// The parameter has its default value
			echo "<div class=\"form-param-def-value\">${defValParam[$param]}&nbsp;<sup>(def)</sup></div>\n";
		}

//		if ($emajdb->isEmaj_Adm()) {
////TODO: Modify button to insert
//			echo "<div class=\"form-button-param\">"
//				. $lang['strupdate']
//				. "</div>\n";
//		} else {
			echo "<div class=\"form-button-param\"></div>\n";
//		}
	}

	/**
	 * Prepare the extension creation: ask for the version and the confirmation
	 */
	function create_extension() {
		global $misc, $lang, $emajdb;
		global $xrefEmajPg;

		$misc->printHeader('database', 'database', 'emajenvir');

		$misc->printTitle($lang['strcreateemajextension']);

		// build the array of usable emaj versions
		$server_info = $misc->getServerInfo();
		$availableVersions = $emajdb->getAvailableExtensionVersions();

		$pgMajorVersion = $server_info['pgMajorVersion'];
		$usableVersions = array();
		foreach($availableVersions as $v) {
			if (!isset($xrefEmajPg[$v['version']]) ||
				 (version_compare($pgMajorVersion, $xrefEmajPg[$v['version']]['minPostgresVersion'], '>=') &&
				  version_compare($pgMajorVersion, $xrefEmajPg[$v['version']]['maxPostgresVersion'], '<='))) {
				// if the emaj version is known in the xref and is compatible with the current PG version, keep it
				// if the emaj version is unknown, keep it too
				// otherwise, discard it
				$usableVersions[] = $v['version'];
			}
		}

		echo "<form action=\"emajenvir.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"create_extension_ok\" />\n";
		echo $misc->form;

		if (count($usableVersions) == 0) {
			// no emaj version is compatible with the PG version
			echo "<p>{$lang['strnocompatibleemajversion']}</p>\n";
			echo "<div class=\"actionslist\">";
			echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</div>";
		} else {
			if (count($usableVersions) == 1) {
				// a single version is compatible with the PG version, so just display the information
				foreach($usableVersions as $v) {
					echo "<p>{$lang['strversion']}{$v}</p>\n";
					echo "<input type=\"hidden\" name=\"version\" value=\"". htmlspecialchars($v) . "\" />\n";
				}
			} else {
				// several versions are compatible with the PG version,
				// so display a select box so that the user can choose the version to install
				echo "<p>{$lang['strversion']}<select name=\"version\" id=\"version\">\n";
				foreach($usableVersions as $v) {
					echo "\t<option value=\"", htmlspecialchars($v), "\" >", htmlspecialchars($v), "</option>\n";
				}
				echo "</select></p>\n";
			}
			echo "<div class=\"actionslist\">";
			echo "\t<input type=\"submit\" name=\"createextension\" value=\"{$lang['strok']}\" />\n";
			echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</div>";
		}
		echo "</form>\n";
	}

	/**
	 * Perform the extension creation
	 */
	function create_extension_ok() {
		global $lang, $emajdb, $_reload_browser;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			doDefault();
		} else {
			// recheck that emaj does not exist
			if (! $emajdb->isEnabled()) {
				$status = $emajdb->createEmajExtension($_POST['version']);
				if ($status == 0) {
					$_reload_browser = true;
					doDefault($lang['strcreateextensionok']);
				} else {
					doDefault('', $lang['strcreateextensionerr']);
				}
			} else {
				doDefault('', $lang['strcreateextensionerr']);
			}
		}
	}

	/**
	 * Prepare the extension update: ask for the version and the confirmation
	 */
	function update_extension() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database', 'emajenvir');

		$misc->printTitle($lang['strupdateemajextension']);

		$server_info = $misc->getServerInfo();

		// Count the number of event triggers and report if some event triggers are missing for postgres 9.5+
		$nbEventTrigger = $emajdb->getNumberEventTriggers();
		if (version_compare($server_info['pgVersion'], '9.5', '>=') && $nbEventTrigger < 3) {
			echo "<p>{$lang['strmissingeventtriggers']}</p>\n";
		}

		// build the array of usable emaj versions
		$availableVersions = $emajdb->getAvailableExtensionVersionsForUpdate();

		$pgMajorVersion = $server_info['pgMajorVersion'];
		$usableVersions = array();
		foreach($availableVersions as $v) {
			// filter unknown emaj versions (i.e. devel) or emaj version compatible with the current PG version
			if (!isset($xrefEmajPg[$v['target']]) ||
				(version_compare($pgMajorVersion, $xrefEmajPg[$v['version']]['minPostgresVersion'], '>=') &&
				 version_compare($pgMajorVersion, $xrefEmajPg[$v['version']]['maxPostgresVersion'], '<='))) {
				// block version update > = 4.2 if some event triggers are missing
				if ($v['target'] < '4.2.0' || $nbEventTrigger >= 3) {
					$usableVersions[] = $v['target'];
				}
			}
		}

		echo "<form action=\"emajenvir.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"update_extension_ok\" />\n";
		echo $misc->form;

		if (count($usableVersions) == 0) {
			// no emaj version is compatible with the PG version
			echo "<p>{$lang['strnocompatibleemajupdate']}</p>\n";
			echo "<div class=\"actionslist\">";
			echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</div>";
		} else {
			if (count($usableVersions) == 1) {
				// a single version is compatible with the PG version, so just display the information
				foreach($usableVersions as $v) {
					echo "<p>{$lang['strversion']}{$v}</p>\n";
					echo "<input type=\"hidden\" name=\"version\" value=\"". htmlspecialchars($v) . "\" />\n";
				}
			} else {
				// several versions are compatible with the PG version,
				// so display a select box so that the user can choose the version to install
				echo "<p>{$lang['strversion']}<select name=\"version\" id=\"version\">\n";
				foreach($usableVersions as $v)
					echo "\t<option value=\"", htmlspecialchars($v), "\" >", htmlspecialchars($v), "</option>\n";
				echo "</select></p>\n";
			}
			echo "<div class=\"actionslist\">";
			echo "\t<input type=\"submit\" name=\"updateextension\" value=\"{$lang['strok']}\" />\n";
			echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</div>";
		}
		echo "</form>\n";
	}

	/**
	 * Perform the extension update
	 */
	function update_extension_ok() {
		global $lang, $emajdb, $_reload_browser;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			doDefault();
		} else {
			$status = $emajdb->updateEmajExtension($_POST['version']);
			if ($status == 0) {
				$_reload_browser = true;
				doDefault($lang['strupdateextensionok']);
			} else {
				doDefault('', $lang['strupdateextensionerr']);
			}
		}
	}

	/**
	 * Prepare the extension drop: ask for confirmation
	 */
	function drop_extension() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database', 'emajenvir');

		$misc->printTitle($lang['strdropemajextension']);

		$nbGroups = $emajdb->getNbGroups();
		if ($nbGroups > 0) {
			$msg = sprintf($lang['strdropextensiongroupsexist'], $nbGroups);
			echo "<p><img src=\"{$misc->icon('Warning')}\" alt=\"Warning\" style=\"width: 20px;\"/> " . htmlspecialchars($msg) . "</p>\n";
		}

		echo "<p>{$lang['strconfirmdropextension']}</p>\n";

		echo "<form action=\"emajenvir.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"drop_extension_ok\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"dropextension\" value=\"{$lang['strok']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform the extension drop
	 */
	function drop_extension_ok() {
		global $lang, $emajdb, $_reload_browser;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			doDefault();
		} else {
			// recheck that emaj still exists
			if ($emajdb->isEnabled()) {
				$status = $emajdb->dropEmajExtension();
				if ($status == 0) {
					$_reload_browser = true;
					doDefault($lang['strdropextensionok']);
				} else {
					doDefault('', $lang['strdropextensionerr']);
				}
			} else {
				doDefault('', $lang['strdropextensionerr']);
			}
		}
	}

	/**
	 * Export a parameters configuration
	 */
	function export_parameters() {

		global $misc, $emajdb;

	// Build the JSON parameter configuration
		$paramConfig = $emajdb->exportParamConfig();

	// Generate a suggested local file name
		$server_info = $misc->getServerInfo();
		$fileName = "emaj_param_" . $server_info['desc'] . "_" . $_REQUEST['database'] . "_" . date("Ymd_His") . ".json";

	// Send it to the browser
		header('Content-Description: File Transfer');
		header('Content-Type: application/json');
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . strlen($paramConfig));
		print $paramConfig;
		exit;
	}

	/**
	 * Import a parameters configuration
	 */
	function import_parameters() {

		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database', 'emajenvir');

		if (($emajdb->isEmaj_Adm()) && $emajdb->getNumEmajVersion() >= 30300) {

			$misc->printTitle($lang['strimportparamconf']);

			// form to import a parameter configuration
			echo "<div>\n";
			echo "\t<form name=\"importparameters\" id=\"importparameters\" enctype=\"multipart/form-data\" method=\"POST\"";
			echo " action=\"emajenvir.php?action=import_parameters_ok&amp;{$misc->href}\">\n";
			echo "\t\t<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"2097152\">\n";
			echo "\t\t<label for=\"file-upload\" class=\"custom-file-upload\">{$lang['strselectfile']}</label>";
			echo "\t\t<p><input type=\"file\" id=\"file-upload\" name=\"file_name\"></p>\n";
			echo "\t\t<p><input type=\"checkbox\" name=\"replaceCurrent\" id=\"replaceCurrent\"/ checked>{$lang['strdeletecurrentparam']}";
			echo " <img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strdeletecurrentparaminfo']}\"/></p>\n";
			echo "\t\t<div class=\"actionslist\">";
			echo "\t\t\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /> \n";
			echo "\t\t\t<input type=\"submit\" name=\"sendfile\" value=\"{$lang['strimport']}\" disabled>";
			echo "\t\t<span id=\"selected-file\"></span>";
			echo "\t\t</div>\n";
			echo "\t\t<script>
				$(document).ready(
					function(){
						$('input:file').change(
							function(){
								if ($(this).val()) { $('input:submit').attr('disabled',false); } 
							}
							);
						$('#file-upload').bind('change',
							function(){
								var fileName = '';
								fileName = $(this).val();
								$('#selected-file').html(fileName.replace(/^.*\\\\/, \"\"));
							}
							);
					});
			</script>\n";

			echo "\t</form>\n";
			echo "</div>\n";
		} else {
		// bad emaj version or function not allowed to this user
			doDefault();
		}
	}

	/**
	 * Effectively import a parameters configuration
	 */
	function import_parameters_ok() {

		global $misc, $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			doDefault();
		} else {

		// Process the uploaded file

			// If the file is properly loaded,
			if (is_uploaded_file($_FILES['file_name']['tmp_name'])) {
				$jsonContent = file_get_contents($_FILES['file_name']['tmp_name']);
				$jsonStructure = json_decode($jsonContent, true);
			// ... contains a valid JSON structure,
				if (json_last_error()===JSON_ERROR_NONE) {

					// check that the json content is valid
					$errors = $emajdb->checkJsonParamConf($jsonContent);

					if ($errors->recordCount() == 0) {

						// No error has been detected in the json structure, so effectively import the parameter configuration
						$nbParam = $emajdb->importParamConfig($jsonContent, isSet($_POST['replaceCurrent']));
						if ($nbParam >= 0) {
							if (isSet($_POST['replaceCurrent'])) {
								$m = $lang['strnewconf'];
							} else {
								$m = $lang['strnewmodifiedconf'];
							}
							doDefault(sprintf($lang['strparamconfimported'], $m, $nbParam, $_FILES['file_name']['name']));
						} else {
							doDefault('', sprintf($lang['strparamconfigimporterr'], $_FILES['file_name']['name']));
						}
					} else {

						// The JSON structure contains errors. Display them

						$misc->printHeader('database', 'database', 'emajenvir');

						$misc->printTitle($lang['strimportparamconf']);
						echo "<p>" . sprintf($lang['strparamconfigimporterr'], $_FILES['file_name']['name']) . "</p>";

						$columns = array(
							'severity' => array(
								'title' => '',
								'field' => field('rpt_severity'),
								'type'	=> 'callback',
								'params'=> array('function' => 'renderMsgSeverity','align' => 'center'),
								'sorter' => false,
							),
							'message' => array(
								'title' => $lang['strdiagnostics'],
								'field' => field('rpt_message'),
							),
						);

						$actions = array ();
	
						$misc->printTable($errors, $columns, $actions, 'paramsconfchecks', null, null, array('sorter' => true, 'filter' => false));

						echo "<form action=\"emajenvir.php\" method=\"post\">\n";
						echo "<input type=\"hidden\" name=\"action\" value=\"import_parameters_ok\" />\n";
						echo $misc->form;
						echo "<div class=\"actionslist\">";
						echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strok']}\" />\n";
						echo "</div></form>\n";

					}
				} else {
					doDefault('', sprintf($lang['strnotjsonfile'], $_FILES['file_name']['name']));
				}
			} else {
				switch($_FILES['file_name']['error']){
					case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
					case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
						$errMsg = $lang['strimportfiletoobig'];
						break;
					case 3: //uploaded file was only partially uploaded
					case 4: //no file was uploaded
					case 0: //no error; possible file attack!
					default: //a default error, just in case!  :)
						$errMsg = $lang['strimporterror-uploadedfile'];
						break;
				}
				doDefault('', $errMsg);
			}
		}
	}

	/**
	 * Show the E-Maj environment characteristics of the database
	 */
	function doDefault($msg = '', $errMsg = '') {
		global $misc, $lang, $data, $emajdb;
		global $oldest_supported_emaj_version_num, $oldest_supported_emaj_version, $last_known_emaj_version_num;
		global $appVersion;
		global $paramValue, $defValParam;

		$misc->printHeader('database', 'database', 'emajenvir');

		$misc->printMsg($msg,$errMsg);

		//
		// Version section
		//
		$misc->printTitle($lang['strversions']);

		// display the postgres version
		$server_info = $misc->getServerInfo();
		echo "<p>{$lang['strpgversion']}{$server_info['pgVersion']}</p>\n";

		// check if E-Maj is installed in the current database
		$isEnabled = $emajdb->isEnabled();
		$isExtensionAvailable = $emajdb->isExtensionAvailable();

		if (! $isEnabled) {
			// emaj is not installed,
			// check if the extension is available
			if ($isExtensionAvailable)
				$msg = $lang['strextnotcreated'];
			else
				$msg = $lang['strextnotavailable'];

			if ($data->isSuperUser($server_info['username'])) {
				// display a message to the superuser and go on
				echo "<p>{$lang['strversion']}{$msg}</p>\n";
			} else {
				// display a message to the user and stop
				echo "<p>{$lang['strversion']}{$msg} {$lang['strcontactdba']}</p>\n";
				return;
			}
		} else {
			// emaj is installed,
			// check that the user has enought rights to continue
			if (! $emajdb->isAccessible()) {
				echo "<p>{$lang['strnogrant']}</p>\n";
				return;
			}

			// OK, now display the E-Maj version
			$emajVersion = $emajdb->getEmajVersion();
			$numEmajVersion = $emajdb->getNumEmajVersion();

			$isExtension = $emajdb->isExtension();
			if ($isExtension) {
				$installationMode = $lang['strasextension'];
			} else {
				$installationMode = $lang['strasscript'];
			}
			echo "<p>{$lang['strversion']}$emajVersion ({$installationMode})</p>\n";

			// check if the emaj version is not too old for this emaj_web
			if ($numEmajVersion < $oldest_supported_emaj_version_num) {
				echo "<p>" . sprintf($lang['strtooold'],$emajVersion,$oldest_supported_emaj_version) . "</p>\n";
				return;
			}

			// if there are more recent emaj or emaj_web versions, tell it
			if ($numEmajVersion <> 999999) {
				if ($numEmajVersion < $last_known_emaj_version_num) {
					if ($data->isSuperUser($server_info['username']))
						echo "<p>{$lang['strversionmorerecent']}</p>\n";
					else
						echo "<p>{$lang['strversionmorerecent']} {$lang['strcontactdba']}</p>\n";
				}
				if ($numEmajVersion > $last_known_emaj_version_num) {
					echo "<p>{$lang['strwebversionmorerecent']} {$lang['strcontactdba']}</p>\n";
				}
			}

			// if the version is <devel>, raise a warning
			if ($numEmajVersion == 999999) {
				echo "<p><img src=\"{$misc->icon('Warning')}\" alt=\"Warning\" style=\"width: 20px;\"/> " . htmlspecialchars($lang['strwarningdevel']) . "</p>\n";
			}
		}
		// display the Emaj_web version
		echo "<p>{$lang['stremajwebversion']}$appVersion</p>";
		if (!class_exists('IntlDateFormatter')) {
			echo "<p><img src=\"{$misc->icon('Warning')}\" alt=\"Warning\" style=\"width: 20px;\"/> " . htmlspecialchars($lang['strmissingIntlDateFormatter']) . "</p>\n";
		}

		//
		// Extension management section (for superusers only)
		//

		if ($data->isSuperUser($server_info['username']) && $isExtensionAvailable) {
			$navlinks = array();
			if (! $isEnabled || $isExtension) {
				echo "<hr/>\n";
				$misc->printTitle($lang['strextensionmngt']);
			}

			if (! $isEnabled) {
				// the extension is not yet created
				// Add a button to create the extension
				$navlinks['createextension'] = array (
					'content' => $lang['strcreateextension'],
					'attr'=> array (
						'href' => array (
							'url' => "emajenvir.php",
							'urlvars' => array(
								'action' => 'create_extension'
							)
						)
					),
				);

			} else {

				if ($isExtension) {
				// emaj exists as an extension
					// Can we update it with a more recent version ?
					if ($emajdb->areThereVersionsToUpdate()) {
						// Add a button to update the extension
						$navlinks['updateextension'] = array (
							'content' => $lang['strupdateextension'],
							'attr'=> array (
								'href' => array (
									'url' => "emajenvir.php",
									'urlvars' => array(
										'action' => 'update_extension'
									)
								)
							),
						);
					}

					// Can we drop it ?
					// Add a button to drop the extension
					$navlinks['dropextension'] = array (
						'content' => $lang['strdropextension'],
						'attr'=> array (
							'href' => array (
								'url' => "emajenvir.php",
								'urlvars' => array(
									'action' => 'drop_extension'
								)
							)
						),
					);
				}
			}
			// print the buttons list, if it contains at least 1 button
			if ($navlinks != array())
				$misc->printLinksList($navlinks, 'buttonslist');
		}

		//
		// General characteristics of the E-Maj environment
		//

		if ($isEnabled) {
			if ($emajdb->isEmaj_Adm()) {
				echo "<hr/>\n";
				$misc->printTitle($lang['strcharacteristics']);
				echo "<p>".sprintf($lang['strdiskspace'],$emajdb->getEmajSize())."</p>\n";
			}

		//
		// E-Maj environment checking
		//

			echo "<hr/>\n";
			$misc->printTitle($lang['strchecking']);

			$messages = $emajdb->checkEmaj();

			$columns = array(
				'severity' => array(
					'title' => '',
					'field' => field('rpt_severity'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderMsgSeverity','align' => 'center'),
					'sorter' => false,
				),
				'message' => array(
					'title' => $lang['strdiagnostics'],
					'field' => field('rpt_message'),
				),
			);

			$actions = array ();

			$misc->printTable($messages, $columns, $actions, 'checks', null, null, array('sorter' => true, 'filter' => false));

		//
		// E-Maj parameters managed with the emaj_param table
		//

			echo "<hr/>\n";
			$misc->printTitle($lang['strextparams']);

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
			echo "<h4>{$lang['strpargeneral']}</h4>\n";

			echo "<div class=\"form-container-param\">\n";
			displayOneParameter('history_retention', $lang['strparhistret'], $lang['strparhistretinfo']);
			displayOneParameter('dblink_user_password', $lang['strpardblinkcon'], $lang['strpardblinkconinfo']);

			if ($emajdb->getNumEmajVersion() >= 30000) {
				# add line feed if several columns added to the log tables
				displayOneParameter('alter_log_table', $lang['strparalterlog'], $lang['strparalterloginfo']);
			}
			echo "</div>\n";

			echo "<div>\n";
			echo "<h4>{$lang['strparcostmodel']}</h4>\n";
			echo "</div>\n";

			echo "<div class=\"form-container-param\">\n";
			displayOneParameter('fixed_step_rollback_duration', $lang['strparfixedstep'], $lang['strparfixedstepinfo']);
			displayOneParameter('fixed_dblink_rollback_duration', $lang['strparfixeddblink'], $lang['strparfixeddblinkinfo']);
			displayOneParameter('fixed_table_rollback_duration', $lang['strparfixedrlbktbl'], $lang['strparfixedrlbktblinfo']);
			displayOneParameter('avg_row_rollback_duration', $lang['strparavgrowrlbk'], $lang['strparavgrowrlbkinfo']);
			displayOneParameter('avg_row_delete_log_duration', $lang['strparavgrowdel'], $lang['strparavgrowdelinfo']);
			displayOneParameter('avg_fkey_check_duration', $lang['strparavgfkcheck'], $lang['strparavgfkcheckinfo']);

			echo "</div>\n";

			if (($emajdb->isEmaj_Adm()) && $emajdb->getNumEmajVersion() >= 30300) {

				// Prepare and generate the export and import buttons list
				$navlinks = array();
				$navlinks['exportparameters'] = array (
					'content' => $lang['strexport'],
					'attr'=> array (
						'href' => array (
							'url' => "emajenvir.php",
							'urlvars' => array(
								'action' => 'export_parameters'
							)
						)
					),
				);
				$navlinks['importparameters'] = array (
					'content' => $lang['strimport'],
					'attr'=> array (
						'href' => array (
							'url' => "emajenvir.php",
							'urlvars' => array(
								'action' => 'import_parameters'
							)
						)
					),
				);
				$misc->printLinksList($navlinks, 'buttonslist');
			}
		}
	}

// The export_parameters action only builds and downloads the configuration file, but do not resend the main page
	if ($action == 'export_parameters') {
		export_parameters();
		exit;
	}

// Other actions
	$misc->printHtmlHeader($lang['strenvironment']);
	$misc->printBody();

	switch ($action) {
		case 'create_extension':
			create_extension();
			break;
		case 'create_extension_ok':
			create_extension_ok();
			break;
		case 'drop_extension':
			drop_extension();
			break;
		case 'drop_extension_ok':
			drop_extension_ok();
			break;
		case 'import_parameters':
			import_parameters();
			break;
		case 'import_parameters_ok':
			import_parameters_ok();
			break;
		case 'update_extension':
			update_extension();
			break;
		case 'update_extension_ok':
			update_extension_ok();
			break;
		default:
			doDefault();
			break;
	}

	$misc->printFooter();

?>
