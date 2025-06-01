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

ini_set('upload_max_filesize', '30M'); // Erhöht die maximale Upload-Dateigröße auf 20 MB (2M)
ini_set('post_max_size', '30M'); // Erhöht die maximale POST-Daten-Größe auf 25 MB (8M)
ini_set('memory_limit', '256M'); // Falls nötig das Speicherlimit erhöhen (128M)
ini_set('max_execution_time', '300'); // Ausführungszeit auf 5min bei nicht performanten Geräten


$sendData       = $_REQUEST['sendData'] ?? 0;
$sendDataUpload = $_REQUEST['sendDataUpload'] ?? 0;
$hardware       = '';

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';
$debugFlag    = false;

$basename     = pathinfo(getcwd())['basename'];
$soundDirSub  = '../sound/';
$soundDirRoot = 'sound/';
$soundDir     = $basename == 'menu' ? $soundDirSub : $soundDirRoot;

if ($sendData === '1')
{
    $resSaveAlertingSetting = saveAlertingSettings();

    if ($resSaveAlertingSetting)
    {
        echo '<span class="successHint">'.date('H:i:s').'-Settings erfolgreich abgespeichert!</span>';
    }
    else
    {
        echo '<span class="failureHint">Es gab einen Fehler beim Abspeichern der Settings!</span>';
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
            echo '<br><span class="successHint">' . $deleteFileImage . ' erfolgreich gelöscht.</span>';
        }
        else
        {
            echo '<br><span class="failureHint">Fehler beim Löschen von ' . $deleteFileImage . '</span>';
        }
    }
    else
    {
        echo '<br><span class="failureHint">' . $deleteFileImage . ' nicht im Sound-Verzeichnis gefunden.</span>';
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

            echo '<span class="successHint">'.date('H:i:s').'-' . $_FILES['uploadSoundFile']['name'] . ' erfolgreich hochgeladen!</span>';
        }
        else
        {
            echo '<span class="failureHint">'.date('H:i:s').'-Fehler beim Hochladen von: ' . $_FILES['uploadSoundFile']['name'] . '!</span>';
        }

        if ($debugFlag === true)
        {
            echo "xxx<pre>";
            print_r($_FILES);
            echo "</pre>";
        }
    }
}

$noDmAlertGlobal   = getParamData('noDmAlertGlobal');

$alertSoundFileSrc = getParamData('alertSoundFileSrc');
$alertEnabledSrc   = getParamData('alertEnabledSrc');
$alertSoundCallSrc = getParamData('alertSoundCallSrc');

$alertSoundFileDst = getParamData('alertSoundFileDst');
$alertEnabledDst   = getParamData('alertEnabledDst');
$alertSoundCallDst = getParamData('alertSoundCallDst');

$alertEnabledSrcChecked = $alertEnabledSrc == 1 ? 'checked' : '';
$alertEnabledDstChecked = $alertEnabledDst == 1 ? 'checked' : '';

echo '<h2>Benachrichtigungen Einstellen';
echo '<span class="hintText"><br>(Dateien müssen im Sound-Verzeichnis <span class="lineBreak">vorhanden und ausführbar sein)</span></span>';
echo '</h2>';

echo '<form id="frmConfigAlerting" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table>';

echo '<tr>';
echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';
echo '<td>SoundFile für SRC-CALL :</td>';
echo '<td><input type="text" name="alertSoundFileSrc" id="alertSoundFileSrc" value="' . $alertSoundFileSrc . '" placeholder="Soundfile wav,mp3"  /></td>';
echo '<td><input type="checkbox" name="alertEnabledSrc" ' . $alertEnabledSrcChecked . ' id="alertEnabledSrc" value="1" /></td>';
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
echo '<td><input type="checkbox" name="alertEnabledDst" ' . $alertEnabledDstChecked . ' id="alertEnabledDst" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>DST-Call :</td>';
echo '<td><input type="text" name="alertSoundCallDst" id="alertSoundCallDst" value="' . $alertSoundCallDst . '" placeholder="DST-Call" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="3"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="3"><input type="button" class="btnSaveConfigAlerting" id="btnSaveConfigAlerting" value="Settings speichern"  /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';
echo '<br>';

showAlertMediaFiles();

echo '<div id="pageLoading" class="pageLoadingSub"></div>';
echo '</body>';
echo '</html>';