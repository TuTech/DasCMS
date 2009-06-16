org.bambuscms.http = {};

//build xmlhttp request objects
org.bambuscms.http.requestFactory = function()
{
	var factories = [
		function(){return new XMLHttpRequest(); },
		function(){return new ActiveXObject("Msxml2.XMLHTTP"); },
		function(){return new ActiveXObject("Microsoft.XMLHTTP"); }
	];
	for(var i = 0; i < factories.length; i++)
	{
		try
		{
			var factory = factories[i];
			var request = factory();
			if(request != null)
			{
				org.bambuscms.http.requestFactory = factory;
				return request;
			}
		}
		catch(e)
		{
			continue;
		}
	}
	org.bambuscms.http.requestFactory = function()
	{
		throw new Error("XMLHttpRequst not supported");
	}
	org.bambuscms.http.requestFactory();
};

//read data from url
org.bambuscms.http.fetch = function(url, asyncHandler, data)
{
	if(typeof url == 'object')
	{
		url = org.bambuscms.http.managementRequestURL(url);
	}
	var method = 'POST';
	if(!data)
	{
		method = 'GET';
		data = null;
	}
	var async = (asyncHandler && typeof asyncHandler == 'function') ? true : false;
	var request = org.bambuscms.http.requestFactory();
	//alert(async);
	request.open(method, url, async);
	if(async)
	{
		request.onreadystatechange = function()
		{
			if(request.readyState == 4)
			{
				asyncHandler(request);
			}
		}
		request.send(data);
	}
	else
	{
		request.send(data);
		return request;
	}
};
org.bambuscms.http.fetchJSONObject = function(url, asyncHandler, data)
{
	async = asyncHandler && typeof asyncHandler == 'function';
	if(async)
	{
		var hdl = function(request)
		{
			var obj = null;
			if(request.responseText)
			{
				obj = org.json.parse(request.responseText);
			}
			if(obj == null || obj.error)
			{
				alert('ERROR\n------\n'+request.responseText);
			}
			asyncHandler(obj);
		};
		org.bambuscms.http.fetch(url, hdl, data);
	}
	else
	{
		var request = org.bambuscms.http.fetch(url, null, data);
		try
		{
			var obj = org.json.parse(request.responseText);
			if(obj.error)
			{
				alert('ERROR\n'+request.responseText);
			}
			return obj;
		}
		catch(e)
		{
			alert('EXCEPTION: '+e+'\n\nSERVER SENT:\n'+request.responseText);
		}
	}
};
org.bambuscms.http.managementRequestURL = function(data)
{
	var url = 'Management/ajaxhandler.php';
	if(typeof data != 'object')
	{
		data = {};
	}
	var sep = '?';
	for(key in data)
	{
		url += sep+encodeURIComponent(key)+'='+encodeURIComponent(data[key]);
		sep = '&';
	}
	return url;
};
