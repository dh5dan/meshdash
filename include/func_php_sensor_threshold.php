<?php

function saveSensorThresholdSettings(): bool
{
    $returnFlag = true;

    $sensorThTempEnabled  = $_REQUEST['sensorThTempEnabled'] ?? 0;
    $sensorThTempMinValue = $_REQUEST['sensorThTempMinValue'] ?? '';
    $sensorThTempMaxValue = $_REQUEST['sensorThTempMaxValue'] ?? '';
    $sensorThTempAlertMsg = $_REQUEST['sensorThTempAlertMsg'] ?? '';
    $sensorThTempDmGrpId  = $_REQUEST['sensorThTempDmGrpId'] ?? '*';
    $sensorThTempDmGrpId  = $sensorThTempDmGrpId == '' ? '999' : $sensorThTempDmGrpId;

    $arrayParam['sensorThTempEnabled']  = $sensorThTempEnabled;
    $arrayParam['sensorThTempMinValue'] = $sensorThTempMinValue;
    $arrayParam['sensorThTempMaxValue'] = $sensorThTempMaxValue;
    $arrayParam['sensorThTempAlertMsg'] = $sensorThTempAlertMsg;
    $arrayParam['sensorThTempDmGrpId']  = $sensorThTempDmGrpId;

    $sensorThToutEnabled  = $_REQUEST['sensorThToutEnabled'] ?? 0;
    $sensorThToutMinValue = $_REQUEST['sensorThToutMinValue'] ?? '';
    $sensorThToutMaxValue = $_REQUEST['sensorThToutMaxValue'] ?? '';
    $sensorThToutAlertMsg = $_REQUEST['sensorThToutAlertMsg'] ?? '';
    $sensorThToutDmGrpId  = $_REQUEST['sensorThToutDmGrpId'] ?? '*';
    $sensorThToutDmGrpId  = $sensorThToutDmGrpId == '' ? '999' : $sensorThToutDmGrpId;

    $arrayParam['sensorThToutEnabled']  = $sensorThToutEnabled;
    $arrayParam['sensorThToutMinValue'] = $sensorThToutMinValue;
    $arrayParam['sensorThToutMaxValue'] = $sensorThToutMaxValue;
    $arrayParam['sensorThToutAlertMsg'] = $sensorThToutAlertMsg;
    $arrayParam['sensorThToutDmGrpId']  = $sensorThToutDmGrpId;

    $resSetThTempData = setThTempData($arrayParam);

    if ($resSetThTempData === false)
    {
        $returnFlag = false;
    }

    $sensorThIna226vCurrentEnabled  = $_REQUEST['sensorThIna226vCurrentEnabled'] ?? 0;
    $sensorThIna226vCurrentMinValue = $_REQUEST['sensorThIna226vCurrentMinValue'] ?? '';
    $sensorThIna226vCurrentMinValue = $_REQUEST['sensorThIna226vCurrentMinValue'] ?? '';
    $sensorThIna226vCurrentAlertMsg = $_REQUEST['sensorThIna226vCurrentAlertMsg'] ?? '';
    $sensorThIna226vCurrentDmGrpId  = $_REQUEST['sensorThIna226vCurrentDmGrpId'] ?? '*';
    $sensorThIna226vCurrentDmGrpId  = $sensorThIna226vCurrentDmGrpId == '' ? '999' : $sensorThIna226vCurrentDmGrpId;

    return $returnFlag;
}
