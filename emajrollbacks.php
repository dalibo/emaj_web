<?php

	/*
	 * Manage E-Maj rollbacks monitoring and consolidation
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	// Callback function to dynamicaly translate a boolean column into the user's language
	function renderBoolean($val) {
		global $lang;
		return $val == 't' ? $lang['stryes'] : $lang['strno'];
	}

	/**
	 * Display the status of past and in progress rollback operations
	 */
	function show_rollbacks() {
		global $lang, $misc, $emajdb;

		$misc->printHeader('database', 'action=show_rollbacks', 'database', 'emajrollbacks');

		$emajOK = $misc->checkEmajExtension();

		if ($emajOK) {
	
			if (isset($_REQUEST['rlbkId']))
				// An asynchronous rollbakc has just been spawned, report the rollback id
				$misc->printMsg(sprintf($lang['emajasyncrlbkstarted'], $_REQUEST['rlbkId']));

			if (!isset($_SESSION['emaj']['RlbkNb'])) {
				$_SESSION['emaj']['RlbkNb'] = 3;
				$_SESSION['emaj']['NbRlbkChecked'] = 1;
			}
			if (!isset($_SESSION['emaj']['RlbkRetention'])) {
				$_SESSION['emaj']['RlbkRetention'] = 24;
			}
			if (!isset($_SESSION['emaj']['NbRlbkChecked'])) {
				$nbRlbk = -1;
			} else {
				$nbRlbk = $_SESSION['emaj']['RlbkNb'];
			}
			if (!isset($_SESSION['emaj']['DurationChecked'])) {
				$rlbkRetention = -1;
			} else {
				$rlbkRetention = $_SESSION['emaj']['RlbkRetention'];
			}
	
			$columnsInProgressRlbk = array(
				'rlbkId' => array(
					'title' => $lang['emajrlbkid'],
					'field' => field('rlbk_id'),
					'params'=> array('align' => 'right'),
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
					'params'=> array('align' => 'center'),
				),
				'rlbkElapse' => array(
					'title' => $lang['emajcurrentduration'],
					'field' => field('rlbk_current_elapse'),
					'params'=> array('align' => 'center'),
				),
				'rlbkRemaining' => array(
					'title' => $lang['emajestimremaining'],
					'field' => field('rlbk_remaining'),
					'params'=> array('align' => 'center'),
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
				'rlbkMarkDateTime' => array(
					'title' => $lang['emajmarksetat'],
					'field' => field('rlbk_mark_datetime'),
				),
				'isLogged' => array(
					'title' => $lang['emajislogged'],
					'field' => field('rlbk_is_logged'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderBoolean', 'align' => 'center')
				),
				'rlbkNbSession' => array(
					'title' => $lang['emajnbsession'],
					'field' => field('rlbk_nb_session'),
					'params'=> array('align' => 'right'),
				),
				'rlbkNbTable' => array(
					'title' => $lang['emajnbtabletoprocess'],
					'field' => field('rlbk_eff_nb_table'),
					'params'=> array('align' => 'right'),
				),
				'rlbkNbSeq' => array(
					'title' => $lang['emajnbseqtoprocess'],
					'field' => field('rlbk_nb_sequence'),
					'params'=> array('align' => 'right'),
				),
			);
	
			$columnsCompletedRlbk = array(
				'rlbkId' => array(
					'title' => $lang['emajrlbkid'],
					'field' => field('rlbk_id'),
					'params'=> array('align' => 'right'),
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
					'params'=> array('align' => 'center'),
				),
				'rlbkEndDateTime' => array(
					'title' => $lang['emajrlbkend'],
					'field' => field('rlbk_end_datetime'),
					'params'=> array('align' => 'center'),
				),
				'rlbkDuration' => array(
					'title' => $lang['emajduration'],
					'field' => field('rlbk_duration'),
					'params'=> array('align' => 'center'),
				),
				'rlbkMark' => array(
					'title' => $lang['emajtargetmark'],
					'field' => field('rlbk_mark'),
				),
				'rlbkMarkDateTime' => array(
					'title' => $lang['emajmarksetat'],
					'field' => field('rlbk_mark_datetime'),
				),
				'isLogged' => array(
					'title' => $lang['emajislogged'],
					'field' => field('rlbk_is_logged'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderBoolean', 'align' => 'center')
				),
				'rlbkNbSession' => array(
					'title' => $lang['emajnbsession'],
					'field' => field('rlbk_nb_session'),
					'params'=> array('align' => 'right'),
				),
				'rlbkNbTable' => array(
					'title' => $lang['emajnbproctable'],
					'field' => field('rlbk_eff_nb_table'),
					'params'=> array('align' => 'right'),
				),
				'rlbkNbSeq' => array(
					'title' => $lang['emajnbprocseq'],
					'field' => field('rlbk_nb_sequence'),
					'params'=> array('align' => 'right'),
				),
			);
	
			$actions = array();
	
			// Get rollback information from the database
			$completedRlbks = $emajdb->getCompletedRlbk($nbRlbk, $rlbkRetention);
	
			$misc->printTitle($lang['emajinprogressrlbk']);
			if ($emajdb->isDblinkUsable()) {
				$inProgressRlbks = $emajdb->getInProgressRlbk();
				$misc->printTable($inProgressRlbks, $columnsInProgressRlbk, $actions, 'inProgressRlbk', $lang['emajnorlbk']);
			} else {
				echo "<p>{$lang['emajrlbkmonitornotavailable']}</p>\n";
			}
	
			$misc->printTitle($lang['emajcompletedrlbk']);
	
			// Form to setup parameters for completed rollback operations filtering
			echo "<div style=\"margin-bottom:10px;\">\n";
			echo "<form action=\"emajrollbacks.php?action=filterrlbk\" method=\"post\">\n";
			echo "{$lang['emajfilterrlbk1']} :&nbsp;&nbsp;\n";
	
			echo "<input type=checkbox name=\"emajnbrlbkchecked\" id=\"nbrlbkchecked\"";
			if (isset($_SESSION['emaj']['NbRlbkChecked'])) echo " checked";
			echo "/>\n<input type=\"number\" name=\"emajRlbkNb\" style=\"width: 3em;\" id=\"rlbkNb\" min=\"1\" value=\"{$_SESSION['emaj']['RlbkNb']}\"";
			if (!isset($_SESSION['emaj']['NbRlbkChecked'])) echo " disabled";
			echo "/>\n{$lang['emajfilterrlbk2']}&nbsp;&nbsp;&nbsp;";
	
			echo "<input type=checkbox name=\"emajdurationchecked\" id=\"durationchecked\"";
			if (isset($_SESSION['emaj']['DurationChecked'])) echo " checked";
			echo "/>\n {$lang['emajfilterrlbk3']} \n";
			echo "<input type=\"number\" name=\"emajRlbkRetention\" style=\"width: 4em;\" id=\"rlbkRetention\" min=\"1\" value=\"{$_SESSION['emaj']['RlbkRetention']}\"";
			if (!isset($_SESSION['emaj']['DurationChecked'])) echo " disabled";
			echo "/>\n {$lang['emajfilterrlbk4']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	
			echo $misc->form;
			echo "<input type=\"submit\" name=\"filterrlbk\" value=\"{$lang['emajfilter']}\" />\n";
			echo "</form></div>\n";
	
			$misc->printTable($completedRlbks, $columnsCompletedRlbk, $actions, 'completedRlbk', $lang['emajnorlbk']);
	
			// JQuery script to disable input field if the associated checkbox is not checked
			echo "<script type=\"text/javascript\">\n";
			echo "  $(\"#nbrlbkchecked\").bind('click', function () {\n";
			echo "    if ($(this).prop('checked')) {\n";
			echo "      $(\"#rlbkNb\").removeAttr('disabled');\n";
			echo "    } else {\n";
			echo "      $(\"#rlbkNb\").attr('disabled', true);\n";
			echo "    }\n";
			echo "  });\n";
			echo "  $(\"#durationchecked\").bind('click', function () {\n";
			echo "    if ($(this).prop('checked')) {\n";
			echo "      $(\"#rlbkRetention\").removeAttr('disabled');\n";
			echo "    } else {\n";
			echo "      $(\"#rlbkRetention\").attr('disabled', true);\n";
			echo "    }\n";
			echo "  });\n";
			echo "</script>\n";
	
			// Display the E-Maj logged rollback operations that may be consolidated (i.e. transformed into unlogged rollback)
			if ($emajdb->getNumEmajVersion() >= 20000) {			// version >= 2.0.0
	
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
					),
					'actions' => array(
						'title' => $lang['stractions'],
					),
				);
				if ($emajdb->isEmaj_Adm()) {
					$actions = array(
						'consolidate' => array(
							'content' => $lang['emajconsolidate'],
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
		}

		$misc->printFooter();
	}

	/**
	 * Prepare a rollback consolidation: ask for confirmation
	 */
	function consolidate_rollback() {
		global $misc, $lang;

		$misc->printHeader('database', '', 'database', 'emajrollbacks');

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

		$misc->printFooter();
	}

	/**
	 * Change the filtering parameters for the display of completed rollback operations
	 */
	function filterrlbk() {

		if (isset($_POST['emajnbrlbkchecked'])) {
			if (isset($_POST['emajRlbkNb'])) 
				$_SESSION['emaj']['RlbkNb'] = $_POST['emajRlbkNb'];
			$_SESSION['emaj']['NbRlbkChecked'] = $_POST['emajnbrlbkchecked'];
		} else {
			unset($_SESSION['emaj']['NbRlbkChecked']);
		}
		if (isset($_POST['emajdurationchecked'])) {
			if (isset($_POST['emajRlbkRetention'])) 
				$_SESSION['emaj']['RlbkRetention'] = $_POST['emajRlbkRetention'];
			$_SESSION['emaj']['DurationChecked'] = $_POST['emajdurationchecked'];
		} else {
			unset($_SESSION['emaj']['DurationChecked']);
		}

		show_rollbacks();
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

	$misc->printHtmlHeader($lang['emajplugin']);
	$misc->printBody();

	switch ($action) {
		case 'consolidate_rollback':
			consolidate_rollback();
			break;
		case 'consolidate_rollback_ok':
			consolidate_rollback_ok();
			break;
		case 'filterrlbk':
			filterrlbk();
			break;
		case 'show_rollbacks':
			show_rollbacks();
			break;
		default:
			show_rollbacks();
	}

	$misc->printFooter();

?>
