<?php
require_once 'global.php';
$type = @$_GET['type'];
$land_special_id = @$_GET['land_special_id'];
$x1 = @$_GET['x1'];
$y1 = @$_GET['y1'];
$x2 = @$_GET['x2'];
$y2 = @$_GET['y2'];
$conOptions = GetGlobalConnectionOptions();
$con = mysql_connect($conOptions['server'], $conOptions['username'], $conOptions['password']);
if (!$con) { die('[[]]'); }
mysql_select_db($conOptions['database'], $con);
$sql = "";

updateLandViewCounter($x1, $y1);

$keys = array_keys($_GET);
if (count($keys)>1&&!$_GET['default']) { //count should be more than 1 cause _ anti cache timestamp variable is always gonna be there
	if($type=='exact'){
		
		$sql = "SELECT 
			if((`a`.`land_special_id` IS NOT NULL and `a`.`web_user_id`=0), 'masteruser@gmail.com', '') as `email`, 
			`id` AS `id`, 
			`x`, 
			`y`, 
			`land_special_id`, 
			`web_user_id`
			FROM `land` as `a` 
			where 
			`x`=$x1 and `y`=$y1 
			";
		$markers = dbQuery($sql, $_dblink);			
		
		if($markers[0]['web_user_id']||$markers[0]['land_special_id']){
			$sql = "SELECT 
				if((`a`.`land_special_id` IS NOT NULL and `a`.`web_user_id`=0), 'masteruser@gmail.com', '') as `email`, 
				`a`.`id` AS `id`, 
				`a`.`x`, 
				`a`.`y`, 
				`a`.`land_special_id`, 
				`a`.`land_detail_id`, 
				`a`.`web_user_id` as `owner_user_id`, 
				`b`.`title`, 
				`b`.`land_owner`, 
				`b`.`detail`, 
				`c`.`useremail`
				FROM `land` as `a` 
				LEFT JOIN `land_detail` as `b` ON (`a`.`land_detail_id` = `b`.`id`)
				LEFT JOIN `web_users` as `c` ON (`a`.`web_user_id` = `c`.`id`) 
				where 
				`a`.`x`=$x1 and `a`.`y`=$y1 
				";
		}
		else{
			//return blank
			echo "[[]]";
			exit();
		}
	}
	else if ($type == 'special') {
		$sql = "SELECT 
				land_special.id as id, 
				owner_user_id, 
				title, 
				'masteruser@gmail.com' as `email`,
				detail,
				(SELECT x FROM land WHERE land_special_id=land_special.id and `web_user_id`=0 LIMIT 1) AS x,
				(SELECT y FROM land WHERE land_special_id=land_special.id and `web_user_id`=0 LIMIT 1) AS y
				FROM land_special WHERE 1";
	}
	else if (!empty($land_special_id)) {
		$sql = "SELECT 
				avg(x) AS x,
				avg(y) AS y 
				FROM land 
				WHERE land_special_id=".$land_special_id;
	}
	else { //non special blocks
		if($_GET['multi']){
			$sql = "SELECT 
			if((`a`.`land_special_id` IS NOT NULL and `a`.`web_user_id`=0), 'masteruser@gmail.com', '') as `email`,
			`a`.`id` AS `id`, 
			`a`.`x`, 
			`a`.`y`, 
			`a`.`land_special_id`,
			`a`.`land_detail_id`, 
			`a`.`web_user_id` as `owner_user_id`, 
			`b`.`title`, 
			`b`.`land_owner`, 
			`b`.`detail`, 
			`c`.`useremail`,
			`d`.`video`
			FROM `land` as `a` 
			LEFT JOIN `land_detail` as `b` ON (`a`.`land_detail_id` = `b`.`id`) 
			LEFT JOIN `web_users` as `c` ON (`a`.`web_user_id` = `c`.`id`) 
			LEFT JOIN `videos` as `d` ON (`b`.`id` = `d`.`land_id`) 
			where `a`.`x`>=$x1 and `a`.`x`<=$x2 and `a`.`y`>=$y1 and `a`.`y`<=$y2
			and 
			(web_user_id <> 0 or land_special_id <> 0)
			
			";
			
		}
		else{
			$sql = "SELECT 
			if((`a`.`land_special_id` IS NOT NULL and `a`.`web_user_id`=0), 'masteruser@gmail.com', '') as `email`, 
			`a`.`id` AS `id`, 
			`a`.`x`, 
			`a`.`y`, 
			`a`.`land_special_id`, 
			`a`.`land_detail_id`, 
			`a`.`web_user_id` as `owner_user_id`, 
			`b`.`title`, 
			`b`.`land_owner`, 
			`b`.`detail`, 
			`c`.`useremail`
			FROM `land` as `a` 
			LEFT JOIN `land_detail` as `b` ON (`a`.`land_detail_id` = `b`.`id`)
			LEFT JOIN `web_users` as `c` ON (`a`.`web_user_id` = `c`.`id`) 
			where 
			`a`.`x`>=$x1 and `a`.`x`<=$x2 and `a`.`y`>=$y1 and `a`.`y`<=$y2
			and 
			(web_user_id <> 0 or land_special_id <> 0)
			";
		}
	}
}
else { //getting purchased lands (red)
	$sql = "SELECT 
	`a`.`id` AS `id`, 
	`a`.`x`, 
	`a`.`y`, 
	`a`.`land_special_id`, 
	`a`.`land_detail_id`, 
	`a`.`web_user_id` as `owner_user_id`, 
	`b`.`title`, 
	`b`.`land_owner`, 
	`b`.`detail`, 
	`c`.`useremail`
	FROM `land` as `a` 
	LEFT JOIN `land_detail` as `b` ON (`a`.`land_detail_id` = `b`.`id`)
	LEFT JOIN `web_users` as `c` ON (`a`.`web_user_id` = `c`.`id`) 
	WHERE web_user_id <> 0";
}

$markers = dbQuery($sql, $_dblink);
//check if special and unbought
if(count($markers)==1&&$markers[0]['id']>0&&$markers[0]['owner_user_id']==0&&$markers[0]['land_special_id']>0){
	$sql = "select 
		if(`web_user_id`=0, 'masteruser@gmail.com', '') as `email`, 
		`id` as `land_special_id`,
		`land_special`.* 
		from 
		`land_special` where 
		`id`='".$markers[0]['land_special_id']."'";
	$markers = dbQuery($sql, $_dblink);
	
	$sql = "select * from `pictures_special` where `land_special_id`='".$markers[0]['id']."' and isMain=1";
	$pictures = dbQuery($sql, $_dblink);
	$t = count($pictures);
	
	$sql = "select `x`, `y` from `land` where `land_special_id`='".$markers[0]['id']."'";
	$points = dbQuery($sql, $_dblink);
	if(count($points)){
		$markers[0]['points'] = $points;
	}
	
	if($t){
		//showThumb($post['filename'], 120, 120*1.3, dirname($post['filename'])."/"."thumb_".basename($post['filename']).".png", true);
		//showThumb($post['filename'], "450", "300", dirname($post['filename'])."/"."450_".basename($post['filename']).".png", false);
		//$markers[0]['thumb_url'] = "/_uploads/".$markers[0]['folder']."/thumb_".basename($post['filename'].".png?_=".time());
		//$markers[0]['img_url'] = "/_uploads/".$markers[0]['folder']."/450_".basename($post['filename'].".png?_=".time());
		$picture = explode("/_uploads2/", $pictures[0]['picture']);
		$picture = dirname(__FILE__)."/../_uploads2/".$picture[1];
	
		showThumb($picture, 120, 120*1.3, dirname($picture)."/"."thumb_".basename($picture).".png", true);
		showThumb($picture, "450", "300", dirname($picture)."/"."450_".basename($picture).".png", false);
		$markers[0]['thumb_url'] = "/_uploads2/specialland/".$markers[0]['id']."/images/thumb_".basename($picture.".png");
		$markers[0]['img_url'] = "/_uploads2/specialland/".$markers[0]['id']."/images/450_".basename($picture.".png");
	}
}
else{
	if($markers[0]['id']){
		$markers[0]['email'] = $markers[0]['useremail'];
	}
	$uploads_dir = dirname(__FILE__).'/../_uploads/'.$markers[0]['folder'];

	if($markers[0]['owner_user_id']){ //
	
		if($markers[0]['land_special_id']){ //if the block was a special land get the attached land
			$sql = "select `x`, `y` from `land` where `land_special_id`='".$markers[0]['land_special_id']."'";
			$points = dbQuery($sql, $_dblink);
			if(count($points)){
				$markers[0]['points'] = $points;
			}
		}
		/*
		if($markers[0]['land_detail_id']){ //if user bought multiple lands
			$sql = "select `x`, `y` from `land` where `land_special_id`='".$markers[0]['land_special_id']."'";
			$points = dbQuery($sql, $_dblink);
			if(count($points)){
				$markers[0]['points'] = $points;
			}
		}
		*/
		$sql = "select * from `pictures` where `land_id`='".$markers[0]['land_detail_id']."' and isMain=1";
		//echo $sql;
		$pictures = dbQuery($sql, $_dblink);
		$t = count($pictures);
		if($t){
			//showThumb($post['filename'], 120, 120*1.3, dirname($post['filename'])."/"."thumb_".basename($post['filename']).".png", true);
			//showThumb($post['filename'], "450", "300", dirname($post['filename'])."/"."450_".basename($post['filename']).".png", false);
			//$markers[0]['thumb_url'] = "/_uploads/".$markers[0]['folder']."/thumb_".basename($post['filename'].".png?_=".time());
			//$markers[0]['img_url'] = "/_uploads/".$markers[0]['folder']."/450_".basename($post['filename'].".png?_=".time());
			$picture = explode("/_uploads2/", $pictures[0]['picture']);
			$dir = dirname($picture[1]);
			
			
			$picturex = dirname(__FILE__)."/../_uploads2/".$picture[1];
			if(!file_exists($picturex)){
				$picturex = dirname(__FILE__)."/../_uploads2/".urldecode($picture[1]);
			}
			
			$picture = $picturex;
			if(file_exists($picture)){
				showThumb($picture, 120, 120*1.3, dirname($picture)."/"."thumb_".basename($picture).".png", true);
				showThumb($picture, "450", "300", dirname($picture)."/"."450_".basename($picture).".png", false);
				//if not special land
				$markers[0]['thumb_url'] = "/_uploads2/".$dir."/thumb_".basename($picture.".png");
				$markers[0]['img_url'] = "/_uploads2/".$dir."/450_".basename($picture.".png");
			}
			else{
				
			}
		}
	}
	
}
if(isset($_GET['print'])){
	echo "<pre>";
	echo $sql."\n";
	echo $uploads_dir;
	print_r($post);
	echo "<hr>";
	echo "Pictures<br>";
	print_r($pictures);
	echo "<hr>";
	echo "Markers<br>";
	print_r($markers);
	echo $picture;
	echo "</pre>";
}
else{
	
	if(count($markers)){
		echo json_encode($markers);
	}
	else{
		echo "[[]]";
	}
}




/************* FUNCTIONS BELOW ****************/

function showThumb($src, $thumbWidth, $thumbHeight, $dest="", $thumb=false, $returnim = false) 
{
	$info = pathinfo($src);

	$img = @imagecreatefromjpeg( $src );
	if(!$img){
		$img = @imagecreatefrompng ( $src );
	}
	if(!$img){
		$img = @imagecreatefromgif ( $src );
	}
	if(!$img){
		$img = @imagecreatefromwbmp ( $src );
	}
	if(!$img){
		$img = @imagecreatefromgd2 ( $src );
	}
	if(!$img){
		$img = @imagecreatefromgd2part ( $src );
	}
	if(!$img){
		$img = @imagecreatefromgd ( $src );
	}
	if(!$img){
		$img = @imagecreatefromstring ( $src );
	}
	if(!$img){
		$img = @imagecreatefromxbm ( $src );
	}
	if(!$img){
		$img = @imagecreatefromxpm ( $src );
	}
	
	if(!$img){
		
		return false;
	}	
	$width = imagesx( $img );
	$height = imagesy( $img );
	$new_width = $width;
	$new_height = $height;
	// calculate thumbnail size
	if($width>$height)
	{
		if($thumbWidth<$width)
		{
			$new_width = $thumbWidth;
			$new_height = floor( $height * ( $thumbWidth / $width ) );
		}
	}
	else
	{
		if($thumbHeight<$height)
		{
			$new_height = $thumbHeight;
			$new_width = floor( $width * ( $thumbHeight / $height ) );
		}
	}
	if($thumb){
		if($new_width>$new_height){
			$side = $new_width;
		}
		else{
			$side = $new_height;
		}
		$side1 = $thumbWidth;
		$side2 = $thumbHeight;
		$tmp_img = imagecreatetruecolor( $side1, $side2 );
		$white = imagecolorallocate($tmp_img, 255, 255, 255);
		imagefill($tmp_img, 0, 0, $white);
		
		imagecopyresampled( $tmp_img, $img, (($side1-$new_width)/2), (($side2-$new_height)/2), 0, 0, $new_width, $new_height, $width, $height );
	}
	else{
		$side1 = $thumbWidth;
		$side2 = $thumbHeight;
		$tmp_img = imagecreatetruecolor( $side1, $side2 );
		$white = imagecolorallocate($tmp_img, 255, 255, 255);
		imagefill($tmp_img, 0, 0, $white);
		
		imagecopyresampled( $tmp_img, $img, (($side1-$new_width)/2), (($side2-$new_height)/2), 0, 0, $new_width, $new_height, $width, $height );
	}
	/*
	else{
		// create a new temporary image
		$tmp_img = imagecreatetruecolor( $new_width, $new_height );
		$white = imagecolorallocate($tmp_img, 255, 255, 255);
		imagefill($tmp_img, 0, 0, $white);
		// copy and resize old image into new image 
		imagecopyresampled( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
	}
	*/
	
	if(!$returnim){
		if(!trim($dest)){
			imagepng( $tmp_img , null, 0);
		}
		else{
			imagepng ( $tmp_img , $dest, 0);
		}
	}
	else{
		return $tmp_img;
	}
	// save thumbnail into a file
	
}
function updateLandViewCounter($x, $y)
{	
	if($x && $y){
		// update land view counter 
		$rs = dbQuery("select id from land_view where x = '$x' and y = '$y' limit 1 ");
		if(!empty($rs)){
			dbQuery("update land_view set viewCtr = viewCtr + 1 where id = '{$rs[0]['id']}' limit 1 ");
		} else {
			// check if there is a land with this coordinate
			$rs = dbQuery("select id from land where x = '$x' and y = '$y' limit 1 ");
			$landId = (!empty($rs))? $rs[0]['id'] : 'null';
			dbQuery("insert into land_view set viewCtr = 1, x='$x', y='$y', land_id = $landId ");
		}	
	
	}
}
?>