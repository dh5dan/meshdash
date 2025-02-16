<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Einstellungen</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="css/loader.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="css/mheard.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';
require_once 'include/func_php_mheard.php';
require_once 'include/func_js_mheard.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$debugFlag = false;
$loraIp    = getParamData('loraIp');
$callSign  = trim(getParamData('callSign'));
$sendData  = $_REQUEST['sendData'] ?? 0;

echo '<br><h2>Lokale Mheard-Liste von '.$callSign .' mit Lora-IP: ' . $loraIp . '</h2>';

echo '<form id="frmMheard" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table>';

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td><input type="button" id="btnGetMheard" value="Lokale Mheard-Liste abfragen"  /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';

if($sendData == 1)
{
    getMheard($loraIp);
}

showMheard($callSign);

echo '</body>';
echo '</html>';

