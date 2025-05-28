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

// Hole mir die letzten 30 Nachrichten aus der Datenbank
$result    = $db->query("SELECT max(timestamps) AS timestamps FROM mheard;");
$dsData    = $result->fetchArray(SQLITE3_ASSOC);
$validData = !empty($dsData);

if ($validData)
{
    $timeStamp = $dsData['timestamps'];

    if ($debugFlag === true)
    {
        echo "<br>#74#resultMh Select 1#<br>";
        echo "<br>timeStamp:$timeStamp";
    }

    $resultMh = $db->query("SELECT *             
                                    FROM mheard
                                   WHERE timestamps = '$timeStamp'
                                ORDER BY mhTime DESC;
                        ");

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

            $dbMd = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
            $dbMd->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

            // Hole mir die pos-Daten aus der Datenbank
            $resultMd    = $dbMd->query("SELECT * 
                                                 FROM meshdash
                                                WHERE src = '$callSign'
                                                  AND type = 'pos'
                                             ORDER BY timestamps DESC
                                                LIMIT 1;
                                                    ");
            $dsDataMd    = $resultMd->fetchArray(SQLITE3_ASSOC);
            $validDataMd = !empty($dsDataMd);

            if ($validDataMd)
            {
                $returnArray[$callSign]['timestamps'] = $dsDataMd['timestamps'];
                $returnArray[$callSign]['latitude']   = substr($dsDataMd['latitude'],0,7);
                $returnArray[$callSign]['longitude']  = substr($dsDataMd['longitude'],0,6);
                $returnArray[$callSign]['altitude']   = number_format($dsDataMd['altitude'] * 0.3048); // Umrechnung Fuss -> Meter;
                $returnArray[$callSign]['firmware']   = $dsDataMd['firmware'];
                $returnArray[$callSign]['fw_sub']     = $dsDataMd['fw_sub'];
                $returnArray[$callSign]['batt']       = $dsDataMd['batt'];
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
        }
    }

    ########### Own Pos

    $dbMd = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
    $dbMd->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    // Hole mir die pos-Daten aus der Datenbank
    $resultMdOwn    = $dbMd->query("SELECT * 
                                         FROM meshdash
                                        WHERE src = '$ownCallSign'
                                          AND type = 'pos'
                                     ORDER BY timestamps DESC
                                        LIMIT 1;
                                            ");
    $dsDataMdOwn    = $resultMdOwn->fetchArray(SQLITE3_ASSOC);
    $validDataMdOwn = !empty($dsDataMdOwn);

    if ($validDataMdOwn)
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
}

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

