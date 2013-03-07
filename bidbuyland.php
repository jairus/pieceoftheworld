<?php
session_start();
function microtime_float(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
?>
<!doctype html>
<html lang="us">
<head>
<meta charset="utf-8">
<title>PieceoftheWorld</title>
<link href="css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="js/jquery-1.8.3.min.js" type="text/javascript"></script>
<script src="js/jquery-ui-1.9.2.custom.min.js" type="text/javascript"></script>
<link href="css/main.css" rel="stylesheet">
<?php
$type = @$_GET['type'];
$plots = explode("_", @$_GET['land']);
$plots = array_unique($plots);

require_once 'ajax/global.php';
$conOptions = GetGlobalConnectionOptions();
$con = mysql_connect($conOptions['server'], $conOptions['username'], $conOptions['password']);
if (!$con) { die(''); }
mysql_select_db($conOptions['database'], $con);

$land_special_id = -1;
$owner_user_id = -1;
$owner_user_email = "";
$title = "";
$detail = "";

$plotsArray = array();

$unboughtStandardPlot = false;
$unboughtSpecialArea = false;
$boughtStandardPlot = false;
$boughtSpecialArea = false;

?>
<script type="text/javascript">
	var unboughtStandardPlot = false;
	var unboughtSpecialArea = false;
	var boughtStandardPlot = false;
	var boughtSpecialArea = false;
	var numberOfPlots = 0;
	var price = 0;
	var discountPercent = 30.00;
	function onLoad() {
		var user_email = getCookie("user_email");
		//document.getElementById('email').value = user_email;
		//document.getElementById('paypal-return-url').value += user_email;
		
		if (unboughtStandardPlot === true) {
			var amount = 9.90 * numberOfPlots;
			price = String(amount.toFixed(2));
		}
		else if (unboughtSpecialArea === true) {
			price = '499';
		}
		/*
		else if (boughtStandardPlot === true) {
		}
		else if (boughtSpecialArea === true) {
		}
		*/
		<?php
		if ($type != "bid") {
			?>
			document.getElementById('amount_id').value = price;
			document.getElementById('totalamount').value = price;
			<?php
		}	
		?>
		<?php
		if (isset($_GET["post_id"])) {
			?>
			document.getElementById('facebookshare').href = '#';
			document.getElementById('facebookshareimg').src = 'images/fsharedisabled.png';

			var discount = (price*discountPercent)/100.00;
			price = price - discount;
			document.getElementById('discount').value = discount.toFixed(2);
			document.getElementById('totalamount').value = price.toFixed(2);
			document.getElementById('amount_id').value = price.toFixed(2);
			<?php
		}
		if($_GET['land']=='526825-289180_526825-289180'){
			?>
			document.getElementById('amount_id').value = 1;
			document.getElementById('totalamount').value = 1;
			<?php
		}
		?>		
	}

	function onSubmit() {
		if (document.getElementById('email').value == "") {
			alert('Please provide a valid email address to proceed.');
			return false;
		}
		
		document.getElementById('paypal-return-url').value += document.getElementById('email').value;
		setTimeout('window.close()',5000);
		return true;
	}
	
	function getCookie(c_name) {
		var i,x,y,ARRcookies=document.cookie.split(";");
		for (i=0;i<ARRcookies.length;i++) {
			x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
			y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
			x=x.replace(/^\s+|\s+$/g,"");
			if (x==c_name) {
				if (y == null || y == "") {
					y = "";
				}
				return unescape(y);
			}
		}
		return "";
	}

	function setCookie(c_name,value,exdays) {
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=c_name + "=" + c_value;
	}
</script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-39101024-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>

<body style="cursor: auto; background-color: white;" onload="onLoad();">
	<?php
// find out the domain:
$domain = $_SERVER['HTTP_HOST'];
// find out the path to the current file:
$path = $_SERVER['SCRIPT_NAME'];
// find out the QueryString:
$queryString = $_SERVER['QUERY_STRING'];
// put it all together:
$urlCurr1 = "http://" . $domain . $path . "?" . $queryString;
 
// An alternative way is to use REQUEST_URI instead of both
// SCRIPT_NAME and QUERY_STRING, if you don't need them seperate:
$urlCurr2 = "http://" . $domain . $_SERVER['REQUEST_URI'];
?>
<?php
if ($type == "bid") {
	echo "<h1>Place your bid</h1>";
}
else {
	echo "<h1>Purchase land</h1>";
}
?>
<table border=0>
<tr>
<td valign="top" >

</td>
<td width="300" valign="top">
<?php
if (sizeof($plots) == 1) {
	$plotCo =  explode("-", $plots[0]);
	$sql = "SELECT * FROM land WHERE x=".$plotCo[0]." AND y=".$plotCo[1];
	$result = mysql_query($sql);
	$row = null;
	if ($result != null) {
		$row = mysql_fetch_array($result);
	}

	if ($row == null) {
		//mysql_close($con);
		// Unbought standard plot selected
		echo '<center><img  src="'.$_GET['thumb'].'" /></center>';
		echo "<ul><li><font color=black>Plot Id (".$plots[0].") (Standard Plot)</font></li></ul>";
		$unboughtStandardPlot = true;
		//die('<li><font color=red>Invalid input</font></li></ul></td></tr></table></body></html>');
	}
	else {
		
		
		
		if ($owner_user_id == -1) {
			$land_special_id = $row[3];
			$owner_user_id = $row[4];
			$title = $row[5];
			$detail = $row[6];
			echo "<h4>".$title." : ".$detail."</h4>";
			echo '<center><img src="'.$_GET['thumb'].'" /></center>';
			echo "<ul>";
		}
		if ($row[4] != 3) {
			if ($row[3] == 0) {
				$plotsArray[] = $row;
				echo '<center><img src="'.$_GET['thumb'].'" /></center>';
				echo "<ul><li><font color=black>Plot Id (".$plots[0].") (Standard Plot)</font></li></ul>";
				$boughtStandardPlot = true;
			}
			else {
				$sql = "SELECT * FROM land WHERE land_special_id=".$row[3];
				$result = mysql_query($sql);
				while ($row = mysql_fetch_array($result)) {
					$plotsArray[] = $row;
					echo "<li><font color=black>Plot Id (".$row[1]."-".$row[2].") (Special Area)</font></li>";
				}
				$boughtSpecialArea = true;
			}
		}
		else {
			if ($row[3] == 0) {
				$plotsArray[] = $row;
				echo '<center><img src="'.$_GET['thumb'].'" /></center>';
				echo "<ul><li>Plot Id (".$plots[0].") (Standard Plot)</li></ul>";
				$unboughtStandardPlot = true;
			}
			else {
				$sql = "SELECT * FROM land WHERE land_special_id=".$row[3];
				$result = mysql_query($sql);
				while ($row = mysql_fetch_array($result)) {
					$plotsArray[] = $row;
					echo "<li>Plot Id (".$row[1]."-".$row[2].") (Special Area)</li>";
				}
				$unboughtSpecialArea = true;
			}
		}
	}
	echo '<script type="text/javascript">numberOfPlots = 1;</script>';
}
else if (sizeof($plots) == 2) {
	$numberOfPlots = 0;
	$plotCoLT =  explode("-", $plots[0]);
	$plotCoRB =  explode("-", $plots[1]);
	$land_not_in_database = false;
	for ($i = $plotCoLT[0]; $i <= $plotCoRB[0]; $i++) {
		if ($land_not_in_database === true) { break; }
		for ($j = $plotCoLT[1]; $j <= $plotCoRB[1]; $j++) {
			$numberOfPlots++;
			$sql = "SELECT * FROM land WHERE x=".$i." AND y=".$j;
			$result = mysql_query($sql);
			$row = null;
			if ($result != null) {
				$row = mysql_fetch_array($result);
			}
			if ($row == null) {
				//mysql_close($con);
				$land_not_in_database = true;
				$unboughtStandardPlot = true;
				break;
				//die('<li><font color=red>Invalid input</font></li></ul></td></tr></table></body></html>');
			}
			if ($owner_user_id == -1) {
				$land_special_id = $row[3];
				$owner_user_id = $row[4];
				$title = $row[5];
				$detail = $row[6];
				
				if($_GET['print']){
					echo "<pre>";
					print_r($row);
					echo "</pre>";
				}
				echo "<h4>".$title." : ".$detail."</h4>";
				echo '<center><img src="'.$_GET['thumb'].'" /></center>';
				echo "<ul>";
			}
			$plotsArray[] = $row;
			if ($row[4] != 3) {
				if ($row[3] == 0) {
					echo "<li><font color=black>Plot Id (".$i."-".$j.") (Standard Plot)</font></li>";
					$boughtStandardPlot = true;
				}
				else {
					echo "<li><font color=black>Plot Id (".$i."-".$j.") (Special Area)</font></li>";
					$boughtSpecialArea = true;
				}
			}
			else {
				if ($row[4] == 3) {
					echo "<li>Plot Id (".$i."-".$j.") (Special Area)</li>";
					$unboughtSpecialArea = true;
				}
				else {
					echo "<li>Plot Id (".$i."-".$j.") (Standard Plot)</li>";
					$unboughtStandardPlot = true;
				}
			}
		}
	}
	if ($land_not_in_database === true) { 
		$numberOfPlots = 0;
		echo "<ul>";
		for ($i = $plotCoLT[0]; $i <= $plotCoRB[0]; $i++) {
			for ($j = $plotCoLT[1]; $j <= $plotCoRB[1]; $j++) {
				$numberOfPlots++;
				echo "<li><font color=black>Plot Id (".$i."-".$j.") (Standard Plot)</font></li>";
			}
		}
		echo "</ul>";
	}
	echo '<script type="text/javascript">numberOfPlots = '.$numberOfPlots.';</script>';
}
else {
	echo '<script type="text/javascript">numberOfPlots = 0;</script>';
	echo "<li><font color=red>Unexpected input found.</font></li>";
}

//echo $unboughtStandardPlot ." - ". $unboughtSpecialArea ." - ". $boughtStandardPlot ." - ". $boughtSpecialArea;

$mixedArea = 
			($unboughtStandardPlot && $unboughtSpecialArea) ||
			($unboughtStandardPlot && $boughtStandardPlot) ||
			($unboughtStandardPlot && $boughtSpecialArea) ||
			($unboughtSpecialArea && $boughtStandardPlot) ||
			($unboughtSpecialArea && $boughtSpecialArea) ||
			($boughtStandardPlot && $boughtSpecialArea);

$sql = "SELECT * FROM user WHERE id=".$owner_user_id;
$result = mysql_query($sql);
$row = null;
if ($result != null) {
	$row = mysql_fetch_array($result);
}
if ($row != null) {
	$owner_user_email = $row[3];
}

if ($con != null) {
	mysql_close($con);
}
?>	
</ul>
<?php
if ($mixedArea) {
	echo "<h4><font color=red>ERROR: Mixed Area can not be purchased.</font></h4>";
}
else if ($boughtStandardPlot || $boughtSpecialArea) {
	echo "<h4><font color=black>Note: This area has already been purchased but you can still bid on this area.</font></h4>";
}
?>
</td>
<td width="50%" valign="top">
<?php
echo '<script type="text/javascript">';
$valueTmp =($unboughtStandardPlot === true) ? 'true' : 'false';
echo 'unboughtStandardPlot='.$valueTmp.';';
$valueTmp =($unboughtSpecialArea === true) ? 'true' : 'false';
echo 'unboughtSpecialArea='.$valueTmp.';';
$valueTmp =($boughtStandardPlot === true) ? 'true' : 'false';
echo 'boughtStandardPlot='.$valueTmp.';';
$valueTmp =($boughtSpecialArea === true) ? 'true' : 'false';
echo 'boughtSpecialArea='.$valueTmp.';';
echo '</script>';
if ($type == "bid") {
	?>
	<h4>Fill this form to place your bid:</h4>

	<form action="sendmail.php" method="POST">
	<table>
	<tr><td>Name</td><td><input type="text" name="name"></td></tr>
	<tr><td>Email</td><td><input type="text" id="email"></td></tr>
	<tr><td>Message</td><td><textarea name="message" rows="6" cols="25"><?php
	echo "Placing a bid for the following plots:\n\n";
	$count = count($plotsArray);
	for ($i = 0; $i < $count; $i++) {
		echo $plotsArray[$i][1]."-".$plotsArray[$i][2]."\n";
	}
	?>
	</textarea></td></tr>
	<tr><td></td><td><input type="submit" value="Send"><input type="reset" value="Clear"><input type="hidden" name="owner_user_email" value="'.$owner_user_email.'"></td></tr>
	</form>
	<?php
}
else {
	
	if(!$_POST['save']&&!$_GET["post_id"]){
		
		echo '<center><h4>Set a title, text and picture to be shown at your very own Piece of the World</h4></center>';
		if($_GET['error']){
			echo '<center><h4>Please complete fields with *</h4></center>';
		}
		?>
		<form action="?type=buy&land=<?php echo $_GET['land']; ?>" method="post" enctype="multipart/form-data">
			<input type="hidden" value="1" name="save">
			<input type="hidden" value="1" name="step">
			<input type="hidden" value="A631CD74-1D21-40b1-8602-346611127127" name="pass">
			<input type="hidden" value="<?php echo @$_GET['land']; ?>" name="land">
			<input type="hidden" value="2" name="step">
			<table>
				<tbody><tr>
					<td><strong>Email<font color="red">*</font>&nbsp;</strong></td>
					<td colspan="2"><input type="text" style="width: 100%;" maxlength="50" name="useremail" value="<?php echo htmlentities($_SESSION['POST']['useremail']); ?>" ></td>
				</tr>
				<tr>
					<td><strong>Title<font color="red">*</font>&nbsp;</strong></td>
					<td colspan="2"><input type="text" style="width: 100%;" maxlength="50" name="title_name" id="title" value="<?php echo htmlentities($_SESSION['POST']['title_name']); ?>"></td>
				</tr>
				<tr>
					<td><strong>Land Owner<font color="red"></font>&nbsp;</strong></td>
					<td colspan="2"><input type="text" style="width: 100%;" maxlength="50" name="land_owner" id="title" value="<?php echo htmlentities($_SESSION['POST']['land_owner']); ?>"></td>
				</tr>
				<tr>
					<td style="vertical-align:top;"><strong>Text&nbsp;</strong></td>
					<td colspan="2"><textarea style="width: 100%; height:75px;" maxlength="160" name="detail_name" id="detail"><?php echo htmlentities($_SESSION['POST']['detail_name']); ?></textarea></td>
				</tr>
				<tr>
					<td><strong>Picture</strong></td>
					<td><input type="file" style="width: 100%;" name="picture_name" id="picture"></td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td align="right" colspan="2"><br><input type="submit" value="  Submit  " name="button_name" id="button"></td>
				</tr>
			</tbody></table>
		</form>
	<?php
	}
	else{
		if($_POST){
			$_SESSION['POST'] = $_POST;
			if(!trim($_POST['useremail'])||!trim($_POST['title_name'])){
				?>
				<script>
					self.location="?error=1&type=buy&land=<?php echo $_GET['land']; ?>";
				</script>
				<?php
				exit();
			}
			$foldername = date("Ymd")."_".microtime_float();
			
			$uploads_dir = dirname(__FILE__).'/_uploads/'.$foldername;
			mkdir($uploads_dir, 0777);
			$filename = $uploads_dir."/post.txt";
			$post = $_POST;
			
			if ($_FILES["picture_name"]["error"] == UPLOAD_ERR_OK) {
				$tmp_name = $_FILES["picture_name"]["tmp_name"];
				$name = $_FILES["picture_name"]["name"];
				move_uploaded_file($tmp_name, "$uploads_dir/$name");
				$post['filename'] = "$uploads_dir/$name";
			}
			file_put_contents ( $filename, serialize($post));
		}
		else if($_GET['f']){
			$foldername  = $_GET['f'];
		}
	
		?>
		<table align="center" border=0 valign=top width="300">
		<tr valign=top>
			<td valign=top>
				<table border=0 align="center">
					<tr>
						<td align="center"><a id="facebookshare" href="https://www.facebook.com/dialog/feed?app_id=454736247931357&link=http://www.pieceoftheworld.co/&picture=http://www.pieceoftheworld.co/images/pastedgraphic.jpg&name=PieceoftheWorld&caption=<?php echo urlencode("Mark your very own Piece of the World!	"); ?>&description=<?php echo urldecode("I just bought myself a piece of the world. <br />Get yours at pieceoftheworld.com"); ?>&redirect_uri=<?php echo urlencode($urlCurr1."&f=".$foldername."&thumb=".urldecode($_GET['thumb'])); ?>"><img id="facebookshareimg" src="images/fshare.png" border="0" valign="center" height="36"></a></td>
						<td><font size="3">Click on this Facebook icon to share PieceoftheWorld.co and get a 30% discount</font></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr><td>
		<table align="center" border=0 valign=top>
		<tr>
		<td>Email: </td><td><input type="text" id="email" name="email_name" value="<?php echo $_POST['useremail']; ?>" size="20"></td>
		</tr>
		<tr>
		<td>Discount: USD </td><td><input type="text" id="discount" name="discount_name" value="0.0" size="4" readonly></td>
		</tr>
		<tr>
		<td>Total amount: USD </td><td><input type="text" id="totalamount" name="totalamount_name" value="0.0" size="4" readonly></td>
		</tr>
		</table>
		</td></tr>
		<tr><td align="center">

		<form name="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" onsubmit="return onSubmit();">
			<input type="hidden" value="_xclick" name="cmd">
			<input type="hidden" value="pieceoftheworld2013@gmail.com" name="business">
			<input type="hidden" name="notify_url" value="http://www.pieceoftheworld.co/ipn.php?f=<?php echo $foldername; ?>">
			<input type="hidden" value="Land" name="item_name">
			<input type="hidden" value="0" name="amount" id="amount_id">
			<input type="hidden" value="http://www.pieceoftheworld.co/ppc2.php?f=<?php echo $foldername; ?>&step=1&pass=A631CD74-1D21-40b1-8602-346611127127&land=<?php echo @$_GET['land']; ?>&useremail=" name="return" id="paypal-return-url">
			<input type="hidden" value="http://www.pieceoftheworld.co/" name="cancel_return">
			<input type="hidden" value="USD" name="currency_code">
			<input type="hidden" value="US" name="lc">
			Upon pressing Buy Now button I accept<br>
			PieceoftheWorld's all <a href="#" onclick="window.showModalDialog('tac.php',0, 'dialogWidth:600px; dialogHeight:400px; center:yes; resizable: no; status: no');">terms and conditions</a><br>
			<br>
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		<!--
		<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_blank" onsubmit="return onSubmit();">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="bilalb_1359948554_biz@gmail.com">
		<input type="hidden" name="lc" value="US">
		<input type="hidden" value="Land" name="item_name">
		<input type="hidden" value="0" name="amount" id="amount_id">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="button_subtype" value="services">
		<input type="hidden" name="no_note" value="1">
		<input type="hidden" name="no_shipping" value="1">
		<input type="hidden" name="rm" value="1">
		<input type="hidden" value="http://www.pieceoftheworld.co/ppc.php?step=1&pass=A631CD74-1D21-40b1-8602-346611127127&land=<?php echo @$_GET['land']; ?>&useremail=" name="return" id="paypal-return-url">
		<input type="hidden" name="cancel_return" value="http://www.pieceoftheworld.co">
		<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHosted">
		Upon pressing Buy Now button I accept<br>
		PieceoftheWorld's all <a href="#" onclick="window.showModalDialog('tac.php',0, 'dialogWidth:600px; dialogHeight:400px; center:yes; resizable: no; status: no');">terms and conditions</a><br>
		<br>
		<input type="image" src="https://www.sandbox.paypal.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		-->
		</td></tr>
		</table>
		<?php
	}
}
?>
</td>
</tr>
</table>
</body>
</html>
