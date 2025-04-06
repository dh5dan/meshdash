<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Sensorschwellwert</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/sensor_threshold.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_sensor_threshold.php';
require_once '../include/func_php_sensor_threshold.php';
require_once '../include/func_php_lora_info.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;
$hardware = '';

#Check what oS is running
$osIssWindows = chkOsIssWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';
$loraIp       = getParamData('loraIp');

#Check ob INA226 Sensoren vorhanden sind
$hasIna226Sensor = false;
$arrayGetLoraInfo = getLoraInfo($loraIp);
if (isset($arrayGetLoraInfo['INA226']))
{
    $hasIna226Sensor = true;
}

if ($sendData === '1')
{
    $resSaveSensorThresholdSetting = saveSensorThresholdSettings($hasIna226Sensor);

    if ($resSaveSensorThresholdSetting)
    {
        echo '<span class="successHint">'.date('H:i:s').'-Settings erfolgreich abgespeichert!</span>';
    }
    else
    {
        echo '<span class="failureHint">Es gab einen Fehler beim Abspeichern der Settings!</span>';
    }
}

$resGetThTempData = getThTempData();

$sensorThTempEnabled      = $resGetThTempData['sensorThTempEnabled'] ?? '';
$sensorThTempMinValue     = $resGetThTempData['sensorThTempMinValue'] ?? '';
$sensorThTempMaxValue     = $resGetThTempData['sensorThTempMaxValue'] ?? '';
$sensorThTempAlertMsg     = $resGetThTempData['sensorThTempAlertMsg'] ?? '';
$sensorThTempDmGrpId      = $resGetThTempData['sensorThTempDmGrpId'] ?? '';
$sensorThTempIntervallMin = $resGetThTempData['sensorThTempIntervallMin'] ?? 1;

$sensorThTempEnabledChecked = $sensorThTempEnabled == 1 ? 'checked' : '';
$sensorThTempDmGrpId        = $sensorThTempDmGrpId == '' ? '999' : $sensorThTempDmGrpId;

$sensorThTempIntervallMin      = $sensorThTempIntervallMin = '' ? 60 : $sensorThTempIntervallMin;

echo '<h2>Sensorschwellwert-Definition <span class="lineBreak">zur Auslösung von Meldungen</span>';
echo '</h2>';

echo '<form id="frmSensorThreshold" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table>';

echo '<tr>';
echo '<td>Abfrage-Intervall:</td>';
echo '<td>';
echo '<input type="text" name="sensorThTempIntervallMin" id="sensorThTempIntervallMin" value="' . $sensorThTempIntervallMin . '" placeholder="Intervall Min." />&nbsp;Min.&nbsp;(>=1 - 1439)';
echo'</td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Temp Enable/Disable:</td>';
echo '<td>';
echo '<input type="checkbox" name="sensorThTempEnabled" ' . $sensorThTempEnabledChecked . ' id="sensorThTempEnabled" value="1" />';
echo'</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Min/Max:</td>';
echo '<td>';
echo '<input type="text" name="sensorThTempMinValue" id="sensorThTempMinValue" value="' . $sensorThTempMinValue . '" placeholder="min. Wert" />';
echo '<input type="text" name="sensorThTempMaxValue" id="sensorThTempMaxValue" value="' . $sensorThTempMaxValue . '" placeholder="max. Wert" />';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Alertmeldung:</td>';
echo '<td colspan="2"><input type="text" name="sensorThTempAlertMsg" id="sensorThTempAlertMsg" value="' . $sensorThTempAlertMsg . '" placeholder="Rückmeldung Temp" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>DM-Gruppe/Call:</td>';
echo '<td><input type="text" name="sensorThTempDmGrpId" id="sensorThTempDmGrpId" value="' . $sensorThTempDmGrpId . '" placeholder="Temp DM-Gruppe/Call" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

$sensorThToutEnabled  = $resGetThTempData['sensorThToutEnabled'] ?? '';
$sensorThToutMinValue = $resGetThTempData['sensorThToutMinValue'] ?? '';
$sensorThToutMaxValue = $resGetThTempData['sensorThToutMaxValue'] ?? '';
$sensorThToutAlertMsg = $resGetThTempData['sensorThToutAlertMsg'] ?? '';
$sensorThToutDmGrpId  = $resGetThTempData['sensorThToutDmGrpId'] ?? 999;

$sensorThToutEnabledChecked = $sensorThToutEnabled == 1 ? 'checked' : '';
$sensorThToutDmGrpId        = $sensorThToutDmGrpId == '' ? '999' : $sensorThToutDmGrpId;

echo '<tr>';
echo '<td>Tout Enable/Disable:</td>';
echo '<td><input type="checkbox" name="sensorThToutEnabled" ' . $sensorThToutEnabledChecked . ' id="sensorThToutEnabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Min/Max:</td>';
echo '<td>';
echo '<input type="text" name="sensorThToutMinValue" id="sensorThToutMinValue" value="' . $sensorThToutMinValue . '" placeholder="min. Wert" />';
echo '<input type="text" name="sensorThToutMaxValue" id="sensorThToutMaxValue" value="' . $sensorThToutMaxValue . '" placeholder="max. Wert" />';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Alertmeldung:</td>';
echo '<td colspan="2"><input type="text" name="sensorThToutAlertMsg" id="sensorThToutAlertMsg" value="' . $sensorThToutAlertMsg . '" placeholder="Rückmeldung Tout" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>DM-Gruppe/Call:</td>';
echo '<td><input type="text" name="sensorThToutDmGrpId" id="sensorThToutDmGrpId" value="' . $sensorThToutDmGrpId . '" placeholder="Tout DM-Gruppe/Call" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="3"><input type="button" class="btnSaveSensorThreshold" id="btnSaveSensorThresholdTop" value="Settings speichern"  /></td>';
echo '</tr>';

if ($hasIna226Sensor === true)
{
    $resGetThIna226Data         = getThIna226Data();
    $sensorThIna226vBusEnabled  = $resGetThIna226Data['sensorThIna226vBusEnabled'] ?? '';
    $sensorThIna226vBusMinValue = $resGetThIna226Data['sensorThIna226vBusMinValue'] ?? '';
    $sensorThIna226vBusMaxValue = $resGetThIna226Data['sensorThIna226vBusMaxValue'] ?? '';
    $sensorThIna226vBusAlertMsg = $resGetThIna226Data['sensorThIna226vBusAlertMsg'] ?? '';
    $sensorThIna226vBusDmGrpId  = $resGetThIna226Data['sensorThIna226vBusDmGrpId'] ?? 999;

    $sensorThIna226vBusEnabledChecked = $sensorThIna226vBusEnabled == 1 ? 'checked' : '';
    $sensorThIna226vBusDmGrpId        = $sensorThIna226vBusDmGrpId == '' ? '999' : $sensorThIna226vBusDmGrpId;
    $sensorThTempIntervallMin         = $sensorThTempIntervallMin = '' ? 60 : $sensorThTempIntervallMin;

    echo '<tr>';
    echo '<td colspan="2"><hr></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>INA226-vBus Enable/Disable:</td>';
    echo '<td><input type="checkbox" name="sensorThIna226vBusEnabled" ' . $sensorThIna226vBusEnabledChecked . ' id="sensorThIna226vBusEnabled" value="1" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Min/Max:</td>';
    echo '<td>';
    echo '<input type="text" name="sensorThIna226vBusMinValue" id="sensorThIna226vBusMinValue" value="' . $sensorThIna226vBusMinValue . '" placeholder="min. Wert" />';
    echo '<input type="text" name="sensorThIna226vBusMaxValue" id="sensorThIna226vBusMaxValue" value="' . $sensorThIna226vBusMaxValue . '" placeholder="max. Wert" />';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Alertmeldung:</td>';
    echo '<td colspan="2"><input type="text" name="sensorThIna226vBusAlertMsg" id="sensorThIna226vBusAlertMsg" value="' . $sensorThIna226vBusAlertMsg . '" placeholder="Rückmeldung Ina226vBus" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>DM-Gruppe/Call:</td>';
    echo '<td><input type="text" name="sensorThIna226vBusDmGrpId" id="sensorThIna226vBusDmGrpId" value="' . $sensorThIna226vBusDmGrpId . '" placeholder="Ina226vBus DM-Gruppe/Call" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2"><hr></td>';
    echo '</tr>';

    $sensorThIna226vShuntEnabled  = $resGetThIna226Data['sensorThIna226vShuntEnabled'] ?? '';
    $sensorThIna226vShuntMinValue = $resGetThIna226Data['sensorThIna226vShuntMinValue'] ?? '';
    $sensorThIna226vShuntMaxValue = $resGetThIna226Data['sensorThIna226vShuntMaxValue'] ?? '';
    $sensorThIna226vShuntAlertMsg = $resGetThIna226Data['sensorThIna226vShuntAlertMsg'] ?? '';
    $sensorThIna226vShuntDmGrpId  = $resGetThIna226Data['sensorThIna226vShuntDmGrpId'] ?? 999;

    $sensorThIna226vShuntEnabledChecked = $sensorThIna226vShuntEnabled == 1 ? 'checked' : '';
    $sensorThIna226vShuntDmGrpId        = $sensorThIna226vShuntDmGrpId == '' ? '999' : $sensorThIna226vShuntDmGrpId;

    echo '<tr>';
    echo '<td>INA226-vShunt Enable/Disable:</td>';
    echo '<td><input type="checkbox" name="sensorThIna226vShuntEnabled" ' . $sensorThIna226vShuntEnabledChecked . ' id="sensorThIna226vShuntEnabled" value="1" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Min/Max:</td>';
    echo '<td>';
    echo '<input type="text" name="sensorThIna226vShuntMinValue" id="sensorThIna226vShuntMinValue" value="' . $sensorThIna226vShuntMinValue . '" placeholder="min. Wert" />';
    echo '<input type="text" name="sensorThIna226vShuntMaxValue" id="sensorThIna226vShuntMaxValue" value="' . $sensorThIna226vShuntMaxValue . '" placeholder="max. Wert" />';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Alertmeldung:</td>';
    echo '<td colspan="2"><input type="text" name="sensorThIna226vShuntAlertMsg" id="sensorThIna226vShuntAlertMsg" value="' . $sensorThIna226vShuntAlertMsg . '" placeholder="Rückmeldung Ina226vShunt" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>DM-Gruppe/Call:</td>';
    echo '<td><input type="text" name="sensorThIna226vShuntDmGrpId" id="sensorThIna226vShuntDmGrpId" value="' . $sensorThIna226vShuntDmGrpId . '" placeholder="Ina226vShunt DM-Gruppe/Call" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2"><hr></td>';
    echo '</tr>';

    $sensorThIna226vCurrentEnabled  = $resGetThIna226Data['sensorThIna226vCurrentEnabled'] ?? '';
    $sensorThIna226vCurrentMinValue = $resGetThIna226Data['sensorThIna226vCurrentMinValue'] ?? '';
    $sensorThIna226vCurrentMaxValue = $resGetThIna226Data['sensorThIna226vCurrentMaxValue'] ?? '';
    $sensorThIna226vCurrentAlertMsg = $resGetThIna226Data['sensorThIna226vCurrentAlertMsg'] ?? '';
    $sensorThIna226vCurrentDmGrpId  = $resGetThIna226Data['sensorThIna226vCurrentDmGrpId'] ?? 999;

    $sensorThIna226vCurrentEnabledChecked = $sensorThIna226vCurrentEnabled == 1 ? 'checked' : '';
    $sensorThIna226vCurrentDmGrpId        = $sensorThIna226vCurrentDmGrpId == '' ? '999' : $sensorThIna226vCurrentDmGrpId;

    echo '<tr>';
    echo '<td>INA226-vCurrent Enable/Disable:</td>';
    echo '<td><input type="checkbox" name="sensorThIna226vCurrentEnabled" ' . $sensorThIna226vCurrentEnabledChecked . ' id="sensorThIna226vCurrentEnabled" value="1" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Min/Max:</td>';
    echo '<td>';
    echo '<input type="text" name="sensorThIna226vCurrentMinValue" id="sensorThIna226vCurrentMinValue" value="' . $sensorThIna226vCurrentMinValue . '" placeholder="min. Wert" />';
    echo '<input type="text" name="sensorThIna226vCurrentMaxValue" id="sensorThIna226vCurrentMaxValue" value="' . $sensorThIna226vCurrentMaxValue . '" placeholder="max. Wert" />';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Alertmeldung:</td>';
    echo '<td colspan="2"><input type="text" name="sensorThIna226vCurrentAlertMsg" id="sensorThIna226vCurrentAlertMsg" value="' . $sensorThIna226vCurrentAlertMsg . '" placeholder="Rückmeldung Ina226vCurrent" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>DM-Gruppe/Call:</td>';
    echo '<td><input type="text" name="sensorThIna226vCurrentDmGrpId" id="sensorThIna226vCurrentDmGrpId" value="' . $sensorThIna226vCurrentDmGrpId . '" placeholder="Ina226vCurrent DM-Gruppe/Call" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2"><hr></td>';
    echo '</tr>';

    $sensorThIna226vPowerEnabled  = $resGetThIna226Data['sensorThIna226vPowerEnabled'] ?? '';
    $sensorThIna226vPowerMinValue = $resGetThIna226Data['sensorThIna226vPowerMinValue'] ?? '';
    $sensorThIna226vPowerMaxValue = $resGetThIna226Data['sensorThIna226vPowerMaxValue'] ?? '';
    $sensorThIna226vPowerAlertMsg = $resGetThIna226Data['sensorThIna226vPowerAlertMsg'] ?? '';
    $sensorThIna226vPowerDmGrpId  = $resGetThIna226Data['sensorThIna226vPowerDmGrpId'] ?? 999;

    $sensorThIna226vPowerEnabledChecked = $sensorThIna226vPowerEnabled == 1 ? 'checked' : '';
    $sensorThIna226vPowerDmGrpId        = $sensorThIna226vPowerDmGrpId == '' ? '999' : $sensorThIna226vPowerDmGrpId;

    echo '<tr>';
    echo '<td>INA226-vPower Enable/Disable:</td>';
    echo '<td><input type="checkbox" name="sensorThIna226vPowerEnabled" ' . $sensorThIna226vPowerEnabledChecked . ' id="sensorThIna226vPowerEnabled" value="1" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Min/Max:</td>';
    echo '<td>';
    echo '<input type="text" name="sensorThIna226vPowerMinValue" id="sensorThIna226vPowerMinValue" value="' . $sensorThIna226vPowerMinValue . '" placeholder="min. Wert" />';
    echo '<input type="text" name="sensorThIna226vPowerMaxValue" id="sensorThIna226vPowerMaxValue" value="' . $sensorThIna226vPowerMaxValue . '" placeholder="max. Wert" />';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Alertmeldung:</td>';
    echo '<td><input type="text" name="sensorThIna226vPowerAlertMsg" id="sensorThIna226vPowerAlertMsg" value="' . $sensorThIna226vPowerAlertMsg . '" placeholder="Rückmeldung Ina226vPower" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>DM-Gruppe/Call:</td>';
    echo '<td><input type="text" name="sensorThIna226vPowerDmGrpId" id="sensorThIna226vPowerDmGrpId" value="' . $sensorThIna226vPowerDmGrpId . '" placeholder="Ina226vPower DM-Gruppe/Call" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2"><hr></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="3"><input type="button" class="btnSaveSensorThreshold" id="btnSaveSensorThresholdBottom" value="Settings speichern"  /></td>';
    echo '</tr>';
}

echo '</table>';
echo '</form>';

echo '</body>';
echo '</html>';