<?php
if(trim($_GET['f'])){
	include_once(dirname(__FILE__)."/emailer/email.php");
	require_once 'ajax/global.php';
	$uploads_dir = dirname(__FILE__).'/_uploads/'.$_GET['f'];
	$file = "http://pieceoftheworld.co/certificate/generate_cert.php?f=".$_GET['f'];
	$contents = file_get_contents($file);
	$filename = "certificate.pdf";
	file_put_contents($uploads_dir."/".$filename, $contents);
	
	
	
	if(isset($_GET['email'])){
		$uploads_dir = dirname(__FILE__).'/_uploads/'.$_GET['f'];
		$post = unserialize(file_get_contents($uploads_dir."/post.txt"));
		$land = $post['land'];
		$useremail = $post['useremail'];
	
	
		$from = "noreply@pieceoftheworld.co";
		$fromname = "PieceOfTheWorld.com";
		$bouncereturn = "pieceoftheworld2013@gmail.com"; //where the email will forward in cases of bounced email
		$message = "<b>Thank you for your purchase. You now own a piece of the world!</b><br/>
		It usually takes a few minutes before your purchased piece of the world appears on the map. If it should not appear or you have any other questions, please contact pieceoftheworld2013@gmail.com.
		";
		
		$emails[0]['email'] = "pieceoftheworld2013@gmail.com";
		$emails[0]['name'] = "pieceoftheworld2013@gmail.com";
		$emails[1]['email'] = "fuzylogic28@gmail.com";
		$emails[1]['name'] = "fuzylogic28@gmail.com";
		$emails[2]['email'] = $useremail;
		$emails[2]['name'] = $useremail;
		$attachments[0] = $uploads_dir."/".$filename;
		emailBlast($from, $fromname, $subject, $message, $emails, $bouncereturn, $attachments,  1); //last parameter for running debug
		
		$sql = "update `land` set `email_resent`='yes' where `folder`='".mysql_escape_string($_GET['f'])."' ";
		dbQuery($sql);
	}
	else{
		header('Content-type: application/pdf');
		echo file_get_contents($uploads_dir."/".$filename);
	}
}
?>