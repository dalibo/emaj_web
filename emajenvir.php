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
			$icon = 'Delete';
		} elseif ($val == '3') {
			$icon = 'EmajWarning';
		} elseif ($val == '4') {
			$icon = 'CheckConstraint';
		} else {
			return '?';
		}
		return "<img src=\"".$misc->icon($icon)."\" alt=\"\" />";
	}

	function displayOneParameter($param, $label, $info) {
	// This function displays the value of a sinigle parameter in the parameter section of the main page.
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

		$misc->printTitle($lang['emajcreateemajextension']);

		// build the array of usable emaj versions
		$server_info = $misc->getServerInfo();
		$availableVersions = $emajdb->getAvailableExtensionVersions();

		$usableVersions = array();
		foreach($availableVersions as $v) {
			if (!isset($xrefEmajPg[$v['version']]) ||
				($server_info['pgVersion'] >= $xrefEmajPg[$v['version']]['minPostgresVersion'] &&
				 $server_info['pgVersion'] <= $xrefEmajPg[$v['version']]['maxPostgresVersion'])) {
				// if the emaj version is known in the xref and is compatible with the current PG version, keep it
				// if the emaj version is unknown, keep it too
				// otherwise, discard it
				$usableVersions[] = $v['version'];
			}
		}

		echo "<form action=\"emajenvir.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"create_extension_ok\" />\n";
		echo $misc->form;

		if (count($usableVersions) == 0) {
			echo "<p>{$lang['emajnocompatibleemajversion']}</p>\n";
		} elseif (count($usableVersions) == 1) {
			// a single version is available, so just display the information
			foreach($usableVersions as $v) {
				echo "<p>{$lang['emajversion']}{$v}</p>\n";
				echo "<p><input type=\"hidden\" name=\"version\" value=\"". htmlspecialchars($v) . "\" />\n";
			}
			echo "<input type=\"submit\" name=\"createextension\" value=\"{$lang['strok']}\" />\n";
		} else {
			// several versions are available,
			// display a select box so that the user can choose the version to install
			echo "<p>{$lang['emajversion']}<select name=\"version\" id=\"version\">\n";
			foreach($usableVersions as $v) {
				echo "\t<option value=\"", htmlspecialchars($v), "\" >", htmlspecialchars($v), "</option>\n";
			}
			echo "</select></p>\n";
			echo "<input type=\"submit\" name=\"createextension\" value=\"{$lang['strok']}\" />\n";
		}
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
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
					doDefault($lang['emajcreateextensionok']);
				} else {
					doDefault('', $lang['emajcreateextensionerr']);
				}
			} else {
				doDefault('', $lang['emajcreateextensionerr']);
			}
		}
	}

	/**
	 * Prepare the extension update: ask for the version and the confirmation
	 */
	function update_extension() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database', 'emajenvir');

		$misc->printTitle($lang['emajupdateemajextension']);

		// build the array of usable emaj versions
		$server_info = $misc->getServerInfo();
		$availableVersions = $emajdb->getAvailableExtensionVersionsForUpdate();

		$usableVersions = array();
		foreach($availableVersions as $v) {
			if (!isset($xrefEmajPg[$v['target']]) ||
				($server_info['pgVersion'] >= $xrefEmajPg[$v['target']]['minPostgresVersion'] &&
				 $server_info['pgVersion'] <= $xrefEmajPg[$v['target']]['maxPostgresVersion'])) {
				// if the emaj version is known in the xref and is compatible with the current PG version, keep it
				// if the emaj version is unknown, keep it too
				// otherwise, discard it
				$usableVersions[] = $v['target'];
			}
		}

		echo "<form action=\"emajenvir.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"update_extension_ok\" />\n";
		echo $misc->form;

		if (count($usableVersions) == 0) {
			echo "<p>{$lang['emajnocompatibleemajupdate']}</p>\n";
		} elseif (count($usableVersions) == 1) {
			// a single version is available, so just display the information
			foreach($usableVersions as $v) {
				echo "<p>{$lang['emajversion']}{$v}</p>\n";
				echo "<p><input type=\"hidden\" name=\"version\" value=\"". htmlspecialchars($v) . "\" />\n";
			}
			echo "<input type=\"submit\" name=\"updateextension\" value=\"{$lang['strok']}\" />\n";
		} else {
			// several versions are available
			// display a select box so that the user can choose the version to install
			echo "<p>{$lang['emajversion']}<select name=\"version\" id=\"version\">\n";
			foreach($usableVersions as $v)
				echo "\t<option value=\"", htmlspecialchars($v), "\" >", htmlspecialchars($v), "</option>\n";
			echo "</select></p>\n";
			echo "<input type=\"submit\" name=\"updateextension\" value=\"{$lang['strok']}\" />\n";
		}
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
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
				doDefault($lang['emajupdateextensionok']);
			} else {
				doDefault('', $lang['emajupdateextensionerr']);
			}
		}
	}

	/**
	 * Prepare the extension drop: ask for confirmation
	 */
	function drop_extension() {
		global $misc, $lang;

		$misc->printHeader('database', 'database', 'emajenvir');

		$misc->printTitle($lang['emajdropemajextension']);

		echo "<p>{$lang['emajconfirmdropextension']}</p>\n";
		echo "<form action=\"emajenvir.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"drop_extension_ok\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"dropextension\" value=\"{$lang['strok']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
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
			// recheck that emaj is always here and that no tables group exist before dropping the extension
			if ($emajdb->isEnabled() && $emajdb->getNbGroups() == 0) {
				$status = $emajdb->dropEmajExtension();
				if ($status == 0) {
					$_reload_browser = true;
					doDefault($lang['emajdropextensionok']);
				} else {
					doDefault('', $lang['emajdropextensionerr']);
				}
			} else {
				doDefault('', $lang['emajdropextensionerr']);
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

			$misc->printTitle($lang['emajimportparamconf']);

			// form to import a parameter configuration
			echo "<div>\n";
			echo "\t<form name=\"importparameters\" id=\"importparameters\" enctype=\"multipart/form-data\" method=\"POST\"";
			echo " action=\"emajenvir.php?action=import_parameters_ok&amp;{$misc->href}\">\n";
			echo "\t\t<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"2097152\">\n";
			echo "\t\t<label for=\"file-upload\" class=\"custom-file-upload\">${lang['emajselectfile']}</label>";
			echo "\t\t<p><input type=\"file\" id=\"file-upload\" name=\"file_name\"></p>\n";
			echo "\t\t<p><input type=\"checkbox\" name=\"replaceCurrent\" id=\"replaceCurrent\"/ checked>${lang['emajdeletecurrentparam']}";
			echo " <img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"${lang['emajdeletecurrentparaminfo']}\"/></p>\n";
			echo "\t\t<p><input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /> \n";
			echo "\t\t<input type=\"submit\" name=\"sendfile\" value=\"${lang['strimport']}\" disabled>";
			echo "\t\t<span id=\"selected-file\"></span></p>\n";
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
								$m = $lang['emajnewconf'];
							} else {
								$m = $lang['emajnewmodifiedconf'];
							}
							doDefault(sprintf($lang['emajparamconfimported'], $m, $nbParam, $_FILES['file_name']['name']));
						} else {
							doDefault('', sprintf($lang['emajparamconfigimporterr'], $_FILES['file_name']['name']));
						}
					} else {

						// The JSON structure contains errors. Display them

						$misc->printHeader('database', 'database', 'emajenvir');

						$misc->printTitle($lang['emajimportparamconf']);
						echo "<p>" . sprintf($lang['emajparamconfigimporterr'], $_FILES['file_name']['name']) . "</p>";

						$columns = array(
							'severity' => array(
								'title' => '',
								'field' => field('rpt_severity'),
								'type'	=> 'callback',
								'params'=> array('function' => 'renderMsgSeverity','align' => 'center'),
								'sorter' => false,
							),
							'message' => array(
								'title' => $lang['emajdiagnostics'],
								'field' => field('rpt_message'),
							),
						);

						$actions = array ();
	
						$misc->printTable($errors, $columns, $actions, 'paramsconfchecks', null, null, array('sorter' => true, 'filter' => false));

						echo "<form action=\"emajenvir.php\" method=\"post\">\n";
						echo "<p><input type=\"hidden\" name=\"action\" value=\"import_parameters_ok\" />\n";
						echo $misc->form;
						echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strok']}\" /></p>\n";
						echo "</form>\n";

					}
				} else {
					doDefault('', sprintf($lang['emajnotjsonfile'], $_FILES['file_name']['name']));
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
		global $paramValue, $defValParam;

		$misc->printHeader('database', 'database', 'emajenvir');

		$misc->printMsg($msg,$errMsg);

		//
		// Version section
		//
		$misc->printTitle($lang['emajversions']);

		// display the postgres version
		$server_info = $misc->getServerInfo();
		preg_match('/PostgreSQL (.*)/',$server_info['platform'], $pgVersion);
		echo "<p>{$lang['emajpgversion']}{$pgVersion[1]}</p>\n";

		// check if E-Maj is installed in the current database
		$isEnabled = $emajdb->isEnabled();
		if (! $isEnabled) {
			// emaj is not installed,
			// check if the extension is available
			if ($emajdb->isExtensionAvailable())
				$msg = $lang['emajextnotcreated'];
			else
				$msg = $lang['emajextnotavailable'];

			if ($data->isSuperUser($server_info['username'])) {
				// display a message to the superuser and go on
				echo "<p>{$lang['emajversion']}{$msg}</p>\n";
			} else {
				// display a message to the user and stop
				echo "<p>{$lang['emajversion']}{$msg} {$lang['emajcontactdba']}</p>\n";
				return;
			}
		} else {
			// emaj is installed,
			// check that the user has enought rights to continue
			if (! $emajdb->isAccessible()) {
				echo "<p>{$lang['emajnogrant']}</p>\n";
				return;
			}

			// OK, now display the E-Maj version
			$isExtension = $emajdb->isExtension();
			if ($isExtension) {
				$installationMode = $lang['emajasextension'];
			} else {
				$installationMode = $lang['emajasscript'];
			}
			echo "<p>{$lang['emajversion']}{$emajdb->getEmajVersion()} ({$installationMode})</p>\n";

			// check if the emaj version is not too old for this emaj_web
			if ($emajdb->getNumEmajVersion() < $oldest_supported_emaj_version_num) {
				echo "<p>" . sprintf($lang['emajtooold'],$emajdb->getEmajVersion(),$oldest_supported_emaj_version) . "</p>\n";
				return;
			} else {

				// if there are more recent emaj or emaj_web versions, tell it
				if ($emajdb->getNumEmajVersion() <> 999999) {
					if ($emajdb->getNumEmajVersion() < $last_known_emaj_version_num) {
						if ($data->isSuperUser($server_info['username']))
							echo "<p>{$lang['emajversionmorerecent']}</p>\n";
						else
							echo "<p>{$lang['emajversionmorerecent']} {$lang['emajcontactdba']}</p>\n";
					}
					if ($emajdb->getNumEmajVersion() > $last_known_emaj_version_num) {
						echo "<p>{$lang['emajwebversionmorerecent']} {$lang['emajcontactdba']}</p>\n";
					}
				}
			}
		}

		if (($data->isSuperUser($server_info['username']))) {
		//
		// Extension management section (for superusers only)
		//
			$navlinks = array();
			if (! $isEnabled || $isExtension) {
				echo "<hr/>\n";
				$misc->printTitle($lang['emajextensionmngt']);
			}

			if (! $isEnabled) {
				// the extension is not yet created
				// Add a button to create the extension
				$navlinks['createextension'] = array (
					'content' => $lang['emajcreateextension'],
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
							'content' => $lang['emajupdateextension'],
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
					if ($emajdb->getNbGroups() > 0) {
						echo "<p>" . $lang['emajdropextensiongroupsexist'] . "</p>\n";
					} else {
						// Add a button to drop the extension
						$navlinks['dropextension'] = array (
							'content' => $lang['emajdropextension'],
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
			}
			// print the buttons list, if it contains at least 1 button
			if ($navlinks != array())
				$misc->printLinksList($navlinks, 'buttonslist');
		}

		if ($isEnabled) {
		//
		// General characteristics of the E-Maj environment
		//
			if ($emajdb->isEmaj_Adm()) {
				echo "<hr/>\n";
				$misc->printTitle($lang['emajcharacteristics']);
				echo "<p>".sprintf($lang['emajdiskspace'],$emajdb->getEmajSize())."</p>\n";
			}

		//
		// E-Maj environment checking
		//
			echo "<hr/>\n";
			$misc->printTitle($lang['emajchecking']);

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
					'title' => $lang['emajdiagnostics'],
					'field' => field('rpt_message'),
				),
			);

			$actions = array ();

			$misc->printTable($messages, $columns, $actions, 'checks', null, null, array('sorter' => true, 'filter' => false));

		//
		// E-Maj parameters managed with the emaj_param table
		//

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
	$misc->printHtmlHeader($lang['emajenvironment']);
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
