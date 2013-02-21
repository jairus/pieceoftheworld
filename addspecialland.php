<!doctype html>
<html lang="us">
<head>
<meta charset="utf-8">
<title>PieceoftheWorld</title>
<link href="css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="js/jquery-1.8.3.min.js" type="text/javascript"></script>
<script src="js/jquery-ui-1.9.2.custom.min.js" type="text/javascript"></script>
<style>
body {
	font: 75.5% "Trebuchet MS", sans-serif;
	margin: 30px;
}
.demoHeaders {
	margin-top: 2em;
}
#dialog-link {
	padding: .4em 1em .4em 20px;
	text-decoration: none;
	position: relative;
}
#dialog-link span.ui-icon {
	margin: 0 5px 0 0;
	position: absolute;
	left: .2em;
	top: 50%;
	margin-top: -8px;
}
#icons {
	margin: 0;
	padding: 0;
}
#icons li {
	margin: 2px;
	position: relative;
	padding: 4px 0;
	cursor: pointer;
	float: left;
	list-style: none;
}
#icons span.ui-icon {
	float: left;
	margin: 0 4px;
}
.cpanelwnd {
	background: none !important;
	top: 15%;
	left: 60%;
}
.change_radio_button_style {
	border-top-left-radius: 10px !important;
	border-top-right-radius: 10px !important;
	border-bottom-left-radius: 0px !important;
	border-bottom-right-radius: 0px !important;
}
.change_titlebar_style {
	border-top-left-radius: 10px !important;
	border-top-right-radius: 10px !important;
	border-bottom-left-radius: 0px !important;
	border-bottom-right-radius: 0px !important;
	padding-bottom: 0px !important;
}
.change_dialog_style {
	border-top-left-radius: 0px !important;
	border-top-right-radius: 0px !important;
	border-bottom-left-radius: 10px !important;
	border-bottom-right-radius: 10px !important;
	padding: 7px !important;
	padding-top: 0px !important;
}
.dialog_body {
	border-top-left-radius: 0px !important;
	border-top-right-radius: 0px !important;
	border-bottom-left-radius: 10px !important;
	border-bottom-right-radius: 10px !important;
	background: #FFF !important;
	padding: 5px !important;
}
.change_tab_style {
	border: 0px !important;
	background: #FFF !important;
}
.change_tab_ul_style {
	border: 0px !important;
	background: #FFF !important;
}
.tab_body {
	background: #F5FFDA !important;
	border: 1px solid #D4FBDA !important;
	border-top-left-radius: 0px !important;
	border-top-right-radius: 0px !important;
	border-bottom-left-radius: 10px !important;
	border-bottom-right-radius: 10px !important;
	padding: 10px !important;
	padding-top: 0px !important;
	color: #1482b4 !important;
}
.tab_body h3 {
	font-size: 12px !important;
	font-weight: bold !important;
	color: #1482b4 !important;
	padding: 0px !important;
	padding-bottom: 3px !important;
	border-bottom: 1px solid #1482b4 !important;
}
.news {
}
.news ul {
	list-style: none !important;
	padding: 0px !important;
}
.news li {
	margin-bottom: -1px !important;
	margin-top: 4px !important;
	padding: 5px !important;
	background: #FFFFFF !important;
	border: 1px solid #8ABE43 !important;
	border-top-left-radius: 5px !important;
	border-top-right-radius: 5px !important;
	border-bottom-left-radius: 5px !important;
	border-bottom-right-radius: 5px !important;
}
.img {
	padding: 5px !important;
	background: #FFFFFF !important;
	border: 1px solid #8ABE43 !important;
	border-top-left-radius: 5px !important;
	border-top-right-radius: 5px !important;
	border-bottom-left-radius: 5px !important;
	border-bottom-right-radius: 5px !important;
}
</style>
</head>

<body style="cursor: auto; background-color: white;">
<?php
error_reporting(E_ERROR);
//print_r($_POST);
$error = true;
$allowedExts = array("jpg", "jpeg", "gif", "png");
$extension = end(explode(".", $_FILES["picture_name"]["name"]));
if ((($_FILES["picture_name"]["type"] == "image/gif")
|| ($_FILES["picture_name"]["type"] == "image/jpeg")
|| ($_FILES["picture_name"]["type"] == "image/png")
|| ($_FILES["picture_name"]["type"] == "image/pjpeg"))
&& ($_FILES["picture_name"]["size"] < 102400)
&& in_array($extension, $allowedExts))
{
	if ($_FILES["picture_name"]["error"] > 0) {
		echo "Error: " . $_FILES["picture_name"]["error"] . "<br>";
	}
	else {
	/*
		$size = getimagesize($_FILES["picture_name"]["tmp_name"]);
		echo "Upload: " . $_FILES["picture_name"]["name"] . "<br>";
		echo "Type: " . $_FILES["picture_name"]["type"] . "<br>";
		echo "Size: " . ($_FILES["picture_name"]["size"] / 1024) . " kB<br>";
		echo "Width: " . $size[0] . " pixels<br>";
		echo "Height: " . $size[1] . " pixels<br>";
		echo "Stored in: " . $_FILES["picture_name"]["tmp_name"];
	*/
		$error = false;
	}
}
else {
	if ($_FILES["picture_name"]["size"] > 102400) {
		echo "Error: Image file size can not be greater than 100 Kb";
	}
	else if ($size[0] > 97 || $size[1] > 127) {
		echo "Error: Image file dimensions can not be greater than 97 x 127 pixels";
	}
	else {
		echo "Error: Invalid image file type";
	}
}
?>
<?php
if ($error == false) {
require_once 'ajax/global.php';
$conOptions = GetGlobalConnectionOptions();
$con = mysql_connect($conOptions['server'], $conOptions['username'], $conOptions['password']);
if (!$con) { die(''); }
mysql_select_db($conOptions['database'], $con);
?>
<?php
$plots = explode("_", @$_POST['land_name']);
$plots = array_unique($plots);
?>
<h1>Add special land</h1>
<p>
<?php
$unboughtStandardPlot = false;
$unboughtSpecialArea = false;
$boughtStandardPlot = false;
$boughtSpecialArea = false;

//echo "The following plots have been selected for insertion:";
//echo "<ul>";

if (sizeof($plots) == 1) {
	$plotCo =  explode("-", $plots[0]);
	$sql = "SELECT * FROM land WHERE x=".$plotCo[0]." AND y=".$plotCo[1];
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	if ($row && $row[4] != 3) {
		if ($row[3] == 0) {
//			echo "<li><font color=red>Plot Id (".$plots[0].") (Standard Plot - Not Available)</font></li>";
			$boughtStandardPlot = true;
		}
		else {
			$sql = "SELECT * FROM land WHERE land_special_id=".$row[3];
			$result = mysql_query($sql);
			while ($row = mysql_fetch_array($result)) {
//				echo "<li><font color=red>Plot Id (".$row[1]."-".$row[2].") (Special Place - Not Available)</font></li>";
			}
			$boughtSpecialArea = true;
		}
	}
	else {
		if ($row[3] == 0) {
//			echo "<li>Plot Id (".$plots[0].") (Standard Plot - Available for Purchase)</li>";
			$unboughtStandardPlot = true;
		}
		else {
			$sql = "SELECT * FROM land WHERE land_special_id=".$row[3];
			$result = mysql_query($sql);
			while ($row = mysql_fetch_array($result)) {
//				echo "<li>Plot Id (".$row[1]."-".$row[2].") (Special Place - Available for Purchase)</li>";
			}
			$unboughtSpecialArea = true;
		}
	}
}
else if (sizeof($plots) == 2) {
	$plotCoLT =  explode("-", $plots[0]);
	$plotCoRB =  explode("-", $plots[1]);
	for ($i = $plotCoLT[0]; $i <= $plotCoRB[0]; $i++) {
		for ($j = $plotCoLT[1]; $j <= $plotCoRB[1]; $j++) {
			$sql = "SELECT * FROM land WHERE x=".$i." AND y=".$j;
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
			if ($row && $row[4] != 3) {
				if ($row[3] == 0) {
//					echo "<li><font color=red>Plot Id (".$i."-".$j.") (Standard Plot - Not Available)</font></li>";
					$boughtStandardPlot = true;
				}
				else {
//					echo "<li><font color=red>Plot Id (".$i."-".$j.") (Special Place - Not Available)</font></li>";
					$boughtSpecialArea = true;
				}
			}
			else {
				if ($row[4] == 3) {
//					echo "<li>Plot Id (".$i."-".$j.") (Special Place - Available for Purchase)</li>";
					$unboughtSpecialArea = true;
				}
				else {
//					echo "<li>Plot Id (".$i."-".$j.") (Standard Plot - Available for Purchase)</li>";
					$unboughtStandardPlot = true;
				}
			}
		}
	}
}
else {
	echo "<li><font color=red>Unexpected input found.</font></li>";
}
//echo "</ul>";

//echo $unboughtStandardPlot ." - ". $unboughtSpecialArea ." - ". $boughtStandardPlot ." - ". $boughtSpecialArea;

$mixedArea = 
			($unboughtStandardPlot && $unboughtSpecialArea) ||
			($unboughtStandardPlot && $boughtStandardPlot) ||
			($unboughtStandardPlot && $boughtSpecialArea) ||
			($unboughtSpecialArea && $boughtStandardPlot) ||
			($unboughtSpecialArea && $boughtSpecialArea) ||
			($boughtStandardPlot && $boughtSpecialArea);

if ($mixedArea) {
	echo "<h3><font color=red>ERROR: Mixed area can not be processed.</font></h3>";
}
else if ($boughtStandardPlot || $boughtSpecialArea) {
	echo "<h3><font color=red>Note: This area has already been purchased.</font></h3>";
}
else {
	$image = $_FILES["picture_name"]["tmp_name"];
	$owner_user_id = 3;
	$title = @$_POST['title_name'];
	$detail = @$_POST['detail_name'];
	$picture = mysql_real_escape_string(file_get_contents($image));
	// Load image content
	//$image = $_FILES["picture_name"]["tmp_name"];
	//$fp = fopen($image, 'r');
	//$content = fread($fp, filesize($image));
	//$content = addslashes($content);
	//fclose($fp);	
	// Insert Special Land record in database
	echo "Inserting special land in database...<br>";
	$sql = "INSERT INTO land_special (owner_user_id, title, detail, picture) VALUES (".$owner_user_id.", '".$title."', '".$detail."', '".$picture."')";
	$result = mysql_query($sql);
	$land_special_id = mysql_insert_id();
	
	if ($land_special_id == 0 || $land_special_id == false) {
		echo "Error: Record insertion failed for 'land_spacial'<br>";
	}
	else {
		// Insert Plots in database
		echo "Inserting plots in database...<br>";
		if (sizeof($plots) == 1) {
			$plotCo =  explode("-", $plots[0]);
			$sql = "INSERT INTO land (x, y, land_special_id, owner_user_id, title, detail, picture) VALUES (".$plotCo[0].", ".$plotCo[1].", ".$land_special_id.", ".$owner_user_id.", '".$title."', '".$detail."', '".$picture."')";
			$result = mysql_query($sql);
			if ($result == TRUE) {
				echo "Success: Record insertion succeeded for 'land' x = ".$plotCo[0]." and y = ".$plotCo[1]."<br>";
			}
			else {
				echo "Error: Record insertion failed for 'land' x = ".$plotCo[0]." and y = ".$plotCo[1]."<br>";
			}
		}
		if (sizeof($plots) == 2) {
			$plotCoLT =  explode("-", $plots[0]);
			$plotCoRB =  explode("-", $plots[1]);
			for ($i = $plotCoLT[0]; $i <= $plotCoRB[0]; $i++) {
				for ($j = $plotCoLT[1]; $j <= $plotCoRB[1]; $j++) {
					$sql = "INSERT INTO land (x, y, land_special_id, owner_user_id, title, detail, picture) VALUES (".$i.", ".$j.", ".$land_special_id.", ".$owner_user_id.", '".$title."', '".$detail."', '".$picture."')";
					$result = mysql_query($sql);
					if ($result == TRUE) {
						echo "Success: Record insertion succeeded for 'land' x = ".$i." and y = ".$j."<br>";
					}
					else {
						echo "Error: Record insertion failed for 'land' x = ".$i." and y = ".$j."<br>";
					}
				}
			}
		}
	}
}
?>	
</p>
<?php
mysql_close($con);
}
?>
</body>
</html>
