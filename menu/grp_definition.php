<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;

echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';
echo '<head><title data-i18n="submenu.grp_definition.lbl.title">Gruppendefinition</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/grp_definition.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../include/func_js_grp_definition.php';
require_once '../include/func_php_grp_definition.php';
require_once '../include/func_php_config_alerting.php';
require_once '../include/func_js_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData       = $_REQUEST['sendData'] ?? 0;
$sendDataUpload = $_REQUEST['sendDataUpload'] ?? 0;

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';

$callSign = getParamData('callSign');
$ownCall  = explode('-', $callSign)[0];

$basename     = pathinfo(getcwd())['basename'];
$soundDirSub  = '../sound/';
$soundDirRoot = 'sound/';
$soundDir     = $basename == 'menu' ? $soundDirSub: $soundDirRoot;

if ($sendData === '1')
{
    $resSaveGroupsSetting = saveGroupsSettings();

    if ($resSaveGroupsSetting)
    {
        echo '<span class="successHint">' . date('H:i:s') . '-Settings erfolgreich abgespeichert!</span>';
    }
    else
    {
        echo '<span class="failureHint">' . date('H:i:s') . '-Es gab einen Fehler beim Abspeichern der Settings!</span>';
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
            echo '<br><span class="successHint">' . date('H:i:s') . '-' . $deleteFileImage . ' erfolgreich gelöscht.</span>';
        }
        else
        {
            echo '<br><span class="failureHint">' . date('H:i:s') . '-Fehler beim Löschen von ' . $deleteFileImage . '</span>';
        }
    }
    else
    {
        echo '<br><span class="failureHint">' . date('H:i:s') . '-' . $deleteFileImage . ' nicht im Sound-Verzeichnis gefunden.</span>';
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

$resGetGroupParameter = getGroupParameter();

$groupNumber1Enabled = $resGetGroupParameter[1]['groupEnabled'] ?? 0;
$groupNumber2Enabled = $resGetGroupParameter[2]['groupEnabled'] ?? 0;
$groupNumber3Enabled = $resGetGroupParameter[3]['groupEnabled'] ?? 0;
$groupNumber4Enabled = $resGetGroupParameter[4]['groupEnabled'] ?? 0;
$groupNumber5Enabled = $resGetGroupParameter[5]['groupEnabled'] ?? 0;
$groupNumber6Enabled = $resGetGroupParameter[6]['groupEnabled'] ?? 0; // Notfall gruppe

$groupPosEnabled = $resGetGroupParameter[-3]['groupEnabled'] ?? 0; // Pos gruppe
$groupCetEnabled = $resGetGroupParameter[-4]['groupEnabled'] ?? 0; // Cet gruppe

$groupNumber1EnabledChecked = $groupNumber1Enabled == 1 ? 'checked' : '';
$groupNumber2EnabledChecked = $groupNumber2Enabled == 1 ? 'checked' : '';
$groupNumber3EnabledChecked = $groupNumber3Enabled == 1 ? 'checked' : '';
$groupNumber4EnabledChecked = $groupNumber4Enabled == 1 ? 'checked' : '';
$groupNumber5EnabledChecked = $groupNumber5Enabled == 1 ? 'checked' : '';
$groupNumber6EnabledChecked = $groupNumber6Enabled == 1 ? 'checked' : ''; // Notfall gruppe
$groupPosEnabledChecked = $groupPosEnabled == 1 ? 'checked' : '';
$groupCetEnabledChecked = $groupCetEnabled == 1 ? 'checked' : '';

$groupSound1Enabled        = $resGetGroupParameter[1]['groupSound'] ?? 0;
$groupSound2Enabled        = $resGetGroupParameter[2]['groupSound'] ?? 0;
$groupSound3Enabled        = $resGetGroupParameter[3]['groupSound'] ?? 0;
$groupSound4Enabled        = $resGetGroupParameter[4]['groupSound'] ?? 0;
$groupSound5Enabled        = $resGetGroupParameter[5]['groupSound'] ?? 0;
$groupSound6Enabled        = $resGetGroupParameter[6]['groupSound'] ?? 0; // Notfall-Gruppe
$groupSoundNoFilterEnabled = $resGetGroupParameter[-1]['groupSound'] ?? 0; // Kein Filter
$groupSoundOwnCallEnabled  = $resGetGroupParameter[-2]['groupSound'] ?? 0; // Own Call
$groupSoundPosEnabled      = $resGetGroupParameter[-3]['groupSound'] ?? 0; // Kein Filter
$groupSoundCetEnabled      = $resGetGroupParameter[-4]['groupSound'] ?? 0; // Own Call

$groupSound1EnabledChecked        = $groupSound1Enabled == 1 ? 'checked' : '';
$groupSound2EnabledChecked        = $groupSound2Enabled == 1 ? 'checked' : '';
$groupSound3EnabledChecked        = $groupSound3Enabled == 1 ? 'checked' : '';
$groupSound4EnabledChecked        = $groupSound4Enabled == 1 ? 'checked' : '';
$groupSound5EnabledChecked        = $groupSound5Enabled == 1 ? 'checked' : '';
$groupSound6EnabledChecked        = $groupSound6Enabled == 1 ? 'checked' : ''; // Notfall-Gruppe
$groupSoundOwnCallEnabledChecked  = $groupSoundOwnCallEnabled == 1 ? 'checked' : ''; // Own Call
$groupSoundNoFilterEnabledChecked = $groupSoundNoFilterEnabled == 1 ? 'checked' : ''; // Kein Filter
$groupSoundPosEnabledChecked  = $groupSoundPosEnabled == 1 ? 'checked' : ''; // POS Call
$groupSoundCetEnabledChecked = $groupSoundCetEnabled == 1 ? 'checked' : ''; // CET Filter

$groupNumber1 = $resGetGroupParameter[1]['groupNumber'] ?? 0;
$groupNumber2 = $resGetGroupParameter[2]['groupNumber'] ?? 0;
$groupNumber3 = $resGetGroupParameter[3]['groupNumber'] ?? 0;
$groupNumber4 = $resGetGroupParameter[4]['groupNumber'] ?? 0;
$groupNumber5 = $resGetGroupParameter[5]['groupNumber'] ?? 0;
$groupNumber6 = $resGetGroupParameter[6]['groupNumber'] ?? 0; // Notfall gruppe

$groupSoundFile = getParamData('groupSoundFile');
$groupSoundFile = $groupSoundFile == '' ? 'new_message.wav' : $groupSoundFile;

$msgExportGroup    = getParamData('msgExportGroup') ?? '';
$msgExportEnable   = getParamData('msgExportEnable') ?? 0;
$msgExportEnableChecked   = $msgExportEnable == 1 ? 'checked' : '';

echo '<h2><span data-i18n="submenu.grp_definition.lbl.title">Gruppendefinition</span>';
echo '<br><span class="hintText failureHint"><span data-i18n="submenu.grp_definition.lbl.sub-title">Hinweis: Reload nötig für Anzeige!</span></span></h2>';

echo '<form id="frmGrpDefinition" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table>';

echo '<tr>';
echo '<td>&nbsp</td>';
echo '<td>&nbsp;</td>';
echo '<td>&#x2714;&#65039;/&#x274C;</td>';
echo '<td>&#x1F50A;/&#x1F507;</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.grp_definition.lbl.group">Gruppe</span> 1:</td>';
echo '<td><input type="text" class="groupIdField" name="groupNumber1" id="groupNumber1" value="' . $groupNumber1 . '" placeholder="1-99999"  /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupNumber1Enabled" ' . $groupNumber1EnabledChecked . ' id="groupNumber1Enabled" value="1" /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupSound1Enabled" ' . $groupSound1EnabledChecked . ' id="groupSound1Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.grp_definition.lbl.group">Gruppe</span> 2:</td>';
echo '<td class=""><input type="text" class="groupIdField" name="groupNumber2" id="groupNumber2" value="' . $groupNumber2 . '" placeholder="1-99999"  /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupNumber2Enabled" ' . $groupNumber2EnabledChecked . ' id="groupNumber2Enabled" value="1" /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupSound2Enabled" ' . $groupSound2EnabledChecked . ' id="groupSound2Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.grp_definition.lbl.group">Gruppe</span> 3:</td>';
echo '<td class=""><input type="text" class="groupIdField" name="groupNumber3" id="groupNumber3" value="' . $groupNumber3 . '" placeholder="1-99999"  /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupNumber3Enabled" ' . $groupNumber3EnabledChecked . ' id="groupNumber3Enabled" value="1" /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupSound3Enabled" ' . $groupSound3EnabledChecked . ' id="groupSound3Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.grp_definition.lbl.group">Gruppe</span> 4:</td>';
echo '<td class=""><input type="text" class="groupIdField" name="groupNumber4" id="groupNumber4" value="' . $groupNumber4 . '" placeholder="1-99999"  /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupNumber4Enabled" ' . $groupNumber4EnabledChecked . ' id="groupNumber4Enabled" value="1" /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupSound4Enabled" ' . $groupSound4EnabledChecked . ' id="groupSound4Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.grp_definition.lbl.group">Gruppe</span> 5:</td>';
echo '<td class=""><input type="text" class="groupIdField" name="groupNumber5" id="groupNumber5" value="' . $groupNumber5 . '" placeholder="1-99999"  /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupNumber5Enabled" ' . $groupNumber5EnabledChecked . ' id="groupNumber5Enabled" value="1" /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupSound5Enabled" ' . $groupSound5EnabledChecked . ' id="groupSound5Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.grp_definition.lbl.grp-own-call">Own-Call</span>:</td>';
echo '<td class="">&nbsp;</td>';
echo '<td class="">&nbsp;</td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupSoundOwnCallEnabled" ' . $groupSoundOwnCallEnabledChecked . ' id="groupSoundOwnCallEnabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.grp_definition.lbl.grp-no-filter">Kein Filter</span>:</td>';
echo '<td class="">&nbsp;</td>';
echo '<td class="">&nbsp;</td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupSoundNoFilterEnabled" ' . $groupSoundNoFilterEnabledChecked . ' id="groupSoundNoFilterEnabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="5">&nbsp;</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.grp_definition.lbl.grp-pos-filter">POS-Filter</span>:</td>';
echo '<td class="">&nbsp;</td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupPosEnabled" ' . $groupPosEnabledChecked . ' id="groupPosEnabled" value="1" /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupSoundPosEnabled" ' . $groupSoundPosEnabledChecked . ' id="groupSoundPosEnabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.grp_definition.lbl.grp-cet-filter">CET-Filter</span>:</td>';
echo '<td class="">&nbsp;</td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupCetEnabled" ' . $groupCetEnabledChecked . ' id="groupCetEnabled" value="1" /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupSoundCetEnabled" ' . $groupSoundCetEnabledChecked . ' id="groupSoundCetEnabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="5">&nbsp;</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.grp_definition.lbl.emergency-group">Notfall-Gruppe</span>:</td>';
echo '<td class="tdCenter"><input type="text" class="groupIdField" name="groupNumber6" id="groupNumber6" value="' . $groupNumber6 . '" placeholder="1-99999"  /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupNumber6Enabled" ' . $groupNumber6EnabledChecked . ' id="groupNumber6Enabled" value="1" /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="groupSound6Enabled" ' . $groupSound6EnabledChecked . ' id="groupSound6Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.grp_definition.lbl.html-export-group">HTML-Export Gruppe</span>:</td>';
echo '<td class="tdCenter"><input type="text" name="msgExportGroup" id="msgExportGroup" class="groupExportField" value="' . $msgExportGroup . '" /></td>';
echo '<td class="tdCenter"><input type="checkbox" name="msgExportEnable" ' . $msgExportEnableChecked . ' id="msgExportEnable" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="3" class="tdCenter smallHintText">* = all, Own-Call = ' . $ownCall . '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="5"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.grp_definition.lbl.media-file">Sound-Datei</span>:</td>';

echo '<td colspan="5">';
echo '<select name="groupSoundFile" id="groupSoundFile">';
selectSoundFile(showAlertMediaFiles(false), $groupSoundFile);
echo '</select>';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="5"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="4">';
if ($sendData === '1')
{
    echo '<button type="button" class="failureHint" id="btnGrpDefinitionReload">
            <span data-i18n="submenu.grp_definition.btn.reload">MeshDash-Seite neu laden</span>
          </button>
         ';
}
else
{
    echo '<button type="button" class="btnSaveGrpDefinition" id="btnSaveGrpDefinition">
            <span data-i18n="submenu.grp_definition.btn.save-settings">Settings speichern</span>
          </button>
         ';
}
echo '</td>';
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