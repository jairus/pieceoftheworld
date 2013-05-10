<?php 
error_reporting(E_ALL^E_NOTICE);
session_start(); 
if($_GET['coords']){
	$_SESSION["coords"] = $_POST['data'];
	$_SESSION["buydetails"] = $_POST['buydetails'];
	print_r($_SESSION["coords"]);
	exit();
}
require_once 'ajax/global.php';
if($_GET['affid']){
	$_SESSION['affid'] = $_GET['affid'];
	$sql = "select * from `affiliates` where `id`='".$_SESSION['affid']."'  and `active`=1";
	$r = dbQuery($sql, $_dblink);
	$r = $r[0];
	if($r['id']){
		$sql = "insert into `affiliate_clicks` set 
			`affiliate_id`='".$r['id']."',
			`server_json`='".mysql_real_escape_string(json_encode($_SERVER))."',
			`dateadded`=NOW()
		";
		dbQuery($sql, $_dblink);
	}
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: index.php');
	exit();
}

if($_GET['px']!=""){
	$_SESSION['px'] = $_GET['px'];
	exit();
}

?>
<!doctype html>
<html lang="us">
<head>

<meta property='og:image' content='http://www.pieceoftheworld.co/images/pastedgraphic_fb.jpg' />
<meta property='og:title' content='Piece of the World' />

<meta charset="utf-8">
<title>PieceoftheWorld</title>

<link href="twitmarquee/front.css" media="screen" rel="stylesheet" type="text/css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.3.0/jquery.min.js" type="text/javascript"></script>


<link href="css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="js/jquery-1.8.3.min.js" type="text/javascript"></script>
<script src="js/jquery-ui-1.9.2.custom.min.js" type="text/javascript"></script>
<script src="js/jquery.jcarousel.min.js" type="text/javascript"></script>
<link href="css/jquery.reveal.css" rel="stylesheet">
<script src="js/jquery.reveal.js" type="text/javascript"></script>
<script src="http://maps.google.com/maps/api/js?sensor=true&libraries=geometry" type="text/javascript"></script>
<link href="css/main.css" rel="stylesheet">

<script type="text/javascript">
<?php
	require_once 'ajax/global.php';
	$masterUser = GetMasterUser();
	$sql = "SELECT * FROM settings WHERE 1";
	$rows = dbQuery($sql, $_dblink);
	$i=0; 
	while ($rows[$i]) {
		$row = $rows[$i];
		echo 'var '.$row["name"].' = "'.$row["value"].'";';
		$i++;
	}
	echo "var masterUser = '".$masterUser."';";
?>
<?php 
	if(!trim($_GET['latlong'])&&!trim($_GET['xy'])){
		if (!isset($_GET['skip'])&&(isset($_SESSION['showTutorial']) == false || $_SESSION['showTutorial'] != 1)) { ?>
			var showTutorial = getCookie("showTutorial");
			if (showTutorial !== "false") {
				window.location="tutorial.php";
				//window.showModalDialog("tutorial.html",0, "dialogWidth:700px; dialogHeight:500px; center:yes; resizable: no; status: no");
			}
			<?php 
		}
	}
	?>
	var enableGeoLoc = false;
	var geoLoc;

	if (enableGeoLoc != false) {
		// Try HTML5 geolocation
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
				geoLoc = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
			});
		}
	}

	var TILE_SIZE = 629961;
	var SAVED_POSITION = new google.maps.LatLng(51.5, 0.1); // new google.maps.LatLng(37.09024, -95.71289); //new google.maps.LatLng(31.41576, 74.26804);

	function bound(value, opt_min, opt_max) {
		if (opt_min != null) value = Math.max(value, opt_min);
		if (opt_max != null) value = Math.min(value, opt_max);
		return value;
	}

	function degreesToRadians(deg) {
		return deg * (Math.PI / 180);
	}

	function radiansToDegrees(rad) {
		return rad / (Math.PI / 180);
	}

	function MercatorProjection() {
		this.pixelOrigin_ = new google.maps.Point(TILE_SIZE / 2, TILE_SIZE / 2);
		this.pixelsPerLonDegree_ = TILE_SIZE / 360;
		this.pixelsPerLonRadian_ = TILE_SIZE / (2 * Math.PI);
	}
	
	MercatorProjection.prototype.fromLatLngToPoint = function(latLng, opt_point) {
		var me = this;
		var point = opt_point || new google.maps.Point(0, 0);
		var origin = me.pixelOrigin_;

		point.x = origin.x + latLng.lng() * me.pixelsPerLonDegree_;

		// NOTE(appleton): Truncating to 0.9999 effectively limits latitude to
		// 89.189.  This is about a third of a tile past the edge of the world
		// tile.
		var siny = bound(Math.sin(degreesToRadians(latLng.lat())), -0.9999, 0.9999);
		point.y = origin.y + 0.5 * Math.log((1 + siny) / (1 - siny)) * -me.pixelsPerLonRadian_;
		return point;
	};

	MercatorProjection.prototype.fromPointToLatLng = function(point) {
		var me = this;
		var origin = me.pixelOrigin_;
		var lng = (point.x - origin.x) / me.pixelsPerLonDegree_;
		var latRadians = (point.y - origin.y) / -me.pixelsPerLonRadian_;
		var lat = radiansToDegrees(2 * Math.atan(Math.exp(latRadians)) - Math.PI / 2);
		return new google.maps.LatLng(lat, lng);
	};

	function CoordMapType(tileSize) {
		this.tileSize = tileSize;
	}

	CoordMapType.prototype.getTile = function(coord, zoom, ownerDocument) {
		var div = ownerDocument.createElement('div');
		div.innerHTML = coord;
		div.style.width = this.tileSize.width + 'px';
		div.style.height = this.tileSize.height + 'px';
		div.style.fontSize = '10';
		div.style.borderStyle = 'solid';
		div.style.borderWidth = '1px';
		div.style.borderColor = '#AAAAAA';
		return div;
	};

	var ZOOM_LEVEL_WORLD = 2;
	var ZOOM_LEVEL_REGION = 5;
	var ZOOM_LEVEL_CITY = 16;
	
	var rectangles = [];
	var rectanglesxy = [];
	var markers = [];
	var markers_loaded = false;
	var map = null;
	var geocoder;
	var searchMarker;
	var draggableRect = null;
	var blocksAvailableInDraggableRect = "";
	var lastEvent;

	function initialize(zoomVal, mapTypeIdVal) {
		//updatePopupWindowTabNews();
		updatePopupWindowTabConfig(false);

		if (map) {
			SAVED_POSITION = map.getCenter();
		}
		//ZOOM_LEVEL_WORLD
		//zoomVal = typeof zoomVal !== 'undefined' ? zoomVal : ZOOM_LEVEL_WORLD;
		zoomVal = 3;
		mapTypeIdVal = typeof mapTypeIdVal !== 'undefined' ? mapTypeIdVal : google.maps.MapTypeId.TERRAIN;
		var mapOptions = {
			zoom: zoomVal,
			mapTypeId: mapTypeIdVal  // ROADMAP, SATELLITE, HYBRID, TERRAIN
		};
		map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
		geocoder = new google.maps.Geocoder();
		searchMarker = new google.maps.Marker({
			map: map,
			draggable: true
		});
		if (enableGeoLoc == true && window.geoLoc) {
			enableGeoLoc = false;
			var geoLocMarker = new google.maps.Marker({
				position: window.geoLoc,
				map: map,
				icon: 'images/loc.png'
			});
			map.setCenter(window.geoLoc);
		}
		else {
			map.setCenter(SAVED_POSITION);
		}
		
		//add click event
		google.maps.event.addListener(map, 'click', function(event) {
			putBox(event);
		});


		// Add markers
		//markers = ajaxAddMarkers(map);
		
		self.setInterval(function(){ajaxAddMarkers(map, true);}, 5*60*1000);
		
		google.maps.event.addListener(map, 'click', function(event) { onClick(event); });
		google.maps.event.addListener(map, 'idle', function(event) { onIdle(false); });
		//google.maps.event.addListener(map, 'zoom_changed', function() { onZoomChanged(); });
		
		
		
		<?php
		if(trim($_GET['latlong'])){
			list($lat, $long) = explode("~", trim($_GET['latlong']));
			?>
			inLatLng = new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $long; ?>);
			updatePopupWindowTabInfo(inLatLng);
			var loc = new google.maps.LatLng(inLatLng.lat(),inLatLng.lng());
			//consoleX(inLatLng.lat()+" - "+inLatLng.lng());
			map.setZoom(17);
			map.setCenter(loc);
			jQuery("#clicktozoom").hide();
			<?php
		}
		else if(trim($_GET['xy'])){
			list($x, $y) = explode("~", trim($_GET['xy']));
			$x += 0;
			$y += 0;
			?>
			var projection = new MercatorProjection();
			var point = {};
			point.x = <?php echo $x;?>;
			point.y = <?php echo $y;?>;
			var inLatLng = projection.fromPointToLatLng(point);
			var loc = new google.maps.LatLng(inLatLng.lat(),inLatLng.lng());
			//consoleX(inLatLng.lat()+" - "+inLatLng.lng());
			map.setZoom(17);
			map.setCenter(loc);
			jQuery("#clicktozoom").hide();
			<?php
		}
		?>
		
		
		
	}

	google.maps.event.addDomListener(window, 'load', showWorldView);

	$(function() {
		document.getElementById('config_email').value = getCookie('user_email');
		/*
		var user_email = getCookie("user_email");
		if (user_email == "false" || user_email == "" || user_email == null) {
			user_email = "";
			var user_email = window.prompt("Enter your registered email address?", user_email);
			if (user_email != "") {
				setCookie("user_email", user_email, 365);
			}
		}
		*/
		$('.cpanelwnd').draggable();
		xleft = jQuery(window).width() - $('.cpanelwnd').width();
		$('.cpanelwnd').css({left: xleft, top: '60px'});
		$("#radioset").buttonset();
		$("#tabs").tabs();
		$("#search_enteraplace").autocomplete({
			// this bit uses the geocoder to fetch address values
			source: function(request, response) {
				geocoder.geocode( {'address': request.term }, function(results, status) {
					response($.map(results, function(item) {
						return {
							label:  item.formatted_address,
							value: item.formatted_address,
							latitude: item.geometry.location.lat(),
							longitude: item.geometry.location.lng()
						}
					}));
				})
			},
			// this bit is executed upon selection of an address
			select: function(event, ui) {
				$("#latitude").val(ui.item.latitude);
				$("#longitude").val(ui.item.longitude);
				var location = new google.maps.LatLng(ui.item.latitude, ui.item.longitude);
				//searchMarker.setPosition(location);
				map.setZoom(15);
				map.setCenter(location);
			}
		});
		
		
	});
	
	function showWorldView(object) {
		if (map) {
			map.setZoom(ZOOM_LEVEL_WORLD);
			map.setMapTypeId(google.maps.MapTypeId.HYBRID);
		}
		else {
			initialize(ZOOM_LEVEL_WORLD, google.maps.MapTypeId.HYBRID);
		}
		//document.getElementById('buy-button').disabled = true;
	}

	function showRegionalView(object) {
		map.setZoom(ZOOM_LEVEL_REGION);
		map.setMapTypeId(google.maps.MapTypeId.HYBRID);
		//document.getElementById('buy-button').disabled = true;
	}

	function showCityView(object) {
		map.setZoom(ZOOM_LEVEL_CITY);
		map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
	}
	
	function onClick(event) {
		showPopupWindowTabInfo(false);
		//updatePopupWindowTabInfo(event.latLng);
	}
	
	function setSessionPrice(p, sync){
		if(sync){
			jQuery.ajax({
				dataType: "html",
				async: false,
				url: "?px="+p,
				success: function(data){
				}
			});

		}
		else{
			jQuery.ajax({
				dataType: "html",
				//async: false,
				url: "?px="+p,
				success: function(data){
				}
			});
		}
	}
	
	function checkWater(LatLng){
		url = "/checkwater.php/?latlong="+LatLng;
		var areatype = "";
		jQuery.ajax({
			dataType: "html",
			url: url,
			success: function(data){
				areatype = data;
				if(areatype=="water"){
					amount = numblocks * 0.90;
				}
				else{
					amount = numblocks * 10.90;
				}
				amount = amount.toFixed(2);
				jQuery("#theprice").html(amount);
				setSessionPrice(amount);
			}
		});
		
		//return areatype;
	}
	
	function setCity(LatLng, box, numblocks){
		url = "http://maps.googleapis.com/maps/api/geocode/json?latlng="+LatLng+"&sensor=true";
		jQuery("#info-city").html("");
		jQuery.ajax({
			dataType: "json",
			url: url,
			success: function(data){
				try{
					ixt = data['results'].length;
					for(ix=0; ix<ixt; ix++){
						length = data['results'][ix]['address_components'].length;
						
						//alert(length);
						//search for locality
						for(i=0; i<length; i++){
							type = data['results'][ix]['address_components'][i]['types'][0];
							if(type=='locality'){
								city = data['results'][ix]['address_components'][i].long_name;
								break; 
							}
							else if(type.indexOf("administrative_area_level_1")==0){
								city = data['results'][ix]['address_components'][i].long_name;
								break;
							}
							else if(type.indexOf("country")==0){
								city = data['results'][ix]['address_components'][i].long_name;
								break;
							}
						}
						jQuery("#info-city").html(city+": ");
					}					
					jQuery("#dcity").html("");
					jQuery("#dregion").html("");
					jQuery("#dcountry").html("");
					city = "";
					region = "";
					country = "";
					if(box){
						for(ix=0; ix<ixt; ix++){
							length = data['results'][ix]['address_components'].length;
							for(i=0; i<length; i++){
								type = data['results'][ix]['address_components'][i]['types'][0];
								if(type=='locality'&&!city){
									city = data['results'][ix]['address_components'][i].long_name;
									jQuery("#dcity").html("City: "+city);
								}
								else if(type.indexOf("administrative_area_level_1")==0&&!region){
									region = data['results'][ix]['address_components'][i].long_name;
									jQuery("#dregion").html("Region: "+region);
								}
								else if(type.indexOf("country")==0&&!country){
									country = data['results'][ix]['address_components'][i].long_name;
									jQuery("#dcountry").html("Country: "+country);
								}
							}
						}
						if(city){
							price = 9.90;
						}
						else if(region||country){
							price = 5.90;
						}
						else{
							price = 0.90;
						}
						if(numblocks){
							amount = numblocks * price;
							amount = amount.toFixed(2);
							jQuery("#theprice").html(amount);
							setSessionPrice(amount);
						}
					}
					//consoleX(city); //address_components[0].long_name+"<---");
					//consoleX(data.results.address_components[0].types[0]+"<---");
					
				}
				catch(e){
					jQuery("#dcity").html("");
					jQuery("#dregion").html("");
					jQuery("#dcountry").html("");
					if(numblocks){
						checkWater(LatLng);
					}
				}
				
			}
		});
		
		
	}
	function onMarkerClick(event, type) {
		//onColoredRectangleClick(event);
		$("#tabs").tabs("select",0);//$("#tabs").tabs("select",1);
		if (map.getZoom() >= ZOOM_LEVEL_CITY) {
			//document.getElementById('buy-button').disabled = false;
		}
		
		//jairus
		if(map.getZoom()<17){
			jQuery("#clicktozoom").show();
		}
		jQuery("#clicktozoom").click(function () {
			var loc = new google.maps.LatLng(event.latLng.lat(),event.latLng.lng());
			//consoleX(event.latLng.lat()+" - "+event.latLng.lng());
			map.setZoom(17);
			map.setCenter(loc);
			jQuery("#clicktozoom").hide();
			//onColoredRectangleClick(event);
			//onRectangleClick(event) ;
			//scheduleDelayedCallback();
			
		});
		
		
		//jQuery("#fbsharelink").attr("href", "http://<?php echo $_SERVER['HTTP_HOST']; ?>/?latlong="+event.latLng.lat()+"-"+event.latLng.lng())
		
		
		
		
		//set draggable rect
		var projection = new MercatorProjection();
		var worldCoordinate = projection.fromLatLngToPoint(event.latLng);
		worldCoordinate.x = Math.floor(worldCoordinate.x);
		worldCoordinate.y = Math.floor(worldCoordinate.y);
		var block = getBlockLTRB(worldCoordinate);
		var bounds = new google.maps.LatLngBounds(
			new google.maps.LatLng(block[0].lat(),block[0].lng()),
			new google.maps.LatLng(block[1].lat(),block[1].lng())
		);

		var LtLgNE = bounds.getNorthEast();
		var LtLgSW = bounds.getSouthWest();

		var projection = new MercatorProjection();
		
		
		
		//consoleX("Lat: "+LtLgNE.lat());
		//consoleX("Lng: "+LtLgNE.lng());
		strlatlong = LtLgNE.lat()+","+LtLgNE.lng();
		
		
		
		var WcNE = projection.fromLatLngToPoint(LtLgNE);
		var WcSW = projection.fromLatLngToPoint(LtLgSW);

		if (Math.abs(Math.floor(WcNE.y) - WcNE.y) >= 0.5) {
			WcNE.y = Math.floor(WcNE.y) + 1;
		}
		else {
			WcNE.y = Math.floor(WcNE.y);
		}

		if (Math.abs(Math.floor(WcNE.x) - WcNE.x) >= 0.5) {
			WcNE.x = Math.floor(WcNE.x);
		}
		else {
			WcNE.x = Math.floor(WcNE.x) - 1;
		}
		
		if (Math.abs(Math.floor(WcSW.y) - WcSW.y) >= 0.5) {
			WcSW.y = Math.floor(WcSW.y);
		}
		else {
			WcSW.y = Math.floor(WcSW.y) - 1;
		}

		if (Math.abs(Math.floor(WcSW.x) - WcSW.x) >= 0.5) {
			WcSW.x = Math.floor(WcSW.x) + 1;
		}
		else {
			WcSW.x = Math.floor(WcSW.x);
		}
		
		// Because we need TopLeft points to set new rectangle so lets move NE block 1 step towards E and SW 1 step towards S
		WcNE.x += 1;
		WcSW.y += 1;
		
		updatePopupWindowTabInfo(event.latLng, strlatlong);
		
		//consoleX(type);
		if(type=='special'){		
			// Acquired Special Area or Special Area
			$.ajax({
				url:'ajax/get_minmaxareacoordinates.php?x='+WcNE.x+'&y='+WcSW.y+"&_=<?php echo time(); ?>",
				dataType:'html',
				async:false,
				success:function(data, textStatus, jqXHR){
					if (data != null) {
						var resultJSON = JSON.parse(data);
						WcNE.x = resultJSON['maxX'];
						WcNE.y = resultJSON['minY']-1;
						WcSW.x = resultJSON['minX']-1;
						WcSW.y = resultJSON['maxY'];
					}
				}
			});
			var BlSW = getBlockLTRB(WcSW);
			var BlNE = getBlockLTRB(WcNE);
			if (BlSW[2].y < BlNE[2].y) {
				blocksAvailableInDraggableRect = BlSW[2].x+"-"+BlNE[2].y-1;
			}
			else {
				blocksAvailableInDraggableRect = BlSW[2].x+"-"+BlNE[2].y+"_"+(BlNE[2].x-1)+"-"+(BlSW[2].y-1);
			}
			if(document.getElementById('buy-button').value=='Buy'){
				//price = 499;
				//document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $'+price;
			}
		}
		else{
			blocksAvailableInDraggableRect = WcNE.x+'-'+WcSW.y;
			/*
			var BlSW = getBlockLTRB(WcSW);
			var BlNE = getBlockLTRB(WcNE);
			if (BlSW[2].y < BlNE[2].y) {
				blocksAvailableInDraggableRect = BlSW[2].x+"-"+BlNE[2].y-1;
			}
			else {
				blocksAvailableInDraggableRect = BlSW[2].x+"-"+BlNE[2].y+"_"+(BlNE[2].x-1)+"-"+(BlSW[2].y-1);
			}
			*/
		}

		
		
		
		//onDraggableRectangleChanged(bounds);
		
		
		
		//window.searchMarker.setPosition(location);
		//window.map.setZoom(ZOOM_LEVEL_CITY);
		//window.map.setCenter(location);
		
		//scheduleDelayedCallback();
		//document.getElementById('buy-button').disabled = false;
		
	}
	
	var numblocks = 0;
	
	function onDraggableRectangleChanged(bounds) {
		consoleX("onDraggableRectangleChanged");
		//alert(blocksAvailableInDraggableRect);
		if (bounds||(lastEvent.getTime() + 250 <= new Date().getTime())) {
			//consoleX(bounds);
			if(!bounds){
				var bounds = draggableRect.getBounds();
			}
			
			var LtLgNE = bounds.getNorthEast();
			var LtLgSW = bounds.getSouthWest();
			
			
			var LtLgNE = bounds.getNorthEast();
			//consoleX(LtLgNE);
			
			var strlatlong = LtLgNE.lat()+","+LtLgNE.lng();
		
			
			var projection = new MercatorProjection();
			
		
			var WcNE = projection.fromLatLngToPoint(LtLgNE);
			var WcSW = projection.fromLatLngToPoint(LtLgSW);
			
			

			if (Math.abs(Math.floor(WcNE.y) - WcNE.y) >= 0.5) {
				WcNE.y = Math.floor(WcNE.y) + 1;
			}
			else {
				WcNE.y = Math.floor(WcNE.y);
			}

			if (Math.abs(Math.floor(WcNE.x) - WcNE.x) >= 0.5) {
				WcNE.x = Math.floor(WcNE.x);
			}
			else {
				WcNE.x = Math.floor(WcNE.x) - 1;
			}
			
			if (Math.abs(Math.floor(WcSW.y) - WcSW.y) >= 0.5) {
				WcSW.y = Math.floor(WcSW.y);
			}
			else {
				WcSW.y = Math.floor(WcSW.y) - 1;
			}

			if (Math.abs(Math.floor(WcSW.x) - WcSW.x) >= 0.5) {
				WcSW.x = Math.floor(WcSW.x) + 1;
			}
			else {
				WcSW.x = Math.floor(WcSW.x);
			}
			
			// Because we need TopLeft points to set new rectangle so lets move NE block 1 step towards E and SW 1 step towards S
			WcNE.x += 1;
			WcSW.y += 1;
			
			//var WcNELTRB = getBlockLTRB(WcNE);
			//drawRect(WcNELTRB[0], WcNELTRB[1], "#555555", 1);
			//var WcSWLTRB = getBlockLTRB(WcSW);
			//drawRect(WcSWLTRB[0], WcSWLTRB[1], "#555555", 1);

			//////////////////////////////////////////////////////////////////////////////////////////
			// Updated [02/01/2013] in order to select all blocks based on the Top Left selected block
			// This is how the selection will be made:
			// IF TopLeft block is EMPTY THEN make selection of only empty blocks
			// IF TopLeft block is a plot THEN make selection of only one plot
			// IF TopLeft block is a special area THEN make selection of only that special area
			//////////////////////////////////////////////////////////////////////////////////////////
			var currRow = -1;
			var matrix = [];
			var row = null;
			var currentPlotType = 0;
			alert(rectangles.length);
			for (var i = 0; i < rectangles.length; i++) {
				var plot = getBlockInfo(rectangles[i].getBounds().getCenter());
				//alert(' ( ' + plot[2].x + ' > ' + WcSW.x + ' && ' + plot[2].x + ' <= ' + WcNE.x + ' ) ' + ' ( ' + plot[2].y + ' > ' + WcNE.y + ' && ' + plot[2].y + ' <= ' + WcSW.y + ' ) ');
				if ((plot[2].x > WcSW.x && plot[2].x <= WcNE.x) && (plot[2].y > WcNE.y && plot[2].y <= WcSW.y)) {
					if (rectangles[i].fillOpacity === 0) {
						currentPlotType = 1;
					}
					else if (rectangles[i].fillColor == fillColorAcquiredPlot) {
						currentPlotType = 2;
					}
					else if (rectangles[i].fillColor == fillColorAcquiredSpecialArea) {
						currentPlotType = 3;
					}
					else if (rectangles[i].fillColor == fillColorSpecialArea) {
						currentPlotType = 4;
					}
					else {
						// Do Nothing
						currentPlotType = 5;
					}
					if (currRow != plot[2].y) {
						currRow = plot[2].y;
						if (row != null) {
							matrix.push(row);
						}
						row = new Array();
					}
					row.push(new Array(plot[2].x, plot[2].y, currentPlotType));
				}
			}
			
			//alert(row.length);
			if (row != null) {
				matrix.push(row);
			}
			else {
				// There is only one block selected
				for (var i = 0; i < rectangles.length; i++) {
					var plot = getBlockInfo(rectangles[i].getBounds().getCenter());
					//alert(' ( ' + plot[2].x + ' > ' + WcSW.x + ' && ' + plot[2].x + ' <= ' + WcNE.x + ' ) ' + ' ( ' + plot[2].y + ' > ' + WcNE.y + ' && ' + plot[2].y + ' <= ' + WcSW.y + ' ) ');
					if (plot[2].x == WcNE.x && plot[2].y == WcNE.y) {
						if (rectangles[i].fillOpacity === 0) {
							currentPlotType = 1;
						}
						else if (rectangles[i].fillColor == fillColorAcquiredPlot) {
							currentPlotType = 2;
						}
						else if (rectangles[i].fillColor == fillColorAcquiredSpecialArea) {
							currentPlotType = 3;
						}
						else if (rectangles[i].fillColor == fillColorSpecialArea) {
							currentPlotType = 4;
						}
						else {
							// Do Nothing
							currentPlotType = 5;
						}
						row = new Array();
						row.push(new Array(plot[2].x, plot[2].y, currentPlotType));
						matrix.push(row);
						break;
					}
				}
			}
			
			//alert(matrix.length);
			
			var typeFirstBlock = -1;
			for (var i = 0; i < matrix.length; i++) {
				for (var j = 0; j < matrix[i].length; j++) {
					//matrix[i][j][0];	// x
					//matrix[i][j][1];	// y
					//matrix[i][j][2];	// Plot Type
					if (typeFirstBlock == -1) { 
						typeFirstBlock = matrix[i][j][2]; 
						break;
					}
				}
				break;
			}
			
			if (typeFirstBlock == 1) {
				document.getElementById('buy-button').value = 'Buy';
				document.getElementById('buy-button').disabled = false;
				// Empty Plot
				var maxX = 0;
				var maxY = 0;
				var columns = matrix[0].length;
				var maxColumn = columns;
				var rows = matrix.length;
				for (var i = 0; i < rows; i++) {
					for (var j = 0; j < columns; j++) {
						if (typeFirstBlock != matrix[i][j][2]) {
							if (maxX == 0) {
								maxX = matrix[i][j][0];
								maxColumn = j;
							}
							else {
								if (maxX > matrix[i][j][0]) {
									maxX = matrix[i][j][0];
									maxColumn = j;
								}
							}
						}
					}
					break; // Only check first row
				}
				for (var l = 0; l < rows; l++) {
					for (var m = 0; m < maxColumn; m++) {
						if (typeFirstBlock != matrix[l][m][2]) {
							if (maxY == 0) {
								maxY = matrix[l][m][1];
							}
							else {
								if (maxY > matrix[l][m][1]) {
									maxY = matrix[l][m][1];
								}
							}
						}
					}
				}
				WcNE.x = (maxX == 0) ? matrix[0][columns-1][0] : maxX-1;
				WcNE.y = matrix[0][0][1]-1;
				WcSW.x = matrix[0][0][0]-1;
				WcSW.y = (maxY == 0) ? matrix[rows-1][0][1] : maxY-1;
			}
			else if (typeFirstBlock == 2) {
				document.getElementById('buy-button').value = 'Bid';
				document.getElementById('buy-button').disabled = false;
				// Acquired Plot
				WcNE.x = matrix[0][0][0];
				WcNE.y = matrix[0][0][1]-1;
				WcSW.x = matrix[0][0][0]-1;
				WcSW.y = matrix[0][0][1];
			}
			else if (typeFirstBlock == 3 || typeFirstBlock == 4) {
				if (typeFirstBlock == 3) {
					document.getElementById('buy-button').value = 'Bid';
					document.getElementById('buy-button').disabled = false;
				}
				else {
					document.getElementById('buy-button').value = 'Buy';
					document.getElementById('buy-button').disabled = false;
					//price = 499;
					//document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $'+price;
				}
				// Acquired Special Area or Special Area
				$.ajax({
					url:'ajax/get_minmaxareacoordinates.php?x='+matrix[0][0][0]+'&y='+matrix[0][0][1]+"&_=<?php echo time(); ?>",
					dataType:'html',
					async:false,
					success:function(data, textStatus, jqXHR){
						if (data != null) {
							var resultJSON = JSON.parse(data);
							WcNE.x = resultJSON['maxX'];
							WcNE.y = resultJSON['minY']-1;
							WcSW.x = resultJSON['minX']-1;
							WcSW.y = resultJSON['maxY'];
						}
					}
				});
			}
			else {
				document.getElementById('buy-button').value = 'Buy';
				//document.getElementById('buy-button').disabled = true;
			}
			//////////////////////////////////////////////////////////////////////////////////////////
			
			var BlSW = getBlockLTRB(WcSW);
			var BlNE = getBlockLTRB(WcNE);
			
			/* Updated [02/01/2013]
			draggableRect.setBounds(new google.maps.LatLngBounds(
				new google.maps.LatLng(BlSW[0].lat(),BlSW[0].lng()),
				new google.maps.LatLng(BlNE[0].lat(),BlNE[0].lng())
			));

			var WcCenter = new google.maps.Point(BlNE[2].x-((BlNE[2].x-BlSW[2].x)/2),BlSW[2].y-((BlSW[2].y-BlNE[2].y)/2));
			var LatLngCenter = getBlockMarker(WcCenter.x, WcCenter.y);

			if (BlSW[2].y < BlNE[2].y) {
				blocksAvailableInDraggableRect = BlSW[2].x+"-"+BlNE[2].y-1;
			}
			else {
				blocksAvailableInDraggableRect = BlSW[2].x+"-"+BlNE[2].y+"_"+(BlNE[2].x-1)+"-"+(BlSW[2].y-1);
			}
			*/

			//////////////////////////////////////////////////////////////////////////////////////////
			// Updated [02/01/2013] in order avoid repeated evens on rect change
			//////////////////////////////////////////////////////////////////////////////////////////
			if (draggableRect != null) {
				draggableRect.setMap(null);
				draggableRect = null;
			}
			var bounds = new google.maps.LatLngBounds(
				new google.maps.LatLng(BlSW[0].lat(),BlSW[0].lng()),
				new google.maps.LatLng(BlNE[0].lat(),BlNE[0].lng())
			);
			draggableRect = new google.maps.Rectangle({
				bounds: bounds,
				editable: true
			});
			draggableRect.setMap(map);
			google.maps.event.addListener(draggableRect, 'bounds_changed', scheduleDelayedCallback);
			//////////////////////////////////////////////////////////////////////////////////////////
						
			var LatLngCenter = bounds.getCenter();

			//document.getElementById('buy-img').src = 'images/place_holder.png';
			//document.getElementById('buy-latitude').innerHTML = LatLngCenter.lat().toFixed(5);
			//document.getElementById('buy-longitude').innerHTML = LatLngCenter.lng().toFixed(5);
			if (currentPlotType == 4 || currentPlotType == 3) {
				// Special Area || Acquired Special Area
			}
			else if (currentPlotType == 2) {
				// Acquired Plot
			}
			else {
				document.getElementById('info-img').src = 'images/place_holder_small.png?_=1';
			}
			document.getElementById('info-latitude').innerHTML = LatLngCenter.lat().toFixed(5);
			document.getElementById('info-longitude').innerHTML = LatLngCenter.lng().toFixed(5);
			
			if (BlSW[2].y < BlNE[2].y) {
				blocksAvailableInDraggableRect = BlSW[2].x+"-"+BlNE[2].y-1;
			}
			else {
				blocksAvailableInDraggableRect = BlSW[2].x+"-"+BlNE[2].y+"_"+(BlNE[2].x-1)+"-"+(BlSW[2].y-1);
			}
			if(typeFirstBlock == 1){
				//alert(typeFirstBlock);
				numblocks = (BlNE[2].x - BlSW[2].x) * (BlSW[2].y - BlNE[2].y);
				if(numblocks > 0){
					document.getElementById('info-detail').innerHTML = 'Your description here.';
					price = "";
					//price = (numblocks*9.90);
					//price = price.toFixed(2);
					document.getElementById('info-detail').innerHTML  =  document.getElementById('info-detail').innerHTML + '<br /><br />Price: $<span id="theprice">'+price+"</span>";
					setCity(strlatlong, 1, numblocks);
				}
			}
			else if(typeFirstBlock == 4){
				//price = 499;
				//document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $'+price;
			}
		}
	}

	function scheduleDelayedCallback() {
		//consoleX("scheduleDelayedCallback");
		lastEvent = new Date();
		setTimeout(onDraggableRectangleChanged, 500);
	}
	
	//click
	function onRectangleClick(event) {
		//consoleX("onRectangleClick");
		if (draggableRect != null) {
			draggableRect.setMap(null);
			draggableRect = null;
		}
		blocksAvailableInDraggableRect = "";		
		var projection = new MercatorProjection();
		var worldCoordinate = projection.fromLatLngToPoint(event.latLng);
		worldCoordinate.x = Math.floor(worldCoordinate.x);
		worldCoordinate.y = Math.floor(worldCoordinate.y);
		var block = getBlockLTRB(worldCoordinate);
		var bounds = new google.maps.LatLngBounds(
			new google.maps.LatLng(block[0].lat(),block[0].lng()),
			new google.maps.LatLng(block[1].lat(),block[1].lng())
		);
		var LtLgNE = bounds.getNorthEast();
		var strlatlong = LtLgNE.lat()+","+LtLgNE.lng();
		//var p = projection.fromLatLngToContainerPixel(new google.maps.LatLng(block[0].lat(),block[0].lng()));
		/*
		draggableRect = new google.maps.Rectangle({
			bounds: bounds,
			editable: true
		});
		draggableRect.setMap(map);
		blocksAvailableInDraggableRect = block[2].x+"-"+block[2].y;
		google.maps.event.addListener(draggableRect, 'bounds_changed', scheduleDelayedCallback);
		*/
		$("#tabs").tabs("select",0);//$("#tabs").tabs("select",4);
		//scheduleDelayedCallback();	// To update rect according to selected area
		updatePopupWindowTabInfo(event.latLng, strlatlong);
	}

	function onColoredRectangleClick(event) {
		if (draggableRect != null) {
			draggableRect.setMap(null);
			draggableRect = null;
		}
		blocksAvailableInDraggableRect = "";

		//$("#tabs").tabs("select",1);
		

		var projection = new MercatorProjection();
		var worldCoordinate = projection.fromLatLngToPoint(event.latLng);
		worldCoordinate.x = Math.floor(worldCoordinate.x);
		worldCoordinate.y = Math.floor(worldCoordinate.y);
		var block = getBlockLTRB(worldCoordinate);
		var bounds = new google.maps.LatLngBounds(
			new google.maps.LatLng(block[0].lat(),block[0].lng()),
			new google.maps.LatLng(block[1].lat(),block[1].lng())
		);
		
		var LtLgNE = bounds.getNorthEast();
		var strlatlong = LtLgNE.lat()+","+LtLgNE.lng();

		/*
		draggableRect = new google.maps.Rectangle({
			bounds: bounds,
			editable: true
		});
		draggableRect.setMap(map);
		blocksAvailableInDraggableRect = block[2].x+"-"+block[2].y;
		google.maps.event.addListener(draggableRect, 'bounds_changed', scheduleDelayedCallback);
		*/
		
		$("#tabs").tabs("select",0);//$("#tabs").tabs("select",4);

		
		//consoleX("Here");
		//scheduleDelayedCallback();	// To update rect according to selected area
		
		updatePopupWindowTabInfo(event.latLng, strlatlong);
	}

	var disableZoomChange = false;
	var disableOnIdle = false;
	var blocksHidden = true;
	function onIdle(manualCall) {
		if (disableOnIdle == true) {
			map.setMapTypeId(google.maps.MapTypeId.HYBRID);
			disableOnIdle = false;
			return;
		}
		// Remove rectangles if exist
		// while (rectangles.length > 0) { rectangles.pop().setMap(null); }
		//Hide rectangles
		
		
		if (map == null) { return; }
		if (map.getZoom() >= 17) {
			consoleX("show blocks");
			if (markers_loaded == true) {
				markers_loaded = false;
				while (markers.length > 0) {
					markers.pop().setMap(null);
				}
			}
			drawBlocks(map);
			blocksHidden = false;
		}
		else {
			unsetGZones();
			if(!blocksHidden){
				consoleX("hide blocks");
				var i = 0;
				process = function(){
					for(; i<window.rectangles.length; i++){
						//window.rectangles[i].setOptions({strokeColor: "#ff0000"});
						window.rectangles[i].setOptions({strokeOpacity: 0});
						window.rectangles[i].setVisible(false);
					}
					if (i + 1 <= window.rectangles.length && i % 20 == 0) {
						setTimeout(process, 5);
					}
				}
				process();
				blocksHidden = true;
			}
			
			if (manualCall) {
				// Manually refresh markers if required
				if (markers_loaded == true) {
					markers_loaded = false;
					while (markers.length > 0) {
						markers.pop().setMap(null);
					}
				}
			}
			// Load markers for world view and regional view if not exist
			if (markers_loaded == false) { 
				consoleX("show markers");
				markers = ajaxAddMarkers(map); 
			
			}
		}
	}
	
	
	function getBlockLTRB(worldCoordinate) {
		var projection = new MercatorProjection();
		var lt = projection.fromPointToLatLng(worldCoordinate);
		worldCoordinate.x = Number(worldCoordinate.x) + 1;
		worldCoordinate.y = Number(worldCoordinate.y) + 1;
		var rb = projection.fromPointToLatLng(worldCoordinate);
		var result = [];
		result.push(lt);
		result.push(rb);
		result.push(worldCoordinate);
		return result;
	}

	function getBlockMarker(x, y) {
		var worldCoordinate = new google.maps.Point(x, y);
		worldCoordinate.x -= 0.5;
		worldCoordinate.y -= 0.5;
		var projection = new MercatorProjection();
		return projection.fromPointToLatLng(worldCoordinate);
	}
	
	function gotoLoc(x, y){
		LatLng = getBlockMarker(x, y);
		var loc = new google.maps.LatLng(LatLng.lat(),LatLng.lng());
		map.setZoom(17);
		map.setCenter(loc);
		jQuery("#clicktozoom").hide();
		strlatlong = LatLng.lat()+","+LatLng.lng();
		updatePopupWindowTabInfo(LatLng, strlatlong);
	}
	
	function getBlockInfo(inLatLng) {
		var projection = new MercatorProjection();
		var worldCoordinate = projection.fromLatLngToPoint(inLatLng);
		worldCoordinate.x = Math.floor(worldCoordinate.x);
		worldCoordinate.y = Math.floor(worldCoordinate.y);
		var lt = projection.fromPointToLatLng(worldCoordinate);
		worldCoordinate.x += 1;
		worldCoordinate.y += 1;
		var rb = projection.fromPointToLatLng(worldCoordinate);
		var result = [];
		result.push(lt);
		result.push(rb);
		result.push(worldCoordinate);
		return result;
	}
	var blockmoreinfocache = {};
	function getBlockMoreInfo(LatLng){
		if(blockmoreinfocache[LatLng]){
			return blockmoreinfocache[LatLng];
		}
		ret = {};
		url = "http://maps.googleapis.com/maps/api/geocode/json?latlng="+LatLng+"&sensor=true";
		var price = 0;
		var city = "";
		var region = "";
		var country = "";
		var areatype = "";
		jQuery.ajax({
			dataType: "json",
			url: url,
			async:false,
			success: function(data){
				try{
					ixt = data['results'].length;			
					for(ix=0; ix<ixt; ix++){
						length = data['results'][ix]['address_components'].length;
						for(i=0; i<length; i++){
							type = data['results'][ix]['address_components'][i]['types'][0];
							if(type=='locality'&&!city){
								city = data['results'][ix]['address_components'][i].long_name;
							}
							else if(type.indexOf("administrative_area_level_1")==0&&!region){
								region = data['results'][ix]['address_components'][i].long_name;
							}
							else if(type.indexOf("country")==0&&!country){
								country = data['results'][ix]['address_components'][i].long_name;
							}
						}
					}
					if(city){
						price = 9.90;
					}
					else if(region||country){
						price = 5.90;
					}
					else{
						url = "/checkwater.php/?latlong="+LatLng;
						jQuery.ajax({
							dataType: "html",
							url: url,
							async:false,
							success: function(data){
								areatype = data;
								if(areatype=="water"){
									price = 0.90;
								}
								else{
									price = 10.90;
								}
							}
						});
					}
					
					//consoleX(city); //address_components[0].long_name+"<---");
					//consoleX(data.results.address_components[0].types[0]+"<---");
					
				}
				catch(e){
					url = "/checkwater.php/?latlong="+LatLng;
					jQuery.ajax({
						dataType: "html",
						url: url,
						async:false,
						success: function(data){
							areatype = data;
							if(areatype=="water"){
								price = 0.90;
							}
							else{
								price = 10.90;
							}
						}
					});
				}
				
			}
		});
		ret.city = city;
		ret.region = region;
		ret.country = country;
		ret.areatype = areatype;
		ret.price = price;
		blockmoreinfocache[LatLng] = ret;
		return ret;
	}
	
	
	function ajaxGetMarker2(map, x1, y1, x2, y2, multi) {
		var markersJSON = null;
		$.ajax({
			url:'ajax/get_markers.php?x1='+x1+'&y1='+y1+"&type=exact&_=<?php echo time(); ?>",
			dataType:'html',
			async:false,
			success:function(data, textStatus, jqXHR){
				markersJSON = data;
			}
		});
		return markersJSON;
	}
	
	
	
	function getBlockInfoNew(inLatLng, strlatlong, worldCoordinate){
		var ret = {};
		ret.inLatLng = inLatLng;
		ret.detail = "";
		ret.title = "";
		ret.points = "";
		ret.json = "";
		ret.attached = "";
		var blockInfo = getBlockInfo(inLatLng);
		var returnText = ajaxGetMarker2(map, blockInfo[2].x, blockInfo[2].y, blockInfo[2].x, blockInfo[2].y);
		var markerJSON = JSON.parse(returnText);
		//returnText = '[[]]';
		if (returnText != '[[]]') {
			if(markerJSON[0].email){ //if special land unpaid
				consoleX(markerJSON[0].land_special_id);
				consoleX(markerJSON[0].points);
				ret.attached = markerJSON[0].points;
				ret.price = markerJSON[0].price;
			}
			else{ //for bidding only
				consoleX("for bidding");
				ret.price = 0;
			}
			ret.colored = 1;
			//set the points
			ret.points = worldCoordinate.x+"-"+worldCoordinate.y;
			ret.strlatlong = strlatlong;
			ret.title = markerJSON[0].title;
			ret.detail = markerJSON[0].detail;
			ret.json = markerJSON[0];
			rtemp = getBlockMoreInfo(strlatlong);
			ret.city = rtemp.city;
			ret.region = rtemp.region;
			ret.country = rtemp.country;
			ret.areatype = rtemp.areatype;
			
		}
		else{
			rtemp = getBlockMoreInfo(strlatlong);
			ret.price = rtemp.price;
			ret.city = rtemp.city;
			ret.region = rtemp.region;
			ret.country = rtemp.country;
			ret.areatype = rtemp.areatype;
			ret.colored = 0;
			//set the points
			ret.points = worldCoordinate.x+"-"+worldCoordinate.y;
			ret.strlatlong = strlatlong;
		}
		return ret; //returns ret object
	}
	
	function unsetGZones(){
		consoleX("unset gzones");
		for(i=0; i<gzones.length; i++){
			gzones[i].ret.active = 0;
			gzones[i].setVisible(false);
			gzones[i].setMap(null);
		}
		gzones = [];
		for(i=0; i<gattached.length; i++){
			gattached[i].setVisible(false);
			gattached[i].setMap(null);
		}
		gattached = [];
		updatePopupWindowTabInfoNew();
	}
	
	function calculateTotal(sync){
		total = 0;
		for(i=0; i<gzones.length; i++){
			if(gzones[i].ret.active){
				total += gzones[i].ret.price*1;
			}
		}
		setSessionPrice(total, sync);
		consoleX("Calculated total = "+total);
	}
	
	function cancelBox(i){
		is = i.split(",");
		for(x=0; x<is.length; x++){
			i = is[x];
			if(i!=""){
				gzones[i].ret.price = 0;
				gzones[i].ret.active = 0;
				gzones[i].setMap(); //remove the box
			}
		}
		calculateTotal(); //calculate total of 
		updatePopupWindowTabInfoNew();
	}
	
	gsessiondetails = "";
	gsessiondetails = "";
	function updatePopupWindowTabInfoNew(){
	
		consoleX("updatePopupWindowTabInfoNew");
		jQuery("#buy-button").hide();
		if(!gzones.length){
			showPopupWindowTabInfo(false);
			return false;
		}
		
		shareowner = "";
		sharetitle = "";
		sharedetail = "";
		sharetext = "";
		
		jQuery("#fbsharelink").hide();
		jQuery("#sharethisloc").hide();
		showPopupWindowTabInfo(false);
		
		/*
		info-city
		info-img
		info-lightbox
		info-land_owner_container
		info-land_owner
		info-title
		info-detail
		buy-button
		*/
		//initialize
		jQuery("#info-land_owner_container").hide();
		jQuery("#info-land_owner").html("");
		jQuery("#info-city").hide();
		jQuery("#info-title").html("");
		jQuery("#info-detail").html("");
		
		
		//set first active index to get the 1st lat and long of selected boxes
		firstindex = "";
		
		//write in details
		details = "";
		total = 0;
		blankblocks = false;
		specialbought = false;
		detailsobjarr = {};
		
		for(i=0; i<gzones.length; i++){
			if(gzones[i].ret.active){
				if(gzones[i].ret.json){ //if a special land or a bought land
					jQuery("#info-title").html(gzones[i].ret.json.title);
					jQuery("#info-detail").html(gzones[i].ret.json.detail);
					jQuery("#info-city").show();
					if(gzones[i].ret.city){
						jQuery("#info-city").html(gzones[i].ret.city);
					}
					else if(gzones[i].ret.region){
						jQuery("#info-city").html(gzones[i].ret.region);
					}
					else if(gzones[i].ret.country){
						jQuery("#info-city").html(gzones[i].ret.country);
					}
					else if(gzones[i].ret.areatype=='water'){
						jQuery("#info-city").html("Water Area");
					}
					else {
						jQuery("#info-city").html("Land Area");
					}
					xx = jQuery("#info-city").html();
					jQuery("#info-city").html(xx+": ");
					
					if(gzones[i].ret.json.land_owner){
						jQuery("#info-land_owner_container").show();
						jQuery("#info-land_owner").html(gzones[i].ret.json.land_owner);
					}
					if(firstindex==""){
						firstindex = i;
					}
					specialbought = true;
					break;
				}
				else{ //selected blank blocks
					blankblocks = true;
					if(firstindex==""){
						firstindex = i;
					}
					if(gzones[i].ret.city){
						if(!detailsobjarr["City: "+gzones[i].ret.city]){
							detailsobjarr["City: "+gzones[i].ret.city] = new function(){
								var price = 0;
								var count = 0;
								var idx = "";
							};
						}
						if(!detailsobjarr["City: "+gzones[i].ret.city].price){
							detailsobjarr["City: "+gzones[i].ret.city].price = 0;
						}
						detailsobjarr["City: "+gzones[i].ret.city].price = gzones[i].ret.price.toFixed(2);
						if(!detailsobjarr["City: "+gzones[i].ret.city].count){
							detailsobjarr["City: "+gzones[i].ret.city].count = 0;
						}
						detailsobjarr["City: "+gzones[i].ret.city].count++;
						if(!detailsobjarr["City: "+gzones[i].ret.city].idx){
							detailsobjarr["City: "+gzones[i].ret.city].idx = "";
						}
						detailsobjarr["City: "+gzones[i].ret.city].idx += i+",";
						//details += gzones[i].ret.city+": USD "+gzones[i].ret.price.toFixed(2)+" <img src='images/x.png' onclick='cancelBox("+i+")' style='cursor:pointer' /><br />";
					}
					else if(gzones[i].ret.region){
						
						if(!detailsobjarr["Region: "+gzones[i].ret.region]){
							detailsobjarr["Region: "+gzones[i].ret.region] = new function(){
								var price = 0;
								var count = 0;
								var idx = "";
							};
						}
						if(!detailsobjarr["Region: "+gzones[i].ret.region].price){
							detailsobjarr["Region: "+gzones[i].ret.region].price = 0;
						}
						detailsobjarr["Region: "+gzones[i].ret.region].price = gzones[i].ret.price.toFixed(2);
						if(!detailsobjarr["Region: "+gzones[i].ret.region].count){
							detailsobjarr["Region: "+gzones[i].ret.region].count = 0;
						}
						detailsobjarr["Region: "+gzones[i].ret.region].count++;
						if(!detailsobjarr["City: "+gzones[i].ret.city].idx){
							detailsobjarr["City: "+gzones[i].ret.city].idx = "";
						}
						detailsobjarr["Region: "+gzones[i].ret.region].idx += i+",";
						//details += gzones[i].ret.region+": USD "+gzones[i].ret.price.toFixed(2)+" <img src='images/x.png'  onclick='cancelBox("+i+")' style='cursor:pointer' /><br />";
					}
					else if(gzones[i].ret.country){
						if(!detailsobjarr["Country: "+gzones[i].ret.country]){
							detailsobjarr["Country: "+gzones[i].ret.country] = new function(){
								var price = 0;
								var count = 0;
								var idx = "";
							};
						}
						if(!detailsobjarr["Country: "+gzones[i].ret.country].price){
							detailsobjarr["Country: "+gzones[i].ret.country].price = 0;
						}
						detailsobjarr["Country: "+gzones[i].ret.country].price = gzones[i].ret.price.toFixed(2);
						if(!detailsobjarr["Country: "+gzones[i].ret.country].count){
							detailsobjarr["Country: "+gzones[i].ret.country].count = 0;
						}
						detailsobjarr["Country: "+gzones[i].ret.country].count++;
						if(!detailsobjarr["City: "+gzones[i].ret.city].idx){
							detailsobjarr["City: "+gzones[i].ret.city].idx = "";
						}
						detailsobjarr["Country: "+gzones[i].ret.country].idx += i+",";
						//details += gzones[i].ret.country+": USD "+gzones[i].ret.price.toFixed(2)+" <img src='images/x.png'  onclick='cancelBox("+i+")' style='cursor:pointer' /><br />";
					}
					else if(gzones[i].ret.areatype=='water'){
						if(!detailsobjarr["Water Area: "]){
							detailsobjarr["Water Area: "] = new function(){
								var price = 0;
								var count = 0;
								var idx = "";
							};
						}
						if(!detailsobjarr["Water Area: "].price){
							detailsobjarr["Water Area: "].price = 0;
						}
						detailsobjarr["Water Area: "].price = gzones[i].ret.price.toFixed(2);
						if(!detailsobjarr["Water Area: "].count){
							detailsobjarr["Water Area: "].count = 0;
						}
						detailsobjarr["Water Area: "].count++;
						if(!detailsobjarr["City: "+gzones[i].ret.city].idx){
							detailsobjarr["City: "+gzones[i].ret.city].idx = "";
						}
						detailsobjarr["Water Area: "].idx += i+",";
						//details += "Water Area"+": USD "+gzones[i].ret.price.toFixed(2)+" <img src='images/x.png'  onclick='cancelBox("+i+")' style='cursor:pointer' /><br />";
					}
					else {
						if(!detailsobjarr["Land Area: "]){
							detailsobjarr["Land Area: "] = new function(){
								var price = 0;
								var count = 0;
								var idx = "";
							};
						}
						if(!detailsobjarr["Land Area: "].price){
							detailsobjarr["Land Area: "].price = 0;
						}
						detailsobjarr["Land Area: "].price = gzones[i].ret.price.toFixed(2);
						if(!detailsobjarr["Land Area: "].count){
							detailsobjarr["Land Area: "].count = 0;
						}
						detailsobjarr["Land Area: "].count++;
						if(!detailsobjarr["City: "+gzones[i].ret.city].idx){
							detailsobjarr["City: "+gzones[i].ret.city].idx = "";
						}
						detailsobjarr["Land Area: "].idx += i+",";
						//details += "Land Area"+": USD "+gzones[i].ret.price.toFixed(2)+" <img src='images/x.png'  onclick='cancelBox("+i+")' style='cursor:pointer' /><br />";
					}
					total += gzones[i].ret.price*1;
				}
			}	
		}
		gsessiondetails = "";
		if(blankblocks){
			consoleX("blankblocks");
			for(x in detailsobjarr){
				detail = x + " USD " + detailsobjarr[x].price  + " x " + detailsobjarr[x].count ;
				gsessiondetails += detail + "<br />";
				details += detail + " <img src='images/x.png'  onclick='cancelBox(\""+detailsobjarr[x].idx+"\")' style='cursor:pointer' /><br />" ;
			}
			showPopupWindowTabInfo(true);
			jQuery("#info-lightbox").attr("href", "images/place_holder.png?_=1" );
			jQuery("#info-lightbox").attr("title", "" );
			jQuery("#info-lightbox").colorbox({width:'550px'});
			jQuery("#info-img")[0].src = "images/place_holder_small.png?_=1";
			jQuery("#info-img").unbind();
				
			jQuery("#buy-button").val("Buy");
			jQuery("#buy-button").show();
			details += "<hr />Total: USD "+total.toFixed(2);
			jQuery("#info-title").html("Buy Land");
			jQuery("#info-detail").html(details);
		}
		else if(specialbought){
			consoleX("special or bought");
			if(gzones[i].ret.json.thumb_url){
				jQuery("#info-lightbox").attr("href", gzones[i].ret.json.img_url );
				xtitle = "";
				if(gzones[i].ret.json.land_owner){
					xtitle = "Land Owner: "+gzones[i].ret.json.land_owner+"<br />";
				}
				xtitle += gzones[i].ret.json.detail;
				xtitle = "";
				jQuery("#info-lightbox").attr("title", xtitle );
				//jQuery("#info-lightbox").lightBox({fixedNavigation:true});
				jQuery("#info-lightbox").colorbox({width:'550px'});
				jQuery("#info-img")[0].src = gzones[i].ret.json.thumb_url;
			}
			else{
				jQuery("#info-lightbox").attr("href", "images/place_holder.png?_=1" );
				Query("#info-lightbox").attr("title", "" );
				jQuery("#info-lightbox").colorbox({width:'550px'});
				jQuery("#info-img")[0].src = "images/place_holder_small.png?_=1";
				jQuery("#info-img").unbind();
			}
			if(gzones[i].ret.json.land_special_id){
				if (gzones[i].ret.json.email == masterUser) {
					consoleX("special unbought");
					price = gzones[i].ret.json.price;
					document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $<span id="theprice">'+price+"</span>";
				}
				else{
					consoleX("special bought");
				}
			}
			else{
				consoleX("not special bought");
			}
			showPopupWindowTabInfo(true);
		}
		else{
			showPopupWindowTabInfo(false);
			return false;
		}
		
		if(!sharetitle){
			sharetitle = "Mark your very own Piece of the World!";
		}
		if(!sharetext){
			sharetext = "Get your own piece of the world at pieceoftheworld.com";
		}
		link = "http://www.pieceoftheworld.co/?latlong="+gzones[0].ret.inLatLng.lat()+"~"+gzones[firstindex].ret.inLatLng.lng();
		
		globallink = link;
		//link = encodeURIComponent(link);
		sharelink = "https://www.facebook.com/dialog/feed?app_id=454736247931357&link="+link+"&picture="+document.getElementById('info-img').src+"&name=Piece of the World&caption="+sharetitle+"&description="+sharetext+"&redirect_uri="+link;
		
		jQuery("#fbsharelink").attr("href", sharelink);
		jQuery("#fbsharelink").show();
		jQuery("#sharethisloc").show();
	}
	
	var gzones = [];
	var gattached = [];
	function putBox(event){
		//disable block clicking when zoomed out
		if(map.getZoom()<17){
			unsetGZones();
			return 0;
		}
		var projection = new MercatorProjection();
		var worldCoordinate = projection.fromLatLngToPoint(event.latLng);
		worldCoordinate.x = Math.floor(worldCoordinate.x);
		worldCoordinate.y = Math.floor(worldCoordinate.y);
		var block = getBlockLTRB(worldCoordinate);
		var bounds = new google.maps.LatLngBounds(
			new google.maps.LatLng(block[0].lat(),block[0].lng()),
			new google.maps.LatLng(block[1].lat(),block[1].lng())
		);
		var LtLgNE = bounds.getNorthEast();
		var LtLgSW = bounds.getSouthWest();
		
		strlatlong = LtLgNE.lat()+","+LtLgNE.lng();
		jQuery("#tabs").tabs("select",0);
		//new update popup info
		ret = getBlockInfoNew(event.latLng, strlatlong, worldCoordinate);
		
		if(ret.colored){
			unsetGZones();
		}
		else{
			//remove all colored selections
			for(i=0; i<gzones.length; i++){
				if(gzones[i].ret.colored==1){
					gzones[i].ret.active = 0;
					gzones[i].setMap();
				}
			}
			//remove all attached selections
			for(i=0; i<gattached.length; i++){
				gattached[i].setVisible(false);
				gattached[i].setMap(null);
			}
			gattached = [];
		}
		
		//put in the box
		var zoneCoords = [
			new google.maps.LatLng(LtLgNE.lat(), LtLgNE.lng()),
			new google.maps.LatLng(LtLgNE.lat(), LtLgSW.lng()),
			new google.maps.LatLng(LtLgSW.lat(), LtLgSW.lng()),
			new google.maps.LatLng(LtLgSW.lat(), LtLgNE.lng())
		];
		ret.active = 1;
		zone = new google.maps.Polygon({
			paths: zoneCoords,
			strokeColor: "#ffffff",
			strokeOpacity: 1,
			strokeWeight: 2,
			fillColor: "#40E0D0",
			fillOpacity: 0.2,
			zIndex: 10000,
			ret: ret //just extra 'ret' object variable
		});
		google.maps.event.addListener(zone, 'click', function(event){
			this.ret.price = 0;
			this.ret.active = 0;
			this.setMap(); //remove the box
			if(this.ret.attached.length){
				unsetGZones();
			}
			calculateTotal(); //calculate total of 
			updatePopupWindowTabInfoNew();
		});
		zone.setMap(window.map);
		gzones.push(zone);
		calculateTotal();
		updatePopupWindowTabInfoNew();
		
		if(ret.attached){
			thex = worldCoordinate.x;
			they = worldCoordinate.y;
			for(i=0; i<ret.attached.length; i++){
				if(ret.attached[i].x==thex && ret.attached[i].y==they){
					continue;
				}
				worldCoordinate = new google.maps.Point(ret.attached[i].x-1, ret.attached[i].y-1);
				var block = getBlockLTRB(worldCoordinate);
				consoleX("block = ");
				consoleX(block);
				var bounds = new google.maps.LatLngBounds(
					new google.maps.LatLng(block[0].lat(),block[0].lng()),
					new google.maps.LatLng(block[1].lat(),block[1].lng())
				);
				var LtLgNE = bounds.getNorthEast();
				var LtLgSW = bounds.getSouthWest();
				
				//put in the box
				var zoneCoords = [
					new google.maps.LatLng(LtLgNE.lat(), LtLgNE.lng()),
					new google.maps.LatLng(LtLgNE.lat(), LtLgSW.lng()),
					new google.maps.LatLng(LtLgSW.lat(), LtLgSW.lng()),
					new google.maps.LatLng(LtLgSW.lat(), LtLgNE.lng())
				];
				ret.active = 1;
				zone = new google.maps.Polygon({
					paths: zoneCoords,
					strokeColor: "#ffffff",
					strokeOpacity: 1,
					strokeWeight: 2,
					fillColor: "#40E0D0",
					fillOpacity: 0.2,
					zIndex: 10000
				});
				gattached.push(zone);
				google.maps.event.addListener(zone, 'click', function(event){
					
					unsetGZones();
					calculateTotal(); //calculate total of 
					updatePopupWindowTabInfoNew();
				});
				zone.setMap(window.map);
			}
		}
	}
	
	function drawRect(lt, rb, color, opacity) {
		if (getCookie("config_showownedland") == "false" && color == fillColorAcquiredPlot) {
			opacity = 0;
		}
		if (getCookie("config_showimportantplaces") == "false" && color == fillColorSpecialArea) {
			opacity = 0;
		}
		strokeColor = "#ffff00"; //yellow
		//strokeColor = "#00fff0"; //light blue
		//consoleX(strokeOpacity);
		strokeOpacity = 0.9;
		var rectangle = new google.maps.Rectangle({
			strokeColor: strokeColor,
			strokeOpacity: parseInt(strokeOpacity),
			strokeWeight: parseFloat(strokeWeight),
			fillColor: color,
			fillOpacity: opacity,
			clickable: true,
			map: window.map,
			bounds: new google.maps.LatLngBounds(lt, rb)
		});
		google.maps.event.addListener(rectangle, 'click', function(event) 
		{ 
			putBox(event);
		});
		window.rectangles.push(rectangle);
	}
	
	function consoleX(str){
		try{
			console.log(str);
		}
		catch(e){
		}
	}
	
	
	var returnTextCache = [];
	
	function inreturnTextCache(hStart, vStart, hEnd, vEnd){
		for(i=0; i<returnTextCache.length; i++){
			//consoleX(hStart + ">=" + returnTextCache[i].hStart +"&&"+ hEnd +"<="+ returnTextCache[i].hEnd +"&&"+ vStart +">="+ returnTextCache[i].vStart +"&&"+ vEnd +"<="+ returnTextCache[i].vEnd)
			if(
				hStart >= returnTextCache[i].hStart && hEnd <= returnTextCache[i].hEnd &&
				vStart >= returnTextCache[i].vStart && vEnd <= returnTextCache[i].vEnd
			){
				//consoleX("in cache");
				return returnTextCache[i].returnText;
			}
		}
		return false;
	}
	
	function returnTextClass(hStartx, vStartx, hEndx, vEndx, returnTextx){
		this.hStart = hStartx;
		this.vStart = vStartx;
		this.hEnd = hEndx;
		this.vEnd = vEndx;
		this.returnText = returnTextx;
	}
	
	function drawBlocks(map) {		
		jQuery("#loadinggrid").css('left', jQuery(window).width()/2 - jQuery("#loadinggrid").width()/2);
		jQuery("#loadinggrid").css('top', jQuery(window).height()/2 - jQuery("#loadinggrid").height()/2);
		jQuery("#loadinggrid").css('z-index', 20000);
		jQuery("#loadinggrid").show();
		
		
		//consoleX("show loading");
		var config_showownedland = getCookie("config_showownedland");
		var config_showimportantplaces = getCookie("config_showimportantplaces");
		var config_showownland = getCookie("config_showownland");
		var user_email = getCookie("user_email");
		var bounds = map.getBounds();
		var ne = google.maps.geometry.spherical.computeOffset(bounds.getNorthEast(), 63.615*3, 45);
		var sw = google.maps.geometry.spherical.computeOffset(bounds.getSouthWest(), 63.615*3, 225);
		var blockRT = getBlockInfo(ne);
		var blockLB = getBlockInfo(sw);
		var vStart = blockRT[2].y;
		var vEnd = blockLB[2].y;
		var hStart = blockLB[2].x;
		var hEnd = blockRT[2].x;
		var returnText;
		returnText = inreturnTextCache(hStart, vStart, hEnd, vEnd);
		//consoleX(returnText);
		if(!returnText){
			allowance = 0.20;
			returnText = ajaxGetMarker(map, (hStart*1)-Math.round(hStart*allowance), (vStart*1)-Math.round(vStart*allowance), (hEnd*1)+Math.round(hEnd*allowance), (vEnd*1)+Math.round(vEnd*allowance), true);
			rtc = new returnTextClass((hStart*1)-Math.round(hStart*allowance), (vStart*1)-Math.round(vStart*allowance), (hEnd*1)+Math.round(hEnd*allowance), (vEnd*1)+Math.round(vEnd*allowance), returnText);
			returnTextCache.push(rtc);
		}
		
		//returnText = '[[]]';
		//alert(JSON.stringify(returnText));
		var markersJSON = JSON.parse(returnText);
		var color;
		var opacity;
		var email;
		var cnt1 = vStart;		
		var process0 = function() {
			for (; cnt1 <= vEnd; cnt1++) {
				var cnt2 = hStart;
				var process = function() {
					/*
					for (; index < length; index++) {
					var toProcess = xmlElements[index];
					// Perform xml processing
					if (index + 1 < length && index % 100 == 0) {
						setTimeout(process, 5);
					}
					}
					*/
					for (; cnt2 <= hEnd; cnt2++) {
						color = "";
						opacity = 0;
						var result = getBlockLTRB(new google.maps.Point(cnt2, cnt1));
						
						if(window.rectanglesxy.indexOf(result[0]+"-"+result[1])<0){
							
							if (returnText != '[[]]') {
								for (var i = 0, len = markersJSON.length; i < len; i++) {
									email = markersJSON[i].email;
									if (markersJSON[i].x == result[2].x && markersJSON[i].y == result[2].y) {
										color = (markersJSON[i].land_special_id == null) ? fillColorAcquiredPlot : fillColorAcquiredSpecialArea;
										if (email == masterUser) { color = fillColorSpecialArea; }
											opacity = parseFloat(fillOpacity);
										break;
									}
								}
								
							}
							if(color!=""||1){
								//draw rect
								if (config_showownedland == "false" && color != fillColorSpecialArea && masterUser != email && user_email != email) { opacity = 0; }
								if (config_showimportantplaces == "false" && color == fillColorSpecialArea) { opacity = 0; }
								if (config_showownland == "false" && user_email == email) { opacity = 0; }
								drawRect(result[0], result[1], color, opacity);
								window.rectanglesxy[window.rectangles.length-1]= result[0]+"-"+result[1];
							}
							
						}
						else{ //in cache
							ndexx = window.rectanglesxy.indexOf(result[0]+"-"+result[1]);
							window.rectangles[ndexx].setOptions({strokeOpacity: 0.9});
							window.rectangles[ndexx].setMap(window.map);
							window.rectangles[ndexx].setVisible(true);
						}
						if (cnt2 + 1 <= hEnd && cnt2 % 20 == 0) {
							//consoleX("cnt2 setTimeout "+cnt2);
							setTimeout(process, 5);
						}
					}
				};
				process();
				if (cnt1 + 1 <= vEnd && cnt1 % 20 == 0) {
					//consoleX("cnt1 setTimeout "+cnt1);
					setTimeout(process0, 5);
				}
				
				//consoleX(cnt1 +" "+ vEnd +" - "+ cnt2 +" "+ hEnd);
				
				if(cnt1 >= vEnd && cnt2 >= hEnd ){
					jQuery("#loadinggrid").css('top', -10000);
					//consoleX("hide loading");
				}
			}
		}
		<?php if($_GET['noprocess']){ echo "//";};?>process0();		
	}
	/*
	function trimString(id, title, str, noOfCharactersToRestrict) {
		// Create new DIV
		var divTag = document.createElement("div");
		divTag.id = "news-id-"+id;
		divTag.className = "reveal-modal";
		divTag.setAttribute('style', 'position: absolute; z-index: 1010;');
		divTag.innerHTML = "<h1>"+title+"</h1><p><img src=\"images/thumbs/news_id_"+id+"\" align=\"left\">"+str+"</p><a class=\"close-reveal-modal\">&#215;</a>";
		document.body.appendChild(divTag);
		
		// Create a new LI
		var url = "getnews.php?id="+id;
		str = (str.length > noOfCharactersToRestrict) ? str.substring(0, noOfCharactersToRestrict-3-5)+"..." : str;
		//str += " <a href=\""+url+"\" target=\"_blank\">more</a>";
		str += " <a href=\"#\" data-reveal-id=\"news-id-"+id+"\" data-animation=\"fade\">more</a>";
		return str;
	}
	
	function updatePopupWindowTabNews() {
		$('#news-ul > li').remove();
		var markersJSON = null;
		$.ajax({
			url:'ajax/get_news.php',
			dataType:'html',
			async:false,
			success:function(data, textStatus, jqXHR){
				var newsJSON = JSON.parse(data);
				if (data != '[[]]') {
					for (var i = 0, len = newsJSON.length; i < len; ++i) {
						var li = document.createElement('li');
						li.innerHTML = "<span id=\"news-"+(i+1)+"-text\" style=\"float: left; width: 202px;\">"+trimString(newsJSON[i].id, newsJSON[i].title, newsJSON[i].detail, 60)+"</span><span><img id=\"news-"+(i+1)+"-img\" src=\"images/thumbs/news_id_"+newsJSON[i].id+"\" width=\"32\" height=\"32\" border=\"0\"></span>";
						$("#news-ul").append(li);
					}
					var liLen = newsJSON.length;
					if (liLen > 3) { 
						jQuery('#news-ul').jcarousel({vertical:true,scroll:2});
					}
					else {
						$(".jcarousel-next").hide();
						$(".jcarousel-prev").hide();
					}
				}
			}
		});
	}
	*/
	
	function showPopupWindowTabInfo(isSelected) {
		document.getElementById('info-span-noselection').style.display = (isSelected == true) ? 'none' : 'block';
		document.getElementById('info-span').style.display = (isSelected == true) ? 'block' : 'none';
        FB.XFBML.parse();
	}
	
	

	var globallink;
	function updatePopupWindowTabInfo(inLatLng, strlatlong) {
		consoleX("updatePopupWindowTabInfo");
		shareowner = "";
		sharetitle = "";
		sharedetail = "";
		sharetext = "";
		
		/*
		info-city
		info-img
		info-lightbox
		info-land_owner_container
		info-land_owner
		info-title
		info-detail
		buy-button
		*/
		//initialize
		jQuery("#info-land_owner_container").hide();
		jQuery("#info-land_owner").html("");
		jQuery("#info-city").hide();
		jQuery("#info-title").html("");
		jQuery("#info-detail").html("");
		
		
		jQuery("#fbsharelink").hide();
		jQuery("#sharethisloc").hide();
		showPopupWindowTabInfo(true);
		document.getElementById('info-latitude').innerHTML = inLatLng.lat().toFixed(5);
		document.getElementById('info-longitude').innerHTML = inLatLng.lng().toFixed(5);

		
		
		
		
		//document.getElementById('buy-latitude').innerHTML = inLatLng.lat().toFixed(5);
		//document.getElementById('buy-longitude').innerHTML = inLatLng.lng().toFixed(5);
		
		var blockInfo = getBlockInfo(inLatLng);
		var returnText = ajaxGetMarker(map, blockInfo[2].x, blockInfo[2].y, blockInfo[2].x, blockInfo[2].y);
		var markerJSON = JSON.parse(returnText);
		document.getElementById('info-land_owner_container').style.display="none";
		document.getElementById('info-img').src = "images/place_holder_small.png?_=1";
		if (returnText != '[[]]') {
			document.getElementById('info-title').innerHTML = markerJSON[0].title;
			document.getElementById('info-detail').innerHTML = markerJSON[0].detail;
			

			if(markerJSON[0].land_owner){
				
				document.getElementById('info-land_owner').innerHTML = markerJSON[0].land_owner;
				document.getElementById('info-land_owner_container').style.display="";
			}
			else{
				document.getElementById('info-land_owner').innerHTML = "";
			}
			//ajaxExtractLandPicture(markerJSON[0].id);
			//document.getElementById('info-img').src = "images/thumbs/land_id_"+markerJSON[0].id;
			//alert(markerJSON[0].thumb_url);
			if(markerJSON[0].thumb_url){
				document.getElementById('info-img').src = markerJSON[0].thumb_url;
				jQuery("#info-lightbox").attr("href", markerJSON[0].img_url );
				xtitle = "";
				if(markerJSON[0].land_owner){
					xtitle = "Land Owner: "+markerJSON[0].land_owner+"<br />";
				}
				xtitle += markerJSON[0].detail;
				xtitle = "";
				jQuery("#info-lightbox").attr("title", xtitle );
				//jQuery("#info-lightbox").lightBox({fixedNavigation:true});
				jQuery("#info-lightbox").colorbox({width:'550px'});
			}
			else{
				jQuery("#info-lightbox").attr("href", "images/place_holder.png?_=1" );
				jQuery("#info-lightbox").attr("title", "" );
				jQuery("#info-lightbox").colorbox({width:'550px'});
				jQuery("#info-img")[0].src = "images/place_holder_small.png?_=1";
				jQuery("#info-img").unbind();
			}
	
			shareowner = jQuery('#info-land_owner').text();
			sharetitle = jQuery('#info-title').text();	
			sharedetail = jQuery('#info-detail').text();
			
			if(shareowner){
				sharetext = " Owner: "+shareowner+". "+sharedetail;
			}
			else{
				sharetext = sharedetail;
			}
			consoleX(markerJSON[0]);
			consoleX(markerJSON[0].email+"="+masterUser);
			if (markerJSON[0].email == masterUser) {
				if(markerJSON[0].land_special_id){
					price = markerJSON[0].price;
					document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $<span id="theprice">'+price+"</span>";
				}
				else if(numblocks > 0){
					price = "";
					//price = (numblocks*9.90);
					//price = price.toFixed(2);
					document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $<span id="theprice">'+price+"</span>";
					setCity(strlatlong, 1, numblocks);
				}
				document.getElementById('buy-button').value = "Buy";
			}
			else {
				document.getElementById('buy-button').value = "Bid";
				setCity(strlatlong);
			}
			//document.getElementById('buy-img').src = "images/thumbs/land_id_"+markerJSON[0].id;
		}
		else {
			//alert(updatePopupWindowTabInfo);
			document.getElementById('info-title').innerHTML = 'Your title here.';
			document.getElementById('info-detail').innerHTML = 'Your description here.';
			
			if(markerJSON[0].land_special_id){
				price = markerJSON[0].price;
				document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $<span id="theprice">'+price+"</span>";
			}
			else if(numblocks > 0){
				price = "";
				//price = (numblocks*9.90);
				//price = price.toFixed(2);
				document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $<span id="theprice">'+price+"</span>";
				setCity(strlatlong, 1, numblocks);
			}

			document.getElementById('info-img').src = 'images/place_holder_small.png?_=1';
			document.getElementById('buy-button').value = "Buy";

			//document.getElementById('buy-img').src = 'images/place_holder.png';
		}
		
		if(!sharetitle){
			sharetitle = "Mark your very own Piece of the World!";
		}
		if(!sharetext){
			sharetext = "Get your own piece of the world at pieceoftheworld.com";
		}
		link = "http://www.pieceoftheworld.co/?latlong="+inLatLng.lat()+"~"+inLatLng.lng();
		
		globallink = link;
		//link = encodeURIComponent(link);
		sharelink = "https://www.facebook.com/dialog/feed?app_id=454736247931357&link="+link+"&picture="+document.getElementById('info-img').src+"&name=Piece of the World&caption="+sharetitle+"&description="+sharetext+"&redirect_uri="+link;
		
		jQuery("#fbsharelink").attr("href", sharelink);
		jQuery("#fbsharelink").show();
		jQuery("#sharethisloc").show();
		
		//temporarily hide buy button
		jQuery("#buy-button").hide();
	}
	
	function updatePopupWindowTabConfig(update) {
		if (update == false) {
			document.getElementById("config_showownland").checked = (getCookie("config_showownland") == "false") ? false : true;
			document.getElementById("config_showimportantplaces").checked = (getCookie("config_showimportantplaces") == "false") ? false : true;
			document.getElementById("config_showownedland").checked = (getCookie("config_showownedland") == "false") ? false : true;
			document.getElementById("config_showgrid").checked = (getCookie("config_showgrid") == "false") ? false : true;
		}
		else {
			if (document.getElementById("config_showownland").checked == true && getCookie("config_showownland") == "false") {
				var user_email = getCookie("user_email");
				if (user_email == "false" || user_email == "") {
					document.getElementById("config_showownland").checked = false;
					alert("You can not view your own land unless you provide us\nyour registered email address. Please use 'Settings' screen\nto enter your registered email address.");
				}
				/*
				var msg = "";
				if (user_email == "false" || user_email == "") {
					user_email = "";
					var user_email = window.prompt("Enter your registered email address?", user_email);
					if (user_email != "") {
						setCookie("user_email", user_email, 365);
					}
				}
				else {
					var user_email = window.prompt("Please confirm that this your registered email address?", user_email);
					if (user_email != "") {
						setCookie("user_email", user_email, 365);
					}
				}
				*/
			}
			setCookie("config_showownland", document.getElementById("config_showownland").checked.toString(), 365);
			setCookie("config_showimportantplaces", document.getElementById("config_showimportantplaces").checked.toString(), 365);
			setCookie("config_showownedland", document.getElementById("config_showownedland").checked.toString(), 365);
			setCookie("config_showgrid", document.getElementById("config_showgrid").checked.toString(), 365);
			onIdle(true);
		}
	}
	
	function updatePopupWindowTabSearch() {
		var place = document.getElementById("search_topplaces").value;
		var location;
		var zoom;
		if (place == "Atlantis") {
			location = new google.maps.LatLng(31.254314,-24.258481);
			zoom = 8;
		}
		else if (place == "Firefox Crop Circles") {
			location = new google.maps.LatLng(45.123719,-123.113633);
			zoom = 18;
		}
		else if (place == "UFO Landing Pads") {
			location = new google.maps.LatLng(52.481725,0.520627);
			zoom = 20;
		}
		else if (place == "Badlands Guardian") {
			location = new google.maps.LatLng(50.010489,-110.116906);
			zoom = 16;
		}
		else if (place == "Lost at Sea") {
			location = new google.maps.LatLng(19.646108,37.295047);
			zoom = 20;
		}
		//disableZoomChange = true;
		window.searchMarker.setPosition(location);
		window.map.setZoom(zoom);
		window.map.setCenter(location);
	}

	function ajaxExtractLandPicture(land_id) {
		//var markersJSON = null;
		$.ajax({
			url:'ajax/extract_land_picture.php?land_id='+land_id+"&_=<?php echo time(); ?>",
			dataType:'html',
			async:false,
			success:function(data, textStatus, jqXHR){
				//markersJSON = data;
			}
		});
		//return markersJSON;
	}
	
	function ajaxGetMarker(map, x1, y1, x2, y2, multi) {
		if(multi){
			var markersJSON = null;
			$.ajax({
				url:'ajax/get_markers.php?x1='+x1+'&y1='+y1+'&x2='+x2+'&y2='+y2+'&multi=1'+"&_=<?php echo time(); ?>",
				dataType:'html',
				async:false,
				success:function(data, textStatus, jqXHR){
					markersJSON = data;
				}
			});
			return markersJSON;
		}
		else{
			var markersJSON = null;
			$.ajax({
				url:'ajax/get_markers.php?x1='+x1+'&y1='+y1+'&x2='+x2+'&y2='+y2+"&_=<?php echo time(); ?>",
				dataType:'html',
				async:false,
				success:function(data, textStatus, jqXHR){
					markersJSON = data;
				}
			});
			return markersJSON;
		}
	}
	
	function ajaxAddMarkers(map, force) {
		markers_loaded = true;
		var markers = [];
		if(force){
			ajaxAddGreenMarkers(map, markers, force);
		}
		else{
			ajaxAddGreenMarkers(map, markers);
		}
		ajaxAddRedMarkers(map, markers);
		//markers = ajaxAddGreenMarkers(map);
		//markers.push.apply(markers, ajaxAddRedMarkers(map));
		return markers;
	}
	
	
	
	function setGreenMarkers(map, gMarkers, markersJSON){
		var config_showownland = getCookie("config_showownland");
		var config_showownedland = getCookie("config_showownedland");
		var user_email = getCookie("user_email");
		
		var pass = false;
		//alert(JSON.stringify(markersJSON));
		for (var i = 0, len = markersJSON.length; i < len; ++i) {
			pass = true;
			// check your own id when possible and act according to this config setting
			if (config_showownedland == "false" && user_email != markersJSON[i].email) {
				pass = false;
			}
			if (config_showownland == "false" && user_email == markersJSON[i].email) {
				pass = false;
			}
			if (pass == true) {
				if (markersJSON[i].land_special_id == null) {
					var marker = new google.maps.Marker({
						position: getBlockMarker(parseInt(markersJSON[i].x), parseInt(markersJSON[i].y)),
						map: map,
						icon: 'images/rmarker.png'
						//icon: (markersJSON[i].land_special_id == null) ? 'images/gmarker.png' : 'images/rmarker.png'
					});
					google.maps.event.addListener(marker, 'click', function(event) { onMarkerClick(event, 'regular'); /*consoleX(this.position);*/ });
					markers.push(marker);
				}
			}
		}
		gMarkers.push.apply(gMarkers, markers);
	}
	var globalMarkersResponseTextCacheJSON = "";
	
	function ajaxAddGreenMarkers(map, gMarkers, force) {
		if(globalMarkersResponseTextCacheJSON!=""&&!force){
			//consoleX(globalMarkersResponseTextCacheJSON);
			setGreenMarkers(map, gMarkers, globalMarkersResponseTextCacheJSON);
		}
		else{
			var jqxhr = $.ajax('ajax/get_markers.php'+"?_=<?php echo time(); ?>")
			.done(function() { 
				if (jqxhr.status == 200) {
					var markers = [];
					var markersJSON = JSON.parse(jqxhr.responseText);
					globalMarkersResponseTextCacheJSON = markersJSON;
					setGreenMarkers(map, gMarkers, markersJSON);
				}
			})
			.fail(function() {
				//alert("error");
			})
			.always(function() {
				//alert("complete");
			});
		}
	}

	
	
	function ajaxGetRedMarkerCoordinates(land_special_id) {
		var marker;
		$.ajax({
			url:'ajax/get_markers.php?land_special_id='+land_special_id+"&_=<?php echo time(); ?>",
			dataType:'html',
			async:false,
			success:function(data, textStatus, jqXHR){
				var markerJSON = JSON.parse(data);
				marker = new google.maps.Point(markerJSON[0].x, markerJSON[0].y);
			}
		});
		return marker;
	}
	
	
	function setRedMarkers(map, gMarkers, markersJSON){
		var config_showownland = getCookie("config_showownland");
		var config_showownedland = getCookie("config_showownedland");
		var config_showimportantplaces = getCookie("config_showimportantplaces");
		var user_email = getCookie("user_email");
		
		var pass = false;
		//alert(JSON.stringify(markersJSON));
		for (var i = 0, len = markersJSON.length; i < len; ++i) {
			pass = true;
			// check your own id when possible and act according to this config setting
			if (config_showownedland == "false" && (user_email != markersJSON[i].email && masterUser != markersJSON[i].email)) {
				pass = false;
			}
			if (config_showownland == "false" && user_email == markersJSON[i].email) {
				pass = false;
			}
			if (config_showimportantplaces == "false" && masterUser == markersJSON[i].email) {
				pass = false;
			}
			if (pass == true) {
				/*
				var coordinates = ajaxGetRedMarkerCoordinates(markersJSON[i].id);
				if (coordinates != null) {
					var marker = new google.maps.Marker({
						position: getBlockMarker(coordinates.x, coordinates.y),
						map: map,
						icon: (markersJSON[i].email == masterUser) ? 'images/rmarker.png' : 'images/dgmarker.png'
						//icon: (markersJSON[i].land_special_id == null) ? 'images/gmarker.png' : 'images/rmarker.png'
					});
					google.maps.event.addListener(marker, 'click', function(event) { onMarkerClick(event); });
					markers.push(marker);
				}
				*/
				var marker = new google.maps.Marker({
					position: getBlockMarker(markersJSON[i].x, markersJSON[i].y),
					map: map,
					icon: (markersJSON[i].email == masterUser) ? 'images/gmarker.png' : 'images/gmarker.png' //'images/dgmarker.png'
				});
				google.maps.event.addListener(marker, 'click', function(event) { onMarkerClick(event, "special"); });
				markers.push(marker);
			}
		}
		gMarkers.push.apply(gMarkers, markers);
		return markers;
	}
	
	var globalRedMarkersResponseTextCacheJSON = "";
	function ajaxAddRedMarkers(map, gMarkers, force) { //really they are green
		if(globalRedMarkersResponseTextCacheJSON!=""&&!force){
			setRedMarkers(map, gMarkers, globalRedMarkersResponseTextCacheJSON);
		}
		else{
			var jqxhr = $.ajax('ajax/get_markers.php?type=special'+"&_=<?php echo time(); ?>")
			.done(function() { 
				if (jqxhr.status == 200) {
					var markers = [];
					var markersJSON = JSON.parse(jqxhr.responseText);
					globalRedMarkersResponseTextCacheJSON = markersJSON;
					setRedMarkers(map, gMarkers, markersJSON);
				}
			})
			.fail(function() {
				//alert("error");
			})
			.always(function() {
				//alert("complete");
			});
		}
		
	}
	
	function setCoords(){
		total = 0;
		datax = {};
		datax.points = [];
		datax.strlatlongs = [];
		for(i=0; i<gzones.length; i++){
			if(gzones[i].ret.active){
				datax.points.push(gzones[i].ret.points);
				datax.strlatlongs.push(gzones[i].ret.strlatlong);
			}
		}
		str = JSON.stringify(datax);
		
		jQuery.ajax({
			dataType: "html",
			async: false,
			type: "POST",
			data: "data="+str+"&buydetails="+gsessiondetails,
			url: "?coords=1",
			success: function(data){
				
			}
		});
		
	}
	
	function onBuyLand() {
		calculateTotal(true); //second parameter to make the routine sync
		setCoords();
		
		var url = "";
		if (document.getElementById('buy-button').value == "Buy") {
			//url = "bidbuyland.php?type=buy&land="+blocksAvailableInDraggableRect+"&thumb="+document.getElementById('info-img').src+"&link="+globallink;
			url = "bidbuyland.php?type=buy&thumb="+document.getElementById('info-img').src+"&link="+globallink;
		}
		else {
			//url = "bidbuyland.php?type=bid&land="+blocksAvailableInDraggableRect+"&thumb="+document.getElementById('info-img').src+"&link="+globallink;
			url = "bidbuyland.php?type=bid&thumb="+document.getElementById('info-img').src+"&link="+globallink;
		}
		//window.open(url);
		jQuery.colorbox({iframe:true, width:"870px", height:"650px", href:url});
		//window.showModalDialog(url,0, "dialogWidth:700px; dialogHeight:450px; center:yes; resizable: no; status: no");
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



<script type="text/javascript" src="jquery-lightbox-0.5/js/jquery.lightbox-0.5.js"></script>
<link rel="stylesheet" type="text/css" href="jquery-lightbox-0.5/css/jquery.lightbox-0.5.css" media="screen" />

<script src="js/colorbox-master/jquery.colorbox.js"></script>
<link rel="stylesheet" type="text/css" href="js/colorbox-master/example1/colorbox.css" media="screen" />

<style>
.ui-dialog {
    width: 450px;
	top:60px;
}
#jquery-lightbox {
    z-index: 10000;
}
#jquery-overlay {
    z-index: 9000;
}
#cboxTitle{position:absolute; bottom:4px; left:0; text-align:center; width:90%; color:#000; font-size:11px;}
</style>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-39101024-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
	
  jQuery(".cboxiframe").colorbox({iframe:true, width:"80%", height:"80%"});
</script>
</head>

<body style="cursor: auto; margin:0px;">
<div id="fb-root"></div>

<table style='z-index: 1010; width:300px; height:100px; position:absolute; background:white; top:-10000px' id='loadinggrid' ><tr><td valign='middle' align='center'>Loading Data...</td></tr></table>
<div id="map_canvas" style='top:55px;'></div>
<div class="cpanelwnd ui-dialog ui-widget ui-widget-content ui-corner-all" style="outline: 0px none; z-index: 1008; position: absolute; border: 0px !important;" tabindex="-1" role="dialog" aria-labelledby="ui-id-1">
  <!--
  <div id="radioset" style="text-align: center;">
    <input type="radio" id="radio1" name="radio" onClick="showWorldView(this);return false;" checked="checked">
    <label for="radio1" class="change_radio_button_style">World View</label>
    <input type="radio" id="radio2" name="radio" onClick="showRegionalView(this);return false;">
    <label for="radio2" class="change_radio_button_style">Regional View</label>
    <input type="radio" id="radio3" name="radio" onClick="showCityView(this);return false;">
    <label for="radio3" class="change_radio_button_style">City View</label>
  </div>
  -->
      <div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix change_titlebar_style" style="border: solid 1px #578C0B !important; border-bottom: solid 1px #97CF48 !important; background: #97CF48 !important;"><span id="ui-id-1" class="ui-dialog-title" style="text-align:center; width: 100%;"><img src="images/cpanel-logo.png?_=<?php echo time();?>"></span></div>
      <div id="dialog" class="ui-dialog-content ui-widget-content change_dialog_style" style="width: auto; min-height: 52px; height: auto; border: solid 1px #578C0B !important; border-top: solid 1px #97CF48 !important; background: #97CF48 !important;" scrolltop="0" scrollleft="0">
		<div class="dialog_body">
			<?php include('mainTabs.php'); ?>
		</div>
	</div>
    </div>

<div id="header">
<style>
#table{
	width:100%;
	height:100%;
}
#shadowx{
	height:55px;
	-webkit-box-shadow: 0px 3px 0px rgba(255, 255, 255, 0.21);
	-moz-box-shadow:    0px 3px 0px rgba(255, 255, 255, 0.21);
	box-shadow:         0px 4px 0px rgba(255, 255, 255, 0.21);
	top:0px;
	position:absolute;
	width:100%;
	background:#24416d;
}
</style>
<div id='shadowx'>
	<table id='table' cellpadding=0 cellspacing=0>
		<tr>
			<td style='vertical-align:middle; width:250px; padding-left:30px;'>
				<a href='/'><img src='images/logo.png' style='border:0px'></a>
			</td>
			<td style='vertical-align:middle; display:none'>
			<div id="trends" style="position:absolute; top:0px; height:55px; width:100%;">
				<div class="inner" style="height:55px; width:100%; top:15px; font-size:14px;">
							<?php
								
								echo '<ul class="trendscontent">';
								$sql = "SELECT `a`.`x`, `a`.`y`, `b`.`title`, `b`.`detail` FROM `land` as  `a` left join `land_detail` as `b` on (`a`.`land_detail_id`=`b`.`id`) ORDER BY `a`.`id` DESC LIMIT 30";
								$results = dbQuery($sql);
								$t = count($results);
								$titles = array();
								$counter = 0;
								for($i=0; $i<$t; $i++){
									$tt = trim($results[$i]['title'])."~".trim($results[$i]['detail']);
									if(!in_array($tt, $titles)){
										$titles[] = $tt;
										echo '<li style="display:inline;">';
										echo '<img src="images/tinytrans.png">';
										echo '</li>';
										echo '<li style="display:inline; position:relative; margin-right:30px">';
										//echo "<table style='display:inline; float: left;' cellpadding=0 cellspcaing=0 ><tr>";
										//echo "<td style='padding-right:10px;'>";
										//echo '<img src="images/tinytrans.png">';
										//echo "</td>";
										//echo "<td style='vertical-align:middle; padding-right:30px;'>";
										echo '<a href=# onclick="gotoLoc('.$results[$i]['x'].', '.$results[$i]['y'].')" class="search_link" rel="nofollow" style="color:#b8ff00; text-decoration:none; top:-5px; position:relative">'.$results[$i]['title'].'</a>';
										echo '<em class="description" style="color:#b8ff00; text-decoration:none">'.$results[$i]['detail'].'</em>';
										//echo "</td>";
										//echo "</tr></table>";
										echo '</li>';
										$counter++;
										if($counter>15){
											break;
										}
									}
									
								}
							
								echo '</ul>';
							?>
				</div>
			</div>
			</td>
		</tr>
	</table>
</div>
</div>
<div class="trendtip">
	<div class="trendtip-content">
		<div align="center">Recently Purchased Land</div>
			<a class="trendtip-trend"></a>
			<div class="trendtip-why">
				<span class="trendtip-desc"></span>
				<span class="trendtip-source"><span></span></span>
			</div>
		</div>
	<div class="trendtip-pointer">&nbsp;</div>
</div>
<script src="twitmarquee/twitmarquee.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
var page={};
$(function() { new FrontPage().init(); });
//]]>

</script>
</body>
</html>
