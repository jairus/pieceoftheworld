<?php
$controller = "user";
@session_start();
$sid = session_id()."_".time();
?>
<script>
function saveRecord(approve){
	if( checkPassword() )
	{
		extra = "";
		jQuery("#savebutton").val("Saving...");
		formdata = jQuery("#record_form").serialize();
		jQuery("#record_form *").attr("disabled", true);
		jQuery.ajax({
			<?php
			if($record['id']){
				?>url: "<?php  echo site_url(); echo $controller ?>/ajax_edit"+extra,<?php
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
function checkPassword()
{
	if(jQuery('#password').attr("disabled") != false && jQuery('#password').val() != jQuery('#password_again').val()){
		alert("Please retype the password");
		jQuery('#password_again').focus();
		return false;
	}
	return true;
}

</script>

<input type='hidden' id='tempcreatelabel' />
<form id='record_form'>

<?php
if($record['id']){
	?>
	<input type='hidden' name='id' id='co_id'  value="" />
	<?php
}
else{
	?>
	<input type='hidden' name='sid' value="<?php echo sanitizeX($sid); ?>">
	<?php
}


?>
<table width="100%" cellpadding="10px">
<?php
if(!$record['id']){
	?>
	<tr>
	<td class='font18 bold'>Add a New Web User </td>
	<td></td>
	</tr>
	<?php
}
else{
	?>
	<tr>
	<td class='font18 bold'>Edit Web User</td>
	<td></td>
	</tr>
	<?php
}

?>
<tr>
<td width='50%'> 
	<table width="100%">
		<tr class="even required">
		  <td>* Email:</td>
		  <td><input type="text" name="email" size="40" autocomplete="off"></td>
		</tr>
		<tr class="odd" >
		  <td colspan="2"><a href="#" id="changePass" style="display: none">Change Password?</a>&nbsp;</td>		  
		</tr>					
		<tr class="even passHolder">
		  <td>Password:</td>
		  <td><input type="password" name="password" id="password" class="pass" size="40"   autocomplete="off"/></td>
		</tr>	
		<tr class="odd passHolder">
		  <td>Retype Password:</td>
		  <td><input type="password" name="password_again" id="password_again" class="pass" size="40"  /></td>
		</tr>	
		<tr class="even required">
		  <td>First Name:</td>
		  <td><input type="text" name="first_name" size="40"></td>
		</tr>
		<tr class="odd required">
		  <td>Last Name:</td>
		  <td><input type="text" name="last_name" size="40"></td>
		</tr>
		<tr class="even required">
		  <td>Is Admin?:</td>
		  <td>
			<input type="radio" name="is_admin" value="0" checked> No
			<input type="radio" name="is_admin" value="1"> Yes
		  </td>
		</tr>
	</table>
</td>
<td width='50%'>
	<table width="100%">
		<tr class="even required">
		  <td>City:</td>
		  <td><input type="text" name="city" size="40"></td>
		</tr>
		<tr class="odd required">
		  <td>State (USA):</td>
		  <td><input type="text" name="state_us" size="40"></td>
		</tr>
		<tr class="even required">
		  <td>State (Non-USA):</td>
		  <td><input type="text" name="state_nonus" size="40"></td>
		</tr>
		<tr class="odd required">
		  <td>Country:</td>
		  <td><input type="text" name="country" size="40"></td>
		</tr>
	</table>	
</td>
</tr>

<tr>
	<td colspan="2" class='center'>
		<table width='100%'>
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
	</td>
</tr>
</td>
</table>
</form>

<script>
<?php

if(is_array($pictures)){
	?>
	html = "";
	<?php
}
if($record){
	foreach($record as $key=>$value){	
		if($key=="is_admin"){
			?>
			jQuery('[name="<?php echo $key; ?>"][value="<?php echo $value; ?>"]').attr("checked", true);
			<?php
		}
		elseif(trim($value)||1){
			?>
			jQuery('[name="<?php echo $key; ?>"]').val("<?php echo sanitizeX($value); ?>");
			<?php
		}		
	}
	?>
	jQuery(".passHolder").hide();
	jQuery(".pass").attr("disabled", true);
	jQuery("#changePass").show();
	<?php
}
?>


jQuery("#changePass").click(function(e){
	e.preventDefault();
	jQuery(".passHolder").slideToggle(function(){		
		jQuery(".pass").attr("disabled", $(this).is(":hidden") );
	});
});
</script>
