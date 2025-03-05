<?php
ob_start(); // Output Buffering starten
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Update</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/config_update.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_config_update.php';
require_once '../include/func_php_config_update.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

ini_set('upload_max_filesize', '30M'); // Erhöht die maximale Upload-Dateigröße auf 20 MB (2M)
ini_set('post_max_size', '30M'); // Erhöht die maximale POST-Daten-Größe auf 25 MB (8M)
ini_set('memory_limit', '256M'); // Falls nötig das Speicherlimit erhöhen (128M)

$sendData      = $_REQUEST['sendData'] ?? 0;
$debugFlag     = $_REQUEST['debug'] ?? 0 == 1;
$doUpdate      = true;

if ($doUpdate === false)
{
    echo '<span class="failureHint">Update Funktion disabled!</span>';
}

if ($debugFlag === true)
{
    echo "<br>Debug enabled";
}

#Check what oS is running
$osIssWindows = chkOsIssWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';

echo '<h2>MeshDash-SQL Update';
echo '<span class="hintText"><br>(Update-Datei muss im MeshDash-SQL Format<span class="lineBreak">als Zip vorliegen.)</span></span>';
echo '</h2>';

if ($debugFlag === true)
{
    $errorText = date('Y-m-d H:i:s') . ' SendData:' . $sendData . "\n";
    file_put_contents('../log/debug_update.log', $errorText, FILE_APPEND);
}

#Update
if ($sendData === '1')
{
    // Die Verzeichnisse, die nicht überschrieben werden dürfen
    $protectedDirs = ['database', 'log', 'execute', 'sound'];

    // Zielverzeichnis (hier das Verzeichnis der Web-App)
    $rootDir   = dirname(__DIR__); // Das Hauptverzeichnis der Web-App
    $backupDir = $rootDir . '/backup'; // Backup Verzeichnis für das Backup
    $tempDir   = $rootDir . '/update_temp'; // Temporäres Verzeichnis für das entpackte Update

    if ($debugFlag === true)
    {
        $errorText = date('Y-m-d H:i:s') . ' rootDir:' . $rootDir . "\n";
        $errorText .= date('Y-m-d H:i:s') . ' backupDir:' . $backupDir . "\n";
        $errorText .= date('Y-m-d H:i:s') . ' tempDir:' . $tempDir . "\n";
        file_put_contents('../log/debug_update.log', $errorText, FILE_APPEND);
    }

    // Prüft, ob eine Datei hochgeladen wurde und ob sie eine ZIP-Datei ist
    if (isset($_FILES['updateFile']) && $_FILES['updateFile']['error'] === UPLOAD_ERR_OK)
    {
        $uploadFile = $_FILES['updateFile']['tmp_name'];

        if ($debugFlag === true)
        {
            echo "<pre>";
            print_r($_FILES);
            echo "</pre>";

            $filesArray = implode(", ", $_FILES['updateFile']);
            $errorText  = date('Y-m-d H:i:s') . ' filesArray:' . $filesArray . "\n";
            $errorText .= date('Y-m-d H:i:s') . ' uploadFile:' . $uploadFile . "\n";
            file_put_contents('../log/debug_update.log', $errorText, FILE_APPEND);
        }

        $resCheckValidUpdatePackage = checkValidUpdatePackage($uploadFile, $debugFlag);

        if ($resCheckValidUpdatePackage)
        {
            if ($debugFlag === true)
            {
                $tt         = " Führe backupApp aus\n";
                $tt        .= " rootDir:$rootDir\n";
                $tt        .= " backupDir:$backupDir\n";
                $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                file_put_contents('../log/debug_update.log', $errorText, FILE_APPEND);
            }

            // Erstelle ein Backup der aktuellen Version
            $backupFile = backupApp($rootDir, $backupDir);

            if ($backupFile === false)
            {
                echo '<br><span class="failureHint">Fehler beim Erstellen des Backups!</span>';

                echo "<pre>";
                print_r($_FILES);
                echo "</pre>";

                if ($debugFlag === true)
                {   $tArray = implode(',',$_FILES);
                    $tt         = "Fehler beim Erstellen des Backups!\n";
                    $tt        .= "tArray: $tArray\n";
                    $errorText = date('Y-m-d H:i:s') . ' filesArray:' . $tt . "\n";

                    file_put_contents('../log/debug_update.log', $errorText, FILE_APPEND);
                }

                exit;
            }
            else
            {
                echo '<br><span class="successHint">Backupfile ' . $backupFile . ' erfolgreich erstellt!</span>';

                if ($debugFlag === true)
                {
                    $tt        = "Backup-File ' . $backupFile . ' erfolgreich erstellt!\n";
                    $errorText .= date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                    file_put_contents('../log/debug_update.log', $errorText, FILE_APPEND);
                }
            }

            if ($doUpdate === true)
            {
                if (file_exists($tempDir))
                {
                    #Falls noch reste drin sein sollten
                    cleanUp($tempDir);
                }

                // Prüfen, ob das Verzeichnis existiert, wenn nicht, dann erstellen
                if (!is_dir($tempDir))
                {
                    mkdir($tempDir, 0755, true);
                }

                // Entpacke die Update-Datei
                if (unzipUpdate($uploadFile, $tempDir))
                {
                    echo '<br><span class="successHint">Update-Datei erfolgreich entpackt.</span>';

                    if ($debugFlag === true)
                    {
                        $tt        = "Update-Datei erfolgreich entpackt.\n";
                        $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                        file_put_contents('../log/debug_update.log', $errorText, FILE_APPEND);
                    }

                    updateFiles($tempDir, $rootDir, $protectedDirs);

                    if ($osIssWindows === false)
                    {
                        // Berechtigungen anpassen (Optional)
                        setPermissions($rootDir);
                    }

                    // Aufräumen
                    cleanUp($tempDir);

                    echo '<br><span class="successHint">Update abgeschlossen!</span>';

                    if ($debugFlag === true)
                    {
                        $tt        = "Update abgeschlossen!\n";
                        $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                        file_put_contents('../log/debug_update.log', $errorText, FILE_APPEND);
                    }
                }
                else
                {
                    echo '<br><span class="failureHint">Fehler beim Entpacken der Update-Datei!</span>';

                    if ($debugFlag === true)
                    {
                        $tt        = "UFehler beim Entpacken der Update-Datei!\n";
                        $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                        file_put_contents('../log/debug_update.log', $errorText, FILE_APPEND);
                    }
                }
            }
        }
        else
        {
            echo '<br><span class="failureHint">Dies ist kein gültiges Meshdash-SQL Update Packet!</span>';

            if ($debugFlag === true)
            {
                $tt        = "Dies ist kein gültiges Meshdash-SQL Update Packet!\n";
                $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                file_put_contents('../log/debug_update.log', $errorText, FILE_APPEND);
            }
        }
    }
    else
    {
        echo '<br><span class="failureHint">Keine Datei hochgeladen oder Fehler beim Upload!</span>';
        if ($debugFlag === true)
        {
            $tt        = "Keine Datei hochgeladen oder Fehler beim Upload!\n";
            $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

            file_put_contents('../log/debug_update.log', $errorText, FILE_APPEND);
        }
    }
}

#Backup
if ($sendData === '2')
{
    // Zielverzeichnis (hier das Verzeichnis der Web-App)
    $rootDir   = dirname(__DIR__); // Das Hauptverzeichnis der Web-App
    $backupDir = $rootDir . '/backup'; // Backup Verzeichnis für das Backup

    // Erstelle ein Backup der aktuellen Version
    $backupFile = backupApp($rootDir, $backupDir);

    if ($backupFile === false)
    {
        echo '<br><span class="failureHint">Fehler beim Erstellen des Backups!</span>';
        exit;
    }
    else
    {
        echo '<br><span class="successHint">Backupfile ' . $backupFile . ' erfolgreich erstellt!</span>';
    }
}

#Delete Backup
if ($sendData === '3')
{
    $backupDir               = '../backup/';
    $deleteFileImage         = trim($_POST['deleteFileImage']);
    $deleteFileImageFullPath = $backupDir . $deleteFileImage;

    if (file_exists($deleteFileImageFullPath))
    {
        if(unlink($deleteFileImageFullPath))
        {
            echo '<br><span class="successHint">Backupfile ' . $deleteFileImage . ' erfolgreich gelöscht.</span>';
        }
        else
        {
            echo '<br><span class="failureHint">Fehler beim Löschen von Backupfile ' . $deleteFileImage . '</span>';
        }
    }
    else
    {
        echo '<br><span class="failureHint">Das Backup-File: ' . $deleteFileImage . ' wurde nicht im Backup verzeichnis gefunden.</span>';
    }
}

#Lod latest Release from GitHub
if ($sendData === '4')
{
  getLatestRelease();
}
ob_end_flush(); // Ausgabe abschließen
echo '<form id="frmConfigUpdate" action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" name="deleteFileImage" id="deleteFileImage" value="" />';
echo '<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />';

echo '<table>';
echo '<tr>';
if ($sendData != 1)
{
    echo '<td ><label for="updateFile">Wähle das Update (Zip-Datei):&nbsp;</label></td>';
    echo '<td><input type="file" name="updateFile" id="updateFile" required></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td ><label for="updateFile">Lade letztes Release von GitHub herunter:&nbsp;</label></td>';
    echo '<td><input type="button" class="btnDwnLatestRelease" id="btnDwnLatestRelease" value="Hole letzte Release-Version"></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2">&nbsp;</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><input type="button" class="btnConfigUpdateBackup" id="btnConfigUpdateBackup" value="Nur Backup anlegen"/></td>';
    echo '<td><input type="button" class="btnConfigUpdate" id="btnConfigUpdate" value="Update Hochladen"/></td>';
}
else
{
    echo '<td colspan="2">&nbsp;</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td ><label for="btnConfigUpdateReload" class="reloadMsg">MeshDash-Seite jetzt neu laden:&nbsp;</label></td>';
    echo '<td><input type="button" id="btnConfigUpdateReload" value="MeshDash-Seite neu laden"/></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2">&nbsp;</td>';
}
echo '</tr>';
echo '</table>';

echo "<br><br>";

showBackups();

echo '</form>';
echo '</body>';
echo '</html>';