<?php

	/**
	 * English language file for Emaj_web.
	 * Use this as a basis for new translations.
	 */

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

	// Welcome
	$lang['strintro'] = 'Welcome to %s %s, the web client for';
	$lang['strlink'] = 'Some links:';
	$lang['strpgsqlhome'] = 'PostgreSQL Homepage';
	$lang['strpgsqlhome_url'] = 'http://www.postgresql.org/';
	$lang['stremajdoc'] = 'Online E-Maj documentation';
	$lang['stremajdoc_url'] = 'http://emaj.readthedocs.io/en/latest/';
	$lang['stremajproject'] = 'E-Maj on github';
	$lang['stremajwebproject'] = 'Emaj_web on github';

	// Basic strings
	$lang['strlogin'] = 'Login';
	$lang['strloginfailed'] = 'Login failed';
	$lang['strlogindisallowed'] = 'Login disallowed for security reasons.';
	$lang['strserver'] = 'Server';
	$lang['strservers'] = 'Servers';
	$lang['strconfiguredservers'] = 'PostgreSQL servers';
	$lang['strgroupservers'] = 'PostgreSQL servers in group "%s"';
	$lang['strallservers'] = 'All servers';
	$lang['strintroduction'] = 'Introduction';
	$lang['strhost'] = 'Host';
	$lang['strport'] = 'Port';
	$lang['strlogout'] = 'Logout';
	$lang['strowner'] = 'Owner';
	$lang['straction'] = 'Action';
	$lang['stractions'] = 'Actions';
	$lang['strname'] = 'Name';
	$lang['strproperties'] = 'Properties';
	$lang['strbrowse'] = 'Browse';
	$lang['strdrop'] = 'Drop';
	$lang['strnotnull'] = 'Not Null';
	$lang['strprev'] = '< Prev';
	$lang['strnext'] = 'Next >';
	$lang['strfirst'] = '<< First';
	$lang['strlast'] = 'Last >>';
	$lang['strcreate'] = 'Create';
	$lang['strrecreate'] = 'Recreate';
	$lang['strcomment'] = 'Comment';
	$lang['strcommentlabel'] = 'Comment: ';
	$lang['strdefault'] = 'Default';
	$lang['strok'] = 'OK';
	$lang['strcancel'] = 'Cancel';
	$lang['strreset'] = 'Reset';
	$lang['strdelete'] = 'Delete';
	$lang['strupdate'] = 'Update';
	$lang['stryes'] = 'Yes';
	$lang['strno'] = 'No';
	$lang['strtrue'] = 'TRUE';
	$lang['strfalse'] = 'FALSE';
	$lang['strcolumn'] = 'Column';
	$lang['strcolumns'] = 'Columns';
	$lang['strrows'] = 'row(s)';
	$lang['strrowsaff'] = 'row(s) affected.';
	$lang['strback'] = 'Back';
	$lang['strqueryresults'] = 'Query Results';
	$lang['strencoding'] = 'Encoding';
	$lang['strsql'] = 'SQL';
	$lang['strexecute'] = 'Execute';
	$lang['strconfirm'] = 'Confirm';
	$lang['strellipsis'] = '...';
	$lang['strexpand'] = 'Expand';
	$lang['strcollapse'] = 'Collapse';
	$lang['strrefresh'] = 'Refresh';
	$lang['strdownload'] = 'Download';
	$lang['strexport'] = 'Export';
	$lang['strimport'] = 'Import';
	$lang['stropen'] = 'Open';
	$lang['strruntime'] = 'Total runtime: %s ms';
	$lang['strpaginate'] = 'Paginate results';
	$lang['strtrycred'] = 'Use these credentials for all servers';
	$lang['strconfdropcred'] = 'For security reason, disconnecting will destroy your shared login information. Are you sure you want to disconnect ?';
	$lang['strstart'] = 'Start';
	$lang['strstop'] = 'Stop';
	$lang['strgotoppage'] = 'back to top';
	$lang['strselect'] = 'Select';
	$lang['stractionsonselectedobjects'] = 'Actions on objects (%s)';
	$lang['strall'] = 'All';
	$lang['strnone'] = 'None';
	$lang['strinvert'] = 'Invert';
	$lang['emajnotassigned'] = 'Not assigned';
	$lang['strlevel'] = 'Level';
	$lang['strmessage'] = 'Message';
	$lang['strbegin'] = 'Begin';
	$lang['strend'] = 'End';
	$lang['strsince'] = 'Since';
	$lang['strquantity'] = 'Quantity';
	$lang['strautorefresh'] = 'Auto refresh';
	$lang['strbacktolist'] = 'Back to the list';
	$lang['strset'] = 'Set';
	$lang['stredit'] = 'Edit';
	$lang['strclear'] = 'Clear';

	// User-supplied SQL editing
	$lang['strsqledit'] = 'SQL statement editing';
	$lang['strsearchpath'] = 'Schemas search path';

	// User-supplied SQL history
	$lang['strhistory'] = 'History';
	$lang['strsqlhistory'] = 'SQL Statements history';
	$lang['strnohistory'] = 'No history.';
	$lang['strclearhistory'] = 'Clear history';
	$lang['strdelhistory'] = 'Delete from history';
	$lang['strconfdelhistory'] = 'Really remove this request from history?';
	$lang['strconfclearhistory'] = 'Really clear history?';
	$lang['strnodatabaseselected'] = 'Please, select a database.';

	// Database sizes
	$lang['strnoaccess'] = 'No Access'; 
	$lang['strsize'] = 'Size';
	$lang['strbytes'] = 'bytes';
	$lang['strkb'] = 'kB';
	$lang['strmb'] = 'MB';
	$lang['strgb'] = 'GB';
	$lang['strtb'] = 'TB';

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

	// Users
	$lang['strusername'] = 'Username';
	$lang['strpassword'] = 'Password';

	// Groups
	$lang['strgroup'] = 'Group';
	$lang['strgroupgroups'] = 'Groups in group "%s"';
	$lang['strserversgroups'] = 'Servers groups';

	// Roles
	$lang['strrole'] = 'Role';
	$lang['strroles'] = 'Roles';

	// Databases
	$lang['strdatabase'] = 'Database';
	$lang['strdatabases'] = 'Databases';
	$lang['strdatabaseslist'] = 'Server\'s databases';
	$lang['strnodatabases'] = 'No databases found.';
	$lang['strsqlexecuted'] = 'SQL executed.';

	// Schemas
	$lang['strschema'] = 'Schema';
	$lang['strschemas'] = 'Schemas';
	$lang['strallschemas'] = 'All schemas';
	$lang['strnoschemas'] = 'No schemas found.';

	// Tables
	$lang['strtable'] = 'Table';
	$lang['strtables'] = 'Tables';
	$lang['strtableslist'] = 'Schema "%s" tables';
	$lang['strnotables'] = 'No tables found.';
	$lang['strestimatedrowcount'] = 'Estimated row count';
	$lang['strtblproperties'] = 'Table "%s.%s" properties';
	$lang['strtblcontent'] = 'Table "%s.%s" content';
	$lang['emajemajlogtable'] = 'The table is an E-Maj log table.';
	$lang['emajinternaltable'] = 'The table is an internal E-Maj table.';
	$lang['emajtblnogroupownership'] = 'The table does not currently belong to any tables group.';

	// Sequences
	$lang['strsequence'] = 'Sequence';
	$lang['strsequences'] = 'Sequences';
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
	$lang['emajemajlogsequence'] = 'The sequence is an E-Maj log sequence.';
	$lang['emajinternalsequence'] = 'The sequence is an internal E-Maj sequence.';
	$lang['emajseqnogroupownership'] = 'The sequence does not currently belong to any tables group.';

	// Constraints
	$lang['strconstraints'] = 'Constraints';
	$lang['strpk'] = 'Primary key';

	// Types
	$lang['strtype'] = 'Type';

	// Tablespaces
	$lang['strtablespace'] = 'Tablespace';

	// Triggers
	$lang['strtrigger'] = 'Trigger';
	$lang['strtriggers'] = 'Triggers';
	$lang['strnotriggerontable'] = 'The table has no trigger.';
	$lang['emajapptriggers'] = 'Application triggers';
	$lang['emajapptriggershelp'] = 'List of triggers in the database, excluding system and E-Maj triggers.';
	$lang['strnoapptrigger'] = 'No application trigger in the database.';
	$lang['emajexecorder'] = 'Exec. order';
	$lang['emajtriggeringevent'] = 'Triggering event';
	$lang['emajcalledfunction'] = 'Called function';
	$lang['emajisemaj'] = 'E-Maj?';
	$lang['emajisautodisable'] = 'Auto disable';
	$lang['emajisautodisablehelp'] = 'Indicate whether the trigger is automatically disabled at E-maj rollback time (default = ON = Yes)';
	$lang['emajswitchautodisable'] = 'Switch auto disable';
	$lang['emajtriggerautook'] = 'The trigger %s for the table %s.%s will be automatically disabled at E-Maj rollbacks time.';
	$lang['emajtriggernoautook'] = 'The trigger %s for the table %s.%s will NOT be automatically disabled at E-Maj rollbacks time.';
	$lang['emajtriggerprocerr'] = 'An error occured while processing the trigger %s of the table %s.%s.';
	$lang['emajnoselectedtriggers'] = 'No selected trigger.';
	$lang['emajtriggersautook'] = '%s new triggers will be automatically disabled at E-Maj rollbacks time.';
	$lang['emajtriggersnoautook'] = '%s new triggers will NOT be automatically disabled at E-Maj rollbacks time.';
	$lang['emajorphantriggersexist'] = 'The table that contains the identifiers of triggers not to be automatically disabled at E-Maj rollbacks (emaj_ignored_app_trigger) references schemas, tables or triggers that do not exist anymore.';
	$lang['emajtriggersremovedok'] = '%s triggers have been removed.';

	// Miscellaneous
	$lang['strtopbar'] = 'Connection: %s:%s - role "%s"';
	$lang['strlogintitle'] = 'Login to %s';
	$lang['strlogoutmsg'] = 'Logged out of %s';
	$lang['strloading'] = 'Loading...';
	$lang['strerrorloading'] = 'Error Loading';
	$lang['strclicktoreload'] = 'Click to reload';

//
// E-Maj strings
//

	// Basic strings 
	$lang['emajnotavail'] = 'Sorry, E-Maj is not available or accessible for this database. More details in the %s tab.';
	$lang['emajstate'] = 'State';
	$lang['emajnoselectedgroup'] = 'No tables group has been selected!';
	$lang['emajtablesgroup'] = 'Tables group';
	$lang['emajgroup'] = 'Group';
	$lang['emajgroups'] = 'Groups';
	$lang['emajnewgroup'] = 'New group';
	$lang['emajmark'] = 'Mark';
	$lang['emajlogsession'] = 'Log session';
	$lang['emajgroupcreatedat'] = 'Created at';
	$lang['emajgroupcreateddroppedat'] = 'Created/dropped at';
	$lang['emajgrouplatesttype'] = 'Latest type';
	$lang['emajgrouplatestdropat'] = 'Latest drop at';
	$lang['emajgroupstartedat'] = 'Started at';
	$lang['emajgroupstoppedat'] = 'Stopped at';
	$lang['emajmarksetat'] = 'Set at';
	$lang['emajgrouptype'] = 'Group type';
	$lang['emajrollback'] = 'E-Maj rollback';
	$lang['emajrollbacktype'] = 'Rollback type';
	$lang['emajauditonly'] = 'AUDIT-ONLY';
	$lang['emajrollbackable'] = 'ROLLBACK-ABLE';
	$lang['emajunlogged'] = 'unlogged';
	$lang['emajlogged'] = 'logged';
	$lang['emajlogging'] = 'Logging';
	$lang['emajidle'] = 'Idle';
	$lang['emajactivemark'] = 'Active mark, thus usable for an E-Maj rollback.';
	$lang['emajdeletedmark'] = 'A stop of the changes recording has left the mark inactive, thus unusable for an E-Maj rollback.';
	$lang['emajprotectedmark'] = 'The protection set on the mark blocks any E-Maj rollbacks on prior marks.';
	$lang['emajprotected'] = 'Protected against E-Maj rollbacks';
	$lang['emajpagebottom'] = 'Go to bottom';
	$lang['emajlogsize'] = 'Log size';
	$lang['emajrequiredfield'] = 'Required field';
	$lang['emajestimates'] = 'Estimates';
	$lang['emajestimate'] = 'Estimate';
	$lang['emajreestimate'] = 'Reestimate';
	$lang['emajestimatedduration'] = 'Estimated duration';
	$lang['emajproperties'] = 'E-Maj properties';
	$lang['emajschema'] = 'E-Maj schema';
	$lang['emajtrigger'] = 'E-Maj trigger';
	$lang['emajselectfile'] = 'Select a file';
	$lang['emajnotjsonfile'] = 'The file %s has not a valid JSON format.';
	$lang['emajtxid'] = 'Transaction id.';
	$lang['emajstartmark'] = 'Start mark';
	$lang['emajstartdatetime'] = 'Start date-time';
	$lang['emajendmark'] = 'End mark';
	$lang['emajenddatetime'] = 'End date-time';
	$lang['emajassign'] = 'Assign';
	$lang['emajassigned'] = 'Assigned';
	$lang['emajmove'] = 'Move';
	$lang['emajremove'] = 'Remove';
	$lang['emajremoved'] = 'Removed';
	$lang['emajvisible'] = 'Visible';
	$lang['emajsetmark'] = 'Set a mark';
	$lang['emajsetcomment'] = 'Set a comment';
	$lang['emajdorollbackfor'] = 'Execute a rollback for';
	$lang['emajforget'] = 'Forget';

	// E-Maj html titles and tabs
	$lang['emajgroupsmanagement'] = 'E-Maj groups management';
	$lang['emajgroupsconfiguration'] = 'Tables groups\' configuration';
	$lang['emajgroupsconf'] = 'Groups conf.';
	$lang['emajrollbacksmanagement'] = 'E-Maj rollbacks management';
	$lang['emajrlbkop'] = 'E-Maj Rollbacks';
	$lang['emajenvironment'] = 'E-Maj environment';
	$lang['emajenvir'] = 'E-Maj';
	$lang['emajchangesstat'] = 'Changes statistics';
	$lang['emajhistory'] = 'History';

	// Common help messages
	$lang['emajmarknamehelp'] = 'The mark name must be unique within the group. A % character represents the current time (format hh.mn.ss.ms).';
	$lang['emajmarknamemultihelp'] = 'The mark name must be unique within the concerned groups. A % character represents the current time (format hh.mn.ss.ms).';
	$lang['emajfiltershelp'] = 'Display/hide filters. Filters on the columns content may contain character strings (abc), numbers (123), inequality conditions (>= 1000), regular expressions (/^ABC\d\d/), multiple conditions with \'and\', \'or\', \'!\' operators.';

	// E-Maj environment
	$lang['emajextnotavailable'] = 'The E-Maj software is not installed on this PostgreSQL instance.';
	$lang['emajextnotcreated'] = 'The emaj extension is not created in this database.';
	$lang['emajcontactdba'] = 'Contact your database administrator.';
	$lang['emajnogrant'] = 'Your connection role has no E-Maj rights. Use another role or contact your database administrator.';
	$lang['emajcharacteristics'] = 'E-Maj environment characteristics';
	$lang['emajversions'] = 'Versions';
	$lang['emajpgversion'] = 'PostgreSQL version: ';
	$lang['emajversion'] = 'E-Maj version: ';
	$lang['emajasextension'] = 'installed as extension';
	$lang['emajasscript'] = 'installed by script';
	$lang['emajtooold'] = 'Sorry, this E-Maj version (%s) is too old. The minimum version supported by Emaj_web is %s.';
	$lang['emajversionmorerecent'] = 'A more recent "emaj" extension version exists, compatible with this Emaj_web version.';
	$lang['emajwebversionmorerecent'] = 'A more recent Emaj_web version probably exists.';
	$lang['emajwarningdevel'] = 'Accessing an emaj extension in <devel> version may generate trouble. It is advisable to get a stable emaj version from pgxn.org.';
	$lang['emajextensionmngt'] = '"emaj" extension management';
	$lang['emajcreateextension'] = 'Create extension';
	$lang['emajcreateemajextension'] = 'Create the "emaj" extension';
	$lang['emajnocompatibleemajversion'] = 'No installed emaj extension version is compatible with the PostgreSQL version.';
	$lang['emajcreateextensionok'] = 'The "emaj" extension has been created.';
	$lang['emajcreateextensionerr'] = 'Error while creating the "emaj" extension.';
	$lang['emajupdateextension'] = 'Update extension';
	$lang['emajupdateemajextension'] = 'Update the "emaj" extension';
	$lang['emajmissingeventtriggers'] = 'Some event triggers are missing. It blocks any version update >= 4.2.0. Execute the sql/emaj_upgrade_after_postgres_upgrade.sql script or drop and recreate the extension.';
	$lang['emajnocompatibleemajupdate'] = 'No installed emaj extension update is compatible with the PostgreSQL version.';
	$lang['emajupdateextensionok'] = 'The "emaj" extension has been updated.';
	$lang['emajupdateextensionerr'] = 'Error while updating the "emaj" extension.';
	$lang['emajdropextension'] = 'Drop extension';
	$lang['emajdropextensiongroupsexist'] = 'To drop the "emaj" extension, first drop the tables groups.';
	$lang['emajdropemajextension'] = 'Drop the "emaj" extension';
	$lang['emajconfirmdropextension'] = 'Confirm the "emaj" extension drop';
	$lang['emajdropextensionok'] = 'The "emaj" extension has been dropped.';
	$lang['emajdropextensionerr'] = 'Error while dropping the "emaj" extension.';
	$lang['emajdiskspace'] = 'Disk space used by the E-Maj environment: %s of the current database.';
	$lang['emajchecking'] = 'E-Maj environment consistency';
	$lang['emajdiagnostics'] = 'Diagnostics';
	$lang['emajextparams'] = 'Extension parameters';
	$lang['emajpargeneral'] = 'General parameters';
	$lang['emajparcostmodel'] = 'E-Maj rollback cost model parameters';
	$lang['emajparhistret'] = 'History retention delay';
	$lang['emajparhistretinfo'] = 'The \'history_retention\' parameter of the emaj_param table determines the retention delay for some internal tables containing E-Maj operations histories. The default value is 1 year. The parameter is of type INTERVAL.';
	$lang['emajpardblinkcon'] = 'Dblink connection string';
	$lang['emajpardblinkconinfo'] = 'The \'dblink_user_password\' parameter of the emaj_param table determines the connection string used by dblink to allow the E-Maj rollback operations monitoring.  The parameter content follows the usual PostgreSQL connection string format, for instance \'user=<user> password=<password>\'. By default, the connection string is empty and no rollback monitoring is possible.';
	$lang['emajparalterlog'] = 'Log tables structure change';
	$lang['emajparalterloginfo'] = 'The \'alter_log_table\' parameter of the emaj_param table determines the structure change to apply at log tables creation. The parameter format corresponds to an ALTER TABLE directive, for instance \'ADD COLUMN emaj_appname TEXT DEFAULT current_setting(\'\'application_name\'\')\'. The parameter is empy by default.';
	$lang['emajparfixedstep'] = 'Rollback step fixed cost';
	$lang['emajparfixedstepinfo'] = 'The \'fixed_step_rollback_duration\' parameter of the emaj_param table determines a fixed cost to process an elementary E-Maj rollback step. The parameter is of type INTERVAL. The default value is 2,5 ms.';
	$lang['emajparfixeddblink'] = 'Dblink overhead for a rollback step';
	$lang['emajparfixeddblinkinfo'] = 'The \'fixed_dblink_rollback_duration\' parameter of the emaj_param table determines the overhead generated by a dblink connection for a rollback step. The parameter is of type INTERVAL. The default value is 4 ms.';
	$lang['emajparfixedrlbktbl'] = 'Table rollback fixed cost';
	$lang['emajparfixedrlbktblinfo'] = 'The \'fixed_table_rollback_duration\' parameter of the emaj_param table determines a fixed cost for a table or sequence rollback. The parameter is of type INTERVAL. The default value is 1 ms.';
	$lang['emajparavgrowrlbk'] = 'Average cost of an elementary update rollback';
	$lang['emajparavgrowrlbkinfo'] = 'The \'avg_row_rollback_duration\' parameter of the emaj_param table determines an average cost of an elementary upde rollback. The parameter is of type INTERVAL. The default value is 100 µs.';
	$lang['emajparavgrowdel'] = 'Average cost of an elementary log table deletion';
	$lang['emajparavgrowdelinfo'] = 'The \'avg_row_delete_log_duration\' parameter of the emaj_param table determines an average cost of an elementary update deletion in an E-Maj log table. The parameter is of type INTERVAL. The default value is 10 µs.';
	$lang['emajparavgfkcheck'] = 'Average foreign key check cost';
	$lang['emajparavgfkcheckinfo'] = 'The \'avg_fkey_check_duration\' parameter of the emaj_param table determines an average cost to ckeck a foreign key. The parameter is of type INTERVAL. The default value is 20 µs.';

	// Import parameters
	$lang['emajimportparamconf'] = 'Import a parameters configuration';
	$lang['emajdeletecurrentparam'] = 'Delete all existing parameters';
	$lang['emajdeletecurrentparaminfo'] = 'If the box is checked, all existing parameters of the emaj extension are deleted before loading the file.';
	$lang['emajcheckjsonparamconf101'] = 'The JSON structure does not contain any "parameters" array.';
	$lang['emajcheckjsonparamconf102'] = 'The #%s parameter has no "key" attribute or a "key" set to null.';
	$lang['emajcheckjsonparamconf103'] = 'For the parameter "%s", the attribute "%s" is unknown.';
	$lang['emajcheckjsonparamconf104'] = '"%s" is not a known E-Maj parameter.';
	$lang['emajcheckjsonparamconf105'] = 'The JSON structure references several times the parameter "%s".';
	$lang['emajparamconfimported'] = '%s: %s parameters imported from the file %s.';
	$lang['emajnewconf'] = 'New configuration';
	$lang['emajnewmodifiedconf'] = 'Modified configuration';
	$lang['emajparamconfigimporterr'] = 'Error while importing parameters from file %s';

	// Dynamic groups content management: common messages
	$lang['emajlogdattsp'] = 'Log table tablespace';
	$lang['emajlogidxtsp'] = 'Log index tablespace';
	$lang['emajthetable'] = 'the "%s.%s" table';
	$lang['emajthesequence'] = 'the "%s.%s" sequence';
	$lang['emajthetblseqingroup'] = '%s (group %s)';
	$lang['emajthetableingroup'] = 'the "%s.%s" table (group %s)';
	$lang['emajthesequenceingroup'] = 'the "%s.%s" sequence (group %s)';
	$lang['emajenterpriority'] = 'Processing priority';
	$lang['emajpriorityhelp'] = 'Tables are processed in priority ascending order, and in names alphabetic order if no priority is defined.';
	$lang['emajenterlogdattsp'] = 'Log table tablespace';
	$lang['emajenterlogidxtsp'] = 'Log index tablespace';
	$lang['emajmarkiflogginggroup'] = 'Mark (if logging group)';
	$lang['emajthesequenceingroup'] = 'the "%s.%s" sequence (group %s)';

	// Dynamic groups content management: generic error messages
	$lang['emajschemamissing'] = 'The schema "%s" does not exist anymore.';
	$lang['emajtablemissing'] = 'The table "%s.%s" does not exist anymore.';
	$lang['emajsequencemissing'] = 'The sequence "%s.%s" does not exist anymore.';
	$lang['emajtablesmissing'] = '%s tables (%s) do not exist anymore.';
	$lang['emajsequencesmissing'] = '%s sequences (%s) do not exist anymore.';

	// Assign tables
	$lang['emajassigntable'] = 'E-Maj: Assign tables to a tables group';
	$lang['emajconfirmassigntable'] = 'Assign the table "%s.%s"';
	$lang['emajconfirmassigntables'] = 'Assign these %s tables of schema "%s":';
	$lang['emajassigntableok'] = '%s table has been assigned to the tables group %s.';
	$lang['emajassigntablesok'] = '%s tables have been assigned to the tables group %s.';
	$lang['emajassigntableerr'] = 'Error while assigning the table "%s.%s".';
	$lang['emajassigntableerr2'] = 'Error while assigning the table "%s.%s" to the tables group "%s".';
	$lang['emajassigntableserr'] = 'Error while assigning these %s tables of schema "%s".';
	$lang['emajassigntableserr2'] = 'Error while assigning these %s tables of schema "%s" to the tables group "%s".';

	// Move tables
	$lang['emajmovetable'] = 'E-Maj: Move tables to another tables group';
	$lang['emajconfirmmovetable'] = 'Move the table "%s.%s" from its tables group "%s".';
	$lang['emajconfirmmovetables'] = 'Move these %s tables of schema "%s":';
	$lang['emajmovetableok'] = '%s table has been moved to the tables group %s.';
	$lang['emajmovetablesok'] = '%s tables have been moved to the tables group %s.';
	$lang['emajmovetableerr'] = 'Error while moving the table "%s.%s".';
	$lang['emajmovetableerr2'] = 'Error while moving the table "%s.%s" from tables group "%s" to tables group "%s".';
	$lang['emajmovetableserr'] = 'Error while moving these %s tables of schema "%s".';
	$lang['emajmovetableserr2'] = 'Error while moving these %s tables of schema "%s" from their tables group to the tables group "%s".';

	// Modify table
	$lang['emajmodifytable'] = 'E-Maj: Modify tables E-Maj properties';
	$lang['emajconfirmmodifytable'] = 'Modify the E-Maj properties of the table "%s.%s".';
	$lang['emajmodifytablesok'] = 'E-Maj properties for %s tables have been modified.';
	$lang['emajmodifytableerr'] = 'Error while modifying E-Maj properties of the table "%s.%s".';

	// Remove tables
	$lang['emajremovetable'] = 'E-Maj : Remove tables from their tables group';
	$lang['emajconfirmremovetable'] = 'Remove the table "%s.%s" from its tables group "%s".';
	$lang['emajconfirmremovetables'] = 'Remove these %s tables of schema "%s" from their tables group:';
	$lang['emajremovetableok'] = '%s table has been removed from its tables group.';
	$lang['emajremovetablesok'] = '%s tables have been removed from their tables group.';
	$lang['emajremovetableerr'] = 'Error while removing the table "%s.%s" from its tables group "%s".';
	$lang['emajremovetableserr'] = 'Error while removing these %s tables of schema "%s" from their tables group.';

	// Assign sequences
	$lang['emajassignsequence'] = 'E-Maj: Assign sequences to a tables group';
	$lang['emajconfirmassignsequence'] = 'Assign the sequence "%s.%s"';
	$lang['emajconfirmassignsequences'] = 'Assign these %s sequences of schema "%s":';
	$lang['emajassignsequenceok'] = '%s sequence has been assigned to the tables group %s.';
	$lang['emajassignsequencesok'] = '%s sequences have been assigned to the tables group %s.';
	$lang['emajassignsequenceerr'] = 'Error while assigning the sequence "%s.%s".';
	$lang['emajassignsequenceerr2'] = 'Error while assigning the sequence "%s.%s" to the tables group "%s".';
	$lang['emajassignsequenceserr'] = 'Error while assigning these %s sequences of schema "%s".';
	$lang['emajassignsequenceserr2'] = 'Error while assigning these %s sequences of schema "%s" to the tables group "%s".';

	// Move sequences
	$lang['emajmovesequence'] = 'E-Maj: Move sequences to another tables group';
	$lang['emajconfirmmovesequence'] = 'Move the sequence "%s.%s" from its tables group "%s".';
	$lang['emajconfirmmovesequences'] = 'Move these %s sequences from schema "%s":';
	$lang['emajmovesequenceok'] = '%s sequence has been moved to the tables group %s.';
	$lang['emajmovesequencesok'] = '%s sequences have been moved to the tables group %s.';
	$lang['emajmovesequenceerr'] = 'Error while moving the sequence "%s.%s".';
	$lang['emajmovesequenceerr2'] = 'Error while moving the sequence "%s.%s" from tables group "%s" to tables group "%s".';
	$lang['emajmovesequenceserr'] = 'Error while moving these %s sequences of schema "%s".';
	$lang['emajmovesequenceserr2'] = 'Error while moving these %s sequences of schema "%s" from their tables group to the tables group "%s".';

	// Remove sequences
	$lang['emajremovesequence'] = 'E-Maj : Remove sequences from their tables group';
	$lang['emajconfirmremovesequence'] = 'Remove the sequence "%s.%s" from its tables group "%s".';
	$lang['emajconfirmremovesequences'] = 'Remove these %s sequences from schema "%s" :';
	$lang['emajremovesequenceok'] = '%s sequence has been removed from its tables group.';
	$lang['emajremovesequencesok'] = '%s sequences have been removed from their tables group.';
	$lang['emajremovesequenceerr'] = 'Error while removing the sequence "%s.%s" from its tables group "%s".';
	$lang['emajremovesequenceserr'] = 'Error while removing these %s sequences of schema "%s" from their tables group.';

	// Old Groups' content setup
	$lang['emajappschemas'] = 'Application schemas';
	$lang['emajunknownobject'] = 'This object is referenced in the emaj_group_def table but is not created.';
	$lang['emajunsupportedobject'] = 'This object type is not supported by E-Maj (unlogged table, table with OIDS, partition table,...).';
	$lang['emajtblseqofschema'] = 'Tables and sequences in schema "%s"';
	$lang['emajlogschemasuffix'] = 'Log schema suffix';
	$lang['emajnamesprefix'] = 'Objects name prefix';
	$lang['emajspecifytblseqtoassign'] = 'Specify at least one table or sequence to assign';
	$lang['emajtblseqyetgroup'] = 'Error, "%s.%s" is already assigned to a tables group.';
	$lang['emajtblseqbadtype'] = 'Error, type of "%s.%s" is not supported by E-Maj.';
	$lang['emajassigntblseq'] = 'E-Maj: Assign tables / sequences to a tables group';
	$lang['emajfromgroup'] = 'from the group "%s"';
	$lang['emajenterlogschema'] = 'Log schema suffix';
	$lang['emajlogschemahelp'] = 'A log schema contains log tables, sequences and functions. The default log schema is \'emaj\'. If a suffix is defined for the table, its objects will be hosted in the schema \'emaj\' + suffix.';
	$lang['emajenternameprefix'] = 'E-Maj objects name prefix';
	$lang['emajnameprefixhelp'] = 'By default, log objects names are prefixed by &lt;schema&gt;_&lt;table&gt;. But another prefix can be defined for the table. It must be unique in the database.';
	$lang['emajspecifytblseqtoupdate'] = 'Specify at least one table or sequence to update';
	$lang['emajupdatetblseq'] = 'E-Maj: Update properties of a table / sequence in a tables group';
	$lang['emajspecifytblseqtoremove'] = 'Specify at least one table or sequence to remove';
	$lang['emajtblseqnogroup'] = 'Error, "%s.%s" is not currently assigned to any tables group.';
	$lang['emajremovetblseq'] = 'E-Maj: Remove tables / sequences from tables groups';
	$lang['emajconfirmremove1tblseq'] = 'Are you sure you want to remove %s from the tables group "%s"?';
	$lang['emajmodifygroupok'] = 'The configuration change is recorded. It will take effect when the concerned tables groups will be (re)created or when the configuration changes will be applied for these groups.';
	$lang['emajspecifytblseqtoprocess'] = 'Specify at least one table or sequence to process.';

	// List Groups
	$lang['emajidlegroups'] = 'Tables groups in "IDLE" state ';
	$lang['emajlogginggroups'] = 'Tables groups in "LOGGING" state ';
	$lang['emajconfiguredgroups'] = 'Configured but not yet created tables groups ';
	$lang['emajlogginggrouphelp'] = 'When a tables group is in \'logging\' state, the row insertions, updates and deletions on its tables are recorded.';
 	$lang['emajidlegrouphelp'] = 'When a tables group is in \'idle\' state, the row insertions, updates and deletions on its tables are NOT recorded.';
	$lang['emajconfiguredgrouphelp'] = 'The group configuration defines the tables and sequences that will compose it. Once \'configured\', the group must be \'created\' in order to prepare all abjects that will be needed for its use (log tables, functions,...).';
	$lang['emajnbtbl'] = 'Tables';
	$lang['emajnbseq'] = 'Sequences';
	$lang['emajnbmark'] = 'Marks';
	$lang['emajApplyConfChanges'] = 'Apply conf changes';
	$lang['emajnoidlegroup'] = 'No tables group is currently in idle state.';
	$lang['emajnologginggroup'] = 'No tables group is currently in logging state.';
	$lang['emajnoconfiguredgroups'] = 'No tables group is currently configured but not created.';
	$lang['emajnoschema'] = 'Schema not found (x%s) / ';
	$lang['emajinvalidschema'] = 'Invalid schema (x%s) / ';
	$lang['emajnorelation'] = 'Table or sequence not found (x%s) / ';
	$lang['emajinvalidtable'] = 'Invalid table type (x%s) / ';
	$lang['emajduplicaterelation'] = 'Table or sequence already assigned to another group (x%s) / ';
	$lang['emajnoconfiguredgroup'] = 'To create a (another) tables group, go first to the groups configuration tab.<br>Alternatively, you can create an empty group, then add tables and sequences into it, and apply the configuration change.';
	$lang['emajcreateemptygroup'] = 'Create an empty group';
	$lang['emajnewgroup'] = 'New group';
	$lang['emajdroppedgroupslist'] = 'Old dropped tables groups';
	$lang['emajnodroppedgroup'] = 'No old dropped tables groups.';

	// Rollback activity
	$lang['emajrlbkid'] = 'Rlbk Id.';
	$lang['emajrlbkstart'] = 'Rollback start';
	$lang['emajrlbkend'] = 'Rollback end';
	$lang['emajduration'] = 'Duration';
	$lang['emajislogged'] = 'Logged ?';
	$lang['emajnbsession'] = 'Sessions';
	$lang['emajnbproctable'] = 'Processed tables';
	$lang['emajnbprocseq'] = 'Processed sequences';
	$lang['emajcurrentduration'] = 'Current duration';
	$lang['emajglobalduration'] = 'Global duration';
	$lang['emajplanningduration'] = 'Planning duration';
	$lang['emajlockingduration'] = 'Locking duration';
	$lang['emajestimremaining'] = 'Estimated remaining';
	$lang['emajpctcompleted'] = '% completed';
	$lang['emajinprogressrlbk'] = 'In progress E-Maj rollbacks';
	$lang['emajrlbkmonitornotavailable'] = 'In progress rollbacks monitoring is not available.';
	$lang['emajcompletedrlbk'] = 'Completed E-Maj rollbacks';
	$lang['emajnbtabletoprocess'] = 'Tables to process';
	$lang['emajnbseqtoprocess'] = 'Sequences to process';
	$lang['emajnorlbk'] = 'No E-Maj rollback.';
	$lang['emajconsolidablerlbk'] = 'Consolidable E-Maj logged rollbacks';
	$lang['emajtargetmark'] = 'Target mark';
	$lang['emajendrollbackmark'] = 'End rollback mark';
	$lang['emajnbintermediatemark'] = 'Intermediate marks';
	$lang['emajconsolidate'] = 'Consolidate';
	$lang['emajconsolidaterlbk'] = 'Consolidate a logged rollback';
	$lang['emajconfirmconsolidaterlbk'] = 'Are you sure you want to consolidate the rollback ended with the mark "%s" of the tables group "%s"?';
	$lang['emajconsolidaterlbkok'] = 'The rollback ended with the mark "%s" of the tables group "%s" has been consolidated.';
	$lang['emajconsolidaterlbkerr'] = 'Error while consolidating the rollback ended by the mark "%s" of the tables group "%s"!';
	$lang['emajrlbkprogress'] = 'Rollback progress';
	$lang['emajrlbksessions'] = 'Sessions';
	$lang['emajrlbksession'] = 'Session';
	$lang['emajrlbkexecreport'] = 'Execution report';
	$lang['emajrlbkplanning'] = 'Planning';
	$lang['emajrlbkplanninghelp'] = 'The main elementary steps of the E-Maj Rollback execution. Are not included: the planning and the locks set on tables at the beginning of the operation, and, for emaj version < 4.2, the sequences processing at the end of the operation.';
	$lang['emajrlbkestimmethodhelp'] = 'At planning time, the duration of each step is estimated, using statistics of similar steps in the past, with the same order of magnitude of quantity to process (STAT+), or other orders of magnitude (STAT), or, by default, the extension parameters (PARAM). The Q column evaluates the duration estimates quality.';
	$lang['emajnorlbkstep'] = 'No elementary step for this rollback.';
	$lang['emajhideestimates'] = 'Hide estimates';
	$lang['emajshowestimates'] = 'Show estimates';
	$lang['emajrlbkstep'] = 'Step';
	$lang['emajestimatedquantity'] = 'Estimated quantity';
	$lang['emajestimationmethod'] = 'Estimation method';
	$lang['emajrlbksequences'] = 'Rollback sequences';
	$lang['emajrlbkdisapptrg'] = 'Disable the trigger %s';
	$lang['emajrlbkdislogtrg'] = 'Disable the log trigger';
	$lang['emajrlbksetalwaysapptrg'] = 'Set the trigger %s as ALWAYS';
	$lang['emajrlbkdropfk'] = 'Drop the foreign key %s';
	$lang['emajrlbksetfkdef'] = 'Set the foreign key %s DEFFERED';
	$lang['emajrlbkrlbktable'] = 'Rollback the table';
	$lang['emajrlbkdeletelog'] = 'Delete logs';
	$lang['emajrlbksetfkimm'] = 'Set the foreign key %s IMMEDIATE';
	$lang['emajrlbkaddfk'] = 'Recreate the foreign key %s';
	$lang['emajrlbkenaapptrg'] = 'Re-enable the trigger %s';
	$lang['emajrlbksetlocalapptrg'] = 'Set the trigger %s as LOCAL';
	$lang['emajrlbkenalogtrg'] = 'Re-enable the log trigger';

	// Rollback comment
	$lang['emajcommentarollback'] = 'E-Maj: Record a comment for a rollback';
	$lang['emajcommentrollback'] = 'Enter, modify or erase the comment for the rollback %s';
	$lang['emajcommentrollbackok'] = 'The comment for the rollback %s has been recorded.';
	$lang['emajcommentrollbackerr'] = 'Error during comment recording for the rollback %s!';

	// Group's properties and marks
	$lang['emajgroupproperties'] = 'Tables group "%s" properties';
	$lang['emajcontent'] = 'Content';
	$lang['emajgroupmarks'] = 'Tables group "%s" marks';
	$lang['emajlogsessionshelp'] = 'Log session, representing the time interval between the tables group start and stop.';
	$lang['emajlogsessionstart'] = 'Log session started at: %s';
	$lang['emajlogsessionstop'] = ' and stopped at: %s';
	$lang['emajtimestamp'] = 'Date/Time';
	$lang['emajnbchanges'] = 'Row changes';
	$lang['emajcumchanges'] = 'Cumulated<br>changes';
	$lang['emajcumchangeshelp'] = 'The cummulative number of row changes represents the number of row changes to cancel in case of E-Maj rollback to the corresponding mark.';
	$lang['emajrlbk'] = 'Rollback';
	$lang['emajfirstmark'] = 'First mark';
	$lang['emajrename'] = 'Rename';
	$lang['emajnomark'] = 'The tables group has no mark';
	$lang['emajprotect'] = 'Protect';
	$lang['emajunprotect'] = 'Unprotect';

	// Statistics
	$lang['emajchangesgroup'] = 'Recorded changes for the tables group "%s"';
	$lang['emajcurrentsituation'] = 'Current state';
	$lang['emajestimatetables'] = 'Estimate tables';
	$lang['emajestimatesequences'] = 'Estimate sequences';
	$lang['emajdetailtables'] = 'Detail tables';
	$lang['emajdetailedlogstatwarning'] = 'Scanning the log tables needed to get detailed statistics may take a long time. Although less detailed and precise, the changes number estimate is quicker because it only uses the log sequence values recorded at each mark.';
	$lang['emajchangestblbetween'] = 'Table changes between marks "%s" and "%s"';
	$lang['emajchangestblsince'] = 'Table changes since mark "%s"';
	$lang['emajtblingroup'] = 'Tables in group';
	$lang['emajtblwithchanges'] = 'Tables with changes';
	$lang['emajchangesseqbetween'] = 'Sequence changes between marks "%s" and "%s"';
	$lang['emajchangesseqsince'] = 'Sequence changes since mark "%s"';
	$lang['emajseqingroup'] = 'Sequences in group';
	$lang['emajseqwithchanges'] = 'Sequences with changes';
	$lang['emajstatincrements'] = 'Increments';
	$lang['emajstatstructurechanged'] = 'Structure changed ?';
	$lang['emajstatverb'] = 'SQL verb';
	$lang['emajnbinsert'] = 'INSERT';
	$lang['emajnbupdate'] = 'UPDATE';
	$lang['emajnbdelete'] = 'DELETE';
	$lang['emajnbtruncate'] = 'TRUNCATE';
	$lang['emajnbrole'] = 'Roles';
	$lang['emajlogsessionwarning'] = 'This marks range covers several log sessions. Data changes may have been not recorded.';
	$lang['emajstatrows'] = 'Row changes';
	$lang['emajbrowsechanges'] = 'Browse changes';

	// Dump changes SQL generation
	$lang['emajsqlgentitle'] = 'Generate the changes dump SQL statement';
	$lang['emajsqlgenmarksinterval'] = 'Marks interval';
	$lang['emajsqlgennopk'] = 'The table has no primary key. Consolidated views are not possible.';
	$lang['emajsqlgenconsolidation'] = 'Consolidation';
	$lang['emajsqlgenconsonone'] = 'None';
	$lang['emajsqlgenconsopartial'] = 'Partial';
	$lang['emajsqlgenconsofull'] = 'Full';
	$lang['emajsqlgenconsohelp'] = 'Without consolidation, all elementary changes recorded into log tables for the selected marks frame are returned. With a (partial or full) consolidation, only the initial and the final states of each primary key are returned. With a full consolidation, no data is returned when both initial and final states are strictly equal.';
	$lang['emajsqlgenverbs'] = 'SQL verbs';
	$lang['emajsqlgenverbshelp'] = 'Without consolidation, it is possible to filter returned changes on SQL verbs.';
	$lang['emajsqlgenknownroles'] = 'Known roles:';
	$lang['emajsqlgenroleshelp'] = 'Without consolidation, it is possible to filter returned changes on roles having generated the changes. If filled, the field must contain a comma separated roles list.';
	$lang['emajsqlgentechcols'] = 'E-Maj technical columns';
	$lang['emajsqlgentechcolshelp'] = 'In the SQl statement result, most E-Maj technical columns can be masked.';
	$lang['emajsqlgencolsorder'] = 'Columns order';
	$lang['emajsqlgencolsorderlog'] = 'As the log table';
	$lang['emajsqlgencolsorderpk'] = 'Primary key ahead';
	$lang['emajsqlgencolsorderhelp'] = 'In the SQl statement result, either returned columns are in the same order as in the log table, or the primary key columns (those of the application table + emaj_tuple) are ahead.';
	$lang['emajsqlgenroworder'] = 'Rows order';
	$lang['emajsqlgenrowordertime'] = 'Chronological';
	$lang['emajsqlgenroworderhelp'] = 'In the SQl statement result, rows can be returned either in the chronological recording order or in primary keys order.';
	$lang['emajsqlgenerate'] = 'Generate SQL';

	// Group's content
	$lang['emajgroupcontent'] = 'Current content of the tables group "%s"';
	$lang['emajemptygroup'] = 'The tables group "%s" is currently empty.';
	$lang['emajpriority'] = 'Priority';
	$lang['emajlogtable'] = 'Log table';

	// Group's history
	$lang['emajgrouphistory'] = 'Tables group "%s" history';
	$lang['emajnohistory'] = 'There is no history to display for this group.';
	$lang['emajgrouphistoryorder'] = 'Most recent group creations, group drops and log sessions are placed ahead on the sheet.';
	$lang['emajnblogsessions'] = 'Log sessions';
	$lang['emajgroupcreate'] = 'Group creation';
	$lang['emajgroupdrop'] = 'Group drop';
	$lang['emajdeletedlogsessions'] = 'Some deleted log sessions';

	// Generic error messages for groups and marks checks
	$lang['emajgroupmissing'] = 'The tables group "%s" does not exist anymore!';
	$lang['emajgroupsmissing'] = '%s tables groups (%s) do not exist anymore!';
	$lang['emajgroupnotstopped'] = 'The tables group "%s" is not stopped anymore!';
	$lang['emajgroupalreadyexists'] = 'The tables group "%s" already exists!';
	$lang['emajgroupstillexists'] = 'The tables group "%s" still exists!';
	$lang['emajgroupsnotstopped'] = '%s tables group (%s) are not stopped anymore!';
	$lang['emajgroupnotstarted'] = 'The tables group "%s" is not started anymore!';
	$lang['emajgroupsnotstarted'] = '%s tables group (%s) are not started anymore!';
	$lang['emajgroupprotected'] = 'The tables group "%s" is protected.';
	$lang['emajgroupsprotected'] = '%s tables groups (%s) are protected.';
	$lang['emajinvalidmark'] = 'The supplied mark (%s) is invalid!';
	$lang['emajduplicatemarkgroup'] = 'The mark "%s" already exists in the tables group "%s"!';
	$lang['emajduplicatemarkgroups'] = 'The mark "%s" already exists in %s tables groups (%s)!';
	$lang['emajmarkmissing'] = 'The mark "%s" does not exist anymore!';
	$lang['emajmarksmissing'] = '%s marks (%s) do not exist anymore!';
	$lang['emajmissingmarkgroup'] = 'The mark does not exist anymore in the tables group "%s"!';
	$lang['emajmissingmarkgroups'] = 'The mark does not exist anymore in %s tables groups (%s)!';
	$lang['emajadoreturncode'] = 'Return code from the ADO layer = %s.';

	// Group creation
	$lang['emajcreateagroup'] = 'E-Maj: Create a tables group';
	$lang['emajconfirmcreategroup'] = 'Are you sure you want to create the tables group "%s"?';
	$lang['emajinvalidemptygroup'] = 'Error, the tables group "%s" is already created or configured!';
	$lang['emajcreategroupok'] = 'The tables group "%s" has been created.';
	$lang['emajcreategrouperr'] = 'Error while creating the tables group "%s".';

	// Groups content checks
	$lang['emajgroupconfok'] = 'The configuration of the tables group "%s" is correct.';
	$lang['emajgroupconfwithdiag'] = 'The checks performed on the tables group "%s" configuration show that:';
	$lang['emajgroupsconfok'] = 'The configuration of the tables groups "%s" is correct.';
	$lang['emajgroupsconfwithdiag'] = 'The checks performed on the tables groups "%s" configuration show that:';
	$lang['emajcheckconfgroups01'] = 'In the group "%s", the table or sequence "%s.%s" does not exist.';
	$lang['emajcheckconfgroups02'] = 'In the group "%s", the table "%s.%s" is a partitionned table (only elementary partitions are supported by E-Maj).';
	$lang['emajcheckconfgroups03'] = 'In the group "%s", the table or sequence "%s.%s" belongs to an E-Maj schema.';
	$lang['emajcheckconfgroups04'] = 'In the group "%s", the table or sequence "%s.%s" already belongs to the group "%s".';
	$lang['emajcheckconfgroups05'] = 'In the group "%s", the table "%s.%s" is a TEMPORARY table.';
	$lang['emajcheckconfgroups10'] = 'In the group "%s", the table "%s.%s" would have a duplicate emaj prefix "%s".';
	$lang['emajcheckconfgroups11'] = 'In the group "%s", the table "%s.%s" would have an already used emaj prefix "%s".';
	$lang['emajcheckconfgroups12'] = 'In the group "%s", for the table "%s.%s", the data log tablespace %s does not exist.';
	$lang['emajcheckconfgroups13'] = 'In the group "%s", for the table "%s.%s", the index log tablespace %s does not exist.';
	$lang['emajcheckconfgroups15'] = 'In the group "%s", for the table "%s.%s", the trigger "%s" does not exist.';
	$lang['emajcheckconfgroups16'] = 'In the group "%s", for the table "%s.%s", the trigger "%s" is an E-Maj trigger.';
	$lang['emajcheckconfgroups20'] = 'In the group "%s", the table "%s.%s" is an UNLOGGED table.';
	$lang['emajcheckconfgroups21'] = 'In the group "%s", the table "%s.%s" is declared WITH OIDS.';
	$lang['emajcheckconfgroups22'] = 'In the group "%s", the table "%s.%s" has no PRIMARY KEY.';
	$lang['emajcheckconfgroups30'] = 'in the group "%s", for the sequence "%s.%s", the secondary log schema suffix is not NULL.';
	$lang['emajcheckconfgroups31'] = 'In the group "%s", for the sequence "%s.%s", the emaj names prefix is not NULL.';
	$lang['emajcheckconfgroups32'] = 'In the group "%s", for the sequence "%s.%s", the data log tablespace is not NULL.';
	$lang['emajcheckconfgroups33'] = 'In the group "%s", for the sequence "%s.%s", the index log tablespace is not NULL.';

	// Group drop
	$lang['emajdropagroup'] = 'E-Maj: Drop a tables group';
	$lang['emajconfirmdropgroup'] = 'Are you sure you want to drop the tables group "%s"?';
	$lang['emajdropgroupok'] = 'The tables group "%s" has been dropped.';
	$lang['emajdropgrouperr'] = 'Error while dropping tables group "%s".';

	// Groups drop
	$lang['emajdropgroups'] = 'E-Maj: Drop tables groups';
	$lang['emajconfirmdropgroups'] = 'Are you sure you want to drop the tables groups "%s"?';
	$lang['emajdropgroupsok'] = 'The tables groups "%s" have been dropped.';
	$lang['emajdropgroupserr'] = 'Error while dropping tables groups "%s".';

	// Group forget
	$lang['emajforgetagroup'] = 'E-Maj: Erase a tables group from histories';
	$lang['emajconfirmforgetgroup'] = 'Are you sure you want to erase the tables group "%s" from histories?';
	$lang['emajforgetgroupok'] = 'The tables group "%s" has been erased from histories.';
	$lang['emajforgetgrouperr'] = 'Error while erasing the tables group "%s" from histories.';

	// Export groups configuration
	$lang['emajexportgroupsconf'] = 'Export a tables groups configuration';
	$lang['emajexportgroupsconfselect'] = 'Select the tables groups whose configuration will be exported on a local file.';
	$lang['emajexportgroupserr'] = 'Error while exporting tables groups "%s".';

	// Import groups configuration
	$lang['emajimportgroupsconf'] = 'Import a tables groups configuration';
	$lang['emajimportgroupsinfile'] = 'Select the tables groups to import from the file "%s":';
	$lang['emajimportgroupsinfileerr'] = 'Some errors have been detected in the file "%s":';
	$lang['emajcheckjsongroupsconf201'] = 'The JSON structure does not contain any "tables_groups" array.';
	$lang['emajcheckjsongroupsconf202'] = 'The JSON structure references several times the tables groups "%s".';
	$lang['emajcheckjsongroupsconf210'] = 'The tables group #%s has no "group" attribute.';
	$lang['emajcheckjsongroupsconf211'] = 'For the tables group "%s", the keyword "%s" is unknown.';
	$lang['emajcheckjsongroupsconf212'] = 'For the tables group "%s", the "is_rollbackable" attribute is not a boolean.';
	$lang['emajcheckjsongroupsconf220'] = 'In the tables group "%s", the table #%s has no "schema" attribute.';
	$lang['emajcheckjsongroupsconf221'] = 'In the tables group "%s", the table #%s has no "table" attribute.';
	$lang['emajcheckjsongroupsconf222'] = 'In the tables group "%s" and for the table %s.%s, the keyword "%s" is unknown.';
	$lang['emajcheckjsongroupsconf223'] = 'In the tables group "%s" and for the table %s.%s, the "priority" attribute is not a number.';
	$lang['emajcheckjsongroupsconf224'] = 'In the tables group "%s" and for the table %s.%s, the trigger #%s has no "trigger" attribute.';
	$lang['emajcheckjsongroupsconf225'] = 'In the tables group "%s" and for a trigger of the table %s.%s, the keyword "%s" is unknown.';
	$lang['emajcheckjsongroupsconf226'] = 'In the tables group "%s" and for the table %s.%s, the trigger #%s is not a string.';
	$lang['emajcheckjsongroupsconf227'] = 'In the tables group "%s" and for the table %s.%s, the "ignored_triggers" attribute is not an array.';
	$lang['emajcheckjsongroupsconf230'] = 'In the tables group "%s", the sequence #%s has no "schema" attribute.';
	$lang['emajcheckjsongroupsconf231'] = 'In the tables group "%s", the sequence #%s has no "sequence" attribute.';
	$lang['emajcheckjsongroupsconf232'] = 'In the tables group "%s" and for the sequence %s.%s, the keyword "%s" is unknown.';
	$lang['emajgroupsconfimport250'] = 'The tables group "%s" to import is not referenced in the JSON structure.';
	$lang['emajgroupsconfimport251'] = 'The tables group "%s" already exists.';
	$lang['emajgroupsconfimport252'] = 'Changing the type of the tables group "%s" is not allowed.';
	$lang['emajgroupsconfimport260'] = 'In the group "%s" and for the table %s.%s, the trigger %s does not exist.';
	$lang['emajgroupsconfimport261'] = 'In the group "%s" and for the table %s.%s, the trigger %s is an E-Maj trigger.';
	$lang['emajgroupsconfimportpreperr'] = 'Importing the configuration of tables groups "%s" from the file  "%s" has failed for the following reasons:';
	$lang['emajgroupsconfimported'] = '%s tables groups have been imported from the file "%s".';
	$lang['emajgroupsconfimporterr'] = 'Error while importing tables groups from file "%s"';

	// Group alter
	$lang['emajaltergroups'] = 'E-Maj: Apply configuration changes';
	$lang['emajalteraloggingroup'] = 'The group "%s" is in LOGGING state. You can specify a mark name.';
	$lang['emajconfirmaltergroup'] = 'Are you sure you want to apply the configuration changes for the tables group "%s"?';
	$lang['emajcantaltergroup'] = 'Applying the configuration changes for the group "%s" would generate actions that cannot be executed on LOGGING group. Stop the group before altering it.';
	$lang['emajaltergroupok'] = 'The configuration changes for the tables group "%s" have been applied.';
	$lang['emajaltergrouperr'] = 'Error during tables group "%s" configuration change!';

	// Groups alter
	$lang['emajalterallloggingroups'] = 'The groups "%s" are in LOGGING state. You can specify a mark name.';
	$lang['emajconfirmaltergroups'] = 'Are you sure you want to apply the configuration changes for the tables groups "%s"?';
	$lang['emajaltergroupsok'] = 'The configuration changes for the tables groups "%s" have been applied.';
	$lang['emajaltergroupserr'] = 'Error during tables groups "%s" configuration change!';

	// Group comment
	$lang['emajcommentagroup'] = 'E-Maj: Record a comment for a tables group';
	$lang['emajcommentgroup'] = 'Enter, modify or erase the comment for tables group "%s".';
	$lang['emajcommentgroupok'] = 'The comment for the tables group "%s" has been recorded.';
	$lang['emajcommentgrouperr'] = 'Error while commenting the tables group "%s".';

	// Group start
	$lang['emajstartagroup'] = 'E-Maj: Start a tables group';
	$lang['emajconfirmstartgroup'] = 'Starting the tables group "%s"';
	$lang['emajinitmark'] = 'Initial mark';
	$lang['emajoldlogsdeletion'] = 'Old logs deletion';
	$lang['emajstartgroupok'] = 'The tables group "%s" is started with the mark "%s".';
	$lang['emajstartgrouperr'] = 'Error while starting tables group "%s".';
	$lang['emajstartgrouperr2'] = 'Error while starting tables group "%s" with the mark "%s".';

	// Groups start
	$lang['emajstartgroups'] = 'E-Maj: Start tables groups';
	$lang['emajconfirmstartgroups'] = 'Starting the tables groups "%s"';
	$lang['emajstartgroupsok'] = 'The tables groups "%s" are started with the mark "%s".';
	$lang['emajstartgroupserr'] = 'Error while starting the tables groups "%s".';
	$lang['emajstartgroupserr2'] = 'Error while starting the tables groups "%s" with the mark "%s".';

	// Group stop
	$lang['emajstopagroup'] = 'E-Maj: Stop a tables group';
	$lang['emajconfirmstopgroup'] = 'Stopping the tables group "%s"';
	$lang['emajstopmark'] = 'Final mark';
	$lang['emajforcestop'] = 'Forced stop (in case of problem only)';
	$lang['emajstopgroupok'] = 'The tables group "%s" has been stopped.';
	$lang['emajstopgrouperr'] = 'Error while stopping the tables group "%s".';
	$lang['emajstopgrouperr2'] = 'Error while stopping the tables group "%s" with the mark "%s".';

	// Groups stop
	$lang['emajstopgroups'] = 'E-Maj: Stop tables groups';
	$lang['emajconfirmstopgroups'] = 'Stopping the tables groups "%s"';
	$lang['emajstopgroupsok'] = 'The tables groups "%s" have been stopped.';
	$lang['emajstopgroupserr'] = 'Error while stopping the tables groups "%s".';
	$lang['emajstopgroupserr2'] = 'Error while stopping the tables groups "%s" with the mark "%s".';

	// Group reset
	$lang['emajresetagroup'] = 'E-Maj: Reset a tables group';
	$lang['emajconfirmresetgroup'] = 'Are you sure you want to reset the tables group "%s"?';
	$lang['emajresetgroupok'] = 'The tables group "%s" has been reset.';
	$lang['emajresetgrouperr'] = 'Error while resetting the tables group "%s".';

	// Groups reset
	$lang['emajresetgroups'] = 'E-Maj: Reset tables groups';
	$lang['emajconfirmresetgroups'] = 'Are you sure you want to reset the tables groups "%s"?';
	$lang['emajresetgroupsok'] = 'The tables group "%s" have been reset.';
	$lang['emajresetgroupserr'] = 'Error while resetting the tables groups "%s".';

	// Group protect
	$lang['emajprotectgroupok'] = 'The tables group "%s" is now protected against rollbacks.';
	$lang['emajprotectgrouperr'] = 'Error while protecting the tables group "%s".';

	// Groups protect
	$lang['emajprotectgroupsok'] = 'The tables groups "%s" are now protected against rollbacks.';
	$lang['emajprotectgroupserr'] = 'Error while protecting the tables groups "%s".';

	// Group unprotect
	$lang['emajunprotectgroupok'] = 'The tables group "%s" is now unprotected.';
	$lang['emajunprotectgrouperr'] = 'Error while unprotecting the tables group "%s".';

	// Groups unprotect
	$lang['emajunprotectgroupsok'] = 'The tables groups "%s" are now unprotected.';
	$lang['emajunprotectgroupserr'] = 'Error while unprotecting the tables groups "%s".';

	// Set Mark for one group
	$lang['emajsetamark'] = 'E-Maj: Set a mark';
	$lang['emajconfirmsetmarkgroup'] = 'Setting a mark for the tables group "%s":';
	$lang['emajsetmarkgroupok'] = 'The mark "%s" has been set for the tables group "%s".';
	$lang['emajsetmarkgrouperr'] = 'Error while setting a for the tables group "%s".';
	$lang['emajsetmarkgrouperr2'] = 'Error while setting the mark "%s" for the tables group "%s".';

	// Set Mark for several groups
	$lang['emajconfirmsetmarkgroups'] = 'Setting a mark for the tables groups "%s":';
	$lang['emajsetmarkgroupsok'] = 'The mark "%s" has been set for the tables groups "%s".';
	$lang['emajsetmarkgroupserr'] = 'Error while setting a mark for the tables groups "%s".';
	$lang['emajsetmarkgroupserr2'] = 'Error while setting the mark "%s" for the tables groups "%s".';

	// Group rollback
	$lang['emajrlbkagroup'] = 'E-Maj: Rollback a tables group';
	$lang['emajconfirmrlbkgroup'] = 'Rollbacking the tables group "%s" to the mark "%s"';
	$lang['emajunknownestimate'] = 'unknown';
	$lang['emajdurationminutesseconds'] = '%s min %s s';
	$lang['emajdurationhoursminutes'] = '%s h %s min';
	$lang['emajdurationovertendays'] = '> 10 days';
	$lang['emajselectmarkgroup'] = 'Rollbacking the tables group "%s" to the mark: ';
	$lang['emajrlbkthenmonitor'] = 'Rollback and monitor';
	$lang['emajcantrlbkinvalidmarkgroup'] = 'The mark "%s" is not valid.';
	$lang['emajreachaltergroup'] = 'Rollbacking the tables group "%s" to the mark "%s" would reach a point in time prior alter_group operations. Please confirm the rollback.';
	$lang['emajautorolledback'] = 'Automatically rolled back?';
	$lang['emajrlbkgroupok'] = 'The tables group "%s" has been rollbacked to the mark "%s".';
	$lang['emajrlbkgrouperr'] = 'Error while rollbacking the tables group "%s".';
	$lang['emajrlbkgrouperr2'] = 'Error while rollbacking the tables group "%s" to the mark "%s".';
	$lang['emajestimrlbkgrouperr'] = 'Error while estimating the rollback duration for the tables group "%s" to the mark "%s".';
	$lang['emajbadconfparam'] = 'Error: asynchronous rollback is not possible anymore. Check the dblink extension exists and both the pathname of the psql command (%s) and the temporary directory (%s) configuration parameters are correct.';
	$lang['emajasyncrlbkstarted'] = 'Rollback #%s started.';
	$lang['emajrlbkgroupreport'] = 'Rollback execution report for the tables group "%s" to the mark "%s"';

	// Groups rollback
	$lang['emajrlbkgroups'] = 'E-Maj: Rollback tables groups';
	$lang['emajselectmarkgroups'] = 'Rollbacking the tables groups "%s" to the mark: ';
	$lang['emajnomarkgroups'] = 'No common mark for the tables groups "%s" can be used for a rollback.';
	$lang['emajcantrlbkinvalidmarkgroups'] = 'Rollbacking the tables groups "%s" is not possible. The mark "%s" is not valid.';
	$lang['emajreachaltergroups'] = 'Rollbacking the tables groups "%s" to the mark "%s" would reach a point in time prior alter_group operations. Please confirm the rollback.';
	$lang['emajrlbkgroupsok'] = 'The tables groups "%s" have been rollbacked to mark "%s".';
	$lang['emajrlbkgroupserr'] = 'Error while rollbacking tables groups "%s".';
	$lang['emajrlbkgroupserr2'] = 'Error while rollbacking tables groups "%s" to mark "%s".';
	$lang['emajestimrlbkgroupserr'] = 'Error while estimating the rollback duration for the tables groups "%s" to the mark "%s".';
	$lang['emajrlbkgroupreport'] = 'Rollback execution report for the tables groups "%s" to the mark "%s"';

	// Elementary alter group actions previously executed, reported at rollback time 
	$lang['emajalteredremovetbl'] = 'The table "%s.%s" has been removed from the tables group "%s"';
	$lang['emajalteredremoveseq'] = 'The sequence "%s.%s" has been removed from the tables group "%s"';
	$lang['emajalteredrepairtbl'] = 'E-Maj objects for the table "%s.%s" have been repaired';
	$lang['emajalteredrepairseq'] = 'E-Maj objects for the sequence "%s.%s" have been repaired';
	$lang['emajalteredchangetbllogschema'] = 'The E-Maj log schema for the table "%s.%s" has been changed';
	$lang['emajalteredchangetblnamesprefix'] = 'The E-Maj names prefix for the table "%s.%s" has been changed';
	$lang['emajalteredchangetbllogdatatsp'] = 'The tablespace for the log data files of the table "%s.%s" has been changed';
	$lang['emajalteredchangetbllogindextsp'] = 'The tablespace for the log index files of the table "%s.%s" has been changed';
	$lang['emajalteredassignrel'] = 'The table or sequence "%s.%s" has been moved from the tables group "%s" to the tables group "%s"';
	$lang['emajalteredchangerelpriority'] = 'The E-Maj priority for the table "%s.%s" has been changed';
	$lang['emajalteredchangeignoredtriggers'] = 'the triggers to be ignored at rollback for the table "%s.%s" have been changed';
	$lang['emajalteredmovetbl'] = 'The table "%s.%s" has been moved from the tables groupe "%s" to the tables group "%s"';
	$lang['emajalteredmoveseq'] = 'The sequence "%s.%s" has been moved from the tables groupe "%s" to the tables group "%s"';
	$lang['emajalteredaddtbl'] = 'The table "%s.%s" has been added to the tables group "%s"';
	$lang['emajalteredaddseq'] = 'The sequence "%s.%s" has been added to the tables group "%s"';

	// Protect mark
	$lang['emajprotectmarkok'] = 'The mark "%s" for the tables group "%s" is now protected against rollbacks.';
	$lang['emajprotectmarkerr'] = 'Error while protecting the mark "%s" for the tables group "%s".';

	// Unprotect mark
	$lang['emajunprotectmarkok'] = 'The mark "%s" for the tables group "%s" is now unprotected.';
	$lang['emajunprotectmarkerr'] = 'Error while unprotecting the mark "%s" for the tables group "%s".';

	// Comment mark
	$lang['emajcommentamark'] = 'E-Maj: Record a comment for a mark';
	$lang['emajcommentmark'] = 'Enter, modify or erase the comment for the mark "%s" of the tables group "%s"';
	$lang['emajcommentmarkok'] = 'The comment for the mark "%s" of the tables group "%s" has been recorded.';
	$lang['emajcommentmarkerr'] = 'Error while commenting the mark "%s" of the tables group "%s".';

	// Mark renaming
	$lang['emajrenameamark'] = 'E-Maj : Rename a mark';
	$lang['emajconfirmrenamemark'] = 'Renaming the mark "%s" of the tables group "%s"';
	$lang['emajnewnamemark'] = 'New name';
	$lang['emajrenamemarkok'] = 'The mark "%s" of the tables group "%s" has been renamed into "%s".';
	$lang['emajrenamemarkerr'] = 'Error while renaming the mark "%s" of the tables group "%s" into "%s".';
	$lang['emajrenamemarkerr2'] = 'Error while renaming the mark "%s" of the tables group "%s" into "%s".';

	// Mark deletion
	$lang['emajdeleteamark'] = 'E-Maj: Delete a mark';
	$lang['emajconfirmdeletemark'] = 'Are you sure you want to delete the mark "%s" for the tables group "%s"?';
	$lang['emajdeletemarkok'] = 'The mark "%s" has been deleted for the tables group "%s".';
	$lang['emajdeletemarkerr'] = 'Error while deleting the mark "%s" of the tables group "%s".';

	// Marks deletion
	$lang['emajdeletemarks'] = 'E-Maj: Delete marks';
	$lang['emajconfirmdeletemarks'] = 'Are you sure you want to delete these %s marks for the tables group "%s"?';
	$lang['emajdeletemarksok'] = 'The %s marks have been deleted for the tables group "%s".';
	$lang['emajdeletemarkserr'] = 'Error while deleting marks "%s" of the tables group "%s".';

	// Marks before mark deletion
	$lang['emajdelmarksprior'] = 'E-Maj: Delete marks';
	$lang['emajconfirmdelmarksprior'] = 'Are you sure you want to delete all marks and logs preceeding the mark "%s" for the tables group "%s"?';
	$lang['emajdelmarkspriorok'] = 'All (%s) marks preceeding mark "%s" have been deleted for the tables group "%s".';
	$lang['emajdelmarkspriorerr'] = 'Error while deleting all marks preceeding mark "%s" for the tables group "%s".';

?>
