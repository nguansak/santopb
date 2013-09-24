<?php
include_once("./lib/lib.inc.php");

error_reporting(E_ALL);
ini_set("display_error", true);
print '<pre>' ;

$ftp_server = GetValue("ftp_server"); // "10.14.2.52" ;
$ftp_user = GetValue("ftp_user"); // "santorini" ;
$ftp_pass = GetValue("ftp_pass"); // 'M0n$ter@santo' ;

//$ftp_server = "10.14.2.52" ; // "10.14.2.52" ;
//$ftp_user = "santorini" ; // "santorini" ;
//$ftp_pass =  'M0n$ter@santo' ; // 'M0n$ter@santo' ;
$root_pb = "/tmp/photobooth" ;
$ftp_incomming = "/STR/incomming" ;

$current_date = date("Ymd") ;

$project_code = GetValue("project_code") ;
$machine_code = GetValue("machine_code") ;

echo "$ftp_server ; $ftp_user ;  $ftp_pass \n";

$files_list = array() ;
echo "list {$root_pb}\n";
$users_dir = glob("{$root_pb}/*" , GLOB_ONLYDIR);
//print_r($users_dir);
foreach( $users_dir as $dir ) {
	$user_id = substr($dir , strlen($root_pb) + 1);
	$time_dir =  glob("{$dir}/*" , GLOB_ONLYDIR);
	//print_r($time_dir);
	$files_list[$user_id]  = array() ;
	foreach( $time_dir as $sub_dir ) {
		$time = substr($sub_dir , strlen($dir) + 1);
		//print "check {$sub_dir}/*.JPG";
		$files = glob("{$sub_dir}/*.JPG",GLOB_BRACE);
		 
		$files_list[$user_id][$time] = $files ;
	}
} 
 
$conn = ftp_connect($ftp_server) or die("Could not connect");
ftp_login($conn,$ftp_user,$ftp_pass);
if($conn) {
	$mkdir = "{$ftp_incomming}/{$current_date}" ;
	echo "mkdir $mkdir\n";
	if(!@ftp_chdir ($conn,$mkdir)) {
		ftp_mkdir($conn, $mkdir);
	}

	foreach($files_list as $user => $value) {
		
		$mkdir_user = "{$mkdir}/{$user}" ;
		echo "mkdir $mkdir_user\n";
		if(!@ftp_chdir ($conn,$mkdir_user)) {
			ftp_mkdir($conn, $mkdir_user);
		}

		foreach($value as $time => $files ) {
			$seq = 1 ;
			foreach($files as $file ) {
				$str_seq = str_pad($seq++, 2, "0", STR_PAD_LEFT); 
				$target_filename = "{$mkdir_user}/{$project_code}{$machine_code}_{$time}_{$str_seq}.JPG" ;
				$rs = ftp_put($conn, "{$target_filename}" ,$file, FTP_BINARY);
				print "FTP File to : {$target_filename} ->" . ( $rs ? "True" : "False" ) . "\n" ;
        print "<img src='http://10.14.2.51/photo{$target_filename}' width='320' height='180' /><br />";
				if($rs) {
					unlink($file) ;
				}
			}

			if(count(glob("{$root_pb}/$user/$time/*")) === 0) {
				$rs = rmdir("{$root_pb}/$user/$time") ;
				print "Delete : {$root_pb}/$user/$time ->"  . ( $rs ? "True" : "False" )  . "\n";
			}
		}

		if(count(glob("{$root_pb}/$user/*")) === 0) {
			$rs = rmdir("{$root_pb}/$user") ;
			print "Delete : {$root_pb}/$user ->"  . ( $rs ? "True" : "False" ) . "\n" ;
		}
	
	}

}else {
	print "Cannot connect FTP" ;
}
ftp_close($conn);

