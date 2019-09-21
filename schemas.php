<?php

	/*
	 * Manage schemas in a database
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	// Callback function to dynamicaly modify the schema type column content
	// It replaces the database value by an icon
	function renderSchemaType($val) {
		global $misc, $lang;
		if ($val == 'E') {
			$icon = $misc->icon('EmajIcon');
			$alt = $lang['emajschema'];
			return "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		}
		return;
	}

	/**
	 * Show the list of schemas in the database
	 * and the tables and sequences lists if a schema has already been selected
	 */
	function list_schemas($msg = '', $errMsg = '', $prevSchema = '') {
		global $data, $misc, $conf, $emajdb;
		global $lang;

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
				'type' => array(
					'title' => $lang['strtype'],
					'field' => field('nsptype'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderSchemaType','align' => 'center')
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
			),
		));

		$misc->printTable($schemas, $columns, $actions, 'schemas-schemas', $lang['strnoschemas'], null, array('sorter' => true, 'filter' => true));

		// Tables and séquences for the selected schema, if any

		if (isset($_REQUEST['schema']) && $_REQUEST['schema'] != '') {

			// is it an E-Maj schema ?
			$isEmajSchema = false;
			foreach ($schemas as $schema) {
				if ($schema["nspname"] == $_REQUEST['schema'] && $schema["nsptype"] == 'E') {
					$isEmajSchema = true;
				}
			}
			// emaj attribute and actions to manage ?
			$emajAttributesToManage = ($emajdb->isEnabled() && $emajdb->isAccessible() && ! $isEmajSchema);

			// Display the tables list
			echo "<a name=\"tables\">&nbsp;</a>\n";

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
				),
			));

			$actions = array(
				'browse' => array(
					'content' => $lang['strbrowse'],
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

			if (!$data->hasTablespaces()) unset($columns['tablespace']);

			$misc->printTable($tables, $columns, $actions, 'tables-tables', $lang['strnotables'], null, array('sorter' => true, 'filter' => true));

			// Display the sequences list

			echo "<a name=\"sequences\">&nbsp;</a>\n";

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
				),
			));

			$misc->printTable($sequences, $columns, $actions, 'sequences-sequences', $lang['strnosequences'], null, array('sorter' => true, 'filter' => true));
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
		default:
			list_schemas();
			break;
	}

	$misc->printFooter();

?>
