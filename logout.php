<?php

/*
 * Logs a user out of the app
 */

if (!ini_get('session.auto_start')) {
	session_name('EMAJ_ID'); 
	session_start();
}
unset($_SESSION);
session_destroy();

header('Location: index.php');

?>
