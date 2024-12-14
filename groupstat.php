<?php
	/*
	 * Manage the tables groups recorded changes statistics
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

/********************************************************************************************************
 * Callback functions 
 *******************************************************************************************************/

	// Callback function to dynamicaly translate a boolean column into an icon
	function renderBooleanIcon($val) {
		global $misc;

		if ($val == 't') {
			$icon = 'Checkmark';
		} else {
			$icon = 'RedX';
		}
		return "<img src=\"" . $misc->icon($icon) . "\" class=\"cellicon\" />";
	}

/********************************************************************************************************
 * Main functions displaying pages
 *******************************************************************************************************/

	/**
	 * Show changes statistics between 2 marks or since a mark for tables or sequences
	 */
	function changes_stat_group() {
		global $misc, $lang, $emajdb, $_reload_browser;

		if (! $emajdb->existsGroup($_REQUEST['group'])) {
			show_groups('', sprintf($lang['strgroupmissing'], htmlspecialchars($_REQUEST['group'])));
			$_reload_browser = true;
			return;
		}

		$misc->printHeader('emaj', 'emajgroup', 'emajchangesstat');

		$misc->printTitle(sprintf($lang['strchangesgroup'], htmlspecialchars($_REQUEST['group'])));

		// display the form

		$estimateTables = (isset($_REQUEST['estimatetables'])) ? true : false;
		$estimateSequences = (isset($_REQUEST['estimatesequences'])) ? true : false;
		$detailTables = (isset($_REQUEST['detailtables'])) ? true : false;

		// get marks from database
		$marks = $emajdb->getMarks($_REQUEST['group']);

		// get group's characteristics
		$group = $emajdb->getGroup($_REQUEST['group']);

		if ($marks->recordCount() < 1) {

			// No mark recorded for the group => no update logged => no stat to display
			echo "<p>{$lang['strnomark']}</p>\n"; 

		} else {

			// form for statistics selection
			echo "<form id=\"statistics_form\" action=\"groupstat.php?action=changes_stat_group&amp;back=detail&amp;{$misc->href}\"";
			echo "  method=\"post\" enctype=\"multipart/form-data\">\n";

			echo "<div class=\"form-container\">\n";
			// First mark defining the marks range to analyze
			echo "\t<div class=\"form-label\">{$lang['strstartmark']}</div>\n";
			echo "\t<div class=\"form-input\">\n";
			echo "\t\t<select name=\"rangestart\" id=\"rangestart\">\n";
			foreach($marks as $r)
				echo "\t\t\t<option value=\"", htmlspecialchars($r['mark_name']), "\" >", htmlspecialchars($r['mark_name']), " ({$r['mark_datetime']})</option>\n";
			echo "\t\t</select>\n";
			echo "\t</div>\n";
			echo "\t<div class=\"form-comment\"></div>\n";

			// Last mark defining the marks range to analyze
			echo "\t<div class=\"form-label\">{$lang['strendmark']}</div>\n";
			echo "\t<div class=\"form-input\">\n";
			echo "\t\t<select name=\"rangeend\" id=\"rangeend\" >\n";
			echo "\t\t\t<option value=\"currentsituation\">{$lang['strcurrentsituation']}</option>\n";
			foreach($marks as $r)
				echo "\t\t\t<option value=\"", htmlspecialchars($r['mark_name']), "\" >", htmlspecialchars($r['mark_name']), " ({$r['mark_datetime']})</option>\n";
			echo "\t\t</select>\n";
			echo "\t</div>\n";
			echo "\t<div class=\"form-comment\"></div>\n";
			echo "</div>\n";

			// Buttons
			echo "<div class=\"actionslist\">\n";
			echo "\t<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
			echo "\t<input type=\"submit\" name=\"estimatetables\" value=\"{$lang['strestimatetables']}\" />\n";
			if ($emajdb->getNumEmajVersion() >= 40301) {			// version >= 4.3.1
				echo "\t<input type=\"submit\" name=\"estimatesequences\" value=\"{$lang['strestimatesequences']}\" />\n";
			}
			echo "\t<input type=\"submit\" name=\"detailtables\" value=\"{$lang['strdetailtables']}\" />\n";
			echo "\t<img src=\"{$misc->icon('Warning')}\" alt=\"warning\" title=\"{$lang['strdetailedlogstatwarning']}\" style=\"vertical-align:middle; height:22px;\"/>";
			echo "</div>\n";
			echo "</form>\n";

			// JQuery scripts
			echo "<script>\n";

			// JQuery to remove the last mark as it cannot be selected as end mark
			echo "  $(\"#rangeend option:last-child\").remove();\n";

			// JQuery to set the selected start mark by default 
			// (the previous requested start mark or the first mark if no stat are already displayed)
			if (isset($_REQUEST['rangestart'])) {
				echo "  $(\"#rangestart option[value='{$_REQUEST['rangestart']}']\").attr(\"selected\", true);\n";
			} else {
				echo "  $(\"#rangestart option:first-child\").attr(\"selected\", true);\n";
			}

			// JQuery to set the selected end mark by default 
			// (the previous requested end mark or the current state if no stat are already displayed)
			if (isset($_REQUEST['rangeend'])) {
				echo "  $(\"#rangeend option[value='{$_REQUEST['rangeend']}']\").attr(\"selected\", true);\n";
			} else {
				echo "  $(\"#rangeend option:first-child\").attr(\"selected\", true);\n";
			}

			// JQuery script to avoid rangestart > rangeend
				// After document loaded
			echo "  $(document).ready(function() {\n";
			echo "    mark = $(\"#rangestart option:selected\").val();\n";
			echo "    todisable = false;\n";
			echo "    $(\"#rangeend option\").each(function() {\n";
			echo "      $(this).prop('disabled', todisable);\n";
			echo "      if ($(this).val() == mark) {todisable = true;}\n";
			echo "    });\n";
			echo "    mark = $(\"#rangeend option:selected\").val();\n";
			echo "    todisable = true;\n";
			echo "    if (mark == \"currentsituation\") {todisable = false;}\n";
			echo "    $(\"#rangestart option\").each(function() {\n";
			echo "      if ($(this).val() == mark) { todisable = false; }\n";
			echo "      $(this).prop('disabled', todisable);\n";
			echo "    });\n";
			echo "  });\n";

				// At each list box change
			echo "  $(\"#rangestart\").change(function () {\n";
			echo "    mark = $(\"#rangestart option:selected\").val();\n";
			echo "    todisable = false;\n";
			echo "    $(\"#rangeend option\").each(function() {\n";
			echo "      $(this).prop('disabled', todisable);\n";
			echo "      if ($(this).val() == mark) {todisable = true;}\n";
			echo "    });\n";
			echo "  });\n";
			echo "  $(\"#rangeend\").change(function () {\n";
			echo "    mark = $(\"#rangeend option:selected\").val();\n";
			echo "    todisable = true;\n";
			echo "    if (mark == \"currentsituation\") {todisable = false;}\n";
			echo "    $(\"#rangestart option\").each(function() {\n";
			echo "      if ($(this).val() == mark) { todisable = false; }\n";
			echo "      $(this).prop('disabled', todisable);\n";
			echo "    });\n";
			echo "  });\n";

			echo "</script>\n";

			// If any stat is requested, get common data
			if ($estimateTables || $estimateSequences || $detailTables) {
				$groupStat = $emajdb->getNbObjectsGroupInPeriod($_REQUEST['group'],$_REQUEST['rangestart'],$_REQUEST['rangeend']);
			}

			// If tables estimates stats are requested, display them
			if ($estimateTables) {
				disp_tables_estimates_stat_section($groupStat);
			}

			// If Sequences estimates stats are requested, display them
			if ($estimateSequences) {
				disp_sequences_estimates_stat_section($groupStat);
			}

			// If tables detailed stats are requested, display them
			if ($detailTables) {
				disp_tables_details_stat_section($groupStat);
			}
		}
	}

	/**
	 * This function is called by the changes_stat_group() function.
	 * It generates the page section corresponding to the tables estimates statistics output
	 */
	function disp_tables_estimates_stat_section($groupStat) {
		global $misc, $lang, $emajdb;

		// Get statistics from E-Maj
		if ($_REQUEST['rangeend'] == 'currentsituation')
			$stats = $emajdb->getLogStatGroup($_REQUEST['group'], $_REQUEST['rangestart'],'');
		else
			$stats = $emajdb->getLogStatGroup($_REQUEST['group'], $_REQUEST['rangestart'], $_REQUEST['rangeend']);

		$summary = $emajdb->getLogStatSummary();

		if ($emajdb->getNumEmajVersion() >= 40400) {			// version >= 4.4.0
			$nbLogSession = $emajdb->getNbLogSessionInPeriod($_REQUEST['group'], $_REQUEST['rangestart'], $_REQUEST['rangeend']);
		} else {
			$nbLogSession = 0;
		}

		// Title
		if ($_REQUEST['rangeend'] == 'currentsituation')
			$misc->printSubtitle(sprintf($lang['strchangestblsince'], htmlspecialchars($_REQUEST['rangestart'])));
		else
			$misc->printSubtitle(sprintf($lang['strchangestblbetween'], htmlspecialchars($_REQUEST['rangestart']), htmlspecialchars($_REQUEST['rangeend'])));

		// Display summary statistics
		echo "<table class=\"data\"><tr>\n";
		echo "<th class=\"data\"></th>";
		echo "<th class=\"data\">{$lang['strtblingroup']}</th>";
		echo "<th class=\"data\">{$lang['strtblwithchanges']}</th>";
		echo "<th class=\"data\">{$lang['strchanges']}</th>";
		echo "</tr><tr class=\"data1\">\n";
		echo "<th class=\"data\" style=\"font-size: larger;\">{$lang['strestimates']}</td>\n";
		echo "<td class=\"center\">{$groupStat->fields['nb_tbl_in_group']}</td>";
		echo "<td class=\"center\">{$summary->fields['nb_tables']}</td>";
		echo "<td class=\"center\">{$summary->fields['sum_rows']}</td>";
		echo "</tr></table>\n";

		if ($nbLogSession > 1) {
			echo "<p><img src=\"{$misc->icon('Warning')}\" alt=\"warning\" style=\"vertical-align:middle; height:22px;\"/> {$lang['strlogsessionwarning']}</p>";
		}
		echo "<hr/>\n";

		if ($summary->fields['nb_tables'] > 0) {

			// Display per table statistics
			$urlvars = $misc->getRequestVars();

			$columns = array(
				'schema' => array(
					'title' => $lang['strschema'],
					'field' => field('stat_schema'),
					'url'   => "schemas.php?action=list_schemas&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema'),
				),
				'table' => array(
					'title' => $lang['strtable'],
					'field' => field('stat_table'),
					'url'	=> "redirect.php?subject=table&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema', 'table' => 'stat_table'),
				));
			if ($emajdb->getNumEmajVersion() >= 20300) {			// version >= 2.3.0
				$columns = array_merge($columns, array(
					'start_mark' => array(
						'upper_title' => $lang['strbegin'],
						'upper_title_colspan' => 2,
						'title' => $lang['strmark'],
						'field' => field('stat_first_mark'),
					),
					'start_datetime' => array(
						'title' => $lang['strdatetime'],
						'field' => field('stat_first_mark_datetime'),
						'type' => 'spanned',
						'params'=> array(
							'dateformat' => $lang['strrecenttimestampformat'],
							'locale' => $lang['applocale'],
							'class' => 'tooltip left-aligned-tooltip',
							),
					),
					'end_mark' => array(
						'upper_title' => $lang['strend'],
						'upper_title_colspan' => 2,
						'title' => $lang['strmark'],
						'field' => field('stat_last_mark'),
					),
					'end_datetime' => array(
						'title' => $lang['strdatetime'],
						'field' => field('stat_last_mark_datetime'),
						'type' => 'spanned',
						'params'=> array(
							'dateformat' => $lang['strrecenttimestampformat'],
							'locale' => $lang['applocale'],
							'class' => 'tooltip left-aligned-tooltip',
							),
					),
				));
			}
			$columns = array_merge($columns, array(
				'nbrow' => array(
					'title' => $lang['strstatrows'],
					'field' => field('stat_rows'),
					'type'  => 'numeric'
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
			));

			if ($emajdb->getNumEmajVersion() >= 40300) {			// version >= 4.3.0
				// Request parameters to prepare the SQL statement to edit
				$actions = array(
					'gen_sql_dump_changes' => array(
						'content' => $lang['strbrowsechanges'],
						'icon' => 'Eye',
						'attr' => array (
							'href' => array (
								'url' => 'groupstat.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'gen_sql_dump_changes',
									'group' => field('stat_group'),
									'schema' => field('stat_schema'),
									'table' => field('stat_table'),
									'startMark' => field('stat_first_mark'),
									'startTs' => field('stat_first_mark_datetime'),
									'endMark' => field('stat_last_mark'),
									'endTs' => field('stat_last_mark_datetime'),
									'verb' => '',
									'role' => '',
									'knownRoles' => ''
						)))),
					),
				);
			} else {
				// Direct call to the sql editor
				$actions = array(
					'gen_sql_dump_changes' => array(
						'content' => $lang['strbrowsechanges'],
						'icon' => 'Eye',
						'attr' => array (
							'href' => array (
								'url' => 'groupstat.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'call_sqledit',
									'sqlquery' => field('sql_text')
						)))),
					),
				);
			}

			$misc->printTable($stats, $columns, $actions, 'logStats', null, null, array('sorter' => true, 'filter' => true));

			// Dynamicaly change the behaviour of the SQL link using JQuery code: open a new window.
			// The link may be either a text button with a SQL content (td of type textbutton) or an icon (td of type iconbutton)
			$sql_window_id = htmlentities('emaj_sqledit:'.$_REQUEST['server']);
			echo "<script>
			$(\"#logStats\").find(\"td.textbutton a:contains('SQL'), td.iconbutton a\").click(function() {
				window.open($(this).attr('href'),'{$sql_window_id}','toolbar=no,width=700,height=550,resizable=yes,scrollbars=yes').focus();
				return false;
			});
			</script>\n";
		}
	}

	/**
	 * This function is called by the changes_stat_group() function.
	 * It generates the page section corresponding to the sequences estimates statistics output
	 */
	function disp_sequences_estimates_stat_section($groupStat) {
		global $misc, $lang, $emajdb;

		// Get statistics from E-Maj
		if ($_REQUEST['rangeend'] == 'currentsituation')
			$stats = $emajdb->getSeqStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],'');
		else
			$stats = $emajdb->getSeqStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],$_REQUEST['rangeend']);

		$summary = $emajdb->getSeqStatSummary();

		// Title
		if ($_REQUEST['rangeend'] == 'currentsituation')
			$misc->printSubtitle(sprintf($lang['strchangesseqsince'], htmlspecialchars($_REQUEST['rangestart'])));
		else
			$misc->printSubtitle(sprintf($lang['strchangesseqbetween'], htmlspecialchars($_REQUEST['rangestart']), htmlspecialchars($_REQUEST['rangeend'])));

		// Display summary statistics
		echo "<table class=\"data\"><tr>\n";
		echo "<th class=\"data\"></th>";
		echo "<th class=\"data\">{$lang['strseqingroup']}</th>";
		echo "<th class=\"data\">{$lang['strseqwithchanges']}</th>";
		echo "</tr><tr class=\"data1\">\n";
		echo "<th class=\"data\" style=\"font-size: larger;\">{$lang['strestimates']}</td>\n";
		echo "<td class=\"center\">{$groupStat->fields['nb_seq_in_group']}</td>";
		echo "<td class=\"center\">{$summary->fields['nb_sequences']}</td>";
		echo "</tr></table>\n";

		echo "<hr/>\n";

		if ($summary->fields['nb_sequences'] > 0) {

			// Display per sequence statistics
			$urlvars = $misc->getRequestVars();

			$columns = array(
				'schema' => array(
					'title' => $lang['strschema'],
					'field' => field('stat_schema'),
					'url'   => "schemas.php?action=list_schemas&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema'),
				),
				'sequence' => array(
					'title' => $lang['strsequence'],
					'field' => field('stat_sequence'),
					'url'	=> "redirect.php?subject=sequence&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema', 'sequence' => 'stat_sequence'),
				),
				'start_mark' => array(
					'upper_title' => $lang['strbegin'],
					'upper_title_colspan' => 2,
					'title' => $lang['strmark'],
					'field' => field('stat_first_mark'),
				),
				'start_datetime' => array(
					'title' => $lang['strdatetime'],
					'field' => field('stat_first_mark_datetime'),
					'type' => 'spanned',
					'params'=> array(
						'dateformat' => $lang['strrecenttimestampformat'],
						'locale' => $lang['applocale'],
						'class' => 'tooltip left-aligned-tooltip',
						),
				),
				'end_mark' => array(
					'upper_title' => $lang['strend'],
					'upper_title_colspan' => 2,
					'title' => $lang['strmark'],
					'field' => field('stat_last_mark'),
				),
				'end_datetime' => array(
					'title' => $lang['strdatetime'],
					'field' => field('stat_last_mark_datetime'),
					'type' => 'spanned',
					'params'=> array(
						'dateformat' => $lang['strrecenttimestampformat'],
						'locale' => $lang['applocale'],
						'class' => 'tooltip left-aligned-tooltip',
						),
				),
				'nbincrement' => array(
					'title' => $lang['strstatincrements'],
					'field' => field('stat_increments'),
					'type'  => 'numeric'
				),
				'hasstructurechanged' => array(
					'title' => $lang['strstatstructurechanged'],
					'field' => field('stat_has_structure_changed'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderBooleanIcon','align' => 'center')
				),
			);
			$actions = '';

			$misc->printTable($stats, $columns, $action, 'seqStats', null, null, array('sorter' => true, 'filter' => true));
		}
	}

	/**
	 * This function is called by the changes_stat_group() function.
	 * It generates the page section corresponding to the detailed tables statistics output
	 */
	function disp_tables_details_stat_section($groupStat) {
		global $misc, $lang, $emajdb;

		if (!ini_get('safe_mode')) set_time_limit(0);		// Prevent timeouts on large stats (non-safe mode only)

		// Get statistics from E-Maj
		if ($_REQUEST['rangeend']=='currentsituation')
			$stats = $emajdb->getDetailedLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],'');
		else
			$stats = $emajdb->getDetailedLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],$_REQUEST['rangeend']);

		$summary = $emajdb->getDetailedLogStatSummary();

		$roles = $emajdb->getDetailedLogStatRoles();
		$rolesList = implode(', ', $roles);

		if ($emajdb->getNumEmajVersion() >= 40400) {			// version >= 4.4.0
			$nbLogSession = $emajdb->getNbLogSessionInPeriod($_REQUEST['group'], $_REQUEST['rangestart'], $_REQUEST['rangeend']);
		} else {
			$nbLogSession = 0;
		}

		// Title
		if ($_REQUEST['rangeend'] == 'currentsituation')
			$misc->printSubtitle(sprintf($lang['strchangestblsince'], htmlspecialchars($_REQUEST['rangestart'])));
		else
			$misc->printSubtitle(sprintf($lang['strchangestblbetween'], htmlspecialchars($_REQUEST['rangestart']), htmlspecialchars($_REQUEST['rangeend'])));

		// Display summary statistics
		echo "<table class=\"data\"><tr>\n";
		echo "<th class=\"data\">{$lang['strtblingroup']}</th>";
		echo "<th class=\"data\">{$lang['strtblwithchanges']}</th>";
		echo "<th class=\"data\">{$lang['strchanges']}</th>";
		echo "<th class=\"data\">{$lang['strnbinsert']}</th>";
		echo "<th class=\"data\">{$lang['strnbupdate']}</th>";
		echo "<th class=\"data\">{$lang['strnbdelete']}</th>";
		echo "<th class=\"data\">{$lang['strnbtruncate']}</th>";
		echo "<th class=\"data\">{$lang['strnbrole']}</th>";
		echo "<th class=\"data\">{$lang['strroles']}</th>";
		echo "</tr><tr class=\"data1\">\n";
		echo "<td class=\"center\">{$groupStat->fields['nb_tbl_in_group']}</td>";
		echo "<td class=\"center\">{$summary->fields['nb_tables']}</td>";
		echo "<td class=\"center\">{$summary->fields['sum_rows']}</td>";
		echo "<td class=\"center\">{$summary->fields['nb_ins']}</td>";
		echo "<td class=\"center\">{$summary->fields['nb_upd']}</td>";
		echo "<td class=\"center\">{$summary->fields['nb_del']}</td>";
		echo "<td class=\"center\">{$summary->fields['nb_tru']}</td>";
		echo "<td class=\"center\">{$summary->fields['nb_roles']}</td>";
		echo "<td class=\"center\">{$rolesList}</td>";
		echo "</tr></table>\n";
		if ($nbLogSession > 1) {
			echo "<p><img src=\"{$misc->icon('Warning')}\" alt=\"warning\" style=\"vertical-align:middle; height:22px;\"/> {$lang['strlogsessionwarning']}</p>";
		}
		echo "<hr/>\n";

		if ($summary->fields['nb_tables'] > 0) {

			// Display per table statistics
			$urlvars = $misc->getRequestVars();

			$columns = array(
				'schema' => array(
					'title' => $lang['strschema'],
					'field' => field('stat_schema'),
					'url'   => "schemas.php?action=list_schemas&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema'),
				),
				'table' => array(
					'title' => $lang['strtable'],
					'field' => field('stat_table'),
					'url'	=> "redirect.php?subject=table&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema', 'table' => 'stat_table'),
				));
			if ($emajdb->getNumEmajVersion() >= 20300) {			// version >= 2.3.0
				$columns = array_merge($columns, array(
					'start_mark' => array(
						'upper_title' => $lang['strbegin'],
						'upper_title_colspan' => 2,
						'title' => $lang['strmark'],
						'field' => field('stat_first_mark'),
					),
					'start_datetime' => array(
						'title' => $lang['strdatetime'],
						'field' => field('stat_first_mark_datetime'),
						'type' => 'spanned',
						'params'=> array(
							'dateformat' => $lang['strrecenttimestampformat'],
							'locale' => $lang['applocale'],
							'class' => 'tooltip left-aligned-tooltip',
							),
					),
					'end_mark' => array(
						'upper_title' => $lang['strend'],
						'upper_title_colspan' => 2,
						'title' => $lang['strmark'],
						'field' => field('stat_last_mark'),
					),
					'end_datetime' => array(
						'title' => $lang['strdatetime'],
						'field' => field('stat_last_mark_datetime'),
						'type' => 'spanned',
						'params'=> array(
							'dateformat' => $lang['strrecenttimestampformat'],
							'locale' => $lang['applocale'],
							'class' => 'tooltip left-aligned-tooltip',
							),
					),
				));
			}
			$columns = array_merge($columns, array(
				'role' => array(
					'title' => $lang['strrole'],
					'field' => field('stat_role'),
				),
				'statement' => array(
					'title' => $lang['strstatverb'],
					'field' => field('stat_verb'),
				),
				'nbrow' => array(
					'title' => $lang['strstatrows'],
					'field' => field('stat_rows'),
					'type'  => 'numeric'
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
			));

			if ($emajdb->getNumEmajVersion() >= 40300) {			// version >= 4.3.0
				// Request parameters to prepare the SQL statement to edit
				$actions = array(
					'gen_sql_dump_changes' => array(
						'content' => $lang['strbrowsechanges'],
						'icon' => 'Eye',
						'attr' => array (
							'href' => array (
								'url' => 'groupstat.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'gen_sql_dump_changes',
									'subject' => 'table',
									'group' => field('stat_group'),
									'schema' => field('stat_schema'),
									'table' => field('stat_table'),
									'startMark' => field('stat_first_mark'),
									'startTs' => field('stat_first_mark_datetime'),
									'endMark' => field('stat_last_mark'),
									'endTs' => field('stat_last_mark_datetime'),
									'verb' => field('stat_verb'),
									'role' => field('stat_role'),
									'knownRoles' => $rolesList
						)))),
					),
				);
			} else {
				// Direct call to the sql editor
				$actions = array(
					'gen_sql_dump_changes' => array(
						'content' => $lang['strbrowsechanges'],
						'icon' => 'Eye',
						'attr' => array (
							'href' => array (
								'url' => 'groupstat.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'call_sqledit',
									'subject' => 'table',
									'sqlquery' => field('sql_text'),
									'paginate' => 'true',
						)))),
					),
				);
			}

			$misc->printTable($stats, $columns, $actions, 'detailedLogStats', null, null, array('sorter' => true, 'filter' => true));

			// Dynamicaly change the behaviour of the SQL link using JQuery code: open a new window
			$sql_window_id = htmlentities('emaj_sqledit:'.$_REQUEST['server']);
			echo "<script>
			$(\"#detailedLogStats\").find(\"td.textbutton a:contains('SQL'), td.iconbutton a\").click(function() {
				window.open($(this).attr('href'),'{$sql_window_id}','toolbar=no,width=700,height=550,resizable=yes,scrollbars=yes').focus();
				return false;
			});
			</script>\n";
		}
	}

	/**
	 * Call the sqleditor.php page passing the sqlquery to display in $_SESSION
	 * We are already in the target frame
	 */
	function call_sqledit() {
		global $misc;

		$_SESSION['sqlquery'] = $_REQUEST['sqlquery'];
		echo "<meta http-equiv=\"refresh\" content=\"0;url=sqledit.php?subject=table&amp;{$misc->href}&amp;action=sql&amp;paginate=true\">";
	}

	/**
	 * Call the sqleditor.php page passing the sqlquery to display in $_SESSION
	 * We are already in the target frame
	 */
	function gen_sql_dump_changes() {
		global $misc;

		echo "<meta http-equiv=\"refresh\" content=\"0;url=sqledit.php?subject=table&amp;{$misc->href}&amp;action=gen_sql_dump_changes" .
			 "&amp;group=" . urlencode($_REQUEST['group']) .
			 "&amp;schema=" . urlencode($_REQUEST['schema']) . "&amp;table=" . urlencode($_REQUEST['table']) .
			 "&amp;startMark=" . urlencode($_REQUEST['startMark']) . "&amp;startTs=" . urlencode($_REQUEST['startTs']) . 
			 "&amp;endMark=" . urlencode($_REQUEST['endMark']) . "&amp;endTs=" . urlencode($_REQUEST['endTs']) .
			 "&amp;verb=" . urlencode($_REQUEST['verb']) . "&amp;role=" . urlencode($_REQUEST['role']) .
			 "&amp;knownRoles=" . urlencode($_REQUEST['knownRoles']) . "\">";
	}

/********************************************************************************************************
 * Main piece of code
 *******************************************************************************************************/

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	$misc->printHtmlHeader($lang['strgroupsmanagement']);
	$misc->printBody();

	switch ($action) {
		case 'call_sqledit':
			call_sqledit();
			break;
		case 'changes_stat_group':
			changes_stat_group();
			break;
		case 'gen_sql_dump_changes':
			gen_sql_dump_changes();
			break;
		default:
			changes_stat_group();
	}

	$misc->printFooter();
?>
