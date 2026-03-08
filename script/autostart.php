<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

#Check what oS is running
$osIssWindows = chkOsIsWindows();

// Pfad zu deinem Webverzeichnis DocRoot
$basePath    = dirname(__DIR__);
$execDir     = 'log';
$errorCode   = '';
$errorMsg    = '';
$infoFile    = "$basePath/$execDir/" . 'info_autostart_' . date('Ymd') . '.log';

$triggerLink = TRIGGER_LINK_AUTOSTART;

#Starte Trigger, wenn LINUX System erkannt
if ($osIssWindows === false)
{
    // --- HIER Trigger-CODE ---
    exec('/usr/bin/wget -q -O /dev/null ' . $triggerLink);

    $infoText = date('Y-m-d H:i:s') . " - Autostart executed with URL: [$triggerLink]" . "\n";
    file_put_contents($infoFile, $infoText,FILE_APPEND);
}

Echo "<br>Autostart ausgeführt";