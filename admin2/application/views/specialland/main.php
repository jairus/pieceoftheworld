<?php
$controller = 'specialland';
?>
<script>
jQuery(function(){
	jQuery("#company_search").autocomplete({
		//define callback to format results
		source: function(req, add){
			//pass request to server
			jQuery.getJSON("<?php echo site_url(); ?>specialland/ajax_search", req, function(data) {
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
			self.location = "<?php echo site_url(); ?>specialland/edit/"+value;
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

function deleteRecord(co_id){
	if(confirm("Are you sure you want to delete this record?")){
		formdata = "id="+co_id;
		jQuery.ajax({
			url: "<?php echo site_url(); echo $controller ?>/ajax_delete/"+co_id,
			type: "POST",
			data: formdata,
			dataType: "script",
			success: function(){
				jQuery("#tr"+co_id).fadeOut(200);
				self.location = "<?php echo site_url(); echo $controller ?>";
			}
		});
		
	}
}

function searchCompany(){
	self.location = "<?php echo site_url(); ?>specialland/search/?search="+jQuery("#search").val()+"&filter="+jQuery("#sfilter").val();
}
</script>
<center>
<div class='pad10' >
<form action="<?php echo site_url(); ?>specialland/search/" class='inline' >
	Filter: <select name='filter' id='sfilter'>
	<option value="id">ID</option>
	<option value="land_owner">Land Owner</option>	
	<option value="title">Title</option>
	<option value="detail">Detail</option>
	<option value="price">Price</option>
    <option value="category">Category</option>
	</select>
	Search: <input type='text' id='search' value="<?php echo sanitizeX($search); ?>" name='search' />
	<input type='button' class='button normal' value='search' onclick='searchCompany()'>
	<input type='button' class='button normal' value='Add Special Land' onclick='self.location="/addspecial.php"'>
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
			[ <a href='<?php echo site_url(); ?>specialland/export/<?php echo $export_sql?>/xls' >EXPORT TO EXCEL</a> ]
			[ <a href='<?php echo site_url(); ?>specialland/export/<?php echo $export_sql?>/csv' >EXPORT TO CSV</a> ]
			[ <a href='<?php echo site_url(); ?>specialland/import' >IMPORT CSV FILE</a> ]
			[ <a href='<?php echo site_url(); ?>specialland/import/old' >IMPORT CSV FILE (OLD)</a> ]
			</th>
		</tr>
		<?php
	}
	else{
		?>
		<tr>
			<td colspan=6 style='border:0px;'>
			[ <a href='<?php echo site_url(); ?>specialland/import' >IMPORT CSV FILE</a> ]
			[ <a href='<?php echo site_url(); ?>specialland/import/old' >IMPORT CSV FILE (OLD)</a> ]
			</th>
		</tr>
		<?php
	}
	*/
	?>
	<tr>
		<th style="width:20px"></th>
		<th>Land&nbsp;ID</th>
		<th>Title</th>
        <th>Category</th>
		<th>Detail</th>
		<th>Price</th>
		<th>Sold</th>
		<th>Land Owner</th>
        <th>FB Likes</th>
		<th></th>
	</tr>
	<?php
	
	for($i=0; $i<$t; $i++){
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
		/*
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
			
			$imageurl = basename($post['filename']);
		}
		*/
		$sql = "select * from `land` where `land_special_id`='".$records[$i]['id']."'";
		$q = $this->db->query($sql);
		$lands = $q->result_array();
		?>
		<tr id="tr<?php echo htmlentitiesX($records[$i]['id']); ?>" class="row" >
			
			<td><?php echo $start+$i+1; ?></td>
			<td><a href="<?php echo site_url(); ?>specialland/edit/<?php echo $records[$i]['id']?>" ><?php echo htmlentitiesX($records[$i]['id']); ?></a></td>
			<!--<td style='vertical-align:middle;'><?php if(trim($records[$i]['logo'])){ ?><img src='<?php echo site_url(); ?>media/image.php?p=<?php echo $records[$i]['logo'] ?>&mx=25' /> <?php } ?></td>-->
			<td><?php echo htmlentitiesX($records[$i]['title']); ?></td>
            <td><?php echo $records[$i]['categoryName']; ?></td>
			<td><?php echo $records[$i]['detail']; ?></td>
			<td><?php echo $records[$i]['price'];?></td>
			<td><?php echo ($records[$i]['web_user_id'])? '<span style="color: green">Yes</span>' : '<span style="color: red">No</span>' ;	?></td>			
			<td><?php echo $records[$i]['land_owner'];	?></td>
            <td><?php echo $records[$i]['totalLikes'];	?></td>
			<td width='300px'>
			<?php
			if(trim($lands[0]['id'])){
				?>
				[ <a href="<?php echo "/gencert.php?land_id=".$lands[0]['id'];	?>" target='_blank' >Download Cert</a> ] 
				[ <a href="<?php echo "/gencert.php?land_id=".$lands[0]['id'];	?>&email=1" target='_blank' onclick='return confirm("Are you sure you want to resend certificate?");' >Resend Cert</a> ] 
				<?php
			}
			?>
			[ <a href="<?php echo site_url(); ?>specialland/edit/<?php echo $records[$i]['id']?>" >Edit</a> ] 
			[ <a style='color: red; cursor:pointer; text-decoration: underline' onclick='deleteRecord("<?php echo htmlentitiesX($records[$i]['id']) ?>"); ' >Delete</a> ]
			
			</td>
		</tr>
		<?php
	}
	if($pages>0){
		?>
		<tr>
			<td colspan="10" class='center font12' >
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
