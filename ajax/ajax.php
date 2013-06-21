<?php
session_start();

if($_GET['action']=='createsession'){
	$_SESSION['trailer'] = 1;
	
	exit();
}
?>