// hello from php.php 
 <?php
error_reporting(E_ALL);
ini_set("display_errors", 1);


if(isset($_REQUEST['message'])){
$msg=$_REQUEST['message'];
require("php_serial.class.php");
deviceSet("/dev/ttyACM0");
$serial = new phpSerial();
$serial->deviceSet("/dev/ttyACM0"); // Arduino usb-port
$serial->confBaudRate(115200);  //baud rate
$serial->confParity("none");  //Parity 
$serial->confCharacterLength(8); //Character length   
$serial->confStopBits(1);  //Stop bits
$serial->confFlowControl("none");
$serial->deviceOpen(); // open connection

$serial->sendMessage($msg); //send the message
}
?>
<html><body>
<h3>If you're running Linux: remember to give permissions to www-data to access usbport ($ sudo chmod 777 /dev/ttyACM0)</h3>
<a href="ex4.php?message=trigger-servo">Send hello message</a>
</body></html>

