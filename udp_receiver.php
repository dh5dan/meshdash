<?php
#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

$errorCode      = '';
$errorMsg       = '';
$file           = 'log/udp_msg_data_' . date('Ymd') . '.log';
$errorFile      = 'log/udp_receiver_error_' . date('Ymd') . '.log';
$callMsgLogFile = 'log/call_message_' . date('Ymd') . '.log';
$udpPidFile     = UPD_PID_FILE;
$udpStopFile    = UPD_STOP_FILE;
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
$osIssWindows = chkOsIssWindows();

#Check what oS is running
if (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN')
{
    $osTypeIsLinux = false;

    #check if UDP Port is currently in Use from PHP
    $getPortInUse = shell_exec('netstat -a -n -b -o -P UDP | find "1799"');

    #If in USe kill Process
    if ($getPortInUse != '')
    {
        echo "<br>ex: $getPortInUse";

        $errorText = "Windows: Port In use [$getPortInUse] at " . date('Y-m-d H:i:s') . "\n";
        file_put_contents($errorFile, $errorText,FILE_APPEND);

        $splitOut = explode(' ', $getPortInUse);

        foreach ($splitOut as $outData)
        {
            if ($outData != '')
            {
                $outDataArray[] = $outData;
            }
        }

        end($outDataArray);

        $pid = (current($outDataArray) !== false) ? current($outDataArray) : 0;

        if ($pid != 0)
        {
            $resKillPid = shell_exec('taskkill /F /PID ' . $pid);

            $errorText = "Windows: Kill Task  Pid: [$pid] at " . date('Y-m-d H:i:s') . "\n";
            file_put_contents($errorFile, $errorText,FILE_APPEND);

            sleep(1);
        }
    }
}
else
{
    #Linux Part
    $getPortInUse = shell_exec('netstat -nlpu|grep 1799');

    #If in Use kill Process
    if ($getPortInUse != '')
    {
        $errorText = "Linux: Port 1799 in use: [$getPortInUse] at " . date('Y-m-d H:i:s') . "\n";
        $errorText .= "Try to kill process at " . date('Y-m-d H:i:s') . "\n";
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
            $resKillPid = shell_exec('kill -9 ' . $pidId);

            $errorText = "Linux: Kill process with pid: [$pidId] at " . date('Y-m-d H:i:s') . "\n";
            file_put_contents($errorFile, $errorText,FILE_APPEND);

            sleep(1);
        }
    }
}

#Create Socket
if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
{
    $errorCode = socket_last_error();
    $errorMsg  = socket_strerror($errorCode);

    $errorText = "Couldn't create socket: [$errorCode] $errorMsg at " . date('Y-m-d H:i:s') . "\n";
    file_put_contents($errorFile, $errorText,FILE_APPEND);
    die("<br>Couldn't create socket: [$errorCode] $errorMsg");
}

// Socket Option
#Reuse an existing Address SO_REUSEADDR
#socket_set_option ($sock, SOL_SOCKET, SO_REUSEADDR, 1);
#Reuse an existing Port SO_REUSEPort ->error
#socket_set_option ($sock, SOL_SOCKET, 15, 1);

// Bind the source address to Socket and listen on all Ip
if (!@socket_bind($sock, "0.0.0.0", 1799))
{
    $errorCode = socket_last_error();
    $errorMsg  = socket_strerror($errorCode);

    $errorText = "Could not bind socket: [$errorCode] $errorMsg at " . date('Y-m-d H:i:s') . "\n";
    file_put_contents($errorFile, $errorText,FILE_APPEND);

    die("<br>Could not bind socket : [$errorCode] $errorMsg");
}

file_put_contents('udp.pid', getmypid());
// PID speichern in Parameter Datenbank.
setParamData('udpReceiverPid', getmypid());
setParamData('udpReceiverTs', date('Y-m-d H:i:s'),'txt');

//Infinite Iteration to Receive UDP Data from Bind Port
while (true)
{
    $bufJson   = '';
    $r         = socket_recvfrom($sock, $bufJson, 512, 0, $remote_ip, $remote_port);

    if ($r === false)
    {
        $errorText = "Error in Receive UDP Data at " . date('Y-m-d H:i:s') . "\n";
        file_put_contents($errorFile, $errorText, FILE_APPEND);

        die("<br>Error in Receive UDP Data");
    }

    #Add Timestamp to JSON
    $bufJsonDecodedArray              = json_decode($bufJson, true);
    $bufJsonDecodedArray['timestamp'] = date('Y-m-d H:i:s');
    $bufJson                          = json_encode($bufJsonDecodedArray);

    #Prüfe ob Logging aktiv ist
    if (getParamData('doLogEnable') == 1)
    {
        $bufJsonLog = $bufJson . ",\n";
        file_put_contents($file, $bufJsonLog, FILE_APPEND);
    }

    $dbArraySqliteJson = json_decode($bufJson, true);

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
    $battery         = $dbArraySqliteJson['batt'] ?? '';  // Batt Kapazität in %
    $dst             = $dbArraySqliteJson['dst'] ?? ''; // 995 | call
    $firmware        = $dbArraySqliteJson['firmware'] ?? ''; // Firmware 4.34
    $fwSubVersion    = $dbArraySqliteJson['fw_sub'] ?? ''; // FirmwareSUb Version: v

    #Open Database
    $db = new SQLite3('database/meshdash.db');
    $db->exec('PRAGMA synchronous = NORMAL;');

    #Escape Msg
    $msg = SQLite3::escapeString($msg);

    #Store in SQLite DB
    $db->exec("REPLACE INTO meshdash (
                                          msg_id, 
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
                                       ) VALUES (
                                         '$msgId', 
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
           ");

    #Close Database connection
    $db->close();
    unset($db);

    #Trigger MessageSeite um Keywords abzuarbeiten
    $resCallMessagePage = callMessagePage();

    #Prüfe ob Logging aktiv ist
    if (getParamData('doLogEnable') == 1)
    {
        if ($resCallMessagePage === true)
        {
            $callMsgText = "Message.php Triggered via Curl at " . date('Y-m-d H:i:s') . "\n";
        }
        else
        {
            $callMsgText = "Error: Message.php NOT Triggered via Curl at " . date('Y-m-d H:i:s') . "\n";
        }

        file_put_contents($callMsgLogFile, $callMsgText, FILE_APPEND);
    }

    #Rekonstruiere PID
    if (!file_exists($udpPidFile))
    {
        file_put_contents('udp.pid', getmypid());
    }

    if (file_exists($udpStopFile))
    {
        socket_close($sock);

        $errorText = "UDP-Listener beendet via udp_stop! at " . date('Y-m-d H:i:s') . "\n";
        file_put_contents($errorFile, $errorText,FILE_APPEND);
        unlink($udpStopFile);
        unlink($udpPidFile);

        exit();
    }
}