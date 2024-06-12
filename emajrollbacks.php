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
			$icon = 'Checkmark';
		} else {
			$icon = 'Warning';
		}
		return "<img src=\"{$misc->icon($icon)}\" alt=\"$val\" class=\"cellicon\"/>";
	}

	// Callback function to dynamicaly replace the rollback status by a colored dot
	function renderRlbkStatusInList($val) {
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
		if ($val == 'COMMITTED') {
			$img = "<span class=\"dot greenbg\"></span>&nbsp;";
		} elseif ($val == 'ABORTED') {
			$img = "<span class=\"dot redbg\"></span>&nbsp;";
		} else {
			$img='';
		}
		return $img . $val;
	}

	// Callback function to dynamicaly transform the duration estimate quality into a colored rectangle
	// The value is composed of 2 parts: the first indicator is transformed into a color, the second is the effective duration transformed into the rectangle width.
	function renderEstimateQuality($val) {
		$indicators = explode(':', $val);
		if ($indicators[0] == 'A') return '';	// empty cell if the duration is unknown or < 10ms
		$bg['B'] = 'greenbg'; $bg['C'] = 'orangebg'; $bg['D'] = 'redbg';
		$px = ceil(log($indicators[1], 2)) * 2;
		$ret = "<div class=\"rectangle {$bg[$indicators[0]]}\" style=\"width: {$px}px;\"><span>$val</span></div>";
		return $ret;
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
					'locale' => $lang['applocale'],
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
		if ($emajdb->getNumEmajVersion() >= 40300 && $emajdb->isEmaj_Adm()) {	// version >= 4.3
			$columnsInProgressRlbk = array_merge($columnsInProgressRlbk, array(
				'actions' => array(
					'title' => $lang['stractions'],
				),
			));
		};
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
					'locale' => $lang['applocale'],
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
					'locale' => $lang['applocale'],
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
		if ($emajdb->getNumEmajVersion() >= 40300 && $emajdb->isEmaj_Adm()) {	// version >= 4.3
			$columnsCompletedRlbk = array_merge($columnsCompletedRlbk, array(
				'actions' => array(
					'title' => $lang['stractions'],
				),
			));
		};
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
		if ($emajdb->getNumEmajVersion() >= 40300 && $emajdb->isEmaj_Adm()) {	// version >= 4.3
			$actions = array_merge($actions, array(
				'comment_rollback' => array(
					'content' => $lang['emajsetcomment'],
					'icon' => 'Bubble',
					'attr' => array (
						'href' => array (
							'url' => 'emajrollbacks.php',
							'urlvars' => array (
								'action' => 'comment_rollback',
								'rlbkid' => field('rlbk_id'),
								'back' => 'list',
							)))
				))
			);
		};

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
				'title' => $lang['strgroup'],
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
					'locale' => $lang['applocale'],
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
					'locale' => $lang['applocale'],
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
		global $lang, $misc, $emajdb, $conf;

		if (!isset($_SESSION['emaj']['RlbkShowEstimates'])) {
			$_SESSION['emaj']['RlbkShowEstimates'] = true;
		}

		$misc->printHeader('emajrollback', 'database', 'emajrollbacks');

		if (isset($_REQUEST['asyncRlbk']))
			// An asynchronous rollback has just been spawned, report the rollback id
			$misc->printMsg(sprintf($lang['emajasyncrlbkstarted'], $_REQUEST['rlbkid']));

		$rlbkInfo = $emajdb->getOneRlbk($_REQUEST['rlbkid']);

		// Save some pieces of information
		$priorRlbk = isset($rlbkInfo->fields['rlbk_prior']) ? $rlbkInfo->fields['rlbk_prior'] : '';
		$nextRlbk = isset($rlbkInfo->fields['rlbk_next']) ? $rlbkInfo->fields['rlbk_next'] : '';
		$status = $rlbkInfo->fields['rlbk_status'];
		$isCompleted = ($status == 'COMPLETED' || $status == 'COMMITTED' || $status == 'ABORTED');
		if ($emajdb->getNumEmajVersion() >= 40300) {	// version >= 4.3
			$comment = $rlbkInfo->fields['rlbk_comment'];
		};

		if (! $isCompleted) {
			$rlbkInProgressInfo = $emajdb->getOneInProgressRlbk($_REQUEST['rlbkid']);
		} else {
			$rlbkReportMsgs = $emajdb->getRlbkReportMsg($_REQUEST['rlbkid']);
		}

		$rlbkSessions = $emajdb->getRlbkSessions($_REQUEST['rlbkid']);

		$rlbkSteps = $emajdb->getRlbkSteps($_REQUEST['rlbkid']);

		$columnsProperties = array(
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
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
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
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkEndDateTime' => array(
				'title' => $lang['emajrlbkend'],
				'field' => field('rlbk_end_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'locale' => $lang['applocale'],
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
					'locale' => $lang['applocale'],
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
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'sessionEndDateTime' => array(
				'title' => $lang['strend'],
				'field' => field('rlbs_end_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'locale' => $lang['applocale'],
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
					'locale' => $lang['applocale'],
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
				'estimateQuality' => array(
					'title' => 'Q',
					'field' => field('rlbp_estimate_quality'),
					'type'	=> 'callback',
					'params'=> array(
						'function' => 'renderEstimateQuality',
					),
					'sorter_text_extraction' => 'span_text',
					'filter' => false,
				),
			));
		}

		$columnsSteps = array_merge($columnsSteps, array(
			'quantity' => array(
				'title' => $lang['strquantity'],
				'field' => field('rlbp_quantity'),
				'params'=> array('align' => 'right'),
				),
			),
		);

		if ($_SESSION['emaj']['RlbkShowEstimates']) {
			$columnsSteps = array_merge($columnsSteps, array(
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

		// Get the auto-refresh configuration parameter (10 seconds if not found in the config.inc.php file)
		if (isset($conf['auto_refresh'])) {
			$autoRefreshTimeout = $conf['auto_refresh'];
		} else {
			$autoRefreshTimeout = 10;
		}

		// Manage the autorefresh_rlbkid cookie if it exists
		if ($autoRefreshTimeout > 0) {
			if ($isCompleted) {
				// Delete the autorefresh_rlbkid cookie if it exists
				if (isset($_COOKIE['autorefresh_rlbkid'])) {
					echo "<script>deleteARCookie();</script>\n";
				}
			} else {
				$refreshUrl = "emajrollbacks.php?action=show_rollback&amp;{$misc->href}&amp;rlbkid=" . htmlspecialchars($_REQUEST['rlbkid']);
				$checked = '';
				if (isset($_COOKIE['autorefresh_rlbkid'])) {
					if ($_COOKIE['autorefresh_rlbkid'] == $_REQUEST['rlbkid']) {
						$checked = 'checked';
						echo "<script>schedulePageReload({$autoRefreshTimeout}, '" . htmlspecialchars_decode($refreshUrl) . "');</script>\n";
					} else {
						echo "<script>deleteARCookie();</script>\n";
					}
				}
			}
		}

		// Display the navigation links
		// ... in column 1, the prior rollback link, if any
		echo "<div class=\"rlbk-nav\">\n";
		echo "\t<div class=\"rlbk-nav-15\">\n";
		if ($priorRlbk <> '') {
			echo "\t\t<a href=\"emajrollbacks.php?action=show_rollback&amp;{$misc->href}&amp;rlbkid={$priorRlbk}\">\n";
			echo "\t\t\t<div><b>&lt;</b></div>\n";
			echo "\t\t\t<div>#{$priorRlbk}</div>\n";
			echo "\t\t</a>\n";
		}
		echo "\t</div>\n";

		// ... an empty column 2
		echo "\t<div class=\"rlbk-nav-24\"></div>\n";

		// ... in column 3, the title and the rollbacks list link
		echo "\t<div class=\"rlbk-nav-3\">\n";
		echo "\t\t<div class=\"rlbk-nav-title\">\n";
		echo "\t\t<a href=\"emajrollbacks.php?action=show_rollback&amp;{$misc->href}&amp;rlbkid=" . htmlspecialchars($_REQUEST['rlbkid']) . "\">\n";
		echo "\t\t\t{$lang['emajrollback']} #" . htmlspecialchars($_REQUEST['rlbkid']) . "</a>\n";
		echo "\t\t</div>\n";
		echo "\t\t<div>\n";
		echo "\t\t\t<a href=\"emajrollbacks.php?action=show_rollbacks&amp;{$misc->href}\">{$lang['strbacktolist']}</a>\n";
		echo "\t\t</div>\n";
		echo "\t</div>\n";

		// ... in column 4, the autorefresh toogle button, if relevant
		echo "\t<div class=\"rlbk-nav-24\">\n";
		echo "\t\t<div class=\"rlbk-nav-arbutton\">\n";
		if ($autoRefreshTimeout > 0 && !$isCompleted) {
			echo "\t\t<label class=\"switch\">\n";
			echo "\t\t\t<input type=\"checkbox\" name=\"autorefresh\" {$checked} onchange=\"toggleAutoRefresh(this, {$_REQUEST['rlbkid']}, '" . htmlspecialchars_decode($refreshUrl) . "')\">\n";
			echo "\t\t\t<span class=\"slider\"></span>";
			echo "\t\t</label>";
			echo "\t\t\t<br>{$lang['strautorefresh']}\n";
		}
		echo "\t\t</div>\n";
		echo "\t</div>\n";

		// ... in column 5, the next rollback link, if any
		echo "\t<div class=\"rlbk-nav-15\">\n";
		if ($nextRlbk <> '') {
			echo "\t\t<a href=\"emajrollbacks.php?action=show_rollback&amp;{$misc->href}&amp;rlbkid={$nextRlbk}\">\n";
			echo "\t\t\t<div><b>&gt;</b></div>\n";
			echo "\t\t\t<div>#{$nextRlbk}</div>\n";
			echo "\t\t</a>\n";
		}
		echo "\t</div>\n";
		echo "</div>\n";

		// print rollback properties
		$misc->printTitle($lang['strproperties']);
		$misc->printTable($rlbkInfo, $columnsProperties, $actions, 'detailRlbkProperties', 'No rollback, internal error !');

		// display rollback comment if exists
		if ($emajdb->getNumEmajVersion() >= 40300 && $comment<>'') {	// version >= 4.3
			echo "<p>{$lang['strcommentlabel']}<span class=\"comment\">{$comment}</span></p>\n";
		}

		// display the buttons corresponding to the available functions for the rollback

		$navlinks = array();
		if ($emajdb->isEmaj_Adm()) {

			// comment_group
			if ($emajdb->getNumEmajVersion() >= 40300) {	// version >= 4.3
				$navlinks['comment_rollback'] = array (
					'content' => $lang['emajsetcomment'],
					'attr'=> array (
						'href' => array (
							'url' => 'emajrollbacks.php',
							'urlvars' => array (
								'action' => 'comment_rollback',
								'rlbkid' => $_REQUEST['rlbkid'],
								'back' => 'detail',
								'autorefresh' => '0',
							)
						)
					),
				);
			}
		}
		$misc->printLinksList($navlinks, 'buttonslist');

		// print rollback progress data
		$rlbkInfo->moveFirst();
		echo "<h4>{$lang['emajrlbkprogress']}</h4>\n";
		if ($isCompleted) {

			// The rollback is completed
			$misc->printTable($rlbkInfo, $columnsCompleted, $actions, 'detailRlbkProgress', 'No rollback, internal error !');
		} else {

			// The rollback is in progress
			$misc->printTable($rlbkInProgressInfo, $columnsInProgress, $actions, 'detailRlbkProgress', 'No rollback, internal error !');
		}

		// final execution report of the rollback operation
		if ($isCompleted) {
			$misc->printTitle($lang['emajrlbkexecreport']);
			echo "<div id=\"rlbkReport\" style=\"margin-top:15px;margin-bottom:15px\" >\n";
			$misc->printTable($rlbkReportMsgs, $columnsReport, $actions, 'rlbkReport', null, null, array('sorter' => true, 'filter' => false));
			echo "</div>\n";
		}

		// print planning data
		if ($rlbkSteps->recordCount() > 0) {
			$misc->printTitle($lang['emajrlbkplanning'] . "&nbsp;&nbsp;<img src=\"{$misc->icon('Info-inv')}\" alt=\"info\" title=\"{$lang['emajrlbkplanninghelp']}\"/>");

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
			if ($_SESSION['emaj']['RlbkShowEstimates'])
				echo "&nbsp;&nbsp;<img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajrlbkestimmethodhelp']}\"/>\n";
			echo "\t</form>\n";
			echo "</div>\n";

			$misc->printTable($rlbkSteps, $columnsSteps, $actions, 'detailRlbkSteps', null, null, array('sorter' => true, 'filter' => true));

		} else {
			$misc->printTitle($lang['emajrlbkplanning']);
			echo "<p>{$lang['emajnorlbkstep']}</p>\n";
		}

		// print sessions data
		if ($rlbkSessions->recordCount() > 0) {
			$misc->printTitle($lang['emajrlbksessions']);
			$misc->printTable($rlbkSessions, $columnsSessions, $actions, 'detailRlbkSession', null);
		}
	}

	/**
	 * Prepare comment rollback: ask for comment and confirmation
	 */
	function comment_rollback() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('emajrollback', 'database', 'emajrollbacks');

		$misc->printTitle($lang['emajcommentarollback']);

		$rlbk = $emajdb->getOneRlbk($_REQUEST['rlbkid']);

		echo "<p>", sprintf($lang['emajcommentrollback'], htmlspecialchars($_REQUEST['rlbkid'])), "</p>\n";
		echo "<form action=\"emajrollbacks.php\" method=\"post\">\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" value=\"", htmlspecialchars($rlbk->fields['rlbk_comment']), "\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"comment_rollback_ok\" />\n";
		echo "<input type=\"hidden\" name=\"rlbkid\" value=\"", htmlspecialchars($_REQUEST['rlbkid']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"commentrollback\" value=\"{$lang['strok']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform comment rollback
	 */
	function comment_rollback_ok() {
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				show_rollbacks();
			} else {
				show_rollback();
			}
		} else {

			$status = $emajdb->setCommentRollback($_POST['rlbkid'],$_POST['comment']);
			if ($status == 0)
				if ($_POST['back']=='list') {
					show_rollbacks(sprintf($lang['emajcommentrollbackok'], htmlspecialchars($_POST['rlbkid'])));
				} else {
					show_rollback(sprintf($lang['emajcommentrollbackok'], htmlspecialchars($_POST['rlbkid'])));
				}
			else
				if ($_POST['back']=='list') {
					show_rollbacks('',sprintf($lang['emajcommentrollbackerr'], htmlspecialchars($_POST['rlbkid'])));
				} else {
					show_rollback('',sprintf($lang['emajcommentrollbackerr'], htmlspecialchars($_POST['rlbkid'])));
				}
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
		echo "<form action=\"emajrollbacks.php\" method=\"post\">\n";
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

	$scripts = "<script src=\"js/emajrollbacks.js\"></script>";

	$misc->printHtmlHeader($lang['emajrollbacksmanagement'], $scripts, 'emajrollbacks');
	$misc->printBody();

	if (isset($_COOKIE['autorefresh_rlbkid'])) {
		// Delete the autorefresh cookie if it is not relevant for the requested action
		if ($action != 'show_rollback') {
			echo "<script>deleteARCookie();</script>\n";
		}
	}

	switch ($action) {
		case 'comment_rollback':
			comment_rollback();
			break;
		case 'comment_rollback_ok':
			comment_rollback_ok();
			break;
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
			if (isset($_REQUEST['rlbkid']))
				show_rollback();
			else
				show_rollbacks();
	}

	$misc->printFooter();

?>
