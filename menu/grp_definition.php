<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Gruppendefinition</title>';

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

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_grp_definition.php';
require_once '../include/func_php_grp_definition.php';

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
    $resSaveGroupsSetting = saveGroupsSettings();

    if ($resSaveGroupsSetting)
    {
        echo '<span class="successHint">Settings wurden erfolgreich abgespeichert!</span>';
    }
    else
    {
        echo '<span class="failureHint">Es gab einen Fehler beim Abspeichern der Settings!</span>';
    }
}

$resGetGroupParameter = getGroupParameter();

$groupNumber1Enabled = $resGetGroupParameter[1]['groupEnabled'] ?? 0;
$groupNumber2Enabled = $resGetGroupParameter[2]['groupEnabled'] ?? 0;
$groupNumber3Enabled = $resGetGroupParameter[3]['groupEnabled'] ?? 0;
$groupNumber4Enabled = $resGetGroupParameter[4]['groupEnabled'] ?? 0;
$groupNumber5Enabled = $resGetGroupParameter[5]['groupEnabled'] ?? 0;
$groupNumber6Enabled = $resGetGroupParameter[6]['groupEnabled'] ?? 0; // Notfall gruppe

$groupNumber1EnabledChecked = $groupNumber1Enabled == 1 ? 'checked' : '';
$groupNumber2EnabledChecked = $groupNumber2Enabled == 1 ? 'checked' : '';
$groupNumber3EnabledChecked = $groupNumber3Enabled == 1 ? 'checked' : '';
$groupNumber4EnabledChecked = $groupNumber4Enabled == 1 ? 'checked' : '';
$groupNumber5EnabledChecked = $groupNumber5Enabled == 1 ? 'checked' : '';
$groupNumber6EnabledChecked = $groupNumber6Enabled == 1 ? 'checked' : ''; // Notfall gruppe

$groupNumber1 = $resGetGroupParameter[1]['groupNumber'] ?? 0;
$groupNumber2 = $resGetGroupParameter[2]['groupNumber'] ?? 0;
$groupNumber3 = $resGetGroupParameter[3]['groupNumber'] ?? 0;
$groupNumber4 = $resGetGroupParameter[4]['groupNumber'] ?? 0;
$groupNumber5 = $resGetGroupParameter[5]['groupNumber'] ?? 0;
$groupNumber6 = $resGetGroupParameter[6]['groupNumber'] ?? 0; // Notfall gruppe

echo "<h2>Gruppen-Definition zur Filterung der Nachrichten";
echo '<br><span class="hintText failureHint">Hinweis: Die Änderung wird erst nach einem Reload der Seite sichtbar!</span></h2>';

echo '<form id="frmGrpDefinition" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table>';

echo '<tr>';
echo '<td>Gruppe1 :</td>';
echo '<td><input type="text" name="groupNumber1" id="groupNumber1" value="' . $groupNumber1 . '" placeholder="1-99999"  /></td>';
echo '<td><input type="checkbox" name="groupNumber1Enabled" ' . $groupNumber1EnabledChecked . ' id="groupNumber1Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Gruppe2 :</td>';
echo '<td><input type="text" name="groupNumber2" id="groupNumber2" value="' . $groupNumber2 . '" placeholder="1-99999"  /></td>';
echo '<td><input type="checkbox" name="groupNumber2Enabled" ' . $groupNumber2EnabledChecked . ' id="groupNumber2Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Gruppe3 :</td>';
echo '<td><input type="text" name="groupNumber3" id="groupNumber3" value="' . $groupNumber3 . '" placeholder="1-99999"  /></td>';
echo '<td><input type="checkbox" name="groupNumber3Enabled" ' . $groupNumber3EnabledChecked . ' id="groupNumber3Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Gruppe4 :</td>';
echo '<td><input type="text" name="groupNumber4" id="groupNumber4" value="' . $groupNumber4 . '" placeholder="1-99999"  /></td>';
echo '<td><input type="checkbox" name="groupNumber4Enabled" ' . $groupNumber4EnabledChecked . ' id="groupNumber4Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Gruppe5 :</td>';
echo '<td><input type="text" name="groupNumber5" id="groupNumber5" value="' . $groupNumber5 . '" placeholder="1-99999"  /></td>';
echo '<td><input type="checkbox" name="groupNumber5Enabled" ' . $groupNumber5EnabledChecked . ' id="groupNumber5Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Notfall-Gruppe :</td>';
echo '<td><input type="text" name="groupNumber6" id="groupNumber6" value="' . $groupNumber6 . '" placeholder="1-99999"  /></td>';
echo '<td><input type="checkbox" name="groupNumber6Enabled" ' . $groupNumber6EnabledChecked . ' id="groupNumber6Enabled" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="3"><hr></td>';
echo '</tr>';


echo '<tr>';
echo '<td colspan="2">';
if ($sendData === '1')
{
    echo '<input type="button" class="failureHint" id="btnGrpDefinitionReload" value="MeshDash-Seite neu laden"/>';
}
else
{
    echo '<input type="button" id="btnSaveGrpDefinition" value="Settings speichern" />';
}
echo '</td>';
echo '</tr>';

echo '</table>';
echo '</form>';

echo '</body>';
echo '</html>';