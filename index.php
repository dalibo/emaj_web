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
	echo "    <a href=\"#\" id=\"minimize_button\" class=\"bottom_link\"><img src=\"images/Top.png\" alt=\"Minimize\"/></a>\n";
	echo "    <a href=\"#\" id=\"maximize_button\" class=\"bottom_link\"><img src=\"images/Top.png\" alt=\"Maximize\"/></a>\n";
	echo "  </div>\n";
	echo "  <div id=\"mainDiv\">\n";
	echo "    <iframe src=\"intro.php\" name=\"detail\" id=\"mainFrame\"></iframe>\n";
	echo "  </div>\n";
	echo "</div>\n";

	// associate actions to the minimize/maximize buttons of the browser div
	echo "<script>\n";

	// global variables
	echo "minWidth = 40;\n";
	echo "previousBrowserDivWidth = '';\n";

	// functions to change the width of a div
	echo "function changeWidth(div, size){\n";
	echo "  div.style.transition = 'width .4s';\n";									// set the transition parameter
	echo "  div.style.width = size+'px';\n";
	echo "  setTimeout(function(){ div.style.transition = 'width 0s'; }, 400);\n";	// reset the transition timer, once the transition is
																					// done, so that the resize scroll bar works smoothly
	echo "};\n";

	// process a click on the minimize icon
	echo "document.getElementById(\"minimize_button\").onclick=function(){\n";
	echo "  var div = document.getElementById(\"browserDiv\");\n";
	echo "  previousBrowserDivWidth = div.offsetWidth;\n";							// keep the current width in memory
	echo "  changeWidth(div,minWidth);\n";											// set the width to the minimum and change the button
	echo "  document.getElementById(\"minimize_button\").style.visibility = \"hidden\";\n";
	echo "  document.getElementById(\"maximize_button\").style.visibility = \"visible\";\n";
	echo "};\n";

	// process a click on the maximize icon
	echo "document.getElementById(\"maximize_button\").onclick=function(){\n";
	echo "  var div = document.getElementById(\"browserDiv\");\n";
	echo "  if (previousBrowserDivWidth < minWidth) { previousBrowserDivWidth = minWidth; }\n";
	echo "  if (div.offsetWidth < previousBrowserDivWidth) {\n";					// if the width must be enlarge, do it
	echo "    changeWidth(div,previousBrowserDivWidth);\n";
	echo "  };\n";
	echo "  document.getElementById(\"minimize_button\").style.visibility = \"visible\";\n";
	echo "  document.getElementById(\"maximize_button\").style.visibility = \"hidden\";\n";
	echo "};\n";

	echo "</script>\n";

	echo "</body></html>\n";
?>
