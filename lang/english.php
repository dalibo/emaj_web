<?php

	/**
	 * English language file for Emaj_web.  Use this as a basis
	 * for new translations.
	 */

	// Language and character set
	$lang['applocale'] = 'en-US';
	$lang['applangdir'] = 'ltr';

	// Welcome
	$lang['strintro'] = 'Welcome to Emaj_web.';
	$lang['strpgsqlhome'] = 'PostgreSQL Homepage';
	$lang['strpgsqlhome_url'] = 'http://www.postgresql.org/';
	$lang['stremajdoc'] = 'Online E-Maj documentation';
	$lang['stremajproject'] = 'E-Maj on github';

	// Basic strings
	$lang['strlogin'] = 'Login';
	$lang['strloginfailed'] = 'Login failed';
	$lang['strlogindisallowed'] = 'Login disallowed for security reasons.';
	$lang['strserver'] = 'Server';
	$lang['strservers'] = 'Servers';
	$lang['strgroupservers'] = 'Servers in group "%s"';
	$lang['strallservers'] = 'All servers';
	$lang['strintroduction'] = 'Introduction';
	$lang['strhost'] = 'Host';
	$lang['strport'] = 'Port';
	$lang['strlogout'] = 'Logout';
	$lang['strowner'] = 'Owner';
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
	
	// Admin
	
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

	// Columns
		
	// Users
	$lang['strusername'] = 'Username';
	$lang['strpassword'] = 'Password';
	
	// Groups
	$lang['strgroup'] = 'Group';
	$lang['strgroupgroups'] = 'Groups in group "%s"';

	// Roles
	$lang['strrole'] = 'Role';
	$lang['strroles'] = 'Roles';

	// Privileges

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

	// Reports

	// Domains
	$lang['strdomains'] = 'Domains';

	// Operators
	$lang['stroperator'] = 'Operator';
	$lang['stroperators'] = 'Operators';

	// Casts
	
	// Conversions
	$lang['strconversions'] = 'Conversions';
	
	// Languages
	$lang['strlanguages'] = 'Languages';
	
	// Info

	// Aggregates
	$lang['straggregates'] = 'Aggregates';

	// Operator Classes
	$lang['stropclasses'] = 'Op Classes';

	// Stats and performance

	// Tablespaces
	$lang['strtablespace'] = 'Tablespace';

	// Miscellaneous
	$lang['strtopbar'] = '%s running on %s:%s -- You are logged in as user "%s"';
	$lang['strlogintitle'] = 'Login to %s';
	$lang['strlogoutmsg'] = 'Logged out of %s';
	$lang['strloading'] = 'Loading...';
	$lang['strerrorloading'] = 'Error Loading';
	$lang['strclicktoreload'] = 'Click to reload';

	// Autovacuum

	// Table-level Locks

	// Prepared transactions
	
	// Fulltext search

	//Plugins
	$lang['strpluginnotfound'] = 'Error: plugin \'%s\' not found. Check if this plugin exists in the plugins/ directory, or if this plugins has a plugin.php file. Plugin\'s names are case sensitive';
	$lang['stractionnotfound'] = 'Error: action \'%s\' not found in the \'%s\' plugin, or it was not specified as an action.';
	$lang['strhooknotfound'] = 'Error: hook \'%s\' is not avaliable.';
?>
