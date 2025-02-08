<?php

function execScriptCurl($keyword1Cmd)
{
    $osIssWindows = chkOsIssWindows();

        if($osIssWindows === true)
        {
            #Unter Windows mit Curl Starten
            callBackgroundTask('task_bg.php');
        }
        else
        {
            #Unter Linux direkt starten
            #exec('nohup php test_receiver.php >/dev/null 2>&1 &');
            exec('nohup php udp_receiver.php >/dev/null 2>&1 &');
        }
}
