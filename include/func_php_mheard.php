<?php

function getMheard($loraIp)
{
    // Array, um die Daten zu speichern
    $heardData = [];

    // URL der Remote-Seite
    $actualHost = 'http';
    $url        = $actualHost . '://' . $loraIp . '/mheard';

    // Holen des HTML-Inhalts von der Remote-Seite
    $htmlContent = @file_get_contents($url);

    if ($htmlContent === false)
    {
        echo "<br>Keine Daten zu finden unter der Url:" . $url;
        exit();
    }

    // Initialisieren des DOMDocuments
    $doc = new DOMDocument();
    libxml_use_internal_errors(true); // Fehler unterdrücken
    $doc->loadHTML($htmlContent);
    libxml_clear_errors();

    // Suchen nach der Tabelle mit den relevanten Daten
    $tableRows = $doc->getElementsByTagName('tr');

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
        }
    }

    if (count($heardData) > 0)
    {
        setMheardData($heardData);
    }

    if (count($heardData) == 0)
    {
        echo '<h3>Keine MHeard-Daten gefunden.';
        echo '<br>Zeige zuletzt gespeicherte Werte wenn vorhanden.</br>';
        return false;
    }

    return true;
}

function showMheard($localCallSign)
{
    $db = new SQLite3('database/mheard.db', SQLITE3_OPEN_READONLY);
    $db->busyTimeout(5000); // warte wenn busy in millisekunden

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
            echo '<th>PL</th>';
            echo '<th>M</th>';
            echo '</tr>';

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
                echo '<td>'.$pl.'</td>';
                echo '<td>'.$m.'</td>';

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
    $dbMd->busyTimeout(5000); // warte wenn busy in millisekunden

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
