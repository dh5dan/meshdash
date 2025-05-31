<script>
    $(function ($) {

        $.datepicker.setDefaults({
            showOn: "both",
            buttonImageOnly: true,
            buttonImage: "",
            buttonText: "",
            regional: "de",
            dateFormat: 'dd.mm.yy',
            monthNames: ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
            dayNamesMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ],
        });

        $("#purgeDate").datepicker();
        $('#ui-datepicker-div').draggable();

        $("#btnPurgeData").on("click", function ()
        {
            let purgeDate      = $("#purgeDate").val();
            let titleMsg    = 'Hinweis';
            let outputMsg = 'Anzahl der zu löschenden Nachrichtendaten ermitteln bis zum ' + purgeDate + '?';
            let width       = 700;
            let sendData    = 11;

            if (purgeDate === '')
            {
                outputMsg = 'Bitte ein Datum angeben, bis zu dem Daten';
                outputMsg += '<br>zur Löschung ermittelt werden sollen.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            dialogConfirm(outputMsg, titleMsg, width, sendData)

            return false;
        });

        $("#btnPurgeDataNow").on("click", function ()
        {
            let purgeDate      = $("#purgeDateNow").val();
            let titleMsg    = 'Hinweis';
            let outputMsg = 'Jetzt alle Nachrichtendaten unwiderruflich vor dem ' + purgeDate + ' l&ouml;schen?';
            let width       = 700;
            let sendData    = 13;

            dialogConfirm(outputMsg, titleMsg, width, sendData)

            return false;
        });

        $("#btnPurgeNew").on("click", function ()
        {
            $("#sendData").val(0);
            $("#frmPurgeData").trigger('submit');
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
                        $("#frmPurgeData").trigger('submit');
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