<?php

$USBDRIVE_DETECT_TIME = 20;
$mount_point = ""; 

 
function move_all_files_from_usbdrive($temp_picture_folder, $not_download_photo=false) {
  write( "> Move all file from usbdrive to '" . $temp_picture_folder . "'");
  
  _sudo("ls /mnt/usbdrive");

  $file_list = _sudo("find /mnt/usbdrive/DCIM/ -name \*.JPG");

  if (!empty($file_list)) {

    _sudo("mkdir -p " . $temp_picture_folder);
    _sudo("ls $temp_picture_folder");
  
    if (!$not_download_photo) {
  	  _sudo("find /mnt/usbdrive/DCIM/ -name \*.JPG -exec mv {} " . $temp_picture_folder . " \;");
    }

    $file_list_check = _sudo("find /mnt/usbdrive/DCIM/ -name \*.JPG"); 

    if (!$not_download_photo) {
      if (!empty($file_list_check))
      {
        write("Still has file after move");
        return false;
      }
    }

    _sudo("chown -Rf linaro:linaro " . $temp_picture_folder);
    _sudo("rm -Rf /mnt/usbdrive/DCIM/*");

  }

  return true;

}

function auto_mount_usbdrive($n, $retry=2) {
  global $mount_point;
  
  write( "> Auto mount usbdrive");


  while ($retry>=0) {

    $mount_point = retrive_mount_point($n);

    if (empty($mount_point)) {

      // make sure it in usb mode
      _send("trigger-servo");
      _send("usb-mode"); 
      
    } else {

      break;

    }


    $retry = $retry - 1;
  }

  if ($mount_point) {
    write( "> Mount usb drive to '" . $mount_point . "'");
    _sudo("/bin/mount " . $mount_point . " /mnt/usbdrive");
	 
    if (file_exists("/mnt/usbdrive"))  {
      return "ok";
    }
		  
	 return "";
  } else {
    print "here";
    return "";
  }
}

function unmount_usbdrive() {
  global $mount_point;

  if (!$mount_point) {
    return;
  }

  write( "> Unmount usb drive");
  _sudo("/bin/umount " . $mount_point . " /mnt/usbdrive");
}

function retrive_mount_point($n) {
  global $mount_point;
  write( "> Retrive mount point (retry $n)");

  while ($n > 0) {
    $mount_point = get_mount_point();
    if ($mount_point)
	  {
		  write( "found mounth_pount $mount_point");
      return $mount_point;
	  }
    sleep(1);
    $n = $n - 1;
  }
  return "";
}


function get_mount_point() {

  write( "> Get mount point");

  $devicePath = "";
  $contents = file_get_contents('/proc/partitions');

  $lines = explode("\n", $contents);

  $drives = array();
  foreach ($lines as $line) {

    $words = explode(' ', $line);
    $words = array_filter($words, "strlen");
    $words = array_values($words);

    if (count($words)==4) {
      $minorNumber = intval($words[1]);
      $deviceName = $words[3];

      if (startsWith($deviceName, 'sd'))
      {
        $drives[$deviceName] = true;

        if (strlen($deviceName)==4) {
          $mainDrive = substr($deviceName, 0, 3);
          unset($drives[$mainDrive]);
        }

      }

    }
  }

  foreach ($drives as $deviceName => $value) {
    // Check if drive is exist
    $path = "/sys/class/block/" . $deviceName;
    //echo "$path \n";
    if (is_link($path))
    {
      $realpath = realpath($path);

      if (strpos($realpath, '/usb')>0)
      {
        $checkDevicePath = "/dev/{$deviceName}";
        if (file_exists($checkDevicePath))
        {
          $devicePath = $checkDevicePath;
          return $devicePath;
        }
      }  
    }  
  }

  return $devicePath;

  //for ($i=0;$i<20;$i++) {
	//  $m = "/dev/sd" . chr(ord('a')+$i) . "1";
	//  echo $m . "\n";
	//	if (file_exists($m)) return $m;
  //if (file_exists("/dev/sdb1")) return "/dev/sdb1";
  //}
  //return "";
  /*
  partitionsFile = open("/proc/partitions") 
  lines = partitionsFile.readlines()[2:]#Skips the header lines
  for line in lines:
    words = [x.strip() for x in line.split()]
    minorNumber = int(words[1])
    deviceName = words[3]
    if minorNumber % 16 == 1:
      if deviceName.startswith("sd"):
        path = "/sys/class/block/" + deviceName
        if os.path.islink(path):
          if os.path.realpath(path).find("/usb") > 0:
            device_path = "/dev/%s" % deviceName
            if os.path.exists(device_path):
              return device_path

  return "";*/
}


function auto_download_photo($n,$temp_picture_folder, $not_download_photo=false) { 
	write(  "> Auto download photo");

  $return_result = false;

	if (auto_mount_usbdrive($n)=="ok") {
		
    $return_result = move_all_files_from_usbdrive($temp_picture_folder, $not_download_photo);

		unmount_usbdrive();

	}

  if ($return_result) {
    write( "> auto_download_photo done");
  } else {
    write( "> auto_download_photo fail");
  }

  return $return_result;
}

function download_file_from_usb($userid, $timestamp) {
	global $USBDRIVE_DETECT_TIME;

  if (!isset($userid)) {
    $userid = "123";
  }

  if (!isset($timestamp)) {
    $seq = rand(0,99);
    $seq = str_pad($seq, 2, "0", STR_PAD_LEFT); 
    $timestamp = date("ymdHis".$seq) ;
  }

  $folder = "{$userid}/{$timestamp}";
  $photobooth_folder = "/store/photobooth/";

  _sudo("chown -Rf linaro:linaro {$photobooth_folder}");

  $temp_picture_folder = "{$photobooth_folder}{$folder}/";

  write("Working Folder: " . $temp_picture_folder);

	return auto_download_photo($USBDRIVE_DETECT_TIME,$temp_picture_folder);
}



/*
 whoami
www-data

$ sudo whoami
www-data

Working Folder: /tmp/photobooth/2323/13091501553620/
> Auto download photo
> Auto mount usbdrive
> Retrive mount point (retry 20)
> Get mount point
found mounth_pount /dev/sdb1
> Mount usb drive to '/dev/sdb1'
$ sudo /bin/mount /dev/sdb1 /mnt/usbdrive

> Move all file from usbdrive to '/tmp/photobooth/2323/13091501553620/'
$ mkdir -p /tmp/photobooth/2323/13091501553620/

$ find /mnt/usbdrive/DCIM/ -name \*.JPG
exist code 1 find: `/mnt/usbdrive/DCIM/': No such file or directory

$ find /mnt/usbdrive/DCIM/ -name \*.JPG -exec mv {} /tmp/photobooth/2323/13091501553620/ \;
exist code 1 find: `/mnt/usbdrive/DCIM/': No such file or directory

$ chown -Rf www-data:www-data /tmp/photobooth/2323/13091501553620/

$ sudo rm -Rf /mnt/usbdrive/DCIM/*

> Unmount usb drive
$ sudo /bin/umount /dev/sdb1 /mnt/usbdrive

> done 

*/