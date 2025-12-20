<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;

echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';

echo '<head><title data-i18n="submenu.send_queue.lbl.title">Send-Queue</title>';

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

echo '<link rel="stylesheet" href="../css/core.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/config_send_queue.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_config_send_queue.php';
require_once '../include/func_php_config_send_queue.php';
require_once '../include/func_js_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';

if ($sendData === '1')
{
    $resSaveSendQueueSettings = saveSendQueueSettings();

    if ($resSaveSendQueueSettings)
    {
        echo '<span class="successHint">'.date('H:i:s') .
            '-<span data-i18n="submenu.send_queue.msg.save-settings-success">Settings wurden erfolgreich abgespeichert!</span></span>';
    }
    else
    {
        echo '<span class="failureHint">' . date('H:i:s') .
            '-<span data-i18n="submenu.send_queue.msg.save-settings-failed">Es gab einen Fehler beim Abspeichern der Settings!</span></span>';
    }
}

$sendQueueInterval = getParamData('sendQueueInterval');
$sendQueueInterval = $sendQueueInterval == '' ? 20 : $sendQueueInterval;

$sendQueueEnabled        = getParamData('sendQueueMode');
$sendQueueEnabled        = $sendQueueEnabled == '' ? 0 : $sendQueueEnabled;
$sendQueueEnabledChecked = $sendQueueEnabled == 1 ? 'checked' : '';

$resCheckCronLoopBgTask = checkBgTask('cron') == '' ? getStatusIcon('inactive') : getStatusIcon('active');

echo '<h2><span data-i18n="submenu.send_queue.lbl.title">Sende-Queue</span></h2>';

echo '<form id="frmSendQueue" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<table>';

echo '<tr>';
echo '<td><span data-i18n="submenu.send_queue.lbl.send-intervall">Sendeintervall in Sek.</span> >= 5:</td>';
echo '<td><input type="text" name="sendQueueInterval" size="2" id="sendQueueInterval" value="' . $sendQueueInterval . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.send_queue.lbl.queue-enabled">Send-Queue enabled</span>:</td>';

echo '<td>';
echo '<label class="switch">';
echo '<input type="checkbox" name="sendQueueMode" ' . $sendQueueEnabledChecked . ' id="sendQueueMode" value="1" />';
echo '<span class="slider"></span>';
echo '</label>';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><span data-i18n="submenu.send_queue.lbl.send-cron-status">Send-Cron Status</span>:</td>';

echo '<td>';
echo $resCheckCronLoopBgTask;
echo '</td>';

echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
    echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';

echo '<td colspan="2">
        <button type="button" class="btnSaveConfigSendQueue" id="btnSaveSendQueue">
            <span data-i18n="submenu.send_queue.btn.save-settings">Settings speichern</span>
        </button>
      </td>';


echo '</tr>';

echo '</table>';
echo '</form>';

echo '<script>
            $.getJSON("../translation.php?lang=' . $userLang . '", function(dict) {
            applyTranslation(dict); // siehe JS oben
            });
        </script>';


echo '</body>';
echo '</html>';