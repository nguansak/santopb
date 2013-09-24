<?

// Make it possible to test in source directory
// This is for PEAR developers only
ini_set('include_path', ini_get('include_path').':..');

// Include Class
//error_reporting(E_STRICT);
error_reporting(E_ALL); 

require_once "serial.php";
require_once 'System/Daemon.php';

require_once "lib/lib.inc.php";


if (!defined('DAEMON_MODE'))
{
	runProcess();
}

function runProcess() 
{
	
	getProcessCommand();
	
}



function getProcessCommand()
{
	write_daemon_log("Get Process Command from 'comman.run'");

	if (file_exists("command.run"))
	{
		$data = file_get_contents("command.run");
		$arrCommand = json_decode($data);

		write_daemon_log($data);

		$userid = $arrCommand->userid;
		write_daemon_log("Process [user_id: {$userid}]");

		SetValue("userid", $userid);

		process_command();
		//unlink("command.run");
	}
}

function process_command() {
	global $time_start;

	include_once "run_command.php";

	$time_start = microtime(true);

	capture();


}

function write_daemon_log($message, $level=System_Daemon::LOG_INFO) 
{
	if (!defined('NO_SHOW_OUTPUT'))
	{
		echo "{$message}\r\n" ;
	}
	System_Daemon::info($message);
}

