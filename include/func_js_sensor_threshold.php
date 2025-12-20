<script>
    $(function ($) {

        $("#infoImagePoint").on("click", function ()
        {
            let titleMsg = 'Hinweis';
            let width    = 600;
            let outputMsg;

            outputMsg  = '<b>Hinweis zum Intervall-Wert</b><br><br>';
            outputMsg += 'Der Abfrage-Intervall-Wert muss:<ul>';
            outputMsg += '<li>eine ganze Zahl sein</li>';
            outputMsg += '<li>zwischen <b>1 und 1439</b> Minuten liegen</li>';
            outputMsg += '</ul>';
            outputMsg += '<b>Zulässige Beispielwerte:</b><br>';
            outputMsg += '<table class="interval-table">';
            outputMsg += '<thead><tr style="background-color: #f0f0f0;"><th>Minuten</th><th>Beschreibung</th><th>Messwerte/Tag</th></tr></thead>';
            outputMsg += '<tbody>';

            const examples = [
                {min: 5, desc: 'Sehr häufig'},
                {min: 15, desc: 'Standard für viele Sensoren'},
                {min: 30, desc: 'Alle halbe Stunde'},
                {min: 60, desc: 'Stündlich'},
                {min: 90, desc: 'Alle 1,5 Stunden'},
                {min: 120, desc: 'Alle 2 Stunden'},
                {min: 180, desc: 'Alle 3 Stunden'},
                {min: 240, desc: 'Alle 4 Stunden'},
                {min: 1440, desc: 'Alle 24 Stunden'}
            ];

            examples.forEach(item => {
                const countPerDay = Math.floor(1440 / item.min);
                outputMsg += `<tr><td>${item.min}</td><td>${item.desc}</td><td>${countPerDay}</td></tr>`;
            });

            outputMsg += '</tbody></table>';


            dialog(outputMsg, titleMsg, width);
            return false;
        });

        $("#btnSaveSensorThresholdTop, #btnSaveSensorThresholdBottom").on("click", function ()
        {
            let titleMsg                  = 'Hinweis';
            let outputMsg                 = 'Jetzt alle Settings speichern?';
            let width                     = 300;
            let sendData                  = 1;
            let regex                     = /^-?\d+(\.\d+)?$/; // Erlaubt ein Integer und Float mit Punkt
            let callSignPattern           = /^[A-Z0-9]{1,2}[0-9][A-Z0-9]{1,4}-(?:[1-9][0-9]?)$/i
            let sensorPollingIntervallMin = $("#sensorPollingIntervallMin").val();

            const basicSensors = [
                {
                    label: 'Temp',
                    enabled: '#sensorThTempEnabled',
                    min: '#sensorThTempMinValue',
                    max: '#sensorThTempMaxValue',
                    msg: '#sensorThTempAlertMsg',
                    dm:  '#sensorThTempDmGrpId'
                },
                {
                    label: 'Tout',
                    enabled: '#sensorThToutEnabled',
                    min: '#sensorThToutMinValue',
                    max: '#sensorThToutMaxValue',
                    msg: '#sensorThToutAlertMsg',
                    dm:  '#sensorThToutDmGrpId'
                }
            ];

            for (const sensor of basicSensors) {
                if (!validateBasicSensor(sensor, regex, callSignPattern, titleMsg)) {
                    return false;
                }
            }


            ///// INA226 Sensoren

            const inaSensors = [
                {
                    label: 'Ina226 vBus',
                    enabled: '#sensorThIna226vBusEnabled',
                    min: '#sensorThIna226vBusMinValue',
                    max: '#sensorThIna226vBusMaxValue',
                    msg: '#sensorThIna226vBusAlertMsg',
                    dm:  '#sensorThIna226vBusDmGrpId'
                },
                {
                    label: 'Ina226 vShunt',
                    enabled: '#sensorThIna226vShuntEnabled',
                    min: '#sensorThIna226vShuntMinValue',
                    max: '#sensorThIna226vShuntMaxValue',
                    msg: '#sensorThIna226vShuntAlertMsg',
                    dm:  '#sensorThIna226vShuntDmGrpId'
                },
                {
                    label: 'Ina226 Current',
                    enabled: '#sensorThIna226vCurrentEnabled',
                    min: '#sensorThIna226vCurrentMinValue',
                    max: '#sensorThIna226vCurrentMaxValue',
                    msg: '#sensorThIna226vCurrentAlertMsg',
                    dm:  '#sensorThIna226vCurrentDmGrpId'
                },
                {
                    label: 'Ina226 Power',
                    enabled: '#sensorThIna226vPowerEnabled',
                    min: '#sensorThIna226vPowerMinValue',
                    max: '#sensorThIna226vPowerMaxValue',
                    msg: '#sensorThIna226vPowerAlertMsg',
                    dm:  '#sensorThIna226vPowerDmGrpId'
                }
            ];

            for (const sensor of inaSensors) {

                // falls INA auf der Hardware nicht vorhanden ist
                if (!$(sensor.enabled).length) {
                    continue;
                }

                if (!validateBasicSensor(sensor, regex, callSignPattern, titleMsg)) {
                    return false;
                }
            }

            //////////////// Finale
            // Endprüfung: Ganze Zahl, Wertebereich 1–1439, muss 24h (1440 Minuten) ohne Rest teilen
            if ((!/^\d+$/.test(sensorPollingIntervallMin) || parseInt(sensorPollingIntervallMin, 10) <= 0 || parseInt(sensorPollingIntervallMin, 10) > 1439))
            {
                width      = 600;
                outputMsg  = '<b>Ungültiger Intervall-Wert!</b><br><br>';
                outputMsg += 'Der Abfrage-Intervall-Wert muss:<ul>';
                outputMsg += '<li>eine ganze Zahl sein</li>';
                outputMsg += '<li>zwischen <b>1 und 1439</b> Minuten liegen</li>';
                outputMsg += '</ul>';
                outputMsg += '<b>Zulässige Beispielwerte:</b><br>';
                outputMsg += '<table class="interval-table">';
                outputMsg += '<thead><tr style="background-color: #f0f0f0;"><th>Minuten</th><th>Beschreibung</th><th>Messwerte/Tag</th></tr></thead>';
                outputMsg += '<tbody>';

                const examples = [
                    {min: 5, desc: 'Sehr häufig'},
                    {min: 15, desc: 'Standard für viele Sensoren'},
                    {min: 30, desc: 'Alle halbe Stunde'},
                    {min: 60, desc: 'Stündlich'},
                    {min: 90, desc: 'Alle 1,5 Stunden'},
                    {min: 120, desc: 'Alle 2 Stunden'},
                    {min: 180, desc: 'Alle 3 Stunden'},
                    {min: 240, desc: 'Alle 4 Stunden'},
                    {min: 1440, desc: 'Alle 24 Stunden'}
                ];

                examples.forEach(item => {
                    const countPerDay = Math.floor(1440 / item.min);
                    outputMsg += `<tr><td>${item.min}</td><td>${item.desc}</td><td>${countPerDay}</td></tr>`;
                });

                outputMsg += '</tbody></table>';

                outputMsg += '<br>Bitte gültigen Wert auswählen.';

                dialog(outputMsg, titleMsg, width);
                return false;
            }

            const sensorEnabledSelectors = [
                '#sensorThTempEnabled',
                '#sensorThToutEnabled',

                '#sensorThIna226vBusEnabled',
                '#sensorThIna226vShuntEnabled',
                '#sensorThIna226vCurrentEnabled',
                '#sensorThIna226vPowerEnabled'
            ];

            const isAnySensorActive = isAnySensorEnabled(sensorEnabledSelectors);
            const isPollingEnabled = $('#sensorPollingEnabled').is(':checked');

            if (isAnySensorActive && !isPollingEnabled) {

                width = 500;
                outputMsg = 'Hinweis:<br><br>';
                outputMsg += 'Mindestens ein Sensorblock ist aktiviert,<br>';
                outputMsg += 'das Abfrage-Intervall ist jedoch aktuell deaktiviert.<br><br>';
                outputMsg += 'Die Sensoren werden erst ausgewertet, <br>sobald das Intervall aktiviert wird.<br>';
                outputMsg += 'Mit speichern der Settings fortfahren?';

                dialogConfirm(outputMsg, titleMsg, width, sendData);
                return false;
            }

            dialogConfirm(outputMsg, titleMsg, width, sendData);
            return false;
        });

        function isValidNumberOrEmpty(val, regex) {
            return val === '' || regex.test(val);
        }

        function isMinMaxValid(min, max) {
            return min === '' || max === '' || parseFloat(min) <= parseFloat(max);
        }

        function isValidDmGroup(val, callSignPattern) {
            return ($.isNumeric(val) && val > 0) || callSignPattern.test(val);
        }

        function validateBasicSensor(cfg, regex, callSignPattern, titleMsg) {

            const enabled = $(cfg.enabled).is(':checked');
            const min     = $(cfg.min).val().trim();
            const max     = $(cfg.max).val().trim();
            const msg     = $(cfg.msg).val().trim();
            const dm      = $(cfg.dm).val().trim();

            let width = 500;

            if (!isValidNumberOrEmpty(min, regex)) {
                dialog(`${cfg.label} Min-Wert ist keine Zahl oder nicht mit Punkt getrennt.<br><br>Bitte prüfen.`,
                    titleMsg, width);
                return false;
            }

            if (!isValidNumberOrEmpty(max, regex)) {
                dialog(`${cfg.label} Max-Wert ist keine Zahl oder nicht mit Punkt getrennt.<br><br>Bitte prüfen.`,
                    titleMsg, width);
                return false;
            }

            if (!isMinMaxValid(min, max)) {
                dialog(`Der ${cfg.label} Max-Wert ist kleiner als der ${cfg.label} Min-Wert.<br><br>Bitte prüfen.`,
                    titleMsg, width);
                return false;
            }

            if (!isValidDmGroup(dm, callSignPattern)) {
                dialog(`DM-${cfg.label} Gruppe muss eine Zahl > 0 sein<br>oder ein gültiges Call mit SID 1–99.<br><br>Bitte prüfen.`,
                    titleMsg, width);
                return false;
            }

            if (enabled && (msg === '' || min === '' || max === '')) {
                dialog(`Der aktivierte ${cfg.label}-Sensor hat unvollständige Daten.`,
                    titleMsg, width);
                return false;
            }

            return true;
        }

        function isAnySensorEnabled(selectors) {
            return selectors.some(sel => $(sel).length && $(sel).is(':checked'));
        }

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
                        $("#frmSensorThreshold").trigger('submit');
                        $("#pageLoading").show();
                        $(this).dialog('close');
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