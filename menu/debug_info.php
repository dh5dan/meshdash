<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;
echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';
echo '<head><title data-i18n="submenu.debug_info.lbl.title">Debug-Info zu MeshDash-SQL</title>';

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

echo '<link rel="stylesheet" href="../css/debug_info.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../include/func_js_debug_info.php';
require_once '../include/func_php_debug_info.php';
require_once '../include/func_js_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';
$hardware     = '';
$architecture = php_uname('m');
$debugFlag = true;

if ($osIssWindows === true && $debugFlag === true)
{
    // Konvertiere Array in JSON: Alle Windows PHP-Prozesse
    $allPhpProcesses = getWindowsPhpProcesses();
    $jsonAllPhpProcesses = json_encode($allPhpProcesses);
    echo '<input type="hidden" id="allPhpProcessesData" value="'. htmlspecialchars($jsonAllPhpProcesses, ENT_QUOTES).'">';
    echo '<input type="hidden" id="osIsWindows" value="'. ($osIssWindows ? '1' : '0') .'">';
}
elseif ($osIssWindows === false && $debugFlag === true)
{
    // Konvertiere Array in JSON: Alle Windows PHP-Prozesse
    $allPhpProcesses = getPhpProcessesLinux();
    $jsonAllPhpProcesses = json_encode($allPhpProcesses);
    echo '<input type="hidden" id="allPhpProcessesData" value="'. htmlspecialchars($jsonAllPhpProcesses, ENT_QUOTES).'">';
    echo '<input type="hidden" id="osIsWindows" value="'. ($osIssWindows ? '1' : '0') .'">';
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
$pidUdpReceiver          = getPidFromCmd($taskResultUdpReceiver);

$checkTaskCmdCronLoop = getTaskCmd('cron');
$taskResultCronLoop   = shell_exec($checkTaskCmdCronLoop); //Prüfe Hintergrundprozess
$statusCronLoop       = $taskResultCronLoop != '' ? getStatusIcon('active') : getStatusIcon('inactive');
$pidCronLoop          = getPidFromCmd($taskResultCronLoop);

$checkTaskCmdCronBeacon = getTaskCmd('cronBeacon');
$taskResultCronBeacon   = shell_exec($checkTaskCmdCronBeacon); //Prüfe Hintergrundprozess
$statusCronBeacon       = $taskResultCronBeacon != '' ? getStatusIcon('active') : getStatusIcon('inactive');
$pidCronBeacon          = getPidFromCmd($taskResultCronBeacon);

$checkTaskCmdCronMheard = getTaskCmd('cronMheard');
$taskResultCronMheard   = shell_exec($checkTaskCmdCronMheard); //Prüfe Hintergrundprozess
$statusCronMheard       = $taskResultCronMheard != '' ? getStatusIcon('active') : getStatusIcon('inactive');
$pidCronMheard          = getPidFromCmd($taskResultCronMheard);

$checkTaskCmdCronGetSensorData = getTaskCmd('cronGetSensorData');
$taskResultCronGetSensorData   = shell_exec($checkTaskCmdCronGetSensorData); //Prüfe Hintergrundprozess
$statusCronGetSensorData       = $taskResultCronGetSensorData != '' ? getStatusIcon('active') : getStatusIcon('inactive');
$pidCronGetSensorData          = getPidFromCmd($taskResultCronGetSensorData);

$resExtensionPdoSqlite3  = extension_loaded('pdo_sqlite') == 1 ? getStatusIcon('active') : getStatusIcon('inactive');
$resExtensionSqlite3     = extension_loaded('sqlite3') == 1 ? getStatusIcon('active') : getStatusIcon('inactive');

if ($sendData === '2')
{
    releasePurgeLock(trim($_REQUEST['purgeBlockReleaseProcName']));
    echo '<br><span class="successHint">'.date('H:i:s').'-Sperre für Prozess ' . trim($_REQUEST['purgeBlockReleaseProcName']) . ' erfolgreich gelöscht.</span>';
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
            echo '<br><span class="successHint">'.date('H:i:s').'-Logfile ' . $deleteLogFile . ' erfolgreich gelöscht.</span>';
        }
        else
        {
            echo '<br><span class="failureHint">'.date('H:i:s').'-Fehler beim Löschen von Logfile ' . $deleteLogFile . '</span>';
        }
    }
    else
    {
        echo '<br><span class="failureHint">'.date('H:i:s').'-Das Logfile: ' . $deleteLogFile . ' wurde nicht im Log-Verzeichnis gefunden.</span>';
    }
}

echo '<h2><span data-i18n="submenu.debug_info.lbl.title">Debug-Info zu MeshDash-SQL</span></h2>';

echo '<form id="frmDebugInfo" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" name="deleteFileImage" id="deleteFileImage" value="" />';
echo '<input type="hidden" name="purgeBlockReleaseProcName" id="purgeBlockReleaseProcName" value="" />';
echo '<table>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.os">OS</span>:</td>';
echo '<td>'. $osName .'</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.architecture">Architektur</span>:</td>';
echo '<td>'. $architecture .'</td>';
echo '</tr>';

if ($osIssWindows === false)
{
    echo '<tr>';
    echo '<td><span data-i18n="submenu.debug_info.lbl.release">Release</span>:</td>';
    echo '<td>'. $osRelease .'</td>';
    echo '</tr>';
}

if ($hardware != '')
{
    echo '<tr>';
    echo '<td><span data-i18n="submenu.debug_info.lbl.hardware">Hardware</span>:</td>';
    echo '<td>' . $hardware . '</td>';
    echo '</tr>';
}

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.tx-interval">Sendeintervall (Sek.)</span>:</td>';
echo '<td>';
echo  $sendQueueInterval;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.queue-status">Send-Queue Status</span>:</td>';
echo '<td>';
echo  $sendQueueMode;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.php-version">Aktuelle PHP-Version</span>:</td>';
echo '<td>';
echo  phpversion();
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.pdo-sqlite">PHP-Extension <b>pdo_sqlite</b> geladen</span>:</td>';
echo '<td>';
echo  $resExtensionPdoSqlite3;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.sqlite">PHP-Extension <b>sqlite3</b> geladen</span>:</td>';
echo '<td>';
echo  $resExtensionSqlite3;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.webserver">Webserver</span>:</td>';
echo '<td>';
echo  getServerSoftware();
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.uptime">System Uptime</span>:</td>';
echo '<td>';
echo gmdate("d",getSystemUptimeSeconds()). 'Tage ' .gmdate("H:i:s",getSystemUptimeSeconds());
echo '</td>';
echo '</tr>';

getLoadAverage();

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.node-firmware">Lora-Node GUI-Status</span>:</td>';
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
echo '<td><span data-i18n="submenu.debug_info.lbl.udp-bg-status">UDP-Receiver BG-Status</span>:</td>';
echo '<td>';
echo $statusImageUpdReceiver;
if ($pidUdpReceiver !== false)
{
    echo '<img src="..\image\info_blau.png" class="process-indicator" data-pid="' . $pidUdpReceiver . '">';
}
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.udp-bg-task">UDP-Receiver BG-Task</span>:</td>';
echo '<td>';
echo  $taskResultUdpReceiver;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.udp-bg-timestamp">UDP-Receiver BG-Timestamp</span>:</td>';
echo '<td>';
echo  getParamData('udpReceiverTs');
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.cron-bg-status">Cron-Loop BG-Status</span>:</td>';
echo '<td>';
echo  $statusCronLoop;
if ($pidCronLoop !== false)
{
    echo '<img src="..\image\info_blau.png" class="process-indicator" data-pid="' . $pidCronLoop . '">';
}
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.cron-bg-task">Cron-Loop BG-Task</span>:</td>';
echo '<td>';
echo  $taskResultCronLoop;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.cron-bg-timestamp">Cron-Loop BG-Timestamp</span>:</td>';
echo '<td>';
echo  getParamData('cronLoopTs') ?? '0000-00-00 00:00';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.cron-beacon-bg-status">Cron-Beacon BG-Status</span>:</td>';
echo '<td>';
echo  $statusCronBeacon;
if ($pidCronBeacon !== false)
{
    echo '<img src="..\image\info_blau.png" class="process-indicator" data-pid="' . $pidCronBeacon . '">';
}
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.cron-beacon-bg-task">Cron-Beacon BG-Task</span>:</td>';
echo '<td>';
echo  $taskResultCronBeacon;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.cron-beacon-bg-timestamp">Cron-Beacon BG-Timestamp</span>:</td>';
echo '<td>';
echo  getParamData('cronLoopBeaconTs') ?? '0000-00-00 00:00';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.cron-mheard-bg-status">Cron-Mheard BG-Status</span>:</td>';
echo '<td>';
echo  $statusCronMheard;
if ($pidCronMheard !== false)
{
    echo '<img src="..\image\info_blau.png" class="process-indicator" data-pid="' . $pidCronMheard . '">';
}
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.cron-mheard-bg-task">Cron-Mheard BG-Task</span>:</td>';
echo '<td>';
echo  $taskResultCronMheard;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.cron-mheard-bg-timestamp">Cron-Mheard BG-Timestamp</span>:</td>';
echo '<td>';
echo  getParamData('cronLoopMheardPidTs') ?? '0000-00-00 00:00';
echo '</td>';
echo '</tr>';

#
echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.cron-mheard-bg-status">Cron-SensorData BG-Status</span>:</td>';
echo '<td>';
echo  $statusCronGetSensorData;
if ($pidCronGetSensorData !== false)
{
    echo '<img src="..\image\info_blau.png" class="process-indicator" data-pid="' . $pidCronGetSensorData . '">';
}
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.cron-mheard-bg-task">Cron-SensorData BG-Task</span>:</td>';
echo '<td>';
echo  $taskResultCronGetSensorData;
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.debug_info.lbl.cron-mheard-bg-timestamp">Cron-SensorData BG-Timestamp</span>:</td>';
echo '<td>';
echo  getParamData('cronLoopGetSensorDataPidTs') ?? '0000-00-00 00:00';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2">';
getWriteMutex();
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

echo '</table>';
echo '</form>';

echo '<script>
            $.getJSON("../translation.php?lang=' . $userLang . '", function(dict) {
            applyTranslation(dict); // siehe JS oben
            });
        </script>';

echo '</body>';
echo '</html>';