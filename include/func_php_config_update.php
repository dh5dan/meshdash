<?php

function backupApp($sourceDir, $backupDir)
{
    $zip            = new ZipArchive();
    $backupFullPath = $backupDir . '/backup_' . date('Ymd_His') . '.zip';
    $backupFile     = 'backup_' . date('Ymd_His') . '.zip';
    $doNotBackupDb  = getParamData('doNotBackupDb');

    $excludeList = [
        'backup/',   // komplettes Verzeichnis
        '.git/',     // komplettes Verzeichnis
        '.idea/',    // komplettes Verzeichnis
        'test/',     // komplettes Verzeichnis
        '.gitignore' // einzelne Datei
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
            $relativePath = str_replace($sourceDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath); // Für Windows korrigieren

            // Prüfen, ob die Datei oder ihr Verzeichnis ausgeschlossen werden soll
            foreach ($excludeList as $excluded)
            {
                if (preg_match('/^' . preg_quote($excluded, '/') . '/', $relativePath))
                {
                    continue 2; // Datei überspringen
                }
            }

            // Falls Datei im "execute/"-Verzeichnis liegt, nur *.sh und *.py zulassen
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
        }

        @$zip->close();

        if (!file_exists($backupFullPath))
        {
            echo "<br> Fehler beim Anlegen des Backups $backupFile aufgetreten!";
            return false;
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
            chmod($file, 0755);

            // Falls das Verzeichnis "execute" heißt, setze auch seinen Inhalt auf 755
            if ($file->getFilename() === 'execute')
            {
                $subIterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($filePath, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($subIterator as $subFile)
                {
                    chmod($subFile->getPathname(), 0755);
                }
            }
        }
        else
        {
            // Dateien auf 644 setzen
            chmod($file, 0644);
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
        echo'<th>Datum</th>';
        echo'<th>Uhrzeit</th>';
        echo'<th>Backup-Datei</th>';
        echo'<th colspan="2">&nbsp;</th>';
    echo'</tr>';

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

            $datum       = "{$matches[3]}.{$matches[2]}.{$matches[1]}";
            $uhrzeit     = "{$matches[4]}:{$matches[5]}:{$matches[6]}";
            $downloadUrl = $downloadBase . $filename;

            echo '<tr>';
            echo '<td>' . $datum . '</td>';
            echo '<td>' . $uhrzeit . '</td>';
            echo '<td>' . $filename . '</td>';
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
    if ($zip->open($_FILES['updateFile']['tmp_name']) === true)
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
    $apiUrl    = "https://api.github.com/repos/{$repoOwner}/{$repoName}/releases/latest";

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




