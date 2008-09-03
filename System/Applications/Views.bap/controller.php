<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');


if(!empty($_POST['rebuildAliasDatabase']) && BAMBUS_GRP_ADMINISTRATOR)
{
	SNotificationCenter::report('message', 'rebuilding alias database');
	SAlias::alloc()->init()->rebuildAliases();
}
function getData($key, $GPCarray)
{
	$data = isset($GPCarray[$key]) ? $GPCarray[$key] : '';
	return (get_magic_quotes_gpc()) ? stripslashes($data) : $data;
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
	
	if(!empty($post['posted']))
	{
		foreach ($post as $key => $value) 
		{
			if(substr($key,0,5) == 'spore')
			{
				$spore = substr($key,6);
				$delete = !empty($post[$key]);
				
				if($delete)
				{
					QSpore::remove($spore);
				}
				else
				{
					QSpore::set(
						$spore, 
						!empty($post['actv_'.$spore]), 
						getData('init_'.$spore, $_POST), 
						getData('err_'.$spore, $_POST)
					);
				}
			}
		}
		if(!empty($post['new_spore']) && !QSpore::exists($post['new_spore']))
		{
			try{
				QSpore::set(
					$post['new_spore'], 
					!empty($post['new_actv']), 
					getData('new_init', $_POST), 
					getData('new_err', $_POST)
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
