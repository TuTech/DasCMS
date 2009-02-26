org.bambuscms.wcolors = {};
org.bambuscms.wcolors.interval = null;
org.bambuscms.wcolors.intervalTime = 2000;
org.bambuscms.wcolors.run = function(){
	//org.bambuscms.wnotifications.report(org.bambuscms.wnotifications.INFORMATION, 'running');
	var text = $(org.bambuscms.app.document.editorElementId).value;
	var regexp = /#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})[^a-fA-F0-9]/g;//(#([a-zA-Z0-9]{3}|[a-zA-Z0-9]{6}))[\s,;]+/g;
	var res;// = text.match(regexp);
	var colors = {};
	var no;
	while((res = regexp.exec(text)) != null)
	{
		colors[res[1]] = (colors[res[1]] >= 1) ? (colors[res[1]]+1) : 1;
	}
	var out = '';
	var textColor;
	var min = '888888';
	for(color in colors)
	{
		textColor = (parseInt(color, 16) < parseInt(min.substr(0,color.length),16)) ? 'WColors-light' : 'WColors-dark';
		out += '<div style="background:#'+color+';" class="'+textColor+'" onclick="org.bambuscms.app.document.insertText(\'#'+color+'\');">#'+color+' ('+colors[color]+')</div>\n';
	}
	$('WColors-area').innerHTML = out;
	//org.bambuscms.wnotifications.report(org.bambuscms.wnotifications.INFORMATION, res[1]);

};
org.bambuscms.wcolors.load = function(){
	if($(org.bambuscms.app.document.editorElementId) && $('WColors-area'))
	{
		org.bambuscms.wcolors.run();
		org.bambuscms.wcolors.interval = window.setInterval(org.bambuscms.wcolors.run, org.bambuscms.wcolors.intervalTime);
	}
};
org.bambuscms.autorun.register(org.bambuscms.wcolors.load);