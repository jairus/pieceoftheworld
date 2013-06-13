<?php
require_once 'global.php';
$type = @$_GET['type'];
$land_special_id = @$_GET['land_special_id'];
$x1 = @$_GET['x1'];
$y1 = @$_GET['y1'];
$x2 = @$_GET['x2'];
$y2 = @$_GET['y2'];

if($x1>$x2){
	$x2 = 630000;
}

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
			`land_detail_id`, 
			`web_user_id`,
			`a`.`country`, 
			`a`.`region`, 
			`a`.`city`, 
			`a`.`areatype`
			FROM `land` as `a` 
			where 
			`x`=$x1 and `y`=$y1 
			";
		$markers = dbQuery($sql, $_dblink);			
		
		
		if($markers[0]['land_special_id']){
			/*
			$sql = "SELECT 
				if((`a`.`land_special_id` IS NOT NULL and `a`.`web_user_id`=0), 'masteruser@gmail.com', '') as `email`, 
				`a`.`id` AS `id`, 
				`a`.`x`, 
				`a`.`y`, 
				`a`.`land_special_id`, 
				`a`.`land_detail_id`, 
				`b`.`web_user_id` as `owner_user_id`, 
				`a`.`country`, 
				`a`.`region`, 
				`a`.`city`, 
				`a`.`areatype`, 
				`b`.`title`, 
				`b`.`land_owner`, 
				`b`.`price`,
				`b`.`detail`, 
				`c`.`useremail`
				FROM `land` as `a` 
				LEFT JOIN `land_special` as `b` ON (`a`.`land_special_id` = `b`.`id`)
				LEFT JOIN `web_users` as `c` ON (`a`.`web_user_id` = `c`.`id`) 
				where 
				`a`.`x`=$x1 and `a`.`y`=$y1 
				";
			*/
			$sql = "select 
				`a`.`id`,
				`a`.`id` as `land_special_id`,
				`a`.`web_user_id` as `owner_user_id`, 
				`a`.`title`,
				`a`.`land_owner`, 
				`a`.`price`,
				`a`.`detail`,
				`b`.`useremail`,
				'".$x1."' as `x`,
				'".$y1."' as `y`,
				'' as `email`
				FROM `land_special` as `a`
				LEFT JOIN `web_users` as `b` ON (`a`.`web_user_id` = `b`.`id`) 
				where `a`.`id`='".$markers[0]['land_special_id']."'
			";
		}
		else if($markers[0]['web_user_id']){
			$sql = "SELECT 
				if((`a`.`land_special_id` IS NOT NULL and `a`.`web_user_id`=0), 'masteruser@gmail.com', '') as `email`, 
				`a`.`id` AS `id`, 
				`a`.`x`, 
				`a`.`y`, 
				`a`.`land_special_id`, 
				`a`.`land_detail_id`, 
				`a`.`web_user_id` as `owner_user_id`, 
				`a`.`country`, 
				`a`.`region`, 
				`a`.`city`, 
				`a`.`areatype`, 
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
			$sql = "SELECT 
				`a`.`id` AS `id`, 
				`a`.`x`, 
				`a`.`y`, 
				`a`.`land_detail_id`, 
				`a`.`country`, 
				`a`.`region`, 
				`a`.`city`, 
				`a`.`areatype`
				FROM `land` as `a` 
				where 
				`a`.`x`=$x1 and `a`.`y`=$y1 
				";
		}
		
		//echo $sql;
	}
	else if ($type == 'special') {
		/*
		$sql = "SELECT 
				id, 
				owner_user_id, 
				'masteruser@gmail.com' as `email`,
				(SELECT x FROM land WHERE land_special_id=land_special.id LIMIT 1) AS x,
				(SELECT y FROM land WHERE land_special_id=land_special.id LIMIT 1) AS y,
				(SELECT areatype FROM land WHERE land_special_id=land_special.id order by `id` asc LIMIT 1) AS areatype
				FROM land_special WHERE 
				`web_user_id`=0
		";
		*/
		$sql = "SELECT 
				id, 
				owner_user_id, 
				if((`web_user_id`=0), 'masteruser@gmail.com', '') as `email`,
				`web_user_id` as `owner_user_id`, 
				`web_user_id`,
				(SELECT x FROM land WHERE land_special_id=land_special.id LIMIT 1) AS x,
				(SELECT y FROM land WHERE land_special_id=land_special.id LIMIT 1) AS y,
				(SELECT country FROM land WHERE land_special_id=land_special.id LIMIT 1) AS country
				FROM land_special
		";
		$markers = dbQuery($sql, $_dblink);
		
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
			WHERE `a`.web_user_id <> 0 group by `a`.`land_detail_id`";
		$markers2 = dbQuery($sql, $_dblink);	
		$markers = array_merge($markers, $markers2);
	
		
		if($_GET['bounded']){
			$markers = clipMarkers($markers, $x1, $x2, $y1, $y2);
		}
		
		//echo count($markers)."<br>";
		if($_GET['trim']){
			$markers = trimMarkers($markers);
		}
		//echo count($markers)."<--<br>";
		
		if(count($markers)){
			$r = json_encode($markers);
			file_put_contents(dirname(__FILE__)."/cache/test.txt", $r);
			echo $r;
		}
		else{
			echo "[[]]";
		}
		exit();
		/*
		$sql = "SELECT 
				`a`.`id`, 
				`a`.`owner_user_id`, 
				'masteruser@gmail.com' as `email`,
				left join `land` as `b`
				on (`a`.`id` = `b`.`land_special_id`)
				FROM 
				`land_special` as `a` 
				WHERE 
				`web_user_id`=0
		";
		*/
	}
	else if (!empty($land_special_id)) {
		$sql = "SELECT 
				avg(x) AS x,
				avg(y) AS y 
				FROM land 
				WHERE land_special_id=".$land_special_id;
	}
	else {
		if($_GET['multi']){
			$sql = "SELECT 
			if((`a`.`land_special_id` IS NOT NULL and `e`.`web_user_id`=0), 'masteruser@gmail.com', '') as `email`,
			if((`a`.`land_special_id` IS NOT NULL), `e`.`web_user_id`, `a`.`web_user_id`) as `owner_user_id`,
			`a`.`id` AS `id`, 
			`a`.`x`, 
			`a`.`y`, 
			`a`.`land_special_id`,
			`a`.`land_detail_id`, 
			if((`a`.`land_special_id` IS NULL), `b`.`title`, `e`.`title`) as `title`, 
			if((`a`.`land_special_id` IS NULL), `b`.`land_owner`, `e`.`land_owner`) as `land_owner`, 
			if((`a`.`land_special_id` IS NULL), `b`.`detail`, `e`.`detail`) as `detail`, 
			`c`.`useremail`,
			`d`.`video`
			FROM `land` as `a` 
			LEFT JOIN `land_detail` as `b` ON (`a`.`land_detail_id` = `b`.`id`) 
			LEFT JOIN `web_users` as `c` ON (`a`.`web_user_id` = `c`.`id`) 
			LEFT JOIN `videos` as `d` ON (`b`.`id` = `d`.`land_id`) 
			LEFT JOIN `land_special` as `e` ON (`a`.`land_special_id` = `e`.`id`) 
			where `a`.`x`>=$x1 and `a`.`x`<=$x2 and `a`.`y`>=$y1 and `a`.`y`<=$y2
			and 
			(a.web_user_id <> 0 or a.land_special_id <> 0)
			
			";
			
		}
		else{
			$sql = "SELECT 
			if((`a`.`land_special_id` IS NOT NULL and `a`.`web_user_id`=0), 'masteruser@gmail.com', '') as `email`, 
			if((`a`.`land_special_id` IS NOT NULL), `d`.`web_user_id`, `a`.`web_user_id`) as `owner_user_id`,
			`a`.`id` AS `id`, 
			`a`.`x`, 
			`a`.`y`, 
			`a`.`land_special_id`, 
			`a`.`land_detail_id`, 
			if((`a`.`land_special_id` IS NULL), `b`.`title`, `d`.`title`) as `title`, 
			if((`a`.`land_special_id` IS NULL), `b`.`land_owner`, `d`.`land_owner`) as `land_owner`, 
			if((`a`.`land_special_id` IS NULL), `b`.`detail`, `d`.`detail`) as `detail`, 
			`c`.`useremail`
			FROM `land` as `a` 
			LEFT JOIN `land_detail` as `b` ON (`a`.`land_detail_id` = `b`.`id`)
			LEFT JOIN `web_users` as `c` ON (`a`.`web_user_id` = `c`.`id`) 
			LEFT JOIN `land_special` as `d` ON (`a`.`land_special_id` = `d`.`id`) 
			where 
			`a`.`x`>=$x1 and `a`.`x`<=$x2 and `a`.`y`>=$y1 and `a`.`y`<=$y2
			and 
			(`a`.`web_user_id` <> 0 or `a`.`land_special_id` <> 0)
			";
			//echo $sql;
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
	if((`b`.`title`), `b`.`title`, `d`.`title`) as `title`, 
	if((`b`.`land_owner`), `b`.`land_owner`, `d`.`land_owner`) as `land_owner`, 
	if((`b`.`detail`), `b`.`detail`, `d`.`detail`) as `detail`, 
	`c`.`useremail`
	FROM `land` as `a` 
	LEFT JOIN `land_detail` as `b` ON (`a`.`land_detail_id` = `b`.`id`)
	LEFT JOIN `web_users` as `c` ON (`a`.`web_user_id` = `c`.`id`) 
	LEFT JOIN `land_special` as `d` ON (`a`.`land_special_id` = `d`.`id`)
	WHERE `a`.web_user_id <> 0";
}
$sqlx = $sql;

//echo $sqlx;
$markers = dbQuery($sql, $_dblink);
if($_GET['trim']){
	$markers = trimMarkers($markers);
}

//check if special and unbought
if(count($markers)==1&&$markers[0]['id']>0&&$markers[0]['owner_user_id']==0&&$markers[0]['land_special_id']>0){
	
	$sql = "select 
		if(`web_user_id`=0, 'masteruser@gmail.com', '') as `email`, 
		`id` as `land_special_id`,
		`land_special`.* 
		from 
		`land_special` where 
		`id`='".$markers[0]['land_special_id']."'";
	$markerstemp = dbQuery($sql, $_dblink);
	foreach($markerstemp[0] as $key=>$value){
		$markers[0][$key] = $value;
	}
	
	
	$sql = "select * from `pictures_special` where `land_special_id`='".$markers[0]['land_special_id']."' and isMain=1";
	$pictures = dbQuery($sql, $_dblink);
	$t = count($pictures);
	
	$sql = "select `x`, `y` from `land` where `land_special_id`='".$markers[0]['land_special_id']."'";
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
		$dir = dirname($picture[1]);
		
		
		$picturex = dirname(__FILE__)."/../_uploads2/".$picture[1];
		if(!file_exists($picturex)){
			$picturex = dirname(__FILE__)."/../_uploads2/".urldecode($picture[1]);
		}
		
		$picture = $picturex;
		if(file_exists($picture)){
			showThumb($picture, 290, 150, dirname($picture)."/"."thumb_".basename($picture).".png", true);
			showThumb($picture, "450", "300", dirname($picture)."/"."450_".basename($picture).".png", false);
			//if not special land
			$markers[0]['thumb_url'] = "http://pieceoftheworld.co/_uploads2/".$dir."/thumb_".basename($picture.".png");
			$markers[0]['img_url'] = "http://pieceoftheworld.co/_uploads2/".$dir."/450_".basename($picture.".png");
		}
		else{
			
		}
	}
		

}
else if($markers[0]['owner_user_id']){ //if bought
	
	
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
		
		if($markers[0]['land_special_id']){ //if special bought
			$sql = "select * from `pictures_special` where `land_special_id`='".$markers[0]['land_special_id']."' and isMain=1";
			$pictures = dbQuery($sql, $_dblink);
			$t = count($pictures);
			
			if($t){
				$picture = explode("/_uploads2/", $pictures[0]['picture']);
				$dir = dirname($picture[1]);	
				$picturex = dirname(__FILE__)."/../_uploads2/".$picture[1];
				if(!file_exists($picturex)){
					$picturex = dirname(__FILE__)."/../_uploads2/".urldecode($picture[1]);
				}
				
				$picture = $picturex;
				if(file_exists($picture)){
					showThumb($picture, 290, 150, dirname($picture)."/"."thumb_".basename($picture).".png", true);
					showThumb($picture, "450", "300", dirname($picture)."/"."450_".basename($picture).".png", false);
					//if not special land
					$markers[0]['thumb_url'] = "http://pieceoftheworld.co/_uploads2/".$dir."/thumb_".basename($picture.".png");
					$markers[0]['img_url'] = "http://pieceoftheworld.co/_uploads2/".$dir."/450_".basename($picture.".png");
				}
				else{
					
				}
			}
		}
		else{
			$sql = "select * from `pictures` where `land_id`='".$markers[0]['land_detail_id']."' and isMain=1";
			//echo $sql;
			$pictures = dbQuery($sql, $_dblink);
			$t = count($pictures);
			if($t){
				$picture = explode("/_uploads2/", $pictures[0]['picture']);
				$dir = dirname($picture[1]);
				
				
				$picturex = dirname(__FILE__)."/../_uploads2/".$picture[1];
				if(!file_exists($picturex)){
					$picturex = dirname(__FILE__)."/../_uploads2/".urldecode($picture[1]);
				}
				
				$picture = $picturex;
				if(file_exists($picture)){
					//showThumb($picture, 120, 120*1.3, dirname($picture)."/"."thumb_".basename($picture).".png", true);
					showThumb($picture, 290, 150, dirname($picture)."/"."thumb_".basename($picture).".png", true);
					showThumb($picture, "450", "300", dirname($picture)."/"."450_".basename($picture).".png", false);
					//if not special land
					$markers[0]['thumb_url'] = "http://pieceoftheworld.co/_uploads2/".$dir."/thumb_".basename($picture.".png");
					$markers[0]['img_url'] = "http://pieceoftheworld.co/_uploads2/".$dir."/450_".basename($picture.".png");
				}
				else{
					
				}
			}
		}
	}
	
}
if(isset($_GET['print'])){
	echo "<pre>";
	echo $sqlx."\n";
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

function showThumb_force($src, $thumbWidth, $thumbHeight, $dest="", $thumb=false, $returnim = false) {
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
		//imagefill($tmp_img, 0, 0, $white);
		$dstTransparent = imagecolorallocatealpha($tmp_img, 0, 0, 0, 127);
		imagefill($tmp_img, 0, 0, $dstTransparent);
        imagecolortransparent($tmp_img, $dstTransparent);
		imagealphablending($tmp_img, true);	
		imagesavealpha($tmp_img, true);
		
		imagecopyresampled( $tmp_img, $img, (($side1-$new_width)/2), (($side2-$new_height)/2), 0, 0, $new_width, $new_height, $width, $height );
	}
	else{
		$side1 = $thumbWidth;
		$side2 = $thumbHeight;
		$tmp_img = imagecreatetruecolor( $side1, $side2 );
		$white = imagecolorallocate($tmp_img, 255, 255, 255);
		//imagefill($tmp_img, 0, 0, $white);
		$dstTransparent = imagecolorallocatealpha($tmp_img, 0, 0, 0, 127);
		imagefill($tmp_img, 0, 0, $dstTransparent);
        imagecolortransparent($tmp_img, $dstTransparent);
		imagealphablending($tmp_img, true);	
		imagesavealpha($tmp_img, true);
		
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

function showThumb($src, $thumbWidth, $thumbHeight, $dest="", $thumb=false, $returnim = false) {
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
	// create a new temporary image
	$tmp_img = imagecreatetruecolor( $new_width, $new_height );
	$white = imagecolorallocate($tmp_img, 255, 255, 255);
	imagefill($tmp_img, 0, 0, $white);
	// copy and resize old image into new image 
	$dstTransparent = imagecolorallocatealpha($tmp_img, 0, 0, 0, 127);
	imagefill($tmp_img, 0, 0, $dstTransparent);
	imagecolortransparent($tmp_img, $dstTransparent);
	imagealphablending($tmp_img, true);	
	imagesavealpha($tmp_img, true);
	imagecopyresampled( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
	
	
	if(!trim($dest)){
		imagepng( $tmp_img , null, 0);
	}
	else{
		@imagepng ( $tmp_img , $dest, 0);
		//imagepng ( $tmp_img , null, 0);
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

function inMarkers($marker, $marr){
	$t = count($marr);
	if($_GET['trim']){
		$d = $_GET['trim'];
	}
	else{
		$d = 10000;
	}
	for($i=0; $i<$t; $i++){
		if(
			$marr[$i]['x'] + $d >= $marker['x'] && $marr[$i]['x'] - $d <= $marker['x'] && 
			$marr[$i]['y'] + $d >= $marker['y'] && $marr[$i]['y'] - $d <= $marker['y'] 
		){
			return $i;
			break;
		}
	}
	return -1;
}
function clipMarkers($markers, $x1, $x2, $y1, $y2){
	$marr = array();
	$t = count($markers);
	$d = 0;
	//echo $x1."-", $x2."-", $y1."-", $y2."-","<br>";
	//echo "<pre>";
	//print_r($markers);
	for($i=0; $i<$t; $i++){
		if(
			$markers[$i]['x'] >= $x1 && $markers[$i]['x'] <= $x2 && 
			$markers[$i]['y'] >= $y1 && $markers[$i]['y'] <= $y2
		){
			$marr[] = $markers[$i];
		}
	}
	//echo "<hr>";
	//print_r($marr);
	return $marr;
}
function trimMarkers($markers){
	$m = array();
	$marr = array();
	$t = count($markers);
	//echo "<pre>";
	//print_r($markers);
	
	for($i=0; $i<$t; $i++){
		$ret = inMarkers($markers[$i], $marr);
		//echo "<pre>";
		//print_r($markers[$i]);
		//echo $ret."<-<br>";
		if($ret==-1){
			$m = $markers[$i];
			$m['count'] = 1;
			$marr[] = $m;
			//echo $m['country']."= 1<br>";
		}
		else{
			//echo $marr[$ret]['country']."++<br>";
			$marr[$ret]['count']++;
		}
		//echo "<hr>";
	}
	return array_values($marr); 
	
}
?>