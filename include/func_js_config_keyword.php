<script>
    $(function ($) {

        $("#btnSaveConfigKeyword").on("click", function ()
        {
            let titleMsg            = 'Hinweis';
            let outputMsg           = 'Jetzt alle Settings speichern?';
            let width               = 350;
            let sendData            = 1;
            let keyHookTriggerMap   = {};
            let keyHookExecuteMap   = {};
            let keyHookReturnMsgMap = {};
            let hasError            = false;

            // validieren
            $("input[name^='keyHookEnabled']:checked").each(function()
            {
                let id               = $(this).attr("name").match(/\[(\d+)\]/)[1];
                let keyHookExecute   = $("#keyHookExecute_" + id);
                let keyHookTrigger   = $("#keyHookTrigger_" + id);
                let keyHookReturnMsg = $("#keyHookReturnMsg_" + id);
                let keyHookDmGrpId   = $("#keyHookDmGrpId_" + id);
                let numberPattern    = /^\d+$/;

                // Wenn irgendeines der Felder disabled ist, skippen:
                if (keyHookTrigger.is(':disabled') || keyHookReturnMsg.is(':disabled'))
                {
                    return true; // continue zur nächsten Iteration
                }

                if (keyHookTrigger.val() === "")
                {
                    width       = 500;
                    outputMsg = 'KeyWord Eintrag: ' + id + ' kann nicht aktiviert werden,';
                    outputMsg += '<br> wenn Keyword  leer ist.';
                    outputMsg += '<br><br>Bitte prüfen.';
                    keyHookTrigger.css("border", "2px solid red");

                    dialog(outputMsg, titleMsg, width);
                    hasError = true;
                    return false;
                }
                else if (/\s/.test(keyHookTrigger.val()))
                {
                    width = 500;
                    outputMsg = 'KeyWord Eintrag: ' + id + ' enthält Leerzeichen.<br>';
                    outputMsg += 'Bitte nur Zeichen ohne Leerzeichen verwenden,';
                    outputMsg += '<br>z.B. "led_off" statt "led off".';
                    outputMsg += '<br><br>Bitte prüfen.';
                    keyHookTrigger.css("border", "2px solid red");

                    dialog(outputMsg, titleMsg, width);
                    hasError = true;
                    return false;
                }
                else if (keyHookExecute.val() === "")
                {
                    width     = 500;
                    outputMsg = 'KeyWord Eintrag: ' + id + ' kann nicht aktiviert werden,';
                    outputMsg += '<br> wenn Startskript leer ist.';
                    outputMsg += '<br><br>Bitte prüfen.';
                    keyHookExecute.css("border", "2px solid red");

                    dialog(outputMsg, titleMsg, width);
                    hasError = true;
                    return false;
                }
                else if (keyHookReturnMsg.val() === "")
                {
                    width     = 650;
                    outputMsg = 'KeyWord Eintrag: ' + id + ' hat keine Statusrückmeldung.';
                    outputMsg += '<br>Sie erhalten sonst keine Rückmeldung.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    keyHookReturnMsg.css("border", "2px solid red");

                    dialog(outputMsg, titleMsg, width);
                    hasError = true;
                    return false;
                }
                else if (keyHookDmGrpId.val() === "")
                {
                    width     = 650;
                    outputMsg = 'DM-Gruppe Eintrag: ' + id + ' ist nicht gesetzt.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    keyHookDmGrpId.css("border", "2px solid red");

                    dialog(outputMsg, titleMsg, width);
                    hasError = true;
                    return false;
                }
                else if (!numberPattern.test(keyHookDmGrpId.val()))
                {
                    width     = 650;
                    outputMsg = 'DM-Gruppe Eintrag: ' + id + ' mit Wert: '+keyHookDmGrpId.val()+' ist keine Zahl.';
                    outputMsg += '<br>Die DM-Gruppe darf nicht ALL/* oder ein CallSign sein.';
                    outputMsg += '<br><br>Bitte prüfen.';

                    keyHookDmGrpId.css("border", "2px solid red");

                    dialog(outputMsg, titleMsg, width);
                    hasError = true;
                    return false;
                }

                if (keyHookTriggerMap[keyHookTrigger.val()])
                {
                    if (keyHookTriggerMap[keyHookTrigger.val()])
                    {
                        $(this).css("border", "2px solid red");

                        outputMsg = 'Doppeltes Keyword: ' + keyHookTrigger.val() + ' bei Eintrag:'+ id;
                        dialog(outputMsg, titleMsg, width);
                        hasError = true;
                        return false;
                    }
                }
                else
                {
                    keyHookTriggerMap[keyHookTrigger.val()] = true;
                    $(this).css("border", "");
                }

                if (keyHookExecuteMap[keyHookExecute.val()])
                {
                    if (keyHookExecuteMap[keyHookExecute.val()])
                    {
                        $(this).css("border", "2px solid red");

                        outputMsg = 'Doppeltes Startskript : ' + keyHookExecute.val() + ' bei Eintrag:'+ id;
                        dialog(outputMsg, titleMsg, width);
                        hasError = true;
                        return false;
                    }
                }
                else
                {
                    keyHookExecuteMap[keyHookExecute.val()] = true;
                    $(this).css("border", "");
                }

                if (keyHookReturnMsgMap[keyHookReturnMsg.val()])
                {
                    if (keyHookReturnMsgMap[keyHookReturnMsg.val()])
                    {
                        $(this).css("border", "2px solid red");

                        outputMsg = 'Doppeltes Statusrückmeldung : ' + keyHookReturnMsg.val() + ' bei Eintrag:'+ id;
                        dialog(outputMsg, titleMsg, width);
                        hasError = true;
                        return false;
                    }
                }
                else
                {
                    keyHookReturnMsgMap[keyHookReturnMsg.val()] = true;
                    $(this).css("border", "");
                }
            });

            // Alle Trigger- und Rückmeldung-Felder visuell zurücksetzen
            $("input[name^='keyHookTrigger']").css("border", "");
            $("input[name^='keyHookReturnMsg']").css("border", "");

            // Check: kein Trigger-Wert darf in irgendeiner ReturnMsg enthalten sein
            let allReturnMsg = Object.keys(keyHookReturnMsgMap);

            $("input[name^='keyHookEnabled']:checked").each(function ()
            {
                let id = $(this).attr("name").match(/\[(\d+)\]/)[1];
                let keyHookTrigger   = $("#keyHookTrigger_" + id);

                let triggerVal = keyHookTrigger.val();

                if (triggerVal === '')
                {
                    return true;
                }

                for (let j = 0; j < allReturnMsg.length; j++)
                {
                    let msgVal = allReturnMsg[j];

                    if (msgVal.includes(triggerVal))
                    {
                        $("input[name^='keyHookReturnMsg']").each(function ()
                        {
                            if ($(this).val() === msgVal)
                            {
                                $(this).css("border", "2px solid red");
                            }
                        });

                        keyHookTrigger.css("border", "2px solid red");

                        outputMsg  = 'Keyword "<b>' + triggerVal + '</b>" ist als Teil';
                        outputMsg += '<br>in einer Rückmeldung enthalten:<br><br>"' + msgVal + '"';
                        outputMsg += '<br><br>Bitte prüfen.';

                        dialog(outputMsg, titleMsg, 500);
                        hasError = true;
                        return false;
                    }
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

        $("#btnAddNewHookItem").on("click", function()
        {
            $(".keyHookNewRow").each(function()
            {
                const isHidden = $(this).is(":hidden");
                if (isHidden)
                {
                    // sichtbar machen und inputs aktivieren
                    $(this).show();
                    $(this).find("input, select").prop("disabled", false);
                    $("#btnAddNewHookItem").val('Eintrag verbergen');
                }
                else
                {
                    // verstecken und inputs deaktivieren
                    $(this).hide();
                    $(this).find("input, select").prop("disabled", true);
                    $("#btnAddNewHookItem").val('Neuer Eintrag');
                }
            });
        });

        $(".deleteHookItem").on("click", function ()
        {
            let titleMsg       = 'Hinweis';
            let outputMsg;
            let width          = 750;
            let sendData       = 2;
            let keyHookId      = $(this).data('hook_delete');
            let keyHookTrigger = $("#keyHookTrigger_" + keyHookId).val();
            $("#deleteHookItemId").val(keyHookId);

            outputMsg = 'Soll der Eintrag: ' + keyHookId + ' mit Keyword: ' + keyHookTrigger + ' wirklich gelöscht werden?';

            dialogConfirm(outputMsg, titleMsg, width, sendData)

            return false;
        });

        $("#btnUploadScriptFile").on("click", function ()
        {
            let titleMsg  = 'Hinweis';
            let width     = 450;
            let sendData  = 6;
            let fileInput = $('#uploadScriptFile');

            // Extrahiere den Dateinamen (unter Windows ggf. den Pfad trennen)
            let fileName = fileInput.val().split('\\').pop();

            // Regex: Dateiname  ".mp3 | .wav" enden (case-insensitive)
            let pattern = /^[a-zA-Z0-9_-]+\.(cmd|bat|exe|sh)$/i;

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
                outputMsg += 'Es sind nur Dateien mit der Endung cmd, bat, exe oder sh erlaubt.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            dialogConfirmUpload(outputMsg, titleMsg, width, sendData)

            return false;
        });

        $(".imageDelete").on("click", function ()
        {
            let titleMsg   = 'Hinweis';
            let outputMsg;
            let width      = 750;
            let sendData   = 3;
            let scriptFile = $(this).data('delete');

            $("#deleteFileImage").val(scriptFile);

            outputMsg = 'Soll die Skript: ' + scriptFile + ' wirklich gelöscht werden?';

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
                        $("#frmUploadScriptFile").trigger('submit');
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
                        $("#frmConfigKeyword").trigger('submit');
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