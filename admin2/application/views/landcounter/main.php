<?php

?>
<script>

function searchCompany(){
	self.location = "<?php echo site_url(); ?>landcounter/search/?search="+jQuery("#search").val()+"&filter="+jQuery("#sfilter").val();
}
</script>
<center>
<div class='pad10' >
<form action="<?php echo site_url(); ?>landcounter/search/" class='inline' >
	Filter: <select name='filter' id='sfilter'>		
	<option value="x">X</option>
	<option value="y">Y</option>
	<option value="sold">Sold</option>	
	<option value="land_id">Land ID</option>
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
	<tr>
		<th style="width:20px"></th>
		<th>Total Views</th>
		<th>Plot (X-Y)</th>		
		<th>Sold</th>
		<th>Land ID</th>													
	</tr>
	<?php
	
	for($i=0; $i<$t; $i++){
		?>
		<tr id="tr<?php echo htmlentitiesX($records[$i]['id']); ?>" class="row" >
			
			<td><?php echo $start+$i+1; ?></td>
			<td align="right"><?php echo $records[$i]['viewCtr']; ?></td>
			<td><a target='_blank' href='/?xy=<?php echo $records[$i]['x']."~".$records[$i]['y']?>'><?php echo $records[$i]['x']."-".$records[$i]['y']?></a></td>			
			<td><?php echo ($records[$i]['web_user_id'])? '<span style="color: green">Yes</span>' : '<span style="color: red">No</span>' ;	?></td>			
			<td><?php echo htmlentitiesX($records[$i]['land_id']); ?></td>
			
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
