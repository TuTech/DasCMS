org.bambuscms.weventplanner = {};
org.bambuscms.weventplanner._controller = 'org.bambuscms.plugin.ueventplanner';
org.bambuscms.weventplanner._query = function(call, param, callback)
{
	var data = {
		'controller':org.bambuscms.weventplanner._controller,
		'call':call
	};
	param = (param && typeof param == 'object') ? param : {};
	callback = (callback && typeof callback == 'function') ? callback : function(){};
	param.alias = org.bambuscms.app.document.alias;
	send = org.json.stringify(param);
	var qobj = org.bambuscms.http.managementRequestURL(data);
	org.bambuscms.http.fetchJSONObject(
		qobj,
		callback,
		send
	);
};

org.bambuscms.weventplanner.removeSchedule = function(begin, end){
	if(confirm(_('remove_scheduled_event')))
	{
		org.bambuscms.weventplanner._query(
			'removeEvent', 
			{'begin':begin,'end':end}, 
			org.bambuscms.weventplanner._eventCallBack
		);
	}
};

org.bambuscms.weventplanner._makeList = function(respObj)
{
	var list = $('WEventPlanner_Events');
	var i = 0;
	while(list.hasChildNodes())
		list.removeChild(list.firstChild);
	if(respObj.items)
	{
		for(var k in respObj.items)
		{
			var b,e,d,a;
			b = $c('span', $t(respObj.items[k].b));
			e = $c('span', $t(respObj.items[k].e));
			a = $c('b', $t(_('X')));
			a.style.cursor = 'pointer';
			a.setAttribute('onclick', 'org.bambuscms.weventplanner.removeSchedule(this.nextSibling.firstChild.data,this.nextSibling.nextSibling.firstChild.data);');
			b.className = 'wepe_begin';
			e.className = 'wepe_end';
			
			d = $c('div', [a,b,e]);
			d.className = (i++%2) ? 'alt' : '';
			list.appendChild(d);
		}
	}
};
org.bambuscms.weventplanner.show = function()
{
	//load
	org.bambuscms.weventplanner._query('listEvents', null, org.bambuscms.weventplanner._makeList);
};
org.bambuscms.weventplanner._eventCallBack = function(respObj)
{
	if(respObj && respObj.success == 0)
	{
		alert(_(respObj.message));
	}
	else
	{
		org.bambuscms.weventplanner.show();
	}
}

org.bambuscms.weventplanner.hide = function()
{
};
org.bambuscms.weventplanner.scheduleEvent = function(begin, end)
{
	org.bambuscms.weventplanner._query(
		'scheduleEvent', 
		{'begin':begin,'end':end}, 
		org.bambuscms.weventplanner._eventCallBack
	);
};

