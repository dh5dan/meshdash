<script>
    $(function ($) {

        $("#btnSaveSensorThresholdTop, #btnSaveSensorThresholdBottom").on("click", function ()
        {
            let titleMsg                 = 'Hinweis';
            let outputMsg                = 'Jetzt alle Settings speichern?';
            let width                    = 700;
            let sendData                 = 1;
            let regex                    = /^-?\d+(\.\d+)?$/; // Erlaubt ein Integer und Float mit Punkt
            let sensorThTempIntervallMin = $("#sensorThTempIntervallMin").val();

            let sensorThTempEnabled  = $("#sensorThTempEnabled").is(":checked");
            let sensorThTempMinValue = $("#sensorThTempMinValue").val().trim();
            let sensorThTempMaxValue = $("#sensorThTempMaxValue").val().trim();
            let sensorThTempAlertMsg = $("#sensorThTempAlertMsg").val().trim();
            let sensorThTempDmGrpId  = $("#sensorThTempDmGrpId").val().trim();

            if (sensorThTempMinValue !== '' && !regex.test(sensorThTempMinValue)) {
                width       = 500;
                outputMsg = 'Temp Min-Wert ist keine Zahl oder nicht mit Punkt getrennt.';
                outputMsg += '<br><br>Bitte prüfen.';

                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (sensorThTempMaxValue !== '' && !regex.test(sensorThTempMaxValue)) {
                width       = 500;
                outputMsg = 'Temp Max-Wert ist keine Zahl oder nicht mit Punkt getrennt.';
                outputMsg += '<br><br>Bitte prüfen.';

                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (sensorThTempMinValue !== '' && sensorThTempMaxValue !== '' && parseFloat(sensorThTempMinValue) > parseFloat(sensorThTempMaxValue))
            {
                width       = 500;
                outputMsg = 'Der Temp Max-Wert ist kleiner als der Temp-Min Wert.';
                outputMsg += '<br><br>Bitte prüfen.';

                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (!$.isNumeric(sensorThTempDmGrpId) || sensorThTempDmGrpId <= 0) {

                width       = 500;
                outputMsg = 'DM-Temp Gruppe muss eine Zahl sein und > 0.';
                outputMsg += '<br><br>Bitte prüfen.';

                dialog(outputMsg, titleMsg, width);
                return false;
            }

            ////////                   Tout
            let sensorThToutEnabled  = $("#sensorThToutEnabled").is(":checked");
            let sensorThToutMinValue = $("#sensorThToutMinValue").val().trim();
            let sensorThToutMaxValue = $("#sensorThToutMaxValue").val().trim();
            let sensorThToutAlertMsg = $("#sensorThToutAlertMsg").val().trim();
            let sensorThToutDmGrpId  = $("#sensorThToutDmGrpId").val().trim();

            let sensorThIna226vBusEnabled     = false;
            let sensorThIna226vShuntEnabled   = false;
            let sensorThIna226vCurrentEnabled = false;
            let sensorThIna226vPowerEnabled   = false;

            let sensorThIna226vBusAlertMsg     = '';
            let sensorThIna226vShuntAlertMsg   = '';
            let sensorThIna226vCurrentAlertMsg = '';
            let sensorThIna226vPowerAlertMsg   = '';

            if (sensorThToutMinValue !== '' && !regex.test(sensorThToutMinValue)) {
                width       = 500;
                outputMsg = 'Tout Min-Wert ist keine Zahl oder nicht mit Punkt getrennt.';
                outputMsg += '<br><br>Bitte prüfen.';

                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (sensorThToutMaxValue !== '' && !regex.test(sensorThToutMaxValue)) {
                width       = 500;
                outputMsg = 'Tout Max-Wert ist keine Zahl oder nicht mit Punkt getrennt.';
                outputMsg += '<br><br>Bitte prüfen.';

                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (sensorThToutMinValue !== '' && sensorThToutMaxValue !== '' && parseFloat(sensorThToutMinValue) > parseFloat(sensorThToutMaxValue))
            {
                width       = 500;
                outputMsg = 'Der Tout Max-Wert ist kleiner als der Tout-Min Wert.';
                outputMsg += '<br><br>Bitte prüfen.';

                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (!$.isNumeric(sensorThToutDmGrpId) || sensorThToutDmGrpId <= 0) {

                width       = 500;
                outputMsg = 'DM-Tout Gruppe muss eine Zahl sein und > 0.';
                outputMsg += '<br><br>Bitte prüfen.';

                dialog(outputMsg, titleMsg, width);
                return false;
            }

            //Prüfroutine nur durchlaufen, wenn INA226 Sensor vorhanden ist
            if ($("#sensorThIna226vBusEnabled").length)
            {
                //////////////////// INA226 vBus
                let sensorThIna226vBusEnabled  = $("#sensorThIna226vBusEnabled").is(":checked");
                let sensorThIna226vBusMinValue = $("#sensorThIna226vBusMinValue").val().trim();
                let sensorThIna226vBusMaxValue = $("#sensorThIna226vBusMaxValue").val().trim();
                let sensorThIna226vBusAlertMsg = $("#sensorThIna226vBusAlertMsg").val().trim();
                let sensorThIna226vBusDmGrpId  = $("#sensorThIna226vBusDmGrpId").val().trim();

                if (sensorThIna226vBusMinValue !== '' && !regex.test(sensorThIna226vBusMinValue))
                {
                    width     = 500;
                    outputMsg = 'Ina226vBus Min-Wert ist keine Zahl oder nicht mit Punkt getrennt.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (sensorThIna226vBusMaxValue !== '' && !regex.test(sensorThIna226vBusMaxValue))
                {
                    width     = 500;
                    outputMsg = 'Ina226vBus Max-Wert ist keine Zahl oder nicht mit Punkt getrennt.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (sensorThIna226vBusAlertMsg === '' && sensorThIna226vBusEnabled === true)
                {
                    width     = 500;
                    outputMsg = 'Ina226vBus-Alertmeldung hat keine Statusrückmeldung.';
                    outputMsg += '<br>Sie erhalten dann keine Rückmeldung.';
                    outputMsg += '<br><br>Soll die Einstellung so wirklich aktiviert werden?';

                    dialogConfirm(outputMsg, titleMsg, width, sendData)
                    return false;
                }

                if (sensorThIna226vBusMinValue !== '' && sensorThIna226vBusMaxValue !== '' && parseFloat(sensorThIna226vBusMinValue) > parseFloat(sensorThIna226vBusMaxValue))
                {
                    width     = 500;
                    outputMsg = 'Der Ina226vBus Max-Wert ist kleiner als der Ina226vBus-Min Wert.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (!$.isNumeric(sensorThIna226vBusDmGrpId) || sensorThIna226vBusDmGrpId <= 0)
                {

                    width     = 500;
                    outputMsg = 'DM-Ina226vBus Gruppe muss eine Zahl sein und > 0.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                /////////////////// VSHunt
                let sensorThIna226vShuntEnabled  = $("#sensorThIna226vShuntEnabled").is(":checked");
                let sensorThIna226vShuntMinValue = $("#sensorThIna226vShuntMinValue").val().trim();
                let sensorThIna226vShuntMaxValue = $("#sensorThIna226vShuntMaxValue").val().trim();
                let sensorThIna226vShuntAlertMsg = $("#sensorThIna226vShuntAlertMsg").val().trim();
                let sensorThIna226vShuntDmGrpId  = $("#sensorThIna226vShuntDmGrpId").val().trim();

                if (sensorThIna226vShuntMinValue !== '' && !regex.test(sensorThIna226vShuntMinValue))
                {
                    width     = 500;
                    outputMsg = 'Ina226vShunt Min-Wert ist keine Zahl oder nicht mit Punkt getrennt.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (sensorThIna226vShuntMaxValue !== '' && !regex.test(sensorThIna226vShuntMaxValue))
                {
                    width     = 500;
                    outputMsg = 'Ina226vShunt Max-Wert ist keine Zahl oder nicht mit Punkt getrennt.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (sensorThIna226vShuntAlertMsg === '' && sensorThIna226vShuntEnabled === true)
                {
                    width     = 500;
                    outputMsg = 'Ina226vShunt-Alertmeldung hat keine Statusrückmeldung.';
                    outputMsg += '<br>Sie erhalten dann keine Rückmeldung.';
                    outputMsg += '<br><br>Soll die Einstellung so wirklich aktiviert werden?';

                    dialogConfirm(outputMsg, titleMsg, width, sendData)
                    return false;
                }

                if (sensorThIna226vShuntMinValue !== '' && sensorThIna226vShuntMaxValue !== '' && parseFloat(sensorThIna226vShuntMinValue) > parseFloat(sensorThIna226vShuntMaxValue))
                {
                    width     = 500;
                    outputMsg = 'Der Ina226vShunt Max-Wert ist kleiner als der Ina226vShunt-Min Wert.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (!$.isNumeric(sensorThIna226vShuntDmGrpId) || sensorThIna226vShuntDmGrpId <= 0)
                {

                    width     = 500;
                    outputMsg = 'DM-Ina226vShunt Gruppe muss eine Zahl sein und > 0.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                //// vCurrent
                let sensorThIna226vCurrentEnabled  = $("#sensorThIna226vCurrentEnabled").is(":checked");
                let sensorThIna226vCurrentMinValue = $("#sensorThIna226vCurrentMinValue").val().trim();
                let sensorThIna226vCurrentMaxValue = $("#sensorThIna226vCurrentMaxValue").val().trim();
                let sensorThIna226vCurrentAlertMsg = $("#sensorThIna226vCurrentAlertMsg").val().trim();
                let sensorThIna226vCurrentDmGrpId  = $("#sensorThIna226vCurrentDmGrpId").val().trim();

                if (sensorThIna226vCurrentMinValue !== '' && !regex.test(sensorThIna226vCurrentMinValue))
                {
                    width     = 500;
                    outputMsg = 'Ina226vCurrent Min-Wert ist keine Zahl oder nicht mit Punkt getrennt.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (sensorThIna226vCurrentMaxValue !== '' && !regex.test(sensorThIna226vCurrentMaxValue))
                {
                    width     = 500;
                    outputMsg = 'Ina226vCurrent Max-Wert ist keine Zahl oder nicht mit Punkt getrennt.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (sensorThIna226vCurrentAlertMsg === '' && sensorThIna226vCurrentEnabled === true)
                {
                    width     = 500;
                    outputMsg = 'Ina226vCurrent-Alertmeldung hat keine Statusrückmeldung.';
                    outputMsg += '<br>Sie erhalten dann keine Rückmeldung.';
                    outputMsg += '<br><br>Soll die Einstellung so wirklich aktiviert werden?';

                    dialogConfirm(outputMsg, titleMsg, width, sendData)
                    return false;
                }

                if (sensorThIna226vCurrentMinValue !== '' && sensorThIna226vCurrentMaxValue !== '' && parseFloat(sensorThIna226vCurrentMinValue) > parseFloat(sensorThIna226vCurrentMaxValue))
                {
                    width     = 500;
                    outputMsg = 'Der Ina226vCurrent Max-Wert ist kleiner als der Ina226vCurrent-Min Wert.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (!$.isNumeric(sensorThIna226vCurrentDmGrpId) || sensorThIna226vCurrentDmGrpId <= 0)
                {

                    width     = 500;
                    outputMsg = 'DM-Ina226vCurrent Gruppe muss eine Zahl sein und > 0.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                ///// vPower
                let sensorThIna226vPowerEnabled  = $("#sensorThIna226vPowerEnabled").is(":checked");
                let sensorThIna226vPowerMinValue = $("#sensorThIna226vPowerMinValue").val().trim();
                let sensorThIna226vPowerMaxValue = $("#sensorThIna226vPowerMaxValue").val().trim();
                let sensorThIna226vPowerAlertMsg = $("#sensorThIna226vPowerAlertMsg").val().trim();
                let sensorThIna226vPowerDmGrpId  = $("#sensorThIna226vPowerDmGrpId").val().trim();

                if (sensorThIna226vPowerMinValue !== '' && !regex.test(sensorThIna226vPowerMinValue))
                {
                    width     = 500;
                    outputMsg = 'Ina226vPower Min-Wert ist keine Zahl oder nicht mit Punkt getrennt.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (sensorThIna226vPowerMaxValue !== '' && !regex.test(sensorThIna226vPowerMaxValue))
                {
                    width     = 500;
                    outputMsg = 'Ina226vPower Max-Wert ist keine Zahl oder nicht mit Punkt getrennt.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (sensorThIna226vPowerAlertMsg === '' && sensorThIna226vPowerEnabled === true)
                {
                    width     = 500;
                    outputMsg = 'Ina226vPower-Alertmeldung hat keine Statusrückmeldung.';
                    outputMsg += '<br>Sie erhalten dann keine Rückmeldung.';
                    outputMsg += '<br><br>Soll die Einstellung so wirklich aktiviert werden?';

                    dialogConfirm(outputMsg, titleMsg, width, sendData)
                    return false;
                }

                if (sensorThIna226vPowerMinValue !== '' && sensorThIna226vPowerMaxValue !== '' && parseFloat(sensorThIna226vPowerMinValue) > parseFloat(sensorThIna226vPowerMaxValue))
                {
                    width     = 500;
                    outputMsg = 'Der Ina226vPower Max-Wert ist kleiner als der Ina226vPower-Min Wert.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (!$.isNumeric(sensorThIna226vPowerDmGrpId) || sensorThIna226vPowerDmGrpId <= 0)
                {

                    width     = 500;
                    outputMsg = 'DM-Ina226vPower Gruppe muss eine Zahl sein und > 0.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                // Intervall prüfen, ob die Eingabe EINE ganze Zahl >= 1 ist und ob ein Sensor aktiv ist
                if ((!/^\d+$/.test(sensorThTempIntervallMin) || parseInt(sensorThTempIntervallMin, 10) <= 0 || parseInt(sensorThTempIntervallMin, 10) > 1439) &&
                    (sensorThIna226vBusEnabled === true || sensorThIna226vShuntEnabled === true || sensorThIna226vCurrentEnabled === true || sensorThIna226vPowerEnabled === true))
                {
                    width       = 600;
                    outputMsg = 'Der Abfrage-Intervallwert muss eine ganze Zahl >= 1 - 1439 min. sein.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    dialog(outputMsg, titleMsg, width);
                    return false;
                }
            }

            //////////////// Endprüfung
            // Prüfen, ob die Eingabe EINE ganze Zahl und im Wertebereich >= 1 bis 1439 ist
            if ((!/^\d+$/.test(sensorThTempIntervallMin) || parseInt(sensorThTempIntervallMin, 10) <= 0 || parseInt(sensorThTempIntervallMin, 10) > 1439) &&
                (sensorThTempEnabled === true || sensorThTempEnabled === true))
            {
                width       = 600;
                outputMsg = 'Der Abfrage-Intervallwert muss eine ganze Zahl >= 1 - 1439 min. sein.';
                outputMsg += '<br><br>Bitte prüfen.';

                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (
                (sensorThTempAlertMsg === '' && sensorThTempEnabled === true) ||
                (sensorThToutAlertMsg === '' && sensorThToutEnabled === true) ||
                (sensorThIna226vBusAlertMsg === '' && sensorThIna226vBusEnabled === true) ||
                (sensorThIna226vShuntAlertMsg === '' && sensorThIna226vShuntEnabled === true) ||
                (sensorThIna226vCurrentAlertMsg === '' && sensorThIna226vCurrentEnabled === true) ||
                (sensorThIna226vPowerAlertMsg === '' && sensorThIna226vPowerEnabled === true)
            )
            {
                width     = 500;
                outputMsg = 'Eine der aktivierten Sensoren hat keine Statusrückmeldung.';
                outputMsg += '<br>Sie erhalten dann keine Rückmeldung.';
                outputMsg += '<br><br>Soll die Einstellung so wirklich aktiviert werden?';

                dialogConfirm(outputMsg, titleMsg, width, sendData)
                return false;
            }

            dialogConfirm(outputMsg, titleMsg, width, sendData)

            return false;
        });

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