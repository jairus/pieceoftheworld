<?php
$str = print_r($_GET, 1);
$str .= print_r($_POST, 1);
$str .= print_r($_SERVER, 1);

$req = "";
foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}

$url = "https://www.paypal.com/cgi-bin/webscr?cmd=_notify-validate".$req;
$str .= "\n\n".$url;
$ppvalidate = file_get_contents($url);
$str .= "\n\n".$ppvalidate;

file_put_contents(dirname(__FILE__)."/_ipn/".date("Ymd_His").".txt", $str);

error_reporting(E_ERROR);
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

if(trim(strtoupper($ppvalidate))=="VERIFIED"){
	$uploads_dir = dirname(__FILE__).'/_uploads/'.$_GET['f'];
	$post = unserialize(file_get_contents($uploads_dir."/post.txt"));

	$land = $post['land'];
	$useremail = $post['useremail'];
	$title = mysql_escape_string(utf8_encode($post['title_name']));
	$detail = mysql_escape_string(utf8_encode($post['detail_name']));	
	$image = $post['filename'];
	
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
	$subject = "Land purchased by $useremail";

	$message = "Purchased land has been associated with the below given information:\r\n\r\n";
	$message .= "Email: ".$useremail."\r\n";
	$message .= "Title: ".$title."\r\n";
	$message .= "Detail: ".$detail."\r\n";
	$message .= "Picture: (Attached)\r\n\r\n";
	$message .= "This following plots have been purchased:\r\n\r\n";

	foreach ($plot_list as $tPlot) {
		$message .= $tPlot."\r\n";
	}

	//mail_attachment("johandblomberg@gmail.com", $subject, $message, "noreply@pieceoftheworld.co", $_FILES["picture_name"]["tmp_name"], $_FILES["picture_name"]["name"]);
	mail_attachment("pieceoftheworld2013@gmail.com", $subject, $message, "noreply@pieceoftheworld.co", $post['filename'], basename($post['filename']));
	mail_attachment("fuzylogic28@gmail.com", $subject, $message, "noreply@pieceoftheworld.co", $post['filename'], basename($post['filename']));

}
?>