<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

echo 'Current script owner: ' . get_current_user();
define('SERIAL_DEVICE', '/dev/ttyACM0');

function readMessage($fp) {
 $header = fread($fp, 200);
 return $header;
}

function sendMessage( $fp, $str ) {

 fwrite($fp, $str );
 $ret = readMessage($fp);
 return true;
}

$fp = fopen(SERIAL_DEVICE, "w+");
if( !$fp) {
 die("can\'t open " . SERIAL_DEVICE);
}

while( true ) {
 $msg = readline('> ');
 if( $msg == 'exit' ) {
  break;
 }

 echo "* sending… ";

 if( sendMessage($fp, $msg) ) {
  echo "OK\n\n";
 }
 else {
  echo "FAILED!!!\n\n";
 }
 
 echo readMessage($fp) . "\n";
}


fclose($fp);

