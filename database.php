<?php
	$db_server = "localhost";	// change this to your database server
	$db_user = "root";			// change this to your database username
	$db_pass = "";			// change this to your database password
	$db_name = "connectaru";		//database namin
	$db_port = '3306';			// change this to your dabase port
	
	try {
		$conn = mysqli_connect($db_server, $db_user, 
								$db_pass, $db_name, $db_port);
	} catch (mysqli_sql_exception){
		echo "Connection Unsuccessful";
	}
?>