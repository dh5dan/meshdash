<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Nachrichten</title>';

#Prevents UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );
echo '<script type="text/javascript" src="jquery/jquery.min.js"></script>';

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

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';
require_once 'include/func_js_message.php';
require_once 'include/func_php_message.php';
require_once 'include/func_php_index.php';
require_once 'include/func_php_mheard.php';

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

#Hole Parameter für NoPos und DMA-Alert aus Datenbank
$loraIp             = trim(getParamData('loraIp'));
$noPosData          = (int) getParamData('noPosData');
$noDmAlertGlobal    = (int) getParamData('noDmAlertGlobal');
$noTimeSyncMsgValue = (int) getParamData('noTimeSyncMsg');
$maxScrollBackRows  = (int) getParamData('maxScrollBackRows');
$callSign           = trim(getParamData('callSign'));
$posStatusValue     = $noPosData;

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

#Soundfiles Preload
echo '<audio id="alertSoundSrc" src="sound\\' . $alertSoundFileSrc . '" preload="auto"></audio>';
echo '<audio id="alertSoundDst" src="sound\\' . $alertSoundFileDst . '" preload="auto"></audio>';

#Werte für Jquery die dann im Bottom Frame abgebildet werden
echo '<input type="hidden" id="posStatusValue" value="'. $posStatusValue . '" />';
echo '<input type="hidden" id="noTimeSyncMsgValue" value="'. $noTimeSyncMsgValue . '" />';

$db = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
$db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

if ($searchPage == '' && $doSearchQuery === true)
{
    // Für Pagination: Gesamtanzahl holen (ohne LIMIT)
    $countQuery  = "SELECT COUNT(*) AS total FROM meshdash $sqlAddonSearch";
    $countResult = $db->query($countQuery);
    $totalRows   = $countResult->fetchArray(SQLITE3_ASSOC)['total'];
    $totalPages  = ceil($totalRows / $perPage);

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

    $result = $db->query($searchQuery);
}
else
{
    # Hole mir die letzten xx Nachrichten aus der Datenbank
    # Maybe False when Database is locked
    $result = $db->query("SELECT * 
                              FROM meshdash
                             WHERE msgIsAck = 0
                                   $sqlAddon
                          ORDER BY timestamps DESC
                             LIMIT $maxScrollBackRows");
}

#Init Values
$keyword1Text      = '';
$keyword1Cmd       = '';
$keyword1ReturnMsg = '';
$keyword1DmGrpId   = '*';

$keyword2Text      = '';
$keyword2Cmd       = '';
$keyword2ReturnMsg = '';
$keyword2DmGrpId   = '*';

$debugFlag = false;

#Check if Keyword1 is enabled
if (getParamData('keyword1Enabled') == 1 || $debugFlag === true)
{
    $keyword1Text           = getParamData('keyword1Text');
    $keyword1Cmd            = getParamData('keyword1Cmd');
    $keyword1ReturnMsg      = getParamData('keyword1ReturnMsg');
    $keyword1DmGrpId        = getParamData('keyword1DmGrpId');
}

#Check if Keyword2 is enabled
if (getParamData('keyword2Enabled') == 1 || $debugFlag === true)
{
    $keyword2Text           = getParamData('keyword2Text');
    $keyword2Cmd            = getParamData('keyword2Cmd');
    $keyword2ReturnMsg      = getParamData('keyword2ReturnMsg');
    $keyword2DmGrpId        = getParamData('keyword2DmGrpId');
}

if ($debugFlag === true)
{
    echo "<br>keyword1Enabled :<b>" .getParamData('keyword1Enabled').'</b>';
    echo "<br>keyword1Text<b>:$keyword1Text".'</b>';
    echo "<br>keyword1Cmd<b>:$keyword1Cmd".'</b>';
    echo "<br>keyword1ReturnMsg<b>:$keyword1ReturnMsg".'</b>';
    echo "<br>keyword1DmGrpId<b>:$keyword1DmGrpId".'</b>';
    echo "<br>---------------------------";
    echo "<br>keyword2Enabled:<b>" .getParamData('keyword2Enabled').'</b>';
    echo "<br>keyword2Text:<b>$keyword2Text".'</b>';
    echo "<br>keyword2Cmd:<b>$keyword2Cmd".'</b>';
    echo "<br>keyword2ReturnMsg:<b>$keyword2ReturnMsg".'</b>';
    echo "<br>keyword2DmGrpId:<b>$keyword2DmGrpId".'</b>';
}

#Prevents Error on fetch array
if ($result !== false)
{
    while ($row = $result->fetchArray(SQLITE3_ASSOC))
    {
        ###############################################
        #Common
        $srcType          = $row['src_type'] ?? ''; // node, lora
        $type             = $row['type'] ?? '';     // pos / msg
        $src              = $row['src'] ?? '';     // <call>-<sid>
        $msg              = $row['msg'] ?? '';     //
        $msgId            = $row['msg_id']; // 72378728
        $timestamp        = $row['timestamps'] ?? date('Y-m-d H:i:s');  // Timestamp added by myself
        $dst              = $row['dst'] ?? ''; // 995 | call
        $msgAckReqDb      = $row['ackReq'] ?? '';
        $msgAckDb         = $row['ack'] ?? '';
        $mhSend           = $row['mhSend'] ?? 0;
        $alertExecutedSrc = $row['alertExecutedSrc'] ?? 0;
        $alertExecutedDst = $row['alertExecutedDst'] ?? 0;

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
        $resGetKeywordsData = getKeywordsData($msgId);
        $keywordExecuted    = $resGetKeywordsData['executed'];
        $keywordErrorCode   = $resGetKeywordsData['errCode'];

        if ($debugFlag === true)
        {
            echo "<br>resGetKeywordsData#$msgId#<pre>";
            print_r($resGetKeywordsData);
            echo "</pre>";
        }

        #Wenn es einen fehler mit dem Keyword gab dann Meldung hier ausgeben
        if ($keywordExecuted == 1 && $keywordErrorCode > 0)
        {
            $keywordCmd = $keyword2Cmd;
            if (strpos($msg, $keyword1Text) !== false && $dst == $keyword1DmGrpId)
            {
                $keywordCmd = $keyword1Cmd;
            }

            $msg .= '<br>';
            $msg .= '<span class="failureHint">';
            $msg .= 'Fehler bei Ausführung des CMD: ' . $keywordCmd;
            $msg .= '<br>ErrorCode: ' . $keywordErrorCode;
            $msg .= '<br>Error: ' . $resGetKeywordsData['errText'] . '</span>';
        }

        if ($debugFlag === true)
        {
            echo "<br>msgid: $msgId exec:" . $keywordExecuted . " loraIp=$loraIp";
        }

        #Check auf Keyword1
        if ($keyword1Text != '' && $keyword1Cmd != '' && $msg != '' && $keywordExecuted == 0)
        {
            if ($debugFlag === true)
            {
                echo "<br>dst:$dst";
                echo "<br>keyword1DmGrpId:$keyword1DmGrpId";
            }

            if (strpos($msg, $keyword1Text) !== false && $dst == $keyword1DmGrpId)
            {
                execScriptCurl($keyword1Cmd);

                $keyword1ErrorCode = 0; //debug
                $keyword1ErrorText = '';

                setKeywordsData($msgId, 1, $keyword1ErrorCode, $keyword1ErrorText);

                #Sende nur, wenn kein Fehler aufgetreten ist
                if ($keyword1ReturnMsg != '' && $keyword1ErrorCode == 0)
                {
                    $arraySend['txType'] = 'msg';
                    $arraySend['txDst']  = $keyword1DmGrpId;
                    $arraySend['txMsg']  = $keyword1ReturnMsg;
                    $resSetTxQueue       = setTxQueue($arraySend);
                }
            }
        }

        #Check auf Keyword2
        if ($keyword2Text != '' && $keyword2Cmd != '' && $msg != '' && $keywordExecuted == 0)
        {
            if (strpos($msg, $keyword2Text) !== false && $dst == $keyword2DmGrpId)
            {
                execScriptCurl($keyword2Cmd);

                $keyword2ErrorCode = 0;
                $keyword2ErrorText = '';

                setKeywordsData($msgId, 1, $keyword2ErrorCode, $keyword2ErrorText);

                if ($keyword2ReturnMsg != '' && $keyword2ErrorCode == 0)
                {
                    $arraySend['txType'] = 'msg';
                    $arraySend['txDst']  = $keyword2DmGrpId;
                    $arraySend['txMsg']  = $keyword2ReturnMsg;
                    $resSetTxQueue       = setTxQueue($arraySend);
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
            #echo 'Quelle: ' . $src . ', Ziel: all</h3>';

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
            #
            #Nur ausführen wenn:
            #src call = AlertSrc Call.
            #Der sound noch nicht ausgeführt wurde.
            #Global die alert Sounds nicht abgeschaltet sind.
            #Der SrcAlert eingeschaltet ist
            if (strcasecmp($firstCall, $alertSoundCallSrc) === 0 && $alertExecutedSrc == 0 && $noDmAlertGlobal == 0 && $alertEnabledSrc == 1)
            {
                echo '<script>';
                echo 'document.getElementById("alertSoundSrc").play();'; // Ton abspielen
                echo '</script>';

                updateMeshDashData($msgId,'alertExecutedSrc', 1, $doSearchQuery);
            }

            #DestinatationCall
            if (strcasecmp($dst, $alertSoundCallDst) === 0 && $alertExecutedDst == 0 && $noDmAlertGlobal == 0 && $alertEnabledDst == 1)
            {
                echo '<script>';
                echo 'document.getElementById("alertSoundDst").play();'; // Ton abspielen
                echo '</script>';

                updateMeshDashData($msgId,'alertExecutedDst', 1, $doSearchQuery);
            }

            $alertSrcCss = '';

            if (strcasecmp($firstCall, $alertSoundCallSrc) === 0 && $alertEnabledSrc == 1)
            {
                $alertSrcCss = 'failureHint';
            }

            $alertDstCss = '';

            #DestinatationCall
            if (strcasecmp($dst, $alertSoundCallDst) === 0 && $alertEnabledDst == 1)
            {
                $alertDstCss = 'failureHint';
            }

            $dstTxt = $dst == '*' ? 'all' : $dst;

            // URL in der Text-Variable suchen und als Link umwandeln
            $pattern    = '/https?:\/\/[a-zA-Z0-9\.-]+\.[a-zA-Z]{2,3}(\/\S*)?/';
            $replace    = '<a href="$0" target="_blank">$0</a>';
            $linkedText = preg_replace($pattern, $replace, $msg);

            if ($clickOnCall == 0)
            {
                # Call in DM-Feld
                $patternClickOnCall = '/\b([a-zA-Z0-9]+(?:-\d+)?)\b/';
                $replaceClickOnCall    = '<span onclick="sendToBottomFrame(\'$1\')" style="cursor: pointer;color:#0000ee">$0</span>';
                $linkedTextClickOnCall = preg_replace($patternClickOnCall, $replaceClickOnCall, $firstCall);

                echo '<span class="' . $fromToSquare . '"><span class="' . $alertSrcCss . '">' . $linkedTextClickOnCall. '</span> > ' . '<span class="' . $alertDstCss . '">' . $dstTxt . '</span> :</span> ' . $linkedText;
            }
            else if ($clickOnCall == 1)
            {
                # Öffne QRZ.com
                $patternClickOnCall    = '/\b([a-zA-Z0-9]+)(?:-\d+)?\b/';
                $replaceClickOnCall    = '<a href="https://qrz.com/db/$1" target="_blank">$0</a>';
                $linkedTextClickOnCall = preg_replace($patternClickOnCall, $replaceClickOnCall, $firstCall);

                echo '<span class="' . $fromToSquare . '"><span class="' . $alertSrcCss . '">' . $linkedTextClickOnCall . '</span>' . ' > ' . '<span class="' . $alertDstCss . '">' . $dstTxt . '</span> :</span> ' . $linkedText;
            }
            else
            {
                #Setze Call mit @ in MSG Feld ohne SSID
                #$patternClickOnCall = '/\b([a-zA-Z0-9]+(?:-\d+)?)\b/';
                $patternClickOnCall = '/\b([A-Za-z0-9]{3,})(?:-\d+)?\b/i';
                $replaceClickOnCall    = '<span onclick="sendToBottomMsgFrame(\'$1\')" style="cursor: pointer;color:#0000ee">$0</span>';
                $linkedTextClickOnCall = preg_replace($patternClickOnCall, $replaceClickOnCall, $firstCall);

                echo '<span class="' . $fromToSquare . '"><span class="' . $alertSrcCss . '">' . $linkedTextClickOnCall. '</span> > ' . '<span class="' . $alertDstCss . '">' . $dstTxt . '</span> :</span> ' . $linkedText;
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

echo '</body>';
echo '</html>';
#SnapsHot Flag um Iterationsschleife zu vermeiden
$isSnapshot = $_REQUEST['isSnapshot'] ?? 0;

#Erzeuge html Ausgabe der Nachrichten, wenn aktiviert.
if ($msgExportEnable === true && $msgExportGroup != '' && $isSnapshot == 0)
{
    $html = file_get_contents('http://localhost/5d/message.php?isSnapshot=1&group=' . $msgExportGroup);
    file_put_contents($msgExportGroup.'.html', $html);
}