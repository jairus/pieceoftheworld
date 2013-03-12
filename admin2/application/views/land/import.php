<?php
$table = "companies";
$filename = microtime_float().".csv";
if($command=='old'){
	?>
	<script>
	function processFile(filepath){
		jQuery("#uploadbutton").attr("disabled", true);
		formdata = "filepath="+escape(filepath);
		if(jQuery("#skiph").attr("checked")){
			formdata += "&skipheaders=1";
		}
		jQuery("#processfile").html("<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />Processing...");
		jQuery.ajax({
			url: "<?php echo site_url().$table; ?>/import/processfileold",
			type: "POST",
			data: formdata,
			dataType: "html",
			success: function(html){
				jQuery("#processfile").html(html);
				jQuery("#uploadbutton").attr("disabled", false);
			}
		});
	}
	jQuery(function(){
		jQuery('#csvfile').uploadify({
				'uploader'  : '<?php echo site_url(); ?>media/js/uploadify/uploadify.swf',
				'script'    : '<?php echo site_url(); ?>media/js/uploadify/uploadify.php?filename=<?php echo $filename;?>',
				'cancelImg' : '<?php echo site_url(); ?>media/js/uploadify/cancel.png',
				'folder'    : '<?php
					$folder = dirname(__FILE__)."/../../../media/uploads/";
					if(!is_dir($folder)){
						mkdir($folder, 0777);
					}
					$folder = dirname(__FILE__)."/../../../media/uploads/csv";
					if(!is_dir($folder)){
						mkdir($folder, 0777);
					}
					$folder = dirname(__FILE__)."/../../../media/uploads/csv/".$table;
					if(!is_dir($folder)){
						mkdir($folder, 0777);
					}
					$folder = str_replace(dirname(__FILE__)."/../../..", "", $folder);
					echo $folder;
				?>',
				'auto'      : false,
				'multi'       : false,
				'onComplete'  : function(event, ID, fileObj, response, data) {
				  //alert('There are ' + data.fileCount + ' files remaining in the queue.');
				  str = "";
				  for(x in fileObj){
					str += x+"\n";
				  }
				  //alert(str);
				  //alert(fileObj.filePath);
				  //refreshFiles("<?php echo $table; ?>");
				  processFile("<?php echo $folder."/".$filename; ?>");
				  
				}	
			});
	});
	</script>
	
	<div class='pad10' style='width:400px; margin:auto' >
		Upload CSV File (OLD VERSION)[ <a href='<?php echo site_url().$table; ?>/import/samplecsvold' >DOWNLOAD OLD SAMPLE CSV FILE</a> ]
		<br><input type='checkbox' id='skiph' checked="checked" >Skip Headers <div class='hint'>Check to skip the 1st 2 lines of the CSV file</div><br><input type='text' id="csvfile" />
		<input type='button' id='uploadbutton' class='button normal' value='Upload and Process' onclick="jQuery('#csvfile').uploadifyUpload();" >
		<div id='processfile'></div>
	</div>
	<?php
}
else{
	?>
	<script>
	function processFile(filepath){
		jQuery("#uploadbutton").attr("disabled", true);
		formdata = "filepath="+escape(filepath);
		if(jQuery("#skiph").attr("checked")){
			formdata += "&skipheaders=1";
		}
		jQuery("#processfile").html("<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />Processing...");
		jQuery.ajax({
			url: "<?php echo site_url().$table; ?>/import/processfile",
			type: "POST",
			data: formdata,
			dataType: "html",
			success: function(html){
				jQuery("#processfile").html(html);
				jQuery("#uploadbutton").attr("disabled", false);
			}
		});
	}
	jQuery(function(){
		jQuery('#csvfile').uploadify({
				'uploader'  : '<?php echo site_url(); ?>media/js/uploadify/uploadify.swf',
				'script'    : '<?php echo site_url(); ?>media/js/uploadify/uploadify.php?filename=<?php echo $filename;?>',
				'cancelImg' : '<?php echo site_url(); ?>media/js/uploadify/cancel.png',
				'folder'    : '<?php
					$folder = dirname(__FILE__)."/../../../media/uploads/";
					if(!is_dir($folder)){
						mkdir($folder, 0777);
					}
					$folder = dirname(__FILE__)."/../../../media/uploads/csv";
					if(!is_dir($folder)){
						mkdir($folder, 0777);
					}
					$folder = dirname(__FILE__)."/../../../media/uploads/csv/".$table;
					if(!is_dir($folder)){
						mkdir($folder, 0777);
					}
					$folder = str_replace(dirname(__FILE__)."/../../..", "", $folder);
					echo $folder;
				?>',
				'auto'      : false,
				'multi'       : false,
				'onComplete'  : function(event, ID, fileObj, response, data) {
				  //alert('There are ' + data.fileCount + ' files remaining in the queue.');
				  str = "";
				  for(x in fileObj){
					str += x+"\n";
				  }
				  //alert(str);
				  //alert(fileObj.filePath);
				  //refreshFiles("<?php echo $table; ?>");
				  processFile("<?php echo $folder."/".$filename; ?>");
				  
				}	
			});
	});
	</script>
	
	<div class='pad10' style='width:400px; margin:auto' >
		Upload CSV File [ <a href='<?php echo site_url().$table; ?>/import/samplecsv' >DOWNLOAD SAMPLE CSV FILE</a> ]
		<br><input type='checkbox' id='skiph' checked="checked" >Skip Headers <div class='hint'>Check to skip the 1st line of the CSV file</div><br><input type='text' id="csvfile" />
		<input type='button' class='button normal' value='Upload and Process' onclick="jQuery('#csvfile').uploadifyUpload();" >
		<div id='processfile'></div>
	</div>
	<?php
}
?>