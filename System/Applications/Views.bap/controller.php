<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');


if(RSent::has('rebuildAliasDatabase') && BAMBUS_GRP_ADMINISTRATOR)
{
	SNotificationCenter::report('message', 'rebuilding alias database');
	SAlias::alloc()->init()->rebuildAliases();
}
if(BAMBUS_APPLICATION_TAB == 'content_access')
{
	
	//side bar
	////////////////////	
	try
	{
		echo new WSidebar(null);
	}
	catch (Exception $e)
	{
		echo "<pre>".$e->getTraceAsString()."</pre>";
	}
	////////////////////
	
	if(RSent::hasValue('posted'))
	{
		foreach (RSent::data() as $key => $value) 
		{
			if(substr($key,0,5) == 'spore')
			{
				$spore = substr($key,6);
				$delete = !empty($value);
				
				if($delete)
				{
					QSpore::remove($spore);
				}
				else
				{
					QSpore::set(
						$spore, 
						RSent::hasValue('actv_'.$spore), 
						RSent::get('init_'.$spore), 
						RSent::get('err_'.$spore)
					);
				}
			}
		}
		if(RSent::hasValue('new_spore') && !QSpore::exists(RSent::get('new_spore')))
		{
			try{
				QSpore::set(
					RSent::get('new_spore'), 
					RSent::hasValue('new_actv'), 
					RSent::get('new_init'), 
					RSent::get('new_err')
				);
			}
			catch(Exception $e){
				//@todo notify  could not set blah
			}
		}
		QSpore::Save();
	}
}
?>
