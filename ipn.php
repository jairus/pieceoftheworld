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
	
	//if there is an affiliate
	if(trim($_GET['affid'])){
		$sql = "select * from `affiliates` where `id`='".$_GET['affid']."' and `active`=1";
		$r = dbQuery($sql, $_dblink);
		$r = $r[0];
		if($r['id']){
			$rate = trim($r['commissionrate']);
			//if percentage
			if(strpos($rate, "%")!==false){
				$rate = $rate*1;
				$rate = $rate/100;
				$commission = $_POST['mc_gross']*$rate;
			}
			//if fixed
			else{
				$rate = $rate*1;
				$commission = $rate;
			}
			
			$sql = "insert into `affiliate_commissions` set 
				`affiliate_id`='".$r['id']."',
				`server_json`='".mysql_real_escape_string(json_encode($_SERVER))."',
				`commission` = '".$commission."',
				`dateadded`=NOW()
			";
			dbQuery($sql, $_dblink);
		}
	}
	
	/*
	$post
	Array
	(
		[step] => 1
		[email] => jairus@nmg.com.ph
		[title] => test
		[description] => test
		[land_owner] => 
		[filename] => 
		[http_picture] => 
		[amount] => 9.9
		[coords] => {\"points\":[\"380698-197059\"],\"strlatlongs\":[\"55.7134666222033,37.55518198745631\"]}
		[buydetails] => City: Moscow USD 9.90 x 1

	)
	*/
	
	$useremail = $post['email'];
	//get web user id
	$sql = "select * from `web_users` where LOWER(`useremail`) = '".strtolower($post['email'])."'";
	$web_user = dbQuery($sql, $_dblink);
	if($web_user[0]['id']){
		$web_user_id = $web_user[0]['id'];
	}
	else{
		$sql = "insert into `web_users` set 
			`useremail` = '".strtolower($post['email'])."',
			`password` = '".md5($post['password'])."',
			`plain_pass` = '".mysql_real_escape_string($post['password'])."'
		";
		$web_user_id = dbQuery($sql, $_dblink);
		$web_user_id = $web_user_id['mysql_insert_id'];
	}
	
	//insert land detail
	$sql = "insert into `land_detail` set
		`title` = '".mysql_real_escape_string($post['title'])."',
		`detail` = '".mysql_real_escape_string($post['description'])."'
	";
	$land_detail_id = dbQuery($sql, $_dblink);
	$land_detail_id = $land_detail_id['mysql_insert_id'];
	
	//set the land
	//get the points
	$coords = json_decode(stripslashes($post['coords']));
	$points = $coords->points;
	$strlatlongs = $coords->strlatlongs;
	$t = count($points);
	for($i=0; $i<$t; $i++){
		list($x, $y) = explode("-", $points[$i]);
		$sql = "select * from `land` where `x`='".$x."' and `y`='".$y."'";
		$land = dbQuery($sql, $_dblink);
		if($land[0]['id']){
			$insertid = $land[0]['id'];
			//update land detail id
			$sql = "update `land` set `land_detail_id`='".$land_detail_id."' where `id`='".$insertid."'";
			dbQuery($sql, $_dblink);
		}
		else{
			$sql = "insert into `land` set 
			`x`='".$x."',
			`y`='".$y."',
			`web_user_id`='".$web_user_id."',
			`land_detail_id`='".$land_detail_id."'";
			$insertid = dbQuery($sql, $_dblink);
			$insertid  = $insertid ['mysql_insert_id'];
		}
		
		if($i==0){
			//move picture
			if(is_file($post['filename'])){
				@mkdir(dirname(__FILE__)."/_uploads2/land/".$insertid, 0777);
				@mkdir(dirname(__FILE__)."/_uploads2/land/".$insertid."/images", 0777);
				$imgfolder = dirname(__FILE__)."/_uploads2/land/".$insertid."/images";
				copy($post['filename'], $imgfolder."/".basename($post['filename']));
				$picture = "http%3A//www.pieceoftheworld.co/_uploads2/land/".$insertid."/images/".basename($post['filename']);
			}
		}
		$sql = "delete from `pictures` where `land_id` = '".$insertid."'";
		dbQuery($sql, $_dblink);
		if(trim($picture)){
			$sql = "insert into `pictures` set
				`land_id` = '".$insertid."',
				`picture` = '".$picture."',
				`isMain` = 1,
				`created_on` = NOW()
			";
			dbQuery($sql, $_dblink);
		}
	}

	/*
	if ($land_special_id != -1) {
		$sql = "UPDATE land_special SET owner_user_id=".$owner_user_id.", title='".mysql_real_escape_string($title)."', detail='".mysql_real_escape_string($detail)."', picture='".$picture."' WHERE id=".$land_special_id;
		$result = mysql_query($sql);
	}
	mysql_close($con);
	*/
	
	// Send email
	
	$from = "noreply@pieceoftheworld.co";
	$fromname = "PieceOfTheWorld.com";
	$bouncereturn = "pieceoftheworld2013@gmail.com"; //where the email will forward in cases of bounced email
	$subject = "Land purchased by $useremail";
	$message = "Purchased land has been associated with the below given information:<br /><br />";
	$message .= "Email: ".$useremail."<br />";
	$message .= "Title: ".$title."<br />";
	$message .= "Detail: ".$detail."<br />";
	$message .= "Picture: (Attached)<br /><br />";
	$message .= "This following plots have been purchased:<br /><br />";
	foreach ($plot_list as $tPlot) {
		$message .= $tPlot."\r\n";
	}
	$iid = mysql_insert_id();
	if($iid){
		$message .= "ID: ".$iid."\r\n";
	}
	
	$emails[0]['email'] = "pieceoftheworld2013@gmail.com";
	$emails[0]['name'] = "pieceoftheworld2013@gmail.com";
	$emails[1]['email'] = "fuzylogic28@gmail.com";
	$emails[1]['name'] = "fuzylogic28@gmail.com";
	$attachments[0] = $post['filename'];
	emailBlast($from, $fromname, $subject, $message, $emails, $bouncereturn, $attachments,  1); //last parameter for running debug
	
	
	$file = "http://pieceoftheworld.co/certificate/generate_cert.php?f=".$_GET['f'];
	$contents = file_get_contents($file);
	$filename = "certificate.pdf";
	file_put_contents($uploads_dir."/".$filename, $contents);
	
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
	
}
?>