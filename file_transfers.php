<?php


function fileTransferToFTP() {

	$return_result = true;

	$ftp_server = GetValue("ftp_server"); // "10.14.2.52" ;
	$ftp_user = GetValue("ftp_user"); // "santorini" ;
	$ftp_pass = GetValue("ftp_pass"); // 'M0n$ter@santo' ;

	//$ftp_server = "10.14.2.52" ; // "10.14.2.52" ;
	//$ftp_user = "santorini" ; // "santorini" ;
	//$ftp_pass =  'M0n$ter@santo' ; // 'M0n$ter@santo' ;
	$root_pb = "/store/photobooth" ;
	$ftp_incomming = "/STR/incomming" ;

	$current_date = date("Ymd") ;

	$project_code = GetValue("project_code") ;
	$machine_code = GetValue("machine_code") ;

	echo "$ftp_server ; $ftp_user ;  \n";

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

?>
	<style type="text/css">
	#image {
	    transform-origin: top left; /* IE 10+, Firefox, etc. */
	    -webkit-transform-origin: top left; /* Chrome */
	    -ms-transform-origin: top left; /* IE 9 */
	}

	#image.rotate180 {
	    transform: rotate(180deg) translate(-100%,-100%);
	    -webkit-transform: rotate(180deg) translate(-100%,-100%);
	    -ms-transform: rotate(180deg) translateX(-100%,-100%);
	}
	</style>
<?php

	$conn = ftp_connect($ftp_server) or die("Could not connect");
	ftp_login($conn,$ftp_user,$ftp_pass);
	if($conn) {
		$mkdir = "{$ftp_incomming}/{$current_date}" ;
		write( "mkdir $mkdir");
		if(!@ftp_chdir ($conn,$mkdir)) {
			ftp_mkdir($conn, $mkdir);
		}

		foreach($files_list as $user => $value) {
			
			$mkdir_user = "{$mkdir}/{$user}" ;
			write( "mkdir $mkdir_user");
			if(!@ftp_chdir ($conn,$mkdir_user)) {
				ftp_mkdir($conn, $mkdir_user);
			}

			foreach($value as $time => $files ) {

				$seq = 1 ;
				foreach($files as $file ) {
					$str_seq = str_pad($seq++, 2, "0", STR_PAD_LEFT); 
					$filename = "{$project_code}{$machine_code}_{$time}_{$str_seq}.JPG";
					$target_filename = "{$mkdir_user}/{$filename}" ;
					$rs = ftp_put($conn, "{$target_filename}" ,$file, FTP_BINARY);
					//write("FTP from '{$file}' to '{$target_filename}'\n");
					$target_filename_inbox = str_replace("incomming", "inbox", $target_filename);
					write("FTP to <a href='http://10.14.2.51/photo{$target_filename_inbox}' target='blank'>{$target_filename}</a>");
					print "FTP File to : {$target_filename} ->" . ( $rs ? "True" : "False" ) . "\n" ;
					print "<a href='http://10.14.2.51/photo{$target_filename}' target='blank'>";
					print "<img src='http://10.14.2.51/photo{$target_filename}' width='320' height='180' id='image' class='rotate180'/><br />";
					print "</a>";
					if($rs) {
						unlink($file) ;
					}
				}

				if(count(glob("{$root_pb}/$user/$time/*")) === 0) {
					$rs = _sudo_rmdir("{$root_pb}/$user/$time") ;
					print "Delete : {$root_pb}/$user/$time ->"  . ( $rs ? "True" : "False" )  . "\n";
				}
			}

			if(count(glob("{$root_pb}/$user/*")) === 0) {
				$rs = _sudo_rmdir("{$root_pb}/$user") ;
				print "Delete : {$root_pb}/$user ->"  . ( $rs ? "True" : "False" ) . "\n" ;
			}
		
		}

	}else {
		print "Cannot connect FTP" ;
		$return_result = false;
	}
	ftp_close($conn);


	write("End FTP");
	return $return_result;

}

