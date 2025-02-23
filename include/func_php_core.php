<?php

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

    $db  = new SQLite3($dbFilename);
    $db->busyTimeout(5000); // warte wenn busy in millisekunden
    $res = $db->query("
                        SELECT * FROM parameter AS pa 
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

    $paramValue = $paramValue != '' ? $paramValue : $paramText;

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

    #Escape Value
    $value = SQLite3::escapeString($value);

    $param_value = '';
    $param_text  = trim($value);

    if ($mode === 'int')
    {
        $param_value = trim($value);
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

    $db  = new SQLite3($dbFilename);
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

    #Escape Value
    $msgId   = SQLite3::escapeString($msgId);
    $value   = (int) $value;
    $errCode = $errCode ?? 0;
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

    foreach ($heardData AS $key)
    {
        $callSign = SQLite3::escapeString($key['callSign']);
        $date     = SQLite3::escapeString($key['date']);
        $time     = SQLite3::escapeString($key['time']);
        $hardware = SQLite3::escapeString($key['hardware']);
        $mod      = SQLite3::escapeString($key['mod']);
        $rssi     = SQLite3::escapeString($key['rssi']);
        $snr      = SQLite3::escapeString($key['snr']);
        $dist     = SQLite3::escapeString($key['dist']);
        $pl       = SQLite3::escapeString($key['pl']);
        $m        = SQLite3::escapeString($key['m']);

        $db->exec(
            "
                        REPLACE INTO mheard (
                                             timestamps, 
                                             mhCallSign, 
                                             mhDate, 
                                             mhTime, 
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

function updateMeshDashData($msgId, $key, $value): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/meshdash.db';
    $dbFilenameRoot = 'database/meshdash.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);

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