<?php
//phpinfo() ;



include_once("./lib/lib.inc.php");

foreach( $_GET as $key => $value ) {
	SetValue($key, $value) ;
}
