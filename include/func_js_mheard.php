<script>
   $(function ($) {

       let leafletMap;
       let $btnGetMheardOpenStreet = $("#btnGetMheardOpenStreet");

        $("#btnGetMheard").on("click", function ()
        {
            let sendData  = 1;

            $("#pageLoading").show();
            $("#sendData").val(sendData);
            $("#frmMheard").trigger('submit');
        });

       $btnGetMheardOpenStreet.on("click", function ()
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

           // Default-Datum: Heute und 7 Tage zurück
           const today = new Date();
           const sevenDaysAgo = new Date(today);
           sevenDaysAgo.setDate(today.getDate() - 7);

           let dateFrom = sevenDaysAgo.toISOString().split('T')[0];
           let dateTo   = today.toISOString().split('T')[0];

           dialogOpenStreet(outputMsg, titleMsg, width, dateFrom, dateTo);

       });

       $("#btnGetMheardOpenStreetFullSize").on("click", function ()
       {
           let url = 'mheard_map_fullsize.php';

           window.open(url, '_blank');

       });

       //Damit die Dialog-Funktion auch Global aufrufbar ist und nicht nur im DOM-Context von Jquery
       window.dialogOpenStreet = dialogOpenStreet;

       ////////////// Dialog OpenStreet with repeater Nodes and neighbors
       function dialogOpenStreet(contentHtml, title, width, dateFrom, dateTo, mode = 'dialog') {

           let directLines        = []; // Array für die direkten Links (local)
           let directHitboxes     = []; // Array für die direkten Klick-Links (local)
           let indirectLines      = []; // Array für die indirekten Links (repeater)
           let indirectHitboxes   = []; // Array für die indirekten Klick-Links (repeater)
           let indirectCallLabels = []; // Call-Text unter indirekten Nodes


           let allPolygonVisible      = true; // Initialer Status: Alle Polygone sind sichtbar
           let indirectPolygonVisible = true; // Initialer Status: Alle indirekten Polygone sind sichtbar
           let indirectIconsVisible   = true; // Initialer Status: Alle indirekten Nodes sind sichtbar

           let polygonLines    = []; // Array für die Polygone
           let polygonHitboxes = [];// Array für die Polygone Hit-boxen
           let indirectIcons   = []; // Array für indirekte Marker

           let $container      = $("<div></div>").html(contentHtml);

           // Default-Datum: Heute und 7 Tage zurück
           const today = new Date();
           const sevenDaysAgo = new Date(today);
           sevenDaysAgo.setDate(today.getDate() - 7);

           dateFrom = dateFrom ?? sevenDaysAgo.toISOString().split('T')[0];
           dateTo   = dateTo   ?? today.toISOString().split('T')[0];

           let dialogOpenHandler = function () {

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
                   method: 'POST',
                   dataType: 'json',
                   data: {
                       dateFrom: dateFrom,  // von der Funktion übergebene Variable
                       dateTo: dateTo       // von der Funktion übergebene Variable
                   },
                   success: function (data) {
                       //Hier wid lat/lon des eigenen Standortes gespeichert
                       let ownLat = null;
                       let ownLon = null;

                       let stationCoords  = {}; // callSign -> [lat, lon]
                       let localRepeaters = {}; // localRepeaterCall -> [lat, lon]
                       let pathEdges      = []; // { fromCall, toCall }

                       let ownCallSign = $("#ownCallSign").val();

                       Object.keys(data).forEach(call => {
                           const item = data[call];
                           const lat  = parseFloat(item.latitude);
                           const lon  = parseFloat(item.longitude);

                           const redIcon = new L.Icon({
                               iconUrl:
                                   "jquery/leaflet/images/other_color/marker-icon-red.png",
                               iconSize: [25, 41],
                               iconAnchor: [12, 41],
                               popupAnchor: [1, -34],
                               shadowSize: [41, 41]
                           });

                           const greenIcon = new L.Icon({
                               iconUrl:
                                   "jquery/leaflet/images/other_color/marker-icon-green.png",
                               iconSize: [25, 41],
                               iconAnchor: [12, 41],
                               popupAnchor: [1, -34],
                               shadowSize: [41, 41]
                           });

                           // Prüfen auf valide Koordinaten
                           if (!isNaN(lat) && !isNaN(lon))
                           {
                               let popupHtml;
                               stationCoords[item.callSign] = [lat, lon];

                               //Eintrag ist eigener Standort
                               //if (item.callSign === ownCallSign)
                               if (item.range === 'own')
                               {
                                   ownLat = lat; // merke mir eigene Latitude Koordinate
                                   ownLon = lon; // merke mir eigene Longitude Koordinate

                                   //Pop-up-Fenster was erscheint beim Klicken auf das Icon.
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
                                    .bindPopup(popupHtml);
                               }
                               else if (item.range === 'path')
                               {
                                   // ===== NEU: Path in direkte Hop-Kanten zerlegen =====
                                   if (item.path)
                                   {
                                       let hops = item.path.split(',');

                                       for (let i = 0; i < hops.length - 1; i++)
                                       {
                                           pathEdges.push({
                                               fromCall: hops[i],
                                               toCall: hops[i + 1]
                                           });
                                       }
                                   }

                                   //Pop-up-Fenster was erscheint beim Klicken auf das Icon.
                                   popupHtml = `
                                                    <b>${item.callSign}</b><br>
                                                    Hardware ID: ${item.hwId}<br>
                                                    Altitude: ${item.altitude} m<br>
                                                    Battery: ${item.batt} %<br>
                                                    FW: ${item.firmware} ${item.fw_sub}<br>
                                                    Repeater: ${item.repeater}<br>
                                                    Path: ${item.path}<br>
                                                    Timestamp: ${item.timestamps}
                                                `;

                                   marker = L.marker([lat, lon], {
                                       icon: greenIcon
                                   })
                                    .addTo(leafletMap)
                                    .bindPopup(popupHtml);

                                   indirectIcons.push(marker); // <-- Array befüllen
                               }
                               else if (item.range === 'local')
                               {
                                   // Local-Repeater merken
                                   localRepeaters[item.callSign] = [lat, lon];

                                   //Pop-up-Fenster was erscheint beim Klicken auf das Icon.
                                   popupHtml = `
                                                    <b>${item.callSign}</b><br>
                                                    Hardware: ${item.hardware}<br>
                                                    RSSI: ${item.rssi}<br>
                                                    SNR: ${item.snr}<br>
                                                    Altitude: ${item.altitude} m<br>
                                                    Battery: ${item.batt} %<br>
                                                    Distance: ${item.dist} Km<br>
                                                    FW: ${item.firmware} ${item.fw_sub}<br>
                                                    Timestamp: ${item.timestamps}
                                                `;

                                   L.marker([lat, lon])
                                    .addTo(leafletMap)
                                    .bindPopup(popupHtml);
                               }
                               else
                               {
                                   console.error("unbekannter Stationseintrag beim Laden der Positionsdaten:", error);
                               }

                               //Add Call under Marker
                               let callLabel = L.marker([lat, lon], {
                                   icon: L.divIcon({
                                       html: '<b>' + item.callSign + '</b>',
                                       className: 'text-below-marker',
                                   })
                               }).addTo(leafletMap);

                               // NUR indirekte Nodes merken (für Toggle)
                               if (item.range === 'path') {
                                   indirectCallLabels.push(callLabel);
                               }
                           }
                       });

                       ///////////////////////////////////////////////
                       /*   Zeichne Polygon-Linien                */
                       //////////////////////////////////////////////

                       // 1. Eigener Standort → Repeater
                       if (ownLat !== null && ownLon !== null)
                       {
                           Object.keys(localRepeaters).forEach(repeaterCall => {
                               let repCoordinates = localRepeaters[repeaterCall];
                               let line = L.polyline([[ownLat, ownLon], repCoordinates], {
                                   color: 'blue',
                                   weight: 2,
                                   opacity: 0.7,
                                   interactive: false
                               }).addTo(leafletMap);

                               // Speichere das Polygon
                               polygonLines.push(line);
                               directLines.push(line);

                               // unsichtbare dicke Linie für Maus-Events. Vergrößert Fangbereich.
                               let lineHitbox = L.polyline([[ownLat, ownLon], repCoordinates], {
                                   color: 'transparent',
                                   weight: 12,
                                   interactive: true
                               }).addTo(leafletMap);

                               // Speichere das Polygon Hitbox
                               polygonHitboxes.push(lineHitbox);
                               directHitboxes.push(lineHitbox);

                               // Popup mit den beiden verbundenen Nodes
                               let popupHtmlLine = `<b>Verbundene Nodes:</b><br>${ownCallSign} &harr; ${repeaterCall}`;
                               lineHitbox.bindPopup(popupHtmlLine);
                           });
                       }

                       // 2. Path-Segmente zeichnen (Hop → Hop)
                       pathEdges.forEach(edge => {
                           let from = stationCoords[edge.fromCall];
                           let to   = stationCoords[edge.toCall];

                           if (from && to)
                           {
                               let line = L.polyline([from, to], {
                                   color: 'green',
                                   weight: 2,
                                   opacity: 0.7,
                                   interactive: false
                               }).addTo(leafletMap);

                               // Speichere das Polygon
                               polygonLines.push(line);
                               indirectLines.push(line);

                               // unsichtbare dicke Linie für Maus-Events. Vergrößert Fangbereich.
                               let lineHitbox = L.polyline([from, to], {
                                   color: 'green',
                                   opacity: 0,
                                   weight: 12,
                                   interactive: true
                               }).addTo(leafletMap);

                               // Speichere das Polygon
                               polygonHitboxes.push(lineHitbox);
                               indirectHitboxes.push(lineHitbox);

                               // Popup mit den beiden verbundenen Nodes
                               let popupHtmlLine = `<b>Verbundene Nodes:</b><br>${edge.fromCall} &harr; ${edge.toCall}`;
                               lineHitbox.bindPopup(popupHtmlLine);
                               //lineHitbox.bindTooltip(popupHtmlLine, { permanent: false, direction: 'center' });
                           }
                       });

                       ///////////////////////////////////////////////
                       /*   Custom Map Buttons/Icons                */
                       //////////////////////////////////////////////

                       //Map-Button Alle Node Links EIN/AUS
                       L.control.custom({
                           position: 'topright',
                           content: '<button class="osm-toggle-btn" id="toggleAllLinksBtn">Alle Links aus</button>',
                           classes: 'leaflet-bar',
                           style: {
                               background: '',
                               padding: '0',
                               fontSize: '12px'
                           },
                           events: {
                               click: function (e) {
                                   // wichtig: Klick nicht an die Karte weiterreichen
                                   L.DomEvent.stopPropagation(e);

                                   toggleAllLinks();

                                   // Button-Text anpassen
                                   $('#toggleAllLinksBtn').text(
                                       allPolygonVisible ? 'Alle Links aus' : 'Alle Links ein'
                                   );

                                   normalizeToggleButtonWidths();
                               }
                           }
                       }).addTo(leafletMap);

                       //Map-Button Nur indirekte Node Links EIN/AUS
                       L.control.custom({
                           position: 'topright',
                           content: '<button class="osm-toggle-btn" id="toggleIndirectLinksBtn">Indirekte Links aus</button>',
                           classes: 'leaflet-bar',
                           style: {
                               background: '',
                               padding: '0',
                               fontSize: '12px'
                           },
                           events: {
                               click: function (e) {
                                   // wichtig: Klick nicht an die Karte weiterreichen
                                   L.DomEvent.stopPropagation(e);

                                   toggleIndirectLinks();

                                   $('#toggleIndirectLinksBtn').text(
                                       indirectPolygonVisible ? 'Indirekte Links aus' : 'Indirekte Links ein'
                                   );

                                   normalizeToggleButtonWidths();
                               }
                           }
                       }).addTo(leafletMap);

                       //Map-Button Nur indirekte Node Links/Icons EIN/AUS
                       L.control.custom({
                           position: 'topright',
                           content: '<button class="osm-toggle-btn" id="toggleIndirectIconsBtn">Indirekte Nodes aus</button>',
                           classes: 'leaflet-bar',
                           style: {
                               background: 'white',
                               padding: '0',
                               fontSize: '12px'
                           },
                           events: {
                               click: function (e) {
                                   // wichtig: Klick nicht an die Karte weiterreichen
                                   L.DomEvent.stopPropagation(e);

                                   toggleIndirectIcons();
                               }
                           }
                       }).addTo(leafletMap);

                       //Legende rechts unten
                       let legend = L.control({position: 'bottomright'});

                       legend.onAdd = function () {
                           let div = L.DomUtil.create('div', 'legend');
                           div.style.background = 'white';
                           div.style.padding = '5px';
                           div.style.fontSize = '12px';
                           div.style.boxShadow = '0 0 5px rgba(0,0,0,0.3)';

                           div.innerHTML += '<b class="leaflet-legend-text">Link-Legende</b><br>';
                           //div.innerHTML += '<i style="background: red; width: 12px; height: 12px; display: inline-block; margin-right: 4px;"></i> <span class="leaflet-legend-text">Eigener Standort</span><br>';
                           div.innerHTML += '<i style="background: green; width: 12px; height: 12px; display: inline-block; margin-right: 4px;"></i> <span class="leaflet-legend-text">Indirekter Link</span><br>';
                           div.innerHTML += '<i style="background: blue; width: 12px; height: 12px; display: inline-block; margin-right: 4px;"></i> <span class="leaflet-legend-text">Direkter Link</span><br>';

                           div.innerHTML += '<br><b class="leaflet-legend-text">Marker Icons</b><br>';
                           div.innerHTML += '<img src="jquery/leaflet/images/other_color/marker-icon-red.png" alt="Eigener Standort" height="20"> <span class="leaflet-legend-text">Eigener Standort</span><br>';
                           div.innerHTML += '<img src="jquery/leaflet/images/other_color/marker-icon-blue.png" alt="Direkte Nodes" height="20"> <span class="leaflet-legend-text">Direkte Nodes</span><br>';
                           div.innerHTML += '<img src="jquery/leaflet/images/other_color/marker-icon-green.png" alt="Indirekte Nodes" height="20"> <span class="leaflet-legend-text">Indirekte Nodes</span><br>';

                           return div;
                       };

                       legend.addTo(leafletMap);

                       //Geht nur im fullscreen da nur hier die seite neu aufgerufen werden kann
                       //Dialog Mode zu umständlich
                       if (mode === 'fullscreen')
                       {
                           //Datumsfilter
                           let dateFilter = L.control({position: 'topright'});

                           dateFilter.onAdd = function () {
                               let div = L.DomUtil.create('div', 'leaflet-date-filter');

                               div.innerHTML = `
                                            <label>Datum Von:</label><br>
                                            <input type="date" id="date_from" value="${dateFrom}"><br>

                                            <label>Datum Bis:</label><br>
                                            <input type="date" id="date_to" value="${dateTo}"><br>

                                            <button id="date_filter_btn">Filter Anwenden</button>
                                        `;

                               // verhindert Karten-Zoom beim Klicken
                               L.DomEvent.disableClickPropagation(div);

                               return div;
                           };

                           dateFilter.addTo(leafletMap);
                       }

                       //Button breite einheitlich machen
                       normalizeToggleButtonWidths();
                   },
                   error: function (xhr, status, error) {
                       console.error("Fehler beim Laden der Positionsdaten:", error);
                   }
               });
           }

           function normalizeToggleButtonWidths() {

               let maxWidth = 0;
               let $osmToggleBtn = $('.osm-toggle-btn');

               $osmToggleBtn.each(function () {
                   maxWidth = Math.max(maxWidth, this.offsetWidth);
               });

               $osmToggleBtn.css('width', maxWidth + 'px');
           }

           // Funktion zum Umschalten der Sichtbarkeit
           function toggleAllLinks()
           {
               allPolygonVisible = !allPolygonVisible; // Umkehren des aktuellen Status

               // Abhängigkeit zu indirekten Links berücksichtigen
               if (allPolygonVisible === false)
               {
                   indirectPolygonVisible = false;
               }
               else if (allPolygonVisible === true)
               {
                   indirectPolygonVisible = true;
               }

               $('#toggleIndirectLinksBtn').text(
                   indirectPolygonVisible ? 'Indirekte Links aus' : 'Indirekte Links ein'
               );

               polygonLines.forEach(line => {
                   if (allPolygonVisible)
                   {
                       leafletMap.addLayer(line);
                   }
                   else
                   {
                       leafletMap.removeLayer(line);
                   }
               });

               polygonHitboxes.forEach(hitbox => {
                   if (allPolygonVisible)
                   {
                       leafletMap.addLayer(hitbox);
                   }
                   else
                   {
                       leafletMap.removeLayer(hitbox);
                   }
               });

               // Wenn alle Links eingeschaltet werden und indirekte Icons aus sind → Icons einschalten
               if (allPolygonVisible && indirectPolygonVisible && indirectIconsVisible === false) {
                   toggleIndirectIcons();
               }
           }

           function toggleIndirectLinks()
           {
               indirectPolygonVisible = !indirectPolygonVisible; // Umkehren des aktuellen Status

               // Wenn Links eingeschaltet werden und Icons gerade aus sind, Icons automatisch einschalten
               if (indirectPolygonVisible && indirectIconsVisible === false) {
                   toggleIndirectIcons();
               }

               indirectLines.forEach(line => {
                   if (indirectPolygonVisible)
                   {
                       leafletMap.addLayer(line);
                   }
                   else
                   {
                       leafletMap.removeLayer(line);
                   }
               });

               indirectHitboxes.forEach(hitbox => {
                   if (indirectPolygonVisible)
                   {
                       leafletMap.addLayer(hitbox);
                   }
                   else
                   {
                       leafletMap.removeLayer(hitbox);
                   }
               });
           }

           function toggleIndirectIcons()
           {
               indirectIconsVisible = !indirectIconsVisible;

               indirectIcons.forEach(icon => {
                   if (indirectIconsVisible)
                   {
                       leafletMap.addLayer(icon);
                   }
                   else
                   {
                       leafletMap.removeLayer(icon);
                   }
               });

               // Zwangsweise auch die indirekten Links ein/aus
               indirectPolygonVisible = indirectIconsVisible;
               indirectLines.forEach(line => {
                   if (indirectPolygonVisible && allPolygonVisible) leafletMap.addLayer(line);
                   else leafletMap.removeLayer(line);
               });

               indirectHitboxes.forEach(hitbox => {
                   if (indirectPolygonVisible && allPolygonVisible) leafletMap.addLayer(hitbox);
                   else leafletMap.removeLayer(hitbox);
               });

               $('#toggleIndirectIconsBtn').text(indirectIconsVisible ? 'Indirekte Nodes aus' : 'Indirekte Nodes ein');

               $('#toggleIndirectLinksBtn').text(
                   indirectPolygonVisible ? 'Indirekte Links aus' : 'Indirekte Links ein'
               );

               normalizeToggleButtonWidths();

               indirectCallLabels.forEach(label => {
                   if (indirectIconsVisible) {
                       leafletMap.addLayer(label);
                   } else {
                       leafletMap.removeLayer(label);
                   }
               });

           }

           let dialogCloseHandler = function () {

               //resize Beenden
               $(window).off('resize.openstreet'); // <<< WICHTIG

               // Optional: Map entfernen beim Schließen
               if (leafletMap)
               {
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
           }

           let dialogButtons = [
               {
                   text: "Karte schließen",
                   class: "desktop-only-button",
                   click: function () {
                       $(this).dialog("close");
                   }
               }
           ];

           if (mode === 'dialog')
           {
               let size = calcDialogSize();

               // Dialog PopUp
               $container.dialog({
                   title: title,
                   resizable: true,
                   modal: true,
                   width: size.width,
                   height: size.height,
                   position: {my: "center top", at: "center top", of: window, collision: "fit"},
                   open: dialogOpenHandler,
                   close: dialogCloseHandler,
                   buttons: dialogButtons
               }).prev(".ui-dialog-titlebar").css("background", "red");

               // ===== NEU: Dialog an Frame/Fenster anpassen =====
               $(window).on('resize.openstreet', function () {
                   let newSize = calcDialogSize();

                   $container.dialog("option", {
                       width:  newSize.width,
                       height: newSize.height
                   });

                   // Leaflet korrekt neu zeichnen
                   if (leafletMap) {
                       leafletMap.invalidateSize();
                   }
               });

               // Cleanup beim Schließen
               $container.on("dialogclose", function () {
                   $(window).off("resize.osmDialog");
               });
           }
           else
           {
               // Fullscreen-Variante
               $("body").css("margin", "0").append($container);
               dialogOpenHandler();
           }

           $(".ui-dialog-buttonpane").css({
               "padding": "2px",         // reduziert den Innenabstand
               "margin-top": "5px",      // weniger Abstand nach oben
               "height": "auto"          // Höhe an Inhalt anpassen (default ist meist auto)
           });

           $(".ui-dialog-buttonset button").css({
               "padding": "2px 6px",     // kleinerer Button
               "font-size": "12px"       // kleinere Schrift
           });

           $(document).on('click', '#date_filter_btn', function () {

               let width     = 500;
               let titleMsg  = 'Hinweis';
               let outputMsg;

               let newDateFrom = $('#date_from').val();
               let newDateTo   = $('#date_to').val();

               if (!newDateFrom || !newDateTo)
               {
                   outputMsg = 'Bitte Von/Bis-Datum eintragen.';
                   dialogZ(outputMsg, titleMsg, width);
                   return false;
               }

               // Minimalprüfung
               if (newDateFrom && newDateTo && newDateFrom > newDateTo)
               {
                   outputMsg = 'Das Von-Datum darf nicht größer als Bis-Datum sein.';
                   dialogZ(outputMsg, titleMsg, width);
                   return false;
               }

               // === POST via dynamisches Formular ===
               let $form = $('<form>', {
                   method: 'POST',
                   action: window.location.pathname
               });

               $form.append($('<input>', { type: 'hidden', name: 'date_from', value: newDateFrom }));
               $form.append($('<input>', { type: 'hidden', name: 'date_to',   value: newDateTo }));

               $('body').append($form);
               $form.submit();
           });
       }

       function calcDialogSize() {
           let w = $(window).width();
           let h = $(window).height();

           return {
               width:  Math.floor(w * 0.96),   // 96 % Frame-Breite
               height: Math.floor(h * 0.90)    // 90 % Frame-Höhe
           };
       }

       //Open OSM on Get Parameter only
       if ($("#showOsm").val() === '1')
       {
           $btnGetMheardOpenStreet.click();
       }

       //Spezielle LeafLet variante
       function dialogZ(outputMsg, titleMsg, width)
       {
           width     = width ?? 300;
           titleMsg  = titleMsg ?? '';
           outputMsg = outputMsg ?? '';

           $('<div>')
               .html(outputMsg)
               .appendTo('body')                 // 🔴 WICHTIG
               .dialog({
                   title: titleMsg,
                   modal: true,
                   resizable: true,
                   width: width,
                   close: function () {
                       $(this).remove();          // sauber aufräumen
                   },
                   buttons: {
                       'Hinweis schliessen': function () {
                           $(this).dialog('close');
                       }
                   }
               })
               .parent()
               .css('z-index', 9999)             // 🔴 über Leaflet
               .find('.ui-dialog-titlebar')       // 🔴 Titelbalken
               .css('background', 'red');
       }

   });

</script>