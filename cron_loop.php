<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';
ignore_user_abort(true);
set_time_limit(0);

#Check ob aufruf via CLI
if (php_sapi_name() !== 'cli')
{
    die("Aufruf nur via CLI. Abbruch.");
}

// Relativer Pfad zu deinem Webverzeichnis
$basePath    = __DIR__;
$execDir     = 'log';
$pidFile     = "$basePath/$execDir/" . CRON_PID_FILE;
$configFile  = "$basePath/$execDir/" . CRON_CONF_FILE;
$stopFile    = "$basePath/$execDir/" . CRON_STOP_FILE; // Stop-Datei
$debugFile   = "log/cron_loop_debug_log.txt";
$debugTime   = date('Y-m-d H:i:s');
$triggerLink = TRIGGER_LINK_SEND_QUEUE;

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$debugFlag    = true;

$cron_loop_pid = getParamData('cronLoopPid');

if ($cron_loop_pid != '')
{
    // Prüfen, ob bereits eine Instanz läuft
    if (file_exists($pidFile))
    {
        $pid = file_get_contents($pidFile);

        if (getmypid() == (int) $pid)
        {
            echo "Pid-File vorhanden. Skript bereits gestartet mit (PID: $pid)";
            exit;
        }
    }
    else
    {
        #Wenn Pid File nicht vorhanden prüfen, ob zuletzt gespeicherte PID aktiv ist.
        #Wenn aktiv, Pid-File rekonstruieren.
        if ($cron_loop_pid != '')
        {
            if (getmypid() == (int) $cron_loop_pid)
            {
                file_put_contents($pidFile, getmypid());
                echo "Pid-File NICHT vorhanden. Skript bereits gestartet mit (PID: $cron_loop_pid)";
                exit;
            }
        }
    }
}

// PID speichern in Pid-File, wenn Pid nicht mehr existiert oder Pid-File nicht vorhanden ist.
file_put_contents($pidFile, getmypid());
// PID speichern in Parameter Datenbank.
setParamData('cronLoopPid', getmypid());
setParamData('cronLoopTs', date('Y-m-d H:i:s'),'txt');

while (true)
{
    // Prüfen, ob das Skript gestoppt werden soll (cron_stop File/flag)
    if (file_exists($stopFile) || (int) getParamData('sendQueueMode') == 0)
    {
        @unlink($pidFile); // Pid-File entfernen
        @unlink($stopFile); // Stop-File entfernen
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

    if (getParamData('cronLoopPid') != getmypid())
    {
        setParamData('cronLoopPid', getmypid());
        setParamData('cronLoopTs', date('Y-m-d H:i:s'),'txt');
        file_put_contents($pidFile, getmypid());
    }

    #Starte Trigger
    if ($osIssWindows === false)
    {
        // --- HIER Trigger-CODE ---
        exec('/usr/bin/wget -q -O /dev/null ' . $triggerLink);
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

    // Prüfen, ob Pid existiert
    if (!file_exists($pidFile))
    {
        #Wenn Pid File nicht vorhanden prüfen, ob zuletzt gespeicherte PID aktiv ist.
        #Wenn aktiv, Pid-File rekonstruieren.

        file_put_contents($pidFile, getmypid());
    }

    sleep($interval);
}
