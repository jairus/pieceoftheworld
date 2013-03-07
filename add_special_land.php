<?php
ob_start();
?><!doctype html>
<html lang="us">
<head>
<meta charset="utf-8">
<title>PieceoftheWorld</title>
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
	/*
	$conOptions = GetGlobalConnectionOptions();
	$con = mysql_connect($conOptions['server'], $conOptions['username'], $conOptions['password']);
	if (!$con) { die('Database connection error.'); }
	mysql_select_db($conOptions['database'], $con);
	
	$sql = "SELECT * FROM settings WHERE 1";
	
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) {
		echo 'var '.$row["name"].' = "'.$row["value"].'";';
	}
	mysql_close($con);
	*/
	
	$sql = "SELECT * FROM settings WHERE 1";
	$result = dbQuery($sql, $_dblink);
	foreach($result as $row){
		echo 'var '.$row["name"].' = "'.$row["value"].'";';
	}
	echo "var masterUser = '".$masterUser."';";
?>
	// Try HTML5 geolocation
	var geoLoc;
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function(position) {
			geoLoc = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
		});
	}

	var TILE_SIZE = 629961;
	var SAVED_POSITION = new google.maps.LatLng(37.09024, -95.71289); //new google.maps.LatLng(31.41576, 74.26804);

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
	var markers = [];
	var markers_loaded = false;
	var map = null;
	var geocoder;
	var searchMarker;
	var enableGeoLoc = true;
	var draggableRect = null;
	var blocksAvailableInDraggableRect = "";
	var lastEvent;

	function initialize(zoomVal, mapTypeIdVal) {
		if (map) {
			SAVED_POSITION = map.getCenter();
		}
		zoomVal = typeof zoomVal !== 'undefined' ? zoomVal : ZOOM_LEVEL_WORLD;
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
		if (window.geoLoc && enableGeoLoc == true) {
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

		// Add markers
		markers = ajaxAddMarkers(map);
		
		google.maps.event.addListener(map, 'click', function(event) { onClick(event); });
		google.maps.event.addListener(map, 'idle', function(event) { onIdle(false); });
		google.maps.event.addListener(map, 'zoom_changed', function() { onZoomChanged(); });
	}

	google.maps.event.addDomListener(window, 'load', showWorldView);

	$(function() {
		$('.cpanelwnd').draggable();
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
				map.setCenter(location);
				map.setZoom(ZOOM_LEVEL_CITY);
				map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
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
	}

	function showRegionalView(object) {
		map.setZoom(ZOOM_LEVEL_REGION);
		map.setMapTypeId(google.maps.MapTypeId.HYBRID);
	}

	function showCityView(object) {
		map.setZoom(ZOOM_LEVEL_CITY);
		map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
	}
	
	function onClick(event) {
		updatePopupWindowTabInfo(event.latLng);
	}

	function onMarkerClick(event) {
		$("#tabs").tabs("select",1);
		updatePopupWindowTabInfo(event.latLng);
	}

	var boundsPrev;
	function onDraggableRectangleChanged() {
		if (lastEvent.getTime() + 500 <= new Date().getTime()) {
			var bounds = draggableRect.getBounds();
			
			if (bounds != boundsPrev) {
				boundsPrev = bounds;
			}
			else {
				return;
			}

			var LtLgNE = bounds.getNorthEast();
			var LtLgSW = bounds.getSouthWest();
		
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
			
			// Because we need TopLeft points to set new rectangle so lets move NE block 1 step towards E and SW 1 step towards W
			WcNE.x += 1;
			WcSW.y += 1;
			
			//var WcNELTRB = getBlockLTRB(WcNE);
			//drawRect(WcNELTRB[0], WcNELTRB[1], "#555555", 1);
			//var WcSWLTRB = getBlockLTRB(WcSW);
			//drawRect(WcSWLTRB[0], WcSWLTRB[1], "#555555", 1);

			var BlSW = getBlockLTRB(WcSW);
			var BlNE = getBlockLTRB(WcNE);

			if (draggableRect != null) {
				draggableRect.setMap(null);
				draggableRect = null;
			}
			blocksAvailableInDraggableRect = "";

			var newBounds = new google.maps.LatLngBounds(
				new google.maps.LatLng(BlSW[0].lat(),BlSW[0].lng()),
				new google.maps.LatLng(BlNE[0].lat(),BlNE[0].lng())
			);
			
			draggableRect = new google.maps.Rectangle({
				bounds: newBounds,
				editable: true
			});
			draggableRect.setMap(map);

			google.maps.event.addListener(draggableRect, 'bounds_changed', scheduleDelayedCallback);
			
			/*
			draggableRect.setBounds(new google.maps.LatLngBounds(
				new google.maps.LatLng(BlSW[0].lat(),BlSW[0].lng()),
				new google.maps.LatLng(BlNE[0].lat(),BlNE[0].lng())
			));
			*/
			
			var WcCenter = new google.maps.Point(BlNE[2].x-((BlNE[2].x-BlSW[2].x)/2),BlSW[2].y-((BlSW[2].y-BlNE[2].y)/2));
			var LatLngCenter = getBlockMarker(WcCenter.x, WcCenter.y);

			if (BlSW[2].y < BlNE[2].y) {
				blocksAvailableInDraggableRect = BlSW[2].x+"-"+BlNE[2].y-1;
			}
			else {
				blocksAvailableInDraggableRect = BlSW[2].x+"-"+BlNE[2].y+"_"+(BlNE[2].x-1)+"-"+(BlSW[2].y-1);
			}
		}
//		google.maps.event.addListener(draggableRect, 'bounds_changed', scheduleDelayedCallback);
	}

	function scheduleDelayedCallback() {
//		google.maps.event.addListener(draggableRect, 'bounds_changed', null);
		lastEvent = new Date();
		setTimeout(onDraggableRectangleChanged, 500);
	}
	
	function onRectangleClick(event) {
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
		draggableRect = new google.maps.Rectangle({
			bounds: bounds,
			editable: true
		});
		draggableRect.setMap(map);
		blocksAvailableInDraggableRect = block[2].x+"-"+block[2].y;

		google.maps.event.addListener(draggableRect, 'bounds_changed', scheduleDelayedCallback);

		$("#tabs").tabs("select",4);
	}

	function onColoredRectangleClick(event) {
		if (draggableRect != null) {
			draggableRect.setMap(null);
			draggableRect = null;
		}
		blocksAvailableInDraggableRect = "";

		//$("#tabs").tabs("select",1);
		updatePopupWindowTabInfo(event.latLng);

		var projection = new MercatorProjection();
		var worldCoordinate = projection.fromLatLngToPoint(event.latLng);
		worldCoordinate.x = Math.floor(worldCoordinate.x);
		worldCoordinate.y = Math.floor(worldCoordinate.y);
		var block = getBlockLTRB(worldCoordinate);
		var bounds = new google.maps.LatLngBounds(
			new google.maps.LatLng(block[0].lat(),block[0].lng()),
			new google.maps.LatLng(block[1].lat(),block[1].lng())
		);
		draggableRect = new google.maps.Rectangle({
			bounds: bounds,
			editable: true
		});
		draggableRect.setMap(map);
		blocksAvailableInDraggableRect = block[2].x+"-"+block[2].y;
		
		google.maps.event.addListener(draggableRect, 'bounds_changed', scheduleDelayedCallback);

		$("#tabs").tabs("select",4);
	}
	
	function onIdle(manualCall) {		
		// Remove rectangles if exist
		while (rectangles.length > 0) { rectangles.pop().setMap(null); }
		if (map.getZoom() >= ZOOM_LEVEL_CITY) {
			if (markers_loaded == true) {
				markers_loaded = false;
				while (markers.length > 0) {
					markers.pop().setMap(null);
				}
			}
			if (getCookie("config_showgrid") != "false") {
				drawBlocks(map);
			}
		}
		else {
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
			if (markers_loaded == false) { markers = ajaxAddMarkers(map); }
		}
	}

	function onZoomChanged() {
		// Select tab according to current zoom level
		if (map.getZoom() < ZOOM_LEVEL_REGION) {
			map.setMapTypeId(google.maps.MapTypeId.HYBRID);
			$('input:radio[name=radio]')[0].checked = true;
			$("#radio1").button("refresh");
			if (draggableRect != null) {
				draggableRect.setMap(null);
				draggableRect = null;
				blocksAvailableInDraggableRect = "";
			}
		}
		else if (map.getZoom() < ZOOM_LEVEL_CITY) {
			map.setMapTypeId(google.maps.MapTypeId.HYBRID);
			$('input:radio[name=radio]')[1].checked = true;
			$("#radio2").button("refresh");
			if (draggableRect != null) {
				draggableRect.setMap(null);
				draggableRect = null;
				blocksAvailableInDraggableRect = "";
			}
		}
		else {
			map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
			$('input:radio[name=radio]')[2].checked = true;
			$("#radio3").button("refresh");
		}
	}
	
	function getBlockLTRB(worldCoordinate) {
		var projection = new MercatorProjection();
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

	function getBlockMarker(x, y) {
		var worldCoordinate = new google.maps.Point(x, y);
		worldCoordinate.x -= 0.5;
		worldCoordinate.y -= 0.5;
		var projection = new MercatorProjection();
		return projection.fromPointToLatLng(worldCoordinate);
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
	
	function drawRect(lt, rb, color, opacity) {
		if (getCookie("config_showownedland") == "false" && color == fillColorAcquiredPlot) {
			opacity = 0;
		}
		if (getCookie("config_showimportantplaces") == "false" && color == fillColorSpecialArea) {
			opacity = 0;
		}
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
		google.maps.event.addListener(rectangle, 'click', function(event) { (opacity == 0) ? onRectangleClick(event) :  onColoredRectangleClick(event); });
		window.rectangles.push(rectangle);
	}
	
	function drawBlocks(map) {
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
		var returnText = ajaxGetMarker(map, hStart, vStart, hEnd, vEnd);
		//alert(JSON.stringify(returnText));
		var markersJSON = JSON.parse(returnText);
		var color;
		var opacity;
		var email;
		for (var cnt1 = vStart; cnt1 <= vEnd; cnt1++) {
			for (var cnt2 = hStart; cnt2 <= hEnd; cnt2++) {
				color = "";
				opacity = 0;
				var result = getBlockLTRB(new google.maps.Point(cnt2, cnt1));
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
				if (config_showownedland == "false" && color != fillColorSpecialArea && masterUser != email && user_email != email) { opacity = 0; }
				if (config_showimportantplaces == "false" && color == fillColorSpecialArea) { opacity = 0; }
				if (config_showownland == "false" && user_email == email) { opacity = 0; }
				drawRect(result[0], result[1], color, opacity);
			}
		}
	}
	
	function ajaxGetMarker(map, x1, y1, x2, y2) {
		var markersJSON = null;
		$.ajax({
			url:'ajax/get_markers.php?x1='+x1+'&y1='+y1+'&x2='+x2+'&y2='+y2,
			dataType:'html',
			async:false,
			success:function(data, textStatus, jqXHR){
				markersJSON = data;
			}
		});
		return markersJSON;
	}
	
	function ajaxAddMarkers(map) {
		markers_loaded = true;
		var markers = [];
		ajaxAddGreenMarkers(map, markers);
		ajaxAddRedMarkers(map, markers);
		//markers = ajaxAddGreenMarkers(map);
		//markers.push.apply(markers, ajaxAddRedMarkers(map));
		return markers;
	}

	function ajaxAddGreenMarkers(map, gMarkers) {
		var jqxhr = $.ajax('ajax/get_markers.php')
		.done(function() { 
			if (jqxhr.status == 200) {
				var config_showownland = getCookie("config_showownland");
				var config_showownedland = getCookie("config_showownedland");
				var user_email = getCookie("user_email");
				var markers = [];
				var markersJSON = JSON.parse(jqxhr.responseText);
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
								icon: 'images/gmarker.png'
								//icon: (markersJSON[i].land_special_id == null) ? 'images/gmarker.png' : 'images/rmarker.png'
							});
							google.maps.event.addListener(marker, 'click', function(event) { onMarkerClick(event); });
							markers.push(marker);
						}
					}
				}
				gMarkers.push.apply(gMarkers, markers);
			}
		})
		.fail(function() {
			//alert("error");
		})
		.always(function() {
			//alert("complete");
		});
	}

	function ajaxGetRedMarkerCoordinates(land_special_id) {
		return 0;
		var marker;
		$.ajax({
			url:'ajax/get_markers.php?land_special_id='+land_special_id,
			dataType:'html',
			async:false,
			success:function(data, textStatus, jqXHR){
				var markerJSON = JSON.parse(data);
				marker = new google.maps.Point(markerJSON[0].x, markerJSON[0].y);
			}
		});
		return marker;
	}
	
	function ajaxAddRedMarkers(map, gMarkers) {
		return 0;
		var jqxhr = $.ajax('ajax/get_markers.php?type=special')
		.done(function() { 
			if (jqxhr.status == 200) {
				var config_showownland = getCookie("config_showownland");
				var config_showownedland = getCookie("config_showownedland");
				var config_showimportantplaces = getCookie("config_showimportantplaces");
				var user_email = getCookie("user_email");
				var markers = [];
				var markersJSON = JSON.parse(jqxhr.responseText);
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
					}
				}
				gMarkers.push.apply(gMarkers, markers);
			}
		})
		.fail(function() {
			//alert("error");
		})
		.always(function() {
			//alert("complete");
		});
		return markers;
	}

	function onBuySpecialLand() {
//		document.getElementById('addSpecialAreaForm').action = "addspecialland.php?land="+blocksAvailableInDraggableRect;
		if (blocksAvailableInDraggableRect == null || blocksAvailableInDraggableRect == "") {
			alert('Please select a land first.');
			return false;
		}
		document.getElementById('land').value = blocksAvailableInDraggableRect;
		if (document.getElementById('title').value == "") {
			alert('Please provide a Title for special land.');
			return false;
		}
		document.getElementById('addSpecialAreaForm').submit();
		//document.addSpecialAreaForm.submit();
		//window.open(url);
		return true;
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
<?php
function getEmailAndPassword($error) {
	?>
	<body>
	<div style="height:50%; margin-top:100px; font-family:Arial;" align="center" valign="middle">
	<h1>Add Special Land</h1>
	<table border="1px" style="border-color:black;">
	<tr>
	<td style="text-align:center; background-color:#CC0000; color: white;">
		<strong>Login</strong>
	</td>
	</tr>
	<tr>
	<td>
		<form action="add_special_land.php">
			<table>
				<tr>
					<td>Email</td>
					<td><input type="text" name="email" value=""></td>
				</tr>
				<tr>
					<td>Password</td>
					<td><input type="text" name="pwd" value=""></td>
				</tr>
				<tr>
					<td colspan="2"><center><input type="submit" name="Submit" value="Submit"></center></td>
				</tr>
			</table>
		</form>
	</td>
	</tr>
	</table>
	<?php
	if ($error != "") {
		die("<br><font color=red><strong>Error: </strong>".$error."</font>");
	}
	?>
	</div>
	</body>
	</html>
	<?php
}
?>
<?php
$email = @$_GET['email'];
$pwd = @$_GET['pwd'];
if ($email == "" || $pwd == "") {
	getEmailAndPassword("Please provide a username and password");
	exit();
}
require_once 'ajax/global.php';
$conOptions = GetGlobalConnectionOptions();
$con = mysql_connect($conOptions['server'], $conOptions['username'], $conOptions['password']);
if (!$con) { die('Error: Connection with database failed'); }
mysql_select_db($conOptions['database'], $con);
$sql = "SELECT * FROM user WHERE email='".$email."' AND password='".$pwd."'";
$result = mysql_query($sql);
if (!$result) {
	getEmailAndPassword("Invalid query: ".mysql_error());
}
$row = mysql_fetch_array($result);
if (!$row || ($row && $row[9] != 1)) {
	getEmailAndPassword("Only administrator is allowed to add special lands");
}
?>
<body style="cursor: auto;">
<div id="map_canvas"></div>
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
	<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix change_titlebar_style" style="border: solid 1px #578C0B !important; border-bottom: solid 1px #97CF48 !important; background: #97CF48 !important;"><span id="ui-id-1" class="ui-dialog-title" style="text-align:center; width: 100%;"><img src="images/cpanel-logo.png"></span></div>
		<div id="dialog" class="ui-dialog-content ui-widget-content change_dialog_style" style="width: auto; min-height: 52px; height: auto; border: solid 1px #578C0B !important; border-top: solid 1px #97CF48 !important; background: #97CF48 !important;" scrolltop="0" scrollleft="0">
			<div class="dialog_body">
				<div id="buy-special_land" class="tab_body">
					<h3>Search Google Map</h3>
					<strong>Enter a place</strong>
					<div style="width: 250px; overflow: hidden;">
						<input type="text" id="search_enteraplace" name="search_enteraplace_name" style="width: 97%;">
					</div>
					<form id="addSpecialAreaForm" name="addSpecialAreaFormName" enctype="multipart/form-data" method="post" action="addspecialland.php" target="_blank">
					<input type="hidden" id="land" name="land_name" value="">
					<h3>Add Special Land</h3>
					<strong>Title<font color=red>*</font>&nbsp;</strong>
					<div style="width: 250px; overflow: hidden;">
						<input type="text" id="title" name="title_name" maxlength="50" style="width: 97%;">
					</div>
					<strong style="vertical-align:top;">Detail&nbsp;</strong>
					<div style="width: 250px; overflow: hidden;">
						<textarea id="detail" name="detail_name" maxlength="150" style="width: 97%; height:75px;"></textarea>
					</div>
					<strong>Picture</strong>
					<div style="width: 250px; overflow: hidden;">
						<input type="file" id="picture" name="picture_name" style="width: 97%;">
					</div>
					<br>
					<div style="width: 250px; overflow: hidden;">
						<center><input type="button" id="button" name="button_name" value="Add Special Area" onclick="onBuySpecialLand();" style="width: 50%; height:30px;"></center>
					</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
