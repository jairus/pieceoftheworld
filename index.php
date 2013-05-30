<?php 
if(strtolower($_SERVER['HTTP_HOST'])=="pieceoftheworld.com"){
	$url = "http://pieceoftheworld.co/".ltrim($_SERVER['REQUEST_URI'], "/");
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: '.$url);
}
else if(strpos(strtolower($_SERVER['HTTP_HOST']), "www.")===0){
	$url = "http://pieceoftheworld.co/".ltrim($_SERVER['REQUEST_URI'], "/");
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: '.$url);
}
session_start(); 
include_once(dirname(__FILE__).'/ajax/global.php');
if($_GET['coords']){
	$_SESSION["coords"] = $_POST['data'];
	$_SESSION["buydetails"] = $_POST['buydetails'];
	print_r($_SESSION["coords"]);
	exit();
}
else if($_GET['saveblockdetails']){
	/*
	stdClass Object
	(
		[points] => 526670-289189
		[strlatlong] => 14.578996186780754,120.97291737107537
		[city] => Manila
		[region] => Metro Manila
		[country] => Philippines
		[areatype] => 
	)
	*/
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
	}
	else{
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
<link href="http://cdn.pieceoftheworld.co/twitmarquee/front.css" media="screen" rel="stylesheet" type="text/css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.3.0/jquery.min.js" type="text/javascript"></script>
<link href="http://cdn.pieceoftheworld.co/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="http://cdn.pieceoftheworld.co/js/jquery-1.8.3.min.js" type="text/javascript"></script>
<script src="http://cdn.pieceoftheworld.co/js/jquery-ui-1.9.2.custom.min.js" type="text/javascript"></script>
<script src="http://cdn.pieceoftheworld.co/js/jquery.jcarousel.min.js" type="text/javascript"></script>
<link href="http://cdn.pieceoftheworld.co/css/jquery.reveal.css" rel="stylesheet">
<script src="http://cdn.pieceoftheworld.co/js/jquery.reveal.js" type="text/javascript"></script>
<script src="http://maps.google.com/maps/api/js?sensor=true&libraries=geometry" type="text/javascript"></script>
<link href="http://cdn.pieceoftheworld.co/css/main.css" rel="stylesheet">

<!-- main javascript file -->
<link href="http://pieceoftheworld.co/js/main.js" rel="stylesheet">


<script type="text/javascript" src="jquery-lightbox-0.5/js/jquery.lightbox-0.5.js"></script>
<link rel="stylesheet" type="text/css" href="jquery-lightbox-0.5/css/jquery.lightbox-0.5.css" media="screen" />

<script src="js/colorbox-master/jquery.colorbox.js"></script>
<link rel="stylesheet" type="text/css" href="js/colorbox-master/example1/colorbox.css" media="screen" />

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
</style>
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
</head>

<body style="cursor: auto; margin:0px;">
<div id="fb-root"></div>
<table style='z-index: 1010; width:300px; height:100px; position:absolute; background:white; top:-10000px' id='loadinggrid' ><tr><td valign='middle' align='center'>Loading Data...</td></tr></table>
<div id="map_canvas" style='top:55px;'></div>
<div class="cpanelwnd ui-dialog ui-widget ui-widget-content ui-corner-all" style="outline: 0px none; z-index: 1008; position: absolute; border: 0px !important;" tabindex="-1" role="dialog" aria-labelledby="ui-id-1">
      <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix change_titlebar_style" style="border: solid 1px #578C0B !important; border-bottom: solid 1px #97CF48 !important; background: #97CF48 !important;"><span id="ui-id-1" class="ui-dialog-title" style="text-align:center; width: 100%;"><img src="images/cpanel-logo.png?_=<?php echo time();?>"></span></div>
      <div id="dialog" class="ui-dialog-content ui-widget-content change_dialog_style" style="width: auto; min-height: 52px; height: auto; border: solid 1px #578C0B !important; border-top: solid 1px #97CF48 !important; background: #97CF48 !important;" scrolltop="0" scrollleft="0">
		<div class="dialog_body">
			<?php include('mainTabs.php'); ?>
		</div>
	</div>
    </div>

<div id="header">
<style>
#table{
	width:100%;
	height:100%;
}
#shadowx{
	height:55px;
	-webkit-box-shadow: 0px 3px 0px rgba(255, 255, 255, 0.21);
	-moz-box-shadow:    0px 3px 0px rgba(255, 255, 255, 0.21);
	box-shadow:         0px 4px 0px rgba(255, 255, 255, 0.21);
	top:0px;
	position:absolute;
	width:100%;
	background:#24416d;
}
</style>
<div id='shadowx'>
	<table id='table' cellpadding=0 cellspacing=0>
		<tr>
			<td style='vertical-align:middle; width:250px; padding-left:30px;'>
				<a href='/'><img src='images/logo.png' style='border:0px'></a>
			</td>
			<td style='vertical-align:middle; display:none'>
			<div id="trends" style="position:absolute; top:0px; height:55px; width:100%;">
				<div class="inner" style="height:55px; width:100%; top:15px; font-size:14px;">
							<?php
								
								echo '<ul class="trendscontent">';
								$sql = "SELECT `a`.`x`, `a`.`y`, `b`.`title`, `b`.`detail` FROM `land` as  `a` left join `land_detail` as `b` on (`a`.`land_detail_id`=`b`.`id`) ORDER BY `a`.`id` DESC LIMIT 30";
								$results = dbQuery($sql);
								$t = count($results);
								$titles = array();
								$counter = 0;
								for($i=0; $i<$t; $i++){
									$tt = trim($results[$i]['title'])."~".trim($results[$i]['detail']);
									if(!in_array($tt, $titles)){
										$titles[] = $tt;
										echo '<li style="display:inline;">';
										echo '<img src="images/tinytrans.png">';
										echo '</li>';
										echo '<li style="display:inline; position:relative; margin-right:30px">';
										echo '<a href=# onclick="gotoLoc('.$results[$i]['x'].', '.$results[$i]['y'].')" class="search_link" rel="nofollow" style="color:#b8ff00; text-decoration:none; top:-5px; position:relative">'.$results[$i]['title'].'</a>';
										echo '<em class="description" style="color:#b8ff00; text-decoration:none">'.$results[$i]['detail'].'</em>';
										//echo "</td>";
										//echo "</tr></table>";
										echo '</li>';
										$counter++;
										if($counter>15){
											break;
										}
									}
									
								}
							
								echo '</ul>';
							?>
				</div>
			</div>
			</td>
		</tr>
	</table>
</div>
</div>
<div class="trendtip">
	<div class="trendtip-content">
		<div align="center">Recently Purchased Land</div>
			<a class="trendtip-trend"></a>
			<div class="trendtip-why">
				<span class="trendtip-desc"></span>
				<span class="trendtip-source"><span></span></span>
			</div>
		</div>
	<div class="trendtip-pointer">&nbsp;</div>
</div>
<script src="twitmarquee/twitmarquee.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
var page={};
$(function() { new FrontPage().init(); });
//]]>

</script>
</body>
</html>
