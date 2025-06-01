<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

#Wenn Datei nicht existiert, dann exit.
#Verhindert das ein offener Browser eine 0 byte Datei erzeugt
if (!file_exists('database/mheard.db') || !file_exists('database/meshdash.db'))
{
    exit();
}

$debugFlag   = false;
$returnArray = array();
$ownCallSign = getParamData('callSign');

$db = new SQLite3('database/mheard.db', SQLITE3_OPEN_READONLY);
$db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

$sql = "SELECT max(timestamps) AS timestamps 
          FROM mheard;
       ";

$logArray   = array();
$logArray[] = "ajax_mheard_sql1: Database: database/mheard.db";

$result = safeDbRun($db, $sql, 'query', $logArray);

if ($result === false)
{
    #Close and write Back WAL
    $db->close();
    unset($db);

   exit();
}

$dsData = $result->fetchArray(SQLITE3_ASSOC);

if (!empty($dsData) === true)
{
    $timeStamp = $dsData['timestamps'];

    $sqlMh = "SELECT *             
                FROM mheard
               WHERE timestamps = '$timeStamp'
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
        echo "<br>timeStamp:$timeStamp";
        echo "<br>sqlMh:$sqlMh";
        echo "<br>resultMh:";
        var_dump($resultMh);
    }

    if ($resultMh !== false)
    {
        while ($row = $resultMh->fetchArray(SQLITE3_ASSOC))
        {
            ###############################################
            #Common
            $callSign = $row['mhCallSign'];
            $type     = $row['mhType'];
            $hardware = $row['mhHardware'];
            $mod      = $row['mhMod'];
            $rssi     = $row['mhRssi'];
            $snr      = $row['mhSnr'];
            $dist     = $row['mhDist'];
            $pl       = $row['mhPl'];
            $m        = $row['mhM'];

            $dbMd1 = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
            $dbMd1->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

            // Hole mir die pos-Daten aus der Datenbank

            $sqlMd1 = "SELECT * 
                         FROM meshdash
                        WHERE src = '$callSign'
                          AND type = 'pos'
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
                $returnArray[$callSign]['latitude']   = substr($dsDataMd1['latitude'],0,7);
                $returnArray[$callSign]['longitude']  = substr($dsDataMd1['longitude'],0,6);
                $returnArray[$callSign]['altitude']   = number_format($dsDataMd1['altitude'] * 0.3048); // Umrechnung Fuss -> Meter;
                $returnArray[$callSign]['firmware']   = $dsDataMd1['firmware'];
                $returnArray[$callSign]['fw_sub']     = $dsDataMd1['fw_sub'];
                $returnArray[$callSign]['batt']       = $dsDataMd1['batt'];
                $returnArray[$callSign]['dist']       = substr($dist,0,5);
                $returnArray[$callSign]['callSign']   = $callSign;
                $returnArray[$callSign]['hardware']   = $hardware;
                $returnArray[$callSign]['rssi']       = $rssi;

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

            #Close and write Back WAL
            $dbMd1->close();
            unset($dbMd1);
        }
    }

    ########### Own Pos

    $dbMd2 = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
    $dbMd2->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

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
        $returnArray[$ownCallSign]['timestamps'] = $dsDataMdOwn['timestamps'];
        $returnArray[$ownCallSign]['latitude']   = substr($dsDataMdOwn['latitude'],0,7);
        $returnArray[$ownCallSign]['longitude']  = substr($dsDataMdOwn['longitude'],0,6);
        $returnArray[$ownCallSign]['altitude']   = number_format($dsDataMdOwn['altitude'] * 0.3048); // Umrechnung Fuss -> Meter;
        $returnArray[$ownCallSign]['firmware']   = $dsDataMdOwn['firmware'];
        $returnArray[$ownCallSign]['fw_sub']     = $dsDataMdOwn['fw_sub'];
        $returnArray[$ownCallSign]['batt']       = $dsDataMdOwn['batt'];
        $returnArray[$ownCallSign]['dist']       = substr($dsDataMdOwn['dist'],0,5);
        $returnArray[$ownCallSign]['callSign']   = $ownCallSign;

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