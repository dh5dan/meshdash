#!/usr/bin/env python
#coding: utf8

import time
import RPi.GPIO as GPIO

# Zählweise der Pins festlegen
GPIO.setmode(GPIO.BCM)

# Pin 17 (GPIO 17) als Ausgang festlegen
GPIO.setup(17, GPIO.OUT)

# Pin 17 (GPIO 17) auf Low setzen
GPIO.output(17, GPIO.LOW)
