<?php
include_once(dirname(__FILE__)."/emailer/email.php");
$from = "jairus@nmgresources.ph";
$fromname = "Jairus Bondoc";
$bouncereturn = "jairus@nmgresources.ph"; //where the email will forward in cases of bounced email
$subject = "Testing Email Blast";
$message = "<b>Hello World</b>";
$emails[0]['email'] = "fuzylogic28@yahoo.com";
$emails[0]['name'] = "Jairus Bondoc";
$emails[1]['email'] = "jairus@nmgresources.ph";
$emails[1]['name'] = "Jairus Bondoc";
$emails[2]['email'] = "jairussss@nmgresources.ph";
$emails[2]['name'] = "Jairus Bondoc";
$attachments[0] = "";
$attachments[1] = "";
emailBlast($from, $fromname, $subject, $message, $emails, $bouncereturn, $attachments,  1); //last parameter for running debug
?>