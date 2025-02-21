<script>
   $(function ($)
   {
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
       setInterval(fetchServerTime, 10000); // Alle 10 Sekunden Serverzeit abrufen
       setInterval(updateDateTime, 1000); // Jede Sekunde lokale Zeit aktualisieren

       let isTabClick = false; // Globale Variable die prüft, ob Tab geklickt wurde

       //Refresh via Ajax für message.php
       function loadNewMessages()
       {
           if (isTabClick) return; // Falls gerade ein Tab-Klick aktiv ist, beende die Funktion

           let messageFrame   = $("#message-frame"); // ID des Iframes
           let currentSrc     = messageFrame.attr("src");

           if (currentSrc && currentSrc.includes("message.php"))
           {
               let groupValue     = messageFrame.contents().find("#group").val();
               let scrollPosition = messageFrame.contents().scrollTop(); // Aktuelle Scroll-Position merken

               // Prüfen, ob der Benutzer ganz oben ist (Scroll-Position = 0)
               let isAtTop = (scrollPosition === 0);

               // `message.php` ruft die neuen Nachrichten ab
               $.get("message.php", {group: groupValue}, function (data)
               {
                   // Wenn der Benutzer ganz oben ist, neue Nachrichten laden
                   if (isAtTop)
                   {
                       let doc = messageFrame.contents();
                       //doc.find("body").html(data); // Neuen Inhalt einfügen
                       doc.find("body").empty().append(data); // mal probieren
                       doc.scrollTop(scrollPosition); // Scroll-Position wiederherstellen
                   }

                   data = null; // Speicher freigeben
               });
           }
       }

       setInterval(loadNewMessages, 2000); // Alle 2 Sekunden aktualisieren

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
               case 'mHeard':
                   iframeSrc = 'mheard.php';
                   break;
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