<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

$debugFlag = false;

if ($debugFlag === true)
{
    echo "<br>lastChecked vor:" . $_GET['lastChecked'];
    $_GET['lastChecked'] = 1740291656; // '2025-02-23 07:20:56'
    echo "<br>lastChecked nach:" . $_GET['lastChecked'];
}

#Wenn Datei nicht existiert, dann exit.
#Verhindert das ein offener Browser eine 0 byte Datenbank-Datei erzeugt
if (!file_exists('database/parameter.db') || !file_exists('database/groups.db') || !file_exists('database/meshdash.db'))
{
    exit();
}

#Trigger LogRotate
logRotate();

// Verbindung zur ersten DB (groups)
$db1 = new SQLite3('database/groups.db', SQLITE3_OPEN_READONLY);
$db1->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
$callSign = SQLite3::escapeString(getParamData('callSign'));

$sql1 = "SELECT groupNumber 
           FROM groups
          WHERE groupNumber != 0
            AND groupEnabled = 1;
        ";

$logArray   = array();
$logArray[] = "checkMessages_sql1: Database: database/groups.db";
$logArray[] = "checkMessages_sql1: SQLITE3_BUSY_TIMEOUT:" . SQLITE3_BUSY_TIMEOUT;

$result1 = safeDbRun($db1, $sql1, 'query', $logArray);

if ($result1 === false)
{
    #Close and write Back WAL
    $db1->close();
    unset($db1);
    exit();
}

$groups = [];

while ($row = $result1->fetchArray(SQLITE3_ASSOC))
{
    $groups[] = "'" . SQLite3::escapeString($row['groupNumber']) . "'";
}

#Close and write Back WAL
$db1->close();
unset($db1);

$groupsSql  = implode(',', $groups);

#Wenn noch keine Gruppe definiert wurde
if ($groupsSql != '')
{
    $groupsSql .= ",'*','$callSign'";
}
else
{
    $groupsSql .= "'*','$callSign'";
}

if ($debugFlag === true)
{
    echo "<br>groupsSql:$groupsSql";
    echo "<br>#38#defined grp#<br><pre>";
    print_r($groups);
    echo "</pre>";
}

// Verbindung zur zweiten DB (meshdash)
$db2 = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
$db2->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

// Zeitstempel des letzten Checks abrufen
$lastChecked          = isset($_GET['lastChecked']) ? intval($_GET['lastChecked']) : 0;
$lastCheckedFormatted = $lastChecked != '' ? date('Y-m-d H:i:s', $lastChecked) : '2000-01-01 00:00:00';

$sql2 = "SELECT DISTINCT dst
           FROM meshdash 
           WHERE timestamps > '$lastCheckedFormatted'
             AND dst in ($groupsSql)
             AND type = 'msg'
             AND msg NOT LIKE '%{CET}%';
        ";

$logArray   = array();
$logArray[] = "checkMessages_sql2: Database: database/groups.db";
$logArray[] = "checkMessages_sql2: SQLITE3_BUSY_TIMEOUT:" . SQLITE3_BUSY_TIMEOUT;

$result2 = safeDbRun($db2, $sql2, 'query', $logArray);

if ($result2 === false)
{
    #Close and write Back WAL
    $db2->close();
    unset($db2);
    exit();
}

if ($debugFlag === true)
{
    echo "<br>" . $sql2;
}

$mergedData = [];

# Wenn DB leer, wird False zurückgeben.
# Verhindert Server Error 500
if ($result2 !== false)
{
    while ($row = $result2->fetchArray(SQLITE3_ASSOC))
    {
        $groupId      = $row['dst'];
        $groupId      = $groupId == '*' ? -1 : $groupId;
        $groupId      = $groupId == $callSign ? -2 : $groupId;
        $mergedData[] = (int) $groupId;
    }
}

#Close and write Back WAL
$db2->close();
unset($db2);

if ($debugFlag === true)
{
    echo "<br>lastCheckedFormatted:$lastCheckedFormatted";
    echo "<br>#74#mergedData#<br><pre>";
    print_r($mergedData);
    echo "</pre>";
}

if ($debugFlag === false)
{
    // JSON-Ausgabe für das Frontend
    header('Content-Type: application/json');
}

echo json_encode([
    'newMessages' => $mergedData // Gruppen wo neue daten gefunden wurden
]);

