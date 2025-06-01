<?php

function saveKeywordSettings(): bool
{
    $keyword1Text           = $_REQUEST['keyword1Text'] ?? '';
    $keyword1Cmd            = $_REQUEST['keyword1Cmd'] ?? '';
    $keyword1Enabled        = $_REQUEST['keyword1Enabled'] ?? 0;
    $keyword1ReturnMsg      = $_REQUEST['keyword1ReturnMsg'] ?? '';
    $keyword1DmGrpId        = $_REQUEST['keyword1DmGrpId'] ?? '*';
    $keyword1DmGrpId        = $keyword1DmGrpId == '' ? '*' : $keyword1DmGrpId;
    setParamData('keyword1Text', trim($keyword1Text), 'txt');
    setParamData('keyword1Cmd', trim($keyword1Cmd), 'txt');
    setParamData('keyword1Enabled', $keyword1Enabled);
    setParamData('keyword1ReturnMsg', trim($keyword1ReturnMsg), 'txt');
    setParamData('keyword1DmGrpId', trim($keyword1DmGrpId), 'txt');

    $keyword2Text           = $_REQUEST['keyword2Text'] ?? '';
    $keyword2Cmd            = $_REQUEST['keyword2Cmd'] ?? '';
    $keyword2Enabled        = $_REQUEST['keyword2Enabled'] ?? 0;
    $keyword2ReturnMsg      = $_REQUEST['keyword2ReturnMsg'] ?? '';
    $keyword2DmGrpId        = $_REQUEST['keyword2DmGrpId'] ?? '*';
    $keyword2DmGrpId        = $keyword2DmGrpId == '' ? '*' : $keyword2DmGrpId;
    setParamData('keyword2Text', trim($keyword2Text), 'txt');
    setParamData('keyword2Cmd', trim($keyword2Cmd), 'txt');
    setParamData('keyword2Enabled', $keyword2Enabled);
    setParamData('keyword2ReturnMsg', trim($keyword2ReturnMsg), 'txt');
    setParamData('keyword2DmGrpId', trim($keyword2DmGrpId), 'txt');

    return true;
}

function showKeyScriptFiles()
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

    $maxSoundFiles   = 10;
    $maxFilesCount   = 0;

    $executeDir = dirname(__DIR__) . '/execute';

    if (!is_dir($executeDir))
    {
        echo "Execute-Verzeichnis nicht gefunden.";
        return;
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
        return;
    }

    // Neueste zuerst
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    // Basis-URL ermitteln
    $scriptDir    = dirname($_SERVER['SCRIPT_NAME']);
    $baseUrl      = dirname($scriptDir);
    $downloadBase = $baseUrl . '/execute/';

    echo '<div class="scrollable-container">';
    echo '<table class="backupTable">';
    echo '<tr>';
    echo '<th>Datum</th>';
    echo '<th>Uhrzeit</th>';
    echo '<th>Script-Datei</th>';
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
