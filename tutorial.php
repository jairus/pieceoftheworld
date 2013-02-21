<!doctype html>
<html lang="us">
<head>
<meta charset="utf-8">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.3.0/jquery.min.js" type="text/javascript"></script>
<style>
.relative{
	position:relative;
	width:704px;
	margin:auto;
}
.hidden{
	display:none;
	
}
.button{
	cursor:pointer;
	position:absolute;
	width:50px;
	height:50px;
	left:0px;
}
a:link, a:visited, a:hover{
	color:black;
}  
*{
	font-family:verdana;
	font-size:11px;
}
</style>
<script>
function show(n){
	jQuery(".step").hide();
	jQuery("#step"+n).show();
}
function onCheckboxClick() {
	if (document.getElementById('donotshowagain').checked === true) {
		setCookie("showTutorial","false",365)
	}
	else {
		setCookie("showTutorial","true",365)
	}
}

function getCookie(c_name) {
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++) {
		x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
		y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
		x=x.replace(/^\s+|\s+$/g,"");
		if (x==c_name) {
			if (y == null || y == "") {
				y = "";
			}
			return unescape(y);
		}
	}
	return "";
}

function setCookie(c_name,value,exdays) {
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=c_name + "=" + c_value;
}
</script>
</head>
<body>
	<table style="height:100%; width:100%;" height="100%"><tr>
		<td style="padding-top:40px; text-align:center">
			<div id='step1' class='step relative' ><div class='button' style='left:28px; top:29px' onclick='show(2);'></div><img src='tutorial/1.jpg' /></div>
			<div id='step2' class='step relative hidden'><div class='button' style='left:95px; top:29px' onclick='show(3);'></div><img src='tutorial/2.jpg' /></div>
			<div id='step3' class='step relative hidden'><div class='button' style='left:95px; top:29px' onclick='show(2);'></div><div class='button' style='left:155px; top:29px' onclick='show(4);'></div><img src='tutorial/3.jpg' /></div>
			<div id='step4' class='step relative hidden'><div class='button' style='left:95px; top:29px' onclick='show(3);'></div><div class='button' style='left:160px; top:29px' onclick='show(5);'></div><img src='tutorial/4.jpg' /></div>
			<div id='step5' class='step relative hidden'><div class='button' style='left:95px; top:29px' onclick='show(4);'></div><div class='button' style='left:160px; top:29px' onclick='self.location="index.php?skip=1"'></div><img src='tutorial/5.jpg' /></div>
		
		</td>
	</tr>
	</table>
	<center>
	<table>
	<tbody><tr><td><input type="checkbox" onclick="onCheckboxClick();" id="donotshowagain"></td><td style="font-size:12px;">Don't show this screen again</td></tr>
	<tr><td align="center" colspan="2"><a href='index.php?skip=1'>Skip Tutorial</a></td></tr>
	</tbody></table>
	</center>
</body>
</html>