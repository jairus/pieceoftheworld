<!doctype html>
<html lang="us">
<head>
<meta charset="utf-8">
<title>thebrowngiftbox.com</title>
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
require_once 'ajax/global.php';
$conOptions = GetGlobalConnectionOptions();
$con = mysql_connect($conOptions['server'], $conOptions['username'], $conOptions['password']);
if (!$con) { die(''); }
mysql_select_db($conOptions['database'], $con);
$sql = "SELECT * FROM news WHERE id=".@$_GET['id'];
$result = mysql_query($sql);
$news[ ] = array();
$index = 0;
if ($row = mysql_fetch_array($result)) {
	$filename = 'images/thumbs/news_id_'.$row[0];
	if (!file_exists($filename) || filesize($filename) == 0) {
		$file = fopen($filename,'w');
		$success = fwrite($file, $row[2]);
		fclose($file);
		if ($success == false) {
			unlink($filename);
		}
	}
	echo "<h1>".$row[1]."</h1>";
	echo "<p>".$row[3]."</p>";
}
mysql_close($con);
?>
</body>
</html>
