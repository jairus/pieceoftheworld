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
<div id='buy_form_results' style="display:none;">
	<div id='buy_form_tab_wrapperonly'></div>
</div>