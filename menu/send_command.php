<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Command Setting</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/send_command.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_send_command.php';
require_once '../include/func_php_send_command.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData    = $_REQUEST['sendData'] ?? 0;
$sendCommand = $_REQUEST['sendCommand'] ?? '';

#Check what oS is running
$osIssWindows = chkOsIssWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';
$loraIp       = getParamData('loraIp');

$ips = $osIssWindows ? getLocalIpAddressesWin() : getLocalIpAddressesLinux();

$countIps = count($ips);

if ($sendData === '1')
{
    $resSendCommand = sendCommand($sendCommand, $loraIp);

    if ($resSendCommand)
    {
        echo '<span class="successHint">'.date('H:i:s').'-Befehl erfolgreich gesendet!</span>';
    }
    else
    {
        echo '<span class="failureHint">'.date('H:i:s').'-Fehler beim Senden des Befehls!</span>';
    }
}

echo "<h2>Befehl an Lora senden</h2>";

echo '<form id="frmSendCommand" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden"id="loraIp" value="' . $loraIp . '" />';
echo '<table>';

echo '<tr>';
echo '<td>Befehlszeile :</td>';
echo '<td><input type="text" name="sendCommand"  size="30" id="sendCommand" value="' . $sendCommand . '" placeholder="--extudpip on"  /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td><input type="button" id="btnSendCommand" value="Sende Befehl"  /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';


if ($countIps == 1)
{
    echo '<tr>';
    echo '<td>Setzte UDP Ziel-Ip :</td>';
    echo '<td><input type="button" class="btnPreCmd" data-cmd="cmd1" value="--extudpip ' . $ips[0] . '" /></td>';
    echo '</tr>';
}
else
{
    for ($t = 0;$t < $countIps;++$t)
    {
        echo '<tr>';
        echo '<td>Setzte UDP Ziel-Ip' . $t . ' :</td>';
        echo '<td><input type="button" class="btnPreCmd" data-cmd="cmd1" value="--extudpip ' . $ips[$t] . '" /></td>';
        echo '</tr>';
    }
}

echo '<tr>';
echo '<td>Aktiviere UDP :</td>';
echo '<td><input type="button" class="btnPreCmd" data-cmd="cmd2" value="--extudpip on" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2">&nbsp;</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Gateway ON :</td>';
echo '<td><input type="button" class="btnPreCmd" data-cmd="cmd3" value="--gateway on" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>Gateway OFF :</td>';
echo '<td><input type="button" class="btnPreCmd" data-cmd="cmd4" value="--gateway off" /></td>';
echo '</tr>';

echo '</table>';
echo '</form>';

echo '</body>';
echo '</html>';