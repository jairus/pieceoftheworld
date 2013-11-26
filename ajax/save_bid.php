<?php
require_once 'global.php';
include_once("../emailer/email.php");


if(!checkEmail(trim($_POST['user_email']))){
	echo '<span style="color:red; font-weight:bold;">Invalid E-mail address</span>';
	
	exit();
}else if(!trim($_GET['bid'])){
	echo '<span style="color:red; font-weight:bold;">Please enter your Bid</span>';
	
	exit();
}else{
	$sql = "SELECT `land_detail_id` FROM `land` WHERE `id`='".$_GET['land_id']."' LIMIT 0,1";
	$land = dbQuery($sql, $_dblink);
	
	$sql = "SELECT * FROM `land_detail` WHERE `id`='".$land[0]['land_detail_id']."' LIMIT 0,1";
	$land_detail = dbQuery($sql, $_dblink);
	
	$land_name = 'Land';
	if($land_detail[0]['title']){
		$land_name = $land_detail[0]['title'];
	}

	$from = $_POST['user_email'];
	$fromname = $_POST['user_email'];
	$bouncereturn = $_POST['user_email'];
	$subject = "Bid for ".$land_name;
	$message = '<b>Land ID: </b>'.$_GET['land_id'].'<br /><br /><b>User Bid: </b>'.$_GET['bid'].'<br /><br /><b>Message: </b>'.$_POST['user_message'];
	$emails[0]['email'] = "pieceoftheworld2013@gmail.com";
	$emails[0]['name'] = "PieceOfTheWorld.Co";
	$attachments[0] = "";
	$attachments[1] = "";
	emailBlast($from, $fromname, $subject, $message, $emails, $bouncereturn, $attachments,  0);
	
	$sql = "INSERT INTO `land_bids` (`bidder`, `bid`, `message`, `land_id`) VALUES ('".$_POST['user_email']."', '".$_GET['bid']."', '".$_POST['user_message']."', '".$_GET['land_id']."')";
	dbQuery($sql, $_dblink);
	
	echo '<br /><br /><span style="color:#00FF00; font-weight:bold;">You have successfully submitted your Bid</span>';
	
	exit();
}
?>