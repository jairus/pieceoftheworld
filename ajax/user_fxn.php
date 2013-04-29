<?php
session_start();
require_once 'global.php';
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
	case "getLands": 
		if(isset($_SESSION['userdata'])){
			$response = getLands($_SESSION['userdata']['id']);	
		} else {			
			$response = 'Please login first.';
		}
		break;					
}
die($response);

// START OF FUNCTIONS //
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
	$pass = urldecode($_POST['password']);
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
				<table border="1" class="table" ><tr>
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
						<td valign='top'>".$row["title"]."</td>
						<td valign='top'>".$row["detail"]."</td>
					</tr>";
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
				<table border="1" class="table" ><tr>
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
						<td>".$row["title"]."</td>
						<td>".$row["detail"]."</td>
						<td>".$row["price"]."</td>
					</tr>";
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
?>