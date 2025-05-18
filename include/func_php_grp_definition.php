<?php

function saveGroupsSettings(): bool
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/groups.db';
    $dbFilenameRoot = 'database/groups.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;

    $updateArray = array();

    $updateArray[1]['number'] = $_REQUEST['groupNumber1'] ?? 0;
    $updateArray[2]['number'] = $_REQUEST['groupNumber2'] ?? 0;
    $updateArray[3]['number'] = $_REQUEST['groupNumber3'] ?? 0;
    $updateArray[4]['number'] = $_REQUEST['groupNumber4'] ?? 0;
    $updateArray[5]['number'] = $_REQUEST['groupNumber5'] ?? 0;
    $updateArray[6]['number'] = $_REQUEST['groupNumber6'] ?? 0;

    $updateArray[-1]['number'] = 0; // Kein Filter
    $updateArray[-2]['number'] = 0; // Own Call
    $updateArray[-3]['number'] = -3; // Pos Filter
    $updateArray[-4]['number'] = -4; // Cet Filter

    $updateArray[1]['enabled'] = $_REQUEST['groupNumber1Enabled'] ?? 0;
    $updateArray[2]['enabled'] = $_REQUEST['groupNumber2Enabled'] ?? 0;
    $updateArray[3]['enabled'] = $_REQUEST['groupNumber3Enabled'] ?? 0;
    $updateArray[4]['enabled'] = $_REQUEST['groupNumber4Enabled'] ?? 0;
    $updateArray[5]['enabled'] = $_REQUEST['groupNumber5Enabled'] ?? 0;
    $updateArray[6]['enabled'] = $_REQUEST['groupNumber6Enabled'] ?? 0;

    $updateArray[-1]['enabled'] = 0;
    $updateArray[-2]['enabled'] = 0;
    $updateArray[-3]['enabled'] = $_REQUEST['groupPosEnabled'] ?? 0;
    $updateArray[-4]['enabled'] = $_REQUEST['groupCetEnabled'] ?? 0;

    $updateArray[1]['sound'] = $_REQUEST['groupSound1Enabled'] ?? 0;
    $updateArray[2]['sound'] = $_REQUEST['groupSound2Enabled'] ?? 0;
    $updateArray[3]['sound'] = $_REQUEST['groupSound3Enabled'] ?? 0;
    $updateArray[4]['sound'] = $_REQUEST['groupSound4Enabled'] ?? 0;
    $updateArray[5]['sound'] = $_REQUEST['groupSound5Enabled'] ?? 0;
    $updateArray[6]['sound'] = $_REQUEST['groupSound6Enabled'] ?? 0;

    $updateArray[-1]['sound'] = $_REQUEST['groupSoundNoFilterEnabled'] ?? 0;
    $updateArray[-2]['sound'] = $_REQUEST['groupSoundOwnCallEnabled'] ?? 0;
    $updateArray[-3]['sound'] = $_REQUEST['groupSoundPosEnabled'] ?? 0;
    $updateArray[-4]['sound'] = $_REQUEST['groupSoundCetEnabled'] ?? 0;

    $groupSoundFile = $_REQUEST['groupSoundFile'] ?? 'new_message.wav';
    setParamData('groupSoundFile', $groupSoundFile, 'txt');

    $db = new SQLite3($dbFilename);
    $db->exec('PRAGMA synchronous = NORMAL;');

    for ($groupId = -4; $groupId <= 6; $groupId++)
    {
        if ($groupId == 0)
        {
            continue;
        }

        $groupNumber  = $updateArray[$groupId]['number'];
        $groupEnabled = $updateArray[$groupId]['enabled'];
        $groupSound   = $updateArray[$groupId]['sound'];

        $db->exec("
                        REPLACE INTO groups (groupId, groupNumber, groupEnabled, groupSound)
                        VALUES (
                                '$groupId',
                                '$groupNumber',
                                '$groupEnabled',
                                '$groupSound'
                        );
                    "
        );

        if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
        {
            echo "<br>setParamData";
            echo "<br>ErrMsg:" . $db->lastErrorMsg();
            echo "<br>ErrNum:" . $db->lastErrorCode();
        }
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    return true;
}

function getGroupParameter(int $mode = 0)
{
    #Ermitte Aufrufpfad um Datenbankpfad korrekt zu setzten
    $basename       = pathinfo(getcwd())['basename'];
    $dbFilenameSub  = '../database/groups.db';
    $dbFilenameRoot = 'database/groups.db';
    $dbFilename     = $basename == 'menu' ? $dbFilenameSub : $dbFilenameRoot;
    $returnValue    = array();

    $db  = new SQLite3($dbFilename);
    $db->busyTimeout(5000); // warte wenn busy in millisekunden
    $res = $db->query("
                        SELECT * 
                          FROM groups
                      ORDER BY groupId;
                    ");
    $rows = 0;
    while ($dsData = $res->fetchArray(SQLITE3_ASSOC))
    {
        $rows++;

        if ($mode == 0)
        {
            $groupId                               = $dsData['groupId'] ?? 0;
            $returnValue[$groupId]['groupNumber']  = $dsData['groupNumber'] ?? 0;
            $returnValue[$groupId]['groupEnabled'] = $dsData['groupEnabled'] ?? 0;
            $returnValue[$groupId]['groupSound']   = $dsData['groupSound'] ?? 0;
        }
        else if ( $dsData['groupEnabled'] ?? 0 == 1)
        {
            $groupId                        = $dsData['groupId'] ?? 0;
            $groupNumber                    = $dsData['groupNumber'] ?? 0;
            $returnValue[$groupId]['id']    = $groupNumber;

            if (isMobile() === false)
            {
                $returnValue[$groupId]['label'] = 'Gruppe ' . $groupNumber;
            }
            else
            {
                $returnValue[$groupId]['label'] = $groupNumber;
            }

            switch ($groupId)
            {
                case -3:
                    $returnValue[$groupId]['label'] = 'POS';
                    break;
                case -4:
                    $returnValue[$groupId]['label'] = 'CET';
            }
        }

        if ($db->lastErrorMsg() > 0 && $db->lastErrorMsg() < 100)
        {
            echo "<br>getParamData";
            echo "<br>ErrMsg:" . $db->lastErrorMsg();
            echo "<br>ErrNum:" . $db->lastErrorCode();
        }
    }

    #Close and write Back WAL
    $db->close();
    unset($db);

    if ($rows == 0)
    {
        return false;
    }

    return $returnValue;
}

function getGroupTabsJson()
{
    $tabs     = array();
    $callSign = getParamData('callSign');

    #Predefined fixed Tabs
    $tabs[0]['id'] = -2;
    $tabs[0]['label'] = $callSign;
    $tabs[1]['id'] = -1;
    $tabs[1]['label'] = 'Kein Filter';

    #Userdefined Tabs
    $resGetGroupParameter = getGroupParameter(1);

    if ($resGetGroupParameter !== false)
    {
        $tabs = array_merge($tabs, $resGetGroupParameter);
    }

    return json_encode($tabs);
}

