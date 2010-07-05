function Upload(reshowParam, publishParam)
{
	var div = $c('div');

	var finput = $c('input');
	finput.setAttribute('name','CFile');
	finput.setAttribute('type','file');
	finput.onchange = function(){
		this.style.display = 'none';
		this.parentNode.className += ' sending_ani_bg';
		$('dialogueform').submit();
		var as = $('dialogues').getElementsByTagName('a');
		for(var i = 0; i < as.length; i++)
			as[i].style.display = 'none';
	};

	var minput = $c('input');
	minput.setAttribute('name','MAX_FILE_SIZE');
	minput.setAttribute('type','hidden');
	minput.setAttribute('value','1000000000');
	var action = 'create'
	if(is_in_content_mode && !org.bambuscms.wopenfiledialog.shown())
	{
		var opt = $c('input');
		opt.setAttribute('type','checkbox');
		opt.id = 'update_file_content';
		opt.checked = !reshowParam;
		action = opt.checked ? 'save' : 'create';
		opt.onchange = function(){org.bambuscms.app.dialog.setAction($('update_file_content').checked ? 'save' : 'create');};

		var olb = $c('label');
		olb.setAttribute('for', 'update_file_content');
		olb.appendChild($t(_('update_data_of_current_file')));

		var obox = $c('div');
		obox.appendChild(opt);
		obox.appendChild(olb);
		div.appendChild(obox);
	}

	div.appendChild(finput);
	div.appendChild(minput);

	//reshow dialogue
	var showThisDlgAfterUploadTitle = $c('label');
	showThisDlgAfterUploadTitle.setAttribute('for','reshow_upload_dialogue');
	showThisDlgAfterUploadTitle.appendChild($t(_('show_this_dialogue_again_after_uploading')));

	var showThisDlgAfterUpload = $c('input');
	showThisDlgAfterUpload.setAttribute('type','checkbox');
	showThisDlgAfterUpload.setAttribute('name','reshow_upload_dialogue');
	showThisDlgAfterUpload.setAttribute('id','reshow_upload_dialogue');
	showThisDlgAfterUpload.checked = !!reshowParam;

	var showCon = $c('div');
	showCon.appendChild(showThisDlgAfterUpload);
	showCon.appendChild(showThisDlgAfterUploadTitle);
	div.appendChild(showCon);

	//publish
	var autoPublishTitle = $c('label');
	autoPublishTitle.setAttribute('for','autopublish_upload');
	autoPublishTitle.appendChild($t(_('publish_this_file_now')));

	var autoPublish = $c('input');
	autoPublish.setAttribute('type','checkbox');
	autoPublish.setAttribute('name','autopublish_upload');
	autoPublish.setAttribute('id','autopublish_upload');
	autoPublish.checked = !!publishParam;

	var pubCon = $c('div');
	pubCon.appendChild(autoPublish);
	pubCon.appendChild(autoPublishTitle);
	div.appendChild(pubCon);

	org.bambuscms.app.dialog.create(_('upload_file'), '', div, null, _('cancel'), true);
	org.bambuscms.app.dialog.setAction(action);
}

function Delete()
{
	input = $c('input');
	input.setAttribute('name','delete');
	input.setAttribute('type','hidden');
	input.setAttribute('value','yes');

	org.bambuscms.app.dialog.create(_('delete_file'), _('do_you_really_want_to_delete_this_file'), input, _('yes'), _('no'));
	org.bambuscms.app.dialog.setAction('delete');
}

function MassDelete()
{
	var del = '';
	var sep = '';
	var inputs = $('BambusApplication').getElementsByTagName('input');
	for(var i = 0; i < inputs.length; i++)
	{
		if(inputs[i].type == 'checkbox' && inputs[i].checked)
		{
			del += sep+inputs[i].id.substr(7,inputs[i].id.length);
			sep = ';';
		}
	}
	input = $c('input');
	input.setAttribute('name','delete');
	input.setAttribute('type','hidden');
	input.value = del;
	org.bambuscms.app.dialog.create(_('delete_file'), _('do_you_really_want_to_delete_this_file'), input, _('yes'), _('no'));
	org.bambuscms.app.dialog.setAction('massDelete');
}
/*****************************/
function selectImage(id)
{
	var image = document.getElementById(id);
	var select = document.getElementById('select_'+id);
	if(!select.checked)
	{
		image.style.background = org.bambuscms.app.primarySelectedObjectColor;
		select.checked = true;
	}
	else
	{
		image.style.background = '#fff';
		select.checked = false;
	}
}
function selectItems(allOrNone)
{
	var check, background;
	if(allOrNone)
	{
		check = true;
		background = org.bambuscms.app.primarySelectedObjectColor;
	}
	else
	{
		check = false;
		background = '#fff';
	}
	inputs = document.getElementsByTagName('input');
	var parent = '';
	for(var i = 0; i < inputs.length; i++)
	{
		if(inputs[i].name.substr(0,7) == 'select_')
		{
			inputs[i].checked = check;
			parent = inputs[i].name;
			parent = parent.replace(/select_/, "");
			document.getElementById(parent).style.background = background;
		}
	}
}
function downloadSelected(path)
{
	inputs = document.getElementsByTagName('input');
	if(!document.getElementById('downloadIFrames'))
	{
		document.getElementById("bambusJAX").innerHTML += '<div id="downloadIFrames" />';
	}
	else
	{
		document.getElementById('downloadIFrames').innerHTML = '';
	}
	var id;
	for(var i = 0; i < inputs.length; i++)
	{
		if(inputs[i].name.substr(0,7) == 'select_' &&inputs[i].checked)
		{
			id = inputs[i].name.replace(/select_/, "");
			document.getElementById('downloadIFrames').innerHTML += '<iframe src="file.php?get='+escape(id)+'&amp;download=1" class="downloadIFrame" />';
		}
	}
}
