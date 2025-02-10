<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Alerting</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/config_alerting.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_config_alerting.php';
require_once '../include/func_php_config_alerting.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;
$hardware = '';

#Check what oS is running
$osIssWindows = chkOsIssWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';

if ($sendData === '1')
{
    $resSaveAlertingSetting = saveAlertingSettings();

    if ($resSaveAlertingSetting)
    {
        echo '<span class="successHint">Settings wurden erfolgreich abgespeichert!</span>';
    }
    else
    {
        echo '<span class="failureHint">Es gab einen Fehler beim Abspeichern der Settings!</span>';
    }
}

$noDmAlertGlobal      = getParamData('noDmAlertGlobal');

$alertSoundFileSrc = getParamData('alertSoundFileSrc');
$alertEnabledSrc   = getParamData('alertEnabledSrc');
$alertSoundCallSrc = getParamData('alertSoundCallSrc');

$alertSoundFileDst = getParamData('alertSoundFileDst');
$alertEnabledDst   = getParamData('alertEnabledDst');
$alertSoundCallDst = getParamData('alertSoundCallDst');

$alertEnabledSrcChecked = $alertEnabledSrc == 1 ? 'checked' : '';
$alertEnabledDstChecked = $alertEnabledDst == 1 ? 'checked' : '';

echo "<h2>Benachrichtigungen Einstellen</h2>";
echo "<h5>(Dateien müssen im Sound-Verzeichnis vorhanden sein und ausführbar)</h5>";

echo '<form id="frmConfigAlerting" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table>';

echo '<tr>';
echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';
echo '<td>SoundFile für SRC-CALL :</td>';
echo '<td><input type="text" name="alertSoundFileSrc" id="alertSoundFileSrc" value="' . $alertSoundFileSrc . '" placeholder="Soundfile wav,mp3"  /></td>';
echo '<td><input type="checkbox" name="alertEnabledSrc" ' . $alertEnabledSrcChecked . ' id="alertEnabledSrc" value="1" />aktiv/inaktiv</td>';
echo '</tr>';

echo '<tr>';
echo '<td>SRC-Call :</td>';
echo '<td><input type="text" name="alertSoundCallSrc" id="alertSoundCallSrc" value="' . $alertSoundCallSrc . '" placeholder="SRC-Call" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';
echo '<td>SoundFile für DST-CALL :</td>';
echo '<td><input type="text" name="alertSoundFileDst" id="alertSoundFileDst" value="' . $alertSoundFileDst . '" placeholder="Soundfile wav,mp3"  /></td>';
echo '<td><input type="checkbox" name="alertEnabledDst" ' . $alertEnabledDstChecked . ' id="alertEnabledDst" value="1" />aktiv/inaktiv</td>';
echo '</tr>';

echo '<tr>';
echo '<td>DST-Call :</td>';
echo '<td><input type="text" name="alertSoundCallDst" id="alertSoundCallDst" value="' . $alertSoundCallDst . '" placeholder="DST-Call" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td><input type="button" id="btnSaveConfigAlerting" value="Settings speichern"  /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';

echo '</body>';
echo '</html>';