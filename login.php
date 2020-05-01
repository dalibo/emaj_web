<?php

	/*
	 * Login screen
	 */
	global $conf;
	
	// This needs to be an include once to prevent lib.inc.php infinite recursive includes.
	// Check to see if the configuration file exists, if not, explain
	require_once('./libraries/lib.inc.php');

	$misc->printHtmlHeader($lang['strlogin']);
	$misc->printBody();
	$misc->printHeader('root', '', '');
	
	$server_info = $misc->getServerInfo($_REQUEST['server']);
	
	$misc->printTitle(sprintf($lang['strlogintitle'], $server_info['desc']));
	
	if (isset($msg)) $misc->printMsg($msg);

	$md5_server = md5($_REQUEST['server']);
	$username = isset($_POST['loginUsername']) ? htmlspecialchars($_POST['loginUsername']) : '';

	echo "<form id=\"login_form\" action=\"redirect.php\" method=\"post\" name=\"login_form\">";

	if (!empty($_POST)) $vars =& $_POST;
	else $vars =& $_GET;
	// Pass request vars through form (is this a security risk???)
	foreach ($vars as $key => $val) {
		if (substr($key,0,5) == 'login') continue;
		echo "<input type=\"hidden\" name=\"", htmlspecialchars($key), "\" value=\"", htmlspecialchars($val), "\" />\n";
	}
	echo "<input type=\"hidden\" name=\"loginServer\" value=\"" . htmlspecialchars($_REQUEST['server']) . "\" />";
	echo "<table class=\"navbar\" border=\"0\" cellpadding=\"5\" cellspacing=\"3\">";
	echo "\t<tr>";
	echo "\t\t<td>{$lang['strusername']}</td>";
	echo "\t\t<td><input type=\"text\" name=\"loginUsername\" value=\"{$username}\" size=\"24\" /></td>";
	echo "\t</tr>";
	echo "\t<tr>";
	echo "\t\t<td>{$lang['strpassword']}</td>";
	echo "\t\t<td><input id=\"loginPassword\" type=\"password\" name=\"loginPassword_{$md5_server}\" size=\"24\" /></td>";
	echo "\t</tr>";
	echo "</table>";

	if (sizeof($conf['servers']) > 1) {
		echo "<p><input type=\"checkbox\" id=\"loginShared\" name=\"loginShared\" ";
		echo isset($_POST['loginShared']) ? 'checked="checked"' : '';
		echo "<label for=\"loginShared\">{$lang['strtrycred']}</label></p>";
	}
	echo "<p><input type=\"submit\" name=\"loginSubmit\" value=\"{$lang['strlogin']}\" /></p>";
	echo "</form>";

	echo "<script>";
	echo "	var uname = document.login_form.loginUsername;";
	echo "	var pword = document.login_form.loginPassword_{$md5_server};";
	echo "	if (uname.value == \"\") {";
	echo "		uname.focus();";
	echo "	} else {";
	echo "		pword.focus();";
	echo "	}";
	echo "</script>";

	// Output footer
	$misc->printFooter();
?>
