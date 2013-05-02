$(document).ready(function(){
	if(loggedIn){
		$( "#tabs" ).tabs( {active: 5});
		$('#tabs [href="#login"]').hide();
		getLands();
	} else {
		$( "#tabs" ).tabs( {active: 4});
		$('#tabs [href="#ownedLands"]').hide();
	}

	function getLands(){
		$.ajax({
			dataType: "html",
			type: 'post',
			async: true,
			url: 'ajax/user_fxn.php?action=getLands',						
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
		e.preventDefault();
		$id = $(this).attr('data-id');
		$type = $(this).attr('data-type');

		$holder = $('#pixHolder_'+$type+'_'+$id);		
		$holder.toggle('slide');
	});
});