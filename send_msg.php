<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<meta charset="UTF-8">';
echo '<head><title>Bottom</title>';
echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '</head>';
echo '<body>';

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

// Daten aus dem Formular abrufen
$msgText       = $_POST['msgText'] ?? '';
$directMessage = $_POST['dm'] ?? '*';

$file          = 'log/userdata_' . date('Ymd') . '.log';
$directMessage = $directMessage == '' ? '*' : $directMessage;

#Check what oS is running
$osIssWindows = chkOsIssWindows();

if ($msgText != '')
{
    $utf8    = utf8_encode('äöü#ÄÖÜß');
    $addon   = '$%"§'."'";
    $pattern = "/^[0-9a-zA-Z ,()-<>" . $utf8 . $addon . "&.-_\\s\?\!]+\$/";
    $loraIP  = getParamData('loraIp');

    if (!preg_match($pattern, $msgText))
    {
        $errMsg = htmlspecialchars(utf8_encode("Ungültige Zeichen im Text gefunden. Abbruch!"));
        header("Location: bottom.php?errMsg=" . $errMsg . "&msgText=" . $msgText . "&dm=" . $directMessage);
        exit();
    }

    #Ist noch ein Bug in der aktuellen FW
    if (strlen($msgText) > 125)
    {
        $errMsg = htmlspecialchars(utf8_encode("Maximale Zeichen länge von 125 Zeichen überschritten. Abbruch!"));
        header("Location: bottom.php?errMsg=" . $errMsg . "&msgText=" . $msgText . "&dm=" . $directMessage);
        exit();
    }

    $arraySend['type'] = 'msg';
    $arraySend['dst']  = $directMessage;
    $arraySend['msg']  = $msgText;

    $message = json_encode($arraySend, JSON_UNESCAPED_UNICODE);

    if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))
    {
        socket_sendto($socket, $message, strlen($message), 0, $loraIP, 1799);
        socket_close($socket);
    }
    else
    {
        $errMsg = "Kann Socket nicht erstellen. Abbruch!";
        header("Location: bottom.php?errMsg=" . $errMsg . "&msgText=" . $msgText . "&dm=" . $directMessage);
        exit();
    }
}

// Daten formatieren
$data = "$directMessage $msgText\n";

// Daten in Datei speichern
if (file_put_contents($file, $data, FILE_APPEND))
{
    //    echo "Daten erfolgreich gespeichert!<br>";
    header("Location: bottom.php?dm=" . $directMessage);
}
else
{
    $errMsg = "Fehler beim Speichern der Log-Daten.";
    header("Location: bottom.php?errMsg=" . $errMsg . "&msgText=" . $msgText . "&dm=" . $directMessage);
}

echo '</body>';
echo '</html>';
exit();
