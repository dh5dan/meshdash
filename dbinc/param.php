<?php
const VERSION = '1.10.32';
date_default_timezone_set('Europe/Berlin');

const TRIGGER_LINK_SEND_QUEUE = 'http://localhost/5d/send_queue.php';
const CRON_PID_FILE = 'cron_loop.pid';
const CRON_CONF_FILE = 'cron_interval.conf';
const CRON_STOP_FILE = 'cron_stop';
const CRON_PROC_FILE = 'cron_loop.php';
const UPD_PID_FILE = 'udp.pid';
const UPD_STOP_FILE = 'udp_stop';
const UDP_PROC_FILE = 'udp_receiver.php';