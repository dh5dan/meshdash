<script>
    $(function ($) {

        $("#sendCommand").on("keydown", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
            }
        });

        $("#btnSendCommand").on("click", function ()
        {
            let titleMsg          = 'Hinweis';
            let outputMsg         = '';
            let sendData          = 1;
            let width             = 400;
            let sendCommand = $("#sendCommand").val();

            if (sendCommand === '')
            {
                outputMsg = 'Bitte ein Kommando in der Befehlszeile angeben.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            $("#sendData").val(sendData);
            $("#frmSendCommand").trigger('submit');

            return false;
        });

        $(".btnPreCmd").on("click", function ()
        {

            let sendCommandData = $(this).data('cmd');
            let sendCommand     = $("#sendCommand");
            let loraIp          = $("#loraIp").val();
            let sendData        = 1;

            switch(sendCommandData)
            {
                case 'cmd1':
                    sendCommand.val('--extudpip ' +  loraIp);
                    break;
                case 'cmd2':
                    sendCommand.val('--extudpip on');
                    break;
                case 'cmd3':
                    sendCommand.val('--gateway on');
                    break;
                case 'cmd4':
                    sendCommand.val('--gateway off');
                    break;
                default:
                    sendData = 0;
            }

            $("#sendData").val(sendData);
            $("#frmSendCommand").trigger('submit');

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
                        $("#frmSendCommand").trigger('submit');
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