<?php

function getLoraInfo($loraIp)
{
    // URL der Seite
    $actualHost  = 'http';
    $url         = $actualHost . '://' . $loraIp . '/info';
    $ina226Count = -1;
    $debugFlag   = false;

    if ($debugFlag === true)
    {
        $url = $actualHost . '://192.168.1.111/meshdash/test/info_new/info.html';
    }

    // HTML-Inhalt abrufen
    $htmlContent = @file_get_contents($url);

    if ($htmlContent === false)
    {
        echo "Fehler beim Abrufen der Info-Seite.";
        exit;
    }

    #Workaround in MeshCom 4.34q (build: Mar 6 2025 / 15:17:43)
    #Falscher Tr Tag auf info Seite, dadurch wird Call nicht mehr erkannt und Hardware
    $htmlContent = preg_replace('/<tr><tr><\/tr>/', '</tr><tr>', $htmlContent);

    $info = [];  // Das Array, in dem alle Daten abgelegt werden

    // HTML mit DOMDocument parsen
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($htmlContent);
    libxml_clear_errors();

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
                $info['Hardware'] = explode('...', $value)[1];
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
                for ($i = 0; $i < count($tokens);)
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

                #Schreibe normale Werte in Form Key = Value
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

function showLoraInfo($localInfoArray)
{
    echo '<table class="table">';

    echo '<tr>';
    echo '<th class="thCenter">Lora-Infoseite</th>';
    echo '<th colspan="2"><input type="button" class="btnLoadLoraInfo" id="btnLoadLoraInfo" value="Info-Seite neu laden" /></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<th colspan="10" ><hr></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Firmware:</td>';
    echo '<td colspan="2">' . $localInfoArray['Firmware'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Startzeit:</td>';
    echo '<td colspan="2">' . ($localInfoArray['Start-Date'] ?? '') . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Rufzeichen:</td>';
    echo '<td colspan="2">' . $localInfoArray['Call'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Hardware:</td>';
    echo '<td colspan="2">' . $localInfoArray['Hardware'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td >UTC-OFF:</td>';
    echo '<td colspan="2">' . $localInfoArray['UTC-OFF'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>BATT:</td>';
    echo '<td colspan="2">' . $localInfoArray['BATT'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>SETTINGS:</td>';
    echo '<td colspan="2">&nbsp;</td>';
    echo '</tr>';
    foreach ($localInfoArray['setting'] AS $key=>$value)
    {
        echo '<tr>';
        echo '<td>&nbsp;</td>';
        echo '<td>' . $key . '</td>';
        echo '<td>' . $value . '</td>';
        echo '</tr>';
    }

    echo '<tr>';
    echo '<td>APRS-TXT:</td>';
    echo '<td colspan="2">' . $localInfoArray['APRS-TXT'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>MESH-Settings:</td>';
    echo '<td colspan="2">' . $localInfoArray['MESH-Settings']['text'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>&nbsp;</td>';
    echo '<td colspan="2">' . $localInfoArray['MESH-Settings']['pos'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>COUNTRY:</td>';
    echo '<td colspan="2">' . $localInfoArray['COUNTRY'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Frequenz:</td>';
    echo '<td colspan="2">' . $localInfoArray['FREQ'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Bandbreite:</td>';
    echo '<td colspan="2">' . $localInfoArray['BW'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>SF:</td>';
    echo '<td colspan="2">' . $localInfoArray['SF'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>CR:</td>';
    echo '<td colspan="2">' . $localInfoArray['CR'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Sendeleistung:</td>';
    echo '<td colspan="2">' . $localInfoArray['TXPWR'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>SSID:</td>';
    echo '<td colspan="2">' . $localInfoArray['SSID'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>WIFI-AP:</td>';
    echo '<td colspan="2">' . $localInfoArray['WIFI-AP'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Hat Ip-Adresse:</td>';
    echo '<td colspan="2">' . $localInfoArray['hasIpAddress'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>IP-Adresse:</td>';
    echo '<td colspan="2">' . $localInfoArray['IP address'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Gateway Adresse:</td>';
    echo '<td colspan="2">' . $localInfoArray['GW address'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>DNS-Maske:</td>';
    echo '<td colspan="2">' . $localInfoArray['DNS address'] . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Subnetz-Maske:</td>';
    echo '<td colspan="2">' . $localInfoArray['SUB-MASK'] . '</td>';
    echo '</tr>';

    echo '<table>';


}

