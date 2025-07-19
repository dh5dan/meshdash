<?php
function getGpsInfo($loraIp): array
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

            if (!isset($info[$key]))
            {
                $info[$key] = $value;
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

