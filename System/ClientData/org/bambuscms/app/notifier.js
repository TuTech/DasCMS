org.bambuscms.app.notifier = {};
org.bambuscms.app.notifier.elementId = 'notifier';
org.bambuscms.app.notifier.hide = function(runTotal, runCurrent, beginFadeout, intervall)
{
	runCurrent = Math.min(runCurrent, runTotal);
	if(runCurrent < runTotal)
	{
		window.setTimeout("org.bambuscms.app.notifier.hide("+runTotal+","+(runCurrent+intervall)+","+beginFadeout+","+intervall+")",intervall);
	}
	//show timeout in #nfcTimeoutbarIndicator
	var len,notifier;
	if(runCurrent >= beginFadeout)
	{
		//fade stuff
		notifier = 	$(org.bambuscms.app.notifier.elementId);
		if(runTotal > runCurrent)
		{
			len = 100/(runTotal-beginFadeout)*(runCurrent-beginFadeout);
			notifier.style.opacity = (1.0 - len/100);
		}
		else
		{
			notifier.style.display = 'none';
		}
	}
	
};