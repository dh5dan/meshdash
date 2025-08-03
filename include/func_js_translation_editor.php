<script>
    $(function ($) {



        $('.bottomImgIcons').on('click', function () {
            $('.bottomImgIcons').removeClass('active'); // alle zurücksetzen
            $(this).addClass('active'); // das geklickte hervorheben

            let index = $(".bottomImgIcons").index(this); // Gibt 0, 1 oder 2 zurück

            // AJAX an PHP senden
            $.post("ajax_set_call_click.php", { icon_index: index }, function (response) {
                //console.log("Antwort vom Server:", response);

                // Hier ggf. das iframe triggern zum Neuladen
                // message-frame neu laden
                let groupId = $(parent.document.getElementById("message-frame").contentWindow.document).find("#group").val();
                $(parent.document).find('#message-frame').attr('src', `message.php?group=${groupId}`);
            });
        });

        function dialogConfirm(output_msg, title_msg, width) {
            width      = !width ? 300 : width;
            title_msg  = !title_msg ? '' : title_msg;
            output_msg = !output_msg ? '' : output_msg;

            $("<div></div>").html(output_msg).dialog({
                title: title_msg,
                resizable: true,
                modal: true,
                width: width,
                buttons: {
                    'OK': function () {
                        $(this).closest('form').trigger('submit');
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