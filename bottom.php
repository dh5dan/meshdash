<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<meta charset="UTF-8">';
echo '<head><title>Bottom</title>';
echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
echo '<link rel="stylesheet" href="css/bottom.css?' . microtime() . '">';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '</head>';
echo '<body>';

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

$errMsg  = utf8_decode($_REQUEST['errMsg'] ?? '');
$msgText = $_REQUEST['msgText'] ?? '';
$dm      = $_REQUEST['dm'] ?? '';
$loraIP  = getParamData('loraIp');

if ($errMsg != '')
{
    echo '<span style="color:white; background:red">' . $errMsg . '</span>';
    echo "<br>";
}

echo '</head>';
echo '<body>';
 echo '<br>';
    echo '<form action="send_msg.php" method="POST">';
        echo '<span class="bottomSize16">DM:</span> <input class="bottomInputDm" id="bottomDm" type="text" value="' . $dm . '" size="20" id="dm" name="dm">';
        echo str_repeat('&nbsp;', 10);
        echo '<span class="bottomSize16">MSG:</span> <input class="bottomInputMsg" type="text" value="' . $msgText . '" id="msgText" name="msgText" required>';
        echo '<br><br>';
        echo '<button class="bottomInputSubmit" type="submit">Message Absenden</button>';

        echo'<div class="statusContainer">';
            echo '<div id="posStatus" class="bottomStatus"></div>';
            echo '<div id="noTimeSync" class="bottomStatus"></div>';
            echo '<div id="LoraIP" class="bottomStatus">Lora-IP:' . $loraIP . '</div>';
        echo '</div>';
    echo '</form>';

echo '</body>';
echo '</html>';