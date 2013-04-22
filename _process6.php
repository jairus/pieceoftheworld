<?php
//utf8 decode land_special
require_once 'ajax/global.php';
function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
$sql = "select `id`, `title`, `detail` from `land_special` where 1";
//echo $sql; 
$r = dbQuery($sql, $_dblink);
$t = count($r);

for($i=0; $i<$t; $i++){
	$sql = "select land_detail.title, land_detail.detail from `land` left join land_detail on (land.land_detail_id=land_detail.id) where `land_special_id`='".$r[$i]['id']."' ";
	$rt = dbQuery($sql, $_dblink);
	$sql = "update `land_special` set
		`title` = '".mysql_real_escape_string((($rt[0]['title'])))."',
		`detail` = '".mysql_real_escape_string((($rt[0]['detail'])))."'
		where `id`='".$r[$i]['id']."'
	";
	
	//echo $sql;
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