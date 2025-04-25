<?php

	/*
	 * Manage schemas in a database
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');
	include_once('./libraries/tblseqcommon.inc.php');
	include_once('./libraries/tblactions.inc.php');
	include_once('./libraries/seqactions.inc.php');

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
 * Main functions displaying pages
 *******************************************************************************************************/

	/**
	 * Show the list of schemas in the database
	 * and the tables and sequences lists if a schema has already been selected
	 */
	function doDefault($msg = '', $errMsg = '', $prevSchema = '') {
		global $data, $misc, $conf, $emajdb, $lang, $nbGroups;

		if (!isset($_REQUEST['schema'])) $_REQUEST['schema'] = $prevSchema;
		if (is_array($_REQUEST['schema'])) $_REQUEST['schema'] = $_REQUEST['schema'][0];

		// If a schema has been selected, check it still exists
		if (isset($_REQUEST['schema']) && $_REQUEST['schema'] != '') {
			if (! $emajdb->existsSchema($_REQUEST['schema'])) {
				// If the schema doesn't exist anymore, recall the function with an error message and reload the browser
				$errorMessage = sprintf($lang['strschemamissing'], htmlspecialchars($_REQUEST['schema']));
				unset($_REQUEST['schema']);
				doDefault('', $errorMessage);
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

		$misc->printMsg($msg, $errMsg);
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

			// Get the number of created groups (needed to display or hide some actions)
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
						'url'   => "groupproperties.php&amp;{$misc->href}&amp;",
						'vars'  => array('group' => 'rel_group')
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
						'url'   => "groupproperties.php&amp;{$misc->href}&amp;",
						'vars'  => array('group' => 'rel_group')
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

/********************************************************************************************************
 * Main piece of code
 *******************************************************************************************************/

	if ($action == 'tree')
		doTree();

	$scripts = "<script src=\"js/schemas.js\"></script>";

	$misc->printHtmlHeader($lang['strschemas'], $scripts);
	$misc->printBody();

	if (isset($_POST['cancel']))
		$action = '';

	switch ($action) {
		case 'list_schemas':
			doDefault();
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
		case 'remove_sequences';
			remove_sequences();
			break;
		case 'remove_sequences_ok':
			remove_sequences_ok();
			break;
		default:
			doDefault();
			break;
	}

	$misc->printFooter();

?>
