<style>
*{
	font-family:Verdana, Arial, Helvetica, sans-serif;
	font-size:12px;
}

#toplist_container{
	float:left;
	width:450px;
	height:auto;
	background-color:#38382f;
}

#toplist_categories{
	float:left;
	width:215px;
	height:auto;
	padding:5px;
}

#toplist_category{
	float:left;
	width:173px;
	height:auto;
	background-color:#6d6e71;
	color:#FFFFFF;
	padding:20px;
	cursor:pointer;
	border:1px solid #19c1f3;
	border-top-left-radius:20px;
}

#toplist_category:hover{
	background-color:#38382f;
	color:#19c1f3;
}
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.3.0/jquery.min.js" type="text/javascript"></script>
<script>
function backTo(step){
	jQuery('#step1').hide();
	jQuery('#step2').hide();
	jQuery('#step3').hide();
	jQuery('#step4').hide();
	jQuery('#step5').hide();
	jQuery('#step6').hide();
	jQuery('#step'+step).show();
}

function step1(action){
    jQuery('#step1').hide();
    jQuery('.searching').show();
	
    jQuery.ajax({
        type: 'GET',
        url: "ajax/get_toplist.php?action="+action,
        data: '',

        success: function(data) {
			jQuery('#step2').show();
		
            jQuery("#tab_wrapperonly2").html(data);
            jQuery('#results2').fadeIn(200);
            
            jQuery('.searching').hide();
        }
    });
}

function step2(action){
    jQuery('#step2').hide();
    jQuery('.searching').show();
	
    jQuery.ajax({
        type: 'GET',
        url: "ajax/get_toplist.php?action="+action,
        data: '',

        success: function(data) {
			jQuery('#step3').show();
		
            jQuery("#tab_wrapperonly3").html(data);
            jQuery('#results3').fadeIn(200);
            
            jQuery('.searching').hide();
        }
    });
}

function step3(action, country, areatype){
    jQuery('#step3').hide();
    jQuery('.searching').show();
	
    jQuery.ajax({
        type: 'GET',
        url: "ajax/get_toplist.php?action="+action+"&country="+country+"&areatype="+areatype,
        data: '',

        success: function(data) {
			jQuery('#step4').show();
		
            jQuery("#tab_wrapperonly4").html(data);
            jQuery('#results4').fadeIn(200);
            
            jQuery('.searching').hide();
        }
    });
}

function step4(action, country, region, areatype){
    jQuery('#step4').hide();
    jQuery('.searching').show();
	
    jQuery.ajax({
        type: 'GET',
        url: "ajax/get_toplist.php?action="+action+"&country="+country+"&region="+region+"&areatype="+areatype,
        data: '',

        success: function(data) {
			jQuery('#step5').show();
		
            jQuery("#tab_wrapperonly5").html(data);
            jQuery('#results5').fadeIn(200);
            
            jQuery('.searching').hide();
        }
    });
}

function step5(action, country, region, city, areatype){
    jQuery('#step5').hide();
    jQuery('.searching').show();
	
    jQuery.ajax({
        type: 'GET',
        url: "ajax/get_toplist.php?action="+action+"&country="+country+"&region="+region+"&city="+city+"&areatype="+areatype,
        data: '',

        success: function(data) {
			jQuery('#step6').show();
		
            jQuery("#tab_wrapperonly6").html(data);
            jQuery('#results6').fadeIn(200);
            
            jQuery('.searching').hide();
        }
    });
}
</script>

<div id="toplist_container">
	<div class="searching" style="display:none; color:#FFFFFF; padding:20px;">Searching...</div>
	<div id="step1">
		<?php
		$categories = array(0=>'Biggest Land Owners', 1=>'Biggest Water Owners', 2=>'Most Expensive Lands', 3=>'Most Liked Lands', 4=>'Most Viewed Lands');
		
		$t = count($categories);
		
		for($i=0; $i<$t; $i++){
			echo '<div id="toplist_categories"><div id="toplist_category" onclick="step1(\''.$categories[$i].'\');">'.$categories[$i].'</div></div>';
		}
		?>
	</div>
	<div id="step2" style="display:none;">
		<div id='results2'>
			<div id='tab_wrapperonly2'></div>
		</div>
	</div>
	<div id="step3" style="display:none;">
		<div id='results3'>
			<div id='tab_wrapperonly3'></div>
		</div>
	</div>
	<div id="step4" style="display:none;">
		<div id='results4'>
			<div id='tab_wrapperonly4'></div>
		</div>
	</div>
	<div id="step5" style="display:none;">
		<div id='results5'>
			<div id='tab_wrapperonly5'></div>
		</div>
	</div>
	<div id="step6" style="display:none;">
		<div id='results6'>
			<div id='tab_wrapperonly6'></div>
		</div>
	</div>
</div>