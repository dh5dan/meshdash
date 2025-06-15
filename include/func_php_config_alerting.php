<?php
function saveNotifySettings(): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/notification.db';
    $dbFilenameRoot = 'database/notification.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $callSigns       = $_POST['notifyCallSign'] ?? [];
    $soundFiles      = $_POST['notifySoundFile'] ?? [];
    $srcDstFlags     = $_POST['notifySrcDst'] ?? [];
    $enabledFlags    = $_POST['notifyEnabled'] ?? [];

    $itemCount = count($callSigns);

    if ($itemCount == 0)
    {
        return false;
    }

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');

    foreach ($callSigns as $key => $callSign)
    {
        $callSign  = strtoupper(trim($callSign));
        $soundFile = trim($soundFiles[$key] ?? '');
        $srcDst    = $srcDstFlags[$key] ?? 0;
        $notifyId  = $key;
        $enabled   = isset($enabledFlags[$key]) ? 1 : 0;

        $sql = "REPLACE INTO notification (notifyId,
                                           notifyCallSign,
                                           notifySoundFile,
                                           notifySrcDst,
                                           notifyEnabled
                                         ) VALUES ('$notifyId',
                                                   '$callSign', 
                                                   '$soundFile', 
                                                   $srcDst, 
                                                   $enabled
                                                  )";

        $logArray   = array();
        $logArray[] = "saveNotifySettings: notifyId: $notifyId";
        $logArray[] = "saveNotifySettings: callSign: $callSign";
        $logArray[] = "saveNotifySettings: soundFile: $soundFile";
        $logArray[] = "saveNotifySettings: srcDst: $srcDst";
        $logArray[] = "saveNotifySettings: enabled: $enabled";
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

function deleteNotifyItem($deleteNotifyItemId): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/notification.db';
    $dbFilenameRoot = 'database/notification.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    if (isset($deleteNotifyItemId) === false || empty($deleteNotifyItemId) === true)
    {
        return false;
    }

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');

    $sql = "DELETE FROM notification 
                   WHERE notifyId = $deleteNotifyItemId;
           ";

    $logArray   = array();
    $logArray[] = "deleteNotifyItem: deleteNotifyItemId: $deleteNotifyItemId";
    $logArray[] = "deleteNotifyItem: Database: $dbFilename";

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

function showAlertMediaFiles(bool $showTable = true)
{
    $returnArray   = array();
    $soundDir      = dirname(__DIR__) . '/sound';

    if (!is_dir($soundDir))
    {
        echo "Sound-Verzeichnis nicht gefunden.";
        return false;
    }

    // WAV & MP3 kombinieren
    $wavFiles = glob($soundDir . '/*.wav');
    $mp3Files = glob($soundDir . '/*.mp3');
    $files    = array_merge($wavFiles ?: [], $mp3Files ?: []);

    // Basis-URL ermitteln
    $scriptDir    = dirname($_SERVER['SCRIPT_NAME']);
    $baseUrl      = dirname($scriptDir);
    $downloadBase = $baseUrl . '/sound/';

    if (empty($files) === true)
    {
        echo "Keine Sound-Files vorhanden.";
        return false;
    }

    // Neueste zuerst
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    if ($showTable === true)
    {
        echo '<form id="frmUploadSoundFile" action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data">';
        echo '<input type="hidden" name="sendDataUpload" id="sendDataUpload" value="0" />';
        echo '<input type="hidden" name="deleteFileImage" id="deleteFileImage" value="" />';
        echo '<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />';
        echo '<table>';

        echo '<tr>';
        echo '<td ><label for="uploadSoundFile">Sound hinzufügen:&nbsp;</label></td>';
        echo '<td><input type="file" name="uploadSoundFile" id="uploadSoundFile" required></td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td ><label for="updateFile">Sound hochladen:&nbsp;</label></td>';
        echo '<td><input type="button" class="btnUploadSoundFile" id="btnUploadSoundFile" value="Datei hochladen"></td>';
        echo '</tr>';

        echo '</table>';

        echo '<div class="scrollable-container">';
        echo '<table class="backupTable">';
        echo '<tr>';
        echo '<th>Datum</th>';
        echo '<th>Uhrzeit</th>';
        echo '<th>Media-Datei</th>';
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
            echo '<img src="../image/delete_blk.png" data-delete ="'
                 . htmlspecialchars($filename)
                 . '" class="imageDelete" alt="delete">';
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
function selectSoundFile($arrayFiles, $selectedFile)
{
    if (empty($arrayFiles) === true)
    {
        echo '<option value="">Keine Sound-Files vorhanden</option>';
    }

    if ($selectedFile == '')
    {
        echo '<option value="">Sound-Files wählen</option>';
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
function getNotificationData(string $mode = 'all')
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename          = pathinfo(getcwd())['basename'];
    $dbFilenameSub     = '../database/notification.db';
    $dbFilenameRoot    = 'database/notification.db';
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
        $sqlAddon = " WHERE notifyEnabled = 1 ";
    }

    $db = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    $sql = "SELECT * 
              FROM notification
                   $sqlAddon
            ORDER BY notifyId;
           ";

    $logArray   = array();
    $logArray[] = "getNotificationData: Database: $dbFilename";

    $resNotify = safeDbRun( $db,  $sql, 'query', $logArray);

    if ($resNotify === false)
    {
        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    if ($resNotify !== false)
    {
        while ($row = $resNotify->fetchArray(SQLITE3_ASSOC))
        {
            $arrayReturn[$row['notifyCallSign']]['notifyId']        = $row['notifyId'];
            $arrayReturn[$row['notifyCallSign']]['notifySoundFile'] = $row['notifySoundFile'];
            $arrayReturn[$row['notifyCallSign']]['notifySrcDst']    = $row['notifySrcDst'];  // 0=Src, 1 = Dst
            $arrayReturn[$row['notifyCallSign']]['notifyEnabled']   = $row['notifyEnabled'];
        }
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return $arrayReturn;
}
