<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Bakentest</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/config_beacon.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_config_beacon.php';
require_once '../include/func_php_config_beacon.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;

#Check what oS is running
$osIssWindows = chkOsIsWindows();

if ($sendData === '1')
{
    $resSaveSendQueueSettings = saveBeaconSettings();

    if ($resSaveSendQueueSettings)
    {
        echo '<span class="successHint">'.date('H:i:s').'-Settings wurden erfolgreich abgespeichert!</span>';
    }
    else
    {
        echo '<span class="failureHint">Es gab einen Fehler beim Abspeichern der Settings!</span>';
    }
}

$beaconInterval = getBeaconData('beaconInterval');
$beaconInterval = $beaconInterval == '' ? 5 : $beaconInterval;

$beaconStopCount = getBeaconData('beaconStopCount') ?? 100;
$beaconMsg       = getBeaconData('beaconMsg') ?? 'Bakensendung';
$beaconGroup     = getBeaconData('beaconGroup') ?? 9;

$beaconStopCount = $beaconStopCount == '' ? 100 : $beaconStopCount;
$beaconMsg       = $beaconMsg == '' ? 'Bakensendung' : $beaconMsg;
$beaconGroup     = $beaconGroup == '' ? 9 : $beaconGroup;

$beaconEnabled        = getBeaconData('beaconEnabled');
$beaconEnabled        = $beaconEnabled == '' ? 0 : $beaconEnabled;
$beaconEnabledChecked = $beaconEnabled == 1 ? 'checked' : '';

$beaconInitSendTs   = getBeaconData('beaconInitSendTs') ?? '0000-00-00 00:00:00';
$beaconLastSendTs   = getBeaconData('beaconLastSendTs') ?? '0000-00-00 00:00:00';
$currentBeaconCount = getBeaconData('beaconCount') ?? 0;

$beaconInitSendTs   = $beaconInitSendTs == '' ? '0000-00-00 00:00:00' : $beaconInitSendTs;
$beaconLastSendTs   = $beaconLastSendTs == '' ? '0000-00-00 00:00:00' : $beaconLastSendTs;
$currentBeaconCount = $currentBeaconCount == '' ? 0 : $currentBeaconCount;

if ($osIssWindows === false)
{
    $resCheckBeaconCron = getBeaconCronEntries(array('send_beacon.php')) === false ? getStatusIcon('inactive') : getStatusIcon('active');
}

echo "<h2>Baken Einstellungen</h2>";

echo '<form id="frmBake" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden"  id="osIssWindows" value="' . $osIssWindows . '" />';

echo '<table>';

echo '<tr>';
echo '<td>Intervall in Min.:</td>';
echo '<td><select name="beaconInterval" id="beaconInterval">';
selectBeaconIntervall($beaconInterval);
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Stop-Counts (100):</td>';
echo '<td><input type="text" name="beaconStopCount" size="4" id="beaconStopCount" value="' . $beaconStopCount . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Text:</td>';
echo '<td><input type="text" name="beaconMsg" size="20px" id="beaconMsg" value="' . $beaconMsg . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Gruppe:</td>';
echo '<td><input type="text" name="beaconGroup" size="4" id="beaconGroup" value="' . $beaconGroup . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Task enabled:</td>';
echo '<td><input type="checkbox" name="beaconEnabled" ' . $beaconEnabledChecked . ' id="beaconEnabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Init Send:</td>';
echo '<td>' . $beaconInitSendTs . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Last Send:</td>';
echo '<td>' . $beaconLastSendTs . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Current Count:</td>';
echo '<td>' . $currentBeaconCount . '</td>';
echo '</tr>';

if ($osIssWindows === false)
{
    echo '<tr>';
    echo '<td>Baken-Cron Status:</td>';

    echo '<td>';
    echo $resCheckBeaconCron;
    echo '</td>';

    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><span class="failureHint">Eine autom. Abschaltung erfolgt wenn:';
echo '<br>Laufzeit größer als 8h.';
echo '<br>Stop-Count Ziel erreicht.';
echo'</span></td>';
echo '</tr>';

echo '<tr>';
    echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';
    echo '<td colspan="2"><input type="button" class="btnSaveConfigGenerally" id="btnSaveBakeSettings" value="Settings speichern"  /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';

echo '<div id="pageLoading" class="pageLoadingSub"></div>';
echo '</body>';
echo '</html>';