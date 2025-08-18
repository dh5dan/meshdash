<script>
    $(function ($) {



    });

    function applyTranslation(dict) {



        $('[data-i18n]').each(function () {
            const el = $(this);
            const key = el.attr('data-i18n');
            if (dict[key] && dict[key].trim() !== '')
            {
                let text = dict[key];

                // Prüfen ob Variablen definiert sind
                // Keyword Replace – hier speziell für ###REPLACE###
                const lb = el.attr('data-vars-replace');
                if (lb)
                {
                    text = text.replace(/###REPLACE###/g, lb);
                }else {
                    // Wenn leer, dann einfach den Placeholder entfernen:
                    text = text.replace(/###REPLACE###/g, '');
                }

                el.html(text);
            }
        });


        $('[placeholder]').each(function () {
            const el = $(this);
            const key = el.attr('placeholder');
            if (dict[key] && dict[key].trim() !== '')
            {
                el.attr('placeholder', dict[key]);
            }
        });

        $('[title]').each(function () {
            const el = $(this);
            const key = el.attr('title');
            if (dict[key] && dict[key].trim() !== '')
            {
                el.attr('title', dict[key]);
            }
        });
    }





</script>