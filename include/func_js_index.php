<script>
   $(function ($)
   {
       /////////////////////////////////////////Hole Sensor Intervallabrufe und setzte ggf. neu /////////////////////////////////////
       // let currentTempInterval = null;  // Speichert das aktuelle Temp-Intervall
       // let currentIna226Interval = null;  // Speichert das aktuelle INA226-Intervall
       // let tempIntervalId = null;  // ID des Temp setInterval
       // let ina226IntervalId = null;  // ID des INA226 setInterval
       //
       // // Funktion zum Abrufen der Intervallwerte
       // function getIntervalAndStart()
       // {
       //     $.ajax({
       //         url: 'get_intervall.php',  // PHP-Datei, die die Intervallwerte zurückgibt
       //         type: 'GET',
       //         dataType: 'json',
       //         success: function (data) {
       //             // Überprüfen, ob der Temp-Intervallwert gültig ist und den Intervall starten
       //             if (data.temp !== undefined)
       //             {
       //                 if (data.temp === 0)
       //                 {
       //                     if (tempIntervalId !== null)
       //                     {
       //                         clearInterval(tempIntervalId); // Stoppt den laufenden Temp-Intervall
       //                         tempIntervalId = null;
       //                         console.log("Temp Intervall gestoppt (Intervallwert 0)");
       //                     }
       //                 }
       //                 else
       //                 {
       //                     if (currentTempInterval !== data.temp)
       //                     {
       //                         currentTempInterval = data.temp;
       //
       //                         // Stoppen des alten Temp-Intervalls
       //                         if (tempIntervalId !== null)
       //                         {
       //                             clearInterval(tempIntervalId);
       //                             console.log("Alter Temp-Timer wurde gestoppt");
       //                         }
       //
       //                         // Setzen des neuen Temp-Intervalls
       //                         tempIntervalId = setInterval(function () {
       //                             // Deine PHP-Logik für Temp ausführen
       //                             $.ajax({
       //                                 url: 'get_sensor_data.php?sensor=temp',  // Hier wird deine Logik ausgeführt
       //                                 type: 'GET',
       //                                 success: function (response) {
       //                                     console.log("Temp PHP-Funktion ausgeführt: " + response);
       //                                 },
       //                                 error: function () {
       //                                     console.log("Fehler beim Ausführen der Temp PHP-Funktion");
       //                                 }
       //                             });
       //                         }, data.temp * 1000);  // Intervall in Sekunden
       //                         console.log("Neuer Temp Intervall-Timer gestartet: " + data.temp + " Sekunden");
       //                     }
       //                 }
       //             }
       //
       //             // Überprüfen, ob der INA226-Intervallwert gültig ist und den Intervall starten
       //             if (data.ina226 !== undefined)
       //             {
       //                 if (data.ina226 === 0)
       //                 {
       //                     if (ina226IntervalId !== null)
       //                     {
       //                         clearInterval(ina226IntervalId); // Stoppt den laufenden INA226-Intervall
       //                         ina226IntervalId = null;
       //                         console.log("INA226 Intervall gestoppt (Intervallwert 0)");
       //                     }
       //                 }
       //                 else
       //                 {
       //                     if (currentIna226Interval !== data.ina226)
       //                     {
       //                         currentIna226Interval = data.ina226;
       //
       //                         // Stoppen des alten INA226-Intervalls
       //                         if (ina226IntervalId !== null)
       //                         {
       //                             clearInterval(ina226IntervalId);
       //                             console.log("Alter INA226-Timer wurde gestoppt");
       //                         }
       //
       //                         // Setzen des neuen INA226-Intervalls
       //                         ina226IntervalId = setInterval(function () {
       //                             // Deine PHP-Logik für INA226 ausführen
       //                             $.ajax({
       //                                 url: 'get_sensor_data.php?sensor=ina226',  // Hier wird deine Logik ausgeführt
       //                                 type: 'GET',
       //                                 success: function (response) {
       //                                     console.log("INA226 PHP-Funktion ausgeführt: " + response);
       //                                 },
       //                                 error: function () {
       //                                     console.log("Fehler beim Ausführen der INA226 PHP-Funktion");
       //                                 }
       //                             });
       //                         }, data.ina226 * 1000);  // Intervall in Sekunden
       //                         console.log("Neuer INA226 Intervall-Timer gestartet: " + data.ina226 + " Sekunden");
       //                     }
       //                 }
       //             }
       //
       //         },
       //         error: function () {
       //             console.log('Fehler beim Abrufen des Intervallwertes');
       //         }
       //     });
       // }
       //
       // // Funktion starten
       // getIntervalAndStart();
       //
       // // Alle x Sekunden die Intervallwerte erneut abfragen
       // setInterval(getIntervalAndStart, 5000); // Alle 5 Sekunden den Intervallwert neu abfragen


      /////////////////////////////////////////Tab Indikator /////////////////////////////////////

       // Objekt für jede Gruppe, um den letzten Fokusverlust zu speichern
       let lastFocusLostTimestamps = {};

        // Variable zur Speicherung der aktuell aktiven Gruppe
       let activeGroupId = -1; // init Wert -1 = Kein Filter
       lastFocusLostTimestamps[activeGroupId] = Math.floor(Date.now() / 1000); // Setze Timestamp für den initialen Tab

        // Intervall-Timer zur Überprüfung neuer Nachrichten
       setInterval(() => {
           $(".tab").each(function ()
           {
               let groupId = $(this).data("group"); // Gruppen-ID aus data-group holen

               // Falls für diese Gruppe noch kein Wert existiert, setze einen
               if (!(groupId in lastFocusLostTimestamps))
               {
                   lastFocusLostTimestamps[groupId] = Math.floor(Date.now() / 1000);
               }

               // Wenn die Gruppe aktiv ist, nicht überschreiben!
               let lastChecked = (groupId === activeGroupId)
                   ? lastFocusLostTimestamps[groupId]  // Bleibt unverändert für den aktiven Tab
                   : lastFocusLostTimestamps[groupId]; // Letzter gespeicherter Wert für inaktive Tabs

              //console.log("lastChecked:" + new Date(lastChecked * 1000).toLocaleString() + " groupId:" + groupId);

               checkNewMessages(groupId, lastChecked);
           });
       }, 5000);

        // Funktion zur Nachrichtenprüfung
       function checkNewMessages(groupId, lastChecked)
       {
           //console.log("ajax checkmsg groupId: "+groupId + " lastChecked: " + new Date(lastChecked * 1000).toLocaleString()+ " lastChecked: "+lastChecked+ " activeGroupId:"+activeGroupId);

           // $(`.tab[data-group="999"]`).addClass("new-message-indicator"); // Markierung setzen
           // $(`.tab[data-group="995"]`).addClass("new-message-indicator"); // Markierung setzen
           // $(`.tab[data-group="-1"]`).addClass("new-message-indicator"); // Markierung setzen
           // $(`.tab[data-group="-2"]`).addClass("new-message-indicator"); // Markierung setzen

           $.getJSON(`check_messages.php?lastChecked=${lastChecked}`, function (data)
           {
               // console.log("ajax checkmsg groupId: "+groupId + " activeGroupId:"+activeGroupId);
               // console.log(data);

               if (data.newMessages.includes(groupId))
               {
                   if (groupId !== activeGroupId)
                   {
                       $(`.tab[data-group="${groupId}"]`).addClass("new-message-indicator"); // Markierung setzen
                   }
               }
           });
       }

       /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

       // Zeit vom Server holen und in den Update_Pausen via Jquery weiter führen
       let serverTime = null;
       let offset = 0;

       function fetchServerTime()
       {
           $.getJSON("ajax_time.php", function (data)
           {
               serverTime = new Date(data.time);
               offset     = serverTime - new Date(); // Differenz zwischen Server- und Client Zeit
               data       = null;
           });
       }

       function updateDateTime()
       {
           if (serverTime)
           {
               let now            = new Date(new Date().getTime() + offset); // Korrigierte Zeit
               let dateTimeString = now.toLocaleString("de-DE", {
                   day: "2-digit",
                   month: "2-digit",
                   year: "numeric",
                   hour: "2-digit",
                   minute: "2-digit",
                   second: "2-digit"
               });
               $("#datetime").text(dateTimeString);
           }
       }

       fetchServerTime(); // Initial holen
       setInterval(fetchServerTime, 60000); // Alle 60 Sekunden Serverzeit abrufen
       setInterval(updateDateTime, 1000); // Jede Sekunde lokale Zeit aktualisieren

       /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

       let isTabClick = false; // Globale Variable die prüft, ob Tab geklickt wurde

       // Refresh via Ajax für message.php
       function loadNewMessages()
       {
           if (isTabClick) return; // Falls gerade ein Tab-Klick aktiv ist, beende die Funktion

           let messageFrame = $("#message-frame"); // ID des Iframes
           let currentSrc   = messageFrame.attr("src");

           if (currentSrc && currentSrc.includes("message.php"))
           {
               let doc            = messageFrame.contents();
               let groupValue     = doc.find("#group").val();
               let scrollPosition = doc.scrollTop(); // Aktuelle Scroll-Position merken

               // Prüfen, ob der Benutzer ganz oben ist (Scroll-Position = 0)
               let isAtTop = (scrollPosition === 0);

               // `message.php` ruft die neuen Nachrichten ab
               $.get("message.php", {group: groupValue}, function (data)
               {
                   if (isAtTop)
                   {
                       let body = doc.find("body");
                       body.empty();        // Vorherigen Inhalt sicher entfernen
                       body.append(data);   // Neuen Inhalt einfügen
                       doc.scrollTop(scrollPosition); // Scroll-Position wiederherstellen
                   }

                   // Referenzen explizit freigeben
                   data = null;
                   doc = null;
                   body = null;
               });
           }
       }

       setInterval(loadNewMessages, 5000); // Alle 5 Sekunden aktualisieren

       /////////////////////////////
       // Bg Task Start/Stop

        //Kreis anklicken, um BG-Prozess zu starten oder zu stoppen
       $("#bgTask").on("click", function ()
       {
           let titleMsg  = 'Hinweis';
           let outputMsg = 'Hintergrundprozess jetzt beenden?'
           let width     = 700;
           let sendData  = 1;

           let taskStatusFlag = $("#taskStatusFlag").val();

           if (taskStatusFlag === '0')
           {
               outputMsg = 'Hintergrundprozess jetzt starten?';
               sendData  = 2;
           }

           dialogConfirm(outputMsg, titleMsg, width, sendData)

           return false;
       });

       //Bei Neuinstallation loraIp/Call setzen
       $("#btnSetParamLoraIp").on("click", function ()
       {
           let loraIp          = $("#paramSetLoraIp").val();
           let callSign        = $("#inputParamCallSign").val();
           let titleMsg        = 'Hinweis';
           let outputMsg       = 'Parameter jetzt speichern?';
           let width           = 400;
           let sendData        = 11;
           let ipv4Pattern     = /^(\d{1,3}\.){3}\d{1,3}$/;
           let callSignPattern = /^[a-zA-Z]{2}[0-9]{1}[a-zA-Z]{1,3}-([1-9][0-9]?)$/

           if (loraIp === '')
           {
               outputMsg = 'Bitte die Ip im IPv4 Format angeben.';
               outputMsg += '<br><br>Beispiel: 192.168.0.123';
               dialog(outputMsg, titleMsg, width);
               return false;
           }
           else if (ipv4Pattern.test(loraIp) === false) {
               outputMsg = 'Die Ip hat nicht das gültige Format oder enthält ungültige Zeichen.';
               outputMsg += '<br><br>Bitte Prüfen.';
               dialog(outputMsg, titleMsg, width);
               return false;
           }

           if ($('#inputParamCallSign').length)
           {
               if (callSign === '')
               {
                   outputMsg = 'Bitte das CallSign inkl. SSID angeben.';
                   outputMsg += '<br><br>Beispiel:<br>DB0ABC-99 wobei die SSID 1-99 sein darf.';
                   dialog(outputMsg, titleMsg, width);
                   return false;
               }
               else if (callSignPattern.test(callSign) === false) {
                   let width       = 600;
                   outputMsg = 'Das Rufzeichen inkl. SSID hat nicht das gültige Format';
                   outputMsg += '<br> oder die SSID ist > 99 oder ist 0.';
                   outputMsg += '<br><br>Bitte Prüfen.';
                   dialog(outputMsg, titleMsg, width);
                   return false;
               }
           }

           dialogConfirmParam(outputMsg, titleMsg, width, sendData)

           return false;
       });

       //Bei Update meshdash komplet neu laden, damit updates greifen
       $("#btnParamReload").on("click", function ()
       {
           $("#frmParamIp").trigger('submit');

           return false;
       });

       /*      Menüfunktion                        */
       /*                                  */
       // Menü umschalten, wenn auf das Menü-Icon geklickt wird
       $('#menu-icon').on("click", function ()
       {
           $('#menu').toggle();
       });

       // Klick auf ein Menüelement (li), um das Submenü ein- oder auszublenden
       $('#menu > ul > li').on("click", function (e)
       {
           e.stopPropagation(); // Verhindert, dass der Klick das Dokument schließt
           $(this).toggleClass('active'); // Toggle die 'active'-Klasse für das Submenü
           $(this).siblings().removeClass('active'); // Entfernt die 'active'-Klasse von anderen Submenüs
       });

       // Klick auf das Dokument außerhalb des Menüs schließt das Menü und Submenüs
       $(document).on("click", function (e)
       {
           if (!$(e.target).closest('#menu, #menu-icon').length)
           {
               $('#menu > ul > li').removeClass('active'); // Alle Submenüs ausblenden
               $('#menu').hide(); // Menü ausblenden
           }
       });

       // Zugriff auf die Iframes um Click für Menu-Close abzufangen
       function addIframeClickListeners()
       {
           const iframes = ['#message-frame', '#bottom-frame'];

           iframes.forEach(function (iframeId)
           {
               const iframe = document.querySelector(iframeId);
               if (iframe && iframe.contentWindow)
               {
                   iframe.contentWindow.document.addEventListener('click', function ()
                   {
                       $('#menu > ul > li').removeClass('active'); // Alle Submenüs ausblenden
                       $('#menu').hide();
                   });
               }
           });
       }

       // Event-Listener für Klicks auf Menüeinträge
       $('#menu li').on('click', function ()
       {
           let action = $(this).data('action'); // Holt sich die Aktion für den angeklickten Punkt
           let iframeSrc;
           isTabClick = true;

           switch(action) {
               case 'config_generally':
                   iframeSrc = 'menu/config_generally.php';
                   break;
               case 'config_alerting':
                   iframeSrc = 'menu/config_alerting.php';
                   break;
               case 'config_keyword':
                   iframeSrc = 'menu/config_keyword.php';
                   break;
               case 'config_update':
                   iframeSrc = 'menu/config_update.php';
                   break;
               case 'lora_info':
                   iframeSrc = 'menu/lora_info.php';
                   break;
               case 'config_data_purge':
                   iframeSrc = 'menu/config_data_purge.php';
                   break;
               case 'config_ping_lora':
                   iframeSrc = 'menu/config_ping_lora.php';
                   break;
               case 'grp_definition':
                   iframeSrc = 'menu/grp_definition.php';
                   break;
               case 'message':
                   iframeSrc = 'message.php';
                   break;
               case 'send_command':
                   iframeSrc = 'menu/send_command.php';
                   break;
               case 'sensor_data':
                   iframeSrc = 'menu/sensor_data.php';
                   break;
               case 'sensor_threshold':
                   iframeSrc = 'menu/sensor_threshold.php';
                   break;
               case 'mHeard':
                   iframeSrc = 'mheard.php';
                   break;
               case 'about':
                   let titleMsg  = 'Info'
                   let outputMsg = '';
                   let version   = $("#version").val();
                   let width     = 600;

                   outputMsg  = 'MeshDash ' + version;
                   outputMsg += '<br>Basierend auf der ursprünglichen Version von Andre DL4QB';
                   outputMsg += '<br><br>Erweitert als reine PHP-Version mit tatkräftiger';
                   outputMsg += '<br>Unterstützung von Andre, wie auch zahlreichen Beta-Tester.';
                   outputMsg += '<br><br>73 Christian DH5DAN.';

                   dialog(outputMsg, titleMsg, width)
                   return false;

               default:
                   iframeSrc = ''; // Fallback
           }

           setTimeout(() => { isTabClick = false; }, 500); // warte 500ms das seite geladen wurde

           if (iframeSrc !== '')
           {
               $('#menu').hide();
               $('.submenu').removeClass('active');

               // Setze das src-Attribut des Iframes
               let iframe = $('#message-frame')[0]; // Zugriff auf das Iframe-Element
               iframe.src = iframeSrc; // Setze den neuen src-Wert

               //Muss einmal komplett geladen werden, damit Top aktualisiert wird
               if (iframeSrc === 'message.php')
               {
                   isTabClick = true;
                   window.location.href = '';
                   //
                   // //Rufe die Basis URL neu auf und verhinder, dass diese synchron ausgeführt wird.
                   // // das verhindert ein NS_BINDING_ABORTED
                   // setTimeout(function() {
                   //     // Dynamische Base-URL ermitteln
                   //     let baseUrl = window.location.origin + window.location.pathname.replace(/\/[^\/]+\/[^\/]+\/?$/, '');
                   //
                   //     // URL ohne Neuladen der Seite ändern
                   //     history.pushState(null, null, baseUrl);
                   //
                   //     // Dann das vollständige Neuladen durchführen
                   //     location.reload();
                   // }, 100);
               }
           }
       });

       // Listener erst hinzufügen, wenn das Iframe geladen ist
       $('#message-frame, #bottom-frame').on('load', function ()
       {
           addIframeClickListeners();
       });

       ///////////////// Top Tabs

       // JSON aus dem Hidden-Feld auslesen und parsen
       let tabs = JSON.parse($('#tabConfig').val());

       // Container für Tabs
       let tabsContainer = $('#top-tabs');
       tabsContainer.empty();

       // Erstelle für jeden Tab einen Button oder ein Element
       $.each(tabs, function (index, tabData)
       {
           let tab = $('<button class="tab"></button>')
               .text(tabData.label)
               .attr('data-group', tabData.id);

           // Setze z. B. den "Alles"-Tab als aktiv
           if (tabData.id === -1)
           {
               tab.addClass('active');
           }

           tabsContainer.append(tab);
       });

       // Klick-Handler für Tabs
       $('#top-tabs .tab').on('click', function ()
       {
           isTabClick = true; // loadNewMessages blockieren
           let groupId = $(this).data('group');
           let bottomFrame = $('#bottom-frame');

           // Markiere den angeklickten Tab als aktiv
           $('#top-tabs .tab').removeClass('active');
           $(this).addClass('active');

           // Update die Message-iframe-URL mit dem Filter
           // Annahme: message.php akzeptiert einen GET-Parameter "group"
           $('#message-frame').attr('src', 'message.php?group=' + groupId);

           //Schreibe Werte um für KeinFilter und *
           let groupBottom = groupId; // Unveränderter wert für Bottom own Call Filter

           // // Schreibe Gruppennummer in Abhängig vom Tab in Bottom Iframe und da in das DM-Feld
           groupId = groupId === -1 || groupId === -2 ? '' : groupId;
           groupId = groupId === 0 ? '*' : groupId;
           bottomFrame.contents().find('#bottomDm').val(groupId);
           bottomFrame.contents().find('#groupId').val(groupBottom);

           sessionStorage.setItem('groupId', groupId);  // Speichern der Gruppen-ID für die aktuelle Instanz

           ////////////////////////////// Start Tab-Timestamp
           // Erkenne Tab-Wechsel und aktualisiere Timestamp,
           // für den tab der den Fokus verloren hat.
           let newGroupId = $(this).data("group"); // Neue Gruppe

           //console.log('TabOnClick newGroupId:'+ newGroupId + " activeGroupId:"+activeGroupId);

           if (activeGroupId !== null && activeGroupId !== newGroupId) {
               // Setze den Timestamp NUR für die alte aktive Gruppe
               lastFocusLostTimestamps[activeGroupId] = Math.floor(Date.now() / 1000);
           }

           // Aktualisiere die aktive Gruppe
           activeGroupId = newGroupId;

            // Entferne den roten Punkt beim aktuellen Tab
           $(this).removeClass("new-message-indicator");

           //console.log("Tab gewechselt zu Gruppe:", activeGroupId);
           ///////////////////////////// END Tab-Timestamp

           setTimeout(() => { isTabClick = false; }, 500); // warte 500ms ds seite geladen wurde
       });

       ///////////// Dialog Section

       function dialogConfirm(output_msg, title_msg, width, sendData) {
            width      = !width ? 300 : width;
            title_msg  = !width ? '' : title_msg;
            output_msg = !output_msg ? '' : output_msg;
            sendData   = !sendData ? 0 : sendData;

            $("<div></div>").html(output_msg).dialog({
                title: title_msg,
                resizable: true,
                modal: true,
                width: width,
                buttons: {
                    'OK': function () {
                        $("#sendData").val(sendData);
                        //$("#frmIndex").submit();
                        $("#frmIndex").trigger('submit');
                    }, 'Abbruch': function () {
                        $(this).dialog("close");
                    }
                }
            }).prev(".ui-dialog-titlebar").css("background", "red");
        }

       function dialogConfirmParam(output_msg, title_msg, width, sendData) {
           width      = !width ? 300 : width;
           title_msg  = !width ? '' : title_msg;
           output_msg = !output_msg ? '' : output_msg;
           sendData   = !sendData ? 0 : sendData;

           $("<div></div>").html(output_msg).dialog({
               title: title_msg,
               resizable: true,
               modal: true,
               width: width,
               buttons: {
                   'OK': function () {
                       $("#sendData").val(sendData);
                       $("#frmParamIp").trigger('submit');
                   }, 'Abbruch': function () {
                       $(this).dialog("close");
                   }
               }
           }).prev(".ui-dialog-titlebar").css("background", "red");
       }

       function dialog(outputMsg, titleMsg, width) {
            width     = !width ? 300 : width;
            titleMsg  = !width ? '' : titleMsg;
            outputMsg = !outputMsg ? '' : outputMsg;

            $("<div></div>").html(outputMsg).dialog({
                title: titleMsg,
                resizable: true,
                modal: true,
                width: width,
                buttons: {
                    'Hinweis schliessen': function () {
                        $(this).dialog("close");
                    }
                }
            }).prev(".ui-dialog-titlebar").css("background", "red");
        }

    });

</script>