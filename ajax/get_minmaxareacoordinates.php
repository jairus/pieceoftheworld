<?php
require_once 'global.php';
$X = @$_GET['x'];
$Y = @$_GET['y'];
$sql1 = "SELECT land_special_id FROM land WHERE x=".$X." AND y=".$Y;

$row = dbQuery($sql1, $_dblink);
$row = $row[0];
$sql2 = "SELECT 
		MIN( x ) AS `minX`,
		MAX( x ) AS `maxX`,
		MIN( y ) AS `minY`,
		MAX( y ) AS `maxY` 
		FROM `land` 
		WHERE 
		`land_special_id`='".$row['land_special_id']."'";
$row = dbQuery($sql2, $_dblink);
$row = $row[0];
if($_GET['print']){
	echo "<pre>";
	echo $sql1;
	echo $sql2;
	echo "<hr>";
	print_r($row);
	echo "/<pre>";
	exit();
}
echo json_encode($row);

exit();

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