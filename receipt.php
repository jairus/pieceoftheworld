<?php
require_once(dirname(__FILE__)."/emailer/email.php");
require_once(dirname(__FILE__)."/ajax/global.php"); 

/** Expected indexes in the passed array and sample values
 * txnId -> paypal txn id if there is
 * totalAmount -> 200.00
 * purchaseDate -> 10:14:55 Sep 11, 2011
 * buyerEmail -> joy@gmail.com
 * buyerName -> Joy Eva
 * landId -> land id in the potw db
 * landTitle -> My Park 
 * landDetail -> This is the park where blah blah
 * landOwner -> string as given by buyer
 * isSpecialLand -> true / false
 * web_user_id -> web user id
 * plot_list -> array of points ("526669-289188","526669-289189","526669-289190")
 * pixFilename -> http://pieceoftheworld.co/_uploads2/specialland/1/images/thefile.jpg
 * certFilename -> http://pieceoftheworld.co/_uploads2/1111/certificate.pdf
 */
$arrData = array('txnId' => '12345',
				'totalAmount' => '12345.678',
				'purchaseDate' => '10:14:55 Sep 11, 2011',
				'buyerName' => 'Joy Evan',
				'buyerEmail' => 'djza29@yahoo.com',
				'landId' => '1',
				'landTitle' => 'Joy Place',
				'landDetail' => 'Saya dito',
				'landOwner' => 'Joy E',
				'isSpecialLand' => '1',
				'web_user_id' => '1',
				'plot_list' => array("526669-289188","526669-289189","526669-289190"),
				'pixFilename' => 'kk.jpg',
				'certFilename' => 'ee.pdf',
			);
generateEmailReceipt($arrData);
			
function generateEmailReceipt($arrData)
{		
	$arrData['landPlots'] = implode('<br/>',$arrData['plot_list']);

	
	// name wont always be prsent, especially if newly inserted in ipn process
	$buyerStr = (isset($arrData['buyerName']))? $arrData['buyerName'] .' <'. $arrData['buyerEmail'].'>' : $arrData['buyerEmail'];
	// note if special land
	$landTypeStr = ($arrData['isSpecialLand'])? '(Special Land)' : '';
	
	$message = "<img src='pieceoftheworld.co/admin2/media/pieceoft/logo.png' /><br/>
				Dear {$arrData['buyerName']},
				<br/><br/><strong>Thank you for your purchase. You now own a piece of the world!</strong>
				<br/><br/>This email also serves as your official receipt.
				<br/><hr/>
				<h2>Payment Details</h2>
				<table border='1'>
					<tr><th>Total Amount:</th><td>\$".number_format($arrData['totalAmount'],2)."</td></tr>
					<tr><th>Transaction ID:</th><td>{$arrData['txnId']}</td></tr>
					<tr><th>Purchase Date:</th><td>{$arrData['purchaseDate']}</td></tr>
					<tr><th>Buyer:</th><td>{$buyerStr} </td></tr>
					<tr><th>Land ID:</th><td>{$arrData['landId']} {$landTypeStr} </td></tr>					
					<tr><th>Land Plots:</th><td>{$arrData['landPlots']}</td></tr>					
				</table>				
				<hr/><br/>It usually takes a few minutes before your purchased piece of the world appears on the map.
				<br/>If it should not appear or you have any other questions, please contact pieceoftheworld2013@gmail.com.
				
	";

	$subject = "Land purchased by " . $arrData['buyerName'];

	$from = "noreply@pieceoftheworld.co";
	$fromname = "PieceOfTheWorld.com";
	$bouncereturn = "pieceoftheworld2013@gmail.com"; //where the email will forward in cases of bounced email

	// send to buyer
	$emails[0]['email'] = $arrData['useremail'];
	$emails[0]['name'] = $arrData['webUserName'];	
	// send copy to admin
	$emails[1]['email'] = 'djza29@yahoo.com';
	$emails[1]['name'] = 'Joy';	
	
	emailBlast($from, $fromname, $subject, $message, $emails, $bouncereturn, $attachments,  1); //last parameter for running debug
	saveTransaction($arrData, $subject, $message );
}
function saveTransaction($arrData, $subject, $message )
{
	global $_dblink;
	$arrValue = array();
	$arrData['emailContent'] = array($subject, $message);
	foreach($arrData as $index => $value){
		if(is_array($value)) $value = serialize($value);
		$arrValue[$index] = mysql_real_escape_string($value);
	}
	
	$sql = "insert into transactions (txnId, totalAmount, purchaseDate, web_user_id, land_detail_id, isSpecialLand, pixFilename, certFilename, emailContent) values
			('{$arrValue['txnId']}','{$arrValue['totalAmount']}','{$arrValue['purchaseDate']}','{$arrValue['web_user_id']}','{$arrValue['landId']}','{$arrValue['isSpecialLand']}','{$arrValue['pixFilename']}','{$arrValue['certFilename']}','{$arrValue['emailContent']}' )";
echo $sql;		
	dbQuery($sql, $_dblink );
}
?>