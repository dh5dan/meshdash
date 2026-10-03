<?php
/**
 * @throws Exception
 */
function getMheard($loraIp): bool
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
    $tableRows          = $doc->getElementsByTagName('tr');
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

    callAjaxMheard();

    return true;
}

/**
 * @throws Exception
 */
function getMheard2($loraIp): bool
{
    // Array, um die Daten zu speichern
    $heardData = [];

    // URL der Remote-Seite
    $actualHost = 'http';
    $url        = $actualHost . '://' . $loraIp . '/?page=mheard';

    #Prüfe ob FW >= 4.40 ist da hier Mheard im Aufbau geändert wurde.
    #Neuer Parser erforderlich
    $isNewMheardGui = getParamData('isNewMheardGui');

    // Holen des HTML-Inhalts von der Remote-Seite
    $htmlContent = @file_get_contents($url);

    if ($htmlContent === false)
    {
        echo '<br><span class="failureHint">Keine Daten zu finden unter der Url: ' . $url . '</span>';
       return false;
    }

    // Neuer MHeard-Parser für FW >=4.40 mit neuer GUI
    if ((int)$isNewMheardGui === 1)
    {
        return parseMheardNew($htmlContent);
    }

    // Initialisieren des DOMDocuments
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($htmlContent);
    libxml_clear_errors();

    // Alle Divs mit class="cardlayout" erfassen
    $cards = $doc->getElementsByTagName('div');
    $mheardValueIsValid = false;

    foreach ($cards as $card)
    {
        if ($card->getAttribute('class') === 'cardlayout')
        {

            $label = $card->getElementsByTagName('label')->item(0);
            if (!$label)
            {
                continue;
            }

            // Rufzeichen aus dem <a>-Element
            $a        = $label->getElementsByTagName('a')->item(0);
            $callSign = $a ? trim($a->nodeValue) : '';

            // Zeitstempel aus dem <span class="font-small">
            $span     = $label->getElementsByTagName('span')->item(0);
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

            for ($i = 0; $i < $spans->length; $i++)
            {
                $span  = $spans->item($i);
                $class = $span->getAttribute('class');

                if (str_contains($class, 'font-bold'))
                {
                    $key = trim(str_replace(':', '', $span->nodeValue));

                    // Eltern-<div> ermitteln
                    $parentDiv = $span->parentNode;

                    // Zwei <span> in dem Block (key und value)
                    $innerSpans = $parentDiv->getElementsByTagName('span');
                    if ($innerSpans->length < 2)
                    {
                        continue;
                    }

                    $val = trim($innerSpans->item(1)->nodeValue);

                    switch (strtolower($key))
                    {
                        case 'type':
                            $entry['mhType'] = $val;
                            break;
                        case 'hardware':
                            $entry['hardware'] = $val;
                            break;
                        case 'mod':
                            $entry['mod'] = $val;
                            break;
                        case 'rssi':
                            $entry['rssi'] = $val;
                            break;
                        case 'snr':
                            $entry['snr'] = $val;
                            break;
                        case 'dist':
                            $entry['dist'] = $val;
                            break;
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
            echo '<br><span class="failureHint">Fehler bei Eintrag '
                . $index . ': Ungültiges Datum: ' . $entry['date'] . '</span>';
        }

        if (!validateMheardValue($entry['time'], 'time'))
        {
            $mheardValueIsValid = false;
            echo '<br><span class="failureHint">Fehler bei Eintrag '
                . $index . ': Ungültige Zeit: ' . $entry['time'] . '</span>';
        }

        if (!validateMheardValue($entry['dist'], 'float'))
        {
            $mheardValueIsValid = false;
            echo '<br><span class="failureHint">Fehler bei Eintrag '
                . $index . ': Ungültige Distanz: ' . $entry['dist'] . '</span>';
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
                echo '<br><span class="failureHint">Fehler bei Eintrag '
                    . $index . ': Ungültiger RSSI: ' . $entry['rssi'] . '</span>';
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

    callAjaxMheard();

    return true;
}
function showMheard($localCallSign): bool
{
    $db = new SQLite3('database/mheard.db', SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    // Hole mir den Timestamp der letzten importierten Mheard Liste aus der Datenbank
    $sql = "SELECT max(timestamps) AS timestamps 
              FROM mheard;
           ";

    $logArray   = array();
    $logArray[] = "showMheard: Database: database/mheard.db";
    $logArray[] = "showMheard: Error at:" . date('Y-m-d H:i:s');

    $result = safeDbRun($db, $sql, 'query', $logArray);

    if ($result === false)
    {
        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    $isNewMheardGui = (int) getParamData('isNewMheardGui');
    $dsData         = $result->fetchArray(SQLITE3_ASSOC);

    if (!empty($dsData))
    {
        $timeStamp = $dsData['timestamps'];

        $sqlMh = "SELECT * 
                    FROM mheard
                   WHERE timestamps = '$timeStamp'
                ORDER BY mhTime DESC;
                        ";

        $logArray[] = "showMheard_ts: Database: database/mheard.db";
        $logArray[] = "showMheard_ts: Error at:" . date('Y-m-d H:i:s');

        $resultMh = safeDbRun($db, $sqlMh, 'query', $logArray);

        if ($resultMh === false)
        {
            #Close and write Back WAL
            $db->close();
            unset($db);

            return false;
        }

        if ($resultMh !== false)
        {
            $drawHeader = true;

            while ($row = $resultMh->fetchArray(SQLITE3_ASSOC))
            {
                ###############################################
                #Common
                $callSign    = $row['mhCallSign'] ?? '';
                $date        = $row['mhDate'] ?? '';
                $time        = $row['mhTime'] ?? '';
                $type        = $row['mhType'] ?? '';
                $hardware    = $row['mhHardware'] ?? '';
                $mod         = $row['mhMod'] ?? '';
                $rssi        = $row['mhRssi'] ?? '';
                $snr         = $row['mhSnr'] ?? '';
                $dist        = $row['mhDist'] ?? '0.0';
                $pl          = $row['mhPl'] ?? '';
                $m           = $row['mhM'] ?? '';
                $lastHeard   = $row['lastHeard'] ?? '';
                $hearsMe     = $row['hearsMe'] ?? '';
                $relayRole   = $row['relayRole'] ?? '';
                $onlyItHears = $row['onlyItHears'] ?? '';
                $itHears     = $row['itHears'] ?? '';
                $itReports   = $row['itReports'] ?? '';
                $gateway     = $row['gateway'] ?? '';
                $lastFrame   = $row['lastFrame'] ?? '';


                if ($isNewMheardGui === 0)
                {
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
                    echo '<td class="mhCallColor">' . $callSign . '</td>';
                    echo '<td>' . $date . '</td>';
                    echo '<td>' . $time . '</td>';
                    echo '<td>' . $type . '</td>';
                    echo '<td>' . $hardware . '</td>';
                    echo '<td>' . $mod . '</td>';
                    echo '<td>' . $rssi . '</td>';
                    echo '<td>' . $snr . '</td>';
                    echo '<td>' . $dist . '</td>';
                    if ($pl != '' & $m != '')
                    {
                        echo '<td>' . $pl . '</td>';
                        echo '<td>' . $m . '</td>';
                    }
                    echo '</tr>';
                }
                else
                {
                    if ($drawHeader === true)
                    {
                        echo "<br>";
                        echo "<br>";

                        echo '<table class="table">';

                        echo '<tr>';
                        echo '<th colspan="10" class="thCenter">Letzte gespeicherte Mheard-Liste ('
                            . $localCallSign . ') vom '
                            . $timeStamp . '</th>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<th colspan="10" ><hr></th>';
                        echo '</tr>';

                        $drawHeader = false;
                    }

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

                echo '<tr>';
                echo '<td class="mhCallColor">' . $callSign . '</td>';
                echo '<td>' . $date . '</td>';
                echo '<td>' . $time . '</td>';
                echo '<td>' . $type . '</td>';
                echo '<td>' . $hardware . '</td>';
                echo '<td>' . $mod . '</td>';
                echo '<td>' . $rssi . '</td>';
                echo '<td>' . $snr . '</td>';
                echo '<td>' . $dist . '</td>';
                if ($pl != '' & $m != '')
                {
                    echo '<td>' . $pl . '</td>';
                    echo '<td>' . $m . '</td>';
                }
                echo '</tr>';

                echo '<tr>';
                echo '<th>Last Heard</th>';
                echo '<th>Hears Me</th>';
                echo '<th>Relay Role</th>';
                echo '<th>Only it Hears</th>';
                echo '<th>It Hears</th>';
                echo '<th>It Reports</th>';
                echo '<th>Gateway</th>';
                echo '</tr>';

                echo '<tr>';
                echo '<td>' . $lastHeard . '</td>';
                echo '<td>' . $hearsMe . '</td>';
                echo '<td>' . $relayRole . '</td>';
                echo '<td>' . $onlyItHears . '</td>';
                echo '<td>' . $itHears . '</td>';
                echo '<td>' . $itReports . '</td>';
                echo '<td>' . $gateway . '</td>';
                echo '</tr>';

                echo '<th colspan="10" ><hr></th>';
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

    return true;
}
function getOwnPosition($callSign): bool|array
{
    $returnArray = array();
    $debugFlag   = false;

    # DB-Pfad ermitteln
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/meshdash.db';
    $dbFilenameRoot = 'database/meshdash.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $dbMd = new SQLite3($dbFilename, SQLITE3_OPEN_READONLY);
    $dbMd->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    // Hole mir die pos-Daten aus der Datenbank
    $sqlMd = "SELECT latitude, longitude 
                FROM meshdash
               WHERE src = '$callSign'
                 AND type = 'pos'
            ORDER BY timestamps DESC
               LIMIT 1;
            ";

    $logArray   = array();
    $logArray[] = "getOwnPosition: Database: database/meshdash.db";
    $logArray[] = "getOwnPosition: callSign: $callSign";
    $logArray[] = "getOwnPosition: Error at:" . date('Y-m-d H:i:s');

    $resultMdOwn = safeDbRun($dbMd, $sqlMd, 'query', $logArray);

    if ($resultMdOwn === false)
    {
        #Close and write Back WAL
        $dbMd->close();
        unset($dbMd);

        return false;
    }

    $dsDataMdOwn = $resultMdOwn->fetchArray(SQLITE3_ASSOC);

    #Close and write Back WAL
    $dbMd->close();
    unset($dbMd);

    if (!empty($dsDataMdOwn) === true)
    {
        $returnArray['latitude']  = (float) $dsDataMdOwn['latitude'];
        $returnArray['longitude'] = (float) $dsDataMdOwn['longitude'];

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
function setMheardData($heardData): bool
{
    #Ermittle Aufrufpfad, um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/mheard.db';
    $dbFilenameRoot = 'database/mheard.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $mhTimeStamps   = date('Y-m-d H:i:s');

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');

    foreach ($heardData AS $key)
    {
        $callSign    = SQLite3::escapeString($key['callSign'] ?? '');
        $date        = SQLite3::escapeString($key['date'] ?? '');
        $time        = SQLite3::escapeString($key['time'] ?? '');
        $mhType      = SQLite3::escapeString($key['mhType'] ?? '');
        $hardware    = SQLite3::escapeString($key['hardware'] ?? '');
        $mod         = SQLite3::escapeString($key['mod'] ?? '');
        $rssi        = SQLite3::escapeString($key['rssi'] ?? '');
        $snr         = SQLite3::escapeString($key['snr'] ?? '');
        $dist        = SQLite3::escapeString($key['dist'] ?? '');
        $pl          = SQLite3::escapeString($key['pl'] ?? '');
        $m           = SQLite3::escapeString($key['m'] ?? '');
        $lastHeard   = SQLite3::escapeString($key['lastHeard'] ?? '');
        $hearsMe     = SQLite3::escapeString($key['hearsMe'] ?? '');
        $relayRole   = SQLite3::escapeString($key['relayRole'] ?? '');
        $onlyItHears = SQLite3::escapeString($key['onlyItHears'] ?? '');
        $itHears     = SQLite3::escapeString($key['itHears'] ?? '');
        $itReports   = SQLite3::escapeString($key['itReports'] ?? '');
        $gateway     = SQLite3::escapeString($key['gateway'] ?? '');
        $lastFrame   = SQLite3::escapeString($key['lastFrame'] ?? '');

        $sql = "REPLACE INTO mheard (timestamps, 
                                     mhCallSign, 
                                     mhDate, 
                                     mhTime, 
                                     mhType,
                                     mhHardware, 
                                     mhMod, 
                                     mhRssi, 
                                     mhSnr, 
                                     mhDist, 
                                     mhPl, 
                                     mhM,
                                     lastHeard,
                                     hearsMe,
                                     relayRole,
                                     onlyItHears,
                                     itHears,
                                     itReports,
                                     gateway,
                                     lastFrame
                                    )
                             VALUES ('$mhTimeStamps',
                                     '$callSign',
                                     '$date',
                                     '$time',
                                     '$mhType',
                                     '$hardware',
                                     '$mod',
                                     '$rssi',
                                     '$snr',
                                     '$dist',
                                     '$pl',
                                     '$m',
                                     '$lastHeard',
                                     '$hearsMe',
                                     '$relayRole',
                                     '$onlyItHears',
                                     '$itHears',
                                     '$itReports',
                                     '$gateway',
                                     '$lastFrame'
                                    );
                    ";

        $logArray   = array();
        $logArray[] = "setMheardData: Database: $dbFilename";

        $res = safeDbRun( $db,  $sql, 'exec', $logArray);

        if ($res === false)
        {
            #Close and write Back WAL
            $db->close();
            unset($db);

            return false;
        }
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return true;
}

/**
 * @throws Exception
 */
function parseMheardNew($htmlContent): bool
{
    $heardData          = [];
    $mheardValueIsValid = false;
    $debugFlag          = false;

    // DOM initialisieren
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($htmlContent);
    libxml_clear_errors();

    // Alle cardlayout-Blöcke erfassen
    $cards = $doc->getElementsByTagName('div');

    foreach ($cards as $card)
    {
        if ($card->getAttribute('class') !== 'cardlayout')
        {
            continue;
        }

        // Rufzeichen und Zeitstempel
        $label = $card->getElementsByTagName('label')->item(0);

        if (!$label)
        {
            continue;
        }

        $a        = $label->getElementsByTagName('a')->item(0);
        $callSign = $a ? trim($a->nodeValue) : '';

        $span     = $label->getElementsByTagName('span')->item(0);
        $datetime = $span ? trim($span->nodeValue) : '';

        // Datum & Zeit extrahieren
        preg_match(
            '/\((\d{4})\.(\d{2})\.(\d{2}) (\d{2}:\d{2}:\d{2})\)/',
            $datetime,
            $matches
        );

        $date = '';

        if (!empty($matches))
        {
            $date = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        }

        $time = $matches[4] ?? '';

        // Gleiche Datenstruktur wie bisher
        $entry = [
            'callSign'    => $callSign,
            'date'        => $date,
            'time'        => $time,
            'mhType'      => '',
            'hardware'    => '',
            'mod'         => '',
            'rssi'        => 0,
            'snr'         => '',
            'dist'        => 0,
            'lat'         => '',
            'lon'         => '',
            'alt'         => '',
            'lastHeard'   => '',
            'hearsMe'     => '',
            'relayRole'   => '',
            'onlyItHears' => '',
            'itHears'     => '',
            'itReports'   => '',
            'gateway'     => '',
            'lastFrame'   => '',
        ];

        // Werte aus den einzelnen div-Blöcken lesen
        $divs = $card->getElementsByTagName('div');

        foreach ($divs as $div)
        {
            $spans = $div->getElementsByTagName('span');

            if ($spans->length < 2)
            {
                continue;
            }

            $key = trim(str_replace(':', '', $spans->item(0)->nodeValue));
            $val = trim($spans->item(1)->nodeValue);

            if ($debugFlag === true)
            {
                echo '<br>KEY=[' . $key . '] VALUE=[' . $val . ']';
            }

            switch (strtolower($key))
            {
                case 'last frame':

                    $entry['lastFrame'] = $val;

                    $entry['mhType'] = match ($val)
                    {
                        'Position' => 'POS',
                        'Heartbeat (HEY)' => 'HEY',
                        'Text message' => 'TXT',
                        default => $val,
                    };

                    break;

                case 'hardware':
                    $entry['hardware'] = $val;
                    break;

                case 'country / mode':
                    $entry['mod'] = $val;
                    break;

                case 'rssi':
                    $entry['rssi'] = intval($val);
                    break;

                case 'snr (avg)':
                    $entry['snr'] = $val;
                    break;

                case 'distance':
                    $entry['dist'] = (float)preg_replace('/[^0-9.]/', '', $entry['dist']);
                    $entry['dist'] = number_format($entry['dist'], 1, '.', '');
                    break;

                case 'lat':
                    $entry['lat'] = $val;
                    break;

                case 'lon':
                    $entry['lon'] = $val;
                    break;

                case 'altitude':
                    $entry['alt'] = $val;
                    break;

                case 'last heard':
                    $entry['lastHeard'] = $val;
                    break;

                case 'hears me':
                    $entry['hearsMe'] = $val;
                    break;

                case 'relay role':
                    $entry['relayRole'] = $val;
                    break;

                case 'only it hears':
                    $entry['onlyItHears'] = $val;
                    break;

                case 'it hears':
                    $entry['itHears'] = $val;
                    break;

                case 'it reports':
                    $entry['itReports'] = $val;
                    break;

                case 'gateway':
                    $entry['gateway'] = $val;
                    break;
            }
        }

        $heardData[] = $entry;
    }

    if ($debugFlag === true)
    {
        // Nur zum Testen
        echo '<pre>';
        print_r($heardData);
        echo '</pre>';
    }

    foreach ($heardData as $index => $entry)
    {
        $mheardValueIsValid = true;

        if (!validateMheardValue($entry['date'], 'isoDate'))
        {
            $mheardValueIsValid = false;
            echo '<br><span class="failureHint">Fehler bei Eintrag '
                . $index . ': Ungültiges Datum: ' . $entry['date'] . '</span>';
        }

        if (!validateMheardValue($entry['time'], 'time'))
        {
            $mheardValueIsValid = false;
            echo '<br><span class="failureHint">Fehler bei Eintrag '
                . $index . ': Ungültige Zeit: ' . $entry['time'] . '</span>';
        }

        if (!validateMheardValue($entry['dist'], 'float'))
        {
            $mheardValueIsValid = false;
            echo '<br><span class="failureHint">Fehler bei Eintrag '
                . $index . ': Ungültige Distanz: ' . $entry['dist'] . '</span>';
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
                echo '<br><span class="failureHint">Fehler bei Eintrag '
                    . $index . ': Ungültiger RSSI: ' . $entry['rssi'] . '</span>';
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
            echo '<span class="failureHint">Fehlerhafte Mheard-Daten vom Node empfangen. FW >= v4.40 ?X</span>';
            return false;
        }
    }

    if (count($heardData) == 0)
    {
        echo '<h3>Keine MHeard-Daten gefunden.';
        echo '<br>Zeige zuletzt gespeicherte Werte wenn vorhanden.</br></h3>';
        return false;
    }

    callAjaxMheard();

    return true;
}