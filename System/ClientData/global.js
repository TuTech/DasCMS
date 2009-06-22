function $(id)
{
	return document.getElementById(id);
}
function $c(tag, subNodes)
{
	var node = document.createElement(tag);
	if(subNodes)
	{
		if(typeof subNodes == 'object' && subNodes.ownerDocument)
		{
			subNodes = [subNodes];
		}
		if(typeof subNodes == 'object')
		{
			for(k in subNodes)
			{
				if(subNodes[k].ownerDocument)
					node.appendChild(subNodes[k]);
			}
		}
	}
	return node;
}
function $t(text)
{
	return document.createTextNode(text.replace(/\_/g, '_\u00AD'));
}
function nil(){}
