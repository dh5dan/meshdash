<?php

function getLatestMessages($group)
{
    require_once 'dbinc/param.php';
    require_once 'include/func_php_core.php';
    #require_once 'include/func_js_message.php';
    require_once 'include/func_php_message.php';
    require_once 'include/func_php_index.php';
    require_once 'include/func_php_mheard.php';

    if (!file_exists('database/meshdash.db') ||
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
    $messages = [];
    ob_start();

    #Hole Parameter für NoPos und DMA-Alert aus Datenbank
    $loraIp             = trim(getParamData('loraIp'));
    $noPosData          = (int) getParamData('noPosData');
    $noDmAlertGlobal    = (int) getParamData('noDmAlertGlobal');
    $posStatusValue     = (int) getParamData('noPosData');
    $noTimeSyncMsgValue = (int) getParamData('noTimeSyncMsg');
    $maxScrollBackRows  = (int) getParamData('maxScrollBackRows');
    $callSign           = trim(getParamData('callSign'));

    $alertSoundFileSrc = getParamData('alertSoundFileSrc');
    $alertEnabledSrc   = getParamData('alertEnabledSrc');
    $alertSoundCallSrc = getParamData('alertSoundCallSrc');

    $alertSoundFileDst = getParamData('alertSoundFileDst');
    $alertEnabledDst   = getParamData('alertEnabledDst');
    $alertSoundCallDst = getParamData('alertSoundCallDst');
    $clickOnCall       = getParamData('clickOnCall');

    #Prüfe ob Logging aktiv ist
    $doLogEnable = getParamData('doLogEnable');

    #Prüfe, ob das reine Rufzeichen nur genommen werden soll ohne SSID
    $strictCallEnable = getParamData('strictCallEnable') ?? 0;

    $sqlAddon = '';
    $group    = $_REQUEST['group'] ?? -1;

    $callSignSql = $callSign;

    if ($strictCallEnable == 1)
    {
        $callSignSql = explode("-", $callSign, 2)[0]; // Trennen nach dem ersten '-'
    }

    echo '<input type="hidden" id="group" value="' . $group . '" />';

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
        $sqlAddon .= " AND src GLOB '[A-Za-z]*' "; // src muss ein Rufzeichen sein
        $sqlAddon .= " AND dst GLOB '[A-Za-z]*' "; // dst muss ein Rufzeichen sein
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

    #Soundfiles Preload
    echo '<audio id="alertSoundSrc" src="sound\\' . $alertSoundFileSrc . '" preload="auto"></audio>';
    echo '<audio id="alertSoundDst" src="sound\\' . $alertSoundFileDst . '" preload="auto"></audio>';

    #Werte für Jquery die dann im Bottom Frame abgebildet werden
    echo '<input type="hidden" id="posStatusValue" value="' . $posStatusValue . '" />';
    echo '<input type="hidden" id="noTimeSyncMsgValue" value="' . $noTimeSyncMsgValue . '" />';

    //#Prevents Buffering in Browser for flush()
    //ini_set('zlib.output_compression',0);
    //ini_set('implicit_flush',1);
    //#ob_end_clean(); //verhindert laden von css
    //set_time_limit(0);

    # Lighttpd Prevent Buffering
    # add this to /etc/lighttpd.conf
    #server.stream-response-body = 1

    $db = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
    $db->busyTimeout(5000); // warte wenn busy in millisekunden

    // Hole mir die letzten 30 Nachrichten aus der Datenbank
    $result = $db->query(
        "SELECT * 
                              FROM meshdash
                              WHERE msgIsAck = 0
                              $sqlAddon
                          ORDER BY timestamps DESC
                             LIMIT $maxScrollBackRows"
    );
    # Maybe False when Database is locked

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
        $keyword1Text      = getParamData('keyword1Text');
        $keyword1Cmd       = getParamData('keyword1Cmd');
        $keyword1ReturnMsg = getParamData('keyword1ReturnMsg');
        $keyword1DmGrpId   = getParamData('keyword1DmGrpId');
    }

    #Check if Keyword2 is enabled
    if (getParamData('keyword2Enabled') == 1 || $debugFlag === true)
    {
        $keyword2Text      = getParamData('keyword2Text');
        $keyword2Cmd       = getParamData('keyword2Cmd');
        $keyword2ReturnMsg = getParamData('keyword2ReturnMsg');
        $keyword2DmGrpId   = getParamData('keyword2DmGrpId');
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

            $msgAckReq = 0; // Acknowledge Request
            $msgAck    = 0; // Acknowledge

            #Wenn Leer ist es i.d.R. ein eigene Aussendung
            $callSign = getParamData('callSign');
            $src      = $src == '' ? $callSign : $src;
            $srcType  = $srcType == '' ? 'msg' : $srcType;
            $msgSplit = explode('{', $msg);

            #Berücksichtige nicht Zeitmeldungen von OE1XAR-45
            if (count($msgSplit) > 1 && strpos($msg, '{CET}') === false)
            {
                $msg       = $msgSplit[0];
                $msgAckReq = (int) $msgSplit[1];
            }

            #Ersetzte durch aussagekräftige Meldung von OE1XAR-45,
            # wenn Flag noTimeSync = 0 sonst continue;
            #chtung ergebnis muss auf Type geprüft werden da sonst ein false als 0 interpretiert wird
            if (strpos($msg, '{CET}') !== false && $noTimeSyncMsgValue == 0)
            {
                $msg = str_replace('{CET}', ' TimeSync: CET ', $msg);
            }
            else if (strpos($msg, '{CET}') !== false && $noTimeSyncMsgValue == 1)
            {
                #Wird jetzt in SQL abgefangen
                updateMeshDashData($msgId, 'msgIsTimeSync', 1);
                continue;
            }

            #Prüfe auf Keyword Ergebnisdaten für msgID
            $resGetKeywordsData = getKeywordsData($msgId);
            $keywordExecuted    = $resGetKeywordsData['executed'];
            $keywordErrorCode   = $resGetKeywordsData['errCode'];

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

            #Check auf Keyword1
            if ($keyword1Text != '' && $keyword1Cmd != '' && $msg != '' && $keywordExecuted == 0)
            {

                if (strpos($msg, $keyword1Text) !== false && $dst == $keyword1DmGrpId)
                {
                    execScriptCurl($keyword1Cmd);

                    $keyword1ErrorCode = 0; //debug
                    $keyword1ErrorText = '';

                    setKeywordsData($msgId, 1, $keyword1ErrorCode, $keyword1ErrorText);

                    #Sende nur, wenn kein Fehler aufgetreten ist
                    if ($keyword1ReturnMsg != '' && $keyword1ErrorCode == 0)
                    {
                        $arraySend['type'] = 'msg';
                        $arraySend['dst']  = $keyword1DmGrpId;
                        $arraySend['msg']  = $keyword1ReturnMsg;

                        $message = json_encode($arraySend, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                        if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))
                        {
                            if ($doLogEnable == 1)
                            {
                                $errorText = date('Y-m-d H:i:s') . ' Keyword1-ReturnMsg:' . $keyword1ReturnMsg . "\n";
                                $errorText .= date('Y-m-d H:i:s') . ' Keyword1-ReturnMsg JSON:' . $message . "\n";
                                file_put_contents(
                                    'log/keyword_return_data_' . date('Ymd') . '.log', $errorText, FILE_APPEND
                                );
                            }

                            socket_sendto($socket, $message, strlen($message), 0, $loraIp, 1799);
                            socket_close($socket);
                        }
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
                        $arraySend['type'] = 'msg';
                        $arraySend['dst']  = $keyword2DmGrpId;
                        $arraySend['msg']  = $keyword2ReturnMsg;

                        $message = json_encode($arraySend, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                        if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))
                        {
                            if ($doLogEnable == 1)
                            {
                                $errorText = date('Y-m-d H:i:s') . ' Keyword2-ReturnMsg:' . $keyword2ReturnMsg . "\n";
                                $errorText .= date('Y-m-d H:i:s') . ' Keyword2-ReturnMsg JSON:' . $message . "\n";
                                file_put_contents(
                                    'log/keyword_return_data_' . date('Ymd') . '.log', $errorText, FILE_APPEND
                                );
                            }

                            socket_sendto($socket, $message, strlen($message), 0, $loraIp, 1799);
                            socket_close($socket);
                        }
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
                $firmware        = $row['firmware'] ?? '';  // Batt Kapazität in %
                $altitude        = number_format($altitude * 0.3048); // Umrechnung Fuss -> Meter

                echo '<h3 class="setFontMsgHeader">';
                echo 'MsgId: ' . $msgId . ' (' . $srcType . ')<br>' . $timestamp . ' ';
                echo 'Quelle ' . $src . ', Ziel all</h3>';

                echo '<div class="info-container">';
                echo '<div class="info-row"><span class="info-label">Längengrad: </span><span class="info-value">' . $lat . ' ' . $latDir . '</span></div>';
                echo '<div class="info-row"><span class="info-label">Breitengrad: </span><span class="info-value">' . $long . ' ' . $longDir . '</span></div>';
                echo '<div class="info-row"><span class="info-label">Höhe: </span><span class="info-value">' . $altitude . ' m</span></div>';
                echo '<div class="info-row"><span class="info-label">Batteriekapazität: </span><span class="info-value">' . $batteryCapacity . ' %</span></div>';

                if ($firmware != '')
                {
                    echo '<div class="info-row"><span class="info-label">Firmware: </span><span class="info-value">' . $firmware . '</span></div>';
                }

                echo '</div>';

                echo '</h3><hr>';
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
                    updateMeshDashData($msgId, 'msgIsAck', 1);
                    continue;
                }

                #Prüfe ob Mheard Keyword geschickt wurde
                if ($mhSend == 0)
                {
                    checkMheard($msgId, $msg, $src, $callSign, $loraIp);
                }

                echo '<h3 class="setFontMsgHeader">';
                echo $timestamp . ' ' . 'MsgId: ' . $msgId . ' (' . $srcType . ')';

                #Wenn Bestätigung vorliegt dann bild mit grünem Haken einblenden
                if (($msgAckReqDb != 0 && $msgAckDb != '') && ($msgAckReqDb == $msgAckDb))
                {
                    echo '<img src="image/ack_icon.png" alt="ack" class="imageAck">';
                }

                $parts     = explode(',', $src);
                $firstCall = array_shift($parts); // Nimmt das erste Rufzeichen und entfernt es aus dem Array
                $restCalls = implode(',', $parts);

                echo '<br>VIA: ' . $restCalls . '</h3>';
                echo '<h3 class="setFontMsg">';

                #Source Call.
                #
                #Nur ausführen wenn:
                #src call = AlertSrc Call.
                #Der sound noch nicht ausgeführt wurde.
                #Global die alert Sounds nicht abgeschaltet sind.
                #Der SrcAlert eingeschaltet ist
                if (strcasecmp(
                        $firstCall, $alertSoundCallSrc
                    ) === 0 && $alertExecutedSrc == 0 && $noDmAlertGlobal == 0 && $alertEnabledSrc == 1)
                {
                    echo '<script>';
                    echo 'document.getElementById("alertSoundSrc").play();'; // Ton abspielen
                    echo '</script>';

                    updateMeshDashData($msgId, 'alertExecutedSrc', 1);
                }

                #DestinatationCall
                if (strcasecmp(
                        $dst, $alertSoundCallDst
                    ) === 0 && $alertExecutedDst == 0 && $noDmAlertGlobal == 0 && $alertEnabledDst == 1)
                {
                    echo '<script>';
                    echo 'document.getElementById("alertSoundDst").play();'; // Ton abspielen
                    echo '</script>';

                    updateMeshDashData($msgId, 'alertExecutedDst', 1);
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
                    $patternQrz    = '/\b([a-zA-Z0-9]+(?:-\d+)?)\b/';
                    $replaceQrz    = '<a href="#" onclick="sendToBottomFrame(\'$1\')">$0</a>';
                    $linkedTextQrz = preg_replace($patternQrz, $replaceQrz, $firstCall);

                    echo '<span class="' . $alertSrcCss . '">' . $linkedTextQrz . '</span> > ' . '<span class="' . $alertDstCss . '">' . $dstTxt . '</span> : ' . $linkedText;
                }
                else if ($clickOnCall == 1)
                {
                    $patternQrz    = '/\b([a-zA-Z0-9]+)(?:-\d+)?\b/';
                    $replaceQrz    = '<a href="https://qrz.com/db/$1" target="_blank">$0</a>';
                    $linkedTextQrz = preg_replace($patternQrz, $replaceQrz, $firstCall);

                    echo '<span class="' . $alertSrcCss . '">' . $linkedTextQrz . '</span>' . ' > ' . '<span class="' . $alertDstCss . '">' . $dstTxt . '</span> : ' . $linkedText;
                }
                else
                {
                    $patternQrz    = '/\b([a-zA-Z0-9]+(?:-\d+)?)\b/';
                    $replaceQrz    = '<a href="#" onclick="sendToBottomMsgFrame(\'$1\')">$0</a>';
                    $linkedTextQrz = preg_replace($patternQrz, $replaceQrz, $firstCall);

                    echo '<span class="' . $alertSrcCss . '">' . $linkedTextQrz . '</span> > ' . '<span class="' . $alertDstCss . '">' . $dstTxt . '</span> : ' . $linkedText;
                }

                if ($mhSend == 1)
                {
                    echo "&nbsp;->MH-Liste gesendet.";
                    echo '<img src="image/ack_icon.png" alt="ack" class="imageMheard">';
                }

                echo '</h3><hr>';
            }
        }
    }

    $messages[] = ob_get_clean();
    return $messages;
}

function getLatestMessagesX($group = 0): array
{
    $MAX_MESSAGES = 60;
    $messages = [];

    $db = new SQLite3('database/meshdash.db', SQLITE3_OPEN_READONLY);
    $db->busyTimeout(5000); // warte wenn busy in millisekunden

    // Bereite die SQL-Abfrage vor
    $stmt = $db->prepare("SELECT msg_id, timestamps, msg, src_type, type, src, latitude, longitude 
                          FROM meshdash 
                          WHERE mhSend = 0 
                          ORDER BY timestamps DESC LIMIT :maxMessages");
    $stmt->bindValue(':maxMessages', $MAX_MESSAGES, SQLITE3_INTEGER);

    // Führe die Abfrage aus
    $result = $stmt->execute();

    // Verarbeite die Ergebnisse
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        ob_start();
        ?>
        <div >

            <span class="text"><?= nl2br(htmlspecialchars($row['msg'])) ?></span>

        </div>
        <?php
        $messages[] = ob_get_clean();
    }

    // Schließe die Datenbankverbindung
    $db->close();

    return $messages;
}