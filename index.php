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



include_once(dirname(__FILE__).'/ajax/global.php');

if($_GET['trends']){
	?>
	<div class="inner">
		<div style="position:relative; width:100%; height:auto;">
		<ul class="trendscontent">
			
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
			
		</ul>
		</div>
	</div>
	
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
<link rel="stylesheet" type="text/css" href="http://pieceoftheworld.com/css/styles.css" />
<script src="http://pieceoftheworld.com/js/jquery-1.8.3.min.js" type="text/javascript"></script>
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
.ui-autocomplete{
	z-index: 99999;
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
<script src="http://pieceoftheworld.com/js/draggable/jquery-1.9.1.js"></script>
<script src="http://pieceoftheworld.com/js/draggable/jquery-ui.js"></script>
<link href="http://pieceoftheworld.com/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script>
	var MyHistory = new Array();

	function GoBackHistory(title){
		var last = "", 
			i = "", 
			s = [], 
			s1 = [], 
			title_i = [], 
			title_m = "", 
			j = "", 
			a=[], 
			b=[], 
			c=[], 
			d=[], 
			l=[], 
			e=[], 
			n = "";
		
		if(MyHistory.length>0){
			i = MyHistory.length - 1;
			s = MyHistory[i].split(',');
			s1 = s[0].split("'");
			
			title_i = title.split('_');
			title_m = title_i.join(" ");
			if((s1[1].toLowerCase() === title.toLowerCase() || title_m.toLowerCase()) && MyHistory.length < 2){
				//MyHistory.pop();
				window.location = "/";
			}
			if((s1[1].toLowerCase() === title.toLowerCase() || title_m.toLowerCase()) && MyHistory.length > 1){
				MyHistory.pop();
				
			}

			j = MyHistory.length - 1;
			last = MyHistory[j];
			
			
				a = last.split(',');
				b = a[0].split("'");
				c = b[0].split("(");
				d = last.split(" ");
				l = d.pop();
				e = l.split("'");
				n = c[0]; 

				console.log(s1[1].toLowerCase()+"=="+title.toLowerCase()+" || "+title_m.toLowerCase());
				console.log('last: '+last);
				console.log(MyHistory);
				switch(n){
					case 'step1':
						step1(b[1], e[1]);
						
					break;

					case 'step2':
						step2(b[1], e[1]);
						
					break;

					case 'step3':
						step3(b[1], e[1]);
						
					break;

					case 'step4':
						step5(b[1], e[1]);
						
					break;

					case 'step5':
						step5(b[1], e[1]);
						
					break;

					case 'userProfile':
						userProfile(b[1], e[1]);
					break;

					case 'openClosePopUp':
						openClosePopUp(b[1]);
						//document.getElementById('wraper_result').style.display = 'none';
					break;
					
				}
			
			
			
		} else {
			window.location = "/";
		}
	}

	
	jQuery(function(){
		
			jQuery('#nav_menu').on('click', 'p', function(){
				var toHistory = jQuery(this).parent().attr('onclick');
				if(toHistory){
					MyHistory.push(toHistory);
					console.log(MyHistory);
				}
				
				
				
			});
			jQuery(".goback").on('click', function(){
				var t = jQuery(this).parent().parent().find(".title_result").text();
				var ar = [];
				ar = t.split(" ");
				t = ar.join("_");
				ar = null;
				
				GoBackHistory(t);
				return false;
			});
		
	});

</script>
<!--END OF MAKE BOX DRAGGABLE-->

<!-- colorbox -->

<script src="http://pieceoftheworld.com/js/colorbox-master/jquery.colorbox.js"></script>
<link rel="stylesheet" type="text/css" href="http://pieceoftheworld.com/js/colorbox-master/example1/colorbox.css" media="screen" />

<!-------------->


<!--INITIALIZE MAP-->
<script src="http://maps.google.com/maps/api/js?sensor=true&libraries=geometry" type="text/javascript"></script>


<script>
<?php
include_once(dirname(__FILE__)."/js/main.php");
?>
</script>
<!--END OF INITIALIZE MAP-->


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
	width: 272px;
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

/*#top_list_categories {
    background-color: #5F96E6;
}*/
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
<style>
	html, body{
	max-height: 100%; 
	height: 100%; 
	overflow: hidden;
}
</style>
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:900' rel='stylesheet' type='text/css'>

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
		
		<div style="padding-top:10px; float:right; right:100px" class="fb-like" data-href="http://pieceoftheworld.com/" data-layout="button" data-action="like" data-show-faces="false" data-share="true"></div>
		<style>
		
		
		.mmenus{
			position:relative; 
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
		#nav_menu{
			position: absolute;
			right: 20px;
			z-index: 99999;
		}
		#nav_menu ul {
			display: block;
			margin: 0;
			padding: 0;
		}
		#nav_menu ul li{
			list-style: none;
			display: inline-block;
		}
		#nav_menu ul li p{
			margin: 0;
		}
		
		</style>
		<div id="nav_menu">
			<ul>
				<li><div class='mmenus' id='places_of_interest' onClick="openClosePopUp('places_of_interest');"><p>Places of interest</p></div></li>
				<li><div class='mmenus' id='top_list' onClick="openClosePopUp('top_lists');"><p>Top Lists</p></div></li>
				<li><div style='padding:0px; top:78px; position:relative; width: 274px;' id="search_field">
						<input type="text" class="input_1" style="border: 1px solid #3777A8; padding:1px 3px 2px 3px; border-bottom-left-radius: 0px; border-top-left-radius: 0px; width:264px; height:20px;" id="search_enteraplace" name="search" value="Search for an address or place to buy" onFocus="if(this.value=='Search for an address or place to buy'){ this.value=''; }" onBlur="if(this.value==''){ this.value='Search for an address or place to buy'; }" />
					</div>
					<!--POPUP-->
					<div id="popup" style="display:none; width: 272px; right: auto; top: 112px;">
						
						<div id="popup_content" style='padding:0; border:0px solid #7F97E8; width:272px'>
							<div id="popup_header">
								
							</div>
							<div id="popup_main_content" style="width:272px;">



								<div id="content_places_of_interest" style="display:none;">
									<p class="interscape" style="display: block; overflow: hidden;">
										<img src="/images/mapbg/interscape.png">
										<span style="display: block; width: 122px; float: left;">InterScape</span>
										<a href="/" class="goback" style="display: block; float: left;">
											<img src="/images/mapbg/arrow_left.png" alt="" style="width: 10px; float: left;">
											<span style="color: #008aba;font-family: 'Open Sans', sans-serif;font-weight: 300;">Back</span>
										</a>
									</p>
									<p class="title_popupblock" style="border-bottom: 1px solid #3777a8;padding: 10px 0;margin-bottom: 0;">
										<span class="title_result">Places Of Interest</span>
									</p>	
								</div>

								<div id="content_partner" style="display:none;">
									<p class="interscape" style="display: block; overflow: hidden;">
										<img src="/images/mapbg/interscape.png">
										<span style="display: block; width: 122px; float: left;">InterScape</span>
										<a href="/" class="goback" style="display: block; float: left;">
											<img src="/images/mapbg/arrow_left.png" alt="" style="width: 10px; float: left;">
											<span style="color: #008aba;font-family: 'Open Sans', sans-serif;font-weight: 300;">Back</span>
										</a>
									</p>
									<p class="title_popupblock" style="border-bottom: 1px solid #3777a8;padding: 10px 0;margin-bottom: 0;">
										<span class="title_result">Partner</span>
									</p>	
								</div>

								<div id="content_how_it_works" style="display:none;">
									<p class="interscape"><img src="/images/mapbg/interscape.png"><span>InterScape</span></p>
									<p class="title_popupblock">
										<span class="title_result">How it works</span>
										<a class="goback" href="/" >
											<img src="/images/mapbg/arrow_left.png" alt="">
											<span>Back</span>
										</a>
									</p>
									<p class="bnr_howitworks"><img src="/images/mapbg/eath.png" alt=""></p>
									<p class="list_howitworks">
										<span>1.</span>
										<span>This is how our virtual world looks. You can browse 
										      the map to see what others own, look at their images 
										      and “like” their places.</span>
									</p>
						            <p class="list_howitworks">
						            	<span>2.</span>
						            	<span>You can zoom in and out by using the + and - buttons 
						            	      on the left hand side of the map. You can also use the mouse 
						            	      scroll wheel.</span>
						            </p>
						            <p class="list_howitworks">
						            	<span>3.</span>
						            	<span>When you zoom in, a grid will appear. Red squares 
					    		            are owned by someone else. Left click on any 
					    		            transparent or green square you want. You can select 
					    		            multiple squares.</span>
						            </p>
						            <p class="list_howitworks">
						            	<span>4.</span>
						            	<span>When you have made your selection, click on the 
					    		            button “Click to buy”. You can now upload photos 
					    		            and write a text. You now own a piece of the world. Within minutes you 
					    		            will receive a  certificae of ownership to your inbox. </span>
					    		    </p>
								</div>
							
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
		
								<div id="topListWraper">
									<div id="content_top_lists" style="display:none;">
										<style>
											#content_top_lists ul li div p:before,
											#top_list_items p:before{
												background: #3777A8; /* Для старых браузров */
												background: -moz-linear-gradient(left, #3777A8, #ffffff);
												background: -webkit-linear-gradient(left, #3777A8, #ffffff);
												background: -o-linear-gradient(left, #3777A8, #ffffff);
												background: -ms-linear-gradient(left, #3777A8, #ffffff);
												background: linear-gradient(left, #3777A8, #ffffff);
												content: "";
												position: absolute;
												top: 0px;
												left: 0px;
												right: 0px;
												bottom: -1px;
												z-index: -2;
											}
											#content_top_lists ul li div p:after,
											#top_list_items p:after{
												background: white;
												content: "";
												position: absolute;
												top: 0;
												left: 0;
												right: 2px;
												bottom: 0;
												z-index: -1;
											}
											#content_top_lists ul{
												display: block;
												margin: 0;
												padding: 0;
											}
											#content_top_lists ul li{
												display: block;
												list-style: none;
												position: relative;
											}
											#content_top_lists ul li div p,
											#top_list_items p{
												z-index: 0;
												position: relative;
												padding: 7px 0 7px 11px;
												margin: 0 0 1px !important;
												color: #008aba;
											}
											#top_list_categories {
												float: none !important;
												width: 100% !important;
												height: auto !important;
												padding: 0 !important;
												background-color: transparent !important;
												cursor: pointer !important;
												border-bottom: 0 !important;
											}
											#top_list_img_1 {
												float: none !important;
												width: 272px !important;
												height: 272px;
											}
											#top_list_items {
												float: none !important;
												width: 100% !important;
												height: auto !important;
												padding: 0 !important;
												background-color: transparent !important;
												cursor: pointer !important;
												border-bottom: 0 !important;
												word-wrap: break-word !important;
												}
											.interscape a:hover{
												text-decoration: none;
											}
										</style>
										<p class="interscape"><img src="/images/mapbg/interscape.png"><span>InterScape</span></p>
	
										
										<p class="title_popupblock">
											<span class="title_result">Top Lists</span>
											<a href="/" class="goback" >
												<img src="/images/mapbg/arrow_left.png" alt="">
												<span>Back</span>
											</a>
										</p>
																				
										<ul>
										
										<?php
											$categories = array(
												0=>"Biggest Land Owners", 
												1=>"Most Expensive Lands", 
												2=>"Most Viewed Lands", 
												3=>"Biggest Water Owners", 
												4=>"Most Liked Lands"
												
												);
											
											$t = count($categories);
											
											for($i=0; $i<$t; $i++){

												echo '<li><div id="top_list_categories" class="text_3 cat'.$i.'" onclick="step1(\''.$categories[$i].'\', \''.$i.'\');"><p>'.$categories[$i].'</p></div></li>';
											}
											?>
										</ul>
										<div id="top_list_img_1">
											<div id="slideshow1" style="display:none;"><img src="http://pieceoftheworld.com/images/others_0/1.png" width="272" height="" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
											<div id="slideshow2" style="display:none;"><img src="http://pieceoftheworld.com/images/others_0/2.png" width="272" height="" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
											<div id="slideshow3" style="display:none;"><img src="http://pieceoftheworld.com/images/others_0/3.png" width="272" height="" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
											<div id="slideshow4" style="display:none;"><img src="http://pieceoftheworld.com/images/others_0/4.png" width="272" height="" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
											<div id="slideshow5" style="display:none;"><img src="http://pieceoftheworld.com/images/others_0/5.png" width="272" height="" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
											<div id="slideshow6" style="display:none;"><img src="http://pieceoftheworld.com/images/others_0/6.png" width="272" height="" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
											<div id="slideshow7" style="display:none;"><img src="http://pieceoftheworld.com/images/others_0/7.png" width="272" height="" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
											<div id="slideshow8" style="display:none;"><img src="http://pieceoftheworld.com/images/others_0/8.png" width="272" height="" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
											<div id="slideshow9" style="display:none;"><img src="http://pieceoftheworld.com/images/others_0/9.png" width="272" height="" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
											<div id="slideshow10" style="display:none;"><img src="http://pieceoftheworld.com/images/others_0/10.png" width="272" height="" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
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
	
										
									</div>	

									<div class="searching" style="display:none; color:#FFFFFF;">Searching...</div>
									
									<div id="wraper_result" style="display:none;">
										<p class="interscape" style="display: block; overflow: hidden;">
											<img src="/images/mapbg/interscape.png">
											<span style="display: block; width: 122px; float: left;">InterScape</span>
											<a href="/" class="goback" style="display: block; float: left;">
												<img src="/images/mapbg/arrow_left.png" alt="" style="width: 10px; float: left;">
												<span style="color: #008aba;font-family: 'Open Sans', sans-serif;font-weight: 300;">Back</span>
											</a>
										</p>
										<p class="title_popupblock" style="border-bottom: 1px solid #3777a8;padding: 10px 0;margin-bottom: 0;"><span class="title_result">Top Lists</span></p>
																
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
									</div>
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
								
								<table width='100%'>
								<tr>
									<td style='color:#000000; font-family:Arial; font-size:14px; font-weight:bold'>
								
									<div><img src='images/intro_interscape.jpg' style="width:269px;"></div>
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
									<form id="loginForm" style='display:none; margin-top:7px;'>
									<table width="100%" border="0" cellspacing="0" cellpadding="3">
									  <tr>
										<td align='center'>
											<input type="hidden" name="fb_id" class="fb_id" />
											<input type="hidden" name="name" class="name" />
											<input type="hidden" name="gender" class="gender" />
											<input type="hidden" name="location" class="location" />
											<input type="text" name="email" style="width:250px; height:30px; border:1px solid gray" class="input_3" placeholder="E-mail" />
										</td>
									  </tr>
									  <tr>
										<td align='center'>
											<input type="password" name="password" style="width:250px; height:30px; border:1px solid gray" class="input_3" placeholder="Password" />
										</td>
									  </tr>
									  <tr>
										<td align='center'>
											<input class='longbutton2' style="width:254px;" type='button' id='login-button' onClick="loginUser();" value="Log In" />
										</td>
									  </tr>
									  <tr>
										<td class="text_1">
											<img src="http://pieceoftheworld.com/images/facebook_signin_icon.png" id="fbloginbutton" width="150" height="22" border="0" style="cursor:pointer; margin-left: 7px; margin-bottom:5px;" onClick="loginFb()" />
										</td>
									  </tr>
									</table>
									</form>
								</div>
							</div>
						</div>
						
						<div id="popup_shadow" style="width: 272px;"><img src="http://pieceoftheworld.com/images/interscape_shadow.png" border="0" /></div>
					</div>
					<!--END OF POPUP-->
				</li>
				<li><div class='mmenus' onClick="self.location='?start=1'" ><p>Home</p></div></li>
				<li><div class='mmenus' id='partner' onClick="openClosePopUp('partner');"><p>Partner</p></div></li>
				<li><div class='mmenus' id='how_it_works' onClick="openClosePopUp('how_it_works');"><p>How it works</p></div></li>
				
			<?php
			if($_SESSION['userdata']){
				?>
				<li>
					<div id='sign_in'>
						<div class='mmenus'>
							<a style='cursor:pointer' class="link_1" onClick="logoutUser();">Sign Out</a>
						</div>
						<div class='mmenus'>
							<a class="link_1" style='cursor:pointer;' onClick="updateProfile(); openClosePopUp('facebook'); ">Profile</a>
						</div>
					</div>
				</li>
				<?php
			}
			else{
				?>
				<li>
					<div id='sign_in'>
						<div class='mmenus' id='sign_in'>
							<a onclick="updateProfile(); openClosePopUp('facebook');" style="cursor:pointer;" class="link_1">Sign in</a>
						</div>
					</div>
				</li>
				<?php
			}
			?>
			</ul>
		</div>
	</div>
</div>
<!--<div id="header_arc"></div>-->
<div id="map_canvas"></div>
<div id="mapbg">
	<div id="mapbg_inner">
		<div id="block_inf">
			<p id="p1">Get your own</p>
			<p id="p2">favorite piece</p>
			<p id="p3">of the world!</p>
			<p id="p4">Zoom in & select from grid</p>
		</div>
	</div>
</div>
<div id="footer">
	<script type="text/javascript">
	jQuery(function(){
		jQuery('#mapbg, #footer .trendscontent .search_link').on('click', function(){
			jQuery('#mapbg').animate({opacity: "hide"},1000);
			jQuery("#popup").animate({opacity: "toggle"},1000);
			jQuery('#map_canvas').css('z-index',1);
			
		});

		var srch = window.location.search;
		
		if(srch.length>0){
			jQuery('#mapbg').hide();
			jQuery("#popup").show();
			jQuery('#map_canvas').css('z-index',1);
		}

		jQuery("#search_enteraplace").focusin(function(){
  			jQuery(this).keypress(function(){
  				if(event.keyCode === 13){
  					if(jQuery('#mapbg').css("display")==='block') {
  						jQuery('#mapbg').animate({opacity: "hide"},750);
						jQuery("#popup").animate({opacity: "toggle"},750);
						jQuery('#map_canvas').css('z-index',1);
  					};

  				}
  			});
		});

		jQuery('#top_list').on('click', function(){
			if(jQuery('#mapbg').css("display")==='block') {
  						jQuery('#mapbg').animate({opacity: "hide"},1000);
						jQuery("#popup").animate({opacity: "toggle"},1000);
						jQuery('#map_canvas').css('z-index',1);
  					};
		});
		jQuery('#sign_in').on('click', function(){
			if(jQuery('#mapbg').css("display")==='block') {
  						jQuery('#mapbg').animate({opacity: "hide"},1000);
						jQuery("#popup").animate({opacity: "toggle"},1000);
						jQuery('#map_canvas').css('z-index',1);
  					};
		});
		jQuery('#how_it_works').on('click', function(){
			if(jQuery('#mapbg').css("display")==='block') {
  						jQuery('#mapbg').animate({opacity: "hide"},1000);
						jQuery("#popup").animate({opacity: "toggle"},1000);
						jQuery('#map_canvas').css('z-index',1);
  					};
		});
		jQuery('#partner').on('click', function(){
			if(jQuery('#mapbg').css("display")==='block') {
  						jQuery('#mapbg').animate({opacity: "hide"},1000);
						jQuery("#popup").animate({opacity: "toggle"},1000);
						jQuery('#map_canvas').css('z-index',1);
  					};
		});
		jQuery('#places_of_interest').on('click', function(){
			if(jQuery('#mapbg').css("display")==='block') {
  						jQuery('#mapbg').animate({opacity: "hide"},1000);
						jQuery("#popup").animate({opacity: "toggle"},1000);
						jQuery('#map_canvas').css('z-index',1);
  					};
		});

		function getRandomInt(min, max)
		{
		  return Math.floor(Math.random() * (max - min + 1)) + min;
		}
		var mapbg = getRandomInt(1, 4);
		block_inf = [];
		block_inf[1]='#75ff36';
		block_inf[2]='#43c9ff';
		block_inf[3]='#db654b';
		block_inf[4]='#ba6e9f';

		jQuery("#mapbg").css("background-image","url(./images/mapbg/"+mapbg+".jpg)") ;
		jQuery("#block_inf").css("background",block_inf[mapbg]);
		
	});
	</script>
	<style>
	#block_inf p{
		font-family: 'Open Sans', sans-serif;
		color:#ffffff;
		margin-top: -30px;
		font-weight: 300;
	}
	#p1, #p2, #p3{
		font-size: 54px;
		padding: 0;
		margin: 0;
	}
	#p2, #p3{
		margin-top: -5px;
	}
	#p1{
		margin-left: 30px;
	}
	#p2{
		margin-left: 100px;
	}
	#p3{
		margin-left: 40px;
	}
	#p4{
		text-align: center;
		font-size: 1.3em;
		margin-top: 0px !important;
	}
	#block_inf{
		width: 500px;
		height: 200px;
		/*background: #75ff36;*/
		position: relative;
		top: 50%;
		margin-top: -100px;
		cursor: pointer;
	}
	#mapbg_inner{
		position: absolute;
		width: 100%;
		height: 100%;
	}
	#mapbg{
		display: block;
		position: absolute;
		height: auto;
		top: 50px;
		bottom: 30px;
		left: 0;
		right: 0;
		margin: 0;
		z-index: 1;
		background-size: cover;
		background-repeat: no-repeat;
		/*background-image: url('./images/mapbg/1.jpg');*/
	}

	#footer{
		background-color: #ffffff;
		height: 30px;
	}
	#trends li {
    background-color: #ffffff;
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
	#popup_main_content p{
		color: #008aba;
		font-family: 'Open Sans', sans-serif;
		font-weight: 300;
	}
	/*#content_how_it_works p a{
		color: #008aba;
		text-decoration: none;
		font-family: 'Open Sans', sans-serif;
		font-weight: 300;
		display: block;
		float: right;
		padding-right: 20px;
	}*/
	#content_how_it_works p a:hover{
		color: #008aba;
	}
	#content_how_it_works .list_howitworks span{
		font-family: 'Open Sans', sans-serif;
		font-weight: 300;
		display: block;
		color: #008aba;
		padding: 0 15px;
		font-size: 12px;
	}
	.title_popupblock{
		color: #008aba !important;
		font-weight: 400;
		display: block;
		width: 100%;
		height: 22px;
		margin: 0;
		padding: 10px 0;
	}
	#content_top_lists .title_popupblock{
		border-bottom: 1px solid #3777a8;
		padding: 10px 0 !important;
	}
	.interscape{
		font-family: 'Source Sans Pro', sans-serif !important;
		font-weight: 900 !important;
		color: #3777A8 !important;
		font-size: 22px;
		line-height: 28px;
		margin: 0;
		padding: 10px;
		border-bottom: 1px solid #3777a8;
	}
	.interscape img{
		float: left;
		position: relative;
		margin: 0 6px 0 10px;
		width: 22px;
		top: 4px;
	}
	#content_how_it_works .bnr_howitworks{
		padding: 0;
		margin: 0;
	}
	.title_popupblock span{
		display: block;
		float: left;
		padding: 0 0 0 20px;
		font-size: 22px;
		line-height: 22px;
	}
	.title_popupblock a{
		color: #008aba;
		text-decoration: none;
		font-family: 'Open Sans', sans-serif;
		font-weight: 300;
		display: block;
		float: right;
		padding-right: 20px;
	}
	.title_popupblock a img{
		float: left;
		width: 10px;
	}
	.title_popupblock a span{
		font-size: 14px;
		font-weight: 300;
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
	
</div>




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
<link rel="stylesheet" type="text/css" href="http://pieceoftheworld.com/js/tagsinput/jquery.tagsinput.css" />
<script type="text/javascript" src="http://pieceoftheworld.com/js/twitmarquee/twitmarquee.js"></script>
<script type="text/javascript" src="http://pieceoftheworld.com/js/tagsinput/jquery.tagsinput.js"></script>
<!--IMAGE SLIDE SHOW-->
<script type="text/javascript" src="http://pieceoftheworld.com/js/slideshow/slideshow.js" ></script>
<!--END OF IMAGE SLIDE SHOW-->
<script>startSlideShow(10000);</script>

</body>
</html>