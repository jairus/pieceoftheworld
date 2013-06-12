<?php
require_once 'ajax/global.php';

if($_GET['marker']){

	// Set the content-type
	header('Content-Type: image/png');
	// Create the image
	if($_GET['red']){
		//$im = imagecreatefrompng(dirname(__FILE__)."/images/marker_red.png");
		$im = imagecreatefrompng(dirname(__FILE__)."/images/marker.png");
	}
	else{
		$im = imagecreatefrompng(dirname(__FILE__)."/images/marker.png");
	}
	
	if($_GET['land_special_id']||$_GET['land_detail_id']){
		$im = imagecreatefrompng(dirname(__FILE__)."/images/marker_blue.png");
	}
	
	// Create some colors
	$white = imagecolorallocate($im, 255, 255, 255);
	$grey = imagecolorallocate($im, 128, 128, 128);
	$black = imagecolorallocate($im, 0, 0, 0);
	//imagefilledrectangle($im, 0, 0, 399, 29, $white);

	// The text to draw
	if($_GET['count']){
		$text = $_GET['count'];
		// Replace path by your own font path
		$font = dirname(__FILE__).'/arialbd.ttf';
		
		$minus = 0;
		if(strlen($text)>=3){
			$fontsize = 7;
			$minus = 1;
		}
		else{
			$fontsize = 7;
		}
		$tb = imagettfbbox($fontsize, 0, $font, $text);
		$txtwidth = $tb[2];
		$xpos = (imagesx($im) / 2) - ($txtwidth/2); //to make the text centered
		// Add some shadow to the text
		imagettftext($im, $fontsize, 0, $xpos+1-$minus, 15, $grey, $font, $text);
		// Add the text
		imagettftext($im, $fontsize, 0, $xpos-$minus, 14, $white, $font, $text);
	}

	// Using imagepng() results in clearer text compared with imagejpeg()
	imagealphablending($im, true);	
	imagesavealpha($im, true);
	imagepng($im, null, 9);
	imagedestroy($im);
	exit();
	
}
else if($_GET['special']){
	$sql = "select * from `pictures_special` where `land_special_id`='".$_GET['id']."' and isMain=1";
	$pictures = dbQuery($sql, $_dblink);
	$t = count($pictures);
	if($t){
		$picture = explode("/_uploads2/", $pictures[0]['picture']);
		$dir = dirname($picture[1]);
		$picturex = dirname(__FILE__)."/_uploads2/".$picture[1];
		if(!file_exists($picturex)){
			$picturex = dirname(__FILE__)."/_uploads2/".urldecode($picture[1]);
		}
		
		$picture = $picturex;
		if(file_exists($picture)){
			$im = showThumb($picture, 120, 120*1.3, dirname($picture)."/"."thumb_".basename($picture).".png", true, true);
			showThumb($picture, "450", "300", dirname($picture)."/"."450_".basename($picture).".png", false);
			header("Content-Type: image/png");
			imagepng($im);
			exit();
		}
	}
}
else if($_GET['dir']){
		$picture = explode("/_uploads2/", base64_decode($_GET['dir']));
		$dir = dirname($picture[1]);
		$picturex = dirname(__FILE__)."/_uploads2/".$picture[1];
		if(!file_exists($picturex)){
			$picturex = dirname(__FILE__)."/_uploads2/".urldecode($picture[1]);
		}
		$picture = $picturex;
		if(file_exists($picture)){
			header("Content-Type: image/png");
			showThumbx($picture, 132, 78);
		}
		exit();
}
else{
	$sql = "select * from `pictures` where `land_id`='".$_GET['land_detail_id']."' and isMain=1";
	$pictures = dbQuery($sql, $_dblink);
	$t = count($pictures);
	if($t){
		$picture = explode("/_uploads2/", $pictures[0]['picture']);
		$dir = dirname($picture[1]);
		$picturex = dirname(__FILE__)."/_uploads2/".$picture[1];
		if(!file_exists($picturex)){
			$picturex = dirname(__FILE__)."/_uploads2/".urldecode($picture[1]);
		}
		
		$picture = $picturex;
		if(file_exists($picture)){
			$im = showThumb($picture, 120, 120*1.3, dirname($picture)."/"."thumb_".basename($picture).".png", true, true);
			showThumb($picture, "450", "300", dirname($picture)."/"."450_".basename($picture).".png", false);
			header("Content-Type: image/png");
			imagepng($im);
			exit();
		}
	}
}


/*******************************/
function imagecreatefromfile($src){
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
	return $img;
}
function showThumb($src, $thumbWidth, $thumbHeight, $dest="", $thumb=false, $returnim = false) {
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
		if(trim($dest)){
			imagepng ( $tmp_img , $dest, 0);
		}
		return $tmp_img;
	}
	// save thumbnail into a file	
}


function showThumbx($src, $thumbWidth, $thumbHeight, $dest="") {
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

/*******************************************************/
class CircleCrop{
    private $src_img;
    private $src_w;
    private $src_h;
    private $dst_img;
    private $dst_w;
    private $dst_h;

    public function __construct($img, $dstFile="", $dstWidth="", $dstHeight="")
    {
        $this->src_img = $img;
        $this->src_w = imagesx($img);
        $this->src_h = imagesy($img);
        //$this->dst_w = $dstWidth;
        //$this->dst_h = $dstHeight;
		if(!trim($dstWidth)){
			$this->dst_w = imagesx($img);
			$this->dst_h = imagesy($img);
		}
		else{
			$this->dst_w = $dstWidth;
			$this->dst_h = $dstHeight;
		}
		$this->dst_file = $dstFile;
    }

    public function __destruct()
    {
        if (is_resource($this->dst_img))
        {
            imagedestroy($this->dst_img);
        }
    }

    public function display()
    {
        //header("Content-type: image/png");
		if($this->dst_file){
			imagepng($this->dst_img, $this->dst_file);
		}
		else{
			imagepng($this->dst_img);
		}
        return $this;
    }

    public function reset()
    {
        if (is_resource(($this->dst_img)))
        {
            imagedestroy($this->dst_img);
        }
        $this->dst_img = imagecreatetruecolor($this->dst_w, $this->dst_h);
        imagecopy($this->dst_img, $this->src_img, 0, 0, 0, 0, $this->dst_w, $this->dst_h);
        return $this;
    }

    public function size($dstWidth, $dstHeight)
    {
        $this->dst_w = $dstWidth;
        $this->dst_h = $dstHeight;
        return $this->reset();
    }

    public function crop()
    {
        // Intializes destination image
        $this->reset();

        // Create a black image with a transparent ellipse, and merge with destination
        $mask = imagecreatetruecolor($this->dst_w, $this->dst_h);
        $maskTransparent = imagecolorallocate($mask, 255, 0, 255);
        imagecolortransparent($mask, $maskTransparent);
        imagefilledellipse($mask, $this->dst_w / 2, $this->dst_h / 2, $this->dst_w, $this->dst_h, $maskTransparent);
        imagecopymerge($this->dst_img, $mask, 0, 0, 0, 0, $this->dst_w, $this->dst_h, 100);

        // Fill each corners of destination image with transparency
        $dstTransparent = imagecolorallocatealpha($this->dst_img, 255, 0, 255, 127);
        imagefill($this->dst_img, 0, 0, $dstTransparent);
        imagefill($this->dst_img, $this->dst_w - 1, 0, $dstTransparent);
        imagefill($this->dst_img, 0, $this->dst_h - 1, $dstTransparent);
        imagefill($this->dst_img, $this->dst_w - 1, $this->dst_h - 1, $dstTransparent);
        imagecolortransparent($this->dst_img, $dstTransparent);

        return $this;
    }
	
	function getResource(){
		return $this->dst_img;
	}

}


?>