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
		$areatype = 'water';
	}
	
	$subcategories = array(0=>'In the World', 1=>'In Country', 2=>'In Region', 3=>'In City');
		
	$t = count($subcategories);
	
	for($i=0; $i<$t; $i++){
		echo '<div id="top_list_items" class="text_3" onclick="step2(\''.$subcategories[$i].'\', \''.$areatype.'\');">'.$subcategories[$i].'</div>';
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
		
			$sql2 = "SELECT `id` FROM `land` WHERE `web_user_id`='".$web_users[$i]['id']."' AND (`areatype`='".$areatype."' ".$sqlext.")";
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
					echo '<div id="top_list_items" class="text_3" onclick="userProfile(\''.$ownerArr[$z]['id'].'\');">'.($z+1).' - '.$ownerArr[$z]['name'].'</div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3">No Results</div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3">No Results</div>';
	}
	
	exit();
}
//END OF GET BIGGEST OWNERS IN THE WORLD

//GET BIGGEST OWNERS IN THE COUNTRY
if($_GET['action']=='In Country'){
	$sqlext = '';
	$sqlext2 = '';
	if($_GET['areatype']=='land'){
		$areatype = 'land';
		$sqlext = " OR `areatype`='' ";
		$sqlext2 = " OR `b`.`areatype`='' ";
	}else if($_GET['areatype']=='water'){
		$areatype = 'water';
	}

	$sql = "SELECT `country` FROM `land` WHERE `web_user_id`!='' AND (`areatype`='".$areatype."' ".$sqlext.") AND `country`!='' ORDER BY `country`";
	$countries_land = dbQuery($sql, $_dblink);
	
	$sql = "SELECT `a`.`id`, `b`.`country` FROM `land_special` AS `a` 
	LEFT JOIN `land` AS `b` ON `a`.`id`=`b`.`land_special_id` 
	WHERE `a`.`web_user_id`!='' AND (`b`.`areatype`='".$areatype."' ".$sqlext2.") AND `b`.`country`!=''";
	$countries_land_special = dbQuery($sql, $_dblink);
	
	$countries = array_merge($countries_land, $countries_land_special);
	$countries = array_values($countries);
	
	$t_countries = count($countries);
	
	for($i=0; $i<$t_countries; $i++){
		if($countries[$i]['country']!=$countries[($i-1)]['country']){
			echo '<div id="top_list_items" class="text_3" onclick="step3(\'country\', \''.$countries[$i]['country'].'\', \''.$areatype.'\');">'.$countries[$i]['country'].'</div>';
		}
	}
	
	exit();
}

if($_GET['action']=='country'){
	$sqlext = '';
	$sqlext2 = '';
	if($_GET['areatype']=='land'){
		$sqlext = " OR `areatype`='' ";
		$sqlext2 = " OR `b`.`areatype`='' ";
	}
	  
	$sql2 = "SELECT `id`, `name` FROM `web_users`";
	$web_users = dbQuery($sql2, $_dblink);
	
	$t2 = count($web_users);
	
	if($t2){
		$ownerArr = array();
		for($i2=0; $i2<$t2; $i2++){
			$print = array();
		
			$sql2 = "SELECT `id` FROM `land` WHERE `web_user_id`='".$web_users[$i2]['id']."' AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."'";
			$owner_land = dbQuery($sql2, $_dblink);
			
			$sql2 = "SELECT `a`.`id` FROM `land_special` AS `a` 
			LEFT JOIN `land` AS `b` ON `a`.`id`=`b`.`land_special_id` 
			WHERE `a`.`web_user_id`='".$web_users[$i2]['id']."' AND (`b`.`areatype`='".$areatype."' ".$sqlext2.") AND `b`.`country`='".mysql_escape_string($_GET['country'])."'";
			$owner_land_special = dbQuery($sql2, $_dblink);
			
			$owner = array_merge($owner_land, $owner_land_special);
			$owner = array_values($owner);
			
			if(count($owner)){
				$print['area_owned'] = count($owner);
				$print['id'] = $web_users[$i2]['id'];
				$print['name'] = $web_users[$i2]['name'];
				
				$ownerArr[] = $print;
			}
		}

		$display = count($ownerArr);
		
		if($display){
			rsort($ownerArr);
		
			for($z=0; $z<$limit; $z++){
				if($ownerArr[$z]['area_owned']){
					echo '<div id="top_list_items" class="text_3" onclick="userProfile(\''.$ownerArr[$z]['id'].'\');">'.($z+1).' - '.$ownerArr[$z]['name'].'</div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3">No Results</div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3">No Results</div>';
	}

	exit();
}
//END OF GET BIGGEST OWNERS IN THE COUNTRY

//GET BIGGEST OWNERS IN THE REGION
if($_GET['action']=='In Region'){
	$sqlext = '';
	$sqlext2 = '';
	if($_GET['areatype']=='land'){
		$areatype = 'land';
		$sqlext = " OR `areatype`='' ";
		$sqlext2 = " OR `b`.`areatype`='' ";
	}else if($_GET['areatype']=='water'){
		$areatype = 'water';
	}

	$sql = "SELECT `country` FROM `land` WHERE `web_user_id`!='' AND (`areatype`='".$areatype."' ".$sqlext.") AND `country`!='' AND `region`!='' ORDER BY `country`";
	$countries_land = dbQuery($sql, $_dblink);
	
	$sql = "SELECT `a`.`id`, `b`.`country` FROM `land_special` AS `a` 
	LEFT JOIN `land` AS `b` ON `a`.`id`=`b`.`land_special_id` 
	WHERE `a`.`web_user_id`!='' AND (`b`.`areatype`='".$areatype."' ".$sqlext2.") AND `b`.`country`!='' AND `b`.`region`!=''";
	$countries_land_special = dbQuery($sql, $_dblink);
	
	$countries = array_merge($countries_land, $countries_land_special);
	$countries = array_values($countries);
	
	$t_countries = count($countries);
	
	for($i=0; $i<$t_countries; $i++){
		if($countries[$i]['country']!=$countries[($i-1)]['country']){
			echo '<div id="top_list_items" class="text_3" onclick="step3(\'country2\', \''.$countries[$i]['country'].'\', \''.$areatype.'\');">'.$countries[$i]['country'].'</div>';
		}
	}
	
	exit();
}

if($_GET['action']=='country2'){
	$sqlext = '';
	$sqlext2 = '';
	if($_GET['areatype']=='land'){
		$sqlext = " OR `areatype`='' ";
		$sqlext2 = " OR `b`.`areatype`='' ";
	}

	$sql = "SELECT `region` FROM `land` WHERE `web_user_id`!='' AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`!='' ORDER BY `region`";
	$regions_land = dbQuery($sql, $_dblink);
	
	$sql = "SELECT `a`.`id`, `b`.`region` FROM `land_special` AS `a` 
	LEFT JOIN `land` AS `b` ON `a`.`id`=`b`.`land_special_id` 
	WHERE `a`.`web_user_id`!='' AND (`b`.`areatype`='".$areatype."' ".$sqlext2.") AND `b`.`country`='".mysql_escape_string($_GET['country'])."' AND `b`.`region`!=''";
	$regions_land_special = dbQuery($sql, $_dblink);
	
	$regions = array_merge($regions_land, $regions_land_special);
	$regions = array_values($regions);
	
	$t_regions = count($regions);
	
	for($i=0; $i<$t_regions; $i++){
		if($regions[$i]['region']!=$regions[($i-1)]['region']){
			echo '<div id="top_list_items" class="text_3" onclick="step4(\'region\', \''.$_GET['country'].'\', \''.$regions[$i]['region'].'\', \''.$_GET['areatype'].'\');">'.$regions[$i]['region'].'</div>';
		}
	}

	exit();
}

if($_GET['action']=='region'){
	$sqlext = '';
	$sqlext2 = '';
	if($_GET['areatype']=='land'){
		$sqlext = " OR `areatype`='' ";
		$sqlext2 = " OR `b`.`areatype`='' ";
	}

	$sql2 = "SELECT `id`, `name` FROM `web_users`";
	$web_users = dbQuery($sql2, $_dblink);
	
	$t2 = count($web_users);
	
	if($t2){
		$ownerArr = array();
		for($i2=0; $i2<$t2; $i2++){
			$print = array();
		
			$sql2 = "SELECT `id` FROM `land` WHERE `web_user_id`='".$web_users[$i2]['id']."' AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`='".mysql_escape_string($_GET['region'])."'";
			$owner_land = dbQuery($sql2, $_dblink);
			
			$sql = "SELECT `a`.`id` FROM `land_special` AS `a` 
			LEFT JOIN `land` AS `b` ON `a`.`id`=`b`.`land_special_id` 
			WHERE `a`.`web_user_id`='".$web_users[$i2]['id']."' AND (`b`.`areatype`='".$areatype."' ".$sqlext2.") AND `b`.`country`='".mysql_escape_string($_GET['country'])."' AND `b`.`region`='".mysql_escape_string($_GET['region'])."'";
			$owner_land_special = dbQuery($sql, $_dblink);
			
			$owner = array_merge($owner_land, $owner_land_special);
			$owner = array_values($owner);
			
			if(count($owner)){
				$print['area_owned'] = count($owner);
				$print['id'] = $web_users[$i2]['id'];
				$print['name'] = $web_users[$i2]['name'];
				
				$ownerArr[] = $print;
			}
		}

		$display = count($ownerArr);
		
		if($display){
			rsort($ownerArr);
		
			for($z=0; $z<$limit; $z++){
				if($ownerArr[$z]['area_owned']){
					echo '<div id="top_list_items" class="text_3" onclick="userProfile(\''.$ownerArr[$z]['id'].'\');">'.($z+1).' - '.$ownerArr[$z]['name'].'</div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3">No Results</div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3">No Results</div>';
	}
	
	exit();
}
//END OF GET BIGGEST OWNERS IN THE REGION

//GET BIGGEST OWNERS IN THE CITY
if($_GET['action']=='In City'){
	$sqlext = '';
	$sqlext2 = '';
	if($_GET['areatype']=='land'){
		$areatype = 'land';
		$sqlext = " OR `areatype`='' ";
		$sqlext2 = " OR `b`.`areatype`='' ";
	}else if($_GET['areatype']=='water'){
		$areatype = 'water';
	}
	
	$sql = "SELECT `country` FROM `land` WHERE `web_user_id`!='' AND (`areatype`='".$areatype."' ".$sqlext.") AND `country`!='' AND `region`!='' AND `city`!='' ORDER BY `country`";
	$countries_land = dbQuery($sql, $_dblink);
	
	$sql = "SELECT `a`.`id`, `b`.`country` FROM `land_special` AS `a` 
	LEFT JOIN `land` AS `b` ON `a`.`id`=`b`.`land_special_id` 
	WHERE `a`.`web_user_id`!='' AND (`b`.`areatype`='".$areatype."' ".$sqlext2.") AND `b`.`country`!='' AND `b`.`region`!='' AND `b`.`city`!=''";
	$countries_land_special = dbQuery($sql, $_dblink);
	
	$countries = array_merge($countries_land, $countries_land_special);
	$countries = array_values($countries);

	$t_countries = count($countries);
	
	for($i=0; $i<$t_countries; $i++){
		if($countries[$i]['country']!=$countries[($i-1)]['country']){
			echo '<div id="top_list_items" class="text_3" onclick="step3(\'country3\', \''.$countries[$i]['country'].'\', \''.$areatype.'\');">'.$countries[$i]['country'].'</div>';
		}
	}
	
	exit();
}

if($_GET['action']=='country3'){
	$sqlext = '';
	$sqlext2 = '';
	if($_GET['areatype']=='land'){
		$sqlext = " OR `areatype`='' ";
		$sqlext2 = " OR `b`.`areatype`='' ";
	}

	$sql = "SELECT `region` FROM `land` WHERE `web_user_id`!='' AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`!='' AND `city`!='' ORDER BY `region`";
	$regions_land = dbQuery($sql, $_dblink);
	
	$sql = "SELECT `a`.`id`, `b`.`region` FROM `land_special` AS `a` 
	LEFT JOIN `land` AS `b` ON `a`.`id`=`b`.`land_special_id` 
	WHERE `a`.`web_user_id`!='' AND (`b`.`areatype`='".$areatype."' ".$sqlext2.") AND `b`.`country`='".mysql_escape_string($_GET['country'])."' AND `b`.`region`!='' AND `b`.`city`!=''";
	$regions_land_special = dbQuery($sql, $_dblink);
	
	$regions = array_merge($regions_land, $regions_land_special);
	$regions = array_values($regions);
	
	$t_regions = count($regions);
	
	for($i=0; $i<$t_regions; $i++){
		if($regions[$i]['region']!=$regions[($i-1)]['region']){
			echo '<div id="top_list_items" class="text_3" onclick="step4(\'region2\', \''.$_GET['country'].'\', \''.$regions[$i]['region'].'\', \''.$_GET['areatype'].'\');">'.$regions[$i]['region'].'</div>';
		}
	}

	exit();
}

if($_GET['action']=='region2'){
	$sqlext = '';
	$sqlext2 = '';
	if($_GET['areatype']=='land'){
		$sqlext = " OR `areatype`='' ";
		$sqlext2 = " OR `b`.`areatype`='' ";
	}

	$sql = "SELECT `city` FROM `land` WHERE `web_user_id`!='' AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`='".mysql_escape_string($_GET['region'])."' AND `city`!='' ORDER BY `city`";
	$cities_land = dbQuery($sql, $_dblink);
	
	$sql = "SELECT `a`.`id`, `b`.`city` FROM `land_special` AS `a` 
	LEFT JOIN `land` AS `b` ON `a`.`id`=`b`.`land_special_id` 
	WHERE `a`.`web_user_id`!='' AND (`b`.`areatype`='".$areatype."' ".$sqlext2.") AND `b`.`country`='".mysql_escape_string($_GET['country'])."' AND `b`.`region`='".mysql_escape_string($_GET['region'])."' AND `b`.`city`!=''";
	$cities_land_special = dbQuery($sql, $_dblink);
	
	$cities = array_merge($cities_land, $cities_land_special);
	$cities = array_values($cities);
	
	$t_cities = count($cities);

	for($i=0; $i<$t_cities; $i++){
		if($cities[$i]['city']!=$cities[($i-1)]['city']){
			echo '<div id="top_list_items" class="text_3" onclick="step5(\'city\', \''.$_GET['country'].'\', \''.$_GET['region'].'\', \''.$cities[$i]['city'].'\', \''.$_GET['areatype'].'\');">'.$cities[$i]['city'].'</div>';
		}
	}

	exit();
}

if($_GET['action']=='city'){
	$sqlext = '';
	$sqlext2 = '';
	if($_GET['areatype']=='land'){
		$sqlext = " OR `areatype`='' ";
		$sqlext2 = " OR `b`.`areatype`='' ";
	}
	  
	$sql2 = "SELECT `id`, `name` FROM `web_users`";
	$web_users = dbQuery($sql2, $_dblink);
	
	$t2 = count($web_users);
	
	if($t2){
		$ownerArr = array();
		for($i2=0; $i2<$t2; $i2++){
			$print = array();
		
			$sql2 = "SELECT `id` FROM `land` WHERE `web_user_id`='".$web_users[$i2]['id']."' AND (`areatype`='".$_GET['areatype']."' ".$sqlext.") AND `country`='".mysql_escape_string($_GET['country'])."' AND `region`='".mysql_escape_string($_GET['region'])."' AND `city`='".mysql_escape_string($_GET['city'])."'";
			$owner_land = dbQuery($sql2, $_dblink);
			
			$sql = "SELECT `a`.`id` FROM `land_special` AS `a` 
			LEFT JOIN `land` AS `b` ON `a`.`id`=`b`.`land_special_id` 
			WHERE `a`.`web_user_id`='".$web_users[$i2]['id']."' AND (`b`.`areatype`='".$areatype."' ".$sqlext2.") AND `b`.`country`='".mysql_escape_string($_GET['country'])."' AND `b`.`region`='".mysql_escape_string($_GET['region'])."' AND `b`.`city`='".mysql_escape_string($_GET['city'])."'";
			$owner_land_special = dbQuery($sql, $_dblink);
			
			$owner = array_merge($owner_land, $owner_land_special);
			$owner = array_values($owner);
			
			if(count($owner)){
				$print['area_owned'] = count($owner);
				$print['id'] = $web_users[$i2]['id'];
				$print['name'] = $web_users[$i2]['name'];
				
				$ownerArr[] = $print;
			}
		}

		$display = count($ownerArr);
		
		if($display){
			rsort($ownerArr);
		
			for($z=0; $z<$limit; $z++){
				if($ownerArr[$z]['area_owned']){
					echo '<div id="top_list_items" class="text_3" onclick="userProfile(\''.$ownerArr[$z]['id'].'\');">'.($z+1).' - '.$ownerArr[$z]['name'].'</div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3">No Results</div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3">No Results</div>';
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
	  
	  $sql1 = "SELECT `x`, `y`, `land_detail_id` FROM `land` WHERE `web_user_id`='".$_GET['userID']."'";
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
				echo '<div id="top_list_items" class="text_3" onclick="location.href=\'index2.php?xy='.$landArr[$i]['x'].'~'.$landArr[$i]['y'].'\';">'.$landArr[$i]['title'].'</div>';
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
					echo '<div id="top_list_items" class="text_3" onclick="location.href=\'index2.php?xy='.$expensiveLandArr[$z]['x'].'~'.$expensiveLandArr[$z]['y'].'\';">'.($z+1).' - '.$expensiveLandArr[$z]['title'].'</div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3">No Results</div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3">No Results</div>';
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
					echo '<div id="top_list_items" class="text_3" onclick="location.href=\'index2.php?xy='.$landLikedArr[$z]['x'].'~'.$landLikedArr[$z]['y'].'\';">'.($z+1).' - '.$landLikedArr[$z]['title'].'</div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3">No Results</div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3">No Results</div>';
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
					echo '<div id="top_list_items" class="text_3" onclick="location.href=\'index2.php?xy='.$landViewedArr[$z]['x'].'~'.$landViewedArr[$z]['y'].'\';">'.($z+1).' - '.$landViewedArr[$z]['title'].'</div>';
				}
			}
		}else{
			echo '<div id="top_list_items" class="text_3">No Results</div>';
		}
	}else{
		echo '<div id="top_list_items" class="text_3">No Results</div>';
	}
	
	exit();
}
//END OF GET MOST VIEWED LANDS
?>