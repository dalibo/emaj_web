<?php
	/* Configuration file for the E-Maj plugin */

    /* Uncomment and adjust the value of each parameter */

	/* Pathname for the psql executable file (used to submit batch rollback operations */
//	$plugin_conf['psql_path'] = 'C:\\Bitnami\\wappstack-5.5.30-0\\postgresql\\bin\\psql.exe';
	$plugin_conf['psql_path'] = '/usr/bin/psql';

	/* Directory containing temporary files (used to submit batch rollback operations */
//	$plugin_conf['temp_dir'] = 'C:\\Users\\Default\\AppData\\Local\\Temp';
	$plugin_conf['temp_dir'] = '/tmp';

?>
