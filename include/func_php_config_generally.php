<?php

function saveGenerallySettings(): bool
{
    $noPosData         = $_REQUEST['noPosData'] ?? 0;
    $noDmAlertGlobal   = $_REQUEST['noDmAlertGlobal'] ?? 0;
    $noTimeSyncMsg     = $_REQUEST['noTimeSyncMsg'] ?? 0;
    $doLogEnable       = $_REQUEST['doLogEnable'] ?? 0;
    $loraIp            = $_REQUEST['loraIp'] ?? '0.0.0.0';
    $loraIp            = $loraIp == '' ? '0.0.0.0' : $loraIp;
    $callSign          = $_REQUEST['callSign'] ?? '';
    $maxScrollBackRows = $_REQUEST['maxScrollBackRows'] ?? 60;
    $maxScrollBackRows = $maxScrollBackRows == '' ? 60 : $maxScrollBackRows;
    $doNotBackupDb     = $_REQUEST['doNotBackupDb'] ?? 0;
    $clickOnCall       = $_REQUEST['clickOnCall'] ?? 0;
    $chronLogEnable    = $_REQUEST['chronLogEnable'] ?? 0;
    $retentionDays     = $_REQUEST['retentionDays'] ?? 7;
    $chronMode         = $_REQUEST['chronMode'] == '' ? 'zip' : $_REQUEST['chronMode'];
    $strictCallEnable  = $_REQUEST['strictCallEnable'] ?? 0;

    setParamData('noPosData', $noPosData);
    setParamData('noDmAlertGlobal', $noDmAlertGlobal);
    setParamData('noTimeSyncMsg', $noTimeSyncMsg);
    setParamData('doLogEnable', $doLogEnable);
    setParamData('loraIp', $loraIp, 'txt');
    setParamData('callSign', strtoupper(trim($callSign)), 'txt');
    setParamData('maxScrollBackRows', $maxScrollBackRows);
    setParamData('doNotBackupDb', $doNotBackupDb);
    setParamData('clickOnCall', $clickOnCall);
    setParamData('chronLogEnable', $chronLogEnable);
    setParamData('retentionDays', $retentionDays);
    setParamData('chronMode', trim($chronMode), 'txt');
    setParamData('strictCallEnable', $strictCallEnable);

    return true;
}
