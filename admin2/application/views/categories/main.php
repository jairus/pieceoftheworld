<?php
$controller = 'categories';
?>
<script>
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
	self.location = "<?php echo site_url(); ?>categories/search/?search="+jQuery("#search").val()+"&filter="+jQuery("#sfilter").val();
}
function addRecord(){
	self.location = "<?php echo site_url(); echo $controller; ?>/add";
}
</script>
<center>
<div class='pad10' >
<form action="<?php echo site_url(); ?>categories/search/" class='inline' >
	Filter: <select name='filter' id='sfilter'>
	<option value="name">Name</option>
	<option value="id">ID</option>	
	</select>
	Search: <input type='text' id='search' value="<?php echo sanitizeX($search); ?>" name='search' />
	<input type='button' class='button normal' value='search' onclick='searchCompany()'>
	<input type='button' class='button normal' value='add' onclick='addRecord()'>
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
		<th>ID</th>
		<th>Category Name</th>
		<th></th>
	</tr>
	<?php
	
	for($i=0; $i<$t; $i++){
		?>
		<tr id="tr<?php echo htmlentitiesX($records[$i]['id']); ?>" class="row" >
			
			<td><?php echo $start+$i+1; ?></td>
			<td><a href="<?php echo site_url(); ?>categories/edit/<?php echo $records[$i]['id']?>" ><?php echo htmlentitiesX($records[$i]['id']); ?></a></td>			
			<td><?php echo $records[$i]['name'];	?></td>			
			<td width='300px'>
			[ <a href="<?php echo site_url(); ?>categories/edit/<?php echo $records[$i]['id']?>" >Edit</a> ] 
			[ <a style='color: red; cursor:pointer; text-decoration: underline' onclick='deleteRecord("<?php echo htmlentitiesX($records[$i]['id']) ?>"); ' >Delete</a> ]
			
			</td>
		</tr>
		<?php
	}
	if($pages>0){
		?>
		<tr>
			<td colspan="4" class='center font12' >
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
