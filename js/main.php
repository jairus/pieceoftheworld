<?php
session_start();
header('Content-Type: application/javascript');
?>
var fillColorAcquiredPlot = "#FF0000";
var fillColorAcquiredSpecialArea = "#FF0000";
var fillColorSpecialArea = "#00FF00";
var fillOpacity = "0.5";
var strokeColor = "#5D5D5D";
var strokeOpacity = "1";
var strokeWeight = "0.4";
var masterUser = 'masteruser@gmail.com';	
var enableGeoLoc = true;
var geoLoc;
if (enableGeoLoc != false) {
	// Try HTML5 geolocation
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function(position) {
			geoLoc = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
		});
	}
}

/************************************* MercatorProjection related functions *************************************/

var TILE_SIZE = 629961;
var SAVED_POSITION = new google.maps.LatLng(51.5, 0.1);
function isset(v){
	if(typeof(v)!='undefined'){
		return true;
	}
	else{
		return false;
	}
}
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

/****************************************************************************************************************/

/************************* session setters ************************/

function setSessionPrice(p, sync){ //set the price in session
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
//set coordinates for boxes (used for buying)
gsessiondetails = "";
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
	if(gzones.length==1){
		if(gzones[0].ret.attached){
			ret = gzones[0].ret;
			for(i=0; i<ret.attached.length; i++){
				worldCoordinate = new google.maps.Point(ret.attached[i].x-1, ret.attached[i].y-1);
				var block = getBlockLTRB(worldCoordinate);
				var bounds = new google.maps.LatLngBounds(
					new google.maps.LatLng(block[0].lat(),block[0].lng()),
					new google.maps.LatLng(block[1].lat(),block[1].lng())
				);
				var LtLgNE = bounds.getNorthEast();
				var LtLgSW = bounds.getSouthWest();
				strlatlong = LtLgNE.lat()+","+LtLgNE.lng();
				
				if(datax.points.indexOf(strlatlong)==-1){
					datax.points.push(ret.attached[i].x+"-"+ret.attached[i].y);
					datax.strlatlongs.push(strlatlong);
				}
			}
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
//set coordinates for marker (used for buying)
function setCoordsForMarkers(json){
	consoleX("setCoordsForMarkers");
	consoleX(json);
	total = 0;
	datax = {};
	datax.points = [];
	datax.strlatlongs = [];
	if(json.points){
		ret = json.points;
		for(i=0; i<ret.length; i++){
			worldCoordinate = new google.maps.Point(ret[i].x-1, ret[i].y-1);
			var block = getBlockLTRB(worldCoordinate);
			var bounds = new google.maps.LatLngBounds(
				new google.maps.LatLng(block[0].lat(),block[0].lng()),
				new google.maps.LatLng(block[1].lat(),block[1].lng())
			);
			var LtLgNE = bounds.getNorthEast();
			var LtLgSW = bounds.getSouthWest();
			strlatlong = LtLgNE.lat()+","+LtLgNE.lng();
			
			if(datax.points.indexOf(strlatlong)==-1){
				datax.points.push(ret[i].x+"-"+ret[i].y);
				datax.strlatlongs.push(strlatlong);
			}
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

/******************************************************************/

/************************show functions******************************/
function showWorldView(object) {
	//alert('test');
	if (map) {
		map.setZoom(ZOOM_LEVEL_WORLD);
		map.setMapTypeId(google.maps.MapTypeId.HYBRID);
	}
	else {
		initialize(ZOOM_LEVEL_WORLD, google.maps.MapTypeId.HYBRID);
	}
}	
//ZOI
function showVideo(videoStr) {
	jQuery("#video").empty();

	var mainVideo = '';
	mainVideo += '<a style="cursor:pointer; color:#FF0000; text-decoration:none;" onclick="showInfo();">Info</a> &raquo; <a style="cursor:pointer; color:#FF0000; text-decoration:none;" onclick="showThumbs();">Thumbnails</a> &raquo; Video<br /><br />';
	mainVideo += '<iframe width="430" height="270" src="http://www.youtube.com/embed/'+videoStr+'" frameborder="0" allowfullscreen></iframe>';

	jQuery("#video").append(mainVideo);

	jQuery("#info table").hide();
	jQuery("#info #thumbs").hide();
	jQuery("#info #video").show();
}

function showThumbs() {
	jQuery("#info table").hide();
	jQuery("#info #thumbs").show();
	jQuery("#info #video").hide();
}

function showInfo() {
	jQuery("#info table").show();
	jQuery("#info #thumbs").hide();
	jQuery("#info #video").hide();
}
//ZOI

function showPopupWindowTabInfo(isSelected) {
	if(isSelected){
		jQuery("#info-span").show();
		jQuery("#info-span-noselection").hide();
	}
	else{
		jQuery("#info-span-noselection").show();
		jQuery("#info-span").hide();
	}
	//document.getElementById('info-span-noselection').style.display = (isSelected == true) ? 'none' : 'block';
	//document.getElementById('info-span').style.display = (isSelected == true) ? 'block' : 'none';
}

/*******************************************************************/

/*************************   events     ****************************/

function onClick(event) { //onclick on anywhere in map
	showPopupWindowTabInfo(false); //hide info tab
}
var numblocks = 0;
function onZoomChanged(){
}

/*******************************************************************/

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
	//ZOI
	jQuery("#info table").show();
	jQuery("#info #thumbs").hide();
	jQuery("#info #video").hide();
	jQuery("#clickvideo").hide();
	//ZOI
	jQuery("#tabs").tabs("select",0);
	
	//jairus
	if(map.getZoom()<17){ //17 is the block view
		jQuery("#clicktozoom").show();
	}
	jQuery("#clicktozoom").click(function () {
		var loc = new google.maps.LatLng(event.latLng.lat(),event.latLng.lng());
		map.setZoom(17);
		map.setCenter(loc);
		jQuery("#clicktozoom").hide();
	});
	
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
	strlatlong = LtLgNE.lat()+","+LtLgNE.lng();
	updatePopupWindowTabInfo(event.latLng, strlatlong);
}



var blocksHidden = true;
function onIdle(manualCall) {
	if (map == null) { return; }
	if (map.getZoom() >= 17) { //if block mode
		consoleX("show blocks");
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
	}
	setTimeout(function(){ //delay adding of markers by 200ms
		if (map.getZoom() >= 17){ //if block mode
			for(i=0; i<globalRedMarkers.length; i++){
				globalRedMarkers[i].setMap(null);
			}
			for(i=0; i<globalGreenMarkers.length; i++){
				globalGreenMarkers[i].setMap(null);
			}
			globalRedMarkers = [];
			globalGreenMarkers = [];
		}
		else{ //if marker mode
			ajaxAddMarkers(map);
		}
	},
	200); 
}

function getBlockMarker(x, y) { //get the lat and long position for the marker from points
	var worldCoordinate = new google.maps.Point(x, y);
	worldCoordinate.x -= 0.5;
	worldCoordinate.y -= 0.5;
	var projection = new MercatorProjection();
	return projection.fromPointToLatLng(worldCoordinate);
}

function gotoLoc(x, y){ //go to location
	LatLng = getBlockMarker(x, y);
	var loc = new google.maps.LatLng(LatLng.lat(),LatLng.lng());
	map.setZoom(17);
	map.setCenter(loc);
	jQuery("#clicktozoom").hide();
	strlatlong = LatLng.lat()+","+LatLng.lng();
	updatePopupWindowTabInfo(LatLng, strlatlong);
}

function getBlockInfo(inLatLng) { //get the block from latlong object
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

function getBlockMoreInfo(LatLng){ //get more info like country, region, city, area type
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
						}
					});
					if(areatype=="water"){
						price = 0.90;
					}
					else{
						price = 10.90;
					}
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

function getBlockInfoNew(inLatLng, strlatlong, worldCoordinate){  //collective getting of block info blockinfo+exact details+country,city,region,areatype
	consoleX("getBlockInfoNew");
	var ret = {};
	var retx = {};
	ret.inLatLng = inLatLng;
	ret.detail = "";
	ret.title = "";
	ret.points = "";
	ret.json = "";
	ret.attached = "";
	hasdetails = false;
	var blockInfo = getBlockInfo(inLatLng);
	var returnText = ajaxGetMarker(map, blockInfo[2].x, blockInfo[2].y);
	var markerJSON = JSON.parse(returnText);
	if(markerJSON[0].points){
		ret.attached = markerJSON[0].points;
	}
	specialbought = false;
	try{
		owner_user_id = markerJSON[0].owner_user_id;
		land_special_id = markerJSON[0].land_special_id;
		if(isset(owner_user_id)||isset(land_special_id)){
			specialbought = true;
		}
	}
	catch(e){
		specialbought = false;
	}		
	if (specialbought) { //if bought or special
		if(markerJSON[0].email){ //if special land unpaid
			consoleX(markerJSON[0].land_special_id);
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
		
		if(
			isset(markerJSON[0].country)
			||isset(markerJSON[0].region)
			||isset(markerJSON[0].city)
			||isset(markerJSON[0].areatype)
			){
			rtemp = {};
			hasdetails = true;
			if(isset(markerJSON[0].city)){
				price = 9.90;
			}
			else if(isset(markerJSON[0].country)||isset(markerJSON[0].region)){
				price = 5.90;
			}
			else{
				if(markerJSON[0].areatype=="water"){
					price = 0.90;
				}
				else{
					price = 10.90;
				}
			}
			
			rtemp.country = markerJSON[0].country;
			rtemp.region = markerJSON[0].region;
			rtemp.city = markerJSON[0].city;
			rtemp.areatype = markerJSON[0].areatype;
			rtemp.price = price;
		}
		else{
			rtemp = getBlockMoreInfo(strlatlong); //more info
		}
		
		ret.city = rtemp.city;
		ret.region = rtemp.region;
		ret.country = rtemp.country;
		ret.areatype = rtemp.areatype;
		
		retx.points = worldCoordinate.x+"-"+worldCoordinate.y;
		retx.strlatlong = strlatlong;
		retx.city = rtemp.city;
		retx.region = rtemp.region;
		retx.country = rtemp.country;
		retx.areatype = rtemp.areatype;
		
	}
	else{
		if(
			isset(markerJSON[0].country)
			||isset(markerJSON[0].region)
			||isset(markerJSON[0].city)
			||isset(markerJSON[0].areatype)
			){
			rtemp = {};
			hasdetails = true;
			if(isset(markerJSON[0].city)){
				price = 9.90;
			}
			else if(isset(markerJSON[0].country)||isset(markerJSON[0].region)){
				price = 5.90;
			}
			else{
				if(markerJSON[0].areatype=="water"){
					price = 0.90;
				}
				else{
					price = 10.90;
				}
			}
			
			rtemp.country = markerJSON[0].country;
			rtemp.region = markerJSON[0].region;
			rtemp.city = markerJSON[0].city;
			rtemp.areatype = markerJSON[0].areatype;
			rtemp.price = price;
		}
		else{
			rtemp = getBlockMoreInfo(strlatlong); //more info
		}
		ret.price = rtemp.price;
		ret.city = rtemp.city;
		ret.region = rtemp.region;
		ret.country = rtemp.country;
		ret.areatype = rtemp.areatype;
		ret.colored = 0;
		//set the points
		ret.points = worldCoordinate.x+"-"+worldCoordinate.y;
		ret.strlatlong = strlatlong;
		
		retx.points = worldCoordinate.x+"-"+worldCoordinate.y;
		retx.strlatlong = strlatlong;
		retx.city = rtemp.city;
		retx.region = rtemp.region;
		retx.country = rtemp.country;
		retx.areatype = rtemp.areatype;
	}
	//alert(hasdetails);
	if(!hasdetails){
		jQuery.ajax({
			dataType: "html",
			async: true,
			type: "POST",
			data: "data="+JSON.stringify(retx),
			url: "?saveblockdetails=1",
			success: function(data){
				
			}
		});
	}
	return ret; //returns ret object
}

function unsetGZones(){ //unset global selected blocks (zones)
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

function calculateTotal(sync){ //calculated total blocks
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


function updatePopupWindowTabInfoNew(){ //updated of tab info from block view
	jQuery("#buy-button").hide();
	if(!gzones.length){
		showPopupWindowTabInfo(false);
		return false;
	}
	consoleX("updatePopupWindowTabInfoNew");
	
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
			//consoleX("lalala");
			//consoleX(gzones[i].ret);
			try{
				owner_user_id = gzones[i].ret.json.owner_user_id;
				land_special_id = gzones[i].ret.json.land_special_id;
				if(isset(owner_user_id)||isset(land_special_id)){
					specialbought = true;
				}
			}
			catch(e){
				specialbought = false;
			}
			if(specialbought){ //if a special land or a bought land
				jQuery("#info-title").html(gzones[i].ret.json.title);
				jQuery("#info-detail").html(gzones[i].ret.json.detail);
				jQuery("#info-city").show();
				var fbLikeLink = "http://pieceoftheworld.co/viewLand.php?landId=" + gzones[i].ret.json['id'] + "&specialLandId=" + gzones[i].ret.json['land_special_id'];
				//alert(fbLikeLink);
				jQuery('#fbLikeHolder').html('<fb:like href="'+ fbLikeLink + '" ref="land" layout="standard" show-faces="true" width="450" action="like" colorscheme="light" /></fb:like>');
				FB.XFBML.parse();
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
				jQuery("#info-city").hide();
				
				if(gzones[i].ret.json.land_owner){
					jQuery("#info-land_owner_container").show();
					jQuery("#info-land_owner").html(gzones[i].ret.json.land_owner);
				}
				if(firstindex==""){
					firstindex = i;
				}
				
				if (gzones[i].ret.json.email == masterUser) { //if unbought
					detailsobjarr[gzones[i].ret.json.title] = {};
					if(!detailsobjarr[gzones[i].ret.json.title].price){
						detailsobjarr[gzones[i].ret.json.title].price = 0;
					}
					gzones[i].ret.price = gzones[i].ret.json.price * 1;
					detailsobjarr[gzones[i].ret.json.title].price = gzones[i].ret.price.toFixed(2);
					if(!detailsobjarr[gzones[i].ret.json.title].count){
						detailsobjarr[gzones[i].ret.json.title].count = 0;
					}
					detailsobjarr[gzones[i].ret.json.title].count++;
					if(!detailsobjarr[gzones[i].ret.json.title].idx){
						detailsobjarr[gzones[i].ret.json.title].idx = "";
					}
					detailsobjarr[gzones[i].ret.json.title].idx += i+",";
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
					if(!detailsobjarr["Region: "+gzones[i].ret.region].idx){
						detailsobjarr["Region: "+gzones[i].ret.region].idx = "";
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
					if(!detailsobjarr["Country: "+gzones[i].ret.country].idx){
						detailsobjarr["Country: "+gzones[i].ret.country].idx = "";
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
					if(!detailsobjarr["Water Area: "].idx){
						detailsobjarr["Water Area: "].idx = "";
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
					if(!detailsobjarr["Land Area: "].idx){
						detailsobjarr["Land Area: "].idx = "";
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
			jQuery("#info-lightbox").attr("title", "" );
			jQuery("#info-lightbox").colorbox({width:'550px'});
			jQuery("#info-img")[0].src = "images/place_holder_small.png?_=1";
			jQuery("#info-img").unbind();
		}
		if(gzones[i].ret.json.land_special_id){
			if (gzones[i].ret.json.email == masterUser) { //if unbought
				consoleX("special unbought");
				price = gzones[i].ret.json.price;
				jQuery("#info-detail").html(jQuery("#info-detail").html()+ '<br /><br />Price: $<span id="theprice">'+price+"</span>");
				//document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $<span id="theprice">'+price+"</span>";
				jQuery("#buy-button").val("Buy");
				jQuery("#buy-button").show();
				for(x in detailsobjarr){
					detail = x + " USD " + detailsobjarr[x].price  + " x " + detailsobjarr[x].count ;
					gsessiondetails += detail + "<br />";
					//details += detail + " <img src='images/x.png'  onclick='cancelBox(\""+detailsobjarr[x].idx+"\")' style='cursor:pointer' /><br />" ;
				}
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
	link = "http://pieceoftheworld.co/?latlong="+gzones[0].ret.inLatLng.lat()+"~"+gzones[firstindex].ret.inLatLng.lng();
	
	globallink = link;
	//link = encodeURIComponent(link);
	sharelink = "https://www.facebook.com/dialog/feed?app_id=454736247931357&link="+link+"&picture="+jQuery('#info-img').attr("src")+"&name=Piece of the World&caption="+sharetitle+"&description="+sharetext+"&redirect_uri="+link;
	//sharelink = "https://www.facebook.com/dialog/feed?app_id=454736247931357&link="+link+"&picture="+document.getElementById('info-img').src+"&name=Piece of the World&caption="+sharetitle+"&description="+sharetext+"&redirect_uri="+link;

	//remove fb like button if the previously clicked land is valid
	jQuery('#fbLikeHolder').html('');

	jQuery("#fbsharelink").attr("href", sharelink);
	jQuery("#fbsharelink").show();
	jQuery("#sharethisloc").show();
}

var gzones = []; //selected blocks
var gattached = []; //attached blocks from 1 block

function putBox(event){ //event when a block is clicked
	//disable block clicking when zoomed out
	if(map.getZoom()<17){
		unsetGZones();
		//alert("1");
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
	
	
	showPopupWindowTabInfo(false);
	/***************************/
	ret = {};
	//put in the box
	var zoneCoords = [
		new google.maps.LatLng(LtLgNE.lat(), LtLgNE.lng()),
		new google.maps.LatLng(LtLgNE.lat(), LtLgSW.lng()),
		new google.maps.LatLng(LtLgSW.lat(), LtLgSW.lng()),
		new google.maps.LatLng(LtLgSW.lat(), LtLgNE.lng())
	];
	
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
		//consoleX(this.ret.attached);
		if(isset(this.ret.attached)){
			//alert(this.ret.attached.length);
			if(this.ret.attached.length > 0){
				unsetGZones();
				//alert("3");
			}
		}
		calculateTotal(); //calculate total of 
		updatePopupWindowTabInfoNew();
	});
	zone.setMap(window.map);
	//alert("box is set");
	/***************************/
	
	setTimeout(
	function(){
		//new update popup info
		ret = getBlockInfoNew(event.latLng, strlatlong, worldCoordinate);
		if(ret.colored){
			unsetGZones();
			//alert("2");
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
		ret.active = 1;
		zone.ret = ret;
		
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
				var bounds = new google.maps.LatLngBounds(
					new google.maps.LatLng(block[0].lat(),block[0].lng()),
					new google.maps.LatLng(block[1].lat(),block[1].lng())
				);
				var LtLgNE = bounds.getNorthEast();
				var LtLgSW = bounds.getSouthWest();
				
				//strlatlong = LtLgNE.lat()+","+LtLgNE.lng();
				
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
					//alert("4");
					calculateTotal(); //calculate total of 
					updatePopupWindowTabInfoNew();
				});
				zone.setMap(window.map);
			}
		}
	},
	100);
}

/*************************** blocks function ********************/

function drawRect(lt, rb, color, opacity) { //draw the actual graphic in the google map
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
	google.maps.event.addListener(rectangle, 'click', function(event) { 
		putBox(event);
	});
	window.rectangles.push(rectangle);
}
function drawBlocks(map) {// draw the blocks
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
		returnText = ajaxGetBlocks(map, (hStart*1)-Math.round(hStart*allowance), (vStart*1)-Math.round(vStart*allowance), (hEnd*1)+Math.round(hEnd*allowance), (vEnd*1)+Math.round(vEnd*allowance));
		rtc = new returnTextClass((hStart*1)-Math.round(hStart*allowance), (vStart*1)-Math.round(vStart*allowance), (hEnd*1)+Math.round(hEnd*allowance), (vEnd*1)+Math.round(vEnd*allowance), returnText);
		returnTextCache.push(rtc);
	}

	var markersJSON = JSON.parse(returnText);
	var color;
	var opacity;
	var email;
	var cnt1 = vStart;		
	var process0 = function() {
		for (; cnt1 <= vEnd; cnt1++) {
			var cnt2 = hStart;
			var process = function() {
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
				setTimeout(process0, 5);
			}
			if(cnt1 >= vEnd && cnt2 >= hEnd ){
				jQuery("#loadinggrid").css('top', -10000);
			}
		}
	}
}
/****************************************************************/

function consoleX(str){ //function to debug
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



var globallink;
var updateproc = [];
function updatePopupWindowTabInfo(inLatLng, strlatlong) {
	consoleX("updatePopupWindowTabInfo");
	jQuery("#buy-button").hide();
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
	jQuery('#info-latitude').html(inLatLng.lat().toFixed(5));
	jQuery('#info-longitude').html(inLatLng.lng().toFixed(5));
	//document.getElementById('info-latitude').innerHTML = inLatLng.lat().toFixed(5);
	//document.getElementById('info-longitude').innerHTML = inLatLng.lng().toFixed(5);
	
	
	//document.getElementById('buy-latitude').innerHTML = inLatLng.lat().toFixed(5);
	//document.getElementById('buy-longitude').innerHTML = inLatLng.lng().toFixed(5);
	
	var blockInfo = getBlockInfo(inLatLng);
	x1 = blockInfo[2].x;
	y1 = blockInfo[2].y;
	x2 = blockInfo[2].x;
	y2 = blockInfo[2].y;
	//jjjjj
	if(!isset(updateproc[x1+"-"+y1])||updateproc[x1+"-"+y1]=='processed'){
		updateproc[x1+"-"+y1] = 'processing...';
		consoleX(updateproc[x1+"-"+y1]);
	}
	else{
		
		return false;
	}
	jQuery.ajax({
		url:'ajax/get_markers.php?x1='+x1+'&y1='+y1+'&x2='+x2+'&y2='+y2+'&marker=1'+"&_=<?php echo time(); ?>",
		dataType:'html',
		async:true,
		success:function(data, textStatus, jqXHR){
			updateproc[x1+"-"+y1] = 'processed';
			consoleX(updateproc[x1+"-"+y1]);
			returnText = data;
			markerJSON = JSON.parse(returnText);
			jQuery("#info-land_owner_container").hide();
			jQuery("#info-img").attr("src", "images/place_holder_small.png?_=1");
			//document.getElementById('info-land_owner_container').style.display="none";
			//document.getElementById('info-img').src = "images/place_holder_small.png?_=1";
			if (returnText != '[[]]') {
				jQuery('#info-title').html(markerJSON[0].title);
				jQuery('#info-detail').html(markerJSON[0].detail);
				//document.getElementById('info-title').innerHTML = markerJSON[0].title;
				//document.getElementById('info-detail').innerHTML = markerJSON[0].detail;
				
				//ZOI
				jQuery("#thumbs").empty();					
				var videosJSON = null;
				jQuery.ajax({
					url:'ajax/get_videos.php?action=get_videos&land_detail_id='+markerJSON[0].land_detail_id,
					dataType:'html',
					async:true,
					success:function(data, textStatus, jqXHR){
						videosJSON = data;
						if(videosJSON){
							var videoJSON = JSON.parse(videosJSON);
							var thumbs = '';
							thumbs += '<a style="cursor:pointer; color:#FF0000; text-decoration:none;" onclick="showInfo();">Info</a> &raquo; Thumbnails<br /><br />';
							
							for(var i=0; i<videoJSON.length; i++){
								var videoArr = videoJSON[i].video.split("v=");
								var videoStr = videoArr[1];
							
								thumbs += '<a style="cursor:pointer;" onclick="showVideo(\''+videoStr+'\');"><img src="http://img.youtube.com/vi/'+videoStr+'/0.jpg" width="80" border="0" /></a> &nbsp;&nbsp; ';
							}
							
							jQuery("#thumbs").append(thumbs);
							
							jQuery("#clickvideo").show();
						}
					}
				});
				//ZOI

				if(markerJSON[0].land_owner){
					jQuery("#info-land_owner").html(markerJSON[0].land_owner);
					jQuery('#info-land_owner_container').show();
					//document.getElementById('info-land_owner').innerHTML = markerJSON[0].land_owner;
					//document.getElementById('info-land_owner_container').style.display="";
				}
				else{
					jQuery("#info-land_owner").html("");
					//document.getElementById('info-land_owner').innerHTML = "";
				}
				//ajaxExtractLandPicture(markerJSON[0].id);
				//document.getElementById('info-img').src = "images/thumbs/land_id_"+markerJSON[0].id;
				//alert(markerJSON[0].thumb_url);
				if(markerJSON[0].thumb_url){
					jQuery("#info-img").attr("src", markerJSON[0].thumb_url);
					//document.getElementById('info-img').src = markerJSON[0].thumb_url;
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
					if(markerJSON[0].land_special_id){ //special unbought land
						gBuyOnMarker = true; //flasg to indicate that buy button was pressed from a marker
						price = markerJSON[0].price;
						gsessiondetails=jQuery('#info-detail').html();
						//gsessiondetails=document.getElementById('info-detail').innerHTML;
						setCoordsForMarkers(markerJSON[0]);
						setSessionPrice(price, true);
						jQuery("#buy-button").val("Buy");
						jQuery("#buy-button").show();
						jQuery("#info-detail").html(jQuery("#info-detail").html()+'<br /><br />Price: $<span id="theprice">'+price+"</span>");
						//document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $<span id="theprice">'+price+"</span>";
					}
					else if(numblocks > 0){
						price = "";
						//price = (numblocks*9.90);
						//price = price.toFixed(2);
						jQuery("#info-detail").html(jQuery("#info-detail").html()+'<br /><br />Price: $<span id="theprice">'+price+"</span>");
						//document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $<span id="theprice">'+price+"</span>";
						setCity(strlatlong, 1, numblocks);
					}
					jQuery("#buy-button").val("Buy");
					//document.getElementById('buy-button').value = "Buy";
				}
				else {
					jQuery("#buy-button").val("Bid");
					//document.getElementById('buy-button').value = "Bid";
					setCity(strlatlong);
				}
				//document.getElementById('buy-img').src = "images/thumbs/land_id_"+markerJSON[0].id;
			}
			else {
				//alert(updatePopupWindowTabInfo);
				jQuery("#info-title").html('Your title here.');
				jQuery("#info-detail").html('Your description here.');
				//document.getElementById('info-title').innerHTML = 'Your title here.';
				//document.getElementById('info-detail').innerHTML = 'Your description here.';
				
				if(markerJSON[0].land_special_id){
					price = markerJSON[0].price;
					jQuery("#info-detail").html(jQuery("#info-detail").html()+ '<br /><br />Price: $<span id="theprice">'+price+"</span>");
					//document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $<span id="theprice">'+price+"</span>";
				}
				else if(numblocks > 0){
					price = "";
					//price = (numblocks*9.90);
					//price = price.toFixed(2);
					jQuery("#info-detail").html(jQuery("#info-detail").html()+ '<br /><br />Price: $<span id="theprice">'+price+"</span>");
					//document.getElementById('info-detail').innerHTML  = document.getElementById('info-detail').innerHTML + '<br /><br />Price: $<span id="theprice">'+price+"</span>";
					setCity(strlatlong, 1, numblocks);
				}
				
				jQuery("#info-img").attr("src", 'images/place_holder_small.png?_=1');
				jQuery("#buy-button").val("Buy");
				//document.getElementById('info-img').src = 'images/place_holder_small.png?_=1';
				//document.getElementById('buy-button').value = "Buy";

				//document.getElementById('buy-img').src = 'images/place_holder.png';
			}
			
			if(!sharetitle){
				sharetitle = "Mark your very own Piece of the World!";
			}
			if(!sharetext){
				sharetext = "Get your own piece of the world at pieceoftheworld.com";
			}
			link = "http://pieceoftheworld.co/?latlong="+inLatLng.lat()+"~"+inLatLng.lng();
			
			globallink = link;
			//link = encodeURIComponent(link);
			sharelink = "https://www.facebook.com/dialog/feed?app_id=454736247931357&link="+link+"&picture="+jQuery("#info-img").attr("src")+"&name=Piece of the World&caption="+sharetitle+"&description="+sharetext+"&redirect_uri="+link;
			//sharelink = "https://www.facebook.com/dialog/feed?app_id=454736247931357&link="+link+"&picture="+document.getElementById('info-img').src+"&name=Piece of the World&caption="+sharetitle+"&description="+sharetext+"&redirect_uri="+link;
			
			jQuery("#fbsharelink").attr("href", sharelink);
			jQuery("#fbsharelink").show();
			jQuery("#sharethisloc").show();

			// for facebook like button. only like sold land, sold specialland, unsold specialland
			//if(markerJSON[0]['owner_user_id'] || markerJSON[0]['land_special_id'] != null){
			var fbLikeLink = "http://pieceoftheworld.co/viewLand.php?landId=" + markerJSON[0]['id'] + "&specialLandId=" + markerJSON[0]['land_special_id'];
			jQuery('#fbLikeHolder').html('<fb:like href="'+ fbLikeLink + '" ref="land" layout="standard" show-faces="true" width="450" action="like" colorscheme="light" /></fb:like>');
			FB.XFBML.parse();
			//} else {
			//    jQuery('#fbLikeHolder').html('');
			//}

			
			//temporarily hide buy button
			//jQuery("#buy-button").hide();
		}
	});
	
}

function updatePopupWindowTabSearch() {
	var place = jQuery("#search_topplaces").val();
	//var place = document.getElementById("search_topplaces").value;
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
	window.searchMarker.setPosition(location);
	window.map.setZoom(zoom);
	window.map.setCenter(location);
}

function ajaxExtractLandPicture(land_id) {
	//var markersJSON = null;
	jQuery.ajax({
		url:'ajax/extract_land_picture.php?land_id='+land_id+"&_=<?php echo time(); ?>",
		dataType:'html',
		async:false,
		success:function(data, textStatus, jqXHR){
			//markersJSON = data;
		}
	});
	//return markersJSON;
}

function ajaxGetBlocks(map, x1, y1, x2, y2) { //this is actually 
	var markersJSON = null;
	jQuery.ajax({
		url:'ajax/get_markers.php?x1='+x1+'&y1='+y1+'&x2='+x2+'&y2='+y2+'&multi=1'+"&_=<?php echo time(); ?>",
		dataType:'html',
		async:false,
		success:function(data, textStatus, jqXHR){
			markersJSON = data;
		}
	});
	return markersJSON;
}

function ajaxGetMarker(map, x1, y1, x2, y2) { //get exact details of a marker
	var markersJSON = null;
	jQuery.ajax({
		url:'ajax/get_markers.php?x1='+x1+'&y1='+y1+"&type=exact&_=<?php echo time(); ?>",
		dataType:'html',
		async:false,
		success:function(data, textStatus, jqXHR){
			markersJSON = data;
		}
	});
	return markersJSON;
}

function updateDetails(x, y){ //a function just to bulk update of land table in DB
	worldCoordinate = new google.maps.Point(x-1, y-1);
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
	//new update popup info
	var projection = new MercatorProjection();
	var point = {};
	point.x = x;
	point.y = y;
	var inLatLng = projection.fromPointToLatLng(point);
	ret = getBlockInfoNew(inLatLng, strlatlong, worldCoordinate);
}


function ajaxAddMarkers(map, force) { //get the markers in the map
	markers_loaded = true;
	ajaxAddRedMarkers(map, force);
}

var infowindow;
infowindow =  new google.maps.InfoWindow();
var markersref = [];
function setRedMarkers(map, markersJSON){ //this are actually green markers (special land)
	consoleX("setRedMarkers");
	
	var config_showownland = getCookie("config_showownland");
	var config_showownedland = getCookie("config_showownedland");
	var config_showimportantplaces = getCookie("config_showimportantplaces");
	var user_email = getCookie("user_email");
	var markers = [];
	var pass = false;
	
	//bounding box
	var bounds = map.getBounds();
	if(isset(bounds)){
		var ne = google.maps.geometry.spherical.computeOffset(bounds.getNorthEast(), 63.615*3, 45);
		var sw = google.maps.geometry.spherical.computeOffset(bounds.getSouthWest(), 63.615*3, 225);
		var blockRT = getBlockInfo(ne);
		var blockLB = getBlockInfo(sw);
		var vStart = blockRT[2].y;
		var vEnd = blockLB[2].y;
		var hStart = blockLB[2].x;
		var hEnd = blockRT[2].x;
	}
	markersrefnew = [];
	for (var i = 0, len = markersJSON.length; i < len; ++i) {
		<?php
		if($_GET['updatedetailsspecial']){
			?>
			if(markersJSON[i].areatype==""){
				updateDetails(markersJSON[i].x, markersJSON[i].y);
			}
			<?php
		}
		?>
		if(markersJSON[i].x&&markersJSON[i].y){
			
			pass = false;
			//check if within bounding box
			if(!isset(bounds)||map.getZoom()<=3){
				pass = true;
			}
			if(
				pass==false&&
				markersJSON[i].x>=hStart&&markersJSON[i].x<=hEnd
				&&
				markersJSON[i].y>=vStart&&markersJSON[i].y<=vEnd
			){
				pass = true;
			}
			if(isset(markersref[markersJSON[i].x+"-"+markersJSON[i].y])){ //if nakalagay na
				
				markersrefnew[markersJSON[i].x+"-"+markersJSON[i].y] = markersref[markersJSON[i].x+"-"+markersJSON[i].y];
				if(markersrefnew[markersJSON[i].x+"-"+markersJSON[i].y].map!=map){
					markersrefnew[markersJSON[i].x+"-"+markersJSON[i].y].setMap(map);
				}
				if(isset(markersJSON[i].count)){
					icon = 'http://cdn.pieceoftheworld.co/image.php?marker=1&count='+((isset(markersJSON[i].count))? markersJSON[i].count : "");
				}
				else{
					if(markersJSON[i].owner_user_id>0){
						icon = 'http://cdn.pieceoftheworld.co/images/marker_blue.png';
						if(isset(markersJSON[i].land_detail_id)){
							if(markersJSON[i].land_detail_id>0){
								icon = 'http://cdn.pieceoftheworld.co/images/marker_blue.png';
							}
						}
					}
					else{
						icon = 'http://cdn.pieceoftheworld.co/images/marker_blue.png';
					}
					google.maps.event.addListener(markersrefnew[markersJSON[i].x+"-"+markersJSON[i].y], 'click', function(event) { 
						onMarkerClick(event, "special"); 
					});
					
				}
				markersrefnew[markersJSON[i].x+"-"+markersJSON[i].y].setIcon(icon);
				consoleX(markersJSON[i].x+"-"+markersJSON[i].y+" nakalagay na so skip "+markersrefnew[markersJSON[i].x+"-"+markersJSON[i].y].ret.count);
				pass = false;
			}
			if(pass){
				//consoleX(markersJSON[i].country);
				
				if(isset(markersJSON[i].count)){
					var marker = new google.maps.Marker({
						position: getBlockMarker(markersJSON[i].x, markersJSON[i].y),
						map: map,
						icon: 'http://cdn.pieceoftheworld.co/image.php?marker=1&count='+((isset(markersJSON[i].count))? markersJSON[i].count : ""),
						ret: markersJSON[i]
					});
				}
				else{
					if(markersJSON[i].owner_user_id>0){
						img = 'http://cdn.pieceoftheworld.co/images/marker_blue.png';
						if(isset(markersJSON[i].land_detail_id)){
							if(markersJSON[i].land_detail_id>0){
								img = 'http://cdn.pieceoftheworld.co/images/marker_blue.png';
							}
						}
						
						consoleX(img);
						var marker = new google.maps.Marker({
							position: getBlockMarker(markersJSON[i].x, markersJSON[i].y),
							map: map,
							icon: img,
							ret: markersJSON[i]
						});
						google.maps.event.addListener(marker, 'click', function(event) { 
							onMarkerClick(event, "special"); 
						});
					}
					else{
						img = 'http://cdn.pieceoftheworld.co/images/marker_blue.png';
						consoleX(img);
						var marker = new google.maps.Marker({
							position: getBlockMarker(markersJSON[i].x, markersJSON[i].y),
							map: map,
							icon: img,
							ret: markersJSON[i]
						});
						google.maps.event.addListener(marker, 'click', function(event) { 
							onMarkerClick(event, "special"); 
						});
					}
				}
				markersrefnew[markersJSON[i].x+"-"+markersJSON[i].y] = marker;
				globalRedMarkers.push(marker);
			}
			
		}
	}
	consoleX("hide markers not in markersrefnew");
	for(key in markersref){
		if(isset(markersrefnew[key])){
			consoleX(key+" is in count "+markersref[key].ret.count);
		}
		else{
			consoleX(key+" should be out count "+markersref[key].ret.count);
			markersref[key].setMap(null);
		}
	}
	markersref = markersrefnew;
}
var globalRedMarkers = [];
var globalRedJSONArr = [];
var loadedrz = -1;
var globalPrevZoom;
var zoom2 = 5;
var zoom3 = 8;
var zoom4 = 10;
function ajaxAddRedMarkers(map, force) { //they are not actually red markers... just reusing a function name
	consoleX("globalPrevZoom = "+globalPrevZoom+" map.getZoom() ="+map.getZoom());
	if(globalPrevZoom == map.getZoom()&&map.getZoom()<zoom2){ //if not shifting zoom
		return false;
	}
	if(
		((globalPrevZoom<zoom2&&map.getZoom()>=zoom2&&map.getZoom()<zoom3)||//shifting from level1 zoom to level2
		(globalPrevZoom>=zoom2&&globalPrevZoom<zoom3&&map.getZoom()>=zoom3)|| //shifting from level2 zoom to level3
		(globalPrevZoom>=zoom3&&globalPrevZoom<zoom4&&map.getZoom()>=zoom4)|| //shifting from level3 zoom to level4
		(globalPrevZoom>=zoom4&&map.getZoom()<zoom4)|| //shifting from level4 zoom to level1 or level2 or level 3
		(globalPrevZoom>=zoom3&&map.getZoom()<zoom3)|| //shifting from level3 zoom to level1 or level2
		(globalPrevZoom>=zoom2&&map.getZoom()<zoom2)) //shifting from level2 zoom to level1
	){ 
		consoleX("unset globalRedMarkers");
		for(i=0; i<globalRedMarkers.length; i++){
			globalRedMarkers[i].setMap(null);
		}
		globalRedMarkers = [];
	}
	globalPrevZoom = map.getZoom();
	z = loadedrz;
	if(!isset(globalRedJSONArr[z])){
		globalRedJSONArr[z] = "";
	}
	consoleX("zoom "+map.getZoom());
	consoleX("red z = "+z);
	if(globalRedJSONArr[z]!=""&&(!isset(force)||force==false)&&map.getZoom()<3){ //load from cache
		consoleX("reloading r markers from cache");
		setRedMarkers(map, globalRedJSONArr[z]);
	}
	else{
		if(map.getZoom()>=zoom2){
			var bounds = map.getBounds();
			var ne = google.maps.geometry.spherical.computeOffset(bounds.getNorthEast(), 63.615*3, 45);
			var sw = google.maps.geometry.spherical.computeOffset(bounds.getSouthWest(), 63.615*3, 225);
			var blockRT = getBlockInfo(ne);
			var blockLB = getBlockInfo(sw);
			var vStart = blockRT[2].y;
			var vEnd = blockLB[2].y;
			var hStart = blockLB[2].x;
			var hEnd = blockRT[2].x;
			bounds = "x1="+hStart+"&x2="+hEnd+"&y1="+vStart+"&y2="+vEnd+"&bounded=1";
		}
		if(map.getZoom()>=zoom4){
			url = 'ajax/get_markers.php?type=special&'+bounds;
		}
		else if(map.getZoom()>=zoom3){
			url = 'ajax/get_markers.php?type=special&trim=100&'+bounds;
		}
		else if(map.getZoom()>=zoom2){
			url = 'ajax/get_markers.php?type=special&trim=2000&'+bounds;
		}
		else{
			url = 'ajax/get_markers.php?type=special&trim=10000';
		}
		z = url;
		
		jQuery.ajax({
			dataType: "html",
			async: true,
			type: "POST",
			data: "",
			url: url,
			success: function(data){
				var markersJSON = JSON.parse(data);
				globalRedJSONArr[z] = markersJSON;
				setRedMarkers(map, markersJSON);
			}
		});
	}	
	loadedrz = z;
}

gBuyOnMarker = false;
function onBuyLand() {
	if(!gBuyOnMarker){
		calculateTotal(true); //second parameter to make the routine sync
		setCoords();
	}
	gBuyOnMarker = false;
	
	var url = "";
	if (jQuery('#buy-button').val() == "Buy") {
		//url = "bidbuyland.php?type=buy&land="+blocksAvailableInDraggableRect+"&thumb="+jQuery('#info-img').attr("src")+"&link="+globallink;
		url = "bidbuyland.php?type=buy&thumb="+jQuery('#info-img').attr("src")+"&link="+globallink;
	}
	else {
		//url = "bidbuyland.php?type=bid&land="+blocksAvailableInDraggableRect+"&thumb="+jQuery('#info-img').attr("src")+"&link="+globallink;
		url = "bidbuyland.php?type=bid&thumb="+jQuery('#info-img').attr("src")+"&link="+globallink;
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

/************************************** initialize **************************************/
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
	//updatePopupWindowTabConfig(false);
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
	//ajaxAddMarkers(map);
	
	self.setInterval(
		function(){
		if (map.getZoom() < 17){ //marker mode
			ajaxAddMarkers(map, true); //force ajax
		}
	
	}, 5*60*1000); //every 5 mins
	google.maps.event.addListener(map, 'click', function(event) { onClick(event); });
	google.maps.event.addListener(map, 'idle', function(event) { onIdle(false); });
	google.maps.event.addListener(map, 'zoom_changed', function() { onZoomChanged(); });

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
jQuery(function() {
	//alert('testsss');
	jQuery('#config_email').val(getCookie('user_email'));
	jQuery('.cpanelwnd').draggable();
	xleft = jQuery(window).width() - jQuery('.cpanelwnd').width();
	jQuery('.cpanelwnd').css({left: xleft, top: '60px'});
	jQuery("#radioset").buttonset();
	jQuery("#tabs").tabs();
	jQuery("#search_enteraplace").autocomplete({
		// this bit uses the geocoder to fetch address values
		source: function(request, response) {
			geocoder.geocode( {'address': request.term }, function(results, status) {
				response(jQuery.map(results, function(item) {
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
			jQuery("#latitude").val(ui.item.latitude);
			jQuery("#longitude").val(ui.item.longitude);
			var location = new google.maps.LatLng(ui.item.latitude, ui.item.longitude);
			//searchMarker.setPosition(location);
			map.setZoom(15);
			map.setCenter(location);
		}
	});
});

//alert('test here');
