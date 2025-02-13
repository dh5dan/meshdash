<?php
date_default_timezone_set('Europe/Berlin');
#echo date("d.m.Y H:i:s");
echo json_encode(['time' => date("Y-m-d H:i:s")]);