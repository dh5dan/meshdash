<?php
ob_start(); // Output Buffering starten
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<head><title>Restore</title>';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '<script type="text/javascript" src="../jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="../jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="../jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="../jquery/css/jq_custom.css">';
echo '<link rel="stylesheet" href="../css/config_restore.css?' . microtime() . '">';
echo '<link rel="stylesheet" href="../css/loader.css?' . microtime() . '">';
echo '</head>';
echo '<body>';

require_once '../dbinc/param.php';
require_once '../include/func_php_core.php';
require_once '../include/func_js_config_restore.php';
require_once '../include/func_php_config_restore.php';

#Show all Errors for debugging
error_reporting(E_ALL);
ini_set('display_errors',1);

ini_set('upload_max_filesize', '30M'); // Erhöht die maximale Upload-Dateigröße auf 20 MB (2M)
ini_set('post_max_size', '30M'); // Erhöht die maximale POST-Daten-Größe auf 25 MB (8M)
ini_set('memory_limit', '256M'); // Falls nötig das Speicherlimit erhöhen (128M)

$sendData      = $_REQUEST['sendData'] ?? 0;
$debugFlag     = false;
$doRestore     = true;

if ($doRestore === false)
{
    echo '<span class="failureHint">Restore Funktion disabled!</span>';
}

if ($debugFlag === true)
{
    echo "<br>Debug enabled";
}

#Check what oS is running
$osIssWindows = chkOsIsWindows();
$osName       = $osIssWindows === true ? 'Windows' : 'Linux';

echo '<h2>MeshDash-SQL Restore';
echo '<span class="hintText"><br>(Restore-Datei muss im MeshDash-SQL Format<span class="lineBreak">als Zip vorliegen.)</span></span>';
echo '</h2>';

if ($debugFlag === true)
{
    $errorText = date('Y-m-d H:i:s') . ' SendData:' . $sendData . "\n";
    file_put_contents('../log/debug_restore.log', $errorText, FILE_APPEND);
}

#Restore
if ($sendData === '1')
{
    // Zielverzeichnis (hier das Verzeichnis der Web-App)
    $rootDir    = dirname(__DIR__); // Das Hauptverzeichnis der Web-App
    $restoreDir = $rootDir . '/'; // Root-Verzeichnis für das entpackte Restore-Paket

    if ($debugFlag === true)
    {
        $errorText = date('Y-m-d H:i:s') . ' rootDir:' . $rootDir . "\n";
        file_put_contents('../log/debug_restore.log', $errorText, FILE_APPEND);
    }

    // Prüft, ob eine Datei hochgeladen wurde und ob sie eine ZIP-Datei ist
    if (isset($_FILES['restoreFile']) && $_FILES['restoreFile']['error'] === UPLOAD_ERR_OK)
    {
        $uploadFile = $_FILES['restoreFile']['name'];

        if (move_uploaded_file($_FILES['restoreFile']['tmp_name'], $restoreDir . $uploadFile))
        {
            if ($debugFlag === true)
            {
                echo "<pre>";
                print_r($_FILES);
                echo "</pre>";

                $filesArray = implode(", ", $_FILES['restoreFile']);
                $errorText  = date('Y-m-d H:i:s') . ' filesArray:' . $filesArray . "\n";
                $errorText  .= date('Y-m-d H:i:s') . ' uploadFile:' . $restoreDir . $uploadFile . "\n";
                file_put_contents('../log/debug_restore.log', $errorText, FILE_APPEND);
            }

            $resCheckValidRestorePackage = checkValidRestorePackage($uploadFile, $debugFlag);

            if ($resCheckValidRestorePackage === true)
            {
                $restoreHasErrors = false;

                if ($debugFlag === true)
                {
                    $tt         = " Führe restoreApp aus\n";
                    $tt        .= " rootDir:$rootDir\n";
                    $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                    file_put_contents('../log/debug_restore.log', $errorText, FILE_APPEND);
                }

                if ($doRestore === true)
                {
                    #Beende UDP-BG Prozess.
                    echo '<br><span class="successHint">Beende UDP-Receiver Prozess.</span>';
                    $paramBgProcess['task'] = 'udp';
                    $stopBgProcessUdp = stopBgProcess($paramBgProcess);

                    if ($stopBgProcessUdp === true)
                    {
                        echo '<br><span class="successHint">UDP-Receiver Prozess beendet.</span>';
                    }
                    else
                    {
                        echo '<br><span class="successFail">Fehler beim Beenden von UDP-Receiver Prozess.</span>';
                    }

                    #wenn Cron rennt dann auch beenden
                    if (!(checkCronLoopBgTask() == '') === true)
                    {
                        #Beende UDP-BG Prozess.
                        echo '<br><span class="successHint">Beende CRON-Prozess.</span>';
                        $paramBgProcess['task'] = 'cron';
                        $stopBgProcessCron = stopBgProcess($paramBgProcess);

                        if ($stopBgProcessCron === true)
                        {
                            echo '<br><span class="successHint">CRON-Prozess beendet.</span>';
                        }
                        else
                        {
                            echo '<br><span class="successFail">Fehler beim beenden von CRON-Prozess.</span>';
                        }
                    }

                    // Entpacke die Restore-Datei
                    if (unzipRestore($restoreDir . $uploadFile, $restoreDir))
                    {
                        echo '<br><span class="successHint">Restore-Datei erfolgreich entpackt.</span>';

                        if ($debugFlag === true)
                        {
                            $tt        = "Restore-Datei erfolgreich entpackt.\n";
                            $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                            file_put_contents('../log/debug_restore.log', $errorText, FILE_APPEND);
                        }

                        $resCheckDatabaseRestore = checkDatabaseRestore();

                        if ($resCheckDatabaseRestore === false)
                        {
                            echo '<br><span class="failureHint">Fehler beim Datenbank-Restore aufgetreten!</span>';
                            $restoreHasErrors = true;
                        }

                        // Aufräumen ZIP löschen aus Root
                        unlink($restoreDir . $uploadFile);

                        echo '<br><span class="successHint">Restore erfolgreich abgeschlossen!</span>';

                        if ($debugFlag === true)
                        {
                            $tt        = "Restore abgeschlossen!\n";
                            $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                            file_put_contents('../log/debug_restore.log', $errorText, FILE_APPEND);
                        }

                        ############## Starte Prozesse

                        #Starte UDP-BG Prozess.
                        echo '<br><span class="successHint">Starte UDP-Receiver Prozess.</span>';
                        $paramBgProcess['task'] = 'udp';
                        $startBgProcessUdp = startBgProcess($paramBgProcess);

                        if (!empty($startBgProcessUdp))
                        {
                            echo '<br><span class="successHint">UDP-Receiver Prozess gestartet.</span>';
                        }
                        else
                        {
                            echo '<br><span class="successFail">Fehler beim Starten von UDP-Receiver Prozess.</span>';
                        }

                        #wenn Cron gestartet war dann auch wieder starten
                        if (!(checkCronLoopBgTask() == '') === true)
                        {
                            #Beende UDP-BG Prozess.
                            echo '<br><span class="successHint">Starte CRON-Prozess.</span>';
                            $paramBgProcess['task'] = 'cron';
                            $startBgProcessCron = stopBgProcess($paramBgProcess);

                            if (!empty($startBgProcessCron))
                            {
                                echo '<br><span class="successHint">CRON-Prozess gestartet.</span>';
                            }
                            else
                            {
                                echo '<br><span class="successFail">Fehler beim Starten von CRON-Prozess.</span>';
                            }
                        }

                    }
                    else
                    {
                        echo '<br><span class="failureHint">Fehler beim Entpacken der Restore-Datei!</span>';

                        if ($debugFlag === true)
                        {
                            $tt        = "Fehler beim Entpacken der Restore-Datei!\n";
                            $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                            file_put_contents('../log/debug_restore.log', $errorText, FILE_APPEND);
                        }
                    }
                }
            }
            else
            {
                echo '<br><span class="failureHint">Dies ist kein gültiges Meshdash-SQL Restore-Datei!</span>';

                if ($debugFlag === true)
                {
                    $tt        = "Dies ist kein gültiges Meshdash-SQL Restore-Datei!\n";
                    $errorText = date('Y-m-d H:i:s') . ' result:' . $tt . "\n";

                    file_put_contents('../log/debug_restore.log', $errorText, FILE_APPEND);
                }
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

            file_put_contents('../log/debug_restore.log', $errorText, FILE_APPEND);
        }
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
            echo '<br><span class="successHint">Backup-File ' . $deleteFileImage . ' erfolgreich gelöscht.</span>';
        }
        else
        {
            echo '<br><span class="failureHint">Fehler beim Löschen von Backup-File ' . $deleteFileImage . '</span>';
        }
    }
    else
    {
        echo '<br><span class="failureHint">Das Backup-File: ' . $deleteFileImage . ' wurde nicht im Backup-Verzeichnis gefunden.</span>';
    }
}

ob_end_flush(); // Ausgabe abschließen
echo '<form id="frmConfigRestore" action="' . $_SERVER['REQUEST_URI'] . '" method="post" enctype="multipart/form-data">';
echo '<input type="hidden" name="sendData" id="sendData" value="0" />';
echo '<input type="hidden" name="deleteFileImage" id="deleteFileImage" value="" />';
echo '<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />';

echo '<table>';
echo '<tr>';
if ($sendData != 1)
{
    echo '<td ><label for="restoreFile">Wähle das Restore (Zip-Datei):&nbsp;</label></td>';
    echo '<td><input type="file" name="restoreFile" id="restoreFile" required></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><input type="button" class="btnConfigRestore" id="btnConfigRestore" value="Restore Hochladen"/></td>';
}
else
{
    echo '<td colspan="2">&nbsp;</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td ><label for="btnConfigRestoreReload" class="reloadMsg">MeshDash-Seite jetzt neu laden:&nbsp;</label></td>';
    echo '<td><input type="button" id="btnConfigRestoreReload" value="MeshDash-Seite neu laden"/></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2">&nbsp;</td>';
}
echo '</tr>';
echo '</table>';

echo "<br><br>";

showBackups();

echo '</form>';

echo '<div id="pageLoading" class="pageLoadingSub"></div>';

echo '</body>';
echo '</html>';