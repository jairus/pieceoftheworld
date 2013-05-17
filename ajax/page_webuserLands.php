<?php
session_start();
require_once('user_fxn.php');

$webUserId = $_SESSION['userdata']['id'];
$rs = getLands($webUserId);	

if(empty($rs['land_detail']) && empty($rs['land_special']) ){
?>
You can now start buying your first pieces of land!
<?php
} else {
?>


<?php
	$type = 'land_detail';
	if(!empty($rs[$type])){
	
?>
	<h2>Ordinary Lands</h2>
	<table border="1" class="table landList" >
		<tr>
			<th>Plot</th>
			<th>Title</th>
			<th>Detail</th>
		</tr>
	<?php foreach($rs[$type] as $row){ ?>
		<tr>
			<td>
				<ul>
				<?php foreach($row['land'] as $row2) { ?>
					<li><a href='?xy=<?php echo $row2['x']."~".$row2['y'] ?>' > <?php echo $row2['x']." - ".$row2['y'] ?></a></li>
				<?php } ?>
				</ul>
			</td>
			<td valign='top'>
				<form id='form_<?php echo $row['id']?>'>
					<input type='hidden' name='category_id' value='<?php echo $row['category_id']?>' />
					<input type='hidden' name='type' value='<?php echo $type ?>' />
					<input type='hidden' name='id' value='<?php echo $row['id']?>' />
				</form>
				<p class='editableText' id='title-<?php echo $row['id']?>-land_detail'><?php echo $row["title"] ?></p>
				<br/>
				<a href='#' data-id='<?php echo $row['id'] ?>' class='manageImageLink'>manage images</a>
				<br/><br/>
                <a href='#' data-id='<?php echo $row['id'] ?>' class='manageVideoLink'>manage videos</a>
                <br/><br/>
				<a href='#' data-id='<?php echo $row['id'] ?>' class='manageTags'>manage tags</a><br/>
			</td>
			<td valign='top'><p class='editableTextarea' id='detail-<?php echo $row['id']?>-land_detail'><?php echo nl2br($row["detail"])?></p></td>
		</tr>
	<?php }?>
	</table>
<?php } ?>	

<?php
	$type = 'land_special';
	if(!empty($rs[$type])){
	
?>
	<h2>Special Lands</h2>
	<table border="1" class="table landList" >
		<tr>
			<th>Plot</th>
			<th>Title</th>
			<th>Detail</th>
			<th>Price</th>
		</tr>
	<?php foreach($rs[$type] as $row){ ?>
		<tr>
			<td>
				<ul>
				<?php foreach($row['land'] as $row2) { ?>
					<li><a href='?xy=<?php echo $row2['x']."~".$row2['y'] ?>' > <?php echo $row2['x']." - ".$row2['y'] ?></a></li>
				<?php } ?>
				</ul>
			</td>
			<td valign='top'>
				<form id='form_<?php echo $row['id']?>'>
					<input type='hidden' name='category_id' value='<?php echo $row['category_id']?>' />
					<input type='hidden' name='type' value='<?php echo $type ?>' />
					<input type='hidden' name='id' value='<?php echo $row['id']?>' />
				</form>
				<p class='editableText' id='title-<?php echo $row['id']?>-land_detail'><?php echo $row["title"] ?></p>
				<br/>
				<a href='#' data-id='<?php echo $row['id'] ?>' class='manageImageLink'>manage images</a>
                <br/><br/>
                <a href='#' data-id='<?php echo $row['id'] ?>' class='manageVideoLink'>manage videos</a>
				<br/><br/>
				<a href='#' data-id='<?php echo $row['id'] ?>' class='manageTags'>manage tags</a><br/>
			</td>
			<td valign='top'><p class='editableTextarea' id='detail-<?php echo $row['id']?>-land_detail'><?php echo nl2br($row["detail"])?></p></td>
			<td><?php echo $row['price']?></td>
		</tr>
	<?php }?>
	</table>
<?php }
}
?>