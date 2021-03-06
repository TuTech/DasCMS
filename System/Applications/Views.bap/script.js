function rebuildAliasDB()
{
	var div = $c('div');
	var p = $c('p');
	var p2 = $c('p');
	var input = $c('input');
	p.appendChild($t(
		"Bookmarks may be invalid afterwards. "+
		"This should only be done if you have a lot of '...~number' aliases  or "+
		"a lot of aliases blocked by other items caused by some renamings."));
	input.setAttribute('name','rebuildAliasDatabase');
	input.setAttribute('type','hidden');
	input.setAttribute('value','yes');
	div.appendChild(p);
	div.appendChild(input);
	org.bambuscms.app.dialog.create('Rebuild alias database', 'Do you really want to rebuild the alias database?', div, 'Yes', 'No');
}

var lastFocus = null;
org.bambuscms.app.document.insertMedia = function(type, id, title)
{
	if(lastFocus != null)
	{
		if(type == 'content')
		{
			document.getElementById(lastFocus).value = id;
			document.getElementById(lastFocus+'_t').value = title+' ('+id+')';
		}
		document.getElementById(lastFocus+'_t').focus();
	}
};
function clearOpt(id)
{
	document.getElementById(id).value = '';
	document.getElementById(id+'_t').value = '';
	document.getElementById(id+'_t').focus();
}

function toggleSporeRemove(sporeName)
{
	var flagInput = document.getElementById('spore_'+sporeName);
	var sporeText = document.getElementById('spore_'+sporeName+'_t');
	if(flagInput.value == '')
	{
		//set remove flag
		flagInput.value = '-';
		sporeText.className = 'removedSpore';
		document.getElementById('spore_'+sporeName+'_rm').style.display = 'none';
		document.getElementById('spore_'+sporeName+'_norm').style.display = 'inline';
	}
	else
	{
		//remove remove flag
		flagInput.value = '';
		sporeText.className = '';
		document.getElementById('spore_'+sporeName+'_rm').style.display = 'inline';
		document.getElementById('spore_'+sporeName+'_norm').style.display = 'none';
	}
}