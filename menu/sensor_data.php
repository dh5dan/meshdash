<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;
echo '<head><title data-i18n="submenu.sensor_data.lbl.title">Lokale Sensordaten</title>';

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

require_once '../include/func_php_sensor_data.php';
require_once '../include/func_js_sensor_data.php';
require_once '../include/func_php_lora_info.php';
require_once '../include/func_js_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$debugFlag = false;
$loraIp    = getParamData('loraIp');
$sendData  = $_REQUEST['sendData'] ?? 0;
$lineBreak = '<span class="lineBreak">';

echo '<h2><span data-i18n="submenu.sensor_data.lbl.header-text">Lokale Sensordaten von IP</span>:' . $loraIp .'</h2>';

#echo '<h2>Lokale Sensordaten'.$lineBreak.'von Lora-IP: ' . $loraIp . '</span></h2>';

echo '<form id="frmSensorData" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table class="tableBtn">';

echo '<tr>';

echo '<td>
        <button type="button" class="btnGetSensorData" id="btnGetSensorData">
            <span data-i18n="submenu.sensor_data.btn.get-sensor-data">Lokale Sensordaten abfragen</span>
        </button>
      </td>';

echo '</tr>';

echo '</table>';
echo '</form>';

if ($sendData == 1)
{
    #Check new GUI
    if (getParamData('isNewMeshGui') == 1)
    {
        $resGetSensorData = getSensorData2($loraIp);
    }
    else
    {
        $resGetSensorData = getSensorData($loraIp);
    }

    if ($resGetSensorData !== false)
    {
        echo '<span class="successHint">' . date('H:i:s') . '-Sensordaten wurden erfolgreich abgespeichert!</span>';
    }
}

showSensorData();

echo '<div id="pageLoading" class="pageLoadingSub"></div>';

echo '<script>
            $.getJSON("../translation.php?lang=' . $userLang . '", function(dict) {
            applyTranslation(dict); // siehe JS oben
            });
        </script>';

echo '</body>';
echo '</html>';

