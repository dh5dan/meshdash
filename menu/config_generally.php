<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;

echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';
echo '<head><title data-i18n="submenu.config_generally.lbl.title">Basiseinstellungen</title>';

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


require_once '../include/func_js_config_generally.php';
require_once '../include/func_php_config_generally.php';
require_once '../include/func_js_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData  = $_REQUEST['sendData'] ?? 0;
$hardware  = '';
$lineBreak = '';

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';
$isMobile     = isMobile();
$architecture = php_uname('m');

#Wenn Mobile, Linebreak zur besseren Lesbarkeit einfügen
if ($isMobile === true)
{
    $lineBreak = "<br>";
}

if ($sendData === '1')
{
    $resSaveGenerallySetting = saveGenerallySettings();

    if ($resSaveGenerallySetting)
    {
        echo '<span class="successHint">'.date('H:i:s').'-<span data-i18n="submenu.config_generally.msg.save-settings-success">Settings wurden erfolgreich abgespeichert!</span></span>';

        echo "<script>reloadBottomFrame();</script>";
    }
    else
    {
        echo '<span class="failureHint">' . date('H:i:s') . '-<span data-i18n="submenu.config_generally.msg.save-settings-failed">Es gab einen Fehler beim Abspeichern der Settings!</span></span>';
    }
}

if ($osIssWindows === false)
{
    $cpuInfo      = file_get_contents('/proc/cpuinfo');
    $hardware     = "Kein Raspberry Pi";

    if (file_exists('/sys/class/dmi/id/product_name'))
    {
        $prodName     = file_get_contents('/sys/class/dmi/id/product_name');
        $hardware     = $prodName;
    }

    if ((strpos($cpuInfo, 'Raspberry Pi') !== false || strpos($cpuInfo, 'BCM') !== false) &&
        ($architecture === 'armv7l' || $architecture === 'aarch64'))
    {
        $hardware = "Raspberry Pi";
    }
}
else
{
    $osBuild = explode(' ', php_uname('v')); //Build Version
    $osName .= ' ' . (int) php_uname('r') . ' (' . $osBuild[0] . ' ' . $osBuild[1] . ')';
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
$selLanguage       = getParamData('language') ?? 'de'; // Sprache
$mheardGroup       = getParamData('mheardGroup') ?? 0; // 0= egal welche Gruppe
$bubbleStyleView   = getParamData('bubbleStyleView') ?? 0; // 1= Bubble Style aktiv
$bubbleMaxWidth    = getParamData('bubbleMaxWidth') ?? 40;

$openStreetTileServerUrl = trim(getParamData('openStreetTileServerUrl')) ?? 'tile.openstreetmap.org';
$openStreetTileServerUrl = $openStreetTileServerUrl == '' ? 'tile.openstreetmap.org' : $openStreetTileServerUrl;

$selTzName                = $selTzName == '' ? 'Europe/Berlin' : $selTzName;
$selLanguage              = $selLanguage == '' ? 'de' : $selLanguage;
$noPosDataChecked         = $noPosData == 1 ? 'checked' : '';
$noDmAlertGlobalChecked   = $noDmAlertGlobal == 1 ? 'checked' : '';
$noTimeSyncMsgChecked     = $noTimeSyncMsg == 1 ? 'checked' : '';
$doLogEnableChecked       = $doLogEnable == 1 ? 'checked' : '';
$doNotBackupDbChecked     = $doNotBackupDb == 1 ? 'checked' : '';
$bubbleStyleViewChecked   = $bubbleStyleView == 1 ? 'checked' : '';
$chronLogEnableChecked    = $chronLogEnable == 1 ? 'checked' : '';
$retentionDays            = $retentionDays == '' ? 7 : $retentionDays;
$chronMode                = $chronMode == '' ? 'zip' : $chronMode;
$strictCallEnableChecked  = $strictCallEnable == 1 ? 'checked' : '';
$bubbleMaxWidth           = $bubbleMaxWidth == '' ? 40 : $bubbleMaxWidth;

$onClickChronModeCheckedZip    = $chronMode == 'zip' ? 'checked' : '';
$onClickChronModeCheckedDelete = $chronMode == 'delete' ? 'checked' : '';

$onClickOnCallChecked0 = $clickOnCall == 0 ? 'checked' : '';
$onClickOnCallChecked1 = $clickOnCall == 1 ? 'checked' : '';
$onClickOnCallChecked2 = $clickOnCall == 2 ? 'checked' : '';

$newMsgBgColor = $newMsgBgColor == '' ? '#FFFFFF' : $newMsgBgColor;
$mheardGroup   = $mheardGroup == 0 ? '' : $mheardGroup;

echo '<h2><span data-i18n="submenu.config_generally.lbl.title">Basiseinstellungen</span></h2>';

echo '<form id="frmConfigGenerally" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table>';

echo '<tr>';
    echo '<td>OS :</td>';
    echo '<td>'. $osName . '</td>';
echo '</tr>';

if ($hardware != '')
{
    echo '<tr>';
    echo '<td>Hardware:</td>';
    echo '<td>' . $hardware . '</td>';
    echo '</tr>';
}

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.architecture">Architektur:</span></td>';
echo '<td>' . $architecture . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
    echo '<td><span data-i18n="submenu.config_generally.lbl.pos-msg">POS-Meldungen &#10140;[AUS]</span>:</td>';
    echo '<td><input type="checkbox" name="noPosData" ' . $noPosDataChecked . ' id="noPosData" value="1" /></td>';
echo '</tr>';

echo '<tr>';
    echo '<td><span data-i18n="submenu.config_generally.lbl.dm-alert-global">DM-Alert global &#10140;[AUS]</span>:</td>';
    echo '<td><input type="checkbox" name="noDmAlertGlobal" ' . $noDmAlertGlobalChecked . ' id="noDmAlertGlobal" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.time-sync-msg">Time Sync-Meldung &#10140;[AUS]</span>:</td>';
echo '<td><input type="checkbox" name="noTimeSyncMsg" ' . $noTimeSyncMsgChecked . ' id="noTimeSyncMsg" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.db-backup">DB-Backup &#10140;[AUS]</span>:</td>';
echo '<td><input type="checkbox" name="doNotBackupDb" ' . $doNotBackupDbChecked . ' id="doNotBackupDb" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.bubble-style">Bubble-Style &#10140;[AN]</span>:</td>';
echo '<td><input type="checkbox" name="bubbleStyleView" ' . $bubbleStyleViewChecked . ' id="bubbleStyleView" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.bubble-max-width">Bubble Max-Breite (40-100%)</span>:</td>';
echo '<td><input type="text" name="bubbleMaxWidth" id="bubbleMaxWidth" style="width: auto" size="2" maxlength="3" value="' . $bubbleMaxWidth . '" /> %</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.timezone-dst">Zeitzone (DST)</span>:</td>';

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
echo '<td><span data-i18n="submenu.config_generally.lbl.log-mode">Logfile-Erstellung &#10140;[AN]</span>:</td>';
echo '<td><input type="checkbox" name="doLogEnable" ' . $doLogEnableChecked . ' id="doLogEnable" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.log-rotation">Log-Rotation &#10140;[AN]</span>:</td>';
echo '<td><input type="checkbox" name="chronLogEnable" ' . $chronLogEnableChecked . ' id="chronLogEnable" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.max-hold-days">Aufbewahrungstage</span>:</td>';
echo '<td><input type="text" name="retentionDays" id="retentionDays" style="width: auto" size="2" maxlength="3" value="' . $retentionDays . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.log-rotate-mode">Log-Rotation Modus</span>:</td>';
echo '<td>&nbsp;</td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;- <span data-i18n="submenu.config_generally.lbl.save-zip-archive">in Zip-Archiv speichern</span>:</td>';
echo '<td><input type="radio" name="chronMode" ' . $onClickChronModeCheckedZip . ' id="chronModeZip" value="zip" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;- <span data-i18n="submenu.config_generally.lbl.del-log-now">sofort löschen</span>:</td>';
echo '<td><input type="radio" name="chronMode" ' . $onClickChronModeCheckedDelete . ' id="chronModeDelete" value="delete" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.filter-strict-call">Filter Strict-Call &#10140;[AN]</span>:</td>';
echo '<td><input type="checkbox" name="strictCallEnable" ' . $strictCallEnableChecked . ' id="strictCallEnable" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.click-on-call">Klick auf Call</span>:</td>';
echo '<td>&nbsp</td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;- <span data-i18n="submenu.config_generally.lbl.dm-call-click">Setzt Call in DM-Feld</span>:</td>';
echo '<td><input type="radio" name="clickOnCall" ' . $onClickOnCallChecked0 . ' id="clickOnCall0" value="0" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;- <span data-i18n="submenu.config_generally.lbl.open-qrz-on-click">Öffnet QRZ.com</span>:</td>';
echo '<td><input type="radio" name="clickOnCall" ' . $onClickOnCallChecked1 . ' id="clickOnCall1" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;- <span data-i18n="submenu.config_generally.lbl.at-call-on-click">Setzt @Call in Msg-Feld</span>:</td>';
echo '<td><input type="radio" name="clickOnCall" ' . $onClickOnCallChecked2 . ' id="clickOnCall2" value="2" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.called-mh-grp">Anfrage Mheard-Gruppe</span>:</td>';
echo '<td><input type="text"  name="mheardGroup" id="mheardGroup" value="' . $mheardGroup . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.max-scroll-back" data-vars-replace="' .
    htmlspecialchars($lineBreak, ENT_QUOTES, 'UTF-8') . '">Max. Scroll-Back ' . $lineBreak . 'Reihen (30-200)</span>:</td>';
echo '<td><input type="text" name="maxScrollBackRows" id="maxScrollBackRows" value="' . $maxScrollBackRows . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.lora-ip-mdns">LoraIP/mDNS</span>:</td>';
echo '<td><input type="text" name="loraIp"  id="loraIp" value="' . $loraIp . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.call-ssid">Rufzeichen mit SSID:</span></td>';
echo '<td><input type="text" name="callSign"  id="callSign" value="' . $callSign . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.background-color-new-msg" data-vars-replace="' . htmlspecialchars($lineBreak, ENT_QUOTES, 'UTF-8') . '">Hintergrundfarbe ' . $lineBreak . '<b>Neue Nachrichten</b></span>:</td>';
echo '<td><input type="color" name="newMsgBgColor"  id="newMsgBgColor" value="' . $newMsgBgColor . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.osm-tile-url">OpenStreet Tile-Url</span>:</td>';
echo '<td><input type="text" name="openStreetTileServerUrl"  id="openStreetTileServerUrl" value="' . $openStreetTileServerUrl . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.config_generally.lbl.speech">Sprache</span>:</td>';
echo '<td>';
echo '<select name="selLanguage" id="selLanguage">';
selectLanguage($selLanguage);
echo '</select>';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><span class="failureHint"><span data-i18n="submenu.config_generally.lbl.call-hint">Das Rufzeichen muss mit der Angabe<br>im Lora übereinstimmen!</span></span></td>';
echo '</tr>';

echo '<tr>';
    echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';

echo '<td colspan="2">
        <button type="button" class="btnSaveConfigGenerally" id="btnSaveConfigGenerally">
            <span data-i18n="submenu.config_generally.btn.save-settings">Settings speichern</span>
        </button>
      </td>';

echo '</tr>';

echo '</table>';
echo '</form>';

echo '<script>
            $.getJSON("../translation.php?lang=' . $userLang . '", function(dict) {
            applyTranslation(dict); // siehe JS oben
            });
        </script>';

echo '</body>';
echo '</html>';