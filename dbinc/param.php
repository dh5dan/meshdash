<?php
const VERSION = '1.10.72';
date_default_timezone_set('Europe/Berlin');

if (PHP_SAPI === 'cli')
{
    $baseWebUrl    = 'http://localhost'; // ggf. mit Port anpassen
    $knownRootFile = 'index.php';

    $currentPath = realpath(__DIR__);
    $parts       = explode(DIRECTORY_SEPARATOR, $currentPath);

    while (!empty($parts))
    {
        $testPath = implode(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR . $knownRootFile;
        if (file_exists($testPath))
        {
            break;
        }
        array_pop($parts);
    }

    $meshDashRootPath = implode(DIRECTORY_SEPARATOR, $parts);

    // DOCUMENT_ROOT dynamisch versuchen zu bestimmen
    $docRoot = null;

    if (!empty($_SERVER['DOCUMENT_ROOT']))
    {
        $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
    }

    if (!$docRoot || strpos($meshDashRootPath, $docRoot) !== 0)
    {
        // Fallback: Parent des Root-Verzeichnisses als Document Root
        $docRoot = dirname($meshDashRootPath);
    }

    $relativeWebPath = substr($meshDashRootPath, strlen($docRoot));
    $relativeWebPath = str_replace(DIRECTORY_SEPARATOR, '/', $relativeWebPath);
    $relativeWebPath = trim($relativeWebPath, '/');

    if (!empty($relativeWebPath))
    {
        $relativeWebPath .= '/';
    }

    define('BASE_PATH_URL', rtrim($baseWebUrl, '/') . '/' . $relativeWebPath);
}
else
{
    // Web-Modus wie gehabt
    $protocol = $_SERVER['REQUEST_SCHEME'] . '://';
    $host     = $_SERVER['HTTP_HOST'];

    $scriptName = $_SERVER['SCRIPT_NAME'];
    $scriptDir  = dirname($scriptName);

    $baseDir = explode('/', trim($scriptDir, '/'));
    while (!empty($baseDir))
    {
        $path = '/' . implode('/', $baseDir) . '/';
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path . 'index.php'))
        {
            break;
        }

        array_pop($baseDir);
    }

    $basePath    = '/' . implode('/', $baseDir) . '/';
    $meshDashUrl = $protocol . $host . $basePath;

    define('BASE_PATH_URL', $meshDashUrl);
}

#Prevents Warning: preg_replace(): Allocation of JIT memory failed, PCRE JIT will be disabled.
ini_set("pcre.jit", "0");

$triggerLinkSendQueue = BASE_PATH_URL . 'send_queue.php';
define('TRIGGER_LINK_SEND_QUEUE', $triggerLinkSendQueue);
const CRON_PID_FILE = 'cron_loop.pid';
const CRON_CONF_FILE = 'cron_interval.conf';
const CRON_STOP_FILE = 'cron_stop';
const CRON_PROC_FILE = 'cron_loop.php';

$triggerLinkSendBeacon = BASE_PATH_URL . 'send_beacon.php';
define('TRIGGER_LINK_SEND_BEACON', $triggerLinkSendBeacon);
const CRON_BEACON_PID_FILE = 'cron_beacon_loop.pid';
const CRON_BEACON_CONF_FILE = 'cron_beacon_interval.conf';
const CRON_BEACON_STOP_FILE = 'cron_beacon_stop';
const CRON_BEACON_PROC_FILE = 'cron_beacon_loop.php';

const UPD_PID_FILE = 'udp.pid';
const UPD_STOP_FILE = 'udp_stop';
const UDP_PROC_FILE = 'udp_receiver.php';

const SQLITE3_BUSY_TIMEOUT = 15000;  //Sqlite3 Timeout on Busy
const SQLITE3_LOCK_RETRY_MAX_ATTEMPTS = 5; //Sqlite3 max. retry counts
const SQLITE3_LOCK_RETRY_DELAY_MS = 100; //Sqlite3 max. Wait in ms between retry counts
