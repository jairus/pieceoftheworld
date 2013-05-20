<?php
require_once 'global.php';

if($_GET['action']=='get_videos'){
	if(isset($_GET['land_detail_id'])){
		$sql = "SELECT `video` FROM `videos` WHERE `land_id`='".$_GET['land_detail_id']."'";
		$videos = dbQuery($sql, $_dblink);
		
		if(count($videos)){
			echo json_encode($videos);
		}else{
			echo "";
		}
	}
}
?>