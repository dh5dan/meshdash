<?php
function saveSensorThresholdSettings($hasIna226Sensor): bool
{
    $returnFlag                = true;
    $debugFlag                 = false;
    $sensorPollingIntervallMin = (int) ($_REQUEST['sensorPollingIntervallMin'] ?? 5);
    $sensorPollingEnabled      = (int) ($_REQUEST['sensorPollingEnabled'] ?? 0);

    ################### Toggle-Check ###############################################
    $sensorPollingIntervallMinCheck = (int) (getParamData('sensorPollingIntervallMin') ?? 5); // Intervall in Minuten
    $sensorPollingEnabledCheck      = (int) (getParamData('sensorPollingEnabled') ?? 0); // Intervall AN/AUS

    $sensorIntervallPollingEnabledHasChanged = $sensorPollingEnabledCheck !== $sensorPollingEnabled;
    $sensorIntervallHasChanged               = $sensorPollingIntervallMinCheck !== $sensorPollingIntervallMin;

    #######################################################################################

    setParamData('sensorPollingIntervallMin', $sensorPollingIntervallMin);
    setParamData('sensorPollingEnabled', $sensorPollingEnabled);

    $sensorThTempEnabled  = (int) ($_REQUEST['sensorThTempEnabled'] ?? 0);
    $sensorThTempMinValue = $_REQUEST['sensorThTempMinValue'] ?? '';
    $sensorThTempMaxValue = $_REQUEST['sensorThTempMaxValue'] ?? '';
    $sensorThTempAlertMsg = $_REQUEST['sensorThTempAlertMsg'] ?? '';
    $sensorThTempDmGrpId  = $_REQUEST['sensorThTempDmGrpId'] ?? '999';
    $sensorThTempDmGrpId  = $sensorThTempDmGrpId == '' ? '999' : $sensorThTempDmGrpId;

    $arrayThTempData['sensorThTempEnabled']  = $sensorThTempEnabled;
    $arrayThTempData['sensorThTempMinValue'] = $sensorThTempMinValue;
    $arrayThTempData['sensorThTempMaxValue'] = $sensorThTempMaxValue;
    $arrayThTempData['sensorThTempAlertMsg'] = $sensorThTempAlertMsg;
    $arrayThTempData['sensorThTempDmGrpId']  = $sensorThTempDmGrpId;

    $sensorThToutEnabled  = (int) ($_REQUEST['sensorThToutEnabled'] ?? 0);
    $sensorThToutMinValue = $_REQUEST['sensorThToutMinValue'] ?? '';
    $sensorThToutMaxValue = $_REQUEST['sensorThToutMaxValue'] ?? '';
    $sensorThToutAlertMsg = $_REQUEST['sensorThToutAlertMsg'] ?? '';
    $sensorThToutDmGrpId  = $_REQUEST['sensorThToutDmGrpId'] ?? '999';
    $sensorThToutDmGrpId  = $sensorThToutDmGrpId == '' ? '999' : $sensorThToutDmGrpId;

    $arrayThTempData['sensorThToutEnabled']  = $sensorThToutEnabled;
    $arrayThTempData['sensorThToutMinValue'] = $sensorThToutMinValue;
    $arrayThTempData['sensorThToutMaxValue'] = $sensorThToutMaxValue;
    $arrayThTempData['sensorThToutAlertMsg'] = $sensorThToutAlertMsg;
    $arrayThTempData['sensorThToutDmGrpId']  = $sensorThToutDmGrpId;

    $resSetThTempData = setThTempData($arrayThTempData);

    if ($resSetThTempData === false)
    {
        $returnFlag = false;
    }

    ######################### INA226
    if ($hasIna226Sensor === true)
    {
        $sensorThIna226vBusEnabled  = (int) ($_REQUEST['sensorThIna226vBusEnabled'] ?? 0);
        $sensorThIna226vBusMinValue = $_REQUEST['sensorThIna226vBusMinValue'] ?? '';
        $sensorThIna226vBusMaxValue = $_REQUEST['sensorThIna226vBusMaxValue'] ?? '';
        $sensorThIna226vBusAlertMsg = $_REQUEST['sensorThIna226vBusAlertMsg'] ?? '';
        $sensorThIna226vBusDmGrpId  = $_REQUEST['sensorThIna226vBusDmGrpId'] ?? '999';
        $sensorThIna226vBusDmGrpId  = $sensorThIna226vBusDmGrpId == '' ? '999' : $sensorThIna226vBusDmGrpId;

        $arrayThIna226Data['sensorThIna226vBusEnabled']  = $sensorThIna226vBusEnabled;
        $arrayThIna226Data['sensorThIna226vBusMinValue'] = $sensorThIna226vBusMinValue;
        $arrayThIna226Data['sensorThIna226vBusMaxValue'] = $sensorThIna226vBusMaxValue;
        $arrayThIna226Data['sensorThIna226vBusAlertMsg'] = $sensorThIna226vBusAlertMsg;
        $arrayThIna226Data['sensorThIna226vBusDmGrpId']  = $sensorThIna226vBusDmGrpId;

        $sensorThIna226vShuntEnabled  = (int) ($_REQUEST['sensorThIna226vShuntEnabled'] ?? 0);
        $sensorThIna226vShuntMinValue = $_REQUEST['sensorThIna226vShuntMinValue'] ?? '';
        $sensorThIna226vShuntMaxValue = $_REQUEST['sensorThIna226vShuntMaxValue'] ?? '';
        $sensorThIna226vShuntAlertMsg = $_REQUEST['sensorThIna226vShuntAlertMsg'] ?? '';
        $sensorThIna226vShuntDmGrpId  = $_REQUEST['sensorThIna226vShuntDmGrpId'] ?? '999';
        $sensorThIna226vShuntDmGrpId  = $sensorThIna226vShuntDmGrpId == '' ? '999' : $sensorThIna226vShuntDmGrpId;

        $arrayThIna226Data['sensorThIna226vShuntEnabled']  = $sensorThIna226vShuntEnabled;
        $arrayThIna226Data['sensorThIna226vShuntMinValue'] = $sensorThIna226vShuntMinValue;
        $arrayThIna226Data['sensorThIna226vShuntMaxValue'] = $sensorThIna226vShuntMaxValue;
        $arrayThIna226Data['sensorThIna226vShuntAlertMsg'] = $sensorThIna226vShuntAlertMsg;
        $arrayThIna226Data['sensorThIna226vShuntDmGrpId']  = $sensorThIna226vShuntDmGrpId;

        $sensorThIna226vCurrentEnabled  = (int) ($_REQUEST['sensorThIna226vCurrentEnabled'] ?? 0);
        $sensorThIna226vCurrentMinValue = $_REQUEST['sensorThIna226vCurrentMinValue'] ?? '';
        $sensorThIna226vCurrentMaxValue = $_REQUEST['sensorThIna226vCurrentMaxValue'] ?? '';
        $sensorThIna226vCurrentAlertMsg = $_REQUEST['sensorThIna226vCurrentAlertMsg'] ?? '';
        $sensorThIna226vCurrentDmGrpId  = $_REQUEST['sensorThIna226vCurrentDmGrpId'] ?? '999';
        $sensorThIna226vCurrentDmGrpId  = $sensorThIna226vCurrentDmGrpId == '' ? '999' : $sensorThIna226vCurrentDmGrpId;

        $arrayThIna226Data['sensorThIna226vCurrentEnabled']  = $sensorThIna226vCurrentEnabled;
        $arrayThIna226Data['sensorThIna226vCurrentMinValue'] = $sensorThIna226vCurrentMinValue;
        $arrayThIna226Data['sensorThIna226vCurrentMaxValue'] = $sensorThIna226vCurrentMaxValue;
        $arrayThIna226Data['sensorThIna226vCurrentAlertMsg'] = $sensorThIna226vCurrentAlertMsg;
        $arrayThIna226Data['sensorThIna226vCurrentDmGrpId']  = $sensorThIna226vCurrentDmGrpId;

        $sensorThIna226vPowerEnabled  = (int) ($_REQUEST['sensorThIna226vPowerEnabled'] ?? 0);
        $sensorThIna226vPowerMinValue = $_REQUEST['sensorThIna226vPowerMinValue'] ?? '';
        $sensorThIna226vPowerMaxValue = $_REQUEST['sensorThIna226vPowerMaxValue'] ?? '';
        $sensorThIna226vPowerAlertMsg = $_REQUEST['sensorThIna226vPowerAlertMsg'] ?? '';
        $sensorThIna226vPowerDmGrpId  = $_REQUEST['sensorThIna226vPowerDmGrpId'] ?? '999';
        $sensorThIna226vPowerDmGrpId  = $sensorThIna226vPowerDmGrpId == '' ? '999' : $sensorThIna226vPowerDmGrpId;

        $arrayThIna226Data['sensorThIna226vPowerEnabled']  = $sensorThIna226vPowerEnabled;
        $arrayThIna226Data['sensorThIna226vPowerMinValue'] = $sensorThIna226vPowerMinValue;
        $arrayThIna226Data['sensorThIna226vPowerMaxValue'] = $sensorThIna226vPowerMaxValue;
        $arrayThIna226Data['sensorThIna226vPowerAlertMsg'] = $sensorThIna226vPowerAlertMsg;
        $arrayThIna226Data['sensorThIna226vPowerDmGrpId']  = $sensorThIna226vPowerDmGrpId;

        $resSetThIna226Data = setThIna226Data($arrayThIna226Data);

        if ($resSetThIna226Data === false)
        {
            $returnFlag = false;
        }
    }

    $startGetSensorDataCron   = $sensorIntervallPollingEnabledHasChanged === true && $sensorPollingEnabled == 1;
    $stopGetSensorDataCron    = $sensorIntervallPollingEnabledHasChanged === true && $sensorPollingEnabled == 0;
    $restartGetSensorDataCron = $sensorIntervallPollingEnabledHasChanged === false && $sensorIntervallHasChanged === true && $sensorPollingEnabled == 1;

    if ($debugFlag === true)
    {
        echo "<br>---------------------------------------";
        echo "<br>startGetSensorDataCron";
        var_dump($startGetSensorDataCron);

        echo "<br>stopGetSensorDataCron";
        var_dump($stopGetSensorDataCron);

        echo "<br>restartGetSensorDataCron";
        var_dump($restartGetSensorDataCron);

        echo "<br><br>";
    }

    if ($startGetSensorDataCron === true)
    {
        setCronSensorInterval(1);
    }

    if ($stopGetSensorDataCron === true)
    {
        setCronSensorInterval(0);
    }

    if ($restartGetSensorDataCron === true)
    {
        setCronSensorInterval(0);
        setCronSensorInterval(1);
    }

    return $returnFlag;
}

function setCronSensorInterval(int $startFlag, int $restartFlag = 0): bool
{
    $startStopProcess = $startFlag == 1; // 1= Start, 0 = Stop
    $restartProcess   = $restartFlag == 1; // 1= Restart
    $debugFlag        = false;

    #Hintergrundprozess für getSensorData-Cron
    $paramBgProcess['task'] = 'cronGetSensorData';

    if ($debugFlag === true)
    {
        echo "<br>func: setCronSensorInterval";
        echo "<br>startStopProcess:";
        var_dump($startStopProcess);
        echo "<br>restartProcess:";
        var_dump($restartProcess);
        echo "<br>TASK:";
        var_dump($paramBgProcess);
        echo "<br><br>";
    }

    #Kill Task, wenn abgeschaltet wird
    if ($startStopProcess === false)
    {
        $stopBgProcessGetSensorData = stopBgProcess($paramBgProcess);

        if ($stopBgProcessGetSensorData === true)
        {
            echo '<br><span class="successHint">GetSensorData-Cron Prozess erfolgreich beendet.</span>';
        }
        else
        {
            echo '<br><span class="failureHint">Fehler beim Beenden von GetSensorData-Cron Prozess.</span>';
        }
        echo "<br><br>";
        echo "<br>";
    }

    if ($startStopProcess === true && $restartProcess === false)
    {
        $startBgProcessGetSensorData = startBgProcess($paramBgProcess);

        if (!empty($startBgProcessGetSensorData))
        {
            echo '<br><span class="successHint">GetSensorData-Cron Prozess erfolgreich gestartet.</span>';
        }
        else
        {
            echo '<br><span class="failureHint">Fehler beim Starten von GetSensorData-Cron Prozess.</span>';
        }
        echo "<br><br>";
        echo "<br>";
    }

    if ($restartProcess === true)
    {
        $stopBgProcessGetSensorData  = stopBgProcess($paramBgProcess);
        $startBgProcessGetSensorData = startBgProcess($paramBgProcess);

        if (!empty($startBgProcessGetSensorData) && $stopBgProcessGetSensorData === true)
        {
            echo '<br><span class="successHint">Intervall-Änderung. Mheard-Cron Prozess erfolgreich neu gestartet.</span>';
        }
        else
        {
            echo '<br><span class="failureHint">Intervall-Änderung. Fehler beim Neustart von Mheard-Cron Prozess.</span>';
        }
        echo "<br><br>";
        echo "<br>";
    }

    return true;
}

function setThIna226Data($arrayParam): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/sensor_th_ina226.db';
    $dbFilenameRoot = 'database/sensor_th_ina226.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $timeStamps     = date('Y-m-d H:i:s');

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    $sensorThIna226IntervallMin = $arrayParam['sensorThIna226IntervallMin'] ?? 60;

    $sensorThIna226vBusEnabled  = $arrayParam['sensorThIna226vBusEnabled'] ?? 0;
    $sensorThIna226vBusMinValue = $arrayParam['sensorThIna226vBusMinValue'] ?? '';
    $sensorThIna226vBusMaxValue = $arrayParam['sensorThIna226vBusMaxValue'] ?? '';
    $sensorThIna226vBusAlertMsg = $arrayParam['sensorThIna226vBusAlertMsg'] ?? '';
    $sensorThIna226vBusDmGrpId  = $arrayParam['sensorThIna226vBusDmGrpId'] ?? '*';

    $sensorThIna226vShuntEnabled  = $arrayParam['sensorThIna226vShuntEnabled'] ?? 0;
    $sensorThIna226vShuntMinValue = $arrayParam['sensorThIna226vShuntMinValue'] ?? '';
    $sensorThIna226vShuntMaxValue = $arrayParam['sensorThIna226vShuntMaxValue'] ?? '';
    $sensorThIna226vShuntAlertMsg = $arrayParam['sensorThIna226vShuntAlertMsg'] ?? '';
    $sensorThIna226vShuntDmGrpId  = $arrayParam['sensorThIna226vShuntDmGrpId'] ?? '999';

    $sensorThIna226vCurrentEnabled  = $arrayParam['sensorThIna226vCurrentEnabled'] ?? 0;
    $sensorThIna226vCurrentMinValue = $arrayParam['sensorThIna226vCurrentMinValue'] ?? '';
    $sensorThIna226vCurrentMaxValue = $arrayParam['sensorThIna226vCurrentMaxValue'] ?? '';
    $sensorThIna226vCurrentAlertMsg = $arrayParam['sensorThIna226vCurrentAlertMsg'] ?? '';
    $sensorThIna226vCurrentDmGrpId  = $arrayParam['sensorThIna226vCurrentDmGrpId'] ?? '999';

    $sensorThIna226vPowerEnabled  = $_REQUEST['sensorThIna226vPowerEnabled'] ?? 0;
    $sensorThIna226vPowerMinValue = $_REQUEST['sensorThIna226vPowerMinValue'] ?? '';
    $sensorThIna226vPowerMaxValue = $_REQUEST['sensorThIna226vPowerMaxValue'] ?? '';
    $sensorThIna226vPowerAlertMsg = $_REQUEST['sensorThIna226vPowerAlertMsg'] ?? '';
    $sensorThIna226vPowerDmGrpId  = $_REQUEST['sensorThIna226vPowerDmGrpId'] ?? '999';

    #Escape Value
    $sensorThIna226vBusEnabled  = SQLite3::escapeString($sensorThIna226vBusEnabled);
    $sensorThIna226vBusMinValue = SQLite3::escapeString($sensorThIna226vBusMinValue);
    $sensorThIna226vBusMaxValue = SQLite3::escapeString($sensorThIna226vBusMaxValue);
    $sensorThIna226vBusAlertMsg = SQLite3::escapeString($sensorThIna226vBusAlertMsg);
    $sensorThIna226vBusDmGrpId  = SQLite3::escapeString($sensorThIna226vBusDmGrpId);

    $sensorThIna226vShuntEnabled  = SQLite3::escapeString($sensorThIna226vShuntEnabled);
    $sensorThIna226vShuntMinValue = SQLite3::escapeString($sensorThIna226vShuntMinValue);
    $sensorThIna226vShuntMaxValue = SQLite3::escapeString($sensorThIna226vShuntMaxValue);
    $sensorThIna226vShuntAlertMsg = SQLite3::escapeString($sensorThIna226vShuntAlertMsg);
    $sensorThIna226vShuntDmGrpId  = SQLite3::escapeString($sensorThIna226vShuntDmGrpId);

    $sensorThIna226vCurrentEnabled  = SQLite3::escapeString($sensorThIna226vCurrentEnabled);
    $sensorThIna226vCurrentMinValue = SQLite3::escapeString($sensorThIna226vCurrentMinValue);
    $sensorThIna226vCurrentMaxValue = SQLite3::escapeString($sensorThIna226vCurrentMaxValue);
    $sensorThIna226vCurrentAlertMsg = SQLite3::escapeString($sensorThIna226vCurrentAlertMsg);
    $sensorThIna226vCurrentDmGrpId  = SQLite3::escapeString($sensorThIna226vCurrentDmGrpId);

    $sensorThIna226vPowerEnabled  = SQLite3::escapeString($sensorThIna226vPowerEnabled);
    $sensorThIna226vPowerMinValue = SQLite3::escapeString($sensorThIna226vPowerMinValue);
    $sensorThIna226vPowerMaxValue = SQLite3::escapeString($sensorThIna226vPowerMaxValue);
    $sensorThIna226vPowerAlertMsg = SQLite3::escapeString($sensorThIna226vPowerAlertMsg);
    $sensorThIna226vPowerDmGrpId  = SQLite3::escapeString($sensorThIna226vPowerDmGrpId);

    $sql = "REPLACE INTO sensorThIna226 (sensorThIna226Id,
                                         timestamps, 
                                         sensorThIna226IntervallMin,
                                         sensorThIna226vBusEnabled, 
                                         sensorThIna226vBusMinValue, 
                                         sensorThIna226vBusMaxValue, 
                                         sensorThIna226vBusAlertMsg, 
                                         sensorThIna226vBusDmGrpId, 
                                         sensorThIna226vShuntEnabled, 
                                         sensorThIna226vShuntMinValue, 
                                         sensorThIna226vShuntMaxValue, 
                                         sensorThIna226vShuntAlertMsg, 
                                         sensorThIna226vShuntDmGrpId, 
                                         sensorThIna226vCurrentEnabled, 
                                         sensorThIna226vCurrentMinValue, 
                                         sensorThIna226vCurrentMaxValue, 
                                         sensorThIna226vCurrentAlertMsg, 
                                         sensorThIna226vCurrentDmGrpId, 
                                         sensorThIna226vPowerEnabled, 
                                         sensorThIna226vPowerMinValue, 
                                         sensorThIna226vPowerMaxValue, 
                                         sensorThIna226vPowerAlertMsg, 
                                         sensorThIna226vPowerDmGrpId)
               VALUES ('1',
                       '$timeStamps',
                       '$sensorThIna226IntervallMin',
                       '$sensorThIna226vBusEnabled', 
                       '$sensorThIna226vBusMinValue', 
                       '$sensorThIna226vBusMaxValue', 
                       '$sensorThIna226vBusAlertMsg', 
                       '$sensorThIna226vBusDmGrpId', 
                       '$sensorThIna226vShuntEnabled', 
                       '$sensorThIna226vShuntMinValue', 
                       '$sensorThIna226vShuntMaxValue', 
                       '$sensorThIna226vShuntAlertMsg', 
                       '$sensorThIna226vShuntDmGrpId', 
                       '$sensorThIna226vCurrentEnabled', 
                       '$sensorThIna226vCurrentMinValue', 
                       '$sensorThIna226vCurrentMaxValue', 
                       '$sensorThIna226vCurrentAlertMsg', 
                       '$sensorThIna226vCurrentDmGrpId', 
                       '$sensorThIna226vPowerEnabled', 
                       '$sensorThIna226vPowerMinValue', 
                       '$sensorThIna226vPowerMaxValue', 
                       '$sensorThIna226vPowerAlertMsg', 
                       '$sensorThIna226vPowerDmGrpId'
                     );
         ";

    $logArray   = array();
    $logArray[] = "setThIna226Data: Database: $dbFilename";

    $res = safeDbRun($db, $sql, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

    return true;
}

function setThTempData($arrayParam): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/sensor_th_temp.db';
    $dbFilenameRoot = 'database/sensor_th_temp.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $timeStamps     = date('Y-m-d H:i:s');

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    $sensorThTempEnabled  = $arrayParam['sensorThTempEnabled'];
    $sensorThTempMinValue = $arrayParam['sensorThTempMinValue'];
    $sensorThTempMaxValue = $arrayParam['sensorThTempMaxValue'];
    $sensorThTempAlertMsg = $arrayParam['sensorThTempAlertMsg'];
    $sensorThTempDmGrpId  = $arrayParam['sensorThTempDmGrpId'];

    $sensorThToutEnabled  = $arrayParam['sensorThToutEnabled'];
    $sensorThToutMinValue = $arrayParam['sensorThToutMinValue'];
    $sensorThToutMaxValue = $arrayParam['sensorThToutMaxValue'];
    $sensorThToutAlertMsg = $arrayParam['sensorThToutAlertMsg'];
    $sensorThToutDmGrpId  = $arrayParam['sensorThToutDmGrpId'];

    #Escape Value
    $sensorThTempEnabled  = SQLite3::escapeString($sensorThTempEnabled);
    $sensorThTempMinValue = SQLite3::escapeString($sensorThTempMinValue);
    $sensorThTempMaxValue = SQLite3::escapeString($sensorThTempMaxValue);
    $sensorThTempAlertMsg = SQLite3::escapeString($sensorThTempAlertMsg);
    $sensorThTempDmGrpId  = SQLite3::escapeString($sensorThTempDmGrpId);

    $sensorThToutEnabled  = SQLite3::escapeString($sensorThToutEnabled);
    $sensorThToutMinValue = SQLite3::escapeString($sensorThToutMinValue);
    $sensorThToutMaxValue = SQLite3::escapeString($sensorThToutMaxValue);
    $sensorThToutAlertMsg = SQLite3::escapeString($sensorThToutAlertMsg);
    $sensorThToutDmGrpId  = SQLite3::escapeString($sensorThToutDmGrpId);

    $sqlTemp = "REPLACE INTO sensorThTemp (sensorThTempId,
                                           timestamps, 
                                           sensorThTempEnabled, 
                                           sensorThTempMinValue, 
                                           sensorThTempMaxValue, 
                                           sensorThTempAlertMsg, 
                                           sensorThTempDmGrpId, 
                                           sensorThToutEnabled, 
                                           sensorThToutMinValue, 
                                           sensorThToutMaxValue, 
                                           sensorThToutAlertMsg, 
                                           sensorThToutDmGrpId)
                     VALUES ('1',
                             '$timeStamps',
                             '$sensorThTempEnabled',
                             '$sensorThTempMinValue',
                             '$sensorThTempMaxValue',
                             '$sensorThTempAlertMsg',
                             '$sensorThTempDmGrpId',
                             '$sensorThToutEnabled',
                             '$sensorThToutMinValue',
                             '$sensorThToutMaxValue',
                             '$sensorThToutAlertMsg',
                             '$sensorThToutDmGrpId'
                           );
                    ";

    $logArray   = array();
    $logArray[] = "setThTempData: Database: $dbFilename";

    $res = safeDbRun($db, $sqlTemp, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

    return true;
}