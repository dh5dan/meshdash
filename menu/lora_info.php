<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Lora Info</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/lora_info.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_lora_info.php';
require_once '../include/func_php_lora_info.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;
$loraIp   = getParamData('loraIp');
$btnText  = $sendData == 1 ? 'Infoseite neu laden' : 'Infoseite laden';

echo "<h2>Lora Infoseite</h2>";

echo '<form id="frmLoraInfo" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';

echo '<table class="table">';

echo '<tr>';
echo '<th colspan="3"><input type="button" class="btnLoadLoraInfo" id="btnLoadLoraInfo" value="' . $btnText . '" /></th>';
echo '</tr>';

if ($sendData == 1)
{
       showLoraInfo(getLoraInfo($loraIp));
}

echo '<table>';
echo '</form>';
echo '<div id="pageLoading" class="pageLoadingSub"></div>';
echo '</body>';
echo '</html>';