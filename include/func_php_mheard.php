<?php

function showMheard()
{
    $timeStamp = '';

    $db = new SQLite3('database/mheard.db', SQLITE3_OPEN_READONLY);
    $db->busyTimeout(5000); // warte wenn busy in millisekunden

    // Hole mir die letzten 30 Nachrichten aus der Datenbank
    $result = $db->query("SELECT timestamps from mheard
                
                                        GROUP BY timestamps
                                        ORDER BY timestamps DESC
                                        LIMIT 1;
                        ");

    $dsData = $result->fetchArray(SQLITE3_ASSOC);

    $validData = !empty($dsData);

    if ($validData)
    {
        $timeStamp = $dsData['timestamps'];

        $resultMh = $db->query("SELECT * FROM mheard
                
                                        WHERE timestamps = '$timeStamp'
                                        ORDER BY timestamps DESC;
                        ");

        if ($resultMh !== false)
        {
            echo "<br>";
            echo "<br>";

            echo '<table class="table">';
            echo '<tr>';
            echo '<th>LHeard call</th>';
            echo '<th>Date</th>';
            echo '<th>Time</th>';
            echo '<th>LHeard hardware</th>';
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

    #Close and write Back WAL
    $db->close();
    unset($db);

    exit();
}
