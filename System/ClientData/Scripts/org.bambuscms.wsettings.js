org.bambuscms.wsettings = {};
org.bambuscms.wsettings.updateImage = function()
{
	if($('WSearch-PreviewImage-Alias'))
	{
		var imgs = $('WSearch-PreviewImage').getElementsByTagName('img');
		imgs[0].src = 'image.php/' + ($('WSearch-PreviewImage-Alias').value) + '/80-60-0-f-ff-ff-ff';
	}
}
org.bambuscms.wsettings.setImage = function(e)
{
	var select = this.alt;
	closeDialog()
	if($('WSearch-PreviewImage-Alias'))
	{
		$('WSearch-PreviewImage-Alias').value = select;
		var imgs = $('WSearch-PreviewImage').getElementsByTagName('img');
		imgs[0].src = 'image.php/' + ($('WSearch-PreviewImage-Alias').value) + '/80-60-0-f-ff-ff-ff';
	}
}

org.bambuscms.wsettings.selectImage = function()
{
	if(!$('WSearch-PreviewImage-Alias'))
	{
		alert('cant change preview for this content type');
		return;
	}
	var data = {
			'controller':'org.bambuscms.applications.files',
			'call':'getAvailablePreviewImages'
		};
	var qobj = org.bambuscms.http.managementRequestURL(data);
	org.bambuscms.http.fetchJSONObject(
		qobj,
		org.bambuscms.wsettings.fillDialog
	);
	div = document.createElement('div');
	div.setAttribute('id','wsettings_img_select');
	div.innerHTML = '<img src="System/Icons/16x16/animations/loading.gif" style="margin:10px auto 10px auto;display:block;" alt="loading..." title="loading..." />';
	var dlg = DialogContainer('Select preview image', '', div, false, 'Close');
	dlg.id = 'wsetting_image_picker';
	dlg.style.top = '150px';
	dlg.style.left = '225px';
	org.bambuscms.wsettings.selector = dlg;
	org.bambuscms.display.setAutosize('wsetting_image_picker', -100, -100, true);
	org.bambuscms.display.setAutosize('wsettings_img_select', 0, -175, true);

}
org.bambuscms.wsettings.fillDialog = function (dataObject)
{
	//alert(dataObject.renderer);
	var target = $('wsettings_img_select');
	var current = $('WSearch-PreviewImage-Alias').value;
	if(target)
	{
		target.innerHTML = '';
		for(alias in dataObject.images)
		{
			var img = document.createElement('img');
			img.src = dataObject.renderer + '/' + alias + '/' + dataObject.scaleHash;
			img.alt = alias;
			img.title = dataObject.images[alias];
			if(alias == current)
			{
				img.className = 'wsettings_current';
			}
			org.bambuscms.gui.setEventHandler(img, 'click', org.bambuscms.wsettings.setImage)
			var inner = document.createElement('div');
			inner.className = 'wsettings_inner';
			var outer = document.createElement('div');
			outer.className = 'wsettings_outer';
			inner.appendChild(img);
			outer.appendChild(inner);
			target.appendChild(outer);
		}
	}
}

org.bambuscms.autorun.register(function(){
	if($('WSearch-PreviewImage'))
	{
		org.bambuscms.gui.setEventHandler($('WSearch-PreviewImage'), 'click', org.bambuscms.wsettings.selectImage);
	}
});


