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
			<div class='div'><?php echo $rs['land_detail'][$i]['detail']; ?></div>
			<?php
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
			
			?>
			<input class='longbutton2' style="width:276px; margin:0px; margin-bottom:5px;" type='button' value="Images" />
			<input class='longbutton2' style="width:276px; margin:0px; margin-bottom:5px;" type='button' value="Videos" />
			
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
	function landDetails(idx){
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
		zoomTo(x, y);
		
	}
	</script>
	<?php
	echo "<select id='ownedlands' class='select' onchange='landDetails(this.value)'>";
	
	for($i=0; $i<$ldt; $i++){
		echo "<option value='detail_".$rs['land_detail'][$i]['id']."_".$rs['land_detail'][$i]['land'][0]['x']."_".$rs['land_detail'][$i]['land'][0]['y']."'>".$rs['land_detail'][$i]['title']."</option>";
	}
	for($i=0; $i<$lst; $i++){
		echo "<option value='special_".$rs['land_special'][$i]['id']."_".$rs['land_special'][$i]['land'][0]['x']."_".$rs['land_special'][$i]['land'][0]['y']."'>".$rs['land_special'][$i]['title']."</option>";
	}
	echo "</select>";
	?>
	<div id='the_detailx' style='display:;' ></div>
	<script>
		landDetails(jQuery("#ownedlands").val());
	</script>
	<?php
}
exit();

?>
