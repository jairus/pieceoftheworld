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
if (!empty($_GET)&&!$_GET['default']) {
	if ($type == 'special') {
		//$sql = "SELECT * FROM land_special WHERE 1";
		//$sql = "SELECT land_special.id AS id, owner_user_id, title, detail, picture, email FROM land_special, user WHERE (land_special.owner_user_id = user.id)";
		//$sql = "SELECT land_special.id AS id, owner_user_id, title, detail, picture, email FROM land_special LEFT JOIN user ON (land_special.owner_user_id = user.id) WHERE 1";
		//$sql = "SELECT land_special.id AS id, owner_user_id, title, detail, email FROM land_special LEFT JOIN user ON (land_special.owner_user_id = user.id) WHERE 1";
		//$sql = "SELECT land_special.id AS id, owner_user_id, title, detail, email, (SELECT avg(x) AS x FROM land WHERE land_special_id=land_special.id LIMIT 1), (SELECT avg(y) AS y FROM land WHERE land_special_id=land_special.id LIMIT 1) FROM land_special LEFT JOIN user ON (land_special.owner_user_id = user.id) WHERE 1";
		$sql = "SELECT land_special.id AS id, owner_user_id, title, detail, email, (SELECT avg(x) FROM land WHERE land_special_id=land_special.id LIMIT 1) AS x, (SELECT avg(y) FROM land WHERE land_special_id=land_special.id LIMIT 1) AS y FROM land_special LEFT JOIN user ON (land_special.owner_user_id = user.id) WHERE 1";
	}
	else if (!empty($land_special_id)) {
		$sql = "SELECT avg(x) AS x, avg(y) AS y FROM land WHERE land_special_id=".$land_special_id;
	}
	else {
		//$sql = "SELECT * FROM land WHERE ";
		//$sql = "SELECT land.id AS id, x, y, land_special_id, owner_user_id, title, detail, picture, email FROM land LEFT JOIN user ON (land.owner_user_id = user.id) WHERE ";
		/*
		$sql = "SELECT land.id AS id, x, y, land_special_id, owner_user_id, title, detail, email, folder FROM land LEFT JOIN user ON (land.owner_user_id = user.id) WHERE ";
		for ($x = $x1; $x <= $x2; $x++) {
			for ($y = $y1; $y <= $y2; $y++) {
				if (!($x == $x1 && $y == $y1)) {
					$sql .= "OR ";
				}
				$sql .= "(x=".$x." AND y=".$y.") ";
			}
		}
		*/
		if($_GET['multi']){
			$sql = "SELECT land.id AS id, x, y, land_special_id, owner_user_id, email, folder FROM land LEFT JOIN user ON (land.owner_user_id = user.id) where x>=$x1 and x<=$x2 and y>=$y1 and y<=$y2";
			
		}
		else{
			$sql = "SELECT land.id AS id, x, y, land_special_id, owner_user_id, title, detail, email, folder FROM land LEFT JOIN user ON (land.owner_user_id = user.id) where x>=$x1 and x<=$x2 and y>=$y1 and y<=$y2";
		}
	}
}
else {
	//$sql = "SELECT x, y, land_special_id FROM land WHERE 1";
	$sql = "SELECT x, y, land_special_id, email FROM land LEFT JOIN user ON (land.owner_user_id = user.id) WHERE land_special_id IS NULL";
}

$markers = dbQuery($sql, $_dblink);


$uploads_dir = dirname(__FILE__).'/../_uploads/'.$markers[0]['folder'];

if(trim($markers[0]['folder'])){
	$post = unserialize(file_get_contents($uploads_dir."/post.txt"));
	$post['filename'] = str_replace("/var/www/vhosts/s15331327.onlinehome-server.com/httpdocs/_uploads/", "/home/pieceoft/public_html/_uploads/", $post['filename']);
	
	$markers[0]['land_owner'] = $post['land_owner'];
	
	if(trim($post['filename'])){
		showThumb($post['filename'], "97", "97", dirname($post['filename'])."/"."thumb_".basename($post['filename']).".png", true);
		showThumb($post['filename'], "450", "300", dirname($post['filename'])."/"."450_".basename($post['filename']).".png", false);
		$markers[0]['thumb_url'] = "/_uploads/".$markers[0]['folder']."/thumb_".basename($post['filename'].".png?_=".time());
		$markers[0]['img_url'] = "/_uploads/".$markers[0]['folder']."/450_".basename($post['filename'].".png?_=".time());
		
		/*
		if(trim($markers[0]['land_owner'])==""){
			$markers[0]['land_owner'] = $post['useremail'];
		}
		*/
	}
}
if(isset($_GET['print'])){
	echo "<pre>";
	echo $sql."\n";
	echo $uploads_dir;
	print_r($post);
	print_r($markers);
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

function showThumb($src, $thumbWidth, $thumbHeight, $dest="", $thumb=false) {
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
		$side1 = $side;
		$side2 = $side*1.3;
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
	
	
	if(!trim($dest)){
		imagepng( $tmp_img , null, 0);
	}
	else{
		@imagepng ( $tmp_img , $dest, 0);
	}
	// save thumbnail into a file
	
}


?>