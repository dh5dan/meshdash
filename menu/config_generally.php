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

$sendData  = $_REQUEST['sendData'] ?? 0;
$hardware  = '';
$lineBreak = '';

#Check what oS is running
$osIssWindows = chkOsIssWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';
$isMobile     = isMobile();

#Wenn Mobile, Linebreak zur besseren Lesbarkeit einfügen
if ($isMobile === true)
{
    $lineBreak = "<br />";
}

if ($sendData === '1')
{
    $resSaveGenerallySetting = saveGenerallySettings();

    if ($resSaveGenerallySetting)
    {
        echo '<span class="successHint">'.date('H:i:s').'-Settings wurden erfolgreich abgespeichert!</span>';

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
$newMsgBgColor     = getParamData('newMsgBgColor');
$maxScrollBackRows = getParamData('maxScrollBackRows');
$doLogEnable       = getParamData('doLogEnable');
$doNotBackupDb     = getParamData('doNotBackupDb');
$clickOnCall       = getParamData('clickOnCall');
$chronLogEnable    = getParamData('chronLogEnable');
$retentionDays     = getParamData('retentionDays'); //Tage logs behalten
$chronMode         = getParamData('chronMode'); // zip|delete
$strictCallEnable  = getParamData('strictCallEnable'); // Strict Call Flag
$selTzName         = getParamData('timeZone') ?? 'Europe/Berlin'; // ZeitZone
$mheardGroup       = getParamData('mheardGroup') ?? 0; // 0= egal welche Gruppe

$openStreetTileServerUrl = trim(getParamData('openStreetTileServerUrl')) ?? 'tile.openstreetmap.org';
$openStreetTileServerUrl = $openStreetTileServerUrl == '' ? 'tile.openstreetmap.org' : $openStreetTileServerUrl;

$selTzName                = $selTzName == '' ? 'Europe/Berlin' : $selTzName;
$noPosDataChecked         = $noPosData == 1 ? 'checked' : '';
$noDmAlertGlobalChecked   = $noDmAlertGlobal == 1 ? 'checked' : '';
$noTimeSyncMsgChecked     = $noTimeSyncMsg == 1 ? 'checked' : '';
$doLogEnableChecked       = $doLogEnable == 1 ? 'checked' : '';
$doNotBackupDbChecked     = $doNotBackupDb == 1 ? 'checked' : '';

$chronLogEnableChecked    = $chronLogEnable == 1 ? 'checked' : '';
$retentionDays            = $retentionDays == '' ? 7 : $retentionDays;
$chronMode                = $chronMode == '' ? 'zip' : $chronMode;
$strictCallEnableChecked  = $strictCallEnable == 1 ? 'checked' : '';

$onClickChronModeCheckedZip    = $chronMode == 'zip' ? 'checked' : '';
$onClickChronModeCheckedDelete = $chronMode == 'delete' ? 'checked' : '';

$onClickOnCallChecked0 = $clickOnCall == 0 ? 'checked' : '';
$onClickOnCallChecked1 = $clickOnCall == 1 ? 'checked' : '';
$onClickOnCallChecked2 = $clickOnCall == 2 ? 'checked' : '';

$newMsgBgColor = $newMsgBgColor == '' ? '#FFFFFF' : $newMsgBgColor;

$mheardGroup = $mheardGroup == 0 ? '' : $mheardGroup;

echo "<h2>Basiseinstellungen von MeshDash-SQL</h2>";

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
    echo '<td>POS-Meldungen AUS:</td>';
    echo '<td><input type="checkbox" name="noPosData" ' . $noPosDataChecked . ' id="noPosData" value="1" /></td>';
echo '</tr>';

echo '<tr>';
    echo '<td>DM-Alert global AUS:</td>';
    echo '<td><input type="checkbox" name="noDmAlertGlobal" ' . $noDmAlertGlobalChecked . ' id="noDmAlertGlobal" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Time Sync-Meldung AUS:</td>';
echo '<td><input type="checkbox" name="noTimeSyncMsg" ' . $noTimeSyncMsgChecked . ' id="noTimeSyncMsg" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>DB in Backup:</td>';
echo '<td><input type="checkbox" name="doNotBackupDb" ' . $doNotBackupDbChecked . ' id="doNotBackupDb" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Zeitzone (DST):</td>';

echo '<td>';
echo '<select name="selTzName" id="selTzName">';
selectTimezone($selTzName);
echo '</select>';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Logfile-Erstellung:</td>';
echo '<td><input type="checkbox" name="doLogEnable" ' . $doLogEnableChecked . ' id="doLogEnable" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Log-Rotation aktivieren:</td>';
echo '<td><input type="checkbox" name="chronLogEnable" ' . $chronLogEnableChecked . ' id="chronLogEnable" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Aufbewahrungstage:</td>';
echo '<td><input type="text" name="retentionDays" id="retentionDays" value="' . $retentionDays . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Log-Rotation Modus:</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;- in Zip-Archiv speichern:</td>';
echo '<td><input type="radio" name="chronMode" ' . $onClickChronModeCheckedZip . ' id="chronModeZip" value="zip" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;- sofort löschen:</td>';
echo '<td><input type="radio" name="chronMode" ' . $onClickChronModeCheckedDelete . ' id="chronModeDelete" value="delete" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Filter mit Strict-Call:</td>';
echo '<td><input type="checkbox" name="strictCallEnable" ' . $strictCallEnableChecked . ' id="strictCallEnable" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Klick auf Call:</td>';
echo '<td>&nbsp</td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;- Setzt Call in DM-Feld:</td>';
echo '<td><input type="radio" name="clickOnCall" ' . $onClickOnCallChecked0 . ' id="clickOnCall0" value="0" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;- Öffnet QRZ.com:</td>';
echo '<td><input type="radio" name="clickOnCall" ' . $onClickOnCallChecked1 . ' id="clickOnCall1" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;- Setzt Call in Msg-Feld:</td>';
echo '<td><input type="radio" name="clickOnCall" ' . $onClickOnCallChecked2 . ' id="clickOnCall2" value="2" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Anfrage Mheard-Gruppe:</td>';
echo '<td><input type="text"  name="mheardGroup" id="mheardGroup" value="' . $mheardGroup . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Max. Scroll-Back '.$lineBreak.'Reihen (30-200):</td>';
echo '<td><input type="text" name="maxScrollBackRows" id="maxScrollBackRows" value="' . $maxScrollBackRows . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>LoraIP/mDNS:</td>';
echo '<td><input type="text" name="loraIp"  id="loraIp" value="' . $loraIp . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Rufzeichen mit SSID:</td>';
echo '<td><input type="text" name="callSign"  id="callSign" value="' . $callSign . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Hintergrundfarbe '.$lineBreak.'<b>Neue Nachrichten</b>:</td>';
echo '<td><input type="color" name="newMsgBgColor"  id="newMsgBgColor" value="' . $newMsgBgColor . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>OpenStreet Tile-Url:</td>';
echo '<td><input type="text" name="openStreetTileServerUrl"  id="openStreetTileServerUrl" value="' . $openStreetTileServerUrl . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><span class="failureHint">Das Rufzeichen muss mit der Angabe<br>im Lora übereinstimmen!</span></td>';
echo '</tr>';

echo '<tr>';
    echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';
    echo '<td colspan="2"><input type="button" class="btnSaveConfigGenerally" id="btnSaveConfigGenerally" value="Settings speichern"  /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';

echo '</body>';
echo '</html>';