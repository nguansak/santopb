<?php 
error_reporting(E_ALL); 
ini_set("display_errors", true); 
set_time_limit(60*60*2); // 2h
@apache_setenv('no-gzip', 1);
@ini_set('output_buffering',0);
@ini_set('zlib.output_compression',0);
@ini_set('implicit_flush',true);

@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
@ob_end_flush();
ob_implicit_flush(true);

date_default_timezone_set("Asia/Bangkok");


include "serial.php";
include "./lib/lib.inc.php";

$cmd = @$_GET['precmd'];
if ($cmd) {
	include "run_command.php";
}

include "header.php";
$arduino = @$_GET['arduino'];
if ($arduino) {
	include "run_arduino.php";
}

$cmd = @$_GET['cmd'];
if ($cmd) {
	include "run_command.php";
}
  
if (isset($_GET['setting'])) {
	include "setting.php";
}

$rfid_status = @$_GET['rfid_status'];
if ($rfid_status) {
	
	include "run_rfid_status.php";
}

if (isset($_GET['file_transfers'])) {
	include "file_transfers.php";
}


include "footer.php";



/* 


 sudo /bin/mount /dev/sda1 /mnt/usbdrive

 sudo rm -Rf /mnt/usbdrive/DCIM/*
 sudo /bin/umount /dev/sda1 /mnt/usbdrive



 If you know what you are doing, look at the file /etc/apache2/envvars :

You can customize these variables

export APACHE_RUN_USER=root
export APACHE_RUN_GROUP=root

*/