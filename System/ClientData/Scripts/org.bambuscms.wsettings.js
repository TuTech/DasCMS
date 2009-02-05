org.bambuscms.wsettings = {};
org.bambuscms.wsettings.selectImage = function()
{
	if($('WSearch-PreviewImage-Alias'))
	{
		$('WSearch-PreviewImage-Alias').value = prompt('please insert alias of preview image', $('WSearch-PreviewImage-Alias').value);
		
	}
	else
	{alert( 'preview can not be set');}
}

org.bambuscms.autorun.register(function(){
	if($('WSearch-PreviewImage'))
	{
		org.bambuscms.gui.setEventHandler($('WSearch-PreviewImage'), 'click', org.bambuscms.wsettings.selectImage);
	}
});