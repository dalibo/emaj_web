<?php

	/*
	 * Intro screen
	 */

	// Include application functions (no db conn)
	$_no_db_connection = true;
	include_once('./libraries/lib.inc.php');

	$misc->printHtmlHeader();
	$misc->printBody();

	$misc->printHeader('root', 'root', 'intro');
?>

<div id="welcome">
  <h1><?php echo sprintf($lang['strintro'],$appName,$appVersion)?></h1>
  <img src="<?php echo $misc->icon('E-Maj_H')?>" alt="E-Maj_logo">
</div>

<?php $misc->printTitle($lang['strlink']); ?>

<ul class="intro">
	<li><a href="http://emaj.readthedocs.io/en/stable/" target=blank ><?php echo $lang['stremajdoc'] ?></a></li>
	<li><a href="https://github.com/dalibo/emaj" target=blank ><?php echo $lang['stremajproject'] ?></a></li>
	<li><a href="https://github.com/dalibo/emaj_web" target=blank ><?php echo $lang['stremajwebproject'] ?></a></li>
</ul>
<ul class="intro">
	<li><a href="<?php echo $lang['strpgsqlhome_url'] ?>" target=blank ><?php echo $lang['strpgsqlhome'] ?></a></li>
</ul>

<?php
    echo "<p style=\"font-style: italic; color: grey; font-size: 12px;\">Powered by PHP " . phpversion() . "</p>\n";
	$misc->printFooter();
?>
