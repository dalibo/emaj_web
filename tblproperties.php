<?php

	/*
	 * List tables in a database
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';

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

	/**
	 * Show default list of columns in the table
	 */
	function doDefault($msg = '') {
		global $data, $conf, $misc;
		global $lang;

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

		$misc->printTrail('table');
		$misc->printTabs('table','columns');
		$misc->printMsg($msg);

		// Get table
		$tdata = $data->getTable($_REQUEST['table']);
		// Get columns
		$attrs = $data->getTableAttributes($_REQUEST['table']);
		// Get constraints keys
		$ck = $data->getConstraintsWithFields($_REQUEST['table']);

		// Show comment if any
		if ($tdata->fields['relcomment'] !== null)
			echo '<p class="comment">', $misc->printVal($tdata->fields['relcomment']), "</p>\n";

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

		$misc->printTable($attrs, $columns, $actions, 'tblproperties-tblproperties', null, 'attPre');

		$navlinks = array (
			'browse' => array (
				'attr'=> array (
					'href' => array (
						'url' => 'display.php',
						'urlvars' => array (
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'table' => $_REQUEST['table'],
							'subject' => 'table',
							'return' => 'table'
						)
					)
				),
				'content' => $lang['strbrowse']
			),
		);
		$misc->printNavLinks($navlinks,
			'tblproperties-tblproperties'
			, get_defined_vars()
		);

	}

	$misc->printHeader($lang['strtables'] . ' - ' . $_REQUEST['table']);
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
