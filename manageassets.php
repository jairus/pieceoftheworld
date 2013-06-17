<?php
include_once(dirname(__FILE__)."/emailer/email.php");
require_once('ajax/user_fxn.php');
session_start();
?>
<!doctype html>
<html lang="us">
<head>
<meta charset="utf-8">
<title>PieceoftheWorld</title>
<script src="js/jquery-1.8.3.min.js" type="text/javascript"></script>
<style>
.hint{
	padding-left:20px;
	font-size:10px;
	font-style:italic;
	display:inline;
}
.header{
	padding:10px;
	background-color: #006A9B;
	color: #FFFFFF;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 14px;
    font-weight: bold;
	
}
.longbutton{
	background-color: #006A9B;
	float: left;
	height: auto;
	padding: 5px;
	text-align: center;
	width: 290px;
	color: #FFFFFF;
    font-family: Arial,Helvetica,sans-serif;
    font-size: 14px;
    font-weight: bold;
	border:0px;
	cursor:pointer;
	margin-bottom:3px;
	border:2px solid white;
}
body{
	background: #008aba;
}
a:link, a:hover, a:visited{
	font-family: Arial,Helvetica,sans-serif;
    font-size: 14px;
	color: white;
}
td, div, form{
	font-family: Arial,Helvetica,sans-serif;
    font-size: 14px;
	color: white;
}
</style>
<script>
function consoleX(str){
	try{
		console.log(str);
	}
	catch(e){
	}
}
</script>
<body style="cursor: auto;">
<div id="fb-root"></div>
<?php
$web_user_id = $_SESSION['userdata']['id'];

if($_GET['assettype']=='videos'){
	if($_GET['landtype']=='land_detail'){ //normal land
		if($_POST){
			$http = $_POST['video'];
			if(trim($http)){
				$sql = "insert into `videos` set
					`video` = '".$http."',
					`created_on` = NOW(),
					`land_id`='".$_GET['id']."'
				";
				dbQuery($sql);
			}
			
		}
		if($_GET['set']=='primary'&&$_GET['videoid']){ //set as primary
			$sql = "select * from `videos` where 
				`land_id` = '".mysql_real_escape_string($_GET['id'])."'  and `id`='".mysql_real_escape_string($_GET['videoid'])."'
				and `land_id` in (select `land_detail_id` from `land` where `web_user_id`='".$web_user_id."')
				order by `isMain` desc";
			$picture = dbQuery($sql, $_dblink);
			if($picture[0]['id']){
				$sql = "update `videos` set `isMain`=0 where 
				`land_id` = '".mysql_real_escape_string($_GET['id'])."'";
				dbQuery($sql, $_dblink);
				$sql = "update `videos` set `isMain`=1 where 
				`land_id` = '".mysql_real_escape_string($_GET['id'])."' and `id`='".mysql_real_escape_string($_GET['videoid'])."'";
				dbQuery($sql, $_dblink);
			}
			?>
			<script>
			window.parent.getLands("<?php echo $_GET['idback']; ?>");
			</script>
			<?php
		}
		else if($_GET['set']=='delete'&&$_GET['videoid']){ //set as primary
			$sql = "select * from `videos` where 
				`land_id` = '".mysql_real_escape_string($_GET['id'])."'  and `id`='".mysql_real_escape_string($_GET['videoid'])."'
				and `land_id` in (select `land_detail_id` from `land` where `web_user_id`='".$web_user_id."')
				order by `isMain` desc";
			$picture = dbQuery($sql, $_dblink);
			if($picture[0]['id']){
				$sql = "delete from `videos` where 
				`land_id` = '".mysql_real_escape_string($_GET['id'])."' and `id`='".mysql_real_escape_string($_GET['videoid'])."'";
				dbQuery($sql, $_dblink);
			}
			?>
			<script>
			window.parent.getLands("<?php echo $_GET['idback']; ?>");
			</script>
			<?php
		}
		
		$sql = "select * from `videos` where 
			`land_id` = '".mysql_real_escape_string($_GET['id'])."' 
			and `land_id` in (select `land_detail_id` from `land` where `web_user_id`='".$web_user_id."')
			order by `isMain` desc";
		$videos = dbQuery($sql, $_dblink);
		$pt = count($videos);
		?>
		<div style='text-align:center; padding:20px;'>
		<form action="manageassets.php?assettype=videos&landtype=land_detail&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>" method="post" enctype="multipart/form-data">
		Youtube Video Link<br /><i>e.g. http://www.youtube.com/watch?v=4M6Y06w834w</i><br />
		<input type="text" name="video" style='width:400px;'>&nbsp;<input type="submit" name="submit" value="Add Video">
		</form>
		</div>
		<?php
		if($pt){
			echo "<table style='margin-top:1px;' cellpadding=0 cellspacing=4 >";
			for($j=0; $j<$pt; $j++){
				$videoid = explode("v=",$videos[$j]['video']);
				$videoid = explode("&",$videoid[1]);
				$videoid = $videoid[0];
				$iframe = '<iframe width="290" height="150" src="http://www.youtube.com/embed/'.$videoid.'" frameborder="0" allowfullscreen></iframe>';
				if($j==0){
					echo "<tr>";
				}
				else if($j%5==0){
					echo "</tr><tr>";
				}
				?>
				<td style='background:#00A4DA; width:290px; text-align:center; padding: 2px 0px 2px 0px; vertical-align:middle' ><?php 
				if(trim($videos[$j]['video'])){
					echo "<div>".$iframe."</div>";
				}
				else{
					echo "&nbsp;";
				}
				?>
				<div style='padding:5px;'>
				<?php
				if(!$videos[$j]['isMain']){
					?><a href='manageassets.php?assettype=videos&landtype=land_detail&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>&videoid=<?php echo $videos[$j]['id']; ?>&set=primary'>Set as Primary</a>&nbsp;<?php
				}
				?>
				<a onclick='return confirm("Are you sure you want to delete this video?")' href='manageassets.php?assettype=videos&landtype=land_detail&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>&videoid=<?php echo $videos[$j]['id']; ?>&set=delete'
				style='color:red' >Delete</a>
				</div>
				</td>
				<?php
			}
			echo "<tr></table>";
		}
	}
	else{ //special land
		if($_POST){
			$http = $_POST['video'];
			if(trim($http)){
				$sql = "insert into `videos_special` set
					`video` = '".$http."',
					`created_on` = NOW(),
					`land_special_id`='".$_GET['id']."'
				";
				dbQuery($sql);
			}
			
		}
		if($_GET['set']=='primary'&&$_GET['videoid']){ //set as primary
			$sql = "select * from `videos_special` where 
				`land_special_id` = '".mysql_real_escape_string($_GET['id'])."'  and `id`='".mysql_real_escape_string($_GET['videoid'])."'
				and `land_special_id` in (select `id` from `land_special` where `web_user_id`='".$web_user_id."')
				order by `isMain` desc";
			$picture = dbQuery($sql, $_dblink);
			if($picture[0]['id']){
				$sql = "update `videos_special` set `isMain`=0 where 
				`land_special_id` = '".mysql_real_escape_string($_GET['id'])."'";
				dbQuery($sql, $_dblink);
				$sql = "update `videos_special` set `isMain`=1 where 
				`land_special_id` = '".mysql_real_escape_string($_GET['id'])."' and `id`='".mysql_real_escape_string($_GET['videoid'])."'";
				dbQuery($sql, $_dblink);
			}
			?>
			<script>
			window.parent.getLands("<?php echo $_GET['idback']; ?>");
			</script>
			<?php
		}
		else if($_GET['set']=='delete'&&$_GET['videoid']){ //set as primary
			$sql = "select * from `videos_special` where 
				`land_special_id` = '".mysql_real_escape_string($_GET['id'])."'  and `id`='".mysql_real_escape_string($_GET['videoid'])."'
				and `land_special_id` in (select `id` from `land_special` where `web_user_id`='".$web_user_id."')
				order by `isMain` desc";
			$picture = dbQuery($sql, $_dblink);
			if($picture[0]['id']){
				$sql = "delete from `videos_special` where 
				`land_special_id` = '".mysql_real_escape_string($_GET['id'])."' and `id`='".mysql_real_escape_string($_GET['videoid'])."'";
				dbQuery($sql, $_dblink);
			}
			?>
			<script>
			window.parent.getLands("<?php echo $_GET['idback']; ?>");
			</script>
			<?php
		}
		
		$sql = "select * from `videos_special` where 
			`land_special_id` = '".mysql_real_escape_string($_GET['id'])."' 
			and `land_special_id` in (select `id` from `land_special` where `web_user_id`='".$web_user_id."')
			order by `isMain` desc";
		$videos = dbQuery($sql, $_dblink);
		$pt = count($videos);
		?>
		<div style='text-align:center; padding:20px;'>
		<form action="manageassets.php?assettype=videos&landtype=land_detail&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>" method="post" enctype="multipart/form-data">
		Youtube Video Link<br /><i>e.g. http://www.youtube.com/watch?v=4M6Y06w834w</i><br />
		<input type="text" name="video" style='width:400px;'>&nbsp;<input type="submit" name="submit" value="Add Video">
		</form>
		</div>
		<?php
		if($pt){
			echo "<table style='margin-top:1px;' cellpadding=0 cellspacing=4 >";
			for($j=0; $j<$pt; $j++){
				$videoid = explode("v=",$videos[$j]['video']);
				$videoid = explode("&",$videoid[1]);
				$videoid = $videoid[0];
				$iframe = '<iframe width="290" height="150" src="http://www.youtube.com/embed/'.$videoid.'" frameborder="0" allowfullscreen></iframe>';
				if($j==0){
					echo "<tr>";
				}
				else if($j%5==0){
					echo "</tr><tr>";
				}
				?>
				<td style='background:#00A4DA; width:290px; text-align:center; padding: 2px 0px 2px 0px; vertical-align:middle' ><?php 
				if(trim($videos[$j]['video'])){
					echo "<div>".$iframe."</div>";
				}
				else{
					echo "&nbsp;";
				}
				?>
				<div style='padding:5px;'>
				<?php
				if(!$videos[$j]['isMain']){
					?><a href='manageassets.php?assettype=videos&landtype=land_detail&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>&videoid=<?php echo $videos[$j]['id']; ?>&set=primary'>Set as Primary</a>&nbsp;<?php
				}
				?>
				<a onclick='return confirm("Are you sure you want to delete this video?")' href='manageassets.php?assettype=videos&landtype=land_detail&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>&videoid=<?php echo $videos[$j]['id']; ?>&set=delete'
				style='color:red' >Delete</a>
				</div>
				</td>
				<?php
			}
			echo "<tr></table>";
		}
	}


}
else if($_GET['assettype']=='images'){
	if($_GET['landtype']=='land_detail'){ //normal land
		if($_FILES){
			$folder = dirname(__FILE__)."/_uploads2/".$_POST['folder']."/";
			$http = "http%3A//pieceoftheworld.co/_uploads2/".$_POST['folder']."/";
			$newfilename = $folder.$_FILES['image']['name'];
			while(file_exists($newfilename)){
				$newfilename = $folder.time()._.$_FILES['image']['name'];
			}
			$http = "http%3A//pieceoftheworld.co/_uploads2/".$_POST['folder']."/".basename($newfilename);
			move_uploaded_file($_FILES['image']['tmp_name'], $newfilename);
			$sql = "insert into `pictures` set
				`picture` = '".$http."',
				`created_on` = NOW(),
				`land_id`='".$_GET['id']."'
			";
			dbQuery($sql);
			
		}
		if($_GET['set']=='primary'&&$_GET['imageid']){ //set as primary
			$sql = "select * from `pictures` where 
				`land_id` = '".mysql_real_escape_string($_GET['id'])."'  and `id`='".mysql_real_escape_string($_GET['imageid'])."'
				and `land_id` in (select `land_detail_id` from `land` where `web_user_id`='".$web_user_id."')
				order by `isMain` desc";
			$picture = dbQuery($sql, $_dblink);
			if($picture[0]['id']){
				$sql = "update `pictures` set `isMain`=0 where 
				`land_id` = '".mysql_real_escape_string($_GET['id'])."'";
				dbQuery($sql, $_dblink);
				$sql = "update `pictures` set `isMain`=1 where 
				`land_id` = '".mysql_real_escape_string($_GET['id'])."' and `id`='".mysql_real_escape_string($_GET['imageid'])."'";
				dbQuery($sql, $_dblink);
			}
			?>
			<script>
			window.parent.getLands("<?php echo $_GET['idback']; ?>");
			</script>
			<?php
		}
		else if($_GET['set']=='delete'&&$_GET['imageid']){ //set as primary
			$sql = "select * from `pictures` where 
				`land_id` = '".mysql_real_escape_string($_GET['id'])."'  and `id`='".mysql_real_escape_string($_GET['imageid'])."'
				and `land_id` in (select `land_detail_id` from `land` where `web_user_id`='".$web_user_id."')
				order by `isMain` desc";
			$picture = dbQuery($sql, $_dblink);
			if($picture[0]['id']){
				$sql = "delete from `pictures` where 
				`land_id` = '".mysql_real_escape_string($_GET['id'])."' and `id`='".mysql_real_escape_string($_GET['imageid'])."'";
				dbQuery($sql, $_dblink);
			}
			?>
			<script>
			window.parent.getLands("<?php echo $_GET['idback']; ?>");
			</script>
			<?php
		}
		
		$sql = "select * from `pictures` where 
			`land_id` = '".mysql_real_escape_string($_GET['id'])."' 
			and `land_id` in (select `land_detail_id` from `land` where `web_user_id`='".$web_user_id."')
			order by `isMain` desc";
		$pictures = dbQuery($sql, $_dblink);
		//print_r($pictures);
		$pt = count($pictures);
		if($pt){
			$folder = explode("_uploads2/", $pictures[0]['picture']);
			$folder = $folder[1];
			$folder = dirname($folder);
			//echo $folder;
			?>
			<div style='text-align:center; padding:20px;'>
			<form action="manageassets.php?assettype=images&landtype=land_detail&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>" method="post" enctype="multipart/form-data">
			<input type='hidden' name='folder' value='<?php echo $folder; ?>'>
			Upload another Image:<input type="file" name="image">&nbsp;<input type="submit" name="submit" value="Upload">
			</form>
			</div>
			<?php
			echo "<table style='margin-top:1px;' cellpadding=0 cellspacing=4 >";
			for($j=0; $j<$pt; $j++){
				if($j==0){
					echo "<tr>";
				}
				else if($j%5==0){
					echo "</tr><tr>";
				}
				?>
				<td style='background:#00A4DA; width:290px; text-align:center; padding: 2px 0px 2px 0px; vertical-align:middle' ><?php 
				if(trim($pictures[$j]['picture'])){
					?>
					<a href='<?php echo urldecode($pictures[$j]['picture']); ?>' target='_blank'>
					<img src='/image.php?dir=<?php echo base64_encode($pictures[$j]['picture']); ?>&w=290&h=150' />
					</a>
					<?php
				}
				else{
					echo "&nbsp;";
				}
				?>
				<div style='padding:5px;'>
				<?php
				if(!$pictures[$j]['isMain']){
					?><a href='manageassets.php?assettype=images&landtype=land_detail&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>&imageid=<?php echo $pictures[$j]['id']; ?>&set=primary'>Set as Primary</a>&nbsp;<?php
				}
				?>
				<a onclick='return confirm("Are you sure you want to delete this image?")' href='manageassets.php?assettype=images&landtype=land_detail&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>&imageid=<?php echo $pictures[$j]['id']; ?>&set=delete'
				style='color:red' >Delete</a>
				</div>
				</td>
				<?php
			}
			echo "<tr></table>";
		}
		else{
			$sql = "select `id` from `land` where `web_user_id`='".$web_user_id."' order by `id` asc limit 1 ";
			$folder = dbQuery($sql, $_dblink);
			$folder = "land/".$folder[0]['id']."/images";
			?>
			<div style='text-align:center; padding:20px;'>
			<form action="manageassets.php?assettype=images&landtype=land_detail&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>" method="post" enctype="multipart/form-data">
			<input type='hidden' name='folder' value='<?php echo $folder; ?>'>
			Upload another Image:<input type="file" name="image">&nbsp;<input type="submit" name="submit" value="Upload">
			</form>
			</div>
			<?php
		}
	}
	else{ //special
		if($_FILES){
			$folder = dirname(__FILE__)."/_uploads2/".$_POST['folder']."/";
			$http = "http%3A//pieceoftheworld.co/_uploads2/".$_POST['folder']."/";
			$newfilename = $folder.$_FILES['image']['name'];
			while(file_exists($newfilename)){
				$newfilename = $folder.time()._.$_FILES['image']['name'];
			}
			$http = "http%3A//pieceoftheworld.co/_uploads2/".$_POST['folder']."/".basename($newfilename);
			move_uploaded_file($_FILES['image']['tmp_name'], $newfilename);
			$sql = "insert into `pictures_special` set
				`picture` = '".$http."',
				`created_on` = NOW(),
				`land_special_id`='".$_GET['id']."'
			";
			dbQuery($sql);
			
		}
		if($_GET['set']=='primary'&&$_GET['imageid']){ //set as primary
			$sql = "select * from `pictures_special` where 
				`land_special_id` = '".mysql_real_escape_string($_GET['id'])."'  and `id`='".mysql_real_escape_string($_GET['imageid'])."'
				and `land_special_id` in (select `id` from `land_special` where `web_user_id`='".$web_user_id."')
				order by `isMain` desc";
			$picture = dbQuery($sql, $_dblink);
			if($picture[0]['id']){
				$sql = "update `pictures_special` set `isMain`=0 where 
				`land_special_id` = '".mysql_real_escape_string($_GET['id'])."'";
				dbQuery($sql, $_dblink);
				$sql = "update `pictures_special` set `isMain`=1 where 
				`land_special_id` = '".mysql_real_escape_string($_GET['id'])."' and `id`='".mysql_real_escape_string($_GET['imageid'])."'";
				dbQuery($sql, $_dblink);
			}
			?>
			<script>
			window.parent.getLands("<?php echo $_GET['idback']; ?>");
			</script>
			<?php
		}
		else if($_GET['set']=='delete'&&$_GET['imageid']){ //set as primary
			$sql = "select * from `pictures_special` where 
				`land_special_id` = '".mysql_real_escape_string($_GET['id'])."'  and `id`='".mysql_real_escape_string($_GET['imageid'])."'
				and `land_special_id` in (select `id` from `land_special` where `web_user_id`='".$web_user_id."')
				order by `isMain` desc";
			$picture = dbQuery($sql, $_dblink);
			if($picture[0]['id']){
				$sql = "delete from `pictures_special` where 
				`land_special_id` = '".mysql_real_escape_string($_GET['id'])."' and `id`='".mysql_real_escape_string($_GET['imageid'])."'";
				dbQuery($sql, $_dblink);
			}
			?>
			<script>
			window.parent.getLands("<?php echo $_GET['idback']; ?>");
			</script>
			<?php
		}
		
		$sql = "select * from `pictures_special` where 
			`land_special_id` = '".mysql_real_escape_string($_GET['id'])."' 
			and `land_special_id` in (select `id` from `land_special` where `web_user_id`='".$web_user_id."')
			order by `isMain` desc";
		$pictures = dbQuery($sql, $_dblink);
		//print_r($pictures);
		$pt = count($pictures);
		if($pt){
			$folder = explode("_uploads2/", $pictures[0]['picture']);
			$folder = $folder[1];
			$folder = dirname($folder);
			//echo $folder;
			?>
			<div style='text-align:center; padding:20px;'>
			<form action="manageassets.php?assettype=images&landtype=land_special&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>" method="post" enctype="multipart/form-data">
			<input type='hidden' name='folder' value='<?php echo $folder; ?>'>
			Upload another Image:<input type="file" name="image">&nbsp;<input type="submit" name="submit" value="Upload">
			</form>
			</div>
			<?php
			echo "<table style='margin-top:1px;' cellpadding=0 cellspacing=4 >";
			for($j=0; $j<$pt; $j++){
				if($j==0){
					echo "<tr>";
				}
				else if($j%5==0){
					echo "</tr><tr>";
				}
				?>
				<td style='background:#00A4DA; width:290px; text-align:center; padding: 2px 0px 2px 0px; vertical-align:middle' ><?php 
				if(trim($pictures[$j]['picture'])){
					?>
					<a href='<?php echo urldecode($pictures[$j]['picture']); ?>' target='_blank'>
					<img src='/image.php?dir=<?php echo base64_encode($pictures[$j]['picture']); ?>&w=290&h=150' />
					</a>
					<?php
				}
				else{
					echo "&nbsp;";
				}
				?>
				<div style='padding:5px;'>
				<?php
				if(!$pictures[$j]['isMain']){
					?><a href='manageassets.php?assettype=images&landtype=land_special&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>&imageid=<?php echo $pictures[$j]['id']; ?>&set=primary'>Set as Primary</a>&nbsp;<?php
				}
				?>
				<a onclick='return confirm("Are you sure you want to delete this image?")' href='manageassets.php?assettype=images&landtype=land_special&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>&imageid=<?php echo $pictures[$j]['id']; ?>&set=delete'
				style='color:red' >Delete</a>
				</div>
				</td>
				<?php
			}
			echo "<tr></table>";
		}
		else{
			$folder = "specialland/".$_GET['id']."/images";
			?>
			<div style='text-align:center; padding:20px;'>
			<form action="manageassets.php?assettype=images&landtype=land_special&id=<?php echo $_GET['id']; ?>&idback=<?php echo $_GET['idback']; ?>" method="post" enctype="multipart/form-data">
			<input type='hidden' name='folder' value='<?php echo $folder; ?>'>
			Upload another Image:<input type="file" name="image">&nbsp;<input type="submit" name="submit" value="Upload">
			</form>
			</div>
			<?php
		}
	}
}


?>
</body>
</html>