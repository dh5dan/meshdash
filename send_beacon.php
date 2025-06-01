<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';
require_once 'include/func_php_config_beacon.php';

$beaconGroup      = getBeaconData('beaconGroup') ?? 999;
$beaconMsg        = getBeaconData('beaconMsg') ?? 'Bakensendung';
$beaconCount      = getBeaconData('beaconCount') ?? 0;
$beaconStopCount  = getBeaconData('beaconStopCount') ?? 100;
$beaconEnabled    = getBeaconData('beaconEnabled') ?? 0;
$beaconInterval   = getBeaconData('beaconInterval') ?? 10;
$txQueueData      = array();
$beaconInitSendTs = '0000-00-00 00:00:00';
$beaconLastSendTs = '0000-00-00 00:00:00';
$limitHours       = 8; // Maximal 8h Bakentest

echo "<br>beaconCount VOR:$beaconCount";

if ($beaconCount < $beaconStopCount && $beaconEnabled == 1)
{
    if ($beaconCount == 0)
    {
        setBeaconData('beaconInitSendTs', date('Y-m-d H:i:s'), 'txt');
    }

    ++$beaconCount;

    $txQueueData['txType'] = 'msg';
    $txQueueData['txDst']  = $beaconGroup;
    $txQueueData['txMsg']  = '[CNT:' . $beaconCount . '/' . $beaconStopCount . '][INT:'.$beaconInterval.'] ' . $beaconMsg;
    setTxQueue($txQueueData);

    setBeaconData('beaconLastSendTs', date('Y-m-d H:i:s'), 'txt');
    setBeaconData('beaconCount', $beaconCount);

    $beaconInitSendTs = getBeaconData('beaconInitSendTs') ?? '0000-00-00 00:00:00';
    $beaconLastSendTs = getBeaconData('beaconLastSendTs') ?? '0000-00-00 00:00:00';
    $resIsPassed      = hasBeaconTimePassed($beaconInitSendTs, $beaconLastSendTs, $limitHours);

    #Wenn Zeitlimit erreicht dann sofort beenden
    if ($resIsPassed === true)
    {
        echo "<br>Max. 8h erreicht schalte ab.";
        setBeaconData('beaconEnabled', 0);

        if (chkOsIsWindows() === false)
        {
            setBeaconCronInterval($beaconInterval, 0);
        }

        exit();
    }
}
else
{
    echo "<br>Max count $beaconCount/$beaconStopCount erreicht schalte ab.";
    setBeaconData('beaconEnabled', 0);

    if (chkOsIsWindows() === false)
    {
        setBeaconCronInterval($beaconInterval, 0);
    }
}

echo "<br>beaconInitSendTs:$beaconInitSendTs";
echo "<br>beaconLastSendTs:$beaconLastSendTs";
echo "<br>beaconGroup:$beaconGroup";
echo "<br>beaconCount NACH:$beaconCount";
echo "<br>beaconStopCount:$beaconStopCount";
echo "<br>beaconEnabled:$beaconEnabled";
echo "<br>beaconInterval:$beaconInterval";
echo "<br>beaconGroup:$beaconGroup";

echo "<pre>";
print_r($txQueueData);
echo "</pre>";