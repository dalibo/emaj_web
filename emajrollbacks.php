<?php

	/*
	 * Manage E-Maj rollbacks monitoring and consolidation
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	// Callback function to dynamicaly add an icon to each rollback execution report
	function renderRlbkExecSeverity($val) {
		global $misc;
		if ($val == 'Notice') {
			$icon = 'CheckConstraint';
		} else {
			$icon = 'EmajWarning';
		}
		return "<img src=\"".$misc->icon($icon)."\" />";
	}

	// Callback function to dynamicaly replace the rollback status by a colored dot
	function renderRlbkStatusInList($val) {
		global $misc;
		if ($val == 'COMMITTED') {
			$ret = "<div title=\"COMMITED\"><span class=\"dot greenbg\"></span></div>";
		} elseif ($val == 'ABORTED') {
			$ret = "<div title=\"ABORTED\"><span class=\"dot redbg\"></span></div>";
		} else {
			$ret = '<div title=\"COMPLETED\">?</div>';
		}
		return $ret;
	}

	// Callback function to dynamicaly add a colored dot before rollback status
	function renderRlbkStatusInDetail($val) {
		global $misc;
		if ($val == 'COMMITTED') {
			$img = "<span class=\"dot greenbg\"></span>&nbsp;";
		} elseif ($val == 'ABORTED') {
			$img = "<span class=\"dot redbg\"></span>&nbsp;";
		} else {
			$img='';
		}
		return $img . $val;
	}

	/**
	 * Display the status of past and in progress rollback operations
	 */
	function show_rollbacks() {
		global $lang, $misc, $emajdb;

		$misc->printHeader('database', 'database', 'emajrollbacks');

		$columnsInProgressRlbk = array(
			'rlbkId' => array(
				'title' => $lang['emajrlbkid'],
				'field' => field('rlbk_id'),
				'params'=> array('align' => 'right'),
				'url'   => "emajrollbacks.php?action=show_rollback&amp;{$misc->href}&amp;",
				'vars'  => array('rlbkid' => 'rlbk_id'),
			),
			'rlbkGroups' => array(
				'title' => $lang['emajgroups'],
				'field' => field('rlbk_groups_list'),
			),
			'rlbkStatus' => array(
				'title' => $lang['emajstate'],
				'field' => field('rlbk_status'),
			),
			'rlbkStartDateTime' => array(
				'title' => $lang['emajrlbkstart'],
				'field' => field('rlbk_start_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkElapse' => array(
				'title' => $lang['emajcurrentduration'],
				'field' => field('rlbk_current_elapse'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkRemaining' => array(
				'title' => $lang['emajestimremaining'],
				'field' => field('rlbk_remaining'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkCompletionPct' => array(
				'title' => $lang['emajpctcompleted'],
				'field' => field('rlbk_completion_pct'),
				'params'=> array('align' => 'right'),
			),
			'rlbkMark' => array(
				'title' => $lang['emajtargetmark'],
				'field' => field('rlbk_mark'),
			),
			'isLogged' => array(
				'title' => $lang['emajislogged'],
				'field' => field('rlbk_is_logged'),
				'type'	=> 'yesno',
			),
			'rlbkNbSession' => array(
				'title' => $lang['emajnbsession'],
				'field' => field('rlbk_nb_session'),
				'params'=> array('align' => 'right'),
			),
		);
		if ($emajdb->getNumEmajVersion() >= 40300) {	// version >= 4.3
			$columnsInProgressRlbk = array_merge($columnsInProgressRlbk, array(
				'comment' => array(
					'title' => $lang['strcomment'],
					'field' => field('rlbk_comment'),
					'type' => 'spanned',
					'params'=> array(
							'cliplen' => 12,
							'class' => 'tooltip right-aligned-tooltip',
							),
				),
			));
		};

		$columnsCompletedRlbk = array(
			'rlbkId' => array(
				'title' => $lang['emajrlbkid'],
				'field' => field('rlbk_id'),
				'params'=> array('align' => 'right'),
				'url'   => "emajrollbacks.php?action=show_rollback&amp;{$misc->href}&amp;",
				'vars'  => array('rlbkid' => 'rlbk_id'),
			),
			'rlbkGroups' => array(
				'title' => $lang['emajgroups'],
				'field' => field('rlbk_groups_list'),
			),
			'rlbkStatus' => array(
				'title' => $lang['emajstate'],
				'field' => field('rlbk_status'),
				'type'	=> 'callback',
				'params'=> array(
					'function' => 'renderRlbkStatusInList',
					'align' => 'center',
				),
				'sorter' => false,
				'filter' => false,
			),
			'rlbkStartDateTime' => array(
				'title' => $lang['emajrlbkstart'],
				'field' => field('rlbk_start_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
				'sorter_text_extraction' => 'span_text',
			),
			'rlbkEndDateTime' => array(
				'title' => $lang['emajrlbkend'],
				'field' => field('rlbk_end_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
				'sorter_text_extraction' => 'span_text',
			),
			'rlbkDuration' => array(
				'title' => $lang['emajduration'],
				'field' => field('rlbk_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
				'sorter_text_extraction' => 'span_text',
				'filter' => false,
			),
			'rlbkMark' => array(
				'title' => $lang['emajtargetmark'],
				'field' => field('rlbk_mark'),
			),
			'isLogged' => array(
				'title' => $lang['emajislogged'],
				'field' => field('rlbk_is_logged'),
				'type'	=> 'yesno',
			),
			'rlbkNbSession' => array(
				'title' => $lang['emajnbsession'],
				'field' => field('rlbk_nb_session'),
				'params'=> array('align' => 'right'),
			),
		);
		if ($emajdb->getNumEmajVersion() >= 40300) {	// version >= 4.3
			$columnsCompletedRlbk = array_merge($columnsCompletedRlbk, array(
				'comment' => array(
					'title' => $lang['strcomment'],
					'field' => field('rlbk_comment'),
					'type' => 'spanned',
					'params'=> array(
							'cliplen' => 12,
							'class' => 'tooltip right-aligned-tooltip',
							),
				),
			));
		};
		$actions = array();

		// Get rollback information from the database
		$completedRlbks = $emajdb->getCompletedRlbk();
		$misc->printTitle($lang['emajinprogressrlbk']);
		if ($emajdb->isDblinkUsable()) {
			$inProgressRlbks = $emajdb->getInProgressRlbk();
			$misc->printTable($inProgressRlbks, $columnsInProgressRlbk, $actions, 'inProgressRlbk', $lang['emajnorlbk']);
		} else {
			echo "<p>{$lang['emajrlbkmonitornotavailable']}</p>\n";
		}
		echo "<hr/>\n";

		$misc->printTitle($lang['emajcompletedrlbk']);

		$misc->printTable($completedRlbks, $columnsCompletedRlbk, $actions, 'completedRlbk', $lang['emajnorlbk'], null, array('sorter' => true, 'filter' => true));

		echo "<hr/>\n";

		// Display the E-Maj logged rollback operations that may be consolidated (i.e. transformed into unlogged rollback)
		$columnsConsRlbk = array(
			'consGroup' => array(
				'title' => $lang['emajgroup'],
				'field' => field('cons_group'),
			),
			'consTargetMark' => array(
				'title' => $lang['emajtargetmark'],
				'field' => field('cons_target_rlbk_mark_name'),
			),
			'consTargetMarkDateTime' => array(
				'title' => $lang['emajmarksetat'],
				'field' => field('cons_target_rlbk_mark_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkNbRow' => array(
				'title' => $lang['emajnbchanges'],
				'field' => field('cons_rows'),
				'params'=> array('align' => 'right'),
			),
			'rlbkNbMark' => array(
				'title' => $lang['emajnbintermediatemark'],
				'field' => field('cons_marks'),
				'params'=> array('align' => 'right'),
			),
			'consEndMark' => array(
				'title' => $lang['emajendrollbackmark'],
				'field' => field('cons_end_rlbk_mark_name'),
			),
			'consEndMarkDateTime' => array(
				'title' => $lang['emajmarksetat'],
				'field' => field('cons_end_rlbk_mark_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'actions' => array(
				'title' => $lang['stractions'],
			),
		);
		if ($emajdb->isEmaj_Adm()) {
			$actions = array(
				'consolidate' => array(
					'content' => $lang['emajconsolidate'],
					'icon' => 'Consolidate',
					'attr' => array (
						'href' => array (
							'url' => 'emajrollbacks.php',
							'urlvars' => array (
								'action' => 'consolidate_rollback',
								'group' => field('cons_group'),
								'mark' => field('cons_end_rlbk_mark_name'),
							)))
				),
			);
		} else {
			$actions = array();
		}
		// Get rollback information from the database
		$consolidableRlbks = $emajdb->getConsolidableRlbk();

		$misc->printTitle($lang['emajconsolidablerlbk']);
		$inProgressRlbks = $emajdb->getInProgressRlbk();
		$misc->printTable($consolidableRlbks, $columnsConsRlbk, $actions, 'consolidableRlbk', $lang['emajnorlbk']);
	}

	/**
	 * Display the details of a rollback operation
	 */
	function show_rollback() {
		global $lang, $misc, $emajdb;

		if (!isset($_SESSION['emaj']['RlbkShowEstimates'])) {
			$_SESSION['emaj']['RlbkShowEstimates'] = true;
		}

		$misc->printHeader('emajrollback', 'database', 'emajrollbacks');

		if (isset($_REQUEST['asyncRlbk']))
			// An asynchronous rollback has just been spawned, report the rollback id
			$misc->printMsg(sprintf($lang['emajasyncrlbkstarted'], $_REQUEST['rlbkid']));

		$misc->printTitle(sprintf($lang['emajrlbkdetail'], htmlspecialchars($_REQUEST['rlbkid'])));

		$rlbkInfo = $emajdb->getOneRlbk($_REQUEST['rlbkid']);

		// Save some piece of informations
		$status = $rlbkInfo->fields['rlbk_status'];
		$isCompleted = ($status == 'COMPLETED' || $status == 'COMMITTED' || $status == 'ABORTED');

		if (! $isCompleted) {
			$rlbkInProgressInfo = $emajdb->getOneInProgressRlbk($_REQUEST['rlbkid']);
# TODO: if the rollback status has changed between both data access, may be we should reread the emaj_rlbk table ?
		} else {
			$rlbkReportMsgs = $emajdb->getRlbkReportMsg($_REQUEST['rlbkid']);
		}

		$rlbkSessions = $emajdb->getRlbkSessions($_REQUEST['rlbkid']);

		$rlbkSteps = $emajdb->getRlbkSteps($_REQUEST['rlbkid']);

		$columnsIdent = array(
			'rlbkGroups' => array(
				'title' => $lang['emajgroups'],
				'field' => field('rlbk_groups_list'),
			),
			'rlbkMark' => array(
				'title' => $lang['emajtargetmark'],
				'field' => field('rlbk_mark'),
			),
			'rlbkMarkDateTime' => array(
				'title' => $lang['emajmarksetat'],
				'field' => field('rlbk_mark_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
		);

		$columnsCompleted = array(
			'rlbkStatus' => array(
				'title' => $lang['emajstate'],
				'field' => field('rlbk_status'),
				'type'	=> 'callback',
				'params'=> array(
					'function' => 'renderRlbkStatusInDetail',
					'align' => 'center',
				),
			),
			'rlbkStartDateTime' => array(
				'title' => $lang['emajrlbkstart'],
				'field' => field('rlbk_start_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkEndDateTime' => array(
				'title' => $lang['emajrlbkend'],
				'field' => field('rlbk_end_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkGlobalDuration' => array(
				'title' => $lang['emajglobalduration'],
				'field' => field('rlbk_global_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkPlanningDuration' => array(
				'title' => $lang['emajplanningduration'],
				'field' => field('rlbk_planning_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkLockingDuration' => array(
				'title' => $lang['emajlockingduration'],
				'field' => field('rlbk_locking_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
		);

		$columnsInProgress = array(
			'rlbkStatus' => array(
				'title' => $lang['emajstate'],
				'field' => field('rlbk_status'),
			),
			'rlbkStartDateTime' => array(
				'title' => $lang['emajrlbkstart'],
				'field' => field('rlbk_start_datetime'),
				'params'=> array('align' => 'center'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'class' => 'tooltip',
				),
			),
			'rlbkElapse' => array(
				'title' => $lang['emajcurrentduration'],
				'field' => field('rlbk_current_elapse'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkPlanningDuration' => array(
				'title' => $lang['emajplanningduration'],
				'field' => field('rlbk_planning_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkLockingDuration' => array(
				'title' => $lang['emajlockingduration'],
				'field' => field('rlbk_locking_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkRemaining' => array(
				'title' => $lang['emajestimremaining'],
				'field' => field('rlbk_remaining'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkCompletionPct' => array(
				'title' => $lang['emajpctcompleted'],
				'field' => field('rlbk_completion_pct'),
				'params'=> array('align' => 'center'),
			),
		);

		$columnsCharacteristics = array(
			'isLogged' => array(
				'title' => $lang['emajislogged'],
				'field' => field('rlbk_is_logged'),
				'type'	=> 'yesno',
			),
			'rlbkNbSession' => array(
				'title' => $lang['emajnbsession'],
				'field' => field('rlbk_nb_session'),
				'params'=> array('align' => 'center'),
			),
			'rlbkNbTable' => array(
				'title' => $lang['emajnbtabletoprocess'],
				'field' => field('rlbk_tbl'),
				'params'=> array('align' => 'center'),
			),
			'rlbkNbSeq' => array(
				'title' => $lang['emajnbseqtoprocess'],
				'field' => field('rlbk_seq'),
				'params'=> array('align' => 'center'),
			),
		);
		if ($emajdb->getNumEmajVersion() >= 40300) {	// version >= 4.3
			$columnsCharacteristics = array_merge($columnsCharacteristics, array(
				'comment' => array(
					'title' => $lang['strcomment'],
					'field' => field('rlbk_comment'),
				),
			));
		};

		$columnsReport = array(
			'severity' => array(
				'title' => '',
				'field' => field('rlbk_severity'),
				'type'	=> 'callback',
				'params'=> array('function' => 'renderRlbkExecSeverity','align' => 'center'),
				'sorter' => false,
			),
			'msg' => array(
				'title' => $lang['strmessage'],
				'field' => field('rlbk_message'),
			),
		);

		$columnsSessions = array(
			'session' => array(
				'title' => $lang['emajrlbksession'],
				'field' => field('rlbs_session'),
				'params'=> array('align' => 'center'),
			),
			'sessionStartDateTime' => array(
				'title' => $lang['strbegin'],
				'field' => field('rlbs_start_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'sessionEndDateTime' => array(
				'title' => $lang['strend'],
				'field' => field('rlbs_end_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'sessionDuration' => array(
				'title' => $lang['emajduration'],
				'field' => field('rlbs_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'sessionTxId' => array(
				'title' => $lang['emajtxid'],
				'field' => field('rlbs_txid'),
				'params'=> array('align' => 'right'),
			),
		);

		$columnsSteps = array(
			'rank' => array(
				'title' => '#',
				'field' => field('rlbp_rank'),
				'params'=> array('align' => 'right'),
			),
			'schema_table' => array(
				'title' => $lang['strtable'],
				'field' => field('rlbk_schema_table'),
			),
			'step' => array(
				'title' => $lang['emajrlbkstep'],
				'field' => field('rlbp_action'),
			),
			'session' => array(
				'title' => $lang['emajrlbksession'],
				'field' => field('rlbp_session'),
				'params'=> array('align' => 'center'),
			),
			'startDateTime' => array(
				'title' => $lang['strbegin'],
				'field' => field('rlbp_start_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strprecisetimeformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'duration' => array(
				'title' => $lang['emajduration'],
				'field' => field('rlbp_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'quantity' => array(
				'title' => $lang['strquantity'],
				'field' => field('rlbp_quantity'),
				'params'=> array('align' => 'right'),
			),
		);

		if ($_SESSION['emaj']['RlbkShowEstimates']) {
			$columnsSteps = array_merge($columnsSteps, array(
				'estimatedDuration' => array(
					'title' => $lang['emajestimatedduration'],
					'field' => field('rlbp_estimated_duration'),
					'type' => 'spanned',
					'params'=> array(
						'intervalformat' => $lang['strintervalformat'],
						'class' => 'tooltip left-aligned-tooltip rlbkEstimates',
					),
				),
				'estimatedQuantity' => array(
					'title' => $lang['emajestimatedquantity'],
					'field' => field('rlbp_estimated_quantity'),
					'params'=> array('class' => 'rlbkEstimates', 'align' => 'right'),
				),
				'estimateMethod' => array(
					'title' => $lang['emajestimationmethod'],
					'field' => field('rlbp_estimate_method'),
					'params'=> array('class' => 'rlbkEstimates', 'align' => 'center'),
				),
			));
		}

		$urlvars = $misc->getRequestVars();

		$actions = array();

		// print rollback identification data
		echo "<h4>{$lang['emajrlbkident']}</h4>";
		$misc->printTable($rlbkInfo, $columnsIdent, $actions, 'detailRlbkIdent', 'No rollback, internal error !');

		// print rollback progress data
		$rlbkInfo->moveFirst();
		if ($isCompleted) {
			// The rollback is completed
			echo "<h4>{$lang['emajrlbkprogress']}</h4>\n";
			$misc->printTable($rlbkInfo, $columnsCompleted, $actions, 'detailRlbkProgress', 'No rollback, internal error !');

		} else {
			// The rollback is in progress
			// Insert a refresh button, before the data table
			echo "<h4>{$lang['emajrlbkprogress']}\n";
			echo "\t<a href=\"emajrollbacks.php?action=show_rollback&amp;{$misc->href}&amp;rlbkid=" . htmlspecialchars($_REQUEST['rlbkid']) . "\">\n";
			echo "\t\t<img src=\"{$misc->icon('Refresh')}\" alt=\"{$lang['strrefresh']}\" title=\"{$lang['strrefresh']}\" />";
			echo "\t</a>\n</h4>\n";

			$misc->printTable($rlbkInProgressInfo, $columnsInProgress, $actions, 'detailRlbkProgress', 'No rollback, internal error !');
		}

		// final execution report of the rollback operation
		if ($isCompleted) {
			echo "<h4>{$lang['emajrlbkexecreport']}</h4>";
			echo "<div id=\"rlbkReport\" style=\"margin-top:15px;margin-bottom:15px\" >\n";
			$misc->printTable($rlbkReportMsgs, $columnsReport, $actions, 'rlbkReport', null, null, array('sorter' => true, 'filter' => false));
			echo "</div>\n";
		}

		// print rollback characteristics data
		$rlbkInfo->moveFirst();
		echo "<h4>{$lang['emajrlbkcharacteristics']}</h4>";
		$misc->printTable($rlbkInfo, $columnsCharacteristics, $actions, 'detailRlbkChar', null);

		// print sessions data
		if ($rlbkSessions->recordCount() > 0) {
			echo "<h4>{$lang['emajrlbksessions']}</h4>";
			$misc->printTable($rlbkSessions, $columnsSessions, $actions, 'detailRlbkSession', null);
		}

		// print planning data
		if ($rlbkSteps->recordCount() > 0) {
			echo "<h4 style=\"float:left; margin-right:430px\">{$lang['emajrlbkplanning']}\n";
			echo "&nbsp;&nbsp;<img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajrlbkplanninghelp']}\"/>\n";
			echo "</h4>\n";

			// Button to hide or show estimates columns
			echo "<div style=\"margin:13px\">\n";
			echo "\t<form id=\"showHideEstimates\" action=\"emajrollbacks.php?action=toggle_estimates&amp;{$misc->href}\"";
			echo " method=\"post\" enctype=\"multipart/form-data\">\n";
			if ($_SESSION['emaj']['RlbkShowEstimates'])
				$buttonText = $lang['emajhideestimates'];
			else
				$buttonText = $lang['emajshowestimates'];
			echo "<input type=\"hidden\" name=\"rlbkid\" value=\"{$_REQUEST['rlbkid']}\" />\n";
			echo "\t\t<input type=\"submit\" name=\"showHideEstimates\" value=\"{$buttonText}\">\n";
			echo "&nbsp;&nbsp;<img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajrlbkestimmethodhelp']}\"/>\n";
			echo "\t</form>\n";
			echo "</div>\n";

			$misc->printTable($rlbkSteps, $columnsSteps, $actions, 'detailRlbkSteps', null, null, array('sorter' => true, 'filter' => true));
		} else {
			echo "<h4>{$lang['emajrlbkplanning']}</h4>\n";
			echo "<p>{$lang['emajnorlbkstep']}</p>\n";
		}
	}

	/**
	 * Prepare a rollback consolidation: ask for confirmation
	 */
	function consolidate_rollback() {
		global $misc, $lang;

		$misc->printHeader('database', 'database', 'emajrollbacks');

		$misc->printTitle($lang['emajconsolidaterlbk']);

		echo "<p>", sprintf($lang['emajconfirmconsolidaterlbk'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajrollbacks.php?\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"consolidate_rollback_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"consolidaterlbk\" value=\"{$lang['emajconsolidate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform a rollback consolidation
	 */
	function consolidate_rollback_ok() {
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_rollbacks();
			exit();
		}

		$status = $emajdb->consolidateRollback($_POST['group'],$_POST['mark']);
		if ($status == 0)
			show_rollbacks(sprintf($lang['emajconsolidaterlbkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
		else
			show_rollbacks('',sprintf($lang['emajconsolidaterlbkerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
	}

	/**
	 * Toggle the show_estimates and hide_estimates switch
	 */
	function toggle_estimates() {

		$_SESSION['emaj']['RlbkShowEstimates'] = (! $_SESSION['emaj']['RlbkShowEstimates']);
		show_rollback();
	}

	// redirect to the emajenvir.php page if the emaj extension is not installed or accessible or is too old
	if (!(isset($emajdb) && $emajdb->isEnabled() && $emajdb->isAccessible()
		&& $emajdb->getNumEmajVersion() >= $oldest_supported_emaj_version_num)) {
		header('Location: emajenvir.php?' . $_SERVER["QUERY_STRING"]);
	}

	$misc->printHtmlHeader($lang['emajrollbacksmanagement']);
	$misc->printBody();

	switch ($action) {
		case 'consolidate_rollback':
			consolidate_rollback();
			break;
		case 'consolidate_rollback_ok':
			consolidate_rollback_ok();
			break;
		case 'show_rollbacks':
			show_rollbacks();
			break;
		case 'show_rollback':
			show_rollback();
			break;
		case 'toggle_estimates':
			toggle_estimates();
			break;
		default:
			show_rollbacks();
	}

	$misc->printFooter();

?>
