org.bambuscms.validators = {
	'spore':function(field)
	{
		regexp = /(^[a-zA-z0-9_]+$)/;
		org.bambuscms.validators._impl(field, regexp);
	},
	'alnum':function(field)
	{
		regexp = /^[a-zA-Z0-9]+$/;
		org.bambuscms.validators._impl(field, regexp);
	},
	'filename':function(field)
	{
		regexp = /^[a-zA-Z0-9\-_\.]+$/;
		org.bambuscms.validators._impl(field, regexp);
	},
	'_impl':function(field, regexp)
	{
		if(field.value == '')
		{
			field.className = ''; 
		}
		else
		{
			field.className = (field.value.match(regexp))
				? 'validField'
				: 'invalidField';
		}
	}
};