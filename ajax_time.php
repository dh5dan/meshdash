<?php

date_default_timezone_set('Europe/Berlin');
echo json_encode(['time' => date("Y-m-d H:i:s")]);