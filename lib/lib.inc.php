<?php

define("SIGNAL_RED", 'red');
define("SIGNAL_GREEN", 'green');
define("SIGNAL_OFF", "off");

 function GetValue($key) {
  //writeln("GetValue '{$key}'");
  if($key == "machine_code") {
    return GetMachineCodeFromHostName();
  }
  $machine_code = GetMachineCodeFromHostName();
	$file = "./data/$machine_code/$key.txt";
	//writeln("from file '{$file}'");
	if (file_exists($file)) {
		$value = file_get_contents($file);
		//$handle = fopen($file, "r");
		//$value = fread($handle, filesize($file));
		//fclose($handle);
		return $value ;
	} else {
		return GetDefaultValue($key);
	}
 }

 function GetDefaultValue($key) {
	
	if (isset($key)) {
		$file = "./data/$key.txt";
		if (file_exists($file)) {
			$value = file_get_contents($file);
			//$handle = fopen($file, "r");
			//$value = fread($handle, filesize($file));
			//fclose($handle);
			return $value ;
		} else {
			return false;
		}
	}
 }

 function SetValue($key,$value) {
  $machine_code = GetMachineCodeFromHostName();
  $machine_dir = "./data/$machine_code";
  if(!file_exists($machine_dir)) {
    mkdir($machine_dir);
  }
	$file = "./data/$machine_code/$key.txt";
	$fp = fopen( $file, 'w');
	fwrite($fp, $value);
	fclose($fp);
 }

function GetMachineCodeFromHostName() {
  $hostname = php_uname("n");
  return "00" . substr($hostname, strlen($hostname) - 1);
}

function _exec($cmd, &$out = null)
{
	write( "exec \$ $cmd\n");
	$desc = array(
		1 => array("pipe", "w"),
		2 => array("pipe", "w")
	);

	//system('echo "PASS" | sudo -u root -S COMMAND');
	$proc = proc_open($cmd, $desc, $pipes);

	$ret = stream_get_contents($pipes[1]);
	$err = stream_get_contents($pipes[2]);

	fclose($pipes[1]);
	fclose($pipes[2]);

	$retVal = proc_close($proc);

	if (func_num_args() == 2) $out = array($ret, $err);

	write( "> $ret");
	if ($retVal!=0) 
		write( "> exist code $retVal $err"); 
	write( "\n");
	return $retVal;
} 

//function _sudo($cmd, &$out = null)
//{
//	print "\$ sudo $cmd\n";
//	$desc = array(
//		1 => array("pipe", "w"),
//		2 => array("pipe", "w")
//	);
//
//	//system('echo "PASS" | sudo -u root -S COMMAND');
//	$sudo = '/var/www/sudo ' .$cmd;
//	$proc = proc_open($sudo, $desc, $pipes);
//
//	$ret = stream_get_contents($pipes[1]);
//	$err = stream_get_contents($pipes[2]);
//
//	fclose($pipes[1]);
//	fclose($pipes[2]);
//
//	$retVal = proc_close($proc);
//
//	if (func_num_args() == 2) $out = array($ret, $err);
//
//	print "$ret";
//	if ($retVal!=0) 
//		print "exist code $retVal $err"; 
//	print "\n";
//	return $retVal;
//} 

function _sudo($cmd, &$out = null)
{
	return _exec("sudo ".$cmd,$out);
}

function _sudo222($cmd, &$out = null)
{
	print "\$ sudo $cmd\n";
	$desc = array(
		1 => array("pipe", "w"),
		2 => array("pipe", "w")
	);

	//system('echo "PASS" | sudo -u root -S COMMAND');
	$sudo = 'sudo ' .$cmd;
	$proc = proc_open($sudo, $desc, $pipes);

	$ret = stream_get_contents($pipes[1]);
	$err = stream_get_contents($pipes[2]);

	fclose($pipes[1]);
	fclose($pipes[2]);

	$retVal = proc_close($proc);

	if (func_num_args() == 2) $out = array($ret, $err);

	print "$ret";
	if ($retVal!=0) 
		print "exist code $retVal $err"; 
	print "\n";
	return $retVal;
} 
function _sudoo($cmd, &$out = null)
{
	write( "\$ sudo $cmd\n");
	$sudo = 'sudo ' .$cmd;
	$ret = shell_exec($sudo);
	
	write( "$ret");
	
	return $ret;
}

function send_rfid_status($cmd)
{
	$rfid_status_ip = GetValue("rfid_status_ip");

	write("send_rfid_status:{$cmd} to '{$rfid_status_ip}'\r\n");

	if (!empty($rfid_status_ip)) {
		$cmd = urlencode($cmd);
		$url="http://{$rfid_status_ip}/signal.php?cmd={$cmd}";

		file_get_contents($url);
	}
}

function writeln($content, $fileName = "app") {
	write("{$content}\r\n", $fileName);
}

$log_id = date("Ymd_His");
function write($content, $fileName = "app")
{
	global $log_id, $time_start;

	$curr_time = date("His");

	$time_end = microtime(true);
	$time = $time_end - $time_start;
	$time = round($time, 2);

	$filePath = "/var/log/cameracontrol/{$fileName}.log";
	print $content;
	$fs = fopen($filePath, 'a');

	if ($content != '.') {
		$content = "{$log_id}+[{$time}] : $content";
	}
	fwrite($fs, $content);
	fclose($fs);
}
