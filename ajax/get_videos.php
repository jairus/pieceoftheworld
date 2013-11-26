<?php
require_once 'global.php';

if($_GET['action']=='get_videos'){
	if(isset($_GET['land_detail_id']) || isset($_GET['land_special_id'])){
		$sql = "SELECT `video` FROM `videos` WHERE `land_id`='".$_GET['land_detail_id']."'";
		$videos = dbQuery($sql, $_dblink);
		
		if(count($videos)){
			echo json_encode($videos);
		}else{
			$sql = "SELECT `video` FROM `videos_special` WHERE `land_special_id`='".$_GET['land_special_id']."'";
			$videos_special = dbQuery($sql, $_dblink);
			
			if(count($videos_special)){
				echo json_encode($videos_special);
			}else{
				echo "";
			}
		}
	}
	
	exit();
}
?>