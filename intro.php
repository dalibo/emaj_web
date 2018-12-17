<?php

	/*
	 * Intro screen
	 */

	// Include application functions (no db conn)
	$_no_db_connection = true;
	include_once('./libraries/lib.inc.php');
	include_once('./themes/themes.php');

	$misc->printHeader();
	$misc->printBody();

	$misc->printTrail('root');
	$misc->printTabs('root','intro');
?>

<div style="margin:20px 20px 20px 20px;">
  <h1><?php echo sprintf($lang['strintro'],$appName,$appVersion)?></h1>
  <img src="<?php echo $misc->icon('E-Maj_H')?>" alt="E-Maj_logo" style="width:50%;	height:50%;"/>
</div>

<form method="get" action="intro.php">
<table>
	<tr class="data1">
		<th class="data"><?php echo $lang['strlanguage'] ?></th>
		<td>
			<select name="language" onchange="this.form.submit()">
			<?php
			$language = isset($_SESSION['webdbLanguage']) ? $_SESSION['webdbLanguage'] : 'english';
			foreach ($appLangFiles as $k => $v) {
				echo "\t<option value=\"{$k}\"",
					($k == $language) ? ' selected="selected"' : '',
					">{$v}</option>\n";
			}
			?>
			</select>
		</td>
	</tr>
	<tr class="data2">
		<th class="data"><?php echo $lang['strtheme'] ?></th>
		<td>
			<select name="theme" onchange="this.form.submit()">
			<?php
			foreach ($appThemes as $k => $v) {
				echo "\t<option value=\"{$k}\"",
					($k == $conf['theme']) ? ' selected="selected"' : '',
					">{$v}</option>\n";
			}
			?>
			</select>
		</td>
	</tr>
</table>
<noscript><p><input type="submit" value="<?php echo $lang['stralter'] ?>" /></p></noscript>
</form>

<h3><?php echo $lang['strlink'] ?></h3>
<ul class="intro">
	<li><a href="http://emaj.readthedocs.io/en/stable/" target=blank ><?php echo $lang['stremajdoc'] ?></a></li>
	<li><a href="https://github.com/dalibo/emaj" target=blank ><?php echo $lang['stremajproject'] ?></a></li>
</ul>
<ul class="intro">
	<li><a href="<?php echo $lang['strpgsqlhome_url'] ?>" target=blank ><?php echo $lang['strpgsqlhome'] ?></a></li>
</ul>

<?php
    echo "<p style=\"font-style: italic; color: grey; font-size: 12px;\">Powered by PHP " . phpversion() . "</p>\n";
	if (isset($_GET['language'])) $_reload_browser = true;
	$misc->printFooter();
?>
