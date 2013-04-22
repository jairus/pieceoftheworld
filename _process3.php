<?php
require_once 'ajax/global.php';
function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
$sql = "select `id`, `title`, `detail` from `land_special` where `folder`=''";
//echo $sql; 
$r = dbQuery($sql, $_dblink);
$t = count($r);

for($i=0; $i<$t; $i++){
	$sql = "update `land_detail` set
		`title` = '".mysql_real_escape_string(utf8_decode($r[$i]['title']))."',
		`detail` = '".mysql_real_escape_string(utf8_decode($r[$i]['detail']))."'
		where `id`='".$r[$i]['id']."'
	";
	dbQuery($sql, $_dblink);
	
}




?>