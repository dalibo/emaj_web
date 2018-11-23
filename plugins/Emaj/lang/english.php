<?php

	/**
	 * english language file for E-maj extension of phpPgAdmin.
	 */

	// Basic strings 
	$plugin_lang['emajplugin'] = 'E-Maj plugin';
	$plugin_lang['emajnotavail'] = 'Sorry, E-Maj is not available or accessible for this database. More details in the %s tab.';
	$plugin_lang['emajstate'] = 'State';
	$plugin_lang['emajnoselectedgroup'] = 'No tables group has been selected!';
	$plugin_lang['emajgroup'] = 'Group';
	$plugin_lang['emajgroups'] = 'Groups';
	$plugin_lang['emajmark'] = 'Mark';
	$plugin_lang['emajmarks'] = 'Marks';
	$plugin_lang['emajgrouptype'] = 'Group type';
	$plugin_lang['emajrollbacktype'] = 'Rollback type';
	$plugin_lang['emajauditonly'] = 'AUDIT-ONLY';
	$plugin_lang['emajrollbackable'] = 'ROLLBACK-ABLE';
	$plugin_lang['emajunlogged'] = 'unlogged';
	$plugin_lang['emajlogged'] = 'logged';
	$plugin_lang['emajlogging'] = 'Logging';
	$plugin_lang['emajidle'] = 'Idle';
	$plugin_lang['emajactive'] = 'Active';
	$plugin_lang['emajdeleted'] = 'Deleted';
	$plugin_lang['emajprotected'] = 'Protected against rollback';
	$plugin_lang['emajpagebottom'] = 'Go to bottom';
	$plugin_lang['emajselect'] = 'Select';
	$plugin_lang['emajactionsonselectedobjects'] = 'Actions on selected objects (0)';
	$plugin_lang['emajlogsize'] = 'Log size';
	$plugin_lang['emajrequiredfield'] = 'Required field';
	$plugin_lang['emajestimates'] = 'Estimates';
	$plugin_lang['emajfrom'] = 'From';
	$plugin_lang['emajto'] = 'To';
	$plugin_lang['emajall'] = 'All';
	$plugin_lang['emajnone'] = 'None';
	$plugin_lang['emajinvert'] = 'Invert';

	// E-Maj tabs
	$plugin_lang['emajenvir'] = 'E-Maj environment';
	$plugin_lang['emajgroupsconf'] = 'Groups configuration';
	$plugin_lang['emajrlbkop'] = 'Rollback operations';
	$plugin_lang['emajlogstat'] = 'Log statistics';

	// Common help messages
	$plugin_lang['emajmarknamehelp'] = 'The mark name must be unique within the group. A % character represents the current time (format hh.mn.ss.ms).';
	$plugin_lang['emajmarknamemultihelp'] = 'The mark name must be unique within the concerned groups. A % character represents the current time (format hh.mn.ss.ms).';

	// E-Maj environment
	$plugin_lang['emajenvironment'] = 'E-Maj environment';
	$plugin_lang['emajextnotavailable'] = 'The E-Maj software is not installed on this PostgreSQL instance. Contact your database administrator.';
	$plugin_lang['emajextnotcreated'] = 'The emaj extension is not created in this database. Contact your database administrator.';
	$plugin_lang['emajnogrant'] = 'Your connection role has no E-Maj rights. Use another role or contact your database administrator.';
	$plugin_lang['emajcharacteristics'] = 'Characteristics';
	$plugin_lang['emajversions'] = 'Versions';
	$plugin_lang['emajversion'] = 'E-Maj version: ';
	$plugin_lang['emajasextension'] = 'installed as extension';
	$plugin_lang['emajasscript'] = 'installed by script';
	$plugin_lang['emajtooold'] = 'Sorry, this E-Maj version (%s) is too old. The minimum version supported by this plugin is %s.';
	$plugin_lang['emajversionmorerecent'] = 'A more recent E-Maj version exists. Contact your database administrator.';
	$plugin_lang['emajwebversionmorerecent'] = 'A more recent Emaj_web version probably exists. Contact your database administrator.';
	$plugin_lang['emajdiskspace'] = 'Disk space used by the E-Maj environment: %s of the current database.';
	$plugin_lang['emajchecking'] = 'E-Maj environment consistency';
	$plugin_lang['emajdiagnostics'] = 'Diagnostics';

	// Groups' content setup
	$plugin_lang['emajgroupsconfiguration'] = 'Tables groups\' configuration';
	$plugin_lang['emajschemaslist'] = 'Application schemas list';
	$plugin_lang['emajunknownobject'] = 'This object is referenced in the emaj_group_def table but is not created.';
	$plugin_lang['emajunsupportedobject'] = 'This object type is not supported by E-Maj (unlogged table, table with OIDS, partition table,...).';
	$plugin_lang['emajtblseqofschema'] = 'Tables and sequences in schema "%s"';
	$plugin_lang['emajassign'] = 'Assign';
	$plugin_lang['emajremove'] = 'Remove';
	$plugin_lang['emajlogschemasuffix'] = 'Log schema suffix';
	$plugin_lang['emajlogdattsp'] = 'Log tablespace';
	$plugin_lang['emajlogidxtsp'] = 'Log index tablespace';
	$plugin_lang['emajspecifytblseqtoassign'] = 'Specify at least one table or sequence to assign';
	$plugin_lang['emajtblseqyetgroup'] = 'Error, "%s.%s" is already assigned to a tables group.';
	$plugin_lang['emajtblseqbadtype'] = 'Error, type of "%s.%s" is not supported by E-Maj.';
	$plugin_lang['emajassigntblseq'] = 'E-Maj: Assign tables / sequences to a tables group';
	$plugin_lang['emajconfirmassigntblseq'] = 'Assign :';
	$plugin_lang['emajthetable'] = 'the "%s.%s" table';
	$plugin_lang['emajthesequence'] = 'the "%s.%s" sequence';
	$plugin_lang['emajfromgroup'] = 'from the group "%s"';
	$plugin_lang['emajenterpriority'] = 'Processing priority';
	$plugin_lang['emajpriorityhelp'] = 'Tables and sequences are processed in priority ascending order, and in names alphabetic order if no priority is defined.';
	$plugin_lang['emajenterlogschema'] = 'Log schema suffix';
	$plugin_lang['emajlogschemahelp'] = 'A log schema contains log tables, sequences and functions. The default log schema is \'emaj\'. If a suffix is defined for the table, its objects will be hosted in the schema \'emaj\' + suffix.';
	$plugin_lang['emajenternameprefix'] = 'E-Maj objects name prefix';
	$plugin_lang['emajnameprefixhelp'] = 'By default, log objects names are prefixed by &lt;schema&gt;_&lt;table&gt;. But another prefix can be defined for the table. It must be unique in the database.';
	$plugin_lang['emajenterlogdattsp'] = 'Log table tablespace';
	$plugin_lang['emajenterlogidxtsp'] = 'Log index tablespace';
	$plugin_lang['emajspecifytblseqtoupdate'] = 'Specify at least one table or sequence to update';
	$plugin_lang['emajupdatetblseq'] = 'E-Maj: Update properties of a table / sequence in a tables group';
	$plugin_lang['emajspecifytblseqtoremove'] = 'Specify at least one table or sequence to remove';
	$plugin_lang['emajtblseqnogroup'] = 'Error, "%s.%s" is not currently assigned to any tables group.';
	$plugin_lang['emajremovetblseq'] = 'E-Maj: Remove tables / sequences from tables groups';
	$plugin_lang['emajconfirmremove1tblseq'] = 'Are you sure you want to remove %s from the tables group "%s"?';
	$plugin_lang['emajconfirmremovetblseq'] = 'Are you sure you want to remove:';
	$plugin_lang['emajmodifygroupok'] = 'The configuration change is recorded. It will take effect when the concerned tables groups will be (re)created or altered.';
	$plugin_lang['emajmodifygrouperr'] = 'Error while updating tables groups content.';

	// List Groups
	$plugin_lang['emajgrouplist'] = 'Tables groups list';
	$plugin_lang['emajidlegroups'] = 'Tables groups in "IDLE" state ';
	$plugin_lang['emajlogginggroups'] = 'Tables groups in "LOGGING" state ';
	$plugin_lang['emajconfiguredgroups'] = 'Configured but not yet created tables groups ';
	$plugin_lang['emajlogginggrouphelp'] = 'When a tables group is in \'logging\' state, the row insertions, updates and deletions on its tables are recorded.';
 	$plugin_lang['emajidlegrouphelp'] = 'When a tables group is in \'idle\' state, the row insertions, updates and deletions on its tables are NOT recorded.';
	$plugin_lang['emajconfiguredgrouphelp'] = 'The group configuration defines the tables and sequences that will compose it. Once \'configured\', the group must be \'created\' in order to prepare all abjects that will be needed for its use (log tables, functions,...).';
	$plugin_lang['emajcreationdatetime'] = 'Creation date/time';
	$plugin_lang['emajnbtbl'] = '# tables';
	$plugin_lang['emajnbseq'] = '# sequences';
	$plugin_lang['emajnbmark'] = '# marks';
	$plugin_lang['emajdetail'] = 'Detail';
	$plugin_lang['emajsetmark'] = 'Set a mark';
	$plugin_lang['emajsetcomment'] = 'Set a comment';
	$plugin_lang['emajApplyConfChanges'] = 'Apply conf changes';
	$plugin_lang['emajnoidlegroup'] = 'No tables group is currently in idle state.';
	$plugin_lang['emajnologginggroup'] = 'No tables group is currently in logging state.';
	$plugin_lang['emajnoconfiguredgroups'] = 'No tables group is currently configured but not created.';
	$plugin_lang['emajnoschema'] = 'Schema not found (x%s) / ';
	$plugin_lang['emajinvalidschema'] = 'Invalid schema (x%s) / ';
	$plugin_lang['emajnorelation'] = 'Table or sequence not found (x%s) / ';
	$plugin_lang['emajinvalidtable'] = 'Invalid table type (x%s) / ';
	$plugin_lang['emajduplicaterelation'] = 'Table or sequence already assigned to another group (x%s) / ';
	$plugin_lang['emajnoconfiguredgroup'] = 'To create a (another) tables group, go first to the groups configuration tab.<br>Alternatively, you can create an empty group, then add tables and sequences into it, and \'modify\' it to take the configuration change into account.';
	$plugin_lang['emajcreateemptygroup'] = 'Create an empty group';

	// Rollback activity
	$plugin_lang['emajrlbkoperations'] = 'E-Maj Rollbacks';
	$plugin_lang['emajrlbkid'] = 'Rlbk Id.';
	$plugin_lang['emajrlbkstart'] = 'Rollback start';
	$plugin_lang['emajrlbkend'] = 'Rollback end';
	$plugin_lang['emajduration'] = 'Duration';
	$plugin_lang['emajmarksetat'] = 'Mark set at';
	$plugin_lang['emajislogged'] = 'Logged ?';
	$plugin_lang['emajnbsession'] = 'Nb sessions';
	$plugin_lang['emajnbproctable'] = 'Nb processed tables';
	$plugin_lang['emajnbprocseq'] = 'Nb processed sequences';
	$plugin_lang['emajcurrentduration'] = 'Current duration';
	$plugin_lang['emajestimremaining'] = 'Estimated remaining';
	$plugin_lang['emajpctcompleted'] = '% completed';
	$plugin_lang['emajinprogressrlbk'] = 'In progress E-Maj rollbacks';
	$plugin_lang['emajrlbkmonitornotavailable'] = 'In progress rollbacks monitoring is not available.';
	$plugin_lang['emajcompletedrlbk'] = 'Completed E-Maj rollbacks';
	$plugin_lang['emajnbtabletoprocess'] = 'Nb tables to process';
	$plugin_lang['emajnbseqtoprocess'] = 'Nb sequences to process';
	$plugin_lang['emajnorlbk'] = 'No E-Maj rollback.';
	$plugin_lang['emajfilterrlbk1'] = 'Display the';
	$plugin_lang['emajfilterrlbk2'] = 'most recent';
	$plugin_lang['emajfilterrlbk3'] = 'completed since less than';
	$plugin_lang['emajfilterrlbk4'] = 'hours';
	$plugin_lang['emajfilter'] = 'Filter';
	$plugin_lang['emajconsolidablerlbk'] = 'Consolidable E-Maj logged rollbacks';
	$plugin_lang['emajtargetmark'] = 'Target mark';
	$plugin_lang['emajendrollbackmark'] = 'End rollback mark';
	$plugin_lang['emajnbintermediatemark'] = 'Nb intermediate marks';
	$plugin_lang['emajconsolidate'] = 'Consolidate';
	$plugin_lang['emajconsolidaterlbk'] = 'Consolidate a logged rollback';
	$plugin_lang['emajconfirmconsolidaterlbk'] = 'Are you sure you want to consolidate the rollback ended with the mark "%s" of the tables group "%s"?';
	$plugin_lang['emajconsolidaterlbkok'] = 'The rollback ended with the mark "%s" of tables group "%s" has been consolidated.';
	$plugin_lang['emajconsolidaterlbkerr'] = 'Error while consolidating the rollback ended by the mark "%s" of tables group "%s"!';

	// Group's properties and marks
	$plugin_lang['emajgrouppropertiesmarks'] = 'Tables group "%s" properties and marks';
	$plugin_lang['emajgroupproperties'] = 'Tables group "%s" properties';
	$plugin_lang['emajcontent'] = 'Content';
	$plugin_lang['emajgroupmarks'] = 'Tables group "%s" marks';
	$plugin_lang['emajtimestamp'] = 'Date/Time';
	$plugin_lang['emajnbupdates'] = '# row updates';	
	$plugin_lang['emajcumupdates'] = 'Cumulative updates';
	$plugin_lang['emajsimrlbk'] = 'Simulate Rollback';
	$plugin_lang['emajrlbk'] = 'Rollback';
	$plugin_lang['emajfirstmark'] = 'First mark';
	$plugin_lang['emajrename'] = 'Rename';
	$plugin_lang['emajnomark'] = 'The tables group has no mark';
	$plugin_lang['emajprotect'] = 'Protect';
	$plugin_lang['emajunprotect'] = 'Unprotect';

	// Statistics
	$plugin_lang['emajshowstat'] = 'Statistics from E-Maj log for group "%s"';
	$plugin_lang['emajnoupdate'] = 'No update for this tables group'; 
	$plugin_lang['emajcurrentsituation'] = 'Current situation';
	$plugin_lang['emajdetailedstat'] = 'Detailed stats';
	$plugin_lang['emajdetailedlogstatwarning'] = 'Attention, scanning the log tables needed to get detailed statistics may take a long time';
	$plugin_lang['emajlogstatcurrentsituation'] = 'the current situation';
	$plugin_lang['emajlogstatmark'] = 'mark "%s"';
	$plugin_lang['emajlogstattittle'] = 'Table updates between mark "%s" and %s for tables group "%s"';
	$plugin_lang['emajnosimrlbkduration'] = 'The start mark cannot be used for a rollback. No rollback duration can be estimated.';
	$plugin_lang['emajsimrlbkduration'] = 'Rolling the tables group "%s" back to mark "%s" would take about %s.';
	$plugin_lang['emajstatfirstmark'] = 'First mark';
	$plugin_lang['emajstatfirstmarkdatetime'] = 'First mark date/time';
	$plugin_lang['emajstatlastmark'] = 'Last mark';
	$plugin_lang['emajstatlastmarkdatetime'] = 'Last mark date/time';
	$plugin_lang['emajstatverb'] = 'SQL verb';
	$plugin_lang['emajnbinsert'] = '# INSERT';
	$plugin_lang['emajnbupdate'] = '# UPDATE';
	$plugin_lang['emajnbdelete'] = '# DELETE';
	$plugin_lang['emajnbtruncate'] = '# TRUNCATE';
	$plugin_lang['emajnbrole'] = '# roles';
	$plugin_lang['emajstatrows'] = '# row updates';
	$plugin_lang['emajbackgroup'] = 'Go back to the tables group';

	// Group's content
	$plugin_lang['emajgroupcontent'] = 'Content of tables group "%s"';
	$plugin_lang['emajemptygroup'] = 'The tables group "%s" is currently empty.';
	$plugin_lang['emajpriority'] = 'Priority';
	$plugin_lang['emajlogschema'] = 'Log schema';
	$plugin_lang['emajlogdattsp'] = 'Log tablespace';
	$plugin_lang['emajlogidxtsp'] = 'Log index tablespace';
	$plugin_lang['emajnamesprefix'] = 'Objects name prefix';

	// Group creation
	$plugin_lang['emajcreateagroup'] = 'E-Maj: Create a tables group';
	$plugin_lang['emajcreateanemptygroup'] = "Create an empty tables group";
	$plugin_lang['emajconfirmcreategroup'] = 'Are you sure you want to create the tables group "%s"?';
	$plugin_lang['emajinvalidemptygroup'] = 'Error, the tables group "%s" is already created or configured!';
	$plugin_lang['emajcreategroupok'] = 'The tables group "%s" has been created.';
	$plugin_lang['emajcreategrouperr'] = 'Error during group "%s" creation!';

	// Group drop
	$plugin_lang['emajdropagroup'] = 'E-Maj: Drop a tables group';
	$plugin_lang['emajconfirmdropgroup'] = 'Are you sure you want to drop the tables group "%s"?';
	$plugin_lang['emajcantdropgroup'] = 'Dropping the tables group "%s" is not possible. The group is started.';
	$plugin_lang['emajdropgroupok'] = 'The tables group "%s" has been dropped.';
	$plugin_lang['emajdropgrouperr'] = 'Error during tables group "%s" drop!';

	// Group alter
	$plugin_lang['emajaltergroups'] = 'E-Maj: Apply configuration changes';
	$plugin_lang['emajalteraloggingroup'] = 'The group "%s" is in LOGGING state. You can specify a mark name.';
	$plugin_lang['emajconfirmaltergroup'] = 'Are you sure you want to apply the configuraiton changes for the tables group "%s"?';
	$plugin_lang['emajcantaltergroup'] = 'Applying the configuration changes for the group "%s" would generate actions that cannot be executed on LOGGING group. Stop the group before altering it.';
	$plugin_lang['emajaltergroupok'] = 'The configuration changes for the tables group "%s" have been applied.';
	$plugin_lang['emajaltergrouperr'] = 'Error during tables group "%s" configuration change!';

	// Groups alter
	$plugin_lang['emajalterallloggingroups'] = 'The groups "%s" are in LOGGING state. You can specify a mark name.';
	$plugin_lang['emajconfirmaltergroups'] = 'Are you sure you want to apply the configuraiton changes for the tables groups "%s"?';
	$plugin_lang['emajaltergroupsok'] = 'The configuration changes for the tables groups "%s" have been applied.';
	$plugin_lang['emajaltergroupserr'] = 'Error during tables groups "%s" configuration change!';

	// Group comment
	$plugin_lang['emajcommentagroup'] = 'E-Maj: Record a comment for a tables group';
	$plugin_lang['emajcommentgroup'] = 'Enter, modify or erase the comment for tables group "%s".';
	$plugin_lang['emajcommentgroupok'] = 'The comment for the tables group "%s" has been recorded.';
	$plugin_lang['emajcommentgrouperr'] = 'Error during comment recording for tables group "%s"!';

	// Group protect
	$plugin_lang['emajcantprotectgroup'] = 'Protecting the tables group "%s" is not possible. The group is stopped.';
	$plugin_lang['emajprotectgroupok'] = 'The tables group "%s" is now protected against rollbacks.';
	$plugin_lang['emajprotectgrouperr'] = 'Error while protecting the tables group "%s"!';

	// Group unprotect
	$plugin_lang['emajcantunprotectgroup'] = 'Unprotecting the tables group "%s" is not possible. The group is stopped.';
	$plugin_lang['emajunprotectgroupok'] = 'The tables group "%s" is now unprotected.';
	$plugin_lang['emajunprotectgrouperr'] = 'Error while unprotecting the tables group "%s"!';

	// Group start
	$plugin_lang['emajstartagroup'] = 'E-Maj: Start a tables group';
	$plugin_lang['emajconfirmstartgroup'] = 'Starting the tables group "%s"';
	$plugin_lang['emajinitmark'] = 'Initial mark';
	$plugin_lang['emajoldlogsdeletion'] = 'Old logs deletion';
	$plugin_lang['emajcantstartgroup'] = 'Starting the tables group "%s" is not possible. The group is already started.';
	$plugin_lang['emajstartgroupok'] = 'The tables group "%s" is started with the mark "%s".';
	$plugin_lang['emajstartgrouperr'] = 'Error during tables group "%s" start!';

	// Groups start
	$plugin_lang['emajstartgroups'] = 'E-Maj: Start tables groups';
	$plugin_lang['emajconfirmstartgroups'] = 'Starting the tables groups "%s"';
	$plugin_lang['emajcantstartgroups'] = 'Starting the tables groups "%s" is not possible. the group "%s" is already started.';
	$plugin_lang['emajstartgroupsok'] = 'The tables groups "%s" are started with the mark "%s".';
	$plugin_lang['emajstartgroupserr'] = 'Error during tables groups "%s" start!';

	// Group stop
	$plugin_lang['emajstopagroup'] = 'E-Maj: Stop a tables group';
	$plugin_lang['emajconfirmstopgroup'] = 'Stopping the tables group "%s"';
	$plugin_lang['emajstopmark'] = 'Final mark';
	$plugin_lang['emajforcestop'] = 'Forced stop (in case of problem only)';
	$plugin_lang['emajcantstopgroup'] = 'Stopping the tables group "%s" is not possible. The group is already stopped.';
	$plugin_lang['emajstopgroupok'] = 'The tables group "%s" has been stopped.';
	$plugin_lang['emajstopgrouperr'] = 'Error during tables group "%s" stop!';

	// Groups stop
	$plugin_lang['emajstopgroups'] = 'E-Maj: Stop tables groups';
	$plugin_lang['emajconfirmstopgroups'] = 'Stopping the tables groups "%s"';
	$plugin_lang['emajcantstopgroups'] = 'Stopping the tables groups "%s" is impossible. The group "%s" is already stopped.';
	$plugin_lang['emajstopgroupsok'] = 'The tables groups "%s" have been stopped.';
	$plugin_lang['emajstopgroupserr'] = 'Error during tables groups "%s" stop!';

	// Group reset
	$plugin_lang['emajresetagroup'] = 'E-Maj: Reset a tables group';
	$plugin_lang['emajconfirmresetgroup'] = 'Are you sure you want to reset the tables group "%s"?';
	$plugin_lang['emajcantresetgroup'] = 'Reseting the tables group "%s" is not possible. The group is started.';
	$plugin_lang['emajresetgroupok'] = 'The tables group "%s" has been reset.';
	$plugin_lang['emajresetgrouperr'] = 'Error during tables group "%s" reset!';

	// Set Mark for one or several groups
	$plugin_lang['emajsetamark'] = 'E-Maj: Set a mark';
	$plugin_lang['emajconfirmsetmarkgroup'] = 'Setting a mark for the tables group(s) "%s":';
	$plugin_lang['emajcantsetmarkgroup'] = 'Setting a mark on the tables group "%s" is not possible. The group is stopped.';
	$plugin_lang['emajinvalidmark'] = 'The supplied mark (%s) is invalid!';
	$plugin_lang['emajsetmarkgroupok'] = 'The mark "%s" has been set for the tables group(s) "%s".';
	$plugin_lang['emajsetmarkgrouperr'] = 'Error during mark set "%s" for the tables group(s) "%s"!';
	$plugin_lang['emajcantsetmarkgroups'] = 'Setting a mark for the tables groups "%s" is not possible. The group "%s" is stopped.';

	// Protect mark
	$plugin_lang['emajcantprotectmarkgroup'] = 'Protecting a mark on the tables group "%s" is not possible. The group is stopped.';
	$plugin_lang['emajprotectmarkgroupok'] = 'The mark "%s" for the tables group "%s" is now protected against rollbacks.';
	$plugin_lang['emajprotectmarkgrouperr'] = 'Error while protecting the mark "%s" for the tables group "%s"!';

	// Unprotect mark
	$plugin_lang['emajcantunprotectmarkgroup'] = 'Unprotecting a mark on the tables group "%s" is not possible. The group is stopped.';
	$plugin_lang['emajunprotectmarkgroupok'] = 'The mark "%s" for the tables group "%s" is now unprotected.';
	$plugin_lang['emajunprotectmarkgrouperr'] = 'Error while unprotecting the mark "%s" for the tables group "%s"!';

	// Comment mark
	$plugin_lang['emajcommentamark'] = 'E-Maj: Record a comment for a mark';
	$plugin_lang['emajcommentmark'] = 'Enter, modify or erase the comment for the mark "%s" of the tables group "%s"';
	$plugin_lang['emajcommentmarkok'] = 'The comment for the mark "%s" of the tables group "%s" has been recorded.';
	$plugin_lang['emajcommentmarkerr'] = 'Error during comment recording for the mark "%s" of the tables group "%s"!';

	// Group rollback
	$plugin_lang['emajrlbkagroup'] = 'E-Maj: Rollback a tables group';
	$plugin_lang['emajconfirmrlbkgroup'] = 'Rollbacking the tables group "%s" to the mark "%s"';
	$plugin_lang['emajselectmarkgroup'] = 'Rollbacking tables group "%s" to mark: ';
	$plugin_lang['emajrlbkthenmonitor'] = 'Rollback and monitor';
	$plugin_lang['emajcantrlbkidlegroup'] = 'Rollbacking the tables group "%s" is not possible. The group is stopped.';
	$plugin_lang['emajcantrlbkprotgroup'] = 'Rollbacking the tables group "%s" is not possible. The group is protected.';
	$plugin_lang['emajcantrlbkinvalidmarkgroup'] = 'Rollbacking the tables group "%s" is not possible. The mark "%s" is not valid.';
	$plugin_lang['emajreachaltergroup'] = 'Rollbacking the tables group "%s" to the mark "%s" would reach a point in time prior alter_group operations. Please confirm the rollback.';
	$plugin_lang['emajautorolledback'] = 'Automatically rolled back?';
	$plugin_lang['emajgroupisprotected'] = 'The group "%s" is protected.';
	$plugin_lang['emajinvalidrlbkmark'] = 'Mark "%s" is not valid anymore.';
	$plugin_lang['emajrlbkgroupok'] = 'the tables group "%s" has been rollbacked to the mark "%s".';
	$plugin_lang['emajrlbkgrouperr'] = 'Error during tables group "%s" rollback to the mark "%s"!';
	$plugin_lang['emajbadpsqlpath'] = 'Asynchronous rollback not possible: the configured pathname of the psql command (%s) is not valid.';
	$plugin_lang['emajbadtempdir'] = 'Asynchronous rollback not possible: the configured temporary directory (%s) is not valid.';
	$plugin_lang['emajasyncrlbkstarted'] = 'Rollback started (id = %s).';

	// Groups rollback
	$plugin_lang['emajrlbkgroups'] = 'E-Maj: Rollback tables groups';
	$plugin_lang['emajselectmarkgroups'] = 'Rollbacking the tables groups "%s" to the mark: ';
	$plugin_lang['emajcantrlbkidlegroups'] = 'Rollbacking the tables groups "%s" is not possible. The group "%s" is stopped.';
	$plugin_lang['emajcantrlbkprotgroups'] = 'Rollbacking the tables groups "%s" is not possible. The groups "%s" are protected.';
	$plugin_lang['emajnomarkgroups'] = 'No common mark for tables groups "%s" can be used for a rollback.';
	$plugin_lang['emajcantrlbkinvalidmarkgroups'] = 'Rollbacking the tables groups "%s" is not possible. The mark "%s" is not valid.';
	$plugin_lang['emajreachaltergroups'] = 'Rollbacking the tables groups "%s" to the mark "%s" would reach a point in time prior alter_group operations. Please confirm the rollback.';
	$plugin_lang['emajrlbkgroupsok'] = 'Tables groups "%s" have been rollbacked to mark "%s".';
	$plugin_lang['emajrlbkgroupserr'] = 'Error during tables groups "%s" rollback to mark "%s"!';

	// Elementary alter group actions previously executed, reported at rollback time 
	$plugin_lang['emajalteredremovetbl'] = 'The table "%s.%s" has been removed from the tables group "%s"';
	$plugin_lang['emajalteredremoveseq'] = 'The sequence "%s.%s" has been removed from the tables group "%s"';
	$plugin_lang['emajalteredrepairtbl'] = 'E-Maj objects for the table "%s.%s" have been repaired';
	$plugin_lang['emajalteredrepairseq'] = 'E-Maj objects for the sequence "%s.%s" have been repaired';
	$plugin_lang['emajalteredchangetbllogschema'] = 'The E-Maj log schema for the table "%s.%s" has been changed';
	$plugin_lang['emajalteredchangetblnamesprefix'] = 'The E-Maj names prefix for the table "%s.%s" has been changed';
	$plugin_lang['emajalteredchangetbllogdatatsp'] = 'The tablespace for the log data files of the table "%s.%s" has been changed';
	$plugin_lang['emajalteredchangetbllogindextsp'] = 'The tablespace for the log index files of the table "%s.%s" has been changed';
	$plugin_lang['emajalteredassignrel'] = 'The table or sequence "%s.%s" has been moved from the tables group "%s" to the tables group "%s"';
	$plugin_lang['emajalteredchangerelpriority'] = 'The E-Maj priority for the table "%s.%s" has been changed';
	$plugin_lang['emajalteredaddtbl'] = 'The table "%s.%s" has been added to the tables group "%s"';
	$plugin_lang['emajalteredaddseq'] = 'The sequence "%s.%s" has been added to the tables group "%s"';

	// Mark renaming
	$plugin_lang['emajrenameamark'] = 'E-Maj : Rename a mark';
	$plugin_lang['emajconfirmrenamemark'] = 'Renaming the mark "%s" of the tables group "%s"';
	$plugin_lang['emajnewnamemark'] = 'New name';
	$plugin_lang['emajrenamemarkok'] = 'The mark "%s" of the tables group "%s" has been renamed into "%s".';
	$plugin_lang['emajrenamemarkerr'] = 'Error during renaming the mark "%s" of the tables group "%s" into "%s"!';

	// Mark deletion
	$plugin_lang['emajdelamark'] = 'E-Maj: Delete a mark';
	$plugin_lang['emajconfirmdelmark'] = 'Are you sure you want to delete the mark "%s" for the tables group "%s"?';
	$plugin_lang['emajdelmarkok'] = 'Mark "%s" has been deleted for the tables group "%s".';
	$plugin_lang['emajdelmarkerr'] = 'Error during mark "%s" deletion for the tables group "%s"!';

	// Marks before mark deletion
	$plugin_lang['emajdelmarks'] = 'E-Maj: Delete marks';
	$plugin_lang['emajconfirmdelmarks'] = 'Are you sure you want to delete all marks and logs preceeding the mark "%s" for the tables group "%s"?';
	$plugin_lang['emajdelmarksok'] = 'All (%s) marks preceeding mark "%s" have been deleted for the tables group "%s".';
	$plugin_lang['emajdelmarkserr'] = 'Error during the deletion of marks preceeding mark "%s" for the tables group "%s"!';
?>
