<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.treenavigation
 * @since 2007-09-21
 * @version 1.0
 */
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
		$edit = RURL::get('edit');
	}
}
if($edit != null && RSent::has('1_p') && RSent::get('1_p') == '0')//parent of first has to be "0"
{
	//got data
	$data = array(1 => new NTreeNavigationObject('', null, null, null));
	$i = 2;
	//remove empty next pointers
	while(RSent::has($i.'_n'))
	{
	    $next = RSent::get($i.'_n');
	    $nextAlias = RSent::get($next.'_cid');
	    while(empty($nextAlias) && RSent::has($next.'_n'))
	    {
	        $next = RSent::get($next.'_n');
	        $nextAlias = RSent::get($next.'_cid');
	    }
	    if(!empty($nextAlias))
	    {
	        RSent::alter($i.'_n', $next);
	    }
	    $i++;
	}
	$i = 2;
	//remove empty first-child pointers 
	while(RSent::has($i.'_fc'))
	{
	    //get the first child of element i
	    $fc = RSent::get($i.'_fc');//5
	    $origFc = $fc;
	    //get its alias
	    $fcAlias = RSent::get($fc.'_cid');
	    //if the alias is not set promote the first sibling of the first child with an alias to the first child position 
	    while(empty($fcAlias) && !empty($fc))
	    {
	        $fc = RSent::get($fc.'_n');
	        $fcAlias = RSent::get($fc.'_cid');
	    }
	    if($origFc != $fc && !empty($fcAlias))
	    {
	        RSent::alter($i.'_fc', $fc);
	    }
	    $i++;
	}
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
		if(RSent::has('set_spore') && VSpore::exists(RSent::get('set_spore')))
    	{
    		$sp = new VSpore(RSent::get('set_spore'));
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
    if(!preg_match('/^[a-zA-Z0-9\-_\.]+$/',$newNav))
    {
        SNotificationCenter::report('warning', 'navigation_name_not_valid');
        return;
    }
	if(VSpore::exists($newNav))
	{
		//matching spore exists - use it
		$spore = new VSpore($newNav);
	}
	else
	{
		$allSpores = VSpore::sporeNames();
		if(count($allSpores) == 0)
		{
			//no spores - create one
			VSpore::set($newNav,true,null,null);
			VSpore::Save();
			$spore = new VSpore($newNav);
		}
		else
		{
			//the are some spore use whatever comes first
			$spore = new VSpore($allSpores[0]);
		}
	}
	NTreeNavigation::set($newNav,$spore,new NTreeNavigationObject('', null,null,null));
	NTreeNavigation::Save();
	RURL::alter('edit', $newNav);
	$edit = $newNav;
}
WTemplate::globalSet('DocumentFormAction', SLink::link(array('edit' => $edit)));


$AppController = BAppController::getControllerForID('org.bambuscms.applications.treenavigationeditor');
echo new WOpenDialog($AppController, $edit);
?>