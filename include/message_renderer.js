function fetchMessages() {
    const group = $('#gruppe_id').val();

    $.ajax({
        url: 'message_backend.php',
        method: 'POST',
        data: { group: group },
        dataType: 'json',
        success: function (data) {
            const msgFrame = $('#message-frame-inner');

            if (msgFrame.length) {
                msgFrame.html(data.html);
            }

            // if (data.playSound) {
            //     $('#soundElement')[0]?.play();
            // }

            if (data.scrollToBottom && !userHasScrolledUp()) {
                msgFrame.scrollTop(msgFrame[0].scrollHeight);
            }
        }
    });
}

function userHasScrolledUp() {
    const msgFrame = $('#message-frame-inner');
    return msgFrame.scrollTop() + msgFrame.innerHeight() < msgFrame[0].scrollHeight - 20;
}

setInterval(fetchMessages, 5000);
