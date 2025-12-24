<?php
# Wichtig!
# Gewährleistet, das das Skript immer aus dem Verzeichnis ausgeführt ist, wo es liegt.
# Alle relativen Pfade bleiben somit erhalten, auch wenn es aus dem SubMenü aufgerufen wird.
chdir(__DIR__);

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';
require_once 'include/func_php_sensor_data.php';
require_once 'include/func_php_sensor_threshold.php';
require_once 'include/func_php_lora_info.php';

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
$pidFile      = "$basePath/$execDir/" . GET_SENSOR_DATA_CRON_PID_FILE;
$stopFile     = "$basePath/$execDir/" . GET_SENSOR_DATA_CRON_STOP_FILE;
$debugLogFile = "log/cron_loop_get_sensordata_debug.log";
$debugTime    = date('Y-m-d H:i:s');
$loraIp       = getParamData('loraIp');
$isNewMeshGui = getParamData('isNewMeshGui');

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$debugFlag    = false;

$cronLoopGetSensorDataPid  = getParamData('cronLoopGetSensorDataPid');
$sensorPollingIntervallMin = getParamData('sensorPollingIntervallMin') ?? 5; // Intervall in Minuten. Default 5 Minuten

if ($cronLoopGetSensorDataPid != '')
{
    // Prüfen, ob bereits eine Instanz läuft. Wenn ja, dann Instanz beenden.
    if (file_exists($pidFile))
    {
        $pid = file_get_contents($pidFile);

        if (getmypid() == (int) $pid)
        {
            if ($debugFlag === true)
            {
                $debugMsgText = "Pid-File vorhanden. Get-Sensordata Skript bereits gestartet mit (PID: $pid) um" . date('Y-m-d H:i:s') . "\n";
                file_put_contents($debugLogFile, $debugMsgText, FILE_APPEND);
            }

            exit;
        }
    }
    else
    {
        #Wenn Pid File nicht vorhanden prüfen, ob zuletzt gespeicherte PID aktiv ist.
        #Wenn aktiv, Pid-File rekonstruieren und skript beenden, da schon ein Prozess damit läuft.
        if ($cronLoopGetSensorDataPid != '')
        {
            if (getmypid() == (int) $cronLoopGetSensorDataPid)
            {
                file_put_contents($pidFile, getmypid());

                if ($debugFlag === true)
                {
                    $debugMsgText = "Pid-File NICHT vorhanden. Get-Sensordata Skript bereits gestartet mit (PID: $cronLoopGetSensorDataPid)" . date('Y-m-d H:i:s') . "\n";
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
setParamData('cronLoopGetSensorDataPid', getmypid());
setParamData('cronLoopGetSensorDataPidTs', date('Y-m-d H:i:s'),'txt');

if ($debugFlag === true)
{
    $debugMsgText = "DEBUG: Cron-GetSensorData gestartet mit PID: ".getmypid()." und Intervall: $sensorPollingIntervallMin um: " . date('Y-m-d H:i:s') . "\n";
    file_put_contents($debugLogFile, $debugMsgText, FILE_APPEND);
}

while (true)
{
    #init Wert
    $interval = 5 * 60;

    // Prüfen, ob das Skript gestoppt werden soll (cron_stop File/flag)
    if (file_exists($stopFile))
    {
        if ($debugFlag === true)
        {
            $debugMsgText = "DEBUG: Cron-GetSensorData StopFile oder Disable erkannt um: " . date('Y-m-d H:i:s') . "\n";
            file_put_contents($debugLogFile, $debugMsgText, FILE_APPEND);
        }

        @unlink($pidFile); // Pid-File entfernen
        @unlink($stopFile); // Stop-File entfernen
        exit();
    }

    // Intervall aus Datenbank lesen (Standard: 1 Minute)
    if ((int) $sensorPollingIntervallMin == 0)
    {
        if ($debugFlag === true)
        {
            $debugMsgText = "DEBUG: Cron-GetSensorData EXit da Intervall: $sensorPollingIntervallMin mit 0 Wert erkannt um: " . date('Y-m-d H:i:s') . "\n";
            file_put_contents($debugLogFile, $debugMsgText, FILE_APPEND);
        }

        // Prüfen, ob Pid existiert
        if (file_exists($pidFile))
        {
            @unlink($pidFile); // Pid-File entfernen
        }

       exit(); // Stop wenn Intervall hier 0-Wert hat.
    }
    else
    {
        $interval = (int) $sensorPollingIntervallMin * 60; //Angabe sleep in Sekunden 3600sek = 1h
    }

    if (getParamData('cronLoopGetSensorDataPid') != getmypid())
    {
        setParamData('cronLoopGetSensorDataPid', getmypid());
        setParamData('cronLoopGetSensorDataPidTs', date('Y-m-d H:i:s'),'txt');
        file_put_contents($pidFile, getmypid());
    }

    #Starte Prozess, um Sensordaten zu holen und Schwellwerte zu prüfen

    #Check new GUI
    if ($isNewMeshGui == 1)
    {
        $resGetSensorData = getSensorData2($loraIp, 1);
    }
    else
    {
        $resGetSensorData = getSensorData($loraIp, 1);
    }

    if ($debugFlag === true)
    {
        $debugMsgText = "DEBUG: Cron-GetSensorData getSensorData. isNewMeshGui:$isNewMeshGui CheckeSensor um: " . date('Y-m-d H:i:s') . "\n";
        file_put_contents($debugLogFile, $debugMsgText, FILE_APPEND);
    }

    checkSensor($resGetSensorData);

    if ($debugFlag === true)
    {
        $debugMsgText = "DEBUG: Cron-GetSensorDataCheckSensor fertig. Warte intervall: $interval um: " . date('Y-m-d H:i:s') . "\n";
        file_put_contents($debugLogFile, $debugMsgText, FILE_APPEND);
    }

    // Prüfen, ob Pid existiert, wenn nicht, dann erzeugen und Pid setzen.
    if (!file_exists($pidFile))
    {
        #Wenn Pid File nicht vorhanden prüfen, ob zuletzt gespeicherte PID aktiv ist.
        #Wenn aktiv, Pid-File rekonstruieren.

        file_put_contents($pidFile, getmypid());
    }

    sleep($interval);
}
