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

            let titleMsg    = 'Hinweis';
            let outputMsg   = 'Jetzt alle Settings speichern?';
            let width       = 500;
            let sendData    = 1;
            let pattern     = /^(?:[1-9][0-9]{0,4})$/;

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

            width = 400;

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