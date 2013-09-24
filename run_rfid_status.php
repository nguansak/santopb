<?

if (function_exists($cmd)) {
	$cmd();
}

if ($rfid_status=='all-off') {
	send_rfid_status('red-off');
	send_rfid_status('green-off');
} else {
	send_rfid_status($rfid_status);
}
