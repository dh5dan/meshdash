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
$osIssWindows = chkOsIsWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';
$hardware     = '';
$architecture = php_uname('m');

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

    $osRelease = exec('lsb_release -a');
    $osRelease = $osRelease != '' ? ucfirst(trim(explode(':', $osRelease)[1])) : ''; // ohne "Codename:"

    $osName .= ' ' . php_uname('v');
}
else
{
    $osBuild = explode(' ', php_uname('v')); //Build Version
    $osName .= ' ' . (int) php_uname('r') . ' (' . $osBuild[0] . ' ' . $osBuild[1] . ')';
}

$execDirLog = 'log';
$basename   = pathinfo(getcwd())['basename'];
$logDirSub  = '../' . $execDirLog;
$logDirRoot = $execDirLog;
$logDir     = $basename == 'menu' ? $logDirSub : $logDirRoot;

$sendQueueInterval       = getParamData('sendQueueInterval');
$sendQueueInterval       = $sendQueueInterval == '' ? 'nicht gespeichert' : $sendQueueInterval;

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
echo '<td>OS :</td>';
echo '<td>'. $osName .'</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Architektur :</td>';
echo '<td>'. $architecture .'</td>';
echo '</tr>';

if ($osIssWindows === false)
{
    echo '<tr>';
    echo '<td>Release :</td>';
    echo '<td>'. $osRelease .'</td>';
    echo '</tr>';
}

if ($hardware != '')
{
    echo '<tr>';
    echo '<td>Hardware :</td>';
    echo '<td>' . $hardware . '</td>';
    echo '</tr>';
}

echo '<tr>';
echo '<td>Sendeintervall (Sek.) :</td>';
echo '<td>';
echo  $sendQueueInterval;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Send-Queue Status:</td>';
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
if (getParamData('isNewMeshGui') == 1)
{
    echo "<br> FW >= v4.34x.05.18 mit neuer GUI erkannt";
}
else
{
    echo "<br> FW mit alter GUI erkannt";
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