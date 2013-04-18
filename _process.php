<?php
require_once 'ajax/global.php';
function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
$foldername = date("Ymd")."_".microtime_float();

$sql = "select `id`, `title`, `detail`, `folder` from `land` where land_detail_id='' order by `id` asc";
//echo $sql; 
$r = dbQuery($sql);

$t = count($r);

$ident = array();

for($i=0; $i<$t; $i++){
	$id = md5($r[$i]['title'].$r[$i]['detail']);

	if(!$ident[$id]){
		$ident[$id] = array();
		$sql = "select * from `land` where `id`='".mysql_real_escape_string($r[$i]['id'])."'";
		$data = dbQuery($sql);	
		$sql = "insert into `land_detail` set 
			`title` = '".mysql_real_escape_string($data[0]['title'])."',
			`detail` = '".mysql_real_escape_string($data[0]['detail'])."',
			`land_owner` = '".mysql_real_escape_string($data[0]['land_owner'])."',
			`useremail` = '".mysql_real_escape_string($data[0]['useremail'])."',
			`folder` = '".mysql_real_escape_string($data[0]['folder'])."',
			`email_resent` = '".mysql_real_escape_string($data[0]['email_resent'])."',
			`picture` = '".mysql_real_escape_string($data[0]['picture'])."'
		";
		$insert_id = dbQuery($sql);
		$insert_id = $insert_id['mysql_insert_id'];
		$ident[$id]['insert_id'] = $insert_id;
	}
	$sql = "update `land` set `land_detail_id`='".$ident[$id]['insert_id']."' where `id`='".mysql_real_escape_string($r[$i]['id'])."'";
	dbQuery($sql);
	echo ($i+1)." of ".$t.": ".$r[$i]['id']." \n";
}

/*
d:
cd xampp/htdocs/pieceoftheworld.co
*/
//echo "<pre>";
//print_r($r);




?>