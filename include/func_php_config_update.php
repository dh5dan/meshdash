<?php

function backupApp($sourceDir, $backupDir)
{
    $zip            = new ZipArchive();
    $backupFullPath = $backupDir . '/backup_' . date('Ymd_His') . '.zip';
    $backupFile     = 'backup_' . date('Ymd_His') . '.zip';

    $excludeList = [
        'backup/',   // komplettes Verzeichnis
        '.git/',   // komplettes Verzeichnis
        '.idea/',        // komplettes Verzeichnis
        'test/',      // komplettes Verzeichnis
        '.gitignore', // einzelne Datei
    ];

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
                if (strpos($relativePath, $excluded) === 0)
                {
                    continue 2; // Datei überspringen
                }
            }

            // Datei ins ZIP-Archiv hinzufügen
            $zip->addFile($file->getPathname(), $relativePath);
        }

        $zip->close();
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
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($iterator as $file)
    {
        if ($file->isDir()) {
            // Verzeichnisse auf 755 setzen
            chmod($file, 0755);
        } else {
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
        #echo'<th>&nbsp;</th>';
    echo'</tr>';

    foreach ($files as $file) {
        $filename = basename($file);
        if (preg_match('/backup_(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})\.zip/', $filename, $matches)) {
            $datum   = "{$matches[3]}.{$matches[2]}.{$matches[1]}";
            $uhrzeit = "{$matches[4]}:{$matches[5]}:{$matches[6]}";
            $downloadUrl = $downloadBase . $filename;

            echo '<tr>';
            echo '<td>' . $datum . '</td>';
            echo '<td>' . $uhrzeit . '</td>';
            echo '<td>' . $filename . '</td>';
            echo '<td>';
                echo'<a href="' . $downloadUrl . '">';
                    echo'<img src="../image/download_blk.png" class="imageDownload" alt="download">';
                echo'</a>';
            echo'</td>';
            echo '<td>';
                echo '<img src="../image/delete_blk.png" data-delete ="' . $filename . '" class="imageDelete" alt="delete">';
            echo'</td>';
            echo '</tr>';
        }
    }

    echo '</table>';
    echo '</div>';
}




