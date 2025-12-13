<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>FullSize Node-Map OpenStreet</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="css/loader.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="css/normal_mode.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="css/mheard.css?' . microtime() . '">';

#<!-- Leaflet CSS -->
echo '<link rel="stylesheet" href="jquery/leaflet/leaflet.css" />';

#<!-- Leaflet JS -->
echo'<script src="jquery/leaflet/leaflet.js"></script>';
echo'<script src="jquery/leaflet/plugin/custom_control/leaflet.control.custom.js"></script>';

echo '<link rel="icon" type="image/png" sizes="16x16" href="favicon.png">';
echo '</head>';
echo '<body>';


require_once 'include/func_php_mheard.php';
require_once 'include/func_js_mheard.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$debugFlag               = false;
$callSign                = trim(getParamData('callSign'));
$resGetOwnPosition       = getOwnPosition($callSign); // Für Init OpenStreet View
$openStreetTileServerUrl = trim(getParamData('openStreetTileServerUrl')) ?? 'tile.openstreetmap.org';
$openStreetTileServerUrl = $openStreetTileServerUrl == '' ? 'tile.openstreetmap.org' : $openStreetTileServerUrl;

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

echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" id="ownCallSign" value="' . $callSign . '" />';
echo '<input type="hidden" id="openStreetTileServerUrl" value="' . $openStreetTileServerUrl . '" />';

echo '<div id="map" style="width:100vw; height:100vh;"></div>';

// Defaultwerte: Heute und 7 Tage zurück
$dateFrom = $_POST['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
$dateTo   = $_POST['date_to'] ?? date('Y-m-d');

echo '<script>';
echo "
$(function () {
    dialogOpenStreet(
        '<div id=\"map\"></div>',
        'Nodes in OpenStreetMap',
        window.innerWidth,
        '$dateFrom',
        '$dateTo',
        'fullscreen'
    );
});
";

echo '</script>';

echo '</body>';
echo '</html>';

