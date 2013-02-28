<?php session_start(); ?>
<!doctype html>
<html lang="us">
<head>
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
	echo "var masterUser = '".$masterUser."';";
?>
<?php if (!isset($_GET['skip'])&&(isset($_SESSION['showTutorial']) == false || $_SESSION['showTutorial'] != 1)) { ?>
		var showTutorial = getCookie("showTutorial");
		if (showTutorial !== "false") {
			window.location="tutorial.php";
			//window.showModalDialog("tutorial.html",0, "dialogWidth:700px; dialogHeight:500px; center:yes; resizable: no; status: no");
		}
<?php } ?>
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

		// Add markers
		//markers = ajaxAddMarkers(map);
		
		self.setInterval(function(){ajaxAddMarkers(map, true);}, 5*60*1000);
		
		google.maps.event.addListener(map, 'click', function(event) { onClick(event); });
		google.maps.event.addListener(map, 'idle', function(event) { onIdle(false); });
		google.maps.event.addListener(map, 'zoom_changed', function() { onZoomChanged(); });
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
		$('.cpanelwnd').css({left: xleft, top: '30px'});
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

	function onMarkerClick(event, type) {
		//onColoredRectangleClick(event);
		$("#tabs").tabs("select",0);//$("#tabs").tabs("select",1);
		if (map.getZoom() >= ZOOM_LEVEL_CITY) {
			//document.getElementById('buy-button').disabled = false;
		}
		
		//jairus
		jQuery("#clicktozoom").show();
		jQuery("#clicktozoom").click(function () {
			var loc = new google.maps.LatLng(event.latLng.lat(),event.latLng.lng());
			consoleX(event.latLng.lat()+" - "+event.latLng.lng());
			map.setZoom(ZOOM_LEVEL_CITY);
			map.setCenter(loc);
			jQuery("#clicktozoom").hide();
			onColoredRectangleClick(event);
			//onRectangleClick(event) ;
			//scheduleDelayedCallback();
			
		});
		
		
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
		
		updatePopupWindowTabInfo(event.latLng);
		
		consoleX(type);
		if(type=='special'){		
			// Acquired Special Area or Special Area
			$.ajax({
				url:'ajax/get_minmaxareacoordinates.php?x='+WcNE.x+'&y='+WcSW.y,
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
			consoleX(bounds);
			if(!bounds){
				var bounds = draggableRect.getBounds();
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
			//alert(rectangles.length);
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
			
			consoleX("typeFirstBlock = "+typeFirstBlock);
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
					url:'ajax/get_minmaxareacoordinates.php?x='+matrix[0][0][0]+'&y='+matrix[0][0][1],
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
				document.getElementById('info-img').src = 'images/place_holder.png';
			}
			document.getElementById('info-latitude').innerHTML = LatLngCenter.lat().toFixed(5);
			document.getElementById('info-longitude').innerHTML = LatLngCenter.lng().toFixed(5);
			
			if (BlSW[2].y < BlNE[2].y) {
				blocksAvailableInDraggableRect = BlSW[2].x+"-"+BlNE[2].y-1;
			}
			else {
				blocksAvailableInDraggableRect = BlSW[2].x+"-"+BlNE[2].y+"_"+(BlNE[2].x-1)+"-"+(BlSW[2].y-1);
			}
			if(!(typeFirstBlock == 3 || typeFirstBlock == 4)){
				numblocks = (BlNE[2].x - BlSW[2].x) * (BlSW[2].y - BlNE[2].y);
				if(numblocks > 0){
					document.getElementById('info-detail').innerHTML = 'Your description here.';
					price = (numblocks*9.90);
					price = price.toFixed(2);
					document.getElementById('info-detail').innerHTML  =  document.getElementById('info-detail').innerHTML + '<br /><br />Price: $'+price;
				}
			}
		}
	}

	function scheduleDelayedCallback() {
		consoleX("scheduleDelayedCallback");
		lastEvent = new Date();
		setTimeout(onDraggableRectangleChanged, 500);
	}
	
	function onRectangleClick(event) {
	
		
		if (draggableRect != null) {
			draggableRect.setMap(null);
			draggableRect = null;
		}
		blocksAvailableInDraggableRect = "";

		//updatePopupWindowTabInfo(event.latLng);
				
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

		$("#tabs").tabs("select",0);//$("#tabs").tabs("select",4);
		
		scheduleDelayedCallback();	// To update rect according to selected area
	
		updatePopupWindowTabInfo(event.latLng);
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

		$("#tabs").tabs("select",0);//$("#tabs").tabs("select",4);

		
		consoleX("Here");
		scheduleDelayedCallback();	// To update rect according to selected area
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
		//consoleX(ZOOM_LEVEL_CITY+" - "+map.getZoom());
		if (map.getZoom() >= 17) {
			if (markers_loaded == true) {
				markers_loaded = false;
				while (markers.length > 0) {
					markers.pop().setMap(null);
				}
			}
			if (getCookie("config_showgrid") != "false") {
				drawBlocks(map);
				blocksHidden = false;
			}
		}
		else {
			if(!blocksHidden){
				var i = 0;
				process = function(){
					for(; i<window.rectangles.length; i++){
						//window.rectangles[i].setOptions({strokeColor: "#ff0000"});
						window.rectangles[i].setOptions({strokeOpacity: 0});
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
			if (markers_loaded == false) { markers = ajaxAddMarkers(map); }
		}
	}
	
	function onZoomChanged() {
		if (disableZoomChange == true) {
			map.setMapTypeId(google.maps.MapTypeId.HYBRID);
			disableZoomChange = false;
			disableOnIdle = true;
			return;
		}
		// Select tab according to current zoom level
		if (map.getZoom() < ZOOM_LEVEL_REGION) {
			map.setMapTypeId(google.maps.MapTypeId.HYBRID);
			//$('input:radio[name=radio]')[0].checked = true;
			//$("#radio1").button("refresh");
			if (draggableRect != null) {
				draggableRect.setMap(null);
				draggableRect = null;
				blocksAvailableInDraggableRect = "";
			}
		}
		else if (map.getZoom() < ZOOM_LEVEL_CITY) {
			map.setMapTypeId(google.maps.MapTypeId.HYBRID);
			//$('input:radio[name=radio]')[1].checked = true;
			//$("#radio2").button("refresh");
			if (draggableRect != null) {
				draggableRect.setMap(null);
				draggableRect = null;
				blocksAvailableInDraggableRect = "";
			}
		}
		else {
			//map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
			//$('input:radio[name=radio]')[2].checked = true;
			//$("#radio3").button("refresh");
		}
		if (map.getZoom() < ZOOM_LEVEL_CITY) {
			document.getElementById('buy-button').value = 'Buy';
			//document.getElementById('buy-button').disabled = true;
			showPopupWindowTabInfo(false);
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
		google.maps.event.addListener(rectangle, 'click', function(event) { (opacity == 0) ? onRectangleClick(event) :  onColoredRectangleClick(event); });
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
			consoleX(hStart + ">=" + returnTextCache[i].hStart +"&&"+ hEnd +"<="+ returnTextCache[i].hEnd +"&&"+ vStart +">="+ returnTextCache[i].vStart +"&&"+ vEnd +"<="+ returnTextCache[i].vEnd)
			if(
				hStart >= returnTextCache[i].hStart && hEnd <= returnTextCache[i].hEnd &&
				vStart >= returnTextCache[i].vStart && vEnd <= returnTextCache[i].vEnd
			){
				consoleX("in cache");
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
		
		
		consoleX("show loading");
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
							if (config_showownedland == "false" && color != fillColorSpecialArea && masterUser != email && user_email != email) { opacity = 0; }
							if (config_showimportantplaces == "false" && color == fillColorSpecialArea) { opacity = 0; }
							if (config_showownland == "false" && user_email == email) { opacity = 0; }
						
						
							<?php if($_GET['norec']){ echo "//";};?>drawRect(result[0], result[1], color, opacity);
							window.rectanglesxy[window.rectangles.length-1]= result[0]+"-"+result[1];
						}
						else{
							ndexx = window.rectanglesxy.indexOf(result[0]+"-"+result[1]);
							//window.rectanglesxy[ndexx].setOptions();
							//window.rectangles[ndexx].setOptions({strokeColor: "#ff0000"});
							window.rectangles[ndexx].setOptions({strokeOpacity: 0.9});
						}
						if (cnt2 + 1 <= hEnd && cnt2 % 20 == 0) {
							//consoleX("cnt2 setTimeout "+cnt2);
							setTimeout(process, 5);
						}
					}
				};
				process();
				
				/*
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
				*/
				if (cnt1 + 1 <= vEnd && cnt1 % 20 == 0) {
					//consoleX("cnt1 setTimeout "+cnt1);
					setTimeout(process0, 5);
				}
				
				//consoleX(cnt1 +" "+ vEnd +" - "+ cnt2 +" "+ hEnd);
				
				if(cnt1 >= vEnd && cnt2 >= hEnd ){
					jQuery("#loadinggrid").css('top', -10000);
					consoleX("hide loading");
				}
			}
		}
		<?php if($_GET['noprocess']){ echo "//";};?>process0();
		consoleX("process "+(new Date()).getTime());
		//jQuery("#loadinggrid").hide();
		
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
	}
	function updatePopupWindowTabInfo(inLatLng) {
		showPopupWindowTabInfo(true);
		document.getElementById('info-latitude').innerHTML = inLatLng.lat().toFixed(5);
		document.getElementById('info-longitude').innerHTML = inLatLng.lng().toFixed(5);
		
		//document.getElementById('buy-latitude').innerHTML = inLatLng.lat().toFixed(5);
		//document.getElementById('buy-longitude').innerHTML = inLatLng.lng().toFixed(5);
		
		var blockInfo = getBlockInfo(inLatLng);
		var returnText = ajaxGetMarker(map, blockInfo[2].x, blockInfo[2].y, blockInfo[2].x, blockInfo[2].y);
		var markerJSON = JSON.parse(returnText);
		document.getElementById('info-land_owner_container').style.display="none";
		document.getElementById('info-img').src = "images/place_holder.png";
		if (returnText != '[[]]') {
			document.getElementById('info-title').innerHTML = markerJSON[0].title;
			document.getElementById('info-detail').innerHTML = markerJSON[0].detail;
			
			if(markerJSON[0].land_special_id){
				price = 499;
				document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $'+price;
			}
			
			if(markerJSON[0].land_owner){
				
				document.getElementById('info-land_owner').innerHTML = markerJSON[0].land_owner;
				document.getElementById('info-land_owner_container').style.display="";
			}
			ajaxExtractLandPicture(markerJSON[0].id);
			//document.getElementById('info-img').src = "images/thumbs/land_id_"+markerJSON[0].id;
			if(markerJSON[0].thumb_url){
				document.getElementById('info-img').src = markerJSON[0].thumb_url;
				jQuery("#info-lightbox").attr("href", markerJSON[0].img_url );
				xtitle = "";
				if(markerJSON[0].land_owner){
					xtitle = "Land Owner: "+markerJSON[0].land_owner+"<br />";
				}
				xtitle += markerJSON[0].detail;
				jQuery("#info-lightbox").attr("title", xtitle );
				jQuery("#info-lightbox").lightBox({fixedNavigation:true});
			}
			else{
				document.getElementById('info-img').src = "images/thumbs/land_id_"+markerJSON[0].id+"?_="+((new Date()).getTime());
				jQuery("#info-lightbox").attr("href", "images/thumbs/land_id_"+markerJSON[0].id+"?_="+((new Date()).getTime()) );
				xtitle = "";
				if(markerJSON[0].land_owner){
					xtitle = "Land Owner: "+markerJSON[0].land_owner+"<br />";
				}
				xtitle += markerJSON[0].detail;
				jQuery("#info-lightbox").attr("title", xtitle );
				jQuery("#info-lightbox").lightBox({fixedNavigation:true});
			}
			if (markerJSON[0].email == masterUser) {
				document.getElementById('buy-button').value = "Buy";
			}
			else {
				document.getElementById('buy-button').value = "Bid";
			}
			
			//document.getElementById('buy-img').src = "images/thumbs/land_id_"+markerJSON[0].id;
		}
		else {
			//alert(blocksAvailableInDraggableRect);
			document.getElementById('info-title').innerHTML = 'Your title here.';
			
			
			document.getElementById('info-detail').innerHTML = 'Your description here.';
			if(numblocks > 0){
				price = (numblocks*9.90);
				price = price.toFixed(2);
				document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $'+price;
			}

			document.getElementById('info-img').src = 'images/place_holder.png';
			document.getElementById('buy-button').value = "Buy";

			//document.getElementById('buy-img').src = 'images/place_holder.png';
		}
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
			url:'ajax/extract_land_picture.php?land_id='+land_id,
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
				url:'ajax/get_markers.php?x1='+x1+'&y1='+y1+'&x2='+x2+'&y2='+y2+'&multi=1',
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
				url:'ajax/get_markers.php?x1='+x1+'&y1='+y1+'&x2='+x2+'&y2='+y2,
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
					google.maps.event.addListener(marker, 'click', function(event) { onMarkerClick(event, 'regular'); consoleX(this.position); });
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
			var jqxhr = $.ajax('ajax/get_markers.php')
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
					icon: (markersJSON[i].email == masterUser) ? 'images/gmarker.png' : 'images/dgmarker.png'
				});
				google.maps.event.addListener(marker, 'click', function(event) { onMarkerClick(event, "special"); });
				markers.push(marker);
			}
		}
		gMarkers.push.apply(gMarkers, markers);
		return markers;
	}
	
	var globalRedMarkersResponseTextCacheJSON = "";
	function ajaxAddRedMarkers(map, gMarkers, force) {
		if(globalRedMarkersResponseTextCacheJSON!=""&&!force){
			setRedMarkers(map, gMarkers, globalRedMarkersResponseTextCacheJSON);
		}
		else{
			var jqxhr = $.ajax('ajax/get_markers.php?type=special')
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
	
	function onBuyLand() {
		var url = "";
		if (document.getElementById('buy-button').value == "Buy") {
			url = "bidbuyland.php?type=buy&land="+blocksAvailableInDraggableRect+"&thumb="+document.getElementById('info-img').src;
		}
		else {
			url = "bidbuyland.php?type=bid&land="+blocksAvailableInDraggableRect+"&thumb="+document.getElementById('info-img').src;
		}
		//window.open(url);
		window.showModalDialog(url,0, "dialogWidth:700px; dialogHeight:450px; center:yes; resizable: no; status: no");
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
<style>
.ui-dialog {
    width: 450px;
}
#jquery-lightbox {
    z-index: 10000;
}
#jquery-overlay {
    z-index: 9000;
}
</style>
</head>

<body style="cursor: auto; margin:0px;">
<table style='z-index: 1010; width:300px; height:100px; position:absolute; background:white; top:-10000px' id='loadinggrid' ><tr><td valign='middle' align='center'>Loading Data...</td></tr></table>
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
		<div id="tabs" class="change_tab_style">
			<ul class="change_tab_ul_style">
				<!--
				<li><a style="padding: 2px !important;" href="#news">News</a></li>
				-->
				<li><a style="padding: 2px !important;" href="#info">Info</a></li>
				<li><a style="padding: 2px !important;" href="#search">Search</a></li>
				<!--
				<li><a style="padding: 2px !important;" href="#us">US</a></li>
				-->
				<!--
				<li><a style="padding: 2px !important;" href="#buy"><img src="images/cart.png" width="11" height="11" border="0"></a></li>
				-->
				<!--
				<li><a style="padding: 2px !important;" href="#configuration"><img src="images/compile.png" width="11" height="11" border="0"></a></li>
				-->
				<li><a style="padding: 2px !important;" href="#configuration">Settings</a></li>
				<!--
				<li><a style="padding: 2px !important;" href="#help"><img src="images/question.png" width="11" height="11" border="0"></a></li>
				-->
				<li><a style="padding: 2px !important;" href="#help">About</a></li>
			</ul>
		<!--
		<div id="news" class="tab_body news">
          <h3>News</h3>
          <ul id="news-ul" class="jcarousel jcarousel-skin-tango">
            <li><span id="news-1-text" style="float: left; width: 200px;"></span><span><img id="news-1-img" src="images/news_img_1.png" width="32" height="32" border="0"></span></li>
            <li><span id="news-1-text" style="float: left; width: 200px;"></span><span><img id="news-1-img" src="images/news_img_1.png" width="32" height="32" border="0"></span></li>
            <li><span id="news-1-text" style="float: left; width: 200px;"></span><span><img id="news-1-img" src="images/news_img_1.png" width="32" height="32" border="0"></span></li>
          </ul>
        </div>
		-->
        <div id="info" class="tab_body">
		  <span id="info-span-noselection" style="display:block; padding:5px; padding-top:15px;">
		    <center><img src="images/pastedgraphic.jpg" width="235" border="0"></center>
		  </span>
		  <span id="info-span" style="display:none;">
            <h3><span id="info-title">Phasellus mattis</span></h3>
              <table>
                <tr>
                  <td valign=top><div class="img"><a id='info-lightbox' ><img id="info-img" src="images/place_holder.png" width="97" height="127" border="0"></a></div></td>
                  <td valign="top">
				    <table>
                      <tr style='display:none'>
                        <td><strong>Latitude:</strong></td>
                        <td><span id="info-latitude"></span></td>
                      </tr>
                      <tr style='display:none'>
                        <td><strong>Longitude:</strong></td>
                        <td><span id="info-longitude"></span></td>
                      </tr>
					  
                      <tr id='info-land_owner_container' style='display:none'>
                        <td colspan="2">
                          Owner: <span id="info-land_owner"></span>
					    </td>
                      </tr>
					  <tr>
                        <td colspan="2"><br/>
                          <span id="info-detail">Phasellus mattis tincidunt nibh. Fusce sed lorem in enim dictum bibendum.</span>
					    </td>
                      </tr>
                      <tr>
                        <td colspan="2">
                          <center><br><input type="button" id="buy-button" value="Buy" style="padding: 3px; padding-left: 25px; padding-right: 25px;" onClick="onBuyLand();">
						 <!--<input type="button" id="clicktozoom" value="Click to Zoom" style="padding: 3px; padding-left: 25px; padding-right: 25px; display:none">-->
						  </center>
					    </td>
                      </tr>
                    </table>
			      </td>
                </tr>
              </table>
		  </span>	  
        </div>
        <div id="search" class="tab_body">
              <h3>Search</h3>
              Here you can make a search for any area, street, mountain, country, landmark, address etc. Just make your desired search to instantly bring you to that location.<br/>
              <div style="width: 250px; overflow: hidden;">
            <input type="text" id="search_enteraplace" name="search_enteraplace_name" style="width: 90%;">
          </div>
              <h3>Pick one of the World's top places</h3>
              <div style="width: 250px; overflow: hidden;">
              <select id="search_topplaces" name="search_topplaces_name" onChange="updatePopupWindowTabSearch();" style="width: 90%;">
                  <option value="" selected="selected">Select from World's top places</option>
                  <option value="Atlantis">Atlantis</option>
                  <option value="Firefox Crop Circles">Firefox Crop Circles</option>
                  <option value="UFO Landing Pads">UFO Landing Pads</option>
                  <option value="Badlands Guardian">Badlands Guardian</option>
                  <option value="Lost at Sea">Lost at Sea</option>
             </select>
          </div>
            </div>
		<!--	
        <div id="us" class="tab_body">
              <h3>Learn about POTW</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin dignissim pharetra purus at malesuada.</p>
              <p>Praesent diam neque, malesuada vel aliquet vitae, accumsan ac ipsum. Sed mauris nibh, venenatis vitae pulvinar eget, rutrum vestibulum elit. Suspendisse ac neque enim.</p>
              <p>Donec porta ipsum quis magna interdum facilisis. Nam tristique dignissim mattis. Mauris ornare mollis lectus sed facilisis. Etiam faucibus sollicitudin accumsan.</p>
        </div>
		-->
		<!--
        <div id="buy" class="tab_body">
          <h3><span id="buy-title">Buy Land</span></h3>
              <table>
                <tr>
                  <td><div class="img"><img id="buy-img" src="images/eiffel.png"></div></td>
                  <td valign="top">
				    <table>
                      <tr>
                        <td><strong>Latitude:</strong></td>
                        <td><span id="buy-latitude">48.85800</span></td>
                      </tr>
                      <tr>
                        <td><strong>Longitude:</strong></td>
                        <td><span id="buy-longitude">2.29460</span></td>
                      </tr>
                      <tr>
                        <td colspan="2">
						  <br/><br/><br/>
                          <center><input type="button" id="buy-button" value="Buy Now" style="padding: 10px; padding-left: 25px; padding-right: 25px;" onClick="onBuyLand();"></center>
					    </td>
                      </tr>
                    </table>
			      </td>
                </tr>
              </table>
        </div>
		-->
        <div id="configuration" class="tab_body">
              <h3>Settings</h3>
              <p>
          <table width="100%">
            <tr>
              <td>Email</td>
              <td align="right"><input type="edit" id="config_email" name="config_email_name" value="" size=20 onkeypress="document.getElementById('config_save').disabled = false;"><input type="button" id="config_save" name="config_save_name" value="Save" disabled onClick="this.disabled = true; setCookie('user_email', document.getElementById('config_email').value, 365);" ></td>
            </tr>
          </table>
          <table>
            <tr>
              <td>Show Own Land</td>
              <td><input type="checkbox" id="config_showownland" name="config_showownland_name" onClick="updatePopupWindowTabConfig(true);" checked></td>
            </tr>
            <tr>
              <td>Show Important Places</td>
              <td><input type="checkbox" id="config_showimportantplaces" name="config_showimportantplaces_name" onClick="updatePopupWindowTabConfig(true);" checked></td>
            </tr>
            <tr>
              <td>Show Owned Land</td>
              <td><input type="checkbox" id="config_showownedland" name="config_showownedland_name" onClick="updatePopupWindowTabConfig(true);"></td>
            </tr>
            <tr>
              <td>Show Grid</td>
              <td><input type="checkbox" id="config_showgrid" name="config_showgrid_name" onClick="updatePopupWindowTabConfig(true);" checked></td>
            </tr>
          </table>
              </p>
            </div>
        <div id="help" class="tab_body">
              <h3>About</h3>
			  <p>Dear Citizen of the World</p>
			  <p>Welcome to <a href="http://www.PieceoftheWorld.co" target="_blank">PieceoftheWorld.co</a>, the site where you set your mark on the world. You will be in charge and have full control of your virtual piece - upload a picture and write a description.</p>
			  <p>You will receive a certificate by email proving that you are the exclusive owner. Should you receive a good offer, you can sell your piece of the world, hopefully making a profit.</p>
			  <p>Each piece represents an acre of our planet and it can be yours today! What part of the world means something special to you? That cafe where you met your spouse? The arena of your favorite football team? Your childhood home? Your school or university? One square costs $ 9.90 ($ 6.93 if shared on Facebook).</p>
			  <p>So join us and set your mark - get your piece of the world today.</p>
			  <p>Piece of the World team</p>
			  <p>Contact us:<br><a href='mailto:PieceoftheWorld2013@gmail.com'>PieceoftheWorld2013@gmail.com</a></p>
			  
        </div>
      </div>
        </div>
  </div>
    </div>

<div id="header">
<div id="trends" style="position:absolute; top:0px; left:50px; right:50px;">
	<div class="inner" style="background-color:#224466;">
		<ul class="trendscontent">
			<!--<li class="trend-label">RECENTLY PURCHASED LAND:</li>-->
<?php
	$conOptions = GetGlobalConnectionOptions();
	$con = mysql_connect($conOptions['server'], $conOptions['username'], $conOptions['password']);
	if (!$con) { die('Database connection error.'); }
	mysql_select_db($conOptions['database'], $con);
	$sql = "SELECT x, y, title, detail FROM land ORDER BY id DESC LIMIT 15";
	$result = mysql_query($sql);
	if ($result) {
		while ($row = mysql_fetch_array($result)) {
			echo '<li>';
			echo '<a href=# class="search_link" name="'.$row[2].'" rel="nofollow">'.$row[2].' ('.$row[0].'-'.$row[1].')</a>';
			echo '<em class="description">'.$row[3].'</em>';
			echo '</li>';
		}
	}
	mysql_close($con);
?>
		</ul>
	</div>
	<span class="fade fade-left">&nbsp;</span><span class="fade fade-right">&nbsp;</span>
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
