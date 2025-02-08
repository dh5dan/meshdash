<script>
    $(function ($) {

        window.setTimeout( function() {
            window.location.reload();
        }, 5000);

        setInterval(updatePosStatus, 1000);  // Alle 1 Sekunde Status prüfen

        function updatePosStatus() {
            let posStatusValue = $('#posStatusValue').val();
            let noTimeSyncMsgValue = $('#noTimeSyncMsgValue').val();

            let bottomFrame = parent.document.getElementById('bottom-frame'); // Zugriff über parent

            if (bottomFrame && bottomFrame.contentWindow) {
                let statusTextPos = posStatusValue === '1' ? 'Pos-Filter: ON' : 'Pos-Filter: OFF';
                let statusTextTs  = noTimeSyncMsgValue === '1' ? 'NoTimeSync-Filter: ON' : 'NoTimeSync: OFF';

                let posStatus = $(bottomFrame.contentWindow.document).find('#posStatus');
                let noTimeSync = $(bottomFrame.contentWindow.document).find('#noTimeSync');

                if (posStatus.length)
                {
                    posStatus.text(statusTextPos);
                }

                if (noTimeSync.length)
                {
                    noTimeSync.text(statusTextTs);
                }
            }
        }

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
                        $("#frmMessage").trigger('submit');
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