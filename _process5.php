<?php
//set owner ids
require_once 'ajax/global.php';
function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
$sql = "select `id`, `useremail` from `web_users` where 1";
//echo $sql; 
$r = dbQuery($sql, $_dblink);
$t = count($r);

for($i=0; $i<$t; $i++){
	$sql = "update `land`
	set 
	`web_user_id` = '".$r[$i]['id']."'
	where `land_detail_id` in (select `id` from `land_detail` where `useremail`='".mysql_real_escape_string($r[$i]['useremail'])."')
	";
	dbQuery($sql, $_dblink);
}




?>