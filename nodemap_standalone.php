<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Node-Map OpenStreet</title>';

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

echo '<meta http-equiv="refresh" content="300">'; // Alle 300 sek = 5Min
echo '</head>';
echo '<body>';

require_once 'include/func_php_nodemap_standalone.php';
require_once 'include/func_js_nodemap_standalone.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

if (!file_exists('nodemap.ini'))
{
    echo "<br>Die Datei: <b>nodemap.ini</b> wurde nicht gefunden!";
    exit();
}

$config                  = parse_ini_file('nodemap.ini');
$debugFlag               = false;
$callSign                = $config['callSign'];
$latitude                = $config['latitude'];
$longitude               = $config['longitude'];
$openStreetTileServerUrl = $config['openStreetTileServerUrl'];
$nodeMapJsonFile         = 'nodemap.json';
$dateFrom                = $config['dateFrom'];
$dateTo                  = date('Y-m-d');

date_default_timezone_set('Europe/Berlin'); // setze korrekte Zeitzone

if (!file_exists($nodeMapJsonFile))
{
    echo "<br>Die Datei: <b>$nodeMapJsonFile</b> wurde nicht gefunden!";
    exit();
}

$nodeMapJsonFileTS =  date('d.m.Y H:i:s', filemtime($nodeMapJsonFile));

echo '<input type="hidden" id="latitude" value="' . $latitude . '" />';
echo '<input type="hidden" id="longitude" value="' . $longitude . '" />';

echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" id="ownCallSign" value="' . $callSign . '" />';
echo '<input type="hidden" id="openStreetTileServerUrl" value="' . $openStreetTileServerUrl . '" />';

echo '<div id="map" style="width:100vw; height:100vh;"></div>';

echo '<script>';
echo "
$(function () {
    dialogOpenStreet(
        '<div id=\"map\"></div>',
        'Nodes in OpenStreetMap',
        window.innerWidth,
        '$dateFrom',
        '$dateTo',
        '$nodeMapJsonFileTS',
        'fullscreen'
    );
});
";

echo '</script>';

echo '</body>';
echo '</html>';

