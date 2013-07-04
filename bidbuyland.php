<?php
include_once(dirname(__FILE__)."/emailer/email.php");
require_once('ajax/user_fxn.php');
session_start();

if($_GET['ajaxlogout']){
	unset($_SESSION['userdata']);
	exit();
}

function sanitizeX($str){
	$str = strip_tags($str);
	$str = htmlentities($str);
	return $str;
}
function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
if($_POST['step']=="1"){
	$_SESSION['POST'] = $_POST;
	if(!$_GET['f']){
		$foldername = date("Ymd")."_".microtime_float();
		$post = $_POST;
	}
	else{
		$foldername = $_GET['f'];		
		$uploads_dir = dirname(__FILE__).'/_uploads/'.$foldername;
		$uploads_http = 'http://pieceoftheworld.co/_uploads/'.$foldername;
		$filename = $uploads_dir."/post.txt";
		$post2 = unserialize(file_get_contents($filename));
		$post = $_POST;
		$post['filename'] = $post2['filename'];
		$post['http_picture'] = $post2['http_picture'];
	}
	$uploads_dir = dirname(__FILE__).'/_uploads/'.$foldername;
	$uploads_http = 'http://pieceoftheworld.co/_uploads/'.$foldername;
	@mkdir($uploads_dir, 0777);
	$filename = $uploads_dir."/post.txt";
	
	
	
	if ($_FILES["image"]["error"] == UPLOAD_ERR_OK) {
		$tmp_name = $_FILES["image"]["tmp_name"];
		$name = $_FILES["image"]["name"];
		move_uploaded_file($tmp_name, "$uploads_dir/$name");
		$post['filename'] = "$uploads_dir/$name";
		$http_picture = $uploads_http."/$name";
		$post['http_picture'] = $http_picture;
	}
	$post['amount'] = $_SESSION['px'];
	$post['coords'] = $_SESSION['coords'];
	$post['buydetails'] = $_SESSION['buydetails'];
	
	file_put_contents ( $filename, serialize($post));
	
	
	if($_POST['register']){
		if(!isUnique(trim($_POST['email']))){
			?>
			<script>
				//alert("?error=1&type=buy&f=<?php echo $foldername; ?>");
				self.location="?f=<?php echo $foldername; ?>&error=The inputted E-mail is already taken. Please input an another E-mail address&type=buy&f=<?php echo $foldername; ?>";
			</script>
			<?php
			exit();
		}
		else if(!checkEmail(trim($_POST['email']))){
			?>
			<script>
				//alert("?error=1&type=buy&f=<?php echo $foldername; ?>");
				self.location="?f=<?php echo $foldername; ?>&error=Invalid E-mail address&type=buy&f=<?php echo $foldername; ?>";
			</script>
			<?php
			exit();
		}
		else if(trim($_POST['password'])==""||($_POST['password']!=$_POST['repassword'])){
			?>
			<script>
				//alert("?error=1&type=buy&f=<?php echo $foldername; ?>");
				self.location="?f=<?php echo $foldername; ?>&error=Password and Re-type Password dont match&type=buy&f=<?php echo $foldername; ?>";
			</script>
			<?php
			exit();
		}
		else if(!trim($_POST['email'])||!trim($_POST['title'])||!trim($_POST['name'])||!trim($_POST['country'])){
			?>
			<script>
				//alert("?error=1&type=buy&f=<?php echo $foldername; ?>");
				self.location="?f=<?php echo $foldername; ?>&error=Please complete all fields with *&type=buy&f=<?php echo $foldername; ?>";
			</script>
			<?php
			exit();
		}
	}
	else if(!trim($_POST['email'])||!trim($_POST['title'])){
		?>
		<script>
			//alert("?error=1&type=buy&f=<?php echo $foldername; ?>");
			self.location="?f=<?php echo $foldername; ?>&error=Please complete all fields with *&type=buy&f=<?php echo $foldername; ?>";
		</script>
		<?php
		exit();
	}
}
else if($_GET['f']){
	$foldername = $_GET['f'];
	$uploads_dir = dirname(__FILE__).'/_uploads/'.$foldername;
	$uploads_http = 'http://pieceoftheworld.co/_uploads/'.$foldername;
	$filename = $uploads_dir."/post.txt";
	$post = unserialize(file_get_contents($filename));
	if($_GET['remove_image']){
		unset($post['filename']);
		unset($post['http_picture']);
		file_put_contents ( $filename, serialize($post));
	}
}

?>
<!doctype html>
<html lang="us">
<head>
<meta charset="utf-8">
<title>PieceoftheWorld</title>
<script src="js/jquery-1.8.3.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="/js/tagsinput/jquery.tagsinput.css" />
<script type="text/javascript" src="/js/tagsinput/jquery.tagsinput.js"></script>
<style>
.hint{
	padding-left:20px;
	font-size:10px;
	font-style:italic;
	display:inline;
}
.header{
	padding:10px;
	background-color: #006A9B;
	color: #FFFFFF;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 14px;
    font-weight: bold;
	
}
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
	border:2px solid white;
}
body{
	background: #008aba;
}
a:link, a:hover, a:visited{
	color:white;
}
td{
	color: white;
}
</style>
<script>
function consoleX(str){
	try{
		console.log(str);
	}
	catch(e){
	}
}
function logout(){
	jQuery.ajax({
		url:"?ajaxlogout=1",
		dataType:'html',
		async:false,
		success:function(data, textStatus, jqXHR){
			
		}
	});
	jQuery("#theform *").attr("disabled", true);
	self.location = "bidbuyland.php?type=<?php echo $_GET['type']; ?>&f=<?php echo $_GET['f']; ?>&_="+(new Date()).getTime();
}
function login(){
	jQuery.ajax({
		dataType: "json",
		type: 'post',
		async: false,
		url: "ajax/user_fxn.php?action=login",
		data: "email="+$('#lemail').val()+"&password="+$('#lpassword').val(),
		success: function(data){
			if(data.status){
				self.location = "bidbuyland.php?type=<?php echo $_GET['type']; ?>&f=<?php echo $_GET['f']; ?>&_="+(new Date()).getTime();
			} else {
				jQuery("#loginStatus").html(data.message);			
				jQuery("#loginStatusTr").show('slide');					
			}
		},
	});	
}
function showLogin(){
	jQuery("#loginStatusTr").hide();	
	jQuery(".createaccount").hide();
	jQuery(".loginaccount").show();
	jQuery("#continuebutton").attr("disabled", true);
}
function cancelLogin(){
	jQuery("#loginStatusTr").hide();		
	jQuery(".createaccount").show();
	jQuery(".loginaccount").hide();
	jQuery("#continuebutton").attr("disabled", false);
}
$(document).ready(function() {
	jQuery('#tags_1').tagsInput({width:'auto', height:'30px', 
	'defaultText':''
	});
});
</script>
<body style="cursor: auto;">
<div id="fb-root"></div>
<script>
// Load the SDK asynchronously
	(function(d, s, id){
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) {return;}
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/en_US/all.js";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
</script>
<?php
if($_POST['step']==1||$_GET['fromshare']){
	if($_GET['post_id']){
		$discount = 0.1;
	}
	else{
		$discount = 0;
	}
	$paypalvalue = $_SESSION['px'];
	$paypalvalue = floor($paypalvalue - ($paypalvalue * $discount));
	?>
	<style>
	td{
		vertical-align:top;
	}
	input[type='text']{
		width:200px;
	}
	textarea{
		width:200px;
	}
	*{
		font-size:11px;
		font-family:verdana;
	}
	</style>
	<center>
	<br /><br />
	<table style='height:100%;'><tr><td style='height:100%; vertical-align:middle'>
		<table cellpadding=3 style='width:662px;'>
		<tr>
			<td style='width:405px'>
				<table cellpadding=10 style='width:100%' >	
					<tr class='account'>
						<td colspan=2 class='header'>
						<b>Account</b>
						</td>
					</tr>
					<tr class='account'>
						<td>* E-mail</td>
						<td>
						<?php echo sanitizeX($post['email']) ?>
						</td>
					</tr>
					<tr>
						<td colspan=2 class='header'><b>Land Details</b></td>
					</tr>
					<tr>
						<td>* Name for your land</td>
						<td><?php echo sanitizeX($post['title']) ?></td>
					</tr>
					<tr>
						<td>Description for your land</td>
						<td><?php echo sanitizeX($post['description']); ?></td>
					</tr>
					<tr>
						<td>Name of the land owner</td>
						<td><?php echo sanitizeX($post['land_owner']); ?></td>
					</tr>
					
						<?php
						if($post['http_picture']){
							?>
							<tr>
							<td>Image</td>
							<td>
								<table>
								<tr>
								<td valign='middle'>
									<a href="<?php echo $post['http_picture']; ?>" target='_blank'><img src="images/image.php?p=<?php echo base64_encode($post['http_picture']); ?>&b=1&mx=50" /></a><br>
								</td>
								</tr>
								</table>
									</td>
							</tr>
							<?php
						}
						?>

					<tr>
						<td colspan=2 class='header'>
						<b>Payment Option</b>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<div style='text-align:center'>
							<script>
							function showP(idx){
								jQuery(".paymentform").hide();
								jQuery("#"+idx).show();
							}
							</script>
							<?php
							function curPageURL() {
								$pageURL = 'http';
								if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
								$pageURL .= "://";
								if ($_SERVER["SERVER_PORT"] != "80") {
									$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
								}
								else {
									$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
								}
								return $pageURL;
							}
							$imgurl = "http://pieceoftheworld.co/images/pastedgraphic.jpg";
							$imgurl = urlencode($imgurl);
							$sharetitle = "I just bought a Piece of the World!";
							$caption="www.pieceoftheworld.com";
							$sharetext = "Get yours today at www.pieceoftheworld.com";
							$link = "http://pieceoftheworld.co/";
							$return = $link."bidbuyland.php?type=".$_GET['type']."&f=".$foldername."&fromshare=1";
							
							$return = urlencode($return);
							$sharelink = "https://www.facebook.com/dialog/feed?app_id=454736247931357&link=".$link."&picture=".$imgurl."&name=".$sharetitle."&caption=".$caption."&description=".$sharetext."&redirect_uri=".$return;
							
							if($discount==0){
								?>
								<table width='100%' style='margin:10px;'>
								<tr>
								<td valign='top' align='left'>
									<a href='#' onclick="window.location='<?php echo $sharelink; ?>'"><img src='/images/fb_logo.png'></a>
								</td>
								<td valign='top' align='left'>
									Click on the facebook icon top post your new Piece of the World in your timeline and get a 10% discount.
								</td>
								</tr>
								</table>
								<?php
							}
							?>
							<!--<input type='radio' name='po' checked onclick='showP("paypal")'> Paypal&nbsp;&nbsp;&nbsp;
							<input type='radio' name='po' onclick='showP("skrill")'> Skrill (Bank Transfer)-->
							</div>
							<br /><br />
							<div id='paypal' style='padding:10px; text-align:center' class='paymentform'>
								<form name="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" onSubmit="return onSubmit();">
									<input type="hidden" value="_xclick" name="cmd">
									<input type="hidden" value="pieceoftheworld2013@gmail.com" name="business">
									<input type="hidden" name="notify_url" value="http://www.pieceoftheworld.co/ipn.php?f=<?php echo $foldername; ?>&affid=<?php echo $_SESSION['affid']; ?>">
									<input type="hidden" value="PieceofTheWorld.com Land" name="item_name">
									<input type="hidden" value="<?php echo $paypalvalue; ?>" name="amount" id="amount_id">
									<input type="hidden" value="http://www.pieceoftheworld.co/ppc2.php?f=<?php echo $foldername; ?>&step=1&pass=A631CD74-1D21-40b1-8602-346611127127&land=<?php echo @$_GET['land']; ?>&useremail=" name="return" id="paypal-return-url">
									<input type="hidden" value="http://www.pieceoftheworld.co/" name="cancel_return">
									<input type="hidden" value="USD" name="currency_code">
									<input type="hidden" value="US" name="lc">
									<input type='checkbox' id='tor1'> I accept PieceoftheWorld's all <a href="#" onClick="window.showModalDialog('tac.php',0, 'dialogWidth:600px; dialogHeight:400px; center:yes; resizable: no; status: no');">Terms and Conditions</a><br>
									<br>
									<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" onclick="if(!jQuery('#tor1').attr('checked')){ alert('Please check the Terms and Conditions before proceeding.'); return false; }">
									<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
								</form>
							</div>
							<div id='skrill' style='padding:10px; text-align:center; display:none' class='paymentform'>
								<form action="https://www.moneybookers.com/app/payment.pl" method="post" target="_blank">
								  <input type="hidden" name="pay_to_email" value="pieceoftheworld2013@gmail.com"/>
								  <input type="hidden" name="status_url" value="http://www.pieceoftheworld.co/ipn_skrill.php?f=<?php echo $foldername; ?>&affid=<?php echo $_SESSION['affid']; ?>"/> 
								  <input type="hidden" name="language" value="EN"/>
								  <input type="hidden" name="amount" value="<?php echo $paypalvalue; ?>"/>
								  <input type="hidden" name="currency" value="USD"/>
								  <input type="hidden" name="detail1_description" value="PieceofTheWorld.com Land"/>
								  <input type="hidden" name="detail1_text" value=""/>
								  <input type='checkbox' id='tor2'> I accept PieceoftheWorld's all <a href="#" onClick="window.showModalDialog('tac.php',0, 'dialogWidth:600px; dialogHeight:400px; center:yes; resizable: no; status: no');">Terms and Conditions</a><br>
								  <br />&nbsp;
								  <input type="submit" value="Pay Now!" onclick="if(!jQuery('#tor2').attr('checked')){ alert('Please check the Terms and Conditions before proceeding.'); return false; }" />
								</form>
							</div>
						</td>
					</tr>
				</table>
			</td>
			<td>
				<table cellpadding=3>
					<tr>
						<td class='header'>
							<b>Land Purchase Details</b>
						</td>
					</tr>
					<tr>
						<td>
						<div id='buydetails' style='padding:10px;'><?php echo $_SESSION['buydetails']; ?></div>
						</td>
					</tr>
					<tr>
						<td style='border-top:1px solid #f0f0f0; '>
						<div style='font-size:12px; font-weight:bold; padding:10px;'>
						Total: USD <?php echo number_format($paypalvalue,2); 
						
						if($discount){
							echo "<br /><i>".($discount*100)."% discount</i>";
						}
						?> 
						<!--<a id="facebookshare" href="https://www.facebook.com/dialog/feed?app_id=454736247931357&link=<?php /* echo "http://pieceoftheworld.co/"; */ echo urlencode($_SESSION['GET']['link']); ?>&picture=<?php if(trim($http_picture)){ echo $http_picture; } else { echo urlencode($_GET['thumb']); } /* echo urlencode("http://www.pieceoftheworld.co/images/pastedgraphic.jpg?_=".time()); */ ?>&name=I just bought a Piece of the World&caption=<?php if(trim($post['title_name'])) { echo $post['title_name']; } else { echo urlencode("Mark your very own Piece of the World!	"); } ?>&description=<?php if(trim($post['detail_name'])) { echo $post['detail_name']; } else { echo urldecode("I just bought myself a piece of the world. <br />Get yours at pieceoftheworld.com"); } ?>&redirect_uri=<?php echo urlencode($urlCurr1."&f=".$foldername."&thumb=".urldecode($_GET['thumb'])); ?>"><img id="facebookshareimg" src="images/fshare.png" border="0" valign="center" height="36"></a>-->
						</div>
						</td>
					</tr>
					<tr>
						<td align='center'>
							<img src='<?php echo $_SESSION['GET']['thumb']?>' />
						</td>
					</tr>
				</table>
			</td>
		
		</tr>
		<tr>
			<td colspan=3 align='center'><input type='button' value='Back' class='longbutton' onclick='self.location="bidbuyland.php?type=<?php echo $_GET['type']; ?>&f=<?php echo $foldername; ?>"' style='width:100%; '></td>
		</tr>
		</table>
	</td></tr></table>
	</center>
	<?php
}
else if(strtolower($_GET['type'])=='buy'){
	if($_GET['type']&&$_GET['thumb']&&$_GET['link']){
		$_SESSION["GET"] = $_GET;
	}
	/*
	echo "<pre>";
	//print_r($_GET);

	print_r($_SESSION);
	$coords = json_decode(stripslashes($_SESSION['coords']));
	print_r($coords);
	<td align="center"><a id="facebookshare" href="https://www.facebook.com/dialog/feed?app_id=418617858219868&link=http://www.pieceoftheworld.co/&picture=http://www.pieceoftheworld.co/images/pastedgraphic.jpg&name=PieceoftheWorld&caption=The%20best%20Valentine%27s%20gift%20of%202013&description=I%20just%20bought%20myself%20a%20piece%20of%20the%20world&redirect_uri=<?php echo urlencode($urlCurr1); ?>"><img id="facebookshareimg" src="images/fshare.png" border="0" valign="center" height="36"></a></td>
	<td><font size="3">Share on Facebook to get 30% discount.</font></td>

	Array
	(
		[px] => 49.5
		[coords] => {\"points\":[\"435913-179277\",\"435914-179277\",\"435915-179277\",\"435914-179278\",\"435913-179278\"],\"strlatlongs\":[\"61.03031988363268,69.10856386347723\",\"61.03031988363268,69.10913532742507\",\"61.03031988363268,69.10970679137279\",\"61.030043095736616,69.10913532742507\",\"61.030043095736616,69.10856386347723\"]}
		[GET] => Array
			(
				[type] => buy
				[thumb] => http://www.pieceoftheworld.co/images/place_holder_small.png?_=1
				[link] => http://www.pieceoftheworld.co/?latlong=61.03042900252349~69.10878896713257
			)

	)
	stdClass Object
	(
		[points] => Array
			(
				[0] => 435913-179277
				[1] => 435914-179277
				[2] => 435915-179277
				[3] => 435914-179278
				[4] => 435913-179278
			)

		[strlatlongs] => Array
			(
				[0] => 61.03031988363268,69.10856386347723
				[1] => 61.03031988363268,69.10913532742507
				[2] => 61.03031988363268,69.10970679137279
				[3] => 61.030043095736616,69.10913532742507
				[4] => 61.030043095736616,69.10856386347723
			)

	)
	*/
	?>
	<style>
	td{
		vertical-align:top;
	}
	input[type='text']{
		width:200px;
	}
	textarea{
		width:200px;
	}
	*{
		font-size:11px;
		font-family:verdana;
	}
	</style>
	<center>
	<br /><br />
	<form id="lf_fb" style='display:none'>
			<input type="hidden" name="fb_id" id="lf_fb_id" class="fb_id" />
			<input type="hidden" name="name" id="lf_name" class="name" />
			<input type="hidden" name="gender" id="lf_gender" class="gender" />
			<input type="hidden" name="location" id="lf_location" class="location" />
			<input type="hidden" name="email" id="lf_email"/>
	</form>
	<form enctype="multipart/form-data" method='post' action='bidbuyland.php?type=<?php echo $_GET['type']; ?>&f=<?php echo $_GET['f']; ?>' id='theform' style='margin:0px'>
	<table style='height:100%;'><tr><td style='height:100%; vertical-align:middle'>
		<table cellpadding=3>
		<?php
		if($_GET['error']){
			?>
			<tr class='account'>
				<td colspan=3 style='color:red; font-weight:bold'>
					<?php echo $_GET['error']; ?>.
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
			<td>
				<input type='hidden' name='step' value="1">
				<table cellpadding=5>		
					
					<?php
					/*
					[userdata] => Array
					(
						[useremail] => jairus@nmg.com.ph
						[id] => 56
					)
					*/
					if($_SESSION['userdata']['useremail']){
						?>
						<tr class='account'>
							<td colspan=2 class='header'>
							<b>Account</b><div class='hint'>Not your account? Click <a href='#' onclick='logout();'>here</a> to logout.</div>
							</td>
						</tr>
						<tr class='account'>
							<td>* E-mail</td>
							<td>
							<input type='hidden' name='email' value="<?php echo sanitizeX($_SESSION['userdata']['useremail']) ?>" >
							<input type='hidden' name='web_user_id' value="<?php echo sanitizeX($_SESSION['userdata']['id']) ?>" >
							<?php 
							echo $_SESSION['userdata']['useremail'];
							?>
							</td>
						</tr>
						<?php

					}
					else{
						?>
						<tr class='createaccount'>
							<td colspan=2 class='header'>
							<input type='hidden' name='register' value="1">
							<b>Create an account</b><div class='hint'>Already have an account? Click <a href='#' onclick='showLogin()'>here</a> to login.</div>
							</td>
						</tr>
						<tr class='createaccount'>
							<td>* E-mail</td>
							<td><input type='text' name='email' placeholder="e.g. john@email.com" value="<?php echo sanitizeX($post['email']) ?>" ></td>
						</tr>
						<tr class='createaccount'>
							<td>* Password</td>
							<td><input type='password' name='password' ></td>
						</tr>
						<tr class='createaccount'>
							<td>* Re-type Password</td>
							<td><input type='password' name='repassword'  ></td>
						</tr>
						<tr class='createaccount'>
							<td>* Name</td>
							<td><input type='text' name='name' value="<?php echo sanitizeX($post['name']) ?>" ></td>
						</tr>
						<tr class='createaccount'>
							<td>* Country</td>
							<td><select name="country">
								<option value="">Please select country</option>
								<option value="Andorra">Andorra</option>
								<option value="United Arab Emirates">United Arab Emirates</option>
								<option value="Afghanistan">Afghanistan</option>
								<option value="Antigua and Barbuda">Antigua and Barbuda</option>
								<option value="Anguilla">Anguilla</option>
								<option value="Albania">Albania</option>
								<option value="Armenia">Armenia</option>
								<option value="Angola">Angola</option>
								<option value="Antarctica">Antarctica</option>
								<option value="Argentina">Argentina</option>
								<option value="American Samoa">American Samoa</option>
								<option value="Austria">Austria</option>
								<option value="Australia">Australia</option>
								<option value="Aruba">Aruba</option>
								<option value="Åland Islands">Åland Islands</option>
								<option value="Azerbaijan">Azerbaijan</option>
								<option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
								<option value="Barbados">Barbados</option>
								<option value="Bangladesh">Bangladesh</option>
								<option value="Belgium">Belgium</option>
								<option value="Burkina Faso">Burkina Faso</option>
								<option value="Bulgaria">Bulgaria</option>
								<option value="Bahrain">Bahrain</option>
								<option value="Burundi">Burundi</option>
								<option value="Benin">Benin</option>
								<option value="Saint Barthélemy">Saint Barthélemy</option>
								<option value="Bermuda">Bermuda</option>
								<option value="Brunei Darussalam">Brunei Darussalam</option>
								<option value="Bolivia, Plurinational State of">Bolivia, Plurinational State of</option>
								<option value="Bonaire, Sint Eustatius and Saba">Bonaire, Sint Eustatius and Saba</option>
								<option value="Brazil">Brazil</option>
								<option value="Bahamas">Bahamas</option>
								<option value="Bhutan">Bhutan</option>
								<option value="Bouvet Island">Bouvet Island</option>
								<option value="Botswana">Botswana</option>
								<option value="Belarus">Belarus</option>
								<option value="Belize">Belize</option>
								<option value="Canada">Canada</option>
								<option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
								<option value="Congo, the Democratic Republic of the">Congo, the Democratic Republic of the</option>
								<option value="Central African Republic">Central African Republic</option>
								<option value="Congo">Congo</option>
								<option value="Switzerland">Switzerland</option>
								<option value="Côte d\'Ivoire">Côte d\'Ivoire</option>
								<option value="Cook Islands">Cook Islands</option>
								<option value="Chile">Chile</option>
								<option value="Cameroon">Cameroon</option>
								<option value="China">China</option>
								<option value="Colombia">Colombia</option>
								<option value="Costa Rica">Costa Rica</option>
								<option value="Cuba">Cuba</option>
								<option value="Cape Verde">Cape Verde</option>
								<option value="Curaçao">Curaçao</option>
								<option value="Christmas Island">Christmas Island</option>
								<option value="Cyprus">Cyprus</option>
								<option value="Czech Republic">Czech Republic</option>
								<option value="Germany">Germany</option>
								<option value="Djibouti">Djibouti</option>
								<option value="Denmark">Denmark</option>
								<option value="Dominica">Dominica</option>
								<option value="Dominican Republic">Dominican Republic</option>
								<option value="Algeria">Algeria</option>
								<option value="Ecuador">Ecuador</option>
								<option value="Estonia">Estonia</option>
								<option value="Egypt">Egypt</option>
								<option value="Western Sahara">Western Sahara</option>
								<option value="Eritrea">Eritrea</option>
								<option value="Spain">Spain</option>
								<option value="Ethiopia">Ethiopia</option>
								<option value="Finland">Finland</option>
								<option value="Fiji">Fiji</option>
								<option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
								<option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
								<option value="Faroe Islands">Faroe Islands</option>
								<option value="France">France</option>
								<option value="Gabon">Gabon</option>
								<option value="United Kingdom">United Kingdom</option>
								<option value="Grenada">Grenada</option>
								<option value="Georgia">Georgia</option>
								<option value="French Guiana">French Guiana</option>
								<option value="Guernsey">Guernsey</option>
								<option value="Ghana">Ghana</option>
								<option value="Gibraltar">Gibraltar</option>
								<option value="Greenland">Greenland</option>
								<option value="Gambia">Gambia</option>
								<option value="Guinea">Guinea</option>
								<option value="Guadeloupe">Guadeloupe</option>
								<option value="Equatorial Guinea">Equatorial Guinea</option>
								<option value="Greece">Greece</option>
								<option value="South Georgia and the South Sandwich Islands">South Georgia and the South Sandwich Islands</option>
								<option value="Guatemala">Guatemala</option>
								<option value="Guam">Guam</option>
								<option value="Guinea-Bissau">Guinea-Bissau</option>
								<option value="Guyana">Guyana</option>
								<option value="Hong Kong">Hong Kong</option>
								<option value="Heard Island and McDonald Islands">Heard Island and McDonald Islands</option>
								<option value="Honduras">Honduras</option>
								<option value="Croatia">Croatia</option>
								<option value="Haiti">Haiti</option>
								<option value="Hungary">Hungary</option>
								<option value="Indonesia">Indonesia</option>
								<option value="Ireland">Ireland</option>
								<option value="Israel">Israel</option>
								<option value="Isle of Man">Isle of Man</option>
								<option value="India">India</option>
								<option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
								<option value="Iraq">Iraq</option>
								<option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
								<option value="Iceland">Iceland</option>
								<option value="Italy">Italy</option>
								<option value="Jersey">Jersey</option>
								<option value="Jamaica">Jamaica</option>
								<option value="Jordan">Jordan</option>
								<option value="Japan">Japan</option>
								<option value="Kenya">Kenya</option>
								<option value="Kyrgyzstan">Kyrgyzstan</option>
								<option value="Cambodia">Cambodia</option>
								<option value="Kiribati">Kiribati</option>
								<option value="Comoros">Comoros</option>
								<option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
								<option value="North Korea">North Korea</option>
								<option value="South Korea">South Korea</option>
								<option value="Kuwait">Kuwait</option>
								<option value="Cayman Islands">Cayman Islands</option>
								<option value="Kazakhstan">Kazakhstan</option>
								<option value="Lao People\'s Democratic Republic">Lao People\'s Democratic Republic</option>
								<option value="Lebanon">Lebanon</option>
								<option value="Saint Lucia">Saint Lucia</option>
								<option value="Liechtenstein">Liechtenstein</option>
								<option value="Sri Lanka">Sri Lanka</option>
								<option value="Liberia">Liberia</option>
								<option value="Lesotho">Lesotho</option>
								<option value="Lithuania">Lithuania</option>
								<option value="Luxembourg">Luxembourg</option>
								<option value="Latvia">Latvia</option>
								<option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
								<option value="Morocco">Morocco</option>
								<option value="Monaco">Monaco</option>
								<option value="Moldova, Republic of">Moldova, Republic of</option>
								<option value="Montenegro">Montenegro</option>
								<option value="Saint Martin (French part)">Saint Martin (French part)</option>
								<option value="Madagascar">Madagascar</option>
								<option value="Marshall Islands">Marshall Islands</option>
								<option value="Macedonia, the former Yugoslav Republic of">Macedonia, the former Yugoslav Republic of</option>
								<option value="Mali">Mali</option>
								<option value="Myanmar">Myanmar</option>
								<option value="Mongolia">Mongolia</option>
								<option value="Macao">Macao</option>
								<option value="Northern Mariana Islands">Northern Mariana Islands</option>
								<option value="Martinique">Martinique</option>
								<option value="Mauritania">Mauritania</option>
								<option value="Montserrat">Montserrat</option>
								<option value="Malta">Malta</option>
								<option value="Mauritius">Mauritius</option>
								<option value="Maldives">Maldives</option>
								<option value="Malawi">Malawi</option>
								<option value="Mexico">Mexico</option>
								<option value="Malaysia">Malaysia</option>
								<option value="Mozambique">Mozambique</option>
								<option value="Namibia">Namibia</option>
								<option value="New Caledonia">New Caledonia</option>
								<option value="Niger">Niger</option>
								<option value="Norfolk Island">Norfolk Island</option>
								<option value="Nigeria">Nigeria</option>
								<option value="Nicaragua">Nicaragua</option>
								<option value="Netherlands">Netherlands</option>
								<option value="Norway">Norway</option>
								<option value="Nepal">Nepal</option>
								<option value="Nauru">Nauru</option>
								<option value="Niue">Niue</option>
								<option value="New Zealand">New Zealand</option>
								<option value="Oman">Oman</option>
								<option value="Panama">Panama</option>
								<option value="Peru">Peru</option>
								<option value="French Polynesia">French Polynesia</option>
								<option value="Papua New Guinea">Papua New Guinea</option>
								<option value="Philippines">Philippines</option>
								<option value="Pakistan">Pakistan</option>
								<option value="Poland">Poland</option>
								<option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
								<option value="Pitcairn">Pitcairn</option>
								<option value="Puerto Rico">Puerto Rico</option>
								<option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
								<option value="Portugal">Portugal</option>
								<option value="Palau">Palau</option>
								<option value="Paraguay">Paraguay</option>
								<option value="Qatar">Qatar</option>
								<option value="Réunion">Réunion</option>
								<option value="Romania">Romania</option>
								<option value="Serbia">Serbia</option>
								<option value="Russian Federation">Russian Federation</option>
								<option value="Rwanda">Rwanda</option>
								<option value="Saudi Arabia">Saudi Arabia</option>
								<option value="Solomon Islands">Solomon Islands</option>
								<option value="Seychelles">Seychelles</option>
								<option value="Sudan">Sudan</option>
								<option value="Sweden">Sweden</option>
								<option value="Singapore">Singapore</option>
								<option value="Saint Helena, Ascension and Tristan da Cunha">Saint Helena, Ascension and Tristan da Cunha</option>
								<option value="Slovenia">Slovenia</option>
								<option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
								<option value="Slovakia">Slovakia</option>
								<option value="Sierra Leone">Sierra Leone</option>
								<option value="San Marino">San Marino</option>
								<option value="Senegal">Senegal</option>
								<option value="Somalia">Somalia</option>
								<option value="Suriname">Suriname</option>
								<option value="Sao Tome and Principe">Sao Tome and Principe</option>
								<option value="El Salvador">El Salvador</option>
								<option value="Sint Maarten (Dutch part)">Sint Maarten (Dutch part)</option>
								<option value="Syrian Arab Republic">Syrian Arab Republic</option>
								<option value="Swaziland">Swaziland</option>
								<option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
								<option value="Chad">Chad</option>
								<option value="French Southern Territories">French Southern Territories</option>
								<option value="Togo">Togo</option>
								<option value="Thailand">Thailand</option>
								<option value="Tajikistan">Tajikistan</option>
								<option value="Tokelau">Tokelau</option>
								<option value="Timor-Leste">Timor-Leste</option>
								<option value="Turkmenistan">Turkmenistan</option>
								<option value="Tunisia">Tunisia</option>
								<option value="Tonga">Tonga</option>
								<option value="Turkey">Turkey</option>
								<option value="Trinidad and Tobago">Trinidad and Tobago</option>
								<option value="Tuvalu">Tuvalu</option>
								<option value="Taiwan">Taiwan</option>
								<option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
								<option value="Ukraine">Ukraine</option>
								<option value="Uganda">Uganda</option>
								<option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
								<option value="United States">United States</option>
								<option value="Uruguay">Uruguay</option>
								<option value="Uzbekistan">Uzbekistan</option>
								<option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
								<option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
								<option value="Venezuela, Bolivarian Republic of">Venezuela, Bolivarian Republic of</option>
								<option value="Virgin Islands, British">Virgin Islands, British</option>
								<option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
								<option value="Vietnam">Vietnam</option>
								<option value="Vanuatu">Vanuatu</option>
								<option value="Wallis and Futuna">Wallis and Futuna</option>
								<option value="Samoa">Samoa</option>
								<option value="Yemen">Yemen</option>
								<option value="Mayotte">Mayotte</option>
								<option value="South Africa">South Africa</option>
								<option value="Zambia">Zambia</option>
								<option value="Zimbabwe">Zimbabwe</option>
				        </select></td>
						</tr>
						<tr class='loginaccount' style='display:none'>
							<td colspan=2 class='header'><b>Account Login</b></td>
						</tr>
						<tr id='loginStatusTr' style='display:none'>
							<td id='loginStatus' align='center' colspan=2 style='color:red; font-weight:bold'></td>
						</tr>
						<tr class='loginaccount' style='display:none'>
							<td>* E-mail</td>
							<td><input type='text' id='lemail' placeholder="e.g. john@email.com" ></td>
						</tr>
						<tr class='loginaccount' style='display:none'>
							<td>* Password</td>
							<td><input type='password' id='lpassword' ></td>
						</tr>
						<tr class='loginaccount' style='display:none'>
							<td colspan=2 align='center'><input type='button' value='Login' onclick='login()'><input type='button' value='Cancel' onclick='cancelLogin()'></td>
						</tr>
						<tr>
							<td colspan=2 align='center'>
							-Or-<br /><br />
							<img src="images/facebook_signin_icon.png" id="fbloginbutton" width="150" height="22" border="0" style="cursor:pointer;" onClick="loginFb()" />
							</td>
						</tr>
						<?php
					}
					?>
					
					<tr>
						<td colspan=2 class='header'><b>Label your land</b></td>
					</tr>
					<tr>
						<td>* Name for your land</td>
						<td><input type='text' name='title' placeholder="e.g. The Eiffel Tower" value="<?php echo sanitizeX($post['title']) ?>"></td>
					</tr>
					<tr>
						<td>Description for your land</td>
						<td><textarea name='description' placeholder="e.g. Best place to go to in Paris France" ><?php echo $post['description']; ?></textarea></td>
					</tr>
					<tr>
						<td>Name of the land owner</td>
						<td><input type='text' name='land_owner' value="<?php echo sanitizeX($post['land_owner']) ?>"></td>
					</tr>
					<tr>
						<td>Image</td>
						<td>
						<?php
						if($post['http_picture']){
							?>
							<table>
							<tr>
							<td valign='middle'>
								<a href="<?php echo $post['http_picture']; ?>" target='_blank'><img src="images/image.php?p=<?php echo base64_encode($post['http_picture']); ?>&b=1&mx=50" /></a><br>
							</td>
							<td valign='middle'>
								<a href='bidbuyland.php?type=<?php echo $_GET['type']; ?>&f=<?php echo $_GET['f']; ?>&remove_image=1'>Remove Image</a>
							</td>
							</tr>
							</table>
							<?php
						}
						?>
						<input type='file' name='image'>
						
						</td>
					</tr>
					<tr>
						<td>Category</td>
						<td>
						<select name='categoryid'>
						<?php
						$sql = "select * from `categories` where `deleted`=0";
						$categories = dbQuery($sql, $_dblink);
						$t = count($categories);
						for($i=0; $i<$t; $i++){
							if($categories[$i]['id']==$post['categoryid']){
								echo "<option value='".$categories[$i]['id']."' selected>".$categories[$i]['name']."</option>";
							}
							else{
								echo "<option value='".$categories[$i]['id']."'>".$categories[$i]['name']."</option>";
							}
						}
						?>
						</select>
						
						</td>
					</tr>
					<tr>
						<td>Tags</td>
						<td>Tag your land to help others find it.<br />Separate each word with ","<br />
						<input id="tags_1" type="text" class="tags"  name='tags' value="<?php echo sanitizeX($post['tags']); ?>" style='height:40px;' />
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				</table>
			</td>
			<td>
				<table cellpadding=3>
					<tr>
						<td class='header'>
							<b>Land Purchase Details</b>
						</td>
					</tr>
					<tr>
						<td>
						<div id='buydetails' style='padding:10px;'><?php echo $_SESSION['buydetails']; ?></div>
						</td>
					</tr>
					<tr>
						<td style='border-top:1px solid #f0f0f0; '>
						<div style='font-size:12px; font-weight:bold; padding:10px;'>
						Total: USD <?php echo number_format($_SESSION['px'],2); ?> 
						<!--<a id="facebookshare" href="https://www.facebook.com/dialog/feed?app_id=454736247931357&link=<?php /* echo "http://pieceoftheworld.co/"; */ echo urlencode($_SESSION['GET']['link']); ?>&picture=<?php if(trim($http_picture)){ echo $http_picture; } else { echo urlencode($_GET['thumb']); } /* echo urlencode("http://www.pieceoftheworld.co/images/pastedgraphic.jpg?_=".time()); */ ?>&name=I just bought a Piece of the World&caption=<?php if(trim($post['title_name'])) { echo $post['title_name']; } else { echo urlencode("Mark your very own Piece of the World!	"); } ?>&description=<?php if(trim($post['detail_name'])) { echo $post['detail_name']; } else { echo urldecode("I just bought myself a piece of the world. <br />Get yours at pieceoftheworld.com"); } ?>&redirect_uri=<?php echo urlencode($urlCurr1."&f=".$foldername."&thumb=".urldecode($_GET['thumb'])); ?>"><img id="facebookshareimg" src="images/fshare.png" border="0" valign="center" height="36"></a>-->
						</div>
						</td>
					</tr>
					<tr>
						<td align='center'>
							<img src='<?php echo $_SESSION['GET']['thumb']?>' />
						</td>
					</tr>
				</table>
			</td>
		
		</tr>
		<tr>
			<td colspan=3 align='center'><input type='submit' id='continuebutton' class='longbutton' value="Proceed" style='width:100%;'></td>
		</tr>
		</table>
		</form>
	</td></tr></table>
	</center>
	<?php
}
else if(strtolower($_GET['type'])=='bid'){
	if($_GET['type']&&$_GET['thumb']&&$_GET['link']){
		$_SESSION["GET"] = $_GET;
	}

	if($_POST['register']){
		if(!checkEmail(trim($_POST['user_email']))){
			?> <script>self.location="?f=<?php echo $_GET['f']; ?>&error=Invalid E-mail address&type=bid";</script> <?php
			exit();
		}else if(!trim($_POST['user_bid'])){
			?> <script>self.location="?f=<?php echo $_GET['f']; ?>&error=Please enter your Bid&type=bid";</script> <?php
			exit();
		}else{
			$sql = "SELECT * FROM `land` WHERE `id`='".$_SESSION["GET"]['land_id']."' LIMIT 0,1";
			$land = dbQuery($sql, $_dblink);
			
			$sql = "SELECT * FROM `land_detail` WHERE `id`='".$land[0]['land_detail_id']."' LIMIT 0,1";
			$land_detail = dbQuery($sql, $_dblink);
			
			$land_name = 'Land';
			if($land_detail[0]['title']){
				$land_name = $land_detail[0]['title'];
			}
		
			$from = $_POST['user_email'];
			$fromname = $_POST['user_email'];
			$bouncereturn = $_POST['user_email'];
			$subject = "Bid for ".$land_name;
			$message = '<b>Land ID: </b>'.$_SESSION["GET"]['land_id'].'<br /><br /><b>User Bid: </b>'.$_POST['user_bid'].'<br /><br /><b>Message: </b>'.$_POST['user_message'];
			$emails[0]['email'] = "pieceoftheworld2013@gmail.com";
			$emails[0]['name'] = "PieceOfTheWorld.Co";
			$attachments[0] = "";
			$attachments[1] = "";
			emailBlast($from, $fromname, $subject, $message, $emails, $bouncereturn, $attachments,  1);
			
			$sql = "INSERT INTO `land_bids` (`bidder`, `bid`, `message`, `land_id`) VALUES ('".$_POST['user_email']."', '".$_POST['user_bid']."', '".$_POST['user_message']."', '".$_SESSION["GET"]['land_id']."')";
			dbQuery($sql, $_dblink);
			?>
			<script>self.location="?f=<?php echo $_GET['f']; ?>&success=You have successfully submitted your Bid&type=bid";</script>
			<?php
			
			exit();
		}
	}
	?>
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
	</script>
	<style>
	td{
		vertical-align:top;
	}
	input[type='text']{
		width:200px;
	}
	textarea{
		width:200px;
	}
	*{
		font-size:11px;
		font-family:verdana;
	}
	</style>
	<form enctype="multipart/form-data" method='post' action='bidbuyland.php?type=<?php echo $_GET['type']; ?>&f=<?php echo $_GET['f']; ?>' id='theform' name='theform' style='margin:0px'>
	<table width="350" border="0" cellspacing="0" cellpadding="0">
	  <?php if(isset($_GET['error'])){ ?>
	  <tr>
		<td colspan="2" style='color:red; font-weight:bold;'><?php echo $_GET['error']; ?></td>
	  </tr>
	  <tr>
		<td colspan="2">&nbsp;</td>
	  </tr>
	  <?php } ?>
	  
	  <?php if(isset($_GET['success'])){ ?>
	  <tr>
		<td colspan="2" style='color:#00FF00; font-weight:bold;'><?php echo $_GET['success']; ?></td>
	  </tr>
	  <tr>
		<td colspan="2">&nbsp;</td>
	  </tr>
	  <?php }else{ ?>
	  <tr>
		<td width="100">* Email</td>
		<td><input type='hidden' name='register' value="1"><input type='text' id='user_email' name='user_email' placeholder="e.g. john@email.com" /></td>
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
		<td>Message</td>
		<td><textarea id='user_message' name='user_message' placeholder="e.g. I would like to bid for this land"></textarea></td>
	  </tr>
	  <tr>
		<td colspan="2">&nbsp;</td>
	  </tr>
	  <tr>
		<td colspan="2"><input type='submit' id='submitbidbutton' class='longbutton' value="Submit your bid" style='width:100%;' /></td>
	  </tr>
	  <?php } ?>
	</table>
	</form>
	<?php
}
?>
<script type="text/javascript">
	function isset(v){
		if(typeof(v)!='undefined'){
			return true;
		}
		else{
			return false;
		}
	}
    var loggedIn = '<?php echo isset($_SESSION['userdata'])  ?>';
	// start of facebook functions
	var fbResponse = false;
	jQuery("#fbloginbutton").hide();
	window.fbAsyncInit = function() {
		jQuery("#fbloginbutton").show();
		consoleX(FB);
		FB.init({
			appId      : '454736247931357', // App ID
			channelUrl : '//pieceoftheworld.co/channel.html',
			status     : true, // check login status
			cookie     : true, // enable cookies to allow the server to access the session
			xfbml      : true  // parse XFBML
		});

		FB.Event.subscribe('auth.login', function(response) {
			if (response.status === 'connected') {
				fbResponse = response;
			} else { /* FB.login(); */ }
		});
		FB.Event.subscribe('edge.create', function(href, widget) {
			jQuery.ajax({
				 dataType: "json",
				 type: 'get',
				 data: {'href':  encodeURI(href) }, // from index.php line 2121
				 url: 'ajax/user_fxn.php?action=like',
				 success: function(data){
					console.log(data);
				 }
			});
		});
		FB.Event.subscribe('edge.remove', function(href, widget) {
			jQuery.ajax({
				dataType: "json",
				type: 'get',
				data: {'href':  encodeURI(href) }, // from index.php line 2121
				url: 'ajax/user_fxn.php?action=unlike',
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
	
	if(loggedIn){
		jQuery("#userProfile").show();
		jQuery("#lf_fb").hide();
	} else {
		jQuery("#userProfile").hide();
		jQuery("#lf_fb").show();
	}
	
	/****************************************************************************************************/
	function loginFb() {
		FB.login(function(response) {
				if (response.authResponse) {
					FB.api('/me', function(response) {
						// update fields in login, reg and facebook form in mainTabs.php
						jQuery("#lf_fb_id").val(response.id);
						jQuery("#lf_email").val(response.email);
						jQuery("#lf_name").val(response.name);
						jQuery("#lf_gender").val(response.gender);
						if(typeof(response.location) != 'undefined'){
							jQuery("#lf_fb_location").val(response.location.name);
						}
						console.log(response);
						loginUser(true);
					});
				}
				else {	
				}
			},{scope: 'email'}
		);
	}
	// end of facebook functions
	function loginUser(fb){
		if(!isset(fb)){
			jQuery("#lf_fb_id").val("");
			jQuery("#lf_name").val("");
			jQuery("#lf_gender").val("");
		}
		datax = jQuery('#lf_fb').serialize();
		//alert(jQuery('#lf_fb').html());
		//alert(datax);
		jQuery('#lf_fb *').attr("disabled", true);
		jQuery.ajax({
			dataType: "json",
			type: 'post',
			async: false,
			url: "ajax/user_fxn.php?action=login",
			data: datax,
			success: function(data){
				if(data.status){
					self.location = "bidbuyland.php?type=<?php echo $_GET['type']; ?>&f=<?php echo $_GET['f']; ?>&_="+(new Date()).getTime();
				} else {
					jQuery("#loginStatus").html(data.message);			
					jQuery("#loginStatusTr").show('slide');					
				}
				jQuery('#lf_fb *').attr("disabled", false);
			}
		});
	}
	
	function updateProfile(){
		jQuery.ajax({
			dataType: "html",
			type: 'post',
			async: false,
			url: "ajax/user_fxn.php?action=checklogin",
			success: function(data){
				data = JSON.parse(data);
				consoleX(data);
				//alert(data['id']);
				try{
					if(isset(data.content.id)){
						jQuery("#userProfile").show();
						jQuery("#lf_fb").hide();
						setProfile(data);
					}
					else{
						jQuery("#userProfile").hide();
						jQuery("#lf_fb").show();
						if(isset(FB)){
							consoleX("show fbloginbutton");
							jQuery("#fbloginbutton").show();
						}
					}
				}
				catch(e){
					jQuery("#userProfile").hide();
					jQuery("#lf_fb").show();
					consoleX(FB);
					if(isset(FB)){
						consoleX("show fbloginbutton");
						jQuery("#fbloginbutton").show();
					}
				}
			},
			error: function(){ alert(error);}
		});
	}
	function setProfile(data){
		if(isset(data.content.fb_id)){
			jQuery("#profile_image").html('<img src="http://graph.facebook.com/'+data.content.fb_id+'/picture" style="height:20px; width:20px;">&nbsp;');
			jQuery("#profile_image").show();
		}
		else{
			jQuery("#profile_image").hide();
		}
		
		if(isset(data.content.name)){
			jQuery("#profile_name").html(data.content.name);
		}
		else{
			jQuery("#profile_name").html(data.content.useremail);
		}
	}

	
	function nl2br(str) {
		return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ '<br />' +'$2');
	}
	window.parent.jQuery("#loading-button").hide();
	window.parent.jQuery('#buy-button').show();
</script>
</body>
</html>