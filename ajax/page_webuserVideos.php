<?php
session_start();
require_once('user_fxn.php');

$record['id'] = $_GET['id'];
$record['type'] = $_GET['type'];

$videos = getVideos($record['id'], $record['type']);
?>
<style>
    .vidHolder textarea{
        width: 330px;
        height: 50px;
    }
</style>

<script>
function saveRecord(approve){
	extra = "";
	jQuery("#savebutton").val("Saving...");
	formdata = jQuery("#record_form").serialize();
	//jQuery("#record_form *").attr("disabled", true);
	jQuery.ajax({
		url: "<?php echo site_url(); ?>ajax/user_fxn.php?action=uploadVideo&type=<?php echo $record['type']?>&recordId=<?php echo $record['id']?>",
		type: "POST",
		data: formdata,
		dataType: "json",
		success: function(data){
			jQuery("#savebutton").val("Save");
			alert(data.message);
		}
	});
}
function delSS(obj, filepath){
	if(confirm("Are you sure you want to delete this video link?")){
		index = ss.indexOf(filepath);
		ss.splice(index, 1);
		obj.parentElement.outerHTML = "";		
		return true;
	}
	return false;
}

jQuery(function(){
    $("#addMore").click(function(e){
        e.preventDefault();
        $("#vidMainHolder").append( $("#baseVidHolder").html() );
    });
    $(".removeHolder").live('click',function(e){
        e.preventDefault();
        $(this).parent().remove();
    });


});
</script>


<form id='record_form'>
    <input type='hidden' name='type' value='<?php echo $record['type']?>'>
    <input type='hidden' name='id' value='<?php echo $record['id']?>'>
    <div id="baseVidHolder" class="hide">
        <div class="vidHolder">
            <label></label><a href='#' class='removeHolder'>remove this</a><br/>
            <label>YouTube Embed Script: </label><br/>
            <textarea name="video_link[]" rows="3" cols="10" class="videoLink"></textarea><br/>
            <label>Title: </label><input type="text" name="video_title[]" class="videoLink" /><br/>
            <br/>
        </div>
    </div>

    <div id="vidMainHolder"></div>
    <a href="#" id="addMore">Add Another Link</a><br/>

	<input type="button" id='savebutton' value="Save" onclick="saveRecord()" />

</form>


<script>
<?php

if(!empty($videos)){
	?>
	var html = "";
	<?php
	foreach($videos as $row){
		?>
		html = '<div class="vidHolder">' +
                    '<label></label><a href="#" class="removeHolder">remove this</a><br/>' +
                    '<label>YouTube Embed Script:</label><br/><textarea name="video_link[]" class="videoLink"><?php echo $row['video']?></textarea><br/>' +
                    '<label>Title: </label><input type="text" name="video_title[]" class="videoLink" value="<?php echo $row['title']?>" /><br/>' +
                    '<br/>' +
                '</div>';
        jQuery("#vidMainHolder").append(html);
        <?php
	}
	?>
	<?php
} else {
?>
    jQuery("#vidMainHolder").append( $("#baseVidHolder").html() );
<?php
}
?>
</script>