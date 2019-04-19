<?php

	/*
	 * Display the properties of a table: columns and tables group ownership
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';

	// Callback function to dynamicaly translate a boolean column into the user's language
	function renderBoolean($val) {
		global $lang;
		return $val == 't' ? $lang['stryes'] : $lang['strno'];
	}

	/**
	 * Show default list of columns in the table
	 */
	function doDefault($msg = '') {
		global $data, $conf, $misc, $lang, $emajdb;

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
		$triggers = $emajdb->getTriggers($_REQUEST['schema'], $_REQUEST['table']);

		// Show comment, if any
		if ($tdata->fields['relcomment'] !== null)
			echo "<p>{$lang['strcommentlabel']}<span class=\"comment\">{$misc->printVal($tdata->fields['relcomment'])}</span></p>\n";

		// Show tables group ownership, if any
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
			$group = $emajdb->getTableGroupTblSeq($_REQUEST['schema'], $_REQUEST['table']);
			if ($group != '')
				echo "<p>" . sprintf($lang['emajtblgroupownership'],$group) . "</p>\n";
			else
				echo "<p>{$lang['emajtblnogroupownership']}</p>\n";
		}

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

		// Display the table triggers

		$misc->printTitle($lang['strtriggers']);

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

		$misc->printTable($triggers, $columns, $actions, 'tblproperties-triggers', $lang['strnotrigger'], null, array('sorter' => true, 'filter' => true));
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
		case 'properties':
			if (isset($_POST['cancel'])) doDefault();
			else doProperties();
			break;
		default:
			doDefault();
			break;
	}

	$misc->printFooter();

?>
