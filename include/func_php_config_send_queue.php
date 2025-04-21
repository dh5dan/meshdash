<?php
function saveSendQueueSettings(): bool
{
    $execDir              = 'log';
    $basename             = pathinfo(getcwd())['basename'];
    $intervalFilenameSub  = '../' . $execDir . '/' . CRON_CONF_FILE;
    $intervalFilenameRoot = $execDir . '/' . CRON_CONF_FILE;
    $intervalFilename     = $basename == 'menu' ? $intervalFilenameSub : $intervalFilenameRoot;

    $sendQueueInterval = trim($_REQUEST['sendQueueInterval']) ?? 20;
    setParamData('sendQueueInterval', $sendQueueInterval);

    $sendQueueMode = $_REQUEST['sendQueueMode'] ?? 0;
    setParamData('sendQueueMode', $sendQueueMode);

    #Hintergrundprozess
    $paramBgProcess['task'] = 'cron';

    if ($sendQueueMode === 0)
    {
        stopBgProcess($paramBgProcess);
    }
    else
    {
        startBgProcess($paramBgProcess);
    }

    if ($sendQueueInterval != 20)
    {
        file_put_contents($intervalFilename, $sendQueueInterval);
    }
    else
    {
        if (file_exists($intervalFilename))
        {
            unlink($intervalFilename);
        }
    }

    return true;
}

