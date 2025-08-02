<script>
    $(function ($) {

        $("#btnSaveDataPurgeAuto").on("click", function ()
        {
            let titleMsg        = 'Hinweis';
            let outputMsg       = 'Jetzt alle Settings speichern?';
            let width           = 300;
            let sendData        = 1;
            let daysMsgPurge    = $("#daysMsgPurge").val().trim();
            let daysSensorPurge = $("#daysSensorPurge").val().trim();
            let numberPattern   = /^\d+$/;

            if (!numberPattern.test(daysSensorPurge))
            {
                width = 600;
                outputMsg = 'Der eingegebene Wert für die Aufbewahrungszeit ist keine Zahl oder leer.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (daysSensorPurge < 2)
            {
                width     = 600;
                outputMsg = 'Der eingegebene Wert < 2 für die Aufbewahrungszeit ist nicht zulässig.';
                outputMsg += '<br>Der Mindestwert ist 2 Tage.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (!numberPattern.test(daysMsgPurge))
            {
                width = 600;
                outputMsg = 'Der eingegebene Wert für die Aufbewahrungszeit ist keine Zahl oder leer.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (daysMsgPurge < 2)
            {
                width     = 600;
                outputMsg = 'Der eingegebene Wert 0 für die Aufbewahrungszeit ist nicht zulässig.';
                outputMsg += '<br>Der Mindestwert ist 2 Tage.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

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
                        $("#frmPurgeDataAuto").trigger('submit');
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