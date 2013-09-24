<?php
set_time_limit(60*60*2); // 2h
@ini_set('output_buffering',0);
@ini_set('zlib.output_compression',0);
@ini_set('implicit_flush',true);
ob_implicit_flush(true);
@ob_end_clean();
//ini_get('max_execution_time');

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

//@phpinfo();
for ($i=1;$i<10;$i++) {
echo  "fork $i";
@ob_flush();
flush();
ob_end_clean();
sleep(1);
}


?>
