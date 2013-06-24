<!doctype html>
<html lang="us">
<head>
<meta charset="utf-8">
<title>PieceoftheWorld</title>
<script src="http://cdn.pieceoftheworld.co/js/jquery-1.8.3.min.js" type="text/javascript"></script>
<script>
function openGallery(){
	jQuery.ajax({
		type: 'GET',
		url: "ajax/ajax.php?action=open_gallery&land_id="+jQuery('#land_id').val(),
		data: "",
	
		success: function(data) {
			jQuery("#gallery_tab_wrapperonly").html(data);
			jQuery('#galleryresults').fadeIn(200);
		}
	});
}
</script>
</head>
<body>
<div id='galleryresults'>
	<div id='gallery_tab_wrapperonly'></div>
</div>
<input type="button" onClick="openGallery();" value="open gallery" />
</body>
</html>