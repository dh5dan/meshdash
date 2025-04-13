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
echo '<link rel="stylesheet" href="../css/config_send_queue.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_config_send_queue.php';
require_once '../include/func_php_config_send_queue.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;

#Check what oS is running
$osIssWindows = chkOsIssWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';

$basename         = pathinfo(getcwd())['basename'];
$lockFilenameSub  = '../log/cron_loop.lock';
$lockFilenameRoot = 'log/cron_loop.lock';
$lockFilename     = $basename == 'menu' ? $lockFilenameSub : $lockFilenameRoot;

if ($sendData === '1')
{
    $resSaveSendQueueSettings = saveSendQueueSettings();

    if ($resSaveSendQueueSettings)
    {
        echo '<span class="successHint">'.date('H:i:s').'-Settings wurden erfolgreich abgespeichert!</span>';

        echo "<script>reloadBottomFrame();</script>";
    }
    else
    {
        echo '<span class="failureHint">Es gab einen Fehler beim Abspeichern der Settings!</span>';
    }
}

$sendQueueInterval = getParamData('sendQueueInterval');
$sendQueueInterval = $sendQueueInterval == '' ? 30 : $sendQueueInterval;

echo "<h2>Send-Queue Einstellungen von MeshDash-SQL</h2>";

echo '<form id="frmSendQueue" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table>';

echo '<tr>';
echo '<td>Sendeintervall in Sek.>= 20:</td>';
echo '<td><input type="text" name="sendQueueInterval" id="sendQueueInterval" value="' . $sendQueueInterval . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Send-Cron Status:</td>';

echo '<td>';
if (file_exists($lockFilename))
{
    echo '<img src="../image/punkt_green.png" id="bgTask" class="cronStatusPoint" alt="statusColor">';
}
else
{
    echo '<img src="../image/punkt_red.png" id="bgTask" class="cronStatusPoint" alt="statusColor">';
}
echo '</td>';

echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
    echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';
    echo '<td colspan="2"><input type="button" class="btnSaveConfigGenerally" id="btnSaveSendQueue" value="Settings speichern"  /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';

echo '</body>';
echo '</html>';