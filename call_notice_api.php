<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

$callSign = $_POST['callSign'] ?? '';
$action   = $_POST['action'] ?? '';

header('Content-Type: application/json');

if (!$callSign) {
    echo json_encode(['error'=>'Kein CallSign übergeben']);
    exit;
}

if ($action === 'get') {
    $db = new SQLite3('database/call_notice.db', SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden

    $stmt = $db->prepare("SELECT callNotice FROM callNotice WHERE callSign = :cs");
    $stmt->bindValue(':cs', $callSign, SQLITE3_TEXT);
    $res = $stmt->execute();
    $row = $res->fetchArray(SQLITE3_ASSOC);

    #Close and write Back WAL
    $db->close();
    unset($db);

    echo json_encode(['callNotice'=> $row['callNotice'] ?? '']);
    exit;
}

if ($action === 'set') {
    #Open Database
    $db = new SQLite3('database/call_notice.db');
    $db->exec('PRAGMA journal_mode = wal;');
    $db->exec('PRAGMA synchronous = NORMAL;');
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden

    $lastHeard = '0000-00-00 00:00:00';
    $timestamps = DATE('Y-m-d H:i:s');

    $callNotice = $_POST['callNotice'] ?? '';
    $stmt = $db->prepare("INSERT INTO callNotice(callSign,callNotice,lastHeard,timestamps) VALUES(:cs,:callNotice,:lastHeard,:timestamps)
        ON CONFLICT(callSign) DO UPDATE SET callNotice = :callNotice,lastHeard = :lastHeard,timestamps = :timestamps");
    $stmt->bindValue(':cs', $callSign, SQLITE3_TEXT);
    $stmt->bindValue(':callNotice', $callNotice, SQLITE3_TEXT);
    $stmt->bindValue(':lastHeard', $lastHeard, SQLITE3_TEXT);
    $stmt->bindValue(':timestamps', $timestamps, SQLITE3_TEXT);
    $stmt->execute();

    #Close and write Back WAL
    $db->close();
    unset($db);

    echo json_encode(['success'=>true]);
    exit;
}
echo json_encode(['error'=>'Ungültige Aktion']);
