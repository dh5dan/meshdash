<script>
    $(function ($) {

        $("#btnSaveConfigAlerting").on("click", function ()
        {
            let titleMsg          = 'Hinweis';
            let outputMsg         = 'Jetzt alle Settings speichern?';
            let width             = 300;
            let sendData          = 1;

            let callSignPattern   = /^[a-zA-Z]{2}[0-9]{1}[a-zA-Z]{1,3}-([1-9][0-9]?)$/; // Mit SSID
            //let callSignPattern = /^[a-zA-Z]{2}[0-9]{1}[a-zA-Z]{1,3}(-([1-9][0-9]?))?$/; // Prüft mit und ohne SSID

            let alertSoundFileSrc = $("#alertSoundFileSrc").val();
            let alertSoundCallSrc = $("#alertSoundCallSrc").val();
            let alertEnabledSrc   = $("#alertEnabledSrc").is(":checked");

            let alertSoundFileDst = $("#alertSoundFileDst").val();
            let alertSoundCallDst = $("#alertSoundCallDst").val();
            let alertEnabledDst   = $("#alertEnabledDst").is(":checked");

            // Pattern für den Dateinamen ohne Sonderzeichen
            let namePattern = /^[a-zA-Z0-9_-]+$/;

            // Pattern für die Dateiendung (mp3 oder wav)
            let extensionPattern = /\.(mp3|wav)$/i;

            if (alertEnabledSrc === true)
            {
                width = 700;

                // Trennen des Dateinamens von der Erweiterung
                let fileNameSrc = alertSoundFileSrc.split('.')[0];  // Vor dem Punkt

                if (alertSoundFileSrc === '')
                {   width = 700;
                    outputMsg = 'Bitte das Src-Soundfile ohne Leerzeichen oder Sonderzeichen im Namen angeben.';
                    outputMsg += '<br>Es sind derzeit (wav, mp3) erlaubt.';
                    dialog(outputMsg, titleMsg, width);
                    return false;
                }
                else if (!(namePattern.test(fileNameSrc) && extensionPattern.test(alertSoundFileSrc)))
                {
                    outputMsg = 'Bitte das Src-Soundfile ohne Umlaute, Sonder-/Leerzeichen angeben.';
                    outputMsg += '<br>Erlaubt sind derzeit nur wav oder mp3 Dateien.';
                    outputMsg += '<br><br>Beispiel: DB0ABC-99_ping.mp3';
                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (alertSoundCallSrc === '')
                {
                    outputMsg = 'Bitte das Src-CallSign inkl. SSID angeben.';
                    outputMsg += '<br><br>Beispiel:<br>DB0ABC-99 wobei die SSID 1-99 sein darf.';
                    dialog(outputMsg, titleMsg, width);
                    return false;
                }
                else if (callSignPattern.test(alertSoundCallSrc) === false) {
                    width     = 600;
                    outputMsg = 'Das Src-CallSign inkl. SSID hat nicht das gültige Format';
                    outputMsg += '<br> oder die SSID ist > 99 oder ist 0.';
                    outputMsg += '<br><br>Bitte Prüfen.';
                    dialog(outputMsg, titleMsg, width);
                    return false;
                }
            }

            if (alertEnabledDst === true)
            {
                width = 700;

                // Trennen des Dateinamens von der Erweiterung
                let fileNameDst = alertSoundFileDst.split('.')[0];  // Vor dem Punkt

                if (alertSoundFileDst === '')
                {
                    width = 700;
                    outputMsg = 'Bitte das Dst-Soundfile ohne Leerzeichen oder Sonderzeichen im Namen (wav, mp3) angeben.';
                    dialog(outputMsg, titleMsg, width);
                    return false;
                }
                else if (!(namePattern.test(fileNameDst) && extensionPattern.test(alertSoundFileDst)))
                {
                    outputMsg = 'Bitte das Dst-Soundfile ohne Umlaute, Sonder-/Leerzeichen angeben.';
                    outputMsg += '<br>Erlaubt sind derzeit nur wav oder mp3 Dateien.';
                    outputMsg += '<br><br>Beispiel:<br>DB0ABC-99_ping.mp3.';
                    dialog(outputMsg, titleMsg, width);
                    return false;
                }

                if (alertSoundCallDst === '')
                {
                    outputMsg = 'Bitte das Dst-CallSign inkl. SSID angeben.';
                    outputMsg += '<br><br>Beispiel:<br>DB0ABC-99 wobei die SSID 1-99 sein darf.';
                    dialog(outputMsg, titleMsg, width);
                    return false;
                }
                else if (callSignPattern.test(alertSoundCallDst) === false) {
                    width     = 600;
                    outputMsg = 'Das Dst-CallSign inkl. SSID hat nicht das gültige Format';
                    outputMsg += '<br> oder die SSID ist > 99 oder ist 0.';
                    outputMsg += '<br><br>Bitte Prüfen.';
                    dialog(outputMsg, titleMsg, width);
                    return false;
                }
            }

            width = 300;
            dialogConfirm(outputMsg, titleMsg, width, sendData)

            return false;
        });

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
                        $("#frmConfigAlerting").trigger('submit');
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

    function reloadBottomFrame()
    {
        parent.document.getElementById("bottom-frame").contentWindow.location.reload();
    }

</script>