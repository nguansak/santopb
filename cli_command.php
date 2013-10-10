<?

// Make it possible to test in source directory
// This is for PEAR developers only
ini_set('include_path', ini_get('include_path').':..');

// Include Class
//error_reporting(E_STRICT);

require_once "serial.php";
require_once 'System/Daemon.php';

require_once "lib/lib.inc.php";

require_once "run_command.php";

	
error_reporting(E_ALL); 

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
	global $run_main_state;

	//write_daemon_log("Get Process Command from 'comman.run'");
	if (is_offline()) {
		write("#", "app", true);

		send_rfid_status(SIGNAL_OFF, true);

		ensure_usb_normal_mode('00');
		
		return false;
	} else {
		write(".", "app", true);

		send_rfid_status(SIGNAL_GREEN, true);

		ensure_usb_normal_mode('00');
		
	}

	if (file_exists("/var/www/command.run"))
	{
	
		$data = file_get_contents("/var/www/command.run");
		$arrCommand = json_decode($data);

		$next_run = $arrCommand->next_run;


		write_daemon_log("next_run {$next_run} now " . strtotime('now'));

		if ($next_run <= strtotime('now')) {

			write_daemon_log($data);

			if (isset($arrCommand->userid)) {
				$userid = $arrCommand->userid;
				SetValue("userid", $userid);
			}

			// Initial Timestamp
			if (isset($arrCommand->timestamp)) {
				$timestamp = $arrCommand->timestamp;
			} else {
				$timestamp = generate_new_timestamp() ;
			}
			SetValue("timestamp",$timestamp);

			// Initial last run state
			if (isset($arrCommand->lastState)) {
				$run_main_state = $arrCommand->lastState;
				write("#### RESUME STATE {$run_main_state}");
			} else {
				writeln("==============================================================================================");
				writeln("START RUNNING");
			}

			write("PARAM userid = {$userid}");
			write("PARAM timestamp = {$timestamp}");
			
			write_daemon_log("Process [user id: {$userid}]");

			process_command();

			if ($run_main_state !== "done") {

				writeln("#### NOT COMPLETE PROCESS !!!");

				ensure_usb_normal_mode();

				$system_recovery_delay = GetValue(system_recovery_delay);

				if ($system_recovery_delay) {
					writeln("Sleep for system recovery for {$system_recovery_delay} secs");
					sleep($system_recovery_delay);
				}

				$userid = GetValue("userid");
				$timestamp = GetValue("timestamp");

				$arrCommand->lastState = $run_main_state;
				$arrCommand->timestamp = $timestamp;
			
				if (isset($arrCommand->retryCount)) {

					if ($arrCommand->retryCount < 10) {
						$arrCommand->retryCount += 1;
					} else {
						writeln("#### RETRY FAIL !!");

						// System Alert !!

						offline();
					}

				} else {
					$arrCommand->retryCount = 0;
				}

				$json = json_encode($arrCommand);

				file_put_contents("/var/www/command.run", $json);

			} else {

				writeln("FINISH RUNNING");

				@unlink("/var/www/command.run");
			}

			//unlink("command.run");
		}
		
	}
}

function process_command() {
	
	//capture();
	run_main();

}

function write_daemon_log($message, $level=System_Daemon::LOG_INFO) 
{
	if (!defined('NO_SHOW_OUTPUT'))
	{
		echo "{$message}\r\n" ;
	}
	System_Daemon::info($message);
}

