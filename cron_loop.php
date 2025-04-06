<?php
require_once 'include/func_php_core.php';
ignore_user_abort(true);
set_time_limit(0);

// Relativer Pfad zu deinem Webverzeichnis
$basePath   = __DIR__;
$execDir    = "log";
$lockFile   = "$basePath/$execDir/cron_loop.lock";
$configFile = "$basePath/$execDir/cron_interval.conf";
$stopFile   = "$basePath/$execDir/cron_stop"; // Stop-Datei

$actualHost  = (empty($_SERVER['HTTPS']) ? 'http' : 'https');
$triggerLink = $actualHost . '://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER["REQUEST_URI"] . '?') . '/' . 'send_queue.php';

#Check what oS is running
$osIssWindows = chkOsIssWindows();

#Prüfe, ob Verzeichnis existiert und setzte es ggf. neu
if (!is_dir("$basePath/$execDir") && $osIssWindows === false)
{
    mkdir("$basePath/$execDir", 0775, true);
    chown("$basePath/$execDir", "www-data");  // Besitzer setzen
    chgrp("$basePath/$execDir", "www-data");  // Gruppenbesitz setzen
}

// Prüfen, ob bereits eine Instanz läuft
if (file_exists($lockFile))
{
    $pid = file_get_contents($lockFile);

    if ($osIssWindows === false)
    {
        if (posix_getpgid((int) $pid))
        {
            echo "Skript bereits gestartet mit (PID: $pid)";
            exit;
        }
    }
    else
    {
        if (getmypid() == (int) $pid)
        {
            echo "Skript bereits gestartet mit (PID: $pid)";
            exit;
        }
    }
}

// Eigene PID speichern
file_put_contents($lockFile, getmypid());

while (true)
{
    // Prüfen, ob das Skript gestoppt werden soll
    if (file_exists($stopFile))
    {
        unlink($lockFile); // Lock-File entfernen
        unlink($stopFile); // Stop-File entfernen
        exit();
    }

    // Intervall aus Datei lesen (Standard: 30 Sekunden)
    $interval = 30;
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
