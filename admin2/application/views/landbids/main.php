<?php

?>
<script>
function deleteRecord(co_id){
	if(confirm("Are you sure you want to delete this record?")){
		formdata = "id="+co_id;
		jQuery.ajax({
			url: "<?php echo site_url(); ?>landbids/ajax_delete/"+co_id,
			type: "POST",
			data: formdata,
			dataType: "script",
			success: function(){
				jQuery("#tr"+co_id).fadeOut(200);
				self.location = "<?php echo site_url(); ?>landbids";
			}
		});
		
	}
}

function searchCompany(){
	self.location = "<?php echo site_url(); ?>landbids/search/?search="+jQuery("#search").val()+"&filter="+jQuery("#sfilter").val();
}
</script>
<center>
<div class='pad10' >
<form action="<?php echo site_url(); ?>landbids/search/" class='inline' >
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
	<tr>
		<th style="width:20px"></th>
		<th>Title</th>
		<th>Bid</th>
		<th>Bidder</th>		
		<th>Message</th>
		<th>Land ID</th>
		<th></th>
	</tr>
	<?php
	
	for($i=0; $i<$t; $i++){
		?>
		<tr id="tr<?php echo htmlentitiesX($records[$i]['id']); ?>" class="row" >
			
			<td><?php echo $start+$i+1; ?></td>
			<td align="right"><?php echo $records[$i]['title']; ?></td>
			<td align="right"><?php echo $records[$i]['bid']; ?></td>
			<td><?php echo $records[$i]['bidder']; ?></td>			
			<td><?php echo $records[$i]['message']; ?></td>			
			<td align="right"><?php echo htmlentitiesX($records[$i]['land_id']); ?></td>
			<td width='300px'>
			[ <a href="<?php echo site_url(); ?>landbids/edit/<?php echo $records[$i]['id']?>" >Edit</a> ] 
			[ <a style='color: red; cursor:pointer; text-decoration: underline' onclick='deleteRecord("<?php echo htmlentitiesX($records[$i]['id']) ?>");' >Delete</a> ]
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
