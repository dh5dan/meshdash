<?php

function execScriptCurl($keywordCmd): bool
{
    $osIssWindows = chkOsIssWindows();

    if ($osIssWindows === true)
    {
        #Unter Windows mit Curl Starten
        callWindowsBackgroundTask($keywordCmd, 'execute');
    }
    else
    {
        if (substr($keywordCmd,-2) == 'sh')
        {
            exec('cd execute && nohup ./' . $keywordCmd . ' >/dev/null 2>&1 &');
        }

        if (substr($keywordCmd,-3) == 'php')
        {
            exec('cd execute && nohup php ./' . $keywordCmd . ' >/dev/null 2>&1 &');
        }
    }

    return true;
}

function updateAckReqId($msgId, $ackReqId): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/meshdash.db';
    $dbFilenameRoot = 'database/meshdash.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');
    #$db->busyTimeout(5000); // warte wenn busy in millisekunden

    @$db->exec(" UPDATE meshdash
                          SET ackReq = $ackReqId
                        WHERE msg_id = '$msgId'
                    ");

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return true;
}

function updateAckId($ackId): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/meshdash.db';
    $dbFilenameRoot = 'database/meshdash.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');

    $db->exec(" UPDATE meshdash
                          SET ack = $ackId
                        WHERE ackReq = $ackId
                    ");

    if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
    {
        echo "<br>setParamData";
        echo "<br>ErrMsg:" . $db->lastErrorMsg();
        echo "<br>ErrNum:" . $db->lastErrorCode();
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return true;
}

#Regiere auf ack<integer>
function checkMsgAck($msg): bool
{
    $debugFlag = false;
    $ackFound  = false;
    preg_match('/ack(\d+)/', $msg, $matches);

    if (!empty($matches[1]))
    {
        $ackId = (int) $matches[1];

        updateAckId($ackId);

        $ackFound  = true;
        if($debugFlag === true)
        {
            echo $ackId; // Gibt ackId aus
        }
    }

    return $ackFound;
}

function checkMheard($msgId, $msg, $src, $dst, $callSign, $loraIp, $mhTargetFlag)
{
    #Eliminiere Hops in Quelle und Ziel
    $src = explode(',', $src)[0];
    $dst = explode(',', $dst)[0];

    #$mhTargetFlag 1= Sende an Anfragecall 0=sende an anfrageGruppe
    $mhTarget  = $mhTargetFlag == 1 ? $src : $dst;
    $mhTarget  = $callSign == $dst ? $src : $mhTarget;
    $debugFlag = false;

    if ($debugFlag === true)
    {
        echo "<br>msgId:$msgId";
        echo "<br>msg:$msg";
        echo "<br>src:$src";
        echo "<br>dst:$dst";
        echo "<br>callSign:$callSign";
        echo "<br>mhTargetFlag:$mhTargetFlag";
        echo "<br>mhTarget:$mhTarget";
    }

    // Regulärer Ausdruck für "mheard", gefolgt von einem Rufzeichen mit SSID (1-999)
    $pattern = '/\bmheard\s+([A-Za-z0-9]+-\d{1,3})\b/i';

    if (preg_match($pattern, $msg, $matches))
    {
        $foundCall = $matches[1]; // Gefundenes Rufzeichen mit SSID

        if ($debugFlag === true)
        {
            echo "<br>Gefunden TargetCall: $foundCall";
        }

        if (strcasecmp($foundCall, $callSign) === 0)
        {
            if ($debugFlag === true)
            {
                echo "<br>Übereinstimmung! Funktion wird ausgeführt ...";
            }

            #Setzte auf Pending, um bei Verzögerungen ggf. Mehrfachaussendungen zu vermeiden
            updateMeshDashData($msgId, 'mhSend', -1);
            $resGetMheard = getMheard($loraIp); //Hole aktuelle Mh-Liste

            if ($resGetMheard === true)
            {
                sendMheard($msgId, $mhTarget);
            }
        }
        else
        {
            if ($debugFlag === true)
            {
                echo "<br>Kein Match mit DST ($callSign).";
            }
        }
    }
    else
    {
        if ($debugFlag === true)
        {
            echo "<br>Kein gültiges Mheard-Muster gefunden.";
        }
    }
}

function sendMheard($msgId, $src)
{
    #Prüfe ob Logging aktiv ist
    $doLogEnable   = getParamData('doLogEnable');
    $sendQueueMode = getParamData('sendQueueMode');
    $sendQueueMode = $sendQueueMode == '' ? 0 : $sendQueueMode;
    #$mheardTarget = getParamData('mheardTarget'); // 0=Gruppe / 1=Call (derzeit ungenutzt)

    $db = new SQLite3('database/mheard.db', SQLITE3_OPEN_READONLY);
    $db->busyTimeout(5000); // warte wenn busy in millisekunden

    // Hole mir die letzten 30 Nachrichten aus der Datenbank
    $result = $db->query("SELECT timestamps 
                                   FROM mheard
                               GROUP BY timestamps
                               ORDER BY timestamps DESC
                                  LIMIT 1;
                        ");

    $dsData = $result->fetchArray(SQLITE3_ASSOC);

    $validData = !empty($dsData);

    if ($validData)
    {
        $timeStamp = $dsData['timestamps'];

        $resultMh = $db->query("SELECT * 
                                        FROM mheard
                                       WHERE timestamps = '$timeStamp'
                                    ORDER BY timestamps DESC;
                        ");

        if ($resultMh !== false)
        {
            $sendMheardList = '';

            while ($row = $resultMh->fetchArray(SQLITE3_ASSOC))
            {
                ###############################################
                #Common
                $callSign = $row['mhCallSign'];
                #$date     = $row['mhDate'];
                #$time     = $row['mhTime'];
                #$hardware = $row['mhHardware'];
                #$mod      = $row['mhMod'];
                $rssi     = $row['mhRssi'];
                #$snr      = $row['mhSnr'];
                #$dist     = $row['mhDist'];
                #$pl       = $row['mhPl'];
                #$m        = $row['mhM'];

                $sendMheardList .= $callSign . ' ' . $rssi . '|';
            }

            #Letztes Zeichen entfernen und auch 160 Zeichen begrenzen
            $sendMheardList = substr(rtrim($sendMheardList, "|"), 0, 160);

            $sendMheardList = $sendMheardList == '' ? 'Keine MH-Liste vorhanden.' : $sendMheardList;

            $arraySend['txType'] = 'msg';
            $arraySend['txDst']  = $src;
            $arraySend['txMsg']  = $sendMheardList;
            $resSetTxQueue       = setTxQueue($arraySend);

            if ($resSetTxQueue === true)
            {
                updateMeshDashData($msgId, 'mhSend', 1);

                if ($doLogEnable === 1 && $sendQueueMode == 1)
                {
                    $logText = date('Y-m-d H:i:s') . " MHeard in Send-Queue gespeichert: Ziel: $src MHListe: $sendMheardList\n";
                    file_put_contents('log/send_queue_mheard.log', $logText, FILE_APPEND);
                }
                else
                {
                    $logText = date('Y-m-d H:i:s') . " MHeard gesendet: Ziel: $src MHListe: $sendMheardList\n";
                    file_put_contents('log/send_mheard.log', $logText, FILE_APPEND);
                }
            }
        }
    }
    else
    {
        echo "<h3>Keine gespeicherten MH-Heard Daten zum Senden vorhanden.</h3>";
    }

    #Close and write Back WAL
    $db->close();
    unset($db);
}