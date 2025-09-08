<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Nachrichten</title>';

#Prevents UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );
echo '<script type="text/javascript" src="jquery/jquery.min.js"></script>';

echo '<link rel="stylesheet" href="jquery/jquery-ui-1.13.3/jquery-ui.css">';
echo '<link rel="stylesheet" href="jquery/css/jq_custom.css">';

# Achtung das ist V jquery-ui-1.13.3 weil nur die mit dem DateTimePicker Addon funktioniert
echo '<script type="text/javascript" src="jquery/jquery-ui-1.13.3/jquery-ui.min.js"></script>';
$scrollToTopImage = 'scroll_to_top_md50.png';

if ((getParamData('darkMode') ?? 0) == 1)
{
    echo '<link rel="stylesheet" href="css/dark_mode.css?' . microtime() . '">';
    $scrollToTopImage = 'scroll_to_top_md50dark.png';
}
else
{
    echo '<link rel="stylesheet" href="css/normal_mode.css?' . microtime() . '">';
}

#Wenn Snapshot Abfrage, dann CSS in HTML Inline einbetten und UTF-8 Charset Meta-Tag setzen
if (isset($_GET['isSnapshot']) && $_GET['isSnapshot'] == 1)
{
    echo '<meta charset="UTF-8">';
    echo "<style>" . file_get_contents(__DIR__ . "/css/message.css") . "</style>";
}
else
{
    $ts = filemtime('css/message.css');
    echo '<link rel="stylesheet" href="css/message.css?' . $ts . '">';
}

echo '</head>';
echo '<body>';

require_once 'include/func_js_message.php';
require_once 'include/func_php_message.php';
require_once 'include/func_php_index.php';
require_once 'include/func_php_mheard.php';
require_once 'include/func_php_config_alerting.php';
require_once 'include/func_php_config_keyword.php';

if(!file_exists('database/meshdash.db') ||
    !file_exists('database/parameter.db') ||
    !file_exists('database/mheard.db') ||
    !file_exists('database/groups.db') ||
    !file_exists('database/keywords.db'))
{
    echo "<h3>Es wurden ein oder mehrere Datenbanken nicht gefunden!";
    echo "<br><br>Wenn gerade ein Update stattfindet, dann bitte warten bis es abgeschlossen ist.";
    echo "<br>Wenn nicht, bitte das Verzeichnis <b>database</b> prüfen.</hr>";
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', '300'); // Ausführungszeit auf 5min bei nicht performanten Geräten

$debugFlag = false;

#Hole Parameter für NoPos und DMA-Alert aus Datenbank
$loraIp             = trim(getParamData('loraIp'));
$noPosData          = (int) getParamData('noPosData');
$noDmAlertGlobal    = (int) getParamData('noDmAlertGlobal');
$noTimeSyncMsgValue = (int) getParamData('noTimeSyncMsg');
$maxScrollBackRows  = (int) getParamData('maxScrollBackRows');
$callSign           = trim(getParamData('callSign'));
$posStatusValue     = $noPosData;

$beaconOtp          = getBeaconData('beaconOtp') ?? '';
$beaconGroup        = getBeaconData('beaconGroup');
$beaconGroup        = $beaconGroup == '' ? 0 : $beaconGroup;

$alertSoundFileSrc = getParamData('alertSoundFileSrc');
$alertEnabledSrc   = getParamData('alertEnabledSrc');
$alertSoundCallSrc = getParamData('alertSoundCallSrc');

$alertSoundFileDst = getParamData('alertSoundFileDst');
$alertEnabledDst   = getParamData('alertEnabledDst');
$alertSoundCallDst = getParamData('alertSoundCallDst');

$clickOnCall       = getParamData('clickOnCall');
$mheardGroup       = getParamData('mheardGroup');
$mheardGroup       = $mheardGroup == '' ? 0 : $mheardGroup;

$bubbleMaxWidth    = getParamData('bubbleMaxWidth') ?? 40;
$bubbleMaxWidth    = $bubbleMaxWidth == '' ? 40 : $bubbleMaxWidth;

#Prüfe ob Logging aktiv ist
$doLogEnable = getParamData('doLogEnable');

#Export Group to HTML:
$msgExportEnable = getParamData('msgExportEnable') ?? 0;
$msgExportEnable = $msgExportEnable === 1; // True/False
$msgExportGroup  = getParamData('msgExportGroup') ?? '';

#Prüfe, ob das reine Rufzeichen nur genommen werden soll ohne SSID
$strictCallEnable = getParamData('strictCallEnable');

$sqlAddon        = '';
$sqlAddonSearch  = '';
$groupRequest    = $_REQUEST['group'] ?? -1;
$group           = (int) $groupRequest;
$callSignSql     = $callSign;
$doSearchQuery   = false;
$bubbleStyleView = getParamData('bubbleStyleView') == 1;

$searchMsg    = $_REQUEST['searchMsg'] ?? '';
$searchSrc    = $_REQUEST['searchSrc'] ?? '';
$searchDst    = $_REQUEST['searchDst'] ?? '';
$searchTsFrom = $_REQUEST['searchTsFrom'] ?? '';
$searchTsTo   = $_REQUEST['searchTsTo'] ?? '';
$searchPage   = $_REQUEST['page'] ?? '';
$totalRows    = $_REQUEST['totalRows'] ?? 0;
$totalPages   = $_REQUEST['totalPages'] ?? 0;

$page    = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 100;
$offset  = ($page - 1) * $perPage;

$searchTsFromUrl = $searchTsFrom;
$searchTsToUrl   = $searchTsTo;

#Ermittel vorhandene Notizen zum Call
$arrayGetCallNotices = getCallNotices();
$arrayGetCallNotices = $arrayGetCallNotices === false ? array() : $arrayGetCallNotices;

$checkTaskCmdUdpReceiver = getTaskCmd('udp');
$taskResultUdpReceiver   = shell_exec($checkTaskCmdUdpReceiver); //Prüfe Hintergrundprozess
$statusImageUpdReceiver  = $taskResultUdpReceiver != '' ? '' : '<span class="failureHint">Achtung: Background-Task UDP-Receiver ist inaktiv!</span>';

if ($statusImageUpdReceiver != '')
{
    echo '<script>
              $(parent.document).find("#bgTask").attr("src", "image/punkt_red.png");
              $(parent.document).find("#taskStatusFlag").val(0);
          </script>';
}
else
{
    echo '<script>
              $(parent.document).find("#bgTask").attr("src", "image/punkt_green.png");
              $(parent.document).find("#taskStatusFlag").val(1);
          </script>';
}

if ($totalRows != 0)
{
    echo '<span class="setFontMsg searchInfo">';
    echo 'Gefundene Datensätze:' . $totalRows;
    echo '<br>Entspricht ' . $totalPages . ' Seiten bei ' . $perPage . ' Einträgen pro Seite';
    echo '</span>';

    echo '<br><br><span class="setFontMsg searchInfo">';

    if ($page > 1)
    {
        echo '<input type="button" class="btnPagePagination" value="« Zurück" ';
        echo 'data-group="' . $group . '" ';
        echo 'data-search_msg="' . $searchMsg . '" ';
        echo 'data-search_src="' . $searchSrc . '" ';
        echo 'data-search_dst="' . $searchDst . '" ';
        echo 'data-search_ts_from="' . $searchTsFromUrl . '" ';
        echo 'data-search_ts_to="' . $searchTsToUrl . '" ';
        echo 'data-total_rows="' . $totalRows . '" ';
        echo 'data-total_pages="' . $totalPages . '" ';
        echo 'data-current_page="' . $page . '" ';
        echo 'data-search_direction="back" ';
        echo '/>';

        echo "&nbsp;&nbsp;&nbsp;Seite $page von $totalPages&nbsp;&nbsp;&nbsp;";
    }

    if ($page < $totalPages)
    {
        echo '<input type="button" class="btnPagePagination" value="Weiter »" ';
        echo 'data-group="' . $group . '" ';
        echo 'data-search_msg="' . $searchMsg . '" ';
        echo 'data-search_src="' . $searchSrc . '" ';
        echo 'data-search_dst="' . $searchDst . '" ';
        echo 'data-search_ts_from="' . $searchTsFromUrl . '" ';
        echo 'data-search_ts_to="' . $searchTsToUrl . '" ';
        echo 'data-total_rows="' . $totalRows . '" ';
        echo 'data-total_pages="' . $totalPages . '" ';
        echo 'data-current_page="' . $page . '" ';
        echo 'data-search_direction="forward" ';
        echo '/>';
    }

    if ($page == 1)
    {
        echo "&nbsp;&nbsp;&nbsp;Seite $page von $totalPages&nbsp;&nbsp;&nbsp;";
    }
    echo '</span>';
    echo "<hr>";
}

#Konvertiere Datum in ein Timestamp
$searchTsFrom = $searchTsFrom != '' ? str_replace('T', ' ', $searchTsFrom).':00' : '';
$searchTsTo   = $searchTsTo != '' ? str_replace('T',' ', $searchTsTo).':59' : '';

if ($searchMsg != '' || $searchSrc != '' || $searchDst != '' || $searchTsFrom != '' || $searchTsTo != '')
{
    $doSearchQuery = true;

    $conditions = [];

    if ($searchMsg !== '') {
        $conditions[] = "lower(msg) LIKE lower('%$searchMsg%')";
    }

    if ($searchSrc !== '') {
        $conditions[] = "lower(substr(src, 1, instr(src || ',', ',') - 1)) LIKE lower('%$searchSrc%')";
    }

    if ($searchDst !== '') {
        $conditions[] = "lower(dst) LIKE lower('%$searchDst%')";
    }

    if ($searchTsFrom !== '' && $searchTsTo !== '') {
        $conditions[] = "timestamps BETWEEN '$searchTsFrom' AND '$searchTsTo'";
    }

    if ($searchTsFrom !== '' && $searchTsTo === '') {
        $conditions[] = "timestamps >= '$searchTsFrom'";
    }

    if ($searchTsFrom === '' && $searchTsTo !== '') {
        $conditions[] = "timestamps <= '$searchTsTo'";
    }

    $conditions[] = "type = 'msg'";
    $conditions[] = "lower(msg) NOT LIKE lower('%{CET}%')";

    if (!empty($conditions)) {
        $sqlAddonSearch = 'WHERE ' . implode(' AND ', $conditions);
    }
}

if ($strictCallEnable == 1)
{
    $callSignSql = explode("-", $callSign, 2)[0]; // Trennen nach dem ersten '-'
}

echo '<input type="hidden" id="group" name="group" value="' . $group . '" />';

if ($group > 0)
{
    $sqlAddon .= ' AND dst = "' . $group . '" ';
}
else if ($group == 0)
{
    $sqlAddon .= ' AND dst = "*" ';
}
else if ($group == -2)
{
    $sqlAddon .= ' AND (dst like "' . $callSignSql . '%" OR src like "' . $callSignSql . '%") ';
    $sqlAddon .= " AND src GLOB '[A-Za-z0-9]*' "; // src muss ein Rufzeichen sein
    $sqlAddon .= " AND dst GLOB '[A-Za-z0-9]*' "; // dst muss ein Rufzeichen sein
    $sqlAddon .= " AND type = 'msg' ";
}

if ($noPosData == 1)
{
    $sqlAddon .= ' AND type != "pos" ';
}

if ($noTimeSyncMsgValue == 1)
{
    $sqlAddon .= ' AND msgIsTimeSync = 0 ';
}

#Gruppe POS
if ($group == -3)
{
    $sqlAddon       = " AND type = 'pos' ";
    $noPosData      = 0;
    $posStatusValue = 0;
}

#Gruppe {CET}
if ($group == -4)
{
    $sqlAddon            = " AND type = 'msg' ";
    $sqlAddon           .= " AND msg like '{CET}%' ";
    $noTimeSyncMsgValue = 0;
}

$arrayNotificationData = getNotificationData('active');

if (empty($arrayNotificationData) === false)
{
    // Alle notifySoundFile Werte extrahieren
    $arraySoundFiles = array_column($arrayNotificationData, 'notifySoundFile');

    // Duplikate entfernen
    $uniqueSoundFiles = array_unique($arraySoundFiles);

    #Soundfiles Preload
    foreach ($uniqueSoundFiles as $soundFile)
    {
        $soundFileId = str_replace('.', '_', $soundFile); // ergibt: z.B. callsign_dst_alert_wav
        echo '<audio id="' . $soundFileId . '" src="sound\\' . $soundFile . '" preload="auto"></audio>';
    }
}

#Werte für Jquery die dann im Bottom Frame abgebildet werden
echo '<input type="hidden" id="posStatusValue" value="'. $posStatusValue . '" />';
echo '<input type="hidden" id="noTimeSyncMsgValue" value="'. $noTimeSyncMsgValue . '" />';

$db = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
$db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

if ($searchPage == '' && $doSearchQuery === true)
{
    // Für Pagination: Gesamtanzahl holen (ohne LIMIT)
    $totalRows   = 0;
    $countQuery  = "SELECT COUNT(*) AS total 
                      FROM meshdash $sqlAddonSearch;
                   ";

    $logArray   = array();
    $logArray[] = "message_Pagination: Database: database/meshdash.db";
    $logArray[] = "message_Pagination: sqlAddonSearch: $sqlAddonSearch";

    $countResult = safeDbRun($db, $countQuery, 'query', $logArray);

    if ($countResult !== false)
    {
        $totalRows  = $countResult->fetchArray(SQLITE3_ASSOC)['total'];
        $totalPages = ceil($totalRows / $perPage);
    }

    if ($totalRows == 0)
    {
        echo '<br><span class="failureHint">Keine Daten zum Suchkriterium vorhanden.</span>';
        exit();
    }

    echo '<span class="setFontMsg searchInfo">';
    echo 'Gefundene Datensätze:' . $totalRows;
    echo '<br>Entspricht ' . $totalPages . ' Seiten bei ' . $perPage . ' Einträgen pro Seite';
    echo '</span>';

    echo '<br><br><span class="setFontMsg searchInfo">';

    if ($page > 1)
    {
        echo '<input type="button" class="btnPagePagination" value="« Zurück" ';
        echo 'data-group="' . $group . '" ';
        echo 'data-search_msg="' . $searchMsg . '" ';
        echo 'data-search_src="' . $searchSrc . '" ';
        echo 'data-search_dst="' . $searchDst . '" ';
        echo 'data-search_ts_from="' . $searchTsFromUrl . '" ';
        echo 'data-search_ts_to="' . $searchTsToUrl . '" ';
        echo 'data-total_rows="' . $totalRows . '" ';
        echo 'data-total_pages="' . $totalPages . '" ';
        echo 'data-current_page="' . $page . '" ';
        echo 'data-search_direction="back" ';
        echo '/>';

        echo "&nbsp;&nbsp;&nbsp;Seite $page von $totalPages&nbsp;&nbsp;&nbsp;";
    }

    if ($page < $totalPages)
    {
        echo '<input type="button" class="btnPagePagination" value="Weiter »" ';
        echo 'data-group="' . $group . '" ';
        echo 'data-search_msg="' . $searchMsg . '" ';
        echo 'data-search_src="' . $searchSrc . '" ';
        echo 'data-search_dst="' . $searchDst . '" ';
        echo 'data-search_ts_from="' . $searchTsFromUrl . '" ';
        echo 'data-search_ts_to="' . $searchTsToUrl . '" ';
        echo 'data-total_rows="' . $totalRows . '" ';
        echo 'data-total_pages="' . $totalPages . '" ';
        echo 'data-current_page="' . $page . '" ';
        echo 'data-search_direction="forward" ';
        echo '/>';
    }

    if ($page == 1)
    {
        echo "&nbsp;&nbsp;&nbsp;Seite $page von $totalPages&nbsp;&nbsp;&nbsp;";
    }

    echo '</span>';
    echo "<hr>";
}

if ($doSearchQuery === true)
{
    $searchQuery = "SELECT * 
                      FROM meshdash
                           $sqlAddonSearch
                           
                  ORDER BY timestamps DESC
                     LIMIT $perPage OFFSET $offset
                        ";

    $logArray   = array();
    $logArray[] = "doSearchQuery_message_Pagination: Database: database/meshdash.db";
    $logArray[] = "doSearchQuery_message_Pagination: sqlAddonSearch: $sqlAddonSearch";
    $logArray[] = "doSearchQuery_message_Pagination: perPage: $perPage";
    $logArray[] = "doSearchQuery_message_Pagination: offset: $offset";

    $result = safeDbRun($db, $searchQuery, 'query', $logArray);
}
else
{
    # Hole mir die letzten xx Nachrichten aus der Datenbank
    # Maybe False when Database is locked
    $sql = "SELECT * 
              FROM meshdash
             WHERE msgIsAck = 0
                   $sqlAddon
          ORDER BY timestamps DESC
             LIMIT $maxScrollBackRows";

    $logArray   = array();
    $logArray[] = "Message_Normal: Database: database/meshdash.db";
    $logArray[] = "Message_Normal: sqlAddon: $sqlAddon";
    $logArray[] = "Message_Normal: maxScrollBackRows: $maxScrollBackRows";

    $result = safeDbRun($db, $sql, 'query', $logArray);

    echo $statusImageUpdReceiver;
}

#Get Keywords
$arrayKeyWords = getKeyWordHooks('aktive');

#Prevents Error on fetch array
if ($result !== false)
{
    while ($row = $result->fetchArray(SQLITE3_ASSOC))
    {
        ###############################################
        #Common
        $srcType                 = $row['src_type'] ?? ''; // node, lora
        $type                    = $row['type'] ?? '';     // pos / msg
        $src                     = $row['src'] ?? '';     // <call>-<sid>
        $msg                     = $row['msg'] ?? '';     //
        $msgId                   = $row['msg_id']; // 72378728
        $timestamp               = $row['timestamps'] ?? date('Y-m-d H:i:s');  // Timestamp added by myself
        $dst                     = $row['dst'] ?? ''; // 995 | call
        $msgAckReqDb             = $row['ackReq'] ?? '';
        $msgAckDb                = $row['ack'] ?? '';
        $mhSend                  = $row['mhSend'] ?? 0;
        $beaconEnabledStatusSend = $row['beaconEnabledStatusSend'] ?? 0;
        $alertExecutedSrc        = $row['alertExecutedSrc'] ?? 0;
        $alertExecutedDst        = $row['alertExecutedDst'] ?? 0;

        $msgAckReq   = 0; // Acknowledge Request
        $msgAck      = 0; // Acknowledge

        #Wenn Leer ist es i.d.R. ein eigene Aussendung
        $callSign = getParamData('callSign');
        $src      = $src == '' ? $callSign : $src;
        $srcType  = $srcType == '' ? 'msg' : $srcType;
        $msgSplit = explode('{', $msg);

        #Berücksichtige nicht Zeitmeldungen von OE1XAR-45
        if (count($msgSplit) > 1 && strpos($msg, '{CET}') === false)
        {
            $msg        = $msgSplit[0];
            $msgAckReq  = (int) $msgSplit[1];
        }

        #Ersetzte durch aussagekräftige Meldung von OE1XAR-45,
        #wenn Flag noTimeSync = 0 sonst continue;
        #Achtung, Ergebnis muss auf Type geprüft werden da sonst ein false als 0 interpretiert wird
        if (strpos($msg, '{CET}') !== false && $noTimeSyncMsgValue == 0)
        {
            $msg = str_replace('{CET}',' TimeSync: CET ', $msg);
        }
        else if (strpos($msg, '{CET}') !== false && $noTimeSyncMsgValue == 1)
        {
            #Wird jetzt in SQL abgefangen
            updateMeshDashData($msgId,'msgIsTimeSync', 1, $doSearchQuery);
            continue;
        }

        #Prüfe auf Keyword Ergebnisdaten für msgID
        $resGetKeywordsData   = getKeywordsData($msgId);
        $keywordExecuted      = $resGetKeywordsData['executed'];
        $keywordErrorCode     = $resGetKeywordsData['errCode'];
        $keywordMsgSendFlag   = $resGetKeywordsData['execMsgSend'];
        $keywordExecTimestamp = $resGetKeywordsData['execTimestamp'];

        foreach ($arrayKeyWords as $resKey => $resValue)
        {
            if ($resValue['keyHookTrigger'] != '' && $resValue['keyHookExecute'] != '' && $resValue['keyHookReturnMsg'] != '' && $keywordExecuted == 0)
            {
                if (strpos($msg, $resValue['keyHookTrigger']) !== false && $dst == $resValue['keyHookDmGrpId'])
                {
                    execScriptCurl($resValue['keyHookExecute']);

                    $paramSetKeyword['msgId']         = $msgId;
                    $paramSetKeyword['executed']      = 1;
                    $paramSetKeyword['errCode']       = 0; //debug
                    $paramSetKeyword['errText']       = ''; //debug
                    $paramSetKeyword['execScript']    = $resValue['keyHookExecute'];
                    $paramSetKeyword['execTimestamp'] = date('Y-m-d H:i:s');
                    $paramSetKeyword['execTrigger']   = $resValue['keyHookTrigger'];
                    $paramSetKeyword['execReturnMsg'] = $resValue['keyHookReturnMsg'];
                    $paramSetKeyword['execGroup']     = $resValue['keyHookDmGrpId'];

                    setKeywordsData($paramSetKeyword);
                }
            }

            #Wenn Script ausgeführt und Return-Msg noch nicht gesendet nach OffsetTime, dann jetzt senden
            if ($keywordExecuted == 1 && $keywordMsgSendFlag == 0 && strpos($msg, $resValue['keyHookTrigger']) !== false)
            {
                $resHasKeywordTimePassed = hasKeywordTimePassed($keywordExecTimestamp, Date('Y-m-d H:i:s'), 10);

                #Sende nur, wenn kein Fehler aufgetreten ist
                if ($resValue['keyHookReturnMsg'] != '' && $keywordErrorCode == 0 && $resHasKeywordTimePassed === true && $resValue['keyHookDmGrpId'] != '')
                {
                    $arraySend['txType'] = 'msg';
                    $arraySend['txDst']  = $resValue['keyHookDmGrpId'];
                    $arraySend['txMsg']  = $resValue['keyHookReturnMsg'];
                    $resSetTxQueue       = setTxQueue($arraySend);

                    #Setzte gesendet Flag mit Timestamp
                    updateKeywordsData($msgId);
                }
            }
        }

        #Check, ob es nur Positionsdaten sind
        if ($type === 'pos' && $noPosData == 0)
        {
            $lat             = $row['latitude'];     // 51.5012
            $latDir          = $row['lat_dir']; // N
            $long            = $row['longitude'];    // 7.34
            $longDir         = $row['long_dir'];  //E
            $aprsSymbol      = $row['aprs_symbol']; // #
            $aprsSymbolGroup = $row['aprs_symbol_group']; // /
            $hwId            = $row['hw_id']; // 3
            $altitude        = $row['altitude']; // 344 (Höhe in Fuss)
            $batteryCapacity = $row['batt'];  // Batt Kapazität in %
            $firmware        = $row['firmware'] ?? '';  // Firmware
            $fwSubVersion    = $row['fw_sub'] ?? '';  // FirmwareSub Version
            $altitude        = number_format($altitude * 0.3048); // Umrechnung Fuss -> Meter

            if ($bubbleStyleView === true)
            {
                echo '<div class="chat-container">';
                echo '<div class="message-row incoming">';
                echo '<div class="message-bubble" style="max-width:' . $bubbleMaxWidth . '%">';
            }

            $parts     = explode(',', $src);
            $firstCall = array_shift($parts); // Nimmt das erste Rufzeichen und entfernt es aus dem Array
            $restCalls = implode(',', $parts);

            echo '<h3 class="setFontMsgHeader">';
            echo 'MsgId: ' . $msgId . ' (' . $srcType . ')<br>' . $timestamp . ' ';

            if ($restCalls != '')
            {
                echo 'Von: ' . $firstCall . ' VIA: ' . $restCalls . ' Ziel: all</h3>';
            }
            else
            {
                echo 'Von: ' . $firstCall . ' Ziel: all</h3>';
            }

            echo '<div class="info-container">';
                echo '<div class="info-row"><span class="info-label">Längengrad: </span><span class="info-value">'.$lat . ' ' . $latDir.'</span></div>';
                echo '<div class="info-row"><span class="info-label">Breitengrad: </span><span class="info-value">'.$long . ' ' . $longDir.'</span></div>';
                echo '<div class="info-row"><span class="info-label">Höhe: </span><span class="info-value">' . $altitude . ' m</span></div>';
                echo '<div class="info-row"><span class="info-label">Batteriekapazität: </span><span class="info-value">' . $batteryCapacity . ' %</span></div>';
                if ($firmware != '')
                {
                    echo '<div class="info-row"><span class="info-label">Firmware: </span><span class="info-value">' . $firmware .'</span></div>';
                }
                if ($fwSubVersion != '')
                {
                    echo '<div class="info-row"><span class="info-label">FW-Subversion: </span><span class="info-value">' . $fwSubVersion .'</span></div>';
                }
            echo '</div>';

            if ($bubbleStyleView === true)
            {
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</h3>';
            }
            else
            {
                echo '</h3><hr>';
            }

        }
        else if ($type === 'msg')
        {
            $dst = $dst == '' ? 'all' : $dst;

            if ($msgAckReqDb == '')
            {
                updateAckReqId($msgId, $msgAckReq);
            }

            #Prüfe ob ack vorliegt und wenn ja, packe es zur korrespondierenden Nachricht
            $resCheckMsgAck = checkMsgAck($msg);

            #Wenn MSg ein Ack ist dann nicht anzeigen aber auswerten
            if ($resCheckMsgAck === true)
            {
                #Wird jetzt in SQL abgefangen
                updateMeshDashData($msgId,'msgIsAck', 1, $doSearchQuery);
                continue;
            }

            #Prüfe ob Mheard Keyword geschickt wurde.
            #Prüfe ob MH Gruppe gesetzt wurde, ansonsten reagiere auf alles != all
            #Anfrage gilt nicht, wenn Mh schon gesendet wurde. mhSend = 1
            #MhTargetFlag = 1 sende nur an das anfragende CallSign zurück.
            $mheardGroup = $mheardGroup == 0 ? $dst : $mheardGroup;
            if ($mhSend == 0 && $dst != 'all' && $dst != '*' && $dst == $mheardGroup)
            {
                checkMheard($msgId, $msg, $src, $dst, $callSign, $loraIp, 1);
            }

            #Prüfe ob Baken-OTP Keyword geschickt wurde.
            #Prüfe ob Baken-Gruppe übereinstimmt und Enabled MSg noch nicht gesendet wurde
            #Anfrage gilt nicht, wenn OTP schon gesendet wurde.
            if ($beaconOtp != ''&& $dst == $beaconGroup && $beaconEnabledStatusSend == 0)
            {
                checkBeaconOtp($msgId, $msg, $callSign, $dst, $beaconOtp);
            }

            $parts     = explode(',', $src);
            $firstCall = array_shift($parts); // Nimmt das erste Rufzeichen und entfernt es aus dem Array
            $restCalls = implode(',', $parts);

            $bubbleFontMsgHeader = '';
            $bubbleFontMsg       = '';
            $fromToSquare        = '';

            if ($bubbleStyleView === true)
            {
                $cssInOutBubble = (strtolower($firstCall) === strtolower($callSign)) ? 'outgoing' : 'incoming';

                $bubbleFontMsgHeader = (strtolower($firstCall) === strtolower($callSign)) ? 'bubbleFontMsgHeaderOutgoing' : 'bubbleFontMsgHeaderIncoming';
                $bubbleFontMsg       = (strtolower($firstCall) === strtolower($callSign)) ? 'bubbleFontMsgOutgoing' : 'bubbleFontMsgIncoming';
                $fromToSquare        = (strtolower($firstCall) === strtolower($callSign)) ? 'fromToSquareOutgoing' : 'fromToSquareIncoming'; //Hervorhebung von/an

                #Bubble CSS-Container
                echo '<div class="chat-container">';
                echo '<div class="message-row ' . $cssInOutBubble . '">';
                echo '<div class="message-bubble" style="max-width:' . $bubbleMaxWidth . '%">';
            }

            echo '<h3 class="setFontMsgHeader ' . $bubbleFontMsgHeader . '">';
            echo $timestamp . ' ' . 'MsgId: ' . $msgId . ' (' . $srcType . ')';

            #Wenn Bestätigung vorliegt dann bild mit grünem Haken einblenden
            if (($msgAckReqDb != 0 && $msgAckDb != '') && ($msgAckReqDb == $msgAckDb))
            {
                echo '<img src="image/ack_icon.png" alt="ack" class="imageAck">';
            }

            if ($restCalls != '')
            {
                echo '<br>VIA: ' . $restCalls;
            }

            echo '</h3>';
            echo '<h3 class="setFontMsg ' . $bubbleFontMsg . '">';

            #Source Call.
            # SRC = 0, DST = 1
            #Nur ausführen wenn:
            # - $firstCall/$dst = AlertSrc Call. Achtung Case Sensitive!
            # - Der Sound noch nicht ausgeführt wurde.
            # - Global die Alert-Sounds nicht abgeschaltet sind.
            # - Der Alert eingeschaltet ist
            if ($alertExecutedSrc == 0 && $noDmAlertGlobal == 0 && isset($arrayNotificationData[$firstCall]) &&
                $arrayNotificationData[$firstCall]['notifySrcDst'] === 0)
            {
                $soundFileId = str_replace('.', '_', $arrayNotificationData[$firstCall]['notifySoundFile']);

                echo '<script>';
                echo 'document.getElementById("' . $soundFileId . '").play();'; // Ton abspielen
                echo '</script>';

                updateMeshDashData($msgId,'alertExecutedSrc', 1, $doSearchQuery);
            }

            #DestinatationCall
            if ($alertExecutedDst == 0 && $noDmAlertGlobal == 0 && isset($arrayNotificationData[$dst]) &&
                $arrayNotificationData[$dst]['notifySrcDst'] === 1)
            {
                $soundFileId = str_replace('.', '_', $arrayNotificationData[$dst]['notifySoundFile']);
                echo '<script>';
                echo 'document.getElementById("' . $soundFileId . '").play();'; // Ton abspielen
                echo '</script>';

                updateMeshDashData($msgId,'alertExecutedDst', 1, $doSearchQuery);
            }

            $alertSrcCss = '';

            if (isset($arrayNotificationData[$firstCall]) && $arrayNotificationData[$firstCall]['notifySrcDst'] === 0)
            {
                $alertSrcCss = 'failureHint';
            }

            $alertDstCss = '';

            #DestinatationCall
            if (isset($arrayNotificationData[$dst]) && $arrayNotificationData[$dst]['notifySrcDst'] === 1)
            {
                $alertDstCss = 'failureHint';
            }

            $dstTxt = $dst == '*' ? 'all' : $dst;

            // URL in der Text-Variable suchen und als Link umwandeln
            $pattern    = '/https?:\/\/[a-zA-Z0-9\.-]+\.[a-zA-Z]{2,3}(\/\S*)?/';
            $replace    = '<a href="$0" target="_blank">$0</a>';
            $linkedText = preg_replace($pattern, $replace, $msg);

            $noticeCall = trim(explode('-', $firstCall)[0]);
            $noticeIcon = '';
            if (in_array($noticeCall, $arrayGetCallNotices))
            {
                $noticeIcon = '<img src="image/call_notice.png" alt="callNotice" class="imageCallNotice">';
            }

            if ($clickOnCall == 0)
            {
                # Call in DM-Feld
                $patternClickOnCall    = '/\b([a-zA-Z0-9]+(?:-\d+)?)\b/';
                #$replaceClickOnCall    = '<span onclick="sendToBottomFrame(\'$1\')" style="cursor: pointer;color:#0000ee" class="' . $alertSrcCss . '" >$0</span>';
                $replaceClickOnCall    = '<span class="bubbleMsgClickToCall "' . $alertSrcCss . '" onclick="sendToBottomFrame(\'$1\')">$0</span>';

                $linkedTextClickOnCall = preg_replace($patternClickOnCall, $replaceClickOnCall, $firstCall);

                echo '<span class="' . $fromToSquare . '">'
                    . '<span class="' . $alertSrcCss . '">' . $linkedTextClickOnCall . $noticeIcon .  '</span> > '
                    . '<span class="' . $alertDstCss . '">' . $dstTxt
                    . '</span> :</span> ' . $linkedText;
            }
            else if ($clickOnCall == 1)
            {
                # Öffne QRZ.com
                $patternClickOnCall    = '/\b([a-zA-Z0-9]+)(?:-\d+)?\b/';
                $replaceClickOnCall    = '<a href="https://qrz.com/db/$1" target="_blank" class="bubbleMsgClickToCall ' . $alertSrcCss . '">$0</a>';
                $linkedTextClickOnCall = preg_replace($patternClickOnCall, $replaceClickOnCall, $firstCall);

                echo '<span class="' . $fromToSquare . '">'
                    . '<span class="' . $alertSrcCss . '">' . $linkedTextClickOnCall
                    . $noticeIcon .  '</span>' . ' > ' . '<span class="' . $alertDstCss . '">' . $dstTxt
                    . '</span> :</span> ' . $linkedText;
            }
            else if ($clickOnCall == 2)
            {
                #Setze Call mit @ in MSG Feld ohne SSID
                $patternClickOnCall    = '/\b([A-Za-z0-9]{3,})(?:-\d+)?\b/i';
                #$replaceClickOnCall    = '<span onclick="sendToBottomMsgFrame(\'$1\')" style="cursor: pointer;color:#0000ee" class="' . $alertSrcCss . '">$0</span>';
                $replaceClickOnCall    = '<span class="bubbleMsgClickToCall "' . $alertSrcCss . '" onclick="sendToBottomMsgFrame(\'$1\')" >$0</span>';
                $linkedTextClickOnCall = preg_replace($patternClickOnCall, $replaceClickOnCall, $firstCall);

                echo '<span class="' . $fromToSquare . '">'
                    . '<span class="' . $alertSrcCss . '">' . $linkedTextClickOnCall . $noticeIcon .  '</span> > '
                    . '<span class="' . $alertDstCss . '">' . $dstTxt
                    . '</span> :</span> ' . $linkedText;
            }
            else
            {
                #popup Notizfeld
                $patternClickOnCall    = '/\b([A-Za-z0-9]{3,})(?:-\d+)?\b/i';
                #$replaceClickOnCall    = '<span class="callNotice" data-callsign = "$1" style="cursor: pointer;color:#0000ee" class="' . $alertSrcCss . '">$0</span>';
                $replaceClickOnCall    = '<span class="callNotice bubbleMsgClickToCall "' . $alertSrcCss . '" data-callsign="$1" >$0</span>';
                $linkedTextClickOnCall = preg_replace($patternClickOnCall, $replaceClickOnCall, $firstCall);

                echo '<span class="' . $fromToSquare . '">'
                    . '<span class="' . $alertSrcCss . '">' . $linkedTextClickOnCall . $noticeIcon . '</span> > '
                    . '<span class="' . $alertDstCss . '">' . $dstTxt
                    . '</span> :</span> ' . $linkedText;
            }

            if ($mhSend == 1)
            {
                echo "&nbsp;->MH-Liste gesendet.";
                echo '<img src="image/ack_icon.png" alt="ack" class="imageMheard">';
            }

            if ($bubbleStyleView === true)
            {
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</h3>';
            }
            else
            {
                echo '</h3><hr>';
            }
        }

        flush();
    }
}

echo '<button id="scrollTopBtn" title="Nach oben">
        <img src="image/' . $scrollToTopImage . '" class="pictureScrollToTop" alt="Nach oben">
      </button>
     ';

echo '</body>';
echo '</html>';
#SnapsHot Flag um Iterationsschleife zu vermeiden
$isSnapshot = $_REQUEST['isSnapshot'] ?? 0;

#Erzeuge html Ausgabe der Nachrichten, wenn aktiviert.
if ($msgExportEnable === true && $msgExportGroup != '' && $isSnapshot == 0)
{
    $ownCall        = strtolower(explode('-', $callSign)[0]);
    $msgExportGroup = strtolower($msgExportGroup);

    $msgExportGroupFile = $msgExportGroup == '*' ? 'all' : $msgExportGroup; // Wenn ALL
    $msgExportGroupFile = $msgExportGroup == $ownCall ? $ownCall : $msgExportGroupFile; // Wenn Own-Call

    $msgExportGroup = $msgExportGroup == '*' ? -1 : $msgExportGroup; // Wenn ALL
    $msgExportGroup = $msgExportGroup == $ownCall ? -2 : $msgExportGroup; // Wenn Own-Call

    $html = file_get_contents(BASE_PATH_URL. 'message.php?isSnapshot=1&group=' . $msgExportGroup);
    file_put_contents('export/' . $msgExportGroupFile . '.html', $html);
}
