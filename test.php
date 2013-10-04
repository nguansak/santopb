<?php

include "serial.php";
include "./lib/lib.inc.php";

include_once "download_file_from_usb.php";

$mount_point = get_mount_point();
echo "$mount_point \n";

