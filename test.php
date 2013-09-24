<?php
// led.php code in here
error_reporting(E_ALL); 
ini_set("display_errors", 1);

if (isset($_GET['action'])) {
  require("php_serial.class.php");
  $serial = new phpSerial();
  $serial->deviceSet("/dev/ttyACM0");
   $serial->confBaudRate(9600);
        $serial->deviceOpen();
$action = $_GET['action'];
$serial->sendMessage($action."\0");
$serial->deviceClose();
}


?>
<!--// now show your html form regardless 
of whether the form was submitted or not // -->
<!DOCTYPE html>
<html>
<head>
<title>ARDUINO</title>
</head>
<body>

<h1> ARDUINO AND PHP COMMUNICATION </h1>

<a href="?action=help">gelp</a></br>
<a href="?action=trigger-servo">trigger-servo</a></br>

</body>
</html>

