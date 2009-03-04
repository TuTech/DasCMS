<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
//side bar
////////////////////	
try
{
	$panel = new WSidePanel();
	$panel->setMode(WSidePanel::CONTENT_LOOKUP);
	echo $panel;
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
				VSpore::remove($spore);
			}
			else
			{
				VSpore::set(
					$spore, 
					RSent::hasValue('actv_'.$spore), 
					RSent::get('init_'.$spore), 
					RSent::get('err_'.$spore)
				);
			}
		}
	}
	if(RSent::hasValue('new_spore') && !VSpore::exists(RSent::get('new_spore')))
	{
		try{
			VSpore::set(
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
	VSpore::Save();
}
?>
