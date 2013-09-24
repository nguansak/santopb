<?php

include_once("./lib/lib.inc.php");

print_r($_GET);

$json = json_encode($_GET);

file_put_contents("command.run", $json);