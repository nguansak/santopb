#!/usr/bin/python
import os
import time
import random
import subprocess 

from time import sleep

import sys

USBDRIVE_DETECT_TIME = 20

mount_point = ""
localtime   = time.localtime()

timeString  = time.strftime("%Y%m%d_%H%M%S", localtime)
timeStamp = timeString + str(random.randint(0, 99)).zfill(2)

if len(sys.argv)==2:
  folder = sys.argv[1];
else:
  folder = timeStamp;

temp_picture_folder = "/tmp/photobooth/" + folder + "/"

print "Working Folder: " + temp_picture_folder


def auto_download_photo():
  print "> Auto download photo"

  if (auto_mount_usbdrive()):
    move_all_files_from_usbdrive()
    unmount_usbdrive()
    print "> done"
  else:
    print "> fail"

def shell_exec(cmd):
  popen = subprocess.Popen(cmd, stdout=subprocess.PIPE) 
  out, err = popen.communicate()
  print out

def move_all_files_from_usbdrive():
  global temp_picture_folder
  print "> Move all file from usbdrive to '" + temp_picture_folder + "'"
  try:
    command = "sudo mkdir -p " + temp_picture_folder
    print "# " + command
    os.system(command)

    #command = "sudo find /mnt/usbdrive/DCIM/ -name \*.JPG"
    #print "# " + command  
    #os.system(command)
    #shell_exec(command)

    command = "sudo find /mnt/usbdrive/DCIM/ -name \*.JPG -exec mv {} " + temp_picture_folder + " \;"
    print "# " + command  
    os.system(command)

    #command = "sudo chown -Rf www-data:www-data " + temp_picture_folder
    command = "sudo chown -R www-data:www-data /tmp/" 
    print "# " + command
    os.system(command)

    #command = "sudo rm -Rf /mnt/usbdrive/DCIM/*"
    #print "# " + command
    #os.system(command)

  except Exception, e:
    print e.message

def auto_mount_usbdrive():
  global mount_point
  
  print "> Auto mount usbdrive"
  retrive_mount_point(USBDRIVE_DETECT_TIME)

  if (mount_point):
    print "> Mount usb drive to '" + mount_point + "'"
    command = "sudo /bin/mount " + mount_point + " /mnt/usbdrive"
    print "# " + command
    try:
      os.system(command)
    except Exception, e:
      print e.message
    return "ok"
  else:
    print "here"
    return ""

def unmount_usbdrive():
  global mount_point

  if not mount_point:
    return

  print "> Unmount usb drive"
  command = "sudo /bin/umount " + mount_point + " /mnt/usbdrive"
  print "# " + command
  os.system(command)

def retrive_mount_point(n):
  global mount_point
  print "> Retrive mount point"
  while n > 0:
    mount_point = get_mount_point()
    if (mount_point):
      return mount_point
    sleep(1)
    n = n - 1
  return ""

def get_mount_point():
  print "> Get mount point"
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

  return ""

#get_mount_point()
auto_download_photo()
#print retrive_mount_point(10)
