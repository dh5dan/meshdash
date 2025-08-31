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
                echo '<br><span class="failureHint">Fehler beim Löschen von ' . $file .' aufgetreten!</span>';
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


