<?php
function execScriptCurl($keywordCmd): bool
{
    $osIssWindows = chkOsIsWindows();

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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    $sql = "UPDATE meshdash
               SET ackReq = $ackReqId
             WHERE msg_id = '$msgId'
           ";

    $logArray   = array();
    $logArray[] = "updateAckReqId: Database: $dbFilename";
    $logArray[] = "updateAckReqId: ackReqId: $ackReqId";
    $logArray[] = "updateAckReqId: msgId: $msgId";

    $res = safeDbRun( $db, $sql,'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

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
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    $sql = "UPDATE meshdash
               SET ack = $ackId
             WHERE ackReq = $ackId
          ";

    $logArray   = array();
    $logArray[] = "updateAckId: Database: $dbFilename";
    $logArray[] = "updateAckId: ackId: $ackId";
    $logArray[] = "updateAckId: ackId: $ackId";

    $res = safeDbRun($db, $sql, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

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

    // Regulärer Ausdruck für "#mheard", gefolgt von einem Rufzeichen mit SSID (1-999)
    $pattern = '/#mheard\s+([A-Za-z0-9]+-\d{1,3})\b/i';

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
function checkBeaconOtp($msgId, $msg, $callSign, $dst, $beaconOtp): bool
{
    #Eliminiere Hops in Quelle und Ziel
    $dst = explode(',', $dst)[0];

    $debugFlag = false;

    if ($debugFlag === true)
    {
        echo "<br>msgId:$msgId";
        echo "<br>msg:$msg";
        echo "<br>callSign:$callSign";
        echo "<br>dst:$dst";
        echo "<br>beaconOtp:$beaconOtp";
    }

    // Regulärer Ausdruck für "#otpstart", gefolgt von einem OTP
    $pattern = '/#beacon\s+(.+)/i';

    if (preg_match($pattern, $msg, $matches))
    {
        $foundOtp = $matches[1]; // Gefundenes Rufzeichen mit SSID

        if ($debugFlag === true)
        {
            echo "<br>Gefunden foundOtp: $foundOtp";
        }

        if (strcasecmp($foundOtp, $beaconOtp) === 0)
        {
            if ($debugFlag === true)
            {
                echo "<br>Übereinstimmung! Funktion wird ausgeführt ...";
            }
          #  return false;
            #Aktiviere Bake lösche OPT
            $resRemoteStartBeacon = remoteStartBeacon();

            if ($resRemoteStartBeacon === true)
            {
                $beaconInterval  = getBeaconData('beaconInterval');
                $beaconStopCount = getBeaconData('beaconStopCount');

                $arraySend['txType'] = 'msg';
                $arraySend['txDst']  = 9;
                $arraySend['txMsg']  = 'Beacon['. $callSign .'] activated. Interval: '. $beaconInterval .' Stop-Count: '. $beaconStopCount;
                $resSetTxQueue       = setTxQueue($arraySend);

                if($resSetTxQueue === true)
                {
                    #Setzte Msg als gesendet
                    updateMeshDashData($msgId, 'beaconEnabledStatusSend', 1);
                }
            }
        }
        else
        {
            if ($debugFlag === true)
            {
                echo "<br>Kein Match mit OTP ($beaconOtp).";
                return false;
            }
        }
    }
    else
    {
        if ($debugFlag === true)
        {
            echo "<br>Kein gültigen #beacon Key gefunden.";
        }

        return false;
    }

    return true;
}
function sendMheard($msgId, $src): bool
{
    #Prüfe ob Logging aktiv ist
    $doLogEnable      = getParamData('doLogEnable');
    $sendQueueMode    = getParamData('sendQueueMode');
    $sendQueueMode    = $sendQueueMode == '' ? 0 : $sendQueueMode;
    $mhTxQueueLogFile = 'log/send_queue_mheard_' . date('Ymd') . '.log';
    $mhTxLogFile      = 'log/send_mheard_' . date('Ymd') . '.log';
    #$mheardTarget = getParamData('mheardTarget'); // 0=Gruppe / 1=Call (derzeit ungenutzt)

    $db = new SQLite3('database/mheard.db', SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden


    $sql = "SELECT timestamps 
              FROM mheard
          GROUP BY timestamps
          ORDER BY timestamps DESC
             LIMIT 1;
         ";

    $logArray   = array();
    $logArray[] = "sendMheard: Database: database/mheard.db";
    $logArray[] = "sendMheard: msgId:" . $msgId;
    $logArray[] = "sendMheard: src:" . $src;

    $result = safeDbRun($db, $sql, 'query', $logArray);

    if ($result === false)
    {
        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    $dsData = $result->fetchArray(SQLITE3_ASSOC);

    if (!empty($dsData) === true)
    {
        $timeStamp = $dsData['timestamps'];

        $sqlMh = "SELECT * 
                    FROM mheard
                   WHERE timestamps = '$timeStamp'
                ORDER BY timestamps DESC;
              ";

        $logArray   = array();
        $logArray[] = "sendMheard_TS: Database: database/mheard.db";
        $logArray[] = "sendMheard_TS: timeStamp:" . $timeStamp;

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
                    $logText = date(
                            'Y-m-d H:i:s'
                        ) . " MHeard in Send-Queue gespeichert: Ziel: $src MHListe: $sendMheardList\n";
                    file_put_contents($mhTxQueueLogFile, $logText, FILE_APPEND);
                }
                else
                {
                    $logText = date('Y-m-d H:i:s') . " MHeard gesendet: Ziel: $src MHListe: $sendMheardList\n";
                    file_put_contents($mhTxLogFile, $logText, FILE_APPEND);
                }
            }
        }
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return true;
}
function hasKeywordTimePassed(string $startTs, string $endTs, int $limitSeconds): bool
{
    try
    {
        $start = new DateTime($startTs);
        $end   = new DateTime($endTs);

        $startUnix = $start->getTimestamp();
        $endUnix   = $end->getTimestamp();

        $diffSeconds = $endUnix - $startUnix;

        return $diffSeconds >= $limitSeconds;
    }
    catch (Exception $e)
    {
        return false;
    }
}
function getCallNotices()
{
    $arrayCallSign      = array();
    $arrayCleanCallSign = array(); //zu korrigierende CallSign mit Hochkomma

    $db = new SQLite3('database/call_notice.db', SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    $sql = "SELECT callSign 
              FROM callNotice
          GROUP BY callSign;
         ";

    $logArray   = array();
    $logArray[] = "getCallNotices: Database: database/call_notice.db";

    $result = safeDbRun($db, $sql, 'query', $logArray);

    if ($result === false)
    {
        #Close and write Back WAL
        $db->close();
        unset($db);

        return false;
    }

    if ($result !== false)
    {
        while ($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $callSign = $row['callSign'];

            // Prüfen, ob Hochkomma am Anfang/Ende
            if (substr($callSign, 0, 1) === "'" && substr($callSign, -1) === "'")
            {

                #Hochkomma CallSign vorher sichern für später
                $arrayCleanCallSign[$callSign] = $callSign;

                // Hochkommas entfernen
                $callSign = trim($callSign, "'");
            }

            $arrayCallSign[$callSign] = $callSign;
        }
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    if (count($arrayCleanCallSign) > 0)
    {
        updateCallNoticeDb($arrayCleanCallSign);
    }

    return $arrayCallSign;
}
function updateCallNoticeDb(array $arrayCleanCallSign)
{
    $db = new SQLite3('database/call_notice.db', SQLITE3_OPEN_READWRITE);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT);

    foreach ($arrayCleanCallSign as $oldCall) {
        $cleanCall = trim($oldCall, "'");
        $stmt = $db->prepare("UPDATE callNotice SET callSign = :cleanCall WHERE callSign = :oldCall");
        $stmt->bindValue(':cleanCall', $cleanCall, SQLITE3_TEXT);
        $stmt->bindValue(':oldCall', $oldCall, SQLITE3_TEXT);
        $stmt->execute();
    }

    $db->close();
    unset($db);
}

function remoteStartBeacon(): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/beacon.db';
    $dbFilenameRoot = 'database/beacon.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden

    $sql = "UPDATE beacon
               SET 
                   param_text  = CASE WHEN param_key = 'beaconOtp'        THEN '' END,
                   param_text  = CASE WHEN param_key = 'beaconInitSendTs' THEN '0000-00-00 00:00:00' END,
                   param_text  = CASE WHEN param_key = 'beaconLastSendTs' THEN '0000-00-00 00:00:00' END,
                   param_value = CASE WHEN param_key = 'beaconCount'      THEN 0 END,
                   param_value = CASE WHEN param_key = 'beaconEnabled'    THEN 1 END
             WHERE param_key IN ('beaconOtp', 'beaconEnabled');
           ";

    $logArray   = array();
    $logArray[] = "updateBeaconOtp: OTP";
    $logArray[] = "updateBeaconOtp: Enable Beacon";
    $logArray[] = "updateBeaconOtp: Database: $dbFilename";

    $res = safeDbRun( $db,  $sql, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($res === false)
    {
        return false;
    }

    #Aktiviere Bake
    $beaconInterval = getBeaconData('beaconInterval') ?? 0;
    $beaconInterval = $beaconInterval == '' ? 5 : $beaconInterval;

    setBeaconCronInterval($beaconInterval, 1);

    return true;
}