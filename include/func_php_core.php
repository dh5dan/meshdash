<?php
/** @noinspection SqlWithoutWhere */
function getParamData($key)
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/parameter.db';
    $dbFilenameRoot = 'database/parameter.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    if ($key == '')
    {
        return false;
    }

    $db  = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden

    $sql = "SELECT * 
              FROM parameter AS pa 
             WHERE pa.param_key = '$key';
           ";

    $logArray   = array();
    $logArray[] = "getParamData: Database: $dbFilename";
    $logArray[] = "getParamData: key: $key";

    $res = safeDbRun( $db,  $sql, 'query', $logArray);

    if ($res === false)
    {
        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    $dsData = $res->fetchArray(SQLITE3_ASSOC);

    #Close and write Back WAL
    $db->close();
    unset($db);

    $paramValue = $dsData['param_value'] ?? '';
    $paramText  = $dsData['param_text'] ?? '';

    return ($paramValue !== '' && $paramValue !== null) ? $paramValue : $paramText;
}
function setParamData($key, $value, $mode = 'int'): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/parameter.db';
    $dbFilenameRoot = 'database/parameter.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    #Escape Value
    $value = SQLite3::escapeString($value);

    $param_value = '';
    $param_text  = trim($value);

    if ($mode === 'int')
    {
        $param_value = (int) $value;
        $param_text  = '';
    }

    $sql = "REPLACE INTO parameter (param_key, 
                                    param_value, 
                                    param_text)
                  VALUES ('$key',
                          '$param_value',
                          '$param_text'
                        );
          ";

    $logArray   = array();
    $logArray[] = "setParamData: Database: $dbFilename";
    $logArray[] = "setParamData: key: $key";
    $logArray[] = "setParamData: param_value: $param_value";
    $logArray[] = "setParamData: param_text: $param_text";

    $res = safeDbRun( $db,  $sql, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

    return true;
}
function getBeaconData($key)
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/beacon.db';
    $dbFilenameRoot = 'database/beacon.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    if ($key == '')
    {
        return false;
    }

    $db  = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden

    $sql = "SELECT * 
              FROM beacon AS pa 
             WHERE pa.param_key = '$key';
           ";

    $logArray   = array();
    $logArray[] = "getBeaconData: Database: $dbFilename";
    $logArray[] = "getBeaconData: key: $key";

    $res = safeDbRun( $db,  $sql, 'query', $logArray);

    if ($res === false)
    {
        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    $dsData = $res->fetchArray(SQLITE3_ASSOC);

    #Close and write Back WAL
    $db->close();
    unset($db);

    $paramValue = $dsData['param_value'] ?? '';
    $paramText  = $dsData['param_text'] ?? '';

    return ($paramValue !== '' && $paramValue !== null) ? $paramValue : $paramText;
}
function setBeaconData($key, $value, $mode = 'int'): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/beacon.db';
    $dbFilenameRoot = 'database/beacon.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    #Escape Value
    $value = SQLite3::escapeString($value);

    $param_value = '';
    $param_text  = trim($value);

    if ($mode === 'int')
    {
        $param_value = (int) $value;
        $param_text  = '';
    }

    $sql = "REPLACE INTO beacon (param_key, 
                                    param_value, 
                                    param_text)
                  VALUES ('$key',
                          '$param_value',
                          '$param_text'
                        );
          ";

    $logArray   = array();
    $logArray[] = "setBeaconData: Database: $dbFilename";
    $logArray[] = "setBeaconData: key: $key";
    $logArray[] = "setBeaconData: param_value: $param_value";
    $logArray[] = "setBeaconData: param_text: $param_text";

    $res = safeDbRun( $db,  $sql, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

    return true;
}
function getThTempData()
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/sensor_th_temp.db';
    $dbFilenameRoot = 'database/sensor_th_temp.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $arrayReturn    = array();

    $db = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    $sql = "SELECT * 
              FROM sensorThTemp 
          ORDER BY timestamps DESC
             LIMIT 1;
           ";

    $logArray   = array();
    $logArray[] = "getThTempData: Database: $dbFilename";

    $result = safeDbRun( $db,  $sql, 'query', $logArray);

    if ($result === false)
    {
        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    if ($result !== false)
    {
        while ($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            ###############################################
            #Common
            $arrayReturn['sensorThTempIntervallMin']  = $row['sensorThTempIntervallMin'];

            $arrayReturn['sensorThTempEnabled']        = $row['sensorThTempEnabled'];
            $arrayReturn['sensorThTempMinValue']       = $row['sensorThTempMinValue'];
            $arrayReturn['sensorThTempMaxValue']       = $row['sensorThTempMaxValue'];
            $arrayReturn['sensorThTempAlertMsg']       = $row['sensorThTempAlertMsg'];
            $arrayReturn['sensorThTempAlertCount']     = $row['sensorThTempAlertCount'];
            $arrayReturn['sensorThTempAlertTimestamp'] = $row['sensorThTempAlertTimestamp'];
            $arrayReturn['sensorThTempDmGrpId']        = $row['sensorThTempDmGrpId'];

            $arrayReturn['sensorThToutEnabled']        = $row['sensorThToutEnabled'];
            $arrayReturn['sensorThToutMinValue']       = $row['sensorThToutMinValue'];
            $arrayReturn['sensorThToutMaxValue']       = $row['sensorThToutMaxValue'];
            $arrayReturn['sensorThToutAlertMsg']       = $row['sensorThToutAlertMsg'];
            $arrayReturn['sensorThToutAlertCount']     = $row['sensorThToutAlertCount'];
            $arrayReturn['sensorThToutAlertTimestamp'] = $row['sensorThToutAlertTimestamp'];
            $arrayReturn['sensorThToutDmGrpId']        = $row['sensorThToutDmGrpId'];
        }
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return $arrayReturn;
}
function getThIna226Data()
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/sensor_th_ina226.db';
    $dbFilenameRoot = 'database/sensor_th_ina226.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $arrayReturn    = array();

    $db = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    $sql = "SELECT * 
             FROM sensorThIna226 
         ORDER BY timestamps DESC
            LIMIT 1;
           ";

    $logArray   = array();
    $logArray[] = "getThIna226Data: Database: $dbFilename";

    $result = safeDbRun( $db,  $sql, 'query', $logArray);

    if ($result === false)
    {
        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    if ($result !== false)
    {
        while ($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            ###############################################
            #Common
            $arrayReturn['sensorThIna226IntervallMin']  = $row['sensorThIna226IntervallMin'];

            $arrayReturn['sensorThIna226vBusEnabled']        = $row['sensorThIna226vBusEnabled'];
            $arrayReturn['sensorThIna226vBusMinValue']       = $row['sensorThIna226vBusMinValue'];
            $arrayReturn['sensorThIna226vBusMaxValue']       = $row['sensorThIna226vBusMaxValue'];
            $arrayReturn['sensorThIna226vBusAlertMsg']       = $row['sensorThIna226vBusAlertMsg'];
            $arrayReturn['sensorThIna226vBusAlertCount']     = $row['sensorThIna226vBusAlertCount'];
            $arrayReturn['sensorThIna226vBusAlertTimestamp'] = $row['sensorThIna226vBusAlertTimestamp'];
            $arrayReturn['sensorThIna226vBusDmGrpId']        = $row['sensorThIna226vBusDmGrpId'];

            $arrayReturn['sensorThIna226vShuntEnabled']        = $row['sensorThIna226vShuntEnabled'];
            $arrayReturn['sensorThIna226vShuntMinValue']       = $row['sensorThIna226vShuntMinValue'];
            $arrayReturn['sensorThIna226vShuntMaxValue']       = $row['sensorThIna226vShuntMaxValue'];
            $arrayReturn['sensorThIna226vShuntAlertMsg']       = $row['sensorThIna226vShuntAlertMsg'];
            $arrayReturn['sensorThIna226vShuntAlertCount']     = $row['sensorThIna226vShuntAlertCount'];
            $arrayReturn['sensorThIna226vShuntAlertTimestamp'] = $row['sensorThIna226vShuntAlertTimestamp'];
            $arrayReturn['sensorThIna226vShuntDmGrpId']        = $row['sensorThIna226vShuntDmGrpId'];

            $arrayReturn['sensorThIna226vCurrentEnabled']        = $row['sensorThIna226vCurrentEnabled'];
            $arrayReturn['sensorThIna226vCurrentMinValue']       = $row['sensorThIna226vCurrentMinValue'];
            $arrayReturn['sensorThIna226vCurrentMaxValue']       = $row['sensorThIna226vCurrentMaxValue'];
            $arrayReturn['sensorThIna226vCurrentAlertMsg']       = $row['sensorThIna226vCurrentAlertMsg'];
            $arrayReturn['sensorThIna226vCurrentAlertCount']     = $row['sensorThIna226vCurrentAlertCount'];
            $arrayReturn['sensorThIna226vCurrentAlertTimestamp'] = $row['sensorThIna226vCurrentAlertTimestamp'];
            $arrayReturn['sensorThIna226vCurrentDmGrpId']        = $row['sensorThIna226vCurrentDmGrpId'];

            $arrayReturn['sensorThIna226vPowerEnabled']        = $row['sensorThIna226vPowerEnabled'];
            $arrayReturn['sensorThIna226vPowerMinValue']       = $row['sensorThIna226vPowerMinValue'];
            $arrayReturn['sensorThIna226vPowerMaxValue']       = $row['sensorThIna226vPowerMaxValue'];
            $arrayReturn['sensorThIna226vPowerAlertMsg']       = $row['sensorThIna226vPowerAlertMsg'];
            $arrayReturn['sensorThIna226vPowerAlertCount']     = $row['sensorThIna226vPowerAlertCount'];
            $arrayReturn['sensorThIna226vPowerAlertTimestamp'] = $row['sensorThIna226vPowerAlertTimestamp'];
            $arrayReturn['sensorThIna226vPowerDmGrpId']        = $row['sensorThIna226vPowerDmGrpId'];
        }
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return $arrayReturn;
}
function getKeywordsData($msgId)
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/keywords.db';
    $dbFilenameRoot = 'database/keywords.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $returnValue    = array();

    if ($msgId == '')
    {
        return false;
    }

    $db  = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden

    $sql = "SELECT * 
              FROM keywords AS kw 
             WHERE kw.msg_id = '$msgId';
          ";

    $logArray   = array();
    $logArray[] = "getThIna226Data: Database: $dbFilename";

    $res = safeDbRun( $db,  $sql, 'query', $logArray);

    if ($res === false)
    {
        $returnValue['errCode']  = 1;
        $returnValue['executed'] = 1;

        #Close and write Back WAL
        $db->close();
        unset($db);

        return $returnValue;
    }

    #Leerer Datensatz ist erlaubt!
    $dsData = $res->fetchArray(SQLITE3_ASSOC);

    #Close and write Back WAL
    $db->close();
    unset($db);

    $returnValue['msg_id']   = $dsData['msg_id'] ?? 0;
    $returnValue['executed'] = $dsData['executed'] ?? 0;
    $returnValue['errCode']  = $dsData['errCode'] ?? 0;
    $returnValue['errText']  = $dsData['errText'] ?? '';

    $returnValue['execScript']           = $dsData['execScript'] ?? '';
    $returnValue['execTimestamp']        = $dsData['execTimestamp'] ?? '';
    $returnValue['execTrigger']          = $dsData['execTrigger'] ?? '';
    $returnValue['execReturnMsg']        = $dsData['execReturnMsg'] ?? '';
    $returnValue['execGroup']            = $dsData['execGroup'] ?? '';
    $returnValue['execMsgSend']          = $dsData['execMsgSend'] ?? 0;
    $returnValue['execMsgSendTimestamp'] = $dsData['execMsgSendTimestamp'] ?? '';

    return $returnValue;
}
function setKeywordsData(array $paramSetKeyword): bool
{
    $msgId         = $paramSetKeyword['msgId'];
    $executed      = $paramSetKeyword['executed'];
    $errCode       = $paramSetKeyword['errCode']; //debug = 0
    $errText       = $paramSetKeyword['errText']; //debug = ''
    $execScript    = $paramSetKeyword['execScript'];
    $execTimestamp = $paramSetKeyword['execTimestamp'];
    $execTrigger   = $paramSetKeyword['execTrigger'];
    $execReturnMsg = $paramSetKeyword['execReturnMsg'];
    $execGroup     = $paramSetKeyword['execGroup'];

    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/keywords.db';
    $dbFilenameRoot = 'database/keywords.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');

    #Escape Value
    $msgId    = SQLite3::escapeString($msgId);
    $executed = (int) $executed;
    $errText  = SQLite3::escapeString($errText);
    $msgId    = trim($msgId);

    $execScript    = SQLite3::escapeString($execScript);
    $execTrigger   = SQLite3::escapeString($execTrigger);
    $execReturnMsg = SQLite3::escapeString($execReturnMsg);

    $sql = "REPLACE INTO keywords (msg_id, 
                                   executed,
                                   errCode,
                                   errText,
                                   execScript,
                                   execTimestamp,
                                   execTrigger,
                                   execReturnMsg,
                                   execGroup
                                  )
                           VALUES ('$msgId',
                                   '$executed',
                                   '$errCode',
                                   '$errText',
                                   '$execScript',
                                   '$execTimestamp',
                                   '$execTrigger',
                                   '$execReturnMsg',
                                   '$execGroup'
                                  );
                    ";

    $logArray   = array();
    $logArray[] = "setKeywordsData: msgId: $msgId";
    $logArray[] = "setKeywordsData: errCode: $errCode";
    $logArray[] = "setKeywordsData: errCode: $errCode";
    $logArray[] = "setKeywordsData: errText: $errText";
    $logArray[] = "setKeywordsData: execScript: $execScript";
    $logArray[] = "setKeywordsData: execTimestamp: $execTimestamp";
    $logArray[] = "setKeywordsData: execTrigger: $execTrigger";
    $logArray[] = "setKeywordsData: execReturnMsg: $execReturnMsg";
    $logArray[] = "setKeywordsData: execGroup: $execGroup";
    $logArray[] = "setKeywordsData: Database: $dbFilename";

    $res = safeDbRun( $db,  $sql, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

    return true;
}
function updateKeywordsData(string $msgId): bool
{
    $execMsgSendTimestamp = date('Y-m-d H:i:s');

    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/keywords.db';
    $dbFilenameRoot = 'database/keywords.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');

    #Escape Value
    $msgId    = SQLite3::escapeString($msgId);
    $msgId    = trim($msgId);

    $sql = "UPDATE keywords 
                SET execMsgSend = 1,
                    execMsgSendTimestamp = '$execMsgSendTimestamp' 
              WHERE msg_id = '$msgId'
                    ";

    $logArray   = array();
    $logArray[] = "updateKeywordsData: msgId: $msgId";
    $logArray[] = "setKeywordsData: Database: $dbFilename";

    $res = safeDbRun( $db,  $sql, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

    return true;
}

function chkOsIsWindows(): bool
{
    #Check what oS is running
    if (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN')
    {
        return  true;
    }

    return false;
}
function updateMeshDashData($msgId, $key, $value, $doNothing = false): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/meshdash.db';
    $dbFilenameRoot = 'database/meshdash.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    if ($doNothing === true)
    {
        return true;
    }

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden
    #Escape Value
    $value = trim(SQLite3::escapeString($value));

    $sql = " UPDATE meshdash
                SET $key = '$value'
              WHERE msg_id = '$msgId';
           ";

    $logArray   = array();
    $logArray[] = "updateMeshDashData: key: $key";
    $logArray[] = "updateMeshDashData: value: $value";
    $logArray[] = "updateMeshDashData: msgId: $msgId";
    $logArray[] = "updateMeshDashData: Database: $dbFilename";

    $res = safeDbRun( $db,  $sql, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

    return true;
}
function getTaskCmd($mode)
{
    #Check what oS is running
    $osIssWindows          = chkOsIsWindows();
    $udpReceiverPid        = getParamData('udpReceiverPid');
    $cronLoopPid           = getParamData('cronLoopPid');
    $cronLoopPidFile       = 'log/' . CRON_PID_FILE;
    $cronBeaconLoopPid     = getParamData('cronBeaconLoopPid');
    $cronBeaconLoopPidFile = 'log/' . CRON_BEACON_PID_FILE;

    $cronMheardLoopPid     = getParamData('cronLoopMheardPid');
    $cronMheardLoopPidFile = 'log/' . MHEARD_CRON_PID_FILE;

    $cronGetSensorDataLoopPid     = getParamData('cronLoopGetSensorDataPid');
    $cronGetSensorDataLoopPidFile = 'log/' . GET_SENSOR_DATA_CRON_PID_FILE;

    $mode                  = $mode == '' ? 'udp' : $mode;  // default UDP

    if ($mode == 'udp')
    {
        #Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
        if ($udpReceiverPid == '')
        {
            return $osIssWindows === true ? 'tasklist | find "php.exe"' : "pgrep -a -f udp_receiver.php | grep -v pgrep | awk '{print $1}'";
        }
        else
        {
            return $osIssWindows === true ? 'tasklist /FI "PID eq ' . $udpReceiverPid . '" | findstr /I "php.exe"' : "pgrep -a -f udp_receiver.php | grep -v pgrep | awk '{print $1}'";
        }
    }

    if ($mode == 'cron')
    {
        #Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
        if ($cronLoopPid == '')
        {
            # Wenn keine Pid, dann über Pid-File Status ermitteln.
            # Wenn Pid-File fehlt, dann unter Windows über Dummy einen leeren Eintrag zurückgeben lassen mittels Dummy
            if (!file_exists($cronLoopPidFile))
            {
                return $osIssWindows === true ? 'tasklist | find "dummyFile.exe"' : "pgrep -a -f " . CRON_PROC_FILE . " | grep -v pgrep | awk '{print $1}'";
            }

            return $osIssWindows === true ? 'tasklist | find "php.exe"' : "pgrep -a -f " . CRON_PROC_FILE . " | grep -v pgrep | awk '{print $1}'";
        }
        else
        {
            return $osIssWindows === true ? 'tasklist /FI "PID eq ' . $cronLoopPid . '" | findstr /I "php.exe"' : "pgrep -a -f " . CRON_PROC_FILE . " | grep -v pgrep | awk '{print $1}'";
        }
    }

    if ($mode == 'cronBeacon')
    {
        #Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
        if ($cronBeaconLoopPid == '')
        {
            # Wenn keine Pid, dann über Pid-File Status ermitteln.
            # Wenn Pid-File fehlt, dann unter Windows über Dummy einen leeren Eintrag zurückgeben lassen mittels Dummy
            if (!file_exists($cronBeaconLoopPidFile))
            {
                return $osIssWindows === true ? 'tasklist | find "dummyFile.exe"' : "pgrep -a -f " . CRON_BEACON_PROC_FILE . " | grep -v pgrep | awk '{print $1}'";
            }

            return $osIssWindows === true ? 'tasklist | find "php.exe"' : "pgrep -a -f " . CRON_BEACON_PROC_FILE . " | grep -v pgrep | awk '{print $1}'";
        }
        else
        {
            return $osIssWindows === true ? 'tasklist /FI "PID eq ' . $cronBeaconLoopPid . '" | findstr /I "php.exe"' : "pgrep -a -f " . CRON_BEACON_PROC_FILE . " | grep -v pgrep | awk '{print $1}'";
        }
    }

    if ($mode == 'cronMheard')
    {
        #Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
        if ($cronMheardLoopPid == '')
        {
            # Wenn keine Pid, dann über Pid-File Status ermitteln.
            # Wenn Pid-File fehlt, dann unter Windows über Dummy einen leeren Eintrag zurückgeben lassen mittels Dummy
            if (!file_exists($cronMheardLoopPidFile))
            {
                return $osIssWindows === true ? 'tasklist | find "dummyFile.exe"' : "pgrep -a -f " . MHEARD_CRON_PROC_FILE . " | grep -v pgrep | awk '{print $1}'";
            }

            return $osIssWindows === true ? 'tasklist | find "php.exe"' : "pgrep -a -f " . MHEARD_CRON_PROC_FILE . " | grep -v pgrep | awk '{print $1}'";
        }
        else
        {
            return $osIssWindows === true ? 'tasklist /FI "PID eq ' . $cronMheardLoopPid . '" | findstr /I "php.exe"' : "pgrep -a -f " . MHEARD_CRON_PROC_FILE . " | grep -v pgrep | awk '{print $1}'";
        }
    }

    if ($mode == 'cronGetSensorData')
    {
        #Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
        if ($cronGetSensorDataLoopPid == '')
        {
            # Wenn keine Pid, dann über Pid-File Status ermitteln.
            # Wenn Pid-File fehlt, dann unter Windows über Dummy einen leeren Eintrag zurückgeben lassen mittels Dummy
            if (!file_exists($cronGetSensorDataLoopPidFile))
            {
                return $osIssWindows === true ? 'tasklist | find "dummyFile.exe"' : "pgrep -a -f " . GET_SENSOR_DATA_CRON_PROC_FILE . " | grep -v pgrep | awk '{print $1}'";
            }

            return $osIssWindows === true ? 'tasklist | find "php.exe"' : "pgrep -a -f " . GET_SENSOR_DATA_CRON_PROC_FILE . " | grep -v pgrep | awk '{print $1}'";
        }
        else
        {
            return $osIssWindows === true ? 'tasklist /FI "PID eq ' . $cronGetSensorDataLoopPid . '" | findstr /I "php.exe"' : "pgrep -a -f " . GET_SENSOR_DATA_CRON_PROC_FILE . " | grep -v pgrep | awk '{print $1}'";
        }
    }

    return false;
}
function getTaskKillCmd($mode = 'udp')
{
    #Check what oS is running
    $osIssWindows         = chkOsIsWindows();
    $udpReceiverPid       = getParamData('udpReceiverPid');
    $cronLoopPid          = getParamData('cronLoopPid');
    $cronBeaconPid        = getParamData('cronBeaconLoopPid');
    $cronMheardPid        = getParamData('cronLoopMheardPid');
    $cronGetSensorDataPid = getParamData('cronLoopGetSensorDataPid');

    if ($mode == 'udp')
    {
        #Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
        if ($udpReceiverPid == '')
        {
            return $osIssWindows === true ? 'taskkill /f /fi "imagename eq php.exe"' : 'pkill -9 -f "' . UDP_PROC_FILE . '"';
        }
        else
        {
            return $osIssWindows === true ? 'taskkill /F /PID ' . $udpReceiverPid : 'pkill -9 -f "' . UDP_PROC_FILE . '"';
        }
    }

    if ($mode == 'cron')
    {
        #Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
        if ($cronLoopPid == '')
        {
            # Wenn keine Pid, dann All-Kill für Windows.
            return $osIssWindows === true ? 'taskkill /f /fi "imagename eq php.exe"' : 'pkill -9 -f "' . CRON_PROC_FILE . '"';
        }
        else
        {
            return $osIssWindows === true ? 'taskkill /F /PID ' . $cronLoopPid : 'pkill -9 -f "' . CRON_PROC_FILE . '"';
        }
    }

    if ($mode == 'cronBeacon')
    {
        #Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
        if ($cronBeaconPid == '')
        {
            # Wenn keine Pid, dann All-Kill für Windows.
            return $osIssWindows === true ? 'taskkill /f /fi "imagename eq php.exe"' : 'pkill -9 -f "' . CRON_BEACON_PROC_FILE . '"';
        }
        else
        {
            return $osIssWindows === true ? 'taskkill /F /PID ' . $cronBeaconPid : 'pkill -9 -f "' . CRON_BEACON_PROC_FILE . '"';
        }
    }

    if ($mode == 'cronMheard')
    {
        #Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
        if ($cronMheardPid == '')
        {
            # Wenn keine Pid, dann All-Kill für Windows.
            return $osIssWindows === true ? 'taskkill /f /fi "imagename eq php.exe"' : 'pkill -9 -f "' . MHEARD_CRON_PROC_FILE . '"';
        }
        else
        {
            return $osIssWindows === true ? 'taskkill /F /PID ' . $cronMheardPid : 'pkill -9 -f "' . MHEARD_CRON_PROC_FILE . '"';
        }
    }

    if ($mode == 'cronGetSensorData')
    {
        #Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
        if ($cronGetSensorDataPid == '')
        {
            # Wenn keine Pid, dann All-Kill für Windows.
            return $osIssWindows === true ? 'taskkill /f /fi "imagename eq php.exe"' : 'pkill -9 -f "' . GET_SENSOR_DATA_CRON_PROC_FILE . '"';
        }
        else
        {
            return $osIssWindows === true ? 'taskkill /F /PID ' . $cronGetSensorDataPid : 'pkill -9 -f "' . GET_SENSOR_DATA_CRON_PROC_FILE . '"';
        }
    }

    return false;
}

function logRotate()
{
    $debugFlag = false;

    if ((int) getParamData('chronLogEnable') === 0)
    {
        if ($debugFlag === true)
        {
            echo "<br>chronLogEnable ist False. Abbruch.";
        }

        return false;
    }

    $returnArray = array();
    $rootDir     = dirname(__DIR__); // Das Hauptverzeichnis der Web-App
    $logDir      = $rootDir . '/log'; // Verzeichnis mit den Logs
    $archiveDir  = $logDir . "/archive"; // Zielverzeichnis für Archive
    $zipName     = $archiveDir . "/logs_" . date("Ymd") . ".zip"; // ZIP-Dateiname mit aktuellem Datum
    $prefixes    = [
        "msg_data_",
        "user_data_",
        "user_json_data_",
        "tx_json_data_",
        "call_message_",
        "tx_data_",
        "tx_json_data_",
        "tx_queue_json_data_",
        "udp_msg_data_",
        "send_queue_mheard_",
        "send_mheard_",
        "db_integrity_",
        "udp_forward_msg_data_",
    ]; // Präfixe der Log-Dateien

    $prefixes = array_unique($prefixes); // falls doch mal eine Dublette drin sein sollte

    $retentionDays = getParamData('retentionDays') ?? 7;
    $retentionDays = $retentionDays == '' ? 7 : $retentionDays; // Wie viele Tage die Logs behalten werden sollen
    $chronMode     = getParamData('chronMode') ?? 'zip';
    $chronMode     = $chronMode == '' ? 'zip' : $chronMode;  // "zip" = archivieren, "delete" = direkt löschen

    if (!file_exists($archiveDir))
    {
        mkdir($archiveDir, 0777, true);
    }

    $zip      = new ZipArchive();
    $toDelete = []; // Hier speichern wir die zu löschenden Dateien

    if ($chronMode === "zip" && $zip->open($zipName, ZipArchive::CREATE) !== true)
    {
        echo '<span class="failureHint">Konnte ZIP-Archiv nicht erstellen!</span>';
        return false;
    }

    $now           = time();
    $deletedFiles  = 0;
    $archivedFiles = 0;

    foreach (scandir($logDir) as $file)
    {
        if ($debugFlag === true)
        {
            echo "<br>logDir:$logDir file:$file";
        }

        foreach ($prefixes as $prefix)
        {
            if (strpos($file, $prefix) === 0)
            {
                preg_match('/(\d{8})\.log$/', $file, $matches);
                if (!isset($matches[1]))
                {
                    continue;
                }

                $fileDate = DateTime::createFromFormat("Ymd", $matches[1]);
                if (!$fileDate)
                {
                    continue;
                }

                if ($debugFlag === true)
                {
                    echo "<br><b>Praefixcheck: prefix:$prefix file:$file</b>";
                }

                $fileTimestamp = $fileDate->getTimestamp();
                $age           = floor(($now - $fileTimestamp) / (60 * 60 * 24));

                if ($debugFlag === true)
                {
                    echo "<br>   if ($age > $retentionDays)";
                    echo "<br><b>Add $chronMode: $chronMode logDir:$logDir file: $file</b>";
                }

                if ($age > $retentionDays)
                {
                    $filePath = $logDir . "/" . $file;

                    if ($debugFlag === true)
                    {
                        echo "<br><b>Add $chronMode: $chronMode filePath:$filePath</b>";
                    }

                    if (!is_file($filePath)) {
                        // Datei wurde zwischenzeitlich gelöscht → überspringen
                        continue;
                    }

                    if ($chronMode === "zip")
                    {
                        if ($zip->addFile($filePath, $file) === true) {
                            $toDelete[] = $filePath; // Datei merken und erst nach dem ZIP-Schließen löschen
                            $archivedFiles++;
                        }
                    }
                    elseif ($chronMode === "delete")
                    {
                        unlink($filePath);
                        $deletedFiles++;
                    }
                }
            }
        }
    }

    if ($chronMode === "zip")
    {
        if ($zip->numFiles > 0)
        {
            $zip->close(); // ZIP-Archiv schließen, bevor Dateien gelöscht werden
        }
        else
        {
            // nichts archiviert → ZIP verwerfen
            $zip->close();
            @unlink($zipName);
        }

        //Fallback
        //Dateien aus dem Archiv löschen, wenn nicht schon vorher gelöscht
        foreach ($toDelete as $file)
        {
            @unlink($file);
        }
    }

    $returnArray['archivedFiles'] = $archivedFiles;
    $returnArray['deletedFiles']  = $deletedFiles;

    return $returnArray;
}
function isMobile(): bool
{
    return (bool) preg_match('/(android|iphone|ipad|ipod|blackberry|windows phone)/i', $_SERVER['HTTP_USER_AGENT']);
}
function checkBgTask($task)
{
    return shell_exec(getTaskCmd($task));
}
function setTxQueue($txQueueData): bool
{
    $sendQueueMode = getParamData('sendQueueMode');
    $loraIP        = getParamData('loraIp');
    $doLogEnable   = getParamData('doLogEnable');
    $logfile       = 'log/tx_data_' . date('Ymd') . '.log';
    $fileLogJson   = 'log/tx_json_data_' . date('Ymd') . '.log';
    $errorLogfile  = 'log/error_tx_data_' . date('Ymd') . '.log';
    $sendQueueMode = $sendQueueMode == '' ? 0 : $sendQueueMode;

    #Eliminiere Hops in Quelle und Ziel
    $txQueueData['txDst'] = explode(',', $txQueueData['txDst'])[0];
    $debugFlag            = false;

    if ($debugFlag === true)
    {
        echo "<br>sendQueueMode:$sendQueueMode";
        echo "<br>doLogEnable:$doLogEnable";
    }

    if ($sendQueueMode == 0)
    {
        #Workaround da Anführungszeichen derzeit via UDP nicht übertragen werden. Möglicher FW Bug
        $msgText              = str_replace('"', '``', $txQueueData['txMsg']); // tausche mit Accent-Aigu
        $arraySendUdp['type'] = trim($txQueueData['txType']);
        $arraySendUdp['dst']  = trim($txQueueData['txDst']);
        $arraySendUdp['msg']  = trim($msgText);

        if ($arraySendUdp['type'] == '' && $arraySendUdp['dst'] == '' && $arraySendUdp['msg'] == '')
        {
            echo '<span class="failureHint">Es fehlen Daten im TX-Array bei Funktion: setTxQueue!</span>';

            if ($doLogEnable == 1)
            {
                $messageRaw = trim($txQueueData['txType'])." ". trim($txQueueData['txDst'])." ". trim($msgText);

                // Daten formatieren
                $dataLogTx  = "Es fehlen Daten im TX-Array bei Funktion: setTxQueue!\n";
                $dataLogTx .= date('Y-m-d H:i:s') . ': ' . "$messageRaw\n";

                // Json-Daten in Datei speichern
                file_put_contents($logfile, $dataLogTx, FILE_APPEND);
            }

            return false;
        }

        if ($doLogEnable == 1)
        {
            $messageRaw = trim($txQueueData['txType'])." ". trim($txQueueData['txDst'])." ". trim($msgText);

            // Daten formatieren
            $dataLogTx = date('Y-m-d H:i:s') . ': ' . "$messageRaw\n";

            // Json-Daten in Datei speichern
            file_put_contents($logfile, $dataLogTx, FILE_APPEND);
        }

        $message = json_encode($arraySendUdp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))
        {
            socket_sendto($socket, $message, strlen($message), 0, $loraIP, 1799);
            socket_close($socket);

            if ($debugFlag === true)
            {
                echo "<br>Nachricht versand: " . trim($txQueueData['txType']) . " " . trim(
                        $txQueueData['txDst']
                    ) . " " . trim($msgText);
            }

            if ($doLogEnable == 1)
            {
                // Daten formatieren
                $dataLogJson = date('Y-m-d H:i:s') . ': ' . "$message\n";

                // Json-Daten in Datei speichern
                file_put_contents($fileLogJson, $dataLogJson, FILE_APPEND);
            }
        }
        else
        {
            $data = date('Y-m-d H:i:s') . ': ' . "Kann Socket nicht erstellen. Abbruch!";
            file_put_contents($errorLogfile, $data, FILE_APPEND);

            echo '<span class="failureHint">' . $data . '</span>';
            return false;
        }

        return true;
    }

    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename        = pathinfo(getcwd())['basename'];
    $dbFilenameSub   = '../database/tx_queue.db';
    $dbFilenameRoot  = 'database/tx_queue.db';
    $dbFilename      = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $insertTimestamp = date('Y-m-d H:i:s');

    if ($txQueueData['txType'] == '' || $txQueueData['txDst'] == '' || $txQueueData['txMsg'] == '')
    {
        return false;
    }

    #Workaround da Anführungszeichen derzeit via UDP nicht übertragen werden. Möglicher FW Bug
    $txQueueData['txMsg'] = str_replace('"', '``', $txQueueData['txMsg']); // tausche mit Accent-Aigu

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    $txTimestamp     = '0000-00-00 00:00:00';
    $txType          = SQLite3::escapeString($txQueueData['txType'] ?? 'msg');
    $txDst           = SQLite3::escapeString($txQueueData['txDst'] ?? '');
    $txMsg           = SQLite3::escapeString($txQueueData['txMsg'] ?? '');
    $txFlag          = 0;

    $sql = "REPLACE INTO txQueue (insertTimestamp,
                                  txTimestamp, 
                                  txType, 
                                  txDst, 
                                  txMsg, 
                                  txFlag
                                 )
                          VALUES ('$insertTimestamp',
                                  '$txTimestamp',
                                  '$txType',
                                  '$txDst',
                                  '$txMsg',
                                  '$txFlag'
                                 );
                    ";

    $logArray   = array();
    $logArray[] = "setTxQueue: Database: $dbFilename";
    $logArray[] = "setTxQueue: insertTimestamp: $insertTimestamp";
    $logArray[] = "setTxQueue: txTimestamp: $txTimestamp";
    $logArray[] = "setTxQueue: txType: $txType";
    $logArray[] = "setTxQueue: txDst: $txDst";
    $logArray[] = "setTxQueue: txMsg: $txMsg";
    $logArray[] = "setTxQueue: txFlag: $txFlag";

    $res = safeDbRun( $db,  $sql, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

    return true;
}
function getTxQueue()
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename          = pathinfo(getcwd())['basename'];
    $dbFilenameSub     = '../database/tx_queue.db';
    $dbFilenameRoot    = 'database/tx_queue.db';
    $dbFilename        = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $returnValue       = array();
    $minSecondsLastMsg = 600; //Suche rückwirkend max. 600 Sekunden (10min)

    // Prüfen, ob Datenbank existiert
    if (!file_exists($dbFilename))
    {
        return false;
    }

    $db = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden

    $sql = "SELECT * 
              FROM txQueue AS tx
             WHERE tx.txFlag = 0
               AND strftime('%s', 'now', 
                    CASE 
                        -- Sommerzeit (letzter Sonntag im März bis letzter Sonntag im Oktober)
                        WHEN (
                            -- Berechnung für Sommerzeitbeginn: letzter Sonntag im März
                            (strftime('%m', 'now') = '03' 
                             AND strftime('%d', 'now') >= strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0') 
                             AND strftime('%H:%M', 'now') >= '02:00') 
                            OR 
                            -- Sommerzeit: Monate April bis September
                            (strftime('%m', 'now') IN ('04', '05', '06', '07', '08', '09'))
                            OR 
                            -- Berechnung für Sommerzeitende: letzter Sonntag im Oktober
                            (strftime('%m', 'now') = '10' 
                             AND strftime('%d', 'now') < strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0'))
                            ) 
                            THEN '+2 hours'  -- UTC → MESZ (+2h)
                            ELSE '+1 hours'  -- UTC → MEZ (+1h)
                    END
                    ) - strftime('%s', insertTimestamp) <= $minSecondsLastMsg
          ORDER BY tx.txQueueId
             LIMIT 1;
       ";

    $logArray   = array();
    $logArray[] = "getTxQueue: Database: $dbFilename";
    $logArray[] = "getTxQueue: minSecondsLastMsg: $minSecondsLastMsg";

    $resTxQueue = safeDbRun( $db,  $sql, 'query', $logArray);

    if ($resTxQueue === false)
    {
        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    $dsData = $resTxQueue->fetchArray(SQLITE3_ASSOC);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if (empty($dsData) === false)
    {
        $returnValue['txQueueId'] = $dsData['txQueueId'] ?? 0;
        $returnValue['txType']    = $dsData['txType'] ?? '';
        $returnValue['txDst']     = $dsData['txDst'] ?? '';
        $returnValue['txMsg']     = $dsData['txMsg'] ?? '';
    }
    else
    {
        return false;
    }

    return $returnValue;
}
function updateTxQueue($txQueueId): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/tx_queue.db';
    $dbFilenameRoot = 'database/tx_queue.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $timeStamps     = date('Y-m-d H:i:s');

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    $txQueueId = SQLite3::escapeString($txQueueId);

    $sql = "UPDATE txQueue
               SET txFlag = 1,
                   txTimestamp = '$timeStamps'
             WHERE txQueueId = '$txQueueId';
          ";

    $logArray   = array();
    $logArray[] = "updateTxQueue: Database: $dbFilename";
    $logArray[] = "updateTxQueue: timeStamps: $timeStamps";
    $logArray[] = "updateTxQueue: txQueueId: $txQueueId";

    $res = safeDbRun( $db,  $sql, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

    return true;
}
function getStatusIcon(string $status, bool $withLabel = false): string
{
    # HTML-Entity-Format
    $icons = [
        'inactive'   => ['symbol' => '&#x1F534;', 'label' => 'Inaktiv'],        // 🔴
        'active'     => ['symbol' => '&#x1F7E2;', 'label' => 'Aktiv'],          // 🟢
        'wait'       => ['symbol' => '&#x1F7E1;', 'label' => 'Warten'],         // 🟡
        'ok'         => ['symbol' => '&#x2705;', 'label' => 'Aktiv'],           // ✅
        'checked'    => ['symbol' => '&#10062;', 'label' => 'Checked'],         // ❎
        'error'      => ['symbol' => '&#x274C;', 'label' => 'Fehler'],          // ❌
        'warning'    => ['symbol' => '&#9888;&#65039;', 'label' => 'Warnung'],  // ⚠️
        'blocked'    => ['symbol' => '&#x26D4;', 'label' => 'Blockiert'],       // ⛔
        'on'         => ['symbol' => '&#x1F51B;', 'label' => 'Eingeschaltet'],  // 🔛
        'off'        => ['symbol' => '&#x1F4F4;', 'label' => 'Ausgeschaltet'],  // 📴
        'attention'  => ['symbol' => '&#10071;', 'label' => 'Achtung'],         // ❗


        'locked'     => ['symbol' => '&#128274;', 'label' => 'Gesperrt'],       // 🔒
        'unlocked'   => ['symbol' => '&#128275;', 'label' => 'Entsperrt'],      // 🔓
        'clock'      => ['symbol' => '&#128338;', 'label' => 'Uhr'],            // 🕒
        'battery'    => ['symbol' => '&#128267;', 'label' => 'Einstellung'],    // 🔋
        'watch'      => ['symbol' => '&#8986;', 'label' => 'Uhr'],              // ⌚
        'hourglass'  => ['symbol' => '&#8987;', 'label' => 'Uhrenglas'],        // ⌛
        'star'       => ['symbol' => '&#11088;', 'label' => 'Stern'],           // ⭐
        'trash   '   => ['symbol' => '&#128465;&#65039;', 'label' => 'Papierkorb'],  // 🗑️
        'unknown'    => ['symbol' => '&#x2753;', 'label' => 'Unbekannt'],            // ❓

        'right_triangle3' => ['symbol' => '&#9654;', 'label' => ''], // ⏵
        'right_triangle'  => ['symbol' => '&#9656;', 'label' => ''], // ⏵
        'toolbox'         => ['symbol' => '&#129520;', 'label' => ''], // 🧰


        'configuration' => ['symbol' => '&#128736;&#65039;', 'label' => 'menu.einstellung'], // 🛠️
        'generally2'    => ['symbol' => '&#9881;&#65039;', 'label' => 'menu.allgemein'],             // ⚙️
        'generally3'    => ['symbol' => '&#128295;', 'label' => 'menu.allgemein'],             // 🔧
        'generally4'    => ['symbol' => '&#129535;', 'label' => 'menu.allgemein'],             // 🧿
        'generally'     => ['symbol' => '&#128261;', 'label' => 'menu.allgemein'],             // 🔅
        'interval'      => ['symbol' => '&#9201;&#65039;', 'label' => 'menu.send-queue'],       // ⏱️
        'notification'  => ['symbol' => '&#128276;', 'label' => 'menu.notification'],    // 🔔️
        'keyword'       => ['symbol' => '&#128278;', 'label' => 'menu.keyword'],             // 🏷️
        'update'        => ['symbol' => '&#128260;', 'label' => 'menu.update'],              // 🔄
        'restore'       => ['symbol' => '&#128257;', 'label' => 'menu.restore'],     // 🔁
        'lora-info'     => ['symbol' => '&#128225;&#65039;', 'label' => 'menu.lora-info'],           // 📡
        'ping-lora'     => ['symbol' => '&#128246;', 'label' => 'menu.ping-lora'],           // 📶
        'debug-info'    => ['symbol' => '&#128030;', 'label' => 'menu.debug-info'],          // 🐞
        'edit_translation'    => ['symbol' => '&#127760;', 'label' => 'menu.edit_translation'],          // 🌐

        'groups'        => ['symbol' => '&#128101;&#65039;', 'label' => 'menu.gruppen'],  // 👥
        'groups_define' => ['symbol' => '&#128450;&#65039;', 'label' => 'menu.gruppenfilter'],  // 🗂️

        'data-purge'         => ['symbol' => '&#129529;&#65039;', 'label' => 'menu.data-purge'],   // 🧹
        'data-purge-manuell' => ['symbol' => ' &#9995;&#65039;', 'label' => 'menu.purge-manuell'], // ✋
        'data-purge-auto'    => ['symbol' => '&#129302;&#65039;', 'label' => 'menu.purge-auto'],   // 🤖

        'sensors'    => ['symbol' => '&#127777;&#65039;', 'label' => 'menu.sensoren'],  // 🌡️
        'sensordata' => ['symbol' => '&#128202;', 'label' => 'menu.sensordaten'],  // 📊
        'threshold'  => ['symbol' => '&#129514;', 'label' => 'menu.schwellwerte'],  // 🧪
        'plot'       => ['symbol' => '&#128201;', 'label' => 'menu.plot'],  // 📉
        'gps'        => ['symbol' => '&#x1F6F0;&#65039;', 'label' => 'menu.gps-info'],  // 🛰️

        'mheard'      => ['symbol' => '&#128066;&#65039;', 'label' => 'MHeard'],  // 👂
        'mheard-page' => ['symbol' => '&#x1F3A7;&#65039;', 'label' => 'MHeard-Lokal'],  // 🎧
        'mheard-osm'  => ['symbol' => '&#x1F5FA;&#xFE0F;', 'label' => 'MHeard-Map'],  // 🗺️
        'mheard-osm-full'  => ['symbol' => '&#x1F5BC;&#xFE0F;', 'label' => 'Fullsize-Map'],  // 🖼️

        'beacon'   => ['symbol' => ' &#x1F9ED;&#65039;', 'label' => 'Bake'],  // 🧭
        'send-cmd' => ['symbol' => '&#128228;', 'label' => 'menu.sende-befehl'],  // 📤
        'message'  => ['symbol' => '&#128172;&#65039;', 'label' => 'menu.message'],  // 💬
        'about'    => ['symbol' => '&#8505;&#65039;', 'label' => 'menu.about'],  // ℹ️

        # https://www.amp-what.com/unicode/search/lock
        'key'                 => ['symbol' => '&#128273;', 'label' => 'key'],  //🔑
        'closedLockKey'       => ['symbol' => '&#128272;', 'label' => 'closedLockKey'],  //🔐
        'chartUpwardsTrend'   => ['symbol' => '&#128200;', 'label' => 'chartUpwardsTrend'],  //📈
        'chartDownwardsTrend' => ['symbol' => '&#128201;', 'label' => 'chartDownwardsTrend'],  //📉

        'de'   => ['symbol' => '&#x1F1E9;&#x1F1EA;', 'label' => 'Deutsch'],
        'en'   => ['symbol' => '&#x1F1EC;&#x1F1E7;', 'label' => 'Englisch'],
        'fr'   => ['symbol' => '&#x1F1EB;&#x1F1F7;', 'label' => 'Französisch'],
        'it'   => ['symbol' => '&#x1F1EE;&#x1F1F9;', 'label' => 'Italienisch'],
        'es'   => ['symbol' => '&#x1F1EA;&#x1F1F8;', 'label' => 'Spanisch'],
        'nl'   => ['symbol' => '&#x1F1F3;&#x1F1F1;', 'label' => 'Niederländisch'],
        'us'   => ['symbol' => '&#x1F1FA;&#x1F1F8;', 'label' => 'Amerikanisch']
    ];

    $key = strtolower($status);

    if (!isset($icons[$key]))
    {
        $key = 'unknown';
    }

    $entry = $icons[$key];

    return $withLabel
        ? '<span class="menu-icon">' . $entry['symbol'] . '</span> ' .'<span data-i18n="'.htmlspecialchars($entry['label']).'">'. htmlspecialchars($entry['label']). '</span> '
        : '<span class="menu-icon">' . $entry['symbol'] . '</span>';
}
function stopBgProcess($paramBgProcess)
{
    $osIssWindows   = chkOsIsWindows();
    $taskBg         = $paramBgProcess['task'] ?? '';
    $taskBg         = $taskBg == '' ? 'udp' : $taskBg;
    $checkBgTaskCmd = getTaskCmd($taskBg);
    $debugFlag      = false;

    switch ($taskBg)
    {
        case 'udp':
            $bgPidFile = UPD_PID_FILE;
            $bgTaskPid = getParamData('udpReceiverPid');
            break;
        case 'cron':
            $bgPidFile = CRON_PID_FILE;
            $bgTaskPid = getParamData('cronLoopPid');
            break;
        case 'cronBeacon':
            $bgPidFile = CRON_BEACON_PID_FILE;
            $bgTaskPid = getParamData('cronBeaconLoopPid');
            break;
        case 'cronMheard':
            $bgPidFile = MHEARD_CRON_PID_FILE;
            $bgTaskPid = getParamData('cronLoopMheardPid');
            break;
        case 'cronGetSensorData':
            $bgPidFile = GET_SENSOR_DATA_CRON_PID_FILE;
            $bgTaskPid = getParamData('cronLoopGetSensorDataPid');
            break;
        default:
            $bgPidFile = CRON_PID_FILE;
            $bgTaskPid = getParamData('cronLoopPid');
    }

    #Pidfile liegt im Log Verzeichnis
    if ($taskBg  == 'udp' || $taskBg  == 'cron' || $taskBg == 'cronBeacon' || $taskBg == 'cronMheard' || $taskBg == 'cronGetSensorData')
    {
        $execDir         = 'log';
        $basename        = pathinfo(getcwd())['basename'];
        $cronPidFileSub  = '../' . $execDir . '/' . $bgPidFile;
        $cronPidFileRoot = $execDir . '/' . $bgPidFile;
        $bgPidFile       = $basename == 'menu' ? $cronPidFileSub : $cronPidFileRoot;
    }

    $bgTaskKillCmd = getTaskKillCmd($taskBg);
    $taskResultBg  = shell_exec($checkBgTaskCmd);

    if ($debugFlag === true)
    {
        echo "<br>bgTaskPid: $bgTaskPid";
        echo "<br>#652#bgTask: $taskBg";
        echo "<br>#652#bgPidFile: $bgPidFile";
        echo "<br>#652#checkBgTaskCmd: $checkBgTaskCmd";
        echo "<br>#652#bgTaskKillCmd: $bgTaskKillCmd";
        echo "<br>#652#taskResultBg: $taskResultBg<br>vardump: ";
        var_dump($taskResultBg);
    }

    #Process is offline
    if ($taskResultBg == '')
    {
        return $taskResultBg;
    }

    if ($osIssWindows === true)
    {
        #Beende Hintergrundprozess php.exe in Windows
        exec($bgTaskKillCmd);

        @unlink($bgPidFile);
    }
    else
    {
        #Beende Hintergrundprozess in Linux
        #Ermittel PID anhand des Skript-Namens, um
        #andere Bg Prozesse nicht aus Versehen zu beenden.
        $taskResultBg = shell_exec($checkBgTaskCmd);

        #Wenn PID nicht ermittelt wurde, ist der Task schon beendet
        #oder wurde nicht gestartet.
        if ($taskResultBg == '')
        {
            echo "<br>Kill Task: Task PID konnte nicht ermittelt werden!";
            echo "<br>checkTaskCmd: $checkBgTaskCmd";
            echo "<br>taskResult PID: " . $taskResultBg;

            return false;
        }
        else
        {
            exec($bgTaskKillCmd);
            @unlink($bgPidFile);
        }
    }

    #Gib 1sek Zeit
    sleep(2);

    #Prüfe, ob Prozess wirklich beendet wurde
    if ($osIssWindows === true)
    {
        $taskResult = shell_exec('tasklist /FI "PID eq ' . $bgTaskPid . '" | findstr /I "php.exe"');
    }
    else
    {
        $taskResult = shell_exec($checkBgTaskCmd);
    }

    if ($taskResult != '')
    {
        echo "<br>Task wurde nicht beendet!";
        echo "<br>checkTaskCmd: $checkBgTaskCmd";
        echo "<br>taskResult PID: " . $taskResult;

        return false;
    }

    return true;
}
function startBgProcess($paramStartBgProcess)
{
    $osIsWindows = chkOsIsWindows();
    $taskBg      = $paramStartBgProcess['task'] ?? 'udp';
    $taskCmd     = getTaskCmd($taskBg);
    $taskResult  = shell_exec($taskCmd);
    $debugFlag   = false;

    switch ($taskBg)
    {
        case 'udp':
            $bgProcFile = UDP_PROC_FILE;
            break;
        case 'cron':
            $bgProcFile = CRON_PROC_FILE;
            break;
        case 'cronMheard':
            $bgProcFile = MHEARD_CRON_PROC_FILE;
            break;
        case 'cronGetSensorData':
            $bgProcFile = GET_SENSOR_DATA_CRON_PROC_FILE;
            break;
        case 'cronBeacon':
            $bgProcFile = CRON_BEACON_PROC_FILE;
            break;
        default:
            $bgProcFile = CRON_PROC_FILE;
    }

    #Ermittel korrekte Aufruf Pfad unter Linux,
    #wenn aus Submenü gestartet wird.
    #Hier stimmen dann die relativen Pfade nicht mehr
    #Wichtig ist das "chdir(__DIR__);" im aufzurufenden Skript als Erstes steht!
    #Sonst stimmen die reaktiven Pfade nicht mehr.
    if ($osIsWindows === false)
    {
        $basename       = pathinfo(getcwd())['basename'];
        $bgProcFileSub  = realpath(__DIR__ . '/../') . '/' . $bgProcFile;
        $bgProcFile     = $basename == 'menu' ? $bgProcFileSub : $bgProcFile;
    }

    if ($debugFlag === true)
    {
        echo "<br>#1956#startBgProcess# task: " . $taskBg . ' Task-Result:' . $taskResult;
        echo "<br>#1956#startBgProcess# taskCmd: " . $taskCmd;
        echo "<br>#1956#startBgProcess# bgProcFile: " . $bgProcFile;
        echo "<br>osIsWindows:";
        var_dump($osIsWindows);
    }

    if (empty($taskResult))
    {
        if($osIsWindows === true)
        {
            #Unter Windows über task_bg.php mit Curl Starten
            callWindowsBackgroundTask($bgProcFile);
        }
        else
        {
            #Unter Linux direkt starten
            exec('nohup php ' . $bgProcFile . ' >/dev/null 2>&1 &');
        }

        sleep(2);

        $checkTaskCmd = getTaskCmd($taskBg);
        $taskResult   = shell_exec($checkTaskCmd);

        if ($debugFlag === true)
        {
            echo "<br>#2632#startBgProcess#Taskresult taskResult:" . $taskResult;
        }
    }

    return $taskResult;
}
function callWindowsBackgroundTask($taskFile, $execDir = ''): bool
{
    // Holt den Projekt-Root aus SCRIPT_NAME (NICHT SCRIPT_FILENAME!)
    $protocol    = (empty($_SERVER['HTTPS']) ? 'http' : 'https');
    $host        = $_SERVER['HTTP_HOST'];
    $scriptName  = $_SERVER['SCRIPT_NAME']; // z.B. /meshdash/menu/index.php
    $projectRoot = explode('/', trim($scriptName, '/'))[0]; // ergibt 'meshdash'
    $baseUrl     = $protocol . '://' . $host . '/' . $projectRoot;
    $triggerLink = $baseUrl . '/task_bg.php';

    $postFields = array(
        'taskFile' => "$taskFile",
        'execDir' => "$execDir",
    );

    $debugFlag = false;

    #Starte Trigger
    $ch = curl_init();

    # Set Curl Options
    curl_setopt($ch, CURLOPT_URL, $triggerLink);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100); // Warte max. 100 ms und beende Verbindung
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

    #Ignoriere Timeout Meldung da so gewollt
    if (curl_exec($ch) === false && curl_errno($ch) != 28)
    {
        echo 'Curl error: ' . curl_error($ch);
        echo 'Curl error: ' . curl_errno($ch);
    }

    curl_close($ch);

    if ($debugFlag === true)
    {
        echo "<br> Debug: callWindowsBackgroundTask";
        echo "<br>triggerLink:$triggerLink";
        echo "<br>taskFile:$taskFile";

        echo "<br>#Postfields#<br><pre>";
        print_r($postFields);
        echo "</pre>";

        echo "<br>#curlResult#<br><pre>";
        print_r($ch);
        echo "</pre>";

        return true;
    }

    return true;
}
function callMessagePage(): bool
{
    $triggerLink = BASE_PATH_URL . 'message.php';

    $debugFlag = false;

    #Starte Trigger
    $ch = curl_init();

    # Set Curl Options
    curl_setopt($ch, CURLOPT_URL, $triggerLink);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100); // Warte max. 100 ms und beende Verbindung

    #Ignoriere Timeout Meldung da so gewollt
    if (curl_exec($ch) === false && curl_errno($ch) != 28)
    {
        echo 'Curl error: ' . curl_error($ch);
        echo 'Curl error: ' . curl_errno($ch);

        $callMsgText = "Error: Message.php NOT Triggered via Curl at " . date('Y-m-d H:i:s') . "\n";
        $callMsgText .= 'Curl error: ' . curl_error($ch);
        $callMsgText .= 'Curl error: ' . curl_errno($ch);
        $callMsgText .= 'triggerLink:' . $triggerLink;

        if ($debugFlag === true)
        {
            file_put_contents('log/debug_call_message.log', $callMsgText, FILE_APPEND);
        }

        return false;
    }

    $callMsgText = "Success: Message.php Triggered via Curl at " . date('Y-m-d H:i:s') . "\n";
    $callMsgText .= 'triggerLink:' . $triggerLink;

    if ($debugFlag === true)
    {
        file_put_contents('log/debug_call_message.log', $callMsgText, FILE_APPEND);
    }

    curl_close($ch);

    if ($debugFlag === true)
    {
        echo "<br> Debug: callMessage";
        echo "<pre>";
        print_r($ch);
        echo "</pre>";

        return true;
    }

    return true;
}
function debugLog($logArray): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename        = pathinfo(getcwd())['basename'];
    $logFilenameSub  = '../log/debug_log';
    $logFilenameRoot = 'log/debug_log';
    $logFilename     = $basename == 'menu' ? $logFilenameSub : $logFilenameRoot;

    $now         = DateTime::createFromFormat('U.u', microtime(true));
    $dts         = $now->format("Ymd_His_v");
    $logFilename = $logFilename . '_' . $dts . '.log';

    if (!is_array($logArray) || empty($logArray) || count($logArray) == 0)
    {
        return false;
    }

    foreach ($logArray as $logItem)
    {
        $data = $dts . ': ' . $logItem . "\n";
        file_put_contents($logFilename, $data, FILE_APPEND);
    }

    return true;
}

function safeDbRun(SQLite3 $db, string $sql, string $method = 'exec', array $logArray = [], int $retries = SQLITE3_LOCK_RETRY_MAX_ATTEMPTS, int $waitMs = SQLITE3_LOCK_RETRY_DELAY_MS)
{
    $method = strtolower($method);

    if (!$db instanceof SQLite3) {
        throw new InvalidArgumentException('Ungültiges SQLite3-Objekt übergeben!');
    }

    if (!is_string($sql) || trim($sql) === '') {
        throw new InvalidArgumentException('SQL-Statement darf nicht leer sein!');
    }

    if ($method !== 'exec' && $method !== 'query') {
        throw new InvalidArgumentException("Methode muss 'exec' oder 'query' sein!");
    }

    for ($i = 0; $i < $retries; $i++)
    {
        if ($method === 'exec')
        {
            $result = $db->exec($sql);

            if ($result === true)
            {
                return true;
            }
        }
        elseif ($method === 'query')
        {
            $result = $db->query($sql);

            if ($result instanceof SQLite3Result)
            {
                return $result;
            }
        }

        if (strpos($db->lastErrorMsg(), 'locked') !== false)
        {
            usleep($waitMs * 1000);
            continue;
        }

        break; // anderer Fehler
    }

    $logArray[] = "safeDbRun: sql: $sql";
    $logArray[] = "safeDbRun: method: $method";
    $logArray[] = "safeDbRun: Error at: " . date('Y-m-d H:i:s');
    $logArray[] = "safeDbRun ErrMsg: " . $db->lastErrorMsg();
    $logArray[] = "safeDbRun ErrNum: " . $db->lastErrorCode();
    $logArray[] = "safeDbRun: SQLITE3_BUSY_TIMEOUT:" . SQLITE3_BUSY_TIMEOUT;
    $logArray[] = "safeDbRun: SQLITE3_LOCK_RETRY_MAX_ATTEMPTS:" . SQLITE3_LOCK_RETRY_MAX_ATTEMPTS;
    $logArray[] = "safeDbRun: SQLITE3_LOCK_RETRY_DELAY_MS:" . SQLITE3_LOCK_RETRY_DELAY_MS;
    $logArray[] = "-----------------------------------------";
    $logArray[] = "LOCK/SQL-Fehler: " . $db->lastErrorMsg();
    debugLog($logArray);

    return false;
}
function showBackups(): void
{
    $maxBackups      = 5; //max. Anzahl Backups
    $maxBackupsCount = 0; //Counter für Backups

    $backupDir = dirname(__DIR__) . '/backup'; // Verzeichnis der Backups

    if (!is_dir($backupDir)) {
        echo "Backup-Verzeichnis nicht gefunden.";
        return;
    }

    $files = glob($backupDir . '/backup_*.zip');
    if (!$files) {
        echo "Keine Backups vorhanden.";
        return;
    }

    // Neueste Backups zuerst
    rsort($files);

    // Ermittele den Download-Pfad:
    // Skript wird z.B. in /meshdash/menu ausgeführt, Backups liegen in /meshdash/backup.
    // Wir nehmen dirname von SCRIPT_NAME, um den Root-Ordner zu erhalten
    $scriptDir    = dirname($_SERVER['SCRIPT_NAME']); // z.B. "/meshdash/menu"
    $baseUrl      = dirname($scriptDir);                // z.B. "/meshdash"
    $downloadBase = $baseUrl . '/backup/';           // z.B. "/meshdash/backup/"

    echo '<div class="scrollable-container">';
    echo '<table class="backupTable">';
    echo '<tr>';
    echo '<th><span data-i18n="submenu.config_update.lbl.date">Datum</span</th>';
    echo '<th><span data-i18n="submenu.config_update.lbl.time">Uhrzeit</span></th>';
    echo '<th><span data-i18n="submenu.config_update.lbl.backup-file">Backup-Datei</span></th>';
    echo '<th>Version</th>';
    echo '<th colspan="2">&nbsp;</th>';
    echo '</tr>';

    foreach ($files as $file)
    {
        $filename = basename($file);
        if (preg_match('/backup_(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})\.zip/', $filename, $matches))
        {
            ++$maxBackupsCount;

            if ($maxBackupsCount > $maxBackups)
            {
                unlink('../backup/' . $filename);
                continue;
            }

            $datum       = "$matches[3].$matches[2].$matches[1]";
            $uhrzeit     = "$matches[4]:$matches[5]:$matches[6]";
            $downloadUrl = $downloadBase . $filename;

            // === Version aus ZIP lesen ===
            $version = '-';
            $zip = new ZipArchive();
            if ($zip->open($file) === true)
            {
                $paramContent = $zip->getFromName('dbinc/param.php');
                if ($paramContent !== false)
                {
                    if (preg_match("/const\s+VERSION\s*=\s*'([^']+)'/", $paramContent, $verMatch))
                    {
                        $version = $verMatch[1];
                    }
                }
                $zip->close();
            }

            echo '<tr>';
            echo '<td>' . $datum . '</td>';
            echo '<td>' . $uhrzeit . '</td>';
            echo '<td class="filename-cell">' . $filename . '</td>';
            echo '<td class="version-cell">' . htmlspecialchars($version) . '</td>';
            echo '<td>';
            echo '<a href="' . $downloadUrl . '">';
            echo '<img src="../image/download_blk.png" class="imageDownload" alt="download">';
            echo '</a>';
            echo '</td>';
            echo '<td>';
            echo '<img src="../image/delete_blk.png" data-delete ="' . $filename . '" class="imageDelete" alt="delete">';
            echo '</td>';
            echo '</tr>';
        }
    }

    echo '</table>';
    echo '</div>';
}
function autoPurgeTable(string $tableName, string $paramEnable, string $paramDaysParam, string $procName, string $dbFile): void
{
    if ((int) getParamData($paramEnable) !== 1) {
        return; // Purge für diese Tabelle deaktiviert
    }

    // Prüfen, ob Purge fällig ist
    if (!isPurgeDue($tableName)) {
        return;
    }

    $daysPurge = (int) getParamData($paramDaysParam);

    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/' . $dbFile;
    $dbFilenameRoot = 'database/' . $dbFile;
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT);

    $anzahlPurgeSel = 0;

    $sqlSelect = "SELECT COUNT(*) AS anzahl
                    FROM $tableName
                   WHERE rowid IN (
                           SELECT rowid
                             FROM $tableName
                            WHERE timestamps < datetime('now', '-$daysPurge days')
                        );";

    $sqlDelete = "DELETE FROM $tableName
                     WHERE rowid IN (
                             SELECT rowid
                               FROM $tableName
                              WHERE timestamps < datetime('now', '-$daysPurge days')
                          );";

    $logArray = ["AutoPurge $tableName Select"];
    $result   = safeDbRun($db, $sqlSelect, 'query', $logArray);

    if ($result !== false) {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $anzahlPurgeSel = $row['anzahl'] ?? 0;
    }

    $db->close();
    unset($db);

    if ($anzahlPurgeSel > 0) {
        if (!tryAcquirePurgeLock($tableName, $paramEnable)) {
            return; // anderer Prozess hält Lock
        }

        $dbWrite = new SQLite3("database/$dbFile");
        $dbWrite->exec('PRAGMA synchronous = NORMAL;');

        $logArray = ["AutoPurge $tableName DELETE"];
        $res      = safeDbRun($dbWrite, $sqlDelete, 'exec', $logArray);

        $dbWrite->close();
        unset($dbWrite);

        releasePurgeLock($tableName);

        if ($res === false) {
            return; // Fehler beim Löschen
        }
    } else {
        markPurgeChecked($tableName, $procName);
    }
}
function autoPurgeData(): bool
{
    autoPurgeTable('meshdash', 'enableMsgPurge', 'daysMsgPurge', 'enableMsgPurge', 'meshdash.db');
    autoPurgeTable('sensordata', 'enableSensorPurge', 'daysSensorPurge', 'enableSensorPurge', 'sensordata.db');

    return true;
}
function setBeaconCronInterval($beaconInterval,$beaconEnabled): bool
{
    $delete                         = $beaconEnabled == 0; // Wenn 0 = true
    $paramCronBeaconProcess['task'] = 'cronBeacon';
    $debugFlag                      = false;

    $execDir              = 'log';
    $basename             = pathinfo(getcwd())['basename'];
    $intervalFilenameSub  = '../' . $execDir . '/' . CRON_BEACON_CONF_FILE;
    $intervalFilenameRoot = $execDir . '/' . CRON_BEACON_CONF_FILE;
    $intervalFilename     = $basename == 'menu' ? $intervalFilenameSub : $intervalFilenameRoot;

    if ($debugFlag === true)
    {
        echo "<br>beaconInterval:$beaconInterval";
        echo "<br>beaconEnabled:$beaconEnabled";
        echo "<br>intervalFilename:$intervalFilename";
        echo "<br>";
    }

    if ($beaconInterval <= 60 && $delete === false)
    {
        file_put_contents($intervalFilename, $beaconInterval);
        startBgProcess($paramCronBeaconProcess);
    }

    if ($delete === true)
    {
        stopBgProcess($paramCronBeaconProcess);
        @unlink($intervalFilename);
    }

    return true;
}
function sqliteGetWALCheckpoint(string $database): bool
{
    #Schreibe Daten aus WAL sofort in DB zurück.
    $database = $database . '.db';

    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename              = pathinfo(getcwd())['basename'];
    $dbFilenameSub         = '../database/' . $database;
    $dbFilenameRoot        = 'database/' . $database;
    $dbFilename            = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $getCheckPointedValues = false;

    if (!file_exists($dbFilename))
    {
        return false;
    }

    // DB readonly öffnen, nur für Lesezugriff
    $db = new SQLite3($dbFilename, SQLITE3_OPEN_READWRITE); // Checkpoint braucht Schreibrechte!
    if (!$db)
    {
        return false;
    }

    // WAL-Checkpoint durchführen
    $result = $db->query("PRAGMA wal_checkpoint(FULL);");

    #liest nur die Rückgabewerte des PRAGMA aus (busy, log, checkpointed).
    if ($result && $getCheckPointedValues === true)
    {
        while ($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            // Optional: Werte ausgeben oder loggen
            // busy, log, checkpointed

            echo "<pre>";
            print_r($row);
            echo "</pre>";

             #Könnte so aussehen.
             #Array
             #(
             #   [busy] => 0          // Anzahl der Verbindungen, die die WAL blockieren.
             #   [log] => 5           // Anzahl der Seiten, die noch im WAL-Log stehen.
             #   [checkpointed] => 20 // Anzahl der Seiten, die gerade zurück in die Haupt-DB geschrieben wurden.
             #)
        }
    }

    // DB schließen
    $db->close();
    unset($db);

    return true;
}
function tryAcquirePurgeLock(string $tableName, string $procName): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/write_mutex.db';
    $dbFilenameRoot = 'database/write_mutex.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(5000);

    $now = time();

    // Prüfen, ob Lock gesetzt oder letzte Purge <24h
    $stmt = $db->prepare(
        "
        SELECT is_locked, last_purge_ts
          FROM purge_lock
         WHERE name = :name
    "
    );
    $stmt->bindValue(':name', $tableName, SQLITE3_TEXT);
    $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($row)
    {
        if ((int) $row['is_locked'] === 1 || $now - (int) $row['last_purge_ts'] < 86400)
        {
            $db->close();

            return false;
        }
        // Lock setzen
        $db->exec("UPDATE purge_lock SET is_locked = 1 WHERE name = '$tableName'");
    }
    else
    {
        // Neuen Eintrag anlegen und lock setzen
        $db->exec(
            "INSERT OR IGNORE INTO purge_lock (name, is_locked, last_purge_ts, proc_name) VALUES ('$tableName', 1, 0, '$procName')"
        );

        // Prüfen, ob wir jetzt den Lock haben
        $stmt = $db->prepare(
            "
                        SELECT is_locked FROM purge_lock WHERE name = :name
                    "
        );
        $stmt->bindValue(':name', $tableName, SQLITE3_TEXT);
        $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if (!$row || (int) $row['is_locked'] !== 1)
        {
            $db->close();

            return false; // Lock konnte nicht gesetzt werden
        }
    }

    $db->close();
    unset($db);

    return true;
}
function releasePurgeLock(string $tableName): void
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/write_mutex.db';
    $dbFilenameRoot = 'database/write_mutex.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(5000);

    $now = time();

    // Lock freigeben und Timestamp aktualisieren
    $stmt = $db->prepare(
        "
        UPDATE purge_lock
           SET is_locked = 0,
               last_purge_ts = :ts
         WHERE name = :name
    "
    );
    $stmt->bindValue(':ts', $now, SQLITE3_INTEGER);
    $stmt->bindValue(':name', $tableName, SQLITE3_TEXT);
    $stmt->execute();

    $db->close();
    unset($db);
}
function isPurgeDue(string $tableName, int $mode = 1): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/write_mutex.db';
    $dbFilenameRoot = 'database/write_mutex.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(5000);

    $stmt = $db->prepare(
        "
        SELECT is_locked, last_purge_ts
          FROM purge_lock
         WHERE name = :name
    "
    );
    $stmt->bindValue(':name', $tableName, SQLITE3_TEXT);
    $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    $db->close();
    unset($db);

    $now = time();

    if (!$row)
    {
        // Kein Eintrag = KEIN Purge fällig. Frei
        return true;
    }

    if ((int) $row['is_locked'] === 1)
    {
        // Gerade ein Purge läuft
        return false;
    }

    if ($mode === 2)
    {
        if ((int) $row['is_locked'] === 0)
        {
            // Gerade läuft kein Purge. Frei
            return true;
        }
    }

    if ($mode === 1)
    {
        if (!empty($row['last_purge_ts']) && ($now - (int) $row['last_purge_ts'] >= 86400))
        {
            // 24h vergangen → Purge fällig
            return true;
        }
    }

    return false; // noch nicht fällig
}
function markPurgeChecked(string $tableName, string $procName): void
{
    # DB-Pfad ermitteln
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/write_mutex.db';
    $dbFilenameRoot = 'database/write_mutex.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(5000);

    $now = time();

    // 1) Prüfen ob Eintrag existiert
    $stmt = $db->prepare("
        SELECT 1
          FROM purge_lock
         WHERE name = :name
         LIMIT 1
    ");
    $stmt->bindValue(':name', $tableName, SQLITE3_TEXT);
    $exists = $stmt->execute()->fetchArray(SQLITE3_NUM);

    if ($exists === false)
    {
        // 2a) Kein Eintrag → INSERT
        $stmt = $db->prepare("
            INSERT  OR IGNORE INTO purge_lock (name, is_locked, last_purge_ts, proc_name)
            VALUES (:name, 0, :ts, :procName)
        ");

        $stmt->bindValue(':procName', $procName, SQLITE3_TEXT);
    }
    else
    {
        // 2b) Eintrag vorhanden → UPDATE
        $stmt = $db->prepare("
            UPDATE purge_lock
               SET last_purge_ts = :ts,
                   is_locked = 0
             WHERE name = :name
        ");
    }

    $stmt->bindValue(':name', $tableName, SQLITE3_TEXT);
    $stmt->bindValue(':ts', $now, SQLITE3_INTEGER);
    $stmt->execute();

    $db->close();
    unset($db);
}
function getPhpExeAndVersion(string $phpExePath = ''): array
{
    if ($phpExePath === '')
    {
        $output   = [];
        $exitCode = 0;

        exec('where php 2>NUL', $output, $exitCode);

        if ($exitCode !== 0 || empty($output))
        {
            return [
                'path'      => null,
                'version'   => null,
                'isPhp8Up'  => false
            ];
        }

        $phpExePath = trim($output[0]);
    }

    $versionOutput = [];
    exec('"' . $phpExePath . '" -v', $versionOutput, $versionExitCode);

    if ($versionExitCode !== 0 || empty($versionOutput))
    {
        return [
            'path'      => $phpExePath,
            'version'   => null,
            'isPhp8Up'  => false
        ];
    }

    // Erste Zeile: "PHP 7.4.33 (cli)"
    $versionLine = $versionOutput[0];

    if (!preg_match('/^PHP\s+(\d+\.\d+\.\d+)\s+\(cli\)/', $versionLine, $m))
    {
        return [
            'path'      => $phpExePath,
            'version'   => null,
            'isPhp8Up'  => false
        ];
    }

    $versionNumber = $m[1];           // z.B. 7.4.33
    $phpVersion    = 'PHP ' . $versionNumber . ' (cli)';

    // Versionsvergleich
    $isPhp8Up = version_compare($versionNumber, '8.0.0', '>=');

    return [
        'path'      => $phpExePath,
        'version'   => $phpVersion,
        'isPhp8Up'  => $isPhp8Up
    ];
}
function getPidFromCmd(?string $cmd)
{
    if(!$cmd)
    {
        return false;
    }

    #Wenn Linux hat cmd schon die PID
    if (chkOsIsWindows() === false)
    {
        return $cmd;
    }

    #preg_match('/PID eq (\d+)/i', $cmd, $matches);
    preg_match('/php\.exe\s+(\d+)/i', $cmd, $matches);
    return $matches[1] ?? false;
}

function acquireAutoPurgeLock(): bool
{
    $lockFile = AUTO_PURGE_LOCK_FILE;

    // Prüfen, ob Lock existiert
    if (file_exists($lockFile))
    {
        $lockTime = @filemtime($lockFile);
        if ($lockTime === false)
        {
            // Datei existiert, aber Zugriff verweigert, ignorieren
        }
        else
        {
            $age = time() - $lockTime;
            if ($age < AUTO_PURGE_LOCK_TIMEOUT)
            {
                return false; // Lock noch gültig
            }
            // Lock abgelaufen → alte Datei entfernen
            @unlink($lockFile);
        }
    }

    // Lock atomar erzeugen
    $fp = @fopen($lockFile, 'x'); // "x" erstellt nur, wenn Lock-File nicht existiert
    if ($fp === false)
    {
        return false; // Ein anderer Prozess hat gerade Lock erzeugt
    }

    fwrite($fp, date('Y-m-d H:i:s'));
    fclose($fp);
    return true;
}

function releaseAutoPurgeLock(): void
{
    $lockFile = AUTO_PURGE_LOCK_FILE;
    if (file_exists($lockFile)) {
        @unlink($lockFile);
    }
}