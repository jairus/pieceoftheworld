<?php
session_start();
require_once('user_fxn.php');

$record['id'] = $_GET['id'];
$record['type'] = $_GET['type'];

$pictures = getPix($record['id'], $record['type']);

?>	
<script type="text/javascript" src="http://localhost/pieceoftheworld.co/admin2/media/js/uploadify/swfobject.js"></script>
<script type="text/javascript" src="http://localhost/pieceoftheworld.co/admin2/media/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<link rel="stylesheet" type="text/css" href="http://localhost/pieceoftheworld.co/admin2/media/js/uploadify/uploadify.css" media="screen" />

<script>
function saveRecord(approve){
	extra = "";
	jQuery("#savebutton").val("Saving...");
	formdata = jQuery("#record_form").serialize();
	//jQuery("#record_form *").attr("disabled", true);
	jQuery.ajax({
		url: "<?php echo site_url(); ?>ajax/user_fxn.php?action=upload&type=<?php echo $record['type']?>&recordId=<?php echo $record['id']?>",
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
	if(confirm("Are you sure you want to delete this image?")){
		index = ss.indexOf(filepath);
		ss.splice(index, 1);
		obj.parentElement.outerHTML = "";		
		return true;
	}
	return false;
}

var ss = [];
function refreshPictures(filepath){
	file = filepath.split(/\//g);
	file = file[file.length-1];
	filepath = escape(filepath);
	if(ss.indexOf(filepath)==-1){
		ss.push(filepath);
		html = jQuery("#sspathhtml").html();	
		html += "<div><a target='_blank' href='"+filepath+"'>"+file+"</a> <label><input type='radio' name='isMainPix' value='"+filepath+"' /> Set as Main Image</label>" +
				"<br/>Description: <input type='text' name='picture_titles[]' /><input type='hidden' name='pictures[]' value='"+filepath+"' /> <a style='cursor:pointer; text-decoration:underline' class='red delete' onclick='delSS(this, \""+filepath+"\")' >Delete</a></div><br/>";
		jQuery("#sspathhtml").html(html);
		
	}	
}

jQuery(function(){
	jQuery('#co_pictures').uploadify({
		'uploader'  : '<?php echo site_url(); ?>admin2/media/js/uploadify/uploadify.swf',
		'script'    : '<?php echo site_url(); ?>admin2/media/js/uploadify/uploadify.php',
		'cancelImg' : '<?php echo site_url(); ?>admin2/media/js/uploadify/cancel.png',		
		'folder'    : '<?php
			$folder = dirname(__FILE__)."/_uploads2/";
			if(!is_dir($folder)){
				mkdir($folder, 0777);
			}
			$folder = dirname(__FILE__)."/_uploads2/land/";
			if(!is_dir($folder)){
				mkdir($folder, 0777);
			}
			if($record['id']){
				$folder = dirname(__FILE__)."/_uploads2/land/".$record['id'];
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/_uploads2/land/".$record['id']."/images";
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
			}
			echo str_replace(dirname(__FILE__), "/..", $folder);						
			?>',				
		'auto'      : true,
		'multi'       : true,
		'onComplete'  : function(event, ID, fileObj, response, data) {
		  str = "";
		  for(x in fileObj){
		  	str += x+"\n";
		  }
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


<form id='record_form'>
	
	<div id='sspathhtml'></div>
	<input type='text' id="co_pictures" />
	<input type='button' class='button normal' value='Upload' onclick="jQuery('#co_pictures').uploadifyUpload();" >			  
	<input type='hidden' name='type' value='<?php echo $record['type']?>'>
	<input type='hidden' name='id' value='<?php echo $record['id']?>'>

	<input type="button" id='savebutton' value="Save" onclick="saveRecord()" />

</form>


<script>
<?php

if(!empty($pictures)){
	?>
	var html = "";
	<?php
	foreach($pictures as $value){
		?>
		filepath = "<?php echo urldecode($value['picture']); ?>";
		ss.push(filepath);
		file = "<?php echo (urldecode(basename($value['picture']))); ?>";
		title = "<?php echo ($value['title']); ?>";
		
		html += "<div><a target='_blank' href='"+filepath+"'>"+file+"</a> <label><input type='radio' name='isMainPix' value='"+filepath+"' <?php if($value['isMain']) echo 'checked'?> /> Set as Main Image</label>" +
				"<br>Description: <input type='text' name='picture_titles[]' value='"+title+"' /><input type='hidden' name='pictures[]' value='"+filepath+"' /> <a style='cursor:pointer; text-decoration:underline' class='red delete' onclick='delSS(this, \""+filepath+"\")' >Delete</a></div><br/>";
		<?php
	}
	?>
	jQuery("#sspathhtml").html(html);		
	<?php
}
?>
</script>