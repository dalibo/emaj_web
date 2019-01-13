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
		function Misc() {
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
					$vars = array ('params' => array(
						'server' => $_REQUEST['server'],
						'subject' => 'server'
					));
					break;
				case 'database':
					$vars = array('params' => array(
						'server' => $_REQUEST['server'],
						'subject' => 'database',
						'database' => $_REQUEST['database'],
					));
					break;
				case 'schema':
					$vars = array('params' => array(
						'server' => $_REQUEST['server'],
						'subject' => 'schema',
						'database' => $_REQUEST['database'],
						'schema' => $_REQUEST['schema']
					));
					break;
				case 'table':
					$vars = array('params' => array(
						'server' => $_REQUEST['server'],
						'subject' => 'table',
						'database' => $_REQUEST['database'],
						'schema' => $_REQUEST['schema'],
						'table' => $_REQUEST['table']
					));
					break;
				case 'selectrows':
					$vars = array(
						'url' => 'tables.php',
						'params' => array(
							'server' => $_REQUEST['server'],
							'subject' => 'table',
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'table' => $_REQUEST['table'],
							'action' => 'confselectrows'
					));
					break;
				case 'column':
						$vars = array('params' => array(
							'server' => $_REQUEST['server'],
							'subject' => 'column',
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'table' => $_REQUEST['table'],
							'column' => $_REQUEST['column']
						));
					break;
				case 'emaj':
					$vars = array (
						'url' => 'emajgroups.php',
						'params' => array (
							'server' => $_REQUEST['server'],
							'subject' => 'emaj',
							'action' => $_REQUEST['action'],
							'database' => $_REQUEST['database'],
							'group' => $_REQUEST['group'],
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
		 *
		 * @param $params Type parameters (optional), known parameters:
		 *			null     - string to display if $str is null, or set to TRUE to use a default 'NULL' string,
		 *			           otherwise nothing is rendered.
		 *			clip     - if true, clip the value to a fixed length, and append an ellipsis...
		 *			cliplen  - the maximum length when clip is enabled (defaults to $conf['max_chars'])
		 *			ellipsis - the string to append to a clipped value (defaults to $lang['strellipsis'])
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
		 * Print out the page heading and help link
		 * @param $title Title, already escaped
		 * @param $help (optional) The identifier for the help link
		 */
		function printTitle($title, $help = null) {
			global $data, $lang;

			echo "<h2>{$title}</h2>\n";
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
			global $lang, $conf, $misc, $_connection;

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
			$this->setServerInfo('platform', $platform, $server_id);
			$this->setServerInfo('pgVersion', $_connection->conn->pgVersion, $server_id);

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
		 * Prints the html page header.
		 * @param $title The title of the page
		 * @param $script script tag
		 */
		function printHtmlHeader($title = '', $script = null, $css = null, $frameset = false) {
			global $appName, $lang, $conf;

			header("Content-Type: text/html; charset=utf-8");
			// Send XHTML headers, or regular XHTML strict headers
			echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			if ($frameset == true) {
				echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">\n";
			} else if (isset($conf['use_xhtml_strict']) && $conf['use_xhtml_strict']) {
				echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-Strict.dtd\">\n";
			} else {
				echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
			}
			echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$lang['applocale']}\" lang=\"{$lang['applocale']}\"";
			if (strcasecmp($lang['applangdir'], 'ltr') != 0) echo " dir=\"", htmlspecialchars($lang['applangdir']), "\"";
			echo ">\n";

			echo "<head>\n";
			echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";

			// Theme
			if (!$frameset) {
				echo "<link rel=\"stylesheet\" href=\"css/global.css\" type=\"text/css\" />\n";
				if ($css)
					echo "<link rel=\"stylesheet\" href=\"css/{$css}.css\" type=\"text/css\" />\n";
			} else {
				echo "<link rel=\"shortcut icon\" type=\"image/vnd.microsoft.icon\" href=\"images/Favicon.ico\" />\n";
				echo "<link rel=\"icon\" type=\"image/png\" href=\"images/EmajwebIcon.png\" />\n";
			}

			// Javascript
			if (!$frameset) {
				echo "<script type=\"text/javascript\" src=\"js/multiactionform.js\"></script>\n";
				echo "<script type=\"text/javascript\" src=\"libraries/js/jquery-3.3.1.min.js\"></script>\n";
				echo "<script type=\"text/javascript\" src=\"libraries/js/jquery.tablesorter.min.js\"></script>\n";
				echo "<script type=\"text/javascript\" src=\"libraries/js/jquery.tablesorter.widgets.min.js\"></script>\n";
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
		 *        $tabs = name of the tabs bar to display, $activetab = name of ... the active tab
		 */
		function printHeader($trail, $tabs, $activetab) {
			global $lang;

			echo "<header>\n";

			$this->printTopbar();

			if ($trail != '') {
				$this->printTrail($trail);
			}

			if ($tabs != '') {
				$this->printTabs($tabs, $activetab);
			};

			echo "</header>\n";
		}

		/**
		 * Prints the top bar
		 */
		function printTopbar() {
			global $lang, $conf, $appName, $appVersion, $appLangFiles, $_reload_browser;

			$server_info = $this->getServerInfo();
			$reqvars = $this->getRequestVars('table');

			echo "<div class=\"topbar\">\n\t<div class=\"conninfo\">";

			if ($server_info && isset($server_info['platform']) && isset($server_info['username'])) {
				/* top left informations when connected */
				echo sprintf($lang['strtopbar'],
					'<span class="host">'.htmlspecialchars((empty($server_info['host'])) ? 'localhost':$server_info['host']).'</span>',
					'<span class="port">'.htmlspecialchars($server_info['port']).'</span>',
					'<span class="username">'.htmlspecialchars($server_info['username']).'</span>');
				echo "</div>\n";

				/* top right links when connected */

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
					'logout' => array(
						'attr' => array (
							'href' => array (
								'url' => 'servers.php',
								'urlvars' => array (
									'action' => 'logout',
									'logoutServer' => "{$server_info['host']}:{$server_info['port']}:{$server_info['sslmode']}"
								)
							),
							'id' => 'toplink_logout',
						),
						'content' => $lang['strlogout']
					)
				);

				echo "\t<div class=\"connlinks\">";
				$this->printLinksList($toplinks, 'toplink');

				$sql_window_id = htmlentities('sqledit:'.$_REQUEST['server']);
				$history_window_id = htmlentities('history:'.$_REQUEST['server']);

				echo "\t<script type=\"text/javascript\">
						$('#toplink_sql').click(function() {
							window.open($(this).attr('href'),'{$sql_window_id}','toolbar=no,width=700,height=500,resizable=yes,scrollbars=yes').focus();
							return false;
						});
						$('#toplink_history').click(function() {
							window.open($(this).attr('href'),'{$history_window_id}','toolbar=no,width=700,height=500,resizable=yes,scrollbars=yes').focus();
							return false;
						});";

				if (isset($_SESSION['sharedUsername'])) {
					printf("
						$('#toplink_logout').click(function() {
							return confirm('%s');
						});", str_replace("'", "\'", $lang['strconfdropcred']));
				}

				echo "\n\t</script>\n";
			}
			else {
				echo "<span class=\"appname\">{$appName}</span> <span class=\"version\">{$appVersion}</span>";
			}
			echo "</div>\n";

			// language change management
			// if the language has just been changed, set the flag that will force the browser reload at the end of the page processing on client side 
			if (isset($_GET['language']))
				$_reload_browser = true;

			// language selection
			echo "\t<div class=\"language\">";
			if ($_SERVER["REQUEST_METHOD"] == 'GET')
				echo "<form method=\"get\">";
			else
				echo "<form method=\"get\" action=\"intro.php\">";
			echo "<select name=\"language\" onchange=\"this.form.submit()\">\n";
			$language = isset($_SESSION['webdbLanguage']) ? $_SESSION['webdbLanguage'] : 'english';
			foreach ($appLangFiles as $k => $v) {
				echo "<option value=\"{$k}\"",
					($k == $language) ? ' selected="selected"' : '',
					">{$v}</option>\n";
			}
			echo "</select>\n";
			echo "<noscript><input type=\"submit\" value=\"Set Language\"></noscript>\n";
			if ($_SERVER["REQUEST_METHOD"] == 'GET') {
				foreach ($_GET as $key => $val) {
					if ($key == 'language') continue;
					echo "<input type=\"hidden\" name=\"{$key}\" value=\"", htmlspecialchars($val), "\" />\n";
				}
			}
			echo "</form>\n";
			echo "\t</div>\n";

			echo "</div>\n";
		}

		/**
		 * Prints the page footer
		 * @param $doBody True to output body tag, false otherwise
		 */
		function printFooter($doBody = true, $doBottomLink = true) {
			global $_reload_browser;
			global $lang, $_no_bottom_link;

			if ($doBody) {
				echo "<footer>\n";

				// reload the browser if requested
				if (isset($_reload_browser)) {
					echo "<script type=\"text/javascript\">\n";
					echo "\tparent.frames.browser.location.reload();\n";
					echo "</script>\n";
				}

				// the button to reach the page top if requested
				if ($doBottomLink) {
					echo "\t<a name=\"bottom\">&nbsp;</a>\n";
					if (!isset($_no_bottom_link))
						echo "\t<a href=\"#\" class=\"bottom_link\"><img src=\"{$this->icon('Top')}\" alt=\"{$lang['strgotoppage']}\" title=\"{$lang['strgotoppage']}\"/></a>\n";
				}

				echo "</footer>\n";
				echo "</body>\n";
			}
			echo "</html>\n";
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
				}
				else {
					$tag.= htmlentities($attr).'="'. value($value, $link['fields'], 'html') .'" ';
				}
			}
			$tag.= ">". value($link['content'], $link['fields'], 'html') ."</a>";
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
			echo "<ul class=\"{$class}\">\n";
			foreach ($links as $link) {
				echo "\t\t<li>";
				$this->printLink($link);
				echo "</li>\n";
			}
			echo "\t</ul>\n";
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
			if ($nbTabs != 0)
				$width = (int)(100 / $nbTabs) . '%';
			else
				$width = '100%';

			echo "<nav>\n";
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

			echo "</nav>\n";
		}

		/**
		 * Retrieve the tab info for a specific tab bar.
		 * @param $section The name of the tab bar.
		 */
		function getNavTabs($section) {
			global $data, $lang, $conf, $emajdb;

			$tabs = array();

			switch ($section) {
				case 'root':
					$tabs = array (
						'intro' => array (
							'title' => $lang['strintroduction'],
							'url'   => "intro.php",
							'icon'  => 'EmajwebIcon',
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
							'url'   => 'all_db.php',
							'urlvars' => array('subject' => 'server'),
							'icon'  => 'Databases',
						)
					);
					break;

				case 'database':
					$tabs = array (
						'emajgroups' => array (
							'title' => $lang['emajgroups'],
							'url' => 'emajgroups.php',
							'urlvars' => array(
								'subject' => 'database',
								'action' => 'show_groups'
							),
							'icon' => 'EmajGroup',
						),
						'emajconfiguregroups' => array (
							'title' => $lang['emajgroupsconf'],
							'url' => 'emajgroupsconf.php',
							'urlvars' => array(
								'subject' => 'database',
								'action' => 'configure_groups'
							),
							'hide' => !($emajdb->isEmaj_Adm()),
							'icon' => 'Admin',
							'tree' => false,
						),
						'emajrollbacks' => array (
							'title' => $lang['emajrlbkop'],
							'url' => 'emajrollbacks.php',
							'urlvars' => array(
								'subject' => 'database',
								'action' => 'show_rollbacks'
							),
							'icon' => 'EmajRollback',
							'tree' => false,
						),
						'schemas' => array (
							'title' => $lang['strschemas'],
							'url'   => 'schemas.php',
							'urlvars' => array('subject' => 'database'),
							'icon'  => 'Schemas',
						),
						'emajenvir' => array (
							'title' => $lang['emajenvir'],
							'url' => 'emajenvir.php',
							'urlvars' => array('subject' => 'database'),
							'icon' => 'Emaj',
							'tree' => false,
						)
					);
					break;

				case 'emajgroup':
					$tabs = array (
						'emajgroupproperties' => array (
							'title' => $lang['strproperties'],
							'url' => 'emajgroups.php',
							'urlvars' => array(
								'subject' => 'emajgroups',
								'action' => 'show_group',
								'group' => $_REQUEST['group']
							),
							'icon' => 'Property'
						),
						'emajlogstat' => array (
							'title' => $lang['emajlogstat'],
							'url' => 'emajgroups.php',
							'urlvars' => array(
								'subject' => 'emajgroups',
								'action' => 'log_stat_group',
								'group' => $_REQUEST['group']
							),
							'icon' => 'EmajStat'
						),
						'emajcontent' => array (
							'title' => $lang['emajcontent'],
							'url' => 'emajgroups.php',
							'urlvars' => array(
								'subject' => 'emajgroups',
								'action' => 'show_content_group',
								'group' => $_REQUEST['group']
							),
							'icon' => 'Tablespace'
						),
					);
					break;

				case 'schema':
					$tabs = array (
						'tables' => array (
							'title' => $lang['strtables'],
							'url'   => 'tables.php',
							'urlvars' => array('subject' => 'schema'),
							'icon'  => 'Tables',
						),
						'sequences' => array (
							'title' => $lang['strsequences'],
							'url'   => 'sequences.php',
							'urlvars' => array('subject' => 'schema'),
							'icon'  => 'Sequences',
						),
					);
					break;

				case 'table':
					$tabs = array (
						'columns' => array (
							'title' => $lang['strcolumns'],
							'url'   => 'tblproperties.php',
							'urlvars' => array('subject' => 'table', 'table' => field('table')),
							'icon'  => 'Columns',
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
		 * Display a bread crumb trail.
		 * ... and the buttons to refresh the page and to go to the page bottom
		 */
		function printTrail($trail = array()) {
			global $lang;

			if (is_string($trail)) {
				$trail = $this->getTrail($trail);
			}
			echo "<div class=\"trail\">\n";
			echo "  <div class=\"crumb\">\n";

			$firstElement = 1;
			foreach ($trail as $crumb) {
				if (!$firstElement)
					echo " &gt; ";

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

				$crumblink .= "<span class=\"label\">" . htmlspecialchars($crumb['text']) . "</span></a>";

				echo $crumblink;
			}
			echo "  </div>\n";

			// right cell containing the bottom button
			echo "\t<div class=\"trailicons\">\n";
			echo "\t\t<a href=\"#bottom\"><img src=\"{$this->icon('Bottom')}\" alt=\"{$lang['emajpagebottom']}\" title=\"{$lang['emajpagebottom']}\"  /></a>\n";
			echo "\t</div>\n";
			echo "</div>\n";
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
				'icon'  => 'EmajwebIcon'
			);

			if ($subject == 'root') $done = true;

			if (!$done) {
				$server_info = $this->getServerInfo();
				$trail['server'] = array(
					'title' => $lang['strserver'],
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

			if (isset($_REQUEST['group']) && !$done) {
				$trail['emaj'] = array(
					'title' => 'E-Maj',
					'text'  => $_REQUEST['group'],
					'url'   => $this->getHREFSubject('emaj'),
					'icon'  => 'Emaj'
				);
			}
			if ($subject == 'group') $done = true;

			if (!$done && !is_null($subject)) {
				switch ($subject) {
					case 'column':
						$trail['column'] = array (
							'title' => $lang['strcolumn'],
							'text'  => $_REQUEST['column'],
							'icon'	=> 'Column',
							'url'   => $this->getHREFSubject('column')
						);
						break;
					default:
						if (isset($_REQUEST[$subject])) {
							switch ($subject) {
								case 'sequence': $icon = 'Sequence'; break;
								default: $icon = null; break;
							}
							$trail[$subject] = array(
								'title' => $lang['str'.$subject],
								'text'  => $_REQUEST[$subject],
								'icon'  => $icon,
							);
						}
				}
			}

			return $trail;
		}

		/**
		* Display the navlinks
		*
		* @param $navlinks - An array with the the attributes and values that will be shown. See printLinksList for array format.
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
			echo "<script type=\"text/javascript\">\n";
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
			echo "<script type=\"text/javascript\">\n";
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
		 * @param $tabledata A set of data to be formatted, as returned by $data->getDatabases() etc.
		 * @param $columns   An associative array of columns to be displayed:
		 *			$columns = array(
		 *				column_id => array(
		 *					'title' => Column heading,
		 *					'field' => Field name for $tabledata->fields[...],
		 *					'help'  => Help page for this column,
		 *				), ...
		 *			);
		 * @param $actions   Actions that can be performed on each object:
		 *			$actions = array(
		 *				* multi action support
		 *				* parameters are serialized for each entries and given in $_REQUEST['ma']
		 *				'multiactions' => array(
		 *					'keycols' => Associative array of (URL variable => field name), // fields included in the form
		 *					'url' => URL submission,
		 *					'default' => Default selected action in the form.
		 *									if null, an empty action is added & selected
		 *				),
		 *				* actions *
		 *				action_id => array(
		 *					'title' => Action heading,
		 *					'url'   => Static part of URL.  Often we rely
		 *							   relative urls, usually the page itself (not '' !), or just a query string,
		 *					'vars'  => Associative array of (URL variable => field name),
		 *					'multiaction' => Name of the action to execute.
		 *										Add this action to the multi action form
		 *				), ...
		 *			);
		 * @param $place     Place where the $actions are displayed. Like 'display-browse', where 'display' is the file (display.php)
		 *                   and 'browse' is the place inside that code (doBrowse).
		 * @param $nodata    (optional) Message to display if data set is empty.
		 * @param $pre_fn    (optional) Name of a function to call for each row,
		 *					 it will be passed two params: $rowdata and $actions,
		 *					 it may be used to derive new fields or modify actions.
		 *					 It can return an array of actions specific to the row,
		 *					 or if nothing is returned then the standard actions are used.
		 *					 (see tblproperties.php and constraints.php for examples)
		 *					 The function must not must not store urls because
		 *					 they are relative and won't work out of context.
		 * @param $tablesorter (optional) array of jquery tablesorter plugin option. It may take 2 values:
		 *					 - sorter: to activate the sort feature on columns
		 *					 - filter: to activate the filter feature on columns
		 *					 This defines a default behaviour for the entire table (excepting for actions).
		 *					 Some specific behavious can be defined at column level.
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

				// Remove the 'comment' column if they have been disabled
				if (!$conf['show_comments']) {
					unset($columns['comment']);
				}

				if (isset($columns['comment'])) {
					// Uncomment this for clipped comments.
					// TODO: This should be a user option.
					//$columns['comment']['params']['clip'] = true;
				}

				if ($has_ma) {
					echo "<form id=\"{$place}\" action=\"{$ma['url']}\" method=\"post\" enctype=\"multipart/form-data\">\n";
					if (isset($ma['vars']))
						foreach ($ma['vars'] as $k => $v)
							echo "<input type=\"hidden\" name=\"$k\" value=\"$v\" />";
				} else {
					echo "<div id=\"{$place}\">\n";
				}

				if ($sorter || $filter)
					echo "<table class=\"data table-sorter\">\n";
				else
					echo "<table class=\"data\">\n";
				echo "<thead>\n";
				echo "<tr>\n";

				// Display column headings
				$colnum = 0; $textExtractionJS = '';
				if ($has_ma) {
					echo "<th class=\"data sorter-false filter-false\"></th>\n";
					$colnum++;
				}
				foreach ($columns as $column_id => $column) {
					switch ($column_id) {
						case 'actions':
							if (sizeof($actions) > 0) echo "<th class=\"data sorter-false filter-false\" colspan=\"", count($actions), "\">{$column['title']}</th>\n";
							// actions columns have neither sorter nor filter capabilities
							for ($i = 0; $i < count($actions); ++$i) {
								$colnum++;
							}
							break;
						default:
							// add a sorter_false class to the data column header if a 'sorter' attribute is set to false
							$class_sorter = '';
							if ((isset($column['sorter']) && !$column['sorter']) || ($filter && ! $sorter))
								$class_sorter = ' sorter-false';
							// add a filter_false class to the data column header if a 'filter' attribute is set to false
							$class_filter = '';
							if ($filter && (isset($column['filter']) && !$column['filter']))
								$class_filter = ' filter-false';
							echo "<th class=\"data{$class_sorter}{$class_filter}\">";
							if (isset($column['help']))
								$this->printHelp($column['title'], $column['help']);
							else
								echo $column['title'];
							echo "</th>\n";
							// when the data column has a 'sorter_text_extraction' attribute set to 'img_alt',
							//   add a function to extract the alt attribute of images to build the text that tablesorter will use to sort
							if ($sorter && isset($column['sorter_text_extraction']) && $column['sorter_text_extraction'] = 'img_alt') {
								$textExtractionJS .= "\t\t\t\t$colnum: function(s) {return $(s).find('img').attr('alt');}\n";
							}
							$colnum++;
							break;
					}
				}
				echo "</tr>\n";
				echo "</thead>\n";
				echo "<tbody>\n";

				// Display table rows
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
						echo "<td>";
						echo "<input type=\"checkbox\" name=\"ma[]\" value=\"". htmlentities(serialize($a), ENT_COMPAT, 'UTF-8') ."\" onclick=\"javascript:countChecked('{$place}');\"/>";
						echo "</td>\n";
					}

					foreach ($columns as $column_id => $column) {

						// Apply default values for missing parameters
						if (isset($column['url']) && !isset($column['vars'])) $column['vars'] = array();

						switch ($column_id) {
							case 'actions':
								foreach ($alt_actions as $action) {
									if (isset($action['disable']) && $action['disable'] === true) {
										echo "<td></td>\n";
									} else {
										echo "<td class=\"opbutton{$id}\">";
										$action['fields'] = $tabledata->fields;
										$this->printLink($action);
										echo "</td>\n";
									}
								}
								break;
							default:
								echo "<td>";
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

				// Multi action table footer w/ options & [un]check'em all
				if ($has_ma) {
					echo "<table class=\"multiactions\">\n";
					echo "<tr>\n";
					echo "<th class=\"multiactions\">{$lang['strselect']}</th>\n";
					echo "<th class=\"multiactions\" id=\"selectedcounter\">{$lang['stractionsonselectedobjects']}</th>\n";
					echo "</tr>\n";
					echo "<tr class=\"row1\">\n";
					echo "\t<td>\n";
					echo "\t\t&nbsp;<a href=\"#\" onclick=\"javascript:checkSelect('all','{$place}');countChecked('{$place}');\">{$lang['strall']}</a>&nbsp;/\n";
					echo "\t\t&nbsp;<a href=\"#\" onclick=\"javascript:checkSelect('none','{$place}');countChecked('{$place}');\">{$lang['strnone']}</a>&nbsp;/\n";
					echo "\t\t&nbsp;<a href=\"#\" onclick=\"javascript:checkSelect('invert','{$place}');countChecked('{$place}');\">{$lang['strinvert']}</a>&nbsp;\n";
					echo "\t</td><td>\n";
					foreach($actions as $k => $a)
						if (isset($a['multiaction']))
							echo "\t\t<button id=\"{$a['multiaction']}\" name=\"action\" value=\"{$a['multiaction']}\" disabled=\"true\" >{$a['content']}</button>\n";
					echo $this->form;
					echo "</td>\n";
					echo "</tr>\n";
					echo "</table>\n";
					echo '</form>';
				} else {
					echo "</div>\n";
				};

				// generate the javascript for the tablesorter JQuery plugin
				if ($sorter || $filter) {
					echo "<script type=\"text/javascript\">\n";
					echo "\t$(document).ready(function() {\n";
					echo "\t\t$(\"#{$place} table\").tablesorter( {\n";
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
					echo "\t\t\t\tfilter_hideFilters : true,\n";
					echo "\t\t\t\t},\n";
					echo "\t\t\t}\n";
					echo "\t\t);\n";
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
			if (file_exists($path.'.png')) return $path.'.png';
			if (file_exists($path.'.gif')) return $path.'.gif';
			return '';
		}

		/**
		 * Function to escape command line parameters
		 * @param $str The string to escape
		 * @return The escaped string
		 */
		function escapeShellArg($str) {
			global $data, $lang;

			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				// Due to annoying PHP bugs, shell arguments cannot be escaped
				// (command simply fails), so we cannot allow complex objects
				// to be dumped.
				if (preg_match('/^[_.[:alnum:]]+$/', $str))
					return $str;
				else {
					echo $lang['strcannotdumponwindows'];
					exit;
				}
			}
			else
				return escapeshellarg($str);
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
				if (($group === false) 
					or (isset($group[$idx]))
					or ($group === 'all')
				) {
					$server_id = $info['host'].':'.$info['port'].':'.$info['sslmode'];
					
					if (isset($logins[$server_id])) $srvs[$server_id] = $logins[$server_id];
					else $srvs[$server_id] = $info;

					$srvs[$server_id]['id'] = $server_id;
					$srvs[$server_id]['action'] = url('redirect.php',
						array(
							'subject' => 'server',
							'server' => field('id')
						)
					);
					if (isset($srvs[$server_id]['username'])) {
						$srvs[$server_id]['icon'] = 'Server';
						$srvs[$server_id]['branch'] = url('all_db.php',
							array(
								'action' => 'tree',
								'subject' => 'server',
								'server' => field('id')
							)
						);
					}
					else {
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

		/**
		* Check that the emaj extension exists in the current database, is accessible by the current user and is not too old
		*/
		function checkEmajExtension() {
			global $lang, $emajdb, $oldest_supported_emaj_version_num;
	
		// For all but the emaj_envir functions,
			// if Emaj is not usable for this database, only display a message
			if (!(isset($emajdb) && $emajdb->isEnabled() && $emajdb->isAccessible()
				&& $emajdb->getNumEmajVersion() >= $oldest_supported_emaj_version_num)) {
				echo "<p>";
				$href = $this->getHREF();
				$link = "<a href=\"emajenvir.php?{$href}\">\"{$lang['emajenvir']}\"</a>";
				echo sprintf($lang['emajnotavail'], $link);
				echo "</p>";
				return 0;
			}
			return 1;

		}

	}
?>
