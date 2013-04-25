<?php
$controller = 'user';
?>
<script>
jQuery(function(){
	
	jQuery("#company_search").autocomplete({
		//define callback to format results
		source: function(req, add){
			//pass request to server
			jQuery.getJSON("<?php echo site_url(); ?>user/ajax_search", req, function(data) {
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
			self.location = "<?php echo site_url(); ?>user/edit/"+value;
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
	self.location = "<?php echo site_url(); ?>user/search/?search="+jQuery("#search").val()+"&filter="+jQuery("#sfilter").val();
}
function addRecord(){
	self.location = "<?php echo site_url(); echo $controller; ?>/add";
}
</script>
<center>
<div class='pad10' >
<form action="<?php echo site_url(); ?>user/search/" class='inline' >
	Filter: <select name='filter' id='sfilter'>
	<option value="id">ID</option>
	<option value="first_name">First Name</option>
	<option value="last_name">Last Name</option>
	<option value="email">E-mail</option>		
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
		<th>E-mail</th>
		<th>First Name</th>
		<th>Last Name</th>
		<th>Address</th>
		<th>Admin</th>
		<th></th>
	</tr>
	<?php
	
	for($i=0; $i<$t; $i++){		
		?>
		<tr id="tr<?php echo htmlentitiesX($records[$i]['id']); ?>" class="row" >
			
			<td><?php echo $start+$i+1; ?></td>
			<td><a href="<?php echo site_url(); ?>user/edit/<?php echo $records[$i]['id']?>" ><?php echo htmlentitiesX($records[$i]['id']); ?></a></td>			
			<td><?php echo $records[$i]['email'];	?></td>
			<td><?php echo $records[$i]['first_name'];	?></td>
			<td><?php echo $records[$i]['last_name'];	?></td>
			<td><?php	$address = '';		
					
						if($records[$i]['city'] && $records[$i]['city'] != 'NA' && $records[$i]['city'] != 'N/A') $address .=  ', ' . $records[$i]['city'];
						if($records[$i]['state_us'] && $records[$i]['state_us'] != 'NA' && $records[$i]['state_us'] != 'N/A') $address .=  ', ' .  $records[$i]['state_us'];
						if($records[$i]['state_nonus'] && $records[$i]['state_nonus'] != 'NA' && $records[$i]['state_nonus'] != 'N/A') $address .=  ', ' .  $records[$i]['state_nonus'];
						if($records[$i]['country'] && $records[$i]['country'] != 'NA' && $records[$i]['country'] != 'N/A') $address .=  ', ' .  $records[$i]['country'];
						
						if($address){
							$address = substr($address,2);
							echo $address;
						}					
			?></td>
			<td><?php echo ($records[$i]['is_admin'])? 'yes' : 'no ';	?></td>			
			<td width='300px'>
			[ <a href="<?php echo site_url(); ?>user/edit/<?php echo $records[$i]['id']?>" >Edit</a> ] 
			[ <a style='color: red; cursor:pointer; text-decoration: underline' onclick='deleteRecord("<?php echo htmlentitiesX($records[$i]['id']) ?>"); ' >Delete</a> ]
			
			</td>
		</tr>
		<?php
	}
	if($pages>0){
		?>
		<tr>
			<td colspan="12" class='center font12' >
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
