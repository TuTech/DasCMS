//needs:
//org.js

//define bambus object
org.bambuscms = {
	onload: [],
	onunload: [],
};

//define bambus methods
org.bambuscms.addLoadListener = function(listener)
{
	if(listener && typeof listener == "function")
	{
		org.bambuscms.onload[org.bambuscms.onload.length] = listener;
	}
}
org.bambuscms.addUnLoadListener = function(listener)
{
	if(listener && typeof listener == "function")
	{
		org.bambuscms.onunload[org.bambuscms.onunload.length] = listener;
	}
}
org.bambuscms.executeLoadListeners = function()
{
	for(var i = 0; i < org.bambuscms.onload.length; i++)
	{
		try
		{
			org.bambuscms.onload[i]();
		}
		catch(e){/* ignore */}
	}
}
org.bambuscms.executeUnLoadListeners = function()
{
	for(var i = 0; i < org.bambuscms.onunload.length; i++)
	{
		try
		{
			org.bambuscms.onunload[i]();
		}
		catch(e){/* ignore */}
	}
}
org.bambuscms.registerEvent = function()
{
//@todo
/////////////7
}


//link functions

