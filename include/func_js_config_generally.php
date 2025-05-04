<script>
    $(function ($) {

        $("#btnSaveConfigGenerally").on("click", function ()
        {
            let titleMsg          = 'Hinweis';
            let outputMsg         = 'Jetzt alle Settings speichern?';
            let width             = 300;
            let sendData          = 1;
            let loraIp            = $("#loraIp").val();
            let callSign          = $("#callSign").val();
            let maxScrollBackRows = $("#maxScrollBackRows").val();
            let retentionDays     = $("#retentionDays").val();

            let ipv4Pattern     = /^(\d{1,3}\.){3}\d{1,3}$/;
            let callSignPattern = /^[A-Z0-9]{1,2}[0-9][A-Z0-9]{1,4}-(?:[1-9][0-9]?)$/i
            let numberPattern   = /^\d+$/;
            let mDnsPatter      = /^[a-zA-Z0-9\-]+\.local$/;

            if (loraIp === '')
            {
                outputMsg = 'Bitte die Ip im IPv4/mDNS Format angeben.';
                outputMsg += '<br><br>Beispiel: 192.168.0.123 oder dl1abs-13.local';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (!ipv4Pattern.test(loraIp) && !mDnsPatter.test(loraIp)) {
                outputMsg = 'Ip/mDNS hat nicht das gültige Format oder enthält ungültige Zeichen.';
                outputMsg += '<br><br>Bitte Prüfen.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (callSign === '')
            {
                outputMsg = 'Bitte das CallSign inkl. SSID angeben.';
                outputMsg += '<br><br>Beispiel:<br>DB0ABC-99 wobei die SSID 1-99 sein darf.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (callSignPattern.test(callSign) === false) {
                width     = 600;
                outputMsg = 'Das CallSign inkl. SSID hat nicht das gültige Format';
                outputMsg += '<br> oder die SSID ist > 99 oder ist 0.';
                outputMsg += '<br><br>Bitte Prüfen.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (maxScrollBackRows === '')
            {
                width = 600;
                outputMsg = 'Bitte einen Wert im Bereich 30-200 für die ScrollBack Reihen angeben.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (!numberPattern.test(maxScrollBackRows))
            {
                width = 600;
                outputMsg = 'Der eingegebene Wert für ScrollBack ist keine Zahl.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (maxScrollBackRows < 30 || maxScrollBackRows > 200)
            {
                width     = 600;
                outputMsg = 'Der eingegebene Wert für ScrollBack ist ausserhalb des Wertebereichs.';
                outputMsg += '<br>Erlaubt ist 30-200.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (retentionDays === '')
            {
                width = 600;
                outputMsg = 'Bitte einen Wert im Bereich 1-n für die Aufbewahrungszeit angeben.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (!numberPattern.test(retentionDays))
            {
                width = 600;
                outputMsg = 'Der eingegebene Wert für die Aufbewahrungszeit ist keine Zahl.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (retentionDays === '0')
            {
                width     = 600;
                outputMsg = 'Der eingegebene Wert für die Aufbewahrungszeit darf nicht 0 sein.';
                outputMsg += '<br>Erlaubt ist 1-n.';
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
                        $("#frmConfigGenerally").trigger('submit');
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