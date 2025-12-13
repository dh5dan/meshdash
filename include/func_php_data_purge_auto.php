<?php
function saveSettingsAutoPurge(): bool
{
    $enableMsgPurge    = $_REQUEST['enableMsgPurge'] ?? 0;
    $enableSensorPurge = $_REQUEST['enableSensorPurge'] ?? 0;
    $daysMsgPurge      = $_REQUEST['daysMsgPurge'] ?? 30;
    $daysSensorPurge   = $_REQUEST['daysSensorPurge'] ?? 30;

    #Zustand alt abfragen, um Wechsel zu erkennen
    $enableMsgPurgeCheck    = getParamData('enableMsgPurge');
    $enableSensorPurgeCheck = getParamData('enableSensorPurge');

    setParamData('enableMsgPurge', $enableMsgPurge);
    setParamData('enableSensorPurge', $enableSensorPurge);
    setParamData('daysMsgPurge', $daysMsgPurge);
    setParamData('daysSensorPurge', $daysSensorPurge);

    if ((int)$enableMsgPurge != (int)$enableMsgPurgeCheck)
    {
        deletePurgeWriteMutex('meshdash');
    }

    if ((int)$enableSensorPurge != (int)$enableSensorPurgeCheck)
    {
        deletePurgeWriteMutex('sensordata');
    }

    return true;
}
function deletePurgeWriteMutex($procMutexName): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/write_mutex.db';
    $dbFilenameRoot = 'database/write_mutex.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(5000);

    $sqlDeleteWriteMutex = "DELETE FROM purge_lock WHERE name = '$procMutexName';";

    $logArray   = array();
    $logArray[]          = "AutoPurge Write-Mutex DELETE";
    $logArray[]          = "ProcMutexName:".$procMutexName;
    $res                 = safeDbRun($db, $sqlDeleteWriteMutex, 'exec', $logArray);

    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

    return true;
}
