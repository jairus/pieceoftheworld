<?php 
session_start(); 
include_once(dirname(__FILE__).'/ajax/global.php');

function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

$_SESSION['foldername'] = date("Ymd")."_".microtime_float();
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

<!-- colorbox -->
<script src="http://cdn.pieceoftheworld.co/js/colorbox-master/jquery.colorbox.js"></script>
<link rel="stylesheet" type="text/css" href="http://cdn.pieceoftheworld.co/js/colorbox-master/example1/colorbox.css" media="screen" />
<!-------------->

<!-- OPEN IMAGE UPLOADER -->
<script>
function openImageUploader(type){
	var url = "imageuploader.php?type="+type;
	jQuery.colorbox({iframe:true, width:"500px", height:"170px", href:url});
}
</script>
<!-- END OF OPEN IMAGE UPLOADER -->

<style>
.longbutton{
	background-color: #006A9B;
	float: left;
	height: auto;
	padding: 5px;
	text-align: center;
	width: 100%;
	color: #FFFFFF;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 14px;
    font-weight: bold;
	border:0px;
	cursor:pointer;
	margin-bottom:3px;
	border:1px solid white;
}
</style>
</head>

<body>
<!-- BUY FORM -->
<div style="width:310px; height:auto; background-color:#008ABA; padding-left:20px;">
<center>
<form id="form_main_details" name="form_main_details" method="post" enctype="multipart/form-data">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
	<td>
		<div style="padding-bottom:10px;"><a style="cursor:pointer;" class="text_3" onclick="jQuery('#buy_form_results').hide(); jQuery('#info-span').show(); jQuery('#field_land_title').val(''); jQuery('#field_land_cover_image').val(''); jQuery('#field_land_details').val(''); jQuery('#field_land_owner').val('');">&laquo; back to land info</a></div>
		<div id="top_list_title_1"><input type="text" id="field_land_title" name="field_land_title" style="width:250px; text-align:center;" placeholder="Name your land here" /></div>
		<div id="top_list_img_2">
			<div style="float:left; width:245px; height:auto; border:2px dotted #333333; padding:20px;" id="div_image"><input class='longbutton' type='button' id='btn_upload_cover_images' name='btn_upload_cover_images' value="Upload your cover image here" onClick="openImageUploader(1);" /></div>
		</div>
		<table width="290" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td colspan="3" align="center"><textarea id="field_land_details" name="field_land_details" style="width:280px; height:80px;" placeholder="Enter the description of your land here"></textarea></td>
		  </tr>
		  <tr>
			<td colspan="3" height="2"></td>
		  </tr>
		  <tr>
			<td colspan="3" class="text_1" align="right"><div id='info-land_owner_container'>Owner: <input type="text" id="field_land_owner" name="field_land_owner" style="width:160px;" placeholder="Name the land owner here" /></div></td>
		  </tr>
		  <tr>
			<td colspan="3" height="2"></td>
		  </tr>
		  <tr>
			<td colspan="3" class="text_1">
				<input class='longbutton' type='button' id='btn_upload_images' name='btn_upload_images' value="Upload images" />
				<input class='longbutton' type='button' id='btn_upload_videos' name='btn_upload_videos' value="Upload videos" />
				<input class='longbutton' type='button' id='btn_buy_land' name='btn_buy_land' value="Title of the land" onclick="landPayment('form_main_details');" />
			</td>
		  </tr>
		</table>
	</td>
  </tr>
</table>
</form>
<!-- END OF BUY FORM -->
</center>
</div>
</body>
</html>