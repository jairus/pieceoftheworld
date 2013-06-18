<script>
function landPayment(){
	url = "bidbuyland.php?type=buy&step=1";
	jQuery.colorbox({iframe:true, width:"870px", height:"650px", href:url});
}
</script>
<style>
.longbutton{
	background-color: #006A9B;
	float: left;
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
</style>
<span id="info-buy-span" style="display:none;">
	<form id="form_main_details" name="form_main_details" method="post" enctype="multipart/form-data">
	<!-- TABLE BUY 2 -->
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td>
			<div style="padding-bottom:10px;"><a style="cursor:pointer;" class="text_3" onclick="jQuery('#info-buy-span').hide(); jQuery('#info-span').show(); jQuery('#field_land_title').val(''); jQuery('#field_land_cover_image').val(''); jQuery('#field_land_details').val(''); jQuery('#field_land_owner').val('');">&laquo; back to land info</a></div>
			<div id="top_list_title_1"><input type="text" id="field_land_title" name="field_land_title" style="width:250px; text-align:center;" placeholder="Name your land here" /></div>
			<div id="top_list_img_2" align="center" class="text_3"><div style="border:2px dotted #333333; padding:20px;"><b>Upload your cover image here</b><br /><input type="file" id="field_land_cover_image" name="field_land_cover_image" /></div></div>
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
					<input class='longbutton' type='button' id='btn_buy_land' name='btn_buy_land' value="<?php echo $_SESSION['buydetails']; ?>" onclick="landPayment();" />
				</td>
			  </tr>
			</table>
		</td>
	  </tr>
	</table>
	<!-- END OF TABLE BUY 2 -->
	</form>
</span>