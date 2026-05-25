<?php
function showNinaInfo($ninaResponse,  $params)
{
    $mowasType = $params['mowasType'] ?? '';
    $ars       = $params['ars'] ?? '';
    $katId     = $params['warningId'] ?? '';

    $debugFlag = false;

    if (empty($ninaResponse))
    {
        echo "<br>Keine Infodaten empfangen für:";
        echo "<br>Mowas-Type: $mowasType";
        echo "<br>Ars-ID:: $ars";
        if ($mowasType == 'warning' || $mowasType == 'warningsGeo')
        {
            echo "<br>Kat-MSG-ID: $katId";
        }
        echo "<br>--------------------------";
        return false;
    }

    if ($debugFlag === true)
    {
        echo "<pre>";
        print_r($ninaResponse);
        echo "</pre>";
    }

    echo '<tr>';
    echo '<th colspan="10" ><hr></th>';
    echo '</tr>';

    $html = '<table class="nina-table">';

    // Header
    $html .= '
        <thead>
            <tr>
                <th>Titel</th>
                <th>Start</th>
                <th>Ablauf</th>
                <th>Typ</th>
                <th>Schwere</th>
                <th>Quelle</th>
            </tr>
        </thead>
        <tbody>
    ';

    foreach ($ninaResponse as $row) {

        // Schutz gegen fehlende Keys (API-sicher)
        $title   = htmlspecialchars($row['title'] ?? '');
        $start   = htmlspecialchars($row['start'] ?? '');
        $expires = htmlspecialchars($row['expires'] ?? '');
        $type    = htmlspecialchars($row['type'] ?? '');
        $source  = htmlspecialchars($row['source'] ?? '');

        // Severity als Badge
        $severity      = $row['severity'] ?? '';
        $severityClass = $row['severityClass'] ?? '';
        $sevClass      = strtolower($severityClass);

        $title    = $title == '' ? 'Keine Daten' : $title;
        $severity = $type == '' ? 'Keine Daten' : $severity;

        $html .= "
            <tr>
                <td class='col-title'>{$title}</td>
                <td>{$start}</td>
                <td>{$expires}</td>
                <td>{$type}</td>
                <td><span class='badge badge-{$sevClass}'>{$severity}</span></td>
                <td>{$source}</td>
            </tr>
        ";
    }

    $html .= '</tbody></table>';

    echo  $html;
}

