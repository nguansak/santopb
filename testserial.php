x<?php  
 include "php_serial.class.php";  

// Let's start the class
$serial = new phpSerial;

// First we must specify the device. This works on both linux and windows (if
// your linux serial device is /dev/ttyS0 for COM1, etc)
$serial->deviceSet("/dev/ttyACM0");

// We can change the baud rate, parity, length, stop bits, flow control
$serial->confBaudRate(19200);
$serial->confParity("none");
$serial->confCharacterLength(8);
$serial->confStopBits(1);
$serial->confFlowControl("none");

// Then we need to open it
$serial->deviceOpen();  

// read from serial port
$read = $serial->readPort();

//Determine if a variable is set and is not NULL
if(isset($read)){
   while(1){
       $read = $serial->readPort();
       print_r(" (size ".strlen($read). " ) ");
       for($i = 0; $i < strlen($read); $i++)
       {
          echo ord($read[$i])." ";
       }
       print_r("\n");
       sleep(1);
  }// end while
}// end if  


// If you want to change the configuration, the device must be closed
$serial->deviceClose();

// We can change the baud rate
$serial->confBaudRate(19200);  

