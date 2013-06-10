<?php
$controller = "specialland";
@session_start();
$sid = session_id()."_".time();
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
function saveVideo(){
    extra = "";
    formdata = jQuery("#record_form").serialize();
    jQuery.ajax({
        <?php
        if($record['id']){
            ?>url: "<?php echo site_url(); echo $controller ?>/ajax_saveVideo/"+extra,<?php
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

function showConfig(val){
	jQuery(".configs").hide();
	jQuery("#"+val).show();
}


function defaultHTML(){
	jQuery('#htmlpage').val(jQuery('#defaulthtml').html());
	//jQuery('#htmlpage').tinymce().setContent("");
	//jQuery('#htmlpage').tinymce().setContent(jQuery('#defaulthtml').html());
	//jQuery('#htmlpage').tinymce().execCommand('mceInsertContent',false,jQuery('#defaulthtml').html());	
}
function setHTML(html){
	jQuery('#htmlpage').tinymce().setContent("");
	jQuery('#htmlpage').tinymce().setContent(html);
}

var ss = [];
function refreshPictures(filepath){
	file = filepath.split(/\//g);
	file = file[file.length-1];
	filepath = escape(filepath);
	if(ss.indexOf(filepath)==-1){
		ss.push(filepath);
		html = jQuery("#sspathhtml").html();	
		html += "<div><a target='_blank' href='<?php echo site_url(); ?>media/image.php?p="+filepath+"'>"+file+"</a> <label><input type='radio' name='isMainPix' value='"+filepath+"' /> Set as Main Image</label>" +
				"<br/><input type='text' name='picture_titles[]' /><input type='hidden' name='pictures[]' value='"+filepath+"' /><div class='hint'>Description</div>&nbsp;&nbsp;&nbsp;<a style='cursor:pointer; text-decoration:underline' class='red delete' onclick='this.parentElement.outerHTML=\"\"' >Delete</a></div><br/>";
		jQuery("#sspathhtml").html(html);
	}
	//jQuery("#logopath").val(filepath);
}
jQuery(function(){
	jQuery('#co_pictures').uploadify({
		'uploader'  : '<?php echo site_url(); ?>media/js/uploadify/uploadify.swf',
		'script'    : '<?php echo site_url(); ?>media/js/uploadify/uploadify.php',
		'cancelImg' : '<?php echo site_url(); ?>media/js/uploadify/cancel.png',
		'folder'    : '<?php
			$folder = dirname(__FILE__)."/../../../../_uploads2/";
			if(!is_dir($folder)){
				mkdir($folder, 0777);
			}
			$folder = dirname(__FILE__)."/../../../../_uploads2/specialland/";
			if(!is_dir($folder)){
				mkdir($folder, 0777);
			}
			if($record['id']){			
				$folder = dirname(__FILE__)."/../../../../_uploads2/specialland/".$record['id'];
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/../../../../_uploads2/specialland/".$record['id']."/images";
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
			}
			else{
				$folder = dirname(__FILE__)."/../../../../_uploads2/specialland/temp";
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/../../../../_uploads2/specialland/temp/".$_GET['idx'];
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/../../../../_uploads2/specialland/temp/".$_GET['idx'];
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}

			}
			echo str_replace(dirname(__FILE__)."/../../..", "", $folder);			
		?>',
		'auto'      : true,
		'multi'       : true,
		'onComplete'  : function(event, ID, fileObj, response, data) {
		  //alert('There are ' + data.fileCount + ' files remaining in the queue.');
		  str = "";
		  for(x in fileObj){
		  	str += x+"\n";
		  }
		  //alert(str);
		  //alert(fileObj.filePath);		  
		  fp = fileObj.filePath;
		  //remove slash at fron of path
		  while(fp[0]=='/'){
		  	fp = fp.substring(1);
		  }
		  
		  filepath = "<?php echo site_url(); ?>"+fp;
		  refreshPictures(filepath);
		}	
	});	
	jQuery("#webuser_search").autocomplete({		
		source: function(req, add){
			//pass request to server
			jQuery.getJSON("<?php echo site_url(); ?>webuser/ajax_search", req, function(data) {
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
			jQuery("#web_user_id").val(value);
			jQuery("#web_user_name").val(label);			
			return false;
		},
		focus: function(e, ui) {
			label = ui.item.label;
			value = ui.item.value;
			jQuery("#webuser_search").val(label);
			return false;
		}
	});	
	jQuery("#webuser_search").blur(function(e){
		if( jQuery('#webuser_search').val() == '' || (jQuery('#webuser_search').val() != jQuery('#web_user_name').val()) ){
			jQuery("#web_user_id").val('');
		}
	});
    $("#addMore").click(function(e){
        e.preventDefault();
        //$("#vidMainHolder").append( $("#baseVidHolder").html() );
        saveVideo();

    });
    $(".removeHolder").live('click',function(e){
        e.preventDefault();
        $(this).parent().remove();
    });

});


</script>

<input type='hidden' id='tempcreatelabel' />
<form id='record_form'>

<?php
if($record['id']){
	?>
	<input type='hidden' name='id' id='co_id'  value="" />
	<input type='hidden' name='land_detail_id' id=''  value="" />
	<input type='hidden' name='land_special_id' id=''  value="" />
	<?php
}
else{
	?>
	<input type='hidden' name='sid' value="<?php echo sanitizeX($_GET['idx']); ?>">
	<?php
}

if($_SESSION[$_GET['idx']]){
	$add_data = json_decode(stripslashes($_SESSION[$_GET['idx']]));
}

?>
<table width="100%" cellpadding="10px">
<?php
if(!$record['id']){
	?>
	<tr>
	<td class='font18 bold'>Add a New Land </td>
	<td></td>
	</tr>
	<?php
}
else{
	?>
	<tr>
	<td class='font18 bold'>Edit Land</td>
	<td></td>
	</tr>
	<?php
}

?>
<tr>
<td width='50%'> 
	<?php
	if(!$record&&count($add_data->points)){
		$t = count($add_data->points);
		for($i=0; $i<$t; $i++){
			?><input type='hidden' value="<?php echo $add_data->points[$i]; ?>" name='points[]'><?php
		}
	}
	?>
	<table width="100%">
		<tr class="even required">
		  <td>* Title:</td>
		  <td><input type="text" name="title" size="40">&nbsp;
		  <?php
		  if($record){
			?><a target='_blank' href='/index.php?xy=<?php echo $record['points'][0]['x']."~".$record['points'][0]['y']?>'>View in Map</a><?php
		  }
		  else{
			if($add_data->points[0]){
				list($x, $y) = explode("-",$add_data->points[0]);
				?><a href='/addspecial.php?xy=<?php echo $x."~".$y; ?>&idx=<?php echo $_GET['idx']; ?>'>View in Map</a><?php
			}
		  }
		  ?>
		  </td>
		</tr>
		<tr class="odd required">
		  <td>* Detail:</td>
		  <td><textarea name="detail"></textarea></td>
		</tr>		
		<tr class="even required">
		  <td>* Price:</td>
		  <td><input type="text" name="price" size="40"></td>
		</tr>		
		<tr class="even">
		  <td>Land Owner:</td>
		  <td><input type="text" name="land_owner" size="40"></td>
		</tr>	
		<tr class="odd">
		  <td>Web User:</td>
		  <td><input type="text" id="webuser_search" name="useremail" size="40" />
				<input type="hidden" id="web_user_id" name="web_user_id" size="40" >
				<input type="hidden" id="web_user_name" size="40" >
		  </td>
		</tr>
		<tr class="odd">
		  <td>Date Bought:</td>
		  <td><input type="text" name="datebought" size="40" /><div class='hint'>YYYY-MM-DD </div>
		  </td>
		</tr>
        <tr class="even">
            <td>Category:</td>
            <td>
                <select name="category_id" id="category_id">
                    <option value=""></option>
                    <?php foreach($categories as $cat){
                        $selected = ($cat['id'] == $record['category_id'])? 'selected' : '';
                        ?>
                        <option value="<?php echo $cat['id']?>" <?php echo $selected ?> ><?php echo $cat['name']?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
		
	</table>
</td>
<td width='50%'>
	<table width="100%">

        <tr class="odd required">
		  <td>Picture:</td>
		  <td>
			  <div id='sspathhtml' style='padding-bottom:10px;'></div>
			  <input type='text' id="co_pictures" />
			  <input type='button' class='button normal' value='Upload' onclick="jQuery('#co_pictures').uploadifyUpload();" >			  
		  </td>
		</tr>
        <tr><td colspan="2"><hr/></td></tr>
        <tr class="odd required">
            <td>Videos:</td>
            <td>
                <div id="baseVidHolder" style="display: none">
                    <div class="vidHolder">
                        <!-- <label></label><a href='#' class='removeHolder'>remove this</a><br/> -->
                        <label>YouTube Embed Script: </label><br/>
                        <textarea name="video_link[]" rows="3" cols="10" class="videoLink"></textarea><br/>
                        <label>Title: </label><input type="text" name="video_title[]" class="videoLink" /><br/>
                        <br/>
                    </div>
                </div>

                <div id="vidMainHolder"></div>
                <input type="button" id="addMore" value="Add Video" />
            </td>
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
if(!empty($videos)){
?>
    var html = "";
    <?php
        foreach($videos as $row){
    ?>
    html = '<div class="vidHolder">' +
        //'<label></label><a href="#" class="removeHolder">remove this</a><br/>' +
        '<label>YouTube Embed Script:</label><br/><textarea name="video_link[]" class="videoLink"><?php echo $row['video']?></textarea><br/>' +
        '<label>Title: </label><input type="text" name="video_title[]" class="videoLink" value="<?php echo $row['title']?>" /><br/>' +
        '<br/>' +
        '</div>';
    jQuery("#vidMainHolder").append(html);
<?php
        }
} else {
?>
jQuery("#vidMainHolder").append( $("#baseVidHolder").html() );
<?php
}
?>
<?php

if(is_array($pictures)){
	?>
	html = "";
	<?php
	foreach($pictures as $value){
		?>
		filepath = "<?php echo sanitizeX($value['picture']); ?>";
		ss.push(filepath);
		file = "<?php echo sanitizeX(urldecode(basename($value['picture']))); ?>";
		title = "<?php echo sanitizeX($value['title']); ?>";
		html += "<div><a target='_blank' href='<?php echo site_url(); ?>media/image.php?p="+filepath+"'>"+file+"</a> <label><input type='radio' name='isMainPix' value='"+filepath+"' <?php if($value['isMain']) echo 'checked'?> /> Set as Main Image</label>" +
				"<br><input type='text' name='picture_titles[]' value='"+title+"' /><div class='hint'>Description</div><input type='hidden' name='pictures[]' value='"+filepath+"' />&nbsp;&nbsp;&nbsp;<a onclick='this.parentElement.outerHTML=\"\"' style='cursor:pointer; text-decoration:underline' >Delete</a></div><br/>";
		<?php
	}
	?>
	jQuery("#sspathhtml").html(html);
	<?php
}
if($record){
	foreach($record as $key=>$value){
		if($key=="points"||$key=="picture"){
		}
		else if($key=="active"){
			if($value=="1"){
				?>
				jQuery('[name="<?php echo $key; ?>"]').attr("checked", true);
				<?php
			}
			else{
				?>
				jQuery('[name="<?php echo $key; ?>"]').attr("checked", false);
				<?php
			}
		}
		
		else if(trim($value)||1){
			?>
			jQuery('[name="<?php echo $key; ?>"]').val("<?php echo sanitizeX($value); ?>");
			<?php
		}
		
	}
}
?>	
</script>