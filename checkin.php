<?php

include_once("./lib/lib.inc.php");

$userid = $_GET['userid'];
write("Got checkin {$userid}\r\n");

if (!file_exists("command.run"))
{
	if (isset($_GET['userid']))
	{
		

		$standby_delay_time = GetValue("standby_delay_time");

		$_GET['next_run'] = strtotime("+{$standby_delay_time} sec");

		
		send_rfid_status(SIGNAL_GREEN);
		send_rfid_status('green-on');

		//usleep(500 * 1000);

		//send_rfid_status(SIGNAL_OFF);

		$json = json_encode($_GET);

		file_put_contents("command.run", $json);

	}
}

