<?php
require_once('./classes/Plugin.php');
include_once('./plugins/Emaj/classes/EmajDb.php');

class Emaj extends Plugin {

	/**
	 * Attributes
	 */
	protected $name = 'Emaj';
	protected $lang;
	protected $conf = array();
	protected $emajdb = null;
	protected $oldest_supported_emaj_version = '0.11.0';		// Oldest emaj version supported by the plugin = 0.11.0
	protected $oldest_supported_emaj_version_num = 1100;
	protected $previous_cumlogrows;								// used to compute accumulated updates in marks'table

/********************************************************************************************************
 * Functions linked to the plugin architecture
 *******************************************************************************************************/

	/**
	 * Constructor
	 * Call parent constructor, passing the language that will be used.
	 * @param $language Current phpPgAdmin language. If it was not found in the plugin, English will be used.
	 */
	function __construct($language) {

		/* loads $this->lang and $this->conf */
		parent::__construct($language);

		// instanciate early an EmajDb class
		$this->emajdb = new EmajDb();
	}

	function get_hooks() {
		$hooks = array(
			'head' => array('add_plugin_head'),
			'tabs' => array('add_plugin_tabs'),
			'trail' => array('add_plugin_trail')
		);
		return $hooks;
	}

	/**
	 * Add some code in page's head
	 */
    function add_plugin_head(&$args) {
		global $conf;

        $args['heads']['plugin_name'] = 
			"<script type=\"text/javascript\" src=\"plugins/Emaj/js/jquery.keyfilter-1.7.min.js\"></script>\n" .
			"<script type=\"text/javascript\" src=\"plugins/Emaj/js/jquery.tablesorter.min.js\"></script>\n" .
			"<script type=\"text/javascript\" src=\"plugins/Emaj/js/jquery.tablesorter.widgets.min.js\"></script>\n" .
			"<link rel=\"stylesheet\" href=\"plugins/Emaj/themes/{$conf['theme']}/tablesorter.css\" type=\"text/css\" />\n";
        return;
    }

	/**
	 * Insert the E-Maj tabs in the tabs structure
	 */
	function add_plugin_tabs(&$plugin_functions_parameters) {
		global $lang;

		$tabs = &$plugin_functions_parameters['tabs'];

		switch ($plugin_functions_parameters['section']) {
			case 'database':
				$tabs['emaj'] = array (
					'title' => 'E-Maj',
					'url' => 'plugin.php',
					'urlvars' => array(
						'plugin' => $this->name,
						'subject' => 'database',
						'action' => 'show_groups'
					),
					'hide' => !($this->emajdb->isEnabled()&&$this->emajdb->isAccessible()),
					'icon' => $this->icon('Emaj')
				);
				break;
			  break;
			case 'emaj':
				$tabs['emajenvir'] = array (
					'title' => $this->lang['emajenvir'],
					'url' => 'plugin.php',
					'urlvars' => array(
						'plugin' => $this->name,
						'subject' => 'emaj',
						'action' => 'show_emaj_envir'
					),
					'icon' => $this->icon('Emaj')
				);
				if ($this->emajdb->isEmaj_Adm()) {
					$tabs['emajconfiguregroups'] = array (
						'title' => $this->lang['emajgroupsconf'],
						'url' => 'plugin.php',
						'urlvars' => array(
							'plugin' => $this->name,
							'subject' => 'emaj',
							'action' => 'configure_groups'
						),
						'icon' => 'Admin'
					);
				}
				$tabs['emajgroups'] = array (
					'title' => $this->lang['emajgroups'],
					'url' => 'plugin.php',
					'urlvars' => array(
						'plugin' => $this->name,
						'subject' => 'emaj',
						'action' => 'show_groups'
					),
					'icon' => $this->icon('EmajGroup')
				);
				$tabs['emajmonitorrlbk'] = array (
					'title' => $this->lang['emajrlbkop'],
					'url' => 'plugin.php',
					'urlvars' => array(
						'plugin' => $this->name,
						'subject' => 'emaj',
						'action' => 'show_rollbacks'
					),
					'hide' => ($this->emajdb->getNumEmajVersion() < 10100),
					'icon' => $this->icon('EmajRollback')
				);
				break;
			case 'emajgroup':
				$tabs['emajgroupproperties'] = array (
					'title' => $lang['strproperties'],
					'url' => 'plugin.php',
					'urlvars' => array(
						'plugin' => $this->name,
						'subject' => 'emajgroups',
						'action' => 'show_group',
						'group' => $_REQUEST['group']
					),
					'icon' => 'Property'
				);
				$tabs['emajlogstat'] = array (
					'title' => $this->lang['emajlogstat'],
					'url' => 'plugin.php',
					'urlvars' => array(
						'plugin' => $this->name,
						'subject' => 'emajgroups',
						'action' => 'log_stat_group',
						'group' => $_REQUEST['group']
					),
					'icon' => $this->icon('EmajStat')
				);
				$tabs['emajcontent'] = array (
					'title' => $this->lang['emajcontent'],
					'url' => 'plugin.php',
					'urlvars' => array(
						'plugin' => $this->name,
						'subject' => 'emajgroups',
						'action' => 'show_content_group',
						'group' => $_REQUEST['group']
					),
					'icon' => 'Tablespace'
				);
				break;
		}
	}

	/**
	 * Insert the E-Maj trail in the trail structure
	 */
	function add_plugin_trail(&$plugin_functions_parameters) {
		global $misc; 

		$trail = &$plugin_functions_parameters['trail'];

		switch ($plugin_functions_parameters['section']) {
			case 'emaj':
				$url = array (
					'url' => 'plugin.php',
					'urlvars' => array (
						'plugin' => $this->name,
						'subject' => 'emaj',
						'action' => 'show_groups'
					)
				);
				$trail['emaj'] = array (
					'tittle' => 'E-Maj',
					'text' => 'E-Maj',
					'url' => $misc->getActionUrl($url, $_REQUEST, null, false),
					'icon' => $this->icon('Emaj')
				);
				break;
		}
	}

	/**
	 * This method returns the functions that will be used as actions.
	 */
	function get_actions() {
		$actions = array(
		'alter_group',
		'alter_group_ok',
		'alter_groups',
		'alter_groups_ok',
		'assign_tblseq',
		'assign_tblseq_ok',
		'call_sqledit',
		'comment_group',
		'comment_group_ok',
		'comment_mark_group',
		'comment_mark_group_ok',
		'consolidate_rollback',
		'consolidate_rollback_ok',
		'configure_groups',
		'create_group',
		'create_group_ok',
		'delete_before_mark',
		'delete_before_mark_ok',
		'delete_mark',
		'delete_mark_ok',
		'drop_group',
		'drop_group_ok',
		'filterrlbk',
		'log_stat_group',
		'protect_group',
		'protect_mark_group',
		'remove_tblseq',
		'remove_tblseq_ok',
		'rename_mark_group',
		'rename_mark_group_ok',
		'reset_group',
		'reset_group_ok',
		'rollback_group',
		'rollback_group_confirm_alter',
		'rollback_group_ok',
		'rollback_groups',
		'rollback_groups_confirm_alter',
		'rollback_groups_ok',
		'set_mark_group',
		'set_mark_group_ok',
		'set_mark_groups',
		'set_mark_groups_ok',
		'show_content_group',
		'show_emaj_envir',
		'show_group',
		'show_groups',
		'show_rollbacks',
		'start_group',
		'start_group_ok',
		'start_groups',
		'start_groups_ok',
		'stop_group',
		'stop_group_ok',
		'stop_groups',
		'stop_groups_ok',
		'tree',
		'update_tblseq',
		'update_tblseq_ok',
		'unprotect_group',
		'unprotect_mark_group',
		);
		return $actions;
	}

/********************************************************************************************************
 * Callback functions 
 *******************************************************************************************************/

	/**
	 * callback functions to define authorized actions on lists
	 */

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

	// Functions to dynamicaly modify actions list for each table group to display
	function loggingGroupPre(&$rowdata, $loggingActions) {
		global $emajdb;
		// disable the rollback button for audit_only groups
		$isGroupRollbackable = $this->emajdb->isGroupRollbackable($rowdata->fields['group_name']);
		if (isset($loggingActions['rollback_group']) && !$isGroupRollbackable) {
			$loggingActions['rollback_group']['disable'] = true;
		}
		if ($this->emajdb->getNumEmajVersion() >= 10300) {
			$isGroupProtected = $this->emajdb->isGroupProtected($rowdata->fields['group_name']);
		// disable the protect button for audit_only or protected groups
			if (isset($loggingActions['protect_group']) && (!$isGroupRollbackable || $isGroupProtected)) {
				$loggingActions['protect_group']['disable'] = true;
				$loggingActions['rollback_group']['disable'] = true;
			}
		// disable the unprotect button for audit_only or unprotected groups
			if (isset($loggingActions['unprotect_group']) && (!$isGroupRollbackable || !$isGroupProtected)) {
				$loggingActions['unprotect_group']['disable'] = true;
			}
		}
		return $loggingActions;
	}

	// Function to dynamicaly modify actions list for each mark
	function markPre(&$rowdata, $actions) {
		global $emajdb;

		// disable the rollback button if the mark is deleted
		if (isset($actions['rollbackgroup']) && $rowdata->fields['mark_state'] == 'DELETED') {
			$actions['rollbackgroup']['disable'] = true;
		}
		// disable the rollback button if a previous mark is protected
		if ($this->protected_mark_flag == 1) {
			$actions['rollbackgroup']['disable'] = true;
		}
		// disable the protect button if the mark is already protected
		if (isset($actions['protectmark']) && $rowdata->fields['mark_state'] != 'ACTIVE') {
			$actions['protectmark']['disable'] = true;
		}
		// disable the unprotect button if the mark is not protected
		if (isset($actions['unprotectmark']) && $rowdata->fields['mark_state'] != 'ACTIVE-PROTECTED') {
			$actions['unprotectmark']['disable'] = true;
		}
		// if the mark is protected, set the flag to disable the rollback button for next marks
		// (this is not done in SQL because windowing functions are not available with pg version 8.3-)
		if ($rowdata->fields['mark_state']== 'ACTIVE-PROTECTED') {
			$this->protected_mark_flag = 1;
		}
		// compute the cumulative number of log rows
		// (this is not done in SQL because windowing functions are not available with pg version 8.3-)
		$this->previous_cumlogrows = $this->previous_cumlogrows + $rowdata->fields['mark_logrows'];
		$rowdata->fields['mark_cumlogrows'] = $this->previous_cumlogrows;
		return $actions;
	}

	/**
	 * Render callback functions
	 */

	// Callback function to dynamicaly add an icon to each diagnostic message
	function renderDiagnostic($val) {
		global $misc;
		if (preg_match("/[Nn]o error /",$val)) {
			$icon = 'CheckConstraint';
		} else {
			$icon = 'CorruptedDatabase';
		}
		return "<img src=\"".$misc->icon($icon)."\" style=\"vertical-align:bottom;\" />" . $val;
	}

	// Callback function to dynamicaly modify the schema owner columns content
	// It generates a warning icon when the owner is not known (i.e. the object does not exist)
	function renderSchemaOwner($val) {
		global $misc;
		if ($val == '!') {
			return "<img src=\"".$misc->icon('ObjectNotFound')."\" alt=\"{$this->lang['emajunknownobject']}\" title=\"{$this->lang['emajunknownobject']}\" style=\"vertical-align:bottom;\" />";
		}
		return $val;
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
			$alt = $this->lang['emajunknownobject'];
		} else {									// unsupported type
			$icon = $misc->icon('ObjectNotFound');
			$alt = $this->lang['emajunsupportedobject'];
		}
		return "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
	}

	// Callback function to dynamicaly replace the group type from the database by one or two icons
	function renderGroupType($val) {
		global $misc;
		if ($val == 'ROLLBACKABLE') {
			$icon = $misc->icon(array($this->name,'EmajRollbackable'));
			$alt = $this->lang['emajrollbackable'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		} elseif ($val == 'AUDIT_ONLY') {
			$icon = $misc->icon(array($this->name,'EmajAuditOnly'));
			$alt = $this->lang['emajauditonly'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		} elseif ($val == 'ROLLBACKABLE-PROTECTED') {
			$icon = $misc->icon(array($this->name,'EmajRollbackable'));
			$alt = $this->lang['emajrollbackable'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
			$icon = $misc->icon(array($this->name,'EmajPadlock'));
			$alt = $this->lang['emajprotected'];
			$img .= "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		}
		return $img;
	}

	// Callback function to dynamicaly modify the group state column content
	// It replaces the database value by an icon
	function renderGroupState($val) {
		global $misc;
		if ($val == 'IDLE') {
			$icon = $misc->icon(array($this->name,'EmajIdle'));
			$alt = $this->lang['emajidle'];
		} else {
			$icon = $misc->icon(array($this->name,'EmajLogging'));
			$alt = $this->lang['emajlogging'];
		}
		return "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
	}

	// Callback function to dynamicaly translate a boolean column into the user's language
	function renderBoolean($val) {
		global $lang;
		return $val == 't' ? $lang['stryes'] : $lang['strno'];
	}

	// Callback function to dynamicaly translate a boolean column into an icon
	function renderBooleanIcon($val) {
		global $misc;
		if ($val == 't') {
			$icon = 'CheckConstraint';
		} else {
			$icon = 'Delete';
		}
		return "<img src=\"".$misc->icon($icon)."\" style=\"vertical-align:bottom;\" />";
	}

	// Callback function to dynamicaly modify the mark state column content
	// It replaces the database value by an icon
	function renderMarkState($val) {
		global $misc;
		if ($val == 'ACTIVE') {
			$icon = $misc->icon(array($this->name,'EmajMark'));
			$alt = $this->lang['emajactive'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		} elseif ($val == 'DELETED') {
			$icon = $misc->icon('Delete');
			$alt = $this->lang['emajdeleted'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		} elseif ($val == 'ACTIVE-PROTECTED') {
			$icon = $misc->icon(array($this->name,'EmajMark'));
			$alt = $this->lang['emajactive'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
			$icon = $misc->icon(array($this->name,'EmajPadlock'));
			$alt = $this->lang['emajprotected'];
			$img .= "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		}
		return $img;
	}

/********************************************************************************************************
 * Main functions displaying pages
 *******************************************************************************************************/

	/**
	 * Show E-Maj environment characteristics
	 */
	function show_emaj_envir() {
		global $misc, $lang;

		$this->printPageHeader('emaj','emajenvir');

		$emajOK = $this->printEmajHeader("action=show_emaj_envir",$this->lang['emajenvironment']);

		if ($emajOK) {

			// General characteristics of the E-Maj environment
			echo "<h3>{$this->lang['emajcharacteristics']}</h3>\n";
			echo "<p>{$this->lang['emajversion']}{$this->emajdb->getEmajVersion()}</p>\n";
			if ($this->emajdb->isEmaj_Adm()) {
				echo "<p>".sprintf($this->lang['emajdiskspace'],$this->emajdb->getEmajSize())."</p>\n";
			}

			// E-Maj environment checking
			echo "<hr/>\n";
			echo "<h3>{$this->lang['emajchecking']}</h3>\n";

			$messages = $this->emajdb->checkEmaj();

			$columns = array(
				'message' => array(
					'title' => $this->lang['emajdiagnostics'],
					'field' => field('emaj_verify_all'),
					'type'	=> 'callback',
					'params'=> array('function' => array($this, 'renderDiagnostic'))
				),
			);

			$actions = array ();

//			$misc->printTable($messages, $columns, $actions, 'checks');
			$this->printTable($messages, $columns, $actions, 'checks');
		}

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Define groups content
	 */
	function configure_groups($msg = '', $errMsg = '') {
		global $misc, $lang;

		$this->printPageHeader('emaj','emajconfiguregroups');

		if (!isset($_REQUEST['appschema'])) $_REQUEST['appschema'] = '';
		if (is_array($_REQUEST['appschema'])) $_REQUEST['appschema'] = $_REQUEST['appschema'][0];

		$emajOK = $this->printEmajHeader("action=configure_groups&amp;appschema=".urlencode($_REQUEST['appschema']),$this->lang['emajgroupsconfiguration']);

		if ($emajOK) {
			$this->printMsg($msg,$errMsg);

		// Schemas list
			echo "<h3>{$this->lang['emajschemaslist']}</h3>\n";

			$schemas = $this->emajdb->getSchemas();

			$columns = array(
				'schema' => array(
					'title' => $lang['strschema'],
					'field' => field('nspname'),
					'url'   => "plugin.php?plugin={$this->name}&amp;action=configure_groups&amp;back=define&amp;{$misc->href}&amp;",
					'vars'  => array('appschema' => 'nspname'),
				),
				'owner' => array(
					'title' => $lang['strowner'],
					'field' => field('nspowner'),
					'type'	=> 'callback',
					'params'=> array('function' => array($this, 'renderSchemaOwner'))
				),
				'comment' => array(
					'title' => $lang['strcomment'],
					'field' => field('nspcomment'),
				),
			);

			$actions = array ();

			echo "<div id=\"schemasTable\">\n";
//			$misc->printTable($schemas, $columns, $actions, 'defineGroupSchemas', $lang['strnoschemas']);
			$this->printTable($schemas, $columns, $actions, 'defineGroupSchemas', $lang['strnoschemas']);
			echo "</div>\n";

			// activate tablesorter script
			echo "<script type=\"text/javascript\">
				$(document).ready(function() {
					$(\"#schemasTable table\").addClass('tablesorter');
					$(\"#schemasTable table\").tablesorter(
						{	
							emptyTo: 'none',
							widgets: [\"zebra\"],
							widgetOptions: {
								zebra : [ \"data1\", \"data2\" ],
								filter_hideFilters : true,
								stickyHeaders : 'tablesorter-stickyHeader', 
							},
						})
					});
				</script>";
			echo "<hr>\n";

		// Tables and sequences for the selected schema, if any
			if (isset($_REQUEST['appschema']) && $_REQUEST['appschema'] != '') {

				echo "<h3>".sprintf($this->lang['emajtblseqofschema'],$_REQUEST['appschema'])."</h3>\n";
				$tblseq = $this->emajdb->getTablesSequences($_REQUEST['appschema']);

				$columns = array(
					'type' => array(
						'title' => $lang['strtype'],
						'field' => field('relkind'),
						'type'	=> 'callback',
						'params'=> array('function' => array($this, 'renderTblSeq'),'align' => 'center')
					),
					'appschema' => array(
						'title' => $lang['strschema'],
						'field' => field('nspname'),
					),
					'tblseq' => array(
						'title' => $lang['strname'],
						'field' => field('relname'),
					),
					'group' => array(
						'title' => $this->lang['emajgroup'],
						'field' => field('grpdef_group'),
					),
					'priority' => array(
						'title' => $this->lang['emajpriority'],
						'field' => field('grpdef_priority'),
						'params'=> array('align' => 'center'),
					),
				);
				if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
					$columns = array_merge($columns, array(
						'logschemasuffix' => array(
							'title' => $this->lang['emajlogschemasuffix'],
							'field' => field('grpdef_log_schema_suffix'),
						),
					));
				};
				if ($this->emajdb->getNumEmajVersion() >= 10200) {			// version >= 1.2.0
					$columns = array_merge($columns, array(
						'emajnamesprefix' => array(
							'title' => $this->lang['emajnamesprefix'],
							'field' => field('grpdef_emaj_names_prefix'),
						),
					));
				};
				if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
					$columns = array_merge($columns, array(
						'logdattsp' => array(
							'title' => $this->lang['emajlogdattsp'],
							'field' => field('grpdef_log_dat_tsp'),
						),
						'logidxtsp' => array(
							'title' => $this->lang['emajlogidxtsp'],
							'field' => field('grpdef_log_idx_tsp'),
						),
					));
				};
				$columns = array_merge($columns, array(
					'actions' => array(
						'title' => $lang['stractions'],
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
						'url' => "plugin.php?plugin={$this->name}&amp;back=define",
					),
					'assign' => array(
						'content' => $this->lang['emajassign'],
						'attr' => array (
							'href' => array (
								'url' => 'plugin.php',
								'urlvars' => array_merge($urlvars, array (
									'plugin' => $this->name,
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
								'url' => 'plugin.php',
								'urlvars' => array_merge($urlvars, array (
									'plugin' => $this->name,
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
						'content' => $this->lang['emajremove'],
						'attr' => array (
							'href' => array (
								'url' => 'plugin.php',
								'urlvars' => array_merge($urlvars, array (
									'plugin' => $this->name,
									'action' => 'remove_tblseq',
									'appschema' => field('nspname'),
									'tblseq' => field('relname'),
									'group' => field('grpdef_group'),
								)))),
						'multiaction' => 'remove_tblseq',
					),
				);

				echo "<div id=\"tablesSeqTable\">\n";
//				$misc->printTable($tblseq, $columns, $actions, 'defineGroupTblseq', $lang['strnotables'],'tablePre');
				$this->printTable($tblseq, $columns, $actions, 'defineGroupTblseq', $lang['strnotables'],array($this,'tblseqPre'));
				echo "</div>\n";

				// activate tablesorter script
				echo "<script type=\"text/javascript\">
					$(document).ready(function() {
						$(\"#tablesSeqTable table\").addClass('tablesorter');
						$(\"#tablesSeqTable table\").tablesorter(
							{
							textExtraction: { 
								1: function(s) {
									if($(s).find('img').length == 0) return $(s).text();
									return $(s).find('img').attr('alt');
								}},
							headers: { 	0: { sorter: false, filter: false },
										1: { filter: false },
										2: { sorter: false, filter: false },
										9: { sorter: false, filter: false } },
							emptyTo: 'none',
							widgets: [\"zebra\", \"filter\"],
							widgetOptions: {
								zebra : [ \"data1\", \"data2\" ],
								filter_hideFilters : true,
								filter_functions : {  4: true, 6: true, 7: true, 8: true, 11: true, 12: true },
								stickyHeaders : 'tablesorter-stickyHeader', 
								},
							}
						);
						// disable filters on all but first actions columns
						$('#tablesSeqTable input[data-column=\"10\"]').addClass(\"disabled\");
						$('#tablesSeqTable input[data-column=\"10\"]').attr(\"disabled\",\"\");
					});
					</script>";
			}
		}

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Show list of created emaj groups
	 */
	function show_groups($msg = '', $errMsg = '') {
		global $lang, $misc;

		$this->printPageHeader('emaj','emajgroups');

		$emajOK = $this->printEmajHeader("action=show_groups",$this->lang['emajgrouplist']);

		if ($emajOK) {
			$this->printMsg($msg,$errMsg);

			$idleGroups = $this->emajdb->getIdleGroups();
			$loggingGroups = $this->emajdb->getLoggingGroups();

			$columns = array(
				'group' => array(
					'title' => $this->lang['emajgroup'],
					'field' => field('group_name'),
					'url'   => "plugin.php?plugin={$this->name}&amp;action=show_group&amp;{$misc->href}&amp;",
					'vars'  => array('group' => 'group_name'),
				),
				'creationdatetime' => array(
					'title' => $this->lang['emajcreationdatetime'],
					'field' => field('creation_datetime'),
					'params'=> array('align' => 'center'),
				),
				'nbtbl' => array(
					'title' => $this->lang['emajnbtbl'],
					'field' => field('group_nb_table'),
					'type'  => 'numeric'
				),
				'nbseq' => array(
					'title' => $this->lang['emajnbseq'],
					'field' => field('group_nb_sequence'),
					'type'  => 'numeric'
				),
				'rollbackable' => array(
					'title' => $lang['strtype'],
					'field' => field('group_type'),
					'type'	=> 'callback',
					'params'=> array(
							'function' => array($this, 'renderGroupType'),
							'align' => 'center'
							)
				),
			);
			$columns = array_merge($columns, array(
				'nbmark' => array(
					'title' => $this->lang['emajnbmark'],
					'field' => field('nb_mark'),
					'type'  => 'numeric'
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
				'comment' => array(
					'title' => $lang['strcomment'],
					'field' => field('abbr_comment'),
				),
			));

			$urlvars = $misc->getRequestVars();

			$loggingActions = array(
				'show_group' => array(
					'content' => $this->lang['emajdetail'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'show_group',
								'back' => 'list',
								'group' => field('group_name'),
							)))),
				),
			);
			if ($this->emajdb->isEmaj_Adm()) {
				$loggingActions = array_merge($loggingActions, array(
				'multiactions' => array(
					'keycols' => array('group' => 'group_name'),
					'url' => "plugin.php?plugin={$this->name}&amp;back=list",
					'default' => 'set_mark_group',
				),
				'stop_group' => array(
					'content' => $lang['strstop'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'stop_group',
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'stop_groups',
				),
				'set_mark_group' => array(
					'content' => $this->lang['emajsetmark'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'set_mark_group',
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'set_mark_groups',
				),
				'rollback_group' => array(
					'content' => $this->lang['emajrlbk'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'rollback_group',
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'rollback_groups',
				)));
			};
			if ($this->emajdb->isEmaj_Adm() && $this->emajdb->getNumEmajVersion() >= 10300) {
				$loggingActions = array_merge($loggingActions, array(
				'protect_group' => array(
					'content' => $this->lang['emajprotect'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'protect_group',
								'back' => 'list',
								'group' => field('group_name'),
							))))
					),
				'unprotect_group' => array(
					'content' => $this->lang['emajunprotect'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'unprotect_group',
								'back' => 'list',
								'group' => field('group_name'),
							))))
					),
				));
			};
			if ($this->emajdb->isEmaj_Adm() && $this->emajdb->getNumEmajVersion() >= 20100) {	// version >= 2.1.0
				$loggingActions = array_merge($loggingActions, array(
					'alter_group' => array(
						'content' => $lang['stralter'],
						'attr' => array (
							'href' => array (
								'url' => 'plugin.php',
								'urlvars' => array_merge($urlvars, array (
									'plugin' => $this->name,
									'action' => 'alter_group',
									'back' => 'list',
									'group' => field('group_name'),
							))))
					),
				));
				if ($this->emajdb->getNumEmajVersion() >= 20100) {	// version >= 2.1.0
						$loggingActions['alter_group']['multiaction'] = 'alter_groups';
				}
			};
			if ($this->emajdb->isEmaj_Adm()) {
				$loggingActions = array_merge($loggingActions, array(
				'comment_group' => array(
					'content' => $this->lang['emajsetcomment'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'comment_group',
								'back' => 'list',
								'group' => field('group_name'),
							))))
				),
				));
			};

			$idleActions = array(
				'show_group' => array(
					'content' => $this->lang['emajdetail'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'show_group',
								'back' => 'list',
								'group' => field('group_name'),
							))))
				),
			);
			if ($this->emajdb->isEmaj_Adm()) {
				$idleActions = array_merge($idleActions, array(
				'multiactions' => array(
					'keycols' => array('group' => 'group_name'),
					'url' => "plugin.php?plugin={$this->name}&amp;back=list",
					'default' => 'start_group',
				),
				'start_group' => array(
					'content' => $lang['strstart'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'start_group',
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'start_groups',
				),
				'reset_group' => array(
					'content' => $lang['strreset'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'reset_group',
								'back' => 'list',
								'group' => field('group_name'),
							))))
				),
				'drop_group' => array(
					'content' => $lang['strdrop'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'drop_group',
								'back' => 'list',
								'group' => field('group_name'),
							))))
				),
				));
			};
			if ($this->emajdb->isEmaj_Adm() && $this->emajdb->getNumEmajVersion() >= 10000) {	// version >= 1.0.0
				$idleActions = array_merge($idleActions, array(
					'alter_group' => array(
						'content' => $lang['stralter'],
						'attr' => array (
							'href' => array (
								'url' => 'plugin.php',
								'urlvars' => array_merge($urlvars, array (
									'plugin' => $this->name,
									'action' => 'alter_group',
									'back' => 'list',
									'group' => field('group_name'),
							))))
					),
				));
				if ($this->emajdb->getNumEmajVersion() >= 20100) {	// version >= 2.1.0
						$idleActions['alter_group']['multiaction'] = 'alter_groups';
				}
			};
			if ($this->emajdb->isEmaj_Adm()) {
				$idleActions = array_merge($idleActions, array(
				'comment_group' => array(
					'content' => $this->lang['emajsetcomment'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'comment_group',
								'back' => 'list',
								'group' => field('group_name'),
							))))
				),
			));
			};

			echo "<h3>{$this->lang['emajlogginggroups']}:</h3>\n";

			echo "<div id=\"loggingGroupsTable\">\n";
//			$misc->printTable($loggingGroups, $columns, $loggingActions, 'loggingGroups', $this->lang['emajnologginggroup']
			$this->printTable($loggingGroups, $columns, $loggingActions, 'loggingGroups', $this->lang['emajnologginggroup'], array($this, 'loggingGroupPre'));
			echo "</div>\n";

			// activate tablesorter script
			echo "<script type=\"text/javascript\">
				$(document).ready(function() {
					$(\"#loggingGroupsTable table\").addClass('tablesorter');
					$(\"#loggingGroupsTable table\").tablesorter(
						{
						headers: {
							0: { sorter: false, filter: false },
							5: { sorter: false },
							7: { sorter: false, filter: false },
							8: { filter: false },
							},
						emptyTo: 'none',
						widgets: [\"zebra\", \"filter\"],
						widgetOptions: {
							zebra : [ \"data1\", \"data2\" ],
							filter_hideFilters : true,
							filter_functions : { 5: true },
							stickyHeaders : 'tablesorter-stickyHeader', 
							},
						}
					)
					// disable filters on all but first actions columns
					$('#loggingGroupsTable input[data-column=\"8\"]').addClass(\"disabled\");
					$('#loggingGroupsTable input[data-column=\"8\"]').attr(\"disabled\",\"\");
					$('#loggingGroupsTable input[data-column=\"9\"]').addClass(\"disabled\");
					$('#loggingGroupsTable input[data-column=\"9\"]').attr(\"disabled\",\"\");
					$('#loggingGroupsTable input[data-column=\"10\"]').addClass(\"disabled\");
					$('#loggingGroupsTable input[data-column=\"10\"]').attr(\"disabled\",\"\");
					$('#loggingGroupsTable input[data-column=\"11\"]').addClass(\"disabled\");
					$('#loggingGroupsTable input[data-column=\"11\"]').attr(\"disabled\",\"\");
					$('#loggingGroupsTable input[data-column=\"12\"]').addClass(\"disabled\");
					$('#loggingGroupsTable input[data-column=\"12\"]').attr(\"disabled\",\"\");
					$('#loggingGroupsTable input[data-column=\"13\"]').addClass(\"disabled\");
					$('#loggingGroupsTable input[data-column=\"13\"]').attr(\"disabled\",\"\");
				});
				</script>";

			echo "<hr>";
			echo "<h3>{$this->lang['emajidlegroups']}:</h3>\n";

			echo "<div id=\"idleGroupsTable\">\n";
//			$misc->printTable($idleGroups, $columns, $idleActions, 'idleGroups', $this->lang['emajnoidlegroup']);
			$this->printTable($idleGroups, $columns, $idleActions, 'idleGroups', $this->lang['emajnoidlegroup']);
			echo "</div>\n";

			// activate tablesorter script
			echo "<script type=\"text/javascript\">
				$(document).ready(function() {
					$(\"#idleGroupsTable table\").addClass('tablesorter');
					$(\"#idleGroupsTable table\").tablesorter(
						{
						headers: {
							0: { sorter: false, filter: false },
							7: { sorter: false, filter: false },
							8: { filter: false },
							},
						emptyTo: 'none',
						widgets: [\"zebra\", \"filter\"],
						widgetOptions: {
							zebra : [ \"data1\", \"data2\" ],
							filter_hideFilters : true,
							filter_functions : { 5: true },
							stickyHeaders : 'tablesorter-stickyHeader', 
							},
						}
					)
					// disable filters on all but first actions columns
					$('#idleGroupsTable input[data-column=\"8\"]').addClass(\"disabled\");
					$('#idleGroupsTable input[data-column=\"8\"]').attr(\"disabled\",\"\");
					$('#idleGroupsTable input[data-column=\"9\"]').addClass(\"disabled\");
					$('#idleGroupsTable input[data-column=\"9\"]').attr(\"disabled\",\"\");
					$('#idleGroupsTable input[data-column=\"10\"]').addClass(\"disabled\");
					$('#idleGroupsTable input[data-column=\"10\"]').attr(\"disabled\",\"\");
					$('#idleGroupsTable input[data-column=\"11\"]').addClass(\"disabled\");
					$('#idleGroupsTable input[data-column=\"11\"]').attr(\"disabled\",\"\");
					$('#idleGroupsTable input[data-column=\"12\"]').addClass(\"disabled\");
					$('#idleGroupsTable input[data-column=\"12\"]').attr(\"disabled\",\"\");
				});
				</script>";

			echo "<hr>";

			// get groups name known in emaj_group_def table but not yet created (i.e. not known in emaj_group table)
			// for emaj_adm role only
			if ($this->emajdb->isEmaj_Adm()) {
				$newGroups = $this->emajdb->getNewGroups();
				if ($newGroups->recordCount() > 0) {

				// form used to create a new group

					echo "<form id=\"createGroup_form\" action=\"plugin.php?plugin={$this->name}&amp;action=create_group&amp;back=list&amp;{$misc->href}\"";
					echo "  method=\"post\" enctype=\"multipart/form-data\">\n";
					echo "<table>\n";
					echo "<tr>\n";
					echo "<th class=\"data\" style=\"text-align: left\" colspan=\"2\">{$this->lang['emajcreategroup']}</th>\n";
					echo "</tr>\n";
					echo "<tr>\n";
					echo "<td class=\"data1\">\n";
					echo "\t<select name=\"group\">\n";
					foreach($newGroups as $r)
						echo "\t\t<option value=\"",htmlspecialchars($r['group_name']),"\">",htmlspecialchars($r['group_name']),"</option>\n";
					echo "\t</select>\n";
					echo "<input type=\"submit\" value=\"{$lang['strcreate']}\" />\n";
					echo "</td>\n";
					echo "</tr>\n";
					echo "</table>\n";
					echo '</form>';
				}
			}
		}

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Display the status of past and in progress rollback operations
	 */
	function show_rollbacks($msg = '', $errMsg = '') {
		global $lang, $misc;

		// insert javascript for automatic refresh of marks list
//		$refreshTime = 10000;				// refresh time = 10 seconds (not linked to $conf['ajax_refresh'] parameter)
//		echo "<script src=\"js/emaj.js\" type=\"text/javascript\"></script>\n";
//		echo "<script type=\"text/javascript\">\n";
//		echo "\tvar Emaj = {\n";
//		echo "\tajax_time_refresh: {$refreshTime},\n";
//		echo "\tstr_start: {text:'{$lang['strstart']}',icon: '". $misc->icon('Execute') ."'},\n";
//		echo "\tstr_stop: {text:'{$lang['strstop']}',icon: '". $misc->icon('Stop') ."'},\n";
//		echo "\tload_icon: '". $misc->icon('Loading') ."',\n";
//		echo "\tserver:'{$_REQUEST['server']}',\n";
//		echo "\tdbname:'{$_REQUEST['database']}',\n";
//		echo "\tgroup:'{$_REQUEST['group']}',\n";
//		echo "\taction:'refresh_show_group',\n";
//		echo "\terrmsg: '". str_replace("'", "\'", $lang['strconnectionfail']) ."'\n";
//		echo "\t};\n";
//		echo "</script>\n";

		$this->printPageHeader('emaj','emajmonitorrlbk');

		$emajOK = $this->printEmajHeader("action=show_rollbacks",$this->lang['emajrlbkoperations']);

		if ($emajOK) {
			$this->printMsg($msg,$errMsg);

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
					'title' => $this->lang['emajrlbkid'],
					'field' => field('rlbk_id'),
					'params'=> array('align' => 'right'),
				),
				'rlbkGroups' => array(
					'title' => $this->lang['emajgroups'],
					'field' => field('rlbk_groups_list'),
				),
				'rlbkStatus' => array(
					'title' => $this->lang['emajstate'],
					'field' => field('rlbk_status'),
				),
				'rlbkStartDateTime' => array(
					'title' => $this->lang['emajrlbkstart'],
					'field' => field('rlbk_start_datetime'),
					'params'=> array('align' => 'center'),
				),
				'rlbkElapse' => array(
					'title' => $this->lang['emajcurrentduration'],
					'field' => field('rlbk_current_elapse'),
					'params'=> array('align' => 'center'),
				),
				'rlbkRemaining' => array(
					'title' => $this->lang['emajestimremaining'],
					'field' => field('rlbk_remaining'),
					'params'=> array('align' => 'center'),
				),
				'rlbkCompletionPct' => array(
					'title' => $this->lang['emajpctcompleted'],
					'field' => field('rlbk_completion_pct'),
					'params'=> array('align' => 'right'),
				),
				'rlbkMark' => array(
					'title' => $this->lang['emajtargetmark'],
					'field' => field('rlbk_mark'),
				),
				'rlbkMarkDateTime' => array(
					'title' => $this->lang['emajmarksetat'],
					'field' => field('rlbk_mark_datetime'),
				),
				'isLogged' => array(
					'title' => $this->lang['emajislogged'],
					'field' => field('rlbk_is_logged'),
					'type'	=> 'callback',
					'params'=> array('function' => array($this,'renderBoolean'),'align' => 'center')
				),
				'rlbkNbSession' => array(
					'title' => $this->lang['emajnbsession'],
					'field' => field('rlbk_nb_session'),
					'params'=> array('align' => 'right'),
				),
				'rlbkNbTable' => array(
					'title' => $this->lang['emajnbtabletoprocess'],
					'field' => field('rlbk_eff_nb_table'),
					'params'=> array('align' => 'right'),
				),
				'rlbkNbSeq' => array(
					'title' => $this->lang['emajnbseqtoprocess'],
					'field' => field('rlbk_nb_sequence'),
					'params'=> array('align' => 'right'),
				),
			);

			$columnsCompletedRlbk = array(
				'rlbkId' => array(
					'title' => $this->lang['emajrlbkid'],
					'field' => field('rlbk_id'),
					'params'=> array('align' => 'right'),
				),
				'rlbkGroups' => array(
					'title' => $this->lang['emajgroups'],
					'field' => field('rlbk_groups_list'),
				),
				'rlbkStatus' => array(
					'title' => $this->lang['emajstate'],
					'field' => field('rlbk_status'),
				),
				'rlbkStartDateTime' => array(
					'title' => $this->lang['emajrlbkstart'],
					'field' => field('rlbk_start_datetime'),
					'params'=> array('align' => 'center'),
				),
				'rlbkEndDateTime' => array(
					'title' => $this->lang['emajrlbkend'],
					'field' => field('rlbk_end_datetime'),
					'params'=> array('align' => 'center'),
				),
				'rlbkDuration' => array(
					'title' => $this->lang['emajduration'],
					'field' => field('rlbk_duration'),
					'params'=> array('align' => 'center'),
				),
				'rlbkMark' => array(
					'title' => $this->lang['emajtargetmark'],
					'field' => field('rlbk_mark'),
				),
				'rlbkMarkDateTime' => array(
					'title' => $this->lang['emajmarksetat'],
					'field' => field('rlbk_mark_datetime'),
				),
				'isLogged' => array(
					'title' => $this->lang['emajislogged'],
					'field' => field('rlbk_is_logged'),
					'type'	=> 'callback',
					'params'=> array('function' => array($this,'renderBoolean'),'align' => 'center')
				),
				'rlbkNbSession' => array(
					'title' => $this->lang['emajnbsession'],
					'field' => field('rlbk_nb_session'),
					'params'=> array('align' => 'right'),
				),
				'rlbkNbTable' => array(
					'title' => $this->lang['emajnbproctable'],
					'field' => field('rlbk_eff_nb_table'),
					'params'=> array('align' => 'right'),
				),
				'rlbkNbSeq' => array(
					'title' => $this->lang['emajnbprocseq'],
					'field' => field('rlbk_nb_sequence'),
					'params'=> array('align' => 'right'),
				),
			);

			$actions = array();

			// Get rollback information from the database
			$completedRlbks = $this->emajdb->getCompletedRlbk($nbRlbk, $rlbkRetention);

			echo "<h3>{$this->lang['emajinprogressrlbk']}</h3>\n";
			if ($this->emajdb->isDblinkUsable()) {
				$inProgressRlbks = $this->emajdb->getInProgressRlbk();
				$this->printTable($inProgressRlbks, $columnsInProgressRlbk, $actions, 'inProgressRlbk', $this->lang['emajnorlbk']);
			} else {
				echo "<p>{$this->lang['emajrlbkmonitornotavailable']}</p>\n";
			}

			echo "<h3>{$this->lang['emajcompletedrlbk']}</h3>\n";

			// Form to setup parameters for completed rollback operations filtering
			echo "<div style=\"margin-bottom:10px;\">\n";
			echo "<form action=\"plugin.php?plugin={$this->name}&amp;action=filterrlbk\" method=\"post\">\n";
			echo "{$this->lang['emajfilterrlbk1']} :&nbsp;&nbsp;\n";

				// mask-pnum class is used by jquery.filter to only accept digits
			echo "<input type=checkbox name=\"emajnbrlbkchecked\" id=\"nbrlbkchecked\"";
			if (isset($_SESSION['emaj']['NbRlbkChecked'])) echo " checked";
			echo "/>\n<input name=\"emajRlbkNb\" size=\"2\" id=\"rlbkNb\" class=\"mask-pnum\" value=\"{$_SESSION['emaj']['RlbkNb']}\"";
			if (!isset($_SESSION['emaj']['NbRlbkChecked'])) echo " disabled";
			echo "/>\n{$this->lang['emajfilterrlbk2']}&nbsp;&nbsp;&nbsp;";

			echo "<input type=checkbox name=\"emajdurationchecked\" id=\"durationchecked\"";
			if (isset($_SESSION['emaj']['DurationChecked'])) echo " checked";
			echo "/>\n {$this->lang['emajfilterrlbk3']} \n";
			echo "<input name=\"emajRlbkRetention\" size=\"3\" id=\"rlbkRetention\" class=\"mask-pnum\" value=\"{$_SESSION['emaj']['RlbkRetention']}\"";
			if (!isset($_SESSION['emaj']['DurationChecked'])) echo " disabled";
			echo "/>\n {$this->lang['emajfilterrlbk4']}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

			echo $misc->form;
			echo "<input type=\"submit\" name=\"filterrlbk\" value=\"{$this->lang['emajfilter']}\" />\n";
			echo "</form></div>\n";

			$this->printTable($completedRlbks, $columnsCompletedRlbk, $actions, 'completedRlbk', $this->lang['emajnorlbk']);

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
			if ($this->emajdb->getNumEmajVersion() >= 20000) {			// version >= 2.0.0

				$columnsConsRlbk = array(
					'consGroup' => array(
						'title' => $this->lang['emajgroup'],
						'field' => field('cons_group'),
					),
					'consTargetMark' => array(
						'title' => $this->lang['emajtargetmark'],
						'field' => field('cons_target_rlbk_mark_name'),
					),
					'consTargetMarkDateTime' => array(
						'title' => $this->lang['emajmarksetat'],
						'field' => field('cons_target_rlbk_mark_datetime'),
					),
					'rlbkNbRow' => array(
						'title' => $this->lang['emajnbupdates'],
						'field' => field('cons_rows'),
						'params'=> array('align' => 'right'),
					),
					'rlbkNbMark' => array(
						'title' => $this->lang['emajnbintermediatemark'],
						'field' => field('cons_marks'),
						'params'=> array('align' => 'right'),
					),
					'consEndMark' => array(
						'title' => $this->lang['emajendrollbackmark'],
						'field' => field('cons_end_rlbk_mark_name'),
					),
					'consEndMarkDateTime' => array(
						'title' => $this->lang['emajmarksetat'],
						'field' => field('cons_end_rlbk_mark_datetime'),
					),
					'actions' => array(
						'title' => $lang['stractions'],
					),
				);

				$actions = array(
					'consolidate' => array(
						'content' => $this->lang['emajconsolidate'],
						'attr' => array (
							'href' => array (
								'url' => 'plugin.php',
								'urlvars' => array (
									'plugin' => $this->name,
									'action' => 'consolidate_rollback',
									'group' => field('cons_group'),
									'mark' => field('cons_end_rlbk_mark_name'),
								)))
					),
				);

				// Get rollback information from the database
				$consolidableRlbks = $this->emajdb->getConsolidableRlbk();

				echo "<h3>{$this->lang['emajconsolidablerlbk']}</h3>\n";
				$inProgressRlbks = $this->emajdb->getInProgressRlbk();
				$this->printTable($consolidableRlbks, $columnsConsRlbk, $actions, 'consolidableRlbk', $this->lang['emajnorlbk']);
			}
		}

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Displays all detailed information about one group, including marks
	 */
	function show_group($msg = '', $errMsg = '') {
		global $misc, $lang;

		$this->printPageHeader('emajgroup','emajgroupproperties');

		$emajOK = $this->printEmajHeader("action=show_group&amp;group=".urlencode($_REQUEST['group']),sprintf($this->lang['emajgrouppropertiesmarks'],$_REQUEST['group']));

		if ($emajOK) {
			$this->printMsg($msg,$errMsg);

		// general information about the group
			$group = $this->emajdb->getGroup($_REQUEST['group']);

		// save some fields before calling printTable()
			$comment=$group->fields['group_comment'];
			$nbMarks = $group->fields['nb_mark'];
			$groupState = $group->fields['group_state'];
			$groupType = $group->fields['group_type'];

			$columns = array(
				'state' => array(
					'title' => $this->lang['emajstate'],
					'field' => field('group_state'),
					'type'	=> 'callback',
					'params'=> array('function' => array($this,'renderGroupState'),'align' => 'center')
				),
				'creationdatetime' => array(
					'title' => $this->lang['emajcreationdatetime'],
					'field' => field('group_creation_datetime'),
					'params'=> array('align' => 'center'),
				),
				'nbtbl' => array(
					'title' => $this->lang['emajnbtbl'],
					'field' => field('group_nb_table'),
					'type'  => 'numeric'
				),
				'nbseq' => array(
					'title' => $this->lang['emajnbseq'],
					'field' => field('group_nb_sequence'),
					'type'  => 'numeric'
				),
				'rollbackable' => array(
					'title' => $lang['strtype'],
					'field' => field('group_type'),
					'type'	=> 'callback',
					'params'=> array(
							'function' => array($this,'renderGroupType'),
							'align' => 'center',
							),
				),
				'nbmark' => array(
					'title' => $this->lang['emajnbmark'],
					'field' => field('nb_mark'),
					'type'  => 'numeric'
				),
				'logsize' => array(
					'title' => $this->lang['emajlogsize'],
					'field' => field('log_size'),
					'params'=> array('align' => 'center'),
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
			);

			$urlvars = $misc->getRequestVars();

			$groupActions = array();

		// print group's characteristics
			echo "<h3>".sprintf($this->lang['emajgroupproperties'],$_REQUEST['group'])."</h3>\n";
//			$misc->printTable($group, $columns, $groupActions, 'detailGroup', 'no group, internal error !');
			$this->printTable($group, $columns, $groupActions, 'detailGroup', 'no group, internal error !');

		// display group's comment if exists
			if ($comment<>'') {
				echo "<p>{$lang['strcomment']} : ",$comment,"</p>\n";
			}

		// display the link corresponding to the available functions for the group
			echo "<ul class=\"navlink\">";

			// start_group
			if ($this->emajdb->isEmaj_Adm() && $groupState == 'IDLE') {
				echo "  <li><a href=\"plugin.php?plugin={$this->name}&amp;&action=start_group&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\">{$lang['strstart']}</a></li>\n";
			}

			// stop_group
			if ($this->emajdb->isEmaj_Adm() && $groupState == 'LOGGING') {
				echo "  <li><a href=\"plugin.php?plugin={$this->name}&amp;&action=stop_group&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\">{$lang['strstop']}</a></li>\n";
			}

			// set_mark_group
			if ($this->emajdb->isEmaj_Adm() && $groupState == 'LOGGING') {
				echo "  <li><a href=\"plugin.php?plugin={$this->name}&amp;&action=set_mark_group&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\">{$this->lang['emajsetmark']}</a></li>\n";
			}

			// reset_group
			if ($this->emajdb->isEmaj_Adm() && $groupState == 'IDLE') {
				echo "  <li><a href=\"plugin.php?plugin={$this->name}&amp;&action=reset_group&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\">{$lang['strreset']}</a></li>\n";
			}

			// drop_group
			if ($this->emajdb->isEmaj_Adm() && $groupState == 'IDLE') {
				echo "  <li><a href=\"plugin.php?plugin={$this->name}&amp;&action=drop_group&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\">{$lang['strdrop']}</a></li>\n";
			}

			// alter_group
			if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
				if ($this->emajdb->isEmaj_Adm() && ($groupState == 'IDLE' || $this->emajdb->getNumEmajVersion() >= 20100)) {
					echo "  <li><a href=\"plugin.php?plugin={$this->name}&amp;&action=alter_group&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\">{$lang['stralter']}</a></li>\n";
				}
			}

			// protect_group
			if ($this->emajdb->getNumEmajVersion() >= 10300) {			// version >= 1.3.0
				if ($this->emajdb->isEmaj_Adm() && $groupState == 'LOGGING' && $groupType == "ROLLBACKABLE") {
					echo "  <li><a href=\"plugin.php?plugin={$this->name}&amp;&action=protect_group&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\">{$this->lang['emajprotect']}</a></li>\n";
				}
			}

			// unprotect_group
			if ($this->emajdb->getNumEmajVersion() >= 10300) {			// version >= 1.3.0
				if ($this->emajdb->isEmaj_Adm() && $groupState == 'LOGGING' && $groupType == "ROLLBACKABLE-PROTECTED") {
					echo "  <li><a href=\"plugin.php?plugin={$this->name}&amp;&action=unprotect_group&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\">{$this->lang['emajunprotect']}</a></li>\n";
				}
			}

			// comment_group
			if ($this->emajdb->isEmaj_Adm()) {
				echo "  <li><a href=\"plugin.php?plugin={$this->name}&amp;&action=comment_group&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\">{$this->lang['emajsetcomment']}</a></li>\n";
			}

			echo "</ul>\n";

		// Show marks of the groups

		// get marks from database
			$marks = $this->emajdb->getMarks($_REQUEST['group']);

			echo "<hr/>\n";
			echo "<h3>".sprintf($this->lang['emajgroupmarks'],$_REQUEST['group'])."</h3>\n";

			$columns = array(
				'mark' => array(
					'title' => $this->lang['emajmark'],
					'field' => field('mark_name'),
				),
				'datetime' => array(
					'title' => $this->lang['emajtimestamp'],
					'field' => field('mark_datetime'),
				),
				'state' => array(
					'title' => $this->lang['emajstate'],
					'field' => field('mark_state'),
					'type'	=> 'callback',
					'params'=> array('function' => array($this,'renderMarkState'),'align' => 'center')
				),
				'logrows' => array(
					'title' => $this->lang['emajnbupdates'],
					'field' => field('mark_logrows'),
					'type'  => 'numeric'
				),
				'cumlogrows' => array(
					'title' => $this->lang['emajcumupdates'],
					'field' => field('mark_cumlogrows'),
					'type'  => 'numeric'
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
					'comment' => array(
					'title' => $lang['strcomment'],
					'field' => field('mark_comment'),
				),
			);

			$urlvars = $misc->getRequestVars();

			$actions = array();
			if ($this->emajdb->isEmaj_Adm() && $groupType == "ROLLBACKABLE") {
				$actions = array_merge($actions, array(
					'rollbackgroup' => array(
						'content' => $this->lang['emajrlbk'],
						'attr' => array (
							'href' => array (
								'url' => 'plugin.php',
								'urlvars' => array_merge($urlvars, array (
									'plugin' => $this->name,
									'action' => 'rollback_group',
									'back' => 'detail',
									'group' => field('mark_group'),
									'mark' => field('mark_name'),
								))))
					),
				));
			}
			if ($this->emajdb->isEmaj_Adm()) {
				$actions = array_merge($actions, array(
					'renamemark' => array(
						'content' => $this->lang['emajrename'],
						'attr' => array (
							'href' => array (
								'url' => 'plugin.php',
								'urlvars' => array_merge($urlvars, array (
									'plugin' => $this->name,
									'action' => 'rename_mark_group',
									'back' => 'detail',
									'group' => field('mark_group'),
									'mark' => field('mark_name'),
								))))
					),
					'deletemark' => array(
						'content' => $lang['strdelete'],
						'attr' => array (
							'href' => array (
								'url' => 'plugin.php',
								'urlvars' => array_merge($urlvars, array (
									'plugin' => $this->name,
									'action' => 'delete_mark',
									'back' => 'detail',
									'group' => field('mark_group'),
									'mark' => field('mark_name'),
								))))
					),
					'deletebeforemark' => array(
						'content' => $this->lang['emajfirstmark'],
						'attr' => array (
							'href' => array (
								'url' => 'plugin.php',
								'urlvars' => array_merge($urlvars, array (
									'plugin' => $this->name,
									'action' => 'delete_before_mark',
									'back' => 'detail',
									'group' => field('mark_group'),
									'mark' => field('mark_name'),
								))))
					),
				));
			}
			if ($this->emajdb->getNumEmajVersion() >= 10300) {			// version >= 1.3.0
				if ($this->emajdb->isEmaj_Adm() && $groupState == 'LOGGING' && $groupType != "AUDIT_ONLY") {
					$actions = array_merge($actions, array(
						'protectmark' => array(
							'content' => $this->lang['emajprotect'],
							'attr' => array (
								'href' => array (
									'url' => 'plugin.php',
									'urlvars' => array_merge($urlvars, array (
										'plugin' => $this->name,
										'action' => 'protect_mark_group',
										'back' => 'detail',
										'group' => field('mark_group'),
										'mark' => field('mark_name'),
									))))
						),
						'unprotectmark' => array(
							'content' => $this->lang['emajunprotect'],
							'attr' => array (
								'href' => array (
									'url' => 'plugin.php',
									'urlvars' => array_merge($urlvars, array (
										'plugin' => $this->name,
										'action' => 'unprotect_mark_group',
										'back' => 'detail',
										'group' => field('mark_group'),
										'mark' => field('mark_name'),
									))))
						),
					));
				};
			}
			if ($this->emajdb->isEmaj_Adm()) {
				$actions = array_merge($actions, array(
					'commentmark' => array(
						'content' => $this->lang['emajsetcomment'],
						'attr' => array (
							'href' => array (
								'url' => 'plugin.php',
								'urlvars' => array_merge($urlvars, array (
									'plugin' => $this->name,
									'action' => 'comment_mark_group',
									'back' => 'detail',
									'group' => field('mark_group'),
									'mark' => field('mark_name'),
								))))
					),
				));
			};

			// reset previous_cumlogrows and the flag for protected marks that will be used in markPre function
			$this->previous_cumlogrows = 0;
			$this->protected_mark_flag = 0;

			// display the marks list
			echo "<div id=\"marksTable\">\n";
//			$misc->printTable($marks, $columns, $actions, 'marks', $this->lang['emajnomark'],'markPre');
			$this->printTable($marks, $columns, $actions, 'marks', $this->lang['emajnomark'],array($this,'markPre'));
			echo "</div>\n";

			// activate tablesorter script
			echo "<script type=\"text/javascript\">
				$(document).ready(function() {
					$(\"#marksTable table\").addClass('tablesorter');
					$(\"#marksTable table\").tablesorter(
						{
						headers: {
							0: { sorter: false },
							1: { sorter: false },
							2: { sorter: false, filter: false },
							3: { sorter: false },
							4: { sorter: false },
							5: { sorter: false, filter: false },
							6: { sorter: false },
							7: { sorter: false },
							},
						emptyTo: 'none',
						widgets: [\"zebra\", \"filter\"],
						widgetOptions: {
							zebra : [ \"data1\", \"data2\" ],
							filter_hideFilters : true,
							stickyHeaders : 'tablesorter-stickyHeader', 
							},
						}
					)
					// disable filters on all but first actions columns
					$('#marksTable input[data-column=\"6\"]').addClass(\"disabled\");
					$('#marksTable input[data-column=\"6\"]').attr(\"disabled\",\"\");
					$('#marksTable input[data-column=\"7\"]').addClass(\"disabled\");
					$('#marksTable input[data-column=\"7\"]').attr(\"disabled\",\"\");
					$('#marksTable input[data-column=\"8\"]').addClass(\"disabled\");
					$('#marksTable input[data-column=\"8\"]').attr(\"disabled\",\"\");
					$('#marksTable input[data-column=\"9\"]').addClass(\"disabled\");
					$('#marksTable input[data-column=\"9\"]').attr(\"disabled\",\"\");
					$('#marksTable input[data-column=\"10\"]').addClass(\"disabled\");
					$('#marksTable input[data-column=\"10\"]').attr(\"disabled\",\"\");
					$('#marksTable input[data-column=\"11\"]').addClass(\"disabled\");
					$('#marksTable input[data-column=\"11\"]').attr(\"disabled\",\"\");
				});
				</script>";

			// JQuery to remove the last deleteBeforeMark button as it is meaningless on the first set mark
			echo "<script type=\"text/javascript\">\n";
			echo "  $(\"tr:last td:contains('{$this->lang['emajfirstmark']}')\").removeClass()\n";
			echo "  $(\"tr:last a:contains('{$this->lang['emajfirstmark']}')\").remove()\n";
			echo "</script>\n";
		}

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Show global or detailed log statistics between 2 marks or since a mark
	 */
	function log_stat_group() {
		global $misc, $lang;

		$this->printPageHeader('emajgroup','emajlogstat');

		$globalStat = false; $detailedStat = false; $simRlbk = false;
		if (isset($_REQUEST['simrlbk'])) {
			$simRlbk = true;
		}
		if (isset($_REQUEST['globalstatgroup'])) {
			$globalStat = true;
			$urlExt = 'globalstatgroup='.urlencode($_REQUEST['globalstatgroup']);
		}
		if (isset($_REQUEST['detailedstatgroup'])) {
			$detailedStat = true;
			$urlExt = 'detailedstatgroup='.urlencode($_REQUEST['detailedstatgroup']);
		}

		$emajOK = $this->printEmajHeader("action=log_stat_group&amp;group=".urlencode($_REQUEST['group']),sprintf($this->lang['emajshowstat'],$_REQUEST['group']));

		if ($emajOK) {

		// display the stat form

			// get marks from database
			$marks = $this->emajdb->getMarks($_REQUEST['group']);

			// get group's characteristics
			$group = $this->emajdb->getGroup($_REQUEST['group']);

			if ($marks->recordCount() < 1) {

				// No mark recorded for the group => no update logged => no stat to display
				echo "<p>{$this->lang['emajnoupdate']}</p>\n"; 

			} else {

				// form for statistics selection
				echo "<style type=\"text/css\">[disabled]{color:#933;}</style>";
				echo "<form id=\"statistics_form\" action=\"plugin.php?plugin={$this->name}&amp;action=log_stat_group&amp;back=detail&amp;{$misc->href}\"";
				echo "  method=\"post\" enctype=\"multipart/form-data\">\n";

				// First mark defining the marks range to analyze
				echo "<p>{$this->lang['emajfrom']}\n";
				echo "  <select name=\"rangestart\" id=\"rangestart\">\n";
				foreach($marks as $r)
					echo "    <option value=\"{$r['mark_name']}\" >{$r['mark_name']} ({$r['mark_datetime']})</option>\n";
				echo "  </select>\n";

				// Last mark defining the marks range to analyze
				echo "{$this->lang['emajto']}\n";
				echo "  <select name=\"rangeend\" id=\"rangeend\" >\n";
				echo "    <option value=\"currentsituation\">{$this->lang['emajcurrentsituation']}</option>\n";
				foreach($marks as $r)
					echo "    <option value=\"{$r['mark_name']}\">{$r['mark_name']} ({$r['mark_datetime']})</option>\n";
				echo "  </select></p>\n";

				// Other elements of the form
				echo "  <p><input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";

				// Rollback simulation checkbox is only available for rollbackable groups in logging state
				if ($group->fields['group_type'] == 'ROLLBACKABLE' && $group->fields['group_state'] == 'LOGGING') {
					echo "  {$this->lang['emajsimrlbk']} <input type=\"checkbox\" name=\"simrlbk\" id=\"simrlbk\"/>\n";
				}

				echo "  <input type=\"submit\" name=\"globalstatgroup\" value=\"{$this->lang['emajestimates']}\" />\n";
				echo "  <input type=\"submit\" name=\"detailedstatgroup\" value=\"{$this->lang['emajdetailedstat']}\" />\n";
				echo "  <img src=\"{$misc->icon(array($this->name,'EmajWarning'))}\" alt=\"warning\" title=\"{$this->lang['emajdetailedlogstatwarning']}\" style=\"vertical-align:middle\"/>";
				echo "</p></form>\n";

				// JQuery scripts
				echo "<script type=\"text/javascript\">\n";

				// JQuery to remove the last mark as it cannot be selected as end mark
				echo "  $(\"#rangeend option:last-child\").remove();\n";

				// JQuery to set the selected start mark by default 
				// (the previous requested start mark or the first mark if no stat are already displayed)
				if (isset($_REQUEST['rangestart'])) {
					echo "  $(\"#rangestart option[value={$_REQUEST['rangestart']}]\").attr(\"selected\", true);\n";
				} else {
					echo "  $(\"#rangestart option:first-child\").attr(\"selected\", true);\n";
				}

				// JQuery to set the selected end mark by default 
				// (the previous requested end mark or the current situation if no stat are already displayed)
				if (isset($_REQUEST['rangeend'])) {
					echo "  $(\"#rangeend option[value={$_REQUEST['rangeend']}]\").attr(\"selected\", true);\n";
				} else {
					echo "  $(\"#rangeend option:first-child\").attr(\"selected\", true);\n";
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
						// and disable the simrlbk checkbox if the rangeend is not "currentsituation"
				echo "    if ($(\"#rangeend option:selected\").val() == \"currentsituation\") { \n";
				echo "      $(\"#simrlbk\").removeAttr('checked');\n";
				echo "      $(\"#simrlbk\").removeAttr('disabled');\n";
				echo "    } else {\n";
				echo "      $(\"#simrlbk\").prop('disabled',true);\n";
				echo "    }\n";
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
						// and disable the simrlbk checkbox if the rangeend is not "currentsituation"
				echo "    if ($(\"#rangeend option:selected\").val() == \"currentsituation\") { \n";
				echo "      $(\"#simrlbk\").removeAttr('checked');\n";
				echo "      $(\"#simrlbk\").removeAttr('disabled');\n";
				echo "    } else {\n";
				echo "      $(\"#simrlbk\").prop('disabled',true);\n";
				echo "    }\n";
				echo "  });\n";

				echo "</script>\n";

				// If rollback simulation is requested, compute the duration estimate
				$rlbkDuration = '';
				if ($simRlbk) {
					// check the start mark is not deleted
					if ($this->emajdb->isMarkActiveGroup($_REQUEST['group'],$_REQUEST['rangestart']) == 1) {
						$rlbkDuration = $this->emajdb->estimateRollbackGroup($_REQUEST['group'],$_REQUEST['rangestart']);
					} else {
						$rlbkDuration = "-";
					}
				}

				// If global stat display is requested
				if ($globalStat) {
					$this->disp_global_log_stat_section($rlbkDuration);
				}

				// If detailed stat display is requested
				if ($detailedStat) {
					$this->disp_detailed_log_stat_section($rlbkDuration);
				}
			}
		}

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * This function is called by the log_stat_group() function.
	 * It generates the page section corresponding to the rollback simulation output
	 */
	function disp_global_log_stat_section($rlbkDuration) {
		global $misc, $lang;

		// Get statistics from E-Maj
		if ($_REQUEST['rangeend']=='currentsituation') {
			$w1 = $this->lang['emajlogstatcurrentsituation'];
			$stats = $this->emajdb->getLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],'');
		} else {
			$w1 = sprintf($this->lang['emajlogstatmark'], $_REQUEST['rangeend']);
			$stats = $this->emajdb->getLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],$_REQUEST['rangeend']);
		}
		$summary = $this->emajdb->getLogStatSummary();

		// Title
		echo "<hr/>\n";
		echo "<h3>".sprintf($this->lang['emajlogstattittle'], $misc->printVal($_REQUEST['rangestart']), $misc->printVal($w1), $misc->printVal($_REQUEST['group']))."</h3>\n";

		// Display summary statistics
		echo "<div style=\"margin-bottom:15px\">\n";
		echo "<table><tr>\n";
		echo "<th class=\"data\" colspan=2>{$this->lang['emajestimates']}</th>\n";
		echo "</tr><tr>\n";
		echo "<th class=\"data\">{$this->lang['emajnbtbl']}</th>";
		echo "<th class=\"data\">{$this->lang['emajnbupdates']}</th>";
		echo "</tr><tr class=\"data1\">\n";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_tables']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['sum_rows']}</div></td>";
		echo "</tr></table>\n";
		echo "</div>\n";
		
		// Display rollback duration estimate if requested
		if ($rlbkDuration != '') {
			if ($rlbkDuration == '-') {
				// the start mark cannot be used for a rollback
				echo "<p>{$this->lang['emajnosimrlbkduration']}</p>";
			} else {
				// dispay the duration estimate
				echo "<p>",sprintf($this->lang['emajsimrlbkduration'], $misc->printVal($_REQUEST['group']), $misc->printVal($_REQUEST['rangestart']),$rlbkDuration),"</p>\n";
			}
		}

		if ($summary->fields['nb_tables'] > 0) {

			// Display per table statistics
			$urlvars = $misc->getRequestVars();

			$columns = array(
				'schema' => array(
					'title' => $lang['strschema'],
					'field' => field('stat_schema'),
					'url'   => "redirect.php?subject=schema&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema'),
				),
				'table' => array(
					'title' => $lang['strtable'],
					'field' => field('stat_table'),
					'url'	=> "redirect.php?subject=table&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema', 'table' => 'stat_table'),
				));
			if ($this->emajdb->getNumEmajVersion() >= 20300) {			// version >= 2.3.0
				$columns = array_merge($columns, array(
					'stat_first_mark' => array(
						'title' => $this->lang['emajstatfirstmark'],
						'field' => field('stat_first_mark'),
					),
					'stat_first_mark_datetime' => array(
						'title' => $this->lang['emajstatfirstmarkdatetime'],
						'field' => field('stat_first_mark_datetime'),
					),
					'stat_last_mark' => array(
						'title' => $this->lang['emajstatlastmark'],
						'field' => field('stat_last_mark'),
					),
					'stat_last_mark_datetime' => array(
						'title' => $this->lang['emajstatlastmarkdatetime'],
						'field' => field('stat_last_mark_datetime'),
					),
				));
			}
			$columns = array_merge($columns, array(
				'nbrow' => array(
					'title' => $this->lang['emajstatrows'],
					'field' => field('stat_rows'),
					'type'  => 'numeric'
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
			));

			$actions = array(
				'sql' => array(
					'content' => $lang['strsql'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'call_sqledit',
								'subject' => 'table',
								'sqlquery' => field('sql_text'),
								'paginate' => 'true',
					)))),
				),
			);

			echo "<div id=\"statTable\">\n";
//			$misc->printTable($stats, $columns, $actions, 'stats');
			$this->printTable($stats, $columns, $actions, 'stats');
			echo "</div>\n";

			// activate tablesorter script
			echo "<script type=\"text/javascript\">
				$(document).ready(function() {
					$(\"#statTable table\").addClass('tablesorter');
					$(\"#statTable table\").tablesorter(
						{	headers: { 
								7: { sorter: false, filter: false } 
							},
							emptyTo: 'none',
							widgets: [\"zebra\", \"filter\"],
							widgetOptions: {
								zebra : [ \"data1\", \"data2\" ],
								filter_hideFilters : true,
								filter_functions : {  0: true },
								stickyHeaders : 'tablesorter-stickyHeader', 
							},
						})
					});
			</script>";

			// dynamicly change the behaviour of the SQL link using JQuery code: open a new window
			$sql_window_id = htmlentities('sqledit:'.$_REQUEST['server']);
				echo "<script type=\"text/javascript\">
				$(\"#statTable a:contains('SQL')\").attr('target','emaj_sqledit');
				$(\"#statTable a:contains('SQL')\").click(function() {
					window.open($(this).attr('href'),'{$sql_window_id}','toolbar=no,width=700,height=500,resizable=yes,scrollbars=yes').focus();
					return false;
				});
				</script>";
		}
	}

	/**
	 * This function is called by the log_stat_group() function.
	 * It generates the page section corresponding to the rollback simulation output
	 */
	function disp_detailed_log_stat_section($rlbkDuration) {
		global $misc, $lang;

		if (!ini_get('safe_mode')) set_time_limit(0);		// Prevent timeouts on large stats (non-safe mode only)

		// Get statistics from E-Maj
		if ($_REQUEST['rangeend']=='currentsituation') {
			$w1 = $this->lang['emajlogstatcurrentsituation'];
			$stats = $this->emajdb->getDetailedLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],'');
		} else {
			$w1 = sprintf($this->lang['emajlogstatmark'], $_REQUEST['rangeend']);
			$stats = $this->emajdb->getDetailedLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],$_REQUEST['rangeend']);
		}
		$summary = $this->emajdb->getDetailedLogStatSummary();

		$roles = $this->emajdb->getDetailedLogStatRoles();
		$roleList = '';
		while (!$roles->EOF) {
			if ($roleList == '') {
				$roleList = $roles->fields['stat_role'];
			} else {
				$roleList .= ', '.$roles->fields['stat_role'];
			}
			$roles->moveNext();
		}

		// Title
		echo "<hr/>\n";
		echo "<h3>".sprintf($this->lang['emajlogstattittle'], $misc->printVal($_REQUEST['rangestart']), $misc->printVal($w1), $misc->printVal($_REQUEST['group']))."</h3>\n";

		// Display summary statistics
		echo "<div style=\"margin-bottom:15px\">\n";
		echo "<table><tr>\n";
		echo "<th class=\"data\">{$this->lang['emajnbtbl']}</th>";
		echo "<th class=\"data\">{$this->lang['emajnbupdates']}</th>";
		echo "<th class=\"data\">{$this->lang['emajnbinsert']}</th>";
		echo "<th class=\"data\">{$this->lang['emajnbupdate']}</th>";
		echo "<th class=\"data\">{$this->lang['emajnbdelete']}</th>";
		echo "<th class=\"data\">{$this->lang['emajnbtruncate']}</th>";
		echo "<th class=\"data\">{$this->lang['emajnbrole']}</th>";
		echo "<th class=\"data\">{$lang['strroles']}</th>";
		echo "</tr><tr class=\"data1\">\n";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_tables']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['sum_rows']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_ins']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_upd']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_del']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_tru']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_roles']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$roleList}</div></td>";
		echo "</tr></table>\n";
		echo "</div>\n";

		// Display rollback duration estimate if requested
		if ($rlbkDuration != '') {
			if ($rlbkDuration == '-') {
				// the start mark cannot be used for a rollback
				echo "<p>{$this->lang['emajnosimrlbkduration']}</p>";
			} else {
				// dispay the duration estimate
				echo "<p>",sprintf($this->lang['emajsimrlbkduration'], $misc->printVal($_REQUEST['group']), $misc->printVal($_REQUEST['rangestart']),$rlbkDuration),"</p>\n";
			}
		}

		if ($summary->fields['nb_tables'] > 0) {

			// Display per table statistics
			$urlvars = $misc->getRequestVars();

			$columns = array(
				'schema' => array(
					'title' => $lang['strschema'],
					'field' => field('stat_schema'),
					'url'   => "redirect.php?subject=schema&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema'),
				),
				'table' => array(
					'title' => $lang['strtable'],
					'field' => field('stat_table'),
					'url'	=> "redirect.php?subject=table&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema', 'table' => 'stat_table'),
				));
			if ($this->emajdb->getNumEmajVersion() >= 20300) {			// version >= 2.3.0
				$columns = array_merge($columns, array(
					'stat_first_mark' => array(
						'title' => $this->lang['emajstatfirstmark'],
						'field' => field('stat_first_mark'),
					),
					'stat_first_mark_datetime' => array(
						'title' => $this->lang['emajstatfirstmarkdatetime'],
						'field' => field('stat_first_mark_datetime'),
					),
					'stat_last_mark' => array(
						'title' => $this->lang['emajstatlastmark'],
						'field' => field('stat_last_mark'),
					),
					'stat_last_mark_datetime' => array(
						'title' => $this->lang['emajstatlastmarkdatetime'],
						'field' => field('stat_last_mark_datetime'),
					),
				));
			}
			$columns = array_merge($columns, array(
				'role' => array(
					'title' => $lang['strrole'],
					'field' => field('stat_role'),
				),
				'statement' => array(
					'title' => $this->lang['emajstatverb'],
					'field' => field('stat_verb'),
				),
				'nbrow' => array(
					'title' => $this->lang['emajstatrows'],
					'field' => field('stat_rows'),
					'type'  => 'numeric'
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
			));

			$actions = array(
				'sql' => array(
					'content' => $lang['strsql'],
					'attr' => array (
						'href' => array (
							'url' => 'plugin.php',
							'urlvars' => array_merge($urlvars, array (
								'plugin' => $this->name,
								'action' => 'call_sqledit',
								'subject' => 'table',
								'sqlquery' => field('sql_text'),
								'paginate' => 'true',
					)))),
				),
			);

			echo "<div id=\"statTable\">\n";
//			$misc->printTable($stats, $columns, $actions, 'stats');
			$this->printTable($stats, $columns, $actions, 'stats');
			echo "</div>\n";

			// activate tablesorter script
			echo "<script type=\"text/javascript\">
				$(document).ready(function() {
					$(\"#statTable table\").addClass('tablesorter');
					$(\"#statTable table\").tablesorter(
						{	headers: { 
								9: { sorter: false, filter: false } 
							},
							emptyTo: 'none',
							widgets: [\"zebra\", \"filter\"],
							widgetOptions: {
								zebra : [ \"data1\", \"data2\" ],
								filter_hideFilters : true,
								filter_functions : {  0: true, 2: true, 3: true },
								stickyHeaders : 'tablesorter-stickyHeader', 
							},
						})
					});
			</script>";

			// dynamicly change the behaviour of the SQL link using JQuery code: open a new window
			$sql_window_id = htmlentities('sqledit:'.$_REQUEST['server']);
			echo "<script type=\"text/javascript\">
				$(\"#statTable a:contains('SQL')\").attr('target','emaj_sqledit');
				$(\"#statTable a:contains('SQL')\").click(function() {
					window.open($(this).attr('href'),'{$sql_window_id}','toolbar=no,width=700,height=500,resizable=yes,scrollbars=yes').focus();
					return false;
				});
				</script>";
		}
	}

	/**
	 * Displays the list of tables and sequences that composes a group
	 */
	function show_content_group() {
		global $misc, $lang;

		$this->printPageHeader('emajgroup','emajcontent');

		$emajOK = $this->printEmajHeader("action=show_content_group&amp;group=".urlencode($_REQUEST['group']),sprintf($this->lang['emajgroupcontent'],$_REQUEST['group']));

		if ($emajOK) {

			$groupContent = $this->emajdb->getContentGroup($_REQUEST['group']);

			if ($groupContent->recordCount() < 1) {

				// The group is empty
				echo "<p>" . sprintf($this->lang['emajemptygroup'],$_REQUEST['group']) . "</p>\n";

			} else {

				$columns = array(
					'type' => array(
						'title' => $lang['strtype'],
						'field' => field('relkind'),
						'type'	=> 'callback',
						'params'=> array('function' => array($this, 'renderTblSeq'),'align' => 'center')
					),
					'schema' => array(
						'title' => $lang['strschema'],
						'field' => field('rel_schema'),
						'url'   => "redirect.php?subject=schema&amp;{$misc->href}&amp;",
						'vars'  => array('schema' => 'rel_schema'),
					),
					'tblseq' => array(
						'title' => $lang['strname'],
						'field' => field('rel_tblseq'),
						'url'	=> "redirect.php?subject=table&amp;{$misc->href}&amp;",
						'vars'  => array('schema' => 'rel_schema', 'table' => 'rel_tblseq'),
					),
					'priority' => array(
						'title' => $this->lang['emajpriority'],
						'field' => field('rel_priority'),
						'params'=> array('align' => 'center'),
					),
				);
				if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
					$columns = array_merge($columns, array(
						'log_schema' => array(
							'title' => $this->lang['emajlogschema'],
							'field' => field('rel_log_schema'),
						),
						'log_dat_tsp' => array(
							'title' => $this->lang['emajlogdattsp'],
							'field' => field('rel_log_dat_tsp'),
						),
						'log_idx_tsp' => array(
							'title' => $this->lang['emajlogidxtsp'],
							'field' => field('rel_log_idx_tsp'),
						),
					));
				};
				if ($this->emajdb->getNumEmajVersion() >= 10200) {			// version >= 1.2.0
					$columns = array_merge($columns, array(
						'names_prefix' => array(
							'title' => $this->lang['emajnamesprefix'],
							'field' => field('emaj_names_prefix'),
						),
					));
				};
				$columns = array_merge($columns, array(
					'bytelogsize' => array(
						'title' => $this->lang['emajlogsize'],
						'field' => field('byte_log_size'),
						'params'=> array('align' => 'right'),
					),
					'prettylogsize' => array(
						'title' => $this->lang['emajlogsize'],
						'field' => field('pretty_log_size'),
						'params'=> array('align' => 'center'),
					),
				));
	
				$actions = array ();
	
//				$misc->printTable($groupContent, $columns, $actions, 'groupContent');
				echo "<div id=\"groupContentTable\" style=\"margin-top:15px;margin-bottom:15px\" >\n";
				$this->printTable($groupContent, $columns, $actions, 'groupContent');
				echo "</div>\n";
	
				// activate tablesorter script
				echo "<script type=\"text/javascript\">
					$(document).ready(function() {
						$(\"#groupContentTable table\").addClass('tablesorter');
						$(\"#groupContentTable table\").tablesorter(
							{ 	
								textExtraction: { 
									0: function(s) {
										if($(s).find('img').length == 0) return $(s).text();
										return $(s).find('img').attr('alt');
									}},
								headers: { 
									0: { filter: false }, 
									8: { sorter: false, filter: false } 
								},
								emptyTo: 'none',
								widgets: [\"zebra\", \"filter\"],
								widgetOptions: {
									zebra : [ \"data1\", \"data2\" ],
									filter_hideFilters : true,
									filter_functions : {  1: true, 4: true, 5: true, 6: true },
									stickyHeaders : 'tablesorter-stickyHeader', 
								},
							}
						);
					});
					</script>";
			}
		}
		$this->printEmajFooter();
		$misc->printFooter();
	}

/********************************************************************************************************
 * Functions preparing or performing actions
 *******************************************************************************************************/

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

		$this->show_rollbacks();
	}

	/**
	 * Prepare insert a table/sequence into a group: ask for properties and confirmation
	 */
	function assign_tblseq() {
		global $misc, $lang;

		// Test at least 1 table/sequence is to be processed
		if (empty($_REQUEST['tblseq']) && empty($_REQUEST['ma'])) {
			$this->configure_groups($this->lang['emajspecifytblseqtoassign']);
			exit();
		}
		// Test all tables/sequences to process are not yet assigned to a group and have a valid type
		if (isset($_REQUEST['ma'])) {
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				if ($a['group'] != '') {
					$this->configure_groups('',sprintf($this->lang['emajtblseqyetgroup'],$a['appschema'],$a['tblseq']));
					exit();
				}
				if ($a['type'] != 'r+' and $a['type'] != 'S+') {
					$this->configure_groups('',sprintf($this->lang['emajtblseqbadtype'],$a['appschema'],$a['tblseq']));
					exit();
				}
			}
		} else {
			if ($_REQUEST['group'] != '') {
				$this->configure_groups('',sprintf($this->lang['emajtblseqyetgroup'],$_REQUEST['appschema'],$_REQUEST['tblseq']));
				exit();
			}
		}

		$this->printPageHeader('emaj','emajconfiguregroups');

		$misc->printTitle($this->lang['emajassigntblseq']);

		// Get group names already known in emaj_group_def table
		$knownGroups = $this->emajdb->getKnownGroups();

		// Get log schema suffix already known in emaj_group_def table
		if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
			$knownSuffix = $this->emajdb->getKnownSuffix();
		}

		// Get tablespaces the current user can see
		if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
			$knownTsp = $this->emajdb->getKnownTsp();
		}

		// Build the list of tables and sequences to processs and count them
		$lst = ''; $nbTbl = 0; $nbSeq = 0;
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"assign_tblseq_ok\" />\n";

		if (isset($_REQUEST['ma'])) {
		// multiple assign
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				echo "<input type=\"hidden\" name=\"appschema[]\" value=\"", htmlspecialchars($a['appschema']), "\" />\n";
				echo "<input type=\"hidden\" name=\"tblseq[]\" value=\"", htmlspecialchars($a['tblseq']), "\" />\n";
				$lst .= "<br>- {$a['appschema']}.{$a['tblseq']}";
				if ($a['type'] == 'r+') $nbTbl++; else $nbSeq++;
			}
		} else {

		// single assign
			echo "<input type=\"hidden\" name=\"appschema\" value=\"", htmlspecialchars($_REQUEST['appschema']), "\" />\n";
			echo "<input type=\"hidden\" name=\"tblseq\" value=\"", htmlspecialchars($_REQUEST['tblseq']), "\" />\n";
			$lst = "{$_REQUEST['appschema']}.{$_REQUEST['tblseq']}";
			if ($_REQUEST['type'] == 'r+') $nbTbl++; else $nbSeq++;
		}

		echo "<p>", sprintf($this->lang['emajconfirmassigntblseq'], $lst), "</p>\n";

		// Display the input fields depending on the context
		echo "<table>\n";
		echo "<tr><th class=\"data left required\" rowspan=2>{$this->lang['emajgroup']}</th>";
		echo "<td class=\"data1\"><input id=\"groupInput\" type=\"text\" name=\"group\" value=\"\"/><span style=\"font-size: smaller; vertical-align: super;\"> (1)</span></td></tr>\n";
		echo "<tr><td><select id=\"groupList\" name=\"group1\"><option value=\"new_group\">{$this->lang['emajnewgroup']}</option>\n";
		if ($knownGroups->recordCount() > 0) {
			foreach($knownGroups as $r)
				echo "<option value=\"{$r['group_name']}\">{$r['group_name']}</option>\n";
		}
		echo "</select></td></tr>\n";
		// mask-pnum class is used by jquery.filter to only accept digits
		echo "<tr><th class=\"data left\">{$this->lang['emajenterpriority']}</th>";
		echo "<td class=\"data1\"><input type=\"text\" name=\"priority\" size=9 maxlength=9 style=\"text-align: right;\" value=\"\" class=\"mask-pnum\"/> <span style=\"font-size: smaller; vertical-align: super;\"> (2)</span></td></tr>\n";

		if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
			if ($nbTbl >= 1) {
				echo "<tr><th class=\"data left\" rowspan=2>{$this->lang['emajenterlogschema']}</th>";
				echo "<td class=\"data1\"><input type=\"text\" id=\"suffixInput\" name=\"suffix\" value=\"\"/><span style=\"font-size: smaller; vertical-align: super;\"> (3)</span></td></tr>\n";
				echo "<tr><td><select id=\"suffixList\" name=\"suffix1\">\n";
				echo "\t\t<option value=\"new_log_schema_suffix\">{$this->lang['emajnewsuffix']}</option>\n";
				if ($knownSuffix->recordCount() > 0) {
					foreach($knownSuffix as $r)
						echo "\t\t<option value=\"{$r['known_suffix']}\">{$r['known_suffix']}</option>\n";
				}
				echo "\t</select></td></tr>\n";
			} else {
				echo "<p><input type=\"hidden\" name=\"suffix\" value=\"\" />\n";
			}
		} else {
			echo "<p><input type=\"hidden\" name=\"suffix\" value=\"\" />\n";
		}

		if ($this->emajdb->getNumEmajVersion() >= 10200) {			// version >= 1.2.0
			if ($nbTbl == 1) {
				// the names prefix is accessible only for a single table assignment
				echo "<tr><th class=\"data left\">{$this->lang['emajenternameprefix']}</th>";
				echo "<td class=\"data1\"><input type=\"text\" id=\"nameprefixInput\" name=\"nameprefix\" value=\"\"/><span style=\"font-size: smaller; vertical-align: super;\"> (4)</span></td></tr>\n";
			} else {
				echo "<p><input type=\"hidden\" name=\"nameprefix\" value=\"\" />\n";
			}
		} else {
			echo "<p><input type=\"hidden\" name=\"nameprefix\" value=\"\" />\n";
		}

		if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
			if ($nbTbl >= 1) {
				echo "<tr><th class=\"data left\" rowspan=2>{$this->lang['emajenterlogdattsp']}</th>";
				echo "<td class=\"data1\"><input type=\"text\" id=\"logdattspInput\" name=\"logdattsp\" value=\"\"/></td></tr>\n";
				echo "<tr><td><select id=\"logdattspList\" name=\"logdattsp1\">\n";
				echo "\t\t<option value=\"new_log_dat_tsp\">{$this->lang['emajnewtsp']}</option>\n";
				if ($knownTsp->recordCount() > 0) {
					foreach($knownTsp as $r)
						echo "\t\t<option value=\"{$r['spcname']}\">{$r['spcname']}</option>\n";
				}
				echo "\t</select></td></tr>\n";
	
				echo "<tr><th class=\"data left\" rowspan=2>{$this->lang['emajenterlogidxtsp']}</th>";
				echo "<td class=\"data1\"><input type=\"text\" id=\"logidxtspInput\" name=\"logidxtsp\" value=\"\"/></td></tr>\n";
				echo "<tr><td><select id=\"logidxtspList\" name=\"logidxtsp1\">\n";
				echo "\t\t<option value=\"new_log_idx_tsp\">{$this->lang['emajnewtsp']}</option>\n";
				if ($knownTsp->recordCount() > 0) {
					foreach($knownTsp as $r)
						echo "\t\t<option value=\"{$r['spcname']}\">{$r['spcname']}</option>\n";
				}
				echo "\t</select></td></tr>\n";
			} else {
				echo "<p><input type=\"hidden\" name=\"logdattsp\" value=\"\" />\n";
				echo "<p><input type=\"hidden\" name=\"logidxtsp\" value=\"\" />\n";
			}
		} else {
			echo "<p><input type=\"hidden\" name=\"logdattsp\" value=\"\" />\n";
			echo "<p><input type=\"hidden\" name=\"logidxtsp\" value=\"\" />\n";
		};
		echo "</table>\n";

		echo "<p><span style=\"font-size: smaller;\">(1) </span>{$this->lang['emajrequired']}<br><span style=\"font-size: smaller;\">(2) </span> {$this->lang['emajpriorityhelp']}";
		if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
			if ($nbTbl >= 1) {
				echo "<br><span style=\"font-size: smaller;\">(3) </span>{$this->lang['emajlogschemahelp']}";
			}
		}
		if ($this->emajdb->getNumEmajVersion() >= 10200) {			// version >= 1.2.0
			if ($nbTbl == 1) {
				echo "<br><span style=\"font-size: smaller;\">(4) </span>{$this->lang['emajnameprefixhelp']}";
			}
		}
		echo"</p>\n";
		echo $misc->form;
		echo "<p><input type=\"submit\" name=\"assigntblseq\" value=\"{$this->lang['emajassign']}\" id=\"ok\" disabled=\"disabled\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		echo "<script type=\"text/javascript\">\n";
		// JQuery script to only enable the submit button when the group name is not empty
		echo "  $(\"#groupInput\").keyup(function (data) {\n";
		echo "    if ($(this).val() != \"\") { $(\"#ok\").removeAttr(\"disabled\"); }\n";
		echo "      else { $(\"#ok\").attr(\"disabled\", \"disabled\"); }\n";
		echo "  });\n";
		// JQuery script to link input fields and associated list boxes
		echo "  $(\"#groupList\").change(function () { $(\"#groupInput\").val($(\"#groupList option:selected\").text()); $(\"#ok\").removeAttr(\"disabled\");});\n";
		echo "  $(\"#groupInput\").keyup(function () { $(\"#groupList option:first-child\").attr(\"selected\", true); });\n";
		echo "  $(\"#suffixList\").change(function () { $(\"#suffixInput\").val($(\"#suffixList option:selected\").text()); });\n";
		echo "  $(\"#suffixInput\").keyup(function () { $(\"#suffixList option:first-child\").attr(\"selected\", true); });\n";
		echo "  $(\"#logdattspList\").change(function () { $(\"#logdattspInput\").val($(\"#logdattspList option:selected\").text()); });\n";
		echo "  $(\"#logdattspInput\").keyup(function () { $(\"#logdattspList option:first-child\").attr(\"selected\", true); });\n";
		echo "  $(\"#logidxtspList\").change(function () { $(\"#logidxtspInput\").val($(\"#logidxtspList option:selected\").text()); });\n";
		echo "  $(\"#logidxtspInput\").keyup(function () { $(\"#logidxtspList option:first-child\").attr(\"selected\", true); });\n";
		echo "</script>";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform table/sequence insertion into a tables group
	 */
	function assign_tblseq_ok() {
		global $lang, $data;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {$this->configure_groups(); exit();}

		if (is_array($_POST['tblseq'])) {
		// multiple assignement
			$status = $data->beginTransaction();
			if ($status == 0) {
				for($i = 0; $i < sizeof($_POST['tblseq']); ++$i)
				{
					$status = $this->emajdb->assignTblSeq($_POST['appschema'][$i],$_POST['tblseq'][$i],$_POST['group'],
								$_POST['priority'], $_POST['suffix'], $_POST['nameprefix'], $_POST['logdattsp'], $_POST['logidxtsp']);
					if ($status != 0) {
						$data->endTransaction();
						configure_groups('',$this->lang['emajmodifygrouperr']);
						return;
					}
				}
			}
			if($data->endTransaction() == 0)
				$this->configure_groups($this->lang['emajmodifygroupok']);
			else
				$this->configure_groups('',$this->lang['emajmodifygrouperr']);

		} else {

		// single assignement
			$status = $this->emajdb->assignTblSeq($_POST['appschema'],$_POST['tblseq'],$_POST['group'],
								$_POST['priority'], $_POST['suffix'], $_POST['nameprefix'], $_POST['logdattsp'], $_POST['logidxtsp']);
			if ($status == 0)
				$this->configure_groups($this->lang['emajmodifygroupok']);
			else
				$this->configure_groups('',$this->lang['emajmodifygrouperr']);
		}
	}

	/**
	 * Prepare update a table/sequence into a group: ask for properties and confirmation
	 */
	function update_tblseq() {
		global $misc, $lang;

		// Test at least 1 table/sequence is to be processed
		if (empty($_REQUEST['tblseq'])) {
			$this->configure_groups($this->lang['emajspecifytblseqtoupdate']);
			exit();
		}
		// Test the table/sequence is already assign to a group
		if ($_REQUEST['group'] == '') {
			$this->configure_groups('',sprintf($this->lang['emajtblseqnogroup'],$_REQUEST['appschema'],$_REQUEST['tblseq']));
			exit();
		}

		$this->printPageHeader('emaj','emajconfiguregroups');

		$misc->printTitle($this->lang['emajupdatetblseq']);

		// Get group names already known in emaj_group_def table
		$knownGroups = $this->emajdb->getKnownGroups();

		// Get log schema suffix already known in emaj_group_def table
		if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
			$knownSuffix = $this->emajdb->getKnownSuffix();
		}

		// Get tablespaces the current user can see
		if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
			$knownTsp = $this->emajdb->getKnownTsp();
		}

		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"update_tblseq_ok\" />\n";

		echo "<input type=\"hidden\" name=\"appschema\" value=\"", htmlspecialchars($_REQUEST['appschema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tblseq\" value=\"", htmlspecialchars($_REQUEST['tblseq']), "\" />\n";
		echo "<input type=\"hidden\" name=\"groupold\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";

		echo "<p>", sprintf($this->lang['emajconfirmupdatetblseq'], "{$_REQUEST['appschema']}.{$_REQUEST['tblseq']}"), "</p>\n";

		// Display the input fields depending on the context
		echo "<table>\n";
		echo "<tr><th class=\"data left required\" rowspan=2>{$this->lang['emajgroup']}</th>";
		echo "<td class=\"data1\"><input id=\"groupInput\" type=\"text\" name=\"groupnew\" value=\"{$_REQUEST['group']}\"/><span style=\"font-size: smaller; vertical-align: super;\"> (1)</span></td></tr>\n";
		echo "<tr><td><select id=\"groupList\" name=\"group1\"><option value=\"new_group\">{$this->lang['emajnewgroup']}</option>\n";
		if ($knownGroups->recordCount() > 0) {
			foreach($knownGroups as $r)
				echo "<option value=\"{$r['group_name']}\">{$r['group_name']}</option>\n";
		}
		echo "</select></td></tr>\n";
		// mask-pnum class is used by jquery.filter to only accept digits
		echo "<tr><th class=\"data left\">{$this->lang['emajenterpriority']}</th>";
		echo "<td class=\"data1\"><input type=\"text\" name=\"priority\" size=9 maxlength=9 style=\"text-align: right;\" value=\"{$_REQUEST['priority']}\" class=\"mask-pnum\"/> <span style=\"font-size: smaller; vertical-align: super;\"> (2)</span></td></tr>\n";

		if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
			if ($_REQUEST['type'] == 'r+') {
				echo "<tr><th class=\"data left\" rowspan=2>{$this->lang['emajenterlogschema']}</th>";
				echo "<td class=\"data1\"><input type=\"text\" id=\"suffixInput\" name=\"suffix\" value=\"{$_REQUEST['logschemasuffix']}\"/><span style=\"font-size: smaller; vertical-align: super;\"> (3)</span></td></tr>\n";
				echo "<tr><td><select id=\"suffixList\" name=\"suffix1\">\n";
				echo "\t\t<option value=\"new_log_schema_suffix\">{$this->lang['emajnewsuffix']}</option>\n";
				if ($knownSuffix->recordCount() > 0) {
					foreach($knownSuffix as $r)
						echo "\t\t<option value=\"{$r['known_suffix']}\">{$r['known_suffix']}</option>\n";
				}
				echo "\t</select></td></tr>\n";
			} else {
				echo "<p><input type=\"hidden\" name=\"suffix\" value=\"\" />\n";
			}
		} else {
			echo "<p><input type=\"hidden\" name=\"suffix\" value=\"\" />\n";
		}

		if ($this->emajdb->getNumEmajVersion() >= 10200) {			// version >= 1.2.0
			if ($_REQUEST['type'] == 'r+') {
				// the names prefix is accessible only for a single table assignment
				echo "<tr><th class=\"data left\">{$this->lang['emajenternameprefix']}</th>";
				echo "<td class=\"data1\"><input type=\"text\" id=\"nameprefixInput\" name=\"nameprefix\" value=\"{$_REQUEST['emajnamesprefix']}\"/><span style=\"font-size: smaller; vertical-align: super;\"> (4)</span></td></tr>\n";
			} else {
				echo "<p><input type=\"hidden\" name=\"nameprefix\" value=\"\" />\n";
			}
		} else {
			echo "<p><input type=\"hidden\" name=\"nameprefix\" value=\"\" />\n";
		}

		if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
			if ($_REQUEST['type'] == 'r+') {
				echo "<tr><th class=\"data left\" rowspan=2>{$this->lang['emajenterlogdattsp']}</th>";
				echo "<td class=\"data1\"><input type=\"text\" id=\"logdattspInput\" name=\"logdattsp\" value=\"{$_REQUEST['logdattsp']}\"/></td></tr>\n";
				echo "<tr><td><select id=\"logdattspList\" name=\"logdattsp1\">\n";
				echo "\t\t<option value=\"new_log_dat_tsp\">{$this->lang['emajnewtsp']}</option>\n";
				if ($knownTsp->recordCount() > 0) {
					foreach($knownTsp as $r)
						echo "\t\t<option value=\"{$r['spcname']}\">{$r['spcname']}</option>\n";
				}
				echo "\t</select></td></tr>\n";
	
				echo "<tr><th class=\"data left\" rowspan=2>{$this->lang['emajenterlogidxtsp']}</th>";
				echo "<td class=\"data1\"><input type=\"text\" id=\"logidxtspInput\" name=\"logidxtsp\" value=\"{$_REQUEST['logidxtsp']}\"/></td></tr>\n";
				echo "<tr><td><select id=\"logidxtspList\" name=\"logidxtsp1\">\n";
				echo "\t\t<option value=\"new_log_idx_tsp\">{$this->lang['emajnewtsp']}</option>\n";
				if ($knownTsp->recordCount() > 0) {
					foreach($knownTsp as $r)
						echo "\t\t<option value=\"{$r['spcname']}\">{$r['spcname']}</option>\n";
				}
				echo "\t</select></td></tr>\n";
			} else {
				echo "<p><input type=\"hidden\" name=\"logdattsp\" value=\"\" />\n";
				echo "<p><input type=\"hidden\" name=\"logidxtsp\" value=\"\" />\n";
			}
		} else {
			echo "<p><input type=\"hidden\" name=\"logdattsp\" value=\"\" />\n";
			echo "<p><input type=\"hidden\" name=\"logidxtsp\" value=\"\" />\n";
		};
		echo "</table>\n";

		echo $misc->form;
		echo "<p><span style=\"font-size: smaller;\">(1) </span>{$this->lang['emajrequired']}<br><span style=\"font-size: smaller;\">(2) </span> {$this->lang['emajpriorityhelp']}";
		if ($this->emajdb->getNumEmajVersion() >= 10000) {			// version >= 1.0.0
			if ($_REQUEST['type'] == 'r+') {
				echo "<br><span style=\"font-size: smaller;\">(3) </span>{$this->lang['emajlogschemahelp']}";
			}
		}
		if ($this->emajdb->getNumEmajVersion() >= 10200) {			// version >= 1.2.0
			if ($_REQUEST['type'] == 'r+') {
				echo "<br><span style=\"font-size: smaller;\">(4) </span>{$this->lang['emajnameprefixhelp']}";
			}
		}
		echo"</p>\n";
		echo "<p><input type=\"submit\" name=\"updatetblseq\" value=\"{$lang['strupdate']}\" id=\"ok\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		echo "<script type=\"text/javascript\">\n";
		// JQuery script to only enable the submit button when the group name is not empty
		echo "  $(\"#groupInput\").keyup(function (data) {\n";
		echo "    if ($(this).val() != \"\") { $(\"#ok\").removeAttr(\"disabled\"); }\n";
		echo "      else { $(\"#ok\").attr(\"disabled\", \"disabled\"); }\n";
		echo "  });\n";
		// JQuery script to link input fields and associated list boxes
		echo "  $(\"#groupList\").change(function () { $(\"#groupInput\").val($(\"#groupList option:selected\").text()); $(\"#ok\").removeAttr(\"disabled\");});\n";
		echo "  $(\"#groupInput\").keyup(function () { $(\"#groupList option:first-child\").attr(\"selected\", true); });\n";
		echo "  $(\"#suffixList\").change(function () { $(\"#suffixInput\").val($(\"#suffixList option:selected\").text()); });\n";
		echo "  $(\"#suffixInput\").keyup(function () { $(\"#suffixList option:first-child\").attr(\"selected\", true); });\n";
		echo "  $(\"#logdattspList\").change(function () { $(\"#logdattspInput\").val($(\"#logdattspList option:selected\").text()); });\n";
		echo "  $(\"#logdattspInput\").keyup(function () { $(\"#logdattspList option:first-child\").attr(\"selected\", true); });\n";
		echo "  $(\"#logidxtspList\").change(function () { $(\"#logidxtspInput\").val($(\"#logidxtspList option:selected\").text()); });\n";
		echo "  $(\"#logidxtspInput\").keyup(function () { $(\"#logidxtspList option:first-child\").attr(\"selected\", true); });\n";
		echo "</script>";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform table/sequence insertion into a tables group
	 */
	function update_tblseq_ok() {
		global $lang, $data;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {$this->configure_groups(); exit();}

		$status = $this->emajdb->updateTblSeq($_POST['appschema'],$_POST['tblseq'],$_POST['groupold'],$_POST['groupnew'],
							$_POST['priority'], $_POST['suffix'], $_POST['nameprefix'], $_POST['logdattsp'], $_POST['logidxtsp']);
		if ($status == 0)
			$this->configure_groups($this->lang['emajmodifygroupok']);
		else
			$this->configure_groups('',$this->lang['emajmodifygrouperr']);
	}

	/**
	 * Prepare remove a table/sequence from a group: ask for confirmation
	 */
	function remove_tblSeq() {
		global $misc, $lang;

		// Test at least 1 table/sequence is to be processed
		if (empty($_REQUEST['tblseq']) && empty($_REQUEST['ma'])) {
			$this->configure_groups($this->lang['emajspecifytblseqtoremove']);
			exit();
		}
		// Test all tables/sequences to process are already assigned to a group
		if (isset($_REQUEST['ma'])) {
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				if ($a['group'] == '') {
					$this->configure_groups('',sprintf($this->lang['emajtblseqnogroup'],$a['appschema'],$a['tblseq']));
					exit();
				}
			}
		} else {
			if ($_REQUEST['group'] == '') {
				$this->configure_groups('',sprintf($this->lang['emajtblseqnogroup'],$_REQUEST['appschema'],$_REQUEST['tblseq']));
				exit();
			}
		}

		$this->printPageHeader('emaj','emajconfiguregroups');

		$misc->printTitle($this->lang['emajremovetblseq']);

		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"remove_tblseq_ok\" />\n";

		if (isset($_REQUEST['ma'])) {
		// multiple removal
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				echo '<p>', sprintf($this->lang['emajconfirmremovetblseq'], $misc->printVal($a['appschema']), $misc->printVal($a['tblseq']), $misc->printVal($a['group'])), "</p>\n";
				echo "<input type=\"hidden\" name=\"appschema[]\" value=\"", htmlspecialchars($a['appschema']), "\" />\n";
				echo "<input type=\"hidden\" name=\"tblseq[]\" value=\"", htmlspecialchars($a['tblseq']), "\" />\n";
				echo "<input type=\"hidden\" name=\"group[]\" value=\"", htmlspecialchars($a['group']), "\" />\n";
			}
		}else {

		// single removal
			echo "<p>", sprintf($this->lang['emajconfirmremovetblseq'], $misc->printVal($_REQUEST['appschema']), $misc->printVal($_REQUEST['tblseq']), $misc->printVal($_REQUEST['group'])), "</p>\n";
			echo "<input type=\"hidden\" name=\"appschema\" value=\"", htmlspecialchars($_REQUEST['appschema']), "\" />\n";
			echo "<input type=\"hidden\" name=\"tblseq\" value=\"", htmlspecialchars($_REQUEST['tblseq']), "\" />\n";
			echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		}

		echo $misc->form;
		echo "<input type=\"submit\" name=\"removetblseq\" value=\"{$this->lang['emajremove']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform table/sequence removal from a tables group
	 */
	function remove_tblseq_ok() {
		global $lang, $data;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {$this->configure_groups(); exit();}

		if (is_array($_POST['tblseq'])) {
		// multiple removal
			$status = $data->beginTransaction();
			if ($status == 0) {
				for($i = 0; $i < sizeof($_POST['tblseq']); ++$i)
				{
					$status = $this->emajdb->removeTblSeq($_POST['appschema'][$i],$_POST['tblseq'][$i],$_POST['group'][$i]);
					if ($status != 0) {
						$data->endTransaction();
						$this->configure_groups('',$this->lang['emajmodifygrouperr']);
						return;
					}
				}
			}
			if($data->endTransaction() == 0)
				$this->configure_groups($this->lang['emajmodifygroupok']);
			else
				$this->configure_groups('',$this->lang['emajmodifygrouperr']);

		} else {
		// single removal
			$status = $this->emajdb->removeTblSeq($_POST['appschema'],$_POST['tblseq'],$_POST['group']);

			if ($status == 0)
				$this->configure_groups($this->lang['emajmodifygroupok']);
			else
				$this->configure_groups('',$this->lang['emajmodifygrouperr']);
		}
	}

	/**
	 * Prepare create group: ask for confirmation
	 */
	function create_group() {
		global $misc, $lang;

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajcreateagroup']);

		echo "<p>", sprintf($this->lang['emajconfirmcreategroup'], $misc->printVal($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"create_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "{$this->lang['emajgrouptype']} : ";
		echo "<input type=\"radio\" name=\"grouptype\" value=\"rollbackable\" checked>{$this->lang['emajrollbackable']}";
		echo "<input type=\"radio\" name=\"grouptype\" value=\"auditonly\">{$this->lang['emajauditonly']}\n";
		echo "</p><p>";
		echo "<input type=\"submit\" name=\"creategroup\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform create_group
	 */
	function create_group_ok() {
		global $lang, $_reload_browser;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {$this->show_groups(); exit();}

		$status = $this->emajdb->createGroup($_POST['group'],$_POST['grouptype']=='rollbackable');
		if ($status > 0) {
			$_reload_browser = true;
			$this->show_groups(sprintf($this->lang['emajcreategroupok'],$_POST['group']));
		}else
			$this->show_groups('',sprintf($this->lang['emajcreategrouperr'],$_POST['group']));
	}

	/**
	 * Prepare drop group: ask for confirmation
	 */
	function drop_group() {
		global $misc, $lang;

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajdropagroup']);

		echo "<p>", sprintf($this->lang['emajconfirmdropgroup'], $misc->printVal($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"drop_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"dropgroup\" value=\"{$lang['strdrop']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform drop group
	 */
	function drop_group_ok() {
		global $lang, $_reload_browser;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				$this->show_groups();
			} else {
				$this->show_group();
			}
			exit();
		}

	// Check the group is always in IDLE state
		$group = $this->emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'IDLE') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajcantdropgroup'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcantdropgroup'],$_POST['group']));
			}
			return;
		}

	// OK
		$status = $this->emajdb->dropGroup($_POST['group']);
		if ($status > 0) {
			$_reload_browser = true;
			$this->show_groups(sprintf($this->lang['emajdropgroupok'],$_POST['group']));
		}else
			$this->show_groups('',sprintf($this->lang['emajdropgrouperr'],$_POST['group']));
	}

	/**
	 * Prepare alter group: ask for confirmation
	 */
	function alter_group() {
		global $misc, $lang;

		$this->printPageHeader();
		$misc->printTitle($this->lang['emajalteragroup']);

		$isGroupLogging = $this->emajdb->isGroupLogging($_REQUEST['group']);

		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";

		if ($this->emajdb->getNumEmajVersion() >= 20100) {			// version >= 2.1.0
			if ($isGroupLogging) {
				echo "<p>", sprintf($this->lang['emajalteraloggingroup'], $misc->printVal($_REQUEST['group'])), "</p>";
				echo "<table>\n";
				echo "<tr><th class=\"data left\">{$this->lang['emajmark']}</th>\n";
				echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"\" id=\"mark\"></td></tr>\n";
				echo "</table>\n";
			} else {
				echo "<p>", sprintf($this->lang['emajconfirmaltergroup'], $misc->printVal($_REQUEST['group'])), "</p>\n";
				echo "<input type=\"hidden\" name=\"mark\" value=\"\">";
			}
		} else {
				echo "<p>", sprintf($this->lang['emajconfirmaltergroup'], $misc->printVal($_REQUEST['group'])), "</p>\n";
				echo "<input type=\"hidden\" name=\"mark\" value=\"\">";
		}

		echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"altergroup\" value=\"{$lang['stralter']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform alter group
	 */
	function alter_group_ok() {
		global $lang, $_reload_browser;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				$this->show_groups();
			} else {
				$this->show_group();
			}
			exit();
		}

	// check the group can be altered by looking at its state and operations that will be performed
		$check = $this->emajdb->checkAlterGroup($_REQUEST['group']);
		if ($check == 0) {
			if ($_POST['back'] == 'list') {
				$this->show_groups('',sprintf($this->lang['emajcantaltergroup'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcantaltergroup'],$_POST['group']));
			}
			exit();
		}

	// Check the supplied mark is valid
		if ($_POST['mark'] != '' && !$this->emajdb->isNewMarkValidGroup($_POST['group'],$_POST['mark'])) {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajinvalidmark'],$_POST['mark']));
			} else {
				$this->show_group('',sprintf($this->lang['emajinvalidmark'],$_POST['mark']));
			}
			return;
		}

	// OK
		$status = $this->emajdb->alterGroup($_POST['group'],$_POST['mark']);
		if ($status >= 0) {
			$_reload_browser = true;
			if ($_POST['back'] == 'list') {
				$this->show_groups(sprintf($this->lang['emajaltergroupok'],$_POST['group']));
			} else {
				$this->show_group(sprintf($this->lang['emajaltergroupok'],$_POST['group']));
			}
		}else
			if ($_POST['back'] == 'list') {
				$this->show_groups('',sprintf($this->lang['emajaltergrouperr'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajaltergrouperr'],$_POST['group']));
			}
	}

	/**
	 * Prepare alter groups: ask for confirmation
	 */
	function alter_groups() {
		global $misc, $lang;

		if (!isset($_REQUEST['ma'])) {
			$this->show_groups('',$this->lang['emajnoselectedgroup']);
			return;
		}

		$this->printPageHeader();
		$misc->printTitle($this->lang['emajaltergroups']);

		// build the groups list
		$groupsList='';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList.=$a['group'].', ';
		}
		$groupsList=substr($groupsList,0,strlen($groupsList)-2);

		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";

		if ($this->emajdb->getNumEmajVersion() >= 20100) {			// version >= 2.1.0
			echo "<p>", sprintf($this->lang['emajalterallloggingroups'], $misc->printVal($groupsList)), "</p>";
			echo "<table>\n";
			echo "<tr><th class=\"data left\">{$this->lang['emajmark']}</th>\n";
			echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"\" id=\"mark\"></td></tr>\n";
			echo "</table>\n";
		} else {
			echo "<p>", sprintf($this->lang['emajconfirmaltergroups'], $misc->printVal($groupsList)), "</p>\n";
			echo "<input type=\"hidden\" name=\"mark\" value=\"\">";
		}

		echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"altergroups\" value=\"{$lang['stralter']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform alter groups
	 */
	function alter_groups_ok() {
		global $lang, $_reload_browser;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_groups(); exit(); }

	// check the groups can be altered by looking at their state and operations that will be performed
		$groups = explode(', ',$_POST['groups']);
		foreach($groups as $g) {
			$check = $this->emajdb->checkAlterGroup($g);
			// exit the loop in case of error
			if ($check == 0) {
				if ($_POST['back'] == 'list') {
					$this->show_groups('',sprintf($this->lang['emajcantaltergroup'],$g));
				} else {
					$this->show_group('',sprintf($this->lang['emajcantaltergroup'],$g));
				}
				exit();
			}
		}
	// Check the supplied mark is valid for the groups
		if (!$this->emajdb->isNewMarkValidGroups($_POST['groups'],$_POST['mark'])) {
			$this->show_groups('',sprintf($this->lang['emajinvalidmark'],$_POST['mark']));
			return;
		}

	// OK
		$status = $this->emajdb->alterGroups($_POST['groups'],$_POST['mark']);
		if ($status >= 0) {
			$_reload_browser = true;
			if ($_POST['back'] == 'list') {
				$this->show_groups(sprintf($this->lang['emajaltergroupsok'],$_POST['groups']));
			} else {
				$this->show_group(sprintf($this->lang['emajaltergroupsok'],$_POST['groups']));
			}
		}else
			if ($_POST['back'] == 'list') {
				$this->show_groups('',sprintf($this->lang['emajaltergroupserr'],$_POST['groups']));
			} else {
				$this->show_group('',sprintf($this->lang['emajaltergroupserr'],$_POST['groups']));
			}
	}

	/**
	 * Prepare comment group: ask for comment and confirmation
	 */
	function comment_group() {
		global $misc, $lang;

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajcommentagroup']);

		$group = $this->emajdb->getGroup($_REQUEST['group']);

		echo "<p>", sprintf($this->lang['emajcommentgroup'], $misc->printVal($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left\">{$lang['strcomment']}</th>\n";
		echo "<td class=\"data1\"><input name=\"comment\" size=\"100\" value=\"",
			htmlspecialchars($group->fields['group_comment']), "\" /></td></tr>\n";
		echo "</table>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"comment_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"commentgroup\" value=\"{$lang['strok']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform comment group
	 */
	function comment_group_ok() {
		global $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				$this->show_groups();
			} else {
				$this->show_group();
			}
			exit();
		}

		$status = $this->emajdb->setCommentGroup($_POST['group'],$_POST['comment']);
		if ($status >= 0)
			if ($_POST['back']=='list') {
				$this->show_groups(sprintf($this->lang['emajcommentgroupok'],$_POST['group']));
			} else {
				$this->show_group(sprintf($this->lang['emajcommentgroupok'],$_POST['group']));
			}
		else
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajcommentgrouperr'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcommentgrouperr'],$_POST['group']));
			}
	}

	/**
	 * Prepare start group: enter the initial mark name and confirm
	 */
	function start_group() {
		global $misc, $lang;

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajstartagroup']);

		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p>", sprintf($this->lang['emajconfirmstartgroup'], $misc->printVal($_REQUEST['group'])), "</p>\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left required\" style=\"width: 100px\">{$this->lang['emajinitmark']}</th>\n";
		echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"",
			htmlspecialchars($_POST['mark']), "\" id=\"mark\" /></td></tr>\n";
		echo "</table>\n";
		echo "<p><input type=checkbox name=\"resetlog\" checked/>{$this->lang['emajoldlogsdeletion']}</p>\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"start_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strok']}\" id=\"ok\" disabled=\"disabled\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		echo "<script type=\"text/javascript\">\n";
		echo "  $(document).ready(function () {\n";
		echo "    $(\"#mark\").keyup(function (data) {\n";
		echo "      if ($(this).val() != \"\") {\n";
		echo "        $(\"#ok\").removeAttr(\"disabled\");\n";
		echo "      } else {\n";
		echo "        $(\"#ok\").attr(\"disabled\", \"disabled\");\n";
		echo "      }\n";
		echo "    });\n";
		echo "  });\n";
		echo "</script>";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform start group
	 */
	function start_group_ok() {
		global $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				$this->show_groups();
			} else {
				$this->show_group();
			}
			exit();
		}
		// Check the group is always in IDLE state
		$group = $this->emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'IDLE') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajcantstartgroup'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcantstartgroup'],$_POST['group']));
			}
			return;
		}
		// If old marks are not deleted, check the supplied mark is valid for the group
		if (!isSet($_POST['resetlog']) && !$this->emajdb->isNewMarkValidGroup($_POST['group'],$_POST['mark'])) {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajinvalidmark'],$_POST['mark']));
			} else {
				$this->show_group('',sprintf($this->lang['emajinvalidmark'],$_POST['mark']));
			}
			return;
		}
		// OK
		$status = $this->emajdb->startGroup($_POST['group'],$_POST['mark'],isSet($_POST['resetlog']));
		if ($status > 0)
			if ($_POST['back']=='list') {
				$this->show_groups(sprintf($this->lang['emajstartgroupok'],$_POST['group'],$_POST['mark']));
			} else {
				$this->show_group(sprintf($this->lang['emajstartgroupok'],$_POST['group'],$_POST['mark']));
			}
		else
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajstartgrouperr'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajstartgrouperr'],$_POST['group']));
			}
	}

	/**
	 * Prepare start groups: enter the initial mark name and confirm
	 */
	function start_groups() {
		global $misc, $lang;

		if (!isset($_REQUEST['ma'])) {
			$this->show_groups('',$this->lang['emajnoselectedgroup']);
			return;
		}
		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajstartgroups']);

		// build the groups list
		$groupsList='';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList.=$a['group'].', ';
		}
		$groupsList=substr($groupsList,0,strlen($groupsList)-2);
		// send form
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p>", sprintf($this->lang['emajconfirmstartgroups'], $misc->printVal($groupsList)), "</p>\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left required\" style=\"width: 100px\">{$this->lang['emajinitmark']}</th>\n";
		echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"",
			htmlspecialchars($_POST['mark']), "\" id=\"mark\" /></td></tr>\n";
		echo "</table>\n";
		echo "<p><input type=checkbox name=\"resetlog\" checked/>{$this->lang['emajoldlogsdeletion']}</p>\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"start_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strok']}\" id=\"ok\" disabled=\"disabled\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		echo "<script type=\"text/javascript\">\n";
		echo "  $(document).ready(function () {\n";
		echo "    $(\"#mark\").keyup(function (data) {\n";
		echo "      if ($(this).val() != \"\") {\n";
		echo "        $(\"#ok\").removeAttr(\"disabled\");\n";
		echo "      } else {\n";
		echo "        $(\"#ok\").attr(\"disabled\", \"disabled\");\n";
		echo "      }\n";
		echo "    });\n";
		echo "  });\n";
		echo "</script>";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform start groups
	 */
	function start_groups_ok() {
		global $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_groups(); exit(); }

		// Check the groups are always in IDLE state
		$groups=explode(', ',$_POST['groups']);
		foreach($groups as $g) {
			if ($this->emajdb->getGroup($g)->fields['group_state'] != 'IDLE') {
				$this->show_groups('',sprintf($this->lang['emajcantstartgroups'],$_POST['groups'],$g));
				return;
			}
		}
		// If old marks are not deleted, check the supplied mark is valid for the groups
		if (!isSet($_POST['resetlog']) && !$this->emajdb->isNewMarkValidGroups($_POST['groups'],$_POST['mark'])) {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajinvalidmark'],$_POST['mark']));
			} else {
				$this->show_group('',sprintf($this->lang['emajinvalidmark'],$_POST['mark']));
			}
			return;
		}
		// OK
		$status = $this->emajdb->startGroups($_POST['groups'],$_POST['mark'],isSet($_POST['resetlog']));
		if ($status > 0)
			if ($_POST['back']=='list')
				$this->show_groups(sprintf($this->lang['emajstartgroupsok'],$_POST['groups'],$_POST['mark']));
		else
			if ($_POST['back']=='list')
				$this->show_groups('',sprintf($this->lang['emajstartgroupserr'],$_POST['groups']));
	}

	/**
	 * Prepare stop group: ask for confirmation
	 */
	function stop_group() {
		global $misc, $lang;

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajstopagroup']);

		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p>", sprintf($this->lang['emajconfirmstopgroup'], $misc->printVal($_REQUEST['group'])), "</p>\n";
		if ($this->emajdb->getNumEmajVersion() >= 10000) {					// version >= 1.0.0
			echo "<table>\n";
			echo "<tr><th class=\"data left\" style=\"width: 100px\">{$this->lang['emajstopmark']}</th>\n";
			echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"STOP_%\" /></td></tr>\n";
			echo "</table>\n";
			echo "<p><input type=checkbox name=\"forcestop\" />{$this->lang['emajforcestop']}</p>\n";
		} else {
			echo "<input type=\"hidden\" name=\"mark\" value=\"\" />\n";
		}
		echo "<p><input type=\"hidden\" name=\"action\" value=\"stop_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"stopgroup\" value=\"{$lang['strok']}\"/>\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform stop_group
	 */
	function stop_group_ok() {
		global $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				$this->show_groups();
			} else {
				$this->show_group();
			}
			exit();
		}
		// Check the group is always in LOGGING state
		$group = $this->emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajcantstopgroup'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcantstopgroup'],$_POST['group']));
			}
			return;
		}
		// OK
		$status = $this->emajdb->stopGroup($_POST['group'],$_POST['mark'],isSet($_POST['forcestop']));
		if ($status == 0)
			if ($_POST['back']=='list') {
				$this->show_groups(sprintf($this->lang['emajstopgroupok'],$_POST['group']));
			} else {
				$this->show_group(sprintf($this->lang['emajstopgroupok'],$_POST['group']));
			}
		else
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajstopgrouperr'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajstopgrouperr'],$_POST['group']));
			}
	}

	/**
	 * Prepare stop group: ask for confirmation
	 */
	function stop_groups() {
		global $misc, $lang;

		if (!isset($_REQUEST['ma'])) {
			$this->show_groups('',$this->lang['emajnoselectedgroup']);
			return;
		}

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajstopgroups']);

		// build the groups list
		$groupsList='';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList.=$a['group'].', ';
		}
		$groupsList=substr($groupsList,0,strlen($groupsList)-2);
		// send form
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p>", sprintf($this->lang['emajconfirmstopgroups'], $misc->printVal($groupsList)), "</p>\n";
		if ($this->emajdb->getNumEmajVersion() >= 10000) {					// version >= 1.0.0
			echo "<table>\n";
			echo "<tr><th class=\"data left\" style=\"width: 100px\">{$this->lang['emajstopmark']}</th>\n";
			echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"STOP_%\" /></td></tr>\n";
			echo "</table>\n";
		} else {
			echo "<input type=\"hidden\" name=\"mark\" value=\"\" />\n";
		}
		echo "<p><input type=\"hidden\" name=\"action\" value=\"stop_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"stopgroups\" value=\"{$lang['strok']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform stop_groups
	 */
	function stop_groups_ok() {
		global $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_groups(); exit(); }

		// Check the groups are always in LOGGING state
		$groups=explode(', ',$_POST['groups']);
		foreach($groups as $g) {
			if ($this->emajdb->getGroup($g)->fields['group_state'] != 'LOGGING') {
				$this->show_groups('',sprintf($this->lang['emajcantstopgroups'],$_POST['groups'],$g));
				return;
			}
		}
		// OK
		$status = $this->emajdb->stopGroups($_POST['groups'],$_POST['mark']);
		if ($status > 0)
			$this->show_groups(sprintf($this->lang['emajstopgroupsok'],$_POST['groups']));
		else
			$this->show_groups('',sprintf($this->lang['emajstopgroupserr'],$_POST['groups']));
	}

	/**
	 * Prepare reset group: ask for confirmation
	 */
	function reset_group() {
		global $misc, $lang;

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajresetagroup']);

		echo "<p>", sprintf($this->lang['emajconfirmresetgroup'], $misc->printVal($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"reset_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"resetgroup\" value=\"{$lang['strok']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform reset group
	 */
	function reset_group_ok() {
		global $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				$this->show_groups();
			} else {
				$this->show_group();
			}
			exit();
		}
		// Check the group is always in IDLE state
		$group = $this->emajdb->getGroup($_POST['group']);
		if ($group->fields['group_state'] != 'IDLE') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajcantresetgroup'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcantresetgroup'],$_POST['group']));
			}
			return;
		}
		// OK
		$status = $this->emajdb->resetGroup($_POST['group']);
		if ($status > 0)
			if ($_POST['back']=='list') {
				$this->show_groups(sprintf($this->lang['emajresetgroupok'],$_POST['group']));
			} else {
				$this->show_group(sprintf($this->lang['emajresetgroupok'],$_POST['group']));
			}
		else
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajresetgrouperr'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajresetgrouperr'],$_POST['group']));
			}
	}

	/**
	 * Execute protect group (there is no confirmation to ask)
	 */
	function protect_group() {
		global $lang;

		// Check the group is always in LOGGING state
		$group = $this->emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_REQUEST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajcantprotectgroup'],$_REQUEST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcantprotectgroup'],$_REQUEST['group']));
			}
			return;
		}
		// OK
		$status = $this->emajdb->protectGroup($_REQUEST['group']);
		if ($_REQUEST['back']=='list') {
			$this->show_groups(sprintf($this->lang['emajprotectgroupok'],$_REQUEST['group']));
		} else {
			$this->show_group(sprintf($this->lang['emajprotectgroupok'],$_REQUEST['group']));
		}
	}

	/**
	 * Execute unprotect group (there is no confirmation to ask)
	 */
	function unprotect_group() {
		global $lang;

		// Check the group is always in LOGGING state
		$group = $this->emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_REQUEST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajcantunprotectgroup'],$_REQUEST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcantunprotectgroup'],$_REQUEST['group']));
			}
			return;
		}
		// OK
		$status = $this->emajdb->unprotectGroup($_REQUEST['group']);
		if ($_REQUEST['back']=='list') {
			$this->show_groups(sprintf($this->lang['emajunprotectgroupok'],$_REQUEST['group']));
		} else {
			$this->show_group(sprintf($this->lang['emajunprotectgroupok'],$_REQUEST['group']));
		}
	}

	/**
	 * Prepare set mark group: ask for the mark name and confirmation
	 */
	function set_mark_group() {
		global $misc, $lang;

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajsetamark']);

		echo "<p>", sprintf($this->lang['emajconfirmsetmarkgroup'], $misc->printVal($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left required\">{$this->lang['emajmark']}</th>\n";
		echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"",
			htmlspecialchars($_POST['mark']), "\" id=\"mark\"/></td></tr>\n";
		echo "</table>\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"set_mark_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strok']}\" id=\"ok\" disabled=\"disabled\"/>\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		echo "<script type=\"text/javascript\">\n";
		echo "  $(document).ready(function () {\n";
		echo "    $(\"#mark\").keyup(function (data) {\n";
		echo "      if ($(this).val() != \"\") {\n";
		echo "        $(\"#ok\").removeAttr(\"disabled\");\n";
		echo "      } else {\n";
		echo "        $(\"#ok\").attr(\"disabled\", \"disabled\");\n";
		echo "      }\n";
		echo "    });\n";
		echo "  });\n";
		echo "</script>";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform set mark group
	 */
	function set_mark_group_ok() {
		global $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				$this->show_groups();
			} else {
				$this->show_group();
			}
			exit();
		}
		// Check the group is always in LOGGING state
		$group = $this->emajdb->getGroup($_POST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajcantsetmarkgroup'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcantsetmarkgroup'],$_POST['group']));
			}
			return;
		}
		// Check the supplied mark group is valid
		if (!$this->emajdb->isNewMarkValidGroup($_POST['group'],$_POST['mark'])) {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajinvalidmark'],$_POST['mark']));
			} else {
				$this->show_group('',sprintf($this->lang['emajinvalidmark'],$_POST['mark']));
			}
			return;
		}
		// OK
		$status = $this->emajdb->setMarkGroup($_POST['group'],$_POST['mark']);
		if ($status == 0)
			if ($_POST['back']=='list') {
				$this->show_groups(sprintf($this->lang['emajsetmarkgroupok'],$_POST['mark'],$_POST['group']));
			} else {
				$this->show_group(sprintf($this->lang['emajsetmarkgroupok'],$_POST['mark'],$_POST['group']));
			}
		else
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajsetmarkgrouperr'],$_POST['mark'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajsetmarkgrouperr'],$_POST['mark'],$_POST['group']));
			}
	}

	/**
	 * Prepare set mark groups: ask for the mark name and confirmation
	 */
	function set_mark_groups() {
		global $misc, $lang;

		if (!isset($_REQUEST['ma'])) {
			$this->show_groups('',$this->lang['emajnoselectedgroup']);
			return;
		}
		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajsetamark']);

		// build the groups list
		$groupsList='';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList.=$a['group'].', ';
		}
		$groupsList=substr($groupsList,0,strlen($groupsList)-2);
		echo "<p>", sprintf($this->lang['emajconfirmsetmarkgroup'], $misc->printVal($groupsList)), "</p>\n";
		// send form
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left required\">{$this->lang['emajmark']}</th>\n";
		echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"",
			htmlspecialchars($_POST['mark']), "\" id=\"mark\"/></td></tr>\n";
		echo "</table>\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"set_mark_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strok']}\" id=\"ok\" disabled=\"disabled\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		echo "<script type=\"text/javascript\">\n";
		echo "  $(document).ready(function () {\n";
		echo "    $(\"#mark\").keyup(function (data) {\n";
		echo "      if ($(this).val() != \"\") {\n";
		echo "        $(\"#ok\").removeAttr(\"disabled\");\n";
		echo "      } else {\n";
		echo "        $(\"#ok\").attr(\"disabled\", \"disabled\");\n";
		echo "      }\n";
		echo "    });\n";
		echo "  });\n";
		echo "</script>";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform set mark groups
	 */
	function set_mark_groups_ok() {
		global $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_groups(); exit(); }

		// Check the groups are always in LOGGING state
		$groups=explode(', ',$_POST['groups']);
		foreach($groups as $g) {
			if ($this->emajdb->getGroup($g)->fields['group_state'] != 'LOGGING') {
				$this->show_groups('',(sprintf($this->lang['emajcantsetmarkgroups'],$_POST['groups'],$g)));
				return;
			}
		}
		// Check the supplied mark group is valid
		if (!$this->emajdb->isNewMarkValidGroups($_POST['groups'],$_POST['mark'])) {
			$this->show_groups('',sprintf($this->lang['emajinvalidmark'],$_POST['mark']));
			return;
		}
		// OK
		$status = $this->emajdb->setMarkGroups($_POST['groups'],$_POST['mark']);
		if ($status == 0)
			$this->show_groups(sprintf($this->lang['emajsetmarkgroupok'],$_POST['mark'],$_POST['groups']));
		else
			$this->show_groups('',sprintf($this->lang['emajsetmarkgrouperr'],$_POST['mark'],$_POST['groups']));
	}

	/**
	 * Execute protect mark (there is no confirmation to ask)
	 */
	function protect_mark_group() {
		global $lang;

		// Check the group is always in LOGGING state
		$group = $this->emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_REQUEST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajcantprotectmarkgroup'],$_REQUEST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcantprotectmarkgroup'],$_REQUEST['group']));
			}
			return;
		}
		// OK
		$status = $this->emajdb->protectMarkGroup($_REQUEST['group'],$_REQUEST['mark']);
		if ($_REQUEST['back']=='list') {
			$this->show_groups(sprintf($this->lang['emajprotectmarkgroupok'],$_REQUEST['group'],$_REQUEST['mark']));
		} else {
			$this->show_group(sprintf($this->lang['emajprotectmarkgroupok'],$_REQUEST['group'],$_REQUEST['mark']));
		}
	}

	/**
	 * Execute unprotect mark (there is no confirmation to ask)
	 */
	function unprotect_mark_group() {
		global $lang;

		// Check the group is always in LOGGING state
		$group = $this->emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_REQUEST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajcantunprotectmarkgroup'],$_REQUEST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcantunprotectmarkgroup'],$_REQUEST['group']));
			}
			return;
		}
		// OK
		$status = $this->emajdb->unprotectMarkGroup($_REQUEST['group'],$_REQUEST['mark']);
		if ($_REQUEST['back']=='list') {
			$this->show_groups(sprintf($this->lang['emajunprotectmarkgroupok'],$_REQUEST['group'],$_REQUEST['mark']));
		} else {
			$this->show_group(sprintf($this->lang['emajunprotectmarkgroupok'],$_REQUEST['group'],$_REQUEST['mark']));
		}
	}

	/**
	 * Prepare comment mark group: ask for comment and confirmation
	 */
	function comment_mark_group() {
		global $misc, $lang;

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajcommentamark']);

		$mark = $this->emajdb->getMark($_REQUEST['group'],$_REQUEST['mark']);

		echo "<p>", sprintf($this->lang['emajcommentmark'], $misc->printVal($_REQUEST['mark']), $misc->printVal($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left\">{$lang['strcomment']}</th>\n";
		echo "<td class=\"data1\"><input name=\"comment\" size=\"100\" value=\"",
			htmlspecialchars($mark->fields['mark_comment']), "\" /></td></tr>\n";
		echo "</table>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"comment_mark_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"commentmarkgroup\" value=\"{$lang['strok']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform comment mark group
	 */
	function comment_mark_group_ok() {
		global $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_group(); exit(); }

		$status = $this->emajdb->setCommentMarkGroup($_POST['group'],$_POST['mark'],$_POST['comment']);
		if ($status >= 0)
			$this->show_group(sprintf($this->lang['emajcommentmarkok'],$_POST['mark'],$_POST['group']));
		else
			$this->show_group('',sprintf($this->lang['emajcommentmarkerr'],$_POST['mark'],$_POST['group']));
	}

	/**
	 * Prepare rollback group: ask for confirmation
	 */
	function rollback_group() {
		global $misc, $lang;

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajrlbkagroup']);

		echo "<style type=\"text/css\">[disabled]{color:#933;}</style>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_group_confirm_alter\" />\n";

		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		if (isset($_REQUEST['mark'])) {
		// the mark name is already defined (we are coming from the 'detail group' page)
			echo "<p>", sprintf($this->lang['emajconfirmrlbkgroup'], $misc->printVal($_REQUEST['group']), $misc->printVal($_REQUEST['mark'])), "</p>\n";
			echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		} else {
		// the mark name is not yet defined (we are coming from the 'list groups' page)
			$marks=$this->emajdb->getRollbackMarkGroup($_REQUEST['group']);
			echo sprintf($this->lang['emajselectmarkgroup'], $misc->printVal($_REQUEST['group']));
			echo "<select name=\"mark\">\n";
			$optionDisabled = '';
			foreach($marks as $m) {
				echo "<option value=\"{$m['mark_name']}\" $optionDisabled>{$m['mark_name']} ({$m['mark_datetime']})</option>\n";
				// if the mark is protected against rollback, disabled the next ones
				if ($m['mark_is_rlbk_protected'] == 't') $optionDisabled = 'disabled';
			}
			echo "</select></p><p>\n";
		}
		echo $misc->form;
		echo "{$this->lang['emajrollbacktype']} : ";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"unlogged\" checked>{$this->lang['emajunlogged']}";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"logged\">{$this->lang['emajlogged']}\n";
		echo "</p><p>";
		echo "<input type=\"submit\" name=\"rollbackgroup\" value=\"{$this->lang['emajrlbk']}\" />\n";
		if ($this->emajdb->getNumEmajVersion() >= 10100) {	// version >= 1.1.0
			if ($this->emajdb->isAsyncRlbkUsable($this->conf) ) {
				echo "<input type=\"submit\" name=\"async\" value=\"{$this->lang['emajrlbkthenmonitor']}\" />\n";
			}
		}
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Ask the user to confirm a rollback targeting a mark set prior alter_group operations
	 */
	function rollback_group_confirm_alter() {
		global $lang, $misc;

		if ($this->emajdb->getNumEmajVersion() < 20100) {	// version < 2.1.0) {
			// for emaj version prior 2.1, directly go call the function that executes the rollback
			$this->rollback_group_ok();
		} else {
			// check that the rollback would not reach a mark set before any alter group operation

			// process the click on the <cancel> button
			if (isset($_POST['cancel'])) {
				if ($_POST['back'] == 'list') {
					$this->show_groups();
				} else {
					$this->show_group();
				}
				exit();
			}
			// Check the group is always in LOGGING state and ROLLBACKABLE (i.e. not protected)
			$group = $this->emajdb->getGroup($_POST['group']);
			if ($group->fields['group_state'] != 'LOGGING') {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($this->lang['emajcantrlbkidlegroup'],$_POST['group']));
				} else {
					$this->show_group('',sprintf($this->lang['emajcantrlbkidlegroup'],$_POST['group']));
				}
				return;
			}
			if ($group->fields['group_type'] != 'ROLLBACKABLE') {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($this->lang['emajcantrlbkprotgroup'],$_POST['group']));
				} else {
					$this->show_group('',sprintf($this->lang['emajcantrlbkprotgroup'],$_POST['group']));
				}
				return;
			}
			// Check the mark is always valid for a rollback
			if (!$this->emajdb->isRollbackMarkValidGroup($_POST['group'],$_POST['mark'])) {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($this->lang['emajcantrlbkinvalidmarkgroup'],$_POST['group'],$_POST['mark']));
				} else {
					$this->show_group('',sprintf($this->lang['emajcantrlbkinvalidmarkgroup'],$_POST['group'],$_POST['mark']));
				}
				return;
			}
	
			$alterGroupSteps = $this->emajdb->getAlterAfterMarkGroups($_POST['group'],$_POST['mark'],$this->lang);

			if ($alterGroupSteps->recordCount() > 0) {
				// there are alter_group operation to cross over, so ask for a confirmation

				$columns = array(
					'time' => array(
						'title' => $this->lang['emajtimestamp'],
						'field' => field('time_tx_timestamp'),
					),
					'step' => array(
						'title' => $lang['straction'],
						'field' => field('altr_action'),
					),
					'autorollback' => array(
						'title' => $this->lang['emajautorolledback'],
						'field' => field('altr_auto_rolled_back'),
						'type'	=> 'callback',
						'params'=> array('function' => array($this, 'renderBooleanIcon'),'align' => 'center')
					),
				);
	
				$actions = array ();
	
				$this->printPageHeader();

				$misc->printTitle($this->lang['emajrlbkagroup']);

				echo "<p>" . sprintf($this->lang['emajreachaltergroup'],$_REQUEST['group'], $_REQUEST['mark']) . "</p>\n";

//				$misc->printTable($alterGroupSteps, $columns, $actions, 'alterGroupStep');
				echo "<div id=\"alterGroupStep\" style=\"margin-top:15px;margin-bottom:15px\" >\n";
				$this->printTable($alterGroupSteps, $columns, $actions, 'alterGroupStep');
				echo "</div>\n";
	
				echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
				echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_group_ok\" />\n";
				echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
				echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
				echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
				echo "<input type=\"hidden\" name=\"rollbacktype\"", htmlspecialchars($_REQUEST['rollbacktype']), "\" />\n";
				if (isset($_POST['async'])) {
					echo "<input type=\"hidden\" name=\"async\"", htmlspecialchars($_REQUEST['async']), "\" />\n";
				}
				echo $misc->form;
				echo "<input type=\"submit\" name=\"rollbackgroup\" value=\"{$lang['strconfirm']}\" />\n";
				echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
				echo "</form>\n";

				$this->printEmajFooter();
				$misc->printFooter();

			} else {
				// otherwise, directly execute the rollback
				$this->rollback_group_ok();
			}
		}
	}

	/**
	 * Perform rollback_group
	 */
	function rollback_group_ok() {
		global $lang, $misc;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				$this->show_groups();
			} else {
				$this->show_group();
			}
			exit();
		}
		// Check the group is always in LOGGING state and ROLLBACKABLE (i.e. not protected)
		$group = $this->emajdb->getGroup($_POST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajcantrlbkidlegroup'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcantrlbkidlegroup'],$_POST['group']));
			}
			return;
		}
		if ($group->fields['group_type'] != 'ROLLBACKABLE') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajcantrlbkprotgroup'],$_POST['group']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcantrlbkprotgroup'],$_POST['group']));
			}
			return;
		}
		// Check the mark is always valid for a rollback
		if (!$this->emajdb->isRollbackMarkValidGroup($_POST['group'],$_POST['mark'])) {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajcantrlbkinvalidmarkgroup'],$_POST['group'],$_POST['mark']));
			} else {
				$this->show_group('',sprintf($this->lang['emajcantrlbkinvalidmarkgroup'],$_POST['group'],$_POST['mark']));
			}
			return;
		}

		if (isset($_POST['async'])) {
		// perform the rollback in asynchronous mode and switch to the rollback monitoring page

			$psqlExe = $misc->escapeShellCmd($this->conf['psql_path']);

			// re-check the psql exe path and the temp directory supplied in the config file
			$version = array();
			preg_match("/(\d+(?:\.\d+)?)(?:\.\d+)?.*$/", exec($psqlExe . " --version"), $version);
			if (empty($version)) {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($this->lang['emajbadpsqlpath'], $this->conf['psql_path']));
				} else {
					$this->show_group('',sprintf($this->lang['emajbadpsqlpath'], $this->conf['psql_path']));
				}
				exit;
			}

			// re-check the file can be written into the temp directory supplied in the config file 
			$sep = (substr(php_uname(), 0, 3) == "Win") ? '\\' : '/';
			$testFileName = $this->conf['temp_dir'] . $sep . 'rlbk_report_test';
			$f = fopen($testFileName,'w');
			if (!$f) {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($this->lang['emajbadtempdir'], $this->conf['temp_dir']));
				} else {
					$this->show_group('',sprintf($this->lang['emajbadtempdir'], $this->conf['temp_dir']));
				}
				exit;
			} else {
				fclose($f);
				unlink($testFileName);
			}

			$rlbkId = $this->emajdb->asyncRollbackGroups($_POST['group'],$_POST['mark'],$_POST['rollbacktype']=='logged', $psqlExe, $this->conf['temp_dir'].$sep, false);
			$this->show_rollbacks(sprintf($this->lang['emajasyncrlbkstarted'],$rlbkId));
			exit;
		}

		// perform the rollback in regular synchronous mode

		if (!ini_get('safe_mode')) set_time_limit(0);		// Prevent timeouts on large rollbacks (non-safe mode only)

		if (isset($_POST['rollbacktype'])) {
			$status = $this->emajdb->rollbackGroup($_POST['group'],$_POST['mark'],$_POST['rollbacktype']=='logged');
		} else {
			$status = $this->emajdb->rollbackGroup($_POST['group'],$_POST['mark'],false);
		}
		if ($status >= 0) {
			if ($_POST['back']=='list') {
				$this->show_groups(sprintf($this->lang['emajrlbkgroupok'],$_POST['group'],$_POST['mark']));
			} else {
				$this->show_group(sprintf($this->lang['emajrlbkgroupok'],$_POST['group'],$_POST['mark']));
			}
		} else {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($this->lang['emajrlbkgrouperr'],$_POST['group'],$_POST['mark']));
			} else {
				$this->show_group('',sprintf($this->lang['emajrlbkgrouperr'],$_POST['group'],$_POST['mark']));
			}
		}
	}

	/**
	 * Prepare rollback groups: ask for confirmation
	 */
	function rollback_groups() {
		global $misc, $lang;

		if (!isset($_REQUEST['ma'])) {
		// function called but no selected group
			$this->show_groups('',$this->lang['emajnoselectedgroup']);
			return;
		}
		// build the groups list
		$groupsList='';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList.=$a['group'].', ';
		}
		$groupsList=substr($groupsList,0,strlen($groupsList)-2);

		$server_info = $misc->getServerInfo();
		if ($server_info["pgVersion"]>=8.4) {
		// if at least one selected group is protected, stop
			$protectedGroups=$this->emajdb->getProtectedGroups($groupsList);
			if ($protectedGroups != '') {
				$this->show_groups('',sprintf($this->lang['emajcantrlbkprotgroups'],$groupsList,$protectedGroups));
				return;
			}
		// look for marks common to all selected groups
			$marks=$this->emajdb->getRollbackMarkGroups($groupsList);
		// if no mark is usable for all selected groups, stop
			if ($marks->recordCount()==0) {
				$this->show_groups('',sprintf($this->lang['emajnomarkgroups'],$groupsList));
				return;
			}
		// get the youngest timestamp protected mark for all groups
			if ($this->emajdb->getNumEmajVersion() >= 10300) {
				$youngestProtectedMarkTimestamp=$this->emajdb->getYoungestProtectedMarkTimestamp($groupsList);
			} else {
				$youngestProtectedMarkTimestamp='';
			}
		}
		$this->printPageHeader();

		$misc->printTitle($this->lang['emajrlbkgroups']);

		echo "<style type=\"text/css\">[disabled]{color:#933;}</style>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_groups_confirm_alter\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo sprintf($this->lang['emajselectmarkgroups'], $misc->printVal($groupsList));

		if ($server_info["pgVersion"]>=8.4) {
		// pg 8.4+ => use a combo box with the only acceptable marks
			echo "<select name=\"mark\">\n";
			$optionDisabled = '';
			foreach($marks as $m) {
				// if the mark is older than the youngest protected against rollback, disabled it and the next ones
				if ($m['mark_datetime'] < $youngestProtectedMarkTimestamp) $optionDisabled = 'disabled';
				echo "<option value=\"",htmlspecialchars($m['mark_name']),"\" $optionDisabled>",htmlspecialchars($m['mark_name'])," (",htmlspecialchars($m['mark_datetime']),")</option>\n";
			}
			echo "</select></p><p>\n";
		} else {
		// pg 8.3- => just use a simple text input (the mark validity check will be done in doRollbackGroups() function)
			echo "<input name=\"mark\" size=\"32\" value=\"\" /></p><p>\n";
		}
		echo $misc->form;
		echo "{$this->lang['emajrollbacktype']} : ";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"unlogged\" checked>{$this->lang['emajunlogged']}";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"logged\">{$this->lang['emajlogged']}\n";
		echo "</p><p>";
		echo "<input type=\"submit\" name=\"rollbackgroups\" value=\"{$this->lang['emajrlbk']}\" />\n";
		if ($this->emajdb->getNumEmajVersion() >= 10100) {	// version >= 1.1.0
			if ($this->emajdb->isAsyncRlbkUsable($this->conf) ) {
				echo "<input type=\"submit\" name=\"async\" value=\"{$this->lang['emajrlbkthenmonitor']}\" />\n";
			}
		}
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Ask the user to confirm a multi groups rollback targeting a mark set prior alter_group operations
	 */
	function rollback_groups_confirm_alter() {
		global $lang, $misc;

		if ($this->emajdb->getNumEmajVersion() < 20100) {	// version < 2.1.0) {
			// for emaj version prior 2.1, directly go call the function that executes the rollback
			$this->rollback_groups_ok();
		} else {
			// check that the rollback would not reach a mark set before any alter group operation

			// process the click on the <cancel> button
			if (isset($_POST['cancel'])) {
				if ($_POST['back'] == 'list') {
					$this->show_groups();
				} else {
					$this->show_group();
				}
				exit();
			}

			// Check the groups are always in LOGGING state and not protected
			$groups=explode(', ',$_POST['groups']);
			foreach($groups as $g) {
				if ($this->emajdb->getGroup($g)->fields['group_state'] != 'LOGGING') {
					$this->show_groups('',sprintf($this->lang['emajcantrlbkidlegroups'],$groups,$g));
					return;
				}
			}
			// if at least one selected group is protected, stop
			$protectedGroups=$this->emajdb->getProtectedGroups($_POST['groups']);
			if ($protectedGroups != '') {
				$this->show_groups('',sprintf($this->lang['emajcantrlbkprotgroups'],$groups,$protectedGroups));
				return;
			}

			// Check the mark is always valid
			if (!$this->emajdb->isRollbackMarkValidGroups($_POST['groups'],$_POST['mark'])) {
				$this->show_groups('',sprintf($this->lang['emajcantrlbkinvalidmarkgroups'],$_POST['groups'],$_POST['mark']));
				return;
			}

			$alterGroupSteps = $this->emajdb->getAlterAfterMarkGroups($_POST['groups'],$_POST['mark'],$this->lang);

			if ($alterGroupSteps->recordCount() > 0) {
				// there are alter_group operations to cross over, so ask for a confirmation

				$columns = array(
					'time' => array(
						'title' => $this->lang['emajtimestamp'],
						'field' => field('time_tx_timestamp'),
					),
					'step' => array(
						'title' => $lang['straction'],
						'field' => field('altr_action'),
					),
					'autorollback' => array(
						'title' => $this->lang['emajautorolledback'],
						'field' => field('altr_auto_rolled_back'),
						'type'	=> 'callback',
						'params'=> array('function' => array($this, 'renderBooleanIcon'),'align' => 'center')
					),
				);

				$actions = array ();

				$this->printPageHeader();

				$misc->printTitle($this->lang['emajrlbkgroups']);

				echo "<p>" . sprintf($this->lang['emajreachaltergroups'],$_REQUEST['groups'], $_REQUEST['mark']) . "</p>\n";

//				$misc->printTable($alterGroupSteps, $columns, $actions, 'alterGroupStep');
				echo "<div id=\"alterGroupStep\" style=\"margin-top:15px;margin-bottom:15px\" >\n";
				$this->printTable($alterGroupSteps, $columns, $actions, 'alterGroupStep');
				echo "</div>\n";
	
				echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
				echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_groups_ok\" />\n";
				echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($_REQUEST['groups']), "\" />\n";
				echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
				echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
				echo "<input type=\"hidden\" name=\"rollbacktype\" value=\"", htmlspecialchars($_REQUEST['rollbacktype']), "\" />\n";
				if (isset($_POST['async'])) {
					echo "<input type=\"hidden\" name=\"async\"", htmlspecialchars($_REQUEST['async']), "\" />\n";
				}
				echo $misc->form;
				echo "<input type=\"submit\" name=\"rollbackgroups\" value=\"{$lang['strconfirm']}\" />\n";
				echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
				echo "</form>\n";

				$this->printEmajFooter();
				$misc->printFooter();

			} else {
				// otherwise, directly execute the rollback
				$this->rollback_groups_ok();
			}
		}
	}

	/**
	 * Perform rollback_groups
	 */
	function rollback_groups_ok() {
		global $lang, $misc;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_groups(); exit(); }

		// Check the groups are always in LOGGING state and not protected
		$groups=explode(', ',$_POST['groups']);
		foreach($groups as $g) {
			if ($this->emajdb->getGroup($g)->fields['group_state'] != 'LOGGING') {
				$this->show_groups('',sprintf($this->lang['emajcantrlbkidlegroups'],$groups,$g));
				return;
			}
		}
		$server_info = $misc->getServerInfo();
		if ($server_info["pgVersion"]>=8.4) {
		// if at least one selected group is protected, stop
			$protectedGroups=$this->emajdb->getProtectedGroups($_POST['groups']);
			if ($protectedGroups != '') {
				$this->show_groups('',sprintf($this->lang['emajcantrlbkprotgroups'],$groups,$protectedGroups));
				return;
			}
		}

		// Check the mark is always valid
		if (!$this->emajdb->isRollbackMarkValidGroups($_POST['groups'],$_POST['mark'])) {
			$this->show_groups('',sprintf($this->lang['emajcantrlbkinvalidmarkgroups'],$_POST['groups'],$_POST['mark']));
			return;
		}
		// OK

		if (isset($_POST['async'])) {
		// perform the rollback in asynchronous mode and switch to the rollback monitoring page

			$psqlExe = $misc->escapeShellCmd($this->conf['psql_path']);

			// re-check the psql exe path and the temp directory supplied in the config file
			$version = array();
			preg_match("/(\d+(?:\.\d+)?)(?:\.\d+)?.*$/", exec($psqlExe . " --version"), $version);
			if (empty($version)) {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($this->lang['emajbadpsqlpath'], $this->conf['psql_path']));
				} else {
					$this->show_group('',sprintf($this->lang['emajbadpsqlpath'], $this->conf['psql_path']));
				}
				exit;
			}

			// re-check the file can be written into the temp directory supplied in the config file 
			$sep = (substr(php_uname(), 0, 3) == "Win") ? '\\' : '/';
			$testFileName = $this->conf['temp_dir'] . $sep . 'rlbk_report_test';
			$f = fopen($testFileName,'w');
			if (!$f) {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($this->lang['emajbadtempdir'], $this->conf['temp_dir']));
				} else {
					$this->show_group('',sprintf($this->lang['emajbadtempdir'], $this->conf['temp_dir']));
				}
				exit;
			} else {
				fclose($f);
				unlink($testFileName);
			}

			$rlbkId = $this->emajdb->asyncRollbackGroups($_POST['groups'],$_POST['mark'],$_POST['rollbacktype']=='logged', $psqlExe, $this->conf['temp_dir'].$sep, true);
			$this->show_rollbacks(sprintf($this->lang['emajasyncrlbkstarted'],$rlbkId));
			exit;
		}

		// perform the rollback in regular synchronous mode

		if (!ini_get('safe_mode')) set_time_limit(0);		// Prevent timeouts on large rollbacks (non-safe mode only)

		if (isset($_POST['rollbacktype'])) {
			$status = $this->emajdb->rollbackGroups($_POST['groups'],$_POST['mark'],$_POST['rollbacktype']=='logged');
		} else {
			$status = $this->emajdb->rollbackGroups($_POST['groups'],$_POST['mark'],false);
		}
		if ($status >= 0) {
			$this->show_groups(sprintf($this->lang['emajrlbkgroupsok'],$_POST['groups'],$_POST['mark']));
		} else {
			$this->show_groups('',sprintf($this->lang['emajrlbkgroupserr'],$_POST['groups'],$_POST['mark']));
		}
	}

	/**
	 * Prepare a rollback consolidation: ask for confirmation
	 */
	function consolidate_rollback() {
		global $misc, $lang;

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajconsolidaterlbk']);

		echo "<p>", sprintf($this->lang['emajconfirmconsolidaterlbk'],$misc->printVal($_REQUEST['mark']),$misc->printVal($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"consolidate_rollback_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"consolidaterlbk\" value=\"{$lang['strok']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform a rollback consolidation
	 */
	function consolidate_rollback_ok() {
		global $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			$this->show_rollbacks();
			exit();
		}

		$status = $this->emajdb->consolidateRollback($_POST['group'],$_POST['mark']);
		if ($status > 0)
			$this->show_rollbacks(sprintf($this->lang['emajconsolidaterlbkok'],$_POST['mark'],$_POST['group']));
		else
			$this->show_rollbacks('',sprintf($this->lang['emajconsolidaterlbkerr'],$_POST['mark'],$_POST['group']));

	}

	/**
	 * Prepare rename_mark_group: ask for the new name for the mark to rename and confirmation
	 */
	function rename_mark_group() {
		global $misc, $lang;

		if (!isset($_POST['group'])) $_POST['group'] = '';
		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajrenameamark']);

		echo "<p>", sprintf($this->lang['emajconfirmrenamemark'], $misc->printVal($_REQUEST['mark']), $misc->printVal($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left required\">{$this->lang['emajnewnamemark']}</th>\n";
		echo "<td class=\"data1\"><input name=\"newmark\" size=\"32\" value=\"",
			htmlspecialchars($_POST['mark']), "\" id=\"newmark\"/></td></tr>\n";
		echo "</table>\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"rename_mark_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strok']}\" id=\"ok\" disabled=\"disabled\"/>\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		echo "<script type=\"text/javascript\">\n";
		echo "  $(document).ready(function () {\n";
		echo "    $(\"#newmark\").keyup(function (data) {\n";
		echo "      if ($(this).val() != \"\") {\n";
		echo "        $(\"#ok\").removeAttr(\"disabled\");\n";
		echo "      } else {\n";
		echo "        $(\"#ok\").attr(\"disabled\", \"disabled\");\n";
		echo "      }\n";
		echo "    });\n";
		echo "  });\n";
		echo "</script>";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform rename_mark_group
	 */
	function rename_mark_group_ok() {
		global $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_group(); exit(); }

		// Check the supplied mark group is valid
		if (!$this->emajdb->isNewMarkValidGroup($_POST['group'],$_POST['newmark']))
			$this->show_group('',sprintf($this->lang['emajinvalidmark'],$_POST['newmark']));
		else {
		// OK
			$status = $this->emajdb->renameMarkGroup($_POST['group'],$_POST['mark'],$_POST['newmark']);
			if ($status >= 0)
				$this->show_group(sprintf($this->lang['emajrenamemarkok'],$_POST['mark'],$_POST['group'],$_POST['newmark']));
			else
				$this->show_group('',sprintf($this->lang['emajrenamemarkerr'],$_POST['mark'],$_POST['group'],$_POST['newmark']));
		}
	}

	/**
	 * Prepare delete mark group: ask for confirmation
	 */
	function delete_mark() {
		global $misc, $lang;

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajdelamark']);

		echo "<p>", sprintf($this->lang['emajconfirmdelmark'], $misc->printVal($_REQUEST['mark']), $misc->printVal($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"delete_mark_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"deletemark\" value=\"{$lang['strok']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform delete mark group
	 */
	function delete_mark_ok() {
		global $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_group(); exit(); }

		$status = $this->emajdb->deleteMarkGroup($_POST['group'],$_POST['mark']);
		if ($status >= 0)
			$this->show_group(sprintf($this->lang['emajdelmarkok'],$_POST['mark'],$_POST['group']));
		else
			$this->show_group('',sprintf($this->lang['emajdelmarkerr'],$_POST['mark'],$_POST['group']));
	}

	/**
	 * Prepare delete before mark group: ask for confirmation
	 */
	function delete_before_mark() {
		global $misc, $lang;

		$this->printPageHeader();

		$misc->printTitle($this->lang['emajdelmarks']);

		echo "<p>", sprintf($this->lang['emajconfirmdelmarks'], $misc->printVal($_REQUEST['mark']), $misc->printVal($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"delete_before_mark_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"deletebeforemark\" value=\"{$lang['strok']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform delete before mark group
	 */
	function delete_before_mark_ok() {
		global $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_group(); exit(); }

		$status = $this->emajdb->deleteBeforeMarkGroup($_POST['group'],$_POST['mark']);
		if ($status > 0)
			$this->show_group(sprintf($this->lang['emajdelmarksok'],$status,$_POST['mark'],$_POST['group']));
		else
			$this->show_group('',sprintf($this->lang['emajdelmarkserr'],$_POST['mark'],$_POST['group']));
	}

	/**
	 * Call the sqleditor.php page passing the sqlquery to display in $_SESSION
	 * We are already in the target frame
	 */
	function call_sqledit() {
		global $misc;

		$_SESSION['sqlquery'] = $_REQUEST['sqlquery'];
		echo "<meta http-equiv=\"refresh\" content=\"0;url=sqledit.php?subject=table&amp;{$misc->href}&amp;action=sql&amp;paginate=true\">";
	}

	/**
	 * Generate the page header including the trail and the tabs
	 */
	function printPageHeader($tabs ='emaj',$tab = 'emajgroups') {
		global $misc;

		$misc->printHeader($this->lang['emajplugin']);
		$misc->printBody();
		if ($tabs == 'emaj') {
			$misc->printTrail('database');
		} else {
			$misc->printTrail('emaj');
		}
		$misc->printTabs($tabs,$tab);
		return ;
	}

	/**
	 * Display the emaj header line including the version number
	 */
	function printEmajHeader($urlvar,$title) {
		global $lang, $misc;
	// $urlvar is the piece of the url containing the variables needed to refresh the page

	// If Emaj is not usable for this database, only display a message
		if (!(isset($this->emajdb)&&$this->emajdb->isEnabled()&&$this->emajdb->isAccessible())) {
			echo "<div class=\"topbar\"><table style=\"width: 100%\"><tr><td><span class=\"platform\">{$this->lang['emajnotavail']}</span></td></tr></table></div>";
			return 0;
		}
	// If Emaj version is too old, only display a message
		if ($this->emajdb->getNumEmajVersion() < $this->oldest_supported_emaj_version_num) {
			echo "<div class=\"topbar\"><table style=\"width: 100%\"><tr><td><span class=\"platform\">";
			echo sprintf($this->lang['emajtooold'],$this->emajdb->getEmajVersion(),$this->oldest_supported_emaj_version);
			echo "</span></td></tr></table></div>";
			return 0;
		}

	// generate the E-Maj header
		$currTime = date('H:i:s');
		echo "<div class=\"topbar\"><table style=\"width: 100%\"><tr>\n";
		echo "<td style=\"width:15px\"><a href=\"plugin.php?plugin={$this->name}&amp;{$urlvar}&amp;{$misc->href}\"><img src=\"{$misc->icon('Refresh')}\" alt=\"{$lang['strrefresh']}\" title=\"{$lang['strrefresh']}\" /></a></td>\n";
		echo "<td><span class=\"platform\">{$currTime}&nbsp;&nbsp;";
		echo "E-Maj {$this->emajdb->getEmajVersion()}&nbsp;&nbsp;-&nbsp;&nbsp;{$title}</span></td>\n";
		echo "<td style=\"width:15px\"><a href=\"#bottom\"><img src=\"{$misc->icon(array($this->name,'Bottom'))}\" alt=\"{$this->lang['emajpagebottom']}\" title=\"{$this->lang['emajpagebottom']}\" /></a></td>\n";
		echo "</tr></table></div>\n";

		return 1;
	}

	/**
	 * Display the emaj footer
	 */
	function printEmajFooter() {

		echo "<div class=\"footer\"><a name=\"bottom\">&nbsp;</a></div>\n";

		return;
	}

	/**
	 * Print out a standart message and/or and error message
	 * @param $msg			The message to print
	 *        $errorFlag	Optional flag indicating whether the message is an error message
	 */
	function printMsg($msg,$errMsg) {
		if ($msg != '') echo "<p class=\"message\">{$msg}</p>\n";
		if ($errMsg != '') echo "<p style=\"color:red\">{$errMsg}</p>\n";
	}

	function tree() {
		global $misc;

		$reqvars = $misc->getRequestVars('emaj');

		$groups = $this->emajdb->getGroups();

		$attrs = array(
			'text' => field('group_name'),
			'icon' => $this->icon('EmajGroup'),
			'iconaction' => url	('plugin.php',$reqvars,
				array	(
					'action'  => 'show_group',
					'plugin' => $this->name,
					'group'  => field('group_name')
					)
				),
			'toolTip' => field('group_comment'),
			'action' => url	('plugin.php',$reqvars,
				array	(
					'action'  => 'show_group',
					'plugin' => $this->name,
					'group'  => field('group_name')
					)
				),
		);

		$misc->printTree($groups, $attrs,'emajgroups');
		exit;
	}

		function printTable(&$tabledata, &$columns, &$actions, $place, $nodata = null, $pre_fn = null) {
			global $data, $conf, $misc, $lang, $plugin_manager;
//phb: 1 line added
			global $misc;

			// Action buttons hook's place
			$plugin_functions_parameters = array(
				'actionbuttons' => &$actions,
				'place' => $place
			);
			$plugin_manager->do_hook('actionbuttons', $plugin_functions_parameters);

			if ($has_ma = isset($actions['multiactions']))
				$ma = $actions['multiactions'];
			unset($actions['multiactions']);

			if ($tabledata->recordCount() > 0) {

				// Remove the 'comment' column if they have been disabled
				if (!$conf['show_comments']) {
					unset($columns['comment']);
				}

				if (isset($columns['comment'])) {
					// Uncomment this for clipped comments.
					// TODO: This should be a user option.
					//$columns['comment']['params']['clip'] = true;
				}

				if ($has_ma) {
					echo "<script src=\"multiactionform.js\" type=\"text/javascript\"></script>\n";
					echo "<form id=\"multi_form\" action=\"{$ma['url']}\" method=\"post\" enctype=\"multipart/form-data\">\n";
					if (isset($ma['vars']))
						foreach ($ma['vars'] as $k => $v)
							echo "<input type=\"hidden\" name=\"$k\" value=\"$v\" />";
				}

				echo "<table>\n";
//phb: 1 line added
				echo "<thead>\n";
				echo "<tr>\n";
				// Display column headings
				if ($has_ma) echo "<th></th>";
				foreach ($columns as $column_id => $column) {
					switch ($column_id) {
						case 'actions':
							if (sizeof($actions) > 0) echo "<th class=\"data\" colspan=\"", count($actions), "\">{$column['title']}</th>\n";
							break;
						default:
							echo "<th class=\"data\">";
							if (isset($column['help']))
//phb: 1 line modified
//								$this->printHelp($column['title'], $column['help']);
								$misc->printHelp($column['title'], $column['help']);
							else
								echo $column['title'];
							echo "</th>\n";
							break;
					}
				}
				echo "</tr>\n";
//phb: 2 lines added
				echo "</thead>\n";
				echo "<tbody>\n";

				// Display table rows
				$i = 0;
				while (!$tabledata->EOF) {
					$id = ($i % 2) + 1;

					unset($alt_actions);
//phb: 1 line replaced by 7 lines
//					if (!is_null($pre_fn)) $alt_actions = $pre_fn($tabledata, $actions);
					if (!is_null($pre_fn)) {
						if (is_string($pre_fn)) {
							$alt_actions = $pre_fn($tabledata, $actions);
						} else {
							$alt_actions = $pre_fn[0]->{$pre_fn[1]}($tabledata, $actions);
						}
					}
					if (!isset($alt_actions)) $alt_actions =& $actions;

					echo "<tr class=\"data{$id}\">\n";
					if ($has_ma) {
						foreach ($ma['keycols'] as $k => $v)
							$a[$k] = $tabledata->fields[$v];
						echo "<td>";
						echo "<input type=\"checkbox\" name=\"ma[]\" value=\"". htmlentities(serialize($a), ENT_COMPAT, 'UTF-8') ."\" />";
						echo "</td>\n";
					}

					foreach ($columns as $column_id => $column) {

						// Apply default values for missing parameters
						if (isset($column['url']) && !isset($column['vars'])) $column['vars'] = array();

						switch ($column_id) {
							case 'actions':
								foreach ($alt_actions as $action) {
									if (isset($action['disable']) && $action['disable'] === true) {
										echo "<td></td>\n";
									} else {
										echo "<td class=\"opbutton{$id}\">";
										$action['fields'] = $tabledata->fields;
//phb: 1 line modified					$this->printLink($action);
										$misc->printLink($action);
										echo "</td>\n";
									}
								}
								break;
							default:
								echo "<td>";
								$val = value($column['field'], $tabledata->fields);
								if (!is_null($val)) {
									if (isset($column['url'])) {
										echo "<a href=\"{$column['url']}";
										$misc->printUrlVars($column['vars'], $tabledata->fields);
										echo "\">";
									}
									$type = isset($column['type']) ? $column['type'] : null;
									$params = isset($column['params']) ? $column['params'] : array();
//phb: 1 line modified
//									echo $misc->printVal($val, $type, $params);
									echo $this->printVal($val, $type, $params);
									if (isset($column['url'])) echo "</a>";
								}

								echo "</td>\n";
								break;
						}
					}
					echo "</tr>\n";

					$tabledata->moveNext();
					$i++;
				}
//phb: 1 line added
				echo "</tbody>\n";
				echo "</table>\n";

				// Multi action table footer w/ options & [un]check'em all
				if ($has_ma) {
					// if default is not set or doesn't exist, set it to null
					if (!isset($ma['default']) || !isset($actions[$ma['default']]))
						$ma['default'] = null;
					echo "<br />\n";
					echo "<table>\n";
					echo "<tr>\n";
					echo "<th class=\"data\" style=\"text-align: left\" colspan=\"3\">{$lang['stractionsonmultiplelines']}</th>\n";
					echo "</tr>\n";
					echo "<tr class=\"row1\">\n";
					echo "<td>";
					echo "<a href=\"#\" onclick=\"javascript:checkAll(true);\">{$lang['strselectall']}</a> / ";
					echo "<a href=\"#\" onclick=\"javascript:checkAll(false);\">{$lang['strunselectall']}</a></td>\n";
					echo "<td>&nbsp;--->&nbsp;</td>\n";
					echo "<td>\n";
					echo "\t<select name=\"action\">\n";
					if ($ma['default'] == null)
						echo "\t\t<option value=\"\">--</option>\n";
					foreach($actions as $k => $a)
						if (isset($a['multiaction']))
							echo "\t\t<option value=\"{$a['multiaction']}\"", ($ma['default']  == $k? ' selected="selected"': ''), ">{$a['content']}</option>\n";
					echo "\t</select>\n";
					echo "<input type=\"submit\" value=\"{$lang['strexecute']}\" />\n";
					echo $misc->form;
					echo "</td>\n";
					echo "</tr>\n";
					echo "</table>\n";
					echo '</form>';
				};

				return true;
			} else {
				if (!is_null($nodata)) {
					echo "<p>{$nodata}</p>\n";
				}
				return false;
			}
		}
		function printVal($str, $type = null, $params = array()) {
			global $lang, $conf, $data;

			// Shortcircuit for a NULL value
			if (is_null($str))
				return isset($params['null'])
						? ($params['null'] === true ? '<i>NULL</i>' : $params['null'])
						: '';

			if (isset($params['map']) && isset($params['map'][$str])) $str = $params['map'][$str];

			// Clip the value if the 'clip' parameter is true.
			if (isset($params['clip']) && $params['clip'] === true) {
				$maxlen = isset($params['cliplen']) && is_integer($params['cliplen']) ? $params['cliplen'] : $conf['max_chars'];
				$ellipsis = isset($params['ellipsis']) ? $params['ellipsis'] : $lang['strellipsis'];
				if (strlen($str) > $maxlen) {
					$str = substr($str, 0, $maxlen-1) . $ellipsis;
				}
			}

			$out = '';

			switch ($type) {
				case 'int2':
				case 'int4':
				case 'int8':
				case 'float4':
				case 'float8':
				case 'money':
				case 'numeric':
				case 'oid':
				case 'xid':
				case 'cid':
				case 'tid':
					$align = 'right';
					$out = nl2br(htmlspecialchars($str));
					break;
				case 'yesno':
					if (!isset($params['true'])) $params['true'] = $lang['stryes'];
					if (!isset($params['false'])) $params['false'] = $lang['strno'];
					// No break - fall through to boolean case.
				case 'bool':
				case 'boolean':
					if (is_bool($str)) $str = $str ? 't' : 'f';
					switch ($str) {
						case 't':
							$out = (isset($params['true']) ? $params['true'] : $lang['strtrue']);
							$align = 'center';
							break;
						case 'f':
							$out = (isset($params['false']) ? $params['false'] : $lang['strfalse']);
							$align = 'center';
							break;
						default:
							$out = htmlspecialchars($str);
					}
					break;
				case 'bytea':
					$tag = 'div';
					$class = 'pre';
					$out = $data->escapeBytea($str);
					break;
				case 'errormsg':
					$tag = 'pre';
					$class = 'error';
					$out = htmlspecialchars($str);
					break;
				case 'pre':
					$tag = 'pre';
					$out = htmlspecialchars($str);
					break;
				case 'prenoescape':
					$tag = 'pre';
					$out = $str;
					break;
				case 'nbsp':
					$out = nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($str)));
					break;
				case 'verbatim':
					$out = $str;
					break;
				case 'callback':
//phb: 1 row replaced by 5 rows
//					$out = $params['function']($str, $params);
					if (is_string($params['function'])) {
						$out = $params['function']($str, $params);
					} else {
						$out = $params['function'][0]->{$params['function'][1]}($str, $params);
					}
					break;
				case 'prettysize':
					if ($str == -1) 
						$out = $lang['strnoaccess'];
					else
					{
						$limit = 10 * 1024;
						$mult = 1;
						if ($str < $limit * $mult)
							$out = $str.' '.$lang['strbytes'];
						else
						{
							$mult *= 1024;
							if ($str < $limit * $mult)
								$out = floor(($str + $mult / 2) / $mult).' '.$lang['strkb'];
							else
							{
								$mult *= 1024;
								if ($str < $limit * $mult)
									$out = floor(($str + $mult / 2) / $mult).' '.$lang['strmb'];
								else
								{
									$mult *= 1024;
									if ($str < $limit * $mult)
										$out = floor(($str + $mult / 2) / $mult).' '.$lang['strgb'];
									else
									{
										$mult *= 1024;
										if ($str < $limit * $mult)
											$out = floor(($str + $mult / 2) / $mult).' '.$lang['strtb'];
									}
								}
							}
						}
					}
					break;
				default:
					// If the string contains at least one instance of >1 space in a row, a tab
					// character, a space at the start of a line, or a space at the start of
					// the whole string then render within a pre-formatted element (<pre>).
					if (preg_match('/(^ |  |\t|\n )/m', $str)) {
						$tag = 'pre';
						$class = 'data';
						$out = htmlspecialchars($str);
					} else {
						$out = nl2br(htmlspecialchars($str));
					}
			}

			if (isset($params['class'])) $class = $params['class'];
			if (isset($params['align'])) $align = $params['align'];

			if (!isset($tag) && (isset($class) || isset($align))) $tag = 'div';

			if (isset($tag)) {
				$alignattr = isset($align) ? " style=\"text-align: {$align}\"" : '';
				$classattr = isset($class) ? " class=\"{$class}\"" : '';
				$out = "<{$tag}{$alignattr}{$classattr}>{$out}</{$tag}>";
			}

			// Add line numbers if 'lineno' param is true
			if (isset($params['lineno']) && $params['lineno'] === true) {
				$lines = explode("\n", $str);
				$num = count($lines);
				if ($num > 0) {
					$temp = "<table>\n<tr><td class=\"{$class}\" style=\"vertical-align: top; padding-right: 10px;\"><pre class=\"{$class}\">";
					for ($i = 1; $i <= $num; $i++) {
						$temp .= $i . "\n";
					}
					$temp .= "</pre></td><td class=\"{$class}\" style=\"vertical-align: top;\">{$out}</td></tr></table>\n";
					$out = $temp;
				}
				unset($lines);
			}

			return $out;
		}
}
?>
