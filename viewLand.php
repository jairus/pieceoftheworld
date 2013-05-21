<?php
@session_start();
require_once 'ajax/global.php';

if($_GET['landId']){
	$sql = "select * from `land` where `id`='".mysql_real_escape_string($_GET['landId'])."'";
	$land = dbQuery($sql);
	$land = $land[0];
	print_r($land);
}
?>