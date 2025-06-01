<?php

function saveHookSettings(): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/key_hooks.db';
    $dbFilenameRoot = 'database/key_hooks.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $keyHookTriggerArray   = $_POST['keyHookTrigger'] ?? [];
    $keyHookExecuteArray   = $_POST['keyHookExecute'] ?? [];
    $keyHookReturnMsgArray = $_POST['keyHookReturnMsg'] ?? [];
    $keyHookDmGrpIdArray   = $_POST['keyHookDmGrpId'] ?? [];
    $keyHookEnabledArray   = $_POST['keyHookEnabled'] ?? [];

    $itemCount = count($keyHookTriggerArray);

    if ($itemCount == 0)
    {
        return false;
    }

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');

    foreach ($keyHookTriggerArray as $key => $value)
    {
        $keyHookId        = $key;
        $keyHookTrigger   = trim($value);
        $keyHookExecute   = trim($keyHookExecuteArray[$key] ?? '');
        $keyHookReturnMsg = trim($keyHookReturnMsgArray[$key] ?? '');
        $keyHookDmGrpId   = $keyHookDmGrpIdArray[$key] ?? 999;
        $keyHookEnabled   = isset($keyHookEnabledArray[$key]) ? 1 : 0;

        $sql = "REPLACE INTO keyHooks (keyHookId,
                                       keyHookExecute, 
                                       keyHookTrigger, 
                                       keyHookReturnMsg, 
                                       keyHookDmGrpId, 
                                       keyHookEnabled
                                      ) VALUES ('$keyHookId',
                                                '$keyHookExecute', 
                                                '$keyHookTrigger', 
                                                '$keyHookReturnMsg', 
                                                 $keyHookDmGrpId,
                                                 $keyHookEnabled
                                                )";

        $logArray   = array();
        $logArray[] = "saveNotifySettings: keyHookId: $keyHookId";
        $logArray[] = "saveNotifySettings: keyHookExecute: $keyHookExecute";
        $logArray[] = "saveNotifySettings: keyHookTrigger: $keyHookTrigger";
        $logArray[] = "saveNotifySettings: keyHookReturnMsg: $keyHookReturnMsg";
        $logArray[] = "saveNotifySettings: keyHookDmGrpId: $keyHookDmGrpId";
        $logArray[] = "saveNotifySettings: keyHookEnabled: $keyHookEnabled";
        $logArray[] = "saveNotifySettings: Database: $dbFilename";

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

function deleteHookItem($deleteHookItemId): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/key_hooks.db';
    $dbFilenameRoot = 'database/key_hooks.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    if (isset($deleteHookItemId) === false || empty($deleteHookItemId) === true)
    {
        return false;
    }

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');

    $sql = "DELETE FROM keyHooks 
                   WHERE keyHookId = $deleteHookItemId;
           ";

    $logArray   = array();
    $logArray[] = "deleteHookItem: deleteNotifyItemId: $deleteHookItemId";
    $logArray[] = "deleteHookItem: Database: $dbFilename";

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

function getKeyWordHooks(string $mode = 'all')
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename          = pathinfo(getcwd())['basename'];
    $dbFilenameSub     = '../database/key_hooks.db';
    $dbFilenameRoot    = 'database/key_hooks.db';
    $dbFilename        = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $arrayReturn       = array();

    // Prüfen, ob Datenbank existiert
    if (!file_exists($dbFilename))
    {
        return false;
    }

    $sqlAddon = '';

    if ($mode === 'active')
    {
        $sqlAddon = " WHERE keyHookEnabled = 1 ";
    }

    $db = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    $sql = "SELECT * 
              FROM keyHooks
                   $sqlAddon
            ORDER BY keyHookId;
           ";

    $logArray   = array();
    $logArray[] = "getKeyWordHooks: Database: $dbFilename";

    $resKeyHooks = safeDbRun( $db,  $sql, 'query', $logArray);

    if ($resKeyHooks === false)
    {
        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    if ($resKeyHooks !== false)
    {
        while ($row = $resKeyHooks->fetchArray(SQLITE3_ASSOC))
        {
            $arrayReturn[$row['keyHookId']]['keyHookExecute']   = $row['keyHookExecute'];
            $arrayReturn[$row['keyHookId']]['keyHookTrigger']   = $row['keyHookTrigger'];
            $arrayReturn[$row['keyHookId']]['keyHookReturnMsg'] = $row['keyHookReturnMsg'];
            $arrayReturn[$row['keyHookId']]['keyHookDmGrpId']   = $row['keyHookDmGrpId'];
            $arrayReturn[$row['keyHookId']]['keyHookEnabled']   = $row['keyHookEnabled'];
        }
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return $arrayReturn;
}

function selectScriptFile($arrayFiles, $selectedFile)
{
    if (empty($arrayFiles) === true)
    {
        echo '<option value="">Keine Script-Files vorhanden</option>';
    }

    if ($selectedFile == '')
    {
        echo '<option value="">Script-Files wählen</option>';
    }

    foreach ($arrayFiles as $file)
    {
        if($selectedFile === $file)
        {
            echo '<option value="' . $file . '" selected>' . $file . '</option>';
        }
        else
        {
            echo '<option value="' . $file . '">' . $file . '</option>';
        }
    }
}

function showKeyScriptFiles(bool $showTable = true)
{
    $returnArray   = array();
    $executeDir    = dirname(__DIR__) . '/execute';

    // Basis-URL ermitteln
    $scriptDir    = dirname($_SERVER['SCRIPT_NAME']);
    $baseUrl      = dirname($scriptDir);
    $downloadBase = $baseUrl . '/execute/';

    if (!is_dir($executeDir))
    {
        echo "Execute-Verzeichnis nicht gefunden.";
        return false;
    }

    // WAV & MP3 kombinieren
    $cmdFiles = glob($executeDir . '/*.cmd');
    $shFiles  = glob($executeDir . '/*.sh');
    $batFiles = glob($executeDir . '/*.bat');
    $exeFiles = glob($executeDir . '/*.exe');
    $files    = array_merge($cmdFiles ?: [], $shFiles ?: [], $batFiles ?: [], $exeFiles ?: []);

    if (empty($files))
    {
        echo "Keine Script-Files vorhanden.";
        return false;
    }

    // Neueste zuerst
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    if ($showTable === true)
    {
        echo '<form id="frmUploadScriptFile" action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data">';
        echo '<input type="hidden" name="sendDataUpload" id="sendDataUpload" value="0" />';
        echo '<input type="hidden" name="deleteFileImage" id="deleteFileImage" value="" />';
        echo '<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />';
        echo '<table>';

        echo '<tr>';
        echo '<td ><label for="uploadScriptFile">Skript hinzufügen:&nbsp;</label></td>';
        echo '<td><input type="file" name="uploadScriptFile" id="uploadScriptFile" required></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td ><label for="btnUploadScriptFile">Skript hochladen:&nbsp;</label></td>';
        echo '<td><input type="button" class="btnUploadScriptFile" id="btnUploadScriptFile" value="Datei hochladen"></td>';
        echo '</tr>';

        echo '</table>';

        echo '<div class="scrollable-container">';
        echo '<table class="backupTable">';
        echo '<tr>';
        echo '<th>Datum</th>';
        echo '<th>Uhrzeit</th>';
        echo '<th>Script-Datei</th>';
        echo '<th colspan="2">&nbsp;</th>';
        echo '</tr>';
    }

    foreach ($files as $file)
    {
        $filename      = basename($file);
        $fileTime      = filemtime($file);
        $datum         = date('d.m.Y', $fileTime);
        $uhrzeit       = date('H:i:s', $fileTime);
        $downloadUrl   = $downloadBase . rawurlencode($filename);
        $returnArray[] = htmlspecialchars($filename);

        if ($showTable === true)
        {
            echo '<tr>';
            echo '<td>' . $datum . '</td>';
            echo '<td>' . $uhrzeit . '</td>';
            echo '<td>' . htmlspecialchars($filename) . '</td>';
            echo '<td>';
            echo '<a href="' . $downloadUrl . '">';
            echo '<img src="../image/download_blk.png" class="imageDownload" alt="download">';
            echo '</a>';
            echo '</td>';
            echo '<td>';
            echo '<img src="../image/delete_blk.png" data-delete ="' . htmlspecialchars(
                    $filename
                ) . '" class="imageDelete" alt="delete">';
            echo '</td>';
            echo '</tr>';
        }
    }

    if ($showTable === true)
    {
        echo '</table>';
        echo '</div>';

        echo "</form>";
    }

    return $returnArray;
}
