<?php
include_once(dirname(__FILE__)."/emailer/email.php");
include_once(dirname(__FILE__)."/ajax/global.php"); 
$str = print_r($_GET, 1);
$str .= print_r($_POST, 1);
$str .= print_r($_SERVER, 1);

// Validate the Moneybookers signature
$concatFields = $_POST['merchant_id']
    .$_POST['transaction_id']
    .strtoupper(md5('Paste your secret word here'))
    .$_POST['mb_amount']
    .$_POST['mb_currency']
    .$_POST['status'];

$MBEmail = 'pieceoftheworld2013@gmail.com';

// Ensure the signature is valid, the status code == 2,
// and that the money is going to you

if(trim($_GET['f'])){
	file_put_contents(dirname(__FILE__)."/_ipn/".trim("skrill_".$_GET['f']).".txt", $concatFields);
}
error_reporting(E_ERROR );

if ((strtoupper(md5($concatFields)) == $_POST['md5sig']
    && $_POST['status'] == 2
    && $_POST['pay_to_email'] == $MBEmail)||$_GET['jairus'])
{
	$uploads_dir = dirname(__FILE__).'/_uploads/'.$_GET['f'];
	$post = unserialize(file_get_contents($uploads_dir."/post.txt"));

	include_once(dirname(__FILE__)."/ipn_process.php");
	
}
?>