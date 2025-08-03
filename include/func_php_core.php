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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
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
function setThTempData($arrayParam): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/sensor_th_temp.db';
    $dbFilenameRoot = 'database/sensor_th_temp.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $timeStamps     = date('Y-m-d H:i:s');

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    $sensorThTempIntervallMin = $arrayParam['sensorThTempIntervallMin'];

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
    $sensorThTempIntervallMin = SQLite3::escapeString($sensorThTempIntervallMin);

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
                                           sensorThTempIntervallMin,
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
                             '$sensorThTempIntervallMin',
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

    $res = safeDbRun( $db,  $sqlTemp, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
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
function disableAllIna226Sensors(): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/sensor_th_ina226.db';
    $dbFilenameRoot = 'database/sensor_th_ina226.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $timeStamps     = date('Y-m-d H:i:s');

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    $sql = "REPLACE INTO sensorThIna226 (sensorThIna226Id,
                                         timeStamps,
                                         sensorThIna226vBusEnabled, 
                                         sensorThIna226vShuntEnabled, 
                                         sensorThIna226vCurrentEnabled, 
                                         sensorThIna226vPowerEnabled
                                        )
                                 VALUES ('1',
                                         '$timeStamps',
                                         '0',
                                         '0',
                                         '0', 
                                         '0'
                                         );
                    ";

    $logArray = array();
    $logArray[] = "disableAllIna226Sensors: Database: $dbFilename";

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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

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
function setMheardData($heardData): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/mheard.db';
    $dbFilenameRoot = 'database/mheard.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $mhTimeStamps   = date('Y-m-d H:i:s');

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');

    foreach ($heardData AS $key)
    {
        $callSign = SQLite3::escapeString($key['callSign'] ?? '');
        $date     = SQLite3::escapeString($key['date'] ?? '');
        $time     = SQLite3::escapeString($key['time'] ?? '');
        $mhType   = SQLite3::escapeString($key['mhType'] ?? '');
        $hardware = SQLite3::escapeString($key['hardware'] ?? '');
        $mod      = SQLite3::escapeString($key['mod'] ?? '');
        $rssi     = SQLite3::escapeString($key['rssi'] ?? '');
        $snr      = SQLite3::escapeString($key['snr'] ?? '');
        $dist     = SQLite3::escapeString($key['dist'] ?? '');
        $pl       = SQLite3::escapeString($key['pl'] ?? '');
        $m        = SQLite3::escapeString($key['m'] ?? '');

        $sql = "REPLACE INTO mheard (timestamps, 
                                     mhCallSign, 
                                     mhDate, 
                                     mhTime, 
                                     mhType,
                                     mhHardware, 
                                     mhMod, 
                                     mhRssi, 
                                     mhSnr, 
                                     mhDist, 
                                     mhPl, 
                                     mhM
                                    )
                             VALUES ('$mhTimeStamps',
                                     '$callSign',
                                     '$date',
                                     '$time',
                                     '$mhType',
                                     '$hardware',
                                     '$mod',
                                     '$rssi',
                                     '$snr',
                                     '$dist',
                                     '$pl',
                                     '$m'
                                    );
                    ";

        $logArray   = array();
        $logArray[] = "setMheardData: Database: $dbFilename";

        $res = safeDbRun( $db,  $sql, 'exec', $logArray);

        if ($res === false)
        {
            #Close and write Back WAL
            $db->close();
            unset($db);

            return false;
        }
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return true;
}
function setSensorData($sensorData): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/sensordata.db';
    $dbFilenameRoot = 'database/sensordata.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $timeStamps     = date('Y-m-d H:i:s');

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');

    $bme280         = SQLite3::escapeString($sensorData['BME(P)280'] ?? '');
    $bme680         = SQLite3::escapeString($sensorData['BME680'] ?? '');
    $mcu811         = SQLite3::escapeString($sensorData['MCU811'] ?? '');
    $lsp33          = SQLite3::escapeString($sensorData['LPS33'] ?? '');
    $oneWire        = SQLite3::escapeString($sensorData['ONEWIRE'] ?? '');
    $tout           = SQLite3::escapeString($sensorData['TOUT'] ?? '');
    $temp           = SQLite3::escapeString($sensorData['TEMP'] ?? '');
    $hum            = SQLite3::escapeString($sensorData['HUM'] ?? '');
    $qfe            = SQLite3::escapeString($sensorData['QFE'] ?? '');
    $qnh            = SQLite3::escapeString($sensorData['QNH'] ?? '');
    $altAsl         = SQLite3::escapeString($sensorData['ALT asl'] ?? '');
    $gas            = SQLite3::escapeString($sensorData['GAS'] ?? '');
    $eCo2           = SQLite3::escapeString($sensorData['eCO2'] ?? '');
    $ina226vBus     = SQLite3::escapeString($sensorData['vBUS'] ?? '');
    $ina226vShunt   = SQLite3::escapeString($sensorData['vSHUNT'] ?? '');
    $ina226vCurrent = SQLite3::escapeString($sensorData['vCURRENT'] ?? '');
    $ina226vPower   = SQLite3::escapeString($sensorData['vPOWER'] ?? '');

    $sql = "REPLACE INTO sensordata (timestamps,
                                     bme280,
                                     bme680,
                                     mcu811,
                                     lsp33,
                                     oneWire,
                                     temp,
                                     tout,
                                     hum,
                                     qfe,
                                     qnh,
                                     altAsl,
                                     gas,
                                     eCo2,
                                     ina226vBus,
                                     ina226vShunt,
                                     ina226vCurrent,
                                     ina226vPower
                                    )
                             VALUES ('$timeStamps',
                                     '$bme280',
                                     '$bme680',
                                     '$mcu811',
                                     '$lsp33',
                                     '$oneWire',
                                     '$temp',
                                     '$tout',
                                     '$hum',
                                     '$qfe',
                                     '$qnh',
                                     '$altAsl',
                                     '$gas',
                                     '$eCo2',
                                     '$ina226vBus',
                                     '$ina226vShunt',
                                     '$ina226vCurrent',
                                     '$ina226vPower'
                                    );
                    ";

    $logArray   = array();
    $logArray[] = "setSensorData: Database: $dbFilename";

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
function setSensorData2($sensorData): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/sensordata.db';
    $dbFilenameRoot = 'database/sensordata.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $timeStamps     = date('Y-m-d H:i:s');

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');

    $bme280         = SQLite3::escapeString($sensorData['bme_p_280'] ?? '');
    $bme680         = SQLite3::escapeString($sensorData['bme680'] ?? '');
    $mcu811         = SQLite3::escapeString($sensorData['mcu811'] ?? '');
    $lsp33          = SQLite3::escapeString($sensorData['lps33'] ?? '');
    $oneWire        = SQLite3::escapeString($sensorData['1_wire'] ?? '');
    $tout           = SQLite3::escapeString($sensorData['tout'] ?? '');
    $temp           = SQLite3::escapeString($sensorData['temperature'] ?? '');
    $hum            = SQLite3::escapeString($sensorData['humidity'] ?? '');
    $qfe            = SQLite3::escapeString($sensorData['qfe'] ?? '');
    $qnh            = SQLite3::escapeString($sensorData['qnh'] ?? '');
    $altAsl         = SQLite3::escapeString($sensorData['altitude_asl'] ?? '');
    $gas            = SQLite3::escapeString($sensorData['gas'] ?? '');
    $eCo2           = SQLite3::escapeString($sensorData['eco2'] ?? '');
    $ina226vBus     = SQLite3::escapeString($sensorData['vBUS'] ?? '');
    $ina226vShunt   = SQLite3::escapeString($sensorData['vSHUNT'] ?? '');
    $ina226vCurrent = SQLite3::escapeString($sensorData['vCURRENT'] ?? '');
    $ina226vPower   = SQLite3::escapeString($sensorData['vPOWER'] ?? '');

    $sql = "REPLACE INTO sensordata (timestamps,
                                     bme280,
                                     bme680,
                                     mcu811,
                                     lsp33,
                                     oneWire,
                                     temp,
                                     tout,
                                     hum,
                                     qfe,
                                     qnh,
                                     altAsl,
                                     gas,
                                     eCo2,
                                     ina226vBus,
                                     ina226vShunt,
                                     ina226vCurrent,
                                     ina226vPower
                                    )
                             VALUES ('$timeStamps',
                                     '$bme280',
                                     '$bme680',
                                     '$mcu811',
                                     '$lsp33',
                                     '$oneWire',
                                     '$temp',
                                     '$tout',
                                     '$hum',
                                     '$qfe',
                                     '$qnh',
                                     '$altAsl',
                                     '$gas',
                                     '$eCo2',
                                     '$ina226vBus',
                                     '$ina226vShunt',
                                     '$ina226vCurrent',
                                     '$ina226vPower'
                                    );
                    ";

    $logArray   = array();
    $logArray[] = "setSensorData2: Database: $dbFilename";

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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
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
function columnExists($database, $tabelle, $spalte): bool
{
    // SQLite3-Datenbank öffnen
    $db = new SQLite3('database/' . $database . '.db');
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    $query  = "PRAGMA table_info('$tabelle')";

    $logArray   = array();
    $logArray[] = "columnExists: Database: $database";
    $logArray[] = "columnExists: tabelle: $tabelle";

    $result = safeDbRun( $db,  $query, 'query', $logArray);

    if ($result === false)
    {
        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    while ($row = $result->fetchArray(SQLITE3_ASSOC))
    {
        if ($row['name'] === $spalte)
        {
            $db->close();
            unset($db);
            return true; // Spalte existiert
        }
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return false; // Spalte existiert nicht
}
function checkVersion($currentVersion, $targetVersion, $operator)
{
    $currentVersion = preg_replace('/[^0-9.]/', '', $currentVersion);
    $targetVersion  = preg_replace('/[^0-9.]/', '', $targetVersion);

    return version_compare($currentVersion, $targetVersion, $operator);
}

function checkDbUpgrade($database)
{
    $debugFlag          = false;
    $doRestartBgProcess = false;

    #Update Datenbank meshdash mit Tabelle Firmware ab > V 1.10.02
    if (checkVersion(VERSION,'1.10.02','>'))
    {
        if ($debugFlag === true)
        {
            echo "<br>'1.10.02' ist kleiner oder gleich " . VERSION;
        }

        // SQLite3-Datenbank prüfen ob in Datenbank meshdash die Tabelle firmware existiert
        if (!columnExists($database, 'meshdash', 'firmware') && $database === 'meshdash')
        {
            // Spalte hinzufügen
            addColumn($database, 'meshdash', 'firmware');

            $doRestartBgProcess = true;
        }

        if (!columnExists($database, 'meshdash', 'fw_sub') && $database === 'meshdash')
        {
            // Spalte hinzufügen
            addColumn($database, 'meshdash', 'fw_sub');
            $doRestartBgProcess = true;
        }

        if (!columnExists($database, 'sensordata', 'ina226vBus') && $database === 'sensordata')
        {
            // Spalte hinzufügen
            addColumn($database, 'sensordata', 'ina226vBus');
            addColumn($database, 'sensordata', 'ina226vShunt');
            addColumn($database, 'sensordata', 'ina226vCurrent');
            addColumn($database, 'sensordata', 'ina226vPower');
        }

        if (!columnExists($database, 'mheard', 'mhType') && $database === 'mheard')
        {
            // Spalte hinzufügen
            addColumn($database, 'mheard', 'mhType');
        }

        if (!columnExists($database, 'groups', 'groupSound') && $database === 'groups')
        {
            // Spalte hinzufügen
            addColumn($database, 'groups', 'groupSound');
        }

        if (!columnExists($database, 'keywords', 'execScript') && $database === 'keywords')
        {
            // Spalte hinzufügen
            addColumn($database, 'keywords', 'execScript');
            addColumn($database, 'keywords', 'execTimestamp');
            addColumn($database, 'keywords', 'execTrigger');
            addColumn($database, 'keywords', 'execReturnMsg');
            addColumn($database, 'keywords', 'execGroup');
            addColumn($database, 'keywords', 'execMsgSend', 'INTEGER', 0);
            addColumn($database, 'keywords', 'execMsgSendTimestamp', 'TEXT', '0000-00-00 00:00:00');
            $doRestartBgProcess = true;
        }

        #Setzte diverse Indizes auf den Datenbanken

        #Meshdash
        if ($database === 'meshdash')
        {
            #addIndex('meshdash', 'meshdash','idx_timestamps', 'timestamps'); // Wird nicht benötigt
            delIndex('meshdash', 'idx_timestamps'); // lösche alten indizes

            #addIndex('meshdash', 'meshdash','idx_dst', 'dst'); // wird nicht benötigt
            delIndex('meshdash', 'idx_dst'); // lösche alten indizes

            #addIndex('meshdash', 'meshdash','idx_type', 'type'); // Wird nicht benötigt
            delIndex('meshdash', 'idx_type'); // lösche alten indizes

            addIndex('meshdash', 'meshdash', 'idx_ack_type_ts', 'msgIsAck, type, timestamps DESC');
            addIndex('meshdash', 'meshdash', 'idx_check_msg', 'type, dst, timestamps');
            addIndex('meshdash', 'meshdash', 'idx_ack_ts', 'msgIsAck, timestamps DESC');
            addIndex('meshdash', 'meshdash', 'idx_ack_dst_ts', 'msgIsAck, dst, timestamps DESC');
        }

        #sensordata
        if ($database === 'sensordata')
        {
            #addIndex('sensordata', 'sensordata','idx_timestamps', 'timestamps'); // Kein Index nötig. SQL optimiert.
            delIndex('sensordata', 'idx_timestamps'); // lösche alten indizes
        }

        #mheard
        if ($database === 'mheard')
        {
            delIndex('mheard', 'idx_timestamps'); // Neuer Index ist optimiert
            addIndex('mheard', 'mheard', 'idx_timestamps', 'timestamps, mhTime DESC');
        }

        if ($database === 'tx_queue')
        {
            #txQueue
            delIndex('tx_queue', 'idx_txInsertTimestamp'); // Neuer Index ist optimiert
            addIndex('tx_queue', 'txQueue', 'idx_txFlag_qid', 'txFlag, txQueueId');
        }
    }

    if (checkVersion(VERSION,'1.10.40','>='))
    {
        // Enable bubble-style view if not specified. As of V1.10.40
        if (getParamData('bubbleStyleView') === '')
        {
            setParamData('bubbleStyleView', 1);
        }
    }

    if ($doRestartBgProcess === true)
    {
        ## Prozess neu laden damit Feld befüllt wird
        # Stop BG-Process
        $paramBgProcess['task'] = 'udp';
        stopBgProcess($paramBgProcess);

        ##start BG-Process
        $paramStartBgProcess['task'] = 'udp';
        startBgProcess($paramStartBgProcess);
    }
}
function addColumn($database, $tabelle, $spalte, $typ = 'TEXT', $default = null): bool
{
    // Den Standardwert hinzufügen, wenn er angegeben wurde
    $defaultSql = '';

    // SQLite3-Datenbank öffnen
    $db = new SQLite3('database/' . $database . '.db');
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    // Sicherstellen, dass der Typ gültig ist
    if (empty($typ))
    {
        $typ = 'TEXT';  // Standardwert verwenden, wenn kein Typ angegeben ist
    }

    if ($default !== null)
    {
        // Wenn ein Standardwert übergeben wurde, wird dieser hinzugefügt
        $defaultSql = " DEFAULT '" . SQLite3::escapeString($default) . "'";
    }

    // SQL Befehl zum Hinzufügen der Spalte mit Typ und optionalem Standardwert
    $query = "ALTER TABLE $tabelle ADD COLUMN $spalte $typ" . $defaultSql;

    $logArray   = array();
    $logArray[] = "addColumn: database: $database";
    $logArray[] = "addColumn: spalte: $spalte";
    $logArray[] = "addColumn: tabelle: $tabelle";

    $res = safeDbRun( $db,  $query, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

    return true;
}
function addIndex($database, $tabelle, $IndexName, $indexField): bool
{
    // SQLite3-Datenbank öffnen
    $db = new SQLite3('database/' . $database . '.db');
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    // SQL Befehl zum Hinzufügen des Index
    $indexFields = implode(',', array_map('trim', explode(',', $indexField)));

    $query = "CREATE INDEX IF NOT EXISTS '$IndexName' ON '$tabelle' ($indexFields);";

    $logArray   = array();
    $logArray[] = "addColumn: database: $database";
    $logArray[] = "addColumn: IndexName: $IndexName";
    $logArray[] = "addColumn: indexField: $indexField";

    $res = safeDbRun( $db,  $query, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

    return true;
}
function delIndex($database, $IndexName): bool
{
    // SQLite3-Datenbank öffnen
    $db = new SQLite3('database/' . $database . '.db');
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    // SQL Befehl zum Löschen des Index
    $query = "DROP INDEX IF EXISTS '$IndexName';";

    $logArray   = array();
    $logArray[] = "addColumn: database: $database";
    $logArray[] = "addColumn: IndexName: $IndexName";

    $res = safeDbRun( $db,  $query, 'exec', $logArray);

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
    $osIssWindows    = chkOsIsWindows();
    $udpReceiverPid  = getParamData('udpReceiverPid');
    $cronLoopPid     = getParamData('cronLoopPid');
    $cronLoopPidFile = 'log/' . CRON_PID_FILE;
    $mode            = $mode == '' ? 'udp' : $mode;  // default UDP

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
                return $osIssWindows === true ? 'tasklist | find "dummyFile.exe"' : "pgrep -a -f cron_loop.php | grep -v pgrep | awk '{print $1}'";
            }

            return $osIssWindows === true ? 'tasklist | find "php.exe"' : "pgrep -a -f cron_loop.php | grep -v pgrep | awk '{print $1}'";
        }
        else
        {
            return $osIssWindows === true ? 'tasklist /FI "PID eq ' . $cronLoopPid . '" | findstr /I "php.exe"' : "pgrep -a -f cron_loop.php | grep -v pgrep | awk '{print $1}'";
        }
    }

    return false;
}
function getTaskKillCmd($mode = 'udp')
{
    #Check what oS is running
    $osIssWindows    = chkOsIsWindows();
    $udpReceiverPid  = getParamData('udpReceiverPid');
    $cronLoopPid     = getParamData('cronLoopPid');

    if ($mode == 'udp')
    {
        #Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
        if ($udpReceiverPid == '')
        {
            return $osIssWindows === true ? 'taskkill /f /fi "imagename eq php.exe"' : 'pkill -9 -f "udp_receiver.php"';
        }
        else
        {
            return $osIssWindows === true ? 'taskkill /F /PID ' . $udpReceiverPid : 'pkill -9 -f "udp_receiver.php"';
        }
    }

    if ($mode == 'cron')
    {
        #Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
        if ($cronLoopPid == '')
        {
            # Wenn keine Pid, dann All-Kill für Windows.
            return $osIssWindows === true ? 'taskkill /f /fi "imagename eq php.exe"' : 'pkill -9 -f "cron_loop.php"';

        }
        else
        {
            return $osIssWindows === true ? 'taskkill /F /PID ' . $cronLoopPid : 'pkill -9 -f "cron_loop.php"';
        }
    }

    return false;
}

function logRotate()
{
    if ((int) getParamData('chronLogEnable') === 0)
    {
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
        "tx_queue_json_data_",
        "udp_msg_data_",
        "send_queue_mheard_",
        "send_mheard_",
        "db_integrity_",
    ]; // Präfixe der Log-Dateien

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

                $fileTimestamp = $fileDate->getTimestamp();
                $age           = floor(($now - $fileTimestamp) / (60 * 60 * 24));

                if ($age > $retentionDays)
                {
                    $filePath = $logDir . "/" . $file;

                    if ($chronMode === "zip")
                    {
                        $zip->addFile($filePath, $file);
                        $toDelete[] = $filePath; // Datei erst nach dem ZIP-Schließen löschen
                        $archivedFiles++;
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
        $zip->close(); // ZIP-Archiv schließen, bevor Dateien gelöscht werden

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
function setCronSensorInterval($intervallInMinuten, $deleteFlag): bool
{
    $delete    = $deleteFlag == 1;
    $debugFlag = false;

    $skriptPfad = '/usr/bin/wget -q -O /dev/null ' . BASE_PATH_URL . 'get_sensor_data.php';

    $cronJobsNeu = [];

    if ($intervallInMinuten < 1)
    {
        if ($debugFlag)
        {
            echo "Intervall muss >= 1 sein.\n";
        }

        return false;
    }

    if ($intervallInMinuten < 60)
    {
        // Einfache Minuten-Intervalle
        $cronJobsNeu[] = "*/$intervallInMinuten * * * * $skriptPfad";
    }
    else
    {
        $gesamtMinuten = 24 * 60;
        $countItems    = intdiv($gesamtMinuten, $intervallInMinuten);
        $rest          = $gesamtMinuten % $intervallInMinuten;

        if ($rest > 0 && $debugFlag)
        {
            echo "⚠️ Achtung: Intervall von {$intervallInMinuten} Minuten passt nicht exakt in 24h.\n";
            echo "Es verbleiben {$rest} Minuten Rest am Tagesende.\n";
        }

        $zeitpunkte = [];
        for ($i = 0; $i < $countItems; $i++)
        {
            $minuteTotal = $i * $intervallInMinuten;
            $stunde      = floor($minuteTotal / 60);
            $minute      = $minuteTotal % 60;

            $zeitpunkte[] = ['hour' => $stunde, 'minute' => $minute];
        }

        // Gruppieren nach Minutenwert
        $gruppen = [];
        foreach ($zeitpunkte as $zeit)
        {
            $gruppen[$zeit['minute']][] = $zeit['hour'];
        }

        foreach ($gruppen as $minute => $stundenArray)
        {
            sort($stundenArray);
            $stundenListe  = implode(',', $stundenArray);
            $cronJobsNeu[] = "$minute $stundenListe * * * $skriptPfad";
        }
    }

    // Bestehende Crontab einlesen
    exec('crontab -l 2>/dev/null', $cronJobsAlt);

    // Löschen aller Jobs, die dieses Skript enthalten
    $cronJobsAlt = array_filter($cronJobsAlt, function ($zeile) use ($skriptPfad) {
        return strpos($zeile, $skriptPfad) === false;
    });

    if (!$delete)
    {
        $cronJobsAlt = array_merge($cronJobsAlt, $cronJobsNeu);
    }

    file_put_contents('/tmp/crontab.txt', implode("\n", $cronJobsAlt) . "\n");
    exec('crontab /tmp/crontab.txt');

    if ($debugFlag)
    {
        echo "<pre>";
        echo "Generierte Cronjobs für Intervall: {$intervallInMinuten} Minuten\n";
        print_r($cronJobsNeu);
        echo "</pre>";
    }

    return true;
}
function checkCronLoopBgTask()
{
    $taskCmdCron = getTaskCmd('cron');

    return shell_exec($taskCmdCron);
}
function deleteOldCron(): bool
{
    $osIssWindows  = chkOsIsWindows();

    #Prüfe ob Alter Cron noch existiert und lösche ihn
    if ($osIssWindows === false)
    {
        // Eingabewerte
        $skriptPfad = '/usr/bin/wget -q -O /dev/null ' . BASE_PATH_URL . 'cron_loop.php';

        // Die Crontab auslesen
        exec('crontab -l 2>/dev/null', $cronJobs);

        // Prüfen, ob der alte Cronjob noch existiert und lösche ihn
        foreach ($cronJobs as $index => $existingJob)
        {
            if (strpos($existingJob, $skriptPfad) !== false)
            {
                unset($cronJobs[$index]);

                // Crontab aktualisieren
                file_put_contents('/tmp/crontab_loop.txt', implode("\n", $cronJobs) . "\n");
                exec('crontab /tmp/crontab_loop.txt');
            }
        }
    }

    if (file_exists('log/cron_loop.lock'))
    {
        @unlink('log/cron_loop.lock');
    }

    return true;
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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
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
function setSensorAlertCounter($sensor, $sensorType): bool
{
    if ($sensor == 'temp')
    {
        #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
        $basename       = pathinfo(getcwd())['basename'];
        $dbFilenameSub  = '../database/sensor_th_temp.db';
        $dbFilenameRoot = 'database/sensor_th_temp.db';
        $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
        $timeStamps     = date('Y-m-d H:i:s');

        $db = new SQLite3($dbFilename);
        $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
        $db->exec('PRAGMA synchronous = NORMAL;');

        $queryTemp = " UPDATE sensorThTemp SET sensorThTempAlertCount = sensorThTempAlertCount + 1,
                                               sensorThTempAlertTimestamp = '$timeStamps';
                    ";

        if ($sensorType == 'Tout')
        {
            $queryTemp = " UPDATE sensorThTemp SET sensorThToutAlertCount = sensorThToutAlertCount + 1,
                                                   sensorThToutAlertTimestamp = '$timeStamps';
                         ";
        }

        $logArray = array();
        $logArray[] = "setSensorAlertCounter_temp: Database: $dbFilename";
        $logArray[] = "setSensorAlertCounter_temp: sensor: $sensor";
        $logArray[] = "setSensorAlertCounter_temp: sensorType: $sensorType";
        $logArray[] = "setSensorAlertCounter_temp: timeStamps: $timeStamps";

        $res = safeDbRun( $db,  $queryTemp, 'exec', $logArray);

        #Close and write Back WAL
        $db->close();
        unset($db);

        if ($res === false)
        {
            return false;
        }
    }

    if ($sensor == 'ina226')
    {
        #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
        $basenameIna226       = pathinfo(getcwd())['basename'];
        $dbFilenameSubIna226  = '../database/sensor_th_ina226.db';
        $dbFilenameRootIna226 = 'database/sensor_th_ina226.db';
        $dbFilenameIna226     = $basenameIna226 == 'menu' ? $dbFilenameSubIna226 : $dbFilenameRootIna226;
        $timeStampIna226      = date('Y-m-d H:i:s');

        $dbIna266 = new SQLite3($dbFilenameIna226);
        $dbIna266->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
        $dbIna266->exec('PRAGMA synchronous = NORMAL;');

        $queryIna226 = " UPDATE sensorThIna226 SET sensorThIna226vBusAlertCount = sensorThIna226vBusAlertCount + 1,
                                                   sensorThIna226vBusAlertTimestamp = '$timeStampIna226';
                         ";

        if ($sensorType == 'vShunt')
        {
            $queryIna226 = " UPDATE sensorThIna226 SET sensorThIna226vShuntAlertCount = sensorThIna226vShuntAlertCount + 1,
                                                       sensorThIna226vShuntAlertTimestamp = '$timeStampIna226';
                         ";
        }

        if ($sensorType == 'vCurrent')
        {
            $queryIna226 = " UPDATE sensorThIna226 SET sensorThIna226vCurrentAlertCount = sensorThIna226vCurrentAlertCount + 1,
                                                       sensorThIna226vCurrentAlertTimestamp = '$timeStampIna226';
                         ";
        }

        if ($sensorType == 'vPower')
        {
            $queryIna226 = " UPDATE sensorThIna226 SET sensorThIna226vPowerAlertCount = sensorThIna226vPowerAlertCount + 1,
                                                       sensorThIna226vPowerAlertTimestamp = '$timeStampIna226';
                         ";
        }

        $logArray = array();
        $logArray[] = "setSensorAlertCounter_ina266: Database: $dbFilenameIna226";
        $logArray[] = "setSensorAlertCounter_ina266: sensor: $sensor";
        $logArray[] = "setSensorAlertCounter_ina266: sensorType: $sensorType";
        $logArray[] = "setSensorAlertCounter_ina266: timeStamps: $timeStampIna226";

        $resIna266 = safeDbRun( $dbIna266,  $queryIna226, 'exec', $logArray);

        #Close and write Back WAL
        $dbIna266->close();
        unset($dbIna266);

        if ($resIna266 === false)
        {
            return false;
        }
    }

    return true;
}

/** @noinspection SqlWithoutWhere
 * @noinspection SqlWithoutWhere
 * @noinspection SqlWithoutWhere
 */
function resetSensorAlertCounter($sensor, $sensorType): bool
{
    if ($sensor == 'temp')
    {
        #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
        $basename       = pathinfo(getcwd())['basename'];
        $dbFilenameSub  = '../database/sensor_th_temp.db';
        $dbFilenameRoot = 'database/sensor_th_temp.db';
        $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
        $timeStamps     = date('Y-m-d H:i:s');

        $db = new SQLite3($dbFilename);
        $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
        $db->exec('PRAGMA synchronous = NORMAL;');

        $queryTemp = " UPDATE sensorThTemp SET sensorThTempAlertCount = 0,
                                               sensorThTempAlertTimestamp = '$timeStamps';
                    ";

        if ($sensorType == 'Tout')
        {
            $queryTemp = " UPDATE sensorThTemp SET sensorThToutAlertCount = 0,
                                                   sensorThToutAlertTimestamp = '$timeStamps';
                         ";
        }

        $logArray   = array();
        $logArray[] = "resetSensorAlertCounter_temp: Database: $dbFilename";
        $logArray[] = "setSensorAlertCounter_temp: sensor: $sensor";
        $logArray[] = "setSensorAlertCounter_temp: sensorType: $sensorType";
        $logArray[] = "setSensorAlertCounter_temp: timeStamps: $timeStamps";

        $res = safeDbRun( $db,  $queryTemp, 'exec', $logArray);

        #Close and write Back WAL
        $db->close();
        unset($db);

        if ($res === false)
        {
            return false;
        }
    }

    if ($sensor == 'ina226')
    {
        #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
        $basename       = pathinfo(getcwd())['basename'];
        $dbFilenameSub  = '../database/sensor_th_ina226.db';
        $dbFilenameRoot = 'database/sensor_th_ina226.db';
        $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
        $timeStamps     = date('Y-m-d H:i:s');

        $dbIna226 = new SQLite3($dbFilename);
        $dbIna226->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
        $dbIna226->exec('PRAGMA synchronous = NORMAL;');

        $queryIna226vBus = " UPDATE sensorThIna226 SET sensorThIna226vBusAlertCount = 0,
                                                       sensorThIna226vBusAlertTimestamp = '$timeStamps';
                         ";

        if ($sensorType == 'vShunt')
        {
            $queryIna226vBus = " UPDATE sensorThIna226 SET sensorThIna226vShuntAlertCount = 0,
                                                           sensorThIna226vShuntAlertTimestamp = '$timeStamps';
                         ";
        }
        else if ($sensorType == 'vCurrent')
        {
            $queryIna226vBus = " UPDATE sensorThIna226 SET sensorThIna226vCurrentAlertCount = 0,
                                                           sensorThIna226vCurrentAlertTimestamp = '$timeStamps';
                         ";
        }
        else if ($sensorType == 'vPower')
        {
            $queryIna226vBus = " UPDATE sensorThIna226 SET sensorThIna226vPowerAlertCount = 0,
                                                           sensorThIna226vPowerAlertTimestamp = '$timeStamps';
                         ";
        }

        $logArray   = array();
        $logArray[] = "resetSensorAlertCounterIna226: Database: $dbFilename";
        $logArray[] = "setSensorAlertCounter_temp: sensor: $sensor";
        $logArray[] = "setSensorAlertCounter_temp: sensorType: $sensorType";
        $logArray[] = "setSensorAlertCounter_temp: timeStamps: $timeStamps";

        $resIna226 = safeDbRun( $dbIna226,  $queryIna226vBus, 'exec', $logArray);

        #Close and write Back WAL
        $dbIna226->close();
        unset($dbIna226);

        if ($resIna226 === false)
        {
            return false;
        }
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

        'restore'       => ['symbol' => '&#128257;', 'label' => 'Restore'],     // 🔁
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
        'right_triangle' => ['symbol' => '&#9656;', 'label' => ''], // ⏵
        'toolbox' => ['symbol' => '&#129520;', 'label' => ''], // 🧰


        'configuration' => ['symbol' => '&#128736;&#65039;', 'label' => 'Einstellung'], // 🛠️
        'generally2'     => ['symbol' => '&#9881;&#65039;', 'label' => 'Allgemein'],             // ⚙️
        'generally3'     => ['symbol' => '&#128295;', 'label' => 'Allgemein'],             // 🔧
        'generally4'     => ['symbol' => '&#129535;', 'label' => 'Allgemein'],             // 🧿
        'generally'     => ['symbol' => '&#128261;', 'label' => 'Allgemein'],             // 🔅
        'interval'      => ['symbol' => '&#9201;&#65039;', 'label' => 'Send-Queue'],       // ⏱️
        'notification'  => ['symbol' => '&#128276;', 'label' => 'Notification'],    // 🔔️
        'keyword'       => ['symbol' => '&#128278;', 'label' => 'Keyword'],             // 🏷️
        'update'        => ['symbol' => '&#128260;', 'label' => 'Update'],              // 🔄
        'lora-info'     => ['symbol' => '&#128225;&#65039;', 'label' => 'Lora-Info'],           // 📡
        'data-purge'    => ['symbol' => '&#129529;&#65039;', 'label' => 'Data-Purge'],          // 🧹

        'data-purge-manuell'    => ['symbol' => ' &#9995;&#65039;', 'label' => 'Purge Manuell'],          // ✋
        'data-purge-auto'    => ['symbol' => '&#129302;&#65039;', 'label' => 'Purge Auto'],          // 🤖

        'ping-lora'     => ['symbol' => '&#128246;', 'label' => 'Ping Lora'],           // 📶
        'debug-info'    => ['symbol' => '&#128030;', 'label' => 'Debug-Info'],          // 🐞


        'groups'   => ['symbol' => '&#128101;&#65039;', 'label' => 'Gruppen'],  // 👥
        'groups_define'   => ['symbol' => '&#128450;&#65039;', 'label' => 'Gruppenfilter'],  // 🗂️

        'sensors'   => ['symbol' => '&#127777;&#65039;', 'label' => 'Sensoren'],  // 🌡️
        'sensordata'   => ['symbol' => '&#128202;', 'label' => 'Sensordaten'],  // 📊
        'threshold'   => ['symbol' => '&#129514;', 'label' => 'Schwellwerte'],  // 🧪

        'mheard'   => ['symbol' => '&#128066;&#65039;', 'label' => 'MHeard'],  // 👂
        'mheard-page'   => ['symbol' => '&#x1F3A7;&#65039;', 'label' => 'MHeard-Lokal'],  // 🎧
        'mheard-osm'   => ['symbol' => '&#x1F5FA;&#xFE0F;', 'label' => 'MHeard-Map'],  // 🗺️

        'beacon'   => ['symbol' => ' &#x1F9ED;&#65039;', 'label' => 'Bake'],  // 🧭
        'send-cmd'   => ['symbol' => '&#128228;', 'label' => 'Sende Befehl'],  // 📤
        'message'   => ['symbol' => '&#128172;&#65039;', 'label' => 'Message'],  // 💬
        'about'   => ['symbol' => '&#8505;&#65039;', 'label' => 'About'],  // ℹ️

        'gps'   => ['symbol' => '&#x1F6F0;&#65039;', 'label' => 'GPS-Info'],  // 🛰️


    ];

    $key = strtolower($status);

    if (!isset($icons[$key]))
    {
        $key = 'unknown';
    }

    $entry = $icons[$key];

    return $withLabel
        ? '<span class="menu-icon">' . $entry['symbol'] . '</span> ' . htmlspecialchars($entry['label'])
        : '<span class="menu-icon">' . $entry['symbol'] . '</span>';

}
function stopBgProcess($paramBgProcess)
{
    $osIssWindows   = chkOsIsWindows();
    $taskBg         = $paramBgProcess['task'] ?? '';
    $taskBg         = $taskBg == '' ? 'udp' : $taskBg;
    $checkBgTaskCmd = getTaskCmd($taskBg);
    $bgPidFile      = $taskBg  == 'udp' ? UPD_PID_FILE : CRON_PID_FILE;
    $debugFlag      = false;

    $bgTaskPid = $taskBg  == 'udp' ? getParamData('udpReceiverPid') : getParamData('cronLoopPid');

    if ($taskBg  == 'cron')
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
        echo "<br>bgTaskPid:$bgTaskPid";
        echo "<br>#652#bgTask:$taskBg";
        echo "<br>#652#bgPidFile:$bgPidFile";
        echo "<br>#652#checkBgTaskCmd:$checkBgTaskCmd";
        echo "<br>#652#bgTaskKillCmd:$bgTaskKillCmd";
        echo "<br>#652#taskResultBg:$taskResultBg";
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
    sleep(1);

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
    $bgProcFile  = $taskBg == 'udp' ? UDP_PROC_FILE : CRON_PROC_FILE;
    $taskCmd     = getTaskCmd($taskBg);
    $taskResult  = shell_exec($taskCmd);
    $debugFlag   = false;

    if ($osIsWindows === false)
    {
        $basename       = pathinfo(getcwd())['basename'];
        $bgProcFileSub  = '../' . $bgProcFile;
        $bgProcFileRoot = $bgProcFile;
        $bgProcFile     = $basename == 'menu' ? $bgProcFileSub : $bgProcFileRoot;
    }

    if ($debugFlag === true)
    {
        echo "<br>#1956#startBgProcess# task:" . $taskBg . ' Task-Result:' . $taskResult;
        echo "<br>#1956#startBgProcess#Taskresult taskCmd:" . $taskCmd;
        echo "<br>#1956#startBgProcess#Taskresult bgProcFile:" . $bgProcFile;
        echo "<br>osIsWindows:";
        var_dump($osIsWindows);
    }

    if (empty($taskResult))
    {
        if ($debugFlag === true)
        {
            echo "<br>#1956#startBgProcess#Taskresult EMpty: task:" . $taskBg . ' Task-Result:' . $taskResult;
            echo "<br>#1956#startBgProcess#Taskresult taskCmd:" . $taskCmd;
            echo "<br>#1956#startBgProcess#Taskresult bgProcFile:" . $bgProcFile;
        }

        if($osIsWindows === true)
        {
            #Unter Windows mit Curl Starten
            callWindowsBackgroundTask($bgProcFile);
        }
        else
        {
            #Unter Linux direkt starten
            exec('nohup php ' . $bgProcFile . ' >/dev/null 2>&1 &');
        }

        sleep(1);

        $checkTaskCmd = getTaskCmd($taskBg);
        $taskResult   = shell_exec($checkTaskCmd);

        if ($debugFlag === true)
        {
            echo "<br>#1956#startBgProcess#Taskresult taskResult:" . $taskResult;
        }
    }

    return $taskResult;
}
function callWindowsBackgroundTask($taskFile, $execDir = ''): bool
{
    // Holt den Projekt-Root aus SCRIPT_NAME (NICHT SCRIPT_FILENAME!)
    $protocol    = (empty($_SERVER['HTTPS']) ? 'http' : 'https');
    $host        = $_SERVER['HTTP_HOST'];
    $scriptName  = $_SERVER['SCRIPT_NAME']; // z. B. /meshdash/menu/index.php
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

        echo "<pre>";
        print_r($postFields);
        echo "</pre>";

        echo "<pre>";
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

function checkLoraNewGui(): bool
{
    $loraIp      = getParamData('loraIp');
    $actualHost  = 'http';
    $triggerLink = $actualHost . '://' . $loraIp . '/getparam/?setcall=';

    $ch = curl_init($triggerLink);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 Sekunden Timeout da bei 3 oft Fehler
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false)
    {
        // Curl Fehler, eventuell Log schreiben
        echo '<br><span class="failureHint">Kann Node mit IP: ' . $loraIp . ' zur Prüfung auf neue GUI nicht erreichen!</span>';
        return false;
    }

    $jsonContent = json_decode($response, true);

    #Alte GUI erkannt
    if ($jsonContent === null || !isset($jsonContent['returncode']))
    {
        setParamData('isNewMeshGui',0);
        return false;
    }

    #Neue GUI erkannt
    setParamData('isNewMeshGui',1);
    return true;
}
function checkDbIntegrity($database)
{
    $realDatabasePath   = 'database/' . $database . '.db';
    $fileDbIntegrityLog = 'log/db_integrity_error_' . date('Ymd') . '.log';

    $dbFileSize = is_readable($realDatabasePath) ? filesize($realDatabasePath) : -1; // Prevents if File is locked
    $sizeKB     = round($dbFileSize / 1024, 1);

    if ($sizeKB == 0)
    {
        @unlink($realDatabasePath);
        initSQLiteDatabase($database);

        $errorText = date('Y-m-d H:i:s') . ' Database: ' . $database . ' faulty integration. Reinitialize' . "\n";
        file_put_contents($fileDbIntegrityLog, $errorText, FILE_APPEND);
    }
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
function showBackups()
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
    echo '<th>Datum</th>';
    echo '<th>Uhrzeit</th>';
    echo '<th>Backup-Datei</th>';
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
function autoPurgeData(): bool
{
    if ((int) getParamData('enableMsgPurge') == 1)
    {
        $db = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
        $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

        $daysMsgPurge      = (int) getParamData('daysMsgPurge');
        $anzahlPurgeMsgSel = 0;

        $sqlPurgeMsgSelect = "SELECT COUNT(*) AS anzahl 
                                FROM meshdash
                               WHERE timestamps < datetime('now', '-$daysMsgPurge days');
                             ";

        $sqlPurgeMsg = "DELETE FROM meshdash
                              WHERE timestamps < datetime('now', '-$daysMsgPurge days');
                       ";

        $logArray   = array();
        $logArray[] = "AutoPurge MSG Select";
        $result     = safeDbRun($db, $sqlPurgeMsgSelect, 'query', $logArray);

        if ($result !== false)
        {
            while ($row = $result->fetchArray(SQLITE3_ASSOC))
            {
                $anzahlPurgeMsgSel = $row['anzahl'] ?? 0;
            }
        }

        #Close and write Back WAL
        $db->close();
        unset($db);

        if ($anzahlPurgeMsgSel > 0)
        {
            $dbWrite = new SQLite3('database/meshdash.db');
            $dbWrite->exec('PRAGMA synchronous = NORMAL;');

            $logArray   = array();
            $logArray[] = "AutoPurge MSG DELETE";
            $res = safeDbRun( $dbWrite,  $sqlPurgeMsg, 'exec', $logArray);

            if ($res === false)
            {
                #Close and write Back WAL
                $dbWrite->close();
                unset($dbWrite);

                return false;
            }
        }
    }

    if ((int) getParamData('enableSensorPurge') == 1)
    {
        $db = new SQLite3('database/sensordata.db', SQLITE3_OPEN_READONLY);
        $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

        $daysSensorPurge      = (int) getParamData('daysSensorPurge');
        $anzahlPurgeSensorSel = 0;

        $sqlPurgeSensorSelect = "SELECT count(*) AS anzahl 
                                   FROM sensordata
                                  WHERE timestamps < datetime('now', '-$daysSensorPurge days');
                             ";

        $sqlPurgeSensor = "DELETE FROM sensordata
                                 WHERE timestamps < datetime('now', '-$daysSensorPurge days');
                       ";

        $logArray   = array();
        $logArray[] = "AutoPurge Sensor Select";
        $result     = safeDbRun($db, $sqlPurgeSensorSelect, 'query', $logArray);

        if ($result !== false)
        {
            while ($row = $result->fetchArray(SQLITE3_ASSOC))
            {
                $anzahlPurgeSensorSel = $row['anzahl'] ?? 0;
            }
        }

        #Close and write Back WAL
        $db->close();
        unset($db);

        if ($anzahlPurgeSensorSel > 0)
        {
            $dbWrite = new SQLite3('database/sensordata.db');
            $dbWrite->exec('PRAGMA synchronous = NORMAL;');

            $logArray   = array();
            $logArray[] = "AutoPurge SENSOR DELETE";
            $res        = safeDbRun($dbWrite, $sqlPurgeSensor, 'exec', $logArray);

            if ($res === false)
            {
                #Close and write Back WAL
                $dbWrite->close();
                unset($dbWrite);

                return false;
            }
        }
    }

    return true;
}
