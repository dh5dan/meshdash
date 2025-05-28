<?php

function getSensorData($loraIp, int $mode = 0)
{

    $url       = "http" . "://" . $loraIp . "/wx";
    $wxArray   = array();
    $debugFlag = false;

    $html = @file_get_contents($url);

    if ($html === false)
    {
        echo '<span class="failureHint">' . date('H:i:s') . '-Fehler beim Abrufen der WX-Seite.</span>';

        return false;
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true); // Fehler unterdrücken (wegen ungültigem HTML)
    $doc->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);
    $rows  = $xpath->query("//table//tr");

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
        echo '<span class="failureHint">Keine Sensordaten gefunden.';
        echo '<br>Zeige zuletzt gespeicherte Werte, wenn vorhanden.</br></span>';
        return false;
    }

    if ($mode === 1)
    {
        return $wxArray;
    }

    return true;
}

function getSensorData2($loraIp, int $mode = 0)
{
    $url       = 'http://' . $loraIp . '/?page=wx';
    $wxArray   = array();
    $html      = @file_get_contents($url);

    if ($html === false)
    {
        echo '<span class="failureHint">' . date('H:i:s') . '-Fehler beim Abrufen der WX-Seite.</span>';

        return false;
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);
    $rows  = $xpath->query('//table[@class="table"]//tr');

    foreach ($rows as $row) {
        $tds = $row->getElementsByTagName('td');

        if ($tds->length === 2)
        {
            $key   = trim($tds->item(0)->nodeValue);
            $value = trim($tds->item(1)->nodeValue);

            // Normalize key
            $key = strtolower($key);
            $key = str_replace([' ', '-', ':', '(', ')'], '_', $key);

            if ($key === 'item')
            {
                continue;
            }

            switch ($key)
            {
                case 'BME(P)280':
                case 'BME680':
                case 'MCU811':
                case 'LPS33':
                case '1-Wire':
                case 'Temperature':
                case 'Tout':
                case 'Humidity':
                case 'QFE':
                case 'QNH':
                case 'Altitude asl':
                case 'Gas':
                case 'eCO2':
                $wxArray[$key] = $value;
                    break;

                default:
                    // Fallback: speichern, wenn noch nicht gesetzt
                    if (!isset($wxArray[$key]))
                    {
                        $wxArray[$key] = $value;
                    }
                    break;
            }
        }
    }

    if (count($wxArray) > 0)
    {
        setSensorData2($wxArray);
    }

    if (count($wxArray) == 0)
    {
        echo '<span class="failureHint">Keine Sensordaten gefunden.';
        echo '<br>Zeige zuletzt gespeicherte Werte, wenn vorhanden.</br></span>';
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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    // Hole mir die letzten 30 Nachrichten aus der Datenbank
    $result = $db->query("SELECT * FROM sensordata
                               ORDER BY sensorDataId DESC
                                  LIMIT 1;
                        ");

    $dsData = $result->fetchArray(SQLITE3_ASSOC);

    $validData = !empty($dsData);

    if ($validData)
    {
        $timeStamp = $dsData['timestamps'];

        echo "<br>";
        echo "<br>";

        echo '<table class="table">';

        echo '<tr>';
        echo '<th colspan="13" class="thCenter">Letzte gespeicherte Sensordaten vom ' . $timeStamp . '</th>';
        echo '</tr>';
        echo '<tr>';
        echo '<th colspan="13" ><hr></th>';
        echo '</tr>';

        ###############################################
        #Common
        $bme280         = $dsData['bme280'];
        $bme680         = $dsData['bme680'];
        $mcu811         = $dsData['mcu811'];
        $lsp33          = $dsData['lsp33'];
        $oneWire        = $dsData['oneWire'];
        $temp           = $dsData['temp'];
        $tout           = $dsData['tout'];
        $hum            = $dsData['hum'];
        $qfe            = $dsData['qfe'];
        $qnh            = $dsData['qnh'];
        $altAsl         = $dsData['altAsl'];
        $gas            = $dsData['gas'];
        $eCo2           = $dsData['eCo2'];
        $ina226vBus     = $dsData['ina226vBus'];
        $ina226vShunt   = $dsData['ina226vShunt'];
        $ina226vCurrent = $dsData['ina226vCurrent'];
        $ina226vPower   = $dsData['ina226vPower'];

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

        echo '<table>';
    }
    else
    {
        echo "<h3>Keine gespeicherten Daten vorhanden.";
    }

    #Close and write Back WAL
    $db->close();
    unset($db);
}

function checkSensor($resGetSensorData)
{
    #Check what oS is running
    $osIssWindows = chkOsIsWindows();
    $debugFlag    = true;

    #Setze AlertCount zurück
    checkSensorAlertCount();

    #Prüfe ob INA226 vorhanden ist
    $hasIna226             = isset($resGetSensorData['vBUS']);

    #Prüfe, ob irgendein Sensor aus den Gruppen aktiv ist
    $anyTemSensorAktive    = false;
    $anyIna226SensorActive = false;

    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/sensordata.db';
    $dbFilenameRoot = 'database/sensordata.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $mxSendAlertMsg = 2;//Max. 2 Nachrichten in einer Stunde senden

    $db = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    // Hole mir die letzten 30 Nachrichten aus der Datenbank
    $result = $db->query("SELECT * 
                                  FROM sensordata 
                                  WHERE sensorDataId = (SELECT MAX(sensorDataId) 
                                                          FROM sensordata);
                        ");

    $dsData = $result->fetchArray(SQLITE3_ASSOC);

    $validData = !empty($dsData);

    if ($validData === true)
    {
        if ($debugFlag === true)
        {
            echo "<br>SensorData<br><pre>";
            print_r($dsData);
            echo "</pre>";
        }

        $tempValue = preg_replace('/[^0-9.]/', '', $dsData['temp']);
        $toutValue = preg_replace('/[^0-9.]/', '', $dsData['tout']);

        $ina226vBus     = preg_replace('/[^0-9.]/', '',$dsData['ina226vBus']);
        $ina226vShunt   = preg_replace('/[^0-9.]/', '',$dsData['ina226vShunt']);
        $ina226vCurrent = preg_replace('/[^0-9.]/', '',$dsData['ina226vCurrent']);
        $ina226vPower   = preg_replace('/[^0-9.]/', '',$dsData['ina226vPower']);

        #########################################
        ######### Check INA226
        #########################################
        #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
        $basename       = pathinfo(getcwd())['basename'];
        $dbFilenameSub  = '../database/sensor_th_ina226.db';
        $dbFilenameRoot = 'database/sensor_th_ina226.db';
        $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

        $dbIna226 = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
        $dbIna226->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

        $resultThSensor = $dbIna226->query("SELECT * FROM sensorThIna226;");
        $dsDataTh       = $resultThSensor->fetchArray(SQLITE3_ASSOC);

        if ($debugFlag === true)
        {
            echo "<br>INA226-Data<br><pre>";
            print_r($dsDataTh);
            echo "</pre>";
        }

        if (!empty($dsDataTh))
        {
            $resGetThIna226Data = getThIna226Data();

            if ($dsDataTh["sensorThIna226vBusEnabled"] == 1)
            {
                $sensorThIna226vBusMinValue = $dsDataTh["sensorThIna226vBusMinValue"];
                $sensorThIna226vBusMaxValue = $dsDataTh["sensorThIna226vBusMaxValue"];
                $sensorThIna226vBusAlertMsg = $dsDataTh["sensorThIna226vBusAlertMsg"];
                $sensorThIna226vBusDmGrpId  = $dsDataTh["sensorThIna226vBusDmGrpId"];
                $anyIna226SensorActive      = true;

                if ($ina226vBus < $sensorThIna226vBusMinValue && $sensorThIna226vBusMinValue != '')
                {
                    if ($resGetThIna226Data['sensorThIna226vBusAlertCount'] < $mxSendAlertMsg)
                    {
                        setSensorAlertCounter('ina226','vBus');
                        $txQueueData['txDst'] = $sensorThIna226vBusDmGrpId;
                        $txQueueData['txMsg'] = 'Ina226[vBus]: (' . $ina226vBus . ' < ' . $sensorThIna226vBusMinValue .') ' . $sensorThIna226vBusAlertMsg;
                        setTxQueue($txQueueData);
                    }

                    if ($debugFlag === true)
                    {
                        echo "<br>" . 'Ina226[vBus]: (' . $ina226vBus . ' < ' . $sensorThIna226vBusMinValue .') ' . $sensorThIna226vBusAlertMsg;
                        echo "<br>vBus Count:" . $resGetThIna226Data['sensorThIna226vBusAlertCount'];
                    }
                }

                if ($ina226vBus > $sensorThIna226vBusMaxValue && $sensorThIna226vBusMaxValue != '')
                {
                    if ($resGetThIna226Data['sensorThIna226vBusAlertCount'] < $mxSendAlertMsg)
                    {
                        setSensorAlertCounter('ina226','vBus');
                        $txQueueData['txDst'] = $sensorThIna226vBusDmGrpId;
                        $txQueueData['txMsg'] = 'Ina226[vBus]: (' . $ina226vBus . ' > ' . $sensorThIna226vBusMaxValue .') ' . $sensorThIna226vBusAlertMsg;
                        setTxQueue($txQueueData);
                    }

                    if ($debugFlag === true)
                    {
                        echo "<br>" . 'Ina226[vBus]: (' . $ina226vBus . ' > ' . $sensorThIna226vBusMaxValue .') ' . $sensorThIna226vBusAlertMsg;
                        echo "<br>vBus Count:" . $resGetThIna226Data['sensorThIna226vBusAlertCount'];
                    }
                }
            }

            if ($dsDataTh["sensorThIna226vShuntEnabled"] == 1)
            {
                $sensorThIna226vShuntMinValue = $dsDataTh["sensorThIna226vShuntMinValue"];
                $sensorThIna226vShuntMaxValue = $dsDataTh["sensorThIna226vShuntMaxValue"];
                $sensorThIna226vShuntAlertMsg = $dsDataTh["sensorThIna226vShuntAlertMsg"];
                $sensorThIna226vShuntDmGrpId  = $dsDataTh["sensorThIna226vShuntDmGrpId"];
                $anyIna226SensorActive        = true;

                if ($ina226vShunt < $sensorThIna226vShuntMinValue && $sensorThIna226vShuntMinValue != '')
                {
                    if ($resGetThIna226Data['sensorThIna226vShuntAlertCount'] < $mxSendAlertMsg)
                    {
                        setSensorAlertCounter('ina226','vShunt');
                        $txQueueData['txDst'] = $sensorThIna226vShuntDmGrpId;
                        $txQueueData['txMsg'] = 'Ina226[vShunt]: (' . $ina226vShunt . ' < ' . $sensorThIna226vShuntMinValue .') ' . $sensorThIna226vShuntAlertMsg;
                        setTxQueue($txQueueData);
                    }

                    if ($debugFlag === true)
                    {
                        echo "<br>" . 'Ina226[vShunt]: (' . $ina226vShunt . ' < ' . $sensorThIna226vShuntMinValue .') ' . $sensorThIna226vShuntAlertMsg;
                        echo "<br>vShunt Count:" . $resGetThIna226Data['sensorThIna226vShuntAlertCount'];
                    }
                }

                if ($ina226vShunt > $sensorThIna226vShuntMaxValue && $sensorThIna226vShuntMaxValue != '')
                {
                    if ($resGetThIna226Data['sensorThIna226vShuntAlertCount'] < $mxSendAlertMsg)
                    {
                        setSensorAlertCounter('ina226','vShunt');
                        $txQueueData['txDst'] = $sensorThIna226vShuntDmGrpId;
                        $txQueueData['txMsg'] = 'Ina226[vShunt]: (' . $ina226vShunt . ' > ' . $sensorThIna226vShuntMaxValue .') ' . $sensorThIna226vShuntAlertMsg;
                        setTxQueue($txQueueData);
                    }

                    if ($debugFlag === true)
                    {
                        echo "<br>" . 'Ina226[vShunt]: (' . $ina226vShunt . ' > ' . $sensorThIna226vShuntMaxValue .') ' . $sensorThIna226vShuntAlertMsg;
                        echo "<br>vShunt Count:" . $resGetThIna226Data['sensorThIna226vShuntAlertCount'];
                    }
                }
            }

            if ($dsDataTh["sensorThIna226vCurrentEnabled"] == 1)
            {
                $sensorThIna226vCurrentMinValue = $dsDataTh["sensorThIna226vCurrentMinValue"];
                $sensorThIna226vCurrentMaxValue = $dsDataTh["sensorThIna226vCurrentMaxValue"];
                $sensorThIna226vCurrentAlertMsg = $dsDataTh["sensorThIna226vCurrentAlertMsg"];
                $sensorThIna226vCurrentDmGrpId  = $dsDataTh["sensorThIna226vCurrentDmGrpId"];
                $anyIna226SensorActive          = true;

                if ($ina226vCurrent < $sensorThIna226vCurrentMinValue && $sensorThIna226vCurrentMinValue != '')
                {
                    if ($resGetThIna226Data['sensorThIna226vCurrentAlertCount'] < $mxSendAlertMsg)
                    {
                        setSensorAlertCounter('ina226','vCurrent');
                        $txQueueData['txDst'] = $sensorThIna226vCurrentDmGrpId;
                        $txQueueData['txMsg'] = 'Ina226[vCurrent]: (' . $ina226vCurrent . ' < ' . $sensorThIna226vCurrentMinValue .') ' . $sensorThIna226vCurrentAlertMsg;
                        setTxQueue($txQueueData);
                    }

                    if ($debugFlag === true)
                    {
                        echo "<br>" . 'Ina226[vCurrent]: (' . $ina226vCurrent . ' < ' . $sensorThIna226vCurrentMinValue .') ' . $sensorThIna226vCurrentAlertMsg;
                        echo "<br>vCurrent Count:" . $resGetThIna226Data['sensorThIna226vCurrentAlertCount'];
                    }
                }

                if ($ina226vCurrent > $sensorThIna226vCurrentMaxValue && $sensorThIna226vCurrentMaxValue != '')
                {
                    if ($resGetThIna226Data['sensorThIna226vCurrentAlertCount'] < $mxSendAlertMsg)
                    {
                        setSensorAlertCounter('ina226','vCurrent');
                        $txQueueData['txDst'] = $sensorThIna226vCurrentDmGrpId;
                        $txQueueData['txMsg'] = 'Ina226[vCurrent]: (' . $ina226vCurrent . ' > ' . $sensorThIna226vCurrentMaxValue .') ' . $sensorThIna226vCurrentAlertMsg;
                        setTxQueue($txQueueData);
                    }

                    if ($debugFlag === true)
                    {
                        echo "<br>" . 'Ina226[vCurrent]: (' . $ina226vCurrent . ' > ' . $sensorThIna226vCurrentMaxValue .') ' . $sensorThIna226vCurrentAlertMsg;
                        echo "<br>vCurrent Count:" . $resGetThIna226Data['sensorThIna226vCurrentAlertCount'];
                    }
                }
            }

            if ($dsDataTh["sensorThIna226vPowerEnabled"] == 1)
            {
                $sensorThIna226vPowerMinValue = $dsDataTh["sensorThIna226vPowerMinValue"];
                $sensorThIna226vPowerMaxValue = $dsDataTh["sensorThIna226vPowerMaxValue"];
                $sensorThIna226vPowerAlertMsg = $dsDataTh["sensorThIna226vPowerAlertMsg"];
                $sensorThIna226vPowerDmGrpId  = $dsDataTh["sensorThIna226vPowerDmGrpId"];
                $anyIna226SensorActive        = true;

                if ($ina226vPower < $sensorThIna226vPowerMinValue && $sensorThIna226vPowerMinValue != '')
                {
                    if ($resGetThIna226Data['sensorThIna226vPowerAlertCount'] < $mxSendAlertMsg)
                    {
                        setSensorAlertCounter('ina226','vPower');
                        $txQueueData['txDst'] = $sensorThIna226vPowerDmGrpId;
                        $txQueueData['txMsg'] = 'Ina226[vPower]: (' . $ina226vPower . ' < ' . $sensorThIna226vPowerMinValue .') ' . $sensorThIna226vPowerAlertMsg;
                        setTxQueue($txQueueData);
                    }

                    if ($debugFlag === true)
                    {
                        echo "<br>" . 'Ina226[vPower]: (' . $ina226vPower . ' < ' . $sensorThIna226vPowerMinValue .') ' . $sensorThIna226vPowerAlertMsg;
                        echo "<br>vPower Count:" . $resGetThIna226Data['sensorThIna226vPowerAlertCount'];
                    }
                }

                if ($ina226vPower > $sensorThIna226vPowerMaxValue && $sensorThIna226vPowerMaxValue != '')
                {
                    if ($resGetThIna226Data['sensorThIna226vPowerAlertCount'] < $mxSendAlertMsg)
                    {
                        setSensorAlertCounter('ina226','vPower');
                        $txQueueData['txDst'] = $sensorThIna226vPowerDmGrpId;
                        $txQueueData['txMsg'] = 'Ina226[vPower]: (' . $ina226vPower . ' > ' . $sensorThIna226vPowerMaxValue .') ' . $sensorThIna226vPowerAlertMsg;
                        setTxQueue($txQueueData);
                    }

                    if ($debugFlag === true)
                    {
                        echo "<br>" . 'Ina226[vPower]: (' . $ina226vPower . ' > ' . $sensorThIna226vPowerMaxValue .') ' . $sensorThIna226vPowerAlertMsg;
                        echo "<br>vPower Count:" . $resGetThIna226Data['sensorThIna226vPowerAlertCount'];
                    }
                }
            }
        }

        #########################################
        ######### Check Temp
        #########################################
        #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
        $basenameTemp       = pathinfo(getcwd())['basename'];
        $dbFilenameSubTemp  = '../database/sensor_th_temp.db';
        $dbFilenameRootTemp = 'database/sensor_th_temp.db';
        $dbFilenameTemp     = $basenameTemp == 'menu' ? $dbFilenameSubTemp : $dbFilenameRootTemp;

        $dbTemp = new SQLite3($dbFilenameTemp, SQLITE3_OPEN_READONLY);
        $dbTemp->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

        $resultThTempSensor = $dbTemp->query("SELECT * FROM sensorThTemp;");
        $dsDataThTemp       = $resultThTempSensor->fetchArray(SQLITE3_ASSOC);

        if ($debugFlag === true)
        {
            echo "<br>#############################################";
            echo "<br>tempValue:$tempValue";
            echo "<br>toutValue:$toutValue";

            echo "<br>Temp/Tout<br><pre>";
            print_r($dsDataThTemp);
            echo "</pre>";
        }

        if (!empty($dsDataThTemp))
        {
            $resGetThTempData = getThTempData();

            if ($dsDataThTemp["sensorThTempEnabled"] == 1)
            {
                $sensorThTempMinValue = $dsDataThTemp["sensorThTempMinValue"];
                $sensorThTempMaxValue = $dsDataThTemp["sensorThTempMaxValue"];
                $sensorThTempAlertMsg = $dsDataThTemp["sensorThTempAlertMsg"];
                $sensorThTempDmGrpId  = $dsDataThTemp["sensorThTempDmGrpId"];
                $anyTemSensorAktive   = true;

                if ($tempValue < $sensorThTempMinValue && $sensorThTempMinValue != '')
                {
                    if ($resGetThTempData['sensorThTempAlertCount'] < $mxSendAlertMsg)
                    {
                        setSensorAlertCounter('temp','Temp');
                        $txQueueData['txDst'] = $sensorThTempDmGrpId;
                        $txQueueData['txMsg'] = '[Temp]: (' . $tempValue . ' < ' . $sensorThTempMinValue .') ' . $sensorThTempAlertMsg;
                        setTxQueue($txQueueData);
                    }

                    if ($debugFlag === true)
                    {
                        echo "<br>" . '[Temp]: (' . $tempValue . ' < ' . $sensorThTempMinValue .') ' . $sensorThTempAlertMsg;
                        echo "<br>Temp Count:" . $resGetThTempData['sensorThTempAlertCount'];
                    }
                }

                if ($tempValue > $sensorThTempMaxValue && $sensorThTempMaxValue != '')
                {
                    if ($resGetThTempData['sensorThTempAlertCount'] < $mxSendAlertMsg)
                    {
                        setSensorAlertCounter('temp','Temp');
                        $txQueueData['txDst'] = $sensorThTempDmGrpId;
                        $txQueueData['txMsg'] = '[Temp]: (' . $tempValue . ' > ' . $sensorThTempMaxValue .') ' . $sensorThTempAlertMsg;
                        setTxQueue($txQueueData);
                    }

                    if ($debugFlag === true)
                    {
                        echo "<br>" . '[Temp]: (' . $tempValue . ' > ' . $sensorThTempMaxValue .') ' . $sensorThTempAlertMsg;
                        echo "<br>Temp Count:" . $resGetThTempData['sensorThTempAlertCount'];
                    }
                }
            }

            if ($dsDataThTemp["sensorThToutEnabled"] == 1)
            {
                $sensorThToutMinValue = $dsDataThTemp["sensorThToutMinValue"];
                $sensorThToutMaxValue = $dsDataThTemp["sensorThToutMaxValue"];
                $sensorThToutAlertMsg = $dsDataThTemp["sensorThToutAlertMsg"];
                $sensorThToutDmGrpId  = $dsDataThTemp["sensorThToutDmGrpId"];
                $anyTemSensorAktive   = true;

                if ($toutValue < $sensorThToutMinValue && $sensorThToutMinValue != '')
                {
                    if ($resGetThTempData['sensorThToutAlertCount'] < $mxSendAlertMsg)
                    {
                        setSensorAlertCounter('temp','Tout');
                        $txQueueData['txDst'] = $sensorThToutDmGrpId;
                        $txQueueData['txMsg'] = '[Tout]: (' . $toutValue . ' < ' . $sensorThToutMinValue .') ' . $sensorThToutAlertMsg;
                        setTxQueue($txQueueData);
                    }

                    if ($debugFlag === true)
                    {
                        echo "<br>" . '[Tout]: (' . $toutValue . ' < ' . $sensorThToutMinValue .') ' . $sensorThToutAlertMsg;
                        echo "<br>Tout Count:" . $resGetThTempData['sensorThToutAlertCount'];
                    }
                }

                if ($toutValue > $sensorThToutMaxValue && $sensorThToutMaxValue != '')
                {
                    if ($resGetThTempData['sensorThToutAlertCount'] < $mxSendAlertMsg)
                    {
                        setSensorAlertCounter('temp','Tout');
                        $txQueueData['txDst'] = $sensorThToutDmGrpId;
                        $txQueueData['txMsg'] = '[Tout]: (' . $toutValue . ' > ' . $sensorThToutMaxValue .') ' . $sensorThToutAlertMsg;
                        setTxQueue($txQueueData);
                    }

                    if ($debugFlag === true)
                    {
                        echo "<br>" . '[Tout]: (' . $toutValue . ' > ' . $sensorThToutMaxValue .') ' . $sensorThToutAlertMsg;
                        echo "<br>Tout Count:" . $resGetThTempData['sensorThToutAlertCount'];
                    }
                }
            }
        }

        echo "<br>hasIna226:";
        var_dump($hasIna226);

        echo "<br>anyIna226SensorActive:";
        var_dump($anyIna226SensorActive);

        echo "<br>anyTemSensorAktive:";
        var_dump($anyTemSensorAktive);

        # Wenn kein Ina226 vorhanden aber InaSensor aktiv dann alle abschalten
        if ($hasIna226 === false && $anyIna226SensorActive === true)
        {
            #Schalte alle Ina266 Sensoren ab
            echo "<br>Schalte alle Ina266 Sensoren ab";
            disableAllIna226Sensors();
            $anyIna226SensorActive = false;
        }

        # Wenn kein Ina226 vorhanden InaSensor inaktiv und TempSensor inaktiv
        # dann Cron abschalten
        if ($hasIna226 === false && $anyIna226SensorActive === false && $anyTemSensorAktive === false)
        {
            #Lösche Cron komplett, nur wenn Linux OS
            if ($osIssWindows === false)
            {
                echo "<br>Lösche Cron komplett!";
                setCronSensorInterval(1, 1);
            }
        }
    }
}

function checkSensorAlertCount(): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/sensor_th_ina226.db';
    $dbFilenameRoot = 'database/sensor_th_ina226.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $dbIna226 = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $dbIna226->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    $queryIna226 = "SELECT strftime('%s', 'now', 
                                CASE 
                                    -- Sommerzeit (letzter Sonntag im März bis letzter Sonntag im Oktober)
                                    WHEN (
                                        -- Berechnung für Sommerzeitbeginn: letzter Sonntag im März
                                        (strftime('%m', 'now') = '03' 
                                         AND strftime('%d', 'now') >= strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0') 
                                         AND strftime('%H:%M', 'now') >= '02:00') 
                                        OR 
                                        -- Sommerzeit: Monate April bis September
                                        (strftime('%m', 'now') IN ('04', '05', '06', '07', '08', '09'))
                                        OR 
                                        -- Berechnung für Sommerzeitende: letzter Sonntag im Oktober
                                        (strftime('%m', 'now') = '10' 
                                         AND strftime('%d', 'now') < strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0'))
                                        ) 
                                        THEN '+2 hours'  -- UTC → MESZ (+2h)
                                        ELSE '+1 hours'  -- UTC → MEZ (+1h)
                                END
                                ) - strftime('%s', sensorThIna226vBusAlertTimestamp) AS Ina226vBusTimeDiff,
                                                   sensorThIna226vBusAlertCount,
                            strftime('%s', 'now', 
                                 CASE 
                                        -- Sommerzeit (letzter Sonntag im März bis letzter Sonntag im Oktober)
                                        WHEN (
                                            -- Berechnung für Sommerzeitbeginn: letzter Sonntag im März
                                            (strftime('%m', 'now') = '03' 
                                             AND strftime('%d', 'now') >= strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0') 
                                             AND strftime('%H:%M', 'now') >= '02:00') 
                                            OR 
                                            -- Sommerzeit: Monate April bis September
                                            (strftime('%m', 'now') IN ('04', '05', '06', '07', '08', '09'))
                                            OR 
                                            -- Berechnung für Sommerzeitende: letzter Sonntag im Oktober
                                            (strftime('%m', 'now') = '10' 
                                             AND strftime('%d', 'now') < strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0'))
                                        ) 
                                        THEN '+2 hours'  -- UTC → MESZ (+2h)
                                        ELSE '+1 hours'  -- UTC → MEZ (+1h)
                                 END
                                 ) - strftime('%s', sensorThIna226vShuntAlertTimestamp) AS Ina226vShuntTimeDiff,
                                                    sensorThIna226vShuntAlertCount,
                            strftime('%s', 'now', 
                                CASE 
                                        -- Sommerzeit (letzter Sonntag im März bis letzter Sonntag im Oktober)
                                        WHEN (
                                            -- Berechnung für Sommerzeitbeginn: letzter Sonntag im März
                                            (strftime('%m', 'now') = '03' 
                                             AND strftime('%d', 'now') >= strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0') 
                                             AND strftime('%H:%M', 'now') >= '02:00') 
                                            OR 
                                            -- Sommerzeit: Monate April bis September
                                            (strftime('%m', 'now') IN ('04', '05', '06', '07', '08', '09'))
                                            OR 
                                            -- Berechnung für Sommerzeitende: letzter Sonntag im Oktober
                                            (strftime('%m', 'now') = '10' 
                                             AND strftime('%d', 'now') < strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0'))
                                        ) 
                                        THEN '+2 hours'  -- UTC → MESZ (+2h)
                                        ELSE '+1 hours'  -- UTC → MEZ (+1h)
                                 END
                                 ) - strftime('%s', sensorThIna226vCurrentAlertTimestamp) AS Ina226vCurrentTimeDiff,
                                                    sensorThIna226vCurrentAlertCount,
                            strftime('%s', 'now', 
                                CASE 
                                        -- Sommerzeit (letzter Sonntag im März bis letzter Sonntag im Oktober)
                                        WHEN (
                                            -- Berechnung für Sommerzeitbeginn: letzter Sonntag im März
                                            (strftime('%m', 'now') = '03' 
                                             AND strftime('%d', 'now') >= strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0') 
                                             AND strftime('%H:%M', 'now') >= '02:00') 
                                            OR 
                                            -- Sommerzeit: Monate April bis September
                                            (strftime('%m', 'now') IN ('04', '05', '06', '07', '08', '09'))
                                            OR 
                                            -- Berechnung für Sommerzeitende: letzter Sonntag im Oktober
                                            (strftime('%m', 'now') = '10' 
                                             AND strftime('%d', 'now') < strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0'))
                                        ) 
                                        THEN '+2 hours'  -- UTC → MESZ (+2h)
                                        ELSE '+1 hours'  -- UTC → MEZ (+1h)
                                 END
                                 ) - strftime('%s', sensorThIna226vPowerAlertTimestamp) AS Ina226vPowerTimeDiff,
                                                    sensorThIna226vPowerAlertCount
                    FROM sensorThIna226;
";

    $resultIna226 = $dbIna226->query($queryIna226);

    if ($dbIna226->lastErrorMsg() > 0 && $dbIna226->lastErrorMsg() < 100)
    {
        echo "<br>checkSensorAlertCount (ina226)";
        echo "<br>ErrMsg:" . $dbIna226->lastErrorMsg();
        echo "<br>ErrNum:" . $dbIna226->lastErrorCode();

        #Close and write Back WAL
        $dbIna226->close();
        unset($dbIna226);

        return false;
    }

    $dsDataTh     = $resultIna226->fetchArray(SQLITE3_ASSOC);

    $Ina226vBusTimeDiff           = $dsDataTh['Ina226vBusTimeDiff'];
    $sensorThIna226vBusAlertCount = $dsDataTh['sensorThIna226vBusAlertCount'];

    if ($sensorThIna226vBusAlertCount > 0 && $Ina226vBusTimeDiff >= 3600)
    {
        resetSensorAlertCounter('ina226', 'vBus');
    }

    $Ina226vShuntTimeDiff           = $dsDataTh['Ina226vShuntTimeDiff'];
    $sensorThIna226vShuntAlertCount = $dsDataTh['sensorThIna226vShuntAlertCount'];

    if ($sensorThIna226vShuntAlertCount > 0 && $Ina226vShuntTimeDiff >= 3600)
    {
        resetSensorAlertCounter('ina226', 'vShunt');
    }

    $Ina226vCurrentTimeDiff           = $dsDataTh['Ina226vCurrentTimeDiff'];
    $sensorThIna226vCurrentAlertCount = $dsDataTh['sensorThIna226vCurrentAlertCount'];

    if ($sensorThIna226vCurrentAlertCount > 0 && $Ina226vCurrentTimeDiff >= 3600)
    {
        resetSensorAlertCounter('ina226', 'vCurrent');
    }

    $Ina226vPowerTimeDiff           = $dsDataTh['Ina226vPowerTimeDiff'];
    $sensorThIna226vPowerAlertCount = $dsDataTh['sensorThIna226vPowerAlertCount'];

    if ($sensorThIna226vPowerAlertCount > 0 && $Ina226vPowerTimeDiff >= 3600)
    {
        resetSensorAlertCounter('ina226', 'vPower');
    }

    #Close and write Back WAL
    $dbIna226->close();
    unset($dbIna226);

    ################ Temp

    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/sensor_th_temp.db';
    $dbFilenameRoot = 'database/sensor_th_temp.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $dbTemp = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $dbTemp->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    $queryTemp = "SELECT strftime('%s', 'now', 
                                CASE 
                                        -- Sommerzeit (letzter Sonntag im März bis letzter Sonntag im Oktober)
                                        WHEN (
                                            -- Berechnung für Sommerzeitbeginn: letzter Sonntag im März
                                            (strftime('%m', 'now') = '03' 
                                             AND strftime('%d', 'now') >= strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0') 
                                             AND strftime('%H:%M', 'now') >= '02:00') 
                                            OR 
                                            -- Sommerzeit: Monate April bis September
                                            (strftime('%m', 'now') IN ('04', '05', '06', '07', '08', '09'))
                                            OR 
                                            -- Berechnung für Sommerzeitende: letzter Sonntag im Oktober
                                            (strftime('%m', 'now') = '10' 
                                             AND strftime('%d', 'now') < strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0'))
                                        ) 
                                        THEN '+2 hours'  -- UTC → MESZ (+2h)
                                        ELSE '+1 hours'  -- UTC → MEZ (+1h)
                                END
                                ) - strftime('%s', sensorThTempAlertTimestamp) AS tempTimeDiff,
                                                   sensorThTempAlertCount,
                         strftime('%s', 'now', 
                                CASE 
                                        -- Sommerzeit (letzter Sonntag im März bis letzter Sonntag im Oktober)
                                        WHEN (
                                            -- Berechnung für Sommerzeitbeginn: letzter Sonntag im März
                                            (strftime('%m', 'now') = '03' 
                                             AND strftime('%d', 'now') >= strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0') 
                                             AND strftime('%H:%M', 'now') >= '02:00') 
                                            OR 
                                            -- Sommerzeit: Monate April bis September
                                            (strftime('%m', 'now') IN ('04', '05', '06', '07', '08', '09'))
                                            OR 
                                            -- Berechnung für Sommerzeitende: letzter Sonntag im Oktober
                                            (strftime('%m', 'now') = '10' 
                                             AND strftime('%d', 'now') < strftime('%d', 'now', 'start of month', '+1 month', '-7 days', 'weekday 0'))
                                        ) 
                                        THEN '+2 hours'  -- UTC → MESZ (+2h)
                                        ELSE '+1 hours'  -- UTC → MEZ (+1h)
                                END
                                ) - strftime('%s', sensorThToutAlertTimestamp) AS toutTimeDiff,
                                                   sensorThToutAlertCount
                    FROM sensorThTemp;
";

    $resultTemp = $dbTemp->query($queryTemp);

    if ($dbTemp->lastErrorMsg() > 0 && $dbTemp->lastErrorMsg() < 100)
    {
        echo "<br>checkSensorAlertCount (Temp)";
        echo "<br>ErrMsg:" . $dbTemp->lastErrorMsg();
        echo "<br>ErrNum:" . $dbTemp->lastErrorCode();

        #Close and write Back WAL
        $dbTemp->close();
        unset($dbTemp);

        return false;
    }

    $dsDataTemp     = $resultTemp->fetchArray(SQLITE3_ASSOC);

    $tempTimeDiff           = $dsDataTemp['tempTimeDiff'];
    $sensorThTempAlertCount = $dsDataTemp['sensorThTempAlertCount'];

    if ($sensorThTempAlertCount > 0 && $tempTimeDiff >= 3600)
    {
        resetSensorAlertCounter('temp', 'temp');
    }

    $toutTimeDiff           = $dsDataTemp['toutTimeDiff'];
    $sensorThToutAlertCount = $dsDataTemp['sensorThToutAlertCount'];

    if ($sensorThToutAlertCount > 0 && $toutTimeDiff >= 3600)
    {
        resetSensorAlertCounter('temp', 'Tout');
    }

    #Close and write Back WAL
    $dbTemp->close();
    unset($dbTemp);

    return true;
}