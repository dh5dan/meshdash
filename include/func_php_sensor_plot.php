<?php

function selectSensorType()
{
    $allowedSensors = [
        'Temp IN' =>'temp',
        'Temp Out' =>'tout',
        'Humidity' => 'hum',
        'Quality of Field Elevation (Qfe)' => 'qfe',
        'Quality of Nautical Height (Qnh)' => 'qnh',
        'Altitude Above Sea Level' => 'altAsl',
        'Bme 280'=> 'bme280',
        'Bme 680' => 'bme680',
        'Mcu 811' => 'mcu811',
        'Lsp 33' => 'lsp33',
        'OneWire' => 'oneWire',
        'gas' => 'gas',
        'eCo2' => 'eCo2',
        'ina226vBus' => 'ina226vBus',
        'ina226vShunt' => 'ina226vShunt',
        'ina226vCurrent' => 'ina226vCurrent',
        'ina226vPower' => 'ina226vPower',
    ];

    foreach ($allowedSensors as $key => $value)
    {
        echo '<option value="' . $value . '">' . $key . '</option>';
    }
}