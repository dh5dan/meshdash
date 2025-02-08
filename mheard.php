<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Einstellungen</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="css/loader.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="css/mheard.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';
require_once 'include/func_php_mheard.php';
require_once 'include/func_js_mheard.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$debugFlag = true;
$loraIp    = getParamData('loraIp');
$sendData  = $_REQUEST['sendData'] ?? 0;
// Array, um die Daten zu speichern
$heardData = [];

echo "<br><h2>Mheard Liste</h2>";

echo "<br><br>";
echo '<form id="frmMheard" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table>';

echo '<tr>';
echo '<td>MH-Liste mit Abfrage speichern:</td>';
echo '<td><input type="checkbox" name="doSaveMheard" id="doSaveMheard" value="1" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td><input type="button" id="btnGetMheard" value="Mheard abfragen"  /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';

showMheard();

if($sendData == 1 || $sendData == 2)
{
    // URL der Remote-Seite
    $url = 'http://' . $loraIp . '/mheard';

    // Holen des HTML-Inhalts von der Remote-Seite
    $htmlContent = @file_get_contents($url);

    if ($htmlContent === false)
    {
        echo "<br>Keine Daten zu finden unter der Url:" . $url;
        exit();
    }

    // Initialisieren des DOMDocuments
    $doc = new DOMDocument();
    libxml_use_internal_errors(true); // Fehler unterdrücken
    $doc->loadHTML($htmlContent);
    libxml_clear_errors();

    // Suchen nach der Tabelle mit den relevanten Daten
    $tableRows = $doc->getElementsByTagName('tr');

    foreach ($tableRows as $row)
    {
        $cols = $row->getElementsByTagName('td');

        // Wenn es mehr als 0 Zellen gibt, dann schauen wir uns die Zeile an
        if ($cols->length > 0)
        {
            // Prüfen, ob jede Zelle existiert, bevor auf sie zugegriffen wird
            $callSign = $cols->item(0) ? trim($cols->item(0)->nodeValue) : '';
            $date     = $cols->item(1) ? trim($cols->item(1)->nodeValue) : '';
            $time     = $cols->item(2) ? trim($cols->item(2)->nodeValue) : '';
            $hardware = $cols->item(3) ? trim($cols->item(3)->nodeValue) : '';
            $mod      = $cols->item(4) ? trim($cols->item(4)->nodeValue) : '';
            $rssi     = $cols->item(5) ? trim($cols->item(5)->nodeValue) : '';
            $snr      = $cols->item(6) ? trim($cols->item(6)->nodeValue) : '';
            $dist     = $cols->item(7) ? trim($cols->item(7)->nodeValue) : '';
            $pl       = $cols->item(8) ? trim($cols->item(8)->nodeValue) : '';
            $m        = $cols->item(9) ? trim($cols->item(9)->nodeValue) : '';

            // Falls die Zelle einen Button oder Link enthält, überspringen wir sie
            if (empty($callSign) || preg_match('/<button.*?>.*?<\/button>/', $cols->item(0)->C14N()))
            {
                continue; // Überspringe diese Zeile
            }

            // Speichern der extrahierten Daten
            $heardData[] = [
                'callSign' => $callSign,
                'date'     => $date,
                'time'     => $time,
                'hardware' => $hardware,
                'mod'      => $mod,
                'rssi'     => $rssi,
                'snr'      => $snr,
                'dist'     => $dist,
                'pl'       => $pl,
                'm'        => $m
            ];
        }
    }

    if (count($heardData) > 0 && $sendData == 2)
    {
        echo "<br>Save MHeard";
        setMheardData($heardData);
    }
}

if ($debugFlag === true)
{
    // Ausgabe der extrahierten Daten
    echo '<pre>';
    print_r($heardData);
    echo '</pre>';
}

echo '</body>';
echo '</html>';

