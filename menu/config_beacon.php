<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;
echo '<head><title data-i18n="submenu.config_beacon.lbl.title">Baken Einstellungen</title>';

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

require_once '../include/func_js_config_beacon.php';
require_once '../include/func_php_config_beacon.php';
require_once '../include/func_js_core.php';

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
        echo '<span class="successHint">'.date('H:i:s').'-<span data-i18n="submenu.config_beacon.msg.save-settings-success">Settings wurden erfolgreich abgespeichert!</span></span>';
    }
    else
    {
        echo '<span class="failureHint">' . date('H:i:s') . '-<span data-i18n="submenu.config_beacon.msg.save-settings-failed">Es gab einen Fehler beim Abspeichern der Settings!</span></span>';
    }
}

$beaconInterval = getBeaconData('beaconInterval');
$beaconInterval = $beaconInterval == '' ? 5 : $beaconInterval;

$beaconStopCount = getBeaconData('beaconStopCount') ?? 100;
$beaconMsg       = getBeaconData('beaconMsg') ?? 'Bakensendung';
$beaconGroup     = getBeaconData('beaconGroup') ?? 9;
$beaconOtp       = getBeaconData('beaconOtp') ?? '';

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

$resCheckBeaconCron = checkBgTask('cronBeacon') == '' ? getStatusIcon('inactive') : getStatusIcon('active');

echo '<h2><span data-i18n="submenu.config_beacon.lbl.title">Baken Einstellungen</span></h2>';

echo '<form id="frmBake" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden"  id="osIssWindows" value="' . $osIssWindows . '" />';

echo '<table>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_beacon.lbl.beacon-interval">Intervall in Min.</span>:</td>';
echo '<td><select name="beaconInterval" id="beaconInterval">';
selectBeaconIntervall($beaconInterval);
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_beacon.lbl.beacon-stop-counts">Stop-Counts (100)</span>:</td>';
echo '<td><input type="text" name="beaconStopCount" size="4" id="beaconStopCount" value="' . $beaconStopCount . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_beacon.lbl.beacon-text">Baken-Text</span>:</td>';
echo '<td><input type="text" name="beaconMsg" size="20px" id="beaconMsg" value="' . $beaconMsg . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_beacon.lbl.beacon-group">Baken-Gruppe</span>:</td>';
echo '<td><input type="text" name="beaconGroup" size="4" id="beaconGroup" value="' . $beaconGroup . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_beacon.lbl.beacon-task-status">Task enabled</span>:</td>';
echo '<td><input type="checkbox" name="beaconEnabled" ' . $beaconEnabledChecked . ' id="beaconEnabled" value="1" /></td>';
echo '</tr>';


echo '<tr>';
echo '<td><span data-i18n="submenu.config_beacon.lbl.beacon-otp-pwd">Remote-Start OTP</span>:</td>';
echo '<td><input type="text" name="beaconOtp" size="20px" id="beaconOtp" value="' . $beaconOtp . '" placeholder="A-Z, a-z, 0-9"/></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_beacon.lbl.beacon-init-sent">Startzeit</span>:</td>';
echo '<td>' . $beaconInitSendTs . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_beacon.lbl.beacon-last-sent">Zuletzt gesendet</span>:</td>';
echo '<td>' . $beaconLastSendTs . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_beacon.lbl.beacon-current-count">Aktueller Zähler</span>:</td>';
echo '<td>' . $currentBeaconCount . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_beacon.lbl.beacon-cron-status">Baken-Cron Status</span>:</td>';

echo '<td>';
echo $resCheckBeaconCron;
echo '</td>';

echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><span class="failureHint"><span data-i18n="submenu.config_beacon.lbl.beacon-hint">' .
    'Eine autom. Abschaltung erfolgt wenn:' .
    '<br>Laufzeit größer als 8h.' .
    '<br>Stop-Count Ziel erreicht.' .
    '</span></span></td>';
echo '</tr>';

echo '<tr>';
    echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';

echo '<td colspan="2">
        <button type="button" class="btnSaveConfigGenerally" id="btnSaveBakeSettings"><span data-i18n="submenu.config_beacon.btn.save-settings">Settings speichern</span></button>
      </td>';
echo '</tr>';

echo '</table>';
echo '</form>';

echo '<div id="pageLoading" class="pageLoadingSub"></div>';
echo '<script>
            $.getJSON("../translation.php?lang=' . $userLang . '", function(dict) {
            applyTranslation(dict); // siehe JS oben
            });
        </script>';
echo '</body>';
echo '</html>';