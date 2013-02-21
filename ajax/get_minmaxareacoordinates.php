<?php
require_once 'global.php';
$X = @$_GET['x'];
$Y = @$_GET['y'];
$conOptions = GetGlobalConnectionOptions();
$con = mysql_connect($conOptions['server'], $conOptions['username'], $conOptions['password']);
if (!$con) { die('[[]]'); }
mysql_select_db($conOptions['database'], $con);
$sql = "";
if (empty($_GET)) { die('[[]]'); }
$sql = "SELECT land_special_id FROM land WHERE x=".$X." AND y=".$Y;
$result = mysql_query($sql);
if ($row = mysql_fetch_array($result)) {
	$sql = "SELECT MIN( x ) AS minX, MAX( x ) AS maxX, MIN( y ) AS minY, MAX( y ) AS maxY FROM land WHERE land_special_id=".$row[0];
	$result = mysql_query($sql);
	if ($row = mysql_fetch_array($result)) {
		echo json_encode($row);
	}
}
mysql_close($con);
?>