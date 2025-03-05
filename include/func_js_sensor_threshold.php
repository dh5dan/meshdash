<script>
    $(function ($) {

        $("#btnSaveSensorThreshold").on("click", function ()
        {
            let titleMsg    = 'Hinweis';
            let outputMsg   = 'Jetzt alle Settings speichern?';
            let width       = 700;
            let sendData    = 1;
            let regex = /^-?\d+(\.\d+)?$/; // Erlaubt ein Integer und Float mit Punkt

            let sensorThTempEnabled  = $("#sensorThTempEnabled").is(":checked");
            let sensorThTempMinValue = $("#sensorThTempMinValue").val();
            let sensorThTempMaxValue = $("#sensorThTempMaxValue").val();
            let sensorThTempAlertMsg = $("#sensorThTempAlertMsg").val();
            let sensorThTempDmGrpId  = $("#sensorThTempDmGrpId").val();

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

            if (sensorThTempAlertMsg === '' && sensorThTempEnabled === true)
            {
                width       = 500;
                outputMsg = 'Temp-Alertmeldung hat keine Statusrückmeldung.';
                outputMsg += '<br>Sie erhalten dann keine Rückmeldung.';
                outputMsg += '<br><br>Soll die Einstellung so wirklich aktiviert werden?';

                dialogConfirm(outputMsg, titleMsg, width, sendData)
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
            let sensorThToutMinValue = $("#sensorThToutMinValue").val();
            let sensorThToutMaxValue = $("#sensorThToutMaxValue").val();
            let sensorThToutAlertMsg = $("#sensorThToutAlertMsg").val();
            let sensorThToutDmGrpId  = $("#sensorThToutDmGrpId").val();

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

            if (sensorThToutAlertMsg === '' && sensorThToutEnabled === true)
            {
                width       = 500;
                outputMsg = 'Tout-Alertmeldung hat keine Statusrückmeldung.';
                outputMsg += '<br>Sie erhalten dann keine Rückmeldung.';
                outputMsg += '<br><br>Soll die Einstellung so wirklich aktiviert werden?';

                dialogConfirm(outputMsg, titleMsg, width, sendData)
                return false;
            }

            if (!$.isNumeric(sensorThToutDmGrpId) || sensorThToutDmGrpId <= 0) {

                width       = 500;
                outputMsg = 'DM-Tout Gruppe muss eine Zahl sein und > 0.';
                outputMsg += '<br><br>Bitte prüfen.';

                dialog(outputMsg, titleMsg, width);
                return false;
            }

            width       = 400;

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