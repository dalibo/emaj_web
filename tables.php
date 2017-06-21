<?php

	/*
	 * List tables in a database
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';


	/**
	 * Ask for select parameters and perform select
	 */
	function doSelectRows($confirm, $msg = '') {
		global $data, $misc, $_no_output;
		global $lang;

		if ($confirm) {
			$misc->printTrail('table');
			$misc->printTitle($lang['strselect'], 'pg.sql.select');
			$misc->printMsg($msg);

			$attrs = $data->getTableAttributes($_REQUEST['table']);

			echo "<form action=\"tables.php\" method=\"post\" id=\"selectform\">\n";
			if ($attrs->recordCount() > 0) {
				// JavaScript for select all feature
				echo "<script type=\"text/javascript\">\n";
				echo "//<![CDATA[\n";
				echo "	function selectAll() {\n";
				echo "		for (var i=0; i<document.getElementById('selectform').elements.length; i++) {\n";
				echo "			var e = document.getElementById('selectform').elements[i];\n";
				echo "			if (e.name.indexOf('show') == 0) e.checked = document.getElementById('selectform').selectall.checked;\n";
				echo "		}\n";
				echo "	}\n";
				echo "//]]>\n";
				echo "</script>\n";

				echo "<table>\n";

				// Output table header
				echo "<tr><th class=\"data\">{$lang['strshow']}</th><th class=\"data\">{$lang['strcolumn']}</th>";
				echo "<th class=\"data\">{$lang['strtype']}</th><th class=\"data\">{$lang['stroperator']}</th>";
				echo "<th class=\"data\">{$lang['strvalue']}</th></tr>";

				$i = 0;
				while (!$attrs->EOF) {
					$attrs->fields['attnotnull'] = $data->phpBool($attrs->fields['attnotnull']);
					// Set up default value if there isn't one already
					if (!isset($_REQUEST['values'][$attrs->fields['attname']]))
						$_REQUEST['values'][$attrs->fields['attname']] = null;
					if (!isset($_REQUEST['ops'][$attrs->fields['attname']]))
						$_REQUEST['ops'][$attrs->fields['attname']] = null;
					// Continue drawing row
					$id = (($i % 2) == 0 ? '1' : '2');
					echo "<tr class=\"data{$id}\">\n";
					echo "<td style=\"white-space:nowrap;\">";
					echo "<input type=\"checkbox\" name=\"show[", htmlspecialchars($attrs->fields['attname']), "]\"",
						isset($_REQUEST['show'][$attrs->fields['attname']]) ? ' checked="checked"' : '', " /></td>";
					echo "<td style=\"white-space:nowrap;\">", $misc->printVal($attrs->fields['attname']), "</td>";
					echo "<td style=\"white-space:nowrap;\">", $misc->printVal($data->formatType($attrs->fields['type'], $attrs->fields['atttypmod'])), "</td>";
					echo "<td style=\"white-space:nowrap;\">";
					echo "<select name=\"ops[{$attrs->fields['attname']}]\">\n";
					foreach (array_keys($data->selectOps) as $v) {
						echo "<option value=\"", htmlspecialchars($v), "\"", ($v == $_REQUEST['ops'][$attrs->fields['attname']]) ? ' selected="selected"' : '',
						">", htmlspecialchars($v), "</option>\n";
					}
					echo "</select>\n</td>\n";
					echo "<td style=\"white-space:nowrap;\">", $data->printField("values[{$attrs->fields['attname']}]",
						$_REQUEST['values'][$attrs->fields['attname']], $attrs->fields['type']), "</td>";
					echo "</tr>\n";
					$i++;
					$attrs->moveNext();
				}
				// Select all checkbox
				echo "<tr><td colspan=\"5\"><input type=\"checkbox\" id=\"selectall\" name=\"selectall\" onclick=\"javascript:selectAll()\" /><label for=\"selectall\">{$lang['strselectallfields']}</label></td>";
				echo "</tr></table>\n";
			}
			else echo "<p>{$lang['strinvalidparam']}</p>\n";

			echo "<p><input type=\"hidden\" name=\"action\" value=\"selectrows\" />\n";
			echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";
			echo "<input type=\"hidden\" name=\"subject\" value=\"table\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"select\" value=\"{$lang['strselect']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		}
		else {
			if (!isset($_POST['show'])) $_POST['show'] = array();
			if (!isset($_POST['values'])) $_POST['values'] = array();
			if (!isset($_POST['nulls'])) $_POST['nulls'] = array();

			// Verify that they haven't supplied a value for unary operators
			foreach ($_POST['ops'] as $k => $v) {
				if ($data->selectOps[$v] == 'p' && $_POST['values'][$k] != '') {
					doSelectRows(true, $lang['strselectunary']);
					return;
				}
			}

			if (sizeof($_POST['show']) == 0)
				doSelectRows(true, $lang['strselectneedscol']);
			else {
				// Generate query SQL
				$query = $data->getSelectSQL($_REQUEST['table'], array_keys($_POST['show']),
					$_POST['values'], $_POST['ops']);
				$_REQUEST['query'] = $query;
				$_REQUEST['return'] = 'selectrows';

				$_no_output = true;
				include('./display.php');
				exit;
			}
		}
	}


	/**
	 * Show default list of tables in the database
	 */
	function doDefault($msg = '') {
		global $data, $conf, $misc, $data;
		global $lang;

		$misc->printTrail('schema');
		$misc->printTabs('schema','tables');
		$misc->printMsg($msg);

		$tables = $data->getTables();

		$columns = array(
			'table' => array(
				'title' => $lang['strtable'],
				'field' => field('relname'),
				'url'	=> "redirect.php?subject=table&amp;{$misc->href}&amp;",
				'vars'  => array('table' => 'relname'),
			),
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
			'actions' => array(
				'title' => $lang['stractions'],
			),
			'comment' => array(
				'title' => $lang['strcomment'],
				'field' => field('relcomment'),
			),
		);

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
			'select' => array(
				'content' => $lang['strselect'],
				'attr'=> array (
					'href' => array (
						'url' => 'tables.php',
						'urlvars' => array (
							'action' => 'confselectrows',
							'table' => field('relname')
						)
					)
				)
			),
		);

		if (!$data->hasTablespaces()) unset($columns['tablespace']);

		$misc->printTable($tables, $columns, $actions, 'tables-tables', $lang['strnotables']);

	}
	
	/**
	 * Generate XML for the browser tree.
	 */
	function doTree() {
		global $misc, $data;

		$tables = $data->getTables();

		$reqvars = $misc->getRequestVars('table');

		$attrs = array(
			'text'   => field('relname'),
			'icon'   => 'Table',
			'iconAction' => url('display.php',
							$reqvars,
							array('table' => field('relname'))
						),
			'toolTip'=> field('relcomment'),
			'action' => url('redirect.php',
							$reqvars,
							array('table' => field('relname'))
						)
		);

		$misc->printTree($tables, $attrs, 'tables');
		exit;
	}


	if ($action == 'tree') doTree();

	$misc->printHeader($lang['strtables']);
	$misc->printBody();

	switch ($action) {
		case 'selectrows':
			if (!isset($_POST['cancel'])) doSelectRows(false);
			else doDefault();
			break;
		case 'confselectrows':
			doSelectRows(true);
			break;
		default:
			doDefault();
			break;
	}

	$misc->printFooter();

?>
