<?php

	/**
	 * Common relation browsing function that is used for tables and sql statements result
	 * to avoid code duplication.
	 * @param $query The SQL SELECT string to execute
	 * @param $count The same SQL query, but only retrieves the count of the rows (AS total)
	 * @param $return The return section
	 * @param $page The current page
	 *
	 * $Id: display.php,v 1.68 2008/04/14 12:44:27 ioguix Exp $
	 */

	// Prevent timeouts on large exports (non-safe mode only)
	if (!ini_get('safe_mode')) set_time_limit(0);

	// Include application functions
	include_once('./libraries/lib.inc.php');

	global $conf, $lang;

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';

	
	/* build & return the FK information data structure 
	 * used when deciding if a field should have a FK link or not*/
	function &getFKInfo() {
		global $data, $misc, $lang;
		 
		// Get the foreign key(s) information from the current table
		$fkey_information = array('byconstr' => array(), 'byfield' => array());

		if (isset($_REQUEST['table'])) {
			$constraints = $data->getConstraintsWithFields($_REQUEST['table']);
			if ($constraints->recordCount() > 0) {

				$fkey_information['common_url'] = $misc->getHREF('schema') .'&amp;subject=table';

				/* build the FK constraints data structure */
				while (!$constraints->EOF) {
					$constr =& $constraints->fields;
					if ($constr['contype'] == 'f') {

						if (!isset($fkey_information['byconstr'][$constr['conid']])) {
							$fkey_information['byconstr'][$constr['conid']] = array (
								'url_data' => 'table='. urlencode($constr['f_table']) .'&amp;schema='. urlencode($constr['f_schema']),
								'fkeys' => array(),
								'consrc' => $constr['consrc']
							);
						}

						$fkey_information['byconstr'][$constr['conid']]['fkeys'][$constr['p_field']] = $constr['f_field'];

						if (!isset($fkey_information['byfield'][$constr['p_field']]))
							$fkey_information['byfield'][$constr['p_field']] = array();

						$fkey_information['byfield'][$constr['p_field']][] = $constr['conid'];
					}
					$constraints->moveNext();
				}
			}
		}

		return $fkey_information;
	}

	/* Print table header cells 
	 * @param $args - associative array for sort link parameters
	 * */
	function printTableHeaderCells(&$rs, $args, $withOid) {
		global $misc, $data, $conf;
		$j = 0;

		foreach ($rs->fields as $k => $v) {

			if (($k === $data->id) && ( !($withOid && $conf['show_oids']) )) {
				$j++;
				continue;
			}
			$finfo = $rs->fetchField($j);

			if ($args === false) {
				echo "<th class=\"data\">", $misc->printVal($finfo->name), "</th>\n";
			}
			else {
				$args['page'] = $_REQUEST['page'];
				$args['sortkey'] = $j + 1;
				// Sort direction opposite to current direction, unless it's currently ''
				$args['sortdir'] = (
					$_REQUEST['sortdir'] == 'asc'
					and $_REQUEST['sortkey'] == ($j + 1)
				) ? 'desc' : 'asc';

				$sortLink = http_build_query($args);

				echo "<th class=\"data\"><a href=\"?{$sortLink}\">"
					, $misc->printVal($finfo->name)
					, "</a></th>\n";
			}
			$j++;
		}

		reset($rs->fields);
	}

	/* Print data-row cells */
	function printTableRowCells(&$rs, &$fkey_information, $withOid) {
		global $data, $misc, $conf;
		$j = 0;
		
		if (!isset($_REQUEST['strings'])) $_REQUEST['strings'] = 'collapsed';

		foreach ($rs->fields as $k => $v) {
			$finfo = $rs->fetchField($j++);

			if (($k === $data->id) && ( !($withOid && $conf['show_oids']) )) continue;
			elseif ($v !== null && $v == '') echo "<td>&nbsp;</td>";
			else {
				echo "<td style=\"white-space:nowrap;\">";

				if (($v !== null) && isset($fkey_information['byfield'][$k])) {
					foreach ($fkey_information['byfield'][$k] as $conid) {

						$query_params = $fkey_information['byconstr'][$conid]['url_data'];

						foreach ($fkey_information['byconstr'][$conid]['fkeys'] as $p_field => $f_field) {
							$query_params .= '&amp;'. urlencode("fkey[{$f_field}]") .'='. urlencode($rs->fields[$p_field]);
						}

						/* $fkey_information['common_url'] is already urlencoded */
						$query_params .= '&amp;'. $fkey_information['common_url'];
						echo "<div style=\"display:inline-block;\">";
						echo "<a class=\"fk fk_". htmlentities($conid, ENT_QUOTES, 'UTF-8') ."\" href=\"display.php?{$query_params}\">";
						echo "<img src=\"".$misc->icon('ForeignKey')."\" style=\"vertical-align:middle;\" alt=\"[fk]\" title=\""
							. htmlentities($fkey_information['byconstr'][$conid]['consrc'], ENT_QUOTES, 'UTF-8')
							."\" />";
						echo "</a>";
						echo "</div>";
					}
					echo $misc->printVal($v, $finfo->type, array('null' => true, 'clip' => ($_REQUEST['strings']=='collapsed'), 'class' => 'fk_value'));
				} else {
					echo $misc->printVal($v, $finfo->type, array('null' => true, 'clip' => ($_REQUEST['strings']=='collapsed')));
				}
				echo "</td>";
			}
		}
	}

	/* Print the FK row, used in ajax requests */
	function doBrowseFK() {
		global $data, $misc, $lang;

		$ops = array();
		foreach($_REQUEST['fkey'] as $x => $y) {
			$ops[$x] = '=';
		}
		$query = $data->getSelectSQL($_REQUEST['table'], array(), $_REQUEST['fkey'], $ops);
		$_REQUEST['query'] = $query;

		$fkinfo =& getFKInfo();

		$max_pages = 1;
		// Retrieve page from query.  $max_pages is returned by reference.
		$rs = $data->browseQuery('SELECT', $_REQUEST['table'], $_REQUEST['query'],  
			null, null, 1, 1, $max_pages);

		echo "<a href=\"\" class=\"fk_delete\"><img alt=\"{$lang['strdelete']}\" src=\"". $misc->icon('Delete') ."\" /></a>\n";
		echo "<div style=\"display:table-cell;\">";

		if (is_object($rs) && $rs->recordCount() > 0) {
			/* we are browsing a referenced table here
			 * we should show OID if show_oids is true
			 * so we give true to withOid in functions bellow
			 * as 3rd paramter */
		
			echo "<table><tr>";
				printTableHeaderCells($rs, false, true);
			echo "</tr>";
			echo "<tr class=\"data1\">\n";
				printTableRowCells($rs, $fkinfo, true);
			echo "</tr>\n";
			echo "</table>\n";
		}
		else
			echo $lang['strnodata'];

		echo "</div>";

		exit;
	}

	/** 
	 * Displays requested data
	 */
	function doBrowse($msg = '') {
		global $data, $conf, $misc, $lang;

		$save_history = false;
		// If current page is not set, default to first page
		if (!isset($_REQUEST['page']))
			$_REQUEST['page'] = 1;
		if (!isset($_REQUEST['nohistory']))
			$save_history = true;
		
		if (isset($_REQUEST['subject'])) {
			$subject = $_REQUEST['subject'];
			if (isset($_REQUEST[$subject])) $object = $_REQUEST[$subject];
		}
		else {
			$subject = '';
		}

		if ($subject == '')
			$misc->printHeader('database', '', '');
		else
			$misc->printHeader('table', 'table', 'content');

		/* This code is used when browsing FK in pure-xHTML (without js) */
		if (isset($_REQUEST['fkey'])) {
			$ops = array();
			foreach($_REQUEST['fkey'] as $x => $y) {
				$ops[$x] = '=';
			}
			$query = $data->getSelectSQL($_REQUEST['table'], array(), $_REQUEST['fkey'], $ops);
			$_REQUEST['query'] = $query;
		}
		
		if (isset($object)) {
			if (isset($_REQUEST['query'])) {
				$_SESSION['sqlquery'] = $_REQUEST['query'];
				$misc->printTitle($lang['strselect']);
				$type = 'SELECT';
			}
			else {
				$misc->printTitle(sprintf($lang['strtblcontent'], $_REQUEST['schema'], $_REQUEST['table']));
				$type = 'TABLE';
			}
		} else {
			$misc->printTitle($lang['strqueryresults']);
			/*we comes from sql.php, $_SESSION['sqlquery'] has been set there */
			$type = 'QUERY';
		}

		$misc->printMsg($msg);

		// If 'sortkey' is not set, default to ''
		if (!isset($_REQUEST['sortkey'])) $_REQUEST['sortkey'] = '';

		// If 'sortdir' is not set, default to ''
		if (!isset($_REQUEST['sortdir'])) $_REQUEST['sortdir'] = '';
	
		// If 'strings' is not set, default to collapsed 
		if (!isset($_REQUEST['strings'])) $_REQUEST['strings'] = 'collapsed';
	
		// Fetch unique row identifier, if this is a table browse request.
		if (isset($object))
			$key = $data->getRowIdentifier($object);
		else
			$key = array();
		
		// Set the schema search path
		if (isset($_REQUEST['search_path'])) {
			if ($data->setSearchPath(array_map('trim',explode(',',$_REQUEST['search_path']))) != 0) {
				return;
			}
		}

		// Retrieve page from query.  $max_pages is returned by reference.
		$rs = $data->browseQuery($type, 
			isset($object) ? $object : null, 
			isset($_SESSION['sqlquery']) ? $_SESSION['sqlquery'] : null,
			$_REQUEST['sortkey'], $_REQUEST['sortdir'], $_REQUEST['page'],
			$conf['max_rows'], $max_pages);

		$fkey_information =& getFKInfo();

		// Build strings for GETs in array
		$_gets = array(
			'server' => $_REQUEST['server'],
			'database' => $_REQUEST['database']
		);

		if (isset($_REQUEST['schema'])) $_gets['schema'] = $_REQUEST['schema'];
		if (isset($object)) $_gets[$subject] = $object;
		if (isset($subject)) $_gets['subject'] = $subject;
		if (isset($_REQUEST['query'])) $_gets['query'] = $_REQUEST['query'];
		if (isset($_REQUEST['count'])) $_gets['count'] = $_REQUEST['count'];
		if (isset($_REQUEST['return'])) $_gets['return'] = $_REQUEST['return'];
		if (isset($_REQUEST['search_path'])) $_gets['search_path'] = $_REQUEST['search_path'];
		if (isset($_REQUEST['table'])) $_gets['table'] = $_REQUEST['table'];
		if (isset($_REQUEST['sortkey'])) $_gets['sortkey'] = $_REQUEST['sortkey'];
		if (isset($_REQUEST['sortdir'])) $_gets['sortdir'] = $_REQUEST['sortdir'];
		if (isset($_REQUEST['nohistory'])) $_gets['nohistory'] = $_REQUEST['nohistory'];
		$_gets['strings'] = $_REQUEST['strings'];

		if ($save_history && is_object($rs) && ($type == 'QUERY')) //{
			$misc->saveScriptHistory($_REQUEST['query']);

		// Prepare and generate the navigation links at the page top
		$navlinks = array();

		$fields = array(
			'server' => $_REQUEST['server'],
			'database' => $_REQUEST['database'],
		);

		if (isset($_REQUEST['schema']))
			$fields['schema'] = $_REQUEST['schema'];
		// Expand/Collapse
		if ($_REQUEST['strings'] == 'expanded')
			$navlinks['collapse'] = array (
				'attr'=> array (
					'href' => array (
						'url' => 'display.php',
						'urlvars' => array_merge(
							$_gets,
							array (
								'strings' => 'collapsed',
								'page' => $_REQUEST['page']
						))
					)
				),
				'content' => $lang['strcollapse']
			);
		else
			$navlinks['collapse'] = array (
				'attr'=> array (
					'href' => array (
						'url' => 'display.php',
						'urlvars' => array_merge(
							$_gets,
							array (
								'strings' => 'expanded',
								'page' => $_REQUEST['page']
						))
					)
				),
				'content' => $lang['strexpand']
			);
		// Refresh
		$navlinks['refresh'] = array (
			'attr'=> array (
				'href' => array (
					'url' => 'display.php',
					'urlvars' => array_merge(
						$_gets,
						array(
							'strings' => $_REQUEST['strings'],
							'page' => $_REQUEST['page']
					))
				)
			),
			'content' => $lang['strrefresh']
		);
		$misc->printNavLinks($navlinks);


		if (is_object($rs) && $rs->recordCount() > 0) {
			// Show page navigation
			$misc->printPages($_REQUEST['page'], $max_pages, $_gets);

			echo "<table id=\"data\">\n<tr>";

			// Check that the key is actually in the result set.  This can occur for select
			// operations where the key fields aren't part of the select.  XXX:  We should
			// be able to support this, somehow.
			foreach ($key as $v) {
				// If a key column is not found in the record set, then we
				// can't use the key.
				if (!in_array($v, array_keys($rs->fields))) {
					$key = array();
					break;
				}
			}

			$buttons = array(
			);
			$actions = array(
			);

			/* we show OIDs only if we are in TABLE or SELECT type browsing */
			printTableHeaderCells($rs, $_gets, isset($object));

			echo "</tr>\n";

			$i = 0;		
			reset($rs->fields);
			while (!$rs->EOF) {
				$id = (($i % 2) == 0 ? '1' : '2');
				echo "<tr class=\"data{$id}\">\n";

				print printTableRowCells($rs, $fkey_information, isset($object));

				echo "</tr>\n";
				$rs->moveNext();
				$i++;
			}
			echo "</table>\n";

			echo "<p>", $rs->recordCount(), " {$lang['strrows']}</p>\n";
			// Show page navigation
			$misc->printPages($_REQUEST['page'], $max_pages, $_gets);
		}
		else echo "<p>{$lang['strnodata']}</p>\n";

		// regenerate the navigation links at the page bottom

		$misc->printNavLinks($navlinks);
	}

	/* shortcuts: this function exit the script for ajax purpose */
	if ($action == 'dobrowsefk') {
		doBrowseFK();
	}

	$scripts = "<script src=\"libraries/js/jquery-ui.min.js\" type=\"text/javascript\"></script>";
	$scripts .= "<script src=\"js/display.js\" type=\"text/javascript\"></script>";
	$scripts .= "<script type=\"text/javascript\">\n";
	$scripts .= "var Display = {\n";
	$scripts .= "errmsg: '". str_replace("'", "\'", $lang['strconnectionfail']) ."'\n";
	$scripts .= "};\n";
	$scripts .= "</script>\n";

	// If a table is specified, then set the title differently
	if (isset($_REQUEST['subject']) && isset($_REQUEST[$_REQUEST['subject']]))
		$misc->printHtmlHeader($lang['strtables'], $scripts);
	else	
		$misc->printHtmlHeader($lang['strqueryresults']);

	$misc->printBody();

	switch ($action) {
		default:
			doBrowse();
			break;
	}

	$misc->printFooter();
?>
