<?php 
if(strtolower($_SERVER['HTTP_HOST'])=="pieceoftheworld.com"){
	$url = "http://pieceoftheworld.co/".ltrim($_SERVER['REQUEST_URI'], "/");
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: '.$url);
}else if(strpos(strtolower($_SERVER['HTTP_HOST']), "www.")===0){
	$url = "http://pieceoftheworld.co/".ltrim($_SERVER['REQUEST_URI'], "/");
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: '.$url);
}

session_start(); 
include_once(dirname(__FILE__).'/ajax/global.php');

if($_GET['trends']){
	?>
	<div class="inner">
		<div style="position:relative; width:100%; height:auto; left:180px;">
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
					//$sql = "select * from ``"
				}
				else{
					$str = trim($c[$i]['land_owner'])." just bought '".trim($c[$i]['title'])."'";
					?>
					<li>
						<a href="?xy=<?php echo $c[$i]['x']."~".$c[$i]['y']; ?>" class="search_link" name="<?php echo htmlentities($str) ?>" rel="nofollow"><?php echo strip_tags($str); ?></a>
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
	<span class="fade fade-left">&nbsp;</span><span class="fade fade-right">&nbsp;</span>
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

if($_GET['affid']){
	$_SESSION['affid'] = $_GET['affid'];
	$sql = "select * from `affiliates` where `id`='".$_SESSION['affid']."'  and `active`=1";
	$r = dbQuery($sql, $_dblink);
	$r = $r[0];
	if($r['id']){
		$sql = "insert into `affiliate_clicks` set 
			`affiliate_id`='".$r['id']."',
			`server_json`='".mysql_real_escape_string(json_encode($_SERVER))."',
			`dateadded`=NOW()
		";
		dbQuery($sql, $_dblink);
	}
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: index.php');
	exit();
}

if($_GET['px']!=""){
	$_SESSION['px'] = $_GET['px'];
	exit();
}

?>
<!doctype html>
<html lang="us">
<head>
<meta property='og:image' content='http://pieceoftheworld.co/images/pastedgraphic_fb.jpg' />
<meta property='og:title' content='Piece of the World' />
<meta charset="utf-8">
<title>PieceoftheWorld</title>
<link rel="stylesheet" type="text/css" href="css/styles.css?_<?php echo time(); ?>" />
<script src="http://cdn.pieceoftheworld.co/js/jquery-1.8.3.min.js" type="text/javascript"></script>
<link href="css/twitmarquee.css" media="screen" rel="stylesheet" type="text/css" />
<style>
.ui-dialog {
    width: 450px;
	top:60px;
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
</style>

<!--IMAGE SLIDE SHOW-->
<script type="text/javascript" src="http://cdn.pieceoftheworld.co/js/slideshow/slideshow.js" ></script>
<!--END OF IMAGE SLIDE SHOW-->

<!--MAKE BOX DRAGGABLE-->
<script src="http://cdn.pieceoftheworld.co/js/draggable/jquery-1.9.1.js"></script>
<script src="http://cdn.pieceoftheworld.co/js/draggable/jquery-ui.js"></script>
<link href="http://cdn.pieceoftheworld.co/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<!--END OF MAKE BOX DRAGGABLE-->

<!-- colorbox -->
<script src="http://cdn.pieceoftheworld.co/js/colorbox-master/jquery.colorbox.js"></script>
<link rel="stylesheet" type="text/css" href="http://cdn.pieceoftheworld.co/js/colorbox-master/example1/colorbox.css" media="screen" />
<!-------------->

<!--INITIALIZE MAP-->
<script src="http://maps.google.com/maps/api/js?sensor=true&libraries=geometry" type="text/javascript"></script>
<script src="js/main.php?_<?php echo time(); ?>&<?php echo $_SERVER['QUERY_STRING']; ?>" type="text/javascript"></script>
<!--END OF INITIALIZE MAP-->

<script src="js/zoi.php?_<?php echo time(); ?>" type="text/javascript"></script>


<script type="text/javascript" src="js/twitmarquee/twitmarquee.js"></script>


</head>

<body>
<div id="fb-root"></div>
<table id='loadinggrid' ><tr><td valign='middle' align='center'>Loading Data...</td></tr></table>
<div id="header_bg">
	<div id="logo"><a href="index2.php"><img src="images/logo.png" width="144" height="18" border="0" alt="PieceoftheWorld" title="PieceoftheWorld" /></a></div>
	<div id="updates">
		<table width="1021" height="28" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td width="25"><img src="images/interscape_white.png" width="19" height="20" border="0" alt="InterScape" title="InterScape" /></td>
			<td valign="top" width="996">
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
	<div id="search">
		<table width="435" height="42" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td valign="top" width="318"><input type="text" class="input_1" style="width:318px; height:24px; border:0; text-align:center;" id="search_enteraplace" name="search" value="Enter your favorite place on earth here..." onFocus="if(this.value=='Enter your favorite place on earth here...'){ this.value=''; }" onBlur="if(this.value==''){ this.value='Enter your favorite place on earth here...'; }" /></td>
			<td valign="top" width="117"><img src="images/search_btn.png" width="117" height="26" border="0" alt="Take me there..." title="Take me there..." style="cursor:pointer;" /></td>
		  </tr>
		</table>
	</div>
	<div id="menus">
		<img src="images/menu_about.png" width="39" height="14" border="0" alt="About" title="About" style="cursor:pointer;" id="menu_about" onClick="openClosePopUp('about');" /> &nbsp; 
		<img src="images/menu_top_lists.png" width="58" height="14" border="0" alt="Top Lists" title="Top Lists" style="cursor:pointer;" id="menu_top_lists" onClick="openClosePopUp('top_lists');" /> &nbsp; 
		<!--<img src="images/menu_tutorials.png" width="56" height="14" border="0" alt="Tutorials" title="Tutorials" style="cursor:pointer;" id="menu_tutorials" onClick="openClosePopUp('tutorials');" /> &nbsp; -->
	</div>
	<div id="facebook" style='background:white; width;380px; height:30px; padding-left:10px; padding-top:6px; margin-top:3px; margin-right:20px; float:right; 
	-moz-border-radius: 5px; border-radius: 5px;'>
		<table height="35" border="0" cellspacing="0" cellpadding="0" id="facebook_table">
		  <tr>
			<td colspan=3>
			<iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fpieceoftheworld.co&amp;send=false&amp;layout=standard&amp;width=400&amp;show_faces=false&amp;font=arial&amp;colorscheme=light&amp;action=like&amp;height=35&amp;appId=454736247931357" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:400px; height:35px;" allowTransparency="true"></iframe>
			</td>
		  </tr>
		</table>
	</div>
</div>
<div id="header_arc">&nbsp;</div>
<div id="map_canvas"></div>

<!--POPUP-->
<div id="popup" style="display:none;">
	<div id="popup_top">&nbsp;</div>
	<div id="popup_top_arc">&nbsp;</div>
	<div id="popup_content">
		<div id="popup_header">
			<div id="popup_icon_interscape"><img src="images/interscape_blue.png" width="21" height="22" border="0" /></div>
			<div id="popup_title_interscape" class="text_2">InterScape</div>
			<div id="popup_header_right" align="right"><img src="images/facebook_signin_icon.png" id="facebook_signin_btn" width="150" height="22" border="0" style="cursor:pointer;" onClick="openClosePopUp('facebook');" /></div>
		</div>
		<div id="popup_main_content">
			<div id="content_about" style="display:none;">
				<div class="text_3">
				<p style="padding:0px; margin:0px;" class="text_2">About</p>
				<p><hr /></p>
				<p class="text_2">Dear Citizen of the World</p>
				
				<p>Welcome to <a target="_blank" href="http://www.PieceoftheWorld.com">PieceoftheWorld.com</a>, the site where you set your mark on the world. You will be in charge and have full control of your virtual piece - upload a picture and write a description.</p>
				
				<p>You will receive a certificate by email proving that you are the exclusive owner. Should you receive a good offer, you can sell your piece of the world, hopefully making a profit.</p>
				
				<p>Each piece represents an acre of our planet and it can be yours today! What part of the world means something special to you? That cafe where you met your spouse? The arena of your favorite football team? Your childhood home? Your school or university? One square costs $ 9.90 ($ 6.93 if shared on Facebook).</p>
				
				<p>So join us and set your mark - get your piece of the world today.</p>
				
				<p>Piece of the World team</p>
				
				<p style="padding:0px; margin:0px;">Contact us:<br /><a href="mailto:PieceoftheWorld2013@gmail.com">PieceoftheWorld2013@gmail.com</a></p>
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
							<div id="slideshow1" style="display:none;"><img src="images/others/1.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow2" style="display:none;"><img src="images/others/2.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow3" style="display:none;"><img src="images/others/3.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow4" style="display:none;"><img src="images/others/4.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow5" style="display:none;"><img src="images/others/5.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow6" style="display:none;"><img src="images/others/6.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow7" style="display:none;"><img src="images/others/7.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow8" style="display:none;"><img src="images/others/8.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow9" style="display:none;"><img src="images/others/9.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
							<div id="slideshow10" style="display:none;"><img src="images/others/10.png" width="140" height="120" alt="PieceOfTheWorld" title="PieceOfTheWorld" border="0" /></div>
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
							<script>startSlideShow(10000);</script>
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
			<div id="content_info" style="display:none;"><?php include_once('includes/contentinfo.php'); ?></div>
			<div id="content_facebook" style="display:none;">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				  <tr>
					<td height="30">&nbsp;</td>
				  </tr>
				  <tr>
					<td align="center"><img src="images/facebook_logo.png" width="150" height="31" border="0" alt="Facebook" title="Facebook" /></td>
				  </tr>
				  <tr>
					<td height="10"></td>
				  </tr>
				  <tr>
					<td align="center">
						<input type="text" id="facebook_field_email_id" name="facebook_field_email" value="Email" style="width:270px; height:30px;" class="input_3" onFocus="if(this.value=='Email'){ this.value=''; }" onBlur="if(this.value==''){ this.value='Email'; }" /><br />
						<input type="text" id="facebook_field_password_id" name="facebook_field_password" value="Password" style="width:270px; height:30px;" class="input_3" onFocus="if(this.value=='Password'){ this.value=''; }" onBlur="if(this.value==''){ this.value='Password'; }" />
					</td>
				  </tr>
				  <tr>
					<td height="10"></td>
				  </tr>
				  <tr>
					<td align="center"><input type="button" id="facebook_btn_id" name="facebook_btn" value="Log In" style="width:280px; height:30px;" class="input_2" /></td>
				  </tr>
				  <tr>
					<td height="50">&nbsp;</td>
				  </tr>
				</table>
			</div>
		</div>
	</div>
	<div id="popup_bottom"></div>
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
</body>
</html>