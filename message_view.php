<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
ini_set( 'default_charset', 'UTF-8' );

echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Messages</title>';
echo '<script type="text/javascript" src="jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="include/message_renderer.js"></script>';
echo '<link rel="stylesheet" href="css/message.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

//require_once 'dbinc/param.php';
//require_once 'include/func_php_core.php';
require_once 'include/func_js_message.php';
//require_once 'include/func_php_message.php';
//require_once 'include/func_php_index.php';
//require_once 'include/func_php_mheard.php';

echo '<input type="hidden" id="gruppe_id" value="-1">';
echo '<div id="message-frame-inner"></div>';



echo '</body>';
echo '</html>';