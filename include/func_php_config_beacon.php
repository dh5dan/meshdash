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
function selectBeaconIntervall($beaconInterval): void
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
