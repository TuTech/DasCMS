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
	org.bambuscms.app.dialog.cancel()
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
			'controller':org.bambuscms.app.controller,
			'call':'provideAvailablePreviewImages'
		};
	var qobj = org.bambuscms.http.managementRequestURL(data);
	org.bambuscms.http.fetchJSONObject(
		qobj,
		org.bambuscms.wsettings.fillDialog
	);
	div = $c('div');
	div.setAttribute('id','wsettings_img_select');
	div.innerHTML = '<img src="System/ClientData/Icons/16x16/animations/loading.gif" style="margin:10px auto 10px auto;display:block;" alt="'+_('loading')+'" title="'+_('loading')+'" />';
	var dlg = org.bambuscms.app.dialog.create(_('select_preview_image'), '', div, false, 'Close');
	dlg.id = 'wsetting_image_picker';
	dlg.style.top = '150px';
	dlg.style.left = '225px';
	org.bambuscms.wsettings.selector = dlg;
	org.bambuscms.display.setAutosize('wsetting_image_picker', -100, -100, true);
	org.bambuscms.display.setAutosize('wsettings_img_select', 0, -175, true);
}
org.bambuscms.wsettings.fillDialog = function (dataObject)
{
	var target = $('wsettings_img_select');
	var current = $('WSearch-PreviewImage-Alias').value;
	if(target)
	{
		target.innerHTML = '';
		for(alias in dataObject.images)
		{
			var img = $c('img');
			img.src = dataObject.renderer + '/' + alias + '/' + dataObject.scaleHash;
			img.alt = alias;
			img.title = dataObject.images[alias];
			if(alias == current)
			{
				img.className = 'wsettings_current';
			}
			org.bambuscms.gui.setEventHandler(img, 'click', org.bambuscms.wsettings.setImage)
			var inner = $c('div');
			inner.className = 'wsettings_inner';
			var outer = $c('div');
			outer.className = 'wsettings_outer';
			inner.appendChild(img);
			outer.appendChild(inner);
			target.appendChild(outer);
		}
	}
}

//WSearch-PubDate
org.bambuscms.wsettings.pubDateHelper = null;
org.bambuscms.wsettings.pubDateHelperTimeOut = null;
org.bambuscms.wsettings.setPubDate = function()
{
	if(org.bambuscms.wsettings.pubDateHelperTimeOut)
	{
		window.clearTimeout(org.bambuscms.wsettings.pubDateHelperTimeOut);
	}
	$('WSearch-PubDate').value = (this.title); 
	$('WSearch-PubDate').focus();
}

org.bambuscms.wsettings.showPubDateHelper = function()
{
	var dd = function(val)
	{
		return (val < 10) ? '0'+val : val;
	};
	if(org.bambuscms.wsettings.pubDateHelper)
		return;
	var helps;
	if($('WSearch-PubDate').value == '')
	{
		var d = new Date();
		var t = _('publish_now');
		helps = {};
		//y-m-d h:m:s
		helps[t] = d.getFullYear()+'-'+dd(d.getMonth())+'-'+dd(d.getDay())+
					' '+dd(d.getHours())+':'+dd(d.getMinutes())+':'+dd(d.getSeconds());
	}
	else
	{
		var t = _('revoke_publication');
		helps = {};
		helps[t] = '';
	}
	var content = $c('div');
	for(caption in helps)
	{
		var help = $c('b');
		help.title = helps[caption];
		help.innerHTML = caption;
		org.bambuscms.gui.setEventHandler(help, 'click', org.bambuscms.wsettings.setPubDate);
		content.appendChild(help);
	}
	org.bambuscms.wsettings.pubDateHelper = org.bambuscms.app.helper.create('WSearch-PubDate', content);
	org.bambuscms.wsettings.pubDateHelper.style.width = '200px';
}

org.bambuscms.wsettings.hidePubDateHelper = function()
{
	if(org.bambuscms.wsettings.pubDateHelper)
	{
		org.bambuscms.app.helper.remove(org.bambuscms.wsettings.pubDateHelper);
		window.clearTimeout(org.bambuscms.wsettings.pubDateHelperTimeOut);
		org.bambuscms.wsettings.pubDateHelper = null;
	}
}
org.bambuscms.wsettings.scheduleHidePubDateHelper = function()
{
	if(org.bambuscms.wsettings.pubDateHelper)
	{
		org.bambuscms.wsettings.pubDateHelperTimeOut = 
			window.setTimeout('org.bambuscms.wsettings.hidePubDateHelper()', 300);
	}
}

org.bambuscms.autorun.register(function(){
	if($('WSearch-PubDate'))
	{
		org.bambuscms.gui.setEventHandler($('WSearch-PubDate'), 'focus', org.bambuscms.wsettings.showPubDateHelper);
		org.bambuscms.gui.setEventHandler($('WSearch-PubDate'), 'blur', org.bambuscms.wsettings.scheduleHidePubDateHelper);
	}
});

//WSearch-PreviewImage
org.bambuscms.autorun.register(function(){
	if($('WSearch-PreviewImage'))
	{
		org.bambuscms.gui.setEventHandler($('WSearch-PreviewImage'), 'click', org.bambuscms.wsettings.selectImage);
	}
});


