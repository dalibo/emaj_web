<?php

	/*
	 * Display the properties of a table: columns and tables group ownership
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';

	// Callback function to dynamically adjust the icons and links for constraints on table columns
	function cstrRender($s, $p) {
		global $misc, $data;

		$str ='';
		foreach ($p['keys'] as $k => $c) {

			if (is_null($p['keys'][$k]['consrc'])) {
				$atts = $data->getAttributeNames($_REQUEST['table'], explode(' ', $p['keys'][$k]['indkey']));
				$c['consrc'] = ($c['contype'] == 'u' ? "UNIQUE (" : "PRIMARY KEY (") . join(',', $atts) . ')';
			}

			if ($c['p_field'] == $s)
				switch ($c['contype']) {
					case 'p':
						$str .= '<img src="'. $misc->icon('PrimaryKey') .'" alt="[pk]" title="'. htmlentities($c['consrc'], ENT_QUOTES, 'UTF-8') .'" />';
					break;
					case 'f':
						$str .= '<a href="tblproperties.php?'. $misc->href ."&amp;table=". urlencode($c['f_table']) ."&amp;schema=". urlencode($c['f_schema']) ."\"><img src=\"".
							$misc->icon('ForeignKey') .'" alt="[fk]" title="'. htmlentities($c['consrc'], ENT_QUOTES, 'UTF-8') .'" /></a>';
					break;
					case 'u':
						$str .= '<img src="'. $misc->icon('UniqueConstraint') .'" alt="[uniq]" title="'. htmlentities($c['consrc'], ENT_QUOTES, 'UTF-8') .'" />';
					break;
					case 'c':
						$str .= '<img src="'. $misc->icon('CheckConstraint') .'" alt="[check]" title="'. htmlentities($c['consrc'], ENT_QUOTES, 'UTF-8') .'" /></a>';
				}
		}
		return $str;
	}

	// Callback function to dynamicaly add a link to the tables group's description when the group name is suffixed by "###LINK###"
	function renderlinktogroup($val) {
		global $misc;

		if (preg_match("/(.*)###LINK###$/", $val, $matches)) {
			$val = $matches[1];
			return "<a href=\"emajgroups.php?action=show_group&amp;" . $misc->href . "&amp;group=". urlencode($val) . "\">" . $val . "</a>";
		} else {
			return $val;
		}
	}

	// Callback function to dynamicaly translate a boolean column into the user's language
	function renderBoolean($val) {
		global $lang;
		return $val == 't' ? $lang['stryes'] : $lang['strno'];
	}

	// Function to dynamicaly modify actions list for each table column description
	function attPre(&$rowdata, $actions) {
		global $data;
		$rowdata->fields['+type'] = $data->formatType($rowdata->fields['type'], $rowdata->fields['atttypmod']);
		$attname = $rowdata->fields['attname'];
		$table = $_REQUEST['table'];
		$data->fieldClean($attname);
		$data->fieldClean($table);

		$actions['browse']['attr']['href']['urlvars']['query'] = "SELECT \"{$attname}\", count(*) AS \"count\"
			FROM \"{$table}\" GROUP BY \"{$attname}\" ORDER BY \"{$attname}\"";

		return $actions;
	}

	// Function to dynamicaly modify actions list for each trigger
	function triggerPre(&$rowdata, $actions) {

		// disable the switch button if the trigger is an E-Maj trigger
		if (isset($actions['switchkeepenabledtrigger']) && $rowdata->fields['tgisemaj'] == 't') {
			$actions['switchkeepenabledtrigger']['disable'] = true;
		}
		return $actions;
	}

	/**
	 * Show the table's properties: E-Maj group owning the table, if any, list of columns in the table, list of triggers
	 */
	function showProperties($msg = '') {
		global $data, $conf, $misc, $lang, $emajdb;

		$misc->printHeader('table', 'table', 'properties');
		$misc->printMsg($msg);
		$misc->printTitle(sprintf($lang['strtblproperties'], $_REQUEST['schema'], $_REQUEST['table']));

		// Get table
		$tdata = $data->getTable($_REQUEST['table']);
		// Get columns
		$attrs = $data->getTableAttributes($_REQUEST['table']);
		// Get constraints keys
		$ck = $data->getConstraintsWithFields($_REQUEST['table']);
		// Get triggers
		$triggers = $emajdb->getTriggersTable($_REQUEST['schema'], $_REQUEST['table']);

		// Show comment, if any
		if ($tdata->fields['relcomment'] !== null)
			echo "<p>{$lang['strcommentlabel']}<span class=\"comment\">{$misc->printVal($tdata->fields['relcomment'])}</span></p>\n";

		// Display the table structure
		$columns = array(
			'column' => array(
				'title' => $lang['strcolumn'],
				'field' => field('attname'),
				'vars'  => array('column' => 'attname'),
			),
			'type' => array(
				'title' => $lang['strtype'],
				'field' => field('+type'),
			),
			'notnull' => array(
				'title' => $lang['strnotnull'],
				'field' => field('attnotnull'),
				'type'  => 'bool',
				'params'=> array('true' => 'NOT NULL', 'false' => ''),
			),
			'default' => array(
				'title' => $lang['strdefault'],
				'field' => field('adsrc'),
			),
			'keyprop' => array(
				'title' => $lang['strconstraints'],
				'field' => field('attname'),
				'type'  => 'callback',
				'params'=> array(
					'function' => 'cstrRender',
					'keys' => $ck->getArray()
				)
			),
			'comment' => array(
				'title' => $lang['strcomment'],
				'field' => field('comment'),
			),
		);

		$misc->printTable($attrs, $columns, $actions, 'tblproperties-columns', null, 'attPre');

		// Display the E-Maj properties, if any
		if ($emajdb->isEnabled() && $emajdb->isAccessible() && $emajdb->getNumEmajVersion() >= 20200) {

			$misc->printTitle($lang['emajproperties']);

			$type = $emajdb->getEmajTypeTblSeq($_REQUEST['schema'], $_REQUEST['table']);

			if ($type == 'L') {
				echo "<p>{$lang['emajemajlogtable']}</p>\n";
			} elseif ($type == 'E') {
				echo "<p>{$lang['emajinternaltable']}</p>\n";
			} else {
				$groups = $emajdb->getTableGroupsTblSeq($_REQUEST['schema'], $_REQUEST['table']);

				$columns = array(
					'group' => array(
						'title' => $lang['emajgroup'],
						'field' => field('rel_group'),
						'type'	=> 'callback',
						'params'=> array('function' => 'renderlinktogroup')
					),
					'starttime' => array(
						'title' => $lang['strbegin'],
						'field' => field('start_datetime')
					),
					'stoptime' => array(
						'title' => $lang['strend'],
						'field' => field('stop_datetime')
					),
				);
		
				$misc->printTable($groups, $columns, $actions, 'tblproperties-groups', $lang['emajtblnogroupownership']);
			}
		}

		// Display the table triggers

		$misc->printTitle($lang['strtriggers']);

		$urlvars = $misc->getRequestVars();

		$columns = array(
			'tgrank' => array(
				'title' => $lang['emajexecorder'],
				'field' => field('tgrank'),
				'params'=> array('align' => 'center'),
			),
			'tgname' => array(
				'title' => $lang['strtrigger'],
				'field' => field('tgname'),
			),
			'tglevel' => array(
				'title' => $lang['strlevel'],
				'field' => field('tglevel'),
				'params'=> array('align' => 'center'),
			),
			'tgevent' => array(
				'title' => $lang['emajtriggeringevent'],
				'field' => field('tgevent'),
			),
			'tgfnct' => array(
				'title' => $lang['emajcalledfunction'],
				'field' => field('tgfnct'),
			),
			'tgenabled' => array(
				'title' => $lang['emajstate'],
				'field' => field('tgstate'),
			),
			'tgisemaj' => array(
				'title' => $lang['emajisemaj'],
				'field' => field('tgisemaj'),
				'type'	=> 'callback',
				'params'=> array('function' => 'renderBoolean', 'align' => 'center')
			),
		);
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
			if ($emajdb->getNumEmajVersion() >= 30100) {			// version >= 3.1.0
				$columns = array_merge($columns, array(
					'emajisautodisable' => array(
						'title' => $lang['emajisautodisable'],
						'field' => field('tgisautodisable'),
						'info'  => $lang['emajisautodisablehelp'],
						'type'	=> 'callback',
						'params'=> array('function' => 'renderBoolean', 'align' => 'center')
					),
					'actions' => array(
						'title' => $lang['stractions'],
					),
				));
			}
		}

		$actions = array();
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
			if ($emajdb->getNumEmajVersion() >= 30100) {			// version >= 3.1.0
				if ($emajdb->isEmaj_Adm()) {
					$actions = array_merge($actions, array(
						'switchkeepenabledtrigger' => array(
							'content' => $lang['emajswitchautodisable'],
							'attr' => array (
								'href' => array (
									'url' => 'tblproperties.php',
									'urlvars' => array_merge($urlvars, array (
										'action' => 'switch_keep_enabled_trigger',
										'schema' => $_REQUEST['schema'],
										'table' => $_REQUEST['table'],
										'trigger' => field('tgname'),
										'tgisdisableauto' => field('tgisautodisable'),
									)))))
						)
					);
				}
			}
		}

		$misc->printTable($triggers, $columns, $actions, 'tblproperties-triggers', $lang['strnotrigger'], 'triggerPre', array('sorter' => true, 'filter' => false));
	}

	/**
	 * Switch the ignored_app_trigger state for the selected requested trigger.
	 * Then show the updated table's properties
	 */
	function doSwitchIgnoredAppTriggerState() {
		global $lang, $emajdb;

		if ($_REQUEST['tgisdisableauto'] == 't') {
			// the trigger is currently NOT set as 'not to be automatically disabled at rollback', so set it
			$action = 'ADD';
		} else {
			// the trigger is currently set as 'not to be automatically disabled at rollback', so unset it
			$action = 'REMOVE';
		}
		$nbTriggers = $emajdb->ignoreAppTrigger($action, $_REQUEST['schema'], $_REQUEST['table'], $_REQUEST['trigger']);

		if ($nbTriggers > 0) {
			showProperties(sprintf($lang['emajtriggerpropswitchedok'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		} else {
			showProperties(sprintf($lang['emajtriggerprocerr'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		}
	}

	function doTree() {
		global $misc, $data;

		$columns = $data->getTableAttributes($_REQUEST['table']);
		$reqvars = $misc->getRequestVars('column');

		$attrs = array (
			'text'   => field('attname'),
			'icon'   => 'Column',
			'iconAction' => url('display.php',
								$reqvars,
								array(
									'table'		=> $_REQUEST['table'],
									'column'	=> field('attname'),
									'query'		=> replace(
														'SELECT "%column%", count(*) AS "count" FROM "%table%" GROUP BY "%column%" ORDER BY "%column%"',
														array (
															'%column%' => field('attname'),
															'%table%' => $_REQUEST['table']
														)
													)
								)
							),
			'toolTip'=> field('comment')
		);

		$misc->printTree($columns, $attrs, 'tblcolumns');

		exit;
	}

	if ($action == 'tree') doTree();

	$misc->printHtmlHeader($lang['strtables'] . ' - ' . $_REQUEST['table']);
	$misc->printBody();

	switch ($action) {
		case 'switch_keep_enabled_trigger':
			doSwitchIgnoredAppTriggerState();
			break;
		default:
			showProperties();
			break;
	}

	$misc->printFooter();

?>
