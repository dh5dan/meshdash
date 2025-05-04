<script>
    $(function ($) {

        const $input            = $("#msgText");
        const $byteCountDisplay = $("#byteCount");
        const maxBytes          = 149;

        function getUTF8ByteSize(str) {
            return new TextEncoder().encode(str).length;
        }

        function updateByteCounter() {
            let text = $input.val();
            let byteSize = getUTF8ByteSize(text);

            while (byteSize > maxBytes) {
                text = text.slice(0, -1);
                byteSize = getUTF8ByteSize(text);
            }

            $input.val(text);
            $byteCountDisplay.text(`${byteSize} / ${maxBytes} Byte`);
        }

        // Zähle live beim Tippen
        $input.on("input", updateByteCounter);

        // jQuery: Entity einfügen an Cursorposition im Eingabefeld
        $('#entitySelect').on('change', function () {
            const entity = $(this).val();
            if (!entity) return;

            const input = $('#msgText')[0];
            const start = input.selectionStart;
            const end = input.selectionEnd;
            const text = input.value;

            // Entity an Cursorposition einfügen
            input.value = text.substring(0, start) + entity + text.substring(end);
            input.selectionStart = input.selectionEnd = start + entity.length;
            input.focus();

            // Dropdown zurücksetzen auf Platzhalter
            $(this).val('');

            // Bytezähler aktualisieren
            updateByteCounter();
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
                        $("#frmLoraInfo").trigger('submit');
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