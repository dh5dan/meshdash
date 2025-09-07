<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;
echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';
echo '<head><title data-i18n="submenu.lora_info.lbl.title">Lora-Info</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';

if ((getParamData('darkMode') ?? 0) == 1)
{
    echo '<link rel="stylesheet" href="../css/dark_mode.css?' . microtime() . '">';
}
else
{
    echo '<link rel="stylesheet" href="../css/normal_mode.css?' . microtime() . '">';
}

echo '<link rel="stylesheet" href="../css/lora_info.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../include/func_js_lora_info.php';
require_once '../include/func_php_lora_info.php';
require_once '../include/func_js_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;
$loraIp   = getParamData('loraIp');
$btnText  = $sendData == 1 ? '<span data-i18n="submenu.lora_info.lbl.load-page-new">Infoseite neu laden</span>' : '<span data-i18n="submenu.lora_info.lbl.load-page">Infoseite laden</span>';

echo '<h2><span data-i18n="submenu.lora_info.lbl.title">Lora-Infoseite</span></h2>';

echo '<form id="frmLoraInfo" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';

echo '<table class="table">';

echo '<tr>';

echo '<td colspan="3">
        <button type="button" class="btnLoadLoraInfo" id="btnLoadLoraInfo">' . $btnText . '</button>
      </td>';

echo '</tr>';

if ($sendData == 1)
{
       showLoraInfo(getLoraInfo($loraIp));
}

echo '<table>';
echo '</form>';
echo '<div id="pageLoading" class="pageLoadingSub"></div>';

echo '<script>
            $.getJSON("../translation.php?lang=' . $userLang . '", function(dict) {
            applyTranslation(dict); // siehe JS oben
            });
        </script>';

echo '</body>';
echo '</html>';