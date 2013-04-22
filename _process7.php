<?php
//create images from special land
require_once 'ajax/global.php';
function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
$sql = "select `id`, `picture`, `title` from `land_special` where 1";
//echo $sql; 
$r = dbQuery($sql, $_dblink);
$t = count($r);

for($i=0; $i<$t; $i++){
	
	if(trim($r[$i]['picture'])){
		@mkdir(dirname(__FILE__)."/_uploads2/specialland/".$r[$i]['id'], 0777);
		@mkdir(dirname(__FILE__)."/_uploads2/specialland/".$r[$i]['id']."/images", 0777);
		file_put_contents(dirname(__FILE__)."/_uploads2/specialland/".$r[$i]['id']."/images/image", $r[$i]['picture']);
		$sql = "delete from `pictures_special` where `land_special_id` = '".$r[$i]['id']."'";
		dbQuery($sql, $_dblink);
		$sql = "insert into `pictures_special` set 
			`picture` = 'http%3A//pieceoftheworld.co/_uploads2/specialland/".$r[$i]['id']."/images/image',
			`title` = '".mysql_real_escape_string($r[$i]['title'])."',
			`land_special_id`='".$r[$i]['id']."',
			`isMain`=1
			";
		dbQuery($sql, $_dblink);
	}
	
}





?>