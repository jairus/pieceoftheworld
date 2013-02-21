<!doctype html>
<html lang="us">
<head>
<meta charset="utf-8">
<title>PieceoftheWorld</title>
<link href="css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="js/jquery-1.8.3.min.js" type="text/javascript"></script>
<script src="js/jquery-ui-1.9.2.custom.min.js" type="text/javascript"></script>
<script src="http://maps.google.com/maps/api/js?sensor=true&libraries=geometry" type="text/javascript"></script>
<script src="js/gmapmarkers.js" type="text/javascript"></script>
<style>
body {
	font: 62.5% "Trebuchet MS", sans-serif;
	margin: 50px;
}
.gmarker {
  height: 16px;
  width: 16px;
  background-image: url('images/gmarker.png');
  cursor: pointer;
}
.rmarker {
  height: 16px;
  width: 16px;
  background-image: url('images/rmarker.png');
  cursor: pointer;
}
#map_canvas {
	display: block;
	position: absolute;
	height: auto;
	bottom: 0;
	top: 0;
	left: 0;
	right: 0;
	margin: 0px;
}
.demoHeaders {
	margin-top: 2em;
}
#dialog-link {
	padding: .4em 1em .4em 20px;
	text-decoration: none;
	position: relative;
}
#dialog-link span.ui-icon {
	margin: 0 5px 0 0;
	position: absolute;
	left: .2em;
	top: 50%;
	margin-top: -8px;
}
#icons {
	margin: 0;
	padding: 0;
}
#icons li {
	margin: 2px;
	position: relative;
	padding: 4px 0;
	cursor: pointer;
	float: left;
	list-style: none;
}
#icons span.ui-icon {
	float: left;
	margin: 0 4px;
}
.cpanelwnd {
	background: none !important;
	top: 15%;
	left: 60%;
}
.change_radio_button_style {
	border-top-left-radius: 10px !important;
	border-top-right-radius: 10px !important;
	border-bottom-left-radius: 0px !important;
	border-bottom-right-radius: 0px !important;
}
.change_titlebar_style {
	border-top-left-radius: 10px !important;
	border-top-right-radius: 10px !important;
	border-bottom-left-radius: 0px !important;
	border-bottom-right-radius: 0px !important;
	padding-bottom: 0px !important;
}
.change_dialog_style {
	border-top-left-radius: 0px !important;
	border-top-right-radius: 0px !important;
	border-bottom-left-radius: 10px !important;
	border-bottom-right-radius: 10px !important;
	padding: 7px !important;
	padding-top: 0px !important;
}
.dialog_body {
	border-top-left-radius: 0px !important;
	border-top-right-radius: 0px !important;
	border-bottom-left-radius: 10px !important;
	border-bottom-right-radius: 10px !important;
	background: #FFF !important;
	padding: 5px !important;
}
.change_tab_style {
	border: 0px !important;
	background: #FFF !important;
}
.change_tab_ul_style {
	border: 0px !important;
	background: #FFF !important;
}
.tab_body {
	background: #F5FFDA !important;
	border: 1px solid #D4FBDA !important;
	border-top-left-radius: 0px !important;
	border-top-right-radius: 0px !important;
	border-bottom-left-radius: 10px !important;
	border-bottom-right-radius: 10px !important;
	padding: 10px !important;
	padding-top: 0px !important;
	color: #1482b4 !important;
}
.tab_body h3 {
	font-size: 12px !important;
	font-weight: bold !important;
	color: #1482b4 !important;
	padding: 0px !important;
	padding-bottom: 3px !important;
	border-bottom: 1px solid #1482b4 !important;
}
.news {
}
.news ul {
	list-style: none !important;
	padding: 0px !important;
}
.news li {
	margin-bottom: -1px !important;
	margin-top: 4px !important;
	padding: 5px !important;
	background: #FFFFFF !important;
	border: 1px solid #8ABE43 !important;
	border-top-left-radius: 5px !important;
	border-top-right-radius: 5px !important;
	border-bottom-left-radius: 5px !important;
	border-bottom-right-radius: 5px !important;
}
.img {
	padding: 5px !important;
	background: #FFFFFF !important;
	border: 1px solid #8ABE43 !important;
	border-top-left-radius: 5px !important;
	border-top-right-radius: 5px !important;
	border-bottom-left-radius: 5px !important;
	border-bottom-right-radius: 5px !important;
}
</style>
<script type="text/javascript">
	$(function() {
	});

	// Try HTML5 geolocation
	var geoLoc;
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function(position) {
			geoLoc = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
		});
	}

	var TILE_SIZE = 629961;
	var SAVED_POSITION = new google.maps.LatLng(0,0);

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
	
	var rectangles = [];
	var map;
	var enableGeoLoc = true;

	function initialize(zoomVal, mapTypeIdVal) {
		if (map) {
			SAVED_POSITION = map.getCenter();
		}
		zoomVal = typeof zoomVal !== 'undefined' ? zoomVal : 4;
		mapTypeIdVal = typeof mapTypeIdVal !== 'undefined' ? mapTypeIdVal : google.maps.MapTypeId.TERRAIN;
		var mapOptions = {
			zoom: zoomVal,
			mapTypeId: mapTypeIdVal  // ROADMAP, SATELLITE, HYBRID, TERRAIN
		};
		map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
		if (window.geoLoc && enableGeoLoc == true) {
			enableGeoLoc = false;
			var image = 'images/loc.png';
			var beachMarker = new google.maps.Marker({
				position: window.geoLoc,
				map: map,
				icon: image
			});
			map.setCenter(window.geoLoc);
			updateControls(window.geoLoc);
		}
		else {
			map.setCenter(SAVED_POSITION);
			updateControls(SAVED_POSITION);
		}
		
		google.maps.event.addListener(map, 'click', function(event) {
			updateControls(event.latLng);
			var result = getBlockInfo(event.latLng);
		});
		
		google.maps.event.addListener(map, 'idle', function(event) {
			while (rectangles.length > 0) {
				var rectangle = rectangles.pop();
				rectangle.setMap(null);
				rectangle = null;
			}
			if (map.getZoom() >= 16) {
				// Insert this overlay map type as the first overlay map type at
				// position 0. Note that all overlay map types appear on top of
				// their parent base map.
				//map.overlayMapTypes.insertAt(0, new CoordMapType(new google.maps.Size(52.1,52.1)));
				drawBlocks(map);
			}
			else {
				// map.overlayMapTypes.clear();
			}
		});
	}

	google.maps.event.addDomListener(window, 'load', showWorldView);
	  
	function showWorldView(object) {
		initialize(4, google.maps.MapTypeId.HYBRID);
	}
	
	function getBlockLTRT(worldCoordinate) {
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
		var rectangle = new google.maps.Rectangle({
			strokeColor: "#0000FF",
			strokeOpacity: 0.5,
			strokeWeight: 1,
			fillColor: color,
			fillOpacity: opacity,
			map: window.map,
			bounds: new google.maps.LatLngBounds(lt, rb)
		});
		window.rectangles.push(rectangle);
	}
	
	function drawBlocks(map) {
		return;
		var bounds = map.getBounds();
		var ne = google.maps.geometry.spherical.computeOffset(bounds.getNorthEast(), 63.615*10, 45);
		var sw = google.maps.geometry.spherical.computeOffset(bounds.getSouthWest(), 63.615*10, 225);
		var blockRT = getBlockInfo(ne);
		var blockLB = getBlockInfo(sw);
		var vStart = blockRT[2].y;
		var vEnd = blockLB[2].y;
		var hStart = blockLB[2].x;
		var hEnd = blockRT[2].x;
		for (var cnt1 = vStart; cnt1 <= vEnd; cnt1++) {
			for (var cnt2 = hStart; cnt2 < hEnd; cnt2++) {
				var result = getBlockLTRT(new google.maps.Point(cnt2, cnt1));
				drawRect(result[0], result[1], "#FF0000", 0);
			}
		}
	}
	
	function updateControls(inLatLng) {
		document.getElementById('lat').value = inLatLng.lat();
		document.getElementById('lng').value = inLatLng.lng();
	}
	
	function gotoLatLng() {
		map.setCenter(new google.maps.LatLng(document.getElementById('lat').value,document.getElementById('lng').value));
		map.setZoom(17);
	}

	function addtodatabaseLatLng() {
		var LatLng = new google.maps.LatLng(document.getElementById('lat').value,document.getElementById('lng').value);
		if (confirm("Click OK to add " + LatLng + " to your database.")) { 
			var result = getBlockInfo(LatLng);
			var o = new Object();
			o.x = result[2].x;
			o.y = result[2].y;
			window.returnValue = o;
			window.close();
		}
	}
</script>
</head>

<body style="cursor: auto; background-color: black;">
<div id="map_canvas" style="width: 630px; height: 430px; left: 35px; top: 35px;"></div>
<div id="controls_canvas" style="position: absolute; left: 35px; top: 470px; color: white;"><center>Latitude: <input id="lat" type="text"> Longitude: <input id="lng" type="text"> <input id="go" type="button" value="Go" onClick="gotoLatLng()"> <input id="add" type="button" value="Add to database" onClick="addtodatabaseLatLng()"></center></div>
</body>
</html>
