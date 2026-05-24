<?php
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjax)
{
    #exit();
}

/* if the 'term' variable is not sent with the request, exit */
if (!isset($_GET['term'], $_GET['type'])) {
    exit;
}

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

$debugFlag           = false;
$term                = strtolower(trim(strip_tags($_GET['term'])));
$requestType         = (int) $_GET['type'];

$a_json     = array();
$a_json_row = array();
$parts[]    = $term;

if ($requestType === 1)
{
    $db = new SQLite3('database/nina_ars.db', SQLITE3_OPEN_READONLY);
    $db->busyTimeout(SQLITE3_BUSY_TIMEOUT); // warte wenn busy in millisekunden

    $sqlQuery = "SELECT *
                   FROM nina_ars
                  WHERE LOWER(stadt) LIKE '%$term%';
                ";

    $logArray   = array();
    $logArray[] = "Ajax Autocomplete Search ARS: Database: database/nina_ars.db";
    $logArray[] = "Type: 1 term: $term";

    $countResult = safeDbRun($db, $sqlQuery, 'query', $logArray);

    if ($countResult !== false)
    {
        while ($row = $countResult->fetchArray(SQLITE3_ASSOC))
        {
            $id    = strip_tags(stripslashes($row['arsId']));
            $label = strip_tags(stripslashes(trim($row['stadt'])));

            #Verhindert das ein leeres Array bei Highlight funktion Fehler verursacht
            if ($label != '')
            {
                $a_json_row["id"]    = $id;
                $a_json_row["label"] = $label;
                $a_json_row["rawLabel"] = $label;
                $a_json[] = $a_json_row;
            }
        }
    }

    $db->close();
    unset($db);
}

if ($debugFlag === true)
{
    echo "<pre>";
    print_r($a_json);
    var_dump($a_json);
    echo "</pre>";
}

// highlight search results
$a_json = apply_highlight($a_json, $parts);

// jQuery wants JSON data
// Value must be  UTF8 encoded!
echo json_encode($a_json);
#echo json_last_error_msg();
exit();
//flush();

