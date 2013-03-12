<?php
$t = count($companies);
if($format=='xls'||$format=="html"){
	if($format=='xls'){
		header('Content-type: application/ms-excel');
		header("Content-Type: application/force-download");
		header('Content-Disposition: attachment; filename=companies.xls');
	}
	?>
	<table border="1">
	<tr>
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">Company Name</font></td>
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">Description</font></td>
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">E-mail Address</font></td>	
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">Website</font></td>
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">Blog URL</font></td>
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">Blog RSS feed URL</font></td>	
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">Twitter Username</font></td>
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">Facebook Page</font></td>
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">LinkedIn Page</font></td>
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">Number of Employees</font></td>
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">Founded (yyyy or mm/dd/yyyy)</font></td>
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">Country</font></td>
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">Tags</font></td>
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">Status</font></td>
		<td valign="top" bgcolor="#008000"><font color="#FFFFFF">Active?</font></td>
		<td valign="top" bgcolor="red"><font color="#FFFFFF">Categories</font></td>
		<td valign="top" bgcolor="red"><font color="#FFFFFF">People</font></td>
		<td valign="top" bgcolor="red"><font color="#FFFFFF">Competitors</font></td>
		<td valign="top" bgcolor="red"><font color="#FFFFFF">Funding</font></td>
		<td valign="top" bgcolor="red"><font color="#FFFFFF">Investments</font></td>
		<td valign="top" bgcolor="red"><font color="#FFFFFF">Date Added</font></td>
	</tr>
	<?php
	for($i=0; $i<$t; $i++){
		$c = $companies[$i];
		?>
		<tr>
		
		<td valign="top"><?php echo htmlentitiesX($c['name']); ?></td>
		<td valign="top"><?php echo htmlentitiesX($c['description']); ?></td>
		<td valign="top"><?php echo htmlentitiesX($c['email_address']); ?></td>	
		<td valign="top"><?php echo htmlentitiesX($c['website']); ?></td>
		<td valign="top"><?php echo htmlentitiesX($c['blog_url']); ?></td>
		<td valign="top"><?php echo htmlentitiesX($c['blog']); ?></td>	
		<td valign="top"><?php echo htmlentitiesX($c['twitter_username']); ?></td>
		<td valign="top"><?php echo htmlentitiesX($c['facebook']); ?></td>
		<td valign="top"><?php echo htmlentitiesX($c['linkedin']); ?></td>
		<td valign="top"><?php echo htmlentitiesX($c['number_of_employees']); ?></td>
		<td valign="top"><?php echo htmlentitiesX($c['founded']); ?></td>
		<td valign="top"><?php echo htmlentitiesX($c['country']); ?></td>
		<td valign="top"><?php echo htmlentitiesX($c['tags']); ?></td>
		<td valign="top"><?php echo htmlentitiesX($c['status']); ?></td>
		<td valign="top"><?php echo htmlentitiesX($c['active']); ?></td>
		<td valign="top" bgcolor="red"><font color="#FFFFFF"><?php echo htmlentitiesX($c['categories']); ?></font></td>
		<td valign="top" bgcolor="red"><font color="#FFFFFF"><?php echo htmlentitiesX($c['people']); ?></font></td>
		<td valign="top" bgcolor="red"><font color="#FFFFFF"><?php echo htmlentitiesX($c['competitors']); ?></font></td>
		<td valign="top" bgcolor="red"><font color="#FFFFFF"><?php echo htmlentitiesX($c['company_fundings']); ?></font></td>
		<td valign="top" bgcolor="red"><font color="#FFFFFF"><?php echo htmlentitiesX($c['milestones']); ?></font></td>
		<td valign="top" bgcolor="red"><font color="#FFFFFF"><?php echo htmlentitiesX(date("M d, Y", strtotime($c['dateadded']))); ?></font></td>
		</tr>
		<?php
	}
	?>
	</table>
	<?php
}
else if($format=='csv'){
	//header('Content-type: application/ms-excel');
	header("Content-Type: application/force-download");
	header('Content-Disposition: attachment; filename=companies.csv');
	$line = "";
	$line .= '"Company Name",';
	$line .= '"Description",';
	$line .= '"E-mail Address",';
	$line .= '"Website",';
	$line .= '"Blog URL",';
	$line .= '"Blog RSS feed URL",';
	$line .= '"Twitter Username",';
	$line .= '"Facebook Page",';
	$line .= '"LinkedIn Page",';
	$line .= '"Number of Employees",';
	$line .= '"Founded (yyyy or mm/dd/yyyy)",';
	$line .= '"Country",';
	$line .= '"Tags",';
	$line .= '"Status",';
	$line .= '"Active?",';
	$line .= '"Categories",';
	$line .= '"People",';
	$line .= '"Competitors",';
	$line .= '"Funding",';
	$line .= '"Investments",';
	$line .= '"Date Added",';
	echo $line."\n";

	for($i=0; $i<$t; $i++){
		$c = $companies[$i];
		$line = "";
		$line .= '"'.htmlentitiesX($c['name']).'",';
		$line .= '"'.htmlentitiesX($c['description']).'",';
		$line .= '"'.htmlentitiesX($c['email_address']).'",';	
		$line .= '"'.htmlentitiesX($c['website']).'",';
		$line .= '"'.htmlentitiesX($c['blog_url']).'",';
		$line .= '"'.htmlentitiesX($c['blog']).'",';	
		$line .= '"'.htmlentitiesX($c['twitter_username']).'",';
		$line .= '"'.htmlentitiesX($c['facebook']).'",';
		$line .= '"'.htmlentitiesX($c['linkedin']).'",';
		$line .= '"'.htmlentitiesX($c['number_of_employees']).'",';
		$line .= '"'.htmlentitiesX($c['founded']).'",';
		$line .= '"'.htmlentitiesX($c['country']).'",';
		$line .= '"'.htmlentitiesX($c['tags']).'",';
		$line .= '"'.htmlentitiesX($c['status']).'",';
		$line .= '"'.htmlentitiesX($c['active']).'",';
		$line .= '"'.htmlentitiesX($c['categories']).'",';
		$line .= '"'.htmlentitiesX($c['people']).'",';
		$line .= '"'.htmlentitiesX($c['competitors']).'",';
		$line .= '"'.htmlentitiesX($c['company_fundings']).'",';
		$line .= '"'.htmlentitiesX($c['milestones']).'",';
		$line .= '"'.htmlentitiesX(date("M d, Y", strtotime($c['dateadded']))).'",';
		echo $line."\n";
	}
}
?>
