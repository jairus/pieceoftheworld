<?php 
session_start();
?>
<style>
.hide {display: none;}
.statusResult { padding: 5px; background-color: #FFFFAA; border: solid 1px #FFD455; display: none;}
.table {border-collapse: collapse; }
.table td , .table th { padding: 5px;}
form label {display: inline-block; text-align: right; margin-right: 10px;}
#loginForm label {width: 70px;}
#registerForm label {width: 130px;}
#ownedLandList .table {width: 410px;}
</style>

		<div id="tabs" class="change_tab_style">
			<ul class="change_tab_ul_style">
				<!--
				<li><a style="padding: 2px !important;" href="#news">News</a></li>
				-->				
				<li><a style="padding: 2px !important;" href="#info">Info</a></li>
				<li><a style="padding: 2px !important;" href="#search">Search</a></li>
				<!--
				<li><a style="padding: 2px !important;" href="#us">US</a></li>
				-->
				<!--
				<li><a style="padding: 2px !important;" href="#buy"><img src="images/cart.png" width="11" height="11" border="0"></a></li>
				-->
				<!--
				<li><a style="padding: 2px !important;" href="#configuration"><img src="images/compile.png" width="11" height="11" border="0"></a></li>
				-->
				<li><a style="padding: 2px !important;" href="#configuration">Settings</a></li>
				<!--
				<li><a style="padding: 2px !important;" href="#help"><img src="images/question.png" width="11" height="11" border="0"></a></li>
				-->
				<li><a style="padding: 2px !important;" href="#help">About</a></li>
				<li><a style="padding: 2px !important;" href="#login">Login</a></li>
				<li><a style="padding: 2px !important;" href="#ownedLands">Your Land</a></li>				
				
			</ul>
		<!--
		<div id="news" class="tab_body news">
          <h3>News</h3>
          <ul id="news-ul" class="jcarousel jcarousel-skin-tango">
            <li><span id="news-1-text" style="float: left; width: 200px;"></span><span><img id="news-1-img" src="images/news_img_1.png" width="32" height="32" border="0"></span></li>
            <li><span id="news-1-text" style="float: left; width: 200px;"></span><span><img id="news-1-img" src="images/news_img_1.png" width="32" height="32" border="0"></span></li>
            <li><span id="news-1-text" style="float: left; width: 200px;"></span><span><img id="news-1-img" src="images/news_img_1.png" width="32" height="32" border="0"></span></li>
          </ul>
        </div>
		-->
		<div id="login" class="tab_body" >			
			<div id="loginHolder" >
              <h3>Login</h3>
			  <form id="loginForm">
				<label>Email: </label><input type="text" name="email" /><br/>
				<label>Password: </label><input type="password" name="password"  /><br/>
				<label></label><input type="button" value="submit" id="loginButton" />
			  </form>
			  <div id="loginStatus" class="statusResult"></div>
			  <br/>No Account yet? <a href="#" id="regLink">SIGN UP</a> now!
			</div>
			<div id="regHolder" style="display: none;">
			  <h3>Register</h3>              
			  <form id="registerForm">
				<label>Email: </label><input type="text" name="email" /><br/>
				<label>Password: </label><input type="password" name="password" /><br/>
				<label>Retype Password: </label><input type="password" name="password_again" /><br/>
				<label></label><input type="button" value="submit" id="registerButton" />
			  </form>
			  <div id="regStatus"  class="statusResult"></div>
			  <br/>Already have an account? <a href="#" id="loginLink">LOGIN</a> now!
            </div>		
        </div>    
        <div id="ownedLands" class="tab_body">			
			<div id="loggedinHolder">
				<h3>Hello <span class='currentUser' ><?php echo $_SESSION['userdata']['useremail']?> </span>! | <a href="#" id="logoutLink" >LOGOUT</a></h3>								
				<div id='ownedLandList'>loading lands...</div>				
			</div>
		</div>
        <div id="info" class="tab_body">
		  <span id="info-span-noselection" style="display:block; padding:5px; padding-top:15px;">
		    <center><img src="images/pastedgraphic.jpg" width="235" border="0"></center>
		  </span>
		  <span id="info-span" style="display:none;">
            <h3><span id="info-city"></span><span id="info-title"></span></h3>
              <table>
                <tr>
                  <td valign=top><div class="img"><a id='info-lightbox' ><img id="info-img" border="0"></a></div></td>
                  <td valign="top">
				    <table>
                      <tr style='display:none'>
                        <td><strong>Latitude:</strong></td>
                        <td><span id="info-latitude"></span></td>
                      </tr>
                      <tr style='display:none'>
                        <td><strong>Longitude:</strong></td>
                        <td><span id="info-longitude"></span></td>
                      </tr>
					 
                      <tr id='info-land_owner_container' style='display:none'>
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
						  <td><a id='fbsharelink' style='border:0px;' ><img style='border:0px;' src='fbshare.jpg' id='fbshare'></a></td>
						  <td valign='middle' id='sharethisloc'>Share this location</td>
						  </tr>
						  </table>
						  </center>
					    </td>
                      </tr>
                    </table>
			      </td>
                </tr>
              </table>
		  </span>	  
        </div>
        <div id="search" class="tab_body">
              <h3>Search</h3>
              Here you can make a search for any area, street, mountain, country, landmark, address etc. Just make your desired search to instantly bring you to that location.<br/>
              <div style="width: 250px; overflow: hidden;">
            <input type="text" id="search_enteraplace" name="search_enteraplace_name" style="width: 90%;">
          </div>
              <h3>Pick one of the World's top places</h3>
              <div style="width: 250px; overflow: hidden;">
              <select id="search_topplaces" name="search_topplaces_name" onChange="updatePopupWindowTabSearch();" style="width: 90%;">
                  <option value="" selected="selected">Select from World's top places</option>
                  <option value="Atlantis">Atlantis</option>
                  <option value="Firefox Crop Circles">Firefox Crop Circles</option>
                  <option value="UFO Landing Pads">UFO Landing Pads</option>
                  <option value="Badlands Guardian">Badlands Guardian</option>
                  <option value="Lost at Sea">Lost at Sea</option>
             </select>
          </div>
            </div>
		<!--	
        <div id="us" class="tab_body">
              <h3>Learn about POTW</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin dignissim pharetra purus at malesuada.</p>
              <p>Praesent diam neque, malesuada vel aliquet vitae, accumsan ac ipsum. Sed mauris nibh, venenatis vitae pulvinar eget, rutrum vestibulum elit. Suspendisse ac neque enim.</p>
              <p>Donec porta ipsum quis magna interdum facilisis. Nam tristique dignissim mattis. Mauris ornare mollis lectus sed facilisis. Etiam faucibus sollicitudin accumsan.</p>
        </div>
		-->
		<!--
        <div id="buy" class="tab_body">
          <h3><span id="buy-title">Buy Land</span></h3>
              <table>
                <tr>
                  <td><div class="img"><img id="buy-img" src="images/eiffel.png"></div></td>
                  <td valign="top">
				    <table>
                      <tr>
                        <td><strong>Latitude:</strong></td>
                        <td><span id="buy-latitude">48.85800</span></td>
                      </tr>
                      <tr>
                        <td><strong>Longitude:</strong></td>
                        <td><span id="buy-longitude">2.29460</span></td>
                      </tr>
                      <tr>
                        <td colspan="2">
						  <br/><br/><br/>
                          <center><input type="button" id="buy-button" value="Buy Now" style="padding: 10px; padding-left: 25px; padding-right: 25px;" onClick="onBuyLand();"></center>
					    </td>
                      </tr>
                    </table>
			      </td>
                </tr>
              </table>
        </div>
		-->
        <div id="configuration" class="tab_body">
              <h3>Settings</h3>
              <p>
          <table width="100%">
            <tr>
              <td>Email</td>
              <td align="right"><input type="edit" id="config_email" name="config_email_name" value="" size=20 onkeypress="document.getElementById('config_save').disabled = false;"><input type="button" id="config_save" name="config_save_name" value="Save" disabled onClick="this.disabled = true; setCookie('user_email', document.getElementById('config_email').value, 365);" ></td>
            </tr>
          </table>
          <table>
            <tr>
              <td>Show Own Land</td>
              <td><input type="checkbox" id="config_showownland" name="config_showownland_name" onClick="updatePopupWindowTabConfig(true);" checked></td>
            </tr>
            <tr>
              <td>Show Important Places</td>
              <td><input type="checkbox" id="config_showimportantplaces" name="config_showimportantplaces_name" onClick="updatePopupWindowTabConfig(true);" checked></td>
            </tr>
            <tr>
              <td>Show Owned Land</td>
              <td><input type="checkbox" id="config_showownedland" name="config_showownedland_name" onClick="updatePopupWindowTabConfig(true);"></td>
            </tr>
            <tr>
              <td>Show Grid</td>
              <td><input type="checkbox" id="config_showgrid" name="config_showgrid_name" onClick="updatePopupWindowTabConfig(true);" checked></td>
            </tr>
          </table>
              </p>
            </div>
        <div id="help" class="tab_body">
              <h3>About</h3>
			  <p>Dear Citizen of the World</p>
			  <p>Welcome to <a href="http://www.PieceoftheWorld.com" target="_blank">PieceoftheWorld.com</a>, the site where you set your mark on the world. You will be in charge and have full control of your virtual piece - upload a picture and write a description.</p>
			  <p>You will receive a certificate by email proving that you are the exclusive owner. Should you receive a good offer, you can sell your piece of the world, hopefully making a profit.</p>
			  <p>Each piece represents an acre of our planet and it can be yours today! What part of the world means something special to you? That cafe where you met your spouse? The arena of your favorite football team? Your childhood home? Your school or university? One square costs $ 9.90 ($ 6.93 if shared on Facebook).</p>
			  <p>So join us and set your mark - get your piece of the world today.</p>
			  <p>Piece of the World team</p>
			  <p>Contact us:<br><a href='mailto:PieceoftheWorld2013@gmail.com'>PieceoftheWorld2013@gmail.com</a></p>
			  
        </div>
      </div>

<script type="text/javascript">
	var loggedIn = '<?php echo isset($_SESSION['userdata'])  ?>';
</script>
<script src="js/webuserControl.js" type="text/javascript"></script>
