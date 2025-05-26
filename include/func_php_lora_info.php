<?php
function getLoraInfo($loraIp): array
{
    // URL der Seite
    $actualHost  = 'http';
    $url         = $actualHost . '://' . $loraIp . '/info';
    $ina226Count = -1;
    $info        = array();  // Das Array, in dem alle Daten abgelegt werden
    $debugFlag   = false;

    #Check new GUI
    if (getParamData('isNewMeshGui') == 1)
    {
        return getLoraInfo2($loraIp);
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
function getLoraInfo2($loraIp): array
{
    $url       = 'http://' . $loraIp . '/?page=info';
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
    $rows  = $xpath->query('//table[@class="table"]//tr');

    foreach ($rows as $row) {
        $tds = $row->getElementsByTagName('td');

        if ($tds->length === 2) {
            $key   = trim($tds->item(0)->nodeValue);
            $value = trim($tds->item(1)->nodeValue);

            // Normalize key
            $key = strtolower($key);
            $key = str_replace([' ', '-', ':', '(', ')'], '_', $key);

            switch ($key) {
                case 'call':
                case 'hardware':
                case 'firmware':
                case 'start_date':
                case 'utc_offset':
                case 'battery':
                case 'country':
                case 'frequency':
                case 'bandwidth':
                case 'spreading_factor_sf':
                case 'coding_rate_cr':
                case 'tx_power':
                case 'wifi_ap':
                case 'wifi_ssid':
                case 'hasipaddress':
                case 'ip_address':
                case 'gw_address':
                case 'dns_address':
                case 'sub_mask':
                    $info[$key] = $value;
                    break;

                case 'settings':
                    $lines = explode('<br>', $tds->item(1)->ownerDocument->saveHTML($tds->item(1)));
                    foreach ($lines as $line)
                    {
                        if (preg_match('/(.+?):\s*(on|off)/i', strip_tags($line), $matches))
                        {
                            $settingKey                    = strtolower(str_replace([' ', '-'], '_', trim($matches[1])));
                            $info['settings'][$settingKey] = strtolower($matches[2]);
                        }
                    }
                    break;

                case 'aprs_text':
                    $info['aprs_text'] = $value;
                    break;

                case 'mesh_settings':
                    $lines = explode('<br>', $tds->item(1)->ownerDocument->saveHTML($tds->item(1)));
                    foreach ($lines as $line)
                    {
                        if (preg_match('/(.+?):\s*(.+)/i', strip_tags($line), $matches))
                        {
                            $meshKey = strtolower(str_replace([' ', '-'], '_', trim($matches[1])));
                            $info['mesh_settings'][$meshKey] = trim($matches[2]);
                        }
                    }
                    break;

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
function showLoraInfo($localInfoArray)
{
    if (count($localInfoArray) == 0)
    {
        return false;
    }

    #Wenn Daten aus new GUI dann andere Darstellung anzeigen
    if (getParamData('isNewMeshGui') == 1)
    {
        showLoraInfo2($localInfoArray);
        return true;
    }

    echo '<tr>';
    echo '<th colspan="10" ><hr></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Firmware:</td>';
    echo '<td colspan="2">' . ($localInfoArray['Firmware'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Startzeit:</td>';
    echo '<td colspan="2">' . ($localInfoArray['Start-Date'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Rufzeichen:</td>';
    echo '<td colspan="2">' . ($localInfoArray['Call'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Hardware:</td>';
    echo '<td colspan="2">' . ($localInfoArray['Hardware'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td >UTC-OFF:</td>';
    echo '<td colspan="2">' . ($localInfoArray['UTC-OFF'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>BATT:</td>';
    echo '<td colspan="2">' . ($localInfoArray['BATT'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>SETTINGS:</td>';
    echo '<td colspan="2">&nbsp;</td>';
    echo '</tr>';

    if (isset($localInfoArray['setting']) && is_array($localInfoArray['setting']))
    {
        foreach ($localInfoArray['setting'] as $key => $value)
        {
            echo '<tr>';
            echo '<td>&nbsp;</td>';
            echo '<td>' . $key . '</td>';
            echo '<td>' . $value . '</td>';
            echo '</tr>';
        }
    }

    echo '<tr>';
    echo '<td>APRS-TXT:</td>';
    echo '<td colspan="2">' . ($localInfoArray['APRS-TXT'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>MESH-Settings:</td>';
    echo '<td colspan="2">' . ($localInfoArray['MESH-Settings']['text'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>&nbsp;</td>';
    echo '<td colspan="2">' . ($localInfoArray['MESH-Settings']['pos'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>COUNTRY:</td>';
    echo '<td colspan="2">' . ($localInfoArray['COUNTRY'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Frequenz:</td>';
    echo '<td colspan="2">' . ($localInfoArray['FREQ'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Bandbreite:</td>';
    echo '<td colspan="2">' . ($localInfoArray['BW'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>SF:</td>';
    echo '<td colspan="2">' . ($localInfoArray['SF'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>CR:</td>';
    echo '<td colspan="2">' . ($localInfoArray['CR'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Sendeleistung:</td>';
    echo '<td colspan="2">' . ($localInfoArray['TXPWR'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>SSID:</td>';
    echo '<td colspan="2">' . ($localInfoArray['SSID'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>WIFI-AP:</td>';
    echo '<td colspan="2">' . ($localInfoArray['WIFI-AP'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Hat Ip-Adresse:</td>';
    echo '<td colspan="2">' . ($localInfoArray['hasIpAddress'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>IP-Adresse:</td>';
    echo '<td colspan="2">' . ($localInfoArray['IP address'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Gateway Adresse:</td>';
    echo '<td colspan="2">' . ($localInfoArray['GW address'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>DNS-Maske:</td>';
    echo '<td colspan="2">' . ($localInfoArray['DNS address'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Subnetz-Maske:</td>';
    echo '<td colspan="2">' . ($localInfoArray['SUB-MASK'] ?? '') . '</td>';
    echo '</tr>';
}
function showLoraInfo2($localInfoArray)
{
    if (count($localInfoArray) == 0)
    {
        return false;
    }

    echo '<tr>';
    echo '<th colspan="10" ><hr></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Firmware:</td>';
    echo '<td colspan="2">' . ($localInfoArray['firmware'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Startzeit:</td>';
    echo '<td colspan="2">' . ($localInfoArray['start_date'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Rufzeichen:</td>';
    echo '<td colspan="2">' . ($localInfoArray['call'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Hardware:</td>';
    echo '<td colspan="2">' . ($localInfoArray['hardware'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td >UTC-OFF:</td>';
    echo '<td colspan="2">' . ($localInfoArray['utc_offset'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>BATT:</td>';
    echo '<td colspan="2">' . ($localInfoArray['battery'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>SETTINGS:</td>';
    echo '<td colspan="2">&nbsp;</td>';
    echo '</tr>';

    if (isset($localInfoArray['settings']) && is_array($localInfoArray['settings']))
    {
        foreach ($localInfoArray['settings'] as $key => $value)
        {
            echo '<tr>';
            echo '<td>&nbsp;</td>';
            echo '<td>' . $key . '</td>';
            echo '<td>' . $value . '</td>';
            echo '</tr>';
        }
    }

    echo '<tr>';
    echo '<td>APRS-TXT:</td>';
    echo '<td colspan="2">' . ($localInfoArray['aprs_text'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>MESH-Settings:</td>';
    echo '<td colspan="2">Max-Hops: ' . ($localInfoArray['mesh_settings']['max_hop_text'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>&nbsp;</td>';
    echo '<td colspan="2">Hops-Pos: ' . ($localInfoArray['mesh_settings']['max_hop_pos'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>COUNTRY:</td>';
    echo '<td colspan="2">' . ($localInfoArray['country'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Frequenz:</td>';
    echo '<td colspan="2">' . ($localInfoArray['frequency'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Bandbreite:</td>';
    echo '<td colspan="2">' . ($localInfoArray['bandwidth'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Spread-Factor::</td>';
    echo '<td colspan="2">' . ($localInfoArray['spreading_factor__sf_'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Coding-Rate:</td>';
    echo '<td colspan="2">' . ($localInfoArray['coding_rate__cr_'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Sendeleistung:</td>';
    echo '<td colspan="2">' . ($localInfoArray['tx_power'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>SSID:</td>';
    echo '<td colspan="2">' . ($localInfoArray['wifi_ssid'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>WIFI-AP:</td>';
    echo '<td colspan="2">' . ($localInfoArray['wifi_ap'] ?? ''). '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Hat Ip-Adresse:</td>';
    echo '<td colspan="2">' . ($localInfoArray['hasipaddress'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>IP-Adresse:</td>';
    echo '<td colspan="2">' . ($localInfoArray['ip_address'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Gateway Adresse:</td>';
    echo '<td colspan="2">' . ($localInfoArray['gw_address'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>DNS-Maske:</td>';
    echo '<td colspan="2">' . ($localInfoArray['dns_address'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Subnetz-Maske:</td>';
    echo '<td colspan="2">' . ($localInfoArray['sub_mask'] ?? '') . '</td>';
    echo '</tr>';
}

