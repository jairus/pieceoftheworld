<?php
if(strtolower($_SERVER['HTTP_HOST'])=='www.pieceoftheworld.co'||strtolower($_SERVER['HTTP_HOST'])=='pieceoftheworld.co'){
	$url = "http://pieceoftheworld.com/".ltrim($_SERVER['REQUEST_URI'], "/");
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: '.$url);
}
if(strpos(strtolower($_SERVER['HTTP_HOST']), "www.")===0){
	$url = "http://pieceoftheworld.com/".ltrim($_SERVER['REQUEST_URI'], "/");
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: '.$url);
}


@session_start();
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">

	<title>Piece of the World Admin System</title>


	<script language="javascript" src="<?php echo site_url(); ?>media/js/jquery-1.7.2.min.js"></script>
	
	<link rel="stylesheet" href="<?php echo site_url(); ?>media/js/development-bundle/themes/base/jquery.ui.all.css">
	<script src="<?php echo site_url(); ?>media/js/development-bundle/jquery-1.8.0.js"></script>
	<script src="<?php echo site_url(); ?>media/js/development-bundle/ui/jquery.ui.core.js"></script>
	<script src="<?php echo site_url(); ?>media/js/development-bundle/ui/jquery.ui.widget.js"></script>
	<script src="<?php echo site_url(); ?>media/js/development-bundle/ui/jquery.ui.position.js"></script>
	<script src="<?php echo site_url(); ?>media/js/development-bundle/ui/jquery.ui.autocomplete.js"></script>
	
	<script src="<?php echo site_url(); ?>media/js/development-bundle/ui/jquery.ui.mouse.js"></script>
	<script src="<?php echo site_url(); ?>media/js/development-bundle/ui/jquery.ui.draggable.js"></script>
	<script src="<?php echo site_url(); ?>media/js/development-bundle/ui/jquery.ui.position.js"></script>
	<script src="<?php echo site_url(); ?>media/js/development-bundle/ui/jquery.ui.resizable.js"></script>
	<script src="<?php echo site_url(); ?>media/js/development-bundle/ui/jquery.ui.dialog.js"></script>
	<script src="<?php echo site_url(); ?>media/js/development-bundle/ui/jquery.ui.datepicker.js"></script>

	
	<script type="text/javascript" src="<?php echo site_url(); ?>media/js/jquery.alerts-1.1/jquery.alerts.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo site_url(); ?>media/js/jquery.alerts-1.1/jquery.alerts.css" media="screen" />

	<script type="text/javascript" src="<?php echo site_url(); ?>media/js/uploadify/swfobject.js"></script>
	<script type="text/javascript" src="<?php echo site_url(); ?>media/js/uploadify/jquery.uploadify.v2.1.4.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo site_url(); ?>media/js/uploadify/uploadify.css" media="screen" />
	
	<script>
	jQuery(function(){
		jQuery(".datepicker").datepicker({ 
		dateFormat: "mm/dd/yy",
		changeMonth: true, changeYear: true, yearRange: '1910:<?php echo date("Y"); ?>',
		onSelect: function(date) {
				/*
				thedate = new Date(date);
				thedate.setDate(thedate.getDate());
				jQuery(this).val(dateFormat(thedate, "mmm dd, yyyy"));
				id = jQuery(this).attr("alt");
				jQuery("#"+id).val(date);
				return false;
				*/
			},
		});
		
		jQuery( "#dialog" ).dialog({ autoOpen: false, closeOnEscape: true, title: "",
			open: function(){
				setTimeout(function(){
					jQuery(".ui-dialog").fadeOut(200, function(){
						jQuery("#dialog").dialog("close");
					});
				}, 2000);
			}
		});
	
	});
	function alertX(data){
		//jAlert(data);
		jQuery("#dialoghtml").html(data);
		jQuery("#dialog").dialog("open"); 
		
        
	}
	
	function confirmX(data){
		return jConfirm(data);
	}
	
	/****************** JS Date ***************************/
	var dateFormat = function () {
	var	token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
		timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
		timezoneClip = /[^-+\dA-Z]/g,
		pad = function (val, len) {
			val = String(val);
			len = len || 2;
			while (val.length < len) val = "0" + val;
			return val;
		};
	
	// Regexes and supporting functions are cached through closure
	return function (date, mask, utc) {
		var dF = dateFormat;
	
		// You can't provide utc if you skip other args (use the "UTC:" mask prefix)
		if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
			mask = date;
			date = undefined;
		}
	
		// Passing date through Date applies Date.parse, if necessary
		date = date ? new Date(date) : new Date;
		if (isNaN(date)) throw SyntaxError("invalid date");
	
		mask = String(dF.masks[mask] || mask || dF.masks["default"]);
	
		// Allow setting the utc argument via the mask
		if (mask.slice(0, 4) == "UTC:") {
			mask = mask.slice(4);
			utc = true;
		}
	
		var	_ = utc ? "getUTC" : "get",
			d = date[_ + "Date"](),
			D = date[_ + "Day"](),
			m = date[_ + "Month"](),
			y = date[_ + "FullYear"](),
			H = date[_ + "Hours"](),
			M = date[_ + "Minutes"](),
			s = date[_ + "Seconds"](),
			L = date[_ + "Milliseconds"](),
			o = utc ? 0 : date.getTimezoneOffset(),
			flags = {
				d:    d,
				dd:   pad(d),
				ddd:  dF.i18n.dayNames[D],
				dddd: dF.i18n.dayNames[D + 7],
				m:    m + 1,
				mm:   pad(m + 1),
				mmm:  dF.i18n.monthNames[m],
				mmmm: dF.i18n.monthNames[m + 12],
				yy:   String(y).slice(2),
				yyyy: y,
				h:    H % 12 || 12,
				hh:   pad(H % 12 || 12),
				H:    H,
				HH:   pad(H),
				M:    M,
				MM:   pad(M),
				s:    s,
				ss:   pad(s),
				l:    pad(L, 3),
				L:    pad(L > 99 ? Math.round(L / 10) : L),
				t:    H < 12 ? "a"  : "p",
				tt:   H < 12 ? "am" : "pm",
				T:    H < 12 ? "A"  : "P",
				TT:   H < 12 ? "AM" : "PM",
				Z:    utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
				o:    (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
				S:    ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
			};
	
		return mask.replace(token, function ($0) {
			return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
		});
	};
	}();
	
	
	/********************************** number formating *****************************/
	
	function addCommas(nStr){
		nStr += '';
		x = nStr.split('.');
		x1 = x[0];
		x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/;
		while (rgx.test(x1)) {
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
		return x1 + x2;
	}
	
	function fNum(num){
		num = uNum(num);
		num = num.toFixed(2);
		return addCommas(num);
	}
	function uNum(num){
		if(!num){
			num = 0;
		}
		else if(isNaN(num)){
			num = num.replace(/[^0-9\.]/g, "");
			if(isNaN(num)){
				num = 0;
			}
		}
		return num*1;
	}

	// Some common format strings
	dateFormat.masks = {
		"default":      "ddd mmm dd yyyy HH:MM:ss",
		shortDate:      "m/d/yy",
		mediumDate:     "mmm d, yyyy",
		longDate:       "mmmm d, yyyy",
		fullDate:       "dddd, mmmm d, yyyy",
		shortTime:      "h:MM TT",
		mediumTime:     "h:MM:ss TT",
		longTime:       "h:MM:ss TT Z",
		isoDate:        "yyyy-mm-dd",
		isoTime:        "HH:MM:ss",
		isoDateTime:    "yyyy-mm-dd'T'HH:MM:ss",
		isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
	};
	
	// Internationalization strings
	dateFormat.i18n = {
		dayNames: [
			"Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
			"Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
		],
		monthNames: [
			"Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
			"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
		]
	};
	
	// For convenience...
	Date.prototype.format = function (mask, utc) {
		return dateFormat(this, mask, utc);
	};
	
	function addDays(date, daystoadd){
		if(daystoadd==""){
			daystoadd = 0;
		}
		daystoadd = Math.ceil(daystoadd);
	
		if(date){
			date = date.split(",");
			date = date[0].split("/");
			date = date[1]+"/"+date[0]+"/"+date[2];
	
			try{
				thedate = new Date(date);
				thedate.setDate(thedate.getDate()+daystoadd);
				return dateFormat(thedate, "dd/mm/yyyy, dddd");
			}
			catch(e){
			}
			
		}
	}

	
	</script>
	<style type="text/css">

	::selection{ background-color: #21913E; color: white; }
	::moz-selection{ background-color: #21913E; color: white; }
	::webkit-selection{ background-color: #21913E; color: white; }

	body {
		background-color: #f0f0f0;
		margin: 30px;
		margin-top: 0px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: black;
	}
	td{
		vertical-align:top;
	}
	a {
		color: black;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body{
		margin: 0 15px 0 15px;
	}
	
	p.footer{
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 0px 0 0 0;
	}
	form{
		margin:0px;
	}
	#container{
		margin: 10px;
		border: 1px solid #D0D0D0;
		/*-webkit-box-shadow: 0 0 8px #D0D0D0;*/
		border: 1px solid #E5E5E5;
    	border-radius: 5px 5px 5px 5px;
    	box-shadow: 0 4px 18px #C8C8C8;
		background:white;
	}
	.list{
		margin: 10px;
		/*border: 1px solid #D0D0D0;*/
		/*-webkit-box-shadow: 0 0 8px #D0D0D0;*/
		/*border: 1px solid #E5E5E5;*/
    	/*border-radius: 5px 5px 5px 5px;*/
		background:white;
	}
	.list table{
		border-collapse:collapse;
		border-radius: 5px 5px 5px 5px;
		width:100%;
	}
	.list th{
		border: 1px solid #D0D0D0;
		padding:5px;
		color: white;
		background: #666666;
	}
	.list td{
		border: 1px solid #D0D0D0;
		padding:5px;
	}
	#header{
		background:#24416D;
		padding: 5px;
	}
	#header a{
		color:white;	
	}
	#menus{
		background:black;
		color:white;
		padding:0px;
		height: 34px;
	}
	#menus ul{
		margin:0px;
		padding:7px;
		
	}
	#menus li{
		margin:0px;
		list-style:none;
		display:inline;
		padding:10px;
		cursor:pointer;
	}
	#menus li:hover, #menus li.selected{
		background:white;
		color:black;
	}
	#dialoghtml{
		text-align:center;
		margin:auto;
	}
	
	#content{
		padding:10px;
	}
	*{
		font-family: Arial, Helvetica, sans-serif;
		font-size:12px;
	}
	.font14{
		font-size:14px;
	}
	.font16{
		font-size:16px;
	}
	.font18{
		font-size:18px;
	}
	.bold{
		font-weight:bold;
	}
	.red{
		color:red;
	}
	.right{
		text-align:right;
	}
	.left{
		text-align:left;
	}
	.center{
		text-align:center;
	}
	.pad10{
		padding: 10px;
	}
	.margin10{
		margin:10px;
	}
	
	/* add company */
	.hint{
		font-size:10px;
		font-style:italic;
		color:#666666;
		display:inline;
		padding: 0px 5px 0px 5px;
	}
	.more{
		font-size:10px;
		color:#666666;
		padding: 0px 5px 0px 5px;
	}
	
	input[type="password"], input[type="text"], textarea{
		border: 1px solid #aaaaaa;
    	border-radius: 3px 3px 3px 3px;
		padding:4px;
		width:220px;
	}
	input[name="name"]{
		width:350px;
	}
	input[type="submit"], input[type="button"]{
		min-width: 80px;
		cursor:pointer;	
		padding-top:10px;
		padding-bottom:10px;
	}
	
	input[type="button"].button{
		min-width: 50px;
		cursor:pointer;	
	}
	input[type="button"].normal{
		padding:2px;
	}
	
	#savebutton{
		width:100%;
		padding-top:10px;
		padding-bottom:10px;
	}
	
	textarea{
		width: 350px;
		height: 80px;
	}
	select[multiple="multiple"]{
		height: 150px;
		width: 250px;
		padding: 5px;
	}
	.even{
		
		background:#EEEEEE;
	}
	.odd{
		background:#FFFFFF;
	}
	.inline{
		display:inline;
	}
	.block{
		display:block;
	}
	.row:hover{
		background:#FFDB96;
	}
	.inline{
		display:inline;
	}
	.border{
		border: 1px solid #CCCCCC;
		border-radius: 3px 3px 3px 3px;
	}
	.hidden{
		display:none;
	}
	.cursor{
		cursor:pointer;
	}
	
	#peoplehtml table, 
	#companyhtml table, 
	#investment_orghtml table, 
	#competitors_html table,
	#milestoneshtml table
	{
		border-collapse:collapse;
	}
	#peoplehtml table td, 
	#companyhtml table td, 
	#investment_orghtml table td, 
	#competitors_html table td,
	#milestoneshtml table td
	{
		padding: 2px 5px 2px 5px;
		border: 1px solid #AAAAAA;
	}
	
	
	
	#fundinghtml{
		width:100%;
	}
	#fundinghtml .label{
		background:#505050;
		width:15%;
		color:#FFFFFF;
		font-size:11px;
	}
	
	#fundinghtml .label_ipc{
		background:#CCCCCC;
	}
	
	#fundinghtml .value0{
		font-size:11px;
	}
	#fundinghtml .value1{
		width:20%;
		font-size:11px;
	}
	#fundinghtml .value2{
		width:50%;
		
	}
	
	.fundingtable{
		width:100%;
		border:1px solid #CCCCCC;
		margin-bottom:10px;
	}
	.fundingtable td{
		padding:4px;
	}
	
	.underline{
		text-decoration:underline;
	}
	
	.milestone{
		padding:5px;
		margin:5px;
		background:white;
		font-size:11px;
	}
	
	.lightgreen{
		background: #E4FFE4;
	}
	.changed{
		background: #50FA50;
	}
	
	.additem{
		font-style:italic;
		font-weight:bold;
	}
	
	.f_check{
		position:relative;
	}
	.f_check img{
		position:absolute;
		top:-3px;
	}
	.f_delete{
		padding-left:18px;
	}
	#ipc{
		margin-top:10px;
	}
	
	
	/**********BLOGS RSS**********/
	.new{
		/*background:green;*/
	}
	.submenus{
		color:white;
		padding:0px;
		height: 34px;
	}
	.submenus ul{
		margin-top:10px;
		padding:0px;
	}
	.submenus li{
		margin:0px;
		list-style:none;
		display:inline;
		cursor:pointer;
		background:black;
		padding:10px;
	}
	.submenus li:hover, .submenus li.selected{
		background:orange;
	}
	ul.items{
		margin-top:0px;
	}
	
	/**********LATEST UPDATES**********/
	.logs_container{
		padding:20px;
	}
	.log_date{
		font-size:14px;
		font-weight:bold;
		background:orange;
		padding:10px;
		color:white;
	}
	.logs{
		padding:10px;
	}
	.log_time{
		font-size:14px;
		padding-left:40px;
		padding-top:5px;
		color:green;
	}
	.log, .log a, .log b{
		font-size:14px;
		padding-top:5px;
	}
	.ui-dialog .ui-widget-header{
	background: white;
	border:0px;
	}
	.ui-dialog {
		border: 2px solid gray;
		color: #222222;
	}
	#dialoghtml{
		text-align:center;
	}
	
	/** alerts **/
	#revcount{
		background:red;
		color:white;
		z-index:1000;
		position:absolute;
		border-radius: 10px;
		height:20px;
		width:20px;
		font-size:9px;
		text-align:center;
		top: -10px;
		left: 110px;
	}
	#concount{
		background:red;
		color:white;
		z-index:1000;
		position:absolute;
		border-radius: 10px;
		height:20px;
		width:20px;
		font-size:9px;
		text-align:center;
		top: -10px;
		left: 120px;
	}
	</style>
</head>
<body>
<div id='imagepreload' class='hidden'>
	<img src='<?php echo site_url(); ?>media/check.png' />
	<img src='<?php echo site_url(); ?>media/x.png' />
	<img src='<?php echo site_url(); ?>media/new.png' />
	<img src='<?php echo site_url(); ?>media/ajax-loader.gif' />
</div>
<div id="dialog" title="">
    <div id='dialoghtml'></div>
</div>

<div id="container">
	<table cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td id='header'>
				<table cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td><img onclick='self.location="<?php echo site_url(); ?>main"' src="<?php echo site_url()."media/pieceoft/logo.png"; ?>"  style='cursor:pointer'/></td>
						<?php
						if($user){ //if logged in
							?>
							<td class='right'><a href='<?php echo site_url(); ?>main/logout'>Log Out</a></td>
							<?php
						}
						?>
					</tr>
				</table>	
		</tr>
		<tr>
			<td id='menus'>
				<?php
				if($user){ //if logged in
					?>
					
							<?php
							$this->load->view("layout/menus");
							?>
						
					<?php
				}
				?>
			</td>
		</tr>
		<tr>
			<td id='content'>
				<?php
				if($content&&$user){
					echo $content;
				}
				else if(!$user){ //if not logged in
					$this->load->view("layout/login");
				}
				else{
					ob_end_clean();
					?>
					<script>
						self.location = "<?php echo site_url(); ?>latest";
					</script>
					<?php
					exit();
				}
				?>
			</td>
		</tr>
	</table>
	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
</div>

</body>
</html>