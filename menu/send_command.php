<?php
require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;
echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';
echo '<head><title data-i18n="submenu.send_command.lbl.title">Befehl an Lora senden</title>';

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

echo '<link rel="stylesheet" href="../css/send_command.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../include/func_js_send_command.php';
require_once '../include/func_php_send_command.php';
require_once '../include/func_js_core.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData    = $_REQUEST['sendData'] ?? 0;
$sendCommand = $_REQUEST['sendCommand'] ?? '';

$newFavoriteDesc = trim($_REQUEST['newFavoriteDesc'] ?? '');
$newFavoriteCmd  = trim($_REQUEST['newFavoriteCmd'] ?? '');

$deleteFavoriteDesc = trim($_REQUEST['deleteFavoriteDesc'] ?? '');
$deleteFavoriteCmd  = trim($_REQUEST['deleteFavoriteCmd'] ?? '');

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';
$loraIp       = getParamData('loraIp');

$ips      = $osIssWindows ? getLocalIpAddressesWin() : getLocalIpAddressesLinux();
$countIps = count($ips);

if ($sendData === '1')
{
    $resSendCommand = sendCommand($sendCommand, $loraIp);

    if ($resSendCommand)
    {
        echo '<span class="successHint">'.date('H:i:s').'-<span data-i18n="submenu.send_command.msg.save-settings-success">Settings wurden erfolgreich abgespeichert!</span></span>';
    }
    else
    {
        echo '<span class="failureHint">' . date('H:i:s') . '-<span data-i18n="submenu.send_command.msg.save-settings-failed">Es gab einen Fehler beim Abspeichern der Settings!</span></span>';
    }
}

#Favorit löschen
if ($sendData === '11')
{
    $sendCommand = '';
    echo "<br>Fav delete";
    echo "<br>deleteFavoriteDesc:$deleteFavoriteDesc";
    echo "<br>deleteFavoriteCmd:$deleteFavoriteCmd";

    $resDeleteFavorite = deleteFavorite($deleteFavoriteCmd);

    if ($resDeleteFavorite)
    {
        echo '<span class="successHint">'.date('H:i:s').'-<span data-i18n="submenu.send_command.msg.delete-favorite-success">Favorit: '. $deleteFavoriteCmd . ' wurde erfolgreich gelöscht!</span></span>';
    }
    else
    {
        echo '<span class="failureHint">' . date('H:i:s') . '-<span data-i18n="submenu.send_command.msg.delete-favorite-failed">Es gab einen Fehler beim Löschen des Favoriten: '. $deleteFavoriteCmd . '</span></span>';
    }
}

#Favorit speichern
if ($sendData === '12')
{
    $sendCommand = '';
    echo "<br>Fav Save";
    echo "<br>deleteFavoriteCmd:$deleteFavoriteCmd";
    echo "<br>newFavoriteDesc:$newFavoriteDesc";
    echo "<br>newFavoriteCmd:$newFavoriteCmd";

    $resAddFavorite = addFavorite($deleteFavoriteCmd, $newFavoriteCmd, $newFavoriteDesc);

    if ($resAddFavorite)
    {
        echo '<span class="successHint">'.date('H:i:s').'-<span data-i18n="submenu.send_command.msg.delete-favorite-success">Favorit: '. $newFavoriteDesc . ': ' . $newFavoriteCmd . ' wurde erfolgreich gespeichert!</span></span>';
    }
    else
    {
        echo '<span class="failureHint">' . date('H:i:s') . '-<span data-i18n="submenu.send_command.msg.delete-favorite-failed">Es gab einen Fehler beim Löschen des Favoriten: '. $newFavoriteDesc . ': ' . $newFavoriteCmd . '</span></span>';
    }

}

echo '<h2><span data-i18n="submenu.send_command.lbl.title">Befehl an Lora senden</span></h2>';

echo '<form id="frmSendCommand" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" id="loraIp" value="' . $loraIp . '" />';
echo '<input type="hidden" id="deleteFavoriteDesc" name="deleteFavoriteDesc" value="" />';
echo '<input type="hidden" id="deleteFavoriteCmd" name="deleteFavoriteCmd" value="" />';
echo '<table>';

echo '<tr>';
echo '<td class="tdMin"><span data-i18n="submenu.send_command.lbl.command-line">Befehlszeile</span>:</td>';
echo '<td><input type="text" name="sendCommand"  size="30" id="sendCommand" value="' . $sendCommand . '" placeholder="--extudpip on"  /></td>';
echo '</tr>';

echo '<tr>';
echo '<td>&nbsp;</td>';
echo '<td>';
echo '<img src="../image/info_blau.png" class="infoImagePoint" id="infoImagePoint" alt="info" />';
echo '<button type="button" class="btnSendCommand" id="btnSendCommand"><span data-i18n="submenu.send_command.btn.send-command">Sende Befehl</span></button>';
echo'</td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2">';
echo '<span class="failureHint"><span data-i18n="submenu.send_command.lbl.hint">Bei der erstmaligen UDP-Aktivierung,<br>muss einmalig ein Reboot ausgeführt werden!</span></span></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2">Favoritenliste:</td>';

echo '</tr>';
echo '<tr>';

echo '<td colspan="2"><select name="btnPreCmd" id="btnPreCmd">"';

echo '<option value="" data-cmddesc="-2" selected>Favorit neu hinzufügen</option>';

if ($countIps == 1)
{
    echo '<option value="--extudpip ' . $ips[0] . '" data-cmddesc="-1">Setze UDP Ziel-Ip: --extudpip ' . $ips[0] . '</option>';
}
else
{
    for ($t = 0; $t < $countIps; ++$t)
    {
        echo '<option value="--extudpip ' . $ips[$t] . '" data-cmddesc="-1">Setze UDP Ziel-Ip: --extudpip ' . $ips[$t] . '</option>';
    }
}
echo '<option value="--ota-update" data-cmddesc="-1">OTA-Update: --ota-update</option>';
echo '<option value="" disabled>--- Eigene Favoriten -----</option>';

$favoritesArray = getSendCmdFavorites();

if (is_array($favoritesArray) && count($favoritesArray) > 0)
{
    foreach ($favoritesArray as $favoriteItem)
    {
        echo '<option value="' . $favoriteItem['cmd'] . '" data-cmddesc="' . htmlspecialchars($favoriteItem['cmdDesc'], ENT_QUOTES) . '">'
            . htmlspecialchars($favoriteItem['cmdDesc'], ENT_QUOTES) . ': ' . htmlspecialchars($favoriteItem['cmd'], ENT_QUOTES)
            . '</option>';
    }
}

echo "</select></td>";
echo '</tr>';

echo '<tr>';
echo '<td colspan="2"><hr></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2">Favoriten hinzufügen, bearbeiten oder löschen';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>';
echo "Beschreibung:";
echo '</td>';
echo '<td>';
echo'<input type="text" name="newFavoriteDesc" id="newFavoriteDesc" class="tdMaxDesc" placeholder="Beschreibung" />';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td>';
echo "Befehl:";
echo '</td>';
echo '<td>';
echo '<input type="text" name="newFavoriteCmd" id="newFavoriteCmd" class="tdMaxCmd" placeholder="Befehl" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td colspan="2">';
echo '<div style="display:flex; justify-content:space-between;">';
echo '<button type="button" class="btnSendCommand" id="btnFavoriteDelete"><span data-i18n="submenu.send_command.btn.favorite-delete">Favorit löschen</span></button>';
echo '<button type="button" class="btnSendCommand" id="btnFavoriteSave"><span data-i18n="submenu.send_command.btn.favorite-save">Favorit neu hinzufügen</span></button>';
echo '</div>';
echo'</td>';
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