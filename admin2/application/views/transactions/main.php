<div id="dialogEmail" title="Email Receipt Content"></div>

<center>
<div class='pad10' >
<form action="<?php echo site_url(); ?>transactions/search/" class='inline' method="post">
	Search: 	
	<select id="searchSelector" name="searchField">
		<option value="T.id" <?php if(isset($searchField) && $searchField == 'receiptId') echo 'selected' ?> >Receipt No.</option>
		<option value="txnId" <?php if(isset($searchField) && $searchField == 'txnId') echo 'selected' ?>>Transaction No.</option>
		<option value="W.useremail" <?php if(isset($searchField) && $searchField == 'useremail') echo 'selected' ?>>User Email</option>
	<select>
	<input type="text" name="searchString" value="<?php if(isset($searchString)) echo $searchString?>" />	
	<br/>
	<p>
		Start Date: <input type="text" class="datepicker" id="startDate" name="startDate" value="<?php if(isset($startDate)) echo $startDate?>" /> &nbsp;
		End Date: <input type="text" class="datepicker" id="endDate" name="endDate" value="<?php if(isset($endDate)) echo $endDate?>" /> &nbsp;			
		<input type="submit" value="Go" class="button normal" style="padding: 2px;" />
	</p>
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
		<td><strong>Total Amount:</strong> $<?php if(isset($stats['sumAmount'])) echo number_format($stats['sumAmount'], 2)?></td>
		<td><strong>Average Amount:</strong> $<?php if(isset($stats['averageAmount'])) echo number_format($stats['averageAmount'], 2)?></td>
		<td><strong>No. of Sales:</strong> <?php if(isset($stats['salesNo'])) echo number_format($stats['salesNo'])?></td>		
	</tr>
</table>
<br/><br/>
<table>
	<tr>
		<th width="50">Receipt No.</th>
		<th width="50">Transaction No.</th>
		<th width="150">Purchase Date</th>
		<th width="150" align="right">Total Amount</th>		
		<th width="250">Web User Name</th>
		<th width="250">Email</th>
		<th width="50">Land Detail ID</th>
		<th width="30">Special Land</th>		
		<th width="80">Receipt</th>
	</tr>
	<?php
	
	for($i=0; $i<$t; $i++){
		?>
		<tr id="tr<?php echo htmlentitiesX($records[$i]['id']); ?>" class="row" >
			
			<td><?php echo $records[$i]['id']; ?></td>
			<td><?php echo $records[$i]['txnId']; ?></td>
			<td><?php echo date('M j, Y h:i a', strtotime($records[$i]['dateCreated'])); ?></td>			
			<td>$<?php echo number_format($records[$i]['totalAmount'], 2); ?></td>
			<td><?php echo $records[$i]['name']; ?></td>
			<td><?php echo $records[$i]['useremail']; ?></td>
			<td><?php echo $records[$i]['land_detail_id']; ?></td>
			<td><?php echo ($records[$i]['isSpecialLand'])? 'Yes' : 'No'; ?></td>			
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