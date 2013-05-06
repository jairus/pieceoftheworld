<?php
session_start();
require_once('user_fxn.php');

$record['id'] = $_GET['id'];
$record['type'] = $_GET['type'];

$rs = getTags($record['id'], $record['type']);
$cats = getCategories();

?>
    <link href="<?php echo site_url() ?>tag-it/css/jquery.tagit.css" rel="stylesheet" type="text/css">
    <link href="<?php echo site_url() ?>tag-it/css/tagit.ui-zendesk.css" rel="stylesheet" type="text/css">
		
	<script src="<?php echo site_url() ?>tag-it/js/tag-it.js" type="text/javascript" charset="utf-8"></script>

	
<script>


function saveRecord(approve){
	extra = "";
	jQuery("#savebutton").val("Saving...");
	formdata = jQuery("#record_form").serialize();
	//jQuery("#record_form *").attr("disabled", true);
	jQuery.ajax({
		url: "<?php echo site_url(); ?>ajax/user_fxn.php?action=saveTags",
		type: "POST",
		data: formdata,
		dataType: "json",
		success: function(data){
			jQuery("#savebutton").val("Save");
			alert(data.message);
			jQuery('#oldTags').val(data.tags);
		}
	});
}
$(function(){
		
	$('#landTags').tagit({	
		allowSpaces: true,
		itemName: 'item',
		fieldName: 'tags[]'

	});

});	
</script>


<form id='record_form'>
	<input type='hidden' name='id' value='<?php echo $record['id']?>' />
	<input type='hidden' name='type' value='<?php echo $record['type']?>' />
	<input type='hidden' name='oldTags' id='oldTags' value='<?php if(isset($rs['tags']) && $rs['tags'] != '') echo $rs['tags']?>' />
		
	<label>Category: </label>
	<select name='category_id'>
		<option value=''></option>
	<?php foreach($cats as $id => $value){?>
		<?php $selected = ($id == $rs['category_id'])? 'selected' : ''; ?>
		<option value='<?php echo $id?>' <?php echo $selected?> > <?php echo $value?></option>
	<?php } ?>
	</select>	
	
	<br/><br/>
	
	<label>Tags (comma separated):</label>		
	<ul id="landTags">	
	<?php 	if(isset($rs['tags']) && $rs['tags'] != ''){ 
				$tagArr = explode(',',$rs['tags']);
				foreach($tagArr as $t){
	?>
					<li><?php echo $t?></li>
	<?php
				}
			} 
	?>
	</ul>	
	<input type="button" id='savebutton' value="Save" onclick="saveRecord()" />

</form>