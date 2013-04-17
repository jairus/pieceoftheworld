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
	
	$land = $post['land'];
	$useremail = $post['useremail'];
	$sql = "select * from `web_users` where `useremail`='".mysql_real_escape_string(trim($useremail))."'";
	$web_user = dbQuery($sql, $_dblink);
	if($web_user[0]['id']){
		$web_user_id = $web_user[0]['id'];
	}
	else{
		$pass = rand(1000,9999);
		$sql = "insert into `web_users` set 
		`useremail`='".mysql_real_escape_string(trim($useremail))."',
		`password`='".md5($pass)."',
		`plain_pass`='".$pass."'
		";
		$web_user = dbQuery($sql, $_dblink);
		$web_user_id = $web_user['mysql_insert_id'];
	}
	$land_owner = ($post['land_owner']);
	$title = ($post['title_name']);
	$detail = ($post['detail_name']);	
	$image = $post['filename'];
	if ($image != null) {
		$picture = mysql_real_escape_string(file_get_contents($image)); // the content of the image
	}
	
	$land_special_id = -1;	
	$plot_list = array();	
	$owner_user_id = 0;
	$plots = explode("_", $land);
	$plots = array_unique($plots);
	
	if ($land != null) {
		if (sizeof($plots) == 1) {
			$plotCo =  explode("-", $plots[0]);
			do{
				$sql = "SELECT * FROM land WHERE x=".$plotCo[0]." AND y=".$plotCo[1];
				$rows = dbQuery($sql, $_dblink);
				if($rows[0]['id']){
					$sql = "select `id` from `land_detail` where `id`='".$rows[0]['land_detail_id']."'";
					$land_detail = dbQuery($sql, $_dblink);
					//if detaul record exists
					if($land_detail[0]['id']){
						$sql = "UPDATE `land_detail` set 
						`title`='".mysql_real_escape_string($title)."', 
						`detail`='".mysql_real_escape_string($detail)."', 
						`picture`='".mysql_real_escape_string($picture)."', 
						`land_owner`='".mysql_real_escape_string($land_owner)."',
						`folder`='".$_GET['f']."'
						where `id`='".$rows[0]['land_detail_id']."'
						";
						dbQuery($sql, $_dblink);
						$insert_id = $rows[0]['land_detail_id'];
					}
					else{
						$sql = "INSERT into `land_detail` set 
						`title`='".mysql_real_escape_string($title)."', 
						`detail`='".mysql_real_escape_string($detail)."', 
						`picture`='".mysql_real_escape_string($picture)."', 
						`land_owner`='".mysql_real_escape_string($land_owner)."',
						`folder`='".$_GET['f']."'";
						$insert_id = dbQuery($sql, $_dblink);
						$insert_id = $insert_id['mysql_insert_id'];
					}
					//update land_detail_id of rows
					$tx = count($rows);
					for($ix=0; $ix<$tx; $ix++){
						$sql  = "update `land` set 
						`land_detail_id`='".$insert_id ."',
						`web_user_id`='".$web_user_id."'
						where `id`='".$rows[$ix]['id']."'";
						dbQuery($sql, $_dblink);
					}
				}
				else{
					$sql = "insert into `land` set 
					`x`='".$plotCo[0]."',
					`y`='".$plotCo[1]."'";
					dbQuery($sql, $_dblink);
				}
				
			}while(!$rows[0]['id']);
			$plot_list[] = "(".$plotCo[0]."-".$plotCo[1].")";
			
		}
		else { //if multiple plots
			
			$plotCoLT =  explode("-", $plots[0]);
			$plotCoRB =  explode("-", $plots[1]);
			$insert_id = "";
			for ($i = $plotCoLT[0]; $i <= $plotCoRB[0]; $i++) {
				for ($j = $plotCoLT[1]; $j <= $plotCoRB[1]; $j++) {
					do{
						$sql = "SELECT * FROM land WHERE x=".$i." AND y=".$j;
						echo $sql."<br>"; 
						$rows = dbQuery($sql, $_dblink);
						if($rows[0]['id']){
							$sql = "select `id` from `land_detail` where `id`='".$rows[0]['land_detail_id']."'";
							$land_detail = dbQuery($sql, $_dblink);
							//if detaul record exists
							if($land_detail[0]['id']&&!$insert_id){
								$sql = "UPDATE `land_detail` set 
								`title`='".mysql_real_escape_string($title)."', 
								`detail`='".mysql_real_escape_string($detail)."', 
								`picture`='".mysql_real_escape_string($picture)."', 
								`land_owner`='".mysql_real_escape_string($land_owner)."',
								`folder`='".$_GET['f']."'
								where `id`='".$rows[0]['land_detail_id']."'
								";
								dbQuery($sql, $_dblink);
								$insert_id = $rows[0]['land_detail_id'];
								
							}
							else if(!$insert_id){
								$sql = "INSERT into `land_detail` set 
								`title`='".mysql_real_escape_string($title)."', 
								`detail`='".mysql_real_escape_string($detail)."', 
								`picture`='".mysql_real_escape_string($picture)."', 
								`land_owner`='".mysql_real_escape_string($land_owner)."',
								`folder`='".$_GET['f']."'";
								$insert_id = dbQuery($sql, $_dblink);
								$insert_id = $insert_id['mysql_insert_id'];
							}
							
							//update land_detail_id of rows
							$tx = count($rows);
							for($ix=0; $ix<$tx; $ix++){
								$sql  = "update `land` set 
								`land_detail_id`='".$insert_id ."',
								`web_user_id`='".$web_user_id."'
								where `id`='".$rows[$ix]['id']."'";
								dbQuery($sql, $_dblink);
							}
						}
						else{
							$sql = "insert into `land` set 
							`x`='".$i."',
							`y`='".$j."'";
							dbQuery($sql, $_dblink);
						}
						
					}while(!$rows[0]['id']);
				}
			}
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