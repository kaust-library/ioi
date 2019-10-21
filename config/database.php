<?php
	//Initiate the database connection, the user and password are defined in constants.php
	$ioi = new mysqli("localhost", MYSQL_USER, MYSQL_PW, "ioi");
	
	ini_set('mbstring.internal_encoding','UTF-8');
	ini_set('mbstring.func_overload',7);	
	ini_set('default_charset', 'UTF-8');
	
	$ioi->set_charset("utf8");
?>