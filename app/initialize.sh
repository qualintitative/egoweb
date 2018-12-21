#!/bin/sh
mkdir -p assets
chmod 777 assets
mkdir -p ${PWD}/protected/runtime
chmod 777 ${PWD}/protected/runtime
cp -n ${PWD}/protected/config/main.php.example ${PWD}/protected/config/main.php
cp -n ${PWD}/protected/config/console.php.example ${PWD}/protected/config/console.php
