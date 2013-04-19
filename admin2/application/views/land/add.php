<?php
$controller = "land";
@session_start();
$sid = session_id()."_".time();
?>
<script>
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
				"<br/><input type='text' name='picture_titles[]' /><input type='hidden' name='pictures[]' value='"+filepath+"' /><div class='hint'>Description</div>&nbsp;&nbsp;&nbsp;<a style='cursor:pointer; text-decoration:underline' class='red delete' onclick='delSS(this, \""+filepath+"\")' >Delete</a></div><br/>";
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
			$folder = dirname(__FILE__)."/../../../../_uploads2/land/";
			if(!is_dir($folder)){
				mkdir($folder, 0777);
			}
			if($record['id']){
				$folder = dirname(__FILE__)."/../../../../_uploads2/land/".$record['id'];
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/../../../../_uploads2/land/".$record['id']."/images";
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
			}
			// else{
				// $folder = dirname(__FILE__)."/../../../media/uploads/temp";
				// if(!is_dir($folder)){
					// mkdir($folder, 0777);
				// }
				// $folder = dirname(__FILE__)."/../../../media/uploads/temp/".$sid;
				// if(!is_dir($folder)){
					// mkdir($folder, 0777);
				// }
				// $folder = dirname(__FILE__)."/../../../media/uploads/temp/".$sid."/images";
				// if(!is_dir($folder)){
					// mkdir($folder, 0777);
				// }
			// }
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
});


</script>

<input type='hidden' id='tempcreatelabel' />
<form id='record_form'>

<?php
if($record['id']){
	?>
	<input type='hidden' name='id' id='co_id'  value="" />
	<input type='hidden' name='land_detail_id' id=''  value="" />
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
	<table width="100%">
		<tr class="even required">
		  <td>* X:</td>
		  <td><input type="text" name="x" size="40" readonly="readonly" /></td>
		</tr>
		<tr class="odd required">
		  <td>* Y:</td>
		  <td><input type="text" name="y" size="40" readonly="readonly" /></td>
		</tr>
		<tr class="even required">
		  <td>* Title:</td>
		  <td><input type="text" name="title" size="40"></td>
		</tr>
		<tr class="odd required">
		  <td>* Detail:</td>
		  <td><textarea name="detail"></textarea></td>
		</tr>		
		<tr class="even required">
		  <td>* Land Owner:</td>
		  <td><input type="text" name="land_owner" size="40"></td>
		</tr>	
		
	</table>
</td>
<td width='50%'>
	<table width="100%">
		<tr class="even required">
		  <td>* Picture:</td>
		  <td>
			  <div id='sspathhtml' style='padding-bottom:10px;'></div>
			  <input type='text' id="co_pictures" />
			  <input type='button' class='button normal' value='Upload' onclick="jQuery('#co_pictures').uploadifyUpload();" >			  
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
		if($key=="active"){
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
