<?php

	/*
	 * Display the properties of a table: columns and tables group ownership
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');
	include_once('./libraries/tblseqcommon.inc.php');
	include_once('./libraries/tblactions.inc.php');

	// Callback function to modify the notnull column content
	// It replaces the TRUE value by an icon
	function renderIsNotNull($val) {
		global $misc, $lang;
		if ($val == 't') {
			$icon = $misc->icon('Checkmark');
			$alt = $lang['strnotnull'];
			return "<img src=\"{$icon}\" width=18 alt=\"{$alt}\" title=\"{$alt}\"/>";
		}
		return;
	}

	// Callback function to modify the valuationkind column content
	function renderValuationKind($val) {
		global $misc, $lang;

		$icon = '';
		switch ($val) {
			case 'DEF': $icon = $misc->icon('ColDEF'); $alt = $lang['strcolDEF']; break;
			case 'GDI': $icon = $misc->icon('ColGDI'); $alt = $lang['strcolGDI']; break;
			case 'GAI': $icon = $misc->icon('ColGAI'); $alt = $lang['strcolGAI']; break;
			case 'GAES': $icon = $misc->icon('ColGAES'); $alt = $lang['strcolGAES']; break;
			case 'GAE': $icon = $misc->icon('ColGAE'); $alt = $lang['strcolGAE']; break;
			default: return $val;
		}
		return "<img src=\"{$icon}\" alt=\"{$alt}\" title=\"{$alt}\" class=\"cellicon\"/>";
	}

	// Callback function to adjust the icons and links for constraints on table columns
	function renderConstraints($s, $p) {
		global $misc, $data;

		$str ='';
		foreach ($p['keys'] as $k => $c) {

			if (is_null($p['keys'][$k]['consrc'])) {
				$atts = $data->getAttributeNames($_REQUEST['table'], explode(' ', $p['keys'][$k]['indkey']));
				$c['consrc'] = ($c['contype'] == 'u' ? "UNIQUE (" : "PRIMARY KEY (") . join(',', $atts) . ')';
			}

			if ($c['p_field'] == $s)
				switch ($c['contype']) {
					case 'p':
						$str .= '<img src="'. $misc->icon('PrimaryKey') .'" alt="[pk]" title="'. htmlentities($c['consrc'], ENT_QUOTES, 'UTF-8') .'" />';
					break;
					case 'f':
						$str .= '<a href="tblproperties.php?'. $misc->href ."&amp;table=". urlencode($c['f_table']) ."&amp;schema=". urlencode($c['f_schema']) ."\"><img src=\"".
							$misc->icon('ForeignKey') .'" alt="[fk]" title="'. htmlentities($c['consrc'], ENT_QUOTES, 'UTF-8') .'" /></a>';
					break;
					case 'u':
						$str .= '<img src="'. $misc->icon('UniqueConstraint') .'" alt="[uniq]" title="'. htmlentities($c['consrc'], ENT_QUOTES, 'UTF-8') .'" />';
					break;
					case 'c':
						$str .= '<img src="'. $misc->icon('CheckConstraint') .'" alt="[check]" title="'. htmlentities($c['consrc'], ENT_QUOTES, 'UTF-8') .'" /></a>';
				}
		}
		return $str;
	}

	// Callback function to modify the isemaj column content
	// It replaces the database value by an icon
	function renderIsEmaj($val) {
		global $misc, $lang;
		if ($val == 't') {
			$icon = $misc->icon('EmajIcon');
			$alt = $lang['stremajtrigger'];
			return "<img src=\"{$icon}\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		}
		return;
	}

	// Function to dynamicaly modify actions list for each trigger
	function triggerPre(&$rowdata, $actions) {

		// disable both buttons if the trigger is an E-Maj trigger, otherwise, disable the useless button
		if ($rowdata->fields('tgisautodisable') == 't') {
			$actions['autoDisableTrigger']['disable'] = true;
		} elseif ($rowdata->fields('tgisautodisable') == 'f') {
			$actions['noAutoDisableTrigger']['disable'] = true;
		} else {
			$actions['autoDisableTrigger']['disable'] = true;
			$actions['noAutoDisableTrigger']['disable'] = true;
		}
		return $actions;
	}

	/**
	 * Show the table's properties: E-Maj group owning the table, if any, list of columns in the table, list of triggers
	 */
	function doDefault($msg = '', $errMsg = '') {
		global $data, $conf, $misc, $lang, $emajdb;

		$misc->printHeader('table', 'table', 'properties');

		$misc->printMsg($msg, $errMsg);
		$misc->printTitle(sprintf($lang['strnamedtable'], $_REQUEST['schema'], $_REQUEST['table']));

		// Get table information
		$tdata = $data->getTable($_REQUEST['table']);
		// Get columns description
		$attrs = $emajdb->getColumns($_REQUEST['schema'], $_REQUEST['table']);
		// Get constraints keys
		$ck = $data->getConstraintsWithFields($_REQUEST['table']);
		// Get triggers
		$triggers = $emajdb->getTriggersTable($_REQUEST['schema'], $_REQUEST['table']);

		// Display the E-Maj properties, if any
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {

			$misc->printSubtitle($lang['stremajproperties']);

			$type = $emajdb->getEmajTypeTblSeq($_REQUEST['schema'], $_REQUEST['table']);

			if ($type == 'L') {
				echo "<p>{$lang['stremajlogtable']}</p>\n";
			} elseif ($type == 'E') {
				echo "<p>{$lang['stremajinternaltable']}</p>\n";
			} elseif ($type == 'U') {
				echo "<p>{$lang['strnotassignabletable']}</p>\n";
			} else {
				$prop = $emajdb->getRelationEmajProperties($_REQUEST['schema'], $_REQUEST['table']);

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
					'priority' => array(
						'title' => $lang['strpriority'],
						'field' => field('rel_priority'),
						'params'=> array('align' => 'center'),
					),
					'logdattsp' => array(
						'upper_title' => $lang['strtablespace'],
						'upper_title_colspan' => 2,
						'title' => $lang['strlogtables'],
						'field' => field('rel_log_dat_tsp'),
					),
					'logidxtsp' => array(
						'title' => $lang['strlogindexes'],
						'field' => field('rel_log_idx_tsp'),
					),
				);

				$misc->printTable($prop, $columns, $actions, 'tblproperties-emaj', $lang['strtblnogroupownership']);

				// Display the buttons corresponding to the available functions for the table.

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
							$navlinks['assign_table'] = array (
								'content' => $lang['strassign'],
								'attr'=> array (
									'href' => array (
										'url' => "tblproperties.php",
										'urlvars' => array(
											'action' => 'assign_tables',
											'schema' => $_REQUEST['schema'],
											'table' => $_REQUEST['table'],
										)
									)
								),
							);
						} else {
							// Already assigned to a tables group
							$prop->moveFirst();
							$group = $prop->fields['rel_group'];

							$navlinks['modify_table'] = array (
								'content' => $lang['strupdate'],
								'attr'=> array (
									'href' => array (
										'url' => "tblproperties.php",
										'urlvars' => array(
											'action' => 'modify_tables',
											'schema' => $_REQUEST['schema'],
											'table' => $_REQUEST['table'],
											'group' => $group,
										)
									)
								),
							);
							if ($nbGroups > 1) {
								$navlinks['move_table'] = array (
									'content' => $lang['strmove'],
									'attr'=> array (
										'href' => array (
											'url' => "tblproperties.php",
											'urlvars' => array(
												'action' => 'move_tables',
												'schema' => $_REQUEST['schema'],
												'table' => $_REQUEST['table'],
												'group' => $group,
											)
										)
									),
								);
							}
							$navlinks['remove_table'] = array (
								'content' => $lang['strremove'],
								'attr'=> array (
									'href' => array (
										'url' => "tblproperties.php",
										'urlvars' => array(
											'action' => 'remove_tables',
											'schema' => $_REQUEST['schema'],
											'table' => $_REQUEST['table'],
											'group' => $group,
										)
									)
								),
							);
						}

						$misc->printLinksList($navlinks, 'buttonslist');
					}
				}
			}

			echo "<hr/>\n";
		}

		// Display the table structure
		$misc->printSubtitle($lang['strtblstructure']);

		// Verify that the user has enough privileges to read the table
		$privilegeOk = $emajdb->hasSelectPrivilegeOnTable($_REQUEST['schema'], $_REQUEST['table']);

		if (! $privilegeOk) {
			echo $lang['strnograntontable'];
		} else {

			// Show comment, if any
			if ($tdata->fields['relcomment'] !== null)
				echo "<p>{$lang['strcommentlabel']}<span class=\"comment\">{$misc->printVal($tdata->fields['relcomment'])}</span></p>\n";

			// Display the table structure
			$columns = array(
				'num' => array(
					'title' => '#',
					'field' => field('attnum'),
					'params'=> array(
						'align' => 'center',
					),
					'filter'=> false,
				),
				'column' => array(
					'title' => $lang['strcolumn'],
					'field' => field('attname'),
				),
				'type' => array(
					'title' => $lang['strtype'],
					'field' => field('type'),
				),
				'notnull' => array(
					'title' => $lang['strnotnull'],
					'field' => field('attnotnull'),
					'type'  => 'callback',
					'params'=> array(
						'function' => 'renderIsNotNull',
						'align' => 'center',
					),
					'filter'=> false,
				),
				'valuationkind' => array(
					'upper_title' => $lang['strvaluation'],
					'upper_title_colspan' => 2,
					'title' => $lang['strtype'],
					'field' => field('valuationkind'),
					'type'  => 'callback',
					'params'=> array(
						'function' => 'renderValuationKind',
						'align' => 'center',
					),
					'filter'=> false,
					'sorter'=> false,
				),
				'expression' => array(
					'title' => $lang['strexpression'],
					'field' => field('expression'),
				),
				'keyprop' => array(
					'title' => $lang['strconstraints'],
					'field' => field('attname'),
					'type'  => 'callback',
					'params'=> array(
						'function' => 'renderConstraints',
						'keys' => $ck->getArray(),
						'align' => 'center',
					),
					'filter'=> false,
					'sorter'=> false,
				),
				'comment' => array(
					'title' => $lang['strcomment'],
					'field' => field('comment'),
					'type' => 'spanned',
					'params'=> array(
							'cliplen' => 32,
							'class' => 'tooltip right-aligned-tooltip',
							),
				),
			);

			$actions = array();

			$misc->printTable($attrs, $columns, $actions, 'tblproperties-columns', null, null, array('sorter' => true, 'filter' => true));

			echo "<hr/>\n";

			// Display the table triggers

			$misc->printSubtitle($lang['strtriggers']);

			$urlvars = $misc->getRequestVars();

			$columns = array(
				'tgrank' => array(
					'title' => $lang['strexecorder'],
					'field' => field('tgrank'),
					'params'=> array('align' => 'center'),
				),
				'tgname' => array(
					'title' => $lang['strtrigger'],
					'field' => field('tgname'),
				),
				'tglevel' => array(
					'title' => $lang['strlevel'],
					'field' => field('tglevel'),
					'params'=> array('align' => 'center'),
				),
				'tgevent' => array(
					'title' => $lang['strtriggeringevent'],
					'field' => field('tgevent'),
				),
				'tgfnct' => array(
					'title' => $lang['strcalledfunction'],
					'field' => field('tgfnct'),
				),
				'tgenabled' => array(
					'title' => $lang['strstate'],
					'field' => field('tgstate'),
				),
				'tgisemaj' => array(
					'title' => $lang['strisemaj'],
					'field' => field('tgisemaj'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderIsEmaj', 'align' => 'center'),
					'sorter_text_extraction' => 'img_alt',
				),
			);
			if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
				if ($emajdb->getNumEmajVersion() >= 30100) {			// version >= 3.1.0
					$columns = array_merge($columns, array(
						'emajisautodisable' => array(
							'title' => $lang['strisautodisable'],
							'field' => field('tgisautodisable'),
							'info'  => $lang['strisautodisablehelp'],
							'params'=> array(
								'map' => array('t' => 'ON', 'f' => 'OFF'),
								'align' => 'center'
							),
						),
						'actions' => array(
							'title' => $lang['stractions'],
						),
					));
				}
			}

			$actions = array();
			if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
				if ($emajdb->getNumEmajVersion() >= 30100) {			// version >= 3.1.0
					if ($emajdb->isEmaj_Adm()) {
						$actions = array_merge($actions, array(
							'noAutoDisableTrigger' => array(
								'content' => 'Manuel',
								'icon' => 'Off',
								'attr' => array (
									'href' => array (
										'url' => 'tblproperties.php',
										'urlvars' => array_merge($urlvars, array (
											'action' => 'no_auto_disable_trigger',
											'schema' => $_REQUEST['schema'],
											'table' => $_REQUEST['table'],
											'trigger' => field('tgname'),
										)))),
							),
							'autoDisableTrigger' => array(
								'content' => 'Auto',
								'icon' => 'On',
								'attr' => array (
									'href' => array (
										'url' => 'tblproperties.php',
										'urlvars' => array_merge($urlvars, array (
											'action' => 'auto_disable_trigger',
											'schema' => $_REQUEST['schema'],
											'table' => $_REQUEST['table'],
											'trigger' => field('tgname'),
										)))),
							),
						));
					}
				}
			}

			$misc->printTable($triggers, $columns, $actions, 'tblproperties-triggers', $lang['strnotriggerontable'], 'triggerPre', array('sorter' => true, 'filter' => false));
		}
	}

	/**
	 * Register the selected trigger as 'not to be automatically disabled at rollback'
	 * Then show the updated table properties
	 */
	function noAutoDisableTrigger() {
		global $lang, $emajdb;

		$nbTriggers = $emajdb->ignoreAppTrigger('ADD', $_REQUEST['schema'], $_REQUEST['table'], $_REQUEST['trigger']);

		if ($nbTriggers > 0) {
			doDefault(sprintf($lang['strtriggernoautook'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		} else {
			doDefault('',sprintf($lang['strtriggerprocerr'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		}
	}

	/**
	 * Register the selected trigger as 'to be automatically disabled at rollback'
	 * Then show the updated table properties
	 */
	function autoDisableTrigger() {
		global $lang, $emajdb;

		$nbTriggers = $emajdb->ignoreAppTrigger('REMOVE', $_REQUEST['schema'], $_REQUEST['table'], $_REQUEST['trigger']);

		if ($nbTriggers > 0) {
			doDefault(sprintf($lang['strtriggerautook'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		} else {
			doDefault('',sprintf($lang['strtriggerprocerr'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		}
	}

/********************************************************************************************************
 * Main piece of code
 *******************************************************************************************************/

	// Check that emaj and the table still exist.
	$misc->onErrorRedirect('table');

	$scripts = "<script src=\"js/schemas.js\"></script>";
	$misc->printHtmlHeader($lang['strtables'] . ' - ' . $_REQUEST['table'], $scripts);

	$misc->printBody();

	switch ($action) {
		case 'assign_tables';
			assign_tables();
			break;
		case 'assign_tables_ok':
			assign_tables_ok();
			break;
		case 'auto_disable_trigger':
			autoDisableTrigger();
			break;
		case 'move_tables';
			move_tables();
			break;
		case 'move_tables_ok':
			move_tables_ok();
			break;
		case 'modify_tables';
			modify_tables();
			break;
		case 'modify_tables_ok':
			modify_tables_ok();
			break;
		case 'no_auto_disable_trigger':
			noAutoDisableTrigger();
			break;
		case 'remove_tables';
			remove_tables();
			break;
		case 'remove_tables_ok':
			remove_tables_ok();
			break;
		default:
			doDefault();
			break;
	}

	$misc->printFooter();

?>
