<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

#Wenn Datei nicht existiert, dann exit.
#Verhindert das ein offener Browser eine 0 byte Datei erzeugt
if (!file_exists('database/mheard.db') || !file_exists('database/meshdash.db'))
{
    exit();
}

$debugFlag        = false;
$returnArray      = array();
$ownCallSign      = getParamData('callSign');
$localMheardCalls = array();
$dateFrom         = $_POST['dateFrom'] ?? date('Y-m-d', strtotime('-7 days'));
$dateTo           = $_POST['dateTo'] ?? date('Y-m-d');

// Für die Abfrage das Datum auf den gesamten Tag erweitern
$from = $dateFrom . ' 00:00:00';
$to   = $dateTo   . ' 23:59:59';

$db = new SQLite3('database/mheard.db', SQLITE3_OPEN_READONLY);
$db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden

$sqlMh = "SELECT *             
            FROM mheard
           WHERE timestamps between '$from' AND '$to'
        ORDER BY mhTime DESC;
        ";

$logArray   = array();
$logArray[] = "ajax_mheard_sqlMh: Database: database/mheard.db";

$resultMh = safeDbRun($db, $sqlMh, 'query', $logArray);

if ($resultMh === false)
{
    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($debugFlag === true)
    {
        echo "<br>peng0";
    }

    exit();
}

if ($debugFlag === true)
{
    echo "<br>#74#resultMh Select 1#<br>";
    echo "<br>from:$from";
    echo "<br>to:$to";
    echo "<br>sqlMh:$sqlMh";
    echo "<br>resultMh:";
    var_dump($resultMh);
}

if ($resultMh !== false)
{

    $dbMd1 = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
    $dbMd1->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden

    while ($row = $resultMh->fetchArray(SQLITE3_ASSOC))
    {
        ###############################################
        #Common
        $callSign = $row['mhCallSign'] ?? 0;
        $type     = $row['mhType'] ?? 0;
        $hardware = $row['mhHardware'] ?? 0;
        $mod      = $row['mhMod'] ?? 0;
        $rssi     = $row['mhRssi'] ?? 0;
        $snr      = $row['mhSnr'] ?? 0;
        $dist     = $row['mhDist'] ?? 0;
        $pl       = $row['mhPl'] ?? 0;
        $m        = $row['mhM'] ?? 0;

        $localMheardCalls[$callSign] = $localMheardCalls[$callSign] ?? '';

        if ($callSign === $localMheardCalls[$callSign])
        {
            continue;
        }

        $localMheardCalls[$callSign] = $callSign;

        // Hole mir die pos-Daten aus der Datenbank
        $sqlMd1 = "SELECT * 
                     FROM meshdash
                    WHERE src = '$callSign'
                      AND type = 'pos'
                      AND timestamps between '$from' AND '$to'
                 ORDER BY timestamps DESC
                    LIMIT 1;
                 ";

        $logArray   = array();
        $logArray[] = "ajax_mheard_sqlMd1: Database: database/mheard.db";

        $resultMd1 = safeDbRun($dbMd1, $sqlMd1, 'query', $logArray);

        if ($resultMd1 === false)
        {
            #Close and write Back WAL
            $dbMd1->close();
            unset($dbMd1);

            if ($debugFlag === true)
            {
                echo "<br>Peng! resultMd = false";
            }

            exit();
        }

        $dsDataMd1 = $resultMd1->fetchArray(SQLITE3_ASSOC);

        if ($debugFlag === true)
        {
            echo "<br>sqlmd1: sqlMd1:<br>$sqlMd1";
            echo "<br>dsDataMd1:";
            var_dump($dsDataMd1);
        }

        if (!empty($dsDataMd1) === true)
        {
            $returnArray[$callSign]['timestamps'] = $dsDataMd1['timestamps'];
            $returnArray[$callSign]['latitude']   = substr($dsDataMd1['latitude'] ?? 0,0,7);
            $returnArray[$callSign]['longitude']  = substr($dsDataMd1['longitude'] ?? 0,0,6);
            $returnArray[$callSign]['altitude']   = number_format((($dsDataMd1['altitude'] ?? 0) * 0.3048)); // Umrechnung von Fuss -> Meter;
            $returnArray[$callSign]['firmware']   = $dsDataMd1['firmware'] ?? 0;
            $returnArray[$callSign]['fw_sub']     = $dsDataMd1['fw_sub'] ?? 0;
            $returnArray[$callSign]['batt']       = $dsDataMd1['batt'] ?? 0;
            $returnArray[$callSign]['dist']       = substr($dist,0,5);
            $returnArray[$callSign]['callSign']   = $callSign;
            $returnArray[$callSign]['hardware']   = $hardware;
            $returnArray[$callSign]['rssi']       = $rssi;
            $returnArray[$callSign]['snr']        = $snr;
            $returnArray[$callSign]['range']      = 'local';

            if ($debugFlag === true)
            {
                echo "<pre>";
                print_r($returnArray);
                echo "</pre>";

                echo "<br>#74#resultMh#";
                echo "<br>callSign:$callSign";
                echo "<br>---------------------------";
            }
        }
    }

    #Close and write Back WAL
    $dbMd1->close();
    unset($dbMd1);
}

########### Own Pos

$dbMd2 = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
$dbMd2->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden

$sqlMd2 = "SELECT * 
             FROM meshdash
            WHERE src = '$ownCallSign'
              AND type = 'pos'
         ORDER BY timestamps DESC
            LIMIT 1;
                ";

$logArray   = array();
$logArray[] = "ajax_mheard_sqlMd2: Database: database/mheard.db";

$resultMdOwn = safeDbRun($dbMd2, $sqlMd2, 'query', $logArray);

if ($resultMdOwn === false)
{
    #Close and write Back WAL
    $dbMd2->close();
    unset($dbMd2);

    exit();
}

$dsDataMdOwn = $resultMdOwn->fetchArray(SQLITE3_ASSOC);

if (!empty($dsDataMdOwn) === true)
{
    $returnArray[$ownCallSign]['timestamps'] = $dsDataMdOwn['timestamps'] ?? 0;
    $returnArray[$ownCallSign]['latitude']   = substr($dsDataMdOwn['latitude'] ?? 0,0,7);
    $returnArray[$ownCallSign]['longitude']  = substr($dsDataMdOwn['longitude'] ?? 0,0,6);
    $returnArray[$ownCallSign]['altitude']   = number_format((($dsDataMdOwn['altitude'] ?? 0) * 0.3048)); // Umrechnung Fuss -> Meter;
    $returnArray[$ownCallSign]['firmware']   = $dsDataMdOwn['firmware'] ?? 0;
    $returnArray[$ownCallSign]['fw_sub']     = $dsDataMdOwn['fw_sub'] ?? 0;
    $returnArray[$ownCallSign]['batt']       = $dsDataMdOwn['batt'] ?? 0;
    $returnArray[$ownCallSign]['dist']       = substr($dsDataMdOwn['dist'] ?? 0,0,5);
    $returnArray[$ownCallSign]['callSign']   = $ownCallSign;
    $returnArray[$ownCallSign]['range']      = 'own';

    if ($debugFlag === true)
    {
        echo "<pre>";
        print_r($dsDataMdOwn);
        echo "</pre>";

        echo "<br>#74#resultMh#";
        echo "<br>ownCallSign:$ownCallSign";
        echo "<br>---------------------------";
    }
}

#Close and write Back WAL
$dbMd2->close();
unset($dbMd2);

### Heard via Path
###########
$enablePathNodes = true;

if ($enablePathNodes === true)
{
    $dbMd3 = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
    $dbMd3->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden

    $sqlMd3 = "SELECT m.src,
                  m.timestamps, 
                  m.latitude,
                  m.longitude,
                  m.altitude,
                  m.firmware,
                  m.fw_sub,
                  m.batt,
                  m.hw_id,
                  '0' AS dist,
                  substr(m.src, 1, instr(m.src, ',') - 1) AS ziel,
                   trim(
                       substr(
                           m.src,
                           length(rtrim(m.src, replace(m.src, ',', ''))) + 1
                       )
                   ) AS repeater,
                   m.timestamps
              FROM meshdash m
              JOIN (
                    SELECT substr(src, 1, instr(src, ',') - 1) AS ziel,
                           MAX(timestamps) AS max_ts
                      FROM meshdash
                     WHERE instr(src, ',') > 0
                       AND timestamps between '$from' AND '$to'
                  GROUP BY ziel
                ) latest
              ON latest.ziel = substr(m.src, 1, instr(m.src, ',') - 1)
             AND latest.max_ts = m.timestamps
           WHERE instr(m.src, ',') > 0
             AND m.timestamps between '$from' AND '$to';
                ";

    $logArray   = array();
    $logArray[] = "ajax_mheard_sqlMd3: Database: database/mheard.db";

    $resultMdPath = safeDbRun($dbMd3, $sqlMd3, 'query', $logArray);

    if ($resultMdPath === false)
    {
        #Close and write Back WAL
        $dbMd3->close();
        unset($dbMd3);

        exit();
    }

    if ($resultMh !== false)
    {
        while ($dsDataMdPath = $resultMdPath->fetchArray(SQLITE3_ASSOC))
        {
            if (!empty($dsDataMdPath) === true)
            {
                $pathCallSign = $dsDataMdPath['ziel'] ?? 0;

                if (in_array($pathCallSign, $localMheardCalls) === true)
                {
                    continue;
                }

                $returnArray[$pathCallSign]['timestamps'] = $dsDataMdPath['timestamps'] ?? 0;
                $returnArray[$pathCallSign]['latitude']   = substr($dsDataMdPath['latitude'] ?? 0, 0, 7);
                $returnArray[$pathCallSign]['longitude']  = substr($dsDataMdPath['longitude'] ?? 0, 0, 6);
                $returnArray[$pathCallSign]['altitude']   = number_format((((float) $dsDataMdPath['altitude'] ?? 0) * 0.3048)); // Umrechnung Fuss -> Meter;
                $returnArray[$pathCallSign]['firmware']   = $dsDataMdPath['firmware'] ?? 0;
                $returnArray[$pathCallSign]['fw_sub']     = $dsDataMdPath['fw_sub'] ?? 0;
                $returnArray[$pathCallSign]['batt']       = $dsDataMdPath['batt'] ?? 0;
                $returnArray[$pathCallSign]['dist']       = substr($dsDataMdPath['dist'] ?? 0, 0, 5);
                $returnArray[$pathCallSign]['callSign']   = $pathCallSign;
                $returnArray[$pathCallSign]['repeater']   = $dsDataMdPath['repeater'] ?? 0;
                $returnArray[$pathCallSign]['range']      = 'path';
                $returnArray[$pathCallSign]['hwId']       = $dsDataMdPath['hw_id'] ?? 0;

                $returnArray[$pathCallSign]['path'] = $dsDataMdPath['src'] ?? 0;
                $returnArray[$pathCallSign]['connection'] = count(explode(',', $dsDataMdPath['src'])) === 2 ? 'direct' : 'indirect';

                if ($debugFlag === true)
                {
                    echo "<pre>";
                    print_r($dsDataMdPath);
                    echo "</pre>";

                    echo "<br>#74#resultMh#";
                    echo "<br>pathCallSign:$pathCallSign";
                    echo "<br>---------------------------";
                }
            }
        }
    }

    #Close and write Back WAL
    $dbMd3->close();
    unset($dbMd3);
}


#Close and write Back WAL
$db->close();
unset($db);

if ($debugFlag === false)
{
    // JSON-Ausgabe für das Frontend
    header('Content-Type: application/json');
}
else
{
    echo "<br>";
}

echo json_encode($returnArray);