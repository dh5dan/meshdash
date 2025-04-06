<script>
    $(function ($) {

        $("#btnSaveSendQueue").on("click", function ()
        {
            let titleMsg          = 'Hinweis';
            let outputMsg         = 'Jetzt alle Settings speichern?';
            let width             = 300;
            let sendData          = 1;
            let sendQueueInterval = $("#sendQueueInterval").val();
            let numberPattern   = /^\d+$/;

            if (sendQueueInterval === '')
            {
                width = 600;
                outputMsg = 'Bitte einen Wert im Bereich >= 20-Sekunden für den Sendeintervall angeben.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (!numberPattern.test(sendQueueInterval))
            {
                width = 600;
                outputMsg = 'Der eingegebene Wert für den Sendeintervall ist keine Zahl.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (sendQueueInterval < 20)
            {
                width     = 600;
                outputMsg = 'Der eingegebene Wert für Sendeintervall ist ausserhalb des Wertebereichs.';
                outputMsg += '<br>Erlaubt ist >=20 Sekunden.';
                dialog(outputMsg, titleMsg, width);
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
                        $("#frmSendQueue").trigger('submit');
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

    function reloadBottomFrame()
    {
        parent.document.getElementById("bottom-frame").contentWindow.location.reload();
    }

</script>