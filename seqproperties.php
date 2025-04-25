<?php

	/*
	 * Display the properties of a given sequence
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');
	include_once('./libraries/tblseqcommon.inc.php');
	include_once('./libraries/seqactions.inc.php');

	/**
	 * Display the properties of a sequence
	 */
	function doDefault($msg = '', $errMsg = '') {
		global $data, $misc, $lang, $emajdb;

		$misc->printHeader('sequence', 'sequence', 'properties');

		$misc->printMsg($msg, $errMsg);
		$misc->printTitle(sprintf($lang['strnamedsequence'], $_REQUEST['schema'], $_REQUEST['sequence']));

		// Display the E-Maj properties, if any
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {

			$misc->printSubtitle($lang['stremajproperties']);

			$type = $emajdb->getEmajTypeTblSeq($_REQUEST['schema'], $_REQUEST['sequence']);

			if ($type == 'L') {
				echo "<p>{$lang['stremajlogsequence']}</p>\n";
			} elseif ($type == 'E') {
				echo "<p>{$lang['stremajinternalsequence']}</p>\n";
			} else {

				$prop = $emajdb->getRelationEmajProperties($_REQUEST['schema'], $_REQUEST['sequence']);

				$isAssigned = ($prop->recordCount() == 1);

				$columns = array(
					'group' => array(
						'title' => $lang['strgroup'],
						'field' => field('rel_group'),
						'url'   => "groupproperties.php&amp;{$misc->href}&amp;",
						'vars'  => array('group' => 'rel_group')
					),
					'starttime' => array(
						'title' => $lang['strsince'],
						'field' => field('assign_ts'),
						'type' => 'spanned',
						'params'=> array(
							'dateformat' => $lang['stroldtimestampformat'],
							'locale' => $lang['applocale'],
							'class' => 'tooltip left-aligned-tooltip',
							),
					),
				);

				$misc->printTable($prop, $columns, $actions, 'seqproperties-emaj', $lang['strseqnogroupownership']);
				// Display the buttons corresponding to the available functions for the sequence.

				if ($emajdb->isEmaj_Adm() && $emajdb->getNumEmajVersion() >= 30200) {			// version >= 3.2.0

					// Get the number of created groups (needed to display or hide some actions)
					if ($emajdb->isAccessible())
						$nbGroups = $emajdb->getNbGroups();
					else
						$nbGroups = 0;

					if ($nbGroups > 0) {

						$navlinks = array();

						if (! $isAssigned) {
							// Not yet assigned to a tables group
							$navlinks['assign_sequence'] = array (
								'content' => $lang['strassign'],
								'attr'=> array (
									'href' => array (
										'url' => "seqproperties.php",
										'urlvars' => array(
											'action' => 'assign_sequences',
											'schema' => $_REQUEST['schema'],
											'sequence' => $_REQUEST['sequence'],
										)
									)
								),
							);
						} else {
							// Already assigned to a tables group
							$prop->moveFirst();
							$group = $prop->fields['rel_group'];

							if ($nbGroups > 1) {
								$navlinks['move_sequence'] = array (
									'content' => $lang['strmove'],
									'attr'=> array (
										'href' => array (
											'url' => "seqproperties.php",
											'urlvars' => array(
												'action' => 'move_sequences',
												'schema' => $_REQUEST['schema'],
												'sequence' => $_REQUEST['sequence'],
												'group' => $group,
											)
										)
									),
								);
							}
							$navlinks['remove_sequence'] = array (
								'content' => $lang['strremove'],
								'attr'=> array (
									'href' => array (
										'url' => "seqproperties.php",
										'urlvars' => array(
											'action' => 'remove_sequences',
											'schema' => $_REQUEST['schema'],
											'sequence' => $_REQUEST['sequence'],
											'group' => $group,
										)
									)
								),
							);
						}
					}

					$misc->printLinksList($navlinks, 'buttonslist');
				}
			}

			echo "<hr/>\n";
		}

		// Display the sequence properties

		$misc->printSubtitle($lang['strseqproperties']);

		// Verify that the user has enough privileges to read the sequence
		$privilegeOk = $emajdb->hasSelectPrivilegeOnSequence($_REQUEST['schema'], $_REQUEST['sequence']);

		if (! $privilegeOk) {
			echo $lang['strnograntonsequence'];
		} else {

			// Fetch the sequence information
			$sequence = $emajdb->getSequenceProperties($_REQUEST['schema'], $_REQUEST['sequence']);

			// Show comment if any
			if ($sequence->fields['seqcomment'] !== null)
				echo "<p>{$lang['strcommentlabel']}<span class=\"comment\">{$misc->printVal($sequence->fields['seqcomment'])}</span></p>\n";
			$sequence->moveFirst();

			$columns = array(
				'lastvalue' => array(
					'title' => $lang['strlastvalue'],
					'field' => field('last_value'),
					'type'  => 'numeric',
					'params'=> array('class' => 'bold'),
				),
				'iscalled' => array(
					'title' => $lang['striscalled'],
					'field' => field('is_called'),
					'type'  => 'bool',
					'params'=> array('true' => $lang['stryes'], 'false' => $lang['strno'], 'class' => 'bold'),
				),
				'startvalue' => array(
					'title' => $lang['strstartvalue'],
					'field' => field('start_value'),
					'type'  => 'numeric',
				),
				'minvalue' => array(
					'title' => $lang['strminvalue'],
					'field' => field('min_value'),
					'type'  => 'numeric',
				),
				'maxvalue' => array(
					'title' => $lang['strmaxvalue'],
					'field' => field('max_value'),
					'type'  => 'numeric',
				),
				'increment' => array(
					'title' => $lang['strincrement'],
					'field' => field('increment_by'),
					'type'  => 'numeric',
				),
				'cancycle' => array(
					'title' => $lang['strcancycle'],
					'field' => field('cycle'),
					'type'  => 'bool',
					'params'=> array('true' => $lang['stryes'], 'false' => $lang['strno']),
				),
				'cachesize' => array(
					'title' => $lang['strcachesize'],
					'field' => field('cache_size'),
					'type'  => 'numeric',
				),
				'logcount' => array(
					'title' => $lang['strlogcount'],
					'field' => field('log_cnt'),
					'type'  => 'numeric',
				),
			);

			$actions = array();

			$misc->printTable($sequence, $columns, $actions, 'seqproperties-columns', $lang['strnodata']);
		}
	}

/********************************************************************************************************
 * Main piece of code
 *******************************************************************************************************/

	// Check that emaj and the sequence still exist.
	$misc->onErrorRedirect('sequence');

	// Print header
	$misc->printHtmlHeader($lang['strsequences']);
	$misc->printBody();

	switch($action) {
		case 'assign_sequences';
			assign_sequences();
			break;
		case 'assign_sequences_ok':
			assign_sequences_ok();
			break;
		case 'move_sequences';
			move_sequences();
			break;
		case 'move_sequences_ok':
			move_sequences_ok();
			break;
		case 'remove_sequences';
			remove_sequences();
			break;
		case 'remove_sequences_ok':
			remove_sequences_ok();
			break;
		default:
			doDefault();
			break;
	}

	// Print footer
	$misc->printFooter();

?>
