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
    $db->busyTimeout(5000); // warte wenn busy in millisekunden
    $res = $db->query("SELECT * 
                               FROM parameter AS pa 
                              WHERE pa.param_key = '$key';
                    ");

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>getParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();
    }

    $dsData = $res->fetchArray(SQLITE3_ASSOC);

    $paramValue = $dsData['param_value'] ?? '';
    $paramText  = $dsData['param_text'] ?? '';
    $paramValue = ($paramValue !== '' && $paramValue !== null) ? $paramValue : $paramText;

    #Close and write Back WAL
    $db->close();
    unset($db);

    return $paramValue;
}

function setParamData($key, $value, $mode = 'int'): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/parameter.db';
    $dbFilenameRoot = 'database/parameter.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(5000); // warte wenn busy in millisekunden
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

    $db->exec("
                        REPLACE INTO parameter (
                                                param_key, 
                                                param_value, 
                                                param_text)
                        VALUES (
                                '$key',
                                '$param_value',
                                '$param_text'
                        );
                    ");

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

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
    $db->busyTimeout(5000); // warte wenn busy in millisekunden
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

    $db->exec("
                        REPLACE INTO sensorThTemp (sensorThTempId,
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
                        VALUES (
                                '1',
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
                    ");

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();

        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

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
    $db->busyTimeout(5000); // warte wenn busy in millisekunden
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

    $db->exec("
                        REPLACE INTO sensorThIna226 (
                                                     sensorThIna226Id,
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
                        VALUES (
                                     '1',
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
                    ");

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();

        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

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
    $db->busyTimeout(5000); // warte wenn busy in millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    $db->exec("
                        REPLACE INTO sensorThIna226 (
                                                     sensorThIna226Id,
                                                     timeStamps,
                                                     sensorThIna226vBusEnabled, 
                                                     sensorThIna226vShuntEnabled, 
                                                     sensorThIna226vCurrentEnabled, 
                                                     sensorThIna226vPowerEnabled
                                                    )
                                        VALUES (
                                                     '1',
                                                     '$timeStamps',
                                                     '0',
                                                     '0',
                                                     '0', 
                                                     '0'
                                               );
                    ");

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();

        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

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
    $db->busyTimeout(5000); // warte wenn busy in millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    $result = $db->query("
                        SELECT * 
                          FROM sensorThTemp 
                      ORDER BY timestamps DESC
                         LIMIT 1;
                    ");

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();

        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    if ($db !== false)
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
    $db->busyTimeout(5000); // warte wenn busy in millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    $result = $db->query("
                        SELECT * FROM sensorThIna226 
                                 ORDER BY timestamps DESC
                             LIMIT 1;
                    ");

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();

        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    if ($db !== false)
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
    $db->busyTimeout(5000); // warte wenn busy in millisekunden
    $res = $db->query("
                        SELECT * FROM keywords AS kw 
                         WHERE kw.msg_id = '$msgId';
                    ");
    $dsData = $res->fetchArray();

    $returnValue['msg_id']   = $dsData['msg_id'] ?? 0;
    $returnValue['executed'] = $dsData['executed'] ?? 0;
    $returnValue['errCode']  = $dsData['errCode'] ?? 0;
    $returnValue['errText']  = $dsData['errText'] ?? '';

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>getParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return $returnValue;
}

function setKeywordsData($msgId, $value, int $errCode, string $errText): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/keywords.db';
    $dbFilenameRoot = 'database/keywords.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');

    #Escape Value
    $msgId   = SQLite3::escapeString($msgId);
    $value   = (int) $value;
    $errText = SQLite3::escapeString($errText);
    $msgId   = trim($msgId);

    $db->exec("
                        REPLACE INTO keywords (
                                                msg_id, 
                                                executed,
                                                errCode,
                                                errText
                                              )
                                VALUES (
                                        '$msgId',
                                        '$value',
                                        '$errCode',
                                        '$errText'
                                );
                    ");

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return true;
}

function chkOsIssWindows(): bool
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

        $db->exec(
            "
                        REPLACE INTO mheard (
                                             timestamps, 
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
                                             mhM)
                                VALUES (
                                        '$mhTimeStamps',
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
                    "
        );

        if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
        {
            echo "<br>setParamData";
            echo "<br>ErrMsg:" . $db->lastErrorMsg();
            echo "<br>ErrNum:" . $db->lastErrorCode();
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

    $db->exec(
        "
                        REPLACE INTO sensordata (
                                                 timestamps,
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
                                VALUES (
                                        '$timeStamps',
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
                    "
    );

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();
    }


    #Close and write Back WAL
    $db->close();
    unset($db);

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

    $db->exec(
        "
                        REPLACE INTO sensordata (
                                                 timestamps,
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
                                VALUES (
                                        '$timeStamps',
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
                    "
    );

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();
    }


    #Close and write Back WAL
    $db->close();
    unset($db);

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

    #Escape Value
    $value = trim(SQLite3::escapeString($value));

    $db->exec(" UPDATE meshdash
                          SET $key = $value
                        WHERE msg_id = '$msgId';
                    ");

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return true;
}

function columnExists($database, $tabelle, $spalte): bool
{
    // SQLite3-Datenbank öffnen
    $db = new SQLite3('database/' . $database . '.db');
    $db->busyTimeout(5000); // warte wenn busy in millisekunden

    $query  = "PRAGMA table_info($tabelle)";
    $result = $db->query($query);

    while ($row = $result->fetchArray(SQLITE3_ASSOC))
    {
        if ($row['name'] === $spalte)
        {
            $db->close();
            return true; // Spalte existiert
        }
    }

    $db->close();
    return false; // Spalte existiert nicht
}

function checkVersion($currentVersion, $targetVersion, $operator)
{
    $currentVersion = preg_replace('/[^0-9.]/', '', $currentVersion);
    $targetVersion  = preg_replace('/[^0-9.]/', '', $targetVersion);

    return version_compare($targetVersion, $currentVersion, $operator);
}

function checkDbUpgrade($database)
{
    $debugFlag = false;

    #Update Datenbank meshdash mit Tabelle Firmware ab > V 1.10.02
    if (checkVersion(VERSION,'1.10.02','<'))
    {
        if ($debugFlag === true)
        {
            echo "<br>'1.10.02' ist kleiner oder gleich " . VERSION;
        }

        // SQLite3-Datenbank prüfen ob in Datenbank meshdash die Tabelle firmware existiert
        if (!columnExists($database, 'meshdash', 'firmware') && $database === 'meshdash')
        {
            if ($debugFlag === true)
            {
                echo "<br>Die Spalte: 'firmware' in Tabelle: 'meshdash' existiert nicht.";
            }

            // Spalte hinzufügen
            addColumn($database, 'meshdash', 'firmware');

            ## Prozess neu laden damit Feld befüllt wird
            # Stop BG-Process
            $paramBgProcess['task'] = 'udp';
            stopBgProcess($paramBgProcess);

            ##start BG-Process
            $paramStartBgProcess['task'] = 'udp';
            startBgProcess($paramStartBgProcess);
        }

        if (!columnExists($database, 'meshdash', 'fw_sub') && $database === 'meshdash')
        {
            if ($debugFlag === true)
            {
                echo "<br>Die Spalte: 'fw_sub' in Tabelle: 'meshdash' existiert nicht.";
            }

            // Spalte hinzufügen
            addColumn($database, 'meshdash', 'fw_sub');

            ## Prozess neu laden damit Feld befüllt wird
            # Stop BG-Process
            $paramBgProcess['task'] = 'udp';
            stopBgProcess($paramBgProcess);

            ##start BG-Process
            $paramStartBgProcess['task'] = 'udp';
            startBgProcess($paramStartBgProcess);
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

        #Setzte diverse Indizes auf den Datenbanken
        #Meshdash
        addIndex('meshdash', 'meshdash','idx_timestamps', 'timestamps');
        addIndex('meshdash', 'meshdash','idx_dst', 'dst');
        addIndex('meshdash', 'meshdash','idx_type', 'type');
        addIndex('meshdash', 'meshdash','idx_check_msg', 'type, dst, timestamps');
        #sensordata
        addIndex('sensordata', 'sensordata','idx_timestamps', 'timestamps');
        #mheard
        addIndex('mheard', 'mheard','idx_timestamps', 'timestamps');
    }
}

function addColumn($database, $tabelle, $spalte, $typ = 'TEXT', $default = null)
{
    // SQLite3-Datenbank öffnen
    $db = new SQLite3('database/' . $database . '.db');
    $db->busyTimeout(5000); // warte wenn busy in millisekunden

    // Sicherstellen, dass der Typ gültig ist
    if (empty($typ))
    {
        $typ = 'TEXT';  // Standardwert verwenden, wenn kein Typ angegeben ist
    }

    // Den Standardwert hinzufügen, wenn er angegeben wurde
    $defaultSql = '';
    if ($default !== null)
    {
        $defaultSql = " DEFAULT '$default'"; // Wenn ein Standardwert übergeben wurde, wird dieser hinzugefügt
    }

    // SQL Befehl zum Hinzufügen der Spalte mit Typ und optionalem Standardwert
    $query = "ALTER TABLE $tabelle ADD COLUMN $spalte $typ" . $defaultSql;
    if (!$db->exec($query))
    {
        echo "<br>Fehler beim Hinzufügen der Spalte: $spalte in Tabelle $tabelle bei Datenbank $database.";
    }

    $db->close();
}

function addIndex($database, $tabelle, $IndexName, $indexField)
{
    // SQLite3-Datenbank öffnen
    $db = new SQLite3('database/' . $database . '.db');
    $db->busyTimeout(5000); // warte wenn busy in millisekunden

    // SQL Befehl zum Hinzufügen des Index
    $query = "CREATE INDEX IF NOT EXISTS $IndexName ON $tabelle($indexField);";
    if (!$db->exec($query))
    {
        echo "<br>Fehler beim Hinzufügen des Index mit Idexname: $IndexName und IndexField: $indexField bei Datenbank $database.";
    }

    $db->close();
}

function getTaskCmd($mode)
{
    #Check what oS is running
    $osIssWindows    = chkOsIssWindows();
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
    $osIssWindows    = chkOsIssWindows();
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

function chronLog()
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
    $prefixes    = ["msg_data_", "user_data_", "user_json_data_"]; // Präfixe der Log-Dateien

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
        die("Konnte ZIP-Archiv nicht erstellen!");
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

        // Jetzt die Dateien aus dem Archiv löschen
        foreach ($toDelete as $file)
        {
            unlink($file);
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

    if ($debugFlag === true)
    {
        echo "<br>intervallInMinuten : $intervallInMinuten";
        echo "<br>deleteFlag : $deleteFlag -><br>";
        var_dump($delete);
    }

    // Eingabewerte
    $skriptPfad = '/usr/bin/wget -q -O /dev/null http://localhost/5d/get_sensor_data.php';

    // Umrechnung von Minuten in Stunden und Minuten
    // Erstellen der Cron-Syntax im Schrittwert-Modus
    if ($intervallInMinuten >= 60)
    {
        $stunden = floor($intervallInMinuten / 60);
        $minuten = $intervallInMinuten % 60;

        if ($minuten > 0)
        {
            // Falls es sowohl einen Stunden- als auch Minutenanteil gibt
            $cronIntervall = "*/$minuten */$stunden * * *";
        }
        else
        {
            // Falls nur ganze Stunden angegeben sind
            $cronIntervall = "0 */$stunden * * *";
        }
    }
    else
    {
        // Falls nur Minuten angegeben sind
        $cronIntervall = "*/$intervallInMinuten * * * *";
    }

    // Cronjob suchen und prüfen
    $cronJob = "$cronIntervall $skriptPfad";

    if ($debugFlag === true)
    {
        echo "<br>cronJob : $cronJob";
    }

    // Die Crontab auslesen
    exec('crontab -l 2>/dev/null', $cronJobs);

    // Prüfen, ob der Cronjob bereits existiert
    $found = false;
    foreach ($cronJobs as $index => $existingJob)
    {
        if (strpos($existingJob, $skriptPfad) !== false)
        {
            $found = true;

            // Wenn das Delete-Flag gesetzt ist, den Cronjob löschen
            if ($delete === true)
            {
                unset($cronJobs[$index]);
                // Crontab aktualisieren
                file_put_contents('/tmp/crontab.txt', implode("\n", $cronJobs) . "\n");
                exec('crontab /tmp/crontab.txt');
                if ($debugFlag === true)
                {
                    echo "Cronjob wurde gelöscht.\n";
                }

                return true;
            }

            // Wenn das Intervall anders ist, den Job aktualisieren
            if ($existingJob !== $cronJob)
            {
                $cronJobs[$index] = $cronJob;
                // Crontab aktualisieren
                file_put_contents('/tmp/crontab.txt', implode("\n", $cronJobs) . "\n");
                exec('crontab /tmp/crontab.txt');
                if ($debugFlag === true)
                {
                    echo "Cronjob wurde aktualisiert.\n";
                }

                return true;
            }
        }
    }

    // Wenn der Cronjob noch nicht existiert, hinzufügen
    if (!$found && $delete === false)
    {
        // Cronjob zur Liste hinzufügen
        $cronJobs[] = $cronJob;
        // Crontab aktualisieren
        file_put_contents('/tmp/crontab.txt', implode("\n", $cronJobs) . "\n");
        exec('crontab /tmp/crontab.txt');

        if ($debugFlag === true)
        {
            echo "Cronjob wurde hinzugefügt.\n";
        }
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
    $osIssWindows  = chkOsIssWindows();

    #Prüfe ob Alter Cron noch existiert und lösche ihn
    if ($osIssWindows === false)
    {
        // Eingabewerte
        $skriptPfad = '/usr/bin/wget -q -O /dev/null http://localhost/5d/cron_loop.php';

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

            if ($debugFlag === true)
            {
                echo "<br>" . $data;
            }

            exit();
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
    $db->exec('PRAGMA synchronous = NORMAL;');

    $txTimestamp     = '0000-00-00 00:00:00';
    $txType          = SQLite3::escapeString($txQueueData['txType'] ?? 'msg');
    $txDst           = SQLite3::escapeString($txQueueData['txDst'] ?? '');
    $txMsg           = SQLite3::escapeString($txQueueData['txMsg'] ?? '');
    $txFlag          = 0;

    $db->exec(
        "
                        REPLACE INTO txQueue (
                                              insertTimestamp,
                                              txTimestamp, 
                                              txType, 
                                              txDst, 
                                              txMsg, 
                                              txFlag
                                                 )
                                VALUES (
                                        '$insertTimestamp',
                                        '$txTimestamp',
                                        '$txType',
                                        '$txDst',
                                        '$txMsg',
                                        '$txFlag'
                                );
                    "
    );

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

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

    // Prüfen, ob bereits eine Instanz läuft
    if (!file_exists($dbFilename))
    {
        return false;
    }

    $db = new SQLite3($dbFilename);
    $db->busyTimeout(5000); // warte wenn busy in millisekunden

    $resTxQueue = $db->query(
        "         SELECT * 
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
               ");

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();

        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    $dsData = $resTxQueue->fetchArray();

    if (!empty($dsData))
    {
        $returnValue['txQueueId'] = $dsData['txQueueId'] ?? 0;
        $returnValue['txType']    = $dsData['txType'] ?? 0;
        $returnValue['txDst']     = $dsData['txDst'] ?? 0;
        $returnValue['txMsg']     = $dsData['txMsg'] ?? '';
    }
    else
    {
        return false;
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

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
    $db->exec('PRAGMA synchronous = NORMAL;');

    $db->exec(" UPDATE txQueue
                        SET txFlag = 1,
                            txTimestamp = '$timeStamps'
                      WHERE txQueueId = '$txQueueId';
                    ");

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

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
        $db->busyTimeout(5000); // warte wenn busy in millisekunden
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
        
        $db->exec($queryTemp);

        if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
        {
            echo "<br>setParamData";
            echo "<br>ErrMsg:" . $db->lastErrorMsg();
            echo "<br>ErrNum:" . $db->lastErrorCode();

            #Close and write Back WAL
            $db->close();
            unset($db);

            return false;
        }

        #Close and write Back WAL
        $db->close();
        unset($db);
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
        $dbIna266->busyTimeout(5000); // warte wenn busy in millisekunden
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

        $dbIna266->exec($queryIna226);

        if ($dbIna266->lastErrorMsg() > 0 && $dbIna266->lastErrorMsg() < 100)
        {
            echo "<br>setParamData";
            echo "<br>ErrMsg:" . $dbIna266->lastErrorMsg();
            echo "<br>ErrNum:" . $dbIna266->lastErrorCode();

            #Close and write Back WAL
            $dbIna266->close();
            unset($dbIna266);

            return false;
        }

        #Close and write Back WAL
        $dbIna266->close();
        unset($dbIna266);
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
        $db->busyTimeout(5000); // warte wenn busy in millisekunden
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

        $db->exec($queryTemp);

        if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
        {
            echo "<br>setParamData";
            echo "<br>ErrMsg:" . $db->lastErrorMsg();
            echo "<br>ErrNum:" . $db->lastErrorCode();

            #Close and write Back WAL
            $db->close();
            unset($db);

            return false;
        }

        #Close and write Back WAL
        $db->close();
        unset($db);
    }

    if ($sensor == 'ina226')
    {
        #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
        $basename       = pathinfo(getcwd())['basename'];
        $dbFilenameSub  = '../database/sensor_th_ina226.db';
        $dbFilenameRoot = 'database/sensor_th_ina226.db';
        $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
        $timeStamps     = date('Y-m-d H:i:s');

        $db = new SQLite3($dbFilename);
        $db->busyTimeout(5000); // warte wenn busy in millisekunden
        $db->exec('PRAGMA synchronous = NORMAL;');

        $queryIna226vBus = " UPDATE sensorThIna226 SET sensorThIna226vBusAlertCount = 0,
                                                       sensorThIna226vBusAlertTimestamp = '$timeStamps';
                         ";

        if ($sensorType == 'vShunt')
        {
            $queryIna226vBus = " UPDATE sensorThIna226 SET sensorThIna226vShuntAlertCount = 0,
                                                           sensorThIna226vShuntAlertTimestamp = '$timeStamps';
                         ";
        }

        if ($sensorType == 'vCurrent')
        {
            $queryIna226vBus = " UPDATE sensorThIna226 SET sensorThIna226vCurrentAlertCount = 0,
                                                           sensorThIna226vCurrentAlertTimestamp = '$timeStamps';
                         ";
        }

        if ($sensorType == 'vPower')
        {
            $queryIna226vBus = " UPDATE sensorThIna226 SET sensorThIna226vPowerAlertCount = 0,
                                                           sensorThIna226vPowerAlertTimestamp = '$timeStamps';
                         ";
        }

        $db->exec($queryIna226vBus);

        if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
        {
            echo "<br>setParamData";
            echo "<br>ErrMsg:" . $db->lastErrorMsg();
            echo "<br>ErrNum:" . $db->lastErrorCode();

            #Close and write Back WAL
            $db->close();
            unset($db);

            return false;
        }

        #Close and write Back WAL
        $db->close();
        unset($db);
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
        'interval'      => ['symbol' => '&#9201;&#65039;', 'label' => 'Sende-Intervall'],       // ⏱️
        'notification'  => ['symbol' => '&#128276;', 'label' => 'Benachrichtigung'],    // 🔔️
        'keyword'       => ['symbol' => '&#128278;', 'label' => 'Keyword'],             // 🏷️
        'update'        => ['symbol' => '&#128260;', 'label' => 'Update'],              // 🔄
        'lora-info'     => ['symbol' => '&#128225;&#65039;', 'label' => 'Lora-Info'],           // 📡
        'data-purge'    => ['symbol' => '&#129529;&#65039;', 'label' => 'Data-Purge'],          // 🧹
        'ping-lora'     => ['symbol' => '&#128246;', 'label' => 'Ping Lora'],           // 📶
        'debug-info'    => ['symbol' => '&#128030;', 'label' => 'Debug-Info'],          // 🐞

        'groups'   => ['symbol' => '&#128101;&#65039;', 'label' => 'Gruppen'],  // 👥
        'groups_define'   => ['symbol' => '&#128450;&#65039;', 'label' => 'Gruppenfilter'],  // 🗂️

        'sensors'   => ['symbol' => '&#127777;&#65039;', 'label' => 'Sensoren'],  // 🌡️
        'sensordata'   => ['symbol' => '&#128202;', 'label' => 'Sensordaten'],  // 📊
        'threshold'   => ['symbol' => '&#129514;', 'label' => 'Schwellwerte'],  // 🧪

        'mheard2'   => ['symbol' => '&#128225;&#65039;', 'label' => 'MHeard'],  // 📡
        'mheard'   => ['symbol' => '&#128066;&#65039;', 'label' => 'MHeard'],  // 👂

        'send-cmd'   => ['symbol' => '&#128228;', 'label' => 'Sende Befehl'],  // 📤

        'message'   => ['symbol' => '&#128172;&#65039;', 'label' => 'Message'],  // 💬

        'about'   => ['symbol' => '&#8505;&#65039;', 'label' => 'About'],  // ℹ️

    ];

    $key = strtolower($status);

    if (!isset($icons[$key])) {
        $key = 'unknown';
    }

    $entry = $icons[$key];

    return $withLabel
        ? $entry['symbol'] . ' ' . htmlspecialchars($entry['label'])
        : $entry['symbol'];
}

function stopBgProcess($paramBgProcess)
{
    $osIssWindows   = chkOsIssWindows();
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
    $osIsWindows = chkOsIssWindows();
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
    $triggerLink = 'http://localhost/5d/message.php';

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
    $response = curl_exec($ch);
    curl_close($ch);

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