<?php
@session_start();
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
        case "like":            
            parse_str(parse_url($_GET['href'], PHP_URL_QUERY), $arrLand);
            $result = saveFbLike($arrLand['landId'], $arrLand['specialLandId'], 'like');
            $response = json_encode($result);
            break;
        case "unlike":
            parse_str(parse_url($_GET['href'], PHP_URL_QUERY), $arrLand);
            $result = saveFbLike($arrLand['landId'], $arrLand['specialLandId'], 'unlike');
            $response = json_encode($result);
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
    $fb_id = urldecode($_POST['fb_id']);
    $name = urldecode($_POST['name']);
    $gender = urldecode($_POST['gender']);
    $location = urldecode($_POST['location']);

    // if logged in via facebook, register details before logging in
    if($fb_id != ''){
        $rs = dbQuery("select * from web_users where fb_id = '".mysql_real_escape_string($fb_id)."' or useremail = '".mysql_real_escape_string($useremail)."' limit 1 ");
        if(!empty($rs) ){
            $row = $rs[0];
            // check logged in via fb and had previous account, so update details
            $addl = '';
            if($fb_id && !$row['fb_id']){
                $addl .= ", fb_id = '".mysql_real_escape_string($fb_id)."' ";
            }
            if($name && !$row['name']){
                $addl .= ", name = '".mysql_real_escape_string($name)."' ";
            }
            if($gender && !$row['gender']){
                $addl .= ", gender = '".mysql_real_escape_string($gender)."' ";
            }
            if($location && !$row['location']){
                $addl .= ", location = '".mysql_real_escape_string($location)."' ";
            }

            if($addl){
                $addl = substr($addl,1);
                dbQuery("update web_users set $addl where id = '".$row['id'] ."'  limit 1");
            }
            $row['fb_id'] = $fb_id;
            $row['name'] = $name;
            $_SESSION['userdata'] = $row;
        } elseif($fb_id) {
            $sql = "insert into web_users (useremail, name, fb_id, gender, location) values ('".mysql_real_escape_string($useremail)."', '".mysql_real_escape_string($name)."','".mysql_real_escape_string($fb_id)."','".mysql_real_escape_string($gender)."','".mysql_real_escape_string($location)."')";
            $rs = dbQuery($sql);
            $newId = $rs['mysql_insert_id'];
            $row =  array('useremail' => $useremail, 'id' => $newId, 'name' => $name, 'fb_id' => $fb_id);
            $_SESSION['userdata'] = $row;
        }
        $result = array('status' => true, 'message' => 'You are now successfully logged in.', 'content' => $row);

    } else {
        $rs = dbQuery("select id, useremail, password from web_users where useremail = '".mysql_real_escape_string($useremail)."' limit 1 ");
        if(!empty($rs) && $rs[0]['password'] == md5($pass)){
            unset($rs[0]['password']);
            $_SESSION['userdata'] = $rs[0];
            $result = array('status' => true, 'message' => 'You are now successfully logged in.', 'content' => $rs[0]);
        }
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
    $name = urldecode($_POST['name']);
    $gender = urldecode($_POST['gender']);
    $location = urldecode($_POST['location']);

    if(!isUnique($useremail)){
		$result = array('status' => false, 'message' => 'Email not available anymore.');
	} else {
		$sql = "insert into web_users (useremail, password, name, gender, location) values ('$useremail', '$password', '$name', '$gender', '$location')";
		$rs = dbQuery($sql);
		$newId = $rs['mysql_insert_id'];
		$row =  array('useremail' => $useremail, 'id' => $newId, 'name' => $name);
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
	
	// save each tag in tags table
	$oldTags = explode(',',$_POST['oldTags']);
	$newTags = $_POST['tags'];
	// get deleted tags
	$toDelete = array_diff($oldTags, $newTags);
	// get to add tags
	$toAdd = array_diff($newTags, $oldTags);
	
	foreach($toAdd as $tag){
		$tag = mysql_real_escape_string(strtolower(trim($tag)));		
		$rs = dbQuery("select id from tags where lower(name) = '$tag' limit 1");
		if(!empty($rs)){
			dbQuery("update tags set useCounter = useCounter + 1 where id = '".$rs[0]['id']."' limit 1");
		} else {
			$newId = dbQuery("insert into tags set name='$tag' ");
		}
	}
	foreach($toDelete as $tag){
		$tag = mysql_real_escape_string(strtolower(trim($tag)));
		dbQuery("update tags set useCounter = useCounter - 1 where lower(name) = '$tag' limit 1");
	}
	
	$result = array('status' => true, 'message' => 'Tags saved successfully', 'tags' => implode(',',$newTags) );		
	return $result;
}
function saveFbLike($landId, $specialLandId, $status = 'like'){
    $result = array('status' => false, 'message' => "Invalid Land. ID: $landId . Special: $specialLandId ");
    $operator = ($status == 'like')? '+' : '-';
    if($specialLandId != null && is_numeric($specialLandId) ){
        dbQuery("update land_special set totalLikes = totalLikes $operator 1 where id = '$specialLandId' limit 1");
        $result = array('status' => true, 'message' => 'Land like saved for special land');
    } elseif($landId != null && is_numeric($landId) ){
        dbQuery("update land set totalLikes = totalLikes $operator 1 where id = '$landId' limit 1");
        $result = array('status' => true, 'message' => 'Land like saved for ordinary land');
    }
    return $result;
}
?>