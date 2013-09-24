<?php



include "php_serial.class.php";
$serial = null;

function _init() {
	global $serial;
	if ($serial != null) return false;

	send_rfid_status(SIGNAL_RED);


	$serial = new phpSerial;
	$serial->deviceSet("/dev/ttyACM0");
	
	//$serial->autoflush = false;
	$serial->confBaudRate(9600);
	$serial->confParity("none");
	$serial->confCharacterLength(8);
	$serial->confStopBits(1);
	$serial->confFlowControl("none");

	$serial->deviceOpen();

	return true;
}

function microtime_float()
{
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
}


function _send($cmd) {
	global $serial;
	write( "$cmd\n");
	$serial->sendMessage($cmd."\0");
}

function _read() {
	global $serial;
	return $serial->readPort();
}

function _readAll() {
	global $serial;
	$read = '';
	$theResult = '';
	$start = microtime_float();

	while ( ($read == '') && (microtime_float() <= $start + 0.5) ) {
        	$read = $serial->readPort();
       	 	if ($read != '') {
                	$theResult .= $read;
                	$read = '';
        	}
	}
	return $read;
}

function process_result($result, $time) {
	global $senser;
	$pos = strpos($result, 10);
	//echo " #{$pos}# $result \n";
	if ($pos) {
		$cmd = substr($result,0,$pos);
		$result = substr($result,$pos+2);
		echo "## $time $cmd   "; 
		
		//#sensor|B|on
		$val = explode("|", $cmd);
		//print_r($val);
		if ($val[0] == "#sensor") {
			$key = "{$val[1]}{$val[2]}";
			$senser[$key] = $time;
			echo " $key = $time";
		}
		
		echo "\n";
		//print_r($senser);
	}
	return $result;
}

function valid_senser() {
	global $senser;
	/*
	if (!(sizeof($senser) == 4)) 
		return false;
	if (!($senser['Aon'] < $senser['Aoff'])) 
		return false;
	if (!($senser['Bon'] < $senser['Boff'])) 
		return false;
	if (!($senser['Aoff'] < $senser['Bon'])) 
		return false;
		*/
	
	/*if (isset($senser['Aon']) && isset($senser['Bon']))
	{
	
	echo "senser done!!\n";
	//print_r($senser);
	return true;
	}
	else
		return false; */

	if (isset($senser['Aon']) || isset($senser['Bon']))
		return true;

	return false;
	
}

function process_result_capture($result, $time) {
	global $senser;
	$pos = strpos($result, 10);
	//echo " #{$pos}# $result \n";
	if ($pos) {
		$cmd = substr($result,0,$pos);
		$result = substr($result,$pos+2);
		echo "## $time $cmd   "; 
		
		//#sensor|B|on
		$val = explode("|", $cmd);
		//print_r($val);
		if ($val[0] == "#auto-capture-done") {
			write( "Auto Capture Done");
			return "#auto-capture-done";
		}
		
		echo "\n";
		//print_r($senser);
	}
	return $result;
}


function valid_senser_auto_capture($theResult) {
	global $senser;
	
	$pos = strpos($theResult, "#auto-capture-done");


	if ($pos !== false)
	{
		echo "Auto Capture Done";
		return true;
	}

	return false;
	
}

function wait_senser() { 
	global $serial,$senser;
	$senser = [];
	$read = '';
	$theResult = '';
	$start = microtime_float(); 
    $t = microtime_float() - $start;
	$c = 0 ; 
	$timeout = 60;
	while ( ($read == '') && ($t <= $timeout) ) {
        	$read = $serial->readPort(); 				
       	 	if ($read != '') {
					//echo "$t $read\n";
					
					/*
					for ($x=0;$x<strlen($read);$x++) {
						echo ord($read[$x]) . "  ";
					}
					echo "\n"; 
					*/
                	$theResult .= $read;
					$theResult = process_result($theResult, $t);
                	$read = '';


			if (valid_senser())
				return true;


        	}
			/*		
			if ($t>$c) {
				echo ".";
				//echo "---" . $theResult . "\n";
				$c += 1;
			}*/
			$t = microtime_float() - $start;

	}
	write( "end with timeout {$timeout}s\n");
	return false;
}


function wait_auto_capture() { 
	global $serial,$senser;

	$auto_capture_timeout = GetValue('auto_capture_timeout');

	$senser = [];
	$read = '';
	$theResult = '';
	$start = microtime_float(); 
    $t = microtime_float() - $start;
	$c = 0 ; 
	$timeout = $auto_capture_timeout;
	while ( ($read == '') && ($t <= $timeout) ) {
        	$read = $serial->readPort(); 				
       	 	if ($read != '') {
				/*
					echo "$t $read\n";
					
					
					for ($x=0;$x<strlen($read);$x++) {
						echo ord($read[$x]) . "  ";
					}
					echo "\n"; 
					*/
                	$theResult .= $read;
					$theResult = process_result_capture($theResult, $t);
                	$read = '';


			if (valid_senser_auto_capture($theResult))
				return true;


        	}
			
			if ($t>$c) {
				echo ".";
				//echo "---" . $theResult . "\n";
				$c += 1;
			}
			$t = microtime_float() - $start;

	}
	write( "end with timeout {$timeout}s\n");
	return false;
}

function _close() {
	global $serial; 

	send_rfid_status(SIGNAL_OFF);

	$serial->deviceClose();
	$serial = null;
}
