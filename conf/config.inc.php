<?php

	/**
	 * Central Emaj_web configuration.  As a user you may modify the
	 * settings here for your particular configuration.
	 */

	// Display name for the server on the login screen
	$conf['servers'][0]['desc'] = 'PostgreSQL';

	// Hostname or IP address for server.  Use '' for UNIX domain socket.
	// use 'localhost' for TCP/IP connection on this computer
	$conf['servers'][0]['host'] = 'localhost';

	// Database port on server (5432 is the PostgreSQL default)
	$conf['servers'][0]['port'] = 5432;

	// Database SSL mode
	// Possible options: disable, allow, prefer, require
	// To require SSL on older servers use option: legacy
	// To ignore the SSL mode, use option: unspecified
	$conf['servers'][0]['sslmode'] = 'allow';

	// Change the default database only if you cannot connect to template1.
	// For a PostgreSQL 8.1+ server, you can set this to 'postgres'.
	$conf['servers'][0]['defaultdb'] = 'postgres';

	// Example for a second server
	//$conf['servers'][1]['desc'] = 'Test Server';
	//$conf['servers'][1]['host'] = '127.0.0.1';
	//$conf['servers'][1]['port'] = 5432;
	//$conf['servers'][1]['sslmode'] = 'allow';
	//$conf['servers'][1]['defaultdb'] = 'template1';

	$conf['servers'][1]['desc'] = 'Pg 9.1';
	$conf['servers'][1]['host'] = 'localhost';
	$conf['servers'][1]['port'] = 5491;
	$conf['servers'][1]['sslmode'] = 'allow';
	$conf['servers'][1]['defaultdb'] = 'postgres';

	$conf['servers'][2]['desc'] = 'Pg 9.2';
	$conf['servers'][2]['host'] = 'localhost';
	$conf['servers'][2]['port'] = 5492;
	$conf['servers'][2]['sslmode'] = 'allow';
	$conf['servers'][2]['defaultdb'] = 'postgres';

	$conf['servers'][3]['desc'] = 'Pg 9.3';
	$conf['servers'][3]['host'] = 'localhost';
	$conf['servers'][3]['port'] = 5493;
	$conf['servers'][3]['sslmode'] = 'allow';
	$conf['servers'][3]['defaultdb'] = 'postgres';

	$conf['servers'][4]['desc'] = 'Pg 9.4';
	$conf['servers'][4]['host'] = 'localhost';
	$conf['servers'][4]['port'] = 5494;
	$conf['servers'][4]['sslmode'] = 'allow';
	$conf['servers'][4]['defaultdb'] = 'postgres';

	$conf['servers'][5]['desc'] = 'Pg 9.5';
	$conf['servers'][5]['host'] = 'localhost';
	$conf['servers'][5]['port'] = 5495;
	$conf['servers'][5]['sslmode'] = 'allow';
	$conf['servers'][5]['defaultdb'] = 'postgres';

	$conf['servers'][6]['desc'] = 'Pg 9.6';
	$conf['servers'][6]['host'] = 'localhost';
	$conf['servers'][6]['port'] = 5496;
	$conf['servers'][6]['sslmode'] = 'allow';
	$conf['servers'][6]['defaultdb'] = 'postgres';
	
	$conf['servers'][7]['desc'] = 'Pg 10';
	$conf['servers'][7]['host'] = 'localhost';
	$conf['servers'][7]['port'] = 5410;
	$conf['servers'][7]['sslmode'] = 'allow';
	$conf['servers'][7]['defaultdb'] = 'postgres';

	/* Groups definition */
	/* Groups allow administrators to logicaly group servers together under
	 * group nodes in the left browser tree
	 *
	 * The group '0' description
	 */
	//$conf['srv_groups'][0]['desc'] = 'group one';

	/* Add here servers indexes belonging to the group '0' seperated by comma */
	//$conf['srv_groups'][0]['servers'] = '0,1,2'; 

	/* A server can belong to multi groups. Here server 1 is referenced in both
	 * 'group one' and 'group two'*/
	//$conf['srv_groups'][1]['desc'] = 'group two';
	//$conf['srv_groups'][1]['servers'] = '3,1';

	/* A group can be nested in one or more existing groups using the 'parents'
	 * parameter. Here the group 'group three' contains only one server and will
	 * appear as a subgroup in both 'group one' and 'group two':
	 */
	//$conf['srv_groups'][2]['desc'] = 'group three';
	//$conf['srv_groups'][2]['servers'] = '4';
	//$conf['srv_groups'][2]['parents'] = '0,1';

	/* Warning: Only groups with no parents appears at the root of the tree. */
	

	// Default language. E.g.: 'english'.  See lang/ directory
	// for all possibilities. If you specify 'auto' (the default) it will use 
	// your browser preference.
	$conf['default_lang'] = 'auto';

	// If extra login security is true, then logins with no
	// password or certain usernames (pgsql, postgres, root, administrator)
	// will be denied. Only set this false once you have read the FAQ and
	// understand how to change PostgreSQL's pg_hba.conf to enable
	// passworded local connections.
	$conf['extra_login_security'] = false;

	// Only show owned databases?
	// Note: This will simply hide other databases in the list - this does
	// not in any way prevent your users from seeing other database by
	// other means. (e.g. Run 'SELECT * FROM pg_database' in the SQL area.)
	$conf['owned_only'] = false;

	// Display comments on objects?  Comments are a good way of documenting
	// a database, but they do take up space in the interface.
	$conf['show_comments'] = true;

	// Display "system" objects?
	$conf['show_system'] = false;

	// Width of the left frame in pixels (object browser)
	$conf['left_width'] = 200;
	
	// Which look & feel theme to use
	$conf['theme'] = 'default';
	
	// Show OIDs when browsing tables?
	$conf['show_oids'] = false;
	
	// Max rows to show on a page when browsing record sets
	$conf['max_rows'] = 30;

	// Max chars of each field to display by default in browse mode
	$conf['max_chars'] = 50;

	// Send XHTML strict headers?
	$conf['use_xhtml_strict'] = false;

	// Configuration for ajax scripts
	// Time in seconds. If set to 0, refreshing data using ajax will be disabled (locks and activity pages)
	$conf['ajax_refresh'] = 3;

	/*****************************************
	 * Don't modify anything below this line *
	 *****************************************/

	$conf['version'] = 19;
	$conf['plugins'] = array('Emaj');

?>
