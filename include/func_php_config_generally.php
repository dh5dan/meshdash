<?php
function saveGenerallySettings(): bool
{
    $noPosData               = $_REQUEST['noPosData'] ?? 0;
    $noDmAlertGlobal         = $_REQUEST['noDmAlertGlobal'] ?? 0;
    $noTimeSyncMsg           = $_REQUEST['noTimeSyncMsg'] ?? 0;
    $doLogEnable             = $_REQUEST['doLogEnable'] ?? 0;
    $loraIp                  = trim($_REQUEST['loraIp']) ?? '0.0.0.0';
    $loraIp                  = $loraIp == '' ? '0.0.0.0' : $loraIp;
    $callSign                = trim($_REQUEST['callSign']) ?? '';
    $maxScrollBackRows       = trim($_REQUEST['maxScrollBackRows']) ?? 60;
    $maxScrollBackRows       = $maxScrollBackRows == '' ? 60 : $maxScrollBackRows;
    $doNotBackupDb           = $_REQUEST['doNotBackupDb'] ?? 0;
    $clickOnCall             = $_REQUEST['clickOnCall'] ?? 0;
    $chronLogEnable          = $_REQUEST['chronLogEnable'] ?? 0;
    $retentionDays           = trim($_REQUEST['retentionDays']) ?? 7;
    $retentionDays           = $retentionDays == '' ? 7 : $retentionDays;
    $chronMode               = $_REQUEST['chronMode'] == '' ? 'zip' : $_REQUEST['chronMode'];
    $strictCallEnable        = $_REQUEST['strictCallEnable'] ?? 0;
    $selTzName               = $_REQUEST['selTzName'] ?? 'Europe/Berlin';
    $newMsgBgColor           = $_REQUEST['newMsgBgColor'] ?? '#FFFFFF';
    $mheardGroup             = trim($_REQUEST['mheardGroup']) ?? 0;
    $openStreetTileServerUrl = trim($_REQUEST['openStreetTileServerUrl']) ?? 'tile.openstreetmap.org';
    $bubbleStyleView         = $_REQUEST['bubbleStyleView'] ?? 0;
    $bubbleMaxWidth          = trim($_REQUEST['bubbleMaxWidth']) ?? 40;
    $bubbleMaxWidth          = $bubbleMaxWidth == '' ? 40 : $bubbleMaxWidth;
    $language                = $_REQUEST['selLanguage'] ?? 'de';
    $language                = $language == '' ? 'de' : $language;

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
    setParamData('timeZone', $selTzName, 'txt');
    setParamData('newMsgBgColor', $newMsgBgColor, 'txt');
    setParamData('mheardGroup', $mheardGroup);
    setParamData('openStreetTileServerUrl', $openStreetTileServerUrl, 'txt');
    setParamData('bubbleStyleView', $bubbleStyleView);
    setParamData('bubbleMaxWidth', $bubbleMaxWidth);
    setParamData('language', $language, 'txt');

    return true;
}
function selectTimezone($selTzName)
{
    $selTzName = $selTzName ?? 'Europe/Berlin';

    $timezones = [
        'Pacific/Pago_Pago' => -11,  // UTC-11
        'Pacific/Honolulu' => -10,   // UTC-10
        'America/Anchorage' => -9,   // UTC-9
        'America/Vancouver' => -8,   // UTC-8
        'America/Los_Angeles' => -7, // UTC-7
        'America/Chicago' => -6,     // UTC-6
        'America/Toronto' => -5,     // UTC-5
        'America/Caracas' => -4,     // UTC-4
        'America/Sao_Paulo' => -3,   // UTC-3
        'America/Noronha' => -2,     // UTC-2
        'Atlantic/Azores' => -1,     // UTC-1
        'UTC' => 0,                  // UTC+0
        'Europe/Berlin' => +1,        // UTC+1
        'Africa/Cairo' => +2,         // UTC+2
        'Africa/Nairobi' => +3,       // UTC+3
        'Asia/Dubai' => +4,           // UTC+4
        'Asia/Karachi' => +5,          // UTC+5
        'Asia/Dhaka' => +6,           // UTC+6
        'Asia/Bangkok' => +7,         // UTC+7
        'Asia/Hong_Kong' => +8,       // UTC+8
        'Asia/Tokyo' => +9,           // UTC+9
        'Australia/Sydney' => +10,    // UTC+10
        'Australia/Lord_Howe' => +11, // UTC+11
        'Pacific/Fiji' => +12,        // UTC+12
        'Pacific/Tongatapu' => +13,   // UTC+13
    ];

    echo "<option>Zeitzone wählen</option>";

    foreach ($timezones as $code => $name) {

        $name = $name > 0 ? '+' . $name : $name;

        if ($selTzName == $code)
        {
            echo '<option value="' . $code . '" selected>(UTC' . $name . ') ' . $code . '</option>';
        }
        else
        {
            echo '<option value="' . $code . '">(UTC' . $name . ') ' . $code . '</option>';
        }
    }
}

function selectLanguage($selLanguage)
{
    $selLanguage = $selLanguage == '' ? 'de' : $selLanguage;

    $languages = [
        'Deutsch' => 'de',
        'Englisch' => 'en',
        'Französisch' => 'fr',
        'Spanisch' => 'es',
        'Niederländisch' => 'nl',
        'Italienisch' => 'it',
    ];

    foreach ($languages as $name => $code) {

        $iconHtml = html_entity_decode(getStatusIcon($code));

        if ($selLanguage == $code)
        {
            echo '<option value="' . $code . '" selected>' . $iconHtml . ' ' . $name . ' (' . $code . ')</option>';
        }
        else
        {
            echo '<option value="' . $code . '">' . $iconHtml . ' ' . $name . ' (' . $code . ')</option>';
        }
    }
}
