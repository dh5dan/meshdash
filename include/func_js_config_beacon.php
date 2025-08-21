<script>
    $(function ($) {

        $("#btnSaveBakeSettings").on("click", function ()
        {
            let titleMsg        = 'Hinweis';
            let outputMsg       = 'Jetzt alle Settings speichern?';
            let width           = 300;
            let sendData        = 1;
            let beaconStopCount = $("#beaconStopCount").val().trim();
            let beaconMsg       = $("#beaconMsg").val().trim();
            let beaconOtp       = $("#beaconOtp").val().trim();
            let beaconGroup     = $("#beaconGroup").val().trim();
            let numberPattern   = /^\d+$/;
            let otpPattern      = /^[A-Za-z0-9]+$/;

            if (beaconOtp !== '' && (!otpPattern.test(beaconOtp)))
            {
                width = 750;
                outputMsg = 'Bitte einen OTP-Passwort im Bereich A-Z, a-z, 0-9 eingeben.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (beaconStopCount === '')
            {
                width = 750;
                outputMsg = 'Bitte einen Stop-Count eingeben.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (!numberPattern.test(beaconStopCount))
            {
                width = 750;
                outputMsg = 'Der eingegebene Wert für die Stop-Count ist keine Zahl.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (beaconStopCount > 100)
            {
                width = 750;
                outputMsg = 'Der eingegebene Wert für die Stop-Count darf 100 nicht überschreiten.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (beaconMsg === '')
            {
                width = 750;
                outputMsg = 'Bitte einen Bakentext eingeben.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            if (beaconGroup === '')
            {
                width = 750;
                outputMsg = 'Bitte eine Bakengruppe eingeben.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else if (!numberPattern.test(beaconGroup))
            {
                width = 750;
                outputMsg = 'Der eingegebene Wert für die Bakengruppe ist keine Zahl.';
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
                        $("#frmBake").trigger('submit');
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