//GLOBAL VARIABLES FOR ZOISHOW ^_^
slidestartcount = 1;
slidecount = 0;
slidepause = false;
slidedelay = 5000;
slidecancelledintervals = [];
slideintervalid = 0;

function opacity(id, opacStart, opacEnd, millisec) { 
    //speed for each frame 
    var speed = Math.round(millisec / 100); 
    var timer = 0; 

    //determine the direction for the blending, if start and end are the same nothing happens 
    if(opacStart > opacEnd) { 
        for(i = opacStart; i >= opacEnd; i--) { 
            setTimeout("changeOpac(" + i + ",'"+id+"')",(timer * speed)); 
            timer++; 
        } 
    } else if(opacStart < opacEnd) { 
        for(i = opacStart; i <= opacEnd; i++) 
            { 
            setTimeout("changeOpac(" + i + ",'"+id+"')",(timer * speed)); 
            timer++; 
        } 
    } 
} 

//change the opacity for different browsers 
function changeOpac(opacity, id) { 
    var object = document.getElementById(id).style; 
    //object = spoilobj.style;
    if(!object.width)
		object.width = "100%";
    object.opacity = (opacity / 100); 
    object.MozOpacity = (opacity / 100); 
    object.KhtmlOpacity = (opacity / 100); 
    object.filter = "alpha(opacity=" + opacity + ")"; 
    //alert(opacity);
} 
function slideSlideEm(){
	slideintervalid++;
	if(slidestartcount>slidecount)
		slidestartcount = 1;
	for(i=0; i<slidecount; i++){
		obj = document.getElementById("slideshow"+(i+1));
		obj.style.display = "none";
		slidelinkobj = document.getElementById("slide_link"+(i+1));
		slidelinkobj.className = "slide_link";
	}
	
	obj = document.getElementById("slideshow"+slidestartcount);
	slidelinkobj = document.getElementById("slide_link"+slidestartcount);
	slidelinkobj.className = "slide_hover";
	obj.style.display = "";
	id = obj.id;
	changeOpac(0, id)
	opacity(id, 0, 100, 1000);	
	setTimeout('if(!slidecancelledintervals['+slideintervalid+']){ opacity("'+id+'", 100, 0, 1000); slidestartcount++; slideSlideEm(); }', slidedelay);
}

function slideGoToSlide(n){
	slidecancelledintervals[slideintervalid] = true;
	slideintervalid++;
	slidestartcount = n;
	for(i=0; i<slidecount; i++){
		obj = document.getElementById("slideshow"+(i+1));
		obj.style.display = "none";
		slidelinkobj = document.getElementById("slide_link"+(i+1));
		slidelinkobj.className = "slide_link";		
	}
	
	obj = document.getElementById("slideshow"+slidestartcount);
	obj.style.display = "";
	slidelinkobj = document.getElementById("slide_link"+slidestartcount);
	slidelinkobj.className = "slide_hover";
	
	id = obj.id;
	changeOpac(0, id)
	opacity(id, 0, 100, 1000);	
	
	setTimeout('if(!slidecancelledintervals['+slideintervalid+']){ opacity("'+id+'", 100, 0, 1000); slidestartcount++; slideSlideEm(); }', slidedelay);
}
function startSlideShow(delay){
	//get all slideshow objects and put in an array
	slideshowarray = [];
	slidecount=1;
	slidedelay = delay;
	try{
		//just count divs with id = slideshow
		while(1)
		{
			slideobj = document.getElementById("slideshow"+slidecount);
			if(!slideobj){
				slidecount -= 1;
				break;
			}
			else{
				slidelinkobj = document.getElementById("slide_link"+slidecount);
				slidelinkobj.onmouseover = function(){
					this.className = "slide_hover";
				}
				slidelinkobj.onmouseout = function(){			
					for(i=0; i<slidecount; i++){
						slidelinkobj = document.getElementById("slide_link"+(i+1));
						slidelinkobj.className = "slide_link";		
					}
					slidelinkobj = document.getElementById("slide_link"+slidestartcount);
					slidelinkobj.className = "slide_hover";					
				}			
				slidecount++;
			}
			
		}
	}
	catch(e){
		alert(e.message);
	}
	slideSlideEm();
}