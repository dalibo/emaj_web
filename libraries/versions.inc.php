<?php

	/**
	 * This component contains all versions related data.
	 * To be maintained...
	 */

	// Emaj_web current version
	$appVersion = '3.3';

	// PostgreSQL and PHP minimum version
	$postgresqlMinVer = '9.1';
	$phpMinVer = '5.0';

	// E-Maj versions
	$oldest_supported_emaj_version = '1.3.0';			// Oldest supported emaj version
	$oldest_supported_emaj_version_num = 10300;
	$last_known_emaj_version = '3.3.0';					// Most recent known emaj version
	$last_known_emaj_version_num = 30300;

	// Cross references between the emaj extensions (as string) and the Postgres major versions (as float)
	$xrefEmajPg['2.0.0']['minPostgresVersion'] = 9.1; $xrefEmajPg['2.0.0']['maxPostgresVersion'] = 9.6;
	$xrefEmajPg['2.0.1']['minPostgresVersion'] = 9.1; $xrefEmajPg['2.0.1']['maxPostgresVersion'] = 9.6;
	$xrefEmajPg['2.1.0']['minPostgresVersion'] = 9.1; $xrefEmajPg['2.1.0']['maxPostgresVersion'] = 10;
	$xrefEmajPg['2.2.0']['minPostgresVersion'] = 9.1; $xrefEmajPg['2.2.0']['maxPostgresVersion'] = 10;
	$xrefEmajPg['2.2.1']['minPostgresVersion'] = 9.1; $xrefEmajPg['2.2.1']['maxPostgresVersion'] = 10;
	$xrefEmajPg['2.2.2']['minPostgresVersion'] = 9.1; $xrefEmajPg['2.2.2']['maxPostgresVersion'] = 10;
	$xrefEmajPg['2.2.3']['minPostgresVersion'] = 9.1; $xrefEmajPg['2.2.3']['maxPostgresVersion'] = 10;
	$xrefEmajPg['2.3.0']['minPostgresVersion'] = 9.2; $xrefEmajPg['2.3.0']['maxPostgresVersion'] = 10;
	$xrefEmajPg['2.3.1']['minPostgresVersion'] = 9.2; $xrefEmajPg['2.3.1']['maxPostgresVersion'] = 11;
	$xrefEmajPg['3.0.0']['minPostgresVersion'] = 9.5; $xrefEmajPg['3.0.0']['maxPostgresVersion'] = 11;
	$xrefEmajPg['3.1.0']['minPostgresVersion'] = 9.5; $xrefEmajPg['3.1.0']['maxPostgresVersion'] = 12;
	$xrefEmajPg['3.2.0']['minPostgresVersion'] = 9.5; $xrefEmajPg['3.2.0']['maxPostgresVersion'] = 12;
	$xrefEmajPg['3.3.0']['minPostgresVersion'] = 9.5; $xrefEmajPg['3.3.0']['maxPostgresVersion'] = 12;

?>
