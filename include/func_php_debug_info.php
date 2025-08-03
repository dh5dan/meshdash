<?php
function showLogFiles()
{
    $execDirLog    = 'log';
    $basename      = pathinfo(getcwd())['basename'];
    $logDirSub     = '../' . $execDirLog;
    $logDirRoot    = $execDirLog;
    $logDir        = $basename == 'menu' ? $logDirSub : $logDirRoot;

    if (!is_dir($logDir))
    {
        echo "Log-Verzeichnis nicht gefunden.";

        return;
    }

    $files = glob($logDir . '/*.log');
    if (!$files)
    {
        echo '<div class="scrollable-container">';
        echo '<table class="logTable">';
        echo '<tr>';
        echo '<td>Keine Logs zur Anzeige vorhanden.</td>';
        echo'</tr>';
        echo '</table>';
        return;
    }

    // Sortiere nach Änderungsdatum (neueste zuerst)
    usort($files, function ($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    // Ermittele den Download-Pfad:
    // Skript wird z.B. in /meshdash/menu ausgeführt, Backups liegen in /meshdash/backup.
    // Wir nehmen dirname von SCRIPT_NAME, um den Root-Ordner zu erhalten
    $scriptDir    = dirname($_SERVER['SCRIPT_NAME']); // z.B. "/meshdash/menu"
    $baseUrl      = dirname($scriptDir);                // z.B. "/meshdash"
    $downloadBase = $baseUrl . '/'.$execDirLog.'/';           // z.B. "/meshdash/log/"

    echo '<div class="scrollable-container">';
    echo '<table class="logTable">';
    echo '<tr>';
    echo'<th><span data-i18n="submenu.debug_info.lbl.date">Datum</span></th>';
    echo'<th><span data-i18n="submenu.debug_info.lbl.time">Uhrzeit</span></th>';
    echo'<th><span data-i18n="submenu.debug_info.lbl.log-file">Log-Datei</span></th>';
    echo'<th colspan="2">&nbsp;</th>';
    echo'</tr>';

    foreach ($files as $file)
    {
        $filename = basename($file);
        if (preg_match('/\.log/', $filename))
        {
            $fileDateTime = is_readable($file) ? filemtime($file) : '0000-00-00 00:00:00'; // Prevents if File is locked
            $datum        = date('Y-m-d', $fileDateTime);
            $uhrzeit      = date('H:i:s', $fileDateTime);

            $downloadUrl = $downloadBase . $filename;

            $heute      = date('Y-m-d');
            $gestern    = date('Y-m-d', strtotime('-1 day'));
            $dateOfFile = date('Y-m-d', $fileDateTime);
            $rowClass   = 'log-old';

            if ($dateOfFile === $heute)
            {
                $rowClass = 'log-today';
            }
            elseif ($dateOfFile === $gestern)
            {
                $rowClass = 'log-yesterday';
            }

            echo '<tr>';
            echo '<td class="' . $rowClass . '">' . $datum . '</td>';
            echo '<td class="' . $rowClass . '">' . $uhrzeit . '</td>';
            echo '<td class="' . $rowClass . '">' . $filename . '</td>';
            echo '<td>';
            echo '<a href="' . $downloadUrl . '" target="_blank">';
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
function getCronEntries()
{
    exec('crontab -l 2>/dev/null', $cronJobs);// Die Crontab auslesen
    if (!empty($cronJobs))
    {
        foreach ($cronJobs as $index => $cronJob)
        {
            echo '<tr>';
            echo '<td>' . ($index === 0 ? 'CronJobs (www-data):' : '&nbsp;') . '</td>';
            echo '<td>' . htmlspecialchars($cronJob) . '</td>';
            echo '</tr>';
        }
    }
    else
    {
        echo '<tr>';
        echo '<td>CronJobs (www-data):</td>';
        echo '<td>Kein Eintrag</td>';
        echo '</tr>';
    }
}

function getServerSoftware()
{
    return $_SERVER['SERVER_SOFTWARE'] ?? 'Nicht verfügbar';
}
function getPhpConfig()
{
    $phpIniArray = array(
        'memory_limit'        => ini_get('memory_limit'),
        'max_execution_time'  => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size'       => ini_get('post_max_size'),
        'max_input_vars'      => ini_get('max_input_vars'),
        'file_uploads'        => ini_get('file_uploads'),
    );

    // Min-Werte für jede Konfiguration
    $minValues = array(
        'memory_limit'        => 128,   // Min. 128M
        'max_execution_time'  => 30,    // Min. 30 Sekunden
        'upload_max_filesize' => 30, // Min. 30M
        'post_max_size'       => 10, // Min. 10M
        'max_input_vars'      => 1000, // Min. 1000
        'file_uploads'        => 1,  // Min. 1
    );

    $maxLen      = 0;
    $maxLenValue = 0;

    foreach ($phpIniArray as $phpIniKey => $phpIniValue)
    {
        $result[$phpIniKey] = $phpIniValue;

        // Maximale Länge ermitteln
        $maxLen      = max($maxLen, strlen($phpIniKey));
        $maxLenValue = max($maxLenValue, strlen($phpIniValue));
    }

    if (!empty($result))
    {
        echo '<tr>';
        echo '<td style="vertical-align: top;"><span data-i18n="submenu.debug_info.lbl.php-value">PHP-Values</span>:</td>';
        echo '<td><pre style="margin:0; font-family: monospace;">';

        foreach ($result as $checkPhpKey => $phpKeyValue)
        {
            // Minimale Anforderungen prüfen
            $isOk = false;

            // Vergleichen, ob der Wert größer oder gleich dem Min-Wert ist
            if (isset($minValues[$checkPhpKey]))
            {
                $minValue = $minValues[$checkPhpKey];

                // Umwandeln der ini-Werte (z.B. 128M, 2G) in Integer
                $phpIniValueInt = convertToInt($phpKeyValue);

                // Prüfen, ob der Wert den Min-Wert erreicht oder überschreitet
                $isOk = $phpIniValueInt >= $minValue;
            }

            // Wenn ok, 'OK' als Status setzen
            $status = $isOk ? 'ok' : 'warning';

            // Padding auf gleiche Länge für Ausrichtung des Pfeils
            $label    = str_pad($checkPhpKey, $maxLen) . ' => ' . str_pad($phpKeyValue, $maxLenValue);
            $iconHtml = getStatusIcon($status);

            echo htmlspecialchars($label) . ' ' . $iconHtml . "\n";

        }

        echo '</pre></td>';
        echo '</tr>';
    }
}
function convertToInt($value)
{
    # Hilfsfunktion zur Umwandlung von ini-Werten wie 128M, 2G in Integer (Bytes)
    $value = trim($value);

    if (is_numeric($value)) {
        return (int)$value; // Wenn der Wert nur eine Zahl ist, zurückgeben
    }

    // Wenn der Wert mit M, G oder K endet, dann konvertieren wir in Integer (Bytes)
    $lastChar = strtoupper($value[strlen($value) - 1]);
    $numericValue = (int) rtrim($value, 'MKG');

    switch ($lastChar) {
        case 'M':
            return $numericValue * 1024 * 1024; // Megabyte -> Bytes
        case 'G':
            return $numericValue * 1024 * 1024 * 1024; // Gigabyte -> Bytes
        case 'K':
            return $numericValue * 1024; // Kilobyte -> Bytes
        default:
            return $numericValue; // Wenn keine Einheit, als Zahl zurückgeben
    }
}
function getWritableStatus(): array
{
    $basename          = pathinfo(getcwd(), PATHINFO_BASENAME);
    $result            = [];
    $maxLen            = 0;
    $relativePathArray = ($basename === 'menu') ? '../' : '.';
    $dirArray          = getWritableDirectories($relativePathArray);
    $dirExcludeArray   = array('.', '..', '.git', '.idea', 'test');

    // 1. Pfade prüfen und gleichzeitig maximale Länge des Verzeichnisnamens ermitteln
    foreach ($dirArray as $dir)
    {
        if ($dir === '.' || $dir === '..' || in_array($dir, $dirExcludeArray)) continue;

        $relativePath = ($basename === 'menu') ? '../' . $dir : $dir;
        $realPath     = realpath($relativePath);

        if ($realPath === false)
        {
            $result[$dir] = 'Verzeichnis ' . $dir . ' nicht gefunden';
        }
        else
        {
            $result[$dir] = is_writable($realPath) ? '<span data-i18n="submenu.debug_info.lbl.dir-status-writable">Beschreibbar</span> ' . html_entity_decode(getStatusIcon('ok')) : '<span data-i18n="submenu.debug_info.lbl.dir-status-readonly">Nur lesend</span> ' . html_entity_decode(getStatusIcon('warning'));
        }

        // Maximale Länge ermitteln
        $maxLen = max($maxLen, strlen($dir));
    }

    // 2. Ausgabe im monospace Block
    echo '<tr>';
    if (!empty($result))
    {
        echo '<td style="vertical-align: top;"><span data-i18n="submenu.debug_info.lbl.dir-writeable">Schreibstatus Directory</span>:</td>';
        echo '<td><pre style="margin:0; font-family: monospace;">';

        foreach ($result as $checkDir => $dirAccess)
        {
            // Padding auf gleiche Länge für Ausrichtung des Pfeils
            $label = str_pad($checkDir, $maxLen) . ' => ';
            echo htmlspecialchars($label) . $dirAccess . "\n";
        }

        echo '</pre></td>';
    }
    else
    {
        echo '<td>Schreibstatus Directory:</td>';
        echo '<td>Kein Eintrag</td>';
    }
    echo '</tr>';

    return $result;
}
function getWritableDirectories(string $basePath = '.'): array
{
    $allEntries = scandir($basePath);
    $dirs = [];

    foreach ($allEntries as $entry) {
        if ($entry === '.' || $entry === '..') continue;

        $fullPath = $basePath . DIRECTORY_SEPARATOR . $entry;

        if (is_dir($fullPath)) {
            $dirs[] = $entry;
        }
    }

    return $dirs;
}
function getSqliteDbSizes(): array
{
    $basename             = pathinfo(getcwd(), PATHINFO_BASENAME);
    $relativeDatabasePath = ($basename === 'menu') ? '../database' : 'database';
    $databaseFileArray    = getSqliteDatabases($relativeDatabasePath);
    $isWindows            = chkOsIsWindows();

    $maxLenName = 0;
    $maxLenSize = 0;
    $result     = [];

    foreach ($databaseFileArray as $file)
    {
        $realDatabasePath = realpath($relativeDatabasePath . '/' . $file);

        if ($realDatabasePath && file_exists($realDatabasePath))
        {
            $dbFileSize = is_readable($realDatabasePath) ? filesize($realDatabasePath) : -1; // Prevents if File is locked
            $sizeKB     = round($dbFileSize / 1024, 1);

            if ($isWindows === true)
            {
                $lastAccess = date('d.m.Y H:i:s', fileatime($realDatabasePath));
            }
            else
            {
                $lastAccess = date('d.m.Y H:i:s', filemtime($realDatabasePath));
            }

            // Wenn ok, 'OK' als Status setzen
            $status = $sizeKB > 0 ? 'ok' : 'warning';


            $result[$file] = [
                'size'  => "$sizeKB KB",
                'atime' => $lastAccess,
                'status' => $status,
            ];

            $maxLenName = max($maxLenName, strlen($file));
            $maxLenSize = max($maxLenSize, strlen($result[$file]['size']));
        }
        else
        {
            $result[$file] = [
                'size'  => 'nicht vorhanden oder leer',
                'atime' => '-',
                'status' => 'warning',
            ];

            $maxLenName = max($maxLenName, strlen($file));
            $maxLenSize = max($maxLenSize, strlen('nicht vorhanden oder leer'));
        }
    }

    // Ausgabe
    echo '<tr>';
    if (!empty($result))
    {
        echo '<td style="vertical-align: top;"><span data-i18n="submenu.debug_info.lbl.databases">Datenbanken</span>:</td>';
        echo '<td><pre style="margin:0; font-family: monospace;">';

        foreach ($result as $file => $data)
        {
            $label = str_pad($file, $maxLenName)
                . ' => '
                . str_pad($data['size'], $maxLenSize)
                . str_repeat(' ', 2);

            $iconHtml = getStatusIcon($data['status']);
            #$accessInfo = '   [Letzter Zugriff: ' . $data['atime'] . ']';
            $accessInfo = '   [<span data-i18n="submenu.debug_info.lbl.last-access">Letzter Zugriff</span>: ' . $data['atime'] . ']';


            #echo htmlspecialchars($label) . $iconHtml . htmlspecialchars($accessInfo) . "\n";
            echo htmlspecialchars($label) . $iconHtml . $accessInfo . "\n";
        }

        echo '</pre></td>';
    }
    else
    {
        echo '<td>Datenbanken:</td>';
        echo '<td>Keine gefunden</td>';
    }
    echo '</tr>';

    return $result;
}
function getSqliteDatabases(string $basePath = 'database'): array
{
    $allEntries = scandir($basePath);
    $databases = [];

    foreach ($allEntries as $entry)
    {
        if ($entry === '.' || $entry === '..')
        {
            continue;
        }

        $fullPath = $basePath . DIRECTORY_SEPARATOR . $entry;

        // Überprüfe, ob es eine Datei ist und ob sie mit .sqlite oder .db endet
        if (is_file($fullPath) && (substr($entry, -7) === '.sqlite' || substr($entry, -3) === '.db'))
        {
            $databases[] = $entry;
        }
    }

    return $databases;
}
function getSystemUptimeSeconds()
{
    if (strncasecmp(PHP_OS, 'WIN', 3) === 0)
    {
        // Windows: PowerShell-Abfrage nutzen
        $cmd    = 'powershell -Command "(get-date) - (gcim Win32_OperatingSystem).LastBootUpTime | % { $_.TotalSeconds }"';
        $uptime = trim(shell_exec($cmd));
        $uptime = str_replace(',', '.', $uptime);

        return is_numeric($uptime) ? (int) $uptime : false;
    }
    else
    {
        // Linux: aus /proc/uptime lesen
        $uptimeContent = @file_get_contents('/proc/uptime');
        if ($uptimeContent === false)
        {
            return false;
        }
        $parts = explode(' ', $uptimeContent);

        return isset($parts[0]) ? (int) floatval($parts[0]) : false;
    }
}
function getLoadAverage()
{
    if (strncasecmp(PHP_OS, 'WIN', 3) === 0)
    {
        // Windows: PowerShell-Befehl zur Ermittlung der CPU-Auslastung
        $cmd  = 'powershell -Command "(Get-WmiObject Win32_Processor).LoadPercentage"';
        $load = shell_exec($cmd);

        echo '<tr>';
        echo '<td><span data-i18n="submenu.debug_info.cpu-load">Rechnerauslastung</span>:</td>';
        echo '<td>';
        echo  $load . '%';
        echo '</td>';
        echo '</tr>';

        return $load . '%'; // Rückgabe der CPU-Auslastung in Prozent

    } else
    {
        // Unix-basierte Systeme: sys_getloadavg verwenden
        $load = sys_getloadavg();

        foreach ($load as $index => $loadValue)
        {
            // Setzen der Ampelfarbe basierend auf dem 1-Minuten-Durchschnitt
            if ($loadValue < 0.5)
            {
                $trafficLight = '&#x1F7E2;'; // 🟢 Grün: Niedrige Last
            }
            elseif ($loadValue >= 0.5 && $loadValue < 1.5)
            {
                $trafficLight = '&#x1F7E1;'; // 🟡 Gelb: Mittlere Last
            }
            else
            {
                $trafficLight = '&#x1F534;'; // 🔴 Rot: Hohe Last
            }

            if ($index === 0)
            {
                echo '<tr>';
                echo '<td>' . 'Rechnerauslastung 1 Minuten:' . '</td>';
                echo '<td>' . $trafficLight . ' ' . $loadValue . '</td>';
                echo '</tr>';
            }
            else if ($index === 1)
            {
                echo '<tr>';
                echo '<td>' . 'Rechnerauslastung 5 Minuten:' . '</td>';
                echo '<td>' . $trafficLight . ' ' . $loadValue . '</td>';
                echo '</tr>';
            }
            else if ($index === 2)
            {
                echo '<tr>';
                echo '<td>' . 'Rechnerauslastung 15 Minuten:' . '</td>';
                echo '<td>' . $trafficLight . ' ' . $loadValue . '</td>';
                echo '</tr>';
            }
        }

        return $load;
    }
}