<script>
function addCommas(nStr){
	nStr += '';

	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';

	var rgx = /(\d+)(\d{3})/;

	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}

	return x1 + x2;
}

function fNum(num){
	num = uNum(num);

	if(num==0){ return ""; }

	num = num.toFixed(2);

	return addCommas(num);
}

function uNum(num){
	if(!num){
		num = 0;
	}else if(isNaN(num)){
		num = num.replace(/[^0-9\.]/g, "");

		if(isNaN(num)){ num = 0; }
	}

	return num*1;
}

function submitBidForm(){
	jQuery('#table_bid_form').hide();
    jQuery('#bidresults').hide();

    jQuery('#pleasewait').show();

    jQuery.ajax({
        type: 'POST',
        url: "ajax/save_bid.php?land_id="+jQuery('#land_id').val()+"&bid="+uNum(jQuery('#user_bid').val()),
        data:  jQuery("#theform").serialize(),

        success: function(data) {
            jQuery("#bid_tab_wrapperonly").html(data);
            jQuery('#bidresults').fadeIn(200);
			
			if(data=='<span style="color:red; font-weight:bold;">Invalid E-mail address</span>'){
				jQuery('#table_bid_form').show();
			}else if(data=='<span style="color:red; font-weight:bold;">Please enter your Bid</span>'){
				jQuery('#table_bid_form').show();
			}
            
            jQuery('#pleasewait').hide();
        }
    });
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
<span id="info-span-noselection" style="display:block; padding:5px; padding-top:15px;">
<center><img src="images/pastedgraphic.jpg" width="235" border="0"></center> 
</span>
<input type="hidden" id="land_id" name="land_id" />
<span id="info-span" style="display:none;">
	<div id="top_list_title_1" class="text_2"><span id="info-city"></span><span id="info-title"></span></div>
	<div id="top_list_img_2" align="center"><div class="img"><a id="info-lightbox"><img id="info-img" border="0"></a></div></div>
	<div style="float:left; width:290px; height:auto;">
	<div id="thumbs" style="display:none;" class="text_3"></div>
	<div id="video" style="display:none;" class="text_3"></div>
	<table width="290" border="0" cellspacing="0" cellpadding="0" id="table_main_info">
	  <tr>
		<td width="150px"><a id='fbsharelink'><img src="images/facebook_icon.png" height="15" border="0" id='fbshare' style="cursor:pointer;" />&nbsp;Share this location</a></td>
		<td id='likecolumn_id'></td>
		<td></td>
	  </tr>
	  <tr>
		<td colspan="3" height="5"></td>
	  </tr>
	  <tr>
		<td colspan="3" class="text_1">
			<span id="info-detail"></span>
			<!--<br />&nbsp;
			<div id="dcountry"></div>
			<div id="dregion"></div>
			<div id="dcity"></div>-->
		</td>
	  </tr>
	  <tr>
		<td colspan="3" height="5"></td>
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
	  <tr>
		<td colspan="3" class="text_1">
			<input class='longbutton' type='button' id='loading-button' value="Loading..." style='cursor:default; display:none' />
			<input class='longbutton' type='button' id='buy-button' onClick="onBuyLand();" value="Click to Buy" />
			<input class='longbutton' type="button" id="clicktozoom" value="Click to Zoom" style="display:none" />
			<input class='longbutton' type="button" id="clicktowatch" onClick="showThumbs();" value="Videos" style="display:none;" />
		</td>
	  </tr>
	  <tr>
		<td colspan="3" height="10"></td>
	  </tr>
	</table>
	<div id="div_bid" style="display:none;" class="text_3">
		<form enctype="multipart/form-data" method='post' id='theform' name='theform' style='margin:0px'>
		<div id="pleasewait" style="display:none; text-align:center;"><img src="images/loading.gif" /></div>
		<div id='bidresults' style="display:none;">
			<div id='bid_tab_wrapperonly'></div>
		</div>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" id="table_bid_form">
		  <tr>
			<td colspan="2"><a style="cursor:pointer;" onclick="jQuery('#info-span #div_bid').hide(); jQuery('#info-span #table_main_info').show(); getHighestBid(jQuery('#info-span #land_id').val());">&laquo; back to land info</a></td>
		  </tr>
		  <tr>
			<td colspan="2">&nbsp;</td>
		  </tr>
		  <tr>
			<td width="100">* Email</td>
			<td><input type='text' id='user_email' name='user_email' placeholder="e.g. john@email.com" /></td>
		  </tr>
		  <tr>
			<td colspan="2">&nbsp;</td>
		  </tr>
		  <tr>
			<td>* Bid (<i>USD</i>)</td>
			<td><input type='text' id='user_bid' name='user_bid' placeholder="e.g. 500.00" style="width:80px;" onBlur="this.value=fNum(this.value);" /></td>
		  </tr>
		  <tr>
			<td colspan="2">&nbsp;</td>
		  </tr>
		  <tr>
			<td valign="top">Message</td>
			<td><textarea id='user_message' name='user_message' placeholder="e.g. I would like to bid for this land"></textarea></td>
		  </tr>
		  <tr>
			<td colspan="2">&nbsp;</td>
		  </tr>
		  <tr>
			<td colspan="2">
			<input type='button' id='submitbidbutton' class='longbutton' value="Submit your bid" style='width:100%;' onclick="submitBidForm();" />
			<script>
			function backFromBid(){
				jQuery('#theform')[0].reset();
				jQuery("#info-span #table_main_info").show();
				jQuery('#info-span #div_bid').hide();
				jQuery('#table_bid_form').hide();
			}
			</script>
			<input type='button' id='submitbidbutton' class='longbutton' value="Back" style='width:100%;' onclick="backFromBid();" />
			</td>
		  </tr>
		</table>
		</form>
	</div>
	</div>
</span>