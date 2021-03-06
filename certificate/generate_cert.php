<?php
//20130213_1360814201.6756
require_once("dompdf_config.inc.php");
include_once(dirname(__FILE__)."/../ajax/global.php"); 
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);


if(isset($_GET['f'])){
	$uploads_dir = dirname(__FILE__).'/../_uploads/'.$_GET['f'];
	$post = unserialize(file_get_contents($uploads_dir."/post.txt"));
	
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
	$land_owner = ($post['land_owner']);
	$title = ($post['title']);
	$detail = ($post['description']);	

	$coords = json_decode(stripslashes($post['coords']));
	$points = $coords->points;
	$strlatlongs = $coords->strlatlongs;
	$t = count($points);
	for($i=0; $i<$t; $i++){
		list($x, $y) = explode("-", $points[$i]);
		break;
	}
	$sql = "select * from `land` where `x`='".$x."' and `y`='".$y."'";
	$land = dbQuery($sql, $_dblink);
	$theid = $land[0]["land_detail_id"];
	
	$post['filename'] = "";
	
	//get image
	$sql = "select * from `pictures` where `land_id`='".$theid."' order by `isMain` desc";
	$pics = dbQuery($sql, $_dblink);
	if(!$pics[0]['id']&&$land[0]['land_special_id']){
		$sql = "select * from `pictures_special` where `land_special_id`='".$land[0]['land_special_id']."' order by `isMain` desc";
		$pics = dbQuery($sql, $_dblink);
	}
	if($pics[0]['id']){
		if(trim($pics[0]['picture'])){
			$folder = explode("_uploads2/",$pics[0]['picture']);
			$filename = $folder[1];
			$filename = dirname(__FILE__)."/../_uploads2/".$filename;
			$post['filename'] = $filename;
		}
	}
	if(!trim($post['filename'])){
		$post['filename'] = dirname(__FILE__)."/../images/place_holder.png";
	}
	else{
		$post['filename'] = str_replace("/var/www/vhosts/s15331327.onlinehome-server.com/httpdocs/_uploads/", "/home/pieceoft/public_html/_uploads/", $post['filename']);
	}
	
}
else{
	exit();
}


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

$html = "<html>
<style>
.image{
	position:absolute; left:100px;
}
.text1{
	position:absolute; left:50px;
}
</style>
<body style='margin:0px;'>
	<div class='image'><img src='http://pieceoftheworld.co/certificate/generate_cert.php?f=".$_GET['f']."&image=1' /></div>
</body>
</html>
";




if(isset($_GET['image'])){

	// Set the content-type
	
	header('Content-Type: image/png');

	// Create the image
	$im = imagecreatefromjpeg("image_h.jpg"); 

	// Create some colors
	$white = imagecolorallocate($im, 255, 255, 255);
	$grey = imagecolorallocate($im, 128, 128, 128);
	$black = imagecolorallocate($im, 0, 0, 0);
	$red = imagecolorallocate($im, 192, 80, 77);
        //imagefilledrectangle($im, 0, 0, 399, 29, $white);
	

	// Add some shadow to the text
	//imagettftext($im, 20, 0, 11, 21, $grey, $font, $text);

	$font = 'arial.ttf';
	imagettftext($im, ceil(20*1.66), 0, ceil(100*1.66), ceil(150*1.66), $red, $font, "ID: ".$theid);


	// Add the text
	//Owner
	$font = 'arialbd.ttf';
	imagettftext($im, ceil(9*1.66), 0, ceil((285-20)*1.66), ceil(303*1.66), $black, $font, "Owner:");
	$font = 'arial.ttf';
	$owner = $land_owner;
	if(!trim($owner)){
		$owner = $useremail;
	}
	imagettftext($im, ceil(9*1.66), 0, ceil((335-20)*1.66), ceil(303*1.66), $black, $font, $owner);
	
	$font = 'arialbd.ttf';
	imagettftext($im, ceil(9*1.66), 0, ceil((285-20)*1.66), ceil(320*1.66), $black, $font, "Date:");
	$font = 'arial.ttf';
	$date = date("M d, Y");
	imagettftext($im, ceil(9*1.66), 0, ceil((322-20)*1.66), ceil(320*1.66), $black, $font, $date);
	
	$font = 'arialbd.ttf';
	imagettftext($im, ceil(9*1.66), 0, ceil((285-20)*1.66), ceil(354*1.66), $black, $font, $title);
	$font = 'arial.ttf';
	$text = $detail;
	$atext = explode(" ", $text);
	$t = count($atext);
	$str = "";
	for($i=0; $i<$t; $i++){
		$str .= $atext[$i]." ";
		if($i%9==0&&$i>0){
			$str .= "\n";
		}
	}
	imagettftext($im, ceil(8*1.66), 0, ceil((285-20)*1.66), ceil(370*1.66), $black, $font, $str);
	
	if($post['filename']){
		$theimage = showThumb($post['filename'], ceil(135*1.66), ceil(135*1.3*1.66), dirname($post['filename'])."/"."cert_".basename($post['filename']).".png", true, true);
		imagecopyresampled( $im, $theimage, ceil(115*1.66), ceil(287*1.66), 0, 0, imagesx($theimage), imagesy($theimage), imagesx($theimage), imagesy($theimage) );
	}
	
	// Using imagepng() results in clearer text compared with imagejpeg()
	imagepng($im, null, 0);
	imagedestroy($im);

}
else if ( isset( $html ) ) {
	
	if($_GET['html']){
		echo $html;
		exit();
	}
	
	$dompdf = new DOMPDF();
	$dompdf->load_html($html);
	
	
	$dompdf->set_paper("8.5x11", "landscape");
	$dompdf->render();

	$dompdf->stream("certificate.pdf");
	
	exit(0);
}

?>