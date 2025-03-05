<?php
set_time_limit(0);

// Relativer Pfad zu deinem Webverzeichnis
$basePath   = __DIR__;
$execDir    = "log";
$lockFile   = "$basePath/$execDir/cron_loop.lock";
$configFile = "$basePath/$execDir/cron_interval.conf";
$stopFile   = "$basePath/$execDir/cron_stop"; // Stop-Datei

#Prüfe, ob Verzeichnis existiert und setzte es ggf. neu
if (!is_dir("$basePath/$execDir"))
{
    mkdir("$basePath/$execDir", 0775, true);
    chown("$basePath/$execDir", "www-data");  // Besitzer setzen
    chgrp("$basePath/$execDir", "www-data");  // Gruppenbesitz setzen
}

// Prüfen, ob bereits eine Instanz läuft
if (file_exists($lockFile))
{
    $pid = file_get_contents($lockFile);
    if (posix_getpgid((int) $pid))
    {
        echo "Skript läuft bereits (PID: $pid)";
        exit;
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

    // --- HIER KOMMT DEIN CODE ---
    exec('/usr/bin/wget -q -O /dev/null http://localhost/5d/send_queue.php');
   # file_put_contents("$basePath/$execDir/cron_log.txt", date('Y-m-d H:i:s') . " - WGET Job ausgeführt mit interval:$interval\n", FILE_APPEND);

    sleep($interval);
}
