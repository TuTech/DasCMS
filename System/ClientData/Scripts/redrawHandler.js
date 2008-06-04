///////////////////
//Redraw handling//
///////////////////

//vars
var aRedrawIds      = new Array();
var aRedrawXOffset  = new Array();
var aRedrawYOffset  = new Array();

//catch redraw event
window.onresize = Redraw;

//handle redraw events
function Redraw()
{
	var redrawToX, redrawToY, innerWidth, innerHeight;
	if (window.innerWidth) 
	{
    	innerWidth = window.innerWidth;
    	innerHeight = window.innerHeight;
  	} 
  	else if (document.body && document.body.offsetWidth) 
  	{
	    innerWidth = document.body.offsetWidth;
	    innerHeight = document.body.offsetHeight;
	}
	//resize every element in the aRedrawIds array
	for(var i = 0; i < aRedrawIds.length; i++)
	{
		if(aRedrawIds[i] != false && document.getElementById(aRedrawIds[i]))
		{
			redrawId = document.getElementById(aRedrawIds[i]);
			redrawToX = 0;
			redrawToY = 0;
			
			if(aRedrawXOffset[i] < 1.0 && aRedrawXOffset[i] > 0.0)
				redrawToX = Math.round(innerWidth  * aRedrawXOffset[i]);
			else if(aRedrawXOffset[i] > 0)
				redrawToX = innerWidth  - aRedrawXOffset[i];
			else
				redrawToX = aRedrawXOffset[i];
			
			if(aRedrawYOffset[i] < 1.0 && aRedrawYOffset[i] > 0.0)
				redrawToY = Math.round(innerHeight  * aRedrawYOffset[i]);
			else if(aRedrawYOffset[i] > 0)
				redrawToY = innerHeight  - aRedrawYOffset[i];
			else
				redrawToY = aRedrawYOffset[i];
			
			if(redrawToX > 0)redrawId.style.width  = redrawToX+'px';
			if(redrawToY > 0)redrawId.style.height = redrawToY+'px';
			
			if(redrawToX < 0)redrawId.style.width  = innerWidth+'px';
			if(redrawToY < 0)redrawId.style.height = innerHeight+'px';
		}
	}
}

//add elements to the redraw list
function RedrawAddId(id, xOffset, yOffset)
{
	RedrawRemoveId(id); //do not resize twice 
	var addAt = aRedrawIds.length;
	aRedrawIds[addAt] = id;
	aRedrawXOffset[addAt] = xOffset;
	aRedrawYOffset[addAt] = yOffset;
}

//change elements in the redraw list
function RedrawChangeId(id, xOffset, yOffset)
{
	for(var i = 0; i < aRedrawIds.length; i++)
	{	
		if(aRedrawIds[i] == id)
		{
			aRedrawXOffset[i] = xOffset;
			aRedrawYOffset[i] = yOffset;
		}
	}
}

//remove elements from the redraw list
function RedrawRemoveId(id)
{
	for(var i = 0; i < aRedrawIds.length; i++)
	{	
		if(aRedrawIds[i] == id)
		{
			aRedrawIds[i] = false;
		}
	}
}
