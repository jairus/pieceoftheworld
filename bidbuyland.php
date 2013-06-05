<?php
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
function checkEmail($email, $mx=false) {
    if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/" , $email))
    {
        list($username,$domain)=explode('@',$email);
        if($mx){
			if(!getmxrr ($domain,$mxhosts)) {
				return false;
			}
		}
        return true;
    }
    return false;
}
if($_POST['step']=="1"){
	$_SESSION['POST'] = $_POST;
	if(!$_GET['f']){
		$foldername = date("Ymd")."_".microtime_float();
		$post = $_POST;
	}
	else{
		$foldername = $_GET['f'];		$foldername = $_GET['f'];
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
		else if(!trim($_POST['email'])||!trim($_POST['title'])||!trim($_POST['description'])){
			?>
			<script>
				//alert("?error=1&type=buy&f=<?php echo $foldername; ?>");
				self.location="?f=<?php echo $foldername; ?>&error=Please complete all fields with *&type=buy&f=<?php echo $foldername; ?>";
			</script>
			<?php
			exit();
		}
	}
	else if(!trim($_POST['email'])||!trim($_POST['title'])||!trim($_POST['description'])){
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
</script>
<body style="cursor: auto;">
<?php
if($_POST['step']==1){
	$discount = 0;
	$paypalvalue = $_SESSION['px'];
	$paypalvalue = $paypalvalue - ($paypalvalue * $discount);
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
						<td>* Description for your land</td>
						<td><?php echo sanitizeX($post['description']); ?></td>
					</tr>
					<tr>
						<td>Name of the land owner</td>
						<td><?php echo sanitizeX($post['land_owner']); ?></td>
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
							</tr>
							</table>
							<?php
						}
						?>
						
						</td>
					</tr>
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
							<input type='radio' name='po' checked onclick='showP("paypal")'> Paypal&nbsp;&nbsp;&nbsp;
							<input type='radio' name='po' onclick='showP("skrill")'> Skrill (Bank Transfer)
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
									Upon pressing Buy Now button I accept PieceoftheWorld's all <a href="#" onClick="window.showModalDialog('tac.php',0, 'dialogWidth:600px; dialogHeight:400px; center:yes; resizable: no; status: no');">terms and conditions</a><br>
									<br>
									<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
									<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
								</form>
							</div>
							<div id='skrill' style='padding:10px; text-align:center' class='paymentform'>
								<form action="https://www.moneybookers.com/app/payment.pl" method="post" target="_blank">
								  <input type="hidden" name="pay_to_email" value="pieceoftheworld2013@gmail.com"/>
								  <input type="hidden" name="status_url" value="http://www.pieceoftheworld.co/ipn_skrill.php?f=<?php echo $foldername; ?>&affid=<?php echo $_SESSION['affid']; ?>"/> 
								  <input type="hidden" name="language" value="EN"/>
								  <input type="hidden" name="amount" value="<?php echo $paypalvalue; ?>"/>
								  <input type="hidden" name="currency" value="USD"/>
								  <input type="hidden" name="detail1_description" value="PieceofTheWorld.com Land"/>
								  <input type="hidden" name="detail1_text" value=""/>
								  <input type="submit" value="Pay Now!"/>
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
				<table cellpadding=10>		
					
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
						<td>* Description for your land</td>
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
	<form enctype="multipart/form-data" method='post' action='bidbuyland.php?type=<?php echo $_GET['type']; ?>&f=<?php echo $_GET['f']; ?>' id='theform' style='margin:0px'>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td width="150">* Email</td>
		<td><input type='text' id='user_email' placeholder="e.g. john@email.com" /></td>
	  </tr>
	  <tr>
		<td colspan="2">&nbsp;</td>
	  </tr>
	  <tr>
		<td width="150">* Bid (<i>USD</i>)</td>
		<td><input type='text' id='user_bid' placeholder="e.g. 500.00" style="width:80px;" onBlur="this.value=fNum(this.value);" /></td>
	  </tr>
	  <tr>
		<td colspan="2">&nbsp;</td>
	  </tr>
	  <tr>
		<td>* Message</td>
		<td><textarea name='description' placeholder="e.g. I would like to bid for this land"></textarea></td>
	  </tr>
	  <tr>
		<td colspan="2">&nbsp;</td>
	  </tr>
	  <tr>
		<td colspan="2"><input type='submit' id='submitbidbutton' class='longbutton' value="Submit your bid" style='width:100%;'></td>
	  </tr>
	</table>
	</form>
	<?php
}
?>
</body>
</html>