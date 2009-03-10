<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.views
 * @since 2007-11-28
 * @version 1.0
 */

$panel = WSidePanel::alloc()->init();
$panel->setMode(WSidePanel::CONTENT_LOOKUP);
$panel->processInputs();
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
