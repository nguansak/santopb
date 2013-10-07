</code></pre>
<?php

	$machine_code = GetValue("machine_code");
	$project_code = GetValue("project_code");
	$userid = GetValue("userid");
	$timestamp = GetValue("timestamp");

	$ftp_server = GetValue("ftp_server");
	$ftp_user = GetValue("ftp_user");
	$ftp_pass = GetValue("ftp_pass"); 
  
	$standby_delay_time = GetValue("standby_delay_time");
	$checkin_wait = GetValue("checkin_wait");
	$wait = GetValue("wait");
	$hold = GetValue("hold");
	$wait_download_photo = GetValue("wait_download_photo");
	$sensor = GetValue("sensor");
	$auto_capture_timeout = GetValue("auto_capture_timeout");
	
	$system_recovery_delay = GetValue("system_recovery_delay");

	$rfid_status_ip = GetValue("rfid_status_ip");

?>

<form action="index.php" method="get">
<fieldset>
<legend>Check-In</legend>
<input type="hidden" name="cmd" value="checkin" />


<label>user-id</label>
<input type="text" name="userid" value="<?=$userid?>" />
<br/>

<label>timestamp</label>
<input type="text" disabled="disabled" name="timestamp" value="<?=$timestamp?>" />

<br/>

<input type="submit" class="btn btn-primary"/>
</fieldset>
</form>


<form action="index.php" method="get">
<fieldset>
<legend>Set</legend>
<input type="hidden" name="cmd" value="set" />


<label>user-id</label>
<input type="text" name="userid" value="<?=$userid?>" />
<br/>

<label>timestamp</label>
<input type="text" disabled="disabled" name="timestamp" value="<?=$timestamp?>" />
<br/>

<input type="submit" class="btn btn-primary"/>
</fieldset>
</form>

<form action="index.php" method="get">
<fieldset>
<legend>Run Main</legend>
<input type="hidden" name="cmd" value="set" />


<label>sensor</label>
<input type="text" name="sensor" value="<?=$sensor?>" />
<br/>

<label>standby_delay_time</label>
<input type="text" name="standby_delay_time" value="<?=$standby_delay_time?>" /> seconds
<br/>

<label>checkin_wait</label>
<input type="text" name="checkin_wait" value="<?=$checkin_wait?>" /> seconds
<br/>

<label>wait</label>
<input type="text" name="wait" value="<?=$wait?>" /> milliseconds
<br/>

<label>hold</label>
<input type="text" name="hold" value="<?=$hold?>" /> milliseconds
<br/>

<label>wait_download_photo</label>
<input type="text" name="wait_download_photo" value="<?=$wait_download_photo?>" /> seconds
<br/>

<label>auto_capture_timeout</label>
<input type="text" name="auto_capture_timeout" value="<?=$auto_capture_timeout?>" /> seconds
<br/>

<label>system_recovery_delay</label>
<input type="text" name="system_recovery_delay" value="<?=$system_recovery_delay?>" /> seconds
<br/>

<input type="submit" class="btn btn-primary"/>
</fieldset>
</form>

<form action="index.php" method="get">
<fieldset>
<legend>Setting</legend>
<input type="hidden" name="cmd" value="set" />


<label>machine_code</label>
<input type="text" name="machine_code" disabled="disabled" value="<?=$machine_code?>" />
<br/>

<label>project_code</label>
<input type="text" name="project_code" value="<?=$project_code?>" />
<br/>
<label>ftp_server</label>
<input type="text" name="ftp_server" value="<?=$ftp_server?>" />
<br/>
<label>ftp_user</label>
<input type="text" name="ftp_user" value="<?=$ftp_user?>" />
<br/>
<label>ftp_pass</label>
<input type="password" name="ftp_pass" value="<?=$ftp_pass?>" />
<br/>
<label>rfid_status_ip</label>
<input type="text" name="rfid_status_ip" value="<?=$rfid_status_ip?>" />
<br/>
<input type="submit" class="btn btn-primary"/>
</fieldset>
</form>



<form action="index.php" method="get">
<fieldset>
<legend>EEPROM</legend>
<input type="hidden" name="arduino" value="set" />
<label>eeprom0</label>
<input type="text" name="eeprom0" value="" />
<br/>
<label>eeprom1</label>
<input type="text" name="eeprom1" value="" />
<br/>
<label>eeprom2</label>
<input type="text" name="eeprom2" value="" />
<br/>
<label>eeprom3</label>
<input type="text" name="eeprom3" value="" />
<br/>
<label>eeprom4</label>
<input type="text" name="eeprom4" value="" />
<br/>
<input type="submit" class="btn btn-primary"/>
<br/>
set-eeprom0=255  //reset to default<br/>
set-eeprom1=1000  //cfg_change_usb_mode_delay_time<br/>
set-eeprom2=90  //cfg_shutter_servo_standby_position<br/>
set-eeprom3=73  //cfg_shutter_servo_trigger_position<br/>
set-eeprom4=200  //cfg_shutter_servo_trigger_duration<br/>s
</fieldset>
</form>



<hr/>
