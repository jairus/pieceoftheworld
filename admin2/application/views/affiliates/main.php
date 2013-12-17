<?php
$controller = "affiliates";
?>
<script>
jQuery(function(){
	jQuery("#<?php echo $controller; ?>_search").autocomplete({
		//define callback to format results
		source: function(req, add){
			//pass request to server
			jQuery.getJSON("<?php echo site_url(); echo $controller; ?>/ajax_search", req, function(data) {
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
			self.location = "<?php echo site_url(); echo $controller; ?>/edit/"+value;
			return false;
		},
		focus: function(e, ui) {
			label = ui.item.label;
			value = ui.item.value;
			jQuery("#<?php echo $controller; ?>_search").val(label);
			return false;
		},


	});	
});

function deleteRecord(idx){
	if(confirm("Are you sure you want to delete this record?")){
		formdata = "id="+idx;
		jQuery.ajax({
			url: "<?php echo site_url(); echo $controller; ?>/ajax_delete/"+idx,
			type: "POST",
			data: formdata,
			dataType: "script",
			success: function(){
				jQuery("#tr"+idx).fadeOut(200);
				self.location = "<?php echo site_url(); echo $controller; ?>";
			}
		});
		
	}
}
function searchRecord(){
	self.location = "<?php echo site_url(); echo $controller; ?>/search/?search="+jQuery("#search").val()+"&filter="+jQuery("#sfilter").val();
}
function addRecord(){
	self.location = "<?php echo site_url(); echo $controller; ?>/add";
}
</script>
<center>
<div class='pad10' >
<form action="<?php echo site_url(); echo $controller; ?>/search/" class='inline' >
	Filter: <select name='filter' id='sfilter'>
	<option value="id">ID</option>
	<option value="title">Name</option>
	<option value="email">E-mail</option>
	<option value="website">Website</option>
	<option value="detail">Details</option>
	<option value="">All</option>
	</select>
	Search: <input type='text' id='search' value="<?php echo sanitizeX($search); ?>" name='search' />
	<input type='button' class='button normal' value='search' onclick='searchRecord()'>
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
	<?php
	/*
	if($t){
		?>
		<tr>
			<td colspan=6 style='border:0px;'>
			[ <a href='<?php echo site_url(); echo $controller; ?>/export/<?php echo $export_sql?>/xls' >EXPORT TO EXCEL</a> ]
			[ <a href='<?php echo site_url(); echo $controller; ?>/export/<?php echo $export_sql?>/csv' >EXPORT TO CSV</a> ]
			[ <a href='<?php echo site_url(); echo $controller; ?>/import' >IMPORT CSV FILE</a> ]
			[ <a href='<?php echo site_url(); echo $controller; ?>/import/old' >IMPORT CSV FILE (OLD)</a> ]
			</th>
		</tr>
		<?php
	}
	else{
		?>
		<tr>
			<td colspan=6 style='border:0px;'>
			[ <a href='<?php echo site_url(); echo $controller; ?>/import' >IMPORT CSV FILE</a> ]
			[ <a href='<?php echo site_url(); echo $controller; ?>/import/old' >IMPORT CSV FILE (OLD)</a> ]
			</th>
		</tr>
		<?php
	}
	*/
	?>
	<tr>
		<th style="width:20px"></th>
		<!--<th style="width:20px"></th>-->
		<th>ID</th>
		<th>Name</th>
		<th>E-mail</th>
		<th>Affiliate Website</th>
		<th>URL/Coupon Code</th>
		<th>Clicks/Use</th>
		<th>Total Commission</th>
		<th></th>
	</tr>
	<?php
	
	for($i=0; $i<$t; $i++){
		?>
		<tr id="tr<?php echo htmlentitiesX($records[$i]['id']); ?>" class="row" >
			
			<td>
				<?php echo $start+$i+1; ?>
			</td>
			<td>
				<a href="<?php echo site_url(); echo $controller; ?>/edit/<?php echo $records[$i]['id']?>" ><?php echo htmlentitiesX($records[$i]['id']); ?></a>
			</td>
			<td align='center'>
				<?php echo $records[$i]['title']; ?>
			</td>
			<td>
				<?php echo $records[$i]['email']; ?>
			</td>
			<td>
				<?php echo $records[$i]['website'];	?>
			</td>
			<td>
				<?php
				if(trim($records[$i]['coupon'])){
					echo trim($records[$i]['coupon']);
				}
				else{
					?><a href='<?php echo site_url2()."?a=".($records[$i]['id']); ?>' target='_blank'>Link</a><?php
				}
				?>	
			</td>
			<td>
				<?php echo $records[$i]['clicks'];	?>
			</td>
			<td>
				<?php echo $records[$i]['commission'];	?>
			</td>
			<td width='300px'>
			[ <a href="<?php echo site_url(); echo $controller; ?>/edit/<?php echo $records[$i]['id']?>" >More Details</a> ] 
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
