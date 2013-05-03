$(document).ready(function(){
	if(loggedIn){
		$( "#tabs" ).tabs( {active: 5});
		$('#tabs [href="#login"]').hide();
		getLands();
	} else {
		$( "#tabs" ).tabs( {active: 4});
		$('#tabs [href="#ownedLands"]').hide();
	}
	$( "#userPanelExtra" ).dialog({
		height: 400,
		width: 400,
		//modal: true,	  
		autoOpen: false,
		show: {
			effect: "slide",        		
			duration: 300
		},
	});		

	function getLands(){
		$.ajax({
			dataType: "html",
			type: 'post',
			async: true,
			url: 'ajax/page_webuserLands.php',						
			success: function(data){
				$('#ownedLandList').html(data);
				$(".editableText").editInPlace({					
					url: "ajax/user_fxn.php?action=edit",		
					saving_animation_color: "#ECF2F8",					
				});
				$(".editableTextarea").editInPlace({
					url: "ajax/user_fxn.php?action=edit",
					saving_animation_color: "#ECF2F8",
					field_type: "textarea",
				});
			},
		});	
	}
	function nl2br(str) {   		
		return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ '<br />' +'$2');
	}	
	function resizeHeight(){
		$("#userPanelExtra").dialog({height: 500});
//		<?php echo $record['type']?>_<?php echo $record['id']?> iframe",window.parent.document).height( $("#record_form").height() + 100 );
	}	
	$('#regLink').click(function(e){
		e.preventDefault();
		$('#loginHolder').slideToggle();
		$('#regHolder').slideToggle();
	});
	$('#loginLink').click(function(e){
		e.preventDefault();
		$('#regHolder').slideToggle();
		$('#loginHolder').slideToggle();
	});
	$('#logoutLink').click(function(e){
		e.preventDefault();
		$.ajax({
			dataType: "json",
			type: 'get',
			async: false,
			url: "ajax/user_fxn.php?action=logout",
			success: function(data){				
				if(data.status){					
					$('#tabs [href="#ownedLands"]').hide();
					$('#tabs [href="#login"]').show();
					$( "#tabs" ).tabs( {active: 4});
				}
			}		
		});
	});
	$('#loginButton').click(function(){
		$("#loginStatus").hide('slide');
		$.ajax({
			dataType: "json",
			type: 'post',
			async: false,
			url: "ajax/user_fxn.php?action=login",
			data: $('#loginForm').serialize(),
			success: function(data){
				if(data.status){
					$('#tabs [href="#ownedLands"]').show();
					$('#tabs [href="#login"]').hide();
					$( "#tabs" ).tabs( {active: 5});
					$('.currentUser').html(data.content.useremail);
					getLands();
				} else {
					$("#loginStatus").html(data.message);			
					$("#loginStatus").show('slide');					
				}
			},
		});	
	});	
	$('#registerButton').click(function(){
		$("#regStatus").hide('slide');
		$.ajax({
			dataType: "json",
			type: 'post',
			async: false,
			url: "ajax/user_fxn.php?action=register",
			data: $('#registerForm').serialize(),
			success: function(data){
				if(data.status){
					$('#tabs [href="#ownedLands"]').show();
					$('#tabs [href="#login"]').hide();
					$( "#tabs" ).tabs( {active: 5});
					$('.currentUser').html(data.content.useremail);
					getLands();
				} else {
					$("#regStatus").html(data.message);			
					$("#regStatus").show('slide');
					
				}
			},
		});	
	});
	$('.manageImageLink').live('click', function(e){	
		$( "#userPanelExtra" ).html("<img src='images/loading.gif'>");
		$( "#userPanelExtra" ).dialog( "open" );
		e.preventDefault();
		$id = $(this).attr('data-id');
		$.ajax({
			dataType: "html",
			type: 'get',
			data: $('#form_'+$id).serialize(),
			url: 'ajax/page_webuserPictures.php',						
			success: function(data){
				$( "#userPanelExtra" ).dialog( {title: "Manage Images"} );
				$('#userPanelExtra').html(data);
				resizeHeight();				
			},
		});	
	});
	$('.manageTags').live('click', function(e){	
		$( "#userPanelExtra" ).html("<img src='images/loading.gif'>");
		$( "#userPanelExtra" ).dialog( "open" );
		e.preventDefault();
		$id = $(this).attr('data-id');
		$.ajax({
			dataType: "html",
			type: 'get',
			data: $('#form_'+$id).serialize(),
			url: 'ajax/page_webuserTags.php',						
			success: function(data){
				$( "#userPanelExtra" ).dialog( {title: "Manage Category and Tags"} );
				$('#userPanelExtra').html(data);
				resizeHeight();
				
			},
		});	
	});
});