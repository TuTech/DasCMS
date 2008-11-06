<?php
/************************************************
* Bambus CMS 
* Created:     21. Sep 07
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/

$EditingObject = '';
$edit = null;

if(RURL::has('edit') && NTreeNavigation::exists(RURL::get('edit')))
{
	if(RSent::has('delete_nav'))
	{
		//delete nav
		NTreeNavigation::remove(RURL::get('edit'));
		NTreeNavigation::save();
	}
	else
	{
		$EditingObject = RURL::get('edit').'.nav';
		$edit = RURL::get('edit');
	}
}
if($edit != null && RSent::has('1_p') && RSent::get('1_p') == '0')//parent of first has to be "0"
{
	//got data
	$data = array(1 => new NTreeNavigationObject('', null, null, null));
	$i = 2;
	//get all nav objects 
	while(RSent::has($i.'_p'))
	{
		$cid = RSent::get($i.'_cid');
		$data[$i] = new  NTreeNavigationObject($cid, null, null, null);
		$i++;
	}
	//link nav objects
	foreach ($data as $id => $obj) 
	{
		if(RSent::has($id.'_fc') && array_key_exists(RSent::get($id.'_fc'), $data))
		{
			$obj->setFirstChild($data[RSent::get($id.'_fc')]);
		}
		if(RSent::has($id.'_p') && array_key_exists(RSent::get($id.'_p'), $data))
		{
			$obj->setParent($data[RSent::get($id.'_p')]);
		}
		if(RSent::has($id.'_n') && array_key_exists(RSent::get($id.'_n'), $data))
		{
			$obj->setNext($data[RSent::get($id.'_n')]);
		}
	}
	try{
		if(RSent::has('set_spore') && QSpore::exists(RSent::get('set_spore')))
    	{
    		$sp = new QSpore(RSent::get('set_spore'));
    		SNotificationCenter::report('message', 'changing target view');
    	}
		else
		{
			$sp = NTreeNavigation::sporeOf($edit);
		}
		NTreeNavigation::set($edit,$sp, $data[1]);
		NTreeNavigation::Save();
		SNotificationCenter::report('message', 'saved');
	}
	catch(Exception $e)
	{
		SNotificationCenter::report('warning', $e->getMessage());
	}
}
if(RSent::has('new_nav_name'))
{
	$newNav = RSent::get('new_nav_name');
	if(QSpore::exists($newNav))
	{
		//matching spore exists - use it
		$spore = new QSpore($newNav);
	}
	else
	{
		$allSpores = QSpore::sporeNames();
		if(count($allSpores) == 0)
		{
			//no spores - create one
			QSpore::set($newNav,true,null,null);
			QSpore::Save();
			$spore = new QSpore($newNav);
		}
		else
		{
			//the are some spore use whatever comes first
			$spore = new QSpore($allSpores[0]);
		}
	}
	NTreeNavigation::set($newNav,$spore,new NTreeNavigationObject('', null,null,null));
	NTreeNavigation::Save();
	$EditingObject = $newNav.'.nav';
	$edit = $newNav;
}
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




$AppController = BAppController::getControllerForID('org.bambuscms.applications.treenavigationeditor');
echo new WOpenDialog($AppController, $edit);


?>