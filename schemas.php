<?php

	/*
	 * Manage schemas in a database
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	global $nbGroups;									// used to display or hide actions in the tblseqPre callback function

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	// Function to modify actions list for tables and sequences
	function tblseqPre(&$rowdata, $actions) {
		global $nbGroups;

		// if E-Maj schema or no action, return
		if (!isset($rowdata->fields['rel_group'])) return $actions;
		if (!isset($actions['assign'])) return $actions;

		if ($rowdata->fields['rel_group'] != NULL) {
			// disable 'assign' if the table already belongs to a group
			$actions['assign']['disable'] = true;
		} else {
			// otherwise, disable 'remove', 'modify' (if a table) and 'update'
			$actions['move']['disable'] = true;
			if (isset($actions['modify']))
				$actions['modify']['disable'] = true;
			$actions['remove']['disable'] = true;
		};
		// disable also 'assign' for unsupported object type or if no group exists
		if (($rowdata->fields['relkind'] != 'r+' and $rowdata->fields['relkind'] != 'S')
			|| $nbGroups == 0) {
			$actions['assign']['disable'] = true;
		}
		// disable also 'move' if only 1 group exists
		if ($nbGroups < 2) {
			$actions['move']['disable'] = true;
		}
		return $actions;
	}

	// Callback function to modify the schema type column content
	// It replaces the database value by an icon
	function renderIsEmaj($val) {
		global $misc, $lang;
		if ($val == 't') {
			$icon = $misc->icon('EmajIcon');
			$alt = $lang['stremajschema'];
			return "<img src=\"{$icon}\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		}
		return;
	}

/********************************************************************************************************
 * Other elementary functions
 *******************************************************************************************************/

	/**
	 * Process the click on the <cancel> button.
	 */
	function processCancelButton() {
		global $misc;

		// Call the schemas list display back.
		if (isset($_POST['cancel'])) {
			list_schemas();
			$misc->printFooter();
			exit();
		}
	}

	/**
	 * Process the multiactions list.
	 * It returns the number of relations (tables or sequences), the simple relations list the html formatted list,
	 *   and the group names list if the groups processing is requested.
	 */
	function processMultiActions($array, $relkind, $withGroup = false) {
		global $lang;

		$nbRelations = count($array);
		$relationsList = '';
		$groupsList = '';
		$htmlList = "<div class=\"longlist\"><ul>\n";
		foreach($array as $t) {
			$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
			$relationsList .= $a[$relkind] . ', ';
			$htmlList .= "\t<li>";
			if ($withGroup) {
				$htmlList .= sprintf($lang['strthetblseqingroup'], $a[$relkind], $a['group']);
				if (strpos($groupsList, $a['group'] . ', ') === false) {
					$groupsList .= $a['group'] . ', ';
				}
			} else {
				$htmlList .= $a[$relkind];
			}
			$htmlList .= "</li>\n";
		}
		$groupsList = substr($groupsList, 0, strlen($groupsList) - 2);
		$relationsList = substr($relationsList, 0, strlen($relationsList) - 2);
		$htmlList .= "</ul></div>\n";
		return array($nbRelations, $relationsList, $htmlList, $groupsList);
	}

	/**
	 * Check that groups still exists
	 */
	function recheckGroups($groupsList, $errMsgAction) {
		global $lang, $emajdb, $_reload_browser, $misc;

		// Check the groups existence
		$missingGroups = $emajdb->missingGroups($groupsList);
		if ($missingGroups->fields['nb_groups'] > 0) {
			if ($missingGroups->fields['nb_groups'] == 1)
				// One group doesn't exist anymore
				list_schemas('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupmissing'], htmlspecialchars($missingGroups->fields['groups_list'])));
			else
				// Several groups do not exist anymore
				list_schemas('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupsmissing'], $missingGroups->fields['nb_groups'], htmlspecialchars($missingGroups->fields['groups_list'])));
			$_reload_browser = true;
			$misc->printFooter();
			exit();
		}
	}

	/**
	 * Check that a supplied mark name is valid for one or several groups.
	 * It returns the mark name, modified with resolved % characters, if any.
	 * If the mark is not valid or is already known by any groups, it directly branches to the calling page with an error message.
	 */
	function checkNewMarkGroups($groupsList, $mark, $errMsgAction) {
		global $emajdb, $lang, $misc;

		// Check the forbidden values.
		if ($mark == '' or $mark == 'EMAJ_LAST_MARK') {
			$errorMessage = sprintf($lang['strinvalidmark'], htmlspecialchars($mark));
			list_schemas('', $errMsgAction . '<br>' . $errorMessage);
			$misc->printFooter();
			exit();
		}

		// Resolve the mark name. Replace the % characters by the time of day, in format 'HH24.MI.SS.MS'.
		$finalMarkName = str_replace('%', date('H.i.s.') . substr(microtime(),2,3), $mark);

		// Check the new mark doesn't already exist for the groups, if requested.
		$errorGroups = $emajdb->knownMarkGroups($groupsList, $finalMarkName);
		$errorMessage = '';
		if ($errorGroups->fields['nb_groups'] == 1) {
			// The mark already exists for one group
			$errorMessage = sprintf($lang['strduplicatemarkgroup'], htmlspecialchars($mark), htmlspecialchars($errorGroups->fields['groups_list']));
		}
		if ($errorGroups->fields['nb_groups'] > 1) {
			// The mark already exist for several groups
			$errorMessage = sprintf($lang['strduplicatemarkgroups'], htmlspecialchars($mark),
									$errorGroups->fields['nb_groups'], htmlspecialchars($errorGroups->fields['groups_list']));
		}
		if ($errorMessage != '') {
			list_schemas('', $errMsgAction . '<br>' . $errorMessage);
			$misc->printFooter();
			exit();
		}

		return $finalMarkName;
	}

	/**
	 * Check that a set of tables or sequences in a schema still exists.
	 * Tables and sequences that are assigned to tables group are protected by the main event trigger. But not the others.
	 */
	function checkRelations($schema, $tblSeqsList, $relKind, $errMsgAction) {
		global $emajdb, $lang, $misc;

		// Check the schema already exists
		if (! $emajdb->existsSchema($schema)) {
			$errorMessage = sprintf($lang['strschemamissing'], htmlspecialchars($schema));
			list_schemas('', $errMsgAction . '<br>' . $errorMessage);
			$_reload_browser = true;
			$misc->printFooter();
			exit();
		}

		// Check all relations already exist.
		$errorMessage = '';
		$missingTblSeqs = $emajdb->missingTblSeqs($schema, $tblSeqsList, $relKind);
		if ($missingTblSeqs->fields['nb_tblseqs'] > 0) {
			if ($missingTblSeqs->fields['nb_tblseqs'] == 1)
				// One table/sequence doesn't exist anymore
				if ($relKind == 'table') {
					$errorMessage = sprintf($lang['strtablemissing'], htmlspecialchars($schema), htmlspecialchars($missingTblSeqs->fields['tblseqs_list']));
				} else {
					$errorMessage = sprintf($lang['strsequencemissing'], htmlspecialchars($schema), htmlspecialchars($missingTblSeqs->fields['tblseqs_list']));
				}
			else
				// Several tables/sequences do not exist anymore
				if ($relKind == 'table') {
					$errorMessage = sprintf($lang['strtablesmissing'], $missingTblSeqs->fields['nb_tblseqs'], htmlspecialchars($missingTblSeqs->fields['tblseqs_list']));
				} else {
					$errorMessage = sprintf($lang['strsequencesmissing'], $missingTblSeqs->fields['nb_tblseqs'], htmlspecialchars($missingTblSeqs->fields['tblseqs_list']));
				}
		}
		if ($errorMessage != '') {
			list_schemas('', $errMsgAction . '<br>' . $errorMessage);
			$misc->printFooter();
			exit();
		}

		return;
	}

/********************************************************************************************************
 * Main functions displaying pages
 *******************************************************************************************************/

	/**
	 * Show the list of schemas in the database
	 * and the tables and sequences lists if a schema has already been selected
	 */
	function list_schemas($msg = '', $errMsg = '', $prevSchema = '') {
		global $data, $misc, $conf, $emajdb, $lang, $nbGroups;

		if (!isset($_REQUEST['schema'])) $_REQUEST['schema'] = $prevSchema;
		if (is_array($_REQUEST['schema'])) $_REQUEST['schema'] = $_REQUEST['schema'][0];

		// If a schema has been selected, check it still exists
		if (isset($_REQUEST['schema']) && $_REQUEST['schema'] != '') {
			if (! $emajdb->existsSchema($_REQUEST['schema'])) {
				// If the schema doesn't exist anymore, recall the function with an error message and reload the browser
				$errorMessage = sprintf($lang['strschemamissing'], htmlspecialchars($_REQUEST['schema']));
				unset($_REQUEST['schema']);
				list_schemas('', $errorMessage);
				$_reload_browser = true;
				$misc->printFooter();
				exit();
			}
		}

		if (isset($_REQUEST['schema']) && $_REQUEST['schema'] != '') {	// the trail differs if a schema is selected
			$misc->printHeader('schema', 'database', 'schemas');
		} else {
			$misc->printHeader('database', 'database', 'schemas');
		};

		// Get the schemas list
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
			$schemas = $emajdb->getAllSchemas();
		} else {
			$schemas = $data->getSchemas();
		}

		$misc->printMsg($msg,$errMsg);
		$misc->printTitle($lang['strallschemas'], $misc->buildTitleRecordsCounter($schemas));

		$columns = array(
			'schema' => array(
				'title' => $lang['strschema'],
				'field' => field('nspname'),
				'url'   => "schemas.php?action=list_schemas&amp;back=define&amp;{$misc->href}&amp;",
				'vars'  => array('schema' => 'nspname'),
			),
		);
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
			$columns = array_merge($columns, array(
				'isemaj' => array(
					'title' => $lang['strisemaj'],
					'field' => field('nspisemaj'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderIsEmaj','align' => 'center'),
					'filter' => false,
					'sorter_text_extraction' => 'img_alt',
				),
			));
		}
		$columns = array_merge($columns, array(
			'owner' => array(
				'title' => $lang['strowner'],
				'field' => field('nspowner'),
			),
			'comment' => array(
				'title' => $lang['strcomment'],
				'field' => field('nspcomment'),
				'type' => 'spanned',
				'params'=> array(
						'cliplen' => 32,
						'class' => 'tooltip right-aligned-tooltip',
						),
			),
		));

		$actions = array();

		$misc->printTable($schemas, $columns, $actions, 'schemas-schemas', $lang['strnoschemas'], null, array('sorter' => true, 'filter' => true));

		// Tables and séquences for the selected schema, if any

		if (isset($_REQUEST['schema']) && $_REQUEST['schema'] != '') {

			// is it an E-Maj schema ?
			$isEmajSchema = false;
			if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
				foreach ($schemas as $schema) {
					if ($schema['nspname'] == $_REQUEST['schema'] && $schema['nspisemaj'] == 't') {
						$isEmajSchema = true;
					}
				}
			}
			// E-Maj attributes and actions to manage ?
			$emajAttributesToManage = ($emajdb->isEnabled() && $emajdb->isAccessible() && ! $isEmajSchema);

			// get the number of created groups (needed to display or hide some actions)
			if ($emajdb->isAccessible())
				$nbGroups = $emajdb->getNbGroups();
			else
				$nbGroups = 0;

			$urlvars = $misc->getRequestVars();

			// Display the tables list
			echo "<a id=\"tables\">&nbsp;</a>\n";

			if ($emajAttributesToManage) {
				$tables = $emajdb->getTables($_REQUEST['schema']);
			} else {
				$tables = $data->getTables();
			}

			$misc->printTitle(sprintf($lang['strtableslist'], $_REQUEST['schema']), $misc->buildTitleRecordsCounter($tables));

			$columns = array(
				'table' => array(
					'title' => $lang['strtable'],
					'field' => field('relname'),
					'url'	=> "tblproperties.php?subject=table&amp;{$misc->href}&amp;",
					'vars'  => array('table' => 'relname'),
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
			);
			if ($emajAttributesToManage) {
				$columns = array_merge($columns, array(
					'group' => array(
						'title' => $lang['strgroup'],
						'field' => field('rel_group'),
					),
					'priority' => array(
						'title' => $lang['strpriority'],
						'field' => field('rel_priority'),
						'params'=> array('align' => 'center'),
					),
					'logdattsp' => array(
						'upper_title' => $lang['strtablespace'],
						'upper_title_colspan' => 2,
						'title' => $lang['strlogtables'],
						'field' => field('rel_log_dat_tsp'),
					),
					'logidxtsp' => array(
						'title' => $lang['strlogindexes'],
						'field' => field('rel_log_idx_tsp'),
					),
				));
			}
			$columns = array_merge($columns, array(
				'owner' => array(
					'title' => $lang['strowner'],
					'field' => field('relowner'),
				),
				'tablespace' => array(
					'title' => $lang['strtablespace'],
					'field' => field('tablespace')
				),
				'tuples' => array(
					'title' => $lang['strestimatedrowcount'],
					'field' => field('reltuples'),
					'type'  => 'numeric'
				),
				'comment' => array(
					'title' => $lang['strcomment'],
					'field' => field('relcomment'),
				'type' => 'spanned',
				'params'=> array(
						'cliplen' => 32,
						'class' => 'tooltip right-aligned-tooltip',
						),
				),
			));

			$actions = array(
				'browse' => array(
					'content' => $lang['strbrowse'],
					'icon' => 'Eye',
					'attr'=> array (
						'href' => array (
							'url' => 'display.php',
							'urlvars' => array (
								'subject' => 'table',
								'return' => 'schema',
								'table' => field('relname')
							)
						)
					)
				),
			);
			if ($emajAttributesToManage && $emajdb->isEmaj_Adm() && $emajdb->getNumEmajVersion() >= 30200) {			// version >= 3.2.0
				$actions = array_merge($actions, array(
					'multiactions' => array(
						'keycols' => array('appschema' => 'nspname', 'table' => 'relname', 'group' => 'rel_group', 'type' => 'relkind'),
						'url' => "schemas.php",
					),
					'assign' => array(
						'content' => $lang['strassign'],
						'icon' => 'Assign',
						'attr' => array (
							'href' => array (
								'url' => 'schemas.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'assign_tables',
									'appschema' => field('nspname'),
									'table' => field('relname'),
									'group' => field('rel_group'),
									'type' => field('relkind'),
								)))),
						'multiaction' => 'assign_tables',
					),
					'move' => array(
						'content' => $lang['strmove'],
						'icon' => 'Move',
						'attr' => array (
							'href' => array (
								'url' => 'schemas.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'move_tables',
									'appschema' => field('nspname'),
									'table' => field('relname'),
									'group' => field('rel_group'),
									'type' => field('relkind'),
								)))),
						'multiaction' => 'move_tables',
					),
					'modify' => array(
						'content' => $lang['strupdate'],
						'icon' => 'Pencil',
						'attr' => array (
							'href' => array (
								'url' => 'schemas.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'modify_tables',
									'type' => field('relkind'),
									'appschema' => field('nspname'),
									'table' => field('relname'),
									'group' => field('rel_group'),
								)))),
						'multiaction' => 'modify_tables',
					),
					'remove' => array(
						'content' => $lang['strremove'],
						'icon' => 'Remove',
						'attr' => array (
							'href' => array (
								'url' => 'schemas.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'remove_tables',
									'appschema' => field('nspname'),
									'table' => field('relname'),
									'group' => field('rel_group'),
									'type' => field('relkind'),
								)))),
						'multiaction' => 'remove_tables',
					),
				));
			}

			$misc->printTable($tables, $columns, $actions, 'tables-tables', $lang['strnotables'], 'tblseqPre', array('sorter' => true, 'filter' => true));

			// Display the sequences list

			// Get all sequences
			if ($emajAttributesToManage) {
				$sequences = $emajdb->getSequences($_REQUEST['schema']);
			} else {
				$sequences = $data->getSequences();
			}

			echo "<a id=\"sequences\">&nbsp;</a>\n";
			$misc->printTitle(sprintf($lang['strsequenceslist'], $_REQUEST['schema']), $misc->buildTitleRecordsCounter($sequences));

			$columns = array(
				'sequence' => array(
					'title' => $lang['strsequence'],
					'field' => field('seqname'),
					'url'   => "seqproperties.php?action=properties&amp;{$misc->href}&amp;",
					'vars'  => array('sequence' => 'seqname'),
				),
			);
			if ($emajAttributesToManage && $emajdb->isEmaj_Adm() && $emajdb->getNumEmajVersion() >= 30200) {			// version >= 3.2.0
				$columns = array_merge($columns, array(
					'actions' => array(
						'title' => $lang['stractions'],
					),
				));
			}
			if ($emajAttributesToManage) {
				$columns = array_merge($columns, array(
					'group' => array(
						'title' => $lang['strgroup'],
						'field' => field('rel_group'),
					),
				));
			}
			$columns = array_merge($columns, array(
				'owner' => array(
					'title' => $lang['strowner'],
					'field' => field('seqowner'),
				),
				'comment' => array(
					'title' => $lang['strcomment'],
					'field' => field('seqcomment'),
					'type' => 'spanned',
					'params'=> array(
							'cliplen' => 32,
							'class' => 'tooltip right-aligned-tooltip',
							),
				),
			));

			if ($emajAttributesToManage && $emajdb->isEmaj_Adm() && $emajdb->getNumEmajVersion() >= 30200) {			// version >= 3.2.0
				$actions = array(
					'multiactions' => array(
						'keycols' => array('appschema' => 'nspname', 'sequence' => 'seqname', 'group' => 'rel_group', 'type' => 'relkind'),
						'url' => "schemas.php",
					),
					'assign' => array(
						'content' => $lang['strassign'],
						'icon' => 'Assign',
						'attr' => array (
							'href' => array (
								'url' => 'schemas.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'assign_sequences',
									'appschema' => field('nspname'),
									'sequence' => field('seqname'),
									'group' => field('rel_group'),
									'type' => field('relkind'),
								)))),
						'multiaction' => 'assign_sequences',
					),
					'move' => array(
						'content' => $lang['strmove'],
						'icon' => 'Move',
						'attr' => array (
							'href' => array (
								'url' => 'schemas.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'move_sequences',
									'appschema' => field('nspname'),
									'sequence' => field('seqname'),
									'group' => field('rel_group'),
									'type' => field('relkind'),
								)))),
						'multiaction' => 'move_sequences',
					),
					'remove' => array(
						'content' => $lang['strremove'],
						'icon' => 'Remove',
						'attr' => array (
							'href' => array (
								'url' => 'schemas.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'remove_sequences',
									'appschema' => field('nspname'),
									'sequence' => field('seqname'),
									'group' => field('rel_group'),
									'type' => field('relkind'),
								)))),
						'multiaction' => 'remove_sequences',
					),
				);
			} else {
				$actions = array();
			}

			$misc->printTable($sequences, $columns, $actions, 'sequences-sequences', $lang['strnosequences'], 'tblseqPre', array('sorter' => true, 'filter' => true));
		}
	}

/********************************************************************************************************
 * Functions preparing or performing actions
 *******************************************************************************************************/

	/**
	 * Prepare assign tables to a group: ask for properties and confirmation
	 */
	function assign_tables() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbTbl, $tablesList, $fullList) = processMultiActions($_REQUEST['ma'], 'table');
		} else {
			$nbTbl = 1;
			$tablesList = $_REQUEST['table'];
		}

		// Prepare the action part of potential error messages
		if ($nbTbl == 1)
			$errMsgAction = sprintf($lang['strassigntableerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($tablesList));
		else
			$errMsgAction = sprintf($lang['strassigntableserr'], $nbTbl, htmlspecialchars($_REQUEST['schema']));

		// Check that the schema and the tables still exist.
		checkRelations($_REQUEST['schema'], $tablesList, 'table', $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strassigntable']);

		// Get created group names.
		$knownGroups = $emajdb->getCreatedGroups();

		// Get tablespaces the current user can see.
		$knownTsp = $emajdb->getKnownTsp();

		// Get the number of application triggers held by these tables.
		$nbAppTriggers = $emajdb->getNbAppTriggers($_REQUEST['schema'], $tablesList);

		// Build the form
		if ($nbTbl > 1) {
			echo "<p>" . sprintf($lang['strconfirmassigntables'], $nbTbl, $_REQUEST['schema']) . "</p>\n{$fullList}";
		} else {
			echo "<p>" . sprintf($lang['strconfirmassigntable'], $_REQUEST['schema'], $tablesList) . "</p>\n";
		}

		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"assign_tables_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tables\" value=\"", htmlspecialchars($tablesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbtables\" value=\"{$nbTbl}\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// group name
		echo "\t<div class=\"form-label required\">{$lang['strgroup']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"group\">";
		foreach($knownGroups as $r)
			echo "\t\t<option>", htmlspecialchars($r['group_name']), "\n";
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// priority level
		echo "\t<div class=\"form-label\">{$lang['strenterpriority']}</div>\n";
		echo "\t<div class=\"form-input\">";
		echo "<input type=\"number\" name=\"priority\" class=\"priority\" min=\"0\" max=\"2147483647\" value=\"\" />";
		echo "</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strpriorityhelp']}\"/></div>\n";

		// data log tablespace
		echo "\t<div class=\"form-label\">{$lang['strenterlogdattsp']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"logdattsp\"";
		if (empty($knownTsp))
			echo " disabled>";
		else {
			echo ">";
			echo "\t\t<option checked value=''>" . htmlspecialchars($lang['strdefaulttsp']) . "\n";
			foreach($knownTsp as $r)
				echo "\t\t<option>"  . htmlspecialchars($r['spcname']) . "\n";
		}
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// index log tablespace
		echo "\t<div class=\"form-label\">{$lang['strenterlogidxtsp']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"logidxtsp\"";
		if (empty($knownTsp))
			echo " disabled>";
		else {
			echo ">";
			echo "\t\t<option checked value=''>" . htmlspecialchars($lang['strdefaulttsp']) . "\n";
			foreach($knownTsp as $r)
				echo "\t\t<option>" . htmlspecialchars($r['spcname']) . "\n";
		}
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['strmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"ASSIGN_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";

		echo"</div>\n";

		if ($nbAppTriggers > 0 ) {
			echo "<p>" . sprintf($lang['strtableshavetriggers'], $nbAppTriggers) . "</p>\n";
		}

		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"assigntable\" value=\"{$lang['strassign']}\" id=\"ok\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform tables assignment into a tables group
	 */
	function assign_tables_ok() {
		global $lang, $data, $emajdb, $_reload_browser;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbtables'] == 1)
			$errMsgAction = sprintf($lang['strassigntableerr2'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['tables']), $_POST['group']);
		else
			$errMsgAction = sprintf($lang['strassigntableserr2'], $_POST['nbtables'], htmlspecialchars($_POST['schema']), $_POST['group']);

		// Check that the schema and the tables still exist
		checkRelations($_REQUEST['schema'], $_POST['tables'], 'table', $errMsgAction);

		// Check that the tables group still exists
		recheckGroups($_POST['group'], $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($_POST['group'], $_POST['mark'], $errMsgAction);

		// OK, process the tables assignment
		// get the list of emaj_schema before the assignment
		$emajSchemasBefore = $emajdb->getEmajSchemasList();

		$nbTables = $emajdb->assignTables($_POST['schema'], $_POST['tables'], $_POST['group'],
							$_POST['priority'], $_POST['logdattsp'], $_POST['logidxtsp'], $finalMark);

		// Check the result and exit
		if ($nbTables >= 0) {
			// if new emaj schemas have been created, reload the browser
			$emajSchemasAfter = $emajdb->getEmajSchemasList();
			if ($emajdb->getEmajSchemasList() <> $emajSchemasBefore)
				$_reload_browser = true;
			if ($nbTables > 1)
				list_schemas(sprintf($lang['strassigntablesok'], $nbTables, htmlspecialchars($_POST['group'])));
			else
				list_schemas(sprintf($lang['strassigntableok'], $nbTables, htmlspecialchars($_POST['group'])));
		} else {
			list_schemas('', $errMsgAction);
		}
	}

	/**
	 * Prepare move tables to another group: ask for confirmation
	 */
	function move_tables() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbTbl, $tablesList, $fullList, $groupsList) = processMultiActions($_REQUEST['ma'], 'table', true);
		} else {
			$nbTbl = 1;
			$tablesList = $_REQUEST['table'];
			$groupsList = $_REQUEST['group'];
		}

		// Prepare the action part of potential error messages
		if ($nbTbl == 1)
			$errMsgAction = sprintf($lang['strmovetableerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($tablesList));
		else
			$errMsgAction = sprintf($lang['strmovetableserr'], $nbTbl, htmlspecialchars($_REQUEST['schema']));

		// Check that the tables group still exists
		recheckGroups($groupsList, $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strmovetable']);

		// Get created group names
		$knownGroups = $emajdb->getCreatedGroups();

		// Build the form
		if ($nbTbl > 1) {
			echo "<p>" . sprintf($lang['strconfirmmovetables'], $nbTbl, $_REQUEST['schema']) . "</p>\n{$fullList}";
		} else {
			echo "<p>" . sprintf($lang['strconfirmmovetable'], $_REQUEST['schema'], $tablesList, $groupsList) . "</p>\n";
		}

		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"move_tables_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tables\" value=\"", htmlspecialchars($tablesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbtables\" value=\"{$nbTbl}\" />\n";
		echo "<input type=\"hidden\" name=\"oldgroups\" value=\"", htmlspecialchars($groupsList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// group name
		echo "\t<div class=\"form-label required\">{$lang['strnewgroup']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"newgroup\">";
		foreach($knownGroups as $r)
			echo "\t\t<option>", htmlspecialchars($r['group_name']), "\n";
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['strmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"MOVE_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
		echo"</div>\n";

		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"movetable\" value=\"{$lang['strmove']}\" id=\"ok\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform tables move into another tables group
	 */
	function move_tables_ok() {
		global $lang, $data, $emajdb;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbtables'] == 1)
			$errMsgAction = sprintf($lang['strmovetableerr2'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['tables']), htmlspecialchars($_POST['oldgroups']), htmlspecialchars($_POST['newgroup']));
		else
			$errMsgAction = sprintf($lang['strmovetableserr2'], $_POST['nbtables'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['newgroup']));

		$allGroups = $_POST['oldgroups'] . ', ' . $_POST['newgroup'];

		// Check that the tables group still exists
		recheckGroups($allGroups, $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($allGroups, $_POST['mark'], $errMsgAction);

		// OK, process the tables move
		$nbTables = $emajdb->moveTables($_POST['schema'], $_POST['tables'], $_POST['newgroup'], $finalMark);

		// Check the result and exit
		if ($nbTables >= 0)
			if ($nbTables > 1)
				list_schemas(sprintf($lang['strmovetablesok'], $nbTables, htmlspecialchars($_POST['newgroup'])));
			else
				list_schemas(sprintf($lang['strmovetableok'], $nbTables, htmlspecialchars($_POST['newgroup'])));
		else
			list_schemas('', $errMsgAction);
	}

	/**
	 * Prepare modify tables : ask for properties and confirmation
	 */
	function modify_tables() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbTbl, $tablesList, $fullList, $groupsList) = processMultiActions($_REQUEST['ma'], 'table', true);
		} else {
			$nbTbl = 1;
			$tablesList = $_REQUEST['table'];
			$groupsList = $_REQUEST['group'];
		}

		// Prepare the action part of potential error messages
		if ($nbTbl == 1)
			$errMsgAction = sprintf($lang['strmodifytableerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($tablesList));
		else
			$errMsgAction = sprintf($lang['strmodifytableserr'], $nbTbl, htmlspecialchars($_REQUEST['schema']));

		// Check that the tables group still exists
		recheckGroups($groupsList, $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strmodifytable']);

		// Get the tables properties
		$properties = $emajdb->getTablesProperties($_REQUEST['schema'],$tablesList);

		// Prepare the current values to display
		if ($properties->fields['nb_priority'] == 1)
			$currentPriority = $properties->fields['min_priority'];
		else
			$currentPriority = "<i>" . sprintf($lang['strdifferentvalues'], $properties->fields['nb_priority']) . "</i>";

		if ($properties->fields['nb_log_dat_tsp'] == 1) {
			$currentLogDatTsp = $properties->fields['min_log_dat_tsp'];
			if ($currentLogDatTsp == '')
				$currentLogDatTsp = htmlspecialchars($lang['strdefaulttsp']);
		} else {
			$currentLogDatTsp = "<i>" . sprintf($lang['strdifferentvalues'], $properties->fields['nb_log_dat_tsp']) . "</i>";
		}

		if ($properties->fields['nb_log_idx_tsp'] == 1) {
			$currentLogIdxTsp = $properties->fields['min_log_idx_tsp'];
			if ($currentLogIdxTsp == '')
				$currentLogIdxTsp = htmlspecialchars($lang['strdefaulttsp']);
		} else {
			$currentLogIdxTsp = "<i>" . sprintf($lang['strdifferentvalues'], $properties->fields['nb_log_idx_tsp']) . "</i>";
		}

		// Get tablespaces the current user can see
		$knownTsp = $emajdb->getKnownTsp();

		// Build the form
		if ($nbTbl == 1) {
			echo "<p>" . sprintf($lang['strconfirmmodifytable'], $_REQUEST['schema'], $tablesList, $groupsList) . "</p>\n";
		} else {
			echo "<p>" . sprintf($lang['strconfirmmodifytables'], $nbTbl, $_REQUEST['schema']) . "</p>\n{$fullList}";
		}
		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"modify_tables_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tables\" value=\"", htmlspecialchars($tablesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbtables\" value=\"{$nbTbl}\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container-5c\">\n";

		// Header row
		echo "\t<div></div>\n";
		echo "\t<div></div>\n";
		echo "\t<div class=\"form-header\">{$lang['strcurrentvalue']}</div>\n";
		echo "\t<div></div>\n";
		echo "\t<div class=\"form-header\">{$lang['strnewvalue']}</div>\n";

		// priority level
		echo "\t<div class=\"form-label\">{$lang['strenterpriority']}</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strpriorityhelp']}\"/></div>\n";
		echo "\t<div id=\"priorityvalue\" class=\"form-value style=\"justify-content: right;\"\">$currentPriority</div>\n";
		echo "\t<div class=\"form-button\"><button type=\"button\" onclick=\"javascript:toogleInput(this, 'priority');\">&gt;&gt;</button></div>\n";
		echo "\t<div class=\"form-input\">";
		echo "<input id=\"priorityinput\" type=\"number\" name=\"priority\" class=\"priority\" min=\"0\" max=\"2147483647\" value=\"\" disabled/>";
		echo "</div>\n";

		// data log tablespace
		echo "\t<div class=\"form-label\">{$lang['strenterlogdattsp']}</div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "\t<div id=\"logdattspvalue\" class=\"form-value\">$currentLogDatTsp</div>\n";
		echo "\t<div class=\"form-button\"><button type=\"button\" onclick=\"javascript:toogleInput(this, 'logdattsp');\">&gt;&gt;</button></div>\n";
		echo "\t<div class=\"form-input\"><select id=\"logdattspinput\" name=\"logdattsp\" disabled";
		if (empty($knownTsp))
			echo " disabled>";
		else {
			echo ">";
			echo "\t\t<option checked value=''>" . htmlspecialchars($lang['strdefaulttsp']) . "\n";
			foreach($knownTsp as $r) {
				echo "\t\t<option>", htmlspecialchars($r['spcname']), "\n";
			}
		}
		echo "\t</select></div>\n";

		// index log tablespace
		echo "\t<div class=\"form-label\">{$lang['strenterlogidxtsp']}</div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "\t<div id=\"logidxtspvalue\" class=\"form-value\">$currentLogIdxTsp</div>\n";
		echo "\t<div class=\"form-button\"><button type=\"button\" onclick=\"javascript:toogleInput(this, 'logidxtsp');\">&gt;&gt;</button></div>\n";
		echo "\t<div class=\"form-input\"><select id=\"logidxtspinput\" name=\"logidxtsp\" disabled";
		if (empty($knownTsp))
			echo " disabled>";
		else {
			echo ">";
			echo "\t\t<option checked value=''>" . htmlspecialchars($lang['strdefaulttsp']) . "\n";
			foreach($knownTsp as $r) {
				echo "\t\t<option>", htmlspecialchars($r['spcname']), "\n";
			}
		}
		echo "\t</select></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['strmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
		echo "\t<div class=\"form-input\" style=\"grid-column: span 3;\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"MODIFY_%\" /></div>\n";

		echo"</div>\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" id=\"ok\" name=\"modifytable\" value=\"{$lang['strupdate']}\" disabled/>\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform tables modification
	 */
	function modify_tables_ok() {
		global $lang, $data, $emajdb;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbtables'] == 1)
			$errMsgAction = sprintf($lang['strmodifytableerr'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['tables']));
		else
			$errMsgAction = sprintf($lang['strmodifytableserr'], $_POST['nbtables'], htmlspecialchars($_POST['schema']));

		// Check that the tables group still exists
		recheckGroups($_POST['groups'], $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction);

		// OK,process the tables properties changes
		$nbTables = $emajdb->modifyTables($_POST['schema'], $_POST['tables'],
							isset($_POST['priority']) ? $_POST['priority'] : null,
							isset($_POST['logdattsp']) ? $_POST['logdattsp'] : null,
							isset($_POST['logidxtsp']) ? $_POST['logidxtsp'] : null,
							$finalMark);

		// Check the result and exit
		if ($nbTables >= 0)
			list_schemas(sprintf($lang['strmodifytablesok'], $nbTables));
		else
			list_schemas('', $errMsgAction);
	}

	/**
	 * Prepare remove tables: ask for confirmation
	 */
	function remove_tables() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbTbl, $tablesList, $fullList, $groupsList) = processMultiActions($_REQUEST['ma'], 'table', true);
		} else {
			$nbTbl = 1;
			$tablesList = $_REQUEST['table'];
			$groupsList = $_REQUEST['group'];
		}

		// Prepare the action part of potential error messages
		if ($nbTbl == 1)
			$errMsgAction = sprintf($lang['strremovetableerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($tablesList), htmlspecialchars($groupsList));
		else
			$errMsgAction = sprintf($lang['strremovetableserr'], $nbTbl, htmlspecialchars($_REQUEST['schema']));

		// Check that the tables group still exists
		recheckGroups($groupsList, $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strremovetable']);

		// Build the form
		if ($nbTbl > 1) {
			echo "<p>" . sprintf($lang['strconfirmremovetables'], $nbTbl, $_REQUEST['schema']) . "</p>\n{$fullList}";
		} else {
			echo "<p>" . sprintf($lang['strconfirmremovetable'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($tablesList), htmlspecialchars($groupsList)) . "</p>\n";
		}

		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"remove_tables_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tables\" value=\"", htmlspecialchars($tablesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbtables\" value=\"{$nbTbl}\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['strmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"REMOVE_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
		echo"</div>\n";

		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"removetable\" value=\"{$lang['strremove']}\" id=\"ok\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform tables remove from their tables group
	 */
	function remove_tables_ok() {
		global $lang, $data, $emajdb, $_reload_browser;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbtables'] == 1)
			$errMsgAction = sprintf($lang['strremovetableerr'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['tables']), htmlspecialchars($_POST['groups']));
		else
			$errMsgAction = sprintf($lang['strremovetableserr'], $_POST['nbtables'], htmlspecialchars($_POST['schema']));

		// Check that the tables group still exists
		recheckGroups($_POST['groups'], $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction);

		// OK, process the tables removal
		// get the list of emaj_schema before the removal
		$emajSchemasBefore = $emajdb->getEmajSchemasList();

		$nbTables = $emajdb->removeTables($_POST['schema'], $_POST['tables'], $finalMark);

		// Check the result and exit
		if ($nbTables >= 0) {
			// reload the browser only if emaj schemas have been dropped
			if ($emajdb->getEmajSchemasList() <> $emajSchemasBefore)
				$_reload_browser = true;
			if ($nbTables > 1)
				list_schemas(sprintf($lang['strremovetablesok'], $nbTables));
			else
				list_schemas(sprintf($lang['strremovetableok'], $nbTables));
		} else {
			list_schemas('', $errMsgAction);
		}
	}

	/**
	 * Prepare assign sequences to a group: ask for properties and confirmation
	 */
	function assign_sequences() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbSeq, $sequencesList, $fullList) = processMultiActions($_REQUEST['ma'], 'sequence');
		} else {
			$nbSeq = 1;
			$sequencesList = $_REQUEST['sequence'];
		}

		// Prepare the action part of potential error messages
		if ($nbSeq == 1)
			$errMsgAction = sprintf($lang['strassignsequenceerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($sequencesList));
		else
			$errMsgAction = sprintf($lang['strassignsequenceserr'], $nbSeq, htmlspecialchars($_REQUEST['schema']));

		// Check that the schema and the sequences still exist
		checkRelations($_REQUEST['schema'], $sequencesList, 'sequence', $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strassignsequence']);

		// Get created group names
		$knownGroups = $emajdb->getCreatedGroups();

		// Build the form
		if ($nbSeq > 1) {
			echo "<p>" . sprintf($lang['strconfirmassignsequences'], $nbSeq, $_REQUEST['schema']) . "</p>\n{$fullList}";
		} else {
			echo "<p>" . sprintf($lang['strconfirmassignsequence'], $_REQUEST['schema'], $sequencesList) . "</p>\n";
		}

		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"assign_sequences_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"sequences\" value=\"", htmlspecialchars($sequencesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbsequences\" value=\"{$nbSeq}\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// group name
		echo "\t<div class=\"form-label required\">{$lang['strgroup']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"group\">";
		foreach($knownGroups as $r)
			echo "\t\t<option>", htmlspecialchars($r['group_name']), "\n";
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['strmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"ASSIGN_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";

		echo"</div>\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"assignsequence\" value=\"{$lang['strassign']}\" id=\"ok\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform sequences assignment into a tables group
	 */
	function assign_sequences_ok() {
		global $lang, $data, $emajdb;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbsequences'] == 1)
			$errMsgAction = sprintf($lang['strassignsequenceerr2'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['sequences']), $_POST['group']);
		else
			$errMsgAction = sprintf($lang['strassignsequenceserr2'], $_POST['nbsequences'], htmlspecialchars($_POST['schema']), $_POST['group']);

		// Check that the schema and the sequences still exist
		checkRelations($_REQUEST['schema'], $_POST['sequences'], 'sequence', $errMsgAction);

		// Check that the tables group still exists
		recheckGroups($_POST['group'], $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($_POST['group'], $_POST['mark'], $errMsgAction);

		// OK, process the sequences assignment
		$nbSequences = $emajdb->assignSequences($_POST['schema'], $_POST['sequences'], $_POST['group'], $finalMark);

		// Check the result and exit
		if ($nbSequences >= 0)
			if ($nbSequences > 1)
				list_schemas(sprintf($lang['strassignsequencesok'], $nbSequences, htmlspecialchars($_POST['group'])));
			else
				list_schemas(sprintf($lang['strassignsequenceok'], $nbSequences, htmlspecialchars($_POST['group'])));
		else
			list_schemas('', $errMsgAction);
	}

	/**
	 * Prepare move sequences to another group: ask for confirmation
	 */
	function move_sequences() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbSeq, $sequencesList, $fullList, $groupsList) = processMultiActions($_REQUEST['ma'], 'sequence', true);
		} else {
			$nbSeq = 1;
			$sequencesList = $_REQUEST['sequence'];
			$groupsList = $_REQUEST['group'];
		}

		// Prepare the action part of potential error messages
		if ($nbSeq == 1)
			$errMsgAction = sprintf($lang['strmovesequenceerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($sequencesList));
		else
			$errMsgAction = sprintf($lang['strmovesequenceserr'], $nbSeq, htmlspecialchars($_REQUEST['schema']));

		// Check that the tables group still exists
		recheckGroups($groupsList, $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strmovesequence']);

		// Get created group names
		$knownGroups = $emajdb->getCreatedGroups();

		// Build the form
		if ($nbSeq > 1) {
			echo "<p>" . sprintf($lang['strconfirmmovesequences'], $nbSeq, $_REQUEST['schema']) . "</p>\n{$fullList}";
		} else {
			echo "<p>" . sprintf($lang['strconfirmmovesequence'], $_REQUEST['schema'], $sequencesList, $groupsList) . "</p>\n";
		}

		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"move_sequences_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"sequences\" value=\"", htmlspecialchars($sequencesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbsequences\" value=\"{$nbSeq}\" />\n";
		echo "<input type=\"hidden\" name=\"oldgroups\" value=\"", htmlspecialchars($groupsList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// group name
		echo "\t<div class=\"form-label required\">{$lang['strnewgroup']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"newgroup\">";
		foreach($knownGroups as $r)
			echo "\t\t<option>", htmlspecialchars($r['group_name']), "\n";
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['strmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"MOVE_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
		echo"</div>\n";

		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"movesequence\" value=\"{$lang['strmove']}\" id=\"ok\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform sequences move into another tables group
	 */
	function move_sequences_ok() {
		global $lang, $data, $emajdb;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbsequences'] == 1)
			$errMsgAction = sprintf($lang['strmovesequenceerr2'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['sequences']), htmlspecialchars($_POST['oldgroups']), htmlspecialchars($_POST['newgroup']));
		else
			$errMsgAction = sprintf($lang['strmovesequenceserr2'], $_POST['nbsequences'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($_POST['newgroup']));

		$allGroups = $_POST['oldgroups'] . ', ' . $_POST['newgroup'];

		// Check that the tables group still exists
		recheckGroups($allGroups, $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($allGroups, $_POST['mark'], $errMsgAction);

		// OK, process the sequences move
		$nbSequences = $emajdb->moveSequences($_POST['schema'], $_POST['sequences'], $_POST['newgroup'], $finalMark);

		// Check the result and exit
		if ($nbSequences>= 0)
			if ($nbSequences > 1)
				list_schemas(sprintf($lang['strmovesequencesok'], $nbSequences, htmlspecialchars($_POST['newgroup'])));
			else
				list_schemas(sprintf($lang['strmovesequenceok'], $nbSequences, htmlspecialchars($_POST['newgroup'])));
		else
			list_schemas('', $errMsgAction);
	}

	/**
	 * Prepare remove sequences: ask for confirmation
	 */
	function remove_sequences() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbSeq, $sequencesList, $fullList, $groupsList) = processMultiActions($_REQUEST['ma'], 'sequence', true);
		} else {
			$nbSeq = 1;
			$sequencesList = $_REQUEST['sequence'];
			$groupsList = $_REQUEST['group'];
		}

		// Prepare the action part of potential error messages
		if ($nbSeq == 1)
			$errMsgAction = sprintf($lang['strremovesequenceerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($sequencesList), htmlspecialchars($groupsList));
		else
			$errMsgAction = sprintf($lang['strremovesequenceserr'], $nbSeq, htmlspecialchars($_REQUEST['schema']));

		// Check that the tables group still exists
		recheckGroups($groupsList, $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strremovesequence']);

		// Build the form
		if ($nbSeq > 1) {
			echo "<p>" . sprintf($lang['strconfirmremovesequences'], $nbSeq, $_REQUEST['schema']) . "</p>\n{$fullList}";
		} else {
			echo "<p>" . sprintf($lang['strconfirmremovesequence'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($sequencesList), htmlspecialchars($groupsList)) . "</p>\n";
		}
		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"remove_sequences_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"sequences\" value=\"", htmlspecialchars($sequencesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbsequences\" value=\"{$nbSeq}\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['strmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"REMOVE_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
		echo"</div>\n";

		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"removesequence\" value=\"{$lang['strremove']}\" id=\"ok\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform sequences remove from their tables group
	 */
	function remove_sequences_ok() {
		global $lang, $data, $emajdb;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbsequences'] == 1)
			$errMsgAction = sprintf($lang['strremovesequenceerr'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['sequences']), htmlspecialchars($_POST['groups']));
		else
			$errMsgAction = sprintf($lang['strremovesequenceserr'], $_POST['nbsequences'], htmlspecialchars($_POST['schema']));

		// Check that the tables group still exists
		recheckGroups($_POST['groups'], $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction);

		// OK, process the sequences removal
		$nbSequences = $emajdb->removeSequences($_POST['schema'], $_POST['sequences'], $finalMark);

		// Check the result and exit
		if ($nbSequences>= 0)
			if ($nbSequences > 1)
				list_schemas(sprintf($lang['strremovesequencesok'], $nbSequences));
			else
				list_schemas(sprintf($lang['strremovesequenceok'], $nbSequences));
		else
			list_schemas($errMsgAction);
	}

	/**
	 * Generate XML for the browser tree.
	 */
	function doTree() {
		global $misc, $data, $lang;

		$schemas = $data->getSchemas();

		$reqvars = $misc->getRequestVars('schema');

		$attrs = array(
			'text'   => field('nspname'),
			'icon'   => 'Schema',
			'toolTip'=> field('nspcomment'),
			'action' => url(
				'schemas.php',
				$reqvars,
				array(
					'action' => 'list_schemas',
					'schema'  => field('nspname')
				)
			),
		);

		$misc->printTree($schemas, $attrs, 'schemas');
		exit;
	}

	if ($action == 'tree') doTree();

	$scripts = "<script src=\"js/schemas.js\"></script>";

	$misc->printHtmlHeader($lang['strschemas'], $scripts, 'schemas');
	$misc->printBody();

	if (isset($_POST['cancel'])) $action = '';

	switch ($action) {
		case 'list_schemas':
			list_schemas();
			break;
		case 'assign_tables';
			assign_tables();	
			break;
		case 'assign_tables_ok':
			assign_tables_ok();
			break;
		case 'move_tables';
			move_tables();	
			break;
		case 'move_tables_ok':
			move_tables_ok();
			break;
		case 'modify_tables';
			modify_tables();	
			break;
		case 'modify_tables_ok':
			modify_tables_ok();
			break;
		case 'remove_tables';
			remove_tables();	
			break;
		case 'remove_tables_ok':
			remove_tables_ok();
			break;
		case 'assign_sequences';
			assign_sequences();	
			break;
		case 'assign_sequences_ok':
			assign_sequences_ok();
			break;
		case 'move_sequences';
			move_sequences();	
			break;
		case 'move_sequences_ok':
			move_sequences_ok();
			break;
		case 'modify_sequences';
			modify_sequences();	
			break;
		case 'remove_sequences';
			remove_sequences();	
			break;
		case 'remove_sequences_ok':
			remove_sequences_ok();
			break;
		default:
			list_schemas();
			break;
	}

	$misc->printFooter();

?>
