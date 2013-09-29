<?php
error_reporting(E_ALL); 
ini_set("display_errors", true); 
set_time_limit(60*60*2); // 2h
@apache_setenv('no-gzip', 1);
@ini_set('output_buffering',0);
@ini_set('zlib.output_compression',0);
@ini_set('implicit_flush',true);

@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
@ob_end_flush();
ob_implicit_flush(true);

echo "<pre>";

$handle = popen("sudo tail -f /var/log/cameracontrol/app.log 2>&1", 'r');
while(!feof($handle)) {
    $buffer = fgets($handle);
    echo "$buffer";
    flush();
}
pclose($handle);
