<?php

if (isset($_POST['icon_index']))
{
    $index = (int) $_POST['icon_index'];

    try
    {
        $db   = new SQLite3('database/parameter.db');
        $db->busyTimeout(5000); // warte wenn busy in millisekunden
        $stmt = $db->prepare("UPDATE parameter SET param_value = :index WHERE param_key = 'clickOnCall'");
        $stmt->bindValue(':index', $index, SQLITE3_INTEGER);
        $stmt->execute();
    }
    catch (Exception $e)
    {
        echo "Fehler: " . $e->getMessage();
    }
}

