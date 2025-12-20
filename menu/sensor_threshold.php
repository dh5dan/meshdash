<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;
echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';
echo '<head><title data-i18n="submenu.sensor_threshold.lbl.title">Sensorschwellwert</title>';

#Prevents UTF8 Errors on misconfigured php.ini
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
echo '<link rel="stylesheet" href="../css/sensor_threshold.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../include/func_js_sensor_threshold.php';
require_once '../include/func_php_sensor_threshold.php';
require_once '../include/func_php_lora_info.php';
require_once '../include/func_js_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;
$hardware = '';

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';
$loraIp       = getParamData('loraIp');

#Check ob INA226 Sensoren vorhanden sind
$hasIna226Sensor  = false;
$arrayGetLoraInfo = getLoraInfo($loraIp);

#Init Temp
$sensorThTempEnabled       = '';
$sensorThTempMinValue      = '';
$sensorThTempMaxValue      = '';
$sensorThTempAlertMsg      = '';
$sensorThTempDmGrpId       = '';

$sensorThToutEnabled  = '';
$sensorThToutMinValue = '';
$sensorThToutMaxValue = '';
$sensorThToutAlertMsg = '';
$sensorThToutDmGrpId  = 999;

#Init Ina 226
$sensorThIna226vBusEnabled  = '';
$sensorThIna226vBusMinValue = '';
$sensorThIna226vBusMaxValue = '';
$sensorThIna226vBusAlertMsg = '';
$sensorThIna226vBusDmGrpId  = 999;

$sensorThIna226vShuntEnabled  = '';
$sensorThIna226vShuntMinValue = '';
$sensorThIna226vShuntMaxValue = '';
$sensorThIna226vShuntAlertMsg = '';
$sensorThIna226vShuntDmGrpId  = 999;

$sensorThIna226vCurrentEnabled  = '';
$sensorThIna226vCurrentMinValue = '';
$sensorThIna226vCurrentMaxValue = '';
$sensorThIna226vCurrentAlertMsg = '';
$sensorThIna226vCurrentDmGrpId  = 999;

$sensorThIna226vPowerEnabled  = '';
$sensorThIna226vPowerMinValue = '';
$sensorThIna226vPowerMaxValue = '';
$sensorThIna226vPowerAlertMsg = '';
$sensorThIna226vPowerDmGrpId  = 999;

if (isset($arrayGetLoraInfo['INA226']))
{
    $hasIna226Sensor = true;
}

if ($sendData === '1')
{
    $resSaveSensorThresholdSetting = saveSensorThresholdSettings($hasIna226Sensor);

    if ($resSaveSensorThresholdSetting)
    {
        echo '<span class="successHint">'.date('H:i:s') .
            '-<span data-i18n="submenu.sensor_threshold.msg.save-settings-success">Settings wurden erfolgreich abgespeichert!</span></span>';
    }
    else
    {
        echo '<span class="failureHint">' . date('H:i:s') .
            '-<span data-i18n="submenu.sensor_threshold.msg.save-settings-failed">Es gab einen Fehler beim Abspeichern der Settings!</span></span>';
    }
}

$sensorPollingIntervallMin = getParamData('sensorPollingIntervallMin') ?? 5;
$sensorPollingIntervallMin = $sensorPollingIntervallMin == '' ? 5 : $sensorPollingIntervallMin;

$sensorPollingEnabled = (int) (getParamData('sensorPollingEnabled') ?? 0);

$resGetThTempData = getThTempData();

if ($resGetThTempData !== false)
{
    $sensorThTempEnabled      = (int) ($resGetThTempData['sensorThTempEnabled'] ?? 0);
    $sensorThTempMinValue     = $resGetThTempData['sensorThTempMinValue'] ?? '';
    $sensorThTempMaxValue     = $resGetThTempData['sensorThTempMaxValue'] ?? '';
    $sensorThTempAlertMsg     = $resGetThTempData['sensorThTempAlertMsg'] ?? '';
    $sensorThTempDmGrpId      = $resGetThTempData['sensorThTempDmGrpId'] ?? '';
}
else
{
    echo '<span class="failureHint">Es gab einen Fehler bei der Abfrage der Daten!</span>';
}

$sensorThTempEnabledChecked  = $sensorThTempEnabled == 1 ? 'checked' : '';
$sensorPollingEnabledChecked = $sensorPollingEnabled == 1 ? 'checked' : '';
$sensorThTempDmGrpId         = $sensorThTempDmGrpId == '' ? '999' : $sensorThTempDmGrpId;

$lineBreak = '<span class="lineBreak">';
echo '<h2><span data-i18n="submenu.sensor_threshold.lbl.header-text" data-vars-replace="' .
    htmlspecialchars($lineBreak, ENT_QUOTES, 'UTF-8') . '">Sensor-Meldungen</span></span>';
echo '</h2>';

echo '<form id="frmSensorThreshold" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table>';

echo '<tr>';
echo '<td><span data-i18n="submenu.sensor_threshold.lbl.tx-interval">Intervall (1-1439 min.)</span>:</td>';
echo '<td>';
echo '<input type="text" name="sensorPollingIntervallMin" size="6" id="sensorPollingIntervallMin" value="' .
    $sensorPollingIntervallMin . '" placeholder="1-1439 min" />';
echo '<img src="../image/info_blau.png" class="infoImagePoint" id="infoImagePoint" alt="info" />';

echo '<label class="switch">';
    echo '<input type="checkbox" name="sensorPollingEnabled" ' .
        $sensorPollingEnabledChecked . ' id="sensorPollingEnabled" value="1" />';
    echo '<span class="slider"></span>';
echo '</label>';

echo'</td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.sensor_threshold.lbl.temp-status">Temp</span>:</td>';
echo '<td>';
echo '<label class="switch">';
echo '<input type="checkbox" name="sensorThTempEnabled" ' .
    $sensorThTempEnabledChecked . ' id="sensorThTempEnabled" value="1" />';
echo '<span class="slider"></span>';
echo '</label>';
echo'</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.sensor_threshold.lbl.temp-min-max">Min/Max</span>:</td>';
echo '<td>';
echo '<input type="number" step="0.1" class="number-field" name="sensorThTempMinValue" id="sensorThTempMinValue" value="' .
    $sensorThTempMinValue . '" placeholder="min." />';
echo '<input type="number" step="0.1" class="number-field" name="sensorThTempMaxValue" id="sensorThTempMaxValue" value="' .
    $sensorThTempMaxValue . '" placeholder="max." />';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.sensor_threshold.lbl.temp-alert-msg">Alarm-Meldung</span>:</td>';
echo '<td colspan="2"><input type="text" name="sensorThTempAlertMsg" id="sensorThTempAlertMsg" value="' .
    $sensorThTempAlertMsg . '" placeholder="Meldung Temp" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.sensor_threshold.lbl.temp-dm-group">DM-Gruppe/Call</span>:</td>';
echo '<td><input type="text" name="sensorThTempDmGrpId" id="sensorThTempDmGrpId" value="' .
    $sensorThTempDmGrpId . '" placeholder="Temp DM-Gruppe/Call" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

if ($resGetThTempData !== false)
{
    $sensorThToutEnabled  = (int) ($resGetThTempData['sensorThToutEnabled'] ?? 0);
    $sensorThToutMinValue = $resGetThTempData['sensorThToutMinValue'] ?? '';
    $sensorThToutMaxValue = $resGetThTempData['sensorThToutMaxValue'] ?? '';
    $sensorThToutAlertMsg = $resGetThTempData['sensorThToutAlertMsg'] ?? '';
    $sensorThToutDmGrpId  = $resGetThTempData['sensorThToutDmGrpId'] ?? 999;
}
else
{
    echo '<span class="failureHint">Es gab einen Fehler bei der Abfrage der Daten!</span>';
}

$sensorThToutEnabledChecked = $sensorThToutEnabled == 1 ? 'checked' : '';
$sensorThToutDmGrpId        = $sensorThToutDmGrpId == '' ? '999' : $sensorThToutDmGrpId;

echo '<tr>';
echo '<td><span data-i18n="submenu.sensor_threshold.lbl.tout-status">Tout</span>:</td>';
echo '<td>';
echo '<label class="switch">';
echo '<input type="checkbox" name="sensorThToutEnabled" ' .
    $sensorThToutEnabledChecked . ' id="sensorThToutEnabled" value="1" />';
echo '<span class="slider"></span>';
echo '</label>';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.sensor_threshold.lbl.tout-min-max">Min/Max</span>:</td>';
echo '<td>';
echo '<input type="number" step="0.1" class="number-field" name="sensorThToutMinValue" id="sensorThToutMinValue" value="' .
    $sensorThToutMinValue . '" placeholder="min." />';
echo '<input type="number" step="0.1" class="number-field" name="sensorThToutMaxValue" id="sensorThToutMaxValue" value="' .
    $sensorThToutMaxValue . '" placeholder="max." />';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.sensor_threshold.lbl.tout-alert-msg">Alarm-Meldung</span>:</td>';
echo '<td colspan="2"><input type="text" name="sensorThToutAlertMsg" id="sensorThToutAlertMsg" value="' .
    $sensorThToutAlertMsg . '" placeholder="Meldung Tout" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.sensor_threshold.lbl.temp-dm-group">DM-Gruppe/Call</span>:</td>';
echo '<td><input type="text" name="sensorThToutDmGrpId" id="sensorThToutDmGrpId" value="' .
    $sensorThToutDmGrpId . '" placeholder="Tout DM-Gruppe/Call" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';

echo '<td colspan="3">
        <button type="button" class="btnSaveSensorThreshold" id="btnSaveSensorThresholdTop">
            <span data-i18n="submenu.sensor_threshold.btn.save-settings">Settings speichern</span>
        </button>
      </td>';

echo '</tr>';

if ($hasIna226Sensor === true)
{
    $resGetThIna226Data = getThIna226Data();

    if ($resGetThIna226Data !== false)
    {
        $sensorThIna226vBusEnabled  = (int) ($resGetThIna226Data['sensorThIna226vBusEnabled'] ?? 0);
        $sensorThIna226vBusMinValue = $resGetThIna226Data['sensorThIna226vBusMinValue'] ?? '';
        $sensorThIna226vBusMaxValue = $resGetThIna226Data['sensorThIna226vBusMaxValue'] ?? '';
        $sensorThIna226vBusAlertMsg = $resGetThIna226Data['sensorThIna226vBusAlertMsg'] ?? '';
        $sensorThIna226vBusDmGrpId  = $resGetThIna226Data['sensorThIna226vBusDmGrpId'] ?? 999;
    }

    $sensorThIna226vBusEnabledChecked = $sensorThIna226vBusEnabled == 1 ? 'checked' : '';
    $sensorThIna226vBusDmGrpId        = $sensorThIna226vBusDmGrpId == '' ? '999' : $sensorThIna226vBusDmGrpId;

    echo '<tr>';
    echo '<td colspan="2"><hr></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>INA226-vBus:</td>';
    echo '<td>';
    echo '<label class="switch">';
    echo'<input type="checkbox" name="sensorThIna226vBusEnabled" ' .
        $sensorThIna226vBusEnabledChecked . ' id="sensorThIna226vBusEnabled" value="1" />';
    echo '<span class="slider"></span>';
    echo '</label>';
    echo'</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Min/Max:</td>';
    echo '<td>';
    echo '<input type="number" step="0.1" class="number-field" name="sensorThIna226vBusMinValue" id="sensorThIna226vBusMinValue" value="' .
        $sensorThIna226vBusMinValue . '" placeholder="min." />';
    echo '<input type="number" step="0.1" class="number-field" name="sensorThIna226vBusMaxValue" id="sensorThIna226vBusMaxValue" value="' .
        $sensorThIna226vBusMaxValue . '" placeholder="max." />';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.sensor_threshold.lbl.ina226vbus-alert-msg">Alarm-Meldung</span>:</td>';
    echo '<td colspan="2"><input type="text" name="sensorThIna226vBusAlertMsg" id="sensorThIna226vBusAlertMsg" value="' .
        $sensorThIna226vBusAlertMsg . '" placeholder="Meldung Ina226vBus" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>DM-Gruppe/Call:</td>';
    echo '<td><input type="text" name="sensorThIna226vBusDmGrpId" id="sensorThIna226vBusDmGrpId" value="' .
        $sensorThIna226vBusDmGrpId . '" placeholder="Ina226vBus DM-Gruppe/Call" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2"><hr></td>';
    echo '</tr>';

    if ($resGetThIna226Data !== false)
    {
        $sensorThIna226vShuntEnabled  = $resGetThIna226Data['sensorThIna226vShuntEnabled'] ?? '';
        $sensorThIna226vShuntMinValue = $resGetThIna226Data['sensorThIna226vShuntMinValue'] ?? '';
        $sensorThIna226vShuntMaxValue = $resGetThIna226Data['sensorThIna226vShuntMaxValue'] ?? '';
        $sensorThIna226vShuntAlertMsg = $resGetThIna226Data['sensorThIna226vShuntAlertMsg'] ?? '';
        $sensorThIna226vShuntDmGrpId  = $resGetThIna226Data['sensorThIna226vShuntDmGrpId'] ?? 999;
    }

    $sensorThIna226vShuntEnabledChecked = $sensorThIna226vShuntEnabled == 1 ? 'checked' : '';
    $sensorThIna226vShuntDmGrpId        = $sensorThIna226vShuntDmGrpId == '' ? '999' : $sensorThIna226vShuntDmGrpId;

    echo '<tr>';
    echo '<td>INA226-vShunt:</td>';
    echo '<td>';
    echo '<label class="switch">';
    echo '<input type="checkbox" name="sensorThIna226vShuntEnabled" ' .
        $sensorThIna226vShuntEnabledChecked . ' id="sensorThIna226vShuntEnabled" value="1" />';
    echo '<span class="slider"></span>';
    echo '</label>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Min/Max:</td>';
    echo '<td>';
    echo '<input type="number" step="0.1" class="number-field" name="sensorThIna226vShuntMinValue" id="sensorThIna226vShuntMinValue" value="' .
        $sensorThIna226vShuntMinValue . '" placeholder="min." />';
    echo '<input type="number" step="0.1" class="number-field" name="sensorThIna226vShuntMaxValue" id="sensorThIna226vShuntMaxValue" value="' .
        $sensorThIna226vShuntMaxValue . '" placeholder="max." />';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.sensor_threshold.lbl.ina226vshunt-alert-msg">Alarm-Meldung</span>:</td>';
    echo '<td colspan="2"><input type="text" name="sensorThIna226vShuntAlertMsg" id="sensorThIna226vShuntAlertMsg" value="' .
        $sensorThIna226vShuntAlertMsg . '" placeholder="Meldung Ina226vShunt" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>DM-Gruppe/Call:</td>';
    echo '<td><input type="text" name="sensorThIna226vShuntDmGrpId" id="sensorThIna226vShuntDmGrpId" value="' .
        $sensorThIna226vShuntDmGrpId . '" placeholder="Ina226vShunt DM-Gruppe/Call" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2"><hr></td>';
    echo '</tr>';

    if ($resGetThIna226Data !== false)
    {
        $sensorThIna226vCurrentEnabled  = $resGetThIna226Data['sensorThIna226vCurrentEnabled'] ?? '';
        $sensorThIna226vCurrentMinValue = $resGetThIna226Data['sensorThIna226vCurrentMinValue'] ?? '';
        $sensorThIna226vCurrentMaxValue = $resGetThIna226Data['sensorThIna226vCurrentMaxValue'] ?? '';
        $sensorThIna226vCurrentAlertMsg = $resGetThIna226Data['sensorThIna226vCurrentAlertMsg'] ?? '';
        $sensorThIna226vCurrentDmGrpId  = $resGetThIna226Data['sensorThIna226vCurrentDmGrpId'] ?? 999;
    }

    $sensorThIna226vCurrentEnabledChecked = $sensorThIna226vCurrentEnabled == 1 ? 'checked' : '';
    $sensorThIna226vCurrentDmGrpId        = $sensorThIna226vCurrentDmGrpId == '' ? '999' : $sensorThIna226vCurrentDmGrpId;

    echo '<tr>';
    echo '<td>INA226-vCurrent:</td>';
    echo '<td>';
    echo '<label class="switch">';
    echo '<input type="checkbox" name="sensorThIna226vCurrentEnabled" ' . $sensorThIna226vCurrentEnabledChecked . ' id="sensorThIna226vCurrentEnabled" value="1" />';
    echo '<span class="slider"></span>';
    echo '</label>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Min/Max:</td>';
    echo '<td>';
    echo '<input type="number" step="0.1" class="number-field" name="sensorThIna226vCurrentMinValue" id="sensorThIna226vCurrentMinValue" value="' .
        $sensorThIna226vCurrentMinValue . '" placeholder="min." />';
    echo '<input type="number" step="0.1" class="number-field" name="sensorThIna226vCurrentMaxValue" id="sensorThIna226vCurrentMaxValue" value="' .
        $sensorThIna226vCurrentMaxValue . '" placeholder="max." />';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.sensor_threshold.lbl.ina226vcurrent-alert-msg">Alarm-Meldung</span>:</td>';
    echo '<td colspan="2">';
    echo '<input type="text" name="sensorThIna226vCurrentAlertMsg" id="sensorThIna226vCurrentAlertMsg" value="' .
        $sensorThIna226vCurrentAlertMsg . '" placeholder="Meldung Ina226vCurrent" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>DM-Gruppe/Call:</td>';
    echo '<td><input type="text" name="sensorThIna226vCurrentDmGrpId" id="sensorThIna226vCurrentDmGrpId" value="' .
        $sensorThIna226vCurrentDmGrpId . '" placeholder="Meldung DM-Gruppe/Call" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2"><hr></td>';
    echo '</tr>';

    if ($resGetThIna226Data !== false)
    {
        $sensorThIna226vPowerEnabled  = $resGetThIna226Data['sensorThIna226vPowerEnabled'] ?? '';
        $sensorThIna226vPowerMinValue = $resGetThIna226Data['sensorThIna226vPowerMinValue'] ?? '';
        $sensorThIna226vPowerMaxValue = $resGetThIna226Data['sensorThIna226vPowerMaxValue'] ?? '';
        $sensorThIna226vPowerAlertMsg = $resGetThIna226Data['sensorThIna226vPowerAlertMsg'] ?? '';
        $sensorThIna226vPowerDmGrpId  = $resGetThIna226Data['sensorThIna226vPowerDmGrpId'] ?? 999;
    }

    $sensorThIna226vPowerEnabledChecked = $sensorThIna226vPowerEnabled == 1 ? 'checked' : '';
    $sensorThIna226vPowerDmGrpId        = $sensorThIna226vPowerDmGrpId == '' ? '999' : $sensorThIna226vPowerDmGrpId;

    echo '<tr>';
    echo '<td>INA226-vPower:</td>';
    echo '<td>';
    echo '<label class="switch">';
    echo '<input type="checkbox" name="sensorThIna226vPowerEnabled" ' . $sensorThIna226vPowerEnabledChecked . ' id="sensorThIna226vPowerEnabled" value="1" />';
    echo '<span class="slider"></span>';
    echo '</label>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Min/Max:</td>';
    echo '<td>';
    echo '<input type="number" step="0.1" class="number-field" name="sensorThIna226vPowerMinValue" id="sensorThIna226vPowerMinValue" value="' .
        $sensorThIna226vPowerMinValue . '" placeholder="min." />';
    echo '<input type="number" step="0.1" class="number-field" name="sensorThIna226vPowerMaxValue" id="sensorThIna226vPowerMaxValue" value="' .
        $sensorThIna226vPowerMaxValue . '" placeholder="max." />';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.sensor_threshold.lbl.ina226vpower-alert-msg">Alarm-Meldung</span>:</td>';
    echo '<td><input type="text" name="sensorThIna226vPowerAlertMsg" id="sensorThIna226vPowerAlertMsg" value="' .
        $sensorThIna226vPowerAlertMsg . '" placeholder="Meldung Ina226vPower" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>DM-Gruppe/Call:</td>';
    echo '<td><input type="text" name="sensorThIna226vPowerDmGrpId" id="sensorThIna226vPowerDmGrpId" value="' .
        $sensorThIna226vPowerDmGrpId . '" placeholder="Ina226vPower DM-Gruppe/Call" /></td>';
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

echo '<script>
            $.getJSON("../translation.php?lang=' . $userLang . '", function(dict) {
            applyTranslation(dict); // siehe JS oben
            });
        </script>';

echo '</body>';
echo '</html>';