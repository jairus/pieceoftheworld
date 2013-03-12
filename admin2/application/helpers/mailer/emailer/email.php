<?php
/*
$from = "jairus@nmgresources.ph";
$fromname = "Jairus Bondoc";
$bouncereturn = "jairus@nmgresources.ph"; //where the email will forward in cases of bounced email
$subject = "Testing Email Blast";
$message = "<b>Hello World</b>";
$emails[0]['email'] = "fuzylogic28@yahoo.com";
$emails[0]['name'] = "Jairus Bondoc";
$emails[1]['email'] = "jairus@nmgresources.ph";
$emails[1]['name'] = "Jairus Bondoc";
emailBlast($from, $fromname, $subject, $message, $emails, $bouncereturn);
*/
include_once(dirname(__FILE__)."/class.phpmailer.php");
include_once(dirname(__FILE__)."/config.php");

function emailBlast($from, $fromname, $subject, $message, $emails, $bouncereturn, $debug=0)
{
	$_SMTPHOST='www.startuplist.sg';
	$_SMTPUSER='mailer@startuplist.sg';
	$_SMTPPASS='mailer';

	$mail = new PHPMailer();
	$mail->IsSMTP();                                      // set mailer to use SMTP
	//$mail->IsQmail();

	$mail->Host = $_SMTPHOST;  // specify main and backup server
	$mail->SMTPAuth = true;     // turn on SMTP authentication
	$mail->Username = $_SMTPUSER;  // SMTP username
	$mail->Password = $_SMTPPASS; // SMTP password
	
	$mail->Sender = $bouncereturn;
	$mail->From = $from;
	$mail->FromName = $fromname;
	
	if($debug)
	{
		echo "From: $fromname <",  $from, "><br>";
		echo "Reply-To: ",  $from, "<br>";
		echo "Return Path: ",  $bouncereturn, "<br>";
	}
				
	$t = count($emails);
	for($i=0; $i<$t; $i++)
	{
		//print_r($emails[$i]);
		//$mail->AddAddress("josh@example.net", "Josh Adams");
		//$mail->AddReplyTo("josh@example.net", "Josh Adams");
		
		$mail->AddAddress($emails[$i]['email'], $emails[$i]['name']);
		
		$mail->AddReplyTo($from ,$fromname);
		
		//$mail->WordWrap = 50;                                 // set word wrap to 50 characters
		//$mail->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
		//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
		$mail->IsHTML(true);                                  // set email format to HTML
		
		//western european encoding 
		//$mail->Subject = "=?iso-8859-1?q?".$subject."?=";
		//$mail->Subject = "=?utf-8?q?".$this->subject."?=";
		$mail->Subject = $subject;
		$emailtext=$message;
		$mail->Body    = $emailtext;
		$mail->AltBody = strip_tags($emailtext);
		
		if($debug)
		{
			echo "Sending to <b>".$emails[$i]['email']."</b> ... ", $mail->Send(),"<br>";
			echo $mail->ErrorInfo;
		}
		else
		{
			$mail->Send();
		}
		$mail->ClearAddresses();
	}
}
?>