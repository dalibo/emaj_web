<?php
	/*
	 * Manage the single table recorded changes statistics
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	// Global variable to handle the icon color for the graphic column.
	$graphicColor = 'Green';

/********************************************************************************************************
 * Callback functions
 *******************************************************************************************************/

	// Function to dynamicaly modify actions list for each stat
	function statPre(&$rowdata, $actions) {

		// disable the show_changes action if there is no recorded changes
		if ($rowdata->fields['stat_changes'] == 0) {
			$actions['gen_sql_dump_changes']['disable'] = true;
		}
		return $actions;
	}

	// Callback function to transform the graphic column into icon.
	function renderGraphic($val) {
		global $misc, $graphicColor, $lang;

		if ($graphicColor == 'Green' && ($val == 'End' || $val == 'BeginEnd'))
			$graphicColor = 'Grey';
		$icon = $misc->icon($graphicColor . $val);
		$title = $lang['strlogsession' . $val];
		$div = "<div><img src=\"$icon\" alt=\"$val\" title=\"$title\"class=\"fullsizecellicon\" /></div>";

		return $div;
	}

/********************************************************************************************************
 * Main functions displaying pages
 *******************************************************************************************************/

	/**
	 * Show changes statistics between 2 marks for a single table
	 */
	function doDefault() {
		global $misc, $lang, $emajdb, $_reload_browser;

		$misc->printHeader('table', 'table', 'changesstat');

		$misc->printTitle(sprintf($lang['strchangestable'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($_REQUEST['table'])));

		$type = $emajdb->getEmajTypeTblSeq($_REQUEST['schema'], $_REQUEST['table']);

		if ($type == 'L') {
			echo "<p>{$lang['stremajlogtable']}</p>\n";
		} elseif ($type == 'E') {
			echo "<p>{$lang['stremajinternaltable']}</p>\n";
		} elseif ($type == 'U') {
			echo "<p>{$lang['strnotassignabletable']}</p>\n";
		} else {

			// display the form

			$compute = (isset($_REQUEST['compute'])) ? true : false;

			// get marks points in time for the table
			$marks = $emajdb->getMarksTable($_REQUEST['schema'], $_REQUEST['table']);

			// is the table currently in a logging group ?
			$isTableInLoggingGroup = $emajdb->isTblseqInLoggingGroup($_REQUEST['schema'], $_REQUEST['table']);

			if ($marks->recordCount() < 1) {

				// No mark recorded for the table => no stat to display
				echo "<p>{$lang['strnomarktbl']}</p>\n";

			} else {

				// form for statistics selection
				echo "<form id=\"statistics_form\" action=\"tblstat.php?action=changes_stat_table&amp;{$misc->href}\"";
				echo "  method=\"post\" enctype=\"multipart/form-data\">\n";

				echo "<div class=\"form-container\">\n";
				// First mark defining the marks range to analyze
				echo "\t<div class=\"form-label\">{$lang['strstartmark']}</div>\n";
				echo "\t<div class=\"form-input\">\n";
				echo "\t\t<select name=\"rangestart\" id=\"rangestart\">\n";
				foreach($marks as $r)
					echo "\t\t\t<option value=\"", htmlspecialchars($r['mark_time_id'] . '#' . $r['mark_id']), "\" >", htmlspecialchars($r['mark_id']), " ({$r['mark_datetime']})</option>\n";
				echo "\t\t</select>\n";
				echo "\t</div>\n";
				echo "\t<div class=\"form-comment\"></div>\n";

				// Last mark defining the marks range to analyze
				echo "\t<div class=\"form-label\">{$lang['strendmark']}</div>\n";
				echo "\t<div class=\"form-input\">\n";
				echo "\t\t<select name=\"rangeend\" id=\"rangeend\" >\n";
				if ($isTableInLoggingGroup == 't')
					echo "\t\t\t<option value=\"currentsituation\">{$lang['strcurrentsituation']}</option>\n";
				foreach($marks as $r)
					echo "\t\t\t<option value=\"", htmlspecialchars($r['mark_time_id'] . '#' . $r['mark_id']), "\" >", htmlspecialchars($r['mark_id']), " ({$r['mark_datetime']})</option>\n";
				echo "\t\t</select>\n";
				echo "\t</div>\n";
				echo "\t<div class=\"form-comment\"></div>\n";
				echo "</div>\n";

				// Button
				echo "<div class=\"actionslist\">\n";
				echo "\t<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
				echo "\t<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";
				echo "\t<input type=\"submit\" name=\"compute\" value=\"{$lang['strcompute']}\" />\n";
				echo "</div>\n";
				echo "</form>\n";

				// JQuery scripts
				echo "<script>\n";

				// JQuery to remove the last mark as it cannot be selected as end mark
				echo "  $(\"#rangeend option:last-child\").remove();\n";

				// JQuery to set the selected start mark by default
				// (the previous requested start mark if stat are already displayed or the last)
				if (isset($_REQUEST['rangestart'])) {
					echo "  $(\"#rangestart option[value='{$_REQUEST['rangestart']}']\").attr(\"selected\", true);\n";
				} else {
					echo "  $(\"#rangestart option:eq(-1)\").attr(\"selected\", true);\n";
				}

				// JQuery to set the selected end mark by default
				// (the previous requested end mark if stat are already displayed or the first mark or current state depending on the group state)
				if (isset($_REQUEST['rangeend'])) {
					echo "  $(\"#rangeend option[value='{$_REQUEST['rangeend']}']\").attr(\"selected\", true);\n";
				} else {
					echo "  $(\"#rangeend option:eq(0)\").attr(\"selected\", true);\n";
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

				// If table stats are requested, display them
				if ($compute) {
					disp_table_stat_section();
				}
			}
		}
	}

	/**
	 * This function generates the page section corresponding to the table statistics output
	 */
	function disp_table_stat_section() {
		global $misc, $lang, $emajdb;

		// Split both range bounds
		$sepPos = strpos($_REQUEST['rangestart'], '#');
		$startTimeId = substr($_REQUEST['rangestart'], 0, $sepPos);
		$startMarkId = substr($_REQUEST['rangestart'], $sepPos + 1);
		if ($_REQUEST['rangeend'] == 'currentsituation') {
			$endTimeId = '';
		} else {
			$sepPos = strpos($_REQUEST['rangeend'], '#');
			$endTimeId = substr($_REQUEST['rangeend'], 0, $sepPos);
			$endMarkId = substr($_REQUEST['rangeend'], $sepPos + 1);
		}

		// Title
		if ($_REQUEST['rangeend'] == 'currentsituation') {
			$misc->printSubtitle(sprintf($lang['strrecchangessince'], htmlspecialchars($startMarkId)));
		} else {
			$misc->printSubtitle(sprintf($lang['strrecchangesbetween'], htmlspecialchars($startMarkId), htmlspecialchars($endMarkId)));
		}

		// Get statistics from E-Maj
		$stats = $emajdb->getLogStatTable($_REQUEST['schema'], $_REQUEST['table'], $startTimeId, $endTimeId);

		// Display statistics

		if ($stats->recordCount() < 1) {

			// There is no timeinterval to display
			echo "<p>{$lang['strnostatoninterval']}</p>\n";

		} else {

			echo "<p>{$lang['strdescendingintervalsorder']}</p>\n";

			$urlvars = $misc->getRequestVars();
	
			$columns = array(
				'log_session' => array(
					'title' => '',
					'info'  => $lang['strlogsessionshelp'],
					'field' => field('graphic'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderGraphic'),
					'class' => 'nopadding center',
					'filter'=> false,
				),
				'group' => array(
					'title' => $lang['strgroup'],
					'field' => field('stat_group'),
					'url'   => "groupproperties.php?action=show_group&amp;{$misc->href}&amp;",
					'vars'  => array('group' => 'stat_group'),
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
				'nbchanges' => array(
					'title' => $lang['strstatrows'],
					'field' => field('stat_changes'),
					'type'  => 'numeric'
				),
				'nbrollbacks' => array(
					'title' => $lang['strnbrollbacks'],
					'field' => field('stat_rollbacks'),
					'type'  => 'numeric'
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
			);
	
			// Request parameters to prepare the SQL statement to edit
			$actions = array(
				'gen_sql_dump_changes' => array(
					'content' => $lang['strbrowsechanges'],
					'icon' => 'Eye',
					'attr' => array (
						'href' => array (
							'url' => 'tblstat.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'gen_sql_dump_changes',
								'group' => field('stat_group'),
								'schema' => $_REQUEST['schema'],
								'table' => $_REQUEST['table'],
								'startMark' => field('stat_first_mark'),
								'startTimeId' => field('stat_first_time_id'),
								'endMark' => field('stat_last_mark'),
								'endTimeId' => field('stat_last_time_id'),
								'verb' => '',
								'role' => '',
								'knownRoles' => ''
					)))),
				),
			);
	
			$misc->printTable($stats, $columns, $actions, 'logStats', null, 'statPre', array('sorter' => false, 'filter' => true));
	
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
	 * Call the sqleditor.php page passing the sqlquery to display in $_SESSION
	 * We are already in the target frame
	 */
	function gen_sql_dump_changes() {
		global $misc;

		echo "<meta http-equiv=\"refresh\" content=\"0;url=sqledit.php?subject=table&amp;{$misc->href}&amp;action=gen_sql_dump_changes" .
			 "&amp;group=" . urlencode($_REQUEST['group']) .
			 "&amp;schema=" . urlencode($_REQUEST['schema']) . "&amp;table=" . urlencode($_REQUEST['table']) .
			 "&amp;startMark=" . urlencode($_REQUEST['startMark']) . "&amp;startTimeId=" . urlencode($_REQUEST['startTimeId']) .
			 "&amp;endMark=" . urlencode($_REQUEST['endMark']) . "&amp;endTimeId=" . urlencode($_REQUEST['endTimeId']) .
			 "&amp;verb=" . urlencode($_REQUEST['verb']) . "&amp;role=" . urlencode($_REQUEST['role']) .
			 "&amp;knownRoles=" . urlencode($_REQUEST['knownRoles']) . "\">";
	}

/********************************************************************************************************
 * Main piece of code
 *******************************************************************************************************/

	// Check that emaj and the table still exist.
	$misc->onErrorRedirect('table');

	$misc->printHtmlHeader($lang['strtables'] . ' - ' . $_REQUEST['table']);
	$misc->printBody();

	switch ($action) {
		case 'changes_stat_table':
			doDefault();
			break;
		case 'gen_sql_dump_changes':
			gen_sql_dump_changes();
			break;
		default:
			doDefault();
	}

	$misc->printFooter();
?>
