<?php
//set users
require_once 'ajax/global.php';
function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
$sql = "select distinct `useremail` from `land_detail` where 1";
//echo $sql; 
$r = dbQuery($sql, $_dblink);
$t = count($r);

for($i=0; $i<$t; $i++){
	$sql = "insert into `web_users`
		set 
		`useremail` = '".mysql_real_escape_string($r[$i]['useremail'])."',
		`password` = '".md5("password")."',
		`plain_pass` = 'password'
	";
	dbQuery($sql, $_dblink);
}




?>