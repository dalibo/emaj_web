<?php
	/*
	 * Manage the single sequence recorded changes statistics
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	// Global variable to handle the icon color for the graphic column.
	$graphicColor = 'Green';

/********************************************************************************************************
 * Callback functions
 *******************************************************************************************************/

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
	 * Show changes statistics between 2 marks for a single sequence
	 */
	function doDefault() {
		global $misc, $lang, $emajdb, $_reload_browser;

		$misc->printHeader('sequence', 'sequence', 'changesstat');

		$misc->printTitle(sprintf($lang['strchangessequence'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($_REQUEST['sequence'])));

		$type = $emajdb->getEmajTypeTblSeq($_REQUEST['schema'], $_REQUEST['sequence']);

		if ($type == 'L') {
			echo "<p>{$lang['stremajlogsequence']}</p>\n";
		} elseif ($type == 'E') {
			echo "<p>{$lang['stremajinternalsequence']}</p>\n";
		} elseif ($type == 'U') {
			echo "<p>{$lang['strnotassignabletable']}</p>\n";
		} else {

			// display the form

			$compute = (isset($_REQUEST['compute'])) ? true : false;

			// get marks points in time for the sequence
			$marks = $emajdb->getMarksSequence($_REQUEST['schema'], $_REQUEST['sequence']);

			// is the table currently in a logging group ?
			$isSequenceInLoggingGroup = $emajdb->isTblseqInLoggingGroup($_REQUEST['schema'], $_REQUEST['sequence']);

			if ($marks->recordCount() < 1) {

				// No mark recorded for the sequence => no stat to display
				echo "<p>{$lang['strnomarkseq']}</p>\n";

			} else {

				// form for statistics selection
				echo "<form id=\"statistics_form\" action=\"seqstat.php?action=changes_stat_sequence&amp;{$misc->href}\"";
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
				if ($isSequenceInLoggingGroup == 't')
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
				echo "\t<input type=\"hidden\" name=\"sequence\" value=\"", htmlspecialchars($_REQUEST['sequence']), "\" />\n";
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

				// If sequence stats are requested, display them
				if ($compute) {
					disp_sequence_stat_section();
				}
			}
		}
	}

	/**
	 * This function generates the page section corresponding to the sequence statistics output
	 */
	function disp_sequence_stat_section() {
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
		$stats = $emajdb->getLogStatSequence($_REQUEST['schema'], $_REQUEST['sequence'], $startTimeId, $endTimeId);

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
				'nbincrement' => array(
					'title' => $lang['strstatincrements'],
					'field' => field('stat_increments'),
					'type'  => 'numeric'
				),
				'hasstructurechanged' => array(
					'title' => $lang['strstatstructurechanged'],
					'field' => field('stat_has_structure_changed'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderBooleanIcon','align' => 'center'),
					'filter'=> false,
				),
				'nbrollbacks' => array(
					'title' => $lang['strnbrollbacks'],
					'field' => field('stat_rollbacks'),
					'type'  => 'numeric'
				),
			);
	
			// Request parameters to prepare the SQL statement to edit
			$actions = array();
	
			$misc->printTable($stats, $columns, $actions, 'logStats', null, null, array('sorter' => false, 'filter' => true));
	
		}
	}

/********************************************************************************************************
 * Main piece of code
 *******************************************************************************************************/

	// Check that emaj and the sequence still exist.
	$misc->onErrorRedirect('emaj');
	$misc->onErrorRedirect('sequence');

	$misc->printHtmlHeader($lang['strsequences'] . ' - ' . $_REQUEST['sequence']);
	$misc->printBody();

	switch ($action) {
		case 'changes_stat_sequence':
			doDefault();
			break;
		default:
			doDefault();
	}

	$misc->printFooter();
?>
