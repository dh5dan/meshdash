<?php
function saveSendQueueSettings(): bool
{
    $execDir              = 'log';
    $basename             = pathinfo(getcwd())['basename'];
    $intervalFilenameSub  = '../' . $execDir . '/cron_interval.conf';
    $intervalFilenameRoot = $execDir . '/cron_interval.conf';
    $intervalFilename     = $basename == 'menu' ? $intervalFilenameSub : $intervalFilenameRoot;

    $sendQueueInterval         = trim($_REQUEST['sendQueueInterval']) ?? 20;
    setParamData('sendQueueInterval', $sendQueueInterval);

    $sendQueueMode         = $_REQUEST['sendQueueMode'] ?? 0;
    setParamData('sendQueueMode', $sendQueueMode);

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

    #Trigger CronLoop Once for Windows via curl
    triggerCronLoop();

    return true;
}

