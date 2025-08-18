<?php
function loadLanguageDictionary($langCode = 'de')
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/translation.db';
    $dbFilenameRoot = 'database/translation.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    if (!file_exists($dbFilename))
    {
        return false;
    }

    $db     = new SQLite3($dbFilename);
    $stmt   = $db->prepare("SELECT key, $langCode AS text FROM translation");
    $result = $stmt->execute();

    $dictionary = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC))
    {
        $dictionary[$row['key']] = $row['text'];
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    header('Content-Type: application/json');
    echo json_encode($dictionary);
}

$_GET['lang'] = $_GET['lang'] ?? 'de';

loadLanguageDictionary($_GET['lang']);
