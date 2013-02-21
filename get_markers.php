<?php
require_once 'global.php';
$type = @$_GET['type'];
$land_special_id = @$_GET['land_special_id'];
$x1 = @$_GET['x1'];
$y1 = @$_GET['y1'];
$x2 = @$_GET['x2'];
$y2 = @$_GET['y2'];
$conOptions = GetGlobalConnectionOptions();
$con = mysql_connect($conOptions['server'], $conOptions['username'], $conOptions['password']);
if (!$con) { die('[[]]'); }
mysql_select_db($conOptions['database'], $con);
$sql = "";
if (!empty($_GET)) {
	if ($type == 'special') {
		//$sql = "SELECT * FROM land_special WHERE 1";
		//$sql = "SELECT land_special.id AS id, owner_user_id, title, detail, picture, email FROM land_special, user WHERE (land_special.owner_user_id = user.id)";
		//$sql = "SELECT land_special.id AS id, owner_user_id, title, detail, picture, email FROM land_special LEFT JOIN user ON (land_special.owner_user_id = user.id) WHERE 1";
		//$sql = "SELECT land_special.id AS id, owner_user_id, title, detail, email FROM land_special LEFT JOIN user ON (land_special.owner_user_id = user.id) WHERE 1";
		//$sql = "SELECT land_special.id AS id, owner_user_id, title, detail, email, (SELECT avg(x) AS x FROM land WHERE land_special_id=land_special.id LIMIT 1), (SELECT avg(y) AS y FROM land WHERE land_special_id=land_special.id LIMIT 1) FROM land_special LEFT JOIN user ON (land_special.owner_user_id = user.id) WHERE 1";
		$sql = "SELECT land_special.id AS id, owner_user_id, title, detail, email, (SELECT avg(x) FROM land WHERE land_special_id=land_special.id LIMIT 1) AS x, (SELECT avg(y) FROM land WHERE land_special_id=land_special.id LIMIT 1) AS y FROM land_special LEFT JOIN user ON (land_special.owner_user_id = user.id) WHERE 1";
	}
	else if (!empty($land_special_id)) {
		$sql = "SELECT avg(x) AS x, avg(y) AS y FROM land WHERE land_special_id=".$land_special_id;
	}
	else {
		//$sql = "SELECT * FROM land WHERE ";
		//$sql = "SELECT land.id AS id, x, y, land_special_id, owner_user_id, title, detail, picture, email FROM land LEFT JOIN user ON (land.owner_user_id = user.id) WHERE ";
		$sql = "SELECT land.id AS id, x, y, land_special_id, owner_user_id, title, detail, email, folder FROM land LEFT JOIN user ON (land.owner_user_id = user.id) WHERE ";
		for ($x = $x1; $x <= $x2; $x++) {
			for ($y = $y1; $y <= $y2; $y++) {
				if (!($x == $x1 && $y == $y1)) {
					$sql .= "OR ";
				}
				$sql .= "(x=".$x." AND y=".$y.") ";
			}
		}
	}
}
else {
	//$sql = "SELECT x, y, land_special_id FROM land WHERE 1";
	$sql = "SELECT x, y, land_special_id, email FROM land LEFT JOIN user ON (land.owner_user_id = user.id) WHERE 1";
}
$result = mysql_query($sql);
$markers[ ] = array();
$index = 0;
while ($row = mysql_fetch_array($result)) {
	$markers[$index++] = $row;
	/*
	if (!empty($_GET) && empty($land_special_id)) {
		if ($type == 'special') {
			$filename = '../images/thumbs/land_special_id_'.$row[0];
		}
		else {
			$filename = '../images/thumbs/land_id_'.$row[0];
		}
		if (!file_exists($filename) || filesize($filename) == 0) {
			$file = fopen($filename,'w');
			$success = false;
			if ($type == 'special') {
				$success = fwrite($file, $row[4]);	// reading land_special table
			}
			else {
				$success = fwrite($file, $row[7]);	// reading land table
			}
			fclose($file);
			if ($success == false) {
				unlink($filename);
			}
		}
	}
	*/
}
$uploads_dir = dirname(__FILE__).'/../_uploads/'.$markers['folder'];
$post = unserialize(file_get_contents($uploads_dir."/post.txt"));
if(isset($_GET['print'])){
	echo "<pre>";
	print_r($post);
	print_r($markers);
	echo "</pre>";
}
else{
	
	echo json_encode($markers);
}
mysql_close($con);
?>