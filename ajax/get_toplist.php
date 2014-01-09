<?php
require_once 'global.php';
error_reporting(E_ALL^E_NOTICE);
session_start();

$limit = 10;

//GET BIGGEST OWNERS
//GET AREA CATEGORIES
if($_GET['action']=='Biggest Land Owners' || $_GET['action']=='Biggest Water Owners'){
	if($_GET['action']=='Biggest Land Owners'){
		$areatype = 'land';
	}else if($_GET['action']=='Biggest Water Owners'){
		//$areatype = 'water';
		echo '<script>step2(\'In the World\', \'water\');</script>';
	}
	
	$subcategories = array(0=>'In the World', 1=>'In Country', 2=>'In Region', 3=>'In City');
		
	$t = count($subcategories);
	
	for($i=0; $i<$t; $i++){
		echo '<div id="top_list_items" class="text_3" onclick="step2(\''.$subcategories[$i].'\', \''.$areatype.'\');"><p>'.$subcategories[$i].'</p></div>';
	}
	
	exit();
}
//END OF GET AREA CATEGORIES

//GET BIGGEST OWNERS IN THE WORLD
if($_GET['action']=='In the World'){
	$sqlext = '';
	$sqlext2 = '';
	if($_GET['areatype']=='land'){
		$areatype = 'land';
		$sqlext = " OR `areatype`='' ";
		$sqlext2 = " OR `b`.`areatype`='' ";
	}else if($_GET['areatype']=='water'){
		$areatype = 'water';
	}
	
	$sql = "SELECT `id`, `name` FROM `web_users`";
	$web_users = dbQuery($sql, $_dblink);
	
	$t = count($web_users);
	
	if($t){
		$ownerArr = array();
		for($i=0; $i<$t; $i++){
			$print = array();
		
			$sql2 = "SELECT `id` FROM `land` WHERE `web_user_id`='".$web_users[$i]['id']."' AND ISNULL(`land_special_id`) AND (`areatype`='".$areatype."' ".$sqlext.")";
			$owner_land = dbQuery($sql2, $_dblink);
			
			$sql2 = "SELECT `a`.`id` FROM `land_special` AS `a` 
			LEFT JOIN `land` AS `b` ON `a`.`id`=`b`.`land_special_id` 
			WHERE `a`.`web_user_id`='".$web_users[$i]['id']."' AND (`b`.`areatype`='".$areatype."' ".$sqlext2.")";
			$owner_land_special = dbQuery($sql2, $_dblink);
			
			$owner = array_merge($owner_land, $owner_land_special);
			$owner = array_values($owner);
			
			if(count($owner)){
				$print['area_owned'] = count($owner);
				$print['id'] = $web_users[$i]['id'];
				$print['name'] = $web_users[$i]['name'];
				
				$ownerArr[] = $print;
			}
		}
		
		$display = count($ownerArr);
		
		if($display){
			rsort($ownerArr);
			
			for($z=0; $z<$limit; $z++){
				if($ownerArr[$z]['area_owned']){
					echo '<div id="top_list_items" class="text_3" onclick="userProfile(\''.$ownerArr[$z]['id'].'\');"><p>'.($z+1).' - '.$ownerArr[$z]['name'].' - '.$ownerArr[$z]['area_owned'].'</p></div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
	}
	
	exit();
}
//END OF GET BIGGEST OWNERS IN THE WORLD

//GET BIGGEST OWNERS IN THE COUNTRY
if($_GET['action']=='In Country'){
	$sqlext = '';
	if($_GET['areatype']=='land'){
		$areatype = 'land';
		$sqlext = " OR `areatype`='' ";
	}else if($_GET['areatype']=='water'){
		$areatype = 'water';
	}
	
	$countryArr = array();
	
	$sql = "SELECT `country` FROM `land` WHERE `web_user_id`!='' AND ISNULL(`land_special_id`) AND (`areatype`='".$areatype."' ".$sqlext.") AND `country`!='' ORDER BY `country`";
	$countries_land = dbQuery($sql, $_dblink);
	
	$tcl = count($countries_land);
	
	for($icl=0; $icl<$tcl; $icl++){
		$print = array();
		
		$print['country'] = $countries_land[$icl]['country'];
		
		$countryArr[] = $print;
	}
	
	$sql = "SELECT `id` FROM `land_special` WHERE `web_user_id`!=''";
	$countries_land_special = dbQuery($sql, $_dblink);
	
	$tcls = count($countries_land_special);
	
	if($tcls){
		for($icls=0; $icls<$tcls; $icls++){
			$sql2 = "SELECT `country` FROM `land` WHERE `land_special_id`='".$countries_land_special[$icls]['id']."' AND (`areatype`='".$areatype."' ".$sqlext.") AND `country`!='' ORDER BY `country`";
			$countries_land2 = dbQuery($sql2, $_dblink);
			
			if(count($countries_land2)){
				$print = array();
				
				$print['country'] = $countries_land2[0]['country'];
				
				$countryArr[] = $print;
			}
		}
	}
	
	$tc = count($countryArr);
	
	if($tc){
		sort($countryArr);
		for($ic=0; $ic<$tc; $ic++){
			if($countryArr[$ic]['country']!=$countryArr[($ic-1)]['country']){
				echo '<div id="top_list_items" class="text_3" onclick="step3(\'country\', \''.$countryArr[$ic]['country'].'\', \''.$areatype.'\');"><p>'.$countryArr[$ic]['country'].'</p></div>';
			}
		}
	}
	
	exit();
}

if($_GET['action']=='country'){
	$sqlext = '';
	if($_GET['areatype']=='land'){
		$sqlext = " OR `areatype`='' ";
	}
	  
	$sql = "SELECT `id`, `name` FROM `web_users`";
	$web_users = dbQuery($sql, $_dblink);
	
	$t2 = count($web_users);
	
	if($t2){
		$ownerArr = array();
		for($i2=0; $i2<$t2; $i2++){
			$print = array();
			
			$sql = "SELECT `id` FROM `land` WHERE `web_user_id`='".$web_users[$i2]['id']."' AND ISNULL(`land_special_id`) AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."'";
			$owner_land = dbQuery($sql, $_dblink);
			
			if(count($owner_land)){
				$print['area_owned'] = count($owner_land);
				$print['id'] = $web_users[$i2]['id'];
				$print['name'] = $web_users[$i2]['name'];
				
				$ownerArr[] = $print;
			}
			
			$sql = "SELECT `id` FROM `land_special` WHERE `web_user_id`='".$web_users[$i2]['id']."'";
			$countries_land_special = dbQuery($sql, $_dblink);
			
			$tcls = count($countries_land_special);
			
			if($tcls){
				for($icls=0; $icls<$tcls; $icls++){
					$sql = "SELECT `id` FROM `land` WHERE `land_special_id`='".$countries_land_special[$icls]['id']."' AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."'";
					$owner_land_special = dbQuery($sql, $_dblink);
					
					if(count($owner_land_special)){
						$print['area_owned'] = count($owner_land_special);
						$print['id'] = $web_users[$i2]['id'];
						$print['name'] = $web_users[$i2]['name'];
						
						$ownerArr[] = $print;
					}
				}
			}
		}

		$display = count($ownerArr);
		
		if($display){
			rsort($ownerArr);
		
			for($z=0; $z<$limit; $z++){
				if($ownerArr[$z]['area_owned']){
					echo '<div id="top_list_items" class="text_3" onclick="userProfile(\''.$ownerArr[$z]['id'].'\');"><p>'.($z+1).' - '.$ownerArr[$z]['name'].' - '.$ownerArr[$z]['area_owned'].'</p></div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
	}

	exit();
}
//END OF GET BIGGEST OWNERS IN THE COUNTRY

//GET BIGGEST OWNERS IN THE REGION
if($_GET['action']=='In Region'){
	$sqlext = '';
	if($_GET['areatype']=='land'){
		$areatype = 'land';
		$sqlext = " OR `areatype`='' ";
	}else if($_GET['areatype']=='water'){
		$areatype = 'water';
	}
	
	$countryArr = array();
	
	$sql = "SELECT `country` FROM `land` WHERE `web_user_id`!='' AND ISNULL(`land_special_id`) AND (`areatype`='".$areatype."' ".$sqlext.") AND `country`!='' AND `region`!='' ORDER BY `region`";
	$countries_land = dbQuery($sql, $_dblink);
	
	$tcl = count($countries_land);
	
	for($icl=0; $icl<$tcl; $icl++){
		$print = array();
		
		$print['country'] = $countries_land[$icl]['country'];
		
		$countryArr[] = $print;
	}
	
	$sql = "SELECT `id` FROM `land_special` WHERE `web_user_id`!=''";
	$countries_land_special = dbQuery($sql, $_dblink);
	
	$tcls = count($countries_land_special);
	
	if($tcls){
		for($icls=0; $icls<$tcls; $icls++){
			$sql2 = "SELECT `country` FROM `land` WHERE `land_special_id`='".$countries_land_special[$icls]['id']."' AND (`areatype`='".$areatype."' ".$sqlext.") AND `country`!='' AND `region`!='' ORDER BY `region`";
			$countries_land2 = dbQuery($sql2, $_dblink);
			
			if(count($countries_land2)){
				$print = array();
				
				$print['country'] = $countries_land2[0]['country'];
				
				$countryArr[] = $print;
			}
		}
	}
	
	$tc = count($countryArr);
	
	if($tc){
		sort($countryArr);
		for($ic=0; $ic<$tc; $ic++){
			if($countryArr[$ic]['country']!=$countryArr[($ic-1)]['country']){
				echo '<div id="top_list_items" class="text_3" onclick="step3(\'country2\', \''.$countryArr[$ic]['country'].'\', \''.$areatype.'\');"><p>'.$countryArr[$ic]['country'].'</p></div>';
			}
		}
	}
	
	exit();
}

if($_GET['action']=='country2'){
	$sqlext = '';
	if($_GET['areatype']=='land'){
		$sqlext = " OR `areatype`='' ";
	}
	
	$regionArr = array();
	
	$sql = "SELECT `region` FROM `land` WHERE `web_user_id`!='' AND ISNULL(`land_special_id`) AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`!='' ORDER BY `region`";
	$region_land = dbQuery($sql, $_dblink);
	
	$tcl = count($region_land);
	
	for($icl=0; $icl<$tcl; $icl++){
		$print = array();
		
		$print['region'] = $region_land[$icl]['region'];
		
		$regionArr[] = $print;
	}
	
	$sql = "SELECT `id` FROM `land_special` WHERE `web_user_id`!=''";
	$region_land_special = dbQuery($sql, $_dblink);
	
	$tcls = count($region_land_special);
	
	if($tcls){
		for($icls=0; $icls<$tcls; $icls++){
			$sql2 = "SELECT `region` FROM `land` WHERE `land_special_id`='".$region_land_special[$icls]['id']."' AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`!='' ORDER BY `region`";
			$region_land2 = dbQuery($sql2, $_dblink);
			
			if(count($region_land2)){
				$print = array();
				
				$print['region'] = $region_land2[0]['region'];
				
				$regionArr[] = $print;
			}
		}
	}
	
	$tc = count($regionArr);
	
	if($tc){
		sort($regionArr);
		for($ic=0; $ic<$tc; $ic++){
			if($regionArr[$ic]['region']!=$regionArr[($ic-1)]['region']){
				echo '<div id="top_list_items" class="text_3" onclick="step4(\'region\', \''.$_GET['country'].'\', \''.$regionArr[$ic]['region'].'\', \''.$_GET['areatype'].'\');"><p>'.$regionArr[$ic]['region'].'</p></div>';
			}
		}
	}

	exit();
}

if($_GET['action']=='region'){
	$sqlext = '';
	if($_GET['areatype']=='land'){
		$sqlext = " OR `areatype`='' ";
	}

	$sql2 = "SELECT `id`, `name` FROM `web_users`";
	$web_users = dbQuery($sql2, $_dblink);
	
	$t2 = count($web_users);
	
	if($t2){
		$ownerArr = array();
		for($i2=0; $i2<$t2; $i2++){
			$print = array();
			
			$sql = "SELECT `id` FROM `land` WHERE `web_user_id`='".$web_users[$i2]['id']."' AND ISNULL(`land_special_id`) AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`='".mysql_escape_string($_GET['region'])."'";
			$owner_land = dbQuery($sql, $_dblink);
			
			if(count($owner_land)){
				$print['area_owned'] = count($owner_land);
				$print['id'] = $web_users[$i2]['id'];
				$print['name'] = $web_users[$i2]['name'];
				
				$ownerArr[] = $print;
			}
			
			$sql = "SELECT `id` FROM `land_special` WHERE `web_user_id`='".$web_users[$i2]['id']."'";
			$region_land_special = dbQuery($sql, $_dblink);
			
			$tcls = count($region_land_special);
			
			if($tcls){
				for($icls=0; $icls<$tcls; $icls++){
					$sql = "SELECT `id` FROM `land` WHERE `land_special_id`='".$region_land_special[$icls]['id']."' AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`='".mysql_escape_string($_GET['region'])."'";
					$owner_land_special = dbQuery($sql, $_dblink);
					
					if(count($owner_land_special)){
						$print['area_owned'] = count($owner_land_special);
						$print['id'] = $web_users[$i2]['id'];
						$print['name'] = $web_users[$i2]['name'];
						
						$ownerArr[] = $print;
					}
				}
			}
		}

		$display = count($ownerArr);
		
		if($display){
			rsort($ownerArr);
		
			for($z=0; $z<$limit; $z++){
				if($ownerArr[$z]['area_owned']){
					echo '<div id="top_list_items" class="text_3" onclick="userProfile(\''.$ownerArr[$z]['id'].'\');"><p>'.($z+1).' - '.$ownerArr[$z]['name'].' - '.$ownerArr[$z]['area_owned'].'</p></div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
	}
	
	exit();
}
//END OF GET BIGGEST OWNERS IN THE REGION

//GET BIGGEST OWNERS IN THE CITY
if($_GET['action']=='In City'){
	$sqlext = '';
	if($_GET['areatype']=='land'){
		$areatype = 'land';
		$sqlext = " OR `areatype`='' ";
	}else if($_GET['areatype']=='water'){
		$areatype = 'water';
	}
	
	$countryArr = array();
	
	$sql = "SELECT `country` FROM `land` WHERE `web_user_id`!='' AND ISNULL(`land_special_id`) AND (`areatype`='".$areatype."' ".$sqlext.") AND `country`!='' AND `region`!='' AND `city`!='' ORDER BY `city`";
	$countries_land = dbQuery($sql, $_dblink);
	
	$tcl = count($countries_land);
	
	for($icl=0; $icl<$tcl; $icl++){
		$print = array();
		
		$print['country'] = $countries_land[$icl]['country'];
		
		$countryArr[] = $print;
	}
	
	$sql = "SELECT `id` FROM `land_special` WHERE `web_user_id`!=''";
	$countries_land_special = dbQuery($sql, $_dblink);
	
	$tcls = count($countries_land_special);
	
	if($tcls){
		for($icls=0; $icls<$tcls; $icls++){
			$sql2 = "SELECT `country` FROM `land` WHERE `land_special_id`='".$countries_land_special[$icls]['id']."' AND (`areatype`='".$areatype."' ".$sqlext.") AND `country`!='' AND `region`!='' AND `city`!='' ORDER BY `city`";
			$countries_land2 = dbQuery($sql2, $_dblink);
			
			if(count($countries_land2)){
				$print = array();
				
				$print['country'] = $countries_land2[0]['country'];
				
				$countryArr[] = $print;
			}
		}
	}
	
	$tc = count($countryArr);
	
	if($tc){
		sort($countryArr);
		for($ic=0; $ic<$tc; $ic++){
			if($countryArr[$ic]['country']!=$countryArr[($ic-1)]['country']){
				echo '<div id="top_list_items" class="text_3" onclick="step3(\'country3\', \''.$countryArr[$ic]['country'].'\', \''.$areatype.'\');"><p>'.$countryArr[$ic]['country'].'</p></div>';
			}
		}
	}
	
	exit();
}

if($_GET['action']=='country3'){
	$sqlext = '';
	if($_GET['areatype']=='land'){
		$sqlext = " OR `areatype`='' ";
	}
	
	$regionArr = array();
	
	$sql = "SELECT `region` FROM `land` WHERE `web_user_id`!='' AND ISNULL(`land_special_id`) AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`!='' AND `city`!='' ORDER BY `city`";
	$region_land = dbQuery($sql, $_dblink);
	
	$tcl = count($region_land);
	
	for($icl=0; $icl<$tcl; $icl++){
		$print = array();
		
		$print['region'] = $region_land[$icl]['region'];
		
		$regionArr[] = $print;
	}
	
	$sql = "SELECT `id` FROM `land_special` WHERE `web_user_id`!=''";
	$region_land_special = dbQuery($sql, $_dblink);
	
	$tcls = count($region_land_special);
	
	if($tcls){
		for($icls=0; $icls<$tcls; $icls++){
			$sql2 = "SELECT `region` FROM `land` WHERE `land_special_id`='".$region_land_special[$icls]['id']."' AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`!='' AND `city`!='' ORDER BY `city`";
			$region_land2 = dbQuery($sql2, $_dblink);
			
			if(count($region_land2)){
				$print = array();
				
				$print['region'] = $region_land2[0]['region'];
				
				$regionArr[] = $print;
			}
		}
	}
	
	$tc = count($regionArr);
	
	if($tc){
		sort($regionArr);
		for($ic=0; $ic<$tc; $ic++){
			if($regionArr[$ic]['region']!=$regionArr[($ic-1)]['region']){
				echo '<div id="top_list_items" class="text_3" onclick="step4(\'region2\', \''.$_GET['country'].'\', \''.$regionArr[$ic]['region'].'\', \''.$_GET['areatype'].'\');"><p>'.$regionArr[$ic]['region'].'</p></div>';
			}
		}
	}

	exit();
}

if($_GET['action']=='region2'){
	$sqlext = '';
	if($_GET['areatype']=='land'){
		$sqlext = " OR `areatype`='' ";
	}
	
	$cityArr = array();
	
	$sql = "SELECT `city` FROM `land` WHERE `web_user_id`!='' AND ISNULL(`land_special_id`) AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`='".mysql_escape_string($_GET['region'])."' AND `city`!='' ORDER BY `city`";
	$city_land = dbQuery($sql, $_dblink);
	
	$tcl = count($city_land);
	
	for($icl=0; $icl<$tcl; $icl++){
		$print = array();
		
		$print['city'] = $city_land[$icl]['city'];
		
		$cityArr[] = $print;
	}
	
	$sql = "SELECT `id` FROM `land_special` WHERE `web_user_id`!=''";
	$city_land_special = dbQuery($sql, $_dblink);
	
	$tcls = count($city_land_special);
	
	if($tcls){
		for($icls=0; $icls<$tcls; $icls++){
			$sql2 = "SELECT `city` FROM `land` WHERE `land_special_id`='".$city_land_special[$icls]['id']."' AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`='".mysql_escape_string($_GET['region'])."' AND `city`!='' ORDER BY `city`";
			$city_land2 = dbQuery($sql2, $_dblink);
			
			if(count($city_land2)){
				$print = array();
				
				$print['city'] = $city_land2[0]['city'];
				
				$cityArr[] = $print;
			}
		}
	}
	
	$tc = count($cityArr);
	
	if($tc){
		sort($cityArr);
		for($ic=0; $ic<$tc; $ic++){
			if($cityArr[$ic]['city']!=$cityArr[($ic-1)]['city']){
				echo '<div id="top_list_items" class="text_3" onclick="step5(\'city\', \''.$_GET['country'].'\', \''.$_GET['region'].'\', \''.$cityArr[$ic]['city'].'\', \''.$_GET['areatype'].'\');"><p>'.$cityArr[$ic]['city'].'</p></div>';
			}
		}
	}

	exit();
}

if($_GET['action']=='city'){
	$sqlext = '';
	if($_GET['areatype']=='land'){
		$sqlext = " OR `areatype`='' ";
	}
	
	$sql2 = "SELECT `id`, `name` FROM `web_users`";
	$web_users = dbQuery($sql2, $_dblink);
	
	$t2 = count($web_users);
	
	if($t2){
		$ownerArr = array();
		for($i2=0; $i2<$t2; $i2++){
			$print = array();
			
			$sql = "SELECT `id` FROM `land` WHERE `web_user_id`='".$web_users[$i2]['id']."' AND ISNULL(`land_special_id`) AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`='".mysql_escape_string($_GET['region'])."' AND `city`='".mysql_escape_string($_GET['city'])."'";
			$owner_land = dbQuery($sql, $_dblink);
			
			if(count($owner_land)){
				$print['area_owned'] = count($owner_land);
				$print['id'] = $web_users[$i2]['id'];
				$print['name'] = $web_users[$i2]['name'];
				
				$ownerArr[] = $print;
			}
			
			$sql = "SELECT `id` FROM `land_special` WHERE `web_user_id`='".$web_users[$i2]['id']."'";
			$region_land_special = dbQuery($sql, $_dblink);
			
			$tcls = count($region_land_special);
			
			if($tcls){
				for($icls=0; $icls<$tcls; $icls++){
					$sql = "SELECT `id` FROM `land` WHERE `land_special_id`='".$region_land_special[$icls]['id']."' AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`='".mysql_escape_string($_GET['region'])."' AND `city`='".mysql_escape_string($_GET['city'])."'";
					$owner_land_special = dbQuery($sql, $_dblink);
					
					if(count($owner_land_special)){
						$print['area_owned'] = count($owner_land_special);
						$print['id'] = $web_users[$i2]['id'];
						$print['name'] = $web_users[$i2]['name'];
						
						$ownerArr[] = $print;
					}
				}
			}
		}

		$display = count($ownerArr);
		
		if($display){
			rsort($ownerArr);
		
			for($z=0; $z<$limit; $z++){
				if($ownerArr[$z]['area_owned']){
					echo '<div id="top_list_items" class="text_3" onclick="userProfile(\''.$ownerArr[$z]['id'].'\');"><p>'.($z+1).' - '.$ownerArr[$z]['name'].' - '.$ownerArr[$z]['area_owned'].'</p></div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
	}

	exit();
}
//END OF GET BIGGEST OWNERS IN THE CITY

//GET BIGGEST OWNERS PROFILE
if($_GET['action']=='get_user_profile'){
	$sql = "SELECT * FROM `web_users` WHERE `id`='".$_GET['userID']."'";
	$web_users = dbQuery($sql, $_dblink);
	
	echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td class="text_3"><b>Name</b></td>
	  </tr>
	  <tr>
		<td class="text_3">'.$web_users[0]['name'].'</td>
	  </tr>';
	  
	  $landArr = array();
	  
	  $sql1 = "SELECT `x`, `y`, `land_detail_id` FROM `land` WHERE `web_user_id`='".$_GET['userID']."' AND ISNULL(`land_special_id`)";
	  $land = dbQuery($sql1, $_dblink);
	  
	  $sql2 = "SELECT `id`, `title` FROM `land_special` WHERE `web_user_id`='".$_GET['userID']."'";
	  $land_special = dbQuery($sql2, $_dblink);
	
	  $t1 = count($land);
	  $t2 = count($land_special);
	  
	  for($z1=0; $z1<$t1; $z1++){
	  	$print = array();
	  
		$sql = "SELECT `title` FROM `land_detail` WHERE `id`='".$land[$z1]['land_detail_id']."'";
		$land_detail = dbQuery($sql, $_dblink);
		
		$print['title'] = $land_detail[0]['title'];
		$print['x'] = $land[$z1]['x'];
		$print['y'] = $land[$z1]['y'];
		
		$landArr[] = $print;
	  }
	  
	  for($z2=0; $z2<$t2; $z2++){
		$sql = "SELECT `x`, `y` FROM `land` WHERE `land_special_id`='".$land_special[$z2]['id']."'";
		$land_detail = dbQuery($sql, $_dblink);
		
		$print['title'] = $land_special[$z2]['title'];
		$print['x'] = $land_detail[0]['x'];
		$print['y'] = $land_detail[0]['y'];
		
		$landArr[] = $print;
	  }
	  
	  $t = count($landArr);
	  
	  if($t){
	  	sort($landArr);
		
	  	echo '<tr>
		  <td height="10"></td>
	    </tr>
	    <tr>
		  <td class="text_3"><b>Land Owned</b></td>
	    </tr>
	    <tr>
		  <td height="5"></td>
	    </tr>
	    <tr>
		  <td>';
		  
		  for($i=0; $i<$t; $i++){
		  	if($landArr[$i]['title']!=$landArr[($i-1)]['title']){
				echo '<div id="top_list_items" class="text_3" onclick="location.href=\'index.php?xy='.$landArr[$i]['x'].'~'.$landArr[$i]['y'].'\';"><p>'.$landArr[$i]['title'].'</p></div>';
			}
		  }
		  
		  echo '</td>
	    </tr>';
	  }
	  
	echo '</table>';
	
	exit();
}
//END OF GET BIGGEST OWNERS PROFILE
//END OF BIGGEST OWNERS

//GET MOST EXPENSIVE LANDS
if($_GET['action']=='Most Expensive Lands'){
	$sql = "SELECT * FROM `land_special` WHERE `price`!='0'";
	$lands = dbQuery($sql, $_dblink);
	
	$t = count($lands);
	
	if($t){
		$expensiveLandArr = array();
		for($i=0; $i<$t; $i++){
			$print = array();
			
			$print['price'] = $lands[$i]['price'];
			$print['title'] = $lands[$i]['title'];
			
			$sql2 = "SELECT `x`, `y` FROM `land` WHERE `land_special_id`='".$lands[$i]['id']."'";
			$landsxy = dbQuery($sql2, $_dblink);
			
			$print['x'] = $landsxy[0]['x'];
			$print['y'] = $landsxy[0]['y'];
			
			$expensiveLandArr[] = $print;
		}
		
		rsort($expensiveLandArr);
		$display = count($expensiveLandArr);
		
		if($display){
			for($z=0; $z<$limit; $z++){
				if($expensiveLandArr[$z]['price']){
					echo '<div id="top_list_items" class="text_3" onclick="location.href=\'index.php?xy='.$expensiveLandArr[$z]['x'].'~'.$expensiveLandArr[$z]['y'].'\';"><p>'.($z+1).' - '.$expensiveLandArr[$z]['title'].'</p></div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
	}
	
	exit();
}
//END OF GET MOST EXPENSIVE LANDS

//GET MOST LIKED LANDS
if($_GET['action']=='Most Liked Lands'){
	$sql = "SELECT * FROM `land` WHERE `totalLikes`!='0'";
	
	$sql = "SELECT `a`.`totalLikes`, `a`.`x`, `a`.`y`, `b`.`title` FROM `land` AS `a` 
			LEFT JOIN `land_detail` AS `b` ON `a`.`land_detail_id`=`b`.`id`
			WHERE `a`.`totalLikes`!='0'";
	$lands = dbQuery($sql, $_dblink);
	
	$t = count($lands);
	
	if($t){
		$landLikedArr = array();
		for($i=0; $i<$t; $i++){
			$print = array();
			
			$print['total_likes'] = $lands[$i]['totalLikes'];
			$print['title'] = $lands[$i]['title'];
			$print['x'] = $lands[$i]['x'];
			$print['y'] = $lands[$i]['y'];
			
			$landLikedArr[] = $print;
		}
		
		rsort($landLikedArr);
		$display = count($landLikedArr);
		
		if($display){
			for($z=0; $z<$limit; $z++){
				if($landLikedArr[$z]['total_likes']){
					echo '<div id="top_list_items" class="text_3" onclick="location.href=\'index.php?xy='.$landLikedArr[$z]['x'].'~'.$landLikedArr[$z]['y'].'\';"><p>'.($z+1).' - '.$landLikedArr[$z]['title'].'</p></div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
	}
	
	exit();
}
//END OF GET MOST LIKED LANDS

//GET MOST VIEWED LANDS
if($_GET['action']=='Most Viewed Lands'){
	$sql = "SELECT * FROM `land_view` WHERE `viewCtr`!='0'";
	$lands = dbQuery($sql, $_dblink);
	
	$t = count($lands);
	
	if($t){
		$landViewedArr = array();
		for($i=0; $i<$t; $i++){
			$print = array();
			
			$print['total_views'] = $lands[$i]['viewCtr'];
			
			$sql = "SELECT `a`.`id` AS `land_id`, `a`.`x`, `a`.`y`, `b`.`title` FROM `land` AS `a` 
			LEFT JOIN `land_detail` AS `b` ON `a`.`land_detail_id`=`b`.`id`
			WHERE `a`.`id`='".$lands[$i]['land_id']."' AND `a`.`web_user_id`!=''";
			$land = dbQuery($sql, $_dblink);
			
			if(count($land)){
				$print['title'] = $land[0]['title'];
				$print['x'] = $land[0]['x'];
				$print['y'] = $land[0]['y'];
			}
			
			$landViewedArr[] = $print;
		}
		
		rsort($landViewedArr);
		$display = count($landViewedArr);
		
		if($display){
			for($z=0; $z<$limit; $z++){
				if($landViewedArr[$z]['total_views']){
					echo '<div id="top_list_items" class="text_3" onclick="location.href=\'index.php?xy='.$landViewedArr[$z]['x'].'~'.$landViewedArr[$z]['y'].'\';"><p>'.($z+1).' - '.$landViewedArr[$z]['title'].'</p></div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3"><p>No Results</p></div>';
	}
	
	exit();
}
//END OF GET MOST VIEWED LANDS
?>