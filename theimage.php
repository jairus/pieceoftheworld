<?php
require_once 'ajax/global.php';

if(trim($_GET['id'])){
	$sql = "select `picture` from `land` where `id`='".mysql_escape_string($_GET['id'])."'";
	$r = dbQuery($sql);
	$data = $r[0]['picture'];
	echo "<img src='data:image;base64,".base64_encode($data)."' />";
}



?>