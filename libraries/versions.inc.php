<?php

	/**
	 * This component contains all versions related data.
	 * To be maintained...
	 */

	// Emaj_web current version
	$appVersion = '4.7';

	// PostgreSQL and PHP minimum version
	$postgresqlMinVer = '9.5';
	$phpMinVer = '7.0';

	// E-Maj versions
	$oldest_supported_emaj_version = '3.0.0';			// Oldest supported emaj version
	$oldest_supported_emaj_version_num = 30000;
	$last_known_emaj_version = '4.7.1';					// Most recent known emaj version
	$last_known_emaj_version_num = 40701;

	// Cross references between the emaj extensions (as string) and the Postgres major versions (as string)
	$xrefEmajPg['3.0.0']['minPostgresVersion'] = '9.5'; $xrefEmajPg['3.0.0']['maxPostgresVersion'] = '11';
	$xrefEmajPg['3.1.0']['minPostgresVersion'] = '9.5'; $xrefEmajPg['3.1.0']['maxPostgresVersion'] = '14';
	$xrefEmajPg['3.2.0']['minPostgresVersion'] = '9.5'; $xrefEmajPg['3.2.0']['maxPostgresVersion'] = '14';
	$xrefEmajPg['3.3.0']['minPostgresVersion'] = '9.5'; $xrefEmajPg['3.3.0']['maxPostgresVersion'] = '14';
	$xrefEmajPg['3.4.0']['minPostgresVersion'] = '9.5'; $xrefEmajPg['3.4.0']['maxPostgresVersion'] = '14';
	$xrefEmajPg['4.0.0']['minPostgresVersion'] = '9.5'; $xrefEmajPg['4.0.0']['maxPostgresVersion'] = '14';
	$xrefEmajPg['4.0.1']['minPostgresVersion'] = '9.5'; $xrefEmajPg['4.0.1']['maxPostgresVersion'] = '14';
	$xrefEmajPg['4.1.0']['minPostgresVersion'] = '9.5'; $xrefEmajPg['4.1.0']['maxPostgresVersion'] = '15';
	$xrefEmajPg['4.2.0']['minPostgresVersion'] = '11'; $xrefEmajPg['4.2.0']['maxPostgresVersion'] = '16';
	$xrefEmajPg['4.3.0']['minPostgresVersion'] = '11'; $xrefEmajPg['4.3.0']['maxPostgresVersion'] = '16';
	$xrefEmajPg['4.3.1']['minPostgresVersion'] = '11'; $xrefEmajPg['4.3.1']['maxPostgresVersion'] = '16';
	$xrefEmajPg['4.4.0']['minPostgresVersion'] = '11'; $xrefEmajPg['4.4.0']['maxPostgresVersion'] = '16';
	$xrefEmajPg['4.5.0']['minPostgresVersion'] = '11'; $xrefEmajPg['4.5.0']['maxPostgresVersion'] = '17';
	$xrefEmajPg['4.6.0']['minPostgresVersion'] = '11'; $xrefEmajPg['4.6.0']['maxPostgresVersion'] = '17';
	$xrefEmajPg['4.7.0']['minPostgresVersion'] = '12'; $xrefEmajPg['4.7.0']['maxPostgresVersion'] = '18';
	$xrefEmajPg['4.7.1']['minPostgresVersion'] = '12'; $xrefEmajPg['4.7.1']['maxPostgresVersion'] = '18';

?>
