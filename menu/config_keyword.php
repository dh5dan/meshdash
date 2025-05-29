<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Keywords</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/config_keyword.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_config_keyword.php';
require_once '../include/func_php_config_keyword.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;
$hardware = '';

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';

if ($sendData === '1')
{
    $resSaveKeywordSetting = saveKeywordSettings();

    if ($resSaveKeywordSetting)
    {
        echo '<span class="successHint">'.date('H:i:s').'-Settings erfolgreich abgespeichert!</span>';
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

$keyword1Text           = getParamData('keyword1Text');
$keyword1Cmd            = getParamData('keyword1Cmd');
$keyword1Enabled        = getParamData('keyword1Enabled');
$keyword1ReturnMsg      = getParamData('keyword1ReturnMsg');
$keyword1DmGrpId        = getParamData('keyword1DmGrpId');

$keyword2Text           = getParamData('keyword2Text');
$keyword2Cmd            = getParamData('keyword2Cmd');
$keyword2Enabled        = getParamData('keyword2Enabled');
$keyword2ReturnMsg      = getParamData('keyword2ReturnMsg');
$keyword2DmGrpId        = getParamData('keyword2DmGrpId');

$keyword1EnabledChecked = $keyword1Enabled == 1 ? 'checked' : '';
$keyword2EnabledChecked = $keyword2Enabled == 1 ? 'checked' : '';

$keyword1DmGrpId = $keyword1DmGrpId == '' ? '*' : $keyword1DmGrpId;
$keyword2DmGrpId = $keyword2DmGrpId == '' ? '*' : $keyword2DmGrpId;

echo '<h2>Keyword-Definition</span>';
echo '<span class="hintText"><br>(Dateien müssen im Execute-Verzeichnis <span class="lineBreak">vorhanden und ausführbar sein)</span></span>';
echo '</h2>';

echo '<form id="frmConfigKeyword" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
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

echo '<tr>';
echo '<td>KeyWord1 :</td>';
echo '<td><input type="text" name="keyword1Text" id="keyword1Text" value="' . $keyword1Text . '" placeholder="Keyword"  /></td>';
echo '<td><input type="checkbox" name="keyword1Enabled" ' . $keyword1EnabledChecked . ' id="keyword1Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Startskript KeyWord1 :</td>';
echo '<td><input type="text" name="keyword1Cmd" id="keyword1Cmd" value="' . $keyword1Cmd . '" placeholder="Command" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Statusrückmeldung KeyWord1 :</td>';
echo '<td><input type="text" name="keyword1ReturnMsg" id="keyword1ReturnMsg" value="' . $keyword1ReturnMsg . '" placeholder="Return Msg" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>DM-Gruppe: RX/TX KeyWord1 :</td>';
echo '<td><input type="text" name="keyword1DmGrpId" id="keyword1DmGrpId" value="' . $keyword1DmGrpId . '" placeholder="DM-Gruppe" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';
echo '<td>KeyWord2 :</td>';
echo '<td><input type="text" name="keyword2Text" id="keyword2Text" value="' . $keyword2Text . '" placeholder="Keyword" /></td>';
echo '<td><input type="checkbox" name="keyword2Enabled" ' . $keyword2EnabledChecked . ' id="keyword2Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Startskript KeyWord2 :</td>';
echo '<td><input type="text" name="keyword2Cmd" name="keyword2Cmd" value="' . $keyword2Cmd . '" placeholder="Command" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Statusrückmeldung KeyWord2 :</td>';
echo '<td><input type="text" name="keyword2ReturnMsg" id="keyword2ReturnMsg" value="' . $keyword2ReturnMsg . '" placeholder="Return Msg" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>DM-Gruppe: RX/TX KeyWord2 :</td>';
echo '<td><input type="text" name="keyword2DmGrpId" id="keyword2DmGrpId" value="' . $keyword2DmGrpId . '" placeholder="DM-Gruppe" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
    echo '<td colspan="3"><input type="button" class="btnSaveConfigKeyword" id="btnSaveConfigKeyword" value="Settings speichern"  /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';

echo '</body>';
echo '</html>';