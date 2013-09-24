<?php
include_once("./lib/lib.inc.php");

if( isset($_GET['key'] )) {
	$key = $_GET['key'] ;
	print GetValue($key) ; 
}

