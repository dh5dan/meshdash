<script>
    $(function ($) {

        $("#btnSaveDebugInfo").on("click", function ()
        {
            let titleMsg          = 'Hinweis';
            let outputMsg         = 'Jetzt alle Settings speichern?';
            let width             = 300;
            let sendData          = 1;

            dialogConfirm(outputMsg, titleMsg, width, sendData)

            return false;
        });

        $(".purgeBlockRelease").on("click", function ()
        {
            let blockedProcName = $(this).data('procFlagName');
            let titleMsg          = 'Hinweis';
            let outputMsg         = 'Geblockten Prozess: '+blockedProcName+' jetzt freigeben?';
            let width             = 300;
            let sendData          = 2;

            dialogConfirmRelease(outputMsg, titleMsg, width, sendData, blockedProcName)

            return false;
        });

        $(".imageDelete").on("click", function ()
        {
            let titleMsg = 'Hinweis';
            let outputMsg;
            let width    = 750;
            let sendData = 3;
            let logFile  = $(this).data('delete');

            $("#deleteFileImage").val(logFile);

            outputMsg = 'Soll die Log-Datei: ' + logFile + ' wirklich gelöscht werden?';

            dialogConfirm(outputMsg, titleMsg, width, sendData)

            return false;
        });

        // JSON aus hidden-Feld lesen
        const jsonData            = $('#allPhpProcessesData');
        const allPhpProcessesData = JSON.parse(jsonData.val());
        const osIsWindows         = $('#osIsWindows').val();

        $('.process-indicator').on('click', function ()
        {
            const pid       = parseInt($(this).data('pid'));
            const isWindows = $('#osIsWindows').val() === '1';
            let titleMsg    = 'Prozessinfo zum Task mit PID:' + pid;
            let width       = 600;
            let outputMsg;

            //Check ob hidden da ist
            if (jsonData.length === 0 || !jsonData.val()) {
                return false;
            }

            //Keine Pid zum Suchen da, dann false
            if (!pid) {
                dialog('Keine PID gefunden', 'Hinweis', width);
                return false;
            }

            // Prozess im Array suchen
            const process = allPhpProcessesData.find(p => p.PID === pid);

            if (process)
            {
                // Script aus CommandLine extrahieren (falls ScriptName leer oder extra)
                let commandOnly = process.CommandLine;

                const idx = process.CommandLine.indexOf(process.ScriptName);

                if (idx !== -1) {
                    commandOnly = process.CommandLine.substring(0, idx).trim();
                }

                if (osIsWindows === '1')
                {
                    outputMsg = `
                                <strong>PID:</strong> ${process.PID}<br>
                                <strong>Parent PID:</strong> ${process.ParentPID}<br>
                                <strong>Script:</strong> ${process.ScriptName}<br>
                                <strong>CommandLine:</strong> ${commandOnly}<br>
                                <strong>PHP Path:</strong> ${process.PHPExePath}<br>
                                <strong>Node:</strong> ${process.Node}<br>
                                <strong>SessionId:</strong> ${process.SessionId}
                            `;
                }
                else
                {
                    let scriptOnly;
                    const parts = process.CommandLine.trim().split(/\s+/, 2);
                    if (parts.length === 2) {
                        commandOnly = parts[0];
                        scriptOnly  = parts[1];
                    }

                    outputMsg = `
                                <strong>PID:</strong> ${process.PID}<br>
                                <strong>Parent PID:</strong> ${process.ParentPID}<br>
                                <strong>Script:</strong> ${scriptOnly}<br>
                                <strong>CommandLine:</strong> ${commandOnly}<br>
                                <strong>PHP Path:</strong> ${process.PHPExePath}<br>
                            `;
                }

                dialog(outputMsg, titleMsg, width);
                return false;
            }
            else
            {
                titleMsg = 'Hinweis';
                outputMsg = 'Prozess nicht gefunden';
                dialog(outputMsg, titleMsg, width);
                return false;
            }
        });

        function dialogConfirmRelease(output_msg, title_msg, width, sendData, blockedProcName) {
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
                        $("#purgeBlockReleaseProcName").val(blockedProcName);
                        $("#frmDebugInfo").trigger('submit');
                        $("#pageLoading").show();
                        $(this).dialog('close');
                    }, 'Abbruch': function () {
                        $(this).dialog("close");
                    }
                }
            }).prev(".ui-dialog-titlebar").css("background", "red");
        }

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
                        $("#frmDebugInfo").trigger('submit');
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