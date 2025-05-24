<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';
require_once 'include/func_php_sensor_data.php';
require_once 'include/func_php_lora_info.php';

$loraIp           = getParamData('loraIp');

#Check new GUI
if (getParamData('isNewMeshGui') == 1)
{
    $resGetSensorData = getSensorData2($loraIp, 1);
}
else
{
    $resGetSensorData = getSensorData($loraIp, 1);
}

echo "<br>getSensorData:<br>";
echo "<pre>";
print_r($resGetSensorData);
echo "</pre>";

checkSensor($resGetSensorData);

