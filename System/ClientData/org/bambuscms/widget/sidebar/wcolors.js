org.bambuscms.wcolors = {};
org.bambuscms.wcolors.interval = null;
org.bambuscms.wcolors.intervalTime = 2000;
org.bambuscms.wcolors.run = function(){
	var text = $(org.bambuscms.app.document.editorElementId).value;
	var regexp = /#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})[^a-fA-F0-9]/g;
	var res, c;
	var colors = {};
	while((res = regexp.exec(text)) != null)
	{
		c = res[1].toUpperCase();
		if(colors[c])
		{
			colors[c].used++;
		}
		else
		{
			if(c.length == 3)
			{
				r = parseInt(c.substr(0,1)+c.substr(0,1),16);
				g = parseInt(c.substr(1,1)+c.substr(1,1),16);
				b = parseInt(c.substr(2,1)+c.substr(2,1),16);
			}
			else
			{
				r = parseInt(c.substr(0,2),16);
				g = parseInt(c.substr(2,2),16);
				b = parseInt(c.substr(4,2),16);
			}
			colors[c] = {
				'used':1,
				'r':r,
				'g':g,
				'b':b,
				'l':(r+g+b),
				'hex': c
			};
		}
	}
	var out = '';
	var textColor;
	var sorted = [];
	for(color in colors)
	{
		sorted[sorted.length] = colors[color];
	}
	sorted.sort(org.bambuscms.wcolors.lumiSort);
	for(var i = 0; i < sorted.length; i++)
	{
		color = sorted[i];
		cssClass = (color.l < 192) ? 'WColors-light' : 'WColors-dark';
		out += '<div style="background:#'+color.hex+';" class="'+cssClass+'" onclick="org.bambuscms.app.document.insertText(\'#'+color.hex+'\');">'+
				'#'+color.hex+' ('+color.used+')</div>\n';
	}
	$('WColors-area').innerHTML = out;
};
org.bambuscms.wcolors.lumiSort = function(a, b){
	return b.l - a.l;
};
org.bambuscms.wcolors.load = function(){
	if($(org.bambuscms.app.document.editorElementId) && $('WColors-area'))
	{
		org.bambuscms.wcolors.run();
		org.bambuscms.wcolors.interval = window.setInterval(org.bambuscms.wcolors.run, org.bambuscms.wcolors.intervalTime);
	}
};
org.bambuscms.autorun.register(org.bambuscms.wcolors.load);