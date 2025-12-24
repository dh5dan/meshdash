<?php
require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';

if (!file_exists('database/parameter.db'))
{
    exit();
}

$selTzName = getParamData('timeZone') ?? 'Europe/Berlin'; // ZeitZone
date_default_timezone_set($selTzName);

#date_default_timezone_set('Europe/Berlin');
echo json_encode(['time' => date("Y-m-d H:i:s")]);