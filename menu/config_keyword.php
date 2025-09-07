<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;
echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';

echo '<head><title data-i18n="submenu.config_keyword.lbl.title">Keywords-Definition</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
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

echo '<link rel="stylesheet" href="../css/config_keyword.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_config_keyword.php';
require_once '../include/func_php_config_keyword.php';
require_once '../include/func_js_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData       = $_REQUEST['sendData'] ?? 0;
$sendDataUpload = $_REQUEST['sendDataUpload'] ?? 0;
$hardware       = '';
$debugFlag      = false;

#init
$keyHookId             = 0;
$keyHookTrigger        = '';
$keyHookExecute        = '';
$keyHookReturnMsg      = '';
$keyHookDmGrpId        = 999;
$keyHookEnabled        = 0;

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$osName       = $osIssWindows === true ? 'Windows': 'Linux';

$basename      = pathinfo(getcwd())['basename'];
$scriptDirSub  = '../execute/';
$scriptDirRoot = 'execute/';
$scriptDir     = $basename == 'menu' ? $scriptDirSub: $scriptDirRoot;

#Save KeyHooks
if ($sendData === '1')
{
    $resSaveHookSetting = saveHookSettings();

    if ($resSaveHookSetting)
    {
        echo '<span class="successHint">'.date('H:i:s').'-<span data-i18n="submenu.config_keyword.msg.save-settings-success">Settings wurden erfolgreich abgespeichert!</span></span>';
    }
    else
    {
        echo '<span class="failureHint">' . date('H:i:s') . '-<span data-i18n="submenu.config_keyword.msg.save-settings-failed">Es gab einen Fehler beim Abspeichern der Settings!</span></span>';
    }
}

#Delete Hook-Item
if ($sendData === '2')
{
    $deleteHookItemId  = (int) $_REQUEST['deleteHookItemId'];
    $resDeleteHookItem = deleteHookItem($deleteHookItemId);

    if ($resDeleteHookItem === true)
    {
        echo '<br><span class="successHint">Eintrag: ' . $deleteHookItemId . ' erfolgreich gelöscht.</span>';
    }
    else
    {
        echo '<br><span class="failureHint">Fehler beim Löschen von Eintrag: ' . $deleteHookItemId . '</span>';
    }
}

#Delete Soundfile
if ($sendDataUpload === '3')
{
    $deleteFileImage         = trim($_POST['deleteFileImage']);
    $deleteFileImageFullPath = $scriptDir . $deleteFileImage;

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
    // Prüft, ob eine Datei hochgeladen wurde
    if (isset($_FILES['uploadScriptFile']) && $_FILES['uploadScriptFile']['error'] === UPLOAD_ERR_OK)
    {
        if (copy($_FILES['uploadScriptFile']['tmp_name'], $scriptDir . $_FILES['uploadScriptFile']['name']))
        {
            unlink($_FILES['uploadScriptFile']['tmp_name']);

            if ($osIssWindows === false)
            {
                exec('chmod 755 ' . $scriptDir . $_FILES['uploadScriptFile']['name']);
            }

            echo '<span class="successHint">'.date('H:i:s').'-' . $_FILES['uploadScriptFile']['name'] . ' erfolgreich hochgeladen!</span>';
        }
        else
        {
            echo '<span class="failureHint">'.date('H:i:s').'-Fehler beim Hochladen von: ' . $_FILES['uploadScriptFile']['name'] . '!</span>';
        }

        if ($debugFlag === true)
        {
            echo "xxx<pre>";
            print_r($_FILES);
            echo "</pre>";
        }
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

$arrayKeywordHooks = getKeyWordHooks();

if ($arrayKeywordHooks === false)
{
    echo '<span class="failureHint">'.date('H:i:s').'-Fehler beim Abfragen der Datenbank!</span>';
    exit();
}

echo '<h2><span data-i18n="submenu.config_keyword.lbl.title">Keyword-Definition</span></h2>';

echo '<form id="frmConfigKeyword" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" name="deleteHookItemId" id="deleteHookItemId" value="0" />';
echo '<table>';

echo '<tr>';
    echo '<td>OS:</td>';
    echo '<td>'. $osName .'</td>';
echo '</tr>';

if ($hardware != '')
{
    echo '<tr>';
        echo '<td>Hardware:</td>';
        echo '<td>'. $hardware .'</td>';
    echo '</tr>';
}

foreach ($arrayKeywordHooks as $keyHookId => $value)
{
    $keyHookTrigger   = $value['keyHookTrigger'];
    $keyHookExecute   = $value['keyHookExecute'];
    $keyHookReturnMsg = $value['keyHookReturnMsg'];
    $keyHookDmGrpId   = $value['keyHookDmGrpId'];
    $keyHookEnabled   = $value['keyHookEnabled'];

    $keyHookEnabledChecked = $keyHookEnabled == 1 ? 'checked': '';
    $keyword1DmGrpId = $keyHookDmGrpId == '' ? '999': $keyHookDmGrpId;

    echo '<input type="hidden" name="keyHookId[' . $keyHookId . ']" id="keyHookId" value="' . $keyHookId . '" />';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.config_keyword.lbl.keyword">Keyword</span>:</td>';
    echo '<td><input type="text" name="keyHookTrigger[' . $keyHookId . ']" id="keyHookTrigger_' . $keyHookId . '" value="' . $keyHookTrigger . '" placeholder="Keyword"  /></td>';
    echo '<td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.config_keyword.lbl.start-script">Start-Skript</span>:</td>';
    echo '<td><select name="keyHookExecute[' . $keyHookId . ']" id="keyHookExecute_' . $keyHookId . '">';
    selectScriptFile(showKeyScriptFiles(false), $keyHookExecute);
    echo '</select>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.config_keyword.lbl.status-feedback-msg">Statusrückmeldung</span>:</td>';
    echo '<td><input type="text" name="keyHookReturnMsg[' . $keyHookId . ']" id="keyHookReturnMsg_' . $keyHookId . '" value="' . $keyHookReturnMsg . '" placeholder="Return Msg" /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><span data-i18n="submenu.config_keyword.lbl.dm-group-rx-tx">DM-Gruppe: RX/TX</span>:</td>';
    echo '<td><input type="text" name="keyHookDmGrpId[' . $keyHookId . ']" id="keyHookDmGrpId_' . $keyHookId . '" size="8" value="' . $keyHookDmGrpId . '" placeholder="DM-Gruppe" />&nbsp;';
    echo '<input type="checkbox" name="keyHookEnabled[' . $keyHookId . ']" ' . $keyHookEnabledChecked . ' id="keyHookEnabled_' . $keyHookId . '" value="1" />';
    echo '<span data-hook_delete="'
        . $keyHookId
        . '" class="deleteHookItem"/>'
        . html_entity_decode(getStatusIcon("error"))
        . '</span>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2">&nbsp;</td>';
    echo '</tr>';
}

if (count($arrayKeywordHooks) > 0)
{
    ++$keyHookId;

    $keyHookTrigger        = '';
    $keyHookExecute        = '';
    $keyHookReturnMsg      = '';
    $keyHookDmGrpId        = 999;
    $keyHookEnabled        = 0;
    $keyHookEnabledChecked = '';

    echo '<input type="hidden" name="keyHookId[' . $keyHookId . ']" id="keyHookId" value="' . $keyHookId . '"  />';

    echo '<tr class="keyHookNewRow">';
    echo '<td><span data-i18n="submenu.config_keyword.lbl.keyword">Keyword</span>:</td>';
    echo '<td><input type="text" name="keyHookTrigger[' . $keyHookId . ']" id="keyHookTrigger_' . $keyHookId . '" value="' . $keyHookTrigger . '" placeholder="Keyword" disabled /></td>';
    echo '<td></td>';
    echo '</tr>';

    echo '<tr class="keyHookNewRow">';
    echo '<td><span data-i18n="submenu.config_keyword.lbl.start-script">Start-Skript</span>:</td>';
    echo '<td><select name="keyHookExecute[' . $keyHookId . ']" id="keyHookExecute_' . $keyHookId . '" disabled >';
    selectScriptFile(showKeyScriptFiles(false), $keyHookExecute);
    echo '</select>';
    echo '</td>';
    echo '</tr>';

    echo '<tr class="keyHookNewRow">';
    echo '<td><span data-i18n="submenu.config_keyword.lbl.status-feedback-msg">Statusrückmeldung</span>:</td>';
    echo '<td><input type="text" name="keyHookReturnMsg[' . $keyHookId . ']" id="keyHookReturnMsg_' . $keyHookId . '" value="' . $keyHookReturnMsg . '" placeholder="Return Msg" disabled /></td>';
    echo '</tr>';

    echo '<tr class="keyHookNewRow">';
    echo '<td><span data-i18n="submenu.config_keyword.lbl.dm-group-rx-tx">DM-Gruppe: RX/TX</span>:</td>';
    echo '<td><input type="text" name="keyHookDmGrpId[' . $keyHookId . ']" id="keyHookDmGrpId_' . $keyHookId . '" size="8" value="' . $keyHookDmGrpId . '" placeholder="DM-Gruppe" disabled />&nbsp;';
    echo '<input type="checkbox" name="keyHookEnabled[' . $keyHookId . ']" ' . $keyHookEnabledChecked . ' id="keyHookEnabled_' . $keyHookId . '" value="1" disabled />';

    echo '</td>';
    echo '</tr>';

    echo '<tr>';

    echo '<td colspan="3">
        <button type="button" class="btnSaveConfigKeyword" id="btnAddNewHookItem">
            <span data-i18n="submenu.config_keyword.btn.new-item">Neuer Eintrag</span>
        </button>
      </td>';

    echo '</tr>';
}

if (count($arrayKeywordHooks) == 0)
{
    $keyHookId = 1;

    $keyHookTrigger        = '';
    $keyHookExecute        = '';
    $keyHookReturnMsg      = '';
    $keyHookDmGrpId        = 999;
    $keyHookEnabled        = 0;
    $keyHookEnabledChecked = '';

    echo '<input type="hidden" name="keyHookId[' . $keyHookId . ']" id="keyHookId" value="' . $keyHookId . '" />';

    echo '<tr>';
    echo '<td>KeyWord:</td>';
    echo '<td><input type="text" name="keyHookTrigger[' . $keyHookId . ']" id="keyHookTrigger_' . $keyHookId . '" value="' . $keyHookTrigger . '" placeholder="Keyword"  /></td>';
    echo '<td></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Startskript:</td>';
    echo '<td><select name="keyHookExecute[' . $keyHookId . ']" id="keyHookExecute_' . $keyHookId . '"  >';
    selectScriptFile(showKeyScriptFiles(false), $keyHookExecute);
    echo '</select>';
    echo '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Statusrückmeldung:</td>';
    echo '<td><input type="text" name="keyHookReturnMsg[' . $keyHookId . ']" id="keyHookReturnMsg_' . $keyHookId . '" value="' . $keyHookReturnMsg . '" placeholder="Return Msg"  /></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>DM-Gruppe: RX/TX:</td>';
    echo '<td><input type="text" name="keyHookDmGrpId[' . $keyHookId . ']" id="keyHookDmGrpId_' . $keyHookId . '" size="8" value="' . $keyHookDmGrpId . '" placeholder="DM-Gruppe"  />&nbsp;';
    echo '<input type="checkbox" name="keyHookEnabled[' . $keyHookId . ']" ' . $keyHookEnabledChecked . ' id="keyHookEnabled_' . $keyHookId . '" value="1"  />';

    echo '</td>';
    echo '</tr>';
}

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
    echo '<td colspan="3">
        <button type="button" class="btnSaveConfigKeyword" id="btnSaveConfigKeyword">
            <span data-i18n="submenu.config_keyword.btn.save-settings">Settings speichern</span>
        </button>
      </td>';
echo '</tr>';

echo '</table>';
echo '</form>';
echo '<br>';

showKeyScriptFiles();

echo '<div id="pageLoading" class="pageLoadingSub"></div>';

echo '<script>
            $.getJSON("../translation.php?lang=' . $userLang . '", function(dict) {
            applyTranslation(dict); // siehe JS oben
            });
        </script>';

echo '</body>';
echo '</html>';