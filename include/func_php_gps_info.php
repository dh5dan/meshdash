<?php
function getGpsInfo($loraIp): array
{
    // URL der Seite
    $actualHost  = 'http';
    $url         = $actualHost . '://' . $loraIp . '/position';
    $ina226Count = -1;
    $info        = array();  // Das Array, in dem alle Daten abgelegt werden
    $debugFlag   = false;

    #Check new GUI
    if (getParamData('isNewMeshGui') == 1)
    {
        return getGpsInfo2($loraIp);
    }

    // HTML-Inhalt abrufen
    $htmlContent = @file_get_contents($url);

    if ($htmlContent === false)
    {
        echo '<tr>';
        echo '<th colspan="3" ><span class="failureHint">Fehler beim Abrufen der Info-Seite.</span></th>';
        echo '</tr>';

        return $info;
    }

    #Workaround in MeshCom 4.34q (build: Mar 6 2025 / 15:17:43)
    #Falscher Tr Tag auf info Seite, dadurch wird Call nicht mehr erkannt und Hardware
    $htmlContent = preg_replace('/<tr><tr><\/tr>/', '</tr><tr>', $htmlContent);

    // HTML mit DOMDocument parsen
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($htmlContent);
    libxml_clear_errors();
    libxml_use_internal_errors(false);


    // XPath-Abfrage, um die relevanten Zeilen zu extrahieren
    $xpath = new DOMXPath($doc);

    // Extrahiert alle <tr>-Elemente, die Schlüssel-Wert-Paare enthalten
    $rows = $xpath->query('//tr');

    $isSettingsKey     = false;
    $isMeshSettingsKey = false;

    $arrayKey = array(
        'Firmware',
        'Start-Date',
        'UTC-OFF',
        'BATT',
        'COUNTRY',
        'FREQ',
        'BW',
        'SF',
        'CR',
        'TXPWR',
        'SSID',
        'WIFI-AP',
        'hasIpAddress',
        'IP address',
        'GW address',
        'DNS address',
        'SUB-MASK',
        'INA226',
        'vBUS',
        'vSHUNT',
        'vCURRENT',
        'vPOWER',
    );

    foreach ($rows as $row)
    {
        $cells = $row->getElementsByTagName('td');

        if ($cells->length === 2)
        {
            $key   = trim($cells->item(0)->nodeValue);
            $value = trim($cells->item(1)->nodeValue);

            // Wenn "Call" gefunden wird
            if ($key == 'Call')
            {
                $info['Call'] = explode(' ', $value)[0];

                // Die Hardware-Version extrahieren und als "hardware" speichern
                #$info['Hardware'] = explode('...', $value)[1];
                $parts = explode('...', $value);
                $info['Hardware'] = $parts[1] ?? '';

            }
            // Wenn "Setting" gefunden wird
            elseif (($key == 'Setting' && $isSettingsKey === false) || (empty($key) && $isSettingsKey === false))
            {
                // Ersetze die Ellipse (mit möglichen Leerzeichen drumherum) durch ein einzelnes Leerzeichen
                $string = preg_replace('/\s*\.\.\.\s*/', ' ', $value);
                // Jetzt ist der String: "GATEWAY off nopos MESH off"

                // Zerlege den String in Tokens (Trennung an einem oder mehreren Leerzeichen)
                $tokens = preg_split('/\s+/', $string);

                // Ergebnis-Array initialisieren
                if (!isset($info['setting']))
                {
                    $info['setting'] = [];
                }

                // Durchlaufe die Tokens
                $tokenCount = count($tokens);
                for ($i = 0; $i < $tokenCount;)
                {
                    $token = $tokens[$i];

                    // Prüfe, ob es einen nächsten Token gibt und ob dieser "on" oder "off" ist
                    if (($i + 1) < count($tokens) && in_array(strtolower($tokens[$i + 1]), ['on', 'off']))
                    {
                        $info['setting'][$token] = $tokens[$i + 1];
                        $i                       += 2; // überspringe beide Tokens
                    }
                    else
                    {
                        // Falls kein "on"/"off" folgt, nehme den Token als Flag: key und value identisch
                        $info['setting'][$token] = $token;
                        $i++;
                    }
                }
            }
            elseif ($key == 'APRS-TXT')
            {
                $info['APRS-TXT'] = '';
                $isSettingsKey    = true;
            }
            elseif (($key == 'MESH-Settings' && $isMeshSettingsKey === false) || (empty($key) && $isMeshSettingsKey === false))
            {
                if (!empty($key))
                {
                    $info['MESH-Settings']['text'] = $value;
                }
                else
                {
                    $info['MESH-Settings']['pos'] = $value;
                }
            }
            // Weitere Standardwerte
            if (in_array($key, $arrayKey))
            {
                #Sonderlocke für INA226
                #Prüfe ob INA226 Header erkannt wurde.
                #Wenn ja, speicher INA226 Werte in Array 2. Dimension
                if ($ina226Count >= 0)
                {
                    $info['INA226'][$key] = $value;
                    ++$ina226Count;
                }

                #Prüfe oB ina226 Header vorhanden ist und erhöhe CounterFlag
                if ($key == 'INA226')
                {
                    ++$ina226Count;
                }

                #Schreibe normale Werte in der Form: Key = Value
                if ($ina226Count == -1)
                {
                    $info[$key] = $value;
                }

                #Wenn Max Werte von INA226 eingelesen, dann auf Normal-Mode zurückschalten
                if ($ina226Count == 4)
                {
                    $ina226Count = -1;
                }
            }

            if ($debugFlag === true)
            {
                echo "<br>key: $key";
                echo "<br>ina226Count:$ina226Count";
            }
        }
    }

    if ($debugFlag === true)
    {
        echo "<pre>";
        print_r($info);
        echo "</pre>";
    }

    return $info;
}
function getGpsInfo2($loraIp): array
{
    $url       = 'http://' . $loraIp . '/?page=position';
    $info      = array();
    $html      = @file_get_contents($url);

    if ($html === false)
    {
        echo '<tr>';
        echo '<th colspan="3" ><span class="failureHint">Fehler beim Abrufen der Info-Seite.</span></th>';
        echo '</tr>';

        return $info;
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();
    libxml_use_internal_errors(false);

    $xpath = new DOMXPath($doc);

    #Anpassung an V 4.35c. Trigger auf Css Klasse hat sich geändert.
    $rows  = $xpath->query('//table[contains(concat(" ", normalize-space(@class), " "), " table ")]//tr');

    foreach ($rows as $row) {
        $tds = $row->getElementsByTagName('td');

        if ($tds->length === 2) {
            $key   = trim($tds->item(0)->nodeValue);
            $value = trim($tds->item(1)->nodeValue);

            // Normalize key
            $key = strtolower($key);
            $key = str_replace([' ', '-', ':', '(', ')'], '_', $key);

            switch ($key) {

                default:
                    // Fallback: speichern, wenn noch nicht gesetzt
                    if (!isset($info[$key])) {
                        $info[$key] = $value;
                    }
                    break;
            }
        }
    }

    return $info;
}
function showGpsInfo($localInfoArray)
{
    if (count($localInfoArray) == 0)
    {
        echo "<br>Keine Infodaten empfangen.";
        return false;
    }

    #Wenn Daten aus new GUI dann andere Darstellung anzeigen
    if (getParamData('isNewMeshGui') == 1)
    {
        showGpsInfo2($localInfoArray);
        return true;
    }

    echo '<tr>';
    echo '<th colspan="10" ><hr></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Breitengrad:</td>';
    echo '<td colspan="2">' . ($localInfoArray['Latitude'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Längengrad:</td>';
    echo '<td colspan="2">' . ($localInfoArray['Longitude'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Pos Tx-Intervall:</td>';
    echo '<td colspan="2">' . ($localInfoArray['rate'] ?? '') . ' Sek.</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Nächste Pos-Sendung:</td>';
    echo '<td colspan="2">' . ($localInfoArray['next'] ?? '') . ' Sek.</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td >Aktuelle Richtung:</td>';
    echo '<td colspan="2">' . ($localInfoArray['dir__current_'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Letzte Richtung:</td>';
    echo '<td colspan="2">' . ($localInfoArray['dir__last_'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Timestamp:</td>';
    echo '<td colspan="2">' . ($localInfoArray['date'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>APRS-Symbol:</td>';
    echo '<td colspan="2">' . ($localInfoArray['symbol'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>GPS-Status:</td>';
    echo '<td colspan="2">' . ($localInfoArray['gps'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Track-Status:</td>';
    echo '<td colspan="2">' . ($localInfoArray['track'] ?? '') . '</td>';
    echo '</tr>';
}
function showGpsInfo2($localInfoArray)
{
    if (count($localInfoArray) == 0)
    {
        echo "<br>Keine Infodaten empfangen.";
        return false;
    }

    echo '<tr>';
    echo '<th colspan="10" ><hr></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Breitengrad:</td>';
    echo '<td colspan="2">' . ($localInfoArray['latitude'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Längengrad:</td>';
    echo '<td colspan="2">' . ($localInfoArray['longitude'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Pos Tx-Intervall:</td>';
    echo '<td colspan="2">' . ($localInfoArray['rate'] ?? '') . ' Sek.</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Nächste Pos-Sendung:</td>';
    echo '<td colspan="2">' . ($localInfoArray['next'] ?? '') . ' Sek.</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td >Aktuelle Richtung:</td>';
    echo '<td colspan="2">' . ($localInfoArray['dir__current_'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Letzte Richtung:</td>';
    echo '<td colspan="2">' . ($localInfoArray['dir__last_'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Timestamp:</td>';
    echo '<td colspan="2">' . ($localInfoArray['date'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>APRS-Symbol:</td>';
    echo '<td colspan="2">' . ($localInfoArray['symbol'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>GPS-Status:</td>';
    echo '<td colspan="2">' . ($localInfoArray['gps'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Track-Status:</td>';
    echo '<td colspan="2">' . ($localInfoArray['track'] ?? '') . '</td>';
    echo '</tr>';
}

