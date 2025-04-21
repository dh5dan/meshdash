<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>MeshDash-SQL</title>';
  echo '<meta charset="UTF-8">';
  echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
  echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';

  echo '<script type="text/javascript" src="jquery/jquery.min.js"></script>';
  echo '<script type="text/javascript" src="jquery/jquery-ui.js"></script>';
  echo '<link rel="stylesheet" href="jquery/jquery-ui.css">';
  echo '<link rel="stylesheet" href="jquery/css/jq_custom.css">';
  echo '<link rel="stylesheet" href="css/index.css?' . microtime() . '">';
  echo '<link rel="icon" type="image/png" sizes="16x16" href="favicon.png">';

echo '</head>';
echo '<body>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

require_once 'dbinc/param.php';
require_once 'include/func_php_index.php';
require_once 'include/func_js_index.php';
require_once 'include/func_php_core.php';
require_once 'include/func_php_grp_definition.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$autostartBgProcess = true;
$sendData           = $_REQUEST['sendData'] ?? 0;
$sendDataCheck      = $_REQUEST['sendDataCheck'] ?? 0;
$imgTaskRunning     = 'image/punkt_green.png';
$imgTaskStoppedUdp  = 'image/punkt_red.png';
$imgTaskStatusUdp   = $imgTaskRunning;
$doCheckLoraIp      = true;
$taskStatusFlagUdp  = 1;
$debugFlag          = false; // For debug only

#Check what oS is running
$osIssWindows     = chkOsIssWindows();
$sendQueueEnabled = (int) getParamData('sendQueueMode');

#Hole Task Command abhängig vom OS
$checkTaskCmd = getTaskCmd();
echo '<input type="hidden" id="version" value="' . VERSION . '"/>';

#Prüfen, ob schreibrechte für Datenbank und Log existieren
if (!is_writable('database') || !is_writable('log') || !is_writable('execute') || !is_writable('sound'))
{
    echo '<span class="unsetDisplayFlex">';
    echo '<br>';

    echo '<br><b>Sie besitzen nicht die nötigen Schreibrechte für das Verzeichnis:';
    echo '<br> database, execute, sound und/oder log!';
    echo '<br>Bitte die Installation/Update mit SUDO SU ausführen.</b>';
    echo '</span>';

    exit();
}

#Wenn Datenbank noch nicht existiert dann neu initiieren
initDatabases();

#Setzte Leere LoraIp neu in param.php
if ($sendData === '11')
{
    initSetBaseParam();
}

#Prüfe ob Lora Ip gesetzt wurde in param.php
if ($doCheckLoraIp === true)
{
    echo '<span class="unsetDisplayFlex">';

    $param['debugFlag'] = $debugFlag;
    $doCheckLoraIp      = checkBaseParam($param);

    echo '</span>';
}

#Major/Minor Version ermitteln für PHP.ini Modifikation unter Linux
$phpVersionSplit = explode('.', phpversion());
$phpVersionMajor = $phpVersionSplit[0];
$phpVersionMinor = $phpVersionSplit[1];

$chkExtension1 = extension_loaded('pdo_sqlite');
$chkExtension2 = extension_loaded('sqlite3');

if ($chkExtension1 === false || $chkExtension2 === false)
{
    $paramExtension['debugFlag']     = $debugFlag;
    $paramExtension['chkExtension1'] = $chkExtension1;
    $paramExtension['chkExtension2'] = $chkExtension2;
    $paramExtension['osIssWindows '] = $osIssWindows ;

    checkExtension($paramExtension);
}

#Muss ich den UDP-Process beenden?
if ($sendData == 1)
{
    $paramBgProcess['task'] = 'udp';
    stopBgProcess($paramBgProcess);
}
#Lösche alten Linux-Cron Eintrag, wenn vorhanden
deleteOldCron();

######################################################################################
##########  Top bereich
#####################################################################################

// Beispiel-Daten, die du aus der SQLite-Datenbank holen könntest
$tabsJson = getGroupTabsJson();

echo '<input type="hidden" id="tabConfig" value=\'' . $tabsJson . '\' />';

#Check TaskStatus
$taskResultUdp = shell_exec($checkTaskCmd);

if ($autostartBgProcess === true && $sendData != 1)
{
    $paramStartUdpBgProcess['task'] = 'udp';
    $taskResultUdp                  = startBgProcess($paramStartUdpBgProcess);

    #Prüfe ob SendQueue Aktiv ist und starte Cron-loop
    if ($sendQueueEnabled == 1)
    {
        $paramStartCronBgProcess['task'] = 'cron';
        $taskResultCron                  = startBgProcess($paramStartCronBgProcess);
    }
}

#Setzte Bild für UDP-Task gestoppt sonst bleibt er grün
if (empty($taskResultUdp))
{
    $imgTaskStatusUdp  = $imgTaskStoppedUdp;
    $taskStatusFlagUdp = 0;
}

echo '<div class="top">';
echo '<h1 class="topText">';

#showMenu();
showMenuIcons();

echo '<div class="topLeft">';
echo '<img src="' . $imgTaskStatusUdp . '" id="bgTask" class="topImagePoint" alt="statusColor">';
echo '</div>';

#echo '<span class="topTitle" >MeshDash-SQL V ' . VERSION.'</span>';
echo '<span class="topTitle" >&#128007;&#128007;&#128007; MeshDash-SQL V ' . VERSION.' &#128007;&#128007;&#128007;</span>';

// Neues Div für Uhrzeit, ohne das Layout zu zerstören
echo '<div class="topRight" id="datetime">Hole Zeit!</div>';

echo '</h1>';
echo '</div>';

// Hier kommen die Tabs
echo '<div id="top-tabs"></div>';

echo '<form id="frmIndex" method="post"  action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" name="taskStatusFlag" id="taskStatusFlag" value="'.$taskStatusFlagUdp.'" />';
echo '</form>';

#Lade Iframes
echo '<iframe id="message-frame" src="message.php"></iframe>';
echo '<iframe id="bottom-frame" src="bottom.php"></iframe>';

echo '</body>';
echo '</html>';
