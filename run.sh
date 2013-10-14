#!/bin/bash

cd /var/www/
sudo rm /var/www/command.run

while :
do
   sudo -u linaro php /var/www/cli_command.php
   sleep 1
done

