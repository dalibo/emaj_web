<?php

	/**
	 * English language file for Emaj_web.
	 * Use this as a basis for new translations.
	 */

	// Language and character set
	$lang['applocale'] = 'en-US';
	$lang['applangdir'] = 'ltr';

	// Welcome
	$lang['strintro'] = 'Welcome to %s %s, the web client for';
	$lang['strlink'] = 'Some links:';
	$lang['strpgsqlhome'] = 'PostgreSQL Homepage';
	$lang['strpgsqlhome_url'] = 'http://www.postgresql.org/';
	$lang['stremajdoc'] = 'Online E-Maj documentation';
	$lang['stremajproject'] = 'E-Maj on github';
	$lang['stremajwebproject'] = 'Emaj_web on github';

	// Basic strings
	$lang['strlogin'] = 'Login';
	$lang['strloginfailed'] = 'Login failed';
	$lang['strlogindisallowed'] = 'Login disallowed for security reasons.';
	$lang['strserver'] = 'Server';
	$lang['strservers'] = 'Servers';
	$lang['strconfiguredservers'] = 'Configured PostgreSQL servers';
	$lang['strgroupservers'] = 'Servers in group "%s"';
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
	$lang['strcomment'] = 'Comment';
	$lang['strdefault'] = 'Default';
	$lang['stralter'] = 'Alter';
	$lang['strok'] = 'OK';
	$lang['strcancel'] = 'Cancel';
	$lang['strreset'] = 'Reset';
	$lang['strselect'] = 'Select';
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
	$lang['strshow'] = 'Show';
	$lang['strlanguage'] = 'Language';
	$lang['strencoding'] = 'Encoding';
	$lang['strvalue'] = 'Value';
	$lang['strsql'] = 'SQL';
	$lang['strexecute'] = 'Execute';
	$lang['strconfirm'] = 'Confirm';
	$lang['strellipsis'] = '...';
	$lang['strseparator'] = ': ';
	$lang['strexpand'] = 'Expand';
	$lang['strcollapse'] = 'Collapse';
	$lang['strfind'] = 'Find';
	$lang['strrefresh'] = 'Refresh';
	$lang['strdownload'] = 'Download';
	$lang['streditsql'] = 'Edit SQL';
	$lang['strruntime'] = 'Total runtime: %s ms';
	$lang['strpaginate'] = 'Paginate results';
	$lang['struploadscript'] = 'or upload an SQL script:';
	$lang['strtrycred'] = 'Use these credentials for all servers';
	$lang['strconfdropcred'] = 'For security reason, disconnecting will destroy your shared login information. Are you sure you want to disconnect ?';
	$lang['stractionsonmultiplelines'] = 'Actions on multiple lines';
	$lang['strselectall'] = 'Select all';
	$lang['strunselectall'] = 'Unselect all';
	$lang['strstart'] = 'Start';
	$lang['strstop'] = 'Stop';
	$lang['strgotoppage'] = 'back to top';
	$lang['strtheme'] = 'Theme';
	$lang['strselect'] = 'Select';
	$lang['stractionsonselectedobjects'] = 'Actions on selected objects (0)';
	$lang['strall'] = 'All';
	$lang['strnone'] = 'None';
	$lang['strinvert'] = 'Invert';
	
	// User-supplied SQL history
	$lang['strhistory'] = 'History';
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
	$lang['strnoframes'] = 'This application works best with a frames-enabled browser, but can be used without frames by following the link below.';
	$lang['strnoframeslink'] = 'Use without frames';
	$lang['strbadconfig'] = 'Your config.inc.php is out of date. You will need to regenerate it from the new config.inc.php-dist.';
	$lang['strnotloaded'] = 'Your PHP installation does not support PostgreSQL. You need to recompile PHP using the --with-pgsql configure option.';
	$lang['strpostgresqlversionnotsupported'] = 'Version of PostgreSQL not supported. Please upgrade to version %s or later.';
	$lang['strbadschema'] = 'Invalid schema specified.';
	$lang['strsqlerror'] = 'SQL error:';
	$lang['strinstatement'] = 'In statement:';
	$lang['strinvalidparam'] = 'Invalid script parameters.';
	$lang['strnodata'] = 'No rows found.';
	$lang['strnoobjects'] = 'No objects found.';
	$lang['strcannotdumponwindows'] = 'Dumping of complex table and schema names on Windows is not supported.';
	$lang['strinvalidserverparam'] = 'Attempt to connect with invalid server parameter, possibly someone is trying to hack your system.'; 
	$lang['strnoserversupplied'] = 'No server supplied!';
	$lang['strconnectionfail'] = 'Can not connect to server.';

	// Tables
	$lang['strtable'] = 'Table';
	$lang['strtables'] = 'Tables';
	$lang['strnotables'] = 'No tables found.';
	$lang['strnofkref'] = 'There is no matching value in the foreign key %s.';
	$lang['strselectallfields'] = 'Select all fields';
	$lang['strselectneedscol'] = 'You must show at least one column.';
	$lang['strselectunary'] = 'Unary operators cannot have values.';
	$lang['strestimatedrowcount'] = 'Estimated row count';

	// Users
	$lang['strusername'] = 'Username';
	$lang['strpassword'] = 'Password';
	
	// Groups
	$lang['strgroup'] = 'Group';
	$lang['strgroupgroups'] = 'Groups in group "%s"';

	// Roles
	$lang['strrole'] = 'Role';
	$lang['strroles'] = 'Roles';

	// Databases
	$lang['strdatabase'] = 'Database';
	$lang['strdatabases'] = 'Databases';
	$lang['strnodatabases'] = 'No databases found.';
	$lang['strentersql'] = 'Enter the SQL to execute below:';
	$lang['strsqlexecuted'] = 'SQL executed.';
	$lang['strallobjects'] = 'All objects';

	// Views
	$lang['strviews'] = 'Views';
	$lang['strcreateview'] = 'Create view';

	// Sequences
	$lang['strsequence'] = 'Sequence';
	$lang['strsequences'] = 'Sequences';
	$lang['strnosequences'] = 'No sequences found.';
	$lang['strlastvalue'] = 'Last value';
	$lang['strincrementby'] = 'Increment by';	
	$lang['strstartvalue'] = 'Start value';
	$lang['strmaxvalue'] = 'Max value';
	$lang['strminvalue'] = 'Min value';
	$lang['strcachevalue'] = 'Cache value';
	$lang['strlogcount'] = 'Log count';
	$lang['strcancycle'] = 'Can cycle?';
	$lang['striscalled'] = 'Will increment last value before returning next value (is_called)?';
	
	// Indexes
	$lang['strindexes'] = 'Indexes';

	// Rules
	$lang['strrules'] = 'Rules';

	// Constraints
	$lang['strconstraints'] = 'Constraints';

	// Functions
	$lang['strfunctions'] = 'Functions';

	// Triggers
	$lang['strtriggers'] = 'Triggers';

	// Types
	$lang['strtype'] = 'Type';
	$lang['strtypes'] = 'Types';

	// Schemas
	$lang['strschema'] = 'Schema';
	$lang['strschemas'] = 'Schemas';
	$lang['strnoschemas'] = 'No schemas found.';
	$lang['strsearchpath'] = 'Schema search path';

	// Domains
	$lang['strdomains'] = 'Domains';

	// Operators
	$lang['stroperator'] = 'Operator';
	$lang['stroperators'] = 'Operators';

	// Conversions
	$lang['strconversions'] = 'Conversions';
	
	// Languages
	$lang['strlanguages'] = 'Languages';
	
	// Aggregates
	$lang['straggregates'] = 'Aggregates';

	// Operator Classes
	$lang['stropclasses'] = 'Op Classes';

	// Tablespaces
	$lang['strtablespace'] = 'Tablespace';

	// Miscellaneous
	$lang['strtopbar'] = 'Connection: %s:%s - role "%s"';
	$lang['strlogintitle'] = 'Login to %s';
	$lang['strlogoutmsg'] = 'Logged out of %s';
	$lang['strloading'] = 'Loading...';
	$lang['strerrorloading'] = 'Error Loading';
	$lang['strclicktoreload'] = 'Click to reload';

	//Plugins
	$lang['strpluginnotfound'] = 'Error: plugin \'%s\' not found. Check if this plugin exists in the plugins/ directory, or if this plugins has a plugin.php file. Plugin\'s names are case sensitive';
	$lang['stractionnotfound'] = 'Error: action \'%s\' not found in the \'%s\' plugin, or it was not specified as an action.';
	$lang['strhooknotfound'] = 'Error: hook \'%s\' is not avaliable.';
//
// E-Maj strings
//
	// Basic strings 
	$lang['emajplugin'] = 'E-Maj plugin';
	$lang['emajnotavail'] = 'Sorry, E-Maj is not available or accessible for this database. More details in the %s tab.';
	$lang['emajstate'] = 'State';
	$lang['emajnoselectedgroup'] = 'No tables group has been selected!';
	$lang['emajgroup'] = 'Group';
	$lang['emajgroups'] = 'Groups';
	$lang['emajmark'] = 'Mark';
	$lang['emajmarks'] = 'Marks';
	$lang['emajgrouptype'] = 'Group type';
	$lang['emajrollbacktype'] = 'Rollback type';
	$lang['emajauditonly'] = 'AUDIT-ONLY';
	$lang['emajrollbackable'] = 'ROLLBACK-ABLE';
	$lang['emajunlogged'] = 'unlogged';
	$lang['emajlogged'] = 'logged';
	$lang['emajlogging'] = 'Logging';
	$lang['emajidle'] = 'Idle';
	$lang['emajactive'] = 'Active';
	$lang['emajdeleted'] = 'Deleted';
	$lang['emajprotected'] = 'Protected against rollback';
	$lang['emajpagebottom'] = 'Go to bottom';
	$lang['emajlogsize'] = 'Log size';
	$lang['emajrequiredfield'] = 'Required field';
	$lang['emajestimates'] = 'Estimates';
	$lang['emajfrom'] = 'From';
	$lang['emajto'] = 'To';

	// E-Maj tabs
	$lang['emajenvir'] = 'E-Maj environment';
	$lang['emajgroupsconf'] = 'Groups configuration';
	$lang['emajrlbkop'] = 'Rollback operations';
	$lang['emajlogstat'] = 'Log statistics';

	// Common help messages
	$lang['emajmarknamehelp'] = 'The mark name must be unique within the group. A % character represents the current time (format hh.mn.ss.ms).';
	$lang['emajmarknamemultihelp'] = 'The mark name must be unique within the concerned groups. A % character represents the current time (format hh.mn.ss.ms).';

	// E-Maj environment
	$lang['emajenvironment'] = 'E-Maj environment';
	$lang['emajextnotavailable'] = 'The E-Maj software is not installed on this PostgreSQL instance. Contact your database administrator.';
	$lang['emajextnotcreated'] = 'The emaj extension is not created in this database. Contact your database administrator.';
	$lang['emajnogrant'] = 'Your connection role has no E-Maj rights. Use another role or contact your database administrator.';
	$lang['emajcharacteristics'] = 'Characteristics';
	$lang['emajversions'] = 'Versions';
	$lang['emajpgversion'] = 'PostgreSQL version: ';
	$lang['emajversion'] = 'E-Maj version: ';
	$lang['emajasextension'] = 'installed as extension';
	$lang['emajasscript'] = 'installed by script';
	$lang['emajtooold'] = 'Sorry, this E-Maj version (%s) is too old. The minimum version supported by this plugin is %s.';
	$lang['emajversionmorerecent'] = 'A more recent E-Maj version exists. Contact your database administrator.';
	$lang['emajwebversionmorerecent'] = 'A more recent Emaj_web version probably exists. Contact your database administrator.';
	$lang['emajdiskspace'] = 'Disk space used by the E-Maj environment: %s of the current database.';
	$lang['emajchecking'] = 'E-Maj environment consistency';
	$lang['emajdiagnostics'] = 'Diagnostics';

	// Groups' content setup
	$lang['emajgroupsconfiguration'] = 'Tables groups\' configuration';
	$lang['emajschemaslist'] = 'Application schemas list';
	$lang['emajunknownobject'] = 'This object is referenced in the emaj_group_def table but is not created.';
	$lang['emajunsupportedobject'] = 'This object type is not supported by E-Maj (unlogged table, table with OIDS, partition table,...).';
	$lang['emajtblseqofschema'] = 'Tables and sequences in schema "%s"';
	$lang['emajassign'] = 'Assign';
	$lang['emajremove'] = 'Remove';
	$lang['emajlogschemasuffix'] = 'Log schema suffix';
	$lang['emajlogdattsp'] = 'Log tablespace';
	$lang['emajlogidxtsp'] = 'Log index tablespace';
	$lang['emajspecifytblseqtoassign'] = 'Specify at least one table or sequence to assign';
	$lang['emajtblseqyetgroup'] = 'Error, "%s.%s" is already assigned to a tables group.';
	$lang['emajtblseqbadtype'] = 'Error, type of "%s.%s" is not supported by E-Maj.';
	$lang['emajassigntblseq'] = 'E-Maj: Assign tables / sequences to a tables group';
	$lang['emajconfirmassigntblseq'] = 'Assign :';
	$lang['emajthetable'] = 'the "%s.%s" table';
	$lang['emajthesequence'] = 'the "%s.%s" sequence';
	$lang['emajfromgroup'] = 'from the group "%s"';
	$lang['emajenterpriority'] = 'Processing priority';
	$lang['emajpriorityhelp'] = 'Tables and sequences are processed in priority ascending order, and in names alphabetic order if no priority is defined.';
	$lang['emajenterlogschema'] = 'Log schema suffix';
	$lang['emajlogschemahelp'] = 'A log schema contains log tables, sequences and functions. The default log schema is \'emaj\'. If a suffix is defined for the table, its objects will be hosted in the schema \'emaj\' + suffix.';
	$lang['emajenternameprefix'] = 'E-Maj objects name prefix';
	$lang['emajnameprefixhelp'] = 'By default, log objects names are prefixed by &lt;schema&gt;_&lt;table&gt;. But another prefix can be defined for the table. It must be unique in the database.';
	$lang['emajenterlogdattsp'] = 'Log table tablespace';
	$lang['emajenterlogidxtsp'] = 'Log index tablespace';
	$lang['emajspecifytblseqtoupdate'] = 'Specify at least one table or sequence to update';
	$lang['emajupdatetblseq'] = 'E-Maj: Update properties of a table / sequence in a tables group';
	$lang['emajspecifytblseqtoremove'] = 'Specify at least one table or sequence to remove';
	$lang['emajtblseqnogroup'] = 'Error, "%s.%s" is not currently assigned to any tables group.';
	$lang['emajremovetblseq'] = 'E-Maj: Remove tables / sequences from tables groups';
	$lang['emajconfirmremove1tblseq'] = 'Are you sure you want to remove %s from the tables group "%s"?';
	$lang['emajconfirmremovetblseq'] = 'Are you sure you want to remove:';
	$lang['emajmodifygroupok'] = 'The configuration change is recorded. It will take effect when the concerned tables groups will be (re)created or when the configuration changes will be applied for these groups.';
	$lang['emajmodifygrouperr'] = 'Error while updating tables groups content.';

	// List Groups
	$lang['emajgrouplist'] = 'Tables groups list';
	$lang['emajidlegroups'] = 'Tables groups in "IDLE" state ';
	$lang['emajlogginggroups'] = 'Tables groups in "LOGGING" state ';
	$lang['emajconfiguredgroups'] = 'Configured but not yet created tables groups ';
	$lang['emajlogginggrouphelp'] = 'When a tables group is in \'logging\' state, the row insertions, updates and deletions on its tables are recorded.';
 	$lang['emajidlegrouphelp'] = 'When a tables group is in \'idle\' state, the row insertions, updates and deletions on its tables are NOT recorded.';
	$lang['emajconfiguredgrouphelp'] = 'The group configuration defines the tables and sequences that will compose it. Once \'configured\', the group must be \'created\' in order to prepare all abjects that will be needed for its use (log tables, functions,...).';
	$lang['emajcreationdatetime'] = 'Creation date/time';
	$lang['emajnbtbl'] = '# tables';
	$lang['emajnbseq'] = '# sequences';
	$lang['emajnbmark'] = '# marks';
	$lang['emajdetail'] = 'Detail';
	$lang['emajsetmark'] = 'Set a mark';
	$lang['emajsetcomment'] = 'Set a comment';
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

	// Rollback activity
	$lang['emajrlbkoperations'] = 'E-Maj Rollbacks';
	$lang['emajrlbkid'] = 'Rlbk Id.';
	$lang['emajrlbkstart'] = 'Rollback start';
	$lang['emajrlbkend'] = 'Rollback end';
	$lang['emajduration'] = 'Duration';
	$lang['emajmarksetat'] = 'Mark set at';
	$lang['emajislogged'] = 'Logged ?';
	$lang['emajnbsession'] = 'Nb sessions';
	$lang['emajnbproctable'] = 'Nb processed tables';
	$lang['emajnbprocseq'] = 'Nb processed sequences';
	$lang['emajcurrentduration'] = 'Current duration';
	$lang['emajestimremaining'] = 'Estimated remaining';
	$lang['emajpctcompleted'] = '% completed';
	$lang['emajinprogressrlbk'] = 'In progress E-Maj rollbacks';
	$lang['emajrlbkmonitornotavailable'] = 'In progress rollbacks monitoring is not available.';
	$lang['emajcompletedrlbk'] = 'Completed E-Maj rollbacks';
	$lang['emajnbtabletoprocess'] = 'Nb tables to process';
	$lang['emajnbseqtoprocess'] = 'Nb sequences to process';
	$lang['emajnorlbk'] = 'No E-Maj rollback.';
	$lang['emajfilterrlbk1'] = 'Display the';
	$lang['emajfilterrlbk2'] = 'most recent';
	$lang['emajfilterrlbk3'] = 'completed since less than';
	$lang['emajfilterrlbk4'] = 'hours';
	$lang['emajfilter'] = 'Filter';
	$lang['emajconsolidablerlbk'] = 'Consolidable E-Maj logged rollbacks';
	$lang['emajtargetmark'] = 'Target mark';
	$lang['emajendrollbackmark'] = 'End rollback mark';
	$lang['emajnbintermediatemark'] = 'Nb intermediate marks';
	$lang['emajconsolidate'] = 'Consolidate';
	$lang['emajconsolidaterlbk'] = 'Consolidate a logged rollback';
	$lang['emajconfirmconsolidaterlbk'] = 'Are you sure you want to consolidate the rollback ended with the mark "%s" of the tables group "%s"?';
	$lang['emajconsolidaterlbkok'] = 'The rollback ended with the mark "%s" of the tables group "%s" has been consolidated.';
	$lang['emajconsolidaterlbkerr'] = 'Error while consolidating the rollback ended by the mark "%s" of the tables group "%s"!';

	// Group's properties and marks
	$lang['emajgrouppropertiesmarks'] = 'Tables group "%s" properties and marks';
	$lang['emajgroupproperties'] = 'Tables group "%s" properties';
	$lang['emajcontent'] = 'Content';
	$lang['emajgroupmarks'] = 'Tables group "%s" marks';
	$lang['emajtimestamp'] = 'Date/Time';
	$lang['emajnbchanges'] = '# row<br>changes';	
	$lang['emajcumchanges'] = 'Cumulative<br>changes';
	$lang['emajsimrlbk'] = 'Simulate Rollback';
	$lang['emajrlbk'] = 'Rollback';
	$lang['emajfirstmark'] = 'First mark';
	$lang['emajrename'] = 'Rename';
	$lang['emajnomark'] = 'The tables group has no mark';
	$lang['emajprotect'] = 'Protect';
	$lang['emajunprotect'] = 'Unprotect';

	// Statistics
	$lang['emajshowstat'] = 'Statistics from E-Maj logs for the group "%s"';
	$lang['emajcurrentsituation'] = 'Current state';
	$lang['emajdetailedstat'] = 'Detailed stats';
	$lang['emajdetailedlogstatwarning'] = 'Attention, scanning the log tables needed to get detailed statistics may take a long time';
	$lang['emajlogstatcurrentsituation'] = 'the current state';
	$lang['emajlogstatmark'] = 'the mark "%s"';
	$lang['emajlogstattittle'] = 'Table changes between the mark "%s" and %s for the tables group "%s"';
	$lang['emajnosimrlbkduration'] = 'The start mark cannot be used for a rollback. No rollback duration can be estimated.';
	$lang['emajsimrlbkduration'] = 'Rolling the tables group "%s" back to the mark "%s" would take about %s.';
	$lang['emajstatfirstmark'] = 'First mark';
	$lang['emajstatfirstmarkdatetime'] = 'First mark date/time';
	$lang['emajstatlastmark'] = 'Last mark';
	$lang['emajstatlastmarkdatetime'] = 'Last mark date/time';
	$lang['emajstatverb'] = 'SQL verb';
	$lang['emajnbinsert'] = '# INSERT';
	$lang['emajnbupdate'] = '# UPDATE';
	$lang['emajnbdelete'] = '# DELETE';
	$lang['emajnbtruncate'] = '# TRUNCATE';
	$lang['emajnbrole'] = '# roles';
	$lang['emajstatrows'] = '# row changes';
	$lang['emajbackgroup'] = 'Go back to the tables group';

	// Group's content
	$lang['emajgroupcontent'] = 'Content of the tables group "%s"';
	$lang['emajemptygroup'] = 'The tables group "%s" is currently empty.';
	$lang['emajpriority'] = 'Priority';
	$lang['emajlogschema'] = 'Log schema';
	$lang['emajlogdattsp'] = 'Log tablespace';
	$lang['emajlogidxtsp'] = 'Log index tablespace';
	$lang['emajnamesprefix'] = 'Objects name prefix';

	// Group creation
	$lang['emajcreateagroup'] = 'E-Maj: Create a tables group';
	$lang['emajcreateanemptygroup'] = "Create an empty tables group";
	$lang['emajconfirmcreategroup'] = 'Are you sure you want to create the tables group "%s"?';
	$lang['emajinvalidemptygroup'] = 'Error, the tables group "%s" is already created or configured!';
	$lang['emajcreategroupok'] = 'The tables group "%s" has been created.';
	$lang['emajcreategrouperr'] = 'Error during group "%s" creation!';

	// Group drop
	$lang['emajdropagroup'] = 'E-Maj: Drop a tables group';
	$lang['emajconfirmdropgroup'] = 'Are you sure you want to drop the tables group "%s"?';
	$lang['emajcantdropgroup'] = 'Dropping the tables group "%s" is not possible. The group is started.';
	$lang['emajdropgroupok'] = 'The tables group "%s" has been dropped.';
	$lang['emajdropgrouperr'] = 'Error during tables group "%s" drop!';

	// Group alter
	$lang['emajaltergroups'] = 'E-Maj: Apply configuration changes';
	$lang['emajalteraloggingroup'] = 'The group "%s" is in LOGGING state. You can specify a mark name.';
	$lang['emajconfirmaltergroup'] = 'Are you sure you want to apply the configuraiton changes for the tables group "%s"?';
	$lang['emajcantaltergroup'] = 'Applying the configuration changes for the group "%s" would generate actions that cannot be executed on LOGGING group. Stop the group before altering it.';
	$lang['emajaltergroupok'] = 'The configuration changes for the tables group "%s" have been applied.';
	$lang['emajaltergrouperr'] = 'Error during tables group "%s" configuration change!';

	// Groups alter
	$lang['emajalterallloggingroups'] = 'The groups "%s" are in LOGGING state. You can specify a mark name.';
	$lang['emajconfirmaltergroups'] = 'Are you sure you want to apply the configuraiton changes for the tables groups "%s"?';
	$lang['emajaltergroupsok'] = 'The configuration changes for the tables groups "%s" have been applied.';
	$lang['emajaltergroupserr'] = 'Error during tables groups "%s" configuration change!';

	// Group comment
	$lang['emajcommentagroup'] = 'E-Maj: Record a comment for a tables group';
	$lang['emajcommentgroup'] = 'Enter, modify or erase the comment for tables group "%s".';
	$lang['emajcommentgroupok'] = 'The comment for the tables group "%s" has been recorded.';
	$lang['emajcommentgrouperr'] = 'Error during comment recording for the tables group "%s"!';

	// Group protect
	$lang['emajcantprotectgroup'] = 'Protecting the tables group "%s" is not possible. The group is stopped.';
	$lang['emajprotectgroupok'] = 'The tables group "%s" is now protected against rollbacks.';
	$lang['emajprotectgrouperr'] = 'Error while protecting the tables group "%s"!';

	// Group unprotect
	$lang['emajcantunprotectgroup'] = 'Unprotecting the tables group "%s" is not possible. The group is stopped.';
	$lang['emajunprotectgroupok'] = 'The tables group "%s" is now unprotected.';
	$lang['emajunprotectgrouperr'] = 'Error while unprotecting the tables group "%s"!';

	// Group start
	$lang['emajstartagroup'] = 'E-Maj: Start a tables group';
	$lang['emajconfirmstartgroup'] = 'Starting the tables group "%s"';
	$lang['emajinitmark'] = 'Initial mark';
	$lang['emajoldlogsdeletion'] = 'Old logs deletion';
	$lang['emajcantstartgroup'] = 'Starting the tables group "%s" is not possible. The group is already started.';
	$lang['emajstartgroupok'] = 'The tables group "%s" is started with the mark "%s".';
	$lang['emajstartgrouperr'] = 'Error during tables group "%s" start!';

	// Groups start
	$lang['emajstartgroups'] = 'E-Maj: Start tables groups';
	$lang['emajconfirmstartgroups'] = 'Starting the tables groups "%s"';
	$lang['emajcantstartgroups'] = 'Starting the tables groups "%s" is not possible. the group "%s" is already started.';
	$lang['emajstartgroupsok'] = 'The tables groups "%s" are started with the mark "%s".';
	$lang['emajstartgroupserr'] = 'Error during tables groups "%s" start!';

	// Group stop
	$lang['emajstopagroup'] = 'E-Maj: Stop a tables group';
	$lang['emajconfirmstopgroup'] = 'Stopping the tables group "%s"';
	$lang['emajstopmark'] = 'Final mark';
	$lang['emajforcestop'] = 'Forced stop (in case of problem only)';
	$lang['emajcantstopgroup'] = 'Stopping the tables group "%s" is not possible. The group is already stopped.';
	$lang['emajstopgroupok'] = 'The tables group "%s" has been stopped.';
	$lang['emajstopgrouperr'] = 'Error during tables group "%s" stop!';

	// Groups stop
	$lang['emajstopgroups'] = 'E-Maj: Stop tables groups';
	$lang['emajconfirmstopgroups'] = 'Stopping the tables groups "%s"';
	$lang['emajcantstopgroups'] = 'Stopping the tables groups "%s" is impossible. The group "%s" is already stopped.';
	$lang['emajstopgroupsok'] = 'The tables groups "%s" have been stopped.';
	$lang['emajstopgroupserr'] = 'Error during tables groups "%s" stop!';

	// Group reset
	$lang['emajresetagroup'] = 'E-Maj: Reset a tables group';
	$lang['emajconfirmresetgroup'] = 'Are you sure you want to reset the tables group "%s"?';
	$lang['emajcantresetgroup'] = 'Reseting the tables group "%s" is not possible. The group is started.';
	$lang['emajresetgroupok'] = 'The tables group "%s" has been reset.';
	$lang['emajresetgrouperr'] = 'Error during tables group "%s" reset!';

	// Set Mark for one or several groups
	$lang['emajsetamark'] = 'E-Maj: Set a mark';
	$lang['emajconfirmsetmarkgroup'] = 'Setting a mark for the tables group(s) "%s":';
	$lang['emajcantsetmarkgroup'] = 'Setting a mark on the tables group "%s" is not possible. The group is stopped.';
	$lang['emajinvalidmark'] = 'The supplied mark (%s) is invalid!';
	$lang['emajsetmarkgroupok'] = 'The mark "%s" has been set for the tables group(s) "%s".';
	$lang['emajsetmarkgrouperr'] = 'Error during mark set "%s" for the tables group(s) "%s"!';
	$lang['emajcantsetmarkgroups'] = 'Setting a mark for the tables groups "%s" is not possible. The group "%s" is stopped.';

	// Protect mark
	$lang['emajcantprotectmarkgroup'] = 'Protecting a mark on the tables group "%s" is not possible. The group is stopped.';
	$lang['emajprotectmarkgroupok'] = 'The mark "%s" for the tables group "%s" is now protected against rollbacks.';
	$lang['emajprotectmarkgrouperr'] = 'Error while protecting the mark "%s" for the tables group "%s"!';

	// Unprotect mark
	$lang['emajcantunprotectmarkgroup'] = 'Unprotecting a mark on the tables group "%s" is not possible. The group is stopped.';
	$lang['emajunprotectmarkgroupok'] = 'The mark "%s" for the tables group "%s" is now unprotected.';
	$lang['emajunprotectmarkgrouperr'] = 'Error while unprotecting the mark "%s" for the tables group "%s"!';

	// Comment mark
	$lang['emajcommentamark'] = 'E-Maj: Record a comment for a mark';
	$lang['emajcommentmark'] = 'Enter, modify or erase the comment for the mark "%s" of the tables group "%s"';
	$lang['emajcommentmarkok'] = 'The comment for the mark "%s" of the tables group "%s" has been recorded.';
	$lang['emajcommentmarkerr'] = 'Error during comment recording for the mark "%s" of the tables group "%s"!';

	// Group rollback
	$lang['emajrlbkagroup'] = 'E-Maj: Rollback a tables group';
	$lang['emajconfirmrlbkgroup'] = 'Rollbacking the tables group "%s" to the mark "%s"';
	$lang['emajselectmarkgroup'] = 'Rollbacking the tables group "%s" to the mark: ';
	$lang['emajrlbkthenmonitor'] = 'Rollback and monitor';
	$lang['emajcantrlbkidlegroup'] = 'Rollbacking the tables group "%s" is not possible. The group is stopped.';
	$lang['emajcantrlbkprotgroup'] = 'Rollbacking the tables group "%s" is not possible. The group is protected.';
	$lang['emajcantrlbkinvalidmarkgroup'] = 'Rollbacking the tables group "%s" is not possible. The mark "%s" is not valid.';
	$lang['emajreachaltergroup'] = 'Rollbacking the tables group "%s" to the mark "%s" would reach a point in time prior alter_group operations. Please confirm the rollback.';
	$lang['emajautorolledback'] = 'Automatically rolled back?';
	$lang['emajgroupisprotected'] = 'The group "%s" is protected.';
	$lang['emajinvalidrlbkmark'] = 'Mark "%s" is not valid anymore.';
	$lang['emajrlbkgroupok'] = 'The tables group "%s" has been rollbacked to the mark "%s".';
	$lang['emajrlbkgrouperr'] = 'Error during tables group "%s" rollback to the mark "%s"!';
	$lang['emajbadpsqlpath'] = 'Asynchronous rollback not possible: the configured pathname of the psql command (%s) is not valid.';
	$lang['emajbadtempdir'] = 'Asynchronous rollback not possible: the configured temporary directory (%s) is not valid.';
	$lang['emajasyncrlbkstarted'] = 'Rollback started (id = %s).';

	// Groups rollback
	$lang['emajrlbkgroups'] = 'E-Maj: Rollback tables groups';
	$lang['emajselectmarkgroups'] = 'Rollbacking the tables groups "%s" to the mark: ';
	$lang['emajcantrlbkidlegroups'] = 'Rollbacking the tables groups "%s" is not possible. The group "%s" is stopped.';
	$lang['emajcantrlbkprotgroups'] = 'Rollbacking the tables groups "%s" is not possible. The groups "%s" are protected.';
	$lang['emajnomarkgroups'] = 'No common mark for the tables groups "%s" can be used for a rollback.';
	$lang['emajcantrlbkinvalidmarkgroups'] = 'Rollbacking the tables groups "%s" is not possible. The mark "%s" is not valid.';
	$lang['emajreachaltergroups'] = 'Rollbacking the tables groups "%s" to the mark "%s" would reach a point in time prior alter_group operations. Please confirm the rollback.';
	$lang['emajrlbkgroupsok'] = 'The tables groups "%s" have been rollbacked to mark "%s".';
	$lang['emajrlbkgroupserr'] = 'Error during tables groups "%s" rollback to mark "%s"!';

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
	$lang['emajalteredaddtbl'] = 'The table "%s.%s" has been added to the tables group "%s"';
	$lang['emajalteredaddseq'] = 'The sequence "%s.%s" has been added to the tables group "%s"';

	// Mark renaming
	$lang['emajrenameamark'] = 'E-Maj : Rename a mark';
	$lang['emajconfirmrenamemark'] = 'Renaming the mark "%s" of the tables group "%s"';
	$lang['emajnewnamemark'] = 'New name';
	$lang['emajrenamemarkok'] = 'The mark "%s" of the tables group "%s" has been renamed into "%s".';
	$lang['emajrenamemarkerr'] = 'Error during renaming the mark "%s" of the tables group "%s" into "%s"!';

	// Mark deletion
	$lang['emajdelamark'] = 'E-Maj: Delete a mark';
	$lang['emajconfirmdelmark'] = 'Are you sure you want to delete the mark "%s" for the tables group "%s"?';
	$lang['emajdelmarkok'] = 'The mark "%s" has been deleted for the tables group "%s".';
	$lang['emajdelmarkerr'] = 'Error during mark "%s" deletion for the tables group "%s"!';

	// Marks deletion
	$lang['emajdelmarks'] = 'E-Maj: Delete marks';
	$lang['emajconfirmdelmarks'] = 'Are you sure you want to delete the marks "%s" for the tables group "%s"?';
	$lang['emajdelmarksok'] = 'The marks "%s" have been deleted for the tables group "%s".';
	$lang['emajdelmarkserr'] = 'Error during marks "%s" deletion for the tables group "%s"!';
	$lang['emajdelmarkserr2'] = 'It is not allowed to delete all marks of a tables group. A tables group in LOGGING state must have at least one mark.';

	// Marks before mark deletion
	$lang['emajdelmarksprior'] = 'E-Maj: Delete marks';
	$lang['emajconfirmdelmarksprior'] = 'Are you sure you want to delete all marks and logs preceeding the mark "%s" for the tables group "%s"?';
	$lang['emajdelmarkspriorok'] = 'All (%s) marks preceeding mark "%s" have been deleted for the tables group "%s".';
	$lang['emajdelmarkspriorerr'] = 'Error during the deletion of marks preceeding mark "%s" for the tables group "%s"!';

?>
