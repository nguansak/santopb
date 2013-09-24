<?php


$fp =fopen("/dev/ttyACM0", "w+");
if( !$fp) {
        echo "Error";die();
}

fwrite($fp, "help\0" );
$v = fread($fp, 50);
while ($v) {
 echo $v;
 $v = fread($fp, 50);
}


fclose($fp);

?>
