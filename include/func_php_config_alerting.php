<?php

function saveAlertingSettings(): bool
{
    $alertSoundFileSrc = $_REQUEST['alertSoundFileSrc'] ?? '';
    $alertEnabledSrc   = $_REQUEST['alertEnabledSrc'] ?? 0;
    $alertSoundCallSrc = $_REQUEST['alertSoundCallSrc'] ?? '';

    $alertSoundFileDst = $_REQUEST['alertSoundFileDst'] ?? '';
    $alertEnabledDst   = $_REQUEST['alertEnabledDst'] ?? 0;
    $alertSoundCallDst = $_REQUEST['alertSoundCallDst'] ?? '';

    setParamData('alertSoundFileSrc', $alertSoundFileSrc, 'txt');
    setParamData('alertEnabledSrc', $alertEnabledSrc);
    setParamData('alertSoundCallSrc', strtoupper($alertSoundCallSrc), 'txt');

    setParamData('alertSoundFileDst', $alertSoundFileDst, 'txt');
    setParamData('alertEnabledDst', $alertEnabledDst);
    setParamData('alertSoundCallDst', strtoupper($alertSoundCallDst), 'txt');

    return true;
}
