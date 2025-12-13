<?php
function initDatabases()
{
    if (!file_exists('database/meshdash.db'))
    {
        initSQLiteDatabase('meshdash');
    }
    else
    {
        checkDbIntegrity('meshdash');
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

    if (!file_exists('database/translation.db'))
    {
        initSQLiteDatabase('translation');
    }
    else
    {
        checkDbIntegrity('translation');
        checkDbUpgrade('translation');
    }

    if (!file_exists('database/call_notice.db'))
    {
        initSQLiteDatabase('call_notice');
    }

    if (!file_exists('database/send_cmd_favorites.db'))
    {
        initSQLiteDatabase('send_cmd_favorites');
    }

    if (!file_exists('database/write_mutex.db'))
    {
        initSQLiteDatabase('write_mutex');
    }
    else
    {
        checkDbUpgrade('write_mutex');
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
                                  beaconEnabledStatusSend INTEGER DEFAULT 0,
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
        addIndex('meshdash', 'meshdash','idx_meshdash_src_type_ts_desc', 'src, type, timestamps DESC');
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
                                       ('cronLoopTs', '', ''),
                                       ('cronBeaconLoopPid', '', ''),
                                       ('cronLoopBeaconTs', '', ''),
                                       ('sendQueueMode', 0, ''),
                                       ('soundFileNewMsg', '', 'new_message.wav'),
                                       ('mheardGroup', 0, ''),
                                       ('openStreeTileServerUrl', '', 'tile.openstreetmap.org'),
                                       ('bubbleStyleView', 1, ''),
                                       ('bubbleMaxWidth', 40, ''),
                                       ('enableMsgPurge', 0, ''),
                                       ('enableMsgPurge', 0, ''),
                                       ('daysMsgPurge', 30, ''),
                                       ('daysSensorPurge', 30, ''),
                                       ('language', '', 'de'),
                                       ('darkMode', 0, ''),
                                       ('mheardCronEnable', 0, ''),
                                       ('mheardCronIntervall', 1, ''),
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
    elseif ($database == 'translation')
    {
        #Open Database
        $db = new SQLite3('database/translation.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS translation 
                                (
                                  key TEXT NOT NULL UNIQUE,              
                                  de TEXT NOT NULL,
                                  en TEXT,
                                  fr TEXT,
                                  es TEXT,
                                  it TEXT,
                                  nl TEXT,
                                  PRIMARY KEY(key)
                                )
                        ");

        $db->exec("REPLACE INTO translation (
                                          key, 
                                          de, 
                                          en,
                                          fr,
                                          es,
                                          it,
                                          nl
                                       ) VALUES 
                                       ('menu.einstellung', 'Einstellung', 'Settings', 'Paramètres', 'Configuración', 'Impostazioni', 'Instellingen'),
                                       ('menu.allgemein', 'Allgemein', 'General', 'Général', 'General', 'Generale', 'Algemeen'),
                                       ('menu.send-queue', 'Send-Queue', 'Send Queue', 'File d''envoi', 'Cola de envío', 'Coda di invio', 'Verzendwachtrij'),
                                       ('menu.notification', 'Notification', 'Notifications', 'Notifications', 'Notificaciones', 'Notifiche', 'Meldingen'),
                                       ('menu.keyword', 'Keyword', 'Keyword', 'Mot-clé', 'Palabra clave', 'Parola chiave', 'Trefwoord'),
                                       ('menu.update', 'Update', 'Update', 'Mise à jour', 'Actualización', 'Aggiornamento', 'Update'),
                                       ('menu.restore', 'Restore', 'Restore', 'Restauration', 'Restaurar', 'Ripristina', 'Herstellen'),
                                       ('menu.lora-info', 'Lora-Info', 'LoRa Info', 'Infos LoRa', 'Información LoRa', 'Info LoRa', 'LoRa-info'),
                                       ('menu.ping-lora', 'Ping Lora', 'Ping LoRa', 'Ping LoRa', 'Ping LoRa', 'Ping LoRa', 'Ping LoRa'),
                                       ('menu.debug-info', 'Debug-Info', 'Debug Info', 'Infos de débogage', 'Información de depuración', 'Informazioni di debug', 'Debug-info'),
                                       ('menu.edit_translation', 'Lang-Editor', 'Language Editor', 'Éditeur de langue', 'Editor de idioma', 'Editor lingua', 'Taalbewerker'),
                                       
                                       ('menu.gruppen', 'Gruppen', 'Groups', 'Groupes', 'Grupos', 'Gruppi', 'Groepen'),
                                       ('menu.gruppenfilter', 'Gruppenfilter', 'Group Filter', 'Filtre de groupes', 'Filtro de grupos', 'Filtro gruppi', 'Groepsfilter'),
                                        
                                       ('menu.sensoren', 'Sensoren', 'Sensors', 'Capteurs', 'Sensores', 'Sensori', 'Sensoren'),
                                       ('menu.sensordaten', 'Sensordaten', 'Sensor Data', 'Données capteurs', 'Datos de sensores', 'Dati sensore', 'Sensorgegevens'),
                                       ('menu.schwellwerte', 'Schwellwerte', 'Thresholds', 'Seuils', 'Umbrales', 'Soglie', 'Drempels'),
                                       ('menu.plot', 'Auswertung', 'Analysis', 'Analyse', 'Evaluación', 'Valutazione', 'Analyse'),
                                       ('menu.gps-info', 'GPS-Info', 'GPS Info', 'Infos GPS', 'Info GPS', 'Info GPS', 'GPS-info'),
                                        
                                       ('menu.mheard', 'MHeard', 'MHeard', 'MHeard', 'MHeard', 'MHeard', 'MHeard'),
                                       ('menu.mheard-Lokal', 'MHeard-Lokal', 'MHeard Local', 'MHeard local', 'MHeard local', 'MHeard locale', 'MHeard lokaal'),
                                       ('menu.mheard-Map', 'MHeard-Map', 'MHeard Map', 'Carte MHeard', 'Mapa MHeard', 'Mappa MHeard', 'MHeard-kaart'),
                                        
                                       ('menu.data-purge', 'Data-Purge', 'Data Purge', 'Purge de données', 'Purgado de datos', 'Eliminazione dati', 'Gegevensopschoning'),
                                       ('menu.purge-manuell', 'Purge Manuell', 'Manual Purge', 'Purge manuelle', 'Purgado manual', 'Eliminazione manuale', 'Handmatige opschoning'),
                                       ('menu.purge-auto', 'Purge Auto', 'Automatic Purge', 'Purge automatique', 'Purgado automático', 'Eliminazione automatica', 'Automatische opschoning'),
                                        
                                       ('menu.bake', 'Bake', 'Bake', 'Bake', 'Bake', 'Bake', 'Bake'),
                                       
                                       ('menu.sende-befehl', 'Sende Befehl', 'Send Command', 'Envoyer commande', 'Enviar comando', 'Invia comando', 'Commando verzenden'),
                                        
                                       ('menu.message', 'Message', 'Message', 'Message', 'Mensaje', 'Messaggio', 'Bericht'),
                                       ('menu.about', 'About', 'About', 'À propos', 'Acerca de', 'Informazioni', 'Over'),
                                     
                                       ('submenu.config_generally.lbl.title', 'Basiseinstellungen', 'Config-Generally', 'Paramètres de base', 'Configuración básica', 'Impostazioni di base', 'Basisinstellingen'),
                                       ('submenu.config_generally.lbl.architecture', 'Architektur', 'Architecture', 'Architecture', 'Arquitectura', 'Architettura', 'Architectuur'),
                                       ('submenu.config_generally.lbl.pos-msg', 'POS-Meldungen &#10140;[AUS]', 'POS-Message &#10140;[OFF]', 'Message POS &#10140;[OFF]', 'Mensaje POS &#10140;[OFF]', 'Messaggio POS &#10140;[OFF]', 'POS-bericht &#10140;[OFF]'),
                                       ('submenu.config_generally.lbl.dm-alert-global', 'DM-Alert global &#10140;[AUS]', 'DM-Alert global &#10140;[OFF]', 'Alerte DM globale &#10140;[OFF]', 'Alerta DM global &#10140;[OFF]', 'Allerta DM globale &#10140;[OFF]', 'DM-waarschuwing globaal &#10140;[OFF]'),
                                       ('submenu.config_generally.lbl.time-sync-msg', 'Time Sync-Meldung &#10140;[AUS]', 'Time Sync-Message &#10140;[OFF]', 'Message de synchronisation horaire &#10140;[OFF]', 'Mensaje de sincronización de tiempo &#10140;[OFF]', 'Messaggio di sincronizzazione oraria &#10140;[OFF]', 'Tijdsync-bericht &#10140;[OFF]'),
                                       ('submenu.config_generally.lbl.db-backup', 'DB-Backup &#10140;[AUS]', 'DB-Backup &#10140;[OFF]', 'Sauvegarde BD &#10140;[OFF]', 'Copia de seguridad BD &#10140;[OFF]', 'Backup DB &#10140;[OFF]', 'DB-back-up &#10140;[OFF]'),
                                       ('submenu.config_generally.lbl.bubble-style', 'Bubble-Style &#10140;[AN]', 'Bubble-Style &#10140;[ON]', 'Style bulle &#10140;[ON]', 'Estilo burbuja &#10140;[ON]', 'Stile bolla &#10140;[ON]', 'Bubbelstijl &#10140;[ON]'),
                                       ('submenu.config_generally.lbl.bubble-max-width', 'Bubble Max-Breite (40-100%)', 'Bubble Max-Width (40-100%)', 'Largeur max bulle (40-100%)', 'Ancho máx burbuja (40-100%)', 'Larghezza max bolla (40-100%)', 'Max. bubbelbreedte (40-100%)'),
                                       ('submenu.config_generally.lbl.timezone-dst', 'Zeitzone (DST)', 'Timezone (DST)', 'Fuseau horaire (DST)', 'Zona horaria (DST)', 'Fuso orario (DST)', 'Tijdzone (DST)'),
                                       ('submenu.config_generally.lbl.log-mode', 'Logfile-Erstellung &#10140;[AN]', 'Logfile-Creation &#10140;[ON]', 'Création de journal &#10140;[ON]', 'Creación de registro &#10140;[ON]', 'Creazione log &#10140;[ON]', 'Logbestand creatie &#10140;[ON]'),
                                       ('submenu.config_generally.lbl.log-rotation', 'Log-Rotation &#10140;[AN]', 'Log-Rotation &#10140;[ON]', 'Rotation de journal &#10140;[ON]', 'Rotación de registro &#10140;[ON]', 'Rotazione log &#10140;[ON]', 'Logrotatie &#10140;[ON]'),
                                       ('submenu.config_generally.lbl.max-hold-days', 'Aufbewahrungstage', 'Retention days', 'Jours de conservation', 'Días de retención', 'Giorni di conservazione', 'Bewaartermijn (dagen)'),
                                       ('submenu.config_generally.lbl.log-rotate-mode', 'Log-Rotation Modus', 'Log-Rotation Mode', 'Mode de rotation du journal', 'Modo de rotación del registro', 'Modalità rotazione log', 'Logrotatie modus'),
                                       ('submenu.config_generally.lbl.save-zip-archive', 'in Zip-Archiv speichern', 'save in Zip-Archiv', 'enregistrer dans une archive Zip', 'guardar en archivo Zip', 'salva in archivio Zip', 'opslaan in Zip-archief'),
                                       ('submenu.config_generally.lbl.del-log-now', 'sofort löschen', 'delete immediately', 'supprimer immédiatement', 'eliminar inmediatamente', 'elimina subito', 'onmiddellijk verwijderen'),
                                       ('submenu.config_generally.lbl.filter-strict-call', 'Filter Strict-Call &#10140;[AN]', 'Filter Strict-Call &#10140;[ON]', 'Filtre Strict-Call &#10140;[ON]', 'Filtro Strict-Call &#10140;[ON]', 'Filtro Strict-Call &#10140;[ON]', 'Filter Strict-Call &#10140;[ON]'),
                                       ('submenu.config_generally.lbl.click-on-call', 'Klick auf Call', 'Click on Call', 'Clic sur Call', 'Clic en Call', 'Clic su Call', 'Klik op Call'),
                                       ('submenu.config_generally.lbl.dm-call-click', 'Setzt Call in DM-Feld', 'Set Call into DM-Feld', 'Insérer Call dans champ DM', 'Insertar Call en campo DM', 'Inserisci Call nel campo DM', 'Zet Call in DM-veld'),
                                       ('submenu.config_generally.lbl.open-qrz-on-click', 'Öffnet QRZ.com', 'Open QRZ.com', 'Ouvrir QRZ.com', 'Abrir QRZ.com', 'Apri QRZ.com', 'Open QRZ.com'),
                                       ('submenu.config_generally.lbl.at-call-on-click', 'Setzt @Call in Msg-Feld', 'Set @Call into Msg-Feld', 'Insérer @Call dans champ Msg', 'Insertar @Call en campo Msg', 'Inserisci @Call nel campo Msg', 'Zet @Call in Msg-veld'),
                                       ('submenu.config_generally.lbl.called-mh-grp', 'Anfrage Mheard-Gruppe', 'Called Mheard-Group', 'Demande groupe Mheard', 'Solicitud grupo Mheard', 'Richiesta gruppo Mheard', 'Aanvraag Mheard-groep'),
                                       ('submenu.config_generally.lbl.max-scroll-back', 'Max. Scroll-Back ###REPLACE###Reihen (30-200)', 'Max. Scroll-Back ###REPLACE###Rows (30-200)', 'Défilement max. ###REPLACE###lignes (30-200)', 'Máx. retroceso ###REPLACE###filas (30-200)', 'Max. retro ###REPLACE###righe (30-200)', 'Max. terugscrollen ###REPLACE###rijen (30-200)'),
                                       ('submenu.config_generally.lbl.lora-ip-mdns', 'LoraIP/mDNS', 'LoraIP/mDNS', 'LoraIP/mDNS', 'LoraIP/mDNS', 'LoraIP/mDNS', 'LoraIP/mDNS'),
                                       ('submenu.config_generally.lbl.call-ssid', 'Rufzeichen mit SSID', 'Call-Sign with SSID', 'Indicatif avec SSID', 'Indicativo con SSID', 'Call-Sign con SSID', 'Roepletter met SSID'),
                                       ('submenu.config_generally.lbl.background-color-new-msg', 'Hintergrundfarbe ###REPLACE###<b>New MessagesNeue Nachrichten</b>', 'Background-Color ###REPLACE###<b>New Messages</b>', 'Couleur de fond ###REPLACE###<b>Nouveaux messages</b>', 'Color de fondo ###REPLACE###<b>Nuevos mensajes</b>', 'Colore di sfondo ###REPLACE###<b>Nuovi messaggi</b>', 'Achtergrondkleur ###REPLACE###<b>Nieuwe berichten</b>'),
                                       ('submenu.config_generally.lbl.osm-tile-url', 'OpenStreet Tile-Url', 'OpenStreet Tile-Url', 'OpenStreet Tile-Url', 'OpenStreet Tile-Url', 'OpenStreet Tile-Url', 'OpenStreet Tile-Url'),
                                       ('submenu.config_generally.lbl.speech', 'Sprache', 'Language', 'Langue', 'Idioma', 'Lingua', 'Taal'),
                                       ('submenu.config_generally.lbl.call-hint', 'Das Rufzeichen muss mit der Angabe<br>im Lora übereinstimmen!', 'The call sign must match<br>the information in the Lora!', 'L’indicatif doit correspondre<br>aux informations dans le Lora !', 'El indicativo debe coincidir<br>con la información en el Lora!', 'Il nominativo deve corrispondere<br>alle informazioni nel Lora!', 'Het roepletter moet overeenkomen<br>met de informatie in de Lora!'),
                                       ('submenu.config_generally.btn.save-settings', 'Settings speichern', 'Save Settings', 'Enregistrer paramètres', 'Guardar configuración', 'Salva impostazioni', 'Instellingen opslaan'),
                                       ('submenu.config_generally.msg.save-settings-success', 'Settings wurden erfolgreich abgespeichert!', 'Settings saved successfully!', 'Paramètres enregistrés avec succès !', '¡Configuración guardada con éxito!', 'Impostazioni salvate con successo!', 'Instellingen succesvol opgeslagen!'),
                                       ('submenu.config_generally.msg.save-settings-failed', 'Es gab einen Fehler beim Abspeichern der Settings!', 'There was an error saving the settings!', 'Erreur lors de l’enregistrement des paramètres !', '¡Error al guardar la configuración!', 'Errore durante il salvataggio delle impostazioni!', 'Fout bij het opslaan van de instellingen!'),

                                       ('submenu.send_queue.lbl.title', 'Sende-Queue', 'Transmission-Queue', 'File d''attente de transmission', 'Cola de transmisión', 'Coda di trasmissione', 'Transmissiewachtrij'),
                                       ('submenu.send_queue.lbl.send-intervall', 'Sendeintervall (Sek.)', 'Transmission interval (sec.)', 'Intervalle de transmission (sec.)', 'Intervalo de transmisión (seg.)', 'Intervallo di trasmissione (sec.)', 'Transmissie-interval (sec.)'),
                                       ('submenu.send_queue.lbl.queue-enabled', 'Sende-Queue enabled', 'Transmission-Queue enabled', 'File d''attente activée', 'Cola de transmisión habilitada', 'Coda di trasmissione abilitata', 'Transmissiewachtrij ingeschakeld'),
                                       ('submenu.send_queue.lbl.send-cron-status', 'Sende-Cron Status', 'Transmission-Cron Status', 'Statut du Cron de transmission', 'Estado del Cron de transmisión', 'Stato del Cron di trasmissione', 'Status van de transmissie-Cron'),
                                       ('submenu.send_queue.btn.save-settings', 'Settings speichern', 'Save Settings', 'Enregistrer les paramètres', 'Guardar ajustes', 'Salva impostazioni', 'Instellingen opslaan'),
                                       ('submenu.send_queue.msg.save-settings-success', 'Settings wurden erfolgreich abgespeichert!', 'Settings saved successfully!', 'Paramètres enregistrés avec succès !', '¡Ajustes guardados correctamente!', 'Impostazioni salvate con successo!', 'Instellingen succesvol opgeslagen!'),
                                       ('submenu.send_queue.msg.save-settings-failed', 'Es gab einen Fehler beim Abspeichern der Settings!', 'There was an error saving the settings!', 'Une erreur est survenue lors de l''enregistrement des paramètres !', '¡Se produjo un error al guardar los ajustes!', 'Si è verificato un errore durante il salvataggio delle impostazioni!', 'Fout bij het opslaan van de instellingen!'),
                                        
                                       ('submenu.config_alerting.lbl.title', 'Benachrichtigung', 'Notification', 'Notification', 'Notificación', 'Notifica', 'Melding'),
                                       ('submenu.config_alerting.lbl.snd-file', 'Snd-File', 'Snd-File', 'Fichier Snd', 'Archivo Snd', 'File Snd', 'Snd-bestand'),
                                       ('submenu.config_alerting.lbl.callsign', 'Rufzeichen', 'CallSign', 'Indicatif', 'Indicativo', 'Indicativo', 'Rufsignaal'),
                                       ('submenu.config_alerting.lbl.src-dst', 'Src/Dst', 'Src/Dst', 'Src/Dst', 'Src/Dst', 'Src/Dst', 'Src/Dst'),
                                       ('submenu.config_alerting.btn.new-item', 'Neuer Eintrag', 'New Item', 'Nouvel élément', 'Nuevo elemento', 'Nuovo elemento', 'Nieuw item'),
                                       ('submenu.config_alerting.btn.save-settings', 'Settings speichern', 'Save Settings', 'Enregistrer les paramètres', 'Guardar ajustes', 'Salva impostazioni', 'Instellingen opslaan'),
                                       ('submenu.config_alerting.msg.save-settings-success', 'Settings wurden erfolgreich abgespeichert!', 'Settings saved successfully!', 'Paramètres enregistrés avec succès !', '¡Ajustes guardados correctamente!', 'Impostazioni salvate con successo!', 'Instellingen succesvol opgeslagen!'),
                                       ('submenu.config_alerting.msg.save-settings-failed', 'Es gab einen Fehler beim Abspeichern der Settings!', 'There was an error saving the settings!', 'Une erreur est survenue lors de l''enregistrement des paramètres !', '¡Se produjo un error al guardar los ajustes!', 'Si è verificato un errore durante il salvataggio delle impostazioni!', 'Fout bij het opslaan van de instellingen!'),
                                        
                                       ('submenu.config_keyword.lbl.title', 'Keyword-Definition', 'Keyword-Definition', 'Définition du mot-clé', 'Definición de palabra clave', 'Definizione parola chiave', 'Trefwoorddefinitie'),
                                       ('submenu.config_keyword.lbl.keyword', 'Keyword', 'Keyword', 'Mot-clé', 'Palabra clave', 'Parola chiave', 'Trefwoord'),
                                       ('submenu.config_keyword.lbl.start-script', 'Start-Skript', 'Start-Script', 'Script de démarrage', 'Script de inicio', 'Script di avvio', 'Startscript'),
                                       ('submenu.config_keyword.lbl.status-feedback-msg', 'Statusrückmeldung', 'Status-Feedback', 'Retour d''état', 'Retroalimentación de estado', 'Feedback di stato', 'Statusfeedback'),
                                       ('submenu.config_keyword.lbl.dm-group-rx-tx', 'DM-Gruppe: RX/TX', 'DM-Group: RX/TX', 'Groupe DM : RX/TX', 'Grupo DM: RX/TX', 'Gruppo DM: RX/TX', 'DM-Groep: RX/TX'),
                                       ('submenu.config_keyword.btn.new-item', 'Neuer Eintrag', 'New Item', 'Nouvel élément', 'Nuevo elemento', 'Nuovo elemento', 'Nieuw item'),
                                       ('submenu.config_keyword.btn.save-settings', 'Settings speichern', 'Save Settings', 'Enregistrer les paramètres', 'Guardar ajustes', 'Salva impostazioni', 'Instellingen opslaan'),
                                       ('submenu.config_keyword.btn.upload-file', 'Datei hochladen', 'Upload File', 'Téléverser le fichier', 'Subir archivo', 'Carica file', 'Bestand uploaden'),
                                       ('submenu.config_keyword.lbl.add-script', 'Skript hinzufügen', 'Add Script', 'Ajouter un script', 'Agregar script', 'Aggiungi script', 'Script toevoegen'),
                                       ('submenu.config_keyword.lbl.upload-script', 'Skript hochladen', 'Upload Script', 'Téléverser le script', 'Subir script', 'Carica script', 'Script uploaden'),
                                       ('submenu.config_keyword.lbl.date', 'Datum', 'Date', 'Date', 'Fecha', 'Data', 'Datum'),
                                       ('submenu.config_keyword.lbl.time', 'Uhrzeit', 'Time', 'Heure', 'Hora', 'Ora', 'Tijd'),
                                       ('submenu.config_keyword.lbl.script-file', 'Skript-Datei', 'Script-File', 'Fichier script', 'Archivo de script', 'File script', 'Scriptbestand'),
                                       ('submenu.config_keyword.msg.save-settings-success', 'Settings wurden erfolgreich abgespeichert!', 'Settings saved successfully!', 'Paramètres enregistrés avec succès !', '¡Ajustes guardados correctamente!', 'Impostazioni salvate con successo!', 'Instellingen succesvol opgeslagen!'),
                                       ('submenu.config_keyword.msg.save-settings-failed', 'Es gab einen Fehler beim Abspeichern der Settings!', 'There was an error saving the settings!', 'Une erreur est survenue lors de l''enregistrement des paramètres !', '¡Se produjo un error al guardar los ajustes!', 'Si è verificato un errore durante il salvataggio delle impostazioni!', 'Fout bij het opslaan van de instellingen!'),
                                        
                                       ('submenu.config_update.lbl.title', 'MeshDash-SQL Update', 'MeshDash-SQL Update', 'Mise à jour MeshDash-SQL', 'Actualización de MeshDash-SQL', 'Aggiornamento MeshDash-SQL', 'MeshDash-SQL Update'),
                                       ('submenu.config_update.lbl.subtitle', '(Update-Datei muss im MeshDash-SQL Format ###REPLACE###als Zip vorliegen.)', '(Update-File must be in MeshDash-SQL Format ###REPLACE###as Zip-File.)', '(Le fichier de mise à jour doit être au format MeshDash-SQL ###REPLACE###en tant que fichier zip.)', '(El archivo de actualización debe estar en formato MeshDash-SQL ###REPLACE###como archivo zip.)', '(Il file di aggiornamento deve essere nel formato MeshDash-SQL ###REPLACE###come file zip.)', '(Update-bestand moet in MeshDash-SQL-formaat ###REPLACE###als zip-bestand liggen.)'),
                                       ('submenu.config_update.lbl.choose-zip-file', 'Wähle das Update (Zip-Datei)', 'Select the update (zip file)', 'Sélectionnez la mise à jour (fichier zip)', 'Seleccione la actualización (archivo zip)', 'Seleziona l''aggiornamento (file zip)', 'Selecteer de update (zip-bestand)'),
                                       ('submenu.config_update.lbl.load-latest-release', 'Lade aktuelles Release von GitHub herunter', 'Download the latest release from GitHub', 'Télécharger la dernière version depuis GitHub', 'Descargar la última versión desde GitHub', 'Scarica l''ultima release da GitHub', 'Download de laatste release van GitHub'),
                                       ('submenu.config_update.lbl.show-release-info', 'Zeige Changelog zur aktuellen Release-Version', 'Show changelog for the current release version', 'Afficher le changelog pour la version actuelle', 'Mostrar changelog de la versión actual', 'Mostra changelog della versione corrente', 'Toon changelog voor de huidige releaseversie'),
                                       ('submenu.config_update.btn.load-latest-release', 'Lade letzte Release-Version', 'Download latest release version', 'Télécharger la dernière version', 'Descargar la última versión', 'Scarica l''ultima release', 'Download laatste release versie'),
                                       ('submenu.config_update.btn.view-changelog', 'Release Changelog anzeigen', 'View release changelog', 'Afficher le changelog de la release', 'Ver changelog de la release', 'Mostra changelog della release', 'Bekijk release changelog'),
                                       ('submenu.config_update.btn.backup-only', 'Nur Backup anlegen', 'Create backup only', 'Créer uniquement une sauvegarde', 'Crear solo copia de seguridad', 'Crea solo backup', 'Alleen backup aanmaken'),
                                       ('submenu.config_update.btn.upload-update', 'Update Hochladen', 'Upload Update', 'Téléverser la mise à jour', 'Subir actualización', 'Carica aggiornamento', 'Update uploaden'),
                                       ('submenu.config_update.lbl.date', 'Datum', 'Date', 'Date', 'Fecha', 'Data', 'Datum'),
                                       ('submenu.config_update.lbl.time', 'Uhrzeit', 'Time', 'Heure', 'Hora', 'Ora', 'Tijd'),
                                       ('submenu.config_update.lbl.backup-file', 'Backup-Datei', 'Backup-File', 'Fichier de sauvegarde', 'Archivo de respaldo', 'File di backup', 'Backup-bestand'),
                                       ('submenu.config_update.lbl.reload', 'MeshDash hier neu laden!', 'Reload MeshDash now!', 'Recharger MeshDash maintenant !', '¡Recargar MeshDash ahora!', 'Ricarica MeshDash ora!', 'MeshDash nu herladen!'),

                                       ('submenu.config_restore.lbl.title', 'MeshDash-SQL Restore', 'MeshDash-SQL Restore', 'Restauration MeshDash-SQL', 'Restauración MeshDash-SQL', 'Ripristino MeshDash-SQL', 'MeshDash-SQL herstel'),
                                       ('submenu.config_restore.lbl.subtitle', '(Restore-Datei muss im MeshDash-SQL Format ###REPLACE###als Zip vorliegen.)', '(Restore-File must be in MeshDash-SQL Format ###REPLACE###as Zip-File.)', '(Le fichier de restauration doit être au format MeshDash-SQL ###REPLACE###en tant que fichier zip.)', '(El archivo de restauración debe estar en formato MeshDash-SQL ###REPLACE###como archivo zip.)', '(Il file di restore deve essere nel formato MeshDash-SQL ###REPLACE###come file zip.)', '(Restore-bestand moet in MeshDash-SQL-formaat ###REPLACE###als zip-bestand liggen.)'),
                                       ('submenu.config_restore.lbl.choose-zip-file', 'Wähle die Restore (Zip-Datei)', 'Select the restore (zip file)', 'Sélectionnez la restauration (fichier zip)', 'Seleccione la restauración (archivo zip)', 'Seleziona il restore (file zip)', 'Selecteer de restore (zip-bestand)'),
                                       ('submenu.config_restore.lbl.reload', 'MeshDash hier neu laden!', 'Reload MeshDash now!', 'Recharger MeshDash maintenant !', '¡Recargar MeshDash ahora!', 'Ricarica MeshDash ora!', 'MeshDash nu herladen!'),
                                       ('submenu.config_restore.btn.upload-restore', 'Restore Hochladen', 'Upload Restore', 'Téléverser la restauration', 'Subir restauración', 'Carica restore', 'Restore uploaden'),
                                        
                                       ('submenu.lora_info.lbl.title', 'Lora-Infoseite', 'Lora Info-Page', 'Page d''info Lora', 'Página de info Lora', 'Pagina info Lora', 'Lora-informatiepagina'),
                                       ('submenu.lora_info.lbl.load-page', 'Infoseite laden', 'Load Info-Page', 'Charger la page d''info', 'Cargar página de info', 'Carica pagina info', 'Laad informatiepagina'),
                                       ('submenu.lora_info.lbl.load-page-new', 'Infoseite neu laden', 'Load Info-Page again', 'Recharger la page d''info', 'Recargar página de info', 'Ricarica nuovamente la pagina info', 'Laad informatiepagina opnieuw'),
                                        
                                       ('submenu.ping_lora.lbl.title', 'Ping Lora', 'Ping Lora', 'Ping Lora', 'Ping Lora', 'Ping Lora', 'Ping Lora'),
                                       ('submenu.ping_lora.btn.ping', 'Ping jetzt ausführen', 'Execute Ping now', 'Exécuter le ping maintenant', 'Ejecutar ping ahora', 'Esegui ping ora', 'Voer ping nu uit'),
                                        
                                       ('submenu.debug_info.lbl.title', 'Debug-Info zu MeshDash-SQL', 'Debug-Info zu MeshDash-SQL', 'Info de débogage MeshDash-SQL', 'Info de depuración MeshDash-SQL', 'Info di debug MeshDash-SQL', 'Debug-info MeshDash-SQL'),
                                       ('submenu.debug_info.lbl.os', 'OS', 'OS', 'OS', 'SO', 'SO', 'OS'),
                                       ('submenu.debug_info.lbl.architecture', 'Architektur', 'Architecture', 'Architecture', 'Arquitectura', 'Architettura', 'Architectuur'),
                                       ('submenu.debug_info.lbl.release', 'Release', 'Release', 'Release', 'Versión', 'Release', 'Release'),
                                       ('submenu.debug_info.lbl.hardware', 'Hardware', 'Hardware', 'Matériel', 'Hardware', 'Hardware', 'Hardware'),
                                       ('submenu.debug_info.lbl.tx-interval', 'Sendeintervall (Sek.)', 'Transmitting Interval (Sec.)', 'Intervalle d''envoi (sec.)', 'Intervalo de transmisión (seg.)', 'Intervallo di trasmissione (sec.)', 'Transmissie-interval (sec.)'),
                                       ('submenu.debug_info.lbl.queue-status', 'Send-Queue Status', 'Transmitting-Queue Status', 'Statut de la file d''attente', 'Estado de la cola de transmisión', 'Stato della coda di trasmissione', 'Status van de transmissiewachtrij'),
                                       ('submenu.debug_info.lbl.php-version', 'Aktuelle PHP-Version', 'Current PHP-Version', 'Version PHP actuelle', 'Versión PHP actual', 'Versione PHP corrente', 'Huidige PHP-versie'),
                                       ('submenu.debug_info.lbl.pdo-sqlite', 'PHP-Extension <b>pdo_sqlite</b> geladen', 'PHP-Extension <b>pdo_sqlite</b> loaded', 'Extension PHP <b>pdo_sqlite</b> chargée', 'Extensión PHP <b>pdo_sqlite</b> cargada', 'Estensione PHP <b>pdo_sqlite</b> caricata', 'PHP-extensie <b>pdo_sqlite</b> geladen'),
                                       ('submenu.debug_info.lbl.sqlite', 'PHP-Extension <b>sqlite3</b> geladen', 'PHP-Extension <b>sqlite3</b> loaded', 'Extension PHP <b>sqlite3</b> chargée', 'Extensión PHP <b>sqlite3</b> cargada', 'Estensione PHP <b>sqlite3</b> caricata', 'PHP-extensie <b>sqlite3</b> geladen'),
                                       ('submenu.debug_info.lbl.webserver', 'Webserver', 'Webserver', 'Serveur web', 'Servidor web', 'Webserver', 'Webserver'),
                                       ('submenu.debug_info.lbl.uptime', 'System Uptime', 'System Uptime', 'Temps de fonctionnement du système', 'Tiempo de actividad del sistema', 'Uptime del sistema', 'Systeem uptime'),
                                       ('submenu.debug_info.lbl.cpu-load', 'Rechnerauslastung', 'CPU-Load', 'Charge CPU', 'Carga de CPU', 'Carico CPU', 'CPU-belasting'),
                                       ('submenu.debug_info.lbl.node-firmware', 'Lora-Node GUI-Status', 'Lora-Node GUI-Status', 'État GUI du nœud Lora', 'Estado GUI del nodo Lora', 'Stato GUI del nodo Lora', 'Lora-Node GUI-status'),
                                       ('submenu.debug_info.lbl.udp-bg-status', 'UDP-Receiver BG-Status', 'UDP-Receiver BG-Status', 'Statut BG du récepteur UDP', 'Estado BG del receptor UDP', 'Stato BG del ricevitore UDP', 'UDP-Receiver BG-status'),
                                       ('submenu.debug_info.lbl.udp-bg-task', 'UDP-Receiver BG-Task', 'UDP-Receiver BG-Task', 'Tâche BG du récepteur UDP', 'Tarea BG del receptor UDP', 'Task BG del ricevitore UDP', 'UDP-Receiver BG-task'),
                                       ('submenu.debug_info.lbl.udp-bg-timestamp', 'UDP-Receiver BG-Timestamp', 'UDP-Receiver BG-Timestamp', 'Timestamp BG du récepteur UDP', 'Marca de tiempo BG del receptor UDP', 'Timestamp BG del ricevitore UDP', 'UDP-Receiver BG-timestamp'),
                                       ('submenu.debug_info.lbl.cron-bg-status', 'Cron-Loop BG-Status', 'Cron-Loop BG-Status', 'Statut BG du Cron-Loop', 'Estado BG del bucle Cron', 'Stato BG del Cron-Loop', 'Cron-Loop BG-status'),
                                       ('submenu.debug_info.lbl.cron-bg-task', 'Cron-Loop BG-Task', 'Cron-Loop BG-Task', 'Tâche BG du Cron-Loop', 'Tarea BG del bucle Cron', 'Task BG del Cron-Loop', 'Cron-Loop BG-task'),
                                       ('submenu.debug_info.lbl.cron-bg-timestamp', 'Cron-Loop BG-Timestamp', 'Cron-Loop BG-Timestamp', 'Timestamp BG du Cron-Loop', 'Marca de tiempo BG del bucle Cron', 'Timestamp BG del Cron-Loop', 'Cron-Loop BG-timestamp'),
                                       ('submenu.debug_info.lbl.php-value', 'PHP-Values', 'PHP-Values', 'Valeurs PHP', 'Valores PHP', 'Valori PHP', 'PHP-waarden'),
                                       ('submenu.debug_info.lbl.dir-writeable', 'Schreibstatus Directory', 'Directory Write-Status', 'État d''écriture du répertoire', 'Estado de escritura del directorio', 'Stato di scrittura della directory', 'Directory schrijfstatus'),

                                       
                                       ('submenu.debug_info.lbl.databases', 'Datenbanken', 'Databases', 'Bases de données', 'Bases de datos', 'Database', 'Databases'),
                                       ('submenu.debug_info.lbl.dir-status-writable', 'Beschreibbar', 'Writable', 'Écriture possible', 'Escribible', 'Scrivibile', 'Schrijfbaar'),
                                       ('submenu.debug_info.lbl.dir-status-readonly', 'Nur lesend', 'Read-Only', 'Lecture seule', 'Solo lectura', 'Sola lettura', 'Alleen-lezen'),
                                       ('submenu.debug_info.lbl.last-access', 'Letzter Zugriff', 'Last access', 'Dernier accès', 'Último acceso', 'Ultimo accesso', 'Laatste toegang'),
                                       ('submenu.debug_info.lbl.date', 'Datum', 'Date', 'Date', 'Fecha', 'Data', 'Datum'),
                                       ('submenu.debug_info.lbl.time', 'Uhrzeit', 'Time', 'Heure', 'Hora', 'Ora', 'Tijd'),
                                       ('submenu.debug_info.lbl.log-file', 'Log-Datei', 'Log-File', 'Fichier journal', 'Archivo de registro', 'File di log', 'Log-bestand'),
                                        
                                       ('submenu.grp_definition.lbl.title', 'Gruppendefinition', 'Group-Definition', 'Définition du groupe', 'Definición del grupo', 'Definizione gruppo', 'Groepdefinitie'),
                                       ('submenu.grp_definition.lbl.sub-title', 'Hinweis: Reload nötig für Anzeige!', 'Hint: Reload required for display!', 'Remarque : Rechargement nécessaire pour l''affichage !', 'Aviso: ¡Recarga requerida para mostrar!', 'Nota: Ricarica necessaria per la visualizzazione!', 'Tip: Herladen vereist voor weergave!'),
                                       ('submenu.grp_definition.lbl.group', 'Gruppe', 'Group', 'Groupe', 'Grupo', 'Gruppo', 'Groep'),
                                       ('submenu.grp_definition.lbl.grp-own-call', 'Eigenes-Call', 'Own-Call', 'Appel propre', 'Propio-Call', 'Chiamata propria', 'Eigen-Call'),
                                       ('submenu.grp_definition.lbl.grp-no-filter', 'Kein Filter', 'No Filter', 'Pas de filtre', 'Sin filtro', 'Nessun filtro', 'Geen filter'),
                                       ('submenu.grp_definition.lbl.grp-pos-filter', 'POS-Filter', 'POS-Filter', 'Filtre POS', 'Filtro POS', 'Filtro POS', 'POS-filter'),
                                       ('submenu.grp_definition.lbl.grp-cet-filter', 'CET-Filter', 'CET-Filter', 'Filtre CET', 'Filtro CET', 'Filtro CET', 'CET-filter'),
                                       ('submenu.grp_definition.lbl.emergency-group', 'Notfall-Gruppe', 'Emergency-Group', 'Groupe d''urgence', 'Grupo de emergencia', 'Gruppo di emergenza', 'Noodgroep'),
                                       ('submenu.grp_definition.lbl.html-export-group', 'HTML-Export-Gruppe', 'HTML-Export-Group', 'Groupe d''export HTML', 'Grupo de exportación HTML', 'Gruppo di esportazione HTML', 'HTML-exportgroep'),
                                       ('submenu.grp_definition.btn.save-settings', 'Settings speichern', 'Save Settings', 'Enregistrer les paramètres', 'Guardar configuración', 'Salva impostazioni', 'Instellingen opslaan'),
                                       ('submenu.grp_definition.btn.reload', 'MeshDash-Seite neu laden', 'Reload MeshDash-Page', 'Recharger la page MeshDash', 'Recargar página MeshDash', 'Ricarica pagina MeshDash', 'MeshDash-pagina herladen'),
                                       ('submenu.grp_definition.lbl.add-sound', 'Sound hinzufügen', 'Add Sound', 'Ajouter son', 'Añadir sonido', 'Aggiungi suono', 'Geluid toevoegen'),
                                       ('submenu.grp_definition.lbl.upload-sound', 'Sound hochladen', 'Upload Sound', 'Téléverser son', 'Subir sonido', 'Carica suono', 'Upload geluid'),
                                       ('submenu.grp_definition.btn.file-upload', 'Date hochladen', 'Upload File', 'Téléverser fichier', 'Subir archivo', 'Carica file', 'Upload bestand'),
                                       ('submenu.grp_definition.lbl.date', 'Datum', 'Date', 'Date', 'Fecha', 'Data', 'Datum'),
                                       ('submenu.grp_definition.lbl.time', 'Uhrzeit', 'Time', 'Heure', 'Hora', 'Ora', 'Tijd'),
                                       ('submenu.grp_definition.lbl.media-file', 'Sound-Datei', 'Media-File', 'Fichier média', 'Archivo multimedia', 'File multimediale', 'Media-bestand'),
                                        
                                       ('submenu.sensor_data.lbl.title', 'Lokale Sensordaten', 'Local Sensordata', 'Données capteurs locales', 'Datos de sensores locales', 'Dati sensori locali', 'Lokale sensorgegevens'),
                                       ('submenu.sensor_data.lbl.header-text', 'Lokale Sensordaten von IP', 'Local Sensordata from IP', 'Données capteurs locales depuis IP', 'Datos de sensores locales desde IP', 'Dati sensori locali da IP', 'Lokale sensorgegevens van IP'),
                                       ('submenu.sensor_data.btn.get-sensor-data', 'Lokale Sensordaten abfragen', 'Request Sensor-Data', 'Récupérer les données capteurs locales', 'Solicitar datos de sensores locales', 'Richiedi dati sensore locali', 'Vraag lokale sensorgegevens op'),
                                        
                                       ('submenu.sensor_threshold.lbl.title', 'Sensorschwellwert', 'Sensor-Threshold', 'Seuil capteur', 'Umbral del sensor', 'Soglia sensore', 'Sensor-drempel'),
                                       ('submenu.sensor_threshold.lbl.header-text', 'Sensorschwellwert-Definition ###REPLACE###zur Auslösung von Meldungen', 'Sensor threshold definition ###REPLACE###for triggering messages', 'Définition du seuil du capteur ###REPLACE###pour déclencher les messages', 'Definición del umbral del sensor ###REPLACE###para activar mensajes', 'Definizione soglia sensore ###REPLACE###per innescare messaggi', 'Sensor-drempeldefinitie ###REPLACE###voor het triggeren van meldingen'),
                                       ('submenu.sensor_threshold.lbl.tx-interval', 'Abfrage-Intervall', 'Abfrage-Intervall', 'Intervalle de requête', 'Intervalo de consulta', 'Intervallo di interrogazione', 'Query-interval'),
                                       ('submenu.sensor_threshold.lbl.temp-status', 'Temp Enable/Disable', 'Temp Enable/Disable', 'Temp activé/désactivé', 'Temp habilitado/deshabilitado', 'Temp abilitato/disabilitato', 'Temp aan/uit'),
                                       ('submenu.sensor_threshold.lbl.temp-min-max', 'Min/Max', 'Min/Max', 'Min/Max', 'Min/Max', 'Min/Max', 'Min/Max'),
                                       ('submenu.sensor_threshold.lbl.temp-alert-msg', 'Alert-Meldung', 'Alert-Message', 'Message d''alerte', 'Mensaje de alerta', 'Messaggio di allerta', 'Waarschuwing bericht'),
                                       ('submenu.sensor_threshold.lbl.temp-dm-group', 'DM-Gruppe/Call', 'DM-Group/Call', 'Groupe DM/Appel', 'Grupo DM/Llamada', 'Gruppo DM/Chiamata', 'DM-groep/Call'),
                                       ('submenu.sensor_threshold.lbl.tout-status', 'Tout Enable/Disable', 'Tout Enable/Disable', 'Tout activé/désactivé', 'Tout habilitado/deshabilitado', 'Tout abilitato/disabilitato', 'Tout aan/uit'),
                                       ('submenu.sensor_threshold.lbl.tout-min-max', 'Min/Max', 'Min/Max', 'Min/Max', 'Min/Max', 'Min/Max', 'Min/Max'),
                                       ('submenu.sensor_threshold.lbl.tout-alert-msg', 'Alert-Meldung', 'Alert-Message', 'Message d''alerte', 'Mensaje de alerta', 'Messaggio di allerta', 'Waarschuwing bericht'),
                                       ('submenu.sensor_threshold.lbl.tout-dm-group', 'DM-Gruppe/Call', 'DM-Group/Call', 'Groupe DM/Appel', 'Grupo DM/Llamada', 'Gruppo DM/Chiamata', 'DM-groep/Call'),
                                       ('submenu.sensor_threshold.btn.save-settings', 'Settings speichern', 'Save Settings', 'Enregistrer les paramètres', 'Guardar configuración', 'Salva impostazioni', 'Instellingen opslaan'),
                                       ('submenu.sensor_threshold.msg.save-settings-success', 'Settings wurden erfolgreich abgespeichert!', 'Settings saved successfully!', 'Paramètres enregistrés avec succès !', '¡Configuración guardada con éxito!', 'Impostazioni salvate con successo!', 'Instellingen succesvol opgeslagen!'),
                                       ('submenu.sensor_threshold.msg.save-settings-failed', 'Es gab einen Fehler beim Abspeichern der Settings!', 'There was an error saving the settings!', 'Une erreur s''est produite lors de l''enregistrement des paramètres !', '¡Hubo un error al guardar la configuración!', 'Si è verificato un errore durante il salvataggio delle impostazioni!', 'Er trad zich een fout op bij het opslaan van instellingen!'),
                                        
                                       ('submenu.gps_info.lbl.title', 'GPS-Infoseite', 'GPS Info-Page', 'Page d''info GPS', 'Página de info GPS', 'Pagina info GPS', 'GPS-info pagina'),
                                       ('submenu.gps_info.lbl.load-page', 'GPS-Infoseite laden', 'Load GPS-Page', 'Charger la page GPS', 'Cargar página GPS', 'Carica pagina GPS', 'Laad GPS-pagina'),
                                       ('submenu.gps_info.lbl.load-page-new', 'GPS-Infoseite neu laden', 'Load GPS-Page again', 'Recharger la page GPS', 'Recargar página GPS', 'Ricarica pagina GPS nuovamente', 'Laad GPS-pagina opnieuw'),
                                    
                                       
                                       ('submenu.config_beacon.lbl.title', 'Baken Einstellungen', 'Beacon settings', 'Paramètres du beacon', 'Ajustes de beacon', 'Impostazioni beacon', 'Beacon-instellingen'),             
                                       ('submenu.config_beacon.lbl.beacon-interval', 'Intervall in Min.', 'Interval in Min.', 'Intervalle en min.', 'Intervalo en min.', 'Intervallo in min.', 'Interval in min.'),
                                       ('submenu.config_beacon.lbl.beacon-stop-counts', 'Stop-Counts (100)', 'Stop-Counts (100)', 'Compteurs arrêt (100)', 'Conteos de parada (100)', 'Conteggio stop (100)', 'Stop-tellingen (100)'),
                                       ('submenu.config_beacon.lbl.beacon-text', 'Baken-Text', 'Beacon-Text', 'Texte du beacon', 'Texto del beacon', 'Testo beacon', 'Beacon-tekst'),
                                       ('submenu.config_beacon.lbl.beacon-group', 'Baken-Gruppe', 'Beacon-Group', 'Groupe beacon', 'Grupo beacon', 'Gruppo beacon', 'Beacon-groep'),
                                       ('submenu.config_beacon.lbl.beacon-task-status', 'Task enabled', 'Task enabled', 'Tâche activée', 'Tarea habilitada', 'Task abilitato', 'Taak ingeschakeld'),
                                       ('submenu.config_beacon.lbl.beacon-init-sent', 'Startzeit', 'Start time', 'Heure de début', 'Hora de inicio', 'Ora di inizio', 'Starttijd'),
                                       ('submenu.config_beacon.lbl.beacon-last-sent', 'Zuletzt gesendet', 'Last sent', 'Dernier envoi', 'Último enviado', 'Ultimo inviato', 'Laatst verzonden'),
                                       ('submenu.config_beacon.lbl.beacon-current-count', 'Aktueller Zähler', 'Current Count', 'Compteur actuel', 'Contador actual', 'Contatore corrente', 'Huidige teller'),
                                       ('submenu.config_beacon.lbl.beacon-hint', 'Eine autom. Abschaltung erfolgt wenn:<br>Laufzeit größer als 8h.<br>Stop-Count Ziel erreicht.', 'An automatic shutdown occurs when:<br>Running time greater than 8h.<br>Stop count target reached.', 'Arrêt automatique si :<br>Durée > 8h.<br>Objectif Stop-count atteint.', 'Apagado automático si:<br>Tiempo de ejecución > 8h.<br>Objetivo Stop-count alcanzado.', 'Spegnimento automatico se:<br>Tempo di esecuzione > 8h.<br>Obiettivo stop-count raggiunto.', 'Automatisch uitschakelen als:<br>Looptijd > 8h.<br>Stop-count doel bereikt.'),
                                       ('submenu.config_beacon.btn.save-settings', 'Settings speichern', 'Save Settings', 'Enregistrer les paramètres', 'Guardar configuración', 'Salva impostazioni', 'Instellingen opslaan'),
                                       ('submenu.config_beacon.msg.save-settings-success', 'Settings wurden erfolgreich abgespeichert!', 'Settings saved successfully!', 'Paramètres enregistrés avec succès !', '¡Configuración guardada con éxito!', 'Impostazioni salvate con successo!', 'Instellingen succesvol opgeslagen!'),
                                       ('submenu.config_beacon.msg.save-settings-failed', 'Es gab einen Fehler beim Abspeichern der Settings!', 'There was an error saving the settings!', 'Une erreur s''est produite lors de l''enregistrement des paramètres !', '¡Hubo un error al guardar la configuración!', 'Si è verificato un errore durante il salvataggio delle impostazioni!', 'Er trad zich een fout op bij het opslaan van instellingen!'),
                                        
                                       ('submenu.send_command.lbl.title', 'Befehl an Lora senden', 'Send command to Lora', 'Envoyer commande à Lora', 'Enviar comando a Lora', 'Invia comando a Lora', 'Verstuur commando naar Lora'),             
                                       ('submenu.send_command.lbl.command-line', 'Befehlszeile', 'Command line', 'Ligne de commande', 'Línea de comando', 'Linea di comando', 'Opdrachtregel'),
                                       ('submenu.send_command.lbl.set-upd-ip', 'Setzte UDP Ziel-Ip', 'Set UDP Target-Ip', 'Définir IP cible UDP', 'Establecer IP destino UDP', 'Imposta IP target UDP', 'Stel UDP doel-IP in'),
                                       ('submenu.send_command.lbl.activate-upd', 'Aktiviere UDP', 'Enable UDP', 'Activer UDP', 'Activar UDP', 'Abilita UDP', 'Activeer UDP'),
                                       ('submenu.send_command.lbl.deactivate-upd', 'Deaktiviere UDP', 'Disable UDP', 'Désactiver UDP', 'Desactivar UDP', 'Disabilita UDP', 'Deactiveer UDP'),
                                       ('submenu.send_command.lbl.reboot-node', 'Reboot Lora', 'Reboot Lora', 'Redémarrer Lora', 'Reiniciar Lora', 'Riavvia Lora', 'Herstart Lora'),
                                       ('submenu.send_command.lbl.ota-update', 'OTA-Update', 'OTA-Update', 'Mise à jour OTA', 'Actualización OTA', 'Aggiornamento OTA', 'OTA-update'),
                                       ('submenu.send_command.lbl.gateway-on', 'Gateway ON', 'Gateway ON', 'Passerelle ON', 'Gateway ON', 'Gateway ON', 'Gateway ON'),
                                       ('submenu.send_command.lbl.gateway-off', 'Gateway OFF', 'Gateway OFF', 'Passerelle OFF', 'Gateway OFF', 'Gateway OFF', 'Gateway OFF'),
                                       ('submenu.send_command.lbl.hint', 'Bei der erstmaligen UDP-Aktivierung,<br>muss einmalig ein Reboot ausgeführt werden!', 'When activating UDP for the first time,<br>a reboot must be performed once!', 'Lors de l''activation initiale de l''UDP,<br>un redémarrage doit être effectué !', 'Al activar UDP por primera vez,<br>se debe realizar un reinicio!', 'Alla prima attivazione dell''UDP,<br>deve essere eseguito un riavvio!', 'Bij de eerste activering van UDP,<br>moet een herstart worden uitgevoerd!'),
                                       ('submenu.send_command.btn.send-command', 'Sende Befehl', 'Send command', 'Envoyer commande', 'Enviar comando', 'Invia comando', 'Verstuur commando'),
                                       ('submenu.send_command.msg.save-settings-success', 'Settings wurden erfolgreich abgespeichert!', 'Settings saved successfully!', 'Paramètres enregistrés avec succès !', '¡Configuración guardada con éxito!', 'Impostazioni salvate con successo!', 'Instellingen succesvol opgeslagen!'),
                                       ('submenu.send_command.msg.save-settings-failed', 'Es gab einen Fehler beim Abspeichern der Settings!', 'There was an error saving the settings!', 'Une erreur s''est produite lors de l''enregistrement des paramètres !', '¡Hubo un error al guardar la configuración!', 'Si è verificato un errore durante il salvataggio delle impostazioni!', 'Er trad zich een fout op bij het opslaan van instellingen!');
           ");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    elseif ($database == 'call_notice')
    {
        #Open Database
        $db = new SQLite3('database/call_notice.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS callNotice 
                                (
                                  callSign TEXT NOT NULL UNIQUE,              
                                  callNotice TEXT,
                                  lastHeard TEXT NOT NULL,
                                  timestamps TEXT NOT NULL,
                                  PRIMARY KEY(callSign)
                                )
                        ");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    elseif ($database == 'send_cmd_favorites')
    {
        #Open Database
        $db = new SQLite3('database/send_cmd_favorites.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS sendCmdFavorites 
                                (
                                  cmd TEXT NOT NULL UNIQUE,              
                                  cmdDesc TEXT,
                                  PRIMARY KEY(cmd)
                                )
                        ");

        $db->exec("REPLACE INTO sendCmdFavorites (
                                          cmd, 
                                          cmdDesc
                                       ) VALUES 
                                       ('--extudp on', 'Aktiviere UDP'),
                                       ('--extudp off', 'Deaktiviere UDP'),
                                       ('--gateway on', 'Gateway ON'),
                                       ('--gateway off', 'Gateway OFF'),
                                       ('--reboot', 'Reboot Node')
           ");

        #Close and write Back WAL
        $db->close();
        unset($db);
    }
    elseif ($database == 'write_mutex')
    {
        #Open Database an Insert Write-Mutex Table for MeshDash
        #Used for AutoPurge
        $db = new SQLite3('database/write_mutex.db');
        $db->exec('PRAGMA journal_mode = wal;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        #name          -- z.B. 'meshdash' oder 'sensordata'
        #is_locked     -- 0 = frei, 1 = gerade purgen
        #last_purge_ts -- Unix Timestamp des letzten erfolgreichen Purges

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS purge_lock (
                            name TEXT PRIMARY KEY,       
                            is_locked INTEGER,
                            last_purge_ts INTEGER,
                            proc_name TEXT        
                        ); 
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
                echo '<li data-action="config_ping_lora">' . getStatusIcon('ping-lora', true) . '</li>';
                echo '<li data-action="debug_info">' . getStatusIcon('debug-info', true) . '</li>';
                echo '<li data-action="edit_translation">' . getStatusIcon('edit_translation', true) . '</li>';
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
                echo '<li data-action="sensor_plot">' . getStatusIcon('plot', true) . '</li>';
                echo '<li data-action="gps_info">' . getStatusIcon('gps', true) . '</li>';
            echo '</ul>';
        echo '</li>';

        echo '<li class="menuitem with-arrow">'
            . '<span class="menu-left">' . getStatusIcon('mheard', true) . '</span>'
            . '<span class="menu-right">' . getStatusIcon('right_triangle') . '</span>';

            echo '<ul class="submenuIcon">';
                echo '<li class="menuitem" data-action="mHeard">' . getStatusIcon('mheard-page', true) . '</li>';
                echo '<li data-action="mHeard-osm">' . getStatusIcon('mheard-osm', true) . '</li>';
                echo '<li data-action="mHeard-osm-full">' . getStatusIcon('mheard-osm-full', true) . '</li>';
            echo '</ul>';
        echo '</li>';

        echo '<li class="menuitem with-arrow">'
            . '<span class="menu-left">' . getStatusIcon('data-purge', true) . '</span>'
            . '<span class="menu-right">' . getStatusIcon('right_triangle') . '</span>';

            echo '<ul class="submenuIcon">';
                echo '<li data-action="config_data_purge_manuell">' . getStatusIcon('data-purge-manuell', true) . '</li>';
                echo '<li data-action="config_data_purge_auto">' . getStatusIcon('data-purge-auto', true) . '</li>';
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
