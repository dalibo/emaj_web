<?php

	/*
	 * Main access point to Emaj_web
	 */

	// Include application functions
	$_no_db_connection = true;
	include_once('./libraries/lib.inc.php');

	// Page header
	$misc->printHtmlHeader('', null, 'index', true);
	echo "<body>\n";

	// Both iframes
	echo "<div id=\"allFrames\">\n";
	echo "  <div id=\"browserDiv\">\n";
	echo "    <iframe src=\"browser.php\" name=\"browser\" id=\"browserFrame\"></iframe>\n";
	echo "  </div>\n";
	echo "  <div id=\"mainDiv\">\n";
	echo "    <iframe src=\"intro.php\" name=\"detail\" id=\"mainFrame\"></iframe>\n";
	echo "  </div>\n";
	echo "</div>\n";

	echo "</body></html>\n";
?>
