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
$txMsg         = $_POST['msgText'] ?? '';
$directMessage = $_POST['dm'] ?? '*';
$group         = $_POST['group'] ?? '';

$file          = 'log/user_data_' . date('Ymd') . '.log';
$fileLogJson   = 'log/user_json_data_' . date('Ymd') . '.log';
$directMessage = $directMessage == '' ? '*' : $directMessage;

#Prüfe ob Logging aktiv ist
$doLogEnable = getParamData('doLogEnable');

if ($txMsg != '')
{
    $loraIP  = getParamData('loraIp');

    #Begrenze max. Zeichenlänge
    if (strlen($txMsg) > 160)
    {
        $errMsg = htmlspecialchars(utf8_encode("Maximale Zeichen länge von 160 Zeichen überschritten. Abbruch!"));
        header("Location: bottom.php?errMsg=" . $errMsg . "&msgText=" . $txMsg . "&dm=" . $directMessage);
        exit();
    }

    #Workaround da Anführungszeichen derzeit via UDP nicht übertragen werden. Möglicher FW Bug
    $txMsg             = str_replace('"','``', $txMsg); // tausche mit Accent-Aigu
    $arraySend['type'] = 'msg';
    $arraySend['dst']  = $directMessage;
    $arraySend['msg']  = $txMsg;

    $message = json_encode($arraySend, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))
    {
        socket_sendto($socket, $message, strlen($message), 0, $loraIP, 1799);
        socket_close($socket);

        if ($doLogEnable == 1)
        {
            // Daten formatieren
            $dataLogJson = "$message\n";

            // Json-Daten in Datei speichern
            file_put_contents($fileLogJson, $dataLogJson, FILE_APPEND);
        }
    }
    else
    {
        $errMsg = "Kann Socket nicht erstellen. Abbruch!";
        header("Location: bottom.php?errMsg=" . $errMsg . "&msgText=" . $txMsg . "&dm=" . $directMessage . "&group=" . $group);
        exit();
    }
}

if ($doLogEnable == 1)
{
    // Daten formatieren
    $data = "$directMessage $txMsg\n";

    // Daten in Datei speichern
    if (!file_put_contents($file, $data, FILE_APPEND))
    {
        $errMsg = "Fehler beim Speichern der Log-Daten.";
        header("Location: bottom.php?errMsg=" . $errMsg . "&msgText=" . $txMsg . "&dm=" . $directMessage . "&group=" . $group);
    }
}

header("Location: bottom.php?dm=" . $directMessage . "&group=" . $group);

echo '</body>';
echo '</html>';
exit();
