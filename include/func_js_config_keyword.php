<script>
    $(function ($) {

        $("#btnSaveConfigKeyword").on("click", function ()
        {
            let keyword1Text      = $("#keyword1Text").val();
            let keyword1Cmd       = $("#keyword1Cmd").val();
            let keyword1ReturnMsg = $("#keyword1ReturnMsg").val();
            let keyword1Enabled   = $("#keyword1Enabled").is(":checked");

            let keyword2Text    = $("#keyword2Text").val();
            let keyword2Cmd     = $("#keyword2Cmd").val();
            let keyword2ReturnMsg = $("#keyword2ReturnMsg").val();
            let keyword2Enabled = $("#keyword2Enabled").is(":checked");

            let titleMsg    = 'Hinweis';
            let outputMsg   = 'Jetzt alle Settings speichern?';
            let width       = 700;
            let sendData    = 1;

            if (keyword1Enabled === true && (keyword1Text === '' || keyword1Cmd === ''))
            {
                width       = 500;
                outputMsg = 'KeyWord1 kann nicht aktiviert werden,';
                outputMsg += '<br> wenn Keyword1 und/oder Execute Cmd leer ist.';
                outputMsg += '<br><br>Bitte prüfen.';

                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (keyword1Enabled === true && keyword1Text !== '' && keyword1Cmd !== '' && keyword1ReturnMsg === '')
            {
                width       = 500;
                outputMsg = 'KeyWord1 hat keine Statusrückmeldung.';
                outputMsg += '<br>Sie erhalten dann keine Rückmeldung.';
                outputMsg += '<br><br>Soll die Einstellung so wirklich aktiviert werden?';

                dialogConfirm(outputMsg, titleMsg, width, sendData)
                return false;
            }

            if (keyword2Enabled === true && (keyword2Text === '' || keyword2Cmd === ''))
            {
                width       = 500;
                outputMsg = 'KeyWord1 kann nicht aktiviert werden,';
                outputMsg += '<br> wenn Keyword1 und/oder Execute Cmd leer ist.';
                outputMsg += '<br><br>Bitte prüfen.';

                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (keyword2Enabled === true && keyword2Text !== '' && keyword2Cmd !== '' && keyword2ReturnMsg === '')
            {
                width       = 500;
                outputMsg = 'KeyWord2 hat keine Statusrückmeldung.';
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