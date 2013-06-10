<?php
$controller = "landbids";
@session_start();
$sid = session_id()."_".time();
?>
<script>
function addCommas(nStr){
		nStr += '';
	
		x = nStr.split('.');
		x1 = x[0];
		x2 = x.length > 1 ? '.' + x[1] : '';
	
		var rgx = /(\d+)(\d{3})/;
	
		while (rgx.test(x1)) {
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
	
		return x1 + x2;
	}
	
	function fNum(num){
		num = uNum(num);
	
		if(num==0){ return ""; }
	
		num = num.toFixed(2);
	
		return addCommas(num);
	}
	
	function uNum(num){
		if(!num){
			num = 0;
		}else if(isNaN(num)){
			num = num.replace(/[^0-9\.]/g, "");
	
			if(isNaN(num)){ num = 0; }
		}
	
		return num*1;
	}

function saveRecord(approve){
	extra = "";
	jQuery("#savebutton").val("Saving...");
	formdata = jQuery("#record_form").serialize();
	jQuery("#record_form *").attr("disabled", true);
	jQuery.ajax({
		<?php
		if($record['id']){
			?>url: "<?php echo site_url(); echo $controller ?>/ajax_edit"+extra,<?php
		}
		else{
			?>url: "<?php echo site_url(); echo $controller ?>/ajax_add"+extra,<?php
		}
		?>
		type: "POST",
		data: formdata,
		dataType: "script",
		success: function(data){
			//alert(data);
		}
	});

}
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
</script>

<form id='record_form'>
<table width="100%" cellpadding="10px">
<?php
if(!$record['id']){
	?>
	<tr>
	<td class='font18 bold'>Add a New Land Bid</td>
	<td></td>
	</tr>
	<?php
}
else{
	?>
	<tr>
	<td class='font18 bold'>Edit Land Bids</td>
	<td></td>
	</tr>
	<?php
}

?>
</table>
<table width="100%" cellpadding="10px">
	<tr>
	  <td>Land ID:</td>
	  <td><input type="hidden" id="id" name="id" value="<?php echo $record['id']; ?>" /><input type="text" id="land_id" name="land_id" size="20" value="<?php echo $record['land_id']; ?>" readonly="readonly" /></td>
	</tr>
	<tr>
	  <td>Bidder Email:</td>
	  <td><input type="text" id="user_email" name="user_email" size="40" value="<?php echo $record['bidder']; ?>" readonly="readonly" /></td>
	</tr>
	<tr>
	  <td>User Bid (<i>USD</i>):</td>
	  <td><input type="text" id="user_bid" name="user_bid" size="20" value="<?php echo $record['bid']; ?>" onBlur="this.value=fNum(this.value);" /></td>
	</tr>
	<tr>
	<td>Message</td>
	  <td><textarea id='user_message' name='user_message'><?php echo $record['message']; ?></textarea></td>
	</tr>
</table>
<table width='100%' cellpadding="10px">
	<tr>
		<td width=100%>
			<input type="button" id='savebutton' value="Save" onclick="saveRecord()" />
		</td>
		<?php 
		if($record['id']){
			?><td><input type="button" style='background:red; color:white' value="Delete" onclick="deleteRecord('<?php echo $record['id']; ?>')" /></td><?php
		}
		?>
	</tr>
</table>
</form>
