<?php
require_once 'global.php';
$land_id = @$_GET['land_id'];
$conOptions = GetGlobalConnectionOptions();
$con = mysql_connect($conOptions['server'], $conOptions['username'], $conOptions['password']);
if (!$con) { die('[[]]'); }
mysql_select_db($conOptions['database'], $con);
$sql = "SELECT land_special_id, picture FROM land WHERE id=".$land_id;
$result = mysql_query($sql);
$row = null;
if ($result != null) {
	$row = mysql_fetch_array($result);
}
if ($row != null) {
	$filename = '../images/thumbs/land_id_'.$land_id;
	if (!file_exists($filename) || filesize($filename) == 0) {
		$file = fopen($filename,'w');
		$success = fwrite($file, $row[1]);
		fclose($file);
		if ($success == false) {
			unlink($filename);
		}
	}
	if ($row[0] != null) {
		$filename = '../images/thumbs/land_special_id_'.$row[0];
		if (!file_exists($filename) || filesize($filename) == 0) {
			$file = fopen($filename,'w');
			$success = fwrite($file, $row[1]);
			fclose($file);
			if ($success == false) {
				unlink($filename);
			}
		}
	}
}
echo '[[]]';
mysql_close($con);
?>