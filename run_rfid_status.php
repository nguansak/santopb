<?

if (function_exists($cmd)) {
	$cmd();
}

if ($rfid_status=='all-off') {
	send_rfid_status('red-off', true);
	send_rfid_status('green-off', true);
} else {
	send_rfid_status($rfid_status, true);
}
