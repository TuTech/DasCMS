
function Create()
{
	input = document.createElement('input');
	input.setAttribute('onkeyup','org.bambuscms.validators.filename(this);');
	input.setAttribute('onchange','org.bambuscms.validators.filename(this);');
	input.setAttribute('onblur','org.bambuscms.validators.filename(this);');
	input.setAttribute('name','new_nav_name');
	input.setAttribute('type','text');
	input.setAttribute('value','');
		
	DialogContainer('Create navigation', 'filename for the new navigation:', input, 'OK', 'Cancel');
	input.focus();
}
function Delete()
{
	input = document.createElement('input');
	input.setAttribute('name','delete_nav');
	input.setAttribute('type','hidden');
	input.setAttribute('value','yes');
		
	DialogContainer('Delete navigation', 'Do you really want to delete this navigation', input, 'Yes', 'No');
}



var DivID = 1;
var DEBUG = false;

function addSibling(toElementId, cid, cttl)
{
	if(cid == undefined)
	{
		cid = '';
		cttl = '';
	}
	toElement = document.getElementById(toElementId);
	parentDiv = document.getElementById(document.getElementById(toElementId+'_p').value);
	nextDiv = (getTNext(toElement) != '') ? document.getElementById(getTNext(toElement)) : null;
	newDiv = createNavDiv(parentDiv, nextDiv , cid,cttl);
	setTNext(toElement, newDiv.id);
	if(nextDiv ==  null)
	{
		toElement.parentNode.appendChild(newDiv);
	}	
	else
	{
		toElement.parentNode.insertBefore(newDiv, nextDiv);
	}
	document.getElementById(newDiv.id+'_ct').focus();
}
function addChild(toElementId, cid, cttl)
{
	if(cid == undefined)
	{
		cid = '';
		cttl = '';
	}
	toElement = document.getElementById(toElementId);
	parentDiv = toElement;
	if(document.getElementById(toElement.id+'_fc'))
	{
		nextDiv = document.getElementById(document.getElementById(toElement.id+'_fc').value);
	}
	else
	{
		nextDiv = null;
	}
	newDiv = createNavDiv(parentDiv, nextDiv , cid,cttl);
	
	if(nextDiv == null)
	{
		toElement.appendChild(newDiv);
	}
	else
	{
		toElement.insertBefore(newDiv, nextDiv);
	}
	document.getElementById(toElement.id+'_fc').value = newDiv.id;
	document.getElementById(newDiv.id+'_ct').focus();
}

function createNavDiv(parentDiv, nextDiv , c_id, c_title)
{
	//our id
	var Id = ++DivID;
	
	//nav crap container
	var CDiv = document.createElement('div');
	CDiv.setAttribute('id', Id);
	CDiv.setAttribute('class', 'navObject');

	var BRemoveContent = document.createElement('img');
	BRemoveContent.setAttribute('src', 'System/Icons/16x16/actions/delete.png');
	BRemoveContent.setAttribute('alt', 'remove content');
	BRemoveContent.setAttribute('title', 'remove content');
	BRemoveContent.setAttribute('onclick', 'removeContentFrom(\''+Id+'\')');

	//Content ID
	var TContentId = document.createElement('input');
	TContentId.setAttribute('id', Id+'_cid');
	TContentId.setAttribute('type', DEBUG ? 'text' : 'hidden');
	TContentId.setAttribute('name', Id+'_cid');
	TContentId.setAttribute('value', c_id);
	
	//Content title - just to help the user
	var TContentTitle = document.createElement('input');
	TContentTitle.setAttribute('id', Id+'_ct');
	TContentTitle.setAttribute('value', c_title);
	TContentTitle.setAttribute('type', 'text');
	TContentTitle.setAttribute('onfocus', 'lastFocus=\''+Id+'\';');
	TContentTitle.setAttribute('readonly', 'readonly');
	
	//child div id - created elements do not have children
	var TFirstChild = document.createElement('input');
	TFirstChild.setAttribute('id', Id+'_fc');
	TFirstChild.setAttribute('name', Id+'_fc');
	TFirstChild.setAttribute('value', '');
	TFirstChild.setAttribute('type', DEBUG ? 'text' : 'hidden');
	
	//id of next sibling
	var TNext = document.createElement('input');
	TNext.setAttribute('id', Id+'_n');
	TNext.setAttribute('name', Id+'_n');
	TNext.setAttribute('value', (nextDiv != null) ? nextDiv.id : '');
	TNext.setAttribute('type', DEBUG ? 'text' : 'hidden');
	
	//id of prenting div
	var TParent = document.createElement('input');
	TParent.setAttribute('id', Id+'_p');
	TParent.setAttribute('name', Id+'_p');
	TParent.setAttribute('value', parentDiv.id);
	TParent.setAttribute('type', DEBUG ? 'text' : 'hidden');
	
	//link to add child
	var BAddChild = document.createElement('a');
	var BAddChildLabel = document.createTextNode(' ');
	BAddChild.setAttribute('href', 'javascript:addChild(\''+Id+'\');');
	BAddChild.setAttribute('title', 'add child');
	BAddChild.setAttribute('class', 'add_child');
	BAddChild.appendChild(BAddChildLabel);
	
	//link to add sibling
	var BAddSibling = document.createElement('a');
	var BAddSiblingLabel = document.createTextNode(' ');
	BAddSibling.setAttribute('href', 'javascript:addSibling(\''+Id+'\');');
	BAddSibling.setAttribute('title', 'add sibling');
	BAddSibling.setAttribute('class', 'add_sibling');
	BAddSibling.appendChild(BAddSiblingLabel);
	
	OwnId = document.createTextNode(Id);
	ElementId = document.createElement('b');
	ElementId.appendChild(OwnId);
	
	
	CDiv.appendChild(BAddSibling);
	CDiv.appendChild(BAddChild);
	if(DEBUG)CDiv.appendChild(ElementId);
	if(DEBUG)CDiv.appendChild(document.createElement('b').appendChild(document.createTextNode('f:')));
	CDiv.appendChild(TFirstChild);
	if(DEBUG)CDiv.appendChild(document.createElement('b').appendChild(document.createTextNode('n:')));
	CDiv.appendChild(TNext);
	if(DEBUG)CDiv.appendChild(document.createElement('b').appendChild(document.createTextNode('p:')));
	CDiv.appendChild(TParent);
	CDiv.appendChild(TContentId);
	CDiv.appendChild(TContentTitle);
	CDiv.appendChild(BRemoveContent);
	
	lastFocus = Id;
	
	return CDiv;
}

//get content of a divs "parent" input
function getTParent(div)
{
	return getT(div, '_p');
}

//get content of a divs "next" input
function getTNext(div)
{
	return getT(div, '_n');
}

//get content of a divs "first child" input
function getTFirstChild(div)
{
	return getT(div, '_fc');
}
//set content of a divs "parent" input
function setTParent(div, value)
{
	return setT(div, '_p', value);
}

//set content of a divs "next" input
function setTNext(div, value)
{
	return setT(div, '_n', value);
}

//set content of a divs "first child" input
function setTFirstChild(div, value)
{
	return setT(div, '_fc', value);
}

function getT(div, opt)
{
	T = ''; 
	if(document.getElementById(div.id+opt))
	{
		T = document.getElementById(div.id+opt).value;
	}
	return T;
}

function setT(div, opt, value)
{
	T = ''; 
	if(document.getElementById(div.id+opt))
	{
		document.getElementById(div.id+opt).value = value;
		return true;
	}
	return false;
}














function removeContentFrom(id)
{
	document.getElementById(id+'_cid').value = '';
	document.getElementById(id+'_ct').value = '';
}




var lastFocus = null;
function insertMedia(type, id, title)
{
	if(lastFocus != null)
	{
		if(type == 'content')
		{
			document.getElementById(lastFocus+'_cid').value = id;
			document.getElementById(lastFocus+'_ct').value = title;
		}
		document.getElementById(lastFocus+'_ct').focus();
	}//else alert('nofoc');
}
