<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

// get_data.php
header('Content-Type: application/json');

#Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
$basename       = pathinfo(getcwd())['basename'];
$dbFilenameSub  = '../database/sensordata.db';
$dbFilenameRoot = 'database/sensordata.db';
$dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

$db  = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
$db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

// Parameter
$sensor = $_GET['sensor'] ?? 'temp';
$from   = $_GET['from']   ?? null;
$to     = $_GET['to']     ?? null;

// Whitelist für erlaubte Sensor-Spalten
$allowedSensors = [
    'bme280','bme680','mcu811','lsp33','oneWire',
    'temp','tout','hum','qfe','qnh','altAsl',
    'gas','eCo2',
    'ina226vBus','ina226vShunt','ina226vCurrent','ina226vPower'
];

if (!in_array($sensor, $allowedSensors)) {
    echo json_encode(["error" => "Invalid sensor"]);
    exit;
}

// Basis-SQL
$sql = "SELECT timestamps, $sensor FROM sensordata WHERE $sensor IS NOT NULL";

// Filter Zeitraum
$params = [];
if ($from) {
    $sql .= " AND timestamps >= :from";
    $params[':from'] = $from;
}
if ($to) {
    $sql .= " AND timestamps <= :to";
    $params[':to'] = $to;
}

$sql .= " ORDER BY timestamps ASC";

$stmt = $db->prepare($sql);

foreach ($params as $key => $val)
{
    $stmt->bindValue($key, $val, SQLITE3_TEXT);
}

$result = $stmt->execute();
$data   = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $data[] = [
        'timestamp' => $row['timestamps'],
        'value'     => is_numeric($row[$sensor]) ? (float)$row[$sensor] : intval($row[$sensor]),
    ];
}

echo json_encode($data);
