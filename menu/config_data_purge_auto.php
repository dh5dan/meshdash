<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;

echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';
echo '<head><title>Nachrichtendaten ab x Tage löschen löschen</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';

echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';

if ((getParamData('darkMode') ?? 0) == 1)
{
    echo '<link rel="stylesheet" href="../css/dark_mode.css?' . microtime() . '">';
}
else
{
    echo '<link rel="stylesheet" href="../css/normal_mode.css?' . microtime() . '">';
}

echo '<link rel="stylesheet" href="../css/core.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/config_data_purge_auto.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../include/func_js_data_purge_auto.php';
require_once '../include/func_php_data_purge_auto.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;

if ($sendData === '1')
{
    $resSaveSettingsAutoPurge = saveSettingsAutoPurge();

    if ($resSaveSettingsAutoPurge)
    {
        echo '<span class="successHint">'.date('H:i:s').'-Settings wurden erfolgreich abgespeichert!</span>';
    }
    else
    {
        echo '<span class="failureHint">Es gab einen Fehler beim Abspeichern der Settings!</span>';
    }
}

$enableMsgPurge    = (int) getParamData('enableMsgPurge'); //
$enableSensorPurge = (int) getParamData('enableSensorPurge'); // 0= egal welche Gruppe

$daysMsgPurge             = (int) getParamData('daysMsgPurge'); //
$daysMsgPurge             = $daysMsgPurge == 0 ? 30 : $daysMsgPurge;
$daysSensorPurge          = (int) getParamData('daysSensorPurge');
$daysSensorPurge          = $daysSensorPurge == 0 ? 30 : $daysSensorPurge;

$enableMsgPurgeChecked    = $enableMsgPurge == 1 ? 'checked' : '';
$enableSensorPurgeChecked = $enableSensorPurge == 1 ? 'checked' : '';

echo '<span class="unsetDisplayFlex">';

echo "<h2>Auto-Purge Nachrichten/Sensordaten</h2>";

echo '<form id="frmPurgeDataAuto" method="post"  action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td>MSG-Löschen:</td>';
echo '<td>';
echo '<label class="switch">';
echo '<input type="checkbox" name="enableMsgPurge" ' . $enableMsgPurgeChecked . ' id="enableMsgPurge" value="1" />';
echo '<span class="slider"></span>';
echo '</label>';
echo '</td>';

echo '</tr>';

echo '<tr>';
echo '<td>MSG-Daten Löschen nach x Tagen (min. 2):</td>';
echo '<td><input type="text" name="daysMsgPurge" id="daysMsgPurge" style="width: auto" size="2" maxlength="3" value="' . $daysMsgPurge . '" /> Tage</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Sensor-Daten Löschen nach x Tagen:</td>';

echo '<td>';
echo '<label class="switch">';
echo '<input type="checkbox" name="enableSensorPurge" ' . $enableSensorPurgeChecked . ' id="enableSensorPurge" value="1" />';
echo '<span class="slider"></span>';
echo '</label>';
echo '</td>';

echo '</tr>';

echo '<tr>';
echo '<td>Sensor-Daten Löschen ab x Tagen (min. 2):</td>';
echo '<td><input type="text" name="daysSensorPurge" id="daysSensorPurge" style="width: auto" size="2" maxlength="3" value="' . $daysSensorPurge . '" /> Tage</td>';
echo '</tr>';


echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><span class="failureHint">Hinweis!<br>Die Anzahl der Tage bestimmt die Haltezeit.<br>Alle älteren Nachrichten werden unwiderruflich gelöscht!</span></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><input type="button" class="btnSaveConfigSettings" id="btnSaveDataPurgeAuto" value="Settings speichern"  /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';


echo '</span>';

echo '</body>';
echo '</html>';