org.bambuscms.bintree = function(func)
{
	this.compareFunction = func;
	this.left = null;
	this.right = null;
	this.value = null;
};
org.bambuscms.bintree.prototype.toArray = function(arr)
{
	//init array if this is the root call
	data = arr || new Array();
	//first go through left branch
	if(this.left != null)
	{
		this.left.toArray(data);
	}
	//append own data
	if(this.value != null)
	{	
		data[data.length] = this.value;
	}
	//go through right branch
	if(this.right != null)
	{
		this.right.toArray(data);
	}
	return data;
};
org.bambuscms.bintree.prototype.add = function(val)
{
	if(this.value == null)
	{
		this.value = val;
	}
	else
	{
		//sort fx decides if left (true) or right
		if(this.compareFunction(val, this.value))
		{
			if(this.left == null)
			{
				this.left = new org.bambuscms.bintree(this.compareFunction);
			}
			//pass to lower node
			this.left.add(val);
		}
		else
		{
			//now for the right side
			if(this.right == null)
			{
				this.right = new org.bambuscms.bintree(this.compareFunction);
			}
			this.right.add(val);
		}
	}
};
