<script>
    $(function ($) {

        const $btnScrollToTop = $('#scrollTopBtn');
        const showAfterScrollToTop = 150; // Pixel ab Scroll-Höhe Button anzeigen

        $(window).on('scroll', function()
        {
            if ($(this).scrollTop() > showAfterScrollToTop)
            {
                $btnScrollToTop.addClass('show');
            }
            else
            {
                $btnScrollToTop.removeClass('show');
            }
        });

        $btnScrollToTop.on('click', function()
        {
            $('html, body').animate({scrollTop: 0}, 400); // smooth scroll in 400ms
        });


        $(".btnPagePagination").on("click", function ()
        {
            let group           = $(this).data('group');
            let searchMsg       = $(this).data('search_msg');
            let searchSrc       = $(this).data('search_src');
            let searchDst       = $(this).data('search_dst');
            let searchTsFrom    = $(this).data('search_ts_from');
            let searchTsTo      = $(this).data('search_ts_to');
            let totalRows       = $(this).data('total_rows');
            let totalPages      = $(this).data('total_pages');
            let searchDirection = $(this).data('search_direction');
            let currentPage     = $(this).data('current_page');
            let page;

            if (searchDirection === 'back')
            {
                page = --currentPage;
            }
            else
            {
                page = ++currentPage;
            }

            location.href = `?group=${group}` +
                `&searchMsg=${searchMsg}` +
                `&searchSrc=${searchSrc}` +
                `&searchDst=${searchDst}` +
                `&searchTsFrom=${searchTsFrom}` +
                `&searchTsTo=${searchTsTo}` +
                `&totalRows=${totalRows}` +
                `&totalPages=${totalPages}` +
                `&page=${page}`;

            return false;
        });

        let savedGroupId = sessionStorage.getItem('groupId');
        if (savedGroupId) {
            $('#group').val(savedGroupId);
        }

        setInterval(updatePosStatus, 1000);  // Alle 1 Sekunde Status prüfen

        function updatePosStatus() {
            let posStatusValue     = $('#posStatusValue').val();
            let noTimeSyncMsgValue = $('#noTimeSyncMsgValue').val();
            let bottomFrame        = parent.document.getElementById('bottom-frame'); // Zugriff über parent

            if (bottomFrame && bottomFrame.contentWindow) {

                let posOff = 'POS:<img class="statusImageBottom" src="image/punkt_red.png" alt="POS Red-Point" width="15px" >';
                let posOn  = 'POS:<img class="statusImageBottom" src="image/punkt_green.png" alt="POS Green-Point" width="15px" >';

                let ntsOff = 'NTS:<img class="statusImageBottom" src="image/punkt_red.png" alt="NTS Red-Point" width="15px" >';
                let ntsOn  = 'NTS:<img class="statusImageBottom" src="image/punkt_green.png" alt="NTS Green-Point" width="15px" >';

                let statusTextPos = posStatusValue === '1' ? posOff : posOn; // Invertierte Logik
                let statusTextTs  = noTimeSyncMsgValue === '1' ? ntsOff : ntsOn; // Invertierte Logik

                let posStatus = $(bottomFrame.contentWindow.document).find('#posStatus');

                let noTimeSync = $(bottomFrame.contentWindow.document).find('#noTimeSync');

                if (posStatus.length)
                {
                    posStatus.html(statusTextPos);
                }

                if (noTimeSync.length)
                {
                    noTimeSync.html(statusTextTs);
                }
            }
        }

        $(".callNotice").on("click", function ()
        {
            // Reload anhalten
            window.parent.isMessageReloadEnabled = false;

            let callSign  = $(this).data('callsign');
            let titleMsg  = 'Notizen zu ' + callSign;
            let width     = 750;

            dialogConfirmNotice(callSign, titleMsg, width)

            return false;
        });

        function dialogConfirmNotice(callSign, titleMsg, width) {
            width     = !width ? 300 : width;
            titleMsg  = !titleMsg ? '' : titleMsg;

            // Container für die textarea
            let $dialogDiv = $("<div></div>").html('<textarea id="callNoticeText" style="width:100%;height:200px;box-sizing:border-box;"></textarea>');

            // Vorher Daten aus SQLite laden
            $.post('call_notice_api.php', {
                callSign: callSign,
                action:'get'
            }, function(res){
                if (res.callNotice)
                {
                    $dialogDiv.find('#callNoticeText').val(res.callNotice);
                }
            }, 'json');

            $dialogDiv.dialog({
                title: titleMsg,
                resizable: true,
                modal: true,
                width: width,
                buttons: {
                    'Speichern': function () {
                        let noteText = $dialogDiv.find('#callNoticeText').val();

                        // Speichern per AJAX
                        $.post('call_notice_api.php', {
                            callSign: callSign,
                            action: 'set',
                            callNotice: noteText
                        }, function (res) {
                            console.log('Notiz gespeichert', res);
                        }, 'json');

                        window.top.isMessageReloadEnabled = true; // Enable reload
                        $(this).dialog('close');
                    },
                    'Abbruch': function () {
                        window.top.isMessageReloadEnabled = true; // Enable reload
                        $(this).dialog('close');
                    }
                },
                close: function () {
                    window.top.isMessageReloadEnabled = true;
                    $(this).dialog('destroy').remove();
                }
            }).prev(".ui-dialog-titlebar").css("background", "red");
        }

    });

    function sendToBottomFrame(callSign)
    {
        let bottomFrame = parent.document.getElementById("bottom-frame");
        if (bottomFrame)
        {
            let bottomDoc  = bottomFrame.contentDocument || bottomFrame.contentWindow.document;
            let inputField = bottomDoc.getElementById("bottomDm");
            if (inputField)
            {
                inputField.value = callSign;
            }
        }
    }

    function sendToBottomMsgFrame(callSign)
    {
        let bottomFrame = parent.document.getElementById("bottom-frame");
        if (bottomFrame)
        {
            let bottomDoc  = bottomFrame.contentDocument || bottomFrame.contentWindow.document;
            let inputField = bottomDoc.getElementById("msgText");
            if (inputField)
            {
                inputField.value = '@' + callSign + ': ';
            }
        }
    }



</script>