<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

include 'serial.php';
echo "init\n";
_init();
_send('trigger-servo');


for ($i=0;$i<20;$i++) {
echo $i._read()."\n";
sleep(1);
}
_close();
echo "close\n";
