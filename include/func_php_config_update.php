<?php
function backupApp($sourceDir, $backupDir): bool|string
{
    $zip                   = new ZipArchive();
    $backupFullPath        = $backupDir . '/backup_' . date('Ymd_His') . '.zip';
    $backupFile            = 'backup_' . date('Ymd_His') . '.zip';
    $doNotBackupDb         = getParamData('doNotBackupDb');
    $arrayDeleteDbBakFiles = array();

    $excludeList = [
        'backup/',   // komplettes Verzeichnis
        '.git/',     // komplettes Verzeichnis
        '.idea/',    // komplettes Verzeichnis
        'test/',     // komplettes Verzeichnis
        'docs/',     // komplettes Verzeichnis
        'export/',     // komplettes Verzeichnis
        '.gitignore', // einzelne Datei
        'auto_purge.lock', // einzelne Datei
    ];

    // Nur diese Dateitypen in "execute/" sichern
    $allowedExecuteExtensions = ['sh', 'py', 'cmd', 'bat', 'exe', 'com'];
    $executeDir               = 'execute/';

    # Falls Datenbank-Backup ausgeschlossen werden soll
    if ($doNotBackupDb == 1)
    {
        echo '<span class="failureHint">Datenbank wird nicht gesichert!</span>';
        $excludeList[] = "database/";   // komplettes Verzeichnis
    }
    else
    {
        $resDoDatabaseCopyForBackup = doDatabaseCopyForBackup();

        if ($resDoDatabaseCopyForBackup === false)
        {
            echo "<br>Fehler beim Anlegen des Backups der Datenbank aufgetreten!";
            return false;
        }
    }

    if ($zip->open($backupFullPath, ZipArchive::CREATE) === true)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file)
        {
            if ($file->isDir()) continue; // Verzeichnisse überspringen

            // Relativen Pfad berechnen (richtig für Windows und Linux)
            $absolutePath      = $file->getPathname();
            $relativePath      = str_replace($sourceDir . DIRECTORY_SEPARATOR, '', $absolutePath);
            $relativePath      = str_replace('\\', '/', $relativePath); // Für Windows korrigieren
            $doDeleteDBBakFile = false; // Flag, um nach dem Archivieren die temporäre .bak-Datei zu löschen

            // Nur .bak-Dateien im Hauptverzeichnis "database/" zulassen
            if (preg_match('#^database/([^/]+\.bak)$#', $relativePath))
            {
                $doDeleteDBBakFile = true;               // zulässig
            }
            elseif (preg_match('#^database/#', $relativePath))
            {
                continue; // andere Dateien im database/-Ordner ignorieren
            }

            // Prüfen, ob die Datei oder ihr Verzeichnis ausgeschlossen werden soll
            foreach ($excludeList as $excluded)
            {
                if (preg_match('/^' . preg_quote($excluded, '/') . '/', $relativePath))
                {
                    continue 2; // Datei überspringen
                }
            }

            // Falls Datei im "execute/"-Verzeichnis liegt, nur suffix aus dem AllowArray zulassen
            if (preg_match('/^' . preg_quote($executeDir, '/') . '/', $relativePath))
            {
                $fileExtension = pathinfo($relativePath, PATHINFO_EXTENSION);
                if (!in_array($fileExtension, $allowedExecuteExtensions))
                {
                    continue; // Alle anderen Dateien in "execute/" werden ignoriert
                }
            }

            // Datei ins ZIP-Archiv hinzufügen
            $zip->addFile($file->getPathname(), $relativePath);

            #Packe zu löschende Datenbank BAK-Files in Array, da sie hier noch nicht gelöscht werden können
            if ($doDeleteDBBakFile === true)
            {
                $arrayDeleteDbBakFiles[] = "../" . $relativePath;
            }
        }

        @$zip->close();

        if (!file_exists($backupFullPath))
        {
            echo "<br>Fehler beim Anlegen des Backups $backupFile aufgetreten!";
            return false;
        }

        foreach ($arrayDeleteDbBakFiles as $file)
        {
            unlink($file);
        }

        return $backupFile;
    }
    else
    {
        return false;
    }
}

function unzipUpdate($zipFile, $tempDir): bool
{
    $zip = new ZipArchive();
    if ($zip->open($zipFile) === true)
    {
        $zip->extractTo($tempDir);
        $zip->close();

        return true;
    }
    else
    {
        return false;
    }
}

function updateFiles($updateDir, $targetDir, $protectedDirs): bool
{
    $dirSeparator = DIRECTORY_SEPARATOR; // Plattformübergreifend

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($updateDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file)
    {
        // Nur Dateien und Verzeichnisse im Ordner behandeln
        if ($file->isDir())
        {
            $dirName = basename($file);
            if (in_array($dirName, $protectedDirs))
            {
                continue; // Geschützte Verzeichnisse überspringen
            }

            // Den relativen Pfad von $file in Bezug auf $updateDir erhalten
            $relativePath  = str_replace($updateDir . $dirSeparator, '', $file);
            $relativePath  = preg_replace('#[/\\\\]\.+$#', '', $relativePath);
            $targetDirPath = $targetDir . $dirSeparator . $relativePath;

            // Verzeichnis erstellen, falls es noch nicht existiert
            if (!file_exists($targetDirPath))
            {
                if (mkdir($targetDirPath, 0755, true) || is_dir($targetDirPath))
                {
                    // Verzeichnis erfolgreich erstellt
                    echo '';
                }
                else
                {
                    echo '<br><span class="failureHint">Fehler: Verzeichnis konnte nicht erstellt werden: ' . $targetDirPath . '</span>';

                    return false;
                }
            }
        }
        else
        {
            // Den relativen Pfad von $file in Bezug auf $updateDir berechnen
            $relativePath   = str_replace($updateDir . $dirSeparator, '', $file);
            $targetFilePath = $targetDir . $dirSeparator . $relativePath;

            // Verzeichnisse im Ziel erstellen, falls nötig
            $targetFileDir = dirname($targetFilePath);
            if (!file_exists($targetFileDir))
            {
                mkdir($targetFileDir, 0755, true);
            }

            // Datei kopieren
            if (!copy($file, $targetFilePath))
            {
                echo '<br><span class="failureHint">Fehler: Datei konnte nicht kopiert werden: ' . $relativePath . '</span>';

                return false;
            }
        }
    }

    return true;
}

function setPermissions($dir)
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        FilesystemIterator::SKIP_DOTS,
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file)
    {
        $filePath = $file->getPathname(); // Absoluter Pfad

        if ($file->isDir())
        {
            // Verzeichnisse auf 755 setzen
            @chmod($file, 0755);

            // Falls das Verzeichnis "execute" heißt, setze auch seinen Inhalt auf 755
            if ($file->getFilename() === 'execute')
            {
                $subIterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($filePath, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($subIterator as $subFile)
                {
                    @chmod($subFile->getPathname(), 0755);
                }
            }
        }
        else
        {
            // Dateien auf 644 setzen
            @chmod($file, 0644);
        }
    }
}

function cleanUp($tempDir)
{
    // Löscht den temporären Ordner nach der Aktualisierung
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($tempDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileInfo)
    {
        $todo = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileInfo->getRealPath());
    }

    rmdir($tempDir); // Löscht das Update-Verzeichnis
}

function checkValidUpdatePackage($uploadFile, $debugFlag)
{
    $expectedFiles = [
        'backup/',
        'css/',
        'database/',
        'dbinc/',
        'dbinc/param.php',
        'execute/',
        'image/',
        'image/loader/ajax-loader1.gif',
        'image/loader/ajax-loader2.gif',
        'include/',
        'jquery/',
        'jquery/css/',
        'menu/',
        'script/',
        'sound/',
        '.htaccess',
        '.user.ini',
        // Weitere erwartete Dateien und Verzeichnisse
    ];

    $zip = new ZipArchive();
    if ($zip->open($uploadFile) === true)
    {
        // ZIP-Datei erfolgreich geöffnet
        $valid = true;
        foreach ($expectedFiles as $expectedFile)
        {
            if ($zip->locateName($expectedFile) === false)
            {
                #echo "Fehler: Die erwartete Datei oder das Verzeichnis '$expectedFile' fehlt in der ZIP-Datei.<br>";

                if ($debugFlag === true)
                {
                    $tt        = " Die erwartete Datei oder das Verzeichnis: $expectedFile fehlt in der ZIP-Datei.";
                    $errorText = date('Y-m-d H:i:s') . ' filesArray:' . $tt . "\n";

                    file_put_contents('../log/debug_update.log', $errorText, FILE_APPEND);
                }


                $valid = false;
            }
        }

        $zip->close();

        return $valid;
    }
    else
    {
        echo '<br><span class="failureHint">Fehler: Die ZIP-Datei ' . $uploadFile . 'konnte nicht geöffnet werden.</span>';

        if ($debugFlag === true)
        {
            $tt        = " Fehler: Die ZIP-Datei ' . $uploadFile . 'konnte nicht geöffnet werden.Exit";
            $errorText = date('Y-m-d H:i:s') . ' filesArray:' . $tt . "\n";

            file_put_contents('../log/debug_update.log', $errorText, FILE_APPEND);
        }
        exit;
    }
}

function getLatestRelease()
{
    // get_latest_release.php

    $repoOwner = 'dh5dan';  // Deinen GitHub-Nutzername
    $repoName  = 'meshdash';         // Repository-Name
    $apiUrl    = "https://api.github.com/repos/$repoOwner/$repoName/releases/latest";

    // cURL initialisieren
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'MeshDash-Update-Script'); // User-Agent muss gesetzt sein

    $response = curl_exec($ch);
    if (curl_errno($ch))
    {
        die('Fehler: ' . curl_error($ch));
    }
    curl_close($ch);

    $releaseData = json_decode($response, true);
    if (!$releaseData)
    {
        die('Konnte Release-Daten nicht lesen.');
    }

    // Gehe davon aus, dass du ein bestimmtes Release-Asset herunterladen möchtest,
    // zum Beispiel eine ZIP-Datei, und suche danach:
    $downloadUrl = '';
    foreach ($releaseData['assets'] as $asset)
    {
        if (stripos($asset['name'], 'MeshDash') !== false)
        { // Passende Datei finden
            $downloadUrl = $asset['browser_download_url'];
            break;
        }
    }

    if (!$downloadUrl)
    {
        die('Kein passendes Release-Asset gefunden.');
    }

    // Umleiten auf die Download-URL – der Browser startet dann den Download
    header('Location: ' . $downloadUrl);
}

function getLatestChangelog()
{
    // get_latest_release.php
    $repoOwner   = 'dh5dan';  // Deinen GitHub-Nutzername
    $repoName    = 'meshdash';         // Repository-Name
    $apiUrl      = "https://api.github.com/repos/$repoOwner/$repoName/releases/latest";
    $arrayReturn = array();

    // cURL initialisieren
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'MeshDash-Update-Script'); // User-Agent muss gesetzt sein

    $response = curl_exec($ch);
    if (curl_errno($ch))
    {
        die('Fehler: ' . curl_error($ch));
    }
    curl_close($ch);

    $releaseData = json_decode($response, true);

    if (!$releaseData)
    {
        die('Konnte Changelog-Daten nicht lesen.');
    }

    $arrayReturn['version'] = $releaseData['tag_name'];
    $arrayReturn['body']    = str_replace("\r\n", '<br>', $releaseData['body']);

    return $arrayReturn;
}

function doDatabaseCopyForBackup(): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename      = pathinfo(getcwd())['basename'];
    $dbDirSub      = '../database';
    $dbDirRoot     = 'database';
    $dbDir         = $basename == 'menu' ? $dbDirSub : $dbDirRoot;
    $suffix        = '.db';
    $backupSuffix  = '.bak';
    $errorOccurred = false;
    $osIssWindows  = chkOsIsWindows();

    if (!is_dir($dbDir))
    {
        $tt        = "Datenbankverzeichnis nicht gefunden: $dbDir\n";
        $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

        file_put_contents('../log/db_backup_error.log', $errorText, FILE_APPEND);
        echo "<br>$tt";

        return false;
    }

    // Alle *.db-Dateien im Verzeichnis auflisten
    $dbFiles = glob($dbDir . '/*' . $suffix);

    foreach ($dbFiles as $dbPath)
    {
        $dbName     = basename($dbPath);
        $backupPath = realpath($dbDir) . DIRECTORY_SEPARATOR . basename($dbName, $suffix) . $backupSuffix;

        if ($osIssWindows === false)
        {
            $backupPath = $dbDir . '/' . basename($dbName, $suffix) . $backupSuffix;
        }

        // Backup-Datei vor VACUUM INTO sicher löschen wenn noch vorhanden
        if (file_exists($backupPath))
        {
            @unlink($backupPath);

            // Sicherheitscheck: Ist sie wirklich weg?
            if (file_exists($backupPath))
            {
                $tt        = "Konnte $backupPath nicht löschen (wird gesperrt?)\n";
                $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                file_put_contents('../log/db_backup_error.log', $errorText, FILE_APPEND);
                echo "<br>$tt";

                $errorOccurred = true;
                continue;
            }
        }

        try
        {
            $db = new SQLite3($dbPath, SQLITE3_OPEN_READONLY);
            $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
            $query = "VACUUM main INTO '$backupPath'";

            $logArray   = array();
            $logArray[] = "UpdateVacuumCpy: Database: $backupPath";

            $res = safeDbRun($db, $query, 'exec', $logArray);

            if ($res === false)
            {
                $tt        = "Fehler beim VACUUM DB-Backup von $dbPath: " . $db->lastErrorMsg() . "\n";
                $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                file_put_contents('../log/db_backup_error.log', $errorText, FILE_APPEND);

                echo '<br><span class="failureHint">'
                    . 'Fehler beim VACUUM DB-Backup von ' . $dbPath . ': '
                    . $db->lastErrorMsg()
                    . '</span>';
                $errorOccurred = true;
            }

            #Close and write Back WAL
            $db->close();
            unset($db);

            #BackupDB in WAL Modus schalten.
            #Gibt sonst DB-Locks, wenn die getauscht wird, da sie bei vacuum im DELETE Mode ist.
            $backupDb = new SQLite3($backupPath);
            $backupDb->exec("PRAGMA journal_mode=WAL;");
            $backupDb->close();
            unset($backupDb);


            // Plausibilitätsprüfung: Größe muss minimal > 1 KB sein
            if (!file_exists($backupPath) || filesize($backupPath) < 1024)
            {
                $tt        = "Backup-Datei $backupPath ist verdächtig klein! Wird gelöscht.\n";
                $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                file_put_contents('../log/db_backup_error.log', $errorText, FILE_APPEND);
                echo "<br>$tt";

                @unlink($backupPath);
                $errorOccurred = true;
            }
        }
        catch (Exception $e)
        {
            $tt        = "Fehler bei $dbName: " . $e->getMessage() . "\n";
            $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

            file_put_contents('../log/db_backup_error.log', $errorText, FILE_APPEND);
            echo "<br>$tt";

            $errorOccurred = true;
        }
    }

    if ($errorOccurred === true)
    {
        return false;
    }

    return true;
}


