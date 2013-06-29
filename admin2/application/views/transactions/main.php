<div id="dialogEmail" title="Email Receipt Content"></div>

<center>
<div class='pad10' >
<form action="<?php echo site_url(); ?>transactions/search/" class='inline' method="post">
	Search: 
	Start Date: <input type="text" class="datepicker" id="startDate" name="startDate" value="<?php if(isset($startDate)) echo $startDate?>" /> &nbsp;
	End Date: <input type="text" class="datepicker" id="endDate" name="endDate" value="<?php if(isset($endDate)) echo $endDate?>" /> &nbsp;	
	<input type="submit" value="Go" class="button normal" style="padding: 2px;" />
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
		<td><strong>Total Amount:</strong> $<?php echo $amountSum?></td>
		
	</tr>
</table>
<br/><br/>
<table>
	<tr>
		<th>Receipt ID</th>
		<th>Transaction ID</th>
		<th>Purchase Date</th>
		<th align="right">Total Amount</th>		
		<th>Web User Name</th>
		<th>Email</th>
		<th>Land Detail ID</th>
		<th>Is Special Land</th>
		<th>Picture</th>
		<th>Certificate</th>
		<th>Receipt</th>
	</tr>
	<?php
	
	for($i=0; $i<$t; $i++){
		?>
		<tr id="tr<?php echo htmlentitiesX($records[$i]['id']); ?>" class="row" >
			
			<td><?php echo $records[$i]['id']; ?></td>
			<td><?php echo $records[$i]['txnId']; ?></td>
			<td><?php echo date('M j, Y h:i a', strtotime($records[$i]['dateCreated'])); ?></td>			
			<td><?php echo $records[$i]['totalAmount']; ?></td>
			<td><?php echo $records[$i]['name']; ?></td>
			<td><?php echo $records[$i]['useremail']; ?></td>
			<td><?php echo $records[$i]['land_detail_id']; ?></td>
			<td><?php echo ($records[$i]['isSpecialLand'])? 'Yes' : 'No'; ?></td>
			<td><?php if($records[$i]['picture']) echo "<a href='".$records[$i]['picture']."' target='_blank' />view</a>"; ?></td>
			<td><?php if($records[$i]['certificate']) echo "<a href='".$records[$i]['certificate']."' target='_blank' />view</a>"; ?></td>	
			<td><?php if($records[$i]['emailContent']) { 
						list($subject, $message) = unserialize($records[$i]['emailContent']);
				?>
					<a href="#" data-id="<?php echo $records[$i]['id']; ?>" class="emailContentLink" >view content</a>
					<div id="emailContent_<?php echo $records[$i]['id']?>" class="emailContent " style="display: none"><?php echo $subject .'<hr/>'.$message?></div>
				<?php } ?>				
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
<script>
$(document).ready(function(){

	$("#dialogEmail" ).dialog({
		autoOpen: false,
		height: 400,
		width: 600,
	});	
	$('td a.emailContentLink').click(function(e){
		e.preventDefault();
		$("#dialogEmail").dialog("close");
		var id = $(this).attr('data-id');
		var content = $('#emailContent_' + id).html();
		$('#dialogEmail').html(content);		
		$("#dialogEmail").dialog("open");				
	});	
});
</script>