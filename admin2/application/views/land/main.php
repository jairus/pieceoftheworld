<?php

?>
<script>
jQuery(function(){

	
	
	jQuery("#company_search").autocomplete({
		//define callback to format results
		source: function(req, add){
			//pass request to server
			jQuery.getJSON("<?php echo site_url(); ?>land/ajax_search", req, function(data) {
				//create array for response objects
				var suggestions = [];
				//process response
				jQuery.each(data, function(i, val){								
					suggestions.push(val);
				});
				//pass array to callback
				add(suggestions);
			});
		},
		//define select handler
		select: function(e, ui) {
			label = ui.item.label;
			value = ui.item.value;
			jQuery("#company_search").val(label);
			self.location = "<?php echo site_url(); ?>land/edit/"+value;
			return false;
		},
		focus: function(e, ui) {
			label = ui.item.label;
			value = ui.item.value;
			jQuery("#company_search").val(label);
			return false;
		},


	});	
});

function deleteCompany(co_id){
	if(confirm("Are you sure you want to delete this company?")){
		formdata = "id="+co_id;
		jQuery.ajax({
			url: "<?php echo site_url(); ?>land/ajax_delete/"+co_id,
			type: "POST",
			data: formdata,
			dataType: "script",
			success: function(){
				jQuery("#tr"+co_id).fadeOut(200);
				self.location = "<?php echo site_url(); ?>land";
			}
		});
		
	}
}
function searchCompany(){
	self.location = "<?php echo site_url(); ?>land/search/?search="+jQuery("#search").val()+"&filter="+jQuery("#sfilter").val();
}
</script>
<center>
<div class='pad10' >
<form action="<?php echo site_url(); ?>land/search/" class='inline' >
	Filter: <select name='filter' id='sfilter'>
	<option value="id">ID</option>
	<option value="land_owner">Land Owner</option>
	<option value="useremail">E-mail</option>
	<option value="title">Title</option>
	<option value="detail">Detail</option>
	</select>
	Search: <input type='text' id='search' value="<?php echo sanitizeX($search); ?>" name='search' />
	<input type='button' class='button normal' value='search' onclick='searchCompany()'>
</form>
<?php
if(trim($filter)){
	?>
	<script>
	jQuery("#sfilter").val("<?php echo sanitizeX($filter); ?>")
	</script>
	<?php
}
$t = count($records);
?>
</center>
<div class='list'>
<table>
	<?php
	/*
	if($t){
		?>
		<tr>
			<td colspan=6 style='border:0px;'>
			[ <a href='<?php echo site_url(); ?>land/export/<?php echo $export_sql?>/xls' >EXPORT TO EXCEL</a> ]
			[ <a href='<?php echo site_url(); ?>land/export/<?php echo $export_sql?>/csv' >EXPORT TO CSV</a> ]
			[ <a href='<?php echo site_url(); ?>land/import' >IMPORT CSV FILE</a> ]
			[ <a href='<?php echo site_url(); ?>land/import/old' >IMPORT CSV FILE (OLD)</a> ]
			</th>
		</tr>
		<?php
	}
	else{
		?>
		<tr>
			<td colspan=6 style='border:0px;'>
			[ <a href='<?php echo site_url(); ?>land/import' >IMPORT CSV FILE</a> ]
			[ <a href='<?php echo site_url(); ?>land/import/old' >IMPORT CSV FILE (OLD)</a> ]
			</th>
		</tr>
		<?php
	}
	*/
	?>
	<tr>
		<th style="width:20px"></th>
		<!--<th style="width:20px"></th>-->
		<th>Land&nbsp;ID</th>
		<th>Plot (X-Y)</th>
		<th>Land Owner</th>
		<th>E-mail</th>
		<th>Title</th>
		<th width="200px">Detail</th>
		<th>Folder&nbsp;/&nbsp;Date&nbsp;Sold</th>
		<th>Image</th>
		<th>PDF</th>
		<th></th>
	</tr>
	<?php
	
	for($i=0; $i<$t; $i++){
		$post = array();
		$imageurl = "";
		$absfolder = dirname(__FILE__)."/../../../../_uploads/".trim($records[$i]['folder']);
		$filename = $absfolder."/post.txt";
		if(file_exists($filename)){
			$post = (file_get_contents($filename));
			$post = unserialize($post);
			$records[$i]['title'] = $post['title_name'];
			$records[$i]['detail'] = $post['detail_name'];
			$records[$i]['land_owner'] = $post['land_owner'];
			$records[$i]['useremail'] = $post['useremail'];
			/*
			Array
			(
				[save] => 1
				[step] => 2
				[pass] => A631CD74-1D21-40b1-8602-346611127127
				[land] => 101130-220106_101131-220106
				[useremail] => melissa.birdvogel@gmail.com
				[title_name] => Home is where the heart is
				[land_owner] => Melissa
				[detail_name] => 
				[button_name] =>   Submit  
			)
			*/
			$imageurl = basename($post['filename']);
		}
		?>
		<tr id="tr<?php echo htmlentitiesX($records[$i]['id']); ?>" class="row" >
			
			<td><?php echo $start+$i+1; 
			
			//if(trim($records[$i]['folder'])=='20130308_1362746262.21'){
				//echo "here <pre>"; print_r($post);
			//}
			?></td>
			<td><a href="<?php echo site_url(); ?>land/edit/<?php echo $records[$i]['id']?>" ><?php echo htmlentitiesX($records[$i]['id']); ?></a></td>
			<td align='center'>
			<?php echo $records[$i]['x']."-".$records[$i]['y']?>
			</td>
			<td><?php 
			echo $records[$i]['land_owner'];	
			?></td>
			<td><?php 
			echo $records[$i]['useremail'];	
			?></td>
			
			<!--<td style='vertical-align:middle;'><?php if(trim($records[$i]['logo'])){ ?><img src='<?php echo site_url(); ?>media/image.php?p=<?php echo $records[$i]['logo'] ?>&mx=25' /> <?php } ?></td>-->
			<td><?php echo htmlentitiesX($records[$i]['title']); 
			?></td>
			<td><?php 
			echo $records[$i]['detail'];	
			?></td>
			<td>
			<?php
			
			if(trim($records[$i]['folder'])){
				?><a href="<?php echo "/_uploads/".$records[$i]['folder'];	?>" target='_blank'><?php 
				
				$date = explode("_", $records[$i]['folder']); 
				$date = explode(".", $date[1]);
				echo str_replace(" ", "&nbsp;", date("M d, Y H:i", $date[0]));
				
				?></a>
				<?php
			}
			?>
			</td>
			<td>
			<?php
			if($imageurl){
				?><a href="<?php echo "/_uploads/".$records[$i]['folder']."/".$imageurl;	?>" target='_blank'>IMG</a><?php
			}
			else if(trim($records[$i]['picture'])){
				?><a href="/theimage.php?id=<?php echo $records[$i]['id']; ?>" target='_blank'>IMG<br />(old&nbsp;system)</a><?php
			}
			?></td>
			<td>
			<?php
			if(trim($records[$i]['folder'])){
				if(file_exists($absfolder."/certificate.pdf")){
					echo "<a href='/_uploads/".$records[$i]['folder']."/certificate.pdf' target='_blank'>".round(filesize($absfolder."/certificate.pdf")/1000000, 2)."MB</a>";
				}
				else{
					echo "0MB";
				}
				if(trim($records[$i]['email_resent'])=='yes'){
					echo "<br /><a style='color:green'>Cert&nbsp;Resent</a>";
				}
			}
			?>
			</td>
			<td width='300px'>
			<?php
			if(trim($records[$i]['folder'])){
				?>
				[ <a href="<?php echo "/gencert.php?f=".$records[$i]['folder'];	?>" target='_blank' >Generate Cert</a> ] 
				[ <a href="<?php echo "/gencert.php?f=".$records[$i]['folder'];	?>&email=1" target='_blank' onclick='return confirm("Are you sure you want to resend certificate?");' >Resend Cert</a> ] 
				<?php
			}
			?>
			[ <a href="<?php echo site_url(); ?>land/edit/<?php echo $records[$i]['id']?>" >Edit</a> ] 
			[ <a style='color: red; cursor:pointer; text-decoration: underline' onclick='deleteRecord("<?php echo htmlentitiesX($records[$i]['id']) ?>"); ' >Delete</a> ]
			
			</td>
		</tr>
		<?php
	}
	if($pages>0){
		?>
		<tr>
			<td colspan="11" class='center font12' >
				There is a total of <?php echo $cnt; ?> <?php if($cnt>1) { echo "records"; } else{ echo "record"; }?> in the database. 
				Go to Page:
				<?php
				if($search){
					?>
					<select onchange='self.location="?search=<?php echo sanitizeX($search); ?>&filter=<?php echo sanitizeX($filter); ?>&start="+this.value'>
					<?php

				}
				else{
					?>
					<select onchange='self.location="?start="+this.value'>
					<?php
				}
				for($i=0; $i<$pages; $i++){
					if(($i*$limit)==$start){
						?><option value="<?php echo $i*$limit?>" selected="selected"><?php echo $i+1; ?></option><?php
					}
					else{
						?><option value="<?php echo $i*$limit?>"><?php echo $i+1; ?></option><?php
					}
				}
				?>
				</select>
			</td>
		</tr>
		<?php
	}
	?>
</table>
</div>
