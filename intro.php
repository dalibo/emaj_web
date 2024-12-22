<?php

	/*
	 * Intro screen
	 */

	// Include application functions (no db conn)
	$_no_db_connection = true;
	include_once('./libraries/lib.inc.php');

	$misc->printHtmlHeader('', '', 'intro');
	$misc->printBody();

	$misc->printHeader('root', 'root', 'intro');

	// Display the main message
	echo "<div class=\"intro-welcome\">";
	echo "  <h1>" . sprintf($lang['strintro'],$appName,$appVersion) . "</h1>";
	echo "  <img src=\"{$misc->icon('E-Maj_H')}\" alt=\"E-Maj_logo\">";
	echo "</div>";

	// Check the PHP configuration
	if (!class_exists('IntlDateFormatter')) {
		echo "<p><img src=\"{$misc->icon('Warning')}\" alt=\"Warning\" style=\"width: 20px;\"/> " . htmlspecialchars($lang['strmissingIntlDateFormatter']) . "</p>\n";
	}

	// Display the links list
	$misc->printTitle($lang['strlink']);

	echo "<ul class=\"intro-link\">";
	echo "	<li><a href=\"{$lang['stremajdoc_url']}\" target=blank >{$lang['stremajdoc']}</a></li>";
	echo "  <li><a href=\"https://github.com/dalibo/emaj\" target=blank >{$lang['stremajproject']}</a></li>";
	echo "	<li><a href=\"https://github.com/dalibo/emaj_web\" target=blank >{$lang['stremajwebproject']}</a></li>";
	echo "</ul>";
	echo "<ul class=\"intro-link\">";
	echo "	<li><a href=\"{$lang['strpgsqlhome_url']}\" target=blank >{$lang['strpgsqlhome']}</a></li>";
	echo "</ul>";

	echo "<div class=\"intro-footer\">";
    echo "Powered by PHP " . phpversion() . "\n";
	echo "</div>";

	$misc->printFooter();
?>
