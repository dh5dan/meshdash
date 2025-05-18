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
echo '<link rel="stylesheet" href="../css/config_ping_lora.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_js_config_ping_lora.php';
require_once '../include/func_php_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$loraIp   = getParamData('loraIp');
$sendData = $_REQUEST['sendData'] ?? 0;

if ($sendData == 1)
{
    #Check what oS is running
    if (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN')
    {
        exec('ping -n 4 -4 ' . $loraIp, $output, $return_var);

        if ($return_var == 0)
        {
            foreach ($output AS $returnValue)
            {
                echo "<br>" .  sapi_windows_cp_conv(sapi_windows_cp_get('oem'), 65001, $returnValue);
            }
        }
        else
        {
            echo "<br>Ein fehler ist bei der ausführung von Ping aufgetreten";
        }
    }
    else
    {
        exec('ping -c 4 ' . $loraIp, $output, $return_var);

        if ($return_var == 0)
        {
            foreach ($output AS $returnValue)
            {
                echo "<br>" . $returnValue;
            }
        }
        else
        {
            echo "<br>Ein fehler ist bei der ausführung von Ping aufgetreten";
        }
    }
}

echo "<h2>Ping Lora IP: ". $loraIp .'</h2>';

echo '<form id="frmPingLora" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" name="loraIp" id="loraIP" value="' . $loraIp . '" />';
echo '<input type="button" class="submitParamLoraIp" id="btnPingLoraIp" value="Ping jetzt aus&uuml;hren"  />';
echo '</form>';

echo '<div id="pageLoading" class="pageLoadingSub"></div>';

echo '</body>';
echo '</html>';