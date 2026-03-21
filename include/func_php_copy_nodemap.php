<?php

/**
 * Erstellt ein Verzeichnis, wenn es nicht existiert
 */
function ensureDir($path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

/**
 * Kopiert ein Verzeichnis rekursiv
 */
function copyRecursive($src, $dst): bool
{
    if (!is_dir($src)) {
        return false;
    }

    ensureDir($dst);

    $items = scandir($src);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $srcPath = $src . '/' . $item;
        $dstPath = $dst . '/' . $item;

        if (is_dir($srcPath)) {
            copyRecursive($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }

    return true;
}

/**
 * Kopiert einzelne Dateien
 */
function copyFiles(array $files, $baseSrc, $baseDst): void
{
    foreach ($files as $file) {
        $srcPath = $baseSrc . '/' . $file;
        $dstPath = $baseDst . '/' . $file;

        if (!file_exists($srcPath)) {
            // optional: Logging
            continue;
        }

        ensureDir(dirname($dstPath));
        copy($srcPath, $dstPath);
    }
}

/**
 * Prüft ob Build notwendig ist
 */
function shouldBuild($versionFile, $currentVersion): bool
{
    if (!file_exists($versionFile)) {
        return true;
    }

    $storedVersion = trim(file_get_contents($versionFile));

    return $storedVersion !== $currentVersion;
}

/**
 * Speichert die aktuelle Version
 */
function writeVersion($versionFile, $currentVersion): void
{

    ensureDir(dirname($versionFile));
    file_put_contents($versionFile, $currentVersion);
}

function deleteDir($dir): void
{
    if (!is_dir($dir)) return;

    $items = scandir($dir);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $path = $dir . '/' . $item;

        if (is_dir($path)) {
            deleteDir($path);
        } else {
            unlink($path);
        }
    }

    rmdir($dir);
}

/**
 * Hauptfunktion
 */
function buildNodeMap($baseSrc, $baseDst, array $config): void
{

    // komplette Ordner
    foreach ($config['full'] as $dir) {
        $src = $baseSrc . '/' . $dir;
        $dst = $baseDst . '/' . $dir;

        copyRecursive($src, $dst);
    }

    // einzelne Dateien
    copyFiles($config['files'], $baseSrc, $baseDst);
}

function write_ini_file(): void
{
    $callSign                = trim(getParamData('callSign') ?? '');
    $resGetOwnPosition       = getOwnPosition($callSign); // Für Init OpenStreet View
    $openStreetTileServerUrl = trim(getParamData('openStreetTileServerUrl')) ?? 'tile.openstreetmap.org';
    $nodemapDaysPast         = getParamData('nodemapDaysPast') ?: 0; //Tage rückwirkend
    $dateFrom                = date('Y-m-d', strtotime("-{$nodemapDaysPast} days"));

    $longitude = $resGetOwnPosition['longitude'] == '' ? 51.5 : $resGetOwnPosition['longitude'];
    $latitude  = $resGetOwnPosition['latitude'] == '' ? 7.3 : $resGetOwnPosition['latitude'];

    # INI-Pfad ermitteln
    $basename     = pathinfo(getcwd())['basename'];
    $filePathSub  = '../export/nodemap/nodemap.ini';
    $filePathRoot = 'export/nodemap/nodemap.ini';
    $filePath     = $basename == 'menu' ? $filePathSub : $filePathRoot;

    $data = [
        'callSign' => $callSign,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'openStreetTileServerUrl' => $openStreetTileServerUrl,
        'dateFrom' => $dateFrom,
    ];

    $content = '';

    foreach ($data as $key => $value)
    {
        // einfache Skalare
        if (is_array($value))
        {
            continue; // hier ignorieren wir Arrays (kann man erweitern)
        }

        // Werte sauber escapen
        $value = str_replace('"', '\"', $value);

        $content .= $key . '="' . $value . '"' . PHP_EOL;
    }

    file_put_contents($filePath, $content);
}
