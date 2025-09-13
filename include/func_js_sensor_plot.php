<script>
   // $(document).ready(function () {
   $(function ($) {

       $("#btnPlotSensorChart").on("click", function () {

           let titleMsg          = 'Hinweis';
           let outputMsg         = 'Jetzt alle Settings speichern?';
           let width             = 300;
           let sensorPlotFrom = $("#sensorPlotFrom").val();
           let sensorPlotTo   = $("#sensorPlotTo").val();
           let sensorType     = $("#sensorType").val();

           if (sensorPlotFrom === '' || sensorPlotTo === '')
           {
               width = 350;
               outputMsg = 'Bitte ein Start-/Enddatum angeben.';
               dialog(outputMsg, titleMsg, width);
               return false;
           }

           loadChart(sensorType, sensorPlotFrom, sensorPlotTo);
       });

       // const canvas = document.getElementById('sensorChart');
       //
       // canvas.addEventListener('touchstart', e => {
       //     e.preventDefault();  // Page-Zoom blockieren
       // }, { passive: false });


       //////////// Plot Chart
       let sensorChart = null; // <<< ANPASSUNG >>> globale Variable für Chart

       async function loadChart(sensor, from, to) {
           const url = `sensor_data_api.php?sensor=${sensor}&from=${from}&to=${to}`;
           const res = await fetch(url);
           const data = await res.json();

           // Messwerte aufbereiten
           const values = data.map(d => ({
               x: moment(d.timestamp, "YYYY-MM-DD HH:mm:ss").toDate(),
               y: d.value
           }));

           // sicherstellen, dass die Punkte zeitlich aufsteigend sind
           values.sort((a, b) => a.x - b.x);

           // <<< ANPASSUNG >>> Labels nur für vorhandene Messpunkte erzeugen
           const labels = values.map(d => moment(d.x).format("DD.MM.YYYY HH:mm:ss"));

           const ctx = document.getElementById('sensorChart').getContext('2d');

           // <<< ANPASSUNG >>> Vorherigen Chart zerstören, falls vorhanden
           if (sensorChart) {
               sensorChart.destroy();
           }

           // <<< ANPASSUNG >>> Mapping Sensorname → Anzeige
           const sensorLabels = {
               "temp": "Temp IN",
               "tout": "Temp Out",
               "hum": "Humidity",
               "qfe": "Quality of Field Elevation (Qfe)",
               "qnh": "Quality of Nautical Height (Qnh)",
               "altAsl": "Altitude Above Sea Level",
               "bme280": "Bme 280",
               "bme680": "Bme 680",
               "mcu811": "Mcu 811",
               "lsp33": "Lsp 33",
               "oneWire": "OneWire",
               "gas": "gas",
               "eCo2": "eCo2",
               "ina226vBus": "ina226vBus",
               "ina226vShunt": "ina226vShunt",
               "ina226vCurrent": "ina226vCurrent",
               "ina226vPower": "ina226vPower"
           };


           // <<< ANPASSUNG >>> Chart neu erstellen mit Kategorie-Achse
           sensorChart = new Chart(ctx, {
               type: 'line',
               data: {
                   labels: labels, // <<< ANPASSUNG >>> Diskrete x-Achse

                   datasets: [{
                       label: sensorLabels[sensor] || sensor,
                       data: values.map(d => d.y), // Y-Werte
                       borderWidth: 2,
                       borderColor: 'blue',
                       backgroundColor: 'rgba(0, 123, 255, 0.2)',
                       tension: 0,
                       pointRadius: 3,
                       pointBackgroundColor: 'white',
                       pointBorderColor: 'blue',
                       fill: false
                   }]
               },
               options: {
                   responsive: true,
                   maintainAspectRatio: true, // Wenn Zoom genutzt wird
                   interaction: {
                       mode: 'nearest',
                       axis: 'x',
                       intersect: false
                   },
                   plugins: {
                       tooltip: {
                           callbacks: {
                               title: ctx => ctx[0].label, // <<< ANPASSUNG >>> Label aus Kategorie
                               label: ctx => ctx.parsed.y.toFixed(2) + ' °C'
                           }
                       },
                       legend: {
                           display: true
                       },
                       zoom: {
                           pan: {
                               enabled: true,   //Scroll über die Zeitleiste
                               mode: 'x',
                               modifierKey: 'ctrl',
                           },
                           zoom: {
                               wheel: {
                                   enabled: true,
                               },
                               pinch: {
                                   enabled: true,
                               },
                               drag: {
                                   enabled: true //Muss für selektiertes Zoom aktiv sein
                               },
                             mode: 'x',
                           },

                       }
                   },
                   scales: {
                       x: {
                           type: 'category', // <<< ANPASSUNG >>> nur vorhandene Punkte anzeigen
                           title: {
                               display: true,
                               text: 'Zeit'
                           },
                           ticks: {
                               maxRotation: 45,
                               minRotation: 45,
                               autoSkip: true,               // <<< ANPASSUNG >>> automatisch Labels überspringen
                               maxTicksLimit: 10             // <<< ANPASSUNG >>> maximal 10 Labels anzeigen
                           }
                       },
                       y: {
                           title: {
                               display: true, text: 'Messwert (°C)'
                           }
                       }
                   }
               }
           });

       }

       async function loadCharty(sensor, from, to) {
           const url = `sensor_data_api.php?sensor=${sensor}&from=${from}&to=${to}`;
           const res = await fetch(url);
           const data = await res.json();

           // Messwerte aufbereiten
           const values = data.map(d => ({
               x: moment(d.timestamp, "YYYY-MM-DD HH:mm:ss").toDate(),
               y: d.value
           }));

           // sicherstellen, dass die Punkte zeitlich aufsteigend sind
           values.sort((a, b) => a.x - b.x);

           // <<< ANPASSUNG >>> Labels nur für vorhandene Messpunkte erzeugen
           const labels = values.map(d => moment(d.x).format("DD.MM.YYYY HH:mm:ss"));

           const ctx = document.getElementById('sensorChart').getContext('2d');

           // <<< ANPASSUNG >>> Vorherigen Chart zerstören, falls vorhanden
           if (sensorChart) {
               sensorChart.destroy();
           }

           // <<< ANPASSUNG >>> Mapping Sensorname → Anzeige
           const sensorLabels = {
               "temp": "Temp IN",
               "tout": "Temp Out",
               "hum": "Humidity",
               "qfe": "Quality of Field Elevation (Qfe)",
               "qnh": "Quality of Nautical Height (Qnh)",
               "altAsl": "Altitude Above Sea Level",
               "bme280": "Bme 280",
               "bme680": "Bme 680",
               "mcu811": "Mcu 811",
               "lsp33": "Lsp 33",
               "oneWire": "OneWire",
               "gas": "gas",
               "eCo2": "eCo2",
               "ina226vBus": "ina226vBus",
               "ina226vShunt": "ina226vShunt",
               "ina226vCurrent": "ina226vCurrent",
               "ina226vPower": "ina226vPower"
           };


           // <<< ANPASSUNG >>> Chart neu erstellen mit Kategorie-Achse
           sensorChart = new Chart(ctx, {
               type: 'line',
               data: {
                   labels: labels, // <<< ANPASSUNG >>> Diskrete x-Achse

                   datasets: [{
                       label: sensorLabels[sensor] || sensor,
                       data: values.map(d => d.y), // Y-Werte
                       borderWidth: 2,
                       borderColor: 'blue',
                       backgroundColor: 'rgba(0, 123, 255, 0.2)',
                       tension: 0,
                       pointRadius: 3,
                       pointBackgroundColor: 'white',
                       pointBorderColor: 'blue',
                       fill: false
                   }]
               },
               options: {
                   responsive: true,
                   maintainAspectRatio: true, // Wenn Zoom genutzt wird
                   interaction: {
                       mode: 'nearest',
                       axis: 'x',
                       intersect: false
                   },
                   plugins: {
                       tooltip: {
                           callbacks: {
                               title: ctx => ctx[0].label, // <<< ANPASSUNG >>> Label aus Kategorie
                               label: ctx => ctx.parsed.y.toFixed(2) + ' °C'
                           }
                       },
                       legend: {
                           display: true
                       },
                       zoom: {
                           zoom: {
                               wheel: {
                                   enabled: true,
                               },
                               pinch: {
                                   enabled: true,
                               },
                               drag: {
                                   enabled: true,
                               },
                               speed: 0.05,  // <<< ANPASSUNG >>> feineres Zoomen per Zwei-Finger
                               mode: 'x',
                               limits: {
                                   x: { min: 'original', max: 'original' } // verhindert Zoom über die Daten hinaus
                               },
                           },
                           pan: {
                               enabled: true,               // optional: Drag für Verschieben
                               mode: 'x',
                               scaleMode: 'x',
                           },
                       }
                   },
                   scales: {
                       x: {
                           type: 'category', // <<< ANPASSUNG >>> nur vorhandene Punkte anzeigen
                           title: {
                               display: true,
                               text: 'Zeit'
                           },
                           ticks: {
                               maxRotation: 45,
                               minRotation: 45,
                               autoSkip: true,               // <<< ANPASSUNG >>> automatisch Labels überspringen
                               maxTicksLimit: 10             // <<< ANPASSUNG >>> maximal 10 Labels anzeigen
                           }
                       },
                       y: {
                           title: {
                               display: true, text: 'Messwert (°C)'
                           }
                       }
                   }
               }
           });

           //Fallback wenn es mal nicht klappt
           // sensorChart.canvas.addEventListener("touchstart", touchHandler, true);
           // sensorChart.canvas.addEventListener("touchmove", touchHandler, true);
           // sensorChart.canvas.addEventListener("touchend", touchHandler, true);
           // sensorChart.canvas.addEventListener("touchcancel", touchHandler, true);

       }

       ///////////// Dialog Section

       function dialogConfirm(output_msg, title_msg, width, sendData) {
            width      = !width ? 300 : width;
            title_msg  = !title_msg ? '' : title_msg;
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
                        $("#frmMheard").trigger('submit');
                    }, 'Abbruch': function () {
                        $(this).dialog("close");
                    }
                }
            }).prev(".ui-dialog-titlebar").css("background", "red");
        }

       function dialog(outputMsg, titleMsg, width) {
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