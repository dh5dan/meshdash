<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<meta charset="UTF-8">';
echo '<head><title>Send QUEUE</title>';
echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '</head>';
echo '<body>';

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

$logfile      = 'log/tx_queue_data_' . date('Ymd') . '.log';
$errorLogfile = 'log/error_tx_queue_data_' . date('Ymd') . '.log';
$fileLogJson  = 'log/tx_queue_json_data_' . date('Ymd') . '.log';
$debugFlag    = true;

#file_put_contents("$basePath/$execDir/send_queue_log.txt", date('Y-m-d H:i:s') . " - SendQueue-Job ausgeführt\n", FILE_APPEND);
echo "<br>Starte Abarbeitung von TX-Send-Queue";
#Hole nächsten Datensatz aus der TX-Queue
$resGetTxQueue = getTxQueue();

if ($resGetTxQueue !== false)
{
    #Prüfe ob Logging aktiv ist
    $doLogEnable = getParamData('doLogEnable');

    $txQueueId = $resGetTxQueue['txQueueId'];
    $txType    = $resGetTxQueue['txType'];
    $txDst     = $resGetTxQueue['txDst'];
    $txMsg     = $resGetTxQueue['txMsg'];

    if ($txMsg != '')
    {
        $loraIP = getParamData('loraIp');

        #Begrenze max. Zeichenlänge
        if (strlen($txMsg) > 160)
        {
            $data = date('Y-m-d H:i:s') . ': ' . "Maximale Zeichen länge von 160 Zeichen überschritten. Abbruch!";
            file_put_contents($errorLogfile, $data, FILE_APPEND);

            if ($debugFlag === true)
            {
                echo "<br>" . $data;
            }

            exit();
        }

        #Workaround da Anführungszeichen derzeit via UDP nicht übertragen werden. Möglicher FW Bug
        $msgText           = str_replace('"', '``', $txMsg); // tausche mit Accent-Aigu
        $arraySend['type'] = $txType;
        $arraySend['dst']  = $txDst;
        $arraySend['msg']  = $msgText;

        $message = json_encode($arraySend, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))
        {
            socket_sendto($socket, $message, strlen($message), 0, $loraIP, 1799);
            socket_close($socket);

            if ($debugFlag === true)
            {
                echo "<br>Nachricht versand: $txType $txDst $msgText";
            }

            #Setzte send Flag
            updateTxQueue($txQueueId);

            if ($debugFlag === true)
            {
                echo "<br>Send Flag gesetzt für txQueueId: $txQueueId";
            }

            if ($doLogEnable == 1)
            {
                // Daten formatieren
                $dataLogJson = date('Y-m-d H:i:s') . ': ' . "$message\n";

                // Json-Daten in Datei speichern
                file_put_contents($fileLogJson, $dataLogJson, FILE_APPEND);
            }
        }
        else
        {
            $data = date('Y-m-d H:i:s') . ': ' . "Kann Socket nicht erstellen. Abbruch!";
            file_put_contents($errorLogfile, $data, FILE_APPEND);

            if ($debugFlag === true)
            {
                echo "<br>" . $data;
            }

            exit();
        }
    }
    else
    {
        $data = date('Y-m-d H:i:s') . ': ' . "Nachrichten Inhalt ist leer für txQueueId: $txQueueId";
        file_put_contents($errorLogfile, $data, FILE_APPEND);

        if ($debugFlag === true)
        {
            echo "<br>" . $data;
        }

        exit();
    }
}
else
{
    if ($debugFlag === true)
    {
        echo "<br>Keine Daten zum Versand gefunden.";
    }
}

echo '</body>';
echo '</html>';
exit();
