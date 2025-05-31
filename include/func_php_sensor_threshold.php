<?php
function saveSensorThresholdSettings($hasIna226Sensor): bool
{
    #Check what oS is running
    $osIssWindows = chkOsIsWindows();
    $returnFlag   = true;

    $sensorThIna226vBusEnabled     = 0;
    $sensorThIna226vShuntEnabled   = 0;
    $sensorThIna226vCurrentEnabled = 0;
    $sensorThIna226vPowerEnabled   = 0;

    $sensorThTempIntervallMin                    = $_REQUEST['sensorThTempIntervallMin'] ?? 1;
    $arrayThTempData['sensorThTempIntervallMin'] = $sensorThTempIntervallMin;

    $sensorThTempEnabled  = $_REQUEST['sensorThTempEnabled'] ?? 0;
    $sensorThTempMinValue = $_REQUEST['sensorThTempMinValue'] ?? '';
    $sensorThTempMaxValue = $_REQUEST['sensorThTempMaxValue'] ?? '';
    $sensorThTempAlertMsg = $_REQUEST['sensorThTempAlertMsg'] ?? '';
    $sensorThTempDmGrpId  = $_REQUEST['sensorThTempDmGrpId'] ?? '999';
    $sensorThTempDmGrpId  = $sensorThTempDmGrpId == '' ? '999' : $sensorThTempDmGrpId;

    $arrayThTempData['sensorThTempEnabled']  = $sensorThTempEnabled;
    $arrayThTempData['sensorThTempMinValue'] = $sensorThTempMinValue;
    $arrayThTempData['sensorThTempMaxValue'] = $sensorThTempMaxValue;
    $arrayThTempData['sensorThTempAlertMsg'] = $sensorThTempAlertMsg;
    $arrayThTempData['sensorThTempDmGrpId']  = $sensorThTempDmGrpId;

    $sensorThToutEnabled  = $_REQUEST['sensorThToutEnabled'] ?? 0;
    $sensorThToutMinValue = $_REQUEST['sensorThToutMinValue'] ?? '';
    $sensorThToutMaxValue = $_REQUEST['sensorThToutMaxValue'] ?? '';
    $sensorThToutAlertMsg = $_REQUEST['sensorThToutAlertMsg'] ?? '';
    $sensorThToutDmGrpId  = $_REQUEST['sensorThToutDmGrpId'] ?? '999';
    $sensorThToutDmGrpId  = $sensorThToutDmGrpId == '' ? '999' : $sensorThToutDmGrpId;

    $arrayThTempData['sensorThToutEnabled']  = $sensorThToutEnabled;
    $arrayThTempData['sensorThToutMinValue'] = $sensorThToutMinValue;
    $arrayThTempData['sensorThToutMaxValue'] = $sensorThToutMaxValue;
    $arrayThTempData['sensorThToutAlertMsg'] = $sensorThToutAlertMsg;
    $arrayThTempData['sensorThToutDmGrpId']  = $sensorThToutDmGrpId;

    $resSetThTempData = setThTempData($arrayThTempData);

    if ($resSetThTempData === false)
    {
        $returnFlag = false;
    }

    if ($osIssWindows === false)
    {
        if ($sensorThTempIntervallMin > 0 && ($sensorThTempEnabled == 1 || $sensorThToutEnabled == 1))
        {
            #echo "<br>Set Cron temp enabled auf $sensorThTempIntervallMin";
            setCronSensorInterval($sensorThTempIntervallMin,0);
        }
    }

    if ($hasIna226Sensor === true)
    {
        #Es gibt nur noch ein Intervall zur Abfrage der Daten.
        $sensorThIna226IntervallMin                      = $sensorThTempIntervallMin ?? 1;
        $arrayThIna226Data['sensorThIna226IntervallMin'] = $sensorThIna226IntervallMin;

        $sensorThIna226vBusEnabled  = $_REQUEST['sensorThIna226vBusEnabled'] ?? 0;
        $sensorThIna226vBusMinValue = $_REQUEST['sensorThIna226vBusMinValue'] ?? '';
        $sensorThIna226vBusMaxValue = $_REQUEST['sensorThIna226vBusMaxValue'] ?? '';
        $sensorThIna226vBusAlertMsg = $_REQUEST['sensorThIna226vBusAlertMsg'] ?? '';
        $sensorThIna226vBusDmGrpId  = $_REQUEST['sensorThIna226vBusDmGrpId'] ?? '999';
        $sensorThIna226vBusDmGrpId  = $sensorThIna226vBusDmGrpId == '' ? '999' : $sensorThIna226vBusDmGrpId;

        $arrayThIna226Data['sensorThIna226vBusEnabled']  = $sensorThIna226vBusEnabled;
        $arrayThIna226Data['sensorThIna226vBusMinValue'] = $sensorThIna226vBusMinValue;
        $arrayThIna226Data['sensorThIna226vBusMaxValue'] = $sensorThIna226vBusMaxValue;
        $arrayThIna226Data['sensorThIna226vBusAlertMsg'] = $sensorThIna226vBusAlertMsg;
        $arrayThIna226Data['sensorThIna226vBusDmGrpId']  = $sensorThIna226vBusDmGrpId;

        $sensorThIna226vShuntEnabled  = $_REQUEST['sensorThIna226vShuntEnabled'] ?? 0;
        $sensorThIna226vShuntMinValue = $_REQUEST['sensorThIna226vShuntMinValue'] ?? '';
        $sensorThIna226vShuntMaxValue = $_REQUEST['sensorThIna226vShuntMaxValue'] ?? '';
        $sensorThIna226vShuntAlertMsg = $_REQUEST['sensorThIna226vShuntAlertMsg'] ?? '';
        $sensorThIna226vShuntDmGrpId  = $_REQUEST['sensorThIna226vShuntDmGrpId'] ?? '999';
        $sensorThIna226vShuntDmGrpId  = $sensorThIna226vShuntDmGrpId == '' ? '999' : $sensorThIna226vShuntDmGrpId;

        $arrayThIna226Data['sensorThIna226vShuntEnabled']  = $sensorThIna226vShuntEnabled;
        $arrayThIna226Data['sensorThIna226vShuntMinValue'] = $sensorThIna226vShuntMinValue;
        $arrayThIna226Data['sensorThIna226vShuntMaxValue'] = $sensorThIna226vShuntMaxValue;
        $arrayThIna226Data['sensorThIna226vShuntAlertMsg'] = $sensorThIna226vShuntAlertMsg;
        $arrayThIna226Data['sensorThIna226vShuntDmGrpId']  = $sensorThIna226vShuntDmGrpId;

        $sensorThIna226vCurrentEnabled  = $_REQUEST['sensorThIna226vCurrentEnabled'] ?? 0;
        $sensorThIna226vCurrentMinValue = $_REQUEST['sensorThIna226vCurrentMinValue'] ?? '';
        $sensorThIna226vCurrentMaxValue = $_REQUEST['sensorThIna226vCurrentMaxValue'] ?? '';
        $sensorThIna226vCurrentAlertMsg = $_REQUEST['sensorThIna226vCurrentAlertMsg'] ?? '';
        $sensorThIna226vCurrentDmGrpId  = $_REQUEST['sensorThIna226vCurrentDmGrpId'] ?? '999';
        $sensorThIna226vCurrentDmGrpId  = $sensorThIna226vCurrentDmGrpId == '' ? '999' : $sensorThIna226vCurrentDmGrpId;

        $arrayThIna226Data['sensorThIna226vCurrentEnabled']  = $sensorThIna226vCurrentEnabled;
        $arrayThIna226Data['sensorThIna226vCurrentMinValue'] = $sensorThIna226vCurrentMinValue;
        $arrayThIna226Data['sensorThIna226vCurrentMaxValue'] = $sensorThIna226vCurrentMaxValue;
        $arrayThIna226Data['sensorThIna226vCurrentAlertMsg'] = $sensorThIna226vCurrentAlertMsg;
        $arrayThIna226Data['sensorThIna226vCurrentDmGrpId']  = $sensorThIna226vCurrentDmGrpId;

        $sensorThIna226vPowerEnabled  = $_REQUEST['sensorThIna226vPowerEnabled'] ?? 0;
        $sensorThIna226vPowerMinValue = $_REQUEST['sensorThIna226vPowerMinValue'] ?? '';
        $sensorThIna226vPowerMaxValue = $_REQUEST['sensorThIna226vPowerMaxValue'] ?? '';
        $sensorThIna226vPowerAlertMsg = $_REQUEST['sensorThIna226vPowerAlertMsg'] ?? '';
        $sensorThIna226vPowerDmGrpId  = $_REQUEST['sensorThIna226vPowerDmGrpId'] ?? '999';
        $sensorThIna226vPowerDmGrpId  = $sensorThIna226vPowerDmGrpId == '' ? '999' : $sensorThIna226vPowerDmGrpId;

        $arrayThIna226Data['sensorThIna226vPowerEnabled']  = $sensorThIna226vPowerEnabled;
        $arrayThIna226Data['sensorThIna226vPowerMinValue'] = $sensorThIna226vPowerMinValue;
        $arrayThIna226Data['sensorThIna226vPowerMaxValue'] = $sensorThIna226vPowerMaxValue;
        $arrayThIna226Data['sensorThIna226vPowerAlertMsg'] = $sensorThIna226vPowerAlertMsg;
        $arrayThIna226Data['sensorThIna226vPowerDmGrpId']  = $sensorThIna226vPowerDmGrpId;

        $resSetThIna226Data = setThIna226Data($arrayThIna226Data);

        if ($resSetThIna226Data === false)
        {
            $returnFlag = false;
        }

        if ($osIssWindows === false)
        {
            if ($sensorThIna226IntervallMin > 0 && ($sensorThIna226vBusEnabled == 1 || $sensorThIna226vShuntEnabled == 1 || $sensorThIna226vCurrentEnabled == 1 || $sensorThIna226vPowerEnabled == 1))
            {
              #  echo "<br>Set Cron INA enabled auf $sensorThIna226IntervallMin";
                setCronSensorInterval($sensorThIna226IntervallMin,0);
            }
        }
    }

    if ($osIssWindows === false)
    {
        if ($sensorThTempEnabled == 0 &&
            $sensorThToutEnabled == 0 &&
            $sensorThIna226vBusEnabled == 0 &&
            $sensorThIna226vShuntEnabled == 0 &&
            $sensorThIna226vCurrentEnabled == 0 &&
            $sensorThIna226vPowerEnabled == 0)
        {
            setCronSensorInterval($sensorThTempIntervallMin,1);
        }
    }

    return $returnFlag;
}
