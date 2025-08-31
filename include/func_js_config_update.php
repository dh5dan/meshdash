<script>
    $(function ($) {

        $("#btnConfigUpdate").on("click", function ()
        {
            let titleMsg  = 'Hinweis';
            let outputMsg = 'Es wird vor dem Update ein Backup des aktuellen Systems durchgeführt.';
                outputMsg += '<br>Dieser Vorgang dauert eventuell ein paar Sekunden – bitte kurz warten.';
                outputMsg += '<br><br>Update jetzt durchführen?';
            let width     = 750;
            let sendData  = 1;
            let fileInput = $('#updateFile');

            if (!fileInput.val())
            {
                width = 350;
                outputMsg = 'Bitte wählen Sie eine Datei aus.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            // Extrahiere den Dateinamen (unter Windows ggf. den Pfad trennen)
            let fileName = fileInput.val().split('\\').pop();

            // Regex: Dateiname muss mit "meshdash-sql" beginnen und mit ".zip" enden (case-insensitive)
            let pattern = /^meshdash-sql.*\.zip$/i;

            if (!pattern.test(fileName))
            {
                width = 650;
                outputMsg = 'Die Datei muss mit <b>meshdash-sql</b> beginnen und die Endung <b>.zip</b> haben.';
                dialog(outputMsg, titleMsg, width);
                return false;
            }

            dialogConfirm(outputMsg, titleMsg, width, sendData)

            return false;
        });

        $("#btnConfigUpdateBackup").on("click", function ()
        {
            let titleMsg  = 'Hinweis';
            let outputMsg = 'Es wird nun ein Backup des aktuellen Systems durchgeführt.';
            outputMsg += '<br><br>Backup jetzt durchführen?';
            let width     = 750;
            let sendData  = 2;

            dialogConfirm(outputMsg, titleMsg, width, sendData)

            return false;
        });

        $("#btnDwnLatestRelease").on("click", function ()
        {
            let sendData  = 4;
            $("#sendData").val(sendData);
            $("#frmConfigUpdate").trigger('submit');

            return false;
        });

        $("#btnShowChangeLog").on("click", function ()
        {
            let sendData  = 5;
            $("#sendData").val(sendData);
            $("#frmConfigUpdate").trigger('submit');

            return false;
        });

        $("#btnConfigUpdateReload").on("click", function ()
        {
            // Ermittelt die Base-URL dynamisch
            window.top.location.href = window.location.origin + window.location.pathname.replace(/\/[^\/]+\/[^\/]+\/?$/, '') + '/';
        });

        $(".imageDelete").on("click", function ()
        {
            let titleMsg  = 'Hinweis';
            let outputMsg;
            let width     = 750;
            let sendData  = 3;
            let backupFile = $(this).data('delete');

            $("#deleteFileImage").val(backupFile);

            outputMsg = 'Soll die Backupdatei: ' + backupFile + ' wirklich gelöscht werden?';

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
                        $("#frmConfigUpdate").trigger('submit');
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

    function dialogChangeLog(outputMsg, titleMsg, width) {
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

</script>