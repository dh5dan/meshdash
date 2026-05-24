<?php

function getNinaData(array $params): array|bool
{
    # https://nina.api.bund.dev/
    # Amtlicher Regionalschlüssel - kann z.B. von hier bezogen werden.
    # Die letzten 7 Stellen müssen dabei mit "0000000" ersetzt werden,
    # weil die Daten nur auf Kreisebene bereitgestellt werden.

    # Der amtliche Regionalschlüssel (ARS), oft auch als amtlicher Gemeindeschlüssel (AGS) bezeichnet,
    # ist eine 12-stellige Kennziffer, die in der Warn-App NINA (Notfall-Informations- und Nachrichten-App)
    # des Bundesamtes für Bevölkerungsschutz und Katastrophenhilfe (BBK) zur präzisen geografischen Zuordnung von Warnmeldungen verwendet wird.
    # Hier sind die wichtigsten Fakten zum ARS in Bezug auf NINA:
    # Struktur: Der Schlüssel identifiziert Gemeinden, Kreise und Bundesländer eindeutig.
    # Er setzt sich zusammen aus:
    # Bundesland (2 Stellen),
    # Regierungsbezirk (1),
    # Kreis (2),
    # Gemeindeverband (4)
    # und Gemeinde (3).

    $debugFlag = false;
    $mowasType = $params['mowasType'] ?? '';
    $ars       = $params['ars'] ?? '';
    $warningId = $params['warningId'] ?? '';

    if (empty($mowasType))
    {
        return false;
    }

    if (($mowasType =='nina' || $mowasType == 'mowasRss') && empty($ars)) {
        return false;
    }

    if (($mowasType =='warnings' || $mowasType == 'warningsGeo') && empty($warningId)) {
        return false;
    }

    $headers  = ['Accept: application/json'];
    $urlApi   = '';
    $jsonFile = '';

    switch ($mowasType)
    {
        case 'nina': // Erhalten Sie die aktuellen Warnmeldungen für eine bestimmte Region.
            $urlApi   = 'dashboard/' . $ars . '.json';
            $jsonFile = 'dashboard.json';
            break;
        case 'katwarn': // Liefert die Katwarn-Meldungen für die Kartenansicht. KATWARN → Katastrophenwarnsystem (Versicherung / öffentliche Warnmeldungen)
            $urlApi   = 'katwarn/mapData.json';
            $jsonFile = 'katwarn.json';
            break;
        case 'biwapp': // Liefert die Biwapp-Meldungen für die Kartenansicht. BIWAPP → Bürger-Informations- und Warn-App
            $urlApi   = 'biwapp/mapData.json';
            $jsonFile = 'biwapp.json';
            break;
        case 'mowas': // Liefert die Mowas-Meldungen für die Kartenansicht. MoWaS → Modularen Warnsystem des Bundes (BBK)
            $urlApi   = 'mowas/mapData.json';
            $jsonFile = 'mowas.json';
            break;
        case 'mowasRss': // Erhalte alle aktuellen MoWaS Meldungen einer Region als RSS-Feed.
            $urlApi   = 'mowas/' . $ars . '.rss';
            $jsonFile = 'mowasRss.json';
            $headers  = ['Accept: application/rss+xml'];
            break;
        case 'dwd': // Liefert die Unwetterwarnungen des Deutschen Wetterdienstes für die Kartenansicht. DWD → Deutscher Wetterdienst
            $urlApi   = 'dwd/mapData.json';
            $jsonFile = 'dwd.json';
            break;
        case 'lhp': // Liefert die Meldungen des Länderübergreifenden Hochwasserportals für die Kartenansicht. LHP / LHW → Länderübergreifendes Hochwasserportal
            $urlApi   = 'lhp/mapData.json';
            $jsonFile = 'lhp.json';
            break;
        case 'police': // Liefert die Polizeimeldungen für die Kartenansicht. POLICE → Polizeiliche Warn- und Meldungen (je nach Bundesland/Quelle unterschiedlich angebunden)
            $urlApi   = 'police/mapData.json';
            $jsonFile = 'police.json';
            break;
        case 'warnings': // Detailinformation zu einer Warnung
            $urlApi   = 'warnings/' . $warningId . '.json';
            $jsonFile = 'warnings.json';
            break;
        case 'warningsGeo': // GeoJson Information zu einer Warnung
            $urlApi   = 'warnings/' . $warningId . '.geojson';
            $jsonFile = 'warnings.geojson';
            break;
    }

    if (empty($urlApi))
    {
        return false;
    }

    #Offizielle API-url
    $url = "https://warnung.bund.de/api31/" . $urlApi;

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 5,
    ]);

    $response = curl_exec($ch);

    if ($response === false)
    {
        $error = curl_error($ch);
        curl_close($ch);
        echo "NINA cURL Fehler: " . $error;
        return false;
    }

    curl_close($ch);

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE)
    {
        echo "<br>Json Error: " . json_last_error_msg();
        echo "<br>Json Error: " . json_last_error();
        echo "<br>url:$url";
        echo "<br>resonse: $response";
        return false;
    }

    if($debugFlag === true)
    {
       echo"<br>Debug Json Data:<br>". json_encode($data);
    }

    $execDir             = 'log';
    $basename            = pathinfo(getcwd())['basename'];
    $jsonLogFilenameSub  = '../' . $execDir . '/' . $jsonFile;
    $jsonLogFilenameRoot = $execDir . '/' . $jsonFile;
    $jsonFilename        = $basename == 'menu' ? $jsonLogFilenameSub : $jsonLogFilenameRoot;

    if (!empty($data)) {
        file_put_contents($jsonFilename, json_encode($data));
    }

    // NEU:
    return normalizeAlerts($data, $mowasType);
}
function normalizeAlerts(array $data, string $type): array
{
    $items = extractItems($data);

    return match ($type) {

        'biwapp' => normalizeBiwapp($items),
        'katwarn' => normalizeKatwarn($items),
        'mowas' => normalizeMowas($items),
        'dwd' => normalizeGeneric($items, 'dwd'),
        'police' => normalizeGeneric($items, 'police'),
        'lhp' => normalizeGeneric($items, 'lhp'),
        'nina' => normalizeNina($data),
        'warnings' => normalizeWarningDetail($data),
        'warningsGeo' => $data, // raw durchreichen (GeoJSON)

        default => throw new InvalidArgumentException("Unknown mowasType: " . $type)
    };
}
function normalizeBiwapp(array $items): array
{
    # BIWAPP → Bürger-Informations- und Warn-App

    $result = [];

    foreach ($items as $item) {

        if (empty($item['id'])) {
            continue;
        }

        $severity = $item['severity'] ?? '';

        $result[] = [
            'id'            => (string) $item['id'],
            'title'         => $item['i18nTitle']['de'] ?? '',
            'start'         => formatDateLocal($item['startDate'] ?? null),
            'expires'       => formatDateLocal($item['expiresDate'] ?? null),
            'startTs'       => toTimestamp($item['startDate'] ?? null),
            'expiresTs'     => toTimestamp($item['expiresDate'] ?? null),
            'severity'      => mapSeverity($severity),
            'severityClass' => $severity,
            'severityLevel' => mapSeverityLevel($severity),
            'type'          => $item['type'] ?? '',
            'source'        => 'biwapp',
        ];
    }

    return $result;
}
function normalizeNina(array $data): array
{
    $result = [];

    foreach ($data as $item) {

        if (!is_array($item) || empty($item['id'])) {
            continue;
        }

        $dataBlock = getNinaPayload($item);

        $severity = $dataBlock['severity'] ?? '';

        $result[] = [
            'id'            => (string) $item['id'],
            'title'         => $dataBlock['headline'] ?? ($item['i18nTitle']['de'] ?? ''),
            'start'         => formatDateLocal($item['startDate'] ?? null),
            'expires'       => null,
            'startTs'       => toTimestamp($item['sent'] ?? null),
            'expiresTs'     => null,
            'severity'      => mapSeverity($severity),
            'severityClass' => $severity,
            'severityLevel' => mapSeverityLevel($severity),
            'type'          => $dataBlock['msgType'] ?? '',
            'source'        => 'nina',
        ];
    }

    return $result;
}
function normalizeKatwarn(array $data): array
{
    $result = [];

    foreach ($data as $item) {

        if (empty($item['id'])) {
            continue;
        }

        $severity = $item['severity'] ?? '';

        $result[] = [
            'id'            => (string) $item['id'],
            'title'         => $item['title'] ?? '',
            'start'         => formatDateLocal($item['startDate'] ?? null),
            'expires'       => null,
            'startTs'       => toTimestamp($item['startDate'] ?? null),
            'expiresTs'     => null,
            'severity'      => mapSeverity($severity),
            'severityClass' => $severity,
            'severityLevel' => mapSeverityLevel($severity),
            'type'          => '',
            'source'        => 'katwarn',
        ];
    }

    return $result;
}
function normalizeMowas(array $items): array
{
    $result = [];

    foreach ($items as $item) {

        if (!is_array($item) || empty($item['id'])) {
            continue;
        }

        $severity = $item['severity'] ?? '';

        $title =
            $item['i18nTitle']['de']
            ?? $item['i18nTitle']['en']
            ?? '';

        $result[] = [
            'id'            => (string) $item['id'],
            'title'         => $title,
            'start'         => formatDateLocal($item['startDate'] ?? null),
            'expires'       => formatDateLocal($item['expiresDate'] ?? null),
            'startTs'       => toTimestamp($item['startDate'] ?? null),
            'expiresTs'     => toTimestamp($item['expiresDate'] ?? null),
            'severity'      => mapSeverity($severity),
            'severityClass' => $severity,
            'severityLevel' => mapSeverityLevel($severity),
            'type'          => $item['type'] ?? '',
            'source'        => 'mowas',
        ];
    }

    return $result;
}
function normalizeWarningDetail(array $data): array
{
    if (empty($data['identifier'])) {
        return [];
    }

    $severity = $data['severity'] ?? '';

    return [[
        'id'            => (string) $data['identifier'],
        'title'         => $data['headline'] ?? '',
        'start'         => formatDateLocal($item['sent'] ?? null),
        'expiresTs'     => formatDateLocal($item['expires'] ?? null),
        'startTs'       => toTimestamp($data['sent'] ?? null),
        'expires'       => toTimestamp($data['expires'] ?? null),
        'severity'      => mapSeverity($severity),
        'severityClass' => $severity,
        'severityLevel' => mapSeverityLevel($severity),
        'type'          => $data['msgType'] ?? '',
        'source'        => 'warningDetail'
    ]];
}
function toTimestamp(?string $date): ?int
{
    if (empty($date)) {
        return null;
    }

    $ts = strtotime($date);

    return ($ts !== false) ? $ts : null;
}
function extractItems(array $data): array
{
    // Standard: direkt Array
    #if (is_array($data) && array_keys($data) === range(0, count($data) - 1))
    if (array_is_list($data))
    {
        return $data;
    }

    // GeoJSON (mapData.json)
    if (!empty($data['features'])) {
        return array_map(
            fn($f) => $f['properties'] ?? [],
            $data['features']
        );
    }

    return [];
}
function mapSeverityLevel(?string $severity): int
{
    return match ($severity) {
        'Extreme' => 4,
        'Severe'  => 3,
        'Moderate'=> 2,
        'Minor'   => 1,
        default   => 0
    };
}

function mapSeverity(?string $severity): string
{
    return match ($severity) {
        'Extreme'  => 'Extrem',        // höchste Gefahrenstufe
        'Severe'   => 'Schwerwiegend', // teilweise auch "Erheblich"
        'Moderate' => 'Mäßig',
        'Minor'    => 'Gering',
        default    => 'Unbekannt'
    };
}

function normalizeGeneric(array $items, string $source): array
{
    $result = [];

    foreach ($items as $item) {

        if (!is_array($item)) {
            continue;
        }

        $id = $item['id'] ?? $item['identifier'] ?? null;
        if (empty($id)) {
            continue;
        }

        $severity = $item['severity'] ?? '';

        // 🔥 TITLE FIX (DWD + andere APIs)
        $title =
            $item['headline']
            ?? $item['i18nTitle']['de']
            ?? $item['title']
            ?? '';

        // 🔥 TYPE FIX
        $type =
            $item['msgType']
            ?? $item['type']
            ?? '';

        $result[] = [
            'id'            => (string) $id,
            'title'         => $title,
            'start'         => formatDateLocal($item['sent'] ?? $item['startDate'] ?? null),
            'expires'       => formatDateLocal($item['expires'] ?? $item['expiresDate'] ?? null),
            'startTs'       => toTimestamp($item['sent'] ?? $item['startDate'] ?? null),
            'expiresTs'     => toTimestamp($item['expires'] ?? $item['expiresDate'] ?? null),
            'severity'      => mapSeverity($severity),
            'severityClass' => $severity,
            'severityLevel' => mapSeverityLevel($severity),
            'type'          => $type,
            'source'        => $source,
        ];
    }

    return $result;
}
function getNinaPayload(array $item): array
{
    return $item['payload']['data'] ?? [];
}
function formatDateLocal(?string $date, string $timezone = 'Europe/Berlin'): ?string
{
    if (empty($date)) {
        return null;
    }

    try {
        $dt = new DateTimeImmutable($date);
        $dt = $dt->setTimezone(new DateTimeZone($timezone));

        return $dt->format('d.m.Y H:i');
    } catch (Exception $e) {
        return null;
    }
}