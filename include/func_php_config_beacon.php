<?php
function saveBeaconSettings(): bool
{
    $beaconEnabledBefore        = getBeaconData('beaconEnabled');
    $beaconEnabledBefore        = $beaconEnabledBefore == '' ? 0 : $beaconEnabledBefore;

    $beaconInterval = $_REQUEST['beaconInterval'] ?? 0;
    $beaconInterval = $beaconInterval == '' ? 5 : $beaconInterval;

    $beaconStopCount = $_REQUEST['beaconStopCount'] ?? 100;
    $beaconStopCount = $beaconStopCount == '' ? 100 : $beaconStopCount;

    $beaconMsg = trim($_REQUEST['beaconMsg']) ?? '';
    $beaconMsg = $beaconMsg == '' ? 'Bakensendung' : $beaconMsg;

    $beaconOtp = trim($_REQUEST['beaconOtp']) ?? '';

    $beaconGroup = $_REQUEST['beaconGroup'] ?? 9;
    $beaconGroup = $beaconGroup == '' ? 9 : $beaconGroup;

    $beaconEnabled = $_REQUEST['beaconEnabled'] ?? 0;
    $beaconEnabled = $beaconEnabled == '' ? 0 : $beaconEnabled;

    setBeaconData('beaconInterval', $beaconInterval);
    setBeaconData('beaconStopCount', $beaconStopCount);
    setBeaconData('beaconMsg', $beaconMsg, 'txt');
    setBeaconData('beaconOtp', $beaconOtp, 'txt');
    setBeaconData('beaconGroup', $beaconGroup);
    setBeaconData('beaconEnabled', $beaconEnabled);

    #Wenn aktiviert, dann Timestamp zurücksetzten und Counter auch auf 0 setzen
    if ($beaconEnabledBefore == 0 && $beaconEnabled == 1)
    {
        setBeaconData('beaconInitSendTs', '0000-00-00 00:00:00', 'txt');
        setBeaconData('beaconLastSendTs', '0000-00-00 00:00:00', 'txt');
        setBeaconData('beaconCount', 0);
    }

    if ($beaconEnabled == 0)
    {
        setBeaconData('beaconCount', 0);
    }

    setBeaconCronInterval($beaconInterval, $beaconEnabled);

    return true;
}
function selectBeaconIntervall($beaconInterval)
{
    $arrayIntervall = array(
        5,
        10,
        15,
        30,
        45,
        60,
    );

    foreach ($arrayIntervall as $intervall)
    {
        if ($intervall == $beaconInterval)
        {
            echo '<option value="' . $intervall . '" selected>' . $intervall . '</option>';
        }
        else
        {
            echo '<option value="' . $intervall . '">' . $intervall . '</option>';
        }
    }
}

function getBeaconCronEntries(array $scriptsToCheck = []): bool
{
    $debugFlag = false;

    if (count($scriptsToCheck) === 0)
    {
        echo '<br><span style="color: red; font-weight: bold;">✖ '
            . ' Kein Prüfscript übergeben!</span>';
        return false;
    }

    exec('crontab -l 2>/dev/null', $cronJobs); // Crontab auslesen

    $foundScripts = [];

    if (!empty($cronJobs))
    {
        foreach ($cronJobs as $index => $cronJob)
        {
            $match = false;

            // Prüfung, ob eines der übergebenen Scripte im Crontab-Eintrag vorkommt
            foreach ($scriptsToCheck as $script)
            {
                if (stripos($cronJob, $script) !== false)
                {
                    $foundScripts[] = $script;
                    $match = true;
                    break; // Ein Treffer reicht
                }
            }

            if ($debugFlag === true)
            {
                echo "Listed Cron: ". ($index === 0 ? 'CronJobs (www-data):' : '&nbsp;')
                    . htmlspecialchars($cronJob);
            }

            // Wenn ein passendes Script enthalten ist, markieren wir den Eintrag
            if ($match)
            {
                if ($debugFlag === true)
                {
                    echo '<br><span style="color: green; font-weight: bold;">✔ erkannt</span>';
                }

                return true;
            }
        }

        // Nachlauf: Falls ein gesuchtes Script **nicht** gefunden wurde
        foreach ($scriptsToCheck as $script)
        {
            if (!in_array($script, $foundScripts))
            {
                if ($debugFlag === true)
                {
                    echo '<br><span style="color: red; font-weight: bold;">✖ '
                        . htmlspecialchars($script) . ' nicht gefunden</span>';
                }

                return false;
            }
        }
    }
    else
    {
        if ($debugFlag === true)
        {
            echo '<tr>';
            echo '<td>CronJobs (www-data):</td>';
            echo '<td>Kein Eintrag</td>';
            echo '</tr>';
        }

        return false;
    }

    return false;
}
function hasBeaconTimePassed(string $startTs, string $endTs, float $limitHours): bool
{
    try
    {
        $start = new DateTime($startTs);
        $end   = new DateTime($endTs);

        // Differenz berechnen
        $diff = $start->diff($end);

        // Umrechnung in Dezimalstunden (inkl. Minuten)
        $hoursPassed = ($diff->days * 24) + $diff->h + ($diff->i / 60) + ($diff->s / 3600);

        return $hoursPassed >= $limitHours;
    }
    catch (Exception $e)
    {
        // Bei Fehler (z.B. ungültiger Timestamp) false zurückgeben
        return false;
    }
}
