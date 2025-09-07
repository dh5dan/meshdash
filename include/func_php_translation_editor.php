<?php

function getTranslationColumns(): array
{
    $dbFile = 'database/translation.db';
    $db = new SQLite3($dbFile);

    // Sprachen dynamisch ermitteln (alle Spalten außer 'key')
    $res = $db->query("PRAGMA table_info(translation)");
    $columns = [];
    while ($col = $res->fetchArray(SQLITE3_ASSOC)) {
        //    if ($col['name'] !== 'key') {
        //        $columns[] = $col['name'];
        //    }

        $columns[] = $col['name'];
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return $columns;
}

function saveTranslationItem($columns)
{
    $dbFile = 'database/translation.db';
    $db     = new SQLite3($dbFile);
    $key    = $_POST['key'];
    $values = array_intersect_key($_POST, array_flip($columns));

    // Prüfen, ob existiert
    $check = $db->prepare("SELECT 1 FROM translation WHERE key = :key");
    $check->bindValue(':key', $key, SQLITE3_TEXT);
    $exists = $check->execute()->fetchArray();

    if ($exists)
    {
        $set = [];
        foreach ($columns as $lang)
        {
            $set[] = "$lang = :$lang";
        }

        $stmt = $db->prepare("UPDATE translation SET " . implode(',', $set) . " WHERE key = :key");
    }
    else
    {
        #$stmt = $db->prepare("INSERT INTO translation (key, " . implode(',', $columns) . ") VALUES (:" . implode(", :", array_merge(['key'], $columns)) . ")");
        $stmt = $db->prepare("INSERT INTO translation (" . implode(',', $columns) . ") VALUES (:" . implode(', :', $columns) . ")");
    }

    $stmt->bindValue(':key', $key, SQLITE3_TEXT);
    foreach ($columns as $lang)
    {
        $stmt->bindValue(":$lang", $values[$lang] ?? '', SQLITE3_TEXT);
    }
    $stmt->execute();

    #Close and write Back WAL
    $db->close();
    unset($db);

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

function deleteTranslationItem()
{
    $dbFile = 'database/translation.db';
    $db     = new SQLite3($dbFile);
    $stmt   = $db->prepare("DELETE FROM translation WHERE key = :key");
    $stmt->bindValue(':key', $_GET['delete'], SQLITE3_TEXT);
    $stmt->execute();

    #Close and write Back WAL
    $db->close();
    unset($db);

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
