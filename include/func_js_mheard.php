<script>
   $(function ($) {

       let leafletMap;

        $("#btnGetMheard").on("click", function ()
        {
            let sendData  = 1;

            $("#pageLoading").show();
            $("#sendData").val(sendData);
            $("#frmMheard").trigger('submit');
        });

       $("#btnGetMheardOpenStreet").on("click", function ()
       {
           let outputMsg;
           let titleMsg = 'Mheard-Nodes in OpenStreetMap';
           let width    = 750;

           outputMsg = '<!DOCTYPE html>';
           outputMsg += '<html lang="en">';
           outputMsg += '<head><title>Mheard-Nodes in OpenStreetMap</title>';
           outputMsg += '<meta charset="UTF-8">';
           outputMsg += '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
           outputMsg += '</head>';
           outputMsg += '<body>';
           outputMsg += '<div id="map"></div>';

           dialogOpenStreet(outputMsg, titleMsg, width);

       });

       ////////////// Dialog OpenStreet
       function dialogOpenStreet(contentHtml, title, width) {

           let viewportWidth = $(window).width();
           let dialogWidth   = viewportWidth * 1;  // 95 % der Bildschirmbreite
           if (dialogWidth > 800) dialogWidth = width; // Max-Breite, z.B. width für Desktop

           $("<div></div>").html(contentHtml).dialog({
               title: title,
               resizable: true,
               modal: true,
               width: dialogWidth,
               height: "auto",
               position: { my: "center top", at: "center top", of: window, collision: "fit" },
               open: function () {
                   // Falls vorher schon Map initialisiert: sauber entfernen
                   if (leafletMap)
                   {
                       leafletMap.remove(); // entfernt DOM + Events + Leaflet-Objekte
                       leafletMap = null;
                   }

                   let latitude                = $("#latitude").val();
                   let longitude               = $("#longitude").val();
                   let openStreetTileServerUrl = $("#openStreetTileServerUrl").val();
                   let defaultZoomLevel        = 9;

                   // Jetzt neue Map initialisieren (wichtig: nach dem Dialog-Open!)
                   leafletMap = L.map('map').setView([latitude, longitude], defaultZoomLevel);

                   L.tileLayer('https://{s}.' + openStreetTileServerUrl + '/{z}/{x}/{y}.png', {
                       attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                   }).addTo(leafletMap);

                   $.ajax({
                       url: 'ajax_mheard.php', // Pfad zur PHP-Datei
                       method: 'GET',
                       dataType: 'json',
                       success: function (data) {

                           let ownCallSign = $("#ownCallSign").val();

                           Object.keys(data).forEach(call => {
                               const item = data[call];
                               const lat = parseFloat(item.latitude);
                               const lon = parseFloat(item.longitude);

                               const redIcon = new L.Icon({
                                   iconUrl:
                                       "jquery/leaflet/images/other_color/marker-icon-red.png",
                                   iconSize: [25, 41],
                                   iconAnchor: [12, 41],
                                   popupAnchor: [1, -34],
                                   shadowSize: [41, 41]
                               });

                               // Prüfen auf valide Koordinaten
                               if (!isNaN(lat) && !isNaN(lon)) {

                                   let popupHtml;

                                   if (item.callSign === ownCallSign)
                                   {
                                       popupHtml = `
                                                    <b>${item.callSign}</b><br>
                                                    Altitude: ${item.altitude} m<br>
                                                    Battery: ${item.batt} %<br>
                                                    FW: ${item.firmware} ${item.fw_sub}<br>
                                                    Timestamp: ${item.timestamps}
                                                `;

                                       L.marker([lat, lon], {
                                           icon: redIcon
                                       })
                                        .addTo(leafletMap)
                                        .bindPopup(popupHtml)

                                       //Add Call under Marker
                                       L.marker([lat, lon], {
                                           icon: L.divIcon({
                                               html: '<b>' + item.callSign + '</b>',
                                               className: 'text-below-marker',
                                           })
                                       }).addTo(leafletMap);
                                   }
                                   else
                                   {
                                       popupHtml = `
                                                    <b>${item.callSign}</b><br>
                                                    Hardware: ${item.hardware}<br>
                                                    RSSI: ${item.rssi}<br>
                                                    SNR: ${item.snr}<br>
                                                    Altitude: ${item.altitude} m<br>
                                                    Battery: ${item.batt} %<br>
                                                    Distance: ${item.dist} Km<br>
                                                    Hardware: ${item.hardware}<br>
                                                    FW: ${item.firmware} ${item.fw_sub}<br>
                                                    Timestamp: ${item.timestamps}
                                                `;

                                       L.marker([lat, lon])
                                        .addTo(leafletMap)
                                        .bindPopup(popupHtml);
                                   }

                                   //Add Call under Marker
                                   L.marker([lat, lon], {
                                       icon: L.divIcon({
                                           html: '<b>' + item.callSign + '</b>',
                                           className: 'text-below-marker',
                                       })
                                   }).addTo(leafletMap);
                               }
                           });
                       },
                       error: function (xhr, status, error) {
                           console.error("Fehler beim Laden der Positionsdaten:", error);
                       }
                   });

               },
               close: function () {
                   // Optional: Map entfernen beim Schließen
                   if (leafletMap) {
                       leafletMap.remove();
                       leafletMap = null;
                   }

                   // Dialog-Container komplett entfernen (vermeidet ID-Konflikte)
                   $(this).dialog("destroy").remove();

                   // NEU: Falls showOsm = 1, dann message.php laden
                   if ($("#showOsm").val() === '1')
                   {
                       window.location.href = 'message.php?group=' + $("#group").val();
                   }
               },
               buttons: [
                   {
                       text: "Karte schließen",
                       class: "desktop-only-button",
                       click: function () {
                           $(this).dialog("close");
                       }
                   }
               ]
           }).prev(".ui-dialog-titlebar").css("background", "red");

           $(".ui-dialog-buttonpane").css({
               "padding": "2px",         // reduziert den Innenabstand
               "margin-top": "5px",      // weniger Abstand nach oben
               "height": "auto"          // Höhe an Inhalt anpassen (default ist meist auto)
           });

           $(".ui-dialog-buttonset button").css({
               "padding": "2px 6px",     // kleinerer Button
               "font-size": "12px"       // kleinere Schrift
           });
       }

       //Open OSM on Get Parameter only
       if ($("#showOsm").val() === '1')
       {
           $("#btnGetMheardOpenStreet").click();
       }

       ///////////// Dialog Section

       function dialogConfirmMheard(outputMsg, title_msg, width, sendData) {
            width     = !width ? 300 : width;
            title_msg = !title_msg ? '' : title_msg;
            outputMsg = !outputMsg ? '' : outputMsg;
            sendData  = !sendData ? 0 : sendData;

            $("<div></div>").html(outputMsg).dialog({
                title: title_msg,
                resizable: true,
                modal: true,
                width: width,

                buttons: {
                    'OK': function () {
                        $("#sendData").val(sendData);
                        //$("#frmIndex").submit();
                        $("#frmMheard").trigger('submit');
                    }, 'Abbruch': function () {
                        $(this).dialog("close");
                    }
                }
            }).prev(".ui-dialog-titlebar").css("background", "red");
        }

       function dialogMheard(outputMsg, titleMsg, width) {
           width     = !width ? 300 : width;
           titleMsg  = !titleMsg ? '' : titleMsg;
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