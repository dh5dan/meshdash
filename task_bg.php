<?php

require_once 'include/func_php_core.php';

$taskFile = trim($_REQUEST['taskFile']);
$execDir  = trim($_REQUEST['execDir']);

$osIsWindows = chkOsIsWindows();
$debugFlag = false;

#Muss hier stehen da chdir sonst das BaseDir verschiebt
if ($debugFlag === true)
{
    echo "<br>taskFile:$taskFile";
    echo "<br>execDir:$execDir";
    $errorText = date('Y-m-d H:i:s') . " taskFile:$taskFile execDir:$execDir suffix:" . substr($taskFile,-3) . "<--\n";
    file_put_contents('log/task_bg_debug.log', $errorText, FILE_APPEND);
}

#Execute Skript in SubDir
if ($execDir != '')
{
    chdir($execDir);
}

if (substr($taskFile,-3) == 'cmd' || substr($taskFile,-3) == 'bat')
{
    //Verhindert Timeout unter Windows. Shoot an forget.
     pclose(popen('start /B cmd /c ' . escapeshellarg($taskFile), 'r'));
}

if (substr($taskFile,-3) == 'php')
{
    if ($osIsWindows === true)
    {
        #Starte Background task für UDP-Receive
        #in Windows. Wichtig! start /B nutzen!
        exec('start /B php -f ' . $taskFile);
    }
    else
    {
        //Verhindert Timeout unter Windows.
        //Funktioniert aber nicht unter windows
        pclose(popen('start /B php -f ' . escapeshellarg($taskFile), 'r'));
    }
}
