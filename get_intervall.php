<?php

$returnArray = array();

// SQLite3-Verbindung herstellen
$dbTemp      = new SQLite3('database/sensor_th_temp.db', SQLITE3_OPEN_READONLY);

// SQL-Abfrage, um den Intervallwert zu holen
$queryTemp = "SELECT sensorThTempIntervallSec 
            FROM sensorThTemp 
           WHERE sensorThTempEnabled =1 
              OR sensorThToutEnabled = 1;
        ";

$resultTemp = $dbTemp->querySingle($queryTemp, true);

$dbTemp->close();

if ($resultTemp)
{
    $returnArray['temp'] = $resultTemp['sensorThTempIntervallSec'];
}
else
{
    $returnArray['temp'] = 0;
}

####INA22
// SQLite3-Verbindung herstellen
$dbIna226      = new SQLite3('database/sensor_th_ina226.db', SQLITE3_OPEN_READONLY);

// SQL-Abfrage, um den Intervallwert zu holen
$queryIna226 = "SELECT sensorThIna226IntervallSec
                FROM sensorThIna226
               WHERE sensorThIna226vBusEnabled =1
                  OR sensorThIna226vCurrentEnabled = 1
                  OR sensorThIna226vPowerEnabled = 1
                  OR sensorThIna226vShuntEnabled = 1;
        ";

$resultIna226 = $dbIna226->querySingle($queryIna226, true);

$dbIna226->close();

if ($resultIna226)
{
    $returnArray['ina226'] = $resultIna226['sensorThIna226IntervallSec'];
}
else
{
    $returnArray['ina226'] = 0;
}

echo json_encode($returnArray);


