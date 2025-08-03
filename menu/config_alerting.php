<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;

echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';

echo '<head><title data-i18n="submenu.config_alerting.lbl.title">Benachrichtigungen</title>';

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

require_once '../include/func_js_config_alerting.php';
require_once '../include/func_php_config_alerting.php';
require_once '../include/func_js_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);

ini_set('display_errors',1);
ini_set('upload_max_filesize', '30M'); // Erhöht die maximale Upload-Dateigröße auf 20 MB (2M)
ini_set('post_max_size', '30M'); // Erhöht die maximale POST-Daten-Größe auf 25 MB (8M)
ini_set('memory_limit', '256M'); // Falls nötig das Speicherlimit erhöhen (128M)
ini_set('max_execution_time', '300'); // Ausführungszeit auf 5min bei nicht performanten Geräten

$sendData       = $_REQUEST['sendData'] ?? 0;
$sendDataUpload = $_REQUEST['sendDataUpload'] ?? 0;

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$osName       = $osIssWindows === true ? 'Windows': 'Linux';
$debugFlag    = false;

$basename     = pathinfo(getcwd())['basename'];
$soundDirSub  = '../sound/';
$soundDirRoot = 'sound/';
$soundDir     = $basename == 'menu' ? $soundDirSub: $soundDirRoot;

#init
$notifyCallSign  = '';
$notifySoundFile = '';
$notifySrcDst    = 0;
$notifyEnabled   = 0;
$notifyId        = 0;

#Save Notify-Item
if ($sendData === '1')
{
    $resSveNotifySettings = saveNotifySettings();

    if ($resSveNotifySettings)
    {
        echo '<span class="successHint">'.date('H:i:s').'-<span data-i18n="submenu.send_queue.msg.save-settings-success">Settings wurden erfolgreich abgespeichert!</span></span>';
    }
    else
    {
        echo '<span class="failureHint">' . date('H:i:s') . '-<span data-i18n="submenu.send_queue.msg.save-settings-failed">Es gab einen Fehler beim Abspeichern der Settings!</span></span>';
    }
}

#Delete Notify-Item
if ($sendData === '2')
{
    $deleteNotifyItemId  = (int) $_REQUEST['deleteNotifyItemId'];
    $resDeleteNotifyItem = deleteNotifyItem($deleteNotifyItemId);

    if ($resDeleteNotifyItem === true)
    {
        echo '<br><span class="successHint">' . date('H:i:s') . '-Eintrag: ' . $deleteNotifyItemId . ' erfolgreich gelöscht.</span>';
    }
    else
    {
        echo '<br><span class="failureHint">' . date('H:i:s') . '-Fehler beim Löschen von Eintrag: ' . $deleteNotifyItemId . '</span>';
    }
}

#Delete Soundfile
if ($sendDataUpload === '3')
{
    $deleteFileImage         = trim($_POST['deleteFileImage']);
    $deleteFileImageFullPath = $soundDir . $deleteFileImage;

    if (file_exists($deleteFileImageFullPath))
    {
        if(unlink($deleteFileImageFullPath))
        {
            echo '<br><span class="successHint">' . date('H:i:s') . '-'
                . $deleteFileImage . ' erfolgreich gelöscht.</span>';
        }
        else
        {
            echo '<br><span class="failureHint">' . date('H:i:s') . '-Fehler beim Löschen von '
                . $deleteFileImage . '</span>';
        }
    }
    else
    {
        echo '<br><span class="failureHint">' . date('H:i:s') . '-'
            . $deleteFileImage . ' nicht im Sound-Verzeichnis gefunden.</span>';
    }
}

#Upload soundfile
if ($sendDataUpload === '6')
{
    // Prüft, ob eine Datei hochgeladen wurde und ob sie eine ZIP-Datei ist
    if (isset($_FILES['uploadSoundFile']) && $_FILES['uploadSoundFile']['error'] === UPLOAD_ERR_OK)
    {
        if (copy($_FILES['uploadSoundFile']['tmp_name'], $soundDir . $_FILES['uploadSoundFile']['name']))
        {
            unlink($_FILES['uploadSoundFile']['tmp_name']);

            if ($osIssWindows === false)
            {
                exec('chmod 644 ' . $soundDir . $_FILES['uploadSoundFile']['name']);
            }

            echo '<span class="successHint">' . date('H:i:s') . '-'
                . $_FILES['uploadSoundFile']['name'] . ' erfolgreich hochgeladen!</span>';
        }
        else
        {
            echo '<span class="failureHint">' . date('H:i:s') . '-Fehler beim Hochladen von: '
                . $_FILES['uploadSoundFile']['name'] . '!</span>';
        }
    }
}

$arrayNotificationData = getNotificationData();

if ($arrayNotificationData === false)
{
    echo '<span class="failureHint">' . date('H:i:s') . '-Fehler beim Abfragen der Datenbank!</span>';
    exit();
}

echo '<h2></h2>';
echo '<h2><span data-i18n="submenu.config_alerting.lbl.title">Benachrichtigungen</span></h2>';

echo '<form id="frmConfigAlerting" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" name="deleteNotifyItemId" id="deleteNotifyItemId" value="0" />';
echo '<table>';

foreach ($arrayNotificationData as $notifyCallSign => $value)
{
    $notifySoundFile = $value['notifySoundFile'];
    $notifySrcDst    = $value['notifySrcDst'];
    $notifyEnabled   = $value['notifyEnabled'];
    $notifyId        = $value['notifyId'];

    $notifyEnabledChecked = $notifyEnabled == 1 ? 'checked': '';
    $notifySrcDstChecked0 = $notifySrcDst == 0 ? 'checked': '';
    $notifySrcDstChecked1 = $notifySrcDst == 1 ? 'checked': '';

    echo '<input type="hidden" name="notifyId[' . $notifyId . ']" id="notifyId" value="' . $notifyId . '" />';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.config_alerting.lbl.snd-file">Snd-File</span>:</td>';
    echo '<td><select name="notifySoundFile[' . $notifyId . ']" id="notifySoundFile_' . $notifyId . '">';
    selectSoundFile(showAlertMediaFiles(false), $notifySoundFile);
    echo '</select>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.config_alerting.lbl.callsign">Rufzeichen</span>:</td>';
    echo '<td><input type="text" name="notifyCallSign[' . $notifyId . ']" id="notifyCallSign_' . $notifyId . '" size="10" value="' . $notifyCallSign . '" placeholder="Call-SSID" />&nbsp;';
    echo '<input type="checkbox" name="notifyEnabled[' . $notifyId . ']" ' . $notifyEnabledChecked . ' id="notifyEnabled_' . $notifyId . '" value="1" />&nbsp;';
    echo '<span data-notify_delete="'
         . $notifyId
         . '" class="deleteNotifyItem"/>'
         . html_entity_decode(getStatusIcon("error"))
         . '</span>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.config_alerting.lbl.src-dst">Src/Dst</span>:</td>';
    echo '<td>';
    echo 'SRC <input type="radio" ' . $notifySrcDstChecked0 . ' name="notifySrcDst[' . $notifyId . ']" id="notifySrcDst0_' . $notifyId . '" value="0" />&nbsp;&nbsp;';
    echo 'DST <input type="radio" ' . $notifySrcDstChecked1 . ' name="notifySrcDst[' . $notifyId . ']" id="notifySrcDst1_' . $notifyId . '" value="1" />&nbsp;';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2">&nbsp;</td>';
    echo '</tr>';
}

if (count($arrayNotificationData) > 0)
{
    ++$notifyId;

    $notifyNewCallSign    = '';
    $notifyEnabledChecked = '';
    $notifySrcDstChecked0 = 'checked';
    $notifySrcDstChecked1 = '';
    $notifySoundFile      = '';

    echo '<input type="hidden" name="notifyId[' . $notifyId . ']" id="notifyId" value="' . $notifyId . '" />';

    echo '<tr class="notifyNewRow">';
    echo '<td><span data-i18n="submenu.config_alerting.lbl.snd-file">Snd-File</span>:</td>';
    echo '<td>';
    echo '<select name="notifySoundFile[' . $notifyId . ']" id="notifySoundFile_' . $notifyId . '" disabled>';
    selectSoundFile(showAlertMediaFiles(false), $notifySoundFile);
    echo '</select>';
    echo '</td>';
    echo '<td></td>';
    echo '</tr>';

    echo '<tr class="notifyNewRow">';
    echo '<td><span data-i18n="submenu.config_alerting.lbl.callsign">Rufzeichen</span>:</td>';
    echo '<td><input type="text" name="notifyCallSign[' . $notifyId . ']" id="notifyCallSign_' . $notifyId . '" size="10" value="' . $notifyNewCallSign . '" placeholder="Call-SSID" disabled />&nbsp;';
    echo '<input type="checkbox" name="notifyEnabled[' . $notifyId . ']" ' . $notifyEnabledChecked . ' id="notifyEnabled_' . $notifyId . '" value="1" disabled />&nbsp;';
    echo '</td>';
    echo '</tr>';

    echo '<tr class="notifyNewRow">';
    echo '<td><span data-i18n="submenu.config_alerting.lbl.src-dst">Src/Dst</span>:</td>';
    echo '<td>';
    echo 'SRC <input type="radio" ' . $notifySrcDstChecked0 . ' name="notifySrcDst[' . $notifyId . ']" id="notifySrcDst0_' . $notifyId . '" value="0" disabled />&nbsp;&nbsp;';
    echo 'DST <input type="radio" ' . $notifySrcDstChecked1 . ' name="notifySrcDst[' . $notifyId . ']" id="notifySrcDst1_' . $notifyId . '" value="1" disabled/>&nbsp;';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';

    echo '<td colspan="3">
        <button type="button" class="btnSaveConfigAlerting" id="btnAddNewItem">
            <span data-i18n="submenu.config_alerting.btn.new-item">Neuer Eintrag</span>
        </button>
      </td>';

    echo '</tr>';
}

if (count($arrayNotificationData) == 0)
{
    $notifyId = 1;

    $notifyEnabledChecked = $notifyEnabled == 1 ? 'checked': '';
    $notifySrcDstChecked0 = $notifySrcDst == 0 ? 'checked': '';
    $notifySrcDstChecked1 = $notifySrcDst == 1 ? 'checked': '';

    echo '<input type="hidden" name="notifyId[' . $notifyId . ']" id="notifyId" value="' . $notifyId . '" />';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.config_alerting.lbl.snd-file">Snd-File</span>:</td>';
    echo '<td><select name="notifySoundFile[' . $notifyId . ']" id="notifySoundFile_' . $notifyId . '">';
    selectSoundFile(showAlertMediaFiles(false), $notifySoundFile);
    echo '</td>';
    echo '<td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.config_alerting.lbl.callsign">Rufzeichen</span>:</td>';
    echo '<td><input type="text" name="notifyCallSign[' . $notifyId . ']" id="notifyCallSign_' . $notifyId . '" size="10" value="' . $notifyCallSign . '" placeholder="Call-SSID" />&nbsp;';
    echo '<input type="checkbox" name="notifyEnabled[' . $notifyId . ']" ' . $notifyEnabledChecked . ' id="notifyEnabled_' . $notifyId . '" value="1" />&nbsp;';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.config_alerting.lbl.src-dst">Src/Dst</span>:</td>';
    echo '<td>';
    echo 'SRC <input type="radio" ' . $notifySrcDstChecked0 . ' name="notifySrcDst[' . $notifyId . ']" id="notifySrcDst0_' . $notifyId . '" value="0" />&nbsp;&nbsp;';
    echo 'DST <input type="radio" ' . $notifySrcDstChecked1 . ' name="notifySrcDst[' . $notifyId . ']" id="notifySrcDst1_' . $notifyId . '" value="1" />&nbsp;';
    echo '</td>';
    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="3"><hr></td>';
echo '</tr>';

echo '<tr>';

echo '<td colspan="3">
        <button type="button" class="btnSaveConfigAlerting" id="btnSaveConfigAlerting">
            <span data-i18n="submenu.config_alerting.btn.save-settings">Settings speichern</span>
        </button>
      </td>';

echo '</tr>';

echo '</table>';
echo '</form>';

echo '<br>';
showAlertMediaFiles();

echo '<div id="pageLoading" class="pageLoadingSub"></div>';

echo '<script>
            $.getJSON("../translation.php?lang=' . $userLang . '", function(dict) {
            applyTranslation(dict); // siehe JS oben
            });
        </script>';

echo '</body>';
echo '</html>';