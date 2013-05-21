<?php
	/*
	$post
		Array
		(
			[step] => 1
			[email] => jairus@nmgresources.ph
			[web_user_id] => 3
			[title] => My Park
			[description] => My Park
			[land_owner] => 
			[amount] => 99
			[coords] => {\"points\":[\"526670-289190\",\"526669-289188\",\"526669-289189\",\"526669-289190\",\"526670-289188\",\"526670-289189\",\"526670-289190\",\"526671-289188\",\"526671-289189\",\"526671-289190\"],\"strlatlongs\":[\"14.578443122414674,120.97291737107537\",\"14.57954924975833,120.97234590712765\",\"14.578996186780754,120.97234590712765\",\"14.578443122414674,120.97234590712765\",\"14.57954924975833,120.97291737107537\",\"14.578996186780754,120.97291737107537\",\"14.578443122414674,120.97291737107537\",\"14.57954924975833,120.97348883502309\",\"14.578996186780754,120.97348883502309\",\"14.578443122414674,120.97348883502309\"]}
			[buydetails] => Manila Ocean Park USD 99.00 x 1

		)
	*/
	
	$useremail = trim($post['email']);
	//if user email is invalid
	if($useremail==""){
		exit();
	}
	
	//get web user id
	if(trim($post['web_user_id'])){ //check if there is a passed id
		$sql = "select * from `web_users` where LOWER(`useremail`) = '".strtolower($post['email'])."' and `id`='".mysql_real_escape_string($post['web_user_id'])."'";
	}
	else{
		$sql = "select * from `web_users` where LOWER(`useremail`) = '".strtolower($post['email'])."'";
	}
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
			$sql = "update `land` set 
			`land_detail_id`='".$land_detail_id."',
			`web_user_id`='".$web_user_id."'
			where `id`='".$insertid."'";
			dbQuery($sql, $_dblink);
			
			echo "updated land ".$insertid."<br />";
		}
		else{
			$sql = "insert into `land` set 
			`x`='".$x."',
			`y`='".$y."',
			`web_user_id`='".$web_user_id."',
			`land_detail_id`='".$land_detail_id."'";
			$insertid = dbQuery($sql, $_dblink);
			$insertid  = $insertid ['mysql_insert_id'];
			echo "inserted land ".$insertid."<br />";
		}
		
		if($i==0){
			//move picture
			if(is_file($post['filename'])){
				@mkdir(dirname(__FILE__)."/_uploads2/land/".$insertid, 0777);
				@mkdir(dirname(__FILE__)."/_uploads2/land/".$insertid."/images", 0777);
				$imgfolder = dirname(__FILE__)."/_uploads2/land/".$insertid."/images";
				copy($post['filename'], $imgfolder."/".basename($post['filename']));
				$picture = "http%3A//www.pieceoftheworld.co/_uploads2/land/".$insertid."/images/".basename($post['filename']);
				
				$sql = "delete from `pictures` where `land_id` = '".$land_detail_id."'";
				dbQuery($sql, $_dblink);
				if(trim($picture)){
					$sql = "insert into `pictures` set
						`land_id` = '".$land_detail_id."',
						`picture` = '".$picture."',
						`isMain` = 1,
						`created_on` = NOW()
					";
					echo $sql."<br>";
					dbQuery($sql, $_dblink);
				}
			}
		}
		
		
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
	
	
	//delete land detail id's that arent in land
	$sql  = "DELETE FROM `land_detail` WHERE `id` not in (select `land_detail_id` from `land`)";
	dbQuery($sql, $_dblink);
	
	//delete land detail id's that arent in land
	$sql  = "DELETE FROM `pictures` WHERE `land_id` not in (select `id` from `land_detail`)";
	dbQuery($sql, $_dblink);
	
	
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
?>