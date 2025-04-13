<?php

function callWindowsBackgroundTask($taskFile, $execDir = ''): bool
{
    $actualHost  = (empty($_SERVER['HTTPS']) ? 'http' : 'https');
    $triggerLink = $actualHost . '://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER["REQUEST_URI"] . '?') . '/' . 'task_bg.php';

    $postFields = array(
        'taskFile' => "$taskFile",
        'execDir' => "$execDir",
    );

    $debugFlag = false;

    #Starte Trigger
    $ch = curl_init();

    # Set Curl Options
    curl_setopt($ch, CURLOPT_URL, $triggerLink);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100); // Warte max. 100 ms und beende Verbindung
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

    #Ignoriere Timeout Meldung da so gewollt
    if (curl_exec($ch) === false && curl_errno($ch) != 28)
    {
        echo 'Curl error: ' . curl_error($ch);
        echo 'Curl error: ' . curl_errno($ch);
    }

    curl_close($ch);

    if ($debugFlag === true)
    {
        echo "<br> Debug: callWindowsBackgroundTask";
        echo "<br>triggerLink:$triggerLink";
        echo "<br>taskFile:$taskFile";

        echo "<pre>";
        print_r($postFields);
        echo "</pre>";

        echo "<pre>";
        print_r($ch);
        echo "</pre>";

        return true;
    }

    return true;
}

function initSQLiteDatabase($database): bool
{
    if ($database == 'meshdash')
    {
        #Open Database
        $db = new SQLite3('database/meshdash.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS meshdash 
                                (
                                  msg_id TEXT NOT NULL UNIQUE,              
                                  timestamps TEXT NOT NULL,
                                  msg TEXT,
                                  src_type TEXT,
                                  type TEXT,
                                  src TEXT,
                                  latitude REAL,
                                  lat_dir TEXT,
                                  longitude REAL,
                                  long_dir TEXT,
                                  aprs_symbol TEXT,
                                  aprs_symbol_group TEXT,
                                  hw_id INTEGER,
                                  altitude INTEGER,
                                  batt INTEGER,
                                  dst TEXT,
                                  ackReq INTEGER,
                                  ack INTEGER,
                                  mhSend INTEGER DEFAULT 0,
                                  alertExecutedSrc INTEGER DEFAULT 0,
                                  alertExecutedDst INTEGER DEFAULT 0,
                                  msgIsTimeSync INTEGER DEFAULT 0,
                                  msgIsAck INTEGER DEFAULT 0,
                                  firmware TEXT,
                                  fw_sub TEXT,
                                  PRIMARY KEY(msg_id)
                                )
                ");

        #Set Index
        $db->exec("CREATE INDEX IF NOT EXISTS idx_timestamps ON meshdash(timestamps);");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    elseif ($database == 'parameter')
    {
        #Open Database
        $db = new SQLite3('database/parameter.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS parameter 
                                (
                                  param_key TEXT NOT NULL UNIQUE,              
                                  param_value INTEGER,
                                  param_text TEXT,
                                  PRIMARY KEY(param_key)
                                )
                "
        );

        $version = VERSION;
        $db->exec("REPLACE INTO parameter (
                                          param_key, 
                                          param_value, 
                                          param_text
                                       ) VALUES 
                                       ('version', '', '$version'),
                                       ('loraIp', '', '0.0.0.0'),
                                       ('callSign', '', ''),
                                       ('noPosData', '0', ''),
                                       ('noDmAlertGlobal', '0', ''),
                                       ('keyword1Text', '', ''),      
                                       ('keyword11Cmd', '0', ''),
                                       ('keyword1Enabled', '0', ''),
                                       ('keyword2Text', '', ''),      
                                       ('keyword2Cmd', '0', ''),
                                       ('keyword12Enabled', '0', ''),
                                       ('keyword1ReturnMsg', '', ''),
                                       ('keyword2ReturnMsg', '', ''),
                                       ('keyword1DmGrpId', '', '*'),
                                       ('keyword2DmGrpId', '', '*'),
                                       ('noTimeSyncMsg', 0 , ''),
                                       ('maxScrollBackRows', 60 , ''),
                                       ('alertSoundFileSrc','' , 'callsign_src_alert.wav'),
                                       ('alertEnabledSrc',0 , ''),
                                       ('alertSoundCallSrc','' , ''),
                                       ('alertSoundFileDst','' , 'callsign_dst_alert.wav'),
                                       ('alertEnabledDst',0 , ''),
                                       ('alertSoundCallDst','' , ''),
                                       ('doLogEnable',1 , ''),
                                       ('doNotBackupDb', 0, ''),
                                       ('clickOnCall', 0, ''),
                                       ('chronLogEnable', 0, ''),
                                       ('retentionDays', 7, ''),
                                       ('chronMode', '', 'zip'),
                                       ('strictCallEnable', 0, ''),
                                       ('timeZone', '', 'Europe/Berlin'),
                                       ('sendQueueInterval', '30', ''),
                                       ('cronLoopPid', '', '')
           ");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    elseif ($database == 'keywords')
    {
        #Open Database
        $db = new SQLite3('database/keywords.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS keywords 
                                (
                                  msg_id TEXT NOT NULL UNIQUE,              
                                  executed INTEGER,
                                  errCode INTEGER,
                                  errText TEXT,
                                  PRIMARY KEY(msg_id)
                                )
                ");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    elseif ($database == 'sensordata')
    {
        #Open Database
        $db = new SQLite3('database/sensordata.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS sensordata 
                                (
                                  sensorDataId INTEGER PRIMARY KEY AUTOINCREMENT,              
                                  timestamps TEXT NOT NULL,
                                  bme280 TEXT,
                                  bme680 TEXT,
                                  mcu811 TEXT,
                                  lsp33 TEXT,
                                  oneWire TEXT,
                                  temp TEXT,
                                  tout TEXT,
                                  hum TEXT,
                                  qfe TEXT,
                                  qnh TEXT,
                                  altAsl TEXT,
                                  gas TEXT,
                                  eCo2 TEXT,
                                  ina226vBus TEXT,
                                  ina226vShunt TEXT,
                                  ina226vCurrent TEXT,
                                  ina226vPower TEXT
                                )
                ");

        #Set Index
        $db->exec("CREATE INDEX IF NOT EXISTS idx_timestamps ON sensordata(timestamps);");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    elseif ($database == 'sensor_th_temp')
    {
        #Open Database
        $db = new SQLite3('database/sensor_th_temp.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS sensorThTemp 
                                (
                                  sensorThTempId INTEGER PRIMARY KEY AUTOINCREMENT,             
                                  timestamps TEXT NOT NULL,
                                  sensorThTempIntervallMin INTEGER DEFAULT 1,
                                  sensorThTempEnabled INTEGER DEFAULT 0,
                                  sensorThTempMinValue TEXT,
                                  sensorThTempMaxValue TEXT,
                                  sensorThTempAlertMsg TEXT,
                                  sensorThTempAlertCount INTEGER DEFAULT 0,
                                  sensorThTempAlertTimestamp TEXT,
                                  sensorThTempDmGrpId INTEGER DEFAULT 999,
                                  
                                  sensorThToutEnabled INTEGER DEFAULT 0,
                                  sensorThToutMinValue TEXT,
                                  sensorThToutMaxValue TEXT,
                                  sensorThToutAlertMsg TEXT,
                                  sensorThToutAlertCount INTEGER DEFAULT 0,
                                  sensorThToutAlertTimestamp TEXT,
                                  sensorThToutDmGrpId INTEGER DEFAULT 999
                                )
                ");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    elseif ($database == 'sensor_th_ina226')
    {
        #Open Database
        $db = new SQLite3('database/sensor_th_ina226.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS sensorThIna226 
                                (
                                  sensorThIna226Id INTEGER PRIMARY KEY AUTOINCREMENT,             
                                  timestamps TEXT NOT NULL,
                                  sensorThIna226IntervallMin INTEGER DEFAULT 1,
                                  sensorThIna226vBusEnabled INTEGER DEFAULT 0,
                                  sensorThIna226vBusMinValue TEXT,
                                  sensorThIna226vBusMaxValue TEXT,
                                  sensorThIna226vBusAlertMsg TEXT,
                                  sensorThIna226vBusAlertCount INTEGER DEFAULT 0,
                                  sensorThIna226vBusAlertTimestamp TEXT,
                                  sensorThIna226vBusDmGrpId INTEGER DEFAULT 999,
                                  
                                  sensorThIna226vShuntEnabled INTEGER DEFAULT 0,
                                  sensorThIna226vShuntMinValue TEXT,
                                  sensorThIna226vShuntMaxValue TEXT,
                                  sensorThIna226vShuntAlertMsg TEXT,
                                  sensorThIna226vShuntAlertCount INTEGER DEFAULT 0,
                                  sensorThIna226vShuntAlertTimestamp TEXT,
                                  sensorThIna226vShuntDmGrpId INTEGER DEFAULT 999,
                                  
                                  sensorThIna226vCurrentEnabled INTEGER DEFAULT 0,
                                  sensorThIna226vCurrentMinValue TEXT,
                                  sensorThIna226vCurrentMaxValue TEXT,
                                  sensorThIna226vCurrentAlertMsg TEXT,
                                  sensorThIna226vCurrentAlertCount INTEGER DEFAULT 0,
                                  sensorThIna226vCurrentAlertTimestamp TEXT,
                                  sensorThIna226vCurrentDmGrpId INTEGER DEFAULT 999,
                                  
                                  sensorThIna226vPowerEnabled INTEGER DEFAULT 0,
                                  sensorThIna226vPowerMinValue TEXT,
                                  sensorThIna226vPowerMaxValue TEXT,
                                  sensorThIna226vPowerAlertMsg TEXT,
                                  sensorThIna226vPowerAlertCount INTEGER DEFAULT 0,
                                  sensorThIna226vPowerAlertTimestamp TEXT,
                                  sensorThIna226vPowerDmGrpId INTEGER DEFAULT 999
                                )
                ");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    elseif ($database == 'mheard')
    {
        #Open Database
        $db = new SQLite3('database/mheard.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS mheard 
                                (
                                  mheardId INTEGER PRIMARY KEY AUTOINCREMENT,              
                                  timestamps TEXT NOT NULL,
                                  mhCallSign TEXT,
                                  mhDate TEXT,
                                  mhTime TEXT,
                                  mhType TEXT,
                                  mhHardware TEXT,
                                  mhMod INTEGER,
                                  mhRssi INTEGER,
                                  mhSnr INTEGER,
                                  mhDist INTEGER,
                                  mhPl INTEGER,
                                  mhM INTEGER
                                )
                ");

        #Set Index
        $db->exec("CREATE INDEX IF NOT EXISTS idx_timestamps ON mheard(timestamps);");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    elseif ($database == 'groups')
    {
        #0: OFF – SQLite führt keine Synchronisierung durch (geringe Sicherheit, aber schnellere Schreiboperationen).
        #1: NORMAL – Standardmodus, SQLite führt eine Synchronisierung durch, aber nicht für alle Schreibvorgänge (bessere Sicherheit, aber etwas langsamer).
        #2: FULL – Höchste Sicherheit, bei dem alle Schreibvorgänge synchronisiert werden (höchste Sicherheit, aber auch langsamer).

        #Open Database
        $db = new SQLite3('database/groups.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS groups 
                                (
                                  groupId INTEGER PRIMARY KEY,              
                                  groupNumber INTEGER NOT NULL,
                                  groupEnabled INTEGER NOT NULL
                                )
                ");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    elseif ($database == 'tx_queue')
    {
        #0: OFF – SQLite führt keine Synchronisierung durch (geringe Sicherheit, aber schnellere Schreiboperationen).
        #1: NORMAL – Standardmodus, SQLite führt eine Synchronisierung durch, aber nicht für alle Schreibvorgänge (bessere Sicherheit, aber etwas langsamer).
        #2: FULL – Höchste Sicherheit, bei dem alle Schreibvorgänge synchronisiert werden (höchste Sicherheit, aber auch langsamer).

        #Open Database
        $db = new SQLite3('database/tx_queue.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS txQueue 
                                (
                                  txQueueId  INTEGER PRIMARY KEY AUTOINCREMENT, 
                                  insertTimestamp TEXT NOT NULL,              
                                  txTimestamp INTEGER NOT NULL,
                                  txType TEXT DEFAULT 'msg',
                                  txDst TEXT,
                                  txMsg TEXT,
                                  txFlag INTEGER DEFAULT 0
                                )
                ");

        #Set Index
        $db->exec("CREATE INDEX IF NOT EXISTS idx_txInsertTimestamp ON txQueue(insertTimestamp);");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    return true;
}

function showMenu()
{
    echo '
<div id="menu-icon" class="topMenu">&#9776;</div>
<div id="menu">
  <ul>
    <li>Einstellung
      <ul class="submenu">
        <li data-action="config_generally">Allgemein</li>
        <li data-action="config_send_queue">Sende-Intervall</li>
        <li data-action="config_alerting">Benachrichtigung</li>
        <li data-action="config_keyword">Keyword</li>
        <li data-action="config_update">Update</li>
        <li data-action="lora_info">Lora-Info</li>
        <li data-action="config_data_purge">Data-Purge</li>
        <li data-action="config_ping_lora">Ping Lora</li>
      </ul>
    </li>
    <li>Gruppen
      <ul class="submenu">
        <li data-action="grp_definition">Gruppen definieren</li>
      </ul>
    </li>
     <li>Sensoren
      <ul class="submenu">
        <li data-action="sensor_data">Sensordaten</li>
        <li data-action="sensor_threshold">Sensorschwellwerte</li>
      </ul>
    </li>
    <li data-action="mHeard">MHeard</li>';

    if (function_exists('curl_version'))
    {
        echo '  <li data-action="send_command">Sende Befehl</li>';
    }

    echo '
    <li data-action="message">Message</li>
     <li data-action="about">About</li>
  </ul>
</div>
';
}

function setLoraIpDb()
{
    $loraIp   = trim($_REQUEST['paramSetLoraIp']);
    $callSign = strtoupper(trim($_REQUEST['inputParamCallSign']));

    echo '<span class="unsetDisplayFlex">';
    echo "<br>";
    echo '<br><b>Setze Ip des Lora Gerätes auf IP: <mark>' . $loraIp . '</mark>';
    setParamData('loraIp', $loraIp,'txt');
    echo '<br><br>Setze Call in auf : <mark>' . $callSign . '</mark></b>';
    setParamData('callSign', $callSign,'txt');
    echo "<br><br>";
    echo '<form id="frmParamIp" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
    echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
    echo '<input type="button" class="submitParamLoraIp" id="btnParamReload" value="MeshDash jetzt einmal neu laden"  />';
    echo '</form>';

    echo '</span>';
    exit();
}

function checkLoraIPDb($param)
{
    $loraIP    = getParamData('loraIp');
    $callSign  = getParamData('callSign');
    $debugFlag = $param['debugFlag'] ?? false;

    if (trim($loraIP == '0.0.0.0') || trim($callSign == ''))
    {
        if ($debugFlag === true)
        {
            $errorText = date('Y-m-d H:i:s') . ' doCheckLoraIp. In Database Inhalt mit 0.0.0.0 gefunden' . "\n";
            file_put_contents('log/debug.log', $errorText, FILE_APPEND);
        }

        echo '<form id="frmParamIp" method="post"  action="' . $_SERVER['REQUEST_URI'] . '">';
        echo '<input type="hidden" name="sendData" id="sendData" value="0" />';

        if (trim($loraIP == '0.0.0.0'))
        {
            echo '<br><br><u><b>Die Lora-Ip/mDNS wurde noch nicht gesetzt.</b></u>';
            echo '<br><br>Bitte jetzt die IP oder mDNS angeben:</b>';
            echo '&nbsp;&nbsp;&nbsp;<input type="text" class="inputParamLoraIp" name="paramSetLoraIp" id="paramSetLoraIp" value="" required placeholder="Ip im IPv4 Format" />';
        }

        if (trim($callSign == ''))
        {
            echo '<br><br><u><b>Das Rufzeichen mit SSID wurde noch nicht gesetzt.</b></u>';
            echo '<br><br>Bitte jetzt das Rufzeichen mit der SSID angeben, wie im Lora hinterlegt:';
            echo '&nbsp;&nbsp;&nbsp;<input type="text" class="inputParamLoraIp" name="inputParamCallSign" id="inputParamCallSign" value="" required placeholder="DB0ABC-99" />';
        }

        echo '<br><br><input type="button" class="submitParamLoraIp" id="btnSetParamLoraIp" value="Parameter jetzt setzen"  />';

        echo '</form>';

        exit();
    }
    else
    {
        if ($debugFlag === true)
        {
            $errorText = date('Y-m-d H:i:s') . ' doCheckLoraIp. In DB Inhalt ' . $loraIP . ' gefunden' . "\n";
            file_put_contents('log/debug.log', $errorText, FILE_APPEND);
        }

        return false;
    }
}

function checkExtension($param)
{
    $debugFlag     = $param['debugFlag'];
    $chkExtension1 = $param['chkExtension1'];
    $chkExtension2 = $param['chkExtension2'];
    $osIssWindows  = $param['osIssWindows '] ;

    $phpIniPath = php_ini_loaded_file();

    if ($debugFlag === true)
    {
        $errorText = date('Y-m-d H:i:s') . ' PHP.ini Path on ext1/2 false :' . $phpIniPath . "\n";
        file_put_contents('log/debug.log', $errorText, FILE_APPEND);
    }

    echo '<span class="unsetDisplayFlex">';
    echo "<br>";

    echo '<h2>';
    if ($chkExtension1 === false)
    {
        echo '<br>Bitte vorher die Extension <b><u>pdo_sqlite</u></b> in der php.ini aktivieren';
    }

    if ($chkExtension2 === false)
    {
        echo '<br>Bitte vorher die Extension <b><u>sqlite3</u></b> in der php.ini aktivieren';
    }
    echo '</h2>';

    echo '<br><br><b>';
    echo '<h4><u>Vorgehensweise:</u></h4>';

    if ($osIssWindows === true)
    {
        echo 'In der <b><mark>php.ini</mark></b> die Zeilen <b><mark>extension=php_pdo_sqlite.dll</mark></b> / <b><mark>extension=php_sqlite3.dll</mark></b> suchen.';
        echo '<br>Hier dann das <b><mark>Semikolon davor entfernen</mark></b>';
        echo '<br><br>Nun den Apache/Wamp Dienst zum <b><mark>WebServer neu zu starten</mark></b>.';

        echo '<br><br>Ihre <mark><b>php.ini</b></mark> f&uuml;r findet sie hier:';
        echo '<br><b><mark>' . $phpIniPath . '</mark></b>';

        echo '<br><br><u>Windows-IST:</u><br><mark>;</mark>extension=php_pdo_sqlite.dll<br><mark>;</mark>extension=php_sqlite3.dll';
        echo '<br><br><u>Windows-SOLL:</u><br>extension=php_pdo_sqlite.dll<br>extension=php_sqlite3.dll';
    }
    else
    {
        echo 'Sicherstellen das php mit der "sqlite3" Option installiert ist.';
        echo '<br><b><u>Kommando:</u></b>';
        echo '<br><b><mark><i>sudo apt install php-sqlite3</i></mark></b>';

        echo '<br><br>Ihre <mark><b>php.ini</b></mark> f&uuml;r findet sie hier:';
        echo '<br><b><mark>' . $phpIniPath . '</mark></b>';

        echo '<br><br>In der <b><mark>php.ini</mark></b> die Zeilen <b><mark>extension=pdo_sqlite</mark></b> / <b><mark>extension=sqlite3</mark></b> suchen.';
        echo '<br>Hier dann das <b><mark>Semikolon davor entfernen</mark></b>.';

        echo '<br><br><u>IST:</u><br><mark>;</mark>extension=pdo_sqlite<br><mark>;</mark>extension=php_sqlite3.dll';
        echo '<br><br><u>SOLL:</u><br>extension=php_pdo_sqlite.dll<br>extension=sqlite3';
    }

    echo '</b>';
    echo "<br><br>";

    if ($phpIniPath)
    {
        $fileContent = file($phpIniPath);

        foreach ($fileContent as $lineNumber => $lineContent)
        {
            if ($lineContent == ";extension=sqlite3\n" || $lineContent == ";extension=php_sqlite3.dll\r\n")
            {
                $fileContent[$lineNumber] = 'extension=sqlite3' . "\n";
                echo '<br><b><mark>' . $lineContent . '</mark> gefunden in Zeile <mark>' . $lineNumber . '</mark>. Diesen bitte auskommentieren.</b>';

                if ($debugFlag === true)
                {
                    $errorText = date('Y-m-d H:i:s') . ' Check Extension in PHP.ini Path on ext1/2 false Ext(sqlite3) is inactive: Line' . $lineNumber . ' LineContent' . $lineContent . "\n";
                    file_put_contents('log/debug.log', $errorText, FILE_APPEND);
                }
            }
            else if ($lineContent == "extension=sqlite3\n" || $lineContent == "extension=php_sqlite3.dll\r\n")
            {
                echo '<br><b><mark>' . $lineContent . '</mark> gefunden in Zeile <mark>' . $lineNumber . '</mark> keine &Auml;nderung n&ouml;tig.</b>';

                if ($debugFlag === true)
                {
                    $errorText = date('Y-m-d H:i:s') . ' Check Extension in PHP.ini Path on ext1/2 false Ext(sqlite3) is active: Line' . $lineNumber . ' LineContent' . $lineContent . "\n";
                    file_put_contents('log/debug.log', $errorText, FILE_APPEND);
                }
            }

            if ($lineContent == ";extension=pdo_sqlite\n" || $lineContent == ";extension=php_pdo_sqlite.dll\r\n")
            {
                $fileContent[$lineNumber] = 'extension=pdo_sqlite' . "\n";
                echo '<br><b><mark>' . $lineContent . '</mark> gefunden in Zeile <mark>' . $lineNumber . '</mark>. Diesen bitte auskommentieren.</b>';

                if ($debugFlag === true)
                {
                    $errorText = date('Y-m-d H:i:s') . ' Check Extension in PHP.ini Path on ext1/2 false Ext(pdo_sqlite) is inactive: Line' . $lineNumber . ' LineContent' . $lineContent . "\n";
                    file_put_contents('log/debug.log', $errorText, FILE_APPEND);
                }
            }
            else if ($lineContent == "extension=pdo_sqlite\n" || $lineContent == "extension=php_pdo_sqlite.dll\r\n")
            {
                echo '<br><b><mark>' . $lineContent . '</mark> gefunden in Zeile <mark>' . $lineNumber . '</mark> keine &Auml;nderung n&ouml;tig.</b>';

                if ($debugFlag === true)
                {
                    $errorText = date('Y-m-d H:i:s') . ' Check Extension in PHP.ini Path on ext1/2 false Ext(pdo_sqlite) is active: Line' . $lineNumber . ' LineContent' . $lineContent . "\n";
                    file_put_contents('log/debug.log', $errorText, FILE_APPEND);
                }
            }
        }
    }

    echo '</span>';

    exit();
}

function checkBgProcess($paramBgProcess)
{
    $osIssWindows = $paramBgProcess['osIssWindows'];
    $checkTaskCmd = $paramBgProcess['checkTaskCmd'];

    if ($osIssWindows === true)
    {
        #Beende Hintergrundprozess php.exe in Windows
        exec('taskkill /f /fi "imagename eq php.exe"');
    }
    else
    {
        #Beende Hintergrundprozess in Linux
        #Ermittel PID anhand des Skript-Namens, um
        #andere Bg Prozesse nicht aus Versehen zu beenden.
        $taskResultBg = shell_exec($checkTaskCmd);

        #Wenn PID nicht ermittelt wurde, ist der Task schon beendet
        #oder wurde nicht gestartet.
        if ($taskResultBg == '')
        {
            echo "<br>Kill sektion: Task PID konnte nicht ermittelt werden!";
            echo "<br>checkTaskCmd: $checkTaskCmd";
            echo "<br>taskResult PID: " . $taskResultBg;
        }
        else
        {
            exec('kill -9 ' . $taskResultBg);
        }
    }

    #Gib 1sek Zeit
    sleep(1);

    #Prüfe, ob Prozess wirklich beendet wurde
    $taskResult = shell_exec($checkTaskCmd);

    if ($taskResult != '')
    {
        echo "<br>Task wurde nicht beendet!";
        echo "<br>checkTaskCmd: $checkTaskCmd";
        echo "<br>taskResult PID: " . $taskResult;
    }
    else
    {
        #Open Database
        $db = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
}

function startBgProcess($paramStartBgProcess)
{
    $taskResult   = $paramStartBgProcess['taskResult'];
    $osIssWindows = $paramStartBgProcess['osIssWindows'];
    $checkTaskCmd = $paramStartBgProcess['checkTaskCmd'];

    if (empty($taskResult))
    {
        if($osIssWindows === true)
        {
            #Unter Windows mit Curl Starten
            callWindowsBackgroundTask('udp_receiver.php');
        }
        else
        {
            #Unter Linux direkt starten
            #exec('nohup php test_receiver.php >/dev/null 2>&1 &');
            exec('nohup php udp_receiver.php >/dev/null 2>&1 &');
        }

        sleep(1);
        #Check TaskStatus
        $taskResult = shell_exec($checkTaskCmd);
    }

    return $taskResult;
}

