<?php
@session_start();

if($_GET['action']=='updateemail'){
	require_once 'global.php';
	function isUnique2($useremail){
		$sql = "select id from web_users where `useremail` = '".mysql_real_escape_string($useremail)."' and `id`<>'".$_SESSION['userdata']['id']."' limit 1 ";
		$rs = dbQuery($sql);
		return (empty($rs))? true : false;	
	}
	header('Content-type: text/json');
	header('Content-type: application/json');
	$res = array();
	$email = $_POST['email'];
	if(!checkEmail($email)){
		$res['error'] = 'Invalid E-mail Address.';
	}
	else if(!isUnique2($email)){
		$res['error'] = 'Invalid E-mail Address. The E-mail is already registered to an another account.';
	}
	else{
		$sql = "update `web_users` set `useremail`='".mysql_real_escape_string($email)."' where `id`='".$_SESSION['userdata']['id']."'";
		dbQuery($sql, $_dblink);
		$_SESSION['userdata']['useremail'] = $email;
		$res['useremail'] = $email;
	}
	echo json_encode($res);
	exit();
}
require_once('user_fxn.php');

header('Content-Type: text/html; charset=utf-8');

$webUserId = $_SESSION['userdata']['id'];


if($_POST['type']){ //saving on edit of land
	if($_POST['type']=='landdetail'){
		$sql = "update `land_detail` set
			`title` = '".mysql_real_escape_string($_POST['title'])."',
			`detail` = '".mysql_real_escape_string($_POST['detail'])."',
			`land_owner` = '".mysql_real_escape_string($_POST['land_owner'])."'
			where 
			`id` = '".mysql_real_escape_string($_POST['id'])."'
		";
		dbQuery($sql, $_dblink);
	}
	else{
		$sql = "update `land_special` set
			`title` = '".mysql_real_escape_string($_POST['title'])."',
			`detail` = '".mysql_real_escape_string($_POST['detail'])."',
			`land_owner` = '".mysql_real_escape_string($_POST['land_owner'])."'
			where 
			`id` = '".mysql_real_escape_string($_POST['id'])."'
		";
		dbQuery($sql, $_dblink);
	}
	
	exit();
}
$rs = getLands($webUserId);

if(empty($rs['land_detail']) && empty($rs['land_special']) ){
	?>
	<table width="100%">
		<tbody><tr>
			<td style="color:#000000; font-family:Arial; font-size:14px; font-weight:bold">
			<?php
			echo "<center>You currently dont own any virtual land.</center>";
			?>
			<div><img src="images/intro_interscape.jpg"></div>
			</td>
		</tr>
		</tbody></table>

	<?php
} 
else {
	//echo "<pre>";
	//print_r($rs['land_detail']);
	/*
	Array
	(
		[0] => Array
			(
				[id] => 987
				[title] => manila with my love ones
				[detail] => manila with my love ones
				[category_id] => 
				[land] => Array
					(
						[0] => Array
							(
								[x] => 526669
								[y] => 289188
							)

						[1] => Array
							(
								[x] => 526669
								[y] => 289189
							)

						[2] => Array
							(
								[x] => 526669
								[y] => 289190
							)
			 [pictures] => Array
                (
                    [0] => Array
                        (
                            [id] => 191
                            [created_on] => 2013-05-21 10:06:54
                            [land_id] => 987
                            [title] => 
                            [picture] => http%3A//www.pieceoftheworld.co/_uploads2/land/8867/images/camille.jpg
                            [isMain] => 1
                        )

                )
	*/
	//echo "<pre>";
	//print_r($rs['land_detail']);
	$ldt = count($rs['land_detail']);
	$lst = count($rs['land_special']);
	for($i=0; $i<$ldt; $i++){
		$prefix1 = "landdetail_";
		$prefix2 = "land_detail";
		?>
		<div id='<?php echo $prefix1; ?><?php echo $rs[$prefix2][$i]['id']; ?>' style='display:none'>
		<?php
		$imgdir = $rs[$prefix2][$i]['pictures'][0]['picture'];
		if(!trim($imgdir)){
			$imgdir = dirname(__FILE__)."/../images/place_holder_default.jpg";
			$imgurl = "http://pieceoftheworld.co/image.php?abs=".base64_encode($imgdir)."&w=272&h=140";
		}
		else{
			$imgurl = "http://pieceoftheworld.co/image.php?dir=".base64_encode($imgdir)."&w=272&h=140";
		}
		
		//teleportid
		$teleportid = $prefix1.$rs[$prefix2][$i]['id']."_".$rs[$prefix2][$i]['land'][0]['x']."_".$rs[$prefix2][$i]['land'][0]['y'];
		
		$imgurl_d = $imgurl;
		$imgurl = urlencode($imgurl);

		$x1 = $rs[$prefix2][$i]['land'][0]['x'];
		$y1 = $rs[$prefix2][$i]['land'][0]['y'];
		$sharetitle = $rs[$prefix2][$i]['title'];
		$sharetext = $rs[$prefix2][$i]['detail'];
		if(!$sharetitle){
			$sharetitle = "Mark your very own Piece of the World!";
		}
		if(!$sharetext){
			$sharetext = "Get your own piece of the world at pieceoftheworld.com";
		}
		$link = "http://pieceoftheworld.com/?xy=".$x1."~".$y1;
		$sharelink = "https://www.facebook.com/dialog/feed?app_id=454736247931357&link=".$link."&picture=".$imgurl."&name=Piece of the World&caption=".$sharetitle."&description=".$sharetext."&redirect_uri=".$link;
		$likehtml = '<iframe src="//www.facebook.com/plugins/like.php?href='.$link.'&amp;send=false&amp;layout=button_count&amp;width=30&amp;show_faces=false&amp;font=arial&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=454736247931357" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:21px; width:75px;" allowTransparency="true"></iframe>'; 

		if(trim($imgdir)){
			?>
			<div align="center">
				<div class="img" style='padding-top:7px; padding-bottom:7px;'>
					<a title="" class="xcboxElement">
					<img src='<?php echo $imgurl_d; ?>' onclick="landDetails('<?php echo $teleportid?>')" style='cursor:pointer' />
					</a>
				</div>
			</div>
			<?php
		}
		?>
		<table width="272" cellspacing="0" cellpadding="0" border="0">
		  <tbody><tr class='details'>
			<td width="150px"><a style="display: inline;" href="<?php echo $sharelink; ?>"><img border="0" height="15" style="cursor:pointer;" src="images/facebook_icon.png">&nbsp;Share this location</a></td>
			<td style="display: table-cell;"><?php echo $likehtml; ?></td>
			<td></td>
		  </tr>
		  <tr class='details'>
			<td height="5" colspan="3"></td>
		  </tr>
		  <tr class='details'>
			<td colspan="3" class="text_1">
				<span><?php echo $rs[$prefix2][$i]['detail']; ?></span>
			</td>
		  </tr>
		  <tr class='details'>
			<td colspan="3" height="5"></td>
		  </tr>
		  <tr class='details'>
			<td colspan="3" class="text_1" align="right">
			<?php
			if($rs[$prefix2][$i]['land_owner']){
				?>
				<div>Owner: <?php echo $rs[$prefix2][$i]['land_owner']; ?></div></td>
				<?php
			}
			?>
			
		  </tr>
		  <!--
		  <tr>
			<td colspan="3" class="text_1" align="right"><div>Highest Bid: </div></td>
		  </tr>
		  -->
		  <tr class='details'>
			<td colspan="3" height="10"></td>
		  </tr>
		  <tr  class="editor" style='display:none' >
			<td colspan=3 class='text_1'>
				<form id='<?php echo $prefix1; ?>form_<?php echo $rs[$prefix2][$i]['id']; ?>' style='margin:0px;'>
					<input type='hidden' name='type' value='landdetail' />
					<input type='hidden' name='id' value='<?php echo $rs[$prefix2][$i]['id']; ?>' />
					Title:<br />
					<input style='width:285px; color:black;' class='text_1' name='title' type='text' value="<?php echo htmlentities(utf8_decode(stripslashes($rs[$prefix2][$i]['title']))); ?>" /><br />
					Details:<br />
					<textarea style='width:285px; color:black; height:40px;' class='text_1' name='detail'><?php echo htmlentities(utf8_decode(stripslashes($rs[$prefix2][$i]['detail']))); ?></textarea><br />
					Land Owner:<br />
					<input style='width:285px; color:black;' class='text_1' name='land_owner' type='text' value="<?php echo htmlentities(utf8_decode(stripslashes($rs[$prefix2][$i]['land_owner']))); ?>" /><br /><br />
					<input class='longbutton' style="display: block;" type='button' value="Save" onclick='saveLandDetails("<?php echo $prefix1; ?>form_<?php echo $rs[$prefix2][$i]['id']; ?>", "<?php echo $prefix1.$rs[$prefix2][$i]['id']; ?>_<?php echo $x1; ?>_<?php echo $y1; ?>")' />
				</form>
				<input class='longbutton' style="display: block;" type='button' value="Back" onclick='jQuery(".editor").hide(); jQuery(".details").show();  jQuery(".editbuttons").show();' />
			</td>
		  </tr>
		  <tr class='editbuttons'>
			<td colspan="3" class="text_1" align='center'>
				<input class='longbutton' style="display: block;" type='button' value="Edit" onclick='jQuery(".editor").show(); jQuery(".details").hide();  jQuery(".editbuttons").hide();' />
				<input class='longbutton' style="display: block;" type='button' value="Manage Images" onclick='manageAssets("<?php echo htmlentities($rs[$prefix2][$i]['id']);?>", "<?php echo $prefix2; ?>", "images", "<?php echo $prefix1.$rs[$prefix2][$i]['id']; ?>_<?php echo $x1; ?>_<?php echo $y1; ?>");' />
				<input class='longbutton' style="display: block;" type='button' value="Manage Videos" onclick='manageAssets("<?php echo htmlentities($rs[$prefix2][$i]['id']);?>", "<?php echo $prefix2; ?>", "videos", "<?php echo $prefix1.$rs[$prefix2][$i]['id']; ?>_<?php echo $x1; ?>_<?php echo $y1; ?>");' />
			</td>
		  </tr>
		  <tr>
			<td colspan="3" height="10"></td>
		  </tr>
		</tbody>
		</table>
		</div>
		<?php
	}
	
	for($i=0; $i<$lst; $i++){
		$prefix1 = "landspecial_";
		$prefix2 = "land_special";
		?>
		<div id='<?php echo $prefix1; ?><?php echo $rs[$prefix2][$i]['id']; ?>' style='display:none'>
		<?php
		$imgdir = $rs[$prefix2][$i]['pictures'][0]['picture'];
		if(!trim($imgdir)){
			$imgdir = dirname(__FILE__)."/../images/place_holder_default.jpg";
			$imgurl = "http://pieceoftheworld.co/image.php?abs=".base64_encode($imgdir)."&w=272&h=140";
		}
		else{
			$imgurl = "http://pieceoftheworld.co/image.php?dir=".base64_encode($imgdir)."&w=272&h=140";
		}
		
		//teleportid
		$teleportid = $prefix1.$rs[$prefix2][$i]['id']."_".$rs[$prefix2][$i]['land'][0]['x']."_".$rs[$prefix2][$i]['land'][0]['y'];

		$imgurl_d = $imgurl;
		$imgurl = urlencode($imgurl);

		$x1 = $rs[$prefix2][$i]['land'][0]['x'];
		$y1 = $rs[$prefix2][$i]['land'][0]['y'];
		$sharetitle = $rs[$prefix2][$i]['title'];
		$sharetext = $rs[$prefix2][$i]['detail'];
		if(!$sharetitle){
			$sharetitle = "Mark your very own Piece of the World!";
		}
		if(!$sharetext){
			$sharetext = "Get your own piece of the world at pieceoftheworld.com";
		}
		$link = "http://pieceoftheworld.com/?xy=".$x1."~".$y1;
		$sharelink = "https://www.facebook.com/dialog/feed?app_id=454736247931357&link=".$link."&picture=".$imgurl."&name=Piece of the World&caption=".$sharetitle."&description=".$sharetext."&redirect_uri=".$link;
		$likehtml = '<iframe src="//www.facebook.com/plugins/like.php?href='.$link.'&amp;send=false&amp;layout=button_count&amp;width=30&amp;show_faces=false&amp;font=arial&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=454736247931357" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:21px; width:75px;" allowTransparency="true"></iframe>'; 

		if(trim($imgdir)){
			?>
			<div align="center">
				<div class="img" style='padding-top:7px; padding-bottom:7px;'>
					<a title="" class="xcboxElement">
					<img src='<?php echo $imgurl_d; ?>' onclick="landDetails('<?php echo $teleportid?>')" style='cursor:pointer' />
					</a>
				</div>
			</div>
			<?php
		}
		?>
		<table width="272" cellspacing="0" cellpadding="0" border="0">
		  <tbody><tr class='details'>
			<td width="150px"><a style="display: inline;" href="<?php echo $sharelink; ?>"><img border="0" height="15" style="cursor:pointer;" src="images/facebook_icon.png">&nbsp;Share this location</a></td>
			<td style="display: table-cell;"><?php echo $likehtml; ?></td>
			<td></td>
		  </tr>
		  <tr class='details'>
			<td height="5" colspan="3"></td>
		  </tr>
		  <tr class='details'>
			<td colspan="3" class="text_1">
				<span><?php echo $rs[$prefix2][$i]['detail']; ?></span>
			</td>
		  </tr>
		  <tr class='details'>
			<td colspan="3" height="5"></td>
		  </tr>
		  <tr class='details'>
			<td colspan="3" class="text_1" align="right">
			<?php
			if($rs[$prefix2][$i]['land_owner']){
				?>
				<div>Owner: <?php echo $rs[$prefix2][$i]['land_owner']; ?></div></td>
				<?php
			}
			?>
			
		  </tr>
		  <!--
		  <tr>
			<td colspan="3" class="text_1" align="right"><div>Highest Bid: </div></td>
		  </tr>
		  -->
		  <tr class='details'>
			<td colspan="3" height="10"></td>
		  </tr>
		  <tr  class="editor" style='display:none' >
			<td colspan=3 class='text_1'>
				<form id='<?php echo $prefix1; ?>form_<?php echo $rs[$prefix2][$i]['id']; ?>' style='margin:0px;'>
					<input type='hidden' name='type' value='landdetail' />
					<input type='hidden' name='id' value='<?php echo $rs[$prefix2][$i]['id']; ?>' />
					Title:<br />
					<input style='width:285px; color:black;' class='text_1' name='title' type='text' value="<?php echo htmlentities(utf8_decode(stripslashes($rs[$prefix2][$i]['title']))); ?>" /><br />
					Details:<br />
					<textarea style='width:285px; color:black; height:40px;' class='text_1' name='detail'><?php echo htmlentities(utf8_decode(stripslashes($rs[$prefix2][$i]['detail']))); ?></textarea><br />
					Land Owner:<br />
					<input style='width:285px; color:black;' class='text_1' name='land_owner' type='text' value="<?php echo htmlentities(utf8_decode(stripslashes($rs[$prefix2][$i]['land_owner']))); ?>" /><br /><br />
					<input class='longbutton' style="display: block;" type='button' value="Save" onclick='saveLandDetails("<?php echo $prefix1; ?>form_<?php echo $rs[$prefix2][$i]['id']; ?>", "<?php echo $prefix1.$rs[$prefix2][$i]['id']; ?>_<?php echo $x1; ?>_<?php echo $y1; ?>")' />
				</form>
				<input class='longbutton' style="display: block;" type='button' value="Back" onclick='jQuery(".editor").hide(); jQuery(".details").show();  jQuery(".editbuttons").show();' />
			</td>
		  </tr>
		  <tr class='editbuttons'>
			<td colspan="3" class="text_1" align='center'>
				<input class='longbutton' style="display: block;" type='button' value="Edit" onclick='jQuery(".editor").show(); jQuery(".details").hide();  jQuery(".editbuttons").hide();' />
				<input class='longbutton' style="display: block;" type='button' value="Manage Images" onclick='manageAssets("<?php echo htmlentities($rs[$prefix2][$i]['id']);?>", "<?php echo $prefix2; ?>", "images", "<?php echo $prefix1.$rs[$prefix2][$i]['id']; ?>_<?php echo $x1; ?>_<?php echo $y1; ?>");' />
				<input class='longbutton' style="display: block;" type='button' value="Manage Videos" onclick='manageAssets("<?php echo htmlentities($rs[$prefix2][$i]['id']);?>", "<?php echo $prefix2; ?>", "videos", "<?php echo $prefix1.$rs[$prefix2][$i]['id']; ?>_<?php echo $x1; ?>_<?php echo $y1; ?>");' />
			</td>
		  </tr>
		  <tr>
			<td colspan="3" height="10"></td>
		  </tr>
		</tbody>
		</table>
		</div>
		<?php
	}
	?>
	<script>
	function manageAssets(idx, landtype, assettype, idback){
		url = "manageassets.php?landtype="+landtype+"&assettype="+assettype+"&id="+idx+"&idback="+idback;
		jQuery.colorbox({iframe:true, width:"870px", height:"650px", href:url});
		
	}
	function saveLandDetails(idx, id){
		jQuery("#ownedLandList").hide();
		datax = jQuery("#"+idx).serialize();
		jQuery.ajax({
			dataType: "html",
			type: 'post',
			async: true,
			data: datax,
			url: '/ajax/page_webuserLands.php',
			success: function(data){
				jQuery(".editor").hide(); 
				jQuery(".details").show(); 
				jQuery(".editbuttons").show();
				getLands(id);
				jQuery("#ownedLandList").show();
			},
			error: function(){ alert(error);}
		});
	}
	function landDetails(idx, nozoom){
		//alert(idx);
		if(idx.indexOf("landspecial")>=0){
			a = idx.split("_");
			id = a[1];
			x = a[2];
			y = a[3];
		}
		else{
			a = idx.split("_");
			id = a[1];
			x = a[2];
			y = a[3];
		}
		//alert(jQuery("#"+a[0]+"_"+a[1]).html());
		if(isset(jQuery("#"+a[0]+"_"+a[1]).html())){
			str = jQuery("#"+a[0]+"_"+a[1]).html();
		}
		else{
			str = "";
		}
		jQuery("#the_detailx").html(str);
		//jQuery("#the_detailx").show();
		if(!isset(nozoom)){
			zoomTo(x, y);
		}
		
	}
	</script>
	<?php
	echo "<select id='ownedlands' class='select' onchange='landDetails(this.value)' style='text-align:center'>";
	$land_count = 0;
	for($i=0; $i<$ldt; $i++){
		echo "<option style='text-align:center' value='landdetail_".$rs['land_detail'][$i]['id']."_".$rs['land_detail'][$i]['land'][0]['x']."_".$rs['land_detail'][$i]['land'][0]['y']."'>".stripslashes($rs['land_detail'][$i]['title'])."</option>";
		$land_count++;
	}
	for($i=0; $i<$lst; $i++){
		echo "<option style='text-align:center' value='landspecial_".$rs['land_special'][$i]['id']."_".$rs['land_special'][$i]['land'][0]['x']."_".$rs['land_special'][$i]['land'][0]['y']."'>".stripslashes($rs['land_special'][$i]['title'])."</option>";
		$land_count++;
	}
	echo "</select>";
	?>
	<div id='the_detailx' style='display:;' ></div>
	<script>
		<?php
		if($_GET['id']){
			?>jQuery("#ownedlands").val("<?php echo $_GET['id']; ?>");<?php
		}
		if($land_count==1){
			landDetails(jQuery("#ownedlands").val());
		}
		?>
		landDetails(jQuery("#ownedlands").val(), true);
	</script>
	<?php
}
?>
<script>
function updateEmail2(){
	//alert(jQuery("#form_main_details2").serialize());
	//jQuery("#update_email_button3").hide();
	jQuery.ajax({
		dataType: "json",
		type: 'post',
		data: jQuery("#form_main_details2").serialize(), 
		url: '/ajax/page_webuserLands.php?action=updateemail',
		success: function(data){
			if(data['error']){
				alert(data['error']);
				jQuery("#update_email_button3").show();
			}
			else{
				jQuery("#zemail2").val(data['useremail']);
				jQuery("#form_main_details2").show();
				jQuery("#update_email_button3").show();
				alert("You have successfully updated your E-mail Address");
				jQuery("#update_email_button3").show();
			}
		}
	});
}
function updateEmail(){
	//alert(jQuery("#emailupdater").serialize());
	//jQuery("#update_email_button2").hide();
	jQuery.ajax({
		dataType: "json",
		type: 'post',
		data: jQuery("#emailupdater2").serialize(),
		url: '/ajax/page_webuserLands.php?action=updateemail',
		success: function(data){
			if(data['error']){
				alert(data['error']);
				jQuery("#update_email_button3").show();
			}
			else{
				jQuery("#zemail2").val(data['useremail']);
				jQuery("#form_main_details2").show();
				jQuery("#emailupdater2").hide();
				alert("You have successfully updated your E-mail Address");
				jQuery("#update_email_button3").show();
				
			}
		}
	});
}
</script>
<?php
if(!checkEmail($_SESSION['userdata']['useremail'])){
	?>
	<form id='emailupdater2'>
	<div class='text_1' style='padding-bottom:5px'>Please enter a valid e-mail to continue</div>
	<div style='padding-bottom:5px'><input class="input_3" type="text" placeholder="E-mail" style="width:283px; height:30px;" name="email"></input></div>
	<div style='padding-bottom:10px'><input class='longbutton' type='button'  id='update_email_button2' value="Submit" onclick="updateEmail()" /></div>
	</form>
	<?php
	$formstyle = "display:none";
}
/*
?>
<form id="form_main_details2" method="post" enctype="multipart/form-data" style='margin:0px; <?php echo $formstyle; ?>'>
	<div style='padding-bottom:5px'><input id='zemail2' class="input_3" type="text" placeholder="E-mail" style="width:283px; height:30px;" name="email" value="<?php echo $_SESSION['userdata']['useremail']; ?>"></input></div>
	<div style='padding-bottom:10px'><input class='longbutton' type='button'  id='update_email_button3' value="Update E-mail Address" onclick="updateEmail2()" /></div>
</form>
<?php
*/
exit();

?>
