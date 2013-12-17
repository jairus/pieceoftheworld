<?php 
session_start(); 
if(strtolower($_SERVER['HTTP_HOST'])=='www.pieceoftheworld.co'||strtolower($_SERVER['HTTP_HOST'])=='pieceoftheworld.co'){
	$url = "http://pieceoftheworld.com/".ltrim($_SERVER['REQUEST_URI'], "/");
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: '.$url);
}
if(strpos(strtolower($_SERVER['HTTP_HOST']), "www.")===0){
	$url = "http://pieceoftheworld.com/".ltrim($_SERVER['REQUEST_URI'], "/");
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: '.$url);
}

if($_GET['signupfb']){
	$_SESSION['signupfb'] = 1;
}

if(($_GET['start']||$_SERVER['QUERY_STRING'])&&!$_SESSION['start']){
	$_SESSION['start'] = 1;
	/*
	if(!$_GET['start']&&$_SERVER['QUERY_STRING']){
		$url = trim("http://pieceoftheworld.com/?".$_SERVER['QUERY_STRING'], "/");
	}
	else{
		$url = trim("http://pieceoftheworld.com/", "/");
	}
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: '.$url);
	*/
}

if($_GET['home']){
	unset($_SESSION['start']);
	$url = trim("http://pieceoftheworld.com/", "/");
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: '.$url);
}

if(!trim($_SERVER['QUERY_STRING'])){
	unset($_SESSION['start']);
}

if(!$_SESSION['start']){
	include_once(dirname(__FILE__)."/home.php");
	exit();
}


include_once(dirname(__FILE__).'/ajax/global.php');

if($_GET['trends']){
	?>
	<div class="inner">
		<div style="position:relative; width:100%; height:auto;">
		<ul class="trendscontent">
			<!--<li class="trend-label">Trending topics</li>-->
			<?php
			$sql = "select 
			`land`.`web_user_id`, 
			`land`.`x`,
			`land`.`y`,
			`title`, 
			`land_owner`  
			from `land_detail` 
			left join `land` on (`land`.`land_detail_id` = `land_detail`.`id`)
			
			where `land_detail`.`id` in 
				(select `land_detail_id` from `land` where `web_user_id`<>0) 
				group by `title`
				order by `land_detail`.`id` desc limit 10";
			$a = dbQuery($sql, $_dblink);
			$sql = "select 
			`land`.`x`,
			`land`.`y`,
			`title`, 
			`land_owner`,
			`land_special`.`web_user_id`
			from `land_special` 
			left join `land` on (`land`.`land_special_id` = `land_special`.`id`)
			where `land_special`.`web_user_id`<>0 
			group by `title`
			order by `land_special`.`id` desc limit 10";
			$b = dbQuery($sql, $_dblink);
			$c = array_merge($b, $a);
			shuffle($c);
			$t = count($c);
			for($i=0; $i<$t; $i++){
				if(!trim($c[$i]['land_owner'])){
					$str = "'".trim($c[$i]['title'])."'";
					?>
					<li>
						<a href="?xy=<?php echo $c[$i]['x']."~".$c[$i]['y']; ?>" class="search_link" name="<?php echo htmlentities($str) ?>" rel="nofollow"><?php echo stripslashes(strip_tags($str)); ?></a>
						<em class="description"></em>
					</li>
					<?php
				}
				else{
					$str = trim($c[$i]['land_owner'])." just bought '".trim($c[$i]['title'])."'";
					?>
					<li>
						<a href="?xy=<?php echo $c[$i]['x']."~".$c[$i]['y']; ?>" class="search_link" name="<?php echo htmlentities($str) ?>" rel="nofollow"><?php echo stripslashes(strip_tags($str)); ?></a>
						<em class="description"></em>
					</li>
					<?php
				}
			}
			?>
			<!--<li>
				<a href="search?q=DIUSTIN+BIBER" class="search_link" name="Sophie1 just bought 'Where I grew up'" rel="nofollow">Sophie1 just bought "Where I grew up"</a>
				<em class="description"></em>
			</li>-->
		</ul>
		</div>
	</div>
	<!--<span class="fade fade-left">&nbsp;</span><span class="fade fade-right">&nbsp;</span>-->
	<?php
	exit();
}

if($_GET['coords']){
	$_SESSION["coords"] = $_POST['data'];
	$_SESSION["buydetails"] = $_POST['buydetails'];
	print_r($_SESSION["coords"]);
	exit();
}else if($_GET['saveblockdetails']){
	$data = json_decode(stripslashes($_POST['data']));
	print_r($data);
	
	list($x, $y) = explode("-", $data->points);
	list($lat, $long) = explode(",", $data->strlatlong);
	
	$sql ="select * from `land` where 
	`x` = '".mysql_real_escape_string($x)."' and 
	`y` = '".mysql_real_escape_string($y)."'
	"; 
	$r = dbQuery($sql, $_dblink);
	if($r[0]['id']){
		print_r($r);
		$sql = "update `land` set 
		`country` = '".mysql_real_escape_string($data->country)."',
		`city` = '".mysql_real_escape_string($data->city)."',
		`region` = '".mysql_real_escape_string($data->region)."',
		`areatype` = '".mysql_real_escape_string($data->areatype)."',
		`lat` = '".mysql_real_escape_string($lat)."',
		`long` = '".mysql_real_escape_string($long)."'
		where 
		`x` = '".mysql_real_escape_string($x)."' and 
		`y` = '".mysql_real_escape_string($y)."'
		";
		$r = dbQuery($sql, $_dblink);
	}else{
		$sql = "insert into `land` set 
		`country` = '".mysql_real_escape_string($data->country)."',
		`city` = '".mysql_real_escape_string($data->city)."',
		`region` = '".mysql_real_escape_string($data->region)."',
		`areatype` = '".mysql_real_escape_string($data->areatype)."',
		`lat` = '".mysql_real_escape_string($lat)."',
		`long` = '".mysql_real_escape_string($long)."',
		`x` = '".mysql_real_escape_string($x)."',
		`y` = '".mysql_real_escape_string($y)."'
		";
		$r = dbQuery($sql, $_dblink);
	}
	echo $sql;
	exit();
}

if($_GET['a']){
	$sql = "select * from `affiliates` where `id`='".$_GET['a']."'  and `active`=1";
	$r = dbQuery($sql, $_dblink);
	$r = $r[0];
	if($r['id']){
		$_SESSION['affid'] = $_GET['a'];
		setcookie("affid", "", (time()-(3600*24*30)));  /* 30 days */
		setcookie("affid", $_SESSION['affid'], (time()+(3600*24*30)));  /* 30 days */
		$sql = "insert into `affiliate_clicks` set 
			`affiliate_id`='".$r['id']."',
			`server_json`='".mysql_real_escape_string(json_encode($_SERVER))."',
			`dateadded`=NOW()
		";
		dbQuery($sql, $_dblink);
	}
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: http://pieceoftheworld.com');
	exit();
}
if($_COOKIE['affid']){
	//print_r($_COOKIE);
	$_SESSION['affid'] = $_COOKIE['affid'];
}

if($_GET['px']!=""){
	$_SESSION['px'] = $_GET['px'];
	exit();
}

?>
<!doctype html>
<html lang="us">
<head>
<meta property='fb:app_id' content='454736247931357' />
<meta property='og:image' content='http://pieceoftheworld.com/images/og_fb.jpg' />
<meta property='og:title' content='Piece of the World' />
<?php
if(!$_SERVER['QUERY_STRING']){
	?><meta property='og:url' content='http://pieceoftheworld.com/'/><?php
}
?>
<meta property='og:type' content='website'/>
<meta property='og:description'  content="The world is now for sale, piece by piece. Claim your land and own it forever or give it away as a fun or romantic gift. Upload a picture, write a text and receive a certificate of ownership. Be creative and more importantly have fun!"/>
<meta name="description" content="The world is now for sale, piece by piece. Claim your land and own it forever or give it away as a fun or romantic gift. Upload a picture, write a text and receive a certificate of ownership. Be creative and more importantly have fun!"/>
<meta charset="utf-8">
<title>PieceoftheWorld</title>
<link rel="stylesheet" type="text/css" href="http://cdn.pieceoftheworld.com/css/styles.css" />
<script src="http://cdn.pieceoftheworld.com/js/jquery-1.8.3.min.js" type="text/javascript"></script>
<link href="http://pieceoftheworld.com/css/twitmarquee.css" media="screen" rel="stylesheet" type="text/css" />
<style>
.ui-dialog {
    width: 450px;
	top:135px;
}
#jquery-lightbox {
    z-index: 10000;
}
#jquery-overlay {
    z-index: 9000;
}
#cboxTitle{position:absolute; bottom:4px; left:0; text-align:center; width:90%; color:#000; font-size:11px;}

#loadinggrid{
	z-index: 1010; 
	width:300px; 
	height:100px; 
	position:absolute; 
	background-color: #006A9B;
	top:-10000px;
	color: #FFFFFF;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 14px;
    font-weight: bold;
	cursor:pointer;
	margin-bottom:3px;
	border:1px solid white;
	
}
#loadinggrid *{
	color:white;
}

.ui-autocomplete .ui-corner-all{
	font-size:12px;
}

.select{
	width:276px; 
	height:32px; 
	background-color: #006A9B; 
	color:white; 
	font-family: Arial,Helvetica,sans-serif; 
	font-size: 14px; 
	font-weight: bold;
}
.div{
	width:276px; 
	background-color: #00A4DA; 
	color:white; 
	font-family: Arial,Helvetica,sans-serif; 
	font-size: 12px; 
	font-weight: bold;
	padding-top:5px; 
	padding-bottom:5px;
	margin-top:5px;
}
a:link img{
	border:none;
}

</style>





<!--MAKE BOX DRAGGABLE-->
<script src="http://cdn.pieceoftheworld.com/js/draggable/jquery-1.9.1.js"></script>
<script src="http://cdn.pieceoftheworld.com/js/draggable/jquery-ui.js"></script>
<link href="http://cdn.pieceoftheworld.com/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<!--END OF MAKE BOX DRAGGABLE-->

<!-- colorbox -->

<script src="http://cdn.pieceoftheworld.com/js/colorbox-master/jquery.colorbox.js"></script>
<link rel="stylesheet" type="text/css" href="http://cdn.pieceoftheworld.com/js/colorbox-master/example1/colorbox.css" media="screen" />

<!-------------->


<!--INITIALIZE MAP-->
<script src="http://maps.google.com/maps/api/js?sensor=true&libraries=geometry" type="text/javascript"></script>
<!--<script src="js/main.php?<?php echo $_SERVER['QUERY_STRING']; ?>" type="text/javascript"></script>-->

<script>
<?php
include_once(dirname(__FILE__)."/js/main.php");
?>
</script>
<!--END OF INITIALIZE MAP-->

<!--<script src="js/zoi.php?_<?php echo time(); ?>" type="text/javascript"></script>-->
<script>
<?php
include_once(dirname(__FILE__)."/js/zoi.php");
?>

<?php
if($_SESSION['start']&&!$_GET['xy']){
	?>
	link = "http://pieceoftheworld.com/";
	history.pushState(null, null, link);
	<?php
}
?>
</script>




<link href="css/main2.css?_=<?php echo time(); ?>" rel="stylesheet">
<style>
.longbutton2{
	background-color: #006A9B;
	height: auto;
	padding: 5px;
	text-align: center;
	width: 290px;
	color: #FFFFFF;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 14px;
    font-weight: bold;
	border:0px;
	cursor:pointer;
	margin-bottom:3px;
	border:1px solid white;
}


#popup_content{
	background-color: #ffffff;
}

#popup_bottom {
    background-color: #ffffff;
}
#popup_top {
    background-color: #ffffff;	
}
.text_2 {
    color: #000000;
}
.text_1 {
    color: #000000;
	font-size:13px;
}
a:hover{
	color:#000000;
	text-decoration:underline;
}

#popup_top_arc {
    background-image: url("images/popup_top_right_white.png");
}
.details a:link, .details a:hover, .details a:visited {
    color: #000000;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 13px;
    text-decoration: none;
}
#table_main_info a:link, #table_main_info a:hover, #table_main_info a:visited{
	color: #000000;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 13px;
    text-decoration: none;
}

.select {
    background-color: #5F96E6 !important;
}
.longbutton {
    background-color: #5F96E6 !important;
}

#top_list_title_1 {
    background-color: #5F96E6;
}

#top_list_title_1 .text_2{
	color:white;
}
#info-title{
	color:white;
}
.text_3 {
    color: #000000;
}
.link_1:hover {
    color: #FFFFFF;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 16px;
    font-weight: bold;
    text-decoration: none;
}

#top_list_categories {
    background-color: #5F96E6;
}
#top_list_categories:hover {
    background-color: #A5BFDD;
}
#top_list_categories_active {
    background-color: #A5BFDD;
}
#top_list_items{
	background-color: #A5BFDD;
}
#top_list_items:hover{
	background-color: #5F96E6;
}
#links {
    float: left;
    height: auto;
    margin-left: 47px;
    margin-top: 16px;
    width: auto;
}

#popup {
    top: 135px;
}
#popup_content{
	border:0px;
}
#theprice, #info-detail{
	color:#000000;
}

#search_inside{
	border:0px;
}
</style>


<script>
$(document).ready(function() {
	<?php
	if(!isset($_SESSION['trailer'])&&!count($_GET)){
		?> jQuery('#table_introxx').show(); <?php
	}
	?>
	$('#tags_1').tagsInput({width:'auto'});
});

//CLOSE INTRO
function closeIntro(type){
	if(type==1){
		//jQuery('#table_introxx').hide();
		jQuery('#table_introxx').css({'display':'none'});
		jQuery("#introvid").html("");
	}else if(type==2){
		jQuery.ajax({
			type: 'GET',
			url: "ajax/ajax.php?action=createsession",
			data: '',
			success: function(data) {
				//jQuery('#table_introxx').hide();
				jQuery('#table_introxx').css({'display':'none'});
				jQuery("#introvid").html("");
			}
		});
	}
}
//END OF CLOSE INTRO
</script>
<script>
function takeMe(){
	geocoder.geocode( {'address': jQuery("#search_enteraplace").val() }, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			
			map.setMapTypeId(google.maps.MapTypeId.HYBRID);
			map.setCenter(results[0].geometry.location);
			map.setZoom(17);
		} 
		else {
			alert("That address wasn't found!");
			//alert('Geocode was not successful for the following reason: ' + status);
		}
		/*
		response(jQuery.map(results, function(item) {
			jQuery("#latitude").val(item.latitude);
			jQuery("#longitude").val(item.longitude);
			var location = new google.maps.LatLng(item.latitude, item.longitude);
			//searchMarker.setPosition(location);
			map.setZoom(15);
			map.setCenter(location);
			//}
		}));
		*/
		
	})
}
jQuery(function(){
	jQuery("#search_enteraplace").keypress(function(event) {
		if ( event.which == 13 ) {
			takeMe();
		}
	});

});
</script>
</head>
<body>
<div id='galleryresults'>
	<div id='gallery_tab_wrapperonly'></div>
</div>

<div id="fb-root"></div>
<table id='loadinggrid' ><tr><td valign='middle' align='center'>Loading Data...</td></tr></table>
<div id="header_bg">
	<div id="header">
		<div id="logo"><a href="?start=1"><img src="alt/images/logo.png" alt="PieceoftheWorld" title="PieceoftheWorld" /></a></div>
		<!--
		<div id="search">
			<div id="search_inside">
				<div id="search_field"><input type="text" class="input_1" style="width:257px; height:20px; padding:0px 10px;" id="search_enteraplace" name="search" value="Search for an address or place to buy" onFocus="if(this.value=='Search for an address or place to buy'){ this.value=''; }" onBlur="if(this.value==''){ this.value='Search for an address or place to buy'; }" /></div>
				<div id="search_button"><img src="images/btn_search.png" border="0" alt="Take me there..." title="Take me there..." style="cursor:pointer;" /></div>
			</div>
		</div>
		<div id="links" class="text_1">
		<a alt="Home" title="Home" style="cursor:pointer;" id="menu_top_lists" onClick="self.location='?start=1'" class="link_1">Home</a>&nbsp; &nbsp; 
		<a alt="Top Lists" title="Top Lists" style="cursor:pointer;" id="menu_top_lists" onClick="openClosePopUp('top_lists');" class="link_1">Top Lists</a> &nbsp; &nbsp;<?php
			if($_SESSION['userdata']){
				?><div style='display:inline' id='sign_in'>
					<a style='cursor:pointer' class="link_1" onClick="logoutUser();">Sign Out</a>&nbsp;&nbsp;&nbsp;<a class="link_1" style='cursor:pointer;' onClick="updateProfile(); openClosePopUp('facebook'); ">Profile</a>&nbsp;
				</div><?php
			}
			else{
				?><div style='display:inline' id='sign_in'>
					<a onclick="updateProfile(); openClosePopUp('facebook');" style="cursor:pointer;" class="link_1">Sign in</a>
				</div><?php
			}
			?>
			
			
		</div>
		-->
		<div style="padding-top:10px; float:right; right:100px" class="fb-like" data-href="http://pieceoftheworld.com/" data-layout="button" data-action="like" data-show-faces="false" data-share="true"></div>
		<style>
		
		
		.mmenus{
			position:absolute; 
			background:#3777A8; 
			color:white; 
			top:80px;
			font-family:arial;
			padding:5px;
			font-size:12px;
			cursor:pointer;
		}
		.mmenus a, .mmenus a:link, .mmenus a:hover{
			color:white; 
			font-family:arial;
			font-size:12px;
			font-weight:normal;
		}
		</style>
		<div style='float:right; position:relative'>
			<div style='left:-143px; padding:0px; top:82px; position:absolute' id="search_field"><input type="text" class="input_1" style="border: 1px solid #3777A8;; padding:1px 3px 2px 3px; border-bottom-left-radius: 0px; border-top-left-radius: 0px; width:257px; height:20px;" id="search_enteraplace" name="search" value="Search for an address or place to buy" onFocus="if(this.value=='Search for an address or place to buy'){ this.value=''; }" onBlur="if(this.value==''){ this.value='Search for an address or place to buy'; }" /></div>
			<div class='mmenus' style='left:-193px;' onClick="self.location='?start=1'" >Home</div>
			<div class='mmenus' style='left:129px; width:50px;' onClick="openClosePopUp('top_lists');">Top List</div>
			<?php
			if($_SESSION['userdata']){
				?>
				<div id='sign_in'>
					<div class='mmenus' style='left:195px; width:50px;'>
						<a style='cursor:pointer' class="link_1" onClick="logoutUser();">Sign Out</a>
					</div>
					<div class='mmenus' style='left:261px; width:40px;'>
						<a class="link_1" style='cursor:pointer;' onClick="updateProfile(); openClosePopUp('facebook'); ">Profile</a>
					</div>
				</div>
				<?php
			}
			else{
				?>
				<div id='sign_in'>
					<div class='mmenus' style='left:195px; width:40px;' id='sign_in'>
						<a onclick="updateProfile(); openClosePopUp('facebook');" style="cursor:pointer;" class="link_1">Sign in</a>
					</div>
				</div>
				<?php
			}
			?>
			
			
		</div>
	</div>
</div>
<!--<div id="header_arc"></div>-->
<div id="map_canvas"></div>
<div id="footer" style='background:#ffffff; height:30px'>
	<style>
	#trends li {
    background-color: #fffff;
    border: 1px solid #ffffff;
	}
		
	#trends a.active, #trends a:hover {
		color: #5F96E6 !important;
	}
	#trends{
		width:950px;
	}
	#trends .inner{
		width:950px;
	}
	.search_link{
		color: #007DAF !important;
	}
	</style>
	<div id="updates" style='float:left; padding-top:0px; padding-left:170px;'>
		<table width="882" height="28" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td valign="top" width="950px">
				<div id="trends">
					
				</div>
				<script>
				var page={};
				jQuery.ajax({
					dataType: "html",
					async: true,
					url: "?trends=1",
					success: function(data){
						jQuery("#trends").html(data);
						new FrontPage().init();
						page.trendDescriptions = {};
						loadTrendDescriptions();
					}
				});
				</script>
				<div class="trendtip">
					<div class="trendtip-content">
						<div>Trending right now:</div>
						<a class="trendtip-trend"></a>
						<div class="trendtip-why">
							Why?
							<span class="trendtip-desc"></span>
							<span class="trendtip-source">Source: <span>What the Trend?</span></span>
						</div>
					</div>
					<div class="trendtip-pointer">&nbsp;</div>
				</div>
				
			</td>
		  </tr>
		</table>
	</div>
	<div style="float:right; width:auto; height:auto; margin-right:145px; margin-top:8px; color:#00; font-family:Arial, Helvetica, sans-serif; font-size:11px;">Copyright Piece of the World Pte Ltd 2013</div>
</div>



<!--POPUP-->
<div id="popup" style="display:none; width:312px;">
	<?php
	/*
	?>
	<div id="popup_top">&nbsp;</div>
	<div id="popup_top_arc">&nbsp;</div>
	<?php
	*/
	?>
	<div id="popup_content" style='padding:5px; border:0px solid #7F97E8'>
		<div id="popup_header">
			<!--
			<div id="popup_icon_interscape"><img src="http://cdn.pieceoftheworld.com/images/interscape_blue.png" width="21" height="22" border="0" /></div>
			<div id="popup_title_interscape" class="text_2">InterScape</div>
			-->
			
		</div>
		<div id="popup_main_content">
			<div id="content_about" style="display:none;">
				<div class="text_3">
				<p style="padding:0px; margin:0px;" class="text_2">Dear Citizen of the World</p>
				<p><hr /></p>			
				<p>Welcome to <a target="_blank" href="http://www.PieceoftheWorld.com">PieceoftheWorld.com</a>, the site where you set your mark on the world. You will be in charge and have full control of your virtual piece - upload a picture and write a description.</p>
				
				<p>You will receive a certificate by email proving that you are the exclusive owner. Should you receive a good offer, you can sell your piece of the world, hopefully making a profit.</p>
				
				<p>Each piece represents an acre of our planet and it can be yours today! What part of the world means something special to you? That cafe where you met your spouse? The arena of your favorite football team? Your childhood home? Your school or university? One square costs $ 9.90.</p>
				
				<p>So join us and set your mark - get your piece of the world today.</p>
				
				<p>Piece of the World team</p>
				
				<p style="padding:0px; margin:0px;">Contact us:<br /><a href="mailto:contact@pieceoftheworld.com">contact@pieceoftheworld.com</a></p>
				<br />
				<a style='font-size:12px; text-decoration:underline' href="#" onClick="window.showModalDialog('pac.php',0, 'dialogWidth:600px; dialogHeight:400px; center:yes; resizable: no; status: no');">Privacy Policy</a>
				<br />&nbsp;
				</div>	
			</div>
			<div id="content_top_lists" style="display:none;">
				<table width="290" border="0" cellspacing="0" cellpadding="0">
				  <tr>
					<td width="140" valign="top">
					
						<?php
						$categories = array(0=>"Biggest Land Owners", 1=>"Most Expensive Lands", 2=>"Most Viewed Lands", 3=>"Biggest Water Owners", 4=>"Most Liked Lands");
						
						$t = count($categories);
						
						for($i=0; $i<$t; $i++){
							echo '<div id="top_list_categories" class="text_3 cat'.$i.'" onclick="step1(\''.$categories[$i].'\', \''.$i.'\');">'.$categories[$i].'</div>';
						}
						?>
						
						<div id="top_list_img_1">
							<div id="slideshow1" style="display:none;"><img src="http://cdn.pieceoftheworld.com/images/others_0/1.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow2" style="display:none;"><img src="http://cdn.pieceoftheworld.com/images/others_0/2.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow3" style="display:none;"><img src="http://cdn.pieceoftheworld.com/images/others_0/3.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow4" style="display:none;"><img src="http://cdn.pieceoftheworld.com/images/others_0/4.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow5" style="display:none;"><img src="http://cdn.pieceoftheworld.com/images/others_0/5.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow6" style="display:none;"><img src="http://cdn.pieceoftheworld.com/images/others_0/6.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow7" style="display:none;"><img src="http://cdn.pieceoftheworld.com/images/others_0/7.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow8" style="display:none;"><img src="http://cdn.pieceoftheworld.com/images/others_0/8.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow9" style="display:none;"><img src="http://cdn.pieceoftheworld.com/images/others_0/9.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow10" style="display:none;"><img src="http://cdn.pieceoftheworld.com/images/others_0/10.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div style="display:none;">
								<a id="slide_link1" onClick="slideGoToSlide(1); return false;"></a>
								<a id="slide_link2" onClick="slideGoToSlide(2); return false;"></a>
								<a id="slide_link3" onClick="slideGoToSlide(3); return false;"></a>
								<a id="slide_link4" onClick="slideGoToSlide(4); return false;"></a>
								<a id="slide_link5" onClick="slideGoToSlide(5); return false;"></a>
								<a id="slide_link6" onClick="slideGoToSlide(6); return false;"></a>
								<a id="slide_link7" onClick="slideGoToSlide(7); return false;"></a>
								<a id="slide_link8" onClick="slideGoToSlide(8); return false;"></a>
								<a id="slide_link9" onClick="slideGoToSlide(9); return false;"></a>
								<a id="slide_link10" onClick="slideGoToSlide(10); return false;"></a>
							</div>
							
						</div>
					</td>
					<td width="10">&nbsp;</td>
					<td width="140" valign="top">
						<div class="searching" style="display:none; color:#FFFFFF;">Searching...</div>
						<div id="step2" style="display:none;">
							<div id="results2">
								<div id="tab_wrapperonly2" style="overflow-y:auto; overflow-x:hidden; height:auto; max-height:450px;"></div>
							</div>
						</div>
						<div id="step3" style="display:none;">
							<div id="results3">
								<div id="tab_wrapperonly3" style="overflow-y:auto; overflow-x:hidden; height:auto; max-height:450px;"></div>
							</div>
						</div>
						<div id="step4" style="display:none;">
							<div id="results4">
								<div id="tab_wrapperonly4" style="overflow-y:auto; overflow-x:hidden; height:auto; max-height:450px;"></div>
							</div>
						</div>
						<div id="step5" style="display:none;">
							<div id="results5">
								<div id="tab_wrapperonly5" style="overflow-y:auto; overflow-x:hidden; height:auto; max-height:450px;"></div>
							</div>
						</div>
						<div id="step6" style="display:none;">
							<div id="results6">
								<div id="tab_wrapperonly6" style="overflow-y:auto; overflow-x:hidden; height:auto; max-height:450px;"></div>
							</div>
						</div>
						<div id="step7" style="display:none;">
							<div id="results7">
								<div id="tab_wrapperonly7" style="overflow-y:auto; overflow-x:hidden; height:auto; max-height:450px;"></div>
							</div>
						</div>
					</td>
				  </tr>
				</table>
				<div style="padding-top:10px; text-align:center;" class="text_3">The world for sale - piece by piece</div>
			</div>
			<div id="content_tutorials" style="display:none;">CONTENT GOES HERE...</div>
			<div id="content_info" style="display:none;">
				<?php include_once('includes/contentinfo.php'); ?>
			</div>
			<div id='content_buy' style="display:none;">
				<?php include_once('includes/contentbuy.php'); ?>
			</div>
			<style>
			#content_cta div{
				padding-top:0px;
			}
			</style>
			<div id="content_cta" style="display:none;">
			<?php
			/*
			?><table width='100%'>
			<tr>
				<td colspan=2 style='color:#000000; font-family:Arial; font-size:14px; font-weight:bold'>Buy a Romantic Gift</td>
			</tr>
			<tr>
				<td valign='top'><img src='http://cdn.pieceoftheworld.com/images/cta1.png' /></td>
				<td valign='top' style='color:#000000; font-family:Arial; font-size:12px;' >
				 "The place where she said yes! I bought this to my wife on our anniversary" /Johan
				</td>
			</tr>
			<tr>
				<td colspan=2 style='color:#000000; font-family:Arial; font-size:14px; font-weight:bold'>Be Creative</td>
			</tr>
			<tr>
				<td valign='top'><img src='http://cdn.pieceoftheworld.com/images/cta2.png' /></td>
				<td valign='top' style='color:#000000; font-family:Arial; font-size:12px;' >
				 "I bought Old Trafford because I’m a Liverpool fan" /Liverpool forever
				</td>
			</tr>
			<tr>
				<td colspan=2 style='color:#000000; font-family:Arial; font-size:14px; font-weight:bold'>Buy your own virtual place</td>
			</tr>
			<tr>
				<td valign='top'><img src='http://cdn.pieceoftheworld.com/images/cta3.png' /></td>
				<td valign='top' style='color:#000000; font-family:Arial; font-size:12px;' >
				  "My very own beach! =)" /Sarah
				</td>
			</tr>
			</table>
			<?php
			*/
			?>
			<table width='100%'>
			<tr>
				<td style='color:#000000; font-family:Arial; font-size:14px; font-weight:bold'>
				<!--
				<div><a style='font-size:16px; text-decoration:underline; color:#000000'>Welcome to pieceoftheworld.com</a></div>
				<div>1. Zoom or Search</div>
				<div>2. A grid will appear</div>
				<div>3. Select the piece you want</div>
				-->
				<div><img src='images/intro_interscape.jpg'></div>
				</td>
			</tr>
			</table>
			</div>
			<div id="content_facebook" style="display:none;">
				<div id='userProfile'>
					<table width="100%" border="0" cellspacing="0" cellpadding="0">
						<tr style='display:none'>
							
							<td class="text_1" style='vertical-align:middle; text-align:right; width:160px'>
								<a id='profile_name'><?php 
									if(trim($_SESSION['userdata']['name'])){
										echo $_SESSION['userdata']['name']; 
									}
									else{
										echo $_SESSION['userdata']['useremail']; 
									}
								?></a>&nbsp;&nbsp;[ <a style='cursor:pointer' onClick="logoutUser();">Log Out</a> ]&nbsp;&nbsp;
							</td>
							<td class="text_1" id='profile_image' style='width:30px;'>
								<?php
								if($_SESSION['userdata']['fb_id']){
									?><img src="http://graph.facebook.com/<?php echo $_SESSION['userdata']['fb_id']; ?>/picture" style="height:30px; width:30px;"><?php
								}
								?></td>
							</tr>
						<tr>
							<td align='center' colspan=2 id='ownedLandList'>
								
							</td>
						</tr>
					</table>
				</div>
				<form id="loginForm" style='display:none'>
				<table width="100%" border="0" cellspacing="0" cellpadding="3">
				  <tr>
					<td align='center'>
						<input type="hidden" name="fb_id" class="fb_id" />
						<input type="hidden" name="name" class="name" />
						<input type="hidden" name="gender" class="gender" />
						<input type="hidden" name="location" class="location" />
						<input type="text" name="email" style="width:270px; height:30px;" class="input_3" placeholder="E-mail" />
					</td>
				  </tr>
				  <tr>
					<td align='center'>
						<input type="password" name="password" style="width:270px; height:30px;" class="input_3" placeholder="Password" />
					</td>
				  </tr>
				  <tr>
					<td align='center'>
						<input class='longbutton2' style="width:276px;" type='button' id='login-button' onClick="loginUser();" value="Log In" />
					</td>
				  </tr>
				  <tr>
					<td class="text_1">
						<img src="http://cdn.pieceoftheworld.com/images/facebook_signin_icon.png" id="fbloginbutton" width="150" height="22" border="0" style="cursor:pointer;" onClick="loginFb()" />
					</td>
				  </tr>
				</table>
				</form>
			</div>
		</div>
	</div>
	<?php
	/*
	?>
	<div id="popup_bottom"></div>
	<?php
	*/
	?>
	<div id="popup_shadow"><img src="http://cdn.pieceoftheworld.com/images/interscape_shadow.png" border="0" /></div>
</div>
<!--END OF POPUP-->
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-39101024-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
  jQuery(".cboxiframe").colorbox({iframe:true, width:"80%", height:"80%"});
</script>
<script type="text/javascript">
    var loggedIn = '<?php echo isset($_SESSION['userdata'])  ?>';
	// start of facebook functions
	var fbResponse = false;
	jQuery("#fbloginbutton").hide();
	window.fbAsyncInit = function() {
		jQuery("#fbloginbutton").show();
		FB.init({
			appId      : '454736247931357', // App ID
			channelUrl : '//pieceoftheworld.com/channel.html',
			status     : true, // check login status
			cookie     : true, // enable cookies to allow the server to access the session
			xfbml      : true  // parse XFBML
		});

		FB.Event.subscribe('auth.login', function(response) {
			if (response.status === 'connected') {
				fbResponse = response;
				//loginFb();
			} else { /* FB.login(); */ }
		});
		FB.Event.subscribe('edge.create', function(href, widget) {
			jQuery.ajax({
				 dataType: "json",
				 type: 'get',
				 data: {'href':  encodeURI(href) }, // from index.php line 2121
				 url: 'ajax/user_fxn.php?action=like',
				 success: function(data){
					console.log(data);
				 }
			});
		});
		FB.Event.subscribe('edge.remove', function(href, widget) {
			jQuery.ajax({
				dataType: "json",
				type: 'get',
				data: {'href':  encodeURI(href) }, // from index.php line 2121
				url: 'ajax/user_fxn.php?action=unlike',
				success: function(data){
					console.log(data);
				}
			});
		});
		
		<?php
		if($_SESSION['signupfb']){
			?>
			//loginFb();
			updateProfile();
			openClosePopUp('facebook');
			jQuery("#fbloginbutton").trigger("click");
			<?php
			//unset($_SESSION['signupfb']);
		}
		?>

	};

	// Load the SDK asynchronously
	(function(d, s, id){
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) {return;}
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/en_US/all.js";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
	
	if(loggedIn){
		jQuery("#userProfile").show();
		jQuery("#loginForm").hide();
		openClosePopUp('facebook');
		getLands();
	} else {
		jQuery("#userProfile").hide();
		jQuery("#loginForm").show();
		openClosePopUp('cta');
	}
	
	/****************************************************************************************************/
	function loginFb() {
		FB.login(function(response) {
				if (response.authResponse) {
					FB.api('/me', function(response) {
						// update fields in login, reg and facebook form in mainTabs.php
						jQuery("#loginForm input[name='fb_id']").val(response.id);
						jQuery("#loginForm input[name='email']").val(response.email);
						jQuery("#loginForm input[name='name']").val(response.name);
						jQuery("#loginForm input[name='gender']").val(response.gender);
						if(typeof(response.location) != 'undefined'){
							jQuery("#loginForm input[name='location']").val(response.location.name);
						}
						console.log(response);
						loginUser(true);
					});
				}
				else {	
				}
			},{scope: 'email'}
		);
		
	}
	function loginFbBuy() {
		FB.login(function(response) {
				if (response.authResponse) {
					FB.api('/me', function(response) {
						// update fields in login, reg and facebook form in mainTabs.php
						jQuery("#loginForm input[name='fb_id']").val(response.id);
						jQuery("#loginForm input[name='email']").val(response.email);
						jQuery("#loginForm input[name='name']").val(response.name);
						jQuery("#loginForm input[name='gender']").val(response.gender);
						if(typeof(response.location) != 'undefined'){
							jQuery("#loginForm input[name='location']").val(response.location.name);
						}
						console.log(response);
						loginUserBuy(true);
					});
				}
				else {	
				}
			},{scope: 'email'}
		);
		
	}
	function loginUserBuy(fb){
		if(!isset(fb)){
			jQuery("#loginForm input[name='fb_id']").val("");
			jQuery("#loginForm input[name='name']").val("");
			jQuery("#loginForm input[name='gender']").val("");
		}
		jQuery("#login-button").val("Logging in...");
		datax = jQuery('#loginForm').serialize();
		jQuery('#loginForm *').attr("disabled", true);
		jQuery.ajax({
			dataType: "json",
			type: 'post',
			async: false,
			url: "ajax/user_fxn.php?action=login",
			data: datax,
			success: function(data){
				if(data.status){					
					setProfile(data);
					jQuery("#userProfile").show();
					jQuery("#loginForm").hide();
					getLands();
					onBuyLand();
				} else {
					alert(data.message);
				}
				jQuery('#loginForm *').attr("disabled", false);
				jQuery("#login-button").val("Log In");
			}
		});
	}
	// end of facebook functions
	function loginUser(fb){
		if(!isset(fb)){
			jQuery("#loginForm input[name='fb_id']").val("");
			jQuery("#loginForm input[name='name']").val("");
			jQuery("#loginForm input[name='gender']").val("");
		}
		jQuery("#login-button").val("Logging in...");
		datax = jQuery('#loginForm').serialize();
		jQuery('#loginForm *').attr("disabled", true);
		jQuery.ajax({
			dataType: "json",
			type: 'post',
			async: false,
			url: "ajax/user_fxn.php?action=login",
			data: datax,
			success: function(data){
				if(data.status){
					setProfile(data);
					jQuery("#userProfile").show();
					jQuery("#loginForm").hide();
					getLands();
					
				} else {
					alert(data.message);
				}
				jQuery('#loginForm *').attr("disabled", false);
				jQuery("#login-button").val("Log In");
			}
		});
	}
	
	function updateProfile(){
		jQuery.ajax({
			dataType: "html",
			type: 'post',
			async: false,
			url: "ajax/user_fxn.php?action=checklogin",
			success: function(data){
				data = JSON.parse(data);
				consoleX(data);
				//alert(data['id']);
				try{
					if(isset(data.content.id)){
						jQuery("#userProfile").show();
						jQuery("#loginForm").hide();
						setProfile(data);
					}
					else{
						jQuery("#userProfile").hide();
						jQuery("#loginForm").show();
						if(isset(FB)){
							consoleX("show fbloginbutton");
							jQuery("#fbloginbutton").show();
						}
					}
				}
				catch(e){
					jQuery("#userProfile").hide();
					jQuery("#loginForm").show();
					consoleX(FB);
					if(isset(FB)){
						consoleX("show fbloginbutton");
						jQuery("#fbloginbutton").show();
					}
				}
			},
			error: function(){ alert(error);}
		});
	}
	function setProfile(data){
		if(isset(data.content.fb_id)){
			jQuery("#profile_image").html('<img src="http://graph.facebook.com/'+data.content.fb_id+'/picture" style="height:30px; width:30px;">');
			jQuery("#profile_image").show();
		}
		else{
			jQuery("#profile_image").hide();
		}
		
		if(isset(data.content.name)){
			jQuery("#profile_name").html(data.content.name);
		}
		else{
			jQuery("#profile_name").html(data.content.useremail);
		}
		jQuery("#sign_in").html('<div class="mmenus" style="left:195px; width:50px;"><a style="cursor:pointer" class="link_1" onClick="logoutUser();">Sign Out</a></div><div class="mmenus" style="left:261px; width:40px;"><a class="link_1" style="cursor:pointer;" onClick="updateProfile(); openClosePopUp(\'facebook\'); ">Profile</a></div>');
	}

	/*
	jQuery('.manageImageLink').live('click', function(e){
		jQuery( "#userPanelExtra" ).html("<img src='images/loading.gif'>");
		jQuery( "#userPanelExtra" ).dialog( "open" );
		e.preventDefault();
		$id = jQuery(this).attr('data-id');
		jQuery.ajax({
			dataType: "html",
			type: 'get',
			data: jQuery('#form_'+$id).serialize(),
			url: 'ajax/page_webuserPictures.php',
			success: function(data){
				jQuery( "#userPanelExtra" ).dialog( {title: "Manage Images"} );
				jQuery('#userPanelExtra').html(data);
				resizeHeight();
			}
		});
	});
	jQuery('.manageTags').live('click', function(e){
		jQuery( "#userPanelExtra" ).html("<img src='images/loading.gif'>");
		jQuery( "#userPanelExtra" ).dialog( "open" );
		e.preventDefault();
		$id = jQuery(this).attr('data-id');
		jQuery.ajax({
			dataType: "html",
			type: 'get',
			data: jQuery('#form_'+$id).serialize(),
			url: 'ajax/page_webuserTags.php',
			success: function(data){
				jQuery( "#userPanelExtra" ).dialog( {title: "Manage Category and Tags"} );
				jQuery('#userPanelExtra').html(data);
				resizeHeight();

			}
		});
	});
	jQuery('.manageVideoLink').live('click', function(e){
		jQuery( "#userPanelExtra" ).html("<img src='images/loading.gif'>");
		jQuery( "#userPanelExtra" ).dialog( "open" );
		e.preventDefault();
		$id = jQuery(this).attr('data-id');
		jQuery.ajax({
			dataType: "html",
			type: 'get',
			data: jQuery('#form_'+$id).serialize(),
			url: 'ajax/page_webuserVideos.php',
			success: function(data){
				jQuery( "#userPanelExtra" ).dialog( {title: "Manage Videos"} );
				jQuery('#userPanelExtra').html(data);
			}
		});
	});
	*/
	function getLands(idx){
		if(!isset(idx)){
			idx = "";
		}
		jQuery('#ownedLandList').html('');
		jQuery.ajax({
			dataType: "html",
			type: 'post',
			async: true,
			url: 'ajax/page_webuserLands.php?id='+idx+'&t='+(new Date()).getTime(),
			success: function(data){
				jQuery('#ownedLandList').html(data);
				/*
				jQuery(".editableText").editInPlace({
					url: "ajax/user_fxn.php?action=edit",
					saving_animation_color: "#ECF2F8"
				});
				jQuery(".editableTextarea").editInPlace({
					url: "ajax/user_fxn.php?action=edit",
					saving_animation_color: "#ECF2F8",
					field_type: "textarea"
				});
				*/
			},
			error: function(){ alert(error);}
		});
	}
	function nl2br(str) {
		return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ '<br />' +'$2');
	}
	function isValidEmail(emailAddress) {
		var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
		return pattern.test(emailAddress);
	}
	
	function registerUser(){
		if(!isValidEmail(jQuery("#registerForm input[name='email']").val())){
			jQuery("#regStatus").html("Invalid Email Address");
			jQuery("#regStatus").show('slide');
		} else {
			jQuery("#regStatus").hide('slide');
			jQuery.ajax({
				dataType: "json",
				type: 'post',
				async: false,
				url: "ajax/user_fxn.php?action=register",
				data: jQuery('#registerForm').serialize(),
				success: function(data){
					if(data.status){
						jQuery('#loadingImageFb1').hide();
						jQuery('#loadingImageFb2').hide();
						jQuery('#tabs [href="#ownedLands"]').show();
						jQuery('#tabs [href="#login"]').hide();
						jQuery( "#tabs" ).tabs( {active: 5});
						jQuery('.currentUser').html(data.content.useremail);
						//getLands();
						jQuery('#loginHolder').slideToggle();
						jQuery('#regHolder').slideToggle();
					} else {
						jQuery("#regStatus").html(data.message);
						jQuery("#regStatus").show('slide');
					}
				}
			});
		}
	}
	function logoutUser(){
		jQuery.ajax({
			dataType: "json",
			type: 'get',
			async: false,
			url: "ajax/user_fxn.php?action=logout",
			success: function(data){
				//alert("about");
				openClosePopUp("cta");
				if(data.status){
					// if logged in via FB, then also logout the session
					if(fbResponse){
						FB.logout(function(response){
						});
					}
					else {
					}
					jQuery("#userProfile").hide();
					jQuery("#loginForm").show();
					//jQuery("#sign_in").html("<a class=\"text_1\" style='cursor:pointer;' onClick=\"updateProfile(); openClosePopUp('facebook'); \">Sign In</a>");
					jQuery("#sign_in").html('<div class="mmenus" style="left:195px; width:40px;" id="sign_in"><a onclick="updateProfile(); openClosePopUp(\'facebook\');" style="cursor:pointer;" class="link_1">Sign in</a></div>');
					
				}
			}
		});
	}
	
</script>
<?php
if($_SESSION['signupfb']){
	unset($_SESSION['signupfb']);
}
?>
<link rel="stylesheet" type="text/css" href="http://cdn.pieceoftheworld.com/js/tagsinput/jquery.tagsinput.css" />
<script type="text/javascript" src="http://cdn.pieceoftheworld.com/js/twitmarquee/twitmarquee.js"></script>
<script type="text/javascript" src="http://cdn.pieceoftheworld.com/js/tagsinput/jquery.tagsinput.js"></script>
<!--IMAGE SLIDE SHOW-->
<script type="text/javascript" src="http://cdn.pieceoftheworld.com/js/slideshow/slideshow.js" ></script>
<!--END OF IMAGE SLIDE SHOW-->
<script>startSlideShow(10000);</script>
<script>
jQuery(function(){

});
</script>
</body>
</html>