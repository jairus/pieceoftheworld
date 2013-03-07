<?php
function isItWatter($lat,$lng) {

    //$GMAPStaticUrl = "https://maps.googleapis.com/maps/api/staticmap?center=".$lat.",".$lng."&size=40x40&maptype=roadmap&sensor=false&zoom=12&key=YOURAPIKEY";  
	$GMAPStaticUrl = "https://maps.googleapis.com/maps/api/staticmap?center=".$lat.",".$lng."&size=40x40&maptype=roadmap&sensor=false&zoom=12";  
	echo $GMAPStaticUrl;
	echo "<br />";
    $chuid = curl_init();
    curl_setopt($chuid, CURLOPT_URL, $GMAPStaticUrl);   
    curl_setopt($chuid, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($chuid, CURLOPT_SSL_VERIFYPEER, FALSE);
    $data = trim(curl_exec($chuid));
    curl_close($chuid);
    $image = imagecreatefromstring($data);

    // this is for debug to print the image
    ob_start();
    imagepng($image);
    $contents =  ob_get_contents();
    ob_end_clean();
    echo "<img src='data:image/png;base64,".base64_encode($contents)."' />";

    // here is the test : I only test 3 pixels ( enough to avoid rivers ... )
    $hexaColor = imagecolorat($image,0,0);
    $color_tran = imagecolorsforindex($image, $hexaColor);

    $hexaColor2 = imagecolorat($image,0,1);
    $color_tran2 = imagecolorsforindex($image, $hexaColor2);

    $hexaColor3 = imagecolorat($image,0,2);
    $color_tran3 = imagecolorsforindex($image, $hexaColor3);

    $red = $color_tran['red'] + $color_tran2['red'] + $color_tran3['red'];
    $green = $color_tran['green'] + $color_tran2['green'] + $color_tran3['green'];
    $blue = $color_tran['blue'] + $color_tran2['blue'] + $color_tran3['blue'];
	
    imagedestroy($image);
    echo "<br />";
	var_dump($red,$green,$blue);
    //int(492) int(570) int(660) 
    if($red == 492 && $green == 570 && $blue == 660)
        return 1;
    else
        return 0;
}

$latlng = explode(",", $_GET['latlong']);


if(isItWatter($latlng[0], $latlng[1])){
	echo "<br />";
	echo "<br />";
	echo "Water";
}
else{
	echo "<br />";
	echo "<br />";
	echo "Land";
}
?>