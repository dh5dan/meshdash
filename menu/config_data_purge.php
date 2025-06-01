<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Nachrichtendaten löschen</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';

echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/config_data_purge.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_data_purge.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

$sendData = $_REQUEST['sendData'] ?? 0;

echo '<span class="unsetDisplayFlex">';
echo "<br>";

if ($sendData === '11')
{
    $purgeDateIso = $_REQUEST['purgeDate'] ?? 0;
    $purgeDateNat = date( "Y-m-d", strtotime($purgeDateIso));

    $db = new SQLite3('../database/meshdash.db', SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    $sql = "SELECT COUNT(*) AS count 
              FROM meshdash
             WHERE DATE(timestamps) < '$purgeDateNat';
           ";

    $logArray   = array();
    $logArray[] = "config_data_purge_cnt: Database: database/meshdash.db";
    $logArray[] = "config_data_purge_cnt: purgeDateNat: $purgeDateNat";

    $res = safeDbRun($db, $sql, 'query', $logArray);

    if ($res === false)
    {
        #Close and write Back WAL
        $db->close();
        unset($db);

        echo '<br><span class="failureHint">Fehler bei der Abfrage aufgetreten!</span>';
        exit();
    }

    $rows  = $res->fetchArray(SQLITE3_ASSOC);
    $count = $rows['count'];

    #Close and write Back WAL
    $db->close();
    unset($db);

    echo '<br>Es würden <b>'. $count . '</b> Nachrichtendaten gelöscht werden.';
    echo '<form id="frmPurgeData" method="post"  action="' . $_SERVER['REQUEST_URI'] . '">';
    echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
    echo '<br><b>Alle Nachrichten löschen bis zum gewähltem Datum</b>';
    echo '&nbsp;&nbsp;&nbsp;<input type="text" name="purgeDateNow" id="purgeDateNow" readonly value="' . $purgeDateIso . '" required placeholder="" />';

    if ($count != 0)
    {
        echo '&nbsp;&nbsp;&nbsp;<input type="button" class="submitParamLoraIp" id="btnPurgeDataNow" value="Nachrichtendaten jetzt löschen" />';
    }

    echo '<br><br><input type="button" class="submitParamLoraIp" id="btnPurgeNew" value="Daten mit neuem Datum ermitteln" />';
    echo '</form>';
}
elseif ($sendData === '13')
{
    $purgeDateIso = $_REQUEST['purgeDateNow'] ?? 0;
    $purgeDateNat = date( "Y-m-d", strtotime($purgeDateIso));

    $db = new SQLite3('../database/meshdash.db');
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    $sql = "DELETE FROM meshdash 
                  WHERE DATE(timestamps) < '$purgeDateNat';
                     ";

    $logArray   = array();
    $logArray[] = "config_data_purge_del: Database: database/meshdash.db";
    $logArray[] = "config_data_purge_del: purgeDateNat: $purgeDateNat";

    $res = safeDbRun($db, $sql, 'exec', $logArray);

    if ($res === false)
    {
        #Close and write Back WAL
        $db->close();
        unset($db);

        echo '<br><span class="failureHint">Fehler beim Löschen aufgetreten!</span>';
        exit();
    }

    echo '<br><span class="successHint">Es wurden ' . $db->changes() . ' Nachrichtendaten gelöscht.</span>';

    #Close and write Back WAL
    $db->close();
    unset($db);

}
else
{
    echo '<form id="frmPurgeData" method="post"  action="' . $_SERVER['REQUEST_URI'] . '">';
    echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
    echo '<br><b>Ermittel Anzahl der Nachrichten bis zum gewählten Datum</b><br><br>';
    echo '<input type="text" name="purgeDate" id="purgeDate" readonly value="" required placeholder="dd.mm.yyyy" />';
    echo '&nbsp;&nbsp;&nbsp;<input type="button" class="submitParamLoraIp" id="btnPurgeData" value="Anzahl Nachrichtendaten jetzt ermitteln"  />';
    echo '</form>';
}

echo '</span>';

echo '</body>';
echo '</html>';