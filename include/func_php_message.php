<?php

function execScriptCurl($keywordCmd): bool
{
    $osIssWindows = chkOsIssWindows();

    if ($osIssWindows === true)
    {
        #Unter Windows mit Curl Starten
        callWindowsBackgroundTask($keywordCmd, 'execute');
    }
    else
    {
        if (substr($keywordCmd,-2) == 'sh')
        {
            exec('cd execute && nohup ./' . $keywordCmd . ' >/dev/null 2>&1 &');
        }

        if (substr($keywordCmd,-3) == 'php')
        {
            exec('cd execute && nohup php ./' . $keywordCmd . ' >/dev/null 2>&1 &');
        }
    }

    return true;
}
