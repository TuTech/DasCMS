function validateField(field, method)
{
	var regexp = /[*]?/;
	if(method == 'spore')
	{
		regexp = /(^[a-zA-Z0-9]+[a-zA-z0-9_]?$)/;
	}
	else if(method == 'alnum')
	{
		regexp = /^[a-zA-Z0-9]+$/;
	}
	else if(method == 'filename')
	{
		regexp = /^[a-zA-Z0-9\-_\.]+$/;
	}
	
	if(field.value == '')
	{
		field.className = ''; 
	}
	else if(field.value.match(regexp))
	{
		field.className = 'validField';
	}
	else
	{
		field.className = 'invalidField';
	}
}