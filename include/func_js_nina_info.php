<script>
    $(function ($) {

        $("#ninaArsRegion").autocomplete({
            source   : '../ajax_autocomplete.php?type=1',
            minLength: 3,
            dataType : "json",
            html     : true, // optional (jquery.ui.autocomplete.html.js required)
            select   : function (event, ui) {
                let ajaxNinaArsIdSelValue    = ui.item.id;
                let ajaxNinaArsLabelSelValue = ui.item.rawLabel;
                let ninaArsId                = $("#ninaArsId");
                let ninaArsRegion            = $("#ninaArsRegion");

                ninaArsRegion.val(ajaxNinaArsLabelSelValue);
                ninaArsId.val(ajaxNinaArsIdSelValue);

                //Cancel Event and don't Replace Selected text to Selector
                event.preventDefault();

                $("#frmNinaInfo").submit();
            },
            open     : function (event, ui) {
                // optional (if other layers overlap the autocomplete list)
                $(".ui-autocomplete").css("z-index", 1000);
                $(".ui-autocomplete li a").addClass("autocompleteForceCustomNinaInfo");
            }
        });

        $("#btnNinaMowasType").on("click", function ()
        {
            let sendData  = 1;
            let ninaArsId = $("#ninaArsId").val().trim();
            let warningId = $("#warningId").val().trim();
            let width     = 350;
            let titleMsg = 'Hinweis!';
            let outputMsg;

            // alle ausgewählten Checkboxen holen
            let types = $("input[name='ninaMowsTypeChkBox[]']:checked")
                .map(function () {
                    return this.value;
                })
                .get();

            // NEU: mindestens eine Auswahl erzwingen
            if (types.length === 0) {
                dialog('Bitte mindestens eine Kategorie auswählen!', titleMsg, width);
                return false;
            }

            let errors = [];

            // Dashboard → braucht ARS
            if (types.includes('dashboard') && ninaArsId === '') {
                errors.push('ARS-ID fehlt!');
            }

            // Katwarn → braucht Warning-ID
            if (types.includes('warning') && warningId === '') {
                errors.push('Warning-ID fehlt!');
            }

            // Fehler vorhanden → abbrechen
            if (errors.length > 0) {
                outputMsg = errors.join("<br>");
                dialog(outputMsg, titleMsg, width)
                return false;
            }

            $("#pageLoading").show();
            $("#sendData").val(sendData);
            $("#frmNinaInfo").trigger('submit');
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
