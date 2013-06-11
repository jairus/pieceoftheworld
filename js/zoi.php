<?php
session_start();
header('Content-Type: application/javascript');
?>
<!--MAKE BOX DRAGGABLE-->
jQuery(function(){ jQuery("#popup").draggable({ containment: "window" }); });
<!--END OF MAKE BOX DRAGGABLE-->

<!--OPEN/CLOSE POPUP-->
function openClosePopUp(menu){
	jQuery('#content_about').hide();
	jQuery('#content_top_lists').hide();
	jQuery('#content_tutorials').hide();
	jQuery('#content_info').hide();
	jQuery('#content_facebook').hide();
	
	jQuery('#menu_about').attr("src", 'images/menu_about.png');
	jQuery('#menu_top_lists').attr("src", 'images/menu_top_lists.png');
	jQuery('#menu_tutorials').attr("src", 'images/menu_tutorials.png');
	jQuery('#menu_'+menu).attr("src", 'images/menu_'+menu+'_active.png');
	
	jQuery('#content_'+menu).show();
	if(menu=='facebook'){
		jQuery('#sign_in').hide();//jairus 
	}else{
		jQuery('#sign_in').show();//jairus 
	}
	
	jQuery('#popup').show();
	
}
<!--END OF OPEN/CLOSE POPUP-->

//TOP LISTS
function step1(action, count){
	jQuery('#top_list_categories_active').attr("id", "top_list_categories");
	jQuery('.cat'+count).attr("id", "top_list_categories_active");

    jQuery('#step1').hide();
	jQuery('#step2').hide();
	jQuery('#step3').hide();
	jQuery('#step4').hide();
	jQuery('#step5').hide();
	jQuery('#step6').hide();
	jQuery('#step7').hide();
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

function step2(action, areatype){
    jQuery('#step1').hide();
	jQuery('#step2').hide();
	jQuery('#step3').hide();
	jQuery('#step4').hide();
	jQuery('#step5').hide();
	jQuery('#step6').hide();
	jQuery('#step7').hide();
    jQuery('.searching').show();
	
    jQuery.ajax({
        type: 'GET',
        url: "ajax/get_toplist.php?action="+action+"&areatype="+areatype,
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
    jQuery('#step1').hide();
	jQuery('#step2').hide();
	jQuery('#step3').hide();
	jQuery('#step4').hide();
	jQuery('#step5').hide();
	jQuery('#step6').hide();
	jQuery('#step7').hide();
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
    jQuery('#step1').hide();
	jQuery('#step2').hide();
	jQuery('#step3').hide();
	jQuery('#step4').hide();
	jQuery('#step5').hide();
	jQuery('#step6').hide();
	jQuery('#step7').hide();
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
    jQuery('#step1').hide();
	jQuery('#step2').hide();
	jQuery('#step3').hide();
	jQuery('#step4').hide();
	jQuery('#step5').hide();
	jQuery('#step6').hide();
	jQuery('#step7').hide();
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

function userProfile(userID){
	jQuery('#step1').hide();
	jQuery('#step2').hide();
	jQuery('#step3').hide();
	jQuery('#step4').hide();
	jQuery('#step5').hide();
	jQuery('#step6').hide();
	jQuery('#step7').hide();
    jQuery('.searching').show();
	
    jQuery.ajax({
        type: 'GET',
        url: "ajax/get_toplist.php?action=get_user_profile&userID="+userID,
        data: '',

        success: function(data) {
			jQuery('#step7').show();
		
            jQuery("#tab_wrapperonly7").html(data);
            jQuery('#results7').fadeIn(200);
            
            jQuery('.searching').hide();
        }
    });
}
//END OF TOP LISTS