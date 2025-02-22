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
    $onClickCallQrzCom = $_REQUEST['onClickCallQrzCom'] ?? 0;

    setParamData('noPosData', $noPosData);
    setParamData('noDmAlertGlobal', $noDmAlertGlobal);
    setParamData('noTimeSyncMsg', $noTimeSyncMsg);
    setParamData('doLogEnable', $doLogEnable);
    setParamData('loraIp', $loraIp, 'txt');
    setParamData('callSign', strtoupper(trim($callSign)), 'txt');
    setParamData('maxScrollBackRows', $maxScrollBackRows);
    setParamData('doNotBackupDb', $doNotBackupDb);
    setParamData('onClickCallQrzCom', $onClickCallQrzCom);

    return true;
}
