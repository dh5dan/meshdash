<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Mheard</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="css/loader.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="css/mheard.css?' . microtime() . '">';

#<!-- Leaflet CSS -->
echo '<link rel="stylesheet" href="jquery/leaflet/leaflet.css" />';

#<!-- Leaflet JS -->
echo'<script src="jquery/leaflet/leaflet.js"></script>';

echo '</head>';
echo '<body>';

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';
require_once 'include/func_php_mheard.php';
require_once 'include/func_js_mheard.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$debugFlag               = false;
$loraIp                  = getParamData('loraIp');
$callSign                = trim(getParamData('callSign'));
$resGetOwnPosition       = getOwnPosition($callSign); // Für Init OpenStreet View
$openStreetTileServerUrl = trim(getParamData('openStreetTileServerUrl')) ?? 'tile.openstreetmap.org';
$openStreetTileServerUrl = $openStreetTileServerUrl == '' ? 'tile.openstreetmap.org' : $openStreetTileServerUrl;
$sendData                = $_REQUEST['sendData'] ?? 0;

if ($resGetOwnPosition !== false)
{
    $longitude = $resGetOwnPosition['longitude'] == '' ? 51.5 : $resGetOwnPosition['longitude'];
    $latitude  = $resGetOwnPosition['latitude'] == '' ? 7.3 : $resGetOwnPosition['latitude'];

    echo '<input type="hidden" id="latitude" value="' . $latitude . '" />';
    echo '<input type="hidden" id="longitude" value="' . $longitude . '" />';
}
else
{
    #Fallback
    echo '<input type="hidden" id="latitude" value="51.5" />';
    echo '<input type="hidden" id="longitude" value="7.3" />';
}

echo '<h2>Lokale Mheard-Liste<span class="lineBreak">von '.$callSign .' mit Lora-IP: ' . $loraIp . '</span></h2>';

echo '<form id="frmMheard" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" id="ownCallSign" value="' . $callSign . '" />';
echo '<input type="hidden" id="openStreetTileServerUrl" value="' . $openStreetTileServerUrl . '" />';
echo '<table>';

echo '<tr>';
echo '<td colspan="2"><input type="button" class="btnGetMheard" id="btnGetMheard" value="Lokale Mheard-Liste abfragen"  /></td>';
echo '</tr>';
echo '<tr>';
echo '<td colspan="2"><input type="button" class="btnGetMheard" id="btnGetMheardOpenStreet" value="Mheard-Nodes in OpenStreet anzeigen"  /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';

if($sendData == 1)
{
    $resGetMheard = getMheard($loraIp);

    if ($resGetMheard === true)
    {
        echo '<span class="successHint">' . date('H:i:s') . '-MHeard wurden erfolgreich abgespeichert!</span>';
    }
}

showMheard($callSign);

echo '<div id="pageLoading" class="pageLoadingSub"></div>';
echo '</body>';
echo '</html>';

