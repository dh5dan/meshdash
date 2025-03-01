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
    $db->exec('PRAGMA synchronous = NORMAL;');

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
            echo "<br>'1.10.02' ist kleiner als " . VERSION;
        }

        // SQLite3-Datenbank prüfen ob in Datenbank meshdash die Tabelle firmware existiert
        $table  = 'meshdash';
        $column = 'firmware';

        if (!columnExists($database, $table, $column))
        {
            if ($debugFlag === true)
            {
                echo "<br>Die Spalte: $column in Tabelle: $table existiert nicht.";
            }

            #Check what oS is running
            $osIssWindows = chkOsIssWindows();

            #Hole Task Command abhängig vom OS
            $checkTaskCmd = getTaskCmd();

            // Spalte hinzufügen
            addColumn($database, $table, $column);

            ## Prozess neu laden damit Feld befüllt wird

            # Stop BG-Process
            $paramBgProcess['checkTaskCmd'] = $checkTaskCmd;
            $paramBgProcess['osIssWindows'] = $osIssWindows;
            checkBgProcess($paramBgProcess);

            ##start BG-Process
            $paramStartBgProcess['taskResult']   = '';
            $paramStartBgProcess['osIssWindows'] = $osIssWindows;
            $paramStartBgProcess['checkTaskCmd'] = $checkTaskCmd;
            startBgProcess($paramStartBgProcess);
        }
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

function getTaskCmd(): string
{
    #Check what oS is running
    $osIssWindows = chkOsIssWindows();

    #Hinweis Pgrep -x funktioniert nicht, wenn man die PHP Datei ermitteln muss
    return  $osIssWindows === true ? 'tasklist | find "php.exe"' : "pgrep -a -f udp_receiver.php | grep -v pgrep | awk '{print $1}'";
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