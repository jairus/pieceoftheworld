<?php
require_once 'ajax/global.php';
function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
$sql = "select `id`, `title`, `detail` from `land_detail` where `folder`=''";
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
	
	
	/*
	$uploads_dir = dirname(__FILE__).'/_uploads/'.$r[$i]['folder'];
	$post = unserialize(@file_get_contents($uploads_dir."/post.txt"));
	
	echo ($i+1).": ".$uploads_dir." ... ";
	$land_owner = utf8_decode($post['land_owner']);
	$email = $post['email'];
	$title = utf8_decode($post['title']);
	$detail = utf8_decode($post['detail']);
	$sql = "update `land_detail` set
		`useremail` = '".mysql_real_escape_string($email)."',
		`title` = '".mysql_real_escape_string($title)."',
		`detail` = '".mysql_real_escape_string($detail)."',
		`land_owner` = '".mysql_real_escape_string($land_owner)."'
		where `id`='".$r[$i]['id']."'
	";
	dbQuery($sql, $_dblink);
	echo "done \n";
	*/
}




?>