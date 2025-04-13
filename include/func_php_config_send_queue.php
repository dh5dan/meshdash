<?php
function saveSendQueueSettings(): bool
{
    $basename             = pathinfo(getcwd())['basename'];
    $intervalFilenameSub  = '../log/cron_interval.conf';
    $intervalFilenameRoot = 'log/cron_interval.conf';
    $intervalFilename     = $basename == 'menu' ? $intervalFilenameSub : $intervalFilenameRoot;

    $sendQueueInterval         = $_REQUEST['sendQueueInterval'] ?? 30;
    setParamData('sendQueueInterval', $sendQueueInterval);

    if ($sendQueueInterval != 30)
    {
        file_put_contents($intervalFilename, $sendQueueInterval);
    }
    else
    {
        unlink($intervalFilename);
    }

    #Trigger CronLoop Once for Windows via curl
    triggerCronLoop();

    return true;
}

