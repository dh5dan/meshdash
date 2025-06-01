<script>
    $(function ($) {

        $("#btnSaveGrpDefinition").on("click", function ()
        {
            let groupNumber1      = $("#groupNumber1").val();
            let groupNumber2      = $("#groupNumber2").val();
            let groupNumber3      = $("#groupNumber3").val();
            let groupNumber4      = $("#groupNumber4").val();
            let groupNumber5      = $("#groupNumber5").val();
            let groupNumber9      = $("#groupNumber9").val();

            let groupNumber1Enabled = $("#groupNumber1Enabled").is(":checked");
            let groupNumber2Enabled = $("#groupNumber2Enabled").is(":checked");
            let groupNumber3Enabled = $("#groupNumber3Enabled").is(":checked");
            let groupNumber4Enabled = $("#groupNumber4Enabled").is(":checked");
            let groupNumber5Enabled = $("#groupNumber5Enabled").is(":checked");
            let groupNumber9Enabled = $("#groupNumber9Enabled").is(":checked");

            let msgExportGroup    = $("#msgExportGroup").val().trim();
            let msgExportEnable   = $("#msgExportEnable").is(":checked");

            let titleMsg    = 'Hinweis';
            let outputMsg   = 'Jetzt alle Settings speichern?';
            let width       = 500;
            let sendData    = 1;
            let pattern     = /^(?:[1-9][0-9]{0,4})$/;
            let numberPattern   = /^\d+$/;
            let callSignPattern = /^[A-Z0-9]{1,2}[0-9][A-Z0-9]{1,4}$/i

            if (!pattern.test(groupNumber1) && groupNumber1Enabled === true)
            {
                outputMsg = 'Der Wert bei Gruppe1 ist keine Ganze Zahl oder'
                outputMsg += '<br>liegt nicht im Gültigkeitsbereich 1-99999';
                dialog(outputMsg, titleMsg, width)
                return false;
            }

            if (!pattern.test(groupNumber2) && groupNumber2Enabled === true)
            {
                outputMsg = 'Der Wert bei Gruppe2 ist keine Ganze Zahl oder'
                outputMsg += '<br>liegt nicht im Gültigkeitsbereich 1-99999';
                dialog(outputMsg, titleMsg, width)
                return false;
            }

            if (!pattern.test(groupNumber3) && groupNumber3Enabled === true)
            {
                outputMsg = 'Der Wert bei Gruppe3 ist keine Ganze Zahl oder'
                outputMsg += '<br>liegt nicht im Gültigkeitsbereich 1-99999';
                dialog(outputMsg, titleMsg, width)
                return false;
            }

            if (!pattern.test(groupNumber4) && groupNumber4Enabled === true)
            {
                outputMsg = 'Der Wert bei Gruppe4 ist keine Ganze Zahl oder'
                outputMsg += '<br>liegt nicht im Gültigkeitsbereich 1-99999';
                dialog(outputMsg, titleMsg, width)
                return false;
            }

            if (!pattern.test(groupNumber5) && groupNumber5Enabled === true)
            {
                outputMsg = 'Der Wert bei Gruppe5 ist keine Ganze Zahl oder'
                outputMsg += '<br>liegt nicht im Gültigkeitsbereich 1-99999';
                dialog(outputMsg, titleMsg, width)
                return false;
            }

            if (!pattern.test(groupNumber9) && groupNumber9Enabled === true)
            {
                outputMsg = 'Der Wert bei Notfall-Gruppe ist keine Ganze Zahl oder'
                outputMsg += '<br>liegt nicht im Gültigkeitsbereich 1-99999';
                dialog(outputMsg, titleMsg, width)
                return false;
            }

            if (msgExportEnable === true && msgExportGroup === '')
            {
                width = 600;
                outputMsg = 'Der eingegebene Wert für die Export-Gruppe ist leer.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (msgExportEnable === true && msgExportGroup === '0')
            {
                width = 600;
                outputMsg = 'Der Werte 0 für eine Gruppe ist nicht zulässig.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (msgExportEnable === true && !numberPattern.test(msgExportGroup))
            {
                if (!callSignPattern.test(msgExportGroup))
                {
                    if (msgExportGroup !== '*')
                    {
                        width = 600;
                        outputMsg = 'Der Werte ist weder ein Rufzeichen noch ein * für die ALL-Gruppe.';
                        dialog(outputMsg, titleMsg, width);
                        return false;
                    }
                }
                else if (callSignPattern.test(msgExportGroup) && msgExportGroup.length > 6)
                {
                    width = 600;
                    outputMsg = 'Der Werte ist ein Rufzeichen aber es darf keine SSID angeben werden.';
                    dialog(outputMsg, titleMsg, width);
                    return false;
                }
            }

            $("#sendData").val(sendData);
            $("#frmGrpDefinition").trigger('submit');

            return false;
        });

        $("#btnGrpDefinitionReload").on("click", function ()
        {
            // Ermittelt die Base-URL dynamisch und führe reload aus.
            window.top.location.href = window.location.origin + window.location.pathname.replace(/\/[^\/]+\/[^\/]+\/?$/, '');
        });

        $("#btnUploadSoundFile").on("click", function ()
        {
            let titleMsg  = 'Hinweis';
            let width     = 750;
            let sendData  = 6;
            let fileInput = $('#uploadSoundFile');

            // Extrahiere den Dateinamen (unter Windows ggf. den Pfad trennen)
            let fileName = fileInput.val().split('\\').pop();

            // Regex: Dateiname  ".mp3 | .wav" enden (case-insensitive)
            let pattern = /^[a-zA-Z0-9_-]+\.(wav|mp3)$/i;

            let outputMsg = 'Sound-File: ' + fileName + " hochladen?";

            if (!fileInput.val())
            {
                width = 350;
                outputMsg = 'Bitte wählen Sie eine Datei aus.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (!pattern.test(fileName))
            {
                width = 650;
                outputMsg = 'Die Datei darf ausser "_-" keine Sonder oder Leerzeichen enthalten.';
                outputMsg += 'Es sind nur Dateien mit der Endung wav oder mp3 erlaubt.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            dialogConfirmUpload(outputMsg, titleMsg, width, sendData)

            return false;
        });

        $(".imageDelete").on("click", function ()
        {
            let titleMsg  = 'Hinweis';
            let outputMsg;
            let width     = 750;
            let sendData  = 3;
            let soundFile = $(this).data('delete');

            $("#deleteFileImage").val(soundFile);

            outputMsg = 'Soll die Sound_Datei: ' + soundFile + ' wirklich gelöscht werden?';

            dialogConfirmUpload(outputMsg, titleMsg, width, sendData)

            return false;
        });

        function dialogConfirmUpload(output_msg, title_msg, width, sendData) {
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
                        $("#sendDataUpload").val(sendData);
                        $("#frmUploadSoundFile").trigger('submit');
                        $("#pageLoading").show();
                        $(this).dialog('close');
                    }, 'Abbruch': function () {
                        $(this).dialog("close");
                    }
                }
            }).prev(".ui-dialog-titlebar").css("background", "red");
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
                        $("#frmGrpDefinition").trigger('submit');
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