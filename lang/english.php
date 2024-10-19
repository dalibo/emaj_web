<?php

/**
 * English language file for Emaj_web.
 * Use this as a basis for new translations.
 */

//
// Common strings
//

	// Language and character set
	$lang['applocale'] = 'en-US';
	$lang['applocalearray'] = array('en-US','en_US');
	$lang['applangdir'] = 'ltr';
	// Php format for timestamp fields, distinguishing the format for:
	// - the old times, producing something like '23 Jun 2020 12:34:56'
	$lang['stroldtimestampformat'] = 'dd MMM YYYY HH:mm:ss';
	// - the recent times, producing something like 'Mon 23 Jun 12:34:56'
	$lang['strrecenttimestampformat'] = 'EEE dd MMM HH:mm:ss';
	// - the timestamp abbreviated into time with milliseconds
	$lang['strprecisetimeformat'] = 'HH:mm:ss.SSS';
	// Internal format for full interval display
	$lang['strintervalformat'] = 'DD d HH h MM min SS.US s';

	// Basic strings
	$lang['straction'] = 'Action';
	$lang['stractions'] = 'Actions';
	$lang['stractionsonselectedobjects'] = 'Actions on objects (%s)';
	$lang['stractivity'] = 'Activity';
	$lang['stractual'] = 'Actual';
	$lang['strall'] = 'All';
	$lang['strassign'] = 'Assign';
	$lang['strassigned'] = 'Assigned';
	$lang['strauditonly'] = 'AUDIT-ONLY';
	$lang['strautorefresh'] = 'Auto refresh';
	$lang['strback'] = 'Back';
	$lang['strbacktolist'] = 'Back to the list';
	$lang['strbegin'] = 'Begin';
	$lang['strbrowse'] = 'Browse';
	$lang['strcancel'] = 'Cancel';
	$lang['strchanges'] = 'Row changes';
	$lang['strclear'] = 'Clear';
	$lang['strcollapse'] = 'Collapse';
	$lang['strcolumn'] = 'Column';
	$lang['strcomment'] = 'Comment';
	$lang['strcommentlabel'] = 'Comment: ';
	$lang['strconfirm'] = 'Confirm';
	$lang['strconstraints'] = 'Constraints';
	$lang['strcontent'] = 'Content';
	$lang['strcreate'] = 'Create';
	$lang['strcumulated'] = 'Cumulated';
	$lang['strcurrentvalue'] = 'Current value';
	$lang['strdatetime'] = 'Date-time';
	$lang['strdefault'] = 'Default';
	$lang['strdelete'] = 'Delete';
	$lang['strdifferentvalues'] = '(%s different values)';
	$lang['strdisplay'] = 'Display';
	$lang['strdownload'] = 'Download';
	$lang['strdrop'] = 'Drop';
	$lang['stredit'] = 'Edit';
	$lang['strellipsis'] = '...';
	$lang['stremajproperties'] = 'E-Maj properties';
	$lang['stremajschema'] = 'E-Maj schema';
	$lang['stremajtrigger'] = 'E-Maj trigger';
	$lang['strencoding'] = 'Encoding';
	$lang['strend'] = 'End';
	$lang['strendmark'] = 'End mark';
	$lang['strestimate'] = 'Estimate';
	$lang['strestimatedduration'] = 'Estimated duration';
	$lang['strestimates'] = 'Estimates';
	$lang['strexecute'] = 'Execute';
	$lang['strexpand'] = 'Expand';
	$lang['strexport'] = 'Export';
	$lang['strfalse'] = 'FALSE';
	$lang['strfirst'] = '<< First';
	$lang['strforget'] = 'Forget';
	$lang['strgotoppage'] = 'back to top';
	$lang['strgroup'] = 'Group';
	$lang['strgroups'] = 'Groups';
	$lang['strgrouptype'] = 'Group type';
	$lang['strhost'] = 'Host';
	$lang['stridle'] = 'Idle';
	$lang['strimport'] = 'Import';
	$lang['strintroduction'] = 'Introduction';
	$lang['strinvert'] = 'Invert';
	$lang['strlast'] = 'Last >>';
	$lang['strlevel'] = 'Level';
	$lang['strlogged'] = 'logged';
	$lang['strlogging'] = 'Logging';
	$lang['strlogindexes'] = 'Log indexes';
	$lang['strlogsession'] = 'Log session';
	$lang['strlogsize'] = 'Log size';
	$lang['strlogtables'] = 'Log tables';
	$lang['strmark'] = 'Mark';
	$lang['strmarks'] = 'Marks';
	$lang['strmessage'] = 'Message';
	$lang['strmove'] = 'Move';
	$lang['strname'] = 'Name';
	$lang['strnewvalue'] = 'New value';
	$lang['strnext'] = 'Next >';
	$lang['strno'] = 'No';
	$lang['strnone'] = 'None';
	$lang['strnotassigned'] = 'Not assigned';
	$lang['strnotnull'] = 'Not Null';
	$lang['strnumber'] = 'Number';
	$lang['strok'] = 'OK';
	$lang['stropen'] = 'Open';
	$lang['strowner'] = 'Owner';
	$lang['strpagebottom'] = 'Go to bottom';
	$lang['strpk'] = 'Primary key';
	$lang['strport'] = 'Port';
	$lang['strprev'] = '< Prev';
	$lang['strproperties'] = 'Properties';
	$lang['strprotect'] = 'Protect';
	$lang['strprotected'] = 'Protected against E-Maj rollbacks';
	$lang['strquantity'] = 'Quantity';
	$lang['strqueryresults'] = 'Query Results';
	$lang['strrecreate'] = 'Recreate';
	$lang['strreestimate'] = 'Reestimate';
	$lang['strrefresh'] = 'Refresh';
	$lang['strremove'] = 'Remove';
	$lang['strremoved'] = 'Removed';
	$lang['strrename'] = 'Rename';
	$lang['strrequiredfield'] = 'Required field';
	$lang['strreset'] = 'Reset';
	$lang['strrlbk'] = 'Rollback';
	$lang['strrole'] = 'Role';
	$lang['strroles'] = 'Roles';
	$lang['strrollback'] = 'E-Maj rollback';
	$lang['strrollbackable'] = 'ROLLBACK-ABLE';
	$lang['strrollbacktype'] = 'Rollback type';
	$lang['strrows'] = 'row(s)';
	$lang['strrowsaff'] = 'row(s) affected.';
	$lang['strruntime'] = 'Total runtime: %s ms';
	$lang['strselect'] = 'Select';
	$lang['strselectfile'] = 'Select a file';
	$lang['strsequence'] = 'Sequence';
	$lang['strsequences'] = 'Sequences';
	$lang['strsetcomment'] = 'Set a comment';
	$lang['strsince'] = 'Since';
	$lang['strsinceinsec'] = 'Since (sec)';
	$lang['strsql'] = 'SQL';
	$lang['strstart'] = 'Start';
	$lang['strstartmark'] = 'Start mark';
	$lang['strstate'] = 'State';
	$lang['strstop'] = 'Stop';
	$lang['strtable'] = 'Table';
	$lang['strtables'] = 'Tables';
	$lang['strtablesgroup'] = 'Tables group';
	$lang['strtablespace'] = 'Tablespace';
	$lang['strtrue'] = 'TRUE';
	$lang['strtxid'] = 'Transaction id.';
	$lang['strtype'] = 'Type';
	$lang['strunlogged'] = 'unlogged';
	$lang['strunprotect'] = 'Unprotect';
	$lang['strupdate'] = 'Update';
	$lang['strvisible'] = 'Visible';
	$lang['stryes'] = 'Yes';

	// Sizes
	$lang['strnoaccess'] = 'No Access'; 
	$lang['strsize'] = 'Size';
	$lang['strbytes'] = 'bytes';
	$lang['strkb'] = 'kB';
	$lang['strmb'] = 'MB';
	$lang['strgb'] = 'GB';
	$lang['strtb'] = 'TB';

	// Common help messages
	$lang['strmarknamehelp'] = 'The mark name must be unique within the group. A % character represents the current time (format hh.mn.ss.ms).';
	$lang['strmarknamemultihelp'] = 'The mark name must be unique within the concerned groups. A % character represents the current time (format hh.mn.ss.ms).';
	$lang['strfiltershelp'] = 'Display/hide filters. Filters on the columns content may contain character strings (abc), numbers (123), inequality conditions (>= 1000), regular expressions (/^ABC\d\d/), multiple conditions with \'and\', \'or\', \'!\' operators.';

	// Error handling
	$lang['strnotloaded'] = 'Your PHP installation does not support PostgreSQL. You need to recompile PHP using the --with-pgsql configure option.';
	$lang['strpostgresqlversionnotsupported'] = 'This PostgreSQL version is not supported. The minimum supported version is %s.';
	$lang['strbadschema'] = 'Invalid schema specified.';
	$lang['strsqlerror'] = 'SQL error:';
	$lang['strinstatement'] = 'In statement:';
	$lang['strnodata'] = 'No rows found.';
	$lang['strnoobjects'] = 'No objects found.';
	$lang['strcannotdumponwindows'] = 'Dumping of complex table and schema names on Windows is not supported.';
	$lang['strinvalidserverparam'] = 'Attempt to connect with invalid server parameter, possibly someone is trying to hack your system.'; 
	$lang['strnoserversupplied'] = 'No server supplied!';
	$lang['strconnectionfail'] = 'Can not connect to server.';
	$lang['strimporterror-uploadedfile'] = 'Import error: file could not be uploaded to the server';
	$lang['strimportfiletoobig'] = 'Import error: the file to upload is too big.';
	$lang['strnotjsonfile'] = 'The file %s has not a valid JSON format.';

//
// Tabs or actions specific strings
//

	// Miscellaneous
	$lang['strloading'] = 'Loading...';
	$lang['strerrorloading'] = 'Error Loading';
	$lang['strclicktoreload'] = 'Click to reload';

	// Welcome
	$lang['strintro'] = 'Welcome to %s %s, the web client for';
	$lang['strlink'] = 'Some links:';
	$lang['strpgsqlhome'] = 'PostgreSQL Homepage';
	$lang['strpgsqlhome_url'] = 'http://www.postgresql.org/';
	$lang['stremajdoc'] = 'Online E-Maj documentation';
	$lang['stremajdoc_url'] = 'http://emaj.readthedocs.io/en/latest/';
	$lang['stremajproject'] = 'E-Maj on github';
	$lang['stremajwebproject'] = 'Emaj_web on github';

	// Servers and servers Groups
	$lang['strserver'] = 'Server';
	$lang['strservers'] = 'Servers';
	$lang['strconfiguredservers'] = 'PostgreSQL servers';
	$lang['strgroupservers'] = 'PostgreSQL servers in group "%s"';
	$lang['strallservers'] = 'All servers';
	$lang['strgroupgroups'] = 'Groups in group "%s"';
	$lang['strserversgroups'] = 'Servers groups';

	// Connection and disconnection
	$lang['strlogin'] = 'Login';
	$lang['strlogintitle'] = 'Login to %s';
	$lang['strusername'] = 'Username';
	$lang['strpassword'] = 'Password';
	$lang['strtrycred'] = 'Use these credentials for all servers';
	$lang['strloginfailed'] = 'Login failed';
	$lang['strtopbar'] = 'Connection: %s:%s - role "%s"';
	$lang['strlogout'] = 'Logout';
	$lang['strlogoutmsg'] = 'Logged out of %s';
	$lang['strlogindisallowed'] = 'Login disallowed for security reasons.';
	$lang['strconfdropcred'] = 'For security reason, disconnecting will destroy your shared login information. Are you sure you want to disconnect ?';

	// User-supplied SQL editing
	$lang['strsqledit'] = 'SQL statement editing';
	$lang['strsearchpath'] = 'Schemas search path';
	$lang['strpaginate'] = 'Paginate results';

	// User-supplied SQL history
	$lang['strhistory'] = 'History';
	$lang['strsqlhistory'] = 'SQL Statements history';
	$lang['strnohistory'] = 'No history.';
	$lang['strclearhistory'] = 'Clear history';
	$lang['strdelhistory'] = 'Delete from history';
	$lang['strconfdelhistory'] = 'Really remove this request from history?';
	$lang['strconfclearhistory'] = 'Really clear history?';
	$lang['strnodatabaseselected'] = 'Please, select a database.';

	// E-Maj html titles and tabs
	$lang['strgroupsmanagement'] = 'E-Maj groups management';
	$lang['strgroupsconfiguration'] = 'Tables groups\' configuration';
	$lang['strgroupsconf'] = 'Groups conf.';
	$lang['strrollbacksmanagement'] = 'E-Maj rollbacks management';
	$lang['strrlbkop'] = 'E-Maj Rollbacks';
	$lang['strenvironment'] = 'E-Maj environment';
	$lang['strenvir'] = 'E-Maj';
	$lang['strchangesstat'] = 'Changes statistics';

	// Databases
	$lang['strdatabase'] = 'Database';
	$lang['strdatabases'] = 'Databases';
	$lang['strdatabaseslist'] = 'Server\'s databases';
	$lang['strnodatabases'] = 'No databases found.';
	$lang['strsqlexecuted'] = 'SQL executed.';

// E-Maj groups

	// E-Maj groups lists
	$lang['stridlegroups'] = 'Tables groups in "IDLE" state ';
	$lang['strlogginggroups'] = 'Tables groups in "LOGGING" state ';
	$lang['strconfiguredgroups'] = 'Configured but not yet created tables groups ';
	$lang['strlogginggrouphelp'] = 'When a tables group is in \'logging\' state, the row insertions, updates and deletions on its tables are recorded.';
 	$lang['stridlegrouphelp'] = 'When a tables group is in \'idle\' state, the row insertions, updates and deletions on its tables are NOT recorded.';
	$lang['strconfiguredgrouphelp'] = 'The group configuration defines the tables and sequences that will compose it. Once \'configured\', the group must be \'created\' in order to prepare all abjects that will be needed for its use (log tables, functions,...).';
	$lang['strApplyConfChanges'] = 'Apply conf changes';
	$lang['strnoidlegroup'] = 'No tables group is currently in idle state.';
	$lang['strnologginggroup'] = 'No tables group is currently in logging state.';
	$lang['strnoconfiguredgroups'] = 'No tables group is currently configured but not created.';
	$lang['strnoschema'] = 'Schema not found (x%s) / ';
	$lang['strinvalidschema'] = 'Invalid schema (x%s) / ';
	$lang['strnorelation'] = 'Table or sequence not found (x%s) / ';
	$lang['strinvalidtable'] = 'Invalid table type (x%s) / ';
	$lang['strduplicaterelation'] = 'Table or sequence already assigned to another group (x%s) / ';
	$lang['strnoconfiguredgroup'] = 'To create a (another) tables group, go first to the groups configuration tab.<br>Alternatively, you can create an empty group, then add tables and sequences into it, and apply the configuration change.';
	$lang['strcreateemptygroup'] = 'Create an empty group';
	$lang['strnewgroup'] = 'New group';
	$lang['strdroppedgroupslist'] = 'Old dropped tables groups';
	$lang['strnodroppedgroup'] = 'No old dropped tables groups.';
	$lang['strnoselectedgroup'] = 'No tables group has been selected!';

	// Group creation
	$lang['strcreateagroup'] = 'E-Maj: Create a tables group';
	$lang['strconfirmcreategroup'] = 'Are you sure you want to create the tables group "%s"?';
	$lang['strcreategroupok'] = 'The tables group "%s" has been created.';
	$lang['strcreategrouperr'] = 'Error while creating the tables group "%s".';

	// Export groups configuration
	$lang['strexportgroupsconf'] = 'Export a tables groups configuration';
	$lang['strexportgroupsconfselect'] = 'Select the tables groups whose configuration will be exported on a local file.';
	$lang['strexportgroupserr'] = 'Error while exporting tables groups "%s".';

	// Import groups configuration
	$lang['strimportgroupsconf'] = 'Import a tables groups configuration';
	$lang['strimportgroupsinfile'] = 'Select the tables groups to import from the file "%s":';
	$lang['strimportgroupsinfileerr'] = 'Some errors have been detected in the file "%s":';
	$lang['strcheckjsongroupsconf201'] = 'The JSON structure does not contain any "tables_groups" array.';
	$lang['strcheckjsongroupsconf202'] = 'The JSON structure references several times the tables groups "%s".';
	$lang['strcheckjsongroupsconf210'] = 'The tables group #%s has no "group" attribute.';
	$lang['strcheckjsongroupsconf211'] = 'For the tables group "%s", the keyword "%s" is unknown.';
	$lang['strcheckjsongroupsconf212'] = 'For the tables group "%s", the "is_rollbackable" attribute is not a boolean.';
	$lang['strcheckjsongroupsconf220'] = 'In the tables group "%s", the table #%s has no "schema" attribute.';
	$lang['strcheckjsongroupsconf221'] = 'In the tables group "%s", the table #%s has no "table" attribute.';
	$lang['strcheckjsongroupsconf222'] = 'In the tables group "%s" and for the table %s.%s, the keyword "%s" is unknown.';
	$lang['strcheckjsongroupsconf223'] = 'In the tables group "%s" and for the table %s.%s, the "priority" attribute is not a number.';
	$lang['strcheckjsongroupsconf224'] = 'In the tables group "%s" and for the table %s.%s, the trigger #%s has no "trigger" attribute.';
	$lang['strcheckjsongroupsconf225'] = 'In the tables group "%s" and for a trigger of the table %s.%s, the keyword "%s" is unknown.';
	$lang['strcheckjsongroupsconf226'] = 'In the tables group "%s" and for the table %s.%s, the trigger #%s is not a string.';
	$lang['strcheckjsongroupsconf227'] = 'In the tables group "%s" and for the table %s.%s, the "ignored_triggers" attribute is not an array.';
	$lang['strcheckjsongroupsconf230'] = 'In the tables group "%s", the sequence #%s has no "schema" attribute.';
	$lang['strcheckjsongroupsconf231'] = 'In the tables group "%s", the sequence #%s has no "sequence" attribute.';
	$lang['strcheckjsongroupsconf232'] = 'In the tables group "%s" and for the sequence %s.%s, the keyword "%s" is unknown.';
	$lang['strgroupsconfimport250'] = 'The tables group "%s" to import is not referenced in the JSON structure.';
	$lang['strgroupsconfimport251'] = 'The tables group "%s" already exists.';
	$lang['strgroupsconfimport252'] = 'Changing the type of the tables group "%s" is not allowed. You may drop this tables group before importing the configuration.';
	$lang['strgroupsconfimport260'] = 'In the group "%s" and for the table %s.%s, the trigger %s does not exist.';
	$lang['strgroupsconfimport261'] = 'In the group "%s" and for the table %s.%s, the trigger %s is an E-Maj trigger.';
	$lang['strgroupsconfimportpreperr'] = 'Importing the configuration of tables groups "%s" from the file  "%s" has failed for the following reasons:';
	$lang['strgroupsconfimported'] = '%s tables groups have been imported from the file "%s".';
	$lang['strgroupsconfimporterr'] = 'Error while importing tables groups from file "%s"';

	// Groups content checks
	$lang['strgroupconfok'] = 'The configuration of the tables group "%s" is correct.';
	$lang['strgroupconfwithdiag'] = 'The checks performed on the tables group "%s" configuration show that:';
	$lang['strgroupsconfok'] = 'The configuration of the tables groups "%s" is correct.';
	$lang['strgroupsconfwithdiag'] = 'The checks performed on the tables groups "%s" configuration show that:';
	$lang['strcheckconfgroups01'] = 'In the group "%s", the table or sequence "%s.%s" does not exist.';
	$lang['strcheckconfgroups02'] = 'In the group "%s", the table "%s.%s" is a partitionned table (only elementary partitions are supported by E-Maj).';
	$lang['strcheckconfgroups03'] = 'In the group "%s", the table or sequence "%s.%s" belongs to an E-Maj schema.';
	$lang['strcheckconfgroups04'] = 'In the group "%s", the table or sequence "%s.%s" already belongs to the group "%s".';
	$lang['strcheckconfgroups05'] = 'In the group "%s", the table "%s.%s" is a TEMPORARY table.';
	$lang['strcheckconfgroups10'] = 'In the group "%s", the table "%s.%s" would have a duplicate emaj prefix "%s".';
	$lang['strcheckconfgroups11'] = 'In the group "%s", the table "%s.%s" would have an already used emaj prefix "%s".';
	$lang['strcheckconfgroups12'] = 'In the group "%s", for the table "%s.%s", the data log tablespace %s does not exist.';
	$lang['strcheckconfgroups13'] = 'In the group "%s", for the table "%s.%s", the index log tablespace %s does not exist.';
	$lang['strcheckconfgroups15'] = 'In the group "%s", for the table "%s.%s", the trigger "%s" does not exist.';
	$lang['strcheckconfgroups16'] = 'In the group "%s", for the table "%s.%s", the trigger "%s" is an E-Maj trigger.';
	$lang['strcheckconfgroups20'] = 'In the group "%s", the table "%s.%s" is an UNLOGGED table.';
	$lang['strcheckconfgroups21'] = 'In the group "%s", the table "%s.%s" is declared WITH OIDS.';
	$lang['strcheckconfgroups22'] = 'In the group "%s", the table "%s.%s" has no PRIMARY KEY.';
	$lang['strcheckconfgroups30'] = 'in the group "%s", for the sequence "%s.%s", the secondary log schema suffix is not NULL.';
	$lang['strcheckconfgroups31'] = 'In the group "%s", for the sequence "%s.%s", the emaj names prefix is not NULL.';
	$lang['strcheckconfgroups32'] = 'In the group "%s", for the sequence "%s.%s", the data log tablespace is not NULL.';
	$lang['strcheckconfgroups33'] = 'In the group "%s", for the sequence "%s.%s", the index log tablespace is not NULL.';

	// Group forget
	$lang['strforgetagroup'] = 'E-Maj: Erase a tables group from histories';
	$lang['strconfirmforgetgroup'] = 'Are you sure you want to erase the tables group "%s" from histories?';
	$lang['strforgetgroupok'] = 'The tables group "%s" has been erased from histories.';
	$lang['strforgetgrouperr'] = 'Error while erasing the tables group "%s" from histories.';

	// Group's properties and marks
	$lang['strgroupproperties'] = 'Tables group "%s" properties';
	$lang['strgroupmarks'] = 'Tables group "%s" marks';
	$lang['strlogsessionshelp'] = 'Log session, representing the time interval between the tables group start and stop.';
	$lang['strlogsessionstart'] = 'Log session started at: %s';
	$lang['strlogsessionstop'] = ' and stopped at: %s';
	$lang['strtimestamp'] = 'Date/Time';
	$lang['strcumchangeshelp'] = 'The cummulative number of row changes represents the number of row changes to cancel in case of E-Maj rollback to the corresponding mark.';
	$lang['strfirstmark'] = 'First mark';
	$lang['strnomark'] = 'The tables group has no mark';
	$lang['strgroupcreatedat'] = 'Created at';
	$lang['strgroupcreateddroppedat'] = 'Created/dropped at';
	$lang['strgrouplatesttype'] = 'Latest type';
	$lang['strgrouplatestdropat'] = 'Latest drop at';
	$lang['strgroupstartedat'] = 'Started at';
	$lang['strgroupstoppedat'] = 'Stopped at';
	$lang['strmarksetat'] = 'Set at';
	$lang['stractivemark'] = 'Active mark, thus usable for an E-Maj rollback.';
	$lang['strdeletedmark'] = 'A stop of the changes recording has left the mark inactive, thus unusable for an E-Maj rollback.';
	$lang['strprotectedmark'] = 'The protection set on the mark blocks any E-Maj rollbacks on prior marks.';
	$lang['strsetmark'] = 'Set a mark';

	// Generic error messages for groups and marks checks
	$lang['strgroupmissing'] = 'The tables group "%s" does not exist anymore!';
	$lang['strgroupsmissing'] = '%s tables groups (%s) do not exist anymore!';
	$lang['strgroupalreadyexists'] = 'The tables group "%s" already exists!';
	$lang['strgroupstillexists'] = 'The tables group "%s" still exists!';
	$lang['strgroupnotstopped'] = 'The tables group "%s" is not stopped anymore!';
	$lang['strgroupsnotstopped'] = '%s tables group (%s) are not stopped anymore!';
	$lang['strgroupnotstarted'] = 'The tables group "%s" is not started anymore!';
	$lang['strgroupsnotstarted'] = '%s tables group (%s) are not started anymore!';
	$lang['strgroupprotected'] = 'The tables group "%s" is protected.';
	$lang['strgroupsprotected'] = '%s tables groups (%s) are protected.';
	$lang['strinvalidmark'] = 'The supplied mark (%s) is invalid!';
	$lang['strduplicatemarkgroup'] = 'The mark "%s" already exists in the tables group "%s"!';
	$lang['strduplicatemarkgroups'] = 'The mark "%s" already exists in %s tables groups (%s)!';
	$lang['strmarkmissing'] = 'The mark "%s" does not exist anymore!';
	$lang['strmarksmissing'] = '%s marks (%s) do not exist anymore!';
	$lang['strmissingmarkgroup'] = 'The mark does not exist anymore in the tables group "%s"!';
	$lang['strmissingmarkgroups'] = 'The mark does not exist anymore in %s tables groups (%s)!';
	$lang['stradoreturncode'] = 'Return code from the ADO layer = %s.';

	// Group drop
	$lang['strdropagroup'] = 'E-Maj: Drop a tables group';
	$lang['strconfirmdropgroup'] = 'Are you sure you want to drop the tables group "%s"?';
	$lang['strdropgroupok'] = 'The tables group "%s" has been dropped.';
	$lang['strdropgrouperr'] = 'Error while dropping tables group "%s".';

	// Groups drop
	$lang['strdropgroups'] = 'E-Maj: Drop tables groups';
	$lang['strconfirmdropgroups'] = 'Are you sure you want to drop the tables groups "%s"?';
	$lang['strdropgroupsok'] = 'The tables groups "%s" have been dropped.';
	$lang['strdropgroupserr'] = 'Error while dropping tables groups "%s".';

	// Group alter
	$lang['straltergroups'] = 'E-Maj: Apply configuration changes';
	$lang['stralteraloggingroup'] = 'The group "%s" is in LOGGING state. You can specify a mark name.';
	$lang['strconfirmaltergroup'] = 'Are you sure you want to apply the configuration changes for the tables group "%s"?';
	$lang['strcantaltergroup'] = 'Applying the configuration changes for the group "%s" would generate actions that cannot be executed on LOGGING group. Stop the group before altering it.';
	$lang['straltergroupok'] = 'The configuration changes for the tables group "%s" have been applied.';
	$lang['straltergrouperr'] = 'Error during tables group "%s" configuration change!';

	// Groups alter
	$lang['stralterallloggingroups'] = 'The groups "%s" are in LOGGING state. You can specify a mark name.';
	$lang['strconfirmaltergroups'] = 'Are you sure you want to apply the configuration changes for the tables groups "%s"?';
	$lang['straltergroupsok'] = 'The configuration changes for the tables groups "%s" have been applied.';
	$lang['straltergroupserr'] = 'Error during tables groups "%s" configuration change!';

	// Group comment
	$lang['strcommentagroup'] = 'E-Maj: Record a comment for a tables group';
	$lang['strcommentgroup'] = 'Enter, modify or erase the comment for tables group "%s".';
	$lang['strcommentgroupok'] = 'The comment for the tables group "%s" has been recorded.';
	$lang['strcommentgrouperr'] = 'Error while commenting the tables group "%s".';

	// Group start
	$lang['strstartagroup'] = 'E-Maj: Start a tables group';
	$lang['strconfirmstartgroup'] = 'Starting the tables group "%s"';
	$lang['strinitmark'] = 'Initial mark';
	$lang['stroldlogsdeletion'] = 'Old logs deletion';
	$lang['strstartgroupok'] = 'The tables group "%s" is started with the mark "%s".';
	$lang['strstartgrouperr'] = 'Error while starting tables group "%s".';
	$lang['strstartgrouperr2'] = 'Error while starting tables group "%s" with the mark "%s".';

	// Groups start
	$lang['strstartgroups'] = 'E-Maj: Start tables groups';
	$lang['strconfirmstartgroups'] = 'Starting the tables groups "%s"';
	$lang['strstartgroupsok'] = 'The tables groups "%s" are started with the mark "%s".';
	$lang['strstartgroupserr'] = 'Error while starting the tables groups "%s".';
	$lang['strstartgroupserr2'] = 'Error while starting the tables groups "%s" with the mark "%s".';

	// Group stop
	$lang['strstopagroup'] = 'E-Maj: Stop a tables group';
	$lang['strconfirmstopgroup'] = 'Stopping the tables group "%s"';
	$lang['strstopmark'] = 'Final mark';
	$lang['strforcestop'] = 'Forced stop (in case of problem only)';
	$lang['strstopgroupok'] = 'The tables group "%s" has been stopped.';
	$lang['strstopgrouperr'] = 'Error while stopping the tables group "%s".';
	$lang['strstopgrouperr2'] = 'Error while stopping the tables group "%s" with the mark "%s".';

	// Groups stop
	$lang['strstopgroups'] = 'E-Maj: Stop tables groups';
	$lang['strconfirmstopgroups'] = 'Stopping the tables groups "%s"';
	$lang['strstopgroupsok'] = 'The tables groups "%s" have been stopped.';
	$lang['strstopgroupserr'] = 'Error while stopping the tables groups "%s".';
	$lang['strstopgroupserr2'] = 'Error while stopping the tables groups "%s" with the mark "%s".';

	// Group reset
	$lang['strresetagroup'] = 'E-Maj: Reset a tables group';
	$lang['strconfirmresetgroup'] = 'Are you sure you want to reset the tables group "%s"?';
	$lang['strresetgroupok'] = 'The tables group "%s" has been reset.';
	$lang['strresetgrouperr'] = 'Error while resetting the tables group "%s".';

	// Groups reset
	$lang['strresetgroups'] = 'E-Maj: Reset tables groups';
	$lang['strconfirmresetgroups'] = 'Are you sure you want to reset the tables groups "%s"?';
	$lang['strresetgroupsok'] = 'The tables group "%s" have been reset.';
	$lang['strresetgroupserr'] = 'Error while resetting the tables groups "%s".';

	// Group protect
	$lang['strprotectgroupok'] = 'The tables group "%s" is now protected against rollbacks.';
	$lang['strprotectgrouperr'] = 'Error while protecting the tables group "%s".';

	// Groups protect
	$lang['strprotectgroupsok'] = 'The tables groups "%s" are now protected against rollbacks.';
	$lang['strprotectgroupserr'] = 'Error while protecting the tables groups "%s".';

	// Group unprotect
	$lang['strunprotectgroupok'] = 'The tables group "%s" is now unprotected.';
	$lang['strunprotectgrouperr'] = 'Error while unprotecting the tables group "%s".';

	// Groups unprotect
	$lang['strunprotectgroupsok'] = 'The tables groups "%s" are now unprotected.';
	$lang['strunprotectgroupserr'] = 'Error while unprotecting the tables groups "%s".';

	// Set Mark for one group
	$lang['strsetamark'] = 'E-Maj: Set a mark';
	$lang['strconfirmsetmarkgroup'] = 'Setting a mark for the tables group "%s":';
	$lang['strsetmarkgroupok'] = 'The mark "%s" has been set for the tables group "%s".';
	$lang['strsetmarkgrouperr'] = 'Error while setting a for the tables group "%s".';
	$lang['strsetmarkgrouperr2'] = 'Error while setting the mark "%s" for the tables group "%s".';

	// Set Mark for several groups
	$lang['strconfirmsetmarkgroups'] = 'Setting a mark for the tables groups "%s":';
	$lang['strsetmarkgroupsok'] = 'The mark "%s" has been set for the tables groups "%s".';
	$lang['strsetmarkgroupserr'] = 'Error while setting a mark for the tables groups "%s".';
	$lang['strsetmarkgroupserr2'] = 'Error while setting the mark "%s" for the tables groups "%s".';

	// Group rollback
	$lang['strrlbkagroup'] = 'E-Maj: Rollback a tables group';
	$lang['strconfirmrlbkgroup'] = 'Rollbacking the tables group "%s" to the mark "%s"';
	$lang['strunknownestimate'] = 'unknown';
	$lang['strdurationminutesseconds'] = '%s min %s s';
	$lang['strdurationhoursminutes'] = '%s h %s min';
	$lang['strdurationovertendays'] = '> 10 days';
	$lang['strselectmarkgroup'] = 'Rollbacking the tables group "%s" to the mark: ';
	$lang['strrlbkthenmonitor'] = 'Rollback and monitor';
	$lang['strcantrlbkinvalidmarkgroup'] = 'The mark "%s" is not valid.';
	$lang['strreachaltergroup'] = 'Rollbacking the tables group "%s" to the mark "%s" would reach a point in time prior alter_group operations. Please confirm the rollback.';
	$lang['strautorolledback'] = 'Automatically rolled back?';
	$lang['strrlbkgrouperr'] = 'Error while rollbacking the tables group "%s".';
	$lang['strrlbkgrouperr2'] = 'Error while rollbacking the tables group "%s" to the mark "%s".';
	$lang['strestimrlbkgrouperr'] = 'Error while estimating the rollback duration for the tables group "%s" to the mark "%s".';
	$lang['strbadconfparam'] = 'Error: asynchronous rollback is not possible anymore. Check the dblink extension exists and both the pathname of the psql command (%s) and the temporary directory (%s) configuration parameters are correct.';
	$lang['strasyncrlbkstarted'] = 'Rollback #%s started.';
	$lang['strrlbkgroupreport'] = 'Rollback execution report for the tables group "%s" to the mark "%s"';

	// Groups rollback
	$lang['strrlbkgroups'] = 'E-Maj: Rollback tables groups';
	$lang['strselectmarkgroups'] = 'Rollbacking the tables groups "%s" to the mark: ';
	$lang['strnomarkgroups'] = 'No common mark for the tables groups "%s" can be used for a rollback.';
	$lang['strcantrlbkinvalidmarkgroups'] = 'Rollbacking the tables groups "%s" is not possible. The mark "%s" is not valid.';
	$lang['strreachaltergroups'] = 'Rollbacking the tables groups "%s" to the mark "%s" would reach a point in time prior alter_group operations. Please confirm the rollback.';
	$lang['strrlbkgroupserr'] = 'Error while rollbacking tables groups "%s".';
	$lang['strrlbkgroupserr2'] = 'Error while rollbacking tables groups "%s" to mark "%s".';
	$lang['strestimrlbkgroupserr'] = 'Error while estimating the rollback duration for the tables groups "%s" to the mark "%s".';
	$lang['strrlbkgroupsreport'] = 'Rollback execution report for the tables groups "%s" to the mark "%s"';

	// Elementary alter group actions previously executed, reported at rollback time 
	$lang['stralteredremovetbl'] = 'The table "%s.%s" has been removed from the tables group "%s"';
	$lang['stralteredremoveseq'] = 'The sequence "%s.%s" has been removed from the tables group "%s"';
	$lang['stralteredrepairtbl'] = 'E-Maj objects for the table "%s.%s" have been repaired';
	$lang['stralteredrepairseq'] = 'E-Maj objects for the sequence "%s.%s" have been repaired';
	$lang['stralteredchangetbllogschema'] = 'The E-Maj log schema for the table "%s.%s" has been changed';
	$lang['stralteredchangetblnamesprefix'] = 'The E-Maj names prefix for the table "%s.%s" has been changed';
	$lang['stralteredchangetbllogdatatsp'] = 'The tablespace for the log data files of the table "%s.%s" has been changed';
	$lang['stralteredchangetbllogindextsp'] = 'The tablespace for the log index files of the table "%s.%s" has been changed';
	$lang['stralteredchangerelpriority'] = 'The E-Maj priority for the table "%s.%s" has been changed';
	$lang['stralteredchangeignoredtriggers'] = 'the triggers to be ignored at rollback for the table "%s.%s" have been changed';
	$lang['stralteredmovetbl'] = 'The table "%s.%s" has been moved from the tables groupe "%s" to the tables group "%s"';
	$lang['stralteredmoveseq'] = 'The sequence "%s.%s" has been moved from the tables groupe "%s" to the tables group "%s"';
	$lang['stralteredaddtbl'] = 'The table "%s.%s" has been added to the tables group "%s"';
	$lang['stralteredaddseq'] = 'The sequence "%s.%s" has been added to the tables group "%s"';

	// Protect mark
	$lang['strprotectmarkok'] = 'The mark "%s" for the tables group "%s" is now protected against rollbacks.';
	$lang['strprotectmarkerr'] = 'Error while protecting the mark "%s" for the tables group "%s".';

	// Unprotect mark
	$lang['strunprotectmarkok'] = 'The mark "%s" for the tables group "%s" is now unprotected.';
	$lang['strunprotectmarkerr'] = 'Error while unprotecting the mark "%s" for the tables group "%s".';

	// Comment mark
	$lang['strcommentamark'] = 'E-Maj: Record a comment for a mark';
	$lang['strcommentmark'] = 'Enter, modify or erase the comment for the mark "%s" of the tables group "%s"';
	$lang['strcommentmarkok'] = 'The comment for the mark "%s" of the tables group "%s" has been recorded.';
	$lang['strcommentmarkerr'] = 'Error while commenting the mark "%s" of the tables group "%s".';

	// Mark renaming
	$lang['strrenameamark'] = 'E-Maj : Rename a mark';
	$lang['strconfirmrenamemark'] = 'Renaming the mark "%s" of the tables group "%s"';
	$lang['strnewnamemark'] = 'New name';
	$lang['strrenamemarkok'] = 'The mark "%s" of the tables group "%s" has been renamed into "%s".';
	$lang['strrenamemarkerr'] = 'Error while renaming the mark "%s" of the tables group "%s" into "%s".';
	$lang['strrenamemarkerr2'] = 'Error while renaming the mark "%s" of the tables group "%s" into "%s".';

	// Mark deletion
	$lang['strdeleteamark'] = 'E-Maj: Delete a mark';
	$lang['strconfirmdeletemark'] = 'Are you sure you want to delete the mark "%s" for the tables group "%s"?';
	$lang['strdeletemarkok'] = 'The mark "%s" has been deleted for the tables group "%s".';
	$lang['strdeletemarkerr'] = 'Error while deleting the mark "%s" of the tables group "%s".';

	// Marks deletion
	$lang['strdeletemarks'] = 'E-Maj: Delete marks';
	$lang['strconfirmdeletemarks'] = 'Are you sure you want to delete these %s marks for the tables group "%s"?';
	$lang['strdeletemarksok'] = 'The %s marks have been deleted for the tables group "%s".';
	$lang['strdeletemarkserr'] = 'Error while deleting marks "%s" of the tables group "%s".';

	// Marks before mark deletion
	$lang['strdelmarksprior'] = 'E-Maj: Delete marks';
	$lang['strconfirmdelmarksprior'] = 'Are you sure you want to delete all marks and logs preceeding the mark "%s" for the tables group "%s"?';
	$lang['strdelmarkspriorok'] = 'All (%s) marks preceeding mark "%s" have been deleted for the tables group "%s".';
	$lang['strdelmarkspriorerr'] = 'Error while deleting all marks preceeding mark "%s" for the tables group "%s".';

	// Statistics
	$lang['strchangesgroup'] = 'Recorded changes for the tables group "%s"';
	$lang['strcurrentsituation'] = 'Current state';
	$lang['strestimatetables'] = 'Estimate tables';
	$lang['strestimatesequences'] = 'Estimate sequences';
	$lang['strdetailtables'] = 'Detail tables';
	$lang['strdetailedlogstatwarning'] = 'Scanning the log tables needed to get detailed statistics may take a long time. Although less detailed and precise, the changes number estimate is quicker because it only uses the log sequence values recorded at each mark.';
	$lang['strchangestblbetween'] = 'Table changes between marks "%s" and "%s"';
	$lang['strchangestblsince'] = 'Table changes since mark "%s"';
	$lang['strtblingroup'] = 'Tables in group';
	$lang['strtblwithchanges'] = 'Tables with changes';
	$lang['strchangesseqbetween'] = 'Sequence changes between marks "%s" and "%s"';
	$lang['strchangesseqsince'] = 'Sequence changes since mark "%s"';
	$lang['strseqingroup'] = 'Sequences in group';
	$lang['strseqwithchanges'] = 'Sequences with changes';
	$lang['strstatincrements'] = 'Increments';
	$lang['strstatstructurechanged'] = 'Structure changed ?';
	$lang['strstatverb'] = 'SQL verb';
	$lang['strnbinsert'] = 'INSERT';
	$lang['strnbupdate'] = 'UPDATE';
	$lang['strnbdelete'] = 'DELETE';
	$lang['strnbtruncate'] = 'TRUNCATE';
	$lang['strnbrole'] = 'Roles';
	$lang['strlogsessionwarning'] = 'This marks range covers several log sessions. Data changes may have been not recorded.';
	$lang['strstatrows'] = 'Row changes';
	$lang['strbrowsechanges'] = 'Browse changes';

	// Dump changes SQL generation
	$lang['strsqlgentitle'] = 'Generate the changes dump SQL statement';
	$lang['strsqlgenmarksinterval'] = 'Marks interval';
	$lang['strsqlgennopk'] = 'The table has no primary key. Consolidated views are not possible.';
	$lang['strsqlgenconsolidation'] = 'Consolidation';
	$lang['strsqlgenconsonone'] = 'None';
	$lang['strsqlgenconsopartial'] = 'Partial';
	$lang['strsqlgenconsofull'] = 'Full';
	$lang['strsqlgenconsohelp'] = 'Without consolidation, all elementary changes recorded into log tables for the selected marks frame are returned. With a (partial or full) consolidation, only the initial and the final states of each primary key are returned. With a full consolidation, no data is returned when both initial and final states are strictly equal.';
	$lang['strsqlgenverbs'] = 'SQL verbs';
	$lang['strsqlgenverbshelp'] = 'Without consolidation, it is possible to filter returned changes on SQL verbs.';
	$lang['strsqlgenknownroles'] = 'Known roles:';
	$lang['strsqlgenroleshelp'] = 'Without consolidation, it is possible to filter returned changes on roles having generated the changes. If filled, the field must contain a comma separated roles list.';
	$lang['strsqlgentechcols'] = 'E-Maj technical columns';
	$lang['strsqlgentechcolshelp'] = 'In the SQl statement result, most E-Maj technical columns can be masked.';
	$lang['strsqlgencolsorder'] = 'Columns order';
	$lang['strsqlgencolsorderlog'] = 'As the log table';
	$lang['strsqlgencolsorderpk'] = 'Primary key ahead';
	$lang['strsqlgencolsorderhelp'] = 'In the SQl statement result, either returned columns are in the same order as in the log table, or the primary key columns (those of the application table + emaj_tuple) are ahead.';
	$lang['strsqlgenroworder'] = 'Rows order';
	$lang['strsqlgenrowordertime'] = 'Chronological';
	$lang['strsqlgenroworderhelp'] = 'In the SQl statement result, rows can be returned either in the chronological recording order or in primary keys order.';
	$lang['strsqlgenerate'] = 'Generate SQL';

	// Group's content
	$lang['strgroupcontent'] = 'Current content of the tables group "%s"';
	$lang['stremptygroup'] = 'The tables group "%s" is currently empty.';
	$lang['strpriority'] = 'Priority';
	$lang['strcurrentlogtable'] = 'Current log table';

	// Group's history
	$lang['strgrouphistory'] = 'Tables group "%s" history';
	$lang['stremajnohistory'] = 'There is no history to display for this group.';
	$lang['strgrouphistoryorder'] = 'Most recent group creations, group drops and log sessions are placed ahead on the sheet.';
	$lang['strnblogsessions'] = 'Log sessions';
	$lang['strgroupcreate'] = 'Group creation';
	$lang['strgroupdrop'] = 'Group drop';
	$lang['strdeletedlogsessions'] = 'Some deleted log sessions';

// Old Groups content setup

	// Configure groups
	$lang['strappschemas'] = 'Application schemas';
	$lang['strunknownobject'] = 'This object is referenced in the emaj_group_def table but is not created.';
	$lang['strunsupportedobject'] = 'This object type is not supported by E-Maj (unlogged table, table with OIDS, partition table,...).';
	$lang['strtblseqofschema'] = 'Tables and sequences in schema "%s"';
	$lang['strlogschemasuffix'] = 'Log schema suffix';
	$lang['strnamesprefix'] = 'Objects name prefix';
	$lang['strspecifytblseqtoassign'] = 'Specify at least one table or sequence to assign';
	$lang['strtblseqyetgroup'] = 'Error, "%s.%s" is already assigned to a tables group.';
	$lang['strtblseqbadtype'] = 'Error, type of "%s.%s" is not supported by E-Maj.';
	$lang['strassigntblseq'] = 'E-Maj: Assign tables / sequences to a tables group';
	$lang['strconfirmassigntblseq'] = 'Assign:';
	$lang['strfromgroup'] = 'from the group "%s"';
	$lang['strenterlogschema'] = 'Log schema suffix';
	$lang['strlogschemahelp'] = 'A log schema contains log tables, sequences and functions. The default log schema is \'emaj\'. If a suffix is defined for the table, its objects will be hosted in the schema \'emaj\' + suffix.';
	$lang['strenternameprefix'] = 'E-Maj objects name prefix';
	$lang['strnameprefixhelp'] = 'By default, log objects names are prefixed by <schema>_<table>. But another prefix can be defined for the table. It must be unique in the database.';
	$lang['strspecifytblseqtoupdate'] = 'Specify at least one table or sequence to update';
	$lang['strupdatetblseq'] = 'E-Maj: Update properties of a table / sequence in a tables group';
	$lang['strspecifytblseqtoremove'] = 'Specify at least one table or sequence to remove';
	$lang['strtblseqnogroup'] = 'Error, "%s.%s" is not currently assigned to any tables group.';
	$lang['strremovetblseq'] = 'E-Maj: Remove tables / sequences from tables groups';
	$lang['strconfirmremove1tblseq'] = 'Are you sure you want to remove %s from the tables group "%s"?';
	$lang['strconfirmremovetblseq'] = 'Are you sure you want to remove:';
	$lang['strmodifygroupok'] = 'The configuration change is recorded. It will take effect when the concerned tables groups will be (re)created or when the configuration changes will be applied for these groups.';
	$lang['strmodifygrouperr'] = 'An error occured while recording the tables groups configuration change.';

// Schemas

	// Schemas list
	$lang['strschema'] = 'Schema';
	$lang['strschemas'] = 'Schemas';
	$lang['strallschemas'] = 'All schemas';
	$lang['strnoschemas'] = 'No schemas found.';

	// Tables
	$lang['strtableslist'] = 'Schema "%s" tables';
	$lang['strnotables'] = 'No tables found.';
	$lang['strestimatedrowcount'] = 'Estimated row count';
	$lang['strtblproperties'] = 'Table "%s.%s" properties';
	$lang['strtblcontent'] = 'Table "%s.%s" content';
	$lang['stremajlogtable'] = 'The table is an E-Maj log table.';
	$lang['strinternaltable'] = 'The table is an internal E-Maj table.';
	$lang['strtblnogroupownership'] = 'The table does not currently belong to any tables group.';

	// Sequences
	$lang['strsequenceslist'] = 'Schema "%s" sequences';
	$lang['strnosequences'] = 'No sequences found.';
	$lang['strseqproperties'] = 'Sequence "%s.%s" properties';
	$lang['strlastvalue'] = 'Last value';
	$lang['strincrement'] = 'Increment';
	$lang['strstartvalue'] = 'Start value';
	$lang['strmaxvalue'] = 'Max value';
	$lang['strminvalue'] = 'Min value';
	$lang['strcachesize'] = 'Cache size';
	$lang['strlogcount'] = 'Log count';
	$lang['strcancycle'] = 'Can cycle?';
	$lang['striscalled'] = 'Will increment last value before returning next value (is_called)?';
	$lang['stremajlogsequence'] = 'The sequence is an E-Maj log sequence.';
	$lang['strinternalsequence'] = 'The sequence is an internal E-Maj sequence.';
	$lang['strseqnogroupownership'] = 'The sequence does not currently belong to any tables group.';

	// Dynamic groups content management: common messages
	$lang['strlogdattsp'] = 'Log tables tablespace';
	$lang['strlogidxtsp'] = 'Log indexes tablespace';
	$lang['strdefaulttsp'] = '<default tablespace>';
	$lang['strthetable'] = 'the "%s.%s" table';
	$lang['strthesequence'] = 'the "%s.%s" sequence';
	$lang['strthetblseqingroup'] = '%s (group %s)';
	$lang['strenterpriority'] = 'Processing priority';
	$lang['strpriorityhelp'] = 'Tables are processed in priority ascending order, and in names alphabetic order if no priority is defined.';
	$lang['strenterlogdattsp'] = 'Log tables tablespace';
	$lang['strenterlogidxtsp'] = 'Log indexes tablespace';
	$lang['strmarkiflogginggroup'] = 'Mark (if a logging group)';

	// Dynamic groups content management: generic error messages
	$lang['strschemamissing'] = 'The schema "%s" does not exist anymore.';
	$lang['strtablemissing'] = 'The table "%s.%s" does not exist anymore.';
	$lang['strsequencemissing'] = 'The sequence "%s.%s" does not exist anymore.';
	$lang['strtablesmissing'] = '%s tables (%s) do not exist anymore.';
	$lang['strsequencesmissing'] = '%s sequences (%s) do not exist anymore.';

	// Assign tables
	$lang['strassigntable'] = 'E-Maj: Assign tables to a tables group';
	$lang['strconfirmassigntable'] = 'Assign the table "%s.%s"';
	$lang['strconfirmassigntables'] = 'Assign these %s tables of schema "%s":';
	$lang['strtableshavetriggers'] = 'This tables set has %s triggers. They will be automatically disabled at E-Maj rollback time. You will be able to change this behaviour with the "Triggers" tab.';
	$lang['strassigntableok'] = '%s table has been assigned to the tables group %s.';
	$lang['strassigntablesok'] = '%s tables have been assigned to the tables group %s.';
	$lang['strassigntableerr'] = 'Error while assigning the table "%s.%s".';
	$lang['strassigntableerr2'] = 'Error while assigning the table "%s.%s" to the tables group "%s".';
	$lang['strassigntableserr'] = 'Error while assigning these %s tables of schema "%s".';
	$lang['strassigntableserr2'] = 'Error while assigning these %s tables of schema "%s" to the tables group "%s".';

	// Move tables
	$lang['strmovetable'] = 'E-Maj: Move tables to another tables group';
	$lang['strconfirmmovetable'] = 'Move the table "%s.%s" from its tables group "%s".';
	$lang['strconfirmmovetables'] = 'Move these %s tables of schema "%s":';
	$lang['strmovetableok'] = '%s table has been moved to the tables group %s.';
	$lang['strmovetablesok'] = '%s tables have been moved to the tables group %s.';
	$lang['strmovetableerr'] = 'Error while moving the table "%s.%s".';
	$lang['strmovetableerr2'] = 'Error while moving the table "%s.%s" from tables group "%s" to tables group "%s".';
	$lang['strmovetableserr'] = 'Error while moving these %s tables of schema "%s".';
	$lang['strmovetableserr2'] = 'Error while moving these %s tables of schema "%s" from their tables group to the tables group "%s".';

	// Modify table
	$lang['strmodifytable'] = 'E-Maj: Modify tables E-Maj properties';
	$lang['strconfirmmodifytable'] = 'Modify the E-Maj properties of the table "%s.%s".';
	$lang['strconfirmmodifytables'] = 'Modify the E-Maj properties of these %s tables from schema "%s":';
	$lang['strmodifytablesok'] = 'E-Maj properties for %s tables have been modified.';
	$lang['strmodifytableerr'] = 'Error while modifying E-Maj properties of the table "%s.%s".';
	$lang['strmodifytableserr'] = 'Error while modifying E-Maj properties of these %s tables from schema "%s":';

	// Remove tables
	$lang['strremovetable'] = 'E-Maj : Remove tables from their tables group';
	$lang['strconfirmremovetable'] = 'Remove the table "%s.%s" from its tables group "%s".';
	$lang['strconfirmremovetables'] = 'Remove these %s tables of schema "%s" from their tables group:';
	$lang['strremovetableok'] = '%s table has been removed from its tables group.';
	$lang['strremovetablesok'] = '%s tables have been removed from their tables group.';
	$lang['strremovetableerr'] = 'Error while removing the table "%s.%s" from its tables group "%s".';
	$lang['strremovetableserr'] = 'Error while removing these %s tables of schema "%s" from their tables group.';

	// Assign sequences
	$lang['strassignsequence'] = 'E-Maj: Assign sequences to a tables group';
	$lang['strconfirmassignsequence'] = 'Assign the sequence "%s.%s"';
	$lang['strconfirmassignsequences'] = 'Assign these %s sequences of schema "%s":';
	$lang['strassignsequenceok'] = '%s sequence has been assigned to the tables group %s.';
	$lang['strassignsequencesok'] = '%s sequences have been assigned to the tables group %s.';
	$lang['strassignsequenceerr'] = 'Error while assigning the sequence "%s.%s".';
	$lang['strassignsequenceerr2'] = 'Error while assigning the sequence "%s.%s" to the tables group "%s".';
	$lang['strassignsequenceserr'] = 'Error while assigning these %s sequences of schema "%s".';
	$lang['strassignsequenceserr2'] = 'Error while assigning these %s sequences of schema "%s" to the tables group "%s".';

	// Move sequences
	$lang['strmovesequence'] = 'E-Maj: Move sequences to another tables group';
	$lang['strconfirmmovesequence'] = 'Move the sequence "%s.%s" from its tables group "%s".';
	$lang['strconfirmmovesequences'] = 'Move these %s sequences from schema "%s":';
	$lang['strmovesequenceok'] = '%s sequence has been moved to the tables group %s.';
	$lang['strmovesequencesok'] = '%s sequences have been moved to the tables group %s.';
	$lang['strmovesequenceerr'] = 'Error while moving the sequence "%s.%s".';
	$lang['strmovesequenceerr2'] = 'Error while moving the sequence "%s.%s" from tables group "%s" to tables group "%s".';
	$lang['strmovesequenceserr'] = 'Error while moving these %s sequences of schema "%s".';
	$lang['strmovesequenceserr2'] = 'Error while moving these %s sequences of schema "%s" from their tables group to the tables group "%s".';

	// Remove sequences
	$lang['strremovesequence'] = 'E-Maj : Remove sequences from their tables group';
	$lang['strconfirmremovesequence'] = 'Remove the sequence "%s.%s" from its tables group "%s".';
	$lang['strconfirmremovesequences'] = 'Remove these %s sequences from schema "%s" :';
	$lang['strremovesequenceok'] = '%s sequence has been removed from its tables group.';
	$lang['strremovesequencesok'] = '%s sequences have been removed from their tables group.';
	$lang['strremovesequenceerr'] = 'Error while removing the sequence "%s.%s" from its tables group "%s".';
	$lang['strremovesequenceserr'] = 'Error while removing these %s sequences of schema "%s" from their tables group.';

// Triggers

	// Triggers list
	$lang['strtrigger'] = 'Trigger';
	$lang['strtriggers'] = 'Triggers';
	$lang['strnotriggerontable'] = 'The table has no trigger.';
	$lang['strapptriggers'] = 'Application triggers';
	$lang['strapptriggershelp'] = 'List of triggers in the database, excluding system and E-Maj triggers.';
	$lang['strnoapptrigger'] = 'No application trigger in the database.';
	$lang['strexecorder'] = 'Exec. order';
	$lang['strtriggeringevent'] = 'Triggering event';
	$lang['strcalledfunction'] = 'Called function';
	$lang['strisemaj'] = 'E-Maj?';
	$lang['strisautodisable'] = 'Auto disable';
	$lang['strisautodisablehelp'] = 'Indicate whether the trigger is automatically disabled at E-maj rollback time (default = ON = Yes)';
	$lang['strtriggerautook'] = 'The trigger %s for the table %s.%s will be automatically disabled at E-Maj rollbacks time.';
	$lang['strtriggernoautook'] = 'The trigger %s for the table %s.%s will NOT be automatically disabled at E-Maj rollbacks time.';
	$lang['strtriggerprocerr'] = 'An error occured while processing the trigger %s of the table %s.%s.';
	$lang['strnoselectedtriggers'] = 'No selected trigger.';
	$lang['strtriggersautook'] = '%s new triggers will be automatically disabled at E-Maj rollbacks time.';
	$lang['strtriggersnoautook'] = '%s new triggers will NOT be automatically disabled at E-Maj rollbacks time.';
	$lang['strorphantriggersexist'] = 'The table that contains the identifiers of triggers not to be automatically disabled at E-Maj rollbacks (emaj_ignored_app_trigger) references schemas, tables or triggers that do not exist anymore.';
	$lang['strtriggersremovedok'] = '%s triggers have been removed.';

// E-Maj Rollbacks

	// Rollback activity
	$lang['strrlbkid'] = 'Rlbk Id.';
	$lang['strrlbkstart'] = 'Rollback start';
	$lang['strrlbkend'] = 'Rollback end';
	$lang['strduration'] = 'Duration';
	$lang['strislogged'] = 'Logged ?';
	$lang['strnbsession'] = 'Sessions';
	$lang['strcurrentduration'] = 'Current duration';
	$lang['strglobalduration'] = 'Global duration';
	$lang['strplanningduration'] = 'Planning duration';
	$lang['strlockingduration'] = 'Locking duration';
	$lang['strestimremaining'] = 'Estimated remaining';
	$lang['strpctcompleted'] = '% completed';
	$lang['strinprogressrlbk'] = 'In progress E-Maj rollbacks';
	$lang['strrlbkmonitornotavailable'] = 'In progress rollbacks monitoring is not available.';
	$lang['strcompletedrlbk'] = 'Completed E-Maj rollbacks';
	$lang['strnbtabletoprocess'] = 'Tables to process';
	$lang['strnbseqtoprocess'] = 'Sequences to process';
	$lang['strnorlbk'] = 'No E-Maj rollback.';
	$lang['strconsolidablerlbk'] = 'Consolidable E-Maj logged rollbacks';
	$lang['strtargetmark'] = 'Target mark';
	$lang['strendrollbackmark'] = 'End rollback mark';
	$lang['strnbintermediatemark'] = 'Intermediate marks';
	$lang['strconsolidate'] = 'Consolidate';

	// Consolidate an E-Maj rollback
	$lang['strconsolidaterlbk'] = 'Consolidate a logged rollback';
	$lang['strconfirmconsolidaterlbk'] = 'Are you sure you want to consolidate the rollback ended with the mark "%s" of the tables group "%s"?';
	$lang['strconsolidaterlbkok'] = 'The rollback ended with the mark "%s" of the tables group "%s" has been consolidated.';
	$lang['strconsolidaterlbkerr'] = 'Error while consolidating the rollback ended by the mark "%s" of the tables group "%s"!';

	// E-Maj rollback details
	$lang['strrlbkprogress'] = 'Rollback progress';
	$lang['strrlbksessions'] = 'Sessions';
	$lang['strrlbksession'] = 'Session';
	$lang['strrlbkexecreport'] = 'Execution report';
	$lang['strrlbkplanning'] = 'Planning';
	$lang['strrlbkplanninghelp'] = 'The main elementary steps of the E-Maj Rollback execution. Are not included: the planning and the locks set on tables at the beginning of the operation, and, for emaj version < 4.2, the sequences processing at the end of the operation.';
	$lang['strrlbkestimmethodhelp'] = 'At planning time, the duration of each step is estimated, using statistics of similar steps in the past, with the same order of magnitude of quantity to process (STAT+), or other orders of magnitude (STAT), or, by default, the extension parameters (PARAM). The Q column evaluates the duration estimates quality for steps longer than 10ms.';
	$lang['strnorlbkstep'] = 'No elementary step for this rollback.';
	$lang['strrlbkstep'] = 'Step';
	$lang['strabbrquality'] = 'Q';
	$lang['strmethod'] = 'Method';
	$lang['strrlbksequences'] = 'Rollback sequences';
	$lang['strrlbkdisapptrg'] = 'Disable the trigger %s';
	$lang['strrlbkdislogtrg'] = 'Disable the log trigger';
	$lang['strrlbksetalwaysapptrg'] = 'Set the trigger %s as ALWAYS';
	$lang['strrlbkdropfk'] = 'Drop the foreign key %s';
	$lang['strrlbksetfkdef'] = 'Set the foreign key %s DEFFERED';
	$lang['strrlbkrlbktable'] = 'Rollback the table';
	$lang['strrlbkdeletelog'] = 'Delete logs';
	$lang['strrlbksetfkimm'] = 'Set the foreign key %s IMMEDIATE';
	$lang['strrlbkaddfk'] = 'Recreate the foreign key %s';
	$lang['strrlbkenaapptrg'] = 'Re-enable the trigger %s';
	$lang['strrlbksetlocalapptrg'] = 'Set the trigger %s as LOCAL';
	$lang['strrlbkenalogtrg'] = 'Re-enable the log trigger';

	// Comment an E-Maj rollback
	$lang['strcommentarollback'] = 'E-Maj: Record a comment for a rollback';
	$lang['strcommentrollback'] = 'Enter, modify or erase the comment for the rollback %s';
	$lang['strcommentrollbackok'] = 'The comment for the rollback %s has been recorded.';
	$lang['strcommentrollbackerr'] = 'Error during comment recording for the rollback %s!';

// Activity

	$lang['strchangesactivity'] = 'E-Maj changes activity';

	// Form
	$lang['strincluderegexp'] = 'Include regexp';
	$lang['strincluderegexphelp'] = 'Regular expression allowing to select groups, tables or sequences to filter. An empty string equals .* and includes all objects. For tables and sequences, names are schema qualified. See the PostgreSQL documentation for available regexp syntaxes.';
	$lang['strexcluderegexp'] = 'Exclude regexp';
	$lang['strexcluderegexphelp'] = 'Regular expression allowing to exclude groups, tables or sequences to filter. An empty string means no exclusion. For tables and sequences, names are schema qualified. See the PostgreSQL documentation for available regexp syntaxes.';
	$lang['strmaxrows'] = 'Maximum #rows';
	$lang['strmaxrowshelp'] = 'Defines the maximum number of rows to display for groups, tables and sequences lists, these rows being sorted by changes either since the latest mark or since the previous display, in descending order. Value 0 deletes the corresponding list.';
	$lang['strmainsortcriteria'] = 'Main sort criteria';
	$lang['strmainsortcriteriahelp'] = 'Defines the main sort criteria for the displayed groups, tables and sequences. When changes numbers are equal, rows are sorted by groups, tables and sequences names, groups and sequences names being schema qualified. ';
	$lang['strchangessince'] = '#changes since';
	$lang['strlatestmark'] = 'Latest mark';
	$lang['strpreviousdisplay'] = 'Previous display';

	// Display
	$lang['strerrortrapped'] = 'An error has been trapped while reading sequences.';
	$lang['strglobalactivity'] = 'Global activity';
	$lang['strlogginggroupstitle'] = 'Logging groups (%s/%s)';
	$lang['strnogroupselected'] = 'No tables group selected.';
	$lang['strtablesinlogginggroups'] = 'Tables in logging groups (%s/%s)';
	$lang['strnotableselected'] = 'No table selected.';
	$lang['strsequencesinlogginggroups'] = 'Sequences in logging groups (%s/%s)';
	$lang['strnosequenceselected'] = 'No sequence selected.';
	$lang['strsincelatestmark'] = 'Since latest mark';
	$lang['strsincepreviousdisplay'] = 'Since previous display';
	$lang['strchangespersecond'] = 'Changes / sec';

// E-Maj environment

	// Versions
	$lang['strextnotavailable'] = 'The E-Maj software is not installed on this PostgreSQL instance.';
	$lang['strextnotcreated'] = 'The emaj extension is not created in this database.';
	$lang['strcontactdba'] = 'Contact your database administrator.';
	$lang['strnogrant'] = 'Your connection role has no E-Maj rights. Use another role or contact your database administrator.';
	$lang['strcharacteristics'] = 'E-Maj environment characteristics';
	$lang['strversions'] = 'Versions';
	$lang['strpgversion'] = 'PostgreSQL version: ';
	$lang['strversion'] = 'E-Maj version: ';
	$lang['strasextension'] = 'installed as extension';
	$lang['strasscript'] = 'installed by script';
	$lang['strtooold'] = 'Sorry, this E-Maj version (%s) is too old. The minimum version supported by Emaj_web is %s.';
	$lang['strversionmorerecent'] = 'A more recent "emaj" extension version exists, compatible with this Emaj_web version.';
	$lang['strwebversionmorerecent'] = 'A more recent Emaj_web version probably exists.';
	$lang['strwarningdevel'] = 'Accessing an emaj extension in <devel> version may generate trouble. It is advisable to get a stable emaj version from pgxn.org.';

	// Extension management
	$lang['strextensionmngt'] = '"emaj" extension management';
	$lang['strcreateextension'] = 'Create extension';
	$lang['strcreateemajextension'] = 'Create the "emaj" extension';
	$lang['strnocompatibleemajversion'] = 'No installed emaj extension version is compatible with the PostgreSQL version.';
	$lang['strcreateextensionok'] = 'The "emaj" extension has been created.';
	$lang['strcreateextensionerr'] = 'Error while creating the "emaj" extension.';
	$lang['strupdateextension'] = 'Update extension';
	$lang['strupdateemajextension'] = 'Update the "emaj" extension';
	$lang['strmissingeventtriggers'] = 'Some event triggers are missing. It blocks any version update >= 4.2.0. Execute the sql/emaj_upgrade_after_postgres_upgrade.sql script or drop and recreate the extension.';
	$lang['strnocompatibleemajupdate'] = 'No installed emaj extension update is compatible with the PostgreSQL version.';
	$lang['strupdateextensionok'] = 'The "emaj" extension has been updated.';
	$lang['strupdateextensionerr'] = 'Error while updating the "emaj" extension.';
	$lang['strdropextension'] = 'Drop extension';
	$lang['strdropextensiongroupsexist'] = 'Some (%s) tables groups currently exist. Dropping the extension will automatically drop these groups.';
	$lang['strdropemajextension'] = 'Drop the "emaj" extension';
	$lang['strconfirmdropextension'] = 'Confirm the "emaj" extension drop';
	$lang['strdropextensionok'] = 'The "emaj" extension has been dropped.';
	$lang['strdropextensionerr'] = 'Error while dropping the "emaj" extension.';

	// Characteristics and consistency checks
	$lang['strdiskspace'] = 'Disk space used by the E-Maj environment: %s of the current database.';
	$lang['strchecking'] = 'E-Maj environment consistency';
	$lang['strdiagnostics'] = 'Diagnostics';

	// Parameters
	$lang['strextparams'] = 'Extension parameters';
	$lang['strpargeneral'] = 'General parameters';
	$lang['strparcostmodel'] = 'E-Maj rollback cost model parameters';
	$lang['strparhistret'] = 'History retention delay';
	$lang['strparhistretinfo'] = 'The \'history_retention\' parameter of the emaj_param table determines the retention delay for some internal tables containing E-Maj operations histories. The default value is 1 year. The parameter is of type INTERVAL.';
	$lang['strpardblinkcon'] = 'Dblink connection string';
	$lang['strpardblinkconinfo'] = 'The \'dblink_user_password\' parameter of the emaj_param table determines the connection string used by dblink to allow the E-Maj rollback operations monitoring.  The parameter content follows the usual PostgreSQL connection string format, for instance \'user=<user> password=<password>\'. By default, the connection string is empty and no rollback monitoring is possible.';
	$lang['strparalterlog'] = 'Log tables structure change';
	$lang['strparalterloginfo'] = 'The \'alter_log_table\' parameter of the emaj_param table determines the structure change to apply at log tables creation. The parameter format corresponds to an ALTER TABLE directive, for instance \'ADD COLUMN emaj_appname TEXT DEFAULT current_setting(\'\'application_name\'\')\'. The parameter is empy by default.';
	$lang['strparfixedstep'] = 'Rollback step fixed cost';
	$lang['strparfixedstepinfo'] = 'The \'fixed_step_rollback_duration\' parameter of the emaj_param table determines a fixed cost to process an elementary E-Maj rollback step. The parameter is of type INTERVAL. The default value is 2,5 ms.';
	$lang['strparfixeddblink'] = 'Dblink overhead for a rollback step';
	$lang['strparfixeddblinkinfo'] = 'The \'fixed_dblink_rollback_duration\' parameter of the emaj_param table determines the overhead generated by a dblink connection for a rollback step. The parameter is of type INTERVAL. The default value is 4 ms.';
	$lang['strparfixedrlbktbl'] = 'Table rollback fixed cost';
	$lang['strparfixedrlbktblinfo'] = 'The \'fixed_table_rollback_duration\' parameter of the emaj_param table determines a fixed cost for a table or sequence rollback. The parameter is of type INTERVAL. The default value is 1 ms.';
	$lang['strparavgrowrlbk'] = 'Average cost of an elementary update rollback';
	$lang['strparavgrowrlbkinfo'] = 'The \'avg_row_rollback_duration\' parameter of the emaj_param table determines an average cost of an elementary upde rollback. The parameter is of type INTERVAL. The default value is 100 µs.';
	$lang['strparavgrowdel'] = 'Average cost of an elementary log table deletion';
	$lang['strparavgrowdelinfo'] = 'The \'avg_row_delete_log_duration\' parameter of the emaj_param table determines an average cost of an elementary update deletion in an E-Maj log table. The parameter is of type INTERVAL. The default value is 10 µs.';
	$lang['strparavgfkcheck'] = 'Average foreign key check cost';
	$lang['strparavgfkcheckinfo'] = 'The \'avg_fkey_check_duration\' parameter of the emaj_param table determines an average cost to ckeck a foreign key. The parameter is of type INTERVAL. The default value is 20 µs.';

	// Import parameters
	$lang['strimportparamconf'] = 'Import a parameters configuration';
	$lang['strdeletecurrentparam'] = 'Delete all existing parameters';
	$lang['strdeletecurrentparaminfo'] = 'If the box is checked, all existing parameters of the emaj extension are deleted before loading the file.';
	$lang['strcheckjsonparamconf101'] = 'The JSON structure does not contain any "parameters" array.';
	$lang['strcheckjsonparamconf102'] = 'The #%s parameter has no "key" attribute or a "key" set to null.';
	$lang['strcheckjsonparamconf103'] = 'For the parameter "%s", the attribute "%s" is unknown.';
	$lang['strcheckjsonparamconf104'] = '"%s" is not a known E-Maj parameter.';
	$lang['strcheckjsonparamconf105'] = 'The JSON structure references several times the parameter "%s".';
	$lang['strparamconfimported'] = '%s: %s parameters imported from the file %s.';
	$lang['strnewconf'] = 'New configuration';
	$lang['strnewmodifiedconf'] = 'Modified configuration';
	$lang['strparamconfigimporterr'] = 'Error while importing parameters from file %s';

?>
