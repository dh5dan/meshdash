<?php

function saveSettingsAutoPurge(): bool
{
    $enableMsgPurge    = $_REQUEST['enableMsgPurge'] ?? 0;
    $enableSensorPurge = $_REQUEST['enableSensorPurge'] ?? 0;
    $daysMsgPurge      = $_REQUEST['daysMsgPurge'] ?? 30;
    $daysSensorPurge   = $_REQUEST['daysSensorPurge'] ?? 30;

    setParamData('enableMsgPurge', $enableMsgPurge);
    setParamData('enableSensorPurge', $enableSensorPurge);
    setParamData('daysMsgPurge', $daysMsgPurge);
    setParamData('daysSensorPurge', $daysSensorPurge);

    return true;
}
