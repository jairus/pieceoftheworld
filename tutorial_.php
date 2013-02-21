<?php session_start(); $_SESSION['showTutorial']=1; ?>
<html>
<head>
<title>Tutorial</title>
<script type="text/javascript">
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
<body style="padding:0px;font-family:arial;">
	<center>
	<img src="images/potw.jpg">
	<br>
	<table>
	<tr><td><input type="checkbox" id="donotshowagain" onclick="onCheckboxClick();" /></td><td style="font-size:12px;">Don&#39;t show this screen again</td></tr>
	<tr><td colspan="2" align="center"><input type="button" id="closewindow" value="Next" onclick="window.location='index.php';" /></td></tr>
	</table>	
	</center>
</body>
</html>