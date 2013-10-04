<?php

$timestamp = "";
$time_start = microtime(true);

function shell2($cmd) {
	$output = shell_exec($cmd);
	echo "$output"; 
}

/*
function _exec($cmd, &$out = null)
{
	$desc = array(
		1 => array("pipe", "w"),
		2 => array("pipe", "w")
	);

	$proc = proc_open($cmd, $desc, $pipes);

	$ret = stream_get_contents($pipes[1]);
	$err = stream_get_contents($pipes[2]);

	fclose($pipes[1]);
	fclose($pipes[2]);

	$retVal = proc_close($proc);

	if (func_num_args() == 2) $out = array($ret, $err);
	return $retVal;
} */



function shell($cmd) {
 
	$descriptorspec = array(
	   0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
	   1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
	   2 => array("pipe", "w")    // stderr is a pipe that the child will write to
	);
	flush();
	$process = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
	echo "<pre>";
	if (is_resource($process)) {
		while ($s = fgets($pipes[1])) {
			print $s;
			flush();
		}
	}
}

function restart() {
	shell("sudo shutdown -r 0");
	shell("ping 127.0.0.1");
}

function shutdown() {
	shell("sudo shutdown -h 0");
	shell("ping 127.0.0.1");
}

function camera_download() {
	global $USBDRIVE_DETECT_TIME, $temp_picture_folder;
	$userid = GetValue("userid");
	$timeStamp = GetValue("timestamp");

	include_once "download_file_from_usb.php";
	download_file_from_usb();

	//shell("python download_file_from_usb.py {$userid}/{$timestamp}");
}

function file_transfers() {
	$userid = GetValue("userid");
	$timeStamp = GetValue("timestamp");

	include "file_transfers.php";

	//shell("python download_file_from_usb.py {$userid}/{$timestamp}");
}


function testflush() {
	for ($i=0;$i<10;$i++) {
		echo "$i\n";
		//flush();
		sleep(1);
	}
}

function whoami() {
	_exec("whoami");
}
 
function lsusb() {
	_sudo("lsusb");
}

function checkin() {
	set();

	send_rfid_status(SIGNAL_RED);

	$seq = rand(0,99);
	$seq = str_pad($seq, 2, "0", STR_PAD_LEFT); 

	$timestamp = date("ymdHis".$seq) ;
	SetValue("timestamp",$timestamp);

	writeln( "timestamp = $timestamp \n");
	$checkin_wait = GetValue("checkin_wait");

	writeln( "checkin_wait {$checkin_wait} seconds");
	sleep($checkin_wait);

	run_main();

	send_rfid_status(SIGNAL_OFF);

}

function run_main() {

	$machine_code = GetValue("machine_code");
	$wait = GetValue("wait");
	$hold = GetValue("hold");
	$wait_download_photo = GetValue("wait_download_photo");
	$sensor = GetValue("sensor");
  
	run_main_new($wait, $hold, $wait_download_photo, $sensor);
	//$func = "run_main_{$machine_code}";

	//$func();

}


function run_main_new($wait=1, $hold=1, $wait_download_photo=3, $sensor="A") {
	writeln("run_main");

	if (_init()) {
		
		_send("normal-mode");
		sleep(1);
		_send("trigger-servo");
		//sleep(3);
		//_send("trigger-servo");
		_send("auto-capture:{$sensor},{$wait},{$hold}");

		if (wait_auto_capture()) {

			sleep($wait_download_photo);
			_send("usb-mode");

			camera_download();

			sleep(3);

			_send("normal-mode");

			file_transfers();
		
		}

		_close();
	}
	writeln("end.");
}


function capture() {
	global $timestamp;
	set();

	$seq = rand(0,99);
	$seq = str_pad($seq, 2, "0", STR_PAD_LEFT); 

	$timestamp = date("ymdHis".$seq) ;
	SetValue("timestamp",$timestamp);
	writeln("timestamp = $timestamp");

	SetValue("timestamp",$timestamp);


	write("run_main");

	if (_init()) {
		_send("normal-mode");
		sleep(1);
		_send("trigger-servo");
		sleep(3);

		_send("trigger-servo");

		sleep(3);
		_send("usb-mode");

		camera_download();

		sleep(3);

		_send("normal-mode");
		_close();

		file_transfers();
	}
	
	writeln( "end.");
}


function mount() {
	_init();

	$n = 10;
	_send("usb-mode");
	sleep(2);

	echo  "> Auto download photo\n";
	if (auto_mount_usbdrive($n)=="ok") {
		print "> done\n";
	} else 
		print "> fail\n";
	
	_close();
	echo "end.";

}

function unmount() {
  global $mount_point;
	_init();
  
  	$n = 10;

	  print "> Auto mount usbdrive\n";
	  retrive_mount_point($n);


	echo  "> Auto download photo\n";
	if (auto_mount_usbdrive($n)=="ok") {
		unmount_usbdrive();
		print "> done\n";
	} else 
		print "> fail\n";

	sleep(2);
	_send("normal-mode");
	
	_close();
	echo "end.";

}


function testsu() {
	_exec("whoami");
	_exec("ls /mnt");
	_sudo('mkdir /mnt/xx');
	_exec("ls /mnt");
	_sudoo('rm -r /mnt/xx');
	_exec("ls /mnt");

}

function set() {
	echo "---- save -----\n";
	foreach( $_GET as $key => $value ) {
		SetValue($key, $value) ;
		echo "$key = $value \n";
	}
}

function post_process() {
	_init();

	_send("usb-mode");
	sleep(2);

	camera_download();

	_send("normal-mode");

	file_transfers();
	
	_close();
	echo "end.";
}

function auto_capture() {
	_init();

	_send("normal-mode");
	sleep(2);


	_send("auto-capture:A,1,1");

	wait_auto_capture();

	_close();
	echo "end.";

}

function ensure_usb_normal_mode() {

	write("ensure_usb_normal_mode\r\n");

	return "";

	if (_init()) {

		_send("normal-mode");
	
		_close();
	}

}

if (function_exists($cmd)) {
	$cmd();
}
