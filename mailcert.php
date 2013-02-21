<?php
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
      .strip_tags($message)."\r\n\r\n"
      ."--".$uid."\r\n"
      ."Content-type:text/html; charset=iso-8859-1\r\n"
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


$message = "<b>Thank you for your purchase. You now own a piece of the world!</b>
It usually takes a few minutes before your purchased piece of the world appears on the map. If it should not appear or you have any other questions, please contact pieceoftheworld2013@gmail.com.";

$from = "noreply@pieceoftheworld.com";
$file = "http://pieceoftheworld.com/certificate/generate_cert.php?f=20130212_1360677537.9889";
$filename = "certificate.pdf";

mail_attachment("pieceoftheworld2013@gmail.com", "Confirmation of purchase", $message, $from, $file, $filename);
mail_attachment("fuzylogic28@gmail.com", "Confirmation of purchase", $message, $from, $file, $filename);

?>