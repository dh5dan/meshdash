<?php

function getSensorData($loraIp, int $mode = 0)
{
    $debugFlag = false;

    $url  = "http" . "://" . $loraIp . "/wx";
    $html = @file_get_contents($url);

    if ($html === false)
    {
        die('<span class="failureHint">Fehler beim Abrufen der WX-Seite!</span>');
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true); // Fehler unterdrücken (wegen ungültigem HTML)
    $doc->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);
    $rows  = $xpath->query("//table//tr");

    $wxArray = [];

    foreach ($rows as $row)
    {
        $cols = $row->getElementsByTagName("td");

        if ($cols->length == 2)
        {
            $key   = trim(strip_tags($cols->item(0)->textContent));
            $value = trim(strip_tags($cols->item(1)->textContent));

            // Falls der Wert eine Maßeinheit hat, direkt mit abspeichern
            $wxArray[$key] = $value;
        }
    }

    $arrayGetLoraInfo = getLoraInfo($loraIp);
    if (isset($arrayGetLoraInfo['INA226']))
    {
        $wxArray = array_merge($wxArray, $arrayGetLoraInfo['INA226']);
    }

    if ($debugFlag === true)
    {
        // Debug-Ausgabe
        echo "<pre>";
        print_r($wxArray);
        echo "</pre>";
    }

    if (count($wxArray) > 0)
    {
        setSensorData($wxArray);
    }

    if (count($wxArray) == 0)
    {
        echo '<h3>Keine Sensordaten gefunden.';
        echo '<br>Zeige zuletzt gespeicherte Werte, wenn vorhanden.</br></h3>';
        return false;
    }

    if ($mode === 1)
    {
        return $wxArray;
    }

    return true;
}

function showSensorData()
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/sensordata.db';
    $dbFilenameRoot = 'database/sensordata.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $deviceIsMobile = isMobile();


    $db = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(5000); // warte wenn busy in millisekunden

    // Hole mir die letzten 30 Nachrichten aus der Datenbank
    $result = $db->query("SELECT timestamps from sensordata
                               GROUP BY timestamps
                               ORDER BY timestamps DESC
                                  LIMIT 1;
                        ");

    $dsData = $result->fetchArray(SQLITE3_ASSOC);

    $validData = !empty($dsData);

    if ($validData)
    {
        $timeStamp = $dsData['timestamps'];

        $resultSensor = $db->query("SELECT * 
                                        FROM sensordata
                                       WHERE timestamps = '$timeStamp';
                        ");

        if ($resultSensor !== false)
        {
            echo "<br>";
            echo "<br>";

            echo '<table class="table">';

            echo '<tr>';
            echo '<th colspan="13" class="thCenter">Letzte gespeicherte Sensordaten vom ' . $timeStamp . '</th>';
            echo '</tr>';
            echo '<tr>';
            echo '<th colspan="13" ><hr></th>';
            echo '</tr>';

            while ($row = $resultSensor->fetchArray(SQLITE3_ASSOC))
            {
                ###############################################
                #Common
                $bme280         = $row['bme280'];
                $bme680         = $row['bme680'];
                $mcu811         = $row['mcu811'];
                $lsp33          = $row['lsp33'];
                $oneWire        = $row['oneWire'];
                $temp           = $row['temp'];
                $tout           = $row['tout'];
                $hum            = $row['hum'];
                $qfe            = $row['qfe'];
                $qnh            = $row['qnh'];
                $altAsl         = $row['altAsl'];
                $gas            = $row['gas'];
                $eCo2           = $row['eCo2'];
                $ina226vBus     = $row['ina226vBus'];
                $ina226vShunt   = $row['ina226vShunt'];
                $ina226vCurrent = $row['ina226vCurrent'];
                $ina226vPower   = $row['ina226vPower'];


                if ($deviceIsMobile === false)
                {
                    echo '<tr>';
                    echo '<th>BME(P)280</th>';
                    echo '<th>BME680</th>';
                    echo '<th>MCU811</th>';
                    echo '<th>LPS33</th>';
                    echo '<th>ONEWIRE</th>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<td>' . $bme280 . '</td>';
                    echo '<td>' . $bme680 . '</td>';
                    echo '<td>' . $mcu811 . '</td>';
                    echo '<td>' . $lsp33 . '</td>';
                    echo '<td>' . $oneWire . '</td>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<th>TEMP</th>';
                    echo '<th>TOUT</th>';
                    echo '<th>HUM</th>';
                    echo '<th>QFE</th>';
                    echo '<th>QNH</th>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<td>' . $temp . '</td>';
                    echo '<td>' . $tout . '</td>';
                    echo '<td>' . $hum . '</td>';
                    echo '<td>' . $qfe . '</td>';
                    echo '<td>' . $qnh . '</td>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<th>ALT asl</th>';
                    echo '<th>GAS</th>';
                    echo '<th>eCO2</th>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<td>' . $altAsl . '</td>';
                    echo '<td>' . $gas . '</td>';
                    echo '<td>' . $eCo2 . '</td>';
                    echo '</tr>';

                    if ($ina226vBus !== '')
                    {
                        echo '<tr>';
                        echo '<th>INA226<br>vBus</th>';
                        echo '<th>INA226<br>vShunt</th>';
                        echo '<th>INA226<br>vCurrent</th>';
                        echo '<th>INA226<br>vPower</th>';
                        echo '</tr>';

                        echo '<tr>';
                        echo '<td>' . $ina226vBus . '</td>';
                        echo '<td>' . $ina226vShunt . '</td>';
                        echo '<td>' . $ina226vCurrent . '</td>';
                        echo '<td>' . $ina226vPower . '</td>';
                        echo '</tr>';
                    }
                }
                else
                {
                    echo '<tr>';
                    echo '<th>BME(P)280</th>';
                    echo '<th>BME680</th>';
                    echo '<th>MCU811</th>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<td>' . $bme280 . '</td>';
                    echo '<td>' . $bme680 . '</td>';
                    echo '<td>' . $mcu811 . '</td>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<th>LPS33</th>';
                    echo '<th>ONEWIRE</th>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<td>' . $lsp33 . '</td>';
                    echo '<td>' . $oneWire . '</td>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<th>TEMP</th>';
                    echo '<th>TOUT</th>';
                    echo '<th>HUM</th>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<td>' . $temp . '</td>';
                    echo '<td>' . $tout . '</td>';
                    echo '<td>' . $hum . '</td>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<th>QFE</th>';
                    echo '<th>QNH</th>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<td>' . $qfe . '</td>';
                    echo '<td>' . $qnh . '</td>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<th>ALT asl</th>';
                    echo '<th>GAS</th>';
                    echo '<th>eCO2</th>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<td>' . $altAsl . '</td>';
                    echo '<td>' . $gas . '</td>';
                    echo '<td>' . $eCo2 . '</td>';
                    echo '</tr>';

                    if ($ina226vBus !== '')
                    {
                        echo '<tr>';
                        echo '<th>INA226<br>vBus</th>';
                        echo '<th>INA226<br>vShunt</th>';
                        echo '<th>INA226<br>vCurrent</th>';
                        echo '</tr>';

                        echo '<tr>';
                        echo '<td>' . $ina226vBus . '</td>';
                        echo '<td>' . $ina226vShunt . '</td>';
                        echo '<td>' . $ina226vCurrent . '</td>';
                        echo '</tr>';

                        echo '<tr>';
                        echo '<th>INA226<br>vPower</th>';
                        echo '</tr>';

                        echo '<tr>';
                        echo '<td>' . $ina226vPower . '</td>';
                        echo '</tr>';
                    }
                }
            }
            echo '<table>';

        }
    }
    else
    {
        echo "<h3>Keine gespeicherten Daten vorhanden.";
    }

    #Close and write Back WAL
    $db->close();
    unset($db);
}