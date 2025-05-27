<?php

function getMheard($loraIp)
{
    // Array, um die Daten zu speichern
    $heardData = [];

    // URL der Remote-Seite
    $actualHost = 'http';
    $url        = $actualHost . '://' . $loraIp . '/mheard';

    #Check New GUI
    if (getParamData('isNewMeshGui') == 1)
    {
        return getMheard2($loraIp);
    }

    // Holen des HTML-Inhalts von der Remote-Seite
    $htmlContent = @file_get_contents($url);

    if ($htmlContent === false)
    {
        echo '<br><span class="failureHint">Keine Daten zu finden unter der Url: ' . $url . '</span>';

        return false;
    }

    // Initialisieren des DOMDocuments
    $doc = new DOMDocument();
    libxml_use_internal_errors(true); // Fehler unterdrücken
    $doc->loadHTML($htmlContent);
    libxml_clear_errors();

    // Suchen nach der Tabelle mit den relevanten Daten
    $tableRows = $doc->getElementsByTagName('tr');
    $mheardValueIsValid = true;

    foreach ($tableRows as $row)
    {
        $cols = $row->getElementsByTagName('td');

        // Wenn es mehr als 0 Zellen gibt, dann schauen wir uns die Zeile an
        if ($cols->length > 0)
        {
            // Prüfen, ob jede Zelle existiert, bevor auf sie zugegriffen wird
            $callSign = $cols->item(0) ? trim($cols->item(0)->nodeValue) : '';
            $date     = $cols->item(1) ? trim($cols->item(1)->nodeValue) : '';
            $time     = $cols->item(2) ? trim($cols->item(2)->nodeValue) : '';
            $mhType   = $cols->item(3) ? trim($cols->item(3)->nodeValue) : '';
            $hardware = $cols->item(4) ? trim($cols->item(4)->nodeValue) : '';
            $mod      = $cols->item(5) ? trim($cols->item(5)->nodeValue) : '';
            $rssi     = $cols->item(6) ? trim($cols->item(6)->nodeValue) : '';
            $snr      = $cols->item(7) ? trim($cols->item(7)->nodeValue) : '';
            $dist     = $cols->item(8) ? trim($cols->item(8)->nodeValue) : '';
            $pl       = $cols->item(9) ? trim($cols->item(9)->nodeValue) : '';
            $m        = $cols->item(10) ? trim($cols->item(10)->nodeValue) : '';

            // Falls die Zelle einen Button oder Link enthält, überspringen wir sie
            if (empty($callSign) || preg_match('/<button.*?>.*?<\/button>/', $cols->item(0)->C14N()))
            {
                continue; // Überspringe diese Zeile
            }

            // Speichern der extrahierten Daten
            $heardData[] = [
                'callSign' => $callSign,
                'date'     => $date,
                'time'     => $time,
                'mhType'   => $mhType,
                'hardware' => $hardware,
                'mod'      => $mod,
                'rssi'     => $rssi,
                'snr'      => $snr,
                'dist'     => $dist,
                'pl'       => $pl,
                'm'        => $m
            ];

            // ISO-Datum
            if (!validateMheardValue( $date, 'isoDate')) {
                #echo "<br>✅ ISO-Datum falsch: $date\n";
                $mheardValueIsValid = false;
            }

            // Uhrzeit
            if (!validateMheardValue( $time, 'time')) {
                #echo "<br>✅ Uhrzeit falsch: $time\n";
                $mheardValueIsValid = false;
            }

            // Float (mit Punkt oder Komma)
            if (!validateMheardValue( $dist, 'float')) {
                #echo "<br>✅ Float falsch: $dist\n";
                $mheardValueIsValid = false;
            }

            // Integer (auch negativ)
            if (!validateMheardValue( $rssi, 'integer')) {
                #echo "<br>✅ Integer falsch: $rssi\n";
                $mheardValueIsValid = false;
            }
        }
    }

    if (count($heardData) > 0)
    {
        if ($mheardValueIsValid === true)
        {
            setMheardData($heardData);
        }
        else
        {
            echo '<span class="failureHint">Fehlerhafte Mheard-Daten vom Node empfangen.</span>';
            return false;
        }
    }

    if (count($heardData) == 0)
    {
        echo '<h3>Keine MHeard-Daten gefunden.';
        echo '<br>Zeige zuletzt gespeicherte Werte wenn vorhanden.</br></h3>';
        return false;
    }

    return true;
}

function getMheard2($loraIp)
{
    // Array, um die Daten zu speichern
    $heardData = [];

    // URL der Remote-Seite
    $actualHost = 'http';
    $url        = $actualHost . '://' . $loraIp . '/?page=mheard';

    // Holen des HTML-Inhalts von der Remote-Seite
    $htmlContent = @file_get_contents($url);

    if ($htmlContent === false)
    {
        echo '<br><span class="failureHint">Keine Daten zu finden unter der Url: ' . $url . '</span>';
        exit();
    }

    // Initialisieren des DOMDocuments
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($htmlContent);
    libxml_clear_errors();

    // Alle Divs mit class="cardlayout" erfassen
    $cards = $doc->getElementsByTagName('div');
    $mheardValueIsValid = false;

    foreach ($cards as $card) {
        if ($card->getAttribute('class') === 'cardlayout') {

            $label = $card->getElementsByTagName('label')->item(0);
            if (!$label) continue;

            // Rufzeichen aus dem <a>-Element
            $a = $label->getElementsByTagName('a')->item(0);
            $callSign = $a ? trim($a->nodeValue) : '';

            // Zeitstempel aus dem <span class="font-small">
            $span = $label->getElementsByTagName('span')->item(0);
            $datetime = $span ? trim($span->nodeValue) : '';

            // Datum & Zeit extrahieren
            preg_match('/\((\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2})\)/', $datetime, $matches);
            $date = $matches[1] ?? '';
            $time = $matches[2] ?? '';

            $entry = [
                'callSign' => $callSign,
                'date'     => $date,
                'time'     => $time,
                'mhType'   => '',
                'hardware' => '',
                'mod'      => '',
                'rssi'     => '',
                'snr'      => '',
                'dist'     => '',
            ];

            // Jetzt: Suche rekursiv alle <span class="font-bold"> im cardlayout-Block
            $spans = $card->getElementsByTagName('span');

            for ($i = 0; $i < $spans->length; $i++) {
                $span = $spans->item($i);
                $class = $span->getAttribute('class');

                if (strpos($class, 'font-bold') !== false) {
                    $key = trim(str_replace(':', '', $span->nodeValue));

                    // Eltern-<div> ermitteln
                    $parentDiv = $span->parentNode;

                    // Zwei <span> in dem Block (key und value)
                    $innerSpans = $parentDiv->getElementsByTagName('span');
                    if ($innerSpans->length < 2) continue;

                    $val = trim($innerSpans->item(1)->nodeValue);

                    switch (strtolower($key)) {
                        case 'type':     $entry['mhType']   = $val; break;
                        case 'hardware': $entry['hardware'] = $val; break;
                        case 'mod':      $entry['mod']      = $val; break;
                        case 'rssi':     $entry['rssi']     = $val; break;
                        case 'snr':      $entry['snr']      = $val; break;
                        case 'dist':     $entry['dist']     = $val; break;
                    }
                }
            }

            $heardData[] = $entry;
        }
    }

    foreach ($heardData as $index => $entry)
    {
        $mheardValueIsValid = true;

        if (!validateMheardValue($entry['date'], 'isoDate'))
        {
            $mheardValueIsValid = false;
            echo '<br><span class="failureHint">Fehler bei Eintrag $index: Ungültiges Datum: ' . $entry['date'] . '</span>';
        }

        if (!validateMheardValue($entry['time'], 'time'))
        {
            $mheardValueIsValid = false;
            echo '<br><span class="failureHint">Fehler bei Eintrag $index: Ungültige Zeit: ' . $entry['time'] . '</span>';
        }

        if (!validateMheardValue($entry['dist'], 'float'))
        {
            $mheardValueIsValid = false;
            echo '<br><span class="failureHint">Fehler bei Eintrag $index: Ungültige Distanz: ' . $entry['dist'] . '</span>';
        }

        // RSSI: "dBm" wegstrippen, nur Zahl behalten
        if (isset($entry['rssi']))
        {
            // z.B. "-124dBm" -> "-124"
            $rssi_clean    = preg_replace('/[^\-0-9]/', '', $entry['rssi']);
            $entry['rssi'] = $rssi_clean;
            if (!validateMheardValue($entry['rssi'], 'integer'))
            {
                $mheardValueIsValid = false;
                echo '<br><span class="failureHint">Fehler bei Eintrag $index: Ungültiger RSSI: ' . $entry['rssi'] . '</span>';
            }
        }

        if ($mheardValueIsValid === false)
        {
            // Hier kannst du den fehlerhaften Eintrag aus $heardData entfernen, wenn gewünscht:
            unset($heardData[$index]);
        }
    }

    if (count($heardData) > 0)
    {
        if ($mheardValueIsValid === true)
        {
            setMheardData($heardData);
        }
        else
        {
            echo '<span class="failureHint">Fehlerhafte Mheard-Daten vom Node empfangen. FW >= v4.34x.05.18 ?X</span>';
            return false;
        }
    }

    if (count($heardData) == 0)
    {
        echo '<h3>Keine MHeard-Daten gefunden.';
        echo '<br>Zeige zuletzt gespeicherte Werte wenn vorhanden.</br></h3>';
        return false;
    }

    return true;
}

function showMheard($localCallSign)
{
    $db = new SQLite3('database/mheard.db', SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    // Hole mir den Timestamp der letzten importierten Mheard Liste aus der Datenbank
    $result = $db->query("SELECT max(timestamps) as timestamps from mheard;");

    $dsData = $result->fetchArray(SQLITE3_ASSOC);

    $validData = !empty($dsData);

    if ($validData)
    {
        $timeStamp = $dsData['timestamps'];

        $resultMh = $db->query("SELECT * 
                                        FROM mheard
                                       WHERE timestamps = '$timeStamp'
                                    ORDER BY mhTime DESC;
                        ");

        if ($resultMh !== false)
        {
            $drawHeader = true;

            while ($row = $resultMh->fetchArray(SQLITE3_ASSOC))
            {
                ###############################################
                #Common
                $callSign = $row['mhCallSign'];
                $date     = $row['mhDate'];
                $time     = $row['mhTime'];
                $type     = $row['mhType'];
                $hardware = $row['mhHardware'];
                $mod      = $row['mhMod'];
                $rssi     = $row['mhRssi'];
                $snr      = $row['mhSnr'];
                $dist     = $row['mhDist'];
                $pl       = $row['mhPl'];
                $m        = $row['mhM'];

                if ($drawHeader === true)
                {
                    echo "<br>";
                    echo "<br>";

                    echo '<table class="table">';

                    echo '<tr>';
                    echo '<th colspan="10" class="thCenter">Letzte gespeicherte Mheard-Liste (' . $localCallSign . ') vom ' . $timeStamp . '</th>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<th colspan="10" ><hr></th>';
                    echo '</tr>';

                    echo '<tr>';
                    echo '<th>MHeard-Call</th>';
                    echo '<th>Date</th>';
                    echo '<th>Time</th>';
                    echo '<th>Type</th>';
                    echo '<th>Hardware</th>';
                    echo '<th>Mod</th>';
                    echo '<th>RSSI</th>';
                    echo '<th>SNR</th>';
                    echo '<th>DIST</th>';

                    if ($pl != '' & $m != '')
                    {
                        echo '<th>PL</th>';
                        echo '<th>M</th>';
                    }
                    echo '</tr>';

                    $drawHeader = false;
                }

                echo '<tr>';
                echo '<td>'.$callSign.'</td>';
                echo '<td>'.$date.'</td>';
                echo '<td>'.$time.'</td>';
                echo '<td>'.$type.'</td>';
                echo '<td>'.$hardware.'</td>';
                echo '<td>'.$mod.'</td>';
                echo '<td>'.$rssi.'</td>';
                echo '<td>'.$snr.'</td>';
                echo '<td>'.$dist.'</td>';
                if ($pl != '' & $m != '')
                {
                    echo '<td>' . $pl . '</td>';
                    echo '<td>' . $m . '</td>';
                }
                echo '</tr>';
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

function getOwnPosition($callSign)
{
    $returnArray = array();
    $debugFlag   = false;

    $dbMd = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
    $dbMd->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    // Hole mir die pos-Daten aus der Datenbank
    $resultMdOwn    = $dbMd->query("SELECT latitude, longitude 
                                         FROM meshdash
                                        WHERE src = '$callSign'
                                          AND type = 'pos'
                                     ORDER BY timestamps DESC
                                        LIMIT 1;
                                            ");
    $dsDataMdOwn    = $resultMdOwn->fetchArray(SQLITE3_ASSOC);
    $validDataMdOwn = !empty($dsDataMdOwn);

    if ($validDataMdOwn)
    {
        $returnArray['latitude']   = substr($dsDataMdOwn['latitude'],0,7);
        $returnArray['longitude']  = substr($dsDataMdOwn['longitude'],0,6);

        if ($debugFlag === true)
        {
            echo "<pre>";
            print_r($returnArray);
            echo "</pre>";
        }

        return $returnArray;
    }

    return false;
}

function validateMheardValue(string $value, string $type): bool
{
    switch ($type)
    {
        case 'isoDate':
            // yyyy-mm-dd oder yyyy-mm-ddThh:mm:ss
            return preg_match('/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}:\d{2})?$/', $value) === 1;

        case 'time':
            // HH:MM oder HH:MM:SS
            return preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value) === 1;

        case 'float':
            // Mit Punkt oder Komma, optional negativ
            return preg_match('/^-?\d+[.,]\d+$/', $value) === 1;

        case 'integer':
            // Ganze Zahl, auch negativ
            return preg_match('/^-?\d+$/', $value) === 1;

        default:
            return false;
    }
}
