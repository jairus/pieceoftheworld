<?php
require_once 'global.php';

if($_GET['land_id']){
	$sql = "SELECT MAX(`bid`) AS `bid` FROM `land_bids` WHERE `land_id`='".$_GET['land_id']."'";
	$r = dbQuery($sql, $_dblink);
	
	if(count($r)){
		echo json_encode($r);
	}else{
		echo "";
	}
}
?>