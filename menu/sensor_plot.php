<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;
echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';
echo '<head><title data-i18n="submenu.sensor_plot.lbl.title">Plot lokaler Sensordaten</title>';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';

    #Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';

if ((getParamData('darkMode') ?? 0) == 1)
{
    echo '<link rel="stylesheet" href="../css/dark_mode.css?' . microtime() . '">';
}
else
{
    echo '<link rel="stylesheet" href="../css/normal_mode.css?' . microtime() . '">';
}

echo '<link rel="stylesheet" href="../css/sensor_plot.css?' . microtime() . '">';

# Benötigt für Chart
echo '<script type="text/javascript" src="../jquery/chart/chart.js"></script>';
echo '<script src="../jquery/hammer/hammer.min.js"></script>';
echo '<script src="../jquery/npm-moment/moment.js"></script>';
echo '<script src="../jquery/chart/chartjs-adapter-moment.js"></script>';
echo '<script src="../jquery/chart/chartjs-plugin-zoom.min.js"></script>';



echo '</head>';
echo '<body>';

require_once '../include/func_php_sensor_plot.php';
require_once '../include/func_php_sensor_data.php';
require_once '../include/func_js_sensor_plot.php';
require_once '../include/func_php_lora_info.php';
require_once '../include/func_js_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$lineBreak = '<span class="lineBreak">';

#Check new GUI
if (getParamData('isNewMeshGui') == 1)
{
    $resGetSensorData = getSensorData2(getParamData('loraIp'),1);
}
else
{
    $resGetSensorData = getSensorData(getParamData('loraIp'),1);
}

echo '<h2><span data-i18n="submenu.sensor_plot.lbl.header-text">Sensordaten-Diagramm</span></h2>';

echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table class="tableBtn">';

echo '<tr>';
    echo '<td>';
        echo'<span data-i18n="submenu.sensor_plot.btn.get-sensor-data">Von/Bis:</span>';
    echo'</td>';

    echo '<td>';
        echo '<input type="date" name="sensorPlotFrom" id="sensorPlotFrom" />&nbsp;';
        echo '<input type="date" name="sensorPlotTo" id="sensorPlotTo" value="' . date('Y-m-d') . '"/>';
    echo'</td>';

echo '</tr>';

echo '<tr>';
    echo '<td>';
            echo 'Sensor:';
    echo '</td>';
    echo '<td>';
            echo '<select id="sensorType">';
            selectSensorType($resGetSensorData);
            echo '</select>';
            echo "&nbsp;&nbsp;";
            echo '<button type="button" class="btnPlotSensorChart" id="btnPlotSensorChart">';
                echo '<span data-i18n="submenu.sensor_plot.btn.plot-sensor-chart">Anzeigen</span>';
            echo '</button>';
            echo '<img src="../image/info_blau.png" class="infoImagePoint" id="infoImagePoint" alt="info" />';
    echo '</td>';
echo '</tr>';

echo '</table>';


#echo '<canvas id="sensorChart" width="0" height="100px"></canvas>';
echo '<canvas id="sensorChart" width="0" height="50px"></canvas>';


echo '<div id="pageLoading" class="pageLoadingSub"></div>';

echo '<script>
            $.getJSON("../translation.php?lang=' . $userLang . '", function(dict) {
            applyTranslation(dict); // siehe JS oben
            });
        </script>';

echo '</body>';
echo '</html>';

