<?php
require_once 'ajax/global.php';
function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
$sql = "select `id`, `folder` from `land_detail` where `folder`<>'' ";
//echo $sql; 
$r = dbQuery($sql, $_dblink);
$t = count($r);

for($i=0; $i<$t; $i++){
	$uploads_dir = dirname(__FILE__).'/_uploads/'.$r[$i]['folder'];
	$post = unserialize(@file_get_contents($uploads_dir."/post.txt"));
	
	
	$title = utf8_decode($post['title']);
	$detail = utf8_decode($post['detail']);
	
	if(!trim($title)){
		$title = utf8_decode($post['title_name']);
	}
	if(!trim($detail)){
		$detail = utf8_decode($post['detail_name']);
	}
	echo ($i+1).": ".$uploads_dir." ... ";
	$land_owner = utf8_decode($post['land_owner']);
	
	$sql = "update `land_detail` set
		`title` = '".mysql_real_escape_string($title)."',
		`detail` = '".mysql_real_escape_string($detail)."',
		`land_owner` = '".mysql_real_escape_string($land_owner)."'
		where `id`='".$r[$i]['id']."'
	";
	dbQuery($sql, $_dblink);
	echo "done \n";
}




?>