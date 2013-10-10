<?php

include_once("./lib/lib.inc.php");


$userid = $_GET['userid'];

if (empty($userid)) {
	die("userid is empty");
}

writeln();
writeln("Got checkin {$userid}");

/*
if (!isset($_GET['test'])) {
	//send_rfid_status(SIGNAL_OFF);
	exit();
} else {
	online();
}
*/

if (is_offline()) {
	write("System is offline!!");
	return false;
}

if (!file_exists("command.run"))
{
	if (isset($_GET['userid']))
	{

		$standby_delay_time = GetValue("standby_delay_time");

		$_GET['next_run'] = strtotime("+{$standby_delay_time} sec");
		
		send_rfid_status(SIGNAL_ORANGE);

		$json = json_encode($_GET);

		file_put_contents("command.run", $json);

	}
}

