<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

if (isset($_POST['icon_index']))
{
    $index = (int) $_POST['icon_index'];

    $db   = new SQLite3('database/parameter.db');
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden
    $db->exec('PRAGMA synchronous = NORMAL;');

    $index = SQLite3::escapeString($index);

    $sql = "UPDATE parameter 
               SET param_value = '$index' 
             WHERE param_key = 'clickOnCall';
           ";

    $logArray[] = "ajax_set_call_click: Database: database/parameter.db";
    $logArray[] = "ajax_set_call_click: index: $index";

    $res = safeDbRun( $db,  $sql, 'exec', $logArray);

    #Close and write Back WAL
    $db->close();
    unset($db);
}

