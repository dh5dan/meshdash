<?php

#Starte Background task für UDP-Receive
#in Windows. Wichtig! start /B nutzen!

$taskFile = trim($_REQUEST['taskFile']);

exec('start /B php -f ' .$taskFile);
