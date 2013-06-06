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
<span id="info-span-noselection" style="display:block; padding:5px; padding-top:15px;">
<center><img src="images/pastedgraphic.jpg" width="235" border="0"></center> 
</span>

<span id="info-span" style="display:none;">
	<div id="top_list_title_1" class="text_2"><span id="info-city"></span><span id="info-title"></span></div>
	<div id="top_list_img_2" align="center"><div class="img"><a id="info-lightbox"><img id="info-img" border="0"></a></div></div>
	<div style="float:left; width:290px; height:auto;">
	<div id="thumbs"></div>
	<div id="video"></div>
	<table width="290" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td width="150px"><a id='fbsharelink'><img src="images/facebook_icon.png" height="15" border="0" id='fbshare' style="cursor:pointer;" />&nbsp;Share this location</a></td>
		<td id='likecolumn_id'></td>
		<td></td>
	  </tr>
	  <tr>
		<td colspan="3" height="10"></td>
	  </tr>
	  <tr>
		<td colspan="3" class="text_1">
			<span id="info-detail"></span>
			<br />&nbsp;
			<div id="dcountry"></div>
			<div id="dregion"></div>
			<div id="dcity"></div>
			<input class='longbutton' type='button' id='loading-button' value="Loading..." style='cursor:default; display:none' />
			<input class='longbutton' type='button' id='buy-button' onClick="onBuyLand();" value="Click to Buy" />
			<input class='longbutton' type="button" id="clicktozoom" value="Click to Zoom" style="display:none">
			<input type="hidden" id="land_id" name="land_id" />
		</td>
	  </tr>
	  <tr>
		<td colspan="3" height="10"></td>
	  </tr>
	  <tr>
		<td colspan="3" class="text_1" align="right"><div id='info-land_owner_container'>Owner: <span id="info-land_owner"></span></div></td>
	  </tr>
	  <tr>
		<td colspan="3" class="text_1" align="right"><div id='info-land_bid_container'>Highest Bid: <span id="info-land_bid"></span></div></td>
	  </tr>
	  <tr>
		<td colspan="3" height="10"></td>
	  </tr>
	</table>
	</div>
	<!--<div id="buy-button" style="float:left; width:290px; height:auto; padding-bottom:5px; display:none;"><div id="top_list_title_1"><a class="link_1" style="cursor:pointer;" onClick="onBuyLand();">Buy Land</a></div></div>-->
	
	<!--<div id="image-button" style="float:left; width:290px; height:auto; padding-bottom:5px; display:none;"><div id="top_list_title_1"><a class="link_1">Image</a></div></div>-->
	<div id="top_list_title_1 clickvideo" style="display:none;"><a class="link_1" style="cursor:pointer;" onClick="showThumbs();">Videos</a></div>

	<!--<h3><span id="info-city"></span><span id="info-title"></span></h3>
	<div id="fbLikeHolder"></div>
	<div id="thumbs"></div>
	<div id="video"></div>
	<table>
	  <tr>
		  <td valign=top><div class="img"><a id="info-lightbox" ><img id="info-img" border="0"></a></div></td>
		  <td valign="top">
			  <table>
				  <tr style="display:none">
					  <td><strong>Latitude:</strong></td>
					  <td><span id="info-latitude"></span></td>
				  </tr>
				  <tr style="display:none">
					  <td><strong>Longitude:</strong></td>
					  <td><span id="info-longitude"></span></td>
				  </tr>
				  <tr id="info-land_owner_container" style="display:none">
					  <td colspan="2">
						  Owner: <span id="info-land_owner"></span>
					  </td>
				  </tr>
				  <tr>
					  <td colspan="2">
						  <br />
						  <span id="info-detail"></span>
						  <br />&nbsp;
						  <div id="dcountry"></div>
						  <div id="dregion"></div>
						  <div id="dcity"></div>
					  </td>
				  </tr>
				  <tr>
					  <td colspan="2">
						  <center><br>
							  <table>
								  <tr>
									  <td><input type="button" id="buy-button" value="Buy" style="padding: 3px; padding-left: 10px; padding-right: 10px;" onClick="onBuyLand();"></td>
									  <td><input type="button" id="clicktozoom" value="Zoom" style="padding: 3px; padding-left: 10px; padding-right: 10px; display:none"></td>
									  <td><input type="button" id="clickvideo" value="Video" style="padding: 3px; padding-left: 10px; padding-right: 10px; display:none;" onclick="showThumbs();"></td>
									  <td><a id="fbsharelink" style="border:0px;" ><img style="border:0px;" src="fbshare.jpg" id="fbshare"></a></td>
									  <td valign="middle" id="sharethisloc">Share this location</td>
								  </tr>
							  </table>
						  </center>
					  </td>
				  </tr>
			  </table>
		  </td>
	  </tr>
	</table>-->
</span>