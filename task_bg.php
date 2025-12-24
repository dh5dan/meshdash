<?php
# Wichtig!
# Gewährleistet, das das Skript immer aus dem Verzeichnis ausgeführt ist, wo es liegt.
# Alle relativen Pfade bleiben somit erhalten, auch wenn es aus dem SubMenü aufgerufen wird.
chdir(__DIR__);

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

$taskFile  = trim($_REQUEST['taskFile']);
$execDir   = trim($_REQUEST['execDir']);
$logDir    = 'log/';
$debugFile = $logDir . 'debug_task_bg.log';

$osIsWindows = chkOsIsWindows();
$debugFlag   = false;

#################################################################################
###################### Background Task-Starter für Windows ######################
#################################################################################
$winPhpCliPath = (string) (getParamData('winPhpCliPath') ?? ''); // Nur für Windows. Pfad zur php.exe CLI

#Muss hier stehen da chdir sonst das BaseDir verschiebt
if ($debugFlag === true)
{
    echo "<br>taskFile:$taskFile";
    echo "<br>execDir:$execDir";
    $errorText = date('Y-m-d H:i:s') . " taskFile:$taskFile execDir:$execDir suffix:" . substr($taskFile,-3) . "<--\n";
    file_put_contents($debugFile, $errorText, FILE_APPEND);

    $errorText = date('Y-m-d H:i:s') . " winPhpCliPath:$winPhpCliPath<--\n";
    file_put_contents($debugFile, $errorText, FILE_APPEND);
}

#Execute Skript in SubDir
if ($execDir != '')
{
    chdir($execDir);
}

if (substr($taskFile,-3) == 'cmd' || substr($taskFile,-3) == 'bat')
{
    if ($debugFlag === true)
    {
        $errorText = date('Y-m-d H:i:s') . " Führe CMD aus: pclose(popen('start /B cmd /c ' . escapeshellarg($taskFile), 'r'));" . "<--\n";
        file_put_contents($debugFile, $errorText, FILE_APPEND);
    }

    //Verhindert Timeout unter Windows. Shoot an forget.
     pclose(popen('start /B cmd /c ' . escapeshellarg($taskFile), 'r'));
}

if (substr($taskFile,-3) == 'php')
{
    $winPhpCliPath = $winPhpCliPath == '' ? 'php' : $winPhpCliPath;

    if ($debugFlag === true)
    {
        $errorText = date('Y-m-d H:i:s') . " In php Execute Zweig" . "<--\n";
        file_put_contents($debugFile, $errorText, FILE_APPEND);
    }

    if ($osIsWindows === true)
    {
        if ($debugFlag === true)
        {
            $errorText = date('Y-m-d H:i:s') . " Führe CMD aus: exec('start /B $winPhpCliPath -f ' . $taskFile);" . "<--\n";
            file_put_contents($debugFile, $errorText, FILE_APPEND);
        }

        #Starte individuellen Background task
        #in Windows. Wichtig! start /B nutzen!

        // macht probleme bei Sensordata (log max ExecuteTime) und wird nicht immer sauber detached.
        #exec('start /B ' . $winPhpCliPath . ' -f ' . $taskFile);

        # sauberer Detach, Parent wird nie blockiert, egal ob der Task etwas ausgibt oder nicht.
        # Warum DAS funktioniert:
        # cmd /c beendet sich sofort
        # start läuft in eigenem Kontext
        # "" = Fenstertitel (sonst Chaos)
        # > NUL 2>&1 → keine Handle-Vererbung
        # exec() kehrt sofort zurück

        exec(
            'cmd /c start "" /B ' .
            escapeshellarg($winPhpCliPath) .
            ' -f ' . escapeshellarg($taskFile) .
            ' > NUL 2>&1'
        );
    }
    else
    {
        if ($debugFlag === true)
        {
            $errorText = date('Y-m-d H:i:s') . " Führe LINUX-CMD aus: start /B php -f  ' . $taskFile);" . "<--\n";
            file_put_contents($debugFile, $errorText, FILE_APPEND);

            $errorText = date('Y-m-d H:i:s') . " Aktueller SkriptPfad:  ' . __FILE__);" . "<--\n";
            file_put_contents($debugFile, $errorText, FILE_APPEND);
        }

        //Verhindert Timeout unter Windows.
        //Funktioniert aber nicht unter windows
        pclose(popen('start /B php -f ' . escapeshellarg($taskFile), 'r'));
    }
}
