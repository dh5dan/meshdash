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

// Überprüfen, ob der Prozess bereits läuft

$autostartBgProcess = true;
$sendData           = $_REQUEST['sendData'] ?? 0;
$sendDataCheck      = $_REQUEST['sendDataCheck'] ?? 0;
$imgTaskRunning     = 'image/punkt_green.png';
$imgTaskStopped     = 'image/punkt_red.png';
$imgTaskStatus      = $imgTaskRunning;
$doCheckLoraIp      = true;
$taskStatusFlag     = 1;
$debugFlag          = false; // For debug only

#Check what oS is running
$osIssWindows = chkOsIssWindows();

#Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
$checkTaskCmd = $osIssWindows === true ? 'tasklist | find "php.exe"' : "pgrep -a -f udp_receiver.php | grep -v pgrep | awk '{print $1}'";
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

    if ($debugFlag === true)
    {
        $errorText = date('Y-m-d H:i:s') . ' Keine Schreibrechte! Exit().' . "\n";
        file_put_contents('log/debug.log', $errorText, FILE_APPEND);
    }

    exit();
}

if ($debugFlag === true)
{
    $errorText = date('Y-m-d H:i:s') . ' Version MeshDash' . VERSION . "\n";
    file_put_contents('log/debug.log', $errorText, FILE_APPEND);

    if ($sendData != 0)
    {
        $errorText = date('Y-m-d H:i:s') . ' SendData: ' . $sendData . "\n";
        file_put_contents('log/debug.log', $errorText, FILE_APPEND);
    }
}

#Wenn Datenbank noch nicht existiert dann neu initiieren
if (!file_exists('database/meshdash.db'))
{
    initSQLiteDatabase('meshdash');
}

if (!file_exists('database/parameter.db'))
{
    initSQLiteDatabase('parameter');
}

if (!file_exists('database/keywords.db'))
{
    initSQLiteDatabase('keywords');
}

if (!file_exists('database/mheard.db'))
{
    initSQLiteDatabase('mheard');
}

if (!file_exists('database/groups.db'))
{
    initSQLiteDatabase('groups');
}

#Setzte Leere LoraIp neu in param.php
if ($sendData === '11')
{
    setLoraIpDb();
}

#Prüfe ob Lora Ip gesetzt wurde in param.php
if ($doCheckLoraIp === true)
{
    echo '<span class="unsetDisplayFlex">';
    echo "<br>";

    $param['debugFlag'] = $debugFlag;
    $doCheckLoraIp      = checkLoraIPDb($param);

    echo '</span>';
}

if ($debugFlag === true)
{
    $errorText = date('Y-m-d H:i:s') . ' Os ist Linux' . "\n";
    if ($osIssWindows === true)
    {
        $errorText = date('Y-m-d H:i:s') . ' Os ist Windows' . "\n";
    }

    file_put_contents('log/debug.log', $errorText, FILE_APPEND);
}

#Major/Minor Version ermitteln für PHP.ini Modifikation unter Linux
$phpVersionSplit = explode('.', phpversion());
$phpVersionMajor = $phpVersionSplit[0];
$phpVersionMinor = $phpVersionSplit[1];

if ($debugFlag === true)
{
    $errorText = date('Y-m-d H:i:s') . ' PHP Version :' . phpversion() . "\n";
    file_put_contents('log/debug.log', $errorText, FILE_APPEND);
}

$chkExtension1 = extension_loaded('pdo_sqlite');
$chkExtension2 = extension_loaded('sqlite3');

if ($debugFlag === true)
{
    $errorText = date('Y-m-d H:i:s') . ' ' . phpversion() . "\n";

    if ($chkExtension1 === false)
    {
        $errorText = date('Y-m-d H:i:s') . ' chkExtension1 = false. (pdo_sqlite)' . "\n";
        file_put_contents('log/debug.log', $errorText, FILE_APPEND);
    }
    else
    {
        $errorText = date('Y-m-d H:i:s') . ' chkExtension1 = true. (pdo_sqlite)' . "\n";
        file_put_contents('log/debug.log', $errorText, FILE_APPEND);
    }

    if ($chkExtension2 === false)
    {
        $errorText = date('Y-m-d H:i:s') . ' chkExtension2 = false. (sqlite3)' . "\n";
        file_put_contents('log/debug.log', $errorText, FILE_APPEND);
    }
    else
    {
        $errorText = date('Y-m-d H:i:s') . ' chkExtension2 = true. (sqlite3)' . "\n";
        file_put_contents('log/debug.log', $errorText, FILE_APPEND);
    }
}

if ($chkExtension1 === false || $chkExtension2 === false)
{
    $paramExtension['debugFlag']     = $debugFlag;
    $paramExtension['chkExtension1'] = $chkExtension1;
    $paramExtension['chkExtension2'] = $chkExtension2;
    $paramExtension['osIssWindows '] = $osIssWindows ;

    checkExtension($paramExtension);
}

if ($debugFlag === true)
{
    $errorText = date('Y-m-d H:i:s') . ' MeshdasH DB found database/meshdash.db' . "\n";

    if (!file_exists('database/meshdash.db'))
    {
        $errorText = date('Y-m-d H:i:s') . ' MeshdasH DB NOT found database/meshdash.db' . "\n";
    }

    file_put_contents('log/debug.log', $errorText, FILE_APPEND);

    $errorText = date('Y-m-d H:i:s') . ' MeshdasH DB found database/parameter.db' . "\n";

    if (!file_exists('database/parameter.db'))
    {
        $errorText = date('Y-m-d H:i:s') . ' MeshdasH DB NOT found database/parameter.db' . "\n";
    }

    file_put_contents('log/debug.log', $errorText, FILE_APPEND);
}

#Muss ich den Process beenden?
if ($sendData == 1)
{
    $paramBgProcess['checkTaskCmd'] = $checkTaskCmd;
    $paramBgProcess['osIssWindows'] = $osIssWindows;
    checkBgProcess($paramBgProcess);
}

######################################################################################
##########  Top bereich
#####################################################################################

// Beispiel-Daten, die du aus der SQLite-Datenbank holen könntest
$tabsJson = getGroupTabsJson();

echo '<input type="hidden" id="tabConfig" value=\'' . $tabsJson . '\' />';


#Check TaskStatus
$taskResult = shell_exec($checkTaskCmd);

if ($autostartBgProcess === true && $sendData != 1)
{
    $paramStartBgProcess['taskResult']   = $taskResult;
    $paramStartBgProcess['osIssWindows'] = $osIssWindows;
    $paramStartBgProcess['checkTaskCmd'] = $checkTaskCmd;
    $taskResult                          = startBgProcess($paramStartBgProcess);
}

#Setzte Bild für Task gestoppt sonst bleibt er grün
if (empty($taskResult))
{
    $imgTaskStatus  = $imgTaskStopped;
    $taskStatusFlag = 0;
}

if ($debugFlag === true)
{
    if ($debugFlag === true)
    {
        $errorText = date('Y-m-d H:i:s') . ' TaskResult:' . $taskResult . "\n";
        file_put_contents('log/debug.log', $errorText, FILE_APPEND);
    }
}

echo '<div class="top">';
echo '<h1 class="topText">';

showMenu();

echo '<div class="topLeft">';
echo '<img src="' . $imgTaskStatus . '" id="bgTask" class="topImagePoint" alt="statusColor">';
echo '</div>';

echo '<span class="topTitle" >MeshDash-SQL V ' . VERSION.'</span>';

// Neues Div für Uhrzeit, ohne das Layout zu zerstören
echo '<div class="topRight" id="datetime">Hole Zeit!</div>';

echo '</h1>';
echo '</div>';

// Hier kommen die Tabs
echo '<div id="top-tabs"></div>';

echo '<form id="frmIndex" method="post"  action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" name="taskStatusFlag" id="taskStatusFlag" value="'.$taskStatusFlag.'" />';
echo '</form>';

#Lade Iframes
echo '<iframe id="message-frame" src="message.php"></iframe>';
echo '<iframe id="bottom-frame" src="bottom.php"></iframe>';

echo '</body>';
echo '</html>';
