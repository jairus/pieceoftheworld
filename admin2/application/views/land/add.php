<?php
@session_start();
$sid = session_id()."_".time();
//changed for revisions
if(!$changed){
	$changed = array();
}
//echo "<pre>";
//print_r($changed);
//echo "</pre>";
?>
<script>
function saveCompany(approve){
	extra = "";
	if(approve=='approverev'){
		if(!confirm("Are you sure you want to approve this revision?")){
			return false;
		}
		extra = "/<?php echo $revision['id']; ?>"
	}
	else if(approve=='approvecontri'){
		if(!confirm("Are you sure you want to approve this contribution?")){
			return false;
		}
		extra = "/<?php echo $contribution['id']; ?>"
	}
	jQuery("#savebutton").val("Saving...");
	formdata = jQuery("#company_form").serialize();
	jQuery("#company_form *").attr("disabled", true);
	jQuery.ajax({
		<?php
		if($company['id']){
			?>url: "<?php echo site_url(); ?>companies/ajax_edit"+extra,<?php
		}
		else{
			?>url: "<?php echo site_url(); ?>companies/ajax_add"+extra,<?php
		}
		?>
		type: "POST",
		data: formdata,
		dataType: "script",
		success: function(){
			
		}
	});

}
function rejectRevision(id){
	if(confirm("Are you sure you want to reject this revision?")){
		self.location = "<?php echo site_url(); ?>revisions/reject_revision/"+id;
	}
}
function rejectContribution(id){
	if(confirm("Are you sure you want to reject this contribution?")){
		self.location = "<?php echo site_url(); ?>contributions/reject_contribution/"+id;
	}
}
function deleteCompany(co_id){
	if(confirm("Are you sure you want to delete this company?")){
		formdata = "id="+co_id;
		jQuery.ajax({
			url: "<?php echo site_url(); ?>companies/ajax_delete/"+co_id,
			type: "POST",
			data: formdata,
			dataType: "script",
			success: function(){
				self.location = "<?php echo site_url(); ?>companies";
			}
		});
		
	}
}

function checkCompany(co_name){
	if(jQuery.trim(co_name)){
		formdata = "name="+co_name;
		<?php
			if($company['id']){
				?>formdata += "&id=<?php echo $company['id']; ?>";<?php
			}
		?>
		jQuery("#co_check").html("<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />");
		
		jQuery.ajax({
			url: "<?php echo site_url(); ?>companies/ajax_check_company",
			type: "POST",
			data: formdata,
			dataType: "script",
			success: function(){
				
			}
		});
	}
	else{
		jQuery("#co_check").html("");
	}
	
}

function refreshLogo(logopath){
	logopath = escape(logopath);
	jQuery("#logopathhtml").html("<img src='<?php echo site_url(); ?>media/image.php?p="+logopath+"&mx=220&_="+(new Date().getTime())+"' />");
	jQuery("#logopath").val(logopath);
}
var ss = [];
var competitors = []; 
var people = []; 

function refreshScreenshots(filepath){
	file = filepath.split(/\//g);
	file = file[file.length-1];
	filepath = escape(filepath);
	if(ss.indexOf(filepath)==-1){
		ss.push(filepath);
		html = jQuery("#sspathhtml").html();	
		html += "<div><a target='_blank' href='<?php echo site_url(); ?>media/image.php?p="+filepath+"'>"+file+"</a><br><input type='text' name='screenshot_titles[]' /><input type='hidden' name='screenshots[]' value='"+filepath+"' /><div class='hint'>Description</div>&nbsp;&nbsp;&nbsp;<a style='cursor:pointer; text-decoration:underline' class='red delete' onclick='delSS(this, \""+filepath+"\")' >Delete</a></div>";
		jQuery("#sspathhtml").html(html);
	}
	//jQuery("#logopath").val(filepath);
}

function addCompetitor(label, value, add){

	<?php
	if($company['id']){
		?>
		if(value==<?php echo $company['id']; ?>){
			alert("Cannot add self as competitor.");
			return false;
		}
		<?php
	}
	?>
	value = uNum(value);
	
	if(competitors.indexOf(value)==-1){
		competitors.push(value);
		if(add){
			htmladd = "<tr class='compete lightgreen' id='compete"+value+"' style='display:'><td>";
		}
		else{
			htmladd = "<tr class='compete' id='compete"+value+"' style='display:'><td>";
		}
		htmladd += "<a target='' href='<?php echo site_url(); ?>companies/edit/"+value+"'>"+label+"</a>";
		htmladd += "<input type='hidden' name='competitors[]' value='"+value+"' /></td>";
		htmladd += "<td><a class='red delete' onclick='delCompete(this, "+value+")' style='cursor:pointer; text-decoration:underline' >Delete</a></td>";
		htmladd += "</tr>";
		
		html = jQuery("#competitors_html table tbody").html();
		
		if(add){
			html = htmladd + html;
		}
		else{
			html = html + htmladd;
		}
		
		jQuery("#competitors_html table tbody").html(html);
	}
	else{
		alert(label+" is already a competitor.");	
	}
}

function delPerson(obj, pid){
	if(confirm("Are you sure you want to delete this person?")){
		pid = uNum(pid);
		index = people.indexOf(pid);
		people.splice(index, 1);
		obj.parentElement.parentElement.outerHTML = "";
		return true;
	}
	return false;
}

function delSS(obj, filepath){
	if(confirm("Are you sure you want to delete this screenshot?")){
		index = ss.indexOf(filepath);
		ss.splice(index, 1);
		obj.parentElement.outerHTML = "";
		return true;
	}
	return false;
}
function delCompete(obj, value){
	if(confirm("Are you sure you want to delete this competitor?")){
		value = uNum(value);
		index = competitors.indexOf(value);
		competitors.splice(index, 1);
		obj.parentElement.parentElement.outerHTML = "";
		return true;
	}
	return false;
}

function peoplePreAdd(label, value){
	value = uNum(value);
	/*
	if(people.indexOf(value)!=-1){
		alert(label+" is already part of this company.");
		return false;
	}
	*/
	
	jQuery("#peopleadd").slideDown(200);
	jQuery("#p_name").html(label);
	jQuery("#p_id").val(value);
	
	
}

function setFFormDefaults(){ //set funding form defaults
	jQuery("#f_round").val("Seed");
	jQuery("#f_currency").val("SGD");
	jQuery("#f_fund_amount").val("");
	jQuery("#f_date").val("");
	jQuery("#ipc tbody").html(""); //clear ipc
}

function delFunding(idx){
	if(confirm("Are you sure you want to delete this funding record?")){
		jQuery("#"+idx)[0].outerHTML = "";
		
		//set defaults
		setFFormDefaults();
		
		jQuery("#fundingadd").fadeOut(200, function(){
			
		});
		return true;
	}
	return false;
}

fundingindex = 0;
function editFIntro(idx){
	setFFormDefaults();
	f_data = jQuery("#f_data"+idx).val();
	f_data = JSON.parse(f_data);
	/*
	f_data.f_round = f_round;
	f_data.f_currency = f_currency;
	f_data.f_fund_amount = f_fund_amount;
	f_data.f_date = f_date;
	f_data.f_company = f_company;
	f_data.f_company_val = f_company_val;
	f_data.f_person = f_person;
	f_data.f_person_val = f_person_val;
	f_data.f_investment_org = f_investment_org;
	f_data.f_investment_org_val = f_investment_org_val;
	*/

	for(x in f_data){
		eval(x+" = f_data."+x+";");
	}
	jQuery("#f_round").val(f_round);
	jQuery("#f_currency").val(f_currency);
	jQuery("#f_fund_amount").val(fNum(uNum(f_fund_amount)));
	jQuery("#f_date").val(f_date);
	jQuery("#ipc tbody").html(""); //clear ipc
	for(i=0; i<f_investment_org.length; i++){
		addFI();
	}
	for(i=0; i<f_company.length; i++){
		addFC();
	}
	for(i=0; i<f_person.length; i++){
		addFP();
	}
	i=0;
	jQuery(".f_investment_org").each(function(){
		altid = jQuery(this).attr("alt");
		if(f_investment_org_val[i]!=0){
			jQuery("#check_"+altid).html("<img src='<?php echo site_url(); ?>media/check.png' title='Item is in the database.' alt='Item is in the database.' />");
		}
		jQuery(this).val(f_investment_org[i]);
		i++;
	});
	i=0;
	jQuery(".f_investment_org_val").each(function(){
		jQuery(this).val(f_investment_org_val[i]);
		i++;
	});
	i=0;
	jQuery(".f_company").each(function(){
		altid = jQuery(this).attr("alt");
		if(f_company_val[i]!=0){
			jQuery("#check_"+altid).html("<img src='<?php echo site_url(); ?>media/check.png' title='Item is in the database.' alt='Item is in the database.' />");
		}
		jQuery(this).val(f_company[i]);
		i++;
	});
	i=0;
	jQuery(".f_company_val").each(function(){
		jQuery(this).val(f_company_val[i]);
		i++;
	});
	i=0;
	jQuery(".f_person").each(function(){
		altid = jQuery(this).attr("alt");
		if(f_person[i]!=0){
			jQuery("#check_"+altid).html("<img src='<?php echo site_url(); ?>media/check.png' title='Item is in the database.' alt='Item is in the database.' />");
		}
		jQuery(this).val(f_person[i]);
		i++;
	});
	i=0;
	jQuery(".f_person_val").each(function(){
		jQuery(this).val(f_person_val[i]);
		i++;
	});
	
	//jQuery("#f_company").val(f_company);
	//jQuery("#f_company_val").val(f_company_val);
	//jQuery("#f_person").val(f_person);
	//jQuery("#f_person_val").val(f_person_val);
	//jQuery("#f_investment_org").val(f_investment_org);
	//jQuery("#f_investment_org_val").val(f_investment_org_val);
		
	//scroll to
	jQuery("#f_save_button").attr("alt", idx);
	jQuery("#f_cancel_button").attr("alt", idx);
	jQuery("#f_save_button").show();
	jQuery("#f_add_button").hide();
	jQuery("#fundingadd").slideDown(200, function(){
			jQuery('html, body').animate({
				scrollTop: jQuery("#fundingadd").parent().offset().top
			}, 500);
		}
	);
	
	
	//alert(idx);
}
function editFunding(f_round, f_currency, f_fund_amount, f_date, f_company, f_company_val, f_person, f_person_val, f_investment_org, f_investment_org_val, idx){
	
	f_data = {};
	f_data.f_round = f_round;
	f_data.f_currency = f_currency;
	f_data.f_fund_amount = f_fund_amount;
	f_data.f_date = f_date;
	f_data.f_company = f_company;
	f_data.f_company_val = f_company_val;
	f_data.f_person = f_person;
	f_data.f_person_val = f_person_val;
	f_data.f_investment_org = f_investment_org;
	f_data.f_investment_org_val = f_investment_org_val;
	f_datastr = JSON.stringify(f_data);
	
	
	html = "";
	html += "<tr id='fundingtr"+idx+"'>";
	html += "<td id='fundingtd"+idx+"'>";


	
	
	for(i=0; i<f_company.length; i++){
		company = f_company[i];
		company_val = f_company_val[i];
		html += "<input type='hidden' name='f_companies"+idx+"[]' value='"+company+"' />";
		html += "<input type='hidden' name='f_company_vals"+idx+"[]' value='"+company_val+"' />";
	}
	
	for(i=0; i<f_person.length; i++){
		person = f_person[i];
		person_val = f_person_val[i];
		html += "<input type='hidden' name='f_people"+idx+"[]' value='"+person+"' />";
		html += "<input type='hidden' name='f_person_vals"+idx+"[]' value='"+person_val+"' />";
	}
	
	for(i=0; i<f_investment_org.length; i++){
		investment_org = f_investment_org[i];
		investment_org_val = f_investment_org_val[i];
		html += "<input type='hidden' name='f_investment_orgs"+idx+"[]' value='"+investment_org+"' />";
		html += "<input type='hidden' name='f_investment_org_vals"+idx+"[]' value='"+investment_org_val+"' />";
	}
	html += "<table class='fundingtable lightgreen' >";
	html += "<tr>";
	html += "<td class='label'><input type='hidden' id='f_data"+idx+"' />Round:</td>";
	html += "<td class='value0'><span id='round"+idx+"'>"+f_round+"</span>";
	html += "<input type='hidden' id='round"+idx+"' class='hiddenform' name='f_rounds["+idx+"]' value='"+f_round+"' />";
	html +="</td>";
	
	f_fund_amount = uNum(f_fund_amount);
	f_fund_amount = fNum(f_fund_amount);
	html += "<td class='label'>Amount:</td>";
	html += "<td class='value1'><span id='currency"+idx+"' >"+f_currency+"</span>";
	html += "<input type='hidden' id='f_currency"+idx+"' class='hiddenform' name='f_currencies["+idx+"]' value='"+f_currency+"' />";
	html += "<span id='fund_amount"+idx+"'>"+f_fund_amount+"</span>";
	html += "<input type='hidden' id='f_fund_amount"+idx+"' class='hiddenform' name='f_fund_amounts["+idx+"]' value='"+uNum(f_fund_amount)+"' />";
	html += "</td>";
	
	try{
		thedate = new Date(f_date);
		thedate.setDate(thedate.getDate());
		f_date_formated = dateFormat(thedate, "mmm dd, yyyy");
	}
	catch(e){
		alert("Invalid Date.");
		return false;
	}
	html += "<td class='label'>Date:</td>";
	html += "<td class='value1'><span id='date"+idx+"'>"+f_date_formated+"</span>";
	html += "<input type='hidden' id='f_date"+idx+"' class='hiddenform' name='f_dates["+idx+"]' value='"+f_date+"' />";
	html += "</td>";
	html += "</tr>";
	
	
	for(i=0; i<f_investment_org.length; i++){
		investment_org = f_investment_org[i];
		investment_org_val = f_investment_org_val[i];
		investment_org_val = uNum(investment_org_val);
		html += "<tr>";
		html += "<td class='label_ipc' colspan='2'>Investment Org:</td>";
		if(investment_org_val){
			html += "<td colspan='4'><a href='<?php echo site_url()?>investment_orgs/edit/"+investment_org_val+"'>"+investment_org+"</a></td>";
		}
		else{
			html += "<td colspan='4'>"+investment_org+"</td>";
		}
		html += "</tr>";
	}
	
	for(i=0; i<f_person.length; i++){
		person = f_person[i];
		person_val = f_person_val[i];
		person_val = uNum(person_val);
		html += "<tr>";
		html += "<td class='label_ipc' colspan='2'>Person:</td>";
		if(person_val){
			html += "<td colspan='4'><a href='<?php echo site_url()?>people/edit/"+person_val+"'>"+person+"</a></td>";
		}
		else{
			html += "<td colspan='4'>"+person+"</td>";
		}
		html += "</tr>";
	}
	
	
	for(i=0; i<f_company.length; i++){
		company = f_company[i];
		company_val = f_company_val[i];
		company_val = uNum(company_val);
		html += "<tr>";
		html += "<td class='label_ipc' colspan='2'>Company:</td>";
		if(company_val){
			html += "<td colspan='4'><a href='<?php echo site_url()?>companies/edit/"+company_val+"'>"+company+"</a></td>";
		}
		else{
			html += "<td colspan='4'>"+company+"</td>";
		}
		html += "</tr>";
	}
	
	
	
	html += "<tr>";
	html += "<td colspan='6' align='center'><a style='cursor:pointer; text-decoration:underline' class='red delete' onclick='delFunding(\"fundingtr"+idx+"\")' >Delete</a>&nbsp;&nbsp;&nbsp;<a style='cursor:pointer; text-decoration:underline' class='underline pointer' onclick='editFIntro(\""+idx+"\")' >Edit</a></td>"
	html += "</tr>";
	html += "</table>";
	
	html += "</td>";
	html += "</tr>";
	
	//f_round, f_currency, f_fund_amount, f_date, f_company, f_company_val, f_person, f_person_val, f_investment_org, f_investment_org_val
	jQuery("#f_fund_amount").val("");
	jQuery("#f_date").val("");
	jQuery("#f_company").val("");
	jQuery("#f_company_val").val("");
	jQuery("#f_person").val("");
	jQuery("#f_person_val").val("");
	jQuery("#f_investment_org").val("");
	jQuery("#f_investment_org_val").val("");
	
	jQuery("#fundingtr"+idx)[0].outerHTML = html;
	jQuery("#f_data"+idx).val(f_datastr);
	
	jQuery("#ipc tbody").html("");
	jQuery("#fundingadd").fadeOut(200, function(){
		jQuery('html, body').animate({
			scrollTop: jQuery("#fundingtr"+idx+" .fundingtable").offset().top
		}, 500);
	});
	
		
			
	
}

function addFunding(f_round, f_currency, f_fund_amount, f_date, f_company, f_company_val, f_person, f_person_val, f_investment_org, f_investment_org_val, add){
	
	f_data = {};
	f_data.f_round = f_round;
	f_data.f_currency = f_currency;
	f_data.f_fund_amount = f_fund_amount;
	f_data.f_date = f_date;
	f_data.f_company = f_company;
	f_data.f_company_val = f_company_val;
	f_data.f_person = f_person;
	f_data.f_person_val = f_person_val;
	f_data.f_investment_org = f_investment_org;
	f_data.f_investment_org_val = f_investment_org_val;
	f_datastr = JSON.stringify(f_data);
	
	
	html = "";
	if(add){
		html += "<tr id='fundingtr"+fundingindex+"'>";
		html += "<td id='fundingtd"+fundingindex+"'>";
	}
	else{
		html += "<tr id='fundingtr"+fundingindex+"'>";
		html += "<td id='fundingtd"+fundingindex+"'>";
	}
	
		
		
		for(i=0; i<f_company.length; i++){
			company = f_company[i];
			company_val = f_company_val[i];
			html += "<input type='hidden' name='f_companies"+fundingindex+"[]' value='"+company+"' />";
			html += "<input type='hidden' name='f_company_vals"+fundingindex+"[]' value='"+company_val+"' />";
		}
		
		for(i=0; i<f_person.length; i++){
			person = f_person[i];
			person_val = f_person_val[i];
			html += "<input type='hidden' name='f_people"+fundingindex+"[]' value='"+person+"' />";
			html += "<input type='hidden' name='f_person_vals"+fundingindex+"[]' value='"+person_val+"' />";
		}
		
		for(i=0; i<f_investment_org.length; i++){
			investment_org = f_investment_org[i];
			investment_org_val = f_investment_org_val[i];
			html += "<input type='hidden' name='f_investment_orgs"+fundingindex+"[]' value='"+investment_org+"' />";
			html += "<input type='hidden' name='f_investment_org_vals"+fundingindex+"[]' value='"+investment_org_val+"' />";
		}
		if(add){
			html += "<table class='fundingtable lightgreen' >";
		}
		else{
			html += "<table class='fundingtable'>";
		}
		html += "<tr>";
		html += "<td class='label'><input type='hidden' id='f_data"+fundingindex+"' />Round:</td>";
		html += "<td class='value0'><span id='round"+fundingindex+"'>"+f_round+"</span>";
		html += "<input type='hidden' id='round"+fundingindex+"' class='hiddenform' name='f_rounds["+fundingindex+"]' value='"+f_round+"' />";
		html +="</td>";
		
		f_fund_amount = uNum(f_fund_amount);
		f_fund_amount = fNum(f_fund_amount);
		html += "<td class='label'>Amount:</td>";
		html += "<td class='value1'><span id='currency"+fundingindex+"' >"+f_currency+"</span>";
		html += "<input type='hidden' id='f_currency"+fundingindex+"' class='hiddenform' name='f_currencies["+fundingindex+"]' value='"+f_currency+"' />";
		html += "<span id='fund_amount"+fundingindex+"'>"+f_fund_amount+"</span>";
		html += "<input type='hidden' id='f_fund_amount"+fundingindex+"' class='hiddenform' name='f_fund_amounts["+fundingindex+"]' value='"+uNum(f_fund_amount)+"' />";
		html += "</td>";
		
		try{
			thedate = new Date(f_date);
			thedate.setDate(thedate.getDate());
			f_date_formated = dateFormat(thedate, "mmm dd, yyyy");
		}
		catch(e){
			alert("Invalid Date.");
			return false;
		}
		html += "<td class='label'>Date:</td>";
		html += "<td class='value1'><span id='date"+fundingindex+"'>"+f_date_formated+"</span>";
		html += "<input type='hidden' id='f_date"+fundingindex+"' class='hiddenform' name='f_dates["+fundingindex+"]' value='"+f_date+"' />";
		html += "</td>";
		html += "</tr>";
		
		
		for(i=0; i<f_investment_org.length; i++){
			investment_org = f_investment_org[i];
			investment_org_val = f_investment_org_val[i];
			investment_org_val = uNum(investment_org_val);
			html += "<tr>";
			html += "<td class='label_ipc' colspan='2'>Investment Org:</td>";
			if(investment_org_val){
				html += "<td colspan='4'><a href='<?php echo site_url()?>investment_orgs/edit/"+investment_org_val+"'>"+investment_org+"</a></td>";
			}
			else{
				html += "<td colspan='4'>"+investment_org+"</td>";
			}
			html += "</tr>";
		}
		
		for(i=0; i<f_person.length; i++){
			person = f_person[i];
			person_val = f_person_val[i];
			person_val = uNum(person_val);
			html += "<tr>";
			html += "<td class='label_ipc' colspan='2'>Person:</td>";
			if(person_val){
				html += "<td colspan='4'><a href='<?php echo site_url()?>people/edit/"+person_val+"'>"+person+"</a></td>";
			}
			else{
				html += "<td colspan='4'>"+person+"</td>";
			}
			html += "</tr>";
		}
		
		
		for(i=0; i<f_company.length; i++){
			company = f_company[i];
			company_val = f_company_val[i];
			company_val = uNum(company_val);
			html += "<tr>";
			html += "<td class='label_ipc' colspan='2'>Company:</td>";
			if(company_val){
				html += "<td colspan='4'><a href='<?php echo site_url()?>companies/edit/"+company_val+"'>"+company+"</a></td>";
			}
			else{
				html += "<td colspan='4'>"+company+"</td>";
			}
			html += "</tr>";
		}
		
		
		
		html += "<tr>";
		html += "<td colspan='6' align='center'><a style='cursor:pointer; text-decoration:underline' class='red delete' onclick='delFunding(\"fundingtr"+fundingindex+"\")' >Delete</a>&nbsp;&nbsp;&nbsp;<a style='cursor:pointer; text-decoration:underline' class='underline pointer' onclick='editFIntro(\""+fundingindex+"\")' >Edit</a></td>"
		html += "</tr>";
		html += "</table>";
	
	html += "</td>";
	html += "</tr>";
	
	//f_round, f_currency, f_fund_amount, f_date, f_company, f_company_val, f_person, f_person_val, f_investment_org, f_investment_org_val
	jQuery("#f_fund_amount").val("");
	jQuery("#f_date").val("");
	jQuery("#f_company").val("");
	jQuery("#f_company_val").val("");
	jQuery("#f_person").val("");
	jQuery("#f_person_val").val("");
	jQuery("#f_investment_org").val("");
	jQuery("#f_investment_org_val").val("");
	
	htmlorig = jQuery("#fundinghtml table tbody").html();
	
	if(add){
		jQuery("#fundinghtml table tbody").html(html+htmlorig);
	}
	else{
		jQuery("#fundinghtml table tbody").html(htmlorig+html);
	}
	jQuery("#f_data"+fundingindex).val(f_datastr);
	fundingindex+=1;
	jQuery("#ipc tbody").html("");
	jQuery("#fundingadd").fadeOut(200);
}

function addPerson(id, name, role, start_date, end_date, add){
	if(!id){
		return false;
	}
	if(!start_date&&0){
		alert("Must input a start date.");
		return false;
	}
	id = uNum(id);
	/*
	if(people.indexOf(id)!=-1){
		alert(name+" is already part of this company.");
		return false;
	}
	*/
	
	people.push(id);
	jQuery("#peopleadd").hide();
	html = "";
	
	if(add){
		html += "<tr class='lightgreen'><td><input type='hidden' name='p_ids[]' value='"+id+"' />";
	}
	else{
		html += "<tr><td><input type='hidden' name='p_ids[]' value='"+id+"' />";
	}
	html += "<input type='hidden' name='p_roles[]' value='"+role+"' />";
	html += "<input type='hidden' name='p_start_dates[]' value='"+start_date+"' />";
	html += "<input type='hidden' name='p_end_dates[]' value='"+end_date+"' />";
	
	if(!end_date){
		end_date = "Present";
	}
	else{
		thedate = new Date(end_date);
		thedate.setDate(thedate.getDate());
		end_date = dateFormat(thedate, "mmm dd, yyyy");
	}
	
	if(start_date){
		thedate = new Date(start_date);
		thedate.setDate(thedate.getDate());
		start_date = dateFormat(thedate, "mmm dd, yyyy");
		html += "<a href='<?php echo site_url()?>people/edit/"+id+"' target=''>"+name+"</a></td><td>"+role+"</td><td>"+start_date+" to "+end_date+"</td><td><a style='cursor:pointer; text-decoration:underline' class='red delete' onclick='delPerson(this, \""+id+"\")' >Delete</a></td></tr>";
	}
	else{
		html += "<a href='<?php echo site_url()?>people/edit/"+id+"' target=''>"+name+"</a></td><td>"+role+"</td><td>&nbsp;</td><td><a style='cursor:pointer; text-decoration:underline' class='red delete' onclick='delPerson(this, \""+id+"\")' >Delete</a></td></tr>";
	}
	
	
	htmlorig = jQuery("#peoplehtml table tbody").html();
	
	if(add){
		html = html + htmlorig;
	}
	else{
		html = htmlorig + html;
	}
	jQuery("#peoplehtml table tbody").html(html);
}

function addCompetitorShortcut(company_name){
	jQuery("#competitor_search").attr("disabled", true);
	jQuery("#competitor_add_loader").html("<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />");
	jQuery.ajax({
		url: "<?php echo site_url(); ?>companies/ajax_add_competitor_shortcut",
		type: "POST",
		data: "name="+escape(company_name),
		dataType: "script",
		success: function(data){
			
		}
	});
}

function addPersonShortcut(name){
	jQuery("#people_search").attr("disabled", true);
	jQuery("#person_add_loader").html("<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />");
	jQuery.ajax({
		url: "<?php echo site_url(); ?>people/ajax_add_person_shortcut",
		type: "POST",
		data: "name="+escape(name),
		dataType: "script",
		success: function(data){
			
		}
	});
}
function addCompanyShortcutIPC(name, obj){
	jQuery.ajax({
		url: "<?php echo site_url(); ?>companies/ajax_add_company_shortcut_ipc",
		type: "POST",
		data: "name="+escape(name),
		dataType: "script",
		success: function(data){
			if(obj&&success){
				obj.attr("disabled", false);
				idx = obj.attr("alt");
				jQuery("#check_"+idx).html("<img src='<?php echo site_url(); ?>media/check.png' title='Item is in the database.' alt='Item is in the database.' />");
				jQuery("#"+idx).val(insert_id);
			}
			else if(obj){
				obj.attr("disabled", false);
				idx = obj.attr("alt");
				jQuery("#check_"+idx).html("");
			}
		}
	});
}
function addPersonShortcutIPC(name, obj){
	jQuery.ajax({
		url: "<?php echo site_url(); ?>people/ajax_add_person_shortcut_ipc",
		type: "POST",
		data: "name="+escape(name),
		dataType: "script",
		success: function(data){
			if(obj&&success){
				obj.attr("disabled", false);
				idx = obj.attr("alt");
				jQuery("#check_"+idx).html("<img src='<?php echo site_url(); ?>media/check.png' title='Item is in the database.' alt='Item is in the database.' />");
				jQuery("#"+idx).val(insert_id);
			}
			else if(obj){
				obj.attr("disabled", false);
				idx = obj.attr("alt");
				jQuery("#check_"+idx).html("");
			}
		}
	});
}
function addInvestmentOrgShortcutIPC(name, obj){
	jQuery.ajax({
		url: "<?php echo site_url(); ?>investment_orgs/ajax_add_investment_org_shortcut_ipc",
		type: "POST",
		data: "name="+escape(name),
		dataType: "script",
		success: function(data){
			if(obj&&success){
				obj.attr("disabled", false);
				idx = obj.attr("alt");
				jQuery("#check_"+idx).html("<img src='<?php echo site_url(); ?>media/check.png' title='Item is in the database.' alt='Item is in the database.' />");
				jQuery("#"+idx).val(insert_id);
			}
			else if(obj){
				obj.attr("disabled", false);
				idx = obj.attr("alt");
				jQuery("#check_"+idx).html("");
			}
		}
	});
}

function ipcEvent(){
	try{		
		jQuery(".f_company").each(function(){
			var objx = jQuery(this);
			jQuery(this).autocomplete({
				//define callback to format results
				source: function(req, add){
					idx = objx.attr("alt");
					jQuery("#check_"+idx).html("<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />");
					//pass request to server
					jQuery.getJSON("<?php echo site_url(); ?>companies/ajax_search", req, function(data) {
						//create array for response objects
						var suggestions = [];
						//process response
						jQuery.each(data, function(i, val){								
							suggestions.push(val);
						});
						val = [];
						val.label = "Create";
						val.value = -1;
						suggestions.push(val);
						
						//pass array to callback
						add(suggestions);
						idx = objx.attr("alt");
						jQuery("#check_"+idx).html("");
					});
				},
				//define select handler
				select: function(e, ui) {
					label = ui.item.label;
					value = ui.item.value;
					if(value!=-1){
						jQuery(this).val(label)
						idx = jQuery(this).attr("alt");
						jQuery("#check_"+idx).html("<img src='<?php echo site_url(); ?>media/check.png' title='Item is in the database.' alt='Item is in the database.' />");
						jQuery("#"+idx).val(value);
					}
					else{
						idx = jQuery(this).attr("alt");
						jQuery("#check_"+idx).html("<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />");
						jQuery(this).attr("disabled", true);
						addCompanyShortcutIPC(this.value, jQuery(this));
					}
					return false;
				},
				focus: function(e, ui) {
					idx = jQuery(this).attr("alt");
					jQuery("#check_"+idx).html("");
					label = ui.item.label;
					value = ui.item.value;
					if(value!=-1){
						jQuery(this).val(label)
					}
					return false;
				},
				search: function(e, ui) {
					jQuery("#tempcreatelabel").val( jQuery(this).val());
					idx = jQuery(this).attr("alt");
					jQuery("#check_"+idx).html("");
				}
			}).data( "autocomplete" )._renderItem = function( ul, item ) {
				value = item.value;
				label = item.label;
				append = "";
				if(item.value==-1){
					append = "<div class='additem'>"+label+" '"+jQuery("#tempcreatelabel").val()+"'</div>";
					return $( "<li>" )
						.data( "item.autocomplete", item )
						.append( "<a>" + append + "</a>")
						.appendTo( ul );
				}
				else{
					if(item.desc){
						append = "<div class='more'>" + item.desc + "</div>";
					}
					return $( "<li>" )
						.data( "item.autocomplete", item )
						.append( "<a>" + item.label + append + "</a>")
						.appendTo( ul );
				}
			};	
		});
	}
	catch(e){
		
	}

	try{
		jQuery(".f_investment_org").each(function(){
			var objx = jQuery(this);
			jQuery(this).autocomplete({
				//define callback to format results
				source: function(req, add){
					idx = objx.attr("alt");
					jQuery("#check_"+idx).html("<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />");
					//pass request to server
					jQuery.getJSON("<?php echo site_url(); ?>investment_orgs/ajax_search", req, function(data) {
						//create array for response objects
						var suggestions = [];
						//process response
						jQuery.each(data, function(i, val){								
							suggestions.push(val);
						});
						val = [];
						val.label = "Create";
						val.value = -1;
						suggestions.push(val);
						
						//pass array to callback
						add(suggestions);
						idx = objx.attr("alt");
						jQuery("#check_"+idx).html("");
					});
				},
				//define select handler
				select: function(e, ui) {
					label = ui.item.label;
					value = ui.item.value;
					if(value!=-1){
						jQuery(this).val(label)
						idx = jQuery(this).attr("alt");
						jQuery("#check_"+idx).html("<img src='<?php echo site_url(); ?>media/check.png' title='Item is in the database.' alt='Item is in the database.' />");
						jQuery("#"+idx).val(value);
					}
					else{					
						idx = jQuery(this).attr("alt");
						jQuery("#check_"+idx).html("<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />");
						jQuery(this).attr("disabled", true);
						addInvestmentOrgShortcutIPC(this.value, jQuery(this));
					}
					return false;
				},
				focus: function(e, ui) {
					idx = jQuery(this).attr("alt");
					jQuery("#check_"+idx).html("");
					label = ui.item.label;
					value = ui.item.value;
					if(value!=-1){
						jQuery(this).val(label)
					}
					return false;
				},
				search: function(e, ui) {
					jQuery("#tempcreatelabel").val( jQuery(this).val());
					idx = jQuery(this).attr("alt");
					jQuery("#check_"+idx).html("");
				}
			}).data( "autocomplete" )._renderItem = function( ul, item ) {
				value = item.value;
				label = item.label;
				append = "";
				if(item.value==-1){
					append = "<div class='additem'>"+label+" '"+jQuery("#tempcreatelabel").val()+"'</div>";
					return $( "<li>" )
						.data( "item.autocomplete", item )
						.append( "<a>" + append + "</a>")
						.appendTo( ul );
				}
				else{
					if(item.desc){
						append = "<div class='more'>" + item.desc + "</div>";
					}
					return $( "<li>" )
						.data( "item.autocomplete", item )
						.append( "<a>" + item.label + append + "</a>")
						.appendTo( ul );
				}
			};	
		});
	}
	catch(e){
	}
	
	try{
		jQuery(".f_person").each(function(){
			var objx = jQuery(this);
			jQuery(this).autocomplete({
				//define callback to format results
				source: function(req, add){
					//pass request to server
					idx = objx.attr("alt");
					jQuery("#check_"+idx).html("<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />");
					jQuery.getJSON("<?php echo site_url(); ?>people/ajax_search", req, function(data) {
						//create array for response objects
						var suggestions = [];
						//process response
						jQuery.each(data, function(i, val){								
							suggestions.push(val);
						});
						val = [];
						val.label = "Create";
						val.value = -1;
						suggestions.push(val);
						
						//pass array to callback
						add(suggestions);
						idx = objx.attr("alt");
						jQuery("#check_"+idx).html("");
					});
				},
				//define select handler
				select: function(e, ui) {
					label = ui.item.label;
					value = ui.item.value;
					if(value!=-1){
						jQuery(this).val(label)
						idx = jQuery(this).attr("alt");
						jQuery("#check_"+idx).html("<img src='<?php echo site_url(); ?>media/check.png' title='Item is in the database.' alt='Item is in the database.' />");
						jQuery("#"+idx).val(value);
					}
					else{
						idx = jQuery(this).attr("alt");
						jQuery("#check_"+idx).html("<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />");
						jQuery(this).attr("disabled", true);
						addPersonShortcutIPC(this.value, jQuery(this));
					}
					return false;
				},
				focus: function(e, ui) {
					idx = jQuery(this).attr("alt");
					jQuery("#check_"+idx).html("");
					label = ui.item.label;
					value = ui.item.value;
					if(value!=-1){
						jQuery(this).val(label)
					}
					return false;
				},
				search: function(e, ui) {
					jQuery("#tempcreatelabel").val( jQuery(this).val());
					idx = jQuery(this).attr("alt");
					jQuery("#check_"+idx).html("");
				}
			}).data( "autocomplete" )._renderItem = function( ul, item ) {
				value = item.value;
				label = item.label;
				append = "";
				if(item.value==-1){
					append = "<div class='additem'>"+label+" '"+jQuery("#tempcreatelabel").val()+"'</div>";
					return $( "<li>" )
						.data( "item.autocomplete", item )
						.append( "<a>" + append + "</a>")
						.appendTo( ul );
				}
				else{
					if(item.desc){
						append = "<div class='more'>" + item.desc + "</div>";
					}
					return $( "<li>" )
						.data( "item.autocomplete", item )
						.append( "<a>" + item.label + append + "</a>")
						.appendTo( ul );
				}
			};	
		});
	}
	catch(e){
	}
}
jQuery(function(){
	jQuery("#f_fund_amount").blur(function(){
		v = jQuery("#f_fund_amount").val();
		v = uNum(v);
		v = fNum(v);
		jQuery("#f_fund_amount").val(v);
	});
	jQuery("#competitor_search").autocomplete({
		//define callback to format results
		source: function(req, add){
			//pass request to server
			jQuery("#competitor_add_loader").html("<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />");
			jQuery.getJSON("<?php echo site_url(); ?>companies/ajax_search", req, function(data) {
				//create array for response objects
				var suggestions = [];
				//process response
				jQuery.each(data, function(i, val){								
					suggestions.push(val);
				});
				val = [];
				val.label = "Create";
				val.value = -1;
				suggestions.push(val);
				
				//pass array to callback
				add(suggestions);
				jQuery("#competitor_add_loader").html("");
			});
		},
		//define select handler
		select: function(e, ui) {
			label = ui.item.label;
			value = ui.item.value;
			if(value!=-1){
				jQuery("#competitor_search").val("");
				addCompetitor(label, value, true);
			}
			else{
				addCompetitorShortcut(this.value);
			}
			return false;
		},
		focus: function(e, ui) {
			label = ui.item.label;
			value = ui.item.value;
			if(value!=-1){
				jQuery("#competitor_search").val(label);
			}
			return false;
		},
		search: function(e, ui) {
			jQuery("#tempcreatelabel").val( jQuery(this).val());
		}
	}).data( "autocomplete" )._renderItem = function( ul, item ) {
		value = item.value;
		label = item.label;
		append = "";
		if(item.value==-1){
			append = "<div class='additem'>"+label+" '"+jQuery("#tempcreatelabel").val()+"'</div>";
			return $( "<li>" )
				.data( "item.autocomplete", item )
				.append( "<a>" + append + "</a>")
				.appendTo( ul );
		}
		else{
			if(item.desc){
				append = "<div class='more'>" + item.desc + "</div>";
			}
			return $( "<li>" )
				.data( "item.autocomplete", item )
				.append( "<a>" + item.label + append + "</a>")
				.appendTo( ul );
		}
		
	};	

	
	jQuery("#people_search").autocomplete({
		//define callback to format results
		source: function(req, add){
			//pass request to server
			jQuery("#person_add_loader").html("<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />");
			jQuery.getJSON("<?php echo site_url(); ?>people/ajax_search", req, function(data) {
				//create array for response objects
				var suggestions = [];
				//process response
				jQuery.each(data, function(i, val){								
					suggestions.push(val);
				});
				val = [];
				val.label = "Create";
				val.value = -1;
				suggestions.push(val);
				//pass array to callback
				add(suggestions);
				jQuery("#person_add_loader").html("");
			});
		},
		//define select handler
		select: function(e, ui) {
			label = ui.item.label;
			value = ui.item.value;
			
			if(value!=-1){
				jQuery("#people_search").val("");
				peoplePreAdd(label, value);
			}
			else{
				addPersonShortcut(this.value);
			}
			return false;
		},
		focus: function(e, ui) {
			label = ui.item.label;
			value = ui.item.value;
			if(value!=-1){
				jQuery("#people_search").val(label);
			}
			return false;
		},
		search: function(e, ui) {
			jQuery("#tempcreatelabel").val( jQuery(this).val());
		}
	}).data( "autocomplete" )._renderItem = function( ul, item ) {
		value = item.value;
		label = item.label;
		append = "";
		if(item.value==-1){
			append = "<div class='additem'>"+label+" '"+jQuery("#tempcreatelabel").val()+"'</div>";
			return $( "<li>" )
				.data( "item.autocomplete", item )
				.append( "<a>" + append + "</a>")
				.appendTo( ul );
		}
		else{
			if(item.desc){
				append = "<div class='more'>" + item.desc + "</div>";
			}
			return $( "<li>" )
				.data( "item.autocomplete", item )
				.append( "<a>" + item.label + append + "</a>")
				.appendTo( ul );
		}
		
	};
	
	ipcEvent();

	jQuery("#co_name").blur(function(){
		checkCompany(jQuery("#co_name").val());
	});
	
	jQuery('#co_logo').uploadify({
		'uploader'  : '<?php echo site_url(); ?>media/js/uploadify/uploadify.swf',
		'script'    : '<?php echo site_url(); ?>media/js/uploadify/uploadify.php',
		'cancelImg' : '<?php echo site_url(); ?>media/js/uploadify/cancel.png',
		'folder'    : '<?php
			$folder = dirname(__FILE__)."/../../../media/uploads/";
			if(!is_dir($folder)){
				mkdir($folder, 0777);
			}
			if($company['id']){
				$folder = dirname(__FILE__)."/../../../media/uploads/".$company['id'];
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/../../../media/uploads/".$company['id']."/logo";
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
			}
			else{
				$folder = dirname(__FILE__)."/../../../media/uploads/temp";
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/../../../media/uploads/temp/".$sid ;
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/../../../media/uploads/temp/".$sid."/logo";
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
			}
			echo str_replace(dirname(__FILE__)."/../../..", "", $folder);
		?>',
		'auto'      : true,
		'multi'       : false,
		'onComplete'  : function(event, ID, fileObj, response, data) {
		  //alert('There are ' + data.fileCount + ' files remaining in the queue.');
		  str = "";
		  for(x in fileObj){
		  	str += x+"\n";
		  }
		  //alert(str);
		  //alert(fileObj.filePath);		  
		  logopath = "<?php echo site_url(); ?>"+fileObj.filePath;
		  refreshLogo(logopath);
		}	
	});
	
	jQuery('#co_screenshots').uploadify({
		'uploader'  : '<?php echo site_url(); ?>media/js/uploadify/uploadify.swf',
		'script'    : '<?php echo site_url(); ?>media/js/uploadify/uploadify.php',
		'cancelImg' : '<?php echo site_url(); ?>media/js/uploadify/cancel.png',
		'folder'    : '<?php
			$folder = dirname(__FILE__)."/../../../media/uploads/";
			if(!is_dir($folder)){
				mkdir($folder, 0777);
			}
			if($company['id']){
				$folder = dirname(__FILE__)."/../../../media/uploads/".$company['id'];
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/../../../media/uploads/".$company['id']."/screenshots";
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
			}
			else{
				$folder = dirname(__FILE__)."/../../../media/uploads/temp";
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/../../../media/uploads/temp/".$sid;
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
				$folder = dirname(__FILE__)."/../../../media/uploads/temp/".$sid."/screenshots";
				if(!is_dir($folder)){
					mkdir($folder, 0777);
				}
			}
			echo str_replace(dirname(__FILE__)."/../../..", "", $folder);
		?>',
		'auto'      : true,
		'multi'       : true,
		'onComplete'  : function(event, ID, fileObj, response, data) {
		  //alert('There are ' + data.fileCount + ' files remaining in the queue.');
		  str = "";
		  for(x in fileObj){
		  	str += x+"\n";
		  }
		  //alert(str);
		  //alert(fileObj.filePath);		  
		  fp = fileObj.filePath;
		  //remove slash at fron of path
		  while(fp[0]=='/'){
		  	fp = fp.substring(1);
		  }
		  
		  filepath = "<?php echo site_url(); ?>"+fp;
		  refreshScreenshots(filepath);
		}	
	});
	
	
	
	
});

function addFIntro(){
	setFFormDefaults();
	jQuery("#f_save_button").hide();
	jQuery("#f_add_button").show();
	jQuery("#fundingadd").slideDown(200);
	jQuery("#ipc tbody").html("");
	jQuery("#f_cancel_button").attr("alt", "");
}

function cancelFJS(idx){
	setFFormDefaults();
	jQuery("#fundingadd").fadeOut(200, function(){
		if(idx!=""){
			jQuery('html, body').animate({
				scrollTop: jQuery("#fundingtr"+idx+" .fundingtable").offset().top
			}, 500);
		}
	});
}

function editFJS(idx){
	ios = jQuery(".f_investment_org");
	iosv = jQuery(".f_investment_org_val");
	ips = jQuery(".f_person");
	ipsv = jQuery(".f_person_val");
	ics = jQuery(".f_company");
	icsv = jQuery(".f_company_val");
	
	company = [];
	company_val = [];
	person = [];
	person_val = [];
	investment = [];
	investment_val = [];
		
	for(i=0; i<ics.length; i++){
		icsv[i].value = uNum(icsv[i].value);
		if(ics[i].value){
			if(!icsv[i].value||(icsv[i].value&&company_val.indexOf(icsv[i].value)==-1)){
				company.push(ics[i].value);
				company_val.push(icsv[i].value);
			}
		}
	}
	
	for(i=0; i<ips.length; i++){
		ipsv[i].value = uNum(ipsv[i].value);
		if(ips[i].value){
			if(!ipsv[i].value||(ipsv[i].value&&person_val.indexOf(ipsv[i].value)==-1)){
				person.push(ips[i].value);
				person_val.push(ipsv[i].value);
			}
		}
	}
	
	for(i=0; i<ios.length; i++){
		iosv[i].value = uNum(iosv[i].value);
		if(ios[i].value){
			if(!iosv[i].value||(iosv[i].value&&investment_val.indexOf(iosv[i].value)==-1)){
				investment.push(ios[i].value);
				investment_val.push(iosv[i].value);
			}
		}
	}
	
	editFunding(
		jQuery("#f_round").val(),
		jQuery("#f_currency").val(),
		jQuery("#f_fund_amount").val(),
		jQuery("#f_date").val(),
		company,
		company_val,
		person,
		person_val,
		investment,
		investment_val,
		idx
	);
}

function addFJS(){
	ios = jQuery(".f_investment_org");
	iosv = jQuery(".f_investment_org_val");
	ips = jQuery(".f_person");
	ipsv = jQuery(".f_person_val");
	ics = jQuery(".f_company");
	icsv = jQuery(".f_company_val");
	
	company = [];
	company_val = [];
	person = [];
	person_val = [];
	investment = [];
	investment_val = [];
		
	for(i=0; i<ics.length; i++){
		icsv[i].value = uNum(icsv[i].value);
		if(ics[i].value){
			if(!icsv[i].value||(icsv[i].value&&company_val.indexOf(icsv[i].value)==-1)){
				company.push(ics[i].value);
				company_val.push(icsv[i].value);
			}
		}
	}
	
	for(i=0; i<ips.length; i++){
		ipsv[i].value = uNum(ipsv[i].value);
		if(ips[i].value){
			if(!ipsv[i].value||(ipsv[i].value&&person_val.indexOf(ipsv[i].value)==-1)){
				person.push(ips[i].value);
				person_val.push(ipsv[i].value);
			}
		}
	}
	
	for(i=0; i<ios.length; i++){
		iosv[i].value = uNum(iosv[i].value);
		if(ios[i].value){
			if(!iosv[i].value||(iosv[i].value&&investment_val.indexOf(iosv[i].value)==-1)){
				investment.push(ios[i].value);
				investment_val.push(iosv[i].value);
			}
		}
	}
	
	addFunding(
		jQuery("#f_round").val(),
		jQuery("#f_currency").val(),
		jQuery("#f_fund_amount").val(),
		jQuery("#f_date").val(),
		company,
		company_val,
		person,
		person_val,
		investment,
		investment_val,
		true
	);
}
function deleteIPC(obj){
	obj.parentElement.parentElement.outerHTML = "";
}
fi = 0;
function addFI(){
	fi += 1;
	html = "";
	html += "<tr>";
	html += "<td>Investment Org:</td>";
	html += "<td>";
	html += "<input type='text' class='f_investment_org' alt='fi"+fi+"'>";
	html += "<input type='hidden' class='f_investment_org_val' id='fi"+fi+"'>";
	html += "<div class='inline f_check' id='check_fi"+fi+"'></div>&nbsp;<div class='red cursor inline f_delete' onclick='deleteIPC(this)'>[ x ]</div>";
	html += "</td>";
	html += "</tr>";
	jQuery("#ipc tbody").append(html);
	ipcEvent();
}

pi = 0;
function addFP(){
	pi += 1;
	html = "";
	html += "<tr>";
	html += "<td>Person:</td>";
	html += "<td>";
	html += "<input type='text' class='f_person' alt='pi"+pi+"'>";
	html += "<input type='hidden' class='f_person_val' id='pi"+pi+"'>";
	html += "<div class='inline f_check' id='check_pi"+pi+"'></div>&nbsp;<div class='red cursor inline f_delete' onclick='deleteIPC(this)'>[ x ]</div>";
	html += "</td>";
	html += "</tr>";
	jQuery("#ipc tbody").append(html);
	ipcEvent();
}

ci = 0;
function addFC(){
	ci += 1;
	html = "";
	html += "<tr>";
	html += "<td>Company:</td>";
	html += "<td>";
	html += "<input type='text' class='f_company' alt='ci"+ci+"'>";
	html += "<input type='hidden' class='f_company_val' id='ci"+ci+"'>";
	html += "<div class='inline f_check' id='check_ci"+ci+"'></div>&nbsp;<div class='red cursor inline f_delete' onclick='deleteIPC(this)'>[ x ]</div>";
	html += "</td>";
	html += "</tr>";
	jQuery("#ipc tbody").append(html);
	ipcEvent();
}
</script>
<input type='hidden' id='tempcreatelabel' />
<form id='company_form'>

<?php
if($company['id']){
	?>
	<input type='hidden' name='id' id='co_id' >
	<?php
}
else{
	?>
	<input type='hidden' name='sid' value="<?php echo sanitizeX($sid); ?>">
	<?php
}


?>
<table width="100%" cellpadding="10px">
<!--
<tr>
<td colspan="2" class='center bold font14'>Fields with * are required.</td>
</tr>
-->
<?php
//echo "<pre>";
//print_r($revision);
//print_r($web_user);
if(!$company['id']&&!$web_user){
	?>
	<tr>
	<td class='font18 bold'>Add New Company</td>
	<td></td>
	</tr>
	<?php
}
else if(!$web_user){
	?>
	<tr>
	<td class='font18 bold'>Edit Company - <a class='font16 bold' href='<?php echo site_url();?>company/<?php echo $company['slug']; ?>'>Click here to preview</a></td>
	<td></td>
	</tr>
	<?php
}
else if($web_user&&$revision){
	if($revision['approved']==1){
		$approved = "<a style='color:green' class='font18 bold'>(APPROVED)</a>";
	}
	else if($revision['approved']==-1){
		$approved = "<a style='color:red' class='font18 bold'>(REJECTED)</a>";
	}
	else{
		$approved = "<a style='color:black' class='font18 bold'>(PENDING)</a>";
	}
	?>
	<tr>
	<td class='font18 bold'>Revision on <?php echo "<a class='font18 bold' href='".site_url()."companies/edit/".$companyorig['id']."'>".$companyorig['name']."</a>"; ?> by <a class='font18 bold' href='<?php echo site_url()?>/account/<?php echo $web_user['id']?>'><?php echo $web_user['name']; ?></a> <?php echo $approved; ?></td>
	<td></td>
	</tr>
	<?php
}
else if($web_user&&$contribution){
	if($contribution['table']=='companies'){
		$table = "Company";
	}
	else if($contributions[$i]['table']=='people'){
		$table = "Person";
	}
	else if($contributions[$i]['table']=='investment_orgs'){
		$table = "Investment Org";
	}
	if($contribution['approved']==1){
		$approved = "<a style='color:green' class='font18 bold'>(APPROVED)</a>";
	}
	else if($contribution['approved']==-1){
		$approved = "<a style='color:red' class='font18 bold'>(REJECTED)</a>";
	}
	else{
		$approved = "<a style='color:black' class='font18 bold'>(PENDING)</a>";
	}
	?>
	<tr>
	<td class='font18 bold'><?php echo $table;?> Contributed by <a class='font18 bold' href='<?php echo site_url()?>/account/<?php echo $web_user['id']?>'><?php echo $web_user['name']; ?></a> <?php echo $approved; ?></td>
	<td></td>
	</tr>
	<?php
}
?>
<tr>
<td width='50%'> 
  <table width="100%">

    <tr class="odd required <?php if(in_array("name", $changed)){ echo "changed";} ?>">
      <td>* Company Name:</td>
      <td><input type="text" name="name" size="40" id='co_name'><div class='inline' style='padding-left:5px;' id='co_check'></div> <?php
	  
	  if($company['status']=="Closed"){
			echo " <a style='color:red'>[Closed]</a>";
		}
	  
	  ?></td>
    </tr>
    <tr class="even required <?php if(in_array("description", $changed)){ echo "changed";} ?>">
      <td>* Description:</td>
      <td><textarea name="description"></textarea></td>
    </tr>
    <tr class="odd <?php if(in_array("co_categories", $changed)){ echo "changed";} ?>">
      <td>Category:</td>
      <td>
	  <select multiple="multiple" name='categories[]'>
       <?php
	  	foreach($categories as $value){
			
			if(is_array($co_categories)&&in_array($value['id'], $co_categories)){
				?>
				<option selected="selected" value="<?php echo sanitizeX($value['id']); ?>"><?php echo sanitizeX($value['category']); ?></option>
				<?php
			}
			else{
				?>
				<option value="<?php echo sanitizeX($value['id']); ?>"><?php echo sanitizeX($value['category']); ?></option>
				<?php
			}
		}
	  ?>
        </select>
      </td>
    </tr>
	<tr class="even <?php if(in_array("email_address", $changed)){ echo "changed";} ?>">
      <td>* Email Address: </td>
      <td><input type="text" name="email_address" size="35"></td>
    </tr>
    <tr class="odd <?php if(in_array("website", $changed)){ echo "changed";} ?>">
      <td>Website: </td>
      <td><input type="text" name="website" size="30">
        <div class='hint'>e.g. http://www.yourcompany.com</div></td>
    </tr>
    <tr class="even <?php if(in_array("blog_url", $changed)){ echo "changed";} ?>">
      <td>Blog URL:</td>
      <td><input type="text" name="blog_url" size="30">
        <div class='hint'>e.g. http://e27.sg/</div></td>
    </tr>
	<tr class="even <?php if(in_array("blog", $changed)){ echo "changed";} ?>">
      <td>Blog RSS feed URL:</td>
      <td><input type="text" name="blog" size="30">
        <div class='hint'>e.g. http://e27.sg/feed</div></td>
    </tr>
    <tr class="odd <?php if(in_array("twitter_username", $changed)){ echo "changed";} ?>">
      <td>Twitter Username:</td>
      <td><input type="text" name="twitter_username" size="25">
        <div class='hint'>e.g. @kiip</div></td>
    </tr>
    <tr class="even <?php if(in_array("facebook", $changed)){ echo "changed";} ?>">
      <td>Facebook Page:</td>
      <td><input type="text" name="facebook" size="35">
        <div class='hint'>e.g. http://facebook.com/yourpagename</div></td>
    </tr>
    <tr class="odd <?php if(in_array("linkedin", $changed)){ echo "changed";} ?>">
      <td>LinkedIn Page:</td>
      <td><input type="text" name="linkedin" size="35">
        <div class='hint'>e.g. http://linkedin.com/yourpagename</div></td>
    </tr>
    <tr class="even <?php if(in_array("number_of_employees", $changed)){ echo "changed";} ?>">
      <td>Number of Employees: </td>
      <td><input type="text" name="number_of_employees" size="5"></td>
    </tr>

    <tr class="odd <?php if(in_array("founded", $changed)){ echo "changed";} ?>">
      <td>Founded:</td>
      <td>
	  	<input type='text' class='datepicker' alt='founded' id='founded_pick' name='founded' /><div class='hint'>yyyy or mm/dd/yyyy</div>
		<?php /*<input type='hidden' name='founded' id='founded' > */ ?>
	  	<?php
		/*
		// lowest year wanted
		$cutoff = 1910;

		// current year
		$now = date('Y');

		// build years menu
		echo '<select name="found_year">' . PHP_EOL;
		for ($y=$now; $y>=$cutoff; $y--) {
			echo '  <option value="' . $y . '">' . $y . '</option>' . PHP_EOL;
		}
   		echo '</select>' . PHP_EOL;

		// build months menu
		echo '<select name="found_month">' . PHP_EOL;
		for ($m=1; $m<=12; $m++) {
			echo '  <option value="' . $m . '">' . date('M', mktime(0,0,0,$m)) . '</option>' . PHP_EOL;
		}
		echo '</select>' . PHP_EOL;

		// build days menu
		echo '<select name="found_day">' . PHP_EOL;
		for ($d=1; $d<=31; $d++) {
			echo '  <option value="' . $d . '">' . $d . '</option>' . PHP_EOL;
		}
		echo '</select>' . PHP_EOL;
		*/
		?>
      </td>
    </tr>
    <tr class="even <?php if(in_array("logo", $changed)){ echo "changed";} ?>">
      <td>Company logo:</td>
      <td>
	  <div id='logopathhtml'></div>
	  <input type='hidden' id='logopath' name='logo' />
	  <input type='text' id="co_logo" />
	  <input type='button' class='button normal' value='Upload' onclick="jQuery('#co_logo').uploadifyUpload();" >
	  <br><div class='hint'>e.g. Image Suggestion 220 x 220 pixels .jpg file</div>
	  </td>
    </tr>
    <tr class="odd <?php if(in_array("country", $changed)){ echo "changed";} ?>">
      <td>Country:</td>
      <td><select name="country">
	  <?php 
		if($company['id']){
			$incountrylist = false;
			foreach($countries as $value){
				if($value['country']==$company['country']){
					$incountrylist = true;
					break;
				}
			}
			if(!$incountrylist){
				?><option value="<?php echo sanitizeX($company['country']); ?>">Please select a country</option><?php
			}
		}
	  	foreach($countries as $value){
			if(strtolower(trim($value['country']))=="singapore"){
				?>
				<option selected="selected" value="<?php echo sanitizeX($value['country']); ?>"><?php echo sanitizeX($value['country']); ?></option>
				<?php
			}
			else{
				?>
				<option value="<?php echo sanitizeX($value['country']); ?>"><?php echo sanitizeX($value['country']); ?></option>
				<?php
			}
		}
	  ?>
        </select>
		<?php
		if($company['id']){
			if(!$incountrylist){
				?><div class='hint bold' style='color:red'>Imported country value: <?php echo $company['country']; ?></div><?php
			}
		}
		?>
      </td>
    </tr>
    
    
    <tr class="even <?php if(in_array("tags", $changed)){ echo "changed";} ?>">
      <td>Tags:</td>
      <td><textarea name="tags" ></textarea>
      <br/>
      <div class='hint'>multiple tags must be comma separated. e.g. company,person,power</div>
      </td>
    </tr>
    <tr class="odd <?php if(in_array("screenshots", $changed)){ echo "changed";} ?>">
      <td>Screenshots:</td>
      <td>
	  <div id='sspathhtml' style='padding-bottom:10px;'></div>
	  <input type='text' id="co_screenshots" />
	  <input type='button' class='button normal' value='Upload' onclick="jQuery('#co_screenshots').uploadifyUpload();" ></td>
    </tr>
    
    <tr class="even">
      <td>Status:</td>
      <td><select name="status">
          <option value="Live">Live</option>
          <option value="Closed">Closed</option>
        </select>
      </td>
    </tr>
    <tr class="odd">
      <td>Active?</td>
      <td><input type="checkbox" name="active" value="1" checked="checked" />
      </td>
    </tr>
  </table>
</td>
<td width='50%'>
	<table width="100%">
		<tr class="odd <?php if(in_array("people", $changed)){ echo "changed";} ?>">
		  <td>People:</td>
		  <td>
		  <input type="text" size: "30" id="people_search" /><div class='inline' id='person_add_loader'></div><div class='hint'>Type in the name to search and add people.</div>
		  <div id='peopleadd' style='display:none'>
		  	<input type='hidden' id='p_id' />
		  	<table class='border margin10 pad10'>
				<tr>
					<td>Name:</td>
					<td id='p_name'></td>
				</tr>
				<tr>
					<td>Role:</td>
					<td><input type='text' id='p_role' /></td>
				</tr>
				<tr>
					<td>Start Date:</td>
					<td><input type='text' id='p_start_date' class='datepicker' /><div class='hint'>mm/dd/yyyy</div></td>
				</tr>
				<tr>
					<td>End Date:</td>
					<td><input type='text' id='p_end_date' class='datepicker' /><div class='hint'>mm/dd/yyyy (leave blank if present)</div></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><input type='button' value='Add Person' class='button normal' onclick='addPerson(jQuery("#p_id").val(), jQuery("#p_name").html(), jQuery("#p_role").val(), jQuery("#p_start_date").val(), jQuery("#p_end_date").val(), true)' >&nbsp;
					<input type='button' value='Cancel' class='button normal' onclick='jQuery("#peopleadd").hide()' /></td>
				</tr>
			</table>
		  </div>
		  <div class='margin10 pad10'>
		  	<div id='peoplehtml'><table cellspacing=0><tbody></tbody></table></div>
		  </div>
		  </td>
		</tr>
		<tr class='even <?php if(in_array("competitors", $changed)){ echo "changed";} ?>'>
		  <td>Competitors:</td>
		  <td>
		  <input type="text" size="50" id="competitor_search" /><div class='inline' id='competitor_add_loader'></div><div class='hint'>Type in the company name to search and add competitor.</div>
			<div id="competitors_html" class='margin10 pad10'><table cellspacing=0><tbody></tbody></table></div>

		  </td>
		</tr>
		<tr class="odd <?php if(in_array("company_fundings", $changed)){ echo "changed";} ?> " id="funding" >
		  <td>Funding:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class='cursor' onclick='addFIntro()'>[+]</a></td>
		  <td>
		  	<table class='border margin10 pad10 hidden' id='fundingadd' >
				<tr>
					<td>Round:</td>
					<td>
						<select id='f_round'>
						<?php
						$t = count($funding_rounds);
						for($i=0; $i<$t; $i++){
							?><option value="<?php echo sanitizeX($funding_rounds[$i]['round']) ?>"><?php echo sanitizeX($funding_rounds[$i]['round']) ?></option><?php
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Currency:</td>
					<td>
						<select id='f_currency'>
						<?php
						$t = count($currencies);
						for($i=0; $i<$t; $i++){
							if($currencies[$i]['code']=="SGD"){
								?><option selected="selected" value="<?php echo sanitizeX($currencies[$i]['code']) ?>"><?php echo sanitizeX($currencies[$i]['currency']." (".$currencies[$i]['code'].")") ?></option><?php
							}
							else{
								?><option value="<?php echo sanitizeX($currencies[$i]['code']) ?>"><?php echo sanitizeX($currencies[$i]['currency']." (".$currencies[$i]['code'].")") ?></option><?php
							}
						}
						?>
						</select>
					</td>
				</tr>				
				<tr>
					<td>Amount:</td>
					<td>
						<input type='text' id='f_fund_amount' />
					</td>
				</tr>
				<tr>
					<td>Date:</td>
					<td>
						<input type='text' id='f_date' class='datepicker' /><div class='hint'>mm/dd/yyyy</div>
					</td>
				</tr>
				
				<tr>
					<td colspan="2" align="center" class='pad10'>
					
					<a class='cursor bold underline font14' onclick='addFI()'>Investment Org</a>&nbsp;&nbsp;&nbsp;
					<a class='cursor bold underline font14' onclick='addFP()'>Person</a>&nbsp;&nbsp;&nbsp
					<a class='cursor bold underline font14' onclick='addFC()'>Company</a>
					
					<table id='ipc' width="100%">
						<tbody>
						</tbody>
					</table>
					</td>
				</tr>
				<tr>
					<td align="center" colspan="2" style='padding-top:10px;'><input id='f_save_button' type='button' class='button normal hidden' value='   Done Editing   ' onclick='editFJS(this.alt)'>&nbsp;&nbsp;<input id='f_add_button' type='button' class='button normal' value='   Add Funding   ' onclick='addFJS()'>&nbsp;&nbsp;<input type='button' class='button normal' id='f_cancel_button' value='Cancel' onclick='cancelFJS(this.alt)'> </td>
				</tr>
			</table>
			
		 	
		  </td>
		</tr>
		<tr class="odd <?php if(in_array("company_fundings", $changed)){ echo "changed";} ?>">
			<td colspan="2" align="center">
				<div id="fundinghtml" class='pad10'><table cellspacing=0 width="90%"><tbody></tbody></table></div>
			</td>
		</tr>
		
		<tr class="even">
		  <td>Investments:</td>
		  <td>
			<div class='margin10 pad10'>
		  		<div id='milestoneshtml'>
				<?php
				if(is_array($milestones)){
					?><table cellspacing=0><?php
					foreach($milestones as $value){
						echo "<tr>";
						?><td><a href="<?php echo site_url()?>companies/edit/<?php echo $value['company_id']; ?>"><?php echo $value['company_name']; ?></a></td><?php
						?><td><?php echo ucfirst($value['round']) ?></td><?php
						?><td><?php echo $value['currency']; ?> <?php echo number_format($value['amount'],2); ?></td><?php
						?><td><?php echo date("M d, Y", $value['date_ts']); ?></td><?php
						echo "</tr>";
					}
					?></table><?php
				}
				?>
				</div>
			</div>
		  </td>
		</tr>		
		</table>
</tr>
<tr>
	<td colspan="2" class='center'>
		<table width='100%'>
		<tr>
		<td width='100%'>
			<?php
			if($web_user){
				if($revision){
					if(!$revision['approved']){
						
						?><input type="button" id='savebutton' value="Approve and Save Revision" onclick="saveCompany('approverev')" /><?php
					}
				}
				else if($contribution){
					if(!$contribution['approved']){
						?><input type="button" id='savebutton' value="Approve and Save Contribution" onclick="saveCompany('approvecontri')" /><?php
					}
				}
			}
			else{
				?><input type="button" id='savebutton' value="Save" onclick="saveCompany()" /><?php
			}
			?>
		</td>
		<?php 
		if($company['id']||($company&&$contribution&&$web_user)){
			if($web_user){
				if($revision){
					if(!$revision['approved']){
						?><td><input type="button" style='background:red; color:white' value="Reject Revision" onclick="rejectRevision('<?php echo $revision['id']; ?>')" /></td><?php
					}
				}
				else if($contribution){
					if(!$contribution['approved']){
						?><td><input type="button" style='background:red; color:white' value="Reject Contribution" onclick="rejectContribution('<?php echo $contribution['id']; ?>')" /></td><?php
					}
				}
			}
			else{
				?><td><input type="button" style='background:red; color:white' value="Delete" onclick="deleteCompany('<?php echo $company['id']; ?>')" /></td><?php
			}
		}
		?>
		</tr>
		</table>
	</td>
</tr>
</td>
</table>
<?php

if($company['id']||($company&&$contribution&&$web_user)){
	?>
	<script>
		<?php 
		
		if(is_array($company_fundings)){
			foreach($company_fundings as $cf){
				?>
				company = [];
				company_val = [];
				person = [];
				person_val = [];
				investment = [];
				investment_val = [];
				<?php
				foreach($cf['companies'] as $f_company){
					?>
					company.push("<?php echo sanitizeX($f_company['name']); ?>");
					company_val.push("<?php echo sanitizeX($f_company['id']); ?>");
					<?php
				}
				foreach($cf['people'] as $f_people){
					?>
					person.push("<?php echo sanitizeX($f_people['name']); ?>");
					person_val.push("<?php echo sanitizeX($f_people['id']); ?>");
					<?php
				}
				foreach($cf['investment_orgs'] as $f_investment_org){
					?>
					investment.push("<?php echo sanitizeX($f_investment_org['name']); ?>");
					investment_val.push("<?php echo sanitizeX($f_investment_org['id']); ?>");
					<?php
				}
				
				?>
				addFunding(
					"<?php echo sanitizeX($cf['round'])?>",
					"<?php echo sanitizeX($cf['currency'])?>",
					"<?php echo sanitizeX($cf['amount'])?>",
					"<?php echo sanitizeX($cf['date'])?>",
					company,
					company_val,
					person,
					person_val,
					investment,
					investment_val
				);
				<?php
			}
		}
		if(is_array($competitors)){
			foreach($competitors as $value){
				?>
				addCompetitor("<?php echo sanitizeX($value['label']); ?>", <?php echo $value['value']; ?>);
				<?php
			}

		}
		if(is_array($screenshots)){
			?>
			html = "";
			<?php
			foreach($screenshots as $value){
				?>
				filepath = "<?php echo sanitizeX($value['screenshot']); ?>";
				ss.push(filepath);
				file = "<?php echo sanitizeX(urldecode(basename($value['screenshot']))); ?>";
				title = "<?php echo sanitizeX($value['title']); ?>";
				html += "<div><a target='_blank' href='<?php echo site_url(); ?>media/image.php?p="+filepath+"'>"+file+"</a><br><input type='text' name='screenshot_titles[]' value='"+title+"' /><div class='hint'>Description</div><input type='hidden' name='screenshots[]' value='"+filepath+"' />&nbsp;&nbsp;&nbsp;<a onclick='this.parentElement.outerHTML=\"\"' style='cursor:pointer; text-decoration:underline' >Delete</a></div>";
				<?php
			}
			?>
			jQuery("#sspathhtml").html(html);
			<?php
		}
		if(is_array($people)){
			foreach($people as $value){
				?>
				addPerson(<?php echo $value['person_id']?>, "<?php echo $value['name']?>", "<?php echo $value['role']?>", "<?php echo $value['start_date']?>", "<?php echo $value['end_date']?>");
				<?php
			}
		}
		foreach($company as $key=>$value){
			if($key=='founded'&&0){
				?>
				thedate = new Date("<?php echo $value; ?>");
				thedate.setDate(thedate.getDate());
				jQuery("#founded_pick").val(dateFormat(thedate, "mmm dd, yyyy"));
				<?php
			}
			else if($key=="logo"&&trim($value)){
				?>
				jQuery('#logopath').val("<?php echo sanitizeX($value); ?>");
				jQuery("#logopathhtml").html("<img src='<?php echo site_url(); ?>media/image.php?p=<?php echo $value ?>&mx=220' />");
				<?php
			}
			else if($key=="active"){
				if($value=="1"){
					?>
					jQuery('[name="<?php echo $key; ?>"]').attr("checked", true);
					<?php
				}
				else{
					?>
					jQuery('[name="<?php echo $key; ?>"]').attr("checked", false);
					<?php
				}
			}
			else if(trim($value)||1){
				?>
				jQuery('[name="<?php echo $key; ?>"]').val("<?php echo sanitizeX($value); ?>");
				<?php
			}
		}
		?>	
	</script>
	<?php
}
?>
</form>
