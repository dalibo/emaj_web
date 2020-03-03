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
		if (preg_match("/^[Nn]o error /",$val)) {
			$icon = 'CheckConstraint';
		} elseif (preg_match("/^Warning/",$val)) {
			$icon = 'EmajWarning';
		} else {
			$icon = 'CorruptedDatabase';
		}
		return "<img src=\"".$misc->icon($icon)."\" style=\"vertical-align:bottom;\" />&nbsp;" . $val;
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
			echo "\t\t<script type=\"text/javascript\">
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
							'message' => array(
								'title' => $lang['emajdiagnostics'],
								'field' => field('chk_message'),
							),
						);

						$actions = array ();
	
						$misc->printTable($errors, $columns, $actions, 'checks', null, null, array('sorter' => true, 'filter' => false));

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
		global $misc, $lang, $emajdb;
		global $oldest_supported_emaj_version_num, $oldest_supported_emaj_version, $last_known_emaj_version_num;
		global $paramValue, $defValParam;

		$misc->printHeader('database', 'database', 'emajenvir');

		$misc->printMsg($msg,$errMsg);

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
			echo "<div class=\"form-param-value\">{$value}</div>\n";
		} else {
			// The parameter has its default value
			echo "<div class=\"form-param-def-value\">${defValParam[$param]}&nbsp<sup>(def)</sup></div>\n";
		}

		if ($emajdb->isEmaj_Adm()) {
//TODO: Modify button to insert
			echo "<div class=\"form-button-param\">"
//				. $lang['strupdate']
				. "</div>\n";
		} else {
			echo "<div class=\"form-button-param\"></div>\n";
		}
	}
		//
		// Version section
		//
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
		//
		// General characteristics of the E-Maj environment
		//
			echo "<hr/>\n";
			$misc->printTitle($lang['emajcharacteristics']);
			if ($emajdb->isEmaj_Adm()) {
				echo "<p>".sprintf($lang['emajdiskspace'],$emajdb->getEmajSize())."</p>\n";
			}

		//
		// E-Maj environment checking
		//
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

			if (($emajdb->isEmaj_Adm()) && $emajdb->getNumEmajVersion() >= 30300) {

				// form to export the parameter configuration
				echo "<div style=\"float:left; margin:20px\">\n";
				echo "\t<form name=\"exportparameters\" id=\"exportparameters\" enctype=\"multipart/form-data\" method=\"POST\"";
				echo " action=\"emajenvir.php?action=export_parameters&amp;{$misc->href}\">\n";
				echo "\t\t<input type=\"submit\" name=\"exportButton\" value=\"${lang['strexport']}\">\n";
				echo "\t</form>\n";
				echo "</div>\n";

				// form to import a parameter configuration
				echo "<div style=\"margin:20px\">\n";
				echo "\t<form name=\"importparameters\" id=\"importparameters\" method=\"POST\"";
				echo " action=\"emajenvir.php?action=import_parameters&amp;{$misc->href}\">\n";
				echo "\t\t<input type=\"submit\" name=\"importButton\" value=\"${lang['strimport']}\">\n";
				echo "\t</form>\n";
				echo "</div>\n";

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
		case 'import_parameters':
			import_parameters();
			break;
		case 'import_parameters_ok':
			import_parameters_ok();
			break;
		default:
			doDefault();
			break;
	}

	$misc->printFooter();

?>
