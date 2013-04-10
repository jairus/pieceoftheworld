<?php
@session_start();
$sid = session_id()."_".time();
//changed for revisions
if(!$changed){
	$changed = array();
}

$post = array();
$imageurl = "";
$absfolder = dirname(__FILE__)."/../../../../_uploads/".trim($record['folder']);
$filename = $absfolder."/post.txt";
if(file_exists($filename)){
	$post = (file_get_contents($filename));
	$post = unserialize($post);
	$record['title'] = $post['title_name'];
	$record['detail'] = $post['detail_name'];
	$record['land_owner'] = $post['land_owner'];
	$record['useremail'] = $post['useremail'];
	/*
	Array
	(
		[save] => 1
		[step] => 2
		[pass] => A631CD74-1D21-40b1-8602-346611127127
		[land] => 101130-220106_101131-220106
		[useremail] => melissa.birdvogel@gmail.com
		[title_name] => Home is where the heart is
		[land_owner] => Melissa
		[detail_name] => 
		[button_name] =>   Submit  
	)
	*/
	$imageurl = basename($post['filename']);
}

//echo "<pre>";
//print_r($record);
//echo "</pre>";
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
			?>url: "<?php echo site_url(); ?>packages/ajax_edit"+extra,<?php
		}
		else{
			?>url: "<?php echo site_url(); ?>packages/ajax_add"+extra,<?php
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
			url: "<?php echo site_url(); ?>packages/ajax_delete/"+co_id,
			type: "POST",
			data: formdata,
			dataType: "script",
			success: function(){
				jQuery("#tr"+co_id).fadeOut(200);
				self.location = "<?php echo site_url(); ?>products";
			}
		});
		
	}
}

function showConfig(val){
	jQuery(".configs").hide();
	jQuery("#"+val).show();
}


function refreshLogo(logopath){
	logopath = escape(logopath);
	jQuery("#logopathhtml").html("<img src='<?php echo site_url(); ?>media/image.php?p="+logopath+"&mx=220&_="+(new Date().getTime())+"' />");
	jQuery("#logopath").val(logopath);
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


jQuery(function(){
	<?php
	/*
	jQuery('#rec_logo').uploadify({
		'uploader'  : '<?php echo site_url(); ?>media/js/uploadify/uploadify.swf',
		'script'    : '<?php echo site_url(); ?>media/js/uploadify/uploadify.php',
		'cancelImg' : '<?php echo site_url(); ?>media/js/uploadify/cancel.png',
		'folder'    : '<?php
			$folder = dirname(__FILE__)."/../../../media/uploads/";
			if(!is_dir($folder)){
				mkdir($folder, 0777);
			}
			$folder = dirname(__FILE__)."/../../../media/uploads/packages";
			if(!is_dir($folder)){
				mkdir($folder, 0777);
			}
			if($record['id']){
				$folder = dirname(__FILE__)."/../../../media/uploads/packages/".$record['id'];
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/../../../media/uploads/packages/".$record['id']."/logo";
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
			}
			else{
				$folder = dirname(__FILE__)."/../../../media/uploads/temp";
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/../../../media/uploads/temp/".$sid ;
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/../../../media/uploads/temp/".$sid."/logo";
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
			}
			echo str_replace(dirname(__FILE__)."/../../..", "", $folder);
		?>',
		'auto'      : true,
		'multi'       : false,
		'onComplete'  : function(event, ID, fileObj, response, data) {
		  //alert('There are ' + data.fileCount + ' files remaining in the queue.');
		  str = "";
		  for(x in fileObj){
		  	str += x+"\n";
		  }
		  //alert(str);
		  //alert(fileObj.filePath);		  
		  logopath = "<?php echo trim(site_url(),"/"); ?>"+fileObj.filePath;
		  refreshLogo(logopath);
		}	
	});
	*/
	?>
});


</script>

<input type='hidden' id='tempcreatelabel' />
<form id='record_form'>

<?php
if($record['id']){
	?>
	<input type='hidden' name='id' id='co_id' >
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
	<td class='font18 bold'>Add a New Special Land</td>
	<td></td>
	</tr>
	<?php
}
else{
	?>
	<tr>
	<td class='font18 bold'>Edit Special Land</td>
	<td></td>
	</tr>
	<?php
}

?>
<tr>
<td width='50%'> 
  <table width="100%">

    <tr class="odd required">
      <td>* Title:</td>
      <td><input type="text" name="title" size="40"></td>
    </tr>
    <tr class="even required">
      <td>* Detail:</td>
      <td><textarea name="detail"></textarea></td>
    </tr>
  </table>
</td>
<td width='50%'>
	<table width="100%">
		<tr class="odd" >
		  <td>Image:</td>
		  <td>
			<?php
			if($imageurl){
				?><a href="<?php echo "/_uploads/".$record['folder']."/".$imageurl;	?>" target='_blank'>IMG</a><?php
			}
			else if(trim($record['picture'])){
				?><a href="/theimage.php?id=<?php echo $record['id']; ?>" target='_blank'>IMG (old&nbsp;system)</a><?php
			}
			else{
				echo "3";
			}
			?></td>
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
		else if($key=="ultimate"){
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
		else if($key=="products"){
			$t = count($value);
			for($i=0; $i<$t; $i++){
				?>
				addProduct(<?php echo sanitizeX($value[$i]['id']); ?>, "<?php echo sanitizeX($value[$i]['name']); ?>");
				<?php
			}
		}
		else if($key=="logo"&&trim($value)){
			?>
			jQuery('#logopath').val("<?php echo sanitizeX($value); ?>");
			jQuery("#logopathhtml").html("<img src='<?php echo site_url(); ?>media/image.php?p=<?php echo $value ?>&mx=220' />");
			<?php
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
