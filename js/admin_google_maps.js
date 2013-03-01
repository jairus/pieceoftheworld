/*
In order to utilize this file, add the below given code in admin/phpgen_settings.php

function GetPagesHeader()
{
    return 
		'<script language="javascript" type="text/javascript">'.
		'$.getScript("../js/admin_google_maps.js", function() { 
			//alert("Script loaded and executed."); 
		});'.
		'</script>';
}
*/

function GetQueryStringValue (url, name) {       
	name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	var regexS = "[\\?&]" + name + "=([^&#]*)";
	var regex = new RegExp(regexS);
	var results = regex.exec(url);
	if (results == null)
		return undefined;       
	return results[1];      
}

function showMapPopup() {
	var answer = window.showModalDialog("../admin_google_map.php",0, "dialogWidth:700px; dialogHeight:500px; center:yes; resizable: no; status: no");
	document.getElementById('x_edit').value = answer.x;
	document.getElementById('y_edit').value = answer.y;
}

var url = window.location.href;
if (url.search("land.php") != -1 && GetQueryStringValue(window.location.href, "operation") == "insert") {
	var div = document.createElement("div");
	div.setAttribute('id','map_popup');
	div.setAttribute('style','text-align:center;');
	div.innerHTML = '<a href="#" onclick="javascript:void window.open(\'../admin_google_map.php\',\'1357950367934\',\'width=700,height=500,toolbar=0,menubar=0,location=0,status=0,scrollbars=0,resizable=0,menubar=0,left=0,top=0,modal=yes\');">Open map to select a land for X and Y value</a>';
	div.innerHTML = '<a href="#" onclick="showMapPopup();">Open map to select a land for X and Y value</a>';
	document.body.appendChild(div);
}

