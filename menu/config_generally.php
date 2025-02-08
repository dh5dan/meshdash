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
echo '<link rel="stylesheet" href="../css/config_generally.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_config_generally.php';
require_once '../include/func_php_config_generally.php';

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
    $resSaveGenerallySetting = saveGenerallySettings();

    if ($resSaveGenerallySetting)
    {
        echo '<span class="successHint">Settings wurden erfolgreich abgespeichert!</span>';

        echo "<script>reloadBottomFrame();</script>";
    }
    else
    {
        echo '<span class="failureHint">Es gab einen Fehler beim Abspeichern der Settings!</span>';
    }
}

if ($osIssWindows === false)
{
    $cpuInfo      = file_get_contents('/proc/cpuinfo');
    $architecture = php_uname('m');

    if ((strpos($cpuInfo, 'Raspberry Pi') !== false || strpos($cpuInfo, 'BCM') !== false) &&
        ($architecture === 'armv7l' || $architecture === 'aarch64'))
    {
        $hardware = "Raspberry Pi.";
    }
    else
    {
        $hardware = "Kein Raspberry Pi.";
    }
}

$noPosData         = getParamData('noPosData');
$noDmAlertGlobal   = getParamData('noDmAlertGlobal');
$noTimeSyncMsg     = getParamData('noTimeSyncMsg');
$loraIp            = getParamData('loraIp');
$callSign          = getParamData('callSign');
$maxScrollBackRows = getParamData('maxScrollBackRows');

$noPosDataChecked       = $noPosData == 1 ? 'checked' : '';
$noDmAlertGlobalChecked = $noDmAlertGlobal == 1 ? 'checked' : '';
$noTimeSyncMsgChecked   = $noTimeSyncMsg == 1 ? 'checked' : '';

echo "<br><h2>Basiseinstellungen von Meshdash</h2>";

echo "<br><br>";
echo '<form id="frmConfigGenerally" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table>';

echo '<tr>';
    echo '<td>OS: '. $osName .'</td>';
echo '</tr>';

if ($hardware != '')
{
    echo '<tr>';
    echo '<td>Hardware: '. $hardware .'</td>';
    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
    echo '<td>POS-Meldungen abschalten:</td>';
    echo '<td><input type="checkbox" name="noPosData" ' . $noPosDataChecked . ' id="noPosData" value="1" /></td>';
echo '</tr>';

echo '<tr>';
    echo '<td>DM-Alert global abschalten:</td>';
    echo '<td><input type="checkbox" name="noDmAlertGlobal" ' . $noDmAlertGlobalChecked . ' id="noDmAlertGlobal" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Keine Time Sync-Meldung erhalten:</td>';
echo '<td><input type="checkbox" name="noTimeSyncMsg" ' . $noTimeSyncMsgChecked . ' id="noTimeSyncMsg" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Max. ScrollBack Reihen (30-200):</td>';
echo '<td><input type="text" name="maxScrollBackRows" size="5" id="maxScrollBackRows" value="' . $maxScrollBackRows . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>LoraIP:</td>';
echo '<td><input type="text" name="loraIp"  id="loraIp" value="' . $loraIp . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Rufzeichen:</td>';
echo '<td><input type="text" name="callSign"  id="callSign" value="' . $callSign . '" /></td>';
echo '</tr>';

echo '<tr>';
    echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';
    echo '<td>&nbsp;</td>';
    echo '<td><input type="button" id="btnSaveConfigGenerally" value="Settings speichern"  /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';

echo '</body>';
echo '</html>';