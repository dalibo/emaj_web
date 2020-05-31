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

		// if E-Maj schema, return
		if (!isset($rowdata->fields['rel_group'])) return $actions;
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
			$alt = $lang['emajschema'];
			return "<img src=\"{$icon}\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		}
		return;
	}

	/**
	 * Show the list of schemas in the database
	 * and the tables and sequences lists if a schema has already been selected
	 */
	function list_schemas($msg = '', $errMsg = '', $prevSchema = '') {
		global $data, $misc, $conf, $emajdb, $lang, $nbGroups;

		if (!isset($_REQUEST['schema'])) $_REQUEST['schema'] = $prevSchema;
		if (is_array($_REQUEST['schema'])) $_REQUEST['schema'] = $_REQUEST['schema'][0];

		if (isset($_REQUEST['schema']) && $_REQUEST['schema'] != '') {	// the trail differs if a schema is selected
			$misc->printHeader('schema', 'database', 'schemas');
		} else {
			$misc->printHeader('database', 'database', 'schemas');
		};

		$misc->printMsg($msg);
		$misc->printTitle($lang['strallschemas']);

		// Get the list of schemas
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
			$schemas = $emajdb->getAllSchemas();
		} else {
			$schemas = $data->getSchemas();
		}

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
					'title' => $lang['emajisemaj'],
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
			// emaj attribute and actions to manage ?
			$emajAttributesToManage = ($emajdb->isEnabled() && $emajdb->isAccessible() && ! $isEmajSchema);

			// get the number of created groups (needed to display or hide some actions)
			if ($emajdb->isAccessible())
				$nbGroups = $emajdb->getNbGroups();
			else
				$nbGroups = 0;

			$urlvars = $misc->getRequestVars();

			// Display the tables list
			echo "<a id=\"tables\">&nbsp;</a>\n";

			$misc->printTitle(sprintf($lang['strtableslist'], $_REQUEST['schema']));

			if ($emajAttributesToManage) {
				$tables = $emajdb->getTables($_REQUEST['schema']);
			} else {
				$tables = $data->getTables();
			}

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
						'title' => $lang['emajgroup'],
						'field' => field('rel_group'),
					),
					'priority' => array(
						'title' => $lang['emajpriority'],
						'field' => field('rel_priority'),
						'params'=> array('align' => 'center'),
					),
					'logdattsp' => array(
						'title' => $lang['emajlogdattsp'],
						'field' => field('rel_log_dat_tsp'),
					),
					'logidxtsp' => array(
						'title' => $lang['emajlogidxtsp'],
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
			if ($emajAttributesToManage && $emajdb->getNumEmajVersion() >= 30200) {			// version >= 3.2.0
				$actions = array_merge($actions, array(
					'multiactions' => array(
						'keycols' => array('appschema' => 'nspname', 'table' => 'relname', 'group' => 'rel_group', 'type' => 'relkind'),
						'url' => "schemas.php",
					),
					'assign' => array(
						'content' => $lang['emajassign'],
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
						'content' => $lang['emajmove'],
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
									'priority' => field('rel_priority'),
									'logdattsp' => field('rel_log_dat_tsp'),
									'logidxtsp' => field('rel_log_idx_tsp'),
								)))),
// TODO: support "modify" for several tables
//						'multiaction' => 'modify_tables',
					),
					'remove' => array(
						'content' => $lang['emajremove'],
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

			echo "<a id=\"sequences\">&nbsp;</a>\n";

			$misc->printTitle(sprintf($lang['strsequenceslist'], $_REQUEST['schema']));

			// Get all sequences
			if ($emajAttributesToManage) {
				$sequences = $emajdb->getSequences($_REQUEST['schema']);
			} else {
				$sequences = $data->getSequences();
			}

			$columns = array(
				'sequence' => array(
					'title' => $lang['strsequence'],
					'field' => field('seqname'),
					'url'   => "seqproperties.php?action=properties&amp;{$misc->href}&amp;",
					'vars'  => array('sequence' => 'seqname'),
				),
			);
			if ($emajAttributesToManage) {
				$columns = array_merge($columns, array(
					'actions' => array(
						'title' => $lang['stractions'],
					),
					'group' => array(
						'title' => $lang['emajgroup'],
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

			if ($emajAttributesToManage && $emajdb->getNumEmajVersion() >= 30200) {			// version >= 3.2.0

				$actions = array(
					'multiactions' => array(
						'keycols' => array('appschema' => 'nspname', 'sequence' => 'seqname', 'group' => 'rel_group', 'type' => 'relkind'),
						'url' => "schemas.php",
					),
					'assign' => array(
						'content' => $lang['emajassign'],
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
						'content' => $lang['emajmove'],
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
						'content' => $lang['emajremove'],
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

	/**
	 * Prepare assign tables to a group: ask for properties and confirmation
	 */
	function assign_tables() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database', 'schemas');

		$misc->printTitle($lang['emajassigntable']);

		// Get created group names
		$knownGroups = $emajdb->getCreatedGroups();

		// Get tablespaces the current user can see
		$knownTsp = $emajdb->getKnownTsp();

		// Build the list of tables to processs and count them
		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"assign_tables_ok\" />\n";

		if (isset($_REQUEST['ma'])) {
		// multiple assign
			$tablesList = '';
			$fullList = "<ul>";
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				$tablesList .= $a['table'] . ', ';
				$fullList .= "<li>" . sprintf($lang['emajthetable'],$a['appschema'],$a['table']) . "</li>\n";
			}
			$tablesList = substr($tablesList,0,strlen($tablesList)-2);
			$fullList .= "</ul>\n";
			echo "<p>{$lang['emajconfirmassigntblseq']}{$fullList}</p>\n";
		} else {

		// single assign
			$tablesList = $_REQUEST['table'];
			$tableName = sprintf($lang['emajthetable'],$_REQUEST['schema'],$_REQUEST['table']);
			echo "<p>{$lang['emajassign']} {$tableName}</p>\n";
		}
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tables\" value=\"", htmlspecialchars($tablesList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// group name
		echo "\t<div class=\"form-label required\">{$lang['emajgroup']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"group\">";
		foreach($knownGroups as $r)
			echo "\t\t<option>", htmlspecialchars($r['group_name']), "\n";
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// priority level
		echo "\t<div class=\"form-label\">{$lang['emajenterpriority']}</div>\n";
		echo "\t<div class=\"form-input\">";
		echo "<input type=\"number\" name=\"priority\" class=\"priority\" min=\"0\" max=\"2147483647\" value=\"\" />";
		echo "</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajpriorityhelp']}\"/></div>\n";

		// data log tablespace
		echo "\t<div class=\"form-label\">{$lang['emajenterlogdattsp']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"logdattsp\"";
		if (empty($knownTsp))
			echo " disabled>";
		else {
			echo ">";
			echo "\t\t<option>&lt;{$lang['strnone']}&gt;\n";
			foreach($knownTsp as $r)
				echo "\t\t<option>", htmlspecialchars($r['spcname']), "\n";
		}
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// index log tablespace
		echo "\t<div class=\"form-label\">{$lang['emajenterlogidxtsp']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"logidxtsp\"";
		if (empty($knownTsp))
			echo " disabled>";
		else {
			echo ">";
			echo "\t\t<option>&lt;{$lang['strnone']}&gt;\n";
			foreach($knownTsp as $r)
				echo "\t\t<option>", htmlspecialchars($r['spcname']), "\n";
		}
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['emajmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"ASSIGN_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></div>\n";

		echo"</div>\n";
		echo $misc->form;
		echo "<p><input type=\"submit\" name=\"assigntable\" value=\"{$lang['emajassign']}\" id=\"ok\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform tables assignment into a tables group
	 */
	function assign_tables_ok() {
		global $lang, $data, $emajdb, $_reload_browser;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			list_schemas();
		} else {

	// process the tables assignment
			// get the list of emaj_schema before the assignment
			$emajSchemasBefore = $emajdb->getEmajSchemasList();

			if ($_POST['logdattsp'] == "<{$lang['strnone']}>") $_POST['logdattsp'] = '';
			if ($_POST['logidxtsp'] == "<{$lang['strnone']}>") $_POST['logidxtsp'] = '';
			$nbTables = $emajdb->assignTables($_POST['schema'],$_POST['tables'],$_POST['group'],
								$_POST['priority'], $_POST['logdattsp'], $_POST['logidxtsp'], $_POST['mark']);
			if ($nbTables >= 0) {
				// reload the browser only if new emaj schemas have been created
				$emajSchemasAfter = $emajdb->getEmajSchemasList();
				if ($emajdb->getEmajSchemasList() <> $emajSchemasBefore)
					$_reload_browser = true;
				list_schemas(sprintf($lang['emajdynassigntablesok'], $nbTables, htmlspecialchars($_POST['group'])));
			} else {
				list_schemas($lang['emajmodifygrouperr']);
			}
		}
	}

	/**
	 * Prepare move tables to another group: ask for confirmation
	 */
	function move_tables() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database', 'schemas');

		$misc->printTitle($lang['emajmovetable']);

		// Get created group names
		$knownGroups = $emajdb->getCreatedGroups();

		// Build the list of tables to processs and count them
		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"move_tables_ok\" />\n";

		if (isset($_REQUEST['ma'])) {
		// multiple move
			$tablesList = '';
			$fullList = "<ul>";
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				$tablesList .= $a['table'] . ', ';
				$fullList .= "<li>" . sprintf($lang['emajthetableingroup'],$a['appschema'],$a['table'],$a['group']) . "</li>\n";
			}
			$tablesList = substr($tablesList,0,strlen($tablesList)-2);
			$fullList .= "</ul>\n";
			echo "<p>{$lang['emajconfirmmovetblseq']}{$fullList}</p>\n";
		} else {

		// single move
			$tablesList = $_REQUEST['table'];
			$tableName = sprintf($lang['emajthetableingroup'],$_REQUEST['schema'],$_REQUEST['table'],$_REQUEST['group']);
			echo "<p>{$lang['emajmove']} {$tableName}</p>\n";
		}
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tables\" value=\"", htmlspecialchars($tablesList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// group name
		echo "\t<div class=\"form-label required\">{$lang['emajgroup']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"group\">";
		foreach($knownGroups as $r)
			echo "\t\t<option>", htmlspecialchars($r['group_name']), "\n";
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['emajmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"MOVE_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></div>\n";

		echo $misc->form;
		echo "<p><input type=\"submit\" name=\"movetable\" value=\"{$lang['emajmove']}\" id=\"ok\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform tables move into another tables group
	 */
	function move_tables_ok() {
		global $lang, $data, $emajdb;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			list_schemas();
		} else {

	// process the tables assignment
			$nbTables = $emajdb->moveTables($_POST['schema'],$_POST['tables'],$_POST['group'],$_POST['mark']);
			if ($nbTables >= 0)
				list_schemas(sprintf($lang['emajdynmovetablesok'], $nbTables, htmlspecialchars($_POST['group'])));
			else
				list_schemas($lang['emajmodifygrouperr']);
		}
	}

	/**
	 * Prepare modify tables : ask for properties and confirmation
	 */
	function modify_tables() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database', 'schemas');

		$misc->printTitle($lang['emajmodifytable']);

		// Get tablespaces the current user can see
		$knownTsp = $emajdb->getKnownTsp();

		// Build the list of tables to processs and count them
		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"modify_tables_ok\" />\n";

		if (isset($_REQUEST['ma'])) {
// TODO: support "modify" for several tables
//		// multiple modify
//			$tablesList = '';
//			$fullList = "<ul>";
//			foreach($_REQUEST['ma'] as $t) {
//				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
//				$tablesList .= $a['table'] . ', ';
//				$fullList .= "<li>" . sprintf($lang['emajthetableingroup'],$a['appschema'],$a['table'],$a['group']) . "</li>\n";
//			}
//			$tablesList = substr($tablesList,0,strlen($tablesList)-2);
//			$fullList .= "</ul>\n";
//			echo "<p>{$lang['emajconfirmmodifytblseq']}{$fullList}</p>\n";
		} else {

		// single modify
			$tablesList = $_REQUEST['table'];
			$tableName = sprintf($lang['emajthetableingroup'],$_REQUEST['schema'],$_REQUEST['table'],$_REQUEST['group']);
			echo "<p>{$lang['strupdate']} {$tableName}</p>\n";
		}
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tables\" value=\"", htmlspecialchars($tablesList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// priority level
		echo "\t<div class=\"form-label\">{$lang['emajenterpriority']}</div>\n";
		echo "\t<div class=\"form-input\">";
		echo "<input type=\"number\" name=\"priority\" class=\"priority\" min=\"0\" max=\"2147483647\" value=\"{$_REQUEST['priority']}\" />";
		echo "</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajpriorityhelp']}\"/></div>\n";

		// data log tablespace
		echo "\t<div class=\"form-label\">{$lang['emajenterlogdattsp']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"logdattsp\"";
		if (empty($knownTsp))
			echo " disabled>";
		else {
			echo ">";
			echo "\t\t<option>&lt;{$lang['strnone']}&gt;\n";
			foreach($knownTsp as $r) {
				echo "\t\t<option";
				if ($r['spcname'] == $_REQUEST['logdattsp']) {
					echo " selected";
				}
				echo ">", htmlspecialchars($r['spcname']), "\n";
			}
		}
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// index log tablespace
		echo "\t<div class=\"form-label\">{$lang['emajenterlogidxtsp']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"logidxtsp\"";
		if (empty($knownTsp))
			echo " disabled>";
		else {
			echo ">";
			echo "\t\t<option>&lt;{$lang['strnone']}&gt;\n";
			foreach($knownTsp as $r) {
				echo "\t\t<option";
				if ($r['spcname'] == $_REQUEST['logidxtsp']) echo " selected";
				echo ">", htmlspecialchars($r['spcname']), "\n";
			}
		}
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['emajmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"MODIFY_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></div>\n";

		echo"</div>\n";
		echo $misc->form;
		echo "<p><input type=\"submit\" name=\"modifytable\" value=\"{$lang['strupdate']}\" id=\"ok\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform tables modification
	 */
	function modify_tables_ok() {
		global $lang, $data, $emajdb;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			list_schemas();
		} else {

	// process the tables modification
			if ($_POST['logdattsp'] == "<{$lang['strnone']}>") $_POST['logdattsp'] = '';
			if ($_POST['logidxtsp'] == "<{$lang['strnone']}>") $_POST['logidxtsp'] = '';
			$nbTables = $emajdb->modifyTables($_POST['schema'],$_POST['tables'],
								$_POST['priority'], $_POST['logdattsp'], $_POST['logidxtsp'], $_POST['mark']);
			if ($nbTables >= 0)
				list_schemas(sprintf($lang['emajdynmodifytablesok'], $nbTables));
			else
				list_schemas($lang['emajmodifygrouperr']);
		}
	}

	/**
	 * Prepare remove tables: ask for confirmation
	 */
	function remove_tables() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database', 'schemas');

		$misc->printTitle($lang['emajremovetable']);

		// Build the list of tables to processs and count them
		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"remove_tables_ok\" />\n";

		if (isset($_REQUEST['ma'])) {
		// multiple removal
			$tablesList = '';
			$fullList = "<ul>";
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				$tablesList .= $a['table'] . ', ';
				$fullList .= "<li>" . sprintf($lang['emajthetableingroup'],$a['appschema'],$a['table'],$a['group']) . "</li>\n";
			}
			$tablesList = substr($tablesList,0,strlen($tablesList)-2);
			$fullList .= "</ul>\n";
			echo "<p>{$lang['emajconfirmremovetblseq']}{$fullList}</p>\n";
		} else {

		// single removal
			$tablesList = $_REQUEST['table'];
			$tableName = sprintf($lang['emajthetableingroup'],$_REQUEST['schema'],$_REQUEST['table'],$_REQUEST['group']);
			echo "<p>{$lang['emajremove']} {$tableName}</p>\n";
		}
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tables\" value=\"", htmlspecialchars($tablesList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['emajmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"REMOVE_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></div>\n";

		echo $misc->form;
		echo "<p><input type=\"submit\" name=\"removetable\" value=\"{$lang['emajremove']}\" id=\"ok\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform tables remove from their tables group
	 */
	function remove_tables_ok() {
		global $lang, $data, $emajdb, $_reload_browser;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			list_schemas();
		} else {

	// process the tables removal
			// get the list of emaj_schema before the removal
			$emajSchemasBefore = $emajdb->getEmajSchemasList();

			$nbTables = $emajdb->removeTables($_POST['schema'],$_POST['tables'],$_POST['mark']);
			if ($nbTables >= 0) {
				// reload the browser only if emaj schemas have been dropped
				if ($emajdb->getEmajSchemasList() <> $emajSchemasBefore)
					$_reload_browser = true;
				list_schemas(sprintf($lang['emajdynremovetablesok'], $nbTables));
			} else {
				list_schemas($lang['emajmodifygrouperr']);
			}
		}
	}

	/**
	 * Prepare assign sequences to a group: ask for properties and confirmation
	 */
	function assign_sequences() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database', 'schemas');

		$misc->printTitle($lang['emajassignsequence']);

		// Get created group names
		$knownGroups = $emajdb->getCreatedGroups();

		// Build the list of tables to processs and count them
		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"assign_sequences_ok\" />\n";

		if (isset($_REQUEST['ma'])) {
		// multiple assign
			$sequencesList = '';
			$fullList = "<ul>";
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				$sequencesList .= $a['sequence'] . ', ';
				$fullList .= "<li>" . sprintf($lang['emajthesequence'],$a['appschema'],$a['sequence']) . "</li>\n";
			}
			$sequencesList = substr($sequencesList,0,strlen($sequencesList)-2);
			$fullList .= "</ul>\n";
			echo "<p>{$lang['emajconfirmassigntblseq']}{$fullList}</p>\n";
		} else {

		// single assign
			$sequencesList = $_REQUEST['sequence'];
			$sequenceName = sprintf($lang['emajthesequence'],$_REQUEST['schema'],$_REQUEST['sequence']);
			echo "<p>{$lang['emajassign']} {$sequenceName}</p>\n";
		}
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"sequences\" value=\"", htmlspecialchars($sequencesList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// group name
		echo "\t<div class=\"form-label required\">{$lang['emajgroup']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"group\">";
		foreach($knownGroups as $r)
			echo "\t\t<option>", htmlspecialchars($r['group_name']), "\n";
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['emajmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"ASSIGN_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></div>\n";

		echo"</div>\n";
		echo $misc->form;
		echo "<p><input type=\"submit\" name=\"assignsequence\" value=\"{$lang['emajassign']}\" id=\"ok\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform sequences assignment into a tables group
	 */
	function assign_sequences_ok() {
		global $lang, $data, $emajdb;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			list_schemas();
		} else {

	// process the sequences assignment
			$nbSequences = $emajdb->assignSequences($_POST['schema'],$_POST['sequences'],$_POST['group'],$_POST['mark']);
			if ($nbSequences>= 0)
				list_schemas(sprintf($lang['emajdynassignsequencesok'], $nbSequences, htmlspecialchars($_POST['group'])));
			else
				list_schemas($lang['emajmodifygrouperr']);
		}
	}

	/**
	 * Prepare move sequences to another group: ask for confirmation
	 */
	function move_sequences() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database', 'schemas');

		$misc->printTitle($lang['emajmovesequence']);

		// Get created group names
		$knownGroups = $emajdb->getCreatedGroups();

		// Build the list of sequences to processs and count them
		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"move_sequences_ok\" />\n";

		if (isset($_REQUEST['ma'])) {
		// multiple move
			$sequencesList = '';
			$fullList = "<ul>";
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				$sequencesList .= $a['sequence'] . ', ';
				$fullList .= "<li>" . sprintf($lang['emajthesequenceingroup'],$a['appschema'],$a['sequence'],$a['group']) . "</li>\n";
			}
			$sequencesList = substr($sequencesList,0,strlen($sequencesList)-2);
			$fullList .= "</ul>\n";
			echo "<p>{$lang['emajconfirmmovetblseq']}{$fullList}</p>\n";
		} else {

		// single move
			$sequencesList = $_REQUEST['sequence'];
			$sequenceName = sprintf($lang['emajthesequenceingroup'],$_REQUEST['schema'],$_REQUEST['sequence'],$_REQUEST['group']);
			echo "<p>{$lang['emajmove']} {$sequenceName}</p>\n";
		}
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"sequences\" value=\"", htmlspecialchars($sequencesList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// group name
		echo "\t<div class=\"form-label required\">{$lang['emajgroup']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"group\">";
		foreach($knownGroups as $r)
			echo "\t\t<option>", htmlspecialchars($r['group_name']), "\n";
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['emajmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"MOVE_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></div>\n";

		echo $misc->form;
		echo "<p><input type=\"submit\" name=\"movesequence\" value=\"{$lang['emajmove']}\" id=\"ok\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform sequences move into another tables group
	 */
	function move_sequences_ok() {
		global $lang, $data, $emajdb;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			list_schemas();
		} else {

	// process the sequences assignment
			$nbSequences = $emajdb->moveSequences($_POST['schema'],$_POST['sequences'],$_POST['group'],$_POST['mark']);
			if ($nbSequences>= 0)
				list_schemas(sprintf($lang['emajdynmovesequencesok'], $nbSequences, htmlspecialchars($_POST['group'])));
			else
				list_schemas($lang['emajmodifygrouperr']);
		}
	}
	/**
	 * Prepare remove sequences: ask for confirmation
	 */
	function remove_sequences() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database', 'schemas');

		$misc->printTitle($lang['emajremovesequence']);

		// Build the list of sequences to processs and count them
		echo "<form action=\"schemas.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"remove_sequences_ok\" />\n";

		if (isset($_REQUEST['ma'])) {
		// multiple removal
			$sequencesList = '';
			$fullList = "<ul>";
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				$sequencesList .= $a['sequence'] . ', ';
				$fullList .= "<li>" . sprintf($lang['emajthesequenceingroup'],$a['appschema'],$a['sequence'],$a['group']) . "</li>\n";
			}
			$tablesList = substr($sequencesList,0,strlen($sequencesList)-2);
			$fullList .= "</ul>\n";
			echo "<p>{$lang['emajconfirmremovetblseq']}{$fullList}</p>\n";
		} else {

		// single removal
			$sequencesList = $_REQUEST['sequence'];
			$sequenceName = sprintf($lang['emajthesequenceingroup'],$_REQUEST['schema'],$_REQUEST['sequence'],$_REQUEST['group']);
			echo "<p>{$lang['emajremove']} {$sequenceName}</p>\n";
		}
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"sequences\" value=\"", htmlspecialchars($sequencesList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['emajmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"REMOVE_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></div>\n";

		echo $misc->form;
		echo "<p><input type=\"submit\" name=\"removesequence\" value=\"{$lang['emajremove']}\" id=\"ok\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform sequences remove from their tables group
	 */
	function remove_sequences_ok() {
		global $lang, $data, $emajdb;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			list_schemas();
		} else {

	// process the sequences removal
			$nbSequences = $emajdb->removeSequences($_POST['schema'],$_POST['sequences'],$_POST['mark']);
			if ($nbSequences>= 0)
				list_schemas(sprintf($lang['emajdynremovesequencesok'], $nbSequences));
			else
				list_schemas($lang['emajmodifygrouperr']);
		}
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

	$misc->printHtmlHeader($lang['strschemas']);
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
