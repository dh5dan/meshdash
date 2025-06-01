<?php
function saveAlertingSettings(): bool
{
    $alertSoundFileSrc = $_REQUEST['alertSoundFileSrc'] ?? '';
    $alertEnabledSrc   = $_REQUEST['alertEnabledSrc'] ?? 0;
    $alertSoundCallSrc = $_REQUEST['alertSoundCallSrc'] ?? '';

    $alertSoundFileDst = $_REQUEST['alertSoundFileDst'] ?? '';
    $alertEnabledDst   = $_REQUEST['alertEnabledDst'] ?? 0;
    $alertSoundCallDst = $_REQUEST['alertSoundCallDst'] ?? '';

    setParamData('alertSoundFileSrc', $alertSoundFileSrc, 'txt');
    setParamData('alertEnabledSrc', $alertEnabledSrc);
    setParamData('alertSoundCallSrc', strtoupper($alertSoundCallSrc), 'txt');

    setParamData('alertSoundFileDst', $alertSoundFileDst, 'txt');
    setParamData('alertEnabledDst', $alertEnabledDst);
    setParamData('alertSoundCallDst', strtoupper($alertSoundCallDst), 'txt');

    return true;
}
function showAlertMediaFiles()
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

    $maxSoundFiles   = 10;
    $maxFilesCount   = 0;

    $soundDir = dirname(__DIR__) . '/sound';

    if (!is_dir($soundDir))
    {
        echo "Sound-Verzeichnis nicht gefunden.";
        return;
    }

    // WAV & MP3 kombinieren
    $wavFiles = glob($soundDir . '/*.wav');
    $mp3Files = glob($soundDir . '/*.mp3');
    $files    = array_merge($wavFiles ?: [], $mp3Files ?: []);

    if (empty($files))
    {
        echo "Keine Sound-Files vorhanden.";
        return;
    }

    // Neueste zuerst
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    // Basis-URL ermitteln
    $scriptDir    = dirname($_SERVER['SCRIPT_NAME']);
    $baseUrl      = dirname($scriptDir);
    $downloadBase = $baseUrl . '/sound/';

    echo '<div class="scrollable-container">';
    echo '<table class="backupTable">';
    echo '<tr>';
    echo '<th>Datum</th>';
    echo '<th>Uhrzeit</th>';
    echo '<th>Media-Datei</th>';
    echo '<th colspan="2">&nbsp;</th>';
    echo '</tr>';

    foreach ($files as $file)
    {
        ++$maxFilesCount;
        if ($maxFilesCount > $maxSoundFiles)
        {
            break; // nur die neuesten X anzeigen
        }

        $filename    = basename($file);
        $fileTime    = filemtime($file);
        $datum       = date('d.m.Y', $fileTime);
        $uhrzeit     = date('H:i:s', $fileTime);
        $downloadUrl = $downloadBase . rawurlencode($filename);

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
        echo '<img src="../image/delete_blk.png" data-delete ="' . htmlspecialchars($filename) . '" class="imageDelete" alt="delete">';
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';
    echo '</div>';

    echo "</form>";
}
