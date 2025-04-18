<?php

	/*
	 * Manage E-Maj rollbacks monitoring and consolidation
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';
	$isAutoRefresh = (isset($_REQUEST['autorefresh']) && $_REQUEST['autorefresh'] == 1) ? 1 : 0;

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

		// Get E-Maj rollbacks information from the database
		if ($emajdb->isDblinkUsable())
			$inProgressRlbks = $emajdb->getInProgressRlbk();

		$completedRlbks = $emajdb->getCompletedRlbk();

		$consolidableRlbks = $emajdb->getConsolidableRlbk();

		// Display in progress rollbacks

		$columnsInProgressRlbk = array(
			'rlbkId' => array(
				'title' => $lang['strrlbkid'],
				'field' => field('rlbk_id'),
				'params'=> array('align' => 'right'),
				'url'   => "emajrollbacks.php?action=show_rollback&amp;{$misc->href}&amp;",
				'vars'  => array('rlbkid' => 'rlbk_id'),
			),
			'rlbkGroups' => array(
				'title' => $lang['strgroups'],
				'field' => field('rlbk_groups_list'),
			),
			'rlbkStatus' => array(
				'title' => $lang['strstate'],
				'field' => field('rlbk_status'),
			),
			'rlbkStartDateTime' => array(
				'title' => $lang['strrlbkstart'],
				'field' => field('rlbk_start_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkElapse' => array(
				'title' => $lang['strcurrentduration'],
				'field' => field('rlbk_current_elapse'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
				'sorter_text_extraction' => 'span_text',
				'filter' => false,
			),
			'rlbkRemaining' => array(
				'title' => $lang['strestimremaining'],
				'field' => field('rlbk_remaining'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
				'sorter_text_extraction' => 'span_text',
				'filter' => false,
			),
			'rlbkCompletionPct' => array(
				'title' => $lang['strpctcompleted'],
				'field' => field('rlbk_completion_pct'),
				'params'=> array('align' => 'right'),
			),
			'rlbkMark' => array(
				'title' => $lang['strtargetmark'],
				'field' => field('rlbk_mark'),
			),
			'isLogged' => array(
				'title' => $lang['strislogged'],
				'field' => field('rlbk_is_logged'),
				'type'	=> 'yesno',
			),
			'rlbkNbSession' => array(
				'title' => $lang['strnbsession'],
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

		$actions = array();
		if ($emajdb->getNumEmajVersion() >= 40300 && $emajdb->isEmaj_Adm()) {	// version >= 4.3
			$actions = array_merge($actions, array(
				'comment_rollback' => array(
					'content' => $lang['strsetcomment'],
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

		if ($emajdb->isDblinkUsable()) {
			$misc->printTitle($lang['strinprogressrlbk'], $misc->buildTitleRecordsCounter($inProgressRlbks));
			$misc->printTable($inProgressRlbks, $columnsInProgressRlbk, $actions, 'inProgressRlbk', $lang['strnorlbk'], null, array('sorter' => true, 'filter' => true));

		} else {
			$misc->printTitle($lang['strinprogressrlbk']);
			echo "<p>{$lang['strrlbkmonitornotavailable']}</p>\n";
		}
		echo "<hr/>\n";

		// Display completed rollbacks

		$columnsCompletedRlbk = array(
			'rlbkId' => array(
				'title' => $lang['strrlbkid'],
				'field' => field('rlbk_id'),
				'params'=> array('align' => 'right'),
				'url'   => "emajrollbacks.php?action=show_rollback&amp;{$misc->href}&amp;",
				'vars'  => array('rlbkid' => 'rlbk_id'),
			),
			'rlbkGroups' => array(
				'title' => $lang['strgroups'],
				'field' => field('rlbk_groups_list'),
			),
			'rlbkStatus' => array(
				'title' => $lang['strstate'],
				'field' => field('rlbk_status'),
				'type'	=> 'callback',
				'params'=> array(
					'function' => 'renderRlbkStatusInList',
					'align' => 'center',
				),
				'sorter_text_extraction' => 'div_title',
				'filter' => false,
			),
			'rlbkStartDateTime' => array(
				'title' => $lang['strrlbkstart'],
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
				'title' => $lang['strrlbkend'],
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
				'title' => $lang['strduration'],
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
				'title' => $lang['strtargetmark'],
				'field' => field('rlbk_mark'),
			),
			'isLogged' => array(
				'title' => $lang['strislogged'],
				'field' => field('rlbk_is_logged'),
				'type'	=> 'yesno',
			),
			'rlbkNbSession' => array(
				'title' => $lang['strnbsession'],
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

		$misc->printTitle($lang['strcompletedrlbk'], $misc->buildTitleRecordsCounter($completedRlbks));

		// The actions are the same as in progress rollbacks
		$misc->printTable($completedRlbks, $columnsCompletedRlbk, $actions, 'completedRlbk', $lang['strnorlbk'], null, array('sorter' => true, 'filter' => true));

		echo "<hr/>\n";

		// Display the E-Maj logged rollback operations that may be consolidated (i.e. transformed into unlogged rollback)

		$columnsConsRlbk = array(
			'consGroup' => array(
				'title' => $lang['strgroup'],
				'field' => field('cons_group'),
				'url'   => "groupproperties.php&amp;{$misc->href}&amp;",
				'vars'  => array('group' => 'cons_group'),
			),
			'consTargetMark' => array(
				'upper_title' => $lang['strtargetmark'],
				'upper_title_colspan' => 2,
				'title' => $lang['strname'],
				'field' => field('cons_target_rlbk_mark_name'),
			),
			'consTargetMarkDateTime' => array(
				'title' => $lang['strmarksetat'],
				'field' => field('cons_target_rlbk_mark_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
				),
				'sorter_text_extraction' => 'span_text',
			),
			'rlbkNbRow' => array(
				'title' => $lang['strchanges'],
				'field' => field('cons_rows'),
				'params'=> array('align' => 'right'),
			),
			'rlbkNbMark' => array(
				'title' => $lang['strnbintermediatemark'],
				'field' => field('cons_marks'),
				'params'=> array('align' => 'right'),
			),
			'consEndMark' => array(
				'upper_title' => $lang['strendrollbackmark'],
				'upper_title_colspan' => 2,
				'title' => $lang['strname'],
				'field' => field('cons_end_rlbk_mark_name'),
			),
			'consEndMarkDateTime' => array(
				'title' => $lang['strmarksetat'],
				'field' => field('cons_end_rlbk_mark_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
				),
				'sorter_text_extraction' => 'span_text',
			),
			'actions' => array(
				'title' => $lang['stractions'],
			),
		);
		if ($emajdb->isEmaj_Adm()) {
			$actions = array(
				'consolidate' => array(
					'content' => $lang['strconsolidate'],
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

		$misc->printTitle($lang['strconsolidablerlbk'], $misc->buildTitleRecordsCounter($consolidableRlbks));

		$misc->printTable($consolidableRlbks, $columnsConsRlbk, $actions, 'consolidableRlbk', $lang['strnorlbk'], null, array('sorter' => true, 'filter' => true));
	}

	/**
	 * Display the details of a rollback operation
	 */
	function show_rollback($isAutoRefresh) {
		global $lang, $misc, $emajdb, $conf;

		$misc->printHeader('emajrollback', 'database', 'emajrollbacks');

		if (isset($_REQUEST['asyncRlbk']))
			// An asynchronous rollback has just been spawned, report the rollback id
			$misc->printMsg(sprintf($lang['strasyncrlbkstarted'], $_REQUEST['rlbkid']));

		$rlbkInfo = $emajdb->getOneRlbk($_REQUEST['rlbkid']);

		// Save some pieces of information
		$priorRlbk = isset($rlbkInfo->fields['rlbk_prior']) ? $rlbkInfo->fields['rlbk_prior'] : '';
		$nextRlbk = isset($rlbkInfo->fields['rlbk_next']) ? $rlbkInfo->fields['rlbk_next'] : '';
		$status = $rlbkInfo->fields['rlbk_status'];
		$isCompleted = ($status == 'COMPLETED' || $status == 'COMMITTED' || $status == 'ABORTED');
		if ($emajdb->getNumEmajVersion() >= 40300) {	// version >= 4.3
			$comment = $rlbkInfo->fields['rlbk_comment'];
		};

		if ($isCompleted) {
			$isAutoRefresh = 0;
			$rlbkReportMsgs = $emajdb->getRlbkReportMsg($_REQUEST['rlbkid']);
		} else {
			$rlbkInProgressInfo = $emajdb->getOneInProgressRlbk($_REQUEST['rlbkid']);
		}

		$rlbkSessions = $emajdb->getRlbkSessions($_REQUEST['rlbkid']);

		$rlbkSteps = $emajdb->getRlbkSteps($_REQUEST['rlbkid']);

		$columnsProperties = array(
			'rlbkGroups' => array(
				'title' => $lang['strgroups'],
				'field' => field('rlbk_groups_list'),
			),
			'rlbkMark' => array(
				'title' => $lang['strtargetmark'],
				'field' => field('rlbk_mark'),
			),
			'rlbkMarkDateTime' => array(
				'title' => $lang['strmarksetat'],
				'field' => field('rlbk_mark_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'isLogged' => array(
				'title' => $lang['strislogged'],
				'field' => field('rlbk_is_logged'),
				'type'	=> 'yesno',
			),
			'rlbkNbSession' => array(
				'title' => $lang['strnbsession'],
				'field' => field('rlbk_nb_session'),
				'params'=> array('align' => 'center'),
			),
			'rlbkNbTable' => array(
				'title' => $lang['strnbtabletoprocess'],
				'field' => field('rlbk_tbl'),
				'params'=> array('align' => 'center'),
			),
			'rlbkNbSeq' => array(
				'title' => $lang['strnbseqtoprocess'],
				'field' => field('rlbk_seq'),
				'params'=> array('align' => 'center'),
			),
		);

		$columnsCompleted = array(
			'rlbkStatus' => array(
				'title' => $lang['strstate'],
				'field' => field('rlbk_status'),
				'type'	=> 'callback',
				'params'=> array(
					'function' => 'renderRlbkStatusInDetail',
					'align' => 'center',
				),
			),
			'rlbkStartDateTime' => array(
				'title' => $lang['strrlbkstart'],
				'field' => field('rlbk_start_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkEndDateTime' => array(
				'title' => $lang['strrlbkend'],
				'field' => field('rlbk_end_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkGlobalDuration' => array(
				'title' => $lang['strglobalduration'],
				'field' => field('rlbk_global_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkPlanningDuration' => array(
				'title' => $lang['strplanningduration'],
				'field' => field('rlbk_planning_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkLockingDuration' => array(
				'title' => $lang['strlockingduration'],
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
				'title' => $lang['strstate'],
				'field' => field('rlbk_status'),
			),
			'rlbkStartDateTime' => array(
				'title' => $lang['strrlbkstart'],
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
				'title' => $lang['strcurrentduration'],
				'field' => field('rlbk_current_elapse'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkPlanningDuration' => array(
				'title' => $lang['strplanningduration'],
				'field' => field('rlbk_planning_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkLockingDuration' => array(
				'title' => $lang['strlockingduration'],
				'field' => field('rlbk_locking_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkRemaining' => array(
				'title' => $lang['strestimremaining'],
				'field' => field('rlbk_remaining'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'rlbkCompletionPct' => array(
				'title' => $lang['strpctcompleted'],
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

		$columnsSteps = array(
			'rank' => array(
				'title' => '#',
				'field' => field('rlbp_rank'),
				'params'=> array('align' => 'right'),
			),
			'schema' => array(
				'title' => $lang['strschema'],
				'field' => field('rlbp_schema'),
				'url'   => "schemas.php?action=list_schemas&amp;{$misc->href}&amp;",
				'vars'  => array('schema' => 'rlbp_schema'),
			),
			'table' => array(
				'title' => $lang['strtable'],
				'field' => field('rlbp_table'),
				'url'	=> "redirect.php?subject=table&amp;{$misc->href}&amp;",
				'vars'  => array('schema' => 'rlbp_schema', 'table' => 'rlbp_table'),
			),
			'step' => array(
				'title' => $lang['strrlbkstep'],
				'field' => field('rlbp_action'),
			),
			'session' => array(
				'title' => $lang['strrlbksession'],
				'field' => field('rlbp_session'),
				'params'=> array('align' => 'center'),
			),
			'startDateTime' => array(
				'title' => $lang['strrlbkstarttime'],
				'field' => field('rlbp_start_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strprecisetimeformat'],
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'duration' => array(
				'upper_title' => $lang['stractual'],
				'upper_title_colspan' => 2,
				'title' => $lang['strduration'],
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
			'estimatedDuration' => array(
				'upper_title' => $lang['strestimates'],
				'upper_title_colspan' => 4,
				'upper_title_info' => $lang['strrlbkestimmethodhelp'],
				'title' => $lang['strduration'],
				'field' => field('rlbp_estimated_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'estimatedQuantity' => array(
				'title' => $lang['strquantity'],
				'field' => field('rlbp_estimated_quantity'),
				'params'=> array('align' => 'right'),
			),
			'estimateQuality' => array(
				'title' => $lang['strabbrquality'],
				'field' => field('rlbp_estimate_quality'),
				'type'	=> 'callback',
				'params'=> array(
					'function' => 'renderEstimateQuality',
				),
				'sorter_text_extraction' => 'span_text',
				'filter' => false,
				'class' => 'nopadding',
			),
			'estimateMethod' => array(
				'title' => $lang['strmethod'],
				'field' => field('rlbp_estimate_method'),
				'params'=> array('align' => 'center'),
			),
		);

		$columnsSessions = array(
			'session' => array(
				'title' => $lang['strrlbksession'],
				'field' => field('rlbs_session'),
				'params'=> array('align' => 'center'),
			),
			'sessionStartDateTime' => array(
				'title' => $lang['strrlbkstarttime'],
				'field' => field('rlbs_start_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'sessionEndDateTime' => array(
				'title' => $lang['strrlbkendtime'],
				'field' => field('rlbs_end_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'sessionDuration' => array(
				'title' => $lang['strduration'],
				'field' => field('rlbs_duration'),
				'type' => 'spanned',
				'params'=> array(
					'intervalformat' => $lang['strintervalformat'],
					'class' => 'tooltip left-aligned-tooltip',
				),
			),
			'sessionTxId' => array(
				'title' => $lang['strtxid'],
				'field' => field('rlbs_txid'),
				'params'=> array('align' => 'right'),
			),
		);

		$urlvars = $misc->getRequestVars();

		$actions = array();

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
		echo "\t\t\t{$lang['strrollback']} #" . htmlspecialchars($_REQUEST['rlbkid']) . "</a>\n";
		echo "\t\t</div>\n";
		echo "\t\t<div>\n";
		echo "\t\t\t<a href=\"emajrollbacks.php?action=show_rollbacks&amp;{$misc->href}\">{$lang['strbacktolist']}</a>\n";
		echo "\t\t</div>\n";
		echo "\t</div>\n";

		// ... in column 4, the autorefresh toogle button, if relevant
		echo "\t<div class=\"rlbk-nav-24\">\n";
		// Get the auto-refresh configuration parameter (10 seconds if not found in the config.inc.php file)
		$autoRefreshTimeout = (isset($conf['auto_refresh'])) ? $conf['auto_refresh'] : 10;
		if ($autoRefreshTimeout > 0 && !$isCompleted) {
			$refreshUrl = "emajrollbacks.php?action=show_rollback&amp;{$misc->href}&amp;autorefresh=1&amp;rlbkid=" . htmlspecialchars($_REQUEST['rlbkid']);
			$checked = ($isAutoRefresh) ? 'checked' : '';
			echo "\t\t<div class=\"rlbk-nav-arbutton\">\n";
			echo "\t\t<label class=\"switch\">\n";
			echo "\t\t\t<input type=\"checkbox\" name=\"autorefresh\" {$checked} onchange=\"toggleAutoRefresh(this, '" . htmlspecialchars_decode($refreshUrl) . "')\">\n";
			echo "\t\t\t<span class=\"slider\"></span>";
			echo "\t\t</label>";
			$helpMsg = sprintf($lang['strautorefreshhelp'], $autoRefreshTimeout);
			echo "\t<img src=\"{$misc->icon('Info')}\" alt=\"info\" class=\"autorefresh-help\" title=\"$helpMsg\"/>";
			echo "\t\t</div>\n";
			echo "\t\t\t<div class=\"autorefresh-label\">{$lang['strautorefresh']}</div>\n";
		}
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

		// Schedule the page reload when auto-refresh is on.
		if ($autoRefreshTimeout > 0 && $isAutoRefresh) {
			$misc->schedulePageReload($refreshUrl, $autoRefreshTimeout);
		}

		// Print rollback properties
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
					'content' => $lang['strsetcomment'],
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

		// Print rollback progress data

		$rlbkInfo->moveFirst();
		echo "<h4>{$lang['strrlbkprogress']}</h4>\n";
		if ($isCompleted) {

			// The rollback is completed
			$misc->printTable($rlbkInfo, $columnsCompleted, $actions, 'detailRlbkProgress', 'No rollback, internal error !');
		} else {

			// The rollback is in progress
			$misc->printTable($rlbkInProgressInfo, $columnsInProgress, $actions, 'detailRlbkProgress', 'No rollback, internal error !');
		}

		// final execution report of the rollback operation
		if ($isCompleted) {
			$misc->printTitle($lang['strrlbkexecreport']);
			echo "<div id=\"rlbkReport\" style=\"margin-top:15px;margin-bottom:15px\" >\n";
			$misc->printTable($rlbkReportMsgs, $columnsReport, $actions, 'rlbkReport', null, null, array('sorter' => true, 'filter' => false));
			echo "</div>\n";
		}

		// Print planning data

		$misc->printTitle($lang['strrlbkelemsteps'], $misc->buildTitleRecordsCounter($rlbkSteps), $lang['strrlbkelemstepshelp']);

		$misc->printTable($rlbkSteps, $columnsSteps, $actions, 'detailRlbkSteps', $lang['strnorlbkstep'], null, array('sorter' => true, 'filter' => true));

		// Print sessions data

		if ($rlbkSessions->recordCount() > 0) {

			$misc->printTitle($lang['strrlbksessions'], $misc->buildTitleRecordsCounter($rlbkSessions));

			$misc->printTable($rlbkSessions, $columnsSessions, $actions, 'detailRlbkSession', null);
		}
	}

	/**
	 * Prepare comment rollback: ask for comment and confirmation
	 */
	function comment_rollback() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('emajrollback', 'database', 'emajrollbacks');

		$misc->printTitle($lang['strcommentarollback']);

		$rlbk = $emajdb->getOneRlbk($_REQUEST['rlbkid']);

		echo "<p>", sprintf($lang['strcommentrollback'], htmlspecialchars($_REQUEST['rlbkid'])), "</p>\n";
		echo "<form action=\"emajrollbacks.php\" method=\"post\">\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" value=\"", htmlspecialchars($rlbk->fields['rlbk_comment']), "\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"comment_rollback_ok\" />\n";
		echo "<input type=\"hidden\" name=\"rlbkid\" value=\"", htmlspecialchars($_REQUEST['rlbkid']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"commentrollback\" value=\"{$lang['strok']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform comment rollback
	 */
	function comment_rollback_ok($isAutoRefresh) {
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				show_rollbacks();
			} else {
				show_rollback($isAutoRefresh);
			}
		} else {

			$status = $emajdb->setCommentRollback($_POST['rlbkid'],$_POST['comment']);
			if ($status == 0)
				if ($_POST['back']=='list') {
					show_rollbacks(sprintf($lang['strcommentrollbackok'], htmlspecialchars($_POST['rlbkid'])));
				} else {
					show_rollback(sprintf($lang['strcommentrollbackok'], htmlspecialchars($_POST['rlbkid'])));
				}
			else
				if ($_POST['back']=='list') {
					show_rollbacks('',sprintf($lang['strcommentrollbackerr'], htmlspecialchars($_POST['rlbkid'])));
				} else {
					show_rollback('',sprintf($lang['strcommentrollbackerr'], htmlspecialchars($_POST['rlbkid'])));
				}
		}
	}

	/**
	 * Prepare a rollback consolidation: ask for confirmation
	 */
	function consolidate_rollback() {
		global $misc, $lang;

		$misc->printHeader('database', 'database', 'emajrollbacks');

		$misc->printTitle($lang['strconsolidaterlbk']);

		echo "<p>" . sprintf($lang['strconfirmconsolidaterlbk'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])) . "</p>\n";
		echo "<form action=\"emajrollbacks.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"consolidate_rollback_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"consolidaterlbk\" value=\"{$lang['strconsolidate']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
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
			show_rollbacks(sprintf($lang['strconsolidaterlbkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
		else
			show_rollbacks('',sprintf($lang['strconsolidaterlbkerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
	}

	// redirect to the emajenvir.php page if the emaj extension is not installed or accessible or is too old
	if (!(isset($emajdb) && $emajdb->isEnabled() && $emajdb->isAccessible()
		&& $emajdb->getNumEmajVersion() >= $oldest_supported_emaj_version_num)) {
		header('Location: emajenvir.php?' . $_SERVER["QUERY_STRING"]);
	}

	$scripts = "";

	$misc->printHtmlHeader($lang['strrollbacksmanagement'], $scripts, 'emajrollbacks');
	$misc->printBody();

	switch ($action) {
		case 'comment_rollback':
			comment_rollback();
			break;
		case 'comment_rollback_ok':
			comment_rollback_ok($isAutoRefresh);
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
			show_rollback($isAutoRefresh);
			break;
		default:
			if (isset($_REQUEST['rlbkid']))
				show_rollback($isAutoRefresh);
			else
				show_rollbacks();
	}

	$misc->printFooter();

?>
