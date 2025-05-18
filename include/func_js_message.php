<script>
    $(function ($) {

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

                let posOff = 'POS:<img class="statusImageBottom" src="image/punkt_red.png" width="15px" >';
                let posOn  = 'POS:<img class="statusImageBottom" src="image/punkt_green.png" width="15px" >';

                let ntsOff = 'NTS:<img class="statusImageBottom" src="image/punkt_red.png" width="15px" >';
                let ntsOn  = 'NTS:<img class="statusImageBottom" src="image/punkt_green.png" width="15px" >';

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