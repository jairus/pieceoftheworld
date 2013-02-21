<?php
require_once 'global.php';
$conOptions = GetGlobalConnectionOptions();
$con = mysql_connect($conOptions['server'], $conOptions['username'], $conOptions['password']);
if (!$con) { die(''); }
mysql_select_db($conOptions['database'], $con);
$sql = "SELECT * FROM news WHERE 1 ORDER BY date_added DESC LIMIT 15";
$result = mysql_query($sql);
$news[ ] = array();
$index = 0;
while ($row = mysql_fetch_array($result)) {
	$news[$index++] = $row;
	$filename = '../images/thumbs/news_id_'.$row[0];
	if (!file_exists($filename) || filesize($filename) == 0) {
		$file = fopen($filename,'w');
		$success = fwrite($file, $row[2]);
		fclose($file);
		if ($success == false) {
			unlink($filename);
		}
	}
}
echo json_encode($news);
mysql_close($con);
?>