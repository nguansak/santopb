<?php

$timestamp = "";

$run_main_state = "";

$last_camera_active_time = 0;


include "download_file_from_usb.php";

include "file_transfers.php";

/*
function shell2($cmd) {
	$output = shell_exec($cmd);
	echo "$output"; 
}

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

	$return_result = false; 

	send_rfid_status(SIGNAL_RED);

	$seq = rand(0,99);
	$seq = str_pad($seq, 2, "0", STR_PAD_LEFT); 

	$timestamp = date("ymdHis".$seq) ;
	SetValue("timestamp",$timestamp);

	write( "timestamp = $timestamp");
	$checkin_wait = GetValue("checkin_wait");

	write( "checkin_wait {$checkin_wait} seconds");
	sleep($checkin_wait);

	$return_result = run_main();

	send_rfid_status(SIGNAL_GREEN);

	return $return_result;
}

function run_main() {
	$return_result = false;

	$machine_code = GetValue("machine_code");
	$wait = GetValue("wait");
	$hold = GetValue("hold");
	$wait_download_photo = GetValue("wait_download_photo");
	$sensor = GetValue("sensor");
  
	$return_result = run_main_state($wait, $hold, $wait_download_photo, $sensor);
	
	return $return_result;
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

function test_run_main() {
	global $run_main_state;


	$arrCommand = array();
	if (file_exists("/var/www/command.run")) {
		$data = file_get_contents("/var/www/command.run");
		$arrCommand = json_decode($data);
	} else {

	}

	if (isset($arrCommand->lastState)) {
		$run_main_state = $arrCommand->lastState;
	}


	$machine_code = GetValue("machine_code");
	$wait = GetValue("wait");
	$hold = GetValue("hold");
	$wait_download_photo = GetValue("wait_download_photo");
	$sensor = GetValue("sensor");
  
  	write("START WITH STATE $run_main_state");

	run_main_state($wait, $hold, $wait_download_photo, $sensor);


	if ($run_main_state!=="done") {

		write("NOT COMPLETE PROCESS !!!");
		$arrCommand->lastState = $run_main_state;

		if (isset($arrCommand->retryCount)) {
			$arrCommand->retryCount += 1;
		} else {
			$arrCommand->retryCount = 0;
		}

		$json = json_encode($arrCommand);

		file_put_contents("/var/www/command.run", $json);
	} else {
		@unlink("/var/www/command.run");
	}
}

function run_main_state($wait=1, $hold=1, $wait_download_photo=3, $sensor="A") {
	global $run_main_state;

	$return_result = false;

	write("================ RUN MAIN STATE");


	if ($run_main_state==="") {
		$run_main_state = 'checkin_wait';
	}

	if (_init()) {

		if ($run_main_state==='checkin_wait') {
			send_rfid_status(SIGNAL_RED);

			$checkin_wait = GetValue("checkin_wait");

			write( "checkin_wait {$checkin_wait} seconds");
			sleep($checkin_wait);

			$run_main_state = "turn_camera_on";

		}


		if ($run_main_state==='turn_camera_on') {

			if (turn_camera_on()) {
				$run_main_state = "auto_capture";
			}

		}

		if ($run_main_state==='auto_capture') {
			
			if (auto_capture($sensor, $wait, $hold)) {

				sleep($wait_download_photo);

				$run_main_state = "camera_download";
			} else {
				$run_main_state = "done";
			}
			
		}

		if ($run_main_state==='mannual_capture') {
			
			if (mannual_capture( $hold)) {

				sleep($wait_download_photo);

				$run_main_state = "camera_download";
			} else {
				$run_main_state = "done";
			}
			
		}


		if ($run_main_state==='camera_download') {
			
			if (camera_download()) {
				$run_main_state = "file_transfers";
			}
			
		}

		if ($run_main_state==='file_transfers') {
			
			if (file_transfers()) {
				$run_main_state = "done";
			}
			
		}

		if ($run_main_state==='done') {
			
			write("STATE DONE");

			send_rfid_status(SIGNAL_GREEN);

			writeln("end.");

			$return_result = true;
		}

		_close();

	}

	return $return_result;
}


function turn_camera_on() {
	
	write('#### BEGIN STATE turn_camera_on');
	
	$return_result = false;

	if (autoSerialInit()) {

		_send("normal-mode");
		
		sleep(1);

		_send("trigger-servo");

		$return_result = true;

		autoSerialClose();
	}

	write("#### END STATE turn_camera_on : {$return_result}");

	return $return_result;
}


function auto_capture($sensor='A', $wait=1, $hold=1000) {
	
	write('#### BEGIN STATE auto_capture');

	$return_result = false;

	if (autoSerialInit()) {

		_send("normal-mode");

		sleep(2);

		_send("auto-capture:{$sensor},{$wait},{$hold}");

		if (wait_auto_capture()) {
			$return_result = true; 
		}

		autoSerialClose();

	}

	write("#### END STATE auto_capture : {$return_result}");

	return $return_result;

}

function mannual_capture($hold=1000) {
	
	write('#### BEGIN STATE manual_capture');

	$return_result = true;

	if (autoSerialInit()) {

		_send("normal-mode");

		
		_send("press-servo");

		usleep($hold * 1000);

		_send("release-servo");

		$return_result = true;

		autoSerialClose();

	}

	write("#### END STATE manual_capture : {$return_result}");

	return $return_result;

}


function camera_download() {

	write('#### BEGIN STATE camera_download');

	$return_result = false;


	if (autoSerialInit()) {

		_send("usb-mode");

		sleep(2);

		$userid = GetValue("userid");
		$timestamp = GetValue("timestamp");

		if (download_file_from_usb($userid, $timestamp)) {

			_send("normal-mode");

			$return_result = true; 

		}

		autoSerialClose();

	}

	write("#### END STATE camera_download : {$return_result}");

	return $return_result;

}

function file_transfers() {

	write('#### BEGIN STATE file_transfers');

	$return_result = false;


	if (fileTransferToFTP()) {

		$return_result = true; 

	}

	write("#### END STATE file_transfers : {$return_result}");

	return $return_result;
}

function capture() {
	global $timestamp;
	set();

	SetValue("userid", "123");
	

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
		sleep(5);

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

	echo false;
}


function ensure_usb_normal_mode($check_secs=false) {

	if ($check_secs) {
		if (date('s')!==$check_secs) {
			return false;
		}
	}

	write("@");

	if (autoSerialInit()) {

		_send("normal-mode", false);
	
		autoSerialClose();
	}

}

if (isset($cmd)) {
	if (function_exists($cmd)) {


		if ($cmd==='online') {
			
			online();

		} else if ($cmd==='offline') {
			
			offline();

		} else if ($cmd==='set') {
			
			set();

		} else {

			if (!is_in_pocess()) {

				offline();

				$cmd();

				online();

			} else {

				print("Warning: Camera is in process");

			}
		}


	}
}
