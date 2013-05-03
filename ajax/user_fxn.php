<?php
session_start();
require_once 'global.php';
if(isset($_GET['action'])){
	$result = array();
	switch($_GET['action'])
	{
		case "login": $result = login();
			$response = json_encode($result); 			
			break;
		case "register": $result = register();
			$response = json_encode($result);			
			break;
		case "logout": $result = logout();	
			$response = json_encode($result);			
			break;		
		case "upload": $result = upload($_POST['id'], $_POST['type']);
			$response = json_encode($result);			
			break;				
		case "saveTags": $result = saveTags($_POST['id'], $_POST['type']);
			$response = json_encode($result);			
			break;							
		case "edit": 
			header('Content-Type: text/html; charset=utf-8');
			$response = edit();					
			break;
	}
	die($response);
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
	$rs = array();
	$sql = "SELECT LD.id, LD.`title` , LD.`detail`, LD.category_id
			FROM  `land_detail` LD
			LEFT JOIN land L ON LD.id = L.land_detail_id
			WHERE L.web_user_id =  '$id'
			GROUP BY LD.id
			ORDER BY LD.`id` DESC 			
			";
	$rs['land_detail'] = dbQuery($sql);
	if(!empty($rs['land_detail'])){
		foreach($rs['land_detail'] as &$row){							
			$sql2 = "select x, y from land where land_detail_id = '".$row['id']."' ";
			$row['land'] = dbQuery($sql2);
		}			
	} 
	
	$sql = "select LD.id, LD.`title`, LD.`detail`, format(LD.price,2) as price
			from `land_special` LD			
			where LD.web_user_id = '$id' 			
			order by LD.`id` desc 
			";
	$rs['land_special'] = dbQuery($sql);
	if(!empty($rs['land_special'])){
		foreach($rs['land_special'] as &$row){			
		
			$sql2 = "select x, y from land where land_special_id = '".$row['id']."' ";
			$row['land'] = dbQuery($sql2);
		}
	}
	return $rs;	
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
function upload($landId, $type)
{
	$result = array();
	if($type == 'land_detail'){
		$table = 'pictures';
		$landField = 'land_id';		
	} else {
		$table = 'pictures_special';
		$landField = 'land_special_id';		
	}
		
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
function getPix($landId, $type)
{
	$pictures = array();
	if($type == 'land_detail'){
		$table = 'pictures';
		$landField = 'land_id';		
	} else {
		$table = 'pictures_special';
		$landField = 'land_special_id';		
	}
	
	$sql = "select * from `$table` where `$landField`= '".mysql_real_escape_string($landId)."' order by id asc";
	$result = dbQuery($sql);	
	return $result;
}
function getTags($landId, $table)
{	
	$result = array();
	$sql = "select L.tags, L.category_id
			from $table L
			where L.`id`= '".mysql_real_escape_string($landId)."' limit 1";
	$rs = dbQuery($sql);	

	if(!empty($rs)){
		$result = $rs[0];
	}
	return $result;
}
function getCategories()
{
	$result = array();	
	$sql = "select id,name from `categories` where `deleted`= 0 order by name asc ";
	$rs = dbQuery($sql);	
	foreach($rs as $row){
		$result[$row['id']] = $row['name'];
	}
	return $result;
}
function saveTags($landId, $table)
{
	$result = array('status' => false, 'message' => 'Cannot save tags.');
	$sql = "update $table set tags = '".mysql_real_escape_string(implode(',',$_POST['tags']))."', category_id = '".mysql_real_escape_string($_POST['category_id'])."' where id ='".mysql_real_escape_string($landId)."' limit 1";
	dbQuery($sql);	
	$result = array('status' => true, 'message' => 'Tags saved successfully');		
	return $result;
}
?>