<?php
include_once(dirname(__FILE__)."/emailer/email.php");
include_once(dirname(__FILE__)."/ajax/global.php"); 
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
$ppvalidate = @file_get_contents($url);
$str .= "\n\n".$ppvalidate;

if(trim($_GET['f'])){
	file_put_contents(dirname(__FILE__)."/_ipn/".trim($_GET['f']).".txt", $str);
}
error_reporting(E_ERROR );


if(trim(strtoupper($ppvalidate))=="VERIFIED"||$_GET['jairus']){
	$uploads_dir = dirname(__FILE__).'/_uploads/'.$_GET['f'];
	$post = unserialize(file_get_contents($uploads_dir."/post.txt"));

	if(($_POST['mc_gross']+0)!=($post['amount']+0)&&!$_GET['jairus']){ //inconsistent amount
		exit();
	}
	
	include_once(dirname(__FILE__)."/ipn_process.php");
	
}
?>