org.bambuscms.app.scheduler = {};
org.bambuscms.app.scheduler._reqid = 0;
org.bambuscms.app.scheduler.image = $c('img');
org.bambuscms.app.scheduler.image.style.display = 'none';
//org.bambuscms.app.scheduler.image.style.position = 'absolute';
//org.bambuscms.app.scheduler.image.style.zIndex = 1000000;
//org.bambuscms.app.scheduler.image.style.top = '5px';
//org.bambuscms.app.scheduler.image.style.right = '5px';
org.bambuscms.app.scheduler.run = function()
{
	org.bambuscms.app.scheduler.image.src = 'scheduler.php?'+(++org.bambuscms.app.scheduler._reqid);
};

org.bambuscms.app.scheduler.init = function()
{
	var d = new Date();
	org.bambuscms.app.scheduler._reqid = d.getTime();
	$('bambusJAX').appendChild(org.bambuscms.app.scheduler.image);
	//run scheduler  
	var runnings = [2,5,10,20];//seconds after load
	for(var i = 0; i < runnings.length; i++)
	{
		window.setTimeout('org.bambuscms.app.scheduler.run()', runnings[i]*1000);
	}
};
(function(){
	org.bambuscms.autorun.register(function(){org.bambuscms.app.scheduler.init();});
})();