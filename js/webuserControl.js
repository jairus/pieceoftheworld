$(document).ready(function(){
    // start of facebook functions
    var fbResponse = false;
    window.fbAsyncInit = function() {
        FB.init({
            appId      : '454736247931357', // App ID
            channelUrl : '//pieceoftheworld.co/channel.html',
            status     : true, // check login status
            cookie     : true, // enable cookies to allow the server to access the session
            xfbml      : true  // parse XFBML
        });

        FB.Event.subscribe('auth.login', function(response) {
            $("#loadingImageFb2").hide();
            $("#loadingImageFb1").show();
            if (response.status === 'connected') {
                fbResponse = response;
                loginFb();
            } else { /* FB.login(); */ }
        });
        FB.Event.subscribe('edge.create', function(href, widget) {
            $.ajax({
                 dataType: "json",
                 type: 'get',
                 data: {'href':  encodeURI(href) }, // from index.php line 2121
                 url: 'ajax/user_fxn.php?action=like',
                 success: function(data){
                    console.log(data);
                 }
            });
        });
    };

    // Load the SDK asynchronously
    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    function loginFb() {
        FB.api('/me', function(response) {
            // update fields in login, reg and facebook form in mainTabs.php
            $("form input[name='fb_id']").val(response.id);
            $("form input[name='email']").val(response.email);
            $("form input[name='name']").val(response.name);
            loginUser();
        });
    }
    // end of facebook functions

    if(loggedIn){
        var tabs = $( "#tabs" ).tabs( {active: 5});
        $('#tabs [href="#login"]').hide();
        getLands();
    } else {
        var tabs = $( "#tabs" ).tabs( {active: 4});
        $('#tabs [href="#ownedLands"]').hide();
    }
    tabs.bind("click",function(){
        FB.XFBML.parse(); // another parse call in index.php in show popup function
    });

    $( "#userPanelExtra" ).dialog({
        height: 400,
        width: 400,
        //modal: true,
        autoOpen: false,
        show: {
            effect: "slide",
            duration: 300
        }
    });

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
        logoutUser();
    });
    $('#loginButton').click(function(){
        loginUser();
    });
    $('#registerButton').click(function(){
        registerUser();
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
            }
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

            }
        });
    });

    function getLands(){
        $('#ownedLandList').html('');
        $.ajax({
            dataType: "html",
            type: 'post',
            async: true,
            url: 'ajax/page_webuserLands.php',
            success: function(data){
                $('#ownedLandList').html(data);
                $(".editableText").editInPlace({
                    url: "ajax/user_fxn.php?action=edit",
                    saving_animation_color: "#ECF2F8"
                });
                $(".editableTextarea").editInPlace({
                    url: "ajax/user_fxn.php?action=edit",
                    saving_animation_color: "#ECF2F8",
                    field_type: "textarea"
                });
            },
            error: function(){ alert(error);}
        });
    }
    function nl2br(str) {
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ '<br />' +'$2');
    }
    function loginUser(){
        $("#loginStatus").hide('slide');
        $.ajax({
            dataType: "json",
            type: 'post',
            async: false,
            url: "ajax/user_fxn.php?action=login",
            data: $('#loginForm').serialize(),
            success: function(data){
                $('#loadingImageFb1').hide();
                if(data.status){
                    $('#tabs [href="#ownedLands"]').show();
                    $('#tabs [href="#login"]').hide();
                    $( "#tabs" ).tabs( {active: 5});
                    $('.currentUser').html(data.content.useremail);
                    FB.XFBML.parse();
                    getLands();
                } else {
                    $("#loginStatus").html(data.message);
                    $("#loginStatus").show('slide');
                }
            }
        });
    }
    function registerUser(){
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
            }
        });
    }
    function logoutUser(){
        $("#loadingImageFb1").hide();
        $("#loadingImageFb2").show();
        $.ajax({
            dataType: "json",
            type: 'get',
            async: false,
            url: "ajax/user_fxn.php?action=logout",
            success: function(data){
                if(data.status){
                    // if logged in via FB, then also logout the session
                    if(fbResponse){
                        FB.logout(function(response){
                            location.reload();
                        });
                    } else {
                        $('#tabs [href="#ownedLands"]').hide();
                        $('#tabs [href="#login"]').show();
                        $( "#tabs" ).tabs( {active: 4});
                    }

                }
            }
        });
    }
});