<div class='logs_container'>
<?php
$logsbydate = array();
foreach($logs as $key => $value){
	$date = date("M d, Y", $value['dateadded_ts']);
	if(!is_array($logsbydate[$date])){
		$logsbydate[$date] = array();
	}
	$logsbydate[$date][] = $value;
}
foreach($logsbydate as $key=>$logs){
	echo "<div class='log_date'>".$key."</div>";
	echo "<div class='logs'>";
	echo "<table>";
	/*
	echo "<tr>";
	echo "<th>User</th>";
	echo "<th>List</th>";
	echo "<th>Action</th>";
	echo "</tr>";
	*/
	foreach($logs as $log){
		$what = "";
		if($log['table']=='companies'){
			$what = "company";
		}
		else if($log['table']=='people'){
			$what = "person";
		}
		elseif($log['table']=='investment_org'){
			$what = "investment organization";
		}
		echo "<tr>";
		echo "<td class='log_time'>".date("[ H:i ", $log['dateadded_ts'])." UTC+8 ]</td>";
		if($log['action']=='deleted'){
			echo "<td class='log'><b>".$log['username']."</b> <a class='red'>".$log['action']."</a> ".$what." '<b>".$log['name']."</b>'.</td>";
		}
		else{
			echo "<td class='log'><b>".$log['username']."</b> ".$log['action']." ".$what." '<a href='".site_url()."".$log['table']."/edit/".$log['ipc_id']."'><b>".$log['name']."</b></a>'.</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";
}
?>
</div>