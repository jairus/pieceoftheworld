<?php
	ob_start();
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
	$receiptData = array(); // to be passed in receipt.php
	
	$useremail = trim($post['email']);
	//if user email is invalid
	if($useremail==""){
		exit();
	}
	
	
	$receiptData['txnId'] = $txnId;	//from ipn.php or g2s_success.php		
	$receiptData['fbdiscount'] = $post['fbdiscount'];	
	$receiptData['affdiscount'] = $post['affdiscount'];		
	$receiptData['paypalvalue'] = $post['paypalvalue'];	
	$receiptData['affname'] = $post['affname'];	
	$receiptData['purchaseDate'] = $post['payment_date'];		
	$receiptData['totalAmount'] = $mc_gross; //from ipn.php or g2s_success.php		
	$receiptData['buyerEmail'] = $useremail;	
	$receiptData['landTitle'] = $post['title'];	
	$receiptData['landDetail'] = $post['description'];	
	$receiptData['landOwner'] = $post['land_owner'];
	
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
		$receiptData['buyerName'] = $web_user[0]['name'];
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
	$receiptData['web_user_id'] = $web_user_id;	
	
	$coords = json_decode(stripslashes($post['coords']));
	$points = $coords->points;
	$receiptData['plot_list'] = $points;
	
	list($x, $y) = explode("-", $points[0]);
	$sql = "select * from `land` where `x`='".$x."' and `y`='".$y."'";
	$land = dbQuery($sql, $_dblink);
	//echo $sql;
	//print_r($land);
	
	if($land[0]['land_special_id']){
		$land_special_id = $land[0]['land_special_id'];
		$sql = "update `land_special` set
			`title` = '".mysql_real_escape_string($post['title'])."',
			`detail` = '".mysql_real_escape_string($post['description'])."',
			`land_owner` = '".mysql_real_escape_string($post['land_owner'])."',
			`category_id` = '".mysql_real_escape_string($post['categoryid'])."',
			`tags` = '".mysql_real_escape_string($post['tags'])."',
			`web_user_id`='".$web_user_id."',
			`datebought`=NOW()
			where `id` = '".$land_special_id."'
		";
		dbQuery($sql, $_dblink);
		$receiptData['isSpecialLand'] = true;
		$receiptData['landId'] = $land[0]['land_special_id'];
		
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
				`land_special_id`='".$land_special_id."',
				`web_user_id`='".$web_user_id."',
				`datebought`=NOW()
				where `id`='".$insertid."'";
				dbQuery($sql, $_dblink);
				
				echo "updated land ".$insertid."<br />";
			}
			else{
				$sql = "insert into `land` set 
				`x`='".$x."',
				`y`='".$y."',
				`web_user_id`='".$web_user_id."',
				`land_special_id`='".$land_special_id."',
				`datebought`=NOW()";
				$insertid = dbQuery($sql, $_dblink);
				$insertid  = $insertid ['mysql_insert_id'];
				echo "inserted land ".$insertid."<br />";
			}
			
			if($i==0){
				//move picture
				if(is_file($post['filename'])){
					@mkdir(dirname(__FILE__)."/_uploads2/specialland/".$land_special_id, 0777);
					@mkdir(dirname(__FILE__)."/_uploads2/specialland/".$land_special_id."/images", 0777);
					$imgfolder = dirname(__FILE__)."/_uploads2/specialland/".$land_special_id."/images";
					copy($post['filename'], $imgfolder."/".basename($post['filename']));
					$picture = "http%3A//www.pieceoftheworld.co/_uploads2/specialland/".$land_special_id."/images/".basename($post['filename']);
					
					$sql = "delete from `pictures_special` where `land_special_id` = '".$land_special_id."'";
					dbQuery($sql, $_dblink);
					if(trim($picture)){
						$sql = "insert into `pictures_special` set
							`land_special_id` = '".$land_special_id."',
							`picture` = '".$picture."',
							`isMain` = 1,
							`created_on` = NOW()
						";
						echo $sql."<br>";
						dbQuery($sql, $_dblink);
						$receiptData['pixFilename'] = $picture;
					}
				}
			}
		}
	}
	else{
		$receiptData['isSpecialLand'] = false;
		
		//insert land detail
		$sql = "insert into `land_detail` set
			`title` = '".mysql_real_escape_string($post['title'])."',
			`detail` = '".mysql_real_escape_string($post['description'])."',
			`land_owner` = '".mysql_real_escape_string($post['land_owner'])."',
			`category_id` = '".mysql_real_escape_string($post['categoryid'])."',
			`tags` = '".mysql_real_escape_string($post['tags'])."'
		";
		$land_detail_id = dbQuery($sql, $_dblink);
		$land_detail_id = $land_detail_id['mysql_insert_id'];
		$receiptData['landId'] = $land_detail_id;
		
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
				`web_user_id`='".$web_user_id."',
				`datebought`=NOW()
				where `id`='".$insertid."'";
				dbQuery($sql, $_dblink);
				
				echo "updated land ".$insertid."<br />";
			}
			else{
				$sql = "insert into `land` set 
				`x`='".$x."',
				`y`='".$y."',
				`web_user_id`='".$web_user_id."',
				`datebought`=NOW(),
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
						$receiptData['pixFilename'] = $picture;
					}
				}
			}
		}
	
	}
	
	//tags
	$tags = explode(",",$post['tags']);
	foreach($tags as $tag){
		if(trim($tag)){
			$sql = "select * from `tags` where LOWER(`name`) = '".mysql_real_escape_string(strtolower(trim($tag)))."'";
			$itag = dbQuery($sql, $_dblink);
			if($itag[0]){
				$sql = "update `tags` set `useCounter` = (`useCounter`+1) where `name` = '".mysql_real_escape_string(trim($tag))."'";
				dbQuery($sql, $_dblink);
			}
			else{
				$sql = "insert into `tags` set `useCounter` = 1, `name` = '".mysql_real_escape_string(trim($tag))."'";
				dbQuery($sql, $_dblink);
			}
		}
	}
	
	
	//delete land detail id's that arent in land
	$sql  = "DELETE FROM `land_detail` WHERE `id` not in (select `land_detail_id` from `land`)";
	dbQuery($sql, $_dblink);
	
	//delete pictures
	$sql  = "DELETE FROM `pictures` WHERE `land_id` not in (select `id` from `land_detail`)";
	dbQuery($sql, $_dblink);
	
	//delete pictures_special
	$sql  = "DELETE FROM `pictures_special` WHERE `land_special_id` not in (select `id` from `land_special`)";
	dbQuery($sql, $_dblink);
		
	// create certificate
	$file = "http://pieceoftheworld.com/certificate/generate_cert.php?f=".$zfolder;
	$contents = file_get_contents($file);
	$filename = "certificate.pdf";
	file_put_contents($uploads_dir."/".$filename, $contents);
	
	$receiptData['certFilename'] = $uploads_dir."/".$filename;
	
	$receiptData['affid'] = $affid; //from ipn.php or g2s_success.php
	
	print_r($receiptData);
	
	require_once(dirname(__FILE__)."/receipt.php");
	$receiptStat = generateEmailReceipt($receiptData);
	ob_end_clean();
?>
<script>

    
var mc_gross = "<?php echo $mb_amount;?>";
var tracking = "<?php echo $transaction_id;?>";
self.location = 'http://pieceoftheworld.com/ppc2.php?gross='+mc_gross+'&tracking='+tracking;
</script>