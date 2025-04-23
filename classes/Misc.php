<?php
	/*
	 * Class to hold various commonly used functions
	 */

	class Misc {
		// Tracking string to include in HREFs
		var $href;
		// Tracking string to include in forms
		var $form;

		/* Constructor */
		function __construct() {
		}

		/**
		 * Sets the href tracking variable
		 */
		function setHREF() {
			$this->href = $this->getHREF();
		}

		/**
		 * Get a href query string, excluding objects below the given object type (inclusive)
		 */
		function getHREF($exclude_from = null) {
			$href = '';
			if (isset($_REQUEST['server']) && $exclude_from != 'server') {
				$href .= 'server=' . urlencode($_REQUEST['server']);
				if (isset($_REQUEST['database']) && $exclude_from != 'database') {
					$href .= '&database=' . urlencode($_REQUEST['database']);
					if (isset($_REQUEST['schema']) && $exclude_from != 'schema') {
						$href .= '&schema=' . urlencode($_REQUEST['schema']);
					}
				}
			}
			return htmlentities($href);
		}

		/**
		 * Defines the links of the element of the bread crumb trail.
		 */
		function getSubjectParams($subject) {

			$vars = array();
			switch($subject) {
				case 'root':
					$vars = array (
						'params' => array(
							'subject' => 'root'
						)
					);
					break;
				case 'server':
					$vars = array (
						'params' => array(
							'server' => $_REQUEST['server'],
							'subject' => 'server'
					));
					break;
				case 'database':
					$vars = array(
						'params' => array(
							'server' => $_REQUEST['server'],
							'subject' => 'database',
							'database' => $_REQUEST['database'],
					));
					break;
				case 'schema':
					$vars = array(
						'url' => 'schemas.php',
						'params' => array(
							'server' => $_REQUEST['server'],
							'subject' => 'schema',
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema']
					));
					break;
				case 'table':
					$vars = array(
						'params' => array(
							'server' => $_REQUEST['server'],
							'subject' => 'table',
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'table' => $_REQUEST['table']
					));
					break;
				case 'sequence':
					$vars = array(
						'params' => array(
							'server' => $_REQUEST['server'],
							'subject' => 'sequence',
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'sequence' => $_REQUEST['sequence']
					));
					break;
				case 'emajgroup':
					$vars = array (
						'url' => 'groupproperties.php',
						'params' => array (
							'server' => $_REQUEST['server'],
							'subject' => 'emajgroup',
							'database' => $_REQUEST['database'],
							'group' => $_REQUEST['group'],
					));
					break;
				case 'emajrollback':
					$vars = array (
						'url' => 'emajrollbacks.php',
						'params' => array (
							'server' => $_REQUEST['server'],
							'subject' => 'emajrollback',
							'database' => $_REQUEST['database'],
							'rlbkid' => $_REQUEST['rlbkid'],
					));
					break;
				default:
					return false;
			}

			if (!isset($vars['url']))
				$vars['url'] = 'redirect.php';

			return $vars;
		}

		function getHREFSubject($subject) {
			$vars = $this->getSubjectParams($subject);
			return "{$vars['url']}?". http_build_query($vars['params'], '', '&amp;');
		}

		/**
		 * Sets the form tracking variable
		 */
		function setForm() {
			$this->form = '';
			if (isset($_REQUEST['server'])) {
				$this->form .= "<input type=\"hidden\" name=\"server\" value=\"" . htmlspecialchars($_REQUEST['server']) . "\" />\n";
				if (isset($_REQUEST['database'])) {
					$this->form .= "<input type=\"hidden\" name=\"database\" value=\"" . htmlspecialchars($_REQUEST['database']) . "\" />\n";
					if (isset($_REQUEST['schema'])) {
						$this->form .= "<input type=\"hidden\" name=\"schema\" value=\"" . htmlspecialchars($_REQUEST['schema']) . "\" />\n";
					}
				}
			}
		}

		/**
		 * Render a value into HTML using formatting rules specified
		 * by a type name and parameters.
		 *
		 * @param $str The string to change
		 *
		 * @param $type Field type (optional), this may be an internal PostgreSQL type, or:
		 *			yesno    - same as bool, but renders as 'Yes' or 'No'.
		 *			pre      - render in a <pre> block.
		 *			nbsp     - replace all spaces with &nbsp;'s
		 *			verbatim - render exactly as supplied, no escaping what-so-ever.
		 *			callback - render using a callback function supplied in the 'function' param.
		 *			spanned  - a data with an alternate content in a <span> element displayed in a tooltip.
		 *						(the original data in displated in the tooltip, while the 'cliplen' and 'dateformat' parameters
		 *						define the regular cell content)
		 *
		 * @param $params Type parameters (optional), known parameters:
		 *			null     - string to display if $str is null, or set to TRUE to use a default 'NULL' string,
		 *			           otherwise nothing is rendered.
		 *			clip     - if true, clip the value to a fixed length, and append an ellipsis...
		 *			cliplen  - the maximum length when clip is enabled (defaults to $conf['max_chars']) or when the data type is 'spanned'
		 *			ellipsis - the string to append to a clipped value (defaults to $lang['strellipsis'])
		 *			dateformat - the date formating to build the cell content of a 'spanned' data
		 *					 - (the pattern follows the ICU time formating rule)
		 *			intervalformat - the time interval formating to build the cell content of a 'spanned' data
		 *			tag      - an HTML element name to surround the value.
		 *			class    - a class attribute to apply to any surrounding HTML element.
		 *			align    - an align attribute ('left','right','center' etc.)
		 *			true     - (type='bool') the representation of true.
		 *			false    - (type='bool') the representation of false.
		 *			function - (type='callback') a function name, accepts args ($str, $params) and returns a rendering.
		 *			lineno   - prefix each line with a line number.
		 *			map      - an associative array.
		 *
		 * @return The HTML rendered value
		 */
		function printVal($str, $type = null, $params = array()) {
			global $lang, $conf, $data;

			// Shortcircuit for a NULL value
			if (is_null($str))
				return isset($params['null'])
						? ($params['null'] === true ? '<i>NULL</i>' : $params['null'])
						: '';

			if (isset($params['map']) && isset($params['map'][$str])) $str = $params['map'][$str];

			// Clip the value if the 'clip' parameter is true.
			if (isset($params['clip']) && $params['clip'] === true) {
				$maxlen = isset($params['cliplen']) && is_integer($params['cliplen']) ? $params['cliplen'] : $conf['max_chars'];
				$ellipsis = isset($params['ellipsis']) ? $params['ellipsis'] : $lang['strellipsis'];
				if (strlen($str) > $maxlen) {
					$str = substr($str, 0, $maxlen-1) . $ellipsis;
				}
			}

			$out = '';

			switch ($type) {
				case 'int2':
				case 'int4':
				case 'int8':
				case 'float4':
				case 'float8':
				case 'money':
				case 'numeric':
				case 'oid':
				case 'xid':
				case 'cid':
				case 'tid':
					$align = 'right';
					$out = nl2br(htmlspecialchars($str));
					break;
				case 'yesno':
					if (!isset($params['true'])) $params['true'] = $lang['stryes'];
					if (!isset($params['false'])) $params['false'] = $lang['strno'];
					// No break - fall through to boolean case.
				case 'bool':
				case 'boolean':
					if (is_bool($str)) $str = $str ? 't' : 'f';
					switch ($str) {
						case 't':
							$out = (isset($params['true']) ? $params['true'] : $lang['strtrue']);
							$align = 'center';
							break;
						case 'f':
							$out = (isset($params['false']) ? $params['false'] : $lang['strfalse']);
							$align = 'center';
							break;
						default:
							$out = htmlspecialchars($str);
					}
					break;
				case 'bytea':
					$tag = 'div';
					$class = 'pre';
					$out = $data->escapeBytea($str);
					break;
				case 'errormsg':
					$tag = 'pre';
					$class = 'error';
					$out = htmlspecialchars($str);
					break;
				case 'pre':
					$tag = 'pre';
					$out = htmlspecialchars($str);
					break;
				case 'prenoescape':
					$tag = 'pre';
					$out = $str;
					break;
				case 'nbsp':
					$out = nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($str)));
					break;
				case 'verbatim':
					$out = $str;
					break;
				case 'callback':
					if (is_string($params['function'])) {
						$out = $params['function']($str, $params);
					} else {
						$out = $params['function'][0]->{$params['function'][1]}($str, $params);
					}
					break;
				case 'prettysize':
					if ($str == -1) 
						$out = $lang['strnoaccess'];
					else
					{
						$limit = 10 * 1024;
						$mult = 1;
						if ($str < $limit * $mult)
							$out = $str.' '.$lang['strbytes'];
						else
						{
							$mult *= 1024;
							if ($str < $limit * $mult)
								$out = floor(($str + $mult / 2) / $mult).' '.$lang['strkb'];
							else
							{
								$mult *= 1024;
								if ($str < $limit * $mult)
									$out = floor(($str + $mult / 2) / $mult).' '.$lang['strmb'];
								else
								{
									$mult *= 1024;
									if ($str < $limit * $mult)
										$out = floor(($str + $mult / 2) / $mult).' '.$lang['strgb'];
									else
									{
										$mult *= 1024;
										if ($str < $limit * $mult)
											$out = floor(($str + $mult / 2) / $mult).' '.$lang['strtb'];
									}
								}
							}
						}
					}
					break;
				case 'spanned':
					if (isset($params['dateformat'])) {
						// the data is a timestamp
						$locale = (isset($params['locale'])) ? $params['locale'] : null;
						$str2 = $str;
						$dateTimeObj = new DateTime($str);
						if (class_exists('IntlDateFormatter')) {
							$str1 = IntlDateFormatter::formatObject($dateTimeObj, $params['dateformat'], $locale);
						} else {
							$str1 = $str;
						}
					} elseif (isset($params['intervalformat'])) {
						// the data is an interval
						$str1 = $str; $str2 = '';
						// extract all interval parts
						if (preg_match('/(\\d\\d) (\\d\\d):(\\d\\d):(\\d\\d)\\.((\\d\\d\\d)(\\d\\d\\d))/', $str, $reg)) {
							$dd=$reg[1]; $hh=$reg[2]; $mm=$reg[3]; $ss=$reg[4]; $ms=$reg[6]; $us=$reg[5];
							// build the tooltip value
							$str2 = str_replace(array('DD','HH','MM','SS','US'), array($dd,$hh,$mm,$ss,$us), $params['intervalformat']);
							// build the main value
							$hh += $dd * 24;			// days are added to the hours part
							if ($hh > 0) {				// hours to show
								$str1 = ($hh*1).':'.$mm.':'.$ss;
							} elseif ($mm > 0) {		// minutes to show
								$str1 = ($mm*1).':'.$ss.'.'.$ms;
							} else {					// less than a minutes to show
								$str1 = ($ss*1).'.'.$us;
							}
						}
					} elseif (isset($params['spanseparator'])) {
						// a text to split in 2 parts, based on a separator (if the separator is not found, no tooltip is generated)
						$str2 = '';
						list($str1, $str2) = explode($params['spanseparator'], $str, 2);
					} else {
						// otherwise ... (should never occur)
						$str1 = $str; $str2 = '';
					}
					if (isset($params['cliplen']) && is_integer($params['cliplen'])) {
						// a cliplen parameter has been supplied
						$maxlen = $params['cliplen'];
						$ellipsis = isset($params['ellipsis']) ? $params['ellipsis'] : $lang['strellipsis'];
						if (strlen($str1) > $maxlen) {
							if ($str2 == '') {
								$str2 = $str1;
							}
							$str1 = substr($str1, 0, $maxlen) . $ellipsis;
						}
					}
					if ($str2 <> '') {
						// the second part of the supplied string is added in a span tag after the first part, clipped if requested, string
						$out = htmlspecialchars($str1) . "<span>" . htmlspecialchars($str2) . "</span>";
					} else {
						// no need for a span tag
						$out = htmlspecialchars($str);
					}
					break;
				default:
					// If the string contains at least one instance of >1 space in a row, a tab
					// character, a space at the start of a line, or a space at the start of
					// the whole string then render within a pre-formatted element (<pre>).
					if (preg_match('/(^ |  |\t|\n )/m', $str)) {
						$tag = 'pre';
						$class = 'data';
						$out = htmlspecialchars($str);
					} else {
						$out = nl2br(htmlspecialchars($str));
					}
			}

			if (isset($params['class'])) $class = $params['class'];
			if (isset($params['align'])) $align = $params['align'];

			if (!isset($tag) && (isset($class) || isset($align))) $tag = 'div';

			if (isset($tag)) {
				$alignattr = isset($align) ? " style=\"text-align: {$align}\"" : '';
				$classattr = isset($class) ? " class=\"{$class}\"" : '';
				$out = "<{$tag}{$alignattr}{$classattr}>{$out}</{$tag}>";
			}

			// Add line numbers if 'lineno' param is true
			if (isset($params['lineno']) && $params['lineno'] === true) {
				$lines = explode("\n", $str);
				$num = count($lines);
				if ($num > 0) {
					$temp = "<table>\n<tr><td class=\"{$class}\" style=\"vertical-align: top; padding-right: 10px;\"><pre class=\"{$class}\">";
					for ($i = 1; $i <= $num; $i++) {
						$temp .= $i . "\n";
					}
					$temp .= "</pre></td><td class=\"{$class}\" style=\"vertical-align: top;\">{$out}</td></tr></table>\n";
					$out = $temp;
				}
				unset($lines);
			}

			return $out;
		}

		/**
		 * A function to recursively strip slashes.  Used to
		 * enforce magic_quotes_gpc being off.
		 * @param &var The variable to strip
		 */
		function stripVar(&$var) {
			if (is_array($var)) {
				foreach($var as $k => $v) {
					$this->stripVar($var[$k]);

					/* magic_quotes_gpc escape keys as well ...*/
					if (is_string($k)) {
						$ek = stripslashes($k);
						if ($ek !== $k) {
							$var[$ek] = $var[$k];
							unset($var[$k]);
						}
					}
				}
			}
			else
				$var = stripslashes($var);
		}

		/**
		 * Print out the page or section heading and the help
		 * @param $mainTitle Main title, already escaped
         *        $secondaryTitle (optional) Additional text in smaller size
		 *        $help (optional) Text for a help icon
		 */
		function printTitle($mainTitle, $secondaryTitle = null, $help = null) {

			echo "<h2>{$mainTitle}";
			if ($secondaryTitle)
				echo "<span class=\"sec-title\">$secondaryTitle</span>";
			if ($help)
				echo "<img src=\"{$this->icon('Info-inv')}\" alt=\"info\" title=\"{$help}\"/>";
			echo "</h2>\n";
		}

		/**
		 * Print out a subtitle in a h3 tag
		 * @param $mainTitle Main title, already escaped
         *        $secondaryTitle (optional) Additional text in smaller size
		 */
		function printSubtitle($mainTitle, $secondaryTitle = null) {

			echo "<h3>{$mainTitle}";
			if ($secondaryTitle)
				echo "<span class=\"sec-title\">$secondaryTitle</span>";
			echo "</h3>\n";
		}

		/**
		 * Build the record counter of a data collection that will be printed in title
         * An empty string is produced for empty collections
		 * @param $collection Data records collection
		 */
		function buildTitleRecordsCounter($collection) {

			if ($collection->recordCount() == 0)
				return '';
			else
				return "(x {$collection->recordCount()})";
		}

		/**
		* Print out a standart message and/or and error message
		* @param $msg			A (non error) message to print, if supplied
		*        $errMsg		An error message to print, if supplied
		*/
		function printMsg($msg, $errMsg = '') {
			if ($msg != '') echo "<p class=\"message\">{$msg}</p>\n";
			if ($errMsg != '') echo "<p class=\"error-message\">{$errMsg}</p>\n";
		}

		/**
		 * Creates a database accessor
		 */
		function getDatabaseAccessor($database, $server_id = null) {
			global $lang, $conf, $misc, $_connection, $postgresqlMinVer;

			$server_info = $this->getServerInfo($server_id);

			// Perform extra security checks if this config option is set
			if ($conf['extra_login_security']) {
				// Disallowed logins if extra_login_security is enabled.
				// These must be lowercase.
				$bad_usernames = array('pgsql', 'postgres', 'root', 'administrator');

				$username = strtolower($server_info['username']);

				if ($server_info['password'] == '' || in_array($username, $bad_usernames)) {
					unset($_SESSION['webdbLogin'][$_REQUEST['server']]);
					$msg = $lang['strlogindisallowed'];
					include('./login.php');
					exit;
				}
			}

			// Create the connection object and make the connection
			$_connection = new Connection(
				$server_info['host'],
				$server_info['port'],
				$server_info['sslmode'],
				$server_info['username'],
				$server_info['password'],
				$database
			);

			// Get the name of the database driver we need to use.
			// The description of the server is returned in $platform.
			$_type = $_connection->getDriver($platform);
			if ($_type === null) {
				printf($lang['strpostgresqlversionnotsupported'], $postgresqlMinVer);
				exit;
			}

			// Compute and store postgres platform and versions
			preg_match('/PostgreSQL (.*?)(\s|$)/', $platform, $pgVersion);
			preg_match('/(.*?)(devel|beta\d+|rc\d+|\.)/', $pgVersion[1], $pgMajorVersion);
			$this->setServerInfo('platform', $platform, $server_id);
			$this->setServerInfo('pgVersion', $pgVersion[1], $server_id);
			$this->setServerInfo('pgMajorVersion', $pgMajorVersion[1], $server_id);

			// Create a database wrapper class for easy manipulation of the
			// connection.
			include_once('./classes/database/' . $_type . '.php');
			$data = new $_type($_connection->conn);
			$data->platform = $_connection->platform;

			/* we work on UTF-8 only encoding */
			$data->execute("SET client_encoding TO 'UTF-8'");

			if ($data->hasByteaHexDefault()) {
				$data->execute("SET bytea_output TO escape");
			}

			return $data;
		}

		/**
		 * Performs initial checks on the existence of emaj extension, table, séquence or tables group,
		 * If some expected objects are missing, redirect the user to another predefined page.
		 * @param $check indicator of the kind of check to perform ('', 'emajgroup', 'table', 'sequence')
		 */
		function onErrorRedirect($check = '') {
			global $emajdb, $lang;

			$url = '';
			$href = $this->href;
			// Check the emaj extension.
			// Redirect to the emajenvir.php page if the emaj extension is not installed or is not accessible by the user
			if (!(isset($emajdb) && $emajdb->isEnabled() && $emajdb->isAccessible())) {
				$url = "emajenvir.php";
				$errMsg = $lang['stremajnowmissing'];
			} else {
				switch ($check) {
					case 'emajgroup':
						// Check the emaj group.
						// Redirect to the emajgroups.php page if the group does not exist anymore.
						if (! $emajdb->existsGroup($_REQUEST['group'])) {
							$url = "emajgroups.php";
							$errMsg = sprintf($lang['strgroupmissing'], htmlspecialchars($_REQUEST['group']));
						}
						break;
					case 'sequence':
						// Check the sequence.
						// Redirect to the emajschemas.php page if the schema or the sequence does not exist anymore.
						if (! $emajdb->existsSchema($_REQUEST['schema'])) {
							$url = "schemas.php";
							$errMsg = sprintf($lang['strschemamissing'], htmlspecialchars($_REQUEST['schema']));
							$href = $this->getHREF('schema');
						} else {
							if ($emajdb->missingTblSeqs($_REQUEST['schema'], $_REQUEST['sequence'], 'sequence')->fields['nb_tblseqs'] != 0) {
								$url = "schemas.php";
								$errMsg = sprintf($lang['strsequencemissing'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($_REQUEST['sequence']));
							}
						}
						break;
					case 'table':
						// Check the table.
						// Redirect to the emajschemas.php page if the schema or the sequence does not exist anymore.
						if (! $emajdb->existsSchema($_REQUEST['schema'])) {
							$url = "schemas.php";
							$errMsg = sprintf($lang['strschemamissing'], htmlspecialchars($_REQUEST['schema']));
							$href = $this->getHREF('schema');
						} else {
							if ($emajdb->missingTblSeqs($_REQUEST['schema'], $_REQUEST['table'], 'table')->fields['nb_tblseqs'] != 0) {
								$url = "schemas.php";
								$errMsg = sprintf($lang['strtablemissing'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($_REQUEST['table']));
							}
						}
						break;
				}
			}
			if ($url != '') {
				header("Location: $url?" . html_entity_decode($href) . "&errmsg=" . urlencode($errMsg));
				exit();
			}
		}

		/**
		 * Prints the html page header.
		 * @param $title The title of the page
		 * @param $script script tag
		 * @param $css a css file to include
		 * @param $frameset boolean set to true for the main php file
		 */
		function printHtmlHeader($title = '', $script = null, $css = null, $frameset = false) {
			global $appName, $lang, $conf;

			header("Content-Type: text/html; charset=utf-8");

			echo "<!DOCTYPE html>\n";
			echo "<html lang=\"{$lang['applocale']}\"";
			if (strcasecmp($lang['applangdir'], 'ltr') != 0) echo " dir=\"", htmlspecialchars($lang['applangdir']), "\"";
			echo ">\n";

			echo "<head>\n";
			echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";

			// Theme
			if (!$frameset) {
				echo "<link rel=\"stylesheet\" href=\"css/global.css\" type=\"text/css\" />\n";
			}
			if ($css)
				echo "<link rel=\"stylesheet\" href=\"css/{$css}.css\" type=\"text/css\" />\n";
			if ($frameset) {
				echo "<link rel=\"shortcut icon\" type=\"image/vnd.microsoft.icon\" href=\"images/Favicon.ico\" />\n";
				echo "<link rel=\"icon\" type=\"image/png\" href=\"images/EmajIcon.png\" />\n";
			}

			// Javascript
			if (!$frameset) {
				echo "<script src=\"js/global.js\"></script>\n";
				echo "<script src=\"libraries/js/jquery-3.6.4.min.js\"></script>\n";
				echo "<script src=\"libraries/js/jquery.tablesorter.min.js\"></script>\n";
				echo "<script src=\"libraries/js/jquery.tablesorter.widgets.min.js\"></script>\n";
			}
			if ($script) echo "{$script}\n";

			// Title
			echo "<title>", htmlspecialchars($appName);
			if ($title != '') echo htmlspecialchars(" - {$title}");
			echo "</title>\n";

			echo "</head>\n";
		}

		/**
		 * Prints the page body.
		 * @param $doBody True to output body tag, false otherwise
		 * @param $bodyClass - name of body class
		 */
		function printBody($bodyClass = '', $doBody = true ) {

			if ($doBody) {
				$bodyClass = htmlspecialchars($bodyClass);
				echo "<body", ($bodyClass == '' ? '' : " class=\"{$bodyClass}\"");
				echo ">\n";
			}
		}

		/**
		 * Prints the page header
		 * @param $trail = trail name, $urlvar = variables to add to the url for the refresh button
		 *        $tabs = name of the tabs bar to display (may be an empty string)
		 *        $activetab = name of ... the active tab in the tabs bar (may be an empty string)
		 */
		function printHeader($trail, $tabs, $activetab) {
			global $lang, $_reload_browser;

			// if no tabs bar is supplied, try to use the last displayed one
			if ($tabs == '' && isset($_SESSION['lastTabsBar'])) {
				$tabs = $_SESSION['lastTabsBar'];
			}

			$server_info = $this->getServerInfo();

			echo "<header>\n";

			// First header line
			echo "<div class=\"topbar\">\n";

			$this->printDateTime();
			$this->printConnInfo($server_info);
			$this->printLanguageInput();

			echo "</div>\n";

			// second header line
			echo "<div class=\"trail\">\n";

			$this->printTrail($trail);
			$this->printSqlLinks($server_info);
			$this->printBottomButton();

			echo "</div>\n";

			// third header line
			$this->printTabs($tabs, $activetab);

			echo "</header>\n";
			echo "<section>\n";

			// Display the error message justifying a redirection, if any.
			if (isset($_REQUEST['errmsg'])) {
				$this->printMsg('', $_REQUEST['errmsg']);
				$_reload_browser = true;
			}

			// keep in memory the last displayed tabs bar id
			$_SESSION['lastTabsBar'] = $tabs;
		}

		/**
		 * Prints the page footer
		 * @param $doBody True to output body tag, false otherwise
		 */
		function printFooter($doBody = true, $doBottomLink = true) {
			global $_reload_browser;
			global $lang, $_no_bottom_link;

			if ($doBody) {
				echo "</section>\n";
				echo "<footer>\n";

				// reload the browser if requested
				if (isset($_reload_browser)) {
					echo "<script>\n";
					echo "\tparent.frames.browser.location.reload();\n";
					echo "</script>\n";
				}

				// the button to reach the page top if requested
				if ($doBottomLink) {
					echo "\t<a id=\"bottom\">&nbsp;</a>\n";
					if (!isset($_no_bottom_link))
						echo "\t<a href=\"#\" class=\"bottom_link\"><img src=\"{$this->icon('Top')}\" alt=\"{$lang['strgotoppage']}\" title=\"{$lang['strgotoppage']}\"/></a>\n";
				}

				echo "</footer>\n";
				echo "</body>\n";
			}
			echo "</html>\n";
		}

		/**
		 * Prints the current date and time in the page header
		 */
		function printDateTime() {

			echo "\t<div class=\"datetime\"><span id=\"currentdate\"></span> - <span id=\"currenttime\"></span></div>\n";

			// let the client fill the current date and time div by calling a js function
			echo "<script>showDateTime();</script>\n";
		}

		/**
		 * Prints the user info and the disconnect button in the page header
		 */
		function printConnInfo($server_info) {
			global $data, $emajdb, $lang;

			echo "\t<div class=\"conninfo\">\n";

			if ($server_info && isset($server_info['platform']) && isset($server_info['username'])) {

				// print current user info
				echo "\t\t{$lang['struser']}&nbsp;<span class=\"username\">" . htmlspecialchars($server_info['username']) . "</span>\n";

				// print the tooltip to show E-Maj rights
				if ($data->isSuperUser($server_info['username']))
					$help = $lang['strusersuperuser'];
				elseif ($emajdb->isEmaj_Adm())
					$help = $lang['struseremajadm'];
				elseif ($emajdb->isEmaj_Viewer())
					$help = $lang['struseremajviewer'];
				else
					$help = $lang['strusernoright'];

				echo "<img src=\"{$this->icon('Info-inv')}\" class=\"help-icon\" alt=\"info\" title=\"{$help}\"/>";

				// disconnection button
				$logoutHref = htmlentities("servers.php?action=logout&logoutServer=" .
											urlencode("{$server_info['host']}:{$server_info['port']}:{$server_info['sslmode']}"));
				echo "\t\t<a href=\"$logoutHref\"><img src=\"{$this->icon('Logout-light')}\" class=\"button\" alt=\"{$lang['strlogout']}\" title=\"{$lang['strlogout']}\"  /></a>\n";
			}

			echo "\t</div>\n";
		}

		/**
		 * Prints the language input in the page header
		 */
		function printLanguageInput() {
			global $appLangFiles, $_reload_browser;

			echo "\t<div class=\"language\">\n";

			// if the language has just been changed, set the flag that will force the browser reload at the end of the page processing on client side
			if (isset($_POST['language']))
				$_reload_browser = true;

			$noArrayParam = 1;
			foreach ($_REQUEST as $key => $val) {
				if (is_array($val))
					$noArrayParam = 0;
			}

			// language selection form
			if (($_SERVER["REQUEST_METHOD"] == 'GET' || $_SERVER["REQUEST_METHOD"] == 'POST') && $noArrayParam)
				echo "\t\t<form method=\"post\">\n";
			else
				echo "\t\t<form method=\"post\" action=\"intro.php\">\n";

			echo "\t\t\t<select name=\"language\" onchange=\"this.form.submit()\">\n";
			$language = isset($_SESSION['webdbLanguage']) ? $_SESSION['webdbLanguage'] : 'english';
			foreach ($appLangFiles as $k => $v) {
				echo "\t\t\t\t<option value=\"{$k}\"",
					($k == $language) ? ' selected="selected"' : '',
					">{$v}</option>\n";
			}
			echo "\t\t\t</select>\n";
			echo "\t\t\t<noscript><input type=\"submit\" value=\"Set Language\"></noscript>\n";
			// replicate parameters
			foreach ($_REQUEST as $key => $val) {
				if ($key == 'language') continue;
				if (is_array($val)) continue;
				$cleanVal = htmlspecialchars($val);
				echo "\t\t\t<input type=\"hidden\" name=\"{$key}\" value=\"", $cleanVal, "\" />\n";
			}
			echo "\t\t</form>\n";

			echo "\t</div>\n";
		}

		/**
		 * Displays a bread crumb trail in the page header.
		 */
		function printTrail($trail = array()) {
			global $lang;

			echo "\t<div class=\"crumb\">\n";

			if ($trail != '') {

				if (is_string($trail)) {
					$trail = $this->getTrail($trail);
				}

				$firstElement = 1;
				foreach ($trail as $crumb) {
					if (!$firstElement)
						echo "\t\t &gt; ";

					$firstElement = 0;

					$crumblink = "<a";
					if (isset($crumb['url']))
						$crumblink .= " href=\"{$crumb['url']}\"";
					if (isset($crumb['title']))
						$crumblink .= " title=\"{$crumb['title']}\"";
					$crumblink .= ">";

					if (isset($crumb['title']))
						$iconalt = $crumb['title'];
					else
						$iconalt = 'Database Root';

					if (isset($crumb['icon']) && $icon = $this->icon($crumb['icon']))
						$crumblink .= "<span class=\"icon\"><img src=\"{$icon}\" alt=\"{$iconalt}\" /></span>";

					$crumblink .= "<span class=\"label\">" . htmlspecialchars($crumb['text']) . "</span></a>\n";

					echo $crumblink;
				}
			}

			echo "\t</div>\n";
		}

		/**
		 * Prints the SQL links in the page header
		 * Links are only available when connected
		 */
		function printSqlLinks($server_info) {
			global $lang;

			echo "\t<div class=\"connlinks\">\n";

			if ($server_info && isset($server_info['platform']) && isset($server_info['username'])) {

				$reqvars = $this->getRequestVars('table');

				$toplinks = array (
					'sql' => array (
						'attr' => array (
							'href' => array (
								'url' => 'sqledit.php',
								'urlvars' => array_merge($reqvars, array (
									'action' => 'sql'
								))
							),
							'target' => "sqledit",
							'id' => 'toplink_sql',
						),
						'content' => $lang['strsql']
					),
					'history' => array (
						'attr'=> array (
							'href' => array (
								'url' => 'history.php',
								'urlvars' => array_merge($reqvars, array (
									'action' => 'pophistory'
								))
							),
							'id' => 'toplink_history',
						),
						'content' => $lang['strhistory']
					),
				);

				$this->printLinksList($toplinks, 'toplink');

				$sql_window_id = htmlentities('sqledit:'.$_REQUEST['server']);
				$history_window_id = htmlentities('history:'.$_REQUEST['server']);

				echo "\t\t<script>\n";
				echo "\t\t\t$('#toplink_sql').click(function() {\n";
				echo "\t\t\t	window.open($(this).attr('href'),'{$sql_window_id}','toolbar=no,width=700,height=500,resizable=yes,scrollbars=yes').focus();\n";
				echo "\t\t\t	return false;\n";
				echo "\t\t\t});\n";
				echo "\t\t\t$('#toplink_history').click(function() {\n";
				echo "\t\t\t	window.open($(this).attr('href'),'{$history_window_id}','toolbar=no,width=700,height=500,resizable=yes,scrollbars=yes').focus();\n";
				echo "\t\t\t	return false;\n";
				echo "\t\t\t});\n";

				if (isset($_SESSION['sharedUsername'])) {
					echo "\t\t\t$('#toplink_logout').click(function() {\n";
					echo "\t\t\t	return confirm('{$lang['strconfdropcred']}');\n";
					echo "\t\t\t});";
				}

				echo "\n\t\t</script>\n";
			}

			echo "\t</div>\n";
		}

		/**
		 * Displays the button to go to the page bottom in the page header.
		 */
		function printBottomButton() {
			global $lang;

			// right cell containing the bottom button
			echo "\t<div class=\"trailicons\">\n";
			echo "\t\t<a href=\"#bottom\"><img src=\"{$this->icon('Bottom')}\" alt=\"{$lang['strpagebottom']}\" title=\"{$lang['strpagebottom']}\"  /></a>\n";
			echo "\t</div>\n";
		}

		/**
		 * Create a bread crumb trail of the object hierarchy.
		 * @param $object The type of object at the end of the trail.
		 */
		function getTrail($subject = null) {
			global $lang, $conf, $data, $appName;

			$trail = array();
			$vars = '';
			$done = false;

			$trail['root'] = array(
				'text'  => $appName,
				'url'   => 'redirect.php?subject=root',
				'icon'  => 'EmajIcon'
			);

			if ($subject == 'root') $done = true;

			if (!$done) {
				$server_info = $this->getServerInfo();
				$trail['server'] = array(
					'title' => $lang['strserver'] . ' (' . $server_info['host'] . ':' . $server_info['port'] . ')',
					'text'  => $server_info['desc'],
					'url'   => $this->getHREFSubject('server'),
					'icon'  => 'Server'
				);
			}
			if ($subject == 'server') $done = true;

			if (isset($_REQUEST['database']) && !$done) {
				$trail['database'] = array(
					'title' => $lang['strdatabase'],
					'text'  => $_REQUEST['database'],
					'url'   => $this->getHREFSubject('database'),
					'icon'  => 'Database'
				);
			}
			if ($subject == 'database') $done = true;

			if (isset($_REQUEST['schema']) && !$done) {
				$trail['schema'] = array(
					'title' => $lang['strschema'],
					'text'  => $_REQUEST['schema'],
					'url'   => $this->getHREFSubject('schema'),
					'icon'  => 'Schema'
				);
			}
			if ($subject == 'schema') $done = true;

			if (isset($_REQUEST['table']) && !$done) {
				$trail['table'] = array(
					'title' => $lang['strtable'],
					'text'  => $_REQUEST['table'],
					'url'   => $this->getHREFSubject('table'),
					'icon'  => 'Table'
				);
			}
			if ($subject == 'table') $done = true;

			if (isset($_REQUEST['sequence']) && !$done) {
				$trail['sequence'] = array(
					'title' => $lang['strsequence'],
					'text'  => $_REQUEST['sequence'],
					'url'   => $this->getHREFSubject('sequence'),
					'icon'  => 'Sequence'
				);
			}
			if ($subject == 'sequence') $done = true;

			if (isset($_REQUEST['group']) && !$done) {
				$trail['emaj'] = array(
					'title' => $lang['strtablesgroup'],
					'text'  => $_REQUEST['group'],
					'url'   => $this->getHREFSubject('emajgroup'),
					'icon'  => 'EmajGroup'
				);
			}
			if ($subject == 'group') $done = true;

			if (isset($_REQUEST['rlbkid']) && !$done) {
				$trail['emajrollback'] = array(
					'title' => $lang['strrollback'],
					'text'  => $_REQUEST['rlbkid'],
					'url'   => $this->getHREFSubject('emajrollback'),
					'icon'  => 'EmajRollback'
				);
			}
			if ($subject == 'rollback') $done = true;

			if (!$done && !is_null($subject)) {
				if (isset($_REQUEST[$subject])) {
					$trail[$subject] = array(
						'title' => $lang['str'.$subject],
						'text'  => $_REQUEST[$subject],
						'icon'  => null,
					);
				}
			}

			return $trail;
		}

		/**
		 * Display navigation tabs
		 * @param $tabs The name of current section (Ex: intro, server, ...), or an array with tabs (Ex: sqledit.php doFind function)
		 * @param $activetab The name of the tab to be highlighted.
		 */
		function printTabs($tabs, $activetab) {
			global $misc, $conf, $data, $lang;

			if (is_string($tabs)) {
				$_SESSION['webdbLastTab'][$tabs] = $activetab;
				$tabs = $this->getNavTabs($tabs);
			}

			$nbTabs = 0;
			foreach ($tabs as $tab_id => $tab) {
				if (!isset($tab['hide']) || $tab['hide'] !== true) $nbTabs++;
			}

			echo "<nav>\n";
			if ($nbTabs == 0) {
				// Special case when no tab has to be displayed.
				echo "\t<div style=\"width:100%; height:45px;\" class=\"tab-inactive\"></div>\n";
			} else {
				// Display tabs
				$width = (int)(100 / $nbTabs) . '%';
				foreach ($tabs as $tab_id => $tab) {
					$active = ($tab_id == $activetab) ? ' active' : '';

					if (!isset($tab['hide']) || $tab['hide'] !== true) {

						$tablink = "\t\t<a href=\"" . htmlentities($this->getActionUrl($tab, $_REQUEST)) . "\">";

						if (isset($tab['icon']) && $icon = $this->icon($tab['icon']))
							$tablink .= "<span class=\"icon\"><img src=\"{$icon}\" alt=\"{$tab['title']}\" /></span>";

						$tablink .= "{$tab['title']}</a>\n";

						echo "\t<div style=\"width: {$width}\" class=\"tab{$active}\">\n";
						echo $tablink;
						echo "\t</div>\n";
					}
				}
			}
			echo "</nav>\n";
		}

		/**
		 * Retrieve the tab info for a specific tab bar.
		 * @param $section The name of the tab bar.
		 */
		function getNavTabs($section) {
			global $data, $lang, $conf, $emajdb, $oldest_supported_emaj_version_num;

			// For rare cases when the group to process is unknown while asking for the 'emajgroup' tabs bar, switch to the 'database' tabs bar
			if ($section == 'emajgroup' && !isset($_REQUEST['group'])) {
				$section = 'database';
			}

			$tabs = array();

			switch ($section) {
				case 'root':
					$tabs = array (
						'intro' => array (
							'title' => $lang['strintroduction'],
							'url'   => "intro.php",
							'icon'  => 'EmajIcon',
						),
						'servers' => array (
							'title' => $lang['strservers'],
							'url'   => "servers.php",
							'icon'  => 'Servers',
						),
					);
					break;

				case 'server':
					$tabs = array (
						'databases' => array (
							'title' => $lang['strdatabases'],
							'url'   => 'databases.php',
							'urlvars' => array('subject' => 'server'),
							'icon'  => 'Databases',
						)
					);
					break;

				case 'database':
					$emajNotAvail = (!(isset($emajdb) && $emajdb->isEnabled() && $emajdb->isAccessible()
						&& $emajdb->getNumEmajVersion() >= $oldest_supported_emaj_version_num));
					$hideGroupConf = $emajNotAvail || !($emajdb->isEmaj_Adm()) || $emajdb->getNumEmajVersion() >= 30200;
					$hideActivity = $emajNotAvail || !($emajdb->isEmaj_Viewer()) || $emajdb->getNumEmajVersion() < 40500;

					$tabs = array (
						'emajgroups' => array (
							'title' => $lang['strgroups'],
							'url' => 'emajgroups.php',
							'urlvars' => array(
								'subject' => 'database',
								'action' => 'show_groups'
							),
							'hide' => $emajNotAvail,
							'icon' => 'EmajGroup',
							'branch' => true,
						),
						'emajconfiguregroups' => array (
							'title' => $lang['strgroupsconf'],
							'url' => 'emajgroupsconf.php',
							'urlvars' => array(
								'subject' => 'database',
								'action' => 'configure_groups'
							),
							'hide' => $hideGroupConf,
							'icon' => 'Admin',
							'tree' => false,
						),
						'schemas' => array (
							'title' => $lang['strschemas'],
							'url'   => 'schemas.php',
							'urlvars' => array(
								'subject' => 'database',
								'action' => 'list_schemas'
							),
							'icon'  => 'Schemas',
							'branch' => true,
						),
						'triggers' => array (
							'title' => $lang['strtriggers'],
							'url'   => 'triggers.php',
							'urlvars' => array(
								'subject' => 'database'
							),
							'icon'  => 'Triggers',
						),
						'emajrollbacks' => array (
							'title' => $lang['strrlbkop'],
							'url' => 'emajrollbacks.php',
							'urlvars' => array(
								'subject' => 'database',
								'action' => 'show_rollbacks'
							),
							'hide' => $emajNotAvail,
							'icon' => 'EmajRollback',
						),
						'emajactivity' => array (
							'title' => $lang['stractivity'],
							'url' => 'activity.php',
							'urlvars' => array(
								'subject' => 'database'
							),
							'hide' => $hideActivity,
							'icon' => 'Activity',
						),
						'emajenvir' => array (
							'title' => $lang['strenvir'],
							'url' => 'emajenvir.php',
							'urlvars' => array(
								'subject' => 'database'
							),
							'icon' => 'EmajIcon',
						)
					);
					break;

				case 'emajgroup':
					$droppedGroup = (! $emajdb->existsGroup($_REQUEST['group']));
					$historyNotAvail = ($emajdb->getNumEmajVersion() < 40400);
					$tabs = array (
						'emajgroupproperties' => array (
							'title' => $lang['strproperties'],
							'url' => 'groupproperties.php',
							'urlvars' => array(
								'subject' => 'emajgroups',
								'action' => 'show_group',
								'group' => $_REQUEST['group']
							),
							'hide' => $droppedGroup,
							'icon' => 'Property'
						),
						'emajchangesstat' => array (
							'title' => $lang['strchangesstat'],
							'url' => 'groupstat.php',
							'urlvars' => array(
								'subject' => 'emajgroups',
								'action' => 'changes_stat_group',
								'group' => $_REQUEST['group']
							),
							'hide' => $droppedGroup,
							'icon' => 'EmajStat'
						),
						'emajcontent' => array (
							'title' => $lang['strcontent'],
							'url' => 'groupcontent.php',
							'urlvars' => array(
								'subject' => 'emajgroups',
								'action' => 'show_content_group',
								'group' => $_REQUEST['group']
							),
							'hide' => $droppedGroup,
							'icon' => 'Content'
						),
						'emajhistory' => array (
							'title' => $lang['strhistory'],
							'url' => 'grouphistory.php',
							'urlvars' => array(
								'subject' => 'emajgroups',
								'action' => 'show_history_group',
								'group' => $_REQUEST['group']
							),
							'hide' => $historyNotAvail,
							'icon' => 'History'
						),
					);
					break;

				case 'table':
					$historyNotAvail = (!(isset($emajdb) && $emajdb->isEnabled() && $emajdb->isAccessible()
						&& $emajdb->getNumEmajVersion() >= 40400));
					$tabs = array (
						'properties' => array (
							'title' => $lang['strproperties'],
							'url'   => 'tblproperties.php',
							'urlvars' => array(
								'subject' => 'table',
								'table' => field('table')
							),
							'icon'  => 'Property',
							'branch'=> true,
						),
						'content' => array (
							'title' => $lang['strcontent'],
							'url'   => 'display.php',
							'urlvars' => array(
								'subject' => 'table',
								'table' => field('table')
							),
							'icon'  => 'Table',
							'branch'=> true,
						),
						'history' => array (
							'title' => $lang['strhistory'],
							'url'   => 'tblhistory.php',
							'urlvars' => array(
								'subject' => 'table',
								'table' => field('table')
							),
							'icon'  => 'History',
							'hide' => $historyNotAvail,
							'branch'=> true,
						),
					);
					break;

				case 'sequence':
					$historyNotAvail = (!(isset($emajdb) && $emajdb->isEnabled() && $emajdb->isAccessible()
						&& $emajdb->getNumEmajVersion() >= 40400));
					$tabs = array (
						'properties' => array (
							'title' => $lang['strproperties'],
							'url'   => 'seqproperties.php',
							'urlvars' => array(
								'action' => 'properties',
								'subject' => 'sequence',
								'sequence' => field('sequence')),
							'icon'  => 'Property',
							'branch'=> true,
						),
						'history' => array (
							'title' => $lang['strhistory'],
							'url'   => 'seqhistory.php',
							'urlvars' => array(
								'subject' => 'sequence',
								'sequence' => field('sequence')
							),
							'icon'  => 'History',
							'hide' => $historyNotAvail,
							'branch'=> true,
						),
					);
					break;

			}

			return $tabs;
		}

		/**
		 * Get the URL for the last active tab of a particular tab bar.
		 */
		function getLastTabURL($section) {
			global $data;

			$tabs = $this->getNavTabs($section);

			if (isset($_SESSION['webdbLastTab'][$section]) && isset($tabs[$_SESSION['webdbLastTab'][$section]]))
				$tab = $tabs[$_SESSION['webdbLastTab'][$section]];
			else
				$tab = reset($tabs);

			return isset($tab['url']) ? $tab : null;
		}

		/**
		 * Display a link
		 * @param $link An associative array of link parameters to print
		 *     link = array(
		 *       'attr' => array( // list of A tag attribute
		 *          'attrname' => attribute value
		 *          ...
		 *       ),
		 *       'content' => The link text
		 *       'icon' => (optional) if supplied, the icon replaces the 'content' text
		 *       'fields' => (optionnal) the data from which content and attr's values are obtained
		 *     );
		 *   the special attribute 'href' might be a string or an array. If href is an array it
		 *   will be generated by getActionUrl. See getActionUrl comment for array format.
		 */
		function printLink($link) {

			if (! isset($link['fields']))
				$link['fields'] = $_REQUEST;

			$tag = "<a ";
			foreach ($link['attr'] as $attr => $value) {
				if ($attr == 'href' and is_array($value)) {
					$tag.= 'href="'. htmlentities($this->getActionUrl($value, $link['fields'])).'" ';
				} else {
					$tag.= htmlentities($attr).'="'. value($value, $link['fields'], 'html') .'" ';
				}
			}
			$content = value($link['content'], $link['fields'], 'html');

			if (! isset($link['icon'])) {
				$tag.= ">". $content ."</a>";
			} else {
				$iconFile = $this->icon($link['icon']);
				$tag.= "><img src=\"{$iconFile}\" alt=\"{$content}\" title=\"{$content}\" /></a>";
			}
			echo $tag;
		}

		/**
		 * Display a list of links
		 * @param $links An associative array of links to print. See printLink function for
		 *               the links array format.
		 * @param $class An optional class or list of classes seprated by a space
		 *   WARNING: This field is NOT escaped! No user should be able to inject something here, use with care.
		 */
		function printLinksList($links, $class='') {
			echo "\t\t<ul class=\"{$class}\">\n";
			foreach ($links as $link) {
				echo "\t\t\t<li>";
				$this->printLink($link);
				echo "</li>\n";
			}
			echo "\t\t</ul>\n";
		}

		/**
		* Display the navlinks
		*
		* @param $navlinks - An array with the attributes and values that will be shown. See printLinksList for array format.
		*/
		function printNavLinks($navlinks) {

			if (count($navlinks) > 0) {
				$this->printLinksList($navlinks, 'navlink');
			}
		}

		/**
		 * Do multi-page navigation.  Displays the prev, next and page options.
		 * @param $page - the page currently viewed
		 * @param $pages - the maximum number of pages
		 * @param $gets -  the parameters to include in the link to the wanted page
		 * @param $max_width - the number of pages to make available at any one time (default = 20)
		 */
		function printPages($page, $pages, $gets, $max_width = 20) {
			global $lang;

			$window = 10;

			if ($page < 0 || $page > $pages) return;
			if ($pages < 0) return;
			if ($max_width <= 0) return;

			unset ($gets['page']);
			$url = http_build_query($gets);

			if ($pages > 1) {
				echo "<p style=\"text-align: center\">\n";
				if ($page != 1) {
					echo "<a class=\"pagenav\" href=\"?{$url}&amp;page=1\">{$lang['strfirst']}</a>\n";
					$temp = $page - 1;
					echo "<a class=\"pagenav\" href=\"?{$url}&amp;page={$temp}\">{$lang['strprev']}</a>\n";
				}

				if ($page <= $window) {
					$min_page = 1;
					$max_page = min(2 * $window, $pages);
				}
				elseif ($page > $window && $pages >= $page + $window) {
					$min_page = ($page - $window) + 1;
					$max_page = $page + $window;
				}
				else {
					$min_page = ($page - (2 * $window - ($pages - $page))) + 1;
					$max_page = $pages;
				}

				// Make sure min_page is always at least 1
				// and max_page is never greater than $pages
				$min_page = max($min_page, 1);
				$max_page = min($max_page, $pages);

				for ($i = $min_page; $i <= $max_page; $i++) {
					if ($i != $page) echo "<a class=\"pagenav\" href=\"?{$url}&amp;page={$i}\">$i</a>\n";
					else echo "$i\n";
				}
				if ($page != $pages) {
					$temp = $page + 1;
					echo "<a class=\"pagenav\" href=\"?{$url}&amp;page={$temp}\">{$lang['strnext']}</a>\n";
					echo "<a class=\"pagenav\" href=\"?{$url}&amp;page={$pages}\">{$lang['strlast']}</a>\n";
				}
				echo "</p>\n";
			}
		}

		/**
		 * Outputs JavaScript to set default focus
		 * @param $object eg. forms[0].username
		 */
		function setFocus($object) {
			echo "<script>\n";
			echo "   document.{$object}.focus();\n";
			echo "</script>\n";
		}

		/**
		 * Outputs JavaScript to set the name of the browser window.
		 * @param $name the window name
		 * @param $addServer if true (default) then the server id is
		 *        attached to the name.
		 */
		function setWindowName($name, $addServer = true) {
			echo "<script>\n";
			echo "//<![CDATA[\n";
			echo "   window.name = '{$name}", ($addServer ? ':'.htmlspecialchars($_REQUEST['server']) : ''), "';\n";
			echo "//]]>\n";
			echo "</script>\n";
		}

		/**
		 * Converts a PHP.INI size variable to bytes.  Taken from publically available
		 * function by Chris DeRose, here: http://www.php.net/manual/en/configuration.directives.php#ini.file-uploads
		 * @param $strIniSize The PHP.INI variable
		 * @return size in bytes, false on failure
		 */
		function inisizeToBytes($strIniSize) {
			// This function will take the string value of an ini 'size' parameter,
			// and return a double (64-bit float) representing the number of bytes
			// that the parameter represents. Or false if $strIniSize is unparseable.
			$a_IniParts = array();

			if (!is_string($strIniSize))
				return false;

			if (!preg_match ('/^(\d+)([bkm]*)$/i', $strIniSize,$a_IniParts))
				return false;

			$nSize = (double) $a_IniParts[1];
			$strUnit = strtolower($a_IniParts[2]);

			switch($strUnit) {
				case 'm':
					return ($nSize * (double) 1048576);
				case 'k':
					return ($nSize * (double) 1024);
				case 'b':
				default:
					return $nSize;
			}
		}

		/**
		 * Returns URL given an action associative array.
		 * NOTE: this function does not html-escape, only url-escape
		 * @param $action An associative array of the follow properties:
		 *			'url'  => The first part of the URL (before the ?)
		 *			'urlvars' => Associative array of (URL variable => field name)
		 *						these are appended to the URL
		 * @param $fields Field data from which 'urlfield' and 'vars' are obtained.
		 */
		function getActionUrl(&$action, &$fields) {
			$url = value($action['url'], $fields);

			if ($url === false) return '';

			if (!empty($action['urlvars'])) {
				$urlvars = value($action['urlvars'], $fields);
			} else {
				$urlvars = array();
			}

			/* set server, database and schema parameter if not presents */
			if (isset($urlvars['subject']))
				$subject = value($urlvars['subject'], $fields);
			else
				$subject = '';
			
			if (isset($_REQUEST['server']) and !isset($urlvars['server']) and $subject != 'root') {
				$urlvars['server'] = $_REQUEST['server'];
				if (isset($_REQUEST['database']) and !isset($urlvars['database']) and $subject != 'server') {
					$urlvars['database'] = $_REQUEST['database'];
					if (isset($_REQUEST['schema']) and !isset($urlvars['schema']) and $subject != 'database') {
						$urlvars['schema'] = $_REQUEST['schema'];
					}
				}
			}

			$sep = '?';
			foreach ($urlvars as $var => $varfield) {
				$url .= $sep . value_url($var, $fields) . '=' . value_url($varfield, $fields);
				$sep = '&';
			}

			return $url;
		}

		function getRequestVars($subject = '') {
			$v = array();
			if (!empty($subject))
				$v['subject'] = $subject;
			if (isset($_REQUEST['server']) && $subject != 'root') {
				$v['server'] = $_REQUEST['server'];
				if (isset($_REQUEST['database']) && $subject != 'server') {
					$v['database'] = $_REQUEST['database'];
					if (isset($_REQUEST['schema']) && $subject != 'database') {
						$v['schema'] = $_REQUEST['schema'];
						if (isset($_REQUEST['group']) && $subject != 'emaj') {
							$v['group'] = $_REQUEST['group'];
						}
					}
				}
			}
			return $v;
		}

		function printUrlVars(&$vars, &$fields) {
			foreach ($vars as $var => $varfield) {
				echo "{$var}=", urlencode($fields[$varfield]), "&amp;";
			}
		}

		/**
		 * Display a table of data.
		 * @param $tabledata 	The data set to be formatted, as returned by $data->getDatabases() etc.
		 * @param $columns   	An associative array of columns to be displayed, like:
		 *		$columns = array(
		 *			column_id => array(
		 *				'title' => Column heading,
		 *				'field' => Field name for $tabledata->fields[...],
		 *				'info'  => Info Icon with a help message,
		 *				'type' => field type if not simple text ('callback', 'spanned', 'numeric',...),
		 *				'params' => array of additional parameters for the callback or the rendering,
		 *				'url' => url to add a link to the field,
		 *				'vars' => variables array to use for the url,
		 *				'sorter' => boolean to overhide the table level sorter option,
		 *				'filter' => boolean to overhide the table level filter option,
		 *				'sorter_text_extraction' => alternate element used to sort ('img_alt', 'span_text' or 'div_title'),
		 *				'upper_title' => upper part of column header when it is split into 2 rows,
		 *				'upper_title_colspan' => number of contigous columns for the upper_title,
		 *			), ...
		 *		);
		 * @param $actions   	Actions that can be performed on each object:
		 *		$actions = array(
		 *			* multi action support *
		 *			* parameters are serialized for each entries and given in $_REQUEST['ma']
		 *			'multiactions' => array(
		 *				'keycols' => Associative array of (URL variable => field name), // fields included in the form
		 *				'url' => URL submission,
		 *				'default' => Default selected action in the form.
		 *								if null, an empty action is added & selected
		 *			),
		 *			* actions *
		 *			action_id => array(
		 *				'title' => Action heading,
		 *				'url'   => Static part of URL.  Often we rely
		 *						   relative urls, usually the page itself (not '' !), or just a query string,
		 *				'vars'  => Associative array of (URL variable => field name),
		 *				'multiaction' => Name of the action to execute.
		 *									Add this action to the multi action form
		 *			), ...
		 *		);
		 * @param $place    	Place where the $actions are displayed. Like 'display-browse', where 'display' represents the page file
		 *                  	(display.php) and 'browse' the function in that page (doBrowse).
		 * @param $nodata   	(optional) Message to display if the data set is empty.
		 * @param $pre_fn   	(optional) Name of a function to call for each row.
		 *                  	It may be used to derive new fields or modify actions.
		 *                  	Two params will be passed: $rowdata and $actions.
		 *                  	It may return an array of specific actions for the row, or nothing to use the standart actions
		 *                  	(see tblproperties.php for examples).
		 *                  	The function must not store urls because they are relative and won't work out of context.
		 * @param $tablesorter	(optional) array of jquery tablesorter plugin option. It may take 2 values:
		 *                  	- sorter: to activate the sort feature on columns
		 *                  	- filter: to activate the filter feature on columns
		 *                  	This defines a default behaviour for the entire table (excepting for actions).
		 *                  	Some specific behaviour can be defined at column level.
		 */
		function printTable(&$tabledata, &$columns, &$actions, $place, $nodata = null, $pre_fn = null, $tablesorter = null) {
			global $data, $conf, $lang;

			if ($has_ma = isset($actions['multiactions'])) {
				$ma = $actions['multiactions'];
			}
			unset($actions['multiactions']);

			// The 7th parameter defines if the tablesorter JQuery plugin is used for this table, with the sorter and/or the filter functionalities
			$sorter = 0; $filter = 0;
			if (!is_null($tablesorter) && isset($tablesorter['sorter'])) { $sorter = $tablesorter['sorter']; }
			if (!is_null($tablesorter) && isset($tablesorter['filter'])) { $filter = $tablesorter['filter']; }

			if ($tabledata->recordCount() > 0) {

				if ($has_ma) {
					echo "<form id=\"{$place}\" action=\"{$ma['url']}\" method=\"post\" enctype=\"multipart/form-data\">\n";
					if (isset($ma['vars']))
						foreach ($ma['vars'] as $k => $v)
							echo "<input type=\"hidden\" name=\"$k\" value=\"" . htmlspecialchars($v) . "\" />\n";
				} else {
					echo "<div id=\"{$place}\">\n";
				}

				if ($sorter || $filter)
					echo "<table class=\"data table-sorter\">\n";
				else
					echo "<table class=\"data\">\n";
				echo "<thead>\n";
				echo "<tr>\n";

				//
				// Display column headings
				//
				$colnum = 0; $textExtractionJS = ''; $headersJS = '';

				// Determine whether the header is on 2 rows
				$upperTitle = false;
				foreach ($columns as $column_id => $column) {
					if (isset($column['upper_title'])) {
						$upperTitle = true;
					}
				}

				if ($has_ma || $filter) {
					if ($filter) {
						$rowspanClause = ($upperTitle) ? "rowspan=2" : '';
						echo "<th class=\"data sorter-false filter-false filtericon\" $rowspanClause>";
						echo "\t<img src=\"{$this->icon('Filter')}\" alt=\"filter\" class=\"action\" onclick=\"javascript:showHideFilterRow('{$place}');\" title=\"{$lang['strfiltershelp']}\">\n";
						echo "</th>\n";
					} else {
						echo "<th class=\"data sorter-false filter-false\"></th>\n";
					}
					$colnum++;
				}

				// First title row
				$firstDataColnum = $colnum;
				$colToBypass = 0;
				foreach ($columns as $column_id => $column) {
					if ($colToBypass > 0) {
						// Subsequent column of an upper title
						$colToBypass--;
						$colnum++;
					} elseif (isset($column['upper_title'])) {
						// First column of an upper title
						if (isset($column['upper_title_colspan'])) {
							$colspanClause = "colspan={$column['upper_title_colspan']}";
							$colToBypass = $column['upper_title_colspan'] - 1;
						} else {
							$colspanClause = '';
							$colToBypass = 0;
						}
						echo "<th class=\"data sorter-false filter-false\" $colspanClause>{$column['upper_title']}\n";
						// additional info if requested
						if (isset($column['upper_title_info']))
							echo "<img src=\"{$this->icon('Info-inv')}\" alt=\"info\" class=\"info\" title=\"{$column['upper_title_info']}\">";
						echo "</th>\n";
						$colnum = $colnum + 1 + $colToBypass;
					} else {
						// Regular column
						$colspan = ($column_id == 'actions') ? count($actions) : 1;
						$rowspan = ($upperTitle) ? 2 : 1;
						$this->printColumnHeader($column_id, $column, $filter, $sorter, $colspan, $rowspan, $colnum, $headersJS, $textExtractionJS);
						$colnum = $colnum + (($column_id == 'actions') ? count($actions) : 1);
					}
				}

				// Second title row, if any
				$colnum = $firstDataColnum;
				if ($upperTitle) {
					echo "</tr><tr>\n";
					$colTitleToDisplay = 0;
					foreach ($columns as $column_id => $column) {
						if (isset($column['upper_title'])) {
							$colTitleToDisplay = (isset($column['upper_title_colspan'])) ? $column['upper_title_colspan'] : 1;
						}
						if ($colTitleToDisplay > 0) {
							$this->printColumnHeader($column_id, $column, $filter, $sorter, 1, 1, $colnum, $headersJS, $textExtractionJS);
							$colTitleToDisplay--;
						}
						$colnum = $colnum + (($column_id == 'actions') ? count($actions) : 1);
					}
				}
				echo "</tr>\n";
				echo "</thead>\n";
				echo "<tbody>\n";

				//
				// Display table rows
				//
				$i = 0;
				while (!$tabledata->EOF) {
					$id = ($i % 2) + 1;

					unset($alt_actions);
					if (!is_null($pre_fn)) {
						if (is_string($pre_fn)) {
							$alt_actions = $pre_fn($tabledata, $actions);
						} else {
							$alt_actions = $pre_fn[0]->{$pre_fn[1]}($tabledata, $actions);
						}
					}
					if (!isset($alt_actions)) $alt_actions =& $actions;

					echo "<tr class=\"data{$id}\">\n";
					if ($has_ma) {
						foreach ($ma['keycols'] as $k => $v)
							$a[$k] = $tabledata->fields[$v];
						echo "<td class=\"center\">";
						echo "<input type=\"checkbox\" name=\"ma[]\" value=\"". htmlentities(serialize($a), ENT_COMPAT, 'UTF-8') ."\" onclick=\"javascript:countChecked('{$place}');\"";
						if (isset($ma['checked']) && $ma['checked'])
							echo " checked";
						echo "/></td>\n";
					} else {
						if ($filter)
							echo "<td></td>\n";
					}

					foreach ($columns as $column_id => $column) {

						// Apply default values for missing parameters
						if (isset($column['url']) && !isset($column['vars'])) $column['vars'] = array();

						switch ($column_id) {
							case 'actions':
								foreach ($alt_actions as $action) {
									$classMulti = (isset($action['multiaction'])) ? ' multi_' . $action['multiaction'] : '';
									if (isset($action['disable']) && $action['disable'] === true) {
										echo "<td class=\"emptybutton{$classMulti}\"></td>\n";
									} else {
										$classObjType = (isset($action['icon'])) ? 'iconbutton' : 'textbutton';
										echo "<td class=\"opbutton{$id} {$classObjType}{$classMulti}\">";
										$action['fields'] = $tabledata->fields;
										$this->printLink($action);
										echo "</td>\n";
									}
								}
								break;
							default:
								$class = (isset($column['class'])) ? " class=\"{$column['class']}\"" : '';
								echo "<td{$class}>";
								$val = value($column['field'], $tabledata->fields);
								if (!is_null($val)) {
									if (isset($column['url'])) {
										echo "<a href=\"{$column['url']}";
										$this->printUrlVars($column['vars'], $tabledata->fields);
										echo "\">";
									}
									$type = isset($column['type']) ? $column['type'] : null;
									$params = isset($column['params']) ? $column['params'] : array();
									echo $this->printVal($val, $type, $params);
									if (isset($column['url'])) echo "</a>";
								}

								echo "</td>\n";
								break;
						}
					}
					echo "</tr>\n";

					$tabledata->moveNext();
					$i++;
				}
				echo "</tbody>\n";
				echo "</table>\n";

				// Multi action table footer with selectors and action buttons
				if ($has_ma) {
					if (isset($ma['checked']) && $ma['checked'])
						$nbSelectedObject = $i;
					else
						$nbSelectedObject = 0;
					echo "<table class=\"multiactions\">\n";
					echo "<tr>\n";
					echo "<th class=\"multiactions\">{$lang['strselect']}</th>\n";
					echo "<th class=\"multiactions selectedcounter\">" . sprintf($lang['stractionsonselectedobjects'],$nbSelectedObject) ."</th>\n";
					echo "</tr>\n";
					echo "<tr class=\"row1\">\n";
					echo "\t<td>\n";
					echo "\t\t&nbsp;<a onclick=\"javascript:checkSelect('all','{$place}');countChecked('{$place}');\" class=\"action\">{$lang['strall']}</a>&nbsp;/\n";
					if ($filter) {
						echo "\t\t&nbsp;<a onclick=\"javascript:checkSelect('filtered','{$place}');countChecked('{$place}');\" class=\"action\">{$lang['strvisible']}</a>&nbsp;/\n";
					}
					echo "\t\t&nbsp;<a onclick=\"javascript:checkSelect('none','{$place}');countChecked('{$place}');\" class=\"action\">{$lang['strnone']}</a>&nbsp;/\n";
					echo "\t\t&nbsp;<a onclick=\"javascript:checkSelect('invert','{$place}');countChecked('{$place}');\" class=\"action\">{$lang['strinvert']}</a>&nbsp;\n";
					if ($place == 'defineGroupTblseq') {
						// This selector is specific to the groups configuration page
						echo "\t\t/&nbsp;<a onclick=\"javascript:checkSelect('notassigned','{$place}');countChecked('{$place}');\" class=\"action\">{$lang['strnotassigned']}</a>&nbsp;\n";
					}
					echo "\t</td><td class=\"multiactionbuttons\">\n";
					if (isset($ma['checked']) && $ma['checked'])
						$disabledButton = "";
					else
						$disabledButton = " disabled";
					foreach($actions as $k => $a)
						if (isset($a['multiaction'])) {
							echo "\t\t<button id=\"{$a['multiaction']}\" name=\"action\" value=\"{$a['multiaction']}\" ";
							if (! isset($a['icon'])) {
								echo "class=\"text\"{$disabledButton}>{$a['content']}";
							} else {
								echo "class=\"icon\" style=\"background-image: url('{$this->icon($a['icon'])}');\" {$disabledButton} title=\"{$a['content']}\">";
							}
							echo "</button>\n";
						}
					echo $this->form;
					echo "</td>\n";
					echo "</tr>\n";
					echo "</table>\n";
					if (!isset($ma['close_form']) || $ma['close_form']) {
						echo "</form>\n";
					}
				} else {
					echo "</div>\n";
				};

				// generate the javascript for the tablesorter JQuery plugin
				if ($sorter || $filter) {
					echo "<script>\n";
					echo "\t$(document).ready(function() {\n";
					echo "\t\t$(\"#{$place} table\").tablesorter( {\n";
					echo "\t\t\twidthFixed : true,\n";
					if ($headersJS <> '') {
						echo "\t\t\theaders: {\n";
						echo $headersJS;
						echo "\t\t\t\t},\n";
					}
					if ($textExtractionJS <> '') {
						echo "\t\t\ttextExtraction: {\n";
						echo $textExtractionJS;
						echo "\t\t\t\t},\n";
					}
					echo "\t\t\temptyTo: 'none',\n";
					echo "\t\t\twidgets: [\"zebra\"";
					if ($filter) { echo ", \"filter\""; }
					echo "],\n";
					echo "\t\t\twidgetOptions: {\n";
					echo "\t\t\t\tzebra : [ \"data1\", \"data2\" ],\n";
					echo "\t\t\t},\n";
					echo "\t\t});\n";
					echo "\t\taddFilterResetButton('{$place}');\n";
					echo "\t\taddFilterEvent('{$place}');\n";
					echo "\t\tshowHideFilterRow('{$place}');\n";
					echo "\t});\n";
					echo "</script>\n";
				}

				return true;
			} else {
				if (!is_null($nodata)) {
					echo "<p>{$nodata}</p>\n";
				}
				return false;
			}
		}

		/** Print a column header for a data table, with the filtering
		 *  and sorting capabilities. It also process actions columns.
		 */
		function printColumnHeader($column_id, $column, $filter, $sorter, $colspan, $rowspan, $colnum, &$headersJS, &$textExtractionJS) {
			global $lang;

			// prepare the rowspan and colspan clause, if needed
			$colspanClause = ($colspan > 1) ? "colspan=$colspan" : "";
			$rowspanClause = ($rowspan > 1) ? "rowspan=$rowspan" : "";

			if ($column_id == 'actions') {
			// Actions columns
				if ($colspan > 0)
					echo "<th class=\"data sorter-false filter-false\" $colspanClause $rowspanClause>{$column['title']}</th>\n";

			} else {
			// Other columns
				// prepare the class clause
				$thClass = 'data';
				//   add a 'sorter_false' class to the data column header if a 'sorter' attribute is set to false
				if ((isset($column['sorter']) && !$column['sorter']) || ($filter && ! $sorter)) {
					$thClass .=	' sorter-false';
				}
				//   add a 'filter_false' class to the data column header if a 'filter' attribute is set to false
				if ($filter && (isset($column['filter']) && !$column['filter'])) {
					$thClass .= ' filter-false';
				}

				// column title
				echo "<th class=\"{$thClass}\" $rowspanClause $colspanClause>";
				echo $column['title'];

				// additional info if requested
				if (isset($column['info']))
					echo "<img src=\"{$this->icon('Info-inv')}\" alt=\"info\" class=\"info\" title=\"{$column['info']}\">";
				echo "</th>\n";

				// when the data column has a 'sorter_text_extraction' attribute set to 'img_alt' or 'span_text' or 'div_title',
				//   add a function to extract either the alt attribute of images or the text content of the span
				//     to build the text that tablesorter will use to sort
				if ($sorter && isset($column['sorter_text_extraction'])) {
					if ($column['sorter_text_extraction'] == 'img_alt') {
						$textExtractionJS .= "\t\t\t\t{$colnum}: function(node) {return $(node).find('img').attr('alt');},\n";
					} elseif ($column['sorter_text_extraction'] == 'span_text') {
						$textExtractionJS .= "\t\t\t\t{$colnum}: function(node) {return $(node).find('span').text();},\n";
					} elseif ($column['sorter_text_extraction'] == 'div_title') {
						$textExtractionJS .= "\t\t\t\t{$colnum}: function(node) {return $(node).find('div').find('div').attr('title');},\n";
					}
				}
				// When the column is of type 'spanned' with a 'dateformat' parameter, force a simple text sort
				if ($sorter && strpos($thClass, 'sorter-false') === false &&
					isset($column['type']) && $column['type'] == 'spanned' && isset($column['params']['dateformat'])) {
					$headersJS .= "\t\t\t\t{$colnum}: {sorter: 'text'},\n";
				}
			}
		}

		/** Produce XML data for the browser tree
		 * @param $treedata A set of records to populate the tree.
		 * @param $attrs Attributes for tree items
		 *        'text' - the text for the tree node
		 *        'icon' - an icon for node
		 *        'openIcon' - an alternative icon when the node is expanded
		 *        'toolTip' - tool tip text for the node
		 *        'action' - URL to visit when single clicking the node
		 *        'iconAction' - URL to visit when single clicking the icon node
		 *        'branch' - URL for child nodes (tree XML)
		 *        'expand' - the action to return XML for the subtree
		 *        'nodata' - message to display when node has no children
		 * @param $section The section where the branch is linked in the tree
		 */
		function printTree(&$_treedata, &$attrs, $section) {

			$treedata = array();

			if ($_treedata->recordCount() > 0) {
				while (!$_treedata->EOF) {
					$treedata[] = $_treedata->fields;
					$_treedata->moveNext();
				}
			}

			$tree_params = array(
				'treedata' => &$treedata,
				'attrs' => &$attrs,
				'section' => $section
			);

			$this->printTreeXML($treedata, $attrs);
		}

		/** Produce XML data for the browser tree
		 * @param $treedata A set of records to populate the tree.
		 * @param $attrs Attributes for tree items
		 *        'text' - the text for the tree node
		 *        'icon' - an icon for node
		 *        'openIcon' - an alternative icon when the node is expanded
		 *        'toolTip' - tool tip text for the node
		 *        'action' - URL to visit when single clicking the node
		 *        'iconAction' - URL to visit when single clicking the icon node
		 *        'branch' - URL for child nodes (tree XML)
		 *        'expand' - the action to return XML for the subtree
		 *        'nodata' - message to display when node has no children
		 */
		function printTreeXML(&$treedata, &$attrs) {
			global $conf, $lang;

			header("Content-Type: text/xml; charset=UTF-8");
			header("Cache-Control: no-cache");

			echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";

			echo "<tree>\n";

			if (count($treedata) > 0) {
				foreach($treedata as $rec) {

					echo "<tree";
					echo value_xml_attr('text', $attrs['text'], $rec);
					echo value_xml_attr('action', $attrs['action'], $rec);
					echo value_xml_attr('src', $attrs['branch'], $rec);

					$icon = $this->icon(value($attrs['icon'], $rec));
					echo value_xml_attr('icon', $icon, $rec);
					echo value_xml_attr('iconaction', $attrs['iconAction'], $rec);

					if (!empty($attrs['openicon'])) {
						$icon = $this->icon(value($attrs['openIcon'], $rec));
					}
					echo value_xml_attr('openicon', $icon, $rec);

					echo value_xml_attr('tooltip', $attrs['toolTip'], $rec);

					echo " />\n";
				}
			} else {
				$msg = isset($attrs['nodata']) ? $attrs['nodata'] : $lang['strnoobjects'];
				echo "<tree text=\"{$msg}\" onaction=\"tree.getSelected().getParent().reload()\" icon=\"", $this->icon('ObjectNotFound'), "\" />\n";
			}

			echo "</tree>\n";
		}

		function adjustTabsForTree(&$tabs) {
			include_once('./classes/ArrayRecordSet.php');

			foreach ($tabs as $i => $tab) {
				if ((isset($tab['hide']) && $tab['hide'] === true) || (isset($tab['tree']) && $tab['tree'] === false)) {
					unset($tabs[$i]);
				}
			}
			return new ArrayRecordSet($tabs);
		}

		function icon($icon) {
			$path = "images/{$icon}";
			if (file_exists($path.'.svg')) return $path.'.svg';
			if (file_exists($path.'.png')) return $path.'.png';
			if (file_exists($path.'.gif')) return $path.'.gif';
			return '';
		}

		/**
		 * Function to escape command line programs
		 * @param $str The string to escape
		 * @return The escaped string
		 */
		function escapeShellCmd($str) {
			global $data;

			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$data->fieldClean($str);
				return '"' . $str . '"';
			}
			else
				return escapeshellcmd($str);
		}

		/**
		 * Get list of servers' groups if existing in the conf
		 * @return a recordset of servers' groups
		 */
		function getServersGroups($recordset = false, $group_id = false) {
			global $conf, $lang;
			$grps = array();

			if (isset($conf['srv_groups'])) {
				foreach ($conf['srv_groups'] as $i => $group) {
					if (
						(($group_id === false) and (! isset($group['parents']))) /* root */
						or (
							($group_id !== false)
							and isset($group['parents'])
							and in_array($group_id,explode(',',
									preg_replace('/\s/', '', $group['parents'])
								))
						) /* nested group */
					)
						$grps[$i] = array(
							'id' => $i,
							'desc' => $group['desc'],
							'icon' => 'Servers',
							'action' => url('servers.php',
								array(
									'group' => field('id')
								)
							),
							'branch' => url('servers.php',
								array(
									'action' => 'tree',
									'group' => $i
								)
							)
						);
				}

				if ($group_id === false)
					$grps['all'] = array(
						'id' => 'all',
						'desc' => $lang['strallservers'],
						'icon' => 'Servers',
						'action' => url('servers.php',
							array(
								'group' => field('id')
							)
						),
						'branch' => url('servers.php',
								array(
									'action' => 'tree',
									'group' => 'all'
								)
							)
					);
			}

			if ($recordset) {
				include_once('./classes/ArrayRecordSet.php');
				return new ArrayRecordSet($grps);
			}

			return $grps;
		}
		

		/**
		 * Get list of servers
		 * @param $recordset return as RecordSet suitable for printTable if true,
		 *                   otherwise just return an array.
		 * @param $group a group name to filter the returned servers using $conf[srv_groups]
		 */
		function getServers($recordset = false, $group = false) {
			global $conf;

			$logins = isset($_SESSION['webdbLogin']) && is_array($_SESSION['webdbLogin']) ? $_SESSION['webdbLogin'] : array();
			$srvs = array();

			if (($group !== false) and ($group !== 'all'))
				if (isset($conf['srv_groups'][$group]['servers']))
					$group = array_fill_keys(explode(',', preg_replace('/\s/', '',
						$conf['srv_groups'][$group]['servers'])), 1);
				else
					$group = '';

			foreach($conf['servers'] as $idx => $info) {
				$server_id = $info['host'].':'.$info['port'].':'.$info['sslmode'];
				if (($group === false) or (isset($group[$idx]))	or ($group === 'all')) {
					$server_id = $info['host'].':'.$info['port'].':'.$info['sslmode'];

					if (isset($logins[$server_id]))
						$srvs[$server_id] = $logins[$server_id];
					else
						$srvs[$server_id] = $info;

					$srvs[$server_id]['id'] = $server_id;
					$srvs[$server_id]['action'] = url('redirect.php',
						array(
							'subject' => 'server',
							'server' => field('id')
						)
					);
					if (isset($srvs[$server_id]['username'])) {
						$srvs[$server_id]['icon'] = 'Server';
						$srvs[$server_id]['branch'] = url('databases.php',
							array(
								'action' => 'tree',
								'subject' => 'server',
								'server' => field('id')
							));
					} else {
						$srvs[$server_id]['icon'] = 'DisconnectedServer';
						$srvs[$server_id]['branch'] = false;
					}
				}
			}

			function _cmp_desc($a, $b) {
				return strcmp($a['desc'], $b['desc']);
			}
			uasort($srvs, '_cmp_desc');

			if ($recordset) {
				include_once('./classes/ArrayRecordSet.php');
				return new ArrayRecordSet($srvs);
			}
			return $srvs;
		}

		/**
		 * Validate and retrieve information on a server.
		 * If the parameter isn't supplied then the currently
		 * connected server is returned.
		 * @param $server_id A server identifier (host:port)
		 * @return An associative array of server properties
		 */
		function getServerInfo($server_id = null) {
			global $conf, $_reload_browser, $lang;

			if ($server_id === null && isset($_REQUEST['server']))
				$server_id = $_REQUEST['server'];

			// Check for the server in the logged-in list
			if (isset($_SESSION['webdbLogin'][$server_id]))
				return $_SESSION['webdbLogin'][$server_id];

			// Otherwise, look for it in the conf file
			foreach($conf['servers'] as $idx => $info) {
				if ($server_id == $info['host'].':'.$info['port'].':'.$info['sslmode']) {
					// Automatically use shared credentials if available
					if (!isset($info['username']) && isset($_SESSION['sharedUsername'])) {
						$info['username'] = $_SESSION['sharedUsername'];
						$info['password'] = $_SESSION['sharedPassword'];
						$_reload_browser = true;
						$this->setServerInfo(null, $info, $server_id);
					}

					return $info;
				}
			}

			if ($server_id === null){
				return null;
			} else {
				// Unable to find a matching server, are we being hacked?
				echo $lang['strinvalidserverparam'];
				exit;
			}
		}

		/**
		 * Set server information.
		 * @param $key parameter name to set, or null to replace all
		 *             params with the assoc-array in $value.
		 * @param $value the new value, or null to unset the parameter
		 * @param $server_id the server identifier, or null for current
		 *                   server.
		 */
		function setServerInfo($key, $value, $server_id = null)
		{
			if ($server_id === null && isset($_REQUEST['server']))
				$server_id = $_REQUEST['server'];

			if ($key === null) {
				if ($value === null)
					unset($_SESSION['webdbLogin'][$server_id]);
				else
					$_SESSION['webdbLogin'][$server_id] = $value;
			} else {
				if ($value === null)
					unset($_SESSION['webdbLogin'][$server_id][$key]);
				else
					$_SESSION['webdbLogin'][$server_id][$key] = $value;
			}
		}
		
		/**
		 * Set the current schema
		 * @param $schema The schema name
		 * @return 0 on success
		 * @return $data->seSchema() on error
		 */
		function setCurrentSchema($schema) {
			global $data;
			
			$status = $data->setSchema($schema);
			if($status != 0)
				return $status;

			$_REQUEST['schema'] = $schema;
			$this->setHREF();
			return 0;
		}

		/**
		 * Save the given SQL script in the history 
		 * of the database and server.
		 * @param $script the SQL script to save.
		 */
		function saveScriptHistory($script) {
			list($usec, $sec) = explode(' ', microtime());
			$time = ((float)$usec + (float)$sec);
			$_SESSION['history'][$_REQUEST['server']][$_REQUEST['database']]["$time"] = array(
				'query' => $script,
				'paginate' => (!isset($_REQUEST['paginate'])? 'f':'t'),
				'queryid' => $time,
			);
		}
	
		/*
		 * Output dropdown list to select server and 
		 * databases form the popups windows.
		 * @param $onchange Javascript action to take when selections change.
		 */	
		function printConnection($onchange) {
			global $data, $lang, $misc;

			echo "<table style=\"width: 100%\"><tr><td>\n";
			echo "<label>{$lang['strserver']}</label>: <select name=\"server\" {$onchange}>\n";
			
			$servers = $misc->getServers();
			foreach($servers as $info) {
				if (empty($info['username'])) continue; // not logged on this server
				echo "<option value=\"", htmlspecialchars($info['id']), "\"",
					((isset($_REQUEST['server']) && $info['id'] == $_REQUEST['server'])) ? ' selected="selected"' : '', ">",
					htmlspecialchars("{$info['desc']} ({$info['id']})"), "</option>\n";
			}
			echo "</select>\n</td><td style=\"text-align: right\">\n";
			
			// Get the list of all databases
			$databases = $data->getDatabases();

			if ($databases->recordCount() > 0) {

				echo "<label>{$lang['strdatabase']}: <select name=\"database\" {$onchange}>\n";
				
				//if no database was selected, user should select one
				if (!isset($_REQUEST['database']))
					echo "<option value=\"\">--</option>\n";
				
				while (!$databases->EOF) {
					$dbname = $databases->fields['datname'];
					echo "<option value=\"", htmlspecialchars($dbname), "\"",
						((isset($_REQUEST['database']) && $dbname == $_REQUEST['database'])) ? ' selected="selected"' : '', ">",
						htmlspecialchars($dbname), "</option>\n";
					$databases->moveNext();
				}
				echo "</select></label>\n";
			}
			else {
				$server_info = $misc->getServerInfo();
				echo "<input type=\"hidden\" name=\"database\" value=\"", 
					htmlspecialchars($server_info['defaultdb']), "\" />\n";
			}
			
			echo "</td></tr></table>\n";
		}

		/*
		 * Schedule the page reload when auto-refresh is set.
		 * It also stores the page scroll position at page reload time.
		 * It needs the js/global.js file.
		 * @param $url = the url to load.
		 *        $timeout = the timer.
		 */
		function schedulePageReload($url, $timeout) {

			echo "<script>\n";
			// when the document will be ready, scroll to the position saved in sessionStorage, if it exists.
			echo "\t$(document).ready(function() {\n";
			echo "\t\tvar storedScrollposition = sessionStorage.getItem('autorefresh-scroll');\n";
			echo "\t\tif (storedScrollposition) {\n";
			echo "\t\t\t$(document).scrollTop(storedScrollposition);\n";
			echo "\t\t}\n";
			echo "\t});\n";
			// And immediately schedule the page reload.
			echo "\tschedulePageReload({$timeout}, '" . htmlspecialchars_decode($url) . "');\n";
			echo "</script>\n";
		}
	}
?>
