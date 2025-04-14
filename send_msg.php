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
$directMessage = $directMessage == '' ? '*' : $directMessage;

if ($txMsg != '')
{
    $loraIP = getParamData('loraIp');

    #Begrenze max. Zeichenlänge
    if (strlen($txMsg) > 150)
    {
        $errMsg = htmlspecialchars(utf8_encode("Maximale Zeichen länge von 150 Zeichen überschritten. Abbruch!"));
        header("Location: bottom.php?errMsg=" . $errMsg . "&msgText=" . $txMsg . "&dm=" . $directMessage);
        exit();
    }

    $arraySend['txType'] = 'msg';
    $arraySend['txDst']  = trim($directMessage);
    $arraySend['txMsg']  = trim($txMsg);
    $resSetTxQueue       = setTxQueue($arraySend);

    if ($resSetTxQueue === false)
    {
        $errMsg = "Fehler beim Speichern in Send-Queue. Abbruch!";
        header("Location: bottom.php?errMsg=" . $errMsg . "&msgText=" . $txMsg . "&dm=" . $directMessage . "&group=" . $group);
        exit();
    }
}

header("Location: bottom.php?dm=" . $directMessage . "&group=" . $group);

echo '</body>';
echo '</html>';
exit();
