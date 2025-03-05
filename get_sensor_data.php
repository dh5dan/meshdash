<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';
require_once 'include/func_php_sensor_data.php';
require_once 'include/func_php_lora_info.php';

$loraIp           = getParamData('loraIp');
$resGetSensorData = getSensorData($loraIp, 1);

$sensor =$_REQUEST['sensor'];

if ($sensor == "temp")
{
    echo "<br>temp";
    echo "<pre>";
    print_r($resGetSensorData);
    echo "</pre>";
}

if ($sensor == "ina226")
{
    echo "<br>ina226";
    #check temp
}
