<?php

function callBackgroundTask($taskFile): bool
{
    $actualHost  = (empty($_SERVER['HTTPS']) ? 'http' : 'https');
    $httpHost    = $_SERVER['HTTP_HOST']; // localhost, 192.168.123
    $requestUri  = $_SERVER['REQUEST_URI']; // /abcde/
    $triggerLink = $actualHost . '://' . $httpHost . $requestUri . $taskFile;

    $debugFlag = false;

    if ($debugFlag === true)
    {
        echo "<br>triggerLink:$triggerLink";

        echo "<pre>";
        print_r($_SERVER);
        echo "</pre>";

        return true;
    }

    #Starte Trigger
    $ch = curl_init();

    # Set Curl Options
    curl_setopt($ch, CURLOPT_URL, $triggerLink);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    #curl_setopt($ch, CURLOPT_TIMEOUT, 1); // Warte max. 1 Sekunden und beende Verbindung
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100); // Warte max. 100 ms und beende Verbindung

    #Ignoriere Timeout Meldung da so gewollt
    if (curl_exec($ch) === false && curl_errno($ch) != 28)
    {
        echo 'Curl error: ' . curl_error($ch);
        echo 'Curl error: ' . curl_errno($ch);
    }

    curl_close($ch);

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
                                  timestamps INTEGER NOT NULL,
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
                                  PRIMARY KEY('msg_id')
                                )
                ");

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
                                  PRIMARY KEY('param_key')
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
                                       ('keyword1DmGrpId', '','*'),
                                       ('keyword2DmGrpId', '','*'),
                                       ('noTimeSyncMsg', 0 , ''),
                                       ('maxScrollBackRows', 60 , '')
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
        #$db->exec("PRAGMA synchronous = 1");

        // Tabelle erstellen wenn nicht vorhanden
        $db->exec("CREATE TABLE IF NOT EXISTS keywords 
                                (
                                  msg_id TEXT NOT NULL UNIQUE,              
                                  executed INTEGER,
                                  errCode INTEGER,
                                  errText TEXT,
                                  PRIMARY KEY('msg_id')
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
                                  mheardId INTEGER NOT NULL UNIQUE,              
                                  timestamps INTEGER NOT NULL,
                                  mhCallSign TEXT,
                                  mhDate TEXT,
                                  mhTime TEXT,
                                  mhHardware TEXT,
                                  mhMod INTEGER,
                                  mhRssi INTEGER,
                                  mhSnr INTEGER,
                                  mhDist INTEGER,
                                  mhPl INTEGER,
                                  mhM INTEGER,
                                  PRIMARY KEY('mheardId' AUTOINCREMENT)
                                )
                ");

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
        <li data-action="config_keyword">Keyword</li>
        <li data-action="config_data_purge">Data-Purge</li>
        <li data-action="config_ping_lora">Ping Lora</li>
      </ul>
    </li>
    <li>Gruppen
      <ul class="submenu">
        <li data-action="grp_definition">Gruppen definieren</li>
        <li data-action="grp_emergency">Notfallgruppe festlegen</li>
        <li data-action="grp_alerting">Gruppen Benachrichtigung einstellen</li>
      </ul>
    </li>
    <li>Hilfe
      <ul class="submenu">
        <li data-action="hlp_groups">Hilfe Gruppen</li>
        <li data-action="hlp_msg_send">Hilfe zu Msg senden</li>
        <li data-action="hlp_bugs">Hilfe zu Fehlermeldungen</li>
      </ul>
    </li>
    <li>&uuml;ber
      <ul class="submenu">
        <li data-action="about_version">MeshDash Version</li>
      </ul>
    </li>
    <li data-action="mHeard">MHeard</li>
    <li data-action="message">Message</li>
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
            echo '<br><br><b>Die Lora-Ip wurde noch nicht gesetzt.';
            echo '<br><br>Bitte jetzt die IP angeben:</b>';
            echo '&nbsp;&nbsp;&nbsp;<input type="text" class="inputParamLoraIp" name="paramSetLoraIp" id="paramSetLoraIp" value="" required placeholder="Ip im IPv4 Format" />';
        }

        if (trim($callSign == ''))
        {
            echo '<br><br><b>Das Call mit SSID  wurde noch nicht gesetzt.';
            echo '<br><br>Bitte jetzt die Call mit SSID angeben:</b>';
            echo '&nbsp;&nbsp;&nbsp;<input type="text" class="inputParamLoraIp" name="inputParamCallSign" id="inputParamCallSign" value="" required placeholder="DB0ABC-99" />';
        }

        echo '&nbsp;&nbsp;&nbsp;<input type="button" class="submitParamLoraIp" id="btnSetParamLoraIp" value="Parameter jetzt setzen"  />';

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
        #das x ist wichtig, weil genau nur dieser Suchbegriff gesucht, wird
        #das andere tasks gibt die mit php beginnen
        exec('pkill -x php');
    }

    #Gib 1sek Zeit
    sleep(1);

    #Prüfe, ob Prozess wirklich beendet wurde
    $taskResult = shell_exec($checkTaskCmd);

    if ($taskResult != '')
    {
        echo "<br>Task wurde nicht beendet!";
        echo "<br>" . $taskResult;
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
            callBackgroundTask('task_bg.php');
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
