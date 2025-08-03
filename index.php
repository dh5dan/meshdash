<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;

echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';
echo '<head><title>MeshDash-SQL</title>';
  echo '<meta charset="UTF-8">';
  echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
  echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';

  echo '<script type="text/javascript" src="jquery/jquery.min.js"></script>';

  echo '<link rel="stylesheet" href="jquery/jquery-ui-1.13.3/jquery-ui.css">';
  echo '<link rel="stylesheet" href="jquery/css/jq_custom.css">';

  # Achtung das ist V jquery-ui-1.13.3 weil nur die mit dem DateTimePicker Addon funktioniert
  echo '<script type="text/javascript" src="jquery/jquery-ui-1.13.3/jquery-ui.min.js"></script>';

  echo '<script type="text/javascript" src="jquery/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.js"></script>';
  echo '<script type="text/javascript" src="jquery/jquery-ui-timepicker-addon/jquery-ui-sliderAccess.js"></script>';
  echo '<link rel="stylesheet" href="jquery/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.min.css">';

  echo '<link rel="stylesheet" href="css/index.css?' . microtime() . '">';
  echo '<link rel="icon" type="image/png" sizes="16x16" href="favicon.png">';

echo '</head>';
echo '<body>';

#Prevents UTF8 Errors on misconfigured php.ini
ini_set('default_charset', 'UTF-8' );
ini_set('max_execution_time', '300'); // Ausführungszeit auf 5min bei nicht performanten Geräten

require_once 'include/func_php_index.php';
require_once 'include/func_js_index.php';
require_once 'include/func_js_core.php';
require_once 'include/func_php_grp_definition.php';


#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);


$autostartBgProcess = true;
$sendData           = $_REQUEST['sendData'] ?? '0';
$imgTaskRunning     = 'image/punkt_green.png';
$imgTaskStoppedUdp  = 'image/punkt_red.png';
$imgTaskStatusUdp   = $imgTaskRunning;
$doCheckLoraIp      = true;
$taskStatusFlagUdp  = 1;
$debugFlag          = false; // For debug only

#Major/Minor Version ermitteln für PHP.ini Modifikation unter Linux
$phpVersionSplit = explode('.', phpversion());
$phpVersionMajor = $phpVersionSplit[0];
$phpVersionMinor = $phpVersionSplit[1];

if ($phpVersionMajor < 7 || ($phpVersionMajor == 7 && $phpVersionMinor < 4))
{
echo '<br><span class="failureHint">Die benötigte PHP-Version muss mind. PHP 7.4 sein!</span>';
exit();
}

#Wenn Datenbank noch nicht existiert, dann neu initiieren.
#Muss immer zuerst stattfinden!
initDatabases();

$sendQueueEnabled = (int) getParamData('sendQueueMode');

echo '<input type="hidden" id="version" value="' . VERSION . '"/>';
echo '<input type="hidden" id="callSign" value="' . getParamData('callSign') . '"/>';

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

$chkExtension1 = extension_loaded('pdo_sqlite');
$chkExtension2 = extension_loaded('sqlite3');

if ($chkExtension1 === false || $chkExtension2 === false)
{
    $paramExtension['debugFlag']     = $debugFlag;
    $paramExtension['chkExtension1'] = $chkExtension1;
    $paramExtension['chkExtension2'] = $chkExtension2;
    $paramExtension['osIssWindows']  = chkOsIsWindows();

    checkExtension($paramExtension);
}

#Muss ich den UDP-Process beenden?
if ($sendData === '1')
{
    $paramBgProcess['task'] = 'udp';
    stopBgProcess($paramBgProcess);
}
#Lösche alten Linux-Cron Eintrag, wenn vorhanden
deleteOldCron();

#####################################################################################
##########  Top-Bereich
#####################################################################################
#Setzte flag wenn neue MeshCom Gui erkannt wurde
checkLoraNewGui();

// Beispiel-Daten, die du aus der SQLite-Datenbank holen könntest
$tabsJson = getGroupTabsJson();

#Hidden Field damit Jquery die JSON-Daten der Tabs auswerten kann
echo '<input type="hidden" id="tabConfig" value=\'' . $tabsJson . '\' />';

#Starte Automatisch background Prozess
if ($autostartBgProcess === true && $sendData !== '1')
{
    $paramStartUdpBgProcess['task'] = 'udp';
    startBgProcess($paramStartUdpBgProcess);

    #Prüfe ob SendQueue Aktiv ist und starte Cron-loop
    if ($sendQueueEnabled == 1)
    {
        $paramStartCronBgProcess['task'] = 'cron';
        startBgProcess($paramStartCronBgProcess);
    }
}

#Check TaskStatus
$taskResultUdp = file_exists('udp.pid');

#Setzte Bild für UDP-Task gestoppt sonst bleibt er grün
if (empty($taskResultUdp))
{
    $imgTaskStatusUdp  = $imgTaskStoppedUdp;
    $taskStatusFlagUdp = 0;
}

#Setzte Farbe in Tab für neue Nachrichten, wenn gesetzt
setNewMsgBgColor();

#Beginn der Kopfzeile
echo '<div class="top">';
echo '<h1 class="topText">';

#Zeichne Menü
showMenuIcons();

echo '<div class="topLeft">';
echo '<img src="' . $imgTaskStatusUdp . '" id="bgTask" class="topImagePoint" alt="statusColor">';
echo '<span class="dbSearchIcon" id="dbSearch">&#128270;</span>';
echo '</div>';

echo '<span class="topTitle">MeshDash-SQL V ' . VERSION . '</span>';
#Oster-Edition
#echo '<span class="topTitle" >&#128007;&#128007;&#128007; MeshDash-SQL V ' . VERSION.' &#128007;&#128007;&#128007;</span>';

// Neues Div für Uhrzeit, ohne das Layout zu zerstören
echo '<div class="topRight" id="datetime">Hole Zeit!</div>';

echo '</h1>';
echo '</div>';

#Define Sound New-Messages
setNewMsgAudioItems();

// Hier kommen die Tabs
echo '<div id="top-tabs"></div>';

echo '<form id="frmIndex" method="post"  action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" name="taskStatusFlag" id="taskStatusFlag" value="' . $taskStatusFlagUdp . '" />';
echo '</form>';

#Lade Iframes
echo '<iframe id="message-frame" src="message.php"></iframe>';
echo '<iframe id="bottom-frame" src="bottom.php"></iframe>';

echo '<script>
            $.getJSON("translation.php?lang=' . $userLang . '", function(dict) {
            applyTranslation(dict); // siehe JS oben
            });
        </script>';

echo '</body>';
echo '</html>';
