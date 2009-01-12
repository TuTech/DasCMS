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
	RURL::alter('edit', $newNav);
	$EditingObject = $newNav.'.nav';
	$edit = $newNav;
}
if(PAuthorisation::has('org.bambuscms.layout.navigation.ntreenavigation.change'))
{
	printf(
		'<form method="post" id="documentform" name="documentform" action="%s"><input type="hidden" name="posted" value="1" />', 
		SLink::link(array('edit' => $edit))
	);
}
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




$AppController = BAppController::getControllerForID('org.bambuscms.applications.treenavigationeditor');
echo new WOpenDialog($AppController, $edit);


?>