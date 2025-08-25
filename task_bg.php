<?php

$taskFile = trim($_REQUEST['taskFile']);
$execDir  = trim($_REQUEST['execDir']);

$debugFlag = false;

#Muss hier stehen da chdir sonst das BaseDir verschiebt
if ($debugFlag === true)
{
    echo "<br>taskFile:$taskFile";
    $errorText = date('Y-m-d H:i:s') . " taskFile:$taskFile execDir: $execDir suffix:" . substr($taskFile,-3) . "<--\n";
    file_put_contents('log/debug.log', $errorText, FILE_APPEND);
}

#Execute Skript in SubDir
if ($execDir != '')
{
    chdir($execDir);
}

if (substr($taskFile,-3) == 'cmd' || substr($taskFile,-3) == 'bat')
{
    #exec('start /B ' . $taskFile);

    //Verhindert Timeout unter Windows
    pclose(popen('start /B ' . escapeshellarg($taskFile), 'r'));
}

if (substr($taskFile,-3) == 'php')
{
    #Starte Background task für UDP-Receive
    #in Windows. Wichtig! start /B nutzen!
    #exec('start /B php -f ' . $taskFile);

    //Verhindert Timeout unter Windows
    pclose(popen('start /B php -f ' . escapeshellarg($taskFile), 'r'));
}
