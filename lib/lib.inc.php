<?php


define("SIGNAL_RED", 'red');
define("SIGNAL_GREEN", 'green');
define("SIGNAL_ORANGE", 'orange');
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

function _exec($cmd, &$out = null, $verbal=true)
{
	write( "exec \$ $cmd");
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

	if (!empty($ret)) {
		if ($verbal) {
			write( "> $ret");
		}
	}

	if ($retVal!=0) 
		write( "> exist code $retVal $err"); 
	//writeln();
	return $ret;
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

function _sudo($cmd, &$out = null, $verbal=true)
{
	return _exec("sudo ".$cmd, $out, $verbal);
}

function _sudo_rmdir($file) {
	$out = "";
	$result = _sudo("rm -Rf {$file}", $out, false);

	if (empty($result))
	{
		return true;
	} else {
		return false;
	}
}

function send_rfid_status($cmd, $control_command=false)
{
	if (($control_command==false)&&(is_offline())) {
		return false;
		//$cmd = SIGNAL_OFF;
	}

	$rfid_status_ip = GetValue("rfid_status_ip");

	if (!$control_command) {
		write("send_rfid_status:{$cmd} to '{$rfid_status_ip}'");
	}

	if (!empty($rfid_status_ip)) {
		$cmd = urlencode($cmd);
		$url="http://{$rfid_status_ip}/signal.php?cmd={$cmd}";

		file_get_contents($url);
	}
}

function writeln($content='', $fileName = "app") {
	write("{$content}\n", $fileName);
}


$log_id = date("Ymd_His");
$time_start = microtime(true);

function write($content='', $fileName = "app", $check_file=false)
{
	global $log_id, $time_start;

	$curr_time = date("His");

	$time_end = microtime(true);

	$time = $time_end - $time_start;
	$time = round($time, 2);
	$time = number_format($time, 2);
	$time = str_pad($time, 5, "0", STR_PAD_LEFT);

	$machine_code = GetValue("machine_code");

	$curr_date = date("Ymd"); 
	$fileName = "L{$curr_date}_{$machine_code}_{$fileName}";

	$filePath = "/var/log/cameracontrol/{$fileName}.log";

	if ($check_file) {
		if (!file_exists($filePath)) {
			_exec("sudo touch {$filePath}");
			_exec("sudo chown linaro:linaro {$filePath}");
		}
	}

	
	$fs = fopen($filePath, 'a');

	if (($content != '.')&&($content != '#')&&($content != '@')) {
		$content = "\n{$log_id}+[{$time}] : $content";
	}
	print "$content";
	fwrite($fs, $content);
	fclose($fs);
}


//function startsWith($haystack, $needle)
//{
//    return !strncmp($haystack, $needle, strlen($needle));
//}

function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}

function endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

function generate_new_timestamp() {
	$seq = rand(0,99);
	$seq = str_pad($seq, 2, "0", STR_PAD_LEFT); 

	$timestamp = date("ymdHis".$seq) ;

	return $timestamp;
}

function online() {
	writeln("Go Online");
	@unlink("/var/www/offline.lock");
	send_rfid_status(SIGNAL_GREEN);
}

function offline() {
	writeln("Go Offline");
	@touch("/var/www/offline.lock");
	send_rfid_status(SIGNAL_OFF);
}


function is_offline() {
	$status = file_exists("/var/www/offline.lock");
	return $status;
}

function is_in_pocess() {
	$status = file_exists("command.run");
	return $status;
}