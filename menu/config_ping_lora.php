<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;
echo '<head><title data-i18n="submenu.ping_lora.lbl.title">Ping Lora</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/config_ping_lora.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../include/func_js_config_ping_lora.php';
require_once '../include/func_js_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$loraIp   = getParamData('loraIp');
$sendData = $_REQUEST['sendData'] ?? 0;

if ($sendData == 1)
{
    #Check what oS is running
    if (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN')
    {
        exec('ping -n 4 -4 ' . $loraIp, $output, $return_var);

        if ($return_var == 0)
        {
            foreach ($output AS $returnValue)
            {
                echo "<br>" .  sapi_windows_cp_conv(sapi_windows_cp_get('oem'), 65001, $returnValue);
            }
        }
        else
        {
            echo "<br>Ein fehler ist bei der ausführung von Ping aufgetreten";
        }
    }
    else
    {
        exec('ping -c 4 ' . $loraIp, $output, $return_var);

        if ($return_var == 0)
        {
            foreach ($output AS $returnValue)
            {
                echo "<br>" . $returnValue;
            }
        }
        else
        {
            echo "<br>Ein fehler ist bei der ausführung von Ping aufgetreten";
        }
    }
}

echo '<h2><span data-i18n="submenu.ping_lora.lbl.title">Ping Lora IP</span> IP:' . $loraIp .'</h2>';

echo '<form id="frmPingLora" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" name="loraIp" id="loraIP" value="' . $loraIp . '" />';

echo '<td>
        <button type="button" class="submitParamLoraIp" id="btnPingLoraIp">
            <span data-i18n="submenu.ping_lora.btn.ping">Ping jetzt ausführen</span>
        </button>
      </td>';

echo '</form>';

echo '<div id="pageLoading" class="pageLoadingSub"></div>';

echo '<script>
            $.getJSON("../translation.php?lang=' . $userLang . '", function(dict) {
            applyTranslation(dict); // siehe JS oben
            });
        </script>';

echo '</body>';
echo '</html>';