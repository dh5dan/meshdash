<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';
require_once 'include/func_php_sensor_data.php';
require_once 'include/func_php_lora_info.php';

$loraIp           = getParamData('loraIp');
$resGetSensorData = getSensorData($loraIp, 1);

echo "<br>getSensorData:<br>";
echo "<pre>";
print_r($resGetSensorData);
echo "</pre>";

checkSensor($resGetSensorData);

