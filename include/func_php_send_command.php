<?php

function sendCommand($loraCmd, $loraIp): bool
{
    $loraCmd     = trim($loraCmd);
    $actualHost  = 'http';
    $triggerLink = $actualHost . '://' . $loraIp . '/?command=' . urlencode($loraCmd);
    $debugFlag   = false;

    #Check new GUI
    if (getParamData('isNewMeshGui') == 1)
    {
        $triggerLink = $actualHost . '://' . $loraIp . '/setparam/?manualcommand=' . rawurlencode($loraCmd);
    }

    if ($debugFlag === true)
    {
        echo "<br> Debug: sendCommand";
        echo "<br>triggerLink:$triggerLink";

      #  return true;
    }

    #Starte Trigger
    $ch = curl_init();

    #Check new GUI
    if (getParamData('isNewMeshGui') == 1)
    {
        # Set Curl Options
        curl_setopt($ch, CURLOPT_URL, $triggerLink);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // ⚠️ WICHTIG: Header setzen (Content-Type: application/json)
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json',]);

        // Kein Body senden – wir bleiben bei GET
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    }
    else
    {
        # Set Curl Options
        curl_setopt($ch, CURLOPT_URL, $triggerLink);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000); // Warte max. 5 Sek.
    }

    #Fehler abfangen
    if (curl_exec($ch) === false && $loraCmd != '--ota-update'  && $loraCmd != '--reboot')
    {
        echo '<span>Curl error: ' . curl_error($ch) . '</span>';
        echo '<br>';
        echo '<br>';
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    if ($debugFlag === true)
    {
        echo "<br> Debug: sendCommand";
        echo "<br>triggerLink:$triggerLink";

        echo "<pre>";
        print_r($ch);
        echo "</pre>";

        return true;
    }

    return true;
}

function getLocalIpAddressesLinux(): array
{
    $ips = [];

    // Alle Netzwerkinterfaces durchgehen
    $interfaces = shell_exec("ip -o -4 addr show | awk '{print $4}'");
    $interfaces = explode("\n", trim($interfaces));

    foreach ($interfaces as $interface)
    {
        if (strpos($interface, '127.0.0.1') === false)
        {
            // Netzmaske (z.B. /24) entfernen und IP zur Liste hinzufügen
            $ip = explode('/', $interface)[0];

            // Doppelte IPs vermeiden
            if (!in_array($ip, $ips))
            {
                $ips[] = $ip;
            }
        }
    }

    return $ips;
}

function getLocalIpAddressesWin(): array
{
    $ips = [];
    foreach (gethostbynamel(gethostname()) as $ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
        {
            $ips[] = $ip;
        }
    }
    return $ips;
}