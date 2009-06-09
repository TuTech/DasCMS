<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-07
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class ATreeNavigationEditor 
    extends 
        BAppController 
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog  
{
    const GUID = 'org.bambuscms.applications.treenavigationeditor';
        

    /**
	 * @var string
     */
    protected $target = null;
    protected $changed = false;
    
    public function setTarget($target)
    {
        if(!empty($target) && NTreeNavigation::exists($target))
        {
            $this->target = $target;
        }
    }
    
    public function create(array $param)
    {
        parent::requirePermission('org.bambuscms.content.ntreenavigation.create');
        if(!empty($param['create']))
        {
        	$newNav = $param['create'];
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
        	$this->changed = true;
        	$this->target = $newNav;
        }
    }
    
    /**
     * returns all data necessary for the open dialog
     * @param array $namedParameters
     * @return array
     * @throws XPermissionDeniedException
     */
    public function provideContentTags(array $namedParameters)
    {
        return array();
    }
    
    private static function val($k, array &$a)
    {
        return (isset($a[$k]))
            ? $a[$k]
            : '';
    }
    
    public function save(array $param)
    {
        parent::requirePermission('org.bambuscms.content.ntreenavigation.change');
        if($this->target != null
            && isset($param['1_p'])
            && $param['1_p'] == '0'
            )
        {
            $edit = $this->target;
        	//got data
        	$data = array(1 => new NTreeNavigationObject('', null, null, null));
        	$i = 2;
        	//remove empty next pointers
        	while(isset($param[$i.'_n']))
        	{
        	    $next = $param[$i.'_n'];
        	    $nextAlias = self::val($next.'_cid', $param);
        	    while(empty($nextAlias) && isset($param[$next.'_n']))
        	    {
        	        $next = self::val($next.'_n', $param);
        	        $nextAlias = self::val($next.'_cid', $param);
        	    }
        	    if(!empty($nextAlias))
        	    {
        	        $param[$i.'_n'] = $next;
        	    }
        	    $i++;
        	}
        	$i = 2;
        	//remove empty first-child pointers 
        	while(isset($param[$i.'_fc']))
        	{
        	    //get the first child of element i
        	    $fc = self::val($i.'_fc', $param);//5
        	    $origFc = $fc;
        	    //get its alias
        	    $fcAlias = self::val($fc.'_cid', $param);
        	    //if the alias is not set promote the first sibling of the first child with an alias to the first child position 
        	    while(empty($fcAlias) && !empty($fc))
        	    {
        	        $fc = self::val($fc.'_n', $param);
        	        $fcAlias = self::val($fc.'_cid', $param);
        	    }
        	    if($origFc != $fc && !empty($fcAlias))
        	    {
        	        $param[$i.'_fc'] = $fc;
        	    }
        	    $i++;
        	}
        	$i = 2;
        
        	//get all nav objects 
        	while(isset($param[$i.'_p']))
        	{
        		$cid = self::val($i.'_cid', $param);
        		$data[$i] = new  NTreeNavigationObject($cid, null, null, null);
        		$i++;
        	}
        	//link nav objects
        	foreach ($data as $id => $obj) 
        	{
        		if(isset($param[$id.'_fc']) && array_key_exists($param[$id.'_fc'], $data))
        		{
        			$obj->setFirstChild($data[$param[$id.'_fc']]);
        		}
        		if(isset($param[$id.'_p']) && array_key_exists($param[$id.'_p'], $data))
        		{
        			$obj->setParent($data[$param[$id.'_p']]);
        		}
        		if(isset($param[$id.'_n']) && array_key_exists($param[$id.'_n'], $data))
        		{
        			$obj->setNext($data[$param[$id.'_n']]);
        		}
        	}
        	try{
        		if(isset($param['set_spore']) && VSpore::exists($param['set_spore']))
            	{
            		$sp = new VSpore($param['set_spore']);
            		SNotificationCenter::report('message', 'changing target view');
            	}
        		else
        		{
        			$sp = NTreeNavigation::sporeOf($edit);
        		}
        		NTreeNavigation::set($edit,$sp, $data[1]);
        		SNotificationCenter::report('message', 'saved');
        		$this->changed = true;
        	}
        	catch(Exception $e)
        	{
        		SNotificationCenter::report('warning', $e->getMessage());
        	}
        }
    }
    
    public function delete(array $param)
    {
        parent::requirePermission('org.bambuscms.content.ntreenavigation.delete');
        if($this->target != null)
        {
            NTreeNavigation::remove($this->target);
            $this->target = null;
            $this->changed = true;
        }
    }
    
    public function commit()
    {
        if($this->changed)
        {
            NTreeNavigation::save();
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
            $ret = array($this->target, 'cms/treenavigation');
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
        return empty($this->target) ? null : $this->target;
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
        $navigations = NTreeNavigation::navigations();
        $items = array();
        foreach($navigations as $item)
        {
            $items[] = array($item, $item, 0);
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array('System/ClientData/Icons/tango/large/mimetypes/NTreeNavigation.png'),
            'smallIconMap' => array('System/ClientData/Icons/tango/extra-small/mimetypes/NTreeNavigation.png'),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2),//, 'tags' => 4
            'sortable' => array('title' => 'title'),
            'items' => $items
        );
        return $data;
    }
}
?>