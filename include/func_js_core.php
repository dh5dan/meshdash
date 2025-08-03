<script>
    $(function ($) {



    });

    function applyTranslation(dict) {
        $('[data-i18n]').each(function () {
            const el = $(this);
            const key = el.attr('data-i18n');
            if (dict[key]) {
                el.text(dict[key]);
            }
        });

        $('[placeholder]').each(function () {
            const el = $(this);
            const key = el.attr('placeholder');
            if (dict[key]) {
                el.attr('placeholder', dict[key]);
            }
        });

        $('[title]').each(function () {
            const el = $(this);
            const key = el.attr('title');
            if (dict[key]) {
                el.attr('title', dict[key]);
            }
        });
    }





</script>