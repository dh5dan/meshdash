<?php

function parseMheardNeighbours(): bool
{
    $loraIp        = getParamData('loraIp');
    $isMobile      = isMobile();
    $isMobileApple = isMobileApple();

    $actualHost = 'http';
    $url        = $actualHost . '://' . $loraIp . '/?page=neighbours';

    // HTML von der Remote-Seite holen
    $htmlContent = @file_get_contents($url);

    if ($htmlContent === false)
    {
        echo '<br><span class="failureHint">Keine Daten zu finden unter der Url: '
            . $url . '</span>';

        return false;
    }

    // DOM initialisieren
    $doc = new DOMDocument();

    libxml_use_internal_errors(true);
    $doc->loadHTML($htmlContent);
    libxml_clear_errors();

    // content_inner suchen
    $contentInner = null;

    $divs = $doc->getElementsByTagName('div');

    foreach ($divs as $div)
    {
        if ($div->getAttribute('id') === 'content_inner')
        {
            $contentInner = $div;
            break;
        }
    }

    if (!$contentInner)
    {
        echo '<br><span class="failureHint">'
            . 'Der Bereich "content_inner" wurde auf der Node-Seite nicht gefunden.'
            . '</span>';

        return false;
    }

    /*
     * Den kompletten Inhalt von #content_inner ausgeben.
     *
     * Dadurch bleiben insbesondere erhalten:
     * - Tabellenstruktur
     * - Hintergrundfarben
     * - title-Attribute
     * - D/I, G, M, #N, #X, Cov, Role
     * - vertikale Rufzeichen
     * - My relay decision
     * - zusätzliche Informationen unterhalb der Tabelle
     */

    $firstTr = true;
    foreach ($contentInner->childNodes as $child)
    {

        if ($child->nodeName === 'table')
        {
            if ($isMobile)
            {
                $child->setAttribute(
                    'style',
                    'display:inline-block;overflow-x:auto;'
                );
            }
        }

        #Alle vertikalen Calls bündig unten
        if ($child->nodeName === 'table' && $firstTr)
        {
            $trs = $child->getElementsByTagName('tr');

            if ($trs->length > 0)
            {
                $tr = $trs->item(0);

                $style = $tr->getAttribute('style');

                if ($style !== '')
                {
                    $style .= ';';
                }

                $tr->setAttribute('style', $style . 'vertical-align:bottom;');

                // Zahlen über den vertikalen Calls oben ausrichten
                $tds = $tr->getElementsByTagName('td');

                foreach ($tds as $td)
                {
                    #pple WebKit Workaround. verhindert zusammengeschobene Spalten
                    if ($isMobileApple)
                    {
                        $style = $td->getAttribute('style');

                        if ($style !== '')
                        {
                            $style .= ';';
                        }

                        $td->setAttribute('style', $style . 'min-width:2em;');
                    }

                    $spans = $td->getElementsByTagName('span');

                    if ($spans->length > 0)
                    {
                        $span = $spans->item(0);

                        if (str_contains(
                            $span->getAttribute('style'),
                            'writing-mode:vertical-rl'
                        ))
                        {
                            $number = $td->firstChild;

                            if ($number->nodeType === XML_TEXT_NODE)
                            {
                                $number->parentNode->insertBefore(
                                    $doc->createElement('span'),
                                    $number
                                );

                                ///////////////////////////////

                                $numberSpan = $number->previousSibling;

                                $td->setAttribute(
                                    'style',
                                    $td->getAttribute('style') . 'position:relative;padding-top:1.3em;'
                                );

                                $numberSpan->setAttribute(
                                    'style',
                                    'position:absolute;top:0;left:0;right:0;text-align:center;'
                                );
                                $numberSpan->appendChild($number);

                                $br = $numberSpan->nextSibling;

                                if ($br && $br->nodeName === 'br')
                                {
                                    $td->removeChild($br);
                                }
                            }
                        }
                    }
                }

                $firstTr = false;
            }
        }

        #Anpassung P-texte
        if ($child->nodeName === 'p')
        {
            #######Zeilenumbruch bei Call-Liste
            $text = $child->textContent;

            if (str_contains($text, 'Exclusive to me'))
            {
                $html = $doc->saveHTML($child);

                $html = preg_replace(
                    '/(Exclusive to me \(only I hear them directly\):)/',
                    '$1<br>',
                    $html,
                    1
                );

                echo $html;
                continue;
            }

            # Legende auf Breite anpassen
            $style = $child->getAttribute('style');

            if (str_contains($style, 'font-size:0.85em'))
            {

                if ($isMobile)
                {
                    echo '<br><div style="display:inline-block;max-width:100%;">';
                }
                else
                {
                    echo '<br><div style="display:inline-block;max-width:40%;">';
                }

                echo $doc->saveHTML($child);
                echo '</div>';

                continue;
            }
        }

        echo $doc->saveHTML($child);
    }

    return true;
}