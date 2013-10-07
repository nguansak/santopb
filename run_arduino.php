<?php


if (!is_in_pocess()) {

	offline();

	_init();
	if ($arduino=='read-serial')
	{
		for ($i=0;$i<20;$i++)
		{
			echo $i._read()."\n";
			sleep(1);
		}
	}
	else if ($arduino=='wait-senser')
	{
		echo "wait_senser\n";
		wait_senser();
	}
	else if ($arduino=='wait-auto-capture')
	{
		echo "wait_auto_capture\n";
		wait_auto_capture();
	}
	else if ($arduino=="set") {
		global $_GET;
		echo "--- set ---";
		foreach( $_GET as $key => $value ) {
			if (startsWith($key,"eeprom")) {
				if ($value) {
				$cmd = "set-{$key}={$value}";
				echo "$cmd\n";
				_send($cmd);
				echo _read();
				}
			} 
		}
	}
	else
	{
		_send($arduino );
		echo _read();
	}
	_close();
	echo "end.";

	echo "\n";

	online();
} else {
	print("Warning: Camera is in process !!!");
}