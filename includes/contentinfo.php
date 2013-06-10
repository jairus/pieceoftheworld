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
	<div id="thumbs" style="display:none;" class="text_3"></div>
	<div id="video" style="display:none;" class="text_3"></div>
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
		</td>
	  </tr>
	  <tr>
		<td colspan="3" height="10"></td>
	  </tr>
	  <tr>
		<td colspan="3" class="text_1" align="right"><div id='info-land_owner_container'>Owner: <span id="info-land_owner"></span></div></td>
	  </tr>
	  <tr>
		<td colspan="3" height="5"></td>
	  </tr>
	  <tr>
		<td colspan="3" class="text_1" align="right"><div id='info-land_bid_container'>Highest Bid: <span id="info-land_bid"></span></div></td>
	  </tr>
	  <tr>
		<td colspan="3" height="10"></td>
	  </tr>
	  <tr>
		<td colspan="3" class="text_1">
			<input class='longbutton' type='button' id='loading-button' value="Loading..." style='cursor:default; display:none' />
			<input class='longbutton' type='button' id='buy-button' onClick="onBuyLand();" value="Click to Buy" />
			<input class='longbutton' type="button" id="clicktozoom" value="Click to Zoom" style="display:none" />
			<input class='longbutton' type="button" id="clicktowatch" onClick="showThumbs();" value="Videos" style="display:none;" />
			<input type="hidden" id="land_id" name="land_id" />
		</td>
	  </tr>
	  <tr>
		<td colspan="3" height="10"></td>
	  </tr>
	</table>
	</div>
</span>