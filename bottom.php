<?php
echo '<!DOCTYPE html>';
echo '<html lang="de">';
echo '<meta charset="UTF-8">';
echo '<head><title>Bottom</title>';
echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
echo '<link rel="stylesheet" href="css/bottom.css?' . microtime() . '">';

echo '<script type="text/javascript" src="jquery/jquery.min.js"></script>';
echo '<script type="text/javascript" src="jquery/jquery-ui.js"></script>';
echo '<link rel="stylesheet" href="jquery/jquery-ui.css">';
echo '<link rel="stylesheet" href="jquery/css/jq_custom.css">';

#Prevnts UTF8 Errors on misconfigured php.ini
ini_set( 'default_charset', 'UTF-8' );

echo '</head>';
echo '<body>';

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';
require_once 'include/func_php_core.php';
require_once 'include/func_php_bottom.php';
require_once 'include/func_js_bottom.php';

$errMsg  = utf8_decode($_REQUEST['errMsg'] ?? '');
$msgText = $_REQUEST['msgText'] ?? '';
$dm      = $_REQUEST['dm'] ?? '';
$loraIP  = getParamData('loraIp');
$group   = $_REQUEST['group'] ?? '';

$clickOnCall             = getParamData('clickOnCall'); // 0=call->DM, 1= qrz, 2=@call
$clickOnCallDMActiveCss  = $clickOnCall == 0 ? 'active' : '';
$clickOnCallQrzActiveCss = $clickOnCall == 1 ? 'active' : '';
$clickOnCallMsgActiveCss = $clickOnCall == 2 ? 'active' : '';

# Wenn Gruppe nicht DM und Gruppe eine Gruppennummer ist dann wieder Gruppennummer eintragen
$dm = $group != $dm && $group > 0 ? $group : $dm;

if ($errMsg != '')
{
    echo '<span class="bottomErrorMsg">' . $errMsg . '</span>';
}
else
{
    echo '<br>';
}

echo '<form action="send_msg.php" method="POST">';
echo '<input type="hidden" id="groupId" name="group" value="' . $group . '">';
    echo '<div class="bottomDmMsgLine">';

     echo '<span class="bottomSize16">DM:</span>';
     echo'<input class="bottomInputDm" id="bottomDm" type="text" value="' . $dm . '" size="20" id="dm" name="dm" />';
     echo '<span class="bottomSize16">MSG:</span>';
     echo'<input class="bottomInputMsg" type="text" value="' . $msgText . '" id="msgText" name="msgText" required />';

     echo '<select id="entitySelect">';
     selectEmoticons();
     echo '</select>';

    echo '</div>';

echo'<div class="bottomCounterContainer">';
echo '<p id="byteCount">0 / 149 Byte</p>';
echo '</div>';

    echo '<div class="bottomSubmitLine">';
    echo '<button class="bottomInputSubmit" type="submit">Absenden</button>';

            echo '<div class="bottomIconContainer">';
            echo '<img class="bottomImgIcons ' . $clickOnCallDMActiveCss . '" src=" image/call_dm.png" >';
            echo '<img class="bottomImgIcons ' . $clickOnCallQrzActiveCss . '" src=" image/call_qrz.png" >';
            echo '<img class="bottomImgIcons ' . $clickOnCallMsgActiveCss . '" src=" image/call_at.png" >';
            echo '</div>';

    echo '</div>';

    echo'<div class="bottomStatusContainer">';
        echo '<div id="posStatus" class="bottomStatus"></div>';
        echo '<div id="noTimeSync" class="bottomStatus"></div>';
        echo '<div id="LoraIP" class="bottomStatus">IP: ' . $loraIP . '</div>';
    echo '</div>';
echo '</form>';

echo '</body>';
echo '</html>';