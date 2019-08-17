<?php

	/*
	 * Manage the E-Maj tables groups configuration
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	// Function to dynamicaly modify actions list for tables
	function tblseqPre(&$rowdata, $actions) {
		// disable 'assign' if the table already belongs to a group
		if ($rowdata->fields['grpdef_group'] != NULL) {
			$actions['assign']['disable'] = true;
		} else {
		// otherwise, disable 'remove' and 'update'
			$actions['remove']['disable'] = true;
			$actions['update']['disable'] = true;
		// disable also 'assign' for unsupported object type
			if ($rowdata->fields['relkind'] != 'r+' and $rowdata->fields['relkind'] != 'S+') {
				$actions['assign']['disable'] = true;
			}
		};
		return $actions;
	}

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
			$alt = $lang['emajunknownobject'];
		} else {									// unsupported type
			$icon = $misc->icon('ObjectNotFound');
			$alt = $lang['emajunsupportedobject'];
		}
		return "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
	}

	// Callback function to dynamicaly modify the schema owner columns content
	// It generates a warning icon when the owner is not known (i.e. the object does not exist)
	function renderSchemaOwner($val) {
		global $misc, $lang;
		if ($val == '!') {
			return "<img src=\"".$misc->icon('ObjectNotFound')."\" alt=\"{$lang['emajunknownobject']}\" title=\"{$lang['emajunknownobject']}\" style=\"vertical-align:bottom;\" />";
		}
		return $val;
	}

	/**
	 * Define groups content
	 */
	function configure_groups($msg = '', $errMsg = '', $prevSchema = '') {
		global $misc, $lang, $emajdb;

		if (!isset($_REQUEST['appschema'])) $_REQUEST['appschema'] = $prevSchema;
		if (is_array($_REQUEST['appschema'])) $_REQUEST['appschema'] = $_REQUEST['appschema'][0];

		$misc->printHeader('database', 'database', 'emajconfiguregroups');

		$emajOK = $misc->checkEmajExtension();

		if ($emajOK) {
			$misc->printMsg($msg,$errMsg);

		// Schemas list
			$misc->printTitle($lang['emajappschemas']);

			$schemas = $emajdb->getSchemas();

			$columns = array(
				'schema' => array(
					'title' => $lang['strschema'],
					'field' => field('nspname'),
					'url'   => "emajgroupsconf.php?action=configure_groups&amp;back=define&amp;{$misc->href}&amp;",
					'vars'  => array('appschema' => 'nspname'),
				),
				'owner' => array(
					'title' => $lang['strowner'],
					'field' => field('nspowner'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderSchemaOwner')
				),
				'comment' => array(
					'title' => $lang['strcomment'],
					'field' => field('nspcomment'),
				),
			);

			$actions = array ();

			$misc->printTable($schemas, $columns, $actions, 'defineGroupSchemas', $lang['strnoschemas'], null, array('sorter' => true, 'filter' => true));

			echo "<hr>\n";

		// Tables and sequences for the selected schema, if any
			if (isset($_REQUEST['appschema']) && $_REQUEST['appschema'] != '') {

				$misc->printTitle(sprintf($lang['emajtblseqofschema'],$_REQUEST['appschema']));
				$tblseq = $emajdb->getTablesSequences($_REQUEST['appschema']);

				$columns = array(
					'type' => array(
						'title' => $lang['strtype'],
						'field' => field('relkind'),
						'type'	=> 'callback',
						'params'=> array('function' => 'renderTblSeq','align' => 'center'),
						'sorter_text_extraction' => 'img_alt',
						'filter'=> false,
					),
					'appschema' => array(
						'title' => $lang['strschema'],
						'field' => field('nspname'),
					),
					'tblseq' => array(
						'title' => $lang['strname'],
						'field' => field('relname'),
					),
					'actions' => array(
						'title' => $lang['stractions'],
					),
					'group' => array(
						'title' => $lang['emajgroup'],
						'field' => field('grpdef_group'),
					),
					'priority' => array(
						'title' => $lang['emajpriority'],
						'field' => field('grpdef_priority'),
						'params'=> array('align' => 'center'),
					));
				if ($emajdb->getNumEmajVersion() < 30100) {			// version < 3.1.0
					$columns = array_merge($columns, array(
						'logschemasuffix' => array(
							'title' => $lang['emajlogschemasuffix'],
							'field' => field('grpdef_log_schema_suffix'),
						),
						'emajnamesprefix' => array(
							'title' => $lang['emajnamesprefix'],
							'field' => field('grpdef_emaj_names_prefix'),
						),
					));
				}
				$columns = array_merge($columns, array(
					'logdattsp' => array(
						'title' => $lang['emajlogdattsp'],
						'field' => field('grpdef_log_dat_tsp'),
					),
					'logidxtsp' => array(
						'title' => $lang['emajlogidxtsp'],
						'field' => field('grpdef_log_idx_tsp'),
					),
					'owner' => array(
						'title' => $lang['strowner'],
						'field' => field('relowner'),
					),
					'tablespace' => array(
						'title' => $lang['strtablespace'],
						'field' => field('tablespace')
					),
					'comment' => array(
						'title' => $lang['strcomment'],
						'field' => field('relcomment'),
					),
				));

				$urlvars = $misc->getRequestVars();

				$actions = array(
					'multiactions' => array(
						'keycols' => array('appschema' => 'nspname', 'tblseq' => 'relname', 'group' => 'grpdef_group', 'type' => 'relkind'),
						'url' => "emajgroupsconf.php?back=define",
					),
					'assign' => array(
						'content' => $lang['emajassign'],
						'attr' => array (
							'href' => array (
								'url' => 'emajgroupsconf.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'assign_tblseq',
									'appschema' => field('nspname'),
									'tblseq' => field('relname'),
									'group' => field('grpdef_group'),
									'type' => field('relkind'),
								)))),
						'multiaction' => 'assign_tblseq',
					),
					'update' => array(
						'content' => $lang['strupdate'],
						'attr' => array (
							'href' => array (
								'url' => 'emajgroupsconf.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'update_tblseq',
									'type' => field('relkind'),
									'appschema' => field('nspname'),
									'tblseq' => field('relname'),
									'group' => field('grpdef_group'),
									'priority' => field('grpdef_priority'),
									'logschemasuffix' => field('grpdef_log_schema_suffix'),
									'emajnamesprefix' => field('grpdef_emaj_names_prefix'),
									'logdattsp' => field('grpdef_log_dat_tsp'),
									'logidxtsp' => field('grpdef_log_idx_tsp'),
								)))),
					),
					'remove' => array(
						'content' => $lang['emajremove'],
						'attr' => array (
							'href' => array (
								'url' => 'emajgroupsconf.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'remove_tblseq',
									'appschema' => field('nspname'),
									'tblseq' => field('relname'),
									'group' => field('grpdef_group'),
									'type' => field('relkind'),
								)))),
						'multiaction' => 'remove_tblseq',
					),
				);

				$misc->printTable($tblseq, $columns, $actions, 'defineGroupTblseq', $lang['strnotables'], 'tblseqPre', array('sorter' => true, 'filter' => true));
			}
		}
	}

	/**
	 * Prepare insert a table/sequence into a group: ask for properties and confirmation
	 */
	function assign_tblseq() {
		global $misc, $lang, $emajdb;

		// Test at least 1 table/sequence is to be processed
		if (empty($_REQUEST['tblseq']) && empty($_REQUEST['ma'])) {
			configure_groups($lang['emajspecifytblseqtoassign']);
			exit();
		}
		// Test all tables/sequences to process are not yet assigned to a group and have a valid type
		if (isset($_REQUEST['ma'])) {
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				if ($a['group'] != '') {
					configure_groups('', sprintf($lang['emajtblseqyetgroup'],$a['appschema'],$a['tblseq']), $a['appschema']);
					exit();
				}
				if ($a['type'] != 'r+' and $a['type'] != 'S+') {
					configure_groups('', sprintf($lang['emajtblseqbadtype'],$a['appschema'],$a['tblseq']), $a['appschema']);
					exit();
				}
			}
		}

		$misc->printHeader('database', 'database', 'emajconfiguregroups');

		$misc->printTitle($lang['emajassigntblseq']);

		// Get group names already known in emaj_group_def table
		$knownGroups = $emajdb->getKnownGroups();

		// Get log schema suffix already known in emaj_group_def table
		if ($emajdb->getNumEmajVersion() < 30100) {			// version < 3.1.0
			$knownSuffix = $emajdb->getKnownSuffix();
		}

		// Get tablespaces the current user can see
		$knownTsp = $emajdb->getKnownTsp();

		// Build the list of tables and sequences to processs and count them
		$nbTbl = 0; $nbSeq = 0;
		echo "<form action=\"emajgroupsconf.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"assign_tblseq_ok\" />\n";

		if (isset($_REQUEST['ma'])) {
		// multiple assign
			$fullList = "<ul>";
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				echo "<input type=\"hidden\" name=\"appschema[]\" value=\"", htmlspecialchars($a['appschema']), "\" />\n";
				echo "<input type=\"hidden\" name=\"tblseq[]\" value=\"", htmlspecialchars($a['tblseq']), "\" />\n";
				if ($a['type'] == 'r+') {
					$nbTbl++;
					$fullList .= "<li>" . sprintf($lang['emajthetable'],$a['appschema'],$a['tblseq']) . "</li>\n";
				} else {
					$nbSeq++;
					$fullList .= "<li>" . sprintf($lang['emajthesequence'],$a['appschema'],$a['tblseq']) . "</li>\n";
				}
			}
			$fullList .= "</ul>\n";
			echo "<p>{$lang['emajconfirmassigntblseq']}{$fullList}</p>\n";
		} else {

		// single assign
			echo "<input type=\"hidden\" name=\"appschema\" value=\"", htmlspecialchars($_REQUEST['appschema']), "\" />\n";
			echo "<input type=\"hidden\" name=\"tblseq\" value=\"", htmlspecialchars($_REQUEST['tblseq']), "\" />\n";
			if ($_REQUEST['type'] == 'r+') {
				$nbTbl++;
				$tblseqName = sprintf($lang['emajthetable'],$_REQUEST['appschema'],$_REQUEST['tblseq']);
			} else {
				$nbSeq++;
				$tblseqName = sprintf($lang['emajthesequence'],$_REQUEST['appschema'],$_REQUEST['tblseq']);
			}
			echo "<p>{$lang['emajassign']} {$tblseqName}</p>\n";
		}

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// group name
		echo "\t<div class=\"form-label required\">{$lang['emajgroup']}</div>\n";
		echo "\t<div class=\"form-input\">";
		echo "<input type=\"text\" name=\"group\" list=\"groupList\" required pattern=\"\S+.*\" value=\"\" placeholder='{$lang['emajrequiredfield']}' autocomplete=\"off\"/>";
		echo "</div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "\t<datalist id=\"groupList\">\n";
		if ($knownGroups->recordCount() > 0) {
			foreach($knownGroups as $r)
				echo "\t\t<option value=\"", htmlspecialchars($r['group_name']), "\">\n";
		}
		echo "\t</datalist>\n";

		// priority level (only for tables)
		if ($_REQUEST['type'] == 'r+') {
			echo "\t<div class=\"form-label\">{$lang['emajenterpriority']}</div>\n";
			echo "\t<div class=\"form-input\">";
			echo "<input type=\"number\" name=\"priority\" style=\"width:6em; text-align:right;\" min=\"0\" max=\"2147483647\" value=\"\" />";
			echo "</div>\n";
			echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajpriorityhelp']}\"/></div>\n";
		} else {
			echo "<input type=\"hidden\" name=\"priority\" value=\"\" />\n";
		}

		// log schema name suffix
		if ($emajdb->getNumEmajVersion() < 30100 && $nbTbl >= 1) {			// version < 3.1.0
			echo "\t<div class=\"form-label\">{$lang['emajenterlogschema']}</div>\n";
			echo "\t<div class=\"form-input\">";
			echo "<input type=\"text\" name=\"suffix\" list=\"suffixList\" value=\"\"/ autocomplete=\"off\">";
			echo "</div>\n";
			echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajlogschemahelp']}\"/></div>\n";
			echo "\t<datalist id=\"suffixList\">\n";
			if ($knownSuffix->recordCount() > 0) {
				foreach($knownSuffix as $r)
					echo "\t\t<option value=\"", htmlspecialchars($r['known_suffix']), "\">\n";
			}
			echo "\t</datalist>\n";
		} else {
			echo "<input type=\"hidden\" name=\"suffix\" value=\"\" />\n";
		}

		// objects name prefix (only for tables)
		if ($emajdb->getNumEmajVersion() < 30100 && $nbTbl == 1) {			// version < 3.1.0
			// the names prefix is accessible only for a single table assignment
			echo "\t<div class=\"form-label\">{$lang['emajenternameprefix']}</div>\n";
			echo "\t<div class=\"form-input\">";
			echo "<input type=\"text\" name=\"nameprefix\" value=\"\"/>";
			echo "</div>\n";
			echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajnameprefixhelp']}\"/></div>\n";
		} else {
			echo "<input type=\"hidden\" name=\"nameprefix\" value=\"\" />\n";
		}

		// log tablespace (only for tables)
		if ($nbTbl >= 1) {
			// data log tablespace
			echo "\t<div class=\"form-label\">{$lang['emajenterlogdattsp']}</div>\n";
			echo "\t<div class=\"form-input\">";
			echo "<input type=\"text\" name=\"logdattsp\" list=\"tspList\" value=\"\" autocomplete=\"off\"/>";
			echo "</div>\n";
			echo "\t<div class=\"form-comment\"></div>\n";
			// index log tablespace
			echo "\t<div class=\"form-label\">{$lang['emajenterlogidxtsp']}</div>\n";
			echo "\t<div class=\"form-input\">";
			echo "<input type=\"text\" name=\"logidxtsp\"  list=\"tspList\" value=\"\" autocomplete=\"off\"/>";
			echo "</div>\n";
			echo "\t<div class=\"form-comment\"></div>\n";
			echo "\t<datalist id=\"tspList\">\n";
			if ($knownTsp->recordCount() > 0) {
				foreach($knownTsp as $r)
					echo "\t\t<option value=\"", htmlspecialchars($r['spcname']), "\">\n";
			}
			echo "\t</datalist>\n";
		} else {
			echo "<input type=\"hidden\" name=\"logdattsp\" value=\"\" />\n";
			echo "<input type=\"hidden\" name=\"logidxtsp\" value=\"\" />\n";
		}

		echo"</div>\n";
		echo $misc->form;
		echo "<p><input type=\"submit\" name=\"assigntblseq\" value=\"{$lang['emajassign']}\" id=\"ok\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform table/sequence insertion into a tables group
	 */
	function assign_tblseq_ok() {
		global $lang, $data, $emajdb;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {configure_groups(); exit();}

		if (is_array($_POST['tblseq'])) {
		// multiple assignement
			$status = $data->beginTransaction();
			if ($status == 0) {
				for($i = 0; $i < sizeof($_POST['tblseq']); ++$i)
				{
					$status = $emajdb->assignTblSeq($_POST['appschema'][$i],$_POST['tblseq'][$i],$_POST['group'],
								$_POST['priority'], $_POST['suffix'], $_POST['nameprefix'], $_POST['logdattsp'], $_POST['logidxtsp']);
					if ($status != 0) {
						$data->rollbackTransaction();
						configure_groups('', $lang['emajmodifygrouperr']);
						return;
					}
				}
			}
			if($data->endTransaction() == 0)
				configure_groups($lang['emajmodifygroupok']);
			else
				configure_groups('', $lang['emajmodifygrouperr']);

		} else {

		// single assignement
			$status = $emajdb->assignTblSeq($_POST['appschema'],$_POST['tblseq'],$_POST['group'],
								$_POST['priority'], $_POST['suffix'], $_POST['nameprefix'], $_POST['logdattsp'], $_POST['logidxtsp']);
			if ($status == 0)
				configure_groups($lang['emajmodifygroupok']);
			else
				configure_groups('', $lang['emajmodifygrouperr']);
		}
	}

	/**
	 * Prepare update a table/sequence into a group: ask for properties and confirmation
	 */
	function update_tblseq() {
		global $misc, $lang, $emajdb;

		// Test at least 1 table/sequence is to be processed
		if (empty($_REQUEST['tblseq'])) {
			configure_groups($lang['emajspecifytblseqtoupdate']);
			exit();
		}
		// Test the table/sequence is already assign to a group
		if ($_REQUEST['group'] == '') {
			configure_groups('', sprintf($lang['emajtblseqnogroup'],$_REQUEST['appschema'],$_REQUEST['tblseq']), $_REQUEST['appschema']);
			exit();
		}

		$misc->printHeader('database', 'database', 'emajconfiguregroups');

		$misc->printTitle($lang['emajupdatetblseq']);

		// Get group names already known in emaj_group_def table
		$knownGroups = $emajdb->getKnownGroups();

		// Get log schema suffix already known in emaj_group_def table
		if ($emajdb->getNumEmajVersion() < 30100) {			// version < 3.1.0
			$knownSuffix = $emajdb->getKnownSuffix();
		}

		// Get tablespaces the current user can see
		$knownTsp = $emajdb->getKnownTsp();

		echo "<form action=\"emajgroupsconf.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"update_tblseq_ok\" />\n";

		echo "<input type=\"hidden\" name=\"appschema\" value=\"", htmlspecialchars($_REQUEST['appschema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tblseq\" value=\"", htmlspecialchars($_REQUEST['tblseq']), "\" />\n";
		echo "<input type=\"hidden\" name=\"groupold\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";

		if ($_REQUEST['type'] == 'r+') {
			$tblseqName = sprintf($lang['emajthetable'],$_REQUEST['appschema'],$_REQUEST['tblseq']);
		} else {
			$tblseqName = sprintf($lang['emajthesequence'],$_REQUEST['appschema'],$_REQUEST['tblseq']);
		}
		echo "<p>{$lang['strupdate']} {$tblseqName}</p>\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// group name
		echo "\t<div class=\"form-label required\">{$lang['emajgroup']}</div>\n";
		echo "\t<div class=\"form-input\">";
		echo "<input type=\"text\" name=\"groupnew\" list=\"groupList\" required pattern=\"\S+.*\" value=\"", htmlspecialchars($_REQUEST['group']), "\" placeholder='{$lang['emajrequiredfield']}' autocomplete=\"off\"/>\n";
		echo "</div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "\t<datalist id=\"groupList\">\n";
		if ($knownGroups->recordCount() > 0) {
			foreach($knownGroups as $r)
				echo "\t\t<option value=\"", htmlspecialchars($r['group_name']), "\">\n";
		}
		echo "\t</datalist>\n";

		// priority level (only for tables)
		if ($_REQUEST['type'] == 'r+') {
			echo "\t<div class=\"form-label\">{$lang['emajenterpriority']}</div>\n";
			echo "\t<div class=\"form-input\">";
			echo "<input type=\"number\" name=\"priority\" style=\"width:6em; text-align:right;\" min=\"0\" max=\"2147483647\" value=\"{$_REQUEST['priority']}\" />";
			echo "</div>\n";
			echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajpriorityhelp']}\"/></div>\n";
		} else {
			echo "<input type=\"hidden\" name=\"priority\" value=\"\" />\n";
		}

		// log schema name suffix (only for tables)
		if ($emajdb->getNumEmajVersion() < 30100 && $_REQUEST['type'] == 'r+') {			// version < 3.1.0
			echo "\t<div class=\"form-label\">{$lang['emajenterlogschema']}</div>\n";
			echo "\t<div class=\"form-input\">";
			echo "<input type=\"text\" name=\"suffix\" list=\"suffixList\" value=\"", htmlspecialchars($_REQUEST['logschemasuffix']), "\"/ autocomplete=\"off\">";
			echo "</div>\n";
			echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajlogschemahelp']}\"/></div>\n";
			echo "\t<datalist id=\"suffixList\">\n";
			if ($knownSuffix->recordCount() > 0) {
				foreach($knownSuffix as $r)
					echo "\t\t<option value=\"", htmlspecialchars($r['known_suffix']), "\">\n";
			}
			echo "\t</datalist>\n";
		} else {
			echo "<input type=\"hidden\" name=\"suffix\" value=\"\" />\n";
		}

		// objects name prefix (only for tables)
		if ($emajdb->getNumEmajVersion() < 30100 && $_REQUEST['type'] == 'r+') {			// version < 3.1.0
			// the names prefix is accessible only for a table
			echo "\t<div class=\"form-label\">{$lang['emajenternameprefix']}</div>\n";
			echo "\t<div class=\"form-input\">";
			echo "<input type=\"text\" name=\"nameprefix\" value=\"", htmlspecialchars($_REQUEST['emajnamesprefix']), "\"/>";
			echo "</div>\n";
			echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajnameprefixhelp']}\"/></div>\n";
		} else {
			echo "<input type=\"hidden\" name=\"nameprefix\" value=\"\" />\n";
		}

		// log tablespaces (only for tables)
		if ($_REQUEST['type'] == 'r+') {
			// data log tablespace
			echo "\t<div class=\"form-label\">{$lang['emajenterlogdattsp']}</div>\n";
			echo "\t<div class=\"form-input\">";
			echo "<input type=\"text\" name=\"logdattsp\" list=\"tspList\" value=\"", htmlspecialchars($_REQUEST['logdattsp']), "\" autocomplete=\"off\"/>";
			echo "</div>\n";
			echo "\t<div class=\"form-comment\"></div>\n";
			// index log tablespace
			echo "\t<div class=\"form-label\">{$lang['emajenterlogidxtsp']}</div>\n";
			echo "\t<div class=\"form-input\">";
			echo "<input type=\"text\" name=\"logidxtsp\"  list=\"tspList\" value=\"", htmlspecialchars($_REQUEST['logidxtsp']), "\" autocomplete=\"off\"/>";
			echo "</div>\n";
			echo "\t<div class=\"form-comment\"></div>\n";
			echo "\t<datalist id=\"tspList\">\n";
			if ($knownTsp->recordCount() > 0) {
				foreach($knownTsp as $r)
					echo "\t\t<option value=\"", htmlspecialchars($r['spcname']), "\">\n";
			}
			echo "\t</datalist>\n";
		} else {
			echo "<input type=\"hidden\" name=\"logdattsp\" value=\"\" />\n";
			echo "<input type=\"hidden\" name=\"logidxtsp\" value=\"\" />\n";
		}
		echo"</div>\n";

		echo $misc->form;
		echo "<p><input type=\"submit\" name=\"updatetblseq\" value=\"{$lang['strupdate']}\" id=\"ok\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform table/sequence insertion into a tables group
	 */
	function update_tblseq_ok() {
		global $lang, $data, $emajdb;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {configure_groups(); exit();}

		$status = $emajdb->updateTblSeq($_POST['appschema'],$_POST['tblseq'],$_POST['groupold'],$_POST['groupnew'],
							$_POST['priority'], $_POST['suffix'], $_POST['nameprefix'], $_POST['logdattsp'], $_POST['logidxtsp']);
		if ($status == 0)
			configure_groups($lang['emajmodifygroupok']);
		else
			configure_groups('', $lang['emajmodifygrouperr']);
	}

	/**
	 * Prepare remove a table/sequence from a group: ask for confirmation
	 */
	function remove_tblSeq() {
		global $misc, $lang;

		// Test at least 1 table/sequence is to be processed
		if (empty($_REQUEST['tblseq']) && empty($_REQUEST['ma'])) {
			configure_groups($lang['emajspecifytblseqtoremove']);
			exit();
		}
		// Test all tables/sequences to process are already assigned to a group
		if (isset($_REQUEST['ma'])) {
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				if ($a['group'] == '') {
					configure_groups('', sprintf($lang['emajtblseqnogroup'],$a['appschema'],$a['tblseq']), $a['appschema']);
					exit();
				}
			}
		}

		$misc->printHeader('database', 'database', 'emajconfiguregroups');

		$misc->printTitle($lang['emajremovetblseq']);

		$nbTbl = 0; $nbSeq = 0;
		echo "<form action=\"emajgroupsconf.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"remove_tblseq_ok\" />\n";

		if (isset($_REQUEST['ma'])) {
		// multiple removal
			$fullList = "<ul>";
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				echo "<input type=\"hidden\" name=\"appschema[]\" value=\"", htmlspecialchars($a['appschema']), "\" />\n";
				echo "<input type=\"hidden\" name=\"tblseq[]\" value=\"", htmlspecialchars($a['tblseq']), "\" />\n";
				echo "<input type=\"hidden\" name=\"group[]\" value=\"", htmlspecialchars($a['group']), "\" />\n";
				if ($a['type'] == 'r+') {
					$nbTbl++;
					$fullList .= "<li>" . sprintf($lang['emajthetable'],$a['appschema'],$a['tblseq']); 
				} else {
					$nbSeq++;
					$fullList .= "<li>" . sprintf($lang['emajthesequence'],$a['appschema'],$a['tblseq']);
				}
				$fullList .= " " . sprintf($lang['emajfromgroup'], htmlspecialchars($a['group'])) . "</li>\n";
			}
			$fullList .= "</ul>\n";
			echo "<p>{$lang['emajconfirmremovetblseq']}{$fullList}</p>\n";

		} else {

		// single removal
			echo "<input type=\"hidden\" name=\"appschema\" value=\"", htmlspecialchars($_REQUEST['appschema']), "\" />\n";
			echo "<input type=\"hidden\" name=\"tblseq\" value=\"", htmlspecialchars($_REQUEST['tblseq']), "\" />\n";
			echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
			if ($_REQUEST['type'] == 'r+') {
				$nbTbl++;
				$tblseqName = sprintf($lang['emajthetable'],$_REQUEST['appschema'],$_REQUEST['tblseq']);
			} else {
				$nbSeq++;
				$tblseqName = sprintf($lang['emajthesequence'],$_REQUEST['appschema'],$_REQUEST['tblseq']);
			}
			echo "<p>" . sprintf($lang['emajconfirmremove1tblseq'], $tblseqName, htmlspecialchars($_REQUEST['group'])) . "</p>\n";
		}

		echo $misc->form;
		echo "<input type=\"submit\" name=\"removetblseq\" value=\"{$lang['emajremove']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</form>\n";
	}

	/**
	 * Perform table/sequence removal from a tables group
	 */
	function remove_tblseq_ok() {
		global $lang, $data, $emajdb;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {configure_groups(); exit();}

		if (is_array($_POST['tblseq'])) {
		// multiple removal
			$status = $data->beginTransaction();
			if ($status == 0) {
				for($i = 0; $i < sizeof($_POST['tblseq']); ++$i)
				{
					$status = $emajdb->removeTblSeq($_POST['appschema'][$i],$_POST['tblseq'][$i],$_POST['group'][$i]);
					if ($status != 0) {
						$data->rollbackTransaction();
						$configure_groups('', $lang['emajmodifygrouperr']);
						return;
					}
				}
			}
			if($data->endTransaction() == 0)
				configure_groups($lang['emajmodifygroupok']);
			else
				configure_groups('', $lang['emajmodifygrouperr']);

		} else {
		// single removal
			$status = $emajdb->removeTblSeq($_POST['appschema'],$_POST['tblseq'],$_POST['group']);

			if ($status == 0)
				configure_groups($lang['emajmodifygroupok']);
			else
				configure_groups('', $lang['emajmodifygrouperr']);
		}
	}

	$misc->printHtmlHeader($lang['emajgroupsconfiguration']);
	$misc->printBody();

	switch ($action) {
		case 'configure_groups':
			configure_groups();
			break;
		case 'assign_tblseq';
			assign_tblseq();	
			break;
		case 'assign_tblseq_ok':
			assign_tblseq_ok();
			break;
		case 'update_tblseq';
			update_tblseq();	
			break;
		case 'update_tblseq_ok':
			update_tblseq_ok();
			break;
		case 'remove_tblseq';
			remove_tblseq();	
			break;
		case 'remove_tblseq_ok':
			remove_tblseq_ok();
			break;
		default:
			configure_groups();
	}

	$misc->printFooter();

?>
