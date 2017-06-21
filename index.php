<?php

	/*
	 * Main access point to the app.
	 */

	// Include application functions
	$_no_db_connection = true;
	include_once('./libraries/lib.inc.php');
	$misc->printHeader('', null, true);

	$rtl = (strcasecmp($lang['applangdir'], 'rtl') == 0);

	$cols = $rtl ? '*,'.$conf['left_width'] : $conf['left_width'].',*';
	$mainframe = '<frame src="intro.php" name="detail" id="detail" frameborder="0" />'
?>
<frameset cols="<?php echo $cols ?>">

<?php if ($rtl) echo $mainframe; ?>

	<frame src="browser.php" name="browser" id="browser" frameborder="0" />

<?php if (!$rtl) echo $mainframe; ?>

	<noframes>
	<body>
		<?php echo $lang['strnoframes'] ?><br />
		<a href="intro.php"><?php echo $lang['strnoframeslink'] ?></a>
	</body>
	</noframes>

</frameset>

<?php
	$misc->printFooter(false);
?>
