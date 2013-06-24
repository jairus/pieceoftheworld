<?php
@session_start();
require_once 'global.php';

if($_GET['action']=='createsession'){
	$_SESSION['trailer'] = 1;
	
	exit();
}

if($_GET['action']=='open_gallery'){
	if($_GET['land_id']){
		$sql = "SELECT `picture` FROM `pictures` WHERE `land_id`='".$_GET['land_id']."'";
		$r = dbQuery($sql, $_dblink);
		
		$sql = "SELECT `detail` FROM `land_detail` WHERE `id`='".$_GET['land_id']."'";
		$r2 = dbQuery($sql, $_dblink);
	}else if($_GET['land_special_id']){
		$sql = "SELECT `picture` FROM `pictures_special` WHERE `land_special_id`='".$_GET['land_special_id']."'";
		$r = dbQuery($sql, $_dblink);
		
		$sql = "SELECT `detail` FROM `land_special` WHERE `id`='".$_GET['land_id']."'";
		$r2 = dbQuery($sql, $_dblink);
	}
	
	$t = count($r);
	
	$myimage = str_replace('%3A', ':', $r[0]['picture']);
	$size = getimagesize($myimage);
	
	$width = intval($size[0]);
	$height = intval($size[1]);
	?>
	<style>
	#div_images{
		float:left;
		width:90px;
		height:50px;
		padding:0px 0px 10px 10px;
	}
	
	img.desaturate{
		filter: grayscale(100%);
		-webkit-filter: grayscale(100%);
		-moz-filter: grayscale(100%);
		-ms-filter: grayscale(100%);
		-o-filter: grayscale(100%);
		cursor:pointer;
	}
	
	.text_1{
		font-family:Arial, Helvetica, sans-serif;
		font-size:12px;
		color:#FFFFFF;
	}
	</style>
	<table width="100%" height="100%" id="table_gallery">
		<tr>
			<td>
				<table width="100%" height="100%" style="position:fixed; top:0; left:0; z-index:99999; background-color:#000000; background-image:url('images/bg_popup.png'); background-position:bottom; background-attachment:scroll; filter:alpha(opacity=90); opacity:0.9; background-repeat:repeat-x;">
					<tr>
						<td></td>
					</tr>
				</table>
				
				<?php
				if($t){
					if($t==1){
						?>
						<table style="position:absolute; top:50%; left:50%; margin-left:-189px; margin-top:-175px; z-index:999999;">
							<tr>
								<td>
									<table width="378" border="0" cellspacing="0" cellpadding="0">
									  <tr>
										<td width="358">
											<div style="padding-bottom:5px; text-align:center;"><img src="<?php echo str_replace('%3A', ':', $r[0]['picture']); ?>" height="300" border="0" id="image_main" /></div>
											<div class="text_1" style="text-align:center;"><?php echo $r2[0]['detail']; ?></div>
										</td>
										<td width="20" align="right" valign="top"><img src="images/x2.png" width="15" height="15" border="0" style="cursor:pointer;" onclick="jQuery('#table_gallery').hide();" /></td>
									  </tr>
									</table>
								</td>
							</tr>
						</table>
						<?php
					}else{
						?>
						<table style="position:absolute; top:50%; left:50%; margin-left:-460px; margin-top:-175px; z-index:999999;">
							<tr>
								<td>
									<table border="0" cellspacing="0" cellpadding="0" style="z-index:999999;">
									  <tr>
										<td width="700">
											<div style="padding-bottom:5px; text-align:center;"><img src="<?php echo str_replace('%3A', ':', $r[0]['picture']); ?>" height="300" border="0" id="image_main" /></div>
											<div class="text_1" style="text-align:center;"><?php echo $r2[0]['detail']; ?></div>
										</td>
										<td width="200" valign="top">
											<?php
											for($i=0; $i<$t; $i++){
												if(($i+1)==1){
													echo '<div id="div_images"><div style="text-align:center; height:50px;"><img src="'.str_replace('%3A', ':', $r[$i]['picture']).'" height="50" border="0" id="img'.($i+1).'" onClick="showImage('.($i+1).');" class="img" /></div></div>';
												}else{
													echo '<div id="div_images"><div style="text-align:center; height:50px;"><img src="'.str_replace('%3A', ':', $r[$i]['picture']).'" height="50" border="0" id="img'.($i+1).'" onClick="showImage('.($i+1).');" class="img desaturate" /></div></div>';
												}
											}
											?>
										</td>
										<td width="20" align="right" valign="top"><img src="images/x2.png" width="15" height="15" border="0" style="cursor:pointer;" onclick="jQuery('#table_gallery').hide();" /></td>
									  </tr>
									</table>
								</td>
							</tr>
						</table>
						<?php
					}
				}else{
					?>
					<table style="position:absolute; top:50%; left:50%; margin-left:-189px; margin-top:-240px; z-index:999999;">
						<tr>
							<td>
								<table width="378" border="0" cellspacing="0" cellpadding="0" style="z-index:999999;">
								  <tr>
									<td width="358">
										<div style="padding-bottom:5px; text-align:center;"><img src="images/place_holder.png" border="0" id="image_main" /></div>
										<div class="text_1" style="text-align:center;"><?php echo $r2[0]['detail']; ?></div>
									</td>
									<td width="20" align="right" valign="top"><img src="images/x2.png" width="15" height="15" border="0" style="cursor:pointer;" onclick="jQuery('#table_gallery').hide();" /></td>
								  </tr>
								</table>
							</td>
						</tr>
					</table>
					<?php
				}
				?>
			</td>
		</tr>
	</table>
	<script>
	function showImage(type){
		jQuery('.img').each(function() {
			jQuery(this).addClass("desaturate");
		});
	
		jQuery('#img'+type).removeClass('desaturate')
		jQuery('#image_main').attr('src', jQuery('#img'+type).attr('src'));
	}
	</script>
	<?php
	
	exit();
}
?>