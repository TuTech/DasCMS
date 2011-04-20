//sorting with binary tree sort 
//default compare function is case insensitive compare
org.bambuscms.sort = function(data, compareFunction)
{
	compareFunction = compareFunction || function(a,b)
	{
		if(typeof a == "string"){a = a.toUpperCase()}
		if(typeof b == "string"){b = b.toUpperCase()}
		return a <= b;
	};
	var tree = new org.bambuscms.bintree(compareFunction);
	for(var i = 0; i < data.length; i++)
	{
		tree.add(data[i]);
	}
	return tree.toArray();
};