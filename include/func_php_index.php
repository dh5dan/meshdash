<?php
function initDatabases()
{
    if (!file_exists('database/meshdash.db'))
    {
        initSQLiteDatabase('meshdash');
    }
    else
    {
        checkDbIntegrity('parameter');
        checkDbUpgrade('meshdash');
    }

    if (!file_exists('database/parameter.db'))
    {
        initSQLiteDatabase('parameter');
    }
    else
    {
        checkDbIntegrity('parameter');
    }

    if (!file_exists('database/keywords.db'))
    {
        initSQLiteDatabase('keywords');
    }
    else
    {
        checkDbIntegrity('keywords');
        checkDbUpgrade('keywords');
    }

    if (!file_exists('database/sensordata.db'))
    {
        initSQLiteDatabase('sensordata');
    }
    else
    {
        checkDbIntegrity('sensordata');
        checkDbUpgrade('sensordata');
    }

    if (!file_exists('database/sensor_th_temp.db'))
    {
        initSQLiteDatabase('sensor_th_temp');
    }
    else
    {
        checkDbIntegrity('sensor_th_temp');
    }

    if (!file_exists('database/sensor_th_ina226.db'))
    {
        initSQLiteDatabase('sensor_th_ina226');
    }
    else
    {
        checkDbIntegrity('sensor_th_ina226');
    }

    if (!file_exists('database/mheard.db'))
    {
        initSQLiteDatabase('mheard');
    }
    else
    {
        checkDbIntegrity('mheard');
        checkDbUpgrade('mheard');
    }

    if (!file_exists('database/groups.db'))
    {
        initSQLiteDatabase('groups');
    }
    else
    {
        checkDbIntegrity('groups');
        checkDbUpgrade('groups');
    }

    if (!file_exists('database/tx_queue.db'))
    {
        initSQLiteDatabase('tx_queue');
    }
    else
    {
        checkDbIntegrity('tx_queue');
        checkDbUpgrade('tx_queue');
    }

    if (!file_exists('database/notification.db'))
    {
        initSQLiteDatabase('notification');
    }

    if (!file_exists('database/key_hooks.db'))
    {
        initSQLiteDatabase('key_hooks');
    }

    if (!file_exists('database/beacon.db'))
    {
        initSQLiteDatabase('beacon');
    }
}
function initSQLiteDatabase($database): bool
{
    $osIsWindows = chkOsIsWindows();

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

        #Close and write Back WAL
        $db->close();
        unset($db);

        #Set Index
        addIndex('meshdash', 'meshdash','idx_ack_type_ts', 'msgIsAck, type, timestamps DESC');
        addIndex('meshdash', 'meshdash','idx_check_msg', 'type, dst, timestamps');
        addIndex('meshdash', 'meshdash','idx_ack_ts', 'msgIsAck, timestamps DESC');
        addIndex('meshdash', 'meshdash','idx_ack_dst_ts', 'msgIsAck, dst, timestamps DESC');
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
                        ");

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
                                       ('noTimeSyncMsg', 0 , ''),
                                       ('maxScrollBackRows', 60 , ''),
                                       ('doLogEnable',1 , ''),
                                       ('doNotBackupDb', 0, ''),
                                       ('clickOnCall', 0, ''),
                                       ('chronLogEnable', 0, ''),
                                       ('retentionDays', 7, ''),
                                       ('chronMode', '', 'zip'),
                                       ('strictCallEnable', 0, ''),
                                       ('timeZone', '', 'Europe/Berlin'),
                                       ('sendQueueInterval', '30', ''),
                                       ('cronLoopPid', '', ''),
                                       ('sendQueueMode', 0, ''),
                                       ('soundFileNewMsg', '', 'new_message.wav'),
                                       ('mheardGroup', 0, ''),
                                       ('openStreeTileServerUrl', '', 'tile.openstreetmap.org'),
                                       ('bubbleStyleView', 1, ''),
                                       ('bubbleMaxWidth', 40, '')
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
                                  execScript TEXT,
                                  execTimestamp TEXT,
                                  execTrigger TEXT,
                                  execReturnMsg TEXT,
                                  execGroup INTEGER,
                                  execMsgSend INTEGER DEFAULT 0,
                                  execMsgSendTimestamp TEXT DEFAULT '0000-00-00 00:00:00',
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

        #Close and write Back WAL
        $db->close();
        unset($db);

        #Set Index
        addIndex('mheard', 'mheard','idx_timestamps', 'timestamps, mhTime DESC');
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
                                  groupEnabled INTEGER NOT NULL,
                                  groupSound INTEGER NOT NULL
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

        #Close and write Back WAL
        $db->close();
        unset($db);

        #Set Index
        addIndex('tx_queue', 'txQueue','idx_txFlag_qid', 'txFlag, txQueueId');
    }
    elseif ($database == 'notification')
    {
        #Open Database
        $db = new SQLite3('database/notification.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS notification 
                                (
                                  notifyId INTEGER PRIMARY KEY AUTOINCREMENT,       
                                  notifyCallSign TEXT,
                                  notifySoundFile TEXT,
                                  notifySrcDst INTEGER,
                                  notifyEnabled INTEGER
                                )
                ");

        $db->exec("INSERT INTO notification (
                                          notifyCallSign, 
                                          notifySoundFile, 
                                          notifySrcDst,
                                          notifyEnabled
                                       ) VALUES 
                                       ('DB0ABC-12', 'callsign_src_alert.wav', 0, 0),
                                       ('DB0XYZ-11', 'callsign_dst_alert.wav', 1, 0);
           ");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    elseif ($database == 'key_hooks')
    {
        #Open Database
        $db = new SQLite3('database/key_hooks.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS keyHooks 
                                (
                                  keyHookId INTEGER PRIMARY KEY AUTOINCREMENT,       
                                  keyHookExecute TEXT,
                                  keyHookTrigger TEXT,
                                  keyHookReturnMsg TEXT,
                                  keyHookDmGrpId TEXT,
                                  keyHookEnabled INTEGER
                                )
                         ");

        if ($osIsWindows === true)
        {
            $db->exec(
                "INSERT INTO keyHooks (
                                          keyHookExecute, 
                                          keyHookTrigger, 
                                          keyHookReturnMsg,
                                          keyHookDmGrpId,
                                          keyHookEnabled
                                       ) VALUES 
                                       ('test1.cmd', 'led-on', 'LED ist ON led AN', 999, 0),
                                       ('test2.cmd', 'led-off', 'LED ist OFF led AUS', 999, 0);
                      ");
        }
        else
        {
            $db->exec(
                "INSERT INTO keyHooks (
                                          keyHookExecute, 
                                          keyHookTrigger, 
                                          keyHookReturnMsg,
                                          keyHookDmGrpId,
                                          keyHookEnabled
                                       ) VALUES 
                                       ('led_on.sh', 'led-on', 'LED ist ON led AN', 999, 0),
                                       ('led_off.sh', 'led-off', 'LED ist OFF led AUS', 999, 0);
                      ");
        }

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    elseif ($database == 'beacon')
    {
        #Open Database
        $db = new SQLite3('database/beacon.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS beacon 
                                (
                                  param_key TEXT NOT NULL UNIQUE,              
                                  param_value INTEGER,
                                  param_text TEXT,
                                  PRIMARY KEY(param_key)
                                )
                        ");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }

    return true;
}
function showMenuIcons()
{
    echo '<div id="menu-icon" class="topMenu">&#9776;</div>';
    echo '<div id="menu">';
    echo '<ul>';
       # echo '<li class="menuitem">' . getStatusIcon('configuration', true) . ' ' . getStatusIcon('right_triangle');
    echo '<li class="menuitem with-arrow">'
         . '<span class="menu-left">' . getStatusIcon('configuration', true) . '</span>'
         . '<span class="menu-right">' . getStatusIcon('right_triangle') . '</span>';

         echo '<ul class="submenuIcon">';
                echo '<li class="menuitem" data-action="config_generally">' . getStatusIcon('generally', true) . '</li>';
                echo '<li data-action="config_send_queue">' . getStatusIcon('interval', true) . '</li>';
                echo '<li data-action="config_alerting">' . getStatusIcon('notification', true) . '</li>';
                echo '<li data-action="config_keyword">' . getStatusIcon('keyword', true) . '</li>';
                echo '<li data-action="config_update">' . getStatusIcon('update', true) . '</li>';
                echo '<li data-action="config_restore">' . getStatusIcon('restore', true) . '</li>';
                echo '<li data-action="lora_info">' . getStatusIcon('lora-info', true) . '</li>';
                echo '<li data-action="config_data_purge">' . getStatusIcon('data-purge', true) . '</li>';
                echo '<li data-action="config_ping_lora">' . getStatusIcon('ping-lora', true) . '</li>';
                echo '<li data-action="debug_info">' . getStatusIcon('debug-info', true) . '</li>';
            echo '</ul>';
         echo '</li>';

        echo '<li class="menuitem with-arrow">'
            . '<span class="menu-left">' . getStatusIcon('groups', true) . '</span>'
            . '<span class="menu-right">' . getStatusIcon('right_triangle') . '</span>';

            echo '<ul class="submenuIcon">';
                echo '<li data-action="grp_definition">' . getStatusIcon('groups_define', true) . '</li>';
            echo '</ul>';
        echo '</li>';

        echo '<li class="menuitem with-arrow">'
            . '<span class="menu-left">' . getStatusIcon('sensors', true) . '</span>'
            . '<span class="menu-right">' . getStatusIcon('right_triangle') . '</span>';

            echo '<ul class="submenuIcon">';
                echo '<li data-action="sensor_data">' . getStatusIcon('sensordata', true) . '</li>';
                echo '<li data-action="sensor_threshold">' . getStatusIcon('threshold', true) . '</li>';
                echo '<li data-action="gps_info">' . getStatusIcon('gps', true) . '</li>';
            echo '</ul>';
        echo '</li>';

        echo '<li class="menuitem with-arrow">'
            . '<span class="menu-left">' . getStatusIcon('mheard', true) . '</span>'
            . '<span class="menu-right">' . getStatusIcon('right_triangle') . '</span>';

            echo '<ul class="submenuIcon">';
                echo '<li class="menuitem" data-action="mHeard">' . getStatusIcon('mheard-page', true) . '</li>';
                echo '<li data-action="mHeard-osm">' . getStatusIcon('mheard-osm', true) . '</li>';
            echo '</ul>';
        echo '</li>';

        echo '<li class="menuitem" data-action="beacon">' . getStatusIcon('beacon', true) . '</li>';


    if (function_exists('curl_version'))
    {
        echo '<li class="menuitem" data-action="send_command">' . getStatusIcon('send-cmd', true) . '</li>';
    }

        echo '<li class="menuitem" data-action="message">' . getStatusIcon('message', true) . '</li>';
        echo '<li class="menuitem" data-action="about">' . getStatusIcon('about', true) . '</li>';
    echo '</ul>';
    echo '</div>';
}
function initSetBaseParam()
{
    $loraIp   = trim($_REQUEST['paramSetLoraIp'] ?? '0.0.0.0');
    $callSign = strtoupper(trim($_REQUEST['inputParamCallSign'] ?? 'DB0ABC-99'));

    setParamData('loraIp', $loraIp,'txt');
    setParamData('callSign', $callSign,'txt');

    echo '<span class="unsetDisplayFlex">'
    . '<br><br><b>Setze Ip des Lora Gerätes auf IP: <mark>' . $loraIp . '</mark>'
    . '<br><br>Setze Call in auf : <mark>' . $callSign . '</mark></b>'
    . '<br><br>'
    . '<form id="frmParamIp" method="post" action="' . $_SERVER['REQUEST_URI'] . '">'
    . '<input type="hidden" name="sendData" id="sendData" value="0" />'
    . '<input type="button" class="submitParamLoraIp" id="btnParamReload" value="MeshDash jetzt einmal neu laden"  />'
    . '</form>'
    . '</span>';
    exit();
}
function checkBaseParam($param)
{
    $loraIP    = getParamData('loraIp');
    $callSign  = getParamData('callSign');
    $debugFlag = $param['debugFlag'] ?? false;

    if (trim($loraIP) == '0.0.0.0' || trim($callSign) == '')
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
    $debugFlag     = $param['debugFlag'] ?? false;
    $chkExtension1 = $param['chkExtension1'] ?? false;
    $chkExtension2 = $param['chkExtension2'] ?? false;
    $osIssWindows  = $param['osIssWindows'] ?? false;

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
            $trimmedLine = trim($lineContent);

            if ($trimmedLine == ";extension=sqlite3" || $trimmedLine == ";extension=php_sqlite3.dll")
            {
                echo '<br><b><mark>' .  htmlspecialchars($lineContent) . '</mark> gefunden in Zeile <mark>' . $lineNumber . '</mark>. Diesen bitte auskommentieren.</b>';

                if ($debugFlag === true)
                {
                    $errorText = date('Y-m-d H:i:s') . ' Check Extension in PHP.ini Path on ext1/2 false Ext(sqlite3) is inactive: Line' . $lineNumber . ' LineContent' . $lineContent . "\n";
                    file_put_contents('log/debug.log', $errorText, FILE_APPEND);
                }
            }
            else if ($trimmedLine == "extension=sqlite3" || $trimmedLine == "extension=php_sqlite3.dll")
            {
                echo '<br><b><mark>' .  htmlspecialchars($lineContent) . '</mark> gefunden in Zeile <mark>' . $lineNumber . '</mark> keine &Auml;nderung n&ouml;tig.</b>';

                if ($debugFlag === true)
                {
                    $errorText = date('Y-m-d H:i:s') . ' Check Extension in PHP.ini Path on ext1/2 false Ext(sqlite3) is active: Line' . $lineNumber . ' LineContent' . $lineContent . "\n";
                    file_put_contents('log/debug.log', $errorText, FILE_APPEND);
                }
            }

            if ($trimmedLine == ";extension=pdo_sqlite" || $trimmedLine == ";extension=php_pdo_sqlite.dll")
            {
                echo '<br><b><mark>' .  htmlspecialchars($lineContent) . '</mark> gefunden in Zeile <mark>' . $lineNumber . '</mark>. Diesen bitte auskommentieren.</b>';

                if ($debugFlag === true)
                {
                    $errorText = date('Y-m-d H:i:s') . ' Check Extension in PHP.ini Path on ext1/2 false Ext(pdo_sqlite) is inactive: Line' . $lineNumber . ' LineContent' . $lineContent . "\n";
                    file_put_contents('log/debug.log', $errorText, FILE_APPEND);
                }
            }
            else if ($trimmedLine == "extension=pdo_sqlite" || $trimmedLine == "extension=php_pdo_sqlite.dll")
            {
                echo '<br><b><mark>' .  htmlspecialchars($lineContent) . '</mark> gefunden in Zeile <mark>' . $lineNumber . '</mark> keine &Auml;nderung n&ouml;tig.</b>';

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
function setNewMsgBgColor()
{
    $newMsgBgColor = getParamData('newMsgBgColor');
    $newMsgBgColor = $newMsgBgColor === '' ? '#FFFFFF' : $newMsgBgColor;

    echo '<style>
            .new-message-indicator {
                background-color: ' . $newMsgBgColor . ' !important;
            }
           </style>';
}
function setNewMsgAudioItems(): bool
{
    $resGetGroupParameter = getGroupParameter();
    $groupSoundFile       = getParamData('groupSoundFile');

    if ($groupSoundFile == '')
    {
        return false;
    }

    foreach ($resGetGroupParameter as $groupParameter=>$groupParameterValue)
    {
        if ($groupParameterValue['groupEnabled'] == 1 && $groupParameterValue['groupSound'] == 1)
        {
            $groupId = $groupParameterValue['groupNumber'];
            echo '<audio id="beep_' . $groupId . '" src="sound/' . $groupSoundFile . '" preload="auto"></audio>';
        }

        if ((int) $groupParameter < 0 && $groupParameterValue['groupSound'] == 1)
        {
            echo '<audio id="beep_' . $groupParameter . '" src="sound/' . $groupSoundFile . '" preload="auto"></audio>';
        }
    }

    return true;
}

