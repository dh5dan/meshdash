<?php

function unzipRestore($zipFile, $tempDir): bool
{
    $zip = new ZipArchive();
    if ($zip->open($zipFile) === true)
    {
        $zip->extractTo($tempDir);
        $zip->close();

        return true;
    }

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

            $datum       = "$matches[3].$matches[2].$matches[1]";
            $uhrzeit     = "$matches[4]:$matches[5]:$matches[6]";
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

function checkValidRestorePackage($uploadFile, $debugFlag): bool
{
    $uploadFileFullPath = "../$uploadFile";

    $expectedFiles = [
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
    if ($zip->open($uploadFileFullPath) === true)
    {
        $valid = true;

        // Alle ZIP-Einträge einmalig holen
        $zipEntries = [];
        for ($i = 0; $i < $zip->numFiles; $i++)
        {
            $zipEntries[] = $zip->getNameIndex($i);
        }

        foreach ($expectedFiles as $expected)
        {
            $found = false;

            #Wenn auf Verzeichnis geprüft werden soll
            if (substr($expected, -1) === '/')
            {
                // Verzeichnisprüfung: mindestens ein Eintrag beginnt mit diesem Pfad
                foreach ($zipEntries as $entry)
                {
                    if (strpos($entry, $expected) === 0)
                    {
                        $found = true;
                        break;
                    }
                }
            }
            else
            {
                // Datei-Prüfung: exakter Treffer
                $found = in_array($expected, $zipEntries);
            }

            if (!$found)
            {
                echo "<br>zip-file: $uploadFileFullPath";
                echo "- Fehler: Die erwartete Datei oder das Verzeichnis '$expected' fehlt in der ZIP-Datei.<br>";

                if ($debugFlag === true)
                {
                    $tt        = " Die erwartete Datei oder das Verzeichnis: $expected fehlt in der ZIP-Datei.";
                    $errorText = date('Y-m-d H:i:s') . ' filesArray:' . $tt . "\n";
                    file_put_contents('../log/debug_restore.log', $errorText, FILE_APPEND);
                }

                $valid = false;
            }
        }

        $zip->close();

        return $valid;
    }
    else
    {
        echo '<br><span class="failureHint">Fehler: Die ZIP-Datei ' . $uploadFileFullPath . ' konnte nicht geöffnet werden.</span>';

        if ($debugFlag === true)
        {
            $tt        = " Fehler: Die ZIP-Datei '$uploadFile' konnte nicht geöffnet werden. Exit.";
            $errorText = date('Y-m-d H:i:s') . ' filesArray:' . $tt . "\n";
            file_put_contents('../log/debug_restore.log', $errorText, FILE_APPEND);
        }

        return false;
    }
}

function checkDatabaseRestore(): bool
{
    $dir = '../database';

    if (!is_dir($dir))
    {
        echo "Fehler: Verzeichnis 'database' existiert nicht.";

        return false;
    }

    $files = scandir($dir);
    $bakFiles = [];

    // 1. Sammle .bak-Dateien
    foreach ($files as $file)
    {
        if (substr($file, -4) === '.bak')
        {
            $bakFiles[] = $file;
        }
    }

    if (empty($bakFiles))
    {
        return true;
    }

    // 2. Lösche alle Nicht-.bak-Dateien
    foreach ($files as $file)
    {
        $fullPath = $dir . '/' . $file;

        // Nur Dateien, keine Verzeichnisse
        if (is_file($fullPath) && substr($file, -4) !== '.bak')
        {
            if (!unlink($fullPath))
            {
                echo '<br><span class="failureHint">Fehler beim  Löschen von ' . $file .' aufgetreten!</span>';
            }
        }
    }

    // 3. Benenne .bak-Dateien in .db um
    foreach ($bakFiles as $bakFile)
    {
        $bakPath = $dir . '/' . $bakFile;
        $dbFile  = substr($bakFile, 0, -4) . '.db';
        $dbPath  = $dir . '/' . $dbFile;

        if (file_exists($dbPath))
        {
            unlink($dbPath); // ggf. alte .db löschen
        }

        if (!rename($bakPath, $dbPath))
        {
            echo '<br><span class="failureHint">Fehler beim Umbenennen von ' . $bakFile .' aufgetreten!</span>';
        }
    }

    return true;
}


