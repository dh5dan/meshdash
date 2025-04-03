<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Einstellungen</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/sensor_data.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_php_sensor_data.php';
require_once '../include/func_js_sensor_data.php';
require_once '../include/func_php_lora_info.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$debugFlag = false;
$loraIp    = getParamData('loraIp');
$sendData  = $_REQUEST['sendData'] ?? 0;

echo '<h2>Lokale Sensordaten<span class="lineBreak">von Lora-IP: ' . $loraIp . '</span></h2>';

echo '<form id="frmSensorData" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table class="tableBtn">';

echo '<tr>';
echo '<td><input type="button" class="btnGetSensorData" id="btnGetSensorData" value="Lokale Sensordaten abfragen"  /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';

if ($sendData == 1)
{
    getSensorData($loraIp);
    echo '<span class="successHint">'.date('H:i:s').'-Sensordaten wurden erfolgreich abgespeichert!</span>';
}

showSensorData();

echo '</body>';
echo '</html>';

