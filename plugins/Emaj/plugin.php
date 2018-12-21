<?php
require_once('./classes/Plugin.php');

class Emaj extends Plugin {

	/**
	 * Attributes
	 */
	protected $name = 'Emaj';
	protected $lang;
	protected $conf = array();
	protected $oldest_supported_emaj_version = '1.3.0';			// Oldest emaj version supported by the plugin
	protected $oldest_supported_emaj_version_num = 10300;
	protected $last_known_emaj_version = '3.0.0';				// Most recent emaj version known by the plugin
	protected $last_known_emaj_version_num = 30000;
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

		/* loads $lang and $this->conf */
		parent::__construct($language);

	}

	function get_hooks() {
		$hooks = array();
		return $hooks;
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
		'delete_marks',
		'delete_marks_ok',
		'drop_group',
		'drop_group_ok',
		'emaj_envir',
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

	// Functions to dynamicaly modify actions list for each table group in logging state to display
	function loggingGroupPre(&$rowdata, $loggingActions) {
		global $emajdb;
		// disable the rollback button for audit_only groups
		$isGroupRollbackable = $emajdb->isGroupRollbackable($rowdata->fields['group_name']);
		if (isset($loggingActions['rollback_group']) && !$isGroupRollbackable) {
			$loggingActions['rollback_group']['disable'] = true;
		}
		$isGroupProtected = $emajdb->isGroupProtected($rowdata->fields['group_name']);
		// disable the protect button for audit_only or protected groups
		if (isset($loggingActions['protect_group']) && (!$isGroupRollbackable || $isGroupProtected)) {
			$loggingActions['protect_group']['disable'] = true;
			$loggingActions['rollback_group']['disable'] = true;
		}
		// disable the unprotect button for audit_only or unprotected groups
		if (isset($loggingActions['unprotect_group']) && (!$isGroupRollbackable || !$isGroupProtected)) {
			$loggingActions['unprotect_group']['disable'] = true;
		}
		if ($emajdb->getNumEmajVersion() >= 30000) {
		// disable the alter_group button when there is no configuration change to apply
			if (isset($loggingActions['alter_group']) && (!$rowdata->fields['has_waiting_changes'])) {
				$loggingActions['alter_group']['disable'] = true;
			}
		}
		return $loggingActions;
	}

	// Functions to dynamicaly modify actions list for each table group in idle state to display
	function idleGroupPre(&$rowdata, $idleActions) {
		global $emajdb;
		if ($emajdb->getNumEmajVersion() >= 30000) {
		// disable the alter_group button when there is no configuration change to apply
			if (isset($idleActions['alter_group']) && (!$rowdata->fields['has_waiting_changes'])) {
				$idleActions['alter_group']['disable'] = true;
			}
		}
		return $idleActions;
	}

	// Functions to dynamicaly modify actions list for each configured but not yet crated table group to display
	function configuredGroupPre(&$rowdata, $configuredActions) {
		// disable the create button for groups with diagnostics
		if (isset($configuredActions['create_group']) && $rowdata->fields['group_diagnostic'] != '{0,0,0,0,0}') {
			$configuredActions['create_group']['disable'] = true;
		}
		return $configuredActions;
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
		global $misc, $lang;
		if ($val == '!') {
			return "<img src=\"".$misc->icon('ObjectNotFound')."\" alt=\"{$lang['emajunknownobject']}\" title=\"{$lang['emajunknownobject']}\" style=\"vertical-align:bottom;\" />";
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
			$alt = $lang['emajunknownobject'];
		} else {									// unsupported type
			$icon = $misc->icon('ObjectNotFound');
			$alt = $lang['emajunsupportedobject'];
		}
		return "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
	}

	// Callback function to dynamicaly replace the group type from the database by one or two icons
	function renderGroupType($val) {
		global $misc, $lang;
		if ($val == 'ROLLBACKABLE') {
			$icon = $misc->icon(array($this->name,'EmajRollbackable'));
			$alt = $lang['emajrollbackable'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		} elseif ($val == 'AUDIT_ONLY') {
			$icon = $misc->icon(array($this->name,'EmajAuditOnly'));
			$alt = $lang['emajauditonly'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		} elseif ($val == 'ROLLBACKABLE-PROTECTED') {
			$icon = $misc->icon(array($this->name,'EmajRollbackable'));
			$alt = $lang['emajrollbackable'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
			$icon = $misc->icon(array($this->name,'EmajPadlock'));
			$alt = $lang['emajprotected'];
			$img .= "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		}
		return $img;
	}

	// Callback function to dynamicaly modify the diagnostic column of configured but not yet created groups
	// It replaces the database value by an icon
	function renderDiagnosticNewGroup($val) {
		global $misc, $lang;

		if ($val == '{0,0,0,0,0}') {
			$icon = 'CheckConstraint';
			return "<img src=\"".$misc->icon($icon)."\" style=\"vertical-align:bottom;\" />";
		} else {
			if (preg_match("/{(\d+),(\d+),(\d+),(\d+),(\d+)}/",$val,$cpt)) {
				$msg = '';
				if ($cpt[1] > 0) $msg .= sprintf($lang['emajnoschema'], $cpt[1]);
				if ($cpt[2] > 0) $msg .= sprintf($lang['emajinvalidschema'], $cpt[2]);
				if ($cpt[3] > 0) $msg .= sprintf($lang['emajnorelation'], $cpt[3]);
				if ($cpt[4] > 0) $msg .= sprintf($lang['emajinvalidtable'], $cpt[4]);
				if ($cpt[5] > 0) $msg .= sprintf($lang['emajduplicaterelation'], $cpt[5]);
				$msg = substr($msg,0,-3);
				return $msg;
			} else {
				return "$val not decoded";
			}
		}
	}

	// Callback function to dynamicaly modify the group state column content
	// It replaces the database value by an icon
	function renderGroupState($val) {
		global $misc, $lang;
		if ($val == 'IDLE') {
			$icon = $misc->icon(array($this->name,'EmajIdle'));
			$alt = $lang['emajidle'];
		} else {
			$icon = $misc->icon(array($this->name,'EmajLogging'));
			$alt = $lang['emajlogging'];
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
		global $misc, $lang;
		if ($val == 'ACTIVE') {
			$icon = $misc->icon(array($this->name,'EmajMark'));
			$alt = $lang['emajactive'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		} elseif ($val == 'DELETED') {
			$icon = $misc->icon('Delete');
			$alt = $lang['emajdeleted'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		} elseif ($val == 'ACTIVE-PROTECTED') {
			$icon = $misc->icon(array($this->name,'EmajMark'));
			$alt = $lang['emajactive'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
			$icon = $misc->icon(array($this->name,'EmajPadlock'));
			$alt = $lang['emajprotected'];
			$img .= "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		}
		return $img;
	}

/********************************************************************************************************
 * Main functions displaying pages
 *******************************************************************************************************/

	/**
	 * Show list of created emaj groups
	 */
	function show_groups($msg = '', $errMsg = '') {
		global $lang, $misc, $emajdb;

		$this->printPageHeader('emaj','emajgroups');

		$emajOK = $this->printEmajHeader("action=show_groups",$lang['emajgrouplist']);

		if ($emajOK) {
			$this->printMsg($msg,$errMsg);

			$idleGroups = $emajdb->getIdleGroups();
			$loggingGroups = $emajdb->getLoggingGroups();
			$configuredGroups = $emajdb->getConfiguredGroups();

			$columns = array(
				'group' => array(
					'title' => $lang['emajgroup'],
					'field' => field('group_name'),
					'url'   => "plugin.php?plugin={$this->name}&amp;action=show_group&amp;{$misc->href}&amp;",
					'vars'  => array('group' => 'group_name'),
				),
				'creationdatetime' => array(
					'title' => $lang['emajcreationdatetime'],
					'field' => field('creation_datetime'),
					'params'=> array('align' => 'center'),
				),
				'nbtbl' => array(
					'title' => $lang['emajnbtbl'],
					'field' => field('group_nb_table'),
					'type'  => 'numeric'
				),
				'nbseq' => array(
					'title' => $lang['emajnbseq'],
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
							),
					'sorter_text_extraction' => 'img_alt',
					'filter' => false,
				),
			);
			$columns = array_merge($columns, array(
				'nbmark' => array(
					'title' => $lang['emajnbmark'],
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
					'content' => $lang['emajdetail'],
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
			if ($emajdb->isEmaj_Adm()) {
				$loggingActions = array_merge($loggingActions, array(
					'multiactions' => array(
						'keycols' => array('group' => 'group_name'),
						'url' => "plugin.php?plugin={$this->name}&amp;back=list",
					),
					'set_mark_group' => array(
						'content' => $lang['emajsetmark'],
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
					'protect_group' => array(
						'content' => $lang['emajprotect'],
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
						'content' => $lang['emajunprotect'],
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
					'rollback_group' => array(
						'content' => $lang['emajrlbk'],
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
					'comment_group' => array(
						'content' => $lang['emajsetcomment'],
						'attr' => array (
							'href' => array (
								'url' => 'plugin.php',
								'urlvars' => array_merge($urlvars, array (
									'plugin' => $this->name,
									'action' => 'comment_group',
									'back' => 'list',
									'group' => field('group_name'),
								))))
					))
				);
				if ($emajdb->getNumEmajVersion() >= 20100) {	// version >= 2.1.0
					$loggingActions = array_merge($loggingActions, array(
						'alter_group' => array(
							'content' => $lang['emajApplyConfChanges'],
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
					if ($emajdb->getNumEmajVersion() >= 20100) {	// version >= 2.1.0
							$loggingActions['alter_group']['multiaction'] = 'alter_groups';
					}
				};
			};

			$idleActions = array(
				'show_group' => array(
					'content' => $lang['emajdetail'],
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
			if ($emajdb->isEmaj_Adm()) {
				$idleActions = array_merge($idleActions, array(
					'multiactions' => array(
						'keycols' => array('group' => 'group_name'),
						'url' => "plugin.php?plugin={$this->name}&amp;back=list",
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
					'comment_group' => array(
						'content' => $lang['emajsetcomment'],
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
					'alter_group' => array(
						'content' => $lang['emajApplyConfChanges'],
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
				if ($emajdb->getNumEmajVersion() >= 20100) {	// version >= 2.1.0
						$idleActions['alter_group']['multiaction'] = 'alter_groups';
				}
				$idleActions = array_merge($idleActions, array(
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

			$configuredColumns = array(
				'group' => array(
					'title' => $lang['emajgroup'],
					'field' => field('grpdef_group'),
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
				'nbtbl' => array(
					'title' => $lang['emajnbtbl'],
					'field' => field('group_nb_table'),
					'type'  => 'numeric'
				),
				'nbseq' => array(
					'title' => $lang['emajnbseq'],
					'field' => field('group_nb_sequence'),
					'type'  => 'numeric'
				),
				'diagnostic' => array(
					'title' => $lang['emajdiagnostics'],
					'field' => field('group_diagnostic'),
					'type'	=> 'callback',
					'params'=> array(
							'function' => array($this, 'renderDiagnosticNewGroup'),
							)
				),
			);

			if ($emajdb->isEmaj_Adm()) {
				$configuredActions = array(
					'create_group' => array(
						'content' => $lang['strcreate'],
						'attr' => array (
							'href' => array (
								'url' => 'plugin.php',
								'urlvars' => array_merge($urlvars, array (
									'plugin' => $this->name,
									'action' => 'create_group',
									'back' => 'list',
									'empty' => 'false',
									'group' => field('grpdef_group'),
								))))
					),
				);
			} else {
				$configuredActions = array();
			}

			echo "<h3>{$lang['emajlogginggroups']}<img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajlogginggrouphelp']}\"/></h3>";

			$misc->printTable($loggingGroups, $columns, $loggingActions, 'loggingGroups', $lang['emajnologginggroup'], array($this, 'loggingGroupPre'), array('sorter' => true, 'filter' => true));

			echo "<hr>";
			echo "<h3>{$lang['emajidlegroups']}<img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajidlegrouphelp']}\"/></h3>\n";

			$misc->printTable($idleGroups, $columns, $idleActions, 'idleGroups', $lang['emajnoidlegroup'], array($this, 'idleGroupPre'), array('sorter' => true, 'filter' => true));

			echo "<hr>\n";

			// configured but not yet created tables section
			echo "<h3>{$lang['emajconfiguredgroups']}<img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajconfiguredgrouphelp']}\"/></h3>\n";

			$misc->printTable($configuredGroups, $configuredColumns, $configuredActions, 'configuredGroups', $lang['emajnoconfiguredgroups'], array($this, 'configuredGroupPre'), array('sorter' => true, 'filter' => true));

			// for emaj_adm role only, give information about how to create a group and propose the create empty group button
			if ($emajdb->isEmaj_Adm()) {
				if ($emajdb->getNumEmajVersion() >= 20100) {			// version >= 2.1.0
					echo "<p>{$lang['emajnoconfiguredgroup']}</p>\n";
					echo "<form id=\"createEmptyGroup_form\" action=\"plugin.php?plugin={$this->name}&amp;action=create_group&amp;back=list&amp;empty=true&amp;{$misc->href}\"";
					echo " method=\"post\" enctype=\"multipart/form-data\">\n";
					echo "\t<input type=\"submit\" value=\"{$lang['emajcreateemptygroup']}\" />\n";
					echo "</form>\n";
				}
			}
		}

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Define groups content
	 */
	function configure_groups($msg = '', $errMsg = '', $prevSchema = '') {
		global $misc, $lang, $emajdb;

		$this->printPageHeader('emaj','emajconfiguregroups');

		if (!isset($_REQUEST['appschema'])) $_REQUEST['appschema'] = $prevSchema;
		if (is_array($_REQUEST['appschema'])) $_REQUEST['appschema'] = $_REQUEST['appschema'][0];

		$emajOK = $this->printEmajHeader("action=configure_groups&amp;appschema=".urlencode($_REQUEST['appschema']),$lang['emajgroupsconfiguration']);

		if ($emajOK) {
			$this->printMsg($msg,$errMsg);

		// Schemas list
			echo "<h3>{$lang['emajschemaslist']}</h3>\n";

			$schemas = $emajdb->getSchemas();

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

			$misc->printTable($schemas, $columns, $actions, 'defineGroupSchemas', $lang['strnoschemas'], null, array('sorter' => true, 'filter' => true));

			echo "<hr>\n";

		// Tables and sequences for the selected schema, if any
			if (isset($_REQUEST['appschema']) && $_REQUEST['appschema'] != '') {

				echo "<h3>".sprintf($lang['emajtblseqofschema'],$_REQUEST['appschema'])."</h3>\n";
				$tblseq = $emajdb->getTablesSequences($_REQUEST['appschema']);

				$columns = array(
					'type' => array(
						'title' => $lang['strtype'],
						'field' => field('relkind'),
						'type'	=> 'callback',
						'params'=> array('function' => array($this, 'renderTblSeq'),'align' => 'center'),
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
					),
					'logschemasuffix' => array(
						'title' => $lang['emajlogschemasuffix'],
						'field' => field('grpdef_log_schema_suffix'),
					),
					'emajnamesprefix' => array(
						'title' => $lang['emajnamesprefix'],
						'field' => field('grpdef_emaj_names_prefix'),
					),
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
				);

				$urlvars = $misc->getRequestVars();

				$actions = array(
					'multiactions' => array(
						'keycols' => array('appschema' => 'nspname', 'tblseq' => 'relname', 'group' => 'grpdef_group', 'type' => 'relkind'),
						'url' => "plugin.php?plugin={$this->name}&amp;back=define",
					),
					'assign' => array(
						'content' => $lang['emajassign'],
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
						'content' => $lang['emajremove'],
						'attr' => array (
							'href' => array (
								'url' => 'plugin.php',
								'urlvars' => array_merge($urlvars, array (
									'plugin' => $this->name,
									'action' => 'remove_tblseq',
									'appschema' => field('nspname'),
									'tblseq' => field('relname'),
									'group' => field('grpdef_group'),
									'type' => field('relkind'),
								)))),
						'multiaction' => 'remove_tblseq',
					),
				);

				$misc->printTable($tblseq, $columns, $actions, 'defineGroupTblseq', $lang['strnotables'], array($this,'tblseqPre'), array('sorter' => true, 'filter' => true));
			}
		}

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Display the status of past and in progress rollback operations
	 */
	function show_rollbacks($msg = '', $errMsg = '') {
		global $lang, $misc, $emajdb;

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

		$emajOK = $this->printEmajHeader("action=show_rollbacks",$lang['emajrlbkoperations']);

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
				'params'=> array('function' => array($this,'renderBoolean'),'align' => 'center')
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
				'params'=> array('function' => array($this,'renderBoolean'),'align' => 'center')
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

		echo "<h3>{$lang['emajinprogressrlbk']}</h3>\n";
		if ($emajdb->isDblinkUsable()) {
			$inProgressRlbks = $emajdb->getInProgressRlbk();
			$misc->printTable($inProgressRlbks, $columnsInProgressRlbk, $actions, 'inProgressRlbk', $lang['emajnorlbk']);
		} else {
			echo "<p>{$lang['emajrlbkmonitornotavailable']}</p>\n";
		}

		echo "<h3>{$lang['emajcompletedrlbk']}</h3>\n";

		// Form to setup parameters for completed rollback operations filtering
		echo "<div style=\"margin-bottom:10px;\">\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;action=filterrlbk\" method=\"post\">\n";
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
								'url' => 'plugin.php',
								'urlvars' => array (
									'plugin' => $this->name,
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

			echo "<h3>{$lang['emajconsolidablerlbk']}</h3>\n";
			$inProgressRlbks = $emajdb->getInProgressRlbk();
			$misc->printTable($consolidableRlbks, $columnsConsRlbk, $actions, 'consolidableRlbk', $lang['emajnorlbk']);
		}

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * E-Maj environment management
	 */
	function emaj_envir() {
		global $misc, $lang, $emajdb;

		$this->printPageHeader('emaj','emajenvir');

		$emajOK = $this->printEmajHeader("action=emaj_envir",$lang['emajenvironment']);
		// check if E-Maj is installed in the current database
		if (! $emajdb->isEnabled()) {
			// emaj is not installed, check if the extension is available
			if (! $emajdb->isExtensionAvailable()) {
				echo "<p>{$lang['emajextnotavailable']}</p>\n";
			} else {
				echo "<p>{$lang['emajextnotcreated']}</p>\n";
			}
			$emajOK= 0;
		} else {
			// emaj is installed, check that the user has enought rights to continue
			if (! $emajdb->isAccessible()) {
				echo "<p>{$lang['emajnogrant']}</p>\n";
				$emajOK= 0;
			}
		}

		if ($emajOK) {
			// Version section
			echo "<h3>{$lang['emajversions']}</h3>\n";
			if ($emajdb->isExtension()) {
				$installationMode = $lang['emajasextension'];
			} else {
				$installationMode = $lang['emajasscript'];
			}
			echo "<p>{$lang['emajversion']}{$emajdb->getEmajVersion()} ({$installationMode})</p>\n";
			if ($emajdb->getNumEmajVersion() < $this->oldest_supported_emaj_version_num) {
				echo "<p>" . sprintf($lang['emajtooold'],$emajdb->getEmajVersion(),$this->oldest_supported_emaj_version) . "</p>\n";
				$emajOK= 0;
			} else {
				if ($emajdb->getNumEmajVersion() <> 999999) {
					if ($emajdb->getNumEmajVersion() < $this->last_known_emaj_version_num) {
						echo "<p>{$lang['emajversionmorerecent']}</p>\n";
					}
					if ($emajdb->getNumEmajVersion() > $this->last_known_emaj_version_num) {
						echo "<p>{$lang['emajwebversionmorerecent']}</p>\n";
					}
				}
			}
		}

		if ($emajOK) {
			// General characteristics of the E-Maj environment
			echo "<hr/>\n";
			echo "<h3>{$lang['emajcharacteristics']}</h3>\n";
			if ($emajdb->isEmaj_Adm()) {
				echo "<p>".sprintf($lang['emajdiskspace'],$emajdb->getEmajSize())."</p>\n";
			}

			// E-Maj environment checking
			echo "<hr/>\n";
			echo "<h3>{$lang['emajchecking']}</h3>\n";

			$messages = $emajdb->checkEmaj();

			$columns = array(
				'message' => array(
					'title' => $lang['emajdiagnostics'],
					'field' => field('emaj_verify_all'),
					'type'	=> 'callback',
					'params'=> array('function' => array($this, 'renderDiagnostic'))
				),
			);

			$actions = array ();

			$misc->printTable($messages, $columns, $actions, 'checks');
		}

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Displays all detailed information about one group, including marks
	 */
	function show_group($msg = '', $errMsg = '') {
		global $misc, $lang, $emajdb;

		$this->printPageHeader('emajgroup','emajgroupproperties');

		$emajOK = $this->printEmajHeader("action=show_group&amp;group=".urlencode($_REQUEST['group']),sprintf($lang['emajgrouppropertiesmarks'],htmlspecialchars($_REQUEST['group'])));

		if ($emajOK) {
			$this->printMsg($msg,$errMsg);

		// general information about the group
			$group = $emajdb->getGroup($_REQUEST['group']);

		// save some fields before calling printTable()
			$comment=$group->fields['group_comment'];
			$nbMarks = $group->fields['nb_mark'];
			$groupState = $group->fields['group_state'];
			$groupType = $group->fields['group_type'];
			$hasWaitingChanges = $group->fields['has_waiting_changes'];

			$columns = array(
				'state' => array(
					'title' => $lang['emajstate'],
					'field' => field('group_state'),
					'type'	=> 'callback',
					'params'=> array('function' => array($this,'renderGroupState'),'align' => 'center')
				),
				'creationdatetime' => array(
					'title' => $lang['emajcreationdatetime'],
					'field' => field('group_creation_datetime'),
					'params'=> array('align' => 'center'),
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
				'nbtbl' => array(
					'title' => $lang['emajnbtbl'],
					'field' => field('group_nb_table'),
					'type'  => 'numeric'
				),
				'nbseq' => array(
					'title' => $lang['emajnbseq'],
					'field' => field('group_nb_sequence'),
					'type'  => 'numeric'
				),
				'nbmark' => array(
					'title' => $lang['emajnbmark'],
					'field' => field('nb_mark'),
					'type'  => 'numeric'
				),
				'logsize' => array(
					'title' => $lang['emajlogsize'],
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
			echo "<h3>".sprintf($lang['emajgroupproperties'], htmlspecialchars($_REQUEST['group']))."</h3>\n";
			$misc->printTable($group, $columns, $groupActions, 'detailGroup', 'no group, internal error !');

		// display group's comment if exists
			if ($comment<>'') {
				echo "<p>{$lang['strcomment']} : ",$comment,"</p>\n";
			}

		// display the buttons corresponding to the available functions for the group, depending on its state

			if ($emajdb->isEmaj_Adm()) {
				echo "<p>\n";

				// start_group
				if ($groupState == 'IDLE') {
					echo "<form id=\"start_group_form\" action=\"plugin.php?plugin={$this->name}&amp;action=start_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline;\">\n";
					echo "  <input type=\"submit\" name=\"startgroup\" value=\"{$lang['strstart']}\" />";
					echo "</form>\n";
				}

				// set_mark_group
				if ($groupState == 'LOGGING') {
					echo "<form id=\"set_mark_group_form\" action=\"plugin.php?plugin={$this->name}&amp;action=set_mark_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline;\">\n";
					echo "  <input type=\"submit\" name=\"setmarkgroup\" value=\"{$lang['emajsetmark']}\" />";
					echo "</form>\n";
				}

				// reset_group
				if ($groupState == 'IDLE') {
					echo "<form id=\"reset_group_form\" action=\"plugin.php?plugin={$this->name}&amp;action=reset_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:5px;\">\n";
					echo "  <input type=\"submit\" name=\"resetgroup\" value=\"{$lang['strreset']}\" />";
					echo "</form>\n";
				}

				// protect_group
				if ($groupState == 'LOGGING' && $groupType == "ROLLBACKABLE") {
					echo "<form id=\"protect_group_form\" action=\"plugin.php?plugin={$this->name}&amp;action=protect_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:5px;\">\n";
					echo "  <input type=\"submit\" name=\"protectgroup\" value=\"{$lang['emajprotect']}\" />";
					echo "</form>\n";
				}

				// unprotect_group
				if ($groupState == 'LOGGING' && $groupType == "ROLLBACKABLE-PROTECTED") {
					echo "<form id=\"unprotect_group_form\" action=\"plugin.php?plugin={$this->name}&amp;action=unprotect_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:5px;\">\n";
					echo "  <input type=\"submit\" name=\"unprotectgroup\" value=\"{$lang['emajunprotect']}\" />";
					echo "</form>\n";
				}

				// stop_group
				if ($groupState == 'LOGGING') {
					echo "<form id=\"stop_group_form\" action=\"plugin.php?plugin={$this->name}&amp;action=stop_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:5px;\">\n";
					echo "  <input type=\"submit\" name=\"stopgroup\" value=\"{$lang['strstop']}\" />";
					echo "</form>\n";
				}

				// comment_group
				echo "<form id=\"comment_group_form\" action=\"plugin.php?plugin={$this->name}&amp;action=comment_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:30px;\">\n";
				echo "  <input type=\"submit\" name=\"commentgroup\" value=\"{$lang['emajsetcomment']}\" />";
				echo "</form>\n";

				// alter_group
				if ($hasWaitingChanges && ($groupState == 'IDLE' || $emajdb->getNumEmajVersion() >= 20100)) {
					echo "<form id=\"alter_group_form\" action=\"plugin.php?plugin={$this->name}&amp;action=alter_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:5px;\">\n";
					echo "  <input type=\"submit\" name=\"altergroup\" value=\"{$lang['emajApplyConfChanges']}\" />";
					echo "</form>\n";
				}

				// drop_group
				if ($groupState == 'IDLE') {
					echo "<form id=\"drop_group_form\" action=\"plugin.php?plugin={$this->name}&amp;action=drop_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:5px;\">\n";
					echo "  <input type=\"submit\" name=\"dropgroup\" value=\"{$lang['strdrop']}\" />";
					echo "</form>\n";
				}

				echo "</p>\n";
			}

		// Show marks of the groups

		// get marks from database
			$marks = $emajdb->getMarks($_REQUEST['group']);

			echo "<hr/>\n";
			echo "<h3>".sprintf($lang['emajgroupmarks'], htmlspecialchars($_REQUEST['group']))."</h3>\n";

			$columns = array(
				'mark' => array(
					'title' => $lang['emajmark'],
					'field' => field('mark_name'),
				),
				'state' => array(
					'title' => $lang['emajstate'],
					'field' => field('mark_state'),
					'type'	=> 'callback',
					'params'=> array('function' => array($this,'renderMarkState'),'align' => 'center'),
					'filter'=> false,
				),
				'datetime' => array(
					'title' => $lang['emajtimestamp'],
					'field' => field('mark_datetime'),
				),
				'logrows' => array(
					'title' => $lang['emajnbchanges'],
					'field' => field('mark_logrows'),
					'type'  => 'numeric'
				),
				'cumlogrows' => array(
					'title' => $lang['emajcumchanges'],
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
			if ($emajdb->isEmaj_Adm() && ($nbMarks > 1)) {
				$actions = array_merge($actions, array(
					'multiactions' => array(
						'keycols' => array('group' => 'mark_group', 'mark' => 'mark_name'),
						'url' => "plugin.php?plugin={$this->name}&amp;group={$_REQUEST['group']}&amp;back=detail&amp;",
					),
				));
			}
			if ($emajdb->isEmaj_Adm() && $groupType == "ROLLBACKABLE") {
				$actions = array_merge($actions, array(
					'rollbackgroup' => array(
						'content' => $lang['emajrlbk'],
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
			if ($emajdb->isEmaj_Adm()) {
				$actions = array_merge($actions, array(
					'renamemark' => array(
						'content' => $lang['emajrename'],
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
				));
			}
			if ($emajdb->isEmaj_Adm() && ($nbMarks > 1)) {
				$actions = array_merge($actions, array(
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
								)))),
						'multiaction' => 'delete_marks',
					),
				));
			}
			if ($emajdb->isEmaj_Adm()) {
				$actions = array_merge($actions, array(
					'deletebeforemark' => array(
						'content' => $lang['emajfirstmark'],
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
			if ($emajdb->isEmaj_Adm() && $groupState == 'LOGGING' && $groupType != "AUDIT_ONLY") {
				$actions = array_merge($actions, array(
					'protectmark' => array(
						'content' => $lang['emajprotect'],
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
						'content' => $lang['emajunprotect'],
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
			if ($emajdb->isEmaj_Adm()) {
				$actions = array_merge($actions, array(
					'commentmark' => array(
						'content' => $lang['emajsetcomment'],
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
			$misc->printTable($marks, $columns, $actions, 'marks', $lang['emajnomark'], array($this,'markPre'), array('sorter' => false, 'filter' => true));

			// JQuery to remove the last deleteBeforeMark button as it is meaningless on the first set mark
			echo "<script type=\"text/javascript\">\n";
			echo "  $(\"table.data tr:last td:contains('{$lang['emajfirstmark']}')\").removeClass()\n";
			echo "  $(\"table.data tr:last a:contains('{$lang['emajfirstmark']}')\").remove()\n";
			echo "</script>\n";
		}

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Show global or detailed log statistics between 2 marks or since a mark
	 */
	function log_stat_group() {
		global $misc, $lang, $emajdb;

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

		$emajOK = $this->printEmajHeader("action=log_stat_group&amp;group=".urlencode($_REQUEST['group']),sprintf($lang['emajshowstat'], htmlspecialchars($_REQUEST['group'])));

		if ($emajOK) {

		// display the stat form

			// get marks from database
			$marks = $emajdb->getMarks($_REQUEST['group']);

			// get group's characteristics
			$group = $emajdb->getGroup($_REQUEST['group']);

			if ($marks->recordCount() < 1) {

				// No mark recorded for the group => no update logged => no stat to display
				echo "<p>{$lang['emajnomark']}</p>\n"; 

			} else {

				// form for statistics selection
				echo "<style type=\"text/css\">[disabled]{color:#933;}</style>";
				echo "<form id=\"statistics_form\" action=\"plugin.php?plugin={$this->name}&amp;action=log_stat_group&amp;back=detail&amp;{$misc->href}\"";
				echo "  method=\"post\" enctype=\"multipart/form-data\">\n";

				// First mark defining the marks range to analyze
				echo "<p>{$lang['emajfrom']}\n";
				echo "  <select name=\"rangestart\" id=\"rangestart\">\n";
				foreach($marks as $r)
					echo "    <option value=\"", htmlspecialchars($r['mark_name']), "\" >", htmlspecialchars($r['mark_name']), " ({$r['mark_datetime']})</option>\n";
				echo "  </select>\n";

				// Last mark defining the marks range to analyze
				echo "{$lang['emajto']}\n";
				echo "  <select name=\"rangeend\" id=\"rangeend\" >\n";
				echo "    <option value=\"currentsituation\">{$lang['emajcurrentsituation']}</option>\n";
				foreach($marks as $r)
					echo "    <option value=\"", htmlspecialchars($r['mark_name']), "\" >", htmlspecialchars($r['mark_name']), " ({$r['mark_datetime']})</option>\n";
				echo "  </select></p>\n";

				// Other elements of the form
				echo "  <p><input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";

				// Rollback simulation checkbox is only available for rollbackable groups in logging state
				if ($group->fields['group_type'] == 'ROLLBACKABLE' && $group->fields['group_state'] == 'LOGGING') {
					echo "  {$lang['emajsimrlbk']} <input type=\"checkbox\" name=\"simrlbk\" id=\"simrlbk\"/>\n";
				}

				echo "  <input type=\"submit\" name=\"globalstatgroup\" value=\"{$lang['emajestimates']}\" />\n";
				echo "  <input type=\"submit\" name=\"detailedstatgroup\" value=\"{$lang['emajdetailedstat']}\" />\n";
				echo "  <img src=\"{$misc->icon(array($this->name,'EmajWarning'))}\" alt=\"warning\" title=\"{$lang['emajdetailedlogstatwarning']}\" style=\"vertical-align:middle\"/>";
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
					if ($emajdb->isMarkActiveGroup($_REQUEST['group'],$_REQUEST['rangestart']) == 1) {
						$rlbkDuration = $emajdb->estimateRollbackGroup($_REQUEST['group'],$_REQUEST['rangestart']);
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
		global $misc, $lang, $emajdb;

		// Get statistics from E-Maj
		if ($_REQUEST['rangeend']=='currentsituation') {
			$w1 = $lang['emajlogstatcurrentsituation'];
			$stats = $emajdb->getLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],'');
		} else {
			$w1 = sprintf($lang['emajlogstatmark'], $_REQUEST['rangeend']);
			$stats = $emajdb->getLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],$_REQUEST['rangeend']);
		}
		$summary = $emajdb->getLogStatSummary();

		// Title
		echo "<hr/>\n";
		echo "<h3>".sprintf($lang['emajlogstattittle'], htmlspecialchars($_REQUEST['rangestart']), htmlspecialchars($w1), htmlspecialchars($_REQUEST['group']))."</h3>\n";

		// Display summary statistics
		echo "<div style=\"margin-bottom:15px\">\n";
		echo "<table><tr>\n";
		echo "<th class=\"data\" colspan=2>{$lang['emajestimates']}</th>\n";
		echo "</tr><tr>\n";
		echo "<th class=\"data\">{$lang['emajnbtbl']}</th>";
		echo "<th class=\"data\">{$lang['emajnbchanges']}</th>";
		echo "</tr><tr class=\"data1\">\n";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_tables']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['sum_rows']}</div></td>";
		echo "</tr></table>\n";
		echo "</div>\n";
		
		// Display rollback duration estimate if requested
		if ($rlbkDuration != '') {
			if ($rlbkDuration == '-') {
				// the start mark cannot be used for a rollback
				echo "<p>{$lang['emajnosimrlbkduration']}</p>";
			} else {
				// dispay the duration estimate
				echo "<p>",sprintf($lang['emajsimrlbkduration'], htmlspecialchars($_REQUEST['group']), htmlspecialchars($_REQUEST['rangestart']),$rlbkDuration),"</p>\n";
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
			if ($emajdb->getNumEmajVersion() >= 20300) {			// version >= 2.3.0
				$columns = array_merge($columns, array(
					'stat_first_mark' => array(
						'title' => $lang['emajstatfirstmark'],
						'field' => field('stat_first_mark'),
					),
					'stat_first_mark_datetime' => array(
						'title' => $lang['emajstatfirstmarkdatetime'],
						'field' => field('stat_first_mark_datetime'),
					),
					'stat_last_mark' => array(
						'title' => $lang['emajstatlastmark'],
						'field' => field('stat_last_mark'),
					),
					'stat_last_mark_datetime' => array(
						'title' => $lang['emajstatlastmarkdatetime'],
						'field' => field('stat_last_mark_datetime'),
					),
				));
			}
			$columns = array_merge($columns, array(
				'nbrow' => array(
					'title' => $lang['emajstatrows'],
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

			$misc->printTable($stats, $columns, $actions, 'logStats', null, null, array('sorter' => true, 'filter' => true));

			// dynamicaly change the behaviour of the SQL link using JQuery code: open a new window
			$sql_window_id = htmlentities('emaj_sqledit:'.$_REQUEST['server']);
				echo "<script type=\"text/javascript\">
				$(\"#logStats a:contains('SQL')\").click(function() {
					window.open($(this).attr('href'),'{$sql_window_id}','toolbar=no,width=700,height=400,resizable=yes,scrollbars=yes').focus();
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
		global $misc, $lang, $emajdb;

		if (!ini_get('safe_mode')) set_time_limit(0);		// Prevent timeouts on large stats (non-safe mode only)

		// Get statistics from E-Maj
		if ($_REQUEST['rangeend']=='currentsituation') {
			$w1 = $lang['emajlogstatcurrentsituation'];
			$stats = $emajdb->getDetailedLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],'');
		} else {
			$w1 = sprintf($lang['emajlogstatmark'], $_REQUEST['rangeend']);
			$stats = $emajdb->getDetailedLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],$_REQUEST['rangeend']);
		}
		$summary = $emajdb->getDetailedLogStatSummary();

		$roles = $emajdb->getDetailedLogStatRoles();
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
		echo "<h3>".sprintf($lang['emajlogstattittle'], htmlspecialchars($_REQUEST['rangestart']), htmlspecialchars($w1), htmlspecialchars($_REQUEST['group']))."</h3>\n";

		// Display summary statistics
		echo "<div style=\"margin-bottom:15px\">\n";
		echo "<table><tr>\n";
		echo "<th class=\"data\">{$lang['emajnbtbl']}</th>";
		echo "<th class=\"data\">{$lang['emajnbchanges']}</th>";
		echo "<th class=\"data\">{$lang['emajnbinsert']}</th>";
		echo "<th class=\"data\">{$lang['emajnbupdate']}</th>";
		echo "<th class=\"data\">{$lang['emajnbdelete']}</th>";
		echo "<th class=\"data\">{$lang['emajnbtruncate']}</th>";
		echo "<th class=\"data\">{$lang['emajnbrole']}</th>";
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
				echo "<p>{$lang['emajnosimrlbkduration']}</p>";
			} else {
				// dispay the duration estimate
				echo "<p>",sprintf($lang['emajsimrlbkduration'], htmlspecialchars($_REQUEST['group']), htmlspecialchars($_REQUEST['rangestart']),$rlbkDuration),"</p>\n";
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
			if ($emajdb->getNumEmajVersion() >= 20300) {			// version >= 2.3.0
				$columns = array_merge($columns, array(
					'stat_first_mark' => array(
						'title' => $lang['emajstatfirstmark'],
						'field' => field('stat_first_mark'),
					),
					'stat_first_mark_datetime' => array(
						'title' => $lang['emajstatfirstmarkdatetime'],
						'field' => field('stat_first_mark_datetime'),
					),
					'stat_last_mark' => array(
						'title' => $lang['emajstatlastmark'],
						'field' => field('stat_last_mark'),
					),
					'stat_last_mark_datetime' => array(
						'title' => $lang['emajstatlastmarkdatetime'],
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
					'title' => $lang['emajstatverb'],
					'field' => field('stat_verb'),
				),
				'nbrow' => array(
					'title' => $lang['emajstatrows'],
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

			$misc->printTable($stats, $columns, $actions, 'detailedLogStats', null, null, array('sorter' => true, 'filter' => true));

			// dynamicly change the behaviour of the SQL link using JQuery code: open a new window
			$sql_window_id = htmlentities('emaj_sqledit:'.$_REQUEST['server']);
			echo "<script type=\"text/javascript\">
				$(\"#detailedLogStats a:contains('SQL')\").click(function() {
					window.open($(this).attr('href'),'{$sql_window_id}','toolbar=no,width=700,height=400,resizable=yes,scrollbars=yes').focus();
					return false;
				});
				</script>";
		}
	}

	/**
	 * Displays the list of tables and sequences that composes a group
	 */
	function show_content_group() {
		global $misc, $lang, $emajdb;

		$this->printPageHeader('emajgroup','emajcontent');

		$emajOK = $this->printEmajHeader("action=show_content_group&amp;group=".urlencode($_REQUEST['group']),sprintf($lang['emajgroupcontent'],htmlspecialchars($_REQUEST['group'])));

		if ($emajOK) {

			$groupContent = $emajdb->getContentGroup($_REQUEST['group']);

			if ($groupContent->recordCount() < 1) {

				// The group is empty
				echo "<p>" . sprintf($lang['emajemptygroup'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";

			} else {

				$columns = array(
					'type' => array(
						'title' => $lang['strtype'],
						'field' => field('relkind'),
						'type'	=> 'callback',
						'params'=> array('function' => array($this, 'renderTblSeq'),'align' => 'center'),
						'sorter_text_extraction' => 'img_alt',
						'filter'=> false,
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
						'title' => $lang['emajpriority'],
						'field' => field('rel_priority'),
						'params'=> array('align' => 'center'),
					),
					'log_schema' => array(
						'title' => $lang['emajlogschema'],
						'field' => field('rel_log_schema'),
					),
					'log_dat_tsp' => array(
						'title' => $lang['emajlogdattsp'],
						'field' => field('rel_log_dat_tsp'),
					),
					'log_idx_tsp' => array(
						'title' => $lang['emajlogidxtsp'],
						'field' => field('rel_log_idx_tsp'),
					),
					'names_prefix' => array(
						'title' => $lang['emajnamesprefix'],
						'field' => field('emaj_names_prefix'),
					),
					'bytelogsize' => array(
						'title' => $lang['emajlogsize'],
						'field' => field('byte_log_size'),
						'params'=> array('align' => 'right'),
						'filter'=> false,
					),
					'prettylogsize' => array(
						'title' => $lang['emajlogsize'],
						'field' => field('pretty_log_size'),
						'params'=> array('align' => 'center'),
						'sorter'=> false,
					),
				);
	
				$actions = array ();
	
				echo "<p></p>";
				$misc->printTable($groupContent, $columns, $actions, 'groupContent', null, null, array('sorter' => true, 'filter' => true));
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
		global $misc, $lang, $emajdb;

		// Test at least 1 table/sequence is to be processed
		if (empty($_REQUEST['tblseq']) && empty($_REQUEST['ma'])) {
			$this->configure_groups($lang['emajspecifytblseqtoassign']);
			exit();
		}
		// Test all tables/sequences to process are not yet assigned to a group and have a valid type
		if (isset($_REQUEST['ma'])) {
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				if ($a['group'] != '') {
					$this->configure_groups('', sprintf($lang['emajtblseqyetgroup'],$a['appschema'],$a['tblseq']), $a['appschema']);
					exit();
				}
				if ($a['type'] != 'r+' and $a['type'] != 'S+') {
					$this->configure_groups('', sprintf($lang['emajtblseqbadtype'],$a['appschema'],$a['tblseq']), $a['appschema']);
					exit();
				}
			}
		}

		$this->printPageHeader('emaj','emajconfiguregroups');

		$misc->printTitle($lang['emajassigntblseq']);

		// Get group names already known in emaj_group_def table
		$knownGroups = $emajdb->getKnownGroups();

		// Get log schema suffix already known in emaj_group_def table
		$knownSuffix = $emajdb->getKnownSuffix();

		// Get tablespaces the current user can see
		$knownTsp = $emajdb->getKnownTsp();

		// Build the list of tables and sequences to processs and count them
		$nbTbl = 0; $nbSeq = 0;
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
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
		echo "<table>\n";

		// group name
		echo "<tr><th class=\"data left\" style=\"text-align:right\">{$lang['emajgroup']}</th>";
		echo "<td class=\"data1\">";
		echo "<input type=\"text\" name=\"group\" list=\"groupList\" required pattern=\"\S+.*\" value=\"\" placeholder='{$lang['emajrequiredfield']}' autocomplete=\"off\"/>\n";
		echo "</td><td style=\"text-align:center\">*\n";
		echo "</td></tr>\n";
		echo "<datalist id=\"groupList\">\n";
		if ($knownGroups->recordCount() > 0) {
			foreach($knownGroups as $r)
				echo "<option value=\"", htmlspecialchars($r['group_name']), "\">\n";
		}
		echo "</datalist>\n";

		// priority level
		echo "<tr><th class=\"data left\" style=\"text-align:right\">{$lang['emajenterpriority']}</th>";
		echo "<td class=\"data1\">";
		echo "<input type=\"number\" name=\"priority\" style=\"width:6em; text-align:right;\" min=\"0\" max=\"2147483647\" value=\"\" />";
		echo "</td><td><img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajpriorityhelp']}\"/>";
		echo "</td></tr>\n";

		// log schema name suffix
		if ($nbTbl >= 1) {
			echo "<tr><th class=\"data left\" style=\"text-align:right\">{$lang['emajenterlogschema']}</th>";
			echo "<td class=\"data1\">";
			echo "<input type=\"text\" name=\"suffix\" list=\"suffixList\" value=\"\"/ autocomplete=\"off\">";
			echo "</td><td><img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajlogschemahelp']}\"/>";
			echo "</td></tr>\n";
			echo "<datalist id=\"suffixList\">\n";
			if ($knownSuffix->recordCount() > 0) {
				foreach($knownSuffix as $r)
					echo "<option value=\"", htmlspecialchars($r['known_suffix']), "\">\n";
			}
			echo "</datalist>\n";
		} else {
			echo "<p><input type=\"hidden\" name=\"suffix\" value=\"\" />\n";
		}

		// objects name prefix (only for tables)
		if ($nbTbl == 1) {
			// the names prefix is accessible only for a single table assignment
			echo "<tr><th class=\"data left\" style=\"text-align:right\">{$lang['emajenternameprefix']}</th>";
			echo "<td class=\"data1\">";
			echo "<input type=\"text\" name=\"nameprefix\" value=\"\"/>";
			echo "</td><td><img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajnameprefixhelp']}\"/>";
			echo "</td></tr>\n";
		} else {
			echo "<p><input type=\"hidden\" name=\"nameprefix\" value=\"\" />\n";
		}
		// log tablespace (only for tables)
		if ($nbTbl >= 1) {
			// data log tablespace
			echo "<tr><th class=\"data left\" style=\"text-align:right\">{$lang['emajenterlogdattsp']}</th>";
			echo "<td class=\"data1\">";
			echo "<input type=\"text\" name=\"logdattsp\" list=\"tspList\" value=\"\" autocomplete=\"off\"/>";
			echo "</td><td></td></tr>\n";
			// index log tablespace
			echo "<tr><th class=\"data left\" style=\"text-align:right\">{$lang['emajenterlogidxtsp']}</th>";
			echo "<td class=\"data1\">";
			echo "<input type=\"text\" name=\"logidxtsp\"  list=\"tspList\" value=\"\" autocomplete=\"off\"/>";
			echo "</td><td></td></tr>\n";
			echo "<datalist id=\"tspList\">\n";
			if ($knownTsp->recordCount() > 0) {
				foreach($knownTsp as $r)
					echo "<option value=\"", htmlspecialchars($r['spcname']), "\">\n";
			}
			echo "</datalist>\n";
		} else {
			echo "<p><input type=\"hidden\" name=\"logdattsp\" value=\"\" />\n";
			echo "<p><input type=\"hidden\" name=\"logidxtsp\" value=\"\" />\n";
		}
		echo "</table>\n";

		echo "<br>* = {$lang['emajrequiredfield']}";
		echo"</p>\n";
		echo $misc->form;
		echo "<p><input type=\"submit\" name=\"assigntblseq\" value=\"{$lang['emajassign']}\" id=\"ok\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform table/sequence insertion into a tables group
	 */
	function assign_tblseq_ok() {
		global $lang, $data, $emajdb;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {$this->configure_groups(); exit();}

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
						$this->configure_groups('', $lang['emajmodifygrouperr']);
						return;
					}
				}
			}
			if($data->endTransaction() == 0)
				$this->configure_groups($lang['emajmodifygroupok']);
			else
				$this->configure_groups('', $lang['emajmodifygrouperr']);

		} else {

		// single assignement
			$status = $emajdb->assignTblSeq($_POST['appschema'],$_POST['tblseq'],$_POST['group'],
								$_POST['priority'], $_POST['suffix'], $_POST['nameprefix'], $_POST['logdattsp'], $_POST['logidxtsp']);
			if ($status == 0)
				$this->configure_groups($lang['emajmodifygroupok']);
			else
				$this->configure_groups('', $lang['emajmodifygrouperr']);
		}
	}

	/**
	 * Prepare update a table/sequence into a group: ask for properties and confirmation
	 */
	function update_tblseq() {
		global $misc, $lang, $emajdb;

		// Test at least 1 table/sequence is to be processed
		if (empty($_REQUEST['tblseq'])) {
			$this->configure_groups($lang['emajspecifytblseqtoupdate']);
			exit();
		}
		// Test the table/sequence is already assign to a group
		if ($_REQUEST['group'] == '') {
			$this->configure_groups('', sprintf($lang['emajtblseqnogroup'],$_REQUEST['appschema'],$_REQUEST['tblseq']), $_REQUEST['appschema']);
			exit();
		}

		$this->printPageHeader('emaj','emajconfiguregroups');

		$misc->printTitle($lang['emajupdatetblseq']);

		// Get group names already known in emaj_group_def table
		$knownGroups = $emajdb->getKnownGroups();

		// Get log schema suffix already known in emaj_group_def table
		$knownSuffix = $emajdb->getKnownSuffix();

		// Get tablespaces the current user can see
		$knownTsp = $emajdb->getKnownTsp();

		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
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
		echo "<table>\n";
		// group name
		echo "<tr><th class=\"data left\" style=\"text-align:right\">{$lang['emajgroup']}</th>";
		echo "<td class=\"data1\">";
		echo "<input type=\"text\" name=\"groupnew\" list=\"groupList\" required pattern=\"\S+.*\" value=\"", htmlspecialchars($_REQUEST['group']), "\" placeholder='{$lang['emajrequiredfield']}' autocomplete=\"off\"/>\n";
		echo "</td><td style=\"text-align:center\">*\n";
		echo "</td></tr>\n";
		echo "<datalist id=\"groupList\">\n";
		if ($knownGroups->recordCount() > 0) {
			foreach($knownGroups as $r)
				echo "<option value=\"", htmlspecialchars($r['group_name']), "\">\n";
		}
		echo "</datalist>\n";

		// priority level
		echo "<tr><th class=\"data left\" style=\"text-align:right\">{$lang['emajenterpriority']}</th>";
		echo "<td class=\"data1\">";
		echo "<input type=\"number\" name=\"priority\" style=\"width:6em; text-align:right;\" min=\"0\" max=\"2147483647\" value=\"{$_REQUEST['priority']}\" />";
		echo "</td><td><img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajpriorityhelp']}\"/>";
		echo "</td></tr>\n";

		// log schema name suffix (only for tables)
		if ($_REQUEST['type'] == 'r+') {
			echo "<tr><th class=\"data left\" style=\"text-align:right\">{$lang['emajenterlogschema']}</th>";
			echo "<td class=\"data1\">";
			echo "<input type=\"text\" name=\"suffix\" list=\"suffixList\" value=\"", htmlspecialchars($_REQUEST['logschemasuffix']), "\"/ autocomplete=\"off\">";
			echo "</td><td><img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajlogschemahelp']}\"/>";
			echo "</td></tr>\n";
			echo "<datalist id=\"suffixList\">\n";
			if ($knownSuffix->recordCount() > 0) {
				foreach($knownSuffix as $r)
					echo "<option value=\"", htmlspecialchars($r['known_suffix']), "\">\n";
			}
			echo "</datalist>\n";
		} else {
			echo "<p><input type=\"hidden\" name=\"suffix\" value=\"\" />\n";
		}

		// objects name prefix (only for tables)
		if ($_REQUEST['type'] == 'r+') {
			// the names prefix is accessible only for a table
			echo "<tr><th class=\"data left\" style=\"text-align:right\">{$lang['emajenternameprefix']}</th>";
			echo "<td class=\"data1\">";
			echo "<input type=\"text\" name=\"nameprefix\" value=\"", htmlspecialchars($_REQUEST['emajnamesprefix']), "\"/>";
			echo "</td><td><img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajnameprefixhelp']}\"/>";
			echo "</td></tr>\n";
		} else {
			echo "<p><input type=\"hidden\" name=\"nameprefix\" value=\"\" />\n";
		}

		// log tablespaces (only for tables)
		if ($_REQUEST['type'] == 'r+') {
			// data log tablespace
			echo "<tr><th class=\"data left\" style=\"text-align:right\">{$lang['emajenterlogdattsp']}</th>";
			echo "<td class=\"data1\">";
			echo "<input type=\"text\" name=\"logdattsp\" list=\"tspList\" value=\"", htmlspecialchars($_REQUEST['logdattsp']), "\" autocomplete=\"off\"/>";
			echo "</td><td></td></tr>\n";
			// index log tablespace
			echo "<tr><th class=\"data left\" style=\"text-align:right\">{$lang['emajenterlogidxtsp']}</th>";
			echo "<td class=\"data1\">";
			echo "<input type=\"text\" name=\"logidxtsp\"  list=\"tspList\" value=\"", htmlspecialchars($_REQUEST['logidxtsp']), "\" autocomplete=\"off\"/>";
			echo "</td><td></td></tr>\n";
			echo "<datalist id=\"tspList\">\n";
			if ($knownTsp->recordCount() > 0) {
				foreach($knownTsp as $r)
					echo "<option value=\"", htmlspecialchars($r['spcname']), "\">\n";
			}
			echo "</datalist>\n";
		} else {
			echo "<p><input type=\"hidden\" name=\"logdattsp\" value=\"\" />\n";
			echo "<p><input type=\"hidden\" name=\"logidxtsp\" value=\"\" />\n";
		}
		echo "</table>\n";

		echo "<br>* = {$lang['emajrequiredfield']}";
		echo $misc->form;
		echo"</p>\n";
		echo "<p><input type=\"submit\" name=\"updatetblseq\" value=\"{$lang['strupdate']}\" id=\"ok\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform table/sequence insertion into a tables group
	 */
	function update_tblseq_ok() {
		global $lang, $data, $emajdb;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {$this->configure_groups(); exit();}

		$status = $emajdb->updateTblSeq($_POST['appschema'],$_POST['tblseq'],$_POST['groupold'],$_POST['groupnew'],
							$_POST['priority'], $_POST['suffix'], $_POST['nameprefix'], $_POST['logdattsp'], $_POST['logidxtsp']);
		if ($status == 0)
			$this->configure_groups($lang['emajmodifygroupok']);
		else
			$this->configure_groups('', $lang['emajmodifygrouperr']);
	}

	/**
	 * Prepare remove a table/sequence from a group: ask for confirmation
	 */
	function remove_tblSeq() {
		global $misc, $lang;

		// Test at least 1 table/sequence is to be processed
		if (empty($_REQUEST['tblseq']) && empty($_REQUEST['ma'])) {
			$this->configure_groups($lang['emajspecifytblseqtoremove']);
			exit();
		}
		// Test all tables/sequences to process are already assigned to a group
		if (isset($_REQUEST['ma'])) {
			foreach($_REQUEST['ma'] as $t) {
				$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
				if ($a['group'] == '') {
					$this->configure_groups('', sprintf($lang['emajtblseqnogroup'],$a['appschema'],$a['tblseq']), $a['appschema']);
					exit();
				}
			}
		}

		$this->printPageHeader('emaj','emajconfiguregroups');

		$misc->printTitle($lang['emajremovetblseq']);

		$nbTbl = 0; $nbSeq = 0;
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"remove_tblseq_ok\" />\n";

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
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform table/sequence removal from a tables group
	 */
	function remove_tblseq_ok() {
		global $lang, $data, $emajdb;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {$this->configure_groups(); exit();}

		if (is_array($_POST['tblseq'])) {
		// multiple removal
			$status = $data->beginTransaction();
			if ($status == 0) {
				for($i = 0; $i < sizeof($_POST['tblseq']); ++$i)
				{
					$status = $emajdb->removeTblSeq($_POST['appschema'][$i],$_POST['tblseq'][$i],$_POST['group'][$i]);
					if ($status != 0) {
						$data->rollbackTransaction();
						$this->configure_groups('', $lang['emajmodifygrouperr']);
						return;
					}
				}
			}
			if($data->endTransaction() == 0)
				$this->configure_groups($lang['emajmodifygroupok']);
			else
				$this->configure_groups('', $lang['emajmodifygrouperr']);

		} else {
		// single removal
			$status = $emajdb->removeTblSeq($_POST['appschema'],$_POST['tblseq'],$_POST['group']);

			if ($status == 0)
				$this->configure_groups($lang['emajmodifygroupok']);
			else
				$this->configure_groups('', $lang['emajmodifygrouperr']);
		}
	}

	/**
	 * Prepare create group: ask for confirmation
	 */
	function create_group() {
		global $misc, $lang;

		$this->printPageHeader('emaj','emajgroups');

		$misc->printTitle($lang['emajcreateagroup']);

		if (htmlspecialchars($_REQUEST['empty'])=='false') {
			echo "<p>", sprintf($lang['emajconfirmcreategroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		} else {
			echo "<p>{$lang['emajcreateanemptygroup']}</p>\n";
		}
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"create_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo "<input type=\"hidden\" name=\"empty\" value=\"", htmlspecialchars($_REQUEST['empty']), "\" />\n";
		echo $misc->form;

	// if the group name is not defined, ask for a mark name
		if (htmlspecialchars($_REQUEST['empty'])=='false') {
			echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		} else {
			echo "<table>\n";
			echo "<tr><th class=\"data left\">{$lang['emajgroup']}</th>\n";
			echo "<td class=\"data1\"><input name=\"group\" size=\"32\" value=\"\" id=\"group\"></td></tr>\n";
			echo "</table>\n";
		}

		echo "<p>{$lang['emajgrouptype']} : ";
		echo "<input type=\"radio\" name=\"grouptype\" value=\"rollbackable\" checked>{$lang['emajrollbackable']}";
		echo "<input type=\"radio\" name=\"grouptype\" value=\"auditonly\">{$lang['emajauditonly']}\n";
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
		global $lang, $emajdb, $_reload_browser;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {$this->show_groups(); exit();}

	// if the group is supposed to be empty, check the supplied group name doesn't exist
		if ($_POST['empty'] == 'true' && !$emajdb->isNewEmptyGroupValid($_POST['group'])) {
			$this->show_groups('',sprintf($lang['emajinvalidemptygroup'], htmlspecialchars($_POST['group'])));
			return;
		}

		$status = $emajdb->createGroup($_POST['group'],$_POST['grouptype']=='rollbackable',$_POST['empty']=='true');
		if ($status == 0) {
			$_reload_browser = true;
			$this->show_groups(sprintf($lang['emajcreategroupok'], htmlspecialchars($_POST['group'])));
		}else
			$this->show_groups('',sprintf($lang['emajcreategrouperr'], htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare drop group: ask for confirmation
	 */
	function drop_group() {
		global $misc, $lang;

		if ($_REQUEST['back']=='list')
			$this->printPageHeader('emaj','emajgroups');
		else
			$this->printPageHeader('emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajdropagroup']);

		echo "<p>", sprintf($lang['emajconfirmdropgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
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
		global $lang, $emajdb, $_reload_browser;

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
		$group = $emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'IDLE') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajcantdropgroup'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajcantdropgroup'], htmlspecialchars($_POST['group'])));
			}
			return;
		}

	// OK
		$status = $emajdb->dropGroup($_POST['group']);
		if ($status == 0) {
			$_reload_browser = true;
			$this->show_groups(sprintf($lang['emajdropgroupok'], htmlspecialchars($_POST['group'])));
		}else
			$this->show_groups('',sprintf($lang['emajdropgrouperr'], htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare alter group: ask for confirmation
	 */
	function alter_group() {
		global $misc, $lang, $emajdb;

		if ($_REQUEST['back']=='list')
			$this->printPageHeader('emaj','emajgroups');
		else
			$this->printPageHeader('emajgroup','emajgroupproperties');
		$misc->printTitle($lang['emajaltergroups']);

		$isGroupLogging = $emajdb->isGroupLogging($_REQUEST['group']);

		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";

		if ($emajdb->getNumEmajVersion() >= 20100) {			// version >= 2.1.0
			if ($isGroupLogging) {
				echo "<p>", sprintf($lang['emajalteraloggingroup'], htmlspecialchars($_REQUEST['group'])), "</p>";
				echo "<table>\n";
				echo "<tr><th class=\"data left\">{$lang['emajmark']}</th>\n";
				echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"ALTER_%\" id=\"mark\">\n";
				echo "<img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></td></tr>";
				echo "</table>\n";
			} else {
				echo "<p>", sprintf($lang['emajconfirmaltergroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
				echo "<input type=\"hidden\" name=\"mark\" value=\"\">";
			}
		} else {
				echo "<p>", sprintf($lang['emajconfirmaltergroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
				echo "<input type=\"hidden\" name=\"mark\" value=\"\">";
		}

		echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"altergroup\" value=\"{$lang['emajApplyConfChanges']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform alter group
	 */
	function alter_group_ok() {
		global $lang, $emajdb, $_reload_browser;

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
		$check = $emajdb->checkAlterGroup($_REQUEST['group']);
		if ($check == 0) {
			if ($_POST['back'] == 'list') {
				$this->show_groups('',sprintf($lang['emajcantaltergroup'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajcantaltergroup'], htmlspecialchars($_POST['group'])));
			}
			exit();
		}

	// Check the supplied mark is valid
		if ($_POST['mark'] != '') {
			$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['group'], htmlspecialchars($_POST['mark']));
			if (is_null($finalMarkName)) {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
				} else {
					$this->show_group('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
				}
				return;
			}
		} else {
			$finalMarkName = '';
		}

	// OK
		$status = $emajdb->alterGroup($_POST['group'],$finalMarkName);
		if ($status == 0) {
			$_reload_browser = true;
			if ($_POST['back'] == 'list') {
				$this->show_groups(sprintf($lang['emajaltergroupok'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group(sprintf($lang['emajaltergroupok'], htmlspecialchars($_POST['group'])));
			}
		}else
			if ($_POST['back'] == 'list') {
				$this->show_groups('',sprintf($lang['emajaltergrouperr'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajaltergrouperr'], htmlspecialchars($_POST['group'])));
			}
	}

	/**
	 * Prepare alter groups: ask for confirmation
	 */
	function alter_groups() {
		global $misc, $lang, $emajdb;

		if (!isset($_REQUEST['ma'])) {
			$this->show_groups('',$lang['emajnoselectedgroup']);
			return;
		}

		$this->printPageHeader('emaj','emajgroups');
		$misc->printTitle($lang['emajaltergroups']);

		// build the groups list and the global state of this list 
		$groupsList = ''; $anyGroupLogging = 0;
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList .= $a['group'].', ';
			if ($emajdb->isGroupLogging($a['group'])) {
				$anyGroupLogging = 1;
			}
		}
		$groupsList = substr($groupsList,0,strlen($groupsList)-2);

		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";

		if ($emajdb->getNumEmajVersion() >= 20100) {			// version >= 2.1.0
			if ($anyGroupLogging) {
				echo "<p>", sprintf($lang['emajalterallloggingroups'], htmlspecialchars($groupsList)), "</p>";
				echo "<table>\n";
				echo "<tr><th class=\"data left\">{$lang['emajmark']}</th>\n";
				echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"ALTER_%\" id=\"mark\">\n";
				echo "<img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajmarknamemultihelp']}\"/></td></tr>";
				echo "</table>\n";
			} else {
				echo "<p>", sprintf($lang['emajconfirmaltergroups'], htmlspecialchars($groupsList)), "</p>\n";
				echo "<input type=\"hidden\" name=\"mark\" value=\"\">";
			}
		} else {
			echo "<p>", sprintf($lang['emajconfirmaltergroups'], htmlspecialchars($groupsList)), "</p>\n";
			echo "<input type=\"hidden\" name=\"mark\" value=\"\">";
		}

		echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"altergroups\" value=\"{$lang['emajApplyConfChanges']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform alter groups
	 */
	function alter_groups_ok() {
		global $lang, $emajdb, $_reload_browser;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_groups(); exit(); }

	// check the groups can be altered by looking at their state and operations that will be performed
		$groups = explode(', ',$_POST['groups']);
		foreach($groups as $g) {
			$check = $emajdb->checkAlterGroup($g);
			// exit the loop in case of error
			if ($check == 0) {
				if ($_POST['back'] == 'list') {
					$this->show_groups('',sprintf($lang['emajcantaltergroup'], htmlspecialchars($g)));
				} else {
					$this->show_group('',sprintf($lang['emajcantaltergroup'], htmlspecialchars($g)));
				}
				exit();
			}
		}
	// Check the supplied mark is valid for the groups
		if ($_POST['mark'] != '') {
			$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['groups'],$_POST['mark']);
			if (is_null($finalMarkName)) {
				$this->show_groups('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
				return;
			}
		} else {
			$finalMarkName = '';
		}

	// OK
		$status = $emajdb->alterGroups($_POST['groups'],$finalMarkName);
		if ($status == 0) {
			$_reload_browser = true;
			if ($_POST['back'] == 'list') {
				$this->show_groups(sprintf($lang['emajaltergroupsok'], htmlspecialchars($_POST['groups'])));
			} else {
				$this->show_group(sprintf($lang['emajaltergroupsok'], htmlspecialchars($_POST['groups'])));
			}
		}else
			if ($_POST['back'] == 'list') {
				$this->show_groups('',sprintf($lang['emajaltergroupserr'], htmlspecialchars($_POST['groups'])));
			} else {
				$this->show_group('',sprintf($lang['emajaltergroupserr'], htmlspecialchars($_POST['groups'])));
			}
	}

	/**
	 * Prepare comment group: ask for comment and confirmation
	 */
	function comment_group() {
		global $misc, $lang, $emajdb;

		if ($_REQUEST['back']=='list')
			$this->printPageHeader('emaj','emajgroups');
		else
			$this->printPageHeader('emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajcommentagroup']);

		$group = $emajdb->getGroup($_REQUEST['group']);

		echo "<p>", sprintf($lang['emajcommentgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
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
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				$this->show_groups();
			} else {
				$this->show_group();
			}
			exit();
		}

		$status = $emajdb->setCommentGroup($_POST['group'],$_POST['comment']);
		if ($status == 0)
			if ($_POST['back']=='list') {
				$this->show_groups(sprintf($lang['emajcommentgroupok'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group(sprintf($lang['emajcommentgroupok'], htmlspecialchars($_POST['group'])));
			}
		else
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajcommentgrouperr'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajcommentgrouperr'], htmlspecialchars($_POST['group'])));
			}
	}

	/**
	 * Prepare start group: enter the initial mark name and confirm
	 */
	function start_group() {
		global $misc, $lang;

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		if ($_REQUEST['back']=='list')
			$this->printPageHeader('emaj','emajgroups');
		else
			$this->printPageHeader('emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajstartagroup']);

		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p>", sprintf($lang['emajconfirmstartgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left required\" style=\"width: 100px\">{$lang['emajinitmark']}</th>\n";
		echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"",
			htmlspecialchars($_POST['mark']), "\" id=\"mark\" />\n";
		echo "<img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></td></tr>";
		echo "</table>\n";
		echo "<p><input type=checkbox name=\"resetlog\" checked/>{$lang['emajoldlogsdeletion']}</p>\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"start_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strstart']}\" id=\"ok\" disabled=\"disabled\" />\n";
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
		global $lang, $emajdb;

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
		$group = $emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'IDLE') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajcantstartgroup'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajcantstartgroup'], htmlspecialchars($_POST['group'])));
			}
			return;
		}
		// check the supplied mark is valid for the group
		$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['group'],$_POST['mark']);
		if (is_null($finalMarkName)) {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
			} else {
				$this->show_group('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
			}
			return;
		}
		// OK
		$status = $emajdb->startGroup($_POST['group'],$finalMarkName,isSet($_POST['resetlog']));
		if ($status == 0)
			if ($_POST['back']=='list') {
				$this->show_groups(sprintf($lang['emajstartgroupok'], htmlspecialchars($_POST['group']), htmlspecialchars($finalMarkName)));
			} else {
				$this->show_group(sprintf($lang['emajstartgroupok'], htmlspecialchars($_POST['group']), htmlspecialchars($finalMarkName)));
			}
		else
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajstartgrouperr'], htmlspecialchars($finalMarkName)));
			} else {
				$this->show_group('',sprintf($lang['emajstartgrouperr'], htmlspecialchars($finalMarkName)));
			}
	}

	/**
	 * Prepare start groups: enter the initial mark name and confirm
	 */
	function start_groups() {
		global $misc, $lang;

		if (!isset($_REQUEST['ma'])) {
			$this->show_groups('',$lang['emajnoselectedgroup']);
			return;
		}
		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		$this->printPageHeader('emaj','emajgroups');

		$misc->printTitle($lang['emajstartgroups']);

		// build the groups list
		$groupsList='';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList.=$a['group'].', ';
		}
		$groupsList=substr($groupsList,0,strlen($groupsList)-2);
		// send form
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p>", sprintf($lang['emajconfirmstartgroups'], htmlspecialchars($groupsList)), "</p>\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left required\" style=\"width: 100px\">{$lang['emajinitmark']}</th>\n";
		echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"",
			htmlspecialchars($_POST['mark']), "\" id=\"mark\" />\n";
		echo "<img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajmarknamemultihelp']}\"/></td></tr>";
		echo "</table>\n";
		echo "<p><input type=checkbox name=\"resetlog\" checked/>{$lang['emajoldlogsdeletion']}</p>\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"start_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strstart']}\" id=\"ok\" disabled=\"disabled\" />\n";
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
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_groups(); exit(); }

		// Check the groups are always in IDLE state
		$groups=explode(', ',$_POST['groups']);
		foreach($groups as $g) {
			if ($emajdb->getGroup($g)->fields['group_state'] != 'IDLE') {
				$this->show_groups('',sprintf($lang['emajcantstartgroups'], htmlspecialchars($_POST['groups']), htmlspecialchars($g)));
				return;
			}
		}
		// check the supplied mark is valid for the groups
		$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['groups'],$_POST['mark']);
		if (is_null($finalMarkName)) {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
			} else {
				$this->show_group('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
			}
			return;
		}
		// OK
		$status = $emajdb->startGroups($_POST['groups'],$finalMarkName,isSet($_POST['resetlog']));
		if ($status == 0)
			if ($_POST['back']=='list')
				$this->show_groups(sprintf($lang['emajstartgroupsok'], htmlspecialchars($_POST['groups']), htmlspecialchars($finalMarkName)));
		else
			if ($_POST['back']=='list')
				$this->show_groups('',sprintf($lang['emajstartgroupserr'], htmlspecialchars($finalMarkName)));
	}

	/**
	 * Prepare stop group: ask for confirmation
	 */
	function stop_group() {
		global $misc, $lang;

		if ($_REQUEST['back']=='list')
			$this->printPageHeader('emaj','emajgroups');
		else
			$this->printPageHeader('emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajstopagroup']);

		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p>", sprintf($lang['emajconfirmstopgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left\" style=\"width: 100px\">{$lang['emajstopmark']}</th>\n";
		echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"STOP_%\" />\n";
		echo "<img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></td></tr>";
		echo "</table>\n";
		echo "<p><input type=checkbox name=\"forcestop\" />{$lang['emajforcestop']}</p>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"stop_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"stopgroup\" value=\"{$lang['strstop']}\"/>\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform stop_group
	 */
	function stop_group_ok() {
		global $lang, $emajdb;

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
		$group = $emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajcantstopgroup'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajcantstopgroup'], htmlspecialchars($_POST['group'])));
			}
			return;
		}
		// OK
		$status = $emajdb->stopGroup($_POST['group'],$_POST['mark'],isSet($_POST['forcestop']));
		if ($status == 0)
			if ($_POST['back']=='list') {
				$this->show_groups(sprintf($lang['emajstopgroupok'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group(sprintf($lang['emajstopgroupok'], htmlspecialchars($_POST['group'])));
			}
		else
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajstopgrouperr'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajstopgrouperr'], htmlspecialchars($_POST['group'])));
			}
	}

	/**
	 * Prepare stop group: ask for confirmation
	 */
	function stop_groups() {
		global $misc, $lang;

		if (!isset($_REQUEST['ma'])) {
			$this->show_groups('',$lang['emajnoselectedgroup']);
			return;
		}

		$this->printPageHeader('emaj','emajgroups');

		$misc->printTitle($lang['emajstopgroups']);

		// build the groups list
		$groupsList='';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList.=$a['group'].', ';
		}
		$groupsList=substr($groupsList,0,strlen($groupsList)-2);
		// send form
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p>", sprintf($lang['emajconfirmstopgroups'], htmlspecialchars($groupsList)), "</p>\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left\" style=\"width: 100px\">{$lang['emajstopmark']}</th>\n";
		echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"STOP_%\" />\n";
		echo "<img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajmarknamemultihelp']}\"/></td></tr>";
		echo "</table>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"stop_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"stopgroups\" value=\"{$lang['strstop']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform stop_groups
	 */
	function stop_groups_ok() {
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_groups(); exit(); }

		// Check the groups are always in LOGGING state
		$groups=explode(', ',$_POST['groups']);
		foreach($groups as $g) {
			if ($emajdb->getGroup($g)->fields['group_state'] != 'LOGGING') {
				$this->show_groups('',sprintf($lang['emajcantstopgroups'], htmlspecialchars($_POST['groups']),$g));
				return;
			}
		}
		// OK
		$status = $emajdb->stopGroups($_POST['groups'],$_POST['mark']);
		if ($status == 0)
			$this->show_groups(sprintf($lang['emajstopgroupsok'], htmlspecialchars($_POST['groups'])));
		else
			$this->show_groups('',sprintf($lang['emajstopgroupserr'], htmlspecialchars($_POST['groups'])));
	}

	/**
	 * Prepare reset group: ask for confirmation
	 */
	function reset_group() {
		global $misc, $lang;

		if ($_REQUEST['back']=='list')
			$this->printPageHeader('emaj','emajgroups');
		else
			$this->printPageHeader('emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajresetagroup']);

		echo "<p>", sprintf($lang['emajconfirmresetgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"reset_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"resetgroup\" value=\"{$lang['strreset']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform reset group
	 */
	function reset_group_ok() {
		global $lang, $emajdb;

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
		$group = $emajdb->getGroup($_POST['group']);
		if ($group->fields['group_state'] != 'IDLE') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajcantresetgroup'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajcantresetgroup'], htmlspecialchars($_POST['group'])));
			}
			return;
		}
		// OK
		$status = $emajdb->resetGroup($_POST['group']);
		if ($status == 0)
			if ($_POST['back']=='list') {
				$this->show_groups(sprintf($lang['emajresetgroupok'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group(sprintf($lang['emajresetgroupok'], htmlspecialchars($_POST['group'])));
			}
		else
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajresetgrouperr'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajresetgrouperr'], htmlspecialchars($_POST['group'])));
			}
	}

	/**
	 * Execute protect group (there is no confirmation to ask)
	 */
	function protect_group() {
		global $lang, $emajdb;

		// Check the group is always in LOGGING state
		$group = $emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_REQUEST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajcantprotectgroup'], htmlspecialchars($_REQUEST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajcantprotectgroup'], htmlspecialchars($_REQUEST['group'])));
			}
			return;
		}
		// OK
		$status = $emajdb->protectGroup($_REQUEST['group']);
		if ($status == 0)
			if ($_REQUEST['back']=='list') {
				$this->show_groups(sprintf($lang['emajprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
			} else {
				$this->show_group(sprintf($lang['emajprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
			}
		else
			if ($_REQUEST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajprotectgrouperr'], htmlspecialchars($_REQUEST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajprotectgrouperr'], htmlspecialchars($_REQUEST['group'])));
			}
	}

	/**
	 * Execute unprotect group (there is no confirmation to ask)
	 */
	function unprotect_group() {
		global $lang, $emajdb;

		// Check the group is always in LOGGING state
		$group = $emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_REQUEST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajcantunprotectgroup'], htmlspecialchars($_REQUEST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajcantunprotectgroup'], htmlspecialchars($_REQUEST['group'])));
			}
			return;
		}
		// OK
		$status = $emajdb->unprotectGroup($_REQUEST['group']);
		if ($status == 0)
			if ($_REQUEST['back']=='list') {
				$this->show_groups(sprintf($lang['emajunprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
			} else {
				$this->show_group(sprintf($lang['emajunprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
			}
		else
			if ($_REQUEST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajunprotectgrouperr'], htmlspecialchars($_REQUEST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajunprotectgrouperr'], htmlspecialchars($_REQUEST['group'])));
			}
	}

	/**
	 * Prepare set mark group: ask for the mark name and confirmation
	 */
	function set_mark_group() {
		global $misc, $lang;

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		if ($_REQUEST['back']=='list')
			$this->printPageHeader('emaj','emajgroups');
		else
			$this->printPageHeader('emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajsetamark']);
		echo "<p>", sprintf($lang['emajconfirmsetmarkgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left required\">{$lang['emajmark']}</th>\n";
		echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"",
			htmlspecialchars($_POST['mark']), "\" id=\"mark\"/>\n";
		echo "<img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></td></tr>";
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
		global $lang, $emajdb;

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
		$group = $emajdb->getGroup($_POST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajcantsetmarkgroup'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajcantsetmarkgroup'], htmlspecialchars($_POST['group'])));
			}
			return;
		}
		// Check the supplied mark group is valid
		$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['group'],$_POST['mark']);
		if (is_null($finalMarkName)) {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
			} else {
				$this->show_group('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
			}
			return;
		}
		// OK
		$status = $emajdb->setMarkGroup($_POST['group'],$finalMarkName);
		if ($status == 0)
			if ($_POST['back']=='list') {
				$this->show_groups(sprintf($lang['emajsetmarkgroupok'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group(sprintf($lang['emajsetmarkgroupok'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['group'])));
			}
		else
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajsetmarkgrouperr'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajsetmarkgrouperr'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['group'])));
			}
	}

	/**
	 * Prepare set mark groups: ask for the mark name and confirmation
	 */
	function set_mark_groups() {
		global $misc, $lang;

		if (!isset($_REQUEST['ma'])) {
			$this->show_groups('',$lang['emajnoselectedgroup']);
			return;
		}
		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		$this->printPageHeader('emaj','emajgroups');

		$misc->printTitle($lang['emajsetamark']);

		// build the groups list
		$groupsList='';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList.=$a['group'].', ';
		}
		$groupsList=substr($groupsList,0,strlen($groupsList)-2);
		echo "<p>", sprintf($lang['emajconfirmsetmarkgroup'], htmlspecialchars($groupsList)), "</p>\n";
		// send form
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left required\">{$lang['emajmark']}</th>\n";
		echo "<td class=\"data1\"><input name=\"mark\" size=\"32\" value=\"",
			htmlspecialchars($_POST['mark']), "\" id=\"mark\"/>\n";
		echo "<img src=\"{$misc->icon(array($this->name,'Info'))}\" alt=\"info\" title=\"{$lang['emajmarknamemultihelp']}\"/></td></tr>";
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
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_groups(); exit(); }

		// Check the groups are always in LOGGING state
		$groups=explode(', ',$_POST['groups']);
		foreach($groups as $g) {
			if ($emajdb->getGroup($g)->fields['group_state'] != 'LOGGING') {
				$this->show_groups('',(sprintf($lang['emajcantsetmarkgroups'], htmlspecialchars($_POST['groups']),$g)));
				return;
			}
		}
		// Check the supplied mark group is valid
		$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['groups'],$_POST['mark']);
		if (is_null($finalMarkName)) {
			$this->show_groups('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
			return;
		}
		// OK
		$status = $emajdb->setMarkGroups($_POST['groups'],$finalMarkName);
		if ($status == 0)
			$this->show_groups(sprintf($lang['emajsetmarkgroupok'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['groups'])));
		else
			$this->show_groups('',sprintf($lang['emajsetmarkgrouperr'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['groups'])));
	}

	/**
	 * Execute protect mark (there is no confirmation to ask)
	 */
	function protect_mark_group() {
		global $lang, $emajdb;

		// Check the group is always in LOGGING state
		$group = $emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_REQUEST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajcantprotectmarkgroup'], htmlspecialchars($_REQUEST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajcantprotectmarkgroup'], htmlspecialchars($_REQUEST['group'])));
			}
			return;
		}
		// OK
		$status = $emajdb->protectMarkGroup($_REQUEST['group'],$_REQUEST['mark']);
		if ($status == 0)
			if ($_REQUEST['back']=='list') {
				$this->show_groups(sprintf($lang['emajprotectmarkgroupok'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			} else {
				$this->show_group(sprintf($lang['emajprotectmarkgroupok'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			}
		else
			if ($_REQUEST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajprotectmarkgrouperr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajprotectmarkgrouperr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			}
	}

	/**
	 * Execute unprotect mark (there is no confirmation to ask)
	 */
	function unprotect_mark_group() {
		global $lang, $emajdb;

		// Check the group is always in LOGGING state
		$group = $emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_REQUEST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajcantunprotectmarkgroup'], htmlspecialchars($_REQUEST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajcantunprotectmarkgroup'], htmlspecialchars($_REQUEST['group'])));
			}
			return;
		}
		// OK
		$status = $emajdb->unprotectMarkGroup($_REQUEST['group'],$_REQUEST['mark']);
		if ($status == 0)
			if ($_REQUEST['back']=='list') {
				$this->show_groups(sprintf($lang['emajunprotectmarkgroupok'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			} else {
				$this->show_group(sprintf($lang['emajunprotectmarkgroupok'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			}
		else
			if ($_REQUEST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajunprotectmarkgrouperr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajunprotectmarkgrouperr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			}
	}

	/**
	 * Prepare comment mark group: ask for comment and confirmation
	 */
	function comment_mark_group() {
		global $misc, $lang, $emajdb;

		$this->printPageHeader('emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajcommentamark']);

		$mark = $emajdb->getMark($_REQUEST['group'],$_REQUEST['mark']);

		echo "<p>", sprintf($lang['emajcommentmark'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
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
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_group(); exit(); }

		$status = $emajdb->setCommentMarkGroup($_POST['group'],$_POST['mark'],$_POST['comment']);
		if ($status >= 0)
			$this->show_group(sprintf($lang['emajcommentmarkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
		else
			$this->show_group('',sprintf($lang['emajcommentmarkerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare rollback group: ask for confirmation
	 */
	function rollback_group() {
		global $misc, $lang, $emajdb, $conf;

		if ($_REQUEST['back']=='list')
			$this->printPageHeader('emaj','emajgroups');
		else
			$this->printPageHeader('emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajrlbkagroup']);

		echo "<style type=\"text/css\">[disabled]{color:#933;}</style>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_group_confirm_alter\" />\n";

		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		if (isset($_REQUEST['mark'])) {
		// the mark name is already defined (we are coming from the 'detail group' page)
			echo "<p>", sprintf($lang['emajconfirmrlbkgroup'], htmlspecialchars($_REQUEST['group']), htmlspecialchars($_REQUEST['mark'])), "</p>\n";
			echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		} else {
		// the mark name is not yet defined (we are coming from the 'list groups' page)
			$marks=$emajdb->getRollbackMarkGroup($_REQUEST['group']);
			echo sprintf($lang['emajselectmarkgroup'], htmlspecialchars($_REQUEST['group']));
			echo "<select name=\"mark\">\n";
			$optionDisabled = '';
			foreach($marks as $m) {
				echo "<option value=\"", htmlspecialchars($m['mark_name']), "\" $optionDisabled>", htmlspecialchars($m['mark_name']), " ({$m['mark_datetime']})</option>\n";
				// if the mark is protected against rollback, disabled the next ones
				if ($m['mark_is_rlbk_protected'] == 't') $optionDisabled = 'disabled';
			}
			echo "</select></p><p>\n";
		}
		echo $misc->form;
		echo "{$lang['emajrollbacktype']} : ";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"unlogged\" checked>{$lang['emajunlogged']}";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"logged\">{$lang['emajlogged']}\n";
		echo "</p><p>";
		echo "<input type=\"submit\" name=\"rollbackgroup\" value=\"{$lang['emajrlbk']}\" />\n";
		if ($emajdb->isAsyncRlbkUsable($conf) ) {
			echo "<input type=\"submit\" name=\"async\" value=\"{$lang['emajrlbkthenmonitor']}\" />\n";
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
		global $lang, $misc, $emajdb;

		if ($emajdb->getNumEmajVersion() < 20100) {	// version < 2.1.0) {
			// for emaj version prior 2.1, directly call the function that executes the rollback
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
			$group = $emajdb->getGroup($_POST['group']);
			if ($group->fields['group_state'] != 'LOGGING') {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($lang['emajcantrlbkidlegroup'], htmlspecialchars($_POST['group'])));
				} else {
					$this->show_group('',sprintf($lang['emajcantrlbkidlegroup'], htmlspecialchars($_POST['group'])));
				}
				return;
			}
			if ($group->fields['group_type'] != 'ROLLBACKABLE') {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($lang['emajcantrlbkprotgroup'], htmlspecialchars($_POST['group'])));
				} else {
					$this->show_group('',sprintf($lang['emajcantrlbkprotgroup'], htmlspecialchars($_POST['group'])));
				}
				return;
			}
			// Check the mark is always valid for a rollback
			if (!$emajdb->isRollbackMarkValidGroup($_POST['group'],$_POST['mark'])) {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($lang['emajcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
				} else {
					$this->show_group('',sprintf($lang['emajcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
				}
				return;
			}
	
			$alterGroupSteps = $emajdb->getAlterAfterMarkGroups($_POST['group'],$_POST['mark'],$lang);

			if ($alterGroupSteps->recordCount() > 0) {
				// there are alter_group operation to cross over, so ask for a confirmation

				$columns = array(
					'time' => array(
						'title' => $lang['emajtimestamp'],
						'field' => field('time_tx_timestamp'),
					),
					'step' => array(
						'title' => $lang['straction'],
						'field' => field('altr_action'),
					),
					'autorollback' => array(
						'title' => $lang['emajautorolledback'],
						'field' => field('altr_auto_rolled_back'),
						'type'	=> 'callback',
						'params'=> array('function' => array($this, 'renderBooleanIcon'),'align' => 'center')
					),
				);
	
				$actions = array ();
	
				if ($_REQUEST['back']=='list')
					$this->printPageHeader('emaj','emajgroups');
				else
					$this->printPageHeader('emajgroup','emajgroupproperties');

				$misc->printTitle($lang['emajrlbkagroup']);

				echo "<p>" . sprintf($lang['emajreachaltergroup'], htmlspecialchars($_REQUEST['group']), htmlspecialchars($_REQUEST['mark'])) . "</p>\n";

				echo "<div id=\"alterGroupStep\" style=\"margin-top:15px;margin-bottom:15px\" >\n";
				$misc->printTable($alterGroupSteps, $columns, $actions, 'alterGroupStep');
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
		global $lang, $misc, $emajdb, $conf;

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
		$group = $emajdb->getGroup($_POST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajcantrlbkidlegroup'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajcantrlbkidlegroup'], htmlspecialchars($_POST['group'])));
			}
			return;
		}
		if ($group->fields['group_type'] != 'ROLLBACKABLE') {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajcantrlbkprotgroup'], htmlspecialchars($_POST['group'])));
			} else {
				$this->show_group('',sprintf($lang['emajcantrlbkprotgroup'], htmlspecialchars($_POST['group'])));
			}
			return;
		}
		// Check the mark is always valid for a rollback
		if (!$emajdb->isRollbackMarkValidGroup($_POST['group'],$_POST['mark'])) {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
			} else {
				$this->show_group('',sprintf($lang['emajcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
			}
			return;
		}

		if (isset($_POST['async'])) {
		// perform the rollback in asynchronous mode and switch to the rollback monitoring page

			$psqlExe = $misc->escapeShellCmd($conf['psql_path']);

			// re-check the psql exe path and the temp directory supplied in the config file
			$version = array();
			preg_match("/(\d+(?:\.\d+)?)(?:\.\d+)?.*$/", exec($psqlExe . " --version"), $version);
			if (empty($version)) {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($lang['emajbadpsqlpath'], $conf['psql_path']));
				} else {
					$this->show_group('',sprintf($lang['emajbadpsqlpath'], $conf['psql_path']));
				}
				exit;
			}

			// re-check the file can be written into the temp directory supplied in the config file 
			$sep = (substr(php_uname(), 0, 3) == "Win") ? '\\' : '/';
			$testFileName = $conf['temp_dir'] . $sep . 'rlbk_report_test';
			$f = fopen($testFileName,'w');
			if (!$f) {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($lang['emajbadtempdir'], $conf['temp_dir']));
				} else {
					$this->show_group('',sprintf($lang['emajbadtempdir'], $conf['temp_dir']));
				}
				exit;
			} else {
				fclose($f);
				unlink($testFileName);
			}

			$rlbkId = $emajdb->asyncRollbackGroups($_POST['group'],$_POST['mark'],$_POST['rollbacktype']=='logged', $psqlExe, $conf['temp_dir'].$sep, false);
			$this->show_rollbacks(sprintf($lang['emajasyncrlbkstarted'],$rlbkId));
			exit;
		}

		// perform the rollback in regular synchronous mode

		if (!ini_get('safe_mode')) set_time_limit(0);		// Prevent timeouts on large rollbacks (non-safe mode only)

		if (isset($_POST['rollbacktype'])) {
			$status = $emajdb->rollbackGroup($_POST['group'],$_POST['mark'],$_POST['rollbacktype']=='logged');
		} else {
			$status = $emajdb->rollbackGroup($_POST['group'],$_POST['mark'],false);
		}
		if ($status == 0) {
			if ($_POST['back']=='list') {
				$this->show_groups(sprintf($lang['emajrlbkgroupok'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
			} else {
				$this->show_group(sprintf($lang['emajrlbkgroupok'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
			}
		} else {
			if ($_POST['back']=='list') {
				$this->show_groups('',sprintf($lang['emajrlbkgrouperr'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
			} else {
				$this->show_group('',sprintf($lang['emajrlbkgrouperr'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
			}
		}
	}

	/**
	 * Prepare rollback groups: ask for confirmation
	 */
	function rollback_groups() {
		global $misc, $lang, $emajdb;

		if (!isset($_REQUEST['ma'])) {
		// function called but no selected group
			$this->show_groups('',$lang['emajnoselectedgroup']);
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
			$protectedGroups=$emajdb->getProtectedGroups($groupsList);
			if ($protectedGroups != '') {
				$this->show_groups('',sprintf($lang['emajcantrlbkprotgroups'], htmlspecialchars($groupsList), htmlspecialchars($protectedGroups)));
				return;
			}
		// look for marks common to all selected groups
			$marks=$emajdb->getRollbackMarkGroups($groupsList);
		// if no mark is usable for all selected groups, stop
			if ($marks->recordCount()==0) {
				$this->show_groups('',sprintf($lang['emajnomarkgroups'], htmlspecialchars($groupsList)));
				return;
			}
		// get the youngest timestamp protected mark for all groups
			$youngestProtectedMarkTimestamp=$emajdb->getYoungestProtectedMarkTimestamp($groupsList);
		}
		$this->printPageHeader('emaj','emajgroups');

		$misc->printTitle($lang['emajrlbkgroups']);

		echo "<style type=\"text/css\">[disabled]{color:#933;}</style>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_groups_confirm_alter\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo sprintf($lang['emajselectmarkgroups'], htmlspecialchars($groupsList));
		echo "<select name=\"mark\">\n";
		$optionDisabled = '';
		foreach($marks as $m) {
			// if the mark is older than the youngest protected against rollback, disabled it and the next ones
			if ($m['mark_datetime'] < $youngestProtectedMarkTimestamp) $optionDisabled = 'disabled';
			echo "<option value=\"",htmlspecialchars($m['mark_name']),"\" $optionDisabled>",htmlspecialchars($m['mark_name'])," (",htmlspecialchars($m['mark_datetime']),")</option>\n";
		}
		echo "</select></p><p>\n";
		echo $misc->form;
		echo "{$lang['emajrollbacktype']} : ";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"unlogged\" checked>{$lang['emajunlogged']}";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"logged\">{$lang['emajlogged']}\n";
		echo "</p><p>";
		echo "<input type=\"submit\" name=\"rollbackgroups\" value=\"{$lang['emajrlbk']}\" />\n";
		if ($emajdb->isAsyncRlbkUsable($this->conf) ) {
			echo "<input type=\"submit\" name=\"async\" value=\"{$lang['emajrlbkthenmonitor']}\" />\n";
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
		global $lang, $misc, $emajdb;

		if ($emajdb->getNumEmajVersion() < 20100) {	// version < 2.1.0) {
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
				if ($emajdb->getGroup($g)->fields['group_state'] != 'LOGGING') {
					$this->show_groups('',sprintf($lang['emajcantrlbkidlegroups'], htmlspecialchars($groups), htmlspecialchars($g)));
					return;
				}
			}
			// if at least one selected group is protected, stop
			$protectedGroups=$emajdb->getProtectedGroups($_POST['groups']);
			if ($protectedGroups != '') {
				$this->show_groups('',sprintf($lang['emajcantrlbkprotgroups'], htmlspecialchars($groups), htmlspecialchars($protectedGroups)));
				return;
			}

			// Check the mark is always valid
			if (!$emajdb->isRollbackMarkValidGroups($_POST['groups'],$_POST['mark'])) {
				$this->show_groups('',sprintf($lang['emajcantrlbkinvalidmarkgroups'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])));
				return;
			}

			$alterGroupSteps = $emajdb->getAlterAfterMarkGroups($_POST['groups'],$_POST['mark'],$lang);

			if ($alterGroupSteps->recordCount() > 0) {
				// there are alter_group operations to cross over, so ask for a confirmation

				$columns = array(
					'time' => array(
						'title' => $lang['emajtimestamp'],
						'field' => field('time_tx_timestamp'),
					),
					'step' => array(
						'title' => $lang['straction'],
						'field' => field('altr_action'),
					),
					'autorollback' => array(
						'title' => $lang['emajautorolledback'],
						'field' => field('altr_auto_rolled_back'),
						'type'	=> 'callback',
						'params'=> array('function' => array($this, 'renderBooleanIcon'),'align' => 'center')
					),
				);

				$actions = array ();

				$this->printPageHeader('emaj','emajgroups');

				$misc->printTitle($lang['emajrlbkgroups']);

				echo "<p>" . sprintf($lang['emajreachaltergroups'], htmlspecialchars($_REQUEST['groups']), htmlspecialchars($_REQUEST['mark'])) . "</p>\n";

				echo "<div id=\"alterGroupStep\" style=\"margin-top:15px;margin-bottom:15px\" >\n";
				$misc->printTable($alterGroupSteps, $columns, $actions, 'alterGroupStep');
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
		global $lang, $misc, $emajdb, $conf;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_groups(); exit(); }

		// Check the groups are always in LOGGING state and not protected
		$groups=explode(', ',$_POST['groups']);
		foreach($groups as $g) {
			if ($emajdb->getGroup($g)->fields['group_state'] != 'LOGGING') {
				$this->show_groups('',sprintf($lang['emajcantrlbkidlegroups'],htmlspecialchars($groups), htmlspecialchars($g)));
				return;
			}
		}
		$server_info = $misc->getServerInfo();
		if ($server_info["pgVersion"]>=8.4) {
		// if at least one selected group is protected, stop
			$protectedGroups=$emajdb->getProtectedGroups($_POST['groups']);
			if ($protectedGroups != '') {
				$this->show_groups('',sprintf($lang['emajcantrlbkprotgroups'], htmlspecialchars($groups), htmlspecialchars($protectedGroups)));
				return;
			}
		}

		// Check the mark is always valid
		if (!$emajdb->isRollbackMarkValidGroups($_POST['groups'],$_POST['mark'])) {
			$this->show_groups('',sprintf($lang['emajcantrlbkinvalidmarkgroups'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])));
			return;
		}
		// OK

		if (isset($_POST['async'])) {
		// perform the rollback in asynchronous mode and switch to the rollback monitoring page

			$psqlExe = $misc->escapeShellCmd($conf['psql_path']);

			// re-check the psql exe path and the temp directory supplied in the config file
			$version = array();
			preg_match("/(\d+(?:\.\d+)?)(?:\.\d+)?.*$/", exec($psqlExe . " --version"), $version);
			if (empty($version)) {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($lang['emajbadpsqlpath'], $conf['psql_path']));
				} else {
					$this->show_group('',sprintf($lang['emajbadpsqlpath'], $conf['psql_path']));
				}
				exit;
			}

			// re-check the file can be written into the temp directory supplied in the config file 
			$sep = (substr(php_uname(), 0, 3) == "Win") ? '\\' : '/';
			$testFileName = $conf['temp_dir'] . $sep . 'rlbk_report_test';
			$f = fopen($testFileName,'w');
			if (!$f) {
				if ($_POST['back']=='list') {
					$this->show_groups('',sprintf($lang['emajbadtempdir'], $conf['temp_dir']));
				} else {
					$this->show_group('',sprintf($lang['emajbadtempdir'], $conf['temp_dir']));
				}
				exit;
			} else {
				fclose($f);
				unlink($testFileName);
			}

			$rlbkId = $emajdb->asyncRollbackGroups($_POST['groups'],$_POST['mark'],$_POST['rollbacktype']=='logged', $psqlExe, $conf['temp_dir'].$sep, true);
			$this->show_rollbacks(sprintf($lang['emajasyncrlbkstarted'],$rlbkId));
			exit;
		}

		// perform the rollback in regular synchronous mode

		if (!ini_get('safe_mode')) set_time_limit(0);		// Prevent timeouts on large rollbacks (non-safe mode only)

		if (isset($_POST['rollbacktype'])) {
			$status = $emajdb->rollbackGroups($_POST['groups'],$_POST['mark'],$_POST['rollbacktype']=='logged');
		} else {
			$status = $emajdb->rollbackGroups($_POST['groups'],$_POST['mark'],false);
		}
		if ($status == 0) {
			$this->show_groups(sprintf($lang['emajrlbkgroupsok'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])));
		} else {
			$this->show_groups('',sprintf($lang['emajrlbkgroupserr'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])));
		}
	}

	/**
	 * Prepare a rollback consolidation: ask for confirmation
	 */
	function consolidate_rollback() {
		global $misc, $lang;

		$this->printPageHeader('emaj','emajmonitorrlbk');

		$misc->printTitle($lang['emajconsolidaterlbk']);

		echo "<p>", sprintf($lang['emajconfirmconsolidaterlbk'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"consolidate_rollback_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"consolidaterlbk\" value=\"{$lang['emajconsolidate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform a rollback consolidation
	 */
	function consolidate_rollback_ok() {
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			$this->show_rollbacks();
			exit();
		}

		$status = $emajdb->consolidateRollback($_POST['group'],$_POST['mark']);
		if ($status == 0)
			$this->show_rollbacks(sprintf($lang['emajconsolidaterlbkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
		else
			$this->show_rollbacks('',sprintf($lang['emajconsolidaterlbkerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));

	}

	/**
	 * Prepare rename_mark_group: ask for the new name for the mark to rename and confirmation
	 */
	function rename_mark_group() {
		global $misc, $lang;

		if (!isset($_POST['group'])) $_POST['group'] = '';
		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		$this->printPageHeader('emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajrenameamark']);

		echo "<p>", sprintf($lang['emajconfirmrenamemark'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left required\">{$lang['emajnewnamemark']}</th>\n";
		echo "<td class=\"data1\"><input name=\"newmark\" size=\"32\" value=\"",
			htmlspecialchars($_POST['mark']), "\" id=\"newmark\"/></td></tr>\n";
		echo "</table>\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"rename_mark_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['emajrename']}\" id=\"ok\" disabled=\"disabled\"/>\n";
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
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_group(); exit(); }

		// Check the supplied mark group is valid
		$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['group'],$_POST['newmark']);
		if (is_null($finalMarkName)) {
			$this->show_group('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['newmark'])));
		} else {
		// OK
			$status = $emajdb->renameMarkGroup($_POST['group'],$_POST['mark'], $finalMarkName);
			if ($status >= 0)
				$this->show_group(sprintf($lang['emajrenamemarkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group']), htmlspecialchars($finalMarkName)));
			else
				$this->show_group('',sprintf($lang['emajrenamemarkerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group']), htmlspecialchars($finalMarkName)));
		}
	}

	/**
	 * Prepare delete mark group: ask for confirmation
	 */
	function delete_mark() {
		global $misc, $lang;

		$this->printPageHeader('emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajdelamark']);

		echo "<p>", sprintf($lang['emajconfirmdelmark'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"delete_mark_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"deletemark\" value=\"{$lang['strdelete']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform delete mark group
	 */
	function delete_mark_ok() {
		global $lang, $emajdb, $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_group(); exit(); }

		$status = $emajdb->deleteMarkGroup($_POST['group'],$_POST['mark']);
		if ($status >= 0)
			$this->show_group(sprintf($lang['emajdelmarkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
		else
			$this->show_group('',sprintf($lang['emajdelmarkerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare delete mark group for several marks: ask for confirmation
	 */
	function delete_marks() {
		global $misc, $lang, $emajdb;

		$this->printPageHeader('emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajdelmarks']);

		// build the marks list
		$marksList=''; $nbMarks=0;
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$marksList.=$a['mark'].', ';
			$nbMarks++;
		}
		$marksList=substr($marksList,0,strlen($marksList)-2);

		// Chech that at least 1 mark will remain
		if ($nbMarks >= $emajdb->getNbMarks($_REQUEST['group'])) {
			$this->show_group('',sprintf($lang['emajdelmarkserr2'], htmlspecialchars($_POST['marks']), htmlspecialchars($_POST['group'])));
			return;
		}

		echo "<p>", sprintf($lang['emajconfirmdelmarks'], htmlspecialchars($marksList), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"delete_marks_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"marks\" value=\"", htmlspecialchars($marksList), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"deletemarks\" value=\"{$lang['strdelete']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform delete mark group for several marks
	 */
	function delete_marks_ok() {
		global $data, $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_group(); exit(); }

		$marks = explode(', ',$_POST['marks']);
		$status = $data->beginTransaction();
		if ($status == 0) {
			foreach($marks as $m) {
				$status = $emajdb->deleteMarkGroup($_POST['group'],$m);
				if ($status != 0) {
					$data->rollbackTransaction();
					$this->show_group('',sprintf($lang['emajdelmarkserr'], htmlspecialchars($_POST['marks']), htmlspecialchars($_POST['group'])));
					return;
				}
			}
		}
		if($data->endTransaction() == 0)
			$this->show_group(sprintf($lang['emajdelmarksok'], htmlspecialchars($_POST['marks']), htmlspecialchars($_POST['group'])));
		else
			$this->show_group('',sprintf($lang['emajdelmarkserr'], htmlspecialchars($_POST['marks']), htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare delete before mark group: ask for confirmation
	 */
	function delete_before_mark() {
		global $misc, $lang;

		$this->printPageHeader('emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajdelmarksprior']);

		echo "<p>", sprintf($lang['emajconfirmdelmarksprior'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"plugin.php?plugin={$this->name}&amp;\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"delete_before_mark_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"deletebeforemark\" value=\"{$lang['strdelete']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";

		$this->printEmajFooter();
		$misc->printFooter();
	}

	/**
	 * Perform delete before mark group
	 */
	function delete_before_mark_ok() {
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) { $this->show_group(); exit(); }

		$status = $emajdb->deleteBeforeMarkGroup($_POST['group'],$_POST['mark']);
		if ($status > 0)
			$this->show_group(sprintf($lang['emajdelmarkspriorok'],$status, htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
		else
			$this->show_group('',sprintf($lang['emajdelmarkspriorerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
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
		global $misc, $lang;

		$misc->printHeader($lang['emajplugin']);
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
		global $lang, $misc, $emajdb;
	// $urlvar is the piece of the url containing the variables needed to refresh the page

	// For all but the emaj_envir functions,
		if ($urlvar != 'action=emaj_envir') {
			// if Emaj is not usable for this database, only display a message
			if (!(isset($emajdb) && $emajdb->isEnabled() && $emajdb->isAccessible()
				  && $emajdb->getNumEmajVersion() >= $this->oldest_supported_emaj_version_num)) {
				echo "<div class=\"topbar\"><table style=\"width: 100%\"><tr><td><span class=\"platform\">";
				$link = "<a href=\"plugin.php?plugin={$this->name}&amp;action=emaj_envir&amp;{$misc->href}\">\"{$lang['emajenvir']}\"</a>";
				echo sprintf($lang['emajnotavail'], $link);
				echo "</span></td></tr></table></div>";
				return 0;
			}
		}

		// generate the E-Maj header
		$currTime = date('H:i:s');
		if (isset($emajdb) && $emajdb->isEnabled() && $emajdb->isAccessible()
			&& $emajdb->getNumEmajVersion() >= $this->oldest_supported_emaj_version_num) {
			$emajVersion = "E-Maj&nbsp;{$emajdb->getEmajVersion()}&nbsp;&nbsp;-&nbsp;&nbsp;";
		} else {
			$emajVersion = '';
		}
		echo "<div class=\"topbar\"><table style=\"width: 100%\"><tr>\n";
		echo "<td style=\"width:15px\"><a href=\"plugin.php?plugin={$this->name}&amp;{$urlvar}&amp;{$misc->href}\"><img src=\"{$misc->icon('Refresh')}\" alt=\"{$lang['strrefresh']}\" title=\"{$lang['strrefresh']}\" /></a></td>\n";
		echo "<td><span class=\"platform\">{$currTime}&nbsp;&nbsp;";
		echo "{$emajVersion}{$title}</span></td>\n";
		echo "<td style=\"width:15px\"><a href=\"#bottom\"><img src=\"{$misc->icon(array($this->name,'Bottom'))}\" alt=\"{$lang['emajpagebottom']}\" title=\"{$lang['emajpagebottom']}\" /></a></td>\n";
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
		global $misc, $emajdb;

		$reqvars = $misc->getRequestVars('emaj');

		$groups = $emajdb->getGroups();

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
}
?>
