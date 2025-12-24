<?php
# Wichtig!
# Gewährleistet, das das Skript immer aus dem Verzeichnis ausgeführt ist, wo es liegt.
# Alle relativen Pfade bleiben somit erhalten, auch wenn es aus dem SubMenü aufgerufen wird.
chdir(__DIR__);

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

// Relativer Pfad zu deinem Webverzeichnis
$basePath     = __DIR__;
$execDir      = 'log';
$errorCode      = '';
$errorMsg       = '';
$errorFile      = "$basePath/$execDir/" . 'error_udp_receiver_' . date('Ymd') . '.log';
$debugLogFile   = "$basePath/$execDir/" . 'debug_udp_receiver_' . date('Ymd') . '.log';
$udpPidFile     = "$basePath/$execDir/". UPD_PID_FILE;
$udpStopFile    = "$basePath/$execDir/". UPD_STOP_FILE;
$outDataArray   = array();
$osTypeIsLinux  = true;
$debugFlag      = false;

#Scriptexecution Time endless
ini_set('max_execution_time', 0);

#Check ob aufruf via CLI
if (php_sapi_name() !== 'cli')
{
    die("Aufruf nur via CLI. Abbruch.");
}

ini_set('serialize_precision', 14); //Must set it's a Bug in PHP > 7.1.x
ini_set('precision', 14);

#Check what oS is running
$osIssWindows = chkOsIsWindows();

if ($debugFlag === true)
{
    $debugText = date('Y-m-d H:i:s') . " - osIssWindows:$osIssWindows udpPidFile:$udpPidFile" . "\n";
    file_put_contents($debugLogFile, $debugText, FILE_APPEND);

    $debugText = date('Y-m-d H:i:s') . " - osIssWindows:$osIssWindows basePath:$basePath" . "\n";
    file_put_contents($debugLogFile, $debugText, FILE_APPEND);
}

#Check if Windows-OS is running
if (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN')
{
    $osTypeIsLinux = false;

    #check if UDP Port is currently in Use from PHP
    $getPortInUse = shell_exec('netstat -a -n -b -o -P UDP | find "1799"');

    #If port 1799 is in Use then kill Process
    if ($getPortInUse != '')
    {
        $errorText = date('Y-m-d H:i:s') . " - Windows: Port In use [$getPortInUse]" . "\n";
        file_put_contents($errorFile, $errorText,FILE_APPEND);

        $splitOut = explode(' ', $getPortInUse);

        foreach ($splitOut as $outData)
        {
            if ($outData != '')
            {
                $outDataArray[] = $outData;
            }
        }

        #Setze Datenzeiger im Array auf den letzten Eintrag
        end($outDataArray);

        $pid = (current($outDataArray) !== false) ? current($outDataArray) : 0;

        if ($pid != 0)
        {
            $errorText = date('Y-m-d H:i:s') . " - Windows: Kill Task  Pid: [$pid]" . "\n";
            file_put_contents($errorFile, $errorText,FILE_APPEND);

            $resKillPid = shell_exec('taskkill /F /PID ' . $pid);
            exit();
        }
    }
}
else
{
    #Part if OS is Linux
    $getPortInUse = shell_exec('netstat -nlpu|grep 1799');

    #If UDP 1799 in Use then kill Process
    if ($getPortInUse != '')
    {
        $errorText  = date('Y-m-d H:i:s') . " - Linux: Port 1799 in use: [$getPortInUse]" . "\n";
        $errorText .= date('Y-m-d H:i:s') . " - Try to kill process" . "\n";
        file_put_contents($errorFile, $errorText,FILE_APPEND);

        $splitOut = explode(' ', $getPortInUse);

        foreach ($splitOut as $outData)
        {
            if ($outData != '' && $outData != "\n" && $outData != "\r" && $outData != "\n\r" && $outData != "\r\n")
            {
                $outDataArray[] = $outData;
            }
        }

        #Pointer to end of array
        end($outDataArray);

        $pid = (current($outDataArray) !== false) ? current($outDataArray) : 0;

        if ($pid != 0)
        {
            $pidSplit   = explode('/', $pid);
            $pidId      = $pidSplit[0];

            $errorText = date('Y-m-d H:i:s') . " - Linux: Kill process with pid: [$pidId]" . "\n";
            file_put_contents($errorFile, $errorText,FILE_APPEND);

            $resKillPid = shell_exec('kill -9 ' . $pidId);
            exit();
        }
    }
}

#Create RECEIVE (RX) UDP-Socket
if (!($receiveSock = socket_create(AF_INET, SOCK_DGRAM, 0)))
{
    $errorCode = socket_last_error();
    $errorMsg  = socket_strerror($errorCode);

    $errorText = date('Y-m-d H:i:s') . " - Couldn't create Receive-Socket: [$errorCode] $errorMsg" . "\n";
    file_put_contents($errorFile, $errorText,FILE_APPEND);
    exit();
}

#Create SEND (TX) UDP-Socket
if (!($sendSock = socket_create(AF_INET, SOCK_DGRAM, 0)))
{
    $errorCode = socket_last_error();
    $errorMsg  = socket_strerror($errorCode);

    $errorText = date('Y-m-d H:i:s') . " - Couldn't create Send-Socket: [$errorCode] $errorMsg" . "\n";
    file_put_contents($errorFile, $errorText,FILE_APPEND);
    exit();
}

// Socket Option
#Reuse an existing Address SO_REUSEADDR
#socket_set_option ($sock, SOL_SOCKET, SO_REUSEADDR, 1);
#Reuse an existing Port SO_REUSEPort ->error
#socket_set_option ($sock, SOL_SOCKET, 15, 1);

// Bind the source address to Socket and listen on all Ip at Port 1799
if (!@socket_bind($receiveSock, "0.0.0.0", 1799))
{
    $errorCode = socket_last_error();
    $errorMsg  = socket_strerror($errorCode);

    $errorText = date('Y-m-d H:i:s') . " - Could not bind socket: [$errorCode] $errorMsg" . "\n";
    file_put_contents($errorFile, $errorText,FILE_APPEND);

    exit();
}

#Setze PID-File
file_put_contents($udpPidFile, getmypid());

// PID speichern in Parameter Datenbank.
setParamData('udpReceiverPid', getmypid());
setParamData('udpReceiverTs', date('Y-m-d H:i:s'),'txt');

if ($debugFlag === true)
{
    $debugText = date('Y-m-d H:i:s') . " - osIssWindows:$osIssWindows Get PID:" . getmypid() . "\n";
    file_put_contents($debugLogFile, $debugText, FILE_APPEND);
}

//Infinite Iteration to Receive UDP Data from Bind Port
while (true)
{
    if ($debugFlag === true)
    {
        $debugText = date('Y-m-d H:i:s') . " - In infinite Loop. Warte auf UDP-Nachrichten" . "\n";
        file_put_contents($debugLogFile, $debugText, FILE_APPEND);
    }

    $logFileName    = 'log/udp_msg_data_' . date('Ymd') . '.log';
    $errorFile      = 'log/udp_receiver_error_' . date('Ymd') . '.log';
    $callMsgLogFile = 'log/call_message_' . date('Ymd') . '.log';
    $fileUdpForward = 'log/udp_forward_msg_data_' . date('Ymd') . '.log';

    $receivedBytes  = socket_recvfrom($receiveSock, $udpBuffer, 512, 0, $remote_ip, $remote_port);

    if ($receivedBytes === false)
    {
        $errorText  = date('Y-m-d H:i:s') . " - Error in Receive UDP-Data" . "\n";
        $errorText .= date('Y-m-d H:i:s') . " - Failed MSG: " . socket_strerror(socket_last_error($receiveSock)) . "\n";
        file_put_contents($errorFile, $errorText, FILE_APPEND);

        exit();
    }

    if ($debugFlag === true)
    {
        $debugText = date('Y-m-d H:i:s') . " - UDP-Nachricht im RAW-Format empfangen:$udpBuffer<-----" . "\n";
        file_put_contents($debugLogFile, $debugText, FILE_APPEND);
    }

    #Hole Daten für UPD-Weiterleitung
    $udpForwardingEnable = getParamData('udpForwardingEnable') ?? 0; // UDP-Weiterleitung
    $udpFwIp             = getParamData('udpFwIp') ?? ''; // UDP-Weiterleitung IP
    $udpFwPort           = getParamData('udpFwPort') ?? 0; // UDP-Weiterleitung Port

    # Wenn aktiv dann weiterleiten
    if ($udpForwardingEnable == 1 && $udpFwIp != '' && $udpFwPort != 0)
    {
        // Datagram weiterleiten
        $sent = socket_sendto($sendSock, $udpBuffer, $receivedBytes, 0, $udpFwIp, $udpFwPort);

        if ($sent === false)
        {
            $errorText  = date('Y-m-d H:i:s') . " - Error in Forwarding UDP-Data to: $udpFwIp:$udpFwPort" . "\n";
            $errorText .= date('Y-m-d H:i:s') . " - UDP-Forward failed: " . socket_strerror(socket_last_error($sendSock)) . "\n";
            file_put_contents($errorFile, $errorText, FILE_APPEND);
        }
        else
        {
            #Prüfe ob Logging aktiv ist
            if (getParamData('doLogEnable') == 1)
            {
                $udpBufferLogText = date('Y-m-d H:i:s') . " - " . $udpBuffer . ",\n";
                file_put_contents($fileUdpForward, $udpBufferLogText, FILE_APPEND);
            }
        }
    }

    #Add Timestamp to JSON
    $bufJsonDecodedArray              = json_decode($udpBuffer, true); // Decode JSON aus udpBuffer
    $bufJsonDecodedArray['timestamp'] = date('Y-m-d H:i:s'); // Füge Datum an
    $bufJsonTs                        = json_encode($bufJsonDecodedArray); //Encode wieder als JSON

    #Prüfe ob Logging aktiv ist und Logge empfangenes UDP-Packet + TimeStamp
    if (getParamData('doLogEnable') == 1)
    {
        $udpBufferLogText = date('Y-m-d H:i:s') . " - " . $bufJsonTs . ",\n";
        file_put_contents($logFileName, $udpBufferLogText, FILE_APPEND);
    }

    $dbArraySqliteJson = json_decode($bufJsonTs, true);

    $msgId           = $dbArraySqliteJson['msg_id'] ?? rand(); // 72378728
    $timestamp       = $dbArraySqliteJson['timestamp'] ?? date('Y-m-d H:i:s');
    $msg             = $dbArraySqliteJson['msg'] ?? '';     //
    $srcType         = $dbArraySqliteJson['src_type'] ?? ''; // node, lora
    $type            = $dbArraySqliteJson['type'] ?? '';     // pos
    $src             = $dbArraySqliteJson['src'] ?? '';     // <call>-<sid>
    $latitude        = $dbArraySqliteJson['lat'] ?? '';     // 51.5012
    $latDir          = $dbArraySqliteJson['lat_dir'] ?? ''; // N
    $longitude       = $dbArraySqliteJson['long'] ?? '';    // 7.34
    $longDir         = $dbArraySqliteJson['long_dir'] ?? '';  //E
    $aprsSymbol      = $dbArraySqliteJson['aprs_symbol'] ?? ''; // #
    $aprsSymbolGroup = $dbArraySqliteJson['aprs_symbol_group'] ?? ''; // /
    $hwId            = $dbArraySqliteJson['hw_id'] ?? ''; // 3
    $altitude        = $dbArraySqliteJson['alt'] ?? ''; // 344 (Höhe in m)
    $battery         = $dbArraySqliteJson['batt'] ?? '';  // Batterie-Kapazität in %
    $dst             = $dbArraySqliteJson['dst'] ?? ''; // 995 | call
    $firmware        = $dbArraySqliteJson['firmware'] ?? ''; // Firmware 4.34
    $fwSubVersion    = $dbArraySqliteJson['fw_sub'] ?? ''; // FirmwareSUb Version: v

    #Wenn keine Daten vorhanden, dann nicht speichern und auf nächste Msg warten
    if ($msgId == '' && $src == '' && $type == '' && $udpBuffer == '')
    {
        if ($debugFlag === true)
        {
            $debugText = date('Y-m-d H:i:s') . " - UDP-Nachricht verworfen da msgID, SRC und Type leer." . "\n";
            file_put_contents($debugLogFile, $debugText, FILE_APPEND);
        }

        continue;
    }

    if ($debugFlag === true)
    {
        $debugText = date('Y-m-d H:i:s') . " - DB INSERT  DATA: "  . print_r($dbArraySqliteJson, true) . "\n";
        file_put_contents($debugLogFile, $debugText, FILE_APPEND);
    }

    #Open Database
    $db = new SQLite3('database/meshdash.db');
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in Millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    #Escape Msg
    $msg = SQLite3::escapeString($msg);

    #Store in SQLite DB
    $sql = "REPLACE INTO meshdash (msg_id, 
                                   timestamps, 
                                   msg, 
                                   src_type, 
                                   type, 
                                   src, 
                                   latitude, 
                                   lat_dir, 
                                   longitude, 
                                   long_dir, 
                                   aprs_symbol, 
                                   aprs_symbol_group, 
                                   hw_id, 
                                   altitude,
                                   batt,
                                   dst,
                                   firmware,
                                   fw_sub  
                                  ) 
                           VALUES ('$msgId', 
                                   '$timestamp',
                                   '$msg',
                                   '$srcType',
                                   '$type', 
                                   '$src',
                                   '$latitude',
                                   '$latDir',                                                 
                                   '$longitude', 
                                   '$longDir',
                                   '$aprsSymbol',
                                   '$aprsSymbolGroup',
                                   '$hwId', 
                                   '$altitude',
                                   '$battery',
                                   '$dst',
                                   '$firmware',
                                   '$fwSubVersion'        
                                  )
           ";

    $logArray   = array();
    $logArray[] = "udpReceiver: Database: database/meshdash.db";

    $res = safeDbRun($db, $sql, 'query', $logArray);

    #Close Database connection
    $db->close();
    unset($db);

    #Trigger Message-Seite via CURL um Keywords abzuarbeiten wenn Headless
    $resCallMessagePage = callMessagePage();

    #Prüfe ob Logging aktiv ist
    if (getParamData('doLogEnable') == 1)
    {
        if ($resCallMessagePage === true)
        {
            $callMsgText = date('Y-m-d H:i:s') . " - Message.php Triggered via Curl:" . BASE_PATH_URL . "\n";
        }
        else
        {
            $callMsgText = date('Y-m-d H:i:s') . " - Error: Message.php NOT Triggered via Curl:" . BASE_PATH_URL . "\n";
        }

        file_put_contents($callMsgLogFile, $callMsgText, FILE_APPEND);
    }

    #Rekonstruiere PID wenn nicht vorhanden
    if (!file_exists($udpPidFile))
    {
        file_put_contents($udpPidFile, getmypid());

        if ($debugFlag === true)
        {
            $debugText = date('Y-m-d H:i:s') . " - Rekonstruiere PID-File weil es fehlt: Neue PID:" . getmypid() . "\n";
            file_put_contents($debugLogFile, $debugText, FILE_APPEND);
        }
    }

    #Wenn Stop-File erkannt Prozess beenden
    if (file_exists($udpStopFile))
    {
        socket_close($receiveSock);
        socket_close($sendSock);

        $errorText = date('Y-m-d H:i:s') . " - UDP-Listener beendet via udp_stop!" . "\n";
        file_put_contents($errorFile, $errorText,FILE_APPEND);
        @unlink($udpStopFile);
        @unlink($udpPidFile);

        exit();
    }
}