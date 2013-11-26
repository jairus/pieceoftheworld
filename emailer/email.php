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
include_once(dirname(__FILE__)."/mandrill-api-php/src/Mandrill.php");
include_once(dirname(__FILE__)."/class.phpmailer.php");
include_once(dirname(__FILE__)."/config.php");

function emailBlastx($from, $fromname, $subject, $message, $emails, $bouncereturn, $attachments, $debug=0)
{
	global $_SMTPHOST;
	global $_SMTPUSER;
	global $_SMTPPASS;
	
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
				
				
	//$mail->WordWrap = 50;                                 // set word wrap to 50 characters
	//$mail->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
	//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name

	if(is_array($attachments)){
		foreach($attachments as $value){
			$mail->AddAttachment($value, basename($value));
		}
	}
	$mail->IsHTML(true);                                  // set email format to HTML

	//western european encoding 
	//$mail->Subject = "=?iso-8859-1?q?".$subject."?=";
	//$mail->Subject = "=?utf-8?q?".$this->subject."?=";
	$mail->Subject = $subject;
	$emailtext=$message;
	$mail->Body    = $emailtext;
	$mail->AltBody = strip_tags($emailtext);

		
	$t = count($emails);
	for($i=0; $i<$t; $i++)
	{
		//print_r($emails[$i]);
		//$mail->AddAddress("josh@example.net", "Josh Adams");
		//$mail->AddReplyTo("josh@example.net", "Josh Adams");
		
		$mail->AddAddress($emails[$i]['email'], $emails[$i]['name']);
		
		$mail->AddReplyTo($from ,$fromname);

		
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


function emailBlast($from, $fromname, $subject, $message, $emails, $bouncereturn, $attachments, $debug=0){
	
	$from = $from;
	$fromname = $fromname;
	$subject = $subject;
	$email_content = $message;
	
	$template = array();
	$template['data'] = array();
	$template['data']['name'] = $toname;
	
	
	$template['data']['content'] = $email_content;
	$template['data']['content'] = nl2br($template['data']['content']);

	$template['slug'] = "potw-wrap"; 
	
	$attachmentsnew = array();
	if(is_array($attachments)){
		foreach($attachments as $value){
			$attach = array();
			$attach['type'] = "application/pdf";//mime_content_type($value);
			$attach['name'] = basename($value);
			$attach['content'] = base64_encode(file_get_contents($value));
			/*
			"attachments": [
				{
					"type": "text/plain",
					"name": "myfile.txt",
					"content": "ZXhhbXBsZSBmaWxl"
				}
			],
			*/
			$attachmentsnew[] = $attach;
		}
	}
	
	
	$t = count($emails);
	for($i=0; $i<$t; $i++){
		$emailtos = array();
		$email = array();
		$email['name'] = $emails[$i]['name'];
		$email['email'] = $emails[$i]['email'];
		$emailtos[] = $email;
		send_email($from, $fromname, $emailtos, $subject, $message, $template, $attachmentsnew);
		
	}
}


//mandrill
function send_email($from, $fromname, $emailtos, $subject, $message, $template, $attachments=array()){
	
	$formvars['key'] ='lrEvmkpllACYQDQx4qqaCw';
	
	$formvars['template_name'] =  $template['slug'];
	$formvars['template_content'] = array();
	foreach($template['data'] as $key=>$value){
		$content = array();
		$content['name'] = $key;
		$content['content'] = $value;
		$formvars['template_content'][] = $content;
	}
	$formvars['message'] = array();
	//$formvars['message']['html'] = "test email";
	//$formvars['message']['text'] = "test email";
	$formvars['message']['subject'] = $subject;
	$formvars['message']['from_email'] = $from;
	$formvars['message']['from_name'] = $fromname;
	/*
	$email = array();
	$email['email'] = $to;
	$email['name'] = $toname;
	$formvars['message']['to'][] = $emails;
	*/
	$formvars['message']['to'] = $emailtos;

	$formvars['message']['track_opens'] = true;
	$formvars['message']['track_clicks'] = true;
	$formvars['message']['auto_text'] = true;
	if(count($attachments)){
		$formvars['message']['attachments'] = $attachments;
	}
	$formvars['async'] = true;
	
	$m = new Mandrill('lrEvmkpllACYQDQx4qqaCw');
	//print_r($m->call("users/info", $formvars['message']));
	$r = $m->call("messages/send-template", $formvars);
	if(trim($r[0]['status'])!='sent'){
		print_r($formvars);
		print_r($r);
	}
}
?>