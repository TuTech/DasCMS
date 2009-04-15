<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-24
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class AFeeds
    extends 
        BAppController 
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog  
{
    const GUID = 'org.bambuscms.applications.feeds';
    
    /**
	 * @var CFeed
     */
    protected $target = null;
    
    public function setTarget($target)
    {
        try
        {
            if(!empty($target))
            {
                $this->target = CFeed::Open($target);
            }
        }
        catch (Exception $e)
        {
            $this->target = null;
        }
    }
    
    public function create(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cfeed.create');
        if(!empty($param['create']))
        {
            $this->target = CFeed::Create($param['create']);
        }
    }
    
    public function save(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cfeed.change');
        if($this->target != null
            && isset($param['filename']))
        {
            if(!empty($param['filename']))
            {
                $this->target->Title = $param['filename'];
            }
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
        	    CFeed::ITEM => array('LinkTitle' => '', 'LinkTags' => '', 'LinkIcon' => ''), 
        	    CFeed::FOOTER => array('PaginaType' => ''), 
        	    CFeed::SETTINGS => array()
        	);
        	
        	foreach ($param as $key => $value) 
        	{
        	    //read captions: icp_name -> item caption prefix for name
        		if(substr($key,1,1) == 'c' && substr($key,3,1) == '_')
        		{
        		    $type = $charTrans[substr($key,0,1)];
        		    $pos = $capPos[substr($key,2,1)];
        		    $name = substr($key,4);
        		    $captionsToSet[$type][$name] = isset($captionsToSet[$type][$name]) 
        		        ? $captionsToSet[$type][$name] 
        		        : array(CFeed::PREFIX => '', CFeed::SUFFIX => '');
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
        		        $this->target->changeCaption($type, $name, $values[CFeed::PREFIX], $values[CFeed::SUFFIX]);
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
    			    $this->target->changeOption(CFeed::SETTINGS, $name, $value);
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
    			    $this->target->changeOption(CFeed::ITEM, $name, $value);
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
        			    $this->target->changeOption($type, $name, $value);
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
        	    $this->target->changeOrder($type, $setOrder[$type]);
        	}
            
        }
    }
    
    public function delete(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cfeed.delete');
        if($this->target != null)
        {
            $alias = $this->target->Alias;
            if(CFeed::Delete($alias))
            {
                $this->target = null;
            }
        }
    }
    
    public function commit()
    {
        if($this->target != null && $this->target->isModified())
        {
            $this->target->Save();
        }
    } 
    
    /**
     * array(BContent|string file, [string mimetype])
     * 
     * @return array
     */
    public function getSideBarTarget()
    {
        $ret = array();
        if($this->target)
        {
            $ret = array($this->target);
        }
        return $ret;
    }

    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    /**
     * opened object 
     * @return string|null 
     */
    public function getOpenDialogTarget()
    {
        return empty($this->target) ? null : $this->target->Alias;
    }
    
    /**
     * returns all data necessary for the open dialog
     * @param array $namedParameters
     * @return array
     * @throws XPermissionDeniedException
     */
    public function provideOpenDialogData(array $namedParameters)
    {
        if(!$this->isPermitted('view'))
        {
            throw new XPermissionDeniedException('view');
        }
        $IDindex = CFeed::Index();
        $items = array();
        foreach ($IDindex as $alias => $data) 
        {
        	list($title, $pubdate) = $data;
        	$items[] = array($title, $alias, 0, strtotime($pubdate));
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array(CFeed::defaultIcon()->asSize(WIcon::LARGE)->getPath()),
            'smallIconMap' => array(CFeed::defaultIcon()->asSize(WIcon::EXTRA_SMALL)->getPath()),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'pubDate' => 'pubDate'),
            'items' => $items,
            'captions' => array(
                'detail' => SLocalization::get('detail'),
                'icon' => SLocalization::get('icon'),
                'list' => SLocalization::get('list'),
                'asc' => SLocalization::get('asc'),
                'desc' => SLocalization::get('desc'),
                'searchByTitle' => SLocalization::get('search_by_title'),
                'pubDate' => SLocalization::get('pubDate'),
                'notPublished' => SLocalization::get('not_published'),
                'title' => SLocalization::get('title'),
                'type' => SLocalization::get('type'),
            )
        );
        return $data;
    }
}
?>