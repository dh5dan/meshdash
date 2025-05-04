<script>
    $(function ($) {

        $("#sendCommand").on("keydown", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
            }
        });

        $("#infoImagePoint").on("click", function ()
        {
            let titleMsg = 'Hinweis';
            let outputMsg = '';
            let width = 700;
            let loraIp = $("#loraIp").val();

            outputMsg += '<b>−−extudpip <IPv4-Ziel></b> Setzte Zielhost wohin UDP-Pakete <span class="indented">gesendet werden sollen</span><br>';
            outputMsg += '<b>−−extudp on/off</b> Aktiviere/Deaktiviere die Aussendung <span class="indented">von UDP-Paketen an das eingestelltes Ziel.</span><br>';
            outputMsg += '<b>−−proz</b> Display-Anzeige wechselt die Anzeige <span class="indented">zu Akku in %</span><br>';

            outputMsg += '<b>−−volt</b> Display-Anzeige wechselt die Anzeige <span class="indented">zu Akku in V</span><br>';
            outputMsg += '<b>−−proz</b> Display-Anzeige wechselt die Anzeige <span class="indented">zu Akku in %</span><br>';
            outputMsg += '<b>−−maxv 99.9</b> maximale Ladespannung in V<br>';
            outputMsg += '<b>−−button on/off</b> aktiviert/deaktiviert den User-Button <span class="indented">(TBEAM, TLORA, ESP32/E22, HELTEC V2/V3)</span><br>';
            outputMsg += '<b>−−all</b> bringt wieder laufend POS Meldungen am Display <span class="indented">bis eine Meldung kommt</span><br>';
            outputMsg += '<b>−−msg</b> bringt nur Meldungen am Display <span class="indented">(ab der nächsten Meldung)</span><br>';
            outputMsg += '<b>−−display off/on</b> Damit wird das Display abgeschaltet <span class="indented">und nur bei einer Text-Meldung für 30 Sekunden</span><span class="indented"> aktiviert (Gateway bleibt permanent off)</span><br>';
            outputMsg += '<b>−−sendpos</b> eine POS-Meldung mit der letzten <span class="indented">gespeicherten Position wird sofort gesendet</span><br>';
            outputMsg += '<b>−−setlat xx.xxxxxx</b> in ° setzen wenn kein GPS <span class="indented">vorhanden (Werte > 0 East)</span><br>';
            outputMsg += '<b>−−setlon xxx.xxxxxx w.o.</b> (Werte > 0 Nord)<br>';
            outputMsg += '<b>−−utcoff +/- 99.9</b> setzen der Zeitdifferenz zwischen <span class="indented">der lokalen zeit und UTC</span><br>';

            outputMsg += '<b>−−track on</b> aktiviert die Anzeige für Smart-Beaconing <span class="indented">(Sendeintervall in Sekunden, Heading in ° (last/aktuell), </span><span class="indented">Distance in Meter (zum letzten Punkt))</span><br>';
            outputMsg += '<b>−−track off</b> deaktiviert die Anzeige für Smart-Beaconing<br>';
            outputMsg += '<b>−−wx</b> Zeigt Wetterdaten an <span class="indented">(BME280/BMP280 muss vorhanden sein)</span><br>';

            outputMsg += '<b>−−pos</b> die aktuellen gespeicherte Position <span class="indented">und das Datum/Uhrzeit abfragen</span><br>';
            outputMsg += '<b>−−gps on/off</b> aktiviert GPS-Abfragen <span class="indented">(notwendig, wenn GPS-Zusatzhardware verwendet wird)</span><br>';
            outputMsg += '<b>−−bme on</b> aktiviert den BME280 Sensor<br>';
            outputMsg += '<b>−−bmp on</b> aktiviert den BMP280 Sensor<br>';
            outputMsg += '<b>−−bmx off</b> deaktiviert den BME280, BMP280 bzw. <span class="indented">BME680 Sensor</span><br>';
            outputMsg += '<b>−−680 on/off</b> aktiviert oder deaktiviert den <span class="indented">BME680 Sensor</span><br>';

            outputMsg += '<b>−−811 on/off</b> aktiviert oder deaktiviert den <span class="indented">CMCU811 Sensor</span><br>';
            outputMsg += '<b>−−onewire on/off</b> aktiviert/deaktiviert den Onewire <span class="indented">DS-Temperatur-Sensor</span><br>';
            outputMsg += '<b>−−onewire gpio 99</b> legt den OneWire GPIO PIN fest<br>';
            outputMsg += '<b>−−reboot</b> Die Firmware wird neu geladen und gestartet <span class="indented">(Daten im Flash-Speicher bleiben erhalten)</span><br>';
            outputMsg += '<b>−−setcall Callsign</b> setzen (nur gültige Calls laut APRS) <span class="indented">(Auto.-Reboot nach 15 Sekunden)</span><br>';

            outputMsg += '<b>−−setctry xx  [EU, UK, US, VR2, 868, 915]</b> <span class="indented">setz die Landabhängigen LoRa-RX/TX-Parameter</span><span class="indented">siehe Tabelle (Auto.-Reboot nach 15 Sekunden)</span><br>';
            outputMsg += '<b>−−setssid WIFIssid</b> Wifi-SSID setzen <span class="indented">(Auto.-Reboot nach 15 Sekunden wenn ssid und pwd </span><span class="indented">gesetzt sind) max. 32 Zeichen</span><br>';
            outputMsg += '<b>−−setpwd WIFIpassword</b> Wifi Passwort setzen <span class="indented">(Auto.-Reboot nach 15 Sekunden, wenn ssid und pwd </span><span class="indented"> gesetzt sind) max. 63 Zeichen</span><br>';
            outputMsg += '<b>−−setgrc …</b> Eingabe des Gruppenfilters für den Node<br>';
            outputMsg += '<br><b>-Beispiele:<br>−−setgrc232;2321;262;20)<br>−−setgrc löscht die Gruppen</b><br><br>';

            outputMsg += '<b>−−gateway on/off</b> start/stop Gateway zum <span class="indented">MeshCom-Server via WIFI/ETH-connect</span><br>';
            outputMsg += '<b>−−webserver on/off</b> start/stop WEBService <span class="indented">via Wifi/ETH IP-Verbindung</span><br>';

            outputMsg += '<b>−−setwifiap on/off</b> WEBService <span class="indented">in Access-Point-Mode setzen</span><br>';
            outputMsg += '<b>−−showi2c</b> Zeigt die Adressen der aktuell <span class="indented">angeschlossenen I2C-Komponenten an</span><br>';
            outputMsg += '<b>−−webpwd xxxxx</b> setzt das Password für die Web-GUI<br>';
            outputMsg += '<b>−−btcode NNNNNN</b> Custom BLE PIN. <span class="indented">Default 000000. 6 Ziffern und > 100000</span><br>';
            outputMsg += '<b>−−ota-update</b> Startet den Node in das OTA Update<br>';

            dialog(outputMsg, titleMsg, width)

        });

        $("#btnSendCommand").on("click", function ()
        {
            let titleMsg          = 'Hinweis';
            let outputMsg         = '';
            let sendData          = 1;
            let width             = 400;
            let sendCommand = $("#sendCommand").val();

            if (sendCommand === '')
            {
                outputMsg = 'Bitte ein Kommando in der Befehlszeile angeben.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            $("#btnSendCommand").prop('disabled', true);
            $(".btnPreCmd").prop('disabled', true);
            $("#sendData").val(sendData);
            $("#frmSendCommand").trigger('submit');

            return false;
        });

        $(".btnPreCmd").on("click", function ()
        {
            let sendCommandData = $(this).data('cmd');
            let sendData        = 1;
            let loraIp = $("#loraIp").val();
            let titleMsg = 'OTA-Update';
            let maxHeight = '300';
            let outputMsg;
            let width = 700;

            if (sendCommandData === '--ota-update')
            {
                outputMsg ='<b>Ota-Update erkannt!</b><br>'
                outputMsg +='Sie werden im Anschluss auf die Webseite in einem neuen Tab<br>';
                outputMsg +='zum Lora Gerät umgeleitet, um dort das Update auszuführen.<br><br>'
                outputMsg +='<b><u>Wichtig!</u></b><br><span style="color: red">Sollte ein Popup-Blocker aktiv sein,<br>'
                outputMsg +='muss die Lora Seite <u><b>http://'+loraIp +'</b></u><br>manuell geöffnet werden!</span><br><br>'
                outputMsg +='Die Umleitung erfolgt ca 5sek. nach Ausführung des Befehls,<br>'
                outputMsg +='da das Gerät etwas Zeit braucht um den OTA-Mode zu starten.<br>'
                outputMsg +='Ansonsten einfach den Refresh des Browsers nutzen,<br>'
                outputMsg +='um die Lora Seite neu zu laden.<br><br>'
                outputMsg +='Einleitung des OTA-Updates jetzt ausführen?'
                dialogConfirm(outputMsg, titleMsg, width, sendData, sendCommandData, maxHeight, function ()
                {
                    // Neuer Tab mit der LoRa-IP öffnen
                    window.open('http://' + loraIp, '_blank');
                });

                return false;
            }

            $("#sendCommand").val(sendCommandData);
            $("#sendData").val(sendData);
            $("#frmSendCommand").trigger('submit');

            return false;
        });


        function dialogConfirm(outputMsg, titleMsg, width, sendData, sendCommandData, maxHeight, callback) {
            let isMobile = window.innerWidth <= 600; // Prüfen, ob es ein Smartphone ist

            width     = isMobile ? "auto" : (width || 300); // Auto-Breite für Smartphones
            maxHeight = maxHeight || 400; // Standardhöhe setzen, falls nichts übergeben wird
            titleMsg  = titleMsg || '';
            outputMsg = outputMsg || '';

            // Dynamische Schriftgröße abhängig von der Fensterbreite
            // let fontSize = isMobile ? "4vw" : "1.2em"; // Kleinere Schrift auf Smartphones
            let fontSize = isMobile ? "3vw" : "1.1em"; // Kleinere Schrift auf Smartphones

            // **Countdown-Element hinzufügen**
            let countdownTime = 5;
            let countdownText = `<br><br><strong>Automatischer Start nach OK in <span id="countdown">${countdownTime}</span> Sekunden...</strong>`;

            let $dialog = $("<div></div>").html(outputMsg + countdownText).dialog({
                title: titleMsg,
                resizable: true,
                modal: true,
                width: width,
                maxWidth: isMobile ? "90%" : null, // Max 90% der Bildschirmbreite
                minWidth: isMobile ? "200px" : "300px", // Mindestbreite setzen
                maxHeight: maxHeight, // Maximale Höhe setzen
                overflow: "auto", // Scrollbar aktivieren, falls nötig
                buttons: {
                    'OK': function ()
                    {
                        jQuery('.ui-dialog button:nth-child(1)').button('disable'); // Disable OK
                        jQuery('.ui-dialog button:nth-child(2)').button('disable'); // Disable Abbruch

                        let txCmd      = $("#sendCommand");
                        let txSendData = $("#sendData");
                        let txFrm      = $("#frmSendCommand");
                        let txLoader   = $("#pageLoading");

                        if (typeof callback === "function")
                        {
                            // Prüfen, ob das Formular eine gültige Action hat
                            let formAction = txFrm.attr('action') || '';
                            let formMethod = txFrm.attr('method') || 'POST';

                            $.ajax({
                                url: formAction,
                                type: formMethod,
                                data: {
                                    sendCommand: sendCommandData,
                                    sendData: sendData
                                },
                                success: function () {

                                    // **Countdown starten**
                                    setInterval(function ()
                                    {
                                        countdownTime--;
                                        $("#countdown").text(countdownTime);
                                    }, 1000);

                                    // Nach erfolgreichem Senden 5 Sekunden warten, dann weiter
                                    setTimeout(function () {
                                        //Starte den Timeout und öffne den neuen Tab nach 2 Sekunden
                                        callback(); // Hier wird der neue Tab geöffnet

                                        // Seite neu laden und die Parameter weitergeben
                                        txCmd.val(sendCommandData);
                                        txSendData.val(sendData);
                                        txFrm.trigger('submit');

                                    }, 5000);
                                },
                                error: function (xhr, status, error) {
                                    console.error("AJAX Fehler:", status, error);
                                }
                            });

                            //$(this).dialog('close');
                            return false;
                        }

                        // Falls kein Callback definiert wurde, führe normale Aktion aus
                        txCmd.val(sendCommandData);
                        txSendData.val(sendData);
                        txFrm.trigger('submit');
                        txLoader.show();

                        $(this).dialog('close');
                    }, 'Abbruch': function ()
                    {
                        $(this).dialog("close");
                    }
                }
            });

            // Titelbar anpassen
            $dialog.prev(".ui-dialog-titlebar").css("background", "red");

            // Schriftgröße direkt in .ui-widget setzen (wichtig, da jQuery UI das überschreibt)
            $(".ui-widget").css("font-size", fontSize);
        }

        function dialog(outputMsg, titleMsg, width, maxHeight) {
            let isMobile = window.innerWidth <= 600; // Prüfen, ob es ein Smartphone ist

            width = isMobile ? "auto" : (width || 300); // Auto-Breite für Smartphones
            maxHeight = maxHeight || 400; // Standardhöhe setzen, falls nichts übergeben wird
            titleMsg = titleMsg || '';
            outputMsg = outputMsg || '';

            // Dynamische Schriftgröße abhängig von der Fensterbreite
            // let fontSize = isMobile ? "4vw" : "1.2em"; // Kleinere Schrift auf Smartphones
            let fontSize = isMobile ? "3vw" : "1.1em"; // Kleinere Schrift auf Smartphones

            let $dialog = $("<div></div>").html(outputMsg).dialog({
                title: titleMsg,
                resizable: true,
                modal: true,
                width: width,
                maxWidth: isMobile ? "90%" : null, // Max 90% der Bildschirmbreite
                minWidth: isMobile ? "200px" : "300px", // Mindestbreite setzen
                maxHeight: maxHeight, // Maximale Höhe setzen
                overflow: "auto", // Scrollbar aktivieren, falls nötig
                buttons: {
                    'Hinweis schliessen': function () {
                        $(this).dialog("close");
                    }
                }
            });

            // Titelbar anpassen
            $dialog.prev(".ui-dialog-titlebar").css("background", "red");

            // Schriftgröße direkt in .ui-widget setzen (wichtig, da jQuery UI das überschreibt)
            $(".ui-widget").css("font-size", fontSize);
        }



    });

</script>