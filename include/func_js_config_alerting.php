<script>
    $(function ($) {

        $("#btnSaveConfigAlerting").on("click", function ()
        {
            let titleMsg          = 'Hinweis';
            let outputMsg         = 'Jetzt alle Settings speichern?';
            let width             = 350;
            let sendData          = 1;
            let callSignPattern   = /^[a-zA-Z0-9]{1,2}[0-9][a-zA-Z]{1,3}-([1-9][0-9]?)$/; // Mit SSID

            let callSignMap = {};
            let hasError = false;

            // 1. CallSign validieren
            $("input[name^='notifyCallSign']:enabled").each(function()
            {
                let callSign = $(this).val().trim().toUpperCase();
                let id       = $(this).attr("name").match(/\[(\d+)\]/)[1];

                if (callSign === "")
                {
                    $(this).css("border", "2px solid red");
                    width     = 650;
                    outputMsg = 'Bitte das CallSign inkl. SSID angeben bei Eintrag:' + id + ' angeben.';
                    outputMsg += '<br><br>Beispiel:<br>DB0ABC-99 wobei die SSID 1-99 sein darf.';
                    dialog(outputMsg, titleMsg, width);
                    hasError = true;
                    return false;
                }
                else if (callSignPattern.test(callSign) === false)
                {
                    width     = 650;
                    outputMsg = 'Das CallSign inkl. SSID bei Eintrag:' + id + ' hat nicht das gültige Format';
                    outputMsg += '<br> oder die SSID ist > 99 oder ist 0.';
                    outputMsg += '<br><br>Bitte Prüfen.';
                    $(this).css("border", "2px solid red");
                    dialog(outputMsg, titleMsg, width);
                    hasError = true;
                    return false;
                }

                if (callSignMap[callSign])
                {
                    if (callSignMap[callSign])
                    {
                        $(this).css("border", "2px solid red");

                        outputMsg = 'Doppeltes CallSign: ' + callSign + ' bei Eintrag:'+ id;
                        dialog(outputMsg, titleMsg, width);
                        hasError = true;
                        return false;
                    }
                }
                else
                {
                    callSignMap[callSign] = true;
                    $(this).css("border", "");
                }
            });

            if (hasError === true)
            {
                return false;
            }

            // 2. SoundFile validieren
            $("select[name^='notifySoundFile']:enabled").each(function()
            {
                let selected = $(this).val().trim();
                let id = $(this).attr("name").match(/\[(\d+)\]/)[1];

                if (selected === "")
                {
                    $(this).css("border", "2px solid red");
                    width     = 650;
                    outputMsg = 'Kein Soundfile für Eintrag:' + id + " ausgewählt.";
                    dialog(outputMsg, titleMsg, width);
                    hasError = true;
                    return false;
                }
                else
                {
                    $(this).css("border", "");
                }
            });

            if (hasError === true)
            {
                return false;
            }

            // Wenn alles ok → abspeichern
            dialogConfirm(outputMsg, titleMsg, width, sendData)

            return false;
        });

        $("#btnAddNewItem").on("click", function()
        {
            $(".notifyNewRow").each(function()
            {
                const isHidden = $(this).is(":hidden");
                if (isHidden)
                {
                    // sichtbar machen und inputs aktivieren
                    $(this).show();
                    $(this).find("input, select").prop("disabled", false);
                    $("#btnAddNewItem").val('Eintrag verbergen');
                }
                else
                {
                    // verstecken und inputs deaktivieren
                    $(this).hide();
                    $(this).find("input, select").prop("disabled", true);
                    $("#btnAddNewItem").val('Neuer Eintrag');
                }
            });
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

        $(".deleteNotifyItem").on("click", function ()
        {
            let titleMsg = 'Hinweis';
            let outputMsg;
            let width    = 750;
            let sendData = 2;
            let notifyId = $(this).data('notify_delete');
            let callSign = $("#notifyCallSign_" + notifyId).val();
            $("#deleteNotifyItemId").val(notifyId);

            outputMsg = 'Soll der Eintrag: ' + notifyId + ' mit CallSign: ' + callSign + ' wirklich gelöscht werden?';

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