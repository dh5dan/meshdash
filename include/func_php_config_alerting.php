<?php

function saveAlertingSettings(): bool
{
    $alertSoundFileSrc = $_REQUEST['alertSoundFileSrc'] ?? '';
    $alertEnabledSrc   = $_REQUEST['alertEnabledSrc'] ?? 0;
    $alertSoundCallSrc = $_REQUEST['alertSoundCallSrc'] ?? '';

    $alertSoundFileDst = $_REQUEST['alertSoundFileDst'] ?? '';
    $alertEnabledDst   = $_REQUEST['alertEnabledDst'] ?? 0;
    $alertSoundCallDst = $_REQUEST['alertSoundCallDst'] ?? '';

    setParamData('alertSoundFileSrc', strtoupper($alertSoundFileSrc), 'txt');
    setParamData('alertEnabledSrc', $alertEnabledSrc);
    setParamData('alertSoundCallSrc', $alertSoundCallSrc, 'txt');

    setParamData('alertSoundFileDst', strtoupper($alertSoundFileDst), 'txt');
    setParamData('alertEnabledDst', $alertEnabledDst);
    setParamData('alertSoundCallDst', $alertSoundCallDst, 'txt');

    return true;
}
