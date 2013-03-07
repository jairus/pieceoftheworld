<?php
require_once 'global.php';
$land_id = @$_GET['land_id'];
$conOptions = GetGlobalConnectionOptions();
$con = mysql_connect($conOptions['server'], $conOptions['username'], $conOptions['password']);
if (!$con) { die('[[]]'); }
mysql_select_db($conOptions['database'], $con);
$sql = "SELECT land_special_id, picture FROM land WHERE id=".$land_id;
$result = mysql_query($sql);
$row = null;
if ($result != null) {
	$row = mysql_fetch_array($result);
}
if ($row != null) {
	$filename = '../images/thumbs/land_id_'.$land_id;
	if(trim($row[1])){
		$file = fopen($filename,'w');
		$success = fwrite($file, $row[1]);
		fclose($file);
		if ($success == false) {
			unlink($filename);
		}
			
		if (!file_exists($filename) || filesize($filename) == 0 || 1) {
			$file = fopen($filename,'w');
			$success = fwrite($file, $row[1]);
			if($success){
				showThumb($filename, "120", 120*1.3, $filename."_thumb", true);
			}
			
			/*
			$file = fopen($filename,'w');
			$success = fwrite($file, $row[1]);
			fclose($file);
			if ($success == false) {
				unlink($filename);
			}
			*/
		}
		if ($row[0] != null) {
			$filename = '../images/thumbs/land_special_id_'.$row[0];
			$file = fopen($filename,'w');
			$success = fwrite($file, $row[1]);
			fclose($file);
			if ($success == false) {
				unlink($filename);
			}
				
			$file = fopen($filename,'w');
			$success = fwrite($file, $row[1]);
			if($success){
				showThumb($filename, "120", 120*1.3, $filename."_thumb", true);
			}
			
			/*
			if (!file_exists($filename) || filesize($filename) == 0) {
				$file = fopen($filename,'w');
				$success = fwrite($file, $row[1]);
				fclose($file);
				if ($success == false) {
					unlink($filename);
				}
			}
			*/
		}
	}
	else{
		
		if ($row[0] != null) {
			$contents = file_get_contents(dirname(__FILE__)."/../images/place_holder.png");
			$filename = dirname(__FILE__).'/../images/thumbs/land_special_id_'.$row[0];
			echo $filename;
			echo "<br />";
			file_put_contents($filename, $contents);
			
			$contents = file_get_contents(dirname(__FILE__)."/../images/place_holder_small.png");
			$filename = dirname(__FILE__).'/../images/thumbs/land_special_id_'.$row[0].'_thumb';
			echo $filename;
			echo "<br />";
			file_put_contents($filename, $contents);
		}
		else{
			$contents = file_get_contents(dirname(__FILE__)."/../images/place_holder.png");
			$filename = dirname(__FILE__).'/../images/thumbs/land_id_'.$land_id;
			echo $filename;
			echo "<br />";
			file_put_contents($filename, $contents);
			
			$contents = file_get_contents(dirname(__FILE__)."/../images/place_holder_small.png");
			$filename = dirname(__FILE__).'/../images/thumbs/land_id_'.$land_id."_thumb";
			echo $filename;
			echo "<br />";
			file_put_contents($filename, $contents);
		}
	}
}

echo '[[]]';
mysql_close($con);

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
			@imagepng ( $tmp_img , $dest, 0);
		}
	}
	else{
		return $tmp_img;
	}
	// save thumbnail into a file
	
}
?>