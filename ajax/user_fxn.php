<?php
session_start();
require_once 'global.php';
$result = array();
switch($_GET['action'])
{
	case "login": $result = login();
		$response = json_encode($result); 
		die($response);
		break;
	case "register": $result = register();
		$response = json_encode($result);
		die($response);
		break;
	case "logout": $result = logout();	
		$response = json_encode($result);
		die($response);
		break;		
	case "getLands": 
		if(isset($_SESSION['userdata'])){
			$response = getLands($_SESSION['userdata']['id']);	
		} else {			
			$response = 'Please login first.';
		}
		header('Content-Type: text/html; charset=utf-8');
		die($response);
		break;
	case "edit": 
		header('Content-Type: text/html; charset=utf-8');
		$response = edit();		
		die($response);
		break;
	case "getpix": $result = getpix($_GET['id'],$_GET['type'] );	
		$response = json_encode($result);
		die($response);
		break;						
	case "upload": $result = upload($_GET['recordId'],$_GET['type'] );	
		$response = json_encode($result);
		die($response);
		break;								
}

// START OF FUNCTIONS //
function site_url(){
	$host = $_SERVER['HTTP_HOST'];
	if($host=='localhost'){
		return "http://".$host."/pieceoftheworld.co/";
	}
	else{
		return "http://".$host."/";
	}
}
function login()
{
	$result = array('status' => false, 'message' => 'Invalid email or password');
	$useremail = urldecode($_POST['email']);
	$pass = urldecode($_POST['password']);
	$rs = dbQuery("select id, useremail, password from web_users where useremail = '".mysql_real_escape_string($useremail)."' limit 1 ");
	if(!empty($rs) && $rs[0]['password'] == md5($pass)){
		unset($rs[0]['password']);
		$_SESSION['userdata'] = $rs[0];
		$result = array('status' => true, 'message' => 'You are now successfully logged in.', 'content' => $rs[0]);	
	} 
	return $result;
}
function isUnique($useremail)
{
	$rs = dbQuery("select id from web_users where useremail = '".mysql_real_escape_string($useremail)."' limit 1 ");
	return (empty($rs))? true : false;	
}
function register()
{
	$result = array('status' => false, 'message' => 'Cannot register.');
	$useremail = urldecode($_POST['email']);
	$password = md5(urldecode($_POST['password']));
	if(!isUnique($useremail)){
		$result = array('status' => false, 'message' => 'Email not available anymore.');
	} else {
		$sql = "insert into web_users (useremail, password) values ('$useremail', '$password')";
		$rs = dbQuery($sql);
		$newId = $rs['mysql_insert_id'];
		$row =  array('useremail' => $useremail, 'id' => $newId);
		$_SESSION['userdata'] = $row;
		$result = array('status' => true, 'message' => 'You are now successfully registered.', 'content' => $row );	
	}
	return $result;
}
function logout()
{
	session_destroy();
	$result = array('status' => true, 'message' => 'You are now logged out.');	
	return $result;
}
function getLands($id)
{
	$html = '';
	$sql = "SELECT LD.id, LD.`title` , LD.`detail`
			FROM  `land_detail` LD
			LEFT JOIN land L ON LD.id = L.land_detail_id
			WHERE L.web_user_id =  '$id'
			GROUP BY LD.id
			ORDER BY LD.`id` DESC 			
			";
	$rs = dbQuery($sql);
	if(!empty($rs)){						
		$html .= '<h2>Ordinary Lands</h2>
				<table border="1" class="table landList" ><tr>
					<th>Plot</th>
					<th>Title</th>
					<th>Detail</th>
					</tr>';
		foreach($rs as $row){
			$html .= "<tr>
						<td>
							<ul>";
							
			$sql2 = "select x, y from land where land_detail_id = '".$row['id']."' ";
			$rs2 = dbQuery($sql2);
			foreach($rs2 as $row2){
				$html .= "<li><a href='?xy=".$row2['x']."~".$row2['y']."'>".$row2['x']." - ".$row2['y']."</a></li>";
			}
						
			$html .=  "		</ul>
						</td>
						<td valign='top'><p class='editableText' id='title-".$row['id']."-land_detail'>".$row["title"]."</p>
							<a href='#' data-id='".$row['id']."' data-type='pictures' class='manageImageLink'>manage images</a>
						</td>
						<td valign='top'><p class='editableTextarea' id='detail-".$row['id']."-land_detail'>".nl2br($row["detail"])."</p></td>
					</tr>
					<tr id='pixHolder_pictures_".$row['id']."' class='hide'><td colspan='3'>						
							<iframe src='".site_url()."webuserPictures.php?id=".$row['id']."&type=pictures' frameborder=0 height='100' width='300'></iframe>						
					</td></tr>";
		}
		$html .= '</table>';
	
	} 
	$sql = "select LD.id, LD.`title`, LD.`detail`, format(LD.price,2) as price
			from `land_special` LD			
			where LD.web_user_id = '$id' 			
			order by LD.`id` desc 
			";
	$rs = dbQuery($sql);
	if(!empty($rs)){
		$html .= '<h2>Special Lands</h2>
				<table border="1" class="table landList" ><tr>
					<th>Plot</th>
					<th>Title</th>
					<th>Detail</th>
					<th>Price</th>
					</tr>';
		foreach($rs as $row){			
			$html .= "<tr>
						<td>
							<ul>";
							
			$sql2 = "select x, y from land where land_special_id = '".$row['id']."' ";
			$rs2 = dbQuery($sql2);
			foreach($rs2 as $row2){
				$html .= "<li><a href='?xy=".$row2['x']."~".$row2['y']."'>".$row2['x']." - ".$row2['y']."</a></li>";
			}
						
			$html .=  "		</ul>			
						<td><p class='editableText' id='title-".$row['id']."-land_special'>".$row["title"]."</p>
							<a href='#' data-id='".$row['id']."' data-type='pictures' classs='manageImageLink'>manage images</a>
						</td>
						<td><p class='editableTextarea' id='detail-".$row['id']."-land_special'>".nl2br($row["detail"])."</p></td>
						<td>".$row["price"]."</td>
					</tr>
					<tr class='hide'><td colspan='3' id='pixHolder_pictures_special_".$row['id']."'><img src='images/loading.gif'></td></tr>";
		}
		$html .= '</table>';	
	} 
	
	if($html){
		$result = '<p>Below are your owned lands.<p>' . $html;		
	}
	else {
		$result = '<p>You can now start buying pieces of land.<p>' . $html;				
	}
	return $result;			
}
// expect: title-<id>-<table> or detail-<id>-<table>
function edit()
{	
	list($field, $id, $table) = explode('-',$_POST['element_id']);
	$result = urldecode($_POST['update_value']);	
	//$result = html_entity_decode(urldecode($_POST['update_value']), ENT_QUOTES, "UTF-8") ;	
	//$result = iconv('UTF-8', 'ASCII//TRANSLIT', urldecode($_POST['update_value']));
	$sql = "update $table set $field = '".mysql_real_escape_string($result)."' where id = '".mysql_real_escape_string($id)."' limit 1 ";
	dbQuery($sql);
	return nl2br($result);
}
function upload($landId, $table='pictures')
{	
	$landField = ($table == 'pictures')? 'land_id' : 'land_special_id';
	$sql = "delete from `$table` where `$landField`=".mysql_real_escape_string($landId);
	dbQuery($sql);
	if(is_array($_POST['pictures'])){

		// if no main pix, default it to the first one
		$mainPix = (isset($_POST['isMainPix']))? str_replace("../", '', $_POST['isMainPix']) : str_replace("../", '', $_POST['pictures'][0]);

		foreach($_POST['pictures'] as $key=>$value){
			$value = str_replace("../", '', $value); 
			
			$isMain = ($mainPix == $value)? 1 : 0;
			$sql = "insert into `$table` set 
			`$landField`='".mysql_real_escape_string($landId)."', 
			`title`='".mysql_real_escape_string($_POST['picture_titles'][$key])."',
			`isMain`='$isMain', 
			`picture`='".mysql_real_escape_string($value)."'";			
			dbQuery($sql);
		}
		$result = array('status' => true, 'message' => 'Images saved successfully');	
	} else {
		$result = array('status' => false, 'message' => 'Cannot save images');	
	}
	return $result;
}
function getpix($landId, $table='pictures', $isAjax = false)
{
	$pictures = array();
	$landField = ($table == 'pictures')? 'land_id' : 'land_special_id';
	$sql = "select * from `$table` where `$landField`= '".mysql_real_escape_string($landId)."' order by id asc";
	$pictures = dbQuery($sql);	
	foreach($pictures as &$value)
	{		
		$value['picturePath'] =  $value['picture'];
		$value['pictureFile'] = urldecode(basename($value['picture']));
		$value['isChecked'] = ($value['isMain'])? 'checked' : '';
	}
	if($isAjax){
		$result = array('status' => true, 'total' => count($pictures), 'content' => $pictures);
	} else {
		$result = $pictures;
	}	
	return $result;
}
?>