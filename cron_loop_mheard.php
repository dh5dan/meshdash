<?php
# Wichtig!
# Gewährleistet, das das Skript immer aus dem Verzeichnis ausgeführt ist, wo es liegt.
# Alle relativen Pfade bleiben somit erhalten, auch wenn es aus dem SubMenü aufgerufen wird.
chdir(__DIR__);

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';
require_once 'include/func_php_mheard.php';

ignore_user_abort(true);
set_time_limit(0);

#Check ob aufruf via CLI
if (php_sapi_name() !== 'cli')
{
    die("Aufruf nur via CLI. Abbruch.");
}

// Relativer Pfad zu deinem Webverzeichnis
$basePath     = __DIR__;
$execDir      = 'log';
$pidFile      = "$basePath/$execDir/" . MHEARD_CRON_PID_FILE;
$stopFile     = "$basePath/$execDir/" . MHEARD_CRON_STOP_FILE;
$debugLogFile = "$basePath/$execDir/" . 'debug_cron_loop_mheard_' . date('Ymd') . '.log';

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$debugFlag    = false;

$cronLoopMheardPid   = getParamData('cronLoopMheardPid');
$mheardCronIntervall = getParamData('mheardCronIntervall') ?? 1; // Intervall in vollen Stunden 1-4

if ($cronLoopMheardPid != '')
{
    // Prüfen, ob bereits eine Instanz läuft
    if (file_exists($pidFile))
    {
        $pid = file_get_contents($pidFile);

        if (getmypid() == (int) $pid)
        {
            echo "Pid-File vorhanden. Mheard-Cron Skript bereits gestartet mit (PID: $pid)";

            if ($debugFlag === true)
            {
                $debugMsgText = "Pid-File vorhanden. Mheard-Cron Skript bereits gestartet mit (PID: $pid) um" . date('Y-m-d H:i:s') . "\n";
                file_put_contents($debugLogFile, $debugMsgText, FILE_APPEND);
            }

            exit;
        }
    }
    else
    {
        #Wenn Pid File nicht vorhanden prüfen, ob zuletzt gespeicherte PID aktiv ist.
        #Wenn aktiv, Pid-File rekonstruieren.
        if ($cronLoopMheardPid != '')
        {
            if (getmypid() == (int) $cronLoopMheardPid)
            {
                file_put_contents($pidFile, getmypid());
                echo "Pid-File NICHT vorhanden. Mheard-Cron Skript bereits gestartet mit (PID: $cronLoopMheardPid)";

                if ($debugFlag === true)
                {
                    $debugMsgText = "Pid-File NICHT vorhanden. Mheard-Cron Skript bereits gestartet mit (PID: $cronLoopMheardPid)" . date('Y-m-d H:i:s') . "\n";
                    file_put_contents($debugLogFile, $debugMsgText, FILE_APPEND);
                }

                exit;
            }
        }
    }
}

// PID speichern in Pid-File, wenn Pid nicht mehr existiert oder Pid-File nicht vorhanden ist.
file_put_contents($pidFile, getmypid());

// PID speichern in Parameter Datenbank.
setParamData('cronLoopMheardPid', getmypid());
setParamData('cronLoopMheardPidTs', date('Y-m-d H:i:s'),'txt');

if ($debugFlag === true)
{
    $mheardCronIntervall = getParamData('mheardCronIntervall') ?? 1; // Intervall in vollen Stunden 1-4
    $debugMsgText = "DEBUG: Cron_mheard gestartet mit PID: ".getmypid()." und Intervall $mheardCronIntervall um: " . date('Y-m-d H:i:s') . "\n";
    file_put_contents($debugLogFile, $debugMsgText, FILE_APPEND);
}

while (true)
{
    // Prüfen, ob das Skript gestoppt werden soll (cron_stop File/flag)
    if (file_exists($stopFile) || (int) getParamData('mheardCronEnable') == 0)
    {
        if ($debugFlag === true)
        {
            $debugMsgText = "DEBUG: StopFile oder Disable erkannt um: " . date('Y-m-d H:i:s') . "\n";
            file_put_contents($debugLogFile, $debugMsgText, FILE_APPEND);
        }

        @unlink($pidFile); // Pid-File entfernen
        @unlink($stopFile); // Stop-File entfernen
        exit();
    }

    // Intervall aus Datenbank lesen (Standard: 1 Stunde)
    if ($mheardCronIntervall == 0)
    {
        $interval = 1800; // 30min
    }
    else
    {
        $interval = (int) $mheardCronIntervall * 3600; //Angabe sleep in Sekunden 3600sek = 1h
    }

    $debugIntervall = $interval;

    if (getParamData('cronLoopMheardPid') != getmypid())
    {
        setParamData('cronLoopMheardPid', getmypid());
        setParamData('cronLoopMheardPidTs', date('Y-m-d H:i:s'),'txt');
        file_put_contents($pidFile, getmypid());
    }

    #Starte Prozess, um Mh-Liste zu holen

    $resGetMheard = getMheard(getParamData('loraIp'));

    if ($resGetMheard === true && $debugFlag === true)
    {
        $debugMsgText = "DEBUG: MHeard wurden erfolgreich abgespeichert um: " . date('Y-m-d H:i:s') . "\n";
        file_put_contents($debugLogFile, $debugMsgText, FILE_APPEND);
    }

    if ($debugFlag === true)
    {
        $debugMsgText = "DEBUG: Trigger MHeard Abruf mit Intervall $debugIntervall sek und stunde $mheardCronIntervall um: " . date('Y-m-d H:i:s') . "\n";
        file_put_contents($debugLogFile, $debugMsgText, FILE_APPEND);
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
