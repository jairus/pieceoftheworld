<?php
ini_set ("display_errors", "1");
//error_reporting(E_ALL);
error_reporting(E_ERROR);
?>
<?php
$pass = @$_GET['pass'];
$land = @$_GET['land'];
$useremail = @$_GET['useremail'];
$step = @$_GET['step'];
$title = @$_GET['title_name'];
$detail = @$_GET['detail_name'];
if ($step == null) {
	$step = @$_POST['step'];
}
if ($step == 2) {
	$pass = @$_POST['pass'];
	$land = @$_POST['land'];
	$useremail = @$_POST['useremail'];
	$title = @$_POST['title_name'];
	$detail = @$_POST['detail_name'];
}
$picture;
?>
<?php
function mail_simple($to, $subject, $message, $from) {
	// $file should include path and filename
	$uid = md5(uniqid(time()));
	$from = str_replace(array("\r", "\n"), '', $from); // to prevent email injection
	$header = "From: ".$from."\r\n"
      ."MIME-Version: 1.0\r\n"
      ."Content-Type: text/html\r\n"; 
	return mail($to, $subject, $message, $header);  
}
function mail_attachment($to, $subject, $message, $from, $file, $filename) {
	// $file should include path and filename
	$file_size = filesize($file);
	$content = chunk_split(base64_encode(file_get_contents($file))); 
	$uid = md5(uniqid(time()));
	$from = str_replace(array("\r", "\n"), '', $from); // to prevent email injection
	$header = "From: ".$from."\r\n"
      ."MIME-Version: 1.0\r\n"
      ."Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n"
      ."This is a multi-part message in MIME format.\r\n" 
      ."--".$uid."\r\n"
      ."Content-type:text/plain; charset=iso-8859-1\r\n"
      ."Content-Transfer-Encoding: 7bit\r\n\r\n"
      .$message."\r\n\r\n"
      ."--".$uid."\r\n"
      ."Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"
      ."Content-Transfer-Encoding: base64\r\n"
      ."Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n"
      .$content."\r\n\r\n"
      ."--".$uid."--"; 
	return mail($to, $subject, "", $header);
}
?>
<!doctype html>
<html lang="us">
<head>
<meta charset="utf-8">
<title>PieceoftheWorld</title>
<link href="css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="js/jquery-1.8.3.min.js" type="text/javascript"></script>
<script src="js/jquery-ui-1.9.2.custom.min.js" type="text/javascript"></script>
<link href="css/main.css" rel="stylesheet">
</head>

<body style="cursor: auto; background-color: white;">
<?php
if ($step == 2) {
	$error = true;
	$error_msg = "";
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
			$error_msg = "Error: " . $_FILES["picture_name"]["error"];
		}
		else {
			$error = false;
		}
	}
	else {
		if ($_FILES["picture_name"]["size"] > 102400) {
			$error_msg = "Error: Image file size can not be greater than 100 Kb";
		}
		else if ($size[0] > 97 || $size[1] > 127) {
			$error_msg = "Error: Image file dimensions can not be greater than 97 x 127 pixels";
		}
		else {
			$error_msg = "Error: Invalid image file type.";
		}
	}
	if ($error == true) {
		$step = 1;
		echo ('<font color=red><center><h3>'.$error_msg.'</h3></center></font>');
	}
	else {
		$image = $_FILES["picture_name"]["tmp_name"];
		//$picture = mysql_real_escape_string(file_get_contents($image)); // Move this after establishing db connection
	}
}
if ($step == 1) {
	if ($pass != "A631CD74-1D21-40b1-8602-346611127127") {
		die('<center><h2>You are not authorized to access this page.</h2></center>');
	}
	echo '<center><h2>Thank you for your purchase</h2></center>';
	echo '<center><h4>Set a title, text and picture to be shown at your very own Piece of the World</h4></center>';
?>
<center>
	<form enctype="multipart/form-data" method="post" action="ppc.php">
		<input type="hidden" name="step" value="<?php echo $step; ?>">
		<input type="hidden" name="pass" value="<?php echo $pass; ?>">
		<input type="hidden" name="land" value="<?php echo $land; ?>">
		<input type="hidden" name="step" value="2">
		<table>
			<tr>
				<td><strong>Email<font color=red>*</font>&nbsp;</strong></td>
				<td colspan="2"><input type="text" name="useremail" value="<?php echo $useremail; ?>" maxlength="50" style="width: 100%;"></td>
			</tr>
			<tr>
				<td><strong>Title<font color=red>*</font>&nbsp;</strong></td>
				<td colspan="2"><input type="text" id="title" name="title_name" maxlength="50" style="width: 100%;"></td>
			</tr>
			<tr>
				<td style="vertical-align:top;"><strong>Text&nbsp;</strong></td>
				<td colspan="2"><textarea id="detail" name="detail_name" maxlength="150" style="width: 100%; height:75px;"></textarea></td>
			</tr>
			<tr>
				<td><strong>Picture</strong></td>
				<td><input type="file" id="picture" name="picture_name" style="width: 100%;"></td>
				<td>(Max 100kb)</td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2" align="right"><br><input type="submit" id="button" name="button_name" value="  Submit  "></td>
			</tr>
		</table>
	</form>
</center>	
<?php
}
else {
require_once 'ajax/global.php';
$conOptions = GetGlobalConnectionOptions();
$con = mysql_connect($conOptions['server'], $conOptions['username'], $conOptions['password']);
if (!$con) { die('<center><h2>Database connection error.</h2></center>'); }
mysql_select_db($conOptions['database'], $con);
if ($image != null) {
	$picture = mysql_real_escape_string(file_get_contents($image));
}
?>
<?php
$land_special_id = -1;	
$plot_list = array();	
$owner_user_id = 0;
$plots = explode("_", $land);
$plots = array_unique($plots);
if ($pass != "A631CD74-1D21-40b1-8602-346611127127") {
	mysql_close($con);
	die('<center><h2>You are not authorized to access this page.</h2></center>');
}
if ($useremail == null) {
	//mysql_close($con);
	//die('<center><h2>Can not proceed without a valid email.</h2></center>');
	$owner_user_id = GetTmpUserId();
}
else {
	// Check if email is valid
	$sql = "SELECT * FROM user WHERE LOWER(email)=LOWER('".$useremail."')";
	$result = mysql_query($sql);
	if ($result == null) {
		//mysql_close($con);
		//die('<center><h2>Can not proceed without a valid email.</h2></center>');
		$owner_user_id = GetTmpUserId();
	}
	else {
		$row = mysql_fetch_array($result);
		if ($row == null) {
			//mysql_close($con);
			//die('<center><h2>Can not proceed without a valid email.</h2></center>');
			$owner_user_id = GetTmpUserId();
		}
		else {
			$owner_user_id = $row[0];
		}	
	}
}
if ($land == null) {
	mysql_close($con);
	die('<center><h2>Can not proceed without a valid plot.</h2></center>');
}
else if (sizeof($plots) == 1) {
	$plotCo =  explode("-", $plots[0]);
	//echo ($plotCo[0]."-".$plotCo[1]."<br>");
	$sql = "SELECT * FROM land WHERE x=".$plotCo[0]." AND y=".$plotCo[1];
	$result = mysql_query($sql);
	$row = null;
	if ($result != null) {
		$row = mysql_fetch_array($result);
		if ($row != null) {
			if ($land_special_id == -1) {
				$land_special_id = $row[3];
			}
			if (unlink("images/thumbs/land_special_id_".$row[3]) == true) {
				//echo "images/thumbs/land_special_id_".$row[3]." deleted<br>";
			}
			// update record
			//$sql = "UPDATE land SET owner_user_id=".$owner_user_id." WHERE x=".$plotCo[0]." AND y=".$plotCo[1];
			$sql = "UPDATE land SET owner_user_id=".$owner_user_id.", title='".$title."', detail='".$detail."', picture='".$picture."' WHERE x=".$plotCo[0]." AND y=".$plotCo[1];
			if (unlink("images/thumbs/land_id_".$row[0])) {
				//echo "images/thumbs/land_id_".$row[0]." deleted<br>";
			}
		}
		else {
			// insert record
			$sql = "INSERT INTO land (x, y, owner_user_id, title, detail, picture) VALUES (".$plotCo[0].", ".$plotCo[1].", ".$owner_user_id.", '".$title."', '".$detail."', '".$picture."')";
		}
		$result = mysql_query($sql);
		if ($result == true) {
		}
		else {
		}
	}
	$plot_list[] = "(".$plotCo[0]."-".$plotCo[1].")";
}
else {
	$plotCoLT =  explode("-", $plots[0]);
	$plotCoRB =  explode("-", $plots[1]);
	for ($i = $plotCoLT[0]; $i <= $plotCoRB[0]; $i++) {
		for ($j = $plotCoLT[1]; $j <= $plotCoRB[1]; $j++) {
			//echo ($i."-".$j."<br>");
			$sql = "SELECT * FROM land WHERE x=".$i." AND y=".$j;
			$result = mysql_query($sql);
			$row = null;
			if ($result != null) {
				$row = mysql_fetch_array($result);
				if ($row != null) {
					if ($land_special_id == -1) {
						$land_special_id = $row[3];
					}
					if (unlink("images/thumbs/land_special_id_".$row[3]) == true) {
						//echo "images/thumbs/land_special_id_".$row[3]." deleted<br>";
					}
					// update record
					//$sql = "UPDATE land SET owner_user_id=".$owner_user_id." WHERE x=".$plotCo[0]." AND y=".$plotCo[1];
					//$sql = "UPDATE land SET owner_user_id=".$owner_user_id." WHERE x=".$i." AND y=".$j;
					$sql = "UPDATE land SET owner_user_id=".$owner_user_id.", title='".$title."', detail='".$detail."', picture='".$picture."' WHERE x=".$i." AND y=".$j;
					if (unlink("images/thumbs/land_id_".$row[0])) {
						//echo "images/thumbs/land_id_".$row[0]." deleted<br>";
					}
				}
				else {
					// insert record
					//$sql = "INSERT INTO land (x, y, owner_user_id, title, detail, picture) VALUES (".$plotCo[0].", ".$plotCo[1].", ".$owner_user_id.", '".$title."', '".$detail."', '".$picture."')";
					$sql = "INSERT INTO land (x, y, owner_user_id, title, detail, picture) VALUES (".$i.", ".$j.", ".$owner_user_id.", '".$title."', '".$detail."', '".$picture."')";
				}
				$result = mysql_query($sql);
				//echo $result." = ".substr($sql,0,100)."...".substr($sql,-30)."<br>";
				if ($result == true) {
				}
				else {
				}
				$plot_list[] = "(".$i."-".$j.")";
			}
		}
	}
}
if ($land_special_id != -1) {
	$sql = "UPDATE land_special SET owner_user_id=".$owner_user_id.", title='".$title."', detail='".$detail."', picture='".$picture."' WHERE id=".$land_special_id;
	$result = mysql_query($sql);
}
mysql_close($con);

// Send email
$subject = "Land purchased";

$message = "Purchased land has been associated with the below given information:\r\n\r\n";
$message .= "Email: ".$useremail."\r\n";
$message .= "Title: ".$title."\r\n";
$message .= "Detail: ".$detail."\r\n";
$message .= "Picture: (Attached)\r\n\r\n";
$message .= "This following plots have been purchased:\r\n\r\n";

foreach ($plot_list as $tPlot) {
	$message .= $tPlot."\r\n";
}

mail_attachment("johandblomberg@gmail.com", $subject, $message, "noreply@pieceoftheworld.co", $_FILES["picture_name"]["tmp_name"], $_FILES["picture_name"]["name"]);
mail_attachment("pieceoftheworld2013@gmail.com", $subject, $message, "noreply@pieceoftheworld.co", $_FILES["picture_name"]["tmp_name"], $_FILES["picture_name"]["name"]);

die('<center><h2>Thank you for your purchase.</h2></center>');
}
?>
</body>
</html>
