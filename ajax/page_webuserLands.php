<?php
session_start();
require_once('user_fxn.php');

$webUserId = $_SESSION['userdata']['id'];
$rs = getLands($webUserId);

if(empty($rs['land_detail']) && empty($rs['land_special']) ){
	?><?php
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
		?>
		<div id='detail_<?php echo $rs['land_detail'][$i]['id']; ?>' style='display:none'>
			<?php
			/*
			<div class='div'><?php echo $rs['land_detail'][$i]['detail']; ?></div>
			$pt = count($rs['land_detail'][$i]['pictures']);
			if($pt){
				if($pt%2){
					$pt+=1;
				}
				echo "<table style='width:284px; margin-top:1px;' cellpadding=0 cellspacing=4 >";
				for($j=0; $j<$pt; $j++){
					if($j==0){
						echo "<tr>";
					}
					else if($j%2==0){
						echo "</tr><tr>";
					}
					?>
					<td style='background:#00A4DA; width:50%; text-align:center; padding: 2px 0px 2px 0px; vertical-align:middle' ><?php 
					if(trim($rs['land_detail'][$i]['pictures'][$j]['picture'])){
						?><img src='/image.php?dir=<?php echo base64_encode($rs['land_detail'][$i]['pictures'][$j]['picture']); ?>' /><?php
					}
					else{
						echo "&nbsp;";
					}
					?></td>
					<?php
				}
				echo "<tr></table>";
			}
			*/
			$imgdir = $rs['land_detail'][$i]['pictures'][0]['picture'];
			
			$imgurl = "http://pieceoftheworld.co/image.php?dir=".base64_encode($imgdir)."&w=290&h=150";
			$imgurl = urlencode($imgurl);
			$x1 = $rs['land_detail'][$i]['land'][0]['x'];
			$y1 = $rs['land_detail'][$i]['land'][0]['y'];
			$sharetitle = $rs['land_detail'][$i]['title'];
			$sharetext = $rs['land_detail'][$i]['detail'];
			if(!$sharetitle){
				$sharetitle = "Mark your very own Piece of the World!";
			}
			if(!$sharetext){
				$sharetext = "Get your own piece of the world at pieceoftheworld.com";
			}
			$link = "http://pieceoftheworld.co/?xy=".$x1."~".$y1;
			$sharelink = "https://www.facebook.com/dialog/feed?app_id=454736247931357&link=".$link."&picture=".$imgurl."&name=Piece of the World&caption=".$sharetitle."&description=".$sharetext."&redirect_uri=".$link;
			$likehtml = '<iframe src="//www.facebook.com/plugins/like.php?href='.$link.'&amp;send=false&amp;layout=button_count&amp;width=30&amp;show_faces=false&amp;font=arial&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=454736247931357" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:21px; width:75px;" allowTransparency="true"></iframe>'; 
			
			if(trim($imgdir)){
				?>
				<div align="center" id="top_list_img_2">
					<div class="img">
						<a title="" class="xcboxElement">
						<img src='/image.php?dir=<?php echo base64_encode($imgdir); ?>&w=290&h=150' />
						</a>
					</div>
				</div>
				<?php
			}
			?>
			<table width="290" cellspacing="0" cellpadding="0" border="0" id="table_main_info">
			  <tbody><tr>
				<td width="150px"><a style="display: inline;" href="<?php echo $sharelink; ?>"><img border="0" height="15" style="cursor:pointer;" id="fbshare" src="images/facebook_icon.png">&nbsp;Share this location</a></td>
				<td style="display: table-cell;"><?php echo $likehtml; ?></td>
				<td></td>
			  </tr>
			  <tr>
				<td height="5" colspan="3"></td>
			  </tr>
			  <tr>
				<td colspan="3" class="text_1">
					<span id="info-detail"><?php echo $rs['land_detail'][$i]['detail']; ?></span>
				</td>
			  </tr>
			  <tr>
				<td colspan="3" height="5"></td>
			  </tr>
			  <tr>
				<td colspan="3" class="text_1" align="right">
				<?php
				if($rs['land_detail'][$i]['land_owner']){
					?>
					<div>Owner: <?php echo $rs['land_detail'][$i]['land_owner']; ?></div></td>
					<?php
				}
				?>
				
			  </tr>
			  <!--
			  <tr>
				<td colspan="3" class="text_1" align="right"><div>Highest Bid: </div></td>
			  </tr>
			  -->
			  <tr>
				<td colspan="3" height="10"></td>
			  </tr>
			  <tr>
				<td colspan="3" class="text_1" align='center'>
					<input class='longbutton' style="display: block;" type='button' value="Images" />
					<input class='longbutton' style="display: block;" type='button' value="Videos" />
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
		?>
		<div id='special_<?php echo $rs['land_detail'][$i]['id']; ?>' style='display:none'>
			<div class='div'><?php echo $rs['land_detail'][$i]['detail']; ?></div>
		</div>
		<?php
	}
	?>
	<script>
	function landDetails(idx, nozoom){
		//alert(idx);
		if(idx.indexOf("special")>=0){
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
	
	for($i=0; $i<$ldt; $i++){
		echo "<option style='text-align:center' value='detail_".$rs['land_detail'][$i]['id']."_".$rs['land_detail'][$i]['land'][0]['x']."_".$rs['land_detail'][$i]['land'][0]['y']."'>".$rs['land_detail'][$i]['title']."</option>";
	}
	for($i=0; $i<$lst; $i++){
		echo "<option style='text-align:center' value='special_".$rs['land_special'][$i]['id']."_".$rs['land_special'][$i]['land'][0]['x']."_".$rs['land_special'][$i]['land'][0]['y']."'>".$rs['land_special'][$i]['title']."</option>";
	}
	echo "</select>";
	?>
	<div id='the_detailx' style='display:;' ></div>
	<script>
		landDetails(jQuery("#ownedlands").val(), true);
	</script>
	<?php
}
exit();

?>
