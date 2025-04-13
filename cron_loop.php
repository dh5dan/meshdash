<?php
require_once 'include/func_php_core.php';
ignore_user_abort(true);
set_time_limit(0);

// Relativer Pfad zu deinem Webverzeichnis
$basePath   = __DIR__;
$execDir    = 'log';
$lockFile   = "$basePath/$execDir/cron_loop.lock";
$configFile = "$basePath/$execDir/cron_interval.conf";
$stopFile   = "$basePath/$execDir/cron_stop"; // Stop-Datei
$debugFile  = "log/cron_loop_debug_log.txt";
$debugTime  = date('Y-m-d H:i:s');

$actualHost  = (empty($_SERVER['HTTPS']) ? 'http' : 'https');
$triggerLink = $actualHost . '://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER["REQUEST_URI"] . '?') . '/' . 'send_queue.php';

#Check what oS is running
$osIssWindows = chkOsIssWindows();
$debugFlag    = true;

#Prüfe, ob Verzeichnis existiert und setzte es ggf. neu
if (!is_dir("$basePath/$execDir") && $osIssWindows === false)
{
    mkdir("$basePath/$execDir", 0775, true);
    chown("$basePath/$execDir", "www-data");  // Besitzer setzen
    chgrp("$basePath/$execDir", "www-data");  // Gruppenbesitz setzen
}

$storedRebootTimestamp  = getParamData('lastRebootTimestamp');
$cron_loop_pid          = getParamData('cronLoopPid');
#$currentRebootTimestamp = getSystemRebootTimestamp();
$checkRebootFlag        = false; // Prüfe auf Reboot

//#Wenn Uptime noch nie gesetzt wurde einmalig setzen
//if ($storedRebootTimestamp == '' && $cron_loop_pid != '' && $storedRebootTimestamp !== false && $checkRebootFlag === true)
//{
//    setParamData('lastRebootTimestamp', $currentRebootTimestamp, 'txt');
//
//    if($debugFlag === true)
//    {
//        file_put_contents($debugFile, $debugTime . " - lastRebootTimestamp war leer. Neu gesetzt\n", FILE_APPEND);
//    }
//}

//#Prüfe, ob System neu gestartet wurde
//if ($currentRebootTimestamp !== false  && $storedRebootTimestamp != 0 && $storedRebootTimestamp != '' && $currentRebootTimestamp != $storedRebootTimestamp && $checkRebootFlag === true)
//{
//    // Uptime kleiner als gespeicherte. Das System wurde neu gestartet.
//    echo "System reboot erkannt – PID ist ungültig";
//
//    if($debugFlag === true)
//    {
//        file_put_contents($debugFile, $debugTime . " - System reboot erkannt – PID ist ungültig\n", FILE_APPEND);
//        file_put_contents($debugFile, $debugTime . " - System reboot erkannt – currentRebootTimestamp:$currentRebootTimestamp\n", FILE_APPEND);
//        file_put_contents($debugFile, $debugTime . " - System reboot erkannt – storedRebootTimestamp:$storedRebootTimestamp\n", FILE_APPEND);
//    }
//
//    // Lockfile löschen, DB-Eintrag löschen, neuen Prozess starten
//    unlink($lockFile); // Lock-File entfernen
//    unlink($stopFile); // Stop-File entfernen
//    setParamData('lastRebootTimestamp', '');
//    setParamData('cronLoopPid', '');
//}

// Prüfen, ob bereits eine Instanz läuft
if (file_exists($lockFile))
{
    $pid = file_get_contents($lockFile);

    if ($osIssWindows === false)
    {
        if (posix_getpgid((int) $pid))
        {
            echo "Lock-File vorhanden. Skript bereits gestartet mit (PID: $pid)";
            exit;
        }
    }
    else
    {
        if (getmypid() == (int) $pid)
        {
            echo "Lock-File vorhanden. Skript bereits gestartet mit (PID: $pid)";
            exit;
        }
    }
}
else
{
    #Wenn Pid File nicht vorhanden prüfen, ob zuletzt gespeicherte PID aktiv ist.
    #Wenn aktiv, Pid-File rekonstruieren.
    if($cron_loop_pid != '')
    {
        if ($osIssWindows === false)
        {
            if (posix_getpgid((int) $cron_loop_pid))
            {
                file_put_contents($lockFile, getmypid());
                echo "Lock-File NICHT vorhanden. Skript bereits gestartet mit (PID: $cron_loop_pid)";
                exit;
            }
        }
        else
        {
            if (getmypid() == (int) $cron_loop_pid)
            {
                file_put_contents($lockFile, getmypid());
                echo "Lock-File NICHT vorhanden. Skript bereits gestartet mit (PID: $cron_loop_pid)";
                exit;
            }
        }
    }
}

// PID speichern in Lock-File wenn Pid nicht mehr existiert oder LockFile nicht vorhanden ist.
file_put_contents($lockFile, getmypid());
// PID speichern in Parameter Datenbank.
setParamData('cronLoopPid', getmypid());
#setParamData('lastRebootTimestamp', $currentRebootTimestamp, 'txt');

if($debugFlag === true)
{
//    file_put_contents($debugFile, $debugTime . " - Setzte Pid mit ".getmypid()."\n", FILE_APPEND);
//    file_put_contents($debugFile, $debugTime . " - Setzte lastRebootTimestamp mit ".$currentRebootTimestamp."\n", FILE_APPEND);
}

while (true)
{
    // Prüfen, ob das Skript gestoppt werden soll (cron_stop File)
    if (file_exists($stopFile))
    {
        unlink($lockFile); // Lock-File entfernen
        unlink($stopFile); // Stop-File entfernen
        setParamData('cronLoopPid', '');
        exit();
    }

    // Intervall aus Datei lesen (Standard: 20 Sekunden)
    $interval = 20;
    if (file_exists($configFile))
    {
        $content = trim(file_get_contents($configFile));

        if (is_numeric($content) && $content >= 1 && $content <= 59)
        {
            $interval = (int) $content;
        }
    }

    #Starte Trigger
    if ($osIssWindows === false)
    {
        // --- HIER Trigger-CODE ---
        exec('/usr/bin/wget -q -O /dev/null http://localhost/5d/send_queue.php');
    }
    else
    {
        // --- HIER Trigger-CODE Windows ---
        $ch = curl_init();

        # Set Curl Options
        curl_setopt($ch, CURLOPT_URL, $triggerLink);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        #Ignoriere Timeout Meldung da so gewollt
        if (curl_exec($ch) === false && curl_errno($ch) != 28)
        {
            echo 'Curl error: ' . curl_error($ch);
            echo 'Curl error: ' . curl_errno($ch);
        }

        curl_close($ch);
    }

    sleep($interval);
}
