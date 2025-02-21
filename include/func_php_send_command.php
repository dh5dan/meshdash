<?php

function sendCommand($loraCmd, $loraIp): bool
{
    $loraCmd = trim($loraCmd);

    $actualHost  = (empty($_SERVER['HTTPS']) ? 'http' : 'https');
    $triggerLink = $actualHost . '://' . $loraIp . '/?command=' . urlencode($loraCmd);
    $debugFlag   = false;

    #Starte Trigger
    $ch = curl_init();

    # Set Curl Options
    curl_setopt($ch, CURLOPT_URL, $triggerLink);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000); // Warte max. 5 Sek.

    #Fehler abfangen
    if (curl_exec($ch) === false)
    {
        echo '<span>Curl error: ' . curl_error($ch) . '</span>';
        #echo '<br>Curl error: ' . curl_errno($ch);
        echo '<br>';
        echo '<br>';
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    if ($debugFlag === true)
    {
        echo "<br> Debug: callWindowsBackgroundTask";
        echo "<br>triggerLink:$triggerLink";

        echo "<pre>";
        print_r($ch);
        echo "</pre>";

        return true;
    }

    return false;
}
