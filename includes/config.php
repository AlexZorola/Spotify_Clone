<?php
	ob_start(); //Outout Buffering When a php page loads, it sends data to the server in pieces, wait until we have all the data, before sending it to the server
	session_start();

	$timezone = date_default_timezone_set("America/Chicago");

	$con = mysqli_connect("localhost", "root", "", "otl");

	if(mysqli_connect_errno()) {
		echo "Failed to connect: " . mysqli_connect_errno();
	}
?>