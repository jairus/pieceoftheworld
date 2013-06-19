<?php
@session_start();
require_once 'global.php';

if($_GET['action']=='get_buy_form'){
	if($_SESSION['userdata']){
		?>
		<!-- BUY FORM -->
		<form id="form_main_details" name="form_main_details" method="post" enctype="multipart/form-data">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		  <tr>
			<td>
				<div style="padding-bottom:10px;"><a style="cursor:pointer;" class="text_3" onclick="jQuery('#buy_form_results').hide(); jQuery('#info-span').show(); jQuery('#field_land_title').val(''); jQuery('#field_land_cover_image').val(''); jQuery('#field_land_details').val(''); jQuery('#field_land_owner').val('');">&laquo; back to land info</a></div>
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
		</form>
		<!-- END OF BUY FORM -->
		<?php
	}else{
		?>
		<!-- SIGN IN OR CONTINUE -->
		<table width="100%" border="0" cellspacing="0" cellpadding="0" id="table_sign_in_or_continue">
		  <tr>
			<td>
				<div style="padding-bottom:10px;"><a style="cursor:pointer;" class="text_3" onclick="jQuery('#buy_form_results').hide(); jQuery('#info-span').show(); jQuery('#field_land_title').val(''); jQuery('#field_land_cover_image').val(''); jQuery('#field_land_details').val(''); jQuery('#field_land_owner').val('');">&laquo; back to land info</a></div>
			</td>
		  </tr>
		  <tr>
			<td class="text_3" align="center" style="padding-bottom:10px;"><b>You are currently not logged in. If you sign in with facebook you will be able to edit your area in the future</b></td>
		  </tr>
		  <tr>
			<td>
				<input class='longbutton' type='button' id='btn_sign_in_with_facebook' name='btn_sign_in_with_facebook' value="Sign in with facebook" onclick="jQuery('#buy_form_results').hide(); updateProfile(); openClosePopUp('facebook');" />
				<input class='longbutton' type='button' id='btn_continue_without_signing_in' name='btn_continue_without_signing_in' value="Continue without signing in" onclick="jQuery('#table_sign_in_or_continue').hide(); jQuery('#table_main_details_logout').show();" />
			</td>
		  </tr>
		</table>
		<!-- END OF SIGN IN OR CONTINUE -->
		
		<!-- BUY FORM -->
		<form id="form_main_details_logout" name="form_main_details_logout" method="post" enctype="multipart/form-data">
		<table width="100%" border="0" cellspacing="0" cellpadding="0" id="table_main_details_logout" style="display:none;">
		  <tr>
			<td>
				<div style="padding-bottom:10px;"><a style="cursor:pointer;" class="text_3" onclick="jQuery('#table_main_details_logout').hide(); jQuery('#table_sign_in_or_continue').show(); jQuery('#buy_form_results').hide(); jQuery('#info-span').show(); jQuery('#field_land_title').val(''); jQuery('#field_land_cover_image').val(''); jQuery('#field_land_details').val(''); jQuery('#field_land_owner').val('');">&laquo; back to land info</a></div>
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
		</form>
		<!-- END OF BUY FORM -->
		<?php
	}
}
?>