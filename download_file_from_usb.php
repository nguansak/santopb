<?php

_exec('whoami');
_sudo('whoami');

//_exec('find /mnt/usbdrive/DCIMwewe/ -name \*.JPG');
$USBDRIVE_DETECT_TIME = 20;
$mount_point = ""; 

if (!isset($userid)) {
	$userid = "123";
}
if (!isset($timeStamp)) {
	$seq = rand(0,99);
	$seq = str_pad($seq, 2, "0", STR_PAD_LEFT); 
	$timeStamp = date("ymdhis".$seq) ;
}

$folder = "{$userid}/{$timeStamp}";
$temp_picture_folder = "/tmp/photobooth/{$folder}/";

write( "Working Folder: " . $temp_picture_folder . "\n");


 
function move_all_files_from_usbdrive($temp_picture_folder, $not_download_photo=false) { 
  write( "> Move all file from usbdrive to '" . $temp_picture_folder . "'\n");
  _exec("ls /mnt");
  _exec("ls /mnt/usbdrive");
  _exec("mkdir -p " . $temp_picture_folder);
  _exec("find /mnt/usbdrive/DCIM/ -name \*.JPG"); 

  if (!$not_download_photo) {
	  _exec("find /mnt/usbdrive/DCIM/ -name \*.JPG -exec mv {} " . $temp_picture_folder . " \;");
  }

  //_exec("chown -Rf www-data:www-data " . $temp_picture_folder);
  _exec("ls -l /mnt/usbdrive/DCIM/");
  _sudo("rm -Rf /mnt/usbdrive/DCIM/*"); 
}

function auto_mount_usbdrive($n, $retry=2) {
  global $mount_point;
  
  write( "> Auto mount usbdrive\n");

  while ($retry>0) {

    _send("usb-mode"); // make sure it in usb mode
    
    retrive_mount_point($n);
    $retry = $retry - 1;
  }

  if ($mount_point) {
    write( "> Mount usb drive to '" . $mount_point . "'\n");
    _sudo("/bin/mount " . $mount_point . " /mnt/usbdrive");
	if (file_exists("/mnt/usbdrive")) 
		return "ok";
	return "";
  }
  else {
    print "here";
    return "";
  }
}

function unmount_usbdrive() {
  global $mount_point;

  if (!$mount_point)
    return;

  write( "> Unmount usb drive\n");
  _sudo("/bin/umount " . $mount_point . " /mnt/usbdrive");
}

function retrive_mount_point($n) {
  global $mount_point;
  write( "> Retrive mount point (retry $n)\n");
  while ($n > 0) {
    $mount_point = get_mount_point();
    if ($mount_point)
	  {
		write( "found mounth_pount $mount_point\n");
      return $mount_point;
	  }
    sleep(1);
    $n = $n - 1;
  }
  return "";
}


function get_mount_point() {
  write( "> Get mount point\n");
  for ($i=0;$i<20;$i++) {
	  $m = "/dev/sd" . chr(ord('a')+$i) . "1";
	  //echo $m . "\n";
		if (file_exists($m)) return $m;
  //if (file_exists("/dev/sdb1")) return "/dev/sdb1";
  }
  return "";
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
	write(  "> Auto download photo\n");
	if (auto_mount_usbdrive($n)=="ok") {
		move_all_files_from_usbdrive($temp_picture_folder, $not_download_photo);
		unmount_usbdrive();
		write( "> done\n");
	} else 
		write( "> fail\n");
}

function download_file_from_usb() {
	global $temp_picture_folder, $USBDRIVE_DETECT_TIME;

	auto_download_photo($USBDRIVE_DETECT_TIME,$temp_picture_folder);
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