<?php
//ini_set ("display_errors", "1");
//error_reporting(E_ALL);
error_reporting(E_ERROR);
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
function mail_attachment($to, $subject, $message, $from, $file) {
	// $file should include path and filename
	$filename = basename($file);
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
<?php
$name = $_POST['name'];
$email = $_POST['email'];
$message = $_POST['message'];
$formcontent="From: ".$name." \n Message: ".$message;
$recipient = $_POST['owner_user_email'];
$subject = "You have received a bid request";
?>
<html>
<head></head>
<body>
<div style="height:50%; margin-top:200px; text-align: center; font-family:Arial;" align="center" valign="middle">
<?php
mail_simple("johandblomberg@gmail.com", $subject, $formcontent, $email);
if (mail_simple("pieceoftheworld2013@gmail.com", $subject, $formcontent, $email) == true) {
	echo "<h4>Your bid request has been sent!</h4>";
}
else {
	echo "<h4>Your bid request could not be sent!</h4>";
}
?>
</div>
</body>
</html>
<?php
?>
