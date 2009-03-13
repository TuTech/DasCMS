<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.feededitor
 * @since 2008-10-24
 * @version 1.0
 */
$AppController = BAppController::getControllerForID('org.bambuscms.applications.feeds');

$allowEdit = true;
$FileOpened = false;

$editExist = (RURL::has('edit')) && CFeed::Exists(RURL::get('edit'));
/**
 * @var CFeed
 */
$Feed = null;
//delete
if($editExist && RSent::get('delete', 'utf-8') != '' && PAuthorisation::has('org.bambuscms.content.cfeed.delete'))
{
	CFeed::Delete(RURL::get('edit'));
	$editExist = false;
}
if(RSent::get('action') == 'delete' && PAuthorisation::has('org.bambuscms.content.cfeed.delete'))
{
	foreach (RSent::data('utf-8') as $k => $v) 
	{
		if(substr($k,0,7) == 'select_' && !empty($v))
		{
			//delete
			CFeed::Delete(substr($k,7));
		}
	}
}

//create
elseif(RSent::hasValue('create') && PAuthorisation::has('org.bambuscms.content.cfeed.create'))
{
	$Title = RSent::get('create', 'utf-8');
	$Feed = CFeed::Create($Title);
}

//open for editing
elseif($editExist && PAuthorisation::has('org.bambuscms.content.cfeed.change'))
{
	$Feed = CFeed::Open(RURL::get('edit'));
}

//save data
if(isset($Feed) && $Feed instanceof CFeed && PAuthorisation::has('org.bambuscms.content.cfeed.change'))
{
	if(RSent::has('filename'))
	{
		$Feed->Title = RSent::get('filename', 'UTF-8');
		//////////////
		//reading data
		//////////////
    	$charTrans = array(
    	    'i' => CFeed::ITEM,
    	    'h' => CFeed::HEADER,
    	    'f' => CFeed::FOOTER,
    	    's' => CFeed::SETTINGS
    	);
    	$capPos = array(
    	    'p' => CFeed::PREFIX,
    	    's' => CFeed::SUFFIX
    	);
    	$captionsToSet = array(CFeed::HEADER => array(), CFeed::ITEM => array(), CFeed::FOOTER => array());
    	$optionsToSet = array(
    	    CFeed::HEADER => array('PaginaType' => ''), 
    	    CFeed::ITEM => array('LinkTitle' => '', 'LinkTags' => ''), 
    	    CFeed::FOOTER => array('PaginaType' => ''), 
    	    CFeed::SETTINGS => array()
    	);
    	
    	foreach (RSent::data('UTF-8') as $key => $value) 
    	{
    	    //read captions: icp_name -> item caption prefix for name
    		if(substr($key,1,1) == 'c' && substr($key,3,1) == '_')
    		{
    		    $type = $charTrans[substr($key,0,1)];
    		    $pos = $capPos[substr($key,2,1)];
    		    $name = substr($key,4);
    		    $captionsToSet[$type][$name] = isset($captionsToSet[$type][$name]) ? $captionsToSet[$type][$name] : array(CFeed::PREFIX => '', CFeed::SUFFIX => '');
    		    $captionsToSet[$type][$name][$pos] = $value;
    		}
    		//read options: io_name 
    		elseif(substr($key,1,1) == 'o' && substr($key,2,1) == '_')
    		{
    		    $type = $charTrans[substr($key,0,1)];
    		    $name = substr($key, 3);
    		    $optionsToSet[$type][$name] = $value; 
    		}
    	}
    	//////////////
    	//set captions
    	//////////////

    	foreach ($captionsToSet as $type => $pairs) 
    	{
    		foreach ($pairs as $name => $values) 
    		{
    		    try
    		    {
    		        $Feed->changeCaption($type, $name, $values[CFeed::PREFIX], $values[CFeed::SUFFIX]);
    		    }
    		    catch(Exception $e)
		        {
		            SNotificationCenter::report('warning', sprintf('could not set %s:%s', $type, $name));
		        }
    		}
    	}
    	
    	/////////////
    	//set options
    	/////////////

		foreach ($optionsToSet[CFeed::SETTINGS] as $name => $value) 
		{
		    $set = false;
			switch($name)
			{
			    case 'ItemsPerPage':
                case 'MaxPages':
			        $set = is_numeric($value);
			        break;
                case 'Filter':
                    $value = STag::parseTagStr($value);
                    $set = true;
                    break;
                case 'FilterMethod':
                    $set = in_array($value, array(
                        CFeed::ALL, 
                        CFeed::MATCH_ALL, 
                        CFeed::MATCH_SOME, 
                        CFeed::MATCH_NONE));
                    break;
                case 'TargetView':
                    if($value == '')//if-elseif used because php did not accept them or-ed in one if
                    {
                        $set = true;
                    }
                    elseif(class_exists('VSpore', true) && VSpore::exists($value))
                    {
                        $set = true;
                    }
                    break;
                case 'SortOrder':
                    $value = ($value == 'DESC');
                    $set = true;
                    break;
                case 'SortBy':
                    $set = in_array($value, array('title', 'pubdate'));
                    break;
			    default:break;
			}
			if($set)
			{
			    $Feed->changeOption(CFeed::SETTINGS, $name, $value);
			}
		}
		foreach ($optionsToSet[CFeed::ITEM] as $name => $value) 
		{
		    $set = false;
			switch($name)
			{
                case 'LinkTitle':
                case 'LinkTags':
                case 'LinkPreviewImage':
                case 'LinkIcon':
                    $value = strtolower($value) == 'on';
			    case 'ModDateFormat':
                case 'PubDateFormat':
                case 'IconSize':
                case 'PreviewImageWidth':
                case 'PreviewImageHeight':
                case 'PreviewImageBgColor':
                case 'PreviewImageMode':
                    $set = true;
			        break;
			    default:break;
			}
			if($set)
			{
			    $Feed->changeOption(CFeed::ITEM, $name, $value);
			}
		}
		foreach (array(CFeed::HEADER, CFeed::FOOTER) as $type) 
		{
			foreach ($optionsToSet[$type] as $name => $value) 
    		{
    		    $set = false;
    			switch($name)
    			{
    			    case 'PaginaType':
    			        $value = strtolower($value) == 'on';
    			        $set = true;
    			        break;
    			    default:break;
    			}
    			if($set)
    			{
    			    $Feed->changeOption($type, $name, $value);
    			}
    		}
		}
		
		///////////
    	//set order
    	///////////
    	
    	$hf_items = array(
    	    'number_of_start' => 'NumberOfStart', 
    	    'number_of_end' => 'NumberOfEnd', 
    	    'element_count' => 'FoundItems', 
    	    'previous_link' => 'PrevLink', 
    	    'page_no' => 'Pagina', 
    	    'next_link' => 'NextLink'
		);
    	$i_items = array(
        	'content' => 'Content', 
        	'link' => 'Link', 
        	'tags' => 'Tags', 
        	'modDate' => 'ModDate', 
        	'title' => 'Title', 
        	'description' => 'Description', 
        	'author' => 'Author', 
        	'pubDate' => 'PubDate',
    		'icon' => 'Icon',
    		'previewImage' => 'PreviewImage'  
		);
    	$items = array(
    	    CFeed::HEADER => $hf_items,
    	    CFeed::ITEM => $i_items,
    	    CFeed::FOOTER => $hf_items
    	);
    	$setOrder = array();
    	$elements = array(
    	    CFeed::HEADER => 'headerConfig', 
    	    CFeed::ITEM => 'itemConfig', 
    	    CFeed::FOOTER => 'footerConfig'
		);
    	foreach (array_keys($items) as $type) 
    	{
    	    $setOrder[$type] = array();
    	    //printf("\n%s\n",$elements[$type]);
    	    foreach ($items[$type] as $item => $name) 
    	    {
    	        //printf("    %s[%s]: '",$item, $name);
    	    	if(WPropertyEditor::getPropStatus($elements[$type],$item))
    	    	{
    	    	    $setOrder[$type][$name] = WPropertyEditor::getPropPos($elements[$type],$item);
    	    	    //echo $setOrder[$type][$name];
    	    	}
    	    	else
    	    	{
    	    	    $setOrder[$type][$name] = null;
    	    	    //echo 'null';
    	    	}
    	    	//echo "'\n";
    	    }
    	    $Feed->changeOrder($type, $setOrder[$type]);
    	}
	}
}
echo new WOpenDialog($AppController, $Feed);
WTemplate::globalSet('DocumentFormAction', SLink::link(array('edit' => (isset($Feed) && $Feed instanceof CFeed)? $Feed->Alias :'')));

if(isset($Feed))
{
    $panel = WSidePanel::alloc()->init();
    $panel->setTargetContent($Feed);
}
if(isset($Feed) && $Feed instanceof CFeed && $Feed->isModified())
{
    $Feed->Save();
}

?>