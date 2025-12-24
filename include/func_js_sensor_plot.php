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

       $("#infoImagePoint").on("click", function ()
       {
           let titleMsg  = 'Hinweis';
           let outputMsg = '';
           let width     = 700;
           let isMobile  = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

           if (isMobile) {
               // Finger-Gesten erklären
               outputMsg += "<u>Zoom:</u> Zwei-Finger auseinanderziehen<br>";
               outputMsg += "<u>Scrollen:</u> Mit einem Finger verschieben<br>";
           } else {
               // Maus/Tastatur erklären
               outputMsg += "<u>Zoom:</u> Mausrad oder Bereich mit linker Maustaste markieren<br>";
               outputMsg += "<u>Scrollen:</u> STRG + linke Maustaste gedrückt halten<br>";
           }

           dialog(outputMsg, titleMsg, width)
       });

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

           // Mapping Sensor → Einheit
           const sensorUnits = {
               "temp": "°C",
               "tout": "°C",
               "hum": "%",          // <<< angepasst
               "qfe": "hPa",
               "qnh": "hPa",
               "altAsl": "m",
               "bme280": "°C",
               "bme680": "°C",
               "mcu811": "°C",
               "lsp33": "°C",
               "oneWire": "°C",
               "gas": "ppm",
               "eCo2": "ppm",
               "ina226vBus": "V",
               "ina226vShunt": "mV",
               "ina226vCurrent": "A",
               "ina226vPower": "W"
           };

           // ermittelt die Einheit für den aktuellen Sensor
           const unit = sensorUnits[sensor] || "";

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
                       pointRadius: 1,
                       pointBackgroundColor: 'red',
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
                               //label: ctx => ctx.parsed.y.toFixed(2) + ' °C'
                               label: ctx => ctx.parsed.y.toFixed(2) + (unit ? ` ${unit}` : '')
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
                                   enabled: true, //Muss für selektiertes Zoom aktiv sein
                                   backgroundColor: 'rgba(0, 123, 255, 0.3)', // <<< Farbe anpassen
                                   borderColor: 'blue',                       // <<< Randfarbe
                                   borderWidth: 1                             // <<< Randstärke
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
                               //display: true, text: 'Messwert (°C)'
                               display: true, text: unit ? `Messwert (${unit})` : 'Messwert'
                           }
                       }
                   }
               }
           });
       }

       ///////////// Dialog Section

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