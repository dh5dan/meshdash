<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

$userLang = getParamData('language');
$userLang = $userLang == '' ? 'de' : $userLang;
echo '<!DOCTYPE html>';
echo '<html lang="' . $userLang . '">';
echo '<head><title>Übersetzungseditor</title>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';

echo '<script type="text/javascript" src="jquery/jquery.min.js"></script>';

echo '<link rel="stylesheet" href="jquery/jquery-ui-1.13.3/jquery-ui.css">';
echo '<link rel="stylesheet" href="jquery/css/jq_custom.css">';

if ((getParamData('darkMode') ?? 0) == 1)
{
    echo '<link rel="stylesheet" href="css/dark_mode.css?' . microtime() . '">';
}
else
{
    echo '<link rel="stylesheet" href="css/normal_mode.css?' . microtime() . '">';
}

echo '<link rel="stylesheet" href="css/translation_editor.css?' . microtime() . '">';
echo '<link rel="icon" type="image/png" sizes="16x16" href="favicon.png">';

echo '</head>';
echo '<body>';

#Prevents UTF8 Errors on misconfigured php.ini
ini_set('default_charset', 'UTF-8' );

require_once 'include/func_php_translation_editor.php';
require_once 'include/func_js_translation_editor.php';

$dbFile = 'database/translation.db';
$columns = getTranslationColumns();

// Bearbeiten / Hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    saveTranslationItem($columns);
}

// Löschen
if (isset($_GET['delete']))
{
    deleteTranslationItem();
}

// Liste aller Einträge
$db = new SQLite3($dbFile);
$rows = $db->query("SELECT * FROM translation ORDER BY key;");

echo '<h2>Übersetzungen bearbeiten</h2>';

echo '<table>';

    echo '<thead>';
        echo '<tr>';
                foreach ($columns as $lang)
                {
                    echo '<th>' . strtoupper($lang) . '</th>';
                }

                echo '<th>Save/Del</th>';
        echo '</tr>';
    echo '</thead>';

    echo '<tbody>';
    while ($row = $rows->fetchArray(SQLITE3_ASSOC))
    {
        echo '<tr>';

            echo '<td>';
                echo '<form method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
                    echo '<tr>';

                    foreach ($columns as $lang) {
                        $readonly = ($lang === 'key') ? 'readonly' : '';
                        echo '<td><input type="text" name="' . $lang . '" value="' . htmlspecialchars($row[$lang]) . '" ' . $readonly . '></td>';
                    }

                    echo '<td>';
                    echo '<button type="submit">💾</button>';
                    echo '&nbsp;';
                    #echo '<a href="?delete=' . urlencode($row['key']) . '" onclick="return dialogConfirm(\'Wirklich löschen?\', \'Hinweis\', 550)">🗑️</a>';
                    echo '<a href="?delete=' . urlencode($row['key']) . '" onclick="return confirm(\'Wirklich löschen?\')">🗑️</a>';
                    echo '</td>';

                    echo '</tr>';
                echo '</form>';
            echo '</td>';
        echo '</tr>';
    }

echo '</tbody>';
echo '</table>';

echo '<h3>Neue Übersetzung hinzufügen</h3>';
echo '<form method="post">';
    echo '<table>';
        echo '<tr>';

            foreach ($columns as $lang)
            {
                echo '<td><input type="text" name="' . $lang . '" placeholder="' . strtoupper($lang) . '"></td>';
            }

            echo '<td><button type="submit">➕ Hinzufügen</button></td>';
        echo '</tr>';
    echo '</table>';
echo '</form>';

#Close and write Back WAL
$db->close();
unset($db);

echo '</body>';
echo '</html>';
