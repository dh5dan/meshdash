<?php

function getLoraInfo($loraIp)
{
    // URL der Seite
    $actualHost = 'http';
    $url        = $actualHost . '://' . $loraIp . '/info';

    // HTML-Inhalt abrufen
    $htmlContent = @file_get_contents($url);

    if ($htmlContent === false)
    {
        echo "Fehler beim Abrufen der Seite.";
        exit;
    }

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

    foreach ($rows as $row)
    {
        $cells = $row->getElementsByTagName('td');

        if ($cells->length === 2)
        {
            $key   = trim($cells->item(0)->nodeValue);
            $value = trim($cells->item(1)->nodeValue);

            // Wenn "Firmware" gefunden wird
            if ($key == 'Firmware')
            {
                $info['Firmware'] = $value;
            }
            // Wenn "Call" gefunden wird
            elseif ($key == 'Call')
            {
                $info['Call'] = explode(' ', $value)[0];

                // Die Hardware-Version extrahieren und als "hardware" speichern
                $info['Hardware'] = explode('...', $value)[1];
            }
            // Wenn "UTC-OFF" gefunden wird
            elseif ($key == 'UTC-OFF')
            {
                $info['UTC-OFF'] = $value;
            }
            // Wenn "BATT" gefunden wird
            elseif ($key == 'BATT')
            {
                $info['BATT'] = $value;
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
            elseif ($key == 'COUNTRY')
            {
                $isMeshSettingsKey = true;
                $info['COUNTRY']   = $value;
            }
            elseif ($key == 'FREQ')
            {
                $info['FREQ'] = $value;
            }
            elseif ($key == 'BW')
            {
                $info['BW'] = $value;
            }
            elseif ($key == 'SF')
            {
                $info['SF'] = $value;
            }
            elseif ($key == 'CR')
            {
                $info['CR'] = $value;
            }
            elseif ($key == 'TXPWR')
            {
                $info['TXPWR'] = $value;
            }
            elseif ($key == 'SSID')
            {
                $info['SSID'] = $value;
            }
            elseif ($key == 'WIFI-AP')
            {
                $info['WIFI-AP'] = $value;
            }
            elseif ($key == 'hasIpAddress')
            {
                $info['hasIpAddress'] = $value;
            }
            elseif ($key == 'IP address')
            {
                $info['IP address'] = $value;
            }
            elseif ($key == 'GW address')
            {
                $info['GW address'] = $value;
            }
            elseif ($key == 'DNS address')
            {
                $info['DNS address'] = $value;
            }
            elseif ($key == 'SUB-MASK')
            {
                $info['SUB-MASK'] = $value;
            }
        }
    }

    return $info;
}

function showLoraInfo($localInfoArray)
{
    echo '<table class="table">';

    echo '<tr>';
    echo '<th class="thCenter">Lora-Infoseite</th>';
    echo '<th colspan="2"><input type="button" id="btnLoadLoraInfo" value="Info-Seite neu laden" /></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<th colspan="10" ><hr></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<td>Firmware:</td>';
    echo '<td colspan="2">' . $localInfoArray['Firmware'] . '</td>';
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

