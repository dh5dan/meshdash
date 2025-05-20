<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Debug-Info</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/debug_info.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_debug_info.php';
require_once '../include/func_php_debug_info.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;

#Check what oS is running
$osIssWindows = chkOsIssWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';

$execDirLog = 'log';
$basename   = pathinfo(getcwd())['basename'];
$logDirSub  = '../' . $execDirLog;
$logDirRoot = $execDirLog;
$logDir     = $basename == 'menu' ? $logDirSub : $logDirRoot;

#Check what oS is running
$osIssWindows            = chkOsIssWindows();
$sendQueueInterval       = getParamData('sendQueueInterval');
$sendQueueMode           = getParamData('sendQueueMode');
$sendQueueMode           = $sendQueueMode == '' || $sendQueueMode == 0 ? getStatusIcon('error') : getStatusIcon('ok');

$checkTaskCmdUdpReceiver = getTaskCmd('udp');
$taskResultUdpReceiver   = shell_exec($checkTaskCmdUdpReceiver); //Prüfe Hintergrundprozess
$statusImageUpdReceiver  = $taskResultUdpReceiver != '' ? getStatusIcon('active') : getStatusIcon('inactive');

$checkTaskCmdCronLoop = getTaskCmd('cron');
$taskResultCronLoop   = shell_exec($checkTaskCmdCronLoop); //Prüfe Hintergrundprozess
$statusCronLoop       = $taskResultCronLoop != '' ? getStatusIcon('active') : getStatusIcon('inactive');

$resExtensionPdoSqlite3  = extension_loaded('pdo_sqlite') == 1 ? getStatusIcon('active') : getStatusIcon('inactive');
$resExtensionSqlite3     = extension_loaded('sqlite3') == 1 ? getStatusIcon('active') : getStatusIcon('inactive');

if ($sendData === '1')
{
    $resSaveSendQueueSettings = saveDebugInfoSettings();

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

#Delete Logfile
if ($sendData === '3')
{
    $deleteLogFile         = trim($_POST['deleteFileImage'] ?? '');
    $deleteLogFileFullPath = $logDir . '/' . $deleteLogFile;

    if (file_exists($deleteLogFileFullPath) && $deleteLogFile != '')
    {
        if(unlink($deleteLogFileFullPath))
        {
            echo '<br><span class="successHint">Logfile ' . $deleteLogFile . ' erfolgreich gelöscht.</span>';
        }
        else
        {
            echo '<br><span class="failureHint">Fehler beim Löschen von Logfile ' . $deleteLogFile . '</span>';
        }
    }
    else
    {
        echo '<br><span class="failureHint">Das Logfile: ' . $deleteLogFile . ' wurde nicht im Log-Verzeichnis gefunden.</span>';
    }
}

echo "<h2>Debug-Info zu MeshDash-SQL</h2>";

echo '<form id="frmDebugInfo" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" name="deleteFileImage" id="deleteFileImage" value="" />';
echo '<table>';

echo '<tr>';
echo '<td>Sendeintervall :</td>';
echo '<td>';
echo  $sendQueueInterval;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Send-Queue enabled:</td>';
echo '<td>';
echo  $sendQueueMode;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Current PHP-Version:</td>';
echo '<td>';
echo  phpversion();
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>PHP-Extension <b>pdo_sqlite</b> loaded:</td>';
echo '<td>';
echo  $resExtensionPdoSqlite3;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>PHP-Extension <b>sqlite3</b> loaded:</td>';
echo '<td>';
echo  $resExtensionSqlite3;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Webserver:</td>';
echo '<td>';
echo  getServerSoftware();
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>System Uptime:</td>';
echo '<td>';
echo gmdate("d",getSystemUptimeSeconds()). 'Tage ' .gmdate("H:i:s",getSystemUptimeSeconds());
echo '</td>';
echo '</tr>';

getLoadAverage();

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Lora-Node GUI-Status:</td>';
echo '<td>';
#Check new GUI
if (checkLoraNewGui(getParamData('loraIp')) === true)
{
    echo "<br> FW >= v4.34x.05.18 mit neuer GUI erkannt";
}
else
{
    echo "<br> FW < v4.34x.05.18 mit alter GUI erkannt";
}
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td>UDP-Receiver BG-Status:</td>';
echo '<td>';
echo  $statusImageUpdReceiver;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>UDP-Receiver BG-Task:</td>';
echo '<td>';
echo  $taskResultUdpReceiver;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>UDP-Receiver BG-Timestamp:</td>';
echo '<td>';
echo  getParamData('udpReceiverTs');
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Cron-Loop BG-Status:</td>';
echo '<td>';
echo  $statusCronLoop;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Cron-Loop BG-Task:</td>';
echo '<td>';
echo  $taskResultCronLoop;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Cron-Loop BG-Timestamp:</td>';
echo '<td>';
echo  getParamData('cronLoopTs');
echo '</td>';
echo '</tr>';



echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

getPhpConfig();

#Sachen die nur unter Linux rennen.
if ($osIssWindows === false)
{
    #Croneinträge anzeigen nur linux
    getCronEntries();
}

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

getWritableStatus();

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

getSqliteDbSizes();

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
    echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

showLogFiles();

//echo '<tr>';
//    echo '<td colspan="2"><input type="button" class="btnSaveDebugInfo" id="btnSaveDebugInfo" value="Settings speichern"  /></td>';
//echo '</tr>';

echo '</table>';
echo '</form>';

echo '</body>';
echo '</html>';