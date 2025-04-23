<?php
	/*
	 * Manage the tables groups history
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

/********************************************************************************************************
 * Callback functions 
 *******************************************************************************************************/

	// Callback function to dynamicaly modify the Table/Sequence columns content
	// It replaces the database value by an icon representing either a table or a sequence
	function renderTblSeq($val) {
		global $misc, $lang;
		if ($val == 'r+') {							// regular table
			$icon = $misc->icon('Table');
			$alt = $lang['strtable'];
		} elseif ($val == 'S+') {					// sequence
			$icon = $misc->icon('Sequence');
			$alt = $lang['strsequence'];
		} elseif ($val == '!') {					// object declared in the emaj_group_def table but unknown in the catalog
			$icon = $misc->icon('ObjectNotFound');
			$alt = $lang['strunknownobject'];
		} else {									// unsupported type
			$icon = $misc->icon('ObjectNotFound');
			$alt = $lang['strunsupportedobject'];
		}
		return "<img src=\"{$icon}\" alt=\"{$alt}\" title=\"{$alt}\"/>";
	}

/********************************************************************************************************
 * Main functions displaying pages
 *******************************************************************************************************/

	/**
	 * Displays the list of tables and sequences owned by a group
	 */
	function show_content_group() {
		global $misc, $lang, $emajdb, $_reload_browser;

		if (! $emajdb->existsGroup($_REQUEST['group'])) {
			show_groups('', sprintf($lang['strgroupmissing'], htmlspecialchars($_REQUEST['group'])));
			$_reload_browser = true;
			return;
		}

		$misc->printHeader('emaj', 'emajgroup', 'emajcontent');

		$misc->printTitle(sprintf($lang['strgroupcontent'],htmlspecialchars($_REQUEST['group'])));

		$groupContent = $emajdb->getContentGroup($_REQUEST['group']);

		if ($groupContent->recordCount() < 1) {

			// The group is empty
			echo "<p>" . sprintf($lang['stremptygroup'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";

		} else {

			$columns = array(
				'type' => array(
					'title' => $lang['strtype'],
					'field' => field('relkind'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderTblSeq','align' => 'center'),
					'sorter_text_extraction' => 'img_alt',
					'filter'=> false,
				),
				'schema' => array(
					'title' => $lang['strschema'],
					'field' => field('rel_schema'),
					'url'   => "schemas.php?action=list_schemas&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'rel_schema'),
				),
				'tblseq' => array(
					'title' => $lang['strname'],
					'field' => field('rel_tblseq'),
					'url'	=> "redirect.php?{$misc->href}&amp;",
					'vars'  => array('subject' => 'rel_type' , 'schema' => 'rel_schema', 'table' => 'rel_tblseq', 'sequence' => 'rel_tblseq'),
				),
				'starttime' => array(
					'title' => $lang['strsince'],
					'field' => field('start_time'),
					'type' => 'spanned',
					'params'=> array(
						'dateformat' => $lang['stroldtimestampformat'],
						'locale' => $lang['applocale'],
						'class' => 'tooltip left-aligned-tooltip',
					),
					'sorter_text_extraction' => 'span_text',
					'filter'=> false,
				),
				'priority' => array(
					'title' => $lang['strpriority'],
					'field' => field('rel_priority'),
					'params'=> array('align' => 'center'),
				),
				'log_dat_tsp' => array(
					'upper_title' => $lang['strtablespace'],
					'upper_title_colspan' => 2,
					'title' => $lang['strlogtables'],
					'field' => field('rel_log_dat_tsp'),
				),
				'log_idx_tsp' => array(
					'title' => $lang['strlogindexes'],
					'field' => field('rel_log_idx_tsp'),
				),
				'log_table' => array(
					'title' => $lang['strcurrentlogtable'],
					'field' => field('full_log_table'),
				),
				'logsize' => array(
					'title' => $lang['strlogsize'],
					'field' => field('log_size'),
					'type' => 'spanned',
					'params'=> array(
						'spanseparator' => '|',
						'class' => 'tooltip right-aligned-tooltip',
					),
					'sorter_text_extraction' => 'span_text',
					'filter'=> false,
				),
			);

			$actions = array ();

			echo "<p></p>";
			$misc->printTable($groupContent, $columns, $actions, 'groupContent', null, null, array('sorter' => true, 'filter' => true));
		}
	}

/********************************************************************************************************
 * Main piece of code
 *******************************************************************************************************/

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	// Check that emaj and the group still exist.
	$misc->onErrorRedirect('emajgroup');

	$misc->printHtmlHeader($lang['strgroupsmanagement']);
	$misc->printBody();

	switch ($action) {
		case 'show_content_group':
			show_content_group();
			break;
		default:
			show_content_group();
	}

	$misc->printFooter();
?>
