<?php
@session_start();

if($_POST['btn_upload_image']){
	$foldername = $_SESSION['foldername'];

	$uploads_dir = dirname(__FILE__).'/_uploads/'.$foldername;
	$uploads_http = 'localhost/_uploads/'.$foldername;
	@mkdir($uploads_dir, 0777);
	$filename = $uploads_dir."/post.txt";
	
	if($_FILES["image"]["error"] == UPLOAD_ERR_OK) {
		$tmp_name = $_FILES["image"]["tmp_name"];
		$name = $_FILES["image"]["name"];
		move_uploaded_file($tmp_name, "$uploads_dir/$name");
		$post['filename'] = "$uploads_dir/$name";
		$http_picture = $uploads_http."/$name";
		$post['http_picture'] = $http_picture;
	}
	
	file_put_contents($filename, serialize($post));
	
	$http_picture = str_replace('localhost/', '', $post['http_picture']);
	
	header("Location:imageuploader.php?success=1&image=".$name);
}
?>
<!doctype html>
<html lang="us">
<head>
<meta charset="utf-8">
<title>PieceoftheWorld</title>
<style>
.longbutton{
	background-color: #006A9B;
	float: left;
	height: auto;
	padding: 5px;
	text-align: center;
	width: 100%;
	color: #FFFFFF;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 14px;
    font-weight: bold;
	border:0px;
	cursor:pointer;
	margin-bottom:3px;
	border:1px solid white;
}
</style>
<script src="http://cdn.pieceoftheworld.co/js/jquery-1.8.3.min.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
	var success = '<?php echo $_GET['success']; ?>';

	if(success==1){
		parent.jQuery('#btn_upload_cover_images').hide();
		parent.jQuery('#div_image').append('<img src="_uploads/<?php echo $_SESSION['foldername'].'/'.$_GET['image']; ?>" width="245" border="0" />');
		parent.jQuery.colorbox.close();
	}
});
</script>
<body>
<form id="form_image_upload" name="form_image_upload" method="post" enctype="multipart/form-data">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td style="padding-bottom:20px; text-align:center;"><input type="file" id="image" name="image" /></td>
  </tr>
  <tr>
    <td><input type="submit" id="btn_upload_image" name="btn_upload_image" value="Upload image" class="longbutton" /></td>
  </tr>
</table>
</form>
</body>
</html>