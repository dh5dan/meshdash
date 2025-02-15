#!/bin/bash

while true; do

lynx -dump http://localhost/5d/message.php >/dev/null
lynx -dump http://localhost/message.php >/dev/null
sleep 10

done
